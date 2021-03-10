<?php

namespace App\Transaksi;

use App\BaseModel;

class SkriningFarmasi extends Transaksi
{
    protected $table = "skriningfarmasi_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
}