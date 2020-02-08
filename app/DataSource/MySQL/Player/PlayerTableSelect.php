<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Player;

use Atlas\Table\TableSelect;

/**
 * @method PlayerRow|null fetchRow()
 * @method PlayerRow[] fetchRows()
 */
class PlayerTableSelect extends TableSelect
{
}
