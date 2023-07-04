<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentifiedMisstatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identified_misstatements', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id')->nullable();
            $table->integer('task_id')->nullable();
            $table->text('description')->nullable();
            $table->string('period')->nullable();
            $table->string('type_misstatement')->nullable();
            $table->string('corrected')->nullable();
            $table->string('assets')->nullable();
            $table->string('liability')->nullable();
            $table->string('equity')->nullable();
            $table->string('income')->nullable();
            $table->string('re')->nullable();
            $table->text('cause_of_misstatement')->nullable();
            $table->text('managements_reason')->nullable();
            $table->text('summary')->nullable();
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
        Schema::dropIfExists('identified_misstatements');
    }
}
