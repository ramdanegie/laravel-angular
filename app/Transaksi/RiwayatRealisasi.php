<?php
namespace App\Transaksi;

class RiwayatRealisasi extends Transaksi
{
    protected $table = "riwayatrealisasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}