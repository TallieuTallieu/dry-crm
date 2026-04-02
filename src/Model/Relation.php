<?php

namespace Tnt\Crm\Model;

use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\orm\component\ForeignKeyView;
use dry\orm\Model;
use dry\orm\relationship\HasMany;
use dry\orm\relationship\ManyToMany;
use Tnt\Crm\Contracts\PivotReferenceInterface;
use dry\orm\sort\StaticSorter;
use Tnt\Crm\Enum\ContactMode;
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
class Relation extends Model implements PivotReferenceInterface
{
    const TABLE = 'crm_relation';

    public static ContactMode $contactMode = ContactMode::Pivot;
    public static string $sortField = 'last_name';
    public static int $sortDirection = StaticSorter::ASC;
    public static int $paginationAmount = 50;
    public static bool $managerEditable = true;
    public static bool $managerDeletable = true;
    public static array $searchFields = [
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

    static $special_fields = [
        "country" => Country::class,
    ];

    //for backend index purposes
    public function get_address_street_and_number()
    {
        return "{$this->address_street} {$this->address_number}";
    }

    //for backend index purposes
    public function get_address_postal_code_and_city()
    {
        return "{$this->address_postal_code} {$this->address_city}";
    }

    public static function getIndexComponents(): array
    {
        return [
            StringView::create('first_name'),
            StringView::create('last_name'),
            Stack::vertical([
                StringView::create('organisation_name'),
                StringView::create('website')
                    ->set_link(function ($row) {
                        return $row->website;
                    }),
            ])->set_header('Organisation'),
            StringView::create('vat_number'),
            StringView::create('email')
                ->set_link(function ($row) {
                    return "mailto:$row->email";
                }),
            StringView::create('phone'),
            Stack::vertical([
                StringView::create('address_street_and_number'),
                StringView::create('address_postal_code_and_city'),
                ForeignKeyView::create('country'),
            ])->set_header('Address'),
        ];
    }

    public static function getCreateComponents(): array
    {
        return [
            StringEdit::create('first_name')
                ->set_label('First Name'),
            StringEdit::create('last_name')
                ->set_label('Last Name'),
            StringEdit::create('organisation_name')
                ->set_label('Organisation Name'),
            StringEdit::create('vat_number')
                ->set_label('VAT Number'),
            StringEdit::create('website')
                ->set_tooltip('An URL should always start with <strong>https://</strong>')
                ->set_label('Website'),
            StringEdit::create('email')
                ->set_label('Email'),
            StringEdit::create('phone')
                ->set_label('Phone'),
            Country::addressComponents(),
        ];
    }

    public static function getEditComponents(): array
    {
        return static::getCreateComponents();
    }

    public static function getIndexActions(): array
    {
        return [];
    }

    public function getContacts(ContactMode $contact_mode = ContactMode::Pivot): HasMany|ManyToMany
    {
        if ($contact_mode === ContactMode::Direct) {
            return $this->has_many(Contact::class, 'relation');
        }

        return $this->belongs_to_many(RelationContact::class, 'relation', 'contact');
    }

    public function getPivotTitle(): string { return 'contact'; }
    public function getPivotForeignKey(): string { return 'contact'; }
    public function getPivotDisplayColumn(): string { return 'first_name'; }
    public function getPivotIndexName(): string { return 'Contact'; }
    public function getPivotReferenceModel(): Model { return new Contact(); }

    public function __toString()
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->last_name])))
            ?: ($this->organisation_name ?? '');
    }
}
