<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\Stack;
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
use dry\orm\paginate\Paginator;
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
        $pagination_amount = 50;
        $sort_field = 'last_name';
        $manager_editable = true;
        $manager_deletable = true;
        $country_filter = true;
        $sort_direction = StaticSorter::ASC;
        extract($kwargs, EXTR_IF_EXISTS);

        parent::__construct($model, [
            'icon' => 'business_center',
            'singular' => 'relation',
            'plural' => 'relations',
        ]);

        $createComponents = $model::getCreateComponents();
        $editComponents = $model::getEditComponents();

        $this->actions[] = $create = new Create($createComponents, [
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

        if ($manager_editable) {
            $this->actions[] = $this->edit = new Edit([
                Stack::horizontal([
                    $editContent,
                    Stack::vertical([
                        Stack::vertical([
                            ...$editComponents
                        ])->set_title("Relation Settings"),
                        CreateNote::getNoteComponent()
                    ]),
                ])->set_grid([5, 2]),
            ]);
        }

        ['create' => $create_note, 'edit' => $edit_note] = CreateNote::register($this);

        $delete = null;
        if ($manager_deletable) {
            $this->actions[] = $delete = new Delete();
        }

        $this->header[] = new Search();
        $this->header[] = $create->create_link('Add relation');

        foreach ($extra_header_actions as $headerItem) {
            if (property_exists($headerItem, 'action') && $headerItem->action !== null) {
                $this->actions[] = $headerItem->action;
            }
            $this->header[] = $headerItem;
        }

        $this->footer[] = new Pagination();

        $index_action_links = [];
        foreach ($model::getIndexActions() as $action) {
            $this->actions[] = $action;
            $index_action_links[] = $action->create_link();
        }

        if ($manager_editable) {
            $index_action_links[] = $this->edit->create_link();
        }

        $this->index = new Index([
            ...($model::getIndexComponents()),
            CreateNote::renderTableActions($create_note, $edit_note),
            ...$index_action_links,
            ...($manager_deletable ? [$delete->create_link()] : []),
        ])->set_query_params();

        if ($country_filter) {
            $this->index->filters[] = new EnumFilter("country", Country::enum(), ["title" => "Countries"]);
        }

        foreach ($extra_filters as $filter) {
            $this->index->filters[] = new $filter;
        }

        $this->index->sorter = new StaticSorter($sort_field, $sort_direction);
        $this->index->searcher = new LikeSearcher((new $model())->getSearchFields());
        $this->index->paginator = new Paginator($pagination_amount);
    }
}
