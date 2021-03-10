<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 11/2/2017
 * Time: 02:51
 */

namespace App\Transaksi;

class PelayananPasienRetur extends Transaksi
{
    protected $table ="pelayananpasienretur_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}