<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Aura\Router\Generator as RouteGenerator;
use Cake\Chronos\Chronos;
use PhpParser\Error as ParserError;
use Playground\Invoker;
use Playground\Statistics;
use Playground\Code\ParsedCode;
use Playground\Web\DataSource\MySQL\Player\Player;
use Playground\Web\DataSource\MySQL\Password\Password;
use Playground\Web\ParsedCodeFactory;
use Playground\Web\Session;
use Playground\Web\View;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use RandomLib\Generator as RandomGenerator;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use const PASSWORD_DEFAULT;
use function array_values;
use function array_unique;

final class SandboxAction
{
    private const REASON_SYNTAX_ERROR = 'syntax_error';
    private const REASON_TIMEOUT = 'timeout';

    private Invoker $invoker;
    private ParsedCodeFactory $parsed_code_factory;
    private ResponseFactory $factory;
    private RouteGenerator $route_gen;
    private RandomGenerator $rand_gen;
    private Session $session;
    private StreamFactory $stream;
    private View\HtmlFactory $html;

    public function __construct(
        Invoker $invoker,
        ParsedCodeFactory $parsed_code_factory,
        ResponseFactory $factory,
        RouteGenerator $route_gen,
        RandomGenerator $rand_gen,
        Session $session,
        StreamFactory $stream,
        View\HtmlFactory $html
    ) {
        $this->invoker = $invoker;
        $this->parsed_code_factory = $parsed_code_factory;
        $this->factory = $factory;
        $this->rand_gen = $rand_gen;
        $this->route_gen = $route_gen;
        $this->session = $session;
        $this->stream = $stream;
        $this->html = $html;
    }

    public function __invoke(ServerRequest $request): HttpResponse
    {
        /** @var array{code?:string} */
        $params = $request->getParsedBody();

        $original = $params['code'] ?? '';
        $errors = [
            self::REASON_SYNTAX_ERROR => false,
            self::REASON_TIMEOUT => false,
        ];

        [$code, $stats] = $this->parseAndStats($original);

        if ($code === null) {
            $errors[self::REASON_SYNTAX_ERROR] = true;
        }

        [$output, $error_output, $errors] = $this->execProcess($code, $errors);

        $no_error = array_values(array_unique($errors)) === [false];

        return $this->factory->createResponse(200)
            ->withBody($this->stream->createStream(($this->html)('sandbox', [
                'code' => $original,
                'errors' => $no_error ? null : $errors,
                'error_output' => $error_output,
                'output' => $output,
                'pretty_print' => (string)$code,
                'stats' => $stats,
            ])));
    }

    /**
     * @return array{0:?Code,1:?Statistics}
     */
    private function parseAndStats(string $source_code): array
    {
        try {
            $code = $this->parsed_code_factory->create($source_code);
            $stats = Statistics::fromCode($code);
        } catch (ParserError $e) {
            return [null, null];
        }

        return [$code, $stats];
    }

    /**
     * @param array{syntax_error:bool,timeout:bool} $errors
     * @return array{0:?string,1:?string,2:array{syntax_error:bool,timeout:bool}}
     */
    private function execProcess(ParsedCode $code, array $errors): array
    {
        $output = null;
        $error_output = null;

        try {
            $proc = ($this->invoker)->invoke($code);
        } catch (ProcessTimedOutException $e) {
            $proc = $e->getProcess();
            $errors[self::REASON_TIMEOUT] = true;
        } catch (ProcessFailedException $e) {
            $proc = $e->getProcess();
        } finally {
            $output = $proc->getOutput();
            $error_output = $proc->getErrorOutput();
        }

        return [$output, $error_output, $errors];
    }
}
