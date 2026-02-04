<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelsToScenePolygonsTable extends Migration
{
    public function up()
    {
        Schema::table('scene_polygons', function (Blueprint $table) {
            $table->json('edge_labels')->nullable()->after('points');
            $table->string('interior_text')->nullable()->after('edge_labels');
        });
    }

    public function down()
    {
        Schema::table('scene_polygons', function (Blueprint $table) {
            $table->dropColumn(['edge_labels', 'interior_text']);
        });
    }
}
