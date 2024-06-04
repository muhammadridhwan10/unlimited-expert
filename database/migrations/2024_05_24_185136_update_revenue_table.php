<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRevenueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revenues', function (Blueprint $table) {
            // Menghapus kolom yang tidak diperlukan
            $table->dropColumn(['account_id', 'customer_id', 'category_id', 'payment_method', 'reference','add_receipt']);

            // Menambah kolom yang baru
            $table->string('amount')->change();
            $table->text('description')->nullable()->change();
            $table->unsignedBigInteger('invoice_id')->nullable()->after('id');
            $table->unsignedBigInteger('user_id')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revenues', function (Blueprint $table) {
            // Mengembalikan kolom yang dihapus
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();

            // Menghapus kolom yang ditambahkan
            $table->dropColumn(['invoice_id', 'user_id']);
        });
    }
}
