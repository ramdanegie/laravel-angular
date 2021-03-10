<?php
namespace App\Master;

class Pegawai extends MasterModel
{
    protected $table ="pegawai_m";
    protected $fillable = [];
    public $timestamps = false;

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\PegawaiTransformer');}
    //contoh belongsTo
    public function login_user(){
        return $this->hasMany(LoginUser::class, 'objectpegawaifk');
    }

//    public function login_user(){
//        return $this->log_user()->first();
//    }



    public function planning_pelayanan(){
        return $this->hasMany('App\Transaksi\PlanningPelayanan', 'objectpegawaifk');
    }
}
