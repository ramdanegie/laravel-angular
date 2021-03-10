<?php
namespace App\Transaksi;

class RegistrasiPelayananPasien extends Transaksi
{
    protected $table ="registrasipelayananpasien_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountTransformer');}

//    public function pelayanan_pasien(){
//        return $this->belongsTo('App\Transaksi\PelayananPasien',  'noregistrasifk','norec');
//    }
//
//    public  function ruangan(){
//        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk', 'id');
//    }
//
//    public function kelas(){
//        return $this->belongsTo('App\Master\Kelas', 'objectkelasfk');
//    }
//
//    public function kelompok_pasien(){
//        return $this->belongsTo('App\Master\KelompokPasien', 'objectkelompokpasienlastfk');
//    }
    public function pasien_daftar(){
        return $this->belongsTo('App\Transaksi\PasienDaftar', 'noregistrasifk');
    }
    public function kamar(){
        return $this->belongsTo('App\Transaksi\Kamar', 'objectkamarfk');
    }
}
