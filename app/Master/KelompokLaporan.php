<?php

namespace App\Master;

//use Illuminate\Database\Eloquent\Model;

class KelompokLaporan extends MasterModel
{
    protected $table = 'kelompoklaporan_m';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";

    public function mapproduktolaporanrl(){
        return $this->belongsTo('App\Transaksi\MapProdukToLaporanRl', 'objectkontenfk');
    }
}
