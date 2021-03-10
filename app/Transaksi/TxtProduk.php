<?php
namespace App\Transaksi;

class TxtProduk extends Transaksi
{
    protected $table ="txtproduk_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";


}
