<?php
/**
 * Created by PhpStorm.
 * User: Agus Sustian
 * Date: 9/27/2019
 * Time: 3:41 PM
 */
namespace App\Transaksi;

class EECG extends Transaksi
{
    protected $table = "eecg_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}