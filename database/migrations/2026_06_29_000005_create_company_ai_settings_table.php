<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyAiSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('company_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->boolean('enabled')->default(false);

            // Credenciales y modelo (exclusivo de esta empresa)
            $table->text('api_key')->nullable();         // secreto — API key de Anthropic
            $table->string('model')->nullable();

            // Plan y cuota mensual
            $table->string('plan')->nullable();
            $table->integer('included_generations')->nullable();
            $table->decimal('plan_price_usd', 10, 2)->nullable();
            $table->decimal('extra_generation_price_usd', 10, 4)->nullable();
            $table->boolean('allow_overage')->default(false);
            $table->decimal('overage_cap_usd', 10, 2)->nullable();

            // Personalización de contenido (opcional / avanzado)
            $table->string('brand_voice')->nullable();
            $table->string('audience')->nullable();
            $table->string('language')->nullable();
            $table->integer('max_hashtags')->nullable();
            $table->text('system_prompt')->nullable();   // avanzado

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_ai_settings');
    }
}
