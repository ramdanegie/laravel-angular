<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 21/01/2019
 * Time: 15.57
 */

namespace App\Transaksi;

class Yankes_TopTenDarah extends Transaksi
{
    protected $table = "yankes_toptendarah_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
