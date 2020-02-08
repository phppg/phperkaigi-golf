<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\SQLite3\Error;

use Atlas\Table\TableSelect;

/**
 * @method ErrorRow|null fetchRow()
 * @method ErrorRow[] fetchRows()
 */
class ErrorTableSelect extends TableSelect
{
}
