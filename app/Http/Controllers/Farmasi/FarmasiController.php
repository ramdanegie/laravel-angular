<?php
//“Great men are not born great, they grow great . . .”
//― Mario Puzo, The Godfather
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 08/08/2017
 * Time: 12:48
 */

/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 09/08/2019
 * Time: 10:19
 */

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

use App\Transaksi\StrukOrder;
use App\Transaksi\StrukResep;
use App\Transaksi\OrderPelayanan;
use App\Master\LoginUser;



class FarmasiController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getDataComboDaftarPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $idProfile)
            ->whereIn('dp.id',$kdDepartemenRawatPelayanan)
            ->where('dp.statusenabled',true)
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
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
        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->whereIn('ru.objectdepartemenfk', [16,25])
            ->orderBy('ru.namaruangan')
            ->get();
        $dataJenisKelamin = \DB::table('jeniskelamin_m as jk')
            ->where('jk.statusenabled', true)
            ->orderBy('jk.jeniskelamin')
            ->get();
        $dataRuanganFarmasi = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->whereIn('ru.objectdepartemenfk', [14])
            ->orderBy('ru.namaruangan')
            ->get();

        $list = explode(',',$this->settingDataFixed('KdDepartemenPelayanan', $kdProfile));
        $kdDepartemenPelayanan = [];
        foreach ($list as $otem){
            $kdDepartemenPelayanan [] = (int)$otem;
        }
        $dataRuanganPelayanan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->whereIn('ru.objectdepartemenfk', $kdDepartemenPelayanan)
            ->orderBy('ru.namaruangan')
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' =>   $dataKelompok,
            'ruangan' => $dataRuangan,
            'jeniskelamin' => $dataJenisKelamin,
            'ruanganinap' => $dataRuanganInap,
            'ruanganfarmasi' => $dataRuanganFarmasi,
            'ruanganpelayanan' => $dataRuanganPelayanan,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->leftjoin('registrasipelayananpasien_t as rpp','pd.norec','=','rpp.noregistrasifk')
            ->JOIN('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->JOIN('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->JOIN('kelas_m as kl','kl.id','=','pd.objectkelasfk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
                'kp.id as kpid','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id as klid',
                'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','apd.norec','rpp.noregistrasifk as rpp','pd.nostruklastfk','pd.norec as norec_pd')
            ->where('apd.kdprofile', $idProfile)
            ->where('pd.statusenabled', true)
            ->whereNull('br.pasiendaftarfk')
            ->orderBy('pd.noregistrasi');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('pd.tglregistrasi','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir']." 23:59:59";
            $data = $data->where('pd.tglregistrasi','<=', $tgl);
        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $data = $data->where('ru.id', $request['ruid']);
        }
        if(isset($request['dpid']) && $request['dpid']!="" && $request['dpid']!="undefined"){
            $data = $data->where('dp.id', $request['dpid']);
        }
        if(isset($request['kpid']) && $request['kpid']!="" && $request['kpid']!="undefined"){
            $data = $data->where('kp.id', $request['kpid']);
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

        $data = $data->groupBy('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
            'kp.id','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id',
            'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','apd.norec','rpp.noregistrasifk','pd.nostruklastfk','pd.norec');

        $data = $data->take(50);
        $data = $data->get();
        return $this->respond($data);
    }
    public function getDaftarPasienRI(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('registrasipelayananpasien_t as rpp','pd.norec','=','rpp.noregistrasifk')
            ->leftjoin('antrianpasiendiperiksa_t as apd','pd.norec','=','apd.noregistrasifk')
            ->JOIN('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->JOIN('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->JOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->JOIN('kelas_m as kl','kl.id','=','pd.objectkelasfk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
                'kp.id as kpid','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id as klid',
                'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','rpp.noregistrasifk as rpp','pd.nostruklastfk','apd.norec','pd.norec as norec_pd')
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.statusenabled', true)
            ->whereNull('br.pasiendaftarfk')
            ->orderBy('pd.noregistrasi');

//        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
//            $data = $data->where('pd.tglregistrasi','>=', $request['tglAwal']);
//        }
//        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
//            $tgl= $request['tglAkhir']." 23:59:59";
//            $data = $data->where('pd.tglregistrasi','<=', $tgl);
//        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $data = $data->where('ru.id', $request['ruid']);
        }
//        if(isset($request['dpid']) && $request['dpid']!="" && $request['dpid']!="undefined"){
//            $data = $data->where('dp.id', $request['dpid']);
//        }
//        if(isset($request['kpid']) && $request['kpid']!="" && $request['kpid']!="undefined"){
//            $data = $data->where('kp.id', $request['kpid']);
//        }
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','ilike','%'. $request['noregistrasi'].'%');
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $request['nocm'].'%');
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'. $request['namapasien'].'%');
        }

        $data = $data->groupBy('ru.namaruangan','pd.noregistrasi','ps.nocm','ps.namapasien','jk.jeniskelamin',
            'kp.id','kp.kelompokpasien','rk.namarekanan','kl.namakelas','kl.id',
            'pd.tglregistrasi','pd.tglpulang','ps.tgllahir','rpp.noregistrasifk','pd.nostruklastfk','apd.norec','pd.norec');
        $data = $data->where('dp.id','=',16);
        $data = $data->whereNull('pd.tglpulang');
        $data = $data->whereNull('apd.tglkeluar');
        $data = $data->where('pd.tglregistrasi','>','2019-05-25 00:00');
        $data = $data->take(50);
        $data = $data->get();
        return $this->respond($data);
    }
    public function getDaftarResep(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('strukresep_t as sr')
            ->JOIN('antrianpasiendiperiksa_t as apd','apd.norec','=','sr.pasienfk')
            ->JOIN('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->JOIN('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->JOIN('pegawai_m as pg','pg.id','=','sr.penulisresepfk')
            ->JOIN('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->JOIN('departemen_m as dp','dp.id','=','ru.objectdepartemenfk')
            ->JOIN('ruangan_m as ru2','ru2.id','=','sr.ruanganfk')
            ->JOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->JOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->JOIN('kelas_m as kl','kl.id','=','pd.objectkelasfk')
            ->JOIN('ruangan_m as ru1','ru1.id','=','sr.ruanganfk')
            ->select('sr.norec','sr.statusenabled','sr.noresep','sr.tglresep','ps.id as psid','pd.noregistrasi','ps.nocm','ps.namapasien','ru.id as ruid',
                'ru.namaruangan','pg.id as pgid','pg.namalengkap as dokter','ru2.id as ruapotikid',
                'ru2.namaruangan as namaruanganapotik','jk.jeniskelamin','ps.tgllahir','kl.id as klid','kl.namakelas',
                'pd.tglregistrasi','apd.norec as norec_apd','kp.kelompokpasien')
            ->where('sr.kdprofile', $idProfile)
            ->where('pd.statusenabled', true);
//            ->where('sr.statusenabled' != 'f');
        //where('sr.statusenabled' != 'f');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sr.tglresep','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sr.tglresep','<=', $tgl);
        }
        if(isset($request['noresep']) && $request['noresep']!="" && $request['noresep']!="undefined"){
            $data = $data->where('sr.noresep','ilike','%'. $request['noresep']);
        }
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noregistrasi']);
        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $data = $data->where('ru.id', $request['ruid']);
        }
        if(isset($request['dpid']) && $request['dpid']!="" && $request['dpid']!="undefined"){
            $data = $data->where('dp.id', $request['dpid']);
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $request['nocm'].'%');
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('ps.namapasien','ilike','%'. $request['namapasien'].'%');
        }
        if(isset($request['status']) && $request['status']!="" && $request['status']!="undefined"){
            $data = $data->where('sr.status', $request['status']);
        }
        if ($request['noresep']!="" && $request['noregistrasi']!="" && $request['ruid']!="" && $request['dpid']!=""
            && $request['noresep']!="" && $request['namapasien']!="" && $request['status']!=""){
        }
        if(isset($request['IdFarmasi']) && $request['IdFarmasi']!="" && $request['IdFarmasi']!="undefined"){
            $data = $data->where('sr.ruanganfk', $request['IdFarmasi']);
        }
