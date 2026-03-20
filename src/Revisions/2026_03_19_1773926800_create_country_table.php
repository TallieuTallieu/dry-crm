<?php

namespace Tnt\Crm\Revisions;

use Oak\Contracts\Migration\RevisionInterface;
use Tnt\Dbi\TableBuilder;

return new class extends DatabaseRevision implements RevisionInterface {
    /**
     * Create crm_country table
     */
    public function up(): void
    {
        $this->queryBuilder
            ->table('crm_country')
            ->create(function (TableBuilder $table): void {
                $table->id();
                $table->timestamps();
                $table->addColumn('name', 'varchar')->length(255);
            });

        $this->execute();
    }

    /**
     * Drop crm_country table
     */
    public function down(): void
    {
        $this->queryBuilder->table('crm_country')->drop();

        $this->execute();
    }

    /**
     * @return string
     */
    public function describeUp(): string
    {
        return 'Create crm_country table';
    }

    /**
     * @return string
     */
    public function describeDown(): string
    {
        return 'Drop crm_country table';
    }
};
