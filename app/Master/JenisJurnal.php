<?php

namespace App\Master;


class JenisJurnal extends MasterModel
{
    protected $table ="jenisjurnal_m";
    protected $fillable = [];

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\JenisJurnalTransformer');}

    public function posting_jurnal(){
        return $this->hasMany('App\Transaksi\PostingJurnalTransaksi', 'objectjenisaccountfk');
    }

}
