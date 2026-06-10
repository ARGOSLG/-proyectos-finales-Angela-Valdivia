<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rol del usuario en el sistema
            $table->enum('role', ['admin', 'operator', 'supervisor'])
                  ->default('operator')
                  ->after('password');

            // A qué empresa pertenece — nullable para el super admin
            $table->foreignUuid('company_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('companies')
                  ->nullOnDelete();

            // Usuario activo o inactivo
            $table->boolean('active')
                  ->default(true)
                  ->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'company_id', 'active']);
        });
    }
};