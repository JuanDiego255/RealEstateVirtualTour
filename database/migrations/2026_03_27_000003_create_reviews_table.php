<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // Quien califica
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade'); // Quien es calificado
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null'); // Venta relacionada
            $table->unsignedTinyInteger('rating'); // 1-5 estrellas
            $table->text('comment')->nullable();
            $table->timestamps();

            // Un usuario solo puede calificar una vez por venta (si está relacionado a venta)
            // O una vez a otro usuario en general
            $table->unique(['reviewer_id', 'reviewee_id', 'sale_id']);
            $table->index('reviewee_id');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
