<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\orm\action\Create;
use dry\orm\action\Delete;
use dry\orm\action\Edit;
use dry\orm\Index;
use dry\orm\Manager;
use dry\orm\sort\StaticSorter;
use Tnt\Crm\Model\Country;

class CountryManager extends Manager
{
    public $edit;

    public function __construct(array $kwargs = [])
    {
        $model = Country::class;
        extract($kwargs, EXTR_IF_EXISTS);

        parent::__construct($model, [
            'icon' => "public",
            'singular' => 'country',
            'plural' => 'countries',
        ]);


        $this->actions[] = $create = new Create([
            StringEdit::create("name"),
        ], [
            'popup' => true,
        ]);

        $this->actions[] = $this->edit = new Edit($create->components, [
            'popup' => true,
        ]);

        $this->actions[] = $delete = new Delete();

        $this->header[] = $create->create_link('Add country');

        $this->index = new Index([
            StringView::create('name'),
            $this->edit->create_link(),
            $delete->create_link(),
        ]);

        $this->index->sorter = new StaticSorter('name', StaticSorter::ASC);
    }
}
