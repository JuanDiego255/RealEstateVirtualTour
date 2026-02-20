<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spins', function (Blueprint $table) {
            $table->id();

            // Ajusta a tu modelo real (vehicle_id, property_id, scene_id, etc.)
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();

            $table->string('video_path')->nullable();      // donde guardas el mp4 (storage)
            $table->string('frames_dir')->nullable();      // carpeta en storage con frames
            $table->unsignedInteger('frames_count')->default(0);

            $table->string('status')->default('draft');    // draft|uploaded|processing|ready|failed
            $table->string('cloudconvert_job_id')->nullable()->index();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spins');
    }
}
