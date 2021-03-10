<?php

namespace App\Transaksi;

class TargetAnggaran extends Transaksi
{
    protected $table ="targetanggaran_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
