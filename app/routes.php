<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use function ob_start;
use function ob_get_clean;
use function phpinfo;

$router = new RouterContainer();
$map = $router->getMap();

$map->get('index', '/', fn(
    ResponseFactory $factory,
    StreamFactory $stream,
    View\HtmlFactory $html
): HttpResponse => $factory->createResponse()->withBody($stream->createStream($html('index', [
]))));

$map->get('terms', '/terms', fn(
    ResponseFactory $factory,
    StreamFactory $stream,
    View\HtmlFactory $html
): HttpResponse => $factory->createResponse()->withBody($stream->createStream($html('terms', [
    'error' => [
        'fortee_name_duplicate' => false,
        'agree' => false,
        'place' => false,
    ],
    'input' => [
        'fortee_name' => '',
        'display_name' => '',
        'agree' => false,
        'place' => '',
    ],
]))));

$map->get('login', '/login', fn(
    ResponseFactory $factory,
    StreamFactory $stream,
    View\HtmlFactory $html
): HttpResponse => $factory->createResponse()->withBody($stream->createStream($html('login', [
    'error' => [
        'fortee_name_duplicate' => false,
        'agree' => false,
        'place' => false,
    ],
    'input' => [
        'fortee_name' => '',
        'display_name' => '',
        'agree' => false,
        'place' => '',
    ],
]))));

$map->post('post_terms', '/terms', fn(Http\TermsAgreementAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->post('post_login', '/login', fn(Http\LoginAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->get('phpinfo', '/phpinfo.php', function (ResponseFactory $factory, StreamFactory $stream) {
    ob_start();
    phpinfo();

    return $factory->createResponse()->withBody($stream->createStream(ob_get_clean() ?: ''));
});

$map->get('sandbox', '/sandbox', function (ResponseFactory $factory, StreamFactory $stream, View\HtmlFactory $html): HttpResponse {
    return $factory->createResponse()->withBody($stream->createStream($html('sandbox', [
        'code' => <<<PHP
         <?php declare(strict_types=1);

         if (true) if (true) echo 'Hello, world!';
         PHP,
        'errors' => null,
        'error_output' => null,
        'output' => null,
        'pretty_print' => null,
        'stats' => null,
    ])));
});

$map->post('post_sandbox', '/sandbox', fn(Http\SandboxAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->get('http.500', '/http/500', function () {
    throw new \RuntimeException('Expected unexpected Error!');
});

return $router;
