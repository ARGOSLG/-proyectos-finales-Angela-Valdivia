<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Vehículo al que pertenece esta ubicación
            $table->foreignUuid('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // Coordenadas GPS
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // Datos del movimiento
            $table->decimal('speed_kmh', 6, 2)->nullable();  // velocidad en km/h
            $table->decimal('heading', 5, 2)->nullable();    // dirección en grados 0-360
            $table->decimal('accuracy_m', 6, 2)->nullable(); // precisión del GPS en metros

            // Datos de conectividad — para el SIM multi-carrier
            $table->string('carrier', 50)->nullable();  // telcel / att / movistar
            $table->integer('signal_dbm')->nullable();  // fuerza de señal

            // Cuándo se registró esta posición (viene del dispositivo)
            $table->timestamp('recorded_at');

            // Índices para consultas rápidas de historial y mapa
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index(['lat', 'lng']);

            // Sin timestamps() — usamos recorded_at directamente
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_locations');
    }
};