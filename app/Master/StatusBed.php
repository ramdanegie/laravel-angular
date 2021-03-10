<?php
/**
 * Created by PhpStorm.
 * User: Analis
 * Date: 20/06/2017
 * Time: 11:47
 */
namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class StatusBed extends MasterModel
{
    protected $table = 'statusbed_m';
    protected $fillable = [];
    public $timestamps = false;


//    public function ruangan(){
//        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
//    }


}