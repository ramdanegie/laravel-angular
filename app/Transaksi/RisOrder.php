<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 09/05/2018
 * Time: 09.42
 */

namespace App\Transaksi;

class RisOrder extends Transaksi
{
    protected $table ="ris_order";
    protected $primaryKey = 'order_key';
    protected $fillable = [];
    public $timestamps = false;

}
