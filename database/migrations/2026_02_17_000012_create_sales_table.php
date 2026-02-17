<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('restrict');

            // Vendedor principal (dueño del inmueble)
            $table->foreignId('seller_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('seller_company_id')->constrained('companies')->onDelete('restrict');

            // Agente externo (si aplica)
            $table->foreignId('external_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('external_company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('commission_request_id')->nullable()->constrained('commission_requests')->onDelete('set null');

            // Detalles de la venta
            $table->decimal('sale_price', 15, 2);
            $table->enum('currency', ['CRC', 'USD'])->default('CRC');

            // Cálculo de comisiones
            $table->decimal('total_commission_percentage', 5, 2); // % total de comisión
            $table->decimal('total_commission_amount', 15, 2); // Monto total de comisión

            // Distribución (si hay agente externo)
            $table->decimal('owner_share_percentage', 5, 2); // % para el dueño
            $table->decimal('owner_share_amount', 15, 2); // Monto para el dueño
            $table->decimal('agent_share_percentage', 5, 2)->nullable(); // % para agente externo
            $table->decimal('agent_share_amount', 15, 2)->nullable(); // Monto para agente externo

            // Información del comprador
            $table->string('buyer_name');
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_id_number')->nullable(); // Cédula del comprador

            // Fechas y estado
            $table->date('sale_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');

            // Confirmación
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['seller_company_id', 'sale_date']);
            $table->index(['status', 'sale_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
