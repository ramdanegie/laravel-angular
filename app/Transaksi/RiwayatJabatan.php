<?php

namespace App\Transaksi;


class RiwayatJabatan extends Transaksi
{
    protected $table = 'riwayatjabatan_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}