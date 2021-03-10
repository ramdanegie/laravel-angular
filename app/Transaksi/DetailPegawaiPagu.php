<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 10/04/2019
 * Time: 11:13
 */


namespace App\Transaksi;

class DetailPegawaiPagu extends Transaksi
{
    protected $table = 'detailpegawaipagu_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}