<?php

namespace App\Transaksi;

// use Illuminate\Database\Eloquent\Model;

class PengantarPasien extends Transaksi
{
    protected $table ="pengantarpasien_t";
    protected $fillable = [];

    public $timestamps = false;
    protected $primaryKey = "norec";
    public $incrementing = false;


}
