<?php

namespace App\Transaksi;

class StrukPosting extends Transaksi
{
    protected $table ="strukposting_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\StrukPostingTransformer');}
    public function login_user(){
        return $this->belongsTo(\App\Master\LoginUser::class, 'kdhistorylogins');
    }
}
