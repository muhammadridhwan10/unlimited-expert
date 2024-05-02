<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_number');
            $table->string('fee')->nullable();
            $table->string('period')->nullable();
            $table->string('name_pic')->nullable();
            $table->string('email_pic')->nullable();
            $table->string('telp_pic')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('name_invoice')->nullable();
            $table->string('position')->nullable();
            $table->integer('telp')->nullable();
            $table->string('npwp')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('total_company_income_per_year')->nullable();
            $table->string('total_company_assets_value')->nullable();
            $table->string('total_employee')->nullable();
            $table->string('total_branch_offices')->nullable();
            $table->integer('client_business_sector_id')->nullable();
            $table->integer('client_ownership_id')->nullable();
            $table->integer('accounting_standars_id')->nullable();
            $table->string('project_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('estimated_hrs')->nullable();
            $table->integer('budget')->nullable();
            $table->text('description')->nullable();
            $table->text('label')->nullable();
            $table->text('tags')->nullable();
            $table->integer('template_task_id')->nullable();
            $table->integer('public_accountant_id')->nullable();
            $table->integer('leader_project')->nullable();
            $table->string('status');
            $table->integer('ph_partners')->nullable();
            $table->integer('ph_manager')->nullable();
            $table->integer('ph_senior')->nullable();
            $table->integer('ph_associate')->nullable();
            $table->integer('ph_assistant')->nullable();
            $table->integer('rate_partners')->nullable();
            $table->integer('rate_manager')->nullable();
            $table->integer('rate_senior')->nullable();
            $table->integer('rate_associate')->nullable();
            $table->integer('rate_assistant')->nullable();
            $table->integer('is_approve')->nullable();
            $table->integer('is_fulfilling_prospective_clients')->default('0');
            $table->integer('is_fulfill')->default('0');
            $table->integer('created_by');
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
        Schema::dropIfExists('project_orders');
    }
}
