<?php
/**
 * Created by PhpStorm.
 * User: Egie RAmdan
 * Date: 9/5/2019
 * Time: 1:42 PM
 */


namespace App\Http\Controllers\Laboratorium;

use App\Http\Controllers\ApiController;
use App\Master\JenisPetugasPelaksana;
use App\Master\Produk;
use App\Master\DetailJenisProduk;
use App\Master\SatuanStandar;
use App\Master\NilaiNormal;
use App\Master\MapHasilLab;
use App\Master\MapHasilLabDetail;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\HasilPemeriksaan;
use App\Transaksi\LisOrder;
use App\Transaksi\LisOrderTmp;
use App\Transaksi\LoggingUser;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDelete;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\HasilPemeriksaanLab;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\HasilLaboratorium;

use App\Transaksi\RisOrder;
use App\Transaksi\StrukHasilPemeriksaan;
use Illuminate\Http\Request;
use DB;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\OrderProduk;
use App\Master\Pegawai;
use App\Master\Pasien;
use App\Traits\Valet;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;
class LaboratoriumController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function getHasilLab(Request $request) {
        $noOrders = $request['noorder'];
        $lisOrder = DB::table('lisorder as lis')
            ->select('lis.ono as noLab',  'lis.order_testid as ordertestid','lis.pid as rm', 'lis.request_dt as dt')
            ->where('lis.ono',$noOrders)
            ->get();
        $dataResultDetail = [];
        if(count($lisOrder) > 0){
            $dataResultDetail = DB::table('resdt as dt')
                ->select('dt.ono as idLab', 'dt.test_cd as kdtesext',
                    'dt.his_detil as kdDetail', 'dt.test_nm as namaPemeriksaan', 'dt.result_value as hasilPemeriksaan', 'dt.validate_by as validator',
                    'dt.result_ft as hasilft', 'dt.unit as satuan', 'dt.ref_range as nilaiNormal','dt.test_comment as keterangan',
                    'dt.disp_seq as urutan' , 'dt.test_group as paket', 'dt.flag')
                ->where('dt.ono',$lisOrder[0]->noLab)
                ->get();
        }
        $result = array(
            'data' => $dataResultDetail,
            'message' => 'ramdanegie'
        );
        return $this->respond($result);

    }
    public function getDaftarRIlabRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix',$idProfile));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap){
            $kdDepartemenRawatInap []=  (int)$itemRanap;
        }
        $data = \DB::table('pasiendaftar_t as pd')
//            ->leftjoin('registrasipelayananpasien_t as rpp','pd.norec','=','rpp.noregistrasifk')
            ->leftjoin('antrianpasiendiperiksa_t as apd','pd.norec','=','apd.noregistrasifk')
            ->JOIN('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->JOIN('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->JOIN('kelas_m as kl','kl.id','=','pd.objectkelasfk')
            ->leftJOIN('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->select('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
                'kp.id as kpid','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id as klid',
                'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','pd.norec as rpp','pd.nostruklastfk','apd.norec as norec_apd','pd.norec',
                'pd.objectkelasfk','pd.objectruanganlastfk','pd.objectpegawaifk as pgid','rek.namarekanan as rekanan')
            ->where('pd.kdprofile',$idProfile)
            ->where('pd.statusenabled',true)
            ->orderBy('pd.noregistrasi');

//        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
//            $data = $data->where('pd.tglregistrasi','>=', $request['tglAwal']);
//        }
//        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
//            $tgl= $request['tglAkhir']." 23:59:59";
//            $data = $data->where('pd.tglregistrasi','<=', $tgl);
//        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $data = $data->where('ru.id', $request['ruid']);
        }
//        if(isset($request['dpid']) && $request['dpid']!="" && $request['dpid']!="undefined"){
//            $data = $data->where('dp.id', $request['dpid']);
//        }
//        if(isset($request['kpid']) && $request['kpid']!="" && $request['kpid']!="undefined"){
//            $data = $data->where('kp.id', $request['kpid']);
//        }
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noregistrasi'].'%');
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $request['nocm'].'%');
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'. $request['namapasien'].'%');
        }

        $data = $data->groupBy('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
            'kp.id','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id',
            'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','pd.norec','pd.nostruklastfk','apd.norec',
            'pd.norec',
            'pd.objectkelasfk','pd.objectruanganlastfk','pd.objectpegawaifk','rek.namarekanan');
        $data = $data->whereIn('dp.id',$kdDepartemenRawatInap);
        $data = $data->whereNull('pd.tglpulang');
        $data = $data->whereNull('apd.tglkeluar');
