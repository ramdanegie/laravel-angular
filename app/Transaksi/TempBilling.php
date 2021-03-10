<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 12/20/2017
 * Time: 10:57
 */

namespace App\Transaksi;

class TempBilling extends Transaksi
{
    protected $table ="temp_billing_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}