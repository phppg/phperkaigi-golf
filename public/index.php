<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface as Emitter;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Server\RequestHandlerInterface;
use Relay\Relay;
use Relay\ResponseFactoryMiddleware;
use Whoops\RunInterface as WhoopsInterface;
use function error_reporting;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

$container = (include __DIR__ . '/../app/di.php');
$container->get(WhoopsInterface::class)->register();

$router = $container->get(RouterContainer::class);

$_404 = fn(ResponseFactory $factory, StreamFactory $stream, View\HtmlFactory $html): HttpResponse
    => $factory->createResponse(404)->withBody($stream->createStream($html('404', [])));

/** @var array<\Closure|ResponseFactoryMiddleware> */
$queue = [];
$queue[] = fn (ServerRequestInterface $request): HttpResponse
    => $container->call($router->getMatcher()->match($request)->handler ?? $_404);

$relay = new Relay($queue);
$response = $relay->handle($container->get(ServerRequestInterface::class));

$container->get(Emitter::class)->emit($response);
