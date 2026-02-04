<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoPositionToHotspotsTable extends Migration
{
    public function up()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->float('video_time')->nullable()->after('pitch');
            $table->float('pos_x')->nullable()->after('video_time');
            $table->float('pos_y')->nullable()->after('pos_x');
        });
    }

    public function down()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->dropColumn(['video_time', 'pos_x', 'pos_y']);
        });
    }
}
