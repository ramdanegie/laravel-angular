<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/12/2018
 * Time: 11.42
 */

namespace App\Transaksi;

class PlanningDiklatHumasMarketBiaya extends Transaksi
{
    protected $table = "planningdiklathumasmarketbiaya_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";
}
