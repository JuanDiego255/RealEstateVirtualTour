<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->string('payment_frequency', 20)->default('monthly')->after('interest_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->dropColumn('payment_frequency');
        });
    }
};
