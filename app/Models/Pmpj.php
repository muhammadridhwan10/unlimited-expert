<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pmpj extends Model
{
    use HasFactory;

    protected $table = 'pmpj';

    protected $fillable = [
        'project_id',
        'task_id',
        'ruang_lingkup_jasa',
        'profil_pengguna_jasa',
        'risiko_ppj',
        'profil_bisnis_pengguna_jasa',
        'risiko_pbpj',
        'domisili_pengguna_jasa',
        'risiko_domisili',
        'politically_exposed_person',
        'risiko_exposeperson',
        'transaksi_negara_risiko_tinggi',
        'risiko_fatf',
        'prosedur_pmpj',
        'link_surat_pernyataan',
        'kesimpulan',
        'pmpj_sederhana',
        'jenis_pengguna_jasa',
        'pengguna_jasa_bertindak_untuk',
        'namapenggunajasa',
        'nib',
        'alamatpengguna',
        'no_telp',
        'namapihak',
        'jabatanpihak',
        'noidentitaspihak',
        'namabo',
        'nibbo',
        'alamatbo',
        'no_telpbo',
        'namapihakbo',
        'jabatanpihakbo',
        'noidentitaspihakbo',
        'link_arsip',
        'verifikasi',
        'ptransaksi',
        'dokumentasi',
    ];
}
