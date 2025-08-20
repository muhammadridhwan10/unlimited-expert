<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePsychotestResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('psychotest_results', function (Blueprint $table) {
            $table->json('category_results')->nullable()->after('category_scores'); // Detailed results per category
            $table->integer('total_time_spent_seconds')->default(0); // Total time spent across all tests
            $table->enum('completion_status', ['complete', 'partial', 'timeout'])->default('complete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('psychotest_results', function (Blueprint $table) {
            $table->dropColumn(['category_results', 'total_time_spent_seconds', 'completion_status']);
        });
    }
}
