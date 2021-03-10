<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 12/6/2017
 * Time: 10:46
 */

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
//use DB;

class ProdukFormulaProduksi extends MasterModel
{
    protected $table = 'produkformulaproduksi_m';
    protected $fillable = [];
    public $timestamps = false;


//    public function ruangan(){
//        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
//    }


}