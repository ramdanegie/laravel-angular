<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 10/08/2017
 * Time: 15.54
 */

namespace App\Transaksi;

class KonversiSatuan extends Transaksi
{
    protected $table ="konversisatuan_t";
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
