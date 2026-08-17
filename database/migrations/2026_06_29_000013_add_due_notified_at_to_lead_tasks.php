<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueNotifiedAtToLeadTasks extends Migration
{
    public function up()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_tasks', 'due_notified_at')) {
                $table->timestamp('due_notified_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('lead_tasks', 'due_notified_at')) {
                $table->dropColumn('due_notified_at');
            }
        });
    }
}
