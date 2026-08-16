<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyWhatsappBotsTable extends Migration
{
    public function up()
    {
        Schema::create('company_whatsapp_bots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->boolean('enabled')->default(false);

            // Conexión con WhatsApp Cloud API
            $table->string('phone_number_id')->nullable()->unique(); // llave del webhook
            $table->string('waba_id')->nullable();
            $table->string('display_phone')->nullable();
            $table->text('access_token')->nullable();   // secreto
            $table->text('app_secret')->nullable();     // secreto — valida la firma
            $table->string('verify_token')->nullable(); // opcional; si vacío usa el global
            $table->string('graph_version')->nullable();
            $table->text('business_type')->nullable();  // descripción para el prompt

            // Plan y cuotas
            $table->string('plan')->nullable();
            $table->integer('included_conversations')->nullable();
            $table->decimal('plan_price_usd', 10, 2)->nullable();
            $table->decimal('extra_conversation_price_usd', 10, 4)->nullable();
            $table->boolean('allow_overage')->default(false);
            $table->decimal('overage_cap_usd', 10, 2)->nullable();
            $table->integer('max_vehicles_per_reply')->default(3);

            // Cuándo responde
            $table->string('activation_mode')->default('immediate'); // immediate|delayed|manual
            $table->integer('delay_minutes')->default(10);
            $table->time('business_hours_start')->nullable();
            $table->time('business_hours_end')->nullable();
            $table->boolean('instant_outside_hours')->default(true);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_whatsapp_bots');
    }
}
