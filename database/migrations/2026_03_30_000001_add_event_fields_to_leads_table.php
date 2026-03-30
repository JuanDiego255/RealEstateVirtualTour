<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos de evento a la tabla leads para integración con EventLeads
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Campo para guardar el nombre del evento de donde proviene el lead
            $table->string('event_name')->nullable()->after('source');

            // Relación con event_leads para tracking
            $table->unsignedBigInteger('event_lead_id')->nullable()->after('event_name');
            $table->foreign('event_lead_id')
                  ->references('id')
                  ->on('event_leads')
                  ->onDelete('set null');

            // Índices para filtrado eficiente
            $table->index('event_name');
            $table->index('event_lead_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['event_lead_id']);
            $table->dropIndex(['event_name']);
            $table->dropIndex(['event_lead_id']);
            $table->dropColumn(['event_name', 'event_lead_id']);
        });
    }
};
