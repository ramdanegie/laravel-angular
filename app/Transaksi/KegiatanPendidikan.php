<?php
/**
 * Created by PhpStorm.
 * User: asepic
 * Date: 18/12/2018
 * Time: 20.41
 */

namespace App\Transaksi;

class KegiatanPendidikan extends Transaksi
{
    protected $table = 'sdm_kegiatanpendidikan_t';
    protected $fillable = [];
    public $timestamps = false;
    protected $primaryKey = "norec";

}
