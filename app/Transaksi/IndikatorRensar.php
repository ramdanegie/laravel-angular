<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 17/12/2018
 * Time: 17.34
 */
namespace App\Transaksi;
class IndikatorRensar extends Transaksi
{
    protected $table = "indikatorrensar_m";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}