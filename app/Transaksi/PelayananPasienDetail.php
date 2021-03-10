<?php
namespace App\Transaksi;

class PelayananPasienDetail extends Transaksi
{
    protected $table ="pelayananpasiendetail_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


    public function getPelayananSebelumReturnAttribute(){
        $pd = PelayananPasienDetail::where('norec',$this->norectriger)->first();
        return $pd;
    }
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



}
