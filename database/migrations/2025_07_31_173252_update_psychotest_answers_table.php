<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePsychotestAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('psychotest_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->after('schedule_id');
            $table->integer('time_taken_seconds')->nullable(); // Time taken for this specific answer
            $table->json('kraeplin_answers')->nullable(); // For kraeplin test answers (array of calculations)
        });

        // Add foreign key
        Schema::table('psychotest_answers', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('psychotest_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('psychotest_answers', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropColumn(['session_id', 'time_taken_seconds', 'kraeplin_answers']);
        });
    }
}
