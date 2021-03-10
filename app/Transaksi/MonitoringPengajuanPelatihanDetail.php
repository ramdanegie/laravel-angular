<?php
namespace App\Transaksi;

class MonitoringPengajuanPelatihanDetail extends Transaksi
{
    protected $table = "monitoringpengajuanpelatihandetail_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}