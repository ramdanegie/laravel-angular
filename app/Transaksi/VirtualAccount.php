<?php
namespace App\Transaksi;

class VirtualAccount extends Transaksi
{
    protected $table = "virtualaccount_t";
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "trx_id";
    protected $depositId = null;
}