<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('indicator_id');
            $table->string('indicator_category'); // Contoh: Performance Proyek
            $table->string('indicator_name');     // Contoh: Jumlah proyek yang ditangani
            $table->tinyInteger('score')->unsigned(); // Skala 1 - 5
            $table->decimal('weight', 5, 2);      // Bobot persentase
            $table->text('comments')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->onDelete('cascade');
            $table->foreign('indicator_id')->references('id')->on('attributes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluation_details');
    }
}
