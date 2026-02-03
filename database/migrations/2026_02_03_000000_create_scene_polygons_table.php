<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScenePolygonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scene_polygons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scene_id');
            $table->string('name', 255);
            $table->string('fill_color', 7)->default('#00FF00');
            $table->double('fill_opacity', 3, 2)->default(0.35);
            $table->string('stroke_color', 7)->default('#FFFFFF');
            $table->integer('stroke_width')->default(2);
            $table->json('points'); // Array de {yaw, pitch}
            $table->timestamps();

            $table->foreign('scene_id')->references('id')->on('scenes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scene_polygons');
    }
}
