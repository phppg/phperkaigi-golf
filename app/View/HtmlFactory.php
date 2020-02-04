<?php

namespace Playground\Web\View;

use DI\Annotation\Inject;
use Twig\Environment as Twig;

final class HtmlFactory
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param array<string,mixed> $params
     */
    public function __invoke(string $name, array $params): string
    {
        return $this->twig->render("{$name}.html.twig", $params);
    }
}
