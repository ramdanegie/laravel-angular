<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 11/2/2017
 * Time: 02:00
 */


namespace App\Transaksi;

class StrukRetur extends Transaksi
{
    protected $table ="strukretur_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}