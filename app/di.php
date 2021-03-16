<?php

declare(strict_types=1);

namespace Playground\Web;

use Atlas\Orm\Atlas;
use Aura\Router\Generator as RouteGenerator;
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
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\ConverterInterface as MarkdownConverter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinterAbstract;
use Playground\CommandBuilder;
use Playground\File;
use Playground\Invoker;
use Playground\Process\SymfonyProcessFactory as ProcessFactory;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use RandomLib\Factory as RandomFactory;
use RandomLib\Generator as RandomGenerator;
use SecurityLib;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
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
    Atlas::class => factory(function (Container $c) {
        $config = $c->get('atlas');

        return Atlas::new(...$config['pdo']);
    }),
    Chronos::class => factory(function (): Chronos {
        return Chronos::now();
    }),
    CommandBuilder::class => factory(function (Container $c): CommandBuilder\DefaultCommand {
        return new CommandBuilder\DefaultCommand([
            'name' => 'php',
            'ini' => $c->get('sandbox_ini'),
            'noconf' => true,
            'defines' => $c->get('sandbox_php_defines'),
        ]);
    }),
    Cookie\Oven::class => factory(function (Container $c) {
        return new Cookie\Oven([
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => $c->get('use_https'),
        ]);
    }),
    EmitterInterface::class => create(SapiEmitter::class),
    File::class => factory(function (FileFactory $factory): File {
        return $factory->create('');
    }),
    HoleManager::class => function (): HoleManager {
        $manager = new HoleManager();
        $manager->add('helloworld', new Hole\HelloWorld());
        $manager->add('fizzbuzz', new Hole\FizzBuzz());
        $manager->add('arrayconvert', new Hole\ArrayConvert());

        return $manager;
    },
    Http\TermsAgreementAction::class => autowire(),
    Invoker::class => factory(function (Container $c): Invoker\ProcessInvoker {
        return new Invoker\ProcessInvoker(
            $c->get(CommandBuilder::class),
            $c->get('sandbox_dir'),
            [],
            $c->get(File::class),
            $c->get('sandbox_timeout'),
            $c->get(ProcessFactory::class)
        );
    }),
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
        return new JWSSerializerManager([new JoseSerializer()]);
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
    MarkdownConverter::class => factory(function (CommonMarkConverter $converter) {
        return new CommonMarkConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => true,
        ]);
    }),
    ParsedCodeFactory::class => autowire(),
    PrettyPrinterAbstract::class => create(PrettyPrinter\Standard::class),
    Psr17Factory::class => create(Psr17Factory::class),
    RandomGenerator::class => factory(function (RandomFactory $factory): RandomGenerator {
        return $factory->getGenerator(new SecurityLib\Strength(SecurityLib\Strength::MEDIUM));
    }),
    RequestFactoryInterface::class => get(Psr17Factory::class),
    ResponseFactoryInterface::class => get(Psr17Factory::class),
    RouterContainer::class => factory(function () {
        return include __DIR__ . '/routes.php';
    }),
    RouteGenerator::class => factory(function (RouterContainer $router): RouteGenerator {
        return $router->getGenerator();
    }),
    ServerRequestInterface::class => factory(function (Psr17Factory $http_factory) {
        return (new ServerRequestCreator(
            $http_factory,
            $http_factory,
            $http_factory,
            $http_factory
        ))->fromGlobals();
    }),
    Session::class => factory(function (Http\SessionStorage $storage): Session {
        return $storage->getSession();
    }),
    StreamFactoryInterface::class => get(Psr17Factory::class),
    Twig::class => factory(function (Container $c, RouteGenerator $gen, MarkdownConverter $md) {
        $is_production = $c->get('is_production');

        $twig = new Twig(new TwigFilesystemLoader([__DIR__ . '/tpl']), [
            'cache' => $is_production ? __DIR__ . '/../cache/twig' : false,
            'debug' => !$is_production,
            'strict_variables' => true,
        ]);

        $twig->addFunction(new TwigFunction(
            'route',
            function (string $name, array $params = []) use ($gen): string {
                return $gen->generate($name, $params) ?: '';
            }
        ));

        $twig->addFunction(new TwigFunction(
            'markdown',
            function (string $source) use ($md): string {
                return $md->convertToHtml($source);
            },
            ['is_safe' => ['html']]
        ));

        return $twig;
    }),
    UploadedFileFactoryInterface::class => get(Psr17Factory::class),
    UriFactoryInterface::class => get(Psr17Factory::class),
    UuidFactoryInterface::class => get(UuidFactory::class),
    WhoopsInterface::class => factory(function () {
        $whoops = new Whoops();
        $whoops->appendHandler(new PrettyPageHandler());

        return $whoops;
    }),
    View\HtmlFactory::class => factory(function (Twig $twig, Http\SessionStorage $session_storage) {
        return new View\HtmlFactory($twig, $session_storage);
    }),
]);

return $builder->build();
