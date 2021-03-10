<?php
/**
 * Created by PhpStorm.
 * User: GIW
 * Date: 8/2/2019
 * Time: 2:03 PM
 */



namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\ApiController;
use App\Master\JenisPegawai;
use App\Master\Pasien;
use App\Master\Produk;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\LoggingUser;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDelete;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukResep;
use App\Transaksi\TempBilling;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use Carbon\Carbon;

class LoggingController extends ApiController
{

    use Valet, PelayananPasienTrait;

    public function __construct() {
    parent::__construct($skip_authentication=false);
}


    public function saveLoggingVerifTarek(Request $request){
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $transStatus = true;
        $dataLogin = $request->all();
        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])->first();
        $struk = StrukPelayanan::where('norec', $pasienDaftar->nostruklastfk)->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Verifikasi TataRekening';
        $logUser->noreff = $pasienDaftar->nostruklastfk;
        $logUser->referensi='norec Struk Pelayanan';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];//$dataPegawaiUser[0]->id;
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        if(!empty($struk)){
            $logUser->keterangan = 'Verifikasi TataRekening No '.$struk->nostruk .' / No Registrasi '.$request['noregistrasi'];
        }

//            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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
    public function saveLoggingUnverifTarek(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();
        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])->where('kdprofile', $kdProfile)->where('kdprofile', $kdProfile)->first();
        $pasienDaftar = $pasienDaftar->norec;
        $strukPelayanan = StrukPelayanan::where('noregistrasifk', $pasienDaftar)->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Unverifikasi TataRekening';
        $logUser->referensi='norec Struk Pelayanan';
        $logUser->noreff = $strukPelayanan->norec;//$request['noregistrasi'];
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];//$dataPegawaiUser[0]->id;
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        if(!empty($strukPelayanan)){
            $logUser->keterangan = 'Unverifikasi TataRekening No '.$strukPelayanan->nostruk .' / No Registrasi '.$request['noregistrasi'];
        }
//            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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

    public function saveLoggingInputTindakan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();
        $pasien = DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','pd.norec','=','apd.noregistrasifk')
            ->join('ruangan_m as ru','apd.objectruanganfk','=','ru.id')
            ->select('pd.noregistrasi','ru.namaruangan')
            ->where('apd.norec',$request['pelayananpasien'][0]['noregistrasifk'])
            ->where('pd.kdprofile', $kdProfile)
            ->first();
        foreach ($request['pelayananpasien'] as $item) {
            $pelayananPasien = PelayananPasien::where('noregistrasifk', $item['noregistrasifk'])
                ->where('kdprofile', $kdProfile)
                ->where('produkfk', $item['produkfk'])
                ->where('kelasfk', $item['kelasfk'])
                ->where('tglpelayanan',  $item['tglpelayanan'])
                ->first();

            $produk = Produk::where('id', $item['produkfk'])
                ->where('statusenabled',true)->first();
            $newId = LoggingUser::max('id');
            $newId = $newId + 1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile = $kdProfile;
            $logUser->statusenabled = true;
            $logUser->jenislog = 'Input Tindakan';
            $logUser->referensi = 'norec PP';
            $logUser->noreff = $pelayananPasien->norec;//$request['noregistrasi'];
            $logUser->objectloginuserfk = $dataLogin['userData']['id'];//$dataPegawaiUser[0]->id;
            $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            if(!empty($produk) && !empty($pasien)){
                $logUser->keterangan = 'Input Tindakan '.$produk->namaproduk .' di ruangan '. $pasien->namaruangan .' / No Registrasi '.$pasien->noregistrasi;
            }
//            try {
            $logUser->save();
            $transMsg = "Simpan Log Sukses ";
        }
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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

    public function saveLogHapusTindakan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $requestAll = $request->all();
