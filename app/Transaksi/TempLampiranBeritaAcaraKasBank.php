<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 15/11/2018
 * Time: 14:16
 */

namespace App\Transaksi;

class TempLampiranBeritaAcaraKasBank extends Transaksi
{
    protected $table = "temp_lampiranba_kasbank_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}