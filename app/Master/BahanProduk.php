<?php

namespace App\Master;

use Illuminate\Database\Eloquent\Model;

class BahanProduk extends MasterModel
{
    protected $table ="bahanproduk_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectbahanprodukfk');
    }

    public function departemen(){
        return $this->belongsTo('App\Master\Departemen', 'objectdepartemenfk');
    }

    public function kelompok_produk(){
      return $this->belongsTo('App\Master\KelompokProduk', 'objectkelompokprodukfk');
    }
}
