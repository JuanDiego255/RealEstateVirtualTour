<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Agente asignado
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null'); // Propiedad de interés
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null'); // Vehículo de interés

            // Información del lead
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();

            // Clasificación
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->enum('source', ['website', 'whatsapp', 'phone', 'referral', 'social_media', 'walk_in', 'other'])->default('website');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('interest_type', ['buy', 'rent', 'sell', 'other'])->default('buy');

            // Presupuesto
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->enum('budget_currency', ['CRC', 'USD'])->default('CRC');

            // Notas y seguimiento
            $table->text('notes')->nullable();
            $table->text('requirements')->nullable(); // Lo que busca el cliente
            $table->date('next_follow_up')->nullable();

            // Fechas importantes
            $table->timestamp('first_contact_at')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('converted_at')->nullable(); // Cuando se convierte en cliente

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('next_follow_up');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leads');
    }
}
