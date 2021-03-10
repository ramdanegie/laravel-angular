<?php
/**
 * Created by PhpStorm.
 * PiutangController
 * User: Efan Andrian (ea@epic)
 * Date: 22/10/2019
 * Time: 14:25 PM
 */
namespace App\Http\Controllers\Ambulance;
use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\StrukOrder;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;
use DB;
use Illuminate\Http\Request;
class AmbulanController extends ApiController{
    use Valet;
    public function __construct(){
        parent::__construct($skip_authentication=false);
    }

    public function getDataApdAmbulance(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $Ruangan = explode (',',$this->settingDataFixed('KdRuanganAmbulance',$idProfile));
        $listRuangan = [];
        foreach ($Ruangan as $itemRuangan){
            $listRuangan [] = (int) $itemRuangan;
        }
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'apd.objectkelasfk')
            ->select('apd.norec as norec_apd', 'ps.nocm', 'ps.id as nocmfk', 'ps.namapasien', 'pd.noregistrasi',
                'apd.objectruanganfk as id','ru.objectdepartemenfk',
                'ru.namaruangan', 'apd.tglregistrasi', 'kls.namakelas', 'apd.objectruanganasalfk')
            ->whereIn('apd.objectruanganfk', $listRuangan)
            ->where('pd.noregistrasi', $request['noregistrasi'])
            ->where('apd.kdprofile', $idProfile)
            ->orderBy('pd.objectruanganlastfk')
            ->get();

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }

    public function getKelompokUser(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detailLogin = $request->all();
        $data = \DB::table('kelompokuser_s as ku')
            ->join('loginuser_s as lu','lu.objectkelompokuserfk','=','ku.id')
            ->select('ku.id','ku.kelompokuser','lu.namauser')
            ->where('ku.kdprofile', $idProfile)
            ->where('lu.id',$request['luId'])
            ->first();
        $result = array(
            "data" => $data,
            "as" => 'as@rmdn',
        );
        return $this->respond($result);
    }

    public function GetDataForComboAmbulan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $deptRanapGawat = explode(',',$this->settingDataFixed('KdRanapIgd',$idProfile));
        $deptAmbulan = (int) $this->settingDataFixed('KdInstalasiAmbulan', $idProfile);
        $KelUserKasir = $this->settingDataFixed('KdKelompokUser', $idProfile);
        // return $deptAmbulan;
        $deptZenajah = (int) $this->settingDataFixed('KdInstalasiJenazah', $idProfile);
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $kdDepartemenRawatPelayanan = [];
        $kdDeptRanapGawat = [];
        $kdPelayananAmbulan = (int) $this->settingDataFixed('KdKelompokTransaksiAmbulance', $idProfile);
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        foreach ($deptRanapGawat as $items){
            $kdDeptRanapGawat [] = (int)$items;
        }
        $ruangan = \DB::table('ruangan_m as r')
            ->select('r.id','r.namaruangan','r.objectdepartemenfk')
            ->where('statusenabled','true')
            ->where('r.kdprofile', $idProfile)
            ->wherein('r.objectdepartemenfk',$kdDeptRanapGawat)
            ->get();

        $RuanganAmbulan = \DB::table('ruangan_m as r')
            ->select('r.id','r.namaruangan','r.objectdepartemenfk')
            ->where('statusenabled','true')
            ->where('r.kdprofile', $idProfile)
            ->where('r.objectdepartemenfk',$deptAmbulan)
            ->get();

        $dataAmbulan = \DB::table('departemen_m as dp')
            ->select('dp.id','dp.namadepartemen as departemen')
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.id',$deptAmbulan)
            ->where('dp.statusenabled',true)
            ->get();

        $dataInstalasi = \DB::table('departemen_m as dp')
            ->select('dp.id','dp.namadepartemen as departemen')
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled',true)
            ->get();

        $dokter = \DB::table('pegawai_m as p')
            ->select('p.id','p.namalengkap as namadokter')
            ->where('p.kdprofile', $idProfile)
            ->where('p.objectjenispegawaifk',$kdJeniPegawaiDokter)
            ->where('statusenabled','true')
            ->get();

        $hubunganKeluarga = \DB::table('hubungankeluarga_m as h')
            ->select('h.id','h.hubungankeluarga')
            ->where('h.kdprofile', $idProfile)
            ->where('h.statusenabled','true')
            ->get();

        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id','kp.kelompokpasien')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled',true)
            ->get();

        $dataJenisPetugas = \DB::table('jenispetugaspelaksana_m as kp')
            ->select('kp.id','kp.jenispetugaspe')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled',true)
            ->get();

        $dataRuanganPelayanan = \DB::table('ruangan_m as r')
            ->select('r.id','r.namaruangan')
            ->where('r.kdprofile', $idProfile)
            ->where('statusenabled','true')
            ->whereIn('objectdepartemenfk',$kdDepartemenRawatPelayanan)
            ->get();

        $dataKelompokTransaksiAmbulan = \DB::table('kelompoktransaksi_m as r')
            ->select('r.id','r.kelompoktransaksi')
            ->where('r.kdprofile', $idProfile)
            ->where('statusenabled','true')
            ->where('r.id',$kdPelayananAmbulan)
            ->get();

        $result = array(
            'ruangan'=>$ruangan,
            'ruanganambulan'=>$RuanganAmbulan,
            'dokter'=>$dokter,
            'hubunganKeluarga'=>$hubunganKeluarga,
            'departemen'=>$dataAmbulan,
            'kelompokpasien'=>$dataKelompok,
            'jenispetugaspe'=>$dataJenisPetugas,
            'ruanganpelayanan' => $dataRuanganPelayanan,
            'kelompoktransaksi' => $dataKelompokTransaksiAmbulan,
            'keluserkasir' => $KelUserKasir
        );

        return $this->respond($result);
    }

    public function getDaftarOrderAmbulan (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $result = [];
        $data = \DB::table('strukorder_t as so')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('jeniskelamin_m as klm', 'klm.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->leftJoin('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
            ->leftJoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('departemen_m as dp2', 'dp2.id', '=', 'ru2.objectdepartemenfk')
            ->leftJoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
            ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('pelayananpasien_t as pp', 'pp.strukorderfk', '=', 'so.norec')
            ->leftJoin('jenispelayanan_m as jp', 'jp.kodeinternal', '=', 'pd.jenispelayanan')
            ->select('so.norec as norec_so', 'pd.norec as norec_pd', 'so.noorder', 'pd.noregistrasi', 'pd.tglregistrasi', 'pd.tglpulang', 'ps.nocm', 'ps.namapasien',
                'klm.jeniskelamin', 'ps.tgllahir',
                'kps.kelompokpasien', 'dp.namadepartemen', 'pd.objectkelasfk', 'kls.namakelas', 'so.objectruangantujuanfk',
                'so.objectruanganfk', 'pd.objectkelompokpasienlastfk', 'ru.objectdepartemenfk', 'ru2.objectdepartemenfk as iddeptujuan',
                'so.objectpegawaiorderfk', 'pg.namalengkap as pegawaiorder','so.tglorder','pd.jenispelayanan as idjenisPelayanan','jp.jenispelayanan',
                'ru.namaruangan', 'ru2.namaruangan as ruangantujuan','so.tglpelayananakhir','pg2.namalengkap as dpjp','so.tglrencana',
                (DB::raw("case when pp.strukorderfk is null then 'MASUK' else
                                    'Sudah Verifikasi' end as status")))
            ->where('so.kdprofile', $idProfile)
            ->where('ru2.objectdepartemenfk', $this->settingDataFixed('KdInstalasiAmbulan',$idProfile))            ->where('so.statusenabled',true);

        if (isset($request['isNotVerif']) && $request['isNotVerif'] != "" && $request['isNotVerif'] != "undefined") {
            if ($request['isNotVerif'] == true) {
                $data = $data->whereNull('pp.strukorderfk');
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
        if(isset($request['tglAwalRen']) && $request['tglAwalRen']!="" && $request['tglAwalRen']!="undefined"){
            $data = $data->where('so.tglrencana','>=', $request['tglAwalRen']);
        }
        if(isset($request['tglAkhirRen']) && $request['tglAkhirRen']!="" && $request['tglAkhirRen']!="undefined"){
            $tgl= $request['tglAkhirRen'];
            $data = $data->where('so.tglrencana','<=', $tgl);
        }
        if(isset($request['idRuanganOrder']) && $request['idRuanganOrder']!="" && $request['idRuanganOrder']!="undefined"){
            $data = $data->where('so.objectruanganfk','=', $request['idRuanganOrder']);
        }

        if(isset($request['pegId']) && $request['pegId']!="" && $request['pegId']!="undefined"){
            $data = $data->where('so.objectpegawaiorderfk','=', $request['pegId']);
        }
        if(isset($request['idRuanganTujuan']) && $request['idRuanganTujuan']!="" && $request['idRuanganTujuan']!="undefined"){
            $data = $data->where('so.objectruangantujuanfk','=', $request['idRuanganTujuan']);
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

        $dataResult=array(
            'message' =>  'inhuman',
            'data' =>  $data,
        );
        return $this->respond($dataResult);
    }


    public function saveOrderAmbulan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detLogin = $request->all();
        if ($request['pegawaiorderfk'] == "") {
            $dokter2 = null;
        } else {
            $dokter2 = $request['pegawaiorderfk'];
        }
        DB::beginTransaction();
        try {
            if ($request['departemenfk'] == 3) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'L' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 27) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'R' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 25) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'OK' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 5) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'PJ' . $this->getDateTime()->format('ym'), $idProfile);
            }
            if ($request['departemenfk'] == 31) {
                $noOrder = $this->generateCode(new StrukOrder, 'noorder', 11, 'ABM' . $this->getDateTime()->format('ym'), $idProfile);
            }

            $dataPD = PasienDaftar::where('norec', $request['norec_pd'])->first();
            if ($request['norec_so'] == "") {
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
                if(isset( $request['keterangan'])){
                    $dataSO->keteranganlainnya = $request['keterangan'];
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
                    if ($request['status'] == 'bridinglangsung') {
                        $updatePP = PelayananPasien::where('norec', $item['norec_pp'])
                            ->where('kdprofile',$idProfile)
                            ->update([
                                    'strukorderfk' => $dataSOnorec
                                ]
                            );
                    }

                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if (isset($item['iscito'])) {
                        $dataOP->iscito = (float)$item['iscito'];
                    } else {
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
                    if (isset($item['pemeriksaanluar'])) {
                        if ($item['pemeriksaanluar'] == 1) {
                            $dataOP->keteranganlainnya = 'isPemeriksaanKeluar';
                        }
                    }

                    if (isset($item['tglrencana'])) {
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    } else {
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    $dataOP->strukorderfk = $dataSOnorec;
                    if (isset($item['dokterid']) && $item['dokterid'] != "") {
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->save();
                }

            } else {

                foreach ($request['details'] as $item) {
                    $dataOP = new OrderPelayanan;
                    $dataOP->norec = $dataOP->generateNewId();
                    $dataOP->kdprofile = $idProfile;
                    $dataOP->statusenabled = true;
                    if (isset($item['iscito'])) {
                        $dataOP->iscito = (float)$item['iscito'];
                    } else {
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

                    if (isset($item['tglrencana'])) {
                        $dataOP->tglpelayanan = $item['tglrencana'];
                    } else {
                        $dataOP->tglpelayanan = date('Y-m-d H:i:s');
                    }

                    if (isset($item['dokterid']) && $item['dokterid'] != "") {
                        $dataOP->objectnamapenyerahbarangfk = $item['dokterid'];
                    }
                    $dataOP->save();
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan OrderPelayanan";
        }

        if ($transStatus == 'true') {
            if ($request['norec_so'] == "") {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
                    "strukorder" => $dataSO,
                    "as" => 'inhuman',
                );
            } else {
                $transMessage = "Simpan Order Pelayanan";
                DB::commit();
                $result = array(
                    "status" => 201,
                    "message" => $transMessage,
                    "as" => 'inhuman',
                );
            }
        } else {
            $transMessage = "Simpan Order Pelayanan gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function hapusOrderPelayananAmbulan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            StrukOrder::where('norec', $request['norec_order'])->where('kdprofile', $idProfile)->update
            (['statusenabled' => false]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Data Terhapus";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                    "strukorder" => $dataSO,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRiwayatOrderPelayananAmbulan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $results = [];
        $ruanganLab = explode(',', $this->settingDataFixed('KdRuanganAmbulance', $idProfile));
        $kdRuangLab = [];
        foreach ($ruanganLab as $item) {
            $kdRuangLab [] = (int)$item;
        }
        $data = \DB::table('strukorder_t as so')
            ->LEFTJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->JOIN('pasien_m as pas', 'pas.id', '=', 'pd.nocmfk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->LEFTJOIN('ruangan_m as ru2', 'ru2.id', '=', 'so.objectruangantujuanfk')
            ->LEFTJOIN('pegawai_m as p', 'p.id', '=', 'so.objectpegawaiorderfk')
            ->select('so.norec', 'pd.norec as norecpd', 'pd.noregistrasi', 'so.tglorder', 'so.noorder',
                'ru.namaruangan as ruanganasal', 'ru2.namaruangan as ruangantujuan', 'p.namalengkap',
                'so.noorder','pd.noregistrasi','so.keteranganlainnya'
            )
            ->where('pd.kdprofile', $idProfile);
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != "" && $request['noregistrasi'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noregistrasi']);
        }
        if (isset($request['NoCM']) && $request['NoCM'] != "" && $request['NoCM'] != "undefined") {
            $data = $data->where('pas.nocm', 'ilike', '%' . $request['NoCM'] . '%');
        }
        $data = $data->whereIn('so.objectruangantujuanfk', $kdRuangLab);
        $data = $data->where('so.statusenabled', true);
        $data = $data->orderBy('so.tglorder');
        $data = $data->get();

        //$results =array();
        foreach ($data as $item) {
            $noorder = $item->noorder;
            $hasil = DB::select(DB::raw("select * from order_lab where no_lab='$noorder'"));
            if (count($hasil) > 0) {
                $item->statusorder = 'SELESAI DIPERIKSA';
            } else {
                $item->statusorder = 'PENDING';
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
                            where so.kdprofile = $idProfile and so.noorder=:noorder"),
                array(
                    'noorder' => $item->noorder,
                )
            );
            $results[] = array(
                'noregistrasi' => $item->noregistrasi,
                'tglorder' => $item->tglorder,
                'noorder' => $item->noorder,
                'norec' => $item->norec,
                'norecpd' => $item->norecpd,
//                'norecapd' => $item->norecapd,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'dokter' => $item->namalengkap,
                'statusorder' => $item->statusorder,
                'keteranganlainnya' => $item->keteranganlainnya,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getOrderPelayananAmbulan(Request $request) {
        $idkelas = $request['objectkelasfk'];
        $norec_so = $request['norec_so'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataOrderPelayanan = DB::select(DB::raw("select DISTINCT op.norec as norec_op,pr.id as prid,pr.namaproduk,
                op.tglpelayanan,op.qtyproduk ,ru.namaruangan as ruangantujuan,ru.objectdepartemenfk,op.strukorderfk,so.objectruangantujuanfk,
                hnp.hargasatuan ,kls.namakelas,dpm.namadepartemen,
                pps.norec as norec_pp
                from orderpelayanan_t op
                left join strukorder_t as so on so.norec=op.strukorderfk
                INNER JOIN produk_m as pr on pr.id=op.objectprodukfk
                left JOIN harganettoprodukbykelas_m as hnp on pr.id=hnp.objectprodukfk
                        and '$idkelas' =hnp.objectkelasfk                
                left join kelas_m as kls on kls.id = '$idkelas'                
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
            $dataz =  DB::select(DB::raw("select DISTINCT hnp.objectkomponenhargafk,kh.komponenharga,hnp.hargasatuan,
                hnp.objectprodukfk
                from harganettoprodukbykelasd_m as hnp   
                inner join produk_m as prd on prd.id=hnp.objectprodukfk
                inner join komponenharga_m as kh on kh.id=hnp.objectkomponenhargafk
                inner join kelas_m as kls on kls.id = hnp.objectkelasfk
                where hnp.kdprofile = $idProfile and hnp.objectkelasfk='$idkelas'
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

    public function savePelayananPasienAmbulan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $jenisPetugasPe = \DB::table('mapjenispetugasptojenispegawai_m as mpp')
                ->join ('jenispegawai_m as jp','jp.id','=','mpp.objectjenispegawaifk')
                ->join ('pegawai_m as pg','pg.objectjenispegawaifk','=','jp.id')
                ->join ('jenispetugaspelaksana_m as jpp','jpp.id','=','mpp.objectjenispetugaspefk')
                ->select( 'mpp.objectjenispegawaifk','jp.jenispegawai','mpp.objectjenispetugaspefk' ,'jpp.jenispetugaspe',
                    'pg.namalengkap','pg.id'
                )
                ->where('mpp.kdprofile', $kdProfile)
                ->where('pg.id', $request['objectpegawaiorderfk'])
                ->first();

            if($request['norec_pp']=='') {
                $pd = PasienDaftar::where('norec',$request['norec_pd'])->first();
                $dataAPD = new AntrianPasienDiperiksa();
                $dataAPD->norec = $dataAPD->generateNewId();
                $dataAPD->kdprofile = $kdProfile;
                $dataAPD->objectasalrujukanfk = 1;
                $dataAPD->statusenabled = true;
                $dataAPD->objectkelasfk = $request['objectkelasfk'];
                $dataAPD->noantrian = 1;
                $dataAPD->noregistrasifk = $request['norec_pd'];
                $dataAPD->objectpegawaifk = $request['objectpegawaiorderfk'];
                $dataAPD->objectruanganfk = $request['objectruangantujuanfk'];
                $dataAPD->statusantrian = 0;
                $dataAPD->statuspasien = 1;
                $dataAPD->objectstrukorderfk = $request['norec_so'];
                $dataAPD->tglregistrasi =$pd->tglregistrasi;// date('Y-m-d H:i:s');
                $dataAPD->tglmasuk = date('Y-m-d H:i:s');
                $dataAPD->tglkeluar = date('Y-m-d H:i:s');
                $dataAPD->save();

                $dataAPDnorec = $dataAPD->norec;
                $dataAPDtglPel = $dataAPD->tglregistrasi;
            }else{
                $dataAPD =  PelayananPasien::where('norec',$request['norec_pp'])->where('kdprofile', $kdProfile)->first();
                $dataAPDnorec = $dataAPD->noregistrasifk;
                $dataAPDtglPel = $dataAPD->tglregistrasi;

                $HapusPP = PelayananPasien::where('strukorderfk', $request['norec_so'])->where('kdprofile', $kdProfile)->get();
                foreach ($HapusPP as $pp){
                    $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->where('kdprofile', $kdProfile)->delete();
                    $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->where('kdprofile', $kdProfile)->delete();
                }
                $Edit = PelayananPasien::where('strukorderfk', $request['norec_so'])->where('kdprofile', $kdProfile)->delete();
            }


            foreach ($request['bridging'] as $item){
                $PelPasien = new PelayananPasien();
                $PelPasien->norec = $PelPasien->generateNewId();
                $PelPasien->kdprofile = $kdProfile;
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

                if (isset($item['tglpelayanan'])){
                    $PelPasien->tglpelayanan = $item['tglpelayanan'];
                }else{
                    $PelPasien->tglpelayanan = date('Y-m-d H:i:s');
                }

                $PelPasien->harganetto =  $item['hargasatuan'];

                $PelPasien->save();
                $PPnorec = $PelPasien->norec;


                $PelPasienPetugas = new PelayananPasienPetugas();
                $PelPasienPetugas->norec = $PelPasienPetugas->generateNewId();
                $PelPasienPetugas->kdprofile = $kdProfile;
                $PelPasienPetugas->statusenabled = true;
                $PelPasienPetugas->nomasukfk = $dataAPDnorec;
                $PelPasienPetugas->objectpegawaifk = $request['iddokterverif'];//$request['objectpegawaiorderfk'],
                $PelPasienPetugas->objectjenispetugaspefk = $request['idpetugaspe'];//4;//$jenisPetugasPe->idpetugaspe;
                $PelPasienPetugas->pelayananpasien = $PPnorec;
                $PelPasienPetugas->save();
                $PPPnorec = $PelPasienPetugas->norec;


                foreach ($item['komponenharga'] as $itemKomponen) {

                    $PelPasienDetail = new PelayananPasienDetail();
                    $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                    $PelPasienDetail->kdprofile = $kdProfile;
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

            $dataPD = PasienDaftar::where('norec', $request['norec_pd'])
                ->where('kdprofile', $kdProfile)
                ->update(['objectruanganlastfk' => $request['objectruangantujuanfk']]
                );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan PelPasien";
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

    public function getRincianPelayananAmbulan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin=$request->all();
        $result=[];
        $dataruangan = \DB::table('maploginusertoruangan_s as mlu')
            ->leftjoin('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->leftjoin('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
            ->where('mlu.kdprofile', $kdProfile)
            ->where('objectloginuserfk',$dataLogin['userData']['id'])
            ->get();

        $pelayanan = \DB::table('pelayananpasien_t as pp')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'pp.noregistrasifk')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->JOIN('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->JOIN('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftJOIN('strukorder_t as so', 'so.norec', '=', 'pp.strukorderfk')
//                ->leftJOIN('ris_order as ris', 'ris.order_no', '=',
//                    DB::raw('so.noorder AND ris.order_code=pp.produkfk'))
            ->select('ps.nocm', 'ps.namapasien', 'jk.jeniskelamin', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'pp.jumlah', 'pp.hargasatuan', 'pp.hargadiscount', 'sp.nostruk', 'pd.noregistrasi', 'ru.namaruangan',
                'dp.namadepartemen', 'ps.id as psid', 'apd.norec as norec_apd', 'sp.norec as norec_sp', 'pp.norec as norec_pp',
                'ru.objectdepartemenfk', 'so.noorder','apd.objectruanganfk','pp.iscito','pp.jasa')
//                    DB::raw('case when ris.order_key is not null then \'Sudah Dikirim\' else \'-\'end as statusbridging'))
            ->where('pp.kdprofile', $kdProfile)
            ->where('ru.objectdepartemenfk', $this->settingDataFixed('KdInstalasiAmbulan', $kdProfile))
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
//                    ->where('ptu.objectjenispetugaspefk', 4)
                ->where('pd.kdprofile', $kdProfile)
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
                    'iscito' => $item->iscito,
                    'jasa' => (float)$item->jasa,
                );
            }
        }
        $dataTea =array(
            'data' => $result,
            'detaillogin' => $dataLogin,
            'message' => 'Inhuman'
        );
        return $this->respond($dataTea);
    }

    public function getPelayananAmbulan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
//            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('batalregistrasi_t as br', 'pd.norec', '=', 'br.pasiendaftarfk')
            ->select('pd.norec as norec_pd','apd.norec as norec_apd','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                'kls.namakelas','jk.jeniskelamin','pg.namalengkap as namapelaksana','pd.norec as norec_pd',
                'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','apd.tglmasuk','pr.id as prid', 'pr.namaproduk')
            ->where('pd.kdprofile', $kdProfile)
            ->whereNull('p.tglmeninggal')
            ->whereNull('br.norec');

        $filter = $request->all();
        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
//            $data = $data->where('so.tglorder', '>=', $filter['tglAwal']);
            $data = $data->where('apd.tglmasuk', '>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'] ;//. " 23:59:59";
//            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
//            $data = $data->where('so.tglorder', '<=', $tgl);
            $data = $data->where('apd.tglmasuk', '<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId'] != "" && $filter['instalasiId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['instalasiId']);
        }

        if(isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $filter['idRuangan']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('p.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }

        if(isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if(isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('p.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        if(isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $data = $data->take($filter['jmlRow']);
        }

        $data = $data->groupBy('pd.norec','apd.norec','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'kls.namakelas','jk.jeniskelamin','pg.namalengkap','pd.norec',
            'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','apd.tglmasuk','pr.id', 'pr.namaproduk');

        // $data = $data->orderBy('pd.tglregistrasi','desc');
        // $data = $data->take(50);
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function getJadwalAmbulan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('strukorder_t as so', 'so.noregistrasifk', '=', 'pd.norec')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('batalregistrasi_t as br', 'pd.norec', '=', 'br.pasiendaftarfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->select('pd.norec as norec_pd','apd.norec as norec_apd','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                'kls.namakelas','jk.jeniskelamin','pg.namalengkap as namadokter','pd.norec as norec_pd',
                'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','apd.tglmasuk','pp.tglpelayanan')
            ->where('pd.kdprofile', $kdProfile)
            ->whereNull('p.tglmeninggal')
            ->whereNull('br.norec')
            ->where('apd.objectruanganfk',72);

        $filter = $request->all();

        if(isset($filter['tglAwalRen']) && $filter['tglAwalRen'] != "" && $filter['tglAwalRen'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $filter['tglAwalRen']);
        }

        if(isset($filter['tglAkhirRen']) && $filter['tglAkhirRen'] != "" && $filter['tglAkhirRen'] != "undefined") {
            $tgl = $filter['tglAkhirRen'] ;//. " 23:59:59";
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId'] != "" && $filter['instalasiId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['instalasiId']);
        }

        if(isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $filter['idRuangan']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('p.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }

        if(isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if(isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('p.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        if(isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $data = $data->take($filter['jmlRow']);
        }

        $data = $data->groupBy('pd.norec','apd.norec','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'kls.namakelas','jk.jeniskelamin','pg.namalengkap','pd.norec',
            'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','apd.tglmasuk','pp.tglpelayanan');

        // $data = $data->orderBy('pd.tglregistrasi','desc');
        // $data = $data->take(50);
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ridwan',
        );
        return $this->respond($result);
    }

    public function getPasienAmbulan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $ruanganLab = explode(',', $this->settingDataFixed('KdRuanganAmbulance', $kdProfile));
        $kdRuangLab = [];
        foreach ($ruanganLab as $item) {
            $kdRuangLab [] = (int)$item;
        }
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('strukorder_t as so', 'so.noregistrasifk', '=', 'pd.norec')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('batalregistrasi_t as br', 'pd.norec', '=', 'br.pasiendaftarfk')
            ->join ('antrianpasiendiperiksa_t as apd',function($join) use ($kdRuangLab) {
                $join->on('apd.noregistrasifk','=','pd.norec')
                    ->whereIn('apd.objectruanganfk', $kdRuangLab);
            })
//            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'p.id')
            ->select('pd.norec as norec_pd','apd.norec as norec_apd','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                'kls.namakelas','jk.jeniskelamin','pg.namalengkap as namadokter','pd.norec as norec_pd',
                'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','apd.tglmasuk','pp.tglpelayanan','rek.namarekanan','alm.alamatlengkap')
            ->where('pd.kdprofile', $kdProfile)
//            ->whereNull('p.tglmeninggal')
            ->whereNull('br.norec');

        $filter = $request->all();
        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
//            $data = $data->where('so.tglorder', '>=', $filter['tglAwal']);
//            $data = $data->where('apd.tglmasuk', '>=', $filter['tglAwal']);
            $data = $data->where('pp.tglpelayanan', '>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'] ;//. " 23:59:59";
//            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
//            $data = $data->where('so.tglorder', '<=', $tgl);
//            $data = $data->where('apd.tglmasuk', '<=', $tgl);
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        if(isset($filter['instalasiId']) && $filter['instalasiId'] != "" && $filter['instalasiId'] != "undefined") {
            $data = $data->where('dept.id', '=', $filter['instalasiId']);
        }

        if(isset($filter['idRuangan']) && $filter['idRuangan'] != "" && $filter['idRuangan'] != "undefined") {
            $data = $data->where('apd.objectruanganfk', '=', $filter['idRuangan']);
        }

        if(isset($filter['namaPasien']) && $filter['namaPasien'] != "" && $filter['namaPasien'] != "undefined") {
            $data = $data->where('p.namapasien', 'ilike', '%' . $filter['namaPasien'] . '%');
        }

        if(isset($filter['noRegis']) && $filter['noRegis'] != "" && $filter['noRegis'] != "undefined") {
            $data = $data->where('pd.noregistrasi', 'ilike', '%' . $filter['noRegis'] . '%');
        }
        if(isset($filter['noCm']) && $filter['noCm'] != "" && $filter['noCm'] != "undefined") {
            $data = $data->where('p.nocm', 'ilike', '%' . $filter['noCm'] . '%');
        }
        if(isset($filter['jmlRow']) && $filter['jmlRow'] != "" && $filter['jmlRow'] != "undefined") {
            $data = $data->take($filter['jmlRow']);
        }

        $data = $data->groupBy('pd.norec','apd.norec','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
            'kls.namakelas','jk.jeniskelamin','pg.namalengkap','pd.norec',
            'pd.tglpulang', 'pd.statuspasien','p.tgllahir','pd.objectruanganlastfk','apd.tglmasuk','pp.tglpelayanan','rek.namarekanan','alm.alamatlengkap');

        // $data = $data->orderBy('pd.tglregistrasi','desc');
        // $data = $data->take(50);
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getDataComboOperator(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('pg.kdprofile',$idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $dataInstalasi = \DB::table('departemen_m as dp')
//            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.kdprofile',$idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile',$idProfile)
            ->where('dept.id', '18')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->where('dept.kdprofile',$idProfile)
            ->whereIn('dept.id', [18, 16])
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile',$idProfile)
            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile',$idProfile)
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

    public function getDataDiagnosa(Request $request){
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
//            return $this->respond($countNoAntrian);
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

        $result = array(
            'dokter' => $dataDokter,
            'golongandarah' =>   $golonganDarah,
            'message' => 'inhuman',
        );
        return $this->respond($result);
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

    public function getDaftarRegistrasiPasienAmbulan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $ruanganLab = explode(',', $this->settingDataFixed('KdRuanganAmbulance', $kdProfile));
        $kdRuangLab = [];
        foreach ($ruanganLab as $item) {
            $kdRuangLab [] = (int)$item;
        }
        $filter = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
            ->leftJoin('alamat_m as alm', 'alm.nocmfk', '=', 'ps.id')
            ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJoin('jenispelayanan_m as jp','jp.kodeinternal','=','pd.jenispelayanan')
            ->select('pd.norec as norec_pd', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien',
                'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap as namadokter', 'pd.tglpulang', 'pd.statuspasien',
                'pa.norec as norec_pa', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk as pgid', 'pd.objectruanganlastfk',
                'pa.nosep as nosep', 'br.norec as norec_br', 'pd.nostruklastfk','pd.objectkelasfk','kls.namakelas',
                'ps.tgllahir','ps.objectjeniskelaminfk','jk.jeniskelamin','alm.alamatlengkap',
                'pd.jenispelayanan as idjenispelayanan','jp.jenispelayanan','ps.tglmeninggal')
            ->where('pd.kdprofile', $kdProfile)
            ->where('pd.statusenabled', true)
            ->whereNull('br.norec');
//            ->whereIn('ru.id', $kdRuangLab);
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
        $data = $data->groupBy('pd.norec', 'pd.statusenabled', 'pd.tglregistrasi', 'ps.nocm', 'pd.nocmfk', 'pd.noregistrasi',
            'ru.namaruangan', 'ps.namapasien',
            'kp.kelompokpasien', 'rek.namarekanan', 'pg.namalengkap', 'pd.tglpulang', 'pd.statuspasien',
            'pa.nosep', 'br.norec', 'pa.norec', 'pa.objectasuransipasienfk', 'pd.objectpegawaifk', 'pd.objectruanganlastfk',
            'pd.nostruklastfk', 'ps.tgllahir','pd.objectkelasfk','kls.namakelas','ps.objectjeniskelaminfk','jk.jeniskelamin',
            'alm.alamatlengkap','pd.jenispelayanan','jp.jenispelayanan','ps.tglmeninggal');
//        $data = $data->take($filter['jmlRows']);
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDataProduk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kdDetailJenisProduk = (int) $this->settingDataFixed('KdDetailJenisProdukAmbulance', $kdProfile);
        $dataLogin = $request->all();
        $data = \DB::table('produk_m as pr')
            ->JOIN('harganettoprodukbykelas_m as het','het.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','het.harganetto1 as harga')
            ->where('pr.kdprofile', $kdProfile)
            ->where('het.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->where('pr.objectdetailjenisprodukfk', $kdDetailJenisProduk)
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
                $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 10, 'NLA'.$this->getDateTime()->format('ym'), $kdProfile);
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
            $SP->namapasien_klien = $req['namaPasien_klien'];
            $SP->nostruk_intern = $req['nocm'];
            if (isset($req['notelp_klien'])){
                $SP->noteleponfaks = $req['notelp_klien'];
            }
            if (isset($req['alamat'])){
                $SP->namatempattujuan =  $req['alamat'];//alamat
            }
            if (isset($req['tglLahir'])){
                $SP->tglfaktur =  $req['tglLahir'];//tgllahir
            }
            $SP->tglstruk = $req['tglstruk'];
            $SP->totalharusdibayar = $req['totalharusdibayar'];
            $SP->save();

            $hargaTambahanP = 0;
            $hargaTambahanD = 0;

            foreach ($req['details'] as $item) {
                if ($req['perawat'] == true || $req['perawat'] == 'true'){
                    $hargaTambahanP = (float) $item['harga'] * 25/100;
                }
                if ($req['dokter'] == true || $req['dokter'] == 'true'){
                    $hargaTambahanD = (float) $item['harga'] * 25/100;
                }
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
                $SPD->hargatambahan = $hargaTambahanP + $hargaTambahanD;
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
                $SPD->isjasaperawat = $req['perawat'];
                $SPD->isjasadokter = $req['dokter'];
                $SPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Tagihan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $SP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Tagihan Gagal!!";
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

    public function daftarTagihanNonLayanan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kdPelayananAmbulan = (int) $this->settingDataFixed('KdKelompokTransaksiAmbulance', $kdProfile);
        $filter=$request->all();
        $datakelompokuser = \DB::table('loginuser_s as lu')
            ->select('lu.objectkelompokuserfk')
            ->where('lu.kdprofile', $kdProfile)
            ->where('lu.id','=',$filter['userData']['id'])
            ->get();

        $dataNonLayanan = \DB::table('strukpelayanan_t as sp')
            ->join('kelompoktransaksi_m as kt', 'sp.objectkelompoktransaksifk', '=', 'kt.id')
            ->select('sp.norec', 'sp.tglstruk', 'sp.namapasien_klien', 'kt.reportdisplay as jenistagihan',
                     'sp.totalharusdibayar', 'keteranganlainnya', 'sp.nosbklastfk', 'sp.nosbmlastfk', 'kt.id as jenisTagihanId',
                DB::raw("CASE WHEN sp.nosbmlastfk IS NOT NULL OR sp.nosbklastfk IS NOT NULL THEN 'Lunas' ELSE 'Belum Bayar' END AS statusbayar"))
            ->where('sp.kdprofile', $kdProfile)
            ->whereNotNull('sp.totalharusdibayar')
            ->where('sp.objectkelompoktransaksifk', $kdPelayananAmbulan);

        if(isset($filter['tglAwal']) && $filter['tglAwal']!=""){
            $dataNonLayanan = $dataNonLayanan->where('sp.tglstruk','>=', $filter['tglAwal']);
        }

        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!=""){
            $tgl= $filter['tglAkhir'];//." 23:59:59";
            $dataNonLayanan = $dataNonLayanan->where('sp.tglstruk','<=', $tgl);
        }

        if(isset($filter['namaPelanggan']) && $filter['namaPelanggan']!=""){
            $dataNonLayanan = $dataNonLayanan->where('sp.namapasien_klien','ilike','%'. $filter['namaPelanggan'] . '%');
        }

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

        $dataNonLayanan = $dataNonLayanan->where('sp.statusenabled','=',true);
        $dataNonLayanan = $dataNonLayanan->get();
        $result=[];
        foreach ($dataNonLayanan as $item){
            $details = DB::select(DB::raw("select pr.id,pr.namaproduk,spd.keteranganlainnya,spd.qtyproduk,spd.hargasatuan,
                     CASE WHEN isjasaperawat = true THEN spd.hargasatuan * 25/100 ELSE 0 END AS jasaperawat,
                     CASE WHEN isjasadokter = true THEN spd.hargasatuan * 25/100 ELSE 0 END AS jasadokter,
                     (spd.qtyproduk * spd.hargasatuan) + CASE WHEN isjasaperawat = true THEN spd.hargasatuan * 25/100 ELSE 0 END 
                     + CASE WHEN isjasadokter = true THEN spd.hargasatuan * 25/100 ELSE 0 END AS total
                     from strukpelayanandetail_t as spd 
                     left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                     where spd.kdprofile = $kdProfile and nostrukfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'noRec' => $item->norec,
                'tglTransaksi' => $item->tglstruk,
                'namaPelanggan' => $item->namapasien_klien,
                'jenisTagihan' => $item->jenistagihan,
                'total' => $item->totalharusdibayar,
                'keterangan' => $item->keteranganlainnya,
                'jenisTagihanId' => $item->jenisTagihanId,
                'statusBayar' => $item->statusbayar,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $result,
            'datalogin' => $datakelompokuser,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function detailTagihanNonLayanan(Request $request){
        $kdProfile = (int)$this->getDataKdProfile($request);
        $kdPelayananAmbulan = (int)$this->settingDataFixed('KdKelompokTransaksiAmbulance', $kdProfile);
        $noRec = $request['noRec'];
        $data = \DB::table('strukpelayanan_t as sp')
            ->join('kelompoktransaksi_m as kt', 'sp.objectkelompoktransaksifk', '=', 'kt.id')
            ->select('sp.norec', 'sp.tglstruk', 'sp.namapasien_klien', 'kt.reportdisplay as jenistagihan',
                     'sp.totalharusdibayar', 'keteranganlainnya', 'sp.nosbklastfk', 'sp.nosbmlastfk', 'kt.id as jenisTagihanId',
                     'sp.noteleponfaks as notelepon','sp.namatempattujuan as alamatlengkap','sp.tglfaktur as tgllahir','sp.keteranganlainnya',
                DB::raw("CASE WHEN sp.nosbmlastfk IS NOT NULL OR sp.nosbklastfk IS NOT NULL THEN 'Lunas' ELSE 'Belum Bayar' END AS statusbayar"))
            ->where('sp.kdprofile', $kdProfile)
            ->whereNotNull('sp.totalharusdibayar')
            ->where('sp.objectkelompoktransaksifk', $kdPelayananAmbulan)
            ->where('sp.norec', $noRec)
            ->first();

        $details = DB::select(DB::raw("select pr.namaproduk,spd.keteranganlainnya,spd.qtyproduk as jumlah,spd.hargasatuan as harga,
                     CASE WHEN isjasaperawat = true THEN spd.hargasatuan * 25/100 ELSE 0 END AS jasaperawat,
                     CASE WHEN isjasadokter = true THEN spd.hargasatuan * 25/100 ELSE 0 END AS jasadokter,
                     (spd.qtyproduk * spd.hargasatuan) + CASE WHEN isjasaperawat = true THEN spd.hargasatuan * 25/100 ELSE 0 END 
                     + CASE WHEN isjasadokter = true THEN spd.hargasatuan * 25/100 ELSE 0 END AS totalK,spd.isjasaperawat,spd.isjasadokter,
                     1 as qtyoranglast
                     from strukpelayanandetail_t as spd 
                     left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                     where spd.kdprofile = $kdProfile and nostrukfk=:norec"),
            array(
                'norec' => $noRec,
            )
        );

        $result = array(
            'data' => $data,
            'detail' => $details,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
}