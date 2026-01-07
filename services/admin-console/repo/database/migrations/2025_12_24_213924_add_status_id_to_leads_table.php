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
        // Check if table exists (it might not since we dropped it)
        if (!Schema::hasTable('crm_leads')) {
            Schema::create('crm_leads', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('crm_clients');
                $table->foreignId('source_id')->constrained('crm_lead_sources'); // LeadSource is likely integer
                $table->uuid('origin_channel_id')->nullable();
                $table->foreignUuid('assigned_user_id')->nullable()->constrained('users');
                
                $table->string('full_name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->decimal('declared_income', 15, 2)->nullable();
                $table->decimal('current_debts', 15, 2)->nullable();
                $table->char('financial_currency_id', 3)->nullable();
                
                $table->string('status')->nullable(); // Legacy
                $table->foreignId('status_id')->nullable()->constrained('crm_lead_statuses')->nullOnDelete();
                
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
             // Fallback if table somehow exists (should not happen based on plan)
             Schema::table('crm_leads', function (Blueprint $table) {
                $table->foreignId('status_id')->nullable()->constrained('crm_lead_statuses')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
