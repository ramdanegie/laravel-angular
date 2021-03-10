<?php
namespace App\Transaksi;

class LoggingUser extends Transaksi
{
    protected $table ="logginguser_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "id";


    public function pelayanan_pasien(){
        return $this->belongsTo('App\Master\LoginUser',  'id','objectloginuserfk');
    }
}
