<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Atlas\Orm\Atlas;
use Aura\Router\Generator as RouteGenerator;
use Bag2\Cookie\Oven;
use Cake\Chronos\Chronos;
use Playground\Web\DataSource\MySQL\Password\Password;
use Playground\Web\DataSource\MySQL\Player\Player;
use Playground\Web\Session;
use Playground\Web\View;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use RandomLib\Generator as RandomGenerator;
use function password_hash;
use const PASSWORD_DEFAULT;

final class TermsAgreementAction
{
    private const PASSWORD_CHARS =
        'abcdefghijklmnopqrstuvwxyz' .
        'ABCDEFGHIJKLMNOPQRSQUVWXYZ' .
        '0123456789-_#!$%&+@';

    private Atlas $atlas;
    private Chronos $now;
    private Oven $oven;
    private ResponseFactory $factory;
    private RouteGenerator $route_gen;
    private RandomGenerator $rand_gen;
    private Session $session;
    private StreamFactory $stream;
    private View\HtmlFactory $html;

    public function __construct(
        Atlas $atlas,
        Chronos $now,
        Oven $oven,
        ResponseFactory $factory,
        RouteGenerator $route_gen,
        RandomGenerator $rand_gen,
        Session $session,
        StreamFactory $stream,
        View\HtmlFactory $html
    ) {
        $this->atlas = $atlas;
        $this->now = $now;
        $this->oven = $oven;
        $this->factory = $factory;
        $this->rand_gen = $rand_gen;
        $this->route_gen = $route_gen;
        $this->session = $session;
        $this->stream = $stream;
        $this->html = $html;
    }

    public function __invoke(ServerRequest $request): HttpResponse
    {
        /** @var array{fortee_name?:string,display_name?:string,email_addr?:string,agree?:string,place?:string} */
        $params = $request->getParsedBody();

        $fortee_name = $params['fortee_name'] ?? '';
        $display_name = $params['display_name'] ?? '';
        $email_addr = $params['email_addr'] ?? '';
        $accepted_terms = ($params['agree'] ?? '') === 'yes';
        $matched_place = ($params['place'] ?? '') === 'ココネリ';

        $player_record = $this->atlas->select(Player::class)
            ->where('fortee_name =', $fortee_name)
            ->fetchRecord();
        $no_record = $player_record === null;
        if (!($accepted_terms && $matched_place && $no_record)) {
            return $this->factory->createResponse()->withBody(
                $this->stream->createStream(($this->html)('terms', [
                    'error' => [
                        'fortee_name_duplicate' => !$no_record,
                        'agree' => !$accepted_terms,
                        'place' => !$matched_place,
                    ],
                    'input' => [
                        'fortee_name' => $fortee_name,
                        'display_name' => $display_name,
                        'agree' => $accepted_terms,
                        'place' => $matched_place ? $params['place'] : '',
                    ],
                ]))
            );
        }

        $login_code = $this->rand_gen->generateString(16, self::PASSWORD_CHARS);

        $this->atlas->beginTransaction();
        $new_player = $this->atlas->newRecord(Player::class, [
            'fortee_name' => $fortee_name,
            'display_name' => $display_name,
            'created_at' => $this->now->format('Y-m-d H:i:s'),
        ]);
        $this->atlas->insert($new_player);

        $new_password = $this->atlas->newRecord(Password::class, [
            'player_id' => $new_player->id,
            'hash' => password_hash($login_code, PASSWORD_DEFAULT),
            'created_at' => $this->now->format('Y-m-d H:i:s'),
        ]);
        $this->atlas->insert($new_password);

        assert(isset($new_player->id));
        $this->session->id = (int)$new_player->id;
        $this->session->accepted_terms = true;
        $this->session->place = '#PHPerGolf2020inCoconeriCountryClub';
        $this->session->login_code = $login_code;

        $this->atlas->commit();

        $response = $this->factory->createResponse(302)
            ->withHeader('Location', $this->route_gen->generate('index'));

        $this->oven->add('username', $fortee_name);
        $this->oven->add('id', $new_player->id);

        return $this->oven->appendTo($response);
    }
}
