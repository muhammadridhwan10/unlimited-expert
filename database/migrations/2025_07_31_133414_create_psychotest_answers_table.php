<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePsychotestAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('question_id');
            $table->text('answer');
            $table->integer('points_earned')->default(0);
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('psychotest_schedules')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('psychotest_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_answers');
    }
}