//        if(isset($request['jenisobatfk']) && $request['jenisobatfk']!="" && $request['jenisobatfk']!="undefined"){
//            $data = $data->where('pp.jenisobatfk', $request['jenisobatfk']);
//        }
//        $data = $data->where('sr.statusenabled',t);
//        $data = $data->orwhere('sr.statusenabled',null);
        $data = $data->where(function($q) {
            $q->where('sr.statusenabled', true)
                ->orWhere('sr.statusenabled', null);
        });
        $data = $data->take($request['jmlRows']);
        $data = $data->orderby('sr.noresep');
        $data = $data->get();
//        foreach ($data as $item){
//
//            $pelayananpasien = \DB::table('strukresep_t as sr')
//                ->JOIN('pelayananpasien_t as pp','pp.strukresepfk','=','sr.norec')
//                ->JOIN('jeniskemasan_m as jk','jk.id','=','pp.jeniskemasanfk')
//                ->select('sr.noresep','jk.id as jkid','jk.jeniskemasan')
//                ->where('sr.norec',$item->norec )
//                ->where('jk.id',1 )//racikan
//                ->first();
//            if(!empty($pelayananpasien)){
//                $item->jenis = 'Racikan';
//            }else{
//                $item->jenis = 'Non Racikan';
//            }
//        }
        $result = array(
            'daftar' => $data,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarReturObat(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('strukretur_t as srt')
            ->LEFTJOIN('strukresep_t as sr','sr.norec','=','srt.strukresepfk')
            ->LEFTJOIN('antrianpasiendiperiksa_t as apd','apd.norec','=','sr.pasienfk')
            ->LEFTJOIN('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->LEFTJOIN('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','srt.objectpegawaifk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','srt.objectruanganfk')
            ->LEFTJOIN('strukpelayanan_t as sp','sp.norec','=','srt.strukresepfk')
            ->select('srt.tglretur','srt.noretur','pd.noregistrasi','ps.nocm','ps.namapasien','pg.namalengkap',
                'ru.namaruangan','srt.norec','srt.keteranganlainnya','sp.nostruk_intern as nocm_sp','sp.namapasien_klien as namapasien_sp'
            )
            ->where('srt.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('srt.tglretur','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('srt.tglretur','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('srt.noretur','ilike','%'. $request['nostruk']);
        }
//        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
//            $data = $data->where('sp.nostruk_intern','like','%'. $request['nocm'].'%');
//        }
//        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
//            $data = $data->where('sp.namapasien_klien','like','%'. $request['namapasien'].'%');
//        }
        $data = $data->where('srt.statusenabled',true);
        $data = $data->orderBy('srt.noretur');
        $data = $data->get();
        $results = [];
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                select spd.tglpelayanan, spd.rke,jkm.jeniskemasan,pr.namaproduk,
                ss.satuanstandar,spd.jumlah,spd.hargasatuan,spd.hargadiscount,
                spd.jasa,((spd.hargasatuan-spd.hargadiscount)*spd.jumlah)+spd.jasa as total from pelayananpasienretur_t as spd
                INNER JOIN produk_m as pr on pr.id=spd.produkfk
                INNER JOIN jeniskemasan_m as jkm on jkm.id=spd.jeniskemasanfk
                INNER JOIN satuanstandar_m as ss on ss.id=spd.satuanviewfk
                where spd.kdprofile = $idProfile and strukreturfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $namapasien = $item->namapasien;
            $nocm = $item->nocm;
            $noregistrasi = $item->noregistrasi;
            if( is_null($item->noregistrasi )){
                $namapasien =  $item->namapasien_sp;
                $nocm = $item->nocm_sp;
                $noregistrasi = '-';
            }

            $results[] = array(
                'tglretur' => $item->tglretur,
                'noretur' => $item->noretur,
                'noregistrasi' => $noregistrasi,
                'nocm' =>$nocm,
                'namapasien' => $namapasien,
                'namalengkap' => $item->namalengkap,
                'namaruangan' => $item->namaruangan,
                'norec' => $item->norec,
                'keteranganlainnya' => $item->keteranganlainnya,
//                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarPaketObatPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $data = [];
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->LEFTJOIN('antrianpasiendiperiksa_t as apd','apd.norec','=','sp.noregistrasifk')
            ->LEFTJOIN('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->LEFTJOIN('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap',
                'ru.namaruangan as ruanganasal','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim','ps.namapasien',
                'pd.noregistrasi','sp.noregistrasifk'
            )
            ->where('mlu.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ilike','%'. $request['nokirim']);
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.id','=', $request['ruangantujuanfk']);
        }
        if(isset($request['noregistrasi']) && $request['noregistrasi']!="" && $request['noregistrasi']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noregistrasi']);
        }
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',34);
//        $data = $data->wherein('sp.objectruanganasalfk',$strRuangan);
        $data = $data->wherenotnull('sp.noregistrasifk');
        $data = $data->where('sp.noregistrasifk','<>','0');
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk
                     from kirimproduk_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nokirimfk=:norec and spd.qtyproduk <> 0"),
                array(
                    'norec' => $item->norec,
                )
            );
            $jeniskirim ='';
            if ($item->jenispermintaanfk == 1){
                $jeniskirim ='Amprahan';
            }
            if ($item->jenispermintaanfk == 2){
                $jeniskirim ='Transfer';
            }
            $results[] = array(
                'status' => 'Kirim Barang',
                'tglstruk' => $item->tglkirim,
                'nostruk' => $item->nokirim,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'namaruanganasal' => $item->ruanganasal,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnyakirim,
                'namapasien' => $item->namapasien,
                'noregistrasi' => $item->noregistrasi,
                'norec_apd' => $item->noregistrasifk,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
            'str' => $strRuangan,
        );

        return $this->respond($result);
    }

    public function getDaftarReturObatDetail(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukretur_t as srt')
            ->leftJoin('strukresep_t as sr','sr.norec','=','srt.strukresepfk')
            ->leftJoin('antrianpasiendiperiksa_t as apd','apd.norec','=','sr.pasienfk')
            ->leftJoin('strukpelayanan_t as sp','sp.norec','=','srt.strukresepfk')
            ->leftJoin('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->leftJoin('pelayananpasienretur_t as spd','spd.strukreturfk','=', 'srt.norec')
            ->join('produk_m as pr','pr.id','=','spd.produkfk')
            ->join('jeniskemasan_m as jkm','jkm.id','=','spd.jeniskemasanfk')
            ->join('satuanstandar_m as ss','ss.id','=','spd.satuanviewfk')
            ->leftJoin('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','srt.objectpegawaifk')
            ->leftJoin('ruangan_m as ru','ru.id','=','srt.objectruanganfk')
            ->leftJoin('ruangan_m as ru1','ru1.id','=','apd.objectruanganfk')
            ->select(DB::raw("srt.tglretur,srt.noretur,CASE WHEN pd.noregistrasi IS NULL THEN '-' ELSE pd.noregistrasi END AS noregistrasi,
                    CASE WHEN ps.nocm IS NULL THEN sp.nostruk_intern ELSE ps.nocm END AS nocm,
                    CASE WHEN ps.namapasien IS NULL THEN sp.namapasien_klien ELSE ps.namapasien END AS namapasien,
                    CASE WHEN ru1.namaruangan IS NULL THEN '-' ELSE ru1.namaruangan END as unitlayanan,
                    ps.namapasien,pg.namalengkap,ru.namaruangan AS depo,srt.norec,srt.keteranganlainnya,
                    spd.tglpelayanan, spd.rke,jkm.jeniskemasan,pr.namaproduk,ss.satuanstandar,spd.jumlah,spd.hargasatuan,
                    spd.hargadiscount,spd.jasa,((spd.hargasatuan-spd.hargadiscount)*spd.jumlah)+spd.jasa as total"))
            ->where('srt.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('srt.tglretur','>=', $request['tglAwal']);
        }

        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('srt.tglretur','<=', $tgl);
        }

        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('srt.noretur','ilike','%'. $request['nostruk']);
        }

        if(isset($request['idDepo']) && $request['idDepo']!="" && $request['idDepo']!="undefined"){
            $data = $data->where('ru.id','=', $request['idDepo']);
        }

        if(isset($request['idRuangLayanan']) && $request['idRuangLayanan']!="" && $request['idRuangLayanan']!="undefined"){
            $data = $data->where('ru1.id','=', $request['idRuangLayanan']);
        }

        $data = $data->where('srt.statusenabled',true);
        $data = $data->orderBy('srt.noretur');
        $data = $data->get();

        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarOrderProduksiSteril(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('strukorder_t as so')
            ->JOIN('pasien_m as ps','ps.id','=','so.nocmfk')
            ->JOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->JOIN('ruangan_m as ru','ru.id','=','so.objectruanganfk')
            ->JOIN('ruangan_m as ru2','ru2.id','=','so.objectruangantujuanfk')
            ->leftJOIN('pegawai_m as pg','pg.id','=','so.objectpegawaiorderfk')
            ->JOIN('pasiendaftar_t as pd','pd.norec','=','so.noregistrasifk')
            ->JOIN('kelas_m as kl','kl.id','=','pd.objectkelasfk')
            ->leftJOIN('antrianpasiendiperiksa_t as apd', function($join){
                $join->on('apd.noregistrasifk','=','pd.norec')
                    ->on('apd.objectruanganfk', '=', 'so.objectruanganfk')
                    ->on('apd.objectpegawaifk', '=', 'so.objectpegawaiorderfk');
            })
            ->leftJOIN('kelompokpasien_m as kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->select('so.noorder','ps.nocm','ps.namapasien','jk.jeniskelamin','ru.namaruangan as namaruanganrawat',
                'so.tglorder','pg.namalengkap','ru2.namaruangan',
                'so.statusorder','so.namapengambilorder','so.noregistrasifk',
                'pd.noregistrasi','kp.kelompokpasien',
                'apd.norec as norec_apd',
                'pd.tglregistrasi','ps.tgllahir','kl.namakelas','kl.id as klid','so.tglambilorder')
            ->where('so.kdprofile', $idProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('so.tglorder','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir']." 23:59:59";
            $data = $data->where('so.tglorder','<=', $tgl);
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm','ilike','%'. $request['nocm'].'%');
        }
        if(isset($request['norec_apd']) && $request['norec_apd']!="" && $request['norec_apd']!="undefined"){
            $data = $data->where('apd.norec', $request['norec_apd']);
        }
        $data = $data->where('so.keteranganorder','=', 'Order Produksi Steril');
        $data = $data->where('so.objectkelompoktransaksifk', 4);
        $data = $data->get();
        $status ='';

        $result=[];
        foreach ($data as $item){
            if ($item->statusorder == 0){$status='Menunggu';};
            if ($item->statusorder == 5){$status='Verifikasi';};
            if ($item->statusorder == 1){$status='Produksi';};
            if ($item->statusorder == 2){$status='Packaging';};
            if ($item->statusorder == 3){$status='Selesai';};
            if ($item->statusorder == 4){$status='Penyerahan Obat';};
            if ($item->tglambilorder != null){$status='Sudah Di Ambil';};
            $result[] = array(
                'noregistrasi' => $item->noregistrasi,
                'norec' => $item->noregistrasifk,
                'tglregistrasi' => $item->tglregistrasi,
                'norec_apd' => $item->norec_apd,
                'noorder' => $item->noorder,
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'jeniskelamin' => $item->jeniskelamin,
                'namaruanganrawat' => $item->namaruanganrawat,
                'tglorder' => $item->tglorder,
                'namalengkap' => $item->namalengkap,
                'kelompokpasien' => $item->kelompokpasien,
                'namaruangan' => $item->namaruangan,
                'statusorder' => $status,
                'namapengambilorder' => $item->namapengambilorder,
                'tgllahir' => $item->tgllahir,
                'klid' => $item->klid,
                'namakelas' => $item->namakelas,
            );
        }

        return $this->respond($result);
    }
}