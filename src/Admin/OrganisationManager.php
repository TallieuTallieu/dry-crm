<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\admin\Module;
use dry\orm\action\Create;
use dry\orm\action\Delete;
use dry\orm\action\Edit;
use dry\orm\component\ForeignKeyIndexPicker;
use dry\orm\component\InlineManager;
use dry\orm\component\Pagination;
use dry\orm\Index;
use dry\orm\Manager;
use dry\orm\sort\StaticSorter;
use Tnt\Crm\Model\Organisation;

class OrganisationManager extends Manager
{
    public $edit;

    public function __construct(array $kwargs = [])
    {
        $model = Organisation::class;
        extract($kwargs, EXTR_IF_EXISTS);

        parent::__construct($model, [
            'icon' => 'business_center',
            'singular' => 'organisation',
            'plural' => 'organisations',
        ]);

        $generalComponents = [
            StringEdit::create('name')
                ->set_label('Organisation Name')
                ->set_required(),
            StringEdit::create('VAT')
                ->set_label('VAT'),
            StringEdit::create('website')
                ->set_tooltip('An URL should always start with <strong>https://</strong>')
                ->set_label('Website'),
            StringEdit::create('email')
                ->set_label('Email'),
            StringEdit::create('phone')
                ->set_label('Phone'),
            Stack::vertical([
                Stack::horizontal([
                    StringEdit::create('address_street')
                        ->set_label('Street'),
                    StringEdit::create('address_number')
                        ->set_label('Number'),
                ])
                    ->set_grid([5, 3]),
                Stack::horizontal([
                    StringEdit::create('address_postal_code')
                        ->set_label('Postal code'),
                    StringEdit::create('address_city')
                        ->set_label('City'),
                ])
                    ->set_grid([3, 5]),
                ForeignKeyIndexPicker::create("country")
                    ->set_components([
                        StringView::create('name')
                    ])
                    ->set_plural('countries')
                    ->set_label('Country'),
            ])->set_title("Address"),
        ];

        $this->actions[] = $create = new Create($generalComponents, [
            'popup' => true,
        ]);

        $this->actions[] = $this->edit = new Edit([
            Stack::horizontal([
                InlineManager::create(new OrganisationContactManager(new Organisation()))
                    ->set_foreign_key('organisation'),
                Stack::vertical([
                    ...$generalComponents
                ])->set_title("Organisation Settings"),
            ])->set_grid([5, 2]),
        ]);

        $this->actions[] = $delete = new Delete();

        $this->header[] = $create->create_link('Add organisation');

        $this->footer[] = new Pagination();

        $this->index = new Index([
            Stack::vertical([
                new StringView('name'),
                new StringView('website')
                    ->set_link(function ($row) {
                        return $row->website;
                    }),
            ])->set_header('Organisation'),
            new StringView('VAT'),
            new StringView('email')
                ->set_link(function ($row) {
                    return "mailto:$row->email";
                }),
            new StringView('phone'),
            Stack::vertical([
                new StringView('address_street_and_number'),
                new StringView('address_postal_code_and_country'),
            ])->set_header('Address'),
            $this->edit->create_link(),
            $delete->create_link(),
        ]);

        $this->index->sorter = new StaticSorter('name', StaticSorter::ASC);
    }
}
