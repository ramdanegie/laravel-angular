<?php
/**
 * Created by PhpStorm.
 * User: PROGRAMMER_21
 * Date: 10/07/2018
 * Time: 13:49
 */
namespace App\Transaksi;

class RekamMedisICUDetail extends Transaksi
{
    protected $table = "rekammedisicud_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}