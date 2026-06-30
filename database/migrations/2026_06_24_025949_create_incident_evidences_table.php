<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pertenece a un incidente
            $table->foreignUuid('incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete();

            // Quién subió la evidencia (operador desde la app o panel)
            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Tipo de archivo
            $table->enum('type', ['photo', 'video'])->default('photo');

            // Ruta del archivo en storage
            $table->string('file_path');

            // Metadata del archivo
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // solo para videos

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_evidences');
    }
};