//        $data = $data->where('pd.tglregistrasi','>','2019-05-25 00:00');
        $data = $data->take(50);
        $data = $data->get();
        return $this->respond($data);


    }
    public function getDetailRegLabRad(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd','pd.norec','=','apd.noregistrasifk')
            ->JOIN('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->JOIN('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as pg','pg.id','=','apd.objectpegawaifk')
//            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
//            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
//            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
//            ->leftJoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->leftjoin('kelas_m as kl','kl.id','=','apd.objectkelasfk')
//            ->select('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
//                'kp.id as kpid','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id as klid',
//                'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','rpp.noregistrasifk as rpp','pd.nostruklastfk')

            ->select('ru.namaruangan','pd.noregistrasi','pd.norec as norec_pd',
                'pd.tglregistrasi','pg.namalengkap as namadokter','kl.namakelas','apd.tglmasuk','apd.tglkeluar','apd.norec','pg.id as pgid',
                'apd.objectruanganfk')
            ->where('pd.kdprofile',$idProfile)
            ->orderBy('apd.tglmasuk');

        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noregistrasi'].'%');
        }
//        $data = $data->where('dp.id','=',16);
//        $data = $data->whereNull('pd.tglpulang');
//        $data = $data->where('pd.tglregistrasi','>','2019-05-25 00:00');
//        $data = $data->take(50);
        $data = $data->get();
        return $this->respond($data);
    }
    public function getHasilLaborat(Request $request) {
        $noOrders = $request['noorder'];
        $data = DB::table('lab_hasil as lis')
            ->select('*')
            ->where('lis.no_lab',$noOrders)
            ->get();
        $lab_hasil_teks = DB::table('lab_hasil_teks as lis')
            ->select('*')
            ->where('lis.no_lab',$noOrders)
            ->get();

        $result = array(
            'data' => $data,
            'lab_hasil_teks' => $lab_hasil_teks,
            "as" => 'er@epic',
        );
        return $this->respond($result);

    }
