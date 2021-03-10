<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 07/12/2018
 * Time: 11.02
 */

namespace App\Http\Controllers\SDM;


use App\Http\Controllers\ApiController;
use App\Master\PeriodePeminjaman;
use App\Master\DiklatKategory;
use App\Traits\SettingDataFixedTrait;
use App\Transaksi\LoggingUser;
use App\Transaksi\SdmPenelitianEksternal;
use App\Transaksi\KegiatanPenelitianPegawai;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;


class PenelitianController extends ApiController {

    use Valet, SettingDataFixedTrait;
    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getDataComboPenelitian(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $kdProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id',[3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35])
            ->where('dp.statusenabled', true)
            ->where('dp.kdprofile', $kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $kdProfile)
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

        $datadept = \DB::table('departemen_m as dp')
        ->select('dp.id', 'dp.namadepartemen')
            ->where('dp.kdprofile', $kdProfile)
        ->where('dp.statusenabled', true)
        ->orderBy('dp.namadepartemen')
        ->get();

        $JurusanPeminatan = \DB::table('jurusanpeminatan_m as ru')
        ->where('ru.kdprofile', $kdProfile)
        ->where('ru.statusenabled', true)
        ->orderBy('ru.jurusanpeminatan')
        ->get();

        $Fakultas = \DB::table('sdm_fakultas_m as ru')
        ->where('ru.kdprofile', $kdProfile)
        ->where('ru.statusenabled', true)
        ->orderBy('ru.fakultas')
        ->get();

        $Institusi = \DB::table('sdm_institusipendidikan_m as ru')
            ->where('ru.kdprofile', $kdProfile)
        ->where('ru.statusenabled', true)
        ->orderBy('ru.institusipendidikan')
        ->get();

        $result = array(
            'datalogin' => $dataLogin,
            'departemen' => $datadept,
            'pegawaiuser' => $dataPegawaiUser,
            'datadept' => $dataDepartemen,
            'dataruangan' => $dataRuangan,
            'jurusanpeminatan' => $JurusanPeminatan,
            'fakultas' => $Fakultas,
            'institusi' => $Institusi,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function saveKegiatanPenelitianExternal(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $JenisLog ='';
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        DB::beginTransaction();
        try{
            if ($request['data']['norec_kpe'] == ''){
                $dataKPE = new SdmPenelitianEksternal();
                $dataKPE->kdprofile = $kdProfile;
                $dataKPE->statusenabled = true;
                $dataKPE->norec = $dataKPE->generateNewId();
                $JenisLog='Simpan Penelitian Kegiatan Eksternal';
            }else{
                $dataKPE =  SdmPenelitianEksternal::where('norec',$request['data']['norec_kpe'])->first();
                $JenisLog='Ubah Penelitian Kegiatan Eksternal';
            }
            $dataKPE->namapendamping = $request['data']['namapendamping'];
            $dataKPE->biayapenelitian = $request['data']['biayapenelitian'];
            $dataKPE->fakultasfk = $request['data']['fakultasfk'];
            $dataKPE->institusipendidikanfk = $request['data']['institusipendidikanfk']; //count tgl pasien perruanga
            $dataKPE->judulpeneltian = $request['data']['judulpeneltian'];
            $dataKPE->jurusanpeminatanfk=$request['data']['jurusanpeminatanfk'];
            $dataKPE->laporanpenelitian = $request['data']['laporanpenelitian'];
            $dataKPE->lokasipenelitian = $request['data']['lokasipenelitian'];
            $dataKPE->namapeneliti = $request['data']['namapeneliti'];
            $dataKPE->nim = $request['data']['nim'];
            $dataKPE->nomorkwitansi = $request['data']['nomorkwitansi'];
            $dataKPE->periodepengajaran = $request['data']['periodepengajaran'];
            $dataKPE->tanggalmulai = $request['data']['tanggalmulai'];
            $dataKPE->tanggalpembayaran = $request['data']['tanggalpembayaran'];
            $dataKPE->tanggalpresentasi = $request['data']['tanggalpresentasi'];
            $dataKPE->tanggalselesai = $request['data']['tanggalselesai'];
            $dataKPE->kelengkapanadministrasi = $request['data']['kelengkapanadministrasi'];
            $dataKPE->save();
            $idPP=$dataKPE->norec;

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = $JenisLog;
            $logUser->noreff =$idPP;
            $logUser->referensi='norec Penelitian Kegiatan Eksternal';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Kegiatan Penelitian Eksternal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $idPP,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Gagal Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'norec' => $idPP,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarPenelitianKegiatanEksternal (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
         $tglAwal = $request['tglAwal'];
         $tglAkhir = $request['tglAkhir'];
        $NamaPeneliti = '';
         if (isset($request['NamaPeneliti']) && $request['NamaPeneliti'] != "" && $request['NamaPeneliti'] != "undefined") {
            $NamaPeneliti = " and pe.namapeneliti ILIKE ". "'%" . $request['NamaPeneliti'] . "%'";
         }

         $Fakultas = '';
         if (isset($request['Fakultas']) && $request['Fakultas'] != "" && $request['Fakultas'] != "undefined") {
            $Fakultas = ' and pe.fakultasfk = ' . $request['Fakultas'];
         }

         $InstitusiPendidikan = '';
         if (isset($request['InstitusiPendidikan']) && $request['InstitusiPendidikan'] != "" && $request['InstitusiPendidikan'] != "undefined") {
            $InstitusiPendidikan = ' and pe.institusipendidikanfk = ' . $request['InstitusiPendidikan'];
         }

         $data = DB::select(DB::raw("select pe.*,ip.institusipendidikan,fa.fakultas,jp.jurusanpeminatan
                 from sdm_penelitianeksternal_t as pe 
                 INNER JOIN jurusanpeminatan_m as jp on jp.id = pe.jurusanpeminatanfk
                 INNER JOIN sdm_fakultas_m as fa on fa.id = pe.fakultasfk
                 INNER JOIN sdm_institusipendidikan_m as ip on ip.id = pe.institusipendidikanfk
                 where pe.kdprofile = $kdProfile and pe.tanggalmulai >= '$tglAwal' and pe.tanggalmulai <= '$tglAkhir' and pe.statusenabled = true
                 $NamaPeneliti
                 $Fakultas
                 $InstitusiPendidikan")
         );
         $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
         );
         return $this->respond($result);
    }

    public function getDetailPenelitianKegiatanEksternal (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $Norec=$request['Norec'];
        $data = DB::select(DB::raw("select top 1 pe.*,ip.institusipendidikan,fa.fakultas,jp.jurusanpeminatan
                 from sdm_penelitianeksternal_t as pe 
                 INNER JOIN jurusanpeminatan_m as jp on jp.id = pe.jurusanpeminatanfk
                 INNER JOIN sdm_fakultas_m as fa on fa.id = pe.fakultasfk
                 INNER JOIN sdm_institusipendidikan_m as ip on ip.id = pe.institusipendidikanfk
                 where pe.kdprofile = $kdProfile and pe.norec='$Norec'")
        );
        $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveBatalPenelitianEksternal(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();
        try{

            $Kel = SdmPenelitianEksternal::where('norec', $request['data']['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
//                    'statusenabled' => 'f',
                    'statusenabled' => 0,
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Penelitian Kegiatan Eksternal';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Penelitian Kegiatan Eksternal';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Penelitian Kegiatan Eksternal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveKegiatanPenelitianPegawai(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $JenisLog ='';
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();
        DB::beginTransaction();
        try{
            if ($request['data']['norec_kpe'] == ''){
                $dataKPE = new KegiatanPenelitianPegawai();
                $dataKPE->kdprofile = $kdProfile;
                $dataKPE->statusenabled = true;
                $dataKPE->norec = $dataKPE->generateNewId();
                $JenisLog='Simpan Penelitian Kegiatan Pegawai';
            }else{
                $dataKPE =  KegiatanPenelitianPegawai::where('norec',$request['data']['norec_kpe'])->where('kdprofile', $kdProfile)->first();
                $JenisLog='Ubah Penelitian Kegiatan Pegawai';
            }
            $dataKPE->pegawaifk = $request['data']['pegawaifk'];
            $dataKPE->unitkerja = $request['data']['unitkerja'];
            $dataKPE->lokasipenelitian = $request['data']['lokasipenelitian'];
            $dataKPE->judulpenelitian = $request['data']['judulpenelitian'];
            $dataKPE->tanggalmulai = $request['data']['tanggalmulai'];
            $dataKPE->tanggalselesai=$request['data']['tanggalselesai'];
            $dataKPE->biayapenelitian = $request['data']['biayapenelitian'];
            $dataKPE->jumlahbantuan = $request['data']['jumlahbantuan'];
            $dataKPE->bantuanditerima = $request['data']['bantuanditerima'];
            $dataKPE->tanggalpembayaran = $request['data']['tanggalpembayaran'];
            $dataKPE->nokwitansi = $request['data']['nokwitansi'];
            $dataKPE->kelengkapanadministrasi = $request['data']['kelengkapanadministrasi'];
            $dataKPE->tanggalpresentasi = $request['data']['tanggalpresentasi'];
            $dataKPE->tanggalproposal = $request['data']['tanggalproposal'];
            $dataKPE->tanggalpresentasi = $request['data']['tanggalpresentasi'];
            $dataKPE->laporanpenelitian = $request['data']['laporanpenelitian'];
            $dataKPE->tanggalkajian = $request['data']['tanggalkajian'];
            $dataKPE->publikasijurnal = $request['data']['publikasijurnal'];
            $dataKPE->tanggalpublikasi = $request['data']['tanggalpublikasi'];
            $dataKPE->tindaklanjut = $request['data']['tindaklanjut'];
            $dataKPE->save();
            $idPP=$dataKPE->norec;

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = $JenisLog;
            $logUser->noreff =$idPP;
            $logUser->referensi='norec Penelitian Kegiatan Pegawai';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Kegiatan Penelitian Pegawai";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'norec' => $idPP,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Gagal Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'norec' => $idPP,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveBatalPenelitianPegawai(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();
        try{

            $Kel = KegiatanPenelitianPegawai::where('norec', $request['data']['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
//                    'statusenabled' => 'f',
                    'statusenabled' => 0,
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Penelitian Kegiatan Pegawai';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Penelitian Kegiatan Pegawai';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Penelitian Kegiatan Eksternal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarPegawai (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $IdPegawai = $request['IdPegawai'];
        $data = DB::select(DB::raw("select top 1pg.id as pegawaifk,pg.namalengkap,pg.nip_pns,mp.objectunitkerjapegawaifk,uk.unitkerja 
                from pegawai_m as pg
                INNER JOIN mappegawaijabatantounitkerja_m as mp on mp.objectpegawaifk = pg.id
                LEFT JOIN unitkerja_m as uk on uk.id = mp.objectunitkerjapegawaifk
                LEFT JOIN subunitkerja_m as suk on suk.id = mp.objectsubunitkerjapegawaifk
                LEFT JOIN jabatan_m as jb on jb.id = mp.objectjabatanfk
                where pg.kdprofile = $kdProfile and pg.id='$IdPegawai'")
        );
        $result = array(
            'data'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarPenelitianKegiatanPegawai (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $NamaPeneliti = '';
        if (isset($request['NamaPeneliti']) && $request['NamaPeneliti'] != "" && $request['NamaPeneliti'] != "undefined") {
            $NamaPeneliti = " and pe.namapeneliti ILIKE ". "'%" . $request['NamaPeneliti'] . "%'";
        }

        $JudulPenelitian = '';
        if (isset($request['JudulPenelitian']) && $request['JudulPenelitian'] != "" && $request['JudulPenelitian'] != "undefined") {
            $JudulPenelitian = " and pe.judulpenelitian ILIKE ". "'%" . $request['JudulPenelitian'] . "%'";
        }

        $UnitKerja = '';
        if (isset($request['UnitKerja']) && $request['UnitKerja'] != "" && $request['UnitKerja'] != "undefined") {
            $UnitKerja = " and pe.unitkerja ILIKE ". "'%" . $request['UnitKerja'] . "%'";
        }

        $data = DB::select(DB::raw("select pe.*,pg.namalengkap,pg.nip_pns
                 from kegiatanpenelitianpegawai_t as pe 
                 INNER JOIN pegawai_m as pg on pg.id = pe.pegawaifk
                 where pe.kdprofile = $kdProfile and pe.tanggalmulai >= '$tglAwal' and pe.tanggalmulai <= '$tglAkhir' and pe.statusenabled = true
                 $NamaPeneliti
                 $JudulPenelitian
                 $UnitKerja")
        );
        $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDetailPenelitianKegiatanPegawai (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $Norec=$request['Norec'];
        $data = DB::select(DB::raw("select pe.*,pg.namalengkap,pg.nip_pns
                 from kegiatanpenelitianpegawai_t as pe 
                 INNER JOIN pegawai_m as pg on pg.id = pe.pegawaifk
                 where pe.kdprofile = $kdProfile and pe.norec='$Norec' limit 1")
        );
        $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
}
