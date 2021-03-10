<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 08/08/2018
 * Time: 16.17
 */
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 9/19/2017
 * Time: 11:44 PM
 */
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 08/08/2019
 * Time: 14:04
 */

namespace App\Http\Controllers\RawatJalan;

use App\Http\Controllers\ApiController;
use App\Master\KelompokTransaksi;
use App\Master\Pasien;
use App\Master\TempatTidur;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\IndikatorPasienJatuh;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\SuratKeterangan;
use App\Transaksi\DiagnosaPasien;
use App\Transaksi\PemakaianAsuransi;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPengeluaran;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\PasienDaftar;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\StrukPelayananPenjamin;
use App\Transaksi\TempBilling;
use App\Master\JenisPetugasPelaksana;
use App\Master\Pegawai;
use App\Master\Ruangan;
use App\Master\Departemen;

//use App\Transaksi\StrukPelayananDetailK;
use App\Transaksi\HistoriCetakDokumen;
use App\Traits\Valet;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\LogAcc;
use App\Traits\SettingDataFixedTrait;
use Carbon\Carbon;

class RawatJalanController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDataComboDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile',$idProfile)
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile',$idProfile)
            ->where('dept.id', '18')
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile',$idProfile)
            ->whereIn('dept.id', [16,17,35])
            ->orderBy('dept.namadepartemen')
            ->get();
        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenRawatJalanFix',$idProfile));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }
        $ruanganRajal = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->where('statusenabled',true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
            ->orderBy('ru.namaruangan')
            ->get();

        $ruanganRanap = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->where('statusenabled',true)
            ->wherein('ru.objectdepartemenfk', [16,17,35])
            ->orderBy('ru.namaruangan')
            ->get();

//        $dataDokter = \DB::table('pegawai_m as ru')
//            ->where('ru.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
//            ->orderBy('ru.namalengkap')
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
//        $dataKelompok = \DB::table('kelompokpasien_m as kp')
//            ->select('kp.id', 'kp.kelompokpasien')
//            ->where('kp.statusenabled', true)
//            ->orderBy('kp.kelompokpasien')
//            ->get();
//
//        $dataKelas = \DB::table('kelas_m as kl')
//            ->select('kl.id', 'kl.reportdisplay')
//            ->where('kl.statusenabled', true)
//            ->orderBy('kl.reportdisplay')
//            ->get();
//
//        $pembatalan = \DB::table('pembatal_m as p')
//            ->select('p.id', 'p.name')
//            ->where('p.statusenabled', true)
//            ->orderBy('p.name')
//            ->get();
//
//        $jenisDiagnosa = \DB::table('jenisdiagnosa_m as jd')
//            ->select('jd.id', 'jd.jenisdiagnosa')
////            ->where('jd.id',5)
//            ->where('jd.statusenabled', true)
//            ->orderBy('jd.jenisdiagnosa')
//            ->get();
//
//        $kdeDiagnosa = \DB::table('diagnosa_m as dm')
//            ->select('dm.id','dm.kddiagnosa')
//            ->where('dm.statusenabled', true)
//            ->orderBy('dm.id')
//            ->get();
//
//        $Diagnosa = \DB::table('diagnosa_m as dm')
//            ->select('dm.id','dm.namadiagnosa')
//            ->where('dm.statusenabled', true)
//            ->orderBy('dm.id')
//            ->get();

        $result = array(
//            'departemen' => $dataDepartemen,
//            'kelompokpasien' => $dataKelompok,
//            'dokter' => $dataDokter,
//            'datalogin' => $dataLogin,
//            'kelas' => $dataKelas,
            'dept' => $dept,
            'ruanganRajal' => $ruanganRajal,
            'ruanganRanap' => $ruanganRanap,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
//            'pembatalan' => $pembatalan,
//            'jenisdiagnosa'=> $jenisDiagnosa,
//            'diagnosa'=> $Diagnosa,
//            'kddiagnosa'=> $kdeDiagnosa,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getDokters(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataDokters = \DB::table('pegawai_m as p')
            ->select('p.id','p.namalengkap')
            ->where('p.kdprofile',$idProfile)
            ->where('p.statusenabled', true)
            ->where('p.objectjenispegawaifk', 1)
            ->orderBy('p.namalengkap')
            ->get();

        $result = array(
            'dokter'=> $dataDokters,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getDataComboSurat( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $js = \DB::table('jenissurat_m')
            ->select('*')
            ->where('statusenabled',true)
            ->get();
        $pegawai = \DB::table('pegawai_m as pg ')
            ->select('pg.namalengkap', 'pg.id')
            ->where('pg.kdprofile',$idProfile)
            ->where('statusenabled',true)
            ->get();
        $result = array(
            'jenisSurat' => $js,
            'listPegawai'=>$pegawai,
            'message' => 'ridwan',
        );
        return $this->respond($result);
    }
    public function getDataComboOperator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('pg.kdprofile',$idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.kdprofile',$idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile',$idProfile)
            ->where('dept.id', '18')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile',$idProfile)
            ->whereIn('dept.id', [18, 16])
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('ru.namalengkap')
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
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.reportdisplay')
            ->where('kl.statusenabled', true)
            ->orderBy('kl.reportdisplay')
            ->get();

        $pembatalan = \DB::table('pembatal_m as p')
            ->select('p.id', 'p.name')
            ->where('p.statusenabled', true)
            ->orderBy('p.name')
            ->get();

        $jenisDiagnosa = \DB::table('jenisdiagnosa_m as jd')
            ->select('jd.id', 'jd.jenisdiagnosa')
//            ->where('jd.id',5)
            ->where('jd.statusenabled', true)
            ->orderBy('jd.jenisdiagnosa')
            ->get();

//        $kdeDiagnosa = \DB::table('diagnosa_m as dm')
//            ->select('dm.id', 'dm.kddiagnosa')
//            ->where('dm.statusenabled', true)
//            ->orderBy('dm.id')
//            ->get();
//
//        $Diagnosa = \DB::table('diagnosa_m as dm')
//            ->select('dm.id', 'dm.namadiagnosa')
//            ->where('dm.statusenabled', true)
//            ->orderBy('dm.id')
//            ->get();

        $KelompokKerjaHead = \DB::table('kelompokkerjahead_m as dm')
            ->select('dm.id', 'dm.kelompokkerjahead')
            ->where('dm.statusenabled', true)
            ->orderBy('dm.id')
            ->get();

        $KelompokKerja = \DB::table('kelompokkerja_m as dm')
            ->select('dm.id', 'dm.kelompokkerja')
            ->where('dm.statusenabled', true)
            ->orderBy('dm.id')
            ->get();

        $dataJenisKelamin = \DB::table('jeniskelamin_m as jk')
            ->where('jk.statusenabled', true)
            ->orderBy('jk.jeniskelamin')
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'dept' => $dept,
            'ruanganRi' => $ruanganRi,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
            'pembatalan' => $pembatalan,
            'jenisdiagnosa' => $jenisDiagnosa,
//            'diagnosa' => $Diagnosa,
//            'kddiagnosa' => $kdeDiagnosa,
            'kelompokkerjahead' => $KelompokKerjaHead,
            'kelompokkerja' => $KelompokKerja,
            'pegawaiLogin' => $dataPegawai->namalengkap,
            'jeniskelamin' => $dataJenisKelamin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarRegistrasiDokterRajal(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            // ->leftjoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
//            ->leftjoin('strukpelayanan_t as sp','sp.norec','=','pd.nostruklastfk')
//            ->leftjoin('strukbuktipenerimaan_t as sbm','sbm.norec','=','pd.nosbmlastfk')
            ->join('pasien_m as ps', 'ps.id','=','pd.nocmfk')
            ->leftjoin('alamat_m as alm', 'ps.id','=','alm.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->join('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            // ->leftJoin('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','apd.objectpegawaifk')
            ->leftJoin('pegawai_m as pg2','pg2.id','=','apd.residencefk')
            ->Join('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->leftjoin('antrianpasienregistrasi_t as apr', function ($join){
                $join->on('apr.noreservasi','=','pd.statusschedule');
                $join->on('apr.nocmfk','=','pd.nocmfk');
            })
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('asuransipasien_m as asu', 'pa.objectasuransipasienfk', '=', 'asu.id')
            ->leftjoin('kelas_m as klstg','klstg.id','=','asu.objectkelasdijaminfk')
//            ->leftjoin('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
//            ->leftjoin('diagnosapasien_t AS dp','dp.norec','=','ddp.objectdiagnosapasienfk')
//            ->leftjoin('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
            // ->leftjoin('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->select('pd.tglregistrasi','ps.nocm','pd.nocmfk','pd.noregistrasi','ps.namapasien','ps.tgllahir','jk.jeniskelamin','apd.objectruanganfk','ru.namaruangan','kls.id as idkelas','kls.namakelas',
                            'pd.objectkelompokpasienlastfk','kp.kelompokpasien','rek.namarekanan','apd.objectpegawaifk','pg.namalengkap as namadokter','pd.norec as norec_pd','apd.norec as norec_apd','apd.objectasalrujukanfk',
                            'apd.tgldipanggildokter','apd.statuspasien as statuspanggil','pd.statuspasien','apd.tgldipanggildokter','apd.tgldipanggilsuster','apr.noreservasi','apd.noantrian',
                            'apr.tanggalreservasi',  'alm.alamatlengkap','klstg.namakelas as kelasdijamin', 'apd.tglselesaiperiksa','pd.objectruanganlastfk',
//                'ps.foto',
                'apd.norec as norec_apd',
                'ru.ipaddress','ps.iskompleks','apd.residencefk','pg2.namalengkap as residence'
                // ,DB::raw('case when pp.noregistrasifk is null then \'false\' else \'true\' end as statuslayanan'))
                ,DB::raw('case when apd.ispelayananpasien is null then \'false\' else \'true\' end as statuslayanan'))
            // ->whereNull('br.norec')
            ->where('apd.kdprofile', $idProfile)
            ->where('pd.statusenabled',true)
            ->where('ps.statusenabled',true);
//            ->whereNotIn('ru.objectdepartemenfk',[27,3]);
        // ->groupBy('pd.tglregistrasi','ps.nocm','pd.noregistrasi','ps.namapasien','ps.tgllahir','jk.jeniskelamin','apd.objectruanganfk','ru.namaruangan','kls.id','kls.namakelas',
        //           'kp.kelompokpasien','rek.namarekanan','apd.objectpegawaifk','pg.namalengkap','br.norec','pd.norec','apd.norec','apd.objectasalrujukanfk',
        //           'apd.tgldipanggildokter','apd.statuspasien','pd.statuspasien','apd.tgldipanggildokter','apd.tgldipanggilsuster','apr.noreservasi',
        //           'apr.tanggalreservasi','pp.noregistrasifk','apd.noantrian','alm.alamatlengkap');

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $filter['tglAkhir']);
        }
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruangId']);
        }
        if (isset($filter['dokId']) && $filter['dokId'] != "" && $filter['dokId'] != "undefined") {
            $data = $data->where('pg.id', '=', $filter['dokId']);
        }
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $data = $data->where('pd.noregistrasi','=', $filter['noreg']);
        }
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $filter['norm'] . '%');
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $filter['nama'] . '%');
        }
        if (isset($filter['norecApd']) && $filter['norecApd'] != "" && $filter['norecApd'] != "undefined") {
            $data = $data->where('apd.norec',  $filter['norecApd'] );
        }
        if(isset($request['ruanganArr']) && $request['ruanganArr']!="" && $request['ruanganArr']!="undefined"){
            $arrRuang = explode(',',$request['ruanganArr']) ;
            $kodeRuang = [];
            foreach ( $arrRuang as $item){
                $kodeRuang[] = (int) $item;
            }
            $data = $data->whereIn('ru.id',$kodeRuang);
        }
        if (isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $data = $data->take($filter['jmlRow']);
        }
        $data = $data->whereIn('ru.objectdepartemenfk',[18,24,28,26,30,34]);
