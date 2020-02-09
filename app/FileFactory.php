<?php

declare(strict_types=1);

namespace Playground\Web;

use Closure;
use Playground\File;

final class FileFactory
{
    private Closure $filename_generator;

    /**
     * @phan-var class-string<File>
     * @phpstan-var class-string<File>
     */
    private string $class;

    /**
     * @phpstan-var Closure(string $prefix): string
     */
    private Closure $filename_gen;

    /**
     * @phan-param class-string<File> $class
     * @phpstan-param class-string<File> $class
     * @phpstan-param Closure(string $prefix): string $filename_gen
     */
    public function __construct(string $class, Closure $filename_gen)
    {
        $this->class = $class;
        $this->filename_gen = $filename_gen;
    }

    public function create(string $prefix): File
    {
        $class = $this->class;

        return new $class(($this->filename_gen)($prefix));
    }
}
