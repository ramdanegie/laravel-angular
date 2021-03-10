<?php

namespace App\Transaksi;


class KegiatanPenelitianPegawai extends Transaksi
{
    protected $table = 'kegiatanpenelitianpegawai_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}