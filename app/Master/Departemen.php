<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class Departemen extends MasterModel
{
    protected $table = 'departemen_m';
    protected $fillable= [];

    public function ruangan(){
        return $this->hasMany('App\Ruangan', 'objectdepartemenfk');
    }

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectdepartemenfk');
    }

    public function bahan_produk(){
      return $this->hasMany('App\Master\BahanProduk', 'objectdepartemenfk');
    }

    public function kelompok_produk(){
      return $this->hasMany('App\Master\KelompokProduk', 'objectdepartemenfk');
    }

    public function bentuk_produk(){
      return $this->hasMany('App\Master\BentukProduk', 'objectdepartemenfk');
    }

    public function jenis_perawatan(){
      return $this->belongsTo('App\Master\JenisPerawatan', 'objectjenisperawatanfk');
    }


}
