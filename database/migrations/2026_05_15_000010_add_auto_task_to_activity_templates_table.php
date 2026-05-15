<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoTaskToActivityTemplatesTable extends Migration
{
    public function up()
    {
        Schema::table('activity_templates', function (Blueprint $table) {
            $table->boolean('auto_create_task')->default(false)->after('is_active');
            $table->unsignedSmallInteger('task_due_hours')->default(24)->after('auto_create_task');
        });
    }

    public function down()
    {
        Schema::table('activity_templates', function (Blueprint $table) {
            $table->dropColumn(['auto_create_task', 'task_due_hours']);
        });
    }
}
