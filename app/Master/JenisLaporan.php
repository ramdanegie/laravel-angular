<?php

namespace App\Master;

//use Illuminate\Database\Eloquent\Model;

class JenisLaporan extends MasterModel
{
    protected $table = 'jenislaporan_m';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";

    public function mapproduktolaporanrl(){
        return $this->belongsTo('App\Transaksi\MapProdukToLaporanRl', 'objectjenislaporanfk');
    }
}
