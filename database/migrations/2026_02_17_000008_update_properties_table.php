<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            // Relaciones
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->after('category_id')->constrained('users')->onDelete('set null');

            // Tipo de propiedad y descripción
            $table->enum('property_type', ['house', 'apartment', 'land', 'vehicle', 'commercial', 'other'])->default('house')->after('name');
            $table->text('description')->nullable()->after('property_type');

            // Ubicación
            $table->string('location')->nullable()->after('image'); // Ubicación general
            $table->string('address')->nullable()->after('location'); // Dirección exacta
            $table->decimal('latitude', 10, 8)->nullable()->after('address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            // Moneda y estado
            $table->enum('currency', ['CRC', 'USD'])->default('CRC')->after('price');
            $table->enum('status', ['available', 'reserved', 'negotiating', 'sold', 'rented', 'inactive'])->default('available')->after('currency');

            // Sistema de comisiones
            $table->boolean('is_exclusive')->default(true)->after('status'); // ¿Exclusivo del propietario?
            $table->decimal('commission_percentage', 5, 2)->nullable()->after('is_exclusive'); // % comisión para externos
            $table->text('commission_notes')->nullable()->after('commission_percentage'); // Notas sobre comisión

            // Flags y contadores
            $table->boolean('has_virtual_tour')->default(false)->after('commission_notes');
            $table->boolean('is_featured')->default(false)->after('has_virtual_tour');
            $table->unsignedInteger('views_count')->default(0)->after('is_featured');

            // Fechas
            $table->timestamp('published_at')->nullable()->after('views_count');
            $table->timestamp('sold_at')->nullable()->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'category_id',
                'user_id',
                'property_type',
                'description',
                'location',
                'address',
                'latitude',
                'longitude',
                'currency',
                'status',
                'is_exclusive',
                'commission_percentage',
                'commission_notes',
                'has_virtual_tour',
                'is_featured',
                'views_count',
                'published_at',
                'sold_at'
            ]);
        });
    }
}
