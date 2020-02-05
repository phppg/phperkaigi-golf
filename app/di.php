<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Cake\Chronos\Chronos;
use DI;
use Firebase\JWT\JWT;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7\Factory\Psr17Factory;
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
    Chronos::class => create(Chronos::class),
    EmitterInterface::class => create(SapiEmitter::class),
    JWT::class => create(JWT::class),
    JwtEncoder::class => factory(function (JWT $jwt) {
        return new JwtEncoder($jwt);
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
