<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainings', function (Blueprint $table) {

            $table->string('training_title');
            $table->string('location');
            $table->integer('trainer_option')->nullable()->change();

            $table->dropColumn('start_date');
            $table->string('year');

            $table->dropColumn(['trainer', 'training_cost', 'end_date', 'performance', 'status', 'remarks']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainings', function (Blueprint $table) {

            $table->dropColumn('training_title');
            $table->dropColumn('location');
            $table->integer('trainer_option')->nullable(false)->change();
            $table->dropColumn('year');

            $table->integer('trainer');
            $table->float('training_cost')->default(0.00);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('performance')->default(0);
            $table->integer('status')->default(0);
            $table->text('remarks')->nullable();
        });
    }
}
