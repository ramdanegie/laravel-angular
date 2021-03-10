<?php
/**
 * Created by PhpStorm.
 * User: as@nengepic
 * Date: 05/09/2018
 * Time: 11.23
 */

namespace App\Transaksi;

class BPJSGagalKlaimTxt extends Transaksi
{
    protected $table ="bpjsgagalklaimtxt_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}