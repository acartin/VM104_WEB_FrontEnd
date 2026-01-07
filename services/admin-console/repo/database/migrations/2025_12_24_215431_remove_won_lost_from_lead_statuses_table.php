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
        Schema::table('crm_lead_statuses', function (Blueprint $table) {
            $table->dropColumn(['is_won', 'is_lost']);
        });
    }

    public function down(): void
    {
        Schema::table('crm_lead_statuses', function (Blueprint $table) {
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
        });
    }
};
