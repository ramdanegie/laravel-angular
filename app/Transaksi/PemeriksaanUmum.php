<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 23/10/2018
 * Time: 12:27
 */


namespace App\Transaksi;

class PemeriksaanUmum extends Transaksi
{
    protected $table = "pemeriksaanumum_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}