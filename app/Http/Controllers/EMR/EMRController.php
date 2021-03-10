<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 9/19/2017
 * Time: 11:44 PM
 */

namespace App\Http\Controllers\EMR;

use App\Http\Controllers\ApiController;
use App\Master\DiagnosaKeperawatan;
use App\Master\Evaluasi;
use App\Master\Implementasi;
use App\Master\Intervensi;
use App\Master\JenisKelamin;
use App\Master\Kelas;
use App\Master\KelompokPasien;
use App\Master\KelompokTransaksi;
use App\Master\Pasien;
use App\Master\Pegawai;
use App\Master\Ruangan;
use App\Transaksi\EMRPasienD_Temp;
use App\Master\SettingDataFixed;
use App\Transaksi\Anamnesis;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\AntrianPasienRegistrasi;
use App\Transaksi\CPPT;
use App\Transaksi\CPPTDetail;
use App\Transaksi\DetailDiagnosaPasien;
use App\Transaksi\DetailDiagnosaTindakanPasien;
use App\Transaksi\DiagnosaPasien;
use App\Transaksi\DiagnosaTindakanPasien;
use App\Transaksi\Edukasi;
use App\Transaksi\EECG;
use App\Transaksi\FormulisObat;
use App\Transaksi\FormulisObatDetail;
use App\Transaksi\KendaliDokumenRekamMedis;
use App\Transaksi\LoggingUser;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PasienPerjanjian;
use App\Transaksi\PemeriksaanUmum;
use App\Transaksi\PengkajianAwalBaru;
use App\Transaksi\RekamMedisDokter;
use App\Transaksi\RekamMedis;
use App\Transaksi\PengkajianImage;
use App\Transaksi\RekamMedisICU;
use App\Transaksi\RekamMedisICUDetail;
use App\Transaksi\Rencana;
use App\Transaksi\ResumeMedis;
use App\Transaksi\ResumeMedisDetail;
use App\Transaksi\RisOrder;
use App\Transaksi\RiwayatPengobatan;
use App\Transaksi\RmPemeriksaanFisik;
use App\Transaksi\StrukOrder;
use App\Transaksi\EMRPasien;
use App\Transaksi\EMRPasienD;
use App\Transaksi\EMROdontogram;
use App\Transaksi\Surveilans;
use App\Transaksi\SurveilansAntibiotik;
use App\Transaksi\SurveilansFaktorResiko;
use App\Transaksi\SurveilansFrd;
use App\Transaksi\SurveilansOperasi;
use App\Transaksi\KepatuhanCuciTangan;
use App\Transaksi\KepatuhanHandHygiene;
use App\Transaksi\EmrFoto;
use Illuminate\Http\Request;
use App\Transaksi\SeqNumberEMR;
//use Illuminate\Http\Response;
use DB;
use App\Traits\Valet;
//use Carbon\Carbon;
use MongoDB\Driver\ReadConcern;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;
use Twilio\Rest\Client;
use App\Transaksi\SeqNumber;

