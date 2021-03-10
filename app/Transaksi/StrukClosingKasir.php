<?php

namespace App\Transaksi;

class StrukClosingKasir extends Transaksi
{
    protected $table ="strukclosingkasir_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
