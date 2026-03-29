<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migracion para agregar property_id a las tablas de eventos del kiosk
 * Esto permite que los vehiculos se almacenen en la tabla properties con property_type = 'vehicle'
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. vehicle_event_views - agregar property_id
        Schema::table('vehicle_event_views', function (Blueprint $table) {
            // Primero eliminar la foreign key existente
            $table->dropForeign(['vehicle_id']);
            // Hacer vehicle_id nullable
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            // Agregar property_id
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index('property_id');
        });

        // 2. vehicle_quotes - agregar property_id
        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index('property_id');
        });

        // 3. qr_scans - agregar property_id
        Schema::table('qr_scans', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index('property_id');
        });

        // 4. vehicle_colors - agregar property_id
        Schema::table('vehicle_colors', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index('property_id');
        });

        // 5. test_drive_videos - agregar property_id
        Schema::table('test_drive_videos', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index('property_id');
        });

        // 6. event_leads - agregar property_id
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->unsignedBigInteger('property_id')->nullable()->after('id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
            $table->index('property_id');
        });

        // 7. client_wishlists - agregar property_ids (JSON)
        Schema::table('client_wishlists', function (Blueprint $table) {
            $table->json('property_ids')->nullable()->after('client_phone');
        });
    }

    public function down(): void
    {
        // Revertir cambios en orden inverso
        Schema::table('client_wishlists', function (Blueprint $table) {
            $table->dropColumn('property_ids');
        });

        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });

        Schema::table('test_drive_videos', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        Schema::table('vehicle_colors', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        Schema::table('qr_scans', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        Schema::table('vehicle_quotes', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        Schema::table('vehicle_event_views', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id']);
            $table->dropColumn('property_id');
            $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }
};
