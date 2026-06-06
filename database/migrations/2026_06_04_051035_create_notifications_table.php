<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Alerta que disparó esta notificación
            $table->foreignUuid('alert_id')
                  ->constrained('alerts')
                  ->cascadeOnDelete();

            // A quién se le notificó — puede ser conductor o contacto de emergencia
            $table->foreignUuid('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            // Canal de envío
            $table->enum('channel', [
                'whatsapp',  // Bot ARGOS en WhatsApp
                'sms',       // SMS como fallback
                'call',      // llamada de verificación
                'email',     // correo electrónico
                'push',      // notificación push en la app
            ]);

            // A quién se envió — número de teléfono o email
            $table->string('recipient');

            // Mensaje enviado — guardamos el texto exacto para auditoría
            $table->text('message');

            // Estado del envío
            $table->enum('status', [
                'pending',   // en cola, aún no se envía
                'sent',      // enviado exitosamente
                'failed',    // falló el envío
                'delivered', // confirmación de entrega (WhatsApp lo soporta)
            ])->default('pending');

            // Cuándo se envió
            $table->timestamp('sent_at')->nullable();

            // Índice para monitorear notificaciones fallidas
            $table->index(['status', 'channel']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};