<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            
            $table->foreignUuid('user_id')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();
            // Qué acción realizó
            // Ej: 'created', 'updated', 'deleted', 'resolved_alert', 'motor_cut'
            $table->string('action');

            // En qué tabla ocurrió
            $table->string('table_name');

            // ID del registro afectado 
            
            $table->string('record_id')->nullable();

            // Datos antes y después del cambio — para poder revertir si es necesario
            $table->json('payload')->nullable();

            // Desde dónde se hizo — IP del operador o 'system' si fue automático
            $table->string('ip_address', 45)->nullable();
            $table->string('hash', 64)->nullable();

             // SHA256 del registro para evidencia legal
            $table->string('hash', 64)->nullable(); 

            // Índices para búsquedas en la bitácora
            $table->index(['user_id', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index(['action', 'created_at']);

            // Solo created_at — los logs nunca se modifican
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};