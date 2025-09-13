<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('loan_number')->unique();
            $table->string('status')->default('application');
            $table->foreignId('loan_officer_id')->constrained('users');
            $table->foreignId('borrower_id')->nullable()->constrained('borrowers');
            $table->foreignId('co_borrower_id')->nullable()->constrained('borrowers');
            $table->json('loan_data');
            $table->json('compliance_data');
            $table->json('workflow_data');
            $table->decimal('loan_amount', 15, 2);
            $table->decimal('interest_rate', 5, 4)->nullable();
            $table->string('loan_type');
            $table->string('property_type');
            $table->string('occupancy_type');
            $table->string('purpose');
            $table->date('application_date');
            $table->date('closing_date')->nullable();
            $table->date('funding_date')->nullable();
            $table->json('vendor_integrations')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'loan_officer_id']);
            $table->index(['tenant_id', 'application_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};