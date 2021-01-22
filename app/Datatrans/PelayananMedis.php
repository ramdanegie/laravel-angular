<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 3/11/2020
 * Time: 12:52 PM
 */

namespace App\Datatrans;

class PelayananMedis extends Datatrans
{
    protected $table = "pelayananmedis_t";
    protected $fillable = [];
    public $timestamps = false;
//    public $incrementing = false;
    protected $primaryKey = "norec";
    protected $keyType = "string";
}