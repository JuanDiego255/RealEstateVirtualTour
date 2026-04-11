<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadCategoryToEventLeads extends Migration
{
    public function up()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            // Categoría del lead (tipo de prospecto)
            $table->string('lead_category')->default('prospect')->after('interest_level');
            // Estado de la venta
            $table->string('sale_status')->default('open')->after('lead_category');
            // Fecha de conversión (cuando se concretó la venta)
            $table->timestamp('converted_at')->nullable()->after('sale_status');
        });
    }

    public function down()
    {
        Schema::table('event_leads', function (Blueprint $table) {
            $table->dropColumn(['lead_category', 'sale_status', 'converted_at']);
        });
    }
}
