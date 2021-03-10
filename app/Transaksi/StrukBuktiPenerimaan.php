<?php

namespace App\Transaksi;

class StrukBuktiPenerimaan extends Transaksi
{
    protected $table ="strukbuktipenerimaan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


    public function struk_pelayanan(){
        return $this->belongsTo('App\Transaksi\StrukPelayanan', 'nostrukfk');
    }

    public function struk_bukti_penerimaan_carabayar(){
        return $this->hasMany('App\Transaksi\StrukBuktiPenerimaanCaraBayar', 'nosbmfk','norec');
    }
//    public function pegawai(){
//        return $this->belongsTo('App\Master\Pegawai', 'objectpegawaipenerimafk');
//    }
    public function login_user(){
        return $this->belongsTo('App\Master\LoginUser', 'objectpegawaipenerimafk');
    }



    public function getstatusSetorAttribute(){
        if($this->noverifikasifk==null){
            return "Belum Setor";
        }else{
            return "Sudah Setor";
        }
    }
}
