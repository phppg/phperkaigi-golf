<?php

declare(strict_types=1);

namespace Playground\Web\Hole;

use Playground\Code;
use Playground\Code\ParsedCode;
use Playground\Web\Hole;

final class HelloWorld implements Hole
{
    private const SLUG = 'helloworld';
    private const TITLE = 'Hello, world!';
    private const DESCRIPTION = <<<MD
        **"Hello, world!"** はプログラミング入門の課題としてもっともポピュラーなものです。

        [Hello World]にはさまざまな表記のバリエーションがありますが、『`H`大文字, `w`小文字、`, `あり、`!`あり』のフォーマットで出力してください。

        [Hello World]: https://ja.wikipedia.org/wiki/Hello_world
        MD;
    private const EXPECTED_OUTPUT = 'Hello, world!';

    public function getDefaultCode(): string
    {
       return <<<'PHP'
           <?php

           declare(strict_types=1);


           PHP;
    }


    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function getTitle(): string
    {
        return self::TITLE;
    }

    public function isCuppedIn(Code $code, string $output): bool
    {
        return  self::EXPECTED_OUTPUT === rtrim($output);
    }
}
