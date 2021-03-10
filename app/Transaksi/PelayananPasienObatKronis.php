<?php

namespace App\Transaksi;

use App\BaseModel;

class PelayananPasienObatKronis extends Transaksi
{
    protected $table = "pelayananpasienobatkronis_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}