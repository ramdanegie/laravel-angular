<?php

namespace App\Transaksi;

class StrukHistori extends Transaksi
{
    protected $table ="strukhistori_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
