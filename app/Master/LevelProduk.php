<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class LevelProduk extends MasterModel
{
    protected $table ="levelproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectlevelprodukfk');
    }
}
