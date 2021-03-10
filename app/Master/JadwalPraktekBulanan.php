<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 27/03/2019
 * Time: 15:36
 */


namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class JadwalPraktekBulanan extends MasterModel
{
    protected $table = 'jadwalpraktekbulanan_m';
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';


}


