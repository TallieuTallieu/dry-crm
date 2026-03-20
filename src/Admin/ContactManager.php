<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\EnumEdit;
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
use Tnt\Crm\Enum\Language;
use Tnt\Crm\Model\Contact;

class ContactManager extends Manager
{
    public $edit;

    public function __construct(array $kwargs = [])
    {
        $model = Contact::class;
        $language_options = Language::enum();
        extract($kwargs, EXTR_IF_EXISTS);

        parent::__construct($model, [
            'icon' => Module::ICON_PEOPLE,
            'singular' => 'contact',
            'plural' => 'contacts',
        ]);

        $generalComponents = [
            Stack::horizontal([
                StringEdit::create('first_name')
                    ->set_label('First name')
                    ->set_required(),
                StringEdit::create('last_name')
                    ->set_label('Last name')
                    ->set_required(),
            ])->set_grid([4, 4]),
            // todo: this is in the pivot no?
            // StringEdit::create('function')
            //     ->set_label('Function'),
            EnumEdit::create('language')
                ->set_label("Language")
                ->set_options($language_options),
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
                ForeignKeyIndexPicker::create('country')
                    ->set_components([
                        StringView::create('name'),
                    ])
                    ->set_plural('countries')
                    ->set_label('Country'),
            ])->set_title('Address'),
            StringEdit::create('note')
                ->set_label('Note'),
        ];

        $this->actions[] = $create = new Create($generalComponents, [
            'popup' => true,
        ]);

        $this->actions[] = $this->edit = new Edit([
            Stack::horizontal([
                InlineManager::create(new OrganisationContactManager(new Contact()))
                    ->set_foreign_key('contact'),
                Stack::vertical([
                    ...$generalComponents
                ])->enable_background(),
            ])->set_grid([5, 2]),
        ]);

        $this->actions[] = $delete = new Delete();

        $this->header[] = $create->create_link('Add contact');

        $this->footer[] = new Pagination();

        $this->index = new Index([
            Stack::vertical([
                new StringView('first_name'),
                new StringView('last_name'),
            ])->set_header('Name'),
            new StringView('email')
                ->set_link(function ($row) {
                    return "mailto:$row->email";
                }),
            new StringView('phone'),
            $this->edit->create_link(),
            $delete->create_link(),
        ]);

        $this->index->sorter = new StaticSorter('last_name', StaticSorter::ASC);
    }
}
