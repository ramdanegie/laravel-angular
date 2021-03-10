<?php
namespace App\Transaksi;

class TempRiwayatPersediaan extends Transaksi
{
    protected $table = "tempriwayatpersediaan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}