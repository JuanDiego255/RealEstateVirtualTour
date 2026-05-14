<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePipelineRulesTable extends Migration
{
    public function up()
    {
        Schema::create('pipeline_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('trigger_stage'); // Stage that triggers the rule
            $table->unsignedSmallInteger('days_inactive'); // Days without activity
            $table->enum('action', ['alert', 'create_reminder', 'notify_admin'])->default('alert');
            $table->string('action_message')->nullable(); // Message for the action
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pipeline_rules');
    }
}
