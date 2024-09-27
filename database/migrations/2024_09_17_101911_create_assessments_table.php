<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('form_type');
            $table->unsignedBigInteger('criteria_id')->nullable();
            $table->text('work_targets')->nullable();
            $table->text('criteria')->nullable();
            $table->text('performance_achievements')->nullable();
            $table->string('project_name')->nullable();
            $table->tinyInteger('supervisor_id')->nullable();
            $table->tinyInteger('self_assessment')->nullable();
            $table->tinyInteger('supervisor_assessment')->nullable();
            $table->tinyInteger('final_assessment')->nullable(); 
            $table->text('comment')->nullable();
            $table->text('performance_progress')->nullable();
            $table->text('barriers')->nullable();
            $table->text('follow_up')->nullable();
            $table->text('advantages')->nullable();
            $table->text('tiers')->nullable();
            $table->text('training_plan')->nullable();
            $table->unsignedBigInteger('appraisal_id')->nullable();
            $table->year('year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessments');
    }
}