class EMRController  extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function saveRekamMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            if (isset($request['form']) &&
                $request['form'] == "masalahkeperawatan" &&
                isset($request['kodeexternal'])) {
                $delete = RekamMedis::where('riwayatpapfk', $request['riwayatpapfk'])
                    ->where('kdprofile', $idProfile)
                    ->where('noregistrasifk', $request['noregistrasifk'])
                    ->where('kodeexternal', $request['kodeexternal'])
                    ->delete();
            }
            foreach ($request['data'] as $item) {
                if ($item['norec'] == '-') {
                    $RekamMedis = new RekamMedis();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                } else {
                    $RekamMedis = RekamMedis::where('norec', $item['norec'])->where('kdprofile',$idProfile)->first();
                }
                $RekamMedis->objectfk = $item['objectfk'];
                if (isset($request['noregistrasifk'])) {
                    $RekamMedis->noregistrasifk = $request['noregistrasifk'];
                }
                $RekamMedis->nilai = $item['nilai'];
                $RekamMedis->satuan = $item['satuan'];
                $RekamMedis->jenisobject = $item['jenisobject'];
                if (isset($request['riwayatpapfk'])) {
                    $RekamMedis->riwayatpapfk = $request['riwayatpapfk'];
                }
                if (isset($request['kodeexternal'])) {
                    $RekamMedis->kodeexternal = $request['kodeexternal'];
                }
                if (isset($request['jenisskrining'])) {
                    $RekamMedis->jenisskrining = $request['jenisskrining'];
                }
                if (isset($request['nocm'])) {
                    $RekamMedis->nocm = $request['nocm'];
                }
                $RekamMedis->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Data Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Data Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRekamMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('rekammedis_t as rm')
            ->select('rm.norec', 'rm.objectfk', 'rm.nilai', 'rm.satuan', 'rm.jenisobject', 'rm.noregistrasifk')
//            ->where('rm.noregistrasifk',$request['noregistrasifk'])
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.objectfk', 'ilike', $request['objectfk'] . '%');
//            ->where('rm.kodeexternal','igd');
        if (isset($request['riwayatfk']) && $request['riwayatfk'] != '') {
            $data = $data->where('rm.riwayatpapfk', '=', $request['riwayatfk']);
        }
        if (isset($request['Nocm']) && $request['Nocm'] != '') {
            $data = $data->where('rm.nocm', '=', $request['Nocm']);
        }
        if (isset($request['kodeExt']) && $request['kodeExt'] != '') {
            $data = $data->where('rm.jenisskrining', '=', $request['kodeExt']);
        }

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'miftah',
        );

        return $this->respond($result);
    }

    public function getComboRekMed(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $deptJalan = explode(',', $this->settingDataFixed('kdDepartemenRawatJalanFix',$idProfile));
        $deptKonsul = explode(',', $this->settingDataFixed('KdDeptKonsul',$idProfile));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item) {
            $kdDepartemenRawatJalan [] = (int)$item;
        }
        $kdDepartemenKonsul = [];
        foreach ($deptKonsul as $items) {
            $kdDepartemenKonsul [] = (int)$items;
        }

        $dataPegawaiLogin = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.id', $dataLogin['userData']['id'])
            ->first();

        $kebangsaan = \DB::table('kebangsaan_m as rm')
            ->select('rm.id', 'rm.name')
            ->where('rm.statusenabled', true)
            ->get();

        $agama = \DB::table('agama_m as rm')
            ->select('rm.id', 'rm.agama')
            ->where('rm.statusenabled', true)
            ->where('rm.kdprofile', $idProfile)
            ->get();

        $ruangan = \DB::table('ruangan_m as rm')
            ->select('rm.id', 'rm.namaruangan')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true)
            ->get();
        $statusperkawinan = \DB::table('statusperkawinan_m as rm')
            ->select('rm.id', 'rm.statusperkawinan')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true)
            ->get();
        $golongandarah = \DB::table('golongandarah_m as rm')
            ->select('rm.id', 'rm.golongandarah')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true)
            ->where('rm.golongandarah', '!=', '-')
            ->get();
        $dokter = \DB::table('pegawai_m as rm')
            ->select('rm.id', 'rm.namalengkap')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true)
            ->where('rm.objectjenispegawaifk', 1)
            ->orderBy('rm.namalengkap')
            ->get();

        $ruangKonsul = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataRuanganJalan = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
            ->orderBy('ru.namaruangan')
            ->get();
        $JenisKelamin = JenisKelamin::where('statusenabled', true)
            ->select('id', 'jeniskelamin')
            ->get();
        $ruangKonsulNonPenunjang = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk',$kdDepartemenKonsul)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $result = array(
            'kebangsaan' => $kebangsaan,
            'agama' => $agama,
            'ruangan' => $ruangan,
            'statusperkawinan' => $statusperkawinan,
            'golongandarah' => $golongandarah,
            'dokter' => $dokter,
            'ruangankonsul' => $ruangKonsul,
            'ruanganrajal' => $dataRuanganJalan,
            'pegawailogin' => $dataPegawaiLogin,
            'jeniskelamin' => $JenisKelamin,
            'ruangkonsulnonpenunjang' => $ruangKonsulNonPenunjang,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }

    public function getInfoPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->leftJoin('pasiendaftar_t as pd', 'pd.nocmfk', '=', 'ps.id')
            ->leftJoin('agama_m as agm', 'agm.id', '=', 'ps.objectagamafk')
            ->leftJoin('statusperkawinan_m as sp', 'sp.id', '=', 'ps.objectstatusperkawinanfk')
            ->leftJoin('kebangsaan_m as kb', 'kb.id', '=', 'ps.objectkebangsaanfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select('pd.noregistrasi', 'ps.nocm', 'ps.namapasien', 'ps.objectagamafk', 'agm.agama', 'ps.objectstatusperkawinanfk',
                'sp.statusperkawinan', 'ps.objectkebangsaanfk', 'kb.name as kebangsaan', 'pd.objectruanganlastfk', 'ru.namaruangan',
                'pd.tglpulang',
                DB::raw("case when pd.noregistrasi is not null and pd.tglpulang is null then 'dirawat' else 'tidak dirawat' end
                as statusrawat, case when ps.objectstatusperkawinanfk in (2,3) then 'kawin' else 'belum kawin' end as statuskawin"))
            ->where('ps.kdprofile', $idProfile)
            ->where('ps.nocm', $request['noCm'])
            ->orderBy('pd.noregistrasi', 'desc')
            ->get();

        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getMasterPAP(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataHead = DB::table('pengkajianawal_m as pap')
            ->where('pap.kdprofile', $idProfile)
            ->select('*');

        if (isset($request['id']) && $request['id'] != "") {
            $dataHead = $dataHead->where('pap.id', '=', $request['id']);
        }
        $dataHead = $dataHead->get();

        $result = array();
        foreach ($dataHead as $item) {
            $headfk = $item->id;
            $child1 = DB::select(DB::raw("
                      select *
                      from pengkajianawal_m as pap
                      where pap.kdprofile = $idProfile and pap.headpengkajianawalfk=$headfk;")
            );
            foreach ($child1 as $items) {
                $headfk1 = $items->id;
                $child2 = DB::select(DB::raw("
                      select *
                      from pengkajianawal_m as pap
                      where pap.kdprofile = $idProfile and pap.headpengkajianawalfk=$headfk1;")
                );

                foreach ($child2 as $items2) {
                    $headfk2 = $items2->id;
                    $child3 = DB::select(DB::raw("
                      select *
                      from pengkajianawal_m as pap
                      where pap.kdprofile = $idProfile and pap.headpengkajianawalfk=$headfk2;")
                    );

                    foreach ($child3 as $items3) {
                        $headfk3 = $items3->id;
                        $child4 = DB::select(DB::raw("
                              select *
                              from pengkajianawal_m as pap
                              where pap.kdprofile = $idProfile and pap.headpengkajianawalfk=$headfk3;")
                        );
                    }
                }
            }
        }
        $detail4[] = array(
            'id' => $items3->id,
            'nama' => $items3->nama,
            'descNilai' => $items3->descnilai,
            'value' => null,
            'detail' => $child4
        );

        $detail3[] = array(
            'id' => $items2->id,
            'nama' => $items2->nama,
            'descNilai' => $items2->descnilai,
            'value' => null,
            'detail' => $detail4
        );

        $detail2[] = array(
            'id' => $items->id,
            'nama' => $items->nama,
            'descNilai' => $items->descnilai,
            'value' => null,
            'detail' => $detail3
        );
        $detail[] = array(
            'id' => $item->id,
            'nama' => $item->nama,
            'descNilai' => $item->descnilai,
            'value' => null,
            'detail' => $detail2
        );

        $result[] = array(
            'pengkajianawal' => $detail
        );

        return $this->respond($result);
    }

    public function getDiagnosaKeperawatan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('diagnosakeperawatan_m as dp')
            ->select('dp.id', 'dp.namadiagnosakep as namaDiagnosaKep', 'dp.kodeexternal', 'dp.diagnosakep', 'dp.deskripsidiagnosakep')
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true);

        if (isset($request['id']) && $request['id'] != '') {
            $data = $data->where('dp.id', $request['id']);
        }

        if (isset($request['namadiagnosakep']) && $request['namadiagnosakep'] != '') {
            $data = $data->where('dp.namadiagnosakep', 'ilike', '%' . $request['namadiagnosakep'] . '%');
        }
        if (isset($request['kodeexternal']) && $request['kodeexternal'] != '') {
            $data = $data->where('dp.kodeexternal', 'ilike', '%' . $request['kodeexternal'] . '%');
        }
        $data = $data->orderBy('dp.namadiagnosakep');
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDetailDiagnosaKeperawatan(Request $request){
        $id = $request['id'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataHead = DB::table('diagnosakeperawatan_m as pap')
            ->select('pap.id', 'pap.namadiagnosakep')
            ->where('dp.kdprofile', $idProfile)
            ->where('pap.id', $id)
            ->where('pap.statusenabled', true)
            ->get();
        $detail = [];
        foreach ($dataHead as $item) {

            $implementasi = DB::table('implementasi_m as pap')
                ->select('pap.name', 'pap.id', 'pap.kodeexternal')
                ->where('pap.objectdiagnosakeperawatanfk', $id)
                ->where('pap.statusenabled', true)
                ->get();
            $intervensi = DB::table('intervensi_m as pap')
                ->select('pap.name', 'pap.id', 'pap.kodeexternal')
                ->where('pap.objectdiagnosakeperawatanfk', $id)
                ->where('pap.statusenabled', true)
                ->get();
            $evaluasi = DB::table('evaluasi_m as pap')
                ->select('pap.name', 'pap.id', 'pap.kodeexternal')
                ->where('pap.objectdiagnosakeperawatanfk', $id)
                ->where('pap.statusenabled', true)
                ->get();
            $detail[] = array(
                'nama' => $item->namadiagnosakep,
                'header' => 'Intervensi',
                'id' => $item->id,
                'detail' => $intervensi
            );
            $detail[] = array(
                'nama' => $item->namadiagnosakep,
                'header' => 'Implementasi',
                'id' => $item->id,
                'detail' => $implementasi
            );
            $detail[] = array(
                'nama' => $item->namadiagnosakep,
                'header' => 'Evaluasi',
                'id' => $item->id,
                'detail' => $evaluasi
            );

        }
        return $this->respond($detail);
    }

    public function getHistoryDiagnosaKeperawatan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdIntRm = $this->settingDataFixed('KdIntRm',$idProfile);
        $head = [];
        $object = $request['objectfk'];
        $arrObject = explode(",", $object);
        $imp = \DB::table('rekammedis_t as rm')
            ->select('rm.norec', 'rm.objectfk', 'rm.nilai', 'rm.satuan', 'rm.jenisobject', 'rm.noregistrasifk', 'ev.name', 'ev.id', 'dp.kodeexternal',
                'dp.id as diagnosakep_id', 'dp.namadiagnosakep')
            ->leftjoin('diagnosakeperawatan_m as dp', DB::raw("dp.id"), '=', 'rm.nilai')
            ->leftjoin('implementasi_m as ev', 'ev.kodeexternal', '=', 'rm.objectfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.noregistrasifk', $request['noregistrasifk'])
            ->where('rm.riwayatpapfk', '=', $request['riwayatfk'])
            ->where(function ($query) use ($arrObject) {
                for ($i = 0; $i < count($arrObject); $i++) {
                    $query->orWhere('rm.objectfk', 'ilike', $arrObject[$i] . '%');
                }
            })
            ->orderBy('dp.id')
            ->get();

        foreach ($imp as $itemhead) {
            if ($itemhead->diagnosakep_id !== null) {
                $implementasi = DB::table('implementasi_m as pap')
                    ->select('pap.name', 'pap.id', 'pap.kodeexternal')
                    ->join('rekammedis_t as rm', DB::raw("pap.id"), '=', 'rm.nilai')
                    ->where('rm.objectfk', 'ilike', '%IMP%')
                    ->where('pap.objectdiagnosakeperawatanfk', $itemhead->diagnosakep_id)
                    ->where('pap.statusenabled', true)
                    ->where('rm.noregistrasifk', $request['noregistrasifk'])
                    ->where('rm.riwayatpapfk', $request['riwayatfk'])
                    ->groupBy('pap.name', 'pap.id', 'pap.kodeexternal')
                    ->get();
                if (count($implementasi) > 0) {
                    $head [] = array(
                        'header' => 'Implementasi',
                        'id' => $itemhead->diagnosakep_id,
                        'nama' => $itemhead->namadiagnosakep,
                        'kodeexternal' => $itemhead->kodeexternal,
                        'detail' => $implementasi,
                    );
                }
            }
        }
        $ev = \DB::table('rekammedis_t as rm')
            ->select('rm.norec', 'rm.objectfk', 'rm.nilai', 'rm.satuan', 'rm.jenisobject', 'rm.noregistrasifk', 'ev.name', 'ev.id', 'dp.kodeexternal',
                'dp.id as diagnosakep_id', 'dp.namadiagnosakep')
            ->leftjoin('diagnosakeperawatan_m as dp', DB::raw("dp.id "), '=', 'rm.nilai')
            ->leftjoin('evaluasi_m as ev', 'ev.kodeexternal', '=', 'rm.objectfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.noregistrasifk', $request['noregistrasifk'])
            ->where('rm.riwayatpapfk', '=', $request['riwayatfk'])
            ->where(function ($query) use ($arrObject) {
                for ($i = 0; $i < count($arrObject); $i++) {
                    $query->orWhere('rm.objectfk', 'ilike', $arrObject[$i] . '%');
                }
            })
            ->orderBy('dp.id')
            ->get();

        foreach ($ev as $itemhead) {
            if ($itemhead->diagnosakep_id !== null) {
                $evaluasi = DB::table('evaluasi_m as pap')
                    ->select('pap.name', 'pap.id', 'pap.kodeexternal')
                    ->join('rekammedis_t as rm', DB::raw("pap.id"), '=', 'rm.nilai')
                    ->where('rm.objectfk', 'ilike', '%EVL%')
                    ->where('pap.objectdiagnosakeperawatanfk', $itemhead->diagnosakep_id)
                    ->where('pap.statusenabled', true)
                    ->where('rm.noregistrasifk', $request['noregistrasifk'])
                    ->where('rm.riwayatpapfk', $request['riwayatfk'])
                    ->groupBy('pap.name', 'pap.id', 'pap.kodeexternal')
                    ->get();
                if (count($evaluasi) > 0) {
                    $head [] = array(
                        'header' => 'Evaluasi',
                        'id' => $itemhead->diagnosakep_id,
                        'nama' => $itemhead->namadiagnosakep,
                        'kodeexternal' => $itemhead->kodeexternal,
                        'detail' => $evaluasi,
                    );
                }
            }
        }
        $int = \DB::table('rekammedis_t as rm')
            ->select('rm.norec', 'rm.objectfk', 'rm.nilai', 'rm.satuan', 'rm.jenisobject', 'rm.noregistrasifk', 'ev.name', 'ev.id', 'dp.kodeexternal',
                'dp.id as diagnosakep_id', 'dp.namadiagnosakep')
            ->leftjoin('diagnosakeperawatan_m as dp', DB::raw("dp.id "), '=', 'rm.nilai')
            ->leftjoin('intervensi_m as ev', 'ev.kodeexternal', '=', 'rm.objectfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.noregistrasifk', $request['noregistrasifk'])
            ->where('rm.riwayatpapfk', '=', $request['riwayatfk'])
            ->where(function ($query) use ($arrObject) {
                for ($i = 0; $i < count($arrObject); $i++) {
                    $query->orWhere('rm.objectfk', 'ilike', $arrObject[$i] . '%');
                }
            })
            ->orderBy('dp.id')
            ->get();

        foreach ($int as $itemhead) {
            if ($itemhead->diagnosakep_id !== null) {
                $intervensi = DB::table('intervensi_m as pap')
                    ->select('pap.name', 'pap.id', 'pap.kodeexternal')
                    ->join('rekammedis_t as rm', DB::raw("pap.id"), '=', 'rm.nilai')
                    ->where('rm.objectfk', 'like % '.$kdIntRm.' %')
                    ->where('pap.objectdiagnosakeperawatanfk', $itemhead->diagnosakep_id)
                    ->where('pap.statusenabled', true)
                    ->where('rm.noregistrasifk', $request['noregistrasifk'])
                    ->where('rm.riwayatpapfk', '=', $request['riwayatfk'])
                    ->groupBy('pap.name', 'pap.id', 'pap.kodeexternal')
                    ->get();
                if (count($intervensi) > 0) {
                    $head [] = array(
                        'header' => 'Intervensi',
                        'id' => $itemhead->diagnosakep_id,
                        'nama' => $itemhead->namadiagnosakep,
                        'kodeexternal' => $itemhead->kodeexternal,
                        'detail' => $intervensi,
                    );
                }

            }
        }

        return $this->respond($head);

    }

    public function getHistoriDiagnosaKeperawatan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('diagnosakeperawatan_m as dp')
            ->leftjoin('rekammedis_t as rm', 'rm.objectfk', '=', 'dp.kodeexternal')
            ->select('dp.id', 'dp.namadiagnosakep as namaDiagnosaKep', 'dp.kodeexternal',
                'rm.norec', 'rm.objectfk', 'rm.nilai', 'rm.satuan', 'rm.jenisobject', 'rm.noregistrasifk')
            ->where('dp.kdprofile',$idProfile)
            ->where('dp.statusenabled', true)
            ->where('rm.noregistrasifk', $request['noregistrasifk'])
            ->orderBy('dp.namadiagnosakep')
            ->where('rm.objectfk', 'ilike', $request['objectfk'] . '%');
        if (isset($request['riwayatfk']) && $request['riwayatfk'] != '') {
            $data = $data->where('rm.riwayatpapfk', '=', $request['riwayatfk']);
        }
        $data = $data->get();
        $data10 = [];
        foreach ($data as $item) {
            $samateu = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->id == $data10[$i]['id']) {
                    $samateu = true;
                    if ($item->satuan == 'tgl-awal') {
                        $data10[$i]['tglAwal'] = $item->nilai;
                    }
                    if ($item->satuan == 'tgl-akhir') {
                        $data10[$i]['tglAkhir'] = $item->nilai;
                    }
                }
                $i = $i + 1;
            }
            if ($samateu == false) {
                $data10[] = array(
                    'id' => $item->id,
                    'namaDiagnosaKep' => $item->namaDiagnosaKep,
                    'kodeexternal' => $item->kodeexternal,
                    'tglAwal' => $item->nilai,
                    'tglAkhir' => $item->nilai,
                    'noregistrasifk' => $item->noregistrasifk,
                );
            }
        }
        $result = array(
            'data' => $data10,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getRekamMedisDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('rekammedisdokter_t as rm')
            ->select('rm.norec', 'rm.keterangan', 'rm.keterangantambahan', 'rm.tglinput', 'rm.jenisinput', 'rm.noregistrasifk',
                'rm.pegawaifk', 'pg.namalengkap', 'rm.ruanganfk', 'ru.namaruangan')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.pegawaifk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.ruanganfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.noregistrasifk', $request['noregistrasifk'])
            ->where('rm.statusenabled', true)
            ->where('rm.jenisinput', 'ilike', '%' . $request['jenisinput'] . '%');

        if (isset($request['riwayatfk']) && $request['riwayatfk'] != '') {
            $data = $data->where('rm.riwayatpapfk', '=', $request['riwayatfk']);
        }

        $data = $data->get();


        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );

        return $this->respond($result);
    }

    public function saveRekamMedisDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            if ($request['norec'] == '') {
                $RekamMedis = new RekamMedisDokter();
                $RekamMedis->norec = $RekamMedis->generateNewId();
                $RekamMedis->kdprofile = $idProfile;
                $RekamMedis->statusenabled = true;
            } else {
                $RekamMedis = RekamMedisDokter::where('norec', $request['norec'])->first();
            }
            $RekamMedis->noregistrasifk = $request['noregistrasifk'];
            $RekamMedis->keterangan = $request['keterangan'];
            $RekamMedis->keterangantambahan = $request['keterangantambahan'];
            $RekamMedis->ruanganfk = $request['ruanganfk'];
            $RekamMedis->pegawaifk = $request['pegawaifk'];
            $RekamMedis->tglinput = date('Y-m-d H:i:s');
            $RekamMedis->jenisinput = $request['jenisinput'];
            $RekamMedis->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Data Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Simpan Data Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusRekamMedisDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $RekamMedis = RekamMedisDokter::where('norec', $request['norec'])
                ->where('kdprofile',$idProfile)
                ->where('jenisinput', $request['jenisinput'])
                ->update([
                    'statusenabled' => false
                ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Data Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Hapus Data Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function postAnamnesis(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new Anamnesis();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                } else {
                    $RekamMedis = Anamnesis::where('norec', $request['norec'])->first();
                }
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
//                $RekamMedis->tglregistrasi =  $request['tglregistrasi'];
                $RekamMedis->anamnesisdokter = $request['anamnesisdokter'];
                $RekamMedis->anamnesissuster = $request['anamnesissuster'];
                $RekamMedis->pegawaifk = $request['pegawaifk'];
                $RekamMedis->tglinput = date('Y-m-d H:i:s');
                $RekamMedis->ruanganfk = $request['ruanganfk'];
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
            }
            if ($method == 'delete') {
                Anamnesis::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getAnamnesis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('anamnesis_t as rm')
            ->select('rm.norec', 'rm.anamnesisdokter', 'rm.anamnesissuster', 'rm.tglinput', 'rm.noregistrasifk',
                'rm.pegawaifk', 'pg.namalengkap', 'rm.ruanganfk', 'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.pegawaifk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.ruanganfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        if (isset($request['norec_pd']) && $request['norec_pd'] != '') {
            $data = $data->where('pd.norec', $request['norec_pd']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postRiwayatPengobatan(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new RiwayatPengobatan();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                } else {
                    $RekamMedis = RiwayatPengobatan::where('norec', $request['norec'])->first();
                }
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
//                $RekamMedis->tglregistrasi =  $request['tglregistrasi'];
                $RekamMedis->riwayatpengobatan = $request['riwayatpengobatan'];
                $RekamMedis->riwayatpenyakit = $request['riwayatpenyakit'];
                $RekamMedis->pegawaifk = $request['pegawaifk'];
                $RekamMedis->tglinput = date('Y-m-d H:i:s');
                $RekamMedis->ruanganfk = $request['ruanganfk'];
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
            }
            if ($method == 'delete') {
                RiwayatPengobatan::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRiwayatPengobatan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('riwayatpengobatan_t as rm')
            ->select('rm.norec', 'rm.riwayatpengobatan', 'rm.riwayatpenyakit', 'rm.tglinput', 'rm.noregistrasifk',
                'rm.pegawaifk', 'pg.namalengkap', 'rm.ruanganfk', 'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.pegawaifk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.ruanganfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postPemeriksaanUmum(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new PemeriksaanUmum();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                } else {
                    $RekamMedis = PemeriksaanUmum::where('norec', $request['norec'])->first();
                }
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
//                $RekamMedis->tglregistrasi =  $request['tglregistrasi'];
                $RekamMedis->pemeriksaanumum = $request['pemeriksaanumum'];
//                $RekamMedis->riwayatpenyakit =  $request['riwayatpenyakit'];
                $RekamMedis->objectpetugas = $request['pegawaifk'];
                $RekamMedis->tanggalinput = date('Y-m-d H:i:s');
                $RekamMedis->objectruanganfk = $request['ruanganfk'];
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
            }
            if ($method == 'delete') {
                PemeriksaanUmum::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPemeriksaanUmum(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pemeriksaanumum_t as rm')
            ->select('rm.norec', 'rm.pemeriksaanumum', 'rm.tanggalinput as tglinput', 'rm.noregistrasifk',
                'rm.objectpetugas as pegawaifk', 'pg.namalengkap', 'rm.objectruanganfk as ruanganfk', 'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.objectpetugas')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.objectruanganfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postEdukasi(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new Edukasi();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                } else {
                    $RekamMedis = Edukasi::where('norec', $request['norec'])->first();
                }
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
//                $RekamMedis->tglregistrasi =  $request['tglregistrasi'];
                $RekamMedis->edukasi = $request['edukasi'];
//                $RekamMedis->riwayatpenyakit =  $request['riwayatpenyakit'];
                $RekamMedis->objectpetugas = $request['pegawaifk'];
                $RekamMedis->tanggalinput = date('Y-m-d H:i:s');
                $RekamMedis->objectruanganfk = $request['ruanganfk'];
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
            }
            if ($method == 'delete') {
                Edukasi::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getEdukasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('edukasi_t as rm')
            ->select('rm.norec', 'rm.edukasi', 'rm.tanggalinput as tglinput', 'rm.noregistrasifk',
                'rm.objectpetugas as pegawaifk', 'pg.namalengkap', 'rm.objectruanganfk as ruanganfk', 'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.objectpetugas')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.objectruanganfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postRencana(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new Rencana();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                } else {
                    $RekamMedis = Rencana::where('norec', $request['norec'])->first();
                }
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
//                $RekamMedis->tglregistrasi =  $request['tglregistrasi'];
                $RekamMedis->rencana = $request['rencana'];
//                $RekamMedis->riwayatpenyakit =  $request['riwayatpenyakit'];
                $RekamMedis->objectpetugas = $request['pegawaifk'];
                $RekamMedis->tanggalinput = date('Y-m-d H:i:s');
                $RekamMedis->objectruanganfk = $request['ruanganfk'];
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
            }
            if ($method == 'delete') {
                Rencana::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRencana(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('rencana_t as rm')
            ->select('rm.norec', 'rm.rencana', 'rm.tanggalinput as tglinput', 'rm.noregistrasifk',
                'rm.objectpetugas as pegawaifk', 'pg.namalengkap', 'rm.objectruanganfk as ruanganfk', 'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.objectpetugas')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.objectruanganfk')
            ->where('rm.kdprofile',$idProfile)
            ->where('rm.statusenabled', true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postPerjanjianPasien(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new PasienPerjanjian();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                    $noJanji = $this->generateCode(new PasienPerjanjian, 'noperjanjian', 12, 'P' . $this->getDateTime()->format('ymd'), $idProfile);

                } else {
                    $RekamMedis = PasienPerjanjian::where('norec', $request['norec'])->where('kdprofile', $idProfile)->first();
                    $noJanji = $RekamMedis->noperjanjian;
                }
                $idPasien = Pasien::where('nocm', $request['nocm'])->where('kdprofile', $idProfile)->first();
                $RekamMedis->objectpasienfk = $idPasien->id;
                $RekamMedis->objectdokterfk = $request['objectdokterfk'];
                $RekamMedis->jumlahkujungan = $request['jumlahkujungan'];
                $RekamMedis->keterangan = $request['keterangan'];
                $RekamMedis->tglperjanjian = $request['tglperjanjian'];
                $RekamMedis->tglinput = date('Y-m-d H:i:s');
                $RekamMedis->objectruanganfk = $request['objectruanganfk'];
                $RekamMedis->noperjanjian = $noJanji;
                $RekamMedis->save();
                $norec = $RekamMedis->norec;


                $APR = new AntrianPasienRegistrasi();
                $APR->norec = $APR->generateNewId();
                $APR->kdprofile = $idProfile;
                $APR->statusenabled = true;
                $APR->noreservasi = substr(Uuid::generate(), 0, 7);
                $APR->nocmfk = $idPasien->id;
                $APR->objectpegawaifk = $request['objectdokterfk'];
                if ($idPasien->objectpendidikanfk != null) {
                    $APR->objectpendidikanfk = $idPasien->objectpendidikanfk;
                } else {
                    $APR->objectpendidikanfk = 0;
                }

                $APR->objectjeniskelaminfk = $idPasien->objectjeniskelaminfk;
                $APR->objectruanganfk = $request['objectruanganfk'];
                $APR->tanggalreservasi = $request['tglperjanjian'];
                $APR->tipepasien = 'LAMA';
                $APR->type = '';
                $APR->save();

            }
            if ($method == 'delete') {
                PasienPerjanjian::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPasienPerjanjian(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasienperjanjian_t as rm')
            ->select('rm.norec', 'rm.objectdokterfk', 'pg.namalengkap', 'rm.jumlahkujungan', 'rm.keterangan',
                'rm.objectpasienfk', 'rm.tglinput', 'rm.tglperjanjian', 'rm.noperjanjian', 'rm.objectruanganfk', 'ru.namaruangan',
//                'pd.noregistrasi','pd.tglregistrasi',
                'ps.nocm',
                'ps.namapasien')
            ->leftJoin('pasien_m as ps', 'rm.objectpasienfk', '=', 'ps.id')
//            ->leftJoin('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.objectdokterfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'rm.objectruanganfk')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true)
            ->orderBy('rm.noperjanjian');

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        if (isset($request['nik']) && $request['nik'] != '') {
            $data = $data->where('ps.noidentitas', $request['nik']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postCPPT(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new CPPT();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                    $noCPPT = date('dmYHisu');// $this->generateCode(new CPPT, 'noperjanjian', 12, 'CPPT' . $this->getDateTime()->format('ymd') );

                } else {
                    $RekamMedis = CPPT::where('norec', $request['norec'])->where('kdprofile', $idProfile)->first();
//                    $noCPPT = $RekamMedis->nocppt;
//                    CPPTDetail::where('objectcpptfk', $request['norec'])->detele();
                }
                $idJenisPeg = Pegawai::where('id', $request['pegawaifk'])->where('kdprofile', $idProfile)->first();
                if ($idJenisPeg->objectjenispegawaifk == 1) {//dokter
                    $RekamMedis->isverifikasi = true;
                } else {
                    $RekamMedis->isverifikasi = false;
                }
                $idPasien = Pasien::where('nocm', $request['pasienfk'])->where('kdprofile', $idProfile)->first();
                $RekamMedis->pasienfk = $idPasien->id;
                $RekamMedis->pegawaifk = $request['pegawaifk'];
                if ($request['pegawaiasalfk'] != $request['pegawaifk']) {
                    $RekamMedis->pegawaiasalfk = $request['pegawaiasalfk'];
                }
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
                $RekamMedis->ruanganfk = $request['ruanganfk'];
                $RekamMedis->tglinput = date('Y-m-d H:i:s');
                $RekamMedis->s = $request['s'];
                $RekamMedis->o = $request['o'];
                $RekamMedis->a = $request['a'];
                $RekamMedis->p = $request['p'];
//                $RekamMedis->nocppt = $noCPPT;
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
//
//                $detail = new CPPTDetail();
//                $detail->norec = $detail->generateNewId();
//                $detail->kdprofile = 0;
//                $detail->statusenabled = true;
//                $detail->objectcpptfk = $RekamMedis->norec ;
//                $detail->s = $request['details']['s'];
//                $detail->o = $request['details']['o'];
//                $detail->a = $request['details']['a'];
//                $detail->p = $request['details']['p'];
//                $detail->tglinput = date('Y-m-d H:i:s');

            }
            if ($method == 'delete') {
                CPPT::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norec,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getCPPT(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('cppt_t as cp')
            ->select('cp.norec', 'cp.pegawaifk', 'cp.tglinput', 'cp.noregistrasifk', 'cp.isverifikasi',
                'cp.pegawaifk', 'pg.namalengkap', 'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien', 'cp.s', 'cp.o', 'cp.a', 'cp.p', 'cp.nocppt', 'pg2.namalengkap as pegawaiasal', 'pg2.id as pegawaiasalfk')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'cp.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'cp.pasienfk', '=', 'ps.id')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'cp.pegawaifk')
            ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'cp.pegawaiasalfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'cp.ruanganfk')
            ->where('cp.kdprofile',$idProfile)
            ->where('cp.statusenabled', true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        $data = $data->orderBy('cp.tglinput', 'desc');
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function getAntrianPasienDiperiksa($norecAPD,Request $request){
//        $norecAPD = $request['norecAPD'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->leftjoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->leftJoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            ->leftJoin('pendidikan_m as pdd', 'pdd.id', '=', 'ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk', 'pk.id', '=', 'ps.objectpekerjaanfk')
            ->leftjoin('golongandarah_m as gd', 'gd.id', '=', 'ps.objectgolongandarahfk')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->leftJoin('pegawai_m as pg1', 'pg1.id', '=', 'pd.objectpegawaifk')
            ->select('apd.norec', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'ps.namapasien', 'ps.tgllahir', 'ru.objectdepartemenfk',
                'alm.alamatlengkap', 'kps.kelompokpasien', 'ru.namaruangan', 'pg.namalengkap', 'jk.jeniskelamin', 'pd.norec as norec_pd',
                'pdd.pendidikan', 'pk.pekerjaan','ru.objectdepartemenfk','pd.jenispelayanan',
                'rk.namarekanan', 'kls.namakelas', 'pd.nocmfk', 'pd.objectkelompokpasienlastfk', 'apd.objectruanganfk', 'apd.objectpegawaifk',
                'ps.objectjeniskelaminfk', 'apd.objectkelasfk', 'ps.objectgolongandarahfk', 'gd.golongandarah', DB::raw('encode(foto, \'base64\') AS foto'), 'pg1.namalengkap as dokterdpjp', 'pg1.id as iddpjp')
            ->where('apd.kdprofile',$idProfile)
            ->where('apd.norec', $norecAPD);

        $data = $data->first();
        if ($data->foto != null) {
//            $data->foto = "data:image/jpeg;base64," . base64_encode($data->foto);
            $data->foto = "data:image/jpeg;base64," . $data->foto;
        }
        $result = array(
            'result' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function savePengkajianPasien(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idDepRawatInap = (int) $this->settingDataFixed('idDepRawatInap', $kdProfile);

        DB::beginTransaction();
        try {
            if ($request['norec'] == '') {
                $RekamMedis = new PengkajianAwalBaru();
                $RekamMedis->norec = $RekamMedis->generateNewId();
                $RekamMedis->kdprofile = $kdProfile;
                $RekamMedis->statusenabled = true;
                $ruang = Ruangan::where('id', $request['objectruanganfk'])->first();
                $prefix = 'RJ';
                if ($ruang->objectdepartemenfk == $idDepRawatInap) {
                    $prefix = 'RI';
                } else {
                    $prefix = 'RJ';
                }
                $kdPap = $this->generateCode(new PengkajianAwalBaru, 'kdpap', 10, $prefix. $this->getDateTime()->format('ymd'), $kdProfile);
            } else {
                $RekamMedis = PengkajianAwalBaru::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->first();
                $kdPap = $RekamMedis->kdpap;
            }
            $idPasien = Pasien::where('nocm', $request['nocm'])->where('kdprofile', $kdProfile)->first();
            $RekamMedis->objectpasienfk = $idPasien->id;
            $RekamMedis->objectnoregistrasifk = $request['noregistrasifk'];
            $RekamMedis->objectruanganfk = $request['objectruanganfk'];
            $RekamMedis->tglregistrasi = date('Y-m-d H:i:s');
            $RekamMedis->pegawaifk = $request['pegawaifk'];
            $RekamMedis->kdpap = $kdPap;
            $RekamMedis->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'pap' => $RekamMedis,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPengkajianPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pengkajianawalbaru_t as cp')
            ->select('cp.norec', 'cp.tglregistrasi as tglinput', 'cp.objectnoregistrasifk',
                'ru.namaruangan', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'cp.objectpasienfk',
                'ps.namapasien', 'cp.kdpap', 'pg.namalengkap', 'cp.pegawaifk')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'cp.objectnoregistrasifk')
            ->leftJoin('pasien_m as ps', 'cp.objectpasienfk', '=', 'ps.id')
            ->leftJoin('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'cp.objectruanganfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'cp.pegawaifk')
            ->where('cp.kdprofile', $idProfile)
            ->where('cp.statusenabled', true);

        if (isset($request['NoCM']) && $request['NoCM'] != '') {
            $data = $data->where('ps.nocm', $request['NoCM']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('pd.noregistrasi', $request['noregistrasi']);
        }

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function hapusPengkajianPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            if ($request['norec'] != '') {
                $transStatus = 'true';
                // $cekRekam = RekamMedis::where('riwayatpapfk', $request['norec'])->where('kdprofile', $idProfile)->get();
                // if (count($cekRekam) > 0) {
                //     $transStatus = 'false';
                // } else {
                PengkajianAwalBaru::where('norec', $request['norec'])->where('kdprofile', $idProfile)->update(
                    ['statusenabled' => false]
                );
                // }
            }
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Tidak bisa di hapus, Sudah ada riwayat Pengkajian";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function postMasterDiagnosaKeperawatan(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            if ($method == 'save') {
                if ($request['id'] == '') {
                    $RekamMedis = new DiagnosaKeperawatan();
                    $RekamMedis->id = DiagnosaKeperawatan::max('id') + 1;
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                    $kode = DiagnosaKeperawatan::max('kodeexternal');
                    $sub = substr($kode, 7, 3);
                    $kodeEx = $sub + 1;
                    $kodeEx = substr($kode, 0, 7) . $kodeEx;
                    $RekamMedis->kodeexternal = $kodeEx;
                } else {
                    $RekamMedis = DiagnosaKeperawatan::where('id', $request['id'])->first();
                }
                $RekamMedis->deskripsidiagnosakep = $request['deskripsidiagnosakep'];
                $RekamMedis->reportdisplay = $request['namadiagnosakep'];
                $RekamMedis->namadiagnosakep = $request['namadiagnosakep'];
                $RekamMedis->save();

            }
            if ($method == 'delete') {
                DiagnosaKeperawatan::where('id', $request['id'])->update(
                    ['statusenabled' => false]
                );
            }
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
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveResumeMedis(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new ResumeMedis();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;

                } else {
                    $RekamMedis = ResumeMedis::where('norec', $request['norec'])->first();
                }
                $RekamMedis->tglresume = $request['tglresume'];
                $RekamMedis->diagnosisawal = $request['diagnosis'];
                $RekamMedis->icd = $request['icd'];
                $RekamMedis->jenispemeriksaan = $request['jenispemeriksaan'];
                $RekamMedis->riwayatlalu = $request['riwayatlalu'];
//                $RekamMedis->namadokter = $request['namadokter'];
                $RekamMedis->pegawaifk = $request['pegawaifk'];
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
                $RekamMedis->keteranganlainnya = 'RawatJalan';
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
            }
            if ($method == 'delete') {
                $RekamMedis = ResumeMedis::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'resume' => $RekamMedis,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getResumeMedis(Request $request, $nocm){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('resumemedis_t as rm')
            ->select('rm.norec', 'rm.tglresume', 'ru.namaruangan', 'rm.diagnosisawal as diagnosis', 'rm.icd',
                'rm.jenispemeriksaan', 'rm.riwayatlalu', 'pg.namalengkap as namadokter',
                'rm.pegawaifk',
                'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm',
                'ps.namapasien')
            ->Join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->Join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->Join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.pegawaifk')
            ->where('rm.kdprofile',$idProfile)
            ->where('rm.statusenabled', true)
            ->where('ps.nocm', $nocm)
            ->where('rm.keteranganlainnya', 'RawatJalan');
//            ->whereIn('ru.objectdepartemenfk',$iddept);

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function postResumeMedisInap(Request $request, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $norec = '';
            if ($method == 'save') {
                if ($request['norec'] == '') {
                    $RekamMedis = new ResumeMedis();
                    $RekamMedis->norec = $RekamMedis->generateNewId();
                    $RekamMedis->kdprofile = $idProfile;
                    $RekamMedis->statusenabled = true;
                    $kdResume = $this->generateCode(new ResumeMedis, 'koderesume', 12, 'RI' . $this->getDateTime()->format('ymd'),$idProfile);
                } else {
                    $RekamMedis = ResumeMedis::where('norec', $request['norec'])->where('kdprofile', $idProfile)->first();
                    $kdResume = $RekamMedis->koderesume;
                    ResumeMedisDetail::where('resumefk', $request['norec'])->where('kdprofile', $idProfile)->delete();
                }

                $RekamMedis->tglresume = date('Y-m-d H:i:s');

                $RekamMedis->diagnosismasuk = $request['diagnosismasuk'];
                $RekamMedis->kddiagnosismasuk = $request['kddiagnosismasuk'];
                $RekamMedis->diagnosisawal = $request['diagnosisutama'];
                $RekamMedis->kddiagnosisawal = $request['kddiagnosisutama'];
                $RekamMedis->kddiagnosistambahan = $request['kddiagnosistambahan'];
                $RekamMedis->kddiagnosistambahan2 = $request['kddiagnosistambahan2'];
                $RekamMedis->kddiagnosistambahan3 = $request['kddiagnosistambahan3'];
                $RekamMedis->kddiagnosistambahan4 = $request['kddiagnosistambahan4'];
                $RekamMedis->tindakanprosedur = $request['tindakanprosedur'];
                $RekamMedis->alasandirawat = $request['alasandirawat'];
                $RekamMedis->ringkasanriwayatpenyakit = $request['ringkasanriwayatpenyakit'];
                $RekamMedis->pemeriksaanfisik = $request['pemeriksaanfisik'];
                $RekamMedis->terapi = $request['terapi'];
                $RekamMedis->hasilkonsultasi = $request['hasilkonsultasi'];
                $RekamMedis->kondisiwaktukeluar = $request['kondisiwaktukeluar'];
                $RekamMedis->instruksianjuran = $request['instruksianjuran'];
                $RekamMedis->pengobatandilanjutkan = $request['pengobatandilanjutkan'];
                $RekamMedis->tglkontrolpoli = $request['tglkontrolpoli'];
                $RekamMedis->rumahsakittujuan = $request['rumahsakittujuan'];
                $RekamMedis->pegawaifk = $this->getCurrentUserID();
                $RekamMedis->noregistrasifk = $request['noregistrasifk'];
                $RekamMedis->keteranganlainnya = 'RawatInap';
                $RekamMedis->koderesume = $kdResume;
                $RekamMedis->save();
                $norec = $RekamMedis->norec;
                $norecHead = $RekamMedis->norec;

                foreach ($request['detail'] as $item) {
                    $det = new ResumeMedisDetail();
                    $det->norec = $det->generateNewId();
                    $det->kdprofile = $idProfile;
                    $det->statusenabled = true;
                    $det->noregistrasifk = $request['noregistrasifk'];
                    $det->resumefk = $norecHead;
                    if (isset($item['namaobat'])) {
                        $det->namaobat = $item['namaobat'];
                    }
                    if (isset($item['jumlah'])) {
                        $det->jumlah = $item['jumlah'];
                    }
                    if (isset($item['frekuensi'])) {
                        $det->frekuensi = $item['frekuensi'];
                    }
                    if (isset($item['dosis'])) {
                        $det->dosis = $item['dosis'];
                    }
                    if (isset($item['carapemberian'])) {
                        $det->carapemberian = $item['carapemberian'];
                    }
                    $det->save();
                }
            }
            if ($method == 'delete') {
//                ResumeMedisDetail::where('resumefk', $request['norec'])->delete();

                $RekamMedis = ResumeMedis::where('norec', $request['norec'])->update(
                    ['statusenabled' => false]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'resume' => $RekamMedis,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getResumeMedisInap(Request $request, $nocm){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('resumemedis_t as rm')
            ->select('rm.norec', 'rm.tglresume', 'ru.namaruangan', 'pg.namalengkap as namadokter',
                'rm.ringkasanriwayatpenyakit', 'rm.pemeriksaanfisik', 'rm.pemeriksaanpenunjang',
                'rm.hasilkonsultasi', 'rm.terapi', 'rm.diagnosisawal', 'rm.diagnosissekunder', 'rm.tindakanprosedur',
                // 'rm.diagnosismasuk', 'rm.diagnosistambahan', 'rm.alasandirawat',
                'rm.kddiagnosisawal', 'rm.diagnosismasuk', 'rm.kddiagnosismasuk', 'rm.diagnosistambahan', 'rm.kddiagnosistambahan', 'rm.kddiagnosistambahan2', 'rm.kddiagnosistambahan3', 'rm.kddiagnosistambahan4', 'rm.alasandirawat',
                'dg1.kddiagnosa as kddiagnosa1', 'dg1.namadiagnosa as namadiagnosa1',
                'dg2.kddiagnosa as kddiagnosa2', 'dg2.namadiagnosa as namadiagnosa2',
                'dg3.kddiagnosa as kddiagnosa3', 'dg3.namadiagnosa as namadiagnosa3',
                'dg4.kddiagnosa as kddiagnosa4', 'dg4.namadiagnosa as namadiagnosa4',
                'dg5.kddiagnosa as kddiagnosa5', 'dg5.namadiagnosa as namadiagnosa5',
                'dg6.kddiagnosa as kddiagnosa6', 'dg6.namadiagnosa as namadiagnosa6',
                'rm.tglkontrolpoli', 'rm.rumahsakittujuan',
                'rm.alergi', 'rm.diet', 'rm.instruksianjuran', 'rm.hasillab',
                'rm.kondisiwaktukeluar', 'rm.pengobatandilanjutkan', 'rm.koderesume',
                'rm.pegawaifk',
                'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'rm.noregistrasifk',
                'ps.namapasien')
            ->Join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'rm.noregistrasifk')
            ->Join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->Join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'rm.pegawaifk')
            ->leftJoin('diagnosa_m as dg1', 'dg1.id', '=', 'rm.kddiagnosismasuk')
            ->leftJoin('diagnosa_m as dg2', 'dg2.id', '=', 'rm.kddiagnosisawal')
            ->leftJoin('diagnosa_m as dg3', 'dg3.id', '=', 'rm.kddiagnosistambahan')
            ->leftJoin('diagnosa_m as dg4', 'dg4.id', '=', 'rm.kddiagnosistambahan2')
            ->leftJoin('diagnosa_m as dg5', 'dg5.id', '=', 'rm.kddiagnosistambahan3')
            ->leftJoin('diagnosa_m as dg6', 'dg6.id', '=', 'rm.kddiagnosistambahan4')
            ->where('rm.kdprofile', $idProfile)
            ->where('rm.statusenabled', true)
            ->where('ps.nocm', $nocm)
            ->where('rm.keteranganlainnya', 'RawatInap');
//            ->whereIn('ru.objectdepartemenfk',$iddept);

        $data = $data->get();
        $result = [];
        foreach ($data as $item) {
            $details = DB::select(DB::raw("
                   select * from resumemedisdetail_t
                   where resumefk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $diagnosistambahanarray = array($item->kddiagnosa3, $item->kddiagnosa4, $item->kddiagnosa5, $item->kddiagnosa6);
            $diagnosistambahan = [];
            $no = 0;
            foreach ($diagnosistambahanarray as $tambahan){
                if($tambahan != "") {
                    $diagnosistambahan[$no] = $tambahan;
                    $no++;
                }
            }
            $result[] = array(
                'norec' => $item->norec,
                'tglresume' => $item->tglresume,
                'namaruangan' => $item->namaruangan,
                'namadokter' => $item->namadokter,
                'ringkasanriwayatpenyakit' => $item->ringkasanriwayatpenyakit,
                'pemeriksaanfisik' => $item->pemeriksaanfisik,
                'pemeriksaanpenunjang' => $item->pemeriksaanpenunjang,
                'hasilkonsultasi' => $item->hasilkonsultasi,
                'terapi' => $item->terapi,
                'diagnosismasuk' => $item->diagnosismasuk,
                'kddiagnosismasuk' => array($item->kddiagnosismasuk, $item->kddiagnosa1,  $item->namadiagnosa1),
                'diagnosisawal' => $item->diagnosisawal,
                'kddiagnosisawal' => array($item->kddiagnosisawal, $item->kddiagnosa2,  $item->namadiagnosa2),
                'diagnosistambahan' => $item->diagnosistambahan,
                'kddiagnosistambahan' => array($item->kddiagnosistambahan, $item->kddiagnosa3,  $item->namadiagnosa3),
                'kddiagnosistambahan2' => array($item->kddiagnosistambahan2, $item->kddiagnosa4,  $item->namadiagnosa4),
                'kddiagnosistambahan3' => array($item->kddiagnosistambahan3, $item->kddiagnosa5,  $item->namadiagnosa5),
                'kddiagnosistambahan4' => array($item->kddiagnosistambahan4, $item->kddiagnosa6,  $item->namadiagnosa6),
                'kddiagnosistambahanall' => implode(", ", $diagnosistambahan),
                'diagnosissekunder' => $item->diagnosissekunder,
                'tglkontrolpoli' => $item->tglkontrolpoli,
                'rumahsakittujuan' => $item->rumahsakittujuan,
                'tindakanprosedur' => $item->tindakanprosedur,
                'alasandirawat' => $item->alasandirawat,
                'alergi' => $item->alergi,
                'diet' => $item->diet,
                'instruksianjuran' => $item->instruksianjuran,
                'hasillab' => $item->hasillab,
                'kondisiwaktukeluar' => $item->kondisiwaktukeluar,
                'pengobatandilanjutkan' => $item->pengobatandilanjutkan,
                'koderesume' => $item->koderesume,
                'pegawaifk' => $item->pegawaifk,
                'noregistrasi' => $item->noregistrasi,
                'nocm' => $item->nocm,
                'tglregistrasi' => $item->tglregistrasi,
                'namapasien' => $item->namapasien,
                'noregistrasifk' => $item->noregistrasifk,
                'details' => $details,
            );
        }
        $result = array(
            'data' => $result,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function saveOrderKonsul(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $dataPD = PasienDaftar::where('norec', $request['norec_pd'])->where('kdprofile', $kdProfile)->first();
            if ($request['norec_so'] == "") {
                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $kdProfile;
                $dataSO->statusenabled = true;
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'K' . $this->getDateTime()->format('ym'),$kdProfile);
            } else {
                $dataSO = StrukOrder::where('norec', $request['norec_so'])->where('kdprofile', $kdProfile)->first();
                $noOrder = $dataSO->noorder;
            }
            $dataSO->nocmfk = $dataPD->nocmfk;
            $dataSO->isdelivered = 1;
            $dataSO->noorder = $noOrder;
            $dataSO->noorderintern = $noOrder;
            $dataSO->noregistrasifk = $dataPD->norec;
            $dataSO->objectpegawaiorderfk = $request['pegawaifk'];
            $dataSO->objectpetugasfk = $this->getCurrentUserID();
            $dataSO->qtyjenisproduk = 0;
            $dataSO->qtyproduk = 0;
            $dataSO->objectruanganfk = $request['objectruanganasalfk'];
            $dataSO->objectruangantujuanfk = $request['objectruangantujuanfk'];
            $dataSO->keteranganorder = $request['keterangan'];
            $kelompokTransaksi = KelompokTransaksi::where('kelompoktransaksi', 'KONSULTASI DOKTER')->where('kdprofile', $kdProfile)->first();
            $dataSO->objectkelompoktransaksifk = $kelompokTransaksi->id;
            $dataSO->tglorder = date('Y-m-d H:i:s');
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = 0;
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn = 0;
            $dataSO->save();

            $dataSOnorec = $dataSO->norec;

//            foreach ($request['details'] as $item) {
//                $dataOP = new OrderPelayanan();
//                $dataOP->norec = $dataOP->generateNewId();
//                $dataOP->kdprofile = 0;
//                $dataOP->statusenabled = true;
//                $dataOP->iscito = 0;
//                $dataOP->noorderfk = $dataSOnorec;
//                $dataOP->objectprodukfk = $item['produkfk'];
//                $dataOP->qtyproduk = $item['qtyproduk'];
//                $dataOP->objectkelasfk = $item['objectkelasfk'];
//                $dataOP->qtyprodukretur = 0;
//                $dataOP->objectruanganfk = $request['objectruanganfk'];
//                $dataOP->objectruangantujuanfk = $request['objectruangantujuanfk'];
//                $dataOP->strukorderfk = $dataSOnorec;
//                $dataOP->keteranganlainnya =  'isPemeriksaanKeluar';
//                $dataOP->tglpelayanan = date('Y-m-d H:i:s');
//                $dataOP->strukorderfk = $dataSOnorec;
//                $dataOP->save();
//            }
//
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getOrderKonsul(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kelTrans = KelompokTransaksi::where('kelompoktransaksi', 'KONSULTASI DOKTER')->first();
        $data = \DB::table('strukorder_t as so')
            ->Join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->Join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->leftJoin('ruangan_m as rutuju', 'rutuju.id', '=', 'so.objectruangantujuanfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
            ->leftJoin('pegawai_m as pet', 'pet.id', '=', 'so.objectpetugasfk')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.objectstrukorderfk', '=', 'so.norec')
            ->select('so.norec', 'so.noorder', 'so.tglorder', 'ru.namaruangan as ruanganasal', 'pg.namalengkap',
                'rutuju.namaruangan as ruangantujuan', 'pet.namalengkap as pengonsul',
                'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'so.keteranganorder', 'pd.norec as norec_pd',
                'ps.namapasien', 'pg.id as pegawaifk', 'so.objectruangantujuanfk', 'so.objectruanganfk', 'apd.norec as norec_apd',
                'so.keteranganlainnya')
            ->where('so.kdprofile',$idProfile)
            ->where('so.statusenabled', true)
            ->where('so.objectkelompoktransaksifk', $kelTrans->id)
            ->orderBy('so.tglorder', 'desc');
        if (isset($request['norecpd']) && $request['norecpd'] != '') {
            $data = $data->where('pd.norec', $request['norecpd']);
        }
        if (isset($request['dokterid']) && $request['dokterid'] != '') {
            $data = $data->where('pg.id', $request['dokterid']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('pd.noregistrasi', $request['noregistrasi']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function disabledOrderKonsul(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            $dataSO = StrukOrder::where('norec', $request['norec'])->where('kdprofile', $idProfile)->update(
                ['statusenabled' => false]
            );
            $transStatus = 'true';
        } catch (\Exception $e) {
//            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveKonsulFromOrder(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $pd = PasienDaftar::where('norec', $request['norec_pd'])->first();
            $apd = AntrianPasienDiperiksa::where('noregistrasifk', $request['norec_pd'])->first();
            $dataAPD = new AntrianPasienDiperiksa;
            $dataAPD->norec = $dataAPD->generateNewId();
            $dataAPD->kdprofile = $idProfile;
            $dataAPD->statusenabled = true;
            $dataAPD->objectasalrujukanfk = $apd->objectasalarujukanfk;
            $dataAPD->objectkelasfk = $request['kelasfk'];
            $dataAPD->noantrian = $request['noantrian'];
            $dataAPD->noregistrasifk = $request['norec_pd'];
            $dataAPD->objectpegawaifk = $request['dokterfk'];
            $dataAPD->objectruanganfk = $request['objectruangantujuanfk'];
            $dataAPD->statusantrian = 0;
            $dataAPD->statuspasien = 1;
            $dataAPD->statuskunjungan = 'LAMA';
            $dataAPD->statuspenyakit = 'BARU';
            $dataAPD->objectruanganasalfk = $request['objectruanganasalfk'];;
            $dataAPD->tglregistrasi = $pd->tglregistrasi;//date('Y-m-d H:i:s');
            $dataAPD->tglkeluar = date('Y-m-d H:i:s');
            $dataAPD->tglmasuk = date('Y-m-d H:i:s');
            $dataAPD->objectstrukorderfk = $request['norec_so'];
            $dataAPD->save();

            $dataAPDnorec = $dataAPD->norec;
            $transStatus = 'true';
            $transMessage = "simpan AntrianPasienDiperiksa";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataAPD,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRekamMedisAtuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataRaw = \DB::table('emrd_t as emrd')
            ->where('emrd.emrfk', $request['emrid'])
            ->where('emrd.statusenabled', '=', true)
            ->where('emrd.kdprofile',$idProfile)
            ->select('emrd.*')
            ->orderBy('emrd.nourut');
        $dataRaw = $dataRaw->get();
        $dataTitle = \DB::table('emr_t as emr')
            ->where('emr.id', $request['emrid'])
            ->where('emr.kdprofile',$idProfile)
            ->select('emr.caption as captionemr', 'emr.classgrid','emr.namaemr')
            ->get();
        $title = $dataTitle[0]->captionemr;
        $classgrid = $dataTitle[0]->classgrid;
        $namaemr = $dataTitle[0]->namaemr;

        $dataraw3A = [];
        $dataraw3B = [];
        $dataraw3C = [];
        $dataraw3D = [];
        foreach ($dataRaw as $dataRaw2) {
            $head = '';
            if ($dataRaw2->kolom == 1) {
                $dataraw3A[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                );
            } elseif ($dataRaw2->kolom == 2) {
                $dataraw3B[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                );
            } elseif ($dataRaw2->kolom == 3) {
                $dataraw3C[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                );
            } else {
                $dataraw3D[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                );
            }

//            $title = $dataRaw2->captionemr;
//            $classgrid = $dataRaw2->classgrid;
        }
//        $dataA = $dataraw3A;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }


        $dataA = [];
        $dataB = [];
        $dataC = [];
        $dataD = [];
        if (count($dataraw3A) > 0) {
            $dataA = recursiveElements($dataraw3A);
        }
        if (count($dataraw3B) > 0) {
            $dataB = recursiveElements($dataraw3B);
        }
        if (count($dataraw3C) > 0) {
            $dataC = recursiveElements($dataraw3C);
        }
        if (count($dataraw3D) > 0) {
            $dataD = recursiveElements($dataraw3D);
        }


        $result = array(
            'kolom1' => $dataA,
            'kolom2' => $dataB,
            'kolom3' => $dataC,
            'kolom4' => $dataD,
            'title' => $title,
            'classgrid' => $classgrid,
            'namaemr' =>$namaemr,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getMenuRekamMedisAtuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataRaw = \DB::table('emr_t as emr')
            ->where('emr.kdprofile',$idProfile)
            ->where('emr.statusenabled', true)
            ->where('emr.namaemr', $request['namaemr'])
            ->select('emr.*')
            ->orderBy('emr.nourut');
        $dataRaw = $dataRaw->get();
        $dataraw3 = [];
        foreach ($dataRaw as $dataRaw2) {
            $dataraw3[] = array(
                'id' => $dataRaw2->id,
                'kdprofile' => $dataRaw2->kdprofile,
                'statusenabled' => $dataRaw2->statusenabled,
                'kodeexternal' => $dataRaw2->kodeexternal,
                'namaexternal' => $dataRaw2->namaexternal,
                'reportdisplay' => $dataRaw2->reportdisplay,
                'namaemr' => $dataRaw2->namaemr,
                'caption' => $dataRaw2->caption,
                'headfk' => $dataRaw2->headfk,
                'nourut' => $dataRaw2->nourut
            );
        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }

        $data = recursiveElements($data);

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function SaveTransaksiEMR(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();

        $head = $dataReq['head'];
        $data = $dataReq['data'];

//        foreach ($data as $itm){
//            $dtdt[] = $itm;
//        }
//
//        $keys = array_keys($data);

//        return $this->respond($noemr);
        try {


            if ($head['norec_emr'] == '-') {
                $noemr = $this->generateCodeBySeqTable(new EMRPasien, 'noemr', 15, 'MR' . date('ym') . '/',$idProfile);

                if ($noemr == '') {
                    $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $transMessage,
                        "as" => 'as@epic',
                    );
                    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                }

//                $noemr = $this->generateCode(new EMRPasien, 'noemr', 12, 'MR' . $this->getDateTime()->format('ym') . '/');
//                $noemr = $this->generateCode(new EMRPasien, 'noemr', 14, 'MR' . $this->getDateTime()->format('ym') . '/');

                $EMR = new EMRPasien();
                $norecHead = $EMR->generateNewId();
                $EMR->norec = $norecHead;
                $norecTehMenikitunyaeuy = $norecHead;
                $EMR->norec = $norecTehMenikitunyaeuy;
                $EMR->kdprofile = $idProfile;
                $EMR->statusenabled = 1;

            } else {
                $EMR = EMRPasien::where('noemr', $head['norec_emr'])->where('kdprofile', $idProfile)->first();
                $noemr = $EMR->noemr;
            }
            $EMR->noemr = $noemr;
            $EMR->emrfk = $head['emrfk'];
            if (isset($head['norec_pd'])) {
                $EMR->noregistrasifk = $head['norec_pd'];
            }
            $EMR->nocm = $head['nocm'];
            $EMR->namapasien = $head['namapasien'];
            $EMR->jeniskelamin = $head['jeniskelamin'];
            if (isset($head['noregistrasi'])) {
                $EMR->noregistrasifk = $head['noregistrasi'];
            }
            $EMR->umur = $head['umur'];
            if (isset($head['kelompokpasien'])) {
                $EMR->kelompokpasien = $head['kelompokpasien'];
            }
            if (isset($head['tglregistrasi'])) {
                $EMR->tglregistrasi = $head['tglregistrasi'];
            }
            if (isset($head['norec'])) {
                $EMR->norec_apd = $head['norec'];
            }
            if (isset($head['namakelas'])) {
                $EMR->namakelas = $head['namakelas'];
            }
            if (isset($head['namaruangan'])) {
                $EMR->namaruangan = $head['namaruangan'];
            } else {
                $EMR->namaruangan = "Triage Gawat Darurat";
            }
            if (isset($head['tgllahir'])) {
                $EMR->tgllahir = $head['tgllahir'];
            }
            if (isset($head['notelepon'])) {
                $EMR->notelepon = $head['notelepon'];
            }
            if (isset($head['alamatlengkap'])) {
                $EMR->alamat = $head['alamatlengkap'];
            }
            if (isset($head['jenisemr'])) {
                $EMR->jenisemr = $head['jenisemr'];
            }
            $EMR->pegawaifk = $this->getCurrentUserID();
            $EMR->tglemr = $this->getDateTime()->format('Y-m-d H:i:s');
            $EMR->save();

            $norec_EMR = $EMR->noemr;

            $EMRDelete = EMRPasienD::where('emrpasienfk', $norec_EMR)
                ->where('emrfk', $head['emrfk'])
                ->update([
                    'statusenabled' => false
                ]);
            $i = 0;
//            foreach ($keys as $ky) {
//                $emrdfk = $ky;
//                $valueemr = $dtdt[$i];
            foreach ($data as $item) {
                $emrdfk = $item['id'];
                if (is_array($item['values'])) {
                    $valueemr = $item['values']['value'] . '~' . $item['values']['text'];
                } else {
                    $valueemr = $item['values'];
                }

                $EMRD = new EMRPasienD();
                $norecD = $EMRD->generateNewId();
                $EMRD->norec = $norecD;
                $EMRD->kdprofile = $idProfile;
                $EMRD->statusenabled = 1;

                $EMRD->emrpasienfk = $norec_EMR;
                $EMRD->value = $valueemr;
                $EMRD->emrdfk = $emrdfk;
                $EMRD->emrfk = $head['emrfk'];
                $EMRD->pegawaifk = $this->getCurrentUserID();
                $EMRD->tgl = $this->getDateTime()->format('Y-m-d H:i:s');
                $EMRD->save();
                $i = $i + 1;
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Saving EMR Pasien ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $EMR,
                "data2" => $EMRD,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $data,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getEMRTransaksiRiwayat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : Riwayat
        $data = \DB::table('emrpasien_t as emrp')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'emrp.pegawaifk')
            ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'emrp.dokterfk')
            ->leftJoin('pegawai_m as pg3', 'pg3.id', '=', 'emrp.notifikasifk')
            ->select('emrp.*', 'emrp.noregistrasifk as noregistrasi', 'pg.namalengkap', 'pg2.namalengkap as dokter', 'pg3.namalengkap as notifikasi')
            ->where('emrp.kdprofile', $idProfile)
            ->where('emrp.statusenabled', true)
            ->orderBy('emrp.tglemr', 'desc');
        if (isset($request['norec_pd']) && $request['norec_pd'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['norec_pd']);
        }
        if (isset($request['norec_apd']) && $request['norec_apd'] != '') {
            $data = $data->where('emrp.norec_apd', $request['norec_apd']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('emrp.nocm', $request['nocm']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['noregistrasi']);
        }
        if (isset($request['tgllahir']) && $request['tgllahir'] != "" && $request['tgllahir'] != "undefined" && $request['tgllahir'] != "null") {
            $tgllahir = $request['tgllahir'];
            $data = $data->whereRaw("to_char( emrp.tgllahir, 'yyyy-MM-dd')  ='$tgllahir' ");
        }
        if (isset($request['namapasien']) && $request['namapasien'] != '') {
            $data = $data->where('emrp.namapasien', $request['namapasien']);
        }
        if (isset($request['jenisEmr']) && $request['jenisEmr'] != '') {
            $data = $data->where('emrp.jenisemr', 'ilike', '%' . $request['jenisEmr'] . '%');
        } else {
            $data = $data->whereNull('emrp.jenisemr');
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getEMRTransaksiDetail(Request $request)
    {
        //todo : detail
        $kdProfile = $this->getDataKdProfile($request);
        // $data = \DB::table('emrpasiend_t as emrdp')
        //    ->join('emrpasien_t as emrp', 'emrp.noemr', '=', 'emrdp.emrpasienfk')
        //     ->leftjoin('emrd_t as emrd', 'emrd.id', '=', 'emrdp.emrdfk')
        //     // ->JOIN('emrd_t as emrd',function ($join){
        //         // $join->on('emrd.id','=','emrdp.emrdfk');
        //         // $join->on('emrd.emrfk','=','emrdp.emrfk');
        //     // })
        //     ->leftjoin('pasien_m as ps', 'ps.nocm', '=', 'emrp.nocm')
        //     ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'emrdp.pegawaifk')
        //     //->leftjoin('emrfoto_t as ef', 'ef.noemrpasienfk', '=', 'emrp.noemr')
        //     ->select('emrdp.*', 'emrd.caption', 'emrd.type', 'emrd.nourut', 'emrdp.emrfk', 'emrd.reportdisplay', 'emrd.kodeexternal as kodeex', 'emrd.satuan', 'pg.namalengkap')
        //     ->where('emrdp.statusenabled', true)
        //     ->where('emrdp.kdprofile',$kdProfile )
        //     ->orderBy('emrd.nourut');

        $data = \DB::table('emrpasiend_t as emrdp')
            ->leftjoin('emrd_t as emrd', 'emrd.id', '=', 'emrdp.emrdfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'emrdp.pegawaifk')
            ->select('emrdp.*', 'emrd.caption', 'emrd.type', 'emrd.nourut', 'emrdp.emrfk', 'emrd.reportdisplay', 'emrd.kodeexternal as kodeex', 'emrd.satuan', 'pg.namalengkap')
            ->where('emrdp.statusenabled', true)
            ->where('emrdp.kdprofile',$kdProfile )
            ->whereNotNull('emrdp.value')
            ->where('emrdp.value','!=','Invalid date')
            ->orderBy('emrd.nourut');
        if (isset($request['noemr']) && $request['noemr'] != '') {
            $data = $data->where('emrdp.emrpasienfk', $request['noemr']);
        }
        if (isset($request['emrfk']) && $request['emrfk'] != '') {
            $data = $data->where('emrdp.emrfk', $request['emrfk']);
        }
//        if (isset($request['norec']) && $request['norec'] != '') {
//            $data = $data->where('emrp.norec', $request['norec']);
//        }
        // if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
        //     $data = $data->where('emrp.noregistrasifk', $request['noregistrasi']);
        // }
        if (isset($request['objectid']) && $request['objectid'] != '') {
            $data = $data->where('emrdp.emrdfk', $request['objectid']);
        }
        if (isset($request['nik']) && $request['nik'] != '') {
            // $data = $data->where('ps.noidentitas', $request['nik']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDataComboPegawaiPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $dataProduk = \DB::table('pegawai_m')
            ->select('id as value', 'namalengkap as text')
            ->where('statusenabled', true)
            ->where('kdprofile',$idProfile)
            ->orderBy('namalengkap');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('namalengkap', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboDokterPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $dataProduk = \DB::table('pegawai_m')
            ->select('id as value', 'namalengkap as text')
            ->where('kdprofile',$idProfile)
            ->where('statusenabled', true)
            ->where('objectjenispegawaifk', 1)
            ->orderBy('namalengkap');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('namalengkap', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboRuanganPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $dataProduk = \DB::table('ruangan_m')
            ->select('id as value', 'namaruangan as text')
            ->where('kdprofile',$idProfile)
            ->where('statusenabled', true)
            ->orderBy('namaruangan');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('namaruangan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboRuanganPelayananPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $dataProduk = \DB::table('ruangan_m')
            ->select('id as value', 'namaruangan as text')
            ->where('kdprofile',$idProfile)
            ->where('statusenabled', true)
            ->wherein('objectdepartemenfk', array(25, 27, 24, 3, 17, 35, 26, 28, 16, 18))
            ->orderBy('objectdepartemenfk');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('namaruangan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboDiagnosaPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $data = \DB::table('diagnosa_m')
            ->select('id as value', 'kddiagnosa', 'namadiagnosa as text')
            ->where('kdprofile',$idProfile)
            ->where('statusenabled', true)
            ->orderBy('kddiagnosa');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $data = $data->where('kddiagnosa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
            $data = $data->orwhere('namadiagnosa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $data = $data->take(10);
        $data = $data->get();

        $dt = [];
        foreach ($data as $item) {
            $dt[] = array(
                'value' => $item->value,
                'text' => $item->kddiagnosa . ' ' . $item->text,
            );
        }

        return $this->respond($dt);
    }

    public function getDataComboTindakanPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $data = \DB::table('produk_m as pr')
            ->join('mapruangantoproduk_m as map', 'pr.id', '=', 'map.objectprodukfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'map.objectruanganfk')
            ->select('pr.id as value', 'pr.namaproduk as text')
            ->where('pr.kdprofile',$idProfile)
            ->where('pr.statusenabled', true)
            ->where('ru.objectdepartemenfk', 25)
            ->orderBy('pr.namaproduk');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $data = $data->where('pr.namaproduk', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getDataComboJKPart(Request $request){
//        $req=$request->all();
//        $data  = \DB::table('produk_m as pr')
//            ->select('pr.id as value','pr.namaproduk as text')
//            ->where('pr.statusenabled',true)
//            ->where('ru.objectdepartemenfk',25)
//            ->orderBy('pr.namaproduk');
//        if(isset($req['filter']['filters'][0]['value']) &&
//            $req['filter']['filters'][0]['value']!="" &&
//            $req['filter']['filters'][0]['value']!="undefined"){
//            $data = $data->where('pr.namaproduk','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
//        };
//        $data = $data->take(10);
//        $data = $data->get();
        $data[] = array(
            'values' => 1,
            'text' => 'Laki-laki'
        );
        $data[] = array(
            'values' => 2,
            'text' => 'Perempuan'
        );


        return $this->respond($data);
    }
//Select epd.* from emrpasien_t as ep
//INNER JOIN emrpasiend_t as epd on epd.emrpasienfk =ep.noemr;
//
//select * from emrpasien_t;
    public function getDataDiagnosis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noRegistrasi = $request['NorRegistrasi'];
        $dataDiagnosa = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'apd.norec')
            ->join('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->join('diagnosa_m as d', 'd.id', '=', 'ddp.objectdiagnosafk')
            ->select('pd.noregistrasi', 'd.kddiagnosa', 'd.namadiagnosa')
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.noregistrasi', $noRegistrasi)
            ->orderBy('d.kddiagnosa')
            ->get();

        $dataDiagnosa1 = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('diagnosatindakanpasien_t as dtp', 'dtp.objectpasienfk', '=', 'apd.norec')
            ->join('detaildiagnosatindakanpasien_t as ddtp', 'ddtp.objectdiagnosatindakanpasienfk', '=', 'dtp.norec')
            ->join('diagnosatindakan_m as dt', 'dt.id', '=', 'ddtp.objectdiagnosatindakanfk')
            ->select('pd.noregistrasi', 'dt.kddiagnosatindakan', 'dt.namadiagnosatindakan')
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.noregistrasi', $noRegistrasi)
            ->orderBy('dt.kddiagnosatindakan')
            ->get();

        $result = array(
            'icd10' => $dataDiagnosa,
            'icd9' => $dataDiagnosa1,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDataUserResumeMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noRegistrasi = $request['NorRegistrasi'];
        $data = \DB::table('resumemedis_t as rm ')
            ->leftjoin('logginguser_t as lg', 'lg.noreff', '=', 'rm.norec')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'lg.objectloginuserfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'lu.objectpegawaifk')
            ->select('rm.tglresume', 'pg.id as pegawaifk', 'lu.id as userid', 'lu.namauser', 'pg.namalengkap')
            ->where('rm.norec', $noRegistrasi)
            ->get();

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function SaveTransaksiEMROdontogram(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();
        $head = $dataReq['head'];
        $data = $dataReq['data'];
//        emrodontogram_t

        try {

            $EMR = EMROdontogram::where('nocm', $head['nocm'])->delete();

            foreach ($data as $item) {
//                if ($head['norec_emr'] == '-') {
                $noemr = $this->generateCode(new EMROdontogram, 'noemr', 10, 'DG' . $this->getDateTime()->format('ym') . '/',$idProfile);
                $EMR = new EMROdontogram();
                $norecHead = $EMR->generateNewId();
                $EMR->norec = $norecHead;
                $norecTehMenikitunyaeuy = $norecHead;
                $EMR->norec = $norecTehMenikitunyaeuy;
                $EMR->kdprofile = $idProfile;
                $EMR->statusenabled = 1;
                $EMR->noemr = $noemr;
//                } else {

                $EMR->noregistrasifk = $head['norec_pd'];
                $EMR->nocm = $head['nocm'];
                $EMR->namapasien = $head['namapasien'];
                $EMR->jeniskelamin = $head['jeniskelamin'];
                $EMR->noregistrasi = $head['noregistrasi'];
                $EMR->umur = $head['umur'];
                $EMR->kelompokpasien = $head['kelompokpasien'];
                $tglregistrasi = date('Y-m-d H:i:s', strtotime($head['tglregistrasi']));//;
                $EMR->tglregistrasi = $tglregistrasi;
                $EMR->norec_apd = $head['norec'];
                $EMR->namakelas = $head['namakelas'];
                $EMR->namaruangan = $head['namaruangan'];
                $EMR->tglemr = $this->getDateTime()->format('Y-m-d H:i:s');

                $EMR->colour = $item['colour'];
                $EMR->width = $item['width'];
                $EMR->height = $item['height'];
                $EMR->top = $item['top'];
                $EMR->left = $item['left'];
                $EMR->id = $item['id'];
                $EMR->brs = $item['brs'];
                $EMR->kol = $item['kol'];
                $EMR->seg = $item['seg'];
                $EMR->type = $item['type'];
                $EMR->color = $item['color'];
                $EMR->save();
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Saving EMR Odontogram Pasien ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $EMR,
//                "data2" => $EMRD,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $data,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataOdontogram(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $noRegistrasi = $request['noregistrasi'];
        $data = \DB::table('emrodontogram_t as emr')
            ->select('emr.colour', 'emr.width', 'emr.height', 'emr.top', 'emr.left', 'emr.id',
                'emr.brs', 'emr.kol', 'emr.seg', 'emr.type', 'emr.color')
            ->where('emr.kdprofile',$idProfile)
            ->where('emr.nocm', $request['nocm'])
            ->get();

        foreach ($data as $itm) {
            $dt2[] = array(
                'colour' => $itm->colour,
                'width' => (int)$itm->width,
                'height' => (int)$itm->height,
                'top' => (int)$itm->top,
                'left' => (int)$itm->left,
                'id' => (int)$itm->id,
                'brs' => (int)$itm->brs,
                'kol' => (int)$itm->kol,
                'seg' => (int)$itm->seg,
                'type' => $itm->type,
                'color' => $itm->color
            );
        }

        $result = array(
            'data' => $dt2,
            'message' => 'as@epic',
        );
        return $this->respond($result);
//        return Response::json($result, 200, [], JSON_NUMERIC_CHECK);
//        return response()->json($result)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function getDaftarDokumenRekamMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
//			->leftjoin('kendalidokumenrekammedis_t as ken', 'ken.noregistrasifk', '=', 'pd.norec')
//			->leftjoin('statuskendalidokumen_m as ss', 'ss.id', '=', 'ken.objectstatuskendalidokumenfk')
            ->select('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
                'kp.kelompokpasien', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'br.norec as norec_br', 'pd.nostruklastfk', 'jk.jeniskelamin', 'alm.alamatlengkap',
                'ps.tgllahir')
            ->where('pd.kdprofile',$idProfile)
            ->whereNull('br.norec');
        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $filter['tglAkhir']);
        }
        if (isset($filter['deptId']) && $filter['deptId'] != "" && $filter['deptId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['deptId']);
        }
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruangId']);
        }
        if (isset($filter['kelId']) && $filter['kelId'] != "" && $filter['kelId'] != "undefined") {
            $data = $data->where('kp.id', '=', $filter['kelId']);
        }
        if (isset($filter['dokId']) && $filter['dokId'] != "" && $filter['dokId'] != "undefined") {
            $data = $data->where('pg.id', '=', $filter['dokId']);
        }
        if (isset($filter['sttts']) && $filter['sttts'] != "" && $filter['sttts'] != "undefined") {
            $data = $data->where('pd.statuspasien', '=', $filter['sttts']);
        }
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $filter['noreg']);
        }
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $filter['norm'] . '%');
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $filter['nama'] . '%');
        }
        if (isset($filter['jmlRows']) && $filter['jmlRows'] != "" && $filter['jmlRows'] != "undefined") {
            $data = $data->take($filter['jmlRows']);
        }
        $data = $data->orderBy('pd.noregistrasi');
        $data = $data->groupBy('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
            'kp.kelompokpasien', 'pg.namalengkap ', 'pd.tglpulang', 'pd.statuspasien',
            'pd.objectpegawaifk', 'pd.objectruanganlastfk',
            'br.norec', 'pd.nostruklastfk', 'jk.jeniskelamin', 'alm.alamatlengkap',
            'ps.tgllahir');
        $data = $data->get();
        $datafix = [];
        foreach ($data as $item) {
            $kendali = \DB::table('kendalidokumenrekammedis_t as ken')
                ->join('statuskendalidokumen_m as ss', 'ss.id', '=', 'ken.objectstatuskendalidokumenfk')
                ->where('ken.kdprofile',$idProfile)
                ->select('ken.*', 'ss.name')
                ->where('noregistrasifk', $item->norec);
            if (isset($filter['diterima']) && $filter['diterima'] != "" && $filter['diterima'] != "undefined" && $filter['diterima'] != "false") {
                $kendali = $kendali->where('ss.name', 'ilike', '%terima%');
            }
            if (isset($filter['dikirim']) && $filter['dikirim'] != "" && $filter['dikirim'] != "undefined" && $filter['dikirim'] != "false") {
                $kendali = $kendali->where('ss.name', 'ilike', '%kirim%');
            }
            $kendali = $kendali->get();

            $datafix [] = array(
                'norec' => $item->norec,
                'tglregistrasi' => $item->tglregistrasi,
                'nocm' => $item->nocm,
                'nocmfk' => $item->nocmfk,
                'noregistrasi' => $item->noregistrasi,
                'namaruangan' => $item->namaruangan,
                'namapasien' => $item->namapasien,
                'kelompokpasien' => $item->kelompokpasien,
                'namadokter' => $item->namadokter,
                'tglpulang' => $item->tglpulang,
                'statuspasien' => $item->statuspasien,
                'pgid' => $item->pgid,
                'objectruanganlastfk' => $item->objectruanganlastfk,
                'norec_br' => $item->norec_br,
                'jeniskelamin' => $item->jeniskelamin,
                'alamatlengkap' => $item->alamatlengkap,
                'tgllahir' => $item->tgllahir,
            );


        }

        return $this->respond($datafix);
    }

    public function getComboDokRekMed(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', $idProfile)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('dp.kdprofile', $idProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        foreach ($dataInstalasi as $item) {
            $detail = [];
            foreach ($dataRuangan as $item2) {
                if ($item->id == $item2->objectdepartemenfk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'ruangan' => $item2->namaruangan,
                    );
                }
            }

            $dataDepartemen[] = array(
                'id' => $item->id,
                'departemen' => $item->namadepartemen,
                'ruangan' => $detail,
            );
        }
        $result = array(
            'departemen' => $dataDepartemen,
            'ruangan' => $dataRuangan,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function saveDokumenRekamMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $status = \DB::table('statuskendalidokumen_m')->where('kdprofile',$idProfile)
                ->where('name', 'ilike', '%' . $request['statuskendali'] . '%')->first();
            if ($request['statuskendali'] == 'pinjam') {
                foreach ($request['pasien'] as $item) {
                    if ($request['norec_kdr'] == '') {
                        $newKDR = new KendaliDokumenRekamMedis();
                        $norec = $newKDR->generateNewId();
                        $newKDR->norec = $norec;
                        $newKDR->kdprofile = $idProfile;
                        $newKDR->statusenabled = true;
                    } else {
                        $newKDR = KendaliDokumenRekamMedis::where('norec', $request['norec_kdr'])->first();
                    }
                    $newKDR->objectruanganasalfk = $item['objectruanganlastfk'];
                    $newKDR->objectruangantujuanfk = $item['objectruanganlastfk'];
                    $newKDR->nocmfk = $item['nocmfk'];
                    $newKDR->objectstatuskendalidokumenfk = $status->id;
                    $newKDR->tglupdate = date('Y-m-d H:i:s');
                    if (isset($request['tglkeluar'])) {
                        $newKDR->tglkeluar = $request['tglkeluar'];
                    }
                    if (isset($request['tglkembali'])) {
                        $newKDR->tglkembali = $request['tglkembali'];
                    }
                    $newKDR->catatan = $request['keterangan'];
                    $newKDR->noregistrasifk = $item['norec'];
                    $newKDR->pegawaifk = $request['pegawaifk'];
                    $newKDR->save();
                }
            }
            if ($request['statuskendali'] == 'kirim') {
                foreach ($request['pasien'] as $item) {
                    if ($request['norec_kdr'] == '') {
                        $newKDR = new KendaliDokumenRekamMedis();
                        $norec = $newKDR->generateNewId();
                        $newKDR->norec = $norec;
                        $newKDR->kdprofile = $idProfile;
                        $newKDR->statusenabled = true;
                    } else {
                        $newKDR = KendaliDokumenRekamMedis::where('norec', $request['norec_kdr'])->first();
                    }

                    $newKDR->objectruanganasalfk = $item['objectruanganlastfk'];
                    $newKDR->objectruangantujuanfk = $request['ruangantujuanfk'];
                    $newKDR->nocmfk = $item['nocmfk'];
                    $newKDR->objectstatuskendalidokumenfk = $status->id;
                    $newKDR->tglupdate = date('Y-m-d H:i:s');
                    if (isset($request['tglkeluar'])) {
                        $newKDR->tglkeluar = $request['tglkeluar'];
                    }
                    if (isset($request['tglkembali'])) {
                        $newKDR->tglkembali = $request['tglkembali'];
                    }
                    $newKDR->catatan = $request['keterangan'];
                    $newKDR->noregistrasifk = $item['norec'];
                    $newKDR->pegawaifk = $request['pegawaifk'];
                    $newKDR->save();
                }
            }

            if ($request['statuskendali'] == 'terima') {
                foreach ($request['pasien'] as $item) {
                    if ($request['norec_kdr'] == '') {
                        $newKDR = new KendaliDokumenRekamMedis();
                        $norec = $newKDR->generateNewId();
                        $newKDR->norec = $norec;
                        $newKDR->kdprofile = $idProfile;
                        $newKDR->statusenabled = true;
                    } else {
                        $newKDR = KendaliDokumenRekamMedis::where('norec', $request['norec_kdr'])->first();
                    }

                    $newKDR->objectruanganasalfk = $item['objectruanganlastfk'];
                    $newKDR->objectruangantujuanfk = $request['ruanganterimafk'];
                    $newKDR->nocmfk = $item['nocmfk'];
                    $newKDR->objectstatuskendalidokumenfk = $status->id;
                    $newKDR->tglupdate = date('Y-m-d H:i:s');
                    if (isset($request['tglkeluar'])) {
                        $newKDR->tglkeluar = $request['tglkeluar'];
                    }
                    if (isset($request['tglkembali'])) {
                        $newKDR->tglkembali = $request['tglkembali'];
                    }
                    $newKDR->catatan = $request['keterangan'];
                    $newKDR->noregistrasifk = $item['norec'];
                    $newKDR->pegawaifk = $request['pegawaifk'];
                    $newKDR->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'data' => $newKDR,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'data' => $newKDR,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getHistoriDokumenRekmed(Request $request, $nocm){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('kendalidokumenrekammedis_t as ken')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'ken.noregistrasifk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'ken.objectruanganasalfk')
            ->leftJoin('ruangan_m as ru2', 'ru2.id', '=', 'ken.objectruangantujuanfk')
            ->leftJoin('statuskendalidokumen_m as st', 'st.id', '=', 'ken.objectstatuskendalidokumenfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'ken.pegawaifk')
            ->leftJoin('pasien_m as ps', 'ps.id', '=', 'ken.nocmfk')
            ->select('ken.*', 'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan', 'st.name as status', 'pg.namalengkap',
                'pd.noregistrasi')
            ->where('ken.kdprofile', $idProfile)
            ->where('ken.statusenabled', true)
            ->where('ps.nocm', '=', $nocm)
            ->orderBy('ken.tglupdate', 'desc')
            ->get();


        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getRuanganPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $data = \DB::table('ruangan_m as st')
            ->select('st.id', 'st.namaruangan')
            ->where('st.kdprofile',$idProfile)
            ->where('st.statusenabled', true)
            ->orderBy('st.namaruangan');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $data = $data->where('st.namaruangan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        if (isset($req['namaruangan']) && $req['namaruangan'] != "" && $req['namaruangan'] != "undefined") {
            $data = $data
                ->where('st.namaruangan', 'ilike', '%' . $req['namaruangan'] . '%');
        }
        if (isset($req['idRuangan']) && $req['idRuangan'] != "" && $req['idRuangan'] != "undefined") {
            $data = $data
                ->where('st.id', '=', $req['idRuangan']);
        }
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getDaftarDokumenRekamMedisRuangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftjoin('kendalidokumenrekammedis_t as ken', 'ken.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('ruangan_m as tuju', 'tuju.id', '=', 'ken.objectruangantujuanfk')
            ->leftjoin('ruangan_m as asal', 'asal.id', '=', 'ken.objectruanganasalfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ken.pegawaifk')
            ->leftjoin('pegawai_m as pg2', 'pg2.id', '=', 'ken.penerimafk')
            ->leftjoin('statuskendalidokumen_m as ss', 'ss.id', '=', 'ken.objectstatuskendalidokumenfk')
            ->leftJoin('maploginusertoruangan_s as maps', 'maps.objectruanganfk', '=', 'ken.objectruangantujuanfk')//ambil map login
            ->leftJoin('maploginusertoruangan_s as maps2', 'maps2.objectruanganfk', '=', 'ken.objectruanganasalfk')//ambil map login
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
                'kp.kelompokpasien', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'br.norec as norec_br', 'pd.nostruklastfk', 'jk.jeniskelamin', 'pg2.namalengkap as penerima',
                'ps.tgllahir', 'asal.namaruangan as ruangankirim', 'tuju.namaruangan as ruangantujuan', 'pg.namalengkap as pengirim',
                'ken.tglkembali', 'ken.tglkeluar', 'ken.norec as norec_ken', 'tuju.id as ruangantujuanid', 'asal.id as ruanganasalid')
            ->where('pd.kdprofile',$idProfile)
            ->whereNull('br.norec');
        if (isset($filter['isTglMasuk']) && $filter['isTglMasuk'] != "" && $filter['isTglMasuk'] != "undefined") {
            if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
                $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
            }
            if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
                $data = $data->where('pd.tglregistrasi', '<=', $filter['tglAkhir']);
            }
        }
        if (isset($filter['isTglPulang']) && $filter['isTglPulang'] != "" && $filter['isTglPulang'] != "undefined") {
            if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
                $data = $data->where('pd.tglpulang', '>=', $filter['tglAwal']);
            }
            if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
                $data = $data->where('pd.tglpulang', '<=', $filter['tglAkhir']);
            }
        }
        if (isset($filter['isTglKirim']) && $filter['isTglKirim'] != "" && $filter['isTglKirim'] != "undefined") {
            if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
                $data = $data->where('ken.tglkeluar', '>=', $filter['tglAwal']);
            }
            if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
                $data = $data->where('ken.tglkeluar', '<=', $filter['tglAkhir']);
            }
        }
        if (isset($filter['isTglTerima']) && $filter['isTglTerima'] != "" && $filter['isTglTerima'] != "undefined") {
            if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
                $data = $data->where('ken.tglkembali', '>=', $filter['tglAwal']);
            }
            if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
                $data = $data->where('ken.tglkembali', '<=', $filter['tglAkhir']);
            }
        }

        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruangId']);
        }
        if (isset($filter['ruKirimId']) && $filter['ruKirimId'] != "" && $filter['ruKirimId'] != "undefined") {
            $data = $data->where('ken.objectruanganasalfk', '=', $filter['ruKirimId']);
        }
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $filter['noreg']);
        }
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $filter['norm'] . '%');
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $filter['nama'] . '%');
        }
        if (isset($filter['jmlRows']) && $filter['jmlRows'] != "" && $filter['jmlRows'] != "undefined") {
            $data = $data->take($filter['jmlRows']);
        }
        if (isset($filter['status']) && $filter['status'] != "" && $filter['status'] != "undefined") {
            if ($filter['status'] == '1') {
                $data = $data->wherenull('ken.norec')
                    ->wherenull('maps.norec');
            }
            if ($filter['status'] == '2') {
                $data = $data->where('ss.id', '=', '8')
                    ->where('ss.id', '<>', '9')
                    ->wherenotnull('maps.norec');
            }
            if ($filter['status'] == '3') {
                $data = $data->wherenotnull('maps.norec');
            }
            if ($filter['status'] == '4') {
                $data = $data
                    ->wherenull('ken.tglkembali')
                    ->wherenotnull('maps2.norec');
            }
            if ($filter['status'] == '5') {
                $data = $data->whereNotNull('ken.kembali')
                    ->where('ss.id', '9')
                    ->wherenotnull('maps.norec');
            }
        }

        $data = $data->orderBy('pd.noregistrasi');
        $data = $data->groupBy('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
            'kp.kelompokpasien', 'pg.namalengkap', 'pd.tglpulang', 'pd.statuspasien', 'pg2.namalengkap',
            'pd.objectpegawaifk', 'pd.objectruanganlastfk', 'ken.norec', 'tuju.id', 'asal.id',
            'br.norec', 'pd.nostruklastfk', 'jk.jeniskelamin', 'ken.tglkembali', 'ken.tglkeluar',
            'ps.tgllahir', 'asal.namaruangan', 'tuju.namaruangan', 'pg.namalengkap');
        $data = $data->get();
        $res = array(
            'data' => $data,
        );
        return $this->respond($res);
    }

    public function saveDokumenRekamMedisRuangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $status = \DB::table('statuskendalidokumen_m')
                ->where('name', 'ilike', '%' . $request['statuskendali'] . '%')->first();
            if ($request['statuskendali'] == 'terima') {
                foreach ($request['pasien'] as $item) {
                    if ($item['norec_ken'] == '') {
                        $newKDR = new KendaliDokumenRekamMedis();
                        $norec = $newKDR->generateNewId();
                        $newKDR->norec = $norec;
                        $newKDR->kdprofile = $idProfile;
                        $newKDR->statusenabled = true;
                    } else {
                        $newKDR = KendaliDokumenRekamMedis::where('norec', $item['norec_ken'])->first();
                    }

                    $newKDR->objectruanganasalfk = $item['ruanganasalid'];
                    $newKDR->objectruangantujuanfk = $item['ruangantujuanid'];
                    $newKDR->nocmfk = $item['nocmfk'];
                    $newKDR->objectstatuskendalidokumenfk = $status->id;
                    $newKDR->tglupdate = date('Y-m-d H:i:s');
                    //                    if(isset($request['tglkeluar'])){
                    //                        $newKDR->tglkeluar = $request['tglkeluar'];
                    //                    }
                    //                    if(isset($request['tglkembali'])){
                    $newKDR->tglkembali = date('Y-m-d H:i:s');
                    //                    }
                    $newKDR->catatan = $request['keterangan'];
                    $newKDR->noregistrasifk = $item['norec'];
                    $newKDR->penerimafk = $request['pegawaifk'];
                    $newKDR->save();
                }
            }
            if ($request['statuskendali'] == 'kirim') {
                foreach ($request['pasien'] as $item) {
                    if ($item['norec_ken'] == '') {
                        $newKDR = new KendaliDokumenRekamMedis();
                        $norec = $newKDR->generateNewId();
                        $newKDR->norec = $norec;
                        $newKDR->kdprofile = $idProfile;
                        $newKDR->statusenabled = true;
                    } else {
                        $newKDR = KendaliDokumenRekamMedis::where('norec', $item['norec_ken'])->first();
                    }

                    $newKDR->objectruanganasalfk = $item['ruanganasalid'];
                    $newKDR->objectruangantujuanfk = $request['ruangantujuanfk'];
                    $newKDR->nocmfk = $item['nocmfk'];
                    $newKDR->objectstatuskendalidokumenfk = $status->id;
                    $newKDR->tglupdate = date('Y-m-d H:i:s');
//                    if(isset($request['tglkeluar'])){
//                        $newKDR->tglkeluar = $request['tglkeluar'];
//                    }
//                    if(isset($request['tglkembali'])){
                    $newKDR->tglkembali = date('Y-m-d H:i:s');
//                    }
                    $newKDR->catatan = $request['keterangan'];
                    $newKDR->noregistrasifk = $item['norec'];
                    $newKDR->penerimafk = $request['pegawaifk'];
                    $newKDR->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'data' => $newKDR,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'data' => $newKDR,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPengakajianICU(Request $request){
        $data = \DB::table('glasgowcdmascale_m as emr')
            ->select('emr.id', 'emr.reportdisplay', 'emr.namagcs')
            ->where('emr.statusenabled', true)
            ->get();

        $data1 = \DB::table('infus_m as emr')
            ->select('emr.id', 'emr.namainfus')
            ->where('emr.statusenabled', true)
            ->get();

        $data2 = \DB::table('jenisvent_m as emr')
            ->select('emr.id', 'emr.namajenisvent')
            ->where('emr.statusenabled', true)
            ->get();

        $result = array(
            'gcs' => $data,
            'infus' => $data1,
            'jenisvent' => $data2,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataComboERM(Request $request){
        $viTals = $request['vitalSigns'];
        $VitalSigns = \DB::table('vitalsigns_m as emr')
            ->select('emr.id', 'emr.vitalsigns')
            ->where('emr.statusenabled', true)
            ->get();

        $VitalSignsSearch = \DB::table('vitalsigns_m as emr')
            ->select('emr.id', 'emr.vitalsigns')
            ->where('emr.statusenabled', true)
            ->where('emr.vitalsigns', 'ilike', '%' . $viTals . '%')
            ->get();

        $KodeGambar = \DB::table('kodegambar_m as kg')
            ->select('kg.id', 'kg.reportdisplay as group', 'kg.kodegambar')
            ->where('kg.statusenabled', true)
            ->orderBy('kg.id', 'asc')
            ->get();

        $result = array(
            'vitalsigns' => $VitalSigns,
            'searchvitalsigns' => $VitalSignsSearch,
            'kodegambar' => $KodeGambar,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function SaveTransaksiEMRICU(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id', $dataLogin['userData']['id'])
            ->first();
        $dataReq = $request->all();
        $dataDetail = $dataReq['details'];
        $norecEMR = "";

        try {
            if ($dataReq['norec'] == '') {
                $dataEMRICU = new RekamMedisICU();
                $dataEMRICU->kdprofile = $idProfile;
                $dataEMRICU->statusenabled = 1;
                $dataEMRICU->norec = $dataEMRICU->generateNewId();
            } else {
                $dataEMRICU = RekamMedisICU::where('norec', $dataReq['norec'])->first();
                $dataEMRICU_D = RekamMedisICUDetail::where('emricufk', $dataReq['norec'])
                    ->delete();
            }
            $dataEMRICU->noregistrasifk = $dataReq['norec_apd'];
            $dataEMRICU->tglemr = $dataReq['tglinput'];
            $dataEMRICU->objectpegawaifk = $dataPegawai->objectpegawaifk;
            $dataEMRICU->save();
            $norecEMR = $dataEMRICU->norec;

            foreach ($dataDetail as $item) {
                $dataEMRICU_D = new RekamMedisICUDetail();
                $dataEMRICU_D->norec = $dataEMRICU_D->generateNewId();
                $dataEMRICU_D->kdprofile = $idProfile;
                $dataEMRICU_D->statusenabled = 1;
                $dataEMRICU_D->emricufk = $norecEMR;
                $dataEMRICU_D->nilaiobservasi = $item['keteranganobservasi'];
                $dataEMRICU_D->group = $item['group'];
                $dataEMRICU_D->field = $item['field'];
                $dataEMRICU_D->column = $item['desc'];
                $dataEMRICU_D->jam = $item['column'];
                $dataEMRICU_D->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId + 1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile = $idProfile;
            $logUser->statusenabled = 1;
            $logUser->jenislog = 'Simpan EMR ICU';
            $logUser->noreff = $request['data']['norec'];
            $logUser->referensi = 'norec EMR ICU';
            $logUser->objectloginuserfk = $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = 'Saving EMR ICU';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                'message' => $transMessage,
                "norec_emr" => $norecEMR,
                "data_emr" => $dataEMRICU,
                "data_emr_d" => $dataEMRICU_D,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                'message' => $transMessage,
                "norec_emr" => $norecEMR,
                "data_emr" => $dataEMRICU,
                "data_emr_d" => $dataEMRICU_D,
                "as" => 'ea@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataRiwayatERMICU(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noregPasien = $request['NoRegistrasi'];
        $data_Riwayat = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('rekammedisicu_t as rmi', 'rmi.noregistrasifk', '=', 'apd.norec')
            ->join('pegawai_m as pg', 'pg.id', '=', 'rmi.objectpegawaifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->select(DB::raw("pd.norec as norec_pd,rmi.norec,rmi.tglemr,apd.objectruanganfk,ru.namaruangan,
			                  rmi.objectpegawaifk,pg.namalengkap"))
            ->where('pd.kdprofile',$idProfile)
            ->where('pd.norec', $noregPasien)
            ->where('rmi.statusenabled', true)
            ->get();

        $result = array(
            'data_riwayat' => $data_Riwayat,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataDetailEMRICU(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $norcRMI = $request['NorecRmi'];
        $data = \DB::table('rekammedisicu_t as rmi')
            ->join('rekammedisicud_t as rmid', 'rmid.emricufk', '=', 'rmi.norec')
            ->select(DB::raw("rmid.*"))
            ->where('rmi.kdprofile', $idProfile)
            ->where('rmi.norec', $norcRMI)
            ->where('rmi.statusenabled', true)
            ->get();

        $result = array(
            'Details' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataRiwayatERMICUDetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noregPasien = $request['NoRegistrasi'];
        $data_Riwayat = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('rekammedisicu_t as rmi', 'rmi.noregistrasifk', '=', 'apd.norec')
            ->join('rekammedisicud_t as rmid', 'rmid.emricufk', '=', 'rmi.norec')
            ->join('pegawai_m as pg', 'pg.id', '=', 'rmi.objectpegawaifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('vitalsigns_m as vs', 'vs.id', '=', 'rmid.objectvitalsigns')
            ->select(DB::raw("rmid.*,vs.vitalsigns"))
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.norec', $noregPasien)
            ->where('rmi.statusenabled', true)
            ->get();

        $result = array(
            'data_riwayat' => $data_Riwayat,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataPasien(Request $request, $noCmFk){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as pm')
            ->leftJoin('jeniskelamin_m as jk', 'jk.id', '=', 'pm.objectjeniskelaminfk')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'pm.id')
            ->select(DB::raw("getDataRiwayatEMR"))
            ->where('pd.kdprofile', $idProfile)
            ->where('pm.nocm', $noCmFk);

        $data = $data->first();
        $result = array(
            'result' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataPasienNew(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as pm')
            ->leftJoin('jeniskelamin_m as jk', 'jk.id', '=', 'pm.objectjeniskelaminfk')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'pm.id')
            ->select(DB::raw("pm.id as nocmfk,pm.nocm,pm.namapasien,pm.tgllahir,
			                 pm.objectjeniskelaminfk,jk.jeniskelamin,alm.alamatlengkap,pm.notelepon"))
            ->where('pm.kdprofile', $idProfile)
            ->where('pm.statusenabled', true);

        if (isset($request['idPasien']) && $request['idPasien'] != "" && $request['idPasien'] != "undefined") {
            $data = $data->where('pm.id', '=', $request['idPasien']);
        }
        if (isset($request['noCmPasien']) && $request['noCmPasien'] != "" && $request['noCmPasien'] != "undefined") {
            $data = $data->where('pm.nocm', '=', $request['noCmPasien']);
        }
        $data = $data->first();
        $result = array(
            'result' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataComboAsalRujukan(Request $request){
        $req = $request->all();
        $dataProduk = \DB::table('asalrujukan_m')
            ->select('id as value', 'asalrujukan as text')
            ->where('statusenabled', true)
            ->orderBy('id');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('asalrujukan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboKelompokpPasien(Request $request){
        $req = $request->all();
        $dataProduk = \DB::table('kelompokpasien_m')
            ->select('id as value', 'kelompokpasien as text')
            ->where('statusenabled', true)
            ->orderBy('id');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('kelompokpasien', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();
        return $this->respond($dataProduk);
    }

    public function saveRekamMedisPemeriksaanFisik(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id', $dataLogin['userData']['id'])
            ->first();
        $dataReq = $request->all();
        $norecEMR = "";

        try {

            if ($dataReq['norec'] == '') {
                $dataEMR = new RmPemeriksaanFisik();
                $dataEMR->kdprofile = $idProfile;
                $dataEMR->statusenabled = 1;
                $dataEMR->norec = $dataEMR->generateNewId();
                $dataEMR->nocm = $dataReq['nocm'];
            } else {
                $dataEMR = RmPemeriksaanFisik::where('norec', $dataReq['norec'])->first();
            }
            $dataEMR->gcs = $dataReq['gcs'];
            $dataEMR->eyes = $dataReq['eyes'];
            $dataEMR->verbal = $dataReq['verbal'];
            $dataEMR->motoric = $dataReq['motoric'];
            $dataEMR->total = $dataReq['total'];
            $dataEMR->kepalawajah = $dataReq['kepalawajah'];
            $dataEMR->leher = $dataReq['leher'];
            $dataEMR->thoraksinpeksi = $dataReq['thoraksinpeksi'];
            $dataEMR->thorakspalpasi = $dataReq['thorakspalpasi'];
            $dataEMR->thoraksperkusi = $dataReq['thoraksperkusi'];
            $dataEMR->thoraksauskultasi = $dataReq['thoraksauskultasi'];
            $dataEMR->abdomeninpeksi = $dataReq['abdomeninpeksi'];
            $dataEMR->abdomenauskultasi = $dataReq['abdomenauskultasi'];
            $dataEMR->abdomenpalpasi = $dataReq['abdomenpalpasi'];
            $dataEMR->abdomenperkusi = $dataReq['abdomenperkusi'];
            $dataEMR->anogenital = $dataReq['anogenital'];
            $dataEMR->ekstremitas = $dataReq['ekstremitas'];
            $dataEMR->neurologis = $dataReq['neurologis'];
            $dataEMR->tglemr = $dataReq['tglemr'];
            $dataEMR->objectpegawaifk = $dataPegawai->objectpegawaifk;
            $dataEMR->save();
            $norecEMR = $dataEMR->norec;

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId + 1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile = $idProfile;
            $logUser->statusenabled = true;
            $logUser->jenislog = 'Input Rekam Medis IGD Pemeriksaan fisik dengan nocm ' . $dataReq['nocm'];
            $logUser->noreff = $norecEMR;
            $logUser->referensi = 'norec rmpemeriksaanfisik';
            $logUser->objectloginuserfk = $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Data Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $norecEMR,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Data Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'norec' => $norecEMR,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataRiwayatERMPemeriksaanFisik(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noregPasien = $request['noCm'];
        $data_Riwayat = RmPemeriksaanFisik::where('nocm', $noregPasien)->where('kdprofile', $idProfile)->get();
        $result = array(
            'data_riwayat' => $data_Riwayat,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDiagnisaKepIntervensi(Request $request){
        $data = \DB::table('intervensi_m as imp')
            ->join('diagnosakeperawatan_m as dg', 'dg.id', '=', 'imp.objectdiagnosakeperawatanfk')
            ->select('imp.*', 'dg.namadiagnosakep')
            ->where('imp.statusenabled', true);
        if (isset($request['id']) && $request['id'] != '') {
            $data = $data->where('imp.id', $request['id']);
        }

        if (isset($request['name']) && $request['name'] != '') {
            $data = $data->where('imp.name', 'ilike', '%' . $request['name'] . '%');
        }
        if (isset($request['iddiagnosakep']) && $request['iddiagnosakep'] != '') {
            $data = $data->where('imp.objectdiagnosakeperawatanfk', $request['iddiagnosakep']);
        }
        $data = $data->orderBy('imp.id', 'desc');
        $data = $data->take(50);
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDiagnisaKepImplemen(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('implementasi_m as imp')
            ->join('diagnosakeperawatan_m as dg', 'dg.id', '=', 'imp.objectdiagnosakeperawatanfk')
            ->select('imp.*', 'dg.namadiagnosakep')
            ->where('imp.kdprofile', $idProfile)
            ->where('imp.statusenabled', true);

        if (isset($request['id']) && $request['id'] != '') {
            $data = $data->where('imp.id', $request['id']);
        }

        if (isset($request['name']) && $request['name'] != '') {
            $data = $data->where('imp.name', 'ilike', '%' . $request['name'] . '%');
        }
        if (isset($request['iddiagnosakep']) && $request['iddiagnosakep'] != '') {
            $data = $data->where('imp.objectdiagnosakeperawatanfk', $request['iddiagnosakep']);
        }
        $data = $data->orderBy('imp.id', 'desc');
        $data = $data->take(50);

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDiagnisaKepEvaluasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('evaluasi_m as imp')
            ->join('diagnosakeperawatan_m as dg', 'dg.id', '=', 'imp.objectdiagnosakeperawatanfk')
            ->select('imp.*', 'dg.namadiagnosakep')
            ->where('imp.kdprofile', $idProfile)
            ->where('imp.statusenabled', true);
        if (isset($request['id']) && $request['id'] != '') {
            $data = $data->where('imp.id', $request['id']);
        }

        if (isset($request['name']) && $request['name'] != '') {
            $data = $data->where('imp.name', 'ilike', '%' . $request['name'] . '%');
        }
        if (isset($request['iddiagnosakep']) && $request['iddiagnosakep'] != '') {
            $data = $data->where('imp.objectdiagnosakeperawatanfk', $request['iddiagnosakep']);
        }
        $data = $data->orderBy('imp.id', 'desc');
        $data = $data->take(50);
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function postDetailDiagnoaKep(Request $request, $table, $method){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            if ($method == 'save') {
                if ($table == 'intervensi') {
                    if ($request['id'] == '') {
                        $RekamMedis = new Intervensi();
                        $RekamMedis->id = Intervensi::max('id') + 1;
                        $RekamMedis->norec = $RekamMedis->generateNewId();
                        $RekamMedis->kdprofile = $idProfile;
                        $RekamMedis->statusenabled = true;
                        $kode = Intervensi::max('kodeexternal');
                        $sub = substr($kode, 7, 3);
                        $kodeEx = $sub + 1;
                        $kodeEx = substr($kode, 0, 7) . $kodeEx;
                        $RekamMedis->kodeexternal = $kodeEx;
                    } else {
                        $RekamMedis = Intervensi::where('id', $request['id'])->first();
                    }
                    $RekamMedis->name = $request['name'];
                    $RekamMedis->objectdiagnosakeperawatanfk = $request['objectdiagnosakeperawatanfk'];
                    $RekamMedis->reportdisplay = $request['name'];
                    $RekamMedis->save();
                }

                if ($table == 'implementasi') {
                    if ($request['id'] == '') {
                        $RekamMedis = new Implementasi();
                        $RekamMedis->id = Implementasi::max('id') + 1;
                        $RekamMedis->norec = $RekamMedis->generateNewId();
                        $RekamMedis->kdprofile = $idProfile;
                        $RekamMedis->statusenabled = true;
                        $kode = Implementasi::max('kodeexternal');
                        $sub = substr($kode, 7, 3);
                        $kodeEx = $sub + 1;
                        $kodeEx = substr($kode, 0, 7) . $kodeEx;
                        $RekamMedis->kodeexternal = $kodeEx;
                    } else {
                        $RekamMedis = Implementasi::where('id', $request['id'])->first();
                    }
                    $RekamMedis->name = $request['name'];
                    $RekamMedis->objectdiagnosakeperawatanfk = $request['objectdiagnosakeperawatanfk'];
                    $RekamMedis->reportdisplay = $request['name'];
                    $RekamMedis->save();
                }
                if ($table == 'evaluasi') {
                    if ($request['id'] == '') {
                        $RekamMedis = new Evaluasi();
                        $RekamMedis->id = Evaluasi::max('id') + 1;
                        $RekamMedis->norec = $RekamMedis->generateNewId();
                        $RekamMedis->kdprofile = $idProfile;
                        $RekamMedis->statusenabled = true;
                        $kode = Evaluasi::max('kodeexternal');
                        $sub = substr($kode, 7, 3);
                        $kodeEx = $sub + 1;
                        $kodeEx = substr($kode, 0, 7) . $kodeEx;
                        $RekamMedis->kodeexternal = $kodeEx;
                    } else {
                        $RekamMedis = Evaluasi::where('id', $request['id'])->first();
                    }
                    $RekamMedis->name = $request['name'];
                    $RekamMedis->objectdiagnosakeperawatanfk = $request['objectdiagnosakeperawatanfk'];
                    $RekamMedis->reportdisplay = $request['name'];
                    $RekamMedis->save();
                }

            }
            if ($method == 'delete') {
                if ($table == 'intervensi') {
                    Intervensi::where('id', $request['id'])
                        ->where('kdprofile', $idProfile)
                        ->update(
                            ['statusenabled' => false]
                        );
                }

                if ($table == 'implementasi') {
                    Implementasi::where('id', $request['id'])
                        ->where('kdprofile', $idProfile)
                        ->update(
                            ['statusenabled' => false]
                        );
                }
                if ($table == 'evaluasi') {
                    Evaluasi::where('id', $request['id'])
                        ->where('kdprofile', $idProfile)
                        ->update(
                            ['statusenabled' => false]
                        );
                }
            }


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
                'as' => 'Inhuman',
            );
        } else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'Inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataRiwayatEMR(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $paramNoRM = '';
        $paramtglLahir = '';
        $paramnamaPasien = '';
        $paramnamaPasien2 = '';
        if (isset($request['norm']) && $request['norm'] != '') {
            $paramNoRM = " AND pm.nocm  ILIKE '%" . $request['norm'] . "%'";
        }
        if (isset($request['namaPasien']) && $request['namaPasien'] != '') {
            $paramnamaPasien = " AND pm.namapasien  ILIKE '%" . $request['namaPasien'] . "%'";
            $paramnamaPasien2 = " AND ep.namapasien  ILIKE '%" . $request['namaPasien'] . "%'";
        }
        if (isset($request['tglLahir']) && $request['tglLahir'] != '') {
            $paramtglLahir = " AND to_char(pm.tgllahir,'yyyy-MM-dd')  =" . $request['tglLahir'] . "%'";
        }

        $data2 = DB::select(DB::raw("SELECT
                    pm.nocm,
                    pm.namapasien,
                    pm.objectjeniskelaminfk,
                    jk.jeniskelamin,
                    pm.tgllahir,
                    pm.notelepon,
                    alm.alamatlengkap,
                    ep.noemr,
                    ep.tglemr,
                    ep.noregistrasi
                FROM
                    emrpasien_t AS ep
                INNER JOIN pasien_m AS pm ON pm.nocm = ep.nocm
                INNER JOIN alamat_m AS alm ON alm.nocmfk = pm.id
                INNER JOIN jeniskelamin_m AS jk ON jk.id = pm.objectjeniskelaminfk
                WHERE
                    ep.statusenabled = true
                AND ep.namaruangan = 'Triage Gawat Darurat'
                AND ep.nocm <> '-'
                AND ep.tglemr >= '$tglAwal'
                AND ep.tglemr <= '$tglAkhir'
               $paramNoRM
               $paramtglLahir
               $paramnamaPasien
                UNION ALL
                    SELECT
                        ep.nocm,
                        ep.namapasien,
                        jk.id AS objectjeniskelaminfk,
                        jk.jeniskelamin,
                        ep.tgllahir,
                        ep.notelepon,
                        ep.alamat AS alamatlengkap,
                        ep.noemr,
                        ep.tglemr,
                        ep.noregistrasi
                    FROM
                        emrpasien_t AS ep
                    LEFT JOIN jeniskelamin_m AS jk ON jk.jeniskelamin = ep.jeniskelamin
                LEFT JOIN pasien_m AS pm ON pm.nocm = ep.nocm
                    WHERE ep.kdprofile = $idProfile and
                          ep.statusenabled = true
                    AND ep.nocm ='-'
                    AND ep.namaruangan = 'Triage Gawat Darurat'

                   $paramtglLahir
                   $paramnamaPasien2
                   "));
        if(count($data2) > 0){
            foreach ($data2 as $key => $row) {
                $count[$key] = $row->tglemr;
            }
            array_multisort($count, SORT_DESC, $data2);
        }

//        $data = \DB::table('emrpasien_t as ep')
//            ->leftjoin('pasien_m as pm','pm.nocm','=','ep.nocm')
//            ->leftJoin('jeniskelamin_m as jk','jk.jeniskelamin','=','ep.jeniskelamin')
//            ->select('ep.nocm','ep.namapasien','jk.id as objectjeniskelaminfk','jk.jeniskelamin',
//                     'ep.tgllahir','ep.notelepon','ep.alamat as alamatlengkap','ep.noemr','ep.tglemr','ep.noregistrasi')
//            ->where('ep.statusenabled',true)
//            ->where('ep.nocm','=','-')
////            ->whereNull('ep.noregistrasi')
//            ->where('ep.namaruangan','Triage Gawat Darurat');
//        $data2 = \DB::table('emrpasien_t as ep')
//            ->join('pasien_m as pm','pm.nocm','=','ep.nocm')
//            ->join('alamat_m as alm','alm.nocmfk','=','pm.id')
//            ->join('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
//            ->select('pm.nocm','pm.namapasien','pm.objectjeniskelaminfk','jk.jeniskelamin',
//                     'pm.tgllahir','pm.notelepon','alm.alamatlengkap','ep.noemr','ep.tglemr','ep.noregistrasi')
//            ->where('ep.statusenabled',true)
//            ->where('ep.namaruangan','Triage Gawat Darurat')
//            ->where('ep.nocm','<>','-')
////            ->whereNull('ep.noregistrasi')
//            ->unionAll($data)
//            ->orderBy('ep.tglemr','desc');

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data2 = $data2->where('ep.tglemr', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];//." 23:59:59";
//            $data2 = $data2->where('ep.tglemr', '<=', $tgl);
//        }
//        if(isset($request['norm']) && $request['norm'] != ''){
//            $data2 = $data2->where('ep.nocm', $request['norm']);
//        }
//        if(isset($request['namaPasien']) && $request['namaPasien'] != ''){
//            $data2 = $data2->where('pm.namapasien','ilike' .'%'.$request['namaPasien'].'%');
//        }
//        if(isset($request['tglLahir']) && $request['tglLahir'] != ''){
//            $data2 = $data2->where('pm.tgllahir',$request['tglLahir']);
//        }
//        $data2 = $data2->get();
        $result = array(
            'data' => $data2,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function updateNoCmInEmrPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $dataKelas = Kelas::where('id', $request['kelas'])
                ->where('statusenabled', true)
                ->select('id', 'namakelas')
                ->first();

            if (isset($request['nocm']) || $request['nocm'] != "-" | $request['nocm'] != "") {
                $dataUpt = EMRPasien::where('noemr', $request['noemr'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'nocm' => $request['nocm'],
                    ]);
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Update Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Update Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function updatePdInEmrPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $dataKelas = Kelas::where('id', $request['kelas'])
                ->where('statusenabled', true)
                ->select('id', 'namakelas')
                ->first();

            $dataKelompokPasien = KelompokPasien::where('id', $request['kelompokpasien'])
                ->where('statusenabled', true)
                ->select('id', 'kelompokpasien')
                ->first();

//            if ($dataKelas == "" && $dataKelompokPasien ==""){
            if (isset($request['norecpd']) || $request['norecpd'] != "-" | $request['norecpd'] != "") {
                $dataUpt = EMRPasien::where('noemr', $request['noemr'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                        'norec_apd' => $request['norecapd'],
                        'noregistrasifk' => $request['norecpd'],
                        'noregistrasi' => $request['noregistrasi'],
                        'kelompokpasien' => $dataKelompokPasien->kelompokpasien,
                        'namakelas' => $dataKelas->namakelas,
                        'tglregistrasi' => $request['tglregistrasi'],
                    ]);
            }
//            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Update Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Update Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPasienPengkajianMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select(DB::raw("pd.tglregistrasi,pd.noregistrasi,pm.nocm,pm.namapasien,ru.namaruangan,
                     CASE WHEN an.anamnesisdokter is not null then an.anamnesisdokter
                     WHEN an.anamnesissuster is not null then an.anamnesissuster
                     ELSE '-' end as anamnesis,
                     CASE WHEN rp.riwayatpengobatan is null then '-'
                     ELSE rp.riwayatpengobatan end AS riwayatpengobatan,
                     CASE WHEN rp.riwayatpenyakit is null then '-'
                     ELSE rp.riwayatpenyakit end as riwayatpenyakit,
                     CASE WHEN pu.pemeriksaanumum is null then '-'
                     ELSE pu.pemeriksaanumum end as pemeriksaanumum,
                     CASE WHEN ed.edukasi is null then '-'
                     ELSE ed.edukasi end as edukasi,
                     CASE WHEN rn.rencana is null then '-'
                     ELSE rn.rencana end as rencana,
                     pp.tglperjanjian,pg.namalengkap as namadokter,
                     ru1.namaruangan as ruangankontrol,pp.keterangan,
                     CASE WHEN cp.s is not NULL then cp.s else '-' end as s,
                     CASE WHEN cp.o is not NULL then cp.o else '-' end as o,
                     CASE WHEN cp.a is not NULL then cp.a else '-' end as a,
                     CASE WHEN cp.p is not NULL then cp.p else '-' end as p"))
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('anamnesis_t as an', 'an.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('riwayatpengobatan_t as rp', 'rp.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('pemeriksaanumum_t as pu', 'pu.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('edukasi_t as ed', 'ed.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('rencana_t as rn', 'rn.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('pasienperjanjian_t as pp', 'pp.objectpasienfk', '=', 'pm.id')
            ->leftJoin('cppt_t as cp', 'cp.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('ruangan_m as ru1', 'ru1.id', '=', 'pp.objectruanganfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'pp.objectdokterfk')
            ->orderBy('pd.tglregistrasi', 'desc')
            ->where('pd.kdprofile', $idProfile)
            ->take(1);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        if (isset($request['noReg']) && $request['noReg'] != '') {
            $data = $data->where('pd.noregistrasi', $request['noReg']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }


    public function getDataPasienPengkajianKeperawatan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select(DB::raw("pd.tglregistrasi,pd.noregistrasi,pm.nocm,pm.namapasien,ru.namaruangan,
                     rm.objectfk,rm.nilai,rm.satuan"))
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('rekammedis_t as rm', 'rm.noregistrasifk', '=', 'apd.norec')
            ->where('pd.kdprofile', $idProfile)
            ->orderBy('rm.objectfk', 'ASC');

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != '') {
            $data = $data->where('rm.noregistrasifk', $request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('ps.nocm', $request['nocm']);
        }
        if (isset($request['noReg']) && $request['noReg'] != '') {
            $data = $data->where('pd.noregistrasi', $request['noReg']);
        }
        if (isset($request['Norec_apd']) && $request['Norec_apd']) {
            $data = $data->where('apd.norec', $request['Norec_apd']);
        }

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function getDaftarRiwayatRegistrasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->join('pasiendaftar_t as pd', 'pd.nocmfk', '=', 'ps.id')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select(DB::raw("pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pd.objectruanganlastfk,ru.namaruangan,
			                  pd.objectpegawaifk,pg.namalengkap as namadokter,pd.tglpulang,ru.objectdepartemenfk,
			                  CASE when ru.objectdepartemenfk in (16,25,26) then 1 else 0 end as statusinap"))
            ->where('ps.kdprofile', $idProfile)
            ->whereNull('br.pasiendaftarfk');

//        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
//            $data = $data->where('ps.tgllahir','>=', $request['tglLahir'].' 00:00');
//        };
//        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
//            $data = $data->where('ps.tgllahir','<=', $request['tglLahir'].' 23:59');
//        };
        if (isset($request['norm']) && $request['norm'] != "" && $request['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $request['norm'] . '%');
        };
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $request['namaPasien'] . '%');
        };
        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noReg']);
        };
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $request['idRuangan']);
        };

        $data = $data->where('ps.statusenabled', true);
        $data = $data->orderBy('pd.tglregistrasi');
        $data = $data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->leftjoin('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->select('ps.nocm', 'ps.namapasien', 'ps.tgldaftar', 'ps.tgllahir',
                'jk.jeniskelamin', 'ps.noidentitas', 'alm.alamatlengkap',
                'ps.id as nocmfk', 'ps.namaayah', 'ps.notelepon', 'ps.nohp', 'ps.tglmeninggal')
            ->where('ps.kdprofile', $idProfile);
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('ps.tgldaftar', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('ps.tgldaftar', '<=', $tgl);
//        }
        if (isset($request['tglLahir']) && $request['tglLahir'] != "" && $request['tglLahir'] != "undefined") {
            $data = $data->where('ps.tgllahir', '>=', $request['tglLahir'] . ' 00:00');
        };
        if (isset($request['tglLahir']) && $request['tglLahir'] != "" && $request['tglLahir'] != "undefined") {
            $data = $data->where('ps.tgllahir', '<=', $request['tglLahir'] . ' 23:59');
        };
        if (isset($request['norm']) && $request['norm'] != "" && $request['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $request['norm'] . '%');
//                ->OrWhere('ps.namapasien', 'ilike', '%'. $request['norm'] .'%');
        };
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $request['namaPasien'] . '%');
//                ->OrWhere('ps.namapasien', 'ilike', '%'. $request['norm'] .'%');
        };
        if (isset($request['alamat']) && $request['alamat'] != "" && $request['alamat'] != "undefined") {
            $data = $data->where('alm.alamatlengkap', 'ilike', '%' . $request['alamat'] . '%');
        };

        if (isset($request['namaAyah']) && $request['namaAyah'] != "" && $request['namaAyah'] != "undefined") {
            $data = $data->where('ps.namaayah', '=', $request['namaAyah']);
        };
        $data = $data->where('ps.statusenabled', true);
        // $data=$data->orderBy('ps.namapasien','asc');
        $data = $data->take(50);
        $data = $data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function saveOrderPelayanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detLogin = $request->all();
        if ($request['pegawaiorderfk'] == "") {
            $dokter2 = null;
        } else {
            $dokter2 = $request['pegawaiorderfk'];
        }


        DB::beginTransaction();
        try {
            if ($request['departemenfk'] == 3) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'L' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 27) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'R' . $this->getDateTime()->format('ym'),$idProfile);
            }
            if ($request['departemenfk'] == 25) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'OK' . $this->getDateTime()->format('ym'),$idProfile);
            }
            if ($request['departemenfk'] == 5) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'PJ' . $this->getDateTime()->format('ym'),$idProfile);
            }
            if ($request['departemenfk'] == 31) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'ABM' . $this->getDateTime()->format('ym'),$idProfile);
            }

            $dataPD = PasienDaftar::where('norec', $request['norec_pd'])->first();
            if ($request['norec_so'] == "") {
                $dataSO = new StrukOrder;
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->nocmfk = $dataPD->nocmfk;//$dataPD;
//            $dataSO->cito = '0';
//            $dataSO->objectdiagnosafk = true;
                $dataSO->isdelivered = 1;
                $dataSO->noorder = $noOrder;
                $dataSO->noorderintern = $noOrder;
                $dataSO->noregistrasifk = $dataPD->norec;//$dataAPD['noregistrasifk'];
                $dataSO->objectpegawaiorderfk = $dokter2;//$request['pegawaiorderfk'];
                $dataSO->qtyjenisproduk = 1;
                $dataSO->qtyproduk = $request['qtyproduk'];
                $dataSO->objectruanganfk = $request['objectruanganfk'];
                $dataSO->objectruangantujuanfk = $request['objectruangantujuanfk'];
                if ($request['departemenfk'] == 3) {
                    $dataSO->keteranganorder = 'Order Laboratorium';
                    $dataSO->objectkelompoktransaksifk = 93;
                }
                if ($request['departemenfk'] == 27) {
                    $dataSO->keteranganorder = 'Order Radiologi';
                    $dataSO->objectkelompoktransaksifk = 94;
                }
                if ($request['departemenfk'] == 25) {
                    $dataSO->keteranganorder = 'Pesan Jadwal Operasi';
                    $dataSO->objectkelompoktransaksifk = 22;
                    $dataSO->tglpelayananakhir = $request['tgloperasi'];
                    $dataSO->tglpelayananawal = $request['tgloperasi'];
                }
                if ($request['departemenfk'] == 5) {
                    $dataSO->keteranganorder = 'Pelayanan Pemulasaraan Jenazah';
                    $dataSO->objectkelompoktransaksifk = 99;
                }
                if ($request['departemenfk'] == 31) {
                    $dataSO->keteranganorder = 'Pesan Ambulan';
                    $dataSO->objectkelompoktransaksifk = 9;
                    $dataSO->tglrencana = $request['tglrencana'];
                }
                $dataSO->tglorder = date('Y-m-d H:i:s');
                if(isset( $request['keterangan'])){
                    $dataSO->keteranganlainnya = $request['keterangan'];
                }
                $dataSO->totalbeamaterai = 0;
                $dataSO->totalbiayakirim = 0;
                $dataSO->totalbiayatambahan = 0;
                $dataSO->totaldiscount = 0;
                $dataSO->totalhargasatuan = 0;
                $dataSO->totalharusdibayar = 0;
                $dataSO->totalpph = 0;
                $dataSO->totalppn = 0;
                $dataSO->save();

                $dataSOnorec = $dataSO->norec;


                foreach ($request['details'] as $item) {
                    if ($request['status'] == 'bridinglangsung') {
                        $updatePP = PelayananPasien::where('norec', $item['norec_pp'])
                            ->update([
                                    'strukorderfk' => $dataSOnorec
                                ]
                            );
                    }

                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if (isset($item['iscito'])) {
                        $dataOP->iscito = (float)$item['iscito'];
                    } else {
                        $dataOP->iscito = 0;
                    }

                    $dataOP->noorderfk = $dataSOnorec;
                    $dataOP->objectprodukfk = $item['produkfk'];
                    $dataOP->qtyproduk = $item['qtyproduk'];
                    $dataOP->objectkelasfk = $item['objectkelasfk'];
                    $dataOP->qtyprodukretur = 0;
                    $dataOP->objectruanganfk = $request['objectruanganfk'];
                    $dataOP->objectruangantujuanfk = $request['objectruangantujuanfk'];
                    $dataOP->strukorderfk = $dataSOnorec;
                    if (isset($item['pemeriksaanluar'])) {
                        if ($item['pemeriksaanluar'] == 1) {
                            $dataOP->keteranganlainnya = 'isPemeriksaanKeluar';
                        }
                    }

                    if (isset($item['tglrencana'])) {
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    } else {
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    $dataOP->strukorderfk = $dataSOnorec;
                    if (isset($item['dokterid']) && $item['dokterid'] != "") {
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->save();
                }

            } else {

                foreach ($request['details'] as $item) {
                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if (isset($item['iscito'])) {
                        $dataOP->iscito = (float)$item['iscito'];
                    } else {
                        $dataOP->iscito = 0;
                    }

                    $dataOP->noorderfk = $request['norec_so'];
                    $dataOP->objectprodukfk = $item['produkfk'];
                    $dataOP->qtyproduk = $item['qtyproduk'];
                    $dataOP->objectkelasfk = $item['objectkelasfk'];
                    $dataOP->qtyprodukretur = 0;
                    $dataOP->objectruanganfk = $request['objectruanganfk'];
                    $dataOP->objectruangantujuanfk = $request['objectruangantujuanfk'];
                    $dataOP->strukorderfk = $request['norec_so'];

                    if (isset($item['tglrencana'])) {
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    } else {
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    if (isset($item['dokterid']) && $item['dokterid'] != "") {
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan OrderPelayanan";
        }

        if ($transStatus == 'true') {
            if ($request['norec_so'] == "") {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
                    "strukorder" => $dataSO,
                    "as" => 'inhuman',
                );
            } else {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
//                    "strukorder" => $dataSO,
                    "as" => 'inhuman',
                );
            }
        } else {
            $transMessage = "Simpan Order Pelayanan gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getIcd9(Request $request){
        $req = $request->all();
        $icdIX = \DB::table('diagnosatindakan_m as dg')
            ->select('dg.id', 'dg.kddiagnosatindakan as kdDiagnosaTindakan', 'dg.namadiagnosatindakan as namaDiagnosaTindakan')
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosatindakan');

        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $icdIX = $icdIX
                ->where('dg.namadiagnosatindakan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%')
                ->orWhere('dg.kddiagnosatindakan', 'ilike', $req['filter']['filters'][0]['value'] . '%');
        }


        $icdIX = $icdIX->take(10);
        $icdIX = $icdIX->get();
        $data = [];
        if (count($icdIX) > 0) {
            foreach ($icdIX as $item) {
                $data [] = array(
                    'kodeNama' => $item->kdDiagnosaTindakan . ' - ' . $item->namaDiagnosaTindakan,
                    'id' => $item->id,
                    'kdDiagnosaTindakan' => $item->kdDiagnosaTindakan,
                    'namaDiagnosaTindakan' => $item->namaDiagnosaTindakan,

                );

            }
        }

        return $this->respond($data);
    }

    public function getDiagnosaIcd10Part(Request $request){
        $req = $request->all();
        $dataProduk = [];
        $dataProduk = \DB::table('diagnosa_m as st')
            ->select('st.id', 'st.kddiagnosa as kdDiagnosa', 'st.namadiagnosa as namaDiagnosa')
            ->where('st.statusenabled', true)
            ->orderBy('st.kddiagnosa');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('st.kddiagnosa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%')
                ->orWhere('st.namadiagnosa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();


        $data = [];
        if (count($dataProduk) > 0) {
            foreach ($dataProduk as $item) {
                $data [] = array(
                    'kodeNama' => $item->kdDiagnosa . ' - ' . $item->namaDiagnosa,
                    'id' => $item->id,
                    'kdDiagnosa' => $item->kdDiagnosa,
                    'namaDiagnosa' => $item->namaDiagnosa,

                );

            }
        }

        return $this->respond($data);
    }

    public function getComboDiagnosis(Request $request){
        $dataLogin = $request->all();
        $jd = \DB::table('jenisdiagnosa_m as lu')
            ->select('lu.id', 'lu.jenisdiagnosa as jenisDiagnosa')
            ->where('lu.statusenabled', true)
            ->get();


        $result = array(
            'jenisdiagnosa' => $jd,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }

    public function getDiagnosaPasienByNoregICD9(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi', 'pd.tglregistrasi', 'apd.objectruanganfk', 'ru.namaruangan',
                'apd.norec as norec_apd', 'ddt.objectdiagnosatindakanfk', 'dt.kddiagnosatindakan', 'dt.namadiagnosatindakan',
                'dtp.norec as norec_diagnosapasien',
                'ddt.norec as norec_detaildpasien', 'dt.*', 'ddt.keterangantindakan', 'pg.namalengkap', 'ddt.tglinputdiagnosa')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('diagnosatindakanpasien_t as dtp', 'dtp.objectpasienfk', '=', 'apd.norec')
            ->join('detaildiagnosatindakanpasien_t as ddt', 'ddt.objectdiagnosatindakanpasienfk', '=', 'dtp.norec')
            ->join('diagnosatindakan_m as dt', 'dt.id', '=', 'ddt.objectdiagnosatindakanfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ddt.objectpegawaifk')
            ->where('pd.kdprofile',$idProfile);
//            ->join ('jenisdiagnosa_m as jd','jd.id','=','ddp.objectjenisdiagnosafk');
        if (isset($request['noCm']) && $request['noCm'] != "" && $request['noCm'] != "undefined") {
            $data = $data->where('ps.nocm', '=', $request['noCm']);
        };
        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noReg']);
        };
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $request['idDept']);
        };
        if (isset($request['kddiagnosatindakan']) && $request['kddiagnosatindakan'] != "" && $request['kddiagnosatindakan'] != "undefined") {
            $data = $data->where('dt.kddiagnosatindakan', '=', $request['kddiagnosatindakan']);
        }
        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'giw@cepot',
        );
        return $this->respond($result);
    }

    public function getDiagnosaPasienByNoreg(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi', 'pd.tglregistrasi', 'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd',
                'ddp.objectdiagnosafk', 'dg.kddiagnosa', 'dg.namadiagnosa', 'ddp.tglinputdiagnosa', 'ddp.objectjenisdiagnosafk',
                'jd.jenisdiagnosa', 'dp.norec as norec_diagnosapasien', 'ddp.norec as norec_detaildpasien', 'ddp.tglinputdiagnosa',
                'pg.namalengkap',
                'dp.ketdiagnosis', 'ddp.keterangan', 'dg.*', 'dp.iskasusbaru', 'dp.iskasuslama')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'apd.norec')
            ->join('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->join('diagnosa_m as dg', 'dg.id', '=', 'ddp.objectdiagnosafk')
            ->join('jenisdiagnosa_m as jd', 'jd.id', '=', 'ddp.objectjenisdiagnosafk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ddp.objectpegawaifk')
            ->where('pd.kdprofile',$idProfile)
            ->orderby('ddp.tglinputdiagnosa', 'desc');
        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noReg']);
        };

        if (isset($request['noCm']) && $request['noCm'] != "" && $request['noCm'] != "undefined") {
            $data = $data->where('ps.nocm', '=', $request['noCm']);
        };

        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'giw',
        );
        return $this->respond($result);
    }

    public function saveDiagnosaTindakanPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

//        try{
        if ($request['detaildiagnosatindakanpasien']['norec_dp'] == '') {
            $dataDiagnosa = new DiagnosaTindakanPasien();
            $dataDiagnosa->norec = $dataDiagnosa->generateNewId();
            $dataDiagnosa->kdprofile = $idProfile;
            $dataDiagnosa->statusenabled = true;
        } else {
            $dataDiagnosa = DiagnosaTindakanPasien::where('norec', $request['detaildiagnosatindakanpasien']['norec_dp'])->first();
        }
        $dataDiagnosa->objectpasienfk = $request['detaildiagnosatindakanpasien']['objectpasienfk'];
        $dataDiagnosa->tglpendaftaran = $request['detaildiagnosatindakanpasien']['tglpendaftaran'];


        try {
            $dataDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Diagnosa Baru";
        }


        if ($request['detaildiagnosatindakanpasien']['norec_dp'] == '') {
            $dataDetailDiagnosa = new DetailDiagnosaTindakanPasien();
            $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
            $dataDetailDiagnosa->kdprofile = $idProfile;
            $dataDetailDiagnosa->statusenabled = true;
            $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();
        } else {
            $dataDetailDiagnosa = DetailDiagnosaTindakanPasien::where('objectdiagnosatindakanpasienfk', $request['detaildiagnosatindakanpasien']['norec_dp'])->first();
        }

        $dataDetailDiagnosa->objectdiagnosatindakanfk = $request['detaildiagnosatindakanpasien']['objectdiagnosatindakanfk'];
        $dataDetailDiagnosa->objectdiagnosatindakanpasienfk = $dataDiagnosa->norec;
        $dataDetailDiagnosa->jumlah = null;
        $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID()
        if (isset($request['detaildiagnosatindakanpasien']['keterangantindakan'])) {
            $dataDetailDiagnosa->keterangantindakan = $request['detaildiagnosatindakanpasien']['keterangantindakan'];
        }

        $dataDetailDiagnosa->tglinputdiagnosa = date('Y-m-d H:i:s');

        try {
            $dataDetailDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Pasien Baru";
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Tersimpan";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'data' => $dataDiagnosa,
                'as' => 'egie@ramdan',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'data' => $dataDiagnosa,
                'as' => 'egie@ramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteDiagnosaTindakanPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        if ($request['diagnosa']['norec_dp'] != '') {
            try {
                $data1 = DetailDiagnosaTindakanPasien::where('objectdiagnosatindakanpasienfk', $request['diagnosa']['norec_dp'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
            }
            try {
                $data2 = DiagnosaTindakanPasien::where('norec', $request['diagnosa']['norec_dp'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
            }

        }
        if ($transStatus = 'true') {
            DB::commit();
            $transMessage = "Data Terhapus";
        } else {
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }
        return $this->setStatusCode(201)->respond([], $transMessage);
    }

    public function saveDiagnosaPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
//        try{
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where pg.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        if ($request['detaildiagnosapasien']['norec_dp'] == '') {
            $dataDiagnosa = new DiagnosaPasien();
            $dataDiagnosa->norec = $dataDiagnosa->generateNewId();
            $dataDiagnosa->kdprofile = $idProfile;
            $dataDiagnosa->statusenabled = true;

        } else {
            $dataDiagnosa = DiagnosaPasien::where('norec', $request['detaildiagnosapasien']['norec_dp'])->first();
        }

        $dataDiagnosa->noregistrasifk = $request['detaildiagnosapasien']['noregistrasifk'];
        $dataDiagnosa->ketdiagnosis = 'Diagnosa Pasien';
        $dataDiagnosa->tglregistrasi = null;
        $dataDiagnosa->tglpendaftaran = $request['detaildiagnosapasien']['tglregistrasi'];
        if (isset($request['detaildiagnosapasien']['kasusbaru'])) {
            $dataDiagnosa->iskasusbaru = $request['detaildiagnosapasien']['kasusbaru'];
        }
        if (isset($request['detaildiagnosapasien']['kasuslama'])) {
            $dataDiagnosa->iskasuslama = $request['detaildiagnosapasien']['kasuslama'];
        }
        try {
            $dataDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Diagnosa Baru";
        }


        if ($request['detaildiagnosapasien']['norec_dp'] == '' || $request['detaildiagnosapasien']['keterangan'] == '') {
            $dataDetailDiagnosa = new DetailDiagnosaPasien();
            $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
            $dataDetailDiagnosa->kdprofile = $idProfile;
            $dataDetailDiagnosa->statusenabled = true;
//               $dataDetailDiagnosa->keterangan = '-';
            $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();

        } else {
            $dataDetailDiagnosa = DetailDiagnosaPasien::where('objectdiagnosapasienfk', $request['detaildiagnosapasien']['norec_dp'])->first();
        }

        $dataDetailDiagnosa->noregistrasifk = $request['detaildiagnosapasien']['noregistrasifk'];
        $dataDetailDiagnosa->tglregistrasi = $request['detaildiagnosapasien']['tglregistrasi'];
        $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
        $dataDetailDiagnosa->objectdiagnosafk = $request['detaildiagnosapasien']['objectdiagnosafk'];
        $dataDetailDiagnosa->objectdiagnosapasienfk = $dataDiagnosa->norec;
        $dataDetailDiagnosa->objectjenisdiagnosafk = $request['detaildiagnosapasien']['objectjenisdiagnosafk'];
        $dataDetailDiagnosa->tglinputdiagnosa = date('Y-m-d H:i:s');//$request['detaildiagnosapasien']['tglinputdiagnosa'];
        $dataDetailDiagnosa->keterangan = $request['detaildiagnosapasien']['keterangan'];
        $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();


        try {
            $dataDetailDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Pasien Baru";
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Tersimpan";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'data' => $dataDiagnosa,
                'as' => 'egie@ramdan',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'data' => $dataDiagnosa,
                'as' => 'egie@ramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function deleteDiagnosaPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        if ($request['diagnosa']['norec_dp'] != '') {
            try {
                $data1 = DetailDiagnosaPasien::where('objectdiagnosapasienfk', $request['diagnosa']['norec_dp'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
            }
            try {
                $data2 = DiagnosaPasien::where('norec', $request['diagnosa']['norec_dp'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
            }

        }
        if ($transStatus = 'true') {
            DB::commit();
            $transMessage = "Data Terhapus";
        } else {
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }

    public function getDataComboResepEMR(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();

        $dataSigna = \DB::table('stigma as st')
            ->select('st.id', 'st.name')
            ->orderBy('st.name')
            ->get();

        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'mlu.objectruanganfk')
            ->select('ru.id', 'ru.namaruangan')
            ->where('mlu.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk', $request['userData']['id'])
            ->get();

        $dataRuanganFamasi = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.objectdepartemenfk', 14)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataJenisKemasan = \DB::table('jeniskemasan_m as jk')
            ->select('jk.id', 'jk.jeniskemasan')
            ->where('jk.kdprofile', $idProfile)
            ->where('jk.statusenabled', true)
            ->get();
        $dataJenisRacikan = \DB::table('jenisracikan_m as jk')
            ->select('jk.id', 'jk.jenisracikan')
            ->where('jk.kdprofile', $idProfile)
            ->where('jk.statusenabled', true)
            ->get();

        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd', 'spd.objectprodukfk', '=', 'pr.id')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar')
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled', true)
            ->whereIn('jp.id', [97, 283])
            ->where('spd.qtyproduk', '>', 0)
            ->groupBy('pr.id', 'pr.namaproduk', 'ss.id', 'ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataProdukOrder = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd', 'spd.objectprodukfk', '=', 'pr.id')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar')
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled', true)
            ->whereIn('jp.id', [97, 283])
            ->groupBy('pr.id', 'pr.namaproduk', 'ss.id', 'ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();


        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->JOIN('stokprodukdetail_t as spd', 'spd.objectasalprodukfk', '=', 'ap.id')
            ->select('ap.id', 'ap.asalproduk')
            ->where('ap.kdprofile', $idProfile)
            ->where('ap.statusenabled', true)
            ->orderBy('ap.id')
            ->groupBy('ap.id', 'ap.asalproduk')
            ->get();


        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss', 'ss.id', '=', 'ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2', 'ss2.id', '=', 'ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk', 'ks.satuanstandar_asal', 'ss.satuanstandar', 'ks.satuanstandar_tujuan', 'ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $idProfile)
            ->where('ks.statusenabled', true)
            ->get();

        $dataTarifAdminResep = \DB::table('settingdatafixed_m as rt')
            ->select('rt.namafield', 'rt.nilaifield')
            ->where('rt.kdprofile', $idProfile)
            ->where('rt.statusenabled', true)
            ->where('rt.namafield', 'tarifadminresep')
            ->orderBy('rt.id')
            ->first();

        $dataRoute = \DB::table('routefarmasi as rt')
            ->select('rt.id', 'rt.name')
            ->where('rt.kdprofile', $idProfile)
            ->where('rt.statusenabled', true)
            ->orderBy('rt.id')
            ->get();


        $dataProdukResult = [];
        foreach ($dataProduk as $item) {
            $satuanKonversi = [];
            foreach ($dataKonversiProduk as $item2) {
                if ($item->id == $item2->objekprodukfk) {
                    $satuanKonversi[] = array(
                        'ssid' => $item2->satuanstandar_tujuan,
                        'satuanstandar' => $item2->satuanstandar2,
                        'nilaikonversi' => $item2->nilaikonversi,
                    );
                }
            }

            $dataProdukResult[] = array(
                'id' => $item->id,
                'namaproduk' => $item->namaproduk,
                'ssid' => $item->ssid,
                'satuanstandar' => $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
            );
        }

        $dataProdukOrderResult = [];
        foreach ($dataProdukOrder as $item) {
            $satuanKonversi = [];
            foreach ($dataKonversiProduk as $item2) {
                if ($item->id == $item2->objekprodukfk) {
                    $satuanKonversi[] = array(
                        'ssid' => $item2->satuanstandar_tujuan,
                        'satuanstandar' => $item2->satuanstandar2,
                        'nilaikonversi' => $item2->nilaikonversi,
                    );
                }
            }

            $dataProdukOrderResult[] = array(
                'id' => $item->id,
                'namaproduk' => $item->namaproduk,
                'ssid' => $item->ssid,
                'satuanstandar' => $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
            );
        }

        $dataSatuanResep = \DB::table('satuanresep_m as kp')
            ->select('kp.id', 'kp.satuanresep')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.satuanresep')
            ->get();

        $result = array(
            'ruanganfarmasi' => $dataRuanganFamasi,
            'jeniskemasan' => $dataJenisKemasan,
            'produk' => $dataProdukResult,
            'produkorder' => $dataProdukOrderResult,
            'ruangan' => $dataRuangan,
            'asalproduk' => $dataAsalProduk,
            'signa' => $dataSigna,
            'jenisracikan' => $dataJenisRacikan,
            'tarifadminresep' => $dataTarifAdminResep,
            'route' => $dataRoute,
            'satuanresep'=>$dataSatuanResep,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarDetailOrder(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukorder_t as so')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'so.nocmfk')
            ->JOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->JOIN('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
            ->leftJOIN('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->JOIN('kelas_m as kl', 'kl.id', '=', 'pd.objectkelasfk')
            ->leftJOIN('antrianpasiendiperiksa_t as apd', function ($join) {
                $join->on('apd.noregistrasifk', '=', 'pd.norec')
                    ->on('apd.objectruanganfk', '=', 'so.objectruanganfk');
//                    ->on('apd.objectpegawaifk', '=', 'so.objectpegawaiorderfk');
            })
            ->leftJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->select('so.noorder', 'ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'ru.namaruangan as namaruanganrawat',
                'so.tglorder', 'pg.namalengkap', 'ru2.namaruangan',
                'so.statusorder', 'so.namapengambilorder', 'so.noregistrasifk',
                'pd.noregistrasi', 'kp.kelompokpasien',
                'apd.norec as norec_apd',
                'pd.tglregistrasi', 'ps.tgllahir', 'kl.namakelas', 'kl.id as klid', 'so.tglambilorder', 'so.norec as norec_order')
            ->where('so.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('so.tglorder', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'] . " 23:59:59";
            $data = $data->where('so.tglorder', '<=', $tgl);
        }
        if (isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $request['nocm'] . '%');
        }
        if (isset($request['norec_apd']) && $request['norec_apd'] != "" && $request['norec_apd'] != "undefined") {
            $data = $data->where('apd.norec', $request['norec_apd']);
        }
        $data = $data->where('so.keteranganorder', 'ilike', '%' . 'Order Farmasi' . '%');
        $data = $data->where('so.objectkelompoktransaksifk', 4);
        $data = $data->where('so.statusenabled', true);
        $data = $data->get();
        $status = '';

        $result = [];
        foreach ($data as $item) {
            $details = DB::select(DB::raw("
                    SELECT so.noorder,op.rke, jk.jeniskemasan, pr.namaproduk, ss.satuanstandar, op.aturanpakai, op.jumlah, op.hargasatuan,op.keteranganpakai as keterangan,
                           op.satuanresepfk,sn.satuanresep,op.tglkadaluarsa
                    from strukorder_t as so
                    left join orderpelayanan_t as op on op.strukorderfk = so.norec
                    left join produk_m as pr on pr.id=op.objectprodukfk
                    left join jeniskemasan_m as jk on jk.id=op.jeniskemasanfk
                    left join satuanstandar_m as ss on ss.id=op.objectsatuanstandarfk
                    left join satuanresep_m as sn on sn.id=op.satuanresepfk
                    where so.kdprofile = $idProfile and so.statusenabled = true and noorder=:noorder"),
                array(
                    'noorder' => $item->noorder,
                )
            );
            if ($item->statusorder == 0) {
                $status = 'Menunggu';
            };
            if ($item->statusorder == 5) {
                $status = 'Verifikasi';
            };
            if ($item->statusorder == 1) {
                $status = 'Produksi';
            };
            if ($item->statusorder == 2) {
                $status = 'Packaging';
            };
            if ($item->statusorder == 3) {
                $status = 'Selesai';
            };
            if ($item->statusorder == 4) {
                $status = 'Penyerahan Obat';
            };
            if ($item->tglambilorder != null) {
                $status = 'Sudah Di Ambil';
            };
            $result[] = array(
                'noregistrasi' => $item->noregistrasi,
                'norec_order' => $item->norec_order,
                'norec' => $item->noregistrasifk,
                'tglregistrasi' => $item->tglregistrasi,
                'norec_apd' => $item->norec_apd,
                'noorder' => $item->noorder,
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'jeniskelamin' => $item->jeniskelamin,
                'namaruanganrawat' => $item->namaruanganrawat,
                'tglorder' => $item->tglorder,
                'namalengkap' => $item->namalengkap,
                'kelompokpasien' => $item->kelompokpasien,
                'namaruangan' => $item->namaruangan,
                'statusorder' => $status,
                'namapengambilorder' => $item->namapengambilorder,
                'tgllahir' => $item->tgllahir,
                'klid' => $item->klid,
                'namakelas' => $item->namakelas,
                'details' => $details
            );
        }

        return $this->respond($result);

    }

    public function getInformasiStok(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,ru.namaruangan,
                   CAST(sum(spd.qtyproduk) AS FLOAT) as qtyproduk,spd.objectruanganfk as kdruangan
                    from stokprodukdetail_t as spd
                    inner JOIN ruangan_m as ru on ru.id=spd.objectruanganfk
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where spd.kdprofile = $idProfile and spd.objectprodukfk =:produkId and ru.statusenabled = true
                    --and spd.objectruanganfk =:ruanganid
                    group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                            spd.harganetto2,spd.hargadiscount,ru.namaruangan,
                    spd.objectruanganfk
                    order By sk.tglstruk"),
            array(
                'produkId' => $request['produkfk'],
//                'ruanganid' => $request['ruanganfk'],
            )
        );
        $jmlstok = 0;
        foreach ($results as $item) {
            $jmlstok = $jmlstok + $item->qtyproduk;
        }
        $a = [];
        foreach ($results as $nenden) {
            $i = 0;
            $sama = false;
            foreach ($a as $hideung) {
                if ($nenden->kdruangan == $a[$i]['kdruangan']) {
                    $sama = true;
                    $a[$i]['qtyproduk'] = $a[$i]['qtyproduk'] + $nenden->qtyproduk;
                }
                $i = $i + 1;
            }

            if ($sama == false) {
                $a[] = array(
                    'qtyproduk' => $nenden->qtyproduk,
                    'kdruangan' => $nenden->kdruangan,
                    'namaruangan' => $nenden->namaruangan,
                );
            }
        }

        $result = array(
            'jmlstok' => $jmlstok,
            'infostok' => $a,
            'detail' => $results,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function getJenisObat(Request $request){
        $dataLogin = $request->all();
        $data = \DB::table('jenisracikan_m as jr')
            ->select('jr.id', 'jr.jenisracikan');

        if (isset($request['jrid']) && $request['jrid'] != "" && $request['jrid'] != "undefined") {
            $data = $data->where('jr.id', $request['jrid']);
        }
        $data = $data->get();


        $result = array(
            'data' => $data,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function SimpanOrderPelayananObat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;

        // return $request->all();
        // die();
        if ($request['data'][0]['strukorder']['penulisresepfk'] == "") {
            $dokter2 = null;
        } else {
            $dokter2 = $request['data'][0]['strukorder']['penulisresepfk'];
        }
        // $r_SR = $request['0']['strukorder'];
        DB::beginTransaction();
        // $pasien = DB::table('pasien_m')->where('id',$dataPD->nocmfk)->first();
        try {
            foreach($request['data'] as $data)
            {
                $r_SR = $data['strukorder'];

                $dataDetail = \DB::table('antrianpasiendiperiksa_t as apd')
                    ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                    ->select('pd.norec', 'pd.nocmfk', 'apd.objectruanganfk', 'pd.objectkelasfk', 'apd.norec as apdnorec')
                    ->where('apd.norec', $r_SR['noregistrasifk'])
                    ->first();
                // $pdpasien = DB::table('pasiendaftar_t')->where('norec',$dataDetail->norec)->first();

                $pasien = DB::table('pasien_m')->where('id',$dataDetail->nocmfk)->first();
                //        return $this->respond(array($dataDetail->nocmfk));

                if ($r_SR['norec'] == '') {
                    $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorder', 10, date('ym'),$idProfile);
                    if ($noOrder == ''){
                        $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                        DB::rollBack();
                        $result = array(
                            "status" => 400,
                            "message"  => $transMessage,
                            "as" => 'as@epic',
                        );
                        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                    }

                    $newSO = new StrukOrder();
                    $norecSO = $newSO->generateNewId();

                    // $newId = StrukOrder::where('noorder', 'ilike', $this->getDateTime()->format('ym') . '%')->max('noorder');
                    // $newId = substr(trim($newId), 4);
                    // $newId = (int)$newId + 1;
                    // $noOrder = $this->getDateTime()->format('ym') . str_pad($newId, 6, "0", STR_PAD_LEFT);
                    $newSO->norec = $norecSO;
                    $newSO->kdprofile = $idProfile;
                    $newSO->statusenabled = true;
                } else {
                    $newSO = StrukOrder::where('norec', $r_SR['norec'])->first();

                    $noOrder = $newSO->noorder;
                    OrderPelayanan::where('noorderfk', $newSO->norec)->delete();
                }

                $newSO->nocmfk = $dataDetail->nocmfk;
                $newSO->kddokter = $r_SR['penulisresepfk'];
                $newSO->objectjenisorderfk = 5;
                $newSO->isdelivered = 1;
                $newSO->objectkelompoktransaksifk = 4;
                $newSO->keteranganorder = 'Order Farmasi';
                $newSO->noorder = $noOrder;
                $newSO->noregistrasifk = $dataDetail->norec;
                $newSO->objectpegawaiorderfk = $r_SR['penulisresepfk'];
                $newSO->qtyproduk = $r_SR['qtyproduk'];
                $newSO->qtyjenisproduk = $r_SR['qtyproduk'];
                $newSO->objectruanganfk = $dataDetail->objectruanganfk;
                $newSO->objectruangantujuanfk = $r_SR['ruanganfk'];
                $newSO->statusorder = 0;
                $newSO->tglorder = $r_SR['tglresep'];
                //        $newSO->tglorderexpired =  $r_SR['tglresep'];
                $newSO->totalbeamaterai = 0;
                $newSO->totalbiayakirim = 0;
                $newSO->totalbiayatambahan = 0;
                $newSO->totaldiscount = 0;
                $newSO->totalhargasatuan = 0;
                $newSO->totalharusdibayar = 0;
                $newSO->totalpph = 0;
                $newSO->totalppn = 0;
                if(isset($r_SR['isreseppulang']) == 1){
                    $newSO->isreseppulang = true;
                }
                if (isset($r_SR['noruangan'])) {
                    $newSO->nourutruangan = $r_SR['noruangan'];
                }

                //        try {
                $newSO->save();
                //            $transStatus = 'true';
                //        } catch (\Exception $e) {
                //            $transStatus = 'false';
                //            $transMessage = "Simpan StrukOrder";
                //        }

                $norec_SR = $newSO->norec;
                $dokterPenulis = $newSO->penulisresepfk;

                //        $newSR = new StrukResep();
                //        $norecSR = $newSR->generateNewId();
                //        $noResep = $this->generateCode(new StrukResep, 'noresep', 12, 'O/' . $this->getDateTime()->format('ym') . '/');
                //        $newSR->norec = $norecSR;
                //        $newSR->kdprofile = 0;
                //        $newSR->statusenabled = true;
                //        $newSR->noresep = $noResep;
                //        $newSR->pasienfk = $dataDetail->apdnorec;
                //        $newSR->orderfk = $norec_SR;
                //        $newSR->penulisresepfk = $r_SR['penulisresepfk'];
                //        $newSR->ruanganfk = $r_SR['ruanganfk'];
                //        $newSR->status = 0;
                //        $newSR->tglresep =  $r_SR['tglresep'];//->format('Y-m-d H:i:s');
                //
                //        try {
                //            $newSR->save();
                //            $transStatus = 'true';
                //        } catch (\Exception $e) {
                //            $transStatus = 'false';
                //            $transMessage = "Simpan StrukResep Pasien";
                //        }

                //## PelayananPasien
                $r_PP = $data['orderfarmasi'];
                $prod = [];
                // $r_PP = $request['0']['orderfarmasi'];
                foreach ($r_PP as $r_PPL) {
                    $qtyJumlah = (float)$r_PPL['jumlah'] * (float)$r_PPL['nilaikonversi'];
                    $pro = DB::table('produk_m')->where('id',$r_PPL['produkfk'])->first();
                    $prod[] =$pro->namaproduk;

                    $newPP = new OrderPelayanan();
                    $norecPP = $newPP->generateNewId();
                    $newPP->norec = $norecPP;
                    $newPP->kdprofile = $idProfile;
                    $newPP->statusenabled = true;

                    //            $newPP->objectasalprodukfk = $r_PPL['asalprodukfk'];
                    $newPP->aturanpakai = $r_PPL['aturanpakai'];
                    $newPP->isreadystok = 1;
                    $newPP->kddokter = $r_SR['penulisresepfk'];;
                    $newPP->objectkelasfk = $dataDetail->objectkelasfk;;
                    $newPP->nocmfk = $dataDetail->nocmfk;
                    $newPP->noorderfk = $norec_SR;
                    $newPP->noregistrasifk = $dataDetail->norec;
                    $newPP->objectprodukfk = $r_PPL['produkfk'];
                    $newPP->qtyproduk = $r_PPL['jumlah'];
                    $newPP->qtystokcurrent = $r_PPL['jmlstok'];
                    $newPP->racikanke = $r_PPL['rke'];
                    $newPP->objectruanganfk = $dataDetail->objectruanganfk;
                    $newPP->objectruangantujuanfk = $r_PPL['ruanganfk'];
                    $newPP->objectsatuanstandarfk = $r_PPL['satuanstandarfk'];
                    $newPP->strukorderfk = $norec_SR;
                    $newPP->tglpelayanan = $r_SR['tglresep'];
                    if (isset($r_PPL['jenisobatfk'])) {
                        $newPP->jenisobatfk = $r_PPL['jenisobatfk'];//5;
                    }
                    $newPP->jumlah = $qtyJumlah;//$r_PPL['jumlah'];
                    $newPP->iscito = 0;
                    $newPP->hargasatuan = $r_PPL['hargasatuan'];
                    $newPP->hargadiscount = $r_PPL['hargadiscount'];
                    $newPP->qtyprodukretur = 0;
                    $newPP->hasilkonversi = $r_PPL['nilaikonversi'];;
                    $newPP->jeniskemasanfk = $r_PPL['jeniskemasanfk'];
                    $newPP->dosis = $r_PPL['dosis'];
                    $newPP->rke = $r_PPL['rke'];
                    $newPP->satuanviewfk = $r_PPL['satuanviewfk'];
                    $newPP->ispagi = $r_PPL['ispagi'];
                    $newPP->issiang = $r_PPL['issiang'];
                    $newPP->ismalam = $r_PPL['ismalam'];
                    $newPP->issore = $r_PPL['issore'];
                    $newPP->keteranganpakai = $r_PPL['keterangan'];
                    if (isset($r_PPL['satuanresepfk'])){
                        $newPP->satuanresepfk = $r_PPL['satuanresepfk'];
                    }
                    if (isset($r_PPL['tglkadaluarsa']) && $r_PPL['tglkadaluarsa'] != 'Invalid date' && $r_PPL['tglkadaluarsa'] != ''){
                        $newPP->tglkadaluarsa = $r_PPL['tglkadaluarsa'];
                    }
                    if (isset($r_PPL['isoutofstok'])  && $r_PPL['isoutofstok'] != ''){
                        $newPP->isoutofstok = $r_PPL['isoutofstok'];
                    }
                    //            try {
                    $newPP->save();

                }

            }
            $statusBot = false;
            $telegram_id = '' ;
            foreach ($request['data'] as $data){
                if ($data['strukorder']['ruanganfk'] == 94) {
                    $telegram_id = '-437464365';
                    $statusBot = true;
                }
                if ($data['strukorder']['ruanganfk'] == 116) {
                    $telegram_id = '-573225350';
                    $statusBot = true;
                }
                if ($data['strukorder']['ruanganfk'] == 125) {
                    $telegram_id = '-598519318';
                    $statusBot = true;
                }
                if ($data['strukorder']['ruanganfk'] == 556) {
                    $telegram_id = '-443840485';
                    $statusBot = true;
                }
                if ($data['strukorder']['ruanganfk'] == 744) {
                    //1001433844424
                    //555896135
                    $telegram_id = '-1001433844424';
                    $statusBot = true;
                }
                $tglresep = $data['strukorder']['tglresep'];
                if($statusBot){
                    $setting = $this->settingDataFixed('settingOrderTelegramLab', $idProfile);
                    if(!empty( $setting) &&  $setting == 'true'){
                        // $from =  DB::table('ruangan_m')->where('id',$request['objectruanganfk'])->first();
                        $to =  DB::table('ruangan_m')->where('id',$data['strukorder']['ruanganfk'])->first();
                        $peg=  DB::table('pegawai_m')->where('id',  $dokter2)->first();
                        $cito = '';
                        if(isset($request['iscito']) &&$request['iscito']=='true'){
                            $cito =' Cito ';
                        }
                        if($data['strukorder']['isreseppulang'] == 1){
                            $cito =' RESEP PULANG ';
                        }
                        $secret_token = "1545548931:AAHwGMJrXxGMc609WwO9e2UQTcRquu5ri-M";
                        $produks ='';
                        foreach ($prod as $key => $value) {
                            $produks = $produks ." \n ". $value;
                        }

                        $url = "https://api.telegram.org/bot" . $secret_token . "/sendMessage?parse_mode=html&chat_id=" . $telegram_id;
                        $url = $url . "&text=" . urlencode("✅ Order Baru : <b> ".$to->namaruangan.
                                "</b>  \n Pengorder : <b>".$peg->namalengkap."</b>  \n Pasien : <b>".$pasien->namapasien. " (".$pasien->nocm.") ".
                                "</b> \n Tgl Order: <b>".$tglresep.
                                "</b> \n Status : <b>".$cito."</b>  \n Nama Obat : <b>".$produks."</b> \n\n");
                        // return $url;
                        $ch = curl_init();
                        $optArray = array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true
                        );
                        curl_setopt_array($ch, $optArray);
                        $result = curl_exec($ch);
                        curl_close($ch);
                    }
                }
            }



            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan Order Pelayanan";
//
        if ($transStatus == 'true') {
            $transMessage = "Simpan Order Pelayanan Apotik Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newSO,//$noResep,,//$noResep,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Order Pelayanan Apotik Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                // "noresep" => $newSO,//$noResep,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getComboPenunjangOrder(Request $request){
//        $details=$request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPenunjang = explode(',', $this->settingDataFixed('kdDepartemenPenunjang', $idProfile));
        $kode = [];
        foreach ($deptPenunjang as $itemRanap) {
            $kode [] = (int)$itemRanap;
        }
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $idProfile)
            ->whereIn('dp.id', $kode)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuanganTujuan = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan','ru.ipaddress')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.objectdepartemenfk', $request['departemenfk'])
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $ruangan = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk', $kode)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
//        $ruangan = \DB::table('ruangan_m as ru')
//            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
//            ->where('ru.statusenabled',true)
//            ->orderBy('ru.namaruangan')
//            ->get();
//        foreach ($dataInstalasi as $item) {
//            $detail = [];
//            foreach ($ruangan as $item2) {
//                if ($item->id == $item2->objectdepartemenfk) {
//                    $detail[] = array(
//                        'id' => $item2->id,
//                        'namaruangan' => $item2->namaruangan,
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

//        $dataProduk=[];
//        if ($request['departemenfk'] == 3){
//            $dataProduk = \DB::table('produk_m as pr')
//                ->join('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
//                ->join('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
//                ->join('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
//                ->select('pr.id','pr.namaproduk')
//                // ->where('kp.id',1)
//                ->where('pr.statusenabled',true)
//                ->orderBy('pr.namaproduk')
//                ->get();
//        }
//        if ($request['departemenfk'] == 27){
//            $dataProduk = \DB::table('produk_m as pr')
//                ->join('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
//                ->join('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
//                ->join('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
//                ->select('pr.id','pr.namaproduk')
//                // ->where('kp.id',2)
//                ->where('pr.statusenabled',true)
//                ->orderBy('pr.namaproduk')
//                ->get();
//        }
//        if ($request['departemenfk'] == 25){
//            $dataProduk = \DB::table('produk_m as pr')
//                ->join('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
//                ->join('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
//                ->join('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
//                ->select('pr.id','pr.namaproduk')
//                ->where('kp.id',3)
//                ->where('pr.statusenabled',true)
//                ->orderBy('pr.namaproduk')
//                ->get();
//        }
//        if ($request['departemenfk'] == 18){
//            $dataProduk = \DB::table('produk_m as pr')
//                ->join('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
//                ->join('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
//                ->join('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
//                ->select('pr.id','pr.namaproduk')
//                ->where('kp.id',9)
//                ->where('pr.statusenabled',true)
//                ->orderBy('pr.namaproduk')
//                ->get();
//        }


        $dataTea = array(
//            'data' => $data,
            'ruangantujuan' => $dataRuanganTujuan,
            'ruangan' => $ruangan,

//            'produk' => $dataProduk,
//            'detaillogin' => $details,
            'message' => 'as@epic'
        );
        return $this->respond($dataTea);
    }

    public function getRiwayatOrderPenunjang(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = [];
        $ruanganLab = explode(',', $this->settingDataFixed('kdRuanganLabNew', $idProfile));
        $kdRuangLab = [];
        foreach ($ruanganLab as $item) {
            $kdRuangLab [] = (int)$item;
        }

        $data = \DB::table('strukorder_t as so')
            ->LEFTJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->JOIN('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
            ->LEFTJOIN('pegawai_m as p', 'p.id', '=', 'so.objectpegawaiorderfk')
//            ->LEFTJOIN('reshd as pp','pp.ono','=','so.noorder')
            ->select('so.norec', 'pd.norec as norecpd', 'pd.noregistrasi', 'so.tglorder', 'so.noorder',
                'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan', 'p.namalengkap',
                'so.noorder','pd.noregistrasi','so.keteranganlainnya','so.cito'
//                ,DB::raw('case when pp.ono is null then \'PENDING\' else \'SELESAI DIPERIKSA\' end as statusorder')
            )
            ->where('pd.kdprofile', $idProfile);
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noregistrasi']);
        }
        if (isset($request['NoCM']) && $request['NoCM'] != "" && $request['NoCM'] != "undefined") {
            $data = $data->where('pas.nocm', 'ilike', '%' . $request['NoCM'] . '%');
        }
        $data = $data->whereIn('so.objectruangantujuanfk', $kdRuangLab);
        $data = $data->where('so.statusenabled', true);
//        $data = $data->where('apd.objectruanganfk',276);
        $data = $data->orderBy('so.tglorder');
//        $data = $data->distinct();
        $data = $data->get();

        //$results =array();
        foreach ($data as $item) {
            $noorder = $item->noorder;
            $hasil = DB::select(DB::raw("select * from order_lab where no_lab='$noorder'"));
            if (count($hasil) > 0) {
                $item->statusorder = 'SELESAI DIPERIKSA';
            } else {
                $item->statusorder = 'PENDING';
            }
            $details = DB::select(DB::raw("
                            select so.tglorder,so.noorder,
                            pr.id,pr.namaproduk,op.qtyproduk
                            from strukorder_t as so
                            left join orderpelayanan_t as op on op.noorderfk = so.norec
                            left join pasiendaftar_t as pd on pd.norec=so.noregistrasifk
                            left join produk_m as pr on pr.id=op.objectprodukfk
                            left join ruangan_m as ru on ru.id=so.objectruanganfk
                            left join ruangan_m as ru2 on ru2.id=so.objectruangantujuanfk
                            left join pegawai_m as p on p.id=so.objectpegawaiorderfk
                            where so.kdprofile = $idProfile and so.noorder=:noorder"),
                array(
                    'noorder' => $item->noorder,
                )
            );
            $results[] = array(
                'noregistrasi' => $item->noregistrasi,
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'norecpd' => $item->norecpd,
//                'norecapd' => $item->norecapd,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'dokter' => $item->namalengkap,
                'statusorder' => $item->statusorder,
                'keteranganlainnya' => $item->keteranganlainnya,
                'cito' => $item->cito,

                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'mn@epic',
        );

        return $this->respond($result);
    }

    public function saveOrderPelayananLabRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detLogin = $request->all();
        if ($request['pegawaiorderfk'] == "") {
            $dokter2 = null;
        } else {
            $dokter2 = $request['pegawaiorderfk'];
        }
        DB::beginTransaction();
        try {
            if ($request['departemenfk'] == 3) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderlab', 11,'L' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'L' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 27) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderrad', 11,'R' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'R' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 25) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderok', 11,'OK' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'OK' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 5) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderpj', 11,'PJ' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'PJ' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 31) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderabm', 11,'ABM' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'ABM' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($noOrder == ''){
                $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                DB::rollBack();
                $result = array(
                    "status" => 400,
                    "message"  => $transMessage,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, $transMessage);
            }

            $dataPD = PasienDaftar::where('norec', $request['norec_pd'])->first();
            $pasien = DB::table('pasien_m')->where('id',$dataPD->nocmfk)->first();
            if ($request['norec_so'] == "") {
                $dataSO = new StrukOrder;
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->nocmfk = $dataPD->nocmfk;//$dataPD;
//            $dataSO->cito = '0';
//            $dataSO->objectdiagnosafk = true;
                $dataSO->isdelivered = 1;
                $dataSO->noorder = $noOrder;
                $dataSO->noorderintern = $noOrder;
                $dataSO->noregistrasifk = $dataPD->norec;//$dataAPD['noregistrasifk'];
                $dataSO->objectpegawaiorderfk = $dokter2;//$request['pegawaiorderfk'];
                $dataSO->qtyjenisproduk = 1;
                $dataSO->qtyproduk = $request['qtyproduk'];
                $dataSO->objectruanganfk = $request['objectruanganfk'];
                $dataSO->objectruangantujuanfk = $request['objectruangantujuanfk'];
                if ($request['departemenfk'] == 3) {
                    $dataSO->keteranganorder = 'Order Laboratorium';
                    $dataSO->objectkelompoktransaksifk = 93;
                }
                if ($request['departemenfk'] == 27) {
                    $dataSO->keteranganorder = 'Order Radiologi';
                    $dataSO->objectkelompoktransaksifk = 94;
                }
                if ($request['departemenfk'] == 25) {
                    $dataSO->keteranganorder = 'Pesan Jadwal Operasi';
                    $dataSO->objectkelompoktransaksifk = 22;
                    $dataSO->tglpelayananakhir = $request['tgloperasi'];
                    $dataSO->tglpelayananawal = $request['tgloperasi'];
                }
                if ($request['departemenfk'] == 5) {
                    $dataSO->keteranganorder = 'Pelayanan Pemulasaraan Jenazah';
                    $dataSO->objectkelompoktransaksifk = 99;
                }
                if ($request['departemenfk'] == 31) {
                    $dataSO->keteranganorder = 'Pesan Ambulan';
                    $dataSO->objectkelompoktransaksifk = 9;
                    $dataSO->tglrencana = $request['tglrencana'];
                }
                if(isset( $request['keterangan'])){
                    $dataSO->keteranganlainnya = $request['keterangan'];
                }
                $dataSO->tglorder = $request['tanggal'];
                $dataSO->totalbeamaterai = 0;
                $dataSO->totalbiayakirim = 0;
                $dataSO->totalbiayatambahan = 0;
                $dataSO->totaldiscount = 0;
                $dataSO->totalhargasatuan = 0;
                $dataSO->totalharusdibayar = 0;
                $dataSO->totalpph = 0;
                $dataSO->totalppn = 0;
                if(isset($request['iscito'])){
                    $dataSO->cito = $request['iscito'];
                }
                $dataSO->save();

                $dataSOnorec = $dataSO->norec;

                $listProduk ='';
                $prod =[];

                foreach ($request['details'] as $item) {
                    if ($request['status'] == 'bridinglangsung') {
                        $updatePP = PelayananPasien::where('norec', $item['norec_pp'])
                            ->where('kdprofile',$idProfile)
                            ->update([
                                    'strukorderfk' => $dataSOnorec
                                ]
                            );
                    }
                    $pro = DB::table('produk_m')->where('id',$item['produkfk'])->first();
                    $prod[] =$pro->namaproduk;

                    $listProduk = $listProduk .','.$pro->namaproduk;
                    $listProduk = substr($listProduk, 1, strlen($listProduk)-1);
                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if (isset($item['iscito'])) {
                        $dataOP->iscito = (float)$item['iscito'];
                    } else {
                        $dataOP->iscito = 0;
                    }

                    $dataOP->noorderfk = $dataSOnorec;
                    $dataOP->objectprodukfk = $item['produkfk'];
                    $dataOP->qtyproduk = $item['qtyproduk'];
                    $dataOP->objectkelasfk = $item['objectkelasfk'];
                    $dataOP->qtyprodukretur = 0;
                    $dataOP->objectruanganfk = $request['objectruanganfk'];
                    $dataOP->objectruangantujuanfk = $request['objectruangantujuanfk'];
                    $dataOP->strukorderfk = $dataSOnorec;
                    if (isset($item['pemeriksaanluar'])) {
                        if ($item['pemeriksaanluar'] == 1) {
                            $dataOP->keteranganlainnya = 'isPemeriksaanKeluar';
                        }
                    }

                    if (isset($item['tglrencana'])) {
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    } else {
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    $dataOP->strukorderfk = $dataSOnorec;
                    if (isset($item['dokterid']) && $item['dokterid'] != "") {
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->nourut = $item['nourut'];
                    $dataOP->save();
                }

            } else {
                $prod=[];
                $listProduk ='';
                foreach ($request['details'] as $item) {
                    $pro = DB::table('produk_m')->where('id',$item['produkfk'])->first();
                    $listProduk = $listProduk .','.$pro->namaproduk;
                    $prod[] =$pro->namaproduk;
                    $listProduk = substr($listProduk, 1, strlen($listProduk)-1);
                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if (isset($item['iscito'])) {
                        $dataOP->iscito = (float)$item['iscito'];
                    } else {
                        $dataOP->iscito = 0;
                    }

                    $dataOP->noorderfk = $request['norec_so'];
                    $dataOP->objectprodukfk = $item['produkfk'];
                    $dataOP->qtyproduk = $item['qtyproduk'];
                    $dataOP->objectkelasfk = $item['objectkelasfk'];
                    $dataOP->qtyprodukretur = 0;
                    $dataOP->objectruanganfk = $request['objectruanganfk'];
                    $dataOP->objectruangantujuanfk = $request['objectruangantujuanfk'];
                    $dataOP->strukorderfk = $request['norec_so'];

                    if (isset($item['tglrencana'])) {
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    } else {
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    if (isset($item['dokterid']) && $item['dokterid'] != "") {
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->nourut = $item['nourut'];
                    $dataOP->save();
                }
            }

            $statusBot = false;
            $telegram_id = '' ;
            if ($request['departemenfk'] == 3) {
                $telegram_id = '-580507510';
                $statusBot = true;
            }
            if ($request['departemenfk'] == 27) {
                $telegram_id = '-506890893';
                $statusBot = true;
            }
            if($statusBot){
                $setting = $this->settingDataFixed('settingOrderTelegramLab', $idProfile);
                if(!empty( $setting) &&  $setting == 'true'){
                    $from =  DB::table('ruangan_m')->where('id',$request['objectruanganfk'])->first();
                    $to =  DB::table('ruangan_m')->where('id',$request['objectruangantujuanfk'])->first();
                    $peg=  DB::table('pegawai_m')->where('id',  $dokter2)->first();
                    $cito = '';
                    if(isset($request['iscito']) &&$request['iscito']=='true'){
                        $cito =' Cito ';
                    }
                    $secret_token = "1545548931:AAHwGMJrXxGMc609WwO9e2UQTcRquu5ri-M";
                    $produks ='';
                    foreach ($prod as $key => $value) {
                        $produks = $produks ." \n ". $value;
                    }

                    $url = "https://api.telegram.org/bot" . $secret_token . "/sendMessage?parse_mode=html&chat_id=" . $telegram_id;
                    $url = $url . "&text=" . urlencode("✅ Order Baru : <b> ".$to->namaruangan." </b> \n\n Dari Ruangan : <b>".$from->namaruangan.
                            "</b>  \n Pengorder : <b>".$peg->namalengkap."</b>  \n Pasien : <b>".$pasien->namapasien. " (".$pasien->nocm.") ".
                            "</b> \n Status : <b>".$cito."</b>  \n Nama Pelayanan : <b>".$produks."</b> \n\n");
                    // return $url;
                    $ch = curl_init();
                    $optArray = array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true
                    );
                    curl_setopt_array($ch, $optArray);
                    $result = curl_exec($ch);
                    curl_close($ch);
                }
            }



            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan OrderPelayanan";
        }

        if ($transStatus == 'true') {
            if ($request['norec_so'] == "") {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
                    "strukorder" => $dataSO,
                    "as" => 'inhuman',
                );
            } else {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
//                    "strukorder" => $dataSO,
                    "as" => 'inhuman',
                );
            }
        } else {
            $transMessage = "Simpan Order Pelayanan gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusOrderPelayananLabRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            StrukOrder::where('norec', $request['norec_order'])->where('kdprofile', $idProfile)->update
            (['statusenabled' => false]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Terhapus";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                    "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRiwayatOrderRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = [];
        $ruanganRad = explode(',', $this->settingDataFixed('kdRuanganRadiologiNew',$idProfile));
        $kdRuangRad = [];
        foreach ($ruanganRad as $item) {
            $kdRuangRad [] = (int)$item;
        }
        $data = \DB::table('strukorder_t as so')
//            ->LEFTJOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->LEFTJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->LEFTJOIN('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
            ->LEFTJOIN('pegawai_m as p', 'p.id', '=', 'so.objectpegawaiorderfk')
//            ->LEFTJOIN('ris_order as pp','pp.order_no','=','so.noorder')
            ->select('so.norec', 'so.tglorder', 'so.noorder', 'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan', 'p.namalengkap', 'so.statusorder',
                'pd.noregistrasi', 'so.objectruangantujuanfk', 'so.statusorder','so.keteranganlainnya','so.cito'
//                DB::raw('case when pp.order_no is null then \'PENDING\' else \'SELESAI DIPERIKSA\' end as statusorder')
            )
            ->where('so.kdprofile', $idProfile);
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noregistrasi']);
        }
        if (isset($request['NoCM']) && $request['NoCM'] != "" && $request['NoCM'] != "undefined") {
            $data = $data->where('ps.nocm', '=', $request['NoCM']);
        }
        $data = $data->whereIn('so.objectruangantujuanfk', $kdRuangRad);
        $data = $data->where('so.statusenabled', true);
        $data = $data->orderBy('so.tglorder');
//        $data = $data->distinct();
        $data = $data->get();
        $status = '';
        foreach ($data as $item) {
            $risorder = array();
            if ($item->objectruangantujuanfk != $this->settingDataFixed('kdRuanganElektromedik',$idProfile)) {
                //$risorder = RisOrder::where('order_no', $item->noorder)->get();
                $risorder = RisOrder::where('order_no', $item->noorder)->where('study_remark','!=','-')->get(); // syamsu
                if (count($risorder) > 0) {
                    $status = 'Sudah diproses';
                } else {
                    $status = 'Belum diproses';
                }
            } else {
                if ($item->statusorder != null) {
                    $status = 'Verifikasi';
                } else {
                    $status = 'Belum Verifikasi';
                }
            }

            $details = DB::select(DB::raw("
                            select  so.tglorder,so.noorder, op.norec as norecopfk,
                            pr.id, pr.namaproduk, op.qtyproduk,
                            so.norec, pp.norec as norec_pp, hr.norec as norec_hr
                            from strukorder_t as so
                            left join pelayananpasien_t as pp on pp.strukorderfk = so.norec
                            left join hasilradiologi_t as hr on hr.pelayananpasienfk = pp.norec
                            left join orderpelayanan_t as op on op.noorderfk = so.norec
                            and op.objectprodukfk = pp.produkfk  --syamsu biar tdk double
                            left join pasiendaftar_t as pd on pd.norec=so.noregistrasifk
                            left join produk_m as pr on pr.id=op.objectprodukfk
                            left join pegawai_m as p on p.id=so.objectpegawaiorderfk
                            where so.kdprofile = $idProfile and so.noorder=:noorder"),
                // left join ruangan_m as ru on ru.id=so.objectruanganfk
                // left join ruangan_m as ru2 on ru2.id=so.objectruangantujuanfk
                array(
                    'noorder' => $item->noorder,
                )
            );

            $results[] = array(
                'noregistrasi' => $item->noregistrasi,
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'dokter' => $item->namalengkap,
                'statusorder' => $status,
                'keteranganlainnya' =>  $item->keteranganlainnya,
                'cito' =>  $item->cito,
                'details' => $details,
                'risorder' => $risorder
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }

    public function getComboSurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiLogin = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id', $dataLogin['userData']['id'])
            ->first();

        $AsaScore = \DB::table('asascore_m as asa')
            ->select('asa.id', 'asa.asascore')
            ->where('asa.statusenabled', true)
            ->get();

        $JenisOperasi = \DB::table('jenisoperasi_m as jo')
            ->select('jo.id', 'jo.jenisoperasi')
            ->where('jo.kdprofile', 11)
            ->where('jo.statusenabled', true)
            ->get();

        $TindakanOperasi = \DB::table('tindakanoperasi_m as to')
            ->select('to.id', 'to.tindakanoperasi as namaproduk')
            ->where('to.kdprofile', 21  )
            ->where('to.statusenabled', true)
            ->get();

        $JenisWaktu = \DB::table('jeniswaktu_m as jw')
            ->select('jw.id', 'jw.jeniswaktu')
            ->where('jw.statusenabled', true)
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->whereIn('ru.objectdepartemenfk', [16, 17, 25, 35])
//            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $Infeksi = \DB::table('infeksinosokomial_m as ru')
            ->select('ru.id', 'ru.infeksinosokomial as infeksi')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.id')
            ->get();

        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd', 'spd.objectprodukfk', '=', 'pr.id')
            ->select('pr.id', 'pr.kdproduk as kdsirs', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar')
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.statusenabled', true)
            ->where('jp.id', 97)
            ->where('pr.isantibiotik', 1)
            ->groupBy('pr.id', 'pr.kdproduk', 'pr.namaproduk', 'ss.id', 'ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

//
        $result = array(
            'asascore' => $AsaScore,
            'jenisoperasi' => $JenisOperasi,
            'ruangan' => $ruanganRi,
            'pegawailogin' => $dataPegawaiLogin,
            'jeniswaktu' => $JenisWaktu,
            'tindakanoperasi' => $TindakanOperasi,
            'infeksi' => $Infeksi,
            'produk' => $dataProduk,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getHistorySurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('surveilans_t as sv')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'sv.noregistrasifk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'sv.norec_apd')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select(DB::raw("sv.norec,sv.tglsurveilans,sv.nosurvailens,pm.nocm || ' / ' || pd.noregistrasi as nocmregis,
			                  pm.namapasien,ru.namaruangan"))
            ->where('sv.kdprofile', $idProfile)
            ->whereNull('br.norec')
            ->where('sv.statusenabled', true);

        $filter = $request->all();
        if (isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('pm.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }
        if (isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if (isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('pm.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getHistoryDetailSurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $dataHead = \DB::table('surveilans_t as sv')
            ->leftJoin('diagnosa_m as dg', 'dg.id', '=', 'sv.diagnosamasukfk')
            ->leftJoin('diagnosa_m as dg1', 'dg1.id', '=', 'sv.diagnosakeluarfk')
            ->leftJoin('jeniswaktu_m as jw', 'jw.id', '=', 'sv.jeniswaktufk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'sv.ruanganfk')
            ->select(DB::raw("sv.norec,sv.tglsurveilans,sv.nosurvailens,sv.diagnosamasukfk,dg.kddiagnosa,dg.namadiagnosa,
                             sv.keterangandiagnosamasuk,sv.diagnosakeluarfk,dg1.kddiagnosa as kddiagnosakeluar,dg1.namadiagnosa as namadiagnosakeluar,
                             sv.keterangandiagnosakeluar,sv.antibiotikprofillaksis,sv.dosis,sv.ruanganfk,ru.namaruangan,
                             sv.jeniswaktufk,jw.jeniswaktu,sv.culturdarah,sv.cultururine,sv.cultursputum"))
            ->where('sv.kdprofile', $idProfile)
            ->where('sv.norec', $filter['noRec'])
            ->where('sv.statusenabled', true)
            ->get();

        $dataFaktorResiko = \DB::table('surveilansfaktorresiko_t as sv')
            ->Join('surveilans_t as svh', 'svh.norec', '=', 'sv.nosurvailensfk')
            ->select(DB::raw("sv.statusgizi,sv.dm,sv.guladarah,sv.merokok,
                             sv.obesitas,sv.pemeriksaankultur,sv.temp,sv.hasilkultur,
                             sv.tglinput"))
            ->where('sv.kdprofile', $idProfile)
            ->where('svh.norec', $filter['noRec'])
            ->where('svh.statusenabled', true)
            ->get();

        $dataOperasi = \DB::table('surveilansoperasi_t as sv')
            ->Join('surveilans_t as svh', 'svh.norec', '=', 'sv.nosurvailensfk')
            ->leftJoin('diagnosa_m as dg', 'dg.id', '=', 'sv.diagnosafk')
            ->leftJoin('produk_m as pro', 'pro.id', '=', 'sv.produkfk')
            ->leftJoin('asascore_m as asa', 'asa.id', '=', 'sv.asascorefk')
            ->leftJoin('tindakanoperasi_m as op', 'op.id', '=', 'sv.tindakanoperasifk')
            ->leftJoin('jenisoperasi_m as jo', 'jo.id', '=', 'sv.jenisoperasifk')
            ->select(DB::raw("sv.diagnosafk,dg.kddiagnosa,dg.namadiagnosa,sv.keterangandiagnosa,
                             sv.produkfk,pro.namaproduk,sv.tgloperasi,sv.jamoperasi,sv.menitoperasi,
                             sv.asascorefk,asa.asascore,sv.tindakanoperasifk as idtindakan,op.tindakanoperasi,
                             sv.tglinput,sv.jenisoperasifk,jo.jenisoperasi,sv.score,sv.penyakitpenyerta,
                             sv.implant"))
            ->where('sv.kdprofile', $idProfile)
            ->where('svh.norec', $filter['noRec'])
            ->where('svh.statusenabled', true)
            ->get();

        $dataFrd = \DB::table('surveilansfrd_t as sv')
            ->Join('surveilans_t as svh', 'svh.norec', '=', 'sv.nosurvailensfk')
            ->leftJoin('tindakanoperasi_m as op', 'op.id', '=', 'sv.tindakanoperasifk')
            ->leftJoin('infeksinosokomial_m as inf', 'inf.id', '=', 'sv.infeksifk')
            ->select(DB::raw("sv.norec,sv.tglmulai,sv.tglakhir,sv.tindakanoperasifk as idtindakan,op.tindakanoperasi as tindakan,
		                     sv.tglinfeksi,sv.infeksifk as idinfeksi,inf.infeksinosokomial as infeksi,sv.score,sv.status,
			                 sv.idsah,sv.idbatal,sv.lamapasang,sv.hasilkultur"))
            ->where('sv.kdprofile', $idProfile)
            ->where('svh.norec', $filter['noRec'])
            ->where('svh.statusenabled', true)
            ->get();

        $dataAnti = \DB::table('surveilansantibiotik_t as sv')
            ->Join('surveilans_t as svh', 'svh.norec', '=', 'sv.nosurvailensfk')
            ->leftJoin('produk_m as op', 'op.id', '=', 'sv.tindakanoperasifk')
            ->select(DB::raw("sv.norec,sv.tglmulai,sv.tglakhir,sv.tindakanoperasifk as idantibiotika,op.namaproduk as antibiotika,
			                 sv.dosis,sv.metodepemberian,sv.status,sv.sah as idsah,sv.batal as idbatal"))
            ->where('sv.kdprofile', $idProfile)
            ->where('svh.norec', $filter['noRec'])
            ->where('svh.statusenabled', true)
            ->get();

        $result = array(
            'datahead' => $dataHead,
            'faktorresiko' => $dataFaktorResiko,
            'operasi' => $dataOperasi,
            'datafrd' => $dataFrd,
            'dataanti' => $dataAnti,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveDataSurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id', $dataLogin['userData']['id'])
            ->first();

        try {

            $dataHead = $request['datahead'];
            $dataFaktorResiko = $request['faktorresiko'];
            $dataOperasi = $request['operasi'];
            $dataFrd = $request['faktorResikorawat'];
            $dataAnti = $request['antibiotika'];
            if ($dataHead['norec'] == '') {
                $noSurvailens = $this->generateCode(new Surveilans(), 'nosurvailens', 12, 'SV-' . $this->getDateTime()->format('ym'), $idProfile);
                $dataSurveilans = new Surveilans();
                $dataSurveilans->norec = $dataSurveilans->generateNewId();
                $dataSurveilans->kdprofile = $idProfile;
                $dataSurveilans->statusenabled = 1;
                $dataSurveilans->nosurvailens = $noSurvailens;
                $dataSurveilans->noregistrasifk = $dataHead['norec_pd'];
                $dataSurveilans->norec_apd = $dataHead['norec_apd'];
            } else {
                $dataSurveilans = Surveilans::where('norec', $dataHead['norec'])->where('kdprofile', $idProfile)->first();
            }
            $dataSurveilans->tglsurveilans = $dataHead['tglsurveilans'];
            $dataSurveilans->diagnosamasukfk = $dataHead['diagnosamasukfk'];
            $dataSurveilans->diagnosakeluarfk = $dataHead['diagnosakeluarfk'];
            $dataSurveilans->keterangandiagnosamasuk = $dataHead['keterangandiagnosamasuk'];
            $dataSurveilans->keterangandiagnosakeluar = $dataHead['keterangandiagnosakeluar'];
            $dataSurveilans->antibiotikprofillaksis = $dataHead['antibiotikprofillaksis'];
            $dataSurveilans->dosis = $dataHead['dosis'];
            $dataSurveilans->ruanganfk = $dataHead['ruanganfk'];
            $dataSurveilans->jeniswaktufk = $dataHead['jeniswaktufk'];
            $dataSurveilans->culturdarah = $dataHead['culturdarah'];
            $dataSurveilans->cultururine = $dataHead['cultururine'];
            $dataSurveilans->cultursputum = $dataHead['cultursputum'];
            $dataSurveilans->save();
            $norecSurveilan = $dataSurveilans->norec;

            if ($dataFaktorResiko != "") {
                if ($dataHead['norec'] == "") {
                    $dataSurveilansFR = new SurveilansFaktorResiko();
                    $dataSurveilansFR->kdprofile = $idProfile;
                    $dataSurveilansFR->statusenabled = true;
                    $dataSurveilansFR->norec = $dataSurveilansFR->generateNewId();
                    $dataSurveilansFR->nosurvailensfk = $norecSurveilan;
                } else {
                    $dataSurveilansFR = SurveilansFaktorResiko::where('nosurvailensfk', $dataHead['norec'])->where('kdprofile', $idProfile)->first();
                }
                $dataSurveilansFR->statusgizi = $dataFaktorResiko['statusgizi'];
                $dataSurveilansFR->dm = $dataFaktorResiko['dm'];
                $dataSurveilansFR->guladarah = $dataFaktorResiko['guladarah'];
                $dataSurveilansFR->merokok = $dataFaktorResiko['merokok'];
                $dataSurveilansFR->obesitas = $dataFaktorResiko['obesitas'];
                $dataSurveilansFR->pemeriksaankultur = $dataFaktorResiko['pemeriksaankultur'];
                $dataSurveilansFR->temp = $dataFaktorResiko['temp'];
                $dataSurveilansFR->hasilkultur = $dataFaktorResiko['hasilkultur'];
                $dataSurveilansFR->tglinput = $dataFaktorResiko['tglinput'];
                $dataSurveilansFR->save();
            }

            if ($dataOperasi != "") {
                if ($dataHead['norec'] == "") {
                    $dataSurveilansOPM = new SurveilansOperasi();
                    $dataSurveilansOPM->kdprofile = $idProfile;
                    $dataSurveilansOPM->statusenabled = 1;
                    $dataSurveilansOPM->norec = $dataSurveilansOPM->generateNewId();
                    $dataSurveilansOPM->nosurvailensfk = $norecSurveilan;
                } else {
                    $dataSurveilansOPM = SurveilansOperasi::where('nosurvailensfk', $dataHead['norec'])->where('kdprofile', $idProfile)->first();
                }
                $dataSurveilansOPM->diagnosafk = $dataOperasi['diagnosafk'];
                $dataSurveilansOPM->keterangandiagnosa = $dataOperasi['keterangandiagnosa'];
                $dataSurveilansOPM->produkfk = $dataOperasi['produkfk'];
                $dataSurveilansOPM->tgloperasi = $dataOperasi['tgloperasi'] != 'Invalid date' ? $dataOperasi['tgloperasi'] : null;
                $dataSurveilansOPM->jamoperasi = $dataOperasi['jamoperasi'];
                $dataSurveilansOPM->menitoperasi = $dataOperasi['menitoperasi'];
                $dataSurveilansOPM->asascorefk = $dataOperasi['asascorefk'];
//                        $dataSurveilansOPM->tindakanoperasifk = $dataOperasi['tindakanoperasifk'];
                $dataSurveilansOPM->tglinput = $dataOperasi['tglinput'];
                $dataSurveilansOPM->jenisoperasifk = $dataOperasi['jenisoperasifk'];
                $dataSurveilansOPM->score = $dataOperasi['score'];
                $dataSurveilansOPM->penyakitpenyerta = $dataOperasi['penyakitpenyerta'];
                $dataSurveilansOPM->implant = $dataOperasi['implant'];
                $dataSurveilansOPM->save();
            }

            if ($dataFrd != "") {
                foreach ($dataFrd as $hideung) {
                    if ($hideung['norec'] == "") {
                        $dataSaveFRD = new SurveilansFrd();
                        $dataSaveFRD->kdprofile = $idProfile;
                        $dataSaveFRD->statusenabled = 1;
                        $dataSaveFRD->norec = $dataSaveFRD->generateNewId();
                        $dataSaveFRD->nosurvailensfk = $norecSurveilan;
                    } else {
                        $dataSaveFRD = SurveilansFrd::where('norec', $hideung['norec'])
                            ->where('nosurvailensfk', $dataHead['norec'])
                            ->first();
                    }
//                    return $this->respond($hideung['tglakhir'] != "Invalid date");
                    if (isset($hideung['tglmulai']) && $hideung['tglmulai'] != "Invalid date") {
                        $dataSaveFRD->tglmulai = $hideung['tglmulai'];
                    }

                    if (isset($hideung['tglakhir']) && $hideung['tglakhir'] != "Invalid date") {
                        $dataSaveFRD->tglakhir = $hideung['tglakhir'];
                    }

                    if (isset($hideung['tglinfeksi']) && $hideung['tglinfeksi'] != "Invalid date") {
                        $dataSaveFRD->tglinfeksi = $hideung['tglinfeksi'];
                    }

                    $dataSaveFRD->tindakanoperasifk = $hideung['idtindakan'];
                    $dataSaveFRD->infeksifk = $hideung['idinfeksi'];
                    $dataSaveFRD->score = $hideung['score'];
                    $dataSaveFRD->status = $hideung['status'];
                    $dataSaveFRD->idsah = $hideung['idsah'];
                    $dataSaveFRD->idbatal = $hideung['idbatal'];
                    $dataSaveFRD->lamapasang = $hideung['lamapasang'];
                    $dataSaveFRD->hasilkultur = $hideung['hasilkultur'];
                    $dataSaveFRD->save();
                }
            }

            if ($dataAnti != "") {
                if (count($dataAnti) == 0) {
                    $dataSaveAnti = SurveilansAntibiotik::where('nosurvailensfk', $dataHead['norec'])
                        // ->where('nosurvailensfk',$dataHead['norec'])
                        ->delete();
                }
                foreach ($dataAnti as $hideung) {
                    if ($hideung['norec'] == "") {
                        $dataSaveAnti = new SurveilansAntibiotik();
                        $dataSaveAnti->kdprofile = $idProfile;
                        $dataSaveAnti->statusenabled = 1;
                        $dataSaveAnti->norec = $dataSaveAnti->generateNewId();
                        $dataSaveAnti->nosurvailensfk = $norecSurveilan;
                    } else {
                        $dataSaveAnti = SurveilansAntibiotik::where('norec', $hideung['norec'])
                            ->where('nosurvailensfk', $dataHead['norec'])
                            ->first();
                    }

                    if (isset($hideung['tglmulai']) && $hideung['tglmulai'] != "Invalid date") {
                        $dataSaveAnti->tglmulai = $hideung['tglmulai'];
                    }

                    if (isset($hideung['tglakhir']) && $hideung['tglakhir'] != "Invalid date") {
                        $dataSaveAnti->tglakhir = $hideung['tglakhir'];
                    }

                    $dataSaveAnti->tindakanoperasifk = $hideung['idantibiotika'];
                    $dataSaveAnti->dosis = $hideung['dosis'];
                    $dataSaveAnti->metodepemberian = $hideung['metodepemberian'];
                    $dataSaveAnti->status = $hideung['status'];
                    $dataSaveAnti->sah = $hideung['idsah'];
                    $dataSaveAnti->batal = $hideung['idbatal'];
                    $dataSaveAnti->save();
                }
            }

            ## Logging User
            if ($dataHead['norec'] == "") {
                $newId = LoggingUser::max('id');
                $newId = $newId + 1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile = $idProfile;
                $logUser->statusenabled = true;
                $logUser->jenislog = 'Input Data Surveilans';
                $logUser->noreff = $norecSurveilan;
                $logUser->referensi = 'norec surveilans';
                $logUser->objectloginuserfk = $dataLogin['userData']['id'];
                $logUser->tanggal = $tglAyeuna;
                $logUser->save();
            } else {
                $newId = LoggingUser::max('id');
                $newId = $newId + 1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile = 0;
                $logUser->statusenabled = true;
                $logUser->jenislog = 'Edit Data Surveilans';
                $logUser->noreff = $norecSurveilan;
                $logUser->referensi = 'norec surveilans';
                $logUser->objectloginuserfk = $dataLogin['userData']['id'];
                $logUser->tanggal = $tglAyeuna;
                $logUser->save();
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusDataSurveilans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id', $dataLogin['userData']['id'])
            ->first();
        try {

            $Kel = Surveilans::where('norec', $request['norec'])
                ->update([
                    'statusenabled' => '0',
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId + 1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile = $idProfile;
            $logUser->statusenabled = true;
            $logUser->jenislog = 'Batal Surveilans';
            $logUser->noreff = $request['data']['norec'];
            $logUser->referensi = 'norec surveilans';
            $logUser->objectloginuserfk = $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataECG(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $noRegistrasi = $request['noregistrasi'];
        $data = \DB::table('eecg_t as emr')
            ->select('emr.*')
//            ->where('emr.customerid','=',$request['custid'])
            // ->where('emr.kdprofile', $idProfile)
            ->orderBy('emr.urut');
        if (isset($request['custid']) && $request['custid'] != '') {
            $data = $data->where('emr.customerid', $request['custid']);
        }
        if (isset($request['datesend']) && $request['datesend'] != '') {
            $data = $data->where('emr.norec', 'ilike', $request['datesend'] . '%');
        }
        if (isset($request['vis']) && $request['vis'] != '') {
            if ($request['vis'] == 'true') {
                $data = $data->where('emr.statusenabled', $request['vis']);
            } else {
                $data = $data->whereNull('emr.statusenabled');
            }
        }
//        $data= $data->whereNull('emr.kodeexternal');
        $data = $data->get();
//            ->where('statusenabled','true')
//            ->get();


        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function SaveTransaksiECG(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
//        $dataReq = $request->all();

        try {

            $data1 = EECG::where('norec', 'ilike', $request['head']['datesend'] . '%')
                ->where('customerid', $request['head']['customerid'])
                ->where('kunci', 'expertise')
                ->update([
                        // 'kdprofile' => $idProfile,
                        'nilai' => $request['head']['expertise']]
                );

            $data1 = EECG::where('norec', 'ilike', $request['head']['datesend'] . '%')
//                EECG::where('datesend', 'ilike', $request['head']['datesend'] . '%')
//                ->where('customerid', $request['head']['customerid'])
                // ->where('kdprofile', $idProfile)
                ->where('kunci', 'CustomerID')
                ->update([
                        'nilai' => $request['head']['customerid']]
                );

//            $data3 = EECG::where('customerid', $request['head']['customerid'])
//                ->where('statusenabled', true)
//                ->update([
//                        'kodeexternal' => 'old']
//                );
            $data2 = EECG::where('norec', 'ilike', $request['head']['datesend'] . '%')
//                EECG::where('datesend', 'ilike', $request['head']['datesend'] . '%')
//              ->where('customerid', $request['head']['customerid'])
//                ->where('kunci', $request->kunci)
                // ->where('kdprofile', $idProfile)
                ->update([
//                        'kdprofile' => $idProfile,
                        'statusenabled' => true,
                        'dateverif' => $this->getDateTime()->format('Y-m-d H:i:s'),
                        'customerid' => $request['head']['customerid']
                    ]
                );




            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Saving ECG ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $sms = $this->sendChatAPI($idProfile,$request);
            $result = array(
                "status" => 201,
                "data" => $data1,
                // 'smsg' =>$response,
                "data2" => $data2,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    function sendSMS($nomor,$contena) {
        $auth = md5('ECGGENGGAM'.'EC3G348'.$nomor);
        // $mobile = '082211333013';
        $username = 'ECGGENGGAM';

        $url ="http://send.smsmasking.co.id:8080/web2sms/api/sendSMS.aspx?username=".$username."&mobile=".$nomor."&message=".$contena."&auth=".$auth;
        // return $auth;
        // $urlku="http://<server_name>:8080/web2sms/creditsleft.aspx?username=xxx&password=xxx";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            // CURLOPT_SSL_VERIFYHOST => 0,
            // CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $response;

    }
    public function getDaftarECG(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $custid = '';
        if (isset($request['custid']) && $request['custid'] != '') {
            $custid = "where  customerid = '" . $request['custid'] . "'";
        }
        $data = DB::select(DB::raw("
              select distinct  SUBSTRING(norec,1,12) as datesend,customerid
              from eecg_t
               $custid order by SUBSTRING(norec,1,12)  desc limit 20"
        ));

        return $this->respond($data);
    }

    public function getOrderOK(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = [];
        $data = \DB::table('strukorder_t as so')
            ->LEFTJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->JOIN('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
            ->LEFTJOIN('pegawai_m as p', 'p.id', '=', 'so.objectpegawaiorderfk')
            ->LEFTJOIN('reshd as pp', 'pp.ono', '=', 'so.noorder')
            ->select('so.norec', 'pd.norec as norecpd', 'pd.noregistrasi', 'so.tglorder', 'so.noorder',
                'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan', 'p.namalengkap', 'pp.ono',
                DB::raw('case when so.statusorder is null then \'PENDING\' else \'Verifikasi\' end as statusorder')
            )
            ->where('so.kdprofile', $idProfile);
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noregistrasi']);
        }
        if (isset($request['NoCM']) && $request['NoCM'] != "" && $request['NoCM'] != "undefined") {
            $data = $data->where('pas.nocm', 'ilike', '%' . $request['NoCM'] . '%');
        }
        $data = $data->where('ru2.objectdepartemenfk', 25);
        $data = $data->where('so.statusenabled', true);
//        $data = $data->where('apd.objectruanganfk',276);
        $data = $data->orderBy('so.tglorder');
//        $data = $data->distinct();
        $data = $data->get();

        //$results =array();
        foreach ($data as $item) {
            $details = DB::select(DB::raw("
                            select so.tglorder,so.noorder,
                            pr.id,pr.namaproduk,op.qtyproduk
                            from strukorder_t as so
                            left join orderpelayanan_t as op on op.noorderfk = so.norec
                            left join pasiendaftar_t as pd on pd.norec=so.noregistrasifk
                            left join produk_m as pr on pr.id=op.objectprodukfk
                            left join ruangan_m as ru on ru.id=so.objectruanganfk
                            left join ruangan_m as ru2 on ru2.id=so.objectruangantujuanfk
                            left join pegawai_m as p on p.id=so.objectpegawaiorderfk
                            where so.kdprofile = $idProfile and so.noorder=:noorder"),
                array(
                    'noorder' => $item->noorder,
                )
            );
            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'norecpd' => $item->norecpd,
//                'norecapd' => $item->norecapd,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'dokter' => $item->namalengkap,
                'statusorder' => $item->statusorder,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'mn@epic',
        );

        return $this->respond($result);
    }

    public function getPegawaiParts(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request->all();
        $dataProduk = \DB::table('pegawai_m')
            ->select('id', 'namalengkap')
            ->where('kdprofile', $idProfile)
            ->where('statusenabled', true)
            ->orderBy('namalengkap');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('namalengkap', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        if (isset($req['namalengkap']) &&
            $req['namalengkap'] != "" &&
            $req['namalengkap'] != "undefined") {
            $dataProduk = $dataProduk->where('namalengkap', 'ilike', '%' . $req['namalengkap'] . '%');
        };
        if (isset($req['id']) &&
            $req['id'] != "" &&
            $req['id'] != "undefined") {
            $dataProduk = $dataProduk->where('id', $req['id']);
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getComboJensiDiagnosaPart(Request $request){
        $req = $request->all();
        $dataProduk = \DB::table('jenisdiagnosa_m')
            ->select('id as value', 'jenisdiagnosa as text')
            ->where('statusenabled', true)
//            ->where('objectjenispegawaifk',1)
            ->orderBy('jenisdiagnosa');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('jenisdiagnosa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataComboDiagnosa9Part(Request $request){
        $req = $request->all();
        $data = \DB::table('diagnosatindakan_m')
            ->select('id as value', 'kddiagnosatindakan', 'namadiagnosatindakan as text')
            ->where('statusenabled', true)
            ->orderBy('kddiagnosatindakan');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $data = $data->where('kddiagnosatindakan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
            $data = $data->orwhere('namadiagnosatindakan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $data = $data->take(10);
        $data = $data->get();

        $dt = [];
        foreach ($data as $item) {
            $dt[] = array(
                'value' => $item->value,
                'text' => $item->kddiagnosatindakan . ' ' . $item->text,
            );
        }

        return $this->respond($dt);
    }

    public function hapusOrderResep(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $so = StrukOrder::where('norec', $request['norec'])->where('kdprofile', $idProfile)->first();
            if ($so->statusorder == 5) {
                $transMessage = "Tidak Bisa dihapus sudah Di Verifikasi";
                $result = array(
                    "status" => 400,
                    "message" => $transMessage,
                    "as" => 'er@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, $transMessage);
            }
            StrukOrder::where('norec', $request['norec'])->update
            (['statusenabled' => false]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Terhapus";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                    "strukorder" => $dataSO,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusEMRTransaksi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            EMRPasien::where('norec', $request['norec'])
                ->where('kdprofile',$idProfile)
                ->where('emrfk', $request['emrfk'])
                ->update(['statusenabled' => false]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Terhapus";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                    "strukorder" => $dataSO,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getObatSeringDiresepkanDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        // $data = DB::select(DB::raw('select top 10 * from (select prd.id,prd.namaproduk , count(prd.id) as jml
        //      from orderpelayanan_t as op
        //     join strukorder_t as so on so.norec= op.noorderfk
        //     join produk_m as prd on prd.id = op.objectprodukfk
        //     join pegawai_m as pg on pg.id = so.kddokter
        //     where op.kdprofile = $idProfile and pg.objectjenispegawaifk =1
        //     group by prd.namaproduk,prd.id
        //     ) as x order by x.jml desc '));
        $data = DB::select(DB::raw("select * from (select prd.id,prd.namaproduk , count(prd.id) as jml
             from orderpelayanan_t as op
            join strukorder_t as so on so.norec= op.noorderfk
            join produk_m as prd on prd.id = op.objectprodukfk
            join pegawai_m as pg on cast(pg.id as text)= so.kddokter
            where op.kdprofile = '$idProfile' and pg.objectjenispegawaifk =1
            group by prd.namaproduk,prd.id
            LIMIT 10
            ) as x order by x.jml desc  "));
        $result = array(
            'data' => $data,
            'as' => 'er@epic'
        );
        return $this->respond($result);
    }

    public function getDataComboKelompokPaisnePart(Request $request){
        $req = $request->all();
        $data = \DB::table('kelompokpasien_m as pr')
            ->select('pr.id as value', 'pr.kelompokpasien as text')
            ->where('pr.statusenabled', true)
            ->orderBy('pr.kelompokpasien');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $data = $data->where('pr.kelompokpasien', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getDataComboKelasPart(Request $request){
        $req = $request->all();
        $data = \DB::table('kelas_m as pr')
            ->select('pr.id as value', 'pr.namakelas as text')
            ->where('pr.statusenabled', true)
            ->orderBy('pr.namakelas');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $data = $data->where('pr.namakelas', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getDataComboAgama(Request $request){
        $req = $request->all();
        $dataProduk = \DB::table('agama_m')
            ->select('id as value', 'agama as text')
            ->where('statusenabled', true)
            ->orderBy('agama');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('agama', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function SimpanDelegasiPemberiObat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $r_SR = $request["0"]['strukorder'];
        try {

            if ($r_SR['norec'] == '') {
                $newSO = new FormulisObat();
                $norecSO = $newSO->generateNewId();
                $newSO->norec = $norecSO;
                $newSO->kdprofile = $idProfile;
                $newSO->statusenabled = true;
            } else {
                $newSO = StrukOrder::where('norec', $r_SR['norec'])->first();
                $noOrder = $newSO->noorder;
                FormulisObatDetail::where('formulirobatfk', $newSO->norec)->delete();
            }
            $newSO->norec_pd = $r_SR['norec_pd'];
            $newSO->norec_apd = $r_SR['norec_apd'];
            $newSO->nocmfk = $r_SR['nocmfk'];
            $newSO->tglpelayanan = $r_SR['tglpelayanan'];
            $newSO->pegawaifarmasifk = $r_SR['pegawaifarmasifk'];
            $newSO->perawatfk = $r_SR['perawatfk'];
            $newSO->alergi = $r_SR['alergi'];
            $newSO->save();
            $norec_SR = $newSO->norec;


            //## PelayananPasien
            $r_PP = $request["0"]['detail'];
            foreach ($r_PP as $r_PPL) {
                $newPP = new FormulisObatDetail();
                $norecPP = $newPP->generateNewId();
                $newPP->norec = $norecPP;
                $newPP->kdprofile = $idProfile;
                $newPP->statusenabled = true;
                $newPP->produkfk = $r_PPL['produkfk'];
                $newPP->tglpelayanan = $r_SR['tglpelayanan'];
                $newPP->routefk = $r_PPL['routefk'];
                $newPP->dosis = $r_PPL['dosis'];
                $newPP->ispagi = $r_PPL['ispagi'];
                $newPP->issiang = $r_PPL['issiang'];
                $newPP->ismalam = $r_PPL['ismalam'];
                $newPP->issore = $r_PPL['issore'];
                $newPP->pegawaifarmasifk = $r_SR['pegawaifarmasifk'];
                $newPP->perawatfk = $r_SR['perawatfk'];
                $newPP->formulirobatfk = $norec_SR;
                $newPP->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newSO,//$noResep,,//$noResep,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "noresep" => $newSO,//$noResep,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarDelegasiObat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('formulirobat_t as fr')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'fr.norec_pd')
            ->JOIN('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'fr.pegawaifarmasifk')
            ->LEFTJOIN('pegawai_m as pg1', 'pg1.id', '=', 'fr.perawatfk')
            ->select(DB::raw("fr.norec,pd.noregistrasi,pm.nocm,pm.namapasien,fr.tglpelayanan,fr.pegawaifarmasifk,
                              fr.perawatfk,pg.namalengkap as pegawaifarmasi,pg1.namalengkap as perawat,fr.alergi"))
            ->where('fr.kdprofile', $idProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('fr.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('fr.tglpelayanan', '<=', $tgl);
        }
        if (isset($request['Noreg']) && $request['Noreg'] != "" && $request['Noreg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['Noreg']);
        }
        if (isset($request['noRM']) && $request['noRM'] != "" && $request['noRM'] != "undefined") {
            $data = $data->where('pm.nocm', 'ilike', '%' . $request['noRM'] . '%');
        }
        $data = $data->where('fr.statusenabled', true);
        $data = $data->get();

        foreach ($data as $item) {
            $details = DB::select(DB::raw("select frd.norec,frd.produkfk,pr.namaproduk,frd.routefk,rf.name as route,
                                            frd.dosis,frd.ispagi,frd.issiang,frd.ismalam,frd.issore,frd.pegawaifarmasifk,
                                            frd.perawatfk,pg.namalengkap as pegawaifarmasi,pg1.namalengkap as perawat, frd.keterangan
                                from formulirobatdetail_t as frd
                                left JOIN produk_m as pr on pr.id=frd.produkfk
                                left JOIN routefarmasi as rf on rf.id=frd.routefk
                                left JOIN pegawai_m as pg on pg.id=frd.pegawaifarmasifk
                                left JOIN pegawai_m as pg1 on pg1.id=frd.perawatfk
                                where frd.kdprofile = $idProfile and frd.formulirobatfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tglpelayanan' => $item->tglpelayanan,
                'norec' => $item->norec,
                'noregistrasi' => $item->noregistrasi,
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'pegawaifarmasifk' => $item->pegawaifarmasifk,
                'perawatfk' => $item->perawatfk,
                'pegawaifarmasi' => $item->pegawaifarmasi,
                'perawat' => $item->perawat,
                'details' => $details,
            );
        }
        $result = array(
            'daftar' => $result,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDelegasiObat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $Norec = $request['Norec'];
        $data = \DB::table('formulirobat_t as fr')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'fr.norec_pd')
            ->JOIN('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'fr.pegawaifarmasifk')
            ->LEFTJOIN('pegawai_m as pg1', 'pg1.id', '=', 'fr.perawatfk')
            ->select(DB::raw("fr.norec,pd.noregistrasi,pm.nocm,pm.namapasien,fr.tglpelayanan,fr.pegawaifarmasifk,
                              fr.perawatfk,pg.namalengkap as pegawaifarmasi,pg1.namalengkap as perawat,fr.alergi"))
            ->where('fr.kdprofile',$idProfile);
        $data = $data->where('fr.statusenabled', true);
        $data = $data->where('fr.norec', '=', $Norec);
        $data = $data->get();

        $details = DB::select(DB::raw("select frd.produkfk,pr.namaproduk,frd.routefk,rf.name as route,
                                        frd.dosis,frd.ispagi,frd.issiang,frd.ismalam,frd.issore,frd.pegawaifarmasifk,
                                        frd.perawatfk,pg.namalengkap as pegawaifarmasi,pg1.namalengkap as perawat
                            from formulirobatdetail_t as frd
                            left JOIN produk_m as pr on pr.id=frd.produkfk
                            left JOIN routefarmasi as rf on rf.id=frd.routefk
                            left JOIN pegawai_m as pg on pg.id=frd.pegawaifarmasifk
                            left JOIN pegawai_m as pg1 on pg1.id=frd.perawatfk
                            where frd.kdprofile = $idProfile and frd.formulirobatfk=:norec"),
            array(
                'norec' => $Norec
            ));

        $result = array(
            'daftar' => $data,
            'detail' => $details,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function hapusDelegasiObat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            FormulisObat::where('norec', $request['norec'])
                ->where('kdprofile', $idProfile)
                ->update(['statusenabled' => false]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Terhapus";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getEMRTransaksiDetailForm(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : detail
        $paramNocm = '';
        $paramNoreg = '';
        $data = \DB::table('emrpasien_t as emrp')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'emrp.pegawaifk')
            ->select('emrp.*', 'emrp.noregistrasifk as noregistrasi', 'pg.namalengkap')
            ->where('emrp.statusenabled', true)
            ->where('emrp.kdprofile', $idProfile)
            ->orderBy('emrp.tglemr', 'desc');

        if (isset($request['noemr']) && $request['noemr'] != '') {
            $data = $data->where('emrp.noemr', $request['noemr']);
        }
        if (isset($request['emrfk']) && $request['emrfk'] != '') {
            $data = $data->where('emrdp.emrfk', $request['emrfk']);
        }
        if (isset($request['norec']) && $request['norec'] != '') {
            $data = $data->where('emrp.norec', $request['norec']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('emrp.nocm', $request['nocm']);
            $paramNocm = "AND emrp.nocm='" . $request['nocm']."'";
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['noregistrasi']);
            $paramNoreg = "AND emrp.noregistrasifk='" . $request['noregistrasi']."'";
        }
        if (isset($request['tgllahir']) && $request['tgllahir'] != "" && $request['tgllahir'] != "undefined" && $request['tgllahir'] != "null") {
            $tgllahir = $request['tgllahir'];
            $data = $data->whereRaw("format( emrp.tgllahir, 'yyyy-MM-dd')  ='$tgllahir' ");
        }
        if (isset($request['namapasien']) && $request['namapasien'] != '') {
            $data = $data->where('emrp.namapasien', $request['namapasien']);
        }
        if (isset($request['jenisEmr']) && $request['jenisEmr'] != '') {
            $data = $data->where('emrp.jenisemr', 'ilike', '%' . $request['jenisEmr'] . '%');
        } else {
            $data = $data->whereNull('emrp.jenisemr');
        }
        $data = $data->get();
        $jenisEMr = $request['jenisEmr'];
        $result = [];
        foreach ($data as $item) {
            $noemr = $item->noemr;

            $details = DB::select(DB::raw("
             SELECT
                    emrdp.emrpasienfk,
                    emrdp.emrfk,
                    emr.reportdisplay,
                    emrp.norec,emr.caption as namaform
                FROM
                    emrpasiend_t AS emrdp
                INNER JOIN emrpasien_t AS emrp ON emrp.noemr = emrdp.emrpasienfk
                --INNER JOIN emrd_t AS emrd ON emrd.id = emrdp.emrdfk
               INNER JOIN emr_t AS emr ON emr.id = emrdp.emrfk
                WHERE emrdp.kdprofile = $idProfile and
                    emrdp.statusenabled = true
                   $paramNocm
                   $paramNoreg
                    AND emrp.jenisemr ILIKE '%$jenisEMr%'
                    and emrdp.emrpasienfk='$noemr'
                    GROUP BY emrdp.emrpasienfk,
                    emrdp.emrfk,emrp.norec,emr.caption,
                    emr.reportdisplay

            "));

            $result [] = array(
                'norec' => $item->norec,
                'kdprofile' => $item->kdprofile,
                'statusenabled' => $item->statusenabled,
                'kodeexternal' => $item->kodeexternal,
                'emrfk' => $item->emrfk,
                'noregistrasifk' => $item->noregistrasifk,
                'noemr' => $item->noemr,
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'jeniskelamin' => $item->jeniskelamin,
                'noregistrasi' => $item->noregistrasi,
                'umur' => $item->umur,
                'kelompokpasien' => $item->kelompokpasien,
                'tglregistrasi' => $item->tglregistrasi,
                'norec_apd' => $item->norec_apd,
                'namakelas' => $item->namakelas,
                'namaruangan' => $item->namaruangan,
                'tglemr' => $item->tglemr,
                'tgllahir' => $item->tgllahir,
                'notelepon' => $item->notelepon,
                'alamat' => $item->alamat,
                'jenisemr' => $item->jenisemr,
                'pegawaifk' => $item->pegawaifk,
                'namalengkap' => $item->namalengkap,
                'details' => $details
            );
        }
        $result = array(
            'data' => $result,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function hapusEMRtransaksiNorec(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            EMRPasien::where('norec', $request['norec'])->where('kdprofile',$idProfile)->update(
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
                'as' => 'er@epic',
            );
        } else {
            $transMessage = " Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }


    //2019-12 penambahan arif awal
    public function getAntrianPasienDiperiksaDbLama($notransaksi){
        $data = \DB::connection('sqlsrv2')
            ->table('KUNJUNGANPASIEN as kp')
            ->join('pasien as p', 'p.KD_PASIEN', '=', 'kp.KPKD_PASIEN')
            ->join('DOKTER as dok', 'dok.FMDDOKTER_ID', '=', 'kp.KPKD_DOKTER')
            ->join('CUSTOMER as cus', 'cus.CUSID', '=', 'kp.KD_CUSTOMER')
            ->join('POLIKLINIK as pol', 'pol.FMPKLINIK_ID', '=', 'kp.KPKD_POLY')
            ->limit('5')
            ->select('kp.KPKD_PASIEN as nocm', 'p.NAMAPASIEN as namapasien', 'kp.KPNO_TRANSAKSI as notransaksi',
                'pol.FMPKLINIKN as namaruangan', 'kp.KPTGL_PERIKSA as tglregistrasi', 'p.JENIS_KELAMIN as jeniskelamin'
                , 'dok.FMDDOKTERN as namadokter', 'cus.NAME as kelompokpasien', 'kp.KPJAM_MASUK as jam_masuk', 'p.TGL_LAHIR as tgllahir', 'p.alamat as alamatlengkap')
            ->where('kp.KPNO_TRANSAKSI', '=', $notransaksi);

        $data = $data->first();
        $data->foto = null;
        if ($data->jeniskelamin == "1") {
            $data->jeniskelamin = "Laki-laki";
        } else {
            $data->jeniskelamin = "Perempuan";
        }
        $result = array(
            'result' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }

    public function getMenuRekamMedisAtuhDbLama()
    {

        $data[] = array(array(
            'id' => 1,
            'kdprofile' => null,
            'statusenabled' => 1,
            'kodeexternal' => null,
            'namaexternal' => null,
            'reportdisplay' => "RekamMedisDbLama.ResumeDBLama",
            'namaemr' => 'navigasi',
            'caption' => "Diagnosa, Penyakit, Obat",
            'headfk' => null,
            'nourut' => 0
        ), array(
            'id' => 1,
            'kdprofile' => null,
            'statusenabled' => 1,
            'kodeexternal' => null,
            'namaexternal' => null,
            'reportdisplay' => "RekamMedisDbLama.Laboratorium",
            'namaemr' => 'navigasi',
            'caption' => "Laboratorium",
            'headfk' => null,
            'nourut' => 2
        ), array(
            'id' => 1,
            'kdprofile' => null,
            'statusenabled' => 1,
            'kodeexternal' => null,
            'namaexternal' => null,
            'reportdisplay' => "RekamMedisDbLama.Radiologi",
            'namaemr' => 'navigasi',
            'caption' => "Radiologi",
            'headfk' => null,
            'nourut' => 2
        ));


        //$data = recursiveElements($data);

        $result = array(
            'data' => $data[0],
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDiagnosaByNotransaksi(Request $request)
    {
        $filter = $request->all();
        $diagnosa = \DB::connection('sqlsrv2')
            ->table('MR_DIAGNOSA as md')
            ->join('pasien as p', 'p.KD_PASIEN', '=', 'md.MRDKD_PASIEN')
            ->join('DOKTER as dok', 'dok.FMDDOKTER_ID', '=', 'md.MRDKD_DOKTER')
            ->join('POLIKLINIK as pol', 'pol.FMPKLINIK_ID', '=', 'md.MRDKD_UNIT')
            ->select('pol.FMPKLINIKN as poliklinik'
                , 'dok.FMDDOKTERN as namadokter', 'p.NAMAPASIEN as namapasien', 'md.*')
            ->where('md.MRDNO_TRANSAKSI', '=', $filter['notransaksi'])->get();

        $penyakit = \DB::connection('sqlsrv2')
            ->table('MR_PENYAKIT as mp')
            ->join('penyakit as p', 'p.KD_PENYAKIT', '=', 'mp.MRPKD_PENYAKIT')
            ->select('p.PENYAKIT as penyakit'
                , 'p.DESCRIPTION as description', 'mp.MRPKD_PENYAKIT')
            ->where('mp.MRPNO_TRANSAKSI', '=', $filter['notransaksi'])->get();

        $fhrbukti = \DB::connection('sqlsrv2')
            ->table('RESEPDOKTER as rd')
            ->select('rd.FHRBUKTI_ID as fhrbukti')
            ->where('rd.FHRNO_TRANSAKSI', '=', $filter['notransaksi'])->first();
        if (!empty($fhrbukti)) {
            $obat = \DB::connection('sqlsrv2')
                ->table('RESEPDOKTERD as rd')
                ->select('rd.*')
                ->where('rd.FDRBUKTI_ID', '=', $fhrbukti->fhrbukti)->get();
        } else {
            $obat = [];
        }


        $result = array(
            'diagnosa' => $diagnosa[0],
            'penyakit' => $penyakit[0],
            'obat' => $obat,
            'message' => 'as@arif',
        );
        return $this->respond($result);
    }


    public function getLabByNotransaksi(Request $request)
    {
        $filter = $request->all();

        $lab = \DB::connection('sqlsrv2')
            ->table('LAB_HASIL as lh')
            ->join('LAB_TEST as lt', 'lt.MTLKD_LAB', '=', 'lh.MLHKD_LAB')
            ->join('KLAS_PRODUK_RAD_LAB as prod', 'prod.FMKKLAS_ID', '=', 'lh.MLHKKD_PRODUK')
            ->select('lt.MTLLABN as namapemeriksaan', 'lh.MLHHASIL as hasil', 'lt.MTLSATUAN as satuan',
                'MTLNORMAL_LAKI2 as normal_laki', 'MTLNORMAL_WANITA as normal_wanita', 'prod.FMKKLASN as golongan')
            ->where('lh.MLHNO_TRANSAKSI', '=', $filter['notransaksi'])->get();

        $result = array(
            'lab' => $lab,
            'message' => 'as@arif',
        );
        return $this->respond($result);
    }

    public function getRadiologiByNotransaksi(Request $request)
    {
        $filter = $request->all();

        $radiologi = \DB::connection('sqlsrv2')
            ->table('RAD_HASIL as rh')
            ->join('PRODUK as p', 'p.FMPPRODUK_ID', '=', 'rh.MRHKD_PRODUK')
            ->select('p.FMPPRODUKN as produk', 'rh.MRHTGL_MASUK as tglperiksa', 'rh.MRHHASIL as hasil')
            ->where('rh.MRHNO_TRANSAKSI', '=', $filter['notransaksi'])->get();

        $result = array(
            'radiologi' => $radiologi,
            'message' => 'as@arif',
        );
        return $this->respond($result);
    }

    public function saveVerifCPPTEmr(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();

//        return $this->respond($noemr);
        try {

            if ($request['tipe'] == 'verif') {
                $EMR = EMRPasien::where('norec', $request['norec'])->where('kdprofile', $idProfile)->update(
                    [
                        'dokterfk' => $request['iddokter'],

                    ]
                );
            }
            if ($request['tipe'] == 'notif') {
                $EMR = EMRPasien::where('norec', $request['norec'])->where('kdprofile', $idProfile)->update(
                    [

                        'notifikasifk' => $request['iddokter']
                    ]
                );

            }
//
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = ' ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $EMR,
//                "data2" => $EMRD,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $data,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    //2019-12 penambahan arif akhir

    public function saveDataKepatuhanCuci(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();

        try {

            if ($dataReq['norec'] == '') {
                $dataSave = new KepatuhanCuciTangan();
                $dataSave->norec = $dataSave->generateNewId();
                $dataSave->kdprofile = $idProfile;
                $dataSave->statusenabled = true;
                # code...
            } else {

                $dataSave = KepatuhanCuciTangan::where('norec', $dataReq['norec'])->first();

            }
            $dataSave->tanggal = $dataReq['tanggal'];
            $dataSave->doktersp_p = $dataReq['doktersp_p'];
            $dataSave->doktersp_t = $dataReq['doktersp_t'];
            $dataSave->dokteru_p = $dataReq['dokteru_p'];
            $dataSave->dokteru_t = $dataReq['dokteru_t'];
            $dataSave->perawat_p = $dataReq['perawat_p'];
            $dataSave->perawat_t = $dataReq['perawat_t'];
            $dataSave->penunjang_p = $dataReq['penunjang_p'];
            $dataSave->penunjang_t = $dataReq['penunjang_t'];
            $dataSave->administrasi_p = $dataReq['administrasi_p'];
            $dataSave->administrasi_t = $dataReq['administrasi_t'];
            $dataSave->coas_p = $dataReq['coas_p'];
            $dataSave->coas_t = $dataReq['coas_t'];
            $dataSave->siswa_p = $dataReq['siswa_p'];
            $dataSave->siswa_t = $dataReq['siswa_t'];
            $dataSave->lain_p = $dataReq['lain_p'];
            $dataSave->lain_t = $dataReq['lain_t'];
            $dataSave->noruanganfk = $dataReq['noruanganfk'];

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
                "as" => 'afd@epic',
            );
        } else {
            $transMessage = " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'afd@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataKepatuhanCuciTangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $bulan = $filter['bulan'];
        $dataLap = DB::select(DB::raw("SELECT
            x.tanggal,SUM(x.doktersp_p) as doktersp_p,SUM(x.doktersp_t) as doktersp_t,
            SUM(x.dokteru_p) as dokteru_p,SUM(x.dokteru_t) as dokteru_t,
            SUM(x.perawat_p) as perawat_p,SUM(x.perawat_t) as perawat_t,
            SUM(x.penunjang_p) as penunjang_p,SUM(x.penunjang_t) as penunjang_t,
            SUM(x.administrasi_p) as administrasi_p,SUM(x.administrasi_t) as administrasi_t,
            SUM(x.coas_p) as coas_p,SUM(x.coas_t) as coas_t,
            SUM(x.siswa_p) as siswa_p,SUM(x.siswa_t) as siswa_t,
            SUM(x.lain_p) as lain_p,SUM(x.lain_t) as lain_t, x.namaruangan
            FROM(SELECT to_char(kc.tanggal,'YYYY-MM-DD') as tanggal,kc.doktersp_p,kc.doktersp_t,kc.dokteru_p,kc.dokteru_t,kc.perawat_p,
            kc.perawat_t,kc.penunjang_p,kc.penunjang_t,kc.administrasi_p,kc.administrasi_t,kc.coas_p,kc.coas_t,kc.siswa_p,kc.siswa_t,
            kc.lain_p,kc.lain_t,kc.noruanganfk,ru.namaruangan
            FROM kepatuhancucitangan_t as kc
            LEFT JOIN ruangan_m as ru on ru.id=kc.noruanganfk
            WHERE kc.kdprofile = $idProfile and kc.statusenabled=true AND CAST(to_char(kc.tanggal, 'M') as integer) BETWEEN 1 AND 12) as x
            GROUP BY x.tanggal,x.namaruangan"));

        $result = array(
            'data' => $dataLap,
            'message' => 'az@epic',
        );

        return $this->respond($result);
    }

    public function getDataKepatuhanCuciTanganload(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : Riwayat
        $data = \DB::table('kepatuhancucitangan_t AS kc')
            ->leftJoin('ruangan_m AS ru', 'ru.id', '=', 'kc.noruanganfk')
            ->select('kc.norec', 'kc.tanggal', 'kc.doktersp_p', 'kc.doktersp_t', 'kc.dokteru_p', 'kc.dokteru_t', 'kc.perawat_p', 'kc.perawat_t', 'kc.penunjang_p',
                'kc.penunjang_t', 'kc.administrasi_p', 'kc.administrasi_t', 'kc.coas_p', 'kc.coas_t', 'kc.siswa_p', 'kc.siswa_t', 'kc.lain_p',
                'kc.lain_t', 'kc.noruanganfk', 'ru.namaruangan')
            ->where('kc.kdprofile', $idProfile)
            ->where('kc.statusenabled', true);

        if (isset($request['tanggal']) && $request['tanggal'] != "" && $request['tanggal'] != "undefined" && $request['tanggal'] != "null") {
            $tgllahir = $request['tanggal'];
            $data = $data->whereRaw("format( emrp.tgllahir, 'yyyy-MM-dd')  ='$tanggal' ");
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'afd@epic',
        );
        return $this->respond($result);
    }

    public function saveBatalKepatuhanCuciTangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();

        try {

            // ($dataReq['norec'] == '')
            // $dataSave = new KepatuhanCuciTangan();
            // $dataSave->norec = $dataSave->generateNewId();
            # code...


            KepatuhanCuciTangan::where('norec', $request['norec'])
                ->where('kdprofile', $idProfile)
                ->update([

                    'statusenabled' => 0,
                ]);


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'afd@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transStatus,
                'as' => 'afd@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveDataKepatuhanHandHygiene(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();
        $kegiatan = '';
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();

        try {

            if ($dataReq['norec'] == '') {
                $dataSave = new KepatuhanHandHygiene();
                $dataSave->norec = $dataSave->generateNewId();
                $dataSave->kdprofile = 0;
                $dataSave->statusenabled = true;
                $kegiatan = 'Input IPCLN';
                # code...
            } else {

                $dataSave = KepatuhanHandHygiene::where('norec', $dataReq['norec'])->first();
                $kegiatan = 'Update IPCLN';

            }
            $dataSave->tanggal = $dataReq['tanggal'];
            $dataSave->objectjenispegawaifk = $dataReq['objectjenispegawaifk'];
            $dataSave->objectpegawaifk = $dataReq['objectpegawaifk'];
            $dataSave->objectindikasifk = $dataReq['objectindikasifk'];
            $dataSave->objecthygienefk = $dataReq['objecthygienefk'];
            $dataSave->langkah = $dataReq['langkah'];
            $dataSave->objectruanganfk = $dataReq['objectruanganfk'];
            $dataSave->kesempatan = $dataReq['kesempatan'];
            $dataSave->save();
            $norec = $dataSave->norec;

            /*Logging User*/
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = $kegiatan;
            $logUser->noreff = $norec;
            $logUser->referensi='norec kepatuhanhandhygne';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();
            /*End Logging User*/

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataSave,
                "as" => 'afd@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'afd@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataKepatuhanHandHygiene(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : Riwayat
        $data = \DB::table('kepatuhanhandhygiene_t AS kh')
            ->leftJoin('ruangan_m AS ru', 'ru.id', '=', 'kh.objectruanganfk')
            //->leftJoin('indikasi_m AS ink', 'ink.id', '=', 'kh.objectindikasifk')
            //->leftJoin('handhygiene_m AS hh', 'hh.id', '=', 'kh.objecthygienefk')
            ->leftJoin('pegawai_m AS pg', 'pg.id', '=', 'kh.objectpegawaifk')
            ->leftJoin('jenispegawai_m AS jp', 'jp.id', '=', 'kh.objectjenispegawaifk')
            ->select(DB::raw("
                kh.norec,kh.tanggal,kh.objectruanganfk,CAST(kh.objectindikasifk AS VARCHAR) AS objectindikasifk,ru.namaruangan,
                CAST(kh.objecthygienefk AS VARCHAR) AS objecthygienefk,kh.objectpegawaifk,
                pg.namalengkap,kh.langkah,kh.objectjenispegawaifk,jp.jenispegawai,kh.kesempatan,kh.langkah
            "))
            ->where('kh.kdprofile', $idProfile)
            ->where('kh.statusenabled', true)
            ->orderBy('kh.tanggal');
        $filter = $request->all();

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('kh.tanggal', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('kh.tanggal', '<=', $filter['tglAkhir']);
        }
        if (isset($filter['pgid']) && $filter['pgid'] != "" && $filter['pgid'] != "undefined") {
            $data = $data->where('kh.objectpegawaifk', '=', $filter['pgid']);
        }
        if (isset($filter['prid']) && $filter['prid'] != "" && $filter['prid'] != "undefined") {
            $data = $data->where('kh.objectjenispegawaifk', '=', $filter['prid']);
        }
        if (isset($filter['bngid']) && $filter['bngid'] != "" && $filter['bngid'] != "undefined") {
            $data = $data->where('kh.objectruanganfk', '=', $filter['bngid']);
        }
        if (isset($filter['indikasiid']) && $filter['indikasiid'] != "" && $filter['indikasiid'] != "undefined") {
            $data = $data->where('ink.id', '=', $filter['indikasiid']);
        }
        $data = $data->get();

        // foreach ($data as $key => $value) {
        //        $indikasi = \DB::table('indikasi_m')->get();
        //        $handhygiene = \DB::table('handhygiene_m')->get();

        //     # code...
        // }
        $result = array(
            'data' => $data,
            'message' => 'afd@epic',
        );
        return $this->respond($result);
    }

    public function saveBatalKepatuhanHandHygiene(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataReq = $request->all();

        try {

            // ($dataReq['norec'] == '')
            // $dataSave = new KepatuhanCuciTangan();
            // $dataSave->norec = $dataSave->generateNewId();
            # code...


            KepatuhanHandHygiene::where('norec', $request['norec'])
                ->where('kdprofile', $idProfile)
                ->update([

                    'statusenabled' => 0,
                ]);


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'afd@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transStatus,
                'as' => 'afd@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getNilaiStatisIGD(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $objectid = '';
        $noregistrasi = '';
        $idemr = '';

        if (isset($request['objectidawal']) && $request['objectidawal'] != '' && isset($request['objectidakhir']) && $request['objectidakhir'] != '') {
            $objectid = ' and emrdp.emrdfk BETWEEN ' . $request['objectidawal'] . ' and ' . $request['objectidakhir'];
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $noregistrasi = ' and emrp.noregistrasifk=' . $request['noregistrasi'];
        }
        if (isset($request['idemr']) && $request['idemr'] != '') {
            $idemr = ' and emrdp.emrfk=' . $request['idemr'];
        }
        $data = DB::select(DB::raw("select sum( x.nilai) as nilai from (
                SELECT emrp.tglemr , emrdp.[value], emrdp.emrdfk, emrdp.emrfk, cast(emd.reportdisplay as int)as nilai
                FROM [dbo].[emrpasien_t] emrp
                left join emrpasiend_t emrdp on emrdp.emrpasienfk =emrp.noemr
                left join emrd_t emd on emd.id =emrdp.emrdfk
                where emrp.kdprofile = $idProfile and emrp.statusenabled= 1
                and emrdp.statusenabled= 1
                and emrdp.value= 1
               $idemr
                $objectid
                $noregistrasi
                GROUP BY  emrdp.[value],emrdp.emrdfk,emrdp.emrfk,emd.reportdisplay,emrp.tglemr
                ) as x
                GROUP BY x.tglemr
                ORDER BY x.tglemr desc"));

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function disableEMRdetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $emr = EMRPasienD::where('emrfk', $request['idemr'])
                ->where('kdprofile', $idProfile)
                ->where('emrpasienfk', $request['noemr'])
                ->first();
            $pegawai = Pegawai::where('id', $request['idpegawai'])->first();

            if ($emr->pegawaifk != null && $emr->pegawaifk != $request['idpegawai']) {
                $pegawaiInput = Pegawai::where('id', $emr->pegawaifk )->where('kdprofile', $idProfile)->first();
                $transMessage = "Hanya User yang mengisi yang bisa menghapus. ( " . $pegawaiInput->namalengkap . " )";
                DB::rollBack();
                $result = array(
                    "status" => 400,
                    "message" => $transMessage,
                    "as" => 'er@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, $transMessage);
            }

            EMRPasienD::where('emrfk', $request['idemr'])
                ->where('kdprofile', $idProfile)
                ->where('emrpasienfk', $request['noemr'])
                ->update([
                    'statusenabled' => false,
                ]);
            $emrdetail = EMRPasienD::where('emrpasienfk', $request['noemr'])
                ->where('kdprofile', $idProfile)
                ->where('statusenabled', true)
                ->get();
            if (count($emrdetail) == 0) {
                EMRPasien::where('norec', $request['norec'])
                    ->update([
                        'statusenabled' => false,
                    ]);
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transStatus,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataMetodebelajar(Request $request){
        $req = $request->all();
        $dataProduk = \DB::table('Metodecarabelajar_m')
            ->select('id as value', 'indikasi as text')
            ->where('statusenabled', true)
            ->orderBy('indikasi');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('indikasi', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataDiagnosaPrimary(Request $request, $Noregistrasi){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t AS pd')
            ->select(DB::raw("dg.id,dg.kddiagnosa,dg.namadiagnosa"))
            ->Join('antrianpasiendiperiksa_t AS apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->Join('diagnosapasien_t AS dp', 'dp.noregistrasifk', '=', 'apd.norec')
            ->Join('detaildiagnosapasien_t AS ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->Join('diagnosa_m AS dg', 'dg.id', '=', 'ddp.objectdiagnosafk')
            ->where('pd.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->where('pd.noregistrasi', $Noregistrasi)
            ->take(1)
            ->get();
        return $this->respond($data);
    }

    public function getDataDiagnosaJiwa(Request $request){

        $req = $request->all();
        $dataProduk = \DB::table('diagnosajiwa_m')
            ->select('id as value', 'diagnosajiwa as text')
            ->where('statusenabled', true)
            ->orderBy('diagnosajiwa');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('diagnosajiwa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }
    public function jawabKonsul(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            $dataSO = StrukOrder::where('norec', $request['norec_so'])->where('kdprofile',$idProfile)->update([
                'keteranganlainnya' =>  $request['jawaban']
            ]);



            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,

                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPendidikan(Request $request){
        $req = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk = \DB::table('pendidikan_m')
            ->select('id as value', 'pendidikan as text')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('pendidikan');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('pendidikan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataPerkawinan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request->all();
        $dataProduk = \DB::table('statusperkawinan_m')
            ->select('id as value', 'statusperkawinan as text')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('statusperkawinan');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('statusperkawinan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();

        return $this->respond($dataProduk);
    }

    public function getDataPekerjaan(Request $request){
        $req = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk = \DB::table('pekerjaan_m')
            ->select('id as value', 'pekerjaan as text')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('pekerjaan');
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $dataProduk = $dataProduk->where('pekerjaan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();
        return $this->respond($dataProduk);
    }

    public function getVitalSign(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $objectid = '';
        $noregistrasi = '';
        $idemr = '';

        if (isset($request['objectidawal']) && $request['objectidawal'] != '' && isset($request['objectidakhir']) && $request['objectidakhir'] != '') {
            $objectid = ' AND epd.emrdfk BETWEEN ' . $request['objectidawal'] . ' and ' . $request['objectidakhir'];
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $noregistrasi = " AND ep.noregistrasifk='" . $request['noregistrasi']. "'";
        }
        if (isset($request['idemr']) && $request['idemr'] != '') {
            $idemr = ' AND epd.emrfk=' . $request['idemr'];
        }
        $data = DB::select(DB::raw("SELECT epd.[value], epd.emrdfk FROM emrpasiend_t as epd
            INNER JOIN emrpasien_t as ep ON ep.noemr = epd.emrpasienfk
            WHERE epd.kdprofile = $kdProfile
            $idemr
            $noregistrasi
            $objectid
            AND epd.statusenabled = true
            ORDER BY epd.emrdfk"));

        $result = array(
            'data' => $data,
            'message' => 'afd@epic',
        );
        return $this->respond($result);
    }

    public function getMenuEmrById(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataRaw = \DB::table('emr_t as emr')
            ->where('emr.kdprofile',$kdProfile)
            ->where('emr.statusenabled', true)
            ->where('emr.id', $request['id'])
            ->select('emr.*')
            ->first();

        $result = array(
            'data' => $dataRaw,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getInfoEMRPasien(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t AS pd')
            ->JOIN('pasien_m AS ps', 'ps.id', '=', 'pd.nocmfk')
            ->where('pd.kdprofile',$kdProfile)
            ->WHERE('pd.noregistrasi', $request['noregistrasi'])
            ->select('ps.notelepon');

        $data=$data->get();

        $result = array(
            'data' => $data,
            'message' => 'dy@epic',
        );
        return $this->respond($result);
    }
    public function sendChatAPI($kdProfile, $request)
    {

        //wa
        //https://www.twilio.com/console/sms/whatsapp/sandbox
        //composer require twilio/sdk


        $pasien = DB::table('pasien_m')
            ->where('nocm',$request['head']['customerid'])
            ->where('kdprofile',$kdProfile)->first();
        $namapasien = '';
        if(!empty($pasien)){
            $namapasien =$pasien->namapasien;
        }

        $sid    = "ACcacca028756cbdb2676f0132cdcdd290";
        $token  = "f5179246517fea90e24f5e91819d06b3";

        $twilio = new Client($sid, $token);

        $listNumber = explode (',',$this->settingDataFixed('listNumberECG',$kdProfile));
        $listNumberSMS = explode (',',$this->settingDataFixed('listNumberSMS',$kdProfile));

        $numbersWA = [];
        $numbersSMS = [];
        foreach ($listNumber as $t){
            $numbersWA []=  'whatsapp:'.$t;
            // $numbersSMS [] = $t;
        }
        foreach ($listNumberSMS as $t){
            $numbersSMS [] = $t;
        }
        // return $numbersWA;

        /*
       * WA
       */
        $i = 0;
        foreach ($numbersWA as $arr){
            $message = $twilio->messages
                ->create($numbersWA[$i], // to
                    array(
                        "from" => "whatsapp:+14155238886",
                        "body" => "*Electrocardiography*✅ \n\nNo Rekam Medis : *".$request['head']['customerid']."* \nNama Pasien : *".$namapasien."* \nExpertise : *".$request['head']['expertise']."*",
                    )
                );
            $i = $i+1;
        }

        /*
        * SMS
        */
        $i = 0;
        foreach ($numbersSMS as $arr){
            // $number = explode('whatsapp:', $array[$i]);
            // return  $number;
            $message = $twilio->messages
                ->create($numbersSMS[$i], // to
                    array(
                        "from" => "+12512200695",
                        "body" => "Electrocardiography \n\nNo Rekam Medis : ".$request['head']['customerid']."\nNama Pasien : ".$namapasien." \nExpertise : ".$request['head']['expertise']."\n",
                    )
                );
            // if(isset(json_decode($message->status))!=400){

            // }
            $i = $i+1;

        }
        // $resut['response'] = $message->sid;

        //sms Gateway

        // $i = 0;
        // $ekspertise ='Expertise ECG Pasien '.$namapasien.' ('.$request['head']['customerid']. ')'.' : '.$request['head']['expertise'];

        // $conten =str_replace(" ","%20",$ekspertise);
        // $response = "SMS Gateaway Gagal";
        // foreach ($numbersSMS as $arr){
        //     $response =     $this->sendSMS($arr,$conten);
        // }

        // telegram
        // https://api.telegram.org/bot<YourBOTToken>/getUpdates
        $secret_token = "1497541104:AAHwyKKk6Hk47gb6iXbaZM-8ecSFs8z-rwo";
        $telegram_id = '-455096506';
        $url = "https://api.telegram.org/bot" . $secret_token . "/sendMessage?parse_mode=html&chat_id=" . $telegram_id;
        $url = $url . "&text=" .
            // urlencode("Expertise ECG : \n\n No Rekam Medis : <b>".$request['head']['customerid']. "</b> \n\n Nama Pasien : <b> ".$namapasien."</b>\n\n Expertise : <b>".$request['head']['expertise']."</b> ");
            urlencode("Expertise ECG: <b>".$request['head']['expertise']."</b> \n\nNo Rekam Medis: <b>".$request['head']['customerid']."</b>  \nNama Pasien: <b>".$namapasien."</b> \n\n https://transmedic.co.id:5555/app/#/RGFmdGFyRXBpY0VDRw==");
        // return $url;
        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);

        return true;
    }

    public function getDataKepatuhanHandHygieneIPCN(Request $request){
        $bln= '';
        if(isset($request['bln']) && $request['bln']!="" && $request['bln']!="undefined"){
            $bln = "AND to_char(kh.tanggal,'yyyy-MM') ='".$request['bln']."' ";
        };

        $data = \DB::select(DB::raw("SELECT x.bulan, x.tahun, x.namaruangan, x.jenispegawai, SUM(x.patuh) AS patuh, SUM(x.tidakpatuh) AS tidakpatuh
            FROM
                (SELECT
                    EXTRACT(MONTH from kh.tanggal) AS bulan,
                    EXTRACT(YEAR from kh.tanggal) AS tahun,
                    ru.namaruangan,
                    jp.jenispegawai,
                    CASE WHEN kh.langkah=2 THEN 1 ELSE 0 END AS patuh,
                    CASE WHEN kh.langkah!=2 THEN 1 ELSE 0 END AS tidakpatuh
                FROM
                    kepatuhanhandhygiene_t AS kh
                LEFT JOIN ruangan_m AS ru ON ru.id = kh.objectruanganfk
                LEFT JOIN pegawai_m AS pg ON pg.id = kh.objectpegawaifk
                LEFT JOIN jenispegawai_m AS jp ON jp.id = kh.objectjenispegawaifk
                WHERE
                    kh.statusenabled = true
                    $bln) AS x
            GROUP BY x.tahun, x.bulan, x.namaruangan, x.jenispegawai "));

        $result = array(
            'data' => $data,
            'message' => 'dy@epic',
        );
        return $this->respond($result);
    }
    public function SaveTransaksiEMRBackup(Request $request)
    {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataReq = $request->all();

        $head = $dataReq['head'];
        $data = $dataReq['data'];

//        foreach ($data as $itm){
//            $dtdt[] = $itm;
//        }
//
//        $keys = array_keys($data);

        DB::beginTransaction();
        try {

            if ($head['norec_emr'] == '-') {

                $noemr = $this->generateCodeBySeqTable(new EMRPasien, 'noemr', 15, 'MR' . date('ym') . '/',$kdProfile);
                // return $noemr;
                if ($noemr == '') {
                    $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $transMessage,
                        "as" => 'as@epic',
                    );
                    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                }
//                $noemr = $this->generateCode(new EMRPasien, 'noemr', 12, 'MR' . $this->getDateTime()->format('ym') . '/');
//                $noemr = $this->generateCode(new EMRPasien, 'noemr', 14, 'MR' . $this->getDateTime()->format('ym') . '/');

                $EMR = new EMRPasien();
                $norecHead = $EMR->generateNewId();
                $EMR->norec = $norecHead;
                $norecTehMenikitunyaeuy = $norecHead;
                $EMR->norec = $norecTehMenikitunyaeuy;
                $EMR->kdprofile = $kdProfile;
                $EMR->statusenabled = 1;

                if (isset($head['noregistrasi'])) {
                    $EMR->noregistrasifk = $head['noregistrasi'];
                }
                $EMRPASIENDETAIL =[];
                $EMRPASIENDETAILIMG =[];

            } else {
                $EMR = EMRPasien::where('noemr', $head['norec_emr'])
                    ->where('noregistrasifk', $head['noregistrasi'])
                    ->where('kdprofile',$kdProfile)
                    ->first();
                $noemr = $EMR->noemr;

                //LOAD DATA EMR PEMBANDING
                $EMRPASIENDETAIL = EMRPasienD::where('emrpasienfk', $noemr)
                    ->select('emrdfk','value')
                    ->where('emrfk', $head['emrfk'])
                    ->where('kdprofile',$kdProfile)
                    ->where('statusenabled', 1)
                    ->orderBy('emrdfk')
                    ->get();
                if(isset($dataReq['dataimg'])){
                    $EMRPASIENDETAILIMG = EmrFoto::where('noemrpasienfk', $noemr)
                        ->select('emrdfk','image')
                        ->where('emrfk', $head['emrfk'])
                        ->where('kdprofile',$kdProfile)
                        ->where('statusenabled', 1)
                        ->orderBy('emrdfk')
                        ->get();
                }

//                $EMRDelete = EMRPasienD::where('emrpasienfk', $noemr)
//                ->where('emrfk', $head['emrfk'])
//                ->delete();
            }

            //VALIDASI JIKA NOREGISTRASI BEDA //
            if(trim($EMR->noregistrasifk) != $head['noregistrasi']){
                $transMessage = "Kesalahan loading data..!";
                DB::rollBack();
                $result = array(
                    "status" => 400,
                    "message" => $transMessage,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, $transMessage);
            }


            $EMR->noemr = $noemr;
            $EMR->emrfk = $head['emrfk'];
//            if (isset($head['norec_pd'])) {
//                $EMR->noregistrasifk = $head['norec_pd'];
//            }
            $EMR->nocm = $head['nocm'];
            $EMR->namapasien = $head['namapasien'];
            $EMR->jeniskelamin = $head['jeniskelamin'];

            $EMR->umur = $head['umur'];
            if (isset($head['kelompokpasien'])) {
                $EMR->kelompokpasien = $head['kelompokpasien'];
            }
            if (isset($head['tglregistrasi'])) {
                $EMR->tglregistrasi = $head['tglregistrasi'];
            }
            if (isset($head['norec'])) {
                $EMR->norec_apd = $head['norec'];
            }
            if (isset($head['namakelas'])) {
                $EMR->namakelas = $head['namakelas'];
            }
            if (isset($head['namaruangan'])) {
                $EMR->namaruangan = $head['namaruangan'];
            } else {
                $EMR->namaruangan = "Triage Gawat Darurat";
            }
            if (isset($head['tgllahir'])) {
                $EMR->tgllahir = $head['tgllahir'];
            }
            if (isset($head['notelepon'])) {
                $EMR->notelepon = $head['notelepon'];
            }
            if (isset($head['alamatlengkap'])) {
                $EMR->alamat = $head['alamatlengkap'];
            }
            if (isset($head['jenisemr'])) {
                $EMR->jenisemr = $head['jenisemr'];
            }
            $EMR->pegawaifk = $this->getCurrentUserID();
            $EMR->tglemr = $this->getDateTime()->format('Y-m-d H:i:s');
            $EMR->save();

            $norec_EMR = $EMR->noemr;

//            $EMRDelete = EMRPasienD::where('emrpasienfk', $norec_EMR)
//                ->where('emrfk', $head['emrfk'])
//                ->update([
//                    'statusenabled' => false
//                ]);
            $i = 0;
//            foreach ($keys as $ky) {
//                $emrdfk = $ky;
//                $valueemr = $dtdt[$i];
            $sama = 0;
            $j = 0;
            $h=0;
            foreach ($data as $item) {
                $emrdfk = $item['id'];
                if (is_array($item['values'])) {
                    $valueemr = $item['values']['value'] . '~' . $item['values']['text'];
                } else {
                    $valueemr = $item['values'];
                }
                $sama = 0;
                foreach ($EMRPASIENDETAIL as $emrupdate){
                    $sama = 0;
                    if ($emrupdate->emrdfk == $emrdfk ){
                        $sama =  1;
                        if ($emrupdate->value != $valueemr){
                            $sama =  2;
                            break;
                        }
                        break;
                    }
                }

                if  ($sama ==  2){
                    $EMRPasienDUpdatekeun = EMRPasienD::where('emrpasienfk', $norec_EMR)
                        ->where('emrfk', $head['emrfk'])
                        ->where('emrdfk', $emrdfk)
                        ->where('kdprofile',$kdProfile)
                        ->where('statusenabled', 1)
                        ->update([
                            'value' => $valueemr
                        ]);
                    $j++;
                }
                $EMRD =[];
                if($sama ==  0){
                    $EMRD = new EMRPasienD();
                    $norecD = $EMRD->generateNewId();
                    $EMRD->norec = $norecD;
                    $EMRD->kdprofile = $kdProfile;
                    $EMRD->statusenabled = 1;
                    $EMRD->emrpasienfk = $norec_EMR;
                    $EMRD->value = $valueemr;
                    $EMRD->emrdfk = $emrdfk;
                    $EMRD->emrfk = $head['emrfk'];
                    $EMRD->pegawaifk = $this->getCurrentUserID();
                    $EMRD->tgl = $this->getDateTime()->format('Y-m-d H:i:s');
                    $EMRD->save();
                    $h++;
                }




//                $EMRD_temp = new EMRPasienD_Temp();
//                $norecDs = $EMRD_temp->generateNewId();
//                $EMRD_temp->norec = $norecDs;
//                $EMRD_temp->kdprofile = 1;
//                $EMRD_temp->statusenabled = 1;
//                $EMRD_temp->emrpasienfk = $norec_EMR;
//                $EMRD_temp->value = $valueemr;
//                $EMRD_temp->emrdfk = $emrdfk;
//                $EMRD_temp->emrfk = $head['emrfk'];
//                $EMRD_temp->pegawaifk = $this->getCurrentUserID();
//                $EMRD_temp->tgl = $this->getDateTime()->format('Y-m-d H:i:s');
//                $EMRD_temp->save();
                $i = $i + 1;
            }

            if(isset($dataReq['image']) && $dataReq['image']!=null){

                $img = $dataReq['image'];
                $datas = unpack("H*hex", $img);
                $datas = '0x'.$datas['hex'];

                $dataGambar = \DB::table('emrfoto_t as tt')
                    ->where('tt.noemrpasienfk','=',$norec_EMR)
                    ->where('tt.emrfk','=',$head['emrfk'])
                    ->where('tt.kdprofile',$kdProfile)
                    ->first();
//                return $this->respond($noRec);
                if ($dataGambar == '' || $dataGambar == null){
                    $emrFto = new EmrFoto();
                    $norecFto = $emrFto->generateNewId();
                    $emrFto->norec = $norecFto;
                    $emrFto->kdprofile = $kdProfile;
                    $emrFto->statusenabled = 1;
                    $emrFto->noemrpasienfk = $norec_EMR;
                    $emrFto->emrfk = $head['emrfk'];
                }else{
                    $emrFto = EmrFoto::where('noemrpasienfk', $norec_EMR)
                        ->where('kdprofile',$kdProfile)->first();

                }
                $emrFto->image = \DB::raw("CONVERT(VARBINARY(MAX), $datas)");
                $emrFto->save();
            }

            if(isset($dataReq['dataimg'])){
                $i2 = 0;
                $sama2 = 0;
                $j2 = 0;
                $h2 = 0;
                $dataImg =  $dataReq['dataimg'];
                foreach ($dataImg as $item2) {
                    if($item2['values'] != '../app/images/svg/no-image.svg'){

                        $emrdfk2 = $item2['id'];
                        $valueemr2 = $item2['values'];

                        $sama2 = 0;
                        foreach ($EMRPASIENDETAILIMG as $emrupdate){
                            $sama2 = 0;
                            if ($emrupdate->emrdfk == $emrdfk2 ){
                                $sama2 =  1;
                                if ($emrupdate->image != $valueemr2){
                                    $sama2 =  2;
                                    break;
                                }
                                break;
                            }
                        }

                        if($sama2 ==  2){
                            $EMRPasienDUpdatekeun2 = EmrFoto::where('noemrpasienfk', $norec_EMR)
                                ->where('emrfk', $head['emrfk'])
                                ->where('emrdfk', $emrdfk2)
                                ->where('kdprofile',$kdProfile)
                                ->where('statusenabled', 1)
                                ->update([
                                    'image' => $valueemr2
                                ]);
                            $j2++;
                        }
                        $EMRD2 =[];
                        if($sama2 ==  0){
                            $EMRD2 = new EmrFoto();
                            $norecD2 = $EMRD2->generateNewId();
                            $EMRD2->norec = $norecD2;
                            $EMRD2->kdprofile = $kdProfile;
                            $EMRD2->statusenabled = 1;
                            $EMRD2->noemrpasienfk = $norec_EMR;
                            $EMRD2->image = $valueemr2;
                            $EMRD2->emrdfk = $emrdfk2;
                            $EMRD2->emrfk = $head['emrfk'];
                            $EMRD2->save();
                            $h2++;
                        }

                        $i2 = $i2 + 1;
                    }
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Saving EMR Pasien ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $EMR,
                // "data2" => $EMRD,
                "jumlah" => count($EMRPASIENDETAIL),
                "update" => $j,
                "new" => $h,
                "as" => 'as@epic',
            );
            $this->saveEMRBackup($data, $head, $norec_EMR,$kdProfile);

        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $data,
//                "keys" => $keys,
//                "dtdt" => $dtdt,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveEMRBackup($data, $head, $norec_EMR, $kdProfile)
    {
        DB::beginTransaction();
        // try {
        foreach ($data as $item) {
            $emrdfk = $item['id'];
            if (is_array($item['values'])) {
                $valueemr = $item['values']['value'] . '~' . $item['values']['text'];
            } else {
                $valueemr = $item['values'];
            }
            $EMRD_temp = new EMRPasienD_Temp();
            $norecDs = $EMRD_temp->generateNewId();
            $EMRD_temp->norec = $norecDs;
            $EMRD_temp->kdprofile = $kdProfile;
            $EMRD_temp->statusenabled = 1;
            $EMRD_temp->emrpasienfk = $norec_EMR;
            $EMRD_temp->value = $valueemr;
            $EMRD_temp->emrdfk = $emrdfk;
            $EMRD_temp->emrfk = $head['emrfk'];
            $EMRD_temp->pegawaifk = $this->getCurrentUserID();
            $EMRD_temp->tgl = $this->getDateTime()->format('Y-m-d H:i:s');
            $EMRD_temp->save();
        }
        $transStatus = 'true';
        // } catch (\Exception $e) {
        //     $transStatus = 'false';
        // }
        $transMessage = 'Saving Backup EMR Pasien ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();

        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();

        }

        //return $this->setStatusCode($result['status'])->respond($transMessage);
    }
    protected function generateCodeEMR($objectModel, $atrribute, $length=8, $prefix='', $kdprofile ){
        DB::beginTransaction();
        try {
            $result = SeqNumberEMR::where('seqnumber', 'LIKE', $prefix.'%')
                ->where('seqname',$atrribute)
                ->where('kdprofile',$kdprofile)
                ->max('seqnumber');
            $prefixLen = strlen($prefix);
            $subPrefix = substr(trim($result),$prefixLen);
            $SN = $prefix.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));

            $newSN = new SeqNumberEMR();
            $newSN->kdprofile = $kdprofile;
            $newSN->seqnumber = $SN;
            $newSN->tgljamseq = date('Y-m-d H:i:s');;
            $newSN->seqname = $atrribute;
            $newSN->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            return $SN;
        } else {
            DB::rollBack();
            return '';
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarRiwayatRegistrasiPHR(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->join('pasiendaftar_t as pd', 'pd.nocmfk', '=', 'ps.id')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select(DB::raw("pd.norec,pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pd.objectruanganlastfk,ru.namaruangan,
			                  pd.objectpegawaifk,pg.namalengkap as namadokter,pd.tglpulang,ru.objectdepartemenfk,
			                  CASE when ru.objectdepartemenfk in (16,25,26) then 1 else 0 end as statusinap,'' AS kddiagnosa"))
            ->where('ps.kdprofile', $idProfile)
            ->whereNull('br.pasiendaftarfk');

        if (isset($request['norm']) && $request['norm'] != "" && $request['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $request['norm'] . '%');
        };
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $request['namaPasien'] . '%');
        };
        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noReg']);
        };
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $request['idRuangan']);
        };
        if (isset($request['nik']) && $request['nik'] != "" && $request['nik'] != "undefined") {
            $data = $data->where('ps.noidentitas', '=', $request['nik']);
        };
        $data = $data->where('ps.statusenabled', true);
        $data = $data->orderBy('pd.tglregistrasi', 'desc');
        $data = $data->take(3);
        $data = $data->get();
        $norecaPd = '';
        $diagnosa = '';
//        return $this->respond($data);
        foreach ($data as $ob){
            $norecaPd = $norecaPd.",'".$ob->norec . "'";
//                        $ob->kddiagnosa = [];
        }
        $norecaPd = substr($norecaPd, 1, strlen($norecaPd)-1);
//                    $diagnosa = [];
        if($norecaPd!= ''){
            $diagnosa = DB::select(DB::raw("
                           select dg.kddiagnosa || ': ' || dg.namadiagnosa AS diagnosa,ddp.noregistrasifk as norec_apd,apd.noregistrasifk AS norec_pd
                           from antrianpasiendiperiksa_t AS apd
                           inner join detaildiagnosapasien_t as ddp ON ddp.noregistrasifk = apd.norec
                           left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                           left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                           where ddp.objectjenisdiagnosafk = 1 and apd.noregistrasifk in ($norecaPd) "));
            $i = 0;
            foreach ($data as $h){
                foreach ($diagnosa as $d){
                    if($data[$i]->norec == $d->norec_pd){
//                        return $this->respond($d);
                        if ($d->diagnosa != null){
//                            return $this->respond($data[$i]->diagnosa);
                            $data[$i]->kddiagnosa = $data[$i]->kddiagnosa . ', ' . $d->diagnosa;
                        }
                    }
                }
                $i++;
            }
        }
        $d=0;
        $result=[];
        foreach ($data as $hideung){
            if ($hideung->kddiagnosa != ""){
                $data[$d]->kddiagnosa = substr($data[$d]->kddiagnosa,1);
                $result [] = $data[$d];
            }
            $d = $d + 1;
        }


        $result = array(
            'daftar' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveKetDelegasiObatDetail(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataReq = $request->all();
        DB::beginTransaction();
        try{
            $data = FormulisObatDetail::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->update(
                ['keterangan' => $request['keterangan']]
            );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );

        } else {
            $transMessage = "simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }

    public function savePerawatDelegasiObat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataReq = $request->all();
        DB::beginTransaction();
        try{
            $data = FormulisObat::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->update(
                ['perawatfk' => $request['perawat']]
            );
            $data2 = FormulisObatDetail::where('formulirobatfk', $request['norec'])->where('kdprofile', $kdProfile)->update(
                ['perawatfk' => $request['perawat']]
            );


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "simpan Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );

        } else {
            $transMessage = "simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveCapKakiBayi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataReq = $request->all();
        DB::beginTransaction();
        try{
            $cek = PengkajianImage::where('norecpap', $request['norecPap'])->where('kdprofile', $kdProfile)->first();
            if(empty($cek)){
                $mod = new PengkajianImage();
                $mod->norec = $mod->generateNewId();
                $mod->kdprofile = $kdProfile ;
                $mod->statusenabled = true ;
                $mod->norecpap = $request['norecPap'] ;
                $mod->noregistrasifk = $request['noRegistrasi']  ;
                $mod->pegawaifk = $this->getCurrentUserID();
                $mod->jenis = 'Cap Kaki Bayi' ;
                $mod->image = $request['imageEncode']  ;
                $mod->tgl = date('Y-m-d H:i:s');
                $mod->save();
            }else{
                $cek->image =$request['imageEncode'] ;
                $cek->pegawaifk = $this->getCurrentUserID();
                $cek->tgl = date('Y-m-d H:i:s');
                $cek->save();
            }


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = " Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'as@epic',
            );

        } else {
            $transMessage = "simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getCapKakiBayi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);

        $cek = PengkajianImage::where('norecpap', $request['norecPap'])->where('kdprofile', $kdProfile)->first();
        $res['image'] =  $cek ;
        return $this->respond($res);
    }
    public function getEMRTransaksiImage(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //todo : detail
        $paramNocm = '';
        $paramNoreg = '';
        $data = \DB::table('emrfoto_t as emrp')

            ->select('emrp.*')
            ->where('emrp.statusenabled', true)
            ->where('emrp.kdprofile', $idProfile);


        if (isset($request['noemr']) && $request['noemr'] != '') {
            $data = $data->where('emrp.noemrpasienfk', $request['noemr']);
        }
        if (isset($request['emrfk']) && $request['emrfk'] != '') {
            $data = $data->where('emrp.emrfk', $request['emrfk']);
        }

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function saveMenuEMR(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($r['id'] == '') {
                $mp = EMR::where('kdprofile', $idProfile)->max('id');
                $length = 5;
                $prefixLen = 2;
                $prefix = $idProfile;
                $subPrefix = substr(trim($mp), $prefixLen);
                $id = $prefix . (str_pad((int)$subPrefix + 1, $length - $prefixLen, "0", STR_PAD_LEFT));
                $model = new EMR();
                $model->id = $id;
                $model->kdprofile = $idProfile;
                $model->statusenabled = true;
                $nourut = EMR::where('namaemr', $r['namaemr'])
                        ->where('kdprofile', $idProfile)->count() + 1;
                $model->nourut = $nourut;
            } else {
                $model = EMR::where('id', $r['id'])->first();
            }
//            $model->kodeexternal =true;
//            $model->namaexternal =true;
//            $model->reportdisplay =true;
            $model->namaemr = $r['namaemr'];
            $model->caption = $r['caption'];
            $model->headfk = $r['headfk'];
            $model->classgrid = 'grid_12';
            $model->save();
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }
        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteMenuEMR(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        DB::beginTransaction();
        try {
            $model = EMR::where('id', $r['id'])->update([
                'statusenabled' => false
            ]);

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }
        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveEMRD(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        DB::beginTransaction();
        try {
            $cek = EMRD::where('kdprofile', $idProfile)->where('emrfk', $r['data'][0]['emrfk'])->first();
            if (empty($cek)) {
                foreach ($r['data'] as $it) {
                    $mp = EMRD::where('kdprofile', $idProfile)->max('id');
                    $length = 8;
                    $prefixLen = 2;
                    $prefix = $idProfile;
                    $subPrefix = substr(trim($mp), $prefixLen);
                    $id = $prefix . (str_pad((int)$subPrefix + 1, $length - $prefixLen, "0", STR_PAD_LEFT));

                    $model = new EMRD();
                    $model->id = $id;
                    $model->kdprofile = $idProfile;
                    $model->statusenabled = true;
//                    $nourut = EMRD::where('emrfk', $it['emrfk'])
//                            ->where('kdprofile', $idProfile)->count() + 1;
                    $model->nourut = $id;//$nourut;
                    $model->kodeexternal = null;
                    $model->namaexternal = null;
                    $model->reportdisplay = null;
                    $model->headfk = $it['headfk'];
                    $model->caption = $it['caption'];
                    $model->type = $it['type'];
                    $model->satuan = $it['satuan'];
                    $model->cbotable = $it['cbotable'];
                    $model->emrfk = $it['emrfk'];
                    $model->save();
                }
            }

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }
        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getTreeFormularium(Request $request)
    {
        $this->kdProfile = $_SESSION['kdProfile'];
        $dataRaw = \DB::table('formularium_t as emr')
//            ->where('emr.kdprofile',$this->kdProfile)
            ->where('emr.statusenabled', true)
            ->where('emr.namaemr', $request['namaemr'])
            ->select('emr.*')
            ->orderBy('emr.nourut');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
            $dataraw3[] = array(
                'id' => $dataRaw2->id,
                'kdprofile' => $dataRaw2->kdprofile,
                'statusenabled' => $dataRaw2->statusenabled,
                'kodeexternal' => $dataRaw2->kodeexternal,
                'namaexternal' => $dataRaw2->namaexternal,
                'reportdisplay' => $dataRaw2->reportdisplay,
                'namaemr' => $dataRaw2->namaemr,
                'caption' => $dataRaw2->caption,
                'headfk' => $dataRaw2->headfk,
                'nourut' => $dataRaw2->nourut,
                'text' => $dataRaw2->caption,
                'state' => array(
                    'selected' => false,
                ),
            );
        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                    $elements[$parent_id]['children'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }

        $data = recursiveElements($data);

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);

    }

    public function getDetailFormularium(Request $request)
    {
        $this->kdProfile = $_SESSION['kdProfile'];
        $data = DB::table('formulariumd_t')
            ->select('namagenerik')
            ->where('statusenabled',true)
            ->where('emrfk',$request['id'])
            ->groupBy('namagenerik')
//            ->orderBy('nourut')
            ->get();

        $attrs = DB::table('formulariumd_t')
            ->where('statusenabled',true)
            ->where('emrfk',$request['id'])
            ->orderBy('nourut')
            ->get();

        foreach ($data as $key => $value) {
            $value->details = [];
            foreach ($attrs  as $v2){
                if($v2->namagenerik == $value->namagenerik){
                    $value->details []= $v2   ;
                }
            }
        }

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

}
