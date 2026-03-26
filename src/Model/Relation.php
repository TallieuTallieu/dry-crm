<?php

namespace Tnt\Crm\Model;

use dry\orm\Model;
use Tnt\Crm\Contracts\SearchableInterface;
use Tnt\Crm\Model\Country;

/**
 * @property int|null $id
 * @property string $created
 * @property string $updated
 * @property string $name
 * @property string|null $VAT
 * @property string|null $address_street
 * @property string|null $address_number
 * @property string|null $address_city
 * @property string|null $address_postal_code
 * @property int|null $country
 * @property string|null $website
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $note
 */
class Relation extends Model implements SearchableInterface
{
    const TABLE = 'crm_relation';

    static $special_fields = [
        "country" => Country::class
    ];

    //for backend index purposes
    public function get_address_street_and_number()
    {
        return "{$this->address_street} {$this->address_number}";
    }

    //for backend index purposes
    public function get_address_postal_code_and_country()
    {
        return "{$this->address_postal_code} {$this->country?->name}";
    }

    public function getSearchFields(): array
    {
        return [
            'name',
            'email',
            'phone',
            'website',
            'vat',
            'address_city',
            'address_street',
            'address_number',
        ];
    }

    public function __toString()
    {
        return $this->name;
    }
}
