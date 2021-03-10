<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 12/14/2017
 * Time: 13:11
 */

namespace App\Transaksi;
class ProduksiNonSteril extends Transaksi
{
    protected $table ="produksinonsteril_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;


}