<?php

namespace App\Master;

class AsalProduk extends MasterModel
{
    protected $table ="asalproduk_m";
    protected $fillable = [];

    public function departemen(){
        return $this->belongsTo('App\Master\Departemen', 'objectdepartemenfk');
    }
    public function kelompok_produk(){
        return $this->belongsTo('App\Master\KelompokProduk', 'objectkelompokprodukfk');
    }
}