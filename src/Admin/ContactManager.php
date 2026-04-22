<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\EnumEdit;
use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\admin\component\TabbedContent;
use dry\admin\Module;
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
use Tnt\Crm\Enum\ContactMode;
use Tnt\Crm\Enum\Language;
use Tnt\Crm\Model\Country;
use Tnt\Crm\Model\Relation;

class ContactManager extends Manager
{
    public function __construct($model, array $kwargs = [])
    {
        $relation_model = Relation::class;
        $country_filter = true;
        $language_options = null;
        $extra_filters = [];
        extract($kwargs, EXTR_IF_EXISTS);
        $extra_tabs = $model::getExtraTabs();
        $language_enabled = $model::$languageEnabled;
        $sort_field = $model::$sortField;
        $sort_direction = $model::$sortDirection;
        $click_to_edit = $model::$clickToEdit;
        $language_options ??= $language_enabled ? Language::enum() : [];


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
                    ->set_label('Last name'),
            ])->set_grid([4, 4]),
            ...($language_enabled ? [EnumEdit::create('language')
                ->set_label("Language")
                ->set_options($language_options)] : []),
            StringEdit::create('email')
                ->set_label('Email'),
            StringEdit::create('phone')
                ->set_label('Phone'),
            ...($country_filter ? [Country::addressComponents()] : []),
        ];

        $this->actions[] = $create = new Create($generalComponents, [
            'popup' => true,
        ]);

        $relationsTabContent = $relation_model::$contactMode === ContactMode::Direct
            ? [ForeignKeyIndexPicker::create('relation')
                ->set_components([new StringView('first_name')])
                ->set_searcher(new LikeSearcher($relation_model::$searchFields ?? []))]
            : [InlineManager::create(new RelationContactManager(new $model(), ['reference_model' => new $relation_model()]))
                ->set_foreign_key('contact')];

        $editContent = TabbedContent::create()
            ->add_tab("Relations", $relationsTabContent);

        foreach ($extra_tabs as $label => $components) {
            $editContent->add_tab($label, $components);
        }

        $this->actions[] = $edit = new Edit([
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
            ...$model::getIndexComponents($language_options),
            CreateNote::renderTableActions($create_note, $edit_note),
            $edit->create_link(),
            $delete->create_link(),
        ]);

        if ($click_to_edit) {
            $this->index->set_row_action($edit->create_link(''));
        }

        if ($country_filter) {
            $this->index->filters[] = new EnumFilter("country", Country::enum(), ["title" => "Countries"]);
        }
        if ($language_enabled) {
            $this->index->filters[] = new EnumFilter("language", $language_options, ["title" => "Languages"]);
        }

        foreach ($extra_filters as $filter) {
            $this->index->filters[] = $filter;
        }

        $this->index->sorter = new StaticSorter($sort_field, $sort_direction);
        $this->index->searcher = new LikeSearcher($model::$searchFields);
    }
}