//        try{
        $pasien = DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','pd.norec','=','apd.noregistrasifk')
            ->join('ruangan_m as ru','apd.objectruanganfk','=','ru.id')
            ->select('pd.noregistrasi','ru.namaruangan')
            ->where('apd.norec',$request['pelayananpasiendelete'][0]['norec_apd'])
            ->where('pd.kdprofile', $kdProfile)
            ->first();
        foreach ($request['pelayananpasiendelete'] as $item){
            $PelPasien = new PelayananPasienDelete();
            $PelPasien->norec = $PelPasien->generateNewId();
            $PelPasien->kdprofile = $kdProfile;                ;
            $PelPasien->statusenabled = true;
            $PelPasien->objectloginuserfk =  $requestAll['userData']['id'];
            //        $noRegistrasi = $this->generateCode(new PasienDaftar(), 'noregistrasi', 10, $this->getDateTime()->format('ym'));
            $PelPasien->noregistrasifk =  $item['norec_apd'];
            $PelPasien->tglregistrasi = $item['tglPelayanan'];
            //        $PelPasien->aturanpakai = $New_PP['aturanpakai'];
            //        $PelPasien->generik =  $New_PP['generik'];
            $PelPasien->hargadiscount = $item['diskon'];
            $PelPasien->hargajual =  $item['harga'];
            $PelPasien->hargasatuan =  $item['harga'];
            //        $PelPasien->isdokumentasi =  $New_PP['isdokumentasi'];
            //        $PelPasien->isdosis =  $New_PP['isdosis'];
            //        $PelPasien->isinformasi =  $New_PP['isinformasi'];
            //        $PelPasien->isobat =  $New_PP['isobat'];
            //        $PelPasien->ispasien =  $New_PP['ispasien'];
            //        $PelPasien->isroute =  $New_PP['isroute'];
            //        $PelPasien->iswaktu =  $New_PP['iswaktu'];
            //        $PelPasien->jenisobatfk =  $New_PP['jenisobatfk'];
            $PelPasien->jumlah =  $item['jumlah'];
            $PelPasien->kelasfk =  $item['klid'];
            $PelPasien->kdkelompoktransaksi =  1;
            $PelPasien->keteranganlain =  'Log Delete';
            //        $PelPasien->keteranganpakai2 =  $New_PP['keteranganpakai2'];
            //        $PelPasien->keteranganpakaifk =  $New_PP['keteranganpakaifk'];
            //        $PelPasien->nilainormal =  $New_PP['nilainormal'];
            //        $PelPasien->nobatch =  $New_PP['nobatch'];
            $PelPasien->piutangpenjamin =  0;
            $PelPasien->piutangrumahsakit = 0;
            $PelPasien->produkfk =  $item['prid'];
            //        $PelPasien->routefk =  $New_PP['routefk'];
            //        $PelPasien->status =  $New_PP['status'];
            //        $PelPasien->statusorder =  $New_PP['statusorder'];
            $PelPasien->stock =  1;
            //        $PelPasien->strukorderfk =  $New_PP['strukorderfk'];
            $PelPasien->tglpelayanan =  $item['tglPelayanan'];
            $PelPasien->harganetto =  $item['harga'];
            if(isset($item['strukfk'])){
                $PelPasien->strukfk =  $item['strukfk'];
            }

            //        $PelPasien->isbenar =  $New_PP['isbenar'];
            //        $PelPasien->norectriger =  $New_PP['norectriger'];
            //        $PelPasien->jeniskemasanfk =  $New_PP['jeniskemasanfk'];
            //        $PelPasien->rke =  $New_PP['rke'];
            //        $PelPasien->strukresepfk =  $New_PP['strukresepfk'];
            //        $PelPasien->satuanviewfk =  $New_PP['satuanviewfk'];
            //        $PelPasien->nilaikonversi =  $New_PP['nilaikonversi'];
            //        $PelPasien->strukterimafk =  $New_PP['strukterimafk'];
            //        $PelPasien->dosis =  $New_PP['dosis'];
            $PelPasien->jasa =  $item['jasa'];
            $PelPasien->tglhapus = $this->getDateTime()->format('Y-m-d H:i:s');

            $PelPasien->save();
            $PPnorec = $PelPasien->norec;

            $produk = Produk::where('id', $item['prid'])
                ->where('statusenabled',true)->first();

            $dataLogin = $request->all();
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog ='Hapus Layanan';
            $logUser->noreff = $PPnorec;
            $logUser->referensi='norec PelayananPasienDelete';
//                $logUser->keterangan=$request['keterangan'];
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            if(!empty($produk) && !empty($pasien)){
                $logUser->keterangan = 'Hapus Tindakan '.$produk->namaproduk .' di ruangan '. $pasien->namaruangan .' / No Registrasi '.$pasien->noregistrasi;
            }
            $logUser->save();
        }


        $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//            $transMessage = "simpan PelPasien";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan PelayananPasien Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPP' => $PelPasien,
