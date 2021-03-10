<?php

namespace App\Transaksi;

class StrukKonfirmasi extends Transaksi
{
    protected $table ="strukkonfirmasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\StrukPostingTransformer');}

}
