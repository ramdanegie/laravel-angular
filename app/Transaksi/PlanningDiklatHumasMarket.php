<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/12/2018
 * Time: 11.38
 */
namespace App\Transaksi;

class PlanningDiklatHumasMarket extends Transaksi
{
    protected $table = "planningdiklathumasmarket_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
