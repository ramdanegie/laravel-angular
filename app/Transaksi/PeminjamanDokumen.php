<?php
namespace App\Transaksi;

class PeminjamananDokumen extends Transaksi
{
    protected $table ="peminjamandokumen_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountTransformer');}

//    public function periode_account_saldo(){
//        return $this->hasMany('App\Transaksi\PeriodeAccountSaldo', 'kdperiodeaccount', 'kdperiodeaccount');
//    }
//
    public  function dokumen(){
        return $this->belongsTo('App\Master\Dokumen', 'objectdokumenfk');
    }
//
//    public function kelas(){
//        return $this->belongsTo('App\Master\Kelas', 'objectkelasfk');
//    }
//
//    public function kelompok_pasien(){
//        return $this->belongsTo('App\Master\KelompokPasien', 'objectkelompokpasienlastfk');
//    }

}
