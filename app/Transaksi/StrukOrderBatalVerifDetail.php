<?php
namespace App\Transaksi;

class StrukOrderBatalVerifDetail extends Transaksi
{
    protected $table = "strukorderdetailbatal_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}