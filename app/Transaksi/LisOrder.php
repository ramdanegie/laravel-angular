<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 09/05/2018
 * Time: 09.42
 */

namespace App\Transaksi;

class LisOrder extends Transaksi
{
    protected $table ="lisorder";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";


}
