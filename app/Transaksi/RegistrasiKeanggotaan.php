<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 01/08/2018
 * Time: 09.13
 */

namespace App\Transaksi;

class RegistrasiKeanggotaan extends Transaksi
{
    protected $table ="sdm_registrasikeanggotaan_t";
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";


}