<?php

declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\Player;

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

/**
 * @method PlayerTable getTable()
 * @method PlayerRelationships getRelationships()
 * @method PlayerRecord|null fetchRecord($primaryVal, array $with = [])
 * @method PlayerRecord|null fetchRecordBy(array $whereEquals, array $with = [])
 * @method PlayerRecord[] fetchRecords(array $primaryVals, array $with = [])
 * @method PlayerRecord[] fetchRecordsBy(array $whereEquals, array $with = [])
 * @method PlayerRecordSet fetchRecordSet(array $primaryVals, array $with = [])
 * @method PlayerRecordSet fetchRecordSetBy(array $whereEquals, array $with = [])
 * @method PlayerSelect select(array $whereEquals = [])
 * @method PlayerRecord newRecord(array $fields = [])
 * @method PlayerRecord[] newRecords(array $fieldSets)
 * @method PlayerRecordSet newRecordSet(array $records = [])
 * @method PlayerRecord turnRowIntoRecord(Row $row, array $with = [])
 * @method PlayerRecord[] turnRowsIntoRecords(array $rows, array $with = [])
 */
class Player extends Mapper
{
}
