<?php

namespace App\Console\Commands;

use App\Services\DummyData\DummyDataMigrationService;
use Illuminate\Console\Command;

class DummyDataMigrateCommand extends Command
{
    protected $signature = 'dummydata:migrate {--tenant= : Restrict import to a single tenant slug}';

    protected $description = 'Import Midigator dummy JSON fixtures into existing tenants and managers';

    public function handle(DummyDataMigrationService $service): int
    {
        $tenantSlug = $this->option('tenant') ?: null;

        $this->info($tenantSlug
            ? "Seeding dummy data for tenant: $tenantSlug"
            : 'Seeding dummy data across all active tenants');

        $results = $service->run($tenantSlug);

        $this->table(
            ['Entity', 'Inserted'],
            collect($results)->map(fn ($count, $entity) => [$entity, $count])->values()->all(),
        );

        return self::SUCCESS;
    }
}
