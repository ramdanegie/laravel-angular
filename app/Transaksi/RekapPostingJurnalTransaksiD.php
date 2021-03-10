<?php

namespace App\Transaksi;

class RekapPostingJurnalTransaksiD extends Transaksi
{
    protected $table ="rekappostingjurnaltransaksid_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\StrukPostingTransformer');}





}
