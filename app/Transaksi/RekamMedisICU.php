<?php
/**
 * Created by PhpStorm.
 * User: PROGRAMMER_21
 * Date: 10/07/2018
 * Time: 13:49
 */
namespace App\Transaksi;

class RekamMedisICU extends Transaksi
{
    protected $table = "rekammedisicu_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}