<?php

namespace App\Transaksi;


class RiwayatPelatihan extends Transaksi
{
    protected $table = 'riwayatpelatihan_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}