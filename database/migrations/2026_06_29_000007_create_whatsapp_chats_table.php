<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappChatsTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('phone');
            $table->string('contact_name')->nullable();
            $table->boolean('bot_paused')->default(false);
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('needs_human_at')->nullable();
            $table->string('needs_human_reason')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'phone']);
            $table->index(['company_id', 'last_message_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_chats');
    }
}
