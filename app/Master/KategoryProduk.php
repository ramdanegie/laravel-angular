<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class KategoryProduk extends MasterModel
{
    protected $table ="kategoryproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectkategoryprodukfk');
    }
}
