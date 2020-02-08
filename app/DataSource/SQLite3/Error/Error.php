<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\SQLite3\Error;

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

/**
 * @method ErrorTable getTable()
 * @method ErrorRelationships getRelationships()
 * @method ErrorRecord|null fetchRecord($primaryVal, array $with = [])
 * @method ErrorRecord|null fetchRecordBy(array $whereEquals, array $with = [])
 * @method ErrorRecord[] fetchRecords(array $primaryVals, array $with = [])
 * @method ErrorRecord[] fetchRecordsBy(array $whereEquals, array $with = [])
 * @method ErrorRecordSet fetchRecordSet(array $primaryVals, array $with = [])
 * @method ErrorRecordSet fetchRecordSetBy(array $whereEquals, array $with = [])
 * @method ErrorSelect select(array $whereEquals = [])
 * @method ErrorRecord newRecord(array $fields = [])
 * @method ErrorRecord[] newRecords(array $fieldSets)
 * @method ErrorRecordSet newRecordSet(array $records = [])
 * @method ErrorRecord turnRowIntoRecord(Row $row, array $with = [])
 * @method ErrorRecord[] turnRowsIntoRecords(array $rows, array $with = [])
 */
class Error extends Mapper
{
}
