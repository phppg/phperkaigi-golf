<?php

declare(strict_types=1);

namespace Playground\Web;

use JsonSerializable;
use stdClass;

final class Session implements JsonSerializable
{
    public bool $accepted_terms;
    public int $id;
    public string $name;

    private function __construct()
    {
        // noop
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array{id?:int,name?:string}
     */
    public static function fromArray(array $data): self
    {
        $session = new self();

        $session->accepted_terms = $data['accepted_terms'] ?? false;

        if (isset($data['id'])) {
            $session->id = $data['id'];
        }

        if (isset($data['name'])) {
            $session->name = $data['name'];
        }

        return $session;
    }

    public function isAcceptedTerms(): bool
    {
        return $this->accepted_terms;
    }

    public function isLoggedIn(): bool
    {
        return isset($this->id);
    }

    public function jsonSerialize(): array
    {
        $data = [
            'accepted_terms' => $this->accepted_terms,
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        if (isset($this->name)) {
            $data['name'] = $this->name;
        }

        return $data;
    }
}
