# Changelog

All notable changes to this package are documented in this file. Versions
follow [Semantic Versioning](https://semver.org). New entries are generated from
commit messages by [dry-ci](https://github.com/TallieuTallieu/dry-ci); past
entries may be edited by hand.

## v1.0.1 - 2026-10-07

### Features

- Relation conditional row actions

## v1.0.0 - 2026-09-29

### Breaking changes

- Require dry 4 and switch to dry-ci

### Other changes

- Backfill CHANGELOG.md [sc-11672](https://app.shortcut.com/tallieu--tallieu/story/11672)

## v0.1.9 - 2026-04-22

### Features

- Add a `$clickToEdit` static property to the Contact, Relation and Country models; when enabled, clicking an index row opens the edit view
- Move the contact index columns to an overridable `Contact::getIndexComponents()`
- Add `Relation::getPostSaveCallback()` to set a post-save callback on the relation create action

## v0.1.8 - 2026-04-14

No notable changes.

## v0.1.7 - 2026-04-14

### Breaking changes

- `RelationManager` and `ContactManager` now take the model class as their first constructor argument (`new ContactManager($model, [...])`); the `model` kwarg and the public `$edit` property are gone. Only code that constructs these managers directly must change

## v0.1.6 - 2026-04-13

### Breaking changes

- The `relation_extra_tabs` and `contact_extra_tabs` config keys are removed: override the static `getExtraTabs()` method on your custom Relation or Contact model instead

## v0.1.5 - 2026-04-10

### Features

- Respect model managerEditable/managerDeletable in CountryManager
- Add enablePagination flag to Relation model and RelationManager

### Fixes

- Make last_name optional in ContactManager

## v0.1.4 - 2026-04-02

### Breaking changes

- Sorting, pagination, edit/delete and language settings move from config to static properties on the model: replace the `relation_sort_field`, `relation_sort_direction`, `relation_manager_pagination_amount`, `relation_manager_editable`, `relation_manager_deletable`, `contact_sort_field`, `contact_sort_direction` and `language_enabled` config keys with `$sortField`, `$sortDirection`, `$paginationAmount`, `$managerEditable`, `$managerDeletable` and `$languageEnabled` on a custom Relation or Contact model
- `SearchableInterface` is removed (replaced by `PivotReferenceInterface`): override the static `$searchFields` property instead of `getSearchFields()`

### Features

- Add relation FK and country FK to contact table
- Add direct contact mode (`Relation::$contactMode = ContactMode::Direct`), linking contacts to a relation through the contact's `relation` FK instead of the pivot table
- Swap Relations tab to ForeignKeyIndexPicker in direct contact mode

### Other changes

- Remove the explicit `tallieutallieu/dry` requirement from composer.json
- Update the README for model static properties and removed config keys

## v0.1.3 - 2026-04-01

### Breaking changes

- `getIndexCreateComponents()` is renamed to `getCreateComponents()`: rename the method where your custom model overrides or calls it

### Features

- Add address_box_number column to contact table
- Hide country and language fields/filters based on config flags (`country_manager`, `language_enabled`)

## v0.1.2 - 2026-03-30

### Fixes

- Show city instead of country in postal code field and add separate country view

## v0.1.1 - 2026-03-30

### Breaking changes

- Organisation is renamed to Relation: use `Tnt\Crm\Model\Relation`, `RelationContact`, `Tnt\Crm\Admin\RelationManager` and `RelationContactManager` instead of the Organisation classes, and rename the config keys `organisation_model`, `organisation_extra_tabs`, `organisation_sort_field` and `organisation_sort_direction` to `relation_model`, `relation_extra_tabs`, `relation_sort_field` and `relation_sort_direction`. The create-table revisions were changed in place and now create `crm_relation` and `crm_contact_relation`
- The relation `name` column is split into `first_name`, `last_name` and `organisation_name`, and `VAT` is renamed to `vat_number`; the relation index now sorts by `last_name` by default
- `organisation_extra_filters` is replaced by `relation_manager_filters`, which takes filter class name strings instead of filter instances
- `extra_modules` now takes class name strings instead of manager instances; the service provider instantiates them

### Features

- Add the `relation_extra_header_actions` config key to append actions to the relation index header
- Make contact/country managers optional and resolve header actions in service provider
- Add address_box_number to the relation table and the address components
- Move relation index/create/edit components to overridable model methods and add editable, deletable and pagination config

### Fixes

- Capitalise country names with ucwords in enum and __toString

### Other changes

- Show all name fields in the relation index and compose the full name in __toString
- Downgrade Node to 20.11.1
- Update the README and CLAUDE.md for the Relation rename, header actions, optional managers, model component methods and the renamed config keys

## v0.1.0 - 2026-03-20

### Features

- Add SearchableInterface and implement on Contact and Organisation models
- Add Country::enum() static method for use in admin filters
- Add search, filters and propagate custom models through admin managers
- Add CreateNote action for inline note editing
- Integrate note create/edit actions into managers
- Add extra_tabs kwarg to ContactManager and OrganisationManager
- Add extra_filters and configurable sorting to ContactManager and OrganisationManager
- Add crm.extra_modules config key to register additional portal modules

### Other changes

- Add README usage/extension guide and CLAUDE.md project context
- Extract address block and CreateNote registration into reusable helpers
- Clarify config/crm.php format — flat array, keys without crm. prefix
