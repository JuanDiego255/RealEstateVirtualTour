<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadFollowupsTable extends Migration
{
    public function up()
    {
        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_lead_id');
            $table->foreign('event_lead_id')->references('id')->on('event_leads')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id'); // agente que registra
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Acción realizada
            $table->string('action'); // call, whatsapp, email, meeting, demo, quote_sent, other
            // Resultado del contacto
            $table->string('outcome'); // no_answer, interested, not_interested, thinking, follow_up_later, converted, lost
            $table->text('notes')->nullable();
            $table->timestamp('next_followup_at')->nullable(); // próxima fecha de seguimiento

            $table->timestamps();

            $table->index(['event_lead_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_followups');
    }
}
