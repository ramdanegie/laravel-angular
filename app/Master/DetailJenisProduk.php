<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class DetailJenisProduk extends MasterModel
{
    protected $table ="detailjenisproduk_m";
    protected $fillable = [];
    public $timestamps = false;

    public function produk(){
        return $this->hasMany('App\Master\Produk', 'objectdetailjenisprodukfk');
    }
    public function jenis_produk(){
        return $this->belongsTo('App\Master\JenisProduk', 'objectjenisprodukfk');
    }
}
