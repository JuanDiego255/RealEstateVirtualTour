<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpinFieldsToPropertiesTable extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('spin_active')->default(false)->after('has_virtual_tour');
            $table->boolean('spin_auto_rotate')->default(true)->after('spin_active');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['spin_active', 'spin_auto_rotate']);
        });
    }
}
