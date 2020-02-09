<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class SessionSetter implements MiddlewareInterface
{
    private SessionStorage $session_storage;

    public function __construct(SessionStorage $session_storage)
    {
        $this->session_storage = $session_storage;
    }

    public function process(ServerRequest $request, RequestHandler $handler): HttpResponse
    {
        $cookies = $request->getCookieParams();
        $this->session_storage->fromRequest($request);

        $request = $request->withAttribute('session', $this->session_storage->getSession());

        return $this->session_storage->writeTo($handler->handle($request));
    }
}
