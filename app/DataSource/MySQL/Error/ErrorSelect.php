<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Error;

use Atlas\Mapper\MapperSelect;

/**
 * @method ErrorRecord|null fetchRecord()
 * @method ErrorRecord[] fetchRecords()
 * @method ErrorRecordSet fetchRecordSet()
 */
class ErrorSelect extends MapperSelect
{
}
