<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentGoalsTable extends Migration
{
    public function up()
    {
        Schema::create('agent_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // agente
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->date('month'); // Primer día del mes: 2026-04-01

            // Metas del mes
            $table->unsignedInteger('leads_goal')->default(0);
            $table->unsignedInteger('quotes_goal')->default(0);
            $table->unsignedInteger('conversions_goal')->default(0);

            $table->unsignedBigInteger('set_by'); // admin que configuró las metas
            $table->foreign('set_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'month']); // un registro por agente por mes
            $table->index(['company_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_goals');
    }
}
