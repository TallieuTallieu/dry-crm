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

    public function __toString()
    {
        return $this->name;
    }
}
