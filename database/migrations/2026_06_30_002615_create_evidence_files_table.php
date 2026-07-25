<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete();

            $table->foreignUuid('alert_id')
                  ->nullable()
                  ->constrained('alerts')
                  ->nullOnDelete();

            $table->string('file_path');
            $table->enum('file_type', ['video', 'photo', 'audio']);
            $table->integer('duration_seconds')->nullable();
            $table->string('camera_label')->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('synced');
            $table->timestamp('recorded_at');
            
            $table->index(['incident_id', 'file_type']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_files');
    }
};