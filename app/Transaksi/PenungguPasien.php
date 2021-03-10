<?php

namespace App\Transaksi;

class PenungguPasien extends Transaksi
{
    protected $table = "penunggupasien_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
