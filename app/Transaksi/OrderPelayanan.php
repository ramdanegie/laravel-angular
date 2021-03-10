<?php
namespace App\Transaksi;

class OrderPelayanan extends Transaksi
{
    protected $table = "orderpelayanan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}