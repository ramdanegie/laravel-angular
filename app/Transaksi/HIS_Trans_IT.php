<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 11/8/2017
 * Time: 09:59
 */
//

namespace App\Transaksi;

class HIS_Trans_IT extends Transaksi
{
    protected $table ="his_trans_it_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;


}