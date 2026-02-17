<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('packages')->onDelete('restrict');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->date('trial_ends_at')->nullable(); // Fecha fin de prueba
            $table->enum('status', ['pending', 'trial', 'active', 'expired', 'cancelled', 'suspended'])->default('pending');
            $table->enum('payment_method', ['transfer', 'sinpe', 'card'])->nullable(); // Método preferido
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
