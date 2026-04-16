<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDemoToScenesTable extends Migration
{
    public function up()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
}
