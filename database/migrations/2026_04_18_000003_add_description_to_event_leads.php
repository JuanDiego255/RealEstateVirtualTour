<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToEventLeads extends Migration
{
    public function up()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->text('description')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
