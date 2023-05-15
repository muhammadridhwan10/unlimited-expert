<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValueMaterialitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('value_materialitas', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('materialitas_id');
            $table->string('data2020');
            $table->string('data2021');
            $table->string('inhouse');
            $table->string('audited');
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
        Schema::dropIfExists('value_materialitas');
    }
}
