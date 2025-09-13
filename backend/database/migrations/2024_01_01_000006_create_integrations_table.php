<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('status')->default('inactive');
            $table->json('configuration')->nullable();
            $table->json('credentials')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        DB::statement('ALTER TABLE integrations ENABLE ROW LEVEL SECURITY');
        DB::statement("CREATE POLICY tenant_isolation ON integrations USING (tenant_id = current_setting('app.tenant_id')::int)");
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};

