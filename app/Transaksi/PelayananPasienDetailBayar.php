<?php

namespace App\Transaksi;

class PelayananPasienDetailBayar extends Transaksi
{
    protected $table ="pelayananpasiendetailbayar_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}