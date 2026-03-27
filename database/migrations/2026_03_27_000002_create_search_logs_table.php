<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('search_term')->nullable();
            $table->json('filters')->nullable(); // Para almacenar filtros aplicados
            $table->integer('results_count')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('searched_at')->useCurrent();
            $table->timestamps();

            $table->index('searched_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_logs');
    }
}
