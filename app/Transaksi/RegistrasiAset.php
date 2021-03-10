<?php

namespace App\Transaksi;

use App\BaseModel;

class RegistrasiAset extends Transaksi
{
    protected $table = "registrasiaset_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}