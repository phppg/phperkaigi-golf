<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Playground\Web\View;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class Dispatcher implements MiddlewareInterface
{
    private bool $is_production;
    private ResponseFactory $response_factory;
    private StreamFactory $stream_factory;
    private View\HtmlFactory $html_factory;

    public function __construct(
        bool $is_production,
        ResponseFactory $response_factory,
        StreamFactory $stream_factory,
        View\HtmlFactory $html_factory
    ) {
        $this->is_production = $is_production;
        $this->response_factory = $response_factory;
        $this->stream_factory = $stream_factory;
        $this->html_factory = $html_factory;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): HttpResponse {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            if (!$this->is_production) {
                throw $e;
            }
        }

        return $this->response_factory->createResponse(500)
            ->withBody($this->stream_factory->createStream(
                ($this->html_factory)('500', [])
            ));
    }
};
