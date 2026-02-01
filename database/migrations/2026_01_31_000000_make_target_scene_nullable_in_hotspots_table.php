<?php

use Illuminate\Support\Facades\DB;
 
class MakeTargetSceneNullableInHotspotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Usar SQL raw para evitar dependencia de doctrine/dbal
        // Primero eliminar la foreign key existente
        Schema::table('hotspots', function (Blueprint $table) {
            $table->dropForeign(['targetScene']);
        });
 
        // Modificar la columna para permitir NULL usando SQL raw
        DB::statement('ALTER TABLE hotspots MODIFY targetScene BIGINT UNSIGNED NULL');
 
        // Recrear la foreign key con onDelete set null
        Schema::table('hotspots', function (Blueprint $table) {
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
        });
 
        // Primero actualizar los NULL a un valor válido (si existe)
        DB::statement('UPDATE hotspots SET targetScene = sourceScene WHERE targetScene IS NULL');
 
        // Modificar la columna para NO permitir NULL
        DB::statement('ALTER TABLE hotspots MODIFY targetScene BIGINT UNSIGNED NOT NULL');
 
        Schema::table('hotspots', function (Blueprint $table) {
        });
    }
}