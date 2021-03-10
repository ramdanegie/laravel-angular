<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 21/01/2019
 * Time: 14.31
 */
namespace App\Transaksi;
class Yankes_PemenuhanDarah extends Transaksi
{
    protected $table = "yankes_pemenuhandarah_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}