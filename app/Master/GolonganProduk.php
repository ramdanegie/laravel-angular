<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class GolonganProduk extends MasterModel
{
    protected $table ="golonganproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectgolonganprodukfk');
    }
}
