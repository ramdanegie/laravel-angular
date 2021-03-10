<?php

namespace App\Transaksi;


class SdmPenelitianEksternal extends Transaksi
{
    protected $table = 'sdm_penelitianeksternal_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}