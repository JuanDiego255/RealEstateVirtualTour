<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionNegotiationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_request_id')->constrained('commission_requests')->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('proposed_percentage', 5, 2)->nullable(); // Nueva propuesta de %
            $table->text('message');
            $table->boolean('is_counter_offer')->default(false); // ¿Es una contra-oferta?
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['commission_request_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_negotiations');
    }
}
