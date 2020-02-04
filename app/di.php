<?php

declare(strict_types=1);

namespace Playground\Web;

use DI;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;

$builder = new DI\ContainerBuilder();
$builder->enableCompilation(__DIR__ . '/../cache');
$builder->writeProxiesToFile(true, __DIR__ . '/../cache/proxies');
$builder->addDefinitions([
]);

return $builder->build();
