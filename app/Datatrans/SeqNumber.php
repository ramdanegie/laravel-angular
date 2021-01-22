<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 05/12/2018
 * Time: 16.15
 */


namespace App\Datatrans;

class SeqNumber extends Datatrans
{
    protected $table ="seqnumber_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;



}
