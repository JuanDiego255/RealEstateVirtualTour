<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetYawPitchToHotspotsTable extends Migration
{
    public function up()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->double('target_yaw', 8, 2)->nullable()->after('targetScene');
            $table->double('target_pitch', 8, 2)->nullable()->after('target_yaw');
        });
    }

    public function down()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->dropColumn(['target_yaw', 'target_pitch']);
        });
    }
}
