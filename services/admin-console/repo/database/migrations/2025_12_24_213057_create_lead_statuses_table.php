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
        Schema::create('crm_lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('key', 50)->unique()->index();
            $table->string('color', 20)->default('gray'); // success, danger, warning, info, gray
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_lead_statuses');
    }
};
