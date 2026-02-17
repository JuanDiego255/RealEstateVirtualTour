<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');

            // Solicitante (agente que quiere vender)
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('requester_company_id')->constrained('companies')->onDelete('cascade');

            // Propietario del inmueble
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_company_id')->constrained('companies')->onDelete('cascade');

            // Comisión
            $table->decimal('proposed_commission', 5, 2); // % propuesto por el solicitante
            $table->decimal('final_commission', 5, 2)->nullable(); // % acordado final

            // Estado
            $table->enum('status', [
                'pending',      // Esperando respuesta
                'negotiating',  // En negociación
                'accepted',     // Aceptado
                'rejected',     // Rechazado
                'expired',      // Expirado sin respuesta
                'completed',    // Venta realizada
                'cancelled'     // Cancelado
            ])->default('pending');

            // Mensajes
            $table->text('initial_message'); // Mensaje inicial del solicitante

            // Fechas
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Expiración de la solicitud
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['property_id', 'status']);
            $table->index(['requester_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_requests');
    }
}
