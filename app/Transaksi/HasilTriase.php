<?php

namespace App\Transaksi;

// use Illuminate\Database\Eloquent\Model;

class HasilTriase extends Transaksi
{
    protected $table ="hasiltriase_t";
    protected $fillable = [];

    public $timestamps = false;
    protected $primaryKey = "norec";
    public $incrementing = false;


}
