<?php

namespace App\Transaksi;

use App\BaseModel;

class PenangananKeluhanPelanggan extends Transaksi
{
    protected $table = "penanganankeluhanpelanggan_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
}