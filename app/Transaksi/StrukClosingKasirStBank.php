<?php

namespace App\Transaksi;

class StrukClosingKasirStBank extends Transaksi
{
    protected $table ="strukclosingkasirstbank_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
