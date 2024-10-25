<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsInProjectofferingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_offerings', function (Blueprint $table) {
            $table->renameColumn('als_senior_manager', 'als_leader');
            $table->renameColumn('rate_senior_manager', 'rate_leader');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_offerings', function (Blueprint $table) {
            $table->renameColumn('als_leader', 'als_senior_manager');
            $table->renameColumn('rate_leader', 'rate_senior_manager');
        });
    }
}
