<?php

declare(strict_types=1);

namespace Playground\Web\Hole;

use Playground\Code;
use Playground\Web\Hole;

final class ArrayConvert implements Hole
{
    private const SLUG = 'arrayconvert';
    private const TITLE = '配列を展開せよ';
    private const DESCRIPTION = <<<'MD'
        あるサイトにのAPIにリクエストを送ると、結果がとても変な形式のJSONで帰ってきてしまいます。

        ```
        {"foo": "1", "bar[0]": "A", "bar[1]": "B", "buz[0][0]": "00", "buz[0][1]": "01"}
        ```

        あなたの仕事はこの独特なフォーマットのレスポンスを常識的に綺麗な形状の配列に直すことです。

        ```
        {"foo": "1", "bar": ["A", "B"], "buz": [["00", "01"]]}
        ```

        結果の配列は `json_encode()` で変換して出力してください。
        MD;
    private const EXPECTED_OUTPUT = '{"foo":"1","bar":["A","B"],"buz":[["00","01"]]}';
    private const REWORD_TOKEN = '#参照マスターへの道';
    private const STDIN_STR = '{"foo":"1","bar[0]":"A","bar[1]":"B","buz[0][0]":"00","buz[0][1]":"01"}';

    public function getDefaultCode(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

$input = json_decode(stream_get_contents(STDIN), true);

$converter = function (array $in) {
    // ...
};

// 最終的にこの変換結果が出力されるようにしてください。
// コード内の好きな位置に関数やクラスを定義しても構いません。
echo json_encode($converter($input));
PHP;
    }

    public function getStdin(): ?string
    {
        return self::STDIN_STR;
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

    public function getTitle(): string
    {
        return self::TITLE;
    }

    public function isCuppedIn(Code $code, string $output): bool
    {
        return  self::EXPECTED_OUTPUT === strtr($output, [' ' => '']);
    }
}
