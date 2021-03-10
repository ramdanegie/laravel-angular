<?php
/**
 * Created by PhpStorm.
 * User: agus.sustian
 * Date: 07/06/2017
 * Time: 16:49
 */


namespace App\Transaksi;

class LogAcc extends Transaksi
{
    protected $table ="logacc_t";
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
