<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('activity_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('name');
            $table->enum('type', ['call', 'email', 'whatsapp', 'visit', 'meeting', 'note', 'other']);
            $table->string('subject')->nullable();
            $table->text('body'); // Template body with {{name}}, {{property}}, etc.
            $table->string('stage')->nullable(); // null = all stages, or specific stage
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->index(['company_id', 'type', 'stage']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_templates');
    }
}
