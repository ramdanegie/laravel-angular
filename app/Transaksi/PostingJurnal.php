<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 07/02/2018
 * Time: 14:23
 */

namespace App\Transaksi;

class PostingJurnal extends Transaksi
{
    protected $table ="postingjurnal_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}