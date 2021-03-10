<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class JenisTransaksi extends MasterModel
{
    protected $table ="jenistransaksi_m";
    protected $fillable = [];

    public function kelompok_produk(){
        return $this->hasMany('App\Master\KelompokProduk', 'objectjenistransaksifk');
    }

    public function kelompok_pelayanan(){
      return $this->belongsTo('App\Master\KelompokPelayanan', 'objectkelompokpelayananfk');
    }


}
