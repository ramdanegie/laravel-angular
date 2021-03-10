<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 07/02/2018
 * Time: 14:23
 */

namespace App\Transaksi;

class PotonganRemun extends Transaksi
{
    protected $table ="potonganremun_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";


}