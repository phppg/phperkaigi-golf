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

        $id = $cookies['id'] ?? null;
        $username = $cookies['username'] ?? null;
        $this->session_storage->fromRequest($request);

        $session = $this->session_storage->getSession();

        if ($id !== null) {
            $session->id = (int)$id;
        }

        if ($username !== null) {
            $session->name = $username;
        }

        $request = $request->withAttribute('session', $session);

        return $this->session_storage->writeTo($handler->handle($request));
    }
}
