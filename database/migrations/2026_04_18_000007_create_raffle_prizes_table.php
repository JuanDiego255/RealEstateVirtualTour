<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 80);
            $table->string('emoji', 10)->nullable();
            $table->string('color', 20)->default('#e74c3c');
            $table->unsignedInteger('quantity')->nullable(); // null = sin límite
            $table->unsignedInteger('claimed')->default(0);
            $table->unsignedTinyInteger('weight')->default(10); // 1-100, mayor = más probable
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_prizes');
    }
};
