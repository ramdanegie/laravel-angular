<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 10/24/2019
 * Time: 8:48 PM
 */

namespace App\Transaksi;

class AntrianApotik extends Transaksi
{
    protected $table ="antrianapotik_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}