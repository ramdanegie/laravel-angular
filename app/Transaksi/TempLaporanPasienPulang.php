<?php
namespace App\Transaksi;

class TempLaporanPasienPulang extends Transaksi
{
    protected $table = "templaporanpasienpulang_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}