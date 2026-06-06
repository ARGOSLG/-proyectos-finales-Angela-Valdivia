<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            // Identificador único — mejor que auto-increment para SaaS multi-tenant
            $table->uuid('id')->primary();

            // Datos de la empresa transportista
            $table->string('name');
            $table->string('rfc', 13)->unique();
            $table->string('contact_email')->unique();
            $table->string('phone', 20)->nullable();

            // Plan SaaS — basic, pro, enterprise
            $table->enum('plan', ['basic', 'pro', 'enterprise'])->default('basic');

            // Empresas inactivas no pueden operar en el sistema
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};