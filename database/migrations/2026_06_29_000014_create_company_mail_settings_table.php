<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyMailSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('company_mail_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->boolean('enabled')->default(false);
            $table->string('from_name')->nullable();
            $table->string('from_address')->nullable();
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption', 10)->nullable(); // tls | ssl | null
            $table->string('username')->nullable();
            $table->text('password')->nullable();          // encriptada (clave de aplicación)
            $table->timestamp('last_test_at')->nullable();
            $table->boolean('last_test_ok')->default(false);
            $table->text('last_test_error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_mail_settings');
    }
}
