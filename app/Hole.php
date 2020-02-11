<?php

declare(strict_types=1);

namespace Playground\Web;

use Closure;
use Playground\Code;
use Playground\File;

interface Hole
{
    public function getTitle(): string;

    public function getDefaultCode(): string;

    public function getDescription(): string;

    public function getRewordToken(): string;

    public function getSlug(): string;

    public function getStdin(): ?string;

    public function isCuppedIn(Code $code, string $output): bool;
}
