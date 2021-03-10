<?php
namespace App\Transaksi;

class SuratPermohonanJenazah extends Transaksi {
    protected $table = "suratpermohonanjenazah_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}