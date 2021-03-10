<?php
namespace App\Transaksi;

class PelayananPasienTidakTerklaim extends Transaksi
{
    protected $table ="pelayananpasientidakterklaim_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";

    public function antrian_pasien_diperiksa(){
        return $this->belongsTo('App\Transaksi\AntrianPasienDiperiksa', 'noregistrasifk','norec');
    }

    public  function produk(){
        return $this->belongsTo('App\Master\Produk', 'produkfk');
    }

    public function struk_pelayanan(){
        return $this->belongsTo('App\Transaksi\StrukPelayanan', 'strukfk');
    }

    public function kelas(){
        return $this->belongsTo('App\Master\Kelas', 'kelasfk');
    }
}
