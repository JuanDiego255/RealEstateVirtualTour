<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVehiclePrefsToLeadsTable extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('preferred_asset_type', ['property', 'vehicle'])->default('property')->after('preferred_property_types');
            $table->string('preferred_vehicle_brand', 100)->nullable()->after('preferred_asset_type');
            $table->decimal('preferred_vehicle_max_price', 14, 2)->nullable();
            $table->unsignedSmallInteger('preferred_vehicle_min_year')->nullable();
            $table->unsignedSmallInteger('preferred_vehicle_max_year')->nullable();
            $table->string('preferred_vehicle_fuel_type', 50)->nullable();
            $table->string('preferred_vehicle_transmission', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_asset_type', 'preferred_vehicle_brand',
                'preferred_vehicle_max_price', 'preferred_vehicle_min_year',
                'preferred_vehicle_max_year', 'preferred_vehicle_fuel_type',
                'preferred_vehicle_transmission',
            ]);
        });
    }
}
