<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 8/2/2019
 * Time: 10:46 AM
 */
namespace App\Http\Controllers\TataRekening;
use App\Http\Controllers\ApiController;
use App\Master\Alamat;
use App\Master\AsuransiPasien;
use App\Master\KelompokPasien;
use App\Master\Pasien;
use App\Traits\InternalList;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\LoggingUser;
use App\Transaksi\PemakaianAsuransi;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPengeluaran;
use App\Transaksi\StrukPelayananPenjaminDetail;
use App\Transaksi\StrukVerifikasi;
use App\Transaksi\SuratKematianPasienDelete;
use App\Transaksi\SuratPelimpahanJenazah;
use App\Transformers\Master\KelompokPasienTransformer;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\PasienDaftar;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\StrukPelayananPenjamin;
use App\Transaksi\TempBilling;
use App\Transaksi\MapRuanganToAkomodasi;
use App\Master\JenisPetugasPelaksana;
use App\Master\Pegawai;
use App\Master\Ruangan;
use App\Master\Departemen;
//use Illuminate\Support\Facades\Http;
//use App\Transaksi\StrukPelayananDetailK;
use App\Transaksi\HistoriCetakDokumen;
use App\Traits\Valet;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\LogAcc;
use App\Traits\SettingDataFixedTrait;
use Carbon\Carbon;
class TagihanController  extends ApiController
{


    use Valet, PelayananPasienTrait, SettingDataFixedTrait,InternalList;
    public function __construct() {
        parent::__construct($skip_authentication=false);
    }


