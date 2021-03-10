<?php

namespace App\Transaksi;


class SurveyKepuasanPelanggan extends Transaksi
{
    protected $table = 'surveykepuasanpelanggan_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}