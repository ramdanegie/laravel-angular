<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 19/11/2018
 * Time: 13.05
 */

namespace App\Transaksi;

class PersediaanSaldoAwal extends Transaksi
{
    protected $table ="persediaansaldoawal_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
}