    public function verifikasiTagihan(Request $request){
        $noRegister = $request['noRegister'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->where('kdprofile', $idProfile)->first();
        $pelayanan = $this->getPelayananPasienByNoRegistrasi($noRegister);
        $billing = $this->getBillingFromPelayananPasien($pelayanan);
        $totalBilling = $billing->totalBilling;
        $isRawatInap = $this->isPasienRawatInap($pasienDaftar);
        if ($pasienDaftar->nostruklastfk == null && $isRawatInap) {
            $totalBilling += (int)($totalBilling * $this->getPercentageBiayaAdmin());
            $totalBilling += $this->getBiayaMaterai($totalBilling);
        }

        $totalDeposit = $billing->totalDeposit;
        $totalKlaim = 0;
        $result = array(
            'pasienID' => $pasienDaftar->pasien->id,
            'noCm' => $pasienDaftar->pasien->nocm,
            'noRegistrasi' => $pasienDaftar->noregistrasi,
            'namaPasien' => $pasienDaftar->pasien->namapasien,
            'tglPulang' => $pasienDaftar->tglpulang,
            'jenisPasien' => $pasienDaftar->kelompok_pasien->kelompokpasien,
            'kelasRawat' => $pasienDaftar->kelas->namakelas,
            'noAsuransi' => '-', //ambil dari asuransi pasien -m tapi datanya blum ada brooo..
            'kelasPenjamin' => '-', //ini blum ada datanya gimana mau munculin,, gila yaa ?
            'billing' => $totalBilling,
            'penjamin' => $penjamin = $this->getPenjamin($pasienDaftar)->namarekanan,
            'deposit' => $totalDeposit, //ngambil dari mana
            'totalKlaim' => $totalKlaim, //ngambil dari mana? dihitunga gak
            'jumlahBayar' => $totalBilling - $totalDeposit - $totalKlaim, //jumlah bayar ini perlu gak
            'jumlahPiutang' => 0, //ini ngambil dari pembayaran gak ?
            'needDokument' => true, //ini ngambil ddokument dari mana ? pake datafixed
            'dokuments' => [], // sama ini juga ngambilnya dari mana ..
        );
        return $this->respond($result);
    }

    public function verifikasiTagihan2(Request $request){
        $noRegister = $request['noRegister'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->first();
//        $pelayanan = $this->getPelayananPasienByNoRegistrasi($noRegister);
        $pelayanan = DB::select(DB::raw("select pd.objectruanganlastfk,pd.nostruklastfk,ps.id as psid,ps.nocm,
            ps.namapasien,pd.tglpulang,kps.kelompokpasien,kl.namakelas,
            pd.objectruanganlastfk,ru.objectdepartemenfk,
            pd.noregistrasi,pp.* from pasiendaftar_t pd
            left JOIN antrianpasiendiperiksa_t apd on apd.noregistrasifk=pd.norec
            left JOIN pelayananpasien_t pp on pp.noregistrasifk=apd.norec
            left JOIN pasien_m ps on ps.id=pd.nocmfk
            left JOIN kelas_m kl on kl.id=pd.objectkelasfk
            left JOIN kelompokpasien_m kps on kps.id=pd.objectkelompokpasienlastfk
            left JOIN ruangan_m ru on ru.id=pd.objectruanganlastfk
            where pd.kdprofile = $idProfile and pd.noregistrasi=:noregistrasi and pp.strukfk is null;"),
            array(
                'noregistrasi' => $noRegister,
            )
        );

        $pelayanantidakterklaim = DB::select(DB::raw("select pd.objectruanganlastfk,pd.nostruklastfk,ps.id as psid,ps.nocm,
            ps.namapasien,pd.tglpulang,kps.kelompokpasien,kl.namakelas,
            pd.objectruanganlastfk,ru.objectdepartemenfk,
            pd.noregistrasi,pp.* from pasiendaftar_t pd
            INNER JOIN antrianpasiendiperiksa_t apd on apd.noregistrasifk=pd.norec
            INNER JOIN pelayananpasientidakterklaim_t pp on pp.noregistrasifk=apd.norec
            INNER JOIN pasien_m ps on ps.id=pd.nocmfk
            INNER JOIN kelas_m kl on kl.id=pd.objectkelasfk
            INNER JOIN kelompokpasien_m kps on kps.id=pd.objectkelompokpasienlastfk
            INNER JOIN ruangan_m ru on ru.id=pd.objectruanganlastfk
            where pd.kdprofile = $idProfile and pd.noregistrasi=:noregistrasi and pp.strukfk is null;"),
            array(
                'noregistrasi' => $noRegister,
            )
        );
//        $pelayanan=$pelayanan[0];
//        $billing = $this->getBillingFromPelayananPasien($pelayanan);
        $totalBilling = 0;
        $totalKlaim = 0;
        $totalDeposit = 0;
        $totaltakterklaim =0;

        foreach ($pelayanantidakterklaim as $values) {
//            if ($values->produkfk == $this->getProdukIdDeposit()) {
//                $totalDeposit = $totalDeposit + $values->hargajual;
//            } else {
                $totaltakterklaim = $totaltakterklaim + (($values->hargajual - $values->hargadiscount) * $values->jumlah) + $values->jasa;
//            }
        }

        foreach ($pelayanan as $value) {
            if ($value->produkfk == $this->getProdukIdDeposit()) {
                $totalDeposit = $totalDeposit + $value->hargajual;
            } else {
                $totalBilling = $totalBilling + (($value->hargajual - $value->hargadiscount) * $value->jumlah) + $value->jasa;
            }

        }

//        $billing = new \stdClass();
//        $billing->totalBilling = $totalBilling;
//        $billing->totalKlaim= $totalKlaim;
//        $billing->totalDeposit = $totalDeposit;

        $totalBilling = $totalBilling;
//        $isRawatInap  = $this->isPasienRawatInap2($pelayanan);
        $pelayanan = $pelayanan[0];
        $isRawatInap = false;
        if ($pelayanan->objectruanganlastfk != null) {
            if ((int)$pelayanan->objectdepartemenfk == 16) {
                $isRawatInap = true;
            }
        }


//        if ($pelayanan->nostruklastfk == null && $isRawatInap) {
//            $totalBilling = $totalBilling + number_format($totalBilling * $this->getPercentageBiayaAdmin(), 0, '', '');
//            $totalBilling = $totalBilling + $this->getBiayaMaterai($totalBilling);
//        }

        $totalDeposit = $totalDeposit;
        $totalKlaim = 0;
        $result = array(
            'pasienID' => $pelayanan->psid,
            'noCm' => $pelayanan->nocm,
            'noRegistrasi' => $pelayanan->noregistrasi,
            'namaPasien' => $pelayanan->namapasien,
            'tglPulang' => $pelayanan->tglpulang,
            'jenisPasien' => $pelayanan->kelompokpasien,
            'kelasRawat' => $pelayanan->namakelas,
            'noAsuransi' => '-', //ambil dari asuransi pasien -m tapi datanya blum ada brooo..
            'kelasPenjamin' => '-', //ini blum ada datanya gimana mau munculin,, gila yaa ?
            'billing' => $totalBilling,
            'penjamin' => '',//$penjamin=$this->getPenjamin($pelayanan)->namarekanan,
            'deposit' => $totalDeposit, //ngambil dari mana
            'totalKlaim' => $totalKlaim, //ngambil dari mana? dihitunga gak
            'jumlahBayar' => $totalBilling - $totalDeposit - $totalKlaim, //jumlah bayar ini perlu gak
            'jumlahBayarNew' =>  $totalBilling - $totalDeposit - $totalKlaim - $totaltakterklaim, //jumlah bayar dengan tindakan yang tidak d klaim
            'jumlahPiutang' => 0, //ini ngambil dari pembayaran gak ?
            'needDokument' => true, //ini ngambil ddokument dari mana ? pake datafixed
            'dokuments' => [], // sama ini juga ngambilnya dari mana ..
            'totaltakterklaim' => $totaltakterklaim,
            'isRawatInap' => $isRawatInap,
        );
        return $this->respond($result);
    }

    public function simpanVerifikasiTagihan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noRegister=['noRegister'];
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser and pg.kdprofile = $idProfile"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $transStatus = true;
        $transMsg = null;
        $totalBilling = 0;
        $totalDeposit = 0;
        DB::beginTransaction();

        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->where('kdprofile', $idProfile)->first();
        $pelayanan = $pasienDaftar->pelayanan_pasien()->select('pelayananpasien_t.*')->whereNull('strukfk')->get();
        $pelayananDetail = $pasienDaftar->pelayanan_pasien_detail()->whereNull('strukfk')->get();
        $dataPD = \DB::table('pasiendaftar_t as pd')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select('ru.objectdepartemenfk')
            ->where('pd.noregistrasi', $noRegister)
            ->where('pd.kdprofile', $idProfile)
            ->first();
//        return $this->respond($dataPD->objectdepartemenfk);
        if ($dataPD->objectdepartemenfk == 16) {
            $isRawatInap = true;
        } else {
            $isRawatInap = false;
        }
//        $isRawatInap  = $this->isPasienRawatInap($pasienDaftar);
        if (count($pelayanan) == 0) {
            $transStatus = false;
            $transMsg = "Pelayanan yang dilakukan pasien tidak ada.";
        }

        if ($transStatus) {
            $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 10, 'S', $idProfile);
            $strukPelayanan = new StrukPelayanan();
            $strukPelayanan->norec = $strukPelayanan->generateNewId();
            $lastPelayanan = null;

            foreach ($pelayanan as $pel) {
                $harga = ($pel->hargajual == null) ? 0 : $pel->hargajual;
                $diskon = ($pel->hargadiscount == null) ? 0 : $pel->hargadiscount;
                if ($pel->nilainormal == -1) {
                    $totalDeposit += ($harga * $pel->jumlah);
                } else {
                    $totalBilling += (($harga - $diskon) * $pel->jumlah) + $pel->jasa;
                }
            }

            $strukPelayanan->kdprofile = $idProfile;
            $strukPelayanan->nocmfk = $pasienDaftar->nocmfk;
            $strukPelayanan->noregistrasifk = $pasienDaftar->norec;
            $strukPelayanan->objectkelaslastfk = $pasienDaftar->objectkelasfk;
            $strukPelayanan->objectkelompoktransaksifk = 1;
            $strukPelayanan->objectpegawaipenerimafk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();
            $strukPelayanan->nostruk = $noStruk;
            $strukPelayanan->totalharusdibayar = $totalBilling - $totalDeposit;
            $strukPelayanan->tglstruk = $this->getDateTime();
            $strukPelayanan->objectruanganfk = $pasienDaftar->objectruanganlastfk;

//            try{
            $strukPelayanan->save();
//            }
//            catch(\Exception $e){
//                $transStatus= false;
////                $transMsg = $e->getMessage();
////                throw new \Exception($e);
//                $transMsg = "Simpan Biaya Administrasi Gagal {SP}";
//
//            }

            if ($transStatus) {
                foreach ($pelayanan as $pel) {
//                    $harga = ($pel->hargajual==null) ? 0 : $pel->hargajual;
//                    if($pel->nilainormal==-1){
//                        $totalDeposit += ($harga * $pel->jumlah);
//                    }else{
//                        $totalBilling += ($harga * $pel->jumlah);
//                    }

                    $pel->strukfk = $strukPelayanan->norec;
                    //$norecStukPelayananStr = $strukPelayanan->norec;

                    try {
                        $pel->save();
                    } catch (\Exception $e) {
                        $transStatus = false;
//                    throw new \Exception($e);
                        $transMsg = "Transaksi Gagal (update pp)";
                        break;
                    }
                }
            }


//            if ($transStatus && $isRawatInap) {
//                $AntrianPasienDiperiksa = $pasienDaftar->antrian_pasien_diperiksa->first();
//                $biayaAdministrasi = number_format($totalBilling * $this->getPercentageBiayaAdmin(), 0, '', '');
//                if ($biayaAdministrasi > 0 && $transStatus) {
//                    $PP = new PelayananPasien();
//                    $PP->norec = $PP->generateNewId();
//                    $PP->kdprofile = $this->getKdProfile();
//                    $PP->kdprofile = $this->getKdProfile();
//                    $PP->noregistrasifk = $AntrianPasienDiperiksa->norec;
//                    $PP->aturanpakai = "-";
//                    $PP->hargasatuan = (float)$biayaAdministrasi;
//                    $PP->hargajual = (float)$biayaAdministrasi;
//                    $PP->jumlah = 1;
//                    $PP->nilainormal = 1;
//                    $PP->keteranganlain = "Biaya Administrasi";
//                    $PP->keteranganpakai2 = "-";
//                    $PP->produkfk = $this->getProdukBiayaAdministrasi()->id;
//                    $PP->stock = 1;
//                    $PP->tglpelayanan = $this->getDateTime();
//                    $PP->strukfk = $strukPelayanan->norec;
//
//                    try {
//                        $PP->save();
//                    } catch (\Exception $e) {
//                        $transStatus = false;
////                    throw new \Exception($e);
//                        $transMsg = "Simpan Biaya Administrasi Gagal {BA}";
//                    }
//                    $totalBilling += $biayaAdministrasi;
//                    $strukPelayanan->totalharusdibayar += $biayaAdministrasi;
//                }
//            }


//            if ($transStatus && $isRawatInap) {
//                $biayaMaterai = $this->getBiayaMaterai($totalBilling);
//                if ($biayaMaterai > 0 && $transStatus) {
//                    $totalBilling += $biayaMaterai;
//                    $PP = new PelayananPasien();
//                    $PP->norec = $PP->generateNewId();
//                    $PP->kdprofile = $this->getKdProfile();
//                    $PP->kdprofile = $this->getKdProfile();
//                    $PP->noregistrasifk = $AntrianPasienDiperiksa->norec;
//                    $PP->aturanpakai = "-";
//                    $PP->hargasatuan = (float)$biayaMaterai;
//                    $PP->hargajual = (float)$biayaMaterai;
//                    $PP->jumlah = 1;
//                    $PP->nilainormal = 1;
//                    $PP->keteranganlain = "Biaya Administrasi";
//                    $PP->keteranganpakai2 = "-";
//                    $PP->produkfk = $this->getProdukBiayaMaterai()->id;
//                    $PP->stock = 1;
//                    $PP->tglpelayanan = $this->getDateTime();
//                    $PP->strukfk = $strukPelayanan->norec;
//                    try {
//                        $PP->save();
//                    } catch (\Exception $e) {
//                        $transStatus = false;
//                        $transMsg = "Simpan Biaya Administrasi Gagal {BM}";
//                    }
//                    $totalBilling += $biayaMaterai;
//                    $strukPelayanan->totalharusdibayar += $biayaMaterai;
//                }
//            }

        }

        if ($transStatus) {
            foreach ($pelayananDetail as $pelDel) {
                $pelDel->strukfk = $strukPelayanan->norec;
                try {
                    $pelDel->save();
                } catch (\Exception $e) {
                    $transStatus = false;
                    $transMsg = "Transaksi Gagal (insert SP)";
                    break;
                }
            }

        }

        $totalKlaim = (float)$request['totalKlaim'];
        if ($transStatus && $totalKlaim > 0) {
            $strukPelayanan->totalharusdibayar -= $totalKlaim;
            $strukPelayanan->totalprekanan = $totalKlaim;
            if ($pasienDaftar->objectkelompokpasienlastfk == $this->getKelompokPasienPerjanjian()) {
                $rekananpenjamin_id = 0;
            } elseif ($pasienDaftar->objectkelompokpasienlastfk == 2 || $pasienDaftar->objectkelompokpasienlastfk == 4) {
                $rekananpenjamin_id = 2552;
            } else {
                $rekananpenjamin_id = 0; //masih bypass. yang kelompok pasien penjanjian diisini cuma kondisinhya jiga ada klim tapi gak penjaminnya..

            }
            $SPPenjamin = new StrukPelayananPenjamin();
            $SPPenjamin->norec = $SPPenjamin->generateNewId();
            //$norecStukPelayananPenjaminStr =  $SPPenjamin->norec;
            $SPPenjamin->kdprofile = $idProfile;
            $SPPenjamin->kdkelompokpasien = $pasienDaftar->objectkelompokpasienlastfk;
            $SPPenjamin->kdrekananpenjamin = $rekananpenjamin_id;
            $SPPenjamin->totalbiaya = $totalBilling;
            $SPPenjamin->totalsudahppenjamin = $totalKlaim; //? apa in ?
            $SPPenjamin->totalsisaharusdibayar = $totalKlaim;
            $SPPenjamin->totalppenjamin = $totalKlaim;
            $SPPenjamin->totalharusdibayar = $totalKlaim;
            $SPPenjamin->totalsudahdibayar = 0;
            $SPPenjamin->totalsudahdibebaskan = 0;
            $SPPenjamin->totalsisapiutang = $totalKlaim;
            $SPPenjamin->totaldibayarlebih = 0;
            $SPPenjamin->nostrukfk = $strukPelayanan->norec;

            $pasienDaftar->nostruklastfk = $strukPelayanan->norec;
//            //jurnal verif SPP
//            if($transStatus && ($request['totalKlaim']>0)){
//                $logAcc =new  LogAcc;
//                $logAcc->norec = $logAcc->generateNewId();
//                $logAcc->jenistransaksi = 'Verifikasi Penjamin TataRekening';
//                $logAcc->noreff =$strukPelayanan->norec;
//                $logAcc->status = 0;
//                $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
//                if($transStatus){
//                    try{
//                        $logAcc->save();
//                    }
//                    catch(\Exception $e){
//                        $transStatus= false;
//                        $transMsg = "Simpan logAcc penjamin Gagal {SPP}";
//
//                    }
//                }
//            }
//            //end jurnal verif SPP
            try {
                $SPPenjamin->save();
            } catch (\Exception $e) {
                $transStatus = false;
//                throw new \Exception($e);
                $transMsg = "Transaksi Gagal (Insert SPP)";
            }
        }

        if ($transStatus) {
            $pasienDaftar->nostruklastfk = $strukPelayanan->norec;
            try {
                $pasienDaftar->save();
            } catch (\Exception $e) {
                $transStatus = false;
                $transMsg = "Transaksi Gagal (update Pdaf)";
            }
        }


        if ($transStatus) {
            //JURNAL
            //jurnal verif sP
            $logAcc = new  LogAcc;
            $logAcc->norec = $logAcc->generateNewId();
            $logAcc->jenistransaksi = 'Verifikasi TataRekening';
            $logAcc->noreff = $strukPelayanan->norec;
            $logAcc->status = 0;
            $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            try {
                $logAcc->save();
            } catch (\Exception $e) {
                $transStatus = false;
                $transMsg = "Simpan logAcc Gagal {SP}";

            }
        }
        //END jurnal verif sP
        // $saldo = ((float)$totalBilling-(float)$request['totalKlaim']);
        //DB::beginTransaction();

//        if($transStatus && ($totalBilling>$request['totalKlaim'])){
//            $saldoJurnal = array();
//            $saldoJurnal[] = array(
//                'account_id' => 1348, //Piutang Perorangan
//                'balance'    => 'D',
//                'saldo'      => $saldo
//            );
//            $saldoJurnal[] = array(
//                'account_id' =>   1356, //Piutang Pasien dalam Pelayanan  Non BPJS
//                'balance'    =>  'K',
//                'saldo'      => $saldo
//            );
//
//            $detailJurnal[] = array(
//                "tgltransaksi" => $this->getDateTime(),
//                "notransaksi" =>  $strukPelayanan->nostruk,
//                "saldoJurnal" => $saldoJurnal,
//                "ruanganid" => $strukPelayanan->objectruanganfk,
//                "kelompoktransaksiid" => 1,
//                "nobuktitransaksi" => $strukPelayanan->nostruk,
//                "tglbuktitransaksi" => $this->getDateTime(),
//                "keteranganlainnya" => "Verifikasi TataRekening Pasien: ".$pasienDaftar->noregistrasi." - ".$pasienDaftar->pasien->namapasien ,
//            );
//
//            $jurnal = array(
//                "noposting" => $noPosting = $this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'BT-'.$this->getDateTime()->format('ym')),
//                "detailJurnal" =>$detailJurnal
//            );
//            if($this->postingJournal($jurnal)){
//
//            }else{
//                $transStatus = $this->transStatus;
//                $transMsg= $this->transMessage;
//            }
//        }
        //END JURNAL

        if ($transStatus) {
            try {
                $strukPelayanan->save();
            } catch (\Exception $e) {
                $transStatus = false;
//                $transMsg = $e->getMessage();
//                throw new \Exception($e);
                $transMsg = "Simpan Biaya Administrasi Gagal {SP}";

            }
        }

        //JURNAL ke penjamin
//        $detailJurnal = array();
//        $saldo = (float)$request['totalKlaim'];
//        $bpjsID=2552;
//        if($transStatus && ($request['totalKlaim']>0)){
//            if($pasienDaftar->objectkelompokpasienlastfk == $this->getKelompokPasienPerjanjian()){
//                $account_id = 1346;//piutang perjanjian
//                $account_lawan_id =  1356; //Piutang Pasien dalam Pelayanan  Non BPJS
//            }else{
//                $penjamin_id = 12;
//                if($penjamin_id==$bpjsID){
//                    //cari rawat inap atau tidak ini belum nyentuh ke bpjs sama sekali..
//                    if(true){//RI
//                        $account_id = 1359;//Piutang BPJS YBD  Rawat Inap
//                        $account_lawan_id =  1355; //Piutang Pasien dalam Pelayanan   BPJS
//                    }else{
//                        $account_id = 1358;//Piutang BPJS YBD  Rawat Jalan
//                        $account_lawan_id =  1355; //Piutang Pasien dalam Pelayanan   BPJS
//                    }
//
//                }else{
//                    $account_id = 1340;//piutang perusahan/asuransi
//                    $account_lawan_id =  1356; //Piutang Pasien dalam Pelayanan  Non BPJS
//                }
//            }
//            $saldoJurnal = array();
//            $saldoJurnal[] = array(
//                'account_id' =>   $account_id, //Piutang Pasien dalam Pelayanan  Non BPJS
//                'balance'    =>  'D',
//                'saldo'      => $saldo
//            );
//            $saldoJurnal[] = array(
//                'account_id' => $account_lawan_id, //Piutang Perorangan
//                'balance'    => 'K',
//                'saldo'      => $saldo
//            );
//
//
//            $detailJurnal[] = array(
//                "tgltransaksi" => $this->getDateTime(),
//                "notransaksi" =>  $strukPelayanan->nostruk,
//                "saldoJurnal" => $saldoJurnal,
//                "ruanganid" => $strukPelayanan->objectruanganfk,
//                "kelompoktransaksiid" => 1,
//                "nobuktitransaksi" => $strukPelayanan->nostruk,
//                "tglbuktitransaksi" => $this->getDateTime(),
//                "keteranganlainnya" => "Verifikasi TataRekening Pasien: ".$pasienDaftar->noregistrasi." - ".$pasienDaftar->pasien->namapasien,
//            );
//
//            $jurnal = array(
//                "noposting" => $noPosting = $this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'BT-'.$this->getDateTime()->format('ym')),
//                "detailJurnal" =>$detailJurnal
//            );
//            if($this->postingJournal($jurnal)){
//
//            }else{
//                $transStatus = $this->transStatus;
//                $transMsg= $this->transMessage;
//            }
//        }
//


        if ($transStatus) {
            $this->setStatusCode(201);
            $transMsg = "Transaksi Berhasil";
//            DB::rollBack();
            DB::commit();
        } else {
            $this->setStatusCode(400);
            DB::rollBack();
        }
        return $this->respond([], $transMsg);
    }
    public function detailTagihan($noRegister,Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $delete = TempBilling::where('noregistrasi', $noRegister)->where('kdprofile', $kdProfile)->delete();
//        ini_set('max_execution_time', 3000); //6 minutes
        $dataRuangan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select( 'ru.namaruangan as namaruangan')
            ->where('pd.kdprofile', $kdProfile)
            ->where('pd.noregistrasi', $noRegister)
            ->first();
        $pelayanan=[];
        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->leftjoin('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->leftjoin('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftjoin('kelompokprodukbpjs_m as kpBpjs', 'kpBpjs.id', '=', 'pr.objectkelompokprodukbpjsfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
            ->join('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
            ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->leftjoin('pegawai_m as pgpj', 'pgpj.id', '=', 'pd.objectpegawaifk')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->leftjoin('strukresep_t as sr', 'sr.norec', '=', 'pp.strukresepfk')
            ->leftjoin('ruangan_m as rusr', 'rusr.id', '=', 'sr.ruanganfk')
            ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
            ->leftjoin('pegawai_m as pgsr', 'pgsr.id', '=', 'sr.penulisresepfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftjoin('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftjoin('pegawai_m as pgsbm', 'pgsbm.id', '=', 'sbm.objectpegawaipenerimafk')
            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                'pg.id as pgid', 'pg.namalengkap', 'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk', 'ru2.objectdepartemenfk as deptid',
                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
                'kp.id as kpid', 'kp.kelompokpasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
                'pas.id as pasid', 'pas.nocm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jk', 'pd.noregistrasi', 'pas.namapasien',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang', 'pas.notelepon', 'kls.id as klsid', 'kls.namakelas',
                'pd.objectrekananfk as rekananid', 'ru2.namaruangan as ruanganlast', 'kls2.id as klsid2', 'kls2.namakelas as namakelas2',
                'sr.noresep', 'rk.namarekanan', 'rusr.namaruangan as ruanganfarmasi', 'pgsr.namalengkap as penulisresep', 'jp.jenisproduk', 'kpBpjs.kelompokprodukbpjs as kelompokprodukbpjs',
                'pgpj.namalengkap as dokterpj', 'pp.jasa', 'kamar.namakamar', 'sp.totalharusdibayar', 'sp.totalprekanan', 'sppj.totalppenjamin',
                'sp.totalbiayatambahan', 'pgsbm.namalengkap as namalengkapsbm','pd.kdprofile','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis',
                'ru2.id as ruanganlastid'
            )
            ->where('pd.kdprofile', $kdProfile)
            ->where('pd.noregistrasi', $noRegister)
            ->orderBy('pp.tglpelayanan', 'pp.rke');


            if ($request['jenisdata'] == 'resep'){
//                if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
//                    $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
//                };
                $pelayanan = $pelayanan->whereNotNull('pp.aturanpakai');
            }
            if ($request['jenisdata'] == 'layanan'){
                if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
                    $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
                };
                $pelayanan = $pelayanan->whereNull('pp.aturanpakai');
            }
            $pelayanan = $pelayanan->get();



//        return $this->respond($pelayanan);

//        if (is_null($pelayanan[0]->kdprofile)==true){
//            $pelayanandetail = \DB::table('pasiendaftar_t as pd')
//                ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
//                ->join('pelayananpasiendetail_t as ppd', 'ppd.noregistrasifk', '=', 'apd.norec')
//                ->join('komponenharga_m as kh', 'kh.id', '=', 'ppd.komponenhargafk')
//                ->select('ppd.pelayananpasien as norec_pp', 'ppd.norec', 'kh.komponenharga', 'ppd.jumlah', 'ppd.hargasatuan', 'ppd.hargadiscount')
//                ->where('pd.noregistrasi', $noRegister)
//                ->orderBy('ppd.tglpelayanan')
//                ->get();
//        }

        $pelayananpetugas = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pelayananpasienpetugas_t as ptu', 'ptu.nomasukfk', '=', 'apd.norec')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ptu.objectpegawaifk')
            ->select('ptu.pelayananpasien', 'pg.namalengkap')
            ->where('pd.kdprofile', $kdProfile)
            ->where('ptu.objectjenispetugaspefk', 4)
            ->where('pd.noregistrasi', $noRegister)
            ->get();

        if ($request['jenisdata'] == 'bill'){
            $pelayanankelasdijamin = DB::select(DB::raw("
                select pp.norec, pd.noregistrasi,pd.objectkelasfk,kls_pd.namakelas,asp.objectkelasdijaminfk,
                kls_dijamin.namakelas as namakelas_dijamin,pp.hargajual,pp.jumlah,
                case when hnpk.harganetto2 is null then pp.hargajual else hnpk.harganetto2 end as harga_kelasdijamin
                from pasiendaftar_t as pd
                LEFT JOIN pemakaianasuransi_t as pas on pas.noregistrasifk=pd.norec
                left join asuransipasien_m as asp on asp.id=pas.objectasuransipasienfk
                left join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                left join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                left join harganettoprodukbykelas_m as hnpk on hnpk.objectprodukfk=pp.produkfk and hnpk.objectkelasfk=asp.objectkelasdijaminfk
                left join kelas_m as kls_pd on kls_pd.id=pd.objectkelasfk
                left join kelas_m as kls_dijamin on kls_dijamin.id=asp.objectkelasdijaminfk
                where pd.kdprofile = $kdProfile and noregistrasi=:noregistrasi;
            "),
                array(
                    'noregistrasi' => $noRegister,
                )
            );
        }
        $totototol=0;

        $dataTotaldibayar = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                where  pd.noregistrasi=:noregistrasi and sp.nosbmlastfk is not null and pp.produkfk not in (402611);
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );

        $Totdibayar = 0;
        if (count($pelayanan) > 0) {
            $alamat = [];
            $alamat = \DB::table('alamat_m as al')
                ->select('al.alamatlengkap')
                ->where('al.kdprofile', $kdProfile)
                ->where('al.nocmfk', $pelayanan[0]->nocmfk)
                ->first();

            if (empty($alamat)) {
                $alamatTea = '0';
            } else {
                $alamatTea = $alamat->alamatlengkap;
            }

            if (empty($dataTotaldibayar)) {
                $Totdibayar = 0;
            } else {
                $Totdibayar = $dataTotaldibayar[0]->total;
            }

            $totalBilling = 0;
            $norecAPD = '';
            $norecSP = '';
            $details = array();
            $dibayar=0;
            $diverif=0;
            foreach ($pelayanan as $value) {
                $isRawatInap = false;
                if ($value->deptid == 16) {
                    $isRawatInap = true;
                }
                if ($value->produkfk == $this->getProdukIdDeposit()) {
                    continue;
                }
                $komponen = [];
//            if (is_null($pelayanan[0]->kdprofile)==true){
//                foreach ($pelayanandetail as $lalala) {
//                    if ($lalala->norec_pp == $value->norec) {
//                        $komponen[] = array(
//                            'norec_pp' => $lalala->norec_pp,
//                            'norec' => $lalala->norec,
//                            'komponenharga' => $lalala->komponenharga,
//                            'jumlah' => $lalala->jumlah,
//                            'hargasatuan' => $lalala->hargasatuan,
//                            'hargadiscount' => $lalala->hargadiscount,
//                        );
//                    }
//                }
//            }


                $dokter = [];
                $NamaDokter = '-';
                foreach ($pelayananpetugas as $hahaha) {
                    if ($hahaha->pelayananpasien == $value->norec) {
                        $NamaDokter = $hahaha->namalengkap;
                    }
                }

                $jasa = 0;
                if (isset($value->jasa) && $value->jasa != "" && $value->jasa != "undefined") {
                    $jasa = $value->jasa;
                }
                $kmpn = [];
//            if (is_null($pelayanan[0]->kdprofile)==true) {
//                foreach ($komponen as $njir) {
//                    $kmpn[] = array(
//                        'norec_pp' => $njir['norec_pp'],
//                        'norec' => $njir['norec'],
//                        'komponenharga' => $njir['komponenharga'],
//                        'jumlah' => $njir['jumlah'],
//                        'hargasatuan' => $njir['hargasatuan'],
//                        'hargadiscount' => $njir['hargadiscount'],
//                        'strukfk' => $value->nostruk . ' / ' . $value->nosbm,
//                    );
//                }
//            }

                $harga = (float)$value->hargajual;
                $diskon = (float)$value->hargadiscount;
                $detail = array(
                    'norec' => $value->norec,
                    'tglPelayanan' => $value->tglpelayanan,
                    'namaPelayanan' => $value->namaproduk,
                    'dokter' => $NamaDokter,
                    'jumlah' => $value->jumlah,
                    'kelasTindakan' => @$value->namakelas,
                    'ruanganTindakan' => @$value->namaruangan,
                    'harga' => $harga,
                    'diskon' => $diskon,
                    'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                    'jppid' => '',
                    'jenispetugaspe' => '',
                    'strukfk' => $value->nostruk . ' / ' . $value->nosbm,
                    'sbmfk' => $value->nosbm,
                    'pgid' => '',
                    'ruid' => $value->ruid,
                    'prid' => $value->prid,
                    'klid' => $value->klid,
                    'norec_apd' => $value->norec_apd,
                    'norec_pd' => $value->norec_pd,
                    'norec_sp' => $value->norec_sp,
                    'komponen' => $kmpn,
                    'jasa' => $jasa,
                    'aturanpakai' => $value->aturanpakai,
                    'iscito' => $value->iscito,
                    'isparamedis' => $value->isparamedis
                );

                if (is_null($value->nosbm) == true) {
                    $dibayar = $dibayar + (($harga - $diskon) * $value->jumlah) + $jasa;
                }
                if (is_null($value->nostruk) == true) {
                    $diverif = $diverif + (($harga - $diskon) * $value->jumlah) + $jasa;
                }
                $norecAPD = $value->norec_apd;
                $norecSP = $value->norec_sp;
                $totalBilling = $totalBilling + (($harga - $diskon) * $value->jumlah) + $value->jasa;
                $total =  (($harga - $diskon) * $value->jumlah) + $value->jasa;
                $details[] = $detail;

//                $totototol = $totototol + $total;
                if ($request['jenisdata'] == 'bill') {
//                    $delete = TempBilling::where('noregistrasi', $noRegister)->delete();
                    $kelas_dijamin = 0;
                    $harga_dijamin = 0;
                    $total_dijamin = 0;
                    foreach ($pelayanankelasdijamin as $kupret) {
                        if ($value->norec == $kupret->norec) {
                            $kelas_dijamin = $kupret->namakelas_dijamin;
                            $harga_dijamin = $kupret->harga_kelasdijamin;
                            $total_dijamin = ((float)$kupret->harga_kelasdijamin * (float)$value->jumlah) + $value->jasa;
                        }
                    }
//                    try {
                        $namakelas = $value->namakelas;
                        $ruanganTindakan = $value->namaruangan;
                        $jenisprodukMaster = $value->jenisproduk;
                        if ($value->kpid == 2) {
                            $jenisproduk = $value->kelompokprodukbpjs;
                        } else {
                            $jenisproduk = $value->jenisproduk;
                        }
                        if ($value->noresep != null) {
                            $namakelas = '';
                            $ruanganTindakan = $value->ruanganfarmasi . '     Resep No: ' . $value->noresep;
//                    $jenisproduk = 'Resep';
                        }
                        if ($value->namaproduk == 'Biaya Administrasi') {
                            $ruanganTindakan = $dataRuangan->namaruangan;
                        }
                        if ($value->namaproduk == 'Biaya Materai') {
                            $ruanganTindakan = $dataRuangan->namaruangan;
                        }
                        //Biaya Administrasi
                        $namarekanan = '-';
                        if ($value->namarekanan != null) {
                            $namarekanan = $value->namarekanan;
                        }
                        $namaproduk = $value->namaproduk;
                        if ($value->rke != null) {
                            $namaproduk = 'R/' . $value->rke . ' ' . $value->namaproduk;
                        }

                        $hargadiscount = 0;
                        if ($value->hargadiscount != null) {
                            $hargadiscount = $value->hargadiscount;
                        }


                        $hargakurangdiskon = (float)$value->hargajual - $hargadiscount;
                        $hargakalijml = $hargakurangdiskon * (float)$value->jumlah;
                        $jasa = 0;
                        if ($value->jasa != null) {
                            $jasa = $value->jasa;
                        }
                        $total = ((float)$value->jumlah * ((float)$value->hargajual - $hargadiscount)) + $jasa;


                        $namakamar = '-';
                        if ($value->namakamar != null) {
                            $namakamar = $value->namakamar;
                        }
                        $totalprekanan = 0;
                        if ($value->totalprekanan != null) {
                            $totalprekanan = $value->totalprekanan;
                        }
                        $totalppenjamin = 0;
                        if ($value->totalppenjamin != null) {
                            $totalppenjamin = $value->totalppenjamin;
                        }
                        $totalbiayatambahan = 0;
                        if ($value->totalbiayatambahan != null) {
                            $totalbiayatambahan = $value->totalbiayatambahan;
                        }
                        $totalharusdibayar = $value->totalharusdibayar;
                        if ($value->totalharusdibayar < 0) {
                            $totalharusdibayar = 0;
                        }

                        $temp = new TempBilling();#
                        $tempNorec = $temp->generateNewId();#
                        $temp->norec = $tempNorec;#
                        $temp->kdprofile = $kdProfile;
                        $temp->norec_pp = $value->norec;#
                        $temp->tglstruk = $value->tglstruk;#
                        $temp->nobilling = $noRegister;#
                        $temp->nokwitansi = $value->nosbm;#
                        $temp->noregistrasi = $noRegister;#
                        $temp->nocm = $value->nocm;#
                        $temp->namapasienjk = $value->namapasien . ' ( ' . $value->jk . ' )';#
                        $temp->unit = $value->ruanganlast;#
                        $temp->objectdepartemenfk = $value->deptid;#
                        $temp->namakelas = $namakelas;#
                        $temp->dokterpj = $value->dokterpj;#
                        $temp->tglregistrasi = $value->tglregistrasi;#
                        $temp->tglpulang = $value->tglpulang;#
                        $temp->namarekanan = $namarekanan;#
                        $temp->tglpelayanan = $value->tglpelayanan;#
                        $temp->ruangantindakan = $ruanganTindakan;#
                        if ($value->iscito == 1){
                            $temp->namaproduk = $namaproduk . ' -> CITO';#
                        }else{
                            $temp->namaproduk = $namaproduk;
                        }
                        $temp->penulisresep = $value->penulisresep;#
                        $temp->jenisproduk = $jenisproduk;#
                        $temp->dokter = $NamaDokter;#
                        $temp->jumlah = $value->jumlah;#
                        $temp->hargajual = $value->hargajual;#
                        $temp->diskon = $hargadiscount;#
                        $temp->total = $total;#
                        $temp->namakamar = $namakamar;#
                        $temp->tipepasien = $value->kelompokpasien;#
                        $temp->totalharusdibayar = $totalharusdibayar;#
                        $temp->totalprekanan = $totalprekanan;#
                        $temp->totalppenjamin = $totalppenjamin;#
                        $temp->totalbiayatambahan = $totalbiayatambahan;#
                        $temp->user = $value->namalengkapsbm;#
                        $temp->namakelaspd = $value->namakelas2;#
                        $temp->nama_kelasasal = $kelas_dijamin;#
                        $temp->hargajual_kelasasal = $harga_dijamin;#
                        $temp->total_kelasasal = $total_dijamin;#
                        $temp->jenisprodukmaster = $jenisprodukMaster;
                        $temp->totaldibayar = $Totdibayar;
                        $temp->save();

//                        $transStatus = 'true';
//                    } catch (\Exception $e) {
//                        $transStatus = 'false';
//                        $transMessage = "Simpan TEMP";
//                    }
                }
            }
        }

        $dataTotalBill = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                where pd.kdprofile = $kdProfile and pd.noregistrasi=:noregistrasi and pp.produkfk not in (402611) ;
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $totalBilling=0;
        $totalBilling = $dataTotalBill[0]->total;

        $dataTotaldibayar = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                where pd.kdprofile = $kdProfile and pd.noregistrasi=:noregistrasi and sp.nosbmlastfk is not null and pp.produkfk not in (402611);
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $dibayar=0;
        $dibayar = $dataTotaldibayar[0]->total;

        $dataTotalverif = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                where pd.kdprofile = $kdProfile and pd.noregistrasi=:noregistrasi and pp.strukfk is not null and pp.produkfk not in (402611)
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $diverif=0;
        $diverif = $dataTotalverif[0]->total;


        if (count($pelayanan) > 0) {
            $komponen = [];
            if ($pelayanan[0]->nostruklastfk == null && $isRawatInap) {
                $biayaAdministrasi = number_format($totalBilling * $this->getPercentageBiayaAdmin(), 0, '', '');
//                $detail = array(
//                    'norec' => 'norecAdmin',
//                    'tglPelayanan' => Carbon::now()->toDateString(),
//                    'namaPelayanan' => $this->getProdukBiayaAdministrasi()->namaproduk,
//                    'dokter' => '-',
//                    'jumlah' => 1,
//                    'kelasTindakan' => '',
//                    'ruanganTindakan' => '',
//                    'harga' => $biayaAdministrasi,
//                    'diskon' => 0,
//                    'total' => $biayaAdministrasi,
//                    'strukfk' => null,
//                    'sbmfk' => null,
//                    'pgid' => '-',
//                    'ruid' => '-',
//                    'prid' => '-',
//                    'klid' => '-',
//                    'norec_apd' => $norecAPD,
//                    'norec_sp' => $norecSP,
//                    'komponen' => $komponen,
//                    'jasa' => 0,
//                    'aturanpakai' => null,
//                );
//                $totalBilling += $biayaAdministrasi;
//                if ($biayaAdministrasi > 0) {
//                    $details[] = $detail;
//                }

                $biayaMaterai = $this->getBiayaMaterai($totalBilling);
//                $detail = array(
//                    'norec' => 'norecMaterai',
//                    'tglPelayanan' => Carbon::now()->toDateString(),
//                    'namaPelayanan' => $this->getProdukBiayaMaterai()->namaproduk,
//                    'dokter' => '-',
//                    'jumlah' => 1,
//                    'kelasTindakan' => '',
//                    'ruanganTindakan' => '',
//                    'harga' => $biayaMaterai,
//                    'diskon' => 0,
//                    'total' => $biayaMaterai,
//                    'strukfk' => null,
//                    'sbmfk' => null,
//                    'pgid' => '-',
//                    'ruid' => '-',
//                    'prid' => '-',
//                    'klid' => '-',
//                    'norec_apd' => $norecAPD,
//                    'norec_sp' => $norecSP,
//                    'komponen' => $komponen,
//                    'jasa' => 0,
//                    'aturanpakai' => null,
//                );

//                $totalBilling += $biayaMaterai;
//                if ($biayaMaterai > 0) {
//                    $details[] = $detail;
//                }

            }
            if (count($pelayanan) == 0) {
                empty($details);
            }


            try {
                $rekanan = [];
                $rekanan = \DB::table('rekanan_m as al')
                    ->select('al.namarekanan')
                    ->where('al.kdprofile', $kdProfile)
                    ->where('al.id', $pelayanan[0]->rekananid)
                    ->first();
                $penjamin = $rekanan->namarekanan;
            } catch (\Exception $e) {
                $penjamin = '-';
            }
            try {
                $kelompokPasien = $pelayanan[0]->kelompokpasien;
            } catch (\Exception $e) {
                $kelompokPasien = '-';
            }

            try {
                $agama = $pelayanan[0]->agama;
            } catch (\Exception $e) {
                $agama = '-';
            }
            try {
                $umur = $this->hitungUmur($pelayanan[0]->tgllahir);
            } catch (\Exception $e) {
                $umur = '-';
            }

            $keluarga = '-';
            try {
                if ($pelayanan[0]->objectstatusperkawinanfk == '1') {
                    $keluarga = $pelayanan[0]->namaayah;
                } else {
                    $keluarga = $pelayanan[0]->namasuamiistri;
                }
            } catch (\Exception $e) {
                $keluarga = '-';
            }

            $result = array(
                'pasienID' => $pelayanan[0]->pasid,
                'noCm' => $pelayanan[0]->nocm,
                'jenisKelamin' => $pelayanan[0]->jeniskelamin,
                'noRegistrasi' => $pelayanan[0]->noregistrasi,
                'namaPasien' => $pelayanan[0]->namapasien,
                'lastRuangan' => $pelayanan[0]->ruanganlast,
                'tglMasuk' => $pelayanan[0]->tglregistrasi,
                'tglPulang' => $pelayanan[0]->tglpulang,
                'jenisPasien' => $kelompokPasien,
                'kelasRawat' => $pelayanan[0]->namakelas2,
                'kelasId' => $pelayanan[0]->klsid2,
                'noAsuransi' => '-',
                'kelasPenjamin' => '-',
                'namaPenjamin' => $penjamin,
                'penjamin' => $penjamin,
                'deposit' => $this->getDepositPasien($pelayanan[0]->noregistrasi),
                'totalKlaim' => 0,
                'jumlahBayar' => 0,
                'jumlahPiutang' => 0,
                'billing' => (float) $totalBilling,
                'norec_pd' => $pelayanan[0]->norec_pd,
                'strukfk' => $value->nostruk,
                'telepon' => $pelayanan[0]->notelepon,
                'alamat' => $alamatTea,
                'tgllahir' => $pelayanan[0]->tgllahir,
                'agama' => $agama,
                'umur' => $umur,
                'keluarga' => $keluarga,
                'details' => $details,
                'lalal' => $pelayanan[0]->nostruklastfk,
                '$isRawatInap' => $isRawatInap,
                'diverif' => $diverif,
                'dibayar' => $dibayar,
	            'statuspasien' => $pelayanan[0]->statuspasien,
                'ruanganlastid' => $pelayanan[0]->ruanganlastid,
            );
        }else{
            $pelayanan = \DB::table('pasiendaftar_t as pd')
                ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
                ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
                ->leftjoin('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
                ->leftjoin('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
                ->leftjoin('kelompokprodukbpjs_m as kpBpjs', 'kpBpjs.id', '=', 'pr.objectkelompokprodukbpjsfk')
                ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
                ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                ->join('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
                ->join('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
                ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
                ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
                ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
                ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
                ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
                ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
                ->leftjoin('pegawai_m as pgpj', 'pgpj.id', '=', 'pd.objectpegawaifk')
                ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
                ->leftjoin('strukresep_t as sr', 'sr.norec', '=', 'pp.strukresepfk')
                ->leftjoin('ruangan_m as rusr', 'rusr.id', '=', 'sr.ruanganfk')
                ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
                ->leftjoin('pegawai_m as pgsr', 'pgsr.id', '=', 'sr.penulisresepfk')
                ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                ->leftjoin('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
                ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
                ->leftjoin('pegawai_m as pgsbm', 'pgsbm.id', '=', 'sbm.objectpegawaipenerimafk')
                ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                    'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                    'pg.id as pgid', 'pg.namalengkap', 'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk', 'ru2.objectdepartemenfk as deptid',
                    'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
                    'kp.id as kpid', 'kp.kelompokpasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
                    'pas.id as pasid', 'pas.nocm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jk', 'pd.noregistrasi', 'pas.namapasien',
                    'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang', 'pas.notelepon', 'kls.id as klsid', 'kls.namakelas',
                    'pd.objectrekananfk as rekananid', 'ru2.namaruangan as ruanganlast', 'kls2.id as klsid2', 'kls2.namakelas as namakelas2',
                    'sr.noresep', 'rk.namarekanan', 'rusr.namaruangan as ruanganfarmasi', 'pgsr.namalengkap as penulisresep', 'jp.jenisproduk', 'kpBpjs.kelompokprodukbpjs as kelompokprodukbpjs',
                    'pgpj.namalengkap as dokterpj', 'pp.jasa', 'kamar.namakamar', 'sp.totalharusdibayar', 'sp.totalprekanan', 'sppj.totalppenjamin',
                    'sp.totalbiayatambahan', 'pgsbm.namalengkap as namalengkapsbm','pd.kdprofile','pp.aturanpakai','pd.statuspasien','ru2.id as ruanganlastid'
                )
                ->where('pd.kdprofile', $kdProfile)
                ->where('pd.noregistrasi', $noRegister)
                ->orderBy('pp.tglpelayanan', 'pp.rke');


//            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
//                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
//            };
            $pelayanan = $pelayanan->first();

            $isRawatInap = false;
            if ($pelayanan->deptid == 16) {
                $isRawatInap = true;
            }

            $alamat = [];
            $alamat = \DB::table('alamat_m as al')
                ->select('al.alamatlengkap')
                ->where('al.nocmfk', $pelayanan->nocmfk)
                ->get();

            if (count($alamat) == 0) {
                $alamatTea = '0';
            } else {
                $alamatTea = $alamat[0]->alamatlengkap;
            }
            try {
                $rekanan = [];
//                $rekanan = \DB::table('rekanan_m as al')
//                    ->select('al.namarekanan')
//                    ->where('al.id', $pelayanan[0]->rekananid)
//                    ->first();
                $penjamin = $pelayanan->namarekanan;
            } catch (\Exception $e) {
                $penjamin = '-';
            }
            try {
                $kelompokPasien = $pelayanan->kelompokpasien;
            } catch (\Exception $e) {
                $kelompokPasien = '-';
            }

            try {
                $agama = $pelayanan->agama;
            } catch (\Exception $e) {
                $agama = '-';
            }
            try {
                $umur = $this->hitungUmur($pelayanan->tgllahir);
            } catch (\Exception $e) {
                $umur = '-';
            }

            $keluarga = '-';
            try {
                if ($pelayanan->objectstatusperkawinanfk == '1') {
                    $keluarga = $pelayanan->namaayah;
                } else {
                    $keluarga = $pelayanan->namasuamiistri;
                }
            } catch (\Exception $e) {
                $keluarga = '-';
            }

            $result = array(
                'pasienID' => $pelayanan->pasid,
                'noCm' => $pelayanan->nocm,
                'jenisKelamin' => $pelayanan->jeniskelamin,
                'noRegistrasi' => $pelayanan->noregistrasi,
                'namaPasien' => $pelayanan->namapasien,
                'lastRuangan' => $pelayanan->ruanganlast,
                'tglMasuk' => $pelayanan->tglregistrasi,
                'tglPulang' => $pelayanan->tglpulang,
                'jenisPasien' => $kelompokPasien,
                'kelasRawat' => $pelayanan->namakelas2,
                'kelasId' => $pelayanan->klsid2,
                'noAsuransi' => '-',
                'kelasPenjamin' => '-',
                'namaPenjamin' => $penjamin,
                'penjamin' => $penjamin,
                'deposit' => $this->getDepositPasien($pelayanan->noregistrasi),
                'totalKlaim' => 0,
                'jumlahBayar' => 0,
                'jumlahPiutang' => 0,
                'billing' => $totalBilling,
                'norec_pd' => $pelayanan->norec_pd,
                'strukfk' => $pelayanan->nostruk,
                'telepon' => $pelayanan->notelepon,
                'alamat' => $alamatTea,
                'tgllahir' => $pelayanan->tgllahir,
                'agama' => $agama,
                'umur' => $umur,
                'keluarga' => $keluarga,
                'details' => [],
                'lalal' => $pelayanan->nostruklastfk,
                '$isRawatInap' => $isRawatInap,
                'diverif' => $diverif,
                'dibayar' => $dibayar,
	            'statuspasien' => $pelayanan->ruanganlastid,
                'ruanganlastid' => $pelayanan->ruanganlastid,
            );
//            $result = array(
//                'pasienID' => '',
//                'noCm' => '',
//                'jenisKelamin' => '',
//                'noRegistrasi' => $noRegister,
//                'namaPasien' => '',
//                'lastRuangan' => '',
//                'tglMasuk' => '',
//                'tglPulang' => '',
//                'jenisPasien' => '',
//                'kelasRawat' => '',
//                'kelasId' => '',
//                'noAsuransi' => '-',
//                'kelasPenjamin' => '-',
//                'namaPenjamin' => '',
//                'penjamin' => '',
//                'deposit' => '',
//                'totalKlaim' => 0,
//                'jumlahBayar' => 0,
//                'jumlahPiutang' => 0,
//                'billing' => '',
//                'norec_pd' =>'',
//                'telepon' => '',
//                'alamat' => '',
//                'tgllahir' => '',
//                'agama' => '',
//                'umur' => '',
//                'keluarga' => '',
//                'details' => '',
//                'lalal' => '',
//                '$isRawatInap' => '',
//                'diverif' => '',
//                'dibayar' => '',
//            );
        }
        return $this->respond($result);
    }

    public function detailTagihanRevKudunaLeuwihGancang($noRegister,Request $request)
    {
//        $delete = TempBilling::where('noregistrasi', $noRegister)->delete();
//        $dataRuangan = \DB::table('pasiendaftar_t as pd')
//            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
//            ->select( 'ru.namaruangan as namaruangan')
//            ->where('pd.noregistrasi', $noRegister)
//            ->first();
        $pelayanan=[];
        $pelayananHead = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftjoin('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
            ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
            ->select('kl.id as klid', 'kl.namakelas',
                'ru.id as ruid', 'ru.namaruangan','apd.norec as norec_apd',
                'pg.id as pgid', 'pg.namalengkap', 'pd.nocmfk', 'ru2.objectdepartemenfk as deptid',
                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
                'kp.id as kpid', 'kp.kelompokpasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
                'pas.id as pasid', 'pas.nocm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jk', 'pd.noregistrasi', 'pas.namapasien',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang', 'pas.notelepon', 'kl.id as klsid', 'kl.namakelas',
                'pd.objectrekananfk as rekananid', 'ru2.namaruangan as ruanganlast', 'kls2.id as klsid2', 'kls2.namakelas as namakelas2',
                'rk.namarekanan',
                'kamar.namakamar',
                'pd.kdprofile','pd.statuspasien'
            )
            ->where('pd.noregistrasi', $noRegister);
        $pelayananHead = $pelayananHead->first();

        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('pegawai_m as pgdokter', 'pgdokter.id', '=', 'pp.pelayananpegawaifk')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->leftjoin('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->leftjoin('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftjoin('kelompokprodukbpjs_m as kpBpjs', 'kpBpjs.id', '=', 'pr.objectkelompokprodukbpjsfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->leftjoin('strukresep_t as sr', 'sr.norec', '=', 'pp.strukresepfk')
            ->leftjoin('ruangan_m as rusr', 'rusr.id', '=', 'sr.ruanganfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftjoin('pegawai_m as pgsr', 'pgsr.id', '=', 'sr.penulisresepfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftjoin('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftjoin('pegawai_m as pgsbm', 'pgsbm.id', '=', 'sbm.objectpegawaipenerimafk')
            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah',
                'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk',
                'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa','ru.id as ruid', 'ru.namaruangan',
                'sr.noresep',  'rusr.namaruangan as ruanganfarmasi', 'pgsr.namalengkap as penulisresep','pgdokter.namalengkap as namadokter_pp',
                'kpBpjs.kelompokprodukbpjs as kelompokprodukbpjs', 'kls.id as klsid', 'kls.namakelas',
                'pp.jasa', 'sp.totalharusdibayar', 'sp.totalprekanan', 'sppj.totalppenjamin',
                'sp.totalbiayatambahan', 'pgsbm.namalengkap as namalengkapsbm','pp.aturanpakai','pp.iscito','pp.isparamedis'
            )
            ->where('pd.noregistrasi', $noRegister)
            ->orderBy('pp.tglpelayanan', 'pp.rke');


        if ($request['jenisdata'] == 'resep'){
            $pelayanan = $pelayanan->whereNotNull('pp.aturanpakai');
        }
        if ($request['jenisdata'] == 'layanan'){
            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
            };
            $pelayanan = $pelayanan->whereNull('pp.aturanpakai');
        }
        $pelayanan = $pelayanan->get();

        $pelayananpetugas = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pelayananpasienpetugas_t as ptu', 'ptu.nomasukfk', '=', 'apd.norec')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ptu.objectpegawaifk')
            ->select('ptu.pelayananpasien', 'pg.namalengkap')
            ->where('ptu.objectjenispetugaspefk', 4)
            ->where('pd.noregistrasi', $noRegister)
            ->get();


        $totototol=0;

        if (count($pelayanan) > 0) {
            $alamat = [];
            $alamat = \DB::table('alamat_m as al')
                ->select('al.alamatlengkap')
                ->where('al.nocmfk', $pelayananHead->nocmfk)
                ->first();

            if (empty($alamat)) {
                $alamatTea = '0';
            } else {
                $alamatTea = $alamat->alamatlengkap;
            }

            $totalBilling = 0;
            $norecAPD = '';
            $norecSP = '';
            $details = array();
            $dibayar=0;
            $diverif=0;
            foreach ($pelayanan as $value) {
                $isRawatInap = false;
                if ($value->produkfk == 402611) {
                    continue;
                }
                $komponen = [];

                $dokter = [];
//                $NamaDokter = $value->namadokter_pp;//'-';
                $NamaDokter = '-';
                foreach ($pelayananpetugas as $hahaha) {
                    if ($hahaha->pelayananpasien == $value->norec) {
                        $NamaDokter = $hahaha->namalengkap;
                    }
                }
                $jasa = 0;
                if (isset($value->jasa) && $value->jasa != "" && $value->jasa != "undefined") {
                    $jasa = $value->jasa;
                }
                $kmpn = [];
                $harga = (float)$value->hargajual;
                $diskon = (float)$value->hargadiscount;
                $detail = array(
                    'norec' => $value->norec,
                    'tglPelayanan' => $value->tglpelayanan,
                    'namaPelayanan' => $value->namaproduk,
                    'dokter' => $NamaDokter,
                    'jumlah' => $value->jumlah,
                    'kelasTindakan' => $value->namakelas,
                    'ruanganTindakan' => $value->namaruangan,
                    'harga' => $harga,
                    'diskon' => $diskon,
                    'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                    'jppid' => '',
                    'jenispetugaspe' => '',
                    'strukfk' => $value->nostruk . ' / ' . $value->nosbm,
                    'sbmfk' => $value->nosbm,
                    'pgid' => '',
                    'ruid' => $pelayananHead->ruid,
                    'prid' => $value->prid,
                    'klid' => $pelayananHead->klid,
                    'norec_apd' => $pelayananHead->norec_apd,
                    'norec_pd' => $pelayananHead->norec_pd,
                    'norec_sp' => $value->norec_sp,
                    'komponen' => $kmpn,
                    'jasa' => $jasa,
                    'aturanpakai' => $value->aturanpakai,
                    'iscito' => $value->iscito,
                    'isparamedis' => $value->isparamedis
                );

                if (is_null($value->nosbm) == true) {
                    $dibayar = $dibayar + (($harga - $diskon) * $value->jumlah) + $jasa;
                }
                if (is_null($value->nostruk) == true) {
                    $diverif = $diverif + (($harga - $diskon) * $value->jumlah) + $jasa;
                }
                $norecAPD = $pelayananHead->norec_apd;
                $norecSP = $value->norec_sp;
                $totalBilling = $totalBilling + (($harga - $diskon) * $value->jumlah) + $value->jasa;
                $total =  (($harga - $diskon) * $value->jumlah) + $value->jasa;
                $details[] = $detail;

            }
        }

        $dataTotalBill = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                where  pd.noregistrasi=:noregistrasi and pp.produkfk not in (402611) ;
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $totalBilling=0;
        $totalBilling = $dataTotalBill[0]->total;

        $dataTotaldibayar = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                where  pd.noregistrasi=:noregistrasi and sp.nosbmlastfk is not null and pp.produkfk not in (402611);
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $dibayar=0;
        $dibayar = $dataTotaldibayar[0]->total;

        $dataTotalverif = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                where  pd.noregistrasi=:noregistrasi and pp.strukfk is not null and pp.produkfk not in (402611)
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $diverif=0;
        $diverif = $dataTotalverif[0]->total;

        $biayaAdministrasi =0 ;
            $komponen = [];
//            if ($pelayananHead->nostruklastfk == null && $isRawatInap) {
//                $biayaAdministrasi = number_format($totalBilling * $this->getPercentageBiayaAdmin(), 0, '', '');
//                $biayaMaterai = $this->getBiayaMaterai($totalBilling);
//            }
            if (count($pelayanan) == 0) {
                empty($details);
            }


            try {
                $rekanan = [];
                $rekanan = \DB::table('rekanan_m as al')
                    ->select('al.namarekanan')
                    ->where('al.id', $pelayananHead->rekananid)
                    ->first();
                $penjamin = $rekanan->namarekanan;
            } catch (\Exception $e) {
                $penjamin = '-';
            }
            try {
                $kelompokPasien = $pelayananHead->kelompokpasien;
            } catch (\Exception $e) {
                $kelompokPasien = '-';
            }

            try {
                $agama = $pelayananHead->agama;
            } catch (\Exception $e) {
                $agama = '-';
            }
            try {
                $umur = $this->hitungUmur($pelayananHead->tgllahir);
            } catch (\Exception $e) {
                $umur = '-';
            }

            $keluarga = '-';
            try {
                if ($pelayananHead->objectstatusperkawinanfk == '1') {
                    $keluarga = $pelayananHead->namaayah;
                } else {
                    $keluarga = $pelayananHead->namasuamiistri;
                }
            } catch (\Exception $e) {
                $keluarga = '-';
            }

            $result = array(
                'pasienID' => $pelayananHead->pasid,
                'noCm' => $pelayananHead->nocm,
                'jenisKelamin' => $pelayananHead->jeniskelamin,
                'noRegistrasi' => $pelayananHead->noregistrasi,
                'namaPasien' => $pelayananHead->namapasien,
                'lastRuangan' => $pelayananHead->ruanganlast,
                'tglMasuk' => $pelayananHead->tglregistrasi,
                'tglPulang' => $pelayananHead->tglpulang,
                'jenisPasien' => $kelompokPasien,
                'kelasRawat' => $pelayananHead->namakelas2,
                'kelasId' => $pelayananHead->klsid2,
                'noAsuransi' => '-',
                'kelasPenjamin' => '-',
                'namaPenjamin' => $penjamin,
                'penjamin' => $penjamin,
                'deposit' => $this->getDepositPasien($pelayananHead->noregistrasi),
                'totalKlaim' => 0,
                'jumlahBayar' => 0,
                'jumlahPiutang' => 0,
                'billing' => $totalBilling,
                'norec_pd' => $pelayananHead->norec_pd,
                'strukfk' => $value->nostruk,
                'telepon' => $pelayananHead->notelepon,
                'alamat' => $alamatTea,
                'tgllahir' => $pelayananHead->tgllahir,
                'agama' => $agama,
                'umur' => $umur,
                'keluarga' => $keluarga,
                'details' => $details,
                'lalal' => $pelayananHead->nostruklastfk,
                '$isRawatInap' => $isRawatInap,
                'diverif' => $diverif,
                'dibayar' => $dibayar,
                'statuspasien' => $pelayananHead->statuspasien,
            );
        return $this->respond($result);
    }

    public function detailTagihanBill($noRegister,Request $request)
    {
//        ini_set('max_execution_time', 3000); //6 minutes
        $delete = TempBilling::where('noregistrasi', $noRegister)->delete();
        $pelayananHead = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
//            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
//            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftjoin('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
            ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
//            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
            ->select(
                'apd.norec as norec_apd',
                 'pd.nocmfk', 'ru2.objectdepartemenfk as deptid',
                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
                'kp.id as kpid', 'kp.kelompokpasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
                'pas.id as pasid', 'pas.nocm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jk', 'pd.noregistrasi', 'pas.namapasien',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang', 'pas.notelepon',
                'pd.objectrekananfk as rekananid', 'ru2.namaruangan as ruanganlast', 'kls2.id as klsid2', 'kls2.namakelas as namakelas2',
                'rk.namarekanan',
                'kamar.namakamar',
                'pd.kdprofile','pd.statuspasien'
            )
            ->where('pd.noregistrasi', $noRegister);
        $pelayananHead = $pelayananHead->first();

        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->leftjoin('pegawai_m as pgdokter', 'pgdokter.id', '=', 'pp.pelayananpegawaifk')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->leftjoin('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->leftjoin('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftjoin('kelompokprodukbpjs_m as kpBpjs', 'kpBpjs.id', '=', 'pr.objectkelompokprodukbpjsfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->leftjoin('strukresep_t as sr', 'sr.norec', '=', 'pp.strukresepfk')
            ->leftjoin('ruangan_m as rusr', 'rusr.id', '=', 'sr.ruanganfk')
            ->leftjoin('pegawai_m as pgsr', 'pgsr.id', '=', 'sr.penulisresepfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftjoin('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftjoin('pegawai_m as pgsbm', 'pgsbm.id', '=', 'sbm.objectpegawaipenerimafk')
            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah',
                'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk',
                'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa',
                'sr.noresep',  'rusr.namaruangan as ruanganfarmasi', 'pgsr.namalengkap as penulisresep','pgdokter.namalengkap as namadokter_pp',
                'kpBpjs.kelompokprodukbpjs as kelompokprodukbpjs', 'kls.id as klsid', 'kls.namakelas','kpBpjs.id as kpid',
                'pp.jasa', 'sp.totalharusdibayar', 'sp.totalprekanan', 'sppj.totalppenjamin',
                'sp.totalbiayatambahan', 'pgsbm.namalengkap as namalengkapsbm','pp.aturanpakai','pp.iscito','pp.isparamedis','jp.jenisproduk',
                'ru.id as ruid', 'ru.namaruangan'
            )
            ->where('pd.noregistrasi', $noRegister)
            ->orderBy('pp.tglpelayanan', 'pp.rke');


        if ($request['jenisdata'] == 'resep'){
            $pelayanan = $pelayanan->whereNotNull('pp.aturanpakai');
        }
        if ($request['jenisdata'] == 'layanan'){
            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
            };
            $pelayanan = $pelayanan->whereNull('pp.aturanpakai');
        }
        $pelayanan = $pelayanan->get();

        $pelayananpetugas = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pelayananpasienpetugas_t as ptu', 'ptu.nomasukfk', '=', 'apd.norec')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ptu.objectpegawaifk')
            ->select('ptu.pelayananpasien', 'pg.namalengkap')
            ->where('ptu.objectjenispetugaspefk', 4)
            ->where('pd.noregistrasi', $noRegister)
            ->get();

        if ($request['jenisdata'] == 'bill'){
            $pelayanankelasdijamin = DB::select(DB::raw("
                select pp.norec, pd.noregistrasi,pd.objectkelasfk,kls_pd.namakelas,asp.objectkelasdijaminfk,
                kls_dijamin.namakelas as namakelas_dijamin,pp.hargajual,pp.jumlah,
                case when hnpk.harganetto2 is null then pp.hargajual else hnpk.harganetto2 end as harga_kelasdijamin
                from pasiendaftar_t as pd
                LEFT JOIN pemakaianasuransi_t as pas on pas.noregistrasifk=pd.norec
                left join asuransipasien_m as asp on asp.id=pas.objectasuransipasienfk
                left join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                left join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                left join harganettoprodukbykelas_m as hnpk on hnpk.objectprodukfk=pp.produkfk and hnpk.objectkelasfk=asp.objectkelasdijaminfk
                left join kelas_m as kls_pd on kls_pd.id=pd.objectkelasfk
                left join kelas_m as kls_dijamin on kls_dijamin.id=asp.objectkelasdijaminfk
                where noregistrasi=:noregistrasi;
            "),
                array(
                    'noregistrasi' => $noRegister,
                )
            );
        }
        $totototol=0;

        $dataTotaldibayar = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                where  pd.noregistrasi=:noregistrasi and sp.nosbmlastfk is not null and pp.produkfk not in (402611);
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $dibayar = 0;
        $dibayar = $dataTotaldibayar[0]->total;
        if (count($pelayanan) > 0) {
            $alamat = [];
            $alamat = \DB::table('alamat_m as al')
                ->select('al.alamatlengkap')
                ->where('al.nocmfk', $pelayananHead->nocmfk)
                ->first();

            if (empty($alamat)) {
                $alamatTea = '0';
            } else {
                $alamatTea = $alamat->alamatlengkap;
            }

            $totalBilling = 0;
            $norecAPD = '';
            $norecSP = '';
            $details = array();

            $diverif=0;
            foreach ($pelayanan as $value) {
//                $isRawatInap = false;
//                if ($value->deptid == 16) {
//                    $isRawatInap = true;
//                }
//                if ($value->produkfk == $this->getProdukIdDeposit()) {
//                    continue;
//                }
//                $komponen = [];
//                $dokter = [];
                $NamaDokter = '-';
                foreach ($pelayananpetugas as $hahaha) {
                    if ($hahaha->pelayananpasien == $value->norec) {
                        $NamaDokter = $hahaha->namalengkap;
                    }
                }
//
//                $jasa = 0;
//                if (isset($value->jasa) && $value->jasa != "" && $value->jasa != "undefined") {
//                    $jasa = $value->jasa;
//                }
//                $kmpn = [];
//
//                $harga = (float)$value->hargajual;
//                $diskon = (float)$value->hargadiscount;
//                $detail = array(
//                    'norec' => $value->norec,
//                    'tglPelayanan' => $value->tglpelayanan,
//                    'namaPelayanan' => $value->namaproduk,
//                    'dokter' => $NamaDokter,
//                    'jumlah' => $value->jumlah,
//                    'kelasTindakan' => @$value->namakelas,
//                    'ruanganTindakan' => @$value->namaruangan,
//                    'harga' => $harga,
//                    'diskon' => $diskon,
//                    'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
//                    'jppid' => '',
//                    'jenispetugaspe' => '',
//                    'strukfk' => $value->nostruk . ' / ' . $value->nosbm,
//                    'sbmfk' => $value->nosbm,
//                    'pgid' => '',
//                    'ruid' => $value->ruid,
//                    'prid' => $value->prid,
//                    'klid' => $value->klid,
//                    'norec_apd' => $value->norec_apd,
//                    'norec_pd' => $value->norec_pd,
//                    'norec_sp' => $value->norec_sp,
//                    'komponen' => $kmpn,
//                    'jasa' => $jasa,
//                    'aturanpakai' => $value->aturanpakai,
//                    'iscito' => $value->iscito,
//                    'isparamedis' => $value->isparamedis
//                );
//
//                if (is_null($value->nosbm) == true) {
//                    $dibayar = $dibayar + (($harga - $diskon) * $value->jumlah) + $jasa;
//                }
//                if (is_null($value->nostruk) == true) {
//                    $diverif = $diverif + (($harga - $diskon) * $value->jumlah) + $jasa;
//                }
//                $norecAPD = $value->norec_apd;
//                $norecSP = $value->norec_sp;
//                $totalBilling = $totalBilling + (($harga - $diskon) * $value->jumlah) + $value->jasa;
//                $total =  (($harga - $diskon) * $value->jumlah) + $value->jasa;
//                $details[] = $detail;

//                $totototol = $totototol + $total;
                if ($request['jenisdata'] == 'bill') {
                    $kelas_dijamin = 0;
                    $harga_dijamin = 0;
                    $total_dijamin = 0;
                    foreach ($pelayanankelasdijamin as $kupret) {
                        if ($value->norec == $kupret->norec) {
                            $kelas_dijamin = $kupret->namakelas_dijamin;
                            $harga_dijamin = $kupret->harga_kelasdijamin;
                            $total_dijamin = ((float)$kupret->harga_kelasdijamin * (float)$value->jumlah) + $value->jasa;
                        }
                    }
//                    try {
                    $namakelas = $value->namakelas;
                    $ruanganTindakan = $value->namaruangan;
                    $jenisprodukmaster = $value->jenisproduk;
                    if ($value->kpid == 2) {
                        $jenisproduk = $value->kelompokprodukbpjs;
                    } else {
                        $jenisproduk = $value->jenisproduk;
                    }

                    if ($value->noresep != null) {
                        $namakelas = '';
                        $ruanganTindakan = $value->ruanganfarmasi . '     Resep No: ' . $value->noresep;
//                    $jenisproduk = 'Resep';
                    }
//                    if ($value->namaproduk == 'Biaya Administrasi') {
//                        $ruanganTindakan = $dataRuangan->namaruangan;
//                    }
//                    if ($value->namaproduk == 'Biaya Materai') {
//                        $ruanganTindakan = $dataRuangan->namaruangan;
//                    }
                    //Biaya Administrasi
                    $namarekanan = '-';
                    if ($pelayananHead->namarekanan != null) {
                        $namarekanan = $pelayananHead->namarekanan;
                    }
                    $namaproduk = $value->namaproduk;
                    if ($value->rke != null) {
                        $namaproduk = 'R/' . $value->rke . ' ' . $value->namaproduk;
                    }

                    $hargadiscount = 0;
                    if ($value->hargadiscount != null) {
                        $hargadiscount = $value->hargadiscount;
                    }


                    $hargakurangdiskon = (float)$value->hargajual - $hargadiscount;
                    $hargakalijml = $hargakurangdiskon * (float)$value->jumlah;
                    $jasa = 0;
                    if ($value->jasa != null) {
                        $jasa = $value->jasa;
                    }
                    $total = ((float)$value->jumlah * ((float)$value->hargajual - $hargadiscount)) + $jasa;


                    $namakamar = '-';
                    if ($pelayananHead->namakamar != null) {
                        $namakamar = $pelayananHead->namakamar;
                    }
                    $totalprekanan = 0;
                    if ($value->totalprekanan != null) {
                        $totalprekanan = $value->totalprekanan;
                    }
                    $totalppenjamin = 0;
                    if ($value->totalppenjamin != null) {
                        $totalppenjamin = $value->totalppenjamin;
                    }
                    $totalbiayatambahan = 0;
                    if ($value->totalbiayatambahan != null) {
                        $totalbiayatambahan = $value->totalbiayatambahan;
                    }
                    $totalharusdibayar = $value->totalharusdibayar;
                    if ($value->totalharusdibayar < 0) {
                        $totalharusdibayar = 0;
                    }

                    $temp = new TempBilling();#
                    $tempNorec = $temp->generateNewId();#
                    $temp->norec = $tempNorec;#
                    $temp->norec_pp = $value->norec;#
                    $temp->tglstruk = $value->tglstruk;#
                    $temp->nobilling = $noRegister;#
                    $temp->nokwitansi = $value->nosbm;#
                    $temp->noregistrasi = $noRegister;#
                    $temp->nocm = $pelayananHead->nocm;#
                    $temp->namapasienjk = $pelayananHead->namapasien . ' ( ' . $pelayananHead->jk . ' )';#
                    $temp->unit = $pelayananHead->ruanganlast;#
                    $temp->objectdepartemenfk = $pelayananHead->deptid;#
                    $temp->namakelas = $namakelas;#
                    $temp->dokterpj = '';//$value->dokterpj;#
                    $temp->tglregistrasi = $pelayananHead->tglregistrasi;#
                    $temp->tglpulang = $pelayananHead->tglpulang;#
                    $temp->namarekanan = $namarekanan;#
                    $temp->tglpelayanan = $value->tglpelayanan;#
                    $temp->ruangantindakan = $ruanganTindakan;#
                    $temp->namaproduk = $namaproduk;#
                    $temp->penulisresep = $value->penulisresep;#
                    $temp->jenisproduk = $jenisproduk;#
                    $temp->dokter = $value->namadokter_pp;#
                    $temp->jumlah = $value->jumlah;#
                    $temp->hargajual = $value->hargajual;#
                    $temp->diskon = $hargadiscount;#
                    $temp->total = $total;#
                    $temp->namakamar = $namakamar;#
                    $temp->tipepasien = $pelayananHead->kelompokpasien;#
                    $temp->totalharusdibayar = $totalharusdibayar;#
                    $temp->totalprekanan = $totalprekanan;#
                    $temp->totalppenjamin = $totalppenjamin;#
                    $temp->totalbiayatambahan = $totalbiayatambahan;#
                    $temp->user = $value->namalengkapsbm;#
                    $temp->namakelaspd = $pelayananHead->namakelas2;#
                    $temp->nama_kelasasal = $kelas_dijamin;#
                    $temp->hargajual_kelasasal = $harga_dijamin;#
                    $temp->total_kelasasal = $total_dijamin;#
                    $temp->jenisprodukmaster = $value->jenisproduk;
                    $temp->totaldibayar = $dibayar;
                    $temp->save();

//                        $transStatus = 'true';
//                    } catch (\Exception $e) {
//                        $transStatus = 'false';
//                        $transMessage = "Simpan TEMP";
//                    }
                }
            }
        }

        return $this->respond('as@epic');
    }
    public function cetakDetailTagihan($noRegister)
    {
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->first();
        if (!$pasienDaftar) {
            $this->setStatusCode(400);
            return $this->respond([], 'No registrasi Tidak Terdaftar');
        }
        try {
            //$pelayanan = $this->getPelayananPasienByNoRegistrasi($noRegister);
            $pelayanan = \DB::table('pasiendaftar_t as pd')
                ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
                ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
                ->join('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
                ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                ->join('pelayananpasienpetugas_t as ptu', 'ptu.pelayananpasien', '=', 'pp.norec')
                ->join('pegawai_m as pg', 'pg.id', '=', 'ptu.objectpegawaifk')
                ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
                ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
                ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                ->select('pp.norec', 'pp.tglpelayanan', 'pr.namaproduk', 'pp.jumlah', 'kl.namakelas',
                    'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pg.namalengkap', 'pr.id as produkId',
                    'jp.jenisproduk', 'sp.nostruk')
                ->whereNotNull('pd.tglpulang')
                //->whereNull('pp.strukfk')
                ->where('pd.noregistrasi', $noRegister)
                ->get();;
        } catch (\Exception $e) {
            $pelayanan = array();
        }

        $isRawatInap = $this->isPasienRawatInap($pasienDaftar);

        if (count($pelayanan) == 0) {

        }
        $totalBilling = 0;
        $details = array();
        foreach ($pelayanan as $value) {
            if ($value->produkfk == $this->getProdukIdDeposit()) {
                continue;
            }
            //$pelayananpetugas = PelayananPasienPetugas::where('pelayananpasien', $value->norec)->first();
            $namaDokter = $value->namalengkap;//  ($pelayananpetugas && $pelayananpetugas->pegawai) ? $pelayananpetugas->pegawai->namalengkap : "-";
            $harga = ($value->hargajual == null) ? 0 : $value->hargajual;

            $tglPelayanan = Carbon::createFromFormat('Y-m-d', substr($value->tglpelayanan, 0, 9));
//            try {
//                $datatatata = AntrianPasienDiperiksa::where('norec','ff8081815d3a179b015d3a3667590012')->get();
//                $ruangan = $value->antrian_pasien_diperiksa;//->ruangan->namaruangan;
//            } catch (\Exception $e) {
//                $ruangan = '-';
//            }
            $ruangan = $value->namaruangan;
//            try {
//                $jenisProduk = $value->produk->detail_jenis_produk->jenis_produk->jenisproduk;
//            } catch (\Exception $e) {
//                $jenisProduk = '-';
//            }


            $detail = array(
                'tglPelayanan' => $this->getDateReport($tglPelayanan),
                'namaPelayanan' => $value->namaproduk,// $value->produk->namaproduk,
                'dokter' => $namaDokter,
                'jumlah' => $this->getQtyFormatString($value->jumlah),
                'harga' => $this->getMoneyFormatString($harga),
                'total' => $this->getMoneyFormatString($harga * $value->jumlah),
                'noKode' => $value->produkId,// $value->produk->id,
                'kelasrawat' => $value->namakelas,
                'ruangan' => $ruangan,
                'jenisProduk' => $value->jenisproduk,// $jenisProduk,
                'strukfk' => $value->nostruk,
            );
            $totalBilling = $totalBilling + ($value->hargajual * $value->jumlah);
            $details[] = $detail;
        }
        if ($pasienDaftar->nostruklastfk == null && $isRawatInap) {
            $biayaAdministrasi = (int)($totalBilling * $this->getPercentageBiayaAdmin());

            $detail = array(
                'tglPelayanan' => Carbon::now()->toDateString(),
                'namaPelayanan' => $this->getProdukBiayaAdministrasi()->namaproduk,
                'dokter' => '-',
                'jumlah' => 1,
                'harga' => $this->getMoneyFormatString($biayaAdministrasi),
                'total' => $this->getMoneyFormatString($biayaAdministrasi),
                'strukfk' => '-',
            );
            $totalBilling += $biayaAdministrasi;
            if ($biayaAdministrasi > 0) {
                $details[] = $detail;
            }

            $biayaMaterai = $this->getBiayaMaterai($totalBilling);
            $detail = array(
                'tglPelayanan' => Carbon::now()->toDateString(),
                'namaPelayanan' => $this->getProdukBiayaMaterai()->namaproduk,
                'dokter' => '-',
                'jumlah' => 1,
                'harga' => $this->getMoneyFormatString($biayaMaterai),
                'total' => $this->getMoneyFormatString($biayaMaterai),
                'strukfk' => '-',
            );
            $totalBilling += $biayaMaterai;
            if ($biayaMaterai > 0) {
                $details[] = $detail;
            }

        }

        //$penjamin=$this->getPenjamin($pasienDaftar)->namarekanan;
        try {
            $tipe = $pasienDaftar->kelompok_pasien->kelompokpasien;
        } catch (\Exception $e) {
            $tipe = '-';
        }
        try {
            $penjamin = $pasienDaftar->rekanan->namarekanan;
        } catch (\Exception $e) {
            $penjamin = 'A';
        }
        try {
            $unit = $pasienDaftar->last_ruangan->namaruangan;
        } catch (\Exception $e) {
            $unit = '-';
        }
        try {
            $data123 = \DB::table('antrianpasiendiperiksa_t as ap')
                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'ap.noregistrasifk')
                ->join('kamar_m as k', 'k.id', '=', 'ap.objectkamarfk')
                ->select('k.id as kId', 'k.namakamar')
                ->whereNotNull('ap.objectkamarfk')
                ->where('pd.noregistrasi', $noRegister)
                ->first();
            $kamar = $data123->namakamar;
        } catch (\Exception $e) {
            $kamar = '-';
        }

        try {
            $bed = '-';
        } catch (\Exception $e) {
            $bed = '-';
        }

        try {
            $dokter = $pasienDaftar->dokter->namalengkap;
        } catch (\Exception $e) {
            $dokter = '-';
        }
        try {
            $totalKlaim = $pasienDaftar->dokter->namalengkap;
        } catch (\Exception $e) {
            $totalKlaim = 0;
        }


        $tglMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $this->subDateTime($pasienDaftar->tglregistrasi));

        //return $this->respond(array(isset($pasienDaftar->tglpulang)?"--":"+++"));

//        isset($pasienDaftar->tglpulang)?
//            $tglPulang = Carbon::createFromFormat('Y-m-d H:i:s',$this->subDateTime($pasienDaftar->tglpulang)):
//            $tglPulang =Carbon::createFromFormat('Y-m-d H:i:s',$this->subDateTime($this->getDateTime()));
        //return $this->respond(array($tglPulang));
        if (isset($pasienDaftar->tglpulang)) {
            $tglPulang = Carbon::createFromFormat('Y-m-d H:i:s', $this->subDateTime($pasienDaftar->tglpulang));
        } else {
            $tglPulang = Carbon::createFromFormat('Y-m-d H:i:s', $this->subDateTime($this->getDateTime()));
        }


        //$tglPulang = Carbon::createFromFormat('Y-m-d H:i:s',$this->subDateTime($pasienDaftar->tglpulang));

        $deposit = $this->getDepositPasien($pasienDaftar->noregistrasi);
        $diskonJasaMedis = 0;
        $diskonUmum = 0;
        $sisaDeposit = 0;
        $harusDibayar = $totalBilling - $deposit - $diskonJasaMedis - $diskonUmum - $sisaDeposit;

        //        disini nanti untuk manage cetaknya
        $historiCetak = HistoriCetakDokumen::where('nobuktitransaksi', $pasienDaftar->norec)->where('kdobjeckmodulaplikasi',
            'CETAK01')->get();

        $cetakKe = count($historiCetak);
        $nomorCetak = $cetakKe + 1;
        $tanggalcetak = Carbon::now();
        $historyCetak = new HistoriCetakDokumen();
        $dataCetak = array(
            'norec' => $historyCetak->generateNewId(),
            'kdprofile' => 1,
            'nohistori' => $this->generateCode($historyCetak, 'nohistori', 14, 'CETAK-' . $this->getDateTime()->format('ym'),$idProfile),
            'kdobjeckmodulaplikasi' => 'CETAK01',
            'nobuktitransaksi' => $pasienDaftar->norec,
            'cetakke' => $nomorCetak,
            'tglcetak' => $tanggalcetak,
            'keteranganlainnya' => "cetak detal tagihan No. Registrasi: " . $noRegister,
            'statusenabled' => 1,
        );

        $historyCetak->create($dataCetak);

        $result = array(
//            'data' => $pelayanan,
            'pasienID' => $pasienDaftar->pasien->id,
            'noCm' => $pasienDaftar->pasien->nocm,
            'jenisKelamin' => $pasienDaftar->pasien->jenis_kelamin->namaexternal,
            'noRegistrasi' => $pasienDaftar->noregistrasi,
            'namaPasien' => $pasienDaftar->pasien->namapasien,
            'lastRuangan' => $pasienDaftar->last_ruangan->namaruangan,
            'unit' => $unit,//$pasienDaftar->last_ruangan->departemen->namadepartemen,
            'kamar' => $kamar,
            'tglMasuk' => $this->getDateReport($tglMasuk),
            'tglPulang' => $this->getDateReport($tglPulang),
            'jenisPasien' => $pasienDaftar->kelompok_pasien->kelompokpasien,
            'penjamin' => $penjamin,
            'tipe' => $tipe,
            'kelasRawat' => $pasienDaftar->kelas->namakelas,
            'dokter' => $dokter,
            'noAsuransi' => '-', //ambil dari asuransi pasien -m tapi datanya blum ada brooo..
            'kelasPenjamin' => '-', //ini blum ada datanya gimana mau liat,, gila yaa ?
            'namaPenjamin' => '-', //ini blum ada datanya gimana mau liat,, gila yaa ?
            'billing' => $this->getMoneyFormatString($totalBilling),
            'deposit' => $this->getMoneyFormatString($deposit), //ngambil dari mana
            'diskonJasaMedis' => $diskonJasaMedis,
            'diskonUmum' => $this->getMoneyFormatString($diskonUmum),
            'sisaDeposit' => $this->getMoneyFormatString($sisaDeposit),
            'totalKlaim' => 0, //ngambil dari mana? dihitunga gak
            'jumlahPiutang' => 0, //ini ngambil dari pembayaran gak ?
            'harusDibayar' => $this->getMoneyFormatString($harusDibayar),
            'terbilangHarusDibayar' => ucwords($this->makeTerbilang($harusDibayar, ' rupiah')),
            'nomorCetak' => $nomorCetak,
            'tglCetak' => $this->getDateTimeReport($tanggalcetak),
            'details' => $details,  //yang udah ada detailnya yang mana yaa ?
        );
        return $this->respond($result);
    }
    public function cetakPasienPulang($noRegister)
    {
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->first();
        $result = array(
            'pasienID' => $pasienDaftar->pasien->id,
            'noCm' => $pasienDaftar->pasien->nocm,
            'jenisKelamin' => $pasienDaftar->pasien->jenis_kelamin->namaexternal,
            'noRegistrasi' => $pasienDaftar->noregistrasi,
            'namaPasien' => $pasienDaftar->pasien->namapasien,
            'lastRuangan' => $pasienDaftar->last_ruangan->namaruangan,
            'tglMasuk' => $pasienDaftar->tglregistrasi,
            'tglPulang' => $pasienDaftar->tglpulang,
            'jenisPasien' => $pasienDaftar->kelompok_pasien->kelompokpasien,
        );
        return $this->respond($result);
    }

    public function batalVerifikasiTagihan(Request $request){
        $transMsg = null;
        $totalBilling = 0;
        $totalDeposit = 0;
        $noRegister = $request['noregistrasi'];
        $NorecSP = $request['norec_sp'];

        DB::beginTransaction();
      try {
        $dataSP = \DB::table('pasiendaftar_t as pd')
            ->leftJoin('strukpelayanan_t as sp', 'sp.noregistrasifk', '=', 'pd.norec')
            ->select('sp.norec','sp.nostruk','pd.norec as norec_pd','pd.noregistrasi')
            ->where('pd.noregistrasi', '=', $noRegister)
            ->where('sp.norec', '=', $NorecSP)
            // ->where('pd.nosbmlastfk', '=', null)
            ->whereIn('sp.statusenabled',[null,true])
            ->get();
        foreach ($dataSP as $item) {
            $nostrukPelayananVerifikasi = $NorecSP; //$item->norec;//$pasienDaftar->nostruklastfk;
            $norec_pd = $item->norec_pd;//$pasienDaftar->nostruklastfk;


            $sbm = StrukBuktiPenerimaan::where('nostrukfk', $nostrukPelayananVerifikasi)->first();
            $sbk = StrukBuktiPengeluaran::where('nostrukfk', $nostrukPelayananVerifikasi)->first();
            if ($sbm || $sbk) {
                $transStatus = false;
                $transMsg = "Tagihan ini sudah ada pembayarannya";
            }


                $data2 = PelayananPasien::where('strukfk', $nostrukPelayananVerifikasi)
                    ->where('produkfk', 10011572)//$this->getProdukBiayaAdministrasi()->id)
//                        ->update([
//                            'noregistrasifk' => null,
//                            'strukfk' => null,
//                            'hargasatuan' => 0,
//                            'harganetto' => 0,
//                            'jumlah' => 0,
//                            'keteranganlain' => 'DELETED',
//                        ]);
                    ->delete();
                $data3 = PelayananPasien::where('strukfk', $nostrukPelayananVerifikasi)
                    ->where('produkfk', $this->getProdukBiayaMaterai()->id)
//                        ->update([
//                            'noregistrasifk' => null,
//                            'strukfk' => null,
//                            'hargasatuan' => 0,
//                            'harganetto' => 0,
//                            'jumlah' => 0,
//                            'keteranganlain' => 'DELETED',
//                        ]);
                    ->delete();
                $data1 = PelayananPasien::where('strukfk', $nostrukPelayananVerifikasi)
                    ->update([
                        'strukfk' => null,
                    ]);
                $data4 = PelayananPasienDetail::where('strukfk', $nostrukPelayananVerifikasi)
                    ->update([
                        'strukfk' => null,
                    ]);
                $data5 = StrukPelayananPenjamin::where('nostrukfk', $nostrukPelayananVerifikasi)
                    ->update([
                        'statusenabled' => 0
                    ]);

                $data6 = PasienDaftar::where('norec', $norec_pd)
                    ->update([
                        'nostruklastfk' => null,
                    ]);

                $data7 = StrukPelayanan::where('norec', $nostrukPelayananVerifikasi)
                    ->update([
//                        'statusenabled' => false,
                        'statusenabled' => 0,
                    ]);
//                $pelayanan = $pasienDaftar->pelayanan_pasien()->select('pelayananpasien_t.*')->where('strukfk', $nostrukPelayananVerifikasi)->get();
//                $pelayananDetail = $pasienDaftar->pelayanan_pasien_detail()->where('strukfk', $nostrukPelayananVerifikasi)->get();
//                $strukPelayanan = StrukPelayanan::find($nostrukPelayananVerifikasi);
//                $SPP = StrukPelayananPenjamin::where('nostrukfk', $strukPelayanan->norec);
//            }


//            if ($transStatus) {
//                foreach ($pelayanan as $pel) {
//                    $noStrukPelayanan = $pel->strukfk;
//                    if ($pel->produkfk == $this->getProdukBiayaAdministrasi()->id || $pel->produkfk == $this->getProdukBiayaMaterai()->id) {
//                        $pel->delete();
//                    } else {
//                        $pel->strukfk = null;
//                        try {
//                            $pel->save();
//                        } catch (\Exception $e) {
//                            $transStatus = false;
//                            $transMsg = "Transaksi Gagal (update pp)";
//                            break;
//                        }
//                    }
//
//                }
//            }

//            if ($transStatus) {
//                foreach ($pelayananDetail as $pelDel) {
//                    $pelDel->strukfk = null;
//                    try {
//                        $pelDel->save();
//                    } catch (\Exception $e) {
//                        $transStatus = false;
//                        $transMsg = "Transaksi Gagal (insert SP)";
//                        break;
//                    }
//                }
//
//            }

//            if ($transStatus) {
//                //$SPP->statusenabled = 0;
//                try {
//                    //$SPP->save();
//                    $lah = StrukPelayananPenjamin::where('nostrukfk', $strukPelayanan->norec)->update(['statusenabled' => 0]);
//                } catch (\Exception $e) {
//                    $transStatus = false;
//                    $transMsg = $strukPelayanan->norec . "update statusenabled SPP";
//
//                }
//
//            }


//            if ($transStatus) {
//                $pasienDaftar->nostruklastfk = null;
//                try {
//                    $pasienDaftar->save();
//                } catch (\Exception $e) {
//                    $transStatus = false;
//                    $transMsg = "Transaksi Gagal (insert SP)";
//                }
//            }


//            if ($transStatus) {
//                $strukPelayanan->statusenabled = 0;
//                try {
//                    //$strukPelayanan->delete();//## ga usah di delete lah sepertinya
//                    $strukPelayanan->save();
//                } catch (\Exception $e) {
//                    $transStatus = false;
//                    $transMsg = "Transaksi Gagal (insert SP)";
//                }
//            }
        }
//
        $transStatus = true;
      } catch (\Exception $e) {
          $transStatus = false;
      }
        if ($transStatus) {
            //jurnal verif sP
            $logAcc = new  LogAcc;
            $logAcc->norec = $logAcc->generateNewId();
            $logAcc->jenistransaksi = 'Batal Verifikasi TataRekening';
            $logAcc->noreff = $noRegister;
            $logAcc->status = 0;
            $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            if ($transStatus) {
                try {
                    $logAcc->save();
                } catch (\Exception $e) {
                    $transStatus = false;
                    $transMsg = "Simpan logAcc batal verif tarek Gagal {SP}";
                }
            }
        }

        if ($transStatus == 'true') {
           DB::commit();
            $transMessage = 'Unverifikasi Tagihan Berhasil';
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $dataSP,
                "as" => 'as@epic',
                "edited" => 'as@epic'
            );
        } else {
            DB::rollBack();
            $transMessage = 'Unverifikasi Tagihan Gagal';
            $result = array(
                "status" => 400,
                "message" => 'Unverifikasi Tagihan Gagal',
                "data" => $dataSP,
                "as" => 'as@epic',
                "edited" => 'as@epic'
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        /* EDITED 24 - Dec - 2019 16:40 */
    }

    public function UpdateHargaPelayananPasien(Request $request){
//        DB::beginTransaction();
        $transStatus = 'false';
        if ($request['norec'] == 0) {
//            $data = new PelayananPasien();
//            $norecHead = $data->generateNewId();
//            $data->norec = $norecHead;
//
//            $data->kdprofile = 0;
//            $data->statusenabled = true;
//            $data->noregistrasifk = $request['noregistrasifk'];
//            $data->tglregistrasi = $request['tglregistrasi'];
//            $data->hargadiscount = $request['hargadiscount'];
//            $data->hargajual = $request['hargajual'];
//            $data->hargasatuan = $request['hargasatuan'];
//            $data->jumlah = $request['jumlah'];
//            $data->kelasfk = $request['kelasfk'];
//            $data->kdkelompoktransaksi = $request['kdkelompoktransaksi'];
//            $data->keteranganlain = $request['keteranganlain'];
//            $data->nilainormal = $request['nilainormal'];
//            $data->piutangpenjamin = $request['piutangpenjamin'];
//            $data->piutangrumahsakit = $request['piutangrumahsakit'];
//            $data->produkfk = $request['produkfk'];
//            $data->status = $request['status'];
//            $data->tglpelayanan = $request['tglpelayanan'];
//            $data->harganetto = $request['harganetto'];
//            $data->stock = $request['stock'];
//            $data->save();
        } else {
//            $dataPelayananPasien = PelayananPasien::where('norec', $request['norec'])->first();
//
//            $dataPelayananPasien->hargajual = $request['harga'];
//            $dataPelayananPasien->hargasatuan = $request['harga'];
//            $dataPelayananPasien->harganetto = $request['harga'];
//            $dataPelayananPasien->jumlah = $request['jumlah'];
//            try{
//                $dataPelayananPasien->save();
//                $transStatus = 'true';
//            }
//            catch(\Exception $e){
//                $transStatus= false;
////                $transMessage = "Transaksi Gagal (insert SP)";
//            }
//            try{
//                PelayananPasien::where('norec', $request['norec'])
//                    ->update([
//                            'hargajual' => (float)$request['harga'],
//                            'hargasatuan' => (float)$request['harga'],
//                            'harganetto' => (float)$request['harga'],
//                            'jumlah' => (integer)$request['jumlah']]
//                    );
            $harga = $request['harga'];
            $jumlah = $request['jumlah'];
            $norec = $request['norec'];
            $result = DB::select(DB::raw("update pelayananpasien_t set hargajual=$harga,hargasatuan=$harga,
                  harganetto=$harga, jumlah=$jumlah where norec='$norec'"),
                array()
            );
//                $transStatus = 'true';
//            }catch(\Exception $e){
//                $transStatus= false;
////                $transMessage = "Transaksi Gagal (insert SP)";
//            }
        }

//        if ($transStatus == 'true') {
//            DB::commit();
//            $transMessage = "Simpan Pelayanan Berhasil";
//            return $this->setStatusCode(201)->respond([], $transMessage);
//        } else {
//            DB::rollBack();
//            $transMessage = "Simpan Pelayanan gagal";
//            return $this->setStatusCode(400)->respond([], $transMessage);
//        }
    }
    public function HapusPelayananPasien(Request $request)
    {
//        DB::beginTransaction();
        $transStatus = 'false';
        if ($request['norec'] != 0) {
            try {
                $data1 = PelayananPasienDetail::where('pelayananpasien', $request['norec'])->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
//                $transMessage = "Transaksi Gagal (insert SP)";
            }
            try {
                $data2 = PelayananPasienPetugas::where('pelayananpasien', $request['norec'])->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
//                $transMessage = "Transaksi Gagal (insert SP)";
            }
            try {
                $data = PelayananPasien::where('norec', $request['norec'])->delete();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = false;
//                $transMessage = "Transaksi Gagal (insert SP)";
            }
        }

//        if ($transStatus == 'true') {
//            DB::commit();
        $transMessage = "hapus Pelayanan Berhasil";
        return $this->setStatusCode(201)->respond([], $transMessage);
//        } else {
//            DB::rollBack();
//            $transMessage = "hapus Pelayanan gagal";
//            return $this->setStatusCode(400)->respond([], $transMessage);
//        }
    }
    public function getTableMaster(Request $request)
    {
        $dataLogin = $request->all();
        $KelompokUser = DB::select(DB::raw("select ku.id,ku.kelompokuser from loginuser_s as lu 
                    INNER JOIN kelompokuser_s as ku on lu.objectkelompokuserfk=ku.id
                    where lu.id =:IDIDID;"),
            array(
                'IDIDID' => $dataLogin['userData']['id'],
            )
        );
        $data1 = JenisPetugasPelaksana::where('statusenabled', true)->get();
        foreach ($data1 as $item1) {
            $data11[] = array(
                'id' => $item1->id,
                'jenispetugaspe' => $item1->jenispetugaspe,
            );
        }
        $data2 = Pegawai::whereIn('objectjenispegawaifk', array(1, 2, 3))->get();
        $dataDokter = Pegawai::whereIn('objectjenispegawaifk', array(1))->get();
        foreach ($data2 as $item2) {
            $data22[] = array(
                'id' => $item2->id,
                'paramedis' => $item2->namalengkap,
            );
        }
        $data3 = Ruangan::whereIn('objectdepartemenfk', array(3, 16, 17, 18, 24, 25, 26, 27, 28))->get();
        foreach ($data3 as $item3) {
            $data33[] = array(
                'id' => $item3->id,
                'ruanganTindakan' => $item3->namaruangan,
            );
        }
        $dataDept = Departemen::whereIn('id', array(3, 16, 17, 18, 24, 25, 26, 27, 28))->get();
        foreach ($dataDept as $item3) {
            $datadept3[] = array(
                'id' => $item3->id,
                'namadepartemen' => $item3->namadepartemen,
            );
        }
        $data3 = Ruangan::whereIn('objectdepartemenfk', array(3, 16, 17, 18, 24, 25, 26, 27, 28))->get();
        foreach ($data3 as $item3) {
            $data33[] = array(
                'id' => $item3->id,
                'ruanganTindakan' => $item3->namaruangan,
            );
        }
        $result = array(
            'JenisPetugasPelaksana' => $data11,
            'Pegawai' => $data22,
            'dokter' => $dataDokter,
            'Ruangan' => $data33,
            'departemen' => $datadept3,
            'datalogin' => $dataLogin,
            'kelompokuser' => $KelompokUser,
            'xby' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getLogin(Request $request)
    {
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $KelompokUser = DB::select(DB::raw("select ku.id,ku.kelompokuser from loginuser_s as lu 
                    INNER JOIN kelompokuser_s as ku on lu.objectkelompokuserfk=ku.id
                    where lu.id =:IDIDID;"),
            array(
                'IDIDID' => $dataLogin['userData']['id'],
            )
        );

        $dataAPD = DB::select(DB::raw("
                 select x.id,x.namaruangan,x.tglmasuk from (select  ru.id,ru.namaruangan ,apd.tglmasuk
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                where pd.noregistrasi=:noregistrasi
                  
                 ) as x group by x.id,x.namaruangan,x.tglmasuk order by x.tglmasuk asc
            "),
            array(
                'noregistrasi' => $request['noRegistrasi'] ,
            )
        );
        $result = array(
            'datalogin' => $dataLogin,
            'kelompokuser' => $KelompokUser,
            'listRuangan' =>$dataAPD,
            'pegawailoginfk' =>$dataPegawai,
            'xby' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function getProdukbyRuangan(Request $request)
    {
        $data4 = \DB::table('produk_m as pr')
            ->join('mapruangantoproduk_m as mrp', 'mrp.objectprodukfk', '=', 'pr.id')
            ->select('pr.id', 'pr.namaproduk as namaPelayanan')
            ->where('mrp.objectruanganfk', $request['objectruanganfk'])
            ->get();
        foreach ($data4 as $item) {
            $result[] = array(
                'id' => $item->id,
                'namaPelayanan' => $item->namaPelayanan,
            );
        };
        $result[] = array(
            'id' => 395,
            'namaPelayanan' => 'Karcis',
        );

        return $this->respond($result);
    }
    public function getKelasByProduk(Request $request)
    {
        $data4 = \DB::table('kelas_m as kl')
            ->join('harganettoprodukbykelas_m as hnp', 'hnp.objectkelasfk', '=', 'kl.id')
            ->select('kl.id', 'kl.namakelas as kelasTindakan', 'hnp.hargasatuan')
            ->where('hnp.objectprodukfk', $request['objectprodukfk'])
            ->get();

        return $this->respond($data4);
    }
    public function getDaftarRegistrasiPasien(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
//            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pemakaianasuransi_t as pas', 'pas.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk', 'sbm.nosbm', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pd.objectruanganlastfk as ruanganid','pas.nosep','pas.norec as norec_pa','br.norec as norec_br')
            ->whereNull('br.norec');

        $filter = $request->all();
        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
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
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noreg'] . '%');
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

        $data = $data->get();

        return $this->respond($data);
    }
    public function getAPD(Request $request)
    {

        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
//            ->leftjoin('registrasipelayananpasien_t as rpp', 'rpp.noregistrasifk', '=',
//                DB::raw('pd.norec AND rpp.objectruanganfk=apd.objectruanganfk'))
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('kamar_m as km', 'km.id', '=', 'apd.objectkamarfk')
            ->leftJoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->leftJoin('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->leftJoin('tempattidur_m as tt','tt.id','=','apd.nobed')
//            ->select('apd.norec', 'apd.tglregistrasi', 'ru.id as ruid_asal', 'ru.namaruangan', 'kls.id as kelasid', 'kls.namakelas', 'km.namakamar', 'apd.nobed', 'apd.statusantrian',
//                'apd.statuskunjungan', 'apd.tglregistrasi', 'apd.tgldipanggildokter', 'apd.tgldipanggilsuster', 'pg.id as pgid', 'pg.namalengkap as namadokter',
//                'apd.objectasalrujukanfk','pd.nostruklastfk','pd.nosbmlastfk','rpp.noregistrasifk as rpp','apd.tglmasuk','apd.tglkeluar','ru.objectdepartemenfk','dept.namadepartemen',
//                DB::raw('case when rpp.israwatgabung is null or rpp.israwatgabung =0 then \' \' else \'Ya\' end as israwatgabung','pm.tglmeninggal' ));
            ->select('apd.norec', 'apd.tglregistrasi', 'ru.id as ruid_asal', 'ru.namaruangan', 'kls.id as kelasid', 'kls.namakelas', 'km.namakamar', 'tt.reportdisplay as nobed', 'apd.statusantrian',
                'apd.statuskunjungan', 'apd.tglregistrasi', 'apd.tgldipanggildokter', 'apd.tgldipanggilsuster', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'apd.objectasalrujukanfk','pd.nostruklastfk','pd.nosbmlastfk','apd.tglmasuk','apd.tglkeluar','ru.objectdepartemenfk','dept.namadepartemen','pm.tglmeninggal');
//                DB::raw('pm.tglmeninggal' ));

        $filter = $request->all();
        if (isset($filter['noregistrasi']) && $filter['noregistrasi'] != "" && $filter['noregistrasi'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $filter['noregistrasi']);
        }
//        $data = $data->groupBy('apd.norec', 'apd.tglregistrasi', 'ru.id', 'ru.namaruangan', 'kls.id', 'kls.namakelas', 'km.namakamar', 'apd.nobed', 'apd.statusantrian',
//                'apd.statuskunjungan', 'apd.tglregistrasi', 'apd.tgldipanggildokter', 'apd.tgldipanggilsuster', 'pg.id', 'pg.namalengkap',
//                'apd.objectasalrujukanfk','pd.nostruklastfk','pd.nosbmlastfk','rpp.noregistrasifk','pd.noregistrasi','rpp.israwatgabung',
//                'apd.tglmasuk','apd.tglkeluar','ru.objectdepartemenfk','dept.namadepartemen','pm.tglmeninggal');
        $data = $data->groupBy('apd.norec', 'apd.tglregistrasi', 'ru.id', 'ru.namaruangan', 'kls.id', 'kls.namakelas', 'km.namakamar', 'tt.reportdisplay', 'apd.statusantrian',
                                'apd.statuskunjungan', 'apd.tglregistrasi', 'apd.tgldipanggildokter', 'apd.tgldipanggilsuster', 'pg.id', 'pg.namalengkap',
                                'apd.objectasalrujukanfk','pd.nostruklastfk','pd.nosbmlastfk','pd.noregistrasi',
                                'apd.tglmasuk','apd.tglkeluar','ru.objectdepartemenfk','dept.namadepartemen','pm.tglmeninggal');
        $data = $data->orderBy('apd.tglregistrasi','desc');
        $data = $data->get();

        return $this->respond($data);
    }
    public function getDataComboDaftarRegPasien(Request $request)
    {
        $dataLogin = $request->all();
        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $darah = \DB::table('ruangan_m as ru')
            ->where('ru.kdruangan', '41')
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.id', '18')
            ->orderBy('dept.namadepartemen')
            ->get();

        $departemen = \DB::table('departemen_m as dept')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->whereIn('dept.id', [18, 16])
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->whereIn('ru.objectdepartemenfk',[16,17,25,35])
//            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled',true)
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

        $kdPelayananRanap = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.statusenabled', true)
            ->where('p.namafield','kddeptlayananRI')
            ->first();

        $kdPelayananOk = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.statusenabled', true)
            ->where('p.namafield','KdPelayananOk')
            ->first();

        $dataKelompokTanpaUmum = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->where('kp.id', '<>', 1)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $dataJenisDiagnosa = \DB::table('jenisdiagnosa_m as jd')
            ->select('jd.id','jd.jenisdiagnosa')
            ->where('jd.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('jd.jenisdiagnosa')
            ->get();

        $dataRuanganJalan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->whereIn('ru.objectdepartemenfk', [18,28,24,3,27])
            ->orderBy('ru.namaruangan')
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'darah' => $darah,
            'dept' => $dept,
            'ruanganRi' => $ruanganRi,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
            'pembatalan' => $pembatalan,
            'deptt' => $departemen,
//            'rekanan' => $dataRekanan,
            'kelompokpasiensatu' => $dataKelompokTanpaUmum,
            'kddeptlayananranap' => $kdPelayananRanap,
            'kddeptlayananok' => $kdPelayananOk,
            'jenisdiagnosa'=>$dataJenisDiagnosa,
            'ruanganjalan' => $dataRuanganJalan,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function simpanUpdateDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $data = PasienDaftar::where('norec', $request['norec'])
                ->where('kdprofile', (int)$kdProfile)
                ->update([
                        'objectpegawaifk' => $request['objectpegawaifk'],
                        'objectdokterpemeriksafk' => $request['objectpegawaifk']
                    ]);
//            $data2= AntrianPasienDiperiksa::where('noregistrasifk', $request['norec'])
//                ->where('objectruanganfk', $data['objectruanganlastfk'])
//                ->update([
//                        'objectpegawaifk' => $request['objectpegawaifk']]
//                );
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
    public function simpanUpdateDokterAPD(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {
//            $data= PasienDaftar::where('norec', $request['norec'])
//                ->update([
//                        'objectpegawaifk' => $request['objectpegawaifk']]
//                );
            $data2 = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
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
    public function simpanUpdateRekananPD(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $data = PasienDaftar::where('norec', $request['norec_pd'])
                ->update([
                        'objectrekananfk' => $request['objectrekananfk'],
                        'objectkelompokpasienlastfk' => $request['objectkelompokpasienlastfk'],
                    ]
                );
//            $data2= AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
//                ->update([
//                        'objectpegawaifk' => $request['objectpegawaifk']]
//                );
            $transMessage = "Update Rekanan berhasil!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Rekanan gagal";
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
    public function simpanUpdateDokterPPP(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $datadata = PelayananPasienPetugas::where('pelayananpasien', $request['norec_pp'])
                ->where('objectjenispetugaspefk', 4)->first();
            if (count($datadata) > 0) {
                $data2 = PelayananPasienPetugas::where('pelayananpasien', $request['norec_pp'])
                    ->where('objectjenispetugaspefk', 4)
                    ->update([
                            'objectpegawaifk' => $request['objectpegawaifk']]
                    );
            } else {
                $data1 = new PelayananPasienPetugas;
                $data1->norec = $data1->generateNewId();
                $data1->kdprofile = 0;
                $data1->statusenabled = true;
                $data1->nomasukfk = $request['norec_apd'];
//                $data1->objectasalprodukfk = true;
                $data1->objectjenispetugaspefk = 4;
//                $data1->objectprodukfk = true;
//                $data1->objectruanganfk = true;
//                $data1->deskripsitugasfungsi = true;
//                $data1->ispetugaspepjawab = true;
                $data1->pelayananpasien = $request['norec_pp'];
//                $data1->tglpelayanan = true;
                $data1->objectpegawaifk = $request['objectpegawaifk'];
                $data1->save();
            }
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
    public function simpanInsertAPD(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $pd = PasienDaftar::where('norec',$request['norec_pd'])->first();
            $dataAPD = new AntrianPasienDiperiksa;
            $dataAPD->norec = $dataAPD->generateNewId();
            $dataAPD->kdprofile = $kdProfile;
            $dataAPD->statusenabled = true;
            $dataAPD->objectasalrujukanfk = $request['asalrujukanfk'];
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
    public function simpanUpdateTglPelayanan(Request $request)
    {
        DB::beginTransaction();
        $transStatus = 'true';
        try {
            $data = PelayananPasien::where('norec', $request['norec_pp'])
                ->update([
                        'tglpelayanan' => $request['tanggalPelayanan']]
                );
            $transMessage = "Update Tanggal Pelayanan berhasil!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Tanggal Pelayanan gagal";
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
    public function hapusAPD(Request $request) {

        DB::beginTransaction();
        $transStatus = 'true';
        if ($request['norec_apd'] != ''){
            try{
                $delApd = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])->delete();
                $transMessage = "Hapus Registrasi Berhasil";
            }
            catch(\Exception $e){
                $transStatus= 'false';
                $transMessage= "Tidak bisa dihapus, sudah ada tindakannya";
            }
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
    public function saveAkomodasiOtomatis(Request $request) {
//        ini_set('max_execution_time', 3000); //6 minutes
        DB::beginTransaction();
        try {
            $data2 = DB::select(DB::raw("select apd.tglmasuk,apd.tglkeluar,apd.norec as norec_apd,pd.tglregistrasi
                    from pasiendaftar_t as pd
                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                    INNER JOIN ruangan_m as ru_pd on ru_pd.id=apd.objectruanganfk
                    where ru_pd.objectdepartemenfk=16
                    and pd.noregistrasi=:noregistrasi and pd.tglpulang is null order by apd.tglmasuk;"),
                array(
                    'noregistrasi' => $request['noregistrasi'],
                )
            );
            foreach ($data2 as $dateAPD){
                $tglMasuk = $dateAPD->tglmasuk;
                if (is_null($dateAPD->tglkeluar) == true){
                    $tglKeluar = date('Y-m-d 23:59:59');
                }else{
                    $tglKeluar = $dateAPD->tglkeluar;
                }
                $arrDate = $this->dateRange( $tglMasuk, $tglKeluar);
//                $arrDate = $this->dateRange( '2010-07-26', '2010-08-05');
                foreach ($arrDate as $itemDate){
                    $tglAwal = $itemDate . ' 00:00';
                    $tglAkhir = $itemDate . ' 23:59';

                    $data = DB::select(DB::raw("select pp.tglpelayanan,rpp.objectkelasfk,
                    rpp.objectruanganfk,rpp.israwatgabung
                    from pasiendaftar_t as pd
                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
                    INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                    INNER JOIN produk_m as pr on pr.id=pp.produkfk
                    INNER JOIN ruangan_m as ru_pd on ru_pd.id=pd.objectruanganlastfk
                    where pd.tglpulang is null  and ru_pd.objectdepartemenfk=16
                    and pp.tglpelayanan between :tglAwal and :tglAkhir and pr.namaproduk ilike '%akomodasi%'
                    and pd.noregistrasi=:noregistrasi ;"),
                        array(
                            'tglAwal' => $tglAwal,//date('Y-m-d 00:00:00'),
                            'tglAkhir' => $tglAkhir,//date('Y-m-d 23:59:59'),
                            'noregistrasi' => $request['noregistrasi'],
                        )
                    );
                    if (count($data) == 0){
                        $dataDong = DB::select(DB::raw("select rpp.objectkelasfk,
                            rpp.objectruanganfk,rpp.israwatgabung ,apd.norec as norec_apd,pd.tglregistrasi
                            from pasiendaftar_t as pd
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                            INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec and rpp.objectruanganfk=apd.objectruanganfk
                            INNER JOIN ruangan_m as ru_pd on ru_pd.id=apd.objectruanganfk
                            where pd.tglpulang is null and  ru_pd.objectdepartemenfk=16
                            and pd.noregistrasi=:noregistrasi and apd.norec=:norec_apd;"),
                            array(
                                'noregistrasi' => $request['noregistrasi'],
                                'norec_apd' => $dateAPD->norec_apd,
                            )
                        );
                        if ($dataDong[0]->israwatgabung == 1){
                            $sirahMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelas_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid and map.israwatgabung=1"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }else{
                            $sirahMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelas_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid and map.israwatgabung is null"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }

                        // request : RSABHK-1142
                        $diskon = 0 ;
                        $tglAwalDiskon = $itemDate . ' 23:59';
                        $start  = new Carbon($dateAPD->tglregistrasi);
                        $end    = new Carbon($tglAwalDiskon);
                        $tglRegis = date('Y-m-d', strtotime($dateAPD->tglregistrasi));
                        $selisihjam = $start->diff($end)->format('%H');
                        if ($tglRegis == $itemDate){
                            if ((int)$selisihjam <= 6 ){
                                $diskon = ((float)$sirahMacan[0]->hargasatuan * 50)/100;
                            }
                        }
                        // ## END ##


                        $PelPasien = new PelayananPasien();
                        $PelPasien->norec = $PelPasien->generateNewId();
                        $PelPasien->kdprofile = 0;
                        $PelPasien->statusenabled = true;
                        $PelPasien->noregistrasifk =  $dateAPD->norec_apd;//$dataDong[0]->norec_apd;
                        $PelPasien->tglregistrasi = $dataDong[0]->tglregistrasi;
                        $PelPasien->hargadiscount = $diskon;//0;
                        $PelPasien->hargajual =  $sirahMacan[0]->hargasatuan;
                        $PelPasien->hargasatuan =  $sirahMacan[0]->hargasatuan;
                        $PelPasien->jumlah = 1;
                        $PelPasien->kelasfk =  $dataDong[0]->objectkelasfk;
                        $PelPasien->kdkelompoktransaksi =  1;
                        $PelPasien->piutangpenjamin =  0;
                        $PelPasien->piutangrumahsakit = 0;
                        $PelPasien->produkfk =  $sirahMacan[0]->objectprodukfk;
                        $PelPasien->stock =  1;
                        $PelPasien->tglpelayanan = $tglAwal;// date('Y-m-d H:i:22');
                        $PelPasien->harganetto =  $sirahMacan[0]->harganetto1;

                        $PelPasien->save();
                        $PPnorec = $PelPasien->norec;

                        if ($dataDong[0]->israwatgabung == 1){
                            $buntutMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelasd_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid  and map.israwatgabung=1"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }else{
                            $buntutMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
                                INNER JOIN harganettoprodukbykelasd_m as hett on hett.objectprodukfk=map.objectprodukfk
                                where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid  and map.israwatgabung is null"),
                                array(
                                    'ruanganid' => $dataDong[0]->objectruanganfk,
                                    'kelasid' => $dataDong[0]->objectkelasfk,
                                )
                            );
                        }

                        foreach ($buntutMacan as $itemKomponen) {
                            $PelPasienDetail = new PelayananPasienDetail();
                            $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                            $PelPasienDetail->kdprofile = 0;
                            $PelPasienDetail->statusenabled = true;
                            $PelPasienDetail->noregistrasifk = $dateAPD->norec_apd;//$dataDong[0]->norec_apd;
                            $PelPasienDetail->aturanpakai = '-';
                            $PelPasienDetail->hargadiscount = $diskon;
                            $PelPasienDetail->hargajual = $itemKomponen->hargasatuan;
                            $PelPasienDetail->hargasatuan = $itemKomponen->hargasatuan;
                            $PelPasienDetail->jumlah = 1;
                            $PelPasienDetail->keteranganlain = '-';
                            $PelPasienDetail->keteranganpakai2 = '-';
                            $PelPasienDetail->komponenhargafk = $itemKomponen->objectkomponenhargafk;
                            $PelPasienDetail->pelayananpasien = $PPnorec;
                            $PelPasienDetail->piutangpenjamin = 0;
                            $PelPasienDetail->piutangrumahsakit = 0;
                            $PelPasienDetail->produkfk = $itemKomponen->objectprodukfk;
                            $PelPasienDetail->stock = 1;
                            $PelPasienDetail->tglpelayanan = $tglAwal;//date('Y-m-d H:i:22');
                            $PelPasienDetail->harganetto = $itemKomponen->harganetto1;
                            $PelPasienDetail->save();

                            $diskon =0;
                        }
                    }
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Akomodasi Otomatis";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "";
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $selisihjam,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 201,
                "data" => $data2,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($result);

    }
    public function getDataComboAkomodasi(Request $request) {
        $dataLogin = $request->all();

        $dataRuanganInap=[];
        if ($request['ruangan']==1) {
            $dataRuanganInap = \DB::table('ruangan_m as ru')
                ->select('ru.id', 'ru.namaruangan')
                ->where('ru.objectdepartemenfk', 16)
                ->where('ru.statusenabled', true)
                ->orderBy('ru.namaruangan')
                ->get();
        }

        $dataProduk=[];
        $datalistAkomodasi=[];
        if ($request['produk']==1){
            $dataProduk = \DB::table('produk_m as pr')
                ->JOIN('mapruangantoproduk_m as ma','ma.objectprodukfk','=','pr.id')
                ->select('pr.id','pr.namaproduk')
                ->where('pr.statusenabled',true)
                ->orderBy('pr.namaproduk');
            if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
                $dataProduk = $dataProduk->where('ma.objectruanganfk','=', $request['objectruanganfk']);
            }
            $dataProduk->where('pr.namaproduk','ilike','%akomodasi%');
            $dataProduk = $dataProduk->get();

            $datalistAkomodasi = \DB::table('produk_m as pr')
                ->JOIN('mapruangantoakomodasi_t as ma','ma.objectprodukfk','=','pr.id')
                ->select('pr.id','pr.namaproduk','ma.israwatgabung','ma.id as maid','ma.statusenabled')
                ->where('pr.statusenabled',true)
                ->where('ma.statusenabled',true)
                ->orderBy('pr.namaproduk');
            if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
                $datalistAkomodasi = $datalistAkomodasi->where('ma.objectruanganfk','=', $request['objectruanganfk']);
            }
            $datalistAkomodasi = $datalistAkomodasi->get();
        }


        $result = array(
            'produk' => $dataProduk,
            'ruangan' => $dataRuanganInap,
            'listakomodasi' => $datalistAkomodasi,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function SaveMapAkomodasiTea(Request $request) {
        $data = $request->all();
        try {
            if ($data['status'] == 'HAPUS') {
                $newKS = MapRuanganToAkomodasi::where('id', $request['maid'])->delete();

            }else {
                if ($data['maid'] == '') {
                    if ($data['rg'] == 'YES'){
                        $RG =1;
                    }else{
                        $RG =null;
                    }
                    $newKS = new MapRuanganToAkomodasi();
                    $norecKS = MapRuanganToAkomodasi::max('id');
                    $norecKS = $norecKS + 1;
                    $newKS->id = $norecKS;
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = 0;
                    $newKS->statusenabled = true;
                    $newKS->objectprodukfk = $data['pelayanan'];
                    $newKS->objectruanganfk = $data['ruangan'];
                    $newKS->israwatgabung = $RG;

                    $newKS->save();
                } else {
                    if ($data['rg'] == 'YES'){
                        $newKS = MapRuanganToAkomodasi::where('id', $request['maid'])
                            ->update([
                                    'objectruanganfk' => $data['ruangan'],
                                    'israwatgabung' => 1,
                                ]
                            );
                    }else{
                        $newKS = MapRuanganToAkomodasi::where('id', $request['maid'])
                            ->update([
                                    'objectruanganfk' => $data['ruangan'],
                                    'israwatgabung' => null,
                                ]
                            );
                    }

//                    $newKSasas = MapRuanganToAkomodasi::where('id', $request['maid'])->first();
                }

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Simpan Map";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage =  $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $newKS,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDetailpasien(Request $request) {
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
            ->select( 'apd.norec as norec_apd', 'pd.nocmfk',
                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
                'kp.id as kpid', 'kp.kelompokpasien as jenisPasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
                'pas.id as pasid', 'pas.nocm as noCm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jenisKelamin', 'pd.noregistrasi as noRegistrasi', 'pas.namapasien as namaPasien',
                'pd.tglregistrasi as tglMasuk', 'pd.norec as norec_pd', 'pd.tglpulang as tglPulang', 'pas.notelepon',
                'pd.objectrekananfk as rekananid', 'kls2.id as klsid2', 'kls2.namakelas as kelasRawat',
                'rk.namarekanan as namaPenjamin','ru2.namaruangan as lastRuangan','sp.nostruk','sp.norec as strukfk','pd.statuspasien as StatusPasien'
            )
            ->take(1)
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->get();


        $result = array(
            'data' => $pelayanan,
            'message' => 'as@epic',
        );

        return $this->respond($pelayanan);
    }
    public function getKomponenHargaPelayanan(Request $request)
    {
        $data4 = \DB::table('pelayananpasiendetail_t as ppd')
//            ->join('pelayananpasiendetail_t as ppd', 'pp.norec', '=', 'ppd.pelayananpasien')
            ->join('komponenharga_m as kh', 'kh.id', '=', 'ppd.komponenhargafk')
            ->select('ppd.pelayananpasien as norec_pp', 'ppd.norec', 'kh.komponenharga', 'ppd.jumlah',
                     'ppd.hargasatuan','ppd.hargadiscount','ppd.jasa')
            ->where('ppd.pelayananpasien', $request['norec_pp'])
            ->where('ppd.statusenabled',true)
            ->get();

        $result = array(
            'data'=> $data4,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getVerifikasiNoregistrasi(Request $request)
    {
        $status=true;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select('sp.nostruk')
//            ->where('ru.objectdepartemenfk','=',16)
            ->whereNotIn('apd.objectruanganfk',[36,309]);

        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi', $request['noregistrasi']);
        }
        if(isset($request['norec_pd']) && $request['norec_pd']!="" && $request['norec_pd']!="undefined"){
            $data = $data->where('pd.noregistrasi', $request['norec_pd']);
        }
        $data = $data->get();

        if (count($data) == 0 ){
            $status=false;
        }

        $status=array(
            'status' => $status,
        );

        return $this->respond($status);
    }
    public function getDaftarRegistrasiPasienBD (Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pemakaianasuransi_t as pas', 'pas.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk', 'sbm.nosbm', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pd.objectruanganlastfk as ruanganid','pas.nosep','br.norec as norec_br')
            ->whereNull('br.norec');

        $filter = $request->all();
        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
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
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noreg'] . '%');
        }
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%' . $filter['norm'] . '%');
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%' . $filter['nama'] . '%');
        }
        $data = $data->orderBy('pd.noregistrasi');
        $data = $data->take(50);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getTindakanTakTerklaim(Request $request)
    {
        $result=[];
        $total=0;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pelayananpasientidakterklaim_t as pp','pp.noregistrasifk','=','apd.norec')
            ->leftJoin('pelayananpasienpetugas_t as ppp','ppp.pelayananpasien','=','pp.pelayananpasien')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->join('kelas_m as kl','kl.id','=','pp.kelasfk')
            ->join('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->join('ruangan_m as ru1','ru1.id','=','apd.objectruanganfk')
            ->join('produk_m as pr','pr.id','=','pp.produkfk')
            ->leftJoin('strukpelayanan_t as sp','sp.norec','=','pd.nostruklastfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','ppp.objectpegawaifk')
            ->leftJoin('jenispetugaspelaksana_m as jpp','jpp.id','=','ppp.objectjenispetugaspefk')
            ->leftJoin('strukbuktipenerimaan_t as sbm','sbm.norec','=','pd.nosbmlastfk')
            ->select(DB::raw("pp.norec as norec_pptk,pp.tglpelayanan as tglPelayanan,pr.namaproduk as namaPelayanan,
                     pp.jumlah,kl.namakelas,ru1.namaruangan,pp.hargajual as harga,case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon,
                     sp.nostruk || ' / ' || sbm.nosbm as nostrukfk,sbm.nosbm,apd.objectruanganfk,
                     pr.id as prid,0 as klid,apd.norec as norec_apd,pd.norec as norec_pd,sp.norec as norec_sp,
                     pp.pelayananpasien as norec_pp,'-' as komponen,case when pp.jasa is null then 0 else pp.jasa end as jasa,
                     pp.aturanpakai,case when jpp.id is null then 0 else jpp.id end as jppid,
                     case when jpp.jenispetugaspe is null then '-' else jpp.jenispetugaspe end as jenispetugaspe,
                     case when ppp.objectpegawaifk is null then 0 else ppp.objectpegawaifk end as objectpegawaifk,
                     case when pg.namalengkap is null then '-' else pg.namalengkap end as namalengkap"))
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->get();

        foreach ($data as $item){
            $total =0;
            if ($item->prid != null){
                $total = (($total +  $item->harga -  $item->diskon) *  $item->jumlah)+ $item->jasa;
            }

            $details = DB::select(DB::raw("
                select case when jpp.id is null then 0 else jpp.id end as jppid,
                case when jpp.jenispetugaspe is null then '-' else jpp.jenispetugaspe end as jenispetugaspe,
                case when ppp.objectpegawaifk is null then 0 else ppp.objectpegawaifk end as objectpegawaifk,
                case when pg.namalengkap is null then '-' else pg.namalengkap end as namalengkap
                from pelayananpasientidakterklaim_t as pptk
                LEFT JOIN pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pptk.pelayananpasien
                LEFT JOIN pegawai_m as pg on pg.id = ppp.objectpegawaifk
                LEFT JOIN jenispetugaspelaksana_m as jpp on jpp.id = ppp.objectjenispetugaspefk
                where pptk.nomasukfk=:norec"),
                array(
                    'norec' => $item->norec_pd,
                )
            );
//            $details=$details->first();
//            foreach ($details as $data){
                $result[] = array(
                    'norec_pptk' => $item->norec_pptk,
                    'tglPelayanan' => $item->tglpelayanan,
                    'namaPelayanan' => $item->namapelayanan,
                    'jumlah' => $item->jumlah,
                    'kelasTindakan' => $item->namakelas,
                    'ruanganTindakan' => $item->namaruangan,
                    'harga' => $item->harga,
                    'diskon' => $item->diskon,
                    'nostrukfk' => $item->nostrukfk,
                    'nosbm' => $item->nosbm,
                    'ruid' => $item->objectruanganfk,
                    'prid' => $item->prid,
                    'klid' => $item->klid,
                    'total' => $total,
                    'norec_apd' => $item->norec_apd,
                    'norec_pd' => $item->norec_pd,
                    'norec_sp' => $item->norec_sp,
                    'norec_pp' => $item->norec_pp,
                    'komponen' => $item->komponen,
                    'jasa' => $item->jasa,
                    'aturanpakai' => $item->aturanpakai,
                    'jppid' =>$item->jppid,
                    'jenispetugaspe' => $item->jenispetugaspe,
                    'dokter' => $item->namalengkap,
                    'pgid' => $item->objectpegawaifk,
                );
//            }

        }
        $result = array(
            'data'=> $result,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
	public function closePemeriksaan(Request $request)
	{
		DB::beginTransaction();
		$transStatus = 'true';
		try {
			$data = PasienDaftar::where('norec', $request['norec_pd'])
				->update([
						'isclosing' => true
						]);
			$transMessage = "Closing Pemeriksaan Berhasil";
		} catch (\Exception $e) {
			$transStatus = 'false';
			$transMessage = "Closing Pemeriksaan Gagal";
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
	public function getStatusClosePemeriksaan(Request $request)
	{
		$data  = PasienDaftar::where('noregistrasi',$request['noregistrasi'])->first();
		$status = false;
		if(!empty($data) && $data->isclosing != null){
			$status = $data->isclosing;
		}
		$result = array(
			'data'=> $status,
			'message' => 'ramdan@epic',
		);
		return $this->respond($result);
	}
    public function getDataComboDetailRegis(Request $request) {
        $dataLogin = $request->all();
        $tahun =  $this->getDateTime()->format('Y');
        $SuratKematian = "_______/RSUD.C/SKS/_____________/SKK/________/".$tahun;
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->whereIn('ru.objectdepartemenfk', array(3, 17, 18, 24, 25, 26, 27, 28, 16,30,34,5,31))
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id',[3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35,30])
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuanganAll = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataRekanan = \DB::table('rekanan_m as ru')
            ->where('ru.statusenabled',true)
            ->select('ru.id','ru.namarekanan')
            ->orderBy('ru.namarekanan')
            ->get();

        $dataKelompokPasien = \DB::table('kelompokpasien_m as ru')
            ->where('ru.statusenabled',true)
            ->select('ru.id','ru.kelompokpasien')
            ->orderBy('ru.kelompokpasien')
            ->get();
        $kelas = \DB::table('kelas_m as kls')
            ->select('kls.id','kls.namakelas')
            ->where('kls.statusenabled', true)
            ->orderBy('kls.namakelas')
            ->get();

        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $dataPenulis = Pegawai::where('statusenabled',true)
            ->where('objectjenispegawaifk',1)
            ->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        foreach ($dataInstalasi as $item) {
            $detail = [];
            foreach ($dataRuanganAll as $item2) {
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

        $dataJenisKelamin=\DB::table('jeniskelamin_m as kls')
            ->select('kls.id','kls.jeniskelamin')
            ->where('kls.statusenabled', true)
            ->orderBy('kls.jeniskelamin')
            ->get();

        $dataJabatan=\DB::table('jabatan_m as kls')
            ->select('kls.id','kls.namajabatan')
            ->where('kls.statusenabled', true)
            ->orderBy('kls.namajabatan')
            ->get();

        $dataHubKeluarga=\DB::table('hubungankeluarga_m as kls')
            ->select('kls.id','kls.hubungankeluarga')
            ->where('kls.statusenabled', true)
            ->orderBy('kls.hubungankeluarga')
            ->get();

        $result = array(
            'ruangan' => $dataRuangan,
            'dokter' => $dataPenulis2,
            'detaillogin' => $dataPegawaiUser,
            'rekanan' => $dataRekanan,
            'kelompokpasien' => $dataKelompokPasien,
            'kelas' =>$kelas,
            'departemen' => $dataDepartemen,
            'jeniskelamin' => $dataJenisKelamin,
            'jabatan' => $dataJabatan,
            'hubungankeluarga' => $dataHubKeluarga,
            'nosurat' => $SuratKematian,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function ubahTanggalDetailRegis(Request $request) {
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
    public function getDataLogin(Request $request) {
        $data = \DB::table('loginuser_s as lu')
            ->JOIN('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('pg.id','pg.namalengkap')
            ->where('lu.id',$request['userData']['id'])
            ->get();


        return $this->respond($data);
    }
    public function getPegawaiSaeutik(Request $request)
    {
        $req = $request->all();

        $Pegawai = \DB::table('pegawai_m as ru')
            ->select('ru.id','ru.namalengkap')
            ->where('ru.statusenabled', true)
//            ->where('ru.objectjenispegawaifk', 1)
            ->orderBy('ru.namalengkap');

        if(isset($req['namapegawai']) &&
            $req['namapegawai']!="" &&
            $req['namapegawai']!="undefined"){
            $Pegawai = $Pegawai->where('ru.namalengkap','ilike','%'. $req['namapegawai'] .'%' );
        };
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $Pegawai = $Pegawai
                ->where('ru.namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
//                ->orWhere('dg.kddiagnosatindakan','ilike',$req['filter']['filters'][0]['value'].'%' )  ;
        }

        $Pegawai=$Pegawai->take(10);
        $Pegawai=$Pegawai->get();

        return $this->respond($Pegawai);
    }
    public function getComboJenisPetugasPel(Request $request)
    {

        $JenisPelaksana = \DB::table('jenispetugaspelaksana_m as jpp')
            ->select('jpp.id','jpp.jenispetugaspe as jenisPetugasPelaksana')
            ->where('jpp.statusenabled', true)
            ->orderBy('jpp.jenispetugaspe')
            ->get();

        $result = array(

            'jenispetugaspelaksana' => $JenisPelaksana,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }
    public function getPelPetugasByPelPasien(Request $request)
    {
        $data4 = \DB::table('pelayananpasien_t as pp')
            ->join('pelayananpasienpetugas_t as ppp', 'pp.norec', '=', 'ppp.pelayananpasien')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ppp.objectpegawaifk')
            ->leftjoin('jenispetugaspelaksana_m as jpp', 'jpp.id', '=', 'ppp.objectjenispetugaspefk')
            ->select('pp.norec as norec_pp', 'ppp.norec as norec_ppp','pg.id as pg_id','pg.namalengkap','jpp.jenispetugaspe',
                'jpp.id as jpp_id')
            ->where('pp.norec', $request['norec_pp'])
//            ->where('jpp.id','<>',2)
            ->distinct()
            ->get();


        $result = array(
            'data'=> $data4,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function simpanDokterPPP(Request $request)
    {
        DB::beginTransaction();
        $new_PPP=$request['pelayananpasienpetugas'];

        try {
            if (isset($new_PPP['norec_ppp']) && $new_PPP['norec_ppp']=='' && isset($new_PPP['objectjenispetugaspefk'])){
                $data1 = new PelayananPasienPetugas();
                $data1->norec = $data1->generateNewId();
                $data1->kdprofile = 0;
                $data1->statusenabled = true;
            }else{
                $data1 =  PelayananPasienPetugas::where('norec',$new_PPP['norec_ppp'])->first();
            }
            if(!empty($data1)){
                $data1->nomasukfk = $new_PPP['norec_apd'];
//                $data1->objectasalprodukfk = true;
                $data1->objectjenispetugaspefk = $new_PPP['objectjenispetugaspefk'];
//                $data1->objectprodukfk = true;
//                $data1->objectruanganfk = true;
//                $data1->deskripsitugasfungsi = true;
//                $data1->ispetugaspepjawab = true;
                $data1->pelayananpasien = $new_PPP['norec_pp'];
//                $data1->tglpelayanan = true;
                $data1->objectpegawaifk = $new_PPP['objectpegawaifk'];
                $data1->save();

            }

            if(isset( $new_PPP['isparamedis'])){
                $data2 =  PelayananPasien::where('norec',$new_PPP['norec_pp'])->first();
                if(!empty($data2)){
                    $data2->isparamedis =  $new_PPP['isparamedis'];
                    $data2->save();
                }
            }
            $transStatus = 'true';
            $transMessage = "Simpan PelayananPasienPetugas berhasil!";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan PelayananPasienPetugas Gagal!";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "data"=>$data1 != null ? $data1 :$data2,
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
    public function hapusPPP(Request $request) {
        $dataLogin = $request->all();
        DB::beginTransaction();
        $r_PPP=$request['pelayananpasienpetugas'];
        if ($r_PPP['norec_ppp'] != '' || $r_PPP['norec_ppp'] != 'undefined'){
            try{
                $data1 = PelayananPasienPetugas::where('norec', $r_PPP['norec_ppp'])->delete();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus= false;
            }

        }
        if ($transStatus='true')
        {    DB::commit();
            $transMessage = "Hapus PelayananPasienPetugas berhasil!";
        }
        else{
            DB::rollBack();
            $transMessage = "Hapus PelayananPasienPetugas berhasil!";
        }

        return $this->setStatusCode(201)->respond([], $transMessage);

    }
    public function deletePelayananPasien(Request $request) {

        DB::beginTransaction();
        try{
            foreach ($request['dataDel'] as $item) {
                $HapusPP = PelayananPasien::where('norec', $item['norec_pp'])->get();
                foreach ($HapusPP as $pp) {
                    $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->delete();
                    $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->delete();
                }
                $Edit = PelayananPasien::where('norec', $item['norec_pp'])->delete();
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
    public function simpanUpdateDiskonKomponen(Request $request){
        $dataLog = $request->all();
        DB::beginTransaction();
        $transStatus = 'true';
        $nilaiCito=0;
        $totalJasa=0;
        if ($request['hargajasa'] != 0){
            $nilaiCito=0.25;
        }
        try{
            $data= PelayananPasienDetail::where('norec', $request['norec_ppd'])
                ->update([
                        'hargadiscount' => $request['hargadiskon'],
                        'jasa' => ($request['hargakomponen'] - $request['hargadiskon'])*$nilaiCito,
                    ]
                );
            $totalDiskon=0.0;
            $dataaa= PelayananPasienDetail::where('pelayananpasien', $request['norec_pp'])->get();
            foreach ($dataaa as $item){
                $totalDiskon=$totalDiskon+$item->hargadiscount;
                $totalJasa=$totalJasa+$item->jasa;
            }
            $data2= PelayananPasien::where('norec', $request['norec_pp'])
                ->update([
                    'hargadiscount' => $totalDiskon,
                    'jasa' => $totalJasa
                ]);
            $transMessage = "Update Diskon Komponen berhasil!";
        }
        catch(\Exception $e){
            $transStatus = 'false';
            $transMessage = "Update Diskon Komponen gagal";
        }

        if($transStatus != 'false'){
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $dataaa,
                "message" =>$transMessage,
            );
        }else{
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $dataLog,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function  getHeaderRekapTagihan($noregistrasi){
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin ('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->leftjoin ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin ('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin ('alamat_m as alm','alm.id','=','pd.nocmfk')
            ->leftjoin ('agama_m as agm','agm.id','=','ps.objectagamafk')
            ->select('pd.norec as norec_pd','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien',
                'ps.tgllahir','ps.namakeluarga','ru.namaruangan','kls.namakelas','kps.kelompokpasien','rk.namarekanan','alm.alamatlengkap',
                'jk.jeniskelamin','agm.agama','ps.nohp','pd.statuspasien','pd.tglpulang')
            ->where('pd.noregistrasi', $noregistrasi)
            ->first();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getRekapTagihan($noregistasi)
    {
        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftjoin('strukresep_t as sre', 'sre.norec', '=', 'pp.strukresepfk')
            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk',
                'pd.nostruklastfk','pd.noregistrasi',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang',
                'pd.objectrekananfk as rekananid',
                'pp.jasa',  'sp.totalharusdibayar', 'sp.totalprekanan',
                'sp.totalbiayatambahan','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis','pp.strukresepfk'
            )
            ->where('pd.noregistrasi', $noregistasi);
//			->orderBy('pp.tglpelayanan', 'pp.rke');

        $pelayanan = $pelayanan->get();

        if (count($pelayanan) > 0) {
            $details = array();
            foreach ($pelayanan as $value) {
                if($value->prid != $this->getProdukIdDeposit()){
                    $jasa = 0;
                    if (isset($value->jasa) && $value->jasa != "" && $value->jasa != null) {
                        $jasa =(float) $value->jasa;
                    }

                    $harga = (float)$value->hargajual;
                    $diskon = (float)$value->hargadiscount;
                    $detail = array(
                        'norec' => $value->norec,
                        'tglPelayanan' => $value->tglpelayanan,
                        'namaPelayanan' => $value->namaproduk,
                        'jumlah' => (float)$value->jumlah,
                        'kelasTindakan' => @$value->namakelas,
                        'ruanganTindakan' => @$value->namaruangan,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                        'strukfk' => $value->nostruk ,
                        'sbmfk' => $value->nosbm,
                        'pgid' => '',
                        'ruid' => $value->ruid,
                        'prid' => $value->prid,
                        'klid' => $value->klid,
                        'norec_apd' => $value->norec_apd,
                        'norec_pd' => $value->norec_pd,
                        'norec_sp' => $value->norec_sp,
                        'jasa' => $jasa,
                        'aturanpakai' => $value->aturanpakai,
                        'iscito' => $value->iscito,
                        'isparamedis' => $value->isparamedis,
                        'strukresepfk' => $value->strukresepfk
                    );

                    $details[] = $detail;
                }


            }
        }

        $arrHsil = array(
            'details' => $details,
            'deposit' =>  $this->getDepositPasien($noregistasi),
        );
        return $this->respond($arrHsil);
    }

    public function saveLoggingUnverifTarek(Request $request){
        DB::beginTransaction();

        $transStatus = true;
        $dataLogin = $request->all();

        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])->first();
        $pasienDaftar = $pasienDaftar->norec;
        $strukPelayanan = StrukPelayanan::where('noregistrasifk', $pasienDaftar)->first();
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= 11;
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

        public function getStatusVerifPiutang(Request $request){
            $data= \DB::table('pasiendaftar_t as pd')
                // ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
                // ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
                // ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
                // ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
                ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
                ->join('pelayananpasien_t as pp', 'apd.norec', '=', 'pp.noregistrasifk')
                ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                ->leftjoin('strukpelayananpenjamin_t as spp', 'spp.nostrukfk', '=', 'sp.norec')
                ->select('pd.norec', 'pd.noregistrasi',
                    'pd.tglpulang','pd.nostruklastfk', 'pd.nosbmlastfk',
                    'spp.noverifikasi as noverif'

                )
                ->whereNotNull('pd.tglpulang')
                ->where('pd.noregistrasi',$request['noReg'])
                ->first();
            return $this->respond($data);
        }

    public function daftarPasienPulang(Request $request){
        $data= \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            // ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            // ->join('pelayananpasien_t as pp', 'apd.norec', '=', 'pp.noregistrasifk')
            // ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            // ->leftjoin('strukpelayananpenjamin_t as spp', 'spp.nostrukfk', '=', 'sp.norec')
            ->select('pd.norec AS norec_pd','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien','pd.nostruklastfk', 'pd.nosbmlastfk','pd.tglmeninggal','p.nosuratkematian',
                // 'spp.noverifikasi',
                'dept.id as deptid',
                'pd.tglclosing')
//            ->whereNull('pd.nostruklastfk')
//            ->whereNull('pd.nosbmlastfk')
            ->where('pd.statusenabled',true)
            ->whereNotNull('pd.tglpulang')
        ;

        $filter = $request->all();
        if(isset($filter['tglAwal']) && $filter['tglAwal']!="" && $filter['tglAwal']!="undefined"){
            $data = $data->where('pd.tglpulang','>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!="" && $filter['tglAkhir']!="undefined"){
            $tgl= $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglpulang','<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId']!="" && $filter['instalasiId']!="undefined"){
            $data = $data->where('dept.id','=', $filter['instalasiId']);
        }

        if(isset($filter['ruanganId']) && $filter['ruanganId']!="" && $filter['ruanganId']!="undefined"){
            $data = $data->where('ru.id','=', $filter['ruanganId']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien']!="" && $filter['namaPasien']!="undefined"){
            $data = $data->where('p.namapasien','ilike', '%'.$filter['namaPasien'].'%');
        }

        if(isset($filter['noReg']) && $filter['noReg']!="" && $filter['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike', '%'.$filter['noReg'].'%');
        }

        if (isset($filter['noRm']) && $filter['noRm']!="" && $filter['noRm']!="undefined") {
            $data = $data->where('p.nocm','ilike','%'.$filter['noRm'].'%');
        }
        if (isset($filter['kelompokPasienId']) && $filter['kelompokPasienId']!="" && $filter['kelompokPasienId']!="undefined") {
            $data = $data->where('kp.id','=',$filter['kelompokPasienId']);
        }

        if(isset($filter['status']) && $filter['status']!="" && $filter['status']!="undefined"){
            if($filter['status']=='Belum Verifikasi'){
                $data = $data ->whereNull('pd.nostruklastfk')->whereNull('pd.nosbmlastfk');
            }elseif($filter['status']=='Verifikasi'){
                $data = $data ->whereNotNull('pd.nostruklastfk')->whereNull('pd.nosbmlastfk');
            }elseif($filter['status']=='Lunas'){
                $data = $data ->whereNotNull('pd.nostruklastfk')->whereNotNull('pd.nosbmlastfk');;
            }
        }
        if (isset($filter['jmlRows']) && $filter['jmlRows']!="" && $filter['jmlRows']!="undefined") {
            $data = $data->take($filter['jmlRows']);
        }

        // $data=$data->groupBy('pd.norec','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
        //     'pd.tglpulang', 'pd.statuspasien','pd.nostruklastfk', 'pd.nosbmlastfk',
        //     // 'spp.noverifikasi',
        //     'dept.id','pd.tglclosing');


//         if(!empty($filter['tglAwal']) && !empty($filter['tglAkhir']) && empty($filter['noReg']) && empty($filter['noRm']) && empty($filter['status']) && empty($filter['ruanganId']) && empty($filter['namaPasien']) && empty($filter['instalasiId'])){
//             $data = $data->limit(10)->get();
// //            $data = $data->get();

//         }else if (empty($filter['tglAwal']) && empty($filter['tglAkhir']) && empty($filter['noReg']) && empty($filter['noRm']) && empty($filter['status']) && empty($filter['ruanganId']) && empty($filter['namaPasien']) && empty($filter['instalasiId'])) {

//             $data = $data->limit(10)->get();

//         }else if(!empty($filter['tglAwal']) && !empty($filter['tglAkhir']) && empty($filter['noReg']) && empty($filter['noRm']) && $filter['status'] == "undefined" && $filter['ruanganId'] == "undefined" && empty($filter['namaPasien']) && $filter['instalasiId'] == "undefined"){

//             $data = $data->limit(10)->get();

//         } else {
//             $data = $data->limit(10)->get();
// //            $data =$data->get();
//         }
        $data =$data->get();


        $result = array();
        foreach ($data as $pasienD){
//            $pd=PasienDaftar::find($pasienD->norec);
//            $totalHrsDibayar = StrukPelayanan::where('noregistrasifk',$pasienD->norec)
//                ->where('statusenabled',true)
//                ->orderBy('norec','desc')
//                ->first();
//            $pd->DepositId = $this->getProdukIdDeposit();
            $status="-";

//            if (isset($totalHrsDibayar)) {
//                if ($pasienD->nostruklastfk != null && $totalHrsDibayar->totalharusdibayar == 0) {
//                    $status = '-';//"Lunas";
//                } else {
            if ($pasienD->nostruklastfk == null && $pasienD->nosbmlastfk == null) {
                $status = "Belum Verifikasi";
            } elseif ($pasienD->nostruklastfk != null && $pasienD->nosbmlastfk == null) {
                $status = "Verifikasi";
            } elseif ($pasienD->nostruklastfk != null && $pasienD->nosbmlastfk != null) {
                $status = '-';//"Lunas";
            }
//                }
//            }else{
//                if ($pasienD->nostruklastfk == null && $pasienD->nosbmlastfk == null) {
//                    $status = "Belum Verifikasi";
//                } elseif ($pasienD->nostruklastfk != null && $pasienD->nosbmlastfk == null) {
//                    $status = "Verifikasi";
//                } elseif ($pasienD->nostruklastfk != null && $pasienD->nosbmlastfk != null) {
//                    $status = '-';//"Lunas";
//                }
//            }
            $result[] = array(
                'tanggalMasuk'  =>$pasienD->tglregistrasi,
                'noCm'  => $pasienD->nocm,
                'noRegistrasi'  => $pasienD->noregistrasi,
                'namaRuangan'  => $pasienD->namaruangan,
                'namaPasien'  => $pasienD->namapasien,
                'jenisAsuransi'  => $pasienD->kelompokpasien,
                'tanggalPulang' => $pasienD->tglpulang,
                'tglmeninggal' =>  $pasienD->tglmeninggal,
                'norec_pd' => $pasienD->norec_pd,
//                'isVerified' => $pd->IsVerified,
                'status'    =>  $status,
                // 'noverif'    =>  $pasienD->noverifikasi,
                'deptid' => $pasienD->deptid,
                'tglclosing' => $pasienD->tglclosing,
                'nosuratkematian' => $pasienD->nosuratkematian
//                'isPaid' => $pd->isBayar,
//                'isLunas' => $pd->isBayar,
            );
        }
        return $this->respond($result);
    }

    public function closePemeriksaanPD(Request $request){
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $transStatus = 'true';
        try {
            $status = true;
            $msg = '';
            $tglClose = date('Y-m-d H:i:s');
            if($request['close'] ==false){
                $status = null;
                $tglClose = null;
                $msg = 'Batal';
            }
            $data = PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                ->where('kdprofile',$kdProfile)
                ->update([
                    'isclosing' => $status,
                    'tglclosing' => $tglClose
                ]);
            $transMessage = $msg. " Closing Pemeriksaan Berhasil";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Closing Pemeriksaan Gagal";
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

    public function getStrukPelayanan($noRegister){
        $data= \DB::table('pasiendaftar_t as pd')
            ->join('strukpelayanan_t as sp', 'pd.nostruklastfk', '=', 'sp.norec')
            ->select('pd.norec as norec_pd' ,'pd.noregistrasi','sp.norec as norec_sp',
                'sp.nosbmlastfk','sp.nostruk','sp.totalharusdibayar','sp.tglstruk')
            ->whereNull('sp.nosbmlastfk')
            ->where('pd.statusenabled',true)
            ->whereNotNull('pd.tglpulang')
            ->where('pd.noregistrasi',$noRegister)
            ->orderBy('sp.tglstruk','desc')
            ->get();
        $result  = array(
            'data' =>$data,
        );

        return $this->respond($result);
    }

    public function detailTagihanVerifikasi(Request $request){
        $noRegister = $request['noRegister'];
        $dataRuangan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select( 'ru.namaruangan as namaruangan')
            ->where('pd.noregistrasi', $noRegister)
            ->first();
        $pelayanan=[];
        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
//            ->leftjoin('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
//            ->leftjoin('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
//            ->leftjoin('kelompokprodukbpjs_m as kpBpjs', 'kpBpjs.id', '=', 'pr.objectkelompokprodukbpjsfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//            ->join('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
//            ->join('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
//            ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
//            ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
//            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
//            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
//            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
//            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
//            ->leftjoin('pegawai_m as pgpj', 'pgpj.id', '=', 'pd.objectpegawaifk')
//            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
//            ->leftjoin('strukresep_t as sr', 'sr.norec', '=', 'pp.strukresepfk')
//            ->leftjoin('ruangan_m as rusr', 'rusr.id', '=', 'sr.ruanganfk')
//            ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
//            ->leftjoin('pegawai_m as pgsr', 'pgsr.id', '=', 'sr.penulisresepfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
//            ->leftjoin('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
//            ->leftjoin('pegawai_m as pgsbm', 'pgsbm.id', '=', 'sbm.objectpegawaipenerimafk')

            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk',
                'pd.nostruklastfk','pd.noregistrasi',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang',
                'pd.objectrekananfk as rekananid',
                'pp.jasa',  'sp.totalharusdibayar', 'sp.totalprekanan',
                'sp.totalbiayatambahan', 'pd.kdprofile','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis'
            )
//            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
//                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
//                'pg.id as pgid', 'pg.namalengkap', 'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk', 'ru2.objectdepartemenfk as deptid',
//                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
//                'kp.id as kpid', 'kp.kelompokpasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
//                'pas.id as pasid', 'pas.nocm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jk', 'pd.noregistrasi', 'pas.namapasien',
//                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang', 'pas.notelepon', 'kls.id as klsid', 'kls.namakelas',
//                'pd.objectrekananfk as rekananid', 'ru2.namaruangan as ruanganlast', 'kls2.id as klsid2', 'kls2.namakelas as namakelas2',
//                'sr.noresep', 'rk.namarekanan', 'rusr.namaruangan as ruanganfarmasi', 'pgsr.namalengkap as penulisresep', 'jp.jenisproduk', 'kpBpjs.kelompokprodukbpjs as kelompokprodukbpjs',
//                'pgpj.namalengkap as dokterpj', 'pp.jasa', 'kamar.namakamar', 'sp.totalharusdibayar', 'sp.totalprekanan', 'sppj.totalppenjamin',
//                'sp.totalbiayatambahan', 'pgsbm.namalengkap as namalengkapsbm','pd.kdprofile','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis'
//            )
            ->where('pd.noregistrasi', $noRegister)
            ->orderBy('pp.tglpelayanan', 'pp.rke');


//        if ($request['jenisdata'] == 'resep'){
//            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
//                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
//            };
//            $pelayanan = $pelayanan->whereNotNull('pp.aturanpakai');
//        }
//        if ($request['jenisdata'] == 'layanan'){
//            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
//                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
//            };
//            $pelayanan = $pelayanan->whereNull('pp.aturanpakai');
//        }
        $pelayanan = $pelayanan->get();


        if (count($pelayanan) > 0) {

            $totalBilling = 0;
            $norecAPD = '';
            $norecSP = '';
            $details = array();
            $dibayar=0;
            $diverif=0;
            foreach ($pelayanan as $value) {
                if ($value->produkfk == $this->getProdukIdDeposit()) {
                    continue;
                }
                if ($value->namaproduk == null){
                    continue;
                }
                $jasa = 0;
                if (isset($value->jasa) && $value->jasa != "" && $value->jasa != "undefined") {
                    $jasa = $value->jasa;
                }
                $kmpn = [];

                $harga = (float)$value->hargajual;
                $diskon = (float)$value->hargadiscount;
                $detail = array(
                    'norec' => $value->norec,
                    'tglPelayanan' => $value->tglpelayanan,
                    'namaPelayanan' => $value->namaproduk,
//                    'dokter' => $NamaDokter,
                    'jumlah' => $value->jumlah,
                    'kelasTindakan' => @$value->namakelas,
                    'ruanganTindakan' => @$value->namaruangan,
                    'harga' => $harga,
                    'diskon' => $diskon,
                    'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                    'jppid' => '',
                    'jenispetugaspe' => '',
                    'strukfk' => $value->nostruk . ' / ' . $value->nosbm,
                    'sbmfk' => $value->nosbm,
                    'pgid' => '',
                    'ruid' => $value->ruid,
                    'prid' => $value->prid,
                    'klid' => $value->klid,
                    'norec_apd' => $value->norec_apd,
                    'norec_pd' => $value->norec_pd,
                    'norec_sp' => $value->norec_sp,
                    'komponen' => $kmpn,
                    'jasa' => $jasa,
                    'aturanpakai' => $value->aturanpakai,
                    'iscito' => $value->iscito,
                    'isparamedis' => $value->isparamedis
                );
                $details[] = $detail;
            }
        }

        $arrHsil = array(
            'details' => $details
        );
        return $this->respond($arrHsil);
    }

    public function simpanVerifikasiTagihanTatarekening (Request $request){
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $noRegister = $request['data']['noRegistrasi'];
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $kdProfile
                and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $transMsg = null;
        $totalBilling = 0;
        $totalDeposit = 0;
        DB::beginTransaction();
        try {
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)
            ->where('kdprofile', $kdProfile)
            ->first();
//        $pelayanan = $pasienDaftar->pelayanan_pasien()->select('pelayananpasien_t.*')->whereNull('strukfk')->get();
        $pelayanan =  DB::select(DB::raw("
            select pp.* from pasiendaftar_t as pd
            inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
            where pd.noregistrasi='$noRegister' and pp.strukfk is null   
             and pd.kdprofile = $kdProfile          
         "));
        $SPPenjamin = new StrukPelayananPenjamin();
        $pelayananDetail = $pasienDaftar->pelayanan_pasien_detail()->whereNull('strukfk')
            ->where('pelayananpasiendetail_t.kdprofile',$kdProfile   )
            ->get();
        if (count($pelayanan) == 0) {
            $transMsg = "Pelayanan yang dilakukan pasien tidak ada.";
        }
        if (count($dataLogin['data']['datachecklist']) == 0) {
            $transMsg = "Pelayanan blm ada yg di pilih";
        }
            $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 10, 'S',$kdProfile);
            $strukPelayanan = new StrukPelayanan();
            $strukPelayanan->norec = $strukPelayanan->generateNewId();
            $lastPelayanan = null;

            $sama = false;
            foreach ($pelayanan as $pel) {
                $sama = false;
                foreach ($dataLogin['data']['datachecklist'] as $chklist){
                    if ($chklist['norec'] == $pel->norec){
                        $sama=true;
                        break;
                    }
                }
                if ($sama == true){
                    $harga = ($pel->hargajual == null) ? 0 : $pel->hargajual;
                    $diskon = ($pel->hargadiscount == null) ? 0 : $pel->hargadiscount;
                    if ($pel->nilainormal == -1) {
                        $totalDeposit += ($harga * $pel->jumlah);
                    } else {
                        $totalBilling += (($harga - $diskon) * $pel->jumlah) + $pel->jasa;
                    }
                }
            }
            $totalBilling = (float)$request['data']['jumlahBayar'];
            $strukPelayanan->kdprofile = $kdProfile;
            $strukPelayanan->statusenabled = true;
            $strukPelayanan->nocmfk = $pasienDaftar->nocmfk;
            $strukPelayanan->noregistrasifk = $pasienDaftar->norec;
            $strukPelayanan->objectkelaslastfk = $pasienDaftar->objectkelasfk;
            $strukPelayanan->objectkelompoktransaksifk = 1;
            $strukPelayanan->objectpegawaipenerimafk = $dataPegawaiUser[0]->id;// $this->getCurrentLoginID();
            $strukPelayanan->nostruk = $noStruk;
            $strukPelayanan->totalharusdibayar = $totalBilling ;//- $totalDeposit;
            $strukPelayanan->tglstruk = $this->getDateTime();
            $strukPelayanan->objectruanganfk = $pasienDaftar->objectruanganlastfk;

            $strukPelayanan->save();
                foreach ($dataLogin['data']['datachecklist'] as $chklist){
                    PelayananPasien::where('norec', $chklist['norec'])
                        ->where('kdprofile', $kdProfile)
                        ->update([
                                'strukfk' => $strukPelayanan->norec]
                        );
            }

            foreach ($pelayananDetail as $pelDel) {
                $pelDel->strukfk = $strukPelayanan->norec;
                $pelDel->save();
        }

        $totalKlaim = (float)$request['data']['totalKlaim'];
        if ($totalKlaim > 0) {
//            $strukPelayanan->totalharusdibayar -= $totalKlaim;
            $strukPelayanan->totalprekanan = $totalKlaim;
            if ($pasienDaftar->objectkelompokpasienlastfk == $this->getKelompokPasienPerjanjian()) {
                $rekananpenjamin_id = 0;
            } elseif ($pasienDaftar->objectkelompokpasienlastfk == 2 || $pasienDaftar->objectkelompokpasienlastfk == 4) {
                $rekananpenjamin_id = 2552;
            } else {
                $rekananpenjamin_id = 0; //masih bypass. yang kelompok pasien penjanjian diisini cuma kondisinhya jiga ada klim tapi gak penjaminnya..
            }
            $SPPenjamin->norec = $SPPenjamin->generateNewId();
            //$norecStukPelayananPenjaminStr =  $SPPenjamin->norec;
            $SPPenjamin->statusenabled = true;
            $SPPenjamin->kdprofile = $kdProfile;
            $SPPenjamin->kdkelompokpasien = $pasienDaftar->objectkelompokpasienlastfk;
            $SPPenjamin->kdrekananpenjamin = $rekananpenjamin_id;
            $SPPenjamin->totalbiaya = $totalBilling + $totalKlaim + $totalDeposit;
            $SPPenjamin->totalsudahppenjamin = $totalKlaim; //? apa in ?
            $SPPenjamin->totalsisaharusdibayar = $totalKlaim;
            $SPPenjamin->totalppenjamin = $totalKlaim;
            $SPPenjamin->totalharusdibayar = $totalKlaim;
            $SPPenjamin->totalsudahdibayar = 0;
            $SPPenjamin->totalsudahdibebaskan = 0;
            $SPPenjamin->totalsisapiutang = $totalKlaim;
            $SPPenjamin->totaldibayarlebih = 0;
            $SPPenjamin->nostrukfk = $strukPelayanan->norec;
            $pasienDaftar->nostruklastfk = $strukPelayanan->norec;
            $SPPenjamin->save();

            if($dataLogin['data']['cekMultiPenjamin'] == true && count( $dataLogin['data']['multiPenjamin']) > 0 ){
                    $reqDetail = $dataLogin['data']['multiPenjamin'];
                    foreach ($reqDetail as $values){
                        $SPPenjaminDet = new StrukPelayananPenjaminDetail();
                        $SPPenjaminDet->norec = $SPPenjaminDet->generateNewId();
                        $SPPenjaminDet->statusenabled = true;
                        $SPPenjaminDet->kdprofile = $kdProfile;
                        $SPPenjaminDet->nostrukfk = $strukPelayanan->norec;
                        $SPPenjaminDet->totalppenjamin = $values['klaim'];
                        $SPPenjaminDet->totalharusdibayar = $values['klaim'];
                        $SPPenjaminDet->keteranganlainnya = 'Multi Penjamin';
                        $SPPenjaminDet->kdrekananpenjamin = $values['rekananfk'];
                        $SPPenjaminDet->strukpelayananpenjaminfk = $SPPenjamin->norec;
                        $SPPenjaminDet->kdkelompokpasien = $values['kelompokpasienfk'];
                        $SPPenjaminDet->save();
                    }
            }
        }
        $pasienDaftar->nostruklastfk = $strukPelayanan->norec;
        $pasienDaftar->save();
        $strukPelayanan->save();

        $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
            $transMsg = "Transaksi Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $strukPelayanan,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'result' => $SPPenjamin,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function detailTagihanVerifikasiTatarekening (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noRegister = $request['noRegister'];
        $dataRuangan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->select( 'ru.namaruangan as namaruangan')
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.noregistrasi', $noRegister)
            ->first();
        $pelayanan=[];
        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
//            ->leftjoin('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
//            ->leftjoin('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
//            ->leftjoin('kelompokprodukbpjs_m as kpBpjs', 'kpBpjs.id', '=', 'pr.objectkelompokprodukbpjsfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//            ->join('ruangan_m as ru2', 'ru2.id', '=', 'pd.objectruanganlastfk')
//            ->join('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
//            ->leftjoin('agama_m as ag', 'ag.id', '=', 'pas.objectagamafk')
//            ->leftjoin('jeniskelamin_m as jkel', 'jkel.id', '=', 'pas.objectjeniskelaminfk')
//            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
//            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
//            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'pd.objectkelasfk')
//            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
//            ->leftjoin('pegawai_m as pgpj', 'pgpj.id', '=', 'pd.objectpegawaifk')
//            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
//            ->leftjoin('strukresep_t as sr', 'sr.norec', '=', 'pp.strukresepfk')
//            ->leftjoin('ruangan_m as rusr', 'rusr.id', '=', 'sr.ruanganfk')
//            ->leftjoin('kamar_m as kamar', 'kamar.id', '=', 'apd.objectkamarfk')
//            ->leftjoin('pegawai_m as pgsr', 'pgsr.id', '=', 'sr.penulisresepfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
//            ->leftjoin('strukpelayananpenjamin_t as sppj', 'sp.norec', '=', 'sppj.nostrukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
//            ->leftjoin('pegawai_m as pgsbm', 'pgsbm.id', '=', 'sbm.objectpegawaipenerimafk')

            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk',
                'pd.nostruklastfk','pd.noregistrasi',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang',
                'pd.objectrekananfk as rekananid',
                'pp.jasa',  'sp.totalharusdibayar', 'sp.totalprekanan',
                'sp.totalbiayatambahan', 'pd.kdprofile','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis','pp.iskronis'
            )
//            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
//                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
//                'pg.id as pgid', 'pg.namalengkap', 'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk', 'ru2.objectdepartemenfk as deptid',
//                'pd.nocmfk', 'pd.nostruklastfk', 'ag.id as agid', 'ag.agama', 'pas.tgllahir',
//                'kp.id as kpid', 'kp.kelompokpasien', 'pas.objectstatusperkawinanfk', 'pas.namaayah', 'pas.namasuamiistri',
//                'pas.id as pasid', 'pas.nocm', 'jkel.id as jkelid', 'jkel.jeniskelamin', 'jkel.reportdisplay as jk', 'pd.noregistrasi', 'pas.namapasien',
//                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang', 'pas.notelepon', 'kls.id as klsid', 'kls.namakelas',
//                'pd.objectrekananfk as rekananid', 'ru2.namaruangan as ruanganlast', 'kls2.id as klsid2', 'kls2.namakelas as namakelas2',
//                'sr.noresep', 'rk.namarekanan', 'rusr.namaruangan as ruanganfarmasi', 'pgsr.namalengkap as penulisresep', 'jp.jenisproduk', 'kpBpjs.kelompokprodukbpjs as kelompokprodukbpjs',
//                'pgpj.namalengkap as dokterpj', 'pp.jasa', 'kamar.namakamar', 'sp.totalharusdibayar', 'sp.totalprekanan', 'sppj.totalppenjamin',
//                'sp.totalbiayatambahan', 'pgsbm.namalengkap as namalengkapsbm','pd.kdprofile','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis'
//            )
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.noregistrasi', $noRegister)
            ->orderBy('pp.tglpelayanan', 'pp.rke');

        if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
            $pelayanan = $pelayanan->where('apd.objectruanganfk','=', $request['idruangan']);
        }
//        if ($request['jenisdata'] == 'resep'){
//            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
//                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
//            };
//            $pelayanan = $pelayanan->whereNotNull('pp.aturanpakai');
//        }
//        if ($request['jenisdata'] == 'layanan'){
//            if(isset($request['idruangan']) && $request['idruangan']!="" && $request['idruangan']!="undefined"){
//                $pelayanan = $pelayanan->where('apd.objectruanganfk',$request['idruangan']);
//            };
//            $pelayanan = $pelayanan->whereNull('pp.aturanpakai');
//        }
        $pelayanan = $pelayanan->get();
        $dataTotaldibayar = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                where  pd.noregistrasi=:noregistrasi and sp.nosbmlastfk is not null and pp.produkfk not in (402611);
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $dibayar = 0;
        $dibayar = $dataTotaldibayar[0]->total;

        if (count($pelayanan) > 0) {

            $totalBilling = 0;
            $norecAPD = '';
            $norecSP = '';
            $details = array();
//            $dibayar=0;
            $diverif=0;
            foreach ($pelayanan as $value) {
                if ($value->produkfk == $this->getProdukIdDeposit()) {
                    continue;
                }
                if ($value->namaproduk == null){
                    continue;
                }
                $jasa = 0;
                if (isset($value->jasa) && $value->jasa != "" && $value->jasa != "undefined") {
                    $jasa = $value->jasa;
                }
                $kmpn = [];

                $harga = (float)$value->hargajual;
                $diskon = (float)$value->hargadiscount;
                $detail = array(
                    'norec' => $value->norec,
                    'tglPelayanan' => $value->tglpelayanan,
                    'namaPelayanan' => $value->namaproduk,
//                    'dokter' => $NamaDokter,
                    'jumlah' => $value->jumlah,
                    'kelasTindakan' => @$value->namakelas,
                    'ruanganTindakan' => @$value->namaruangan,
                    'harga' => $harga,
                    'diskon' => $diskon,
                    'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                    'jppid' => '',
                    'jenispetugaspe' => '',
                    'strukfk' => $value->nostruk . ' / ' . $value->nosbm,
                    'sbmfk' => $value->nosbm,
                    'pgid' => '',
                    'ruid' => $value->ruid,
                    'prid' => $value->prid,
                    'klid' => $value->klid,
                    'norec_apd' => $value->norec_apd,
                    'norec_pd' => $value->norec_pd,
                    'norec_sp' => $value->norec_sp,
                    'komponen' => $kmpn,
                    'jasa' => $jasa,
                    'aturanpakai' => $value->aturanpakai,
                    'iscito' => $value->iscito,
                    'isparamedis' => $value->isparamedis,
                    'iskronis' => $value->iskronis,
                    'totaldibayar' => $dibayar
                );

                $details[] = $detail;


            }
        }

        $arrHsil = array(
            'details' => $details
        );
        return $this->respond($arrHsil);
    }

    public function getPenjaminByKelompokPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('mapkelompokpasientopenjamin_m as mkp')
            ->join ('kelompokpasien_m as kp','kp.id','=','mkp.objectkelompokpasienfk')
            ->join ('rekanan_m as rk','rk.id','=','mkp.kdpenjaminpasien')
            ->select('rk.id','rk.namarekanan','kp.id as id_kelompokpasien','kp.kelompokpasien')
            ->where('mkp.objectkelompokpasienfk', $request['kdKelompokPasien'])
            ->where('mkp.kdprofile', $idProfile)
            ->where('mkp.statusenabled',true)
            ->get();

        $result = array(
            'rekanan'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function daftarPiutangPasien(Request $request){
        $filter=$request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        $dataPiutang=[];
        $dataPiutang= \DB::table('strukpelayanan_t as sp')
            ->leftjoin('strukpelayananpenjamin_t as spp', 'sp.norec', '=', 'spp.nostrukfk')
            ->join('pelayananpasien_t as pp', 'pp.strukfk', '=', 'sp.norec')
            ->join('antrianpasiendiperiksa_t as ap', 'ap.norec', '=', 'pp.noregistrasifk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'ap.noregistrasifk')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'sp.noregistrasifk')
            ->leftjoin('bpjsklaimtxt_t as bpjs', 'bpjs.sep', '=', 'pa.nosep')
            ->leftjoin('rekanan_m as rkn', 'rkn.id', '=', 'pd.objectrekananfk')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('rekanan_m as r', 'r.id', '=', 'spp.kdrekananpenjamin')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('postinghutangpiutang_t as php', 'php.nostrukfk', '=', 'spp.norec')
            ->leftJoin('strukposting_t as spt', 'spt.noposting', '=', 'php.noposting')
            ->select('kp.kelompokpasien', 'spp.norec','sp.tglstruk', 'pd.noregistrasi', 'pd.tglregistrasi','p.nocm',
                'p.namapasien','spp.totalppenjamin','spp.totalharusdibayar','bpjs.tarif_inacbg as tarifklaim',
                'spp.totalsudahdibayar', 'r.namarekanan', 'spp.totalbiaya', 'spp.noverifikasi','rkn.namarekanan','pd.norec as norec_pd',
                'pd.tglpulang','php.noposting','spt.statusenabled')
            ->where('sp.statusenabled', true)
            ->where('sp.kdprofile',$idProfile);
//            ->where('php.statusenabled',1);
//            ->where('spp.kdrekananpenjamin', 0);


        if(isset($filter['tglAwal']) && $filter['tglAwal']!=""){
            $dataPiutang = $dataPiutang->where('pd.tglpulang','>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!=""){
            $tgl= $filter['tglAkhir']." 23:59:59";
            $dataPiutang = $dataPiutang->where('pd.tglpulang','<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId']!=""){
            $dataPiutang = $dataPiutang->where('dept.id','=', $filter['instalasiId']);
        }

        if(isset($filter['ruanganId']) && $filter['ruanganId']!=""){
            $dataPiutang = $dataPiutang->where('ru.id','=', $filter['ruanganId']);
        }

        if(isset($filter['kelompokpasienlastfk']) && $filter['kelompokpasienlastfk']!=""){
            $dataPiutang = $dataPiutang->where('pd.objectkelompokpasienlastfk','=', $filter['kelompokpasienlastfk']);
        }


        if(isset($filter['namaPasien']) && $filter['namaPasien']!=""){
            $dataPiutang = $dataPiutang->where('p.namapasien','ilike', '%'.$filter['namaPasien'].'%');
        }

        if(isset($filter['noReg']) && $filter['noReg']!=""){
            $dataPiutang = $dataPiutang->where('pd.noregistrasi','=',$filter['noReg']);
        }

        if(isset($filter['status']) && $filter['status']!=""){
            if($filter['status']=='Verifikasi'){
                $dataPiutang = $dataPiutang->whereNotNull('spp.noverifikasi');
            }
            if($filter['status']=='Belum Verifikasi'){
                $dataPiutang = $dataPiutang->whereNull('spp.noverifikasi');
            }
        }

        $dataPiutang=$dataPiutang->groupBy('kp.kelompokpasien', 'spp.norec','sp.tglstruk', 'pd.noregistrasi', 'pd.tglregistrasi','p.nocm','p.namapasien','spp.totalppenjamin','spp.totalharusdibayar',
            'spp.totalsudahdibayar', 'r.namarekanan', 'spp.totalbiaya', 'spp.noverifikasi','rkn.namarekanan','pd.norec','pd.tglpulang','php.noposting',
            'spt.statusenabled','bpjs.tarif_inacbg');
        $dataPiutang =$dataPiutang->orderBy('p.namapasien');
//        $dataPiutang =$dataPiutang->take(50);
        $dataPiutang =$dataPiutang->get();
//        dd($strukPelayanan->items());
//        $data = $strukPelayanan->items();
//        $result[] = array() ;
        $result = [];
        foreach ($dataPiutang as $item) {
            if ($item->statusenabled ==  1 || is_null($item->statusenabled)) {
                $statusVerifikasi = "Belum Diverifikasi";
                $isVerified = false;
                if ($item->noverifikasi != null) {
                    $statusVerifikasi = "Verifikasi";
                    $isVerified = true;
                }
                if ($item->tarifklaim == null){
                    $tarifklaim = 0;
                    $selisihKlaim = 0;
                }else{
                    $tarifklaim = (float)$item->tarifklaim;
                    $selisihKlaim = (float)$item->tarifklaim - (float)$item->totalppenjamin;
                }
                $detailss = DB::select(DB::raw("SELECT spd.norec,sp.nostrukfk,spd.totalppenjamin,kl.kelompokpasien,rek.namarekanan
					 from strukpelayananpenjamindetail_t as spd
					inner join strukpelayananpenjamin_t as sp on sp.norec= spd.strukpelayananpenjaminfk
					inner join kelompokpasien_m as kl on kl.id= spd.kdkelompokpasien
					inner join rekanan_m as rek on rek.id=spd.kdrekananpenjamin
					where spd.kdprofile = $idProfile and spd.strukpelayananpenjaminfk=:norecStruk
	                "),
                    array(
                        'norecStruk' => $item->norec,
                    )
                );
                $result[] = array(
                    'noRec' => $item->norec,
                    'tglTransaksi' => $item->tglstruk,
                    'noRegistrasi' => $item->noregistrasi,
                    'namaPasien' => $item->namapasien,
                    'kelasRawat' => '-',
                    'jenisPasisen' => $item->kelompokpasien,
                    'kelasPenjamin' => "-", //ambilnya dari mana ?
                    'totalBilling' => $item->totalbiaya,
                    'totalKlaim' => $item->totalppenjamin,
                    'totalBayar' => $item->totalsudahdibayar,
                    'statusVerifikasi' => $statusVerifikasi,
                    'rekanan' => $item->namarekanan,
                    'norec_pd' => $item->norec_pd,
                    'tglpulang' => $item->tglpulang,
                    'isVerified' => $isVerified,
                    'noposting' => $item->noposting,
                    'tarifselisihklaim' => $selisihKlaim,
                    'tarifinacbgs' => $tarifklaim,
                    'verifikasi' => $item->noverifikasi,
                    'details' =>$detailss


                );
            }


        }
        $dataKelompokPasien = \DB::table('kelompokpasien_m as rt')
            ->select('rt.id','rt.kelompokpasien')
            ->where('rt.statusenabled',true)
            ->orderBy('rt.kelompokpasien')
            ->get();

        $datadata =array(
            'data' =>   $result,
            'kelompokpasien' =>$dataKelompokPasien,
        );
        return $this->respond($datadata, 'Data Daftar Pasien');
    }

    public function verifyPiutangPasien(Request $request){
        $transMsg=null;
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
        $verifikasi = new StrukVerifikasi();
        $noVerif = $this->generateCode(new StrukVerifikasi, 'noverifikasi', 12,'VP'. $this->getDateTime()->format('dmy'),$idProfile);
        $verifikasi->norec = $verifikasi->generateNewId();
        $verifikasi->kdprofile = $idProfile;
        $verifikasi->objectkelompoktransaksifk = 1; ///ambil dari datafixed pastinya
        $verifikasi->objectpegawaipjawabfk = 1;
        $verifikasi->objectruanganfk = 1; //ambil dari pegawai yang ada ruangankerja
        $verifikasi->namaverifikasi = "Verifikasi Piutang Penjamin";
        $verifikasi->noverifikasi = $noVerif;//$this->generateCode(new StrukVerifikasi, 'noverifikasi', 10, 'VP');
        $verifikasi->tglverifikasi = $this->getDateTime();
        $verifikasi->save();

        foreach ($request['dataPiutang'] as $norec){
            $strukPelayanan = StrukPelayananPenjamin::where('norec', $norec)->first();
            if($strukPelayanan){
                $strukPelayanan->noverifikasi = $verifikasi->noverifikasi;
                $strukPelayanan->save();
            }
        }
        $transStatus = 'true';
        } catch(\Exception $e){
            $transStatus = false;
            $transMsg = "Verifikasi Piutang Gagal";
        }

        //        if($transStatus){
//            DB::commit();
//            $transMsg = "Verifikasi Piutang Berhasil";
//            return $this->setStatusCode(201)->respond([],$transMsg);
//        }else{
//            DB::rollBack();
//            return $this->setStatusCode(400)->respond([],$transMsg);
//        }

        if ($transStatus == 'true') {
            $transMessage = "Verifikasi Piutang Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $verifikasi,
                'as' => 'epic',
            );
        } else {
            $transMessage = "Verifikasi Piutang Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $verifikasi,
                'as' => 'epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function cancelVerifyPiutangPasien(Request $request){
        $transMsg=null;
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
        if(count($request['dataPiutang'])>0){
            foreach ($request['dataPiutang'] as $norec) {
                $strukPelayanan = StrukPelayananPenjamin::where('norec', $norec)->where('kdprofile', $idProfile)->first();
                if($strukPelayanan){
                    $strukPelayanan->noverifikasi = null;
//                    $strukPelayanan->nostrukfk = null;
                    $strukPelayanan->save();
                }
            }
        }
        else{
            $transStatus = false;
            $transMsg = "Transaksi Gagal (0)";
        }

            $transStatus = 'true';
        } catch(\Exception $e){
            $transStatus = false;
            $transMsg = "Unverifikasi Piutang Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Unverifikasi Piutang Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'result' => $strukPelayanan,
                'as' => 'epic',
            );
        } else {
            $transMessage = "Unverifikasi Piutang Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'result' => $strukPelayanan,
                'as' => 'epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getNorecAPD(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
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
            $ruangId = " AND ru.namaruangan ilike '%"  . $filter['namaRuangan']."%'";
        }
        $data = DB::select(DB::raw("select * from
                (select pd.tglregistrasi,pd.noregistrasi, ru.namaruangan,
                 pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, 
                 row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum 
                 from antrianpasiendiperiksa_t as apd
                 inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk and pd.objectruanganlastfk = apd.objectruanganfk
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                 inner join ruangan_m as ru on ru.id = apd.objectruanganfk
                 where apd.kdprofile = $idProfile and br.norec is null 
                and pd.tglpulang is null --and pd.noregistrasi='1808010084'
                $ruangId $noreg  $namaRuangan
              ) as x where x.rownum=1")
        );
        return $this->respond($data);
    }

    public function simpanPemakaianAsuransi(Request $request){
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            if($request['norec_pa'] != ''){
                $dataPA = PemakaianAsuransi::where('noregistrasifk', $request['norec'])
                    ->update([
                        'nokepesertaan' => $request['nokepesertaan'],
                        'nosep' => $request['nosep']
                    ]);
                $transMessage = "Update Pemakaian Asuransi berhasil!";
            }else{
                $pasienDaftar= PasienDaftar::where('norec',$request['norec'])->first();
                $pasien = Pasien::where('nocm',$request['nocm'])->first();
                $alamat = Alamat::where('nocmfk',$pasien->id)->first();
                $AsuransiPAsien = AsuransiPasien::where('nocmfk', $pasien->id)->first();
                if (empty($AsuransiPAsien)){
                    $newId = AsuransiPasien::max('id');
                    $newId = $newId + 1;
                    $dataAP = new AsuransiPasien();
                    $dataAP->id = $newId;
                    $dataAP->kdprofile = $idProfile;
                    $dataAP->statusenabled = true;
                    $dataAP->norec = $dataAP->generateNewId();
                    $dataAP->alamatlengkap = $alamat->alamatlengkap;
                    $dataAP->objecthubunganpesertafk = 1;//PESERTA
                    $dataAP->objectjeniskelaminfk = $pasien->objectjeniskelaminfk;
                    $dataAP->kdinstitusiasal =2552;//BPJS KESEHATAN
                    $dataAP->notelpmobile =$pasien->notelepon;
//                    $dataAP->jenispeserta = $request['asuransipasien']['jenispeserta'];
                    $dataAP->kdprovider ='-';
                    $dataAP->nmprovider = '-';
                    $dataAP->kdpenjaminpasien = 2552;
                    $kelasdijamin=$pasienDaftar->objectkelasfk;
                    if ($pasienDaftar->objectkelasfk != 1 || $pasienDaftar->objectkelasfk != 2 ||$pasienDaftar->objectkelasfk != 3 ){
                        $kelasdijamin = 1;
                    }
                    $dataAP->objectkelasdijaminfk = $kelasdijamin;
                    $dataAP->namapeserta =$pasien->namapasien;
                    $dataAP->nikinstitusiasal = 2552;
                    $dataAP->noasuransi =  $request['nokepesertaan'];
                    $dataAP->nocmfk =$pasien->id;
                    $dataAP->noidentitas =$pasien->noidentitas;
//                    $dataAP->qasuransi = $request['asuransipasien']['qasuransi'];
                    $dataAP->tgllahir = $pasien->tgllahir;
                    $dataAP->save();
                    $idAP=$dataAP->id;
                }else{
                    $idAP= $AsuransiPAsien->id;
                }

                $dataPA = new PemakaianAsuransi();
                $dataPA->norec = $dataPA->generateNewId();;
                $dataPA->kdprofile = $idProfile;
                $dataPA->statusenabled = true;
                $dataPA->noregistrasifk = $request['norec'];
                $dataPA->tglregistrasi = Carbon::now();
//                $dataPA->diagnosisfk = $request['pemakaianasuransi']['diagnosisfk'];
//                $dataPA->lakalantas = $request['pemakaianasuransi']['lakalantas'];
                $dataPA->nokepesertaan = $request['nokepesertaan'];
//                $dataPA->norujukan = $request['pemakaianasuransi']['norujukan'];
                $dataPA->nosep = $request['nosep'];
//                $dataPA->ppkrujukan = $request['asuransipasien']['kdprovider'];
//                $dataPA->tglrujukan = $request['pemakaianasuransi']['tglrujukan'];
                $dataPA->objectasuransipasienfk = $idAP;
//                $dataPA->objectdiagnosafk = $request['pemakaianasuransi']['objectdiagnosafk'];
                $dataPA->tanggalsep = Carbon::now();
                $dataPA->catatan ='-';
//                $dataPA->lokasilakalantas =$request['pemakaianasuransi']['lokasilaka'];
//                $dataPA->penjaminlaka =$request['pemakaianasuransi']['penjaminlaka'];

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
                $transMessage = "Save Pemakaian Asuransi berhasil!";
            }
            $transStatus = 'true';

        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Pemakaian Asuransi gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "res"=> $dataPA,
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

    public function getAntrianPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
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
             where apd.kdprofile = $idProfile and pd.noregistrasi = '$noreg' and apd.objectruanganfk = '$ruanganLast'"));
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
                ->where('pd.kdprofile', $idProfile)
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

    public function getDaftarDepositPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data= \DB::table('pasiendaftar_t as pd')
            ->join ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->leftJoin('strukpelayanan_t as sp','sp.norec','=','pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin ('produk_m as prd','prd.id','=','pp.produkfk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->select('pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien','sp.nostruk', 'sbm.nosbm','pg.id as pgid','pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pp.produkfk','apd.norec',
                DB::raw('sum(pp.hargasatuan) as totaldeposit'))
            ->where('pd.kdprofile',$idProfile);


        $filter = $request->all();
        if(isset($filter['tglAwal']) && $filter['tglAwal']!="" && $filter['tglAwal']!="undefined"){
            $data = $data->where('pd.tglregistrasi','>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!="" && $filter['tglAkhir']!="undefined"){
            $tgl= $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi','<=', $tgl);
        }
        if(isset($filter['deptId']) && $filter['deptId']!="" && $filter['deptId']!="undefined"){
            $data = $data->where('dept.id','=', $filter['deptId']);
        }
        if(isset($filter['ruangId']) && $filter['ruangId']!="" && $filter['ruangId']!="undefined"){
            $data = $data->where('ru.id','=', $filter['ruangId']);
        }
        if(isset($filter['kelId']) && $filter['kelId']!="" && $filter['kelId']!="undefined"){
            $data = $data->where('kp.id','=', $filter['kelId']);
        }
        if(isset($filter['dokId']) && $filter['dokId']!="" && $filter['dokId']!="undefined"){
            $data = $data->where('pg.id','=', $filter['dokId']);
        }
        if(isset($filter['sttts']) && $filter['sttts']!="" && $filter['sttts']!="undefined"){
            $data = $data->where('pd.statuspasien','=', $filter['sttts']);
        }

        if(isset($filter['noreg']) && $filter['noreg']!="" && $filter['noreg']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $filter['noreg'].'%');
        }
        if(isset($filter['norm']) && $filter['norm']!="" && $filter['norm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $filter['norm']. '%');
        }
        if(isset($filter['nama']) && $filter['nama']!="" && $filter['nama']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'. $filter['nama'] .'%');
        }

        $data=$data->where('prd.id',402611);
//        $data=$data->whereNotNull('sbm.nosbm');
        $data=$data->groupBy('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan',
            'ps.namapasien', 'kp.kelompokpasien', 'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk',
            'sbm.nosbm', 'pg.id', 'pg.namalengkap', 'pgs.namalengkap' ,'pp.produkfk','apd.norec');
        $data=$data->orderBy('pd.noregistrasi');
        $data=$data->get();

        $result=[];
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                    select pp.tglpelayanan,pr.namaproduk,pp.hargadiscount,
                    pp.hargajual,pp.harganetto,pp.jumlah,pp.hargasatuan
                    from pelayananpasien_t as pp 
                    inner JOIN produk_m as pr on pr.id=pp.produkfk
                    where pp.kdprofile = $idProfile and pp.noregistrasifk=:norec
                    and pr.id=402611"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tglregistrasi' => $item->tglregistrasi,
                'nocm' => $item->nocm,
                'noregistrasi' => $item->noregistrasi,
                'namaruangan' => $item->namaruangan,
                'namapasien' => $item->namapasien,
                'kelompokpasien' => $item->kelompokpasien,
                'tglpulang' => $item->tglpulang,
                'statuspasien' => $item->statuspasien,
                'nostruk' => $item->nostruk,
                'nosbm' => $item->nosbm,
                'pgid' => $item->pgid,
                'namadokter' => $item->namadokter,
                'kasir' => $item->kasir,
                'produkfk' => $item->produkfk,
                'norec' => $item->norec,
                'totaldeposit' => $item->totaldeposit,
                'details' => $details,
            );
        }
        $result = array(
            'daftar' => $result,
            'datalogin' => $dataLogin,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getLapPasienDalamPerawatan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        ini_set('max_execution_time', 1000); //6 minutes
        $dataLogin = $request->all();
//        $tglAwal = $request['tglAwal'];
//        $tglAkhir = $request['tglAkhir'];
        $paramIdRuang = '';
        $paramKelompokPasien = '';
        $paramNoregistrasi='';
        $paramNoRM ='';
        $paramPasien ='';
        if (isset($request['ruangId']) && $request['ruangId'] != "" && $request['ruangId'] != "undefined") {
            $paramIdRuang = ' and ru.id='.$request['ruangId'] ;
        }
        if (isset($request['kelId']) && $request['kelId'] != "" && $request['kelId'] != "undefined") {
            $paramKelompokPasien = ' and kp.id='.$request['kelId'];
        }

        if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
            $paramNoregistrasi =' and pd.noregistrasi ='.$request['noregistrasi'] ;
        }
        if (isset($request['nocm']) && $request['nocm'] != "" && $request['nocm'] != "undefined") {
            $paramNoRM = " and ps.nocm ilike '%".$request['nocm']."%'";
        }
        if (isset($request['namapasien']) && $request['namapasien'] != "" && $request['namapasien'] != "undefined") {
            $paramPasien = " and ps.namapasien ilike '%".$request['namapasien']."%'";
        }
        $data = DB::select(DB::raw("
      
          SELECT 
                -- DATEDIFF(day, pd.tglregistrasi,GETDATE()) AS hari,
                EXTRACT(day from age(now(), pd.tglregistrasi)) as hari,
                pd.tglregistrasi,pd.noregistrasi,ps.nocm,ps.namapasien,ru.namaruangan,kp.kelompokpasien,kls.namakelas,ps.tgllahir,
                sum((pp.hargasatuan * pp.jumlah) + case when pp.jasa is not null then pp.jasa else 0 end )as total,
                sUM(CASE WHEN (pp.produkfk = 402611) THEN pp.hargasatuan ELSE (0) END * pp.jumlah) AS deposit, 
                SUM(CASE WHEN (pp.hargadiscount IS NULL) THEN (0) ELSE pp.hargadiscount END) AS diskon,
                 sum((((pp.hargasatuan - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is not null then pp.jasa else 0 end ) 
                 -(CASE WHEN (pp.produkfk = 402611) THEN pp.hargasatuan ELSE (0) END * pp.jumlah)) as totalkabeh
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                inner join ruangan_m as ru on ru.id= pd.objectruanganlastfk
                inner join kelas_m as kls on kls.id= pd.objectkelasfk
                inner join kelompokpasien_m as kp on kp.id= pd.objectkelompokpasienlastfk
                inner join pasien_m as ps on ps.id= pd.nocmfk
                LEFT JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                WHERE
                pd.statusenabled = true and
                pd.kdprofile = $idProfile and
                pd.tglpulang is null
                $paramIdRuang
                $paramKelompokPasien
                $paramNoregistrasi
                $paramNoRM
                $paramPasien
                GROUP BY pd.tglregistrasi,pd.noregistrasi,ps.nocm,kls.namakelas,ps.tgllahir,
                ps.namapasien,ru.namaruangan,kp.kelompokpasien
                order by pd.tglregistrasi"));
//            $data = \DB::table('v_pasiendalamperawatan as v');


        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getPasienBynorecpdapd(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $norec_pd = $request['norec_pd'];
        $norec_apd = $request['norec_apd'];
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
            ->where('pd.kdprofile',$idProfile)
            ->get();
        return $this->respond($data);

    }

    public function getDataComboOrder(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataruangan  = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.kdprofile',$idProfile)
            ->wherein('ru.objectdepartemenfk',array(3,27))
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as dp')
            ->select('dp.id','dp.namalengkap')
            ->where('ru.kdprofile',$idProfile)
            ->whereIn('dp.objectjenispegawaifk',array(1))
            ->where('dp.statusenabled',true)
            ->orderBy('dp.namalengkap')
            ->get();

        $result = array(
            'ruangantujuan' => $dataruangan,
            'dokter' => $dataDokter,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function getTindakanParts(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //TODO : GET LIST TINDAKAN
        $req = $request->all();
        $data = \DB::table('mapruangantoproduk_m as mpr')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
//            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
//            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
//            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
//                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
            ->select('mpr.objectprodukfk as id','prd.namaproduk',
                'mpr.objectruanganfk',
                'prd.namaproduk'
            )
            ->where('mpr.kdprofile',$idProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
//            ->wherein('ru.objectdepartemenfk',[27])
//            ->where('hnp.objectkelasfk',$request['idKelas'])
//           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('sk.statusenabled',true)
            ->where('mpr.statusenabled',true)

            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
            ->where('mpr.kdprofile', (int)$kdProfile)
            // ->where('mpr.kodeexternal','2017')
            // ->where('hnp.kodeexternal', '2017')
        ;



        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('prd.namaproduk','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }
        $data = $data->orderBy('prd.namaproduk', 'ASC');
        $data = $data->take(15);
        $data = $data->get();
        // $result = array(
        //     'data' => $data,
        //     'message' => 'ramdanegie',
        // );


        return $this->respond($data);
    }

    public function getDataLaporanPendapatanPerkelas(Request $request){
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t AS pd')
            ->LEFTJOIN('antrianpasiendiperiksa_t AS apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->LEFTJOIN('pelayananpasien_t AS pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->JOIN('produk_m AS pro', 'pro.id', '=', 'pp.produkfk')
            ->LEFTJOIN('kelas_m AS kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->LEFTJOIN('detailjenisproduk_m AS djp', 'djp.id', '=', 'pro.objectdetailjenisprodukfk')
            ->LEFTJOIN('jenisproduk_m AS jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->LEFTJOIN('kelompokproduk_m AS kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->LEFTJOIN('ruangan_m AS ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->LEFTJOIN('departemen_m AS dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->JOIN('pasien_m as ps', 'pd.nocmfk', '=', 'ps.id')
            ->select('ps.nocm', 'ps.namapasien',
                'pd.noregistrasi',
                'ru.namaruangan', 'kls.namakelas',
                'pro.namaproduk',
                'pp.hargadiscount',
                'kls.namakelas',
                DB::raw('case when djp.id in (58,149,155,161,167,476,477,1435,1440,1539) then sum( pp.jumlah * pp.hargajual) else 0 end as akomodasi,
                        case when djp.id in (58,149,155,161,167,476,477,1435,1440,1539) then sum(pp.jumlah) else 0 end as volakomodasi,
                        case when djp.id=1540 then sum( pp.jumlah * pp.hargajual)else 0 end as visit,
                        case when djp.id=1540 then sum(pp.jumlah)else 0 end as volvisit,
                        case when djp.id in (1408,1462) then sum( pp.jumlah * pp.hargajual)else 0 end as sewaalat,
                        case when djp.id in (1408,1462) then sum( pp.jumlah)else 0 end as volsewaalat,
                        case when djp.id not in (58,149,155,161,167,476,477,1435,1440,1539,1540,1408,1462,1522) then sum( pp.jumlah * pp.hargajual) else 0 end AS tindakan,
                        case when djp.id not in (58,149,155,161,167,476,477,1435,1440,1539,1540,1408,1462,1522) then sum( pp.jumlah) else 0 end AS voltindakan,
                        case when djp.id =1522 then sum( pp.jumlah * pp.hargajual) else 0 end AS konsultasi,
                        case when djp.id =1522 then sum( pp.jumlah) else 0 end AS volkonsultasi,
                        ((case when djp.id in (58,149,155,161,167,476,477,1435,1440,1539) then sum( pp.jumlah * pp.hargajual) else 0 end)       
                        +(case when djp.id=1540 then sum( pp.jumlah * pp.hargajual)else 0 end) 
                        +(case when djp.id in (1408,1462) then sum( pp.jumlah * pp.hargajual)else 0 end )
                         +(case when djp.id not in (58,149,155,161,167,476,477,1435,1440,1539,1540,1408,1462,1522) then sum( pp.jumlah * pp.hargajual) else 0 end)
                          + case when djp.id =1522 then sum( pp.jumlah * pp.hargajual) else 0 end ) * 0.05 as adm,
                         ((case when djp.id in (58,149,155,161,167,476,477,1435,1440,1539) then sum( pp.jumlah * pp.hargajual) else 0 end)       
                        +(case when djp.id=1540 then sum( pp.jumlah * pp.hargajual)else 0 end) 
                        +(case when djp.id in (1408,1462) then sum( pp.jumlah * pp.hargajual)else 0 end )
                         +(case when djp.id not in (58,149,155,161,167,476,477,1435,1440,1539,1540,1408,1462,1522) then sum( pp.jumlah * pp.hargajual) else 0 end)
                          + (case when djp.id =1522 then sum( pp.jumlah * pp.hargajual) else 0 end))
                          +((case when djp.id in (58,149,155,161,167,476,477,1435,1440,1539) then sum( pp.jumlah * pp.hargajual) else 0 end)       
                        +(case when djp.id=1540 then sum( pp.jumlah * pp.hargajual)else 0 end) 
                        +(case when djp.id in (1408,1462) then sum( pp.jumlah * pp.hargajual)else 0 end )
                         +(case when djp.id not in (58,149,155,161,167,476,477,1435,1440,1539,1540,1408,1462,1522) then sum( pp.jumlah * pp.hargajual) else 0 end)
                          +  (case when djp.id =1522 then sum( pp.jumlah * pp.hargajual) else 0 end))* 0.05 as total
                       ')
            )
            ->where('pd.kdprofile',$idProfile);
//            ->whereIn('jp.id', [25, 99, 101, 102, 27666])
//            ->Where('djp.objectjenisprodukfk', '<>', 97)
//            ->whereNotIn('pro.id',[10011572,10011571,402611]);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['kelompokPasien']);
        }
        if (isset($request['kelas']) && $request['kelas'] != "" && $request['kelas'] != "undefined") {
            $data = $data->where('kls.id', '=', $request['kelas']);
        }
        $data = $data->groupBy('jp.id', 'ps.nocm', 'ps.namapasien',
            'pd.noregistrasi', 'pp.hargadiscount',
            'ru.namaruangan', 'kls.namakelas', 'kls.namakelas',
            'pro.namaproduk', 'djp.id');

        $data = $data->orderBy('pd.noregistrasi', 'ASC');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'egie@glory',
        );
        return $this->respond($result);
    }

    public function getDataLaporanVolumeKegiatan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = $request['ruanganId'];
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and pd.objectruanganlastfk = ' . $ruanganId;
        }

        $deptRawatJalan = $this->settingDataFixed('kdDepartemenRawatJalanFix', $idProfile);

        $dataLogin = $request->all();
                    $data = DB::select(DB::raw("select pd.noregistrasi,kmp.namaexternal,ru.namaruangan,kls.namakelas as namakelas,pro.namaproduk, 
                                       case when djp.id in (58,476,477,1435,1440,1539) then 'Akomodasi' 
                                       when djp.id in (481,1540) then 'Visit' 
                                       when djp.id not in (58,476,477,1435,1440,1539,481,1540) then 'Tindakan' end as jenisproduk,
                                       CAST(pp.hargajual as INTEGER),pp.jumlah,CAST(pp.hargajual as INTEGER)*CAST(pp.jumlah as INTEGER) as total 
                            from pasiendaftar_t as pd 
                            left join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec 
                            left join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec 
                            inner join produk_m as pro on pro.id = pp.produkfk 
                            left join kelas_m as kls on kls.id = apd.objectkelasfk 
                            left join detailjenisproduk_m as djp on djp.id = pro.objectdetailjenisprodukfk 
                            left join jenisproduk_m as jp on jp.id = djp.objectjenisprodukfk 
                            left join kelompokproduk_m as kp on kp.id = jp.objectkelompokprodukfk 
                            left join ruangan_m as ru on ru.id = apd.objectruanganfk
                            left join departemen_m as dp on dp.id = ru.objectdepartemenfk 
                            INNER JOIN kelompokpasien_m as kmp on kmp.id = pd.objectkelompokpasienlastfk 
                            where pd.kdprofile = $idProfile and pp.strukresepfk is NULL            
                            AND pp.tglpelayanan >= '$tglAwal' AND pp.tglpelayanan <= '$tglAkhir'
                            AND ru.objectdepartemenfk in ($deptRawatJalan)
                            $paramRuangan "));

//        $data = \DB::table('pasiendaftar_t as pd')
////           ->JOIN('strukpelayanandetail_t as spd','spd.nostrukfk','=','sp.norec')
//            ->LEFTJOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
//            ->LEFTJOIN('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
//            ->JOIN('produk_m as pro', 'pro.id', '=', 'pp.produkfk')
//            ->LEFTJOIN('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
//            ->LEFTJOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pro.objectdetailjenisprodukfk')
//            ->LEFTJOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
//            ->LEFTJOIN('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
//            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//            ->LEFTJOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
//            ->select('pd.noregistrasi', 'ru.namaruangan', 'kls.namakelas as namakelas',
//                'pro.namaproduk',
//                DB::raw('case when jp.id in (99,25) then \'Akomodasi\' when jp.id=101 then \'Visit\' when jp.id =102 then \'Tindakan\' end as jenisproduk,
//                     pp.hargajual, pp.jumlah, pp.hargajual*pp.jumlah as total')
//            )
//            ->where('djp.objectjenisprodukfk', '<>', '97')
////          ->where('dp.id','=','14')
//            ->wherein('jp.id', ['25', '99', '101', '102']);

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
//        }
//        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
//            $data = $data->where('dp.id', '=', $request['idDept']);
//        }

//        $data = $data->orderBy('pd.noregistrasi');
//
//        $data = $data->get();
//
////        $data=$data->groupby ('sp.totalharusdibayar','sp.totalprekanan','kp.kelompokpasien','spp.norec',
////            'stp.tglposting','pd.noregistrasi','pd.tglregistrasi','p.nocm','p.namapasien',
////            'ru.namaruangan','pr.id','pp.hargajual','pp.jumlah','pp.hargadiscount','kpr.id',
////            'pr.objectdetailjenisprodukfk', 'spp.totalppenjamin','spp.totalharusdibayar',
////            'spp.totalsudahdibayar','r.namarekanan','spp.totalbiaya','spp.noverifikasi',
////            'php.noposting','stp.kdhistorylogins');
//
//
//        $result = array(
//            'data' => $data,
//            'message' => '@vandrian',
//        );

//        foreach ($data as $item){
//            $details = DB::select(DB::raw("
//                    select spd.tglpelayanan, spd.resepke,jkm.jeniskemasan,pr.namaproduk,
//                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,
//                    spd.hargatambahan,((spd.hargasatuan-spd.hargadiscount)*spd.qtyproduk)+spd.hargatambahan as total
//                    from strukpelayanandetail_t as spd
//                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
//                    left JOIN jeniskemasan_m as jkm on jkm.id=spd.objectjeniskemasanfk
//                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
//                    where nostrukfk=:norec"),
//                array(
//                    'norec' => $item->norec,
//                )
//            );
//            $result[] = array(
//                'tglstruk' => $item->tglstruk,
//                'nostruk' => $item->nostruk,
//                'nostruk_intern' => $item->nostruk_intern,
//                'namapasien_klien' => $item->namapasien_klien,
//                'namalengkap' => $item->namalengkap,
//                'norec' => $item->norec,
//                'namaruangan' => $item->namaruangan,
//                'noteleponfaks' => $item->noteleponfaks,
//                'namatempattujuan' => $item->namatempattujuan,
//                'nosbm' => $item->nosbm,
//                'details' => $details,
//            );
//        }
//
//        $result = array(
//            'daftar' => $result,
//            'datalogin' => $dataLogin,
//            'message' => 'as@epic',
//        );
        return $this->respond($data);
    }

    public function getLaporanRehab(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //$lastdate = DB::select(DB::raw("Select DATEADD(day, -1, DATEADD(month, +1, ".$request['tanggal']."))"));

        //        $lastdate = \DB::table('kalender_s')
//            ->select(DB::raw("(date_trunc('month', tanggal::date) + interval '1 month' - interval '1 day')::date ||' 23:59' as tgl"))
//            ->Where('tanggal','=',$request->tanggal)
//            ->get();
        $tglawal = $request['tanggal'];
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftJOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftJOIN ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->leftJOIN ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftJOIN ('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->leftJOIN ('ruangan_m as ru2','ru2.id','=','apd.objectruanganfk')
            ->leftJOIN ('departemen_m as dp2','dp2.id','=','ru2.objectdepartemenfk')
            ->leftJOIN ('produk_m as pr','pr.id','=','pp.produkfk')
            ->leftJOIN ('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->leftJOIN ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftJOIN ('strukpelayanan_t as sp','sp.noregistrasifk','=','pd.norec')
            ->select('kps.kelompokpasien',
                'pr.namaproduk',
                DB::raw('case when pp.hargajual is not null then pp.hargajual else 0 end as harga,
                        sum(pp.jumlah) as jumlah,
                        sum(pp.hargajual*pp.jumlah) as subtotal'))
            ->where('pd.kdprofile', $idProfile)
            ->whereNull('sp.statusenabled')
            ->whereRaw("to_char(pp.tglpelayanan,'yyyy-MM') ='$tglawal'")
            ->Where('pr.objectdepartemenfk','=',28)
            ->Where('ru2.objectdepartemenfk','=',28);

//        if (isset($request['tanggal']) && $request['tanggal'] != "" && $request['tanggal'] != "undefined") {
//            $tgl = $request['tanggal']." 00:00:00";
//            $data = $data->whereraw('rawpp.tglpelayanan', '>=', "'$tgl'");
//        }
//        if (isset($request['tanggal']) && $request['tanggal'] != "" && $request['tanggal'] != "undefined") {
//            $tglAkhir = $request['tanggal']." 23:59:59";
//            $data = $data->where('pp.tglpelayanan', '<=', "DATEADD(day, -1, DATEADD(month, +1, '$tglAkhir'))");
//        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined" && $request['idDept'] && $request['idDept'] == 16) {
            $data = $data->whereIn('dp.id',[16, 28]);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined" && $request['idDept'] && $request['idDept'] == 18) {
            $data = $data->whereIn('dp.id',[18, 28]);
        }
        $data = $data->groupBy('kps.kelompokpasien','pr.namaproduk','pp.hargajual');
        $data = $data->orderBy('kps.kelompokpasien');
        $data = $data->get();

        $results =array();
        foreach ($data as $item) {
            $results[]=array(
                'kelompokpasien'=>$item->kelompokpasien,
                'layanan'=>$item->namaproduk,
                'harga'=>$item->harga,
                'jumlah'=>$item->jumlah,
                'subtotal'=>$item->subtotal,
            );
        }

        $result = array(
            'data' => $results,
            'message' => 'mn@epic',
        );

        return $this->respond($result);
    }

    public function getDataLaporanDiagnosaPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $kdProfile = $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $deptId = $request['idDept'];
        $ruanganId = $request['idRuangan'];
        $idJenisDiagnosa = $request['idJenisDiagnosa'];
        $diagnosaId = $request['idDiagnosa'];
        $paramDep = ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' . $deptId;
        }

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramJenisDiagnosa = ' ';
        if (isset($idJenisDiagnosa) && $idJenisDiagnosa != "" && $idJenisDiagnosa != "undefined") {
            $paramJenisDiagnosa = ' and jd.id = '.$idJenisDiagnosa;
        }

        $paramDiagnosa = ' ';
        if (isset($diagnosaId) && $diagnosaId != "" && $diagnosaId != "undefined") {
            $paramDiagnosa = ' and dm.id = '.$diagnosaId;
        }

        $data = DB::select(DB::raw("select pd.tglregistrasi,pd.tglpulang,pm.nocm,pd.noregistrasi,pm.namapasien,pm.tgllahir,
                jk.reportdisplay as jeniskelamin,case when sk.statuskeluar is null then '-' else sk.statuskeluar end as statuskeluar,
			    case when sp.statuspulang is null then '-' else sp.statuspulang end as statuspulang,ru.namaruangan,
			    (case when pg.namalengkap is not null then pg.namalengkap when pg1.namalengkap is not null then pg1.namalengkap else '-' end) as namadokter,
			    case when jd.jenisdiagnosa is null then '-' else jd.jenisdiagnosa end as jenisdiagnosa,
			    case when dm.namadiagnosa = '-' then '-, ' || ddp.keterangan else dm.kddiagnosa || ', ' || dm.namadiagnosa end as namadiagnosa,
			    case when alm.alamatlengkap is null then '-' else alm.alamatlengkap end as alamatlengkap,pm.notelepon || '/' || pm.nohp as notelepon
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
                LEFT JOIN detaildiagnosapasien_t as ddp on ddp.noregistrasifk = apd.norec
                LEFT JOIN diagnosapasien_t as dp on dp.norec = ddp.objectdiagnosapasienfk
                INNER JOIN pasien_m as pm on pm.id = pd.nocmfk
                LEFT JOIN alamat_m as alm on alm.nocmfk = pm.id
                LEFT JOIN jeniskelamin_m as jk on jk.id = pm.objectjeniskelaminfk
                INNER JOIN diagnosa_m as dm on dm.id = ddp.objectdiagnosafk
                LEFT JOIN jenisdiagnosa_m as jd on jd.id = ddp.objectjenisdiagnosafk
                LEFT JOIN pegawai_m as pg on pg.id = apd.objectpegawaifk
                LEFT JOIN pegawai_m as pg1 on pg1.id = pd.objectpegawaifk
                INNER JOIN ruangan_m as ru on ru.id = apd.objectruanganfk
                LEFT JOIN statuskeluar_m as sk on sk.id = pd.objectstatuskeluarfk
                LEFT JOIN statuspulang_m as sp on sp.id = pd.objectstatuspulangfk
                LEFT JOIN batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                WHERE br.pasiendaftarfk is null and pd.tglregistrasi between '$tglAwal' and '$tglAkhir'
                and pd.kdprofile = $idProfile  
                $paramDep
                $paramRuangan
                $paramJenisDiagnosa
                $paramDiagnosa"));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDepositPasienPulang(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $tglAwal = ' ';
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $tglAwal = $request['tglAwal'];
        }
        $tglAkhir = ' ';
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tglAkhir =  $request['tglAkhir'];
        }
        $deptId= ' ';
        if(isset($filter['deptId']) && $filter['deptId']!="" && $filter['deptId']!="undefined"){
            $deptId = 'and ru.objectdepartemenfk='. $filter['deptId'];
        }
        $ruangId= ' ';
        if(isset($filter['ruangId']) && $filter['ruangId']!="" && $filter['ruangId']!="undefined"){
            $ruangId = 'and ru.id='. $filter['ruangId'];
        }
        $kelId= ' ';
        if(isset($filter['kelId']) && $filter['kelId']!="" && $filter['kelId']!="undefined"){
            $kelId = 'and klp.id='. $filter['kelId'];
        }
        $noreg =' ';
        if(isset($filter['noreg']) && $filter['noreg']!="" && $filter['noreg']!="undefined"){
            $noreg = "and pd.noregistrasi ilike '%".$filter['noreg']."%'";
        }
        $norm =' ';
        if(isset($filter['norm']) && $filter['norm']!="" && $filter['norm']!="undefined"){
            $norm = "and ps.nocm ilike '%".$filter['norm']."%'";
        }
        $nama =' ';
        if(isset($filter['nama']) && $filter['nama']!="" && $filter['nama']!="undefined"){
            $nama = "and ps.namapasien ilike '%".$filter['nama']."%'";
        }

        $data =DB::select(DB::raw("SELECT
            pd.tglregistrasi,
            --(DATEDIFF(DAY, GETDATE(), pd.tglregistrasi)) as hari,
            pd.noregistrasi,	ps.nocm,ps.namapasien,	kl.namakelas,	klp.kelompokpasien,	rk.namarekanan,	ru.namaruangan,
            pd.tglpulang,sbm.nosbm,sp.nostruk,pg.namalengkap as namadokter,
            SUM (
                (
                    CASE WHEN (pp.produkfk = 402611) THEN pp.hargajual
                    ELSE (0)  END * pp.jumlah
                )
            ) AS totaldeposit
        FROM
        pasiendaftar_t pd
        JOIN antrianpasiendiperiksa_t apd ON apd.noregistrasifk = pd.norec
        left join pegawai_m as pg on pg.id= pd.objectpegawaifk
        left JOIN strukbuktipenerimaan_t sbm ON sbm.norec= pd.nosbmlastfk
        left JOIN strukpelayanan_t sp ON sp.norec= pd.nostruklastfk
        JOIN pelayananpasien_t pp ON pp.noregistrasifk = apd.norec
        JOIN ruangan_m ru ON ru.id = pd.objectruanganlastfk
        JOIN pasien_m ps ON ps.id = pd.nocmfk
        LEFT JOIN kelompokpasien_m klp ON klp.id = pd.objectkelompokpasienlastfk
        LEFT JOIN kelas_m kl ON kl.id = pd.objectkelasfk
        LEFT JOIN rekanan_m rk ON rk.id = pd.objectrekananfk
        WHERE pd.kdprofile = $idProfile and ru.objectdepartemenfk IN (35, 16)
        AND pd.tglpulang IS NOT NULL
        AND pp.produkfk = 402611
        AND pd.tglregistrasi BETWEEN '$tglAwal'
        AND '$tglAkhir'
        $deptId
        $ruangId
        $kelId
        $noreg
        $norm
        $nama
        GROUP BY pd.tglregistrasi,pd.noregistrasi,	ps.nocm,	ps.namapasien,kl.namakelas,pg.namalengkap,
            klp.kelompokpasien,	rk.namarekanan,ru.namaruangan,  pd.tglpulang,sbm.nosbm,sp.nostruk"));
        $result = array(
            'daftar' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getRekapKunjunganRJ(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $monthYear = $request['monthYear'] ;
        $data = DB::select(DB::raw(" 
                select 'Registrasi' as uraian, 'Pengunjung' as satuan,pd.norec, pd.noregistrasi, dp.namadepartemen,ru.id as idruangan,
                ru.namaruangan,kp.id as idkelompokpasien,kp.kelompokpasien,jp.jenispelayanan
                from pasiendaftar_t as pd 
                join kelompokpasien_m as kp on kp.id =pd.objectkelompokpasienlastfk
                join ruangan_m as ru on ru.id =pd.objectruanganlastfk
                join departemen_m as dp on dp.id =ru.objectdepartemenfk
                left join jenispelayanan_m as jp on cast (jp.id as text)=pd.jenispelayanan 
                where pd.kdprofile = $idProfile and to_char( pd.tglregistrasi,'YYYY-MM')='$monthYear'
                and ru.objectdepartemenfk =18
                
                union all 
                 
                select 'Konsultasi' as uraian, 'Kunjungan' as satuan,apd.norec, pd.noregistrasi, dp.namadepartemen,ru.id as idruangan,ru.namaruangan,
                kp.id as idkelompokpasien,kp.kelompokpasien,jp.jenispelayanan
                from antrianpasiendiperiksa_t as apd 
                join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                join kelompokpasien_m as kp on kp.id =pd.objectkelompokpasienlastfk
                join ruangan_m as ru on ru.id =apd.objectruanganfk
                join departemen_m as dp on dp.id =ru.objectdepartemenfk
                left join jenispelayanan_m as jp on cast (jp.id as text)=pd.jenispelayanan 
                where pd.kdprofile = $idProfile and to_char( apd.tglregistrasi,'YYYY-MM')='$monthYear'
                and ru.objectdepartemenfk =18
                "));

        $tempData = [];
        if($data > 0){
            $rajalRegBPJS = 0;
            $rajalRegNonBPJS = 0;
            $melatiBPJS = 0;
            $melatiNonBPJS = 0;
            $clpBPJS = 0;
            $clpNonBPJS = 0;
            $potasBPJS = 0;
            $potasNonBPJS = 0;
            $klinikRemajaBPJS = 0;
            $klinikRemajaNonBPJS = 0;
            foreach ($data  as $item){
                $sama = false;
                $i = 0;
                foreach ($tempData as $item2) {
                    if($item->uraian ==  $tempData[$i]['uraian']) {
                        $sama = true;
                        $jml = (float)$item2['jumlah'] + 1;
                        $tempData[$i]['jumlah'] = $jml;
                        if ($item->jenispelayanan == 'REGULER' && $item->idkelompokpasien == 2) {
                            $tempData[$i]['rajalRegBPJS'] = (float)$item2['rajalRegBPJS'] + 1;
                        }
                        if ($item->jenispelayanan == 'REGULER' && $item->idkelompokpasien != 2) {
                            $tempData[$i]['rajalRegNonBPJS'] = (float)$item2['rajalRegNonBPJS'] + 1;
                        }
                        if ($item->idruangan == 19 && $item->idkelompokpasien == 2) {
                            $tempData[$i]['melatiBPJS'] = (float)$item2['melatiBPJS'] + 1;
                        }
                        if ($item->idruangan == 19 && $item->idkelompokpasien != 2) {
                            $tempData[$i]['melatiNonBPJS'] = (float)$item2['melatiNonBPJS'] + 1;
                        }
                        if ($item->idruangan == 26 && $item->idkelompokpasien == 2) {
                            $tempData[$i]['clpBPJS'] = (float)$item2['clpBPJS'] + 1;
                        }
                        if ($item->idruangan == 26 && $item->idkelompokpasien != 2) {
                            $tempData[$i]['clpNonBPJS'] = (float)$item2['clpNonBPJS'] + 1;
                        }
//                        if ($item->idruangan == 322 && $item->idkelompokpasien == 2) {
//                            $tempData[$i]['potasBPJS '] = (float)$item2['potasBPJS '] + 1;
//                        }
//                        if ($item->idruangan == 322 && $item->idkelompokpasien != 2) {
//                            $tempData[$i]['potasNonBPJS'] = (float)$item2['potasNonBPJS'] + 1;
//                        }
//                        if ($item->idruangan == 245 && $item->idkelompokpasien == 2) {
//                            $tempData[$i]['klinikRemajaBPJS '] = (float)$item2['klinikRemajaBPJS '] + 1;
//                        }
//                        if ($item->idruangan == 245 && $item->idkelompokpasien != 2) {
//                            $tempData[$i]['klinikRemajaNonBPJS'] = (float)$item2['klinikRemajaNonBPJS'] + 1;
//                        }
                        $tempData[$i]['totalRajal'] = $tempData[$i]['rajalRegBPJS'] + $tempData[$i]['rajalRegNonBPJS'];
                        $tempData[$i]['totalMelati'] = $tempData[$i]['melatiBPJS'] + $tempData[$i]['melatiNonBPJS'];
                        $tempData[$i]['totalClp'] = $tempData[$i]['clpNonBPJS'] + $tempData[$i]['clpBPJS'];
//                        $tempData[$i]['totalPotas'] = $tempData[$i]['potasNonBPJS'] + $tempData[$i]['potasBPJS'];
//                        $tempData[$i]['totalKlinikRemaja'] = $tempData[$i]['klinikRemajaNonBPJS'] + $tempData[$i]['klinikRemajaBPJS'];
                    }
                    $i= $i +1;
                }
                if($sama ==  false){
                    if ($item->jenispelayanan == 'REGULER' && $item->idkelompokpasien == 2) {
                        $rajalRegBPJS = 1; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
                    }
                    if ($item->jenispelayanan == 'REGULER' && $item->idkelompokpasien != 2) {
                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 1; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
                    }
                    if ($item->idruangan == 19 && $item->idkelompokpasien == 2) {
                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 1; $melatiNonBPJS = 0; $clpBPJS = 0;
                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
                    }
//                    if ($item->idruangan == 19 && $item->idkelompokpasien != 2) {
//                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 1; $clpBPJS = 0;
//                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
//                    }
//                    if ($item->idruangan == 26 && $item->idkelompokpasien == 2) {
//                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 1;
//                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
//                    }
//                    if ($item->idruangan == 26 && $item->idkelompokpasien != 2) {
//                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
//                        $clpNonBPJS = 1; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
//                    }
//                    if ($item->idruangan == 322 && $item->idkelompokpasien == 2) {
//                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
//                        $clpNonBPJS = 0; $potasBPJS = 1; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
//                    }
                    if ($item->idruangan == 322 && $item->idkelompokpasien != 2) {
                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 1; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 0;
                    }
                    if ($item->idruangan == 245 && $item->idkelompokpasien == 2) {
                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 1; $klinikRemajaNonBPJS = 0;
                    }
                    if ($item->idruangan == 245 && $item->idkelompokpasien != 2) {
                        $rajalRegBPJS = 0; $rajalRegNonBPJS = 0; $melatiBPJS = 0; $melatiNonBPJS = 0; $clpBPJS = 0;
                        $clpNonBPJS = 0; $potasBPJS = 0; $potasNonBPJS = 0; $klinikRemajaBPJS = 0; $klinikRemajaNonBPJS = 1;
                    }
                    $tempData [] = array(
                        'uraian' => $item->uraian,
                        'satuan' => $item->satuan,
                        'rajalRegBPJS' =>  $rajalRegBPJS ,
                        'rajalRegNonBPJS' =>  $rajalRegNonBPJS ,
                        'totalRajal' => $rajalRegBPJS + $rajalRegNonBPJS ,
                        'melatiBPJS' =>  $melatiBPJS ,
                        'melatiNonBPJS' =>  $melatiNonBPJS ,
                        'totalMelati' => $melatiBPJS + $melatiNonBPJS ,
                        'clpBPJS' =>  $clpBPJS ,
                        'clpNonBPJS' =>  $clpNonBPJS ,
                        'totalClp' => $clpBPJS + $clpNonBPJS ,
                        'potasBPJS' =>  $potasBPJS,
                        'potasNonBPJS' =>  $potasNonBPJS ,
                        'totalPotas' => $potasBPJS + $potasNonBPJS ,
                        'klinikRemajaBPJS' =>  $klinikRemajaBPJS ,
                        'klinikRemajaNonBPJS' =>  $klinikRemajaNonBPJS ,
                        'totalKlinikRemaja' => $klinikRemajaBPJS + $klinikRemajaNonBPJS ,
                        'jumlah' => 1,
                    );
                }

            }

        }

        $result = array(
            'count' => count($tempData),
            'data' => $tempData,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getRekapPembayaranJasaPelayanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $data2= [];
        $tglAwal = $request['tglAwal'] ;
        $tglAkhir = $request['tglAkhir'] ;
        $deptId = $request['deptId'];
        $ruanganId = $request['ruangId'];
        $typePegawaiId = $request['typePegawaiId'];
        $dokterId = $request['dokterId'];
        $TypePasien = $request['tipePasien'];
        $paramJadwalDokter = $request['jadwalKerja'];

        $paramDep =  ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' .$deptId;
        }
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId!= "undefined") {
            $paramRuangan = '  and ru.id = ' .$ruanganId;
        }
        $paramTypePegawai = ' ';
        if (isset($typePegawaiId) && $typePegawaiId != "" && $typePegawaiId!= "undefined") {
            $paramTypePegawai = '  and pg.objecttypepegawaifk = ' .$typePegawaiId;
        }
        $paramDokter = ' ';
        if (isset($dokterId) && $dokterId != "" && $dokterId!= "undefined") {
            $paramDokter = ' and pg.id = ' . $dokterId ;
        }
        $paramTypePasien = ' ';
        if (isset($TypePasien) && $TypePasien != "" && $TypePasien!= "undefined") {
            $paramTypePasien = '  and kp.id = ' .$TypePasien;
        }

        if ($paramJadwalDokter == '' || $paramJadwalDokter == null){
            $data = DB::select(DB::raw(" 
                select *, y.jasa - y.pph as diterima
                from(
                select x.dokter,x.namaruangan, x.tglpelayanan,count(*) FILTER (WHERE x.kpid = '1' ) AS jmlch,0  AS jmlkk,
                count(*) FILTER (WHERE x.kpid <> '1' ) AS jmljm, 
                sum(case when x.pendapatancash is null then 0 else x.pendapatancash end) as pendapatancash,
                0 as pendapatankredit,
                sum(case when x.pendapatanjaminan is null then 0 else x.pendapatanjaminan end) as pendapatanjaminan,
                sum(x.jasa ) as jasa, sum(x.remun) as remun,
                sum((x.jasa /100)*7.5) as pph
                from(
                select  UPPER ( pg.namalengkap) as dokter,  UPPER (ru.namaruangan) as namaruangan,  to_char(pp.tglpelayanan,'DD-MM-YYYY') as tglpelayanan, 
                sum( case when ppd.komponenhargafk = 35 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as jasa, 
                0 as remun, sum( pp.jumlah) as jumlah, 
                sum( case when kp.id = 1 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as pendapatancash, 
                sum( case when kp.id <> 1 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as pendapatanjaminan, 
                kp.kelompokpasien,  pg.objecttypepegawaifk ,kp.id as kpid           
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec            
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk            
                where  objectjenispetugaspefk = 4 and pd.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'    
                 $paramDep
                 $paramRuangan
                 $paramTypePegawai
                 $paramDokter
                 $paramTypePasien
                GROUP BY pg.namalengkap,ru.namaruangan, kp.kelompokpasien, 
                pg.objecttypepegawaifk     ,pp.tglpelayanan,kp.id 
                order by pp.tglpelayanan
                ) as x
                GROUP BY  x.dokter,x.namaruangan,x.tglpelayanan
                ) as y  order by y.tglpelayanan"));

            $data2 = DB::select(DB::raw(" 
                select x.dokter,x.tglpelayanan,
                count(*) FILTER (WHERE x.kpid = '1' ) AS jmlch,0  AS jmlkk,
                count(*) FILTER (WHERE x.kpid <> '1' ) AS jmljm
                from (
                select pd.noregistrasi, UPPER ( pg.namalengkap) as dokter,   to_char(pp.tglpelayanan,'DD-MM-YYYY') as tglpelayanan, 
                kp.kelompokpasien,  pg.objecttypepegawaifk ,kp.id as kpid           
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec            
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk            
                where pd.kdprofile = $idProfile and objectjenispetugaspefk = 4
                and ppd.komponenhargafk=35
                and  pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'    
                $paramDep
                $paramRuangan
                $paramTypePegawai
                $paramDokter
                $paramTypePasien
                GROUP BY pg.namalengkap,ru.namaruangan, kp.kelompokpasien, 
                pg.objecttypepegawaifk,pp.tglpelayanan,kp.id ,pd.noregistrasi
                order by pp.tglpelayanan
                ) as x
                GROUP BY x.dokter,x.tglpelayanan"));

        }else if ($paramJadwalDokter == 1 || $paramJadwalDokter == '1'){

            $data = DB::select(DB::raw(" 
                select *, y.jasa - y.pph as diterima
                from(
                select x.dokter,x.namaruangan, x.tglpelayanan,count(*) FILTER (WHERE x.kpid = '1' ) AS jmlch,0  AS jmlkk,
                count(*) FILTER (WHERE x.kpid <> '1' ) AS jmljm, 
                sum(case when x.pendapatancash is null then 0 else x.pendapatancash end) as pendapatancash,
                0 as pendapatankredit,
                sum(case when x.pendapatanjaminan is null then 0 else x.pendapatanjaminan end) as pendapatanjaminan,
                sum(x.jasa ) as jasa, sum(x.remun) as remun,
                sum((x.jasa /100)*7.5) as pph
                from(
                select  UPPER ( pg.namalengkap) as dokter,  UPPER (ru.namaruangan) as namaruangan,  to_char(pp.tglpelayanan,'DD-MM-YYYY') as tglpelayanan, 
                sum( case when ppd.komponenhargafk = 35 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as jasa, 
                0 as remun, sum( pp.jumlah) as jumlah, 
                sum( case when kp.id = 1 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as pendapatancash, 
                sum( case when kp.id <> 1 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as pendapatanjaminan, 
                kp.kelompokpasien,  pg.objecttypepegawaifk ,kp.id as kpid           
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec            
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk
                INNER JOIN pegawaijadwalkerja_m as jdw on jdw.objectpegawaifk=ppp.objectpegawaifk
                INNER JOIN kalender_s as kld on kld.id=jdw.objecttanggalfk
                INNER JOIN shiftkerja_m as sf on sf.id=jdw.objectshiftfk
                where pd.kdprofile = $idProfile and objectjenispetugaspefk = 4
                and  pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'    
                 $paramDep
                 $paramRuangan
                 $paramTypePegawai
                 $paramDokter
                 $paramTypePasien
                and (pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jammasuk,'.',':'),'YYYY-MM-DD HH24:MI')  
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jampulang,'.',':'),'YYYY-MM-DD HH24:MI'))
                GROUP BY pg.namalengkap,ru.namaruangan, kp.kelompokpasien, 
                pg.objecttypepegawaifk     ,pp.tglpelayanan,kp.id 
                order by pp.tglpelayanan
                ) as x
                GROUP BY  x.dokter,x.namaruangan,x.tglpelayanan
                ) as y  order by y.tglpelayanan"));

            $data2 = DB::select(DB::raw(" 
                select x.dokter,x.tglpelayanan,
                count(*) FILTER (WHERE x.kpid = '1' ) AS jmlch,0  AS jmlkk,
                count(*) FILTER (WHERE x.kpid <> '1' ) AS jmljm
                from (
                select pd.noregistrasi, UPPER ( pg.namalengkap) as dokter,   to_char(pp.tglpelayanan,'DD-MM-YYYY') as tglpelayanan, 
                kp.kelompokpasien,  pg.objecttypepegawaifk ,kp.id as kpid           
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec            
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk
                INNER JOIN pegawaijadwalkerja_m as jdw on jdw.objectpegawaifk=ppp.objectpegawaifk
                INNER JOIN kalender_s as kld on kld.id=jdw.objecttanggalfk
                INNER JOIN shiftkerja_m as sf on sf.id=jdw.objectshiftfk
                where  pd.kdprofile = $idProfile and objectjenispetugaspefk = 4
                and ppd.komponenhargafk=35
                and  pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'    
                $paramDep
                $paramRuangan
                $paramTypePegawai
                $paramDokter
                $paramTypePasien
                and (pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jammasuk,'.',':'),'YYYY-MM-DD HH24:MI')  
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jampulang,'.',':'),'YYYY-MM-DD HH24:MI'))
                GROUP BY pg.namalengkap,ru.namaruangan, kp.kelompokpasien, 
                pg.objecttypepegawaifk,pp.tglpelayanan,kp.id ,pd.noregistrasi
                order by pp.tglpelayanan
                ) as x
                GROUP BY x.dokter,x.tglpelayanan"));
        }else{
            $data = DB::select(DB::raw(" 
                select *, y.jasa - y.pph as diterima
                from(
                select x.dokter,x.namaruangan, x.tglpelayanan,count(*) FILTER (WHERE x.kpid = '1' ) AS jmlch,0  AS jmlkk,
                count(*) FILTER (WHERE x.kpid <> '1' ) AS jmljm, 
                sum(case when x.pendapatancash is null then 0 else x.pendapatancash end) as pendapatancash,
                0 as pendapatankredit,
                sum(case when x.pendapatanjaminan is null then 0 else x.pendapatanjaminan end) as pendapatanjaminan,
                sum(x.jasa ) as jasa, sum(x.remun) as remun,
                sum((x.jasa /100)*7.5) as pph
                from(
                select  UPPER ( pg.namalengkap) as dokter,  UPPER (ru.namaruangan) as namaruangan,  to_char(pp.tglpelayanan,'DD-MM-YYYY') as tglpelayanan, 
                sum( case when ppd.komponenhargafk = 35 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as jasa, 
                0 as remun, sum( pp.jumlah) as jumlah, 
                sum( case when kp.id = 1 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as pendapatancash, 
                sum( case when kp.id <> 1 then ((ppd.hargajual-case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end )* pp.jumlah) end ) as pendapatanjaminan, 
                kp.kelompokpasien,  pg.objecttypepegawaifk ,kp.id as kpid           
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec            
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk
                INNER JOIN pegawaijadwalkerja_m as jdw on jdw.objectpegawaifk=ppp.objectpegawaifk
                INNER JOIN kalender_s as kld on kld.id=jdw.objecttanggalfk
                INNER JOIN shiftkerja_m as sf on sf.id=jdw.objectshiftfk
                where pd.kdprofile = $idProfile and  objectjenispetugaspefk = 4
                and  pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'    
                $paramDep
                $paramRuangan
                $paramTypePegawai
                $paramDokter
                $paramTypePasien
                and ((pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' 00:00','YYYY-MM-DD HH24:MI')  
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jammasuk,'.',':'),'YYYY-MM-DD HH24:MI')- interval '1' minute  )
                or (pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jampulang,'.',':'),'YYYY-MM-DD HH24:MI') + interval '1' minute
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' 23:59:59','YYYY-MM-DD HH24:MI')))
                GROUP BY pg.namalengkap,ru.namaruangan, kp.kelompokpasien, 
                pg.objecttypepegawaifk     ,pp.tglpelayanan,kp.id 
                order by pp.tglpelayanan
                ) as x
                GROUP BY  x.dokter,x.namaruangan,x.tglpelayanan
                ) as y  order by y.tglpelayanan"));

            $data2 = DB::select(DB::raw(" 
                select x.dokter,x.tglpelayanan,
                count(*) FILTER (WHERE x.kpid = '1' ) AS jmlch,0  AS jmlkk,
                count(*) FILTER (WHERE x.kpid <> '1' ) AS jmljm
                from (
                select pd.noregistrasi, UPPER ( pg.namalengkap) as dokter,   to_char(pp.tglpelayanan,'DD-MM-YYYY') as tglpelayanan, 
                kp.kelompokpasien,  pg.objecttypepegawaifk ,kp.id as kpid           
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec            
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk
                INNER JOIN pegawaijadwalkerja_m as jdw on jdw.objectpegawaifk=ppp.objectpegawaifk
                INNER JOIN kalender_s as kld on kld.id=jdw.objecttanggalfk
                INNER JOIN shiftkerja_m as sf on sf.id=jdw.objectshiftfk
                where pd.kdprofile = $idProfile and objectjenispetugaspefk = 4
                and ppd.komponenhargafk=35
                and  pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'    
                $paramDep
                $paramRuangan
                $paramTypePegawai
                $paramDokter
                $paramTypePasien
                and ((pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' 00:00','YYYY-MM-DD HH24:MI')  
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jammasuk,'.',':'),'YYYY-MM-DD HH24:MI')- interval '1' minute  )
                or (pp.tglpelayanan between  to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' ' || replace(jampulang,'.',':'),'YYYY-MM-DD HH24:MI') + interval '1' minute
                and to_timestamp(to_char(kld.tanggal, 'YYYY-MM-DD')  || ' 23:59:59','YYYY-MM-DD HH24:MI')))
                GROUP BY pg.namalengkap,ru.namaruangan, kp.kelompokpasien, 
                pg.objecttypepegawaifk,pp.tglpelayanan,kp.id ,pd.noregistrasi
                order by pp.tglpelayanan
                ) as x
                GROUP BY x.dokter,x.tglpelayanan"));
        }

        $result = array(
            'count' => count($data),
            'data' => $data,
            'jmlpasien' => $data2,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDetailJasaPelayanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = [];
        $data2 = [];
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $deptId = $request['deptId'];
        $ruanganId = $request['ruangId'];
        $dokterId = $request['dokterId'];
        $TypePasien = $request['tipePasien'];
        $paramJadwalDokter = $request['jadwalKerja'];
        $typePegawaiId = $request['typePegawaiId'];

        $paramDep = ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' . $deptId;
        }
        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = " and ru.namaruangan ilike ". "'%" . $ruanganId . "%'";
        }
        $paramDokter = ' ';
        if (isset($dokterId) && $dokterId != "" && $dokterId != "undefined") {
            $paramDokter = " and pg.namalengkap ilike ". "'%" . $dokterId . "%'";
        }
        $paramTypePasien = ' ';
        if (isset($TypePasien) && $TypePasien != "" && $TypePasien != "undefined") {
            $paramTypePasien = '  and kp.id = ' . $TypePasien;
        }
        $paramTypePegawai = ' ';
        if (isset($typePegawaiId) && $typePegawaiId != "" && $typePegawaiId!= "undefined") {
            $paramTypePegawai = '  and pg.objecttypepegawaifk = ' .$typePegawaiId;
        }

        $data = DB::select(DB::raw("select pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pro.namaproduk,
                    UPPER ( pg.namalengkap) as dokter,UPPER (ru.namaruangan) as namaruangan,pp.jumlah,
                    pp.tglpelayanan,kp.id as kpid,kp.kelompokpasien,pd.objectkelasfk,kls.namakelas,
                    pg.objecttypepegawaifk,pp.hargajual,case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon	
                from pasiendaftar_t as pd            
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec            
                inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                inner join pasien_m as ps on ps.id = pd.nocmfk       
                left join pelayananpasiendetail_t as ppd on ppd.pelayananpasien=pp.norec            
                left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec                            
                inner join pegawai_m as pg on pg.id=ppp.objectpegawaifk            
                left join ruangan_m as ru on ru.id=apd.objectruanganfk            
                left join kelompokpasien_m as kp on kp.id=pd.objectkelompokpasienlastfk 
                left join produk_m as pro on pro.id = pp.produkfk   
                left join kelas_m as kls on kls.id = pd.objectkelasfk       
                where pd.kdprofile = $idProfile and objectjenispetugaspefk = 4 and pp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'
                $paramDep
                $paramDokter
                $paramRuangan
                $paramTypePasien
                $paramTypePegawai
                GROUP BY pd.noregistrasi,pro.namaproduk,pg.namalengkap,ru.namaruangan,pp.tglpelayanan, 
                      kp.kelompokpasien,pg.objecttypepegawaifk,kp.id,pp.hargajual,pp.hargadiscount,
                      pp.jumlah,ps.nocm,ps.namapasien,pd.objectkelasfk,kls.namakelas,pd.tglregistrasi"));


        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanKegiatanOperasionalRJ(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $monthYear = $request['monthYear'];
        $jenis = $request['jenisRuanganId'];
        $join = '';
        $paramJenis = '';
        if(isset($jenis)&& $jenis != ''){
            $paramJenis = 'and maps.objectjenisruanganfk ='.$jenis;
            $join = 'left JOIN mapruangantojenisruangan_m as maps on maps.objectruanganfk=ru.id'	;
        }
        $data = DB::select(DB::raw("select pd.objectkelompokpasienlastfk,  pro.namaproduk, 
                    case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon,pp.hargajual,pp.jumlah, 
                    (case 
                    when pro.id in (10011572,395,10011571) and pd.objectkelompokpasienlastfk=2 then 'Administrasi BPJS' 
                    when pro.id in (10011572,395,10011571) and pd.objectkelompokpasienlastfk <> 2 then 'Administrasi Non BPJS' 
                    when djp.objectjenisprodukfk =100 and pd.objectkelompokpasienlastfk = 2  then 'Konsultasi BPJS'
                    when djp.objectjenisprodukfk =100 and pd.objectkelompokpasienlastfk <> 2  then 'Konsultasi Non BPJS'
                    when djp.objectjenisprodukfk not in (101,100,99,27666,104,97) --visiste , konsul, akomodasi,alat canggih, usg 
                    then 'Tindakan'
                    when djp.objectjenisprodukfk= 104 then 'USG' end ) as kegiatan
                     from pasiendaftar_t as pd
                    INNER JOIN antrianpasiendiperiksa_t as apd on pd.norec=apd.noregistrasifk
                    INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                    INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                    $join
                    INNER JOIN departemen_m as dp on dp.id=ru.objectdepartemenfk
                    INNER JOIN produk_m as pro on pro.id=pp.produkfk
                    INNER JOIN detailjenisproduk_m as djp on djp.id=pro.objectdetailjenisprodukfk
                    where pd.kdprofile = $idProfile and ru.objectdepartemenfk = 18
                    and pro.id not in (402611)
                    and djp.objectjenisprodukfk not in (97,101,27666)
                    and to_char(pp.tglpelayanan,'yyyy-MM') ='$monthYear'
                    $paramJenis
                    --order by urutan asc;
            "));

        return $this->respond($data);

        $data10 = [];
        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->kegiatan == $data10[$i]['kegiatan']) {
                    $sama = true;
                    $jml = (float)$item->jumlah ;
                    if ($item->objectkelompokpasienlastfk ==2 ) {
                        $data10[$i]['VolBpjs'] = (float)$hideung['VolBpjs'] + $jml;
                        $data10[$i]['RupBpjs'] =$data10[$i]['RupBpjs']+($jml * $item->hargajual);
                    }
                    if ($item->objectkelompokpasienlastfk != 2){
                        $data10[$i]['VolNonBpjs'] = (float)$hideung['VolNonBpjs'] + $jml;
                        $data10[$i]['RupNonBpjs'] =$data10[$i]['RupNonBpjs']+($jml * ($item->hargajual - $item->diskon));
                    }
//
                    $data10[$i]['jmlVol']=  $data10[$i]['VolNonBpjs']+$data10[$i]['VolBpjs'] ;
                    $data10[$i]['jmlRup']=  $data10[$i]['RupNonBpjs']+$data10[$i]['RupBpjs'] ;
                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->objectkelompokpasienlastfk == 2) {
                    $data10[] = array(
//                        'urutan'=>$item->urutan,
//                        'idkegiatan'=>$item->idkegiatan,
                        'kegiatan' => $item->kegiatan,
                        'VolBpjs' => $item->jumlah,
                        'VolNonBpjs' => 0,
                        'jmlVol' => $item->jumlah,
                        'RupBpjs' => ($item->hargajual - $item->diskon)*$item->jumlah ,
                        'RupNonBpjs' => 0,
                        'jmlRup' => ($item->hargajual - $item->diskon)*$item->jumlah ,
                    );

                }  if ($item->objectkelompokpasienlastfk != 2){
                    $data10[] = array(
//                        'urutan'=>$item->urutan,
//                        'idkegiatan'=>$item->idkegiatan,
                        'kegiatan' => $item->kegiatan,
                        'VolBpjs' => 0,
                        'VolNonBpjs' => $item->jumlah,
                        'jmlVol' => $item->jumlah,
                        'RupBpjs' => 0 ,
                        'RupNonBpjs' => ($item->hargajual - $item->diskon)*$item->jumlah ,
                        'jmlRup' =>($item->hargajual - $item->diskon)*$item->jumlah ,
                    );

                }
            }


        }
        $kodeExternal = 'IRJ';
        $year = substr($monthYear,0,4);
        if(isset($jenis)&& $jenis != '') {
            if($jenis == 1 ){
                $kodeExternal = 'ANAK';
            }
            if($jenis == 2 ){
                $kodeExternal = 'BUNDA';
            }
        }


        $RBA = DB::select(DB::raw("
            select id,pelayanan,targetrupiah,targetvolume,tahun,kodeexternal from targetkinerja_m 
            where statusenabled= 1 
            and kodeexternal ilike '%$kodeExternal%'
            and tahun='$year'
        "));
//		return $data10;
//        $this->respond($data10);
        $resultWithRBA = [];
        foreach ($data10 as $item){
            foreach ($RBA as $rbaa){
                if($rbaa->pelayanan == 'Administrasi BPJS'){
                    $urutan = 1;
                }
                else   if($rbaa->pelayanan == 'Administrasi Non BPJS'){
                    $urutan = 2;
                }
                else  if($rbaa->pelayanan == 'Konsultasi BPJS'){
                    $urutan = 3;
                }
                else  if($rbaa->pelayanan == 'Konsultasi Non BPJS'){
                    $urutan = 4;
                }
                else if($rbaa->pelayanan == 'Tindakan'){
                    $urutan = 5;
                }
                else if($rbaa->pelayanan == 'USG' ){
                    $urutan = 6;
                }
                if($item['kegiatan'] == $rbaa->pelayanan ){
                    $resultWithRBA [] = array(
                        'urutan'=> $urutan,
                        'kegiatan' => $item['kegiatan'],
                        'VolBpjs' =>$item['VolBpjs'],
                        'VolNonBpjs' => $item['VolNonBpjs'],
                        'jmlVol' => $item['jmlVol'],
                        'RupBpjs' => $item['RupBpjs'],
                        'RupNonBpjs' => $item['RupNonBpjs'],
                        'jmlRup' => $item['jmlRup'],
                        'targetvolume' => $rbaa->targetvolume,
                        'targetrupiah' => (float)$rbaa->targetrupiah,
                        'persentasevolume' => number_format( ($item['jmlVol'] /$rbaa->targetvolume) * 100,2 ),
                        'persentaserp' => number_format( ($item['jmlRup'] / (float)$rbaa->targetrupiah) * 100,2 ),
                    );
                }
            }
        }

        if(count($resultWithRBA ) != 0) {
            foreach ($resultWithRBA as $key => $row) {
                $count[$key] = $row['urutan'];
            }

            array_multisort($count, SORT_ASC, $resultWithRBA);
        }

        return $this->respond($resultWithRBA)  ;
    }

    public function getLaporanKegiatanOperasionalDetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $monthYear = $request['monthYear'];
        $jenis = $request['jenisRuanganId'];
        $join = '';
        $paramJenis = '';
        $Pro = $request['ProJenis'];
        if(isset($jenis)&& $jenis != ''){
            $paramJenis = 'and maps.objectjenisruanganfk ='.$jenis;
            $join = 'left JOIN mapruangantojenisruangan_m as maps on maps.objectruanganfk=ru.id'	;
        }

        $paramPro ='';
        if(isset($Pro)&& $Pro != ''){
            if ($Pro == 'Administrasi BPJS'){
                $paramPro =' and pro.id in (10011572,395,10011571) and pd.objectkelompokpasienlastfk = 2';
            }elseif ($Pro == 'Administrasi Non BPJS') {
                $paramPro =' and pro.id in (10011572,395,10011571) and pd.objectkelompokpasienlastfk <> 2';
            }elseif ($Pro == 'Konsultasi BPJS'){
                $paramPro =' and djp.objectjenisprodukfk =100 and pd.objectkelompokpasienlastfk = 2';
            }elseif ($Pro == 'Konsultasi Non BPJS'){
                $paramPro =' and djp.objectjenisprodukfk =100 and pd.objectkelompokpasienlastfk <> 2';
            }elseif ($Pro == 'Tindakan'){
                $paramPro =' and djp.objectjenisprodukfk not in (101,100,99,27666,104,97)';
            }elseif ($Pro == 'USG'){
                $paramPro =' and djp.objectjenisprodukfk= 104';
            }
        }

        $data = DB::select(DB::raw("select pd.tglregistrasi,ps.nocm,ps.nocm || '/ ' || pd.noregistrasi as noregistrasic,pd.noregistrasi,
                    ps.namapasien,ru.namaruangan,pd.objectkelasfk,kls.namakelas,pd.objectkelompokpasienlastfk,kp.kelompokpasien,
                    pd.objectkelompokpasienlastfk,pp.tglpelayanan,pro.namaproduk,pp.hargajual,pp.jumlah,
                    case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon,
                    ((pp.hargajual - (case when pp.hargadiscount is null then 0 else pp.hargadiscount end))*pp.jumlah) as subtotal
                    from pasiendaftar_t as pd
                    INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                    INNER JOIN antrianpasiendiperiksa_t as apd on pd.norec=apd.noregistrasifk
                    INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                    INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                    $join
                    INNER JOIN departemen_m as dp on dp.id=ru.objectdepartemenfk
                    INNER JOIN produk_m as pro on pro.id=pp.produkfk
                    INNER JOIN detailjenisproduk_m as djp on djp.id=pro.objectdetailjenisprodukfk
                    INNER JOIN kelas_m as kls on kls.id = pd.objectkelasfk
                    INNER JOIN kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                    where pd.kdprofile = $idProfile and ru.objectdepartemenfk = 18
                    and pro.id not in (402611)
                    and djp.objectjenisprodukfk not in (97,101,27666)
                    and to_char(pp.tglpelayanan,'YYYY-MM') ='$monthYear'
                    $paramJenis
                    $paramPro
                    --order by urutan asc;
            "));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getLaporanKegiatanOperasionalRuangan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $monthYear = $request['monthYear'];
        $jenis = $request['jenisRuanganId'];
        $join = '';
        $paramJenis = '';
        if(isset($jenis)&& $jenis != ''){
            $paramJenis = 'and maps.objectjenisruanganfk ='.$jenis;
            $join = 'left JOIN mapruangantojenisruangan_m as maps on maps.objectruanganfk=ru.id'	;
        }
        $data = DB::select(DB::raw("
				select  COALESCE((sum(x.jumlah) ),0)as volume , 
				COALESCE((sum(x.jumlah * x.hargajual)),0) as rupiah,
				--  COALESCE(( sum(x.jumlah) FILTER (WHERE x.kpid <> '2' ) ),0)as volnonbpjs,
				--  COALESCE(( sum(x.jumlah * x.hargajual) FILTER (WHERE x.kpid <> '2' ) ),0)as rupnonbpjs,
				x.namaruangan,x.jenis
				from (select pd.objectkelompokpasienlastfk as kpid,  pro.namaproduk, 
				
				(	case when pro.id in (10011572,395,10011571) then 'Administrasi'
				when djp.objectjenisprodukfk =100 then 'Konsultasi'
				when djp.objectjenisprodukfk not in (101,100,99,27666,104,97) --visiste , konsul, akomodasi,alat canggih, usg 
				then 'Tindakan'
				when djp.objectjenisprodukfk= 104 then 'USG' end ) as jenis,
				pp.hargajual,pp.jumlah,case when ru.id = 458 then 'Kebidanan'
				when ru.id = 6 then 'THT' 
				when ru.id in (518,5) then 'Mata' 
				when ru.id in (9) then 'Penyakit Dalam'
				when ru.id =  7 then 'Kulit Kelamin'
				when ru.id =  4 then 'Gigi & Mulut'
				when ru.id =  37 then 'Laboratorium Gigi'
				when ru.id =  19 then 'Melati'
				when ru.id =  17 then 'Gizi'
				when ru.id =  311 then 'Anestesi'
				when ru.id =  277 then 'Genetik'
				when ru.id =  230 then 'Akupuntur'
				when ru.id in  (268,98,450,33 ) then 'Klinik Anak'
				when ru.id =   11 then 'Klinik Bedah Anak'
				when ru.id =   322 then 'POTAS'
				when ru.id =   237 then 'DAHLIA'
				when ru.id =   26 then 'CLP'
				when ru.id =   245 then 'Klinik Remaja'	
				when ru.id =   97 then 'Kebidanan Flamboyan'	
				when ru.id =   3 then 'Kebidanan Anyelir'
				end as namaruangan
				from pasiendaftar_t as pd
				INNER JOIN antrianpasiendiperiksa_t as apd on pd.norec=apd.noregistrasifk
				INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
				INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
					$join
				INNER JOIN departemen_m as dp on dp.id=ru.objectdepartemenfk
				INNER JOIN produk_m as pro on pro.id=pp.produkfk
				INNER JOIN detailjenisproduk_m as djp on djp.id=pro.objectdetailjenisprodukfk
				where pd.kdprofile = $idProfile and ru.objectdepartemenfk = 18
				and pro.id not in (402611)
				and djp.objectjenisprodukfk not in (97,101,27666)
				and format(pp.tglpelayanan,'yyyy-MM') ='$monthYear'					$paramJenis
				) as x
				GROUP BY x.namaruangan,x.jenis

            "));

        $data10 = [];
        $namaExternal = '';
        $year = substr($monthYear,0,4);
        if(isset($jenis)&& $jenis != '') {
            if($jenis == 1 ){
                $namaExternal = 'ANAK';
            }
            if($jenis == 2 ){
                $namaExternal = 'BUNDA';
            }
        }


        $RBA = DB::select(DB::raw("
            select id,pelayanan,targetrupiah,targetvolume,tahun,kodeexternal,namaexternal from targetkinerja_m 
			where statusenabled= 1 
            and namaexternal ilike '%$namaExternal%'            and tahun='$year'
        "));
//		return $RBA;
        $resultWithRBA = [];
        foreach ($data as $item){
            foreach ($RBA as $rbaa){
                if($item->namaruangan == $rbaa->pelayanan  && $item->jenis == $rbaa->kodeexternal){
                    $targetRba =  $rbaa->targetvolume!= 0? $rbaa->targetvolume: 1;
                    $targetRbaRup =  $rbaa->targetrupiah!= 0? $rbaa->targetrupiah: 1;
                    $resultWithRBA [] = array(
//						'urutan'=> $urutan,
                        'namaruangan' => $item->namaruangan ,
                        'rupiah' => (float) $item->rupiah ,
                        'volume' => (float)  $item->volume ,
                        'jenis' =>$item->jenis,
                        'targetvolume' => $rbaa->targetvolume,
                        'targetrupiah' => (float)$rbaa->targetrupiah,
                        'persentasevolume' => number_format( ((float)$item->volume /(float)  $targetRba) * 100,2 ),
                        'persentaserp' => number_format( ((float) $item->rupiah / (float) $targetRbaRup) * 100,2 ),
                    );
                }
            }
        }
        if(count($resultWithRBA ) != 0) {
            foreach ($resultWithRBA as $key => $row) {
                $count[$key] = $row['jenis'];
            }

            array_multisort($count, SORT_ASC, $resultWithRBA);
        }

        return $resultWithRBA;
    }

    public function getDataPasienPerjanjian($noRegister){
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->first();
        if(!$pasienDaftar){
            //kalau tidak ditemukan
            return false;
        }

        $result = array(
            'noCm'  => $pasienDaftar->pasien->nocm,
            'noRegistrasi' => $pasienDaftar->noregistrasi,
            'jenisPasienLama'   => $pasienDaftar->kelompok_pasien->kelompokpasien,
            'jenisPasienId'   => $pasienDaftar->objectkelompokpasienlastfk,
            'namaPasien'  => $pasienDaftar->pasien->namapasien,
            'status'        => $pasienDaftar->statuspasien
        );
        return $this->respond($result);
    }

    public function getJenisPasienPerjanjian(Request $request){
        $kelompokPasienID =  $this->getKelompokPasienPerjanjian();
        return $this->respond($this->getList(KelompokPasien::where('id', $kelompokPasienID) , new KelompokPasienTransformer(), $request));
    }

    public function getDataDetailVerifikasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $noRegistrasi = $request['noRegistrasi'];
        $result = [];
        $data= \DB::table('pasiendaftar_t as pd')
            ->join('strukpelayanan_t as sp','sp.noregistrasifk', '=','pd.norec')
            ->leftJoin('strukbuktipenerimaan_t as sbm','sbm.nostrukfk','=','sp.norec')
            ->join('logginguser_t as lg','lg.noreff','=','sp.norec')
            ->join('loginuser_s as lu','lu.id','=','lg.objectloginuserfk')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->leftJoin('loginuser_s as lu1','lu1.id','=','sbm.objectpegawaipenerimafk')
            ->leftJoin('pegawai_m as pg1','pg1.id', '=','lu.objectpegawaifk')
            ->leftJoin('strukpelayananpenjamin_t as spp','spp.nostrukfk','=','sp.norec')
            ->select(DB::raw("pd.noregistrasi,sp.norec,sp.nostruk,sp.tglstruk,sp.totalharusdibayar,pg.namalengkap as petugasverif,sbm.tglsbm,
                                    CASE WHEN sp.nosbmlastfk IS NULL THEN 'Belum Bayar' ELSE 'Lunas' END AS status,
		                            CASE WHEN sp.nosbmlastfk IS NULL THEN NULL ELSE pg1.namalengkap END AS kasir,
		                            spp.norec as norec_piutang,pd.objectkelompokpasienlastfk,sp.nosbmlastfk,spp.noverifikasi"))
            ->where('pd.kdprofile', $idProfile)
//            ->whereNull('sp.statusenabled')
            ->whereIn('sp.statusenabled', [null,true])
            ->where('lg.jenislog','=','Verifikasi TataRekening')
            ->where('pd.noregistrasi', '=',$noRegistrasi)
            ->get();

        foreach ($data as $item){
            $details = \DB::select(DB::raw("SELECT sbm.norec,sbm.tglsbm,sbm.nosbm,cb.carabayar,sbm.totaldibayar,pg.namalengkap as kasir
                        FROM strukbuktipenerimaan_t as sbm
                        INNER JOIN strukbuktipenerimaancarabayar_t as sbmc on sbmc.nosbmfk = sbm.norec
                        INNER JOIN carabayar_m as cb on cb.id = sbmc.objectcarabayarfk
                        INNER JOIN loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk
                        INNER JOIN pegawai_m as pg on pg.id = lu.objectpegawaifk
                        WHERE sbm.kdprofile = $idProfile and sbm.statusenabled = true AND sbm.nostrukfk =:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $result[] = array(
                'norec' => $item->norec,
                'nostruk' => $item->nostruk,
                'tglstruk' => $item->tglstruk,
                'totalharusdibayar' => $item->totalharusdibayar,
                'petugasverif' => $item->petugasverif,
                'tglsbm' => $item->tglsbm,
                'status' => $item->status,
                'kasir' => $item->kasir,
                'norec_piutang' => $item->norec_piutang,
                'objectkelompokpasienlastfk' => $item->objectkelompokpasienlastfk,
                'nosbmlastfk' => $item->nosbmlastfk,
                'noregistrasi' => $item->noregistrasi,
                'noverifikasi' => $item->noverifikasi,
                'details' => $details,
            );

        }

        if (count($data) == 0) {
            $result = [];
        }

        $result = array(
            'data' => $result,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getPendapatanInstalasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data =[];
        $kdProfile = $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $idDept = '';
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $idDept = ' and ru.objectdepartemenfk =  ' . $request['idDept'];
        }
        $idRuangan='';
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = ' and ru.id = ' . $request['idRuangan'];
        }
        $idKelompok='';
        if (isset($request['idKelompokPasien']) && $request['idKelompokPasien'] != "" && $request['idKelompokPasien'] != "undefined") {
            if ($request['idKelompokPasien'] == 153){
                $idKelompok = 'and kps.id not in (2,4)';
            }else{
                $idKelompok = 'and kps.id = '. $request['idKelompokPasien'];
            }
        }
        $data = DB::select(DB::raw("SELECT x.tglpencarian,x.namaruangan,x.namadepartemen,x.kelompokpasien,SUM (x.total) AS total,x.layanan
                FROM (SELECT Format (pp.tglpelayanan,'yyyy-MM-dd') AS tglpencarian,ru.namaruangan,dpm.namadepartemen,kps.kelompokpasien,
                SUM(((CASE WHEN pp.hargajual IS NULL THEN 0 ELSE pp.hargajual END - 
                CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END) * pp.jumlah) + CASE
                WHEN pp.jasa IS NULL THEN 0 ELSE pp.jasa END) AS total,'Layanan' as layanan
                FROM pelayananpasien_t AS pp
                JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                WHERE pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                AND pp.aturanpakai IS NULL AND pd.statusenabled = 1 AND pp.kdprofile = (int) $kdProfile
                $idDept
                $idRuangan
                $idKelompok
                GROUP BY ru.namaruangan,dpm.namadepartemen,kps.kelompokpasien,pp.tglpelayanan
			    UNION ALL
                SELECT Format (sp.tglstruk,'yyyy-MM-dd HH:mm') AS tglpencarian,ru.namaruangan,dp.namadepartemen,
                'Umum/Pribadi' AS kelompokpasien,
                SUM(spd.qtyproduk * (spd.hargasatuan - CASE WHEN spd.hargadiscount IS NULL THEN 0 ELSE spd.hargadiscount END) 
                + CASE WHEN spd.hargatambahan IS NULL THEN 0 ELSE spd.hargatambahan END) AS total,'Non Layanan' as layanan
                FROM strukpelayanan_t AS sp
                JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                LEFT JOIN ruangan_m AS ru ON ru.id = sp.objectruanganfk
                LEFT JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
                WHERE sp.kdprofile = $idProfile and  sp.tglstruk BETWEEN '$tglAwal' AND '$tglAkhir'
                AND SUBSTRING(sp.nostruk, 1, 2) = 'OB'
                AND sp.statusenabled <> 0  AND sp.kdprofile = (int) $kdProfile
                $idDept
                $idRuangan
                $idKelompok
                GROUP BY sp.tglstruk,ru.namaruangan,dp.namadepartemen
			    UNION ALL
                SELECT Format(pp.tglpelayanan,'yyyy-MM-dd HH:mm') AS tglpencarian,ru.namaruangan,
                dpm.namadepartemen,kps.kelompokpasien,
                SUM(((CASE WHEN pp.hargajual IS NULL THEN 0 ELSE pp.hargajual END - CASE
                WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END) * pp.jumlah) + CASE
                WHEN pp.jasa IS NULL THEN 0 ELSE pp.jasa END) AS total,'Layanan' as layanan
                FROM pelayananpasien_t AS pp
                JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
                JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
                LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                WHERE pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'
                AND pp.aturanpakai IS NOT NULL AND pd.statusenabled = 1  AND pp.kdprofile = (int) $kdProfile
                $idDept
                $idRuangan
                $idKelompok
                GROUP BY ru.namaruangan,dpm.namadepartemen,kps.kelompokpasien,pp.tglpelayanan) AS x
                GROUP BY x.tglpencarian,x.kelompokpasien,x.namaruangan,x.namadepartemen,x.layanan"));

        if(count($data) >0){
            foreach ($data as $key => $row) {
                $count[$key] = $row->tglpencarian;
            }
            array_multisort($count, SORT_ASC, $data);
        }
        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
     public function getDatfarSyncTrans(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->join('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->Join('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('asuransipasien_m as asu', 'pa.objectasuransipasienfk', '=', 'asu.id')
            ->leftjoin('kelas_m as klstg','klstg.id','=','asu.objectkelasdijaminfk')
             ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftjoin('golongandarah_m as gd', 'gd.id', '=', 'ps.objectgolongandarahfk')
            ->leftjoin('pendidikan_m as pdd', 'pdd.id', '=', 'ps.objectpendidikanfk')
            ->leftjoin('agama_m as agm', 'agm.id', '=', 'ps.objectagamafk')
            ->leftjoin('statusperkawinan_m as stt', 'stt.id', '=', 'ps.objectstatusperkawinanfk')
            ->leftjoin('diagnosa_m as dg', 'dg.id', '=', 'pa.objectdiagnosafk')
            ->select('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien','ps.noidentitas','ps.nobpjs',
                'kp.kelompokpasien', 'rek.namarekanan','pg.id as iddpjp', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pa.norec as norec_pa', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'pa.nosep as nosep', 'pd.nostruklastfk','klstg.namakelas as kelasditanggung','kls.namakelas',
                'ps.tgllahir','ru.objectdepartemenfk', 'pd.objectkelasfk','dept.id as deptid','pa.ppkrujukan',
                'ps.objectjeniskelaminfk','ps.objectgolongandarahfk','ps.objectpendidikanfk','ps.objectagamafk','pa.prolanisprb',
                'ps.objectstatusperkawinanfk','ps.tempatlahir','ps.notelepon','ps.nohp','ps.objectpekerjaanfk','pa.norujukan',
                'pa.tglrujukan','pa.tanggalsep as tglsep','pa.ppkrujukan','pa.objectdiagnosafk','klstg.nourut as kelastgfk','pa.keteranganlaka',
                'pa.lokasilakalantas','pa.lakalantas','pa.penjaminlaka','pa.cob','pa.katarak','pa.tglkejadian','pa.suplesi','pa.nosepsuplesi',
                'dg.kddiagnosa','dg.namadiagnosa')
            ->where('pd.statusenabled',1)
            ->where('pd.kdprofile', (int)$kdProfile);

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

        $data = $data->get();

        $data2=[];
        foreach ($data as $key => $value) {
           $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
            ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
            ->where('ddp.objectjenisdiagnosafk',1)
            ->where('apd.noregistrasifk',$value->norec)
            ->where('apd.kdprofile', (int)$kdProfile)
            ->first();
            if(!empty($diagnosa)){
                $value->isdiagnosis = true;
            }else{
                $value->isdiagnosis = false;
            }
            $data2 []=  $value;
           
            // $i = $i+1;
        }
       
        for ($i = count($data2) - 1; $i >= 0; $i--) {
            if (isset($filter['isnotdiagnosis']) && $filter['isnotdiagnosis'] != "" && $filter['isnotdiagnosis'] != "undefined" && $filter['isnotdiagnosis'] != 'false' && $data2[$i]->isdiagnosis == true){
                 array_splice($data2,$i,1);

            } 
        }
        return $this->respond($data2);
    }
     public function getSignatureTrans(  $kdProfile){
         $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);
         $client = new \GuzzleHttp\Client();
         $get = $client->get( $baseUrl.'get-signature?username=simrs&password=administrator');
         $respond = json_decode ( $get->getBody()->getContents());
         return $respond;
     }
    public function getPasien($kdProfile,$token,$nik){
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);
        $curl = curl_init();

        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  $baseUrl.'get-pasien?nik='.$nik,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
//            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "X-AUTH-TOKEN: ".  (string)$token,

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result =  json_decode($response);
        }
        return $result;
    }
    public function saveSyncTrans(Request $request){
        $data = $request['data'];
        $kdProfile = $this->getDataKdProfile($request);
        $getSign = $this->getSignatureTrans($kdProfile);
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);
//        $client = new \GuzzleHttp\Client();
        foreach ($data as $item){
            if($item['nik'] != null && $item['nobpjs']!=null ){
                $curl = curl_init();
                $dataJsonSend = json_encode($item);
                curl_setopt_array($curl, array(
    //                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
                    CURLOPT_URL=>  $baseUrl.'save-pasien',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $dataJsonSend,
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json;",
                        "X-AUTH-TOKEN: ".  (string)$token,

                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    $result= "cURL Error #:" . $err;
                } else {
                    $result =  json_decode($response);
                }
            }
        }
        return $this->respond($result);

    }
    public function saveSyncTransEMR(Request $request){
        $data = $request['data'];
        $kdProfile = $this->getDataKdProfile($request);
        $getSign = $this->getSignatureTrans($kdProfile);
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);
//        $client = new \GuzzleHttp\Client();
        $pasien = $this->getPasien($kdProfile,$token,$request['data']['nik']);
        if(isset($pasien[0])){
             $data['pasienfk'] = $pasien[0]->id;
        }
       
        $noregis = $data['noregistrasi'];
        $pelayananMedis = DB::select(DB::raw("
            select apd.norec,4 as profilefk,format(apd.tglmasuk,'yyyy/MM/dd HH:mm') as tglmasuk,
            case when apd.tglkeluar is not null then  format(apd.tglkeluar,'yyyy/MM/dd HH:mm')  else null end as tglkeluar,
            ru.kdinternal as ruanganfk,
            apd.objectpegawaifk as iddpjp,pg.namalengkap as dpjp
            from antrianpasiendiperiksa_t  as apd 
            join ruangan_m as ru on ru.id =apd.objectruanganfk
            join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
            left join pegawai_m as pg on pg.id =apd.objectpegawaifk
            where pd.noregistrasi='$noregis'"));

        $data['pelayananmedisdetail'] = [];
        $data['pelayananmedisdetail'] = $pelayananMedis;
        $datassarr = [];
        foreach ($data['pelayananmedisdetail'] as $pm){
            $norec = $pm->norec;
            // $pm->transaksimedis =[];
            $pmd = DB::select(DB::raw("SELECT 4 as profilefk, emrdp.emrdfk as emrfk,format(emrdp.tgl,'yyyy/MM/dd HH:mm')  as  tgltransaksi,
                    --case when emrd.type='checkbox' then  ( case when emrdp.value =1 then  'Ya' else 'Tidak' end) 
                    --else end
                    emrdp.value   as deskripsi,
                    pg.id as iddpjp,pg.namalengkap as dpjp,
                    0 as jumlah, '-' as satuan, 0 as tarif, null as kelompokvariabelfk
                    FROM
                        emrpasiend_t AS emrdp
                    INNER JOIN emrpasien_t AS emrp ON emrp.noemr = emrdp.emrpasienfk
                    LEFT JOIN emrd_t AS emrd ON emrd.id = emrdp.emrdfk
                    LEFT JOIN pegawai_m AS pg ON pg.id = emrdp.pegawaifk
                    WHERE
                        emrdp.kdprofile =  $kdProfile
                    AND emrdp.statusenabled = 1
                    -- AND emrp.noemr = 'MR2003/00000002'
                    and emrp.norec_apd='$norec'
                    "));

//            $pm['transaksimedis'][]=  $pmd;

          // $datassarr= $pmd;
            $pmd2  = DB::select(DB::raw("select  pp.norec,4 as profilefk,  case when pp.strukresepfk is null then 410018 else 410019 end as emrfk,
            format(pp.tglpelayanan,'yyyy/MM/dd HH:mm')  as  tgltransaksi,pr.namaproduk as deskripsi,
            case when pp.strukresepfk is null then ppd.objectpegawaifk else  sr.penulisresepfk end as iddpjp,
            case when pp.strukresepfk is null then  pg.namalengkap  else    pg2.namalengkap  end as dpjp, pp.jumlah,
            case when pp.strukresepfk is null then '-' else ss.satuanstandar end as satuan,
            (((case when pp.hargasatuan is null then 0 else pp.hargasatuan  end - case when pp.hargadiscount is null then 0 
            else pp.hargadiscount end) * pp.jumlah)
            + case when pp.jasa is null then 0 else pp.jasa end) as tarif, pr.objectkelompokprodukbpjsfk as kelompokvariabelfk
            from pelayananpasien_t as pp
            join produk_m as pr on pr.id=pp.produkfk
            left join satuanstandar_m as ss on ss.id=pp.satuanviewfk
            left join strukresep_t as sr on sr.norec=pp.strukresepfk
            left join pegawai_m as pg2 on pg2.id= sr.penulisresepfk
            left join pelayananpasienpetugas_t as ppd on pp.norec=ppd.pelayananpasien and ppd.objectjenispetugaspefk=4
            left join pegawai_m as pg on pg.id= ppd.objectpegawaifk
            where pp.noregistrasifk ='$norec'"));
//            $pm['transaksimedis'][]=  $pmd2;
             $datassarr =  array_merge($pmd,$pmd2); 
             $pm->transaksimedis = $datassarr ;
        }

      // return $this->respond(  $data);

        $curl = curl_init();
        $dataJsonSend = json_encode ($data);
//        return $this->respond(   $baseUrl.'save-medical-record');
        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  $baseUrl.'save-medical-record',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "X-AUTH-TOKEN: ".  (string)$token,

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result =  json_decode($response);
        }


        return $this->respond($result);

    }
 
 function syncAlamat($kdProfile,$alamat,$emr,$profileRS){

        // $kdProfile = $this->getDataKdProfile($request);
        $getSign = $this->getSignatureTrans($kdProfile);
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);

        if($alamat['nik'] != null ){
            $curl = curl_init();
            $dataJsonSend = json_encode($alamat);
            curl_setopt_array($curl, array(
                CURLOPT_URL=>  $baseUrl.'save-alamat-pasien',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $dataJsonSend,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json;",
                    "X-AUTH-TOKEN: ".  (string)$token,

                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $result= "cURL Error #:" . $err;
            } else {
                $result =  json_decode($response);

            }
          return  $this->syncEmr($kdProfile,$emr,$profileRS);
//            return $response;
        }

    }
    
    function syncEmr($kdProfile,$data,$profileRS){
        // $kdProfile = $this->getDataKdProfile($request);
        $getSign = $this->getSignatureTrans($kdProfile);
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);
//        $client = new \GuzzleHttp\Client();

        $pasien = $this->getPasien($kdProfile,$token,$data['nik']);
        if(isset($pasien[0])){
            $data['pasienfk'] = $pasien[0]->id;
        }


        $noregis = $data['noregistrasi'];
        $pelayananMedis = DB::select(DB::raw("
            select apd.norec,$profileRS as profilefk,apd.tglmasuk,
            case when apd.tglkeluar is not null then apd.tglkeluar else null end as tglkeluar,
            ru.kdinternal as ruanganfk,
            apd.objectpegawaifk as iddpjp,pg.namalengkap as dpjp
            from antrianpasiendiperiksa_t  as apd 
            join ruangan_m as ru on ru.id =apd.objectruanganfk
            join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
            left join pegawai_m as pg on pg.id =apd.objectpegawaifk
            where pd.noregistrasi='$noregis'
            and pd.statusenabled=true
            and pd.kdprofile=$kdProfile"));

        $data['pelayananmedisdetail'] = [];
        $data['pelayananmedisdetail'] = $pelayananMedis;

        foreach ($data['pelayananmedisdetail'] as $pm){
            $norec = $pm->norec;
            // $pm->transaksimedis =[];
            $pmd = DB::select(DB::raw("SELECT $profileRS as profilefk, emrdp.emrdfk as emrfk,emrp.tglemr as tgltransaksi,
                    --case when emrd.type='checkbox' then  ( case when emrdp.value =1 then  'Ya' else 'Tidak' end) 
                    --else end
                    emrdp.value   as deskripsi,
                    pg.id as iddpjp,pg.namalengkap as dpjp,
                    0 as jumlah, '-' as satuan, 0 as tarif, null as kelompokvariabelfk
                    FROM
                        emrpasiend_t AS emrdp
                    INNER JOIN emrpasien_t AS emrp ON emrp.noemr = emrdp.emrpasienfk
                    LEFT JOIN emrd_t AS emrd ON emrd.id = emrdp.emrdfk
                    LEFT JOIN pegawai_m AS pg ON pg.id = emrdp.pegawaifk
                    WHERE
                       emrdp.statusenabled = true
                    -- AND emrp.noemr = 'MR2003/00000002'
                    and emrp.norec_apd='$norec'
                    and emrp.kdprofile=$kdProfile
                    "));

//            $pm['transaksimedis'][]=  $pmd;

            // $datassarr= $pmd;
            $pmd2  = DB::select(DB::raw("select  pp.norec,$profileRS as profilefk,  case when pp.strukresepfk is null then 2000000000 else 2000000001 end as emrfk,
           pp.tglpelayanan as  tgltransaksi,pr.namaproduk as deskripsi,
            case when pp.strukresepfk is null then ppd.objectpegawaifk else  sr.penulisresepfk end as iddpjp,
            case when pp.strukresepfk is null then  pg.namalengkap  else    pg2.namalengkap  end as dpjp, pp.jumlah,
            case when pp.strukresepfk is null then '-' else ss.satuanstandar end as satuan,
            (((case when pp.hargasatuan is null then 0 else pp.hargasatuan  end - case when pp.hargadiscount is null then 0 
            else pp.hargadiscount end) * pp.jumlah)
            + case when pp.jasa is null then 0 else pp.jasa end) as tarif, pr.objectkelompokprodukbpjsfk as kelompokvariabelfk
            from pelayananpasien_t as pp
            join produk_m as pr on pr.id=pp.produkfk
            left join satuanstandar_m as ss on ss.id=pp.satuanviewfk
            left join strukresep_t as sr on sr.norec=pp.strukresepfk
            left join pegawai_m as pg2 on pg2.id= sr.penulisresepfk
            left join pelayananpasienpetugas_t as ppd on pp.norec=ppd.pelayananpasien and ppd.objectjenispetugaspefk=4
            left join pegawai_m as pg on pg.id= ppd.objectpegawaifk
            where pp.noregistrasifk ='$norec'
            and pp.statusenabled =true
            and pp.kdprofile=$kdProfile"));

            $datassarr =  array_merge($pmd,$pmd2);
            $pmd=[];
            $pmd2=[];
            $pm->transaksimedis = $datassarr ;
        }

//           return $data;

        $curl = curl_init();
        $dataJsonSend = json_encode ($data);
//        return $this->respond(   $baseUrl.'save-medical-record');
        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  $baseUrl.'save-medical-record',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "X-AUTH-TOKEN: ".  (string)$token,

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            if(empty(json_decode($response))){
                $result = $response;
            }else{
                $result =  json_decode($response);
            }

        }

        return $result;

    }
    public function saveSyncTransNEW(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        if($kdProfile  ==17){

             $profileRS = 10632;
        }if($kdProfile  ==18){

            $profileRS = 10633;
        }
        if($kdProfile ==21){
            $profileRS = 287;
        }
        $query = collect(DB::select("SELECT pd.norec,pd.tglregistrasi,
                    ps.nocm,pd.noregistrasi,ru.namaruangan,ps.namapasien,
                    ps.noidentitas as nik,  ps.nobpjs,      pg.id AS iddpjp,    pg.namalengkap AS namadokter,
                    pd.tglpulang,   pd.statuspasien,        pd.objectpegawaifk AS pgid, pd.objectruanganlastfk, pa.nosep AS nosep,
                    klstg.namakelas AS kelasditanggung, kls.namakelas,  ps.tgllahir,    ru.objectdepartemenfk,  pd.objectkelasfk,
                    dept.id AS deptid,  pa.ppkrujukan,  ps.objectjeniskelaminfk,    ps.objectgolongandarahfk,   ps.objectpendidikanfk,
                    ps.objectagamafk,   pa.prolanisprb, ps.objectstatusperkawinanfk,    ps.tempatlahir,
                    ps.notelepon,   ps.nohp,    ps.objectpekerjaanfk,   pa.norujukan,   pa.tglrujukan,  pa.tanggalsep AS tglsep,        pa.objectdiagnosafk,    klstg.nourut AS kelastgfk,
                    pa.keteranganlaka,  pa.lokasilakalantas,    pa.lakalantas,  pa.penjaminlaka,    pa.cob, pa.katarak, pa.tglkejadian,
                    pa.suplesi, pa.nosepsuplesi,    dg.kddiagnosa,  dg.namadiagnosa,    al.alamatlengkap,
                    al.objectdesakelurahanfk,   al.objectkecamatanfk,   al.rtrw,    al.objectkotakabupatenfk,   al.objectpropinsifk,
                    al.kodepos, al.objectnegarafk,pd.statuscovidfk,pd.statuscovid
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                INNER JOIN alamat_m AS al ON al.nocmfk = ps.id
                LEFT JOIN pegawai_m AS pg ON pg.id = pd.objectpegawaifk
                INNER JOIN kelas_m AS kls ON kls.id = pd.objectkelasfk
                INNER JOIN departemen_m AS dept ON dept.id = ru.objectdepartemenfk
                LEFT JOIN pemakaianasuransi_t AS pa ON pa.noregistrasifk = pd.norec
                LEFT JOIN asuransipasien_m AS asu ON pa.objectasuransipasienfk = asu.id
                LEFT JOIN kelas_m AS klstg ON klstg.id = asu.objectkelasdijaminfk
                LEFT JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
                LEFT JOIN golongandarah_m AS gd ON gd.id = ps.objectgolongandarahfk
                LEFT JOIN pendidikan_m AS pdd ON pdd.id = ps.objectpendidikanfk
                LEFT JOIN agama_m AS agm ON agm.id = ps.objectagamafk
                LEFT JOIN statusperkawinan_m AS stt ON stt.id = ps.objectstatusperkawinanfk
                LEFT JOIN diagnosa_m AS dg ON dg.id = pa.objectdiagnosafk
                WHERE
                    pd.statusenabled = true
                    and pd.kdprofile='$kdProfile'
                    and pd.noregistrasi='$request[noregistrasi]'
                "))->first();
        // return $this->respond($query);
        // if($query->kddiagnosa ==  null){
        //     $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
        //         ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
        //         ->join('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
        //         ->select('dg.kddiagnosa','dg.namadiagnosa')
        //         ->where('ddp.objectjenisdiagnosafk',1)
        //         ->where('apd.noregistrasifk',$query->norec)
        //         ->first();
        //     if(!empty($diagnosa)){
        //         $query->kddiagnosa =$diagnosa->kddiagnosa;
        //         $query->namadiagnosa =$diagnosa->namadiagnosa;
        //     }
        // }
         $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
            ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
            ->join('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
            ->select('dg.kddiagnosa','dg.namadiagnosa')
            ->where('ddp.objectjenisdiagnosafk',1)
            ->where('dg.kddiagnosa','<>','-')
            ->where('ddp.kdprofile',$kdProfile)
            ->where('apd.noregistrasifk',$query->norec)
            ->first();
        if(!empty($diagnosa)){
            $query->kddiagnosa =$diagnosa->kddiagnosa;
            $query->namadiagnosa =$diagnosa->namadiagnosa;
        }else{
            // $query->kddiagnosa =$diagnosa->kddiagnosa;
            // $query->namadiagnosa =$diagnosa->namadiagnosa;
        }
        // return $this->respond($diagnosa);
        if($query->kddiagnosa == null){
            $query->kddiagnosa = 'B34.2';
            $query->namadiagnosa='Coronavirus infection, unspecified';
        }
        if($query->kddiagnosa == null || $query->nik == null  || $query->kddiagnosa == '-'  ) {
            $ms = '';
            if($query->kddiagnosa == null){
                $ms = 'Diagnosa Kosong';
            }
            if($query->nik == null){
                $ms = 'NIK Kosong';
            }
            if($query->kddiagnosa == '-'){
                $ms = 'Diagnosa Kosong';
            }
            $transMessage = "Sync EMR Gagal, ".$ms;
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'er@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
//        return $this->respond($query);
        /*
         * Sync Pasien
         */
        $dataPasien =array(
            'namapasien' => $query->namapasien,
            'tgllahir' => $query->tgllahir,
            'tempatlahir' => $query->tempatlahir,
            'nik' => $query->nik,
            'nobpjs' => $query->nobpjs,
            'nokk' => null,
            'kewarganegaraan' => "Indonesia",
            'jeniskelaminfk' => $query->objectjeniskelaminfk != null ? (int)$query->objectjeniskelaminfk : null,
            'agamafk' => $query->objectagamafk != null ? (int)$query->objectagamafk : null,
            'golongandarahfk' => $query->objectgolongandarahfk != null ? (int)$query->objectgolongandarahfk : null,
            'pekerjaanfk' => $query->objectpekerjaanfk != null ? (int)$query->objectpekerjaanfk : null,
            'pendidikanfk' => $query->objectpendidikanfk != null ? (int)$query->objectpendidikanfk : null,
            'statusperkawinanfk' => $query->objectstatusperkawinanfk != null ? (int)$query->objectstatusperkawinanfk : null,
            'notelpon' => $query->notelepon,
            'nohp' => $query->nohp,

        );
        $dataAlamat =array(
            'nik' => $query->nik,
            'alamatlengkap' => $query->alamatlengkap,
            'desakelurahanfk' => $query->objectdesakelurahanfk != null ? (int)$query->objectdesakelurahanfk : null,
            'rtrw' => $query->rtrw,
            'kecamatanfk' => $query->objectkecamatanfk != null ? (int)$query->objectkecamatanfk : null,
            'kotakabupatenfk' => $query->objectkotakabupatenfk != null ? (int)$query->objectkotakabupatenfk : null,
            'provinsifk' => $query->objectpropinsifk != null ? (int)$query->objectpropinsifk : null,
            'negarafk' => $query->objectnegarafk != null ? (int)$query->objectnegarafk : null,
            'kodepos' => $query->kodepos,
        );

        $dataEMR = array(
            "nik" => $query->nik,
            "noregistrasi" => $query->noregistrasi,
            "profilefk" => $profileRS,
            "tglregistrasi" => $query->tglregistrasi,
            "norm" => $query->nocm,
            "tglpulang" => $query->tglpulang,
            "norujukan" => $query->norujukan,
            "tglrujukan" => $query->tglrujukan,
            "nosep" => $query->nosep,
            "tglsep" =>$query->tglsep != null && $query->tglsep != '' ? $query->tglsep : null,
            "ppkpelayanan" => $query->ppkrujukan,
            "diagnosafk" => $query->objectdiagnosafk,
            "lokasilakalantas" => $query->lokasilakalantas,
            "penjaminlaka" => $query->penjaminlaka,
            "cob" => $query->cob,
            "katarak" => $query->katarak,
            "keteranganlaka" => $query->keteranganlaka,
            "tglkejadian" => $query->tglkejadian,
            "suplesi" => $query->suplesi,
            "nosepsuplesi" => $query->nosepsuplesi,
            "iddpjp" => $query->iddpjp,
            "dpjp" => $query->namadokter,
            "prolanisprb" => $query->prolanisprb,
            "kelasfk" => $query->kelastgfk ==  null ? $query->objectkelasfk :  $query->kelastgfk,
            "kddiagnosa" => $query->kddiagnosa,
            "namadiagnosa" => $query->namadiagnosa,
            "statuscovidfk" => $query->statuscovidfk,
        );


//      return $this->respond($dataEMR);
        $getSign = $this->getSignatureTrans($kdProfile);
        // return $this->respond($getSign->{'X-AUTH-TOKEN'});
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);

        if($dataPasien['nik'] != null  ){
            $curl = curl_init();
            $dataJsonSend = json_encode($dataPasien);
            curl_setopt_array($curl, array(
                CURLOPT_URL=>  $baseUrl.'save-pasien',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $dataJsonSend,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json;",
                    "X-AUTH-TOKEN: ".  (string)$token,

                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $result= "cURL Error #:" . $err;
            } else {
                $result =  json_decode($response);
                return $this->respond($this->syncAlamat($kdProfile,$dataAlamat,$dataEMR,$profileRS));
            }

        }
    }

    function syncUpdateStatusPasienCovid(Request $request){
        // $kdProfile = $this->getDataKdProfile($request);
        $kdProfile = $this->getDataKdProfile($request);
        $profileRS = 10632;
        $getSign = $this->getSignatureTrans($kdProfile);
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);
//        $client = new \GuzzleHttp\Client();
        $data =$request['data'];
        $pasien = $this->getPasien($kdProfile,$token,$data['nik']);
        if(isset($pasien[0])){
            $data['pasienfk'] = $pasien[0]->id;
        }


        $noregis = $data['noregistrasi'];
//        $pelayananMedis = DB::select(DB::raw("
//            select apd.norec,$profileRS as profilefk,apd.tglmasuk,
//            case when apd.tglkeluar is not null then apd.tglkeluar else null end as tglkeluar,
//            ru.kdinternal as ruanganfk,apd.objectpegawaifk as iddpjp,pg.namalengkap as dpjp,pd.statuscovidfk,pd.statuscovid
//            from antrianpasiendiperiksa_t  as apd
//            join ruangan_m as ru on ru.id =apd.objectruanganfk
//            join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//            left join pegawai_m as pg on pg.id =apd.objectpegawaifk
//            left join statuscovid_m as sc on sc.id = pd.statuscovidfk
//            where pd.noregistrasi='$noregis'
//            and pd.statusenabled=true
//            and pd.kdprofile=$kdProfile"));

//           return $pelayananMedis;

        $curl = curl_init();
        $dataJsonSend = json_encode ($data);
//        return $this->respond(   $baseUrl.'save-medical-record');
        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  $baseUrl.'update-statuspasiencovid',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "X-AUTH-TOKEN: ".  (string)$token,

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            if(empty(json_decode($response))){
                $result = $response;
            }else{
                $result =  json_decode($response);
            }

        }

        return $this->respond($result );

    }
    

   public function updateStatusCovid(Request $request){
        // $kdProfile = $this->getDataKdProfile($request);
        $kdProfile = $this->getDataKdProfile($request);
        $profileRS = 10632;
        $getSign = $this->getSignatureTrans($kdProfile);
        $token = $getSign->{'X-AUTH-TOKEN'};
        $baseUrl = $this->settingDataFixed('urlTransdataHealthCare',$kdProfile);

        $curl = curl_init();

        $dataJsonSend = json_encode ($request['data']);
//        return $this->respond(   $baseUrl.'save-medical-record');
        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  $baseUrl.'update-status-covid',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "X-AUTH-TOKEN: ".  (string)$token,

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            if(empty(json_decode($response))){
                $result = $response;
            }else{
                $result =  json_decode($response);
            }

        }

        return $this->respond($result );

    }
     public function getDatfarSyncTransNEW(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftjoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('asuransipasien_m as asu', 'pa.objectasuransipasienfk', '=', 'asu.id')
            ->leftjoin('kelas_m as klstg','klstg.id','=','asu.objectkelasdijaminfk')
             ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftjoin('golongandarah_m as gd', 'gd.id', '=', 'ps.objectgolongandarahfk')
            ->leftjoin('pendidikan_m as pdd', 'pdd.id', '=', 'ps.objectpendidikanfk')
            ->leftjoin('agama_m as agm', 'agm.id', '=', 'ps.objectagamafk')
            ->leftjoin('statusperkawinan_m as stt', 'stt.id', '=', 'ps.objectstatusperkawinanfk')
            ->leftjoin('diagnosa_m as dg', 'dg.id', '=', 'pa.objectdiagnosafk')
            ->leftjoin('statuscovid_m as sc', 'sc.id', '=', 'pd.statuscovidfk')
            ->select('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien','ps.noidentitas','ps.nobpjs',
                'kp.kelompokpasien', 'rek.namarekanan','pg.id as iddpjp', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pa.norec as norec_pa', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'pa.nosep as nosep', 'pd.nostruklastfk','klstg.namakelas as kelasditanggung','kls.namakelas',
                'ps.tgllahir','ru.objectdepartemenfk', 'pd.objectkelasfk','dept.id as deptid','pa.ppkrujukan',
                'ps.objectjeniskelaminfk','ps.objectgolongandarahfk','ps.objectpendidikanfk','ps.objectagamafk','pa.prolanisprb',
                'ps.objectstatusperkawinanfk','ps.tempatlahir','ps.notelepon','ps.nohp','ps.objectpekerjaanfk','pa.norujukan',
                'pa.tglrujukan','pa.tanggalsep as tglsep','pa.ppkrujukan','pa.objectdiagnosafk','klstg.nourut as kelastgfk','pa.keteranganlaka',
                'pa.lokasilakalantas','pa.lakalantas','pa.penjaminlaka','pa.cob','pa.katarak','pa.tglkejadian','pa.suplesi','pa.nosepsuplesi',
                'dg.kddiagnosa','dg.namadiagnosa','pd.statuscovidfk','sc.status as statuscovid')
            ->where('pd.statusenabled',true)
//            ->whereNotNull('pd.tglpulang')
            ->where('pd.kdprofile', (int)$kdProfile);

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

        $data = $data->get();

        // $data2=[];
        // foreach ($data as $key => $value) {
        //    $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
        //     ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
        //     ->where('ddp.objectjenisdiagnosafk',1)
        //     ->where('apd.noregistrasifk',$value->norec)
        //     ->where('apd.kdprofile', (int)$kdProfile)
        //     ->first();
        //     if(!empty($diagnosa)){
        //         $value->isdiagnosis = true;
        //     }else{
        //         $value->isdiagnosis = false;
        //     }
        //     $data2 []=  $value;
           
        //     // $i = $i+1;
        // }
       
        // for ($i = count($data2) - 1; $i >= 0; $i--) {
        //     if (isset($filter['isnotdiagnosis']) && $filter['isnotdiagnosis'] != "" && $filter['isnotdiagnosis'] != "undefined" && $filter['isnotdiagnosis'] != 'false' && $data2[$i]->isdiagnosis == true){
        //          array_splice($data2,$i,1);

        //     } 
        // }
        // return $this->respond($data2);
        foreach ($data as $key => $value) {
             $diagnosa = \DB::table('antrianpasiendiperiksa_t AS apd')
                    ->join('detaildiagnosapasien_t AS ddp','ddp.noregistrasifk','=','apd.norec')
                    ->join('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
                    ->select('dg.kddiagnosa','dg.namadiagnosa')
                    ->where('ddp.objectjenisdiagnosafk',1)
                    ->where('dg.kddiagnosa','<>','-')
                     ->where('apd.kdprofile', (int)$kdProfile)
                    ->where('apd.noregistrasifk',$value->norec)
                    // ->where('apd.kdprofile', (int)$kdProfile)
                    ->first();
            if(!empty($diagnosa)){
                $value->kddiagnosa =$diagnosa->kddiagnosa;
                $value->namadiagnosa =$diagnosa->namadiagnosa;
                $value->isdiagnosis = true;
                // $data2 []=  $value;
            }else if( $value->kddiagnosa !=null){
                $value->isdiagnosis = true;
            }else{
                $value->isdiagnosis = false;
            }
         }
        return $this->respond($data);
    }

    public function saveNomorSuratKeteranganKematian(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $keterangan = "";
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();
        $pasien= DB::table('pasiendaftar_t as pd')
            ->join('ruangan_m as ru','pd.objectruanganlastfk','=','ru.id')
            ->join('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->select('pd.norec','pd.noregistrasi','ru.namaruangan','pd.tglpulang','pd.nocmfk')
            ->where('pd.noregistrasi','=',$request['noregistrasi'])
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.statusenabled',true)
            ->first();
        DB::beginTransaction();
        try {
            $masterPasien= DB::table('pasien_m')
                            ->select(DB::raw("nosuratkematian"))
                            ->where('id', $pasien->nocmfk)
                            ->first();
//            return $this->respond($masterPasien);
            if ($masterPasien->nosuratkematian == null){
                $keterangan = "Input Nomor Surat Keterangan Kematian";
                $dataPM = Pasien::where('id', $pasien->nocmfk)
                    ->update([
                        'nosuratkematian' => $request['nosurat'],
                    ]);
            }else{
                $keterangan = "Update Nomor Surat Keterangan Kematian";
                $dataSO = new SuratKematianPasienDelete();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
                $dataSO->nocmfk = $pasien->nocmfk;
                $dataSO->nosuratkematian = $masterPasien->nosuratkematian;
                $dataSO->pasiendaftarfk = $pasien->norec;
                $dataSO->status = "Update";
                $dataSO->save();

                $dataPM = Pasien::where('id', $pasien->nocmfk)
                    ->update([
                        'nosuratkematian' => $request['nosurat'],
                    ]);
            }

            /*Logging User*/
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = $keterangan;
            $logUser->noreff =$pasien->norec;
            $logUser->referensi='norec pasiendaftar';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = $keterangan. ' Pasien Dengan No Registrasi '. $pasien->noregistrasi;
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
                "pasien" => $pasien,
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

    public function HapusNomorSuratKeteranganKematian(Request $request){
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
            ->select('pd.norec','pd.noregistrasi','ru.namaruangan','pd.tglpulang','pd.nocmfk')
            ->where('pd.noregistrasi','=',$request['noregistrasi'])
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.statusenabled',true)
            ->first();
//        return $this->respond($pasien->noregistrasi);
        $keterangan = '';
        DB::beginTransaction();
        try {

            $masterPasien= DB::table('pasien_m')
                ->select(DB::raw("nosuratkematian"))
                ->where('id', $pasien->nocmfk)
                ->first();

            $dataSO = new SuratKematianPasienDelete();
            $dataSO->norec = $dataSO->generateNewId();
            $dataSO->kdprofile = $idProfile;
            $dataSO->statusenabled = true;
            $dataSO->nocmfk = $pasien->nocmfk;
            $dataSO->nosuratkematian = $masterPasien->nosuratkematian;
            $dataSO->pasiendaftarfk = $pasien->norec;
            $dataSO->status = "Hapus";
            $dataSO->save();

            $dataPM = Pasien::where('id', $pasien->nocmfk)
                ->update([
                    'nosuratkematian' => null,
                ]);

            /*Logging User*/
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $kdProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = "Hapus Nomor Surat Keterangan Kematian";
            $logUser->noreff =$pasien->norec;
            $logUser->referensi='norec pasiendaftar';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Hapus Nomor Surat Keterangan Kematian Pasien Dengan No Registrasi '. $pasien->noregistrasi;
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

    public function getDataNoSuratKeteranganKematian(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as pm','pm.id','=','pd.nocmfk')
            ->select('pm.nosuratkematian')
            ->where('pd.noregistrasi',$request['norec_pd'])
            ->where('pd.statusenabled',true)
            ->where('pd.kdprofile',$kdProfile)
            ->get();

        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function savePelimpahanRuangJenazah(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();
        $keterangan = '';
        DB::beginTransaction();
        try {

            if ($request['norec'] == "") {
                $keterangan = "Input Pelimpahan Ke Ruangn Jenazah";
                $dataSO = new SuratPelimpahanJenazah();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $kdProfile;
                $dataSO->statusenabled = true;
                $dataSO->tglsurat = $tglAyeuna;
                $dataSO->pasiendaftarfk = $request['norec_pd'];
            } else {
                $keterangan = "Ubah  Pelimpahan Ke Ruangn Jenazah";
                $dataSO = SuratPelimpahanJenazah::where('norec',$request['norec'])->first();
            }
                $dataSO->nosurat = $request['nosurat'];
                $dataSO->pasiendaftarfk = $request['pasiendaftarfk'];
                $dataSO->petugasruanganfk = $request['petugasruanganfk'];
                $dataSO->jabatanfk = $request['jabatanfk'];
                $dataSO->umur = $request['umur'];
                $dataSO->petugasjenazahfk = $request['petugasjenazahfk'];
                $dataSO->penanggungjawab = $request['penanggungjawab'];
                $dataSO->objectjeniskelaminfk = $request['objectjeniskelaminfk'];
                $dataSO->objecthubungankeluargafk = $request['objecthubungankeluargafk'];
                $dataSO->noidentitas = $request['noidentitas'];
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
            $logUser->referensi='Norec Surat Pelimpahan Jenazah';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = $keterangan . ' Pasien Dengan No Registrasi '. $request['noregistrasi'];
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

    public function hapusPelimpahanRuangJenazah(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id', $dataLogin['userData']['id'])
            ->where('lu.kdprofile', $kdProfile)
            ->first();
        $keterangan = '';
        try{
            $dataSO = SuratPelimpahanJenazah::where('norec',$request['norec'])
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
            $logUser->jenislog = "Hapus Surat Perlimpahan Jenazah";
            $logUser->noreff  = $request['norec'];
            $logUser->referensi='Norec Surat Permohonan Jenazah';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = "Hapus Surat Perlimpahan Jenazah Noregistrasi " .$request['noregistrasi'];
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

    public function getDataPelimpahanJenazah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('suratpelimpahanjenazah_t AS spj','spj.pasiendaftarfk','=', 'pd.norec')
            ->leftjoin ('jeniskelamin_m AS jk','jk.id','=','spj.objectjeniskelaminfk')
            ->leftjoin ('hubungankeluarga_m AS hk','hk.id','=','spj.objecthubungankeluargafk')
            ->leftjoin ('pegawai_m AS pg','pg.id','=','spj.petugasruanganfk')
            ->leftjoin ('pegawai_m AS pg1','pg1.id','=','spj.petugasjenazahfk')
            ->leftjoin ('jabatan_m AS jb','jb.id','=','spj.jabatanfk')
            ->select(DB::raw("spj.*,jk.jeniskelamin,hk.hubungankeluarga,pg.namalengkap AS namapetugas,
                                    pg1.namalengkap AS namapetugasjenazah,jb.namajabatan"))
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
}