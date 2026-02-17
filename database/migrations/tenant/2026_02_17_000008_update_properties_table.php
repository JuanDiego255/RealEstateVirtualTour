<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     * Note: category_id already added by 2026_02_13_000003_add_category_id_to_properties_table
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            // User relationship
            if (!Schema::hasColumn('properties', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('category_id')->constrained('users')->onDelete('set null');
            }

            // Tipo de propiedad y descripción
            if (!Schema::hasColumn('properties', 'property_type')) {
                $table->enum('property_type', ['house', 'apartment', 'land', 'vehicle', 'commercial', 'other'])->default('house')->after('name');
            }
            if (!Schema::hasColumn('properties', 'description')) {
                $table->text('description')->nullable()->after('property_type');
            }

            // Ubicación
            if (!Schema::hasColumn('properties', 'location')) {
                $table->string('location')->nullable()->after('image');
            }
            if (!Schema::hasColumn('properties', 'address')) {
                $table->string('address')->nullable()->after('location');
            }
            if (!Schema::hasColumn('properties', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
            }
            if (!Schema::hasColumn('properties', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }

            // Moneda y estado
            if (!Schema::hasColumn('properties', 'currency')) {
                $table->enum('currency', ['CRC', 'USD'])->default('CRC')->after('price');
            }
            if (!Schema::hasColumn('properties', 'status')) {
                $table->enum('status', ['available', 'reserved', 'negotiating', 'sold', 'rented', 'inactive'])->default('available')->after('currency');
            }

            // Sistema de comisiones
            if (!Schema::hasColumn('properties', 'is_exclusive')) {
                $table->boolean('is_exclusive')->default(true)->after('status');
            }
            if (!Schema::hasColumn('properties', 'commission_percentage')) {
                $table->decimal('commission_percentage', 5, 2)->nullable()->after('is_exclusive');
            }
            if (!Schema::hasColumn('properties', 'commission_notes')) {
                $table->text('commission_notes')->nullable()->after('commission_percentage');
            }

            // Flags y contadores
            if (!Schema::hasColumn('properties', 'has_virtual_tour')) {
                $table->boolean('has_virtual_tour')->default(false)->after('commission_notes');
            }
            if (!Schema::hasColumn('properties', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('has_virtual_tour');
            }
            if (!Schema::hasColumn('properties', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('is_featured');
            }

            // Fechas
            if (!Schema::hasColumn('properties', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('views_count');
            }
            if (!Schema::hasColumn('properties', 'sold_at')) {
                $table->timestamp('sold_at')->nullable()->after('published_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'user_id')) {
                $table->dropForeign(['user_id']);
            }

            $columns = [
                'user_id', 'property_type', 'description',
                'location', 'address', 'latitude', 'longitude',
                'currency', 'status',
                'is_exclusive', 'commission_percentage', 'commission_notes',
                'has_virtual_tour', 'is_featured', 'views_count',
                'published_at', 'sold_at'
            ];

            $existing = [];
            foreach ($columns as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $existing[] = $col;
                }
            }
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
