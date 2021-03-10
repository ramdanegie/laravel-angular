<?php
/**
 * Created by PhpStorm.
 * User: ea
 * Date: 1/10/2018
 * Time: 10:54 AM
 */

namespace App\Http\Controllers\Jenazah;

use App\Http\Controllers\ApiController;
use App\Master\Pasien;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\LoggingUser;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\StrukOrder;
use App\Transaksi\SuratPermohonanJenazah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;
use Webpatser\Uuid\Uuid;

use App\Transaksi\PengambilanJenazah;



class JenazahController extends ApiController {
    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication = false);
    }

    public function GetDataForCombo(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $ruangan = \DB::table('ruangan_m as r')
            ->select('r.id','r.namaruangan','r.objectdepartemenfk')
            ->where('r.kdprofile', $kdProfile)
            ->where('statusenabled','true')
            ->wherein('objectdepartemenfk',['16','24'])
            ->get();

        $RuanganJenazah = \DB::table('ruangan_m as r')
            ->select('r.id','r.namaruangan','r.objectdepartemenfk')
            ->where('r.kdprofile', $kdProfile)
            ->where('statusenabled','true')
            ->wherein('objectdepartemenfk',['5'])
            ->get();

        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $kdProfile)
            ->whereIn('dp.id',array(5))
            ->where('dp.statusenabled',true)
            ->get();

        $dokter = \DB::table('pegawai_m as p')
            ->select('p.id','p.namalengkap as namadokter')
            ->where('p.kdprofile', $kdProfile)
            ->where('p.objectjenispegawaifk',1)
            ->where('statusenabled','true')
            ->get();    

        $hubunganKeluarga = \DB::table('hubungankeluarga_m as h')
            ->select('h.id','h.hubungankeluarga')
            ->where('h.kdprofile', $kdProfile)
            ->where('statusenabled','true')
            ->get();

        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id','kp.kelompokpasien')
            ->where('kp.kdprofile', $kdProfile)
            ->where('kp.statusenabled',true)
            ->get();

        $dataJenisPetugas = \DB::table('jenispetugaspelaksana_m as kp')
            ->select('kp.id','kp.jenispetugaspe')
            ->where('kp.kdprofile', $kdProfile)
            ->where('kp.statusenabled',true)
            ->get();

        $dataRuanganPelayanan = \DB::table('ruangan_m as r')
            ->select('r.id','r.namaruangan')
            ->where('r.kdprofile', $kdProfile)
            ->where('statusenabled','true')
            ->whereIn('objectdepartemenfk',[18,16,25,29,26,27,28,31,35,3])
            ->get();
        

        $result = array(
            'ruangan'=>$ruangan,
            'ruanganjenazah'=>$RuanganJenazah,
            'dokter'=>$dokter,
            'hubunganKeluarga'=>$hubunganKeluarga,
            'departemen'=>$dataInstalasi,
            'kelompokpasien'=>$dataKelompok,
            'jenispetugaspe'=>$dataJenisPetugas,
            'ruanganpelayanan' => $dataRuanganPelayanan,
        );

        return $this->respond($result);
    }

    public function GetDataPasien(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','p.objectjeniskelaminfk')
            ->leftJoin('kelas_m as kel','kel.id','=','pd.objectkelasfk')

            ->select('pd.norec', 'pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ru.id as kderuangan' ,'p.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.tglmeninggal','pd.statuspasien','jk.jeniskelamin','p.tgllahir as tgllahir','kel.namakelas', 'pd.norec');
//         if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
//             $data = $data->where('pd.noregistrasi', '=',$request['noregistrasi']);
//         }
         $data = $data->where('pd.kdprofile', $kdProfile);
         $data = $data->where('pd.norec', '=',$request['objectpasiendaftarfk']);
         $data = $data->take(1);
         $data = $data->get();

        return $this->respond($data);
    }

    public function SimpanDataPengambilanJenazah(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
        $dataLogin = $request->all();
        $r_R=$request['pengambilanjenazah'];
        if ($request['pengambilanjenazah']['norec']==''){
            $newR = new PengambilanJenazah();
            $newR->norec = $newR->generateNewId();
            $newR->kdprofile = $kdProfile;
        }else{
            $newR =  PengambilanJenazah::where('norec',$request['pengambilanjenazah']['norec'])->where('kdprofile', $kdProfile)->first();
        }
        $newR->objectpasiendaftarfk = $r_R['objectpasiendaftarfk'];
        $newR->tglpengambilan = $r_R['tanggalpengambilan'];
        $newR->namapengambil = $r_R['namapengambil'];
        $newR->objecthubunganfk = $r_R['objecthubunganfk'];
        $newR->alamatlengkap = $r_R['alamatlengkap'];
        $newR->keterangan = $r_R['keterangan'];
        $newR->objectpegawaifk = $r_R['objectpegawaifk'];
        $newR->objectruanganfk = $r_R['objectruanganmeninggalfk'];
        $newR->objectuserfk=$dataLogin['userData']['id'];
        $newR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Data Pengambilan Jenazah Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "ruangan" => $newR,
                "as" => 'as@dd5',
            );
        } else {
            $transMessage = "Simpan Data Pengambilan Jenazah Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "ruangan" => $newR,
                "as" => 'as@dd5',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }   

    public function GetDataJenazah(Request $request) {
        $dataLogin=$request->all();
        $dataJenazah = \DB::table('V_DaftarPasienInstalasiJenazah as v')
            ->select('*')
            ->orderBy('v.tglmeninggal')
            ->take(50);

        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $dataJenazah = $dataJenazah->where('v.nocm', 'ilike','%'. $request['noCm'].'%');
        }

        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined"){
            $dataJenazah = $dataJenazah->where('v.namapasien', 'ilike','%'. $request['nama'].'%');
        }

        // if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $dataJenazah = $dataJenazah->where('v.tglmeninggal2','>=', $request['tglAwal']);
        // }

        // if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $dataJenazah = $dataJenazah->where('v.tglmeninggal2','<=', $request['tglAkhir']);
        // }

        

        $dataJenazah=$dataJenazah->get();
        $result = array(
            'dataJenazah' => $dataJenazah,
            'message' => '2+2=5',
        );

        return $this->respond($result);

    }

    public function getDaftarOrderJenazah(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $result = [];
            $data = \DB::table('strukorder_t as so')
                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
                ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
                ->leftJoin('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
                ->leftJoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->leftJoin('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
                ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                ->leftJoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
                ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'pd.objectpegawaifk')
                ->leftJoin('pelayananpasien_t as pp', 'pp.strukorderfk', '=', 'so.norec')
                ->leftJoin('jenispelayanan_m as jp','jp.kodeinternal','=','pd.jenispelayanan')
//                ->leftJoin('jenispelayanan_m as jp', function($join){
//                    $join->on(DB::raw("jp.id = CAST(pd.jenispelayanan AS INTEGER)"));
//                })
                ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi',
                                  'pd.tglpulang', 'ps.nocm', 'ps.namapasien','klm.jeniskelamin', 'ps.tgllahir','kps.kelompokpasien','dp.namadepartemen',
                                  'pd.objectkelasfk', 'kls.namakelas', 'so.objectruangantujuanfk','so.objectruanganfk','pd.objectkelompokpasienlastfk',
                                  'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan','so.objectpegawaiorderfk','pg.namalengkap as pegawaiorder',
                                  'so.tglorder','pd.jenispelayanan as idjenisPelayanan','jp.jenispelayanan','ru.namaruangan',
                                  'ru2.namaruangan as ruangantujuan','so.tglpelayananakhir','pg2.namalengkap as dpjp',
                                  (DB::raw("case when pp.strukorderfk is null then 'MASUK' else 'Sudah Verifikasi' end as status")))
                ->where('so.kdprofile', $kdProfile)
                ->where('ru2.objectdepartemenfk', $this->settingDataFixed('KdInstalasiJenazah', $kdProfile))
                ->where('so.statusenabled',true);

            if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
                if ($request['isNotVerif'] == true) {
                    $data = $data->whereNull('pp.strukorderfk');
                }
            }

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglorder','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('so.tglorder','<=', $tgl);
        }
        if(isset($request['tglAwalOperasi']) && $request['tglAwalOperasi']!="" && $request['tglAwalOperasi']!="undefined"){
            $data = $data->where('so.tglpelayananakhir','>=', $request['tglAwalOperasi']);
        }
        if(isset($request['tglAkhirOperasi']) && $request['tglAkhirOperasi']!="" && $request['tglAkhirOperasi']!="undefined"){
            $tgl= $request['tglAkhirOperasi'];
            $data = $data->where('so.tglpelayananakhir','<=', $tgl);
        }
        if(isset($request['idRuanganOrder']) && $request['idRuanganOrder']!="" && $request['idRuanganOrder']!="undefined"){
            $data = $data->where('so.objectruanganfk','=', $request['idRuanganOrder']);
        }

        if(isset($request['pegId']) && $request['pegId']!="" && $request['pegId']!="undefined"){
            $data = $data->where('so.objectpegawaiorderfk','=', $request['pegId']);
        }
        if(isset($request['idRuanganTujuan']) && $request['idRuanganTujuan']!="" && $request['idRuanganTujuan']!="undefined"){
            $data = $data->where('so.objectruangantujuanfk','=', $request['idRuanganTujuan']);
        }
        if(isset($request['kelId']) && $request['kelId']!="" && $request['kelId']!="undefined"){
            $data = $data->where('pd.objectkelompokpasienlastfk','=', $request['kelId']);
        }
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noregistrasi'].'%');
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $request['nocm'].'%');
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'. $request['namapasien'].'%');
        }
        if(isset($request['noOrders']) && $request['noOrders']!="" && $request['noOrders']!="undefined"){
            $data = $data->where('so.noorder','ilike','%'. $request['noOrders'].'%');
        }
        if(isset($request['jmlRow']) && $request['jmlRow']!="" && $request['jmlRow']!="undefined"){
            $data = $data->take($request['jmlRow']);
        }
        $data = $data->orderBy('so.noorder','desc');
        $data = $data->distinct();
        $data = $data->get();

        $dataResult=array(
            'message' =>  'inhuman',
            'data' =>  $data,
        );
        return $this->respond($dataResult);
    }

    public function getPasienForensikMedikolegal(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $filter = $request->all();
        $idRuangan = $filter['idRuangan'];
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('batalregistrasi_t as br', 'pd.norec', '=', 'br.pasiendaftarfk')
            ->join ('antrianpasiendiperiksa_t as apd',function($join) use ($idRuangan) {
                $join->on('apd.noregistrasifk','=','pd.norec')
                    ->where('apd.objectruanganfk', '=',$idRuangan);
            })
            ->join('strukorder_t as st','st.noregistrasifk','=','pd.norec')
            ->leftjoin('ruangan_m as ru2', 'ru2.id', '=', 'st.objectruanganfk')
            ->leftjoin('detaildiagnosapasien_t as ddp','ddp.noregistrasifk','=','apd.norec')
            ->leftjoin('diagnosapasien_t as dp','dp.norec','=','ddp.objectdiagnosapasienfk')
            ->leftjoin('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
            ->leftJoin('pengambilanjenazah_t as pj','pj.objectpasiendaftarfk','=','pd.norec')
            ->select('pd.norec as norec_pd','apd.norec as norec_apd','p.tglmeninggal','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                     'kls.namakelas','jk.reportdisplay as jeniskelamin','pg.namalengkap as namadokter','pd.norec as norec_pd',
                     'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','pj.norec as norec_pj','ru2.namaruangan as namaruangan2')
            ->where('pd.kdprofile', $kdProfile)
            ->where('st.objectkelompoktransaksifk',99)
            ->whereNotNull('p.tglmeninggal')
            ->whereNull('br.norec');
            


        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglmeninggal', '>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'] ;//. " 23:59:59";
            $data = $data->where('pd.tglmeninggal', '<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId'] != "" && $filter['instalasiId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['instalasiId']);
        }

        if(isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $filter['idRuangan']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('p.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }

        if(isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if(isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('p.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        if(isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $data = $data->take($filter['jmlRow']);
        }

        $data = $data->groupBy('pd.norec','apd.norec','p.tglmeninggal','pd.tglregistrasi','p.nocm', 'pd.noregistrasi','ru.namaruangan','p.namapasien','kp.kelompokpasien',
                               'kls.namakelas','jk.reportdisplay','pg.namalengkap','pd.norec','pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk',
                               'pj.norec','ru2.namaruangan');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function savePelayananPasienJenazah(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $jenisPetugasPe = \DB::table('mapjenispetugasptojenispegawai_m as mpp')
                ->join ('jenispegawai_m as jp','jp.id','=','mpp.objectjenispegawaifk')
                ->join ('pegawai_m as pg','pg.objectjenispegawaifk','=','jp.id')
                ->join ('jenispetugaspelaksana_m as jpp','jpp.id','=','mpp.objectjenispetugaspefk')
                ->select( 'mpp.objectjenispegawaifk','jp.jenispegawai','mpp.objectjenispetugaspefk' ,'jpp.jenispetugaspe',
                    'pg.namalengkap','pg.id'
                )
                ->where('mpp.kdprofile', $kdProfile)
                ->where('pg.id', $request['objectpegawaiorderfk'])
                ->first();

            if($request['norec_pp']=='') {
                $pd = PasienDaftar::where('norec',$request['norec_pd'])->first();
                $dataAPD = new AntrianPasienDiperiksa();
                $dataAPD->norec = $dataAPD->generateNewId();
                $dataAPD->kdprofile = $kdProfile;
                $dataAPD->objectasalrujukanfk = 1;
                $dataAPD->statusenabled = true;
                $dataAPD->objectkelasfk = $request['objectkelasfk'];
                $dataAPD->noantrian = 1;
                $dataAPD->noregistrasifk = $request['norec_pd'];
                $dataAPD->objectpegawaifk = $request['objectpegawaiorderfk'];
                $dataAPD->objectruanganfk = $request['objectruangantujuanfk'];
                $dataAPD->statusantrian = 0;
                $dataAPD->statuspasien = 1;
                $dataAPD->objectstrukorderfk = $request['norec_so'];
                $dataAPD->tglregistrasi =$pd->tglregistrasi;// date('Y-m-d H:i:s');
                $dataAPD->tglmasuk = date('Y-m-d H:i:s');
                $dataAPD->tglkeluar = date('Y-m-d H:i:s');
                $dataAPD->save();

                $dataAPDnorec = $dataAPD->norec;
                $dataAPDtglPel = $dataAPD->tglregistrasi;
            }else{
                $dataAPD =  PelayananPasien::where('norec',$request['norec_pp'])->where('kdprofile', $kdProfile)->first();
                $dataAPDnorec = $dataAPD->noregistrasifk;
                $dataAPDtglPel = $dataAPD->tglregistrasi;

                $HapusPP = PelayananPasien::where('strukorderfk', $request['norec_so'])->where('kdprofile', $kdProfile)->get();
                foreach ($HapusPP as $pp){
                    $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->where('kdprofile', $kdProfile)->delete();
                    $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->where('kdprofile', $kdProfile)->delete();
                }
                $Edit = PelayananPasien::where('strukorderfk', $request['norec_so'])->where('kdprofile', $kdProfile)->delete();
            }


            foreach ($request['bridging'] as $item){
                $PelPasien = new PelayananPasien();
                $PelPasien->norec = $PelPasien->generateNewId();
                $PelPasien->kdprofile = $kdProfile;
                $PelPasien->statusenabled = true;
                $PelPasien->noregistrasifk =  $dataAPDnorec;
                $PelPasien->tglregistrasi = $dataAPDtglPel;
                $PelPasien->hargadiscount = 0;
                $PelPasien->hargajual =  $item['hargasatuan'];
                $PelPasien->hargasatuan =  $item['hargasatuan'];
                $PelPasien->jumlah =  $item['qtyproduk'];
                $PelPasien->kelasfk =  $request['objectkelasfk'];
                $PelPasien->kdkelompoktransaksi =  1;
                $PelPasien->piutangpenjamin =  0;
                $PelPasien->piutangrumahsakit = 0;
                $PelPasien->produkfk =  $item['produkid'];
                $PelPasien->stock =  1;
                $PelPasien->strukorderfk =  $request['norec_so'];
                $PelPasien->tglpelayanan = date('Y-m-d H:i:s');
                $PelPasien->harganetto =  $item['hargasatuan'];

                $PelPasien->save();
                $PPnorec = $PelPasien->norec;


                $PelPasienPetugas = new PelayananPasienPetugas();
                $PelPasienPetugas->norec = $PelPasienPetugas->generateNewId();
                $PelPasienPetugas->kdprofile = $kdProfile;
                $PelPasienPetugas->statusenabled = true;
                $PelPasienPetugas->nomasukfk = $dataAPDnorec;
                $PelPasienPetugas->objectpegawaifk = $request['iddokterverif'];//$request['objectpegawaiorderfk'];
                $PelPasienPetugas->objectjenispetugaspefk =  $request['idpetugaspe'];//4;//$jenisPetugasPe->objectjenispetugaspefk;
                $PelPasienPetugas->pelayananpasien = $PPnorec;
                $PelPasienPetugas->save();
                $PPPnorec = $PelPasienPetugas->norec;


                foreach ($item['komponenharga'] as $itemKomponen) {

                    $PelPasienDetail = new PelayananPasienDetail();
                    $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                    $PelPasienDetail->kdprofile = $kdProfile;
                    $PelPasienDetail->statusenabled = true;
                    $PelPasienDetail->noregistrasifk = $dataAPDnorec;
                    $PelPasienDetail->aturanpakai = '-';
                    $PelPasienDetail->hargadiscount = 0;
                    $PelPasienDetail->hargajual = $itemKomponen['hargasatuan'];
                    $PelPasienDetail->hargasatuan = $itemKomponen['hargasatuan'];
                    $PelPasienDetail->jumlah = 1;
                    $PelPasienDetail->keteranganlain = '-';
                    $PelPasienDetail->keteranganpakai2 = '-';
                    $PelPasienDetail->komponenhargafk = $itemKomponen['objectkomponenhargafk'];
                    $PelPasienDetail->pelayananpasien = $PPnorec;
                    $PelPasienDetail->piutangpenjamin = 0;
                    $PelPasienDetail->piutangrumahsakit = 0;
                    $PelPasienDetail->produkfk =  $item['produkid'];
                    $PelPasienDetail->stock = 1;
                    $PelPasienDetail->strukorderfk =  $request['norec_so'];
                    $PelPasienDetail->tglpelayanan =$dataAPDtglPel;
                    $PelPasienDetail->harganetto = $itemKomponen['hargasatuan'];
                    $PelPasienDetail->save();
                    $PPDnorec = $PelPasienDetail->norec;
                    $transStatus = 'true';
                }
            }

            $dataPD = PasienDaftar::where('norec', $request['norec_pd'])
                ->where('kdprofile', $kdProfile)
                ->update(['objectruanganlastfk' => $request['objectruangantujuanfk']]
            );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan PelayananPasien Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPP' => $PelPasien,
//                'dataPPP' => $PelPasienPetugas,
                'dataPPD' => $PelPasienDetail,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan PelayananPasien Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'dataPP' => $PelPasien,
//                'dataPPP' => $PelPasienPetugas,
//                'dataPPD' => $PelPasienDetail,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond();
    }

    public function getDaftarRegistrasiPasienJenazah(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJoin('jenispelayanan_m as jp','jp.kodeinternal','=','pd.jenispelayanan')
//            ->leftjoin('antrianpasiendiperiksa_t as at2','at2.noregistrasifk','=','pd.norec')
//            ->leftjoin('detaildiagnosapasien_t as ddp','ddp.noregistrasifk','=','at2.norec')
//            ->leftjoin('diagnosapasien_t as dp','dp.norec','=','ddp.objectdiagnosapasienfk')
//            ->leftjoin('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
            ->select('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
                'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pa.norec as norec_pa', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'pa.nosep as nosep', 'br.norec as norec_br', 'pd.nostruklastfk','pd.objectkelasfk','kls.namakelas',
                'ps.tgllahir','ps.objectjeniskelaminfk','jk.jeniskelamin','alm.alamatlengkap',
                'pd.jenispelayanan as idjenispelayanan','jp.jenispelayanan','ps.tglmeninggal',
                DB::raw("'' AS diagnosa"))
            ->where('pd.kdprofile', $kdProfile)
            ->whereNull('br.norec')
            ->whereNotNull('ps.tglmeninggal')
            ->where('pd.objectstatuskeluarfk', 5);
        //            ->where('pd.statusenabled', null);

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglmeninggal', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglmeninggal', '<=', $filter['tglAkhir']);
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
        $data = $data->groupBy('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi',
            'ru.namaruangan', 'ps.namapasien',
            'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap', 'pd.tglpulang', 'pd.statuspasien',
            'pa.nosep', 'br.norec', 'pa.norec', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk', 'pd.objectruanganlastfk',
            'pd.nostruklastfk', 'ps.tgllahir','pd.objectkelasfk','kls.namakelas','ps.objectjeniskelaminfk','jk.jeniskelamin',
            'alm.alamatlengkap','pd.jenispelayanan','jp.jenispelayanan','ps.tglmeninggal');
        $data = $data->get();

        $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
                    ->join('pasiendaftar_t AS pd','pd.norec','=','apd.noregistrasifk')
                    ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
                    ->leftjoin('diagnosapasien_t as dp','dp.norec','=','ddp.objectdiagnosapasienfk')
                    ->leftjoin('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
                    ->where('ddp.objectjenisdiagnosafk',1)
                    ->where('pd.tglmeninggal', '>=', $filter['tglAwal'])
                    ->where('pd.tglmeninggal', '<=', $filter['tglAkhir'])
                    ->where('apd.kdprofile', (int)$kdProfile)
                    ->where('pd.statusenabled', true)
                    ->select(DB::raw("
                        pd.noregistrasi,dg.kddiagnosa || ', ' || dg.namadiagnosa AS diagnosa
                    "))
                    ->get();
        $i=0;
        $Datadiagnosa = '';
        foreach ($data as $items){
            foreach ($diagnosa as $gdItems){
                if ($data[$i]->noregistrasi == $gdItems->noregistrasi && $gdItems->diagnosa != null){
                    $data[$i]->diagnosa = $gdItems->diagnosa;
                }
            }
            $i = $i + 1;
        }
        return $this->respond($data);
    }

    public function getRincianPelayananJenazah(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin=$request->all();
        $result=[];
        $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
            ->leftjoin('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->leftjoin('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('mlu.kdprofile', $kdProfile)
            ->where('objectloginuserfk',$dataLogin['userData']['id'])
            ->get();

        $pelayanan = \DB::table('pelayananpasien_t as pp')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->JOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftJOIN('strukorder_t as so', 'so.norec', '=', 'pp.strukorderfk')
//                ->leftJOIN('ris_order as ris', 'ris.order_no', '=',
//                    DB::raw('so.noorder AND ris.order_code=pp.produkfk'))
            ->select('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                'dp.namadepartemen', 'ps.id as psid', 'apd.norec as norec_apd', 'sp.norec as norec_sp', 'pp.norec as norec_pp',
                'ru.objectdepartemenfk', 'so.noorder','apd.objectruanganfk','pp.iscito','pp.jasa')
//                    DB::raw('case when ris.order_key is not null then \'Sudah Dikirim\' else \'-\'end as statusbridging'))
            ->where('pp.kdprofile', $kdProfile)
            ->where('ru.objectdepartemenfk', $this->settingDataFixed('KdInstalasiJenazah', $kdProfile))
            ->orderBy('pp.tglpelayanan');

        if (isset($request['departemenfk']) && $request['departemenfk'] != "" && $request['departemenfk'] != "undefined") {
            $pelayanan = $pelayanan->where('ru.objectdepartemenfk', '=', $request['departemenfk']);
        }
        if (isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $pelayanan = $pelayanan->where('ps.nocm', '=', $request['nocm']);
        }
        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] != "" && $request['noregistrasifk'] != "undefined") {
            $pelayanan = $pelayanan->where('pp.noregistrasifk', '=', $request['noregistrasifk']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
            $pelayanan = $pelayanan->where('pd.noregistrasi', '=', $request['noregistrasi']);
        }
        $pelayanan = $pelayanan->get();
        if (count($pelayanan) > 0) {
            $pelayananpetugas = \DB::table('pasiendaftar_t as pd')
                ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->join('pelayananpasienpetugas_t as ptu', 'ptu.nomasukfk', '=', 'apd.norec')
                ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ptu.objectpegawaifk')
                ->select('ptu.pelayananpasien', 'pg.namalengkap', 'pg.id')
//                    ->where('ptu.objectjenispetugaspefk', 4)
                ->where('pd.kdprofile', $kdProfile)
                ->where('pd.noregistrasi', $pelayanan[0]->noregistrasi)
                ->get();


            $result = [];
            foreach ($pelayanan as $item) {
                if (isset($request['noregistrasifk'])) {
                    $diskon = $item->hargadiscount;
                } else {
                    $diskon = 0;
                }
                $NamaDokter = '-';
                $DokterId = '';
                foreach ($pelayananpetugas as $hahaha) {
                    if ($hahaha->pelayananpasien == $item->norec_pp) {
                        $NamaDokter = $hahaha->namalengkap;
                        $DokterId = $hahaha->id;
                    }
                }
                $total = (((float)$item->hargasatuan - (float)$diskon) * (float)$item->jumlah ) + (float)$item->jasa ;

                $result[] = array(
                    'nocm' => $item->nocm,
                    'namapasien' => $item->namapasien,
                    'jeniskelamin' => $item->jeniskelamin,
                    'tglpelayanan' => $item->tglpelayanan,
                    'produkfk' => $item->produkfk,
                    'namaproduk' => $item->namaproduk,
                    'jumlah' => (float)$item->jumlah,
                    'hargasatuan' => (float)$item->hargasatuan,
                    'hargadiscount' => (float)$diskon,
                    'total' => (float)$total,
                    'nostruk' => $item->nostruk,
                    'noregistrasi' => $item->noregistrasi,
                    'ruangan' => $item->namaruangan,
                    'objectruanganfk' => $item->objectruanganfk,
                    'departemen' => $item->namadepartemen,
                    'objectdepartemenfk' => $item->objectdepartemenfk,
                    'norec_apd' => $item->norec_apd,
                    'norec_sp' => $item->norec_sp,
                    'norec_pp' => $item->norec_pp,
                    'dokter' => $NamaDokter,
                    'dokterid' => $DokterId,
                    'noorder' => $item->noorder,
//                        'idbridging' => $item->idbridging,
//                        'statusbridging' => $item->statusbridging,
                    'iscito' => $item->iscito,
                    'jasa' => (float)$item->jasa,
                );
            }
        }
        $dataTea =array(
            'data' => $result,
            'detaillogin' => $dataLogin,
            'message' => 'Inhuman'
        );
        return $this->respond($dataTea);
    }

    public function getOrderPelayananJenazah(Request $request) {
        $idkelas = $request['objectkelasfk'];
        $norec_so = $request['norec_so'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataOrderPelayanan = DB::select(DB::raw("select DISTINCT op.norec as norec_op,pr.id as prid,pr.namaproduk,
                op.tglpelayanan,op.qtyproduk ,ru.namaruangan as ruangantujuan,ru.objectdepartemenfk,op.strukorderfk,so.objectruangantujuanfk,
                hnp.hargasatuan ,kls.namakelas,dpm.namadepartemen,
                pps.norec as norec_pp
                from orderpelayanan_t op
                left join strukorder_t as so on so.norec=op.strukorderfk
                INNER JOIN produk_m as pr on pr.id=op.objectprodukfk
                left JOIN harganettoprodukbykelas_m as hnp on pr.id=hnp.objectprodukfk
                        and '$idkelas' =hnp.objectkelasfk                
                left join kelas_m as kls on kls.id = '$idkelas'                
                left join ruangan_m as ru on ru.id =so.objectruangantujuanfk
                left join departemen_m as dpm on dpm.id=ru.objectdepartemenfk
                left JOIN pelayananpasien_t as pps on pps.strukorderfk=so.norec
                      and op.objectprodukfk =pps.produkfk
                where op.kdprofile = $kdProfile and op.strukorderfk=:norec_so
                and kls.id=:objectkelasfk
                ORDER by op.tglpelayanan"),
            array(
                'norec_so' => $norec_so,
                'objectkelasfk' =>$idkelas ,
            )
        );

        $result=[];
        foreach ($dataOrderPelayanan as $item){
            $dataz =  DB::select(DB::raw("select DISTINCT hnp.objectkomponenhargafk,kh.komponenharga,hnp.hargasatuan,
                hnp.objectprodukfk
                from harganettoprodukbykelasd_m as hnp   
                inner join produk_m as prd on prd.id=hnp.objectprodukfk
                inner join komponenharga_m as kh on kh.id=hnp.objectkomponenhargafk
                inner join kelas_m as kls on kls.id = hnp.objectkelasfk
                where hnp.kdprofile = $kdProfile and hnp.objectkelasfk='$idkelas'
                and prd.id='$item->prid'"));

            $result[] = array(
                'norec_op' => $item->norec_op,
                'norec_pp' => $item->norec_pp,
                'prid' => $item->prid,
                'namaproduk' => $item->namaproduk,
                'qtyproduk' => $item->qtyproduk,
                'tglpelayanan' => $item->tglpelayanan,
                'idruangan' => $item->objectruangantujuanfk,
                'ruangantujuan' => $item->ruangantujuan,
                'hargasatuan' => $item->hargasatuan,
                'namakelas' => $item->namakelas,
                'objectdepartemenfk' => $item->objectdepartemenfk,
                'namadepartemen' => $item->namadepartemen,
                'details' => $dataz,
            );
        }

        $result=array(
            'message' =>  'inhuman',
            'data' =>  $result,

        );
        return $this->respond($result);
    }

    public function saveOrderJenazah(Request $request){
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
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'R' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 25) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'OK' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 5) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'PJ' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 31) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'ABM' . $this->getDateTime()->format('ym'), $idProfile);
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
                if(isset( $request['keterangan'])){
                    $dataSO->keteranganlainnya = $request['keterangan'];
                }
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


                foreach ($request['details'] as $item) {
                    if ($request['status'] == 'bridinglangsung') {
                        $updatePP = PelayananPasien::where('norec', $item['norec_pp'])
                            ->where('kdprofile',$idProfile)
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

    public function hapusOrderPelayananJenazah(Request $request){
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

    public function getRiwayatOrderPelayananJenazah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = [];
        $ruanganLab = explode(',', $this->settingDataFixed('KdRuanganPemulasaraanJenazah', $idProfile));
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
            ->select('so.norec', 'pd.norec as norecpd', 'pd.noregistrasi', 'so.tglorder', 'so.noorder',
                'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan', 'p.namalengkap',
                'so.noorder','pd.noregistrasi','so.keteranganlainnya'
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
        $data = $data->orderBy('so.tglorder');
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
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarPasienMeninggal(Request $request){
        $kdProfile =(int) $this->getDataKdProfile($request);
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin ('statuskeluar_m as sk','sk.id','=','pd.objectstatuskeluarfk')
            ->leftjoin ('statuspulang_m as sp','sp.id','=','pd.objectstatuspulangfk')
            ->leftjoin ('penyebabkematian_m as pk','pk.id','=','pd.objectpenyebabkematianfk')
            ->leftjoin ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->select(DB::raw("pd.tglregistrasi,pd.noregistrasi,ps.nocm,ps.namapasien,jk.jeniskelamin,ps.tgllahir,
                     sk.statuskeluar,sp.statuspulang,pd.namalengkapambilpasien,
                     case when pd.objectpenyebabkematianfk = 4 then pd.keteranganpenyebabkematian else pk.penyebabkematian end as penyebabkematian,
                     pd.tglmeninggal, ru.namaruangan"))
            ->where('pd.kdprofile', $kdProfile)
            ->where('pd.objectstatuskeluarfk', 5);

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglmeninggal', '>=', $filter['tglAwal'].' 00:00');
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglmeninggal', '<=', $filter['tglAkhir']. ' 23:59');
        }
        if (isset($filter['noReg']) && $filter['noReg'] != "" && $filter['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%'. $filter['noReg'].'%');
        }
        if (isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike','%'. $filter['noCm'].'%');
        }
        if (isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike','%'. $filter['namaPasien'].'%');
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'er@epic',
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
            ->select('jk.id', 'jk.jeniskelamin')
            ->where('jk.statusenabled', true)
            ->orderBy('jk.jeniskelamin')
            ->get();

        $dataHubunganKeluarga = \DB::table('hubungankeluarga_m as jk')
            ->select('jk.id', 'jk.hubungankeluarga')
            ->where('jk.statusenabled', true)
            ->orderBy('jk.hubungankeluarga')
            ->get();
        $tahun =  $this->getDateTime()->format('Y');
        $SuratKematian = "_______/RSUD.C/SKS/_____________/SKK/________/".$tahun;


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
            'hubungankeluarga' => $dataHubunganKeluarga,
            'suratkematian' => $SuratKematian,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDataDiagnosa(Request $request){
        $req = $request->all();
        $icdIX = \DB::table('diagnosa_m as dg')
            ->select('dg.id', 'dg.kddiagnosa', 'dg.namadiagnosa','dg.kddiagnosa as kdDiagnosa','dg.namadiagnosa as namaDiagnosa')
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosa');

        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined"
        ) {
            $icdIX = $icdIX
                ->where('dg.namadiagnosa', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%')
                ->orWhere('dg.kddiagnosa', 'ilike', $req['filter']['filters'][0]['value'] . '%');
        }

        $icdIX = $icdIX->take(10);
        $icdIX = $icdIX->get();

        return $this->respond($icdIX);
    }

    public function saveAntrianPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$request['objectruanganasalfk'])
                ->where('kdprofile', $idProfile)
                ->where('tglregistrasi', '>=', $request['tglregistrasidate'].' 00:00')
                ->where('tglregistrasi', '<=', $request['tglregistrasidate'].' 23:59')
                ->count('norec');
            $pd = PasienDaftar::where('norec',$request['norec_pd'])->first();
            $noAntrian = $countNoAntrian + 1;
            $dataAPD = new AntrianPasienDiperiksa;
            $dataAPD->norec = $dataAPD->generateNewId();
            $dataAPD->kdprofile = $idProfile;
            $dataAPD->statusenabled = true;
            $dataAPD->objectasalrujukanfk = $request['asalrujukanfk'];
            $dataAPD->objectkelasfk = 6;
            $dataAPD->noantrian = $noAntrian;
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
            $dataAPD->save();

            $dataAPDnorec = $dataAPD->norec;
            $transStatus = 'true';
            $transMessage = "simpan AntrianPasienDiperiksa";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan AntrianPasienDiperiksa";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataAPD,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $dataAPD,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataComboLabRab(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataDokter = \DB::table('pegawai_m as dp')
            ->select('dp.id','dp.namalengkap')
            ->whereIn('dp.objectjenispegawaifk',array(1))
            ->where('dp.statusenabled',true)
            ->where('dp.kdprofile',$idProfile)
            ->orderBy('dp.namalengkap')
            ->get();

        $golonganDarah = \DB::table('golongandarah_m')
            ->select('id','golongandarah')
            ->where('statusenabled',true)
            ->where('kdprofile',$idProfile)
            ->orderBy('golongandarah')
            ->get();

        $result = array(
            'dokter' => $dataDokter,
            'golongandarah' =>   $golonganDarah,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function deletePelayananPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            foreach ($request['dataDel'] as $item) {
                $HapusPP = PelayananPasien::where('norec', $item['norec_pp'])->get();
                foreach ($HapusPP as $pp) {
                    $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->where('kdprofile', $idProfile)->delete();
                    $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->where('kdprofile', $idProfile)->delete();
                }
                $Edit = PelayananPasien::where('norec', $item['norec_pp'])->where('kdprofile', $idProfile)->delete();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = " PP PPD PPP";
        }
        if ($transStatus == 'true') {
            $transMessage = "Delete Pelayanan Pasien";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "nokirim" => $dataSO,//$noResep,,//$noResep,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Delete Pelayanan Pasien Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getLaporanPasienMeninggal(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("select pt.norec,pt.tglmeninggal, pt.objectruanganlastfk , rm.namaruangan , pm.namapasien,jm.jeniskelamin ,pm.nocm, date_part('year',now()) - date_part('year',pm.tgllahir) as umur, km.kondisipasien, pm.noidentitas, pm.nobpjs, pm.alamatrmh,
                kps.kelompokpasien
                from pasiendaftar_t pt
                inner join ruangan_m rm on rm.id = pt.objectruanganlastfk
                inner join pasien_m pm on pm.id = pt.nocmfk
                inner join jeniskelamin_m jm on jm.id = pm.objectjeniskelaminfk
                inner join kondisipasien_m km on km.id = pt.objectkondisipasienfk
                inner join kelompokpasien_m kps on kps.id = pt.objectkelompokpasienlastfk
                --inner join detaildiagnosapasien_t AS ddp on ddp.noregistrasifk=at2.norec
                --inner join diagnosapasien_t AS dp on dp.norec=ddp.objectdiagnosapasienfk
                --inner join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                where pt.tglmeninggal between '$tglAwal' and '$tglAkhir' and pt.tglmeninggal is not null and pt.statusenabled = true and pt.kdprofile = $kdProfile
                order by rm.namaruangan asc"));

         $norecaPd = '';
        foreach ($data as $ob){
            $norecaPd = $norecaPd.",'".$ob->norec . "'";
            $ob->kddiagnosa = [];
        }
        $norecaPd = substr($norecaPd, 1, strlen($norecaPd)-1);
        $diagnosa = [];
        if($norecaPd!= ''){
            $diagnosa = DB::select(DB::raw("
                select dg.kddiagnosa,ddp.noregistrasifk as norec_apd,apd.noregistrasifk
                from antrianpasiendiperiksa_t as apd
                join detaildiagnosapasien_t as ddp on  ddp.noregistrasifk=apd.norec
                left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                where apd.noregistrasifk in ($norecaPd) "));
           $i = 0;
           foreach ($data as $h){
               $data[$i]->kddiagnosa = '';
               foreach ($diagnosa as $d){
                   if($data[$i]->norec == $d->noregistrasifk){
                        $data[$i]->kddiagnosa =  $data[$i]->kddiagnosa .', '.$d->kddiagnosa;
                   }
               }
               $data[$i]->kddiagnosa = substr($data[$i]->kddiagnosa , 1, strlen($data[$i]->kddiagnosa )-1);
               $i++;
           }
        }

        $result = array(
           'data'=> $data,
           'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    
    public function getLaporanPemulasaranJenazah(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw(
                    "select  
                        ddp.objectjenisdiagnosafk, 
                        dg.kddiagnosa,
                        pt.norec,
                        pt.tglmeninggal, 
                        pt.objectruanganlastfk,
                        kp.kelompokpasien,
                        rm.namaruangan, 
                        pm.namapasien,
                        pm.alamatrmh,
                        pm.nobpjs,
                        jm.jeniskelamin,
                        pm.nocm,
                        pm.tgllahir,
                        date_part('year',now()) - date_part('year',pm.tgllahir) as umur, 
                        km.kondisipasien 
                    from antrianpasiendiperiksa_t at2 
                    inner join pasiendaftar_t pt on pt.norec = at2.noregistrasifk 
                    inner join ruangan_m rm on rm.id = pt.objectruanganlastfk
                    inner join pasien_m pm on pm.id = pt.nocmfk 
                    inner join jeniskelamin_m jm on jm.id = pm.objectjeniskelaminfk 
                    inner join kondisipasien_m km on km.id = pt.objectkondisipasienfk 
                    inner join detaildiagnosapasien_t AS ddp on ddp.noregistrasifk=at2.norec
                    inner join diagnosapasien_t AS dp on dp.norec=ddp.objectdiagnosapasienfk
                    inner join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                    inner join kelompokpasien_m as kp on kp.id = pt.objectkelompokpasienlastfk
                    where at2.tglregistrasi between '$tglAwal' and '$tglAkhir' 
                        and pt.tglmeninggal is not null 
                        and pt.statusenabled = true 
                        and pt.kdprofile = $kdProfile 
                    order by rm.namaruangan asc"
                ));

        $i = 1;
        foreach ($data as $d) {
            $d->tgllahir = date("d-m-Y", strtotime($d->tgllahir));
            $d->no = $i++;
        }

        $result = array(
           'data'=> $data,
           'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function BatalMeninggalPasien (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();
        DB::beginTransaction();
        try{

            $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                ->where('kdprofile', $kdProfile)
                ->select('nocmfk')
                ->first();

            $updatePasien = Pasien::where('id',$pasienDaftar->nocmfk)
                ->where('kdprofile', $kdProfile)
                ->update([
                    'tglmeninggal' => null,
                ]);

            $upPD = PasienDaftar::where('noregistrasi',$request['noregistrasi'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'keteranganpenyebabkematian' => null,
                    'objectkelompokpasiendetailfk' => null,
                    'objectstatuskeluarfk' => null,
                    'tglmeninggal' => null,
                ]);

            /*Logging User*/
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Meninggal Pasien';
            $logUser->noreff =$request['noregistrasi'];
            $logUser->referensi='Noregistrasi';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Batal Meninggal Pasien Noregistrasi :' . $request['noregistrasi'];
            $logUser->save();
            /*End Logging User*/

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
//                "nokirim" => $dataSO,//$noResep,,//$noResep,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function savePermohonanPelayananJenazah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();
        $pasien= DB::table('pasiendaftar_t as pd')
            ->join('ruangan_m as ru','pd.objectruanganlastfk','=','ru.id')
            ->join('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->select('pd.noregistrasi','ru.namaruangan','pd.tglpulang')
            ->where('pd.norec','=',$request['nores_pd'])
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.statusenabled',true)
            ->first();
//        return $this->respond($pasien->noregistrasi);
        $keterangan = '';
        DB::beginTransaction();
        try {

            if ($request['norec'] == "") {
                $keterangan = "Input Permohonan Pelayanan Jenazah";
                $dataSO = new SuratPermohonanJenazah();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->tglsurat = $tglAyeuna;
                $dataSO->pasiendaftarfk = $request['nores_pd'];
            } else {
                $keterangan = "Ubah Permohonan Pelayanan Jenazah";
                $dataSO = SuratPermohonanJenazah::where('norec',$request['norec'])->first();
            }
                $dataSO->nosurat = $request['nosurat'];
                $dataSO->penanggungjawab = $request['penanggungjawab'];
                $dataSO->objectjeniskelaminfk = $request['objectjeniskelaminfk'];
                $dataSO->objecthubungankeluargafk = $request['objecthubungankeluargafk'];
                $dataSO->alamat = $request['alamat'];
                $dataSO->covid = $request['covid'];
                $dataSO->noncovid = $request['noncovid'];
                $dataSO->petugassatu = $request['petugassatu'];
                $dataSO->petugasdua = $request['petugasdua'];
                $dataSO->petugastiga = $request['petugastiga'];
                $dataSO->petugasempat = $request['petugasempat'];
                $dataSO->petugaslima = $request['petugaslima'];
                $dataSO->pemulasaraanjenazah = $request['pemulasaraanjenazah'];
                $dataSO->pengkafanan = $request['pengkafanan'];
                $dataSO->plastisisasi = $request['plastisisasi'];
                $dataSO->kantongjenazah = $request['kantongjenazah'];
                $dataSO->petijenazah = $request['petijenazah'];
                $dataSO->disinfektanjenazah = $request['disinfektanjenazah'];
                $dataSO->pelayanankerohanian = $request['pelayanankerohanian'];
                $dataSO->transportasiambulan = $request['transportasiambulan'];
                $dataSO->disinfektanambulan = $request['disinfektanambulan'];
                $dataSO->save();
                $dataSOnorec = $dataSO->norec;

                /*Logging User*/
                $newId = LoggingUser::max('id');
                $newId = $newId +1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile= $kdProfile;
                $logUser->statusenabled=true;
                $logUser->jenislog = $keterangan;
                $logUser->noreff =$dataSOnorec;
                $logUser->referensi='Norec Surat Permohonan Jenazah';
                $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                $logUser->tanggal = $tglAyeuna;
                $logUser->keterangan = $keterangan . ' Pasien Dengan No Registrasi '. $pasien->noregistrasi;
                $logUser->save();
                /*End Logging User*/


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

    public function getDataPermohonanPelayananJenazah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('suratpermohonanjenazah_t AS spj','spj.pasiendaftarfk','=', 'pd.norec')
            ->leftjoin ('jeniskelamin_m AS jk','jk.id','=','spj.objectjeniskelaminfk')
            ->leftjoin ('hubungankeluarga_m AS hk','hk.id','=','spj.objecthubungankeluargafk')
            ->leftjoin ('pegawai_m AS pg','pg.id','=','spj.petugassatu')
            ->leftjoin ('pegawai_m AS pg1','pg1.id','=','spj.petugasdua')
            ->leftjoin ('pegawai_m AS pg2','pg2.id','=','spj.petugastiga')
            ->leftjoin ('pegawai_m AS pg3','pg3.id','=','spj.petugasempat')
            ->leftjoin ('pegawai_m AS pg4','pg4.id','=','spj.petugaslima')
            ->select(DB::raw("spj.*,jk.jeniskelamin,hk.hubungankeluarga,pg.namalengkap AS namapetugassatu,
                                    pg1.namalengkap AS namapetugasdua,pg2.namalengkap AS namapetugastiga,
                                    pg3.namalengkap AS namapetugasempat,pg4.namalengkap AS namapetugaslima"))
            ->where('pd.kdprofile', $kdProfile)
            ->where('pd.statusenabled', true)
            ->where('spj.statusenabled', true);

        if (isset($request['norec_pd']) && $request['norec_pd'] != "" && $request['norec_pd'] != "undefined") {
            $data = $data->where('pd.norec',$request['norec_pd']);
        }
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function HapusPermohonanPelayananJenazah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();

        $keterangan = '';
        DB::beginTransaction();
        try {

            $dataSO = SuratPermohonanJenazah::where('norec',$request['norec'])
                      ->update([
                            "statusenabled" => false,
                      ]);

            /*Logging User*/
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = "Hapus Surat Permohonan Jenazah";
            $logUser->noreff  = $request['norec'];
            $logUser->referensi='Norec Surat Permohonan Jenazah';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = "Hapus Surat Permohonan Jenazah Noregistrasi " .$request['noregistrasi'];
            $logUser->save();
            /*End Logging User*/

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
}