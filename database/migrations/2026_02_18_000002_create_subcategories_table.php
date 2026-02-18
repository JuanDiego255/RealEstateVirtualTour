<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubcategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->string('image', 191)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unique(['category_id', 'slug']);
        });

        // Add limit field to packages
        if (!Schema::hasColumn('packages', 'max_subcategories_per_category')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->integer('max_subcategories_per_category')->default(5)->after('max_posts_per_category');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('subcategories');

        if (Schema::hasColumn('packages', 'max_subcategories_per_category')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('max_subcategories_per_category');
            });
        }
    }
}
