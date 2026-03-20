<?php

namespace Tnt\Crm\Revisions;

use Oak\Contracts\Migration\RevisionInterface;
use Tnt\Dbi\TableBuilder;

return new class extends DatabaseRevision implements RevisionInterface {
    /**
     * Create crm_contact table
     */
    public function up(): void
    {
        $this->queryBuilder
            ->table('crm_contact')
            ->create(function (TableBuilder $table): void {
                $table->id();
                $table->timestamps();
                $table->addColumn('first_name', 'varchar')->length(255);
                $table->addColumn('last_name', 'varchar')->length(255);
                $table->addColumn('language', 'varchar')->length(255);
                $table->addColumn('function', 'varchar')->length(255)->null();
                $table->addColumn('email', 'varchar')->length(255)->null();
                $table->addColumn('phone', 'varchar')->length(50)->null();
                $table->addColumn('address_street', 'varchar')->length(255)->null();
                $table->addColumn('address_number', 'varchar')->length(255)->null();
                $table->addColumn('address_city', 'varchar')->length(100)->null();
                $table->addColumn('address_postal_code', 'varchar')->length(20)->null();
                $table->addColumn('note', 'text')->null();
                
                $table->addColumn('country', 'int')->length(11)->null();
            });

        $this->execute();
    }

    /**
     * Drop crm_contact table
     */
    public function down(): void
    {
        $this->queryBuilder->table('crm_contact')->drop();

        $this->execute();
    }

    /**
     * @return string
     */
    public function describeUp(): string
    {
        return 'Create crm_contact table';
    }

    /**
     * @return string
     */
    public function describeDown(): string
    {
        return 'Drop crm_contact table';
    }
};
