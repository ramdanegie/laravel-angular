<?php
namespace App\Transaksi;

class AntrianPasienRegistrasi extends Transaksi
{
    protected $table ="antrianpasienregistrasi_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
