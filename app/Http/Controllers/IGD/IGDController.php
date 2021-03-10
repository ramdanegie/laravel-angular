<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 8/9/2019
 * Time: 1:51 PM
 */

namespace App\Http\Controllers\IGD;

use App\Http\Controllers\ApiController;
use App\Master\JenisKelamin;
use App\Master\TempatTidur;
use App\Master\Pasien;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\DetailHasilTriase;
use App\Transaksi\HasilTriase;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PengantarPasien;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\TempBilling;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use Carbon\Carbon;

class IGDController extends  ApiController
{

    use Valet, PelayananPasienTrait;
    public function __construct() {
    parent::__construct($skip_authentication=false);
    }

    public function getCombo(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', (int)$kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();


        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.objectjenispegawaifk', 1)
            ->where('ru.kdprofile', (int)$kdProfile)
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
        $JenisKelamin = JenisKelamin::where('statusenabled', true)
            ->select('id','jeniskelamin')
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'jeniskelamin' =>$JenisKelamin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getAntrianPasienGawatDarurat( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=',
                DB::raw("pd.norec and apd.objectruanganfk=pd.objectruanganlastfk"))
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->leftjoin('batalregistrasi_t as btl','btl.pasiendaftarfk','=','pd.norec')
            ->leftjoin('strukpelayanan_t as sps','pd.nostruklastfk','=','sps.norec')
            ->select('ps.nocm','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi',
                'pd.objectpegawaifk', 'peg.namalengkap as namadokter',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'ps.tgllahir','kps.kelompokpasien','kls.namakelas','jk.jeniskelamin',
                'pd.objectruanganlastfk','sps.nostruk','ps.id as nocmfk'
            )
            ->where('pd.kdprofile', (int)$kdProfile)
            ->whereNull('btl.norec')
            ->whereIn('ru.objectdepartemenfk',[18,24,28]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['nama']) && $request['nama']!="" && $request['nama']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['nama'] .'%');
        };
        if(isset($request['noreg']) && $request['noreg']!="" && $request['noreg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noreg']);
        };
        if(isset($request['kelId']) && $request['kelId']!="" && $request['kelId']!="undefined"){
            $data = $data->where('kps.id','=', $request['kelId']);
        };
        if(isset($request['dokId']) && $request['dokId']!="" && $request['dokId']!="undefined"){
            $data = $data->where('peg.id','=', $request['dokId']);
        };

        $data=$data->orderBy('pd.tglregistrasi','desc');
        $data=$data->take(50);
        $data=$data->get();

        $result = array(
            'daftar' => $data,
            'message' => 'giw',
        );
        return $this->respond($result);
    }
    public function GetPemeriksaanTriage(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $pemeriksaanTriage = \DB::table('rm_pemeriksaantriage_m as pt')
            ->select('pt.id','pt.jenispemeriksaan as jenisPemeriksaan','pt.namatriage as namaTriage')
            ->where('pt.kdprofile', (int)$kdProfile)
            ->where('pt.statusenabled', true)
            ->get();


        $result = array(
            'jenisPemeriksaan' => $pemeriksaanTriage,

        );

        return $this->respond($result);
    }
    public function GetKategoriTriage(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $hasilKategoriTriage = \DB::table('hasilkategoritriase_m as htt')
            ->select('htt.id','htt.namahasilkategoritriase as namaHasilKategoriTriase')
            ->where('statusenabled', true)
            ->where('htt.kdprofile', (int)$kdProfile)
            ->get();


        $result = array(
            'hasilKategoriTriage' => $hasilKategoriTriage,
        );

        return $this->respond($result);
    }
    public function GetHasilTriase(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $hasilTriase = \DB::table('hasiltriase_t as ht')
            ->leftjoin('pengantarpasien_t as pp','pp.objecthasiltriasefk','=','ht.norec')
            ->select(
                'ht.norec',
                'generatetriase',
                'hasiltriasewaktu',
                'objectkategorihasiltriasefk',
                'pasien',
                'tanggalmasuk',
                'namapasien',
                'statuspasien',
                'beratbadan',
                'tekanandarah',
                'suhu',
                'nadi',
                'pernapasan',
                DB::raw('ROW_NUMBER() OVER(ORDER BY ht.norec asc) AS no')
            )
            ->where('ht.kdprofile', (int)$kdProfile)
            ->orderBy('no')
            ->take(50);

        if (isset($request['noRec']) && $request['noRec'] != "" && $request['noRec'] != "undefined") {
            $hasilTriase = $hasilTriase->where('ht.norec', $request['noRec']);
        }

        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $hasilTriase = $hasilTriase->where('ht.namapasien', 'ilike','%'. $request['namaPasien'].'%');
        }

        if (isset($request['tglMasukAwal']) && $request['tglMasukAwal'] != "" && $request['tglMasukAwal'] != "undefined") {
            if (isset($request['tglMasukAkhir']) && $request['tglMasukAkhir'] != "" && $request['tglMasukAkhir'] != "undefined") {
                $hasilTriase = $hasilTriase->whereBetween('ht.tanggalmasuk', [$request['tglMasukAwal'], $request['tglMasukAkhir']]);
            }
        }


        $hasilTriase=$hasilTriase->get();


        $hasilTriaseTandaVital = \DB::table('hasiltriase_t as ht')
            ->select('beratbadan','tekanandarah','suhu','nadi','pernapasan','norec')
            ->where('ht.norec', $request['noRec'])
            ->where('ht.kdprofile', (int)$kdProfile)
            ->get();

        $detailhasilTriase = \DB::table('detailhasiltriase_t as dht')
            ->select('norec','objectpemeriksaantriagefk as id')
            ->where('dht.objecthasiltriasefk', $request['noRec'])
            ->where('dht.kdprofile', (int)$kdProfile)
            ->get();

        $pengantarPasien = \DB::table('pengantarpasien_t as pp')
            ->leftjoin('hubungankeluarga_m as hk','hk.id','=','pp.objecthubungankeluargafk')
            ->leftjoin('rm_statusbawa_m as sb','sb.id','=','pp.objectstatusbawafk')
            ->select(
                'pp.norec',
                'pp.namakeluarga',
                'pp.objecthubungankeluargafk',
                'pp.tgllahir',
                'pp.tglkejadian',
                'pp.tempatkejadian',
                'pp.objecthasiltriasefk',
                'pp.objectjeniskelaminfk',
                'pp.objectstatusbawafk',
                'sb.id',
                'sb.name',
                'hk.id',
                'hk.namaexternal')
            ->where('pp.objecthasiltriasefk', $request['noRec'])
            ->where('pp.kdprofile', (int)$kdProfile)
            ->get();

        $result = array(
            'hasilTriase' => $hasilTriase,
            'hasilTriaseTandaVital'=>$hasilTriaseTandaVital,
            'detailHasilTriase'=>$detailhasilTriase,
            'pengantarPasien'=>$pengantarPasien
        );

        return $this->respond($result);
    }
    public function getComboTriase(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $hubunganKeluarga = \DB::table('hubungankeluarga_m as hb')
            ->select('hb.id','hb.namaexternal as namaexternal')
            ->where('statusenabled', true)
            ->get();

        $statusBawa = \DB::table('rm_statusbawa_m as sb')
            ->select('sb.id','sb.name')
            ->where('statusenabled', true)
            ->get();
        $hasilKategoriTriage = \DB::table('hasilkategoritriase_m as htt')
            ->select('htt.id','htt.namahasilkategoritriase as namaHasilKategoriTriase')
            ->where('statusenabled', true)
            ->get();
        $jenisKelamin = \DB::table('jeniskelamin_m as jk')
            ->select('jk.id','jk.namaexternal as name')
            ->where('statusenabled', true)
            ->get();

        $result = array(
            'hubunganKeluarga' => $hubunganKeluarga,
            'statusBawa' => $statusBawa,
            'hasilKategoriTriage' => $hasilKategoriTriage,
            'jenisKelamin' => $jenisKelamin,
        );

        return $this->respond($result);
    }
    public function SimpanHasilTriase(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus=false;

        //Hasil Triase
        $arrHasilTriase = json_decode($request->getContent(),true);
        $norecHasilTriase='';


        $hasilTriase =  HasilTriase::where('norec',$arrHasilTriase['noRec'])->first();
        if($hasilTriase==null){
            $hasilTriase= new HasilTriase();
            $norecHasilTriase = $hasilTriase->generateNewId();
            $hasilTriase->norec=$norecHasilTriase;
            $hasilTriase->kdprofile = (int)$kdProfile;
        }


        $hasilTriase->objectkategorihasiltriasefk=$arrHasilTriase['kategoriHasilTriase']['id'];
        $hasilTriase->generatetriase=$arrHasilTriase['generateTriase'];
        $hasilTriase->tanggalmasuk=$arrHasilTriase['tanggalMasuk'];
        $hasilTriase->namapasien=$arrHasilTriase['namaPasien'];
        $hasilTriase->statuspasien=$arrHasilTriase['statusPasien'];
        $hasilTriase->pasien=$arrHasilTriase['pasienId'];


        //Tanda Vital
        foreach($arrHasilTriase['tandaVital'] as $item) {
            if ($item['name']=='Berat Badan'){
                $hasilTriase->beratbadan=$item['nilai'];
            }

            if ($item['name']=='Tekanan Darah'){
                $hasilTriase->tekanandarah=$item['nilai'];
            }

            if ($item['name']=='Suhu'){
                $hasilTriase->suhu=$item['nilai'];
            }

            if ($item['name']=='Nadi'){
                $hasilTriase->nadi=$item['nilai'];
            }

            if ($item['name']=='Pernapasan'){
                $hasilTriase->pernapasan=$item['nilai'];
            }
        }

        try {
            $hasilTriase->save();
        }
        catch(\Exception $e){
            $transStatus = 'false';
        }


        $norecHasilTriase = $hasilTriase->norec; //ieu a


        //Pengantar Pasien

        $pengantarPasien =  PengantarPasien::where('norec',$arrHasilTriase['dataPengantar']['noRec'])->first();

        if ($pengantarPasien==null){
            $pengantarPasien= new PengantarPasien();
            $norecPengantarPasien = $pengantarPasien->generateNewId();
            $pengantarPasien->norec=$norecPengantarPasien;
            $pengantarPasien->kdprofile = (int)$kdProfile;
        }

        $pengantarPasien->namakeluarga=$arrHasilTriase['dataPengantar']['namaKeluarga'];
        $pengantarPasien->objecthubungankeluargafk=$arrHasilTriase['dataPengantar']['objectHubunganKeluargaFk'];
        $pengantarPasien->tgllahir=$arrHasilTriase['dataPengantar']['tglLahir'];
        $pengantarPasien->objectjeniskelaminfk=$arrHasilTriase['dataPengantar']['objectJenisKelaminFk'];
        $pengantarPasien->objectstatusbawafk=$arrHasilTriase['dataPengantar']['objectStatusBawaFk'];
        $pengantarPasien->tglkejadian=$arrHasilTriase['dataPengantar']['tglKejadian'];
        $pengantarPasien->tempatkejadian=$arrHasilTriase['dataPengantar']['tempatKejadian'];
        $pengantarPasien->objecthasiltriasefk=$norecHasilTriase;


//        try{
        $pengantarPasien->save();
//            $transStatus = 'true';
//        }catch(\Exception $e){
        $transStatus = 'false';
//        }


        //Detail Hasil Triase
//        $arrDetailHasilPemeriksaan=[];

        foreach($arrHasilTriase['detailHasilTriase'] as $item) {
            $objectPemeriksaanTriageFk = $item['pemeriksaanTriage']['id'];
            $norec=$item['noRec'];

            if($norec==''){
                $detailHasilTriase= new DetailHasilTriase();
                $tmp=$detailHasilTriase->generateNewId();
                $detailHasilTriase->norec=$tmp;
                $detailHasilTriase->kdprofile = (int)$kdProfile;
                echo $norec;
            }
            else{
                $detailHasilTriase =  DetailHasilTriase::where('norec',$norec)->first();
            }


            $detailHasilTriase->objecthasiltriasefk=$norecHasilTriase;
            $detailHasilTriase->objectpemeriksaantriagefk=$objectPemeriksaanTriageFk;

            try{
                $detailHasilTriase->save();
                $transStatus = 'true';
            }catch(\Exception $e){
                $transStatus = 'false';
            }


//            array_push($arrDetailHasilPemeriksaan,$detailHasilTriase);


        }


//        try{
//
//            foreach ($arrDetailHasilPemeriksaan as $data) {
//
//                $data->save();
//
//            }
//        }catch(\Exception $e){
//            $transStatus = 'false';
//        }



        if ($transStatus == 'true') {
            $transMessage = "Simpan Hasil Triase Berhasil";
            DB::commit();

            $result = array(
                "status" => 201,
                "message" => $transMessage

            );
        } else {
            $transMessage = "Simpan Hasil Triase Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage
            );
        }


//        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        return $this->respond($result, $transMessage);
    }

    public function getDaftarPasien( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('pasien_m as ps')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('ps.nocm','ps.namapasien','ps.tgldaftar', 'ps.tgllahir',
                'jk.jeniskelamin','ps.noidentitas','alm.alamatlengkap',
                'ps.id as nocmfk','ps.namaayah','ps.notelepon','ps.nohp','ps.tglmeninggal'
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
            $data = $data->where('ps.namaayah','=', $request['namaAyah']);
        };
        $data = $data->where('ps.statusenabled',true);
        // $data=$data->orderBy('ps.namapasien','asc');
        $data=$data->where('ps.kdprofile', (int)$kdProfile);
        $data=$data->take(50);
        $data=$data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
}