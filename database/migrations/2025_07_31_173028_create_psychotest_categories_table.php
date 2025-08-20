<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePsychotestCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Deret Gambar", "Matematika Dasar", "Antonim/Sinonim", "Kraeplin"
            $table->string('code')->unique(); // e.g., "visual_sequence", "basic_math", "synonym_antonym", "kraeplin"
            $table->text('description')->nullable();
            $table->enum('type', ['standard', 'kraeplin', 'visual', 'verbal', 'numeric'])->default('standard');
            $table->integer('duration_minutes'); // Duration for this category
            $table->integer('total_questions'); // Total questions in this category
            $table->integer('order')->default(0); // Order of test execution
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Additional settings like kraeplin columns, time per item, etc.
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_categories');
    }
}
