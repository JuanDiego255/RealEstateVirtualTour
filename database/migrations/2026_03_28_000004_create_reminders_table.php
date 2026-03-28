<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Relaciones polimórficas (puede ser para lead, appointment, property, etc.)
            $table->nullableMorphs('remindable');

            // Información del recordatorio
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Fecha y hora del recordatorio
            $table->datetime('remind_at');

            // Estado
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('dismissed_at')->nullable();

            // Notificaciones
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->boolean('email_notification')->default(true);

            // Recurrencia
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable(); // Cada cuántos días/semanas/etc.
            $table->date('recurrence_ends_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'remind_at']);
            $table->index(['remind_at', 'is_completed']);
            $table->index(['company_id', 'remind_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
