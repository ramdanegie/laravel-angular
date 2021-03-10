<?php
namespace App\Transaksi;

use App\Master\SettingDataFixed;
class BatalRegistrasi extends Transaksi
{
    protected $table ="batalregistrasi_t";
    protected $fillable = ['alasanpembatalan'];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
    protected $depositId= null;

    public  function pasiendaftar(){
        return $this->belongsTo('App\Transaksi\PasienDaftar', 'pasiendaftarfk');
    }

    public function pembatalan(){
        return $this->belongsTo('App\Master\Pembatalan', 'pembatalanfk');
    }

    public function pegawai(){
        return $this->belongsTo('App\Master\Pegawai', 'pegawaifk');
    }

}
