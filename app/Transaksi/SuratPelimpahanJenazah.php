<?php
namespace App\Transaksi;

class SuratPelimpahanJenazah extends Transaksi {
    protected $table = "suratpelimpahanjenazah_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}