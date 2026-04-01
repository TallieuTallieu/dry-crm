# dry-crm — Claude context

## What this package does

A CRM package for the Dry framework. Provides admin managers, models, migrations, and a service provider for managing relations, contacts, and countries.

## Package structure

```
src/
├── Admin/               # Dry admin managers (CRUD UI)
│   ├── ContactManager.php
│   ├── RelationManager.php
│   ├── RelationContactManager.php
│   └── CountryManager.php
├── Contracts/           # Interfaces
│   ├── CrmPortalInterface.php
│   └── SearchableInterface.php
├── Enum/
│   └── Language.php     # Backed enum with ::enum() helper returning [[value, label], ...]
├── Model/               # Dry ORM models
│   ├── Contact.php
│   ├── Relation.php
│   ├── RelationContact.php  # Pivot model (many-to-many)
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
`Tnt\Crm\Contracts\SearchableInterface` enforces `getSearchFields(): array`. Both `Contact` and `Relation` implement it. Custom models passed via config must also implement it if search is needed.

### Index columns
`RelationManager` calls `$model::getIndexComponents()` to get the columns shown in the index table. Override this static method in a custom model to customise the columns — no config key needed.

### Create / edit components
`RelationManager` calls `$model::getCreateComponents()` for the create popup form and `$model::getEditComponents()` for the edit view. By default `getEditComponents()` delegates to `getCreateComponents()`. Override `getEditComponents()` in a custom model to use different fields in the edit view.

### Header actions

`crm.relation_extra_header_actions` accepts an array of **class name strings**. The service provider instantiates each class and calls `->create_link()` on it. The resulting objects are appended to the relation index header after the "Add relation" button.

If the object returned by `create_link()` has a non-null `action` property, that action is also registered on the manager (needed for the action to be reachable).

### Extra tabs

`crm.contact_extra_tabs` and `crm.relation_extra_tabs` are associative arrays of `label => components[]` pairs appended to the `TabbedContent` in the respective edit views (after the default "Relations" / "Contacts" tab). Can also be passed directly as an `extra_tabs` kwarg when instantiating the manager.

### Language enum
`Language::enum()` returns `[['nl', 'Dutch'], ['fr', 'French'], ...]`. The language options in `ContactManager` can be overridden via `crm.language_options` config or a custom `language_options` kwarg.

## Configuration file

In the consuming project, config lives at `config/crm.php` in the project root. It returns a flat array — keys are the bare key names (without the `crm.` prefix):

```php
// config/crm.php
return [
    'relation_model' => MyRelation::class,
    'extra_modules' => [...],
];
```

## Configuration keys

Keys in `config/crm.php` (bare, without `crm.` prefix). The service provider reads them as `crm.<key>` via Oak's dot-notation config.

| Key in file | Service provider reads as | Default |
|-------------|--------------------------|---------|
| `extra_modules` | `crm.extra_modules` | `[]` |
| `relation_model` | `crm.relation_model` | `Tnt\Crm\Model\Relation::class` |
| `contact_model` | `crm.contact_model` | `Tnt\Crm\Model\Contact::class` |
| `language_enabled` | `crm.language_enabled` | `true` |
| `language_options` | `crm.language_options` | `Language::enum()` (ignored when `language_enabled` is `false`) |
| `contact_extra_tabs` | `crm.contact_extra_tabs` | `[]` |
| `contact_extra_filters` | `crm.contact_extra_filters` | `[]` |
| `contact_sort_field` | `crm.contact_sort_field` | `'first_name'` |
| `contact_sort_direction` | `crm.contact_sort_direction` | `StaticSorter::ASC` |
| `relation_extra_tabs` | `crm.relation_extra_tabs` | `[]` |
| `relation_manager_filters` | `crm.relation_manager_filters` | `[]` |
| `relation_extra_header_actions` | `crm.relation_extra_header_actions` | `[]` |
| `relation_manager_editable` | `crm.relation_manager_editable` | `true` |
| `relation_manager_deletable` | `crm.relation_manager_deletable` | `true` |
| `relation_manager_pagination_amount` | `crm.relation_manager_pagination_amount` | `50` |
| `relation_sort_field` | `crm.relation_sort_field` | `'last_name'` |
| `relation_sort_direction` | `crm.relation_sort_direction` | `StaticSorter::ASC` |
| `contact_manager` | `crm.contact_manager` | `true` |
| `country_manager` | `crm.country_manager` | `true` |

## Generating migrations

Use the `generate-revision` skill:

```
/generate-revision
```

Migration files go in `src/Revisions/` and are auto-loaded by `CrmServiceProvider::getMigrations()` (skipping `DatabaseRevision.php`).
