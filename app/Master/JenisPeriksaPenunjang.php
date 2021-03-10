<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class JenisPeriksaPenunjang extends MasterModel
{
    protected $table ="jenisperiksapenunjang_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectjenisperiksapenunjangfk');
    }
}
