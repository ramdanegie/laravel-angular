<?php
namespace App\Master;

//use Illuminate\Database\Eloquent\Model;



class MapProdukToLaporanRl extends MasterModel
{
    protected $table ="mapproduktolaporanrl_m";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function JenisLaporan(){
        return $this->belongsTo('App\Master\JenisLaporan', 'id');
    }

    public function KelompokLaporan(){
        return $this->belongsTo('App\Master\KelompokLaporan', 'id');
    }
}
