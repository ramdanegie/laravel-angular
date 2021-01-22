<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 12:54 PM
 */

namespace App\Datatrans;

class TransaksiMedis extends Datatrans
{
    protected $table = "transaksimedis_t";
    protected $fillable = [];
    public $timestamps = false;
//    public $incrementing = false;
    protected $primaryKey = "norec";
    protected $keyType = "string";
}