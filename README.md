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

Create `config/crm.php` in your project root. The file returns a flat array — the `crm.` prefix is derived from the filename by Oak's config repository:

| Key | Default | Description |
|-----|---------|-------------|
| `extra_modules` | `[]` | Extra admin modules (managers) to register in the CRM portal |
| `relation_model` | `Tnt\Crm\Model\Relation::class` | Model class to use for relations |
| `contact_model` | `Tnt\Crm\Model\Contact::class` | Model class to use for contacts |
| `language_options` | `Language::enum()` (nl, fr, en, de) | Options for the language field, array of `[value, label]` pairs |
| `contact_extra_tabs` | `[]` | Extra tabs to add to the contact edit view, keyed by tab label |
| `contact_extra_filters` | `[]` | Extra filters to add to the contact index |
| `contact_sort_field` | `'first_name'` | Field to sort the contact index by |
| `contact_sort_direction` | `StaticSorter::ASC` | Sort direction (`StaticSorter::ASC` or `StaticSorter::DESC`) |
| `relation_extra_tabs` | `[]` | Extra tabs to add to the relation edit view, keyed by tab label |
| `relation_manager_filters` | `[]` | Array of filter class name strings to add to the relation index. Each class is instantiated automatically. |
| `relation_extra_header_actions` | `[]` | Array of class name strings to append to the relation index header. Each class is instantiated and `create_link()` is called; if the result has a non-null `action` property, that action is also registered on the manager. |
| `relation_manager_pagination_amount` | `50` | Number of relations per page in the index |
| `relation_manager_editable` | `true` | Set to `false` to hide the edit action and link |
| `relation_manager_deletable` | `true` | Set to `false` to hide the delete action and link |
| `relation_sort_field` | `'last_name'` | Field to sort the relation index by |
| `relation_sort_direction` | `StaticSorter::ASC` | Sort direction (`StaticSorter::ASC` or `StaticSorter::DESC`) |
| `contact_manager` | `true` | Set to `false` to exclude the ContactManager from the CRM portal |
| `country_manager` | `true` | Set to `false` to exclude the CountryManager from the CRM portal |

> **Note:** The `relation_general_components` config key has been removed. Override `getCreateComponents()` and/or `getEditComponents()` on your custom model instead — see below.

## Customising index columns

The columns shown in the relation index table are defined by `getIndexComponents()` on the relation model. Override this static method in your custom model to change which columns appear:

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

## Customising create / edit form fields

Form fields come from `getCreateComponents()` on the model (used for the create popup) and `getEditComponents()` (used for the edit view). By default `getEditComponents()` delegates to `getCreateComponents()`. Override it in a custom model to use different fields in the edit view:

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

## Extending models

Custom models passed via `crm.relation_model` or `crm.contact_model` **must extend the base model class**. This is required because the admin managers use `instanceof` checks to determine the relationship direction.

```php
// ✓ Correct — extends the base class
class Relation extends \Tnt\Crm\Model\Relation
{
}

// ✗ Wrong — does not extend the base class, will throw a TypeError
class Relation extends \dry\orm\Model
{
}
```

The default models already implement `SearchableInterface`. If your custom model overrides `getSearchFields()`, that version will be used for search:

```php
class Relation extends \Tnt\Crm\Model\Relation
{
    public function getSearchFields(): array
    {
        return ['name', 'email', 'phone'];
    }
}
```
