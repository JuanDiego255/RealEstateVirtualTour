<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos de audio del motor para la experiencia inmersiva de Test Drive
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Imagen destacada del interior para Test Drive
            if (!Schema::hasColumn('properties', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('image');
            }

            // Audio de arranque del motor (sonido al encender)
            $table->string('engine_startup_audio')->nullable()->after('image');

            // Audio del motor en idle (ralenti)
            $table->string('engine_idle_audio')->nullable()->after('engine_startup_audio');

            // Audio del motor acelerando
            $table->string('engine_rev_audio')->nullable()->after('engine_idle_audio');

            // Video POV del interior (primera persona)
            $table->string('interior_pov_video')->nullable()->after('engine_rev_audio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $columns = [
                'engine_startup_audio',
                'engine_idle_audio',
                'engine_rev_audio',
                'interior_pov_video',
            ];

            // Solo eliminar columnas que existen
            foreach ($columns as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }

            // featured_image se mantiene ya que puede ser usada en otros contextos
        });
    }
};
