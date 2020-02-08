<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\SQLite3\Password;

use Atlas\Mapper\RecordSet;

/**
 * @method PasswordRecord offsetGet($offset)
 * @method PasswordRecord appendNew(array $fields = [])
 * @method PasswordRecord|null getOneBy(array $whereEquals)
 * @method PasswordRecordSet getAllBy(array $whereEquals)
 * @method PasswordRecord|null detachOneBy(array $whereEquals)
 * @method PasswordRecordSet detachAllBy(array $whereEquals)
 * @method PasswordRecordSet detachAll()
 * @method PasswordRecordSet detachDeleted()
 */
class PasswordRecordSet extends RecordSet
{
}
