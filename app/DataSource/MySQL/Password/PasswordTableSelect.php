<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Password;

use Atlas\Table\TableSelect;

/**
 * @method PasswordRow|null fetchRow()
 * @method PasswordRow[] fetchRows()
 */
class PasswordTableSelect extends TableSelect
{
}
