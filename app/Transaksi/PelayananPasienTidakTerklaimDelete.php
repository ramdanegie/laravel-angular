<?php
namespace App\Transaksi;

class PelayananPasienTidakTerklaimDelete extends Transaksi
{
    protected $table ="pelayananpasientidakterklaimdelete_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


    public function antrian_pasien_diperiksa(){
        return $this->belongsTo('App\Transaksi\AntrianPasienDiperiksa', 'noregistrasifk','norec');
    }

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountTransformer');}

//    public function periode_account_saldo(){
//        return $this->hasMany('App\Transaksi\PeriodeAccountSaldo', 'kdperiodeaccount', 'kdperiodeaccount');
//    }
//
    public  function produk(){
        return $this->belongsTo('App\Master\Produk', 'produkfk');
    }


    public function struk_pelayanan(){
        return $this->belongsTo('App\Transaksi\StrukPelayanan', 'strukfk');
    }

//    public function pelayan_pasien_detail(){
//        return $this->hasMany('App\Transaksi\PelayananPasienDetail', 'strukfk');
//    }
//
    public function kelas(){
        return $this->belongsTo('App\Master\Kelas', 'kelasfk');
    }
//
//    public function kelompok_pasien(){
//        return $this->belongsTo('App\Master\KelompokPasien', 'objectkelompokpasienlastfk');
//    }

}
