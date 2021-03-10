<?php

namespace App\Transaksi;

class PostingJurnalTransaksi extends Transaksi
{
    protected $table ="postingjurnaltransaksi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\PostingJurnalTransaksiTransformer');}

    public function jenis_jurnal(){
        return $this->belongsTo('App\Master\JenisJurnal', 'objectjenisjurnalfk');
    }

    public function getStatusVerifikasiAttribute(){
        if($this->noverifikasi==null){
            return "Belum Verifikasi";
        }
        return "Verifikasi";
    }

    public function getIsVerifiedAttribute(){
        if($this->noverifikasi==null){
            return false;
        }
        return true;
    }

    public function setTglTransaksiAttribute($date){
        $cleanDate = $date;
        $arrayDate = explode('/', $date);
        if(count($arrayDate)==3){
            if((int)$arrayDate[0]<10){
                $arrayDate[0] = (string)('0'.(int)$arrayDate[0]);
            }
            if((int)$arrayDate[1]<10){
                $arrayDate[1] = (string)('0'.(int)$arrayDate[1]);
            }
            $cleanDate = $arrayDate[2].'-'.$arrayDate[1].'-'.$arrayDate[0];
        }
        $this->attributes['tglbuktitransaksi'] = $cleanDate;
    }

    public function jurnal_detail(){
        return $this->hasMany('App\Transaksi\PostingJurnalTransaksiD', 'norecrelated');
    }

}
