<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quien registró la actividad

            // Tipo de actividad
            $table->enum('type', ['call', 'email', 'whatsapp', 'visit', 'meeting', 'note', 'status_change', 'other']);

            // Detalles
            $table->string('subject')->nullable(); // Asunto breve
            $table->text('description')->nullable(); // Descripción detallada

            // Para llamadas
            $table->enum('call_result', ['answered', 'no_answer', 'busy', 'voicemail', 'callback_requested'])->nullable();
            $table->integer('call_duration')->nullable(); // Duración en segundos

            // Para visitas
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');

            // Cambio de estado (si aplica)
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();

            $table->timestamp('activity_at')->useCurrent(); // Cuándo ocurrió la actividad
            $table->timestamps();

            $table->index(['lead_id', 'activity_at']);
            $table->index(['user_id', 'activity_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_activities');
    }
}
