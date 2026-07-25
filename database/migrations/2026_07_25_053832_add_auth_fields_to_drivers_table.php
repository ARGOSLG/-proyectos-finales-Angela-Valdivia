<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Contraseña para login con ID/contraseña
            $table->string('password')->nullable()->after('license_number');

            // Token NFC — placeholder para cuando se tenga el hardware
            $table->string('nfc_token')->nullable()->unique()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['password', 'nfc_token']);
        });
    }
};