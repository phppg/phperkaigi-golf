<?php

declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Error;

use Atlas\Table\TableSelect;

/**
 * @method ErrorRow|null fetchRow()
 * @method ErrorRow[] fetchRows()
 */
class ErrorTableSelect extends TableSelect
{
}
