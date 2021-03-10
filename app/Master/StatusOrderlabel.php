<?php
/**
 * Created by PhpStorm.
 * User: Giw
 * Date: 12/12/2017
 * Time: 10:31 AM
 */
namespace App\Master;

use App\Master\SettingDataFixed;
class StatusOrderLabel extends Transaksi
{
    protected $table ="statusorderlabel_m";
//    protected $fillable = ['tglpendaftaran'];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public  function daftarpermintaanlabel(){
        return $this->belongsTo('App\Transaksi\DaftarPermintaanLabel', 'objectstatusorderfk');
    }


}