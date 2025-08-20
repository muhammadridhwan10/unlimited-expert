<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePsychotestResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->integer('total_questions');
            $table->integer('answered_questions');
            $table->integer('total_points');
            $table->integer('earned_points');
            $table->decimal('percentage', 5, 2);
            $table->enum('grade', ['A', 'B', 'C', 'D', 'F'])->nullable();
            $table->text('notes')->nullable();
            $table->json('category_scores')->nullable(); // Scores by category
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('psychotest_schedules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_results');
    }
}
