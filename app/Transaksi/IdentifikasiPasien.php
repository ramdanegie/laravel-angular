<?php
namespace App\Transaksi;

class IdentifikasiPasien extends Transaksi
{
    protected $table ="identifikasipasien_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
