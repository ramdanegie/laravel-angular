<?php
/**
 * Created by PhpStorm.
 * User: Khrisnanda
 * Date: 06/11/2019
 * Time: 15:22
 */

namespace App\Http\Controllers\Registrasi;

use App\Http\Controllers\ApiController;
use App\Master\Kelas;
use App\Master\Agama;
use App\Master\Alamat;
use App\Master\AsuransiPasien;
use App\Master\FlagGenerateNoCm;
use App\Master\JenisDiagnosa;
use App\Master\JenisKelamin;
use App\Master\Pasien;
use App\Master\Pendidikan;
use App\Master\Ruangan;
use App\Master\RunningNumber;
use App\Master\SettingDataFixed;
use App\Master\StatusPerkawinan;
use App\Master\TempatTidur;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\AntrianPasienRegistrasi;
use App\Transaksi\BatalRegistrasi;
use App\Transaksi\DetailDiagnosaPasien;
use App\Transaksi\DetailDiagnosaTindakanPasien;
use App\Transaksi\DiagnosaPasien;
use App\Transaksi\DiagnosaTindakanPasien;
use App\Transaksi\EMRPasien;
use App\Transaksi\IdentifikasiPasien;
use App\Transaksi\LoggingUser;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\PemakaianAsuransi;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\RisOrder;
use App\Transaksi\StrukKirim;
use App\Transaksi\StrukOrder;
use App\Transaksi\StrukPraOrder;
use App\Transaksi\StrukResep;
use App\Transaksi\TempBilling;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\This;

