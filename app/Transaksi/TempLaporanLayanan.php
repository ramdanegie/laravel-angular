<?php
namespace App\Transaksi;

class TempLaporanLayanan extends Transaksi
{
    protected $table = "templaporanlayanan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}