<?php
namespace App\Transaksi;

class PengambilanJenazah extends Transaksi
{
    protected $table = "pengambilanjenazah_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    
}