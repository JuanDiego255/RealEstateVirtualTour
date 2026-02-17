<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToSectorsTable extends Migration
{
    /**
     * Run the migrations.
     * Adds subscription-module fields to existing sectors table
     */
    public function up()
    {
        Schema::table('sectors', function (Blueprint $table) {
            if (!Schema::hasColumn('sectors', 'color')) {
                $table->string('color')->nullable()->after('image');
            }
            if (!Schema::hasColumn('sectors', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
            if (!Schema::hasColumn('sectors', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sectors', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('sectors', 'color')) $columns[] = 'color';
            if (Schema::hasColumn('sectors', 'is_active')) $columns[] = 'is_active';
            if (Schema::hasColumn('sectors', 'sort_order')) $columns[] = 'sort_order';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
