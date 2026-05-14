<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmFormsTable extends Migration
{
    public function up()
    {
        Schema::create('crm_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title'); // Public-facing title
            $table->text('description')->nullable();
            $table->json('fields'); // [{name, label, type, required, options}]
            $table->string('default_source')->default('website');
            $table->string('default_assigned_to')->nullable(); // user_id or 'round_robin'
            $table->string('success_message')->default('¡Gracias! Nos pondremos en contacto pronto.');
            $table->string('redirect_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('submissions_count')->default(0);

            $table->timestamps();
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('crm_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_form_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->json('data'); // Raw submitted data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamps();
            $table->index('crm_form_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_form_submissions');
        Schema::dropIfExists('crm_forms');
    }
}
