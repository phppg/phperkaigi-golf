<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use RuntimeException;
use function ob_get_clean;
use function ob_start;
use function phpinfo;
use function safe\file_get_contents;

$router = new RouterContainer();
$map = $router->getMap();

$map->get('index', '/', fn (
    ResponseFactory $factory,
    StreamFactory $stream,
    View\HtmlFactory $html
): HttpResponse => $factory->createResponse()->withBody($stream->createStream($html('index', [
]))));

$map->get('terms', '/terms', fn (
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

$map->get('login', '/login', fn (
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

$map->post('post_terms', '/terms', fn (Http\TermsAgreementAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->post('post_login', '/login', fn (Http\LoginAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->get('phpinfo', '/phpinfo.php', function (ResponseFactory $factory, StreamFactory $stream) {
    ob_start();
    phpinfo();

    return $factory->createResponse()->withBody($stream->createStream(ob_get_clean() ?: ''));
});

$map->get('phpini', '/php.ini', function (ResponseFactory $factory, StreamFactory $stream, Container $c) {
    return $factory->createResponse()
        ->withHeader('Content-Type', 'text/plain')
        ->withBody($stream->createStream(file_get_contents($c->get('sandbox_ini'))));
});

$map->get('sandbox', '/sandbox', function (ResponseFactory $factory, StreamFactory $stream, View\HtmlFactory $html): HttpResponse {
    return $factory->createResponse()->withBody($stream->createStream($html('sandbox', [
        'code' => <<<'PHP'
         <?php declare(strict_types=1);

         if (true) if (true) echo 'Hello, world!';
         PHP,
        'accepted' => null,
        'errors' => null,
        'error_output' => null,
        'output' => '',
        'pretty_print' => null,
        'stats' => null,
    ])));
});

$map->get('golf', '/golf', function (HoleManager $manager, ResponseFactory $factory, ServerRequest $request, StreamFactory $stream, View\HtmlFactory $html): HttpResponse {
    parse_str($request->getUri()->getQuery(), $query);

    $slug = $query['hole'] ?? null;

    if (!is_string($slug) || !$manager->has($slug)) {
        return $factory->createResponse(404)->withBody($stream->createStream($html('404', [])));
    }

    $hole = $manager->get($slug);

    return $factory->createResponse()->withBody($stream->createStream($html('golf', [
        'code' => $hole->getDefaultCode(),
        'cupped_in' => null,
        'hole' => $hole,
        'accepted' => null,
        'errors' => null,
        'error_output' => null,
        'output' => '',
        'pretty_print' => null,
        'stats' => null,
    ])));
});

$map->post('post_sandbox', '/sandbox', fn (Http\SandboxAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->post('post_golf', '/golf', fn (Http\GolfAction $action, ServerRequest $request): HttpResponse => $action($request));

$map->get('http.500', '/http/500', function () {
    throw new RuntimeException('Expected unexpected Error!');
});

return $router;
