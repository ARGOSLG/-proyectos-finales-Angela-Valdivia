<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safe_points', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            $table->string('name');
            $table->string('address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // Tipo de punto seguro
            $table->enum('type', [
                'gas_station', // gasolinera
                'police',      // policía / cuartel
                'hospital',    // hospital / cruz roja
                'base',        // base de la empresa
                'other',       // otro
            ])->default('other');

            $table->boolean('active')->default(true);

            // Índice para búsquedas por ubicación
            $table->index(['lat', 'lng']);
            $table->index(['company_id', 'active']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safe_points');
    }
};