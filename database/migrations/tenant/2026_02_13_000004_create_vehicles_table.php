<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 150);
            $table->string('brand', 80);
            $table->string('model', 80);
            $table->string('year', 10);
            $table->string('color', 50)->nullable();
            $table->string('mileage_km', 30)->nullable();
            $table->string('fuel_tank_capacity', 30)->nullable();
            $table->string('fuel_type', 50)->nullable();
            $table->string('engine_cc', 30)->nullable();
            $table->string('doors', 10)->nullable();
            $table->string('passengers', 10)->nullable();
            $table->string('tires', 50)->nullable();
            $table->string('drivetrain', 50)->nullable();
            $table->string('transmission', 50)->nullable();
            $table->string('price', 60);
            $table->string('condition', 20)->nullable();
            $table->string('plate', 20)->nullable();
            $table->string('image', 191)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
