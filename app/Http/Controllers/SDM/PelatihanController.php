<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 11/12/2018
 * Time: 16.37
 */
namespace App\Http\Controllers\SDM;
use App\Http\Controllers\ApiController;
use App\Master\AkreditasiPelatihan;
use App\Master\AsalProduk;
use App\Master\Eselon;
use App\Master\HargaNettoProdukByKelas1;
use App\Master\HargaNettoProdukByKelasD;
use App\Master\Jabatan;
use App\Master\JenisKelamin;
use App\Master\JenisPelatihan;
use App\Master\JenisTarif;
use App\Master\Kelas;
use App\Master\MapPelatihanPaketToJabatan;
use App\Master\MapRuanganToProduk;
use App\Master\Narasumber;
use App\Master\Pendidikan;
use App\Master\Ruangan;
use App\Master\SDM_Golongan;
use App\Master\UnitKerjaPegawai;
use App\Transaksi\EvaluasiNarasumber;
use App\Transaksi\EvaluasiNarasumberDetail;
use App\Transaksi\EvaluasiPenyelenggara;
use App\Transaksi\EvaluasiPenyelenggaraDetail;
use App\Transaksi\IndikatorRensar;
use App\Transaksi\LoggingUser;
use App\Transaksi\MonitoringPengajuanPelatihan;
use App\Transaksi\MonitoringPengajuanPelatihanDetail;
use App\Transaksi\PelatihanJPL;
use App\Transaksi\PelatihanKreditAkreditasi;
use App\Transaksi\PelatihanPaket;
use App\Transaksi\PlanningDiklatHumasMarket;
use App\Transaksi\PlanningDiklatHumasMarketBiaya;
use App\Transaksi\StrukAgenda;
use App\Transaksi\StrukAgendaDetail;
use App\Transaksi\StrukPlanning;
use App\Transaksi\StrukPlanningDetail;
use App\Transaksi\StrukRealisasi;
use App\Transaksi\StrukVerifikasi;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use DB;
use App\Traits\Valet;
use App\Traits\SettingDataFixedTrait;
use App\User;


class PelatihanController extends ApiController {

    use Valet, SettingDataFixedTrait;

    public function __construct() {
        parent::__construct($skip_authentication=false);
      
    }

    public function getJenisPelatihan(Request $request){
        $data = JenisPelatihan::where('statusenabled',true)
            ->where('kdprofile',(int) $this->getDataKdProfile($request))
            ->get();
        $result = array(
            'data' => $data,
            'by' => 'inhuman',
        );
        return $result;
    }

