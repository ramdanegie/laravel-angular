<?php

namespace App\Master;

class JenisDiet extends MasterModel
{
    protected $table ="jenisdiet_m";
    protected $fillable = [];

    public $timestamps = false;
    protected $primaryKey = "id";

    public function kelompok_produk(){
        return $this->belongsTo('App\Master\KelompokProduk', 'objectkelompokprodukfk');
    }
}