<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherKioskToCompanies extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('other_kiosk')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('other_kiosk');
        });
    }
}
