<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;

class Pasien extends MasterModel
{
    protected $table ="pasien_m";
    protected $fillable = [];
    public $timestamps = false;

    public function getUmurAttribute(){

      $tahun=(int)date('Y', strtotime($this->tgllahir));
      $bulan=(int)date('m', strtotime($this->tgllahir));
      $tanggal=(int)date('d', strtotime($this->tgllahir));
      $selisih_bulan=0;
      $selisih_tahun=0;

      $selisih_tanggal = (int)date('d')-$tanggal;
      if($selisih_tanggal<0){
        $selisih_bulan--;
        $selisih_tanggal+= 30;
      }

      $selisih_bulan += (int)date('m')-$bulan;
      if($selisih_bulan<0){
        $selisih_tahun--;
        $selisih_bulan += 12;
      }


      $selisih_tahun += (int)date('Y') - $tahun;

      return abs($selisih_tahun).' Tahun, '.abs($selisih_bulan).' Bulan, '.abs($selisih_tanggal).' Hari.';

    }

    public function jenis_kelamin(){
        return $this->belongsTo('App\Master\JenisKelamin', 'objectjeniskelaminfk');
    }

    public function agama(){
        return $this->belongsTo('App\Master\Agama','objectagamafk');
    }
    public function statusperkawinan(){
        return $this->belongsTo('App\Master\StatusPerkawinan','objectstatusperkawinanfk');
    }

    public function alamat(){
        return $this->hasMany('App\Master\Alamat','nocmfk','id');
    }

}
