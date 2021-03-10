<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 11/22/2019
 * Time: 12:23 PM
 */

namespace App\Http\Controllers\PMKP;

use App\Http\Controllers\ApiController;
use App\Master\PegawaiJadwalKerja;
use App\Traits\Valet;
use App\Transaksi\AnalisaSasaranMutu;
use App\Transaksi\IdentifikasiRisiko;
use App\Transaksi\IdentifikasiRisikoDetail;
use App\Transaksi\InsidenKeselamatanPasien;
use App\Transaksi\LaporanInsidenInternal;
use App\Transaksi\LembarKerjaInvestigasi;
use App\Transaksi\RiskRegister;
use App\Transaksi\RiwayatPMKP;
use App\Transaksi\SasaranMutu;
use App\Transaksi\StrukAgendaDetail;
use App\Transaksi\StrukPlanningDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class PMKPController extends ApiController
{

    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function  saveRiwayat (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            if($request['norec'] == '') {
                $data = new RiwayatPMKP();
                $data->norec = $data->generateNewId();
                $data->kdprofile = $kdProfile;
                $data->statusenabled = true;
            }else{
                $data = RiwayatPMKP::where('norec',$request['norec'])->first();
            }
            $data->judul = $request['judul'];
            $data->isi = $request['isi'];
            $data->tgl = date('Y-m-d H:i');
            $data->pegawaifk = $this->getCurrentUserID();
            $data->keterangan = $request['keterangan'];
            $data->save();
            $norecs = $data->norec;
            if(isset($request['image']) && $request['image']!=null){

//                egi
                $img = $request['image'];
                $datas = unpack("H*hex", $img);
                $datas = '0x'.$datas['hex'];
                RiwayatPMKP::where('norec',$norecs)->update(
//                    ['image' =>  \DB::raw("CONVERT(VARBINARY(MAX), $datas) ") ]
                    [
                        'image' => $img
                    ]);
                #eg
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
                "norec" => $data,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "norec" => $data,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function  hapusRiwayat (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {

            $data = RiwayatPMKP::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                'statusenabled' => false
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';

        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "norec" => $data,
                "as" => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "norec" => $data,
                "as" => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRiwayat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('riwayatpmkp_t as tt')
            ->leftJoin('pegawai_m as pg','pg.id','=','tt.pegawaifk')
            ->select('tt.*','pg.namalengkap')
            ->where('tt.kdprofile', $kdProfile)
            ->where('tt.statusenabled', true);

        if (isset($request['isi']) && $request['isi'] != "" && $request['isi'] != "undefined") {
            $data = $data->where('tt.isi', 'ilike', '%' . $request['isi'] . '%');
        };
        if (isset($request['judul']) && $request['judul'] != "" && $request['judul'] != "undefined") {
            $data = $data->where('tt.judul', 'ilike', '%' . $request['judul'] . '%');
        };
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDataLaporanDokterPelayananPoliklinik(Request $request) {
        $data = [];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $idDokter = $request['idDokter'];
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramDokter = ' ';
        if (isset($idDokter) && $idDokter != "" && $idDokter != "undefined") {
            $paramDokter = ' and pg.id = '.$idDokter;
        }

        $data = DB::select(DB::raw("SELECT x.namaruangan,x.namalengkap,SUM(x.jumlah) as jumlah
                FROM (SELECT pp.tglpelayanan,ru.namaruangan,ppp.objectpegawaifk,pg.namalengkap,COUNT(pg.namalengkap) as jumlah
                FROM pasiendaftar_t as pd 
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                INNER JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                LEFT JOIN alamat_m as alm on alm.nocmfk = pm.id
                LEFT JOIN pegawai_m as pg on pg.id = ppp.objectpegawaifk
                INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                WHERE pd.kdprofile = $kdProfile and ppp.objectjenispetugaspefk = 4 AND ppp.objectpegawaifk <> 1
                AND ru.objectdepartemenfk in (18,24,26,27,3,28,29,30) 
                AND pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'                
                $paramRuangan
                $paramDokter
                GROUP BY pp.tglpelayanan,namaruangan,ppp.objectpegawaifk,pg.namalengkap)as x
                GROUP BY x.namaruangan,x.namalengkap"));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataLaporanDokterPelayananRanap(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $idDokter = $request['idDokter'];
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramDokter = ' ';
        if (isset($idDokter) && $idDokter != "" && $idDokter != "undefined") {
            $paramDokter = ' and pg.id = '.$idDokter;
        }

        $data = DB::select(DB::raw("SELECT x.namaruangan,x.namalengkap,SUM(x.jumlah) as jumlah
                FROM (SELECT pp.tglpelayanan,ru.namaruangan,ppp.objectpegawaifk,pg.namalengkap,COUNT(pg.namalengkap) as jumlah
                FROM pasiendaftar_t as pd 
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                INNER JOIN pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                INNER JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                LEFT JOIN alamat_m as alm on alm.nocmfk = pm.id
                LEFT JOIN pegawai_m as pg on pg.id = ppp.objectpegawaifk
                INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                WHERE pd.kdprofile = $kdProfile and ppp.objectjenispetugaspefk = 4 AND ppp.objectpegawaifk <> 1
                AND ru.objectdepartemenfk in (16,36,25) 
                AND pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'                
                $paramRuangan
                $paramDokter
                GROUP BY pp.tglpelayanan,namaruangan,ppp.objectpegawaifk,pg.namalengkap)as x
                GROUP BY x.namaruangan,x.namalengkap"));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataCombo(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $ruangan= \DB::table('ruangan_m')
            ->select('id','namaruangan')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->get();
        $dokter= \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->where('objectjenispegawaifk',true)
            ->get();
        $ruanganrajal=\DB::table('ruangan_m')
            ->select('id','namaruangan')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->whereIn('objectdepartemenfk', [18,24,26,27,3,28,29,30])
            ->get();
        $ruanganranap=\DB::table('ruangan_m')
            ->select('id','namaruangan')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->whereIn('objectdepartemenfk', [16,36,25])
            ->get();
        $JenisKeselamatan = \DB::table('jeniskeselamatan_m')
            ->select('id','jeniskeselamatan')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->get();
        $Keselamatan = \DB::table('insidenkeselamatan_m')
            ->select('id','namakeselamatan','jeniskesalamatanfk','namakeselamatan as keselamatan')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->get();
        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id',[3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35])
            ->where('dp.kdprofile',$kdProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataKeselamatanInsidenPasien = \DB::table('jeniskeselamatan_m as jk')
            ->join('insidenkeselamatan_m as ik','ik.jeniskesalamatanfk','=','jk.id')
            ->selectRaw("ik.id,ik.jeniskesalamatanfk,ik.namakeselamatan as keselamatan,jk.jeniskeselamatan")
            ->where('jk.kdprofile',$kdProfile)
            ->where('jk.statusenabled', true)
            ->where('ik.statusenabled', true)
            ->orderBy('ik.id', 'ASC')
            ->get();

        foreach ($JenisKeselamatan as $item) {
            $detail = [];
            foreach ($Keselamatan as $item2) {
                if ($item->id == $item2->jeniskesalamatanfk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'keselamatan' => $item2->namakeselamatan,
                    );
                }
            }

            $dataJenisKeselamatan[] = array(
                'id' => $item->id,
                'jeniskesalamatan' => $item->jeniskeselamatan,
                'keselamatan' => $detail,
            );
        }

        $DimensiMutu = \DB::table('dimensimutu_m')
            ->select('id','demensimutu')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->get();
        $FrekuensiData = \DB::table('frekuensidata_m')
            ->select('id','frekuensi')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled',true)
            ->get();
        $dataWaktuLaporan = \DB::table('waktulaporan_m')
            ->select('id','waktulaporan')
            ->where('statusenabled', true)
            ->where('kdprofile',$kdProfile)
            ->orderBy('waktulaporan')
            ->get();
        $dataPeriodeAnalis = \DB::table('periodeanalis_m')
            ->select('id','periode')
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('periode')
            ->get();
        $dataMetologi = \DB::table('metologi_m')
            ->selectRaw("id,metologi || ': ' || keterangan as metologi")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();
        $dataMetologiAna = \DB::table('metologianalisisdata_m')
            ->selectRaw("id,analisisdata")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();
        $dataCakupan = \DB::table('cakupandata_m')
            ->selectRaw("id,cakupandata")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();
        $dataPublikasi = \DB::table('publikasidata_m')
            ->selectRaw("id,publikasidata")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();
        $dataKategoryIndikator = \DB::table('kategoryindikator_m')
            ->selectRaw("id,kategoryindikator")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();
        $dataRegrading = \DB::table('regrading_m')
            ->selectRaw("id,regrading")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();

        $kdUserPmkp = $this->settingDataFixed('KdKelompokUserPmkp',$kdProfile);
        $dataKategoryRisiko = \DB::table('kategoryrisiko_m')
            ->selectRaw("id,kategoryrisiko")
            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->orderBy('id')
            ->get();

        $result = array(
            'ruangan' => $ruangan,
            'ruanganrajal' => $ruanganrajal,
            'ruanganranap' => $ruanganranap,
            'dokter' => $dokter,
            'jeniskeselamatan' => $dataJenisKeselamatan,
            'departemen' => $dataInstalasi,
            'datakeselamatan' => $Keselamatan,
            'insidenkeselamtanpasien' => $dataKeselamatanInsidenPasien,
            'dimensimutu' => $DimensiMutu,
            'frekuensidata' => $FrekuensiData,
            'waktulaporan' => $dataWaktuLaporan,
            'periodeanalis' => $dataPeriodeAnalis,
            'metologi' => $dataMetologi,
            'metologiana' => $dataMetologiAna,
            'cakupandata' => $dataCakupan,
            'publikasidata' => $dataPublikasi,
            'kategory' => $dataKategoryIndikator,
            'regrading' => $dataRegrading,
            'kdUser' => $kdUserPmkp,
            'kategoryrisiko' => $dataKategoryRisiko,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDataLaporanDokterPenanggungJawabRanap(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $idDokter = $request['idDokter'];
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramDokter = ' ';
        if (isset($idDokter) && $idDokter != "" && $idDokter != "undefined") {
            $paramDokter = ' and pg.id = '.$idDokter;
        }

        $data = DB::select(DB::raw("SELECT x.namaruangan,x.namalengkap,SUM(x.jumlah) as jumlah
                FROM (SELECT apd.tglmasuk,ru.namaruangan,apd.objectpegawaifk,
                            CASE WHEN pg.namalengkap IS NULL THEN '-' ELSE pg.namalengkap END AS namalengkap,
                            COUNT(pg.namalengkap) as jumlah
                FROM pasiendaftar_t as pd 
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                INNER JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                LEFT JOIN alamat_m as alm on alm.nocmfk = pm.id
                LEFT JOIN pegawai_m as pg on pg.id = apd.objectpegawaifk
                INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                WHERE pd.kdprofile = $kdProfile and ru.objectdepartemenfk in (16,36,25)
                AND apd.tglmasuk BETWEEN '$tglAwal' and '$tglAkhir'                
                $paramRuangan
                $paramDokter
                GROUP BY apd.tglmasuk,namaruangan,apd.objectpegawaifk,pg.namalengkap)as x
                GROUP BY x.namaruangan,x.namalengkap"));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanJamVisiteDokter(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->JOIN ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->JOIN ('pelayananpasienpetugas_t as ppp','ppp.pelayananpasien','=','pp.norec')
            ->JOIN ('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','pm.objectjeniskelaminfk')
            ->LEFTJOIN ('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->LEFTJOIN ('pegawai_m as pg','pg.id','=','apd.objectpegawaifk')
            ->JOIN ('ruangan_m as ru','ru.id','=', 'apd.objectruanganfk')
            ->JOIN ('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->JOIN ('produk_m as pro','pro.id','=','pp.produkfk')
            ->select(DB::raw("pm.nocm,pd.noregistrasi,pm.namapasien,pp.tglpelayanan,pro.namaproduk,
			                        CASE WHEN pg.namalengkap IS NULL THEN '-' ELSE pg.namalengkap END AS namalengkap,ru.namaruangan"))
            ->where('pd.kdprofile', $kdProfile)
            ->where('ppp.objectpegawaifk', '<>', 1)
            ->where('ppp.objectjenispetugaspefk',4)
            ->whereIn('ru.objectdepartemenfk', [16,36,25])
            ->whereIn('pp.produkfk', [404047,4040475,4041191]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->Where('pg.id', '=', $request['idDokter'])	;
        }
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanKematianPasienRanap(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','pm.objectjeniskelaminfk')
            ->LEFTJOIN ('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->JOIN ('ruangan_m as ru','ru.id','=', 'pd.objectruanganlastfk')
            ->JOIN ('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("pm.nocm,pd.noregistrasi,pm.namapasien,to_char(pm.tgllahir,'DD-MM-YYYY') as tgllahir,
			                 to_char(pd.tglregistrasi,'DD-MM-YYYY') as tglregistrasi,ru.namaruangan,pd.tglmeninggal"))
            ->where('pd.kdprofile',$kdProfile)
            ->whereRaw(" extract(hour from age(pd.tglregistrasi, pd.tglmeninggal)) > 48")
            ->whereIn('ru.objectdepartemenfk', [16,36,25]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
//        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
//            $data = $data->Where('pg.id', '=', $request['idDokter'])	;
//        }
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanPasienPulangPaksa(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','pm.objectjeniskelaminfk')
            ->LEFTJOIN ('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->JOIN ('ruangan_m as ru','ru.id','=', 'pd.objectruanganlastfk')
            ->JOIN ('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("pm.nocm,pd.noregistrasi,pm.namapasien,to_char(pm.tgllahir,'DD-MM-YYYY') as tgllahir,
			         to_char(pd.tglregistrasi,'DD-MM-YYYY') as tglregistrasi,ru.namaruangan,to_char(pd.tglpulang,'DD-MM-YYYY') as tglpulang"))
            ->where('pd.kdprofile', $kdProfile)
            ->whereNotNull('pd.tglpulang')
            ->where('pd.objectstatuspulangfk',12)
            ->whereIn('ru.objectdepartemenfk', [16,36,25]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
//        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
//            $data = $data->Where('pg.id', '=', $request['idDokter'])	;
//        }
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanLamaHariPerawatanPasien(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','pm.objectjeniskelaminfk')
            ->LEFTJOIN ('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->JOIN ('ruangan_m as ru','ru.id','=', 'pd.objectruanganlastfk')
            ->JOIN ('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("pm.nocm,pd.noregistrasi,pm.namapasien,to_char(pm.tgllahir,'DD-MM-YYYY') as tgllahir,
                                     to_char(pd.tglregistrasi,'DD-MM-YYYY') as tglregistrasi,ru.namaruangan,
                                     to_char(pd.tglpulang,'DD-MM-YYYY') as tglpulang,
                                     -- CASE WHEN DATEDIFF(day, pd.tglregistrasi, pd.tglpulang ) = 0 THEN DATEDIFF(hour, pd.tglregistrasi, pd.tglpulang) ELSE
                                     -- DATEDIFF( day, pd.tglregistrasi, pd.tglpulang) END AS lamadirawat
                                     CASE WHEN EXTRACT(day from AGE(pd.tglregistrasi, pd.tglpulang)) = 0 THEN EXTRACT(hour from AGE(pd.tglregistrasi, pd.tglpulang)) 
                                     ELSE EXTRACT(day from AGE(pd.tglregistrasi, pd.tglpulang)) END AS lamadirawat
                                     "))
            ->where('pd.kdprofile', $kdProfile)
            ->whereNotNull('pd.tglpulang')
            ->whereIn('ru.objectdepartemenfk', [16,36,25]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
//        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
//            $data = $data->Where('pg.id', '=', $request['idDokter'])	;
//        }
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getPerawatMinimalD3(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pegawai_m as pg')
            ->LEFTJOIN ('jenispegawai_m as jp','jp.id','=','pg.objectjenispegawaifk')
            ->LEFTJOIN ('pendidikan_m as pend','pend.id','=','pg.objectpendidikanterakhirfk')
            ->select(DB::raw("pg.nippns,pg.namalengkap,jp.jenispegawai,pend.pendidikan"))
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.id', '<>', 1)
            ->where('pg.kdprofile', $kdProfile)
            ->where('pg.objectjenispegawaifk','=',2)
            ->whereNotIn('pg.objectjenispegawaifk',[0,1,2,3,4,5,6]);

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
//        }
//        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
//            $data = $data->where('ru.id', '=', $request['idRuangan']);
//        }
//        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
//            $data = $data->Where('pg.id', '=', $request['idDokter'])	;
//        }
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanKematianPasienIgd(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->JOIN ('jeniskelamin_m as jk','jk.id', '=','pm.objectjeniskelaminfk')
            ->LEFTJOIN ('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->JOIN ('ruangan_m as ru','ru.id','=', 'pd.objectruanganlastfk')
            ->JOIN ('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select(DB::raw("pm.nocm,pd.noregistrasi,pm.namapasien,to_char(pm.tgllahir,'DD-MM-YYYY') as tgllahir,
			                 to_char(pd.tglregistrasi,'DD-MM-YYYY') as tglregistrasi,ru.namaruangan,pd.tglmeninggal"))
            ->whereRaw(" EXTRACT(hour from AGE(pd.tglregistrasi, pd.tglmeninggal)) < 24");
//            ->whereIn('ru.objectdepartemenfk', [16,36,25]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
//        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
//            $data = $data->Where('pg.id', '=', $request['idDokter'])	;
//        }
        $data =  $data ->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getComboIndikatorMutu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request->all();
        $data = \DB::table('indikatorrensar_m')
            ->select('id','indikator','numerator','denominator')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled', true)
            ->orderBy('indikator');

        if(isset($req['indikator']) &&
            $req['indikator']!="" &&
            $req['indikator']!="undefined"){
            $data = $data->where('indikator','ilike','%'. $req['indikator'] .'%' );
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
                ->where('indikator','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }

        $data = $data->get();
        return $this->respond($data);
    }

    public function saveSasaranMutu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
            $tgl = [];
            $id = [];
            $ket = [];
            $departemenfk = $request['departemenfk'];
            foreach ($request['data'] as $item) {
                $tgl []= $item['tgl'];
                $id []= $item['indikatorfk'];
                $ket []= $item['keterangan'];
            }
            $dele = SasaranMutu::where('departemenfk',$departemenfk)
                ->where('kdprofile', $kdProfile)
                ->whereIn('tgl',$tgl)
                ->whereIn('indikatorfk',$id)
                ->whereIn('keterangan',$ket)
                 ->delete();
//            return $this->respond($dele);

            foreach ($request['data'] as $item) {
//                return $this->respond($item['norecSasaran']);
                if ($item['norecSasaran'] == '') {
                    $dataJadwal = new SasaranMutu();
                    $dataJadwal->norec = $dataJadwal->generateNewId();
                    $dataJadwal->kdprofile = $kdProfile;
                    $dataJadwal->statusenabled = true;
                } else {
                    $dataJadwal = SasaranMutu::where('norec', $item['norecSasaran'])->where('kdprofile', $kdProfile)->first();
                }
                $dataJadwal->indikatorfk = $item['indikatorfk'];
                $dataJadwal->tglnilai = $item['tgl'];
                $dataJadwal->tgl = $item['tgl'];
                $dataJadwal->departemenfk = $request['departemenfk'];
                $dataJadwal->nilai = $item['isi'];
                $dataJadwal->num = $request['numerator'];
                $dataJadwal->denum = $request['denumerator'];
                $dataJadwal->capaian = $request['capaian'];
                $dataJadwal->keterangan = $item['keterangan'];
                $dataJadwal->save();
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
                "res" => $dataJadwal,
                "as" => 'ramdanegie@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ramdanegie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarIndikator(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tahun = Carbon::now()->format('Y');
        $data = DB::table('indikatorrensar_m as head')
            ->select(DB::raw("head.id,head.indikator,head.denominator,head.numerator,'NUM' as keterangan"))
            ->where('head.kdprofile', $kdProfile)
            ->where('head.statusenabled',true)
            ->where('head.objectdepartemenfk',$request['idDept']);
//            ->orderBy('head.id','asc');

        $dataSatu = DB::table('indikatorrensar_m as head')
            ->select(DB::raw("head.id,head.indikator,head.denominator,head.numerator,'DENUM' as keterangan"))
            ->where('head.kdprofile', $kdProfile)
            ->where('head.statusenabled',true)
            ->where('head.objectdepartemenfk',$request['idDept'])
//            ->orderBy('head.id','asc')
            ->union($data);

//        if(isset($request['idDept']) && $request['idDept']!= ''){
//            $data = $data->where('head.objectdepartemenfk',$request['idDept']);
//            $dataSatu = $dataSatu->where('head.objectdepartemenfk',$request['idDept']);
//        }
        $dataSatu = $dataSatu->orderBy('id', 'ASC');
        $dataSatu = $dataSatu->get();
        $dataAnyar = [];
        $result = array(
            'data' => $dataSatu,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataSasaranMutu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('sasaranmutu_t as sm')
            ->join('indikatorrensar_m as ir','ir.id','=','sm.indikatorfk')
            ->select(DB::raw("sm.tglnilai,sm.tgl,sm.nilai,sm.num,sm.denum,sm.capaian,sm.keterangan,ir.indikator,ir.denominator,ir.numerator"))
            ->where('sm.kdprofile', $kdProfile)
            ->where('ir.statusenabled', true);
//            ->orderByRaw('pg.namalengkap,pjk.tgljadwal desc');

        if(isset($request['bulan']) &&
            $request['bulan']!="" &&
            $request['bulan']!="undefined"){
            $tgl = $request['bulan']  ;
            $data = $data->whereRaw("
            -- STUFF(CONVERT(varchar(10), sm.tglnilai,104),1,3,'')
            OVERLAY(to_char(sm.tglnilai,'DD.MM.YYYY') placing '' from 1 for 3)
            ='$tgl' " );
        };
//        if(isset($request['namalengkap']) &&
//            $request['namalengkap']!="" &&
//            $request['namalengkap']!="undefined"){
//            $data = $data->where('ei.objectpegawaifk','=',$request['namalengkap']);
//        };
        if(isset($request['idDept']) &&
            $request['idDept']!="" &&
            $request['idDept']!="undefined"){
            $data = $data->where('sm.departemenfk','=', $request['idDept'] );
        };
        $data = $data->distinct();
        $data = $data->get();
        return $this->respond($data);
    }

    public function saveLembarKerjaInvestigasi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
                if ($request['data']['norec'] == '') {
                    $data = new LembarKerjaInvestigasi();
                    $data->norec = $data->generateNewId();
                    $data->kdprofile = $kdProfile;
                    $data->statusenabled = true;
                } else {
                    $data = LembarKerjaInvestigasi::where('norec', $request['data']['norec'])->where('kdprofile', $kdProfile)->first();
                }
                    if (isset( $request['data']['insidenfk'])){
                        $data->laporaninsidenfk = $request['data']['insidenfk'];
                    }
                     $data->penyebabinsidenlangsung =  $request['data']['penyebabinsidenlangsung'];
                     $data->latarbelakanginsiden =  $request['data']['latarbelakanginsiden'];
                     $data->rekomendasi =  $request['data']['rekomendasi'];
                     $data-> penanggungjawabfk =  $request['data']['penanggungjawabfk'];
                     $data->tanggalrekomendasi =  $request['data']['tanggalrekomendasi'];
                     $data->tindakan =  $request['data']['tindakan'];
                     $data->pegawaifk =  $request['data']['pegawaifk'];
                     $data->namakepala =  $request['data']['namakepala'];
                     $data->tanggalmulai =  $request['data']['tanggalmulai'];
                     $data->tanggalakhir =  $request['data']['tanggalakhir'];
                     $data->tanggaltindakan =  $request['data']['tanggaltindakan'];
                     $data->investigasilengkap =  $request['data']['investigasilengkap'];
                     $data->investigasilanjutan =  $request['data']['investigasilanjutan'];
                     $data->regrading =  $request['data']['regrading'];
                     $data->regradingfk =  $request['data']['regradingfk'];
                     $data->save();

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
                "res" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarLembarInvestigasiSederhana(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('lembarkerjainvestigasi_t as lki')
            ->JOIN('laporaninsideninternal_t as lii','lii.norec','=','lki.laporaninsidenfk')
            ->LEFTJOIN ('pegawai_m as pg','pg.id', '=','lki.penanggungjawabfk')
            ->LEFTJOIN ('pegawai_m as pg1','pg1.id','=','lki.pegawaifk')
            ->LEFTJOIN ('insidenkeselamatan_m as ik','ik.id', '=','lki.insidenkeselamatanfk')
            ->LEFTJOIN ('jeniskeselamatan_m as jkn','jkn.id', '=','ik.jeniskesalamatanfk')
            ->select(DB::raw("lki.*,pg.namalengkap as penanggungjawab, pg1.namalengkap as pegawai,ik.namakeselamatan as keselamatan,
                                     ik.jeniskesalamatanfk,jkn.jeniskeselamatan"))
            ->where('lki.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('lki.tanggalmulai', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('lki.tanggalmulai', '<=', $request['tglAkhir']);
        }
        if (isset($request['Norec']) && $request['Norec'] != "" && $request['Norec'] != "undefined") {
            $data = $data->where('lki.norec', '=', $request['Norec']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('lii.ruanganfk', '<=', $request['idRuangan']);
        }
        if (isset($request['idPelapor']) && $request['idPelapor'] != "" && $request['idPelapor'] != "undefined") {
            $data = $data->where('lii.pembuatlaporanfk', '=', $request['idPelapor']);
        }

        $data = $data->where('lki.statusenabled', true);
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function  hapusDataLembarInvestigasi (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $data = LembarKerjaInvestigasi::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                'statusenabled' => 0
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveLaporanInsidenInternal(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
            if ($request['data']['norec'] == '') {
                $data = new LaporanInsidenInternal();
                $data->norec = $data->generateNewId();
                $data->kdprofile = $kdProfile;
                $data->statusenabled = true;
            } else {
                $data = LaporanInsidenInternal::where('norec', $request['data']['norec'])->where('kdprofile', $kdProfile)->first();
            }
                $data->nocm = $request['data']['nocm'];
                $data->namapasien = $request['data']['namapasien'];
                $data->tglahir = $request['data']['tglahir'];
                $data->ruanganfk = $request['data']['ruanganfk'];
                $data->umur = $request['data']['umur'];
                $data->jeniskelaminfk = $request['data']['jeniskelaminfk'];
                $data->penanggungbiayapasienfk = $request['data']['penanggungbiayapasienfk'];
                $data->tglmasuk = $request['data']['tglmasuk'];
                $data->tglinsiden = $request['data']['tglinsiden'];
                $data->insiden = $request['data']['insiden'];
                $data->kronologisinsiden = $request['data']['kronologisinsiden'];
                $data->jenisinsiden = $request['data']['jenisinsiden'];
                $data->pelaporinsiden = $request['data']['pelaporinsiden'];
                $data->insidenpenyangkut = $request['data']['insidenpenyangkut'];
                $data->tempatinsiden = $request['data']['tempatinsiden'];
                $data->insidenterjadi = $request['data']['insidenterjadi'];
                $data->jiwa = $request['data']['jiwa'];
                $data->unitterkait = $request['data']['unitterkait'];
                $data->akibatinsiden = $request['data']['akibatinsiden'];
                $data->penanganan = $request['data']['penanganan'];
                $data->dilakukanoleh = $request['data']['dilakukanoleh'];
                $data->kejadiansama = $request['data']['kejadiansama'];
                $data->langkahpenanganan = $request['data']['langkahpenanganan'];
                $data->pembuatlaporan = $request['data']['pembuatlaporan'];
                $data->tgllapor = $request['data']['tgllapor'];
                $data->penerimalaporan = $request['data']['penerimalaporan'];
                $data->tglterima = $request['data']['tglterima'];
                $data->grading = $request['data']['grading'];
                $data->insidenkeselamatanfk = $request['data']['insidenkeselamatanfk'];
                $data->noregistrasifk = $request['data']['noregistrasifk'];
                $data->save();

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
//                "res" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function  hapusDataLaporanInsidenInternal (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $data = LaporanInsidenInternal::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                'statusenabled' => 0
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarLaporanInsidenInternal(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('laporaninsideninternal_t as lki')
            ->LEFTJOIN ('ruangan_m as ru','ru.id', '=','lki.ruanganfk')
            ->LEFTJOIN ('kelompokpasien_m as kp','kp.id', '=','lki.penanggungbiayapasienfk')
            ->LEFTJOIN ('jeniskelamin_m as jk','jk.id', '=','lki.jeniskelaminfk')
            ->LEFTJOIN ('lembarkerjainvestigasi_t as lk','lk.laporaninsidenfk', '=','lki.norec')
            ->LEFTJOIN ('insidenkeselamatan_m as ik','ik.id', '=','lki.insidenkeselamatanfk')
            ->LEFTJOIN ('jeniskeselamatan_m as jkn','jkn.id', '=','ik.jeniskesalamatanfk')
            ->LEFTJOIN ('lembarkerjainvestigasi_t AS lkt','lkt.laporaninsidenfk','=','lki.norec')
            ->select(DB::raw("lki.*,ru.namaruangan,kp.kelompokpasien,lk.norec as lk_norec,ik.namakeselamatan as keselamatan,
                                     ik.jeniskesalamatanfk,jkn.jeniskeselamatan,lkt.norec as norec_lk,jk.jeniskelamin"))
            ->where('lki.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('lki.tglinsiden', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('lki.tglinsiden', '<=', $request['tglAkhir']);
        }
        if (isset($request['Norec']) && $request['Norec'] != "" && $request['Norec'] != "undefined") {
            $data = $data->where('lki.norec', '=', $request['Norec']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('lki.ruanganfk', '<=', $request['idRuangan']);
        }
        if (isset($request['idPelapor']) && $request['idPelapor'] != "" && $request['idPelapor'] != "undefined") {
            $data = $data->where('lki.pembuatlaporanfk', '=', $request['idPelapor']);
        }

        $data = $data->where('lki.statusenabled', true);
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveInsidenKeselamatan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try {
            if ($request['norec'] == '') {
                $data = new InsidenKeselamatanPasien();
                $data->norec = $data->generateNewId();
                $data->kdprofile = $kdProfile;
                $data->statusenabled = true;
            } else {
                $data = InsidenKeselamatanPasien::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->first();
            }
            $data->tanggal = $request['tanggal'];
            $data->departemenfk = $request['departemenfk'];
            $data->keselamatanfk = $request['keselamatanfk'];
            $data->pegawaifk = $dataPegawai->objectpegawaifk;
            $data->jumlah = $request['jumlah'];
            $data->tgl = $request['tgl'];
            $data->save();

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
                "res" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function  hapusInsidenKeselamatan (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $data = InsidenKeselamatanPasien::where('norec',$request['norec'])->where('kdprofile', $kdProfile)->update([
                'statusenabled' => 0
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarInsidenKeselamatanPasien(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('insidenkeselamatanpasien_t as lki')
            ->LEFTJOIN ('insidenkeselamatan_m as ik','ik.id', '=','lki.keselamatanfk')
            ->LEFTJOIN ('jeniskeselamatan_m as jk','jk.id', '=','ik.jeniskesalamatanfk')
            ->LEFTJOIN ('departemen_m as dept','dept.id', '=','lki.departemenfk')
            ->select(DB::raw("lki.*, dept.namadepartemen as departemen, ik.namakeselamatan as keselamatan,
                                     ik.jeniskesalamatanfk,jk.jeniskeselamatan,extract(month from (lki.tanggal) as bulan"))
            ->where('lki.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('lki.tanggal', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('lki.tanggal', '<=', $request['tglAkhir']);
        }
        if (isset($request['idJenisKeselamatan']) && $request['idJenisKeselamatan'] != "" && $request['idJenisKeselamatan'] != "undefined") {
            $data = $data->where('ik.jeniskesalamatanfk', '=', $request['idJenisKeselamatan']);
        }
        if (isset($request['idKeselamatan']) && $request['idKeselamatan'] != "" && $request['idKeselamatan'] != "undefined") {
            $data = $data->where('lki.keselamatanfk', '=', $request['idKeselamatan']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('lki.departemenfk', '=', $request['idDept']);
        }
        if(isset($request['bulan']) &&
            $request['bulan']!="" &&
            $request['bulan']!="undefined"){
            $tgl = $request['bulan']  ;
            $data = $data->whereRaw("
            -- STUFF(CONVERT(varchar(10), lki.tanggal,104),1,3,'')  
            OVERLAY(to_char(lki.tanggal,'DD.MM.YYYY') placing '' from 1 for 3)= '$tgl' " );
        };

        $data = $data->where('lki.statusenabled', true);
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDataRekapSasaranMutu(Request $request){
        $Bulan = $request['tahun'];
        $idDept = $request['idDept'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw("SELECT x.bulan,x.indikator,x.target,SUM(x.num) as numerator,SUM(x.denum) as denumerator,((SUM(x.num) / SUM(x.denum))* 100) / 100 as capaian
                FROM (SELECT DISTINCT
                    sm.tglnilai,sm.tgl,CASE WHEN sm.keterangan = 'DENUM' THEN CAST(sm.nilai AS int) ELSE 0 END AS denum,
                    CASE WHEN sm.keterangan = 'NUM' THEN CAST(sm.nilai AS int) ELSE 0 END AS num,
                    CASE WHEN ir.targetpencapaian IS NULL THEN 0 ELSE ir.targetpencapaian END AS target,
                    CAST(sm.capaian AS int) as capaian,sm.keterangan,
                    ir.indikator,ir.denominator,ir.numerator,DATENAME(month, sm.tgl) as bulan
                FROM sasaranmutu_t AS sm
                INNER JOIN indikatorrensar_m AS ir ON ir.id = sm.indikatorfk
                WHERE sm.kdprofile = $kdProfile and ir.statusenabled = true
                AND extract (YEAR from sm.tglnilai) = $Bulan
                AND sm.departemenfk = $idDept) as x
                GROUP BY x.bulan,x.indikator,x.target"));
        $dataAnalisa = DB::select(DB::raw("SELECT ass.norec,ass.departemenfk,ass.tahun,ass.analisa,ass.tindaklanjut
                       FROM analisasasaranmutu_t AS ass
                       INNER JOIN departemen_m as dept on dept.id = ass.departemenfk
                       WHERE ass.kdprofile = $kdProfile and ass.tahun = $Bulan and ass.departemenfk = $idDept"));
        $result= array(
            'data' => $data,
            'analisa' => $dataAnalisa,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function saveAnalisaSasaranMutu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        try {
                if ($request['norec'] == '') {
                    $dataJadwal = new AnalisaSasaranMutu();
                    $dataJadwal->norec = $dataJadwal->generateNewId();
                    $dataJadwal->kdprofile = $kdProfile;
                    $dataJadwal->statusenabled = true;
                } else {
                    $dataJadwal = AnalisaSasaranMutu::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->first();
                }
                $dataJadwal->departemenfk = $request['departemenfk'];
                $dataJadwal->tahun = $request['tahun'];
                $dataJadwal->analisa = $request['analisa'];
                $dataJadwal->tindaklanjut = $request['tindaklanjut'];
                $dataJadwal->save();

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
                "res" => $dataJadwal,
                "as" => 'ramdanegie@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ramdanegie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPasienRegistrasi(Request $request){
        $dataNorec = $request['Norec'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw("SELECT pm.nocm,pd.noregistrasi,pm.namapasien,pm.tgllahir,ru.id,ru.namaruangan,
                pm.objectjeniskelaminfk,jk.jeniskelamin,pd.objectkelompokpasienlastfk,kp.kelompokpasien,pd.tglregistrasi
                FROM pasiendaftar_t as pd 
                INNER JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                INNER JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                WHERE pd.kdprofile = $kdProfile and pd.norec = :norec"),
            array(
                'norec' => $request['Norec'],
            ));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function saveIdentifikasiRisiko(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try {
                if ($request['norec'] == '') {
                    $data = new IdentifikasiRisiko();
                    $data->norec = $data->generateNewId();
                    $data->kdprofile = $kdProfile;
                    $data->statusenabled = true;
                } else {
                    $data = IdentifikasiRisiko::where('norec', $request['norec'])->where('kdprofile', $kdProfile)->first();
                    $dataD = IdentifikasiRisikoDetail::where('identifikasirisikofk',$request['norec'])->where('kdprofile', $kdProfile)->delete();
                }
                    $data->departemenfk = $request['departemenfk'];
                    $data->kategoririsikofk = $request['kategoririsikofk'];
                    $data->kainstalasifk = $request['instalasi'];
                    $data->kepalabidangfk = $request['kplabidang'];
                    $data->direkturfk = $request['direktur'];
                    $data->pegawaifk = $dataPegawai->objectpegawaifk;
                    $data->tanggal = $request['tanggal'];
                    $data->save();
                    $dataNorec = $data->norec;

            foreach ($request['details'] as $item) {
                $dataOP = new IdentifikasiRisikoDetail();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = $kdProfile;
                $dataOP->statusenabled = true;
                $dataOP->jenisrisiko = $item['jenisrisiko'];
                $dataOP->identifikasirisikofk = $dataNorec;
                $dataOP->keparahan = $item['keparahan'];
                $dataOP->kemungkinan = $item['kemungkinan'];
                $dataOP->skor = $item['skor'];
                $dataOP->rangkingrisiko = $item['rangkingrisiko'];
                $dataOP->pengendalian = $item['pengendalian'];
                $dataOP->rangkingaction = $item['rangkingaction'];
                $dataOP->save();
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
                "data" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarLaporanIdentifikasiRisiko(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('identifikasirisiko_t as ir')
            ->LEFTJOIN ('kategoryrisiko_m as kr','kr.id', '=','ir.kategoririsikofk')
            ->LEFTJOIN ('departemen_m as dept','dept.id', '=','ir.departemenfk')
            ->LEFTJOIN ('pegawai_m as pg','pg.id', '=','ir.kainstalasifk')
            ->LEFTJOIN ('pegawai_m as pg1','pg1.id', '=','ir.kepalabidangfk')
            ->LEFTJOIN ('pegawai_m as pg2','pg2.id', '=','ir.direkturfk')
            ->SELECT(DB::raw("ir.*,dept.namadepartemen,kr.kategoryrisiko,pg.namalengkap as kainstalasi,
                                     pg1.namalengkap as kabidang,pg2.namalengkap as direktur"))
            ->where('ir.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('ir.tanggal', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('ir.tanggal', '<=', $request['tglAkhir']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ir.departemenfk', '=', $request['idDept']);
        }
        if (isset($request['Norec']) && $request['Norec'] != "" && $request['Norec'] != "undefined") {
            $data = $data->where('ir.norec', '=', $request['Norec']);
        }
        $data = $data->where('ir.statusenabled', true);
        $data = $data->get();

        foreach ($data as $item) {
            $details = \DB::select(DB::raw("select ird.norec,ird.identifikasirisikofk,ird.jenisrisiko,ird.keparahan,
                                                   ird.kemungkinan,ird.skor,rangkingrisiko,ird.pengendalian,ird.rangkingaction
                                    from identifikasirisikodetail_t as ird
                                    where ird.kdprofile= $kdProfile and ird.identifikasirisikofk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tanggal' => $item->tanggal,
                'norec' => $item->norec,
                'kategoririsikofk' => $item->kategoririsikofk,
                'kategoryrisiko' => $item->kategoryrisiko,
                'departemenfk' => $item->departemenfk,
                'namadepartemen' => $item->namadepartemen,
                'kainstalasifk' => $item->kainstalasifk,
                'kainstalasi' => $item->kainstalasi,
                'kepalabidangfk' => $item->kepalabidangfk,
                'kabidang' => $item->kabidang,
                'direkturfk' => $item->direkturfk,
                'direktur' => $item->direktur,
                'details' => $details,
            );
        }
        if (count($data) == 0) {
            $result = [];
        }

        $result = array(
            'data' => $result,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function  hapusIdentifikasiRisiko (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $data = IdentifikasiRisiko::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                'statusenabled' => false
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveRiskRegister(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
          DB::beginTransaction();
        try {
                if ($request['data']['norec'] == '') {
                    $data = new RiskRegister();
                    $data->norec = $data->generateNewId();
                    $data->kdprofile = $kdProfile;
                    $data->statusenabled = true;
                    $data->identifikasiresikodetailfk = $request['data']['identifikasiresikodetailfk'];
                    $data->identifikasiresikofk = $request['data']['identifikasiresikofk'];
                } else {
                    $data = RiskRegister::where('norec', $request['data']['norec'])->where('kdprofile', $kdProfile)->first();
                }
                        $data->tglpenilaian = $request['data']['tglpenilaian'];
                        $data->tglevaluasi = $request['data']['tglevaluasi'];
                        $data->tglsetujui = $request['data']['tglsetujui'];
                        $data->tanggal = $request['data']['tanggal'];
                        $data->tujuan = $request['data']['tujuan'];
                        $data->lokasi = $request['data']['lokasi'];
                        $data->pemilikrisiko = $request['data']['pemilikrisiko'];
                        $data->penilaifk = $request['data']['penilaifk'];
                        $data->pengevaluasifk = $request['data']['pengevaluasifk'];
                        $data->penyetujuifk = $request['data']['penyetujuifk'];
                        $data->deskripsirisiko = $request['data']['deskripsirisiko'];
                        $data->dampak = $request['data']['dampak'];
                        $data->penyebab = $request['data']['penyebab'];
                        $data->upayakontrol = $request['data']['upayakontrol'];
                        $data->efektifitas = $request['data']['efektifitas'];
                        $data->dampakrisiko = $request['data']['dampakrisiko'];
                        $data->kemungkinan = $request['data']['kemungkinan'];
                        $data->level = $request['data']['level'];
                        $data->evaluasirisiko = $request['data']['evaluasirisiko'];
                        $data->tujuansasaran = $request['data']['tujuansasaran'];
                        $data->rencanakegiatan = $request['data']['rencanakegiatan'];
                        $data->penanggungjwabfk = $request['data']['penanggungjwabfk'];
                        $data->jadwal = $request['data']['jadwal'];
                        $data->statusjaminan = $request['data']['statusjaminan'];
                        $data->laporansingkat = $request['data']['laporansingkat'];
                        $data->ketlevel = $request['data']['ketlevel'];
                        $data->save();

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
                "data" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarLaporanRiskRegister(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('riskregister_t as rr')
            ->JOIN('identifikasirisikodetail_t AS ir','ir.norec','=','rr.identifikasiresikodetailfk')
            ->LEFTJOIN('identifikasirisiko_t AS irr','irr.norec','=','rr.identifikasiresikofk')
            ->LEFTJOIN('kategoryrisiko_m AS kr','kr.id','=','irr.kategoririsikofk')
            ->LEFTJOIN ('pegawai_m as pg','pg.id', '=','rr.penilaifk')
            ->LEFTJOIN ('pegawai_m as pg1','pg1.id', '=','rr.pengevaluasifk')
            ->LEFTJOIN ('pegawai_m as pg2','pg2.id', '=','rr.penyetujuifk')
            ->LEFTJOIN ('pegawai_m as pg3','pg3.id', '=','rr.penanggungjwabfk')
            ->SELECT(DB::raw("irr.kategoririsikofk,kr.kategoryrisiko,
                                     ir.jenisrisiko,rr.*, pg.namalengkap AS penilai,
                                     pg1.namalengkap AS pengevaluasi,
                                     pg2.namalengkap AS penyetujui,
                                     pg3.namalengkap AS penanggungjawab"))
            ->where('rr.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('irr.tanggal', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('irr.tanggal', '<=', $request['tglAkhir']);
        }
//        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
//            $data = $data->where('irr.departemenfk', '=', $request['idDept']);
//        }
        if (isset($request['Norec']) && $request['Norec'] != "" && $request['Norec'] != "undefined") {
            $data = $data->where('rr.norec', '=', $request['Norec']);
        }
        $data = $data->where('rr.statusenabled', true);
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function  hapusRiskRegister (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            $data = RiskRegister::where('norec',$request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
                'statusenabled' => false
            ]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getLaporanSensusKeselamatanPasienBulanan(Request $request) {
        $data = [];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['idRuangan'];
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $data = DB::select(DB::raw("SELECT x.namapasien,x.noregistrasi,x.namaruangan,x.tglinsiden,x.insiden,SUM(x.sentinel) AS sentinel,SUM(x.ktd) AS ktd,SUM(x.ktc) AS ktc,
			                               SUM(x.knc) as knc,SUM(x.kpc) as kpc,x.regrading,x.bulan
                            FROM (SELECT CASE WHEN pm.namapasien IS NULL THEN ii.namapasien ELSE pm.namapasien || ' (' || pm.nocm || ')' END AS namapasien,
                                 ii.tglinsiden,pd.noregistrasi,ru.namaruangan,ii.insiden,CASE WHEN ikn.jeniskesalamatanfk = 1 THEN 1 ELSE 0 END AS sentinel,
                                 CASE WHEN ikn.jeniskesalamatanfk = 2 THEN 1 ELSE 0 END AS ktd,
                                 CASE WHEN ikn.jeniskesalamatanfk = 3 THEN 1 ELSE 0 END AS ktc,
                                 CASE WHEN ikn.jeniskesalamatanfk = 4 THEN 1 ELSE 0 END AS knc,
                                 CASE WHEN ikn.jeniskesalamatanfk = 5 THEN 1 ELSE 0 END AS kpc,lk.regrading,
                                 CASE WHEN to_char(ii.tglinsiden,'M') = '1' THEN 'Januari ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '2' THEN 'Februari ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '3' THEN 'Maret ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '4' THEN 'April ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '5' THEN 'Mei ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '6' THEN 'Juni ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '7' THEN 'Juli ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '8' THEN 'Agustus ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '9' THEN 'September ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '10' THEN 'Oktober ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '11' THEN 'November ' || to_char(ii.tglinsiden,'YYYY')
                                      WHEN to_char(ii.tglinsiden,'M') = '12' THEN 'Desember ' || to_char(ii.tglinsiden,'YYYY') END AS bulan
                            FROM laporaninsideninternal_t as ii
                            LEFT JOIN pasiendaftar_t as pd on pd.norec = ii.noregistrasifk
                            INNER JOIN lembarkerjainvestigasi_t as lk on lk.laporaninsidenfk = ii.norec
                            INNER JOIN insidenkeselamatan_m as ikn on ikn.id = ii.insidenkeselamatanfk
                            INNER JOIN jeniskeselamatan_m as jk on jk.id = ikn.jeniskesalamatanfk
                            LEFT JOIN pasien_m as pm on pm.id = pd.nocmfk
                            LEFT JOIN ruangan_m as ru on ru.id = pd.objectruanganlastfk
                            WHERE ii.kdprofile = $kdProfile and ii.tglinsiden BETWEEN '$tglAwal'  AND  '$tglAkhir'
                            $paramRuangan ) as x
                            GROUP BY x.namapasien,x.noregistrasi,x.namaruangan,x.tglinsiden,x.insiden,x.regrading,x.bulan"));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function GetDetailLaporanIdentifikasiRisiko(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $details = \DB::select(DB::raw("select ird.norec,ird.identifikasirisikofk,ird.jenisrisiko,ird.keparahan,
                                ird.kemungkinan,ird.skor,rangkingrisiko,ird.pengendalian,ird.rangkingaction,ir.departemenfk,
                                dept.namadepartemen
                                from identifikasirisikodetail_t as ird
                                inner join identifikasirisiko_t as ir on ir.norec = ird.identifikasirisikofk
                                left join departemen_m as dept on dept.id = ir.departemenfk
                                where ird.kdprofile = $kdProfile and ird.norec=:norec"),
            array(
                'norec' => $request['Norec'],
            )
        );
        return $this->respond($details);
    }

    public function getLapIndikatorMutu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('sasaranmutu_t as sm')
            ->join('indikatorrensar_m as ir','ir.id','=','sm.indikatorfk')
            ->select(DB::raw("sm.tglnilai,sm.tgl,sm.nilai,sm.num,sm.denum,sm.capaian,sm.keterangan,ir.indikator,ir.denominator,ir.numerator"))
            ->where('sm.kdprofile', $kdProfile)
            ->where('ir.statusenabled', true);
//            ->orderByRaw('pg.namalengkap,pjk.tgljadwal desc');

        if(isset($request['bln']) &&
            $request['bln']!="" &&
            $request['bln']!="undefined"){
            $tgl = $request['bln']  ;
            $data = $data->whereRaw("to_char(sm.tglnilai,'yyyy-MM') ='$tgl' " );
        };
//        if(isset($request['namalengkap']) &&
//            $request['namalengkap']!="" &&
//            $request['namalengkap']!="undefined"){
//            $data = $data->where('ei.objectpegawaifk','=',$request['namalengkap']);
//        };
        if(isset($request['departemenfk']) &&
            $request['departemenfk']!="" &&
            $request['departemenfk']!="undefined"){
            $data = $data->where('sm.departemenfk','=', $request['departemenfk'] );
        };
//        $data = $data->distinct();
        $data = $data->get();
        return $this->respond($data);
    }
    public function getChartJenisKeselamatan(Request $request){
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = DB::select(DB::raw("SELECT
                    *
                FROM
                    (
                        SELECT
                            COUNT (x.jeniskeselamatan) AS jumlah,
                            x.jeniskeselamatan as name
                        FROM
                            (
                                SELECT
                                    lap.namapasien,
                                    lap.nocm,
                                    lap.tglinsiden,
                                    isn.namakeselamatan,
                                    jk.jeniskeselamatan
                                FROM
                                    laporaninsideninternal_t AS lap
                                JOIN insidenkeselamatan_m AS isn ON lap.jenisinsiden = cast(isn.id as text)
                                JOIN jeniskeselamatan_m AS jk ON jk.id = isn.jeniskesalamatanfk
                                WHERE
                                    lap.tglinsiden BETWEEN '$tglAwal'
                                AND '$tglAkhir'
                            ) AS x
                        GROUP BY
                            x.jeniskeselamatan
                    ) AS z
                ORDER BY
                    z.jumlah DESC
         "));
       
        return $this->respond($data);
    }
     public function getChartGrading(Request $request)
    {
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = DB::select(DB::raw("SELECT
                    *
                FROM
                    (
                        SELECT
                            COUNT (x.regrading) AS jumlah,
                            x.regrading as name
                        FROM
                            (
                                SELECT
                                    lap.namapasien,
                                    lap.nocm,
                                    lap.tglinsiden,
                                    gd.regrading
                                FROM
                                    laporaninsideninternal_t AS lap
                                JOIN regrading_m AS gd ON cast(gd.id as text) = lap.grading
                                WHERE
                                    lap.tglinsiden BETWEEN '$tglAwal'
                                AND '$tglAkhir'
                            ) AS x
                        GROUP BY
                            x.regrading
                    ) AS z
                ORDER BY
                    z.jumlah DESC 
         "));
       

      
        return $this->respond($data);
    }
}