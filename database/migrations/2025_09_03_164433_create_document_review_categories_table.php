<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_review_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Category name
            $table->string('slug', 100)->unique(); // URL-friendly slug
            $table->string('description', 255)->nullable(); // Category description
            $table->string('color', 7)->default('#007bff'); // Color for badges/display
            $table->string('icon', 50)->default('ti-file'); // Icon class
            $table->boolean('is_predefined')->default(false); // Is this a system predefined category
            $table->boolean('is_active')->default(true); // Active status
            $table->unsignedBigInteger('created_by')->nullable(); // Who created this category
            $table->unsignedBigInteger('project_id')->nullable(); // Project-specific categories (null = global)
            $table->integer('sort_order')->default(0); // Display order
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Indexes
            $table->index(['is_active', 'is_predefined']);
            $table->index(['project_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_review_categories');
    }
};