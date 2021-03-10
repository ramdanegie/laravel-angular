<?php

namespace App\Transaksi;


class SurveilansOperasi extends Transaksi
{
    protected $table = 'surveilansoperasi_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}