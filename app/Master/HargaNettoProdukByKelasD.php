<?php

namespace App\Master;

class HargaNettoProdukByKelasD extends MasterModel
{
    protected $table ="harganettoprodukbykelasd_m";
    protected $fillable = [];
    public $timestamps = false;

    public function asal_produk(){
        return $this->belongsTo('App\Master\AsalProduk', 'objectasalprodukfk');
    }
    public function jenis_tarif(){
        return $this->belongsTo('App\Master\JenisTarif', 'objectjenistariffk');
    }
    public function kelas(){
        return $this->belongsTo('App\Master\Kelas', 'objectkelasfk');
    }
    public function mata_uang(){
        return $this->belongsTo('App\Master\MataUang', 'objectmatauangfk');
    }
    public function produk(){
        return $this->belongsTo('App\Master\Produk', 'objectprodukfk');
    }
}