class LaporanPengunjungController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getLaporanPengunjung(Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','ps.objectjeniskelaminfk')
            ->JOIN ('agama_m as ag','ag.id','=','ps.objectagamafk')
            ->leftjoin ('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin ('pekerjaan_m as pkr','pkr.id','=','ps.objectpekerjaanfk')
            ->leftjoin ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin ('desakelurahan_m as dsk','dsk.id','=','alm.objectdesakelurahanfk')
            ->leftjoin ('kotakabupaten_m as kkb','kkb.id','=','alm.objectkotakabupatenfk')
            ->leftjoin ('statusperkawinan_m as sp','sp.id','=','objectstatusperkawinanfk')
            ->leftjoin ('pegawai_m as pg','pg.id','=','pd.objectdokterpemeriksafk')
            ->join ('ruangan_m as rg','rg.id','=','apd.objectruanganfk')
            // ->leftjoin ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->leftJoin('kelompokpasien_m as klp','klp.id','=','pd.objectkelompokpasienlastfk')
            // ->leftJoin('detaildiagnosapasien_t as ddp','ddp.objectdiagnosapasienfk', '=','dp.norec')
            // ->leftjoin ('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
            ->select('pd.norec','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ps.nohp','ps.tgllahir','jk.jeniskelamin','ag.agama','pdd.pendidikan','pkr.pekerjaan','alm.alamatlengkap',
                'dsk.namadesakelurahan','alm.kecamatan','kkb.namakotakabupaten','sp.statusperkawinan','pd.statuspasien','rg.namaruangan','pg.namalengkap','ps.tgldaftar','klp.kelompokpasien','apd.norec as norec_apd',
                DB::raw("to_char(pd.tglregistrasi,'DD-MM-YYYY') as tglregistrasi1,to_char(pd.tglregistrasi,'HH:mm') as jamregistrasi,'' AS kddiagnosa"))
            ->where('pd.statusenabled',1);
            //->where('ddp.objectjenisdiagnosafk',1);
            DB::raw('ddp.objectjenisdiagnosafk = 1 or ddp.objectjenisdiagnosafk IS NULL' );
//            ->where('ddp.objectjenisdiagnosafk',NULL);
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

        $data =  $data ->get();
        $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
                    ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
                    ->join('diagnosapasien_t AS dp','dp.norec','=','ddp.objectdiagnosapasienfk')
                    ->join ('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
                    ->select(DB::raw("apd.noregistrasifk,ddp.objectjenisdiagnosafk,dg.kddiagnosa AS diagnosa,
                                     CASE WHEN dp.iskasusbaru = true AND dp.iskasuslama = false THEN 'BARU'
                                     WHEN dp.iskasuslama = true AND dp.iskasusbaru = false THEN 'LAMA' ELSE '' END kasus"))
                    ->where('apd.kdprofile', 21)
                    ->where('apd.statusenabled', true)
                    ->get();
                    $norecaPd = '';
                    $diagnosa = '';
                    foreach ($data as $ob){
                        $norecaPd = $norecaPd.",'".$ob->norec_apd . "'";
//                        $ob->kddiagnosa = [];
                    }
                    $norecaPd = substr($norecaPd, 1, strlen($norecaPd)-1);
//                    $diagnosa = [];
                    if($norecaPd!= ''){
                        $diagnosa = DB::select(DB::raw("
                           select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
                           from detaildiagnosapasien_t as ddp
                           left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                           left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                           where ddp.noregistrasifk in ($norecaPd) "));
                        $i = 0;
                       foreach ($data as $h){
                           foreach ($diagnosa as $d){
                               if($data[$i]->norec_apd == $d->norec_apd){
                                   if ($d->kddiagnosa != null){
                                       $data[$i]->kddiagnosa = $data[$i]->kddiagnosa . ', ' . $d->kddiagnosa;
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
//                        return $this->respond()
                        $data[$d]->kddiagnosa = substr($data[$d]->kddiagnosa,1);
                        $result [] = $data[$d];
                    }
                    $d = $d + 1;
                }

//        $result = array(
//            'data'=> $data,
//            'message' => 'ramdanegie',
//        );
        return $this->respond($result);
    }
    public function getDataCombo(Request $request)
    {
        $ruangan= \DB::table('ruangan_m')
            ->select('id','namaruangan')
            ->where('statusenabled',true)
            ->get();
        $dokter= \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('statusenabled',true)
            ->where('objectjenispegawaifk',true)
            ->get();

            $result = array(
            'ruangan' => $ruangan,
            'dokter' => $dokter,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getKelasByRuangan(Request $request) {
        $data = \DB::table('mapruangantokelas_m as mrk')
            ->join ('ruangan_m as ru','ru.id','=','mrk.objectruanganfk')
            ->join ('kelas_m as kl','kl.id','=','mrk.objectkelasfk')
            ->select('kl.id','kl.namakelas','ru.id as id_ruangan','ru.namaruangan')
            ->where('mrk.objectruanganfk', $request['idRuangan'])
            ->get();

        $result = array(
            'kelas'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getKamarByKelasRuangan(Request $request) {
        $data = \DB::table('kamar_m as kmr')
            ->join ('ruangan_m as ru','ru.id','=','kmr.objectruanganfk')
            ->join ('kelas_m as kl','kl.id','=','kmr.objectkelasfk')
            ->select('kmr.id','kmr.namakamar','kl.id as id_kelas','kl.namakelas','ru.id as id_ruangan',
                'ru.namaruangan','kmr.jumlakamarisi','kmr.qtybed')
            ->where('kmr.objectruanganfk', $request['idRuangan'])
            ->where('kmr.objectkelasfk', $request['idKelas'])
            ->where('kmr.statusenabled',true)
            ->get();

        $result = array(
            'kamar'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getNoBedByKamar(Request $request) {
        $data = \DB::table('tempattidur_m as tt')
            ->join ('statusbed_m as sb','sb.id','=','tt.objectstatusbedfk')
            ->join ('kamar_m as km','km.id','=','tt.objectkamarfk')
            ->select('tt.id','sb.statusbed','tt.reportdisplay')
            ->where('tt.objectkamarfk', $request['idKamar'])
            ->where('km.statusenabled',true)
            ->get();

        $result = array(
            'bed'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getRekananSaeutik(Request $request)
    {
        $req = $request->all();
        $datRek = \DB::table('rekanan_m as rek')
            ->select('rek.id','rek.namarekanan' )
            ->where('rek.statusenabled', true)
            ->orderBy('rek.namarekanan');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $datRek = $datRek
                ->where('rek.namarekanan','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
//                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $datRek=$datRek->take(10);
        $datRek=$datRek->get();

        return $this->respond($datRek);
    }
    private function generateNoReg($objectModel, $atrribute, $length=8, $prefix=''){
        $result = $objectModel->where($atrribute, 'ilike', $prefix.'%')->max($atrribute);
        $prefixLen = strlen($prefix);
        $subPrefix = substr(trim($result),$prefixLen);
        return $prefix.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));
    }
    public function saveRegistrasiPasien(Request $request) {
        $noRegistrasiSeq = $this->generateCodeBySeqTable(new PasienDaftar, 'noregistrasi', 10, date('ym'));
        $noAntrian=0;
        if ($noRegistrasiSeq == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }

        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];
//        $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$r_NewPD['objectruanganfk'])
//            ->where('tglregistrasi', '>=', $r_NewPD['tglregistrasidate'].' 00:00')
//            ->where('tglregistrasi', '<=', $r_NewPD['tglregistrasidate'].' 23:59')
//            ->count('norec');
//        $noAntrian = $countNoAntrian + 1;

        if($r_NewPD['israwatinap']=='true'){
            $isRawatInap='true';
        }else{
            $isRawatInap='false';
        }
        DB::beginTransaction();
        $cekUdahDaftar=PasienDaftar::where('nocmfk', $r_NewPD['nocmfk'])
            ->wherenull('tglpulang')
            ->count();
        if($cekUdahDaftar > 0 && $r_NewPD['norec_pd']=='')
        {
            $transStatus='belumdipulangkan';
        }else{
            try{
                //region Save PasienDaftar
                if ($r_NewPD['norec_pd']==''){
                    $dataPD = new PasienDaftar();
                    $dataPD->norec = $dataPD->generateNewId();
                    $dataPD->kdprofile = 1;
                    $dataPD->statusenabled = true;
                    $noRegistrasi = $noRegistrasiSeq;//$this->generateNoReg(new PasienDaftar, 'noregistrasi', 10, date('ym'));//$this->getMaxNoregistrasi();
                }else{
                    $dataPD =  PasienDaftar::where('norec',$r_NewPD['norec_pd'])->first();
                    $noRegistrasi = $dataPD->noregistrasi ;
                    //          $delAntrian = AntrianPasienDiperiksa::where('noregistrasifk', $r_NewPD['norec_pd'])
                    //                ->delete();
                    if ($isRawatInap=='true'){
                        $delRegistrasiPelPasien = RegistrasiPelayananPasien::where('noregistrasifk', $r_NewPD['norec_pd'])
                            ->where('objectruanganfk',$r_NewPD['objectruanganfk'])
                            ->delete();
                    }
                }

                $dataPD->objectruanganlastfk = $r_NewPD['objectruanganfk'];
                $dataPD->objectdokterpemeriksafk =  $r_NewPD['objectpegawaifk'];
                $dataPD->objectpegawaifk = $r_NewPD['objectpegawaifk'];
                $dataPD->iskajianawal = false;
                $dataPD->isonsiteservice = 0;
                $dataPD->isregistrasilengkap = 0;
                $dataPD->jenispelayanan = $r_NewPD['tipelayanan'];
                $dataPD->objectkasuspenyakitlastfk = 1;
                if ($isRawatInap=='true'){
                    $dataPD->objectkelasfk = $r_NewPD['objectkelasfk'];
                    $dataPD->tglpulang = null;
                }else{
                    $dataPD->objectkelasfk = (int)$this->settingDataFixed('kdKelasNonKelasRegistrasi');
                    $dataPD->tglpulang =  $r_NewPD['tglregistrasi'];
                }
                $dataPD->objectkelompokpasienlastfk = $r_NewPD['objectkelompokpasienlastfk'];
                $dataPD->nocmfk = $r_NewPD['nocmfk'];
                $dataPD->objectrekananfk = $r_NewPD['objectrekananfk'];
                $dataPD->statuskasuspenyakit = false;
                $cekStatusPasien=PasienDaftar::where('nocmfk', $r_NewPD['nocmfk'])
                    ->count('nocmfk');
                if ($cekStatusPasien  > 0){
                    $statusPasien='LAMA';
                }else{
                    $statusPasien='BARU';
                }
                $dataPD->statuspasien = $statusPasien;
                if(isset($r_NewPD['statusschedule'])){
                    $dataPD->statusschedule = $r_NewPD['statusschedule'];
                }
                $dataPD->tglregistrasi =  $r_NewPD['tglregistrasi'];
                //region Dibuang Sayang
                //$dataPD->noregistrasifk = $noRegistrasi;
                //$dataPD->objectpenyebabkematianfk = true;
                //$dataPD->objectkondisipasienfk = true;
                //$dataPD->namalengkapambilpasien = true;
                //$dataPD->objecthubungankeluargaambilpasienfk = true;
                //$dataPD->tglpulang = date('Y-m-d H:i:s')
                //$dataPD->ruangannextschedule = true;
                //$dataPD->objectstatuskeluarfk = true;
                //$dataPD->objectstatuspulangfk = true;
                //$dataPD->totalbiaya = true;
                //$dataPD->nostruklastfk = true;
                //$dataPD->nosbmlastfk = true;
                //$dataPD->emergensi = true;
                //$dataPD->tglmeninggal = true;
                //endregion
                $dataPD->noregistrasi = $noRegistrasi;
                $dataPD->save();
                $dataPDnorec = $dataPD->norec;
                $dataStatusPasien= $dataPD->statuspasien;
                //endregion
                //region Save AntrianPasienDiperiksa


                if ($r_NewAPD['norec_apd']=='' ){
                    $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$r_NewPD['objectruanganfk'])
                        ->where('tglregistrasi', '>=', $r_NewPD['tglregistrasidate'].' 00:00')
                        ->where('tglregistrasi', '<=', $r_NewPD['tglregistrasidate'].' 23:59')
                        ->where('statusenabled',true)
                        ->max('noantrian');
                    $noAntrian = $countNoAntrian + 1;
//                    return $noAntrian;
                    $dataAPD =new AntrianPasienDiperiksa;
                    $dataAPD->norec = $dataAPD->generateNewId();
                    $dataAPD->kdprofile = 1;
                    $dataAPD->statusenabled = true;
                    $dataAPD->noantrian = $noAntrian;
//                    $dataAPD->objectruanganfk = $r_NewAPD['objectruanganfk'];
                }else{
                    $dataAPD =  AntrianPasienDiperiksa::where('norec',$r_NewAPD['norec_apd'])->first();
                    if($r_NewPD['objectruanganfk'] != $dataAPD->objectruanganfk ){
                        $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$r_NewPD['objectruanganfk'])
                            ->where('tglregistrasi', '>=', $r_NewPD['tglregistrasidate'].' 00:00')
                            ->where('tglregistrasi', '<=', $r_NewPD['tglregistrasidate'].' 23:59')
                            ->where('statusenabled',true)
                            ->max('noantrian');
                        $noAntrian = $countNoAntrian + 1;
                        $dataAPD->noantrian = $noAntrian;
                    }
                    //kosongkan
                    if ($isRawatInap=='true') {
                        TempatTidur::where('id', $dataAPD->nobed)->update(['objectstatusbedfk' => 2]);
                    }
                }
                //count tgl pasien perruanga
                $dataAPD->objectasalrujukanfk =  $r_NewAPD['objectasalrujukanfk'];
                $dataAPD->objectkamarfk = $r_NewAPD['objectkamarfk'];
                $dataAPD->objectkasuspenyakitfk = null;
                $dataAPD->objectruanganfk = $r_NewPD['objectruanganfk'];
                if ($isRawatInap=='true'){
                    $dataAPD->objectkelasfk = $r_NewAPD['objectkelasfk'];
                    $dataAPD->tglkeluar= null;
                }else{
                    $dataAPD->objectkelasfk = (int)$this->settingDataFixed('kdKelasNonKelasRegistrasi');
                    $dataAPD->tglkeluar = $r_NewPD['tglregistrasi'];
                }
                $dataAPD->nobed = $r_NewAPD['nobed'];
                $dataAPD->noregistrasifk = $dataPDnorec;
                $dataAPD->objectpegawaifk = $r_NewAPD['objectpegawaifk'];
                $dataAPD->statusantrian = 0;
                $dataAPD->statuskunjungan =$dataStatusPasien;
                $dataAPD->statuspasien = 1;
                $dataAPD->tglregistrasi =  $r_NewAPD['tglregistrasi'];
                $dataAPD->tglmasuk =$r_NewAPD['tglregistrasi'];
                $dataAPD->israwatgabung = null;
                //region Dibuang Sayang Oge
                //$dataAPD->tgldipanggildokter = null;
                //$dataAPD->tgldipanggilsuster = null;
                //$dataAPD->objectruanganasalfk = $r_NewAPD['objectruanganfk'];
                //$dataAPD->tglkeluar = null;
                //$dataAPD->statuspenyakit =null;
                //$dataAPD->objectstrukorderfk = null;
                //$dataAPD->objectstrukreturfk = null;
                //$dataAPD->prefixnoantrian = null;
                //$dataAPD->nomasuk = '';
                //$cekRuanganAsal=AntrianPasienDiperiksa::wherenull('objectruanganasalfk')->first();
                //if($cekRuanganAsal->count >0)
                //{
                //    $dataAPD->objectruanganfk = $r_NewAPD['objectruanganfk'];
                //}
                //endregion
                $dataAPD->save();
                //update statusbed jadi Isi
                if ($isRawatInap=='true') {
                    TempatTidur::where('id', $r_NewAPD['nobed'])->update(['objectstatusbedfk' => 1]);
                }
                //endregion
                //region Save RegistrasiPelayananPasien
                if ($isRawatInap=='true') {
                    $dataRPP = new RegistrasiPelayananPasien();
                    $dataRPP->norec = $dataRPP->generateNewId();;
                    $dataRPP->kdprofile = 1;
                    $dataRPP->statusenabled = true;
                    $dataRPP->objectasalrujukanfk = $r_NewAPD['objectasalrujukanfk'];
                    $dataRPP->israwatgabung = $r_NewAPD['israwatgabung'];;
                    $dataRPP->objectkamarfk =$r_NewAPD['objectkamarfk'];
                    $dataRPP->objectkelasfk = $r_NewAPD['objectkelasfk'];
                    $dataRPP->objectkelaskamarfk = $r_NewAPD['objectkelasfk'];
                    $dataRPP->kdpenjaminpasien = 0;
                    $dataRPP->objectkelompokpasienfk = $r_NewPD['objectkelompokpasienlastfk'];
                    $dataRPP->noantrianbydokter = 0;
                    $dataRPP->nocmfk = $r_NewPD['nocmfk'];
                    $dataRPP->noregistrasifk = $dataPDnorec;
                    $dataRPP->objectruanganfk = $r_NewAPD['objectruanganfk'];
                    $dataRPP->objectstatuskeluarfk = null;
                    $dataRPP->objecttempattidurfk = $r_NewAPD['nobed'];
                    $dataRPP->tglmasuk = $r_NewAPD['tglregistrasi'];
                    //region Dibuang Sayang Deui
                    //            $dataRPP->objectkasuspenyakitfk = null;
                    //            $dataRPP->kddokter = null;
                    //            $dataRPP->kddokterperiksanext =  null;
                    //            $dataRPP->objecthasiltriasefk =null;
                    //            $dataRPP->objectkelaskamarrencanafk =null;
                    //            $dataRPP->objectkelaskamartujuanfk =null;
                    //            $dataRPP->objectkelasrencanafk = null;
                    //            $dataRPP->objectkelastujuanfk = null;
                    //            $dataRPP->objectkeadaanumumfk = null;
                    //            $dataRPP->keteranganlainnyaperiksanext = null;
                    //            $dataRPP->keteranganlainnyarencana = '';
                    //            $dataRPP->kodenomorbuktiperjanjian = null;
                    //            $dataRPP->objectkondisipasienfk = null;
                    //            $dataRPP->namatempattujuan =null;
                    //            $dataRPP->noantrian = null;
                    //            $dataRPP->nobed = null;
                    //            $dataRPP->nobedtujuan =  null;
                    //            $dataRPP->prefixnoantrian =  '1';
                    //            $dataRPP->objectruanganasalfk = $r_NewAPD['objectruanganfk'];
                    //            $dataRPP->objectruanganperiksanextfk = $r_NewAPD['objectruanganfk'];
                    //            $dataRPP->objectruanganrencanafk =  null;
                    //            $dataRPP->objectruangantujuanfk =  null;
                    //            $dataRPP->objectstatuskeluarrencanafk =  null;
                    //            $dataRPP->statuspasien =  0;
                    //            $dataRPP->tglkeluar = null;
                    //            $dataRPP->tglkeluarrencana = null;
                    //            $dataRPP->tglperiksanext = null;
                    //            $dataRPP->tglpindah = $request['tglRegistrasi'];
                    //                 $dataRPP->objecttransportasifk = null;
                    //            $dataRPP->objectdetailkamarfk = null;
                    //endregion
                    $dataRPP->save();
                }
                //endregion
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
            }
        }

        if ($transStatus == 'belumdipulangkan') {
            $transMessage = 'Pasien Belum Dipulangkan';
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ramdanegie',
            );
        }
        else if ($transStatus == 'true') {
            $transMessage = 'SUKSES';
            DB::commit();
//            $cek = DB::table('pasiendaftar_t')->where('noregistrasi',$dataPD->noregistrasi)->get();
//            if (count($cek) > 1){
//                $noRegistrasis = $this->generateNoReg(new PasienDaftar, 'noregistrasi', 10, date('ym'));
//                PasienDaftar::where('norec', $dataPD->norec)->update([
//                        'noregistrasi' => $noRegistrasis]
//                );
//            }
            $result = array(
                'status' => 201,
                'message'=>$transMessage,
                'dataPD' => $dataPD,
                'dataAPD' => $dataAPD,
//                "double" => count($cek),
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = 'Simpan Registrasi Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'=>$transMessage,
                'dataPD' => $dataPD,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPasienByNoRecPD(Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin ('registrasipelayananpasien_t as rpp','rpp.noregistrasifk','=',
                DB::raw("pd.norec and rpp.objectruanganfk=apd.objectruanganfk"))
            ->leftjoin ('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join ('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin ('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTJOIN ('kamar_m as kmr','kmr.id','=','rpp.objectkamarfk')
            ->leftjoin ('tempattidur_m as tt','tt.id','=','rpp.objecttempattidurfk')
            ->leftjoin ('asalrujukan_m as ar','ar.id','=','apd.objectasalrujukanfk')
            ->leftjoin ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin ('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->leftjoin ('pegawai_m as pg','pg.id','=','apd.objectpegawaifk')
            ->leftjoin ('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('pd.norec as norec_pd','pd.noregistrasi','pd.tglregistrasi','pd.objectruanganlastfk','ru.namaruangan','pd.objectkelasfk','kls.namakelas',
                'rpp.objectkamarfk','kmr.namakamar','rpp.objecttempattidurfk','apd.objectasalrujukanfk','ar.asalrujukan',
                'pd.objectkelompokpasienlastfk','kps.kelompokpasien','pd.objectrekananfk','rk.namarekanan',
                'jpl.kodeinternal as objectjenispelayananfk','jpl.jenispelayanan','pd.objectpegawaifk','pg.namalengkap as dokter','ru.objectdepartemenfk',
                'tt.reportdisplay',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' 
                when ru.objectdepartemenfk =45 then \'edelweis\' else \'false\' end as israwatinap'))
            ->where('pd.norec', $request['norecPD'])
            ->where('apd.norec', $request['norecAPD'])
//            ->whereNull('apd.objectruanganasalfk')
            ->get();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getDiagnosaSaeutik(Request $request)
    {
        $req = $request->all();
        $datRek = \DB::table('diagnosa_m as dg')
            ->select('dg.id','dg.kddiagnosa','dg.namadiagnosa' ,
//                DB::raw("dg.kddiagnosa || '-' || dg.namadiagnosa as nama"))
                DB::raw("dg.kddiagnosa + '-' + dg.namadiagnosa as nama"))
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosa');

        if(isset($req['kddiagnosa']) &&
            $req['kddiagnosa']!="" &&
            $req['kddiagnosa']!="undefined"){
            $datRek = $datRek->where('dg.kddiagnosa','ilike','%'. $req['kddiagnosa'] .'%' );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $datRek = $datRek
                ->where('dg.kddiagnosa','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
//                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $datRek=$datRek->take(10);
        $datRek=$datRek->get();

        return $this->respond($datRek);
    }
    public function saveAsuransiPasien(Request $request)
    {
        DB::beginTransaction();
        try{
            if ($request['asuransipasien']['kelompokpasien'] != 'null' || $request['asuransipasien']['kelompokpasien'] != 'undefined') {
                $updateKPS = PasienDaftar::where('noregistrasi', $request['asuransipasien']['noregistrasi'])
                    ->update([
                            'objectkelompokpasienlastfk' =>  $request['asuransipasien']['kelompokpasien'],
                            'objectrekananfk' =>  $request['asuransipasien']['kdpenjaminpasien'],
                        ]
                    );
            }
            if ($request['asuransipasien']['notelpmobile'] != '' ) {
                $updatepas= Pasien::where('nocm', $request['asuransipasien']['nocm'])
                    ->update([
                            'notelepon' =>  $request['asuransipasien']['notelpmobile'],
                        ]
                    );
            }


            if ($request['asuransipasien']['id_ap']==''){
                $newId = AsuransiPasien::max('id');
                $newId = $newId + 1;

                $dataAP = new AsuransiPasien();
                $dataAP->id = $newId;
                $dataAP->kdprofile = 0;
                $dataAP->statusenabled = true;
                //                $dataAP->kodeexternal = true;
                //                $dataAP->namaexternal = true;
                //                $dataAP->reportdisplay = true;
                $dataAP->norec = $dataAP->generateNewId();
            }else{
                $dataAP =  AsuransiPasien::where('id',$request['asuransipasien']['id_ap'])->first();
                $newId = $dataAP->id;
                $delPemakaianAsur = PemakaianAsuransi::where('objectasuransipasienfk', $request['asuransipasien']['id_ap'])
                    ->delete();
            }
            $dataAP->alamatlengkap = $request['asuransipasien']['alamat'];
            $dataAP->objecthubunganpesertafk = $request['asuransipasien']['objecthubunganpesertafk'];
            $dataAP->objectjeniskelaminfk = $request['asuransipasien']['objectjeniskelaminfk'];
            $dataAP->kdinstitusiasal = $request['asuransipasien']['kdinstitusiasal']; //count tgl pasien perruanga
//              $dataAP->objectgolonganasuransifk = $request['asuransipasien']['id'];
//              $dataAP->kdlastunitbagian = $request['asuransipasien']['id'];
//              $dataAP->lastunitbagian = $request['asuransipasien']['id'];
            //  $dataAP->nippns = 0;
            //  $dataAP->noasuransihead = 1;
            //  $dataAP->notelpfixed = null;
            $dataAP->notelpmobile = $request['asuransipasien']['notelpmobile'];
            //  $dataAP->tglakhirberlakulast = $request['obj
            //  $dataAP->tglmulaiberlakulast = $request['tglRegistrasi'];
            $dataAP->jenispeserta = $request['asuransipasien']['jenispeserta'];
            $dataAP->kdprovider = $request['asuransipasien']['kdprovider'];
            $dataAP->nmprovider = $request['asuransipasien']['nmprovider'];
            //  $dataAP->objectpegawaifk = null;
            $dataAP->kdpenjaminpasien = $request['asuransipasien']['kdpenjaminpasien'];
            $dataAP->objectkelasdijaminfk = $request['asuransipasien']['objectkelasdijaminfk'];
            $dataAP->namapeserta = $request['asuransipasien']['namapeserta'];
            $dataAP->nikinstitusiasal = $request['asuransipasien']['nikinstitusiasal'];
            $dataAP->noasuransi = $request['asuransipasien']['noasuransi'];
            $dataAP->nocmfk = $request['asuransipasien']['nocmfkpasien'];
            $dataAP->noidentitas = $request['asuransipasien']['noidentitas'];
            $dataAP->qasuransi = $request['asuransipasien']['qasuransi'];
            $dataAP->tgllahir = $request['asuransipasien']['tgllahir'];
            $dataAP->save();


            $idAP  = $newId;


            $dataPA = new PemakaianAsuransi();
            $dataPA->norec = $dataPA->generateNewId();;
            $dataPA->kdprofile = 0;
            $dataPA->statusenabled = true;
            $dataPA->noregistrasifk = $request['pemakaianasuransi']['noregistrasifk'];
            $dataPA->tglregistrasi = $request['pemakaianasuransi']['tglregistrasi'];
            $dataPA->diagnosisfk = $request['pemakaianasuransi']['diagnosisfk'];
            $dataPA->lakalantas = $request['pemakaianasuransi']['lakalantas'];
            $dataPA->nokepesertaan = $request['pemakaianasuransi']['nokepesertaan'];
            $dataPA->norujukan = $request['pemakaianasuransi']['norujukan'];
            $dataPA->nosep = $request['pemakaianasuransi']['nosep'];
            $dataPA->ppkrujukan = $request['asuransipasien']['kdprovider'];
            $dataPA->tglrujukan = $request['pemakaianasuransi']['tglrujukan'];
            $dataPA->objectasuransipasienfk = $idAP;
            $dataPA->objectdiagnosafk = $request['pemakaianasuransi']['objectdiagnosafk'];
            $dataPA->tanggalsep = $request['pemakaianasuransi']['tanggalsep'];
            $dataPA->catatan =$request['pemakaianasuransi']['catatan'];
            $dataPA->lokasilakalantas =$request['pemakaianasuransi']['lokasilaka'];
            $dataPA->penjaminlaka =$request['pemakaianasuransi']['penjaminlaka'];
            $dataPA->asalrujukanfk =$request['pemakaianasuransi']['asalrujukanfk'];

            /*** nu anyar Vclaim 1.1*/
            if(isset($request['pemakaianasuransi']['cob'])){  $dataPA->cob =$request['pemakaianasuransi']['cob']; }
            if(isset($request['pemakaianasuransi']['katarak'])) {  $dataPA->katarak =$request['pemakaianasuransi']['katarak'];}
            if(isset($request['pemakaianasuransi']['keteranganlaka'])) {  $dataPA->keteranganlaka =$request['pemakaianasuransi']['keteranganlaka'];}
            if(isset($request['pemakaianasuransi']['tglkejadian'])) { $dataPA->tglkejadian =$request['pemakaianasuransi']['tglkejadian']; }
            if(isset($request['pemakaianasuransi']['suplesi'])) { $dataPA->suplesi =$request['pemakaianasuransi']['suplesi']; }
            if(isset($request['pemakaianasuransi']['nosepsuplesi'])) {   $dataPA->nosepsuplesi =$request['pemakaianasuransi']['nosepsuplesi']; }
            if(isset($request['pemakaianasuransi']['kdpropinsi'])) {   $dataPA->kdpropinsi =$request['pemakaianasuransi']['kdpropinsi']; }
            if(isset($request['pemakaianasuransi']['namapropinsi'])) {  $dataPA->namapropinsi =$request['pemakaianasuransi']['namapropinsi'];}
            if(isset($request['pemakaianasuransi']['kdkabupaten'])) {  $dataPA->kdkabupaten =$request['pemakaianasuransi']['kdkabupaten'];}
            if(isset($request['pemakaianasuransi']['namakabupaten'])) {   $dataPA->namakabupaten =$request['pemakaianasuransi']['namakabupaten']; }
            if(isset($request['pemakaianasuransi']['kdkecamatan'])) {  $dataPA->kdkecamatan =$request['pemakaianasuransi']['kdkecamatan']; }
            if(isset($request['pemakaianasuransi']['namakecamatan'])) {  $dataPA->namakecamatan =$request['pemakaianasuransi']['namakecamatan'];}
            if(isset($request['pemakaianasuransi']['nosuratskdp'])) {  $dataPA->nosuratskdp =$request['pemakaianasuransi']['nosuratskdp']; }
            if(isset($request['pemakaianasuransi']['kodedpjp'])) {   $dataPA->kodedpjp =$request['pemakaianasuransi']['kodedpjp']; }
            if(isset($request['pemakaianasuransi']['namadpjp'])) {   $dataPA->namadpjp =$request['pemakaianasuransi']['namadpjp']; }
            if(isset($request['pemakaianasuransi']['prolanisprb'])) {   $dataPA->prolanisprb =$request['pemakaianasuransi']['prolanisprb'];}
            /*** end nu anyar */
            $dataPA->save();


            if(isset($request['asuransipasien']['tgllahir']) &&$request['asuransipasien']['tgllahir']!= null ){
                $pasien = Pasien::where('id', $request['asuransipasien']['nocmfkpasien'])->update(
                    [ 'tgllahir' => $request['asuransipasien']['tgllahir'] ,
                        'nobpjs' => $request['asuransipasien']['noasuransi'] ,
                        'noidentitas' => $request['asuransipasien']['noidentitas']

                    ]
                );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Asuransi Pasien";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'PA' => $dataPA,
                'AP' => $dataAP,
                'as' => 'mythicramdan',
            );
        } else {
            $transMessage = "Gagal Simpan Asuransi Pasien";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'PA' => $dataPA,
//                'AP' => $dataAP,
                'as' => 'mythicramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPenjaminByKelompokPasien(Request $request) {
        $data = \DB::table('mapkelompokpasientopenjamin_m as mkp')
            ->join ('kelompokpasien_m as kp','kp.id','=','mkp.objectkelompokpasienfk')
            ->join ('rekanan_m as rk','rk.id','=','mkp.kdpenjaminpasien')
            ->select('rk.id','rk.namarekanan','kp.id as id_kelompokpasien','kp.kelompokpasien')
//            ->where('mkp.objectkelompokpasienfk', $request['kdKelompokPasien'])
            ->where('mkp.statusenabled',true);
//            ->get();

        if(isset($request['kdKelompokPasien']) && $request['kdKelompokPasien']!="" && $request['kdKelompokPasien']!="undefined"){
            $data = $data->where('mkp.objectkelompokpasienfk','=', $request['kdKelompokPasien']);
        };
        $data = $data->get();

        $result = array(
            'rekanan'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getAsuransiPasienByNoCm( Request $request) {
        $data = \DB::table('asuransipasien_m as ap')
            ->join('pasien_m as ps','ps.id','=','ap.nocmfk')
            ->select('ps.nocm','ps.namapasien','ap.nocmfk','ap.id as id_ap'
            );
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','=', $request['nocm']);
        };
        $data=$data->get();
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getPasienByNoreg($norec_pd,$norec_apd){
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTjoin('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('ps.nocm','ps.id as nocmfk','ps.noidentitas','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi','jk.jeniskelamin',
                'ps.tgllahir','alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.nohp as notelepon','ps.objectjeniskelaminfk',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'kps.kelompokpasien','kls.namakelas','apd.objectkelasfk','pd.objectkelompokpasienlastfk','pd.objectrekananfk',
                'rk.namarekanan','pd.objectruanganlastfk','jpl.jenispelayanan','apd.objectasalrujukanfk',
                'ru.kdinternal','jpl.kodeinternal as objectjenispelayananfk','pd.objectpegawaifk','pd.statuspasien',
                'ps.nobpjs','pd.statuspasien',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' else \'false\' end as israwatinap')
            )
            ->where('pd.norec','=',$norec_pd)
            ->where('apd.norec','=',$norec_apd)
            ->get();
//           try {
//                   $umur = $this->hitungUmur($data->tgllahir);
//               } catch (\Exception $e) {
//                   $umur = '-';
//               }
        return $this->respond($data);

//        $result = array(
//            'nocm' => $data->nocm,
//            'nocmfk' => $data->nocmfk,
//            'noidentitas' => $data->noidentitas,
//            'namapasien' => $data->namapasien,
//            'noregistrasi' => $data->noregistrasi,
//            'tglregistrasi' => $data->tglregistrasi,
//            'tgllahir' => $data->tgllahir,
//            'alamatlengkap' => $data->alamatlengkap,
//            'pendidikan' => $data->pendidikan,
//            'pekerjaan' => $data->pekerjaan,
//            'notelepon' => $data->notelepon,
//            'objectjeniskelaminfk' =>$data->objectjeniskelaminfk,
//            'objectruanganfk' => $data->objectruanganfk,
//            'namaruangan' => $data->namaruangan,
//            'norec_apd' => $data->norec_apd,
//            'norec_pd' => $data->norec_pd,
//            'kelompokpasien' => $data->kelompokpasien,
//            'namakelas' => $data->namakelas,
//            'objectkelasfk' =>$data->objectkelasfk,
//            'objectkelompokpasienlastfk' => $data->objectkelompokpasienlastfk,
//            'objectrekananfk' =>$data->objectrekananfk,
//            'namarekanan' => $data->namarekanan,
//            'objectruanganlastfk' => $data->objectruanganlastfk,
//            'kdinternal' => $data->kdinternal,
//            'jenispelayanan' => $data->jenispelayanan,
////            'kodeinternal' => $data->kodeinternal,
//            'objectjenispelayananfk' => $data->objectjenispelayananfk,
//            'umur' => $umur,

//        );
//        return $this->respond($result);
    }

    public function getHistoryPemakaianAsuransi(Request $request) {
        $data = \DB::table('pemakaianasuransi_t as pa')
            ->join ('pasiendaftar_t as pd','pd.norec','=','pa.noregistrasifk')
            ->leftjoin ('asuransipasien_m as apn','apn.id','=','pa.objectasuransipasienfk')
            ->join ('rekanan_m as rek','rek.id','=','apn.kdpenjaminpasien')
            ->leftjoin ('rekanan_m as rek2','rek2.id','=','pd.objectrekananfk')
            ->join ('hubunganpesertaasuransi_m as hpa','hpa.id','=','apn.objecthubunganpesertafk')
            ->join ('kelas_m as kls','kls.id','=','apn.objectkelasdijaminfk')
            ->leftjoin ('diagnosa_m as dg','dg.id','=','pa.objectdiagnosafk')
            ->leftjoin ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->select('rek.namarekanan','pd.noregistrasi','pa.nokepesertaan','apn.namapeserta',
                'apn.noasuransi','apn.tgllahir','apn.noidentitas','apn.alamatlengkap',
                'apn.objecthubunganpesertafk','pa.nosep','pa.tanggalsep',
                'apn.objectkelasdijaminfk','kls.namakelas','pa.catatan','pa.norujukan',
                'apn.kdprovider','apn.nmprovider','pa.tglrujukan','pa.objectdiagnosafk',
                'dg.kddiagnosa','dg.namadiagnosa','pa.lakalantas','pa.ppkrujukan','pa.lokasilakalantas','pa.penjaminlaka',
                'pd.objectkelompokpasienlastfk','pd.objectrekananfk','rek2.namarekanan as namarekananpd','kps.kelompokpasien','apn.kdpenjaminpasien',
                'apn.jenispeserta','hpa.hubunganpeserta','apn.tgllahir')
            ->where('pa.norec', $request['noregistrasi'])
            ->orWhere('pd.noregistrasi', $request['noregistrasi'])
            ->get();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getDaftarPasien( Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('ps.nocm','ps.namapasien','ps.tgldaftar', 'ps.tgllahir',
                'jk.jeniskelamin','ps.noidentitas','alm.alamatlengkap',
                'ps.id as nocmfk','ps.namaayah','ps.notelepon','ps.nohp','ps.tglmeninggal',
                'ps.foto','ps.iskompleks',
//                ,
                DB::raw('case when ps.foto is null then null else \'ada\' end as photo')
            );
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('ps.tgldaftar', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('ps.tgldaftar', '<=', $tgl);
//        }
        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
            $data = $data->where('ps.tgllahir','>=', $request['tglLahir'].' 00:00');
        };
        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
            $data = $data->where('ps.tgllahir','<=', $request['tglLahir'].' 23:59');
        };
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['norm'] .'%');
//                ->OrWhere('ps.namapasien', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['namaPasien'] .'%');
//                ->OrWhere('ps.namapasien', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['alamat']) && $request['alamat']!="" && $request['alamat']!="undefined") {
            $data = $data->where('alm.alamatlengkap', 'ilike', '%'. $request['alamat'] .'%');
        };

        if(isset($request['namaAyah']) && $request['namaAyah']!="" && $request['namaAyah']!="undefined"){
            $data = $data->where('ps.namaayah','ilike', '%'. $request['namaAyah'] .'%');
        };
        $data = $data->where('ps.statusenabled',true);
        // $data=$data->orderBy('ps.namapasien','asc');
        $data=$data->take(50);
        $data=$data->get();
        $data2= [];
        foreach ($data as $item){
            if( $item->foto != null){
                $item->foto = "data:image/jpeg;base64," . base64_encode($item->foto);
            }
            $data2 [] = array(
                'nocm'=> $item->nocm,
                'namapasien'=> $item->namapasien,
                'tgldaftar'=> $item->tgldaftar,
                'jeniskelamin'=> $item->jeniskelamin,
                'noidentitas'=> $item->noidentitas,
                'alamatlengkap'=> $item->alamatlengkap,
                'nocmfk'=> $item->nocmfk,
                'namaayah'=> $item->namaayah,
                'notelepon'=> $item->notelepon,
                'nohp'=> $item->nohp,
                'tglmeninggal'=> $item->tglmeninggal,
                'foto'=> $item->foto,
                'photo'=> $item->photo,
                'iskompleks'=> $item->iskompleks,
            );
        }
        $result = array(
            'daftar' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function cekTglPulangPasien(Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->leftjoin ('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
            ->leftjoin ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin ('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('ps.nocm','ps.id as nocmfk','ps.namapasien','pd.tglpulang')
            ->where('ps.nocm', $request['noCm'])
            ->wherein('ru.objectdepartemenfk',[16,35,17])
            ->get();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getAntrianPasien(Request $request)
    {
        if ($request['objectruanganlastfk']!="" ) {
            $noreg =  $request['noregistrasi'];
            $ruanganLast = $request['objectruanganlastfk'];
            $data = DB::select(DB::raw("
             select apd.norec as norec_apd,  ps.nocm,  ps.id as nocmfk,  ps.namapasien,  pd.noregistrasi,  apd.objectruanganfk, 
             ru.namaruangan,  apd.tglregistrasi,  kls.namakelas,  apd.objectruanganasalfk,
             row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum
             from antrianpasiendiperiksa_t as apd
             inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk
             left join ruangan_m as ru on ru.id = apd.objectruanganfk
             inner join pasien_m as ps on ps.id = pd.nocmfk
             inner join kelas_m as kls on kls.id = apd.objectkelasfk
             where pd.noregistrasi = '$noreg' and apd.objectruanganfk = '$ruanganLast'"));
//                \DB::table('antrianpasiendiperiksa_t as apd')
//                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
//                ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
//                ->join('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
//                ->select('apd.norec as norec_apd', 'ps.nocm', 'ps.id as nocmfk', 'ps.namapasien', 'pd.noregistrasi', 'apd.objectruanganfk',
//                    'ru.namaruangan', 'apd.tglregistrasi', 'kls.namakelas', 'apd.objectruanganasalfk')
//                ->where('pd.noregistrasi', $request['noregistrasi'])
//                ->where('apd.objectruanganfk', $request['objectruanganlastfk'])
////            ->whereNull('apd.objectruanganasalfk')
//                ->get();
        }else{
            $data = \DB::table('antrianpasiendiperiksa_t as apd')
                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->join('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
                ->select('apd.norec as norec_apd', 'ps.nocm', 'ps.id as nocmfk', 'ps.namapasien', 'pd.noregistrasi', 'apd.objectruanganfk',
                    'ru.namaruangan', 'apd.tglregistrasi', 'kls.namakelas', 'apd.objectruanganasalfk')
                ->where('pd.noregistrasi', $request['noregistrasi'])
//                ->where('apd.objectruanganfk', $request['objectruanganlastfk'])
                ->whereNull('apd.objectruanganasalfk')
                ->get();
        }


        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getDataComboRegLama(Request $request)
    {
        $dataLogin = $request->all();


        $dataDokter = \DB::table('pegawai_m as ru')
            ->select('ru.id','ru.id as dokterId','ru.namalengkap as namaDokter','ru.id as value','ru.namalengkap as namaLengkap')
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('ru.namalengkap')
            ->get();


        $result = array(
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,

            'message' => 'niaramdanegie',
        );

        return $this->respond($result);
    }


    public function getComboPindahPulang(Request $request)
    {
        $dataLogin = $request->all();
        $statusKeluar = \DB::table('statuskeluar_m as st')
            ->select('st.id','st.statuskeluar')
            ->where('st.statusenabled', true)
            ->orderBy('st.statuskeluar')
            ->get();
        $kondisiKeluar = \DB::table('kondisipasien_m as kp')
            ->select('kp.id','kp.kondisipasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kondisipasien')
            ->get();
        $kelas = \DB::table('kelas_m as kls')
            ->select('kls.id','kls.namakelas')
            ->where('kls.statusenabled', true)
            ->orderBy('kls.namakelas')
            ->get();
        $kamar = \DB::table('kamar_m as kmr')
            ->select('kmr.id', 'kmr.namakamar')
            ->where('kmr.statusenabled', true)
            ->orderBy('kmr.namakamar')
            ->get();
        $ruanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', [16,35,17])
            ->orderBy('ru.namaruangan')
            ->get();
        $statusPulang = \DB::table('statuspulang_m as sp')
            ->select('sp.id', 'sp.statuspulang')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.statuspulang')
            ->get();
        $hubunganKeluarga = \DB::table('hubungankeluarga_m as sp')
            ->select('sp.id', 'sp.hubungankeluarga')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.hubungankeluarga')
            ->get();
        $penyebabKematian = \DB::table('penyebabkematian_m as sp')
            ->select('sp.id', 'sp.penyebabkematian')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.penyebabkematian')
            ->get();
        $pindah = \DB::table('statuspulang_m as sp')
            ->select('sp.id', 'sp.statuspulang')
            ->where('sp.statusenabled', true)
            ->where('sp.id',2)
            ->orderBy('sp.statuspulang')
            ->get();
        $result = array(
            'statuskeluar' => $statusKeluar,
            'kondisipasien' =>$kondisiKeluar,
            'kelas' =>$kelas,
            'ruanganinap' =>$ruanganInap,
            'kamar' =>$kamar,
            'statuspulang' =>$statusPulang,
            'hubungankeluarga'=> $hubunganKeluarga,
            'penyebabkematian' => $penyebabKematian,
            'datalogin' => $dataLogin,
            'pindah' => $pindah,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function savePindahPasien(Request $request) {
        $detLogin =$request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];

        DB::beginTransaction();
        try{
            //##Update Pasiendaftar##
            if ($r_NewPD['norec_pd'] != 'null' || $r_NewPD['norec_pd'] != 'undefined') {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objectruanganlastfk' => $r_NewPD['objectruanganlastfk'],
                            'objectkelasfk' => $r_NewPD['objectkelasfk'],
                        ]
                    );
            }
            if ($r_NewAPD['norec_apd'] != 'null' || $r_NewAPD['norec_apd'] != 'undefined') {
                $updateAPD= AntrianPasienDiperiksa::where('norec', $r_NewAPD['norec_apd'])
                    ->update([
                            'tglkeluar' => $r_NewAPD['tglmasuk'],
                        ]
                    );


                $ruangasal = DB::select(DB::raw("select * from antrianpasiendiperiksa_t 
                         where noregistrasifk=:noregistrasifk and objectruanganfk=:objectruanganasalfk;" ),
                    array(
                        'noregistrasifk' => $r_NewPD['norec_pd'],
                        'objectruanganasalfk'=>$r_NewPD['objectruanganasalfk'],
                    )
                );

                //update statusbed jadi Kosong
                foreach ($ruangasal as $Hit){
                    TempatTidur::where('id',$Hit->nobed)->update(['objectstatusbedfk'=>2]);
                }
            }

            if ($request['strukorder']['norecorder'] != ''){
                $updateSO= StrukOrder::where('norec', $request['strukorder']['norecorder'])
                    ->update([
                            'statusorder' => 1,
                            'tglvalidasi' => $tglAyeuna
                        ]
                    );
            }

            $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$r_NewPD['objectruanganlastfk'])
                ->where('tglregistrasi', '>=', $r_NewPD['tglregistrasidate'].' 00:00')
                ->where('tglregistrasi', '<=', $r_NewPD['tglregistrasidate'].' 23:59')
                ->count('norec');
            $noAntrian = $countNoAntrian + 1;
            //##Save Antroan Pasien Diperiksa##
//        try{
            $pd = PasienDaftar::where('norec',$r_NewPD['norec_pd'])->first();
            $dataAPD =new AntrianPasienDiperiksa;
            $dataAPD->norec = $dataAPD->generateNewId();
            $dataAPD->kdprofile = 1;
            $dataAPD->statusenabled = true;
            $dataAPD->objectruanganfk = $r_NewAPD['objectruanganlastfk'];
            $dataAPD->objectasalrujukanfk =  $r_NewAPD['objectasalrujukanfk'];
            $dataAPD->objectkamarfk = $r_NewAPD['objectkamarfk'];
            $dataAPD->objectkasuspenyakitfk = null;
            $dataAPD->objectkelasfk = $r_NewAPD['objectkelasfk'];
            $dataAPD->noantrian = $noAntrian; //count tgl pasien perruanga
            $dataAPD->nobed = $r_NewAPD['nobed'];
//          $dataAPD->nomasuk = '';
            $dataAPD->noregistrasifk = $r_NewPD['norec_pd'];
//          $dataAPD->objectpegawaifk = $r_NewAPD['objectpegawaifk'];
//          $dataAPD->prefixnoantrian = null;
            $dataAPD->statusantrian = 0;
            $dataAPD->statuskunjungan =$r_NewPD['statuspasien'];
            $dataAPD->statuspasien = 1;
//          $dataAPD->statuspenyakit =null;
//          $dataAPD->objectstrukorderfk = null;
//          $dataAPD->objectstrukreturfk = null;
            $dataAPD->tglregistrasi =  $pd->tglregistrasi;//$r_NewAPD['tglregistrasi'];
//          $dataAPD->tgldipanggildokter = null;
//          $dataAPD->tgldipanggilsuster = null;
            $dataAPD->objectruanganasalfk = $r_NewPD['objectruanganasalfk'];
            $dataAPD->tglkeluar = null;
            $dataAPD->tglmasuk =$r_NewAPD['tglmasuk'];
            $dataAPD->israwatgabung = $r_NewAPD['israwatgabung'];

            $dataAPD->save();

            //update statusbed jadi Isi
            TempatTidur::where('id',$r_NewAPD['nobed'])->update(['objectstatusbedfk'=>1]);

////            $transStatus = 'true';
////        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "simpan Antrian Pasien";
//        }
//      try{
            //##Save Registrasi Pel Pasien##
            $dataRPP = new RegistrasiPelayananPasien();
            $dataRPP->norec = $dataRPP->generateNewId();;
            $dataRPP->kdprofile = 1;
            $dataRPP->statusenabled = true;
            $dataRPP->objectasalrujukanfk = $r_NewAPD['objectasalrujukanfk'];
//          $dataRPP->objecthasiltriasefk =null;
            $dataRPP->israwatgabung = $r_NewAPD['israwatgabung'];;
            $dataRPP->objectkamarfk =$r_NewAPD['objectkamarfk'];
//          $dataRPP->objectkasuspenyakitfk = null;
//          $dataRPP->kddokter = null;
//          $dataRPP->kddokterperiksanext =  null;
            $dataRPP->objectkelasfk = $r_NewAPD['objectkelasfk'];
            $dataRPP->objectkelaskamarfk = $r_NewAPD['objectkelasfk'];
//          $dataRPP->objectkelaskamarrencanafk =null;
//          $dataRPP->objectkelaskamartujuanfk =null;
//          $dataRPP->objectkelasrencanafk = null;
//          $dataRPP->objectkelastujuanfk = null;
            $dataRPP->kdpenjaminpasien = 0;
//          $dataRPP->objectkeadaanumumfk = null;
            $dataRPP->objectkelompokpasienfk = $r_NewPD['objectkelompokpasienlastfk'];
//          $dataRPP->keteranganlainnyaperiksanext = null;
            $dataRPP->keteranganlainnyarencana = $r_NewAPD['keteranganpindah'];
//          $dataRPP->kodenomorbuktiperjanjian = null;
//          $dataRPP->objectkondisipasienfk = null;
//          $dataRPP->namatempattujuan =null;
//          $dataRPP->noantrian = null;
            $dataRPP->noantrianbydokter = 0;
//          $dataRPP->nobed = null;
//          $dataRPP->nobedtujuan =  null;
            $dataRPP->nocmfk = $r_NewPD['nocmfk'];
            $dataRPP->noregistrasifk = $r_NewPD['norec_pd'];
//          $dataRPP->prefixnoantrian =  '1';
            $dataRPP->objectruanganasalfk = $r_NewAPD['objectruanganasalfk'];
            $dataRPP->objectruanganfk = $r_NewAPD['objectruanganlastfk'];
//          $dataRPP->objectruanganperiksanextfk = $r_NewAPD['objectruanganfk'];
//          $dataRPP->objectruanganrencanafk =  null;
//          $dataRPP->objectruangantujuanfk =  null;
            $dataRPP->objectstatuskeluarfk = $r_NewPD['objectstatuskeluarfk'];
//            $dataRPP->objectstatuskeluarrencanafk =  $r_NewPD['objectstatuskeluarfk'];
//          $dataRPP->statuspasien =  0;
            $dataRPP->objecttempattidurfk = $r_NewAPD['nobed'];
//          $dataRPP->tglkeluar = null;
//          $dataRPP->tglkeluarrencana = null;
            $dataRPP->tglmasuk = $r_NewAPD['tglmasuk'];
//          $dataRPP->tglperiksanext = null;
            $dataRPP->tglpindah = $r_NewAPD['tglmasuk'];
//          $dataRPP->objecttransportasifk = null;
//          $dataRPP->objectdetailkamarfk = null;
            $dataRPP->save();
            $dataNorecRPP=$dataRPP->norec;

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Registrasi Pelayanan Pasien";
        }

        if ($transStatus == 'true') {
            DB::commit();
            $transMessage = 'SUKSES';
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'dataPD' => $dataPD,
                'dataAPD' => $dataAPD,
                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "GAGAL";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'dataPD' => $dataPD,
                'dataAPD' => $dataAPD,
                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function savePulangPasien(Request $request) {
        $detLogin =$request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];
        DB::beginTransaction();
        //##Update Pasiendaftar##
        try{

            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined' && $r_NewPD['objectstatuskeluarfk']==5) {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objecthubungankeluargaambilpasienfk' => $r_NewPD['objecthubungankeluargaambilpasienfk'],
                            'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                            'namalengkapambilpasien' => $r_NewPD['namalengkapambilpasien'],
                            'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'objectstatuspulangfk' => $r_NewPD['objectstatuspulangfk'],
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                            'tglpulang' => $r_NewPD['tglpulang'],
                        ]
                    );
            }
            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined' && $r_NewPD['objectstatuskeluarfk']==5 && $r_NewPD['objectpenyebabkematianfk'] ==4) {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objecthubungankeluargaambilpasienfk' => $r_NewPD['objecthubungankeluargaambilpasienfk'],
                            'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                            'namalengkapambilpasien' => $r_NewPD['namalengkapambilpasien'],
                            'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'objectstatuspulangfk' => $r_NewPD['objectstatuspulangfk'],
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                            'tglpulang' => $r_NewPD['tglpulang'],
                            'keteranganpenyebabkematian' => $r_NewPD['keterangankematian'],
                        ]
                    );
            }
            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined' && $r_NewPD['objectstatuskeluarfk']!=5) {
                $updatePD= PasienDaftar::where('noregistrasi', $r_NewPD['noregistrasi'])
                    ->update([
                            'objecthubungankeluargaambilpasienfk' => $r_NewPD['objecthubungankeluargaambilpasienfk'],
                            'objectkondisipasienfk' => $r_NewPD['objectkondisipasienfk'],
                            'namalengkapambilpasien' => $r_NewPD['namalengkapambilpasien'],
//                        'objectpenyebabkematianfk' => $r_NewPD['objectpenyebabkematianfk'],
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'objectstatuspulangfk' => $r_NewPD['objectstatuspulangfk'],
//                        'tglmeninggal' => $r_NewPD['tglmeninggal'],
                            'tglpulang' => $r_NewPD['tglpulang'],
                        ]
                    );
            }
            if ($r_NewPD['nocmfk'] != 'undefined' && $r_NewPD['objectstatuskeluarfk']== 5) {
                $updatePS= Pasien::where('id', $r_NewPD['nocmfk'])
                    ->update([
                            'tglmeninggal' => $r_NewPD['tglmeninggal'],
                        ]
                    );

            }
            if ($r_NewAPD['norec_apd'] != 'undefined') {
                $updateAPD= AntrianPasienDiperiksa::where('norec', $r_NewAPD['norec_apd'])
                    ->update([
                            'tglkeluar' => $r_NewPD['tglpulang'],
                        ]
                    );

                $ruangasal = DB::select(DB::raw("select * from antrianpasiendiperiksa_t 
                         where norec=:norec and objectruanganfk=:objectruanganasalfk;" ),
                    array(
                        'norec' => $r_NewAPD['norec_apd'],
                        'objectruanganasalfk'=>$r_NewAPD['objectruanganlastfk'],
                    )
                );

                //update statusbed jadi Kosong
                foreach ($ruangasal as $Hit){
                    TempatTidur::where('id',$Hit->nobed)->update(['objectstatusbedfk'=>2]);
                }

            }

            if ($request['strukorder']['norecorder'] != ''){
                $updateSO= StrukOrder::where('norec', $request['strukorder']['norecorder'])
                    ->update([
                            'statusorder' => 4,
                            'tglvalidasi' => $tglAyeuna
                        ]
                    );
            }

            if ( $r_NewPD['norec_pd'] != 'undefined' && $r_NewPD['noregistrasi']!= 'undefined') {
                // $updateRPP = \DB::table('registrasipelayananpasien_t')
                //     ->select('noregistrasifk','objectruanganfk','tglkeluar')
                //     ->where('objectruanganfk', $r_NewAPD['objectruanganlastfk'])
                //     ->where('noregistrasifk', $r_NewPD['norec_pd'])
                //     ->update([
                //             'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                //             'tglkeluar' => $r_NewPD['tglpulang'],
                //             'tglkeluarrencana' => $r_NewPD['tglpulang'],
                //         ]
                //     );
                $updateRPP =RegistrasiPelayananPasien::where('objectruanganfk', $r_NewAPD['objectruanganlastfk'])
                    ->where('noregistrasifk', $r_NewPD['norec_pd'])
                    ->update([
                            'objectstatuskeluarfk' => $r_NewPD['objectstatuskeluarfk'],
                            'tglkeluar' => $r_NewPD['tglpulang'],
                            'tglkeluarrencana' => $r_NewPD['tglpulang'],
                        ]
                    );

            }
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
//                'dataPD' => $dataPD,
//                'dataAPD' => $dataAPD,
//                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "GAGAL";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'dataPD' => $dataPD,
//                'dataAPD' => $dataAPD,
//                'dataRPP'=> $dataRPP,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function getPindahPasienByNoreg($norec_pd,$norec_apd){
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTjoin('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('ps.nocm','ps.id as nocmfk','ps.noidentitas','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi','jk.jeniskelamin',
                'ps.tgllahir','alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.notelepon','ps.objectjeniskelaminfk',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'kps.kelompokpasien','kls.namakelas','apd.objectkelasfk','pd.objectkelompokpasienlastfk','pd.objectrekananfk',
                'rk.namarekanan','pd.objectruanganlastfk','jpl.jenispelayanan','apd.objectasalrujukanfk',
                'ru.kdinternal','jpl.kodeinternal as objectjenispelayananfk','pd.objectpegawaifk','pd.statuspasien','pd.objectruanganlastfk',
                'ps.qpasien as id_ibu',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' else \'false\' end as israwatinap')
            )
            ->where('pd.norec','=',$norec_pd)
            ->where('apd.norec','=',$norec_apd)
//            ->where('pd.objectruanganlastfk',$ruanganlast)
//            ->where('apd.objectruanganfk',$ruanganlast)
            ->whereNull('pd.tglpulang')
            ->get();

        return $this->respond($data);
    }


    public function getRuanganLast(Request $request)
    {
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as st')
            ->select('st.norec','st.objectruanganlastfk')
            ->where('st.norec', $request['norec_pd'])

            ->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function ubahTanggalRegis(Request $request) {
        DB::beginTransaction();
        //##Update Pasiendaftar##
        try{
            $dataRPP= RegistrasiPelayananPasien::where('noregistrasifk', $request['norec_pd'])->count();
            if ($request['tglregistrasi'] != '' ) {
                if ($dataRPP>0){
                    $updatePD= PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                        ->update([
                                'tglregistrasi' => $request['tglregistrasi']

                            ]
                        );
                    $updateAPDs= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                        ->update([
                                'tglregistrasi' => $request['tglregistrasi'],
                            ]
                        );
                }else{
                    $updatePD= PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                        ->update([
                                'tglregistrasi' => $request['tglregistrasi'],
                                'tglpulang' => $request['tglregistrasi']
                            ]
                        );
                    $updateAPDs= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                        ->update([
                                'tglregistrasi' => $request['tglregistrasi']
                            ]
                        );
                }
                $updatePD= PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                    ->update([
                            'tglregistrasi' => $request['tglregistrasi']
                        ]
                    );
                $updateAPDs= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->update([
                            'tglregistrasi' => $request['tglregistrasi']
                        ]
                    );
            }

            if ($request['tglkeluar'] != ''&& $request['tglmasuk'] != '') {
                $updateAPD= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->update([
                            'tglkeluar' => $request['tglkeluar'],
                            'tglmasuk' => $request['tglmasuk']
                        ]
                    );
                if ($dataRPP >0) {
                    $updateRPP = \DB::table('registrasipelayananpasien_t')
                        ->select('noregistrasifk','objectruanganfk','rpp.tglkeluar')
                        ->where('objectruanganfk', $request['ruanganasal'])
                        ->where('noregistrasifk', $request['norec_pd'])
                        ->update([
                                'tglkeluar' => $request['tglkeluar'],
                                'tglmasuk' => $request['tglmasuk']
                            ]
                        );
                }

            }
            if($request['tglkeluar'] == ''&& $request['tglmasuk'] != ''){
                $updateAssPD= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->update([
                            'tglmasuk' => $request['tglmasuk']

                        ]
                    );
                if ($dataRPP>0) {
                    $updatessRPP = \DB::table('registrasipelayananpasien_t')
                        ->select('noregistrasifk','objectruanganfk','tglkeluar')
                        ->where('objectruanganfk', $request['ruanganasal'])
                        ->where('noregistrasifk', $request['norec_pd'])
                        ->update([
                                'tglmasuk' => $request['tglmasuk']
                            ]
                        );
                }
            }
            if($request['tglkeluar'] != ''&& $request['tglmasuk'] == ''){
                $updatseAPD= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                    ->update([
                            'tglkeluar' => $request['tglkeluar']
                        ]
                    );
                if ($dataRPP  >0) {
                    $updatsseRPP = \DB::table('registrasipelayananpasien_t')
                        ->select('noregistrasifk','objectruanganfk','tglkeluar')
                        ->where('objectruanganfk', $request['ruanganasal'])
                        ->where('noregistrasifk', $request['norec_pd'])
                        ->update([
                                'tglkeluar' => $request['tglkeluar']
                            ]
                        );
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan  Pasien";
        }

        if ($transStatus == 'true') {
            DB::commit();
            $transMessage = 'SUKSES';
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPD' => $dataRPP,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "GAGAL";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getNoCmIbu(Request $request)
    {
        $dataLogin = $request->all();
        $data = \DB::table('pasien_m as ps')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJoin('agama_m as agm','agm.id','=','ps.objectagamafk')
            ->leftJoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftJoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->select('ps.id','ps.nocm','ps.namapasien','ps.objectjeniskelaminfk','jk.jeniskelamin','ps.objectagamafk','agm.agama',
                'ps.objectpendidikanfk','pdd.pendidikan','alm.alamatlengkap','ps.namaayah','ps.namaibu','ps.namakeluarga',
                'ps.notelepon','ps.nohp','ps.objectpekerjaanfk','pk.pekerjaan','ps.tgllahir','ps.tempatlahir','ps.namasuamiistri',
                'alm.kodepos')
            ->where('ps.statusenabled',true)
            ->where('ps.nocm', $request['noCm'])
            ->get();

        $result = array(
            'data' => $data,
            'datalogin' => $dataLogin,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getDesaKelurahanPart(Request $request)
    {
        $req = $request->all();
        $Desa = \DB::table('desakelurahan_m as ru')
            ->select('ru.id','ru.namadesakelurahan as namaDesaKelurahan','ru.kodepos as kodePos')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namadesakelurahan');

        if(isset($req['namadesakelurahan']) &&
            $req['namadesakelurahan']!="" &&
            $req['namadesakelurahan']!="undefined"){
            $Desa = $Desa->where('ru.namadesakelurahan','ilike','%'. $req['namadesakelurahan'] .'%' );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $Desa = $Desa
                ->where('ru.namadesakelurahan','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

        $Desa=$Desa->take(10);
        $Desa=$Desa->get();
        return $this->respond($Desa);
    }
    public function getKecamatanPart(Request $request)
    {
        $req = $request->all();
        $kecamatan = \DB::table('kecamatan_m as ru')
//            ->join('desakelurahan_m as dk','ru.id','=','dk.objectkecamatanfk')
            ->select('ru.id','ru.namakecamatan as namaKecamatan')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namakecamatan');

        if (isset($req['namakecamatan']) &&
            $req['namakecamatan'] != "" &&
            $req['namakecamatan'] != "undefined") {
            $kecamatan = $kecamatan->where('ru.namakecamatan ',  $req['namakecamatan']);
        };
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $kecamatan = $kecamatan
                ->where('ru.namakecamatan', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        }

        $kecamatan = $kecamatan->take(10);
        $kecamatan = $kecamatan->get();
        return $this->respond($kecamatan);
    }
    public function getKotaKabupatenPart(Request $request)
    {
        $req = $request->all();
        $KotaKabupaten = \DB::table('kotakabupaten_m as ru')
            ->select('ru.id', 'ru.namakotakabupaten as namaKotaKabupaten')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namakotakabupaten');

        if (isset($req['namakotakabupaten']) &&
            $req['namakotakabupaten'] != "" &&
            $req['namakotakabupaten'] != "undefined") {
            $KotaKabupaten = $KotaKabupaten->where('ru.namakotakabupaten', 'ilike', '%' . $req['namakotakabupaten'] . '%');
        };
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $KotaKabupaten = $KotaKabupaten
                ->where('ru.namakotakabupaten', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        }

        $KotaKabupaten = $KotaKabupaten->take(10);
        $KotaKabupaten = $KotaKabupaten->get();
        return $this->respond($KotaKabupaten);
    }
    public function getPropinsiPart(Request $request)
    {
        $req = $request->all();
        $Propinsi = \DB::table('propinsi_m as ru')
            ->select('ru.id', 'ru.namapropinsi as namaPropinsi')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namapropinsi');

        if (isset($req['namapropinsi']) &&
            $req['namapropinsi'] != "" &&
            $req['namapropinsi'] != "undefined") {
            $Propinsi = $Propinsi->where('ru.namapropinsi', 'ilike', '%' . $req['namapropinsi'] . '%');
        };
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $Propinsi = $Propinsi
                ->where('ru.namapropinsi', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        }

        $Propinsi = $Propinsi->take(10);
        $Propinsi = $Propinsi->get();
        return $this->respond($Propinsi);
    }
    public function getAlamatByKodePos(Request $request)
    {
        $dataLogin = $request->all();
        $data = \DB::table('desakelurahan_m as dk')
            ->Join('kecamatan_m as kcm','kcm.id','=','dk.objectkecamatanfk')
            ->Join('kotakabupaten_m as kk','kk.id','=','dk.objectkotakabupatenfk')
            ->Join('propinsi_m as pp','pp.id','=','dk.objectpropinsifk')
            ->select(DB::raw("dk.id,dk.id as objectdesakelurahanfk,UPPER(dk.namadesakelurahan) as namadesakelurahan,dk.kodepos,
			                 dk.objectkecamatanfk,dk.objectkotakabupatenfk,dk.objectpropinsifk,
				             UPPER(kcm.namakecamatan) as namakecamatan,UPPER(kk.namakotakabupaten) as namakotakabupaten,
				             UPPER(pp.namapropinsi) as namapropinsi"))
            ->where('dk.statusenabled', true)
            ->where('dk.kodepos', $request['kodePos'])
            ->get();

        $result = array(
            'data' => $data,
            'datalogin' => $dataLogin,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getNegaraPart(Request $request)
    {
        $req = $request->all();
        $neg = \DB::table('negara_m as ru')
            ->select('ru.id', 'ru.namanegara')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namanegara');

        if (isset($req['namanegara']) &&
            $req['namanegara'] != "" &&
            $req['namanegara'] != "undefined") {
            $neg = $neg->where('ru.namanegara', 'ilike', '%' . $req['namanegara'] . '%');
        };
        if (isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value'] != "" &&
            $req['filter']['filters'][0]['value'] != "undefined") {
            $neg = $neg
                ->where('ru.namanegara', 'ilike', '%' . $req['filter']['filters'][0]['value'] . '%');
        }

        $neg = $neg->take(10);
        $neg = $neg->get();
        return $this->respond($neg);
    }

    public function savePasienBayi(Request $request) {
        $detLogin =$request->all();

        DB::beginTransaction();
        $NewPasien=$request['pasien'];
        $NewAlamat=$request['alamat'];
        try{
            $newId2=20000000;
            $newId = Pasien::where('id','>',(float)20000000-1)
                ->where('id','<',(float)25000000)
                ->count('id');
            $newId = (float)$newId2 + (float)$newId;

            $runningNumber = RunningNumber::where('id',13535)->first();
            $extension = $runningNumber->reset . $runningNumber->extention;
            $noCmTerakhir = $runningNumber->nomer_terbaru +1;
            $noCm = $extension . $noCmTerakhir;

            $dataPS = new Pasien();
            $dataPS->id = $newId;
            $dataPS->kdprofile = 0;
            $dataPS->statusenabled = true;
            $dataPS->kodeexternal = $newId;
            $dataPS->norec =  $dataPS->generateNewId();
            $dataPS->nocm = $noCm;//$NewPasien['nocm'];//$noCm;
            $dataPS->namaexternal = $NewPasien['namapasien'];
            $dataPS->reportdisplay =  $NewPasien['namapasien'];
            $dataPS->objectagamafk = $NewPasien['objectagamafk'];
            //            $dataPS->objectgolongandarahfk = $request['xxxx']['xxxxx'];
            $dataPS->objectjeniskelaminfk = $NewPasien['objectjeniskelaminfk'];
            $dataPS->namapasien = $NewPasien['namapasien'];
            $dataPS->objectpekerjaanfk = $NewPasien['objectpekerjaanfk'];
            $dataPS->objectpendidikanfk = $NewPasien['objectpendidikanfk'];
            $dataPS->qpasien =  $NewPasien['pasienIbu']['id'];//id IBU BAYI
            $dataPS->objectstatusperkawinanfk = $NewPasien['objectstatusperkawinanfk'];
            $dataPS->tgldaftar =  date('Y-m-d H:i:s');
            $dataPS->tgllahir = $NewPasien['tgllahir'];// $dt;
            //            $dataPS->objecttitlefk = $request['xxxx']['xxxxx'];
            $dataPS->namaibu = $NewPasien['namaibu'];
            $dataPS->notelepon = $NewPasien['notelepon'];
            $dataPS->noidentitas = $NewPasien['noidentitas'];
            //            $dataPS->tglmeninggal = $request['xxxx']['xxxxx'];
            //            $dataPS->noaditional = $NewPasien['pasienIbu']['id'];
            //            $dataPS->paspor = $request['xxxx']['xxxxx'];
            $dataPS->objectkebangsaanfk = $NewPasien['objectkebangsaanfk'];
            $dataPS->objectnegarafk = $NewPasien['objectnegarafk'];
            //            $dataPS->namadepan = $request['xxxx']['xxxxx'];
            //            $dataPS->namabelakang = $request['xxxx']['xxxxx'];
            //            $dataPS->dokumenrekammedis = $request['xxxx']['xxxxx'];
            $dataPS->namaayah = $NewPasien['namaayah'];
            $dataPS->namasuamiistri = $NewPasien['namasuamiistri'];
            $dataPS->noasuransilain = $NewPasien['noasuransilain'];
            $dataPS->nobpjs = $NewPasien['nobpjs'];
            $dataPS->nohp = $NewPasien['nohp'];
            $dataPS->tempatlahir = $NewPasien['tempatlahir'];
            $dataPS->jamlahir = $NewPasien['jamlahir'];
            $dataPS->save();
            $dataNoCMFk =$dataPS->id;
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Pasien Baru";
        }
        try{
            $updateRN= RunningNumber::where('id', '13535')
                ->update([
                        'nomer_terbaru' => $noCmTerakhir

                    ]
                );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "update Running Number";
        }
//        try{
//        $updateGen= FlagGenerateNoCm::where('nocmgenerate', $NewPasien['nocm'])
//            ->update([
//                    'status' => '1'
//                ]
//            );
//        $transStatus = 'true';
//        } catch (\Exception $e) {
//                $transStatus = 'false';
//                $transMessage = "update Table Genrate No CM";
//        }

        try{
            $newId3=20000000;
            $newIdAl = Alamat::where('id','>',(float)20000000-1)
                ->where('id','<',(float)25000000)
                ->count('id');
            $newIdAl = (float)$newId3 + (float)$newIdAl;
            $newIDAlamat= $newIdAl;
            $dataAL = new Alamat();
            $dataAL->id = $newIDAlamat;
            $dataAL->kdprofile = 0;
            $dataAL->statusenabled = true;
            $dataAL->kodeexternal = $newIDAlamat;
            //            $dataAL->namaexternal = $request['alamatLengkap'];
            $dataAL->norec = $dataAL->generateNewId();
            //            $dataAL->reportdisplay = $request['alamatLengkap'];
            //            $dataAL->alamatemail = $request['xxxx']['xxxxx'];
            $dataAL->alamatlengkap = $NewAlamat['alamatlengkap'];
            //            $dataAL->blackberry = $request['xxxx']['xxxxx'];
            $dataAL->objectdesakelurahanfk = $NewAlamat['objectdesakelurahanfk'];
            //            $dataAL->facebook = $request['xxxx']['xxxxx'];
            //            $dataAL->faksimile1 = $request['xxxx']['xxxxx'];
            //            $dataAL->faksimile2 =$request['xxxx']['xxxxx'];
            //            $dataAL->fixedphone1 =$request['xxxx']['xxxxx'];
            //            $dataAL->fixedphone2 =$request['xxxx']['xxxxx'];
            $dataAL->objecthubungankeluargafk = 7;
            //            $dataAL->isbillingaddress = $request['xxxx']['xxxxx'];
            //            $dataAL->isprimaryaddress = $request['xxxx']['xxxxx'];
            //            $dataAL->isshippingaddress = $request['xxxx']['xxxxx'];
            $dataAL->objectjenisalamatfk = 1;
            $dataAL->kdalamat = $newIDAlamat;
            $dataAL->objectkecamatanfk = $NewAlamat['objectkecamatanfk'];
            //            $dataAL->keteranganlainnya = $request['alamatLengkap'];
            $dataAL->kodepos = $NewAlamat['kodepos'];
            $dataAL->objectkotakabupatenfk = $NewAlamat['objectkotakabupatenfk'];
            //            $dataAL->line =$request['xxxx']['xxxxx'];
            //            $dataAL->mobilephone1 = $request['xxxx']['xxxxx'];
            //            $dataAL->mobilephone2 = $request['xxxx']['xxxxx'];
            $dataAL->namadesakelurahan = $NewAlamat['namadesakelurahan'];
            $dataAL->namakecamatan = $NewAlamat['namakecamatan'];
            $dataAL->namakotakabupaten = $NewAlamat['namakotakabupaten'];
            //            $dataAL->namatempatgedung = $request['xxxx']['xxxxx'];
            $dataAL->objectnegarafk = $NewAlamat['objectnegarafk'];
            $dataAL->nocmfk = $dataNoCMFk;
            $dataAL->objectpegawaifk = $detLogin['userData']['id'];
            $dataAL->objectpropinsifk = $NewAlamat['objectpropinsifk'];
            //            $dataAL->objectrekananfk = $request['xxxx']['xxxxx'];
            //            $dataAL->rtrw = $request['xxxx']['xxxxx'];
            //            $dataAL->twitter = $request['xxxx']['xxxxx'];
            //            $dataAL->website = $request['xxxx']['xxxxx'];
            //            $dataAL->whatsapp = $request['xxxx']['xxxxx'];
            //            $dataAL->yahoomessenger = $request['xxxx']['xxxxx'];
            $dataAL->kecamatan = $NewAlamat['namakecamatan'];
            $dataAL->kotakabupaten = $NewAlamat['namakotakabupaten'];
            //            $dataAL->penanggungjawab_norec = $request['xxxx']['xxxxx'];
            $dataAL->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Pasien Baru";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPasien' => $dataPS,
                'dataAlamat' => $dataAL,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'dataPasien' =>$dataPS,
                'dataAlamat' => $dataAL,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($noCmTerakhir );
    }

    public function saveGenerateNocm(Request $request) {
        $detLogin =$request->all();
        DB::beginTransaction();
//        $data = DB::select(DB::raw("select EXTRACT(HOUR from AGE(NOW(), tgldaftar)) as daterange,
//            * from generate_nocm where status=:status;"),
//            array(
//                'status' =>'0',
//            )
//        );
//        if(count($data)>0){
//            if($data[0]->daterange>6) {
//                $dataAya = $data[0]->nocmgenerate;
//                $transStatus = 'netral';
//            }
//        }else{
//            try{
        $runningNumber = DB::selectOne(
            DB::raw('select nomer_terbaru from running_number where id=13535'));
        $awalan ="01";
        $noCmTerakhir = $runningNumber->nomer_terbaru +1;
        $noCm = $awalan.$noCmTerakhir;

        $updateRN= RunningNumber::where('id', '13535')
            ->update([
                    'nomer_terbaru' => $noCmTerakhir
                ]
            );

        $newId = FlagGenerateNoCm::max('id');
        $newId = $newId + 1;
        $dataGen = new FlagGenerateNoCm();
        $dataGen->norec = $dataGen->generateNewId();
        $dataGen->id = $newId;
        $dataGen->nocmgenerate = $noCm;
        $dataGen->status = '0';
        $dataGen->tgldaftar = $request['tglsekarang'];
        $dataGen->save();

        $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "simpan Running Number";
//        }
//        }
        if ($transStatus == 'true') {
            $transMessage = "No RM : " . $dataGen->nocmgenerate;
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'data' => $dataGen->nocmgenerate,
                'as' => 'ramdanegie',
            );
        }
//        }  if ($transStatus == 'netral'){
//            $transMessage =   "No RM : " .$dataAya;
//            $result = array(
//                'status' => 201,
//                'message' => $transMessage,
//                'data' =>$dataAya ,
//                'as' => 'ramdanegie',
//            );
//        }
        else {
            $transMessage = "Error";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'data' =>$dataGen,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($datas);
    }
    public function getNoCmBelumDipake(Request $request)
    {
//        $data = DB::select(DB::raw("select EXTRACT(HOUR from AGE(NOW(), tgldaftar)) as daterange,
//            * from generate_nocm where status=:status;"),
//            array(
//                'status' =>'0',
//            )
//        );
        $data = DB::select(DB::raw("SELECT 
                (DATE_PART('day',CURRENT_TIMESTAMP - tgldaftar::timestamp) * 24 + 
                DATE_PART('hour',CURRENT_TIMESTAMP - tgldaftar::timestamp) )as hour_range,
                 * from generate_nocm where status=:status
                 order by tgldaftar desc ;"),
            array(
                'status' =>'0',
            )
        );
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }


    public function cekPasienDaftarDuaKali(Request $request)
    {
        // $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->Join('pasien_m as ps','ps.id','=','pd.nocmfk')
            // ->Join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select('pd.noregistrasi','pd.tglregistrasi','pd.objectruanganlastfk')
            ->where('ps.nocm', $request['nocm'])
            ->where('pd.tglregistrasi','>=', $request['tglregistrasi'].' 00:00')
            ->where('pd.tglregistrasi','<=', $request['tglregistrasi'].' 23:59')
            ->whereNull('br.pasiendaftarfk')
            ->orderBy('pd.noregistrasi', 'desc')
            ->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function savePasien(Request $request) {
        $detLogin =$request->all();
        DB::beginTransaction();
        try{
//            $noCM = $this->generateCode(new Pasien, 'nocm', 9, 'P');

            //##id pasien kosong 320027863->2105587804
            //##id alamat kosong 7324898->10000000

            $newId2 = 720000;
            $newId = Pasien::where('id','>',(float)720000-1)
                ->where('id','<',(float)10000000)
                ->count('id');
            $newId = (float)$newId2 + (float)$newId + 4;

            $newId3 = 984000;
            $newIdAl = Alamat::where('id','>',(float)984000-1)
                ->where('id','<',(float)7300000)
                ->count('id');
            $newIdAl = (float)$newId3 + (float)$newIdAl + 4;

            $dt = date('Y-m-d',strtotime($request['pasien']['tglLahir']));

            $runningNumber = RunningNumber::where('id',1745)->first();
            $extension = $runningNumber->extention;
            $noCmTerakhir = $runningNumber->nomer_terbaru +1;
            $noCm = $extension.$noCmTerakhir;

            $dataPS = new Pasien();
            $dataPS->id = $newId;
            $dataPS->kdprofile = 0;
            $dataPS->statusenabled = true;
            $dataPS->kodeexternal = $newId;
            $dataPS->namaexternal =  $request['pasien']['namaPasien'];
            $dataPS->norec =  $dataPS->generateNewId();
            $dataPS->reportdisplay =  $request['pasien']['namaPasien'];
            $dataPS->objectagamafk = $request['agama']['id'];
//            $dataPS->objectgolongandarahfk = $request['xxxx']['xxxxx'];
            $dataPS->objectjeniskelaminfk = $request['jenisKelamin']['id'];
            $dataPS->namapasien = $request['pasien']['namaPasien'];
            $dataPS->objectpekerjaanfk = $request['pekerjaan']['id'];
            $dataPS->objectpendidikanfk = $request['pendidikan']['id'];
            $dataPS->qpasien = 1;
            $dataPS->objectstatusperkawinanfk = $request['statusPerkawinan']['id'];
            $dataPS->tgldaftar = date('Y-m-d H:i:s');
            $dataPS->tgllahir = $dt;
//            $dataPS->objecttitlefk = $request['xxxx']['xxxxx'];
            $dataPS->namaibu = $request['namaIbu'];
            $dataPS->notelepon = $request['noTelepon'];
            $dataPS->noidentitas = $request['pasien']['noIdentitas'];
//            $dataPS->tglmeninggal = $request['xxxx']['xxxxx'];
            $dataPS->noaditional = $request['noAditional'];
//            $dataPS->paspor = $request['xxxx']['xxxxx'];
            $dataPS->objectkebangsaanfk = $request['kebangsaan']['id'];
            $dataPS->objectnegarafk = $request['negara']['id'];
//            $dataPS->namadepan = $request['xxxx']['xxxxx'];
//            $dataPS->namabelakang = $request['xxxx']['xxxxx'];
//            $dataPS->dokumenrekammedis = $request['xxxx']['xxxxx'];
            $dataPS->namaayah = $request['namaAyah'];
            $dataPS->namasuamiistri = $request['pasien']['namaSuamiIstri'];
            $dataPS->noasuransilain = $request['pasien']['noAsuransiLain'];
            $dataPS->nobpjs = $request['pasien']['noBpjs'];
            $dataPS->nohp = $request['pasien']['noHp'];
            $dataPS->tempatlahir = $request['pasien']['tempatLahir'];
            $dataPS->namakeluarga = $request['pasien']['namaKeluarga'];
//            $dataPS->jamlahir = $request['xxxx']['xxxxx'];
            $dataPS->nocm = $noCm;
            $dataPS->save();
            $dataNoCMFk =$dataPS->id;

            $updateRN= RunningNumber::where('id', 1745)
                ->update([
                        'nomer_terbaru' => $noCmTerakhir
                    ]
                );

//            $transStatus = 'true';
//        } catch (\Exception $e) {
            $transStatus = 'false';
//            $transMessage = "simpan Pasien Baru";
//        }

//        try{
            $newIDAlamat= $newIdAl;
            $dataAL = new Alamat();
            $dataAL->id = $newIDAlamat;
            $dataAL->kdprofile = 0;
            $dataAL->statusenabled = true;
            $dataAL->kodeexternal = $newIDAlamat;
//            $dataAL->namaexternal = $request['alamatLengkap'];
            $dataAL->norec = $dataAL->generateNewId();
//            $dataAL->reportdisplay = $request['alamatLengkap'];
//            $dataAL->alamatemail = $request['xxxx']['xxxxx'];
            $dataAL->alamatlengkap = $request['alamatLengkap'];
//            $dataAL->blackberry = $request['xxxx']['xxxxx'];
            $dataAL->objectdesakelurahanfk = $request['desaKelurahan']['id'];
//            $dataAL->facebook = $request['xxxx']['xxxxx'];
//            $dataAL->faksimile1 = $request['xxxx']['xxxxx'];
//            $dataAL->faksimile2 =$request['xxxx']['xxxxx'];
//            $dataAL->fixedphone1 =$request['xxxx']['xxxxx'];
//            $dataAL->fixedphone2 =$request['xxxx']['xxxxx'];
            $dataAL->objecthubungankeluargafk = 7;
//            $dataAL->isbillingaddress = $request['xxxx']['xxxxx'];
//            $dataAL->isprimaryaddress = $request['xxxx']['xxxxx'];
//            $dataAL->isshippingaddress = $request['xxxx']['xxxxx'];
            $dataAL->objectjenisalamatfk = 1;
            $dataAL->kdalamat = $newIDAlamat;
            $dataAL->objectkecamatanfk = $request['kecamatan']['id'];
//            $dataAL->keteranganlainnya = $request['alamatLengkap'];
            $dataAL->kodepos = $request['kodePos'];
            $dataAL->objectkotakabupatenfk = $request['kotaKabupaten']['id'];
//            $dataAL->line =$request['xxxx']['xxxxx'];
//            $dataAL->mobilephone1 = $request['xxxx']['xxxxx'];
//            $dataAL->mobilephone2 = $request['xxxx']['xxxxx'];
            $dataAL->namadesakelurahan = $request['desaKelurahan']['namaDesaKelurahan'];
            $dataAL->namakecamatan = $request['kecamatan']['namaKecamatan'];
            $dataAL->namakotakabupaten = $request['kotaKabupaten']['namaKotaKabupaten'];
//            $dataAL->namatempatgedung = $request['xxxx']['xxxxx'];
            $dataAL->objectnegarafk = $request['negara']['id'];
            $dataAL->nocmfk = $dataNoCMFk;
            $dataAL->objectpegawaifk = $detLogin['userData']['id'];
            $dataAL->objectpropinsifk = $request['propinsi']['id'];
//            $dataAL->objectrekananfk = $request['xxxx']['xxxxx'];
//            $dataAL->rtrw = $request['xxxx']['xxxxx'];
//            $dataAL->twitter = $request['xxxx']['xxxxx'];
//            $dataAL->website = $request['xxxx']['xxxxx'];
//            $dataAL->whatsapp = $request['xxxx']['xxxxx'];
//            $dataAL->yahoomessenger = $request['xxxx']['xxxxx'];
            $dataAL->kecamatan = $request['kecamatan']['namaKecamatan'];
            $dataAL->kotakabupaten = $request['kotaKabupaten']['namaKotaKabupaten'];
//            $dataAL->penanggungjawab_norec = $request['xxxx']['xxxxx'];
            $dataAL->save();

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
                'data' => $dataPS,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
//                'data' =>$dataPS,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function simpanUpdateDokters(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $datazz = PasienDaftar::where('norec', $request['norec'])->first();
            $data = PasienDaftar::where('norec', $request['norec'])
                ->update([
                        'objectpegawaifk' => $request['objectpegawaifk']]
                );
            $data2= AntrianPasienDiperiksa::where('noregistrasifk', $request['norec'])
                ->where('objectruanganfk', $request['objectruanganlastfk'])
                ->update([
                        'objectpegawaifk' => $request['objectpegawaifk']]
                );
            $transMessage = "Update Dokter berhasil!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Dokter gagal";
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
    public function cekNoregistrasi(Request $request)
    {
        $dataLogin = $request->all();
        $data = PasienDaftar::where('noregistrasi',$request['noregistrasi'])
            ->count();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function updateNoregis(Request $request)
    {
        DB::beginTransaction();

        $transStatus = true;
        $dataLogin = $request->all();
        $noRegistrasi = $this->generateCode(new PasienDaftar(), 'noregistrasi', 10, $this->getDateTime()->format('ym'));

        $data = PasienDaftar::where('norec', $request['norec_pd'])
            ->update([
                    'noregistrasi' => $noRegistrasi]
            );

        $transMsg = "Simpan  ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan ";
//
//            }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMsg
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMsg
            );
        }
        return $this->respond($result);
//        return $this->setStatusCode($result['status'])->respond($result, $transMsg);
    }

    public function updatePasien(Request $request) {

        DB::beginTransaction();
        $dataLogin = $request->all();
        $NewPasien=$request['pasien'];
        $NewAlamat=$request['alamat'];
        try{
            if($NewPasien['id']!='') {
                $dataPS = Pasien::where('id', $NewPasien['id'])
                    ->update([
                            'objectagamafk' => $NewPasien['objectagamafk'],
                            'objectjeniskelaminfk' => $NewPasien['objectjeniskelaminfk'],
                            'namapasien' => $NewPasien['namapasien'],
                            'objectpekerjaanfk' => $NewPasien['objectpekerjaanfk'],
                            'objectpendidikanfk' => $NewPasien['objectpendidikanfk'],
                            'objectstatusperkawinanfk' => $NewPasien['objectstatusperkawinanfk'],
                            'tgllahir' => $NewPasien['tgllahir'],
                            'namaibu' => $NewPasien['namaibu'],
                            'notelepon' => $NewPasien['notelepon'],
                            'noidentitas' => $NewPasien['noidentitas'],
                            'objectkebangsaanfk' => $NewPasien['objectkebangsaanfk'],
                            'objectnegarafk' => $NewPasien['objectnegarafk'],
                            'namaayah' => $NewPasien['namaayah'],
                            'namasuamiistri' => $NewPasien['namasuamiistri'],
                            'namakeluarga' => $NewPasien['namakeluarga'],
                            'noasuransilain' => $NewPasien['noasuransilain'],
                            'nobpjs' => $NewPasien['nobpjs'],
                            'nohp' => $NewPasien['nohp'],
                            'tempatlahir' => $NewPasien['tempatlahir'],
                            'jamlahir' => $NewPasien['tgllahir']
                        ]
                    );
                $dataAL = Alamat::where('nocmfk', $NewPasien['id'])
                    ->update([
                        'alamatlengkap' => $NewAlamat['alamatlengkap'],
                        'objectdesakelurahanfk' =>  $NewAlamat['objectdesakelurahanfk'],
                        'objectkecamatanfk' => $NewAlamat['objectkecamatanfk'],
                        'kodepos' => $NewAlamat['kodepos'],
                        'objectkotakabupatenfk' => $NewAlamat['objectkotakabupatenfk'],
                        'namadesakelurahan' => $NewAlamat['namadesakelurahan'],
                        'namakecamatan' => $NewAlamat['namakecamatan'],
                        'namakotakabupaten' => $NewAlamat['namakotakabupaten'],
                        'objectpegawaifk' => $dataLogin['userData']['id'],
                        'objectpropinsifk' => $NewAlamat['objectpropinsifk'],
                        'kecamatan' => $NewAlamat['namakecamatan'],
                        'kotakabupaten' => $NewAlamat['namakotakabupaten']
                    ]);

            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "update Pasien ";
        }

        if ($transStatus == 'true') {
            $transMessage = "Update Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPasien' => $dataPS,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Update Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateStatusEnabledPasien(Request $request) {

        DB::beginTransaction();
        try{
            if($request['idpasien']!='') {
                $dataPS = Pasien::where('id', $request['idpasien'])
                    ->update([
                            'statusenabled' => false,
                        ]
                    );
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "update sTATUS enABLED ";
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateAsuransiPasien(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $cek = Pasien::where('nocm', $request['nocm'])->first();
            $data2 = AsuransiPasien::where('nocmfk', $cek->id)
                ->update([
                    'objectkelasdijaminfk' => $request['objectkelasditanggungfk']
                ]);
            $transMessage = "Update Kelas Ditanggung berhasil";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Kelas Ditanggung gagal";
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

    public function getComboKelas(Request $request)
    {
        $dataLogin = $request->all();
        $data = \DB::table('kelas_m as kls')
            ->select('kls.id','kls.namakelas')
            ->where('kls.statusenabled',true)
            ->orderBy('kls.namakelas')
            ->get();
        $result = array(
            'kelas' => $data
        );
        return $this->respond($result);
    }
    public function getPemakaianAsuransi(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->select('pd.noregistrasi','pa.norec as norec_pa', 'pa.objectasuransipasienfk')
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->get();

        $result = array(
            'dataz' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getPsnByNoCm(Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->leftJOIN ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin ('pendidikan_m as pdd','ps.objectpendidikanfk','=','pdd.id')
            ->leftjoin ('pekerjaan_m as pk','ps.objectpekerjaanfk','=','pk.id')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin ('agama_m as agm','agm.id','=','ps.objectagamafk')
            ->leftjoin ('statusperkawinan_m as sp','sp.id','=','ps.objectstatusperkawinanfk')
            ->leftjoin ('kebangsaan_m as kb','kb.id','=','ps.objectkebangsaanfk')
            ->leftjoin ('negara_m as ng','ng.id','=','alm.objectnegarafk')
            ->leftjoin ('desakelurahan_m as dsk','dsk.id','=','alm.objectdesakelurahanfk')
            ->leftjoin ('kecamatan_m as kcm','kcm.id','=','alm.objectkecamatanfk')
            ->leftjoin ('kotakabupaten_m as kkb','kkb.id','=','alm.objectkotakabupatenfk')
            ->leftjoin ('propinsi_m as prp','prp.id','=','alm.objectpropinsifk')
            ->leftjoin ('pekerjaan_m as pk1','ps.pekerjaanpenangggungjawab','=','pk1.pekerjaan')
            ->leftjoin ('suku_m as sk','sk.id','=','ps.objectsukufk')
            ->leftjoin ('golongandarah_m as gol','gol.id','=','ps.objectgolongandarahfk')
            ->leftjoin ('jeniskelamin_m as jk1','jk1.jeniskelamin','=','ps.jeniskelaminpenanggungjawab')
            ->select('ps.nocm','ps.id as nocmfk','ps.namapasien','ps.tgllahir','ps.tempatlahir',
                'ps.objectjeniskelaminfk','jk.jeniskelamin','ps.objectagamafk','agm.agama','ps.objectstatusperkawinanfk',
                'sp.statusperkawinan','ps.objectpendidikanfk','pdd.pendidikan','ps.objectpekerjaanfk','pk.pekerjaan',
                'ps.objectkebangsaanfk','kb.name as kebangsaan','alm.objectnegarafk','ng.namanegara','ps.noidentitas',
                'ps.nobpjs','ps.noasuransilain','alm.alamatlengkap','alm.kodepos','alm.objectdesakelurahanfk','dsk.namadesakelurahan',
                'alm.objectkecamatanfk','kcm.namakecamatan','alm.objectkotakabupatenfk','kkb.namakotakabupaten',
                'alm.objectpropinsifk','prp.namapropinsi','ps.notelepon','ps.nohp','ps.namaayah','ps.namaibu',
                'ps.namakeluarga','ps.namasuamiistri','ps.penanggungjawab','ps.hubungankeluargapj','ps.pekerjaanpenangggungjawab',
                'ps.ktppenanggungjawab','ps.alamatrmh','ps.alamatktr','pk1.id as idpek','ps.photo','ps.foto',
                'ps.objectgolongandarahfk','gol.golongandarah','ps.objectsukufk','sk.suku','ps.bahasa','ps.teleponpenanggungjawab',
                'ps.dokterpengirim','ps.alamatdokterpengirim','jk1.id as jkidpenanggungjawab','ps.jeniskelaminpenanggungjawab','ps.umurpenanggungjawab');

        if(isset($request['noCm'] ) && $request['noCm']!= '' && $request['noCm']!= 'undefined'){
            $data= $data->where('ps.nocm', $request['noCm']);
        }
        if(isset($request['idPasien'] ) && $request['idPasien']!= '' && $request['idPasien']!= 'undefined'){
            $data= $data->where('ps.id', $request['idPasien']);
        }

        $data=$data->first();
//        $dt=[];
//        foreach ($data as $itm){
        $foto = null;
        if($data->foto != null){
            $foto = "data:image/jpeg;base64," . base64_encode($data->foto);
        }
        $dt = array(
            'nocm' => $data->nocm,
            'nocmfk' => $data->nocmfk,
            'namapasien' => $data->namapasien,
            'tgllahir' => $data->tgllahir,
            'tempatlahir' => $data->tempatlahir,
            'objectjeniskelaminfk' => $data->objectjeniskelaminfk,
            'jeniskelamin' => $data->jeniskelamin,
            'objectagamafk' => $data->objectagamafk,
            'agama' => $data->agama,
            'objectstatusperkawinanfk' => $data->objectstatusperkawinanfk,
            'statusperkawinan' => $data->statusperkawinan,
            'objectpendidikanfk' => $data->objectpendidikanfk,
            'pendidikan' => $data->pendidikan,
            'objectpekerjaanfk' => $data->objectpekerjaanfk,                'pekerjaan' => $data->pekerjaan,
            'objectkebangsaanfk' => $data->objectkebangsaanfk,                'kebangsaan' => $data->kebangsaan,
            'objectnegarafk' => $data->objectnegarafk,                'namanegara' => $data->namanegara,
            'noidentitas' => $data->noidentitas,                'nobpjs' => $data->nobpjs,
            'noasuransilain' => $data->noasuransilain,                'alamatlengkap' => $data->alamatlengkap,
            'kodepos' => $data->kodepos,                'objectdesakelurahanfk' => $data->objectdesakelurahanfk,
            'namadesakelurahan' => $data->namadesakelurahan,                'objectkecamatanfk' => $data->objectkecamatanfk,
            'namakecamatan' => $data->namakecamatan,                'objectkotakabupatenfk' => $data->objectkotakabupatenfk,
            'namakotakabupaten' => $data->namakotakabupaten,
            'objectpropinsifk' => $data->objectpropinsifk,
            'namapropinsi' => $data->namapropinsi,
            'notelepon' => $data->notelepon,'nohp' => $data->nohp,
            'namaayah' => $data->namaayah,'namaibu' => $data->namaibu,
            'namakeluarga' => $data->namakeluarga,'namasuamiistri' => $data->namasuamiistri,'penanggungjawab' => $data->penanggungjawab,'hubungankeluargapj' => $data->hubungankeluargapj,
            'pekerjaanpenangggungjawab' => $data->pekerjaanpenangggungjawab,'ktppenanggungjawab' => $data->ktppenanggungjawab,'penanggungjawab' => $data->penanggungjawab,'alamatrmh' => $data->alamatrmh,
            'alamatktr' => $data->alamatktr,'idpek' => $data->idpek,
            'foto' => $foto,
            'photo' => $data->photo,
            'suku' => $data->suku,
            'objectsukufk' => $data->objectsukufk,
            'golongandarah' => $data->golongandarah,
            'objectgolongandarahfk' => $data->objectgolongandarahfk,
            'bahasa' => $data->bahasa,
            'teleponpenanggungjawab' => $data->teleponpenanggungjawab,
            'dokterpengirim' => $data->dokterpengirim,
            'alamatdokterpengirim' => $data->alamatdokterpengirim,
            'jkidpenanggungjawab' => $data->jkidpenanggungjawab,
            'jeniskelaminpenanggungjawab' => $data->jeniskelaminpenanggungjawab,
            'umurpenanggungjawab' => $data->umurpenanggungjawab,
        );
//        }
        $result = array(
            'data'=> $dt,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getHistoryPemakaianAsuransiNew(Request $request) {
        $data = \DB::table('pemakaianasuransi_t as pa')
            ->join ('pasiendaftar_t as pd','pd.norec','=','pa.noregistrasifk')
            ->leftjoin ('asuransipasien_m as apn','apn.id','=','pa.objectasuransipasienfk')
            ->join ('rekanan_m as rek','rek.id','=','apn.kdpenjaminpasien')
            ->leftjoin ('rekanan_m as rek2','rek2.id','=','pd.objectrekananfk')
            ->join ('hubunganpesertaasuransi_m as hpa','hpa.id','=','apn.objecthubunganpesertafk')
            ->join ('kelas_m as kls','kls.id','=','apn.objectkelasdijaminfk')
            ->leftjoin ('diagnosa_m as dg','dg.id','=','pa.objectdiagnosafk')
            ->leftjoin ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin ('asalrujukan_m as asl','asl.id','=','pa.asalrujukanfk')
            ->select('pa.norec','apn.id as norec_ap','rek.namarekanan','pd.noregistrasi','pa.nokepesertaan','apn.namapeserta',
                'apn.noasuransi','apn.tgllahir','apn.noidentitas','apn.alamatlengkap',
                'apn.objecthubunganpesertafk','pa.nosep','pa.tanggalsep',
                'apn.objectkelasdijaminfk','kls.namakelas','pa.catatan','pa.norujukan',
                'apn.kdprovider','apn.nmprovider','pa.tglrujukan','pa.objectdiagnosafk',
                'dg.kddiagnosa','dg.namadiagnosa','pa.lakalantas','pa.ppkrujukan','pa.lokasilakalantas','pa.penjaminlaka',
                'pd.objectkelompokpasienlastfk','pd.objectrekananfk','rek2.namarekanan as namarekananpd','kps.kelompokpasien','apn.kdpenjaminpasien',
                'apn.jenispeserta','hpa.hubunganpeserta','apn.tgllahir','pa.cob','pa.katarak','pa.keteranganlaka',
                'pa.tglkejadian','pa.suplesi','pa.nosepsuplesi','pa.kdpropinsi','pa.namapropinsi','pa.kdkabupaten',
                'pa.namakabupaten','pa.kdkecamatan','pa.namakecamatan','pa.nosuratskdp','pa.kodedpjp','pa.namadpjp',
                'pa.asalrujukanfk','asl.asalrujukan')
            ->where('pa.norec', $request['noregistrasi'])
            ->orWhere('pd.noregistrasi', $request['noregistrasi'])
            ->get();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getPasienOnlineByNorec($norecReservasi) {
        $data = \DB::table('antrianpasienregistrasi_t as apr')
            ->leftjoin ('pasien_m as ps','ps.id','=','apr.nocmfk')
            ->leftjoin ('agama_m as agm ','agm.id','=','apr.objectagamafk')
            ->leftjoin ('asalrujukan_m as aru','aru.id','=','apr.objectasalrujukanfk')
            ->leftjoin ('desakelurahan_m as ds','ds.id','=','apr.objectdesakelurahanfk')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','apr.objectjeniskelaminfk')
            ->leftjoin ('pegawai_m as pg','pg.id','=','apr.objectpegawaifk')
            ->leftjoin ('pekerjaan_m as pkj','pkj.id','=','apr.objectpekerjaanfk')
            ->leftjoin ('pendidikan_m as pdd','pdd.id','=','apr.objectpendidikanfk')
            ->leftjoin ('ruangan_m as ru','ru.id','=','apr.objectruanganfk')
            ->leftjoin ('statusperkawinan_m as st','st.id','=','apr.objectstatusperkawinanfk')
            ->leftjoin ('kelompokpasien_m as kps','kps.id','=','apr.objectkelompokpasienfk')
            ->select('apr.norec', 'apr.noreservasi','apr.nocmfk', 'ps.nocm','apr.namapasien','apr.alamatlengkap', 'apr.namaayah',
                'apr.namaibu','apr.namasuamiistri','apr.noaditional','apr.noasuransilain','apr.noidentitas','apr.notelepon',
                'apr.nobpjs','apr.tempatlahir','apr.tgllahir','apr.tanggalreservasi','apr.tipepasien',
                'apr.objectagamafk','agm.agama','apr.objectasalrujukanfk','aru.asalrujukan', 'apr.objectdesakelurahanfk','ds.namadesakelurahan',
                'apr.isconfirm','apr.objectjeniskelaminfk','jk.jeniskelamin','apr.objectpegawaifk','pg.namalengkap as dokter',
                'apr.objectpekerjaanfk','pkj.pekerjaan','apr.objectpendidikanfk','pdd.pendidikan','apr.objectruanganfk','ru.namaruangan as ruangantujuan',
                'apr.objectstatusperkawinanfk','st.statusperkawinan',
                'apr.objectkelompokpasienfk','kps.kelompokpasien')
            ->where('apr.norec', $norecReservasi)
            ->first()   ;

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getMaxNoregistrasi() {
        $dateNow = Carbon::now();
        $year = Carbon::createFromFormat('Y-m-d H:i:s', $dateNow)->year;
        $month = Carbon::createFromFormat('Y-m-d H:i:s', $dateNow)->month;
        $tahun = (string)$year;
        $countByMonthAndYear = 0;
        $subStrTahun = substr($tahun, 2,4);
        $lastNoRegistrasi = collect(\DB::select("select max(p.noregistrasi)  as noregistrasi
            from pasiendaftar_t p where cast(substring(p.noregistrasi, 1,2)as int)='$subStrTahun' 
            and cast(substring(p.noregistrasi, 3,2)as int)=$month"))->first();
        if( !empty($lastNoRegistrasi->noregistrasi)){
            $temp = substr($lastNoRegistrasi->noregistrasi,4,10);
            $countByMonthAndYear= (int)$temp;
        }
        $countByMonthAndYear++;

        $monthNow = Carbon::createFromFormat('Y-m-d H:i:s', $dateNow)->month;
        $yearNow = Carbon::createFromFormat('Y-m-d H:i:s', $dateNow)->year;
        $yearNowString=substr((string)$yearNow,2, 4);
        $dateNowString=(string)$monthNow;
        $countByMonthAndYear = (string)$countByMonthAndYear;
        $countString = null;
        if(!empty($countByMonthAndYear)){
            if(strlen($countByMonthAndYear) == 1){
                $countString = '00000'.$countByMonthAndYear;
            }else if(strlen($countByMonthAndYear) == 2){
                $countString = '0000'. $countByMonthAndYear;
            }else if(strlen($countByMonthAndYear) == 3){
                $countString = '000'. $countByMonthAndYear;
            }else if(strlen($countByMonthAndYear) == 4){
                $countString = '00'. $countByMonthAndYear;
            }else if(strlen($countByMonthAndYear) == 5){
                $countString= '0'. $countByMonthAndYear;
            }
        }
        if(strlen($dateNowString)==1){
            $dateNowString='0'.$monthNow;
        }
        $result = $yearNowString.$dateNowString.$countString;

        return $result;
    }

    public function getApdDetail($noregistrasi, $ruanganlast)
    {
        $data =   collect(\DB::select("select * from
                (select pd.tglregistrasi,  ps.id as nocmfk,  ps.nocm,  pd.noregistrasi,  ps.namapasien,  ps.tgllahir, 
                 jk.jeniskelamin,  apd.objectruanganfk, ru.namaruangan,  kls.id as idkelas,kls.namakelas,  kp.kelompokpasien,  rek.namarekanan, 
                 apd.objectpegawaifk,  pg.namalengkap as namadokter,  br.norec, 
                 pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                 from antrianpasiendiperiksa_t as apd
                 inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk and pd.objectruanganlastfk = apd.objectruanganfk
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                 inner join pasien_m as ps on ps.id = pd.nocmfk
                 left join registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
                 left join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                 left join kelas_m as kls on kls.id = pd.objectkelasfk
                 left join ruangan_m as ru on ru.id = apd.objectruanganfk
                 left join departemen_m as dept on dept.id = ru.objectdepartemenfk
                 left join pegawai_m as pg on pg.id = apd.objectpegawaifk
                 left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                 left join rekanan_m as rek on rek.id = pd.objectrekananfk
                 where br.norec is null 
                --and dept.id in (16,  17,  35) 
                --and pd.tglpulang is null 
                and pd.noregistrasi='$noregistrasi'
                and ru.id= $ruanganlast  
              ) as x where x.rownum=1"))->first();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getKecamatanByDesaKelurahan($idDesa)
    {
        $kecamatan = \DB::table('kecamatan_m as ru')
            ->join('desakelurahan_m as dk','ru.id','=','dk.objectkecamatanfk')
            ->select('ru.id','ru.namakecamatan as namaKecamatan',
                'ru.kdkecamatan as kdKecamatan','ru.objectkotakabupatenfk as kotaKabupatenId',
                'ru.namaexternal as namaExternal','ru.objectpropinsifk as propinsiId')
            ->where('ru.statusenabled', true)
            ->where('dk.id',$idDesa)
            ->orderBy('ru.namakecamatan');
        $kecamatan = $kecamatan->get();
        return $this->respond($kecamatan);
    }

    public function batalPeriksaDelete(Request $request)
    {
        $dataLogin = $request->all();
        DB::beginTransaction();
        try {
            $pasienDaftar = PasienDaftar::where('norec',$request['norec'])->first();
            $data = \DB::table('pasiendaftar_t as pd')
                ->Join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->leftJoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
                ->leftJoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
                ->Join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->Join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
                ->Join('ruangan_m as ru2', 'ru2.id', '=', 'apd.objectruanganfk')
                ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ps.namapasien', 'pd.objectruanganasalfk',
                    'ru.namaruangan', 'br.norec as norec_br', 'apd.norec as norec_apd', 'ru2.objectdepartemenfk as departemen_apd', 'pp.norec as norec_pp', 'ru.objectdepartemenfk',
                    'pp.strukresepfk','apd.objectruanganfk')
                ->where('pd.norec', $request['norec'])
                ->orderBy('pp.strukresepfk')
                ->get();
            foreach ($data as $items) {
                $message = 'Sukses';
                if ($items->strukresepfk != null){
                    $message = 'Hapus Resep dulu!';
                    break;
                }
                if ($pasienDaftar->nostruklastfk == null) {
                    if($request['jenishapus'] == 'hapusregis'){
                        foreach ($data as $item) {
                            if ($item->norec_pp != null) {
                                PelayananPasienPetugas::where('pelayananpasien', $item->norec_pp)->delete();
                                PelayananPasienDetail::where('pelayananpasien', $item->norec_pp)->delete();
                                PelayananPasien::where('norec', $item->norec_pp)->delete();
                            }
                            DetailDiagnosaPasien::where('noregistrasifk', $item->norec_apd)->delete();
                            DiagnosaPasien::where('noregistrasifk', $item->norec_apd)->delete();
                        }
                        AntrianPasienDiperiksa::where('noregistrasifk', $request['norec'])->delete();
                        RegistrasiPelayananPasien::where('noregistrasifk', $request['norec'])->delete();
//                        AntrianPasienDiperiksa::where('noregistrasifk', $request['norec'])->update(
//                            [ 'statusenabled' => false]
//                        );
//                        RegistrasiPelayananPasien::where('noregistrasifk', $request['norec'])->update(
//                            [ 'statusenabled' => false]
//                        );
                        //region Model Batal Periksa & Logging
                        $newBR = new BatalRegistrasi();
                        $newBR->norec = $newBR->generateNewId();
                        $newBR->kdprofile = 0;
                        $newBR->statusenabled = true;
                        $newBR->alasanpembatalan = $request['alasanpembatalan'];
                        $newBR->pasiendaftarfk = $request['norec'];
                        $newBR->pegawaifk = $this->getCurrentUserID();
                        $newBR->pembatalanfk = $request['pembatalanfk'];
                        $newBR->tanggalpembatalan = $request['tanggalpembatalan'];
                        $newBR->save();

                        PasienDaftar::where('norec', $request['norec'])
                            ->update([
                                'kdprofile' => 0,
                                'statusenabled' => false,
                                'tglpulang' => $pasienDaftar->tglregistrasi ,
                            ]);

                        $newId = LoggingUser::max('id');
                        $newId = $newId + 1;
                        $logUser = new LoggingUser();
                        $logUser->id = $newId;
                        $logUser->norec = $logUser->generateNewId();
                        $logUser->kdprofile = 0;
                        $logUser->statusenabled = true;
                        $logUser->jenislog = 'Hapus Registrasi';
                        $logUser->noreff = $request['norec'];
                        $logUser->referensi = 'norec Pasien Daftar';
                        $logUser->keterangan = 'hapus semua pelayanan';
                        $logUser->objectloginuserfk = $dataLogin['userData']['id'];
                        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                        $logUser->save();
                        //endregion
                    }else{
                        foreach ($data as $item) {
                            if($item->departemen_apd == 16){
                                if ($item->norec_pp != null) {
                                    PelayananPasienPetugas::where('pelayananpasien', $item->norec_pp)->delete();
                                    PelayananPasienDetail::where('pelayananpasien', $item->norec_pp)->delete();
                                    PelayananPasien::where('norec', $item->norec_pp)->delete();
                                }
                                DetailDiagnosaPasien::where('noregistrasifk', $item->norec_apd)->delete();
                                DiagnosaPasien::where('noregistrasifk', $item->norec_apd)->delete();

                                StrukResep::where('pasienfk', $item->norec_apd)
                                    ->where('statusenabled',false)
                                    ->update([ 'pasienfk' => null ]);

                                AntrianPasienDiperiksa::where('noregistrasifk', $request['norec'])
                                    ->where('objectruanganfk', $item->objectruanganfk)->delete();
                                RegistrasiPelayananPasien::where('noregistrasifk', $request['norec'])
                                    ->where('objectruanganfk', $item->objectruanganfk)->delete();
                            }
                            if($item->departemen_apd != 16 && $item->departemen_apd != 3  && $item->departemen_apd != 27  ){
                                PasienDaftar::where('norec',$request['norec'])->update([
                                    'tglpulang' => $pasienDaftar->tglregistrasi,
                                    'objectruanganlastfk' => $item->objectruanganfk,
                                ]);
                            }
//                            else{
//                                $message = 'Tidak bisa batal Rawat Inap';
//                            }
                        }
                        //region Model Batal Periksa & Logging
                        $newId = LoggingUser::max('id');
                        $newId = $newId + 1;
                        $logUser = new LoggingUser();
                        $logUser->id = $newId;
                        $logUser->norec = $logUser->generateNewId();
                        $logUser->kdprofile = 0;
                        $logUser->statusenabled = true;
                        $logUser->jenislog = 'Batal Rawat Inap';
                        $logUser->noreff = $request['norec'];
                        $logUser->referensi = 'norec Pasien Daftar';
                        $logUser->keterangan = 'hapus semua pelayanan';
                        $logUser->objectloginuserfk = $dataLogin['userData']['id'];
                        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                        $logUser->save();
                        //endregion
                    }
                }
            }

            $transStatus = 'true';
            $transMessage = $message;
        } catch (\Exception $e) {
            $transStatus = 'false';
            $message = 'Gagal';
            $transMessage = $message;
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "norec_pd" => $request['norec'],
                "as" => 'giw',
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "norec_pd" => $request['norec'],
                "as" => 'giw',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getKamarIbuLast(Request $request){
        $idIbu = $request['id_ibu'];
        $noCm = $request['nocm'];
        $data = DB::select(DB::raw("
                select * from 
                            (select pd.norec as norec_pd,  apd.norec as norec_apd,  ps.nocm, 
                            pd.noregistrasi,  apd.objectkamarfk,  tt.id as nobed,  kmr.namakamar, 
                            tt.reportdisplay as tempattidur,ps.namapasien,
                            kls.id as kelasfk, kls.namakelas,ru.namaruangan,pd.objectruanganlastfk,
                            row_number() over (partition by apd.objectruanganfk order by apd.tglmasuk desc) as rownum
                            from pasiendaftar_t as pd
                            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec and pd.objectruanganlastfk=apd.objectruanganfk
                            inner join pasien_m as ps on ps.id = pd.nocmfk
                            inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                            inner join kelas_m as kls on kls.id = pd.objectkelasfk
                            left join kamar_m as kmr on kmr.id = apd.objectkamarfk
                            left join tempattidur_m as tt on tt.id = apd.nobed
                            where ps.qpasien ='$idIbu'
                            and ps.nocm <> '$noCm'
                            and ps.namapasien not  like '%By Ny%'
                            and pd.tglpulang is null 
                ) as x
                where x.rownum =1
               "));

        return $this->respond($data);
    }
    public function getNorecAPD(Request $request)
    {
        $filter = $request->all();
        $noreg = '';
        if (isset($filter['noreg']) && $filter['noreg'] != "" && $filter['noreg'] != "undefined") {
            $noreg = " AND pd.noregistrasi = '" .  $filter['noreg']."'";
        }
        $ruangId = '';
        if (isset($filter['ruangId']) && $filter['ruangId'] != "" && $filter['ruangId'] != "undefined") {
            $ruangId = ' AND apd.objectruanganfk = ' . $filter['ruangId'];
        }
        $namaRuangan = '';
        if (isset($filter['namaRuangan']) && $filter['namaRuangan'] != "" && $filter['namaRuangan'] != "undefined") {
            $ruangId = " AND ru.namaruangan like '%"  . $filter['namaRuangan']."%'";
        }
        $data =DB::select(DB::raw("select * from
                (select pd.tglregistrasi,pd.noregistrasi, ru.namaruangan,
                 pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, 
                 row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                 from antrianpasiendiperiksa_t as apd
                 inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk and pd.objectruanganlastfk = apd.objectruanganfk
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                 inner join ruangan_m as ru on ru.id = apd.objectruanganfk
                 where br.norec is null 
                and pd.tglpulang is null --and pd.noregistrasi='1808010084'
                $ruangId $noreg  $namaRuangan
              ) as x where x.rownum=1")
        );
        return $this->respond($data);
    }

    public function getComboRegBaru(Request $request)
    {
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $jk = JenisKelamin::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(jeniskelamin) as jeniskelamin"))
            ->get();

        $agama = Agama::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(agama) as agama"))
            ->get();

        $statusPerkawinan = StatusPerkawinan::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(statusperkawinan) as statusperkawinan,namaexternal as namadukcapil"))
            ->get();

        $pendidikan = Pendidikan::where('statusenabled',true)
            ->select(DB::raw("id, UPPER(pendidikan) as pendidikan,namaexternal as namadukcapil"))
            ->get();

        $pekerjaan = DB::table('pekerjaan_m')
            ->select(DB::raw("id, UPPER(pekerjaan) as pekerjaan,namaexternal as namadukcapil"))
            ->where('statusenabled',true)
            ->get();

        $gd = DB::table('golongandarah_m')
            ->select(DB::raw("id, UPPER(golongandarah) as golongandarah,namaexternal as namadukcapil"))
            ->where('statusenabled',true)
            ->get();
        $suku = DB::table('suku_m')
            ->select(DB::raw("id, UPPER(suku) as suku"))
            ->where('statusenabled',true)
            ->get();
        $result = array(
            'jeniskelamin' => $jk,
            'agama' => $agama,
            'statusperkawinan' => $statusPerkawinan,
            'pendidikan' => $pendidikan,
            'pekerjaan' => $pekerjaan,
            'pegawaiLogin' => $dataPegawai->namalengkap,
            'golongandarah' => $gd,
            'suku' => $suku,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getComboAddress(Request $request)
    {
        $kebangsaan = DB::table('kebangsaan_m')
            ->select(DB::raw("id, UPPER(name) as name"))
            ->where('statusenabled',true)
            ->get();

        $negara = DB::table('negara_m')
            ->select(DB::raw("id, UPPER(namanegara) as namanegara"))
            ->where('statusenabled',true)
            ->orderBy('namanegara')
            ->get();

        $kotakabupaten = DB::table('kotakabupaten_m')
            ->select(DB::raw("id, UPPER(namakotakabupaten) as namakotakabupaten"))
            ->where('statusenabled',true)
            ->orderBy('namakotakabupaten')
            ->get();

        $propinsi = DB::table('propinsi_m')
            ->select(DB::raw("id, UPPER(namapropinsi) as namapropinsi"))
            ->where('statusenabled',true)
            ->orderBy('namapropinsi')
            ->get();

        $kecamatan = DB::table('kecamatan_m')
            ->select(DB::raw("id, UPPER(namakecamatan) as namakecamatan"))
            ->where('statusenabled',true)
            ->orderBy('namakecamatan')
            ->get();
        $result = array(
            'kebangsaan' => $kebangsaan,
            'negara' => $negara,
            'kotakabupaten' => $kotakabupaten,
            'propinsi' => $propinsi,
            'kecamatan' => $kecamatan,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getDesaKelurahanPaging(Request $request)
    {

        $req = $request->all();
        if(isset($req['filter']['filters'][0]['value'])){
            $explode = explode(',',$req['filter']['filters'][0]['value']);
            if(count($explode) > 1){
                $namaDesa = $explode[0];
                $namaKec = $explode[1];
            }
        }

//        return $namaKec;
        $Desa = \DB::table('desakelurahan_m as ds')
            ->join('kecamatan_m as kc','ds.objectkecamatanfk','=','kc.id')
            ->join('kotakabupaten_m as kk','ds.objectkotakabupatenfk','=','kk.id')
            ->join('propinsi_m as pp','ds.objectpropinsifk','=','pp.id')
            ->select(DB::raw("ds.id,UPPER(ds.namadesakelurahan) as namadesakelurahan,ds.kodepos,
			                 ds.objectkecamatanfk,ds.objectkotakabupatenfk,ds.objectpropinsifk,
				             kc.namakecamatan,kk.namakotakabupaten,pp.namapropinsi"))
            ->where('ds.statusenabled', true)
            ->orderBy('ds.namadesakelurahan');

        if(isset($req['namadesakelurahan']) &&
            $req['namadesakelurahan']!="" &&
            $req['namadesakelurahan']!="undefined"){
            $Desa = $Desa->where('ds.namadesakelurahan','ilike','%'. $req['namadesakelurahan'] .'%' );
        };
        if(isset($req['namakecamatan']) &&
            $req['namakecamatan']!="" &&
            $req['namakecamatan']!="undefined"){
            $Desa = $Desa->where('kc.namakecamatan','ilike','%'. $req['namakecamatan'] .'%' );
        };
        if(isset($req['iddesakelurahan']) &&
            $req['iddesakelurahan']!="" &&
            $req['iddesakelurahan']!="undefined"){
            $Desa = $Desa->where('ds.id', $req['iddesakelurahan'] );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            if(isset($namaDesa) && isset($namaKec)){
                $Desa = $Desa
                    ->where('ds.namadesakelurahan','ilike','%'.$namaDesa.'%' )
                    ->where('kc.namakecamatan','ilike','%'.$namaKec.'%' );
            }else{
                $Desa = $Desa
                    ->where('ds.namadesakelurahan','ilike','%'.$req['filter']['filters'][0]['value'].'%' )
                    ->Orwhere('kc.namakecamatan','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
            }

        }

        $Desa = $Desa->take(20);
        $Desa = $Desa->get();
        $tempDesa = [];
        if(count($Desa) != 0){
            foreach ($Desa as $item){
                $tempDesa [] = array(
                    'id' => $item->id,
                    'namadesakelurahan' => $item->namadesakelurahan,
                    'kodepos' => $item->kodepos,
                    'namakecamatan' => $item->namakecamatan,
                    'namakotakabupaten' => $item->namakotakabupaten,
                    'namapropinsi' => $item->namapropinsi,
                    'desa' => $item->namadesakelurahan .', '. $item->namakecamatan .',  '. $item->namakotakabupaten .', '.
                        $item->namapropinsi ,
                    'objectkecamatanfk' => $item->objectkecamatanfk,
                    'objectkotakabupatenfk' => $item->objectkotakabupatenfk,
                    'objectpropinsifk' => $item->objectpropinsifk,
                );
            }
        }
        return $this->respond($tempDesa);
    }

    public function saveIdPasienDoang(Request $request) {
        $detLogin =$request->all();
        DB::beginTransaction();
        try{
            //region Save Pasien


            $newId = Pasien::max('id') + 1;
            $dataPS = new Pasien();
            $dataPS->id = $newId;
            $dataPS->kdprofile = 12;
            $dataPS->statusenabled = false;
            $dataPS->kodeexternal = $newId;
            $dataPS->norec =  $dataPS->generateNewId();

            $dataPS->save();
            $dataNoCMFk = $newId;


            $idAlamat = Alamat::max('id') + 1;
            $dataAL = new Alamat();
            $dataAL->id = $idAlamat;
            $dataAL->kdprofile = 12;
            $dataAL->statusenabled = false;
            $dataAL->kodeexternal = $idAlamat;
            $dataAL->norec = $dataAL->generateNewId();
            $dataAL->nocmfk = $dataNoCMFk;
            $dataAL->alamatlengkap = '';
            $dataAL->save();
            //endregion
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
//            $dataPS->foto = "data:image/jpeg;base64," . base64_encode($dataPS->foto);
            $result = array(
                'status' => 201,
                'data' => $newId,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function storeImageToFolder($nocm){
        $pasien = Pasien::where('nocm',$nocm)
            ->where('statusenabled',true)->first();
        if(!empty($pasien) ){
            $result = array(
                'status' => 'Sukses menyimpan foto ke Directory '.'../storage/app/photo/'.$nocm.'.jpg',
                'as' => 'er@epic',
            );
            if( $pasien->photo == null){
                $settingDataFix = SettingDataFixed::where('namafield','photoDefault')->first();
                $pasien->photo = $settingDataFix->gambar;
                $result = array(
                    'status' => 'Photo belum di upload',
                    'as' => 'er@epic',
                );

            }
            $data =  $pasien->photo;

            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
            $data = base64_decode($data);
            $tujuan_upload = 'data_file';
            \Storage::disk('local')->put('photo/'.$nocm.'.jpg', $data);
//            $data->move($tujuan_upload,$file->getClientOriginalName());
//            file_put_contents('D:/'.$nocm.'.jpg', $data);
            return $this->respond($result);
        }

    }
    public function getDaftarAntrianPasienDiperiksa(Request $request)
    {
        $filter = $request->all();
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->leftjoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->leftjoin('strukpelayanan_t as sp','sp.norec','=','pd.nostruklastfk')
            ->join('pasien_m as ps', 'ps.id','=','pd.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->leftjoin('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->leftJoin('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','apd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->select('pd.tglregistrasi','ps.nocm','pd.noregistrasi','ps.namapasien','ps.tgllahir',
                'jk.jeniskelamin','apd.objectruanganfk','ru.namaruangan','kls.id as idkelas','kls.namakelas',
                'kp.kelompokpasien','rek.namarekanan','apd.objectpegawaifk','pg.namalengkap as dokter','pg.id as pgid',
                'br.norec','pd.norec as norec_pd','apd.norec as norec_apd','apd.objectasalrujukanfk',
                'apd.tgldipanggildokter','apd.noantrian','apd.tglmasuk',
                'ps.id as nocmfk',
                DB::raw("case when apd.statusantrian = '0' then 'MENUNGGU'
					 when apd.statusantrian = '1' then 'DIPANGGIL_SUSTER'
					 when apd.statusantrian = '2' then 'DIPANGGIL_DOKTER'
					 when apd.statusantrian = '3' then 'SELESAI_DIPERIKSA'
					 else 'MENUNGGU' end as statusantrian"))
            ->whereNull('br.norec');

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('apd.tglregistrasi', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('apd.tglregistrasi', '<=', $filter['tglAkhir']);
        }
        if (isset($filter['ruanganId']) && $filter['ruanganId'] != "" && $filter['ruanganId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruanganId']);
        }
        if (isset($filter['nocm']) && $filter['nocm'] != "" && $filter['nocm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'.$filter['nocm'].'%');
//				->orWhere('ps.namapasien', 'ilike', '%'.$filter['noRmNama'].'%')	;
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->Where('ps.namapasien', 'ilike', '%'.$filter['nama'].'%')	;
        }
//		$data = $data->whereIn('dept.id',[18,24,28,27,3]);
        $data = $data->orderBy('apd.tglregistrasi');
        $data = $data->get();
        $result = array(
            'listData' => $data,
            'as' => 'Inhuman'
        );
        return $this->respond($result);
    }
    public function getComboAntrianPasienOperator(Request $request)
    {
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $ruangan = Ruangan::where('statusenabled',true)
            ->select('id','namaruangan','objectdepartemenfk')
            ->whereIn('objectdepartemenfk',[3,18,16,24,28,27])
            ->orderBy('namaruangan')
            ->get();
        $jenisDiagnosa = JenisDiagnosa::where('statusenabled',true)
            ->select('id','jenisdiagnosa')
            ->orderBy('jenisdiagnosa')
            ->get();
        $kelas = Kelas::where('statusenabled',true)
            ->select('id','namakelas')
            ->orderBy('namakelas')
            ->get();
        $pembatalan = DB::select(DB::raw("select id,name from pembatal_m where statusenabled='true'"));

        $result = array(
            'data' => $ruangan,
            'pembatalan' => $pembatalan,
            'jenisdiagnosa' => $jenisDiagnosa,
            'kelas' => $kelas,
            'pegawaiLogin' => $dataPegawai->namalengkap,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getDiagnosaPasienByNorecAPD(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('dg.id', 'pd.noregistrasi', 'pd.tglregistrasi', 'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd',
                'ddp.objectdiagnosafk', 'dg.kddiagnosa', 'dg.namadiagnosa', 'ddp.objectjenisdiagnosafk',
                'jd.jenisdiagnosa', 'dp.norec as norec_diagnosapasien', 'ddp.norec as norec_detaildpasien',
                'dp.ketdiagnosis', 'ddp.keterangan')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->Join('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->leftJoin('diagnosa_m as dg', 'dg.id', '=', 'ddp.objectdiagnosafk')
            ->leftJoin('jenisdiagnosa_m as jd', 'jd.id', '=', 'ddp.objectjenisdiagnosafk');

        if (isset($request['noRec']) && $request['noRec'] != "" && $request['noRec'] != "undefined") {
            $data = $data->where('apd.norec', '=', $request['noRec']);
        };

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }
    public function saveArrDiagnosaPasien(Request $request)
    {
        $dataLogin = $request->all();
        DB::beginTransaction();
        try{
            $pasienDaftar = PasienDaftar::where('norec',$request['norec_pd'])->first();
            foreach ($request['diagnosis'] as $item){

                $dataDiagnosa = new DiagnosaPasien();
                $dataDiagnosa->norec = $dataDiagnosa->generateNewId();
                $dataDiagnosa->kdprofile = 0;
                $dataDiagnosa->statusenabled = true;
                $dataDiagnosa->noregistrasifk = $request['norec_apd'];
                $dataDiagnosa->ketdiagnosis = $item['keterangan'];
                $dataDiagnosa->tglregistrasi = $pasienDaftar->tglregistrasi;
                $dataDiagnosa->tglpendaftaran =$pasienDaftar->tglregistrasi;
                $dataDiagnosa->save();
                $norec = $dataDiagnosa->norec;

                $dataDetailDiagnosa = new DetailDiagnosaPasien();
                $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
                $dataDetailDiagnosa->kdprofile = 0;
                $dataDetailDiagnosa->statusenabled = true;
                $dataDetailDiagnosa->objectpegawaifk = $request['pegawaifk'];
                $dataDetailDiagnosa->noregistrasifk = $request['norec_apd'];
                $dataDetailDiagnosa->tglregistrasi = $pasienDaftar->tglregistrasi;
                $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
                $dataDetailDiagnosa->objectdiagnosafk = $item['iddiagnosa'];
                $dataDetailDiagnosa->objectdiagnosapasienfk = $norec;
                $dataDetailDiagnosa->objectjenisdiagnosafk = $item['jenisdiagnosisid'];
                $dataDetailDiagnosa->tglinputdiagnosa = date('Y-m-d H:i:s');
                $dataDetailDiagnosa->keterangan = $item['keterangan'];
                $dataDetailDiagnosa->save();

            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Tersimpan";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveUpdateKelasAPD(Request $request)
    {
        DB::beginTransaction();
        try{
            $Rpp = RegistrasiPelayananPasien::where('noregistrasifk',$request['norec_pd'])->get();
            if(count($Rpp) > 0){
                RegistrasiPelayananPasien::where('tglmasuk',$request['tglmasuk'])
                    ->where('objectruanganfk',$request['objectruanganfk'])
                    ->where('noregistrasifk',$request['norec_pd'])
                    ->update([
                        'objectkelasfk' => $request['objectkelasfk']
                    ]);
            }
            PasienDaftar::where('norec',$request['norec_pd'])->update(
                [	'objectkelasfk' => $request['objectkelasfk']]
            );
            AntrianPasienDiperiksa::where('norec',$request['norec_apd'])->update(
                [	'objectkelasfk' => $request['objectkelasfk']]
            );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Update Kelas Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = "Update Kelas Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }


    public function saveRencanaPindah(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];
        try{

            if ($request['strukorder']['norecorder'] == '') {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 14, 'RPP-' . $this->getDateTime()->format('ym'));
                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = 0;
                $dataSO->statusenabled = true;
                $dataSO->isdelivered = 0;
                $dataSO->noorder = $noOrder;
                $dataSO->objectkelompoktransaksifk = 122;
            }else {
                $dataSO = StrukOrder::where('norec',$request['strukorder']['norecorder'])->first();
                OrderPelayanan::where('noorderfk',$request['strukorder']['norecorder'])->delete();
            }
            $dataSO->noregistrasifk = $r_NewPD['norec_pd'];
            $dataSO->nocmfk = $r_NewPD['nocmfk'];
            $dataSO->keteranganorder =  $r_NewAPD['keteranganpindah'];
            $dataSO->objectpegawaiorderfk = $dataPegawai->objectpegawaifk;
            $dataSO->qtyjenisproduk =0;
            $dataSO->qtyproduk = 0;
            $dataSO->objectruanganfk = $r_NewPD['objectruanganlastfk'];
            $dataSO->objectruangantujuanfk = $r_NewAPD['objectruanganlastfk'];
            $dataSO->tglorder = $request['strukorder']['tglorder'];
            $dataSO->tglrencana = $r_NewAPD['tglmasuk'];
//                $dataSO->statusorder = 0; //pengajuan pindah
            $dataSO->statusorder = 1; //langsung pindah
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = 0;
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn = 0;
            $dataSO->save();
            $dataSO = $dataSO->norec;

            $dataOP = new OrderPelayanan();
            $dataOP->norec = $dataOP->generateNewId();
            $dataOP->kdprofile = 0;
            $dataOP->statusenabled = true;
            $dataOP->iscito = 0;
            $dataOP->israwatgabung = $r_NewAPD['israwatgabung'];
            $dataOP->objectkamarfk = $r_NewAPD['objectkamarfk'];
            $dataOP->objectkelasfk = $r_NewAPD['objectkelasfk'];
            $dataOP->objectkelaskamarfk = $r_NewAPD['objectkelasfk'];
            $dataOP->nobed = $r_NewAPD['nobed'];
            $dataOP->objectruanganfk = $r_NewPD['objectruanganlastfk'];
            $dataOP->objectruangantujuanfk = $r_NewAPD['objectruanganlastfk'];
            $dataOP->tglpelayanan = $request['strukorder']['tglorder'];
            $dataOP->noorderfk = $dataSO;
            $dataOP->qtyproduk = 0;
            $dataOP->qtyprodukretur = 0;
            $dataOP->strukorderfk = $dataSO;
            $dataOP->save();

            //##Save Registrasi Pel Pasien##
            if ($request['strukorder']['norecrpp'] == '') {
                $dataRPP = new RegistrasiPelayananPasien();
                $dataRPP->norec = $dataRPP->generateNewId();;
                $dataRPP->kdprofile = 2;
                $dataRPP->statusenabled = true;
            }else{
                $dataRPP = RegistrasiPelayananPasien::where('norec',$request['strukorder']['norecrpp'])->first();
            }
            $dataRPP->objectasalrujukanfk = $r_NewAPD['objectasalrujukanfk'];
            $dataRPP->israwatgabung = $r_NewAPD['israwatgabung'];;
            $dataRPP->objectkelaskamarrencanafk =$r_NewAPD['objectkelasfk'];;
            $dataRPP->objectkelasrencanafk =  $r_NewAPD['objectkelasfk'];
            $dataRPP->kdpenjaminpasien = 0;
            $dataRPP->objectkelompokpasienfk = $r_NewPD['objectkelompokpasienlastfk'];
            $dataRPP->keteranganlainnyarencana = $r_NewAPD['keteranganpindah'];
            $dataRPP->noantrianbydokter = 0;
            $dataRPP->nobedrencana =  $r_NewAPD['nobed'];;
            $dataRPP->nocmfk = $r_NewPD['nocmfk'];
            $dataRPP->noregistrasifk = $r_NewPD['norec_pd'];
            $dataRPP->objectruanganasalfk = $r_NewAPD['objectruanganasalfk'];
            $dataRPP->objectruanganrencanafk =  $r_NewAPD['objectruanganlastfk'];
            $dataRPP->objectstatuskeluarrencanafk =  $r_NewPD['objectstatuskeluarfk'];
            $dataRPP->objecttempattidurrencanafk = $r_NewAPD['nobed'];
            $dataRPP->objectkamarrencanafk = $r_NewAPD['objectkamarfk'];
            $dataRPP->tglkeluarrencana = $r_NewAPD['tglmasuk'];
            $dataRPP->strukorderfk = $dataSO;
            $dataRPP->save();
            $dataNorecRPP=$dataRPP->norec;


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Order";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noorder" => $dataSO,
                "norecrpp" => $dataNorecRPP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "norecrpp" => $dataNorecRPP,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarRencanaPindahPasien(Request $request) {
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as so')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->JOIN('registrasipelayananpasien_t as rpp','rpp.strukorderfk','=','so.norec')
            ->JOIN('pasien_m as pas','pas.id','=','so.nocmfk')
            ->JOIN('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->JOIN('antrianpasiendiperiksa_t as apd',function ($join){
                $join->on('apd.noregistrasifk','=','pd.norec');
                $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
            })
            ->JOIN('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->JOIN('ruangan_m as ru1','ru1.id','=','so.objectruangantujuanfk')
            ->JOIN('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("so.norec as norec_so,pas.nocm,pd.noregistrasi,pas.namapasien,kls.namakelas,ru.namaruangan,
                     ru1.namaruangan as ruanganrencana,so.tglrencana,kp.kelompokpasien,
                     so.statusorder,pd.norec as norec_pd,apd.norec as norec_apd"));

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglrencana','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('so.tglrencana','<=', $tgl);
        }
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noReg']);
        }
        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('pas.nocm','ilike','%'. $request['noCm']);
        }
        $data = $data->where('so.statusenabled',true);
        $data = $data->where('so.objectkelompoktransaksifk',122);
        $data = $data->orderBy('so.tglrencana');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataPasienPindah(Request $request) {
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $dataAwal = DB::select(DB::raw("select pd.objectruanganlastfk,ru.namaruangan,rpp.objectkamarfk,km.namakamar,
		            rpp.objecttempattidurfk,tt.reportdisplay as nomorbed,rpp.objectkelasfk,kls.namakelas
                    from pasiendaftar_t as pd
                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec and pd.objectruanganlastfk = rpp.objectruanganfk
                    INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                    INNER JOIN kamar_m as km on km.id = rpp.objectkamarfk
                    INNER JOIN tempattidur_m as tt on tt.id = rpp.objecttempattidurfk
                    INNER JOIN kelas_m as kls on kls.id = rpp.objectkelasfk
                    where pd.norec=:norec"),
            array(
                'norec' => $request['norec_pd'],
            )
        );

        $dataRencana = DB::select(DB::raw("select rpp.objectstatuskeluarrencanafk,sk.statuskeluar,op.objectruangantujuanfk,ru.namaruangan,op.israwatgabung,
                                     rpp.objectkelaskamarrencanafk,kls.namakelas,rpp.objectkamarrencanafk,km.namakamar,
                                     tt.id as nobedrencana,tt.reportdisplay as nomorbed,so.keteranganorder,so.tglrencana	
                        from strukorder_t as so
                        INNER JOIN orderpelayanan_t as op on op.noorderfk = so.norec
                        INNER JOIN registrasipelayananpasien_t as rpp on rpp.strukorderfk = so.norec
                        INNER JOIN statuskeluar_m as sk on sk.id = rpp.objectstatuskeluarrencanafk
                        INNER JOIN ruangan_m as ru on ru.id = op.objectruangantujuanfk
                        INNER JOIN kelas_m as kls on kls.id = rpp.objectkelaskamarrencanafk
                        INNER JOIN kamar_m as km on km.id = rpp.objectkamarrencanafk
                        INNER JOIN tempattidur_m as tt on tt.id = rpp.nobedrencana
                        where so.norec=:norec"),
            array(
                'norec' => $request['norec_so'],
            )
        );

        $result = array(
            'dataawal' => $dataAwal,
            'datarencana' => $dataRencana,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveRencanaPulang(Request $request) {
        DB::beginTransaction();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $r_NewPD=$request['pasiendaftar'];
        $r_NewAPD=$request['antrianpasiendiperiksa'];
        try{

            if ($request['strukorder']['norecorder'] == '') {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 14, 'RPP-' . $this->getDateTime()->format('ym'));
                $dataSO = new StrukOrder();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = 0;
                $dataSO->statusenabled = true;
                $dataSO->isdelivered = 0;
                $dataSO->noorder = $noOrder;
                $dataSO->objectkelompoktransaksifk = 123;
            }else {
                $dataSO = StrukOrder::where('norec',$request['strukorder']['norecorder'])->first();
                OrderPelayanan::where('noorderfk',$request['strukorder']['norecorder'])->delete();
            }
            $dataSO->noregistrasifk = $r_NewPD['norec_pd'];
            $dataSO->nocmfk = $r_NewPD['nocmfk'];
            $dataSO->keteranganorder =  $r_NewPD['keteranganpulang'];
            $dataSO->objectpegawaiorderfk = $dataPegawai->objectpegawaifk;
            $dataSO->qtyjenisproduk =0;
            $dataSO->qtyproduk = 0;
            $dataSO->objectruanganfk = $r_NewPD['objectruanganlastfk'];
            $dataSO->objectruangantujuanfk = $r_NewAPD['objectruanganlastfk'];
            $dataSO->tglorder = $request['strukorder']['tglorder'];
            $dataSO->tglrencana = $r_NewPD['tglpulang'];
            $dataSO->statusorder = 4;//3; //rencana pulang (4 status sudah d pulangkan)
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = 0;
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn = 0;
            $dataSO->save();
            $dataSO = $dataSO->norec;
            $dataOP = new OrderPelayanan();
            $dataOP->norec = $dataOP->generateNewId();
            $dataOP->kdprofile = 0;
            $dataOP->statusenabled = true;
            $dataOP->iscito = 0;
            $dataOP->objectruanganfk = $r_NewPD['objectruanganlastfk'];
            $dataOP->tglpelayanan = $request['strukorder']['tglorder'];
            $dataOP->noorderfk = $dataSO;
            $dataOP->qtyproduk = 0;
            $dataOP->qtyprodukretur = 0;
            $dataOP->strukorderfk = $dataSO;
            $dataOP->save();

            //##Save Registrasi Pel Pasien##
            if ($request['strukorder']['norecrpp'] == '') {
                $dataRPP = new RegistrasiPelayananPasien();
                $dataRPP->norec = $dataRPP->generateNewId();;
                $dataRPP->kdprofile = 3;
                $dataRPP->statusenabled = true;
            }else{
                $dataRPP = RegistrasiPelayananPasien::where('norec',$request['strukorder']['norecrpp'])->first();
            }
            $dataRPP->kdpenjaminpasien = 0;
            $dataRPP->keteranganlainnyarencana = $r_NewPD['keteranganpulang'];
            $dataRPP->noantrianbydokter = 0;
            $dataRPP->nocmfk = $r_NewPD['nocmfk'];
            $dataRPP->noregistrasifk = $r_NewPD['norec_pd'];
            $dataRPP->objectruanganasalfk =$r_NewAPD['objectruanganlastfk'];
            $dataRPP->objectstatuskeluarrencanafk =  $r_NewPD['objectstatuskeluarfk'];
            $dataRPP->objectstatuspulangrencanafk = $r_NewPD['objectstatuspulangfk'];
            $dataRPP->tglkeluarrencana = $r_NewPD['tglpulang'];
            $dataRPP->objecthubungankeluargaambilpasienfk = $r_NewPD['objecthubungankeluargaambilpasienfk'];
            $dataRPP->objectkondisipasienfk = $r_NewPD['objectkondisipasienfk'];
            $dataRPP->namalengkapambilpasien = $r_NewPD['namalengkapambilpasien'];
            $dataRPP->strukorderfk = $dataSO;
            $dataRPP->save();
            $dataNorecRPP=$dataRPP->norec;


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Order";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noorder" => $dataSO,
                "norecrpp" => $dataNorecRPP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "norecrpp" => $dataNorecRPP,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarRencanaPulangPasien(Request $request) {
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $data = \DB::table('strukorder_t as so')
            ->JOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->JOIN('registrasipelayananpasien_t as rpp','rpp.strukorderfk','=','so.norec')
            ->JOIN('pasien_m as pas','pas.id','=','so.nocmfk')
            ->JOIN('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->JOIN('antrianpasiendiperiksa_t as apd',function ($join){
                $join->on('apd.noregistrasifk','=','pd.norec');
                $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
            })
            ->JOIN('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->JOIN('ruangan_m as ru1','ru1.id','=','so.objectruangantujuanfk')
            ->JOIN('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("so.norec as norec_so,pas.nocm,pd.noregistrasi,pas.namapasien,kls.namakelas,ru.namaruangan,
                     ru1.namaruangan as ruanganrencana,so.tglrencana,kp.kelompokpasien,
                     so.statusorder,pd.norec as norec_pd,apd.norec as norec_apd"));

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglrencana','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('so.tglrencana','<=', $tgl);
        }
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noReg']);
        }
        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('pas.nocm','ilike','%'. $request['noCm']);
        }
        $data = $data->where('so.statusenabled',true);
        $data = $data->where('so.objectkelompoktransaksifk',123);
        $data = $data->orderBy('so.tglrencana');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataPasienPulang(Request $request) {
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $dataAwal = DB::select(DB::raw("select pd.objectruanganlastfk,ru.namaruangan,rpp.objectkamarfk,km.namakamar,
		            rpp.objecttempattidurfk,tt.reportdisplay as nomorbed,rpp.objectkelasfk,kls.namakelas
                    from pasiendaftar_t as pd
                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec and pd.objectruanganlastfk = rpp.objectruanganfk
                    INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                    INNER JOIN kamar_m as km on km.id = rpp.objectkamarfk
                    INNER JOIN tempattidur_m as tt on tt.id = rpp.objecttempattidurfk
                    INNER JOIN kelas_m as kls on kls.id = rpp.objectkelasfk
                    where pd.norec=:norec"),
            array(
                'norec' => $request['norec_pd'],
            )
        );

        $dataRencana = DB::select(DB::raw("select rpp.objectstatuskeluarrencanafk,sk.statuskeluar,rpp.objecthubungankeluargaambilpasienfk,hk.hubungankeluarga,
                                  rpp.objectstatuspulangrencanafk,sp.statuspulang,rpp.namalengkapambilpasien,rpp.tglkeluarrencana,rpp.objectkondisipasienfk,
			                      kp.kondisipasien
                        from strukorder_t as so
                        INNER JOIN orderpelayanan_t as op on op.noorderfk = so.norec
                        INNER JOIN registrasipelayananpasien_t as rpp on rpp.strukorderfk = so.norec
                        INNER JOIN statuskeluar_m as sk on sk.id = rpp.objectstatuskeluarrencanafk
                        INNER JOIN kondisipasien_m as kp on kp.id = objectkondisipasienfk
                        INNER JOIN hubungankeluarga_m as hk on hk.id = rpp.objecthubungankeluargaambilpasienfk
                        INNER JOIN statuspulang_m as sp on sp.id = rpp.objectstatuspulangrencanafk
                        where so.norec=:norec"),
            array(
                'norec' => $request['norec_so'],
            )
        );

        $result = array(
            'dataawal' => $dataAwal,
            'datarencana' => $dataRencana,
            'datalogin' => $dataLogin,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataComboNEW(Request $request)
    {

        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix'));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap){
            $kdDepartemenRawatInap []=  (int)$itemRanap;
        }

        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenRawatJalanFix'));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }

        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatInap)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataRuanganJalan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataAsalRujukan = \DB::table('asalrujukan_m as as')
            ->select('as.id','as.asalrujukan')
            ->where('as.statusenabled', true)
            ->orderBy('as.asalrujukan')
            ->get();


        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.namakelas')
            ->where('kl.statusenabled', true)
            ->orderBy('kl.namakelas')
            ->get();

        $dataKamar = \DB::table('kamar_m as kmr')
            ->select('kmr.id', 'kmr.namakamar')
            ->where('kmr.statusenabled', true)
            ->orderBy('kmr.namakamar')
            ->get();

        $dataHubunganPeserta = \DB::table('hubunganpesertaasuransi_m as hp')
            ->select('hp.id', 'hp.hubunganpeserta')
            ->where('hp.statusenabled', true)
            ->orderBy('hp.hubunganpeserta')
            ->get();

        $jenisPelayanan = \DB::table('jenispelayanan_m as jp')
            ->select('jp.kodeinternal as id', 'jp.jenispelayanan')
            ->where('jp.statusenabled', true)
            ->orderBy('jp.jenispelayanan')
            ->get();
        $pekerjaan = DB::table('pekerjaan_m')
            ->select('id','pekerjaan')
            ->where('statusenabled',true)
            ->get();

        $result = array(
            'ruanganranap' => $dataRuanganInap,
            'ruanganrajal' => $dataRuanganJalan,
            'kelompokpasien' => $dataKelompok,
            'kelas' => $dataKelas,
            'kamar' => $dataKamar,
            'asalrujukan' => $dataAsalRujukan,
            'hubunganpeserta' => $dataHubunganPeserta,
            'jenispelayanan' => $jenisPelayanan,
            'pekerjaan' => $pekerjaan,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getComboDokterPart(Request $request)
    {
        $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter');
        $req = $request->all();
        $data = \DB::table('pegawai_m')
            ->select('*')
            ->where('statusenabled', true)
            ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
            ->orderBy('namalengkap');

        if(isset($req['namalengkap']) &&
            $req['namalengkap']!="" &&
            $req['namalengkap']!="undefined"){
            $data = $data->where('namalengkap','ilike','%'. $req['namalengkap'] .'%' );
        };
        if(isset($req['idpegawai']) &&
            $req['idpegawai']!="" &&
            $req['idpegawai']!="undefined"){
            $data = $data->where('id', $req['idpegawai'] );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }



    public function getPasienDetailTea (Request $request){
        $dataLogin = $request->all();
        $data = \DB::table('pasien_m as pm')
            ->leftJoin('statusperkawinan_m as sp','sp.id','=','pm.objectstatusperkawinanfk')
            ->leftJoin('agama_m as ag','ag.id','=','pm.objectagamafk')
            ->leftJoin('pekerjaan_m as pk','pk.id','=','pm.objectpekerjaanfk')
            ->leftJoin('pendidikan_m as pen','pen.id','=','pm.objectpendidikanfk')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->leftJoin('propinsi_m as prop','prop.id','=','alm.objectpropinsifk')
            ->leftJoin('golongandarah_m as gd','gd.id','=','pm.objectgolongandarahfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
            ->select(DB::raw("pm.nocm,pm.namapasien,pm.tempatlahir,pm.tgllahir,
                     case when sp.statusperkawinan is null then '-' else sp.statusperkawinan end as statusperkawinan,
                     case when ag.agama is null then '-' else ag.agama end as agama,
                     case when pk.pekerjaan is null then '-' else pk.pekerjaan end as pekerjaan,
                     alm.alamatlengkap,alm.namadesakelurahan,alm.kecamatan,
                     alm.kotakabupaten,prop.namapropinsi,alm.kodepos,
                     case when pm.notelepon is null then '-' else pm.notelepon end as notelepon,
                     case when pm.noidentitas is null then '-' else pm.noidentitas end as noidentitas,
                     case when gd.golongandarah is null then '-' else gd.golongandarah end as golongandarah,
                     case when pen.pendidikan is null then '-' else pen.pendidikan end as pendidikan,
                     case when pm.noasuransilain is null then '-' else  pm.noasuransilain end as noasuransilain,
                     case when pm.nobpjs is null then '-' else pm.nobpjs end as nobpjs,jk.jeniskelamin,
                     pm.penanggungjawab,pm.hubungankeluargapj,pm.pekerjaanpenangggungjawab,pm.ktppenanggungjawab,
                     pm.alamatrmh,pm.alamatktr"))
            ->where('pm.nocm', $request['nocm'])
            ->first();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getAntrianPasienRanap(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join('ruangan_m as ru1','ru1.id','=','apd.objectruanganasalfk')
            ->select('pd.noregistrasi','pd.tglregistrasi','pd.norec as norec_pd','apd.norec as norec_apd',
                'apd.objectruanganasalfk','ru1.namaruangan as ruangasal','ru1.objectdepartemenfk as deptasal',
                'apd.objectruanganfk','ru.namaruangan','ru.objectdepartemenfk','apd.objectkamarfk','apd.nobed');
        $filter = $request->all();
        if(isset($filter['noReg']) && $filter['noReg'] != "" && $filter['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi','=',$filter['noReg']);
        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveBatalRanap(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();

        $norecPd='';
        // $resep = [];
        try {
            foreach ($request['data'] as $item) {
                $norecPd=$item['norec_pd'];
                if ($item['objectdepartemenfk'] == 16){
                    $data1 = PasienDaftar::where('norec', $item['norec_pd'])
                        ->update([
                                'objectruanganlastfk' => $item['objectruanganasalfk'],
                                'tglpulang' => $item['tglregistrasi']
                            ]
                        );
                    $dataRpp = RegistrasiPelayananPasien::where('noregistrasifk', $item['norec_pd'])->delete();
                    $data2 = PelayananPasienPetugas::where('nomasukfk', $item['norec_apd'])->delete();
                    $data3 = PelayananPasienDetail::where('noregistrasifk', $item['norec_apd'])->delete();
                    $data4 = PelayananPasien::where('noregistrasifk', $item['norec_apd'])->delete();

                    // $sr = StrukResep::where('pasienfk',$item['norec_apd'])->first();
                    // if(!empty($sr)){
                    //      $resep = array(
                    //         'noresep' => $sr->noresep,
                    //      );

                    // }
                    // $apd = AntrianPasienDiperiksa::where('norec',$item['norec_apd'])
                    //      ->whereNull('tglkeluar')
                    //      ->first();

                    // if(isset($item['nobed']) && $item['nobed'] !='null' && !empty($apd) ){
                    //     //update statusbed jadi Kosong
                    //      TempatTidur::where('id',$item['nobed'])->update(['objectstatusbedfk'=>2]);
                    // }


                    $data6 = AntrianPasienDiperiksa::where('norec',$item['norec_apd'])->delete();
                }
                //## Logging User
                $newId = LoggingUser::max('id');
                $newId = $newId +1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile= 0;
                $logUser->statusenabled=true;
                $logUser->jenislog = 'Batal Ranap Pasien '.$item['noregistrasi'].' Dari Ruangan ' . $item['namaruangan'] ;
                $logUser->noreff =$norecPd;
                $logUser->referensi='norec pasiendaftar';
                $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                $logUser->tanggal = $tglAyeuna;
                $logUser->save();
            }


            $transStatus = 'true';
            $transMessage = "Batal Ranap Berhasil!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Ranap gagal";
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
                // "resep" => $resep,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function updateJenisKelaminPasien(Request $request){
        try {

            $dataApr = Pasien::where('nocm', $request['norm'])
                ->update([
                    'objectjeniskelaminfk' => $request['jeniskelamin'],
                ]);

            $transStatus = 'true';
        }catch (\Exception $e) {
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
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function cekPasienBayar($nocm) {
        $data = DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            // ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            // ->join('strukpelayanan_t as sp','sp.norec','=','pd.nostruklastfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            // ->leftjoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select('pd.noregistrasi','pd.tglregistrasi')
            ->where('pd.objectkelompokpasienlastfk','=',1)
            ->where('pd.statusenabled',true)
            ->whereNull('pp.strukfk')
            // ->whereNull('pd.nosbmlastfk')
            ->where('ps.nocm',$nocm)
            ->whereRaw("format(pd.tglregistrasi,'yyyy-MM-dd') > '2019-06-20'")
            ->get();
        $status = true;
        $nocm = '';
        $tglregistrasi = '';
        if(count($data)> 0){
            // foreach ($data as $item) {
            //     if($item->nosbmlastfk ==  null){
            $status = false;
            $tglregistrasi =$data[0]->tglregistrasi;
            // $nocm = $item->nocm ;
            // break;
            // }
            # code...
            // }
        }

        $result = array(
            'status' => $status,
            'nocm' => $nocm,
            'data'=>$data,
            'tglregistrasi' => $tglregistrasi,
            'message' => 'ramdan@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarRiwayatRegistrasi( Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->join('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select(DB::raw("pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pd.objectruanganlastfk,ru.namaruangan,
			                  pd.objectpegawaifk,pg.namalengkap as namadokter,pd.tglpulang,ru.objectdepartemenfk,
			                  CASE when ru.objectdepartemenfk in (16,25,26) then 1 else 0 end as statusinap"))
            ->whereNull('br.pasiendaftarfk');

//        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
//            $data = $data->where('ps.tgllahir','>=', $request['tglLahir'].' 00:00');
//        };
//        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
//            $data = $data->where('ps.tgllahir','<=', $request['tglLahir'].' 23:59');
//        };
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['namaPasien'] .'%');
        };
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $data = $data->where('pd.objectruanganlastfk','=', $request['idRuangan']);
        };

        $data = $data->where('ps.statusenabled',true);
        $data = $data->orderBy('pd.tglregistrasi');
        $data=$data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function updateKamar(Request $request){
        DB::beginTransaction();
        try {
            $antrians = AntrianPasienDiperiksa::where('noregistrasifk',$request['norec_pd'])
                ->where('objectruanganfk',$request['ruanganlastfk'])
                ->whereNull('tglkeluar')
                ->first();
            $rpp = RegistrasiPelayananPasien::where('noregistrasifk',$request['norec_pd'])
                ->where('objectruanganfk',$request['ruanganlastfk'])
                ->whereNull('tglkeluar')
                ->first();

//			if(!empty($antrians)){
            AntrianPasienDiperiksa::where('norec',$antrians->norec)
                ->update(
                    [
                        'objectkamarfk' => $request['objectkamarfk'] ,
                        'nobed' => $request['nobed'] ,
                    ]
                );

//			}
//			if(!empty($rpp)){
            RegistrasiPelayananPasien::where('norec',$rpp->norec)
                ->update(
                    [
                        'objectkamarfk' => $request['objectkamarfk'] ,
                        'nobed' => $request['nobed'] ,
                    ]
                );
//			}


            //update statusbed jadi Isi
            TempatTidur::where('id',$request['nobed'])->update(['objectstatusbedfk'=>1]);

            if(isset($request['nobedasal']) && $request['nobedasal'] !='null' && $request['nobedasal'] != $request['nobed'] ){
                //update statusbed jadi Kosong
                TempatTidur::where('id',$request['nobedasal'])->update(['objectstatusbedfk'=>2]);
            }

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Update Kamar Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Update Kamar Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveBatalRanapRev(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();


        try {
            $norecPd = $request['data']['norec_pd'];
            $apd = \DB::table('antrianpasiendiperiksa_t as apd')
                ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
                ->select('ru.objectdepartemenfk','apd.norec','apd.objectruanganfk')
                ->where('apd.noregistrasifk',$norecPd)
                ->get();
            $ruanganAsal = \DB::table('registrasipelayananpasien_t as rpp')
                ->join('ruangan_m as ru','ru.id','=','rpp.objectruanganfk')
                ->select('ru.objectdepartemenfk','rpp.norec as norec_rpp','rpp.objectruanganfk','rpp.objectruanganasalfk','rpp.keteranganlainnyarencana')
                ->where('rpp.noregistrasifk',$norecPd)
                ->whereNotIn('ru.objectdepartemenfk',[27,3])
                ->where('rpp.keteranganlainnyarencana','Mutasi Gawat Darurat')
                ->first();


            foreach ($apd as $item){
                if($item->objectdepartemenfk == 16){
                    $pelayanan = PelayananPasien::where('noregistrasifk',$item->norec)->first();
                    if(!empty($pelayanan)){
                        $transMessage = 'Pasien sudah mendapatkan pelayanan, hapus pelayanan dulu !';
                        $pel = array('norec_pp' => $pelayanan->norec);
                        return $this->setStatusCode(400)->respond($pel, $transMessage);
                    }

                    if(!empty($ruanganAsal)){
                        $updatePD = PasienDaftar::where('norec', $norecPd)
                            ->update([
                                    'objectruanganlastfk' => $ruanganAsal->objectruanganasalfk,
                                    'tglpulang' => $request['data']['tglregistrasi'],
                                    'objectkelasfk' => 6,
                                ]
                            );
                    }else{
                        $updatePD = PasienDaftar::where('norec', $norecPd)
                            ->update([
                                    'statusenabled'=>false,
                                    'tglpulang' => $request['data']['tglregistrasi']
                                ]
                            );
                    }
                    $delRPP = RegistrasiPelayananPasien::where('noregistrasifk', $norecPd)->delete();
                    $delAPD = AntrianPasienDiperiksa::where('noregistrasifk', $norecPd)
                        ->where('objectruanganfk',$item->objectruanganfk)
                        ->delete();

                    if(isset($request['data']['nobed']) && $request['data']['nobed'] !='null'  ){
                        //update statusbed jadi Kosong
                        TempatTidur::where('id',$request['data']['nobed'])->update(['objectstatusbedfk'=>2]);
                    }
                }
            }

            ## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= 11;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Rawat Inap';
            $logUser->noreff = $norecPd;
            $logUser->referensi='norec pasiendaftar';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Batal Rawat Inap dengan No Registrasi ' . $request['data']['noregistrasi'].' dari ruangan '.$request['data']['namaruangan'];
            $logUser->save();

//
            $transStatus = 'true';
            $transMessage = "Batal Rawat Inap Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Rawat Inap gagal";
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
                // "resep" => $resep,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveBatalPindahRuangan(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();

        try {
            $norecPd = $request['norec_pd'];
            $apd = \DB::table('antrianpasiendiperiksa_t as apd')
                ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
                ->select('ru.objectdepartemenfk','apd.norec','apd.objectruanganfk','apd.objectruanganasalfk','apd.objectkelasfk','apd.nobed','apd.objectkamarfk')
                ->where('apd.noregistrasifk',$norecPd)
                ->get();
            $rpp = RegistrasiPelayananPasien::where('noregistrasifk',$norecPd)
                ->where('objectruanganfk',$request['objectruanganlastfk'])
                ->first();

            $ruanganAsal = \DB::table('registrasipelayananpasien_t as rpp')
                ->join('ruangan_m as ru','ru.id','=','rpp.objectruanganfk')
                ->select('ru.objectdepartemenfk','rpp.norec as norec_rpp','rpp.objectruanganfk','rpp.objectruanganasalfk','rpp.objectkelasfk',
                    'rpp.objectkamarfk','rpp.objecttempattidurfk')
                ->where('rpp.noregistrasifk',$norecPd)
                ->where('rpp.objectruanganfk',$rpp->objectruanganasalfk)
                ->whereNotIn('ru.objectdepartemenfk',[27,3])
                ->orderBy('rpp.tglpindah','desc')
                ->first();


            foreach ($apd as $item){
                if($item->objectdepartemenfk == 16 && $request['objectruanganlastfk'] == $item->objectruanganfk  ) {
                    $pelayanan = PelayananPasien::where('noregistrasifk', $item->norec)->first();
                    if (!empty($pelayanan)) {
                        $transMessage = 'Pasien sudah mendapatkan pelayanan, hapus pelayanan dulu !';
                        $pel = array('norec_pp' => $pelayanan->norec);
                        return $this->setStatusCode(400)->respond($pel, $transMessage);
                    }else{
                        $updatePD = PasienDaftar::where('norec', $norecPd)
                            ->update([
                                    'objectruanganlastfk' => $ruanganAsal->objectruanganfk,
                                    'objectkelasfk' => $ruanganAsal->objectkelasfk,
                                ]
                            );

                        $rpp = RegistrasiPelayananPasien::where('noregistrasifk',$norecPd)
                            ->where('objectruanganfk',$request['objectruanganlastfk'])
                            ->whereNull('tglpindah')
                            ->delete();
                        $delAPD = AntrianPasienDiperiksa::where('noregistrasifk', $norecPd)
                            ->where('objectruanganfk',$request['objectruanganlastfk'])
                            ->whereNull('tglkeluar')
                            ->delete();
                        $updateAPDs = AntrianPasienDiperiksa::where('noregistrasifk', $norecPd)
                            ->where('objectruanganfk',$ruanganAsal->objectruanganfk)
                            ->wherenotnull('tglkeluar')
                            ->update(
                                [ 'tglkeluar' => null]
                            );
                        $updateRpp = RegistrasiPelayananPasien::where('noregistrasifk', $norecPd)
                            ->where('objectruanganfk',$ruanganAsal->objectruanganfk)
                            ->wherenotnull('tglpindah')
                            ->update(
                                [ 'tglpindah' => null]
                            );
                        if(isset($request['nobed']) && $request['nobed'] !='null'  ){
                            //update statusbed jadi Kosong
                            TempatTidur::where('id',$request['nobed'])->update(['objectstatusbedfk'=>2]);
                        }
                        if( $ruanganAsal->objecttempattidurfk !='null'  ){
                            //update statusbed jadi Kosong
                            TempatTidur::where('id', $ruanganAsal->objecttempattidurfk)->update(['objectstatusbedfk'=>1]);
                        }
                        break;
                    }
                }
            }

            ## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= 11;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Pindah Ruangan';
            $logUser->noreff = $norecPd;
            $logUser->referensi='norec pasiendaftar';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Batal Pindah Ruangan dengan No Registrasi ' . $request['noregistrasi'].' dari ruangan '.$request['namaruangan'];
            $logUser->save();

//
            $transStatus = 'true';
            $transMessage = "Batal Pindah Ruangan Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Tidak bisa Batal Pindah Ruangan";
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
                // "resep" => $resep,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataComboAsuransiPasien(Request $request)
    {

        $dataAsalRujukan = \DB::table('asalrujukan_m as as')
            ->select('as.id','as.asalrujukan')
            ->where('as.statusenabled', true)
            ->orderBy('as.asalrujukan')
            ->get();


        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.namakelas')
            ->where('kl.statusenabled', true)
            ->orderBy('kl.namakelas')
            ->get();



        $dataHubunganPeserta = \DB::table('hubunganpesertaasuransi_m as hp')
            ->select('hp.id', 'hp.hubunganpeserta')
            ->where('hp.statusenabled', true)
            ->orderBy('hp.hubunganpeserta')
            ->get();


        $result = array(
            'kelompokpasien' => $dataKelompok,
            'kelas' => $dataKelas,
            'asalrujukan' => $dataAsalRujukan,
            'hubunganpeserta' => $dataHubunganPeserta,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public  function cekPasienBPJSDaftar(Request $request)
    {
        $tgl =$request['tglRegis'];
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->select('pd.norec','pd.noregistrasi')
            ->where('ps.nocm',$request['nocm'])
            ->whereRaw("format(pd.tglregistrasi,'yyyy-MM-dd') = '$tgl'")
            ->where('pd.statusenabled',true)
            ->where('pd.objectkelompokpasienlastfk',2)
            ->get();


        $result = array(
            'data' =>  $data,
            'msg' => 'er@epic'
        );
        return $this->respond($result);

    }
    public function getDataPegawaiAll(Request $request) {
        $req=$request->all();
        $dataPenulis = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap','st.nip_pns')
            ->where('st.statusenabled',true)
//            ->where('st.objectjenispegawaifk','1')
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataPenulis = $dataPenulis->where('namalengkap','ilike', '%'.$req['filter']['filters'][0]['value'].'%' );
        };
        if(isset($request['pgid']) && $request['pgid']!='') {
            $dataPenulis = $dataPenulis->where('id',$request['pgid'] );
        }
        $dataPenulis = $dataPenulis->take(10);
        $dataPenulis = $dataPenulis->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
                'nip_pns' => $item->nip_pns,
            );
        }
        return $this->respond($dataPenulis2);
    }
    public function IdentifikasiBuktiLayanan(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->isbuktilayanan = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'isbuktilayanan' => true,
                ]);
        }


        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }

    public function IdentifikasiSummaryList(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->issummarylist = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'issummarylist' => true,
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }

    public function IdentifikasiTracer(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->istracer = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'istracer' => true,
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }

    public function IdentifikasiSEP(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->issep = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'issep' => true,
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }

    public function IdentifikasiLabel(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->islabel = $request['islabel'];
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'islabel' => $request['islabel'],
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }

    public function IdentifikasiKartuPasien(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->iskartupasien = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'iskartupasien' => true,
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }

    public function IdentifikasiRMK(Request $request)
    {
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])->first();
        if($pasien == ''){
            $ip = new IdentifikasiPasien();
            $norec = $ip->generateNewId();
            $ip->norec = $norec;
            $ip->kdprofile = 0;
            $ip->statusenabled = true;
            $ip->noregistrasifk = $request['norec_pd'];
            $ip->isrmk = true;
            $ip->save();
        }else{
            $ip = IdentifikasiPasien::where('noregistrasifk', $request['norec_pd'])
                ->update([
                    'isrmk' => true,
                ]);
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'CEPOT'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'CEPOT'
            );
        }
        return $this->respond($result);
    }
    public function saveDiagnosaPasienRMK(Request $request)
    {
        $dataLogin = $request->all();
        DB::beginTransaction();
//        try{
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        if ($dataLogin['norec_dp'] == '') {
            $dataDiagnosa = new DiagnosaPasien();
            $dataDiagnosa->norec = $dataDiagnosa->generateNewId();
            $dataDiagnosa->kdprofile = 0;
            $dataDiagnosa->statusenabled = true;

        } else {
            $dataDiagnosa = DiagnosaPasien::where('norec', $dataLogin['norec_dp'])->first();
        }
        $dataDiagnosa->noregistrasifk = $dataLogin['noregistrasifk'];
        $dataDiagnosa->ketdiagnosis = 'Diagnosa Pasien';
        $dataDiagnosa->tglregistrasi = $dataLogin['tglpendaftaran'];
        $dataDiagnosa->tglpendaftaran = $dataLogin['tglpendaftaran'];

        try {
            $dataDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Diagnosa Baru";
        }


        if ($dataLogin['norec_dp'] == '' || $dataLogin['keterangan'] == '') {
            $dataDetailDiagnosa = new DetailDiagnosaPasien();
            $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
            $dataDetailDiagnosa->kdprofile = 0;
            $dataDetailDiagnosa->statusenabled = true;
            $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();

        } else {
            $dataDetailDiagnosa = DetailDiagnosaPasien::where('objectdiagnosapasienfk', $dataLogin['norec_dp'])->first();
        }

        $dataDetailDiagnosa->noregistrasifk = $dataLogin['noregistrasifk'];
        $dataDetailDiagnosa->tglregistrasi = $dataLogin['tglpendaftaran'];
        $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
        $dataDetailDiagnosa->objectdiagnosafk = $dataLogin['objectdiagnosafk'];
        $dataDetailDiagnosa->objectdiagnosapasienfk = $dataDiagnosa->norec;
        $dataDetailDiagnosa->objectjenisdiagnosafk = $dataLogin['objectjenisdiagnosafk'];
        $dataDetailDiagnosa->tglinputdiagnosa = $dataLogin['tglinputdiagnosa'];
        $dataDetailDiagnosa->keterangan = $dataLogin['keterangan'];
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
                'data' => $dataLogin,
                'as' => 'egie@ramdan',
            );
        } else {
            $transMessage = "Data Gagal Disimpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'data' => $dataLogin,
                'as' => 'egie@ramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($dataLogin);
    }
    public function updateNoCmInEmrPasienReg(Request $request)
    {
        DB::beginTransaction();
        try {
            $dataKelas = Kelas::where('id', $request['kelas'])
                ->where('statusenabled',true)
                ->select('id','namakelas')
                ->first();

            if (isset($request['nocm']) || $request['nocm'] != "-" | $request['nocm'] != ""){
                $dataUpt = EMRPasien::where('noemr', $request['noemr'])
                    ->update([
                        'nocm' => $request['nocm'],
                    ]);
            }

            $transStatus = 'true';
        }catch (\Exception $e) {
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
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function ConfirmOnline(Request $request)
    {
//        $data=$request['data'];
//        return $this->respond($data);
        try {
//            foreach ($data as $item) {
            $dataApr = AntrianPasienRegistrasi::where('noreservasi', $request['noreservasi'])
                ->update([
                    'isconfirm' => true,
//                        'objectstatusbarang'=> 2
                ]);
//            }

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Status Gagal";
        }


        if ($transStatus == 'true') {
            $transMessage = "Update Status Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                    "as" => 'cepot',
            );
        } else {
            $transMessage = "Update Status Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                    "as" => 'Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updatePdInEmrPasien(Request $request)
    {
        DB::beginTransaction();
        try {
            $dataKelas = Kelas::where('id', $request['kelas'])
                ->where('statusenabled',true)
                ->select('id','namakelas')
                ->first();

            $dataKelompokPasien = KelompokPasien::where('id',$request['kelompokpasien'])
                ->where('statusenabled',true)
                ->select('id','kelompokpasien')
                ->first();

//            if ($dataKelas == "" && $dataKelompokPasien ==""){
            if (isset($request['norecpd']) || $request['norecpd'] != "-" | $request['norecpd'] != ""){
                $dataUpt = EMRPasien::where('noemr', $request['noemr'])
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
        }catch (\Exception $e) {
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
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getComboPasienPerjanjian(Request $request)
    {

        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenReservasiOnline'));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }

        $dataRuanganJalan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
            ->orderBy('ru.namaruangan')
            ->get();
        $jk = \DB::table('jeniskelamin_m')
            ->select('id','jeniskelamin')
            ->where('statusenabled', true)
            ->orderBy('jeniskelamin')
            ->get();
        $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter');

        $dkoter = \DB::table('pegawai_m')
            ->select('*')
            ->where('statusenabled', true)
            ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
            ->orderBy('namalengkap')
            ->get();

        $kelompokPasien = \DB::table('kelompokpasien_m')
            ->select('id','kelompokpasien')
            ->where('statusenabled', true)
            ->orderBy('kelompokpasien')
            ->get();
        $result = array(
            'ruanganrajal' => $dataRuanganJalan,
            'jeniskelamin' => $jk,
            'dokter' => $dkoter,
            'kelompokpasien' => $kelompokPasien,
            'message' => 'ramdan@epic',
        );

        return $this->respond($result);
    }

    public function getPasienPerjanjian(Request $request){

        $data = \DB::table('antrianpasienregistrasi_t as apr')
            ->leftJoin('pasien_m as pm','pm.id','=','apr.nocmfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','apr.objectruanganfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','apr.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kps','kps.id','=','apr.objectkelompokpasienfk')
            ->select('apr.norec','pm.nocm','apr.noreservasi','apr.tanggalreservasi','apr.objectruanganfk',
                'apr.objectpegawaifk','ru.namaruangan','apr.isconfirm','pg.namalengkap as dokter',
                'apr.notelepon','pm.namapasien','apr.namapasien','apr.objectkelompokpasienfk','kps.kelompokpasien',
                'apr.tglinput',
                DB::raw('(case when pm.namapasien is null then apr.namapasien else pm.namapasien end) as namapasien, 
                (case when apr.isconfirm=\'true\' then \'Confirm\' else \'Reservasi\' end) as status')
            )
            ->where('apr.noreservasi','<>','-')
            ->where('apr.statusenabled',true)
            ->whereNotNull('apr.noreservasi');

        $filter = $request;
        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('apr.tanggalreservasi', '>=', $filter['tglAwal']. " 00:00:00");
        }
        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir']. " 23:59:59" ;//. " 23:59:59";
            $data = $data->where('apr.tanggalreservasi', '<=', $tgl);
        }
        if(isset($filter['ruanganId']) && $filter['ruanganId'] != "" && $filter['ruanganId'] != "undefined") {
            $data = $data->where('ru.id','=',$filter['ruanganId']);
        }
        if(isset($filter['kdReservasi']) && $filter['kdReservasi'] != "" && $filter['kdReservasi'] != "undefined") {
            $data = $data->where('apr.noreservasi','=',$filter['kdReservasi']);
        }
        if(isset($filter['statusRev']) && $filter['statusRev'] == "Confirm" && $filter['statusRev'] == "Confirm" && $filter['statusRev'] == "Confirm") {
            $data = $data->where('apr.isconfirm','=',true);
        }
        if(isset($filter['statusRev']) && $filter['statusRev'] == "Reservasi" && $filter['statusRev'] == "Reservasi" && $filter['statusRev'] == "Reservasi") {
            $data = $data->whereNull('apr.isconfirm');
        }
        if(isset($filter['namapasienpm']) && $filter['namapasienpm'] != "" && $filter['namapasienpm'] != "undefined") {
            $data = $data->where('pm.namapasien','ilike','%'. $filter['namapasienpm'] .'%');
            // ->orWhere('apr.namapasien','ilike','%'. $filter['namapasienapr'] .'%');
        }

        // if(isset($filter['namapasienapr']) && $filter['namapasienapr'] != "" && $filter['namapasienapr'] != "undefined") {
        //     $data = $data->orWhere('apr.namapasien','ilike','%'. $filter['namapasienapr'] .'%');
        // }
        $data = $data->orderBy('apr.tanggalreservasi','asc');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'cepot',
        );
        return $this->respond($result);
    }

    public function getDiagnosaDaftarAntrian(Request $request){
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
    public function getDataPasienMauBatal(Request $request)
    {
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->Join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftJoin('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ps.namapasien', 'pd.objectruanganasalfk',
                'ru.namaruangan', 'br.norec as norec_br', 'apd.norec as norec_apd', 'pp.norec as norec_pp');

        if (isset($filter['noReg']) && $filter['noReg'] != "" && $filter['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $filter['noReg']);
        }
        $data = $data->orderBy('pd.noregistrasi');
        $data = $data->get();

        return $this->respond($data);
    }

    public function SimpanBatalPeriksa(Request $request){
        $dataLogin = $request->all();
        DB::beginTransaction();
        $TglPulang="";
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $transStatus = 'true';
        try {
            if ($request['norec_br'] == '') {
                $newBR = new BatalRegistrasi();
                $norec = $newBR->generateNewId();
                $newBR->norec = $norec;
                $newBR->kdprofile = 0;
                $newBR->statusenabled = true;
            } else {
                $newBR = BatalRegistrasi::where('pasiendaftarfk', $request['norec'])->first();
            }
            $newBR->alasanpembatalan = $request['alasanpembatalan'];
            $newBR->pasiendaftarfk = $request['norec'];
            $newBR->pegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();
            $newBR->pembatalanfk = $request['pembatalanfk'];
            $newBR->tanggalpembatalan = $request['tanggalpembatalan'];
            $newBR->save();
            $dataaa = PasienDaftar::where('norec', $request['norec'])->get();
            foreach ($dataaa as $item){
                $TglPulang=$item->tglpulang;
            }
//            return $this->respond($TglPulang == null);
            if ($TglPulang != null){

                $data2 = PasienDaftar::where('norec', $request['norec'])
                    ->update([
                        'kdprofile' => 0,
                        'statusenabled' => false,
                    ]);

            }else{

                $data2 = PasienDaftar::where('norec', $request['norec'])
                    ->update([
                        'kdprofile' => 0,
                        'statusenabled' => false,
                        'tglpulang' => $request['tanggalpembatalan']
                    ]);
            }
            $data2 = PasienDaftar::where('norec', $request['norec'])
                ->update([
                    'kdprofile' => 0,
                    'statusenabled' => false
//                    'tglpulang' => $request['tanggalpembatalan']
                ]);
            AntrianPasienDiperiksa::where('noregistrasifk', $request['norec'])
                ->whereNull('tglkeluar')
                ->update([
                    'statusenabled' =>false,
                    'tglkeluar' =>$request['tanggalpembatalan'],
                ]);



            $transMessage = "Batal Periksa Sukses!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Gagal Batal Periksa";
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
    public function pasienBatalPanggil(Request $request)
    {
        DB::beginTransaction();
        try {

            if ($request['norec_apd']!=null) {
                $ddddd = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
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

    public function deleteDiagnosaPasien(Request $request) {
        $dataLogin = $request->all();
        DB::beginTransaction();
        if ($request['diagnosa']['norec_dp'] != ''){
            try{
                $data1 = DetailDiagnosaPasien::where('objectdiagnosapasienfk', $request['diagnosa']['norec_dp'])->delete();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus= false;
            }
            try{
                $data2 = DiagnosaPasien::where('norec',$request['diagnosa']['norec_dp'])->delete();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus= false;
            }

        }
        if ($transStatus='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);
    }


    public function getDiagnosaPasienByNoreg(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('dg.id', 'pd.noregistrasi', 'pd.tglregistrasi', 'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd',
                'ddp.objectdiagnosafk', 'dg.kddiagnosa', 'dg.namadiagnosa', 'ddp.objectjenisdiagnosafk',
                'jd.jenisdiagnosa', 'dp.norec as norec_diagnosapasien', 'ddp.norec as norec_detaildpasien',
                'dp.ketdiagnosis', 'ddp.keterangan')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'apd.norec')
            ->leftJoin('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->leftJoin('diagnosa_m as dg', 'dg.id', '=', 'ddp.objectdiagnosafk')
            ->leftJoin('jenisdiagnosa_m as jd', 'jd.id', '=', 'ddp.objectjenisdiagnosafk');

        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noReg']);
        };

        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'Cepot',
        );
        return $this->respond($result);
    }

    public function getDokters(Request $request)
    {
        $dataLogin = $request->all();
        $dataDokters = \DB::table('pegawai_m as p')
            ->select('p.id','p.namalengkap')
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
    public function getPelayananPasienNonDetail(Request $request)
    {
        $data = \DB::table('pelayananpasien_t as pp')
            ->join ('antrianpasiendiperiksa_t as apd ','pp.noregistrasifk','=','apd.norec')
            ->join ('pasiendaftar_t as pd','apd.noregistrasifk','=','pd.norec')
            ->join ('produk_m as pr','pp.produkfk','=','pr.id')
            ->leftjoin ('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->join ('ruangan_m as ru', 'ru.id','=','apd.objectruanganfk')
            ->select('pp.norec as noRec','pp.strukfk as noRecStruk','pp.tglpelayanan as tglPelayanan',
                'pr.id as produkId','pp.hargasatuan as hargaSatuan','pp.harganetto as hargaNetto','pr.namaproduk as namaProduk',
                'djp.detailjenisproduk as detailJenisProduk','pp.jumlah','ru.namaruangan as namaRuangan')
            ->where('pd.norec',$request['norec_pd']);
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getJenisPelayananByNorecPd($norec_pd)
    {

        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.*')
            ->where('pd.norec',$norec_pd)
            ->first();


        return $this->respond($data);
    }
    public function getStatusClosePeriksa($noregistrasi)
    {
        $data  = PasienDaftar::where('noregistrasi',$noregistrasi)->first();
        $status = false;
        $tgl = null;
        if(!empty($data) && $data->isclosing != null){
            $status = $data->isclosing;
            $tgl = $data->tglclosing;
        }
        $result = array(
            'status'=> $status,
            'tglclosing'=> $tgl,
            'message' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
    public function getComboTindakanPendaftaran(Request $request)
    {
        $detailLog = $request->all();
        $jenisPelaksana = \DB::table('jenispetugaspelaksana_m as jpp')
            ->where('jpp.statusenabled', true)
            ->orderBy('jpp.jenispetugaspe')
            ->get();
        $pegawai = \DB::table('pegawai_m as pg')
            ->where('pg.statusenabled', true)
            ->orderBy('pg.namalengkap')
            ->get();

        $dataTarifAdminCito = \DB::table('settingdatafixed_m as rt')
            ->select('rt.namafield','rt.nilaifield')
            ->where('rt.statusenabled',true)
            ->where('rt.namafield','tarifadmincito')
            ->orderBy('rt.id')
            ->first();

        $result = array(
            'detaillogin' =>$detailLog,
            'jenispelaksana' => $jenisPelaksana,
            'pegawai' => $pegawai,
            'tarifcito' => $dataTarifAdminCito,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function getDaftarRegistrasiPasienOperator(Request $request)
    {
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            //            ->leftjoin('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->join('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
//            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
//            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('asuransipasien_m as asu', 'pa.objectasuransipasienfk', '=', 'asu.id')
            ->leftjoin('kelas_m as klstg','klstg.id','=','asu.objectkelasdijaminfk')
            ->select('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
                'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pa.norec as norec_pa', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'pa.nosep as nosep', 'br.norec as norec_br', 'pd.nostruklastfk','klstg.namakelas as kelasditanggung','kls.namakelas',
                'ps.tgllahir','ru.objectdepartemenfk')
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
//        $data = $data->groupBy('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi',
//            'ru.namaruangan', 'ps.namapasien',
//            'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap', 'pd.tglpulang', 'pd.statuspasien',
//            'pa.nosep', 'br.norec', 'pa.norec', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk', 'pd.objectruanganlastfk',
//            'pd.nostruklastfk', 'ps.tgllahir');
//        $data = $data->take($filter['jmlRows']);
        $data = $data->get();
        return $this->respond($data);
    }
    public function getDataComboOperator(Request $request)
    {

        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.id', '18')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->whereIn('dept.id', [18, 16])
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as ru')
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
    public function updateTanggalPulang(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $pasienDaftar = PasienDaftar::where('noregistrasi',$request['noregistrasi'])->first();
        try {
            $data=$dataAsalRujukan = \DB::table('pasiendaftar_t as pd')
                ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
                ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
                ->select('ru.objectdepartemenfk')
                ->where('pd.noregistrasi',$request['noregistrasi'])
//                ->whereIn('ru.objectdepartemenfk',array(16,17,35))
                ->first();

            $stt=false;
            if ($data->objectdepartemenfk==16){
                $stt=true;
            }
            if ($data->objectdepartemenfk==17){
                $stt=true;
            }
            if ($data->objectdepartemenfk==35){
                $stt=true;
            }
            if ($data->objectdepartemenfk==26){
                $stt=true;
            }


            if ($stt==false){
                $ddddd=[];
                $transMessage = "Hanya Pasien Rawat Inap yang Bisa Batal Pulang!";
            }else{
                $antrian = AntrianPasienDiperiksa::where('noregistrasifk',$pasienDaftar->norec )
                    ->where('objectruanganfk',$pasienDaftar->objectruanganlastfk)
                    ->orderBy('tglmasuk','desc')
                    ->first();
                $registrasiPelPas = RegistrasiPelayananPasien::where('noregistrasifk',$pasienDaftar->norec )
                    ->where('objectruanganfk',$pasienDaftar->objectruanganlastfk)
                    ->orderBy('tglmasuk','desc')
                    ->first();

                if ($request['tglpulang']== 'null'){
                    $ddddd=PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                        ->update([
                                'tglpulang' => null]
//                        'tglpulang' => $request['tglpulang']]
                        );
                    $apd = AntrianPasienDiperiksa::where('norec',$antrian->norec)
                        ->update([ 'tglkeluar' => null ] );
                    $rpp = RegistrasiPelayananPasien::where('norec',$registrasiPelPas->norec)
                        ->update([ 'tglkeluar' => null ] );

                }else{
                    $ddddd=PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                        ->update([
                                'tglpulang' => $request['tglpulang']]
                        );

                    $apd = AntrianPasienDiperiksa::where('norec',$antrian->norec)
                        ->update([ 'tglkeluar' => null ] );
                    $rpp = RegistrasiPelayananPasien::where('norec',$registrasiPelPas->norec)
                        ->update([ 'tglkeluar' => null ] );
                    //## Logging User
                    $newId = LoggingUser::max('id');
                    $newId = $newId +1;
                    $logUser = new LoggingUser();
                    $logUser->id = $newId;
                    $logUser->norec = $logUser->generateNewId();
                    $logUser->kdprofile= 0;
                    $logUser->statusenabled=true;
                    $logUser->jenislog = 'Batal Pulang Pasien';
                    $logUser->noreff =$request['noregistrasi'];
                    $logUser->referensi='noregistrasi Pasien';
                    $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                    $logUser->tanggal = $tglAyeuna;
                    $logUser->save();
                }
                $transMessage = "Sukses";
            }




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
                "message"  => $transMessage,
                "struk" => $ddddd,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateNoSEP(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {

            $data2 = PemakaianAsuransi::where('noregistrasifk', $request['norec'])
                ->update([
                    'nokepesertaan' => $request['nokepesertaan'],
                    'nosep' => $request['nosep']
                ]);
            $transMessage = "Update Pemakaian Asuransi berhasil!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Pemakaian Asuransi gagal";
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
    public function getAPD(Request $request)
    {
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->select('apd.norec as norec_apd', 'ps.nocm', 'ps.id as nocmfk', 'ps.namapasien', 'pd.noregistrasi',
                'apd.objectruanganfk as id','ru.objectdepartemenfk',
                'ru.namaruangan', 'apd.tglregistrasi', 'kls.namakelas', 'apd.objectruanganasalfk')
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->orderBy('pd.objectruanganlastfk')
            ->get();

        $result = array(
            'ruangan' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getAccNumberRadiologi( Request $request) {
        $data = \DB::table('ris_order as ro')
            ->select('ro.*')
            ->where('ro.order_no',$request['noOrder']);

        $data=$data->get();

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function saveMutasiPasien(Request $request) {

        DB::beginTransaction();


        if ($request['tglpulang']!= 'null'){
            $ddddd=PasienDaftar::where('norec', $request['noRecPasienDaftar'])
                ->update([
                        'tglpulang' => null,
                        'objectruanganlastfk' => $request['ruangan']['id'],
                        'objectkelompokpasienlastfk' => $request['kelompokPasien']['id'],
                        'objectpegawaifk' => $request['pegawai']['id'],
                        'objectkelasfk' => $request['kelas']['id'],
                        'nostruklastfk' => null,
                        'nosbmlastfk' => null

                    ]
                );
        }

        try{

            $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk',$request['ruangan']['id'])
                ->where('tglregistrasi', '>=', $request['tglRegisDateOnly'].' 00:00')
                ->where('tglregistrasi', '<=', $request['tglRegisDateOnly'].' 23:59')
                ->count('norec');
            $noAntrian = $countNoAntrian + 1;

            $dataAPD = new AntrianPasienDiperiksa();
//            $dataAPD->id = $newId;
            $dataAPD->kdprofile = 0;
            $dataAPD->statusenabled = true;
            $dataAPD->norec =  $dataAPD->generateNewId();
            $dataAPD->objectasalrujukanfk =  $request['asalRujukan']['id'];
            $dataAPD->objectkamarfk = $request['kamar']['id'];
            $dataAPD->objectkasuspenyakitfk = null;
            $dataAPD->objectkelasfk = $request['kelas']['id'];
            $dataAPD->noantrian = $noAntrian; //count tgl pasien perruanga
            $dataAPD->nobed = $request['nomorTempatTidur']['id'];
//            $dataAPD->nomasuk = '';
            $dataAPD->noregistrasifk = $request['noRecPasienDaftar'];
            $dataAPD->objectpegawaifk = $request['pegawai']['id'];
//            $dataAPD->prefixnoantrian = null;
            $dataAPD->objectruanganfk = $request['ruangan']['id'];
            $dataAPD->statusantrian = 0;
            $dataAPD->statuskunjungan = $request['statusPasien'];
            $dataAPD->statuspasien = 1;
//            $dataAPD->statuspenyakit =null;
//            $dataAPD->objectstrukorderfk = null;
//            $dataAPD->objectstrukreturfk = null;
            $dataAPD->tglregistrasi =  $request['tglRegistrasi'];
//            $dataAPD->tgldipanggildokter = null;
//            $dataAPD->tgldipanggilsuster = null;
            $dataAPD->objectruanganasalfk = $request['objectruanganasalfk'];
            $dataAPD->tglkeluar = null;
            $dataAPD->tglmasuk =$request['tglRegistrasi'];
            $dataAPD->israwatgabung = null;

            $dataAPD->save();

            //update statusbed jadi Isi
            TempatTidur::where('id',$request['nomorTempatTidur']['id'])->update(['objectstatusbedfk'=>1]);

            $dataRPP = new RegistrasiPelayananPasien();
            $dataRPP->norec = $dataRPP->generateNewId();;
            $dataRPP->kdprofile = 0;
            $dataRPP->statusenabled = true;
            $dataRPP->objectasalrujukanfk = $request['asalRujukan']['id'];
//            $dataRPP->objecthasiltriasefk =null;
//            $dataRPP->israwatgabung = null;
            $dataRPP->objectkamarfk = $request['kamar']['id'];
//            $dataRPP->objectkasuspenyakitfk = null;
//            $dataRPP->kddokter = null;
//            $dataRPP->kddokterperiksanext =  null;
            $dataRPP->objectkelasfk = $request['kelas']['id'];
            $dataRPP->objectkelaskamarfk = $request['kelas']['id'];
//            $dataRPP->objectkelaskamarrencanafk =null;
//            $dataRPP->objectkelaskamartujuanfk =null;
//            $dataRPP->objectkelasrencanafk = null;
//            $dataRPP->objectkelastujuanfk = null;
            $dataRPP->kdpenjaminpasien =0;
//            $dataRPP->objectkeadaanumumfk = null;
            $dataRPP->objectkelompokpasienfk = $request['kelompokPasien']['id'];
//            $dataRPP->keteranganlainnyaperiksanext = null;
            $dataRPP->keteranganlainnyarencana = 'Mutasi Gawat Darurat';
//            $dataRPP->kodenomorbuktiperjanjian = null;
//            $dataRPP->objectkondisipasienfk = null;
//            $dataRPP->namatempattujuan =null;
//            $dataRPP->noantrian = null;
            $dataRPP->noantrianbydokter= 0;
//            $dataRPP->nobed = null;
//            $dataRPP->nobedtujuan =  null;
            $dataRPP->nocmfk = $request['pasien']['id'];
            $dataRPP->noregistrasifk = $request['noRecPasienDaftar'];
//            $dataRPP->prefixnoantrian =  '1';
            $dataRPP->objectruanganasalfk = $request['objectruanganasalfk'];
            $dataRPP->objectruanganfk = $request['ruangan']['id'];
            $dataRPP->objectruanganperiksanextfk = $request['ruangan']['id'];
//            $dataRPP->objectruanganrencanafk =  null;
//            $dataRPP->objectruangantujuanfk =  null;
            $dataRPP->objectstatuskeluarfk = null;
//            $dataRPP->objectstatuskeluarrencanafk =  null;
//            $dataRPP->statuspasien =  0;
            $dataRPP->objecttempattidurfk = $request['nomorTempatTidur']['id'];
//            $dataRPP->tglkeluar = null;
//            $dataRPP->tglkeluarrencana = null;
            $dataRPP->tglmasuk = $request['tglRegistrasi'];
//            $dataRPP->tglperiksanext = null;
            $dataRPP->tglpindah = $request['tglRegistrasi'];
//                 $dataRPP->objecttransportasifk = null;
//            $dataRPP->objectdetailkamarfk = null;

            $dataRPP->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "SUCCESS";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'data' => $dataAPD,
                'as' => 'mythicramdan',
            );
        } else {
            $transMessage = "FAILED";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'data' =>$dataAPD,
                'as' => 'mythicramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarPasienBatal(Request $request)
    {
        $filter = $request->all();
        $data = \DB::table('batalregistrasi_t as br')
            ->join ('pasiendaftar_t as pd','pd.norec','=','br.pasiendaftarfk')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin ('pegawai_m as pg','pg.id','=','br.pegawaifk')
            ->leftjoin ('pembatal_m as pmb','pmb.id','=','br.pembatalanfk')
            ->select('pd.norec as norec_pd', 'br.tanggalpembatalan','br.alasanpembatalan','ps.nocm','ps.namapasien','pd.noregistrasi',
                'pg.namalengkap','pmb.name as pembatal');

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('br.tanggalpembatalan', '>=', $filter['tglAwal'].' 00:00');
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('br.tanggalpembatalan', '<=', $filter['tglAkhir']. ' 23:59');
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
        $data = $data->groupBy('pd.norec', 'br.tanggalpembatalan','br.alasanpembatalan','ps.nocm','ps.namapasien','pd.noregistrasi',
            'pg.namalengkap','pmb.name');

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function getDaftarPasienMeninggal(Request $request)
    {
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

    public function getBayiBaruLahir(Request $request)
    {
        $filter = $request->all();
        $kdPasienBayi = $this->settingDataFixed('KdPasienBayi');
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin ('pasien_m as ps2','ps2.id','=','ps.qpasien')
            ->leftjoin ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->select(DB::raw("  pd.tglregistrasi,
                    pd.noregistrasi,
                    ps.nocm,
                    ps.namapasien,
                    jk.jeniskelamin,
                    ps.tgllahir,
                case when   ps2.namapasien is not NULL then ps2.namapasien else ps.namaibu end as namaibu,
                ps.namaayah, alm.alamatlengkap"))
            ->where('ps.statusenabled', true)
            ->where('ps.nocm','ilike','%'.$kdPasienBayi.'%');

        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal'].' 00:00');
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $filter['tglAkhir']. ' 23:59');
        }
        if (isset($filter['tglLahir']) && $filter['tglLahir'] != "" && $filter['tglLahir'] != "undefined") {
            $data = $data->whereBetween('ps.tgllahir', [ $filter['tglLahir'].' 00:00',$filter['tglLahir'].' 23:59' ]);
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
        if (isset($filter['namaIbu']) && $filter['namaIbu'] != "" && $filter['namaIbu'] != "undefined") {
            $data = $data->where('ps.namaibu', 'ilike','%'. $filter['namaIbu'].'%');
        }
        if (isset($filter['rows']) && $filter['rows'] != "" && $filter['rows'] != "undefined") {
            $row =(int) $filter['rows'];
            $data = $data->take( $row );
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getComboRiwayatRegis(Request $request)
    {

        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();


        $deptRajalInap = \DB::table('departemen_m as dept')
            ->whereIn('dept.id', [18, 16])
            ->orderBy('dept.namadepartemen')
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

        $KategoryDiagnosa = \DB::table('kategorydiagnosa_m as ma')
            ->select('ma.id','ma.kategorydiagnosa')
            ->where('ma.statusenabled',true)
            ->orderBy('ma.kategorydiagnosa')
            ->take(100)
            ->get();
        $JenisKelamin = \DB::table('jeniskelamin_m as jr')
            ->select('jr.id','jr.jeniskelamin')
            ->where('jr.statusenabled',true)
            ->orderBy('jr.jeniskelamin')
            ->take(100)
            ->get();

        $jenisDiagnosa = \DB::table('jenisdiagnosa_m as ma')
            ->select('ma.id','ma.jenisdiagnosa as jenisDiagnosa')
            ->where('ma.statusenabled',true)
            ->orderBy('ma.jenisdiagnosa')
            ->get();
        $result = array(
            'deptrirj' => $deptRajalInap,
            'kategorydiagnosa' => $KategoryDiagnosa,
            'jeniskelamin' =>   $JenisKelamin,
            'jenisdiagnosa' =>   $jenisDiagnosa,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }
    public function getPasienByNoCmRiwayatRegis( Request $request) {
        $data = \DB::table('pasien_m as ps')
            ->select('ps.nocm','ps.namapasien','ag.agama','ps.nohp','ps.notelepon','ps.namakeluarga','ps.namaibu',
                'ps.namaayah','ps.tempatlahir','ps.tgllahir','alm.alamatlengkap','ng.namanegara','jk.jeniskelamin',
                'kb.name as kebangsaan')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftJoin('agama_m as ag','ag.id','=','ps.objectagamafk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJoin('negara_m as ng','ng.id','=','ps.objectnegarafk')
            ->leftJoin('kebangsaan_m as kb','kb.id','=','ps.objectkebangsaanfk');

        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['noCm']);
        };
        $data=$data->first();

        $result = array(
            'datas' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getAntrianPasienByNocmRev( Request $request) {
        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix'));
        $kdDepartemenRawatInap = [];
        $result = [];
        foreach ($deptRanap as $itemRanap){
            $kdDepartemenRawatInap []=  (int)$itemRanap;
        }

        $deptJalan = explode (',',$this->settingDataFixed('kdDepartemenRawatJalanFix'));
        $kdDepartemenRawatJalan = [];
        foreach ($deptJalan as $item){
            $kdDepartemenRawatJalan []=  (int)$item;
        }
        $data = \DB::table('pasien_m as ps')
            ->join('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
//            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftJoin('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftJoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->select('ps.nocm', 'ps.namapasien', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.objectpegawaifk',
                'peg.namalengkap', 'pd.tglpulang', 'pd.objectruanganasalfk', 'ru.namaruangan','pd.norec as norec_pd',
                'pd.objectruanganlastfk'
            );

        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="noCm") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['noCm']);
        };
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="noReg"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        if (isset($request['idDept']) && $request['idDept'] == 16 && $request['idDept'] != "undefined") {
            $data = $data->whereIn('dpm.id',$kdDepartemenRawatInap);
        }
        if (isset($request['idDept']) && $request['idDept'] == 18 && $request['idDept'] != "undefined") {
            $data = $data->whereIn('dpm.id',$kdDepartemenRawatJalan);
        }

        $data = $data->distinct();
        $data=$data->get();

        foreach ($data as $item) {
            $details = DB::select(DB::raw("select CASE when ddp.noregistrasifk is null then 0 else 1 end as icd10,
                        dm.kddiagnosa,
			            CASE when dtp.objectpasienfk is null then 0 else 1 end as icd9
                        from antrianpasiendiperiksa_t as apd
                        INNER JOIN  detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec
                        INNER join diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                        LEFT JOIN diagnosatindakanpasien_t as dtp on dtp.objectpasienfk = apd.norec 
                        LEFT JOIN detaildiagnosatindakanpasien_t as ddtp on ddtp.objectdiagnosatindakanpasienfk = dtp.norec
                        LEFT JOIN diagnosatindakan_m as dt on dt.id = ddtp.objectdiagnosatindakanfk
                        where apd.noregistrasifk=:norec
                        GROUP BY ddp.noregistrasifk,dtp.objectpasienfk, dm.kddiagnosa"),
                array(
                    'norec' => $item->norec_pd,
                )
            );
            $result[] = array(
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'noregistrasi' => $item->noregistrasi,
                'tglregistrasi' => $item->tglregistrasi,
                'namalengkap' => $item->namalengkap,
                'tglpulang' => $item->tglpulang,
                'objectruanganasalfk' => $item->objectruanganasalfk,
                'namaruangan' => $item->namaruangan,
                'objectruanganlastfk' => $item->objectruanganlastfk,
                'details' => $details,
            );
        }

        $result = array(
            'datas' => $result,
//            'result' => $result,
            'message' => 'giw',
        );
        return $this->respond($result);
    }

    public function getIcd9(Request $request)
    {
        $req = $request->all();
        $icdIX = \DB::table('diagnosatindakan_m as dg')
            ->select('dg.id','dg.kddiagnosatindakan as kdDiagnosaTindakan','dg.namadiagnosatindakan as namaDiagnosaTindakan')
            ->where('dg.statusenabled', true)
            ->orderBy('dg.kddiagnosatindakan');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $icdIX = $icdIX
                ->where('dg.namadiagnosatindakan','ilike','%'.$req['filter']['filters'][0]['value'].'%' )
                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }


        $icdIX=$icdIX->take(10);
        $icdIX=$icdIX->get();

        return $this->respond($icdIX);
    }

    public function getDiagnosaPasienByNoregICD9 ( Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi', 'pd.tglregistrasi', 'apd.objectruanganfk', 'ru.namaruangan',
                'apd.norec as norec_apd', 'ddt.objectdiagnosatindakanfk', 'dt.kddiagnosatindakan', 'dt.namadiagnosatindakan',
                'dtp.norec as norec_diagnosapasien',
                'ddt.norec as norec_detaildpasien', 'dt.*','ddt.keterangantindakan','pg.namalengkap','ddt.tglinputdiagnosa' )
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join ('diagnosatindakanpasien_t as dtp','dtp.objectpasienfk','=','apd.norec')
            ->join ('detaildiagnosatindakanpasien_t as ddt','ddt.objectdiagnosatindakanpasienfk','=','dtp.norec')
            ->join ('diagnosatindakan_m as dt','dt.id','=','ddt.objectdiagnosatindakanfk')
            ->leftjoin('pegawai_m as pg','pg.id','=','ddt.objectpegawaifk');
//            ->join ('jenisdiagnosa_m as jd','jd.id','=','ddp.objectjenisdiagnosafk');
        if(isset($request['noCm']) && $request['noCm']!="" && $request['noCm']!="undefined"){
            $data = $data->where('ps.nocm','=', $request['noReg']);
        };
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $request['idDept']);
        };
        if (isset($request['kddiagnosatindakan']) && $request['kddiagnosatindakan'] != "" && $request['kddiagnosatindakan'] != "undefined") {
            $data = $data->where('dt.kddiagnosatindakan', '=', $request['kddiagnosatindakan']);
        }
        $data=$data->get();

        $result = array(
            'datas' => $data,
            'message' => 'giw@cepot',
        );
        return $this->respond($result);
    }
    public function getPasienDaftarByNoreg( Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi','pd.objectruanganlastfk','ru.namaruangan','pd.norec')
//                ,'apd.norec as norec_apd')
//            ->select('pd.noregistrasi','apd.objectruanganfk','ru.namaruangan','apd.norec as norec_apd',
//                'pd.objectruanganlastfk','ru2.namaruangan as ruanganlast')
//            ->leftJoin('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
//            ->leftJoin('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk');
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        $data=$data->get();
        $result=[];

        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select apd.norec as norec_apd, apd.objectruanganfk, ru.namaruangan
                     from antrianpasiendiperiksa_t as apd 
                    inner join ruangan_m as ru on ru.id=apd.objectruanganfk
                     where apd.objectruanganfk=:objectruanganlastfk and  apd.noregistrasifk=:norec"),
                array(
                    'objectruanganlastfk' => $item->objectruanganlastfk,
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'noregistrasi' => $item->noregistrasi,
                'objectruanganlastfk' => $item->objectruanganlastfk,
                'namaruangan' => $item->namaruangan,
                'norec' => $item->norec,
                'details' => $details,
            );
        }
        $result = array(
            'data' => $result,
            'message' => 'giw',
        );
        return $this->respond($result);
    }
    public function deleteDiagnosaTindakanPasien(Request $request) {
        $dataLogin = $request->all();
        DB::beginTransaction();
        if ($request['diagnosa']['norec_dp'] != ''){
            try{
                $data1 = DetailDiagnosaTindakanPasien::where('objectdiagnosatindakanpasienfk', $request['diagnosa']['norec_dp'])->delete();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus= false;
            }
            try{
                $data2 = DiagnosaTindakanPasien::where('norec',$request['diagnosa']['norec_dp'])->delete();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus= false;
            }

        }
        if ($transStatus='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }
        return $this->setStatusCode(201)->respond([], $transMessage);
    }

    public function saveDiagnosaPasien(Request $request) {
        $dataLogin = $request->all();
        DB::beginTransaction();
//        try{
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        if ($request['detaildiagnosapasien']['norec_dp']==''){
            $dataDiagnosa = new DiagnosaPasien();
            $dataDiagnosa->norec = $dataDiagnosa->generateNewId();
            $dataDiagnosa->kdprofile = 0;
            $dataDiagnosa->statusenabled = true;

        }else{
            $dataDiagnosa =  DiagnosaPasien::where('norec',$request['detaildiagnosapasien']['norec_dp'])->first();
        }

        $dataDiagnosa->noregistrasifk =  $request['detaildiagnosapasien']['noregistrasifk'];
        $dataDiagnosa->ketdiagnosis =  'Diagnosa Pasien';
        $dataDiagnosa->tglregistrasi = null;
        $dataDiagnosa->tglpendaftaran = $request['detaildiagnosapasien']['tglregistrasi'];
        if(isset($request['detaildiagnosapasien']['kasusbaru'])){
            $dataDiagnosa->iskasusbaru = $request['detaildiagnosapasien']['kasusbaru'];
        }
        if(isset($request['detaildiagnosapasien']['kasuslama'])){
            $dataDiagnosa->iskasuslama = $request['detaildiagnosapasien']['kasuslama'];
        }
        try{
            $dataDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Diagnosa Baru";
        }


        if ($request['detaildiagnosapasien']['norec_dp']=='' || $request['detaildiagnosapasien']['keterangan']=='')
        {
            $dataDetailDiagnosa = new DetailDiagnosaPasien();
            $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
            $dataDetailDiagnosa->kdprofile = 0;
            $dataDetailDiagnosa->statusenabled = true;
//               $dataDetailDiagnosa->keterangan = '-';
            $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();

        }else{
            $dataDetailDiagnosa =  DetailDiagnosaPasien::where('objectdiagnosapasienfk',$request['detaildiagnosapasien']['norec_dp'])->first();
        }

        $dataDetailDiagnosa->noregistrasifk =  $request['detaildiagnosapasien']['noregistrasifk'];
        $dataDetailDiagnosa->tglregistrasi = $request['detaildiagnosapasien']['tglregistrasi'];
        $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
        $dataDetailDiagnosa->objectdiagnosafk = $request['detaildiagnosapasien']['objectdiagnosafk'];
        $dataDetailDiagnosa->objectdiagnosapasienfk = $dataDiagnosa->norec;
        $dataDetailDiagnosa->objectjenisdiagnosafk = $request['detaildiagnosapasien']['objectjenisdiagnosafk'];
        $dataDetailDiagnosa->tglinputdiagnosa = date('Y-m-d H:i:s');//$request['detaildiagnosapasien']['tglinputdiagnosa'];
        $dataDetailDiagnosa->keterangan = $request['detaildiagnosapasien']['keterangan'];
        $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();


        try{
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
                'message'  => $transMessage,
                'data' =>$dataDiagnosa,
                'as' => 'egie@ramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    public function saveDiagnosaTindakanPasien(Request $request) {
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
        if ($request['detaildiagnosatindakanpasien']['norec_dp']==''){
            $dataDiagnosa = new DiagnosaTindakanPasien();
            $dataDiagnosa->norec = $dataDiagnosa->generateNewId();
            $dataDiagnosa->kdprofile = 0;
            $dataDiagnosa->statusenabled = true;
        }else{
            $dataDiagnosa =  DiagnosaTindakanPasien::where('norec',$request['detaildiagnosatindakanpasien']['norec_dp'])->first();
        }
        $dataDiagnosa->objectpasienfk =  $request['detaildiagnosatindakanpasien']['objectpasienfk'];
        $dataDiagnosa->tglpendaftaran = $request['detaildiagnosatindakanpasien']['tglpendaftaran'];


        try{
            $dataDiagnosa->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Diagnosa Baru";
        }


        if ($request['detaildiagnosatindakanpasien']['norec_dp']==''){
            $dataDetailDiagnosa = new DetailDiagnosaTindakanPasien();
            $dataDetailDiagnosa->norec = $dataDetailDiagnosa->generateNewId();
            $dataDetailDiagnosa->kdprofile = 0;
            $dataDetailDiagnosa->statusenabled = true;
            $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();
        }else{
            $dataDetailDiagnosa =  DetailDiagnosaTindakanPasien::where('objectdiagnosatindakanpasienfk',$request['detaildiagnosatindakanpasien']['norec_dp'])->first();
        }

        $dataDetailDiagnosa->objectdiagnosatindakanfk = $request['detaildiagnosatindakanpasien']['objectdiagnosatindakanfk'];
        $dataDetailDiagnosa->objectdiagnosatindakanpasienfk = $dataDiagnosa->norec;
        $dataDetailDiagnosa->jumlah = null;
        $dataDetailDiagnosa->objectpegawaifk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID()
        if(isset( $request['detaildiagnosatindakanpasien']['keterangantindakan'])){
            $dataDetailDiagnosa->keterangantindakan = $request['detaildiagnosatindakanpasien']['keterangantindakan'];
        }

        $dataDetailDiagnosa->tglinputdiagnosa = date('Y-m-d H:i:s');

        try{
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
                'message'  => $transMessage,
                'data' =>$dataDiagnosa,
                'as' => 'egie@ramdan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getAnamnesis(Request $request)
    {
        $data= \DB::table('anamnesis_t as rm')
            ->select('rm.norec','rm.anamnesisdokter','rm.anamnesissuster','rm.tglinput','rm.noregistrasifk',
                'rm.pegawaifk','pg.namalengkap','rm.ruanganfk','ru.namaruangan','pd.noregistrasi','pd.tglregistrasi','ps.nocm',
                'ps.namapasien')
            ->leftJoin('antrianpasiendiperiksa_t as apd','apd.norec','=','rm.noregistrasifk')
            ->leftJoin('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->leftJoin('pasien_m as ps','pd.nocmfk','=','ps.id')
            ->leftJoin('pegawai_m as pg','pg.id','=','rm.pegawaifk')
            ->leftJoin('ruangan_m as ru','ru.id','=','rm.ruanganfk')
            ->where('rm.statusenabled',true);

        if (isset($request['noregistrasifk']) && $request['noregistrasifk'] !='' ) {
            $data= $data->where('rm.noregistrasifk',$request['noregistrasifk']);
        }
        if (isset($request['nocm']) && $request['nocm'] !='' ) {
            $data= $data->where('ps.nocm',$request['nocm']);
        }
        if (isset($request['norec_pd']) && $request['norec_pd'] !='' ) {
            $data= $data->where('pd.norec',$request['norec_pd']);
        }
        $data= $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Inhuman',
        );
        return $this->respond($result);
    }
    public function getResumeMedisInap(Request $request,$nocm)
    {
        $data= \DB::table('resumemedis_t as rm')
            ->select('rm.norec','rm.tglresume','ru.namaruangan','pg.namalengkap as namadokter',
                'rm.ringkasanriwayatpenyakit','rm.pemeriksaanfisik','rm.pemeriksaanpenunjang',
                'rm.hasilkonsultasi','rm.terapi','rm.diagnosisawal','rm.diagnosissekunder','rm.tindakanprosedur',
                'rm.alergi','rm.diet','rm.instruksianjuran','rm.hasillab',
                'rm.kondisiwaktukeluar','rm.pengobatandilanjutkan','rm.koderesume',
                'rm.pegawaifk',
                'pd.noregistrasi','pd.tglregistrasi','ps.nocm','rm.noregistrasifk',
                'ps.namapasien')
            ->Join ('antrianpasiendiperiksa_t as apd','apd.norec','=','rm.noregistrasifk')
            ->Join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->Join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','rm.pegawaifk')
            ->where('rm.statusenabled',true)
            ->where('ps.nocm',$nocm)
            ->where('rm.keteranganlainnya','RawatInap');
//            ->whereIn('ru.objectdepartemenfk',$iddept);

        $data= $data->get();
        $result=[];
        foreach ( $data as $item){
            $details = DB::select(DB::raw("
                   select * from resumemedisdetail_t
                   where resumefk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
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
                'diagnosisawal' => $item->diagnosisawal,
                'diagnosissekunder' => $item->diagnosissekunder,
                'tindakanprosedur' => $item->tindakanprosedur,
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

    public function getDaftarHasilLab(Request $request){
        $results=[];
        $data = \DB::table('strukorder_t as so')
            ->LEFTJOIN('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','so.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','so.objectruangantujuanfk')
            ->LEFTJOIN('pegawai_m as p','p.id','=','so.objectpegawaiorderfk')
//            ->LEFTJOIN('resdt as pp','pp.ono','=','so.noorder')
            ->select('so.norec','pd.norec as norecpd','pd.noregistrasi','so.tglorder','so.noorder',
                'ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','p.namalengkap'
//                DB::raw('case when pp.ono is null then \'Hasil belum ada\' else \'SELESAI\' end as statusorder')
            );
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noregistrasi']);
        }
        $data = $data->where('so.objectruangantujuanfk',276);
        $data = $data->where('so.statusenabled',true);
//        $data = $data->where('apd.objectruanganfk',276);
        $data = $data->orderBy('so.tglorder');
//        $data = $data->distinct();
        $data = $data->get();

        //$results =array();
        foreach ($data as $item){
            $status='';
            $resDT =collect(\DB::select("select * from resdt where ono='$item->noorder'"))->first();
            if(!empty($resDT)){
                $status = 'Selesai';
            }else{
                $status = 'Hasil Belum Ada';
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
                            where so.noorder=:noorder"),
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
                'statusorder'=>$status,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }

    public function getDaftarHasilRad(Request $request){
        $results=[];
        $data = \DB::table('strukorder_t as so')
//            ->LEFTJOIN('orderpelayanan_t as op','op.noorderfk','=','so.norec')
            ->LEFTJOIN('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','so.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','so.objectruangantujuanfk')
            ->LEFTJOIN('pegawai_m as p','p.id','=','so.objectpegawaiorderfk')
//             ->LEFTJOIN('ris_order as pp','pp.order_no','=','so.noorder')
            ->select('so.norec','so.tglorder','so.noorder','ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','p.namalengkap','so.statusorder'
//               DB::raw('case when pp.order_no is null then \'PENDING\' else \'SELESAI\' end as statusorder')
            );
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noregistrasi']);
        }
        $data = $data->where('so.objectruangantujuanfk',35);
        $data = $data->where('so.statusenabled',true);
        $data = $data->orderBy('so.tglorder');
//        $data = $data->distinct();
        $data = $data->get();
        $status ='';
        foreach ($data as $item){
            $risorder = RisOrder::where('order_no', $item->noorder)->first();
            if(!empty($risorder)){
                $status = 'Selesai';
            }else{
                $status = 'Hasil Belum Ada';
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
                            where so.noorder=:noorder"),
                array(
                    'noorder' => $item->noorder,
                )
            );
            $results[] = array(
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'dokter' => $item->namalengkap,
                'statusorder'=>$status,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function getImage(Request $request)
    {
        $data = DB::select(DB::raw("
        select * from pasien_m where id=2"));
        $arr = [];
        foreach ($data as $item){
            $arr = array(
                'photo' => base64_encode($item->foto),//  "data:image/jpeg;base64," . base64_encode($item->foto),
            );
        }
        return $this->respond($arr);
    }
    public function saveImage(Request $request){

        $imageData = $request['photo'];
        $img =$request['photo'];
        $data = unpack("H*hex", $img);
        $data = '0x'.$data['hex'];
        Pasien::where('id',$request['id'])->update(
            ['foto' =>  \DB::raw("CONVERT(image, $data) ") ]
        );
        $filteredData=substr($imageData, strpos($imageData, ",")+1);
        $unencodedData=base64_decode($filteredData);

//                $daritadi = Pasien::where('id',$request['id'])->first();
//                $daritadi->foto =$data;
//                $daritadi->save();
    }

    public function getLaporanPengunjungPemeriksaan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','ps.objectjeniskelaminfk')
            ->JOIN ('agama_m as ag','ag.id','=','ps.objectagamafk')
            ->leftjoin ('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->join ('pekerjaan_m as pkr','pkr.id','=','ps.objectpekerjaanfk')
            ->join ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->join ('desakelurahan_m as dsk','dsk.id','=','alm.objectdesakelurahanfk')
            ->join ('kotakabupaten_m as kkb','kkb.id','=','alm.objectkotakabupatenfk')
            ->leftjoin ('statusperkawinan_m as sp','sp.id','=','objectstatusperkawinanfk')
            ->leftjoin ('pegawai_m as pg','pg.id','=','pd.objectdokterpemeriksafk')
            ->join ('ruangan_m as rg','rg.id','=','apd.objectruanganfk')
            ->join('logginguser_t AS lg',function($join)
            {
                $join->on('lg.noreff','=', 'pd.norec')
                ->where('lg.jenislog','=','Pendaftaran Pasien');
            })
            ->leftJoin('loginuser_s AS lu','lu.id','=','lg.objectloginuserfk')
            ->leftJoin('pegawai_m AS pg1','pg1.id','=','lu.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as klp','klp.id','=','pd.objectkelompokpasienlastfk')
            ->select('pd.norec','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ps.nohp','ps.tgllahir','jk.jeniskelamin','ag.agama','pdd.pendidikan','pkr.pekerjaan','alm.alamatlengkap',
                        'dsk.namadesakelurahan','alm.kecamatan','kkb.namakotakabupaten','sp.statusperkawinan','pd.statuspasien','rg.namaruangan','pg.namalengkap','ps.tgldaftar',
                        'klp.kelompokpasien','pd.statuspasien','apd.noantrian','pg1.namalengkap AS user','apd.objectruanganfk','jk.reportdisplay')
            ->groupBy('pd.norec','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ps.nohp','ps.tgllahir','jk.jeniskelamin','ag.agama','pdd.pendidikan','pkr.pekerjaan','alm.alamatlengkap',
                      'dsk.namadesakelurahan','alm.kecamatan','kkb.namakotakabupaten','sp.statusperkawinan','pd.statuspasien','rg.namaruangan','pg.namalengkap','ps.tgldaftar',
                      'klp.kelompokpasien','pd.statuspasien','apd.noantrian','pg1.namalengkap','apd.objectruanganfk','jk.reportdisplay')
            ->where('pd.statusenabled', true)
            ->where('pd.kdprofile', $kdProfile);
            

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('apd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('apd.tglregistrasi', '<=', $request['tglAkhir']);
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
//  
        $data = $data->orderBy('ps.namapasien');
        $data = $data->distinct();
        $data =  $data ->get();

        $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
                    ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
                    ->join('diagnosapasien_t AS dp','dp.norec','=','ddp.objectdiagnosapasienfk')
                    ->join ('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
                    ->select(DB::raw("apd.noregistrasifk,ddp.objectjenisdiagnosafk,dg.kddiagnosa AS diagnosa,
                                     CASE WHEN dp.iskasusbaru = true AND dp.iskasuslama = false THEN 'BARU'
                                     WHEN dp.iskasuslama = true AND dp.iskasusbaru = false THEN 'LAMA' ELSE '' END kasus"))
                    ->where('apd.kdprofile', $kdProfile)
                    ->where('apd.statusenabled', true)
                    ->where('apd.tglregistrasi', '>=', $request['tglAwal'])
                    ->where('apd.tglregistrasi', '<=', $request['tglAkhir'])
                    ->get();
        $bayar = \DB::table('antrianpasiendiperiksa_t AS apd')
                ->join('pelayananpasien_t AS pp','pp.noregistrasifk','=', 'apd.norec')
                ->leftJoin('strukpelayanan_t AS sp','sp.norec','=','pp.strukfk')
                ->leftJoin ('strukbuktipenerimaan_t AS sbm','sbm.nostrukfk','=','sp.norec')
                ->select(DB::raw("apd.noregistrasifk,apd.objectruanganfk,CASE WHEN pp.strukfk IS NOT NULL AND sbm.norec IS NOT NULL THEN pp.jumlah*pp.hargajual ELSE 0 END total"))
                ->where('apd.kdprofile', $kdProfile)
                ->where('apd.statusenabled', true)
                ->where('apd.tglregistrasi', '>=', $request['tglAwal'])
                ->where('apd.tglregistrasi', '<=', $request['tglAkhir'])
                ->get();
        $i=0;
        $dataDiagnosa = '';
        foreach ($data as $items){
            foreach ($diagnosa as $dg){
                if ($data[$i]->norec == $dg->noregistrasifk){
                    if ($dataDiagnosa == ''){
                        $dataDiagnosa = $dg->diagnosa;
                    }else{
                        $dataDiagnosa = $dataDiagnosa . ',' . $dg->diagnosa;
                    }
                    $data[$i]->diagnosa = $dataDiagnosa;
                    $data[$i]->kasus = $dg->kasus;
                }else{
                    $data[$i]->diagnosa = '';
                    $data[$i]->kasus = '';
                }
            }
            $i = $i + 1;
        }

        $d=0;
        foreach ($data as $itemss){
            foreach ($bayar as $dataBayar){
                if ($data[$d]->norec == $dataBayar->noregistrasifk && $data[$d]->objectruanganfk == $dataBayar->objectruanganfk){
                    $data[$d]->bayar = $dataBayar->total;
                }else{
                    $data[$d]->bayar = 0;
                }
            }
            $d = $d + 1;
        }

        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanPengunjungTindakan(Request $request) {
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftJoin('pelayananpasien_t as pp', function($q)
            {
                $q->on('pp.noregistrasifk', '=', 'apd.norec');
                $q->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
//                $q->on('ddp.objectjenisdiagnosafk', '=', 1);
            })
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','ps.objectjeniskelaminfk')
            ->JOIN ('agama_m as ag','ag.id','=','ps.objectagamafk')
            ->leftjoin ('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->join ('pekerjaan_m as pkr','pkr.id','=','ps.objectpekerjaanfk')
            ->join ('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->join ('desakelurahan_m as dsk','dsk.id','=','alm.objectdesakelurahanfk')
            ->join ('kotakabupaten_m as kkb','kkb.id','=','alm.objectkotakabupatenfk')
            ->leftjoin ('statusperkawinan_m as sp','sp.id','=','objectstatusperkawinanfk')
            ->leftjoin ('pegawai_m as pg','pg.id','=','pd.objectdokterpemeriksafk')
            ->join ('ruangan_m as rg','rg.id','=','pd.objectruanganlastfk')
            ->leftjoin ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->leftJoin('kelompokpasien_m as klp','klp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('detaildiagnosapasien_t as ddp', function($q)
            {
                $q->on('dp.norec', '=', 'ddp.objectdiagnosapasienfk');
//                    ->on('ddp.objectjenisdiagnosafk', '=', 1);
            })
            ->join ('diagnosa_m as dg','ddp.objectdiagnosafk','=','dg.id')
            ->leftJoin ('produk_m as pro','pro.id','=','pp.produkfk')
            ->leftJoin ('detailjenisproduk_m as djp','djp.id','=','pro.objectdetailjenisprodukfk')
            ->select('pd.norec','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ps.nohp','ps.tgllahir','jk.jeniskelamin','ag.agama','pdd.pendidikan','pkr.pekerjaan','alm.alamatlengkap',
                'dsk.namadesakelurahan','alm.kecamatan','kkb.namakotakabupaten','sp.statusperkawinan','pd.statuspasien','rg.namaruangan','pg.namalengkap','dg.kddiagnosa','dg.namadiagnosa','ps.tgldaftar',
                'klp.kelompokpasien','djp.detailjenisproduk','pro.namaproduk')
            ->groupBy('pd.norec','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien','ps.nohp','ps.tgllahir','jk.jeniskelamin','ag.agama','pdd.pendidikan','pkr.pekerjaan','alm.alamatlengkap',
                'dsk.namadesakelurahan','alm.kecamatan','kkb.namakotakabupaten','sp.statusperkawinan','pd.statuspasien','rg.namaruangan','pg.namalengkap','dg.kddiagnosa','dg.namadiagnosa','ps.tgldaftar',
                'klp.kelompokpasien','djp.detailjenisproduk','pro.namaproduk')
            ->where('pd.statusenabled', true)
            ->where('ddp.objectjenisdiagnosafk',1)
//            ->whereNotIn('djp.id', [1587,1597,])
            ->whereNotIn('djp.id', [1405,1406,1407,1408,1409,1587,1588,1589,1590,1591,1592,1593,1594,1595,1596,1597,1598,1599,1600,1601,1346,1347,1348,1349,1350,1351,1352,1353,1354,1355,1356,1357,1358,1359,1360,1361,1362,1363,1364,1365,1366,1367,1368,1369,1370,1371,1372,1373,1374,1375,1376,1377,1378,1379,1380,1381,1382,1383,1384,1385,1386,1387,1388,1389,1390,1391,1392,1393,1394,1395,1396,1397,1398,1399,1400,1401,1402,1403,474
            ])
            ->whereNotIn('pro.id', [4040398,4040406,4041196,4041200,4041204,4041209,4041212,4041215,4041218,4040399]);

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
//
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanPasienDPJP(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

        $deptRawatJalan = $this->settingDataFixed('kdDepartemenRawatJalanFix',$idProfile);

        $data = DB::select(DB::raw("select x.namalengkap as dokter,sum(x.anggrek1) as anggrek1,sum(x.anggrek2) as anggrek2,sum(x.dahlia) as dahlia,sum(x.nicu) as nicu,
        sum(x.icucovid) as icucovid,sum(x.bougenvilleatas) as bougenvilleatas,sum(x.bougenvillebawah) as bougenvillebawah,sum(x.terataiatas) as terataiatas,
        sum(x.terataibawah) as terataibawah,sum(x.flamboyan) as flamboyan,sum(x.cempaka) as cempaka,sum(x.seruniatas) as seruniatas,sum(x.serunibawah) as serunibawah,
        sum(x.vip) as vip,sum(x.icu) as icu,sum(x.iccu) as iccu,sum(x.melati1) as melati1,sum(x.melati2) as melati2,sum(x.melati3) as melati3,sum(x.melati4) as melati4,
        sum(x.camelia) as camelia,sum(x.flamboyan2) as flamboyan2,sum(x.hccu) as hccu,
        sum(x.anggrek1)+sum(x.anggrek2)+sum(x.dahlia)+sum(x.nicu)+sum(x.icucovid)+sum(x.bougenvilleatas)+sum(x.bougenvillebawah)+sum(x.terataiatas)+
        sum(x.terataibawah)+sum(x.flamboyan)+sum(x.cempaka)+sum(x.seruniatas)+sum(x.serunibawah)+sum(x.vip)+sum(x.icu)+sum(x.iccu)+sum(x.melati1)+
        sum(x.melati2)+sum(x.melati3)+sum(x.melati4)+sum(x.flamboyan2)+sum(x.hccu)+sum(x.camelia) as total
        from (select pt.objectdokterpemeriksafk, apd.objectruanganfk , pt.tglregistrasi, rm.namaruangan, pm.namalengkap, pm2.nocm, pm2.namapasien, 
        case when apd.objectruanganfk = 717 then 1 else 0 end as anggrek1,  
        case when apd.objectruanganfk = 718 then 1 else 0 end as anggrek2,
        case when apd.objectruanganfk = 719 then 1 else 0 end as dahlia,
        case when apd.objectruanganfk = 720 then 1 else 0 end as nicu,
        case when apd.objectruanganfk = 721 then 1 else 0 end as icucovid, 
        case when apd.objectruanganfk = 722 then 1 else 0 end as bougenvilleatas, 
        case when apd.objectruanganfk = 723 then 1 else 0 end as bougenvillebawah, 
        case when apd.objectruanganfk = 724 then 1 else 0 end as terataiatas, 
        case when apd.objectruanganfk = 725 then 1 else 0 end as terataibawah, 
        case when apd.objectruanganfk = 726 then 1 else 0 end as flamboyan, 
        case when apd.objectruanganfk = 727 then 1 else 0 end as cempaka, 
        case when apd.objectruanganfk = 728 then 1 else 0 end as seruniatas, 
        case when apd.objectruanganfk = 729 then 1 else 0 end as serunibawah, 
        case when apd.objectruanganfk = 730 then 1 else 0 end as vip, 
        case when apd.objectruanganfk = 731 then 1 else 0 end as icu, 
        case when apd.objectruanganfk = 732 then 1 else 0 end as iccu, 
        case when apd.objectruanganfk = 733 then 1 else 0 end as melati1, 
        case when apd.objectruanganfk = 734 then 1 else 0 end as melati2,
        case when apd.objectruanganfk = 735 then 1 else 0 end as melati3, 
        case when apd.objectruanganfk = 736 then 1 else 0 end as melati4, 
        case when apd.objectruanganfk = 737 then 1 else 0 end as camelia, 
        case when apd.objectruanganfk = 738 then 1 else 0 end as flamboyan2, 
        case when apd.objectruanganfk = 739 then 1 else 0 end as hccu 
        from pasiendaftar_t pt 
        inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pt.norec
        inner join ruangan_m as rm on rm.id = apd.objectruanganfk 
        inner join pegawai_m as pm on pm.id = pt.objectdokterpemeriksafk 
        inner join pasien_m as pm2 on pm2.id = pt.nocmfk 
        where rm.objectdepartemenfk = 16 and pt.statusenabled = true and pt.kdprofile = 21 and pt.tglregistrasi between '$tglAwal' and '$tglAkhir') as x
        group by x.namalengkap
        order by x.namalengkap asc"));
        return $this->respond($data);
    }

    public function getLaporanPasienRJPerDokterPemeriksa(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['ruanganId'];
        $dokter = $request['dokter'];

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and rm.id = ' . $ruanganId;
        }
        $paramDokter = ' ';
        if (isset($dokter) && $dokter != "" && $dokter != "undefined") {
            $paramDokter = ' and pm.id = ' . $dokter;
        }
        $deptRawatJalan = $this->settingDataFixed('kdDepartemenRawatJalanFix',$idProfile);

        $data = DB::select(DB::raw("select x.namaruangan,x.namalengkap,sum(x.januari) as januari ,sum(x.februari) as februari ,sum(x.maret) as maret ,sum(x.april) as april ,
        sum(x.mei) as mei ,sum(x.juni) as juni ,sum(x.juli) as juli ,sum(x.agustus) as agustus ,sum(x.september) as september ,sum(x.oktober) as oktober ,
        sum(x.november) as november ,sum(x.desember) as desember, sum(x.januari)+sum(x.februari)+sum(x.maret)+sum(x.april)+sum(x.mei)+sum(x.juni)+
        sum(x.juli)+sum(x.agustus)+sum(x.september)+sum(x.oktober)+sum(x.november)+sum(x.desember) as jumlah  from
        (select pm.namalengkap,rm.namaruangan, 
        case when (date_part('month',pt.tglregistrasi) = 1) then 1 else 0 end as januari,
        case when (date_part('month',pt.tglregistrasi) = 2) then 1 else 0 end as februari,
        case when (date_part('month',pt.tglregistrasi) = 3) then 1 else 0 end as maret,
        case when (date_part('month',pt.tglregistrasi) = 4) then 1 else 0 end as april,
        case when (date_part('month',pt.tglregistrasi) = 5) then 1 else 0 end as mei,
        case when (date_part('month',pt.tglregistrasi) = 6) then 1 else 0 end as juni,
        case when (date_part('month',pt.tglregistrasi) = 7) then 1 else 0 end as juli,
        case when (date_part('month',pt.tglregistrasi) = 8) then 1 else 0 end as agustus,
        case when (date_part('month',pt.tglregistrasi) = 9) then 1 else 0 end as september,
        case when (date_part('month',pt.tglregistrasi) = 10) then 1 else 0 end as oktober,
        case when (date_part('month',pt.tglregistrasi) = 11) then 1 else 0 end as november,
        case when (date_part('month',pt.tglregistrasi) = 12) then 1 else 0 end as desember
        from pasiendaftar_t pt
        inner join pegawai_m pm on pm.id = pt.objectdokterpemeriksafk 
        inner join ruangan_m rm on rm.id = pt.objectruanganlastfk 
        where pt.statusenabled = true and pt.kdprofile = 21 and rm.objectdepartemenfk = 18 and pt.tglregistrasi between '$tglAwal' and '$tglAkhir' $paramRuangan $paramDokter) as x
        group by x.namaruangan,x.namalengkap
        order by x.namaruangan asc, x.namalengkap asc"));
        return $this->respond($data);
    }

    public function getLapDarah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = DB::select(DB::raw(
            "select pp.tglpelayanan ,pm.nocm ,pm.namapasien ,rm.namaruangan ,km.kelompokpasien ,p.namaproduk ,pp.jumlah ,pg.namalengkap ,pp.hargasatuan ,pp.hargajual,pmi.pmi 
            from pelayananpasien_t pp
            inner join antrianpasiendiperiksa_t at2 on at2.norec = pp.noregistrasifk
            inner join pasiendaftar_t pt on pt.norec = at2.noregistrasifk
            inner join pasien_m pm on pm.id = pt.nocmfk 
            inner join produk_m p on p.id = pp.produkfk 
            inner join kelompokpasien_m km on km.id = pt.objectkelompokpasienlastfk
            left join strukorder_t so on so.norec = at2.objectstrukorderfk
            left join pegawai_m pg on pg.id = pt.objectdokterpemeriksafk 
            inner join pmi_m pmi on pmi.id = pp.pmifk 
            left join ruangan_m rm on rm.id = so.objectruanganfk  
            where pp.statusenabled = true and pp.kdprofile = $idProfile and pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
            order by pp.tglpelayanan asc"
        ));
        return $this->respond($data);
    }

}