<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            $table->string('name');
            $table->string('trigger_type'); // keyword_detected, sos_button, speeding
            $table->json('steps');          // pasos del protocolo
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};