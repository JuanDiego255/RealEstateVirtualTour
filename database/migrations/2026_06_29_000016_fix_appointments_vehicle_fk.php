<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * appointments.vehicle_id apunta (por FK) a la tabla legacy `vehicles`, pero toda
 * la app usa vehículos de `properties` (property_type = vehicle) — así lo valida
 * AppointmentController. Repuntamos el FK a `properties` para que agendar una
 * prueba (o cualquier cita de vehículo) no viole la restricción.
 */
class FixAppointmentsVehicleFk extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('appointments', 'vehicle_id')) {
            return;
        }

        // Limpiar valores huérfanos que no existan en properties (evita que falle el nuevo FK).
        DB::statement('UPDATE appointments SET vehicle_id = NULL
            WHERE vehicle_id IS NOT NULL
              AND vehicle_id NOT IN (SELECT id FROM properties)');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->foreign('vehicle_id')->references('id')->on('properties')->nullOnDelete();
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('appointments', 'vehicle_id')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            // Restaura el FK original a la tabla legacy `vehicles`.
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
        });
    }
}
