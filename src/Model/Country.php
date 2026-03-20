<?php

namespace Tnt\Crm\Model;

use dry\orm\Model;

/**
 * @property int|null $id
 * @property string $name
 */
class Country extends Model
{
    const TABLE = 'crm_country';

    static $special_fields = [];

    public static function enum(): array
    {
        return array_map(function ($row) {
            return [$row->id, $row->name];
        }, self::all()->to_array());
    }

    public function __toString()
    {
        return $this->name;
    }
}
