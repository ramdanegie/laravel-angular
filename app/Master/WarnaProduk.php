<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class WarnaProduk extends MasterModel
{
    protected $table ="warnaproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectwarnaprodukfk');
    }
}
