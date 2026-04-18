<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToVehicleQuotes extends Migration
{
    public function up()
    {
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('property_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropColumn('company_id');
        });
    }
}
