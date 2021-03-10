<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 18/07/2018
 * Time: 15.43
 */
namespace App\Master;

class KategoryDiet extends MasterModel
{
    protected $table = "kategorydiet_m";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "id";

    public function kelompok_produk()
    {
        return $this->belongsTo('App\Master\KelompokProduk', 'objectkelompokprodukfk');
    }
}