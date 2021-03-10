<?php

namespace App\Transaksi;

// use Illuminate\Database\Eloquent\Model;

class DetailHasilTriase extends Transaksi
{
    protected $table ="detailhasiltriase_t";
    protected $fillable = [];

    public $timestamps = false;
    protected $primaryKey = "norec";
    public $incrementing = false;


}
