<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class BentukProduk extends MasterModel
{
    protected $table ="bentukproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectbentukprodukfk');
    }

    public function departemen(){
      return $this->belongsTo('App\Master\Departemen', 'objectdepartemenfk');
    }

    public function kelompok_produk(){
      return $this->belongsTo('App\Master\KelompokProduk', 'objectkelompokprodukfk');
    }
}
