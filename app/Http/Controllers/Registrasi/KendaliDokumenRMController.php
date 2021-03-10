<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 13/08/2019
 * Time: 20.53
 */
namespace App\Http\Controllers\Registrasi;


use App\Http\Controllers\ApiController;
use App\Transaksi\DetailDiagnosaPasien;

use App\Transaksi\DetailDiagnosaTindakanPasien;
use App\Transaksi\DiagnosaPasien;

use App\Transaksi\DiagnosaTindakanPasien;
use App\Transaksi\KendaliDokumenRekamMedis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;
//use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;

use App\Master\Pasien;
use App\Master\Alamat;
use App\Master\Ruangan;
use App\Transaksi\PasienDaftar;
use App\Transaksi\AntrianPasienDiperiksa;

class KendaliDokumenRMController extends  ApiController
{

    use Valet;
    public function __construct() {
        parent::__construct($skip_authentication=false);
    }
    public function getPasienByNoCm( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->select('ps.id as nocmfk','ps.nocm','ps.namapasien','ps.nohp','ps.notelepon','ps.namakeluarga','ps.namaibu',
                'ps.namaayah','ps.tempatlahir','ps.tgllahir','alm.alamatlengkap','ps.foto')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','ps.id')
//            ->leftJoin('agama_m as ag','ag.id','=','ps.objectagamafk')
//            ->leftJoin('pendidikan_m pnd','pnd.id','=','ps.objectpendidikanfk')
//            ->leftJoin('negara_m as ng','ng.id','=','ps.objectnegarafk')
//            ->leftJoin('kebangsaan_m as kb','kb.id','=','ps.objectkebangsaanfk');
            ->where('ps.kdprofile', $idProfile);

        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('ps.nocm', '=', $request['noCm']);
//            $data = $data->where('ps.nocm', 'ilike', '%'. $request['noCm']);
        };
        $data=$data->where('ps.statusenabled',true);
        $data=$data->first();
        if($data->foto != null){
            $data->foto = "data:image/jpeg;base64," . base64_encode($data->foto);
        }
        $result = array(
            'datas' => $data,
            'message' => 'cepot',
        );
        return $this->respond($data);
    }

    public function getPasienDaftar( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.nocmfk','pd.noregistrasi','pd.tglregistrasi','pd.objectruanganlastfk','ru.namaruangan')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->where('pd.kdprofile', $idProfile);

        if(isset($request['nocmfk']) && $request['nocmfk']!="" && $request['nocmfk']!="undefined"){
            $data = $data->where('pd.nocmfk', '=',  $request['nocmfk']);
        };
        $data=$data->get();

        $result = array(
            'data' => $data,
            'message' => 'cepot',
        );
        return $this->respond($result);
    }


    public function getDaftarKendaliDokumen( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('kendalidokumenrekammedis_t as kdr')
            ->join('pasien_m as ps','ps.id','=','kdr.nocmfk')
            ->leftJoin('statuskendalidokumen_m as skd','skd.id','=','kdr.objectstatuskendalidokumenfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','kdr.objectruanganasalfk')
            ->leftJoin('ruangan_m as ru1','ru1.id','=','kdr.objectruangantujuanfk')
            ->select('kdr.norec as norec_kdr','kdr.nocmfk','kdr.tglupdate','ps.nocm', 'kdr.dariunit','kdr.keunit','skd.name','kdr.tglkembali',
                     'kdr.objectstatuskendalidokumenfk','kdr.objectruanganasalfk','ru.namaruangan as ruanganasal','kdr.objectruangantujuanfk','kdr.tglkeluar',
                     'ru1.namaruangan as ruangantujuan','kdr.catatan',
                DB::raw('case when kdr.tglkembali is NULL then \'Belum\' else \'Sudah\' end as kembali')
            )
            ->where('kdr.kdprofile', $idProfile)
            ->orderBy('kdr.tglupdate','desc');

        if(isset($request['nocmfk']) && $request['nocmfk']!="" && $request['nocmfk']!="undefined"){
            $data = $data->where('kdr.nocmfk', '=',  $request['nocmfk']);
        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('kdr.tglupdate', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('kdr.tglupdate', '<=', $tgl);
        }
        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['noCm']);
        };
        if(isset($request['unit']) && $request['unit']!="" && $request['unit']!="undefined"){
            $data = $data->where('kdr.dariunit', 'ilike', '%'. $request['unit'] .'%')
                 ->Orwhere('kdr.keunit', 'ilike', '%'. $request['unit'] .'%');;
        };
        if(isset($request['statusId']) && $request['statusId']!="" && $request['statusId']!="undefined"){
            $data = $data->where('kdr.objectstatuskendalidokumenfk', '=',  $request['statusId']);
        };
