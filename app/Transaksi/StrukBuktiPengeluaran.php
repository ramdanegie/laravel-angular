<?php

namespace App\Transaksi;

class StrukBuktiPengeluaran extends Transaksi
{
    protected $table ="strukbuktipengeluaran_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
