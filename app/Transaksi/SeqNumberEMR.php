<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 05/12/2018
 * Time: 16.15
 */


namespace App\Transaksi;

class SeqNumberEMR extends Transaksi
{
    protected $table ="seqnumberemr_t";
    protected $primaryKey = 'seqnumber';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;



}
