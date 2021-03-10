<?php

namespace App\Transaksi;


class RiwayatPendidikan extends Transaksi
{
    protected $table = 'riwayatpendidikan_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}