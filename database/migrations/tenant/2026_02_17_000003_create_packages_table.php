<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Básico", "Profesional", "Enterprise"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Precio base
            $table->enum('currency', ['CRC', 'USD'])->default('CRC');
            $table->enum('billing_period', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->integer('max_users')->default(1); // Usuarios activos permitidos
            $table->integer('max_categories')->default(1); // Categorías/negocios permitidos
            $table->integer('max_posts_per_category')->default(10); // Inmuebles por categoría
            $table->decimal('tour_price', 10, 2)->nullable(); // Precio adicional por tour
            $table->boolean('allows_tours')->default(false); // ¿Puede crear tours virtuales?
            $table->boolean('allows_commission')->default(false); // ¿Participa en bolsa inmobiliaria?
            $table->integer('trial_days')->default(0); // Días de prueba gratis
            $table->json('features')->nullable(); // Features adicionales flexibles
            $table->boolean('is_featured')->default(false); // Destacar en landing
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
