<?php

namespace Tnt\Crm\Admin;

use dry\admin\component\Foreign;
use dry\admin\component\StringEdit;
use dry\admin\component\StringView;
use dry\admin\Module;
use dry\orm\action\Create;
use dry\orm\action\Delete;
use dry\orm\action\Edit;
use dry\orm\component\ForeignKeyIndexPicker;
use dry\orm\component\Pagination;
use dry\orm\Index;
use dry\orm\Manager;
use dry\orm\search\LikeSearcher;
use Tnt\Crm\Admin\Actions\CreateNote;
use Tnt\Crm\Model\Contact;
use Tnt\Crm\Model\Relation;
use Tnt\Crm\Model\RelationContact;

class RelationContactManager extends Manager
{
    public $edit;

    public function __construct(Relation|Contact $relatedModel, array $kwargs = [])
    {
        $model = RelationContact::class;
        $reference_model = null;
        extract($kwargs, EXTR_IF_EXISTS);

        $title = '';
        $foreignKey = '';
        $foreignKeyColumn = '';
        $foreignKeyIndexName = '';

        if ($relatedModel instanceof Relation) {
            $title = 'contact';
            $foreignKey = 'contact';
            $foreignKeyColumn = 'first_name';
            $foreignKeyIndexName = 'Contact';
            $reference_model = $reference_model ?? new Contact();
        }

        if ($relatedModel instanceof Contact) {
            $title = 'relation';
            $foreignKey = 'relation';
            $foreignKeyColumn = 'name';
            $foreignKeyIndexName = 'Relation';
            $reference_model = $reference_model ?? new Relation();
        }

        /**
         * @param Relation|Contact $relatedModel
         */
        parent::__construct($model, [
            'icon' => Module::ICON_PEOPLE,
            'singular' => $title,
        ]);

        $this->actions[] = $create = new Create(
            [
                ForeignKeyIndexPicker::create($foreignKey)
                    ->set_components([
                        new StringView($foreignKeyColumn),
                    ])
                    ->set_searcher(new LikeSearcher($reference_model->getSearchFields())),
                StringEdit::create('function')
                    ->set_label('Function'),
                CreateNote::getNoteComponent()
            ],
            [
                'popup' => true,
            ],
        );

        $this->actions[] = $edit = new Edit($create->components, [
            'popup' => true,
        ]);

        ['create' => $create_note, 'edit' => $edit_note] = CreateNote::register($this);

        $this->actions[] = $delete = new Delete();

        $this->header[] = $create->create_link("Add $title");

        $this->footer[] = new Pagination();

        $this->index = new Index([
            Foreign::create($foreignKey, new StringView($foreignKeyColumn), ["header" => $foreignKeyIndexName]),
            StringView::create("function")->set_header("Function"),
            CreateNote::renderTableActions($create_note, $edit_note),
            $edit->create_link(),
            $delete->create_link(),
        ]);
    }
}
