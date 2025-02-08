<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            //$table->unsignedBigInteger('property_id');
            $table->string('title', 255);
            $table->string('type', 255);
            $table->double('hfov', 8, 2);
            $table->double('yaw', 8, 2);
            $table->double('pitch', 8, 2);
            $table->string('image');
            $table->string('image_ref', 191);
            $table->integer('status')->nullable();
            //$table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
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
        Schema::dropIfExists('scenes');
    }
}
