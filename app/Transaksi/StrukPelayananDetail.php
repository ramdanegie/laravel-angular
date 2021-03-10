<?php

namespace App\Transaksi;

class StrukPelayananDetail extends Transaksi
{
    protected $table ="strukpelayanandetail_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

//    public function __construct(){$this->setTransformerPath('App\Transformers\Transaksi\StrukPostingTransformer');}

    public function produk(){
        return $this->belongsTo('App\Master\Produk', 'objectprodukfk');
    }

    public function getPelayananSebelumReturnAttribute(){
        $spd = StrukPelayananDetail::where('norec',$this->norectriger)->first();
        return $spd;
    }

    public function ruangan(){
        return $this->belongsTo('App\Master\Ruangan','objectruanganfk');
    }

}
