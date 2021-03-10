<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 8/8/2018
 * Time: 5:41 PM
 */

namespace App\Transaksi;

class BPJSKlaimTxt extends Transaksi
{
    protected $table ="bpjsklaimtxt_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}