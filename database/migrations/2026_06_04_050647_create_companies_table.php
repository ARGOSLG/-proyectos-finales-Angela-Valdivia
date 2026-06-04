<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            // Identificador único universal — mejor que auto-increment para sistemas distribuidos
            $table->uuid('id')->primary();

            // Datos de la empresa transportista
            $table->string('name');                    // nombre comercial
            $table->string('rfc', 13)->unique();       // RFC mexicano, único por empresa
            $table->string('contact_email')->unique();
            $table->string('phone', 20)->nullable();

            // Control de acceso — empresas inactivas no pueden operar
            $table->boolean('active')->default(true);

            $table->timestamps(); // created_at y updated_at automáticos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};