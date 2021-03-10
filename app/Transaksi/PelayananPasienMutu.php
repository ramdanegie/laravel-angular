<?php
namespace App\Transaksi;

class PelayananPasienMutu extends Transaksi
{
    protected $table ="pelayananpasienmutu_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
