<?php

declare(strict_types=1);

namespace Playground\Web\Hole;

use Playground\Code;
use Playground\Web\Hole;

final class FizzBuzz implements Hole
{
    private const SLUG = 'fizzbuzz';
    private const TITLE = 'FizzBuzz問題';
    private const DESCRIPTION = <<<'MD'
        「FizzBuzz」とは、1から数え上げるパーティゲームの一種です。ただし、その数が『3の倍数』のときは数の代わりに“Fizz”、『5の倍数』のときは“Buzz”、そして『15の倍数』のときは“FizzBuzz”と言わなければいけません。

        今回の問題では1から100までの数字を数え上げて、それぞれの行を改行区切りで出力(`echo`)してください。余分な出力を加えてはいけません。
        MD;
    private const EXPECTED_OUTPUT = <<<'OUTPUT'
1
2
Fizz
4
Buzz
Fizz
7
8
Fizz
Buzz
11
Fizz
13
14
FizzBuzz
16
17
Fizz
19
Buzz
Fizz
22
23
Fizz
Buzz
26
Fizz
28
29
FizzBuzz
31
32
Fizz
34
Buzz
Fizz
37
38
Fizz
Buzz
41
Fizz
43
44
FizzBuzz
46
47
Fizz
49
Buzz
Fizz
52
53
Fizz
Buzz
56
Fizz
58
59
FizzBuzz
61
62
Fizz
64
Buzz
Fizz
67
68
Fizz
Buzz
71
Fizz
73
74
FizzBuzz
76
77
Fizz
79
Buzz
Fizz
82
83
Fizz
Buzz
86
Fizz
88
89
FizzBuzz
91
92
Fizz
94
Buzz
Fizz
97
98
Fizz
Buzz
OUTPUT;
    private const REWORD_TOKEN = '#FizzBuzzFizzBuzzFizzBuzz';

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

    public function getRewordToken(): string
    {
        return self::REWORD_TOKEN;
    }

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function getStdin(): ?string
    {
        return null;
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
