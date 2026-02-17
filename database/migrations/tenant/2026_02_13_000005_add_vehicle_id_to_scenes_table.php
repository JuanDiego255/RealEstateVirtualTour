<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddVehicleIdToScenesTable extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('property_id');
            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });

        // Modificar property_id a nullable SIN doctrine/dbal
        DB::statement('ALTER TABLE scenes MODIFY property_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });

        // Volver property_id a NOT NULL si lo deseas
        DB::statement('ALTER TABLE scenes MODIFY property_id BIGINT UNSIGNED NOT NULL');
    }
}
