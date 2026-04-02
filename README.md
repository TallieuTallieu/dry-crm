# dry-crm

CRM package for the Dry framework. Provides admin managers for relations, contacts, and countries.

## Installation

```bash
composer require tallieutallieu/dry-crm
```

### `providers.inc.php`

Register the service provider:

```php
$app->register([

    ...

    // Packages
    \Tnt\Crm\CrmServiceProvider::class,
]);
```

### `admin.inc.php`

Register the CRM portal modules:

```php
/**
 * Crm Manager
 */
admin\Router::$modules[] = Application::get()->get(CrmPortalInterface::class);
```

## Configuration

Create `config/crm.php` in your project root. The file returns a flat array — the `crm.` prefix is derived from the filename by Oak's config repository.

Most behaviour is now controlled by **static properties on the model** (see [Customising models](#customising-models)). The config file handles only package-level concerns:

| Key | Default | Description |
|-----|---------|-------------|
| `extra_modules` | `[]` | Extra admin modules to register in the CRM portal |
| `relation_model` | `Tnt\Crm\Model\Relation::class` | Model class to use for relations |
| `contact_model` | `Tnt\Crm\Model\Contact::class` | Model class to use for contacts |
| `language_options` | `Language::enum()` (nl, fr, en, de) | Options for the language field; ignored when `Contact::$languageEnabled` is `false` |
| `contact_extra_tabs` | `[]` | Extra tabs added to the contact edit view, keyed by tab label |
| `contact_extra_filters` | `[]` | Extra filter instances added to the contact index |
| `relation_extra_tabs` | `[]` | Extra tabs added to the relation edit view, keyed by tab label |
| `relation_manager_filters` | `[]` | Array of filter class name strings added to the relation index. Each class is instantiated automatically. |
| `relation_extra_header_actions` | `[]` | Array of class name strings appended to the relation index header. Each class is instantiated and `create_link()` is called; if the result has a non-null `action` property, that action is also registered on the manager. |
| `contact_manager` | `true` | Set to `false` to exclude the ContactManager from the CRM portal |
| `country_manager` | `true` | Set to `false` to exclude the CountryManager from the CRM portal; also hides the country field and filter in both managers |

## Customising models

Custom models must **extend the base model class**:

```php
// ✓ Correct
class Relation extends \Tnt\Crm\Model\Relation {}

// ✗ Wrong — will throw a TypeError
class Relation extends \dry\orm\Model {}
```

Pass the custom class via config:

```php
// config/crm.php
return [
    'relation_model' => App\Model\Relation::class,
    'contact_model'  => App\Model\Contact::class,
];
```

### Static properties — `Relation`

Override any of these static properties to change manager behaviour without touching config:

| Property | Default | Description |
|----------|---------|-------------|
| `$contactMode` | `ContactMode::Pivot` | `ContactMode::Pivot` — contacts via pivot table; `ContactMode::Direct` — contacts via `Contact.relation` FK (one-to-many) |
| `$sortField` | `'last_name'` | Field the relation index sorts by |
| `$sortDirection` | `StaticSorter::ASC` | Sort direction |
| `$paginationAmount` | `50` | Rows per page in the relation index |
| `$managerEditable` | `true` | Set to `false` to hide the edit action |
| `$managerDeletable` | `true` | Set to `false` to hide the delete action |
| `$searchFields` | `['first_name', 'last_name', ...]` | Fields used by the index search |

### Static properties — `Contact`

| Property | Default | Description |
|----------|---------|-------------|
| `$languageEnabled` | `true` | Set to `false` to hide the language field and filter entirely |
| `$sortField` | `'first_name'` | Field the contact index sorts by |
| `$sortDirection` | `StaticSorter::ASC` | Sort direction |
| `$searchFields` | `['first_name', 'last_name', 'email', 'phone']` | Fields used by the index search |
| `$showCreatedInIndex` | `true` | Show the created date column in the contact index |
| `$showUpdatedInIndex` | `true` | Show the updated date column in the contact index |

Example — switching to direct contact mode and disabling language:

```php
use Tnt\Crm\Enum\ContactMode;
use dry\orm\sort\StaticSorter;

class Relation extends \Tnt\Crm\Model\Relation
{
    public static ContactMode $contactMode = ContactMode::Direct;
}

class Contact extends \Tnt\Crm\Model\Contact
{
    public static bool $languageEnabled = false;
    public static string $sortField = 'last_name';
}
```

### Customising index columns

Override `getIndexComponents()` to change which columns appear in the index:

```php
class Relation extends \Tnt\Crm\Model\Relation
{
    public static function getIndexComponents(): array
    {
        return [
            \dry\admin\component\StringView::create('first_name'),
            \dry\admin\component\StringView::create('last_name'),
            \dry\admin\component\StringView::create('email'),
        ];
    }
}
```

### Customising create / edit form fields

Override `getCreateComponents()` (create popup) and/or `getEditComponents()` (edit view). By default `getEditComponents()` delegates to `getCreateComponents()`:

```php
class Relation extends \Tnt\Crm\Model\Relation
{
    public static function getEditComponents(): array
    {
        return [
            ...static::getCreateComponents(),
            \dry\admin\component\StringEdit::create('extra_field')->set_label('Extra Field'),
        ];
    }
}
```

### Customising search fields

Override `$searchFields` to control which fields the index search queries:

```php
class Relation extends \Tnt\Crm\Model\Relation
{
    public static array $searchFields = ['first_name', 'last_name', 'email', 'vat_number'];
}
```

### Fetching contacts programmatically

`Relation` exposes a `getContacts()` method that respects `$contactMode`:

```php
use Tnt\Crm\Enum\ContactMode;

// Pivot mode (default) — returns ManyToMany via RelationContact
foreach ($relation->getContacts() as $contact) { }

// Direct mode — returns HasMany via Contact.relation FK
foreach ($relation->getContacts(ContactMode::Direct) as $contact) { }
```