//                'dataPPP' => $PelPasienPetugas,
//                'dataPPD' => $PelPasienDetail,
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
        return $this->setStatusCode($result['status'])->respond($result);
//        return $this->respond($requestAll);
    }
    public function saveLoggingKonsulRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();

        $transStatus = true;
        $dataLogin = $request->all();

//        $APD = AntrianPasienDiperiksa::where('noregistrasifk', $request['norec_pd'])
//            ->where ('objectpegawaifk',$request['dokterfk'])
//            ->where ('objectkelasfk',$request['kelasfk'])
//            ->where ('objectruanganfk',$request['objectruangantujuanfk'])
//            ->first();
        $pasien = DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->join('ruangan_m as ru','apd.objectruanganfk','=','ru.id')
            ->select('pd.noregistrasi','ru.namaruangan','apd.norec')
            ->where('apd.kdprofile', $kdProfile)
            ->where('apd.noregistrasifk', $request['norec_pd'])
            ->where ('apd.objectpegawaifk',$request['dokterfk'])
            ->where ('apd.objectkelasfk',$request['kelasfk'])
            ->where ('apd.objectruanganfk',$request['objectruangantujuanfk'])
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Konsul Ruangan';
        $logUser->noreff = $pasien->norec;
        $logUser->referensi='norec Antrian Pasien Diperiksa';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];//$dataPegawaiUser[0]->id;
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        if(!empty($pasien) ){
            $logUser->keterangan = 'Konsul ke '. $pasien->namaruangan .' / No Registrasi '.$pasien->noregistrasi;
        }
