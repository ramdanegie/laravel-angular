<?php

namespace App\Transaksi;


class MasaBerlakuSipStr extends Transaksi
{
    protected $table = 'masaberlakusipstr_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}