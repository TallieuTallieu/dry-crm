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
use Tnt\Crm\Admin\OrganisationManager;
use Tnt\Crm\Contracts\CrmPortalInterface;
use Tnt\Crm\Enum\Language;
use Tnt\Crm\Model\Contact;
use Tnt\Crm\Model\Organisation;

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

        $organisationModel = $config->get('crm.organisation_model', Organisation::class);
        $contactModel = $config->get('crm.contact_model', Contact::class);
        $languageOptions = $config->get('crm.language_options', Language::enum());

        $modules = [
            new OrganisationManager([
                'model' => $organisationModel
            ]),
            new ContactManager([
                "language_options" => $languageOptions,
                'model' => $contactModel
            ]),
            new CountryManager(),
        ];

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
