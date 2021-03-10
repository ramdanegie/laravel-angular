<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class KelompokPelayanan extends MasterModel
{
    protected $table ="kelompokpelayanan_m";
    protected $fillable = [];

    public function jenis_transaksi(){
        return $this->hasMany('App\Master\JenisTransaksi', 'objectkelompokpelayananfk');
    }


}