//        $data = $data->orderBy('pd.tglregistrasi');
        $data = $data->orderBy('apd.noantrian');
        $data = $data->get();
        $norecaPd = '';
        foreach ($data as $ob){
            $norecaPd = $norecaPd.",'".$ob->norec_apd . "'";
            $ob->kddiagnosa = [];
        }
        $norecaPd = substr($norecaPd, 1, strlen($norecaPd)-1);
        $diagnosa = [];
        if($norecaPd!= ''){
            $diagnosa = DB::select(DB::raw("
               select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
               from detaildiagnosapasien_t as ddp
               left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
               left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
               where ddp.noregistrasifk in ($norecaPd) "));
            $i = 0;
//            $d['d']= $diagnosa;
//            $d['da']= $norecaPd;
//            return $this->respond($d);
           foreach ($data as $h){
               $data[$i]->kddiagnosa = [];
               foreach ($diagnosa as $d){
                   if($data[$i]->norec_apd == $d->norec_apd){
                       $data[$i]->kddiagnosa[] = $d->kddiagnosa;
                   }
               }
               $i++;
//               if($data[$i]->kddiagnosa!=''){
//                   $data[$i]->kddiagnosa = substr($data[$i]->kddiagnosa,1);
//               }
           }
        }
        //        if(count($data) > 0){
//            foreach ($data as $item){
//                if($item->foto != null ){
//                    $item->foto = "data:image/jpeg;base64," . base64_encode($item->foto);
//                }
//            }
//        }
        return $this->respond($data);
    }
    public function pasienBatalPanggil(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            if ($request['norec_apd']!=null) {
                $ddddd = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                            'statusantrian' => 0]

                    );
            }
            $transMessage = "Sukses";


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {

            DB::commit();
            $result = array(
                "status" => 201,
//                "message" =>   $transMessae,
                "message" => $transMessage,
                "struk" => $ddddd,//$noResep,,//$noResep,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan  Tanggal Pulang Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "struk" => $ddddd,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateDokterAntrian(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            if ($request['norec_apd']!=null) {
                $apd =  AntrianPasienDiperiksa::where('norec', $request['norec_apd'])->where('kdprofile', $idProfile)->first();
                $ddddd = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->where('kdprofile', $idProfile)
                    ->update([
                            'objectpegawaifk' => $request['iddokter']
                        ]

                    );

                $pasienDaftar = PasienDaftar::where('norec',$apd->noregistrasifk)
                    ->where('kdprofile', $idProfile)
                    ->update([
                    'objectpegawaifk' => $request['iddokter']
                ]);
            }
            $transMessage = "Sukses";

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" =>   $transMessae,
                "message" => $transMessage,
                "struk" => $ddddd,//$noResep,,//$noResep,
                "as" => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan  Tanggal Pulang Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "struk" => $ddddd,
                "as" => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveIndikatorPasienJatuh(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if ($request['norec'] == ''){
                $new = new IndikatorPasienJatuh();
                $new->kdprofile = $idProfile;
                $new->norec = $new->generateNewId();

            }else{
                $new = IndikatorPasienJatuh::where('norec', $request['norec'])->first();
            }

            $new->statusenabled = $request['statusenabled'];
            $pasien = Pasien::where('nocm', $request['nocm'])->first();
            if(isset($request['nocmfk'])){
                $new->nocmfk = $request['nocmfk'] ;
            }ELSE{
                $new->nocmfk =$pasien->id;
            }

            $new->noregistrasifk = $request['noregistrasifk'] ;
            $new->tgljatuh = $request['tgljatuh'] ;
            $new->keterangan = $request['keterangan'] ;
            $new->jumlah = $request['jumlah'] ;
            $new->save();

            $transStatus = 1;
        } catch (\Exception $e) {
            $transStatus = false;
        }

        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'result' => $new,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function savePanggilDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $apd = AntrianPasienDiperiksa::where('norec',$request['norec_apd'])->where('kdprofile', $idProfile)->first();
            if(isset($request['kelompokUser']) && $request['kelompokUser'] != 'suster' ){

                if($apd->tgldipanggildokter == null){
                    AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'tgldipanggildokter' => date('Y-m-d H:i:s'),
                        ]);
                }

            }else{
                if($apd->tgldipanggilsuster == null && isset($request['vitalsign'])) {
                    AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'tgldipanggilsuster' => date('Y-m-d H:i:s'),
                        ]);
                }
            }
            $transStatus ='true';
            $transMessage = "Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveDaftarSurat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
        $jenisSurat = DB::table('jenissurat_m')
            ->select('*')
            ->where('id',$request['jenissuratID'])
            ->first();
        $kodeSurat = '';
        if(!empty($jenisSurat)){
            $kodeSurat =$jenisSurat->kodeexternal;
        }

        $bln = date('m');
        $romawi = $this->KonDecRomawi($bln);
        $pre ='/RSM/'.$kodeSurat.'/'.$romawi.'/'.date('Y');
        $pre2 =date('Y').'/'.$bln.'/';