//     public function getHasilLabVans(Request $request) {
//        $noOrders = $request['noorder'];
//        $data = \DB::connection('firebird')
//        ->table('KUNJUNGANPASIEN as kp')
//        ->join('pasien as p', 'p.KD_PASIEN', '=', 'kp.KPKD_PASIEN')
//        ->join('DOKTER as dok', 'dok.FMDDOKTER_ID', '=', 'kp.KPKD_DOKTER')
//        ->join('CUSTOMER as cus', 'cus.CUSID', '=', 'kp.KD_CUSTOMER')
//        ->join('POLIKLINIK as pol', 'pol.FMPKLINIK_ID', '=', 'kp.KPKD_POLY')
//        ->limit('10')
//        ->select('kp.KPKD_PASIEN as norec','p.NAMAPASIEN as namapasien', 'kp.KPTGL_PERIKSA as tglregistrasi',
//                'kp.KPNO_TRANSAKSI as notransaksi','pol.FMPKLINIKN as poliklinik'
//                ,'dok.FMDDOKTERN as namadokter','cus.NAME as carabayar','kp.KPJAM_MASUK as jam_masuk')
//        ->where('kp.KPKD_POLY','!=','PK011');
//
//        $result = array(
//            'data' => $data,
//            'lab_hasil_teks' => $lab_hasil_teks,
//            "as" => 'er@epic',
//        );
//        return $this->respond($result);
//    }

    public function getLaporanTindakanLaboratorium(Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','ps.objectjeniskelaminfk')
            ->join ('ruangan_m as rg','rg.id','=','pd.objectruanganlastfk')
            ->leftJoin('kelompokpasien_m as klp','klp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin ('produk_m as pro','pro.id','=','pp.produkfk')
            ->leftJoin ('strukorder_t AS so','so.norec','=','apd.objectstrukorderfk')
            ->leftJoin ('ruangan_m AS ru1','ru1.id', '=', 'so.objectruanganfk')
            ->leftJoin ('ruangan_m AS ru2', 'ru2.id', '=', 'apd.objectruanganasalfk')
            ->leftJoin ('batalregistrasi_t AS br', 'br.pasiendaftarfk', '=' ,'pd.norec')
            ->leftjoin ('pegawai_m AS pg','pg.id','=','apd.objectpegawaifk')
            ->select(DB::raw("
                 pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,
                 CASE WHEN ru1.namaruangan IS NOT NULL THEN ru1.namaruangan ELSE ru2.namaruangan END AS ruangan,
                 klp.kelompokpasien,pro.namaproduk,pp.jumlah,pp.hargajual,pg.namalengkap,ps.noidentitas,ps.tgllahir,jk.jeniskelamin,ps.alamatrmh
 			"))
            ->where('pd.statusenabled', true)
            // ->where('ru2.objectdepartemenfk', 3)
            // ->where('apd.objectruanganfk', 39)
            ->whereIn('apd.objectruanganfk', array(39, 575))
            ->where('pro.namaproduk', '!=', null)
            ->whereNull('br.norec');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
            $data = $data->where('rg.id', '=', $request['ruanganId']);
        }
        if (isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'.$request['nocm'].'%');
//				->orWhere('ps.namapasien', 'ilike', '%'.$filter['noRmNama'].'%')	;
        }
        if (isset($request['nama']) && $request['nama'] != "" && $request['nama'] != "undefined") {
            $data = $data->Where('ps.namapasien', 'ilike', '%'.$request['nama'].'%')	;
        }
        if (isset($request['tindakan']) && $request['tindakan'] != "" && $request['tindakan'] != "undefined") {
            $data = $data->Where('pro.namaproduk', 'ilike', '%'.$request['tindakan'].'%')	;
        }
        if (isset($request['dokter']) && $request['dokter'] != "" && $request['dokter'] != "undefined") {
            $data = $data->Where('pg.id', '=', $request['dokter'])	;
        }
        if (isset($request['kotaKab']) && $request['kotaKab'] != "" && $request['kotaKab'] != "undefined") {
            $data = $data->Where('kkb.id', '=', $request['kotaKab'])	;
        }
        // if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
        //     $data = $data->Where('klp.id', '=', $request['kelompokPasien'])	;
        // }
        if (isset($request['KpArr']) && $request['KpArr']!="" && $request['KpArr']!="undefined"){
            $arrayKelompokPasien = explode(',',$request['KpArr']) ;
            $ids = [];
            foreach ( $arrayKelompokPasien as $item){
                $ids[] = (int) $item;
            }
            $data = $data->whereIn('klp.id', $ids);
        }
        $data = $data->orderBy('pd.tglregistrasi', 'asc');
        $data =  $data ->get();
        foreach ($data as $d) {
            if (empty($d->namalengkap)) {
                $d->namalengkap = '-';
            }
        }
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
      public function saveHasilLabPA(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            if ($request['norec']=="") {
                $dataSO = new HasilPemeriksaanLab();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
            }else {
                $dataSO =  HasilPemeriksaanLab::where('norec',$request['norec'])->first();
            }
            if ($request['isDokterLuar'] == true) {
                $dataSO->dokterluar = $request['dokterpengirim2'];
                $dataSO->dokterpengirimfk = NULL;
            }
            else {
                $dataSO->dokterluar = NULL;
                $dataSO->dokterpengirimfk = $request['dokterpengirim1'];
            }
            $dataSO->nomorpa = $request['nomor'];
            $dataSO->tanggal =$request['tglinput'];
            $dataSO->pegawaifk = $request['dokterid'];
            $dataSO->jenis = $request['jenis'];
            $dataSO->pelayananpasienfk = $request['pelayananpasienfk'];
            $dataSO->noregistrasifk = $request['norec_pd'];
            $dataSO->diagnosaklinik = $request['diagnosaklinik'];
            $dataSO->keteranganklinik = $request['keteranganklinik'];
            $dataSO->diagnosapb = $request['diagnosapb'];
            $dataSO->keteranganpb = $request['keteranganpb'];
            $dataSO->topografi = $request['topografi'];
            $dataSO->morfologi = $request['morfologi'];
            $dataSO->makroskopik = $request['makroskopik'];
            $dataSO->mikroskopik = $request['mikroskopik'];
            $dataSO->kesimpulan = $request['kesimpulan'];
            $dataSO->anjuran = $request['anjuran'];
            $dataSO->jaringanasal = $request['jaringanasal'];
            $dataSO->save();

//
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {

            $transMessage = "Simpan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan  gagal!!";
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

    public function getHasilLabPA(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::table('hasilpemeriksaanlab_t as ar')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ar.pegawaifk')
            ->leftjoin('pegawai_m as dokterpengirim', 'dokterpengirim.id', '=', 'ar.dokterpengirimfk')
            ->select('ar.*','pg.namalengkap','dokterpengirim.namalengkap as namadokterpengirim')
            ->where('ar.kdprofile', $idProfile)
            ->where('ar.statusenabled',true)
            ->where('ar.pelayananpasienfk',$request['norec_pp'])
            ->get();
            // if(empty($data))
            // {
            //     $data = DB::table('maptempletlab_m as map')
            //     -> select('map.templethasil as keterangan')
            //     ->where('map.idprodukfk',$request['idproduk'])
            //     ->get(); 
            // }
        return $this->respond($data);
    }
    public function getComboMapLab(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $jenis  = DB::table('jenisproduk_m')
            ->select('id','jenisproduk')
            ->where('kdprofile', $kdProfile  )
            ->where('statusenabled',true)
            ->get();
        $jk  = DB::table('jeniskelamin_m')
            ->select('id','jeniskelamin')
            ->where('kdprofile', $kdProfile  )
            ->where('statusenabled',true)
            ->get();
        $ku  = DB::table('kelompokumur_m')
            ->select('*')
            ->where('kdprofile', $kdProfile  )
            ->where('statusenabled',true)
            ->get();
        // $jenisproduk  = DB::table('jenisproduk_m')
        //          ->select('*')
        //          ->where('kdprofile', $kdProfile  )
        //          ->where('statusenabled',true)
        //          ->get();
        $djenisproduk  = DB::table('detailjenisproduk_m')
            ->select('*')
            ->where('kdprofile', $kdProfile  )
            ->where('statusenabled',true)
            ->get();
        $ss  = DB::table('satuanstandar_m')
            ->select('*')
            ->where('kdprofile', $kdProfile  )
            ->where('objectdepartemenfk',$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $kdProfile))
            ->where('statusenabled',true)
            ->get();
        $kdjenisobat = explode (',',$this->settingDataFixed('kdJenisProdukObat',$kdProfile));
        $arrkdjenisobat = [];
        foreach ($kdjenisobat as $it){
            $arrkdjenisobat []=  (int)$it;
        }

        $dataProduk = \DB::table('produk_m as pr')
            ->leftJOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->select('pr.id','pr.namaproduk')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->where('pr.kodeexternal','<>','BTN 2 TN')
            ->whereIn('djp.objectjenisprodukfk',[1549,1548])
            ->groupBy('pr.id','pr.namaproduk')
            ->orderBy('pr.namaproduk')
            ->get();

        $result =  array(
            'jenisproduk' => $jenis,
            'jeniskelamin' => $jk,
            'kelompokumur'=>$ku,
            'produk'=>$dataProduk,
            'satuanstandar' =>$ss,
            // 'jenisproduk'=>$jenisproduk,
            'detailjenisproduk'=>$djenisproduk
        );
        return $this->respond($result);

    }
    public function getJenisPemeriksaan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $jenis  = DB::table('detailjenisproduk_m as djp')
            ->leftjoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftjoin('departemen_m as dp','dp.id','=','djp.objectdepartemenfk')
            ->select('djp.*','jp.jenisproduk','dp.namadepartemen')
            ->where('djp.kdprofile', $kdProfile  )
            ->where('djp.statusenabled',true)
            ->where('djp.objectdepartemenfk',$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $kdProfile));
        if(isset($request['nama']) && $request['nama']!='undefined' && $request['nama']!=''){
            $jenis = $jenis->where('djp.detailjenisproduk','ilike','%'.$request['nama'].'%');
        }
        $jenis=  $jenis->limit(50);
        $jenis=  $jenis->get();
        $result =  array(
            'data' => $jenis,
        );
        return $this->respond($result);

    }

    public function saveDetailJenis(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if($request['method']=='save'){
                if ($request['id']=="") {
                    $dataSO = new DetailJenisProduk();
                    $id =DetailJenisProduk::max('id') + 1;
                    $dataSO->id =$id ;
                    $dataSO->qdetailjenisproduk =$id ;
                    $dataSO->norec = $dataSO->generateNewId();
                    $dataSO->kdprofile = $idProfile;

                }else {
                    $dataSO =  DetailJenisProduk::where('id',$request['id'])->first();
                }
                $dataSO->statusenabled = true;
                $dataSO->kodeexternal =$request['kodeexternal'];
                $dataSO->namaexternal = $request['namaexternal'];
                $dataSO->detailjenisproduk = $request['detailjenisproduk'];
                $dataSO->objectjenisprodukfk = $request['objectjenisprodukfk'];
                $dataSO->objectdepartemenfk =$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $kdProfile);
                $dataSO->save();
            }else{
                DetailJenisProduk::where('id',$request['id'])->delete();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {

            $transMessage = "Simpan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Simpan  gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getSatuanHasil(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $jenis  = DB::table('satuanstandar_m as djp')
            ->leftjoin('departemen_m as dp','dp.id','=','djp.objectdepartemenfk')
            ->select('djp.*','dp.namadepartemen')
            ->where('djp.kdprofile', $kdProfile  )
            ->where('djp.statusenabled',true)
            ->where('djp.objectdepartemenfk',$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $kdProfile));
        if(isset($request['nama']) && $request['nama']!='undefined' && $request['nama']!=''){
            $jenis = $jenis->where('djp.satuanstandar','ilike','%'.$request['nama'].'%');
        }
        $jenis=  $jenis->limit(50);
        $jenis=  $jenis->get();
        $result =  array(
            'data' => $jenis,
        );
        return $this->respond($result);

    }

    public function saveSatuanHasil(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if($request['method']=='save'){
                if ($request['id']=="") {
                    $dataSO = new SatuanStandar();
                    $id =SatuanStandar::max('id') + 1;
                    $dataSO->id =$id ;
                    $dataSO->norec = $dataSO->generateNewId();
                    $dataSO->kdprofile = $idProfile;

                }else {
                    $dataSO =  SatuanStandar::where('id',$request['id'])->first();
                }
                $dataSO->statusenabled = true;// $request['statusenabled'];
                $dataSO->kodeexternal =$request['satuanstandar'];
                $dataSO->namaexternal = $request['satuanstandar'];
                $dataSO->satuanstandar = $request['satuanstandar'];
                $dataSO->objectdepartemenfk =$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $kdProfile);
                $dataSO->save();
            }else{
                SatuanStandar::where('id',$request['id'])->delete();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {

            $transMessage = "Simpan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Simpan  gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "e"=>$e,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getNilaiNormal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $jenis  = DB::table('nilainormal_m as djp')
            ->leftjoin('jeniskelamin_m as dp','dp.id','=','djp.objectjeniskelaminfk')
            ->leftjoin('kelompokumur_m as ku','ku.id','=','djp.kelompokumurfk')
            ->select('djp.*','dp.jeniskelamin','ku.kelompokumur','ku.umurmax','ku.umurmin','ku.statusumur')
            ->where('djp.kdprofile', $kdProfile  )
            ->where('djp.statusenabled',true);
        // ->where('djp.objectdepartemenfk',$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $kdProfile));
        if(isset($request['nama']) && $request['nama']!='undefined' && $request['nama']!=''){
            $jenis = $jenis->where('djp.nilaitext','ilike','%'.$request['nama'].'%')
                ->orwhere('djp.metode','ilike','%'.$request['nama'].'%');
        }
        if(isset($request['jeniskelaminfk']) && $request['jeniskelaminfk']!='undefined' && $request['jeniskelaminfk']!='' && $request['jeniskelaminfk']!='-'  && $request['jeniskelaminfk']!=0){
            $jenis = $jenis->where('djp.objectjeniskelaminfk','=',$request['jeniskelaminfk']);
        }
        if(isset($request['kelompokfk']) && $request['kelompokfk']!='undefined' && $request['kelompokfk']!=''&& $request['kelompokfk']!='-'){
            $jenis = $jenis->where('djp.kelompokumurfk','=',$request['kelompokfk']);
        }
        $jenis=  $jenis->limit(50);
        $jenis=  $jenis->get();
        $result =  array(
            'data' => $jenis,
        );
        return $this->respond($result);

    }

    public function saveNilaiNormal(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            if($request['method']=='save'){
                if ($request['id']=="") {
                    $dataSO = new NilaiNormal();
                    $id =NilaiNormal::max('id') + 1;
                    $dataSO->id =$id ;
                    $dataSO->norec = $dataSO->generateNewId();
                    $dataSO->kdprofile = $idProfile;

                }else {
                    $dataSO =  NilaiNormal::where('id',$request['id'])->first();
                }
                $dataSO->statusenabled = true;//$request['statusenabled'];
                $dataSO->objectjeniskelaminfk =$request['objectjeniskelaminfk'];
                $dataSO->nilaimax = $request['nilaimax'];
                $dataSO->nilaimin = $request['nilaimin'];
                $dataSO->nilaitext = $request['nilaitext'];
                $dataSO->kelompokumurfk = $request['kelompokumurfk'];
                $dataSO->metode = $request['metode'];
                $dataSO->save();
            }else{
                NilaiNormal::where('id',$request['id'])->delete();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {

            $transMessage = "Simpan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Simpan  gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "e"=>$e,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getMapHasilLab(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $jenis  = DB::table('maphasillab_m as map')
            ->join('produk_m as prd','prd.id','=','map.produkfk')
            ->leftjoin('detailjenisproduk_m as djp','djp.id','=','prd.objectdetailjenisprodukfk')
            ->leftjoin('satuanstandar_m as ss','ss.id','=','map.satuanstandarfk')
            ->select('map.*','prd.namaproduk','ss.satuanstandar','djp.detailjenisproduk')
            ->where('map.kdprofile', $kdProfile  )
            ->where('map.statusenabled',true);
        if(isset($request['nama']) && $request['nama']!='undefined' && $request['nama']!=''){
            $jenis = $jenis->where('prd.namaproduk','ilike','%'.$request['nama'].'%');
        }

        $jenis=  $jenis->limit(50);
        $jenis=  $jenis->get();

        foreach ($jenis as $key => $value) {
            $id =  $value->id;
            $detail = DB::select(DB::raw("select maps.*,jk.jeniskelamin,ku.kelompokumur,nn.nilaitext
                 from maphasillabdetail_m as maps
                join jeniskelamin_m as jk on jk.id =maps.jeniskelaminfk
                join kelompokumur_m as ku on ku.id =maps.kelompokumurfk
                join nilainormal_m as nn on nn.id =maps.nilainormalfk
                where maps.maphasilfk ='$id'"));
            $value->details = $detail;
        }
        $result =  array(
            'data' => $jenis,
        );
        return $this->respond($result);

    }
    public function saveMapHasilLab(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            if ($request['id']=="") {
                $dataSO = new MapHasilLab();
                $id =MapHasilLab::max('id') + 1;
                $dataSO->id =$id ;
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;

            }else {
                $dataSO =  MapHasilLab::where('id',$request['id'])->first();
                MapHasilLabDetail::where('maphasilfk',$request['id'])->delete();
            }
            $dataSO->statusenabled = true;//$request['statusenabled'];
            $dataSO->produkfk =$request['produkfk'];
            $dataSO->detailpemeriksaan = $request['detailpemeriksaan'];
            $dataSO->nourutdetail = $request['nourutdetail'];
            $dataSO->satuanstandarfk = $request['satuanstandarfk'];
            $dataSO->memohasil = $request['memohasil'];
            $dataSO->save();
            $iddet =  $dataSO->id;

            foreach ($request['details'] as $d) {

                $dataSO2 = new MapHasilLabDetail();
                $dataSO2->id =MapHasilLabDetail::max('id') + 1;
                $dataSO2->kdprofile = $idProfile;
                $dataSO2->statusenabled = true;//$request['statusenabled'];
                $dataSO2->jeniskelaminfk =$d['jeniskelaminfk'];
                $dataSO2->nilainormalfk = $d['nilainormalfk'];
                $dataSO2->kelompokumurfk = $d['kelompokumurfk'];
                $dataSO2->maphasilfk = $iddet;
                $dataSO2->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Simpan  gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "e"=>$e,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusMapHasilLab(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            MapHasilLabDetail::where('maphasilfk',$request['id'])->delete();
            MapHasilLab::where('id',$request['id'])->delete();


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Hapus  gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "e"=>$e,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getMasterProduk(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $jenis  = DB::table('produk_m as prd')
            ->leftjoin('detailjenisproduk_m as djp','djp.id','=','prd.objectdetailjenisprodukfk')
            ->leftJOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->select('prd.id','prd.namaproduk','prd.objectdetailjenisprodukfk','djp.detailjenisproduk','djp.objectjenisprodukfk','jp.jenisproduk')
            ->where('prd.kdprofile', $kdProfile  )
            ->where('prd.statusenabled',true)
            ->whereIn('djp.objectjenisprodukfk',[1549,1548,1419]);

        if(isset($request['nama']) && $request['nama']!='undefined' && $request['nama']!=''){
            $jenis = $jenis->where('prd.namaproduk','ilike','%'.$request['nama'].'%');
        }
        $jenis=  $jenis->take(100);
        $jenis=  $jenis->get();
        $result =  array(
            'data' => $jenis,
        );
        return $this->respond($result);

    }
    public function updateProduk(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            $produk = DB::table('produk_m')->where('id',$request['id'])->update(
                [
                    'objectdetailjenisprodukfk' => $request['detailjenisprodukfk']
                ]
            );


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Hapus  gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "e"=>$e,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
   public function getHasilLabManual(Request $r){
        $umur =$r['umur'];
        $data = DB::select(DB::raw("SELECT pp.noregistrasifk as norec_apd,djp.detailjenisproduk,pp.produkfk,prd.namaproduk  ,maps.detailpemeriksaan,maps.memohasil,
                maps.nourutdetail,maps.satuanstandarfk,ss.satuanstandar,nn.nilaitext,nn.tipedata,nn.nilaimin,nn.nilaimax,hh.hasil,
                maps.id as map_id,hh.norec as norec_hasil,maps.nourutjenispemeriksaan,maps.nourutdetail,pp.norec as norecPP,nn.nilaimin || '-' || nn.nilaimax as nilainormalstr,
                hh.flag,nn.id as idnn,maps2.jeniskelaminfk
                FROM pelayananpasien_t  as pp
                inner join produk_m as prd on prd.id = pp.produkfk
                inner join detailjenisproduk_m as djp on djp.id = prd.objectdetailjenisprodukfk
                inner join maphasillab_m  as maps on maps.produkfk = prd.id
                inner join maphasillabdetail_m  as maps2 on maps2.maphasilfk = maps.id 
                and maps2.jeniskelaminfk ='$r[objectjeniskelaminfk]'
                and maps2.kelompokumurfk in (select id from kelompokumur_m  kuu where $umur BETWEEN kuu.umurmin and kuu.umurmax) 
                inner join nilainormal_m  as nn on nn.id = maps2.nilainormalfk
                left join satuanstandar_m  as ss on ss.id = maps.satuanstandarfk
                left join hasillaboratorium_t  as hh on hh.norecpelayanan  = pp.norec 
                and pp.noregistrasifk=hh.noregistrasifk
                 and maps.detailpemeriksaan =hh.detailpemeriksaan 
                where   pp.norec in ($r[norec])  
                order by  maps.nourutjenispemeriksaan,maps.nourutdetail asc"));
        $result =  array(
            'data' => $data, 
        );        
        return $this->respond($result);  
    }
   public function saveHasilLabManual(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            foreach ($request['hasil'] as $key => $value) {
                $cek =  HasilLaboratorium::where([
                    // 'noregistrasifk',$request['hasil'][0]['noregistrasifk']
                    // 'norecpelayanan',$value['norecpelayanan']

                    'noregistrasifk' => $value['noregistrasifk'],
                    'norecpelayanan' => $value['norecpelayanan'],
                    'produkfk' => $value['produkfk'],
                    'detailpemeriksaan' => $value['detailpemeriksaan'],
                    // detailpemeriksaan
                    ])->first();
                if(!empty($cek)){
                   $update = HasilLaboratorium::where([
                        // 'noregistrasifk',$request['hasil'][0]['noregistrasifk']
                        // 'norecpelayanan',$value['norecpelayanan']
                        'noregistrasifk' => $value['noregistrasifk'],
                        'norecpelayanan' => $value['norecpelayanan'],
                        'produkfk' => $value['produkfk'],
                        'detailpemeriksaan' => $value['detailpemeriksaan'],
                        ])->update(
                            [
                                'hasil' => $value['hasil'],
                                'flag' => $value['flag'],
                            
                            ]
                        );
                }else
                {
                    $h = new HasilLaboratorium();
                    $h->norec = $h->generateNewId();
                    $h->kdprofile = $idProfile;
                    $h->statusenabled = true;
                    $h->tglhasil = date('Y-m-d H:i:s');
                    $h->pegawaifk = $this->getCurrentUserID();
                    $h->hasil = $value['hasil'];
                    $h->noregistrasifk = $value['noregistrasifk'];
                    $h->produkfk = $value['produkfk'];
                    $h->flag = $value['flag'];
                    $h->detailpemeriksaan = $value['detailpemeriksaan'];
                    $h->norecpelayanan = $value['norecpelayanan'];
                    $h->save();
                }
    
                    # code...
               
            }
            
        
            
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'er@epic',
            );

        } else {
            $transMessage = "Simpan  gagal!!";
            DB::rollBack();
            // dd($result);
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "e"=>$e,
                "as" =>'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getLapPemeriksaanPA(Request $request) {
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        
        $noCM = '';
        $noPA = '';
        $namaPasien = '';
        if (isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $noCM = " AND ps.nocm ilike '%".$request['nocm']."%'";
        }
        if (isset($request['nopa']) && $request['nopa'] != "" && $request['nopa'] != "undefined") {
            $noPA = " AND hl.nomorpa ilike '%".$request['nopa']."%'";
        }
        if (isset($request['namapasien']) && $request['namapasien'] != "" && $request['namapasien'] != "undefined") {
            $namaPasien = " AND ps.namapasien ilike '%".$request['namapasien']."%'";
        }
        $data = DB::select(DB::raw("
            select ps.nocm, hl.nomorpa , ps.namapasien,
            EXTRACT(YEAR FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Thn ' 
            || EXTRACT(MONTH FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Bln ' 
            || EXTRACT(DAY FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Hr' AS umur, jk.reportdisplay as jk, hl.topografi , hl.morfologi , hl.kesimpulan , pg.namalengkap as dokter_pa, kp.kelompokpasien 
            ,apd.tglmasuk ,case when apd.objectstrukorderfk is null then rm.namaruangan else rm2.namaruangan end as namaruangan, hl.jaringanasal ,hl.diagnosaklinik , pg2.namalengkap as dokterpengirim, p.namaproduk , pp.hargajual, pd.tglregistrasi 
            from antrianpasiendiperiksa_t as apd
            inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk 
            inner join pelayananpasien_t as pp on pp.noregistrasifk  = apd.norec 
            left  join hasilpemeriksaanlab_t as hl on hl.pelayananpasienfk = pp.norec 
            inner join pasien_m as ps on ps.id = pd.nocmfk 
            left join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk 
            left join pegawai_m as pg on pg.id = hl.pegawaifk 
            left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
            left join ruangan_m as rm on rm.id = apd.objectruanganasalfk 
            left join strukorder_t st on st.norec = apd.objectstrukorderfk 
            left join ruangan_m as rm2 on rm2.id = st.objectruanganfk
            left join ruangan_m as rm3 on rm3.id = apd.objectruanganfk
            left join pegawai_m as pg2 on pg2.id = hl.dokterpengirimfk 
            inner join produk_m as p on p.id = pp.produkfk  
            where apd.tglmasuk between '$tglAwal' and '$tglAkhir' and rm3.id = 39
            $noCM
            $noPA
            $namaPasien"
        ));

        $i = 1;
        foreach ($data as $dat) {
            $dat->nomor = $i++;
        }
        return $this->respond($data);
    }
    
    public function getLaporanTindakanBankDarah(Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join ('jeniskelamin_m as jk','jk.id', '=','ps.objectjeniskelaminfk')
            ->join ('ruangan_m as rg','rg.id','=','pd.objectruanganlastfk')
            ->leftjoin ('kelompokpasien_m as klp','klp.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin ('produk_m as pro','pro.id','=','pp.produkfk')
            ->leftjoin ('strukorder_t AS so','so.norec','=','apd.objectstrukorderfk')
            ->leftjoin ('ruangan_m AS ru1','ru1.id', '=', 'so.objectruanganfk')
            ->leftjoin ('ruangan_m AS ru2', 'ru2.id', '=', 'apd.objectruanganasalfk')
            ->leftjoin ('batalregistrasi_t AS br', 'br.pasiendaftarfk', '=' ,'pd.norec')
            ->leftjoin ('pegawai_m AS pg','pg.id','=','apd.objectpegawaifk')
            ->select(DB::raw("
                 pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,
                 CASE WHEN ru1.namaruangan IS NOT NULL THEN ru1.namaruangan ELSE ru2.namaruangan END AS ruangan,
                 klp.kelompokpasien,pro.namaproduk,pp.jumlah,pp.hargajual,pg.namalengkap
 			 "))
            ->where('pd.statusenabled', true)
            ->where('apd.objectruanganfk', 41)
            ->where('pro.namaproduk', '!=', null)
            ->whereNull('br.norec');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
            $data = $data->where('rg.id', '=', $request['ruanganId']);
        }
        if (isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'.$request['nocm'].'%');
//				->orWhere('ps.namapasien', 'ilike', '%'.$filter['noRmNama'].'%')	;
        }
        if (isset($request['nama']) && $request['nama'] != "" && $request['nama'] != "undefined") {
            $data = $data->Where('ps.namapasien', 'ilike', '%'.$request['nama'].'%')	;
        }
        if (isset($request['dokter']) && $request['dokter'] != "" && $request['dokter'] != "undefined") {
            $data = $data->Where('pg.id', '=', $request['dokter'])	;
        }
        if (isset($request['kotaKab']) && $request['kotaKab'] != "" && $request['kotaKab'] != "undefined") {
            $data = $data->Where('kkb.id', '=', $request['kotaKab'])	;
        }
        // if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
        //     $data = $data->Where('klp.id', '=', $request['kelompokPasien'])	;
        // }
        if (isset($request['KpArr']) && $request['KpArr']!="" && $request['KpArr']!="undefined"){
            $arrayKelompokPasien = explode(',',$request['KpArr']) ;
            $ids = [];
            foreach ( $arrayKelompokPasien as $item){
                $ids[] = (int) $item;
            }
            $data = $data->whereIn('klp.id', $ids);
        }
        $data = $data->orderBy('pd.tglregistrasi', 'asc');
        $data =  $data ->get();
        foreach ($data as $d) {
            if (empty($d->namalengkap)) {
                $d->namalengkap = '-';
            }
        }
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanKunjungan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['ruanganId'];
        $kelompokId = $request['kelompokPasien'];
        $jenispelayananId = $request['jenisPelayanan'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and pd.objectruanganlastfk = ' . $ruanganId;
        }

        $paramKelompok = ' ';
        if (isset($kelompokId) && $kelompokId != "" && $kelompokId != "undefined") {
            $paramKelompok = ' and pd.objectkelompokpasienlastfk = ' . $kelompokId;
        }

        $paramJenisPelayanan = ' ';
        if (isset($jenispelayananId) && $jenispelayananId != "" && $jenispelayananId != "undefined") {
            if($jenispelayananId == 1){
                $paramJenisPelayanan = ' and ru.iseksekutif = false' ;
            }else{
                $paramJenisPelayanan = ' and ru.iseksekutif = true';
            }
        }

        $deptRawatJalan = $this->settingDataFixed('kdDepartemenRawatJalanFix',$kdProfile);

        // $data = DB::select(DB::raw("SELECT ru.namaruangan,kp.kelompokpasien,CASE WHEN jk.id = 1 THEN 1 ELSE 0 END AS laki,
        //          CASE WHEN jk.id = 2 THEN 1 ELSE 0 END AS wanita,CASE WHEN pd.statuspasien = 'BARU' THEN 1 ELSE 0 END AS baru,
        //          CASE WHEN pd.statuspasien = 'LAMA' THEN 1 WHEN pd.statuspasien = '' THEN 1 ELSE 0 END AS lama
        //         FROM pasiendaftar_t as pd
        //         INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
        //         INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
        //         INNER JOIN pasien_m as ps on ps.id = pd.nocmfk
        //         LEFT JOIN jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
        //         WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
        //         and ru.objectdepartemenfk in ($deptRawatJalan)
        //         $paramRuangan $paramKelompok "
        // )
        // );
        $data = DB::select(DB::raw("select x.ruangan,SUM(x.laki) AS laki,SUM(x.wanita) AS wanita,SUM(x.baru) as baru,SUM(x.lama) as lama,
        SUM(x.tunai) as tunai,SUM(x.bpjs) as bpjs,SUM(x.swasta) as swasta,SUM(x.hardient) as hardient,
        SUM(x.iks) as iks,SUM(x.thamrin) as thamrin,SUM(x.jamsostek) as jamsostek,SUM(x.jamkesda)as jamkesda,
        SUM(x.skmm) as skmm,SUM(x.karyawan) as karyawan,SUM(x.jamkesmas) as jamkesmas,SUM(x.inhealth) as inhealth,
        SUM(x.tunai)+SUM(x.bpjs)+SUM(x.swasta)+SUM(x.hardient)+SUM(x.iks)+SUM(x.jamsostek)+SUM(x.jamkesmas)+SUM(x.jamkesda)+SUM(x.skmm)+SUM(x.karyawan)+SUM(x.thamrin) as jml
        FROM (SELECT ru3.namaruangan,
        case when ru2.objectdepartemenfk in (18) then 'RJ' when ru2.objectdepartemenfk in (16) then 'RI' when ru2.objectdepartemenfk in (24) then 'IGD' end 
        as ruangan,
        CASE WHEN jk.id = 1 THEN 1 ELSE 0 END AS laki,
        CASE WHEN jk.id = 2 THEN 1 ELSE 0 END AS wanita,CASE WHEN pd.statuspasien = 'BARU' THEN 1 ELSE 0 END AS baru,
        CASE WHEN pd.statuspasien = 'LAMA' THEN 1 WHEN pd.statuspasien = '' THEN 1 ELSE 0 END AS lama,
        CASE WHEN pd.objectkelompokpasienlastfk = 1 THEN 1 ELSE 0 END AS tunai,
        CASE WHEN pd.objectkelompokpasienlastfk in (2,11) THEN 1 ELSE 0 END AS bpjs,
        CASE WHEN pd.objectkelompokpasienlastfk = 3
        and pd.objectrekananfk NOT IN (5906,581183,581184,581185,581186,581187,581140,581136,581162,581187,581188,581165,581168,
        806,581189,581169,581170,581171,581190,581173,581174,581175,581176,581177,581178,581179,
        581180,3269,581182,581191,735,1903,2115,1927,150) THEN 1 ELSE 0 END AS swasta,
        CASE WHEN pd.objectkelompokpasienlastfk = 3 and pd.objectrekananfk IN (581191) THEN 1 ELSE 0 END AS hardient,
        CASE WHEN pd.objectkelompokpasienlastfk = 3 and pd.objectrekananfk IN (5906,581183,581184,581185,581186,581187,581140,581136,581162,581187,581188,581165,581168,
        806,581189,581169,581170,581171,581190,581173,581174,581175,581176,581177,581178,581179,
        581180,3269,581182) THEN 1 ELSE 0 END AS iks,
        CASE WHEN pd.objectkelompokpasienlastfk = 3
        and pd.objectrekananfk IN (735,1903,2115,1927,150) THEN 1 ELSE 0 END AS thamrin,
        Case when pd.objectkelompokpasienlastfk = 3 
        and pd.objectrekananfk in (2669,3023,3039,3057,581045,581140) THEN 1 ELSE 0 END AS inhealth,
        CASE WHEN pd.objectkelompokpasienlastfk in (14) THEN 1 ELSE 0 END AS jamsostek,
        CASE WHEN pd.objectkelompokpasienlastfk in (8) THEN 1 ELSE 0 END AS jamkesda,
        CASE WHEN pd.objectkelompokpasienlastfk in (15) THEN 1 ELSE 0 END AS skmm,
        CASE WHEN pd.objectkelompokpasienlastfk in (12) THEN 1 ELSE 0 END AS karyawan,
        CASE WHEN pd.objectkelompokpasienlastfk in (17) THEN 1 ELSE 0 END AS jamkesmas
        FROM pasiendaftar_t AS pd
        INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
        inner join antrianpasiendiperiksa_t at2 on at2.noregistrasifk = pd.norec 
        INNER JOIN pasien_m as ps on ps.id = pd.nocmfk
        LEFT JOIN jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
        INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
        inner join ruangan_m as ru2 on ru2.id = at2.objectruanganasalfk
        inner join ruangan_m as ru3 on ru3.id = at2.objectruanganfk 
        WHERE pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir' AND pd.statusenabled=true and ru3.id = $ruanganId and ru2.objectdepartemenfk in (16,18,24)
         and pd.kdprofile=21 ) as x
        GROUP BY x.ruangan
        ORDER BY x.ruangan ASC"));
        return $this->respond($data);
    }

    public function savePMI(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all(); 
        DB::beginTransaction();
        try{
            $data = PelayananPasien::where('norec', $request['norec_pp'])
                    ->update([
                        'pmifk' => $request['pmi']
                    ]);
            $transStatus = true;
        }catch (\Exception $e) {
            $transStatus = false;
        }
        
        if ($transStatus) {
            DB::commit();
            $result = array(
                "code" => 201,
                "status" => true,
                "message" => "success",
            );
        }
        else {
            DB::rollback();
            $result = array(
                "code" => 500,
                "status" => false,
                "message" => "failed"
            );
        }
    }

    public function getDokter(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detail = DB::select(DB::raw("select id,namalengkap from pegawai_m WHERE objectjenispegawaifk='1'"));

        return $detail;
    }

    public function saveAntrianPasienDarah(Request $request){
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
}