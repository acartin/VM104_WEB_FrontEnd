<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_title', 100)->nullable()->after('email');
            $table->string('available_status', 20)->default('offline')->after('job_title');
        });

        // 2. Modify contact methods table to point to users
        // First, drop the FK to contacts
        Schema::table('crm_contact_methods', function (Blueprint $table) {
            $table->dropForeign('crm_contact_methods_contact_id_fkey');
            $table->dropIndex('crm_contact_methods_contact_channel_value_unique');
        });

        // Rename column contact_id to user_id
        Schema::table('crm_contact_methods', function (Blueprint $table) {
            $table->renameColumn('contact_id', 'user_id');
        });

        // Truncate table to remove invalid IDs before linking to users
        DB::table('crm_contact_methods')->truncate();

        // Add FK to users and recreate index
        Schema::table('crm_contact_methods', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'channel_id', 'value'], 'crm_methods_user_channel_val_unique');
        });

        // 3. Drop crm_contacts table with CASCADE to handle dependencies
        DB::statement('DROP TABLE IF EXISTS crm_contacts CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate crm_contacts (simplified for rollback)
        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('first_name');
            $table->timestamps();
        });

        Schema::table('crm_contact_methods', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'contact_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'available_status']);
        });
    }
};
