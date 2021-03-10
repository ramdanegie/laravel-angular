<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/12/2018
 * Time: 09.44
 */


namespace App\Transaksi;

class PelatihanPaket extends Transaksi
{
    protected $table = "pelatihanpaket_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}


