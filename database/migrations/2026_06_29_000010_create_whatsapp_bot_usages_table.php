<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappBotUsagesTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_bot_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('phone');
            $table->string('period', 7); // YYYY-MM — la conversación cuenta en el mes que se abrió
            // Nullable a propósito: MySQL en modo estricto no permite dos timestamps
            // NOT NULL sin default. Siempre se setean en WhatsappBotUsage::touchWindow().
            $table->timestamp('window_started_at')->nullable();
            $table->timestamp('window_expires_at')->nullable();
            $table->decimal('anthropic_cost', 12, 6)->default(0);
            $table->decimal('whatsapp_cost', 12, 6)->default(0);
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->unsignedInteger('messages_count')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'period']);
            $table->index(['company_id', 'phone', 'window_expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_bot_usages');
    }
}
