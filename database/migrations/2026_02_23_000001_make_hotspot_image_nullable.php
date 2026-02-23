<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeHotspotImageNullable extends Migration
{
    public function up()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->string('image', 191)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->string('image', 191)->nullable(false)->change();
        });
    }
}
