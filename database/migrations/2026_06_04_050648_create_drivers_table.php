<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pertenece a una empresa — si se borra la empresa, se borran sus conductores
            $table->foreignUuid('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            // Datos personales del conductor
            $table->string('name');
            $table->string('employee_id')->nullable(); // número de empleado interno
            $table->string('phone', 20)->nullable();
            $table->string('license_number')->nullable(); // número de licencia

            // Contacto de emergencia — se notifica en caso de incidente crítico
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Estado actual del conductor
            $table->enum('status', ['available', 'on_route', 'resting', 'inactive'])
                  ->default('available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};