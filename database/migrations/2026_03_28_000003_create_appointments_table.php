<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Agente responsable
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');

            // Información de la cita
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['property_visit', 'vehicle_visit', 'meeting', 'call', 'video_call', 'signing', 'other'])->default('meeting');

            // Fecha y hora
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->boolean('all_day')->default(false);

            // Ubicación
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Cliente (si no es un lead registrado)
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();

            // Estado
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('cancellation_reason')->nullable();

            // Notas post-cita
            $table->text('outcome_notes')->nullable();
            $table->enum('outcome', ['successful', 'follow_up_needed', 'not_interested', 'pending'])->nullable();

            // Recordatorios
            $table->boolean('reminder_sent')->default(false);
            $table->integer('reminder_minutes')->default(60); // Minutos antes para recordatorio

            // Color para calendario
            $table->string('color')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'starts_at']);
            $table->index(['user_id', 'starts_at']);
            $table->index(['status', 'starts_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
