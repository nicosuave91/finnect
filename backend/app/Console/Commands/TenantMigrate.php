<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate';

    protected $description = 'Run migrations for each tenant database';

    public function handle(): int
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant {$tenant->id} ({$tenant->name})");

            config(['database.connections.tenant.database' => $tenant->database]);
            Artisan::call('migrate', [
                '--path' => 'database/migrations',
                '--database' => 'tenant',
                '--force' => true,
            ]);
        }

        $this->info('Tenant migrations completed');
        return Command::SUCCESS;
    }
}

