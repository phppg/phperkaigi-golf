<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Aura\Router\Generator as RouteGenerator;
use Playground\Web\Session;
use Playground\Web\View;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;

final class TermsAgreementAction
{
    private ResponseFactory $factory;
    private RouteGenerator $route_gen;
    private Session $session;
    private StreamFactory $stream;
    private View\HtmlFactory $html;

    public function __construct(
        ResponseFactory $factory,
        RouteGenerator $route_gen,
        Session $session,
        StreamFactory $stream,
        View\HtmlFactory $html
    ) {
        $this->factory = $factory;
        $this->route_gen = $route_gen;
        $this->session = $session;
        $this->stream = $stream;
        $this->html = $html;
    }

    public function __invoke(ServerRequest $request): HttpResponse
    {
        $params = $request->getParsedBody();

        $accepted_terms = ($params['agree'] ?? '') === 'yes';
        $matched_place = ($params['place'] ?? '') === 'ココネリ';

        if (!($accepted_terms && $matched_place)) {
            return $this->factory->createResponse()->withBody(
                $this->stream->createStream(($this->html)('terms', [
                    'error' => [
                        'agree' => !$accepted_terms,
                        'place' => !$matched_place,
                    ],
                    'input' => [
                        'agree' => $accepted_terms,
                        'place' => $matched_place ? $params['place'] : '',
                    ],
                ]))
            );
        }

        $this->session->accepted_terms = true;
        $this->session->place = '#PHPerGolf2020inCoconeriCountryClub';

        return $this->factory->createResponse(302)
            ->withHeader('Location', $this->route_gen->generate('index'));
    }
}
