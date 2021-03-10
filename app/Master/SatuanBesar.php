<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class SatuanBesar extends MasterModel
{
    protected $table ="satuanbesar_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectsatuanbesarfk');
    }
}
