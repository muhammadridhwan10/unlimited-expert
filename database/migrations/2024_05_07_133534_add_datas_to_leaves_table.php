<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatasToLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->integer('leave_type_id')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->string('total_leave_days')->nullable()->change();
            $table->string('leave_reason')->nullable()->change();
            $table->string('total_sick_days')->nullable();
            $table->string('sick_letter')->nullable();
            $table->string('absence_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {

        });
    }
}
