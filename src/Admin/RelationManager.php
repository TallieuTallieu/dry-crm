<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\admin\component\TabbedContent;
use dry\orm\action\Create;
use dry\orm\action\Delete;
use dry\orm\action\Edit;
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
use Tnt\Crm\Model\Relation;

class RelationManager extends Manager
{
    public $edit;

    public function __construct(array $kwargs = [])
    {
        $model = Relation::class;
        $contact_model = Contact::class;
        $extra_tabs = [];
        $extra_filters = [];
        $extra_header_actions = [];
        $general_components = null;
        $sort_field = 'name';
        $sort_direction = StaticSorter::ASC;
        extract($kwargs, EXTR_IF_EXISTS);

        parent::__construct($model, [
            'icon' => 'business_center',
            'singular' => 'relation',
            'plural' => 'relations',
        ]);

        $generalComponents = $general_components ?? [
            StringEdit::create('name')
                ->set_label('Relation Name')
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
            Country::addressComponents(),
        ];

        $this->actions[] = $create = new Create($generalComponents, [
            'popup' => true,
        ]);

        $editContent = TabbedContent::create()
            ->add_tab("Contacts", [
                InlineManager::create(new RelationContactManager(new $model(), ['reference_model' => new $contact_model()]))
                    ->set_foreign_key('relation'),
            ]);

        foreach ($extra_tabs as $label => $components) {
            $editContent->add_tab($label, $components);
        }

        $this->actions[] = $this->edit = new Edit([
            Stack::horizontal([
                $editContent,
                Stack::vertical([
                    Stack::vertical([
                        ...$generalComponents
                    ])->set_title("Relation Settings"),
                    CreateNote::getNoteComponent()
                ]),
            ])->set_grid([5, 2]),
        ]);

        ['create' => $create_note, 'edit' => $edit_note] = CreateNote::register($this);

        $this->actions[] = $delete = new Delete();

        $this->header[] = new Search();
        $this->header[] = $create->create_link('Add relation');

        foreach ($extra_header_actions as $headerItem) {
            $this->header[] = $headerItem;
        }

        $this->footer[] = new Pagination();

        $this->index = new Index([
            Stack::vertical([
                StringView::create('name'),
                StringView::create('website')
                    ->set_link(function ($row) {
                        return $row->website;
                    }),
            ])->set_header('Relation'),
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

        foreach ($extra_filters as $filter) {
            $this->index->filters[] = $filter;
        }

        $this->index->sorter = new StaticSorter($sort_field, $sort_direction);
        $this->index->searcher = new LikeSearcher((new $model())->getSearchFields());
    }
}
