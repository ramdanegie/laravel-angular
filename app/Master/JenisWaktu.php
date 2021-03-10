<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 18/07/2018
 * Time: 14.20
 */

namespace App\Master;

class JenisWaktu extends MasterModel
{
    protected $table ="jeniswaktu_m";
    protected $fillable = [];

    public $timestamps = false;
    protected $primaryKey = "id";

    public function kelompok_produk(){
        return $this->belongsTo('App\Master\KelompokProduk', 'objectkelompokprodukfk');
    }
}