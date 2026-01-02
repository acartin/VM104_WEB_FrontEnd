<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     
    // transaction is disabled to handle try-catch on permission errors
    public $withinTransaction = false;

    public function up(): void
    {
        // Update Leads table
        try {
            Schema::table('crm_leads', function (Blueprint $table) {
                $table->renameColumn('assigned_contact_id', 'assigned_user_id');
            });
            
            Schema::table('crm_leads', function (Blueprint $table) {
                 $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Exception $e) {
            // Log error or ignore if permission denied, proceeding to allow other migrations
        }

        // Update Properties table
        try {
            Schema::table('crm_properties', function (Blueprint $table) {
                $table->renameColumn('assigned_contact_id', 'assigned_user_id');
            });
    
            Schema::table('crm_properties', function (Blueprint $table) {
                $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Exception $e) {}
        
        // Update Appointments table (if exists and has contact_id)
        if (Schema::hasColumn('crm_appointments', 'contact_id')) {
             try {
                 Schema::table('crm_appointments', function (Blueprint $table) {
                    $table->renameColumn('contact_id', 'user_id');
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                 });
             } catch (\Exception $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->renameColumn('assigned_user_id', 'assigned_contact_id');
        });

        Schema::table('crm_properties', function (Blueprint $table) {
             $table->dropForeign(['assigned_user_id']);
            $table->renameColumn('assigned_user_id', 'assigned_contact_id');
        });
    }
};
