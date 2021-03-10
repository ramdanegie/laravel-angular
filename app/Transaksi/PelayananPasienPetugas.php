<?php
namespace App\Transaksi;

class PelayananPasienPetugas extends Transaksi
{
    protected $table ="pelayananpasienpetugas_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


    public function pegawai(){
        return $this->belongsTo('App\Master\Pegawai', 'objectpegawaifk');
    }
}