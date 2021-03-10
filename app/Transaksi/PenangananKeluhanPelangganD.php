<?php

namespace App\Transaksi;

use App\BaseModel;

class PenangananKeluhanPelangganD extends Transaksi
{
    protected $table = "penanganankeluhanpelanggand_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
}