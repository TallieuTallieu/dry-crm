# dry-crm — Claude context

## What this package does

A CRM package for the Dry framework. Provides admin managers, models, migrations, and a service provider for managing organisations, contacts, and countries.

## Package structure

```
src/
├── Admin/               # Dry admin managers (CRUD UI)
│   ├── ContactManager.php
│   ├── OrganisationManager.php
│   ├── OrganisationContactManager.php
│   └── CountryManager.php
├── Contracts/           # Interfaces
│   ├── CrmPortalInterface.php
│   └── SearchableInterface.php
├── Enum/
│   └── Language.php     # Backed enum with ::enum() helper returning [[value, label], ...]
├── Model/               # Dry ORM models
│   ├── Contact.php
│   ├── Organisation.php
│   ├── OrganisationContact.php  # Pivot model (many-to-many)
│   └── Country.php
├── Revisions/           # Database migrations
└── CrmServiceProvider.php
```

## Key conventions

### Managers
- Extend `dry\orm\Manager`
- Accept `array $kwargs` and use `extract($kwargs, EXTR_IF_EXISTS)` so that consuming projects can override `$model` and other vars
- The `Create` action takes an array of components as first argument — pass individual component objects, not a nested array
- Use `...$spread` when inlining a `$components` array into a `Stack`

### Models
- Extend `dry\orm\Model`
- Define `const TABLE`
- Implement `SearchableInterface` to enable LikeSearcher in the admin index — return the field names to search on
- Define `__toString()` for use in foreign key pickers

### SearchableInterface
`Tnt\Crm\Contracts\SearchableInterface` enforces `getSearchFields(): array`. Both `Contact` and `Organisation` implement it. Custom models passed via config must also implement it if search is needed.

### Extra tabs

`crm.contact_extra_tabs` and `crm.organisation_extra_tabs` are associative arrays of `label => components[]` pairs appended to the `TabbedContent` in the respective edit views (after the default "Organisations" / "Contacts" tab). Can also be passed directly as an `extra_tabs` kwarg when instantiating the manager.

### Language enum
`Language::enum()` returns `[['nl', 'Dutch'], ['fr', 'French'], ...]`. The language options in `ContactManager` can be overridden via `crm.language_options` config or a custom `language_options` kwarg.

## Configuration file

In the consuming project, config lives at `config/crm.php` in the project root. It returns a flat array — keys are the bare key names (without the `crm.` prefix):

```php
// config/crm.php
return [
    'organisation_model' => MyOrganisation::class,
    'extra_modules' => [...],
];
```

## Configuration keys

Keys in `config/crm.php` (bare, without `crm.` prefix). The service provider reads them as `crm.<key>` via Oak's dot-notation config.

| Key in file | Service provider reads as | Default |
|-------------|--------------------------|---------|
| `extra_modules` | `crm.extra_modules` | `[]` |
| `organisation_model` | `crm.organisation_model` | `Tnt\Crm\Model\Organisation::class` |
| `contact_model` | `crm.contact_model` | `Tnt\Crm\Model\Contact::class` |
| `language_options` | `crm.language_options` | `Language::enum()` |
| `contact_extra_tabs` | `crm.contact_extra_tabs` | `[]` |
| `contact_extra_filters` | `crm.contact_extra_filters` | `[]` |
| `contact_sort_field` | `crm.contact_sort_field` | `'first_name'` |
| `contact_sort_direction` | `crm.contact_sort_direction` | `StaticSorter::ASC` |
| `organisation_extra_tabs` | `crm.organisation_extra_tabs` | `[]` |
| `organisation_extra_filters` | `crm.organisation_extra_filters` | `[]` |
| `organisation_sort_field` | `crm.organisation_sort_field` | `'name'` |
| `organisation_sort_direction` | `crm.organisation_sort_direction` | `StaticSorter::ASC` |

## Generating migrations

Use the `generate-revision` skill:

```
/generate-revision
```

Migration files go in `src/Revisions/` and are auto-loaded by `CrmServiceProvider::getMigrations()` (skipping `DatabaseRevision.php`).
