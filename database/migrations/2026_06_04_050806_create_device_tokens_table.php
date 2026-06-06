<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Vehículo al que pertenece este dispositivo
            $table->foreignUuid('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // Tipo de dispositivo embarcado
            $table->enum('device_type', [
                'arduino_mic',  // micrófono con detección de palabras clave
                'gps_module',   // módulo GPS con SIM multi-carrier
            ]);

            // Token único que se graba en el firmware del dispositivo
            // Es como la contraseña del hardware — nunca cambia
            $table->string('token')->unique();

            // Control
            $table->boolean('active')->default(true);
            $table->timestamp('last_seen_at')->nullable(); // última vez que se conectó

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};