    public function saveJenisPelatihan(Request $request){
        DB::beginTransaction();
        try{
            $newId = JenisPelatihan::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new JenisPelatihan();
                $TP->id = $newId;
                $TP->kdprofile = (int) $this->getDataKdProfile($request);
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = JenisPelatihan::where('id', $request['id'])->first();
            }
            $TP->statusenabled =  $request['statusenabled'];
            $TP->kodeexternal = $request['kodeexternal'];
            $TP->reportdisplay = $request['jenispelatihan'];
            $TP->jenispelatihan = $request['jenispelatihan'];
            $TP->namaexternal = $request['jenispelatihan'];
            $TP->save();

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
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function savePaketPelatihan(Request $request){
        DB::beginTransaction();
        try{
            //region @Save PelatihanPaket
            $ReqPP = $request['pelatihanpaket'];
            $PP = new PelatihanPaket();
            $PP->norec = $PP->generateNewId();
            $PP->kdprofile = (int) $this->getDataKdProfile($request);
            $PP->statusenabled = true;
            $PP->akreditasipelatihanoleh = $ReqPP['akreditasipelatihanoleh'];
            $PP->objectjenispelatihan  = $ReqPP['objectjenispelatihan'];
            $PP->jumlahnarasumber = $ReqPP['jumlahnarasumber'];
            $PP->kapasitaspeserta = $ReqPP['kapasitaspeserta'];
            $PP->namapaketpelatihan = $ReqPP['namapaketpelatihan'];
            $PP->penyelenggara = $ReqPP['penyelenggara'];
            $PP->objectprodukfk = $ReqPP['objectprodukfk'];
            $PP->tanggalahir = $ReqPP['tanggalahir'];
            $PP->tanggalakreditasi = $ReqPP['tanggalahir'];
            $PP->tanggalawal = $ReqPP['tanggalawal'];
            $PP->tempat = $ReqPP['tempat'];
            $PP->objectjenispelatihanfk = $ReqPP['objectjenispelatihanfk'];
            $PP->objectpegawaifk = $ReqPP['objectpegawaifk'];
            $PP->save();
            $norecPelPaket =  $PP->norec;
            //endregion

            //region @Save MapPelatihanPaketToJabtan
            foreach ($request['mappelatihanpakettojabatan'] as $itemMap){
                $idMap = MapPelatihanPaketToJabatan::max('id') +1 ;
                $MapPP = new MapPelatihanPaketToJabatan();
                $MapPP->id = $idMap;
                $MapPP->kdprofile = (int) $this->getDataKdProfile($request);
                $MapPP->statusenabled = true;
                $MapPP->kodeexternal = null;
                $MapPP->namaexternal  = null;
                $MapPP->norec = $MapPP->generateNewId();;
                $MapPP->reportdisplay = null;
                $MapPP->objectjabatanfk = $itemMap['objectjabatanfk'];
                $MapPP->objectpeatihanfk = $norecPelPaket;
                $MapPP->save();
            }
            //endregion

            //region @Save PelatihanJPL
            foreach ($request['pelatihanjpl'] as $itemJPL){
                $JPL = new PelatihanJPL();
                $JPL->norec = $JPL->generateNewId();;
                $JPL->kdprofile = (int) $this->getDataKdProfile($request);
                $JPL->statusenabled = true;
                $JPL->objecteselonfk = $itemJPL['objecteselonfk'];
                $JPL->objectgolonganfk  = $itemJPL['objectgolonganfk'];
                $JPL->jpl = $itemJPL['jpl'];
                $JPL->objectpeatihanfk = $norecPelPaket;
                $JPL->save();
            }
            //endregion

            //region @Save PelatihanKreditAkreditasi
            foreach ($request['pelatihanjpl'] as $itemJPL){
                $JPL = new PelatihanKreditAkreditasi();
                $JPL->norec = $JPL->generateNewId();;
                $JPL->kdprofile = (int) $this->getDataKdProfile($request);
                $JPL->statusenabled = true;
                $JPL->jeniskeperawatan = $itemJPL['jeniskeperawatan'];
                $JPL->jpl = $itemJPL['jpl'];
                $JPL->objectpeatihanfk = $norecPelPaket;
                $JPL->save();
            }
            //endregion

            //region @Save StrukPlanning
            $ReqStrukP = $request['strukplanning'];
            $StrukP = new StrukPlanning();
            $StrukP->norec = $StrukP->generateNewId();;
            $StrukP->kdprofile = (int) $this->getDataKdProfile($request);
            $StrukP->statusenabled = true;
            $StrukP->nocmfk = $ReqStrukP['nocmfk'];
            $StrukP->nomasukfk = $ReqStrukP['nomasukfk'];
            $StrukP->noregistrasifk = $ReqStrukP['noregistrasifk'];
            $StrukP->objectalamattujuanfk = $ReqStrukP['objectalamattujuanfk'];
            $StrukP->objectasalanggaranfk = $ReqStrukP['objectasalanggaranfk'];
            $StrukP->objectdokumenskfk = $ReqStrukP['objectdokumenskfk'];
            $StrukP->objectjenisanggaranfk = $ReqStrukP['objectjenisanggaranfk'];
            $StrukP->objectjenistempatfk = $ReqStrukP['objectjenistempatfk'];
            $StrukP->objectkelompoktransaksifk = $ReqStrukP['objectkelompoktransaksifk'];
            $StrukP->objectpegawaipjawabevaluasifk = $ReqStrukP['objectpegawaipjawabevaluasifk'];
            $StrukP->objectperiodeaccountfk = $ReqStrukP['objectperiodeaccountfk'];
            $StrukP->objectrekananfk = $ReqStrukP['objectrekananfk'];
            $StrukP->objectruanganasalfk = $ReqStrukP['objectruanganasalfk'];
            $StrukP->objectruanganfk = $ReqStrukP['objectruanganfk'];
            $StrukP->kesimpulanplanning_askep = $ReqStrukP['kesimpulanplanning_askep'];
            $StrukP->keteranganlainnya = $ReqStrukP['keteranganlainnya'];
            $StrukP->namaplanning = $ReqStrukP['namaplanning'];
            $StrukP->namarekanan = $ReqStrukP['namarekanan'];
            $StrukP->noplanning = $ReqStrukP['noplanning'];
            $StrukP->noplanning_intern = $ReqStrukP['noplanning_intern'];
            $StrukP->nourutlogin = $ReqStrukP['nourutlogin'];
            $StrukP->nourutruangan = $ReqStrukP['nourutruangan'];
            $StrukP->qtyharisiklus = $ReqStrukP['qtyharisiklus'];
            $StrukP->rincianexecuteplanning_askep = $ReqStrukP['rincianexecuteplanning_askep'];
            $StrukP->objectpegawaipjawabfk = $ReqStrukP['objectpegawaipjawabfk'];
            $StrukP->tglevaluasiplanning_askep = $ReqStrukP['tglevaluasiplanning_askep'];
            $StrukP->tglpengajuan = $ReqStrukP['tglpengajuan'];
            $StrukP->tglplanning = $ReqStrukP['tglplanning'];
            $StrukP->tglsiklusakhir = $ReqStrukP['tglsiklusakhir'];
            $StrukP->tglsiklusawal = $ReqStrukP['tglsiklusawal'];
            $StrukP->objectasalsukucadangfk = $ReqStrukP['objectasalsukucadangfk'];
            $StrukP->objectjenispekerjaanfk = $ReqStrukP['objectjenispekerjaanfk'];
            $StrukP->objectrekananskkontrakdetailfk = $ReqStrukP['objectrekananskkontrakdetailfk'];
            $StrukP->objectrekananskkontrakfk = $ReqStrukP['objectrekananskkontrakfk'];
            $StrukP->objectstatuspekerjaanfk = $ReqStrukP['objectstatuspekerjaanfk'];
            $StrukP->jenisharga = $ReqStrukP['jenisharga'];
            $StrukP->siklus = $ReqStrukP['siklus'];
            $StrukP->save();
            $noPlanningfk =  $StrukP->norec;
            //endregion

            //region @Save PlanningDiklatHumasMarket
            $reqPDH = $request['planningdiklathumasmarket'];
            $PDH = new PlanningDiklatHumasMarket();
            $PDH->norec = $PDH->generateNewId();;
            $PDH->kdprofile = (int) $this->getDataKdProfile($request);
            $PDH->statusenabled = true;
            $PDH->deskripsidetial = 'Paket Pelatihan';
            $PDH->noplanningfk = $noPlanningfk;
            $PDH->kdprodukdhm = '-';
            $PDH->kdproduk = $reqPDH['kdproduk'];
            $PDH->qtypeserta = $reqPDH['qtypeserta'];
            $PDH->tglplanning = $reqPDH['tglplanning'];
            $PDH->tglplanningakhir = $reqPDH['tglplanningakhir'];
            $PDH->save();
            //endregion

            //region @Save PlanningDiklatHumasMarketBiaya
            foreach ($request['planningdiklathumasmarketbiaya'] as $itemBiaya){
                $persenPpn = 0;
                $persenDiskon = 0;
                $ppn = 0;
                $hargaSatuan = 0;

                $PDHB = new PlanningDiklatHumasMarketBiaya();
                $PDHB->norec = $PDHB->generateNewId();;
                $PDHB->kdprofile = (int) $this->getDataKdProfile($request);
                $PDHB->statusenabled = true;
                $PDHB->deskripsidetailproduk = 'Paket Pelatihan';
                $PDHB->hargadiskon = '-';
                $PDHB->hargapph = $itemBiaya['hargapph'];
                $PDHB->hargappn = $itemBiaya['hargappn'];
                $PDHB->hargasatuan = $itemBiaya['hargasatuan'];
                $PDHB->hargatambahan = $itemBiaya['hargatambahan'];
                $PDHB->isinout = $itemBiaya['isinout'];
                $PDHB->keterangan = $itemBiaya['keterangan'];
                $PDHB->noplanning = $itemBiaya['noplanning'];
                $PDHB->noretur = $itemBiaya['noretur'];
                $PDHB->nostruk = $itemBiaya['nostruk'];
                $PDHB->noverifikasi = $itemBiaya['noverifikasi'];
                $PDHB->persendiskon = $itemBiaya['persendiskon'];
                $PDHB->qtyproduk = $itemBiaya['qtyproduk'];
                $PDHB->tglplanning = $itemBiaya['tglplanning'];
                $PDHB->pegawaifk = $itemBiaya['pegawaifk'];
                $PDHB->produkfk = $itemBiaya['produkfk'];
                $PDHB->kdprodukdhm = $itemBiaya['kdprodukdhm'];
                $PDHB->produkkelasfk = $itemBiaya['produkkelasfk'];
                $PDHB->asalprodukfk = $itemBiaya['produkkelasfk'];
                $PDHB->issetbiayapeserta = $itemBiaya['issetbiayapeserta'];
                $PDHB->save();
            }
            //endregion

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
                'result' => '',
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getComboPelatihan(Request $request){
        // return $this->respond((int) $this->getDataKdProfile($request)) ;
        $req = $request->all();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk','lu.objectkelompokuserfk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile',(int) $this->getDataKdProfile($request))
            ->first();
        $produk  = \DB::table('produk_m as st')
            ->select('st.id','st.namaproduk')
            ->where('st.statusenabled',true)
            ->where('st.kdprofile',(int) $this->getDataKdProfile($request))
            ->orderBy('st.namaproduk');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $produk = $produk->where('st.namaproduk','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $produk = $produk->take(10);
        $produk = $produk->get();

        $asal = AsalProduk::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $kelas = Kelas::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $jenisTarif = JenisTarif::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $jenisPelatihan = JenisPelatihan::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $akreditasiPel = AkreditasiPelatihan::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $eselon = Eselon::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $golongan = SDM_Golongan::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $jabatan = Jabatan::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
//        $indikator = IndikatorRensar::where('statusenabled',true)->get();
        $pendidikan = Pendidikan::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $jeniskelamin = JenisKelamin::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $narasumber = Narasumber::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $UnitKerja = UnitKerjaPegawai::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))->get();
        $RuanganPelatihan = Ruangan::where('id',222)
                            ->where('statusenabled', true)
                            ->where('kdprofile',(int) $this->getDataKdProfile($request))
                            ->get();

        $dataLoginFull = \DB::table('mappegawaijabatantounitkerja_m as mp')
            ->join('pegawai_m as pg','pg.id','=','mp.objectpegawaifk')
            ->join('jabatan_m as jb','jb.id','=','mp.objectjabatanfk')
            ->join('unitkerjapegawai_m as uk','uk.id','=','mp.objectunitkerjapegawaifk')
            ->leftJoin('jenisjabatan_m as jj','jj.id','=','jb.objectjenisjabatanfk')
            ->join('loginuser_s as lu','lu.objectpegawaifk','=','pg.id')
            ->select('mp.objectpegawaifk','pg.namalengkap','mp.objectjabatanfk','jb.namajabatan',
                     'jb.objectjenisjabatanfk','jj.jenisjabatan','mp.objectunitkerjapegawaifk',
                     'uk.name as unitkerjapegawai','lu.id as userid','lu.namauser')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('pg.kdprofile',(int) $this->getDataKdProfile($request))
            ->orderBy('pg.id')
            ->get();

        $result = array(
            'produk'  => $produk,
            'asalproduk' => $asal,
            'kelas' => $kelas,
            'jenistarif' => $jenisTarif,
            'jenispelatihan' =>$jenisPelatihan,
            'akreditasipelatihan'=> $akreditasiPel,
            'golongan' => $golongan,
            'eselon' => $eselon,
            'jabatan' => $jabatan,
//            'indikator' => $indikator,
            'pendidikan' => $pendidikan,
            'jeniskelamin' =>$jeniskelamin,
            'narasumber' => $narasumber,
            'datalogin'=> $dataLogin,
            'datapegawai' => $dataPegawai,
            'unitkerjapegawai' => $UnitKerja,
            'datalogin' => $dataLoginFull,
            'ruanganpelatihan' => $RuanganPelatihan,
            'by' => 'inhuman',
        );

        return $this->respond($result);
    }
    public function saveBiayaDiklat(Request $request){
        DB::beginTransaction();
        try{

            //region @Save HargaNettoProdukByKelas
            $newId = HargaNettoProdukByKelas1::max('id') + 1 ;
            if ($request['id'] == ''){
                $HN = new HargaNettoProdukByKelas1();
                $HN->id = $newId;
                $HN->kdprofile = (int) $this->getDataKdProfile($request);
                $HN->norec = $HN->generateNewId();
                $HN->statusenabled =true;

                //region @Save MapRuanganToProduk
                $Setting = $this->getGlobalSettingDataFixed('idRuanganPelatihan');
                $cekMap = MapRuanganToProduk::where('objectprodukfk', $request['objectprodukfk'])
                    ->where('objectruanganfk',$Setting)
                    ->get();
                if(count($cekMap) == 0){
                    $MRTP = new MapRuanganToProduk();
                    $MRTP->id = MapRuanganToProduk::max('id') + 1;
                    $MRTP->kdprofile = (int) $this->getDataKdProfile($request);
                    $MRTP->norec = $MRTP->generateNewId();
                    $MRTP->statusenabled =true;
                    $MRTP->namaexternal = 'Biaya Diklat';
                    $MRTP->objectruanganfk = $Setting;
                    $MRTP->objectprodukfk = $request['objectprodukfk'];
                    $MRTP->save();
                }
                //endregion

            }else{
                $HN = HargaNettoProdukByKelas1::where('id', $request['id'])->first();
            }
            $HN->namaexternal =  'Biaya Diklat';
            $HN->objectasalprodukfk = $request['objectasalprodukfk'];
            $HN->objectjenistariffk = $request['objectjenistariffk'];
            $HN->objectkelasfk = $request['objectkelasfk'];
            $HN->objectprodukfk = $request['objectprodukfk'];
            $HN->hargadiscount = $request['hargadiscount'];
            $HN->hargasatuan = $request['hargasatuan'];
            $HN->persendiscount = $request['persendiscount'];
            $HN->harganetto1 = $request['hargasatuan'];
            $HN->harganetto2 = $request['hargasatuan'];
            $HN->tglberlakuawal = $request['tglberlakuawal'];
            $HN->tglberlakuakhir = $request['tglberlakuakhir'];
            $HN->save();
            //endregion

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
                'result' => $HN,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarBiayaDiklat(Request $request){
        $ruangPelatihan = $this->getGlobalSettingDataFixed('idRuanganPelatihan');
        $data = \DB::table('harganettoprodukbykelas_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('jenistarif_m as jt','jt.id','=','hnp.objectjenistariffk')
            ->join ('asalproduk_m as as','as.id','=','hnp.objectasalprodukfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->select('hnp.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan','kls.namakelas',
                'jt.jenistarif','hnp.objectjenistariffk','hnp.hargadiscount','hnp.persendiscount',
                'hnp.objectasalprodukfk','as.asalproduk','hnp.tglberlakuawal','hnp.tglberlakuakhir',
                'hnp.hargadiscount','mpr.objectprodukfk'
            )
            ->where('ru.id', $ruangPelatihan)
            ->where('hnp.namaexternal','Biaya Diklat')
            ->where('mpr.statusenabled',true)
            ->where('hnp.statusenabled',true)
             ->where('hnp.kdprofile',(int) $this->getDataKdProfile($request)) ;

        $data = $data->orderBy('prd.namaproduk', 'ASC');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function hapusBiayaDiklat(Request $request){
    DB::beginTransaction();
    try{
        $data = \DB::table('harganettoprodukbykelas_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
            ->select('mpr.id')
            ->where('hnp.id',$request['id'])
             ->where('hnp.kdprofile',(int) $this->getDataKdProfile($request))
            ->first();
        HargaNettoProdukByKelas1::where('id',$request['id'])->update(['statusenabled'=>false]);
        MapRuanganToProduk::where('namaexternal','Biaya Diklat')
            ->where('id', $data->id)
            ->update(['statusenabled' => false]);
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
//            'result' => $HN,
            'as' => 'inhuman',
        );
    } else {
        $transMessage = 'Hapus Gagal';
        DB::rollBack();
        $result = array(
            'status' => 400,
            'message'  => $transMessage,
            'as' => 'inhuman',
        );
    }
    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
}
    public function  getProdukPart(Request $request){
        $req = $request->all();
        $dataProd = \DB::table('produk_m as dg')
            ->select('dg.id','dg.namaproduk as namaProduk' )
            ->where('dg.statusenabled', true)
            ->where('dg.kdprofile',(int) $this->getDataKdProfile($request))
            ->orderBy('dg.namaproduk');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProd = $dataProd
                ->where('dg.namaproduk','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }
        if(isset($req['namaProduk']) && $req['namaProduk']!="" && $req['namaProduk']!="undefined"){
            $dataProd = $dataProd
                ->where('dg.namaproduk','ilike','%'.$req['namaProduk'].'%' );
        }

        $dataProd=$dataProd->take(10);
        $dataProd=$dataProd->get();

        return $this->respond($dataProd);
    }
    public function getAkreditasiPelatihan(Request $request){
        $data = AkreditasiPelatihan::where('statusenabled',true)->where('kdprofile',(int) $this->getDataKdProfile($request))
            ->get();
        $result = array(
            'data' => $data,
            'by' => 'inhuman',
        );
        return $result;
    }
    public function saveAkreditasiPelatihan(Request $request){
        DB::beginTransaction();
        try{
            $newId = AkreditasiPelatihan::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new AkreditasiPelatihan();
                $TP->id = $newId;
                $TP->kdprofile = (int) $this->getDataKdProfile($request);
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = AkreditasiPelatihan::where('id', $request['id'])->first();
            }
            $TP->statusenabled =  $request['statusenabled'];
            $TP->kodeexternal = $request['kodeexternal'];
            $TP->reportdisplay = $request['akreditasipelatihan'];
            $TP->akreditasipelatihan = $request['akreditasipelatihan'];
            $TP->namaexternal = $request['akreditasipelatihan'];
            $TP->save();

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
                'result' => $TP,
                'as' => 'inhuman',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function savePengajuanPelatihan(Request $request) {
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile',(int) $this->getDataKdProfile($request))
            ->first();
	       try{
            if ($request['strukplanning']['norecnoplanning'] == '') {
                $Noplanning = $this->generateCode(new StrukPlanning, 'noplanning', 10, 'PP-' . $this->getDateTime()->format('ym'));
                $dataSO = new StrukPlanning();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = (int) $this->getDataKdProfile($request);
                $dataSO->statusenabled = true;
                $dataSO->noplanning = $Noplanning;
                $dataSO->tglpengajuan=$request['strukplanning']['tglpelatihanawal'];
                $dataSO->tglplanning=$request['strukplanning']['tglpengajuan'];
//                $dataSO->unitkerjapegawifk=$request['strukplanning']['unitkerjapegawifk'];


                $dataSA = new StrukAgenda();
                $dataSA->norec = $dataSA->generateNewId();
                $dataSA->kdprofile = (int) $this->getDataKdProfile($request);
                $dataSA->statusenabled = true;
                $dataSA ->tglpengajuan=$request['strukplanning']['tglpengajuan'];
            }else {
                $dataSO = StrukPlanning::where('norec',$request['strukplanning']['norecnoplanning'])->first();
                $dataSA = StrukAgenda::where('norec',$request['strukplanning']['norecagenda'])->first();
                StrukPlanningDetail::where('noplanningfk',$request['strukplanning']['norecnoplanning'])->delete();
                StrukAgendaDetail::where('strukagendafk',$request['strukplanning']['norecagenda'])->delete();
            }
                $dataSO->objectkelompoktransaksifk=111;
                $dataSO->jenispelatihanfk=$request['strukplanning']['jenispelatihan'];
                $dataSO->namaplanning=$request['strukplanning']['namaplanning'];
                $dataSO->deskripsiplanning=$request['strukplanning']['deskripsiplanning'];
                $dataSO->objectruanganfk=$request['strukplanning']['objectruanganfk'];
                $dataSO->tglsiklusakhir=$request['strukplanning']['tglpelatihanakhir'];
                $dataSO->tglsiklusawal=$request['strukplanning']['tglpelatihanawal'];
                $dataSO->keteranganlainnya=$request['strukplanning']['alasan'];
                $dataSO->tempat=$request['strukplanning']['tempat'];
                $dataSO->narasumberfk=$request['strukplanning']['narasumber'];
                $dataSO->objectpegawaipjawabfk=$dataPegawai->objectpegawaifk;
                $dataSO->save();
                $dataNorec = $dataSO->norec;
                $noStrukPlanning = $dataSO->noplanning;

                $dataSA ->objectkelompoktransaksifk=111;
                $dataSA ->noplanningfk=$dataNorec;
                $dataSA->objectpegawaipjawabfk=$dataPegawai->objectpegawaifk;
                $dataSA->save();
                $dataNorecAgenda = $dataSA->norec;

            foreach ($request['details'] as $item) {
                $dataOP = new StrukPlanningDetail();
                $dataOP->norec = $dataOP->generateNewId();
                $dataOP->kdprofile = (int) $this->getDataKdProfile($request);
                $dataOP->statusenabled = true;
                $dataOP->pegawaifk = $item['pegawaifk'];
                $dataOP->noplanningfk = $dataNorec;
                $dataOP->asalprodukfk=$request['strukplanning']['asalproduk'];
                $dataOP->save();

                foreach ($request['detailagenda'] as $agenda){
                    $dataSAD = new StrukAgendaDetail();
                    $dataSAD->norec = $dataSAD->generateNewId();
                    $dataSAD->kdprofile = (int) $this->getDataKdProfile($request);
                    $dataSAD->statusenabled= true;
                    $dataSAD->pegawaifk=$item['pegawaifk'];
                    $dataSAD->strukagendafk=$dataNorecAgenda;
                    $dataSAD->tglpelatihan=$agenda['tanggalagenda'];
                    $dataSAD->sesikegiatan=$agenda['sesi'];
                    $dataSAD->keterangan=$agenda['keterangan'];
                    $dataSA->jpl=$agenda['jpl'];
//                    $dataSAD->narasumberfk=$agenda['narasumberfk'];
                    $dataSAD->save();
                }
            }

            //***** Monitoring Pengajuan Pelatihan *****
            if ($request['strukplanning']['norecmonitoring'] == '') {
                $dataSR= new MonitoringPengajuanPelatihan();
                $dataSR->norec = $dataSR->generateNewId();
                $dataSR->kdprofile = (int) $this->getDataKdProfile($request);
                $dataSR->statusenabled = true;
            }else {
                $dataSR = MonitoringPengajuanPelatihan::where('norec', $request['strukplanning']['norecmonitoring'])->first();
            }
                $dataSR->tanggal=$tglAyeuna;
                $dataSR->keterangan='Pengajuan Pelatihan ' . $noStrukPlanning . ' tanggal ' . $request['strukplanning']['tglpelatihanawal'] .' s/d '.$request['strukplanning']['tglpelatihanakhir'];
                $dataSR->save();
                $dataSR = $dataSR->norec;

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['strukplanning']['norecmonitoringdetail'] == null) {
                $dataRR= new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 111;
                $dataRR->monitoringpengajuanpelatihanfk=$dataSR;
            }else {
                $dataRR = MonitoringPengajuanPelatihanDetail::where('norec', $request['strukplanning']['norecmonitoringdetail'])->first();
            }
                $dataRR->tanggalstruk = $request['strukplanning']['tglpengajuan'];
                $dataRR->objectstrukfk = $dataNorec;
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $noStrukPlanning;
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->keteranganlainnya = 'Pengajuan Pelatihan ' . $noStrukPlanning . ' tanggal pelatihan ' . $request['strukplanning']['tglpelatihanawal'] .' s/d '.$request['strukplanning']['tglpelatihanakhir'];
                $dataRR->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
       }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Pengajuan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSO,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Pengajuan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSO,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarPengajuanPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->JOIN('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.asalprodukfk')
            ->Leftjoin('monitoringpengajuanpelatihandetail_t as mppd','mppd.objectstrukfk','=','sp.norec')
            ->Leftjoin('monitoringpengajuanpelatihan_t as mpp','mpp.norec','=','mppd.monitoringpengajuanpelatihanfk')
            ->Leftjoin('jenispelatihan_m as jp','jp.id','=','sp.jenispelatihanfk')
            ->Leftjoin('narasumber_m as na','na.id','=','sp.narasumberfk')
            ->select('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                     'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap',
                     'mpp.norec as norecmonitoring','mppd.norec as norecmonitoringdetail','sp.verifikasifk')
            ->groupBy('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                      'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap',
                      'mpp.norec','mppd.norec','sp.verifikasifk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglsiklusawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglsiklusawal','<=', $tgl);
        }
        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['jenispelatihanfk']) && $request['jenispelatihanfk']!="" && $request['jenispelatihanfk']!="undefined"){
            $data = $data->where('spd.jenispelatihanfk','=',$request['jenispelatihanfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.kdprofile',(int) $this->getDataKdProfile($request));
        $data = $data->where('sp.objectkelompoktransaksifk',111);
        $data = $data->whereNull('mppd.verifikasisdmfk');
        $data = $data->whereNotIn('mppd.objectkelompoktransaksifk',[113,114,115,116,121]);
        $data = $data->orderBy('sp.noplanning');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("select spd.pegawaifk,pg.namalengkap,pg.nip_pns
                    from strukplanningdetail_t as spd
                    INNER JOIN pegawai_m as pg on pg.id = spd.pegawaifk
                    where spd.noplanningfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'norec' => $item->norec,
                'norecmonitoring' => $item->norecmonitoring,
                'norecmonitoringdetail' => $item->norecmonitoringdetail,
                'noplanning' => $item->noplanning,
                'asalprodukfk' => $item->asalprodukfk,
                'namaplanning' => $item->namaplanning,
                'deskripsiplanning' => $item->deskripsiplanning,
                'tempat' => $item->tempat,
                'keteranganlainnya' => $item->keteranganlainnya,
                'tglpengajuan' => $item->tglpengajuan,
                'tglsiklusawal' => $item->tglsiklusawal,
                'tglsiklusakhir' => $item->tglsiklusakhir,
                'jenispelatihanfk' => $item->jenispelatihanfk,
                'jenispelatihan' => $item->jenispelatihan,
                'verifikasifk' => $item->verifikasifk,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDetailPengajuanPenelitian(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('strukplanning_t as sp')
            ->JOIN('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.asalprodukfk')
            ->Leftjoin('monitoringpengajuanpelatihandetail_t as mpd','mpd.objectstrukfk','=','sp.norec')
            ->Leftjoin('monitoringpengajuanpelatihan_t as mp','mp.norec','=','mpd.monitoringpengajuanpelatihanfk')
            ->Leftjoin('jenispelatihan_m as jp','jp.id','=','sp.jenispelatihanfk')
            ->Leftjoin('narasumber_m as na','na.id','=','sp.narasumberfk')
            ->select('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                     'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan',
                     'mp.norec as norecmonitoring','mpd.norec as norecmonitoringdetail','sp.narasumberfk','na.namalengkap as narasumber')
            ->groupBy('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                      'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','mp.norec','mpd.norec',
                      'sp.narasumberfk','na.namalengkap');
//            ->whereNotIn('mpd.objectkelompoktransaksifk',[111,113,114,115,116,121,117]);
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norecOrder']);
        }
        $data = $data->where('sp.kdprofile',(int) $this->getDataKdProfile($request));
        $data = $data->get();
        $peserta = [];
        $i = 0;
        $dataStok = DB::select(DB::raw("select pg.id,pg.namalengkap
                    from strukplanningdetail_t as spd
                    INNER JOIN pegawai_m as pg on pg.id = spd.pegawaifk
                    where spd.noplanningfk=:norec"),
            array(
                'norec' => $request['norecOrder']
            )
        );

        $dataAgenda = DB::select(DB::raw("select sad.tglpelatihan as tanggalagenda,sad.sesikegiatan as sesi,sad.keterangan,
                      sad.narasumberfk,na.namalengkap as narasumber
                      from strukagenda_t as sa 
                      INNER JOIN strukagendadetail_t as sad on sad.strukagendafk = sa.norec
                      left join narasumber_m as na on na.id = sad.narasumberfk
                      where sa.noplanningfk=:norec
                      GROUP BY sad.tglpelatihan,sad.sesikegiatan,sad.keterangan,sad.narasumberfk,na.namalengkap
                      ORDER BY sad.tglpelatihan ASC "),
            array(
                'norec' => $request['norecOrder']
            )
        );

        foreach ($dataStok as $item){
            $peserta[] = array(

                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        $result = array(
            'head' => $data,
            'detail' => $peserta,
            'agenda' => $dataAgenda,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function hapusDataPengajuanPelatihan(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
             ->where('lu.kdprofile',(int) $this->getDataKdProfile($request))
            ->first();
        try{

            $Kel = StrukPlanning::where('norec', $request['data']['norec'])
                ->update([
                    'statusenabled' => 'f',
                ]);

            if ($request['data']['norecmonitoringdetail'] != '' && $request['data']['norecmonitoring'] != '') {

                $dataRR = new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 114;
                $dataRR->monitoringpengajuanpelatihanfk = $request['data']['norecmonitoring'];
                $dataRR->tanggalstruk = $request['data']['tglpengajuan'];
                $dataRR->objectstrukfk = $request['data']['norec'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['noplanning'];
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->keteranganlainnya = 'Batal Pengajuan Pelatihan No. ' . $request['data']['noplanning'] . ' tanggal pelatihan ' . $request['data']['tglsiklusawal'] . ' s/d ' . $request['data']['tglsiklusakhir'];
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Input Pengajuan Pelatihan';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Pengajuan Pelatihan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
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
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveVerifikasiPengajuan (Request $request) {
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
             ->where('lu.kdprofile',(int) $this->getDataKdProfile($request))
            ->first();
        try{
            if ($request['data']['norec'] != '') {

                //#struk Verifikasi
                if ($request['data']['verifikasifk'] == ''){
                    $noVerifikasi = $this->generateCode(new StrukVerifikasi(),
                        'noverifikasi', 10, 'VP'.$this->getDateTime()->format('ym'));
                    $dataSV = new StrukVerifikasi();
                    $dataSV->norec = $dataSV->generateNewId();
                    $dataSV->noverifikasi = $noVerifikasi;
                    $dataSV->kdprofile =  (int) $this->getDataKdProfile($request);;
                    $dataSV->statusenabled = true;
                    $dataSV->objectkelompoktransaksifk = 112;
                }else{
                    $dataSV = StrukVerifikasi::where('norec', $request['data']['verifikasifk'])->first();
                }
                    $dataSV->keteranganlainnya = 'Verifikasi Pengajuan Pelatihan Bag SDM';
                    $dataSV->objectpegawaipjawabfk = $dataPegawai->objectpegawaifk;
                    $dataSV->namaverifikasi = 'Verifikasi Pengajuan Pelatihan Bag SDM';
                    $dataSV->tglverifikasi = $tglAyeuna;
                    $dataSV->tgleksekusi = $tglAyeuna;
                    $dataSV->save();
                    $dataSV = $dataSV->norec;

                $dataSP = StrukPlanning::where('norec', $request['data']['norec'])
                    ->update([
                            'verifikasifk' => $dataSV,
                        ]
                    );


            }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecmonitoringdetail'] != '' && $request['data']['norecmonitoring'] != '') {

                $dataRR = new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile =  (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 112;
                $dataRR->monitoringpengajuanpelatihanfk = $request['data']['norecmonitoring'];
                $dataRR->tanggalstruk = $request['data']['tglpengajuan'];
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->objectstrukfk = $request['data']['norec'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['noplanning'];
                $dataRR->verifikasisdmfk = $dataSV;
                $dataRR->keteranganlainnya = 'Verifikasi Pengajuan Pelatihan Bag SDM No. ' . $request['data']['noplanning'] . ' tanggal pelatihan ' . $request['data']['tglsiklusawal'] . ' s/d ' . $request['data']['tglsiklusakhir'];
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile=  (int) $this->getDataKdProfile($request);;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Verifikasi Pengajuan Pelatihan Bag SDM';
            $logUser->noreff =$dataSV;
            $logUser->referensi='norec verifikasi Pengajuan Pelatihan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

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
                "noplanning" => $request['data']['noplanning'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['data']['noplanning'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveUnverifikasiPengajuan (Request $request) {
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile',(int) $this->getDataKdProfile($request))
            ->first();
        try{
                //#struk Verifikasi
                if ($request['data']['verifikasifk'] != ''){

                    $Kel = StrukVerifikasi::where('norec', $request['data']['verifikasifk'])
                        ->update([
                            'statusenabled' => 'f',
                        ]);

                    $dataSP = StrukPlanning::where('verifikasifk', $request['data']['verifikasifk'])
                        ->update([
                                'verifikasifk' => null,
                            ]
                        );
                }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecmonitoringdetail'] != '' && $request['data']['norecmonitoring'] != '') {

                $dataRR = new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 113;
                $dataRR->monitoringpengajuanpelatihanfk = $request['data']['norecmonitoring'];
                $dataRR->tanggalstruk = $request['data']['tglpengajuan'];
                $dataRR->objectstrukfk = $request['data']['norec'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['noplanning'];
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->keteranganlainnya = 'Unverifikasi Pengajuan Pelatihan Bag SDM No. ' . $request['data']['noplanning'] . ' tanggal pelatihan ' . $request['data']['tglsiklusawal'] . ' s/d ' . $request['data']['tglsiklusakhir'];
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile=  (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Unverifikasi Pengajuan Pelatihan Bag SDM';
            $logUser->noreff =$request['data']['verifikasifk'];
            $logUser->referensi='norec verifikasi Pengajuan Pelatihan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

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
                "noplanning" => $request['data']['noplanning'],
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noplanning" => $request['data']['noplanning'] . '  Gagal',
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarMonitoringPengajuanPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->JOIN('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.asalprodukfk')
//            ->Leftjoin('monitoringpengajuanpelatihandetail_t as mppd','mppd.objectstrukfk','=','sp.norec')
//            ->Leftjoin('monitoringpengajuanpelatihan_t as mpp','mpp.norec','=','mppd.monitoringpengajuanpelatihanfk')
            ->Leftjoin('jenispelatihan_m as jp','jp.id','=','sp.jenispelatihanfk')
            ->Leftjoin('narasumber_m as na','na.id','=','sp.narasumberfk')
            ->select('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap')
            ->groupBy('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglsiklusawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglsiklusawal','<=', $tgl);
        }
        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['jenispelatihanfk']) && $request['jenispelatihanfk']!="" && $request['jenispelatihanfk']!="undefined"){
            $data = $data->where('spd.jenispelatihanfk','=',$request['jenispelatihanfk']);
        }

       $data = $data->where('sp.kdprofile', (int) $this->getDataKdProfile($request));
        $data = $data->where('sp.objectkelompoktransaksifk',111);
//        $data = $data->whereNull('mppd.verifikasisdmfk');
//        $data = $data->whereNotIn('mppd.objectkelompoktransaksifk',[113,114]);
        $data = $data->orderBy('sp.noplanning');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("SELECT mppd.tanggaleksekusi,
			 (case when kt.id = 111 then sp.noplanning 
					when kt.id = 112 then sv.noverifikasi
					when kt.id = 113 then mppd.keteranganlainnya
					when kt.id = 114 then mppd.keteranganlainnya 
					when kt.id = 115 then sv1.noverifikasi
					when kt.id = 116 then mppd.keteranganlainnya end) as noverifikasi,pg.namalengkap,kt.id,kt.kelompoktransaksi
			from monitoringpengajuanpelatihan_t as mpp
			left join monitoringpengajuanpelatihandetail_t as mppd on mppd.monitoringpengajuanpelatihanfk=mpp.norec
			left join strukplanning_t as sp on sp.norec = mppd.objectstrukfk
			left join strukverifikasi_t as sv on sv.norec = mppd.verifikasisdmfk
			left join strukverifikasi_t as sv1 on sv1.norec = mppd.verifikasidirekturfk
			left join kelompoktransaksi_m as kt on kt.id = mppd.objectkelompoktransaksifk
			left join pegawai_m as pg on pg.id = mppd.objectpetugasfk
            where mppd.objectstrukfk =:norec
			order by mppd.tanggaleksekusi asc"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'norec' => $item->norec,
                'noplanning' => $item->noplanning,
                'asalprodukfk' => $item->asalprodukfk,
                'namaplanning' => $item->namaplanning,
                'deskripsiplanning' => $item->deskripsiplanning,
                'tempat' => $item->tempat,
                'keteranganlainnya' => $item->keteranganlainnya,
                'tglpengajuan' => $item->tglpengajuan,
                'tglsiklusawal' => $item->tglsiklusawal,
                'tglsiklusakhir' => $item->tglsiklusakhir,
                'jenispelatihanfk' => $item->jenispelatihanfk,
                'jenispelatihan' => $item->jenispelatihan,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function GetDaftarPengajuanPelatihanDirekSDM(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->JOIN('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.asalprodukfk')
            ->Leftjoin('monitoringpengajuanpelatihandetail_t as mppd','mppd.objectstrukfk','=','sp.norec')
            ->Leftjoin('monitoringpengajuanpelatihan_t as mpp','mpp.norec','=','mppd.monitoringpengajuanpelatihanfk')
            ->Leftjoin('jenispelatihan_m as jp','jp.id','=','sp.jenispelatihanfk')
            ->Leftjoin('narasumber_m as na','na.id','=','sp.narasumberfk')
            ->select('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                     'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap',
                     'mpp.norec as norecmonitoring','mppd.norec as norecmonitoringdetail','sp.verifikasifk','sp.verifikasidireksdmfk','sp.verifikasidirekturfk')
            ->groupBy('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                      'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap',
                      'mpp.norec','mppd.norec','sp.verifikasifk','sp.verifikasidireksdmfk','sp.verifikasidirekturfk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglsiklusawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglsiklusawal','<=', $tgl);
        }
        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['jenispelatihanfk']) && $request['jenispelatihanfk']!="" && $request['jenispelatihanfk']!="undefined"){
            $data = $data->where('spd.jenispelatihanfk','=',$request['jenispelatihanfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.kdprofile', (int) $this->getDataKdProfile($request));
        $data = $data->where('sp.objectkelompoktransaksifk',111);
        $data = $data->whereNotNull('sp.verifikasifk');
        $data = $data->whereNull('mppd.verifikasisdmfk');
        $data = $data->whereNull('mppd.verifikasidirekturfk');
        $data = $data->whereNotIn('mppd.objectkelompoktransaksifk',[113,114,115,116,121]);
        $data = $data->orderBy('sp.noplanning');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("select spd.pegawaifk,pg.namalengkap,pg.nip_pns
                    from strukplanningdetail_t as spd
                    INNER JOIN pegawai_m as pg on pg.id = spd.pegawaifk
                    where spd.noplanningfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'norec' => $item->norec,
                'norecmonitoring' => $item->norecmonitoring,
                'norecmonitoringdetail' => $item->norecmonitoringdetail,
                'noplanning' => $item->noplanning,
                'asalprodukfk' => $item->asalprodukfk,
                'namaplanning' => $item->namaplanning,
                'deskripsiplanning' => $item->deskripsiplanning,
                'tempat' => $item->tempat,
                'keteranganlainnya' => $item->keteranganlainnya,
                'tglpengajuan' => $item->tglpengajuan,
                'tglsiklusawal' => $item->tglsiklusawal,
                'tglsiklusakhir' => $item->tglsiklusakhir,
                'jenispelatihanfk' => $item->jenispelatihanfk,
                'jenispelatihan' => $item->jenispelatihan,
                'verifikasifk' => $item->verifikasifk,
                'verifikasidireksdmfk' => $item->verifikasidireksdmfk,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function saveVerifikasiPengajuanDirekSDM (Request $request) {
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        try{
            if ($request['data']['norec'] != '') {

                //#struk Verifikasi
                if ($request['data']['verifikasidireksdmfk'] == ''){
                    $noVerifikasi = $this->generateCode(new StrukVerifikasi(),
                        'noverifikasi', 10, 'VD'.$this->getDateTime()->format('ym'));
                    $dataSV = new StrukVerifikasi();
                    $dataSV->norec = $dataSV->generateNewId();
                    $dataSV->noverifikasi = $noVerifikasi;
                    $dataSV->kdprofile =  (int) $this->getDataKdProfile($request);
                    $dataSV->statusenabled = true;
                    $dataSV->objectkelompoktransaksifk = 115                             ;
                }else{
                    $dataSV = StrukVerifikasi::where('norec', $request['data']['verifikasidireksdmfk'])->first();
                }
                $dataSV->keteranganlainnya = $request['data']['keterangan'];//'Verifikasi Pengajuan Pelatihan Bag SDM';
                $dataSV->objectpegawaipjawabfk = $dataPegawai->objectpegawaifk;
                $dataSV->namaverifikasi = 'Verifikasi Pengajuan Pelatihan Bag SDM';
                $dataSV->tglverifikasi = $tglAyeuna;
                $dataSV->tgleksekusi = $tglAyeuna;
                $dataSV->save();
                $dataSV = $dataSV->norec;

                $dataSP = StrukPlanning::where('norec', $request['data']['norec'])
                    ->update([
                            'verifikasidireksdmfk' => $dataSV,
                            'keteranganverifikasi' => $request['data']['keterangan'],
                        ]
                    );


            }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecmonitoringdetail'] != '' && $request['data']['norecmonitoring'] != '') {

                $dataRR = new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile =  (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 115                             ;
                $dataRR->monitoringpengajuanpelatihanfk = $request['data']['norecmonitoring'];
                $dataRR->tanggalstruk = $request['data']['tglpengajuan'];
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->objectstrukfk = $request['data']['norec'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['noplanning'];
                $dataRR->verifikasidirekturfk = $dataSV;
                $dataRR->keteranganlainnya = 'Verifikasi Pengajuan Pelatihan Direktur SDM No. ' . $request['data']['noplanning'] . ' tanggal pelatihan ' . $request['data']['tglsiklusawal'] . ' s/d ' . $request['data']['tglsiklusakhir'];
                $dataRR->save();

            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile=  (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Verifikasi Pengajuan Pelatihan Direktur SDM';
            $logUser->noreff =$dataSV;
            $logUser->referensi='norec verifikasi Pengajuan Pelatihan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

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
                "noplanning" => $request['data']['noplanning'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['data']['noplanning'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveUnverifikasiPengajuanDirekSDM (Request $request) {
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{
            //#struk Verifikasi
            if ($request['data']['verifikasidireksdmfk'] != ''){

                $Kel = StrukVerifikasi::where('norec', $request['data']['verifikasidireksdmfk'])
                    ->update([
                        'statusenabled' => 'f',
                    ]);

                $dataSP = StrukPlanning::where('verifikasidireksdmfk', $request['data']['verifikasidireksdmfk'])
                    ->update([
                            'verifikasidireksdmfk' => null,
                        ]
                    );
            }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecmonitoringdetail'] != '' && $request['data']['norecmonitoring'] != '') {

                $dataRR = new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile =  (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 116;
                $dataRR->monitoringpengajuanpelatihanfk = $request['data']['norecmonitoring'];
                $dataRR->tanggalstruk = $request['data']['tglpengajuan'];
                $dataRR->objectstrukfk = $request['data']['norec'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['noplanning'];
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->keteranganlainnya = 'Unverifikasi Pengajuan Pelatihan Direk SDM No. ' . $request['data']['noplanning'] . ' tanggal pelatihan ' . $request['data']['tglsiklusawal'] . ' s/d ' . $request['data']['tglsiklusakhir'];
                $dataRR->save();
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile=  (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Unverifikasi Pengajuan Pelatihan Direktur SDM';
            $logUser->noreff =$request['data']['verifikasidireksdmfk'];
            $logUser->referensi='norec verifikasi Pengajuan Pelatihan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

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
                "noplanning" => $request['data']['noplanning'],
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noplanning" => $request['data']['noplanning'] . '  Gagal',
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getComboDataPelatihan(Request $request){
        $req = $request->all();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk','lu.objectkelompokuserfk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', (int) $this->getDataKdProfile($request))
            ->first();

        $strukplanning = StrukPlanning::where('statusenabled',true)
            ->whereNotNull('verifikasifk')
           ->where('kdprofile', (int) $this->getDataKdProfile($request))
//            ->whereNotNull('verifikasidireksdmfk')
            ->get();

        $result = array(
            'strukplanning' => $strukplanning,
            'by' => 'cepot',
        );

        return $this->respond($result);
    }

    public function GetDaftarPesertaPelatihan(Request $request) {

        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser and lu.kdprofile=:kdprofile"),
            array(
                'idLoginUser' => $request['userData']['id'],
                 'kdprofile' =>   (int) $this->getDataKdProfile($request)
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->Leftjoin('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->Leftjoin('asalproduk_m as ap','ap.id','=','spd.asalprodukfk')
            ->Leftjoin('pegawai_m as pg','pg.id','=','spd.pegawaifk')
            ->select( DB::raw("sp.tglpengajuan,sp.namaplanning,sp.deskripsiplanning as penyelenggara,sp.tempat,
			 sp.keteranganlainnya,to_char(sp.tglsiklusawal, 'dd-MM-yyyy')   || ' s/d ' || to_char(sp.tglsiklusakhir, 'dd-MM-yyyy') as tanggalpelaksanaan,
			 spd.pegawaifk,pg.nippns,pg.namalengkap,spd.asalprodukfk,ap.asalproduk"))
            ->groupBy('sp.tglpengajuan','sp.noplanning','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya','sp.tglsiklusawal','sp.tglsiklusakhir',
				       'spd.pegawaifk','pg.nippns','pg.namalengkap','spd.asalprodukfk','ap.asalproduk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglsiklusawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglsiklusawal','<=', $tgl);
        }
        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['Pelatihan']) && $request['Pelatihan']!="" && $request['Pelatihan']!="undefined"){
            $data = $data->where('sp.noplanning','=',$request['Pelatihan']);
        }

        $data = $data->where('sp.statusenabled',true);
         $data = $data->where('sp.kdprofile',(int) $this->getDataKdProfile($request));
         

        $data = $data->where('sp.objectkelompoktransaksifk',111);
        $data = $data->whereNotNull('sp.verifikasifk');
        $data = $data->orderBy('sp.noplanning');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){

//            $results[] = array(
//                'norec' => $item->norec,
//                'noplanning' => $item->noplanning,
//                'asalprodukfk' => $item->asalprodukfk,
//                'namaplanning' => $item->namaplanning,
//                'deskripsiplanning' => $item->deskripsiplanning,
//                'tempat' => $item->tempat,
//                'keteranganlainnya' => $item->keteranganlainnya,
//                'tglpengajuan' => $item->tglpengajuan,
//                'tglsiklusawal' => $item->tglsiklusawal,
//                'tglsiklusakhir' => $item->tglsiklusakhir,
//                'jenispelatihanfk' => $item->jenispelatihanfk,
//                'jenispelatihan' => $item->jenispelatihan,
//                'verifikasifk' => $item->verifikasifk,
//                'verifikasidireksdmfk' => $item->verifikasidireksdmfk,
//                'details' => $details,
//            );
        }

        $result = array(
            'daftar' => $data,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function GetDaftarKehadiranPesertaPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->Leftjoin('strukagenda_t as sa','sa.noplanningfk','=','sp.norec')
            ->Leftjoin('strukagendadetail_t as sad','sad.strukagendafk','=','sa.norec')
            ->Leftjoin('pegawai_m as pg','pg.id','=','sad.pegawaifk')
            ->Leftjoin('kehadiranpelatihan_m as kp','kp.id','=','sad.kehadiranfk')
            ->select( DB::raw("sp.norec,sad.norec as norecdetail,sp.tglpengajuan,sp.noplanning,sp.namaplanning,sp.deskripsiplanning as penyelenggara,
				               sp.tempat,sp.keteranganlainnya,sad.tglpelatihan as tanggalpelaksanaan,sad.pegawaifk,pg.namalengkap,sad.kehadiranfk,kp.kehadiran,pg.nippns"))
            ->groupBy('sp.norec','sad.norec','sp.tglpengajuan','sp.noplanning','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
					  'sad.tglpelatihan','sad.pegawaifk','pg.namalengkap','sad.kehadiranfk','kp.kehadiran','pg.nippns');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $tgl= $request['tglAwal'];
            $data = $data->where('sad.tglpelatihan','>=', $tgl);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sad.tglpelatihan','<=', $tgl);
        }
        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['Pelatihan']) && $request['Pelatihan']!="" && $request['Pelatihan']!="undefined"){
            $data = $data->where('sp.noplanning','=',$request['Pelatihan']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',111);
        $data = $data->where('sp.kdprofile',(int) $this->getDataKdProfile($request));
        $data = $data->whereNotNull('sp.verifikasifk');
        $data = $data->orderBy('pg.namalengkap','ASC');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function saveKehadiranPesertaPelatihan(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{
            if($request['data']['statuskehadiran'] == 1){
                $Kel = StrukAgendaDetail::where('norec', $request['data']['norecdetail'])
                    ->where('pegawaifk', $request['data']['pegawaifk'])
                    ->update([
                        'kehadiranfk' => $request['data']['statuskehadiran'],
                        'tglkehadiran' => $request['data']['tglabsen']
                    ]);
            }else{
                $Kel = StrukAgendaDetail::where('norec', $request['data']['norecdetail'])
                    ->where('pegawaifk', $request['data']['pegawaifk'])
                    ->update([
                        'kehadiranfk' => $request['data']['statuskehadiran'],
                        'tglkehadiran' => null
                    ]);
            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Status Kehadiran Peserta Pelatihan';
            $logUser->noreff =$request['data']['norecdetail'];
            $logUser->referensi='norec strukplanningdetail';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
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
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarRekapitulasiKehadiranPesertaPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser and lu.kdprofile=:kdprofile"),
            array(
                'idLoginUser' => $request['userData']['id'],
                'kdprofile' =>(int) $this->getDataKdProfile($request)
            )
        );
        $tglAwal = $request['tglAwal'] ;
        $tglAkhir = $request['tglAkhir'] ;
        $NoPlanning = $request['Pelatihan'];
        $JenisPelatihan = $request['jenisPelatihan'];

        $paramJenisPelatihan =  ' ';
        if (isset($JenisPelatihan) && $JenisPelatihan!= "" && $JenisPelatihan != "undefined") {
            $paramJenisPelatihan =  ' and sp.jenispelatihanfk =  ' .$JenisPelatihan;
        }
        $paramPelatihan =  ' ';
        if (isset($NoPlanning) && $NoPlanning!= "" && $NoPlanning != "undefined") {
            $paramPelatihan  =  " and sp.noplanning ILIKE ". "'%" . $NoPlanning . "%'";
        }
        $kdprofile =(int) $this->getDataKdProfile($request);

        $data = DB::select(DB::raw("select x.noplanning,x.namaplanning,x.tanggalpelaksanaan,SUM(x.totalpeserta) as totalpeserta,SUM(x.totalhadir) as totalhadir, 
                                     SUM(x.totaltidakhadir) as totaltidakhadir 
                         from
                        (select sp.noplanning,sp.namaplanning,count(pg.namalengkap) as totalpeserta,
                                     (CASE when sad.kehadiranfk = 1 then count(sad.kehadiranfk)end) as totalhadir,
                                     (CASE when sad.kehadiranfk = 2 then count(sad.kehadiranfk)end) as totaltidakhadir,
                                      sad.tglpelatihan as tanggalpelaksanaan  
                         from strukplanning_t as sp
                         left join strukagenda_t as sa on sa.noplanningfk = sp.norec
                         left join strukagendadetail_t as sad on sad.strukagendafk = sa.norec
                         left join pegawai_m as pg on pg.id = sad.pegawaifk
                         left join kehadiranpelatihan_m as kp on kp.id = sad.kehadiranfk
                         where sad.tglpelatihan between '$tglAwal' and '$tglAkhir'
                         and sp.statusenabled = true and sp.objectkelompoktransaksifk = 111 
                         and sp.verifikasifk is not null and sad.kehadiranfk is not null
                         and sp.kdprofile=$kdprofile
                         $paramJenisPelatihan
                         $paramPelatihan
                         GROUP BY sp.namaplanning,sad.kehadiranfk,sad.tglpelatihan,sp.noplanning) as x
                         group by x.namaplanning,x.tanggalpelaksanaan,x.noplanning"));

        $result = array(
            'daftar' => $data,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function GetDetailKehadiranPesertaPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $kdprofile =(int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'] ;
        $tglAkhir = $request['tglAkhir'] ;
        $NoPlanning = $request['Pelatihan'];
        $JenisPelatihan = $request['jenisPelatihan'];
        $StatusKehadiran = $request['StatusKehadiran'];
        $paramJenisPelatihan =  ' ';
        if (isset($JenisPelatihan) && $JenisPelatihan!= "" && $JenisPelatihan != "undefined") {
            $paramJenisPelatihan =  ' and sp.jenispelatihanfk =  ' .$JenisPelatihan;
        }
        $paramPelatihan =  ' ';
        if (isset($NoPlanning) && $NoPlanning!= "" && $NoPlanning != "undefined") {
            $paramPelatihan  =  " and sp.noplanning LIKE ". "'%" . $NoPlanning . "%'";
        }
        $result=[];
        if ($StatusKehadiran == "ALL"){
            $data = DB::select(DB::raw("select sp.noplanning,sp.namaplanning,sad.tglpelatihan as tanggalpelaksanaan,
			        sad.pegawaifk,pg.namalengkap,pg.nippns,mp.objectjabatanfk,jb.namajabatan,sad.kehadiranfk,kp.kehadiran
                    from strukplanning_t as sp
                    left join strukagenda_t as sa on sa.noplanningfk = sp.norec
                    left join strukagendadetail_t as sad on sad.strukagendafk = sa.norec
                    left join pegawai_m as pg on pg.id = sad.pegawaifk
                    left join kehadiranpelatihan_m as kp on kp.id = sad.kehadiranfk
                    left join mappegawaijabatantounitkerja_m as mp on mp.objectpegawaifk = pg.id
                    left join jabatan_m as jb on jb.id = mp.objectjabatanfk
                    where sad.tglpelatihan between '$tglAwal' and '$tglAkhir'
                    and sp.statusenabled = 1 and sp.objectkelompoktransaksifk = 111 
                    and sp.verifikasifk is not null and sad.kehadiranfk is not null
                      and sp.kdprofile=$kdprofile
                    $paramPelatihan
                    GROUP BY sp.namaplanning,sad.kehadiranfk,sp.noplanning,sad.tglpelatihan,sad.pegawaifk,
                             pg.namalengkap,pg.nippns,mp.objectjabatanfk,jb.namajabatan,sad.kehadiranfk,kp.kehadiran
                    order by pg.namalengkap ASC"));

            $result = array(
                'daftar' => $data,
                'datalogin' =>$dataPegawaiUser,
                'message' => 'ea@epic',
            );
        }elseif ($StatusKehadiran == 1){
            $data = DB::select(DB::raw("select sp.noplanning,sp.namaplanning,sad.tglpelatihan as tanggalpelaksanaan,
			        sad.pegawaifk,pg.namalengkap,pg.nippns,mp.objectjabatanfk,jb.namajabatan,sad.kehadiranfk,kp.kehadiran
                    from strukplanning_t as sp
                    left join strukagenda_t as sa on sa.noplanningfk = sp.norec
                    left join strukagendadetail_t as sad on sad.strukagendafk = sa.norec
                    left join pegawai_m as pg on pg.id = sad.pegawaifk
                    left join kehadiranpelatihan_m as kp on kp.id = sad.kehadiranfk
                    left join mappegawaijabatantounitkerja_m as mp on mp.objectpegawaifk = pg.id
                    left join jabatan_m as jb on jb.id = mp.objectjabatanfk
                    where sad.tglpelatihan between '$tglAwal' and '$tglAkhir'
                    and sp.statusenabled = 1 and sp.objectkelompoktransaksifk = 111 
                    and sp.verifikasifk is not null and sad.kehadiranfk is not null and sad.kehadiranfk=1
                      and sp.kdprofile=$kdprofile
                    $paramPelatihan
                    GROUP BY sp.namaplanning,sad.kehadiranfk,sp.noplanning,sad.tglpelatihan,sad.pegawaifk,
                             pg.namalengkap,pg.nippns,mp.objectjabatanfk,jb.namajabatan,sad.kehadiranfk,kp.kehadiran
                    order by pg.namalengkap ASC"));

            $result = array(
                'daftar' => $data,
                'datalogin' =>$dataPegawaiUser,
                'message' => 'ea@epic',
            );
        }else{
            $data = DB::select(DB::raw("select sp.noplanning,sp.namaplanning,sad.tglpelatihan as tanggalpelaksanaan,
			        sad.pegawaifk,pg.namalengkap,pg.nippns,mp.objectjabatanfk,jb.namajabatan,sad.kehadiranfk,kp.kehadiran
                    from strukplanning_t as sp
                    left join strukagenda_t as sa on sa.noplanningfk = sp.norec
                    left join strukagendadetail_t as sad on sad.strukagendafk = sa.norec
                    left join pegawai_m as pg on pg.id = sad.pegawaifk
                    left join kehadiranpelatihan_m as kp on kp.id = sad.kehadiranfk
                    left join mappegawaijabatantounitkerja_m as mp on mp.objectpegawaifk = pg.id
                    left join jabatan_m as jb on jb.id = mp.objectjabatanfk
                    where sad.tglpelatihan between '$tglAwal' and '$tglAkhir'
                    and sp.statusenabled = 1 and sp.objectkelompoktransaksifk = 111 
                    and sp.verifikasifk is not null and sad.kehadiranfk is not null and sad.kehadiranfk=2
                      and sp.kdprofile=$kdprofile
                    $paramPelatihan
                    GROUP BY sp.namaplanning,sad.kehadiranfk,sp.noplanning,sad.tglpelatihan,sad.pegawaifk,
                             pg.namalengkap,pg.nippns,mp.objectjabatanfk,jb.namajabatan,sad.kehadiranfk,kp.kehadiran
                    order by pg.namalengkap ASC"));

            $result = array(
                'daftar' => $data,
                'datalogin' =>$dataPegawaiUser,
                'message' => 'ea@epic',
            );
        }
        return $this->respond($result);
    }

    public function GetDataPelaksanaanPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $tglAwal = $request['tglAwal'] ;
        $tglAkhir = $request['tglAkhir'] ;
        $NoPlanning = $request['Pelatihan'];
        $JenisPelatihan = $request['jenisPelatihan'];
        $Narasumber = $request['Narasumber'];
        $paramJenisPelatihan =  ' ';
        if (isset($JenisPelatihan) && $JenisPelatihan!= "" && $JenisPelatihan != "undefined") {
            $paramJenisPelatihan =  ' and sp.jenispelatihanfk =  ' .$JenisPelatihan;
        }
        $paramPelatihan =  ' ';
        if (isset($NoPlanning) && $NoPlanning!= "" && $NoPlanning != "undefined") {
            $paramPelatihan  =  " and sp.noplanning like ". "'%" . $NoPlanning . "%'";
        }
        $paramNarasumber =  ' ';
        if (isset($Narasumber) && $Narasumber!= "" && $Narasumber != "undefined") {
            $paramNarasumber =  ' and sp.narasumberfk =  ' .$Narasumber;
        }
        $result=[];
        $kdprofile =(int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw("select sp.norec,sp.noplanning,sp.namaplanning,sp.deskripsiplanning,sp.tempat,
                         sp.tglsiklusawal || ' s/d ' || sp.tglsiklusakhir as tanggalpelaksanaan,
                         sp.narasumberfk,na.namalengkap,count(pg.namalengkap) as totalpeserta,sp.keteranganlainnya,
                         evn.noplanningfk,CASE WHEN evn.noplanningfk IS NULL THEN '-' ELSE 'Terevaluasi' END as evaluasinarasumber
                         from strukplanning_t as sp
                         left join strukplanningdetail_t as spd on spd.noplanningfk = sp.norec
                         left join asalproduk_m as ap on ap.id = spd.asalprodukfk
                         left join pegawai_m as pg on pg.id = spd.pegawaifk
                         left join kehadiranpelatihan_m as kp on kp.id = spd.kehadiranfk
                         left join narasumber_m as na on na.id = sp.narasumberfk
                         left join evaluasinarasumber_t as evn on evn.noplanningfk = sp.norec
                         where sp.statusenabled = true and sp.objectkelompoktransaksifk = 111 
                         and sp.verifikasifk is not null and sp.verifikasidireksdmfk is not null
                         and spd.kehadiranfk is not null
                           and sp.kdprofile=$kdprofile
                         $paramJenisPelatihan
                         $paramPelatihan
                         $paramNarasumber
                         GROUP BY sp.norec,sp.namaplanning,sp.tglsiklusawal,sp.tglsiklusakhir,sp.noplanning,
			                      sp.deskripsiplanning,sp.tempat,sp.narasumberfk,na.namalengkap,sp.keteranganlainnya,evn.noplanningfk"));

            $result = array(
                'daftar' => $data,
                'datalogin' =>$dataPegawaiUser,
                'message' => 'ea@epic',
            );

        return $this->respond($result);
    }

    public function GetDaftarPelaksanaanPelatihan(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->Leftjoin('strukagenda_t as sa','sa.noplanningfk','=','sp.norec')
            ->Leftjoin('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->Leftjoin('evaluasinarasumber_t as en','en.noplanningfk','=','sp.norec')
            ->Leftjoin('evaluasipenyelenggara_t as ep','ep.noplanningfk','=','sp.norec')
            ->select( DB::raw("sp.norec,sp.tglpengajuan,sp.noplanning,sp.namaplanning,sp.deskripsiplanning as penyelenggara, 
                              convert(varchar, sp.tglsiklusawal, 105) + ' s/d ' + convert(varchar, sp.tglsiklusakhir, 105) as tanggalpelaksanaan,
                              sp.tempat,sp.keteranganlainnya,count(spd.pegawaifk) as totalpeserta,
                              case when en.statusenabled=1 then en.norec else null end as evaluasinarasumber,
                              case when ep.statusenabled=1 then en.norec else null end as evaluasipenyelenggarafk"))
            ->groupBy('sp.norec','sp.tglpengajuan','sp.noplanning','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
					  'sp.tglsiklusawal','sp.tglsiklusakhir','en.norec','ep.norec','en.statusenabled','ep.statusenabled');

        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['Pelatihan']) && $request['Pelatihan']!="" && $request['Pelatihan']!="undefined"){
            $data = $data->where('sp.noplanning','=',$request['Pelatihan']);
        }

        $data = $data->where('sp.statusenabled',true);
           $data = $data->where('sp.kdprofile',(int) $this->getDataKdProfile($request));
        $data = $data->where('sp.objectkelompoktransaksifk',111);
        $data = $data->whereNotNull('sp.verifikasifk');
//        $data = $data->orderBy('sad.tglpelatihan','ASC');
        $data = $data->get();

//        $datas = DB::select(DB::raw("select x.noplanning,x.namaplanning,x.tanggalpelaksanaan,SUM(x.totalpeserta) as totalpeserta,SUM(x.totalhadir) as totalhadir,
//                                     SUM(x.totaltidakhadir) as totaltidakhadir
//                         from
//                        (select sp.noplanning,sp.namaplanning,count(pg.namalengkap) as totalpeserta,
//                                     (CASE when sad.kehadiranfk = 1 then count(sad.kehadiranfk)end) as totalhadir,
//                                     (CASE when sad.kehadiranfk = 2 then count(sad.kehadiranfk)end) as totaltidakhadir,
//                                      sad.tglpelatihan as tanggalpelaksanaan
//                         from strukplanning_t as sp
//                         left join strukagenda_t as sa on sa.noplanningfk = sp.norec
//                         left join strukagendadetail_t as sad on sad.strukagendafk = sa.norec
//                         left join pegawai_m as pg on pg.id = sad.pegawaifk
//                         left join kehadiranpelatihan_m as kp on kp.id = sad.kehadiranfk
//                         where sad.tglpelatihan between '$tglAwal' and '$tglAkhir'
//                         and sp.statusenabled = true and sp.objectkelompoktransaksifk = 111
//                         and sp.verifikasifk is not null and sp.verifikasidireksdmfk is not null
//                         and sad.kehadiranfk is not null
//                         $paramJenisPelatihan
//                         $paramPelatihan
//                         GROUP BY sp.namaplanning,sad.kehadiranfk,sad.tglpelatihan,sp.noplanning) as x
//                         group by x.namaplanning,x.tanggalpelaksanaan,x.noplanning"));

        $result = array(
            'daftar' => $data,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function saveVerifikasiPengajuanDirekTerkait (Request $request) {
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        try{
            if ($request['data']['norec'] != '') {

                //#struk Verifikasi
                if ($request['data']['verifikasidirekturfk'] == ''){
                    $noVerifikasi = $this->generateCode(new StrukVerifikasi(),
                        'noverifikasi', 10, 'VDT'.$this->getDateTime()->format('ym'));
                    $dataSV = new StrukVerifikasi();
                    $dataSV->norec = $dataSV->generateNewId();
                    $dataSV->noverifikasi = $noVerifikasi;
                    $dataSV->kdprofile = (int) $this->getDataKdProfile($request);
                    $dataSV->statusenabled = true;
                    $dataSV->objectkelompoktransaksifk = 121;
                }else{
                    $dataSV = StrukVerifikasi::where('norec', $request['data']['verifikasidirekturfk'])->first();
                }
                $dataSV->keteranganlainnya = $request['data']['keterangan'];//'Verifikasi Pengajuan Pelatihan Bag SDM';
                $dataSV->objectpegawaipjawabfk = $dataPegawai->objectpegawaifk;
                $dataSV->namaverifikasi = 'Verifikasi Pengajuan Pelatihan Oleh Direktur Terkait';
                $dataSV->tglverifikasi = $tglAyeuna;
                $dataSV->tgleksekusi = $tglAyeuna;
                $dataSV->save();
                $dataSV = $dataSV->norec;

                $dataSP = StrukPlanning::where('norec', $request['data']['norec'])
                    ->update([
                            'verifikasidirekturfk' => $dataSV,
                            'keteranganverifikasidirektur' => $request['data']['keterangan'],
                        ]
                    );
            }

            //***** Monitoring Pengajuan Pelatihan Detail *****
            if ($request['data']['norecmonitoringdetail'] != '' && $request['data']['norecmonitoring'] != '') {

                $dataRR = new MonitoringPengajuanPelatihanDetail();
                $dataRR->norec = $dataRR->generateNewId();
                $dataRR->kdprofile = (int) $this->getDataKdProfile($request);
                $dataRR->statusenabled = true;
                $dataRR->objectkelompoktransaksifk = 121                             ;
                $dataRR->monitoringpengajuanpelatihanfk = $request['data']['norecmonitoring'];
                $dataRR->tanggalstruk = $request['data']['tglpengajuan'];
                $dataRR->tanggaleksekusi = $tglAyeuna;
                $dataRR->objectstrukfk = $request['data']['norec'];
                $dataRR->objectpetugasfk = $dataPegawai->objectpegawaifk;
                $dataRR->noorderintern = $request['data']['noplanning'];
                $dataRR->verifikasidirekturterkaitfk = $dataSV;
                $dataRR->keteranganlainnya = 'Verifikasi Pengajuan Pelatihan Direktur Terkait No. ' . $request['data']['noplanning'] . ' tanggal pelatihan ' . $request['data']['tglsiklusawal'] . ' s/d ' . $request['data']['tglsiklusakhir'];
                $dataRR->save();

            }

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Verifikasi Pengajuan Pelatihan Direktur Terkait';
            $logUser->noreff =$dataSV;
            $logUser->referensi='norec Verifikasi Direktur Terkait';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

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
                "noplanning" => $request['data']['noplanning'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $request['data']['noplanning'],
                "data" => $dataSV,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function GetDaftarPengajuanPelatihanDirek(Request $request) {
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $data = \DB::table('strukplanning_t as sp')
            ->JOIN('strukplanningdetail_t as spd','spd.noplanningfk','=','sp.norec')
            ->JOIN('asalproduk_m as ap','ap.id','=','spd.asalprodukfk')
            ->Leftjoin('monitoringpengajuanpelatihandetail_t as mppd','mppd.objectstrukfk','=','sp.norec')
            ->Leftjoin('monitoringpengajuanpelatihan_t as mpp','mpp.norec','=','mppd.monitoringpengajuanpelatihanfk')
            ->Leftjoin('jenispelatihan_m as jp','jp.id','=','sp.jenispelatihanfk')
            ->Leftjoin('narasumber_m as na','na.id','=','sp.narasumberfk')
            ->select('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap',
                'mpp.norec as norecmonitoring','mppd.norec as norecmonitoringdetail','sp.verifikasifk','sp.verifikasidireksdmfk','sp.verifikasidirekturfk')
            ->groupBy('sp.norec','sp.noplanning','spd.asalprodukfk','ap.asalproduk','sp.namaplanning','sp.deskripsiplanning','sp.tempat','sp.keteranganlainnya',
                'sp.tglpengajuan','sp.tglsiklusawal','sp.tglsiklusakhir','sp.jenispelatihanfk','jp.jenispelatihan','sp.narasumberfk','na.namalengkap',
                'mpp.norec','mppd.norec','sp.verifikasifk','sp.verifikasidireksdmfk','sp.verifikasidirekturfk');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglsiklusawal','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglsiklusawal','<=', $tgl);
        }
        if(isset($request['noplanning']) && $request['noplanning']!="" && $request['noplanning']!="undefined"){
            $data = $data->where('sp.noplanning','ilike','%'. $request['noplanning']);
        }
        if(isset($request['jenispelatihanfk']) && $request['jenispelatihanfk']!="" && $request['jenispelatihanfk']!="undefined"){
            $data = $data->where('spd.jenispelatihanfk','=',$request['jenispelatihanfk']);
        }

        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.kdprofile', (int) $this->getDataKdProfile($request));
       
        $data = $data->where('sp.objectkelompoktransaksifk',111);
        $data = $data->whereNull('mppd.verifikasisdmfk');
        $data = $data->whereNull('mppd.verifikasidirekturfk');
        $data = $data->whereNull('mppd.verifikasidirekturfk');
        $data = $data->whereNotIn('mppd.objectkelompoktransaksifk',[113,114,115,116,121]);
        $data = $data->orderBy('sp.noplanning');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("select spd.pegawaifk,pg.namalengkap,pg.nip_pns
                    from strukplanningdetail_t as spd
                    INNER JOIN pegawai_m as pg on pg.id = spd.pegawaifk
                    where spd.noplanningfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );

            $results[] = array(
                'norec' => $item->norec,
                'norecmonitoring' => $item->norecmonitoring,
                'norecmonitoringdetail' => $item->norecmonitoringdetail,
                'noplanning' => $item->noplanning,
                'asalprodukfk' => $item->asalprodukfk,
                'namaplanning' => $item->namaplanning,
                'deskripsiplanning' => $item->deskripsiplanning,
                'tempat' => $item->tempat,
                'keteranganlainnya' => $item->keteranganlainnya,
                'tglpengajuan' => $item->tglpengajuan,
                'tglsiklusawal' => $item->tglsiklusawal,
                'tglsiklusakhir' => $item->tglsiklusakhir,
                'jenispelatihanfk' => $item->jenispelatihanfk,
                'jenispelatihan' => $item->jenispelatihan,
                'verifikasifk' => $item->verifikasifk,
                'verifikasidireksdmfk' => $item->verifikasidireksdmfk,
                'verifikasidirekturfk' => $item->verifikasidirekturfk,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' =>$dataPegawaiUser,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDetailEvaluasiPenyelenggara(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('evaluasipenyelenggara_t as evn')
            ->JOIN('evaluasipenyelenggaradetail_t as evnd','evnd.evaluasipenyelenggarafk','=','evn.norec')
            ->Leftjoin('pegawai_m as pg','pg.id','=','evn.objectpegawaifk')
            ->Leftjoin('narasumber_m as na','na.id','=','evn.narasumberfk')
            ->select(DB::raw("evn.statusenabled,evn.norec,evn.tglevaluasi,evn.fasilitator,evn.keteranganpelatihan, 
                             evn.materi,evn.saran,evn.objectpegawaifk,pg.namalengkap,evn.penyelenggara,
                             evnd.efektivitaspenyelenggaraan,evnd.persiapan,evnd.dapatditerapkanklinik,
                             evn.narasumberfk,na.namalengkap as narasumber,
                             evnd.hubunganpeserta,evnd.hubunganantarpeserta,evnd.kebersihan,evnd.kebersihantoilet"));
        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('evn.norec','=', $request['norecOrder']);
        }
        $data = $data->where('evn.kdprofile', (int) $this->getDataKdProfile($request));
        $data = $data->get();

        $result = array(
            'head' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveEvaluasiPenyelenggara(Request $request) {
        $detLogin = $request->all();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
//        return $this->respond($dataPegawai->objectpegawaifk);
        DB::beginTransaction();
        try{
            if ($request['data']['norec'] == ''){
                $dataPP = new EvaluasiPenyelenggara();
                $dataPP->kdprofile = (int) $this->getDataKdProfile($request);
                $dataPP->statusenabled = true;
                $dataPP->norec = $dataPP->generateNewId();
                $dataPP->noplanningfk = $request['data']['norecplanning'];
            }else{
                $dataPP =  EvaluasiPenyelenggara::where('norec',$request['data']['norec'])->first();
                $delPPD = EvaluasiPenyelenggaraDetail::where('evaluasipenyelenggarafk', $request['data']['norec'])
                    ->delete();
            }
            $dataPP->narasumberfk = $request['data']['narasumberfk'];
            $dataPP->tglevaluasi = $request['data']['tglevaluasi'];
            $dataPP->fasilitator = $request['data']['fasilitator'];
            $dataPP->materi = $request['data']['materi'];
//            $dataPP->keteranganpelatihan = $request['data']['tglevaluasi'];
            $dataPP->saran=$request['data']['saran'];
            $dataPP->penyelenggara=$request['data']['penyelenggara'];
            $dataPP->objectpegawaifk=$dataPegawai->objectpegawaifk;
            $dataPP->totalnilai=$request['data']['totalnilai'];
            $dataPP->save();
            $idPP=$dataPP->norec;

            $dataPPD = new EvaluasiPenyelenggaraDetail();
            $dataPPD->norec = $dataPPD->generateNewId();;
            $dataPPD->kdprofile = (int) $this->getDataKdProfile($request);
            $dataPPD->statusenabled = true;
            $dataPPD->evaluasipenyelenggarafk = $idPP;//$dataPP->norec;
            $dataPPD->efektivitaspenyelenggaraan =$request['data']['efektivitaspenyelenggaraan'];
            $dataPPD->persiapan =$request['data']['persiapan'];
            $dataPPD->dapatditerapkanklinik=$request['data']['dapatditerapkanklinik'];
            $dataPPD->hubunganpeserta=$request['data']['hubunganpeserta'];
            $dataPPD->hubunganantarpeserta=$request['data']['hubunganantarpeserta'];
            $dataPPD->kebersihan=$request['data']['kebersihan'];
            $dataPPD->kebersihantoilet =$request['data']['kebersihantoilet'];
            $dataPPD->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'NorecEvaluasi' => $idPP,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Gagal Simpan";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'NorecEvaluasi' => $idPP,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDetailEvaluasiNarasumber(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('evaluasinarasumber_t as evn')
            ->JOIN('evaluasinarasumberdetail_t as evnd','evnd.evaluasinarasumberfk','=','evn.norec')
            ->Leftjoin('pegawai_m as pg','pg.id','=','evn.objectpegawaifk')
            ->Leftjoin('narasumber_m as na','na.id','=','evn.narasumberfk')
            ->select('evn.statusenabled','evn.norec','evn.tglevaluasi','evn.fasilitator','evn.keteranganpelatihan','evn.manfaatpelatihan','evn.manfaatfasilitator',
                'evn.materi','evn.saran','evn.objectpegawaifk','pg.namalengkap','evnd.penguasaanmateri','evnd.ketepatanwaktu','evnd.sistematikapenyajian',
                'evnd.penggunaanmetodedanalatbantu','evnd.empati','evnd.penggunaanbahasa','evnd.pemberianmotivasi','evnd.isimateri','evnd.penyajianmateri',
                'evnd.dapatditerapkanklinik','evnd.kesempatantanyajawab','evnd.pencapaiantujuanpembelajaran','evnd.kerjasamaantartimpengajar',
                'evnd.penampilanfasilitator','evn.narasumberfk','na.namalengkap as narasumber');

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('evn.norec','=', $request['norecOrder']);
        }
        $data = $data->where('evn.kdprofile', (int) $this->getDataKdProfile($request));
        $data = $data->get();

        $result = array(
            'head' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveEvaluasiNarasumber(Request $request) {
        $detLogin = $request->all();
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
//        return $this->respond($dataPegawai->objectpegawaifk);
        DB::beginTransaction();
        try{
            if ($request['data']['norec'] == ''){
                $dataPP = new EvaluasiNarasumber();
                $dataPP->kdprofile =  (int) $this->getDataKdProfile($request);
                $dataPP->statusenabled = true;
                $dataPP->norec = $dataPP->generateNewId();
                $dataPP->noplanningfk = $request['data']['norecplanning'];
            }else{
                $dataPP =  EvaluasiNarasumber::where('norec',$request['data']['norec'])->first();
                $delPPD = EvaluasiNarasumberDetail::where('evaluasinarasumberfk', $request['data']['norec'])
                    ->delete();
            }
            $dataPP->narasumberfk = $request['data']['narasumberfk'];
            $dataPP->tglevaluasi = $request['data']['tglevaluasi'];
//            $dataPP->fasilitator = $request['data']['fasilitator'];
//            $dataPP->materi = $request['data']['materi'];
//            $dataPP->keteranganpelatihan = $request['data']['keteranganpelatihan'];
//            $dataPP->manfaatpelatihan = $request['data']['manfaatpelatihan'];
//            $dataPP->manfaatfasilitator=$request['data']['manfaatfasilitator'];
            $dataPP->saran=$request['data']['saran'];
            $dataPP->objectpegawaifk=$dataPegawai->objectpegawaifk;

            $dataPP->totalnilai=$request['data']['totalnilai'];
            $dataPP->save();
            $idPP=$dataPP->norec;

            $dataPPD = new EvaluasiNarasumberDetail();
            $dataPPD->norec = $dataPPD->generateNewId();;
            $dataPPD->kdprofile =  (int) $this->getDataKdProfile($request);
            $dataPPD->statusenabled = true;
            $dataPPD->evaluasinarasumberfk = $idPP;//$dataPP->norec;
            $dataPPD->penguasaanmateri =$request['data']['penguasaanmateri'];
            $dataPPD->ketepatanwaktu =$request['data']['ketepatanwaktu'];
            $dataPPD->sistematikapenyajian=$request['data']['sistematikapenyajian'];
            $dataPPD->penggunaanmetodedanalatbantu=$request['data']['penggunaanmetodedanalatbantu'];
            $dataPPD->empati=$request['data']['empati'];
            $dataPPD->penggunaanbahasa=$request['data']['penggunaanbahasa'];
            $dataPPD->pemberianmotivasi =$request['data']['pemberianmotivasi'];
            $dataPPD->isimateri =$request['data']['isimateri'];
            $dataPPD->penyajianmateri=$request['data']['penyajianmateri'];
            $dataPPD->dapatditerapkanklinik=$request['data']['dapatditerapkanklinik'];
            $dataPPD->kesempatantanyajawab=$request['data']['kesempatantanyajawab'];
            $dataPPD->pencapaiantujuanpembelajaran=$request['data']['pencapaiantujuanpembelajaran'];
            $dataPPD->kerjasamaantartimpengajar=$request['data']['kerjasamaantartimpengajar'];
            $dataPPD->penampilanfasilitator=$request['data']['penampilanfasilitator'];
            $dataPPD->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'NorecEvaluasi' => $idPP,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Gagal Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'NorecEvaluasi' => $idPP,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarEvaluasiPenyelenggara(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('evaluasipenyelenggara_t as evn')
            ->JOIN('evaluasipenyelenggaradetail_t as evnd','evnd.evaluasipenyelenggarafk','=','evn.norec')
            ->JOIN('strukplanning_t as sp','sp.norec','=','evn.noplanningfk')
            ->Leftjoin('pegawai_m as pg','pg.id','=','evn.objectpegawaifk')
            ->Leftjoin('narasumber_m as na','na.id','=','evn.narasumberfk')
            ->select(DB::raw("sp.noplanning,evn.statusenabled,evn.norec,evn.tglevaluasi,evn.fasilitator,evn.keteranganpelatihan, 
                             evn.materi,evn.saran,evn.objectpegawaifk,pg.namalengkap,evn.penyelenggara,
                             evnd.efektivitaspenyelenggaraan,evnd.persiapan,evnd.dapatditerapkanklinik,
                             evnd.hubunganpeserta,evnd.hubunganantarpeserta,evnd.kebersihan,evnd.kebersihantoilet"));

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('evn.norec','=', $request['norecOrder']);
        }
        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('evn.tglevaluasi','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('evn.tglevaluasi','<=', $tgl);
        }
        if(isset($request['NamaFasilitator']) && $request['NamaFasilitator']!="" && $request['NamaFasilitator']!="undefined"){
            $data = $data->where('evn.fasilitator','ilike','%'. $request['NamaFasilitator']);
        }
        if(isset($request['judulMateri']) && $request['judulMateri']!="" && $request['judulMateri']!="undefined"){
            $data = $data->where('evn.materi','ilike','%'. $request['judulMateri']);
        }

        $data = $data->where('evn.statusenabled',true);
        $data = $data->where('evn.kdprofile',(int) $this->getDataKdProfile($request));
         
        $data = $data->orderBy('evn.tglevaluasi');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function hapusDataEvaluasiPenyelenggara(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = EvaluasiPenyelenggara::where('norec', $request['data']['norec'])
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
            $logUser->kdprofile= (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Evaluasi Penyelenggara';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Evaluasi Penyelenggara';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
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
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarEvaluasiNarasumber(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('evaluasinarasumber_t as evn')
            ->JOIN('evaluasinarasumberdetail_t as evnd','evnd.evaluasinarasumberfk','=','evn.norec')
            ->JOIN('strukplanning_t as sp','sp.norec','=','evn.noplanningfk')
            ->Leftjoin('pegawai_m as pg','pg.id','=','evn.objectpegawaifk')
            ->select('sp.noplanning','evn.statusenabled','evn.norec','evn.tglevaluasi','evn.fasilitator','evn.keteranganpelatihan','evn.manfaatpelatihan','evn.manfaatfasilitator',
                'evn.materi','evn.saran','evn.objectpegawaifk','pg.namalengkap','evnd.penguasaanmateri','evnd.ketepatanwaktu','evnd.sistematikapenyajian',
                'evnd.penggunaanmetodedanalatbantu','evnd.empati','evnd.penggunaanbahasa','evnd.pemberianmotivasi','evnd.isimateri','evnd.penyajianmateri',
                'evnd.dapatditerapkanklinik','evnd.kesempatantanyajawab','evnd.pencapaiantujuanpembelajaran','evnd.kerjasamaantartimpengajar','evnd.penampilanfasilitator');

        if(isset($request['norecOrder']) && $request['norecOrder']!="" && $request['norecOrder']!="undefined"){
            $data = $data->where('evn.norec','=', $request['norecOrder']);
        }
        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('evn.tglevaluasi','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('evn.tglevaluasi','<=', $tgl);
        }
        if(isset($request['NamaFasilitator']) && $request['NamaFasilitator']!="" && $request['NamaFasilitator']!="undefined"){
            $data = $data->where('evn.fasilitator','ilike','%'. $request['NamaFasilitator']);
        }
        if(isset($request['judulMateri']) && $request['judulMateri']!="" && $request['judulMateri']!="undefined"){
            $data = $data->where('evn.materi','ilike','%'. $request['judulMateri']);
        }
        $data = $data->where('evn.kdprofile',(int) $this->getDataKdProfile($request));
        $data = $data->where('evn.statusenabled',true);
        $data = $data->orderBy('evn.tglevaluasi');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function hapusDataEvaluasiNarasumberKompetensi(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = EvaluasiNarasumber::where('norec', $request['data']['norec'])
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
            $logUser->kdprofile= (int) $this->getDataKdProfile($request);
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Evaluasi Narasumber';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Evaluasi Narasumber';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
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
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataNarasumber(Request $request){
        $data = \DB::table('narasumber_m as na ')
            ->join ('jeniskelamin_m as km','km.id','=','na.objectjeniskelaminfk')
            ->join ('pendidikan_m as pe','pe.id','=','na.objectpendidikanterakhirfk')
            ->select('na.*','km.jeniskelamin','pe.pendidikan')
            ->where('na.statusenabled',true)
            ->where('na.kdprofile',(int) $this->getDataKdProfile($request))
            ->get();
        $result = array(
            'data' => $data,
            'by' => 'ea@epic',
        );
        return $result;
    }

    public function saveDataNarasumber(Request $request){
        DB::beginTransaction();
        try{
            $newId = Narasumber::max('id') +1 ;
            if ($request['id'] == ''){
                $TP = new Narasumber();
                $TP->id = $newId;
                $TP->kdprofile = (int) $this->getDataKdProfile($request);
                $TP->kdnarasumber = $newId;
                $TP->kodeexternal = $newId;
                $TP->norec = $TP->generateNewId();
            }else{
                $TP = Narasumber::where('id', $request['id'])->first();
            }
            $TP->statusenabled = true;//$request['statusenabled'];

            $TP->reportdisplay = $request['namalengkap'];
            $TP->namaexternal = $request['namalengkap'];
            $TP->objectjeniskelaminfk = $request['objectjeniskelaminfk'];
            $TP->objectpendidikanterakhirfk = $request['objectpendidikanterakhirfk'];
            $TP->namalengkap =$request['namalengkap'];
            $TP->namapanggilan =$request['namapanggilan'];
            $TP->tempatlahir =$request['tempatlahir'];
            $TP->tgllahir =$request['tgllahir'];
            $TP->email =$request['email'];
            $TP->nohandphone =$request['nohandphone'];
            $TP->nama =$request['namalengkap'];
            $TP->save();

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
                'result' => $TP,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = 'Simpan Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusNarasumber(Request $request){
        DB::beginTransaction();
        try{

            Narasumber::where('id',$request['id'])->update(['statusenabled'=>false]);

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
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
      public function getMapKatPendidikanTOprogram(Request $request){
        $data = DB::select(DB::raw("SELECT mp.*,dk.diklatprogram,ju.diklatjurusan,dk.diklatkategorifk,dt.diklatkategori
FROM mapdiklatprogramtodiklatjurusan_m as mp
join diklatprogram_m as dk on dk.id=mp.diklatprogramfk
join diklatjurusan_m as ju on ju.id=mp.diklatjurusanfk
join diklatkategori_m as dt on dt.id=dk.diklatkategorifk
 
     "));
        $result = array(
            'data' => $data,
            'by' => 'ea@epic',
        );
        return $result;
    }
}
