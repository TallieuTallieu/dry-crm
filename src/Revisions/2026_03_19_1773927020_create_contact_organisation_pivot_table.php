<?php

namespace Tnt\Crm\Revisions;

use Oak\Contracts\Migration\RevisionInterface;
use Tnt\Dbi\TableBuilder;

return new class extends DatabaseRevision implements RevisionInterface {
    /**
     * Create crm_contact_organisation pivot table
     */
    public function up(): void
    {
        $this->queryBuilder
            ->table('crm_contact_organisation')
            ->create(function (TableBuilder $table): void {
                $table->id();
                $table->timestamps();
                $table->addColumn('contact', 'int')->length(11);
                $table->addColumn('organisation', 'int')->length(11);
                $table->addForeignKey('contact', 'crm_contact');
                $table->addForeignKey('organisation', 'crm_organisation');
                $table->addColumn('note', 'text')->null();
                $table->addColumn('function', 'varchar')->length(255)->null();
            });

        $this->execute();
    }

    /**
     * Drop crm_contact_organisation pivot table
     */
    public function down(): void
    {
        $this->queryBuilder->table('crm_contact_organisation')->drop();

        $this->execute();
    }

    /**
     * @return string
     */
    public function describeUp(): string
    {
        return 'Create crm_contact_organisation pivot table';
    }

    /**
     * @return string
     */
    public function describeDown(): string
    {
        return 'Drop crm_contact_organisation pivot table';
    }
};
