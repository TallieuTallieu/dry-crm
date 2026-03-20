# dry-crm

CRM package for the Dry framework. Provides admin managers for organisations, contacts, and countries.

## Installation

```bash
composer require tallieutallieu/dry-crm
```

Register the service provider:

```php
$app->register(new \Tnt\Crm\CrmServiceProvider());
```

## Usage

Resolve the portal via the container:

```php
$portal = $app->get(\Tnt\Crm\Contracts\CrmPortalInterface::class);
```

## Configuration

Create `config/crm.php` in your project root. The file returns a flat array — the `crm.` prefix is derived from the filename by Oak's config repository:

| Key | Default | Description |
|-----|---------|-------------|
| `extra_modules` | `[]` | Extra admin modules (managers) to register in the CRM portal |
| `organisation_model` | `Tnt\Crm\Model\Organisation::class` | Model class to use for organisations |
| `contact_model` | `Tnt\Crm\Model\Contact::class` | Model class to use for contacts |
| `language_options` | `Language::enum()` (nl, fr, en, de) | Options for the language field, array of `[value, label]` pairs |
| `contact_extra_tabs` | `[]` | Extra tabs to add to the contact edit view, keyed by tab label |
| `contact_extra_filters` | `[]` | Extra filters to add to the contact index |
| `contact_sort_field` | `'first_name'` | Field to sort the contact index by |
| `contact_sort_direction` | `StaticSorter::ASC` | Sort direction (`StaticSorter::ASC` or `StaticSorter::DESC`) |
| `organisation_extra_tabs` | `[]` | Extra tabs to add to the organisation edit view, keyed by tab label |
| `organisation_extra_filters` | `[]` | Extra filters to add to the organisation index |
| `organisation_sort_field` | `'name'` | Field to sort the organisation index by |
| `organisation_sort_direction` | `StaticSorter::ASC` | Sort direction (`StaticSorter::ASC` or `StaticSorter::DESC`) |

Example `config/crm.php`:

```php
<?php

return [
    'organisation_model' => \App\Model\Organisation::class,
    'contact_model' => \App\Model\Contact::class,
    'language_options' => [
        ['nl', 'Dutch'],
        ['fr', 'French'],
    ],
    'contact_extra_tabs' => [
        'My Tab' => [
            \dry\orm\component\InlineManager::create(new \App\Admin\SomeManager())
                ->set_foreign_key('contact'),
        ],
    ],
    'contact_extra_filters' => [
        new \dry\orm\filter\EnumFilter('type', \App\Enum\ContactType::enum(), ['title' => 'Type']),
    ],
    'contact_sort_field' => 'first_name',
    'contact_sort_direction' => \dry\orm\sort\StaticSorter::ASC,
    'organisation_extra_tabs' => [
        'My Tab' => [
            \dry\orm\component\InlineManager::create(new \App\Admin\SomeManager())
                ->set_foreign_key('organisation'),
        ],
    ],
    'organisation_extra_filters' => [
        new \dry\orm\filter\EnumFilter('type', \App\Enum\OrganisationType::enum(), ['title' => 'Type']),
    ],
    'organisation_sort_field' => 'name',
    'organisation_sort_direction' => \dry\orm\sort\StaticSorter::ASC,
    'extra_modules' => [
        new \App\Admin\CustomManager(),
    ],
];
```

> You can also pass the result of an Enum: `\App\Enum\Language::enum()`

## Extending models

Custom models passed via `crm.organisation_model` or `crm.contact_model` **must extend the base model class**. This is required because the admin managers use `instanceof` checks to determine the relationship direction.

```php
// ✓ Correct — extends the base class
class Organisation extends \Tnt\Crm\Model\Organisation
{
}

// ✗ Wrong — does not extend the base class, will throw a TypeError
class Organisation extends \dry\orm\Model
{
}
```

The default models already implement `SearchableInterface`. If your custom model overrides `getSearchFields()`, that version will be used for search:

```php
class Organisation extends \Tnt\Crm\Model\Organisation
{
    public function getSearchFields(): array
    {
        return ['name', 'email', 'phone'];
    }
}
```
