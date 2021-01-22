<?php
namespace App\Traits;

use App\Transaksi\PostingJurnalTransaksi;

Trait JurnalTrait{

    public function getJurnal($request){
        $posting = new PostingJurnalTransaksi();

        if(isset($request['is_verified'])){
            if($request['is_verified']=='true'){
                $posting = $posting->whereNotNull('noverifikasi');
            }elseif($request['is_verified']=='false'){
                $posting = $posting->whereNull('noverifikasi');
            }
        }

        if(isset($request['tglAwal'])){
            $posting = $posting->where('tglbuktitransaksi', '>=', $request['tglAwal'].' 00:00:00');
        }

        if(!empty($request['noReferensi'])){
            $posting = $posting->where('nobuktitransaksi', 'like', '%'.$request['noReferensi'].'%');
        }

        if(isset($request['tglAkhir'])){
            $posting = $posting->where('tglbuktitransaksi', '<=', $request['tglAkhir'].' 23:59:59');
        }
        //semua yang belum  contohnya order by.
        return $posting->orderBy('nojurnal', 'desc')->get();
    }

    //dari tindakanpelayanan
    public function getAccountByProduk() {

    }

}
