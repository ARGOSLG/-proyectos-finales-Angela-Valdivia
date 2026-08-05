<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('driver_id')
                  ->constrained('drivers')
                  ->cascadeOnDelete();

            // Alert generada por este reporte — para trazabilidad
            $table->foreignUuid('alert_id')
                  ->nullable()
                  ->constrained('alerts')
                  ->nullOnDelete();

            $table->string('tipo'); // emergencia, auxilio_vial, palabra_clave, comportamiento
            $table->string('estado')->default('enviado'); // enviado, confirmado, en_progreso, resuelto, cancelado

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('description')->nullable();

            // Datos extra sin necesidad de migrar
            $table->json('metadata')->nullable();

            $table->index(['driver_id', 'estado']);
            $table->index(['driver_id', 'created_at']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};