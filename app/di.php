<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Cake\Chronos\Chronos;
use DI;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\{RequestFactoryInterface, RequestInterface, ResponseFactoryInterface, ResponseInterface, ServerRequestFactoryInterface, ServerRequestInterface, StreamFactoryInterface, StreamInterface, UploadedFileFactoryInterface, UploadedFileInterface, UriFactoryInterface, UriInterface};
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
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
$builder->addDefinitions([
    Chronos::class => create(Chronos::class),
    EmitterInterface::class => create(SapiEmitter::class),
    Psr17Factory::class => create(Psr17Factory::class),
    RequestFactoryInterface::class => get(Psr17Factory::class),
    ResponseFactoryInterface::class => get(Psr17Factory::class),
    RouterContainer::class => create(RouterContainer::class),
    ServerRequestInterface::class => factory(function (Psr17Factory $http_factory) {
        return (new ServerRequestCreator(
            $http_factory, $http_factory, $http_factory, $http_factory
        ))->fromGlobals();
    }),
    StreamFactoryInterface::class => get(Psr17Factory::class),
    Twig::class => factory(function () {
        return new Twig(new TwigFilesystemLoader([__DIR__ . '/tpl']), [
            'cache' => __DIR__ . '/../cache/twig',
        ]);
    }),
    UploadedFileFactoryInterface::class => get(Psr17Factory::class),
    UriFactoryInterface::class => get(Psr17Factory::class),
    WhoopsInterface::class => factory(function () {
        $whoops = new Whoops;
        $whoops->appendHandler(new PrettyPageHandler());

        return $whoops;
    }),
]);

return $builder->build();
