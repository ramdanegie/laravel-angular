<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 11/8/2017
 * Time: 09:58
 */


namespace App\Transaksi;

class HIS_Obat_MS extends Transaksi
{
    protected $table ="his_obat_ms_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}