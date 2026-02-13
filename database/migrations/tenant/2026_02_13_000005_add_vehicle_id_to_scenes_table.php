<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVehicleIdToScenesTable extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('property_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        // Make property_id nullable so scenes can belong to vehicles instead
        Schema::table('scenes', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });
    }
}
