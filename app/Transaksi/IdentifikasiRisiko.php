<?php
/**
 * Created by PhpStorm.
 * User: Analis
 * Date: 20/06/2017
 * Time: 10:32
 */
namespace App\Transaksi;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class IdentifikasiRisiko extends Transaksi
{
    protected $table = 'identifikasirisiko_t';
    protected $fillable = [];
    public $timestamps = false;
}