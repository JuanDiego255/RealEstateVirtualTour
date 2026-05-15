<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVehicleFieldsToLeadQuotesTable extends Migration
{
    public function up()
    {
        Schema::table('lead_quotes', function (Blueprint $table) {
            $table->enum('quote_type', ['property', 'vehicle'])->default('property')->after('title');
            $table->unsignedBigInteger('vehicle_quote_id')->nullable()->after('property_id');
            // Vehicle financial fields
            $table->decimal('vq_vehicle_price', 14, 2)->nullable()->after('vehicle_quote_id');
            $table->decimal('vq_down_payment', 14, 2)->nullable();
            $table->decimal('vq_down_payment_pct', 5, 2)->nullable()->default(50);
            $table->unsignedSmallInteger('vq_term_months')->nullable();
            $table->decimal('vq_interest_rate', 5, 4)->nullable();
            $table->string('vq_payment_frequency', 20)->nullable()->default('monthly');
            $table->decimal('vq_monthly_payment', 14, 2)->nullable();
            $table->decimal('vq_total_interest', 14, 2)->nullable();
            $table->decimal('vq_total_amount', 14, 2)->nullable();

            $table->foreign('vehicle_quote_id')->references('id')->on('vehicle_quotes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('lead_quotes', function (Blueprint $table) {
            $table->dropForeign(['vehicle_quote_id']);
            $table->dropColumn([
                'quote_type', 'vehicle_quote_id',
                'vq_vehicle_price', 'vq_down_payment', 'vq_down_payment_pct',
                'vq_term_months', 'vq_interest_rate', 'vq_payment_frequency',
                'vq_monthly_payment', 'vq_total_interest', 'vq_total_amount',
            ]);
        });
    }
}
