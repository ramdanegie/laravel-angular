<?php
namespace App\Master;

class Produk extends MasterModel
{
    protected $table ="produk_m";
    protected $fillable = [];
    public $timestamps = false;
    // protected $primaryKey = 'id';
    // protected $guarded = array('id');
     public $incrementing = false;



    //Start Maping belongsTo
    public function bahan_produk(){
        return $this->belongsTo('App\Master\BahanProduk', 'objectbahanprodukfk');
    }

    public function account(){
        return $this->belongsTo('App\Master\ChartOfAccount', 'objectaccountfk');
    }

    public function bentuk_produk(){
        return $this->belongsTo('App\Master\BentukProduk', 'objectbentukprodukfk');
    }

    public function departemen(){
        return $this->belongsTo('App\Master\Departemen', 'objectdepartemenfk');
    }

    public function detail_golongan_produk(){
        return $this->belongsTo('App\Master\DetailGolonganProduk', 'objectdetailgolonganprodukfk');
    }

    public function detail_jenis_produk(){
        return $this->belongsTo('App\Master\DetailJenisProduk', 'objectdetailjenisprodukfk');
    }

    public function fungsi_produk(){
        return $this->belongsTo('App\Master\FungsiProduk', 'objectfungsiprodukfk');
    }

    // public function produk(){
    //     return $this->belongsTo('App\Master\Produk', 'objectprodukfk');
    // } self relation gitu ??

    public function golongan_produk(){
        return $this->belongsTo('App\Master\GolonganProduk', 'objectgolonganprodukfk');
    }

    // public function jenis_periksa(){
    //     return $this->belongsTo('App\Master\JenisPeriksa', 'objectjenisperiksafk');
    // }  //database kok gak ada tanggalnya, di cek 13 july

    public function kategory_produk(){
        return $this->belongsTo('App\Master\KategoryProduk', 'objectkategoryprodukfk');
    }

    public function level_produk(){
        return $this->belongsTo('App\Master\LevelProduk', 'objectlevelprodukfk');
    }

    public function produsen_produk(){
        return $this->belongsTo('App\Master\ProdusenProduk', 'objectprodusenprodukfk');
    }

    public function satuan_besar(){
        return $this->belongsTo('App\Master\SatuanBesar', 'objectsatuanbesarfk');
    }

    public function satuan_kecil(){
        return $this->belongsTo('App\Master\SatuanKecil', 'objectsatuankecilfk');
    }

    public function satuan_standar(){
        return $this->belongsTo('App\Master\SatuanStandar', 'objectsatuanstandarfk');
    }

    public function status_produk(){
        return $this->belongsTo('App\Master\StatusProduk', 'objectstatusprodukfk');
    }

    public function type_produk(){
        return $this->belongsTo('App\Master\TypeProduk', 'objecttypeprodukfk');
    }

    public function unit_laporan(){
        return $this->belongsTo('App\Master\UnitLaporan', 'objectunitlaporanfk');
    }

    public function warna_produk(){
        return $this->belongsTo('App\Master\WarnaProduk', 'objectwarnaprodukfk');
    }

    public function jenis_periksa_penunjang(){
        return $this->belongsTo('App\Master\JenisPeriksaPenunjang', 'objectjenisperiksapenunjangfk');
    }
    //End Maping belongsTo


    //belongsTo
    public function asset(){
        return $this->hasMany('App\Transaksi\Asset', 'objectprodukfk');
    }
//    public  function kegiatanunitcosth_m(){
//        return $this->belongsTo('App\Master\KegiatanUnitCostH', 'produkfk');
//    }
    



}
