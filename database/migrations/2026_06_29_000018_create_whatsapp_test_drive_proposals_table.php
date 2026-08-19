<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Propuestas de prueba de manejo que deja el bot para que un asesor las confirme.
 * La cita real (appointments) se crea recién al confirmar desde el panel de chat.
 */
class CreateWhatsappTestDriveProposalsTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_test_drive_proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('chat_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->string('phone');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->timestamp('proposed_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(45);
            $table->text('notes')->nullable();
            $table->string('status', 12)->default('pending'); // pending | confirmed | dismissed
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['chat_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_test_drive_proposals');
    }
}
