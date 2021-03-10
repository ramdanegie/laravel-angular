<?php
/**
 * Created by PhpStorm.
 * User: Ramdan
 * Date: 05/08/2020
 * Time: 16:36
 */
namespace App\Transaksi;

class HasilLaboratorium extends Transaksi
{
    protected $table = "hasillaboratorium_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}