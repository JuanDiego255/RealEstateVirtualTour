<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     * Adds subscription-module fields to existing categories table
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            // Company relationship (nullable for backward compat with existing categories)
            if (!Schema::hasColumn('categories', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            }

            // Additional business info
            if (!Schema::hasColumn('categories', 'logo')) {
                $table->string('logo')->nullable()->after('image');
            }
            if (!Schema::hasColumn('categories', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('logo');
            }

            // Contact info
            if (!Schema::hasColumn('categories', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('categories', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_name');
            }
            if (!Schema::hasColumn('categories', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_email');
            }
            if (!Schema::hasColumn('categories', 'contact_whatsapp')) {
                $table->string('contact_whatsapp')->nullable()->after('contact_phone');
            }

            // Address and geolocation
            if (!Schema::hasColumn('categories', 'address')) {
                $table->text('address')->nullable()->after('contact_whatsapp');
            }
            if (!Schema::hasColumn('categories', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
            }
            if (!Schema::hasColumn('categories', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }

            // Web and social
            if (!Schema::hasColumn('categories', 'website')) {
                $table->string('website')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('categories', 'social_facebook')) {
                $table->string('social_facebook')->nullable()->after('website');
            }
            if (!Schema::hasColumn('categories', 'social_instagram')) {
                $table->string('social_instagram')->nullable()->after('social_facebook');
            }

            // Shareable link
            if (!Schema::hasColumn('categories', 'share_token')) {
                $table->string('share_token', 32)->nullable()->unique()->after('social_instagram');
            }

            // Active flag (complement existing boolean 'status')
            if (!Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }

            // Views counter
            if (!Schema::hasColumn('categories', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'company_id')) {
                $table->dropForeign(['company_id']);
            }

            $columns = [
                'company_id', 'logo', 'cover_image',
                'contact_name', 'contact_email', 'contact_phone', 'contact_whatsapp',
                'address', 'latitude', 'longitude',
                'website', 'social_facebook', 'social_instagram',
                'share_token', 'is_active', 'views_count',
            ];

            $existing = [];
            foreach ($columns as $col) {
                if (Schema::hasColumn('categories', $col)) {
                    $existing[] = $col;
                }
            }
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
