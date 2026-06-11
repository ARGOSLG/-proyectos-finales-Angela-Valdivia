<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // A qué empresa, conductor y vehículo pertenece esta alerta
            $table->foreignUuid('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            $table->foreignUuid('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            $table->foreignUuid('vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->nullOnDelete();

            // Tipo de alerta — qué detectó el sistema
            $table->enum('type', [
                'keyword_detected',   // Arduino detectó palabra clave
                'phone_usage',        // cámara detectó uso de teléfono
                'drowsiness',         // cámara detectó somnolencia
                'speeding',           // velocidad excesiva
                'harsh_braking',      // frenada brusca
                'sos_button',         // conductor presionó SOS
                'alcohol_detected',   // sensor de alcohol
                'biometric_alert',    // frecuencia cardíaca elevada
                'geofence_exit',      // salió de zona permitida
                'manual',             // creada manualmente por operador
            ]);

            // Severidad — determina el color en el panel y la urgencia
            $table->enum('severity', ['critical', 'warning', 'info'])
                  ->default('warning');

            // De dónde vino la alerta
            $table->enum('source', [
                'arduino',    // micrófono Arduino
                'camera',     // cámara IA del vehículo
                'gps_module', // módulo GPS
                'manual',     // operador la creó a mano
                'system',     // generada automáticamente por reglas
            ]);

            // Datos extra en JSON — flexible según el tipo de alerta
            // Ej: { "keyword": "ayuda", "confidence": 0.95, "duration": "0:15s" }
            $table->json('metadata')->nullable();

            // Estado del ciclo de vida de la alerta
            $table->enum('status', ['active', 'in_progress', 'resolved', 'false_alarm'])
                  ->default('active');

            // Quién y cuándo la resolvió
            
            $table->foreignUuid('resolved_by')
             ->nullable()
             ->constrained('users')
             ->nullOnDelete();

            // Índices para el panel — consultas frecuentes por empresa y estado
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'severity']);
            $table->index(['created_at']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};