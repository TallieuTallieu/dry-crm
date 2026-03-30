<?php

namespace Tnt\Crm\Model;

use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\orm\component\ForeignKeyIndexPicker;
use dry\orm\Model;

/**
 * @property int|null $id
 * @property string $name
 */
class Country extends Model
{
    const TABLE = 'crm_country';

    static $special_fields = [];

    public static function addressComponents(): Stack
    {
        return Stack::vertical([
            Stack::horizontal([
                StringEdit::create('address_street')->set_label('Street'),
                StringEdit::create('address_number')->set_label('Number'),
            ])->set_grid([5, 3]),
            Stack::horizontal([
                StringEdit::create('address_postal_code')->set_label('Postal code'),
                StringEdit::create('address_city')->set_label('City'),
                StringEdit::create('address_box_number')->set_label('Box Number'),
            ])->set_grid([3, 5]),
            ForeignKeyIndexPicker::create('country')
                ->set_components([StringView::create('name')])
                ->set_plural('countries')
                ->set_label('Country'),
        ])->set_title('Address');
    }

    public static function enum(): array
    {
        return array_map(function ($row) {
            return [$row->id, ucwords($row->name)];
        }, self::all()->to_array());
    }

    public function __toString()
    {
        return ucwords($this->name);
    }
}
