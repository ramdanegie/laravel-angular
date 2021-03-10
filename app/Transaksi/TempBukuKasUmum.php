<?php
namespace App\Transaksi;

class TempBukuKasUmum extends Transaksi
{
    protected $table ="tempbukukasumum_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
