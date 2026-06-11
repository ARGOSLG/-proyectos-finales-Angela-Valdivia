<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Vehículo y conductor del viaje
            $table->foreignUuid('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            $table->foreignUuid('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            $table->foreignUuid('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // Tiempos del viaje
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            // Métricas del viaje — igual que los archivos de demo
            $table->decimal('distance_km', 10, 2)->default(0);
            $table->decimal('fuel_used', 10, 2)->default(0);
            $table->integer('drive_time_minutes')->default(0);
            $table->integer('idle_time_minutes')->default(0);
            $table->decimal('idle_percentage', 5, 2)->default(0);

            // Velocidad máxima registrada en el viaje
            $table->decimal('max_speed_kmh', 6, 2)->nullable();

            // Estado del viaje
            $table->enum('status', ['active', 'completed', 'cancelled'])
                  ->default('active');

            // Índices para reportes
            $table->index(['company_id', 'started_at']);
            $table->index(['vehicle_id', 'started_at']);
            $table->index(['driver_id', 'started_at']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};