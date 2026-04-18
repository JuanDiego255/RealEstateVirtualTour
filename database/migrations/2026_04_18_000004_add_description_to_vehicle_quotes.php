<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToVehicleQuotes extends Migration
{
    public function up()
    {
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('event_name');
        });
    }

    public function down()
    {
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
