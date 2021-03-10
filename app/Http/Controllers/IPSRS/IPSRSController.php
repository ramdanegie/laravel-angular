<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 12/2/2019
 * Time: 9:52 PM
 */

namespace App\Http\Controllers\IPSRS;

use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use App\Transaksi\StrukPlanningDetail;
use Illuminate\Http\Request;
use App\Transaksi\StrukPlanning;
use DB;

class IPSRSController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDaftarIPSRS(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdKelTransIPSRS = (int) $this->settingDataFixed('KdKelTransIPSRS', $kdProfile);
        $dataLogin = $request->all();
        $tgl="";
        $jenisalat="";
        $idRuangan="";
        $ruangantj="";

        if ((isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") && (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined"))
        {
            $tgl = "AND stp.tglplanning BETWEEN '".$request['tglAwal']."' AND '".$request['tglAkhir']."' ";
        }

        if (isset($request['jenisalat']) && $request['jenisalat'] != "" && $request['jenisalat'] != "undefined")
        {
            $jenisalat = "AND stp.objectjenisalatfk = '".$request['jenisalat']."' ";
        }

        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined")
        {
                $idRuangan = "AND stp.objectruangantujuanfk = '".$request['idRuangan']."'";
        }

        if (isset($request['ruangantj']) && $request['ruangantj'] != "" && $request['ruangantj'] != "undefined")
        {
                $ruangantj = "AND stp.objectruangantujuanfk = '".$request['ruangantj']."'";
        }

        $data = DB::select(DB::raw("
            SELECT  stp.norec, rg.namaruangan, stp.tglplanning,stp.objectpegawaipjawabfk,pg.namalengkap, stp.startdate, stp.duedate, stp.rincianexecuteplanning_askep, stp.pelapor, 
                    stp.deskripsiplanning, stp.keteranganverifikasi, sttp.namaexternal,stp.signdate, stp.worklist, stp.keteranganverifikasi, 
                    pg2.nama as namainspektor, pg3.nama as namapelapor,stp.objectstatuspekerjaanfk,sttp.reportdisplay as statuspekerjaan,stp.objectjenispekerjaanfk AS idjeniskerusakan,
                    jpk.reportdisplay as jeniskerusakan,stp.objectjenisalatfk,jpk2.reportdisplay as jenisalat,r.namaruangan as namaruangantujuan
            FROM strukplanning_t as stp
            INNER JOIN ruangan_m as rg on rg.id = stp.objectruanganfk
            LEFT JOIN pegawai_m as pg on pg.id = stp.objectpegawaipjawabfk
            LEFT JOIN pegawai_m as pg2 on pg2.id = stp.objectpegawaipjawabevaluasifk
            LEFT JOIN pegawai_m as pg3 on pg3.id = stp.narasumberfk 
            LEFT JOIN statuspekerjaan_m as sttp on sttp.id = stp.objectstatuspekerjaanfk
			LEFT JOIN jenispekerjaan_m as jpk on jpk.id = stp.objectjenispekerjaanfk
            LEFT JOIN jenispekerjaan_m as jpk2 on jpk2.id = stp.objectjenisalatfk
            LEFT JOIN ruangan_m as r on r.id = stp.objectruangantujuanfk
			WHERE stp.kdprofile = $kdProfile 
			AND stp.objectkelompoktransaksifk = $KdKelTransIPSRS
			AND stp.statusenabled = true 
			$tgl
			$jenisalat
            $idRuangan
            $ruangantj
            ORDER BY stp.tglplanning DESC"));

        foreach ($data as $item) {
            $details = \DB::select(DB::raw("select spd.pegawaifk as id,pr.namalengkap
                    from strukplanningdetail_t as spd 
                    left JOIN pegawai_m as pr on pr.id = spd.pegawaifk                    
                    where spd.kdprofile = $kdProfile and noplanningfk = :norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'norec' => $item->norec,
                'namaruangan' => $item->namaruangan,
                'tglplanning' => $item->tglplanning,
                'objectpegawaipjawabfk' => $item->objectpegawaipjawabfk,
                'namalengkap' => $item->namalengkap,
                'startdate' => $item->startdate,
                'duedate' => $item->duedate,
                'rincianexecuteplanning_askep' => $item->rincianexecuteplanning_askep,
                'pelapor' => $item->pelapor,
                'deskripsiplanning' => $item->deskripsiplanning,
                'keteranganverifikasi' => $item->keteranganverifikasi,
                'signdate' => $item->signdate,
                'worklist' => $item->worklist,
                'namainspektor' => $item->namainspektor,
                'namapelapor' => $item->namapelapor,
                'objectstatuspekerjaanfk' => $item->objectstatuspekerjaanfk,
                'statuspekerjaan' => $item->statuspekerjaan,
                'idjeniskerusakan' => $item->idjeniskerusakan,
                'jeniskerusakan' => $item->jeniskerusakan,
                'jenisalat' => $item->jenisalat,
                'objectjenisalatfk' => $item->objectjenisalatfk,
                'details' => $details,
                'namaruangantujuan' => $item->namaruangantujuan
            );
        }
        if (count($data) == 0) {
            $result = [];
        }
        $result = array(
            'data' => $result,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
//        return $this->respond($data);
    }

    public function SavePermohonan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
        $KdKelTransIPSRS = (int) $this->settingDataFixed('KdKelTransIPSRS', $kdProfile);
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
            $newCOA->objectkelompoktransaksifk = $KdKelTransIPSRS;
            $newCOA->rincianexecuteplanning_askep = $request['rincian'];
            $newCOA->pelapor = $request['pelapor'];
            $newCOA->narasumberfk = $request['idpelapor'];
            $newCOA->objectruangantujuanfk = $request['ruangantujuan'];
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

    public function SaveDataIdentifikasi(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = true;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->keteranganverifikasi = $request['identifikasi'];
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

    public function SaveDataJenisKerusakan(Request $request) {
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

    public function getJenisPekerjaan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT * FROM jenispekerjaan_m WHERE kdprofile = $kdProfile and statusenabled = true and jenispekerjaan='IPSRS' AND namaexternal='Status Jenis'"));
        return $this->respond($data);
    }

    public function getStatusPekerjaan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT * FROM statuspekerjaan_m WHERE kdprofile = $kdProfile and statusenabled = true and kodeexternal = 'IPSRS' "));

        return $this->respond($data);
    }

    public function SaveDataStatus(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = true;
            }else{

                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->objectstatuspekerjaanfk = $request['objectstatuspekerjaanfk'];
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

    public function sendNotifWhatsapp(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        //https://www.twilio.com/console/sms/whatsapp/sandbox
        //composer require twilio/sdk
        $sid    = "ACc6ac08ce1cc9096e0fd57a0c38801118";
        $token  = "cada716b5a36721fd86832662bbd06fe";
        $twilio = new Client($sid, $token);
        $array =[
            "whatsapp:+628988891429",
//            "whatsapp:+6282211333013",
//            "whatsapp:+6285702501576",
//            "whatsapp:+6283838339887",
//            "whatsapp:+6281649111417",
        ];
        $i = 0;
        foreach ($array as $arr){
            $message = $twilio->messages
                ->create($array[$i], // to
                    array(
                        "from" => "whatsapp:+14155238886",
                        "body" => "Testing Ipsrs!!! "
                    )
                );
            $i = $i+1;
        }
        $result = array(
            'respond' => 'success',
            'as' => 'er@epic'
        );
        return $this->respond($result);

    }

    public function getJenisAlat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = DB::select(DB::raw("
        SELECT *
            FROM jenispekerjaan_m
			WHERE kdprofile = $kdProfile and statusenabled = true and jenispekerjaan = 'IPSRS' AND namaexternal='Jenis Alat'"));

        return $this->respond($data);

    }

    public function SaveDataJenisAlat(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($request['norec'] == ''){
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = true;
            }else{
                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $newCOA->objectjenisalatfk = $request['jenisAlat'];
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

    public function HapusPermohonanIPSRS(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            StrukPlanning::where('norec', $request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update(['statusenabled' => false]);
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

    public function SavePengerjaanPermohonan(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {

            if ($request['norec'] == ''){
                return $this->respond("ASUP");
                $newCOA = new StrukPlanning();
                $norecHead = $newCOA->generateNewId();
                $newCOA->kdprofile = $kdProfile;
                $newCOA->norec = $norecHead;
                $newCOA->statusenabled = true;
            }else{
                $newCOA =  StrukPlanning::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->first();
                $Spd = StrukPlanningDetail::where('noplanningfk', $request['norec'])->delete();
            }
                $newCOA->startdate = $request['strukplanning']['tglmulai'];
                $newCOA->duedate = $request['strukplanning']['tglselesai'];
                $newCOA->objectstatuspekerjaanfk = $request['strukplanning']['status'];
                $newCOA->deskripsiplanning = $request['strukplanning']['worklist'];
                $newCOA->keteranganverifikasi = $request['strukplanning']['identifikasikerusakan'];
                $newCOA->objectpegawaipjawabevaluasifk = $request['strukplanning']['penanngungjawab'];
                $newCOA->objectpegawaipjawabfk = $request['strukplanning']['penanngungjawab'];
                $newCOA->objectjenispekerjaanfk = $request['strukplanning']['jeniskerusakan'];
                $newCOA->objectjenisalatfk = $request['strukplanning']['jenisalat'];
                $newCOA->save();
                $norecHead2 = $newCOA->norec;

            foreach ($request['datapegawai'] as $items){
                $Spd = new StrukPlanningDetail();
                $norecDetail = $newCOA->generateNewId();
                $Spd->kdprofile = $kdProfile;
                $Spd->norec = $norecDetail;
                $Spd->statusenabled = true;
                $Spd->pegawaifk = $items['idpegawai'];
                $Spd->noplanningfk = $norecHead2;
                $Spd->save();
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan";
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

    public function SaveHapusPengerjaanPermohonan(Request $request){
        DB::beginTransaction();
        $dataLogin = $request->all();
        $kdProfile = (int)$this->getDataKdProfile($request);
        try {



            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan";
        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil" ;
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getComboRuanganIPSRS(Request $request){
        $dataLogin = $request->all();
        $kdProfile = (int)$this->getDataKdProfile($request);
        $data = DB::table('ruangan_m as rm')
                ->select('rm.id','rm.namaruangan')
                ->where('rm.kdprofile',$kdProfile)
                ->whereIn('rm.id',[58,571])
                ->get();
        
        $result[] = array(
            'ruanganipsrs' => $data,
        );

        return $this->respond($result);
    }

}