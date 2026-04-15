<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleInquiriesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 100);
            $table->text('vehicle_description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_inquiries');
    }
}
