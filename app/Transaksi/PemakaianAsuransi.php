<?php
namespace App\Transaksi;

class PemakaianAsuransi extends Transaksi
{
    protected $table ="pemakaianasuransi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountTransformer');}


//    public function periode_account_saldo(){
//        return $this->hasMany('App\Transaksi\PeriodeAccountSaldo', 'kdperiodeaccount', 'kdperiodeaccount');
//    }
//
//    public  function ruangan(){
//        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
//    }
//
//    public function kelas(){
//        return $this->belongsTo('App\Master\Kelas', 'objectkelasfk');
//    }
//
//    public function kelompok_pasien(){
//        return $this->belongsTo('App\Master\KelompokPasien', 'objectkelompokpasienlastfk');
//    }

    public function asuransi(){
        return  $this->belongsTo('App\Master\Rekanan', 'asuransifk');
    }
    public function asuransi_pasien(){
        return  $this->belongsTo('App\Master\AsuransiPasien', 'objectasuransipasienfk');
    }
}
