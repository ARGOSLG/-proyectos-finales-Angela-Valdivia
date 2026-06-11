<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Alerta que originó este incidente
            // Una alerta crítica escala a incidente activo
            $table->foreignUuid('alert_id')
                  ->constrained('alerts')
                  ->cascadeOnDelete();

            // Conductor y vehículo involucrados
            $table->foreignUuid('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            $table->foreignUuid('vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->nullOnDelete();

            // Operador que está atendiendo el incidente
            $table->foreignUuid('operator_id')
             ->nullable()
             ->constrained('users')
             ->nullOnDelete();
              

            // Tipo de incidente
            $table->enum('type', [
                'security',     // robo, asalto, palabra clave detectada
                'medical',      // conductor con malestar
                'accident',     // accidente vial
                'mechanical',   // falla del vehículo
                'behavior',     // conducta indebida del conductor
                'other',
            ]);

            // Si fue keyword_detected — qué palabra se detectó
            $table->string('keyword_detected')->nullable();

            // Ubicación donde ocurrió el incidente
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Estado del incidente
            $table->enum('status', [
                'open',         // recién creado, sin atender
                'in_progress',  // operador lo está atendiendo
                'resolved',     // resuelto satisfactoriamente
                'false_alarm',  // fue una falsa alarma
            ])->default('open');

            // Tiempos del incidente
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();

            // Índices para búsquedas rápidas
            $table->index(['status', 'started_at']);
            $table->index(['driver_id', 'started_at']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};