<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\SQLite3\Error;

use Atlas\Mapper\Record;

/**
 * @method ErrorRow getRow()
 */
class ErrorRecord extends Record
{
    use ErrorFields;
}
