<?php
/**
 * Created by PhpStorm.
 * User: as@epic01
 * Date: 4/26/2018
 * Time: 14:42
 */

namespace App\Transaksi;

class PostingSaldoAwal extends Transaksi
{
    protected $table ="postingsaldoawal_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}