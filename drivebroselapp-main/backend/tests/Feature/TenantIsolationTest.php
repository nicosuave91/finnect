<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_tenant_access_is_denied(): void
    {
        $tenantA = Tenant::create([
            'name' => 'Tenant A',
            'domain' => 'a.test',
            'database' => 'testing',
        ]);

        $tenantB = Tenant::create([
            'name' => 'Tenant B',
            'domain' => 'b.test',
            'database' => 'testing',
        ]);

        DB::statement("SET app.tenant_id = {$tenantA->id}");
        DB::table('users')->insert([
            'tenant_id' => $tenantA->id,
            'first_name' => 'Alice',
            'last_name' => 'A',
            'email' => 'alice@example.com',
            'password' => 'secret',
        ]);

        DB::statement("SET app.tenant_id = {$tenantB->id}");
        DB::table('users')->insert([
            'tenant_id' => $tenantB->id,
            'first_name' => 'Bob',
            'last_name' => 'B',
            'email' => 'bob@example.com',
            'password' => 'secret',
        ]);

        DB::statement("SET app.tenant_id = {$tenantA->id}");
        $usersTenantA = DB::table('users')->pluck('email');
        $this->assertEquals(['alice@example.com'], $usersTenantA->toArray());

        DB::statement("SET app.tenant_id = {$tenantB->id}");
        $usersTenantB = DB::table('users')->pluck('email');
        $this->assertEquals(['bob@example.com'], $usersTenantB->toArray());
    }
}
