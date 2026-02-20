<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpinJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spin_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_id')->constrained('spins')->onDelete('cascade');

            $table->string('provider')->default('cloudconvert');
            $table->string('provider_job_id')->nullable()->index();
            $table->string('status')->default('created'); // created|uploaded|processing|finished|failed|downloaded
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('last_error')->nullable();

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
        Schema::dropIfExists('spin_jobs');
    }
}
