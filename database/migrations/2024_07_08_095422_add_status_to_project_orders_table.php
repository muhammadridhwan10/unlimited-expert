<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToProjectOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_orders', function (Blueprint $table) {
            $table->string('status_client')->default('new');
            $table->string('total_company_profit_or_loss')->nullable();
            $table->string('periode')->nullable();
            $table->string('where_did_you_find_out_about_us')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_orders', function (Blueprint $table) {
            //
        });
    }
}
