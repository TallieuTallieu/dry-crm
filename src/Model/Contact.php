<?php

namespace Tnt\Crm\Model;

use dry\admin\component\DateView;
use dry\admin\component\EnumView;
use dry\admin\component\Stack;
use dry\admin\component\StringView;
use dry\orm\Model;
use dry\orm\sort\StaticSorter;
use Tnt\Crm\Contracts\PivotReferenceInterface;
use Tnt\Crm\Model\Country;

/**
 * @property int|null $id
 * @property string $created
 * @property string $updated
 * @property int|null $relation_id
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
 * @property int|null $country
 * @property int|null $relation
 * @property string|null $note
 */
class Contact extends Model implements PivotReferenceInterface
{
    const TABLE = 'crm_contact';

    public static bool $languageEnabled = true;
    public static string $sortField = 'first_name';
    public static int $sortDirection = StaticSorter::ASC;
    public static array $searchFields = ['first_name', 'last_name', 'email', 'phone'];
    public static bool $showCreatedInIndex = true;
    public static bool $showUpdatedInIndex = true;
    public static bool $clickToEdit = false;

    static $special_fields = [
        "country" => Country::class,
        "relation" => Relation::class
    ];

    public function getPivotTitle(): string { return 'relation'; }
    public function getPivotForeignKey(): string { return 'relation'; }
    public function getPivotDisplayColumn(): string { return 'first_name'; }
    public function getPivotIndexName(): string { return 'Relation'; }
    public function getPivotReferenceModel(): Model { return new Relation(); }

    public static function getIndexComponents(array $language_options = []): array
    {
        return [
            Stack::vertical([
                StringView::create('first_name'),
                StringView::create('last_name'),
            ])->set_header('Name'),
            StringView::create('email')
                ->set_link(function ($row) {
                    return "mailto:$row->email";
                }),
            StringView::create('phone'),
            ...(static::$languageEnabled ? [EnumView::create('language')
                ->set_options($language_options)] : []),
            ...(static::$showCreatedInIndex ? [DateView::create("created")->set_format("d/m/Y H:i")] : []),
            ...(static::$showUpdatedInIndex ? [DateView::create("updated")->set_format("d/m/Y H:i")] : []),
        ];
    }

    public static function getExtraTabs(): array
    {
        return [];
    }

    public function __toString()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
