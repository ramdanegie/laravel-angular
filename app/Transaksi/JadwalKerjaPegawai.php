<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 19/01/2020
 * Time: 8:48 PM
 */

namespace App\Transaksi;

class JadwalKerjaPegawai extends Transaksi
{
    protected $table ="jadwalkerjapegawai_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}