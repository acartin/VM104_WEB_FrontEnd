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
        Schema::dropIfExists('crm_contact_roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('crm_contact_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->boolean('can_receive_leads')->default(false);
            $table->timestamps();
        });
    }
};
