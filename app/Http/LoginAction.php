<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Atlas\Orm\Atlas;
use Aura\Router\Generator as RouteGenerator;
use Cake\Chronos\Chronos;
use const PASSWORD_DEFAULT;
use function password_hash;
use Playground\Web\DataSource\MySQL\Password\Password;
use Playground\Web\DataSource\MySQL\Player\Player;
use Playground\Web\Session;
use Playground\Web\View;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use RandomLib\Generator as RandomGenerator;

final class LoginAction
{
    private const PASSWORD_CHARS =
        'abcdefghijklmnopqrstuvwxyz' .
        'ABCDEFGHIJKLMNOPQRSQUVWXYZ' .
        '0123456789-_#!$%&+@';

    private Atlas $atlas;
    private Chronos $now;
    private ResponseFactory $factory;
    private RouteGenerator $route_gen;
    private RandomGenerator $rand_gen;
    private Session $session;
    private StreamFactory $stream;
    private View\HtmlFactory $html;

    public function __construct(
        Atlas $atlas,
        Chronos $now,
        ResponseFactory $factory,
        RouteGenerator $route_gen,
        RandomGenerator $rand_gen,
        Session $session,
        StreamFactory $stream,
        View\HtmlFactory $html
    ) {
        $this->atlas = $atlas;
        $this->now = $now;
        $this->factory = $factory;
        $this->rand_gen = $rand_gen;
        $this->route_gen = $route_gen;
        $this->session = $session;
        $this->stream = $stream;
        $this->html = $html;
    }

    public function __invoke(ServerRequest $request): HttpResponse
    {
        /** @var array{fortee_name?:string,login_code?:string} */
        $params = $request->getParsedBody();

        $fortee_name = $params['fortee_name'] ?? '';
        $login_code = $params['login_code'] ?? '';

        $player = $this->atlas->select(Player::class)
            ->where('fortee_name =', $fortee_name)
            ->fetchRecord();

        $password = null;
        if ($player !== null) {
            $password = $this->atlas->select(Password::class)
                ->where('player_id =', $player->id)
                ->fetchRecord();
        }

        $verified = false;
        if ($password !== null) {
            $verified = password_verify($login_code, $password->hash);
        }

        if (!$verified) {
            return $this->factory->createResponse()->withBody(
                $this->stream->createStream(($this->html)('login', [
                    'error' => [
                        'fortee_name_not_found' => $password,
                    ],
                    'input' => [
                        'fortee_name' => $fortee_name,
                    ],
                ]))
            );
        }

        $this->session->accepted_terms = true;
        $this->session->place = '#PHPerGolf2020inCoconeriCountryClub';
        $this->session->login_code = $login_code;

        $this->atlas->commit();

        return $this->factory->createResponse(302)
            ->withHeader('Location', $this->route_gen->generate('index'));
    }
}
