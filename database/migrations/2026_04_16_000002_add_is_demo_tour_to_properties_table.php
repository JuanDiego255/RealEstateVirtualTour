<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDemoTourToPropertiesTable extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_demo_tour')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('is_demo_tour');
        });
    }
}
