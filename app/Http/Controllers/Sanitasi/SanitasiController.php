<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 11/22/2019
 * Time: 10:42 AM
 */
namespace App\Http\Controllers\Sanitasi;

use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use Illuminate\Http\Request;
use App\Transaksi\StrukPlanning;
use DB;

class SanitasiController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
//    public function getComboSanitasi(Request $request)
//    {
//
//        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix'));
//        $kdDepartemenRawatInap = [];
//        foreach ($deptRanap as $itemRanap){
//            $kdDepartemenRawatInap []=  (int)$itemRanap;
//        }
//
//        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenRawatJalanFix'));
//        $kdDepartemenRawatJalan = [];
//        foreach ($deptJalan as $item){
//            $kdDepartemenRawatJalan []=  (int)$item;
//        }
//
//        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->where('dp.statusenabled', true)
//            ->orderBy('dp.namadepartemen')
//            ->get();
//        $dataRuangan = \DB::table('ruangan_m as ru')
//            ->where('ru.statusenabled', true)
//            ->orderBy('ru.namaruangan')
//            ->get();
//        foreach ($dataInstalasi as $item) {
//            $detail = [];
//            foreach ($dataRuangan as $item2) {
//                if ($item->id == $item2->objectdepartemenfk) {
//                    $detail[] = array(
//                        'id' => $item2->id,
//                        'ruangan' => $item2->namaruangan,
//                    );
//                }
//            }
//
//            $dataDepartemen[] = array(
//                'id' => $item->id,
//                'departemen' => $item->namadepartemen,
//                'ruangan' => $detail,
//            );
//        }
//        $ruangRJ = \DB::table('ruangan_m as ru')
//            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
//            ->whereIn('ru.id', $kdDepartemenRawatJalan)
//            ->where('ru.statusenabled', true)
//            ->orderBy('ru.namaruangan')
//            ->get();
//        $ruangRI = \DB::table('ruangan_m as ru')
//            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
//            ->whereIn('ru.id', $kdDepartemenRawatInap)
//            ->where('ru.statusenabled', true)
//            ->orderBy('ru.namaruangan')
//            ->get();
//        $dataKelas = \DB::table('kelas_m as kl')
//            ->select('kl.id', 'kl.namakelas')
//            ->where('kl.statusenabled', true)
//            ->orderBy('kl.namakelas')
//            ->get();
//
//        $dataKelasKamar = \DB::table('kelas_m as kl')
//            ->select('kl.id', 'kl.namakelas')
//            ->where('kl.statusenabled', true)
//            ->orderBy('kl.namakelas')
//            ->get();
//        $datadept = \DB::table('departemen_m as dp')
//            ->select('dp.id', 'dp.namadepartemen')
//            ->where('dp.statusenabled', true)
//            ->orderBy('dp.namadepartemen')
//            ->get();
//
//        $result = array(
//            'ruangan' => $dataRuangan,
//            'rawatinap' => $ruangRI,
//            'kelas' => $dataKelas,
//            'kelaskamar' => $dataKelasKamar,
//            'departemen' => $datadept,
//            'rawatjalan' => $ruangRJ,
//            'datadept' => $dataDepartemen,
//            'dataruangan' => $dataRuangan,
//            'message' => 'cepot',
//        );
//
//        return $this->respond($result);
//    }
    public function getTempatTidur(Request $request){
        $arrru = $request['arrru'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data= \DB::table('ruangansanitasi_m as ru')
            ->leftjoin('strukplanning_t as spl', 'ru.id', '=', 'spl.objectruanganfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'spl.objectpegawaipjawabfk')
            ->leftjoin('pegawai_m as pg2', 'pg2.id', '=', 'spl.objectpegawaipjawabevaluasifk')
            ->select('ru.id as idruangan','ru.namaruangan',
                'spl.tglplanning','spl.objectpegawaipjawabfk','spl.startdate','spl.duedate','spl.deskripsiplanning',
                'spl.objectpegawaipjawabevaluasifk','spl.keteranganverifikasi','spl.norec'
                ,'pg.namalengkap','pg2.namalengkap as pegawaipj'
            )
            ->where('ru.kdprofile', $kdProfile)
            ->whereIn('ru.objectdepartemenfk',array($arrru))
            ->where('ru.statusenabled',true);
//            ->where('spl.objectkelompoktransaksifk',121);

        if(isset($request['desc']) && $request['desc']!="" && $request['desc']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike','%'. $request['desc'] .'%');
        };
        if (isset($request['manage']) && $request['manage'] != "" && $request['manage'] != "undefined") {
//            $data = $data->where('spl.tglplanning','>', date('Y-m-d 00:00:00'));
        }else{
            $data = $data->whereBetween('spl.tglplanning', [ $request['tglAwal'],$request['tglAkhir'] ]);
        }
        $data = $data->get();


        return $this->respond($data);
    }
    public function SaveDataSignDate(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{
                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
//            $newCOA->signdate = $request['signdate'];
            $newCOA->objectruanganfk = $request['objectruanganfk'];
            $newCOA->deskripsiplanning = $request['deskripsiplanning'];
            //$newCOA->objectkelompoktransaksifk = 121;
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
    public function SaveDataAlokasiStaff(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->objectpegawaipjawabfk = $request['objectpegawaipjawabfk'];
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
    public function SaveDataWorkList(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->deskripsiplanning = $request['deskripsiplanning'];
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
    public function SaveDataInspeksi(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->keteranganverifikasi = $request['keteranganverifikasi'];
            $newCOA->objectpegawaipjawabevaluasifk = $request['objectpegawaipjawabevaluasifk'];
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
    public function SaveDataStartDate(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();

        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->startdate = date('Y-m-d H:i:s');
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
    public function SaveDataDueDate(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();

        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->duedate = date('Y-m-d H:i:s');
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

    public function getDataPegawaiGeneral(Request $request) {
        $req=$request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk=[];
        $dataProduk  = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap')
            ->where('kdprofile', $kdProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('st.namalengkap','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        foreach ($dataProduk as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        return $this->respond($dataPenulis2);
    }

    public function getDataCombo(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisPekerjaanSanitasi = explode(',', $this->settingDataFixed('KdJenisPekerjaanSanitasi', $kdProfile));
        $KdListJenisPekerjaanSanitasi = [];
        foreach ($KdJenisPekerjaanSanitasi as $data){
            $KdListJenisPekerjaanSanitasi [] = (int) $data;
        }
        $req=$request->all();
        $dataProduk  = \DB::table('jenispekerjaan_m')
            ->select('id as value','namaexternal as text','namaexternal')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->whereIn('id', $KdListJenisPekerjaanSanitasi)
            ->orderBy('id');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('namaexternal','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
//        $dataLogin = $request->all();
//        $dataJenisPek = \DB::table('jenispekerjaan_m as jp')
//            ->select('jp.id', 'jp.namaexternal')
//            ->whereIn('jp.id', [4,5,6,7])
//            ->where('st.statusenabled',true)
//            ->orderBy('jp.id')
//            ->get();
//
//        $result = array(
//            'namaexternal' => $dataJenisPek,
//            'message' => 'as@khris',
//        );
//
//        return $this->respond($result);
    }

    public function saveTambahKegiatan(Request $request) {
        DB::beginTransaction();
        $dataReq = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {

            if ($dataReq['norec'] == '') {
                $dataSave = new StrukPlanning();
                $dataSave->norec = $dataSave->generateNewId();
                $dataSave->kdprofile = $kdProfile;
                $dataSave->statusenabled = true;
                # code...
            }else{

                $dataSave= StrukPlanning::where('norec',$dataReq['norec'])->where('kdprofile', $kdProfile)->first();

            }
            $dataSave->tglplanning = $dataReq['tglplanning'];
            $dataSave->objectruanganasalfk = $dataReq['objectruanganasalfk'];
            $dataSave->objectjenispekerjaanfk = $dataReq['objectjenispekerjaanfk'];
            $dataSave->objectruanganfk = $dataReq['objectruanganfk'];
            $dataSave->objectpegawaipjawabfk = $dataReq['objectpegawaipjawabfk'];
            $dataSave->deskripsiplanning = $dataReq['deskripsiplanning'];


            $dataSave->save();


            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataSave,
                "as" => 'khris@epic',
            );
        }else{
            $transMessage =" Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'khris@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataSanitasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT stp.norec, rg.namaruangan, stp.tglplanning, pg.nama, stp.startdate, stp.duedate, stp.deskripsiplanning, sttp.namaexternal,stp.signdate, jpk.namaexternal as napek, stp.worklist, 
        stp.keteranganverifikasi, pg2.nama as namaevaluasi
            FROM strukplanning_t as stp
            INNER JOIN ruangan_m as rg on rg.id = stp.objectruanganfk
            LEFT JOIN pegawai_m as pg on pg.id = stp.objectpegawaipjawabfk
            LEFT JOIN pegawai_m as pg2 on pg2.id = stp.objectpegawaipjawabevaluasifk
            LEFT JOIN statuspekerjaan_m as sttp on sttp.id = stp.objectstatuspekerjaanfk
            LEFT JOIN jenispekerjaan_m as jpk on jpk.id = stp.objectjenispekerjaanfk
            where stp.kdprofile = $kdProfile
            ORDER BY stp.tglplanning DESC"));

        return $this->respond($data);
    }

    public function SaveSignDate(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
//        return $this->respond($request['signdate']);
        try {
//            if ($request['norec'] == ''){
                $newCOA =  StrukPlanning::where('norec',$request['norec'])
                    ->where('kdprofile', $kdProfile)
                    ->update([
                            'signdate' => $request['signdate']
                        ]

                    );
//            }
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
                "data" => $newCOA,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newCOA,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function SaveAlokasistaff(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
//        return $this->respond($request['signdate']);
        try {
//            if ($request['norec'] == ''){
            $newCOA =  StrukPlanning::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'objectpegawaipjawabfk' => $request['objectpegawaipjawabfk']
                    ]

                );
//            }
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
                "data" => $newCOA,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newCOA,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function SaveWorklist(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
//        return $this->respond($request['signdate']);
        try {
//            if ($request['norec'] == ''){
            $newCOA =  StrukPlanning::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'worklist' => $request['worklist']
                    ]

                );
//            }
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
                "data" => $newCOA,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newCOA,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function SaveStartDate(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
//        return $this->respond($request['signdate']);
        try {
//            if ($request['norec'] == ''){
            $newCOA =  StrukPlanning::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'startdate' => $request['startdate']
                    ]

                );
//            }
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
                "data" => $newCOA,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newCOA,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
      public function getJenisLayananSanitasi(Request $request){
         $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT *
            FROM jenispekerjaan_m
            WHERE jenispekerjaan='Sanitasi' AND namaexternal='Layanan Sanitasi' 
            and kdprofile='$kdProfile'"));

        return $this->respond($data);

    }
      public function SavePermohonanSanitasi(Request $request) {
         $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();

        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->first();
            }
            $newCOA->tglplanning = $request['tglplanning'];
            $newCOA->objectruanganfk = $request['ruangandesc'];
            $newCOA->objectkelompoktransaksifk = 121;
            $newCOA->rincianexecuteplanning_askep = $request['rincian'];
            $newCOA->narasumberfk = $request['pelapor'];
            $newCOA->objectjenispekerjaanfk = $request['jenisKerusakan'];
            $newCOA->save();

            $norecHead2 = $newCOA->norec;

//            $sid    = "ACc6ac08ce1cc9096e0fd57a0c38801118";
//            $token  = "cada716b5a36721fd86832662bbd06fe";
//            $twilio = new Client($sid, $token);
//            $array =[
//                "whatsapp:+6282110191673",
//                "whatsapp:+6282211333013",
//                "whatsapp:+6285702501576",
//                "whatsapp:+6283838339887",
//                "whatsapp:+6281649111417",
//                "whatsapp:+628563637731",
//                "whatsapp:+6285746169422",
//                "whatsapp:+628563637731",
//                "whatsapp:+6281346568936",
//                "whatsapp:+6287758626737",
//                "whatsapp:+6285641367387",
//                "whatsapp:+6285727301273"
//            ];
//            $i = 0;
//            foreach ($array as $arr){
//                $message = $twilio->messages
//                    ->create($array[$i], // to
//                        array(
//                            "from" => "whatsapp:+14155238886",
//                            "body" => "Laporan Baru Sanitasi - ".$request['rincian']
//                        )
//                    );
//                $i = $i+1;
//            }

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
    public function getDaftarPermohonanSanitasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();

        $tgl="";
        $jenisKerusakan="";
        $idRuangan="";

        if ((isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") && (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined"))
        {
            $tgl = "AND stp.tglplanning BETWEEN '".$request['tglAwal']."' AND '".$request['tglAkhir']."' ";
        }

        if (isset($request['jenisKerusakan']) && $request['jenisKerusakan'] != "" && $request['jenisKerusakan'] != "undefined")
        {
            $jenisKerusakan = "AND stp.objectjenispekerjaanfk = '".$request['jenisKerusakan']."' ";
        }

        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined")
        {
            $idRuangan = "AND stp.objectruanganfk = '".$request['idRuangan']."' ";
        }


        $data = DB::select(DB::raw("
        SELECT stp.norec, rg.namaruangan, stp.tglplanning, pg.nama, stp.startdate, stp.duedate, stp.rincianexecuteplanning_askep, stp.pelapor, stp.deskripsiplanning, stp.keteranganverifikasi, sttp.namaexternal,stp.signdate, jpk.reportdisplay, stp.worklist, stp.keteranganverifikasi, pg2.nama as namainspektor, pg3.nama as namapelapor,
                sttp.reportdisplay as statuspekerjaan
            FROM strukplanning_t as stp
            INNER JOIN ruangan_m as rg on rg.id = stp.objectruanganfk
            LEFT JOIN pegawai_m as pg on pg.id = stp.objectpegawaipjawabfk
            LEFT JOIN pegawai_m as pg2 on pg2.id = stp.objectpegawaipjawabevaluasifk
            LEFT JOIN pegawai_m as pg3 on pg3.id = stp.narasumberfk 
            LEFT JOIN statuspekerjaan_m as sttp on sttp.id = stp.objectstatuspekerjaanfk
                        LEFT JOIN jenispekerjaan_m as jpk on jpk.id = stp.objectjenispekerjaanfk
            WHERE stp.objectkelompoktransaksifk=121
            AND stp.statusenabled=true
            and stp.kdprofile='$kdProfile'
            $tgl
            $jenisKerusakan
            $idRuangan
            ORDER BY stp.tglplanning DESC"));

        return $this->respond($data);

    }

    public function HapusPermohonanSanitasi(Request $request){
         $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            StrukPlanning::where('norec', $request['norec'])->update(
                ['statusenabled' => false]
            );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'dy@epic',
            );
        } else {
            $transMessage = " Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'dy@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }


    public function SaveDataJenisLayananSanitasi(Request $request) {
         $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();

        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = 1;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->first();
            }
            $newCOA->objectjenispekerjaanfk = $request['jenisKerusakan'];
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
                "dy" => 'dy@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $newPJT,
                "dy" => 'dy@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function getStatusPekerjaanSanitasi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT *
            FROM statuspekerjaan_m
            WHERE kodeexternal = 'Sanitasi'
            and kdprofile ='$kdProfile'"));

        return $this->respond($data);

    }

}