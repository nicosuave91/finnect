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
        Schema::create('borrowers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('ssn')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->json('address_data');
            $table->json('employment_data');
            $table->json('income_data');
            $table->json('asset_data');
            $table->json('liability_data');
            $table->json('compliance_data');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'ssn']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE borrowers ENABLE ROW LEVEL SECURITY;');
            DB::statement("CREATE POLICY tenant_isolation ON borrowers USING (tenant_id = current_setting('app.tenant_id')::bigint);");
            TenantMigrate::run('borrowers');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP POLICY IF EXISTS tenant_isolation ON borrowers;');
            DB::statement('ALTER TABLE borrowers DISABLE ROW LEVEL SECURITY;');
        }
        Schema::dropIfExists('borrowers');
    }
};