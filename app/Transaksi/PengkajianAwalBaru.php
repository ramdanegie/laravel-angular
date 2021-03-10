<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 25/10/2018
 * Time: 20:30
 */


namespace App\Transaksi;

class PengkajianAwalBaru extends Transaksi
{
    protected $table = "pengkajianawalbaru_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}