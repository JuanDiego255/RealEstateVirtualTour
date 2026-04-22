<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_visits', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20)->default('direct'); // qr | link | direct
            $table->string('ip_hash', 64)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_visits');
    }
};
