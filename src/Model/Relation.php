<?php

namespace Tnt\Crm\Model;

use dry\orm\Model;
use Tnt\Crm\Contracts\SearchableInterface;
use Tnt\Crm\Model\Country;

/**
 * @property int|null $id
 * @property string $created
 * @property string $updated
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $organisation_name
 * @property string|null $vat_number
 * @property string|null $address_street
 * @property string|null $address_number
 * @property string|null $address_box_number
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
            'first_name',
            'last_name',
            'email',
            'phone',
            'website',
            'vat_number',
            'address_city',
            'address_street',
            'address_number',
        ];
    }

    public function __toString()
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->last_name])))
            ?: ($this->organisation_name ?? '');
    }
}
