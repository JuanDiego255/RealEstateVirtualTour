<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCapturedByToEventTables extends Migration
{
    public function up()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->unsignedBigInteger('captured_by_user_id')->nullable()->after('company_id');
            $table->foreign('captured_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->unsignedBigInteger('captured_by_user_id')->nullable()->after('event_name');
            $table->foreign('captured_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropForeign(['captured_by_user_id']);
            $table->dropColumn('captured_by_user_id');
        });

        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->dropForeign(['captured_by_user_id']);
            $table->dropColumn('captured_by_user_id');
        });
    }
}
