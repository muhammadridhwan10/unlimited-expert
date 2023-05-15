<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSummaryMaterialitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summary_materialitas', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('materialitas_id');
            $table->integer('value_materialitas_id');
            $table->integer('rate');
            $table->string('initialmaterialityom');
            $table->string('finalmaterialityom');
            $table->integer('pmrate');
            $table->string('initialmaterialitypm');
            $table->string('finalmaterialitypm');
            $table->integer('terate');
            $table->string('initialmaterialityte');
            $table->string('finalmaterialityte');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('summary_materialitas');
    }
}
