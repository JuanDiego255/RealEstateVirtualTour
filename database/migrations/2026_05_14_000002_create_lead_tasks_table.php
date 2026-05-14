<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadTasksTable extends Migration
{
    public function up()
    {
        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('type', ['call', 'email', 'whatsapp', 'visit', 'meeting', 'document', 'other'])->default('other');

            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();

            $table->timestamps();

            $table->index(['lead_id', 'status']);
            $table->index(['assigned_to', 'status', 'due_at']);
            $table->index(['company_id', 'status', 'due_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_tasks');
    }
}
