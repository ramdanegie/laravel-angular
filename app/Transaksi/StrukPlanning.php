<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/12/2018
 * Time: 11.36
 */
namespace App\Transaksi;

class StrukPlanning extends Transaksi
{
    protected $table = "strukplanning_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}
