<?php

declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Player;

use Atlas\Mapper\RecordSet;

/**
 * @method PlayerRecord offsetGet($offset)
 * @method PlayerRecord appendNew(array $fields = [])
 * @method PlayerRecord|null getOneBy(array $whereEquals)
 * @method PlayerRecordSet getAllBy(array $whereEquals)
 * @method PlayerRecord|null detachOneBy(array $whereEquals)
 * @method PlayerRecordSet detachAllBy(array $whereEquals)
 * @method PlayerRecordSet detachAll()
 * @method PlayerRecordSet detachDeleted()
 */
class PlayerRecordSet extends RecordSet
{
}
