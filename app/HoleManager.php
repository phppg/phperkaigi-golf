<?php

declare(strict_types=1);

namespace Playground\Web;

final class HoleManager
{
    /** @var array<string,Hole> */
    private array $holes = [];

    public function add(string $slug, Hole $hole): self
    {
        $this->holes[$slug] = $hole;

        return $this;
    }

    public function get(string $slug): Hole
    {
        return $this->holes[$slug];
    }

    public function has(string $slug): bool
    {
        return isset($this->holes[$slug]);
    }
}
