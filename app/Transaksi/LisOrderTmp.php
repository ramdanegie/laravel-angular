<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 09/05/2018
 * Time: 09.43
 */

namespace App\Transaksi;

class LisOrderTmp extends Transaksi
{
    protected $table ="lisordertmp";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";



}
