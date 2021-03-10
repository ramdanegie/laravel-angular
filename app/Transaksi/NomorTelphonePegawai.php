<?php

namespace App\Transaksi;


class NomorTelphonePegawai extends Transaksi
{
    protected $table = 'nomortelphone_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}