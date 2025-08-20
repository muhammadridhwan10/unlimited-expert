<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePsychotestQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('psychotest_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('id');
            $table->string('image')->nullable()->after('question'); // Image file path
            $table->json('kraeplin_data')->nullable(); // For kraeplin test data
            $table->integer('time_limit_seconds')->nullable(); // Individual question time limit
            $table->dropColumn('category'); // Remove old string category
        });

        // Add foreign key
        Schema::table('psychotest_questions', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('psychotest_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('psychotest_questions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'image', 'kraeplin_data', 'time_limit_seconds']);
            $table->string('category')->nullable();
        });
    }
}
