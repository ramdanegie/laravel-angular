<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 17/12/2018
 * Time: 17.35
 */
namespace App\Transaksi;

class IndikatorRensarDetail extends Transaksi
{
    protected $table = "indikatorrensardetail_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}