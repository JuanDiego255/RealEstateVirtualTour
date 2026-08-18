<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailLogsTable extends Migration
{
    public function up()
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('to_email')->nullable();
            $table->string('to_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('subject')->nullable();
            $table->string('mailer')->nullable();
            $table->string('context')->nullable();       // reminder | appointment | task | followup | test | ...
            $table->string('status', 10)->default('sent'); // sent | failed
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mail_logs');
    }
}
