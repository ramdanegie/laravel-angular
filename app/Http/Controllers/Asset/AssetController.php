<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 12/9/2019
 * Time: 7:50 PM
 */

//AssetController
namespace App\Http\Controllers\Asset;

use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use Illuminate\Http\Request;
use App\Transaksi\StrukPlanning;
use DB;

class AssetController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function SaveDataJadwalAssetKalibrasi(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();

        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $idProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $idProfile)->first();
            }
            $newCOA->tglplanning = $request['tglplanning'];
            $newCOA->keteranganlainnya = $request['keteranganlainnya'];
            $newCOA->noregisterassetfk = $request['noregisterassetfk'];
            $newCOA->objectpegawaipjawabfk = $request['objectpegawaipjawabfk'];
            $newCOA->objectkelompoktransaksifk = 123;
            $newCOA->save();

            $norecHead2 = $newCOA->norec;

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "";


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil" ;
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getDaftarKalibrasi(Request $request){
        $arrru = $request['arrru'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data= \DB::table('strukplanning_t as spl')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'spl.objectpegawaipjawabfk')
            ->select('spl.tglplanning','spl.objectpegawaipjawabfk','spl.keteranganlainnya','spl.norec'
                ,'pg.namalengkap','spl.startdate','spl.duedate'
            )
            ->where('spl.kdprofile', $idProfile)
            ->where('spl.objectkelompoktransaksifk',123);

        if(isset($request['norecAsset']) && $request['norecAsset']!="" && $request['norecAsset']!="undefined"){
            $data = $data->where('spl.noregisterassetfk','ilike','%'. $request['norecAsset'].'%' );
        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
////            $data = $data->where('spl.tglplanning','>', date('Y-m-d 00:00:00'));
//        }else{
            $data = $data->whereBetween('spl.tglplanning', [ $request['tglAwal'],$request['tglAkhir'] ]);
        }
        $data = $data->get();
        if (count($data)==0 ){
            $data = [];
        }
        return $this->respond($data);
    }

    public function getDaftarPemeliharaan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $arrru = $request['arrru'];
        $data= \DB::table('strukplanning_t as spl')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'spl.objectpegawaipjawabfk')
            ->select('spl.tglplanning','spl.objectpegawaipjawabfk','spl.keteranganlainnya','spl.norec'
                ,'pg.namalengkap','spl.startdate','spl.duedate','spl.keteranganverifikasi'
            )
            ->where('spl.kdprofile', $idProfile)
            ->where('spl.objectkelompoktransaksifk',124);

        if(isset($request['norecAsset']) && $request['norecAsset']!="" && $request['norecAsset']!="undefined"){
            $data = $data->where('spl.noregisterassetfk','=',$request['norecAsset']);
        };
        if(isset($request['jenis']) && $request['jenis']!="" && $request['jenis']!="undefined"){
            $data = $data->whereNotNull('spl.duedate');
        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
////            $data = $data->where('spl.tglplanning','>', date('Y-m-d 00:00:00'));
//        }else{
            $data = $data->whereBetween('spl.tglplanning', [ $request['tglAwal'],$request['tglAkhir'] ]);
        }
        $data = $data->get();
        if (count($data)==0 ){
            $data = [];
        }


        return $this->respond($data);
    }
    public function SaveDataJadwalAssetPemeliharaan(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $idProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->first();
            }
            $newCOA->tglplanning = $request['tglplanning'];
            $newCOA->keteranganlainnya = $request['keteranganlainnya'];
            $newCOA->noregisterassetfk = $request['noregisterassetfk'];
            $newCOA->objectpegawaipjawabfk = $request['objectpegawaipjawabfk'];
            $newCOA->objectkelompoktransaksifk = 124;
            $newCOA->save();

            $norecHead2 = $newCOA->norec;

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "";


        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil" ;
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getJadwalKalibrasi(Request $request){
        $arrru = $request['arrru'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdKelTransKalibrasi = (int) $this->settingDataFixed('KdTransKalibrasi', $kdProfile);
        $data= \DB::table('strukplanning_t as spl')
            ->leftjoin('registrasiaset_t as ra', 'ra.norec', '=', 'spl.noregisterassetfk')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'ra.objectprodukfk')
            ->select('spl.tglplanning','spl.objectpegawaipjawabfk','spl.keteranganlainnya','spl.norec','pr.namaproduk',
                DB::raw(" to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 00:00'   AS start,
                to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 23:59'  AS ends,
                to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 00:00' AS startepoch,
                to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 23:59'  AS endpoch"))
            ->where('spl.objectkelompoktransaksifk',$KdKelTransKalibrasi)
            ->where('spl.kdprofile', $kdProfile);

//        if(isset($request['norecAsset']) && $request['norecAsset']!="" && $request['norecAsset']!="undefined"){
//            $data = $data->where('spl.noregisterassetfk','ilike','%'. $request['norecAsset'].'%' );
//        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
////            $data = $data->where('spl.tglplanning','>', date('Y-m-d 00:00:00'));
//        }else{
            $data = $data->whereBetween('spl.tglplanning', [ $request['tglAwal'],$request['tglAkhir'] ]);
        }
        $data = $data->get();
        if (count($data)==0 ){
            $data = [];
        }


        return $this->respond($data);
    }
    public function getJadwalPemeliharaan(Request $request){
        $arrru = $request['arrru'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data= \DB::table('strukplanning_t as spl')
            ->leftjoin('registrasiaset_t as ra', 'ra.norec', '=', 'spl.noregisterassetfk')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'ra.objectprodukfk')
            ->select('spl.tglplanning','spl.objectpegawaipjawabfk','spl.keteranganlainnya','spl.norec'
                ,'pr.namaproduk',
                DB::raw(" to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 00:00'   AS start,
                to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 23:59'  AS ends,
                to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 00:00' AS startepoch,
                to_char(spl.tglplanning, 'YYYY/MM/DD') || ' 23:59'  AS endpoch")
            )
            ->where('spl.kdprofile', $idProfile)
            ->where('spl.objectkelompoktransaksifk',124);

//        if(isset($request['norecAsset']) && $request['norecAsset']!="" && $request['norecAsset']!="undefined"){
//            $data = $data->where('spl.noregisterassetfk','ilike','%'. $request['norecAsset'].'%' );
//        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
////            $data = $data->where('spl.tglplanning','>', date('Y-m-d 00:00:00'));
//        }else{
            $data = $data->whereBetween('spl.tglplanning', [ $request['tglAwal'],$request['tglAkhir'] ]);
        }
        $data = $data->get();
        if (count($data)==0 ){
            $data = [];
        }


        return $this->respond($data);
    }
    public function getDataProduk(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->JOIN('registrasiaset_t as ra', 'pr.id', '=', 'ra.objectprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->select('ra.norec as id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.kdproduk')
            ->where('pr.statusenabled', true)
            ->where('pr.kdprofile', $idProfile)
//            ->where('jp.id',97)
            ->orderBy('pr.namaproduk');
//            ->take($req['take'])
//            ->get();
        if(isset($req['namaproduk']) &&
            $req['namaproduk']!="" &&
            $req['namaproduk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $req['namaproduk'] .'%' );
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(20);
        $dataProduk = $dataProduk->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.statusenabled',true)
            ->where('ks.kdprofile', $idProfile)
            ->get();


        $dataProdukResult=[];
        foreach ($dataProduk as $item){
            $satuanKonversi=[];
            foreach ($dataKonversiProduk  as $item2){
                if ($item->id == $item2->objekprodukfk){
                    $satuanKonversi[] =array(
                        'ssid' =>   $item2->satuanstandar_tujuan,
                        'satuanstandar' =>   $item2->satuanstandar2,
                        'nilaikonversi' =>   $item2->nilaikonversi,
                    );
                }
            }

            $dataProdukResult[]=array(
                'id' =>   $item->id,
                'namaproduk' =>   $item->namaproduk,
                'ssid' =>   $item->ssid,
                'satuanstandar' =>   $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
                'kdproduk' => $item->kdproduk,
            );
        }

        return $this->respond($dataProdukResult);
    }
}