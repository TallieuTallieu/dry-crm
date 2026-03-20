<?php

namespace Tnt\Crm\Model;

use dry\orm\Model;

/**
 * @property int $contact
 * @property int $organisation
 * @property string|null $note
 * @property string $function
 */
class OrganisationContact extends Model
{
    const TABLE = 'crm_contact_organisation';

    static $special_fields = [
        "contact" => Contact::class,
        "organisation" => Organisation::class,
    ];
}
