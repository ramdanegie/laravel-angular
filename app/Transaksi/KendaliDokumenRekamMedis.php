<?php
namespace App\Transaksi;

class KendaliDokumenRekamMedis extends Transaksi
{
    protected $table ="kendalidokumenrekammedis_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public  function pasien(){
//        return $this->belongsTo('App\Master\Pasien', 'objectruanganfk', 'id');
//    }

}
