<?php
namespace App\Transaksi;

class StrukOrderBatalVerif extends Transaksi
{
    protected $table = "strukorderbatal_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}