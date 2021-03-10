<?php
/**
 * Created by PhpStorm.
 * User: Efan Andrian(ea@epic)
 * Date: 02-Sept-19
 * Time: 08:49
 */

namespace App\Http\Controllers\Kasir;
use App\Http\Controllers\ApiController;
use App\Master\JadwalPraktekBulanan;
use App\Master\Pasien;
use App\Master\SettingDataFixed;
use App\Traits\InternalList;
use App\Traits\PelayananPasienTrait;
use App\Traits\SettingDataFixedTrait;
use App\Traits\Valet;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\LogAcc;
use App\Transaksi\LoggingUser;
use App\Transaksi\MonitoringPengajuanPelatihanDetail;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\RegistrasiPelayananPasien;
use App\Transaksi\StrukBuktiPenerimaan;
use App\Transaksi\StrukBuktiPenerimaanCaraBayar;
use App\Transaksi\StrukOrder;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;
use App\Transaksi\StrukPelayananPenjamin;
use App\Transaksi\StrukPlanning;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Response;
use PDF;
use App\Traits\BniTrait;
use App\Transaksi\BNITransaction;
use App\Transaksi\BniEnc;
use App\Transaksi\VirtualAccount;

class KasirController extends ApiController{
    use Valet, InternalList, PelayananPasienTrait, SettingDataFixedTrait,BniTrait;
    // protected $request;
    protected $client_id = '13513';
    protected $secret_key = 'fda741b2e353fce9856fb0a4674095b1';
    protected $prefix = '98813513';
    protected $url = 'https://apibeta.bni-ecollection.com/';

    public function __construct()
    {
        parent::__construct($skip_authentication=false);

    }

    protected function  getKdTransaksiNonLayanan(){
        $set = SettingDataFixed::where('namafield', 'kdTransaksiNonLayanan')->first();
        if($set){
            return $set->nilaifield;
        }else{
            return null;
        }
    }

    public function getDataComboKasir(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin=$request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('pg.namalengkap','lu.objectpegawaifk','lu.objectkelompokuserfk')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $kdProfile)
            ->whereIn('dp.id',[3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35])
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $kdProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $kelTrans = \DB::table('kelompoktransaksi_m')
            ->where('kdprofile', $kdProfile)
            ->select('id','kelompoktransaksi')
            ->where('statusenabled',true)
            ->get();
        $caraBayar = \DB::table('carabayar_m')
            ->where('kdprofile', $kdProfile)
            ->select('id','carabayar as caraBayar','carabayar as namaExternal')
            ->where('statusenabled',true)
            ->get();
        $jenisKartu = \DB::table('jeniskartu_m')
            ->select('id','jeniskartu as jenisKartu','jeniskartu as namaExternal')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->get();
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.kdprofile', $kdProfile)
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();
        $dataRuanganRi = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk','ru.kdinternal')
            ->where('ru.kdprofile', $kdProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectdepartemenfk', 16)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataKasir= \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s lu
                INNER JOIN pegawai_m pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $kdProfile and objectkelompokuserfk=:id and pg.statusenabled=true"),
            array(
                'id' => 20,
            )
        );
        $dataCB= DB::select(DB::raw("select id,carabayar from carabayar_m
                where kdprofile = $kdProfile and statusenabled=:tt;"),
            array(
                'tt' => 1,
            )
        );
        $dataKP= DB::select(DB::raw("select id,kelompoktransaksi from kelompoktransaksi_m
                where kdprofile = $kdProfile and statusenabled=:tt and id in (2,3,4,5,6,10,13,16,34,8,9,20,21,23,24,26,62,70,71);"),
            array(
                'tt' => 1,
            )
        );
        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile', $kdProfile)
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

        $diklit= $this->settingDataFixed('KdKelompokUserDiklat',$kdProfile);
        $list = explode(',',$this->settingDataFixed('kdTransaksiNonLayanan', $kdProfile));
        $arry=[];
        foreach ($list as $item){
            $arry [] = (int)$item;
        }
        $kelTransNon = \DB::table('kelompoktransaksi_m')
            ->select('id','kelompoktransaksi')
            ->where('statusenabled',1)
            ->whereIn('id',$arry)
            ->get();

        $result = array(
            'ruangan' => $dataRuangan,
            'kelompoktransaksi' => $kelTrans,
            'carabayar' => $caraBayar,
            'jeniskartu' => $jenisKartu,
            'datapegawai'=>$dataPegawai,
            'kelompokpasien'=>$dataKelompok,
            'ruanganri'=>$dataRuanganRi,
            'datakasir'=>$dataKasir,
            'dataInstalasi' =>$dataInstalasi,
            'dataCB' =>$dataCB,
            'dataKP' =>$dataKP,
            'datalogin'=>$dataLogin,
            'dokter' => $dataDokter,
            'departemen' => $dataDepartemen,
            'diklit' => $diklit,
            'kelnon' => $kelTransNon,
            'message' => 'ihuman@epic'
        );
        return $this->respond($result);
    }

    public function getComboRuanganRanapRajal(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataRuanganRanap = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $kdProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectdepartemenfk', 16)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataRuanganRajal = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $kdProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectdepartemenfk', 18)
            ->orderBy('ru.namaruangan')
            ->get();
        $result = array(
            'ruangan_ranap' => $dataRuanganRanap,
            'ruangan_rajal' => $dataRuanganRajal,
            'message' => 'ihuman@epic'
        );
        return $this->respond($result);
    }

    public function daftarTagihanNonLayanan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $filter=$request->all();
        $list = explode (',',$this->settingDataFixed('kdTransaksiNonLayanan', $kdProfile));
        $KdList = [];
        foreach ($list as $item){
            $KdList []=  (int)$item;
        }
        $datakelompokuser= \DB::table('loginuser_s as lu')
            ->select('lu.objectkelompokuserfk')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id','=',$filter['userData']['id'])
            ->get();

        if ($datakelompokuser[0]->objectkelompokuserfk == 58){
            $dataNonLayanan = \DB::table('strukpelayanan_t as sp')
                ->join('kelompoktransaksi_m as kt', 'sp.objectkelompoktransaksifk', '=', 'kt.id')
                ->select('sp.norec', 'sp.tglstruk', 'sp.namapasien_klien', 'kt.reportdisplay as jenistagihan',
                     'keteranganlainnya', 'sp.nosbklastfk', 'sp.nosbmlastfk', 'kt.id as jenisTagihanId',
                    \DB::raw("CAST(sp.totalharusdibayar) AS totalharusdibayar AS FLOAT"))
                ->where('sp.kdprofile', $kdProfile)
                ->whereNotNull('sp.totalharusdibayar')
                ->whereIn('sp.objectkelompoktransaksifk', $KdList)
                ->where('kt.id','=',13);
        }else {
            $dataNonLayanan = \DB::table('strukpelayanan_t as sp')
                ->join('kelompoktransaksi_m as kt', 'sp.objectkelompoktransaksifk', '=', 'kt.id')
                ->select('sp.norec', 'sp.tglstruk', 'sp.namapasien_klien', 'kt.reportdisplay as jenistagihan',
                    'keteranganlainnya', 'sp.nosbklastfk', 'sp.nosbmlastfk', 'kt.id as jenisTagihanId',
                    \DB::raw("CAST(sp.totalharusdibayar AS FLOAT) AS totalharusdibayar"))
//            ->whereNull('sp.noregistrasifk')
                ->where('sp.kdprofile', $kdProfile)
                ->whereNotNull('sp.totalharusdibayar')
                ->whereIn('sp.objectkelompoktransaksifk',$KdList);
//            ->where('spp.kdrekananpenjamin', 0);->whereIn('id', [1, 2, 3])
        }

        if(isset($filter['tglAwal']) && $filter['tglAwal']!=""){
            $dataNonLayanan = $dataNonLayanan->where('sp.tglstruk','>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!=""){
            $tgl= $filter['tglAkhir'];//." 23:59:59";
            $dataNonLayanan = $dataNonLayanan->where('sp.tglstruk','<=', $tgl);
        }
//
        if(isset($filter['jenisTagihanId']) && $filter['jenisTagihanId']!=""){
            $dataNonLayanan = $dataNonLayanan->where('kt.id','=', $filter['jenisTagihanId']);
        }

        if(isset($filter['namaPelanggan']) && $filter['namaPelanggan']!=""){
            $dataNonLayanan = $dataNonLayanan->where('sp.namapasien_klien','ilike','%'. $filter['namaPelanggan'] . '%');
        }
//
        if(isset($filter['status']) && $filter['status']!=""){
            if($filter['status']=='Lunas'){
                $dataNonLayanan = $dataNonLayanan->whereNotNull('sp.nosbmlastfk');
            }else{
                $dataNonLayanan = $dataNonLayanan->whereNull('sp.nosbmlastfk');
            }
        }
        if(isset($filter['statusK']) && $filter['statusK']!=""){
            if($filter['statusK']=='Lunas'){
                $dataNonLayanan = $dataNonLayanan->whereNotNull('sp.nosbklastfk');
            }else{
                $dataNonLayanan = $dataNonLayanan->whereNull('sp.nosbklastfk');
            }
        }

//        $dataPiutang=$dataPiutang->groupBy('kp.kelompokpasien', 'spp.norec','sp.tglstruk', 'pd.noregistrasi', 'pd.tglregistrasi','p.nocm','p.namapasien','spp.totalppenjamin','spp.totalharusdibayar',
//            'spp.totalsudahdibayar', 'r.namarekanan', 'spp.totalbiaya', 'spp.noverifikasi');
        $dataNonLayanan =$dataNonLayanan->where('sp.statusenabled','=',true);
        $dataNonLayanan =$dataNonLayanan->get();
        $result = array();
        foreach ($dataNonLayanan as $item) {
            $statusBayar = "Belum Bayar";
            if($item->nosbmlastfk!=null ||$item->nosbklastfk!=null){
                $statusBayar = "Lunas";
            }
            $result[] = array(
                'noRec' => $item->norec,
                'tglTransaksi' => $item->tglstruk,
                'namaPelanggan' => $item->namapasien_klien,
                'jenisTagihan' => $item->jenistagihan,
                'total' => $item->totalharusdibayar,
                'keterangan' => $item->keteranganlainnya,
                'jenisTagihanId' => $item->jenisTagihanId,
                'statusBayar' =>$statusBayar

            );
        }

        return $this->respond($result, 'Data Tagihan Penunjang');

//        $strukOrder = StrukOrder::has('order_pelayanan')->where('noregistrasifk', null)->get();
//        $result = array();
//        foreach ($strukOrder as $item) {
//            $orderPelayanan = $item->order_pelayanan;
//            $totalTagihan = ($item->totalhargasatuan*$item->qtyproduk);
//            if($totalTagihan>0){
//                $result[] = array(
//                    'noRec' => $item->norec,
//                    'tglTransaksi' => $item->tglorder,
//                    'namaPelanggan' => $item->namapenyewa,
//                    'jenisTagihan' => 'x', //dari mana kelompokTransaksi
//                    'total' => $totalTagihan,
//                    'keterangan' => $item->keteranganorder,
//                    'statusBayar' =>"-", //belum bayar / lunas
//                );
//            }
//        }
//        return $this->respond($result, 'Data tagihan non layanan');
    }

    public function getDataProduk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('produk_m as pr')
            ->JOIN('harganettoprodukbykelas_m as het','het.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','het.harganetto1 as harga')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->where('het.objectkelasfk',6)
            ->where('het.objectjenispelayananfk',1)
            ->where('het.statusenabled',true);
//            ->take($request['take']);

