<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_wishlists', function (Blueprint $table) {
            // Make vehicle_ids nullable with default empty array
            $table->json('vehicle_ids')->nullable()->default('[]')->change();

            // Add property_ids column if it doesn't exist for compatibility
            if (!Schema::hasColumn('client_wishlists', 'property_ids')) {
                $table->json('property_ids')->nullable()->default('[]')->after('client_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_wishlists', function (Blueprint $table) {
            $table->json('vehicle_ids')->nullable(false)->change();
            if (Schema::hasColumn('client_wishlists', 'property_ids')) {
                $table->dropColumn('property_ids');
            }
        });
    }
};
