<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 01/08/2018
 * Time: 15.10
 */
namespace App\Transaksi;

class ReservasiPerpustakaan extends Transaksi
{
    protected $table ="sdm_reservasi_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";


}