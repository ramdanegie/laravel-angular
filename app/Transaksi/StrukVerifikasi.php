<?php

namespace App\Transaksi;

class StrukVerifikasi extends Transaksi
{
    protected $table ="strukverifikasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\StrukPostingTransformer');}

}
