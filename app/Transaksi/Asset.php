<?php
namespace App\Transaksi;

class Asset extends Transaksi
{
    protected $table ="asset_t";
    protected $primaryKey = 'norec';
    protected $fillable = [];
    public $timestamps = false;
    

    public function ruangan(){
        return $this->belongsTo('App\Master\Ruangan', 'objectruanganfk');
    }

    public function produk(){
        return $this->belongsTo('App\Master\Produk', 'objectruanganfk');
    }
    
    //contoh belongsTo
    //public function {method}(){
    //    return $this->belongsTo({namespacemodel}, {fk});
    //}

    //public function {method}(){
        //return $this->hasMany({namespacemodel}, {fk});
    //}
}
