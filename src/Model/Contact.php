<?php

namespace Tnt\Crm\Model;

use dry\orm\Model;
use Tnt\Crm\Contracts\SearchableInterface;
use Tnt\Crm\Model\Country;

/**
 * @property int|null $id
 * @property string $created
 * @property string $updated
 * @property int|null $organisation_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $language
 * @property string|null $function
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address_street
 * @property string|null $address_number
 * @property string|null $address_city
 * @property string|null $address_postal_code
 * @property int|null $country_id
 * @property string|null $note
 */
class Contact extends Model implements SearchableInterface
{
    const TABLE = 'crm_contact';

    static $special_fields = [
        "country" => Country::class,
    ];

    public function getSearchFields(): array
    {
        return ['first_name', 'last_name', 'email', 'phone'];
    }

    public function __toString()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