//            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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
    public function saveLogInputResep(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();
        $pasien = DB::table('strukresep_t as sr')
            ->join('antrianpasiendiperiksa_t as apd','sr.pasienfk','=','apd.norec')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->select('pd.noregistrasi','sr.norec','sr.noresep')
            ->where('sr.kdprofile', $kdProfile)
            ->where('sr.pasienfk', $request['norec_apd'])
            ->where ('sr.penulisresepfk',$request['penulisresepfk'])
            ->where ('sr.ruanganfk',$request['ruanganfk'])
            ->where ('sr.tglresep',$request['tglresep'])
            ->first();
//        $strukResep= StrukResep::where('pasienfk', $request['norec_apd'])
//            ->where ('penulisresepfk',$request['penulisresepfk'])
//            ->where ('ruanganfk',$request['ruanganfk'])
//            ->where ('tglresep',$request['tglresep'])
//            ->first();

        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Input Resep Apotik';
        $logUser->noreff = $pasien->norec;
        $logUser->referensi='norec Struk Resep';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
//            try {
        if(!empty($pasien) ){
            $logUser->keterangan = 'No Resep '. $pasien->noresep .' / No Registrasi '.$pasien->noregistrasi;
        }
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";

//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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

    public function saveLogHapusResep(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien = DB::table('strukresep_t as sr')
            ->join('antrianpasiendiperiksa_t as apd','sr.pasienfk','=','apd.norec')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->select('pd.noregistrasi','sr.norec','sr.noresep')
            ->where('sr.norec', $request['norec_resep'])
            ->where('sr.kdprofile', $kdProfile)
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Hapus Resep Apotik';
        $logUser->noreff = $request['norec_resep'];
        $logUser->referensi='norec Struk Resep';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
//            try {
        if(!empty($pasien) ){
            $logUser->keterangan = 'No Resep '. $pasien->noresep .' / No Registrasi '.$pasien->noregistrasi;
        }
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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
    public function saveLogUbahRekanan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();
        $pasien= DB::table('pasiendaftar_t as pd')
            ->join('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->join('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->select('pd.noregistrasi','rek.namarekanan','kp.kelompokpasien')
            ->where('pd.norec', $request['norec_pd'])
            ->where('pd.kdprofile', $kdProfile)
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Ubah Rekanan';
        $logUser->noreff = $request['norec_pd'];

        $logUser->referensi='norec Pasien Daftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        //            try {
        if(!empty($pasien) ){
            $logUser->keterangan = 'Ubah Penjamin menjadi '.$pasien->kelompokpasien.' ('.$pasien->namarekanan.') / No Registrasi '.$pasien->noregistrasi;
        }
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
        //
        //            } catch (\Exception $e) {
        //                $transStatus = false;
        //                $transMsg = "Simpan Log Gagal ";
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
    public function saveLogPasienDaftar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasien= DB::table('pasiendaftar_t as pd')
            ->join('ruangan_m as ru','pd.objectruanganlastfk','=','ru.id')
            ->join('pasien_m as ps','pd.nocmfk','=','ps.id')
            ->select('pd.noregistrasi','ps.nocm','ru.namaruangan','ps.namapasien')
            ->where('pd.norec','=',$request['norec_pd'])
            ->where('pd.kdprofile', $kdProfile)
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Pendaftaran Pasien';
        $logUser->noreff = $request['norec_pd'];
        $logUser->referensi='norec Pasien Daftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        if(!empty($pasien) ){
            $logUser->keterangan = 'No Registrasi '.$pasien->noregistrasi .' / Nama Pasien '.$pasien->namapasien.' { '.$pasien->nocm. ' ) ke '.$pasien->namaruangan;
        }
        //            try {

        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
        //
        //            } catch (\Exception $e) {
        //                $transStatus = false;
        //                $transMsg = "Simpan Log Gagal ";
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

    }
    public function saveLogPindahKamar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();

        $transStatus = true;
        $dataLogin = $request->all();
        $pasien= DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->join('ruangan_m as ru','apd.objectruanganfk','=','ru.id')
            ->join('ruangan_m as ru2','apd.objectruanganasalfk','=','ru2.id')
            ->select('pd.noregistrasi','ru.namaruangan as tujuan','ru2.namaruangan as asal')
            ->where('apd.norec','=',$request['norec_apd'])
            ->where('apd.kdprofile', $kdProfile)
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Pasien Pindah';
        $logUser->noreff = $request['norec_apd'];
        $logUser->referensi='norec Antrian Pasien Diperiksa';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
//        return $this->respond($pasien);
        if(!empty($pasien) ){
            $logUser->keterangan ='Dari Ruangan '.$pasien->asal .' ke '.$pasien->tujuan. ' dengan No Registrasi '.$pasien->noregistrasi;
        }
        //            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
        //
        //            } catch (\Exception $e) {
        //                $transStatus = false;
        //                $transMsg = "Simpan Log Gagal ";
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

    }
    public function saveLogPulanginPasien(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();
        $pasien= DB::table('pasiendaftar_t as pd')
            ->join('ruangan_m as ru','pd.objectruanganlastfk','=','ru.id')
            ->select('pd.noregistrasi','ru.namaruangan','pd.tglpulang')
            ->where('pd.norec','=',$request['norec_pd'])
            ->where('pd.kdprofile', $kdProfile)
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Pasien Pulang';
        $logUser->noreff = $request['norec_pd'];
        $logUser->referensi='norec Pasien Daftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        if(!empty($pasien) ){
            $logUser->keterangan ='Pulang dari Ruangan '.$pasien->namaruangan .' tgl '.$pasien->tglpulang. ' dengan No Registrasi '.$pasien->noregistrasi;
        }
        //            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
        //
        //            } catch (\Exception $e) {
        //                $transStatus = false;
        //                $transMsg = "Simpan Log Gagal ";
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

    }

    public function saveLogBatalBayar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])->where('kdprofile', $kdProfile)->first();
        $pasien = Pasien::where('id',$pasienDaftar->nocmfk)->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Batal Bayar';
        $logUser->noreff = $pasienDaftar->norec;
        $logUser->referensi='norec Pasien Daftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
