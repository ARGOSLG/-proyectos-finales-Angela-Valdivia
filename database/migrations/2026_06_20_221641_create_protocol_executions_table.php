<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocol_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('protocol_id')
                  ->constrained('protocols')
                  ->cascadeOnDelete();

            $table->foreignUuid('incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete();

            // Operador que ejecutó el protocolo
            $table->uuid('operator_id')->nullable();
            $table->foreign('operator_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // Estado de cada paso del protocolo en JSON
            $table->json('steps_state');

            $table->enum('status', ['in_progress', 'completed', 'cancelled'])
                  ->default('in_progress');

            // Si se envió señal de desaceleración
            $table->boolean('motor_cut_sent')->default(false);

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_executions');
    }
};