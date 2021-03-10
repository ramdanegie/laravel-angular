<?php

namespace App\Transaksi;
class KirimLIS_ext extends Transaksi
{
    protected $table ="KirimLIS";
    protected $primaryKey = 'kode';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;

}