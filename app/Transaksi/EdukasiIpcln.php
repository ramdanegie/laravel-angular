<?php
/**
 * Created by PhpStorm.
 * User: GIW
 * Date: 9/30/2019
 * Time: 10:20 AM
 */
namespace App\Transaksi;

class EdukasiIpcln extends Transaksi
{
    protected $table = 'edukasiipcln_t';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


}


