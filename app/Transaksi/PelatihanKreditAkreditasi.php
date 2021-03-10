<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/12/2018
 * Time: 10.04
 */
namespace App\Transaksi;

class PelatihanKreditAkreditasi extends Transaksi
{
    protected $table = "pelatihankreditakreditasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}

