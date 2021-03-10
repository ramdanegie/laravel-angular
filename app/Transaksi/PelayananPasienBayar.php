<?php

namespace App\Transaksi;

class PelayananPasienBayar extends Transaksi
{
    protected $table ="pelayananpasienbayar_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}