//        $nosurat= $this->genCode(new SuratKeterangan(),'nosint',21,$pre);
        $nosint= $pre2.$this->genCode2(new SuratKeterangan(),'nosint',3).'/RSM/'.$kodeSurat;
        $nosurat = substr(trim($nosint),8,3).$pre;

//        return $nosint;

            if($request['norec'] == ''){
                $newptp = new SuratKeterangan();
                $newptp->norec = $newptp->generateNewId();
                $newptp->statusenabled = true;
                $newptp->kdprofile = $idProfile;
                $newptp->pasiendaftarfk = $request['pasiendaftarfk'];
            }else{
                $newptp = SuratKeterangan::where('norec',$request['norec'])->first();
                $nosint= str_replace(substr(trim($newptp->nosint),16),$kodeSurat,$newptp->nosint);
                $nosurat=str_replace(substr(trim($newptp->nosint),16),$kodeSurat,$newptp->nosurat);
            }


            $newptp->tglsurat =  $request['tglsurat'];
            $newptp->pegawaifk =  $request['namapegID'];
            $newptp->dokterfk =  $request['dokterID'];
            $newptp->jenissuratfk =  $request['jenissuratID'];
            $newptp->keterangan =  $request['keterangan'];
            $newptp->nosurat =  $nosurat;
            $newptp->nosint = $nosint;


            $newptp->save();

            $transMessage = "Simpan Surat Keterangan Berhasil";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Surat Keterangan gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "data" =>$newptp,
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
//				"noRec" =>$noRec,
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDetailpasienSurat(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->join('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
            ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
            ->join('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
            ->leftjoin('pendidikan_m as pdd', 'pas.objectpendidikanfk','=','pdd.id' )
            ->leftjoin('alamat_m as al','al.nocmfk','=','pas.id')
            ->leftjoin ('pekerjaan_m as pk','pas.objectpekerjaanfk','=','pk.id')
            ->select( 'apd.norec as norec_apd', 'pd.nocmfk','al.alamatlengkap','pdd.pendidikan','pk.pekerjaan',
                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
                'kp.id as kpid', 'kp.kelompokpasien as jenisPasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
                'pas.id as pasid', 'pas.nocm as noCm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jenisKelamin', 'pd.noregistrasi as noRegistrasi', 'pas.namapasien as namaPasien',
                'pd.tglregistrasi as tglMasuk', 'pd.norec as norec_pd', 'pd.tglpulang as tglPulang', 'pas.notelepon',
                'pd.objectrekananfk as rekananid', 'kls2.id as klsid2', 'kls2.namakelas as kelasRawat',
                'rk.namarekanan as namaPenjamin','ru2.namaruangan as lastRuangan','sp.nostruk','sp.norec as strukfk','pd.statuspasien as StatusPasien'
            )
            ->where('pd.kdprofile',$idProfile)
            ->take(1)
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->get();


        $result = array(
            'data' => $pelayanan,
            'message' => 'as@epic',
        );

        return $this->respond($pelayanan);
    }
    public function getDaftarSurat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('suratketerangan_t as sk')
            ->join('pasiendaftar_t as pd', 'pd.norec','=','sk.pasiendaftarfk')
            ->leftJoin('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            //            ->leftjoin('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sk.pegawaifk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pegawai_m as pg2', 'pg2.id', '=', 'sk.dokterfk')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJoin('golongandarah_m as gol','gol.id','=','ps.objectgolongandarahfk')
            ->leftJoin('jenissurat_m as js','js.id','=','sk.jenissuratfk')
            ->select('sk.*','pd.tglregistrasi','pd.noregistrasi','ps.namapasien','ps.nocm','ps.tgllahir',
                'pg2.namalengkap as namadokter','pg.namalengkap as namapegawai','jk.jeniskelamin','alm.alamatlengkap','gol.golongandarah','js.name as namasurat')
            ->where('sk.kdprofile', $idProfile)
            ->where('sk.statusenabled', true);

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('sk.tglsurat', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('sk.tglsurat', '<=', $filter['tglAkhir']);
        }

//       if (isset($filter['deptId']) && $filter['deptId'] != "" && $filter['deptId'] != "undefined") {
//           $data = $data->where('dept.id', '=', $filter['deptId']);
//       }
//       if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
//           $data = $data->where('ru.id', '=', $filter['ruangId']);
//       }
//       if (isset($filter['kelId']) && $filter['kelId'] != "" && $filter['kelId'] != "undefined") {
//           $data = $data->where('kp.id', '=', $filter['kelId']);
//       }
        if (isset($filter['dokId']) && $filter['dokId'] != "" && $filter['dokId'] != "undefined") {
            $data = $data->where('pg2.id', '=', $filter['dokId']);
        }
//       if (isset($filter['sttts']) && $filter['sttts'] != "" && $filter['sttts'] != "undefined") {
//           $data = $data->where('pd.statuspasien', '=', $filter['sttts']);
//       }
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
        $data = $data->orderBy('sk.tglsurat');
//       $data = $data->groupBy('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi',
//           'ru.namaruangan', 'ps.namapasien',
//           'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap', 'pd.tglpulang', 'pd.statuspasien',
//           'pa.nosep', 'br.norec', 'pa.norec', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk', 'pd.objectruanganlastfk',
//           'pd.nostruklastfk', 'ps.tgllahir','pd.objectkelasfk','kls.namakelas','ps.objectjeniskelaminfk','jk.jeniskelamin',
//           'alm.alamatlengkap','pd.jenispelayanan','jp.jenispelayanan','gol.golongandarah');
//        $data = $data->take($filter['jmlRows']);
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'ridwan',
        );
        return $this->respond($result);
    }
    public function getDaftarPasienByDiagnosa(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftjoin('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin('diagnosatindakanpasien_t as dtp','dtp.objectpasienfk','=','apd.norec')
            ->leftjoin('detaildiagnosapasien_t as ddp','ddp.noregistrasifk','=', 'apd.norec')
            ->leftjoin('detaildiagnosatindakanpasien_t as ddtp','ddtp.objectdiagnosatindakanpasienfk','=','dtp.norec')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
            ->leftjoin('diagnosatindakan_m as dt','dt.id','=','ddtp.objectdiagnosatindakanfk')
            ->select('pd.norec','pd.statusenabled','pd.tglregistrasi','ps.nocm','pd.nocmfk','pd.noregistrasi','ru.namaruangan','ps.namapasien','kp.kelompokpasien',
                'rek.namarekanan','pg.namalengkap as namadokter','pd.tglpulang','pd.statuspasien','pa.norec as norec_pa','pa.objectasuransipasienfk',
                'pd.objectpegawaifk as pgid','pd.objectruanganlastfk','pa.nosep as nosep','br.norec as norec_br','pd.nostruklastfk',
                'ddp.objectdiagnosafk','dg.kddiagnosa','dg.namadiagnosa','ddp.objectjenisdiagnosafk',
                'ddtp.objectdiagnosatindakanfk','dt.kddiagnosatindakan','dt.namadiagnosatindakan')
            ->where('pd.kdprofile', $idProfile)
            ->whereNull('br.norec')
            ->where('ddp.objectjenisdiagnosafk',1);

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
        if (isset($filter['objectdiagnosafk']) && $filter['objectdiagnosafk'] != "" && $filter['objectdiagnosafk'] != "undefined") {
            $data = $data->where('ddp.objectdiagnosafk', '=', $filter['objectdiagnosafk']);
        }
        if (isset($filter['objectdiagnosatindakanfk']) && $filter['objectdiagnosatindakanfk'] != "" && $filter['objectdiagnosatindakanfk'] != "undefined") {
            $data = $data->where('ddtp.objectdiagnosatindakanfk', '=', $filter['objectdiagnosatindakanfk']);
        }
        $data = $data->orderBy('pd.noregistrasi');
        $data = $data->groupBy('pd.norec','pd.statusenabled','pd.tglregistrasi','ps.nocm','pd.nocmfk','pd.noregistrasi','ru.namaruangan','ps.namapasien','kp.kelompokpasien',
            'rek.namarekanan','pg.namalengkap','pd.tglpulang','pd.statuspasien','pa.nosep','br.norec','pa.norec','pa.objectasuransipasienfk','pd.objectpegawaifk',
            'pd.objectruanganlastfk','pd.nostruklastfk','dg.kddiagnosa','dg.namadiagnosa','dt.kddiagnosatindakan',
            'ddtp.objectdiagnosatindakanfk','dt.namadiagnosatindakan','dg.kddiagnosa','dt.kddiagnosatindakan','ddp.objectdiagnosafk','ddp.objectjenisdiagnosafk');
//        $data = $data->take(50);
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'Cepot',
        );
        return $this->respond($result);
    }
    public function getOrderKonsul(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $arrRuangId = [];
        if(isset($request['perawatId']) && $request['perawatId'] != ''){
            $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
                ->join('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
                ->select('ru.id','ru.namaruangan')
                ->where('mlu.kdprofile', $idProfile)
                ->where('mlu.objectloginuserfk',$request['perawatId'])
                ->get();

            if(count($dataruangan) > 0){
                foreach ($dataruangan as $item){
                    $arrRuangId []  =$item->id ;
                }
            }
        }


        $kelTrans = KelompokTransaksi::where('kelompoktransaksi','KONSULTASI DOKTER')->first();
        $data= \DB::table('strukorder_t as so')
            ->Join ('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->Join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','so.objectruanganfk')
            ->leftJoin('ruangan_m as rutuju','rutuju.id','=','so.objectruangantujuanfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','so.objectpegawaiorderfk')
            ->leftJoin('pegawai_m as pet','pet.id','=','so.objectpetugasfk')
            ->leftJoin('antrianpasiendiperiksa_t as apd','apd.objectstrukorderfk','=','so.norec')
            ->select('so.norec','so.noorder','so.tglorder','ru.namaruangan as ruanganasal','pg.namalengkap',
                'rutuju.namaruangan as ruangantujuan','pet.namalengkap as pengonsul',
                'pd.noregistrasi','pd.tglregistrasi','ps.nocm','so.keteranganorder','pd.norec as norec_pd',
                'ps.namapasien','pg.id as pegawaifk','so.objectruangantujuanfk','so.objectruanganfk','apd.norec as norec_apd')
            ->where('so.kdprofile', $idProfile)
            ->where('so.statusenabled',true)
            ->wherenull('apd.norec')
            ->where('so.objectkelompoktransaksifk',$kelTrans->id)
            ->orderBy('so.tglorder','desc');
        if(isset($request['norecpd']) && $request['norecpd'] != ''){
            $data = $data->where('pd.norec', $request['norecpd']);
        }
        if(isset($request['dokterid']) && $request['dokterid'] != ''){
            $data = $data->where('pg.id', $request['dokterid']);
        }
        if(isset($request['perawatId']) && $request['perawatId'] != ''){
            $data = $data->whereIn('rutuju.id',$arrRuangId);
        }

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }
    public function saveKonsulFromOrder(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $pd = PasienDaftar::where('norec',$request['norec_pd'])->first();
            $apd = AntrianPasienDiperiksa::where('noregistrasifk',$request['norec_pd'])->first();
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

            // $strukOrder = StrukOrder::where('norec',$request['norec_so'])->update([
            //         'keteranganlainnya' =>$request['jawaban'] 
            //     ]);

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
    public function getComboS(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $idProfile)
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();



        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('ru.namalengkap')
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
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();


        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,

            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getPemeriksaanKeluarLab(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pelayananpasien_t as pp ')
            ->leftjoin('antrianpasiendiperiksa_t as apd','apd.norec','=','pp.noregistrasifk')
            ->LEFTJOIN('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->LEFTJOIN('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','pd.objectruanganlastfk')
            ->LEFTJOIN('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->LEFTJOIN('pegawai_m as p','p.id','=','apd.objectpegawaifk')
            ->LEFTJOIN('produk_m as prd','prd.id','=','pp.produkfk')
            ->select('pp.norec','pd.norec as norecpd', 'pd.noregistrasi','pp.tglpelayanan' ,
                'ru.namaruangan' ,'p.namalengkap','ps.nocm','ps.namapasien',  'kps.kelompokpasien',
                'prd.namaproduk','pp.jumlah','pp.keteranganlain','pd.tglregistrasi','ru2.namaruangan as ruanganlast'
            )
            ->where('pp.kdprofile',$idProfile);
        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('pp.tglpelayanan','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $data = $data->where('pp.tglpelayanan','<=', $request['tglAkhir']);
        }
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noregistrasi']);
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'.$request['nocm'].'%');
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'.$request['namapasien'].'%');
        }
//        if(isset($request['noOrder']) && $request['noOrder']!="" && $request['noOrder']!="undefined"){
//            $data = $data->where('so.noorder','ilike','%'.$request['noOrder'].'%');
//        }
        if(isset($request['kelPasienId']) && $request['kelPasienId']!="" && $request['kelPasienId']!="undefined"){
            $data = $data->where('kps.id','=', $request['kelPasienId']);
        }
        if(isset($request['ruangId']) && $request['ruangId']!="" && $request['ruangId']!="undefined"){
            $data = $data->where('ru2.id','=', $request['ruangId']);
        }
        if(isset($request['deptId']) && $request['deptId']!="" && $request['deptId']!="undefined"){
            $data = $data->where('ru.objectdepartemenfk','=', $request['deptId']);
        }
        if(isset($request['jmlRows']) && $request['jmlRows']!="" && $request['jmlRows']!="undefined"){
            $data = $data->take($request['jmlRows']);
        }
        $data = $data->where('pp.keteranganlain','Pemeriksaan Keluar');
//        $data = $data->where('apd.objectruanganfk',276);
        $data = $data->orderBy('pp.tglpelayanan');
//        $data = $data->distinct();
        $data = $data->get();

//        //$results =array();
//        foreach ($data as $item){
//            $details = DB::select(DB::raw("
//                            select so.tglorder,so.noorder,
//                            pr.id,pr.namaproduk,op.qtyproduk
//                            from strukorder_t as so
//                            left join orderpelayanan_t as op on op.noorderfk = so.norec
//                            left join pasiendaftar_t as pd on pd.norec=so.noregistrasifk
//                            left join produk_m as pr on pr.id=op.objectprodukfk
//                            left join ruangan_m as ru on ru.id=so.objectruanganfk
//                            left join ruangan_m as ru2 on ru2.id=so.objectruangantujuanfk
//                            left join pegawai_m as p on p.id=so.objectpegawaiorderfk
//                            where so.noorder=:noorder
//                            and op.keteranganlainnya = 'isPemeriksaanKeluar'"),
//                array(
//                    'noorder' => $item->noorder,
//                )
//            );
//            $results[] = array(
//                'tglorder' => $item->tglorder,
//                'noorder' => $item->noorder,
//                'norec' => $item->norec,
//                'nocm' => $item->nocm,
//                'namapasien' => $item->namapasien,
//                'noregistrasi' => $item->noregistrasi,
//                'kelompokpasien' => $item->kelompokpasien,
//                'norecpd' => $item->norecpd,
////                'norecapd' => $item->norecapd,
//                'namaruanganasal' => $item->ruanganasal,
//                'namaruangantujuan' => $item->ruangantujuan,
//                'dokter' => $item->namalengkap,
//                'statusorder'=>$item->statusorder,
//                'details' => $details,
//            );
//        }

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }
    public function savePasienKompleks(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $pasien = Pasien::where('nocm',$request['nocm'])
                ->where('kdprofile',$idProfile)
                ->update([
                    'iskompleks' => $request['iskompleks']
                ]
            );

            $transStatus = 'true';
            $transMessage = "Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,

            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveResidence(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {

            if ($request['norec_apd']!=null) {
                $apd =  AntrianPasienDiperiksa::where('norec', $request['norec_apd'])->where('kdprofile',$idProfile)->first();
                $ddddd = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->where('kdprofile',$idProfile)
                    ->update([
                            'residencefk' => $request['iddokter']
                        ]
                    );

                $pasienDaftar = PasienDaftar::where('norec',$apd->noregistrasifk)
                    ->where('kdprofile',$idProfile)
                    ->update([
                    'residencefk' => $request['iddokter']
                ]);
            }
            $transMessage = "Sukses";

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            $result = array(
                "status" => 201,
//                "message" =>   $transMessae,
                "message" => $transMessage,
                "struk" => $ddddd,//$noResep,,//$noResep,
                "as" => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "struk" => $ddddd,
                "as" => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarKonsulFromOrder(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->Join('strukorder_t as so','so.norec','=','apd.objectstrukorderfk')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->join('pasien_m as ps', 'ps.id','=','pd.nocmfk')
            ->leftjoin('alamat_m as alm', 'ps.id','=','alm.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->join('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            // ->leftJoin('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','apd.objectpegawaifk')
            ->leftJoin('pegawai_m as pg2','pg2.id','=','apd.residencefk')
            ->Join('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->leftjoin('antrianpasienregistrasi_t as apr', function ($join){
                $join->on('apr.noreservasi','=','pd.statusschedule');
                $join->on('apr.nocmfk','=','pd.nocmfk');
            })
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('asuransipasien_m as asu', 'pa.objectasuransipasienfk', '=', 'asu.id')
            ->leftjoin('kelas_m as klstg','klstg.id','=','asu.objectkelasdijaminfk')
            ->select('apd.tglmasuk as tglregistrasi','ps.nocm','pd.noregistrasi','ps.namapasien','ps.tgllahir','jk.jeniskelamin','apd.objectruanganfk','ru.namaruangan','kls.id as idkelas','kls.namakelas',
                'kp.kelompokpasien','rek.namarekanan','apd.objectpegawaifk','pg.namalengkap as namadokter','pd.norec as norec_pd','apd.norec as norec_apd','apd.objectasalrujukanfk',
                'apd.tgldipanggildokter','apd.statuspasien as statuspanggil','pd.statuspasien','apd.tgldipanggildokter','apd.tgldipanggilsuster','apr.noreservasi','apd.noantrian',
                'apr.tanggalreservasi',  'alm.alamatlengkap','klstg.namakelas as kelasdijamin',
                'ru.ipaddress','ps.iskompleks','apd.residencefk','pg2.namalengkap as residence'
                ,DB::raw('case when apd.ispelayananpasien is null then \'false\' else \'true\' end as statuslayanan'))
            ->where('apd.kdprofile', $idProfile)
            ->where('pd.statusenabled',true)
            ->where('ps.statusenabled',true);

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('apd.tglmasuk', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('apd.tglmasuk', '<=', $filter['tglAkhir']);
        }
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruangId']);
        }
        if (isset($filter['dokId']) && $filter['dokId'] != "" && $filter['dokId'] != "undefined") {
            $data = $data->where('pg.id', '=', $filter['dokId']);
        }
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $data = $data->where('pd.noregistrasi','=', $filter['noreg']);
        }
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $filter['norm'] . '%');
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $filter['nama'] . '%');
        }
        if (isset($filter['norecApd']) && $filter['norecApd'] != "" && $filter['norecApd'] != "undefined") {
            $data = $data->where('apd.norec',  $filter['norecApd'] );
        }
        if(isset($request['ruanganArr']) && $request['ruanganArr']!="" && $request['ruanganArr']!="undefined"){
            $arrRuang = explode(',',$request['ruanganArr']) ;
            $kodeRuang = [];
            foreach ( $arrRuang as $item){
                $kodeRuang[] = (int) $item;
            }
            $data = $data->whereIn('ru.id',$kodeRuang);
        }
        if (isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $data = $data->take($filter['jmlRow']);
        }
        $data = $data->whereIn('ru.objectdepartemenfk',[18,24,28,26,30,34]);
//        $data = $data->orderBy('pd.tglregistrasi');
        $data = $data->orderBy('apd.noantrian');
        $data = $data->get();
//        if(count($data) > 0){
//            foreach ($data as $item){
//                if($item->foto != null ){
//                    $item->foto = "data:image/jpeg;base64," . base64_encode($item->foto);
//                }
//            }
//        }
        return $this->respond($data);
    }

    public function saveSelesaiPeriksa(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $apd = AntrianPasienDiperiksa::where('norec',$request['norec_apd'])->where('kdprofile', $idProfile)->first();
            if(isset($request['kelompokUser']) && $request['kelompokUser'] == 'dokter' ){

                if($apd->tglselesaiperiksa == null){
                    AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                        ->where('kdprofile', $idProfile)
                        ->update([
                            'tglselesaiperiksa' => date('Y-m-d H:i:s'),
                        ]);
                }

            }
            $transStatus ='true';
            $transMessage = "Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function SimpanMeninggalPasien(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        $r_NewPD=$request['pasiendaftar'];
        DB::beginTransaction();
        try{

            $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                    'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                    'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                    'tglmeninggal' => $r_NewPD['tglmeninggal'],
                    'keteranganpenyebabkematian' => $r_NewPD['keterangankematian'],
//                    'objectruanganlastfk' => $r_NewPD['ruangantujuan'],
                ]);

            if ($r_NewPD['nocmfk'] != 'undefined' && $r_NewPD['objectstatuskeluarfk']== 5) {
                $updatePS= Pasien::where('id', $r_NewPD['nocmfk'])
                    ->update([
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                        ]
                    );
            }

            $updateAPD= AntrianPasienDiperiksa::where('norec', $r_NewPD['norec_apd'])->where('kdprofile', $kdProfile)
                ->update([
                        'tglkeluar' => $r_NewPD['tglmeninggal'],
                    ]
                );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            $transMessage = 'SUKSES';
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "GAGAL";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}