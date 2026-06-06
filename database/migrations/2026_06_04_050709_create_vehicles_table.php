<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pertenece a una empresa
            $table->foreignUuid('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // Conductor asignado — nullable porque puede estar sin asignar
            $table->foreignUuid('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            // Datos del vehículo
            $table->string('unit_number');        // número interno ej: EW1015
            $table->string('plate')->unique();    // placa única
            $table->string('brand')->nullable();  // marca ej: Kenworth
            $table->string('model')->nullable();  // modelo ej: T680

            // Estado actual
            $table->enum('status', ['available', 'on_route', 'maintenance', 'inactive'])
                  ->default('available');

            // Última posición GPS conocida
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamp('location_at')->nullable(); // cuándo fue la última ubicación

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};