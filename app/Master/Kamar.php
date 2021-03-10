<?php
/**
 * Created by PhpStorm.
 * User: Analis
 * Date: 20/06/2017
 * Time: 10:30
 */
namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class Kamar extends MasterModel
{
    protected $table = 'kamar_m';
    protected $fillable = [];
    public $timestamps = false;


    public function ruangan(){
        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
    }


}