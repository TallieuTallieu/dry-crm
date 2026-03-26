<?php

namespace Tnt\Crm\Model;

use dry\orm\Model;

/**
 * @property int $contact
 * @property int $relation
 * @property string|null $note
 * @property string $function
 */
class RelationContact extends Model
{
    const TABLE = 'crm_contact_relation';

    static $special_fields = [
        "contact" => Contact::class,
        "relation" => Relation::class,
    ];
}
