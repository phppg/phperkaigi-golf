<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\SavedCode;

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

/**
 * @method SavedCodeTable getTable()
 * @method SavedCodeRelationships getRelationships()
 * @method SavedCodeRecord|null fetchRecord($primaryVal, array $with = [])
 * @method SavedCodeRecord|null fetchRecordBy(array $whereEquals, array $with = [])
 * @method SavedCodeRecord[] fetchRecords(array $primaryVals, array $with = [])
 * @method SavedCodeRecord[] fetchRecordsBy(array $whereEquals, array $with = [])
 * @method SavedCodeRecordSet fetchRecordSet(array $primaryVals, array $with = [])
 * @method SavedCodeRecordSet fetchRecordSetBy(array $whereEquals, array $with = [])
 * @method SavedCodeSelect select(array $whereEquals = [])
 * @method SavedCodeRecord newRecord(array $fields = [])
 * @method SavedCodeRecord[] newRecords(array $fieldSets)
 * @method SavedCodeRecordSet newRecordSet(array $records = [])
 * @method SavedCodeRecord turnRowIntoRecord(Row $row, array $with = [])
 * @method SavedCodeRecord[] turnRowsIntoRecords(array $rows, array $with = [])
 */
class SavedCode extends Mapper
{
}
