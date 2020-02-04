<?php

declare(strict_types=1);

namespace Playground\Web;

use Aura\Router\RouterContainer;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
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

$map->get('phpinfo', '/phpinfo.php', function (ResponseFactory $factory, StreamFactory $stream) {
    ob_start();
    phpinfo();

    return $factory->createResponse()->withBody($stream->createStream(ob_get_clean()));
});

return $router;
