<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class DetailGolonganProduk extends MasterModel
{
    protected $table ="detailgolonganproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectdetailgolonganprodukfk');
    }
}
