<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->enum('channel', ['whatsapp', 'email', 'sms'])->default('whatsapp');
            $table->string('stage')->nullable(); // null = all stages
            $table->string('subject')->nullable(); // For email
            $table->text('body'); // Variables: {{name}}, {{agent}}, {{property}}, {{date}}, {{link}}
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->index(['company_id', 'channel', 'stage']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_templates');
    }
}
