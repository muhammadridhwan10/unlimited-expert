<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialStatementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_statement', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->string('m')->nullable();
            $table->string('lk')->nullable();
            $table->string('cn')->nullable();
            $table->string('rp')->nullable();
            $table->string('add1')->nullable();
            $table->string('add2')->nullable();
            $table->string('add3')->nullable();
            $table->string('coa')->nullable();
            $table->string('account')->nullable();
            $table->string('unaudited2020')->nullable();
            $table->string('audited2021')->nullable();
            $table->string('inhouse2022')->nullable();
            $table->string('dr')->nullable();
            $table->string('cr')->nullable();
            $table->string('audited2022')->nullable();
            $table->string('jan')->nullable();
            $table->string('feb')->nullable();
            $table->string('mar')->nullable();
            $table->string('apr')->nullable();
            $table->string('may')->nullable();
            $table->string('jun')->nullable();
            $table->string('jul')->nullable();
            $table->string('aug')->nullable();
            $table->string('sep')->nullable();
            $table->string('oct')->nullable();
            $table->string('nov')->nullable();
            $table->string('dec')->nullable();
            $table->string('triwulan1')->nullable();
            $table->string('triwulan2')->nullable();
            $table->string('triwulan3')->nullable();
            $table->string('triwulan4')->nullable();
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
        Schema::dropIfExists('financial_statement');
    }
}
