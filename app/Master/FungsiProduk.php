<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class FungsiProduk extends MasterModel
{
    protected $table ="fungsiproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectfungsiprodukfk');
    }
}
