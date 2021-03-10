<?php
namespace App\Transaksi;

class StrukPraOrder extends Transaksi
{
    protected $table = "strukpraorder_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}