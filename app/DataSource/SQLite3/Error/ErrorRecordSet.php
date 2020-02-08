<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\SQLite3\Error;

use Atlas\Mapper\RecordSet;

/**
 * @method ErrorRecord offsetGet($offset)
 * @method ErrorRecord appendNew(array $fields = [])
 * @method ErrorRecord|null getOneBy(array $whereEquals)
 * @method ErrorRecordSet getAllBy(array $whereEquals)
 * @method ErrorRecord|null detachOneBy(array $whereEquals)
 * @method ErrorRecordSet detachAllBy(array $whereEquals)
 * @method ErrorRecordSet detachAll()
 * @method ErrorRecordSet detachDeleted()
 */
class ErrorRecordSet extends RecordSet
{
}
