<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 9/4/2019
 * Time: 4:53 PM
 */

namespace App\Http\Controllers\Radiologi;

use App\Http\Controllers\ApiController;
use App\Master\JenisPetugasPelaksana;
use App\Master\Produk;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\HasilPemeriksaan;
use App\Transaksi\HasilRadiologi;
use App\Transaksi\HasilRadiologiListGambar;
use App\Transaksi\LisOrder;
use App\Transaksi\LisOrderTmp;
use App\Transaksi\LoggingUser;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDelete;
use App\Transaksi\PelayananPasienDetail;
use App\Master\Ruangan;
use App\Transaksi\PelayananPasienPetugas;

use App\Transaksi\RisOrder;
use App\Transaksi\StrukHasilPemeriksaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\OrderProduk;
use App\Master\Pegawai;
use App\Master\Pasien;
use App\Traits\Valet;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;

use Exception;

class RadiologiController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
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

        $pmi = \DB::table('pmi_m')
            ->select('id','pmi')
            ->where('statusenabled',true)
            ->where('kdprofile',$idProfile)
            ->orderBy('id')
            ->get();

        $result = array(
            'dokter' => $dataDokter,
            'golongandarah' =>   $golonganDarah,
            'pmi' => $pmi,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function getRincianPelayanan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
            ->leftjoin('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->leftjoin('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('mlu.kdprofile',$idProfile)
            ->where('objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $pelayanan=[];
        $departemenfk = [];
        if(count($dataruangan) > 0 ){
            foreach ($dataruangan as $item){
                $departemenfk []  =$item->objectdepartemenfk ;
            }
        }
        $result = [];
        if(count($departemenfk) > 0 ) {
            if (in_array($this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile), $departemenfk)) {
                $pelayanan = \DB::table('pelayananpasien_t as pp')
                    ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
                    ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                    ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                    ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
                    ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
                    ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                    ->JOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                    ->leftJOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                    ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
                    ->leftJOIN('strukorder_t as so', 'so.norec', '=', 'pp.strukorderfk')
//                    ->leftJOIN('orderpelayanan_t op', 'so.norec', '=', 'op.strukorderfk') // syamsu tambahan
                    ->leftjoin('pmi_m as pmi','pmi.id','=','pp.pmifk')
                   ->leftJOIN('ris_order as ris', 'ris.order_no', '=',
                       DB::raw('so.noorder AND ris.order_code=cast(pp.produkfk as text)'))
                     ->leftJOIN('hasilradiologi_t AS hr','hr.pelayananpasienfk','=',
                        DB::raw("pp.norec AND hr.statusenabled = true "))
                    // ->leftJOIN('ris_order as ris', 'ris.order_no', '=',
                        // DB::raw('so.noorder AND ris.order_code=pp.produkfk'))
                    ->select('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                        'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                        'dp.namadepartemen', 'ps.id as psid', 'apd.norec as norec_apd', 'sp.norec as norec_sp', 'pp.norec as norec_pp',
                        'ru.objectdepartemenfk', 'so.noorder', 'ris.order_key as idbridging', 'apd.objectruanganfk','pp.iscito','pp.jasa','so.keteranganlainnya',
                        'ps.objectjeniskelaminfk','ps.tgllahir','sbm.nosbm','pmi.pmi',  'ris.order_cnt as nourutrad', // syamsu tambahan
                        DB::raw("case when ris.order_key is not null then 'Sudah Dikirim' else '-' end as statusbridging,hr.norec as  hr_norec"))
                    ->where('pp.kdprofile',$idProfile)
                    ->where('ru.objectdepartemenfk', $this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile))
                    ->groupBy('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                        'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                        'dp.namadepartemen', 'ps.id', 'apd.norec', 'sp.norec', 'pp.norec',
                        'ru.objectdepartemenfk', 'so.noorder', 'ris.order_key', 'apd.objectruanganfk','pp.iscito','pp.jasa', 'hr.norec','sbm.nosbm','pmi.pmi','so.keteranganlainnya')
                   
                    ->orderBy('pp.tglpelayanan');

            }
            if (in_array($this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $idProfile), $departemenfk)) {
                $pelayanan = \DB::table('pelayananpasien_t as pp')
                    ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
                    ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                    ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                    ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
                    ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
                    ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                    ->JOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                    ->leftjoin('pmi_m as pmi','pmi.id','=','pp.pmifk')
                    ->leftJOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                    ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
                    ->leftJOIN('strukorder_t as so', 'so.norec', '=', 'pp.strukorderfk')
//                    ->leftJOIN('orderpelayanan_t op', 'so.norec', '=', 'op.strukorderfk') // syamsu tambahan
//                    ->leftJOIN('lisorder as lis', 'lis.ono', '=', 'so.noorder')
                    ->leftJOIN('order_lab as lis', 'lis.no_lab', '=',
                        DB::raw("so.noorder AND 
                        lis.kode_test =cast (pr.id as VARCHAR)   "))
                    ->select('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                        'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                        'dp.namadepartemen', 'ps.id as psid', 'apd.norec as norec_apd', 'sp.norec as norec_sp', 'pp.norec as norec_pp',
                        'ru.objectdepartemenfk', 'so.noorder',
                        'lis.no_lab as idbridging','so.keteranganlainnya',
                        'apd.objectruanganfk','pp.iscito','pp.jasa','ps.objectjeniskelaminfk','ps.tgllahir','sbm.nosbm','pmi.pmi','so.keteranganlainnya'
                        ,DB::raw("case when lis.no_lab is not null then 'Sudah Dikirim' else '-' end as statusbridging,'' as hr_norec , '1' as nourutrad") // syamsu tambahan
                    )
                    ->where('pp.kdprofile',$idProfile)
                    ->where('ru.objectdepartemenfk', $this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $idProfile))
                    ->orderBy('pp.tglpelayanan');
            }

            if (in_array($this->settingDataFixed('kdDepartemenElektromedik', $idProfile), $departemenfk)) {
                $pelayanan = \DB::table('pelayananpasien_t as pp')
                    ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
                    ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                    ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                    ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
                    ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
                    ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                    ->JOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                    ->leftJOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                    ->leftjoin('pmi_m as pmi','pmi.id','=','pp.pmifk')
                    ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
                    ->leftJOIN('strukorder_t as so', 'so.norec', '=', 'pp.strukorderfk')
//                    ->leftJOIN('orderpelayanan_t op', 'so.norec', '=', 'op.strukorderfk') // syamsu tambahan
//                    ->leftJOIN('lisorder as lis', 'lis.ono', '=', 'so.noorder')
                    ->select('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                        'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                        'dp.namadepartemen', 'ps.id as psid', 'apd.norec as norec_apd', 'sp.norec as norec_sp', 'pp.norec as norec_pp',
                        'ru.objectdepartemenfk', 'so.noorder', 'apd.objectruanganfk','pp.iscito','pp.jasa','ps.objectjeniskelaminfk','ps.tgllahir','so.keteranganlainnya',
                        'sbm.nosbm','pmi.pmi', 
                        DB::raw("'-' as statusbridging, null as idbridging,'' as hr_norec , '1' as nourutrad")) // syamsu tambahan
                    ->where('pp.kdprofile',$idProfile)
                    ->where('ru.objectdepartemenfk', $this->settingDataFixed('kdDepartemenElektromedik', $idProfile))
                    ->orderBy('pp.tglpelayanan');
            }
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
                    ->where('pd.kdprofile',$idProfile)
                    ->where('ptu.objectjenispetugaspefk', 4)
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
//                    $noorder =$item->noorder;
//                    if (in_array($this->settingDataFixed('KdDepartemenInstalasiLaboratorium'), $departemenfk)) {
//                        $order_lab = DB::select(DB::raw("
//                            select  top 1 * from order_lab where no_lab='$noorder'
//                        "));
//                        if(count($order_lab) > 0){
//                            $item->statusbridging = 'Sudah Dikirim';
//                        }else{
//                            $item->statusbridging = '-';
//                        }
//                    }
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
                        'nourutrad' => $item->nourutrad,
                        'dokter' => $NamaDokter,
                        'dokterid' => $DokterId,
                        'noorder' => $item->noorder,
                        'idbridging' => $item->idbridging,
                        'statusbridging' => $item->statusbridging,
                        'iscito' => $item->iscito,
                        'jasa' => (float)$item->jasa,
                        'objectjeniskelaminfk' => $item->objectjeniskelaminfk,
                        'tgllahir' => $item->tgllahir,
                        'nosbm' => $item->nosbm,
                        'hr_norec' => $item->hr_norec,
                        'pmi' => $item->pmi,
                        'keteranganlainnya' => $item->keteranganlainnya
                    );
                }
            } else {
                $result = [];
            }
        }
        $dataTea =array(
            'data' => $result,
            'detaillogin' => $dataLogin,
            'message' => 'Inhuman'
        );
        return $this->respond($dataTea);
    }
    public function saveOrderPelayananLabRad(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detLogin =$request->all();
        if( $request['pegawaiorderfk']==""){
            $dokter2 = null;
        }else{
            $dokter2 = $request['pegawaiorderfk'];
        }

        DB::beginTransaction();
        try{
            if ($request['departemenfk'] == 3) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderlab', 11,'L' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'L' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 27) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderrad', 11,'R' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'R' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 25) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderok', 11,'OK' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'OK' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 5) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderpj', 11,'PJ' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'PJ' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 31) {
                $noOrder = $this->generateCodeBySeqTable(new StrukOrder, 'noorderabm', 11,'ABM' . date('ym'),$idProfile);
                // $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'ABM' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($noOrder == ''){
                $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                DB::rollBack();
                $result = array(
                    "status" => 400,
                    "message"  => $transMessage,
                    "as" => 'as@epic',
                );
                return $this->setStatusCode($result['status'])->respond($result, $transMessage);
            }

            $dataPD = PasienDaftar::where('norec',$request['norec_pd'])->first();
            if ($request['norec_so']=="") {
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
                    if ($request['status']== 'bridinglangsung'){
                        $updatePP=PelayananPasien::where('norec',$item['norec_pp'])
                            ->update([
                                    'strukorderfk' => $dataSOnorec
                                ]
                            );
                    }

                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if(isset($item['iscito'])){
                        $dataOP->iscito =(float) $item['iscito'];
                    }else{
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
                    if(isset( $item['pemeriksaanluar'])){
                        if($item['pemeriksaanluar'] == 1){
                            $dataOP->keteranganlainnya =  'isPemeriksaanKeluar';
                        }
                    }

                    if (isset($item['tglrencana'])){
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    }else{
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    $dataOP->strukorderfk = $dataSOnorec;
                    if (isset($item['dokterid']) && $item['dokterid']!=""){
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->nourut = $item['nourut'];
                    $dataOP->save();
                }

            }else {

                foreach ($request['details'] as $item) {
                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if(isset($item['iscito'])){
                        $dataOP->iscito =(float) $item['iscito'];
                    }else{
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

                    if (isset($item['tglrencana'])){
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    }else{
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    if (isset($item['dokterid']) && $item['dokterid']!=""){
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->nourut = $item['nourut'];
                    $dataOP->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan OrderPelayanan";
        }
        
        

        if ($transStatus == 'true') {
            if ($request['norec_so']=="") {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
                    "strukorder" => $dataSO,
                    "as" => 'inhuman',
                );
            }else{
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
                "message"  => $transMessage,
//                "nokirim" => $dataSO,//$noResep,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
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
    public function getComboRad(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile',$idProfile )
            ->whereIn('dp.id',array(3,14,16,17,18,19,24,25,26,27,28,35))
            ->where('dp.statusenabled',true)
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile )
            ->where('ru.statusenabled',true)
            ->get();
        foreach ($dataInstalasi as $item){
            $detail=[];
            foreach ($dataRuangan  as $item2){
                if ($item->id == $item2->objectdepartemenfk){
                    $detail[] =array(
                        'id' =>   $item2->id,
                        'ruangan' =>   $item2->namaruangan,
                    );
                }
            }

            $dataDepartemen[]=array(
                'id' =>   $item->id,
                'departemen' =>   $item->namadepartemen,
                'ruangan' => $detail,
            );
        }
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id','kp.kelompokpasien')
            ->where('kp.statusenabled',true)
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile )
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataJenisKelamin = \DB::table('jeniskelamin_m as jk')
            ->where('jk.statusenabled', true)
            ->orderBy('jk.jeniskelamin')
            ->get();
        $golonganDarah = \DB::table('golongandarah_m')
            ->select('id','golongandarah')
            ->where('statusenabled',true)
            ->orderBy('golongandarah')
            ->get();
        $dokter = \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('kdprofile',$idProfile )
            ->where('statusenabled',true)
            ->where('objectjenispegawaifk',1)
            ->orderBy('namalengkap')
            ->get();
        $deptRanap = explode (',',$this->settingDataFixed('kdDepartemenRanapFix', $idProfile));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap){
            $kdDepartemenRawatInap []=  (int)$itemRanap;
        }
        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('ru.kdprofile',$idProfile )
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatInap)
            ->orderBy('ru.namaruangan')
            ->get();
        $result = array(
            'golongandarah' => $golonganDarah,
            'departemen' => $dataDepartemen,
            'kelompokpasien' =>   $dataKelompok,
            'ruangan' => $dataRuangan,
            'jeniskelamin' => $dataJenisKelamin,
            'ruanganinap' =>   $dataRuanganInap,
            'dokter' =>   $dokter,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarPasienPenunjang(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
            ->leftjoin('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->leftjoin('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('mlu.kdprofile', $idProfile)
            ->where('objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
//        return $this->respond($dataruangan);

        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->JOIN('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->Join('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJoin('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->leftJoin('kelas_m as kl','kl.id','=','pd.objectkelasfk')
            ->leftJoin('strukpelayanan_t as sp','sp.norec','=','pd.nostruklastfk')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftJoin('golongandarah_m as gol','gol.id','=','ps.objectgolongandarahfk')
            ->leftjoin('strukorder_t as so','so.norec','=','apd.objectstrukorderfk')
            ->leftJoin('ruangan_m as ru1','ru1.id','=','so.objectruanganfk')
            ->select('apd.norec as norec_apd','ru.id as ruid','ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
                'kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id as klid',
                'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','apd.norec','pd.norec as norec_pd',
                'sp.tglstruk','pd.nostruklastfk','alm.alamatlengkap','gol.golongandarah', 'apd.tglmasuk','ru1.namaruangan as ruanganasal',
                DB::raw("'' AS expertise
                "))
            ->where('apd.kdprofile', $idProfile)
            ->where('ru.objectdepartemenfk',$dataruangan[0]->objectdepartemenfk)
//            ->where('apd.objectruanganfk',$dataruangan[0]->id)
            ->orderBy('apd.tglregistrasi','desc');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('apd.tglmasuk','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('apd.tglmasuk','<=', $tgl);
        }
//        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
//            $data = $data->where('so.tglorder','>=', $request['tglAwal']);
//        }
//        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
//            $tgl= $request['tglAkhir'];
//            $data = $data->where('so.tglorder','<=', $tgl);
//        }
        if(isset($request['deptId']) && $request['deptId']!="" && $request['deptId']!="undefined"){
            $data = $data->where('dept.id','=', $request['deptId']);
        }
        if(isset($request['ruangId']) && $request['ruangId']!="" && $request['ruangId']!="undefined"){
            $data = $data->where('ru.id','=', $request['ruangId']);
        }
        if(isset($request['kelId']) && $request['kelId']!="" && $request['kelId']!="undefined"){
            $data = $data->where('kp.id','=', $request['kelId']);
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
        if(isset($request['jmlRow']) && $request['jmlRow']!="" && $request['jmlRow']!="undefined"){
            $data = $data->take($request['jmlRow']);
        }
//        $data = $data->orderBy('apd.tglregistrasi');
        $data = $data->get();
        $apdnorec= [];
        foreach ($data as $key => $v) {
            $norecapd = $v->norec_apd;
            $apdnorec [] = $v->norec_apd;
        }
        $hasilRad = DB::table('pelayananpasien_t as pp')
            ->join('hasilradiologi_t as hh','pp.norec','=','hh.pelayananpasienfk')
            ->distinct()
            ->select('hh.tanggal', 'pp.noregistrasifk as norec_apd')
            ->whereIn('pp.noregistrasifk',$apdnorec)
            ->orderBy('hh.tanggal','desc')
            ->get();
        $hasilLab = DB::table('pelayananpasien_t as pp')
            ->join('hasillaboratorium_t as hh','pp.norec','=','hh.norecpelayanan')
            ->distinct()
            ->select('hh.tglhasil as tanggal', 'pp.noregistrasifk as norec_apd')
            ->whereIn('pp.noregistrasifk',$apdnorec)
            ->orderBy('hh.tglhasil','desc')
            ->get();
        $hasilLabPA = DB::table('pelayananpasien_t as pp')
            ->join('hasilpemeriksaanlab_t as hh','pp.norec','=','hh.pelayananpasienfk')
            ->distinct()
            ->select('hh.tanggal', 'pp.noregistrasifk as norec_apd')
            ->whereIn('pp.noregistrasifk',$apdnorec)
            ->orderBy('hh.tanggal','desc')
            ->get();


        $dataMerge =  array_merge($hasilRad,$hasilLab,$hasilLabPA);
        // return $dataMerge;
        $i=0;
        foreach ($data as $key => $v) {
            $data[$i]->expertise =false;
            $data[$i]->tglexpertise =null;
            foreach ($dataMerge as $key2 => $v2) {
                if($data[$i]->norec_apd ==  $v2->norec_apd){
                    $data[$i]->expertise =true;
                    $data[$i]->tglexpertise =$v2->tanggal;
                }
            }
            $i = $i + 1;
        }
        $result = array(
            "data" => $data,
            "ruanganlogin" => $dataruangan,
            "as" => 'er@epic',
        );
        return $this->respond($result);
    }
    public function updateJenisKelaminPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {

            $dataApr = Pasien::where('nocm', $request['norm'])
                ->where('kdprofile', $idProfile)
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
    public function updateGolonganDarah(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {

            $dataApr = Pasien::where('nocm', $request['norm'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'objectgolongandarahfk' => $request['golongandarahfk'],
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
    public function getComboRegs(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.id', '18')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
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
        $golonganDarah = \DB::table('golongandarah_m')
            ->select('id','golongandarah')
            ->where('statusenabled',true)
            ->orderBy('golongandarah')
            ->get();
        $jk = \DB::table('jeniskelamin_m')
            ->select('id','jeniskelamin')
            ->where('statusenabled',true)
            ->orderBy('jeniskelamin')
            ->get();



        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'golongandarah' => $golonganDarah,
            'dept' => $dept,
            'ruanganall' => $dataRuangan,
            'jeniskelamin' => $jk,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarRegistrasiPasienLabRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            //            ->leftjoin('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            // ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            // ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            // ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            // ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            // ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')

           ->leftJoin('jenispelayanan_m as jp','pd.jenispelayanan','=',
               DB::raw('cast(jp.id as text)'))
            // ->leftJoin('jenispelayanan_m as jp','jp.id','=','pd.jenispelayanan')
            ->leftJoin('golongandarah_m as gol','gol.id','=','ps.objectgolongandarahfk')
            ->select('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
                'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                // 'pa.norec as norec_pa', 'pa.objectasuransipasienfk',
                'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                // 'pa.nosep as nosep',
                // 'br.norec as norec_br',
                 'pd.nostruklastfk','pd.objectkelasfk','kls.namakelas',
                'ps.tgllahir','ps.objectjeniskelaminfk','jk.jeniskelamin','alm.alamatlengkap','pd.jenispelayanan as idjenispelayanan','jp.jenispelayanan',
                'gol.golongandarah')
            ->where('pd.kdprofile', $idProfile)
            // ->whereNull('br.norec');
                   ->where('pd.statusenabled', true);

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
        // $data = $data->groupBy('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi',
        //     'ru.namaruangan', 'ps.namapasien',
        //     'br.norec',
        //     'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap', 'pd.tglpulang', 'pd.statuspasien',
        //     // 'pa.nosep', ', 'pa.norec', 'pa.objectasuransipasienfk',
        //      'pd.objectpegawaifk', 'pd.objectruanganlastfk',
        //     'pd.nostruklastfk', 'ps.tgllahir','pd.objectkelasfk','kls.namakelas','ps.objectjeniskelaminfk','jk.jeniskelamin',
        //     'alm.alamatlengkap','pd.jenispelayanan','jp.jenispelayanan','gol.golongandarah');
//        $data = $data->take($filter['jmlRows']);
        $data = $data->get();
        return $this->respond($data);
    }
    public function getAPD(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->select('apd.norec as norec_apd', 'ps.nocm', 'ps.id as nocmfk', 'ps.namapasien', 'pd.noregistrasi',
                'apd.objectruanganfk as id','ru.objectdepartemenfk',
                'ru.namaruangan', 'apd.tglregistrasi', 'kls.namakelas', 'apd.objectruanganasalfk')
            ->where('apd.kdprofile', $idProfile)
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->orderBy('pd.objectruanganlastfk')
            ->get();

        $result = array(
            'ruangan' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
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
            $dataAPD->objectkelasfk = $request['objectkelasfk'];
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
    public function getAntrian(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $apd = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->leftjoin('strukorder_t as so','so.norec','=','apd.objectstrukorderfk')
            ->select('apd.norec','so.noorder','apd.objectstrukorderfk','apd.objectruanganfk','pd.noregistrasi')
            ->where('apd.kdprofile', $idProfile)
            ->where('pd.noregistrasi',$request['noregistrasi'])
            ->where('so.noorder',$request['noorder'])
            ->get();
        if (count($apd) < 1){
            $apd = \DB::table('antrianpasiendiperiksa_t as apd')
                ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
                ->leftjoin('strukorder_t as so','so.norec','=','apd.objectstrukorderfk')
                ->select('apd.norec','so.noorder','apd.objectstrukorderfk','apd.objectruanganfk','pd.noregistrasi')
                ->where('apd.kdprofile', $idProfile)
                ->where('pd.noregistrasi',$request['noregistrasi'])
                ->where('apd.objectruanganfk',$request['idruangan'])
                ->get();
        }
        $result = array(
            'data' => $apd,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDaftarOrderPenunjang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
            ->leftjoin('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->leftjoin('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('mlu.kdprofile', $idProfile)
            ->where('objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $departemenfk = [];
        if(count($dataruangan) > 0 ){
            foreach ($dataruangan as $item){
                $departemenfk []  =$item->objectdepartemenfk ;
            }
        }
        $result = [];
        if (in_array($this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $idProfile), $departemenfk)) {
            $data = \DB::table('strukorder_t as so')
                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
                // ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->leftjoin('strukpelayanan_t as sps', 'sps.norec', '=', 'pd.nostruklastfk')
                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
                ->join('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
                ->join('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
                ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->join('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
                ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
                ->Join('antrianpasiendiperiksa_t as apd', function($join){
                    $join->on('apd.noregistrasifk','=','pd.norec');
                    $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
                })
//                ->leftJoin('lisorder as pp', 'pp.ono', '=', 'so.noorder')
                ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.tglpulang', 'ps.nocm', 'ps.namapasien','kps.kelompokpasien',
                    'klm.jeniskelamin', 'ps.tgllahir',
                    'kps.kelompokpasien', 'dp.namadepartemen', 'pd.objectkelasfk', 'kls.namakelas','kls.id as klsid', 'so.objectruangantujuanfk',
                    'so.objectruanganfk', 'pd.objectkelompokpasienlastfk', 'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan',
                    'so.objectpegawaiorderfk', 'pg.namalengkap as pegawaiorder',
//                    'pp.norec as norec_pp',
                    'ru.namaruangan','ru.id as ruid', 'ru2.namaruangan as ruangantujuan','so.tglorder','sps.nostruk','apd.norec as norec_apd',
                    // 'apd.norec as norec_apd',
                    'so.keteranganlainnya','so.cito',
//                    (DB::raw("case when pp.ono is null then 'MASUK' else
//                                    'SELESAI' end as status")))
                    (DB::raw("case when so.statusorder is null then 'MASUK' else
                                    'SELESAI' end as status")))
                ->where('so.kdprofile', $idProfile)
                ->where('ru2.objectdepartemenfk',$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $idProfile))
                ->where('so.statusenabled',true) ;
            if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
                if ($request['isNotVerif'] == true) {
                    $data = $data->whereNull('so.statusorder');
                }
            }
//            if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
//                if ($request['isNotVerif'] == true) {
//                    $data = $data->whereNull('pp.ono');
//                }
//            }
        }

        if (in_array($this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile), $departemenfk)) {
            if(isset($request['ruanganTujuanId']) && $request['ruanganTujuanId']!=''){
                $data = \DB::table('strukorder_t as so')
                    ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
//                    ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                    // ->join('antrianpasiendiperiksa_t as apd',function($join)
                    // {
                    //     $join->on('apd.noregistrasifk','=','pd.norec')
                    //         ->where('apd.objectruanganfk','=', 576);
                    //     //                $join->on('spp.keteranganlainnya','=','Setoran Kasir');
                    // })
                    ->leftjoin('strukpelayanan_t as sps', 'sps.norec', '=', 'pd.nostruklastfk')
                    ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                    ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
                    ->join('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
                    ->join('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
                    ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                    ->join('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
                    ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                    ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                    ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
                    ->Join('antrianpasiendiperiksa_t as apd', function($join){
                        $join->on('apd.noregistrasifk','=','pd.norec');
                        $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
                    })
//                    ->leftJoin('ris_order as pp', 'pp.order_no', '=', 'so.noorder')
                    ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.tglpulang', 'ps.nocm', 'ps.namapasien','kps.kelompokpasien',
                        'klm.jeniskelamin', 'ps.tgllahir',
                        'kps.kelompokpasien', 'dp.namadepartemen', 'pd.objectkelasfk', 'kls.namakelas','kls.id as klsid', 'so.objectruangantujuanfk',
                        'so.objectruanganfk', 'pd.objectkelompokpasienlastfk', 'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan',
                        'so.objectpegawaiorderfk', 'pg.namalengkap as pegawaiorder', 'so.tglorder', 'sps.nostruk',
                        'ru.namaruangan', 'ru2.namaruangan as ruangantujuan',     'so.keteranganlainnya','apd.norec as norec_apd',
                        // 'apd.norec as norec_apd',
                        'ru.id as ruid','so.cito',
                        (DB::raw("case when so.statusorder is null then 'MASUK' else
                                    'SELESAI' end as status")))
                    ->where('so.kdprofile', $idProfile)
                    ->where('ru2.objectdepartemenfk', $this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile))
                    ->where('so.statusenabled', true)
                    ->where('so.objectruangantujuanfk',$request['ruanganTujuanId']);
                if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
                    if ($request['isNotVerif'] == true) {
                        $data = $data->whereNull('so.statusorder');
                    }
                }
            }else{
                $data = \DB::table('strukorder_t as so')
                    ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
//                    ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                    // ->join('antrianpasiendiperiksa_t as apd',function($join)
                    // {
                    //     $join->on('apd.noregistrasifk','=','pd.norec')
                    //         ->where('apd.objectruanganfk','=', 576);
                    //     //                $join->on('spp.keteranganlainnya','=','Setoran Kasir');
                    // })
                    ->leftjoin('strukpelayanan_t as sps', 'sps.norec', '=', 'pd.nostruklastfk')
                    ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                    ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
                    ->join('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
                    ->join('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
                    ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                    ->join('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
                    ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                    ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                    ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
                    ->leftJoin('ris_order as pp', 'pp.order_no', '=', 'so.noorder')
                    ->Join('antrianpasiendiperiksa_t as apd', function($join){
                        $join->on('apd.noregistrasifk','=','pd.norec');
                        $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
                    })
                    ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.tglpulang', 'ps.nocm', 'ps.namapasien','kps.kelompokpasien',
                        'klm.jeniskelamin', 'ps.tgllahir',
                        'kps.kelompokpasien', 'dp.namadepartemen', 'pd.objectkelasfk', 'kls.namakelas','kls.id as klsid', 'so.objectruangantujuanfk',
                        'so.objectruanganfk', 'pd.objectkelompokpasienlastfk', 'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan',
                        'so.objectpegawaiorderfk', 'pg.namalengkap as pegawaiorder', 'so.tglorder', 'sps.nostruk','apd.norec as norec_apd',
                        // 'apd.norec as norec_apd',
                        'ru.namaruangan', 'ru2.namaruangan as ruangantujuan',
                             'so.keteranganlainnya','so.cito',
                        (DB::raw("case when pp.order_no is null then 'MASUK' else
                                    'SELESAI' end as status")))
                    ->where('so.kdprofile', $idProfile)
                    ->where('ru2.objectdepartemenfk', $this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile))
                    ->where('so.statusenabled', true)
                    ->whereNotIn('so.objectruangantujuanfk',[$this->settingDataFixed('kdRuanganElektromedik', $idProfile)]);
                if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
                    if ($request['isNotVerif'] == true) {
                        $data = $data->whereNull('pp.order_no');
                    }
                }
            }



        }
        if (in_array($this->settingDataFixed('kdDepartemenElektromedik', $idProfile), $departemenfk)) {
            // $ruangani = Ruangan::where('objectdepartemenfk',$this->settingDataFixed('kdDepartemenElektromedik'))->get();
            // $ruanganiAr=[];
            // foreach ($ruangani as $key => $value) {
            //     $ruanganiAr[]=$value->id;
            //     # code...
            // }
            // return $ruanganiAr;
            $data = \DB::table('strukorder_t as so')
                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
//                ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
 //                 ->leftjoin('antrianpasiendiperiksa_t as apd',function($join)
 //                 {
 //                     $join->on('apd.noregistrasifk','=','pd.norec')
 //                         ->where('apd.objectruanganfk','=', 571);
 // //                $join->on('spp.keteranganlainnya','=','Setoran Kasir');
 //                 })
                ->leftjoin('strukpelayanan_t as sps', 'sps.norec', '=', 'pd.nostruklastfk')
                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
                ->join('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
                ->join('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
                ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->join('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
                ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
//                    ->leftJoin('ris_order as pp', 'pp.order_no', '=', 'so.noorder')
                ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.tglpulang', 'ps.nocm', 'ps.namapasien','kps.kelompokpasien',
                    'klm.jeniskelamin', 'ps.tgllahir',
                    'kps.kelompokpasien', 'dp.namadepartemen', 'pd.objectkelasfk', 'kls.namakelas', 'so.objectruangantujuanfk',
                    'so.objectruanganfk', 'pd.objectkelompokpasienlastfk', 'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan',
                    'so.objectpegawaiorderfk', 'pg.namalengkap as pegawaiorder', 'so.tglorder', 'sps.nostruk', 
                    // 'apd.norec as norec_apd',
                    // 'apd.norec as norec_apd',
                    'ru.namaruangan', 'ru2.namaruangan as ruangantujuan',
                         'so.keteranganlainnya','so.cito',
                    (DB::raw("case when so.statusorder is null then 'MASUK' else
                                    'SELESAI' end as status")))
                ->where('so.kdprofile', $idProfile)
                ->where('ru2.objectdepartemenfk', $this->settingDataFixed('kdDepartemenElektromedik', $idProfile))
                ->where('so.statusenabled', true);
//                ->where('so.objectruangantujuanfk', $request['ruanganTujuanId']);
            if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
                if ($request['isNotVerif'] == true) {
                    $data = $data->whereNull('so.statusorder');
                }
            }
        }

        if (in_array($this->settingDataFixed('KdInstalasiBedahSentral', $idProfile), $departemenfk)) {
            $data = \DB::table('strukorder_t as so')
                ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
                ->join('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
                ->join('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
                ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->join('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
                ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
                ->Join('antrianpasiendiperiksa_t as apd', function($join){
                    $join->on('apd.noregistrasifk','=','pd.norec');
                    $join->on('apd.objectruanganfk','=','pd.objectruanganlastfk');
                })
                ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'pd.objectpegawaifk')
                ->leftJoin('pelayananpasien_t as pp', 'pp.strukorderfk', '=', 'so.norec')
                ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.tglpulang', 'ps.nocm', 'ps.namapasien','kps.kelompokpasien',
                    'klm.jeniskelamin', 'ps.tgllahir',
                    'kps.kelompokpasien', 'dp.namadepartemen', 'pd.objectkelasfk', 'kls.namakelas', 'so.objectruangantujuanfk',
                    'so.objectruanganfk', 'pd.objectkelompokpasienlastfk', 'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan',
                    'so.objectpegawaiorderfk', 'pg.namalengkap as pegawaiorder','so.tglorder',
                    'ru.namaruangan', 'ru2.namaruangan as ruangantujuan','so.tglpelayananakhir','pg2.namalengkap as dpjp','apd.norec as norec_apd','so.cito',
                    (DB::raw("case when pp.strukorderfk is null then 'MASUK' else
                                    'Sudah Verifikasi' end as status,'' as kddiagnosa")))
                ->where('so.kdprofile', $idProfile)
                ->where('ru2.objectdepartemenfk', $this->settingDataFixed('KdInstalasiBedahSentral', $idProfile))
                ->where('so.statusenabled',true);
                

            if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
                if ($request['isNotVerif'] == true) {
                    $data = $data->whereNull('so.statusorder');
//                    $data = $data->whereNull('pp.strukorderfk');
                }
            }
        }
//        if($dataruangan[0]->objectdepartemenfk != 27 && $dataruangan[0]->objectdepartemenfk != 3 && $dataruangan[0]->objectdepartemenfk != 25){
//            $tgltgltgl= date('Y-m-d H:i:s');
//            $data = $data->where('so.tglpelayananakhir','>=', $tgltgltgl);
//        }

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
        if(isset($request['deptId']) && $request['deptId']!="" && $request['deptId']!="undefined"){
            $data = $data->where('ru.objectdepartemenfk','=', $request['deptId']);
        }

        if(isset($request['pegId']) && $request['pegId']!="" && $request['pegId']!="undefined"){
            $data = $data->where('so.objectpegawaiorderfk','=', $request['pegId']);
        }
        if(isset($request['ruangId']) && $request['ruangId']!="" && $request['ruangId']!="undefined"){
            $data = $data->where('so.objectruanganfk','=', $request['ruangId']);
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
//        $data = $data->take(100);
        $data = $data->distinct();
        $data = $data->get();

        $norecaPd = '';
        foreach ($data as $ob){
            $norecaPd = $norecaPd.",'".$ob->norec_apd . "'";
            $ob->kddiagnosa = '';
        }
        $norecaPd = substr($norecaPd, 1, strlen($norecaPd)-1);
        $diagnosa = [];
        if($norecaPd!= ''){
            $diagnosa = DB::select(DB::raw("
               select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
               from detaildiagnosapasien_t as ddp
               left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
               left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
               where ddp.noregistrasifk in ($norecaPd) and ddp.objectjenisdiagnosafk = 1"));
            $i = 0;
//            $d['d']= $diagnosa;
//            $d['da']= $norecaPd;
//            return $this->respond($d);
           foreach ($data as $h){
            //    $data[$i]->kddiagnosa = [];
               foreach ($diagnosa as $d){
                   if($data[$i]->norec_apd == $d->norec_apd){
                       $data[$i]->kddiagnosa = $d->kddiagnosa;
                   }
               }
               $i++;
//               if($data[$i]->kddiagnosa!=''){
//                   $data[$i]->kddiagnosa = substr($data[$i]->kddiagnosa,1);
//               }
           }
        }

        $dataResult=array(
            'message' =>  'inhuman',
            'data' =>  $data,
            'dataruangan' => $dataruangan
        );
        return $this->respond($dataResult);
    }
    public function getDiagnosaRad( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.noregistrasi','pd.tglregistrasi','apd.objectruanganfk','ru.namaruangan','apd.norec as norec_apd',
                'ddp.objectdiagnosafk','dg.kddiagnosa','dg.namadiagnosa','ddp.tglinputdiagnosa','ddp.objectjenisdiagnosafk',
                'jd.jenisdiagnosa','dp.norec as norec_diagnosapasien','ddp.norec as norec_detaildpasien','ddp.tglinputdiagnosa',
                'pg.namalengkap',
                'dp.ketdiagnosis','ddp.keterangan','dg.*','dp.iskasusbaru','dp.iskasuslama')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->join ('detaildiagnosapasien_t as ddp','ddp.objectdiagnosapasienfk','=','dp.norec')
            ->join ('diagnosa_m as dg','dg.id','=','ddp.objectdiagnosafk')
            ->join ('jenisdiagnosa_m as jd','jd.id','=','ddp.objectjenisdiagnosafk')
            ->leftjoin('pegawai_m as pg','pg.id','=','ddp.objectpegawaifk')
            ->where('pd.kdprofile',$idProfile);
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };

        $data=$data->get();

        $result = array(
            'datas' => $data,
            'message' => 'giw',
        );
        return $this->respond($result);
    }
    public function getOrderPelayanan(Request $request) {
//        $dataLogin = $request->all();
        $idkelas = $request['objectkelasfk'];
        $norec_so = $request['norec_so'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $so = StrukOrder::where('norec',$norec_so)->where('kdprofile',$idProfile)->first();
        $pasienDaftar = PasienDaftar::where('norec',$so->noregistrasifk)
            ->where('kdprofile',$idProfile)
            ->first();
        $jp = (int)$pasienDaftar->jenispelayanan;
        $dataOrderPelayanan = DB::select(DB::raw("select DISTINCT op.norec as norec_op,pr.id as prid,pr.namaproduk,
                op.tglpelayanan,op.qtyproduk ,ru.namaruangan as ruangantujuan,ru.objectdepartemenfk,op.strukorderfk,so.objectruangantujuanfk,
                hnp.hargasatuan ,kls.namakelas,dpm.namadepartemen,
                pps.norec as norec_pp
                from orderpelayanan_t op
                left join strukorder_t as so on so.norec=op.strukorderfk
                INNER JOIN produk_m as pr on pr.id=op.objectprodukfk
                left JOIN harganettoprodukbykelas_m as hnp on pr.id=hnp.objectprodukfk
                        and '$idkelas' =hnp.objectkelasfk
                        and hnp.statusenabled=true
                        and hnp.objectjenispelayananfk=$jp
                --and op.objectkelasfk=hnp.objectkelasfk
                left join kelas_m as kls on kls.id = '$idkelas'
                --op.objectkelasfk
                left join ruangan_m as ru on ru.id =so.objectruangantujuanfk
                left join departemen_m as dpm on dpm.id=ru.objectdepartemenfk
                left JOIN pelayananpasien_t as pps on pps.strukorderfk=so.norec
                      and op.objectprodukfk =pps.produkfk
                where op.kdprofile = $idProfile and op.strukorderfk=:norec_so
                and kls.id=:objectkelasfk
                ORDER by op.tglpelayanan"),
            array(
                'norec_so' => $norec_so,
                'objectkelasfk' =>$idkelas ,
            )
        );

        $result=[];
        foreach ($dataOrderPelayanan as $item){
            $dataz =  DB::select(DB::raw("select  
                hnp.objectkomponenhargafk,kh.komponenharga,hnp.hargasatuan,
                hnp.objectprodukfk,hnp.objectjenispelayananfk
                from harganettoprodukbykelasd_m as hnp   
                inner join produk_m as prd on prd.id=hnp.objectprodukfk
                inner join komponenharga_m as kh on kh.id=hnp.objectkomponenhargafk
                inner join kelas_m as kls on kls.id = hnp.objectkelasfk
                where hnp.kdprofile = $idProfile and hnp.objectkelasfk='$idkelas'
                and hnp.statusenabled=true
                and hnp.objectjenispelayananfk=$jp
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
    public function savePelayananPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            StrukOrder::where('norec', $request['norec_so'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'statusorder' => 1
                ]
            );

//            if($request['norec_pp']=='') {
                $pd = PasienDaftar::where('norec',$request['norec_pd'])->where('kdprofile', $idProfile)->first();
                $apd = AntrianPasienDiperiksa::where('noregistrasifk',$pd->norec)
                    ->where('kdprofile', $idProfile)
                    ->where('objectruanganfk',$request['objectruangantujuanfk'])
                    ->where('statusenabled',true)
                    ->first();
                if(empty($apd)){
                    $dataAPD = new AntrianPasienDiperiksa;
                    $dataAPD->norec = $dataAPD->generateNewId();
                    $dataAPD->kdprofile = $idProfile;
                    $dataAPD->objectasalrujukanfk = 1;
                    $dataAPD->statusenabled = true;
                    $dataAPD->objectkelasfk = 6;// $request['objectkelasfk'];
                    $dataAPD->noantrian = 1;
                    $dataAPD->noregistrasifk = $request['norec_pd'];
                    $dataAPD->objectpegawaifk = $request['objectpegawaiorderfk'];
                    $dataAPD->objectruanganfk = $request['objectruangantujuanfk'];
                    $dataAPD->statusantrian = 0;
                    $dataAPD->statuspasien = 1;
                    $dataAPD->objectstrukorderfk = $request['norec_so'];
                    $dataAPD->tglregistrasi = $pd->tglregistrasi;// date('Y-m-d H:i:s');
                    $dataAPD->tglmasuk = date('Y-m-d H:i:s');
                    $dataAPD->tglkeluar = date('Y-m-d H:i:s');
                    $dataAPD->save();

                    $dataAPDnorec = $dataAPD->norec;
                    $dataAPDtglPel = $dataAPD->tglregistrasi;
                }else{
                    $dataAPDnorec  = $apd->norec;
                    $dataAPDtglPel = $apd->tglregistrasi;
                }
//            else{
//                $dataAPD =  PelayananPasien::where('norec',$request['norec_pp'])->first();
//                $dataAPDnorec = $dataAPD->noregistrasifk;
//                $dataAPDtglPel = $dataAPD->tglregistrasi;
//
//                $HapusPP = PelayananPasien::where('strukorderfk', $request['norec_so'])->get();
//                foreach ($HapusPP as $pp){
//                    $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->delete();
//                    $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->delete();
//                }
//                $Edit = PelayananPasien::where('strukorderfk', $request['norec_so'])->delete();
//            }

            $antrian = AntrianPasienDiperiksa::where('norec',$dataAPDnorec)
                ->update([
                    'ispelayananpasien' => true
                ]);

            foreach ($request['bridging'] as $item){
                $PelPasien = new PelayananPasien();
                $PelPasien->norec = $PelPasien->generateNewId();
                $PelPasien->kdprofile = $idProfile;
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
                $PelPasienPetugas->kdprofile = $idProfile;
                $PelPasienPetugas->statusenabled = true;
                $PelPasienPetugas->nomasukfk = $dataAPDnorec;
                $PelPasienPetugas->objectpegawaifk = $request['iddokterverif'];//$request['objectpegawaiorderfk'];

                $PelPasienPetugas->objectjenispetugaspefk = 4;//$jenisPetugasPe->objectjenispetugaspefk;
                $PelPasienPetugas->pelayananpasien = $PPnorec;
                $PelPasienPetugas->save();
                $PPPnorec = $PelPasienPetugas->norec;


                foreach ($item['komponenharga'] as $itemKomponen) {

                    $PelPasienDetail = new PelayananPasienDetail();
                    $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                    $PelPasienDetail->kdprofile = $idProfile;
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

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
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
    public function deleteOrderPelayanan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            $dataOP = OrderPelayanan::where('norec',$request['norec_op'])->where('kdprofile', $idProfile)->delete();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = " OrderPelayanan";
        }
        if ($transStatus == 'true') {
            $transMessage = "Delete Order Pelayanan";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "nokirim" => $dataSO,//$noResep,,//$noResep,
                "as" => 'inhuman',
            );
        } else {
            $transMessage = "Delete Order Pelayanan gagal!!";
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
    public function hapusOrderPenunjang(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{
            StrukOrder::where('norec',$request['norec'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'statusenabled' => false
                ]
            );
            $transStatus = 'true';
        } catch (Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Hapus Order ";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus Order Gagal";
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


    public function saveHasilRadiologi(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try{

            if ($request['norec']=="") {
                $dataSO = new HasilRadiologi();
                $dataSO->norec = $dataSO->generateNewId();
                $dataSO->kdprofile = $idProfile;
                $dataSO->statusenabled = true;
            }else {
                $dataSO =  HasilRadiologi::where('norec',$request['norec'])->first();
            }
            $dataSO->tanggal =$request['tglinput'];
            $dataSO->pegawaifk = $request['dokterid'];
            if(isset( $request['nofoto'])){
                $dataSO->nofoto = $request['nofoto'];
            }
            $dataSO->keterangan = $request['keterangan'];
            $dataSO->pelayananpasienfk = $request['pelayananpasienfk'];
            $dataSO->noregistrasifk = $request['norec_pd'];
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

    public function getHasilRadiologi(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::table('hasilradiologi_t as ar')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'ar.pegawaifk')
            ->select('ar.*','pg.namalengkap')
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

    public function getLaporanTindakanRadiologi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $paramKlp = '';
        $paramDk = '';
        $idDokter = '';
        $dokid = '';
        $ruid = '';

//        if(isset($request['dokid']) && $request['dokid']!="" && $request['dokid']!="undefined"){
//            $idDokter = ' and pg.id = '.$request['dokid'];
//        }

        if(isset($request['idDokter']) && $request['idDokter']!="" && $request['idDokter']!="undefined"){
            $dokid = ' and pg.id = '.$request['idDokter'];
        }

//        if(isset($request['kpid']) && $request['kpid']!="" && $request['kpid']!="undefined"){
//            $kpid = ' and pd.objectkelompokpasienlastfk='.$request['kpid'];
//        }

        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $ruid = ' and rg.id = '.$request['ruid'];
        }

        if (isset($request['KpArr']) && $request['KpArr']!="" && $request['KpArr']!="undefined"){
            $arrayKelompokPasien = explode(',',$request['KpArr']) ;
            $ids = [];
            $str = '';
            $d=0;
            foreach ( $arrayKelompokPasien as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramKlp = " AND klp.id IN ($str)";
        }

        if (isset($request['dkArr']) && $request['dkArr']!="" && $request['dkArr']!="undefined"){
            $arrayDokter = explode(',',$request['dkArr']) ;
            $ids = [];
            $str = '';
            $d=0;
            foreach ( $arrayDokter as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramDk = " AND pg1.id IN ($str)";
        }

        $data = DB::select(DB::raw("          
                SELECT pp.norec,pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,
                       CASE WHEN ru1.namaruangan IS NOT NULL THEN ru1.namaruangan ELSE ru2.namaruangan END AS ruangan,
                       klp.kelompokpasien,pro.namaproduk,pp.jumlah,pp.hargajual,
                       CASE WHEN pg.namalengkap IS NULL THEN '' ELSE pg.namalengkap END AS dokterdpjp,
			           CASE WHEN pg1.namalengkap IS NULL THEN '' ELSE pg1.namalengkap END AS dokter
                FROM pasiendaftar_t AS pd
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN pelayananpasienpetugas_t AS ppp ON ppp.pelayananpasien = pp.norec AND ppp.objectjenispetugaspefk = 4
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                INNER JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
                INNER JOIN ruangan_m AS rg ON rg.id = pd.objectruanganlastfk
                LEFT JOIN kelompokpasien_m AS klp ON klp.id = pd.objectkelompokpasienlastfk
                LEFT JOIN produk_m AS pro ON pro.id = pp.produkfk
                LEFT JOIN strukorder_t AS so ON so.norec = apd.objectstrukorderfk
                LEFT JOIN ruangan_m AS ru1 ON ru1.id = so.objectruanganfk
                LEFT JOIN ruangan_m AS ru2 ON ru2.id = apd.objectruanganasalfk
                LEFT JOIN batalregistrasi_t AS br ON br.pasiendaftarfk = pd.norec
                LEFT JOIN pegawai_m AS pg ON pg.id = apd.objectpegawaifk
                INNER JOIN pegawai_m AS pg1 ON pg1.id = ppp.objectpegawaifk
                WHERE pd.kdprofile = $kdProfile AND pd.statusenabled = true AND apd.objectruanganfk = 576
			          AND pro.namaproduk IS NOT NULL AND br.norec IS NULL
			          AND pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
			          $ruid
			          $dokid
			          $paramKlp
			          $paramDk
        "));

//        $dataPetugas = DB::select(DB::raw("
//            SELECTs pp.norec,CASE WHEN pg.namalengkap IS NULL THEN '' ELSE pg.namalengkap END AS dokter
//            FROM pasiendaftar_t AS pd
//            INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
//            INNER JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
//            INNER JOIN pelayananpasienpetugas_t AS ppp ON ppp.pelayananpasien = pp.norec
//            INNER JOIN pegawai_m AS pg ON pg.id = ppp.objectpegawaifk
//            LEFT JOIN kelompokpasien_m AS klp ON klp.id = pd.objectkelompokpasienlastfk
//            WHERE pd.kdprofile = $kdProfile AND pd.statusenabled = true AND apd.objectruanganfk = 576
//            AND ppp.objectjenispetugaspefk = 4
//            AND pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
//            $ruid
//            $paramKlp
//            $paramDk
//        "));
//
//        $i=0;
//        foreach ($data as $items){
//            foreach ($dataPetugas as $itemPetugas) {
//                if ($data[$i]->noregistrasi == $itemPetugas->norec){
//                    $data[$i]->dokter = $itemPetugas->dokter;
//                }
//            }
//            $i = $i + 1;
//        }

        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    
     /**
	 * @SWG\Get(
	 *   path="/service/medifirst2000/radiologi/get-norec-hasil-radiologi",
	 *   summary="Get NoRec Hasil Radiologi",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *   @SWG\Parameter(
     *       in ="query",
     *       name="norec_pp",
     *       required=true,
     *       type="string"
     *    ),
     *   @SWG\Parameter(
     *       in ="query",
     *       name="noregistrasifk",
     *       required=true,
     *       type="string"
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function getNoRecRadiologi(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        try {
            $data = DB::table('hasilradiologi_t')
            ->select('hasilradiologi_t.norec')
            // ->where('ar.kdprofile', '=', 0)
            ->where('hasilradiologi_t.statusenabled',true)
            ->where('hasilradiologi_t.pelayananpasienfk', '=', $request['norec_pp'])
            ->get();
            
            if ($data == []) {
                $HasilRadiologi = new HasilRadiologi();
                $HasilRadiologi->norec = $HasilRadiologi->generateNewId();
                $HasilRadiologi->kdprofile = $idProfile;
                $HasilRadiologi->statusenabled = true;
                $HasilRadiologi->pelayananpasienfk = $request['norec_pp'];
                $HasilRadiologi->noregistrasifk = $request['noregistrasifk'];
                $HasilRadiologi->save();

                if ($HasilRadiologi) {
                    $result = array(
                        "code" => 200,
                        "status" => true,
                        "message" => "succes",
                        "norec" => $HasilRadiologi->norec
                    );
                }
            }
            else {
                $result = array(
                    "code" => 200,
                    "status" => true,
                    "message" => "succes",
                    "norec" => $data[0]->norec
                );
            }
            return response()->json( $result, $result[ 'code'] );
        }
        catch (Exception $e) {
            return $e;
        }
    }

     /**
	 * @SWG\Get(
	 *   path="/service/medifirst2000/radiologi/get-ekspertise",
	 *   summary="Get Hasil Radiologi",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *   @SWG\Parameter(
     *       in ="query",
     *       name="norec_pp",
     *       required=true,
     *       type="string"
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function getEkspertise(Request $request) {
        $transStatus = false;
        try {
            $data = DB::table('hasilradiologi_t as hr')
                ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'hr.pegawaifk')
                ->select('hr.*','pg.namalengkap')
                // ->where('ar.kdprofile', '=', 0)
                ->where('hr.statusenabled',true)
                ->where('hr.pelayananpasienfk', '=', $request['norec_pp'])
                ->first();
            
            if (!empty($data->norec)) {
                $list = DB::table('hasilradiologilistgambar_t as list')
                    ->select('list.*')
                    ->where('list.noregistrasifk', '=', $data->noregistrasifk)
                    ->get();
                $data->list = $list;
                $transStatus = true;
            }
            else {
                $transStatus = false;
            }
                
            if ($data == []) {
                $result = array(
                    "code" => 500,
                    "status" => false,
                    "message" => "Gagal, maaf data tidak ditemukan"
                );
            }
            if ($transStatus) {
                $result = array(
                    "code" => 200,
                    "status" => true,
                    "message" => "succes",
                    "data" => $data
                );
            }
            else {
                $result = array(
                    "code" => 500,
                    "status" => false,
                    "message" => "Gagal, maaf data tidak ditemukan",
                );
            }
        }
        catch (Exception $e) {
            return $e;
        }
        return response()->json( $result, $result[ 'code'] );
    }

     /**
	 * @SWG\Post(
	 *   path="/service/medifirst2000/radiologi/save-ekspertise",
	 *   summary="Save Hasil Radiologi",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *    @SWG\Parameter(
     *       in = "body",
     *       required=true,
     *       name="Data",
     *       @SWG\Schema(
     *         @SWG\Property(property="norec_pp", type="string"),
     *         @SWG\Property(property="tanggal", type="string"),
     *         @SWG\Property(property="pegawaifk", type="string"),
     *         @SWG\Property(property="keterangan", type="string"),
     *         @SWG\Property(property="norec_gambar", type="string"),
     *       ),
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function saveEkspertise(Request $request) {
        DB::beginTransaction();
        $transStatus = false;
        try {
            // $data = HasilRadiologi::where('pelayananpasienfk', $request['norec_pp'])
            //         ->update([
            //             'tanggal' => $request['tanggal'],
            //             'pegawaifk' => $request['pegawaifk']
            //         ]);

            $list = HasilRadiologiListGambar::where('norec', $request['norec_gambar'])->update(['keterangan' => $request['keterangan']]);
            
            if ($list) {
                $transStatus = true;
            }
        } 
        catch (Exception $e) {
            return $e;
        }

        if (!$transStatus) {
            DB::rollBack();
            $result = array(
                "code" => 500,
                "status" => false,
                "messages" => "Gagal Simpan!"
            );
        }
        else {
            DB::commit();
            $result = array(
                "code" => 200,
                "status" => true,
                "messages" => "Berhasil Simpan!"
            );
        }
        return response()->json( $result, $result['code'] );
    }
    
    
     /**
	 * @SWG\Post(
	 *   path="/service/medifirst2000/radiologi/save-ekspertise-pacs",
	 *   summary="Save List Hasil Radiologi",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *    @SWG\Parameter(
     *       in = "body",
     *       required=true,
     *       name="Data",
     *       @SWG\Schema(
     *         @SWG\Property(property="norec", type="string"),
     *         @SWG\Property(property="keterangan", type="string")
     *       ),
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function saveEkspertisePACS(Request $request) {
        DB::beginTransaction();
        try {
            $data = HasilRadiologiListGambar::where('norec', $request->norec)->update(['keterangan' => $request->keterangan]);

            if ($data) {
                $transStatus = true;
            }
            else {
                $transStatus = false;
            }
        } 
        catch (\Exception $e) {
            return $e;
        }

        if ($transStatus) {
            DB::commit();
            $result = array(
                "code" => 200,
                "status" => true,
                "message" => "success",
                "keterangan" => $request->keterangan
            );
        }
        else {
            DB::rollBack();
            $result = array(
                "code" => 500,
                "status" => false,
                "message" => "Simpan Gagal",
            );
        }
        return response()->json( $result, $result['code'] );
    }

     /**
	 * @SWG\Post(
	 *   path="/service/medifirst2000/radiologi/save-dicom",
	 *   summary="Save Hasil Dicom Web Viewer",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *    @SWG\Parameter(
     *       in = "body",
     *       required=true,
     *       name="Data",
     *       @SWG\Schema(
     *         @SWG\Property(property="norec", type="string"),
     *         @SWG\Property(property="filename", type="string"),
     *         @SWG\Property(property="pacsfile", type="string")
     *       ),
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function saveDicomView(Request $request) {
	$msg = '';
        DB::beginTransaction();
        try {
            $transStatus = false;
                           
            $image = $request->pacsfile;  // base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $filename = $request->filename . '.jpg';
            Storage::disk('pacs')->put($filename, base64_decode($image));
            Storage::disk('sftp')->put($filename, base64_decode($image));

            if (!empty($request->norec)) {
                // $ekspertise = HasilRadiologiListGambar::where('norec', $request->norec)->update(['nofoto' => $filename]);
                $data = new HasilRadiologiListGambar();
                $data->norec = $data->generateNewId();
                $data->tanggal = date('d-m-Y');
                $data->filename = $filename;
                $data->keterangan = $request->keterangan;
                $data->pelayananpasienfk = $request->norec;
                $data->save();
            }
             
            if ($data) {
                $transStatus = true;
            }
        } 
        catch (\Exception $e) {
	          $msg = $e->getMessage();
            $transStatus = false;
        }

        if ($transStatus) {
            DB::commit();
            $result = array(
                "code" => 201,
                "status" => true,
                "message" => "success",
                "filename" => $filename,
                "filepath" => 'service/medifirst2000/radiologi/images/pacs/'
            );
        }
        else {
            DB::rollback();
            $result = array(
                "code" => 500,
                "status" => false,
                "message" => "failed ".$msg
            );
        }
        return response()->json( $result, $result[ 'code'] );
    }
    
     /**
	 * @SWG\Get(
	 *   path="/service/medifirst2000/radiologi/get-list-hasil-radiologi",
	 *   summary="Get List Hasil Radiologi",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *   @SWG\Parameter(
     *       in ="query",
     *       name="norec",
     *       required=true,
     *       type="string"
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function getListHasilRadiologi(Request $request) {
        try {
            $data = DB::table('hasilradiologilistgambar_t as list')
                    ->select('list.*')
                    ->where('list.norec_hasilradiologi', '=', $request['norec'])
                    ->get();
            
            if ($data == []) {
                $result = array(
                    "code" => 500,
                    "status" => false,
                    "message" => "Gagal, maaf data tidak ditemukan"
                );
            }
            else {
                $result = array(
                    "code" => 200,
                    "status" => true,
                    "message" => "succes",
                    "data" => $data
                );
            }
        }
        catch (Exception $e) {
            return $e;
        }
        return response()->json( $result, $result[ 'code'] );
    }
    
     /**
	 * @SWG\Get(
	 *   path="/service/medifirst2000/radiologi/delete-list-hasil-radiologi",
	 *   summary="Delete List Hasil Radiologi",
	 *   tags={"Radiologi"},
     *   @SWG\Parameter(
     *       in ="header",
     *       name="X-AUTH-TOKEN",
     *       required=true,
     *       type="string"
     *    ),
     *   @SWG\Parameter(
     *       in ="query",
     *       name="norec",
     *       required=true,
     *       type="string"
     *    ),
	 *   @SWG\Response(
	 *     response="200",
	 *     description="success"
	 *   ),
	 *   @SWG\Response(
	 *     response="500",
	 *     description="error"
	 *   )
	 * )
	 */
    public function deleteListHasilRadiologi(Request $request) {
        try {
            $delete = DB::table('hasilradiologilistgambar_t as list')
                    ->where('list.norec', '=', $request['norec'])
                    ->delete();
            
            if ($delete) {
                $result = array(
                    "code" => 200,
                    "status" => true,
                    "message" => "successfully deleted",
                );
            }
            else {
                $result = array(
                    "code" => 500,
                    "status" => false,
                    "message" => "failed"
                );
            }
        }
        catch (Exception $e) {
            return $e;
        }
        return response()->json( $result, $result[ 'code'] );
    }
    public function getDetailVerifLabRad(Request $r){
        $kdProfile = $this->getDataKdProfile($r);
        $data= DB::select(DB::raw("select 
            pp.tglpelayanan,pp.jumlah,pp.hargasatuan,(pp.jumlah*pp.hargasatuan) as total,
            prd.namaproduk
            from pelayananpasien_t as pp
            join produk_m as prd on prd.id=pp.produkfk
            where pp.strukorderfk='$r[norec_so]'
            and pp.kdprofile=$kdProfile"));
        $res = array(
            "data" => $data,
            "as" => 'er@epic'
        );
        return $this->respond($res);

    }

    public function getPelayananRad(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();

        $data2 = DB::table('hasilradiologi_t as hr')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'hr.pegawaifk')
            ->select('hr.*','pg.namalengkap')
            // ->where('ar.kdprofile', '=', 0)
            ->where('hr.statusenabled',true)
            ->where('hr.pelayananpasienfk', '=', $request['norec'])
            ->first();
        
            
        $res = array(
            "data" => $data2,
            "as" => 'er@epic'
        );
        return $this->respond($res);
    }

    public function getRiwayatTindakanRadiologi(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin=$request->all();
        $pelayanan = \DB::table('pelayananpasien_t as pp')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->JOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftJOIN('strukorder_t as so', 'so.norec', '=', 'pp.strukorderfk')
            ->select('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                'dp.namadepartemen', 'ps.id as psid', 'apd.norec as norec_apd', 'sp.norec as norec_sp', 'pp.norec as norec_pp',
                'ru.objectdepartemenfk', 'so.noorder', 'apd.objectruanganfk','pp.iscito','pp.jasa',
                'ps.objectjeniskelaminfk','ps.tgllahir',
                DB::raw("pd.tglregistrasi,to_char(pp.tglpelayanan,'DD-MM-YYYY') as tgllayanan,to_char(pp.tglpelayanan,'HH:mm') as jamlayanan")
            )
            ->where('pp.kdprofile',$idProfile)
            ->where('ru.objectdepartemenfk', $this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile))
            ->groupBy('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                'dp.namadepartemen', 'ps.id', 'apd.norec', 'sp.norec', 'pp.norec',
                'ru.objectdepartemenfk', 'so.noorder','apd.objectruanganfk','pp.iscito','pp.jasa','pd.tglregistrasi')

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
                    ->where('pd.kdprofile',$idProfile)
                    ->where('ptu.objectjenispetugaspefk', 4)
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
                        'objectjeniskelaminfk' => $item->objectjeniskelaminfk,
                        'tgllahir' => $item->tgllahir,
//                        'nosbm' => $item->nosbm,
//                        'hr_norec' => $item->hr_norec,
                        'tglregistrasi' => $item->tglregistrasi,
                        'tgllayanan' => $item->tgllayanan,
                        'jamlayanan' => $item->jamlayanan,
                    );
                }
            } else {
                $result = [];
            }

        $dataTea =array(
            'data' => $result,
            'message' => 'Inhuman'
        );
        return $this->respond($dataTea);
    }

}
