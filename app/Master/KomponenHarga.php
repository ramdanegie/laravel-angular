<?php
/**
 * Created by PhpStorm.
 * User: Analis
 * Date: 20/06/2017
 * Time: 21:54
 */
namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class KomponenHarga extends MasterModel
{
    protected $table = 'komponenharga_m';
    protected $fillable = [];
    public $timestamps = false;


//    public function ruangan(){
//        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
//    }


}