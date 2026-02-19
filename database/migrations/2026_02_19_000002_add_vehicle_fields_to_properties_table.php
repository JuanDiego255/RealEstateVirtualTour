<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVehicleFieldsToPropertiesTable extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'brand')) {
                $table->string('brand', 80)->nullable()->after('name');
            }
            if (!Schema::hasColumn('properties', 'model')) {
                $table->string('model', 80)->nullable()->after('brand');
            }
            if (!Schema::hasColumn('properties', 'year')) {
                $table->string('year', 10)->nullable()->after('model');
            }
            if (!Schema::hasColumn('properties', 'color')) {
                $table->string('color', 50)->nullable()->after('year');
            }
            if (!Schema::hasColumn('properties', 'mileage_km')) {
                $table->string('mileage_km', 30)->nullable()->after('color');
            }
            if (!Schema::hasColumn('properties', 'fuel_tank_capacity')) {
                $table->string('fuel_tank_capacity', 30)->nullable()->after('mileage_km');
            }
            if (!Schema::hasColumn('properties', 'fuel_type')) {
                $table->string('fuel_type', 50)->nullable()->after('fuel_tank_capacity');
            }
            if (!Schema::hasColumn('properties', 'engine_cc')) {
                $table->string('engine_cc', 30)->nullable()->after('fuel_type');
            }
            if (!Schema::hasColumn('properties', 'doors')) {
                $table->string('doors', 10)->nullable()->after('engine_cc');
            }
            if (!Schema::hasColumn('properties', 'passengers')) {
                $table->string('passengers', 10)->nullable()->after('doors');
            }
            if (!Schema::hasColumn('properties', 'tires')) {
                $table->string('tires', 50)->nullable()->after('passengers');
            }
            if (!Schema::hasColumn('properties', 'drivetrain')) {
                $table->string('drivetrain', 50)->nullable()->after('tires');
            }
            if (!Schema::hasColumn('properties', 'transmission')) {
                $table->string('transmission', 50)->nullable()->after('drivetrain');
            }
            if (!Schema::hasColumn('properties', 'condition')) {
                $table->string('condition', 20)->nullable()->after('transmission');
            }
            if (!Schema::hasColumn('properties', 'plate')) {
                $table->string('plate', 20)->nullable()->after('condition');
            }
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = ['brand','model','year','color','mileage_km','fuel_tank_capacity','fuel_type','engine_cc','doors','passengers','tires','drivetrain','transmission','condition','plate'];
            $existing = [];
            foreach ($cols as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $existing[] = $col;
                }
            }
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
