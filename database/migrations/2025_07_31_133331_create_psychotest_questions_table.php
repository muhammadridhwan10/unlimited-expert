<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePsychotestQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_questions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('question');
            $table->enum('type', ['multiple_choice', 'essay', 'rating_scale', 'true_false']);
            $table->json('options')->nullable(); // For multiple choice options
            $table->text('correct_answer')->nullable();
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('category')->nullable(); // e.g., 'personality', 'aptitude', 'technical'
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
        Schema::dropIfExists('psychotest_questions');
    }
}
