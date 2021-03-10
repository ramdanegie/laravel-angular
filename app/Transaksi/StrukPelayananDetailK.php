<?php

namespace App\Transaksi;

class StrukPelayananDetailK extends Transaksi
{
    protected $table ="strukpelayanandetailk_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\StrukPostingTransformer');}

}
