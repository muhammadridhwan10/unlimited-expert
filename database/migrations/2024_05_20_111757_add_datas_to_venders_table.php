<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatasToVendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('contact')->nullable()->change();
            $table->string('billing_name')->nullable()->change();
            $table->string('billing_country')->nullable()->change();
            $table->string('billing_state')->nullable()->change();
            $table->string('billing_city')->nullable()->change();
            $table->string('billing_phone')->nullable()->change();
            $table->string('billing_zip')->nullable()->change();
            $table->text('billing_address')->nullable()->change();
            $table->string('shipping_name')->nullable()->change();
            $table->string('shipping_country')->nullable()->change();
            $table->string('shipping_state')->nullable()->change();
            $table->string('shipping_city')->nullable()->change();
            $table->string('shipping_phone')->nullable()->change();
            $table->string('shipping_zip')->nullable()->change();
            $table->text('shipping_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('venders', function (Blueprint $table) {
            //
        });
    }
}
