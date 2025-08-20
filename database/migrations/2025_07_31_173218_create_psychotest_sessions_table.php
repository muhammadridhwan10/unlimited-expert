<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePsychotestSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('category_id');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0); // Actual time spent
            $table->json('session_data')->nullable(); // Additional session data
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('psychotest_schedules')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('psychotest_categories')->onDelete('cascade');
            
            $table->unique(['schedule_id', 'category_id']); // One session per category per schedule
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_sessions');
    }
}
