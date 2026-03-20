<?php

namespace Tnt\Crm\Revisions;

use dry\db\Connection;
use Tnt\Dbi\QueryBuilder;

/**
 * Class DatabaseRevision
 * @package Tnt\Ecommerce
 */
abstract class DatabaseRevision
{
    protected QueryBuilder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = new QueryBuilder();
    }

    protected function execute(): void
    {
        $this->queryBuilder->build();
        $connection = Connection::get();

        foreach ($this->queryBuilder->getQueries() as $query) {
            $query = str_replace("COLLATE 'utf8mb4_0900_ai_ci'", "COLLATE 'utf8mb4_unicode_ci'", $query);
            $connection->query($query);
        }
    }
}
