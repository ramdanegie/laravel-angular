<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 08/11/2018
 * Time: 17.06
 */

namespace App\Transaksi;

class MapLaporanAkuntansiToCoaD extends Transaksi
{
    protected $table ="maplaporanakuntansitocoad_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}