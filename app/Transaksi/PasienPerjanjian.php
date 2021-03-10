<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 23/10/2018
 * Time: 12:41
 */


namespace App\Transaksi;

class PasienPerjanjian extends Transaksi
{
    protected $table = "pasienperjanjian_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}