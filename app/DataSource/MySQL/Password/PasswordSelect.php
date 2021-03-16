<?php

declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Password;

use Atlas\Mapper\MapperSelect;

/**
 * @method PasswordRecord|null fetchRecord()
 * @method PasswordRecord[] fetchRecords()
 * @method PasswordRecordSet fetchRecordSet()
 */
class PasswordSelect extends MapperSelect
{
}
