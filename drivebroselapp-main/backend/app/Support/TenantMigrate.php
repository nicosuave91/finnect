<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantMigrate
{
    /**
     * Run row level security statements for existing tenants.
     */
    public static function run(string $table): void
    {
        Tenant::all()->each(function (Tenant $tenant) use ($table) {
            DB::statement("SET app.tenant_id = {$tenant->id}");
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY;");
            DB::statement("CREATE POLICY IF NOT EXISTS tenant_isolation ON {$table} USING (tenant_id = current_setting('app.tenant_id')::bigint);");
        });
        DB::statement('RESET app.tenant_id');
    }
}
