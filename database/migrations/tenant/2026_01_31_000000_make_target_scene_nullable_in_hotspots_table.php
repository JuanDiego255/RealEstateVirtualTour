<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTargetSceneNullableInHotspotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            // Eliminar la constraint de foreign key existente
            $table->dropForeign(['targetScene']);

            // Modificar la columna para permitir NULL
            $table->unsignedBigInteger('targetScene')->nullable()->change();

            // Recrear la foreign key con onDelete set null
            $table->foreign('targetScene')
                  ->references('id')
                  ->on('scenes')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->dropForeign(['targetScene']);

            $table->unsignedBigInteger('targetScene')->nullable(false)->change();

            $table->foreign('targetScene')
                  ->references('id')
                  ->on('scenes')
                  ->onDelete('cascade');
        });
    }
}
