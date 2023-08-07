<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmpjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pmpj', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('task_id');
            $table->string('ruang_lingkup_jasa')->nullable();
            $table->string('profil_pengguna_jasa')->nullable();
            $table->string('risiko_ppj')->nullable();
            $table->string('profil_bisnis_pengguna_jasa')->nullable();
            $table->string('risiko_pbpj')->nullable();
            $table->string('domisili_pengguna_jasa')->nullable();
            $table->string('risiko_domisili')->nullable();
            $table->string('politically_exposed_person')->nullable();
            $table->string('risiko_exposeperson')->nullable();
            $table->string('transaksi_negara_risiko_tinggi')->nullable();
            $table->string('risiko_fatf')->nullable();
            $table->string('prosedur_pmpj')->nullable();
            $table->string('link_surat_pernyataan')->nullable();
            $table->string('kesimpulan')->nullable();
            $table->string('pmpj_sederhana')->nullable();
            $table->string('jenis_pengguna_jasa')->nullable();
            $table->string('pengguna_jasa_bertindak_untuk')->nullable();
            $table->string('namapenggunajasa')->nullable();
            $table->bigInteger('nib')->nullable();
            $table->text('alamatpengguna')->nullable();
            $table->bigInteger('no_telp')->nullable();
            $table->string('namapihak')->nullable();
            $table->string('jabatanpihak')->nullable();
            $table->string('noidentitaspihak')->nullable();
            $table->string('namabo')->nullable();
            $table->bigInteger('nibbo')->nullable();
            $table->text('alamatbo')->nullable();
            $table->bigInteger('no_telpbo')->nullable();
            $table->string('namapihakbo')->nullable();
            $table->string('jabatanpihakbo')->nullable();
            $table->string('noidentitaspihakbo')->nullable();
            $table->string('link_arsip')->nullable();
            $table->text('verifikasi')->nullable();
            $table->text('ptransaksi')->nullable();
            $table->text('dokumentasi')->nullable();
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
        Schema::dropIfExists('pmpj');
    }
}
