<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 11/29/2017
 * Time: 10:04
 */

namespace App\Transaksi;

class StokProdukDetailAdjusment extends Transaksi
{
    protected $table ="stokprodukdetailadjustment_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;


//    public function ruangan(){
//        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
//    }
//
//    public function produk(){
//        return $this->belongsTo('App\Master\Produk', 'objectruanganfk');
//    }

}