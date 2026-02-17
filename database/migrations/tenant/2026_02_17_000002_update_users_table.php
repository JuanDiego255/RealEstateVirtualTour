<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('username');
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'company_admin', 'agent'])->default('agent')->after('phone');
            $table->foreignId('company_id')->nullable()->after('role')->constrained('companies')->onDelete('set null');
            $table->string('avatar')->nullable()->after('company_id');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('avatar');
            $table->timestamp('email_verified_at')->nullable()->after('status');
        });

        // Agregar owner_id a companies (referencia circular, se hace después)
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('website')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'email',
                'phone',
                'role',
                'company_id',
                'avatar',
                'status',
                'email_verified_at'
            ]);
        });
    }
}
