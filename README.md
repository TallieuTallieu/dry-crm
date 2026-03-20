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

The following values can be set in your config under the `crm` key:

| Key | Default | Description |
|-----|---------|-------------|
| `crm.organisation_model` | `Tnt\Crm\Model\Organisation::class` | Model class to use for organisations |
| `crm.contact_model` | `Tnt\Crm\Model\Contact::class` | Model class to use for contacts |
| `crm.language_options` | `Language::enum()` (nl, fr, en, de) | Options for the language field, array of `[value, label]` pairs |
| `crm.contact_extra_tabs` | `[]` | Extra tabs to add to the contact edit view, keyed by tab label |
| `crm.organisation_extra_tabs` | `[]` | Extra tabs to add to the organisation edit view, keyed by tab label |

Example:

```php
'crm' => [
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
    'organisation_extra_tabs' => [
        'My Tab' => [
            \dry\orm\component\InlineManager::create(new \App\Admin\SomeManager())
                ->set_foreign_key('organisation'),
        ],
    ],
],
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
