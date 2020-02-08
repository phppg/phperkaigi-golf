<?php
declare(strict_types=1);

namespace Playground\Web\DataSource\SQLite3\Password;

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

/**
 * @method PasswordTable getTable()
 * @method PasswordRelationships getRelationships()
 * @method PasswordRecord|null fetchRecord($primaryVal, array $with = [])
 * @method PasswordRecord|null fetchRecordBy(array $whereEquals, array $with = [])
 * @method PasswordRecord[] fetchRecords(array $primaryVals, array $with = [])
 * @method PasswordRecord[] fetchRecordsBy(array $whereEquals, array $with = [])
 * @method PasswordRecordSet fetchRecordSet(array $primaryVals, array $with = [])
 * @method PasswordRecordSet fetchRecordSetBy(array $whereEquals, array $with = [])
 * @method PasswordSelect select(array $whereEquals = [])
 * @method PasswordRecord newRecord(array $fields = [])
 * @method PasswordRecord[] newRecords(array $fieldSets)
 * @method PasswordRecordSet newRecordSet(array $records = [])
 * @method PasswordRecord turnRowIntoRecord(Row $row, array $with = [])
 * @method PasswordRecord[] turnRowsIntoRecords(array $rows, array $with = [])
 */
class Password extends Mapper
{
}
