<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotoPathToEventLeads extends Migration
{
    public function up()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
}
