<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrmAdvancedFieldsToLeadsTable extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            // Lead Scoring
            $table->unsignedTinyInteger('score')->default(0)->after('priority');
            $table->timestamp('score_updated_at')->nullable()->after('score');

            // Pipeline automation — when did the lead enter current stage
            $table->timestamp('stage_entered_at')->nullable()->after('score_updated_at');

            // Client portal
            $table->string('portal_token', 64)->nullable()->unique()->after('stage_entered_at');
            $table->timestamp('portal_token_expires_at')->nullable()->after('portal_token');

            // Property/vehicle preferences for matching
            $table->json('preferred_zones')->nullable()->after('requirements');
            $table->json('preferred_property_types')->nullable()->after('preferred_zones');
            $table->unsignedSmallInteger('preferred_bedrooms_min')->nullable()->after('preferred_property_types');
            $table->unsignedSmallInteger('preferred_bedrooms_max')->nullable()->after('preferred_bedrooms_min');

            $table->index('score');
            $table->index('stage_entered_at');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'score', 'score_updated_at', 'stage_entered_at',
                'portal_token', 'portal_token_expires_at',
                'preferred_zones', 'preferred_property_types',
                'preferred_bedrooms_min', 'preferred_bedrooms_max',
            ]);
        });
    }
}
