<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadIdToWhatsappChats extends Migration
{
    public function up()
    {
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_chats', 'lead_id')) {
                $table->unsignedBigInteger('lead_id')->nullable()->after('contact_name');
                $table->index('lead_id');
            }
        });
    }

    public function down()
    {
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_chats', 'lead_id')) {
                $table->dropIndex(['lead_id']);
                $table->dropColumn('lead_id');
            }
        });
    }
}
