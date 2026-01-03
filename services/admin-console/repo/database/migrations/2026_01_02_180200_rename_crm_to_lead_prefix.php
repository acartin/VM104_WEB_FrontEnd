<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'crm_leads' => 'lead_leads',
            'crm_clients' => 'lead_clients',
            'crm_users' => 'lead_users',
            'crm_integrations' => 'lead_integrations',
            'crm_lead_statuses' => 'lead_statuses',
            'crm_lead_sources' => 'lead_sources',
            'crm_properties' => 'lead_properties',
            'crm_communication_channels' => 'lead_communication_channels',
            'crm_contact_methods' => 'lead_contact_methods',
            'crm_countries' => 'lead_countries',
            'crm_currencies' => 'lead_currencies',
            'crm_property_types' => 'lead_property_types',
            'crm_appointments' => 'lead_appointments',
            'crm_channel_categories' => 'lead_channel_categories',
            'crm_client_channels' => 'lead_client_channels',
            'crm_client_user' => 'lead_client_user',
            'crm_lead_status_history' => 'lead_status_history',
        ];

        foreach ($tables as $oldName => $newName) {
            DB::statement("ALTER TABLE $oldName RENAME TO $newName");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'lead_leads' => 'crm_leads',
            'lead_clients' => 'crm_clients',
            'lead_users' => 'crm_users',
            'lead_integrations' => 'crm_integrations',
            'lead_statuses' => 'crm_lead_statuses',
            'lead_sources' => 'crm_lead_sources',
            'lead_properties' => 'crm_properties',
            'lead_communication_channels' => 'crm_communication_channels',
            'lead_contact_methods' => 'crm_contact_methods',
            'lead_countries' => 'crm_countries',
            'lead_currencies' => 'crm_currencies',
            'lead_property_types' => 'crm_property_types',
            'lead_appointments' => 'crm_appointments',
            'lead_channel_categories' => 'crm_channel_categories',
            'lead_client_channels' => 'crm_client_channels',
            'lead_client_user' => 'crm_client_user',
            'lead_status_history' => 'crm_lead_status_history',
        ];

        foreach ($tables as $oldName => $newName) {
            DB::statement("ALTER TABLE $oldName RENAME TO $newName");
        }
    }
};
