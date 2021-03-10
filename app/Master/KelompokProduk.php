<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class KelompokProduk extends MasterModel
{
    protected $table ="kelompokproduk_m";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";

    public function bahan_produk(){
        return $this->hasMany('App\Master\BahanProduk', 'objectkelompokprodukfk');
    }

    public function bentuk_produk(){
      return $this->hasMany('App\Master\BentukProduk', 'objectbentukprodukfk');
    }

    public function departemen(){
      return $this->belongsTo('App\Master\Departemen', 'objectdepartemenfk');
    }

    public function jenis_transaksi(){
      return $this->belongsTo('App\Master\JenisTransaksi', 'objectjenistransaksifk');
    }
}
