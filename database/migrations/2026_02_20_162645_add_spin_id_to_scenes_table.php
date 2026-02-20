<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpinIdToScenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->unsignedBigInteger('spin_id')->nullable()->after('video');
            // Opcional (si tu tabla spins existe en misma DB y quieres FK):
            // $table->foreign('spin_id')->references('id')->on('spins')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            // $table->dropForeign(['spin_id']);
            $table->dropColumn('spin_id');
        });
    }
}
