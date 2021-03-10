<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 23/10/2018
 * Time: 12:24
 */

namespace App\Transaksi;


class IndikatorPemberianMakan extends Transaksi
{
    protected $table = "indikator_pemberianmakan_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}