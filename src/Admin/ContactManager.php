<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\DateView;
use dry\admin\component\EnumEdit;
use dry\admin\component\EnumView;
use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\admin\component\TabbedContent;
use dry\admin\Module;
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
use Tnt\Crm\Enum\Language;
use Tnt\Crm\Model\Contact;
use Tnt\Crm\Model\Country;
use Tnt\Crm\Model\Relation;

class ContactManager extends Manager
{
    public $edit;

    public function __construct(array $kwargs = [])
    {
        $model = Contact::class;
        $language_options = Language::enum();
        $relation_model = Relation::class;
        $extra_tabs = [];
        $extra_filters = [];
        $sort_field = 'first_name';
        $sort_direction = StaticSorter::ASC;
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
            Country::addressComponents(),
        ];

        $this->actions[] = $create = new Create($generalComponents, [
            'popup' => true,
        ]);

        $editContent = TabbedContent::create()
            ->add_tab("Relations", [
                InlineManager::create(new RelationContactManager(new $model(), ['reference_model' => new $relation_model()]))
                    ->set_foreign_key('contact')
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
                    ])->enable_background(),
                    CreateNote::getNoteComponent(),
                ]),
            ])->set_grid([5, 2]),
        ]);

        ['create' => $create_note, 'edit' => $edit_note] = CreateNote::register($this);

        $this->actions[] = $delete = new Delete();

        $this->header[] = new Search();
        $this->header[] = $create->create_link('Add contact');

        $this->footer[] = new Pagination();

        $this->index = new Index([
            Stack::vertical([
                StringView::create('first_name'),
                StringView::create('last_name'),
            ])->set_header('Name'),
            StringView::create('email')
                ->set_link(function ($row) {
                    return "mailto:$row->email";
                }),
            StringView::create('phone'),
            EnumView::create('language')
                ->set_options($language_options),
            DateView::create("created")->set_format("d/m/Y H:i"),
            DateView::create("updated")->set_format("d/m/Y H:i"),
            CreateNote::renderTableActions($create_note, $edit_note),
            $this->edit->create_link(),
            $delete->create_link(),
        ]);

        $this->index->filters[] = new EnumFilter("country", Country::enum(), ["title" => "Countries"]);
        $this->index->filters[] = new EnumFilter("language", $language_options, ["title" => "Languages"]);

        foreach ($extra_filters as $filter) {
            $this->index->filters[] = $filter;
        }

        $this->index->sorter = new StaticSorter($sort_field, $sort_direction);
        $this->index->searcher = new LikeSearcher((new $model())->getSearchFields());
    }
}
