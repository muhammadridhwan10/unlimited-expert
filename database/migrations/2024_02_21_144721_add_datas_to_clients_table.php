<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatasToClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('name_pic')->nullable();
            $table->string('email_pic')->nullable();
            $table->string('telp_pic')->nullable();
            $table->string('total_company_income_per_year')->nullable();
            $table->string('total_company_assets_value')->nullable();
            $table->string('total_employee')->nullable();
            $table->string('total_branch_offices')->nullable();
            $table->integer('client_ownership_id')->nullable();
            $table->integer('accounting_standars_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
}
