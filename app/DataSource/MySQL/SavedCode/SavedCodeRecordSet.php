<?php

declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\SavedCode;

use Atlas\Mapper\RecordSet;

/**
 * @method SavedCodeRecord offsetGet($offset)
 * @method SavedCodeRecord appendNew(array $fields = [])
 * @method SavedCodeRecord|null getOneBy(array $whereEquals)
 * @method SavedCodeRecordSet getAllBy(array $whereEquals)
 * @method SavedCodeRecord|null detachOneBy(array $whereEquals)
 * @method SavedCodeRecordSet detachAllBy(array $whereEquals)
 * @method SavedCodeRecordSet detachAll()
 * @method SavedCodeRecordSet detachDeleted()
 */
class SavedCodeRecordSet extends RecordSet
{
}
