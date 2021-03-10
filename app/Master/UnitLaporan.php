<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class UnitLaporan extends MasterModel
{
    protected $table ="unitlaporan_m";
    protected $fillable = [];

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectunitlaporanfk');
    }
}
