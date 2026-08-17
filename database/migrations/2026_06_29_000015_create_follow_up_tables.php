<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowUpTables extends Migration
{
    public function up()
    {
        Schema::create('follow_up_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('trigger')->default('lead_created'); // lead_created | manual
            $table->boolean('is_active')->default(true);
            $table->boolean('stop_on_reply')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('follow_up_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sequence_id');
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('delay_hours')->default(0); // desde el paso anterior (o desde la inscripción)
            $table->string('channel')->default('whatsapp');     // whatsapp | email
            $table->unsignedBigInteger('message_template_id')->nullable();
            $table->string('subject')->nullable();              // email (si no usa plantilla)
            $table->text('body')->nullable();                   // si no usa plantilla
            $table->timestamps();
            $table->index('sequence_id');
        });

        Schema::create('follow_up_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sequence_id');
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedInteger('current_position')->default(0);
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->string('status')->default('active');        // active | completed | stopped
            $table->string('stopped_reason')->nullable();
            $table->timestamps();
            $table->unique(['sequence_id', 'lead_id']);
            $table->index(['status', 'next_run_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('follow_up_enrollments');
        Schema::dropIfExists('follow_up_steps');
        Schema::dropIfExists('follow_up_sequences');
    }
}
