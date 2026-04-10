<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentRewardsTables extends Migration
{
    public function up()
    {
        // Reglas de recompensas (configura el super_admin)
        Schema::create('agent_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Premio al mejor vendedor"
            $table->text('description')->nullable();
            $table->unsignedInteger('min_conversions'); // mínimo de conversiones para calificar
            $table->unsignedInteger('max_conversions')->nullable(); // null = sin límite superior
            $table->string('reward_type'); // bonus, commission_percent, gift, recognition, vacation_day
            $table->decimal('reward_value', 12, 2)->default(0); // monto o porcentaje
            $table->string('reward_currency', 3)->default('CRC'); // CRC, USD
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'min_conversions']);
        });

        // Registro de recompensas otorgadas a agentes
        Schema::create('agent_reward_grants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // agente premiado
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('reward_id');
            $table->foreign('reward_id')->references('id')->on('agent_rewards')->cascadeOnDelete();
            $table->date('month'); // Mes del logro
            $table->unsignedInteger('conversions_count'); // cuántas conversiones tuvo ese mes
            $table->unsignedInteger('leads_count');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('granted_by');
            $table->foreign('granted_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->unique(['user_id', 'reward_id', 'month']);
            $table->index(['user_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_reward_grants');
        Schema::dropIfExists('agent_rewards');
    }
}
