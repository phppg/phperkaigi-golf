<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Password;

use Atlas\Mapper\Record;

/**
 * @method PasswordRow getRow()
 */
class PasswordRecord extends Record
{
    use PasswordFields;
}
