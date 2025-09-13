<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Support\TenantMigrate;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('nmls_id')->nullable();
            $table->string('license_number')->nullable();
            $table->json('profile_data')->nullable();
            $table->json('compliance_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'nmls_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ENABLE ROW LEVEL SECURITY;');
            DB::statement("CREATE POLICY tenant_isolation ON users USING (tenant_id = current_setting('app.tenant_id')::bigint);");
            TenantMigrate::run('users');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP POLICY IF EXISTS tenant_isolation ON users;');
            DB::statement('ALTER TABLE users DISABLE ROW LEVEL SECURITY;');
        }
        Schema::dropIfExists('users');
    }
};