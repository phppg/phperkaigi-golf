<?php

declare(strict_types=1);

namespace Playground\Web;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinterAbstract as PrettyPrinter;
use Playground\Code\ParsedCode;
use Playground\Code\SourceCode;

final class ParsedCodeFactory
{
    private PrettyPrinter $pprinter;
    private ParserFactory $factory;

    public function __construct(PrettyPrinter $pprinter, ParserFactory $factory)
    {
        $this->pprinter = $pprinter;
        $this->factory = $factory;
    }

    public function create(string $source): ParsedCode
    {
        return new ParsedCode(
            $this->pprinter,
            $this->factory,
            new SourceCode($source)
        );
    }
}
