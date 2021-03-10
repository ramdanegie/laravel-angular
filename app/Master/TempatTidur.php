<?php
/**
 * Created by PhpStorm.
 * User: Analis
 * Date: 20/06/2017
 * Time: 10:32
 */
namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class TempatTidur extends MasterModel
{
    protected $table = 'tempattidur_m';
    protected $fillable = [];
    public $timestamps = false;


    public function kamar(){
        return $this->belongsTo('App\Master\Kamar', 'objectkamarfk');
    }
    public function status_bed(){
        return $this->belongsTo('App\Master\StatusBed', 'objectstatusbedfk');
    }


}