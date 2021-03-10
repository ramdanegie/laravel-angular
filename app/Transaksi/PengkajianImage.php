<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 25/10/2018
 * Time: 20:30
 */


namespace App\Transaksi;

class PengkajianImage extends Transaksi
{
    protected $table = "pengkajianimage_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}