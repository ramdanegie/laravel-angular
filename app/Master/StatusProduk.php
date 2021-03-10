<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class StatusProduk extends MasterModel
{
    protected $table ="statusproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectstatusprodukfk');
    }
}
