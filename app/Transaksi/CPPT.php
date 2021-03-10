<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 25/10/2018
 * Time: 10:40
 */

namespace App\Transaksi;

class CPPT extends Transaksi
{
    protected $table = "cppt_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}