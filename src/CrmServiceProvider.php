<?php

namespace Tnt\Crm;

use dry\admin\Portal;
use Oak\Contracts\Config\RepositoryInterface;
use Oak\Contracts\Container\ContainerInterface;
use Oak\Migration\MigrationManager;
use Oak\Migration\Migrator;
use Oak\ServiceProvider;
use Tnt\Crm\Admin\ContactManager;
use Tnt\Crm\Admin\CountryManager;
use Tnt\Crm\Admin\RelationManager;
use Tnt\Crm\Contracts\CrmPortalInterface;
use Tnt\Crm\Enum\Language;
use Tnt\Crm\Model\Contact;
use Tnt\Crm\Model\Relation;

class CrmServiceProvider extends ServiceProvider
{
    /**
     * @param ContainerInterface $app
     * @return mixed|void
     */
    public function register(ContainerInterface $app)
    {
        $app->set(CrmPortalInterface::class, function () use ($app) {
            return $this->registerPortal($app);
        });
    }

    /**
     * @param ContainerInterface $app
     * @return mixed|void
     */
    public function boot(ContainerInterface $app)
    {
        if ($app->isRunningInConsole()) {

            $migrator = $app->getWith(Migrator::class, [
                'name' => 'crm',
            ]);

            $migrator->setRevisions($this->getMigrations());

            $app->get(MigrationManager::class)->addMigrator($migrator);
        }
    }

    public function provides(): array
    {
        return [CrmPortalInterface::class];
    }

    /**
     * @param ContainerInterface $app
     * @return Portal
     */
    private function registerPortal(ContainerInterface $app)
    {
        $config = $app->get(RepositoryInterface::class);

        $relationModel = $config->get('crm.relation_model', Relation::class);
        $contactModel = $config->get('crm.contact_model', Contact::class);
        $countryManager = $config->get('crm.country_manager', true);
        $languageEnabled = $config->get('crm.language_enabled', true);
        $languageOptions = $languageEnabled ? $config->get('crm.language_options', Language::enum()) : [];

        $modules = [
            new RelationManager([
                'model' => $relationModel,
                'contact_model' => $contactModel,
                'extra_tabs' => $config->get('crm.relation_extra_tabs', []),
                'extra_filters' => $config->get('crm.relation_manager_filters', []),
                'extra_header_actions' => array_map(
                    fn($class) => (new $class())->create_link(),
                    $config->get('crm.relation_extra_header_actions', [])
                ),
                'sort_field' => $config->get('crm.relation_sort_field', 'last_name'),
                'sort_direction' => $config->get('crm.relation_sort_direction', \dry\orm\sort\StaticSorter::ASC),
                'country_filter' => $countryManager,
                ...array_filter([
                    'pagination_amount' => $config->get('crm.relation_manager_pagination_amount'),
                    'manager_editable' => $config->get('crm.relation_manager_editable'),
                    'manager_deletable' => $config->get('crm.relation_manager_deletable'),
                ], fn($v) => $v !== null),
            ]),
        ];

        if ($config->get('crm.contact_manager', true)) {
            $modules[] = new ContactManager([
                'model' => $contactModel,
                'relation_model' => $relationModel,
                'country_filter' => $countryManager,
                'language_enabled' => $languageEnabled,
                'language_options' => $languageOptions,
                'extra_tabs' => $config->get('crm.contact_extra_tabs', []),
                'extra_filters' => $config->get('crm.contact_extra_filters', []),
                'sort_field' => $config->get('crm.contact_sort_field', 'first_name'),
                'sort_direction' => $config->get('crm.contact_sort_direction', \dry\orm\sort\StaticSorter::ASC),
            ]);
        }

        if ($countryManager) {
            $modules[] = new CountryManager();
        }

        $modules = array_merge($modules, array_map(fn($class) => new $class(), $config->get('crm.extra_modules', [])));

        return new Portal('crm', 'CRM', $modules, [
            "icon" => "account_tree",
            // "icon" => "mediation",
        ]);
    }

    /** @return array<int, object> */
    public function getMigrations(): array
    {
        $revisions = [];

        foreach (glob(__DIR__ . '/Revisions/*.php') as $file) {
            if (basename($file) === 'DatabaseRevision.php') {
                continue;
            }

            $revisionInstance = require $file;

            if (is_object($revisionInstance)) {
                $revisions[] = $revisionInstance;
            }
        }

        return $revisions;
    }
}
