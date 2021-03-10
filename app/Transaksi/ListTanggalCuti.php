<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/10/2019
 * Time: 10:31 PM
 */
namespace App\Transaksi;

class ListTanggalCuti extends Transaksi
{
    protected $table = "listtanggalcuti_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
