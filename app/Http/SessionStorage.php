<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Playground\Web\Session;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

interface SessionStorage
{
    public function fromRequest(ServerRequest $request): self;
    public function getSession(): Session;
    public function writeTo(Response $response): Response;
}
