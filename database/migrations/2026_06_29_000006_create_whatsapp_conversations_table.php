<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappConversationsTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('phone');
            $table->string('contact_name')->nullable();
            $table->string('direction'); // inbound|outbound
            $table->text('message')->nullable();
            $table->string('message_type')->default('text'); // text|image|audio|...
            $table->boolean('is_human')->default(false);     // outbound escrito por una persona
            $table->string('wam_id')->nullable()->unique();  // idempotencia
            $table->timestamp('window_started_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'phone', 'created_at']);
            $table->index(['direction', 'is_human']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
}
