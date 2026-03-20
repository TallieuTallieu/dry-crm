<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\orm\action\Create;
use dry\orm\action\Delete;
use dry\orm\action\Edit;
use dry\orm\component\ForeignKeyIndexPicker;
use dry\orm\component\InlineManager;
use dry\orm\component\Pagination;
use dry\orm\component\Search;
use dry\orm\filter\EnumFilter;
use dry\orm\Index;
use dry\orm\Manager;
use dry\orm\search\LikeSearcher;
use dry\orm\sort\StaticSorter;
use Tnt\Crm\Admin\Actions\CreateNote;
use Tnt\Crm\Model\Contact;
use Tnt\Crm\Model\Country;
use Tnt\Crm\Model\Organisation;

class OrganisationManager extends Manager
{
    public $edit;

    public function __construct(array $kwargs = [])
    {
        $model = Organisation::class;
        $contact_model = Contact::class;
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
                InlineManager::create(new OrganisationContactManager(new $model(), ['reference_model' => new $contact_model()]))
                    ->set_foreign_key('organisation'),
                Stack::vertical([
                    Stack::vertical([
                        ...$generalComponents
                    ])->set_title("Organisation Settings"),
                    CreateNote::getNoteComponent()
                ]),
            ])->set_grid([5, 2]),
        ]);

        $this->actions[] = $create_note = new CreateNote();
        $this->actions[] = $edit_note = new CreateNote(true);

        $this->actions[] = $delete = new Delete();

        $this->header[] = new Search();
        $this->header[] = $create->create_link('Add organisation');

        $this->footer[] = new Pagination();

        $this->index = new Index([
            Stack::vertical([
                StringView::create('name'),
                StringView::create('website')
                    ->set_link(function ($row) {
                        return $row->website;
                    }),
            ])->set_header('Organisation'),
            StringView::create('VAT'),
            StringView::create('email')
                ->set_link(function ($row) {
                    return "mailto:$row->email";
                }),
            StringView::create('phone'),
            Stack::vertical([
                StringView::create('address_street_and_number'),
                StringView::create('address_postal_code_and_country'),
            ])->set_header('Address'),
            CreateNote::renderTableActions($create_note, $edit_note),
            $this->edit->create_link(),
            $delete->create_link(),
        ]);

        $this->index->filters[] = new EnumFilter("country", Country::enum(), ["title" => "Countries"]);

        $this->index->sorter = new StaticSorter('name', StaticSorter::ASC);
        $this->index->searcher = new LikeSearcher((new $model())->getSearchFields());
    }
}