        if(isset($request['prid']) && $request['prid']!="" && $request['prid']!="undefined"){
            $data = $data->where('ru.id', $request['prid']);
        }
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike','%'. $request['namaproduk'].'%');
        }
        $data = $data->get();



        $result = array(
            'data' => $data,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function SaveInputTagihan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        \DB::beginTransaction();
        $transMessage='';
        $req = $request->all();
//        return $this->respond($req);
        try {
        if($req['norec'] == ''){
            $SP = new StrukPelayanan();
            $norecSP = $SP->generateNewId();
            $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 10, 'NL'.$this->getDateTime()->format('ym'), $kdProfile);
            $SP->norec = $norecSP;
            $SP->kdprofile = $kdProfile;
            $SP->statusenabled = true;
            $SP->nostruk = $noStruk;
        }else{
            $SP = StrukPelayanan::where('norec', $req['norec'])->where('kprofile', $kdProfile)->first();
            $DelSPD = StrukPelayananDetail::where('nostrukfk', $req['norec'])->where('kdprofile', $kdProfile)->delete();
        }
            $SP->objectkelompoktransaksifk = $req['kelompoktransaksifk'];
            $SP->keteranganlainnya = $req['keteranganlainnya'];
            $SP->namapasien_klien = $req['namapasien_klien'];
            if (isset($req['notelp_klien'])){
                $SP->noteleponfaks = $req['notelp_klien'];
            }
            $SP->tglstruk = $req['tglstruk'];
            $SP->totalharusdibayar = $req['totalharusdibayar'];
            $SP->save();

        foreach ($req['details'] as $item) {
            $SPD = new StrukPelayananDetail();
            $norecKS = $SPD->generateNewId();
            $SPD->norec = $norecKS;
            $SPD->kdprofile = $kdProfile;
            $SPD->statusenabled = true;
            $SPD->nostrukfk = $SP->norec;
            $SPD->objectprodukfk = $item['id'];
            $SPD->hargadiscount = 0;
            $SPD->hargadiscountgive = 0;
            $SPD->hargadiscountsave = 0;
            $SPD->harganetto = $item['harga'];
            $SPD->hargapph = 0;
            $SPD->hargappn = 0;
            $SPD->hargasatuan = $item['harga'];
            $SPD->hargasatuandijamin = 0;
            $SPD->hargasatuanppenjamin = 0;
            $SPD->hargasatuanpprofile = 0;
            $SPD->hargatambahan = 0;
            $SPD->isonsiteservice = 0;
            $SPD->kdpenjaminpasien = 0;
            $SPD->persendiscount = 0;
            $SPD->qtyproduk = $item['jumlah'];
            $SPD->qtyoranglast = $item['qtyoranglast'];
            $SPD->qtyprodukoutext = 0;
            $SPD->qtyprodukoutint = 0;
            $SPD->qtyprodukretur = 0;
            $SPD->satuan = 0;
            $SPD->tglpelayanan = $req['tglstruk'];
            $SPD->is_terbayar = 0;
            $SPD->linetotal = 0;
            $SPD->keteranganlainnya = $item['keterangan'];
            $SPD->save();
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
                "data" => $SP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $SP,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function detailTagihanNonLayanan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $noRec = $request['noRec'];
        $strukPelayanan = StrukPelayanan::where('norec', $noRec)->where('kdprofile', $kdProfile)->first();
//        if($strukPelayanan){
//            //return notfound
//        }
        $strukPelayananDetail = $strukPelayanan->struk_pelayanan_detail;
        $totalBilling= 0;
        $totalKlaim= 0;
        $detailTagihan = array();
        foreach ($strukPelayananDetail as $value){
            $totalBilling = $totalBilling + ($value->harganetto*$value->qtyproduk);
            $totalKlaim = $totalKlaim + ($value->hargasatuanppenjamin*$value->qtyproduk);
            $detailTagihan[] = array(
                'namaLayanan'  => @$value->produk->namaproduk,
                'namaproduk' => @$value->produk->namaproduk,
                'id' => @$value->produk->id,
                'jumlah'  => $value->qtyproduk,
                'qtyoranglast' => $value->qtyoranglast,
                'harga'  => $value->hargasatuan,
                'jasa' => $value->hargatambahan,
                'total'  => $value->hargasatuan * $value->qtyproduk,
                'totalK'  => (($value->hargasatuan * $value->qtyoranglast) * $value->qtyproduk) + $value->hargatambahan,
                'keterangan' => $value->keteranganlainnya,
            );
        }
        $totalBayar = $totalBilling -$totalKlaim;
        $result =array(
            "tglTransaksi" => $strukPelayanan->tglstruk,
            "namaPasien_klien"  => $strukPelayanan->namapasien_klien,
            "jumlahBayar" => 0,
            "kdkelompokTransaksi" => $strukPelayanan->kelompok_transaksi->id,
            "kelompokTransaksi" => $strukPelayanan->kelompok_transaksi->reportdisplay,
            "notelepon" => $strukPelayanan->noteleponfaks,
            "keterangan" => $strukPelayanan->keteranganlainnya,
            "noRecStruk"  => $strukPelayanan->norec,
            "totalBilling" => $strukPelayanan->totalharusdibayar,
            "detailTagihan"  => $detailTagihan,

        );

        return $this->respond($result, "Detail Tagihan Pasien");
    }

    public function daftarTagihanPasien(Request $request){
        $result = array();
        $filter = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataStrukPelayanan= \DB::table('strukpelayanan_t as sp')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'sp.noregistrasifk')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as r', 'r.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'r.objectdepartemenfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('kelas_m as k', 'k.id', '=', 'pd.objectkelasfk')
            ->select('pd.noregistrasi', 'p.nocm','p.namapasien','pd.tglregistrasi','pd.tglpulang','r.namaruangan',
                'kp.kelompokpasien','sp.totalharusdibayar','sp.norec', 'sp.nostruk', 'k.namakelas', 'sp.tglstruk',
                'sp.totalprekanan', 'r.id as ruanganId', 'dept.id as departmentId')
            ->where('sp.kdprofile', $kdProfile);

        if(isset($filter['noReg']) && $filter['noReg']!="" && $filter['noReg']!="undefined"){
            $dataStrukPelayanan  = $dataStrukPelayanan->where('pd.noregistrasi', $filter['noReg']);
        }

        if(isset($filter['noRm']) && $filter['noRm']!="" && $filter['noRm']!="undefined"){
            $dataStrukPelayanan  = $dataStrukPelayanan->where('p.nocm','ilike','%'. $filter['noRm'].'%');
        }
//
//        if(isset($filter['tglAwal']) && $filter['tglAwal']!="" && $filter['tglAwal']!="undefined"){
//            $dataStrukPelayanan = $dataStrukPelayanan->where('pd.tglregistrasi','>=', $filter['tglAwal']);
//        }
        if(isset($filter['tglAwal']) && $filter['tglAwal']!="" && $filter['tglAwal']!="undefined"){
            $dataStrukPelayanan = $dataStrukPelayanan->where('sp.tglstruk','>=', $filter['tglAwal']);
        }

//        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!="" && $filter['tglAkhir']!="undefined"){
//            $tgl= $filter['tglAkhir']." 23:59:59";
//            $dataStrukPelayanan = $dataStrukPelayanan->where('pd.tglregistrasi','<=', $tgl);
//        }
        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!="" && $filter['tglAkhir']!="undefined"){
            $tgl= $filter['tglAkhir'];//." 23:59:59";
            $dataStrukPelayanan = $dataStrukPelayanan->where('sp.tglstruk','<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId']!="" && $filter['instalasiId']!="undefined"){
            $dataStrukPelayanan = $dataStrukPelayanan->where('dept.id','=', $filter['instalasiId']);
        }

        if(isset($filter['ruanganId']) && $filter['ruanganId']!="" && $filter['ruanganId']!="undefined"){
            $dataStrukPelayanan = $dataStrukPelayanan->where('r.id','=', $filter['ruanganId']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien']!="" && $filter['namaPasien']!="undefined"){
            $dataStrukPelayanan = $dataStrukPelayanan->where('p.namapasien','ilike', '%'.$filter['namaPasien'].'%');
        }

        if(isset($filter['kelompokPasienId']) && $filter['kelompokPasienId']!="" && $filter['kelompokPasienId']!="undefined"){
            $dataStrukPelayanan = $dataStrukPelayanan->where('pd.objectkelompokpasienlastfk','=', $filter['kelompokPasienId']);
        }

//        if($filter['tglAwal']!=""){
//            $dataStrukPelayanan = $dataStrukPelayanan->where('tglregistrasi','>=', $filter['tglAwal']);
//        }
//
//        if($filter['tglAkhir']!=""){
//            $tgl= $filter['tglAkhir']." 23:59:59";
//            $dataStrukPelayanan = $dataStrukPelayanan->where('tglregistrasi','<=', $tgl);
//        }
        if(isset($filter['status']) && $filter['status']!=""){
            if($filter['status']=='Lunas'){
                $dataStrukPelayanan  = $dataStrukPelayanan->whereNotNull('sp.nosbmlastfk');
            }else{
                $dataStrukPelayanan  = $dataStrukPelayanan->whereNull('sp.nosbmlastfk');
            }
        }
        $dataStrukPelayanan = $dataStrukPelayanan->take(50);
        $dataStrukPelayanan = $dataStrukPelayanan->whereRaw('(sp.statusenabled is null or sp.statusenabled =true)');
        $dataStrukPelayanan  = $dataStrukPelayanan->whereNotNull('sp.totalharusdibayar');
        $dataStrukPelayanan  = $dataStrukPelayanan->where('sp.totalharusdibayar','<>',0);
        $dataStrukPelayanan  = $dataStrukPelayanan->whereNotNull('sp.noregistrasifk');
        if(!empty($filter['tglAwal']) && !empty($filter['tglAkhir']) && empty($filter['noReg']) && empty($filter['noRm']) && empty($filter['status']) && empty($filter['ruanganId']) && empty($filter['namaPasien']) && empty($filter['instalasiId'])){
            $dataStrukPelayanan = $dataStrukPelayanan->get();
        }else if (empty($filter['tglAwal']) && empty($filter['tglAkhir']) && empty($filter['noReg']) && empty($filter['noRm']) && empty($filter['status']) && empty($filter['ruanganId']) && empty($filter['namaPasien']) && empty($filter['instalasiId'])) {
            $dataStrukPelayanan = $dataStrukPelayanan->limit(10)->get();
        }else if(!empty($filter['tglAwal']) && !empty($filter['tglAkhir']) && empty($filter['noReg']) && empty($filter['noRm']) && empty($filter['status'])&& empty( $filter['ruanganId']) && empty($filter['namaPasien']) && $filter['instalasiId'] == "undefined"){
            $dataStrukPelayanan = $dataStrukPelayanan->limit(10)->get();
        } else{
            $dataStrukPelayanan    = $dataStrukPelayanan->get();
        }

        foreach ($dataStrukPelayanan as $key => $item){
            $sp=StrukPelayanan::find($item->norec);
            $result[] = array(
                'noRec' => $item->norec,
                'tglStruk' => $item->tglstruk,
                'tglMasuk' => $item->tglregistrasi,
                'tglPulang' => $item->tglpulang,
                'noRegistrasi' => $item->noregistrasi,
                'namaPasien' => $item->namapasien,
                'noCm' => $item->nocm,
                'kelasRawat' => $item->namakelas,
                'lastRuangan' => $item->namaruangan,
                'jenisPasien' => $item->kelompokpasien,
                'kelasPenjamin' => "-", //ambilnya dari mana ?
                'totalBilling' => $item->totalharusdibayar,
                'totalKlaim' => $item->totalprekanan ,
                'totalBayar' => $item->totalharusdibayar,
                'statusBayar' => $sp->statusBayar,
                'ruanganId' => $item->ruanganId,
                'departmentId' => $item->departmentId,
            );
        }
        return $this->respond($result, 'Data Daftar Pasien');
    }

    public function detailTagihanPasien(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $noRecStrukPelayanan = $request['noRecStrukPelayanan'];
        $strukPelayanan = StrukPelayanan::where('norec', $noRecStrukPelayanan)->where('kdprofile', $kdProfile)->first();
        if($strukPelayanan){
            //return notfound
        }
        $pelayanan_pasien = $strukPelayanan->pelayanan_pasien;
        $deposit= 0;
        $detailTagihan = array();

        foreach ($pelayanan_pasien as $value){
            $harga = ($value->hargajual==null) ? 0 : $value->hargajual;
            $diskon = ($value->hargadiscount==null) ? 0 : $value->hargadiscount;
            if($value->nilainormal== -1){
                $deposit += $harga;
            }else{
                $detailTagihan[] = array(
                    'namaLayanan'  => $value->produk->namaproduk,
                    "ruangan" => @$value->antrian_pasien_diperiksa->ruangan->reportdisplay,
                    'jumlah'  => $value->jumlah,
                    'harga'  => $harga,
                    'diskon'  => $diskon,
                    'total'  => ($harga-$diskon) * $value->jumlah,
                );
            }

        }

        $noregistasi =$strukPelayanan->pasien_daftar->noregistrasi;
        $result =array(
            "noRegistrasi"  => $strukPelayanan->pasien_daftar->noregistrasi,
            "noCm"  => $strukPelayanan->pasien_daftar->pasien->nocm,
            "namaPasien"  => $strukPelayanan->pasien_daftar->pasien->namapasien,
            "jenisPenjamin"  => $strukPelayanan->pasien_daftar->kelompok_pasien->kelompokpasien,
            "jenisKelamin"  => $strukPelayanan->pasien_daftar->pasien->jenis_kelamin->jeniskelamin,
            "umur"  => $strukPelayanan->pasien_daftar->pasien->Umur,
            "totalDeposit"  => $this->getDepositPasien($noregistasi),// $deposit,
            "jumlahBayar"  => $strukPelayanan->totalharusdibayar,//+ $this->getDepositPasien($noregistasi),
            "totalPenjamin" => ($strukPelayanan->totalprekanan==null) ? 0 : $strukPelayanan->totalprekanan,
            "detailTagihan"  => $detailTagihan,

        );
        return $this->respond($result, "Detail Tagihan Pasien");
    }

    public function daftarPiutang(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $filter = $request->all();
        $dataPiutang= \DB::table('strukpelayananpenjamin_t as spp')
            ->join('strukpelayanan_t as sp', 'sp.norec', '=', 'spp.nostrukfk')
            ->join('pasien_m as p', 'p.id', '=', 'sp.nocmfk')
            ->join('pasiendaftar_t as pd', 'sp.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin('rekanan_m as rkn', 'rkn.id', '=', 'pd.objectrekananfk')
            ->join('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftjoin('postinghutangpiutang_t as php', 'php.nostrukfk', '=', 'spp.norec')
            ->leftjoin('strukposting_t as spt', 'spt.noposting', '=', 'php.noposting')
            ->select('kp.kelompokpasien', 'spp.norec','pd.tglpulang as tglstruk', 'pd.noregistrasi', 'pd.tglregistrasi','p.nocm',
                'p.namapasien','ru.namaruangan','spp.totalppenjamin','spp.totalharusdibayar',
                'spp.totalsudahdibayar',  'spp.totalbiaya', 'spp.noverifikasi','rkn.namarekanan','php.noposting','spt.statusenabled',
                'pd.norec as norec_pd','php.statusenabled as sttts')
            ->where('spp.kdprofile', $kdProfile)
            ->whereNotNull('spp.noverifikasi')
            ->where('sp.statusenabled', true);

        if(isset($filter['tglAwal']) && $filter['tglAwal']!=""){
            $dataPiutang = $dataPiutang->where('pd.tglpulang','>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!=""){
            $tgl= $filter['tglAkhir']." 23:59:59";
            $dataPiutang = $dataPiutang->where('pd.tglpulang','<=', $tgl);
        }

        if(isset($filter['kelompokpasienfk']) && $filter['kelompokpasienfk']!=""){
            $dataPiutang = $dataPiutang->where('pd.objectkelompokpasienlastfk','=', $filter['kelompokpasienfk']);
        }

        if(isset($filter['penjaminID']) && $filter['penjaminID']!=""){
            $dataPiutang = $dataPiutang->where('pd.objectkelompokpasienlastfk','=', $filter['penjaminID']);
        }
        if(isset($filter['rekananfk']) && $filter['rekananfk']!=""){
            $dataPiutang = $dataPiutang->where('pd.objectrekananfk','=', $filter['rekananfk']);
        }

        if(isset($filter['ruanganId']) && $filter['ruanganId']!=""){
            $dataPiutang = $dataPiutang->where('ru.id','=', $filter['ruanganId']);
        }
        if(isset($filter['namaPasien']) && $filter['namaPasien']!=""){
            $dataPiutang = $dataPiutang->where('p.namapasien','ilike', '%'.$filter['namaPasien'].'%');
        }
        if(isset($filter['noregistrasi']) && $filter['noregistrasi']!=""){
            $dataPiutang = $dataPiutang->where('pd.noregistrasi','ilike', '%'.$filter['noregistrasi'].'');
        }
        if(isset($filter['nocm']) && $filter['nocm']!=""){
            $dataPiutang = $dataPiutang->where('p.nocm','=', $filter['nocm']);
        }
        if (isset($filter['jmlRows']) && $filter['jmlRows'] != "" && $filter['jmlRows'] != "undefined") {
            $dataPiutang = $dataPiutang->take($filter['jmlRows']);
        }
        $dataPiutang =$dataPiutang->orderBy('pd.tglpulang');
        $dataPiutang =$dataPiutang->get();
        $result = array();
        $no = 1;
        foreach ($dataPiutang as $item) {
            if ($item->statusenabled ==  1 || is_null($item->statusenabled)) {
                if ($item->sttts == 1 || is_null($item->sttts)) {
                    if ($item->totalppenjamin > $item->totalsudahdibayar) {
                        if (!isset($item->noposting)) {
                            $status = 'Piutang';
                        } else {
                            $status = 'Collecting';
                        }
                    } else {
                        $status = 'Lunas';
                    }

                    $result[] = array(
                        'no' => $no++,
                        'noRec' => $item->norec,
                        'tglTransaksi' => $item->tglstruk,
                        'noRegistrasi' => $item->noregistrasi,
                        'namaPasien' => $item->namapasien,
                        'ruangan'=>$item->namaruangan,
                        'kelasRawat' => $item->kelompokpasien,
                        'jenisPasien' => $item->kelompokpasien,
                        'umur' => $this->hitungUmur($item->tglstruk),
                        'kelasPenjamin' => "-",
                        'totalBilling' => $item->totalbiaya,
                        'totalKlaim' => $item->totalppenjamin,
                        'totalBayar' => $item->totalsudahdibayar,
                        'rekanan' => $item->namarekanan,
                        'status' => $status,
                        'norec_pd' => $item->norec_pd,
                        'noposting' => $item->noposting,
                        'stts' => $item->statusenabled,
                    );
                }
            }
        }
        return $this->respond($result, 'Data Piutang Layanan');
    }

    public function daftarPasienAktif(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                     'pd.tglpulang', 'pd.statuspasien',
                DB::raw("
                    CASE WHEN pd.nostruklastfk IS NOT NULL AND pd.nosbmlastfk IS NOT NULL THEN '-'
                    WHEN pd.nostruklastfk IS NOT NULL AND pd.nosbmlastfk IS NULL THEN 'Verifikasi'
                    ELSE 'Belum Verifikasi' END AS statusverif
                "))
            ->where('pd.statusenabled', true)
            ->where('pd.kdprofile', $kdProfile)
            ->whereNull('pd.tglpulang');

        $filter = $request->all();
//        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
//            $tgl0 = $filter['tglAwal'] . " 00:00:00";
//            $data = $data->where('pd.tglregistrasi', '>=', $tgl0);
//        }
//
//        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
//            $tgl = $filter['tglAkhir'] . " 23:59:59";
//            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
//        }

        if(isset($filter['instalasiId']) && $filter['instalasiId'] != "" && $filter['instalasiId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['instalasiId']);
        }

        if(isset($filter['ruanganId']) && $filter['ruanganId'] != "" && $filter['ruanganId'] != "undefined") {
            $data = $data->where('ru.id', '=', $filter['ruanganId']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('p.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }

        if(isset($filter['noReg']) && $filter['noReg'] != "" && $filter['noReg'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noReg'] . '%');
        }

        $data = $data->groupBy('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'pd.tglpulang', 'pd.statuspasien','pd.nostruklastfk','pd.nosbmlastfk');

        $data = $data->get();

        $result = array();
        foreach ($data as $pasienD) {
            $result[] = array(
                'tanggalMasuk' => $pasienD->tglregistrasi,
                'noCm' => $pasienD->nocm,
                'noRegistrasi' => $pasienD->noregistrasi,
                'namaRuangan' => $pasienD->namaruangan,
                'namaPasien' => $pasienD->namapasien,
                'jenisAsuransi' => $pasienD->kelompokpasien,
                'tanggalPulang' => $pasienD->tglpulang,
                'statusverif' => $pasienD->statusverif,
                'status' => $pasienD->statuspasien
            );
        }
        return $this->respond($result);
    }

    public function daftarSBM(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin=$request->all();
        $data = \DB::table('strukbuktipenerimaan_t as sbm')
            ->join('strukpelayanan_t as sp', 'sbm.nostrukfk', '=', 'sp.norec')
            ->leftjoin('pasiendaftar_t as pd', 'sp.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as p', 'p.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pasien_m as ps', 'ps.id', '=', 'sp.nocmfk')
            ->leftjoin('strukbuktipenerimaancarabayar_t as sbmcr', 'sbmcr.nosbmfk', '=', 'sbm.norec')
            ->leftjoin('carabayar_m as cb', 'cb.id', '=', 'sbmcr.objectcarabayarfk')
            ->leftjoin('kelompoktransaksi_m as kt', 'kt.id', '=', 'sbm.objectkelompoktransaksifk')
            ->leftjoin('strukclosing_t as sc', 'sc.norec', '=', 'sbm.noclosingfk')
            ->leftjoin('strukverifikasi_t as sv', 'sv.norec', '=', 'sbm.noverifikasifk')
            ->select('sbm.norec as noRec','cb.carabayar as caraBayar','sbmcr.objectcarabayarfk as idCaraBayar','sbm.objectkelompoktransaksifk as idKelTransaksi',
                              'kt.kelompoktransaksi as kelTransaksi','sbm.keteranganlainnya as keterangan','p.id as idPegawai','p.namalengkap as namaPenerima',
                              'sc.noclosing as noClosing','sbm.nosbm as noSbm','sv.noverifikasi as noVerifikasi','sc.tglclosing as tglClosing',
                              'sbm.tglsbm as tglSbm','sv.tglverifikasi as tglVerif','pd.noregistrasi','ps.namapasien',
                              'sp.norec as norec_sp','ru.id as ruid','ru.namaruangan','sp.namapasien_klien','ps.nocm','sbm.totaldibayar AS totalPenerimaan'
//                    ,\DB::raw("")
            )
            ->where('sbm.statusenabled',true)
            ->where('sbm.kdprofile', $kdProfile);

        $filter = $request->all();
        if(isset($filter['dateStartTglSbm']) && $filter['dateStartTglSbm'] != "" && $filter['dateStartTglSbm'] != "undefined") {
            $tgl2 = $filter['dateStartTglSbm'] ;//. " 00:00:00";
            $data = $data->where('sbm.tglsbm', '>=', $tgl2);
        }
        if(isset($filter['dateEndTglSbm']) && $filter['dateEndTglSbm'] != "" && $filter['dateEndTglSbm'] != "undefined") {
            $tgl = $filter['dateEndTglSbm'] ;//. " 23:59:59";
            $data = $data->where('sbm.tglsbm', '<=', $tgl);
        }
        if(isset($filter['idPegawai']) && $filter['idPegawai'] != "" && $filter['idPegawai'] != "undefined") {
            $data = $data->where('p.id', '=', $filter['idPegawai']);
        }
        if(isset($filter['idCaraBayar']) && $filter['idCaraBayar'] != "" && $filter['idCaraBayar'] != "undefined") {
            $data = $data->where('cb.id', '=', $filter['idCaraBayar']);
        }
        if(isset($filter['idKelTransaksi']) && $filter['idKelTransaksi'] != "" && $filter['idKelTransaksi'] != "undefined") {
            $data = $data->where('kt.id', $filter['idKelTransaksi']);
        }
        if(isset($filter['ins']) && $filter['ins'] != "" && $filter['ins'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', $filter['ins']);
        }
        if(isset($filter['nosbm']) && $filter['nosbm'] != "" && $filter['nosbm'] != "undefined") {
            $data = $data->where('sbm.nosbm','ilike','%'.$filter['nosbm'].'%');
        }
        if(isset($filter['nocm']) && $filter['nocm'] != "" && $filter['nocm'] != "undefined") {
            $data = $data->where('ps.nocm','ilike','%'.$filter['nocm'].'%');
        }
        if(isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien','ilike','%'.$filter['nama'].'%');
        }
        if(isset($filter['desk']) && $filter['desk'] != "" && $filter['desk'] != "undefined") {
            $data = $data->where('sp.namapasien_klien','ilike','%'.$filter['desk'].'%');
        }
        if(isset($filter['JenisPelayanan']) && $filter['JenisPelayanan'] != "" && $filter['JenisPelayanan'] != "undefined") {
            $data = $data->where('pd.jenispelayanan','=',$filter['JenisPelayanan']);
            if($filter['JenisPelayanan'] == 1){
                $data = $data->where('ru.id','=',663);
            }
        }

        if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
            $arrRuang = explode(',',$request['KasirArr']) ;
            $kodeRuang = [];
            foreach ( $arrRuang as $item){
                $kodeRuang[] = (int) $item;
            }
            $data = $data->whereIn('p.id',$kodeRuang);
        }

//        $data = $data->take($request['jmlRows']);
        $data = $data->get();
        return $this->respond($data);
    }

    public function saveLogBatalBayar(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $transStatus = true;
        $dataLogin = $request->all();

        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['noregistrasi'])->first();
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

    protected function UbahCaraBayar(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $transStatus = 'true';
        try{
            $data = StrukBuktiPenerimaanCaraBayar::where('nosbmfk', $request['norec_sbm'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'objectcarabayarfk' => $request['idCaraBayar']
                    ]
                );
        }
        catch(\Exception $e){
            $transStatus = 'false';
            $transMsg =   "Batal Pembayaran Gagal {SP}";

        }

        //JURNAL
        //jurnal verif sP
        $logAcc =new  LogAcc;
        $logAcc->norec = $logAcc->generateNewId();
        $logAcc->jenistransaksi = 'Ubah Pembayaran Tagihan';
        $logAcc->noreff = $request['norec_sbm'];
        $logAcc->status = 0;
        $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        try{
            $logAcc->save();
        }
        catch(\Exception $e){
            $transStatus = 'false';
            $transMsg =   "Simpan logAcc Gagal {SP}";

        }
        //END jurnal verif sP

        if ($transStatus == 'true') {
            $transMsg = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMsg,
                "as" => 'as@epic',
            );
        } else {
            $transMsg = "Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMsg,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMsg);
    }

    protected function deletePembayaranTagihan(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $transStatus = 'true';
        try{
            $produkDeposit =$this->getProdukIdDeposit();
            $noreg = $request['noregistrasi'];
            $sbm = StrukBuktiPenerimaan::where('nostrukfk', $request['norec_sp'])->first();
            if ($request['isdeposit'] == true) {
                $getPP = DB::select(DB::raw("select pp.norec ,pp.produkfk,prd.namaproduk,pp.hargasatuan 
                            from pasiendaftar_t as pd
                            inner join antrianpasiendiperiksa_t as apd on pd.norec=apd.noregistrasifk
                            inner join pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                            inner join produk_m as prd on prd.id=pp.produkfk
                            where pd.kdprofile = $kdProfile and pd.noregistrasi ='$noreg' and pp.produkfk='$produkDeposit'" ));
                if (count($getPP) > 0) {
                    foreach ($getPP as $item) {
                        if ($item->hargasatuan == $sbm['totaldibayar']) {
                            $pelayananPasien = PelayananPasien::where('norec', $item->norec)
                                ->where('kdprofile', $kdProfile)
                                ->delete();
                        }
                    }
                }
            }

            $strukPelayanan = StrukPelayanan::where('norec', $request['norec_sp'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'nosbmlastfk'    => null,
                    ]
                );
            $strukBuktiPenerimanan = StrukBuktiPenerimaan::where('nostrukfk', $request['norec_sp'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'statusenabled' => false,
                        'nostrukfk'    => null,
                    ]
                );
            $pasienDaftar = PasienDaftar::where('nostruklastfk', $request['norec_sp'])
                ->where('kdprofile', $kdProfile)
                ->update([
                        'nosbmlastfk'    => null,
                    ]
                );
        }
        catch(\Exception $e){
            $transStatus = 'false';
            $transMsg =   "Batal Pembayaran Gagal {SP}";

        }

        //JURNAL
        //jurnal verif sP
        $logAcc =new  LogAcc;
        $logAcc->norec = $logAcc->generateNewId();
        $logAcc->jenistransaksi = 'Batal Pembayaran Tagihan';
        $logAcc->noreff = $request['norec_sp'];
        $logAcc->status = 0;
        $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
        try{
            $logAcc->save();
        }
        catch(\Exception $e){
            $transStatus = 'false';
            $transMsg =   "Simpan logAcc Gagal {SP}";

        }
        //END jurnal verif sP

        if ($transStatus == 'true') {
            $transMsg = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMsg,
                "as" => 'as@epic',
            );
        } else {
            $transMsg = "Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMsg,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMsg);
    }

    public function getDataLaporanPenerimaanKasirHarian(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('strukbuktipenerimaan_t as sbm')
            ->leftJOIN('strukbuktipenerimaancarabayar_t as sbmc', 'sbmc.nosbmfk', '=', 'sbm.norec')
            ->leftJOIN('carabayar_m as cb', 'cb.id', '=', 'sbmc.objectcarabayarfk')
            ->join('strukpelayanan_t as sp', 'sp.norec', '=', 'sbm.nostrukfk')
            ->leftJOIN('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftJOIN('pegawai_m as pg2', 'pg2.id', '=', 'lu.objectpegawaifk')
            ->leftJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'sp.noregistrasifk')
            ->leftJOIN('pasien_m as ps', 'ps.id', '=', 'sp.nocmfk')
            ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJOIN('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJOIN('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->select('sbm.tglsbm', 'ps.nocm', 'ru.namaruangan', 'pg.namalengkap', 'pg2.namaexternal as kasir',
                'sp.totalharusdibayar', 'sbm.totaldibayar',
                \DB::raw('( case when pd.noregistrasi is null then sp.nostruk else pd.noregistrasi end) as noregistrasi, 
                (case when ps.namapasien is null then sp.namapasien_klien else ps.namapasien end) as namapasien,
                (case when kp.kelompokpasien is null then null else kp.kelompokpasien end) as kelompokpasien,
                (CASE WHEN sp.totalprekanan is null then 0 else sp.totalprekanan end) as hutangpenjamin,
                (case when cb.id = 1 then sbm.totaldibayar else 0 end) as tunai, 
                (case when cb.id > 1 then sbm.totaldibayar else 0 end) as nontunai')
            )
            ->where('sbm.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sbm.tglsbm', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('sbm.tglsbm', '<=', $tgl);
        }
        if (isset($request['idKasir']) && $request['idKasir'] != "" && $request['idKasir'] != "undefined") {
            $data = $data->where('pg2.id', '=', $request['idKasir']);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('pd.objectpegawaifk', '=', $request['idDokter']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['kelompokPasien']);
        }


        $data = $data->orderBy('pd.noregistrasi', 'ASC');

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'egie@epic'
        );
        return $this->respond($result);
    }

    public function getDataLaporanPenerimaanKasirPerusahaan(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayananpenjamin_t as spp')
            ->join('strukpelayanan_t as sp', 'sp.norec', '=', 'spp.nostrukfk')
            ->join('pelayananpasien_t as pp', 'pp.strukfk', '=', 'sp.norec')
            ->join('antrianpasiendiperiksa_t as ap', 'ap.norec', '=', 'pp.noregistrasifk')
            ->join('pasiendaftar_t as pd ', 'pd.norec', '=', 'ap.noregistrasifk')
            ->JOIN('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->leftJOIN('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJOIN('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('rekanan_m as r', 'r.id', '=', 'spp.kdrekananpenjamin')
            ->leftJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->select('kp.kelompokpasien', 'spp.norec', 'sp.tglstruk', 'pd.noregistrasi', 'pd.tglregistrasi',
                'p.nocm', 'p.namapasien', 'ru.namaruangan', 'pg.namalengkap', 'spp.totalppenjamin', 'spp.totalharusdibayar',
                'spp.totalsudahdibayar',
                'spp.totalbiaya', 'spp.noverifikasi',
                \DB::raw('(spp.totalharusdibayar - spp.totalppenjamin) as sisaBayar'))
            ->where('spp.kdprofile', $kdProfile)
            ->where('kp.id', '=', 5);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sp.tglstruk', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('sp.tglstruk', '<=', $tgl);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('pg.id', '=', $request['idDokter']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        $data = $data->groupBy('kp.kelompokpasien', 'spp.norec', 'sp.tglstruk', 'pd.noregistrasi', 'pd.tglregistrasi',
            'p.nocm', 'p.namapasien', 'ru.namaruangan', 'pg.namalengkap', 'spp.totalppenjamin', 'spp.totalharusdibayar',
            'spp.totalsudahdibayar',
            'spp.totalbiaya','spp.noverifikasi');
        $data = $data->orderBy('pd.noregistrasi', 'ASC');
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'egie@epic'
        );
        return $this->respond($result);
    }

    public function getDataLapPendapatan(Request $request){
        $dataLogin = $request->all();
        $kdProfile = (int)$this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->join('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
            ->join('strukpelayanan_t as sp', 'sp.noregistrasifk', '=', 'pd.norec')
            ->select('apd.objectruanganfk', 'ru.namaruangan', 'apd.objectpegawaifk', 'pg.namalengkap', 'ps.nocm',
                'ps.namapasien', 'pd.noregistrasi', 'kps.kelompokpasien',
                \DB::raw('sum(case when pr.id =395 then pp.hargajual* pp.jumlah else 0 end) as karcis,
                         sum(CASE WHEN kp.id = 26 THEN pp.hargajual* pp.jumlah ELSE 0 END) as konsul,
                         sum(case when pr.id =10013116  then pp.hargajual* pp.jumlah else 0 end) as embos,
                         sum(case when kp.id in (1,2,3,4,8,9,10,11,13,14) then pp.hargajual* pp.jumlah else 0 end) as tindakan,
                         sum((case when pp.hargadiscount is null then 0 else pp.hargadiscount end)* pp.jumlah) as diskon,
                         sum(case when pr.id =395 then pp.hargajual* pp.jumlah else 0 end)
                         +sum(case when pr.id =10013116  then pp.hargajual* pp.jumlah else 0 end)
                         +sum(case when kp.id = 26 then pp.hargajual* pp.jumlah else 0 end)
                         +sum(case when kp.id in (1,2,3,4,8,9,10,11,13,14) then pp.hargajual* pp.jumlah else 0 end)
                         -sum((case when pp.hargadiscount is null then 0 else pp.hargadiscount end)* pp.jumlah) as total,
                          (case when pd.objectkelompokpasienlastfk > 1 then 0 end) as NonPj')
            )
            ->where('pd.kdprofile', $kdProfile)
            ->where('djp.objectjenisprodukfk', '<>', 97)
            ->whereNull('sp.statusenabled');
//            ->where('ru.objectdepartemenfk', 18);

        $kelompokpasiens = array('1', '3', '5');
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('apd.objectpegawaifk', '=', $request['idDokter']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            if ($request['idDept'] == '12345') {
                $data = $data->wherein('ru.objectdepartemenfk', [3,14,16,17,18,19,24,25,26,27,28,35]);
            } else {
                $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
            }
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            if ($request['kelompokPasien'] == 153) {
                $data = $data->wherein('kps.id', [1, 5, 3]);
            } else {
                $data = $data->where('kps.id', '=', $request['kelompokPasien']);
            }
        }

        $data = $data->groupBy('apd.objectruanganfk', 'ru.namaruangan', 'apd.objectpegawaifk', 'pg.namalengkap',
            'ps.nocm', 'ps.namapasien', 'pd.noregistrasi', 'kps.kelompokpasien', 'pd.objectkelompokpasienlastfk',
            'sp.norec');
        $data = $data->orderBy('pd.noregistrasi', 'ASC');
        $data = $data->distinct();
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'egie@epic'
        );
        return $this->respond($result);
    }

    public function getDataLaporanPendapatanRuangan(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $idDept = '';
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $idDept = ' and x.objectdepartemenfk =  ' . $request['idDept'];
        }
        $idRuangan='';
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = ' and x.objectruanganfk = ' . $request['idRuangan'];
        }
        $idKelompok='';
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $idKelompok = 'and x.objectkelompokpasienlastfk = '. $request['kelompokPasien'];
        }
        $idDokter = ' ';
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $idDokter = 'and x.pegawaifk  = '. $request['idDokter'];
        }


        $strstr = "SELECT sp.statusenabled,pj.nojurnal_intern,pj.keteranganlainnya,
                    apd.objectruanganfk,ru.namaruangan,pg.namalengkap,ps.nocm,upper(ps.namapasien) AS namapasien,pr.id AS prid,
                        CASE
                            WHEN pp.produkfk in (395) THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS administrasi,
                        CASE
                            WHEN pp.produkfk in (395) THEN 
															
case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
				
	ELSE 0
                        END AS administrasijurnal,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 101 THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS visite,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 101 THEN 
															
case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
				
	ELSE 0
                        END AS visitejurnal,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 100 THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS konsultasi,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 100 THEN 
                            case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END AS konsultasijurnal,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 99 THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS akomodasi,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 99 THEN 
                            case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END AS akomodasijurnal,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 27666 THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS alatcanggih,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 27666 THEN 
                            case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END AS alatcanggihjurnal,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 97 THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS obatalkes,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk = 97 THEN 
                            case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END AS obatalkesjurnal,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk not in(97, 101, 100, 99, 27666) THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS tindakan,
                        CASE
                            WHEN pp.produkfk not in(395, 10011572, 10011571, 402611) AND djp.objectjenisprodukfk not in(97, 101, 100, 99, 27666) THEN 
                            case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END AS tindakanjurnal,
                    
                        CASE
                            WHEN pp.hargadiscount IS NULL THEN 0
                            ELSE pp.hargadiscount
                        END * pp.jumlah AS diskon,
                    
                        CASE
                            WHEN pp.hargadiscount IS NULL and pj.deskripsiproduktransaksi != 'diskon' THEN 0
                            ELSE case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                        END * pp.jumlah AS diskonjurnal,
                    pd.noregistrasi,
                    kps.kelompokpasien,
                        CASE
                            WHEN pd.objectkelompokpasienlastfk > 1 THEN '-'
                            ELSE 'v'
                        END AS nonpj,
                        CASE
                            WHEN pd.objectkelompokpasienlastfk = 1 THEN '-'
                            ELSE 'v'
                        END AS pj,
                        CASE
                            WHEN sp.norec IS NULL THEN '-'
                            ELSE 'v'
                        END AS verif,
                    pd.tglregistrasi,
                    pr.objectdetailjenisprodukfk,
                    ru.objectdepartemenfk,
                    pd.objectkelompokpasienlastfk,
                    pg.id AS pegawaifk,
                    djp.objectjenisprodukfk,
                    br.norec AS norecbatal,
                    dp.namadepartemen,
                    pp.produkfk,
                        CASE
                            WHEN pp.produkfk in (402611) THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS deposit,
                        CASE
                            WHEN pp.produkfk in (402611) THEN 
                            case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END AS depositjurnal,
                    pp.tglpelayanan AS tgllayan
                   FROM pasiendaftar_t pd
                     LEFT JOIN antrianpasiendiperiksa_t apd ON apd.noregistrasifk = pd.norec
                     LEFT JOIN pelayananpasien_t pp ON pp.noregistrasifk = apd.norec
                     left JOIN postingjurnaltransaksi_t pj ON pj.norecrelated = pp.norec and pj.deskripsiproduktransaksi ='pelayananpasien_t' 
                     left JOIN postingjurnaltransaksid_t pjd ON pjd.norecrelated = pj.norec and pjd.hargasatuand >0
                     LEFT JOIN pegawai_m pg ON pg.id = apd.objectpegawaifk
                     LEFT JOIN ruangan_m ru ON ru.id = apd.objectruanganfk
                     LEFT JOIN produk_m pr ON pr.id = pp.produkfk
                     LEFT JOIN detailjenisproduk_m djp ON djp.id = pr.objectdetailjenisprodukfk
                     LEFT JOIN pasien_m ps ON ps.id = pd.nocmfk
                     LEFT JOIN kelompokpasien_m kps ON kps.id = pd.objectkelompokpasienlastfk
                     LEFT JOIN strukpelayanan_t sp ON sp.norec = pp.strukfk
                     LEFT JOIN batalregistrasi_t br ON br.pasiendaftarfk = pd.norec
                     LEFT JOIN departemen_m dp ON dp.id = ru.objectdepartemenfk";
        $strstr2 =  " SELECT sp.statusenabled,pj.nojurnal_intern,pj.keteranganlainnya,
                    pd.objectruanganlastfk AS objectruanganfk,
                    ru.namaruangan,
                    pg.namalengkap,
                    ps.nocm,
                    upper(ps.namapasien) AS namapasien,
                    pr.id AS prid,
                        CASE
                            WHEN pp.produkfk in (10011572, 10011571) THEN 
                            CASE
                                WHEN pp.hargajual IS NULL THEN 0
                                ELSE pp.hargajual
                            END * pp.jumlah +
                            CASE
                                WHEN pp.jasa IS NULL THEN 0
                                ELSE pp.jasa
                            END
                            ELSE 0
                        END AS administrasi, 
                        CASE
                            WHEN pp.produkfk in (10011572, 10011571) THEN 
                              case when pjd.hargasatuand = 0 then pjd.hargasatuank else pjd.hargasatuand end
                            ELSE 0
                        END as administrasijurnal,
                    0 AS visite,0 AS visite,
                    0 AS konsultasi,0 AS konsultasijurnal,
                    0 AS akomodasi,0 AS akomodasijurnal,
                    0 AS alatcanggih,0 AS alatcanggihjurnal,
                    0 AS obatalkes,0 AS obatalkesjurnal,
                    0 AS tindakan,0 AS tindakanjurnal,
                    0 AS diskon,0 AS diskonjurnal,
                    pd.noregistrasi,
                    kps.kelompokpasien,
                        CASE
                            WHEN pd.objectkelompokpasienlastfk > 1 THEN '-'
                            ELSE 'v'
                        END AS nonpj,
                        CASE
                            WHEN pd.objectkelompokpasienlastfk = 1 THEN '-'
                            ELSE 'v'
                        END AS pj,
                        CASE
                            WHEN sp.norec IS NULL THEN '-'
                            ELSE 'v'
                        END AS verif,
                    pd.tglregistrasi,
                    pr.objectdetailjenisprodukfk,
                    ru.objectdepartemenfk,
                    pd.objectkelompokpasienlastfk,
                    pg.id AS pegawaifk,
                    djp.objectjenisprodukfk,
                    br.norec AS norecbatal,
                    dp.namadepartemen,
                    pp.produkfk,
                    0 AS deposit,0 as depositjurnal,
                    pp.tglpelayanan AS tgllayan
                   FROM pasiendaftar_t pd
                     LEFT JOIN antrianpasiendiperiksa_t apd ON apd.noregistrasifk = pd.norec
                     LEFT JOIN pelayananpasien_t pp ON pp.noregistrasifk = apd.norec
                     left JOIN postingjurnaltransaksi_t pj ON pj.norecrelated = pp.norec 
                     left JOIN postingjurnaltransaksid_t pjd ON pjd.norecrelated = pj.norec 
                     LEFT JOIN pegawai_m pg ON pg.id = apd.objectpegawaifk
                     LEFT JOIN ruangan_m ru ON ru.id = pd.objectruanganlastfk
                     LEFT JOIN produk_m pr ON pr.id = pp.produkfk
                     LEFT JOIN detailjenisproduk_m djp ON djp.id = pr.objectdetailjenisprodukfk
                     LEFT JOIN pasien_m ps ON ps.id = pd.nocmfk
                     LEFT JOIN kelompokpasien_m kps ON kps.id = pd.objectkelompokpasienlastfk
                     LEFT JOIN strukpelayanan_t sp ON sp.norec = pp.strukfk
                     LEFT JOIN batalregistrasi_t br ON br.pasiendaftarfk = pd.norec
                     LEFT JOIN departemen_m dp ON dp.id = ru.objectdepartemenfk";

        $strLast = $strstr . "  " .
            " union all " .
//                    $strstr . " where pj.deskripsiproduktransaksi ='diskon' and pjd.hargasatuand >0 "  . ' union all ' .
            $strstr2;

        $data = \DB::select(DB::raw("select p.nocm,p.noregistrasi,p.namapasien,p.objectruanganfk,p.namaruangan,p.namalengkap,
			              SUM(p.administrasi) as administrasi,SUM(p.visite) as visite,SUM(p.konsultasi) as konsultasi,SUM(p.akomodasi) as akomodasi,
			              SUM(p.alatcanggih) as alatcanggih,SUM(p.obatalkes) as obatalkes, SUM(p.tindakan) as tindakan,SUM(p.deposit) as deposit,
				          SUM(p.diskon) as diskon,
				           SUM(case when p.administrasijurnal is null then 0 else p.administrasijurnal end) as administrasij,
				           SUM(case when p.visitejurnal is null then 0 else p.visitejurnal end) as visitej,
				           SUM(case when p.konsultasijurnal is null then 0 else p.konsultasijurnal end) as konsultasij,
				           SUM(case when p.akomodasijurnal is null then 0 else p.akomodasijurnal end) as akomodasij,
			               SUM(case when p.alatcanggihjurnal is null then 0 else p.alatcanggihjurnal end) as alatcanggihj,
			               SUM(case when p.obatalkesjurnal is null then 0 else p.obatalkesjurnal end) as obatalkesj, 
			               SUM(case when p.tindakanjurnal is null then 0 else p.tindakanjurnal end) as tindakanj, 
			              SUM(p.deposit) as deposit,SUM(p.depositjurnal) as depositj,
				          SUM(p.diskonjurnal) as diskonj,p.nojurnal_intern,p.keteranganlainnya,
				          p.kelompokpasien,p.nonpj,p.pj,p.verif from
                          (select x.*
                          from ($strLast) as x
                          where
                                        x.tgllayan >= '$tglAwal' and x.tgllayan <= '$tglAkhir'
                                        $idDept
                                        $idKelompok
                                        $idRuangan
                                        $idDokter
                                        --and p.noregistrasi='1810021802'
                         ) as p
                         GROUP BY p.noregistrasi,p.nocm,p.namapasien,p.objectruanganfk,p.namaruangan,
                                  p.namalengkap,p.kelompokpasien,p.nonpj,p.nojurnal_intern,p.pj,p.verif,p.keteranganlainnya"));
        $dataBEda = [];
        $sama = false;
        $dataGroupNoJurnal = [];
        foreach ($data as $itm){
            if ((float)$itm->administrasi != (float)$itm->administrasij){
                $dataBEda[] =$itm;
            }
            if ((float)$itm->visite != (float)$itm->visitej){
                $dataBEda[] =$itm;
            }
            if ((float)$itm->konsultasi != (float)$itm->konsultasij){
                $dataBEda[] =$itm;
            }
            if ((float)$itm->akomodasi != (float)$itm->akomodasij){
                $dataBEda[] =$itm;
            }
            if ((float)$itm->alatcanggih != (float)$itm->alatcanggihj){
                $dataBEda[] =$itm;
            }
            if ((float)$itm->obatalkes != (float)$itm->obatalkesj){
                $dataBEda[] =$itm;
            }
            if ((float)$itm->tindakan != (float)$itm->tindakanj){
                $dataBEda[] =$itm;
            }
            $sama=false;
            foreach ($dataGroupNoJurnal as $items) {
                if ($items['nojurnal_intern'] == $itm->nojurnal_intern) {
                    $sama = true;
                    break;
                }
            }
            if ($sama == false) {
                $dataGroupNoJurnal[] =  array(
                    'nojurnal_intern' => $itm->nojurnal_intern,
                    'keteranganlainnya' => $itm->keteranganlainnya,
                );
            }

        }

        $result = array(
            'data' => $data,
            'dataselisih' => $dataBEda,
            'data_jurnal' => $dataGroupNoJurnal,
            'as' => 'efan@epic',
            'edited1' => 'as@epic - 18xxxx',
            'edited2' => 'as@epic - 190415',
        );
        return $this->respond($result);
    }

    public function getDataLaporanPiutangPenjamin(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayananpenjamin_t as spp')
            // ->LEFTJOIN ('pelayananpasien_t as pp','pp.strukfk','=','sp.norec')
            ->JOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'spp.nostrukfk')
            ->JOIN('pelayananpasien_t as pp', 'pp.strukfk', '=', 'sp.norec')
            ->LEFTJOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->JOIN('antrianpasiendiperiksa_t as ap', 'ap.norec', '=', 'pp.noregistrasifk')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'ap.noregistrasifk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'ap.objectruanganfk')
            ->join('departemen_m as dpt', 'dpt.id', '=', 'ru.objectdepartemenfk')
            ->JOIN('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->JOIN('postinghutangpiutang_t as php', 'php.nostrukfk', '=', 'spp.norec')
            ->JOIN('strukposting_t as stp', 'stp.noposting', '=', 'php.noposting')
            ->LEFTJOIN('rekanan_m as r', 'r.id', '=', 'pd.objectrekananfk')
            ->LEFTJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->LEFTJOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->LEFTJOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->LEFTJOIN('kelompokproduk_m as kpr', 'kpr.id', '=', 'jp.objectkelompokprodukfk')
            ->select('kp.kelompokpasien', 'spp.norec', 'stp.tglposting', 'pd.noregistrasi', 'pd.tglregistrasi',
                'p.nocm', 'p.namapasien',
                'ru.namaruangan',
                \DB::raw('case when pr.id =395 then pp.hargajual* pp.jumlah else 0 end as karcis,
                        case when pr.id =10013116  then pp.hargajual* pp.jumlah else 0 end as embos,
                        case when kpr.id = 26 then pp.hargajual* pp.jumlah else 0 end as konsul,
                        case when kpr.id in (1,2,3,4,8,9,10,11,13,14) then pp.hargajual* pp.jumlah else 0 end as tindakan,
                        (case when pp.hargadiscount is null then 0 else pp.hargadiscount end)* pp.jumlah as diskon,
                        (case when pr.objectdetailjenisprodukfk=474 then pp.hargajual* pp.jumlah else 0 end) as totalresep,
                        sp.totalharusdibayar as totalharusdibayar, sp.totalprekanan as totalprekanan, spp.totalppenjamin, spp.totalharusdibayar,
                        spp.totalsudahdibayar, r.namarekanan, spp.totalbiaya , spp.noverifikasi, php.noposting, stp.kdhistorylogins')
            )
            ->where('spp.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglpulang', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglpulang', '<=', $tgl);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('pd.objectkelompokpasienlastfk', '=', $request['kelompokPasien']);
        }
        $data = $data->groupby('sp.totalharusdibayar', 'sp.totalprekanan', 'kp.kelompokpasien', 'spp.norec',
            'stp.tglposting', 'pd.noregistrasi', 'pd.tglregistrasi', 'p.nocm', 'p.namapasien',
            'ru.namaruangan', 'pr.id', 'pp.hargajual', 'pp.jumlah', 'pp.hargadiscount', 'kpr.id',
            'pr.objectdetailjenisprodukfk', 'spp.totalppenjamin', 'spp.totalharusdibayar',
            'spp.totalsudahdibayar', 'r.namarekanan', 'spp.totalbiaya', 'spp.noverifikasi',
            'php.noposting', 'stp.kdhistorylogins');
        $data = $data->distinct();
        $data = $data->orderBy('pd.tglregistrasi');

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    protected function getDepositPasien($noregistrasi){
//        $kdProfile = (int)$this->getDataKdProfile($request);
        $produkIdDeposit = $this->getProdukIdDeposit();
        $deposit = 0;
        $pasienDaftar  = PasienDaftar::has('pelayanan_pasien')->where('noregistrasi', $noregistrasi)->first();
        if($pasienDaftar){
            $depositList =$pasienDaftar->pelayanan_pasien()->where('nilainormal', '-1')->whereNull('strukfk')->get();
            foreach ($depositList as $item){
                if($item->produkfk==$produkIdDeposit){
                    $deposit = $deposit + $item->hargasatuan;
                }
            }
        }
        return $deposit;
    }

    protected function simpanPembayaranDeposit(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['parameterTambahan']['noRegistrasi'])->where('kdprofile', $kdProfile)->first();
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        DB::beginTransaction();
        $NOSBM = array();
        $SP = new StrukPelayanan();
        $SP->norec = $SP->generateNewId();
        $AntrianPasienDiperiksa = $pasienDaftar->antrian_pasien_diperiksa->first();
        $PP = new PelayananPasien();
        $PP->norec  = $PP->generateNewId();
        $PP->kdprofile = $kdProfile;
//        $PP->kdprofile = $this->getKdProfile();
        $PP->noregistrasifk = $AntrianPasienDiperiksa->norec; //salah
        $PP->aturanpakai = "-";
        $PP->hargasatuan= (float)$request['jumlahBayar'];
        $PP->hargajual= (float)$request['jumlahBayar'];
        $PP->jumlah= 1;
        $PP->nilainormal= -1;
        $PP->keteranganlain = "-";
        $PP->keteranganpakai2 = "-";
        $PP->produkfk= $this->getProdukIdDeposit();
        $PP->stock= 1;
        $PP->tglpelayanan= $request['tglsbm'];//$this->getDateTime();

        try{
            $PP->save();
        }
        catch(\Exception $e){
            $this->transStatus = false;
            $this->transMessage = "Simpan deposit Gagal {PP}";
        }

        $SP->kdprofile =  $kdProfile;
        $SP->statusenabled =  false;
        $SP->nocmfk =  $pasienDaftar->nocmfk;
        $SP->noregistrasifk =  $pasienDaftar->norec;
        $SP->objectkelaslastfk =  $pasienDaftar->objectkelasfk;
        $SP->objectkelompoktransaksifk =  1;
        $SP->objectpegawaipenerimafk =  $dataPegawaiUser[0]->id;//$this->getCurrentLoginID();
        $SP->nostruk =  $this->generateCode(new StrukPelayanan, 'nostruk', 10, 'S',$kdProfile);
        $SP->tglstruk =  $request['tglsbm'];//$this->getDateTime();
        $SP->objectruanganfk=  $pasienDaftar->objectruanganlastfk;
        try{
            $SP->save();
        }
        catch(\Exception $e){
            $this->transStatus= false;
            $this->transMessage = "Simpan deposit Gagal {SP}";
        }

        $strukPelayananDetail = new StrukPelayananDetail();
        $strukPelayananDetail->norec = $strukPelayananDetail->generateNewId();
        $strukPelayananDetail->nostrukfk = $SP->norec;
        $strukPelayananDetail->kdprofile = $kdProfile;
        $strukPelayananDetail->hargadiscount = 0;
        $strukPelayananDetail->hargadiscountgive = 0;
        $strukPelayananDetail->hargadiscountsave = 0;
        $strukPelayananDetail->harganetto = $PP->hargajual;
        $strukPelayananDetail->hargapph = $PP->hargajual;
        $strukPelayananDetail->hargappn = $PP->hargajual;
        $strukPelayananDetail->hargasatuan = $PP->hargasatuan;
        $strukPelayananDetail->hargasatuandijamin = 0;
        $strukPelayananDetail->hargasatuanppenjamin = 0;
        $strukPelayananDetail->hargasatuanpprofile = 0;
        $strukPelayananDetail->hargatambahan = 0;
        $strukPelayananDetail->isonsiteservice = 0;
        $strukPelayananDetail->persendiscount = 0;
        $strukPelayananDetail->qtyproduk = $PP->jumlah;
        $strukPelayananDetail->qtyprodukoutext = 0;
        $strukPelayananDetail->qtyprodukoutint = 0;
        $strukPelayananDetail->qtyprodukretur = 0;
        $strukPelayananDetail->satuan = 0;
        $strukPelayananDetail->satuanstandar = 0;
        $strukPelayananDetail->is_terbayar = 0;
        $strukPelayananDetail->tglpelayanan = $SP->tglstruk;
        $strukPelayananDetail->objectprodukfk = $PP->produkfk;
        $strukPelayananDetail->objectkelasfk = $PP->kelasfk;

        try{
            $strukPelayananDetail->save();
        }
        catch(\Exception $e){
            $this->transStatus= false;
            $this->transMessage = "Simpan deposit Gagal {SPD}";
        }
//        if($this->transStatus){
        foreach($request['pembayaran'] as $pembayaran){
            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
            //$SBM123213 = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->kdprofile= $kdProfile;
            $strukBuktiPenerimanan->statusenabled = 1;
            $strukBuktiPenerimanan->keteranganlainnya = "Pembayaran Deposit Pasien";
            $strukBuktiPenerimanan->nostrukfk = $SP->norec;
            $strukBuktiPenerimanan->objectkelompokpasienfk = $SP->pasien_daftar->pasien->objectkelompokpasienfk;
            $strukBuktiPenerimanan->objectkelompoktransaksifk = 1;
            $strukBuktiPenerimanan->objectpegawaipenerimafk  =$this->getCurrentLoginID();
            $strukBuktiPenerimanan->tglsbm  = $SP->tglstruk;
            $strukBuktiPenerimanan->totaldibayar  = $pembayaran['nominal'];
            $sbmKode = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
            $strukBuktiPenerimanan->nosbm = $sbmKode;//$this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'));
            $NOSBM[] = $strukBuktiPenerimanan->nosbm;

            //$nostrukfkSTR = $strukBuktiPenerimanan->norec ;
            try{
                $strukBuktiPenerimanan->save();
            }
            catch(\Exception $e){
                $this->transStatus = false;
                throw new \Exception($e->getMessage());
                $this->transMessage = "Simpan deposit Gagal {SBP}";
            }


            if($this->transStatus){
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile= $kdProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $pembayaran['caraBayar']['id'];
                $SBPCB->totaldibayar = $pembayaran['nominal'];
                if(isset($pembayaran['detailBank'])){
                    $SBPCB->objectjeniskartufk = $pembayaran['detailBank']['jenisKartu']['id'];
                    $SBPCB->nokartuaccount = $pembayaran['detailBank']['noKartu'];
                    $SBPCB->namabankprovider = $pembayaran['detailBank']['namaKartu'];
                    $SBPCB->namapemilik = $pembayaran['detailBank']['namaKartu'];
                }
                try{
                    $SBPCB->save();
                }
                catch(\Exception $e){
                    $this->transStatus = false;
//                        throw new \Exception($e);
                    $this->transMessage = "Simpan deposit Gagal {SBPCB}";
                }
                //jurnal verif sP
                $logAcc =new  LogAcc;
                $logAcc->norec = $logAcc->generateNewId();
                $logAcc->jenistransaksi = 'Penerimaan Deposit';
                $logAcc->noreff = $SBPCB->norec;
                $logAcc->status = 0;
                $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                if($this->transStatus){
                    try{
                        $logAcc->save();
                    }
                    catch(\Exception $e){
                        $transStatus= false;
                        $transMsg =   "Simpan logAcc Gagal {SP}";

                    }
                }
            }
        }

        if($this->transStatus){
            $this->transMessage = "Simpan deposit Berhasil";
            $this->statusCode = 201;
            DB::commit();
        }else{
            $this->statusCode = 400;
            DB::rollBack();
        }
        return $this->respond(array('noSBM'=> $NOSBM), $this->transMessage);
    }

    protected function simpanPembayaranTagihanPasien(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $noRegistrasi = '';
        \DB::beginTransaction();
        $strukPelayanan = StrukPelayanan::where('norec', $request['parameterTambahan']['noRecStrukPelayanan'])->first();
        $sisa =0;
        if($strukPelayanan->nosbmlastfk==null || $strukPelayanan->nosbmlastfk==''){
            $sisa = $sisa + $this->getDepositPasien($strukPelayanan->pasien_daftar->noregistrasi);
        }

        $deposit = $sisa;

        $sisa = $sisa + $request['jumlahBayar'];


        $NOSBM = array();
        foreach($request['pembayaran'] as $pembayaran){
            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->kdprofile= $kdProfile;
            $strukBuktiPenerimanan->keteranganlainnya = "Pembayaran Tagihan Pasien";
            $strukBuktiPenerimanan->statusenabled= 1;
            $strukBuktiPenerimanan->nostrukfk = $strukPelayanan->norec;
            $strukBuktiPenerimanan->objectkelompokpasienfk = $strukPelayanan->pasien_daftar->pasien->objectkelompokpasienfk;
            $strukBuktiPenerimanan->objectkelompoktransaksifk = 1;
            $strukBuktiPenerimanan->objectpegawaipenerimafk  = $this->getCurrentLoginID();
            $strukBuktiPenerimanan->tglsbm  =  $request['tglsbm'];//$this->getDateTime();
            $strukBuktiPenerimanan->totaldibayar  = $pembayaran['nominal'];
            $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
            $NOSBM[] = $strukBuktiPenerimanan->nosbm;
            $nostrukfkSTR = $strukPelayanan->norec ;
//            try{
            $strukBuktiPenerimanan->save();
//            }
//            catch(\Exception $e){
//                throw new \Exception($e->getMessage());
            $this->transMessage = "Simpan Pembayaran Gagal {SBP}";
//            }

            if($this->transStatus){
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile= $kdProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $pembayaran['caraBayar']['id'];
                $SBPCB->totaldibayar = $pembayaran['nominal'];

//                if(isset($pembayaran['detailBank'])){
//                    $SBPCB->objectjeniskartufk = $pembayaran['detailBank']['jenisKartu']['id'];
//                    $SBPCB->nokartuaccount = $pembayaran['detailBank']['noKartu'];
//                    $SBPCB->namabankprovider = $pembayaran['detailBank']['namaKartu'];
//                    $SBPCB->namapemilik = $pembayaran['detailBank']['namaKartu'];
//                }
                try{
                    $SBPCB->save();
                }
                catch(\Exception $e){
                    $this->transStatus = false;
//                    throw new \Exception($e->getMessage());
                    $this->transMessage = "Simpan Pembayaran Gagal {SBPCB}";
                }

            }

            //JURNAL
            //jurnal verif sP
            $logAcc =new  LogAcc;
            $logAcc->norec = $logAcc->generateNewId();
            $logAcc->jenistransaksi = 'Pembayaran Tagihan';
            $logAcc->noreff = $nostrukfkSTR;
            $logAcc->status = 0;
            $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            if($this->transStatus){
                try{
                    $logAcc->save();
                }
                catch(\Exception $e){
                    $transStatus= false;
                    $transMsg =   "Simpan logAcc Gagal {SP}";

                }
            }
            //END jurnal verif sP
//            $detailJurnal = array();
//            $saldo = (float)$pembayaran['nominal'];
//            $idbendahara = ((int)$pembayaran['caraBayar']['id']==1) ? 1318 : 1333; //Kas Bendahara Penerimaan : Bank In Transit
//            if($this->transStatus){
//                $saldoJurnal = array();
//                $saldoJurnal[] = array(
//                    'account_id' => $idbendahara,
//                    'balance'    => 'D',
//                    'saldo'      => $saldo
//                );
//                if($deposit>0){
//                    $saldoJurnal[] = array(
//                        'account_id' =>   1475, //Uang Muka PAsien
//                        'balance'    =>  'D',
//                        'saldo'      => $deposit
//                    );
//                }
//
//                $saldoJurnal[] = array(
//                    'account_id' =>   1348, //Piutang Perorangan
//                    'balance'    =>  'K',
//                    'saldo'      => $saldo + $deposit
//                );
//
//                $detailJurnal[] = array(
//                    "tgltransaksi" => $this->getDateTime(),
//                    "notransaksi" =>  $strukBuktiPenerimanan->nosbm,
//                    "saldoJurnal" => $saldoJurnal,
//                    "ruanganid" => $strukPelayanan->objectruanganfk,
//                    "kelompoktransaksiid" => 1,
//                    "nobuktitransaksi" => $strukBuktiPenerimanan->nosbm,
//                    "tglbuktitransaksi" => $this->getDateTime(),
//                    "keteranganlainnya" => "Pembayaran Tagihan Pasien",
//                );
//
//                $jurnal = array(
//                    "noposting" => $noPosting = $this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'BT-'.$this->getDateTime()->format('ym')),
//                    "detailJurnal" =>$detailJurnal
//                );
//                $this->postingJournal($jurnal);
//            }
        }

        if($this->transStatus){
//            $strukPelayananDetail = $strukPelayanan->struk_pelayanan_detail;
//            foreach ($strukPelayananDetail as $item){
//                $total = ($item->harganetto-$item->hargasatuandijamin)*$item->qtyproduk;
//                if(($total<=$sisa || $total<=($sisa+ 0.0001)) && $item->is_terbayar==0){
//                    $item->is_terbayar=1;
//                    $sisa = $sisa - $total;
//                    try{
//                        $item->save();
//                    }
//                    catch(\Exception $e){
//                        $this->transStatus = false;
//                        $this->transMessage = "Simpan Pembayaran Gagal {SPD}";
//                        break;
//                    }
//                }
//            }

            //INSERT DARI PELAYANANPASIEN KE STUKPELAYANANPASIENBAYAR
            //INSERT DARI PELAYAANPASIENDETIAL KE STRUKPEKAYANANPASIENKBAYAR
        }

        if($this->transStatus){
            $strukPelayanan->nosbmlastfk =$strukBuktiPenerimanan->norec;
//            $strukPelayanan->sisa = $sisa;
            try{
                $strukPelayanan->save();
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transStatus = false;
                $this->transMessage = "Simpan Pembayaran Gagal {SPD}";
            }
        }

        if($this->transStatus){
            $pd = $strukPelayanan->pasien_daftar;
            $pd->nosbmlastfk =$strukBuktiPenerimanan->norec;
            $noRegistrasi = $pd['noregistrasi'];
//            $strukPelayanan->sisa = $sisa;
            try{
                $pd->save();
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transStatus = false;
                $this->transMessage = "Simpan Pembayaran Gagal {pasienDaftar}";
            }
        }


        if($this->transStatus){
            $this->transMessage = "Simpan Pembayaran Berhasil";
            $this->statusCode = 201;
            DB::commit();
        }else{
            $this->statusCode = 400;
            DB::rollBack();
        }
        return $this->respond(array('noSBM'=> $NOSBM,'noReg'=> $noRegistrasi), $this->transMessage);
    }

    protected function simpanPengembalianDeposit(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $pasienDaftar = PasienDaftar::where('noregistrasi', $request['parameterTambahan']['noRegistrasi'])->where('kdprofile', $kdProfile)->first();
        $dataSP = StrukPelayanan::where('noregistrasifk',$pasienDaftar->norec)
            ->wherenull('statusenabled')
            ->first();
        $norec_sp = $dataSP['norec'];
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $kdProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $NOSBM = array();

        foreach($request['pembayaran'] as $pembayaran){
            try{
                $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
                //$SBM123213 = $strukBuktiPenerimanan->generateNewId();
                $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
                $strukBuktiPenerimanan->kdprofile= $kdProfile;
                $strukBuktiPenerimanan->statusenabled = 1;
                $strukBuktiPenerimanan->keteranganlainnya = "Pengembalian Deposit Pasien";
                $strukBuktiPenerimanan->nostrukfk = $norec_sp;
                $strukBuktiPenerimanan->objectkelompokpasienfk = $pasienDaftar->objectkelompokpasienlastfk;
                $strukBuktiPenerimanan->objectkelompoktransaksifk = 1;
                $strukBuktiPenerimanan->objectpegawaipenerimafk  =$this->getCurrentLoginID();
                $strukBuktiPenerimanan->tglsbm  = $dataSP['tglstruk'];
                $strukBuktiPenerimanan->totaldibayar  = $pembayaran['nominal'];
                $sbmKode = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
                $strukBuktiPenerimanan->nosbm = $sbmKode;//$this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'));
                $NOSBM[] = $strukBuktiPenerimanan->nosbm;
                $NOSBM2 = $strukBuktiPenerimanan->nosbm;

                $strukBuktiPenerimanan->save();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus = 'false';
                $this->transMessage = "Simpan deposit Gagal {SBP}";
            }
            $norec_sbm=$strukBuktiPenerimanan->norec;

            try{
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile= $kdProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $pembayaran['caraBayar']['id'];
                $SBPCB->totaldibayar = $pembayaran['nominal'];
                if(isset($pembayaran['detailBank'])){
                    $SBPCB->objectjeniskartufk = $pembayaran['detailBank']['jenisKartu']['id'];
                    $SBPCB->nokartuaccount = $pembayaran['detailBank']['noKartu'];
                    $SBPCB->namabankprovider = $pembayaran['detailBank']['namaKartu'];
                    $SBPCB->namapemilik = $pembayaran['detailBank']['namaKartu'];
                }
                $SBPCB->save();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus = 'false';
                $this->transMessage = "Simpan deposit Gagal {SBPCB}";
            }
            //jurnal
            try{
                $logAcc =new  LogAcc;
                $logAcc->norec = $logAcc->generateNewId();
                $logAcc->jenistransaksi = 'Penerimaan Deposit';
                $logAcc->noreff = $SBPCB->norec;
                $logAcc->status = 0;
                $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                $logAcc->save();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus = 'false';
                $transMsg =   "Simpan logAcc Gagal {SP}";
            }
            //END jurnal
            try{
                $dt=StrukPelayanan::where('noregistrasifk', $pasienDaftar->norec)
                    ->wherenull('statusenabled')
                    ->update([
                            'nosbmlastfk' => $norec_sbm]
                    );
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus = 'false';
                $this->transMessage = "Simpan Pembayaran Gagal {SPD}";
            }

            try{
                $pasienDaftar->nosbmlastfk =$norec_sbm;
                $noRegistrasi = $pasienDaftar['noregistrasi'];
                $pasienDaftar->save();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus = 'false';
                $this->transMessage = "Simpan Pembayaran Gagal {pasienDaftar}";
            }
        }

        if($transStatus == 'true'){
            $this->transMessage = "Simpan deposit Berhasil";
            $this->statusCode = 201;
            DB::commit();
        }else{
            $this->transMessage = "Simpan deposit gagal";
            $this->statusCode = 400;
            DB::rollBack();
        }
        return $this->respond(array('noSBM'=> $NOSBM2,'noReg'=>$request['parameterTambahan']['noRegistrasi'],), $this->transMessage);
    }

    protected function simpanCicilanPasien($request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $strukPelayananPenjamin = StrukPelayananPenjamin::where('norec', $request['parameterTambahan']['noRecStrukPelayanan'])
            ->where('kdprofile', $kdProfile)
            ->first();
        $strukPelayanan = StrukPelayanan::where('norec', $strukPelayananPenjamin->nostrukfk)
            ->where('kdprofile', $kdProfile)
            ->first();
//        $sisa =0;
//        if($strukPelayanan->nosbmlastfk==null || $strukPelayanan->nosbmlastfk==''){
//            $sisa = $sisa + $this->getDepositPasien($strukPelayanan->pasien_daftar->noregistrasi);
//        }
//
//        $deposit = $sisa;
//
//        $sisa = $sisa + $request['jumlahBayar'];


        $NOSBM = array();
        foreach($request['pembayaran'] as $pembayaran){
            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->kdprofile= $kdProfile;
            $strukBuktiPenerimanan->keteranganlainnya = "Pembayaran Cicilan Tagihan Pasien";
            $strukBuktiPenerimanan->statusenabled= 1;
            $strukBuktiPenerimanan->nostrukfk = $strukPelayanan->norec;
            $strukBuktiPenerimanan->objectkelompokpasienfk = $strukPelayanan->pasien_daftar->pasien->objectkelompokpasienfk;
            $strukBuktiPenerimanan->objectkelompoktransaksifk = 1;
            $strukBuktiPenerimanan->objectpegawaipenerimafk  = $this->getCurrentLoginID();
            $strukBuktiPenerimanan->tglsbm  = $this->getDateTime();
            $strukBuktiPenerimanan->totaldibayar  = $pembayaran['nominal'];
            $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
            $NOSBM[] = $strukBuktiPenerimanan->nosbm;
            $nostrukfkSTR = $strukPelayanan->norec ;
            try{
                $strukBuktiPenerimanan->save();
                $strukPelayananPenjamin->totalsudahdibayar =$strukPelayananPenjamin->totalsudahdibayar+$pembayaran['nominal'];
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transMessage = "Simpan Pembayaran Gagal {SBP}";
            }

            if($this->transStatus){
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile=$kdProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $pembayaran['caraBayar']['id'];

                if(isset($pembayaran['detailBank'])){
                    $SBPCB->objectjeniskartufk = $pembayaran['detailBank']['jenisKartu']['id'];
                    $SBPCB->nokartuaccount = $pembayaran['detailBank']['noKartu'];
                    $SBPCB->namabankprovider = $pembayaran['detailBank']['namaKartu'];
                    $SBPCB->namapemilik = $pembayaran['detailBank']['namaKartu'];
                }
                try{
                    $SBPCB->save();
                }
                catch(\Exception $e){
                    $this->transStatus = false;
//                    throw new \Exception($e->getMessage());
                    $this->transMessage = "Simpan Pembayaran Gagal {SBPCB}";
                }

            }

            //JURNAL
            //jurnal verif sP
//            $logAcc =new  LogAcc;
//            $logAcc->norec = $logAcc->generateNewId();
//            $logAcc->jenistransaksi = 'Pembayaran Cicilan Piutang';
//            $logAcc->noreff = $nostrukfkSTR;
//            $logAcc->status = 0;
//            $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
//            if($this->transStatus){
//                try{
//                    $logAcc->save();
//                }
//                catch(\Exception $e){
//                    $transStatus= false;
//                    $transMsg =   "Simpan logAcc Gagal {SP}";
//
//                }
//            }
            //END jurnal verif sP
//            $detailJurnal = array();
//            $saldo = (float)$pembayaran['nominal'];
//            $idbendahara = ((int)$pembayaran['caraBayar']['id']==1) ? 1318 : 1333; //Kas Bendahara Penerimaan : Bank In Transit
//            if($this->transStatus){
//                $saldoJurnal = array();
//                $saldoJurnal[] = array(
//                    'account_id' => $idbendahara,
//                    'balance'    => 'D',
//                    'saldo'      => $saldo
//                );
//                if($deposit>0){
//                    $saldoJurnal[] = array(
//                        'account_id' =>   1475, //Uang Muka PAsien
//                        'balance'    =>  'D',
//                        'saldo'      => $deposit
//                    );
//                }
//
//                $saldoJurnal[] = array(
//                    'account_id' =>   1348, //Piutang Perorangan
//                    'balance'    =>  'K',
//                    'saldo'      => $saldo + $deposit
//                );
//
//                $detailJurnal[] = array(
//                    "tgltransaksi" => $this->getDateTime(),
//                    "notransaksi" =>  $strukBuktiPenerimanan->nosbm,
//                    "saldoJurnal" => $saldoJurnal,
//                    "ruanganid" => $strukPelayanan->objectruanganfk,
//                    "kelompoktransaksiid" => 1,
//                    "nobuktitransaksi" => $strukBuktiPenerimanan->nosbm,
//                    "tglbuktitransaksi" => $this->getDateTime(),
//                    "keteranganlainnya" => "Pembayaran Tagihan Pasien",
//                );
//
//                $jurnal = array(
//                    "noposting" => $noPosting = $this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'BT-'.$this->getDateTime()->format('ym')),
//                    "detailJurnal" =>$detailJurnal
//                );
//                $this->postingJournal($jurnal);
//            }
        }

        if($this->transStatus){
//            $strukPelayananDetail = $strukPelayanan->struk_pelayanan_detail;
//            foreach ($strukPelayananDetail as $item){
//                $total = ($item->harganetto-$item->hargasatuandijamin)*$item->qtyproduk;
//                if(($total<=$sisa || $total<=($sisa+ 0.0001)) && $item->is_terbayar==0){
//                    $item->is_terbayar=1;
//                    $sisa = $sisa - $total;
//                    try{
//                        $item->save();
//                    }
//                    catch(\Exception $e){
//                        $this->transStatus = false;
//                        $this->transMessage = "Simpan Pembayaran Gagal {SPD}";
//                        break;
//                    }
//                }
//            }

        }

        if($this->transStatus){
            try{
                $strukPelayananPenjamin->save();
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transStatus = false;
                $this->transMessage = "Simpan Pembayaran Gagal {SPP}";
            }
        }


        if($this->transStatus){
            $this->transMessage = "Simpan Pembayaran Berhasil";
            $this->statusCode = 201;
//            DB::rollBack();

            DB::commit();
        }else{
            $this->statusCode = 400;
            DB::rollBack();
        }
        return $this->respond(array('noSBM'=> $NOSBM), $this->transMessage);
//        disini ngikutin pembayaran laginya..
//        return $this->respond([], "Simpan Pembayaran Berhasil");
    }

    protected function simpanPembayaranTagihanNonLayanan($request){
        $kdProfile = (int)  $kdProfile = $this->getDataKdProfile($request);
        \DB::beginTransaction();
        $strukPelayanan = StrukPelayanan::where('norec', $request['parameterTambahan']['noRecStrukPelayanan'])
            ->where('kdprofile', $kdProfile)
            ->first();
        $sisa =0;
//        if($strukPelayanan->nosbmlastfk==null || $strukPelayanan->nosbmlastfk==''){
//            $sisa = $sisa + $this->getDepositPasien($strukPelayanan->pasien_daftar->noregistrasi);
//        }

        $deposit = $sisa;

        $sisa = $sisa + $request['jumlahBayar'];


        $NOSBM = array();
        foreach($request['pembayaran'] as $pembayaran){
            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->kdprofile= $kdProfile;
            $strukBuktiPenerimanan->keteranganlainnya = "Pembayaran Tagihan Non Layanan";
            $strukBuktiPenerimanan->statusenabled= 1;
            $strukBuktiPenerimanan->nostrukfk = $strukPelayanan->norec;
//            $strukBuktiPenerimanan->objectkelompokpasienfk = $strukPelayanan->pasien_daftar->pasien->objectkelompokpasienfk;
            $strukBuktiPenerimanan->objectkelompoktransaksifk = $this->getGlobalSettingDataFixed('kdTransaksiTagihanNonLayanan', $kdProfile); //dibikin lagi lagi untuk nonlayanan
            $strukBuktiPenerimanan->objectpegawaipenerimafk  = $this->getCurrentLoginID();
            $strukBuktiPenerimanan->tglsbm  = $request['tglsbm'];//$this->getDateTime();
            $strukBuktiPenerimanan->totaldibayar  = $pembayaran['nominal'];
            $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
            $NOSBM[] = $strukBuktiPenerimanan->nosbm;
            $nostrukfkSTR = $strukPelayanan->norec ;
            try{
                $strukBuktiPenerimanan->save();
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transMessage = "Simpan Pembayaran Gagal {SBP}";
            }

            if($this->transStatus){
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile= $kdProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $pembayaran['caraBayar']['id'];

                if(isset($pembayaran['detailBank'])){
                    $SBPCB->objectjeniskartufk = $pembayaran['detailBank']['jenisKartu']['id'];
                    $SBPCB->nokartuaccount = $pembayaran['detailBank']['noKartu'];
                    $SBPCB->namabankprovider = $pembayaran['detailBank']['namaKartu'];
                    $SBPCB->namapemilik = $pembayaran['detailBank']['namaKartu'];
                }
                try{
                    $SBPCB->save();
                }
                catch(\Exception $e){
                    $this->transStatus = false;
//                    throw new \Exception($e->getMessage());
                    $this->transMessage = "Simpan Pembayaran Gagal {SBPCB}";
                }

            }

            //JURNAL

            //jurnal verif sP
            $logAcc =new  LogAcc;
            $logAcc->norec = $logAcc->generateNewId();
            $logAcc->jenistransaksi = 'Pembayaran Tagihan Non Layanan';
            $logAcc->noreff = $nostrukfkSTR;
            $logAcc->status = 0;
            $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            if($this->transStatus){
                try{
                    $logAcc->save();
                }
                catch(\Exception $e){
                    $transStatus= false;
                    $transMsg =   "Simpan logAcc Gagal {SP}";

                }
            }
            //END jurnal verif sP
//            $detailJurnal = array();
//            $saldo = (float)$pembayaran['nominal'];
//            $idbendahara = ((int)$pembayaran['caraBayar']['id']==1) ? 1318 : 1333; //Kas Bendahara Penerimaan : Bank In Transit
//            if($this->transStatus && 1==2){  //nanti aja yaa bosss..
//                $saldoJurnal = array();
//                $saldoJurnal[] = array(
//                    'account_id' => $idbendahara,
//                    'balance'    => 'D',
//                    'saldo'      => $saldo
//                );
//                if($deposit>0){
//                    $saldoJurnal[] = array(
//                        'account_id' =>   1475, //Uang Muka PAsien
//                        'balance'    =>  'D',
//                        'saldo'      => $deposit
//                    );
//                }
//
//                $saldoJurnal[] = array(
//                    'account_id' =>   1348, //Piutang Perorangan
//                    'balance'    =>  'K',
//                    'saldo'      => $saldo + $deposit
//                );
//
//                $detailJurnal[] = array(
//                    "tgltransaksi" => $this->getDateTime(),
//                    "notransaksi" =>  $strukBuktiPenerimanan->nosbm,
//                    "saldoJurnal" => $saldoJurnal,
//                    "ruanganid" => $strukPelayanan->objectruanganfk,
//                    "kelompoktransaksiid" => 1,
//                    "nobuktitransaksi" => $strukBuktiPenerimanan->nosbm,
//                    "tglbuktitransaksi" => $this->getDateTime(),
//                    "keteranganlainnya" => "Pembayaran Tagihan Pasien",
//                );
//
//                $jurnal = array(
//                    "noposting" => $noPosting = $this->generateCode(new PostingJurnalTransaksi, 'noposting', 14, 'BT-'.$this->getDateTime()->format('ym')),
//                    "detailJurnal" =>$detailJurnal
//                );
//                $this->postingJournal($jurnal);
//            }
        }

        if($this->transStatus){
//            $strukPelayananDetail = $strukPelayanan->struk_pelayanan_detail;
//            foreach ($strukPelayananDetail as $item){
//                $total = ($item->harganetto-$item->hargasatuandijamin)*$item->qtyproduk;
//                if(($total<=$sisa || $total<=($sisa+ 0.0001)) && $item->is_terbayar==0){
//                    $item->is_terbayar=1;
//                    $sisa = $sisa - $total;
//                    try{
//                        $item->save();
//                    }
//                    catch(\Exception $e){
//                        $this->transStatus = false;
//                        $this->transMessage = "Simpan Pembayaran Gagal {SPD}";
//                        break;
//                    }
//                }
//            }

            //INSERT DARI PELAYANANPASIEN KE STUKPELAYANANPASIENBAYAR
            //INSERT DARI PELAYAANPASIENDETIAL KE STRUKPEKAYANANPASIENKBAYAR
        }

        if($this->transStatus){
            $strukPelayanan->nosbmlastfk =$strukBuktiPenerimanan->norec;
//            $strukPelayanan->sisa = $sisa;
            try{
                $strukPelayanan->save();
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transStatus = false;
                $this->transMessage = "Simpan Pembayaran Gagal {SPD}";
            }
        }


        if($this->transStatus){
            $this->transMessage = "Simpan Pembayaran Berhasil";
            $this->statusCode = 201;
//            DB::rollBack();

            DB::commit();
        }else{
            $this->statusCode = 400;
            DB::rollBack();
        }
        return $this->respond(array('noSBM'=> $NOSBM), $this->transMessage);
    }

    protected function simpanCicilanPasienCollect($request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $strukPelayananPenjamin = StrukPelayananPenjamin::where('norec', $request['parameterTambahan']['noRecStrukPelayanan'])
            ->where('kdprofile', $kdProfile)
            ->first();
        $strukPelayanan = StrukPelayanan::where('norec', $strukPelayananPenjamin->nostrukfk)
            ->where('kdprofile', $kdProfile)
            ->first();
        $pasienDaftar = PasienDaftar::where('norec', $strukPelayanan->noregistrasifk)
            ->where('kdprofile', $kdProfile)
            ->first();
        $noSBM = '';
        try{
        $NOSBM = array();
        foreach($request['pembayaran'] as $pembayaran){
            $strukBuktiPenerimanan = new StrukBuktiPenerimaan();
            $strukBuktiPenerimanan->norec = $strukBuktiPenerimanan->generateNewId();
            $strukBuktiPenerimanan->kdprofile= $kdProfile;
            $strukBuktiPenerimanan->keteranganlainnya = "Pembayaran Cicilan Tagihan Pasien Collecting";
            $strukBuktiPenerimanan->statusenabled= 1;
            $strukBuktiPenerimanan->nostrukfk = $strukPelayanan->norec;
            $strukBuktiPenerimanan->objectkelompokpasienfk = $strukPelayanan->pasien_daftar->pasien->objectkelompokpasienfk;
            $strukBuktiPenerimanan->objectkelompoktransaksifk = 76;
            $strukBuktiPenerimanan->objectpegawaipenerimafk  = $this->getCurrentLoginID();
            $strukBuktiPenerimanan->tglsbm  = $this->getDateTime();
            $strukBuktiPenerimanan->totaldibayar  = $pembayaran['nominal'];
            $strukBuktiPenerimanan->nosbm = $this->generateCode(new StrukBuktiPenerimaan, 'nosbm', 14, 'RV-'.$this->getDateTime()->format('ym'), $kdProfile);
            $NOSBM[] = $strukBuktiPenerimanan->nosbm;
            $nostrukfkSTR = $strukPelayanan->norec ;
            $noSBM = $strukBuktiPenerimanan->nosbm;
            $strukBuktiPenerimanan->save();
            $strukPelayananPenjamin->totalsudahdibayar =$strukPelayananPenjamin->totalsudahdibayar+$pembayaran['nominal'];

            if($this->transStatus){
                $SBPCB = new StrukBuktiPenerimaanCaraBayar();
                $SBPCB->norec = $SBPCB->generateNewId();
                $SBPCB->kdprofile= $kdProfile;
                $SBPCB->statusenabled = 1;
                $SBPCB->nosbmfk = $strukBuktiPenerimanan->norec;
                $SBPCB->objectcarabayarfk = $pembayaran['caraBayar']['id'];

                if(isset($pembayaran['detailBank'])){
                    $SBPCB->objectjeniskartufk = $pembayaran['detailBank']['jenisKartu']['id'];
                    $SBPCB->nokartuaccount = $pembayaran['detailBank']['noKartu'];
                    $SBPCB->namabankprovider = $pembayaran['detailBank']['namaKartu'];
                    $SBPCB->namapemilik = $pembayaran['detailBank']['namaKartu'];
                }
                $SBPCB->save();
            }

        }

//            $pd = $strukPelayanan->pasien_daftar;
//            $pd->nosbmlastfk =$strukBuktiPenerimanan->norec;
//            $pd->save();

            //JURNAL
            //jurnal verif sP
            $logAcc =new  LogAcc;
            $logAcc->norec = $logAcc->generateNewId();
            $logAcc->jenistransaksi = 'Pembayaran Cicilan Piutang';
            $logAcc->noreff = $nostrukfkSTR;
            $logAcc->status = 0;
            $logAcc->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            $logAcc->save();

        foreach($request['detailSPP'] as $DetailSPP) {
            $SPP = StrukPelayananPenjamin::where('norec', $DetailSPP['noRecSPP'])->where('kdprofile', $kdProfile)->first();
            $SP = StrukPelayanan::where('norec', $SPP->nostrukfk)->where('kdprofile', $kdProfile)->first();
            $PD = PasienDaftar::where('norec', $SP->noregistrasifk)->where('kdprofile', $kdProfile)->first();
            $SPP->totalsudahdibayar = $SPP->totalsudahdibayar + $DetailSPP['bayarKlaim'];
            $SPP->save();
            $pd = $SP->pasien_daftar;
            $pd->nosbmlastfk = $strukBuktiPenerimanan->norec;
            $noRegistrasi = $pd['noregistrasi'];
            $pd->save();
        }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Pembayaran Piutang";
        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            \DB::commit();
            $result = array(
                "status" => 201,
//                "message" => $transMessage,
//                "data" => $aingMacan,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//            if($this->transStatus){
//                $this->transMessage = "Simpan Pembayaran Berhasil";
//                $this->statusCode = 201;
////            DB::rollBack();
//
//                DB::commit();
//            }else{
//                $this->statusCode = 400;
//                DB::rollBack();
//            }
//
//        }
//        return $this->respond(array('noSBM'=> $NOSBM), $this->transMessage);
    }

    public function simpanPembayaran(Request $request){
        switch ($request['parameterTambahan']['tipePembayaran']) {
            case 'depositPasien':
                return $this->simpanPembayaranDeposit($request);
                break;
            case 'PenyetoranDepositKasirKembali':
                return $this->simpanPengembalianDeposit($request);
                break;
            case 'tagihanPasien':
                return $this->simpanPembayaranTagihanPasien($request);
                break;
            case 'cicilanPasien':
                return $this->simpanCicilanPasien($request);
                break;
            case 'tagihanNonLayanan':
                return $this->simpanPembayaranTagihanNonLayanan($request);
                break;
            case 'cicilanPasienCollect':
                return $this->simpanCicilanPasienCollect($request);
//                return $this->simpanCicilanPiutang($request);
                break;
            default:
                return 0;
        }
    }

    protected function getDetailPembayaranDeposit(Request $request){
        $result = array(
            'jumlahBayar' => $request['jumlahBayar'],
            'tipePembayaran' => $request['tipePembayaran'],
            'noRegistrasi' => $request['noRegistrasi'],
        );
        return $this->respond($result, "Data Pembayaran");
    }

    protected function getDetailTagihanPasien(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $strukPelayanan = StrukPelayanan::where('norec', $request['noRecStrukPelayanan'])->where('kdprofile', $kdProfile)->first();
        $totalBilling= $strukPelayanan->totalharusdibayar;
        $totalBayar = $totalBilling;
        $result =array(
            "noRecStrukPelayanan"  => $strukPelayanan->norec,
            "jumlahBayar"  => $totalBayar,
        );
        return $this->respond($result, "Data Pembayaran");
    }

    protected  function getDetailCicilanPasien(Request $request){
        $result =array(
            "noRecStrukPelayanan"  => $request['noRecStrukPelayanan'],
            "jumlahBayar"  => $request['jumlahBayar'],
        );
        return $this->respond($result, "Data Pembayaran");
    }

    protected  function getDetailPembayaranNonLayanan(Request $request){
        return $this->respond("Data Pembayaran");
    }

    public function pembayaran(Request $request){
        switch ($request['tipePembayaran']) {
            case 'depositPasien':
                return $this->getDetailPembayaranDeposit($request);
                break;
            case 'tagihanPasien':
                return $this->getDetailTagihanPasien($request);
                break;
            case 'cicilanPasien':
                return $this->getDetailCicilanPasien($request);
                break;
            case 'pembayaranNonLayanan':
                return $this->getDetailPembayaranNonLayanan($request);
            default:
                return 0;
        }
    }
    public function detailPasienDeposit($noRegister, Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->where('kdprofile', $kdProfile)->first();
        if(!$pasienDaftar){
            //kalau tidak ditemukan
            return false;
        }

        $listDeposit = array();
        $pasienDaftar->DepositId = $this->getProdukIdDeposit();
        $deposit = $pasienDaftar->list_deposit;
        foreach ($deposit as $item){
            $listDeposit[] = array(
                "tglTransaksi" => $item->tglpelayanan,
                "jumlahDeposit" => $item->hargasatuan*$item->jumlah
            );
        }

        $result = array(
            'noCm'  => $pasienDaftar->pasien->nocm,
            'noRegistrasi' => $pasienDaftar->noregistrasi,
            'jenisKelamin' => $pasienDaftar->pasien->jenis_kelamin->namaexternal,
            'namaPasien'  => $pasienDaftar->pasien->namapasien,
            'umur'  => $pasienDaftar->pasien->Umur,
            'status'        => $pasienDaftar->statuspasien,
            'detailDeposit' => $listDeposit
        );


        return $this->respond($result, "Detail Deposit Pasien");
    }
    public function HapusTglPulang(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $pasienDaftar = PasienDaftar::where('noregistrasi',$request['noregistrasi'])->where('kdprofile', $kdProfile)->first();
        try {
            $data=$dataAsalRujukan = \DB::table('pasiendaftar_t as pd')
                ->leftjoin('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
                ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
                ->select('ru.objectdepartemenfk')
                ->where('pd.kdprofile', $kdProfile)
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
                    ->where('kdprofile', $kdProfile)
                    ->where('objectruanganfk',$pasienDaftar->objectruanganlastfk)
                    ->orderBy('tglmasuk','desc')
                    ->first();
                $registrasiPelPas = RegistrasiPelayananPasien::where('noregistrasifk',$pasienDaftar->norec )
                    ->where('objectruanganfk',$pasienDaftar->objectruanganlastfk)
                    ->where('kdprofile', $kdProfile)
                    ->orderBy('tglmasuk','desc')
                    ->first();

                if ($request['tglpulang']== 'null'){
                    $ddddd=PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                        ->where('kdprofile', $kdProfile)
                        ->update([
                                'tglpulang' => null]
//                        'tglpulang' => $request['tglpulang']]
                        );
                    $apd = AntrianPasienDiperiksa::where('norec',$antrian->norec)
                        ->where('kdprofile', $kdProfile)
                        ->update([ 'tglkeluar' => null ] );
                    $rpp = RegistrasiPelayananPasien::where('norec',$registrasiPelPas->norec)
                        ->where('kdprofile', $kdProfile)
                        ->update([ 'tglkeluar' => null ] );

                }else{
                    $ddddd=PasienDaftar::where('noregistrasi', $request['noregistrasi'])
                        ->where('kdprofile', $kdProfile)
                        ->update([
                                'tglpulang' => $request['tglpulang']]
                        );

                    $apd = AntrianPasienDiperiksa::where('norec',$antrian->norec)
                        ->where('kdprofile', $kdProfile)
                        ->update([ 'tglkeluar' => null ] );
                    $rpp = RegistrasiPelayananPasien::where('norec',$registrasiPelPas->norec)
                        ->where('kdprofile', $kdProfile)
                        ->update([ 'tglkeluar' => null ] );
                    //## Logging User
                    $newId = LoggingUser::max('id');
                    $newId = $newId +1;
                    $logUser = new LoggingUser();
                    $logUser->id = $newId;
                    $logUser->norec = $logUser->generateNewId();
                    $logUser->kdprofile= $kdProfile;
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

    public function BatalInputTagihanNonLayanan(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        \DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = StrukPelayanan::where('norec', $request['norec'])
                ->where('kdprofile', $kdProfile)
                ->update([
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
            $logUser->jenislog = 'Batal Transaksi Non Layanan Dengan Nostruk : ' . $request['nostruk'];
            $logUser->noreff =$request['norec'];
            $logUser->referensi='norec strukpelayanan_t';
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

    public function getDataLaporanPenerimaan(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("select ru.kelompokpenerimaan,ru.penerimaankasir,SUM(z.total) AS total 
                            from ruangan_m as ru 
                            left JOIN (SELECT x.id,x.namaruangan,x.penerimaankasir,SUM(x.total) as total
                            FROM(select ru.id,ru.namaruangan,ru.penerimaankasir,
                            ((pp.hargasatuan-(case when pp.hargadiscount is null then 0 else pp.hargadiscount end))* pp.jumlah) + CASE when pp.jasa is null then 0 else pp.jasa end as total
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pd.objectkelompokpasienlastfk not in (2,4,10)
                                        and pp.strukresepfk IS NULL
                            
                            UNION ALL
                            
                             select ru.id,ru.namaruangan,ru.penerimaankasir,
                            ((pp.hargasatuan - (case when pp.hargadiscount is null then 0 else pp.hargadiscount end)) * pp.jumlah) + CASE when pp.jasa is null then 0 else pp.jasa end as total
                            from pelayananpasien_t as pp
                             INNER JOIN strukresep_t as sr on sr.norec=pp.strukresepfk
                             INNER JOIN ruangan_m as ru on ru.id=sr.ruanganfk
                             INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                             INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                             where sbm.tglsbm between '$tglAwal' and '$tglAkhir'	
                            
                             UNION ALL
                            
                            select 599 as id,'bpjs' as namaruangan,'20. BPJS Kesehatan' as penerimaankasir,sbm.totaldibayar AS total
                            from strukpelayanan_t as sp 
                            INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk 
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pd.objectkelompokpasienlastfk in (2,4,10,8)
                            
                            UNION ALL
                            
                            select 600 as id,'Jamkesda' as namaruangan,'21. Jamkesda' as penerimaankasir,sp.totalprekanan AS total
                            from strukpelayanan_t as sp 
                            INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                            and sp.totalprekanan <> 0
                            and pd.objectkelompokpasienlastfk=8
                            
                            union all
                            
                            select 579 as id,'DIKLAT' as namaruangan,'1. Diklat' as penerimaankasir,
                                   CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total
                            from strukpelayanan_t as sp 
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE sp.objectkelompoktransaksifk =13 
                                        AND sp.tglstruk BETWEEN '$tglAwal' and '$tglAkhir'
                                        AND sp.nosbmlastfk IS NOT NULL
                            
                            UNION ALL
                            
                            select 604 as id,'Jasa Ketatausahaan' as namaruangan,'2. Jasa Ketatausahaan' as penerimaankasir,
                                   CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total
                            from strukpelayanan_t as sp 
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE sp.objectkelompoktransaksifk = 2 
                                  AND sp.keteranganlainnya <> 'Penjualan Obat Bebas'
                                        AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'                                       
                                                                
                            UNION ALL
                            
                            select 
                                 ru.id,ru.namaruangan,ru.penerimaankasir,
                                 CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total
                            from strukpelayanan_t as sp 
                                     INNER JOIN ruangan_m as ru on ru.id = sp.objectruanganfk
                                     INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                                     WHERE sp.objectkelompoktransaksifk = 2 AND sp.keteranganlainnya = 'Penjualan Obat Bebas'
                                     AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'
                                     AND sp.nosbmlastfk IS NOT NULL
                            
                            UNION ALL
                            
                            select 605 as id,'Sewa Ruang' as namaruangan,'1. Sewa Ruang' as penerimaankasir,
                                   CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total
                            from strukpelayanan_t as sp
                            INNER JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                            LEFT JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE spd.objectprodukfk in (4041304,4041305,4041306,4041307,4041308,4041309,4041310,4041311,4041312)
                                        AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'
                            
                            
                            UNION ALL
                            
                            select 606 as id,'Pendapatan Lainnya' as namaruangan,'2. Pendapatan Lainnya' as penerimaankasir,
                                   CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total
                            from strukpelayanan_t as sp 
                            INNER JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE spd.objectprodukfk in (1002121435) AND sp.objectkelompoktransaksifk not in (13)
                                  AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'
                            GROUP BY sbm.totaldibayar
                                  
                             UNION ALL
                                                        
                            select 
                                     ru.id,ru.namaruangan,ru.penerimaankasir,
                                     CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total
                            from strukpelayanan_t as sp 
                                             INNER JOIN ruangan_m as ru on ru.id = sp.objectruanganfk
                                             INNER JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                                             INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                                             WHERE spd.objectprodukfk in (402611)
                                             AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir') as x
                            GROUP BY x.id,x.namaruangan,x.penerimaankasir) as z on z.id = ru.id
                            WHERE ru.ipaddress is not null and ru.kelompokpenerimaan is not null
                            GROUP BY ru.kelompokpenerimaan,ru.penerimaankasir"));

        return $this->respond($data);
    }

    public function getDataLaporanRekapPendapatanHarian(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("SELECT x.subheader,x.penerimaankasir,x.kelompokpenerimaan_produk,x.penerimaan_produk,SUM(x.total) AS total,x.nopenerimaan
                            FROM ruangan_m AS ru 
                            LEFT JOIN (SELECT 'a. Rekam Medik' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                      ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                      pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            FROM pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                  and pro.id in (4040398,4040399,4040406) and ru.id not in (558,560,561,559,577,580,569)
                                                                                                                                                        
                            UNION ALL
                                    
                            SELECT 'b. Poli Spesialis Psikiatri' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                     pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pro.id in (4040404,4040405,40411416,4040400,4040407) 
                                        and ru.id in (566,563)
                            
                            UNION ALL
                                    
                            SELECT 'c. Poli Spesialis Saraf' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                     pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399) 
                                and ru.id in (558)
                            
                            UNION ALL
                                    
                            SELECT 'd. Poli Spesialis PD' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                 ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                 pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399) 
                                        and ru.id in (559)
                            
                            UNION ALL
                                    
                            SELECT 'e. Poli Spesialis K&K' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                     pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399) 
                                and ru.id in (561)
                            
                            UNION ALL
                            
                            SELECT 'f. Poli Spesialis Anak' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                     pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399) 
                                        and ru.id in (560)
                            
                            UNION ALL
                            
                            SELECT 'g. Poli Spesialis Rehab Medik' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                     pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399) 
                                and ru.id in (577,580)
                            
                            UNION ALL
                            
                            SELECT CASE WHEN ru.id = 558 THEN 'l. TM Penyakit Saraf' 
                             WHEN ru.id = 559 THEN 'm. TM Penyakit Dalam' 
                             WHEN ru.id = 561 THEN 'n. TM Penyakit Kulit & Kel' 
                             WHEN ru.id = 560 THEN 'o. TM Penyakit ANAK' 
                             WHEN pro.id = 4040575 THEN 'i. MMPI' 
                             WHEN ru.id in (566,563) THEN 'j. Psikoterapi' 
                             ELSE 'k. Tindakan' END AS subheader,
                             ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                             ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                             CASE WHEN ru.id = 558 THEN 'TM Penyakit Saraf' 
                             WHEN ru.id = 559 THEN 'TM Penyakit Dalam' 
                             WHEN ru.id = 561 THEN 'TM Penyakit Kulit & Kel' 
                             WHEN ru.id = 560 THEN 'TM Penyakit ANAK' 
                             WHEN pro.id = 4040575 THEN 'MMPI' 
                             WHEN ru.id in (566,563) THEN 'Psikoterapi' 
                             ELSE 'Tindakan' END AS penerimaan_produk,
                             'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,1 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'						
                                        and pro.id not in (44040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399)
                                        and ru.id in (558,559,561,560,563,566) AND pp.strukresepfk IS NULL
                            
                            UNION ALL
                            
                            SELECT CASE WHEN pro.id in (4040398,4040399,4040406) THEN 'a. Rekam Medik IGD' 
                                         WHEN pro.id in (4040507,4041106,4041107,4041108,4041110,4041170,4041181,4041776) THEN 'b. Askep(Perawatan)' 
                                         WHEN pro.id in (4040404,4040405,40411416,4040400) THEN 'c. Pemeriksaan dr' 		 
                                         WHEN pro.id in (4040467,4040468,4040469,4040470,4040471,4040472,4040473) THEN 'e. AKOMODASI HCU/PICU/NICU' 		 
                                         ELSE 'd. Tindakan Medik IGD' END AS subheader,ru.id,'A.2. Instalasi Gawat Darurat' as penerimaankasir,
                                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                         CASE WHEN pro.id in (4040398,4040399,4040406) THEN 'Rekam Medik IGD'
                                         WHEN pro.id in (4040507,4041106,4041107,4041108,4041110,4041170,4041181,4041776) THEN 'Askep(Perawatan)' 		   
                                         WHEN pro.id in (4040404,4040405,40411416,4040400) THEN 'Pemeriksaan dr'
                                         WHEN pro.id in (4040467,4040471) THEN 'VVIP'
                                         WHEN pro.id in (4040468) THEN 'VIP'
                                         WHEN pro.id in (4040469,4040472) THEN 'I'
                                         WHEN pro.id in (4040470,4040473) THEN 'II'
                                         WHEN pro.id in (28079,28080) THEN 'III'
                                         ELSE 'Tindakan Medik IGD' END AS penerimaan_produk,'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,2 as nopenerimaan
                            FROM pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'						
                                        and ru.id = 569 and pp.strukresepfk IS NULL AND pro.id not in (4040575)
                                        
                            
                            UNION ALL
                                    
                            SELECT CASE WHEN apd.objectkelasfk = 7 AND pro.id = 4041109 THEN 'VVIP Biasa' 
                                         WHEN apd.objectkelasfk = 4 AND pro.id = 4041109 THEN 'VIP Biasa' 
                                         WHEN apd.objectkelasfk = 3 AND pro.id = 4041109 THEN 'Kls I Biasa' 		 
                                         WHEN apd.objectkelasfk = 2 AND pro.id = 4041109 THEN 'Kls II Biasa'
                                         WHEN apd.objectkelasfk = 1 AND pro.id = 4041109 THEN 'Kls III Biasa'
                                         WHEN pro.id in (4040474,4040475) THEN 'Visite Dokter'
                                         WHEN pro.id in (4040411,4041749,4041750,4041751,4041752) Then 'Gelang Pasien'			
                                         ELSE 'Psikoterapi RI' END AS subheader,
                                         ru.id,'A.3. Rawat Inap' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                                         ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                         CASE WHEN apd.objectkelasfk = 7 AND pro.id = 4041109 THEN 'Intensif Care' 
                                         WHEN apd.objectkelasfk = 4 AND pro.id = 4041109 THEN 'Intensif Care' 
                                         WHEN apd.objectkelasfk = 3 AND pro.id = 4041109 THEN 'Intensif Care' 		 
                                         WHEN apd.objectkelasfk = 2 AND pro.id = 4041109 THEN 'Intensif Care'
                                         WHEN apd.objectkelasfk = 1 AND pro.id = 4041109 THEN 'Intensif Care'
                                         WHEN pro.id in (4040474,4040475) THEN 'Visite Dokter'
                                         WHEN pro.id in (4040411,4041749,4041750,4041751,4041752) Then 'Gelang Pasien'			
                                         ELSE 'Psikoterapi RI' END AS penerimaan_produk,'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,3 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pp.strukresepfk IS NULL	and ru.objectdepartemenfk = 16 
                                        and pro.id not in (44040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399,4040575)
                            
                            UNION ALL
                            
                            SELECT 'Instalasi Psikogeriatri' as subheader,ru.id,'A.5. Instalasi Psikogeriatri' as penerimaankasir,
                                        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                         'Instalasi Psikogeriatri' as penerimaan_produk,'A. PELAYANAN PASIEN' as kelompokpenerimaan_produk,5 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pro.id NOT IN (4040398,4040399,4040406,4040575) 
                                        and pp.strukresepfk is null and ru.id in (567)
                            
                            UNION ALL
                                    
                            SELECT CASE WHEN ru.id = 564 THEN 'Instalasi Napza' 		 
                                     WHEN ru.id = 565 THEN 'Instalasi Kes Jiwa Anak Remaja (TKA)' 
                                     WHEN ru.id = 575 THEN 'Instalasi Laboratorium' 
                                     WHEN ru.id = 576 THEN 'Instalasi Radiologi'
                                     WHEN ru.id = 571 THEN 'Instalasi Elektromedik' 
                                     WHEN ru.id IN (572,573) THEN 'Instalasi Rehab Mental'
                                     WHEN ru.id = 568 THEN 'Instalasi Gigi & Mulut' 
                                     WHEN ru.id = 574 THEN 'Instalasi Gizi' END AS subheader,ru.id,
                                     CASE WHEN ru.id = 564 THEN 'A.4 Instalasi Napza' 		 
                                     WHEN ru.id = 565 THEN 'A.6. Instalasi Kes Jiwa Anak Remaja (TKA)' 
                                     WHEN ru.id = 575 THEN 'A.8. Instalasi Laboratorium' 
                                     WHEN ru.id = 576 THEN 'A.9. Instalasi Radiologi'
                                     WHEN ru.id = 571 THEN 'A.10. Instalasi Elektromedik' 
                                     WHEN ru.id IN (572,573) THEN 'A.11. Instalasi Rehab Mental'
                                     WHEN ru.id = 568 THEN 'A.12. Instalasi Gigi & Mulut' 
                                     WHEN ru.id = 574 THEN 'A.15. Instalasi Gizi' END AS penerimaankasir,
                                     ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 ELSE pp.hargadiscount END))*pp.jumlah)+
                                     CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                     CASE WHEN ru.id = 564 THEN 'Instalasi Napza' 		 
                                     WHEN ru.id = 565 THEN 'Instalasi Kes Jiwa Anak Remaja (TKA)' 
                                     WHEN ru.id = 575 THEN 'Instalasi Laboratorium' 
                                     WHEN ru.id = 576 THEN 'Instalasi Radiologi'
                                     WHEN ru.id = 571 THEN 'Instalasi Elektromedik' 
                                     WHEN ru.id IN (572,573) THEN 'Instalasi Rehab Mental'
                                     WHEN ru.id = 568 THEN 'Instalasi Gigi & Mulut' 
                                     WHEN ru.id = 574 THEN 'Instalasi Gizi' END AS penerimaan_produk,
                                    'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,
                                    CASE WHEN ru.id = 564 THEN 4
                                    WHEN ru.id = 565 THEN 6
                                    WHEN ru.id = 575 THEN 8
                                    WHEN ru.id = 576 THEN 9
                                    WHEN ru.id = 571 THEN 10
                                    WHEN ru.id IN (572,573) THEN 11
                                    WHEN ru.id = 568 THEN 12
                                    WHEN ru.id = 574 THEN 15 END AS nopenerimaan
                            FROM pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'						
                                        AND ru.id in (564,565,575,576,571,568,574,572,573)
                                        AND pp.strukresepfk IS NULL and pro.id not in (4040398,4040406,4040399,4040575) --(44040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399)
                            
                            UNION ALL
                            
                            SELECT 'Instalasi Fisioterapi' as subheader,ru.id,'A.7. Instalasi Fisioterapi' as penerimaankasir,
                                        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                         'Instalasi Fisioterapi' as penerimaan_produk,'A. PELAYANAN PASIEN' as kelompokpenerimaan_produk,7 as nopenerimaan
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                                        and pro.id NOT IN (44040404,4040405,40411416,4040400,4040407,4040398,4040406,4040399,4040575) 
                                        and pp.strukresepfk is null and ru.id in (577)
                            
                            UNION ALL
                                    
                            SELECT CASE WHEN ru.id in (94,125) THEN 'R. Jalan'
                                         WHEN ru.id in (116) THEN 'R. Ranap' END AS subheader,ru.id,
                                         'A.13. Instalasi Farmasi' AS penerimaankasir,
                                         ((pp.hargasatuan - (case when pp.hargadiscount is null then 0 else pp.hargadiscount end)) * pp.jumlah) + CASE when pp.jasa is null then 0 else pp.jasa end as total,
                                         CASE WHEN ru.id in (94,125) THEN 'R. Jalan'
                                         WHEN  ru.id in (116) THEN 'R. Ranap' END AS penerimaan_produk,
                                         'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,13 AS nopenerimaan        
                            from pelayananpasien_t as pp
                            INNER JOIN strukresep_t as sr on sr.norec=pp.strukresepfk
                            INNER JOIN ruangan_m as ru on ru.id=sr.ruanganfk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'
                            
                            UNION ALL
                                                                                 
                            select 
                                     CASE WHEN ru.id in (94,125) THEN 'R. Jalan'
                                     WHEN  ru.id in (116) THEN 'R. Ranap' END AS subheader,ru.id,
                                     'A.13. Instalasi Farmasi' AS penerimaankasir,
                                     CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total,
                                     CASE WHEN ru.id in (94,125) THEN 'R. Jalan'
                                     WHEN  ru.id in (116) THEN 'R. Ranap' END AS penerimaan_produk,
                                     'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,13 AS nopenerimaan
                            from strukpelayanan_t as sp 
                                 INNER JOIN ruangan_m as ru on ru.id = sp.objectruanganfk
                                 INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                                 WHERE sp.objectkelompoktransaksifk = 2 AND sp.keteranganlainnya = 'Penjualan Obat Bebas'
                                 AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'		
                            
                            UNION ALL 
                            
                            SELECT  'Visite Apt' AS subheader,ru.id,
                                            'A.13. Instalasi Farmasi' AS penerimaankasir,
                                            ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END))*pp.jumlah)+
                                            CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,'Visite Apt' AS penerimaan_produk,
                                            'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,13 AS nopenerimaan
                            FROM pelayananpasien_t as pp
                                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk        
                                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk        
                                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'				
                            AND pro.id = 4041191
                            
                            UNION ALL
                                   
                            SELECT  CASE WHEN ru1.objectdepartemenfk = 16 THEN 'R. Ranap' ELSE 'R.Jalan' END AS subheader,ru.id,
                                            'A.14. Instalasi Psikologi' AS penerimaankasir,
                                            ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END))*pp.jumlah)+
                                            CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                                            CASE WHEN ru.objectdepartemenfk = 16 THEN 'R. Ranap' ELSE 'R.Jalan' END AS penerimaan_produk,
                                            'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,14 AS nopenerimaan
                            FROM pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN ruangan_m as ru1 on ru1.id=pd.objectruanganlastfk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                            where sbm.tglsbm between '$tglAwal' and '$tglAkhir'						
                                        AND pro.id not in (4040398,4040399,4040406,4040575) 
                                        AND ru.id = 570 and pp.strukresepfk IS NULL
                            
                            UNION ALL
                                        
                            select 'Diklat' AS subheader,579 as id,'B.1. Diklat' AS penerimaankasir,
                                         CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total,
                                         'Diklat' AS penerimaan_produk,'B. PENDAPATAN PENDIDIKAN DAN PELATIHAN' AS kelompokpenerimaan_produk,
                                         15 AS nopenerimaan
                            from strukpelayanan_t as sp 
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE sp.objectkelompoktransaksifk =13 
                                        AND sp.tglstruk BETWEEN '$tglAwal' and '$tglAkhir'						
                            
                            UNION ALL
                            
                            select 'Jasa Ketatausahaan' AS subheader,604 as id,'B.2. Jasa Ketatausahaan' AS penerimaankasir,
                                         CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total,
                                         'Jasa Ketatausahaan' AS penerimaan_produk,'B. PENDAPATAN PENDIDIKAN DAN PELATIHAN' AS kelompokpenerimaan_produk,
                                         16 AS nopenerimaan
                            from strukpelayanan_t as sp 
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE sp.objectkelompoktransaksifk = 2 
                                        AND sp.keteranganlainnya <> 'Penjualan Obat Bebas'
                                        AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'															
                            
                             UNION ALL
                                                             
                            select 
                                         'Sewa Ruang' AS subheader,605 as id,'B.3. Sewa Ruang' AS penerimaankasir,
                                         CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total,
                                         'Sewa Ruang' AS penerimaan_produk,'C. PENDAPATAN LAIN_LAIN' AS kelompokpenerimaan_produk,
                                         17 AS nopenerimaan
                            from strukpelayanan_t as sp
                            INNER JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                            LEFT JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE spd.objectprodukfk in (4041304,4041305,4041306,4041307,4041308,4041309,4041310,4041311,4041312)
                                                    AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'
                            
                            
                            UNION ALL
                            
                            select 'Pendapatan Lainnya' AS subheader,606 as id,'B.4. Pendapatan Lainnya' AS penerimaankasir,
                                         CASE WHEN sbm.totaldibayar IS NULL THEN 0 ELSE sbm.totaldibayar END AS total,
                                         'Pendapatan Lainnya' AS penerimaan_produk,'C. PENDAPATAN LAIN_LAIN' AS kelompokpenerimaan_produk,
                                         18 AS nopenerimaan
                            from strukpelayanan_t as sp 
                            INNER JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
                            WHERE spd.objectprodukfk in (1002121435,402611) AND sp.objectkelompoktransaksifk not in (13)
                                        AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'
                            GROUP BY sbm.totaldibayar) as x on x.id=ru.id
                            WHERE ru.ipaddress is not null and ru.kelompokpenerimaan is not null
                            and x.subheader is not null
                            GROUP BY x.subheader,x.penerimaankasir,x.kelompokpenerimaan_produk,x.penerimaan_produk,x.nopenerimaan
                            ORDER BY x.nopenerimaan ASC"));
       return $this->respond($data);
    }

    public function getDataLaporanPendapatanRuanganNew(Request $request) {
//        $data = \DB::table('pasiendaftar_t as pd')
//            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
//            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
//            ->leftjoin ('pegawai_m as pgw','pgw.id','=','pd.objectdokterpemeriksafk')
//            ->join ('ruangan_m as rg','rg.id','=','apd.objectruanganfk')
//            ->leftJoin('kelompokpasien_m as klp','klp.id','=','pd.objectkelompokpasienlastfk')
//            ->leftJoin('pelayananpasien_t as ply', 'ply.noregistrasifk','=','apd.norec')
//            ->leftJoin('produk_m as prd','prd.id','=','ply.produkfk')
//
//            ->select('pd.norec','pd.noregistrasi','ps.namapasien','pgw.namalengkap as namadokter','pd.statuspasien','rg.namaruangan','ply.hargajual','ply.jumlah','prd.namaproduk','klp.kelompokpasien',
//                DB::raw('(ply.hargajual*ply.jumlah) as   total'))
//            ->where('pd.statusenabled', true);
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('ply.tglpelayanan', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $data = $data->where('ply.tglpelayanan', '<=', $request['tglAkhir']);
//        }
//        if (isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
//            $data = $data->where('rg.id', '=', $request['ruanganId']);
//        }
//        if (isset($request['dokter']) && $request['dokter'] != "" && $request['dokter'] != "undefined") {
//            $data = $data->Where('pgw.id', '=', $request['dokter'])	;
//        }
//        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
//            $data = $data->Where('klp.id', '=', $request['kelompokPasien'])	;
//        }
//        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
//            $data = $data->where('rg.objectdepartemenfk', '=', $request['idDept']);
//        }
//
//        $data =  $data ->get();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $ruanganId = '';
        $dokter = '';
        $idDept ='';
        $kelompokPasien= '';
        if (isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
            $ruanganId = ' and ru.id='.$request['ruanganId'];
        }
        if (isset($request['dokter']) && $request['dokter'] != "" && $request['dokter'] != "undefined") {
            $dokter = '  and pg.id='. $request['dokter']	;
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $kelompokPasien = ' and kps.id='. $request['kelompokPasien'];
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $idDept =' and ru.objectdepartemenfk='.$request['idDept'];
        }
        $data = DB::select(DB::raw("
            SELECT
                        pd.noregistrasi,pd.tglregistrasi,pp.tglpelayanan,ru.namaruangan,dpm.namadepartemen,	kps.kelompokpasien,		
                        prd.namaproduk,pg.namalengkap,ps.nocm,ps.namapasien,
                        (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                        0 ELSE pp.hargadiscount	END) ) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE pp.jasa	END	) AS harga,pp.jumlah,		
                       (((	CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                        0 ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS total
                    FROM pelayananpasien_t AS pp
                    JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                    JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk                    
                    JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                    JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    JOIN produk_m AS prd ON prd.id = pp.produkfk
                    LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                    LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                    LEFT JOIN pegawai_m AS pg ON pg.id = pd.objectpegawaifk
                    WHERE apd.kdprofile = $kdProfile AND pp.tglpelayanan BETWEEN '$tglAwal' AND '$tglAkhir'	AND pp.aturanpakai IS NULL	
                    AND pd.statusenabled = true
                    $ruanganId 
                    $dokter
                    $idDept 
                    $kelompokPasien
                
                UNION ALL
            
                  SELECT '-'  as noregistrasi,sp.tglstruk as tglregistrasi,sp.tglstruk as tglpelayanan,
                    ru.namaruangan,	dp.namadepartemen,	'Umum/Pribadi' AS kelompokpasien,prd.namaproduk,pg.namalengkap,
                    '-' AS nocm,  
                    UPPER(sp.namapasien_klien) AS namapasien,
                    (spd.hargasatuan - CASE	WHEN spd.hargadiscount IS NULL THEN	0 ELSE spd.hargadiscount
                    END	) + CASE WHEN spd.hargatambahan IS NULL THEN 0 ELSE spd.hargatambahan END AS harga,
                    spd.qtyproduk as jumlah, spd.qtyproduk * (spd.hargasatuan - CASE WHEN spd.hargadiscount IS NULL THEN 0	ELSE spd.hargadiscount
                    END	) + CASE WHEN spd.hargatambahan IS NULL THEN 0 ELSE spd.hargatambahan END AS total
                    FROM strukpelayanan_t AS sp
                    JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                    LEFT JOIN ruangan_m AS ru ON ru.id = sp.objectruanganfk
                    LEFT JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
                    LEFT JOIN pegawai_m AS pg ON pg.id = sp.objectpegawaipenerimafk
                    JOIN produk_m AS prd ON prd.id = spd.objectprodukfk
                    WHERE sp.kdprofile = $kdProfile AND sp.tglstruk BETWEEN '$tglAwal' 	AND '$tglAkhir'	
                          AND substring(sp.nostruk,1,2)='OB' AND sp.statusenabled = true
                    $ruanganId 
                    $dokter
                    $idDept 
                
                UNION ALL
                            
                SELECT
                    pd.noregistrasi,pd.tglregistrasi,	pp.tglpelayanan,	ru.namaruangan,	dpm.namadepartemen,	kps.kelompokpasien,		
                    prd.namaproduk,	pg.namalengkap,ps.nocm,ps.namapasien,
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0	ELSE pp.hargajual	END - CASE WHEN pp.hargadiscount IS NULL THEN
                      0	ELSE pp.hargadiscount	END) ) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS harga,
                     pp.jumlah,		
                    (((	CASE WHEN pp.hargajual IS NULL THEN	0	ELSE pp.hargajual		END - CASE WHEN pp.hargadiscount IS NULL THEN
                     0	ELSE pp.hargadiscount	END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS total
                    FROM
                        pelayananpasien_t AS pp
                    join strukresep_t AS sr ON sr.norec= pp.strukresepfk
                    JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                    JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                     JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                    JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
                    JOIN produk_m AS prd ON prd.id = pp.produkfk
                    
                    LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                    LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                    LEFT JOIN pegawai_m AS pg ON pg.id = sr.penulisresepfk
                    WHERE apd.kdprofile = $kdProfile AND 
                        pp.tglpelayanan BETWEEN '$tglAwal' 	AND '$tglAkhir'	AND pp.aturanpakai IS not NULL	
                    AND pd.statusenabled = true
                    $ruanganId 
                    $dokter
                    $idDept 
                    $kelompokPasien
            
           "));

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getDataLaporanOPDAdministrasi(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("SELECT x.grup,SUM(x.total) AS total,x.namadepartemen,'-' as sisa
                FROM (
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,                       
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         dept.namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN departemen_m as dept on dept.id = ru.objectdepartemenfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pro.id in (4040398,4040399,4040406)
                            AND pd.objectkelompokpasienlastfk NOT IN (2,10,8,4)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,                       
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         'BPJS Kesehatan' as namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pro.id in (4040398,4040399,4040406)
                            AND pd.objectkelompokpasienlastfk IN (2,10,4)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,                       
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         'Jamkesda' as namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pro.id in (4040398,4040399,4040406)
                            AND pd.objectkelompokpasienlastfk IN (8)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.02. : B. Pendapatan Pendidikan dan Pelatihan' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'DIKLAT' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in ('4041226','4041227','4041228','4041229','4041230','4041231','4041232','4041233','4041234','4041235','4041246','4041247','4041248','4041249','4041250','4041251','4041252','4041253',
                                           '4041254','4041255','4041256','4041257','4041258','4041259','4041260','4041261','4041262','4041263','4041264','4041265','4041266','4041267','4041268','4041269','4041270','4041271',
                                           '4041272','4041273','4041274','4041275','4041276','4041277','4041278','4041279','4041280','4041281','4041282','4041283','4041284','4041285')
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.02. : B. Pendapatan Pendidikan dan Pelatihan' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Jasa Ketatausahaan' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '2019-12-01 00:00' and '2019-12-10 23:59'
                            and pro.id in ('4041236','4041237','4041238','4041239','4041240',
                                           '4041241','4041242','4041243','4041244','4041245')
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.03. : C. Pendapatan Lain-lain' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Sewa Ambulance' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in (4041286,4041287,4041288,4041289,4041290,4041291,4041292,4041293,4041294)
                
                UNION ALL
                
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.03. : C. Pendapatan Lain-lain' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Sewa Kendaraan' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in (4041295)	
                
                ) as x
                GROUP BY x.grup,x.namadepartemen"));

        return $this->respond($data);
    }

    public function getDataLaporanPerRekening(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("SELECT x.grup,SUM(x.total) AS total,x.namadepartemen,'-' as sisa
                FROM (
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         dept.namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN departemen_m as dept on dept.id = ru.objectdepartemenfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
                      AND pd.objectkelompokpasienlastfk NOT IN (2,10,8,4)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         'BPJS Kesehatan' as namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
                      AND pd.objectkelompokpasienlastfk IN (2,10,4)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         'Jamkesda' as namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
                      AND pd.objectkelompokpasienlastfk IN (8)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.02. : B. Pendapatan Pendidikan dan Pelatihan' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'DIKLAT' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in ('4041226','4041227','4041228','4041229','4041230','4041231','4041232','4041233','4041234','4041235','4041246','4041247','4041248','4041249','4041250','4041251','4041252','4041253',
                                           '4041254','4041255','4041256','4041257','4041258','4041259','4041260','4041261','4041262','4041263','4041264','4041265','4041266','4041267','4041268','4041269','4041270','4041271',
                                           '4041272','4041273','4041274','4041275','4041276','4041277','4041278','4041279','4041280','4041281','4041282','4041283','4041284','4041285')
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.02. : B. Pendapatan Pendidikan dan Pelatihan' as grup,
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Jasa Ketatausahaan' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '2019-12-01 00:00' and '2019-12-10 23:59'
                            and pro.id in ('4041236','4041237','4041238','4041239','4041240',
                                           '4041241','4041242','4041243','4041244','4041245')
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.03. : C. Pendapatan Lain-lain' as grup,                      
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Sewa Ambulance' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in (4041286,4041287,4041288,4041289,4041290,4041291,4041292,4041293,4041294)
                
                UNION ALL
                
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.03. : C. Pendapatan Lain-lain' as grup,                        
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Sewa Kendaraan' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in (4041295)	
                
                ) as x
                GROUP BY x.grup,x.namadepartemen"));

        return $this->respond($data);
    }

    public function getDataLaporanOPDFungsional(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("SELECT x.grup,SUM(x.total) AS total,x.namadepartemen,'-' as sisa
                FROM (
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         dept.namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN departemen_m as dept on dept.id = ru.objectdepartemenfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pro.id NOT IN (4040398,4040399,4040406)
                            AND pd.objectkelompokpasienlastfk NOT IN (2,10,8,4)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         'BPJS Kesehatan' as namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pro.id NOT IN (4040398,4040399,4040406)
                            AND pd.objectkelompokpasienlastfk IN (2,10,4)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.01. : A. Pendapatan Pelayanan Kesehatan' as grup,
                         ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
                     ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
                         'Jamkesda' as namadepartemen
                from pelayananpasien_t as pp
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN produk_m as pro on pro.id = pp.produkfk 
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pro.id NOT IN (4040398,4040399,4040406)
                            AND pd.objectkelompokpasienlastfk IN (8)
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.02. : B. Pendapatan Pendidikan dan Pelatihan' as grup,
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'DIKLAT' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in ('4041226','4041227','4041228','4041229','4041230','4041231','4041232','4041233','4041234','4041235','4041246','4041247','4041248','4041249','4041250','4041251','4041252','4041253',
                                           '4041254','4041255','4041256','4041257','4041258','4041259','4041260','4041261','4041262','4041263','4041264','4041265','4041266','4041267','4041268','4041269','4041270','4041271',
                                           '4041272','4041273','4041274','4041275','4041276','4041277','4041278','4041279','4041280','4041281','4041282','4041283','4041284','4041285')
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.02. : B. Pendapatan Pendidikan dan Pelatihan' as grup,
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Jasa Ketatausahaan' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '2019-12-01 00:00' and '2019-12-10 23:59'
                            and pro.id in ('4041236','4041237','4041238','4041239','4041240',
                                           '4041241','4041242','4041243','4041244','4041245')
                
                UNION ALL
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.03. :  C. Pendapatan Lain-lain' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Sewa Ambulance' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in (4041286,4041287,4041288,4041289,4041290,4041291,4041292,4041293,4041294)
                
                UNION ALL
                
                
                SELECT '1.02.1.02.07.00.00.4.1.4.16.01.03. : C. Pendapatan Lain-lain' as grup,                       
                         ((spd.hargasatuan-(CASE WHEN spd.hargadiscount IS NULL THEN  0 
                                ELSE spd.hargadiscount END))*spd.qtyproduk)+CASE WHEN spd.hargatambahan is null then 0 else spd.hargatambahan end as total,
                         'Sewa Kendaraan' as namadepartemen
                from strukpelayanan_t AS sp
                INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                INNER JOIN produk_m as pro on pro.id = spd.objectprodukfk 
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
                where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and pro.id in (4041295)	
                
                ) as x
                GROUP BY x.grup,x.namadepartemen"));

        return $this->respond($data);
    }
    public function getDataLaporanPenerimaanMingguan(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("
            select y.kelompokpenerimaan,y.penerimaankasir,
            sum(c1) as c1,
            sum(c2) as c2,
            sum(c3) as c3,
            sum(c4) as c4,
            sum(c5) as c5,
            sum(c6) as c6,
            sum(c7) as c7,
            sum(c8) as c8,
            sum(c9) as c9,
            sum(c10) as c10,
            sum(c11) as c11,
            sum(c12) as c12,
            sum(c13) as c13,
            sum(c14) as c14,
            sum(c15) as c15,
            sum(c16) as c16,
            sum(c17) as c17,
            sum(c18) as c18,
            sum(c19) as c19,
            sum(c20) as c20,
            sum(c21) as c21,
            sum(c22) as c22,
            sum(c23) as c23,
            sum(c24) as c24,
            sum(c25) as c25,
            sum(c26) as c26,
            sum(c27) as c27,
            sum(c28) as c28,
            sum(c29) as c29,
            sum(c30) as c30,
            sum(c31) as c31
            from
            (select ru.kelompokpenerimaan,ru.penerimaankasir,
            case when x.tgl='01' then sum(x.total) else 0 end as c1,
            case when x.tgl='02' then sum(x.total) else 0 end as c2,
            case when x.tgl='03' then sum(x.total) else 0 end as c3,
            case when x.tgl='04' then sum(x.total) else 0 end as c4,
            case when x.tgl='05' then sum(x.total) else 0 end as c5,
            case when x.tgl='06' then sum(x.total) else 0 end as c6,
            case when x.tgl='07' then sum(x.total) else 0 end as c7,
            case when x.tgl='08' then sum(x.total) else 0 end as c8,
            case when x.tgl='09' then sum(x.total) else 0 end as c9,
            case when x.tgl='10' then sum(x.total) else 0 end as c10,
            case when x.tgl='11' then sum(x.total) else 0 end as c11,
            case when x.tgl='12' then sum(x.total) else 0 end as c12,
            case when x.tgl='13' then sum(x.total) else 0 end as c13,
            case when x.tgl='14' then sum(x.total) else 0 end as c14,
            case when x.tgl='15' then sum(x.total) else 0 end as c15,
            case when x.tgl='16' then sum(x.total) else 0 end as c16,
            case when x.tgl='17' then sum(x.total) else 0 end as c17,
            case when x.tgl='18' then sum(x.total) else 0 end as c18,
            case when x.tgl='19' then sum(x.total) else 0 end as c19,
            case when x.tgl='20' then sum(x.total) else 0 end as c20,
            case when x.tgl='21' then sum(x.total) else 0 end as c21,
            case when x.tgl='22' then sum(x.total) else 0 end as c22,
            case when x.tgl='23' then sum(x.total) else 0 end as c23,
            case when x.tgl='24' then sum(x.total) else 0 end as c24,
            case when x.tgl='25' then sum(x.total) else 0 end as c25,
            case when x.tgl='26' then sum(x.total) else 0 end as c26,
            case when x.tgl='27' then sum(x.total) else 0 end as c27,
            case when x.tgl='28' then sum(x.total) else 0 end as c28,
            case when x.tgl='29' then sum(x.total) else 0 end as c29,
            case when x.tgl='30' then sum(x.total) else 0 end as c30,
            case when x.tgl='31' then sum(x.total) else 0 end as c31
                             from ruangan_m as ru 
                             left JOIN (
            
                                            select ru.id,ru.namaruangan,ru.penerimaankasir,((pp.hargasatuan-(case when pp.hargadiscount is null then 0 
                                            else pp.hargadiscount end))*pp.jumlah)+CASE when pp.jasa is null then 0 else pp.jasa end as total,
            to_char(pp.tglpelayanan,'dd')as tgl
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                            where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pd.objectkelompokpasienlastfk=1
                            
                            UNION ALL
                            
                            select ru.id,ru.namaruangan,ru.penerimaankasir,((pp.hargasatuan-
                                         (case when pp.hargadiscount is null then 0 else pp.hargadiscount end))*pp.jumlah)
                                         +CASE when pp.jasa is null then 0 else pp.jasa end as total,
            to_char(sr.tglresep,'dd')as tgl
                            from pelayananpasien_t as pp
                            INNER JOIN strukresep_t as sr on sr.norec=pp.strukresepfk
                            INNER JOIN ruangan_m as ru on ru.id=sr.ruanganfk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                            where sr.tglresep between '$tglAwal' and '$tglAkhir'
                            
                            UNION ALL
                             
                            select 599 as id,'bpjs' as namaruangan,'20. BPJS Kesehatan' as penerimaankasir,sp.totalprekanan as total,
            to_char(sp.tglstruk,'dd')as tgl
                            from  strukpelayanan_t as sp 
                            INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                            where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and sp.totalprekanan <> 0 and pd.objectkelompokpasienlastfk = 2
                            
                            UNION ALL
                            
                            select 600 as id,'Jamkesda' as namaruangan,'21. Jamkesda' as penerimaankasir,sp.totalprekanan as total,
            to_char(sp.tglstruk,'dd')as tgl
                            from  strukpelayanan_t as sp 
                            INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                            where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and sp.totalprekanan <> 0
                            and pd.objectkelompokpasienlastfk=8
                            ) as x on x.id=ru.id
                            WHERE ru.ipaddress is not null and ru.kelompokpenerimaan is not null
                            GROUP BY ru.kelompokpenerimaan,ru.penerimaankasir,x.tgl
            ) as y
            group by y.kelompokpenerimaan,y.penerimaankasir
            ORDER BY y.kelompokpenerimaan,y.penerimaankasir ASC

         "));

        return $this->respond($data);
    }
    public function getDataLaporanRekapHasilRetribusiDaerah(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("

        select y.subheader,y.penerimaankasir,y.kelompokpenerimaan_produk,y.penerimaan_produk,y.nopenerimaan,
        case when y.tgl ='01' then SUM(y.total) else 0 end AS c1,
        case when y.tgl ='02' then SUM(y.total) else 0 end AS c2,
        case when y.tgl ='03' then SUM(y.total) else 0 end AS c3,
        case when y.tgl ='04' then SUM(y.total) else 0 end AS c4,
        case when y.tgl ='05' then SUM(y.total) else 0 end AS c5,
        case when y.tgl ='06' then SUM(y.total) else 0 end AS c6,
        case when y.tgl ='07' then SUM(y.total) else 0 end AS c7,
        case when y.tgl ='08' then SUM(y.total) else 0 end AS c8,
        case when y.tgl ='09' then SUM(y.total) else 0 end AS c9,
        case when y.tgl ='10' then SUM(y.total) else 0 end AS c10,
        case when y.tgl ='11' then SUM(y.total) else 0 end AS c11,
        case when y.tgl ='12' then SUM(y.total) else 0 end AS c12
        from
        (SELECT x.subheader,x.penerimaankasir,x.kelompokpenerimaan_produk,x.penerimaan_produk,SUM(x.total) AS total,x.nopenerimaan,x.tgl
        FROM ruangan_m AS ru 
        LEFT JOIN (
        SELECT 'a. Rekam Medik' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040398,4040399,4040406) 
        
        UNION ALL
        
        SELECT 'b. Poli Spesialis Psikiatri' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040404,4040405,40411416,4040400,4040407) and ru.id in (564,565,566,572,572)
        
        UNION ALL
        
        SELECT 'c. Poli Spesialis Saraf' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406) 
        and ru.id in (558)
        
        UNION ALL
        
        SELECT 'd. Poli Spesialis PD' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406) 
        and ru.id in (559)
        
        UNION ALL
        
        SELECT 'e. Poli Spesialis K&K' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406) 
        and ru.id in (561)
        
        UNION ALL
        
        SELECT 'f. Poli Spesialis Anak' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406) 
        and ru.id in (560)
        
        UNION ALL
        
        SELECT 'g. Poli Spesialis Rehab Medik' as subheader,ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        pro.penerimaankasir as penerimaan_produk,pro.kelompokpenerimaan as kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id in (4040404,4040405,40411416,4040400,4040407,4040398,4040406) 
        and ru.id in (570,571,572,573)
        
        UNION ALL
        
        SELECT CASE WHEN ru.id = 558 THEN 'l. TM Penyakit Saraf' 
        WHEN ru.id = 559 THEN 'm. TM Penyakit Dalam' 
        WHEN ru.id = 561 THEN 'n. TM Penyakit Kulit & Kel' 
        WHEN ru.id = 560 THEN 'o. TM Penyakit ANAK' 
        WHEN pro.id = 4040575 THEN 'i. MMPI' 
        WHEN ru.id in (565,566,570,572) THEN 'j. Psikoterapi' 
        ELSE 'k. Tindakan' END AS subheader,
        ru.id,'A.1. Rawat Jalan' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        CASE WHEN ru.id = 558 THEN 'TM Penyakit Saraf' 
        WHEN ru.id = 559 THEN 'TM Penyakit Dalam' 
        WHEN ru.id = 561 THEN 'TM Penyakit Kulit & Kel' 
        WHEN ru.id = 560 THEN 'TM Penyakit ANAK' 
        WHEN pro.id = 4040575 THEN 'MMPI' 
        WHEN ru.id in (565,566,570,572) THEN 'Psikoterapi' 
        ELSE 'Tindakan' END AS penerimaan_produk,'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,1 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'		
        and pro.id not in (4040404,4040405,40411416,4040400,4040407)
        
        UNION ALL
        
        SELECT CASE WHEN pro.id in (4040398,4040399,4040406) THEN 'a. Rekam Medik IGD' 
        WHEN pro.id in (4040507,4041106,4041107,4041108,4041110,4041170,4041181,4041776) THEN 'b. Askep(Perawatan)' 
        WHEN pro.id in (4040404,4040405,40411416,4040400) THEN 'c. Pemeriksaan dr' 		 
        WHEN pro.id in (4040467,4040468,4040469,4040470,4040471,4040472,4040473) THEN 'e. AKOMODASI HCU/PICU/NICU' 		 
        ELSE 'd. Tindakan Medik IGD' END AS subheader,ru.id,'A.2. Instalasi Gawat Darurat' as penerimaankasir,
        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        CASE WHEN pro.id in (4040398,4040399,4040406) THEN 'Rekam Medik IGD'
        WHEN pro.id in (4040507,4041106,4041107,4041108,4041110,4041170,4041181,4041776) THEN 'Askep(Perawatan)' 		   
        WHEN pro.id in (4040404,4040405,40411416,4040400) THEN 'Pemeriksaan dr'
        WHEN pro.id in (4040467,4040471) THEN 'VVIP'
        WHEN pro.id in (4040468) THEN 'VIP'
        WHEN pro.id in (4040469,4040472) THEN 'I'
        WHEN pro.id in (4040470,4040473) THEN 'II'
        WHEN pro.id in (28079,28080) THEN 'III'
        ELSE 'Tindakan Medik IGD' END AS penerimaan_produk,'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,2 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        FROM pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'		
        and ru.id = 569
        
        UNION ALL
        
        SELECT CASE WHEN apd.objectkelasfk = 7 AND pro.id = 4041109 THEN 'VVIP Biasa' 
        WHEN apd.objectkelasfk = 4 AND pro.id = 4041109 THEN 'VIP Biasa' 
        WHEN apd.objectkelasfk = 3 AND pro.id = 4041109 THEN 'Kls I Biasa' 		 
        WHEN apd.objectkelasfk = 2 AND pro.id = 4041109 THEN 'Kls II Biasa'
        WHEN apd.objectkelasfk = 1 AND pro.id = 4041109 THEN 'Kls III Biasa'
        WHEN pro.id in (4040474,4040475) THEN 'Visite Dokter'
        WHEN pro.id in (4040411,4041749,4041750,4041751,4041752) Then 'Gelang Pasien'			
        ELSE 'Psikoterapi RI' END AS subheader,
        ru.id,'A.3. Rawat Inap' as penerimaankasir,((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 
        ELSE pp.hargadiscount END))*pp.jumlah)+CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        CASE WHEN apd.objectkelasfk = 7 AND pro.id = 4041109 THEN 'Intensif Care' 
        WHEN apd.objectkelasfk = 4 AND pro.id = 4041109 THEN 'Intensif Care' 
        WHEN apd.objectkelasfk = 3 AND pro.id = 4041109 THEN 'Intensif Care' 		 
        WHEN apd.objectkelasfk = 2 AND pro.id = 4041109 THEN 'Intensif Care'
        WHEN apd.objectkelasfk = 1 AND pro.id = 4041109 THEN 'Intensif Care'
        WHEN pro.id in (4040474,4040475) THEN 'Visite Dokter'
        WHEN pro.id in (4040411,4041749,4041750,4041751,4041752) Then 'Gelang Pasien'			
        ELSE 'Psikoterapi RI' END AS penerimaan_produk,'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,3 as nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        from pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
        and pro.id not in (4040404,4040405,40411416,4040400,4040407)						
        and ru.objectdepartemenfk = 16                                                        
        
        UNION ALL
        
        SELECT CASE WHEN ru.id = 564 THEN 'Instalasi Napza' 
        WHEN ru.id = 567 THEN 'Instalasi Psikogeriatri' 
        WHEN ru.id = 573 THEN 'Instalasi Kes Jiwa Anak Remaja (TKA)' 
        WHEN ru.id = 577 THEN 'Instalasi Fisioterapi'
        WHEN ru.id = 575 THEN 'Instalasi Laboratorium' 
        WHEN ru.id = 576 THEN 'Instalasi Radiologi'
        WHEN ru.id = 571 THEN 'Instalasi Elektromedik' 
        WHEN ru.id = 580 THEN 'Instalasi Rehab Mental'
        WHEN ru.id = 568 THEN 'Instalasi Gigi & Mulut' 
        WHEN ru.id = 574 THEN 'Instalasi Gizi' END AS subheader,ru.id,
        CASE WHEN ru.id = 564 THEN 'Instalasi Napza' 
        WHEN ru.id = 567 THEN 'A.5. Instalasi Psikogeriatri' 
        WHEN ru.id = 573 THEN 'A.6. Instalasi Kes Jiwa Anak Remaja (TKA)' 
        WHEN ru.id = 577 THEN 'A.7. Instalasi Fisioterapi'
        WHEN ru.id = 575 THEN 'A.8. Instalasi Laboratorium' 
        WHEN ru.id = 576 THEN 'A.9. Instalasi Radiologi'
        WHEN ru.id = 571 THEN 'A.10. Instalasi Elektromedik' 
        WHEN ru.id = 580 THEN 'A.11. Instalasi Rehab Mental'
        WHEN ru.id = 568 THEN 'A.12. Instalasi Gigi & Mulut' 
        WHEN ru.id = 574 THEN 'A.15. Instalasi Gizi' END AS penerimaankasir,
        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN  0 ELSE pp.hargadiscount END))*pp.jumlah)+
        CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        CASE WHEN ru.id = 564 THEN 'Instalasi Napza' 
        WHEN ru.id = 567 THEN 'Instalasi Psikogeriatri' 
        WHEN ru.id = 573 THEN 'Instalasi Kes Jiwa Anak Remaja (TKA)' 
        WHEN ru.id = 577 THEN 'Instalasi Fisioterapi'
        WHEN ru.id = 575 THEN 'Instalasi Laboratorium' 
        WHEN ru.id = 576 THEN 'Instalasi Radiologi'
        WHEN ru.id = 571 THEN 'Instalasi Elektromedik' 
        WHEN ru.id = 580 THEN 'Instalasi Rehab Mental'
        WHEN ru.id = 568 THEN 'Instalasi Gigi & Mulut' 
        WHEN ru.id = 574 THEN 'Instalasi Gizi' END AS penerimaan_produk,
        'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,
        CASE WHEN ru.id = 564 THEN 4
        WHEN ru.id = 567 THEN 5
        WHEN ru.id = 573 THEN 6
        WHEN ru.id = 577 THEN 7
        WHEN ru.id = 575 THEN 8
        WHEN ru.id = 576 THEN 9
        WHEN ru.id = 571 THEN 10
        WHEN ru.id = 580 THEN 11
        WHEN ru.id = 568 THEN 12
        WHEN ru.id = 574 THEN 15 END AS nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        FROM pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'					
        AND ru.objectdepartemenfk in (34,45,30,26,6,3,27,28) AND pro.id NOT IN (4040398,4040399,4040406)
        
        UNION ALL
        
        SELECT CASE WHEN ru1.id in (94,125) THEN 'R. Jalan'
        WHEN  ru1.id in (116) THEN 'R. Ranap' END AS subheader,ru.id,
        'A.13. Instalasi Farmasi' AS penerimaankasir,
        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END))*pp.jumlah)+
        CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        CASE WHEN ru1.id in (94,125) THEN 'R. Jalan'
        WHEN  ru1.id in (116) THEN 'R. Ranap' END AS penerimaan_produk,
        'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,13 AS nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        FROM pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN strukresep_t as sr on sr.norec = pp.strukresepfk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN ruangan_m as ru1 on ru1.id=sr.ruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'			
        AND pro.id NOT IN (4040398,4040399,4040406)
        
        UNION ALL
        
        SELECT 'Visite Apt' AS subheader,ru.id,
        'A.13. Instalasi Farmasi' AS penerimaankasir,
        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END))*pp.jumlah)+
        CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,'Visite Apt' AS penerimaan_produk,
        'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,13 AS nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        FROM pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk        
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk        
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'							
        AND pro.id = 4041191
        
        UNION ALL
        
        SELECT  CASE WHEN ru.objectdepartemenfk = 16 THEN 'R. Ranap' ELSE 'R.Jalan' END AS subheader,ru.id,
        'A.14. Instalasi Psikologi' AS penerimaankasir,
        ((pp.hargasatuan-(CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END))*pp.jumlah)+
        CASE WHEN pp.jasa is null then 0 else pp.jasa end as total,
        CASE WHEN ru.objectdepartemenfk = 16 THEN 'R. Ranap' ELSE 'R.Jalan' END AS penerimaan_produk,
        'A. PELAYANAN PASIEN' AS kelompokpenerimaan_produk,14 AS nopenerimaan,
        to_char(pp.tglpelayanan,'MM') as tgl
        FROM pelayananpasien_t as pp
        INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        INNER JOIN produk_m as pro on pro.id = pp.produkfk 
        INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
        INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec 
        where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'							
        AND pro.id not in (4040404,4040405,40411416,4040400,4040407) AND ru.id = 570) as x on x.id=ru.id
        WHERE ru.ipaddress is not null and ru.kelompokpenerimaan is not null
        and x.subheader is not null
        GROUP BY x.subheader,x.penerimaankasir,x.kelompokpenerimaan_produk,x.penerimaan_produk,x.nopenerimaan,x.tgl
        ) as y 
        GROUP BY y.subheader,y.penerimaankasir,y.kelompokpenerimaan_produk,y.penerimaan_produk,y.nopenerimaan,y.tgl
        ORDER BY y.subheader,y.penerimaankasir,y.kelompokpenerimaan_produk
       
       "));
        return $this->respond($data);
    }

    public function getDataLaporanTargetRealisasiPendapatan(Request $request){
        //todo : laporan penerimaan
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $data = \DB::select(DB::raw("
            select ru.kelompokpenerimaan,ru.penerimaankasir,sum(x.total)  as totalrealisai,0 as totaltarget,(sum(x.total)/100)*0 as persen
                             from ruangan_m as ru 
                             left JOIN (
            
                                            select ru.id,ru.namaruangan,ru.penerimaankasir,((pp.hargasatuan-(case when pp.hargadiscount is null then 0 
                                            else pp.hargadiscount end))*pp.jumlah)+CASE when pp.jasa is null then 0 else pp.jasa end as total,
            to_char(pp.tglpelayanan,'dd')as tgl
                            from pelayananpasien_t as pp
                            INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                            INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                            INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                            where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pd.objectkelompokpasienlastfk=1
                            
                            UNION ALL
                            
                            select ru.id,ru.namaruangan,ru.penerimaankasir,((pp.hargasatuan-
                                         (case when pp.hargadiscount is null then 0 else pp.hargadiscount end))*pp.jumlah)
                                         +CASE when pp.jasa is null then 0 else pp.jasa end as total,
            to_char(sr.tglresep,'dd')as tgl
                            from pelayananpasien_t as pp
                            INNER JOIN strukresep_t as sr on sr.norec=pp.strukresepfk
                            INNER JOIN ruangan_m as ru on ru.id=sr.ruanganfk
                            INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                            INNER JOIN strukbuktipenerimaan_t as sbm on sbm.nostrukfk=sp.norec
                            where sr.tglresep between '$tglAwal' and '$tglAkhir'
                            
                            UNION ALL
                             
                            select 599 as id,'bpjs' as namaruangan,'20. BPJS Kesehatan' as penerimaankasir,sp.totalprekanan as total,
            to_char(sp.tglstruk,'dd')as tgl
                            from  strukpelayanan_t as sp 
                            INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                            where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and sp.totalprekanan <> 0 and pd.objectkelompokpasienlastfk = 2
                            
                            UNION ALL
                            
                            select 600 as id,'Jamkesda' as namaruangan,'21. Jamkesda' as penerimaankasir,sp.totalprekanan as total,
            to_char(sp.tglstruk,'dd')as tgl
                            from  strukpelayanan_t as sp 
                            INNER JOIN pasiendaftar_t as pd on pd.norec=sp.noregistrasifk
                            where sp.tglstruk between '$tglAwal' and '$tglAkhir'
                            and sp.totalprekanan <> 0
                            and pd.objectkelompokpasienlastfk=8
                            ) as x on x.id=ru.id
                            WHERE ru.ipaddress is not null and ru.kelompokpenerimaan is not null
                            GROUP BY ru.kelompokpenerimaan,ru.penerimaankasir
            
            ORDER BY ru.kelompokpenerimaan,ru.penerimaankasir ASC

         "));

        return $this->respond($data);
    }

    public function getDataLaporanPendapatanDiklat(Request $request){
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
   $kdProfile = (int) $this->getDataKdProfile($request);

        $data = \DB::select(DB::raw("
            select sp.norec, 
                sp.tglstruk, 
                sp.namapasien_klien, 
                kt.reportdisplay as jenistagihan, 
                sp.totalharusdibayar, 
                keteranganlainnya, 
                sp.nosbklastfk, 
                sp.nosbmlastfk, 
                kt.id as jenisTagihanId,
                sp.totalharusdibayar * 39/100 as jasa 
                from strukpelayanan_t as sp 
                inner join kelompoktransaksi_m as kt on sp.objectkelompoktransaksifk = kt.id 
                where sp.totalharusdibayar is not null 
                and sp.objectkelompoktransaksifk in (2, 3, 4, 5, 6, 10, 13, 16, 34, 8, 9, 20, 21, 23, 24, 26, 62, 70, 71) 
                and sp.tglstruk >= '$tglAwal' 
                and sp.tglstruk <= '$tglAkhir' 
                and kt.id = 13 and sp.nosbmlastfk is not null 
                and sp.statusenabled = true
                and sp.kdprofile    =$kdProfile
                ORDER BY sp.tglstruk DESC

         "));

        return $this->respond($data);

    }

    public function getDataRekapDiklat(Request $request){
        $tahun = $request['tahun'];
  $kdProfile = (int) $this->getDataKdProfile($request);

        $data = \DB::select(DB::raw("
                SELECT x.jenistagihan,x.bulanku,SUM(x.totalharusdibayar) as total FROM (select sp.norec,sp.tglstruk, sp.namapasien_klien,kt.reportdisplay as jenistagihan,
							 sp.totalharusdibayar,sp.keteranganlainnya,sp.nosbklastfk,sp.nosbmlastfk, 
							 kt.id as jenisTagihanId, 
                CASE
                 WHEN date_part('month',sp.tglstruk) = 1 THEN 'Januari'
                WHEN date_part('month',sp.tglstruk) = 2 THEN 'Februari'
                WHEN date_part('month',sp.tglstruk) = 3 THEN 'Maret'
                WHEN date_part('month',sp.tglstruk) = 4 THEN 'April'
                WHEN date_part('month',sp.tglstruk) = 5 THEN 'Mei'
                WHEN date_part('month',sp.tglstruk) = 6 THEN 'Juni'
                WHEN date_part('month',sp.tglstruk) = 7 THEN 'Juli'
                WHEN date_part('month',sp.tglstruk) = 8 THEN 'Agustus'
                WHEN date_part('month',sp.tglstruk) = 9 THEN 'September'
                WHEN date_part('month',sp.tglstruk) = 10 THEN 'Oktober' 
                WHEN date_part('month',sp.tglstruk) = 11 THEN 'November' 
                WHEN date_part('month',sp.tglstruk) = 12 THEN 'Desember'
                END AS bulanku
                    from strukpelayanan_t as sp 
                    inner join kelompoktransaksi_m as kt on sp.objectkelompoktransaksifk = kt.id 
                    LEFT JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec	
                    LEFT JOIN produk_m as pro ON pro.id = spd.objectprodukfk 
                LEFT JOIN detailjenisproduk_m as djp on djp.id = pro.objectdetailjenisprodukfk
                    where sp.totalharusdibayar is not null 
                    and sp.objectkelompoktransaksifk in (2, 3, 4, 5, 6, 10, 13, 16, 34, 8, 9, 20, 21, 23, 24, 26, 62, 70, 71) 
                    and to_char(sp.tglstruk,'yyyy') >= '$tahun' 
                -- 	and sp.tglstruk <= '2019-11-30' 
                    and kt.id = 13 and sp.nosbmlastfk is not null and sp.statusenabled = true and sp.kdprofile  =$kdProfile ) as x
                GROUP BY x.jenistagihan,x.bulanku
                ORDER BY x.bulanku DESC

         "));
        return $this->respond($data);
    }

    function getDataLaporanPenerimaanSemuaKasir(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        
        $idKasir = '';
        $idRuangan = '';
        if (isset($request['idKasir']) && $request['idKasir'] != "" && $request['idKasir'] != "undefined") {
            $idKasir = 'AND p.id ='.$request['idKasir'];
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $idRuangan = 'AND p.id ='.$request['idRuangan'];
        }

        $data = \DB::select(DB::raw("
                    SELECT
                        p.namalengkap AS namapenerima,
                        sum(cast(sbm.totaldibayar AS float)) AS totalpenerimaan,
                        '' AS keterangan
                    FROM strukbuktipenerimaan_t AS sbm
                    INNER JOIN strukpelayanan_t AS sp ON sbm.nostrukfk = sp.norec
                    LEFT JOIN pasiendaftar_t AS pd ON sp.noregistrasifk = pd.norec
                    LEFT JOIN ruangan_m as ru ON ru.id=pd.objectruanganlastfk
                    LEFT JOIN loginuser_s AS lu ON lu.id = sbm.objectpegawaipenerimafk
                    LEFT JOIN pegawai_m AS p ON p.id = lu.objectpegawaifk
                    WHERE
                        sbm.kdprofile = $kdProfile
                        AND sbm.tglsbm >= '$tglAwal' 
                        AND sbm.tglsbm <= '$tglAkhir' 
                        $idKasir
                        $idRuangan
                    GROUP BY p.namalengkap"
                ));
        
        return $this->respond($data);
    }

    public function getLapJaspelRajalRanap(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dep = [];

//         $data = DB::table('spasiendaftar_t AS pd')
//             ->join('pasien_m AS ps','ps.id','=','pd.nocmfk')
//             ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
//             ->LEFTJOIN ('pelayananpasien_t AS pp','pp.noregistrasifk','=','apd.norec')
//             ->LEFTJOIN ('pelayananpasienpetugas_t AS ppp','ppp.pelayananpasien','=','pp.norec')
//             ->LEFTJOIN ('kelompokpasien_m AS kp','kp.id','=','pd.objectkelompokpasienlastfk')
//             ->LEFTJOIN ('produk_m as prd','prd.id','=','pp.produkfk')
//             ->LEFTJOIN ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
//             ->LEFTJOIN ('pegawai_m AS pg','pg.id','=','apd.objectpegawaifk')
//             ->LEFTJOIN ('pegawai_m AS pg2','pg2.id','=','pd.objectpegawaifk')
//             ->LEFTJOIN ('strukresep_t AS sr','sr.norec','=','pp.strukresepfk')
//             ->LEFTJOIN ('pegawai_m AS pg3','pg3.id','=','sr.penulisresepfk')
//             ->LEFTJOIN ('pegawai_m AS pg4','pg4.id','=','ppp.objectpegawaifk')
//             ->select(DB::raw("pd.noregistrasi,pd.tglregistrasi,ps.nocm,ps.namapasien,ru.namaruangan, pg4.namalengkap as dokter_rad,
//                 case when pp.strukresepfk is not null then pg3.namalengkap else pg.namalengkap end as dokter,
//                 pg2.namalengkap as dpjputama,kp.kelompokpasien as tipe,
//                 case when pp.strukresepfk is not  null then prd.namaproduk end as obat,
//                 case when pp.strukresepfk is not null then ((pp.hargasatuan - (COALESCE(pp.hargadiscount, 0))) * pp.jumlah) + COALESCE(pp.jasa, 0) else 0 end as totalobat,
//                 case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44) 
//                 and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631) then prd.namaproduk end as tindakan,
//                 case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44) 
//                 and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631) then pp.jumlah else 0 end as qtytindakan,
//                 case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44) 
//                 and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631) then pp.hargasatuan  else 0 end as totaltindakan,
//                 case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44) 
//                 and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631)then pp.norec end as norec_tindakan,
//                 case when apd.objectruanganfk in (575,39) then prd.namaproduk end as lab,
//                 case when apd.objectruanganfk in (575,39) then pp.jumlah end as qtylab,
//                 case when apd.objectruanganfk in (575,39) then pp.hargasatuan end as totallab,
//                 case when apd.objectruanganfk in (575,39) then pp.norec end as norec_lab,
//                 case when apd.objectruanganfk in (576) then prd.namaproduk end as radiologi,
//                 case when apd.objectruanganfk in (576) then pp.jumlah end as qtyradiologi,
//                 case when apd.objectruanganfk in (576) then pp.hargasatuan end as totalradiologi,
//                 case when apd.objectruanganfk in (576) then pp.norec end as norec_rad,
//                 case when pp.produkfk in ( 403531 ) then prd.namaproduk end as askep,
//                 case when pp.produkfk in ( 403531 ) then pp.jumlah end as qtyaskep,
//                 case when pp.produkfk in ( 403531 ) then pp.hargasatuan end as totalaskep,
//                 case when pp.produkfk in ( 403531 ) then pp.norec end as norec_askep,
//                 case when pp.produkfk in ( 0 ) then prd.namaproduk end as dokterumum,
//                 case when pp.produkfk in ( 0 ) then pp.jumlah end as qtydokterumum,
//                 case when pp.produkfk in ( 0 ) then pp.hargasatuan end as totaldokterumum,
//                 case when pp.produkfk in ( 0 ) then pp.norec end as norec_dokterumum,
//                 case when pp.produkfk in ( 0 ) then prd.namaproduk end as dokterspe,
//                 case when pp.produkfk in ( 0 ) then pp.jumlah end as qtydokterspe,
//                 case when pp.produkfk in ( 0 ) then pp.hargasatuan end as totaldokterspe,
//                 case when pp.produkfk in ( 0 ) then pp.norec end as norec_dokterspe,
//                 case when apd.objectruanganfk in (44) then prd.namaproduk end as operasi,
//                 case when apd.objectruanganfk in (44) then pp.jumlah end as qtyoperasi,
//                 case when apd.objectruanganfk in (44) then pp.hargasatuan end as totaloperasi,
//                 case when apd.objectruanganfk in (44) then pp.norec end as norec_operasi,
//                 case when pp.produkfk in ( 28434,28435,29539,29909 ) then prd.namaproduk end as oxygen,
//                 case when pp.produkfk in ( 28434,28435,29539,29909 ) then pp.jumlah end as qtyoxygen,
//                 case when pp.produkfk in ( 28434,28435,29539,29909 ) then pp.hargasatuan end as totaloxygen,
//                 case when pp.produkfk in ( 28434,28435,29539,29909 ) then pp.norec end as norec_oxygen,
//                 case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then prd.namaproduk end as ruang,
//                 case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then pp.jumlah end as qtyruang,
//                 case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then pp.hargasatuan end as totalruang,
//                 case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then pp.norec end as norec_ruang,
//                 case when pp.produkfk in ( 0 ) then prd.namaproduk end as adm,
//                 case when pp.produkfk in ( 0 ) then pp.jumlah end as qtyadm,
//                 case when pp.produkfk in ( 0 ) then pp.hargasatuan end as totaladm,
//                 case when pp.produkfk in ( 0 ) then pp.norec end as norec_adm,
//                  null as jp_tindakan, null as js_tindakan, 
//                  null as jp_rad, null as js_rad,
//                  null as jp_lab, null as js_lab,
//                  null as jp_askep, null as js_askep,
//                  null as jp_dokterumum, null as js_dokterumum,
//                  null as jp_dokterspe, null as js_dokterspe,
//                  null as jp_operasi, null as js_operasi,
//                  null as jp_oxygen, null as js_oxygen,
//                  null as jp_ruang, null as js_ruang,
//                  null as jp_adm, null as js_adm,
//                  ((pp.hargasatuan - (COALESCE (pp.hargadiscount, 0))) * pp.jumlah)
//                  + COALESCE (pp.jasa, 0) AS total_all
             
//                 "))
// //            ->whereNotNull('pp.strukresepfk')
//             ->where('pd.kdprofile',$kdProfile);

        $tglAwal = '';
        $tglAkhir = '';
        $idDept = '';
        $idRuangan = '';
        $idKelPasien='';
        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $tglAwal = " and pd.tglregistrasi >='".$request['tglAwal']."'";
        };
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tglAkhir = " and pd.tglregistrasi <='".$request['tglAkhir']."'";
        };
        if(isset($request['idDept']) && $request['idDept']!="" && $request['idDept']!="undefined"){
            $idDept = ' and ru.objectdepartemenfk = '.$request['idDept'];
        }
        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $idRuangan = ' and ru.id = '.$request['idRuangan'];
        }
        if(isset($request['kelPasien']) && $request['kelPasien'] != "" && $request['kelPasien'] != "undefined") {
            $idKelPasien = ' and pd.objectkelompokpasienlastfk = ' . $request['kelPasien'];
        }
//        $data = $data->where('pd.noregistrasi','2010005564');
        // $data = $data->orderby('pd.noregistrasi');
        // $data = $data->get();
//        return $this->respond($data);
        $data = DB::select(DB::raw("select pd.noregistrasi,pd.tglregistrasi,ps.nocm,ps.namapasien,ru.namaruangan, pg4.namalengkap as dokter_pemeriksa, pa.nosep,
        case when pp.strukresepfk is not null then pg3.namalengkap else pg.namalengkap end as dokter,pp.tglpelayanan,ru2.namaruangan as ruanganasal,
        pg2.namalengkap as dpjputama,kp.kelompokpasien as tipe,
        case when pp.strukresepfk is not null then prd.namaproduk end as obat,
        case when pp.strukresepfk is not null then ((pp.hargasatuan - (COALESCE(pp.hargadiscount, 0))) * pp.jumlah) + COALESCE(pp.jasa, 0) else 0 end as totalobat,
        case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44)
        and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,
        33653,33654,33655,33649,33625,33650,33631,29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 
        1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885,29544,33665, 29549, 33639, 33638, 33640, 
        33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650,33740,4038359,29612,33662,33663,33671,33673,33672,33645,33646) then prd.namaproduk end as tindakan,
        case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44)
        and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,
        33653,33654,33655,33649,33625,33650,33631,29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 
        1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885,29544,33665, 29549, 33639, 33638, 33640, 
        33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650,33740,4038359,29612,33662,33663,33671,33673,33672,33645,33646) then pp.jumlah else 0 end as qtytindakan,
        case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44)
        and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,
        33653,33654,33655,33649,33625,33650,33631,29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 
        1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885,29544,33665, 29549, 33639, 33638, 33640, 
        33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650,33740,4038359,29612,33662,33663,33671,33673,33672,33645,33646) then pp.hargasatuan else 0 end as totaltindakan,
        case when pp.strukresepfk is null and apd.objectruanganfk not in(575,39,576,44)
        and pp.produkfk not in (403531,28434,28435,29539,29909,33652,33651,33634,33633,33632,33636,33637,
        33653,33654,33655,33649,33625,33650,33631,29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 
        1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885,29544,33665, 29549, 33639, 33638, 33640, 
        33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650,33740,4038359,29612,33662,33663,33671,33673,33672,33645,33646) then pp.norec end as norec_tindakan,
        case when apd.objectruanganfk in (575,39) then prd.namaproduk end as lab,
        case when apd.objectruanganfk in (575,39) then pp.jumlah end as qtylab,
        case when apd.objectruanganfk in (575,39) then pp.hargasatuan end as totallab,
        case when apd.objectruanganfk in (575,39) then pp.norec end as norec_lab,
        case when apd.objectruanganfk in (576) then prd.namaproduk end as radiologi,
        case when apd.objectruanganfk in (576) then pp.jumlah end as qtyradiologi,
        case when apd.objectruanganfk in (576) then pp.hargasatuan end as totalradiologi,
        case when apd.objectruanganfk in (576) then pp.norec end as norec_rad,
        case when pp.produkfk in ( 1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885 ) then prd.namaproduk end as pendaftaran,
        case when pp.produkfk in ( 1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885 ) then pp.jumlah end as qty_pendaftaran,
        case when pp.produkfk in ( 1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885 ) then pp.hargasatuan end as total_pendaftaran,
        case when pp.produkfk in ( 1002121482,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,33625,5001885 ) then pp.norec end as norec_pendaftaran,
        case when pp.produkfk in ( 29544,33665, 29549, 33639, 33638, 33640, 33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650, 403531) then prd.namaproduk end as askep,
        case when pp.produkfk in ( 29544,33665, 29549, 33639, 33638, 33640, 33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650, 403531) then pp.jumlah end as qtyaskep,
        case when pp.produkfk in ( 29544,33665, 29549, 33639, 33638, 33640, 33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650, 403531) then pp.hargasatuan end as totalaskep,
        case when pp.produkfk in ( 29544,33665, 29549, 33639, 33638, 33640, 33641, 32362, 30151, 32361, 5001885, 33630, 1002121482, 28343, 31206, 31867, 31207, 30650, 403531) then pp.norec end as norec_askep,
        case when pp.produkfk in ( 33740,29612,33662,33663,33671,33673,33672,33645,33646 ) then prd.namaproduk end as dokterumum,
        case when pp.produkfk in ( 33740,29612,33662,33663,33671,33673,33672,33645,33646 ) then pp.jumlah end as qtydokterumum,
        case when pp.produkfk in ( 33740,29612,33662,33663,33671,33673,33672,33645,33646 ) then pp.hargasatuan end as totaldokterumum,
        case when pp.produkfk in ( 33740,29612,33662,33663,33671,33673,33672,33645,33646 ) then pp.norec end as norec_dokterumum,
        case when pp.produkfk in ( 29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 4038359 ) then prd.namaproduk end as dokterspe,
        case when pp.produkfk in ( 29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 4038359 ) then pp.jumlah end as qtydokterspe,
        case when pp.produkfk in ( 29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 4038359 ) then pp.hargasatuan end as totaldokterspe,
        case when pp.produkfk in ( 29596,33656,33658,33657,33669,33674,33670,33647,33648,33659,33661,33660,33667,33666,33668, 4038359 ) then pp.norec end as norec_dokterspe,
        case when apd.objectruanganfk in (44) and pp.strukresepfk is null then prd.namaproduk end as operasi,
        case when apd.objectruanganfk in (44) and pp.strukresepfk is null then pg4.namalengkap end as dokteroperasi,
        case when apd.objectruanganfk in (44) and pp.strukresepfk is null then pp.jumlah end as qtyoperasi,
        case when apd.objectruanganfk in (44) and pp.strukresepfk is null then cast(pp.hargasatuan as float) end as totaloperasi,
        case when apd.objectruanganfk in (44) and pp.strukresepfk is null then pp.norec end as norec_operasi,
        case when pp.produkfk in ( 28434,28435,29539,29909 ) then prd.namaproduk end as oxygen,
        case when pp.produkfk in ( 28434,28435,29539,29909 ) then pp.jumlah end as qtyoxygen,
        case when pp.produkfk in ( 28434,28435,29539,29909 ) then pp.hargasatuan end as totaloxygen,
        case when pp.produkfk in ( 28434,28435,29539,29909 ) then pp.norec end as norec_oxygen,
        case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then prd.namaproduk end as ruang,
        case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then pp.jumlah end as qtyruang,
        case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then pp.hargasatuan end as totalruang,
        case when pp.produkfk in ( 33652,33651,33634,33633,33632,33636,33637,33653,33654,33655,33649,33650,33631 ) then pp.norec end as norec_ruang,
        case when pp.produkfk in ( 0 ) then prd.namaproduk end as adm,
        case when pp.produkfk in ( 0 ) then pp.jumlah end as qtyadm,
        case when pp.produkfk in ( 0 ) then pp.hargasatuan end as totaladm,
        case when pp.produkfk in ( 0 ) then pp.norec end as norec_adm,
        null as jp_tindakan, null as js_tindakan,
        null as jp_rad, null as js_rad,
        null as jp_lab, null as js_lab,
        null as jp_askep, null as js_askep,
        null as jp_dokterumum, null as js_dokterumum,
        null as jp_dokterspe, null as js_dokterspe,
        null as jp_operasi, null as js_operasi,
        null as jp_oxygen, null as js_oxygen,
        null as jp_ruang, null as js_ruang,
        null as jp_adm, null as js_adm,
        null as jp_pendaftaran, null as js_pendaftaran,
        ((pp.hargasatuan - (COALESCE (pp.hargadiscount, 0))) * pp.jumlah)
        + COALESCE (pp.jasa, 0) AS total_all
        from pasiendaftar_t as pd
        inner join pasien_m as ps on ps.id = pd.nocmfk
        inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
        left join pelayananpasien_t as pp on pp.noregistrasifk = apd.norec 
        left join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec and ppp.objectjenispetugaspefk = 4
        left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk 
        left join produk_m as prd on prd.id = pp.produkfk 
        left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
        left join ruangan_m as ru2 on ru2.id = apd.objectruanganfk 
        left join pegawai_m as pg on pg.id = apd.objectpegawaifk 
        left join pegawai_m as pg2 on pg2.id = pd.objectpegawaifk 
        left join strukresep_t as sr on sr.norec = pp.strukresepfk 
        left join pegawai_m as pg3 on pg3.id = sr.penulisresepfk 
        left join pegawai_m as pg4 on pg4.id = ppp.objectpegawaifk 
        left join pemakaianasuransi_t as pa on pa.noregistrasifk = pd.norec
        where pd.kdprofile = $kdProfile
        $tglAwal
        $tglAkhir
        $idDept
        $idRuangan
        $idKelPasien
        and pd.tglpulang is not null
        and pd.statusenabled = true
        order by pp.tglpelayanan asc;"));

        $norecPP = '';
        $no = 1;
        foreach ($data as $ob){
            $ob->no = $no++;
            $ob->noregistrasi = (float) $ob->noregistrasi;
            $ob->nocm = (float) $ob->nocm;
            $ob->total_all = (float) $ob->total_all;
            $ob->totalobat = (float) $ob->totalobat;
            $ob->qtytindakan = (float) $ob->qtytindakan;
            $ob->totaltindakan = (float) $ob->totaltindakan * $ob->qtytindakan;
            $ob->qtylab = (float) $ob->qtylab;
            $ob->totallab = (float) $ob->totallab * $ob->qtylab;
            $ob->qtyradiologi = (float) $ob->qtyradiologi;
            $ob->totalradiologi = (float) $ob->totalradiologi * $ob->qtyradiologi;
            $ob->qty_pendaftaran = (float) $ob->qty_pendaftaran;
            $ob->total_pendaftaran = (float) $ob->total_pendaftaran * $ob->qty_pendaftaran;
            $ob->qtyaskep = (float) $ob->qtyaskep;
            $ob->totalaskep = (float) $ob->totalaskep * $ob->qtyaskep;
            $ob->qtydokterumum = (float) $ob->qtydokterumum;
            $ob->totaldokterumum = (float) $ob->totaldokterumum * $ob->qtydokterumum;
            $ob->qtydokterspe = (float) $ob->qtydokterspe;
            $ob->totaldokterspe = (float) $ob->totaldokterspe * $ob->qtydokterspe;
            $ob->qtyoperasi = (float) $ob->qtyoperasi;
            $ob->totaloperasi = (float) $ob->totaloperasi * $ob->qtyoperasi;
            $ob->qtyoxygen = (float) $ob->qtyoxygen;
            $ob->totaloxygen = (float) $ob->totaloxygen * $ob->qtyoxygen;
            $ob->qtyruang = (float) $ob->qtyruang;
            $ob->totalruang = (float) $ob->totalruang * $ob->qtyruang;
            $ob->qtyadm = (float) $ob->qtyadm;
            $ob->totaladm = (float) $ob->totaladm * $ob->qtyadm;


            if (empty($ob->nosep)) {
                $ob->nosep = '';
            }
            if (!empty($ob->radiologi)) {
                if (empty($ob->dokter_pemeriksa)) {
                    $ob->dokter_pemeriksa = ' - ';
                }
                $ob->radiologi .= ' ( '.$ob->dokter_pemeriksa.' )';
            }
            if (!empty($ob->tindakan)) {
                if (empty($ob->dokter_pemeriksa)) {
                    $ob->dokter_pemeriksa = ' - ';
                }
                $ob->tindakan .= ' ( '.$ob->dokter_pemeriksa.' )';
            }
            if (!empty($ob->dokterspe)) {
                if (empty($ob->dokter_pemeriksa)) {
                    $ob->dokter_pemeriksa = ' - ';
                }
                $ob->dokterspe =$ob->dokter_pemeriksa;
            }
            // if (!empty($ob->operasi)) {
            //     if (empty($ob->dokter_pemeriksa)) {
            //         $ob->dokter_pemeriksa = ' - ';
            //     }
            //     $ob->operasi .= ' ( '.$ob->dokter_pemeriksa.' )';
            // }
            if($ob->norec_tindakan != null){
                $norecPP = $norecPP.",'".$ob->norec_tindakan . "'";
            }
            if($ob->norec_lab != null){
                $norecPP = $norecPP.",'".$ob->norec_lab . "'";
            }
            if($ob->norec_rad != null){
                $norecPP = $norecPP.",'".$ob->norec_rad . "'";
            }
            if($ob->norec_askep != null){
                $norecPP = $norecPP.",'".$ob->norec_askep . "'";
            }
            if($ob->norec_dokterumum != null){
                $norecPP = $norecPP.",'".$ob->norec_dokterumum . "'";
            }
            if($ob->norec_dokterspe != null){
                $norecPP = $norecPP.",'".$ob->norec_dokterspe . "'";
            }
            if($ob->norec_operasi != null){
                $norecPP = $norecPP.",'".$ob->norec_operasi . "'";
            }
            if($ob->norec_oxygen != null){
                $norecPP = $norecPP.",'".$ob->norec_oxygen . "'";
            }
            if($ob->norec_ruang != null){
                $norecPP = $norecPP.",'".$ob->norec_ruang . "'";
            }
            if($ob->norec_adm != null){
                $norecPP = $norecPP.",'".$ob->norec_adm . "'";
            }
            if($ob->norec_pendaftaran != null){
                $norecPP = $norecPP.",'".$ob->norec_pendaftaran . "'";
            }
        }
        $norecPP = substr($norecPP, 1, strlen($norecPP)-1);
//        return $this->respond($norecPd);
//        return $norecPd;
        $detail=[];
        if($norecPP !=''){
            $detail = DB::select(DB::raw("select ppd.hargasatuan,kom.komponenharga,
                ppd.pelayananpasien as norec_pp,ppd.komponenhargafk
                from pelayananpasiendetail_t as ppd
                join komponenharga_m as kom on kom.id=ppd.komponenhargafk
                where ppd.pelayananpasien in ($norecPP)
             "));
        }

        $i = 0;
        foreach ($data  as $h){
            foreach ($detail as $d){
                //tindakan
                if($h->norec_tindakan != null && $h->norec_tindakan == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_tindakan = (float) $d->hargasatuan * $h->qtytindakan; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_tindakan = (float) $d->hargasatuan * $h->qtytindakan; }
                }
                //lab
                if($h->norec_lab != null && $h->norec_lab == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_lab = (float) $d->hargasatuan * $h->qtylab; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_lab = (float) $d->hargasatuan * $h->qtylab; }
                }
                //rad
                if($h->norec_rad != null && $h->norec_rad == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_rad = (float) $d->hargasatuan * $h->qtyradiologi; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_rad = (float) $d->hargasatuan * $h->qtyradiologi; }
                }
                //askep
                if($h->norec_askep != null && $h->norec_askep == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_askep = (float) $d->hargasatuan * $h->qtyaskep; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_askep = (float) $d->hargasatuan * $h->qtyaskep; }
                }
                //dokterumum
                if($h->norec_dokterumum != null && $h->norec_dokterumum == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_dokterumum = (float) $d->hargasatuan * $h->qtydokterumum; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_dokterumum = (float) $d->hargasatuan * $h->qtydokterumum; }
                }
                //norec_dokterspe
                if($h->norec_dokterspe != null && $h->norec_dokterspe == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_dokterspe = (float) $d->hargasatuan * $h->qtydokterspe; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_dokterspe = (float) $d->hargasatuan * $h->qtydokterspe; }
                }
                //norec_operasi
                if($h->norec_operasi != null && $h->norec_operasi == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_operasi = (float) $d->hargasatuan * $h->qtyoperasi; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_operasi = (float) $d->hargasatuan * $h->qtyoperasi; }
                }
                //norec_oxygen
                if($h->norec_oxygen != null && $h->norec_oxygen == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_oxygen = (float) $d->hargasatuan * $h->qtyoxygen; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_oxygen = (float) $d->hargasatuan * $h->qtyoxygen; }
                }
                //norec_ruang
                if($h->norec_ruang != null && $h->norec_ruang == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_ruang = (float) $d->hargasatuan * $h->qtyruang; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_ruang = (float) $d->hargasatuan * $h->qtyruang; }
                }
                //admin
                if($h->norec_adm != null && $h->norec_adm == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_adm = (float) $d->hargasatuan * $h->qtyadm; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_adm = (float) $d->hargasatuan * $h->qtyadm; }
                }
                //pendaftaran
                if($h->norec_pendaftaran != null && $h->norec_pendaftaran == $d->norec_pp ){
                    if($d->komponenhargafk == 93){ $data[$i]->js_pendaftaran = (float) $d->hargasatuan * $h->qty_pendaftaran; }
                    if($d->komponenhargafk == 94){ $data[$i]->jp_pendaftaran = (float) $d->hargasatuan * $h->qty_pendaftaran; }
                }
            }
            $i++;
        }

        $res['data'] = $data;
        $res['as'] = 'ramdanegie';
        return $this->respond($res);
    }

    public function getLaporanDetailPenerimaanKasir(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $Kronis = "";
        $noreg = '';
        if (isset($request['noReg']) && $request['noReg'] != "" && $request['noReg'] != "undefined") {
            $noreg = " AND pd.noregistrasi ILIKE '%" .  $request['noReg']."%'";
        }
        $norm = '';
        if (isset($request['noRm']) && $request['noRm'] != "" && $request['noRm'] != "undefined") {
            $norm = " AND pm.nocm ILIKE '%" .  $request['noRm']."%'";
        }
        $namaPasien = '';
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $request['namaPasien'] =  str_replace("'", "",   $request['namaPasien']);
            $namaPasien = "  AND replace(pm.namapasien,'''','') ILIKE '%".$request['namaPasien']."%'";
        }
        $ruId='';
        if(isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
            $ruId = ' AND ru.id = ' . $request['ruanganId'];
        }
        $deptId='';
        if(isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $deptId = ' AND ru.objectdepartemenfk = ' . $request['idDept'];
        }
        $idKelPasien='';
        if(isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $idKelPasien = ' AND pd.objectkelompokpasienlastfk = ' . $request['kelompokPasien'];
        }
        $paramKasir = '';
        if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
            $arrRuang = explode(',',$request['KasirArr']) ;
            $kodeRuang = [];
            $str = '';
            $d=0;
            foreach ( $arrRuang as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramKasir = " AND pg.id IN ($str)";
//            return $this->respond($paramKasir);
        }
        $paramKelLayanan='';
        if(isset($request['KelLayanan']) && $request['KelLayanan'] != "" && $request['KelLayanan'] != "undefined") {
            if ($request['KelLayanan'] == 1){
                $paramKelLayanan = ' AND pp.produkfk IN (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,5001885) AND pp.strukresepfk IS NULL ';
//                $Kronis = " Limit 0";
            }elseif ($request['KelLayanan'] == 2){
                $paramKelLayanan = ' AND pp.produkfk NOT IN (33625,28343,30111,30110,30168,30650,31206,31207,32361,32362,33630,30151,5001885) AND pp.strukresepfk IS NULL ';
//                $Kronis = " Limit 0";
            }elseif ($request['KelLayanan'] == 3){
                $paramKelLayanan = ' AND pp.strukresepfk IS NOT NULL ';
            }

        }


        $data = \DB::select(DB::raw("
                SELECT pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,ru.namaruangan,kp.kelompokpasien,
                       pp.tglpelayanan,pp.produkfk,pr.namaproduk,			 
                       CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END AS harga,
                       CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END AS diskon,
                       CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END AS jasa,pp.jumlah,		
                       (((CASE WHEN pp.hargajual IS NULL THEN	0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
                       0 ELSE pp.hargadiscount END) * pp.jumlah) + CASE WHEN pp.jasa IS NULL THEN	0 ELSE	pp.jasa	END	) AS total,
                       pd.tglpulang,sp.nostruk as nomorverif,sbm.nosbm,pg.namalengkap as kasir
                FROM pelayananpasien_t AS pp  
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk 
                INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                INNER JOIN pasien_m AS pm ON pm.id = pd.nocmfk
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk  
                INNER JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk  
                LEFT JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = sp.norec AND sbm.statusenabled = true  
                LEFT JOIN loginuser_s AS lu ON lu.id = sbm.objectpegawaipenerimafk  
                LEFT JOIN pegawai_m AS pg ON pg.id = lu.objectpegawaifk  
                LEFT JOIN produk_m AS pr ON pr.id = pp.produkfk  
                LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
                WHERE pp.kdprofile = $kdProfile AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir'
                AND pd.objectkelompokpasienlastfk <> 2
                $noreg
                $norm 
                $namaPasien
                $ruId
                $deptId 
                $paramKasir
                $paramKelLayanan
                $idKelPasien                                                                   
        "));
//        UNION ALL
//        SELECT pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,ru.namaruangan,kp.kelompokpasien,
//                pp.tglpelayanan,pp.produkfk,pr.namaproduk,
//                CASE WHEN pp.hargajual IS NULL THEN 0 ELSE pp.hargajual END AS harga,
//                CASE WHEN pp.hargadiscount IS NULL THEN 0 ELSE pp.hargadiscount END AS diskon,
//                CASE WHEN pp.jasa IS NULL THEN 0 ELSE pp.jasa END AS jasa,(pp.jumlah*7)/30 AS jumlah,
//                (((CASE WHEN pp.hargajual IS NULL THEN 0 ELSE pp.hargajual END - CASE WHEN pp.hargadiscount IS NULL THEN
//                0 ELSE pp.hargadiscount END) * ((pp.jumlah*7)/30)) + CASE WHEN pp.jasa IS NULL THEN 0 ELSE pp.jasa END ) AS total,
//                pd.tglpulang,sp.nostruk as nomorverif,sbm.nosbm,pg.namalengkap as kasir
//                FROM pelayananpasien_t AS pp
//                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
//                INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
//                INNER JOIN pasien_m AS pm ON pm.id = pd.nocmfk
//                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
//                INNER JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk
//                LEFT JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = sp.norec AND sbm.statusenabled = true
//                LEFT JOIN loginuser_s AS lu ON lu.id = sbm.objectpegawaipenerimafk
//                LEFT JOIN pegawai_m AS pg ON pg.id = lu.objectpegawaifk
//                LEFT JOIN produk_m AS pr ON pr.id = pp.produkfk
//                LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
//                WHERE pp.kdprofile = $kdProfile AND sbm.tglsbm BETWEEN '$tglAwal' AND '$tglAkhir'
//        AND pd.objectkelompokpasienlastfk = 2 AND pp.iskronis = true
//                $noreg
//                $norm
//                $namaPasien
//                $ruId
//                $deptId
//                $paramKasir
//                $idKelPasien
//                $Kronis

        return $this->respond($data);
    }

    public function getLaporanDetailPenerimaanKasirNonLayanan(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $noreg = '';
        if (isset($request['noTrans']) && $request['noTrans'] != "" && $request['noTrans'] != "undefined") {
            $noreg = " AND sp.nostruk ILIKE '%" . $request['noTrans'] . "%'";
        }
        $namaPasien = '';
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $request['namaPasien'] = str_replace("'", "", $request['namaPasien']);
            $namaPasien = "  AND replace(sp.namapasien_klien,'''','') ILIKE '%" . $request['namaPasien'] . "%'";
        }
        $paramKasir = '';
        if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
            $arrRuang = explode(',',$request['KasirArr']) ;
            $kodeRuang = [];
            $str = '';
            $d=0;
            foreach ( $arrRuang as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramKasir = " AND pg3.id IN ($str)";
        }

        $data = \DB::select(DB::raw("
                SELECT sp.tglstruk AS tglregistrasi,'-' AS nocm,'-' AS noregistrasi,sp.namapasien_klien AS namapasien,
                       ru.namaruangan,'Umum/Pribadi' as kelompokpasien,sp.tglstruk AS tglpelayanan,
                       spd.objectprodukfk AS produkfk,pr.namaproduk,
                       CASE WHEN spd.hargasatuan IS NULL THEN	0 ELSE spd.hargasatuan END AS harga,
                       CASE WHEN spd.hargadiscount IS NULL THEN 0 ELSE spd.hargadiscount END AS diskon,
                       CASE WHEN spd.hargatambahan IS NULL THEN	0 ELSE	spd.hargatambahan	END AS jasa,
                       spd.qtyproduk AS jumlah,		
                       (((CASE WHEN spd.hargasatuan  IS NULL THEN	0 ELSE spd.hargasatuan  END - CASE WHEN spd.hargadiscount IS NULL THEN
                       0 ELSE spd.hargadiscount	END) * spd.qtyproduk) + CASE WHEN spd.hargatambahan IS NULL THEN	0 ELSE	spd.hargatambahan END) AS total,
                       pg3.namalengkap as kasir,sbm.nosbm
                FROM strukpelayanan_t as sp  
                LEFT JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
                LEFT JOIN pegawai_m as pg on pg.id=sp.objectpegawaipenanggungjawabfk  
                INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk  
                LEFT JOIN pegawai_m as pg2 on pg2.id = sbm.objectpegawaipenerimafk  
                LEFT JOIN loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk  
                LEFT JOIN pegawai_m as pg3 on pg3.id = lu.objectpegawaifk  
                LEFT JOIN ruangan_m as ru on ru.id=sp.objectruanganfk  
                LEFT JOIN produk_m as pr on pr.id = spd.objectprodukfk
                WHERE sp.kdprofile = $kdProfile and sbm.tglsbm BETWEEN '$tglAwal' AND '$tglAkhir'
                AND substring(sp.nostruk,1,2)='OB' 
                $noreg
                $namaPasien
                $paramKasir
                GROUP BY sp.tglstruk,sp.namapasien_klien,ru.namaruangan,sp.tglstruk,
				         spd.objectprodukfk,pr.namaproduk,spd.hargasatuan,spd.hargadiscount,
			             spd.hargatambahan,spd.qtyproduk,pg3.namalengkap,sbm.nosbm
        "));

        return $this->respond($data);
    }

    public function getDataLaporanPenerimaanAzaleaMCU(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin=$request->all();

        // $data = \DB::table('sstrukbuktipenerimaan_t as sbm')
        //     ->join('strukpelayanan_t as sp', 'sbm.nostrukfk', '=', 'sp.norec')
        //     ->leftjoin('pasiendaftar_t as pd', 'sp.noregistrasifk', '=', 'pd.norec')
        //     ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
        //     ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
        //     ->leftjoin('pegawai_m as p', 'p.id', '=', 'lu.objectpegawaifk')
        //     ->leftjoin('pasien_m as ps', 'ps.id', '=', 'sp.nocmfk')
        //     ->leftjoin('strukbuktipenerimaancarabayar_t as sbmcr', 'sbmcr.nosbmfk', '=', 'sbm.norec')
        //     ->leftjoin('carabayar_m as cb', 'cb.id', '=', 'sbmcr.objectcarabayarfk')
        //     ->leftjoin('kelompoktransaksi_m as kt', 'kt.id', '=', 'sbm.objectkelompoktransaksifk')
        //     ->leftjoin('strukclosing_t as sc', 'sc.norec', '=', 'sbm.noclosingfk')
        //     ->leftjoin('strukverifikasi_t as sv', 'sv.norec', '=', 'sbm.noverifikasifk')
        //     ->select('sbm.norec as noRec','cb.carabayar as caraBayar','sbmcr.objectcarabayarfk as idCaraBayar','sbm.objectkelompoktransaksifk as idKelTransaksi',
        //         'kt.kelompoktransaksi as kelTransaksi','sbm.keteranganlainnya as keterangan','p.id as idPegawai','p.namalengkap as namaPenerima',
        //         'pd.tglregistrasi as tglRegis','sbm.totaldibayar as totalPenerimaan','pd.noregistrasi','ps.namapasien',
        //         'sp.norec as norec_sp','ru.id as ruid','ru.namaruangan','sp.namapasien_klien','ps.nocm', 'pd.jenispelayanan')
        //     ->where('sbm.kdprofile', $kdProfile);
        //     ->groupBy('noRec', 'caraBayar', 'idCaraBayar', 'idKelTransaksi', 'kelTransaksi', 'keterangan', 'idPegawai', 'namaPenerima', 'tglRegis', 'noregistrasi', 'namapasien', 'norec_sp','ruid','ru.namaruangan','sp.namapasien_klien','ps.nocm','pd.jenispelayanan');

        $filter = $request->all();
        $tglAwal = '';
        if(isset($filter['dateStartTglSbm']) && $filter['dateStartTglSbm'] != "" && $filter['dateStartTglSbm'] != "undefined") {
            $tgl2 = $filter['dateStartTglSbm'] ;//. " 00:00:00";
            $tglAwal = " and sbm.tglsbm >= '".$tgl2."'";
        }
        $tglAkhir = '';
        if(isset($filter['dateEndTglSbm']) && $filter['dateEndTglSbm'] != "" && $filter['dateEndTglSbm'] != "undefined") {
            $tgl = $filter['dateEndTglSbm'] ;//. " 23:59:59";
            $tglAkhir = " and sbm.tglsbm <= '".$tgl."'";
        }
        $idPegawai = '';
        if(isset($filter['idPegawai']) && $filter['idPegawai'] != "" && $filter['idPegawai'] != "undefined") {
            $idPegawai = " and p.id = ".$filter['idPegawai'];
        }
        $jenisPelayanan = '';
        $ruid1 = 'and ru.id = 663';
        $ruid2 = '';
        if(isset($filter['jenisPelayanan']) && $filter['jenisPelayanan'] != "" && $filter['jenisPelayanan'] != "undefined") {
            $jenisPelayanan = " and pd.jenispelayanan = '".$filter['jenisPelayanan']."'";
            if ($filter['jenisPelayanan'] == 1) {
                $ruid1 = '';
                $ruid2 = " and ru.id = 663";
            }
            else {
                $ruid1 = '';
                $ruid2 = '';
            }
        }
        $nosbm = '';
        if(isset($filter['nosbm']) && $filter['nosbm'] != "" && $filter['nosbm'] != "undefined") {
            $nosbm = " and sbm.nosbm ilike '%".$filter['nosbm']."%'";
        }
        $nocm = '';
        if(isset($filter['nocm']) && $filter['nocm'] != "" && $filter['nocm'] != "undefined") {
            $nocm = " and ps.nocm ilike '%".$filter['nocm']."%'";
        }
        $nama = '';
        if(isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $nama = " and ps.namapasien ilike '%".$filter['nama']."%'";
        }
        $kasir = '';
        if(isset($filter['kasir']) && $filter['kasir'] != "" && $filter['kasir'] != "undefined") {
            $kasir = " and p.id = ".$filter['kasir'];
        }

        // if(isset($filter['desk']) && $filter['desk'] != "" && $filter['desk'] != "undefined") {
        //     $data = $data->where('sp.namapasien_klien','ilike','%'.$filter['desk'].'%');
        // }
        // $kasirArr = '';
        // if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
        //     $arrRuang = explode(',',$request['KasirArr']) ;
        //     $kodeRuang = [];
        //     foreach ( $arrRuang as $item){
        //         $kodeRuang[] = (int) $item;
        //     }
        //     $data = $data->whereIn('',);
        //     $kasirArr = ' and p.id in '.$kodeRuang;
        // }
        // $data = $data->get();
        
        $data = \DB::select(DB::raw("
                select cb.carabayar as caraBayar, 
                    sbmcr.objectcarabayarfk as idCaraBayar, 
                    sbm.objectkelompoktransaksifk as idKelTransaksi, 
                    kt.kelompoktransaksi as kelTransaksi, 
                    sbm.keteranganlainnya as keterangan, 
                    p.id as idPegawai, 
                    p.namalengkap as namapenerima, 
                    pd.tglregistrasi, 
                    sum(cast(sbm.totaldibayar AS float)) AS totalpenerimaan,
                    pd.noregistrasi, ps.namapasien,  
                    ru.id as ruid, 
                    ru.namaruangan, sp.namapasien_klien, 
                    ps.nocm, pd.jenispelayanan 
                from strukbuktipenerimaan_t as sbm 
                inner join strukpelayanan_t as sp on sbm.nostrukfk = sp.norec 
                left join pasiendaftar_t as pd on sp.noregistrasifk = pd.norec 
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk 
                left join loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk 
                left join pegawai_m as p on p.id = lu.objectpegawaifk 
                left join pasien_m as ps on ps.id = sp.nocmfk 
                left join strukbuktipenerimaancarabayar_t as sbmcr on sbmcr.nosbmfk = sbm.norec 
                left join carabayar_m as cb on cb.id = sbmcr.objectcarabayarfk 
                left join kelompoktransaksi_m as kt on kt.id = sbm.objectkelompoktransaksifk 
                left join strukclosing_t as sc on sc.norec = sbm.noclosingfk 
                left join strukverifikasi_t as sv on sv.norec = sbm.noverifikasifk 
                where sbm.kdprofile = $kdProfile
                    $tglAwal 
                    $tglAkhir
                    $idPegawai
                    $jenisPelayanan
                    $ruid1
                    $ruid2
                    $nosbm
                    $nocm
                    $nama
                    $kasir
                group by cb.carabayar, sbmcr.objectcarabayarfk, sbm.objectkelompoktransaksifk, kt.kelompoktransaksi, sbm.keteranganlainnya, 
                    p.id, p.namalengkap, pd.tglregistrasi, pd.noregistrasi, ps.namapasien, ru.id, ru.namaruangan,
                    sp.namapasien_klien,ps.nocm, pd.jenispelayanan
        "));

        return $this->respond($data);
    }

    public function getLaporanDetailPenerimaanKasirObatKronis(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $noreg = '';
        if (isset($request['noTrans']) && $request['noTrans'] != "" && $request['noTrans'] != "undefined") {
            $noreg = " AND pd.noregistrasi ILIKE '%" . $request['noTrans'] . "%'";
        }
        $namaPasien = '';
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $request['namaPasien'] = str_replace("'", "", $request['namaPasien']);
            $namaPasien = "  AND replace(ps.namapasien,'''','') ILIKE '%" . $request['namaPasien'] . "%'";
        }
        $paramKasir = '';
        if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
            $arrRuang = explode(',',$request['KasirArr']) ;
            $kodeRuang = [];
            $str = '';
            $d=0;
            foreach ( $arrRuang as $item){
                if ($str == ''){
                    $str = $item;
                }else{
                    $str = $str . ',' . $item;
                }
                $d = $d + 1;
            }
            $paramKasir = " AND pg.id IN ($str)";
        }

        $data = \DB::select(DB::raw("
                SELECT pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,ru.namaruangan,kp.kelompokpasien,
                       sbm.tglsbm AS tglpelayanan,0 AS produkfk,'Obat Kronis' AS namaproduk,
                       sbm.totaldibayar AS total,pd.tglpulang,sp.nostruk as nomorverif,sbm.nosbm,
                       pg.namalengkap as kasir
                FROM pelayananpasien_t AS pp
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                INNER JOIN pasien_m AS pm ON pm.id = pd.nocmfk
                LEFT JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                INNER JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk
                LEFT JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = sp.norec AND sbm.statusenabled = true
                LEFT JOIN loginuser_s AS lu ON lu.id = sbm.objectpegawaipenerimafk
                LEFT JOIN pegawai_m AS pg ON pg.id = lu.objectpegawaifk
                LEFT JOIN produk_m AS pr ON pr.id = pp.produkfk
                LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
                WHERE sp.kdprofile = $kdProfile and sbm.tglsbm BETWEEN '$tglAwal' AND '$tglAkhir'
                      AND pd.objectkelompokpasienlastfk = 2 AND pp.iskronis = true
                $noreg
                $namaPasien
                $paramKasir
                GROUP BY pd.tglregistrasi,pm.nocm,pd.noregistrasi,pm.namapasien,ru.namaruangan,kp.kelompokpasien,
				        sbm.tglsbm,sbm.totaldibayar,pd.tglpulang,sp.nostruk,sbm.nosbm,pg.namalengkap
                ORDER BY pm.namapasien ASC
        "));

        return $this->respond($data);
    }
    public function daftarVirtualAccount(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin=$request->all();
        $data = \DB::table('virtualaccount_t as vr')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'vr.norec_sbm')
            ->leftjoin('strukpelayanan_t as sp', 'vr.norec_sp', '=', 'sp.norec')
            ->leftjoin('pasiendaftar_t as pd', 'vr.norec_pd', '=', 'pd.norec')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            // ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'vr.pegawaifk')
            ->leftjoin('pegawai_m as p', 'p.id', '=', 'vr.objectpegawaifk')
            ->leftjoin('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->select(
                   \DB::raw("vr.*,pd.noregistrasi,pd.tglregistrasi,ps.nocm,ps.namapasien,p.namalengkap as kasir,ru.namaruangan,sbm.nosbm")
            )
            ->where('vr.statusenabled',true)
            ->where('vr.kdprofile', $kdProfile)
            ->whereNotnull('vr.norec_pd');

        $filter = $request->all();
        if(isset($filter['dateStartTglSbm']) && $filter['dateStartTglSbm'] != "" && $filter['dateStartTglSbm'] != "undefined") {
            $tgl2 = $filter['dateStartTglSbm'] ;//. " 00:00:00";
            $data = $data->where('vr.datetime_created', '>=', $tgl2);
        }
        if(isset($filter['dateEndTglSbm']) && $filter['dateEndTglSbm'] != "" && $filter['dateEndTglSbm'] != "undefined") {
            $tgl = $filter['dateEndTglSbm'] ;//. " 23:59:59";
            $data = $data->where('vr.datetime_created', '<=', $tgl);
        }
        if(isset($filter['idPegawai']) && $filter['idPegawai'] != "" && $filter['idPegawai'] != "undefined") {
            $data = $data->where('p.id', '=', $filter['idPegawai']);
        }
        if(isset($filter['idCaraBayar']) && $filter['idCaraBayar'] != "" && $filter['idCaraBayar'] != "undefined") {
            $data = $data->where('cb.id', '=', $filter['idCaraBayar']);
        }
        if(isset($filter['idKelTransaksi']) && $filter['idKelTransaksi'] != "" && $filter['idKelTransaksi'] != "undefined") {
            $data = $data->where('kt.id', $filter['idKelTransaksi']);
        }
        if(isset($filter['ins']) && $filter['ins'] != "" && $filter['ins'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', $filter['ins']);
        }
        if(isset($filter['nosbm']) && $filter['nosbm'] != "" && $filter['nosbm'] != "undefined") {
            $data = $data->where('sbm.nosbm','ilike','%'.$filter['nosbm'].'%');
        }
        if(isset($filter['nocm']) && $filter['nocm'] != "" && $filter['nocm'] != "undefined") {
            $data = $data->where('ps.nocm','ilike','%'.$filter['nocm'].'%');
        }
        if(isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien','ilike','%'.$filter['nama'].'%');
        }
        // if(isset($filter['desk']) && $filter['desk'] != "" && $filter['desk'] != "undefined") {
        //     $data = $data->where('sp.namapasien_klien','ilike','%'.$filter['desk'].'%');
        // }
        // if(isset($filter['JenisPelayanan']) && $filter['JenisPelayanan'] != "" && $filter['JenisPelayanan'] != "undefined") {
        //     $data = $data->where('pd.jenispelayanan','=',$filter['JenisPelayanan']);
        //     if($filter['JenisPelayanan'] == 1){
        //         $data = $data->where('ru.id','=',663);
        //     }
        // }

        if(isset($request['KasirArr']) && $request['KasirArr']!="" && $request['KasirArr']!="undefined"){
            $arrRuang = explode(',',$request['KasirArr']) ;
            $kodeRuang = [];
            foreach ( $arrRuang as $item){
                $kodeRuang[] = (int) $item;
            }
            $data = $data->whereIn('p.id',$kodeRuang);
        }

//        $data = $data->take($request['jmlRows']);
        $data = $data->get();
        return $this->respond($data);
    }
     public function createBillingSIMRS(Request $request)
    {
         $kdProfile = (int)$this->getDataKdProfile($request);
   
       // return $this->sendSMS('082211333013',$kontenSMS);
        DB::beginTransaction();
        try{
            // $r = $request->input();
            $vir['trx_id'] =  $this->generateCodeBySeqTable(new VirtualAccount(), 'trx_id', 10, date('ymd'), $this->kdProfile);
            // return $vir['trx_id'];
            $strukPelayanan = StrukPelayanan::where('norec', $request['parameterTambahan']['noRecStrukPelayanan'])->first();
            $norecPD =$strukPelayanan->pasien_daftar;
            $nocmfk =$strukPelayanan->pasien_daftar->nocmfk;
            $pref = substr($norecPD->noregistrasi, 6,4);
            $expired = date("Y-m-d H:i:s", strtotime('+3 hours', strtotime(date('Y-m-d H:i:s'))));
            $pasien= DB::table('pasien_m')->where('id',$nocmfk)->first();
            
            $vir['type'] = 'createbilling';
            $vir['virtual_account'] =$this->getPrefix().date('ym').$pref;
            $vir['client_id'] =$this->getClientId();
            $vir['trx_amount'] =$request['jumlahBayar'];
            $vir['billing_type'] ='c';
            $vir['customer_name'] =$pasien->namapasien;
            $vir['customer_email'] ='';
            $vir['customer_phone'] =$request['nohp'];
            $vir['datetime_expired'] =$expired;
            $vir['description'] ='Trx Virtual '. $vir['trx_id'];
             // return  $vir;
            $kontenSMS = '';
            $response = $this->encryptBNI($vir) ;
           
            if($response['status'] =='105'){
                $vir['trx_id']=  $this->generateCodeBySeqTable(new VirtualAccount(), 'trx_id', 10, date('ymd'), $this->kdProfile);

                $response = $this->encryptBNI($vir) ;
            }
            if($response['status'] == '000'){
               $kontenSMS=   "Hai ".$vir['customer_name'].", Harap melakukan pembayaran dengan No Virtual Account BNI ".$response['data']['virtual_account']." !. Batas waktu pembayaran ".$vir['datetime_expired'].".No Transaksi kamu ".$response['data']['trx_id'].".";
               $kontenSMS=  str_replace(' ', '%20', $kontenSMS);
                $newVA = New VirtualAccount();
                $newVA->trx_id =  $response['data']['trx_id'];
                $newVA->type =  $vir['type'];
                $newVA->client_id =  $vir['client_id'];
                $newVA->trx_amount =  $vir['trx_amount'];
                $newVA->billing_type =  $vir['billing_type'];
                $newVA->customer_name =  $vir['customer_name'];
                $newVA->customer_email =  '';
                $newVA->customer_phone =  $vir['customer_phone'];
                $newVA->datetime_created =  date('Y-m-d H:i:s');
                $newVA->datetime_expired =  $vir['datetime_expired'];
                $newVA->description = $vir['description'] ;
                $newVA->virtual_account =  $response['data']['virtual_account'];
                $newVA->bank =  'BNI';
                $newVA->norec_pd =  $norecPD->norec;
                $newVA->norec_sp = $request['parameterTambahan']['noRecStrukPelayanan'];
                $newVA->objectpegawaifk =  $this->getCurrentUserID();
                $newVA->kdprofile =  $kdProfile;
                $newVA->statusenabled =  true;
                $newVA->save();
            }else{
                  DB::rollBack();
                  $transMessage = $response['message'];
                  $result = array(
                     'status' => 400,
                     'message' =>  $response['message'] ,
                 );
                 return $this->setStatusCode($result['status'])->respond($result, $transMessage);
            }


               $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
            }


            if ($transStatus == 'true') {
                $transMessage = "Sukses";
                DB::commit();
                $this->sendSMS($vir['customer_phone'],$kontenSMS);
                $result = array(
                    'status' => 201,
                    'message' => $transMessage,
                    'data' => $response,
                    'as' => 'er@epic',
                );
            } else {
                $transMessage = " Simpan Gagal";
                DB::rollBack();
                $result = array(
                    'status' => 400,
                    'message'  => $transMessage,

                    'as' => 'er@epic',
                );
            }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);

    }
    function sendSMS($nomor,$contena) {
            $auth = md5('ECGGENGGAM'.'EC3G348'.$nomor);
            // $mobile = '082211333013';
            $username = 'ECGGENGGAM';
         
            $url ="http://send.smsmasking.co.id:8080/web2sms/api/sendSMS.aspx?username=".$username."&mobile=".$nomor."&message=".$contena."&auth=".$auth;

           $curl = curl_init();

           curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              // CURLOPT_SSL_VERIFYHOST => 0,
              // CURLOPT_SSL_VERIFYPEER => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            return $response;

    }
      public function encryptBNI($data_asli){
        $hashed_string = BniEnc::encrypt(
            $data_asli,
            $this->client_id,
            $this->secret_key
        );
        $data = array(
            'client_id' => $this->client_id,
            'data' => $hashed_string,
        );
//        dd($data_asli);
        $response = $this->get_content($this->url, json_encode($data));
        $response_json = json_decode($response, true);
        if ($response_json['status'] !== '000') {
            // handling jika gagal
            return $response_json;
        }else {
            $data_response = BniEnc::decrypt($response_json['data'], $this->client_id, $this->secret_key);
            $respond = array(
                'status' => $response_json['status'],
                'data' =>  $data_response ,
            );

            return $respond;

        }
    }
    function getClientId (){
        return '13513';
    }
    function getSecretKey (){
        return 'fda741b2e353fce9856fb0a4674095b1';//'ea0c88921fb033387e66ef7d1e82ab83';
    }
    function getPrefix(){
        return '98813513';
    }
     function get_content($url, $post = '') {
//        $usecookie = __DIR__ . "/cookie.txt";
        $header[] = 'Content-Type: application/json';
        $header[] = "Accept-Encoding: gzip, deflate";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        // curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36");

        if ($post)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $rs = curl_exec($ch);

        if(empty($rs)){
            var_dump($rs, curl_error($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $rs;
    }

    public function detailPiutangPasien($norec){
        $spp = StrukPelayananPenjamin::where('norec', $norec)->first();
//        $sp = StrukPelayanan::where('norec', $spp->nostrukfk)->first();
        $sbp = StrukBuktiPenerimaan::where('nostrukfk', $spp->nostrukfk)->orderBy('nosbm')->get();

        $detailPembayaran = array();
        foreach ($sbp as $item){
            $detailPembayaran[] = array(
                'noSbm' => $item->nosbm,
                'tglPembayaran' => $item->tglsbm,
                'jlhPembayaran' => $item->totaldibayar
            );
        }
        $dibayarAwal = $spp->totalbiaya - $spp->totalppenjamin;
        $data = array(
            "noRegistrasi" => $spp->struk_pelayanan->pasien_daftar->noregistrasi,
            "namaPasien" => $spp->struk_pelayanan->pasien_daftar->pasien->namapasien,
            "jenisPenjamin" => $spp->struk_pelayanan->pasien_daftar->kelompok_pasien->kelompokpasien,
            "jenisKelamin" => $spp->struk_pelayanan->pasien_daftar->pasien->jenis_kelamin->jeniskelamin,
            "umurPiutang" => $spp->struk_pelayanan->UmurPiutang,
            "noCM" => $spp->struk_pelayanan->pasien_daftar->pasien->nocm,
            "totalTagihan" => $spp->totalbiaya,
            "sudahDibayar" => $spp->totalsudahdibayar+$dibayarAwal,
            "sisaPiutang" => $spp->totalbiaya - $dibayarAwal-$spp->totalsudahdibayar,
            "detailPembayaran" => $detailPembayaran
        );

        return $this->respond($data);
    }
}