//        $data=$data->orderBy('kdr.tglupdate');
        $data=$data->get();

        $result = array(
            'data' => $data,
            'message' => 'cepot',
        );
        return $this->respond($result);
   }
    public function saveKendaliDokRM(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();

        try{
        if ($request['norec_kdr']=='') {
            $newKDR = new KendaliDokumenRekamMedis();
            $norec = $newKDR->generateNewId();
            $newKDR->norec = $norec;
            $newKDR->kdprofile = $idProfile;
            $newKDR->statusenabled =true;
        }else{
            $newKDR =  KendaliDokumenRekamMedis::where('norec',$request['norec_kdr'])->first();
        }
//            $newKDR->dariunit = $request['dariunit'];
//            $newKDR->keunit = $request['keunit'];
            if($request['objectstatuskendalidokumenfk'] == 3){
                $newKDR->objectruanganasalfk = $request['objectruanganasalfk'];
                $newKDR->objectruangantujuanfk = $request['objectruangantujuanfk'];
                $newKDR->nocmfk = $request['nocmfk'];
                $newKDR->objectstatuskendalidokumenfk = $request['objectstatuskendalidokumenfk'];
                $newKDR->tglupdate = $request['tglupdate'];
                $newKDR->tglkembali = $request['tglkembali'];
                $newKDR->catatan = $request['catatan'];
                $newKDR->save();
            }
            elseif($request['objectstatuskendalidokumenfk'] != 3){
                $newKDR->objectruanganasalfk = $request['objectruanganasalfk'];
                $newKDR->objectruangantujuanfk = $request['objectruangantujuanfk'];
                $newKDR->nocmfk = $request['nocmfk'];
                $newKDR->objectstatuskendalidokumenfk = $request['objectstatuskendalidokumenfk'];
                $newKDR->tglupdate = $request['tglupdate'];
                $newKDR->tglkembali = $request['tglkembali'];
                $newKDR->tglkeluar = $request['tglkeluar'];
                $newKDR->catatan = $request['catatan'];
                $newKDR->save();
            }


        $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
          $transMessage = "simpan";
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Tersimpan";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'data' => $newKDR,
                'as' => 'ramdanegie@Cepot',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'data' =>$newKDR,
                'as' => 'ramdanegie@Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataCombo(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $details=$request->all();
        $dataRuanganTujuan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->wherein('ru.objectdepartemenfk',$kdDepartemenRawatPelayanan)
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataRuanganKendali = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
//            ->wherein('ru.objectdepartemenfk',[16,17,18,24,27,28,35,30])
            ->where('ru.id',469)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataStatusKendali = \DB::table('statuskendalidokumen_m as skd')
            ->select('skd.id','skd.name')
            ->where('skd.kdprofile', $idProfile)
            ->where('skd.statusenabled',true)
            ->orderBy('skd.name')
            ->get();

        $data=array(
            'ruangan' => $dataRuanganTujuan,
            'dataRuanganKendali' => $dataRuanganKendali,
            'dataStatusKendali' => $dataStatusKendali,
            'message' => 'Cepot'
        );
        return $this->respond($data);
    }

    public function getDataTambahKendali(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data= \DB::table('pasiendaftar_t as pd')
            ->leftJoin('kendalidokumenrekammedis_t as kdrm','kdrm.nocmfk','=','pd.nocmfk')
            ->join('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftJoin('ruangan_m as ru1','ru1.id','=','kdrm.objectruanganasalfk')
            ->leftJoin('ruangan_m as ru2','ru2.id','=','kdrm.objectruangantujuanfk')
            ->leftJoin('statuskendalidokumen_m as skd','skd.id','=','kdrm.objectstatuskendalidokumenfk')
            ->select('pd.noregistrasi','pm.nocm','pm.namapasien','pd.objectruanganlastfk as kdruangan','ru.namaruangan as ruangdaftar',
			         'kdrm.objectruanganasalfk','ru1.namaruangan as ruangasaldokumen','kdrm.objectruangantujuanfk',
                     'ru2.namaruangan as ruangtujuan','kdrm.tglkeluar','kdrm.tglkembali','kdrm.tglupdate','kdrm.catatan'
//                DB::raw('COUNT(pd.objectruanganasalfk)')
                )
            ->where('pd.kdprofile', $idProfile)
            ->groupBy('pd.noregistrasi','pm.nocm','pm.namapasien','pd.objectruanganlastfk','ru.id','ru.namaruangan','kdrm.objectruanganasalfk','ru1.namaruangan',
                      'kdrm.objectruangantujuanfk','ru2.namaruangan','kdrm.tglkeluar','kdrm.tglkembali','kdrm.tglupdate','kdrm.catatan');

        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('pm.nocm','=',$request['nocm']);
        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        $data=$data->orderBy('pd.noregistrasi', 'desc');
        $data=$data->take(1);
        $data=$data->get();

        $dataRuanganKendali = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.id',469)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

        $data=array(
            'datakendali' => $data,
            'rak' => $dataRuanganKendali,
            'message' => 'Cepot'
        );
        return $this->respond($data);
    }
    public function getDataKendali(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $details=$request->all();
        $dataKendali = \DB::table('kendalidokumenrekammedis_t as kdrm')
            ->leftJoin('ruangan_m as ru','ru.id','=','kdrm.objectruanganasalfk')
            ->leftJoin('ruangan_m as ru1','ru1.id','=','kdrm.objectruangantujuanfk')
            ->leftJoin('statuskendalidokumen_m as skd','skd.id','=','kdrm.objectstatuskendalidokumenfk')
            ->select('kdrm.norec','kdrm.nocmfk','kdrm.objectruanganasalfk',
                     'ru.namaruangan as ruanganasal','kdrm.objectruangantujuanfk',
			         'ru1.namaruangan as ruangantujuan','kdrm.objectstatuskendalidokumenfk','skd.name',
                     'kdrm.tglupdate','kdrm.tglkeluar','kdrm.tglkembali','kdrm.catatan')
            ->where('kdrm.kdprofile', $idProfile)
            ->where('kdrm.statusenabled',true)
            ->orderBy('kdrm.tglupdate');

        if(isset($request['norec']) && $request['norec']!="" && $request['norec']!="undefined"){
            $dataKendali = $dataKendali->where('kdrm.norec', '=',  $request['norec']);
        };
        $dataKendali=$dataKendali->get();

        $data=array(
            'datakendali' => $dataKendali,
            'message' => 'Cepot'
        );
        return $this->respond($data);
    }
    public function getLaporanTracer(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data= \DB::table('pasiendaftar_t as pd')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join ('kendalidokumenrekammedis_t as kdrm','kdrm.nocmfk','=','ps.id')
//            ->join ('antrianpasiendiperiksa_t as apd',function($join)
//            {
//                $join->on('apd.noregistrasifk','=','pd.norec');
//                $join->on('kdrm.objectruangantujuanfk','=','apd.objectruanganfk');
//            })
            ->join ('ruangan_m as ru1','ru1.id','=','kdrm.objectruanganasalfk')
            ->join ('ruangan_m as ru2','ru2.id','=','kdrm.objectruangantujuanfk')
            ->leftjoin ('statuskendalidokumen_m as skd','skd.id','=','kdrm.objectstatuskendalidokumenfk')
            ->select('pd.noregistrasi','pd.tglregistrasi',
                        'ps.nocm','ps.namapasien', 'ru1.namaruangan as unitasal', 'ru2.namaruangan as unittujuan',
                        'kdrm.tglkeluar','kdrm.objectstatuskendalidokumenfk','skd.name as status',
                        'kdrm.dariunit','kdrm.keunit'
            )
            ->where('pd.kdprofile', $idProfile)
            ->where('kdrm.objectstatuskendalidokumenfk','1'); #dipinjam
//            ->groupBy('pd.noregistrasi','pd.tglregistrasi',
//                'ps.nocm','ps.namapasien', 'ru1.namaruangan', 'ru2.namaruangan',
//                'kdrm.tglkeluar','kdrm.objectstatuskendalidokumenfk','skd.name',
//                'kdrm.dariunit','kdrm.keunit');

        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike', '%'.$request['nocm'].'%');
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined"){
            $data = $data->where('ps.namapasien','ilike', '%'.$request['namaPasien'].'%');
        };
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%'.$request['noReg'].'%');
        }
        if (isset($request['unit']) && $request['unit'] != "" && $request['unit'] != "undefined") {
            $data = $data->where('ru1.id', '=', $request['unit']);
        }
        if (isset($request['status']) && $request['status'] != "" && $request['status'] != "undefined") {
            $data = $data->where('kdrm.objectstatuskendalidokumenfk', '=', $request['status']);
        }
        $data=$data->orderBy('kdrm.tglkeluar', 'desc');
        $data=$data->get();

        $dataFix = [];
        foreach ($data as $item){
            $samateu = false;
            foreach ($dataFix as $itemsss){
                if ($item->nocm == $itemsss['nocm']){
                    $samateu = true;
                    if (date($item->tglkeluar) > date($itemsss['tglkeluar']))
                        $itemsss['tglkeluar'] = $item->tglkeluar;
                        $itemsss['unittujuan'] = $item->unittujuan;
                        break;
                    }
                }
            if ($samateu == false){
                $dataFix[] = array(
                    'selisih' => $this->getSelisih($item->tglregistrasi,$item->tglkeluar) ,
                    'noregistrasi' => $item->noregistrasi,
                    'tglregistrasi' => $item->tglregistrasi,
                    'tglkeluar' => $item->tglkeluar,
                    'nocm' => $item->nocm,
                    'namapasien' => $item->namapasien,
                    'unitasal' => $item->unitasal,
                    'unittujuan' => $item->unittujuan,
                    'status' => $item->status,
                    'dariunit' => $item->dariunit,
                    'keunit' => $item->keunit,
                );
            }
        }
        $data = array(
            'data' => $dataFix,
            'message' => 'Inhuman'
        );
        return $this->respond($data);
    }
    public function getSelisih($dateAwal, $dateAkhir){
        $datetime = new \DateTime(date($dateAwal));
        return $datetime->diff(new \DateTime(date($dateAkhir)))
            ->format('%dhr %hjam %imnt');
    }
}