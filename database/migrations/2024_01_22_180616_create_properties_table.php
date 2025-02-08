<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 90);
            $table->string('code', 30);
            $table->string('rooms', 15);
            $table->string('bathrooms', 15);
            $table->string('garage', 15);
            $table->string('floor_levels', 15);
            $table->string('construction', 30);
            $table->string('land', 30);
            $table->string('construction_year', 30);
            $table->string('maintenance', 60);
            $table->string('price', 60);
            $table->string('image', 191);
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
        Schema::dropIfExists('properties');
    }
}
