<?php
namespace App\Transaksi;

class StrukPraOrderDetail extends Transaksi
{
    protected $table = "strukpraorderdetail_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}