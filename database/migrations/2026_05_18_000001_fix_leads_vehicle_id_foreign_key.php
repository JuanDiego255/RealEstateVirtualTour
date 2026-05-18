<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix vehicle_id FK on leads table — was pointing to `vehicles`, should point to `properties`.
 */
class FixLeadsVehicleIdForeignKey extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop the incorrect FK that points to `vehicles`
            $table->dropForeign('leads_vehicle_id_foreign');
            // Add correct FK pointing to `properties`
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('properties')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('leads_vehicle_id_foreign');
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('set null');
        });
    }
}
