<?php
/**
 * Created by IntelliJ IDEA.
 * User: UNKNOWS
 * Date: 4/12/2019
 * Time: 8:53 AM
 */
namespace App\Transaksi;

class RmPemeriksaanFisik extends Transaksi
{
    protected $table = "rmpemeriksaanfisik_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}