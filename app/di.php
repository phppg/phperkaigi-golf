<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Bag2\Cookie;
use Cake\Chronos\Chronos;
use DI;
use Jose\Component\Checker;
use Jose\Component\Checker\ClaimCheckerManager as JoseClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager as JoseHeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager as JoseAlgorithmManager;
use Jose\Component\Core\AlgorithmManagerFactory as JoseAlgorithmManagerFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JoseSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Playground\Web\Http;
use Playground\Web\View;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\{RequestFactoryInterface, RequestInterface, ResponseFactoryInterface, ResponseInterface, ServerRequestFactoryInterface, ServerRequestInterface, StreamFactoryInterface, StreamInterface, UploadedFileFactoryInterface, UploadedFileInterface, UriFactoryInterface, UriInterface};
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Twig\Environment as Twig;
use Twig\Extension\OptimizerExtension as TwigOptimizer;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
use Twig\NodeVisitor\OptimizerNodeVisitor;
use Twig\TwigFunction;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;
use Whoops\RunInterface as WhoopsInterface;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;

$builder = new DI\ContainerBuilder();
// $builder->enableCompilation(__DIR__ . '/../cache');
// $builder->writeProxiesToFile(true, __DIR__ . '/../cache/proxies');
$builder->addDefinitions((include __DIR__ . '/../config.php') + [
    Chronos::class => factory(function (): Chronos {
        return Chronos::now();
    }),
    Cookie\Oven::class => factory(function () {
        return new Cookie\Oven(['path' => '/', 'httponly' => true, 'samesite' => 'Strict']);
    }),
    EmitterInterface::class => create(SapiEmitter::class),
    JoseAlgorithmManager::class => factory(function (JoseAlgorithmManagerFactory $factory) {
        return $factory->create(['HS256']);
    }),
    JoseAlgorithmManagerFactory::class => factory(function (): JoseAlgorithmManagerFactory {
        $factory = new JoseAlgorithmManagerFactory();
        $factory->add('HS256', new HS256());

        return $factory;
    }),
    JoseClaimCheckerManager::class => factory(function (Container $c): JoseClaimCheckerManager {
        return new JoseClaimCheckerManager([
            new Checker\IssuedAtChecker(),
            new Checker\NotBeforeChecker(),
            new Checker\ExpirationTimeChecker(),
            new Checker\IssuerChecker($c->get('jose.issuers')),
        ]);
    }),
    JoseHeaderCheckerManager::class => factory(function (Container $c): JoseHeaderCheckerManager {
        return new JoseHeaderCheckerManager([
            new Checker\IssuedAtChecker(),
            new Checker\NotBeforeChecker(),
            new Checker\ExpirationTimeChecker(),
            new Checker\IssuerChecker($c->get('jose.issuers')),
        ], [new JWSTokenSupport()]);
    }),
    JWSSerializerManager::class => factory(function (): JWSSerializerManager {
        return new JWSSerializerManager([new JoseSerializer]);
    }),
    JWSBuilder::class => factory(function (JoseAlgorithmManager $algo): JWSBuilder {
        return new JWSBuilder($algo);
    }),
    JWSLoader::class => factory(function (
        JWSSerializerManager $serializer_manager,
        JWSVerifier $verifier,
        JoseHeaderCheckerManager $header_checker_manager
    ): JWSLoader {
        return new JWSLoader($serializer_manager, $verifier, $header_checker_manager);
    }),
    JWSVerifier::class => factory(function (JoseAlgorithmManager $algo): JWSVerifier {
        return new JWSVerifier($algo);
    }),
    Http\Dispatcher::class => factory(function (Container $c) {
        return new Http\Dispatcher(
            $c->get('is_production'),
            $c->get(ResponseFactoryInterface::class),
            $c->get(StreamFactoryInterface::class),
            $c->get(View\HtmlFactory::class),
        );
    }),
    Http\SessionStorage::class => factory(function (Container $c): Http\SessionStorage {
        $serializer = $c->get(JoseSerializer::class);
        $jwk = $c->get(JWK::class);
        $jws_builder = $c->get(JWSBuilder::class);
        $jws_loader = $c->get(JWSLoader::class);
        $jws_verifier = $c->get(JWSVerifier::class);
        $now = $c->get(Chronos::class);
        $oven = $c->get(Cookie\Oven::class);
        $cookie_name = $c->get('cookie_name');

        return new Http\CookieJwtSession($serializer, $jwk, $jws_builder, $jws_loader, $jws_verifier, $now, $oven, $cookie_name);
    }),
    Psr17Factory::class => create(Psr17Factory::class),
    RequestFactoryInterface::class => get(Psr17Factory::class),
    ResponseFactoryInterface::class => get(Psr17Factory::class),
    RouterContainer::class => factory(function () {
        return include __DIR__ . '/routes.php';
    }),
    ServerRequestInterface::class => factory(function (Psr17Factory $http_factory) {
        return (new ServerRequestCreator(
            $http_factory, $http_factory, $http_factory, $http_factory
        ))->fromGlobals();
    }),
    StreamFactoryInterface::class => get(Psr17Factory::class),
    Twig::class => factory(function (Container $c, RouterContainer $router) {
        $is_production = $c->get('is_production');

        $twig = new Twig(new TwigFilesystemLoader([__DIR__ . '/tpl']), [
            'cache' => $is_production ? __DIR__ . '/../cache/twig' : false,
            'debug' => !$is_production,
            'strict_variables' => true,
        ]);

        $gen = $router->getGenerator();

        $twig->addFunction(new TwigFunction(
            'route',
            function (string $name, array $params = []) use ($gen): string {
                return $gen->generate($name, $params);
            }
        ));

        if ($is_production) {
            $twig->addExtension(new TwigOptimizer(OptimizerNodeVisitor::OPTIMIZE_ALL));
        }

        return $twig;
    }),
    UploadedFileFactoryInterface::class => get(Psr17Factory::class),
    UriFactoryInterface::class => get(Psr17Factory::class),
    UuidFactoryInterface::class => get(UuidFactory::class),
    WhoopsInterface::class => factory(function () {
        $whoops = new Whoops;
        $whoops->appendHandler(new PrettyPageHandler());

        return $whoops;
    }),
    View\HtmlFactory::class => factory(function (Twig $twig) {
        return new View\HtmlFactory($twig);
    }),
]);

return $builder->build();
