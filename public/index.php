<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface as Emitter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Relay\Relay;
use Throwable;
use Whoops\RunInterface as WhoopsInterface;
use const PHP_VERSION;
use function date_default_timezone_set;
use function error_reporting;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
date_default_timezone_set('Asia/Tokyo');

$container = (include __DIR__ . '/../app/di.php');
$container->get(WhoopsInterface::class)->register();

$server_request = $container->get(ServerRequest::class);
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . $server_request->getUri()->getPath())) {
    return false;
}

$http = $container->get(Psr17Factory::class);
$router = $container->get(RouterContainer::class);

$_404 = fn(ResponseFactory $factory, StreamFactory $stream, View\HtmlFactory $html): HttpResponse
    => $factory->createResponse(404)->withBody($stream->createStream($html('404', [])));

/** @var array<\Closure|MiddlewareInterface> */
$queue = [];

$queue[] = fn(ServerRequest $request, RequestHandler $handler): HttpResponse
    => $handler->handle($request)->withHeader('X-Powered-By', 'PHP/' . PHP_VERSION)->withHeader('X-Robots-Tag', 'noindex');
$queue[] = $container->get(Http\Dispatcher::class);
$queue[] = $container->get(Http\SessionSetter::class);
$queue[] = function (ServerRequest $request, RequestHandler $handler) use ($http, $router): HttpResponse {
    $session = $request->getAttribute('session');

    $uri = $request->getUri()->getPath();

    if (!in_array($uri, ['/', '/terms'], true)) {
        $gen = $router->getGenerator();

        return $http->createResponse(302)->withHeader('Location', $gen->generate('terms'));
    }

    return $handler->handle($request);
};

$queue[] = fn (ServerRequest $request): HttpResponse
    => $container->call($router->getMatcher()->match($request)->handler ?? $_404);

$relay = new Relay($queue);
$response = $relay->handle($server_request);

$container->get(Emitter::class)->emit($response);
