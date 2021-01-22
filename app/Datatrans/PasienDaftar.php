<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 10:27 AM
 */


namespace App\Datatrans;

class PasienDaftar extends Datatrans
{
    protected $table ="pasiendaftar_t";
    protected $fillable = ['objectkelompokpasienlastfk'];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
    protected $depositId= null;

    public function __construct(){
        $set = SettingDataFixed::where('namafield', 'idProdukDeposit')->first();
        $this->depositId= ($set) ? (int)$set->nilaifield: null;
//        $this->setTransformerPath('App\Transformers\Transaksi\PeriodeAccountTransformer');

    }



    public  function ruangan(){
        return $this->belongsTo('App\Datatans\Ruangan', 'objectruanganlastfk');
    }

    public function kelas(){
        return $this->belongsTo('App\Datatans\Kelas', 'objectkelasfk');
    }

    public function pemakaian_asuransi(){
        return $this->hasManyThrough('App\Datatrans\PemakaianAsuransi', 'App\Datatrans\AntrianPasienDiperiksa', 'noregistrasifk', 'noregistrasifk', 'norec');
    }

    public function pemakaian_asuransi2(){
        return $this->hasMany('App\Datatrans\PemakaianAsuransi', 'noregistrasifk');
    }

    public function kelompok_pasien(){
        return $this->belongsTo('App\Datatrans\KelompokPasien', 'objectkelompokpasienlastfk');
    }

    public function rekanan(){
        return $this->belongsTo('App\Datatrans\Rekanan', 'objectrekananfk');
    }

    public function dokter(){
        return $this->belongsTo('App\Datatrans\Pegawai', 'objectpegawaifk');
    }

    public function antrian_pasien_diperiksa(){
        return $this->hasMany('App\Datatans\AntrianPasienDiperiksa', 'noregistrasifk', 'norec');
    }

    public function pelayanan_pasien()
    {
        return $this->hasManyThrough('App\Datatrans\PelayananPasien', 'App\Datatrans\AntrianPasienDiperiksa', 'noregistrasifk', 'noregistrasifk', 'norec');
    }

    public function setDepositIdAttribute($produkDepositId){
        $this->depositId = $produkDepositId;
    }

    public function list_deposit(){
        return $this->pelayanan_pasien()->where('produkfk', $this->depositId);
    }

    public function pelayanan_pasien_detail()
    {
        return $this->hasManyThrough('App\Transaksi\PelayananPasienDetail', 'App\Transaksi\AntrianPasienDiperiksa', 'noregistrasifk', 'noregistrasifk', 'norec');
    }

    public  function pasien(){
        return $this->belongsTo('App\Master\Pasien', 'nocmfk');
    }

    public function last_ruangan(){
        return $this->belongsTo('App\Master\Ruangan', 'objectruanganlastfk');
    }

    public function  getIsVerifiedAttribute(){
        $pelayananPasien= $this->pelayanan_pasien()->whereNotNull('strukfk')->where('produkfk', '<>', $this->depositId)->get();
        if(count($pelayananPasien)>0){
            return true;
        }else{
            return false;
        }
    }

    protected function isBayar(){
        $pelayananPasien= $this->pelayanan_pasien()->whereNotNull('strukfk')->where('produkfk', '<>', $this->depositId)->get();
        foreach ($pelayananPasien as $pp){
            if($pp->struk_pelayanan) {
                if($pp->struk_pelayanan->nosbmlastfk != null){
                    return true;
                }

            }
        }
        return false;
    }

    public function getIsBayarAttribute(){
        return $this->isBayar();
    }

    public function getStatusBayarAttribute(){
        if($this->isBayar()){
            return 'Sudah Bayar';
        }else{
            return 'Belum Bayar';
        }
    }
}
