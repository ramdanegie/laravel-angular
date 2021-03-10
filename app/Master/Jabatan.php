<?php
namespace App\Master;

class Jabatan extends MasterModel
{
    protected $table ="jabatan_m";
    protected $fillable = [];
    public $timestamps = false;
    

    //contoh belongsTo
    //public function {method}(){
    //    return $this->belongsTo({namespacemodel}, {fk});
    //}

    //public function {method}(){
        //return $this->hasMany({namespacemodel}, {fk});
    //}
}
