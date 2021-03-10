<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 9/13/2017
 * Time: 12:34 PM
 */

namespace App\Transaksi;
class KirimProduk extends Transaksi
{
    protected $table ="kirimproduk_t";
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