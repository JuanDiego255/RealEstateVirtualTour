<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadDeletionAuditTable extends Migration
{
    public function up()
    {
        Schema::create('lead_deletion_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('lead_name', 255);
            $table->string('lead_email', 255)->nullable();
            $table->string('lead_phone', 50)->nullable();
            $table->string('lead_status', 50)->nullable();
            $table->unsignedTinyInteger('lead_score')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamps();

            $table->index('company_id');
            $table->index('deleted_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_deletion_audit');
    }
}