//        if(!empty($pasien) ){
        $logUser->keterangan ='No SBM - '.$request['nosbm'].' atas nama '.$pasien->namapasien .' ( '.$pasien->nocm.' ) No Registrasi '.$pasienDaftar->noregistrasi;
        $logUser->save();


        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'Inhuman'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'Inhuman'
            );
        }
        return $this->respond($result);

    }
    public function saveLogReturResep(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $strukResep= StrukResep::where('norec', $request['norec_resep'])
            ->where('kdprofile', $kdProfile)
            ->first();

        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Retur Resep Apotik';
        $logUser->noreff = $strukResep->norec;
        $logUser->referensi='norec Struk Resep';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        $logUser->keterangan ='Retur Resep - '.$strukResep->noresep;
//            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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

    public function saveLogBatalKirim(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $strukResep= StrukResep::where('norec', $request['norec_resep'])
            ->where('kdprofile', $kdProfile)
            ->first();

        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Retur Resep Apotik';
        $logUser->noreff = $strukResep->norec;
        $logUser->referensi='norec Struk Resep';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        $logUser->keterangan ='Retur Resep - '.$strukResep->noresep;
//            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
//
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan Log Gagal ";
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

    public function GetDaftarLoggingNew(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $LimitRow = ' limit '.$request['LimitRow'];
        $JenisLoging = ' ';
        if(isset($request['JenisLoging']) && $request['JenisLoging']!="" && $request['JenisLoging']!="undefined"){
            $JenisLoging = " and x.jenislog iLIKE '%".$request['JenisLoging']."%'";
        }
        $UserId = ' ';
        if (isset($request['UserId']) && $request['UserId']!="" && $request['UserId']!="undefined") {
            $UserId = ' and x.idpegawai = ' . $request['UserId'];
        }
        $Keterangan = ' ';
        if (isset($request['Keterangan']) && $request['Keterangan'] != "" && $request['Keterangan'] != "undefined") {
            $Keterangan = "and x.keterangan iLIKE '%".$request['Keterangan']."%'";
        }
        $jenisPegawai = ' ';
        if (isset($request['jenisPegawai']) && $request['jenisPegawai'] != "" && $request['jenisPegawai'] != "undefined") {
            $jenisPegawai = "and x.keterangan iLIKE '%".$request['jenisPegawai']."%'";
        }
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $kdProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.kdprofile', $kdProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }

        $results =array();
        $data = DB::select(DB::raw("select  * from 
                (select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'daftar pasien dengan no rm / noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ke ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join logginguser_t as lg on lg.noreff = pd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%pendaftaran pasien%'
                group by pd.norec,pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap, 
                lg.noreff,ru.namaruangan
                $LimitRow
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Pulangkan pasien dengan no rm / noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ke ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join logginguser_t as lg on lg.noreff = pd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Pasien Pulang%'
                group by pd.norec,pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap, 
                lg.noreff,ru.namaruangan
                     $LimitRow
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Konsul pasien dengan no rm / noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ke ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join logginguser_t as lg on lg.noreff = apd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = apd.objectruanganfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%konsul ruangan%'
                group by pd.norec,pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id, 
                pg.namalengkap,lg.noreff,ru.namaruangan
                     $LimitRow
                
                UNION ALL 
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Pindah pasien dengan no rm / noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ke ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join logginguser_t as lg on lg.noreff = apd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = apd.objectruanganfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Pasien Pindah%'
                group by pd.norec,pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id, 
                pg.namalengkap,lg.noreff,ru.namaruangan
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'input resep no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' noresep ' + sr.noresep + ' keruangan ' + ru.namaruangan + ' obat ' + pro.namaproduk as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join strukresep_t as sr on sr.norec = pp.strukresepfk
                inner join logginguser_t as lg on lg.noreff = sr.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = apd.objectruanganfk
                left join produk_m as pro on pro.id = pp.produkfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%input resep apotik%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,pro.namaproduk, 
                sr.noresep
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Retur resep no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' noresep ' + sr.noresep + ' keruangan ' + ru.namaruangan + ' obat ' + pro.namaproduk as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join strukresep_t as sr on sr.norec = pp.strukresepfk
                inner join logginguser_t as lg on lg.noreff = sr.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = apd.objectruanganfk
                left join produk_m as pro on pro.id = pp.produkfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Retur Resep Apotik%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,pro.namaproduk, 
                sr.noresep
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'input tindakan no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' keruangan ' + ru.namaruangan + ' tindakan ' + pro.namaproduk as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join logginguser_t as lg on lg.noreff = pp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = apd.objectruanganfk
                left join produk_m as pro on pro.id = pp.produkfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%input tindakan%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan, 
                pro.namaproduk
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'verifikasi tatarekening no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ' + ru.namaruangan +' noverifikasi '+ sp.nostruk as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join strukpelayanan_t as sp on sp.norec = pp.strukfk
                inner join logginguser_t as lg on lg.noreff = sp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%verifikasi tatarekening%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,sp.nostruk
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Unverifikasi tatarekening no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ' + ru.namaruangan +' noverifikasi '+ sp.nostruk as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join strukpelayanan_t as sp on sp.norec = pp.strukfk
                inner join logginguser_t as lg on lg.noreff = sp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Unverifikasi TataRekening%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,sp.nostruk
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'hapus resep ' + sr.noresep + ' keruangan ' + ru.namaruangan as keterangan
                from strukresep_t as sr
                inner join logginguser_t as lg on lg.noreff = sr.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = sr.ruanganfk
                where sr.kdprofile = $kdProfile and lg.jenislog ilike '%hapus resep apotik%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,ru.namaruangan,sr.noresep
                UNION ALL
                select lg.noreff,lg.tanggal,'Batal Bayar Kasir' as jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                lg.jenislog as keterangan
                from logginguser_t as lg
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                where lg.kdprofile = $kdProfile and lg.jenislog ilike '%Batal Bayar -%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Hapus Layanan (' + prd.namaproduk +') pada no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasiendelete_t as pp on pp.noregistrasifk = apd.norec
                inner join produk_m as prd on prd.id = pp.produkfk
                inner join logginguser_t as lg on lg.noreff = pp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Hapus Layanan%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,prd.namaproduk
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Ubah Tgl Layanan (' + prd.namaproduk +') pada no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join produk_m as prd on prd.id = pp.produkfk
                inner join logginguser_t as lg on lg.noreff = pp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Ubah Tgl Pelayanan%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,prd.namaproduk
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Diskon Layanan (' + prd.namaproduk +') pada no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join produk_m as prd on prd.id = pp.produkfk
                inner join logginguser_t as lg on lg.noreff = pp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Diskon Layanan%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,prd.namaproduk
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Tambah ' +jp.jenispetugaspe +' '+ pg2.namalengkap + ' pada Layanan ' + prd.namaproduk +' no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                inner join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec
                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                inner join jenispetugaspelaksana_m as jp on jp.id= ppp.objectjenispetugaspefk
                inner join produk_m as prd on prd.id = pp.produkfk
                inner join logginguser_t as lg on lg.noreff = ppp.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                inner join pegawai_m as pg2 on pg2.id = ppp.objectpegawaifk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Input/Ubah Petugas Layanan%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan,prd.namaproduk, pg2.namalengkap,
                jp.jenispetugaspe
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Hapus Konsul Ruangan ' +lg.keterangan+' no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi as keterangan
                from pasiendaftar_t as pd
                inner join logginguser_t as lg on lg.noreff = pd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Hapus Konsul%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,lg.keterangan
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap, 
                'Ubah Tgl Ruangan ' +ru.namaruangan+' no rm/noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi as keterangan
                from pasiendaftar_t as pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                inner join ruangan_m as ru on ru.id=apd.objectruanganfk
                inner join logginguser_t as lg on lg.noreff = apd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Ubah Tgl Detail Registrasi%'
                group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap,pm.nocm,pd.noregistrasi,ru.namaruangan
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'edit registrasi dengan no rm / noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' dari ' + lg.keterangan + ' ke ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                inner join logginguser_t as lg on lg.noreff = pd.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pasien_m as pm on pm.id = pd.nocmfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Edit Registrasi%'
                group by pd.norec,pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap, 
                lg.noreff,ru.namaruangan,lg.keterangan 
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Stok Opname ' + ru.namaruangan + ' ( ' + prd.namaproduk + ' ) qty ' + CAST(spo.qtyprodukreal as VARCHAR) as keterangan
                from strukclosing_t as sc
                inner join logginguser_t as lg on lg.noreff = sc.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join stokprodukdetailopname_t as spo on spo.noclosingfk = sc.norec
                inner join produk_m as prd on prd.id = spo.objectprodukfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = sc.objectruanganfk
                where sc.kdprofile = $kdProfile and lg.jenislog ilike '%Stok Opname%'
                group by sc.norec,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap, 
                lg.noreff,ru.namaruangan,lg.keterangan ,prd.namaproduk,spo.qtyprodukreal
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Batal Kirim ' + sc.nokirim + ' ' + ru.namaruangan + ' ( ' + prd.namaproduk + ' ) qty ' +   CAST(kp.qtyproduk as VARCHAR) as keterangan
                from strukkirim_t as sc
                inner join logginguser_t as lg on lg.noreff = sc.norec
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join kirimproduk_t as kp on kp.nokirimfk = sc.norec
                inner join produk_m as prd on prd.id = kp.objectprodukfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                left join ruangan_m as ru on ru.id = sc.objectruanganfk
                where sc.kdprofile = $kdProfile and lg.jenislog ilike '%Batal Kirim%'
                group by sc.norec,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap, 
                lg.noreff,ru.namaruangan,lg.keterangan ,prd.namaproduk,kp.qtyproduk,sc.nokirim
                
                UNION ALL
                
                select lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,
                'Batal Pulang Pasien dengan no rm / noregistrasi ' + pm.nocm + ' / ' + pd.noregistrasi + ' ke ' + ru.namaruangan as keterangan
                from pasiendaftar_t as pd
                INNER join logginguser_t as lg on lg.noreff = pd.noregistrasi
                INNER join loginuser_s as lu on lu.id = lg.objectloginuserfk
                INNER join pasien_m as pm on pm.id = pd.nocmfk
                INNER join pegawai_m as pg on pg.id = lu.objectpegawaifk
                INNER join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where pd.kdprofile = $kdProfile and lg.jenislog ilike '%Batal Pulang Pasien%'
                group by pm.nocm,pd.noregistrasi,pm.namapasien,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id, 
                pg.namalengkap,lg.noreff,ru.namaruangan
                
                -- select lg.noreff,lg.tanggal,lg.jenislog,
                -- lu.id as userid, lu.namauser,pg.id as idpegawai,pg.namalengkap,jp.jenispegawai as keterangan
                -- from loginuser_s as lu
                -- inner join logginguser_t as lg on cast(lg.noreff as INT) =lu.id 
                -- inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                -- inner join jenispegawai_m as jp on jp.id = pg.objectjenispegawaifk
                -- where lg.jenislog like '%Login User%'
                -- group by lg.noreff,lg.tanggal,lg.jenislog,lu.id,lu.namauser,pg.id,pg.namalengkap, 
                -- lg.keterangan ,jp.jenispegawai
                 
                 ) as x
                 where x.tanggal >= '$tglAwal' and x.tanggal <= '$tglAkhir'
                 $JenisLoging 
                 $UserId
                 $Keterangan
                 $jenisPegawai
                 ")
        );
//            $data=$data->take($LimitRow);
        foreach ($data as $item) {

            $results[] = array(
                'noreff' => $item->noreff,
                'tanggal' => $item->tanggal,
                'jenislog' => $item->jenislog,
                'userid' => $item->userid,
                'namauser' => $item->namauser,
                'idpegawai' => $item->idpegawai,
                'namalengkap' => $item->namalengkap,
                'keterangan' => $item->keterangan,
            );
        }
//        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataPegawaiUser,
            'message' => 'Cepot',
            'str' => $strRuangan,
        );
        return $this->respond($result);
    }

    public function saveLoggingAll(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $transStatus = true;
        $dataLogin = $request->all();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = $request['jenislog'];
        $logUser->noreff =$request['noreff'];
        $logUser->referensi=$request['referensi'];
        $logUser->keterangan=$request['keterangan'];
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        try {
            $logUser->save();
            $res = $logUser->jenislog ;
            $transMsg = "Simpan Log ".$res." Sukses ";
        } catch (\Exception $e) {
            $transStatus = false;
            $transMsg = "Simpan Log Gagal ";
        }

        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMsg,
                "as" => 'Inhuman'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMsg
            );
        }
        return $this->respond($result);
    }

    public function getCombo(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $jenisPegawai = JenisPegawai::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $result = array(
            "jenispegawai" => $jenisPegawai,
            "as" => 'Inhuman'
        );

        return $this->respond($result);
    }

    public function getAktivitasUser(Request $request ){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idPegawai='';
        if (isset($request['UserId']) && $request['UserId'] !='undefined' && $request['UserId'] != '' ){
            $idPegawai = 'and pg.id='.$request['UserId'];
        }
        $jenisPegawaiId='';
        if (isset($request['jenisPegawaiId']) && $request['jenisPegawaiId'] !='undefined' && $request['jenisPegawaiId'] != '' ){
            $jenisPegawaiId = 'and jsp.id='.$request['jenisPegawaiId'];
        }
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = DB::select(DB::raw("
            SELECT pg.id AS idpegawai, pg.namalengkap, jsp.jenispegawai,
            count(lggr.objectloginuserfk) as jumlahaktivitas 
            FROM logginguser_t as lggr 
            LEFT JOIN loginuser_s as lgs on lggr.objectloginuserfk=lgs.id
            LEFT JOIN pegawai_m as pg on lgs.objectpegawaifk=pg.id 
            LEFT JOIN jenispegawai_m as jsp on pg.objectjenispegawaifk=jsp.id
            where lggr.kdprofile = $kdProfile and lggr.statusenabled = true 
            and lggr.tanggal between  '$tglAwal' and '$tglAkhir'
            $idPegawai
            $jenisPegawaiId
            GROUP BY pg.id, jsp.jenispegawai, pg.namalengkap
            ORDER BY jumlahaktivitas DESC;"));
        $result = array(
            "data" => $data,
            "by" => "akbar",
        );
        return  $this->respond($result) ;
    }

    public function saveLogBayartTagihanPasien(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

//        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Pembayaran Tagihan Pasien - '.$request['nosbm'];
        $logUser->noreff = $request['nosbm'];
        $logUser->referensi='norec Pasien Daftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        $logUser->save();


        if ($transStatus == true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => 'ea@epic'
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => 'ea@epic'
            );
        }
        return $this->respond($result);

    }
    public function getDaftarLog(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $LimitRow ='';
        $JenisLoging = ' ';
        if(isset($request['JenisLoging']) && $request['JenisLoging']!="" && $request['JenisLoging']!="undefined"){
            $JenisLoging = " and lg.jenislog ilike '%".$request['JenisLoging']."%'";
        }
        $UserId = ' ';
        if (isset($request['UserId']) && $request['UserId']!="" && $request['UserId']!="undefined") {
            $UserId = ' and pg.id = ' . $request['UserId'];
        }
        $Keterangan = ' ';
        if (isset($request['Keterangan']) && $request['Keterangan'] != "" && $request['Keterangan'] != "undefined") {
            $Keterangan = "and lg.keterangan ilike '%".$request['Keterangan']."%'";
        }
        $jenisPegawai = ' ';
        if (isset($request['jenisPegawai']) && $request['jenisPegawai'] != "" && $request['jenisPegawai'] != "undefined") {
            $jenisPegawai = "and lg.keterangan ilike '%".$request['jenisPegawai']."%'";
        }
          $LimitRow = ' ';
        if (isset($request['LimitRow']) && $request['LimitRow']!="" && $request['LimitRow']!="undefined") {
            $LimitRow = ' limit ' . $request['LimitRow'];
        }

        $data = DB::select(DB::raw("
                select  lg.noreff,lg.tanggal,lg.jenislog,lu.id as userid,lu.namauser,pg.id as idpegawai,pg.namalengkap,lg.keterangan
                from logginguser_t as lg
                inner join loginuser_s as lu on lu.id = lg.objectloginuserfk
                inner join pegawai_m as pg on pg.id = lu.objectpegawaifk
                where lg.kdprofile = $kdProfile and lg.tanggal between '$tglAwal' and '$tglAkhir'
                --and lg.kdprofile=11
                 $JenisLoging 
                 $UserId
                 $Keterangan
                 $jenisPegawai
                 order by lg.tanggal desc
                  $LimitRow")
        );


        $result = array(
            'daftar' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function saveLogMeninggalPasienRJ(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();
        $pasien= DB::table('pasiendaftar_t as pd')
            ->join('ruangan_m as ru','pd.objectruanganlastfk','=','ru.id')
            ->join('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->select('pd.noregistrasi','ru.namaruangan','pd.tglpulang')
            ->where('pd.norec','=',$request['norec_pd'])
            ->where('pd.kdprofile', $kdProfile)
            ->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $kdProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Pasien Meninggal '. $pasien->namadepartemen;
        $logUser->noreff = $request['norec_pd'];
        $logUser->referensi='norec Pasien Daftar';
        $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
        $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        if(!empty($pasien) ){
            $logUser->keterangan ='Meninggal dari Ruangan '.$pasien->namaruangan .' tgl '.$pasien->tglpulang. ' dengan No Registrasi '.$pasien->noregistrasi;
        }
        //            try {
        $logUser->save();
        $transMsg = "Simpan Log Sukses ";
        //
        //            } catch (\Exception $e) {
        //                $transStatus = false;
        //                $transMsg = "Simpan Log Gagal ";
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
    }
}