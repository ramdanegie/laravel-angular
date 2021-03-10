<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class SatuanStandar extends MasterModel
{
    protected $table ="satuanstandar_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectsatuanstandarfk');
    }
}
