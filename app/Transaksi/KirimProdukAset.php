<?php

namespace App\Transaksi;

use App\BaseModel;

class KirimProdukAset extends Transaksi
{
    protected $table = "kirimprodukaset_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
}