<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoToScenesTable extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->string('video')->nullable()->after('image');
        });
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('video');
        });
    }
}
