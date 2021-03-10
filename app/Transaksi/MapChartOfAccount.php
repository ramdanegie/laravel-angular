<?php
namespace App\Transaksi;

class MapChartOfAccount extends Transaksi
{
    protected $table = "mapchartofaccount_t";
    protected $fillable = [];
    public $incrementing = false;
    protected $primaryKey = "norec";
}