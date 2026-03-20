Generate a new database revision file in `src/Revisions/`.

## Instructions

The user may provide a revision name as an argument: `$ARGUMENTS`

If no argument is provided, ask the user for the revision name.

**Naming convention:** snake_case and prepend with timestamp `Y_m_d_timestamp_` (e.g., `CreateOrganisationTable`, `UpdateContactAddPhone`, `AddSlugToOrganisationTable`).

### Steps

1. Determine the revision name (from `$ARGUMENTS` or ask the user).
2. Detect the action type from the name:
   - Starts with `Create` → use **create** template
   - Starts with `Add`, `Update`, `Alter`, `Modify`, `Remove`, `Drop` → use **alter** template
   - Default to **alter** template if unclear
3. Extract the table name from the revision name:
   - `CreateOrganisationTable` → `crm_organisation`
   - `UpdateContactAddPhone` → `crm_contact` (convert PascalCase to snake_case, drop trailing action words like `AddPhone`)
   - `AddSlugToOrganisationTable` → `crm_organisation`
   - Use your best judgment to extract the primary table name
4. Generate descriptions:
   - **Create**: up = `Create {table} table`, down = `Drop {table} table`
   - **Drop**: up = `Drop {table} table`, down = `Create {table} table`
   - **Add columns**: up = `Add columns to {table} table`, down = `Remove columns from {table} table`
   - **Alter/Update/Modify**: up = `Update {table} table`, down = `Revert {table} table changes`
5. Write the file to `src/Revisions/{RevisionName}.php`.

### Create template

```php
<?php

namespace Tnt\Crm\Revisions;

use Oak\Contracts\Migration\RevisionInterface;
use Tnt\Dbi\TableBuilder;

return new class extends DatabaseRevision implements RevisionInterface {
    /**
     * {description_up}
     */
    public function up(): void
    {
        $this->queryBuilder
            ->table('{table_name}')
            ->create(function (TableBuilder $table): void {
                $table->id();
                $table->timestamps();
            });

        $this->execute();
    }

    /**
     * {description_down}
     */
    public function down(): void
    {
        $this->queryBuilder->table('{table_name}')->drop();

        $this->execute();
    }

    /**
     * @return string
     */
    public function describeUp(): string
    {
        return '{description_up}';
    }

    /**
     * @return string
     */
    public function describeDown(): string
    {
        return '{description_down}';
    }
};
```

### Alter template

```php
<?php

namespace Tnt\Crm\Revisions;

use Oak\Contracts\Migration\RevisionInterface;
use Tnt\Dbi\TableBuilder;

return new class extends DatabaseRevision implements RevisionInterface {
    /**
     * {description_up}
     */
    public function up(): void
    {
        $this->queryBuilder
            ->table('{table_name}')
            ->alter(function (TableBuilder $table): void {
                // Add your column modifications here
            });

        $this->execute();
    }

    /**
     * {description_down}
     */
    public function down(): void
    {
        $this->queryBuilder
            ->table('{table_name}')
            ->alter(function (TableBuilder $table): void {
                // Revert your column modifications here
            });

        $this->execute();
    }

    /**
     * @return string
     */
    public function describeUp(): string
    {
        return '{description_up}';
    }

    /**
     * @return string
     */
    public function describeDown(): string
    {
        return '{description_down}';
    }
};
```

After writing the file, confirm with the path of the created file.
