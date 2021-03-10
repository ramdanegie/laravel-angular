<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/17/2019
 * Time: 3:42 PM
 */
namespace App\Transaksi;

class OrderLab extends Transaksi
{
    protected $table ="order_lab";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
//    protected $primaryKey = "id";


}
