<?php

declare(strict_types=1);

namespace Playground\Web\Http;

use Bag2\Cookie\Oven;
use Exception;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JoseSerializer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use function Safe\json_decode;
use function Safe\json_encode;

final class CookieJwtSession implements SessionStorage
{
    /** @var array<string,mixed> */
    private array $data = [];
    private string $cookie_name;
    private JWK $jwk;
    private JWSBuilder $jws_builder;
    private JWSLoader $jws_loader;
    private JWSVerifier $jws_verifier;
    private Oven $oven;
    private JoseSerializer $serializer;

    public function __construct(
        JoseSerializer $serializer,
        JWK $jwk,
        JWSBuilder $jws_builder,
        JWSLoader $jws_loader,
        JWSVerifier $jws_verifier,
        Oven $oven,
        string $cookie_name
    ) {
        $this->cookie_name = $cookie_name;
        $this->jwk = $jwk;
        $this->jws_builder = $jws_builder;
        $this->jws_loader = $jws_loader;
        $this->jws_verifier = $jws_verifier;
        $this->oven = $oven;
        $this->serializer = $serializer;
    }

    private function fetchJws(string $token): array
    {
        try {
            $jws = $this->jws_loader->loadAndVerifyWithKey($token, $this->jwk, $_);
        } catch (Exception $e) {
            if ($e->getMessage() !== 'Unable to load and verify the token.') {
                throw $e;
            }

            return [];
        }

        return json_decode($jws->getPayload(), true);
    }

    public function fromRequest(ServerRequest $request): self
    {
        $cookies = $request->getCookieParams();

        if (isset($cookies[$this->cookie_name])) {
            $this->data = $this->fetchJws($cookies[$this->cookie_name]);
        }

        return $this;
    }

    public function writeTo(Response $response): Response
    {
        $payload = json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $jws = $this->jws_builder
            ->create()
            ->withPayload($payload)
            ->addSignature($this->jwk, ['alg' => 'HS256'])
            ->build();
        $this->oven->add($this->cookie_name, $this->serializer->serialize($jws, 0));

        return $this->oven->appendTo($response);
    }
}
