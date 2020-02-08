<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Playground\Web\DataSource\MySQL\SavedCode;

use Atlas\Table\Table;

/**
 * @method SavedCodeRow|null fetchRow($primaryVal)
 * @method SavedCodeRow[] fetchRows(array $primaryVals)
 * @method SavedCodeTableSelect select(array $whereEquals = [])
 * @method SavedCodeRow newRow(array $cols = [])
 * @method SavedCodeRow newSelectedRow(array $cols)
 */
class SavedCodeTable extends Table
{
    const DRIVER = 'mysql';

    const NAME = 'saved_codes';

    const COLUMNS = [
        'id' => [
            'name' => 'id',
            'type' => 'mediumint unsigned',
            'size' => 7,
            'scale' => 0,
            'notnull' => true,
            'default' => null,
            'autoinc' => true,
            'primary' => true,
            'options' => null,
        ],
        'player_id' => [
            'name' => 'player_id',
            'type' => 'mediumint unsigned',
            'size' => 7,
            'scale' => 0,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'code' => [
            'name' => 'code',
            'type' => 'mediumtext',
            'size' => 16777215,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'hole' => [
            'name' => 'hole',
            'type' => 'varchar',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'hash' => [
            'name' => 'hash',
            'type' => 'varbinary',
            'size' => 32,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
    ];

    const COLUMN_NAMES = [
        'id',
        'player_id',
        'code',
        'hole',
        'hash',
    ];

    const COLUMN_DEFAULTS = [
        'id' => null,
        'player_id' => null,
        'code' => null,
        'hole' => null,
        'hash' => null,
    ];

    const PRIMARY_KEY = [
        'id',
    ];

    const AUTOINC_COLUMN = 'id';

    const AUTOINC_SEQUENCE = null;
}