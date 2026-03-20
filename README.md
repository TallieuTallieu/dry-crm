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

Example:

```php
'crm' => [
    'organisation_model' => \App\Model\Organisation::class,
    'contact_model' => \App\Model\Contact::class,
    'language_options' => [
        ['nl', 'Dutch'],
        ['fr', 'French'],
    ],
],
```

> You can also pass the result of an Enum: `\App\Enum\Language::enum()`
