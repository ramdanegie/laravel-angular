<?php
/**
 * Created by PhpStorm.
 * User: Giw
 * Date: 12/12/2017
 * Time: 10:31 AM
 */
namespace App\Transaksi;

use App\Master\SettingDataFixed;
class SuratKeterangan extends Transaksi
{
    protected $table ="suratketerangan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

}
