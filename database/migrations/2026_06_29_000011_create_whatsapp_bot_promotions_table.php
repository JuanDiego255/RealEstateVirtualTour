<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappBotPromotionsTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_bot_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('title');
            $table->text('description');
            $table->boolean('active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_bot_promotions');
    }
}
