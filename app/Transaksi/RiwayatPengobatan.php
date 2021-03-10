<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 23/10/2018
 * Time: 12:26
 */


namespace App\Transaksi;

class RiwayatPengobatan extends Transaksi
{
    protected $table = "riwayatpengobatan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}