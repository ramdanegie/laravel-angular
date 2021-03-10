<?php

namespace App\Transaksi;


class Surveilans extends Transaksi
{
    protected $table = 'surveilans_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}