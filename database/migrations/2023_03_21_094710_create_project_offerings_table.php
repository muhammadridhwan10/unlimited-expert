<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectOfferingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_offerings', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('als_partners')->nullable();
            $table->integer('als_senior_manager')->nullable();
            $table->integer('als_manager')->nullable();
            $table->integer('als_senior_associate')->nullable();
            $table->integer('als_associate')->nullable();
            $table->integer('als_intern')->nullable();
            $table->integer('rate_partners')->nullable();
            $table->integer('rate_senior_manager')->nullable();
            $table->integer('rate_manager')->nullable();
            $table->integer('rate_senior_associate')->nullable();
            $table->integer('rate_associate')->nullable();
            $table->integer('rate_intern')->nullable();
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
        Schema::dropIfExists('project_offerings');
    }
}
