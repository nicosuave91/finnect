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
        Schema::create('compliance_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('audit_type');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['tenant_id', 'audit_type']);
            $table->index(['tenant_id', 'entity_type', 'entity_id']);
            $table->index(['tenant_id', 'created_at']);
        });


        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE compliance_audits ENABLE ROW LEVEL SECURITY;');
            DB::statement("CREATE POLICY tenant_isolation ON compliance_audits USING (tenant_id = current_setting('app.tenant_id')::bigint);");
            TenantMigrate::run('compliance_audits');
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {


        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP POLICY IF EXISTS tenant_isolation ON compliance_audits;');
            DB::statement('ALTER TABLE compliance_audits DISABLE ROW LEVEL SECURITY;');
        }


        Schema::dropIfExists('compliance_audits');
    }
};