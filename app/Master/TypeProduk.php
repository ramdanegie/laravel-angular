<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class TypeProduk extends MasterModel
{
    protected $table ="typeproduk_m";
    protected $fillable = [];
    public $timestamps = false;

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objecttypeprodukfk');
    }
}
