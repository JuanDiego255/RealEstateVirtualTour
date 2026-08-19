<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * lead_activities.vehicle_id apuntaba (por FK) a la tabla legacy `vehicles`, pero
 * los vehículos viven en `properties`. Al registrar una actividad con el id de un
 * vehículo (p. ej. "prueba de manejo") se violaba la restricción. Se repunta a
 * `properties`, igual que ya se hizo con leads y appointments.
 */
class FixLeadActivitiesVehicleFk extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('lead_activities', 'vehicle_id')) {
            return;
        }

        DB::statement('UPDATE lead_activities SET vehicle_id = NULL
            WHERE vehicle_id IS NOT NULL
              AND vehicle_id NOT IN (SELECT id FROM properties)');

        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->foreign('vehicle_id')->references('id')->on('properties')->nullOnDelete();
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('lead_activities', 'vehicle_id')) {
            return;
        }

        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
        });
    }
}
