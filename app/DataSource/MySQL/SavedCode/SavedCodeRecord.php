<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\SavedCode;

use Atlas\Mapper\Record;

/**
 * @method SavedCodeRow getRow()
 */
class SavedCodeRecord extends Record
{
    use SavedCodeFields;
}
