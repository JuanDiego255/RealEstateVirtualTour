<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappBotSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_bot_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->string('store_name')->nullable();
            $table->string('notify_email')->nullable();
            $table->text('training_profile')->nullable();   // CÓMO HABLA ESTE NEGOCIO (IA + edición)
            $table->text('custom_rules')->nullable();        // REGLAS DEL NEGOCIO
            $table->text('order_instructions')->nullable();  // CÓMO SE CIERRA UNA COMPRA
            $table->json('handoff')->nullable();             // política de relevo (disparadores)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_bot_settings');
    }
}
