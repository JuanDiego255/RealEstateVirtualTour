<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancingToggleToCompanyWhatsappBots extends Migration
{
    public function up()
    {
        Schema::table('company_whatsapp_bots', function (Blueprint $table) {
            if (!Schema::hasColumn('company_whatsapp_bots', 'allow_financing_quote')) {
                // Si true, el bot puede cotizar una cuota estimada; si false, hace handoff.
                $table->boolean('allow_financing_quote')->default(false)->after('max_vehicles_per_reply');
            }
        });
    }

    public function down()
    {
        Schema::table('company_whatsapp_bots', function (Blueprint $table) {
            if (Schema::hasColumn('company_whatsapp_bots', 'allow_financing_quote')) {
                $table->dropColumn('allow_financing_quote');
            }
        });
    }
}
