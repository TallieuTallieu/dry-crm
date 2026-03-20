<?php

namespace Tnt\Crm\Admin\Actions;

use dry\admin\component\Conditional;
use dry\admin\component\Stack;
use dry\admin\component\StringEdit;
use dry\expr\Equals;
use dry\expr\Field;
use dry\expr\Literal;
use dry\expr\Or_;
use dry\orm\action\Edit;

class CreateNote extends Edit
{
    public function __construct($edit = false)
    {
        $kwargs = [
            'id' => $edit ? 'edit_note' : 'create_note',
            'icon' => $edit ? Edit::ICON_NOTE : Edit::ICON_ADD,
            'mode' => Edit::MODE_POPUP,
        ];

        parent::__construct([Stack::vertical([self::getNoteComponent()])], $kwargs);
    }

    public static function getNoteComponent(): StringEdit
    {
        return StringEdit::create('note')->set_multiline();
    }

    public static function renderTableActions(self $create_note, self $edit_note): Conditional
    {
        return new Conditional([
            new Or_(new Equals(new Field(['note']), new Literal("")), new Equals(new Field(['note']), new Literal(null))),
            [$create_note->create_link('')],
            new Literal(true),
            [$edit_note->create_link('')],
        ]);
    }
}
