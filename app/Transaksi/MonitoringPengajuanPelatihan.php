<?php
namespace App\Transaksi;

class MonitoringPengajuanPelatihan extends Transaksi
{
    protected $table = "monitoringpengajuanpelatihan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}