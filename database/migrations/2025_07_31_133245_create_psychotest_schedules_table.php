<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePsychotestSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate'); // Job Application ID
            $table->string('username')->unique();
            $table->string('password');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'expired', 'cancelled'])->default('scheduled');
            $table->text('instructions')->nullable();
            $table->integer('duration_minutes')->default(60); // Duration in minutes
            $table->boolean('email_sent')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('candidate')->references('id')->on('job_applications')->onDelete('cascade');
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
        Schema::dropIfExists('psychotest_schedules');
    }
}
