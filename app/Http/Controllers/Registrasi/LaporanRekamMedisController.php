<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 8/15/2019
 * Time: 3:47 PM
 */

namespace App\Http\Controllers\Registrasi;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;
use Webpatser\Uuid\Uuid;
use App\Master\CaraBayar;
use App\Master\JenisLaporan;
use App\Master\KelompokLaporan;
use App\Master\MapProdukToLaporanRl;
use App\Master\Ruangan;
use App\Master\Pegawai;

class LaporanRekamMedisController extends   ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);

    }
    public function getMapLaporanRL(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('mapproduktolaporanrl_m as mptlr')
            ->join('produk_m as pro','pro.id','=','mptlr.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mptlr.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mptlr.objectkontenlaporanfk')
            ->select('mptlr.norec','pro.id as idproduk','pro.namaproduk','jl.id','jl.jenislaporan',
                'kl.id as idkelompoklaporan','kl.kelompoklaporan')
            ->where('mptlr.kdprofile', $idProfile)
            ->where('mptlr.statusenabled',true)
            ->orderBy('jl.id','asc');

        if(isset($request['idKonten']) && $request['idKonten'] != "" && $request['idKonten'] != "undefined") {
            $data = $data->where('kl.id','=', $request['idKonten']);
        }
//         if(isset($request['idMap']) && $request['idMap'] != "" && $request['idMap'] != "undefined") {
//            $data = $data->where('mptlr.norec','=', $request['idMap']);
//         }

        $data = $data->get();
        return $this->respond($data);
    }
    public function getComboMappingRL(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $kdDeptRanapAll = explode(',',$this->settingDataFixed('KdDepartemenRIAll',$idProfile));
        $kdDepartemenRanapAll = [];
        foreach ($kdDeptRanapAll as $items){
            $kdDepartemenRanapAll []=  (int)$items;
        }
        $dataPenulis = Pegawai::where('statusenabled',true)
            ->where('objectjenispegawaifk',$kdJeniPegawaiDokter)
            ->where('kdprofile', $idProfile)
            ->get();

        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }

        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id','ru.namaruangan')
            ->where('mlu.kdprofile', $idProfile)
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();

        $dataRuanganall = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataRuanganPelayanan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.objectdepartemenfk',16)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();
        
        $dataRuanganRajal = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk',[18,24])
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();


        $dataCaraBayar = \DB::table('carabayar_m as cb')
            ->select('cb.id','cb.carabayar')
            ->where('cb.kdprofile', $idProfile)
            ->where('cb.statusenabled',true)
            ->get();

        $dataJenisLaporan = \DB::table('jenislaporan_m as jl')
            ->select('jl.id','jl.jenislaporan')
            ->where('jl.kdprofile', $idProfile)
            ->where('jl.statusenabled',true)
            ->get();

        $dataKelompokLaporan = \DB::table('kelompoklaporan_m as kl')
            ->select('kl.id','kl.kelompoklaporan')
            ->where('kl.kdprofile', $idProfile)
            ->where('kl.statusenabled',true)
            ->get();

        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $result = array(
           'ruanganranap' =>   $dataRuanganPelayanan,
//            'ruangan' => $dataRuangan,
            'ruanganrajal' => $dataRuanganRajal,
            'ruanganall' => $dataRuanganall,
//            'produk' => $dataProduk,
//            'caraBayar' => $dataCaraBayar,
            'jenisLaporan' => $dataJenisLaporan,
            'kelompokLaporan' => $dataKelompokLaporan,
            'detaillogin' => $dataPegawaiUser,
            'message' => 'as@cepot',
        );

        return $this->respond($result);
    }
    public function deleteMapProdukToLaporanRL(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        if ($request['mapping']['norec'] != ''){
            try{
                $data1 = MapProdukToLaporanRl::where('norec', $request['mapping']['norec'])->where('kdprofile', $idProfile)->delete();
                $transStatus = 'true';
            }
            catch(\Exception $e){
                $transStatus= false;
            }
        }
        if ($transStatus='true')
        {    DB::commit();
            $transMessage = "Data Terhapus";
        }
        else{
            DB::rollBack();
            $transMessage = "Data Gagal Dihapus";
        }
        return $this->setStatusCode(201)->respond([], $transMessage);
    }
    public function getProdukMapLaporanRL(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdPelayanan = explode(',',$this->settingDataFixed('KdlistPelayanan',$idProfile));
        $kdListPelayanan = [];
        foreach ($kdPelayanan as $items){
            $kdListPelayanan []=  (int)$items;
        }
        $dataProduk = \DB::table('produk_m as pro')
            ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pro.objectdetailjenisprodukfk')
            ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJoin('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->select('pro.id as idproduk','pro.namaproduk','djp.id as iddetail','djp.detailjenisproduk',
                'jp.id as idjenis','jp.jenisproduk','jp.objectkelompokprodukfk','kp.kelompokproduk')
            ->where('pro.kdprofile', $idProfile)
            ->whereIn('jp.objectkelompokprodukfk',$kdListPelayanan)
            ->where('pro.statusenabled',true)
            ->orderBy('pro.namaproduk');
        if(isset($request['filter']['filters'][0]['value']) &&
            $request['filter']['filters'][0]['value']!="" &&
            $request['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('pro.namaproduk','ilike','%'. $request['filter']['filters'][0]['value'].'%' );
        };

        $dataProduk=$dataProduk->take(10);
        $dataProduk=$dataProduk->get();
//        $result = array(
//            'data'=>$dataProduk,
//            'message' => 'as@cepot',
//        );

        return $this->respond($dataProduk);
    }

    public function SaveMappingRl(Request $request) {
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req = $request;
        try {
//        return $this->respond($req);
        if ($request['norec'] == '') {
            $newMap = new MapProdukToLaporanRl();
            $norecMap = $newMap->generateNewId();
            $newMap->norec = $norecMap;
            $newMap->kdprofile = $idProfile;
            $newMap->statusenabled = true;
        }else{
            $newMap = MapProdukToLaporanRl::where('norec',$request['norec'])->first();
        }
        $newMap->objectjenislaporanfk = $request['objectjenislaporanfk'];
        $newMap->objectkontenlaporanfk = $request['objectkelompokkontenfk'];
        $newMap->produkfk = $request['produkfk'];

          $newMap->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Mapping Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "map" => $newMap,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Mapping Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "map" => $newMap,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataRL31RawatInap(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptId = explode (',',$this->settingDataFixed('KdListDepartemen',$idProfile));
        $KdJenisLaporan = (int) $this->settingDataFixed('KdJenisLapRanap', $idProfile);
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptId as $items){
            $kdDepartemenRawatPelayanan []=  (int)$items;
        }
        $data = \DB::table('antrianpasiendiperiksa_t as app')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'app.noregistrasifk')
            ->join('asalrujukan_m as ar', 'ar.id', '=', 'app.objectasalrujukanfk')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'app.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'app.norec')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->join ('kelas_m as kls','kls.id','=','app.objectkelasfk')
            ->leftjoin ('statuskeluar_m as sk','sk.id','=','pd.objectstatuskeluarfk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'pm.objectjeniskelaminfk')
            ->select('pd.tglregistrasi', 'pm.nocm', 'pd.noregistrasi', 'pm.namapasien', 'app.objectasalrujukanfk',
                'ar.asalrujukan', 'ru.namaruangan', 'dpm.namadepartemen', 'pg.namalengkap', 'pm.objectjeniskelaminfk',
                'ru.objectdepartemenfk','pd.objectstatuskeluarfk as objectstatuskeluarfk','pd.objectkelasfk as objectkelasfk',
                'kl.kelompoklaporan as jenis_spesialisasi',
//                DB::raw('
//                            case when pd.tglpulang is null then  EXTRACT(Day FROM CURRENT_DATE) - EXTRACT(day FROM pd.tglregistrasi)
//                            else EXTRACT(Day FROM pd.tglpulang) - EXTRACT(day FROM pd.tglregistrasi) end as lamadirawat,
//                             EXTRACT(MONTH FROM pd.tglregistrasi) as bulanregistrasi'
//                )

                DB::raw("
                CASE
                    WHEN pd.tglpulang IS NULL THEN
                    DATE_PART('day', now() - pd.tglregistrasi::timestamp)
                    ELSE
                    DATE_PART('day', pd.tglpulang::TIMESTAMP - pd.tglregistrasi::timestamp)
                    END AS lamadirawat,
                    to_char( pd.tglregistrasi,'MM') as bulanregistrasi"
                )

            )
            ->where('app.kdprofile', $idProfile)
            ->whereIn('dpm.id',$kdDepartemenRawatPelayanan)
            ->where('jl.id',$KdJenisLaporan);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $sama = false;

        $pasienAwalTahun = 0;
        $pasienMasuk = 0;
        $pasienKeluarHidup = 0;
        $matilebih48jam = 0;
        $matikurang48jam=0;
        $jumlahLamaDirawat=0;
        $pasienAkhirTahun=0;
        $jumlahHariDirawat=0;
        $rincianVipB=0;
        $rincianVipA=0;
        $rincianKel1=0;
        $rincianKel2=0;
        $rincianKel3=0;
        $rincianKelNonKelas=0;
        $DiTerimaKembali = 0;


        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->jenis_spesialisasi == $data10[$i]['jenis_spesialisasi']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;

                    if ($item->bulanregistrasi==1 )
                    {
                        $data10[$i]['pasienawaltahun'] = (float)$hideung['pasienawaltahun'] + 1;
                    }
                    else if ($item->bulanregistrasi==12 )
                    {
                        $data10[$i]['pasienakhirtahun'] = (float)$hideung['pasienakhirtahun'] + 1;
                    }
                    else if ($item->objectdepartemenfk==16 ||$item->objectdepartemenfk==25)
                    {
                        $data10[$i]['pasienmasuk'] = (float)$hideung['pasienmasuk'] + 1;
                    }
                    elseif ($item->objectstatuskeluarfk != 5)
                    {
                        $data10[$i]['pasienkeluarhidup'] = (float)$hideung['pasienkeluarhidup'] + 1;
                    }
                    elseif ($item->objectstatuskeluarfk == 5 && $item->lamadirawat < 48 )
                    {
                        $data10[$i]['matikurang48jam'] = (float)$hideung['matikurang48jam'] + 1;
                    }
                    elseif ($item->objectstatuskeluarfk == 5 && $item->lamadirawat > 48 )
                    {
                        $data10[$i]['matilebih48jam'] = (float)$hideung['matilebih48jam'] + 1;
                    }

                    if ($item->objectkelasfk == 8 )
                    {
                        $data10[$i]['rincianvipb'] = (float)$hideung['rincianvipb'] + 1;
                    }
                    elseif ($item->objectkelasfk == 5 )
                    {
                        $data10[$i]['rincianvipa'] = (float)$hideung['rincianvipa'] + 1;
                    }
                    elseif ($item->objectkelasfk == 3 )
                    {
                        $data10[$i]['rinciankel1'] = (float)$hideung['rinciankel1'] + 1;
                    }
                    elseif ($item->objectkelasfk == 2 )
                    {
                        $data10[$i]['rinciankel2'] = (float)$hideung['rinciankel2'] + 1;
                    }
                    elseif ($item->objectkelasfk == 1 )
                    {
                        $data10[$i]['rinciankel3'] = (float)$hideung['rinciankel3'] + 1;
                    }
                    elseif ($item->objectkelasfk == 6 )
                    {
                        $data10[$i]['rinciannonkelas'] = (float)$hideung['rinciannonkelas'] + 1;
                    }
//                    $data10[$i]['total'] = $data10[$i]['jmlBaruL'] + $data10[$i]['jmlBaruP'];
                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->bulanregistrasi==1)
                {
                    $pasienAwalTahun = 1;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                else if ($item->bulanregistrasi==12)
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=1;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                else if ($item->objectdepartemenfk==16 ||$item->objectdepartemenfk==25)
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 1;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectstatuskeluarfk != 5)
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 1;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectstatuskeluarfk == 5 && $item->lamadirawat < 48 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=1;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectstatuskeluarfk == 5 && $item->lamadirawat > 48 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 1;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                if ($item->objectkelasfk == 8 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=1;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectkelasfk == 5)
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=1;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectkelasfk == 3 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=1;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectkelasfk == 2 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=1;
                    $rincianKel3=0;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectkelasfk == 1 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=1;
                    $rincianKelNonKelas=0;
                    $DiTerimaKembali = 0;
                }
                elseif ($item->objectkelasfk == 6 )
                {
                    $pasienAwalTahun = 0;
                    $pasienMasuk = 0;
                    $pasienKeluarHidup = 0;
                    $matilebih48jam = 0;
                    $matikurang48jam=0;
                    $jumlahLamaDirawat=0;
                    $pasienAkhirTahun=0;
                    $jumlahHariDirawat=0;
                    $rincianVipB=0;
                    $rincianVipA=0;
                    $rincianKel1=0;
                    $rincianKel2=0;
                    $rincianKel3=0;
                    $rincianKelNonKelas=1;
                    $DiTerimaKembali = 0;
                }

                $data10[] = array(
                    'jenis_spesialisasi' => $item->jenis_spesialisasi,
                    'pasienmasuk' => $pasienMasuk,
                    'lamadirawat'=>$item->lamadirawat,
                    'pasienawaltahun'=>$pasienAwalTahun,
                    'pasienakhirtahun'=>$pasienAkhirTahun,
                    'pasienkeluarhidup'=> $pasienKeluarHidup,
                    'matilebih48jam' => $matilebih48jam,
                    'matikurang48jam' => $matikurang48jam,
                    'jumlahlamadirawat' => $jumlahLamaDirawat,
                    'pasienakhirtahun' => $pasienAkhirTahun,
                    'jumlahharidirawat' => $jumlahHariDirawat,
                    'rincianvipb' => $rincianVipB,
                    'rincianvipa' => $rincianVipA,
                    'rinciankel1' => $rincianKel1,
                    'rinciankel2' => $rincianKel2,
                    'rinciankel3' => $rincianKel3,
                    'rinciannonkelas' => $rincianKelNonKelas,
                    'jumlah' => 1,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'as@cepot',
        );
        return $this->respond($result);
    }
    public function getLaporanRL32RawatDarurat(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdDeptIGD = (int) $this->settingDataFixed('KdDepartemenInstalasiGawatDarurat', $idProfile);
        $KdJenisLapIGD = (int) $this->settingDataFixed('KdJenisLapIGD', $idProfile);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftjoin('asalrujukan_m as ar', 'ar.id', '=', 'apd.objectasalrujukanfk')
            ->leftJoin('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->select('pd.objectruanganasalfk as objectruanganasalfk ', 'pd.objectruanganlastfk as objectruanganlastfk', 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'pr.objectdetailjenisprodukfk',
                'djp.detailjenisproduk', 'apd.objectasalrujukanfk', 'ar.asalrujukan', 'pd.objectstatuskeluarfk',
                'kl.kelompoklaporan as jenispelayanan'
//                DB::raw('(case when jp.id in(16,46)  then \'Bedah\'
//                        when jp.id <>16 and jp.id <>46 then \'Non Bedah\'
//                        when jp.id =15 then \'Kebidanan\' end) as jenispelayanan')
            )
            ->where('pd.kdprofile', $idProfile)
            ->where('dpm.id', $kdDeptIGD)
            ->where('pd.objectruanganasalfk', 36)
            ->where('jl.id', $KdJenisLapIGD);
//            ->whereNotIn('jp.id', [99, 97, 14]);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $rujukan = 0;
        $nonrujukan = 0;
        $dirawat = 0;
        $dirujuk = 0;
        $pulang = 0;
        $mati = 0;
        $doa = 0;
        $sama = false;


        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            $o = 0;
            foreach ($data10 as $hideung) {
                if ($item->jenispelayanan == $data10[$i]['jenispelayanan']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
                    if ($item->objectasalrujukanfk == 5) {
                        $data10[$i]['nonrujukan'] = (float)$hideung['nonrujukan'] + 1;
                    } else if ($item->objectasalrujukanfk != 5) {
                        $data10[$i]['rujukan'] = (float)$hideung['rujukan'] + 1;
                    }
                    if ($item->objectruanganlastfk != 36) {
                        $data10[$i]['dirawat'] = (float)$hideung['dirawat'] + 1;
                    } else if ($item->objectstatuskeluarfk == 4) {
                        $data10[$i]['dirujuk'] = (float)$hideung['dirujuk'] + 1;
                    } else if ($item->objectruanganlastfk == 36) {
                        $data10[$i]['pulang'] = (float)$hideung['pulang'] + 1;
                    } else if ($item->objectstatuskeluarfk == 5 && $item->objectruanganlastfk == 36) {
                        $data10[$i]['mati'] = (float)$hideung['mati'] + 1;
                    } else if ($item->objectstatuskeluarfk == 123456) {
                        $data10[$i]['doa'] = (float)$hideung['doa'] + 1;
                    }

                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->objectasalrujukanfk == 5) {
                    $rujukan = 0;
                    $nonrujukan = 1;
                    $dirawat = 0;
                    $dirujuk = 0;
                    $pulang = 0;
                    $mati = 0;
                    $doa = 0;
                } else if ($item->objectasalrujukanfk != 5) {
                    $rujukan = 1;
                    $nonrujukan = 0;
                    $dirawat = 0;
                    $dirujuk = 0;
                    $pulang = 0;
                    $mati = 0;
                    $doa = 0;
                }
                if ($item->objectruanganlastfk != 36) {
                    $rujukan = 0;
                    $nonrujukan = 0;
                    $dirawat = 1;
                    $dirujuk = 0;
                    $pulang = 0;
                    $mati = 0;
                    $doa = 0;
                } else if ($item->objectstatuskeluarfk == 4) {
                    $rujukan = 0;
                    $nonrujukan = 0;
                    $dirawat = 0;
                    $dirujuk = 1;
                    $pulang = 0;
                    $mati = 0;
                    $doa = 0;

                } else if ($item->objectstatuskeluarfk == 5 && $item->objectruanganlastfk == 36) {
                    $rujukan = 0;
                    $nonrujukan = 0;
                    $dirawat = 0;
                    $dirujuk = 0;
                    $pulang = 0;
                    $mati = 1;
                    $doa = 0;
                } else if ($item->objectruanganlastfk == 36) {
                    $rujukan = 0;
                    $nonrujukan = 0;
                    $dirawat = 0;
                    $dirujuk = 0;
                    $pulang = 1;
                    $mati = 0;
                    $doa = 0;
                } else if ($item->objectstatuskeluarfk == 123456) {
                    $rujukan = 0;
                    $nonrujukan = 0;
                    $dirawat = 0;
                    $dirujuk = 0;
                    $pulang = 0;
                    $mati = 0;
                    $doa = 1;
                }

                $data10[] = array(
//                    'produkfk' => $item->produkfk,
                    'jenispelayanan' => $item->jenispelayanan,
                    'jumlah' => 1,
                    'rujukan' => $rujukan,
                    'nonrujukan' => $nonrujukan,
                    'dirawat' => $dirawat,
                    'dirujuk' => $dirujuk,
                    'pulang' => $pulang,
                    'mati' => $mati,
                    'doa' => $doa,

                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }

    public function getKegiatanKesehatanGigidanMulut(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $KdJenisLapGigiMulut = (int) $this->settingDataFixed('KdJenisLapGigiMulut',$idProfile);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->join('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->join('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->join('mapproduktolaporanrl_m as mpptrl','mpptrl.produkfk','=','pr.id')
            ->join('kelompoklaporan_m as kl','kl.id','=','mpptrl.objectkontenlaporanfk')
            ->join('jenislaporan_m as jl','jl.id','=','mpptrl.objectjenislaporanfk')
            ->select('jl.jenislaporan','kl.kelompoklaporan',
                (DB::raw('COUNT(mpptrl.objectkontenlaporanfk) as jumlah'))
//            ->select('pp.norec','pr.id','pr.namaproduk',
//                (DB::raw('case
//                          when pr.id in (401790,401791,478,479)
//                                    then \'Pencabutan Gigi Tetap\'
//                          when pr.id in (15366,401789,402686,402692,15247,15364,15245,401796,477,502,15467)
//                                    then \'Pencabutan Gigi Sulung\'
//                          when pr.id in (401854,10003505,401855,493,493119,401548,402852,574,612)
//                                    then \'Pembersihan karang gigi\'
//                          when pp.produkfk in (401864,401862,401865,401861,401863,401866,401867,401868,494,495,496,497,498,499,532)
//                                    then \'Tumpatan Gigi\'
//                          when pp.produkfk in (15728,401872,523,401829,401830,402991,19856,19859,19851,402046,10012701,536,731)
//                                    then \'Orthodonti\'
//                          when pp.produkfk in (546,724,723,403077,401817)
//                                    then \'Jacket/Bridge\'
//			              else \'-\'
//			              end as jeniskegiatan'))
            )
            ->where('pd.kdprofile', $idProfile)
            ->where('jl.id',$KdJenisLapGigiMulut)
            ->groupBy('mpptrl.objectkontenlaporanfk','jl.jenislaporan','kl.kelompoklaporan');
//            ->where('ru.id', 4)
//            ->whereNotIn('djp.objectjenisprodukfk',[97])
//            ->wherein('kp.id', [1, 2, 3, 4, 8, 9, 10, 11, 13, 14])
//            ->whereNull('sp.statusenabled');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();
//        $data10 = [];
//        $jml = 0;
//        $jmlCentralLine = 0;
//        $jmlKateterUrine = 0;
//
//        foreach ($data as $item) {
//            $sama = false;
//            $i = 0;
//            foreach ($data10 as $dat) {
//                if ($item->jeniskegiatan == $data10[$i]['jeniskegiatan']) {
//                    $sama = true;
//                    $jml = (float)$dat['jumlah'] + 1;
//                    $data10[$i]['jumlah'] = $jml;
//
//                    $data10[$i]['Total'] = (float)$dat['Total'] + 1;
//                }
//                $i = $i + 1;
//            }
//            if ($sama == false) {
//
//                $data10[] = array(
//                    'jeniskegiatan'=>$item->jeniskegiatan,
//                    'Total' => 1,
//                    'jumlah' => 1,
//                );
//            }
//
//            foreach ($data10 as $key => $row) {
//                $count[$key] = $row['jumlah'];
//            }
//            array_multisort($count, SORT_DESC, $data10);
//        }

        $result = array(
//            'data' => $data10,
            'data' => $data,
            'message' => 'kabayan',
        );

        return $this->respond($result);
    }
    public function getLaporanRL34Kebidanan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $KdJenisLapKebidanan = (int) $this->settingDataFixed('KdJenisLapKebidanan', $idProfile);
        $KdRuangKebidanan = explode(',', $this->settingDataFixed('KdRuangKebidanan', $idProfile));
        $dataLogin = $request->all();
        $kdListRuanganKebidanan = [];
        foreach ($KdRuangKebidanan as $item){
            $kdListRuanganKebidanan [] = (int) $item;
        }
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->join('asalrujukan_m as ar', 'ar.id', '=', 'apd.objectasalrujukanfk')
            ->leftjoin ('statuskeluar_m as stk','stk.id','=','pd.objectstatuskeluarfk')
            ->leftJoin('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->select( 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'apd.objectasalrujukanfk', 'ar.asalrujukan','pd.objectstatuskeluarfk','apd.objectruanganfk',
                'kl.kelompoklaporan as jenispelayanan'
//                DB::raw('(case when pr.id =402494 then \'Persalinan Normal\'
//                        when pr.id in (15375,15386,15387,15388,15477) then \'Sectio Caesaria\'
//                        else \'-\'
//                         end) as jenispelayanan ')
            )
            ->where('pd.kdprofile', $idProfile)
            ->where('jl.id', $KdJenisLapKebidanan)//laporan RL 34
            ->whereIn('apd.objectruanganfk', $kdListRuanganKebidanan);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $rujukanRS = 0;
        $rujukanBidan = 0;
        $rujukanPuskes = 0;
        $rujukanFaskesLain = 0;
        $jmlMedisHidup = 0;
        $jmlMedisMati = 0;
        $jmlTotalMedis = 0;
        $jmlNonMedisHidup = 0;
        $jmlNonMedisMati = 0;
        $jmlTotalNonMedis = 0;
        $nonRujukanHidup=0;
        $nonRujukanMati=0;
        $totalNonRujukan=0;
        $dirujuk=0;
        $sama = false;


        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            $o = 0;
            foreach ($data10 as $hideung) {
                if ($item->jenispelayanan == $data10[$i]['jenispelayanan']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;

                    //RS=2, puskes=1, faskeslan=3, non rujikan =5, klinik=3
                    //statuskeluar --->1 pulang , 2 pindah,3 rajal, 4 rujuk, 5 meningggal

                    if ($item->objectasalrujukanfk == 2) {
                        $data10[$i]['rujukanRS'] = (float)$hideung['rujukanRS'] + 1;
                    } else if ($item->objectasalrujukanfk == 1) {
                        $data10[$i]['rujukanPuskes'] = (float)$hideung['rujukanPuskes'] + 1;
                    } else if ($item->objectasalrujukanfk == 3 || $item->objectasalrujukanfk ==4) {
                        $data10[$i]['rujukanFaskesLain'] = (float)$hideung['rujukanFaskesLain'] + 1;
                    }
                    if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==1 ) {
                        $data10[$i]['jmlMedisHidup'] = (float)$hideung['jmlMedisHidup'] + 1;
                    } else if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==5 ) {
                        $data10[$i]['jmlMedisMati'] = (float)$hideung['jmlMedisMati'] + 1;
                    }
                    if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4) && $item->objectstatuskeluarfk==1) {
                        $data10[$i]['jmlNonMedisHidup'] = (float)$hideung['jmlNonMedisHidup'] + 1;
                    } else if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4) && $item->objectstatuskeluarfk==5) {
                        $data10[$i]['jmlNonMedisMati'] = (float)$hideung['jmlNonMedisMati'] + 1;
                    }  if ($item->objectstatuskeluarfk == 4) {
                        $data10[$i]['dirujuk'] = (float)$hideung['dirujuk'] + 1;
                    }

                    $data10[$i]['jmlTotalMedis'] = $data10[$i]['rujukanRS'] + $data10[$i]['rujukanPuskes']+$data10[$i]['rujukanFaskesLain'] + $data10[$i]['jmlMedisHidup']+ $data10[$i]['jmlMedisMati'];
                    $data10[$i]['jmlTotalNonMedis'] = $data10[$i]['jmlNonMedisHidup'] + $data10[$i]['jmlNonMedisMati'];

                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->objectasalrujukanfk == 2) {
                    $rujukanRS = 1;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if ($item->objectasalrujukanfk == 1) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 1;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if ($item->objectasalrujukanfk == 3 || $item->objectasalrujukanfk ==4) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 1;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                }  if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==1 ) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 1;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==5 ) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 1;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                }
                if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4)&& $item->objectstatuskeluarfk==1) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 1;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4) && $item->objectstatuskeluarfk==5) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 1;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                }  if ($item->objectstatuskeluarfk == 4) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=1;
                }

                $data10[] = array(
//                    'produkfk' => $item->produkfk,
                    'jenispelayanan' => $item->jenispelayanan,
                    'jumlah' => 1,

                    'rujukanRS'=> $rujukanRS,
                    'rujukanFaskesLain'=> $rujukanFaskesLain ,
                    'rujukanPuskes'=> $rujukanPuskes ,
                    'rujukanBidan'=> 0 ,
                    'jmlMedisHidup'=> $jmlMedisHidup,
                    'jmlMedisMati'=> $jmlMedisMati,
                    'jmlTotalMedis'=>$jmlTotalMedis ,
                    'jmlNonMedisHidup'=> $jmlNonMedisHidup ,
                    'jmlNonMedisMati'=> $jmlNonMedisMati,
                    'jmlTotalNonMedis'=> $jmlTotalNonMedis,
                    'nonRujukanHidup'=> $nonRujukanHidup,
                    'nonRujukanMati'=> $nonRujukanMati,
                    'totalNonRujukan'=> $totalNonRujukan,
                    'dirujuk'=> $dirujuk,
                    'jmlTotalMedis'=>$jmlTotalMedis,
                    'jmlTotalNonMedis'=>$jmlTotalNonMedis,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }
    public function getLaporanRL35Perinatologi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $KdJenisLapPerinatologi = (int)$this->settingDataFixed('KdJenisLapPerinatologi', $idProfile);
        $KdRuangKebidanan = explode(',', $this->settingDataFixed('KdRuangKebidanan', $idProfile));
        $kdListRuanganKebidanan = [];
        foreach ($KdRuangKebidanan as $item){
            $kdListRuanganKebidanan [] = (int) $item;
        }
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->join('asalrujukan_m as ar', 'ar.id', '=', 'apd.objectasalrujukanfk')
            ->leftjoin ('statuskeluar_m as stk','stk.id','=','pd.objectstatuskeluarfk')
            ->leftJoin('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->select( 'pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk',
                'apd.objectasalrujukanfk', 'ar.asalrujukan','pd.objectstatuskeluarfk','apd.objectruanganfk',
                'kl.kelompoklaporan as jenispelayanan'
//                DB::raw('(case when pr.id =402494 then \'Persalinan Normal\'
//                        when pr.id in (15375,15386,15387,15388,15477) then \'Sectio Caesaria\'
//                        else \'-\'
//                         end) as jenispelayanan ')
            )
//            ->where('dpm.id', 24)
            ->where('pd.kdprofile', $idProfile)
            ->where('jl.id', $KdJenisLapPerinatologi)//laporan RL 35
            ->whereIn('apd.objectruanganfk', $kdListRuanganKebidanan);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $rujukanRS = 0;
        $rujukanBidan = 0;
        $rujukanPuskes = 0;
        $rujukanFaskesLain = 0;
        $jmlMedisHidup = 0;
        $jmlMedisMati = 0;
        $jmlTotalMedis = 0;
        $jmlNonMedisHidup = 0;
        $jmlNonMedisMati = 0;
        $jmlTotalNonMedis = 0;
        $nonRujukanHidup=0;
        $nonRujukanMati=0;
        $totalNonRujukan=0;
        $dirujuk=0;
        $sama = false;


        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            $o = 0;
            foreach ($data10 as $hideung) {
                if ($item->jenispelayanan == $data10[$i]['jenispelayanan']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;

                    //RS=2, puskes=1, faskeslan=3, non rujikan =5, klinik=3
                    //statuskeluar --->1 pulang , 2 pindah,3 rajal, 4 rujuk, 5 meningggal

                    if ($item->objectasalrujukanfk == 2) {
                        $data10[$i]['rujukanRS'] = (float)$hideung['rujukanRS'] + 1;
                    } else if ($item->objectasalrujukanfk == 1) {
                        $data10[$i]['rujukanPuskes'] = (float)$hideung['rujukanPuskes'] + 1;
                    } else if ($item->objectasalrujukanfk == 3 || $item->objectasalrujukanfk ==4) {
                        $data10[$i]['rujukanFaskesLain'] = (float)$hideung['rujukanFaskesLain'] + 1;
                    }
                    if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==1 ) {
                        $data10[$i]['jmlMedisHidup'] = (float)$hideung['jmlMedisHidup'] + 1;
                    } else if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==5 ) {
                        $data10[$i]['jmlMedisMati'] = (float)$hideung['jmlMedisMati'] + 1;
                    }
                    if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4) && $item->objectstatuskeluarfk==1) {
                        $data10[$i]['jmlNonMedisHidup'] = (float)$hideung['jmlNonMedisHidup'] + 1;
                    } else if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4) && $item->objectstatuskeluarfk==5) {
                        $data10[$i]['jmlNonMedisMati'] = (float)$hideung['jmlNonMedisMati'] + 1;
                    }  if ($item->objectstatuskeluarfk == 4) {
                        $data10[$i]['dirujuk'] = (float)$hideung['dirujuk'] + 1;
                    }

                    $data10[$i]['jmlTotalMedis'] = $data10[$i]['rujukanRS'] + $data10[$i]['rujukanPuskes']+$data10[$i]['rujukanFaskesLain'] + $data10[$i]['jmlMedisHidup']+ $data10[$i]['jmlMedisMati'];
                    $data10[$i]['jmlTotalNonMedis'] = $data10[$i]['jmlNonMedisHidup'] + $data10[$i]['jmlNonMedisMati'];

                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->objectasalrujukanfk == 2) {
                    $rujukanRS = 1;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if ($item->objectasalrujukanfk == 1) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 1;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if ($item->objectasalrujukanfk == 3 || $item->objectasalrujukanfk ==4) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 1;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                }  if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==1 ) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 1;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if (($item->objectasalrujukanfk == 2 || $item->objectasalrujukanfk == 1 ||$item->objectasalrujukanfk == 3)&& $item->objectstatuskeluarfk==5 ) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 1;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                }
                if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4)&& $item->objectstatuskeluarfk==1) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 1;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                } else if (($item->objectasalrujukanfk == 5 ||$item->objectasalrujukanfk == 4) && $item->objectstatuskeluarfk==5) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 1;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=0;
                }  if ($item->objectstatuskeluarfk == 4) {
                    $rujukanRS = 0;$rujukanBidan = 0;$rujukanPuskes = 0;$rujukanFaskesLain = 0;$jmlMedisHidup = 0;$jmlMedisMati = 0;
                    $jmlTotalMedis = 0;$jmlNonMedisHidup = 0;$jmlNonMedisMati = 0;$jmlTotalNonMedis = 0;$nonRujukanHidup=0;$nonRujukanMati=0;
                    $totalNonRujukan=0;$dirujuk=1;
                }

                $data10[] = array(
//                    'produkfk' => $item->produkfk,
                    'jenispelayanan' => $item->jenispelayanan,
                    'jumlah' => 1,

                    'rujukanRS'=> $rujukanRS,
                    'rujukanFaskesLain'=> $rujukanFaskesLain ,
                    'rujukanPuskes'=> $rujukanPuskes ,
                    'rujukanBidan'=> 0 ,
                    'jmlMedisHidup'=> $jmlMedisHidup,
                    'jmlMedisMati'=> $jmlMedisMati,
                    'jmlTotalMedis'=>$jmlTotalMedis ,
                    'jmlNonMedisHidup'=> $jmlNonMedisHidup ,
                    'jmlNonMedisMati'=> $jmlNonMedisMati,
                    'jmlTotalNonMedis'=> $jmlTotalNonMedis,
                    'nonRujukanHidup'=> $nonRujukanHidup,
                    'nonRujukanMati'=> $nonRujukanMati,
                    'totalNonRujukan'=> $totalNonRujukan,
                    'dirujuk'=> $dirujuk,

                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }
    public function getLaporanRL36Pembedahan(Request $request){
        $idProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapPembedahan = (int) $this->settingDataFixed('KdJenisLapPembedahan', $idProfile);
//        $dataLogin = $request->all();
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJoin('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
            ->select('pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk', 'pr.objectdetailjenisprodukfk',
                'djp.detailjenisproduk','kl.kelompoklaporan as spesialisasi')
            ->where('apd.kdprofile', $idProfile)
            ->where('jl.id',$KdJenisLapPembedahan);
//                DB::raw('(case when ru.id = 44 then \'Bedah\'
//                                when ru.id in (18,3,45,464) then \'Obstetrik & Ginekologi\'
//                                when ru.id =198 then \'Bedah Saraf\'
//                                when ru.id =6 then \'THT\'
//                                when ru.id =5 then \'Mata\'
//                                when ru.id =7 then \'Kulit & Kelamin\'
//                                when ru.id =4 then \'Gigi & Mulut\'
//                                when ru.id =1 then \'Bedah Anak\'
//                                when  ru.id=98 then \'Kardiovaskuler\'
//                                when ru.id =13 then \'Bedah Orthopedi\'
//                                when  ru.id= 35 then \'Thorax\'
//                                when  ru.id =240 then \'Urologi\'
//                                else \'Lain-lain\' end ) as spesialisasi')
//            )
        //PEMATOKAN :(((((
//            ->whereIn('ru.id', [44, 18, 3, 45, 464, 198, 6, 5, 7, 4, 1, 98, 13, 35, 240])
//            ->wherein('pr.objectdetailjenisprodukfk', [62, 138, 429, 431, 428, 78, 139, 430, 432, 426, 51,
//                427, 425, 433, 51, 427, 425, 433, 48, 140, 437, 434, 9, 52,
//                141, 435, 41, 56, 146, 438, 45, 39, 61, 70, 77, 54, 424, 421,
//                418, 66, 7, 64, 73, 47, 136, 423, 420, 417, 33, 143, 409, 412,
//                411, 60, 63, 6, 410, 414, 408, 415, 413, 142, 411]);
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $khusus = 0;
        $besar = 0;
        $sedang = 0;
        $kecil = 0;
        $lainnya = 0;

        $sama = false;


        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            $o = 0;
            foreach ($data10 as $hideung) {
                if ($item->spesialisasi == $data10[$i]['spesialisasi']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
                    if ($item->objectdetailjenisprodukfk == 62 || $item->objectdetailjenisprodukfk == 78 || $item->objectdetailjenisprodukfk == 51
                        || $item->objectdetailjenisprodukfk == 138 || $item->objectdetailjenisprodukfk == 139 || $item->objectdetailjenisprodukfk == 427
                        || $item->objectdetailjenisprodukfk == 429 || $item->objectdetailjenisprodukfk == 430 || $item->objectdetailjenisprodukfk == 425
                        || $item->objectdetailjenisprodukfk == 431 || $item->objectdetailjenisprodukfk == 432 || $item->objectdetailjenisprodukfk == 433
                        || $item->objectdetailjenisprodukfk == 428 || $item->objectdetailjenisprodukfk == 426
                    ) {
                        $data10[$i]['besar'] = (float)$hideung['besar'] + 1;
                    } else if ($item->objectdetailjenisprodukfk == 9 || $item->objectdetailjenisprodukfk == 41 || $item->objectdetailjenisprodukfk == 45
                        || $item->objectdetailjenisprodukfk == 48 || $item->objectdetailjenisprodukfk == 52 || $item->objectdetailjenisprodukfk == 56
                        || $item->objectdetailjenisprodukfk == 140 || $item->objectdetailjenisprodukfk == 141 || $item->objectdetailjenisprodukfk == 146
                        || $item->objectdetailjenisprodukfk == 437 || $item->objectdetailjenisprodukfk == 435 || $item->objectdetailjenisprodukfk == 438
                        || $item->objectdetailjenisprodukfk == 434
                    ) {
                        $data10[$i]['khusus'] = (float)$hideung['khusus'] + 1;
                    } else if ($item->objectdetailjenisprodukfk == 26 || $item->objectdetailjenisprodukfk == 30 || $item->objectdetailjenisprodukfk == 33
                        || $item->objectdetailjenisprodukfk == 36 || $item->objectdetailjenisprodukfk == 39 || $item->objectdetailjenisprodukfk == 7
                        || $item->objectdetailjenisprodukfk == 43 || $item->objectdetailjenisprodukfk == 61 || $item->objectdetailjenisprodukfk == 64
                        || $item->objectdetailjenisprodukfk == 68 || $item->objectdetailjenisprodukfk == 70 || $item->objectdetailjenisprodukfk == 73
                        || $item->objectdetailjenisprodukfk == 76 || $item->objectdetailjenisprodukfk == 77 || $item->objectdetailjenisprodukfk == 47
                        || $item->objectdetailjenisprodukfk == 50 || $item->objectdetailjenisprodukfk == 54 || $item->objectdetailjenisprodukfk == 136
                        || $item->objectdetailjenisprodukfk == 137 || $item->objectdetailjenisprodukfk == 424 || $item->objectdetailjenisprodukfk == 423
                        || $item->objectdetailjenisprodukfk == 422 || $item->objectdetailjenisprodukfk == 421 || $item->objectdetailjenisprodukfk == 420
                        || $item->objectdetailjenisprodukfk == 419 || $item->objectdetailjenisprodukfk == 418 || $item->objectdetailjenisprodukfk == 417
                        || $item->objectdetailjenisprodukfk == 416 || $item->objectdetailjenisprodukfk == 66
                    ) {
                        $data10[$i]['sedang'] = (float)$hideung['sedang'] + 1;
                    } else if ($item->objectdetailjenisprodukfk == 60 || $item->objectdetailjenisprodukfk == 63 || $item->objectdetailjenisprodukfk == 142
                        || $item->objectdetailjenisprodukfk == 143 || $item->objectdetailjenisprodukfk == 6 || $item->objectdetailjenisprodukfk == 408
                        || $item->objectdetailjenisprodukfk == 409 || $item->objectdetailjenisprodukfk == 410 || $item->objectdetailjenisprodukfk == 415
                        || $item->objectdetailjenisprodukfk == 412 || $item->objectdetailjenisprodukfk == 414 || $item->objectdetailjenisprodukfk == 413
                        || $item->objectdetailjenisprodukfk == 411
                    ) {
                        $data10[$i]['kecil'] = (float)$hideung['kecil'] + 1;
                    }
//                    else  {
//                        $data10[$i]['lainnya'] = (float)$hideung['lainnya'] + 1;
//                    }

                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->objectdetailjenisprodukfk == 62 || $item->objectdetailjenisprodukfk == 78 || $item->objectdetailjenisprodukfk == 51
                    || $item->objectdetailjenisprodukfk == 138 || $item->objectdetailjenisprodukfk == 139 || $item->objectdetailjenisprodukfk == 427
                    || $item->objectdetailjenisprodukfk == 429 || $item->objectdetailjenisprodukfk == 430 || $item->objectdetailjenisprodukfk == 425
                    || $item->objectdetailjenisprodukfk == 431 || $item->objectdetailjenisprodukfk == 432 || $item->objectdetailjenisprodukfk == 433
                    || $item->objectdetailjenisprodukfk == 428 || $item->objectdetailjenisprodukfk == 426
                ) {
                    $khusus = 0;
                    $besar = 1;
                    $sedang = 0;
                    $kecil = 0;
                    $lainnya = 0;
                } else if ($item->objectdetailjenisprodukfk == 9 || $item->objectdetailjenisprodukfk == 41 || $item->objectdetailjenisprodukfk == 45
                    || $item->objectdetailjenisprodukfk == 48 || $item->objectdetailjenisprodukfk == 52 || $item->objectdetailjenisprodukfk == 56
                    || $item->objectdetailjenisprodukfk == 140 || $item->objectdetailjenisprodukfk == 141 || $item->objectdetailjenisprodukfk == 146
                    || $item->objectdetailjenisprodukfk == 437 || $item->objectdetailjenisprodukfk == 435 || $item->objectdetailjenisprodukfk == 438
                    || $item->objectdetailjenisprodukfk == 434
                ) {
                    $khusus = 1;
                    $besar = 0;
                    $sedang = 0;
                    $kecil = 0;
                    $lainnya = 0;
                } else if ($item->objectdetailjenisprodukfk == 26 || $item->objectdetailjenisprodukfk == 30 || $item->objectdetailjenisprodukfk == 33
                    || $item->objectdetailjenisprodukfk == 36 || $item->objectdetailjenisprodukfk == 39 || $item->objectdetailjenisprodukfk == 7
                    || $item->objectdetailjenisprodukfk == 43 || $item->objectdetailjenisprodukfk == 61 || $item->objectdetailjenisprodukfk == 64
                    || $item->objectdetailjenisprodukfk == 68 || $item->objectdetailjenisprodukfk == 70 || $item->objectdetailjenisprodukfk == 73
                    || $item->objectdetailjenisprodukfk == 76 || $item->objectdetailjenisprodukfk == 77 || $item->objectdetailjenisprodukfk == 47
                    || $item->objectdetailjenisprodukfk == 50 || $item->objectdetailjenisprodukfk == 54 || $item->objectdetailjenisprodukfk == 136
                    || $item->objectdetailjenisprodukfk == 137 || $item->objectdetailjenisprodukfk == 424 || $item->objectdetailjenisprodukfk == 423
                    || $item->objectdetailjenisprodukfk == 422 || $item->objectdetailjenisprodukfk == 421 || $item->objectdetailjenisprodukfk == 420
                    || $item->objectdetailjenisprodukfk == 419 || $item->objectdetailjenisprodukfk == 418 || $item->objectdetailjenisprodukfk == 417
                    || $item->objectdetailjenisprodukfk == 416 || $item->objectdetailjenisprodukfk == 66
                ) {
                    $khusus = 0;
                    $besar = 0;
                    $sedang = 1;
                    $kecil = 0;
                    $lainnya = 0;
                } else if ($item->objectdetailjenisprodukfk == 60 || $item->objectdetailjenisprodukfk == 63 || $item->objectdetailjenisprodukfk == 142
                    || $item->objectdetailjenisprodukfk == 143 || $item->objectdetailjenisprodukfk == 6 || $item->objectdetailjenisprodukfk == 408
                    || $item->objectdetailjenisprodukfk == 409 || $item->objectdetailjenisprodukfk == 410 || $item->objectdetailjenisprodukfk == 415
                    || $item->objectdetailjenisprodukfk == 412 || $item->objectdetailjenisprodukfk == 414 || $item->objectdetailjenisprodukfk == 413
                    || $item->objectdetailjenisprodukfk == 411
                ) {
                    $khusus = 0;
                    $besar = 0;
                    $sedang = 0;
                    $kecil = 1;
                    $lainnya = 0;
                }
//                else{
//                    $lainnya=1;
//                }


                $data10[] = array(
//                    'produkfk' => $item->produkfk,
                    'spesialisasi' => $item->spesialisasi,
                    'jumlah' => 1,
                    'khusus' => $khusus,
                    'besar' => $besar,
                    'sedang' => $sedang,
                    'kecil' => $kecil,
//                    'lainnya' => $lainnya,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }
    public function getLaporanRL37(Request $request){
        $idProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        ///////ORIGINAL*****
//        $data = \DB::table('antrianpasiendiperiksa_t as apd')
//            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
//            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
//            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
//            ->join('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
//            ->join('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
//            ->leftJoin('kelompokproduk_m as kp', 'kp.id', '=', 'jp.objectkelompokprodukfk')
//            ->select('pp.tglpelayanan', 'pp.produkfk', 'pr.namaproduk', 'pr.objectdetailjenisprodukfk',
//                     'djp.detailjenisproduk',
//                DB::raw('(case when pr.objectdetailjenisprodukfk = 499 then \'Foto tanpa bahan kontras\'
//                                    when pr.objectdetailjenisprodukfk  =498 then \'Foto dengan bahan kontras\'
//                                    when pr.objectdetailjenisprodukfk =1410 then \'Foto dengan rol film\'
//                                    when pr.objectdetailjenisprodukfk =1411 then \'Flouroskopi\'
//                                    when pr.objectdetailjenisprodukfk =1412 then \'Foto Gigi\'
//                                    when pr.objectdetailjenisprodukfk =1413 then \'Lymphografi\'
//                                    when pr.objectdetailjenisprodukfk =1414 then \'Angiograpi\'
//                                    when pr.objectdetailjenisprodukfk =1415 then \'Kegiatan Radiotherapi\'
//                                    when pr.objectdetailjenisprodukfk =1416 then \'Kegiatan Diagnostik\'
//                                    when pr.objectdetailjenisprodukfk =1417 then \'Kegiatan Therapi\'
//                                    when pr.objectdetailjenisprodukfk in (502,503) then \'CT Scan\'
//                                    WHEn pr.objectdetailjenisprodukfk in (504,505) then \'MRI\'
//                                    when pr.objectdetailjenisprodukfk in (501,500) then \'USG\'
//                                    when pr.objectdetailjenisprodukfk =1293 then \'Lainnya\'
//                                    end) as jeniskegiatan')
//            )
//            ->where('dpm.id', 27)
//            ->wherein('djp.id', [501, 502, 505, 500, 1293, 499, 503, 504, 498, 1410,
//                                1411, 1412, 1413, 1414, 1415, 1416, 1417]);
////            ->distinct;
//
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
//        }
//
//        $data = $data->get();
//
//        $data10 = [];
//        $jml = 0;
//        $fotoNonKontras = 0;
//        $fotoKontras = 0;
//        $fotoRolFilm = 0;
//        $flouroskopi = 0;
//        $fotoGigi = 0;
//        $ctScan = 0;
//        $lymphografi = 0;
//        $angiograpi = 0;
//        $radiotherapi = 0;
//        $diagnostik = 0;
//        $therapi = 0;
//        $usg = 0;
//        $mri = 0;
//        $lainnya = 0;
//
//        $sama = false;
//
//
//        foreach ($data as $item) {
//            $sama = false;
//            $i = 0;
//            $o = 0;
//            foreach ($data10 as $hideung) {
//                if ($item->jeniskegiatan == $data10[$i]['jeniskegiatan']) {
//                    $sama = true;
//                    $jml = (float)$hideung['jumlah'] + 1;
//                    $data10[$i]['jumlah'] = $jml;
//                    if ($item->objectdetailjenisprodukfk == 499) {
//                        $data10[$i]['fotoNonKontras'] = (float)$hideung['fotoNonKontras'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 498) {
//                        $data10[$i]['fotoKontras'] = (float)$hideung['fotoKontras'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1410) {
//                        $data10[$i]['fotoRolFilm'] = (float)$hideung['fotoRolFilm'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1411) {
//                        $data10[$i]['flouroskopi'] = (float)$hideung['flouroskopi'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1412) {
//                        $data10[$i]['fotoGigi'] = (float)$hideung['fotoGigi'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1413) {
//                        $data10[$i]['lymphografi'] = (float)$hideung['lymphografi'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1414) {
//                        $data10[$i]['angiograpi'] = (float)$hideung['angiograpi'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1415) {
//                        $data10[$i]['radiotherapi'] = (float)$hideung['radiotherapi'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1417) {
//                        $data10[$i]['therapi'] = (float)$hideung['therapi'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1416) {
//                        $data10[$i]['diagnostik'] = (float)$hideung['diagnostik'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 502 || $item->objectdetailjenisprodukfk == 503) {
//                        $data10[$i]['ctScan'] = (float)$hideung['ctScan'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 504 || $item->objectdetailjenisprodukfk == 505) {
//                        $data10[$i]['mri'] = (float)$hideung['mri'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 500 || $item->objectdetailjenisprodukfk == 501) {
//                        $data10[$i]['usg'] = (float)$hideung['usg'] + 1;
//                    } else if ($item->objectdetailjenisprodukfk == 1293) {
//                        $data10[$i]['lainnya'] = (float)$hideung['lainnya'] + 1;
//                    }
//
//                }
//                $i = $i + 1;
//            }
//
//            if ($sama == false) {
//                if ($item->objectdetailjenisprodukfk == 499) {
//                    $fotoNonKontras = 1;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 498) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 1;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1410) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 1;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1411) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 1;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1412) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 1;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1413) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 1;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1414) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 1;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1415) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 1;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1416) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 1;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1417) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 1;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 502 || $item->objectdetailjenisprodukfk == 503) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 1;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 504 || $item->objectdetailjenisprodukfk == 505) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 1;
//                } else if ($item->objectdetailjenisprodukfk == 500 || $item->objectdetailjenisprodukfk == 501) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 1;
//                    $mri = 0;
//                } else if ($item->objectdetailjenisprodukfk == 1293) {
//                    $fotoNonKontras = 0;
//                    $fotoKontras = 0;
//                    $fotoRolFilm = 0;
//                    $flouroskopi = 0;
//                    $fotoGigi = 0;
//                    $ctScan = 0;
//                    $lymphografi = 0;
//                    $angiograpi = 0;
//                    $radiotherapi = 0;
//                    $diagnostik = 0;
//                    $therapi = 0;
//                    $usg = 0;
//                    $mri = 0;
//                    $lainnya = 1;
//
//                }
//
//                $data10[] = array(
////                    'produkfk' => $item->produkfk,
//                    'jeniskegiatan' => $item->jeniskegiatan,
//                    'jumlah' => 1,
//                    'fotoNonKontras' => $fotoNonKontras,
//                    'fotoKontras' => $fotoKontras,
//                    'fotoRolFilm' => $fotoRolFilm,
//                    'flouroskopi' => $flouroskopi,
//                    'fotoGigi' => $fotoGigi,
//                    'ctScan' => $ctScan,
//                    'lymphografi' => $lymphografi,
//                    'angiograpi' => $angiograpi,
//                    'radiotherapi' => $radiotherapi,
//                    'diagnostik' => $diagnostik,
//                    'therapi' => $therapi,
//                    'usg' => $usg,
//                    'mri' => $mri,
//                    'lainnya' => $lainnya,
//                );
//            }
//
//            foreach ($data10 as $key => $row) {
//                $count[$key] = $row['jumlah'];
//            }
//
//            array_multisort($count, SORT_DESC, $data10);
//        }
        $KdJenisLapRadiologi = (int) $this->settingDataFixed('KdJenisLapRadiologi', $idProfile);

        //////With Mapping****
        $data = \DB::table('antrianpasiendiperiksa_t as apd')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mpptrl','mpptrl.produkfk','=','pr.id')
            ->join('kelompoklaporan_m as kl','kl.id','=','mpptrl.objectkontenlaporanfk')
            ->join('jenislaporan_m as jl','jl.id','=','mpptrl.objectjenislaporanfk')
            ->select('jl.jenislaporan','kl.kelompoklaporan',
                (DB::raw('COUNT(mpptrl.objectkontenlaporanfk) as jumlah')
                )
            )
            ->where('apd.kdprofile', $idProfile)
            ->where('jl.id',$KdJenisLapRadiologi)
            ->groupBy('mpptrl.objectkontenlaporanfk','jl.jenislaporan','kl.kelompoklaporan');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();
        $result = array(
//            'data' => $data10,
            'data' => $data,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }


    public function getPemeriksaanLab(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapLaboratorium = (int) $this->settingDataFixed('KdJenisLapLaboratorium', $kdProfile);
        $KdDepartemenInstalasiLaboratorium = (int) $this->settingDataFixed('KdDepartemenInstalasiLaboratorium',$kdProfile);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('produk_m as prd', 'prd.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->select('ru.objectdepartemenfk', 'dpm.namadepartemen', 'apd.objectruanganfk', 'ru.namaruangan',
                'pp.produkfk', 'prd.id as idproduk',
//                'prd.namaproduk',
                'kl.kelompoklaporan as namaproduk',
//                DB::raw('
//               (case when prd.id in (11909,11911) then \'Hematologi\'
//                when prd.id in (401258,401256,401307,401260,401262,401263,401255) then \'Lat. Gerak (Exercise)\'
//                when prd.id in (13218,13219,11873,10013101,10013105,10013108) then \'Sitokimia Darah\'
//                when prd.id in (11862,11868) then \'Perbankan Darah\'
//                when prd.id in (12181,12284,12290,12256,12184,12208,12272,12162,12168) then \'Pemeriksaan Lain\'
//                when prd.id in (11980,12020,12022,12023,12035,12036,12047,12029,12066,12016,12011,12018,12021,
//                                                12033,12034,12039,12040,12044,12046,12053,12074,12075,12077,12188,12298,12172,
//                                                12250,12037,12069,12058,12064,11979,11989,12012,12013,12015,12024,12038,12043,
//                                                12065,12070,12071,12072,13212,11983,11987,11990,11998,12007,12009,12063,12166,
//                                                11988,11991,12004,12014,12017,12027,12030,12042,12143,12144,12032,12177,12245,
//                                                11970,12050,12051,12203,12052,12178,12186,12165,11986,11996,12026,12002,12025,
//                                                12003) then \'Kimia Klinik\'
//                when prd.id in (12282,12117,12005,12006,12159) then \'Mikronutrient dan Monitoring kadar terapi obat\'
//                else null
//                end) as jenislayanan,
//                COUNT(prd.namaproduk) as jmlProduk')
//            )
                DB::raw('
                COUNT(prd.namaproduk) as jmlProduk')
            )
            ->groupBy('ru.objectdepartemenfk', 'dpm.namadepartemen', 'apd.objectruanganfk', 'ru.namaruangan', 'pp.produkfk',
                'prd.id', 'prd.namaproduk','kl.kelompoklaporan')
            ->where('ru.objectdepartemenfk', '=', $KdDepartemenInstalasiLaboratorium)
            ->where('pd.kdprofile', $kdProfile)
            ->where('jl.id',$KdJenisLapLaboratorium);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'as@cepot',
        );
        return $this->respond($result);
    }
    public function getPelayananRehab(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapRehab = (int) $this->settingDataFixed('KdJenisLapRehab', $kdProfile);
        $KdDepartemenInstalasiRehabilitasiMedik = (int) $this->settingDataFixed('KdDepartemenInstalasiRehabilitasiMedik', $kdProfile);
//        $dataLogin = $request->all();
//        $data = \DB::table('pasiendaftar_t as pd')
//            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
//            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
//            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
//            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
//            ->join('produk_m as prd', 'prd.id', '=', 'pp.produkfk')
//            ->select('ru.objectdepartemenfk', 'dpm.namadepartemen', 'apd.objectruanganfk', 'ru.namaruangan',
//                'pp.produkfk', 'prd.id as idproduk', 'prd.namaproduk',
//                DB::raw('
//               (case when prd.id = 401322 then \'Inh (Nebulizer)\'
//                    when prd.id in (401258,401256,401307,401260,401262,401263,401255) then \'Lat. Gerak (Exercise)\'
//                    when prd.id in (401262,401261) then \'US\'
//                    when prd.id in (402940,402435) then \'Pijat Bayi\'
//                    when prd.id = 401270 then \'Senam Hamil\'
//                    when prd.id = 401274 then \'Terapi Musik\'
//                    when prd.id in (401275,401329) then \'Deteksi dini Program Psikologi\'
//                    when prd.id in (401301,401300) then \'Tindakan Sensori Integrasi (SI)\'
//                    when prd.id in (863,879,401302) then \'Tindakan Okupasi Terapi (OT)\'
//                    when prd.id in (401305,401304,864) then \'Tindakan Terapi Wicara (TW)\'
//                    when prd.id = 925 then \'Seleksi\'
//                    when prd.id = 402867 then \'Follow Up\' else null
//                    end) as jenislayanan,COUNT(prd.namaproduk) as JmlTindakan')
//            )
//            ->groupBy('ru.objectdepartemenfk', 'dpm.namadepartemen', 'apd.objectruanganfk', 'ru.namaruangan', 'pp.produkfk',
//                'prd.id', 'prd.namaproduk')
//            ->where('ru.objectdepartemenfk', '=', '28')
//            ->whereIn('prd.id', [401322, 401258, 401256, 401307, 401260, 401262, 401263, 401255, 401262,
//                401261, 402940, 402435, 401270, 401274, 401270, 401275, 401329, 401301,
//                401300, 863, 879, 401302, 401305, 401304, 864, 925, 402867]);
//
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
//        }
//
//        $data = $data->get();
//
//        $result = array(
//            'data' => $data,
//            'message' => 'as@cepot',
//        );
//        return $this->respond($result);

        //#WIth Mapp
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('produk_m as prd', 'prd.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->select(
//                'prd.namaproduk',
                'kl.kelompoklaporan as namaproduk',
                DB::raw('
             COUNT(prd.namaproduk) as JmlTindakan')
            )
            ->groupBy('kl.kelompoklaporan')
            ->where('ru.objectdepartemenfk', '=', $KdDepartemenInstalasiRehabilitasiMedik)
            ->where('jl.id',$KdJenisLapRehab);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'as@cepot',
        );
        return $this->respond($result);
    }
    public function getLaporanRL310Khusus(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapPelKhusus = (int) $this->settingDataFixed('KdJenisLapPelKhusus', $kdProfile);
        //#WIth Mapp
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('produk_m as prd', 'prd.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->select(
//                'prd.namaproduk',
                'kl.kelompoklaporan as namaproduk',
                DB::raw('
             COUNT(prd.namaproduk) as JmlTindakan')
            )
            ->groupBy('kl.kelompoklaporan')
            //            ->where('ru.objectdepartemenfk', '=', '28')
            ->where('pd.kdprofile', $kdProfile)
            ->where('jl.id',$KdJenisLapPelKhusus);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'as@cepot',
        );
        return $this->respond($result);
    }
    public function getLaporanRL311KesehatanJiwa(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapKesehatanJiwa = (int) $this->settingDataFixed('KdJenisLapKesehatanJiwa', $kdProfile);
        //#WIth Mapp
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('produk_m as prd', 'prd.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->select(
//                'prd.namaproduk',
                'kl.kelompoklaporan as namaproduk',
                DB::raw('
                 COUNT(prd.namaproduk) as jmltindakan')
            )
            ->groupBy('kl.kelompoklaporan')
            ->where('pd.kdprofile', $kdProfile)
            ->where('jl.id',$KdJenisLapKesehatanJiwa);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'as@cepot',
        );
        return $this->respond($result);
    }
    public function getLaporanRL312KeluargaBerencana(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapKB = (int) $this->settingDataFixed('KdJenisLapKB', $kdProfile);
        //#WIth Mapp
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '= ', 'apd.norec')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('produk_m as prd', 'prd.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->select(
//                'prd.namaproduk',
                'kl.kelompoklaporan as namaproduk',
                DB::raw('
                 COUNT(prd.namaproduk) as jmltindakan')
            )
            ->groupBy('kl.kelompoklaporan')
            ->where('pd.kdprofile', $KdJenisLapKB)
            ->where('jl.id',$KdJenisLapKB);


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }

        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getPengadaanObat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapFarmasi = (int) $this->settingDataFixed('KdJenisLapFarmasi', $kdProfile);
//        $dataLogin = $request->all();
//
//        $data = \DB::table('produk_m as prd')
//            ->join('stokprodukdetail_t as spd','spd.objectprodukfk','=','prd.id')
//            ->join('ruangan_m as ru','ru.id','=','spd.objectruanganfk')
//            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
//
//            ->select('prd.namaproduk', 'spd.qtyproduk','spd.objectruanganfk','ru.namaruangan','ru.objectdepartemenfk',
//                'prd.id as idproduk', 'prd.isgeneric', 'spd.tglpelayanan',
//
////            case when prd.isgeneric = true then 'Obat Generik(Formularium + Non Formularium)'
//                DB::raw('(case when prd.objectstatusprodukfk = 1 and prd.isgeneric =\'f\' THEN \'Obat Non Generik Non Formularium\'
//                          when prd.objectstatusprodukfk = 2 and prd.isgeneric =\'f\' THEN \'Obat Non Generik Formularium\'
//                          else \'Obat Generik(Formularium + Non Formularium)\' end) as detailobat'
//                )
//            )
//
//            ->where('prd.objectdetailjenisprodukfk', 474);
//
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('spd.tglpelayanan', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('spd.tglpelayanan', '<=', $tgl);
//        }
//        $data = $data->get();
//
//        $data10 = [];
//        $jml = 0;
//        $sama = false;
//
//        $JmlGenerik = 0;
//        $stokFormula = 0;
//        $stokObat =0;
//
//        foreach ($data as $item) {
//            $sama = false;
//            $i = 0;
//            foreach ($data10 as $hideung) {
//                if ($item->detailobat == $data10[$i]['detailobat']) {
//                    $sama = true;
//                    $jml = (float)$hideung['jumlah'] + 1;
//                    $data10[$i]['jumlah'] = $jml;
//
//                    if ($item->isgeneric == true)
//                    {
//                        $data10[$i]['JmlGeneric'] = (float)$hideung['JmlGeneric'] + 1;
//                    }
//                    $data10[$i]['Total'] = (float)$hideung['Total'] + 1;
//                    $data10[$i]['Totalitem'] = (float)$hideung['Total'] + $data10[$i]['JmlGeneric'];
//
//                }
//                $i = $i + 1;
//            }
//
//            if ($sama == false) {
//
//                if ($item->isgeneric == true)
//                {
//                    $JmlGenerik = 1;
//                    $stokFormula = 0;
//                    $stokObat =0;
//                }
//
//                $data10[] = array(
//                    'detailobat' => $item->detailobat,
//                    'Total' => 1,
//                    'JmlGeneric' => $JmlGenerik,
//                    'Totalitem' => 1 + $JmlGenerik,
//                    'jumlah' => 1,
//                );
//            }
//
//            foreach ($data10 as $key => $row) {
//                $count[$key] = $row['jumlah'];
//            }
//
//            array_multisort($count, SORT_DESC, $data10);
//        }
//
//        $result = array(
//            'data' => $data10,
//            'message' => 'as@cepot',
//        );
//        return $this->respond($result);


//    #with Mapping
        $data = \DB::table('produk_m as prd')
            ->join('stokprodukdetail_t as spd','spd.objectprodukfk','=','prd.id')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','spd.objectprodukfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('ruangan_m as ru','ru.id','=','spd.objectruanganfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->select('prd.namaproduk',
                'kl.kelompoklaporan as detailobat',
                DB::raw('count(prd.id) as jumlahitem,sum(spd.qtyproduk) as jumlahtersedia'))
            ->groupBy('prd.namaproduk','prd.id','kl.kelompoklaporan')
            ->where('prd.kdprofile', $kdProfile)
            ->where('jl.id', $KdJenisLapFarmasi);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('spd.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('spd.tglpelayanan', '<=', $tgl);
        }
        $data = $data->get();

        $data10 = [];
        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->detailobat == $data10[$i]['detailobat']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
                }
                $i = $i + 1;
            }

            if ($sama == false) {

                $data10[] = array(
                    'detailobat' => $item->detailobat,
                    'jumlahtersedia' => $item->jumlahtersedia,
                    'jumlahfortersedia'=>0,
//                    'Total' => 1,
//                    'JmlGeneric' => $JmlGenerik,
//                    'Totalitem' => 1 + $JmlGenerik,
                    'jumlah' => 1,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }
            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(
            'data' => $data10,
            'message' => 'as@lancelot',
        );
        return $this->respond($result);
    }

    public function getRL314Rujukan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapRujukan = (int) $this->settingDataFixed('KdJenisLapRujukan', $kdProfile);
        $dataLogin = $request->all();
        $data = \DB::table('antrianpasiendiperiksa_t as app')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'app.noregistrasifk')
            ->join('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'app.norec')
            ->join('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('asalrujukan_m as ar', 'ar.id', '=', 'app.objectasalrujukanfk')
            ->join('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->join('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->join('jeniskelamin_m as jk', 'jk.id', '=', 'pm.objectjeniskelaminfk')
            ->leftJoin('statuspulang_m as splng', 'splng.id', '=', 'pd.objectstatuspulangfk')
            ->leftJoin('statuskeluar_m as sklr', 'sklr.id', '=', 'pd.objectstatuskeluarfk')
            ->select('pd.tglregistrasi', 'pm.nocm', 'pd.noregistrasi', 'pm.namapasien', 'app.objectasalrujukanfk',
                'ar.asalrujukan', 'ru.namaruangan', 'dpm.namadepartemen', 'pg.namalengkap', 'pm.objectjeniskelaminfk',
                'pd.objectstatuspulangfk','splng.statuspulang', 'sklr.statuskeluar',
                'kl.kelompoklaporan as jenis_spesialisasi'
//                DB::raw('(case when ru.id in (3, 97, 237, 251, 458, 464, 45, 465, 42, 75, 463) then \'Obsterik & Ginekologi\'
//                        when ru.id in (247, 27, 452, 18, 448, 322, 28, 241, 245, 28,
//                                       76, 74, 62, 63, 64, 65, 66, 72, 73, 92, 67) then \'Kesehatan Anak\'
//                        when ru.id = 9 then \'Penyakit Dalam\'
//                        when ru.id = 6 then \'THT\'
//                        when ru.id = 5 then \'Mata\'
//                        when ru.id = 7 then \'Kulit\'
//                        when ru.id = 4 then \'Gigi\'
//                        when ru.id = 35 then \'Radiologi\'
//                        when ru.id = 276 then \'Laboratorium\'
//                        when ru.id in (14, 11, 12, 13, 240, 455, 198, 459, 14, 44, 11, 12, 13,
//                                       80, 272) then \'Bedah\'
//                        when ru.id in (278, 446, 33, 32, 255, 249, 250, 248, 26, 243, 253, 453, 450,
//                                       230, 310, 319, 320, 449) then \'Spesialisasi Lain\'
//                        else \'-\' end) as jenis_spesialisasi'
//                      )
            )
            ->where('app.kdprofile', $kdProfile)
            ->where('jl.id',$KdJenisLapRujukan);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $sama = false;

        $DatangSendiri = 0;
        $FasilitasLain = 0;
        $Puskesmas = 0;
        $RsLain = 0;
        $PasienRujukan = 0;
        $DiterimaKembali= 0;

        $BackToPuskesmas = 0;
        $BackToRsAsal = 0;
        $BackToKlinik = 0;
        $BackToRs = 0;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->jenis_spesialisasi == $data10[$i]['jenis_spesialisasi']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;

                    if ($item->objectasalrujukanfk == 5)
                    {
                        $data10[$i]['dtgsendiri'] = (float)$hideung['dtgsendiri'] + 1;
                    }
                    elseif ($item->objectasalrujukanfk == 1)
                    {
                        $data10[$i]['puskesmas'] = (float)$hideung['puskesmas'] + 1;
                    }
                    elseif ($item->objectasalrujukanfk == 2)
                    {
                        $data10[$i]['hospital'] = (float)$hideung['hospital'] + 1;
                    }
                    elseif ($item->objectasalrujukanfk == 3)
                    {
                        $data10[$i]['klinik'] = (float)$hideung['klinik'] + 1;
                    }
                    elseif ($item->objectasalrujukanfk == 4)
                    {
                        $data10[$i]['PasienRujukan'] = (float)$hideung['PasienRujukan'] + 1;
                    }
                    if ($item->objectasalrujukanfk == 6)
                    {
                        $data10[$i]['DTrmaKembali'] = (float)$hideung['DTrmaKembali'] + 1;
                    }
                    if ($item->objectasalrujukanfk == 6)
                    {
                        $data10[$i]['DTrmaKembali'] = (float)$hideung['DTrmaKembali'] + 1;
                    }
                    if ($item->objectstatuspulangfk == 5)
                    {
                        $data10[$i]['DKembalikanKRs'] = (float)$hideung['DKembalikanKRs'] + 1;
                    }
                    if ($item->objectstatuspulangfk == 10)
                    {
                        $data10[$i]['DKembalikanKPuskes'] = (float)$hideung['DKembalikanKPuskes'] + 1;
                    }
                    if ($item->objectstatuspulangfk == 11)
                    {
                        $data10[$i]['DKembalikanKKlinik'] = (float)$hideung['DKembalikanKKlinik'] + 1;
                    }
//                    $data10[$i]['total'] = $data10[$i]['jmlBaruL'] + $data10[$i]['jmlBaruP'];
                }
                $i = $i + 1;
            }

            if ($sama == false) {

                if ($item->objectasalrujukanfk == 5)
                {
                    $DatangSendiri = 1;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectasalrujukanfk == 1)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 1;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectasalrujukanfk == 2)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 1;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectasalrujukanfk == 3)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 1;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                } elseif ($item->objectasalrujukanfk == 6)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 1;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectasalrujukanfk == 4)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 1;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectstatuspulangfk == 5)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 1;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectstatuspulangfk == 10)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 1;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 0;
                    $BackToRs = 0;
                }
                elseif ($item->objectstatuspulangfk == 11)
                {
                    $DatangSendiri = 0;
                    $FasilitasLain = 0;
                    $Puskesmas = 0;
                    $RsLain = 0;
                    $PasienRujukan = 0;
                    $DiterimaKembali= 0;

                    $BackToPuskesmas = 0;
                    $BackToRsAsal = 0;
                    $BackToKlinik = 1;
                    $BackToRs = 0;
                }

                $data10[] = array(
                    'jenis_spesialisasi' => $item->jenis_spesialisasi,
                    'DTrmaKembali' => $DiterimaKembali,
                    'dtgsendiri' => $DatangSendiri,
                    'puskesmas'=> $Puskesmas,
                    'hospital' => $RsLain,
                    'klinik' => $FasilitasLain,
                    'PasienRujukan' => $PasienRujukan,
                    'DKembalikanKRs' => $BackToRsAsal,
                    'DKembalikanKPuskes' => $BackToPuskesmas,
                    'DKembalikanKKlinik' => $BackToKlinik,
                    'jumlah' => 1,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getRL315CaraBayar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapCaraBayar = (int) $this->settingDataFixed('KdJenisLapCaraBayar', $kdProfile);
//        $dataLogin = $request->all();
//        $data = \DB::table('pasiendaftar_t as pd ')
//            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
//            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
//            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
//            ->leftJoin('strukbuktipenerimaancarabayar_t as sbmcb', 'sbmcb.nosbmfk', '=', 'sbm.norec')
//            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
//            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
//            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
//            ->leftJoin('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
//            ->leftJoin('kelas_m as kel', 'kel.id', '=', 'pd.objectkelasfk')
//            ->leftJoin('carabayar_m as cb', 'cb.id', '=', 'sbmcb.objectcarabayarfk')
//            ->select('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.id', 'kp.kelompokpasien as kdekelompokpasien', 'pd.tglpulang as tglpulang',
//                'pd.statuspasien', 'jk.jeniskelamin', 'p.tgllahir as tglLahir', 'kel.namakelas', 'pd.nosbmlastfk', 'cb.carabayar', 'ru.objectdepartemenfk as kddepartemen',
//                DB::raw('EXTRACT(YEAR FROM current_date) - EXTRACT(YEAR FROM tgllahir) as umur,
//                         (case when kp.id = 2 then \'Asuransi Pemerintah\'
//                          when kp.id in (3, 5) then \'Asuransi Swasta\'
//                          when kp.id = 6 then \'Keringanan (Cost Sharing)\'
//                          when kp.id = 1 then \'Membayar Sendiri\'
//                          else null end) as golbayar,
//                          (case when kp.id = 2 then \'2.1\'
//                           when kp.id in (3, 5) then \'2.2\'
//                           when kp . id = 6 then \'3\'
//                           when kp . id = 1 then \'1\'
//                           else null end) as idbayar')
//
//            )
//            ->groupBy('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien',
//                'kp.kelompokpasien', 'pd.tglpulang', 'pd.statuspasien', 'jk.jeniskelamin', 'p.tgllahir',
//                'pd.nosbmlastfk', 'cb.carabayar', 'kel.namakelas', 'kp.id', 'ru.objectdepartemenfk');
//
//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];
//            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
//        }
//        $data = $data->get();
//
//        $data10 = [];
//        $jml = 0;
//        $sama = false;
//        $jmlUmum = 0;
//        $JmlBpjs = 0;
//        $JmlAsuransi = 0;
//        $JmlLain = 0;
//        $JmlRawatBpjs = 0;
//        $JmlRawatUmum = 0;
//        $JmlRawatPerjanjian = 0;
//        $JmlRawatAsuransi = 0;
//
//        $JmlJalanBpjs = 0;
//        $JmlJalanUmum = 0;
//        $JmlJalanPerjanjian = 0;
//        $JmlJalanAsuransi = 0;
//
//
//        foreach ($data as $item) {
//            $sama = false;
//            $i = 0;
//            foreach ($data10 as $hideung) {
//                if ($item->golbayar == $data10[$i]['golbayar']) {
//                    $sama = true;
//                    $jml = (float)$hideung['jumlah'] + 1;
//                    $data10[$i]['jumlah'] = $jml;
////                  Pasien RI Masih Dirawat
//                    if ($item->golbayar == 'Asuransi Pemerintah' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                        $data10[$i]['jmldrBpjs'] = (float)$hideung['jmldrBpjs'] + 1;
//                    } else if ($item->golbayar == 'Asuransi Swasta' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                        $data10[$i]['jmldrAsuransi'] = (float)$hideung['jmldrAsuransi'] + 1;
//                    } else if ($item->golbayar == 'Membayar Sendiri' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                        $data10[$i]['jmldrUmum'] = (float)$hideung['jmldrUmum'] + 1;
//                    } else if ($item->golbayar == 'Keringanan (Cost Sharing)' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                        $data10[$i]['jmldrPerjanjian'] = (float)$hideung['jmldrPerjanjian'] + 1;
//                    } //                  Pasien RI Sudah Pulang
//                    else if ($item->golbayar == 'Asuransi Pemerintah' && $item->kddepartemen == 16) {
//                        $data10[$i]['jmlklBpjs'] = (float)$hideung['jmlklBpjs'] + 1;
//                    } else if ($item->golbayar == 'Asuransi Swasta' && $item->kddepartemen == 16) {
//                        $data10[$i]['jmlklAsuransi'] = (float)$hideung['jmlklAsuransi'] + 1;
//                    } else if ($item->golbayar == 'Membayar Sendiri' && $item->kddepartemen == 16) {
//                        $data10[$i]['jmlklUmum'] = (float)$hideung['jmlklUmum'] + 1;
//                    } else if ($item->golbayar == 'Keringanan (Cost Sharing)' && $item->kddepartemen == 16) {
//                        $data10[$i]['jmlklPerjanjian'] = (float)$hideung['jmlklPerjanjian'] + 1;
//                    } //                  RawatJalan
//                    else if ($item->golbayar == 'Asuransi Pemerintah' && $item->kddepartemen == 18) {
//                        $data10[$i]['jmlriBpjs'] = (float)$hideung['jmlriBpjs'] + 1;
//                    } else if ($item->golbayar == 'Asuransi Swasta' && $item->kddepartemen == 18) {
//                        $data10[$i]['jmlriAsuransi'] = (float)$hideung['jmlriAsuransi'] + 1;
//                    } else if ($item->golbayar == 'Membayar Sendiri' && $item->kddepartemen == 18) {
//                        $data10[$i]['jmlriUmum'] = (float)$hideung['jmlriUmum'] + 1;
//                    } else if ($item->golbayar == 'Keringanan (Cost Sharing)' && $item->kddepartemen == 18) {
//                        $data10[$i]['jmlriPerjanjian'] = (float)$hideung['jmlriPerjanjian'] + 1;
//                    }
//                    //              Laboratorium
////                    $data10[$i]['total'] = $data10[$i]['jmlBaruL'] + $data10[$i]['jmlBaruP'];
//                }
//                $i = $i + 1;
//            }
//
//            if ($sama == false) {
////              Pasien RI Masih Dirawat
//                if ($item->golbayar == 'Asuransi Pemerintah' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
//                    $JmlRawatBpjs = 1;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Asuransi Swasta' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Membayar Sendiri' && $item->tglpulang == null && $item->kddepartemen == 16) {
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Keringanan (Cost Sharing)' && $item->tglpulang == null && $item->kddepartemen == 16) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } //              Pasien RI Sudah Pulang
//                else if ($item->golbayar == 'Asuransi Pemerintah' && $item->kddepartemen == 16) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 1;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Asuransi Swasta' && $item->kddepartemen == 16) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 1;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//
//                } else if ($item->golbayar == 'Membayar Sendiri' && $item->kddepartemen == 16) {
////                  RI Pulang
//                    $jmlUmum = 1;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
//                    $JmlRawatLain = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Keringanan (Cost Sharing)' && $item->kddepartemen == 16) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 1;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
//                    $JmlRawatLain = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } //              RawatJalan
//                else if ($item->golbayar == 'Asuransi Pemerintah' && $item->kddepartemen == 18) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
//                    $JmlRawatLain = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 1;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Asuransi Swasta' && $item->kddepartemen == 18) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
//                    $JmlRawatLain = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 1;
//                } else if ($item->golbayar == 'Membayar Sendiri' && $item->kddepartemen == 18) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
//                    $JmlRawatLain = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 1;
//                    $JmlJalanPerjanjian = 0;
//                    $JmlJalanAsuransi = 0;
//                } else if ($item->golbayar == 'Keringanan (Cost Sharing)' && $item->kddepartemen == 18) {
////                  RI Pulang
//                    $jmlUmum = 0;
//                    $JmlBpjs = 0;
//                    $JmlAsuransi = 0;
//                    $JmlPerusahaan = 0;
//                    $JmlLain = 0;
////                  RI Dirawat
//                    $JmlRawatBpjs = 0;
//                    $JmlRawatUmum = 0;
//                    $JmlRawatAsuransi = 0;
//                    $JmlRawatPerjanjian = 0;
//                    $JmlRawatLain = 0;
////                  Rawat Jalan
//                    $JmlJalanBpjs = 0;
//                    $JmlJalanUmum = 0;
//                    $JmlJalanPerjanjian = 1;
//                    $JmlJalanAsuransi = 0;
//                }
////              Laboratorium
//                $data10[] = array(
//                    'golbayar' => $item->golbayar,
//                    'idbayar' => $item->idbayar,
//                    'jmldrBpjs' => $JmlRawatBpjs,
//                    'jmldrAsuransi' => $JmlRawatAsuransi,
//                    'jmldrUmum' => $JmlRawatUmum,
//                    'jmldrPerjanjian' => $JmlRawatPerjanjian,
//                    'jmlklBpjs' => $JmlBpjs,
//                    'jmlklAsuransi' => $JmlAsuransi,
//                    'jmlklUmum' => $jmlUmum,
//                    'jmlklPerjanjian' => $JmlLain,
//                    'jmlriBpjs' => $JmlJalanBpjs,
//                    'jmlriAsuransi' => $JmlJalanAsuransi,
//                    'jmlriUmum' => $JmlJalanUmum,
//                    'jmlriPerjanjian' => $JmlJalanPerjanjian,
//                    'jumlah' => 1,
//                );
//            }
//
//            foreach ($data10 as $key => $row) {
//                $count[$key] = $row['jumlah'];
//            }
//
//            array_multisort($count, SORT_DESC, $data10);
//        }
//
//        $result = array(
//            'data' => $data10,
//            'message' => 'as@cepot',
//        );
//        return $this->respond($result);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd ')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftJoin('strukbuktipenerimaancarabayar_t as sbmcb', 'sbmcb.nosbmfk', '=', 'sbm.norec')
//            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('jeniskelamin_m as jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->leftJoin('kelas_m as kel', 'kel.id', '=', 'pd.objectkelasfk')
            ->leftJoin('carabayar_m as cb', 'cb.id', '=', 'sbmcb.objectcarabayarfk')
            ->select('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.id', 'kp.kelompokpasien as kdekelompokpasien', 'pd.tglpulang as tglpulang',
                'pd.statuspasien', 'jk.jeniskelamin', 'p.tgllahir as tglLahir', 'kel.namakelas', 'pd.nosbmlastfk', 'cb.carabayar', 'ru.objectdepartemenfk as kddepartemen',
               DB::raw('EXTRACT(YEAR FROM current_date) - EXTRACT(YEAR FROM tgllahir) as umur,
                        (case when kp.id = 2 then \'Asuransi BPJS\'
                         when kp.id in (3, 5) then \'Asuransi Swasta\'
                         when kp.id = 6 then \'Perjanjian\'
                         when kp.id = 1 then \'Membayar Sendiri\'
                         else null end) as golbayar,
                         (case when kp.id = 2 then \'2.1\'
                          when kp.id in (3, 5) then \'2.2\'
                          when kp.id = 6 then \'3\'
                          when kp.id = 1 then \'1\'
                          else null end) as idbayar')

           )

            //     DB::raw('YEAR(GETDATE()) - YEAR(tgllahir) as umur,
            //              (case when kp.id in (2,4,10) then \'Asuransi BPJS\'
            //               when kp.id = 8 then \'Jamkesda\'
            //               when kp.id in (3, 5) then \'Asuransi Swasta\'
            //               when kp.id = 6 then \'Perjanjian\'
            //               when kp.id = 1 then \'Membayar Sendiri\'
            //               else null end) as golbayar,
            //               (case when kp.id in (2,4,10) then \'2.1\'
            //               when kp.id = 8 then \'2.2\'
            //                when kp.id in (3, 5) then \'2.3\'
            //                when kp.id = 6 then \'3\'
            //                when kp.id = 1 then \'1\'
            //                else null end) as idbayar')

            // )
            ->groupBy('pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien',
                'kp.kelompokpasien', 'pd.tglpulang', 'pd.statuspasien', 'jk.jeniskelamin', 'p.tgllahir',
                'pd.nosbmlastfk', 'cb.carabayar', 'kel.namakelas', 'kp.id', 'ru.objectdepartemenfk')
            ->where('pd.kdprofile', $kdProfile)
            ->where('kp.id','<>',6);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $sama = false;
        $pasienKeluarRI = 0;
        $pasienDirawatRI = 0;
        $pasienRawatJalan=0;
        $pasienLab = 0;
        $pasienRad = 0;
        $pasienLain = 0;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->golbayar == $data10[$i]['golbayar']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
//                  Pasien RI Masih Dirawat
                    if ( $item->tglpulang == null && $item->kddepartemen == 16) {
                        $data10[$i]['pasienDirawatRI'] = (float)$hideung['pasienDirawatRI'] + 1;
                    } else if ($item->tglpulang != null && $item->kddepartemen == 16) {
                        $data10[$i]['pasienKeluarRI'] = (float)$hideung['pasienKeluarRI'] + 1;
                    }
//                    else if ( $item->kddepartemen == 18  ||$item->kddepartemen == 27||$item->kddepartemen ==3) {
//                        $data10[$i]['pasienRawatJalan'] = (float)$hideung['pasienRawatJalan'] + 1;
//                    }
                    else if ( $item->kddepartemen == 27) {
                        $data10[$i]['pasienRad'] = (float)$hideung['pasienRad'] + 1;
                    } else if ($item->kddepartemen == 3) {
                        $data10[$i]['pasienLab'] = (float)$hideung['pasienLab'] + 1;
                    } else if ($item->kddepartemen != 3 && $item->kddepartemen != 27 && $item->kddepartemen!=16) {
                        $data10[$i]['pasienLain'] = (float)$hideung['pasienLain'] + 1;
                    }
                    $data10[$i]['pasienRawatJalan'] = $data10[$i]['pasienLain'] + $data10[$i]['pasienRad']+ $data10[$i]['pasienLab'];
                }
                $i = $i + 1;
            }

            if ($sama == false) {
//              Pasien RI Masih Dirawat
//                if ( $item->tglpulang == null && $item->kddepartemen == 16) {
//                    $pasienKeluarRI = 0; $pasienDirawatRI = 1; $pasienRawatJalan=0; $pasienLab = 0;
//                    $pasienRad = 0;$pasienLain = 0;
//                } else if ($item->tglpulang != null && $item->kddepartemen == 16) {
//                    $pasienKeluarRI = 1; $pasienDirawatRI = 0; $pasienRawatJalan=0; $pasienLab = 0;
//                    $pasienRad = 0;$pasienLain = 0;
//                }
////               else if ( $item->kddepartemen == 18 &&$item->kddepartemen == 27&&$item->kddepartemen ==3) {
////                    $pasienKeluarRI = 0; $pasienDirawatRI = 0; $pasienRawatJalan=1; $pasienLab = 0;
////                    $pasienRad = 0;$pasienLain = 0;
////                }
//                else if ( $item->kddepartemen == 27) {
//                    $pasienKeluarRI = 0; $pasienDirawatRI = 0; $pasienRawatJalan=0; $pasienLab = 1;
//                    $pasienRad = 0;$pasienLain = 0;
//                } else if ($item->kddepartemen == 3) {
//                    $pasienKeluarRI = 0; $pasienDirawatRI = 0; $pasienRawatJalan=0; $pasienLab = 1;
//                    $pasienRad = 0;$pasienLain = 0;
//                } else if ($item->kddepartemen != 3 && $item->kddepartemen != 27 && $item->kddepartemen!=16) {
//                    $pasienKeluarRI = 0; $pasienDirawatRI = 0; $pasienRawatJalan=0; $pasienLab = 0;
//                    $pasienRad = 0;$pasienLain = 1;
//                }

                $data10[] = array(
                    'golbayar' => $item->golbayar,
                    'idbayar' => $item->idbayar,
                    'pasienKeluarRI' => $pasienKeluarRI,
                    'pasienDirawatRI' => $pasienDirawatRI,
                    'pasienRawatJalan' => $pasienRawatJalan,
                    'pasienLab' => $pasienLab,
                    'pasienRad' => $pasienRad,
                    'pasienLain' => $pasienLain,
                    'jumlah' => 1,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['idbayar'];
            }

            array_multisort($count, SORT_ASC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function getPelayananResep(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdJenisLapFarmasi = (int) $this->settingDataFixed('KdJenisLapFarmasi', $kdProfile);
//        $dataLogin = $request->all();
//
//        $data = \DB::table('pasiendaftar_t as pd')
//            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
//            ->leftJoin('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
//            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
//            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
//            ->join('produk_m as prd','prd.id','=','pp.produkfk')
//            ->select('prd.namaproduk','apd.objectruanganfk','ru.namaruangan','ru.objectdepartemenfk',
//                'prd.id as idproduk', 'prd.objectdetailjenisprodukfk',
//
//                DB::raw('(case when ru.objectdepartemenfk in (16, 17, 35, 26) then \'Rawat Inap\'
//                        when ru.objectdepartemenfk in (18, 27, 3, 28) then \'Rawat Jalan\'
//                        when prd.id = 24 then \'IGD\'
//                        else null end) as deptpelayanan,
//                        (case when prd.objectstatusprodukfk = 1 and prd.isgeneric =\'f\' THEN \'Obat Non Generik Non Formularium\'
//                        when prd.objectstatusprodukfk = 2 and prd.isgeneric =\'f\' THEN \'Obat Non Generik Formularium\'
//                        else \'Obat Generik(Formularium + Non Formularium)\' end) as detailobat'
//                )
//            )
//
//            ->where('prd.objectdetailjenisprodukfk', 474);
//
////        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
////            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
////        }
////        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
////            $tgl = $request['tglAkhir'];
////            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
////        }
//        $data = $data->get();
//
//        $data10 = [];
//        $jml = 0;
//        $sama = false;
//
//        $JmlRajal = 0;
//        $JmlRI = 0;
//        $JmlIGD = 0;
//
//        foreach ($data as $item) {
//            $sama = false;
//            $i = 0;
//            foreach ($data10 as $hideung) {
//                if ($item->detailobat == $data10[$i]['detailobat']) {
//                    $sama = true;
//                    $jml = (float)$hideung['jumlah'] + 1;
//                    $data10[$i]['jumlah'] = $jml;
//
//                    if ($item->deptpelayanan == 'Rawat Inap')
//                    {
//                        $data10[$i]['JmlRawatInap'] = (float)$hideung['JmlRawatInap'] + 1;
//                    }
//                    elseif ($item->deptpelayanan == 'Rawat Jalan')
//                    {
//                        $data10[$i]['Jmlrj'] = (float)$hideung['Jmlrj'] + 1;
//                    }
//                    elseif ($item->deptpelayanan == 'IGD')
//                    {
//                        $data10[$i]['JmlIGD'] = (float)$hideung['JmlIGD'] + 1;
//                    }
//                }
//                $i = $i + 1;
//            }
//
//            if ($sama == false) {
//
//                if ($item->deptpelayanan == 'Rawat Inap')
//                {
//                    $JmlRajal = 0;
//                    $JmlRI = 1;
//                    $JmlIGD = 0;
//                }
//                elseif ($item->deptpelayanan == 'Rawat Jalan')
//                {
//                    $JmlRajal = 1;
//                    $JmlRI = 0;
//                    $JmlIGD = 0;
//                }
//                elseif ($item->deptpelayanan == 'IGD')
//                {
//                    $JmlRajal = 0;
//                    $JmlRI = 0;
//                    $JmlIGD = 1;
//                }
//                $data10[] = array(
//                    'detailobat' => $item->detailobat,
//                    'JmlRawatInap' => $JmlRI,
//                    'Jmlrj' => $JmlRajal,
//                    'JmlIGD' => $JmlIGD,
//                    'jumlah' => 1,
//                );
//            }
//
//            foreach ($data10 as $key => $row) {
//                $count[$key] = $row['jumlah'];
//            }
//
//            array_multisort($count, SORT_DESC, $data10);
//        }
//
//        $result = array(
//            'data' => $data10,
//            'message' => 'as@cepot',
//        );
//        return $this->respond($result);

        //#WITH MAPPING
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->leftJoin('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->join('mapproduktolaporanrl_m as mprl','mprl.produkfk','=','pp.produkfk')
            ->join('jenislaporan_m as jl','jl.id','=','mprl.objectjenislaporanfk')
            ->join('kelompoklaporan_m as kl','kl.id','=','mprl.objectkontenlaporanfk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->join('produk_m as prd','prd.id','=','pp.produkfk')
            ->select('prd.namaproduk','apd.objectruanganfk','ru.namaruangan','kl.kelompoklaporan as detailobat',
                'pp.tglpelayanan',
                DB::raw('(case when ru.objectdepartemenfk in (16, 17, 35, 26) then \'Rawat Inap\'
                        when ru.objectdepartemenfk in (18, 27, 3, 28) then \'Rawat Jalan\'
                        when ru.objectdepartemenfk = 24 then \'IGD\'
                        else null end) as deptpelayanan'
                )
            )
            ->where('pd.kdprofile', $kdProfile)
            ->where('jl.id', $KdJenisLapFarmasi);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pp.tglpelayanan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pp.tglpelayanan', '<=', $tgl);
        }
        $data = $data->get();

        $data10 = [];
        $jml = 0;
        $sama = false;

        $JmlRajal = 0;
        $JmlRI = 0;
        $JmlIGD = 0;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->detailobat == $data10[$i]['detailobat']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;

                    if ($item->deptpelayanan == 'Rawat Inap')
                    {
                        $data10[$i]['JmlRawatInap'] = (float)$hideung['JmlRawatInap'] + 1;
                    }
                    elseif ($item->deptpelayanan == 'Rawat Jalan')
                    {
                        $data10[$i]['Jmlrj'] = (float)$hideung['Jmlrj'] + 1;
                    }
                    elseif ($item->deptpelayanan == 'IGD')
                    {
                        $data10[$i]['JmlIGD'] = (float)$hideung['JmlIGD'] + 1;
                    }
                }
                $i = $i + 1;
            }

            if ($sama == false) {

                if ($item->deptpelayanan == 'Rawat Inap')
                {
                    $JmlRajal = 0;
                    $JmlRI = 1;
                    $JmlIGD = 0;
                }
                elseif ($item->deptpelayanan == 'Rawat Jalan')
                {
                    $JmlRajal = 1;
                    $JmlRI = 0;
                    $JmlIGD = 0;
                }
                elseif ($item->deptpelayanan == 'IGD')
                {
                    $JmlRajal = 0;
                    $JmlRI = 0;
                    $JmlIGD = 1;
                }
                $data10[] = array(
                    'detailobat' => $item->detailobat,
//                    'namaruangan' => $item->namaruangan,
//                    'namaproduk' => $item->namaproduk,
//                    'tglpelayanan' => $item->tglpelayanan,
                    'JmlRawatInap' => $JmlRI,
                    'Jmlrj' => $JmlRajal,
                    'JmlIGD' => $JmlIGD,
                    'jumlah' => 1,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'as@lancelot',
        );
        return $this->respond($result);
    }
    public function getLaporanRL4aRawatInap(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kdDeptRanapAll = explode(',',$this->settingDataFixed('KdDepartemenRIAll',$kdProfile));
        $kdDepartemenRanapAll = [];
        foreach ($kdDeptRanapAll as $items){
            $kdDepartemenRanapAll []=  (int)$items;
        }
        $data = \DB::table('pasiendaftar_t as pd ')
            ->join('antrianpasiendiperiksa_t as app', 'app.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'app.norec')
            ->join('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->join('diagnosa_m as dm', 'ddp.objectdiagnosafk', '=', 'dm.id')
            ->join('diagnosabantuan_m as dbn','dbn.kddiagnosa','=','dm.kddiagnosa')
            ->join ('diagnosadtd_m as ddtd','ddtd.nodtd','=','dbn.nodtd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('jeniskelamin_m as jk', 'ps.objectjeniskelaminfk', '=', 'jk.id')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
//            ->join ('diagnosabantu as db','db.kddiagnosa','=','dm.kddiagnosa')
            ->join ('mappingrlmorbiditas_m as mpr','mpr.kddiagnosa','=','dm.kddiagnosa')
            ->select('pd.tglregistrasi','app.statuspenyakit','dbn.nodtd','dm.kddiagnosa',
                'ps.tgllahir as tglLahir','ps.objectjeniskelaminfk','pd.objectstatuskeluarfk',
                DB::raw('EXTRACT(YEAR from AGE(pd.tglregistrasi, ps.tgllahir)) as umuryear,
                                EXTRACT(MONTH from AGE(pd.tglregistrasi, ps.tgllahir)) as umurmonth,
                                EXTRACT(DAY from AGE(pd.tglregistrasi, ps.tgllahir)) as umurday,
                                lower(ddtd.golongansebabpenyakit) as golongansebabpenyakit
                       ')
            )
            //departemen inap
            ->wherein('dpm.id', $kdDepartemenRanapAll)
            ->distinct();

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        $data = $data->orderBy('dm.kddiagnosa');
        $data = $data->get();

        $data10 = [];
        $sama = false;
        $jml = 0;
        $jml6HariL = 0;
        $jml6HariP = 0;
        $jml28HariL = 0;
        $jml28HariP = 0;
        $jml1ThnL = 0;
        $jml1ThnP = 0;
        $jml4ThnL = 0;
        $jml4ThnP = 0;
        $jml14ThnL = 0;
        $jml14ThnP = 0;
        $jml24ThnL = 0;
        $jml24ThnP = 0;
        $jml44ThnL = 0;
        $jml44ThnP = 0;
        $jml64ThnL = 0;
        $jml64ThnP = 0;
        $jml65ThnL = 0;
        $jml65ThnP = 0;
        $totalMenurutL = 0;
        $totalMenurutP = 0;
        $totalKasusBaru = 0;
        $totalKunjungan = 0;
        $jmlPL=0;

        $totalMenurutP=0;
        $totalMenurutL=0;
        $jmlKeluarMati=0;
        $jmlKeluarHidup=0;
        $statusKd=true;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            $o = 0;

            foreach ($data10 as $hideung) {
                if ($item->nodtd == $data10[$i]['nodtd']) {
                    $sama = true;
                    $jml = (float)$hideung['totalAll'] + 1;
                    $data10[$i]['totalAll'] = $jml;
                    $statusKd=false;

                    if (str_contains($data10[$i]['kddiagnosa'],$item->kddiagnosa)){
                        $statusKd=true;
                    }
                    if ($statusKd == false){
                        $data10[$i]['kddiagnosa'] = $data10[$i]['kddiagnosa'] . ',' . $item->kddiagnosa;
                    }

                    //Laki =1 && Perempuan=2
                    if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml6HariL'] = (float)$hideung['jml6HariL'] + 1;
                    }else if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml6HariP'] = (float)$hideung['jml6HariP'] + 1;

                    }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml28HariL'] = (float)$hideung['jml28HariL'] + 1;
                    }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml28HariP'] = (float)$hideung['jml28HariP'] + 1;

                    }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml1ThnL'] = (float)$hideung['jml1ThnL'] + 1;
                    }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml1ThnP'] = (float)$hideung['jml1ThnP'] + 1;

                    }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml4ThnL'] = (float)$hideung['jml4ThnL'] + 1;
                    }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml4ThnP'] = (float)$hideung['jml4ThnP'] + 1;

                    }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml14ThnL'] = (float)$hideung['jml14ThnL'] + 1;
                    }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml14ThnP'] = (float)$hideung['jml14ThnP'] + 1;

                    }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml24ThnL'] = (float)$hideung['jml24ThnL'] + 1;
                    }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml24ThnP'] = (float)$hideung['jml24ThnP'] + 1;

                    }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml44ThnL'] = (float)$hideung['jml44ThnL'] + 1;
                    }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml44ThnP'] = (float)$hideung['jml44ThnP'] + 1;

                    }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml64ThnL'] = (float)$hideung['jml64ThnL'] + 1;
                    }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml64ThnP'] = (float)$hideung['jml64ThnP'] + 1;

                    }else if ($item->umuryear >= 65  && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml65ThnL'] = (float)$hideung['jml65ThnL'] + 1;
                    }else if ($item->umuryear >= 65 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml65ThnP'] = (float)$hideung['jml65ThnP'] + 1;

                    }
//                   else if ($item->statuspenyakit == 'BARU'  && $item->objectjeniskelaminfk == 1  ) {
//                        $data10[$i]['jmlKasusBaruL'] = (float)$hideung['jmlKasusBaruL'] + 1;
//                    }else if ($item->statuspenyakit == 'BARU' && $item->objectjeniskelaminfk == 2  ) {
//                        $data10[$i]['jmlKasusBaruP'] = (float)$hideung['jmlKasusBaruP'] + 1;
//                    }

                    if ($item->objectstatuskeluarfk==5)
                    {
                        $data10[$i]['jmlKeluarMati']=(float)$hideung['jmlKeluarMati']+1;
                    }
                    if ($item->objectstatuskeluarfk!=5)
                    {
                        $data10[$i]['jmlKeluarHidup']=(float)$hideung['jmlKeluarHidup']+1;
                    }

//                    $data10[$i]['totalKasusBaru'] = $data10[$i]['jmlKasusBaruP'] + $data10[$i]['jmlKasusBaruL'];
                    $data10[$i]['jmlPL'] = $data10[$i]['jml6HariL'] + $data10[$i]['jml6HariP']
                        +$data10[$i]['jml28HariL'] + $data10[$i]['jml28HariP']
                        +$data10[$i]['jml1ThnL'] + $data10[$i]['jml1ThnP']
                        +$data10[$i]['jml4ThnL'] + $data10[$i]['jml4ThnP']
                        +$data10[$i]['jml14ThnL'] + $data10[$i]['jml14ThnP']
                        +$data10[$i]['jml24ThnL'] + $data10[$i]['jml24ThnP']
                        +$data10[$i]['jml44ThnL'] + $data10[$i]['jml44ThnP']
                        +$data10[$i]['jml64ThnL'] + $data10[$i]['jml64ThnP']
                        +$data10[$i]['jml65ThnL'] + $data10[$i]['jml65ThnP'];
                    $data10[$i]['totalMenurutL'] = $data10[$i]['jml6HariL']
                        +$data10[$i]['jml28HariL']
                        +$data10[$i]['jml1ThnL']
                        +$data10[$i]['jml4ThnL']
                        +$data10[$i]['jml14ThnL']
                        +$data10[$i]['jml24ThnL']
                        +$data10[$i]['jml44ThnL']
                        +$data10[$i]['jml64ThnL']
                        +$data10[$i]['jml65ThnL'] ;
                    $data10[$i]['totalMenurutP'] = $data10[$i]['jml6HariP']
                        +$data10[$i]['jml28HariP']
                        +$data10[$i]['jml1ThnP']
                        +$data10[$i]['jml4ThnP']
                        +$data10[$i]['jml14ThnP']
                        +$data10[$i]['jml24ThnP']
                        +$data10[$i]['jml44ThnP']
                        +$data10[$i]['jml64ThnP']
                        +$data10[$i]['jml65ThnP'] ;

                }
                $i = $i + 1;
            }

            //jika false
            if ($sama == false) {
                if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 1;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 1;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 1  ) {
                    $jml28HariL = 1;
                    $jml6HariL = 1;$jml6HariP = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 1;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 1;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 1;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 1;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 1;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 1;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 1;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;$jmlKeluarMati=0; $jmlKeluarHidup=0;

                }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 1;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 1;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;

                }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 1;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 1;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;

                }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 1;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 1;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 65  && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 1;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }else if ($item->umuryear >= 65 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 1;  $jmlKeluarMati=0; $jmlKeluarHidup=0;
                }
                if($item->objectstatuskeluarfk==5)
                {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=1; $jmlKeluarHidup=0;
                }
                if($item->objectstatuskeluarfk!=5)
                {
                    $jml6HariL = 0;$jml6HariP = 0;$jml28HariL = 0;$jml28HariP = 0;$jml1ThnL = 0;
                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;  $jmlKeluarMati=0; $jmlKeluarHidup=1;
                }

//                if ($item->statuspenyakit == 'BARU'  && $item->objectjeniskelaminfk == 1  ) {
//                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
//                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
//                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;$jmlKasusBaruL = 1;$jmlKasusBaruP = 0;
//                }else if ($item->statuspenyakit == 'BARU' && $item->objectjeniskelaminfk == 2  ) {
//                    $jml1ThnP = 0;$jml4ThnL = 0;$jml4ThnP = 0;$jml14ThnL = 0;$jml14ThnP = 0;
//                    $jml24ThnL = 0;$jml24ThnP = 0;$jml44ThnL = 0;$jml44ThnP = 0;$jml64ThnL = 0;
//                    $jml64ThnP = 0;$jml65ThnL = 0;$jml65ThnP = 0;$jmlKasusBaruL = 0;$jmlKasusBaruP = 1;
//                }

                $data10[] = array(
                    'kddiagnosa'=> $item->kddiagnosa,
                    'nodtd' => $item->nodtd,
                    'golongansebabpenyakit'=> $item->golongansebabpenyakit,
                    'tglregistrasi' => $item->tglregistrasi,
                    'statuspenyakit' => $item->statuspenyakit,
                    'tglLahir' => $item->tglLahir,
                    'objectstatuskeluarfk'=>$item->objectstatuskeluarfk,
                    'umuryear' => $item->umuryear,
                    'umurmonth' => $item->umurmonth,
                    'umurday' => $item->umurday,
                    'totalAll' => 1,
                    'jml6HariL'=>  $jml6HariL,
                    'jml6HariP'=>  $jml6HariP,
                    'jml28HariL'=>  $jml28HariL,
                    'jml28HariP'=>  $jml28HariP,
                    'jml1ThnL'=>  $jml1ThnL,
                    'jml1ThnP'=>  $jml1ThnP,
                    'jml4ThnL'=>  $jml4ThnL,
                    'jml4ThnP'=>  $jml4ThnP,
                    'jml14ThnL'=>  $jml14ThnL,
                    'jml14ThnP'=>  $jml14ThnP,
                    'jml24ThnL'=>  $jml24ThnL,
                    'jml24ThnP'=>  $jml24ThnP,
                    'jml44ThnL'=>  $jml44ThnL,
                    'jml44ThnP'=>  $jml44ThnP,
                    'jml64ThnL'=>  $jml64ThnL,
                    'jml64ThnP'=>  $jml64ThnP,
                    'jml65ThnL'=>  $jml65ThnL,
                    'jml65ThnP'=>  $jml65ThnP,
                    'totalMenurutL' => $totalMenurutL,
                    'jmlKeluarMati'=>$jmlKeluarMati,
                    'jmlKeluarHidup'=>$jmlKeluarHidup,
                    'totalMenurutP'=>$totalMenurutP,
                    'totalHidupMati'=>$jmlKeluarHidup+$jmlKeluarMati,
                    'jmlPL'=>$totalMenurutL+$totalMenurutP,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['nodtd'];
            }

            array_multisort($count, SORT_ASC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }
    public function getLaporanRL4b(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $KdDeptRajalRehab = explode (',',$this->settingDataFixed('KdDeptRajalRehab',$kdProfile));
        $kdDepartemenRajalRehab = [];
        foreach ($KdDeptRajalRehab as $items){
            $kdDepartemenRajalRehab []=  (int)$items;
        }
        $data = \DB::table('pasiendaftar_t as pd ')
            ->join('antrianpasiendiperiksa_t as app', 'app.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'app.norec')
            ->leftjoin('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->leftjoin('diagnosa_m as dm', 'ddp.objectdiagnosafk', '=', 'dm.id')
            ->leftjoin('diagnosabantuan_m as dbn','dbn.kddiagnosa','=','dm.kddiagnosa')
            ->leftjoin ('diagnosadtd_m as ddtd','ddtd.nodtd','=','dbn.nodtd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('jeniskelamin_m as jk', 'ps.objectjeniskelaminfk', '=', 'jk.id')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
//            ->join ('diagnosabantu as db','db.kddiagnosa','=','dm.kddiagnosa')
            ->join ('mappingrlmorbiditas_m as mpr','mpr.kddiagnosa','=','dm.kddiagnosa')
            ->select('pd.tglregistrasi','app.statuspenyakit','dbn.nodtd','dm.kddiagnosa',
                'ps.tgllahir as tglLahir','ps.objectjeniskelaminfk',
                DB::raw('EXTRACT(YEAR from AGE(pd.tglregistrasi, ps.tgllahir)) as umuryear,
                                EXTRACT(MONTH from AGE(pd.tglregistrasi, ps.tgllahir)) as umurmonth,
                                EXTRACT(DAY from AGE(pd.tglregistrasi, ps.tgllahir)) as umurday,lower(ddtd.golongansebabpenyakit) as golongansebabpenyakit
                       ')
            )
            ->where('pd.kdprofile', $kdProfile)
            ->wherein('dpm.id', $kdDepartemenRajalRehab)
            ->distinct();


        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        $data = $data->orderBy('dm.kddiagnosa');
        $data = $data->get();

        $data10 = [];
        $sama = false;
        $jml = 0;
        $jml6HariL = 0;
        $jml6HariP = 0;
        $jml28HariL = 0;
        $jml28HariP = 0;
        $jml1ThnL = 0;
        $jml1ThnP = 0;
        $jml4ThnL = 0;
        $jml4ThnP = 0;
        $jml14ThnL = 0;
        $jml14ThnP = 0;
        $jml24ThnL = 0;
        $jml24ThnP = 0;
        $jml44ThnL = 0;
        $jml44ThnP = 0;
        $jml64ThnL = 0;
        $jml64ThnP = 0;
        $jml65ThnL = 0;
        $jml65ThnP = 0;
        $jmlKasusBaruL = 0;
        $jmlKasusBaruP = 0;
        $totalKasusBaru = 0;
        $totalKunjungan = 0;
        $totalMenurutUmur=0;
        $totalMenurutL=0;
        $totalMenurutP=0;
        $jmlPL=0;

        $statusKd=true;

        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            $o = 0;

            foreach ($data10 as $hideung) {
                if ($item->nodtd == $data10[$i]['nodtd']) {
                    $sama = true;
                    $jml = (float)$hideung['totalAll'] + 1;
                    $data10[$i]['totalAll'] = $jml;
                    $statusKd=false;

                    if (str_contains($data10[$i]['kddiagnosa'],$item->kddiagnosa)){
                        $statusKd=true;
                    }
                    if ($statusKd == false){
                        $data10[$i]['kddiagnosa'] = $data10[$i]['kddiagnosa'] . ',' . $item->kddiagnosa;
                    }

                    //Laki =1 && Perempuan=2
                    if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml6HariL'] = (float)$hideung['jml6HariL'] + 1;
                    }else if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml6HariP'] = (float)$hideung['jml6HariP'] + 1;

                    }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml28HariL'] = (float)$hideung['jml28HariL'] + 1;
                    }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml28HariP'] = (float)$hideung['jml28HariP'] + 1;

                    }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml1ThnL'] = (float)$hideung['jml1ThnL'] + 1;
                    }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml1ThnP'] = (float)$hideung['jml1ThnP'] + 1;

                    }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml4ThnL'] = (float)$hideung['jml4ThnL'] + 1;
                    }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml4ThnP'] = (float)$hideung['jml4ThnP'] + 1;

                    }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml14ThnL'] = (float)$hideung['jml14ThnL'] + 1;
                    }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml14ThnP'] = (float)$hideung['jml14ThnP'] + 1;

                    }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml24ThnL'] = (float)$hideung['jml24ThnL'] + 1;
                    }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml24ThnP'] = (float)$hideung['jml24ThnP'] + 1;

                    }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml44ThnL'] = (float)$hideung['jml44ThnL'] + 1;
                    }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml44ThnP'] = (float)$hideung['jml44ThnP'] + 1;

                    }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml64ThnL'] = (float)$hideung['jml64ThnL'] + 1;
                    }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml64ThnP'] = (float)$hideung['jml64ThnP'] + 1;

                    }else if ($item->umuryear >= 65  && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jml65ThnL'] = (float)$hideung['jml65ThnL'] + 1;
                    }else if ($item->umuryear >= 65 && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jml65ThnP'] = (float)$hideung['jml65ThnP'] + 1;

                    }
                    if ($item->statuspenyakit == 'BARU'  && $item->objectjeniskelaminfk == 1  ) {
                        $data10[$i]['jmlKasusBaruL'] = (float)$hideung['jmlKasusBaruL'] + 1;
                    }else if ($item->statuspenyakit == 'BARU' && $item->objectjeniskelaminfk == 2  ) {
                        $data10[$i]['jmlKasusBaruP'] = (float)$hideung['jmlKasusBaruP'] + 1;
                    }
                    $data10[$i]['totalKasusBaru'] = $data10[$i]['jmlKasusBaruP'] + $data10[$i]['jmlKasusBaruL'];

                    $data10[$i]['jmlPL'] = $data10[$i]['jml6HariL'] + $data10[$i]['jml6HariP']
                        +$data10[$i]['jml28HariL'] + $data10[$i]['jml28HariP']
                        +$data10[$i]['jml1ThnL'] + $data10[$i]['jml1ThnP']
                        +$data10[$i]['jml4ThnL'] + $data10[$i]['jml4ThnP']
                        +$data10[$i]['jml14ThnL'] + $data10[$i]['jml14ThnP']
                        +$data10[$i]['jml24ThnL'] + $data10[$i]['jml24ThnP']
                        +$data10[$i]['jml44ThnL'] + $data10[$i]['jml44ThnP']
                        +$data10[$i]['jml64ThnL'] + $data10[$i]['jml64ThnP']
                        +$data10[$i]['jml65ThnL'] + $data10[$i]['jml65ThnP'];
                    $data10[$i]['totalMenurutL'] = $data10[$i]['jml6HariL']
                        +$data10[$i]['jml28HariL']
                        +$data10[$i]['jml1ThnL']
                        +$data10[$i]['jml4ThnL']
                        +$data10[$i]['jml14ThnL']
                        +$data10[$i]['jml24ThnL']
                        +$data10[$i]['jml44ThnL']
                        +$data10[$i]['jml64ThnL']
                        +$data10[$i]['jml65ThnL'] ;
                    $data10[$i]['totalMenurutP'] = $data10[$i]['jml6HariP']
                        +$data10[$i]['jml28HariP']
                        +$data10[$i]['jml1ThnP']
                        +$data10[$i]['jml4ThnP']
                        +$data10[$i]['jml14ThnP']
                        +$data10[$i]['jml24ThnP']
                        +$data10[$i]['jml44ThnP']
                        +$data10[$i]['jml64ThnP']
                        +$data10[$i]['jml65ThnP'] ;
                    $data10[$i]['totalKasusBaru'] = $data10[$i]['jmlKasusBaruP'] + $data10[$i]['jmlKasusBaruL'];

                    $data10[$i]['totalMenurutUmur'] = $data10[$i]['jml6HariL'] + $data10[$i]['jml6HariP']
                        +$data10[$i]['jml28HariL'] + $data10[$i]['jml28HariP']
                        +$data10[$i]['jml1ThnL'] + $data10[$i]['jml1ThnP']
                        +$data10[$i]['jml4ThnL'] + $data10[$i]['jml4ThnP']
                        +$data10[$i]['jml14ThnL'] + $data10[$i]['jml14ThnP']
                        +$data10[$i]['jml24ThnL'] + $data10[$i]['jml24ThnP']
                        +$data10[$i]['jml44ThnL'] + $data10[$i]['jml44ThnP']
                        +$data10[$i]['jml64ThnL'] + $data10[$i]['jml64ThnP']
                        +$data10[$i]['jml65ThnL'] + $data10[$i]['jml65ThnP'];

                }
                $i = $i + 1;
            }

            //jika false
            if ($sama == false) {
                if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 1; $jml6HariP = 0; $jml28HariL = 0; $jml28HariP = 0; $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umurday <= 6 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 1;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 1;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umurday >= 7 && $item->umurday <= 28 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 1;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 1;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umurday > 28 && $item->umuryear < 1 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;$jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 1;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 1;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 1 && $item->umuryear <= 4 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 1;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 1;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 5 && $item->umuryear <= 14 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 1;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 1;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 15 && $item->umuryear <= 24 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 1;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 1;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 25 && $item->umuryear <= 44 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 1;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 1;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 45 && $item->umuryear <= 64 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 1;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }else if ($item->umuryear >= 65  && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 1;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;
                }else if ($item->umuryear >= 65 && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 1;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 0;

                }

                if ($item->statuspenyakit == 'BARU'  && $item->objectjeniskelaminfk == 1  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 1;
                    $jmlKasusBaruP = 0;
                }else if ($item->statuspenyakit == 'BARU' && $item->objectjeniskelaminfk == 2  ) {
                    $jml6HariL = 0;
                    $jml6HariP = 0;
                    $jml28HariL = 0;
                    $jml28HariP = 0;
                    $jml1ThnL = 0;
                    $jml1ThnP = 0;
                    $jml4ThnL = 0;
                    $jml4ThnP = 0;
                    $jml14ThnL = 0;
                    $jml14ThnP = 0;
                    $jml24ThnL = 0;
                    $jml24ThnP = 0;
                    $jml44ThnL = 0;
                    $jml44ThnP = 0;
                    $jml64ThnL = 0;
                    $jml64ThnP = 0;
                    $jml65ThnL = 0;
                    $jml65ThnP = 0;
                    $jmlKasusBaruL = 0;
                    $jmlKasusBaruP = 1;
                }

                $data10[] = array(
                    'kddiagnosa'=> $item->kddiagnosa,
                    'nodtd' => $item->nodtd,
                    'golongansebabpenyakit'=> $item->golongansebabpenyakit,
                    'tglregistrasi' => $item->tglregistrasi,
                    'statuspenyakit' => $item->statuspenyakit,
                    'tglLahir' => $item->tglLahir,
                    'umuryear' => $item->umuryear,
                    'umurmonth' => $item->umurmonth,
                    'umurday' => $item->umurday,
                    'totalAll' => 1,
                    'jml6HariL'=>  $jml6HariL,
                    'jml6HariP'=>  $jml6HariP,
                    'jml28HariL'=>  $jml28HariL,
                    'jml28HariP'=>  $jml28HariP,
                    'jml1ThnL'=>  $jml1ThnL,
                    'jml1ThnP'=>  $jml1ThnP,
                    'jml4ThnL'=>  $jml4ThnL,
                    'jml4ThnP'=>  $jml4ThnP,
                    'jml14ThnL'=>  $jml14ThnL,
                    'jml14ThnP'=>  $jml14ThnP,
                    'jml24ThnL'=>  $jml24ThnL,
                    'jml24ThnP'=>  $jml24ThnP,
                    'jml44ThnL'=>  $jml44ThnL,
                    'jml44ThnP'=>  $jml44ThnP,
                    'jml64ThnL'=>  $jml64ThnL,
                    'jml64ThnP'=>  $jml64ThnP,
                    'jml65ThnL'=>  $jml65ThnL,
                    'jml65ThnP'=>  $jml65ThnP,
                    'jmlKasusBaruL' =>$jmlKasusBaruL,
                    'jmlKasusBaruP' =>$jmlKasusBaruP,
                    'totalKasusBaru'=>$totalKasusBaru,
                    'totalMenurutUmur'=>$totalMenurutUmur,
                    'totalMenurutL'=>$totalMenurutL,
                    'totalMenurutP'=>$totalMenurutP,
                    'jmlPL'=>$jmlPL,

                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['nodtd'];
            }

            array_multisort($count, SORT_ASC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'er@epic',

        );

        return $this->respond($result);
    }

    public function getDataLaporanRL51(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->leftjoin('alamat_m as alm ','alm.nocmfk','=','pm.id')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('batalregistrasi_t as btl','btl.pasiendaftarfk','=','pd.norec')
            ->select( DB::raw('count(pd.norec) as jumlah,pd.statuspasien,ru.objectdepartemenfk'))
        ->where('pd.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk ', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        $data = $data->whereNull('btl.pasiendaftarfk');
        $data = $data->groupBy('pd.statuspasien','ru.objectdepartemenfk');
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'Cepot',
        );
        return $this->respond($result);
    }
    public function getDataLaporanRL52(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->JOIN('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->LEFTJOIN('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->LEFTJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->LEFTJOIN('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->select('ru.namaruangan',
                DB::raw('COUNT(ru.namaruangan) as jumlahKunjungan')
            )
            ->where('pd.kdprofile', $kdProfile);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '<=', $request['tglAkhir']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('dpm.id', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        $data = $data->groupBy('dept.namadepartemen', 'ru.namaruangan');
        $data = $data->orderBy('ru.namaruangan', 'ASC');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => '@vandrian',
        );

        return $this->respond($result);
    }
    public function getDataLaporanRL53(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kdDeptRanapAll = explode(',',$this->settingDataFixed('KdDepartemenRIAll',$kdProfile));
        $kdDepartemenRanapAll = [];
        foreach ($kdDeptRanapAll as $items){
            $kdDepartemenRanapAll []=  (int)$items;
        }
//        $dataLogin = $request->all();
        $datadiagnosa = \DB::table('antrianpasiendiperiksa_t as app')
            ->LEFTJOIN('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'app.norec')
            ->LEFTJOIN('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->JOIN('diagnosa_m as dm', 'ddp.objectdiagnosafk', '=', 'dm.id')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'app.noregistrasifk')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->LeftJOIN('jeniskelamin_m as jk', 'ps.objectjeniskelaminfk', '=', 'jk.id')
            ->LeftJOIN('ruangan_m as ru', 'ru.id', '=', 'app.objectruanganfk')
            ->LeftJOIN('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->select('dm.kddiagnosa', 'dm.namadiagnosa', 'pd.noregistrasi','pd.objectstatuskeluarfk','ps.objectjeniskelaminfk', 'jk.jeniskelamin',
                'pd.tglregistrasi', 'pd.tglpulang', 'pd.tglmeninggal', 'ru.reportdisplay', 'app.noregistrasifk as noregistrasifk','pd.statuspasien',
                'dpm.reportdisplay', 'dpm.id')
            ->where('app.kdprofile',$kdProfile);
//            ->wherein('dpm.id', $kdDepartemenRanapAll);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $datadiagnosa = $datadiagnosa->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $datadiagnosa = $datadiagnosa->where('app.objectruanganfk', '=', $request['idRuangan']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $datadiagnosa = $datadiagnosa->where('pd.tglregistrasi', '<=', $tgl);
        }
//            $datadiagnosa = $datadiagnosa->orderBy('nomor_urut','ASC');
        $datadiagnosa = $datadiagnosa->get();

        $data10 = [];
        $jml = 0;
        $jmlM=0;
        $jmlL = 0;
        $jmlLM=0;
        $sama = false;
        $jmlP = 0;
        $jmlPM=0;
        $jmlHidupMati = 0;
        foreach ($datadiagnosa as $item) {
            $sama = false;
            $i = 0;
            $o = 0;
            foreach ($data10 as $hideung) {
                if ($item->kddiagnosa == $data10[$i]['kddiagnosa']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
                    if ($item->objectjeniskelaminfk == 1 && ($item->objectstatuskeluarfk != 5 ||$item->objectstatuskeluarfk == '')) {
                        $data10[$i]['jumlahLKH'] = (float)$hideung['jumlahLKH'] + 1;
                    } else if ($item->objectjeniskelaminfk == 2 &&( $item->objectstatuskeluarfk != 5  ||$item->objectstatuskeluarfk == '')){
                        $data10[$i]['jumlahPRH'] = (float)$hideung['jumlahPRH'] + 1;
                    }
                    else if ($item->objectjeniskelaminfk == 1 && $item->objectstatuskeluarfk == 5) {
                        $data10[$i]['jumlahLKM'] = (float)$hideung['jumlahLKM'] + 1;
                    } else if ($item->objectjeniskelaminfk == 2 && $item->objectstatuskeluarfk == 5) {
                        $data10[$i]['jumlahPRM'] = (float)$hideung['jumlahPRM'] + 1;
                    }
                }
                $i = $i + 1;
            }

            if ($sama == false) {
                if ($item->objectjeniskelaminfk == 1 && $item->objectstatuskeluarfk == 1) {
                    $jmlL = 1;
                    $jmlP = 0;
                    $jmlPM = 0;
                    $jmlLM = 0;
                } else if ($item->objectjeniskelaminfk == 2 && $item->objectstatuskeluarfk == 1){
                    $jmlL = 0;
                    $jmlP = 1;
                    $jmlPM = 0;
                    $jmlLM = 0;
                }
                else if ($item->objectjeniskelaminfk == 1 && $item->objectstatuskeluarfk == 5) {
                    $jmlL = 0;
                    $jmlP = 0;
                    $jmlPM = 0;
                    $jmlLM = 1;
                } else if ($item->objectjeniskelaminfk == 2 && $item->objectstatuskeluarfk == 5){
                    $jmlL = 0;
                    $jmlP = 0;
                    $jmlLM = 0;
                    $jmlPM = 1;
                }

                $data10[] = array(
                    'kddiagnosa' => $item->kddiagnosa,
                    'namadiagnosa' => $item->namadiagnosa,
                    'jumlah' => 1,
                    'jumlahLKH' => $jmlL,
                    'jumlahPRH' => $jmlP,
                    'jumlahLKM' => $jmlLM,
                    'jumlahPRM' => $jmlPM,
                );
            }

            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }

            array_multisort($count, SORT_DESC, $data10);
        }
        $result = array(

//              'data' => $datadiagnosa,
            'data' => $data10,
            'message' => 'as@vandrian',

        );

        return $this->respond($result);
    }
    public function getDataLaporanRL54(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $KdDeptRajalAll = explode(',', $this->settingDataFixed('KdDeptRajalAll', $kdProfile));
        $kdDepartemenRajalAll = [];
        foreach ($KdDeptRajalAll as $itemPelayanan){
            $kdDepartemenRajalAll []=  (int)$itemPelayanan;
        }
        $datadiagnosa = \DB::table('antrianpasiendiperiksa_t as app')
            ->LEFTJOIN('diagnosapasien_t as dp', 'dp.noregistrasifk', '=', 'app.norec')
            ->LEFTJOIN('detaildiagnosapasien_t as ddp', 'ddp.objectdiagnosapasienfk', '=', 'dp.norec')
            ->JOIN('diagnosa_m as dm', 'ddp.objectdiagnosafk', '=', 'dm.id')
            ->JOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'app.noregistrasifk')
            ->JOIN('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->LeftJOIN('jeniskelamin_m as jk', 'ps.objectjeniskelaminfk', '=', 'jk.id')
            ->LeftJOIN('ruangan_m as ru', 'ru.id', '=', 'app.objectruanganfk')
            ->LeftJOIN('departemen_m as dpm', 'dpm.id', '=', 'ru.objectdepartemenfk')
            ->select('dm.kddiagnosa', 'dm.namadiagnosa','app.statuspenyakit', 'pd.noregistrasi', 'ps.objectjeniskelaminfk', 'jk.jeniskelamin',
                'pd.tglregistrasi', 'pd.tglpulang', 'pd.tglmeninggal', 'ru.reportdisplay', 'app.noregistrasifk as noregistrasifk','pd.statuspasien',
                'dpm.reportdisplay', 'dpm.id')
            ->where('app.kdprofile', $kdProfile);
//            ->wherein('dpm.id', $kdDepartemenRajalAll);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $datadiagnosa = $datadiagnosa->where('pd.tglpulang', '>=', $request['tglAwal']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $datadiagnosa = $datadiagnosa->where('app.objectruanganfk', '=', $request['idRuangan']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $datadiagnosa = $datadiagnosa->where('pd.tglpulang', '<=', $tgl);
        }
//            $datadiagnosa = $datadiagnosa->orderBy('nomor_urut','ASC');
        $datadiagnosa = $datadiagnosa->get();

        $data10 = [];
        $sama = false;
        $jml = 0;
        $jmlL = 0;
        $jmlP = 0;
        $jmlLL = 0;
        $jmlPL = 0;
        $jmlHidupMati = 0;
        foreach ($datadiagnosa as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->kddiagnosa == $data10[$i]['kddiagnosa']) {
                    $sama = true;
                    $jml = (float)$hideung['jumlah'] + 1;
                    $data10[$i]['jumlah'] = $jml;
//asalna statuspenyakit jadi statuspasien
                    if ($item->objectjeniskelaminfk == 1 && $item->statuspasien == 'BARU') {
                        $data10[$i]['jumlahLKH'] = (float)$hideung['jumlahLKH'] + 1;
                    } else if ($item->objectjeniskelaminfk == 2 && $item->statuspasien == 'BARU')  {
                        $data10[$i]['jumlahPRH'] = (float)$hideung['jumlahPRH'] + 1;
                    }
                    else if ($item->objectjeniskelaminfk == 1 && $item->statuspasien == 'LAMA') {
                        $data10[$i]['jumlahLKL'] = (float)$hideung['jumlahLKL'] + 1;
                    } else if ($item->objectjeniskelaminfk == 2 && $item->statuspasien == 'LAMA')  {
                        $data10[$i]['jumlahPRL'] = (float)$hideung['jumlahPRL'] + 1;
                    }
                    $data10[$i]['totalbaru'] = $data10[$i]['jumlahLKH'] + $data10[$i]['jumlahPRH'];
                    $data10[$i]['totallama'] = $data10[$i]['jumlahLKL'] + $data10[$i]['jumlahPRL'];
                }
                $i = $i + 1;
            }
            if ($sama == false) {
                if ($item->objectjeniskelaminfk == 1 && $item-> statuspasien == 'BARU') {
                    $jmlL = 1;
                    $jmlP = 0;
                } else if ($item->objectjeniskelaminfk == 2 && $item->statuspasien == 'BARU')  {
                    $jmlL = 0;
                    $jmlP = 1;
                }

                $data10[] = array(
//                    'nourut'=>$item->nomor_urut,
//                    'no'=>$i+1,
                    'kddiagnosa' => $item->kddiagnosa,
                    'namadiagnosa' => $item->namadiagnosa,
                    'jumlah' => 1,
                    'jumlahLKH' => $jmlL,
                    'jumlahPRH' => $jmlP,
                    'jumlahLKL' => $jmlLL,
                    'jumlahPRL' => $jmlPL,
                    'totalbaru' => $jmlL + $jmlP,
                    'totallama' => $jmlLL + $jmlPL,
                );
            }


            foreach ($data10 as $key => $row) {
                $count[$key] = $row['jumlah'];
            }
            array_multisort($count, SORT_DESC, $data10);
        }

        $result = array(
            'data' => $data10,
            'message' => 'as@vandrian',

        );
        return $this->respond($result);
    }
    public function getDataLaporanPasienMasuk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idDepRawatInap = (int) $this->settingDataFixed('idDepRawatInap', $kdProfile);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->JOIN ('registrasipelayananpasien_t as rpp','rpp.noregistrasifk','=','pd.norec')
            ->JOIN ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->leftJOIN ('detaildiagnosapasien_t as ddp','ddp.noregistrasifk','=','apd.norec')
            ->leftJOIN ('diagnosa_m as d','d.id','=','ddp.objectdiagnosafk')
            ->leftJOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJOIN ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJOIN ('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJOIN ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftJOIN ('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->leftJOIN ('kamar_m as km','km.id','=','apd.objectkamarfk')
            ->leftJOIN ('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->leftJOIN ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->select('pd.noregistrasi','ps.nocm', 'ps.namapasien','d.kddiagnosa',
                'kps.kelompokpasien','rek.namarekanan',
                'jk.jeniskelamin', 'apd.tglkeluar', 'apd.nobed', 'apd.objectkamarfk','km.namakamar',
                'kls.namakelas','d.kddiagnosa','d.namadiagnosa','ru.namaruangan','pd.tglregistrasi as tglmasukinap',
                'pd.objectstatuskeluarfk',
                DB::raw('pg.namalengkap as dokter,
                case WHEn pd.tglpulang is not null then \'Dipulangkan\' else \'Masih Dirawat\' end as carakeluar,
                  case when ru.objectdepartemenfk =16 then \'Masuk\' else null end as mutasi'
                )
            )
            ->where('pd.kdprofile', $kdProfile)
            ->Where('ru.objectdepartemenfk','=',$idDepRawatInap);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['idRuangan']);
        }
        if (isset($request['idKelas']) && $request['idKelas'] != "" && $request['idKelas'] != "undefined") {
            $data = $data->where('kls.id', '=', $request['idKelas']);
        }
        $data = $data->distinct();
        $data = $data->get();

        $data10 =[];
        $sama=false;

        foreach ($data as $item) {
            $sama=false;
            $i=0;
            foreach ($data10 as $hideung){
                if ($item->noregistrasi == $data10[$i]['noregistrasi']){
                    $sama=true;
                    $data10[$i]['kddiagnosa']=$data10[$i]['kddiagnosa']. ', '.($item->kddiagnosa);
                }
                $i=$i+1;
            }
            if ($sama==false){
                $data10[]=array(
                    'noregistrasi'=>$item->noregistrasi,
                    'namapasien'=>$item->namapasien,
                    'nocm'=>$item->nocm,
                    'namakamar'=>$item->namakamar,
                    'nobed'=>$item->nobed,
                    'namaruangan'=>$item->namaruangan,
                    'kelompokpasien'=>$item->kelompokpasien,
                    'namarekanan'=>$item->namarekanan,
                    'tglmasukinap'=>$item->tglmasukinap,
                    'dokter'=>$item->dokter,
                    'namakelas'=>$item->namakelas,
                    'carakeluar'=>$item->carakeluar,
                    'kddiagnosa'=>$item->kddiagnosa,
                    'mutasi'=>$item->mutasi,
                );
            }
        }

        $result = array(
            'data' => $data10,
            'message' => 'as@Ramdan',
        );

        return $this->respond($result);
    }
    public function getDataLaporanPasienKeluar(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $kdDeptRanapAll = explode(',',$this->settingDataFixed('KdDepartemenRIAll',$kdProfile));
        $kdDepartemenRanapAll = [];
        foreach ($kdDeptRanapAll as $items){
            $kdDepartemenRanapAll []=  (int)$items;
        }
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->Join ('registrasipelayananpasien_t as rpp','rpp.noregistrasifk','=','pd.norec')
            ->Join ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->leftJOIN ('detaildiagnosapasien_t as ddp','ddp.noregistrasifk','=','apd.norec')
            ->leftJOIN ('diagnosa_m as d','d.id','=','ddp.objectdiagnosafk')
            ->leftJOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJOIN ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJOIN ('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJOIN ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftJOIN ('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->leftJOIN ('kamar_m as km','km.id','=','apd.objectkamarfk')
            ->leftJOIN ('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->leftJOIN ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->select('pd.noregistrasi','ps.nocm', 'ps.namapasien','d.kddiagnosa',
                'kps.kelompokpasien','rek.namarekanan',
                'jk.jeniskelamin', 'apd.tglkeluar', 'apd.nobed', 'apd.objectkamarfk','km.namakamar',
                'kls.namakelas','d.kddiagnosa','d.namadiagnosa','ru.namaruangan','pd.tglregistrasi as tglmasukinap',
                'pd.objectstatuskeluarfk','pd.tglpulang',
                DB::raw('pg.namalengkap as dokter,
                case when pd.tglpulang is not null then \'Keluar\' end as mutasi,
                 case when pd.tglpulang is not null then \'Dipulangkan\' end as carakeluar'
                ))
            ->where('pd.kdprofile',$kdProfile)
            ->whereNotNull('pd.tglpulang')
            ->WhereIN('ru.objectdepartemenfk',$kdDepartemenRanapAll);


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
        if (isset($request['idKelas']) && $request['idKelas'] != "" && $request['idKelas'] != "undefined") {
            $data = $data->where('kls.id', '=', $request['idKelas']);
        }
        $data = $data->distinct();
        $data = $data->get();

        $data10 =[];
        $sama=false;

        foreach ($data as $item) {
            $sama=false;
            $i=0;
            foreach ($data10 as $hideung){
                if ($item->noregistrasi == $data10[$i]['noregistrasi']){
                    $sama=true;
                    $data10[$i]['kddiagnosa']=$data10[$i]['kddiagnosa']. ', '.($item->kddiagnosa);
                }
                $i=$i+1;
            }
            if ($sama==false){
                $data10[]=array(
                    'noregistrasi'=>$item->noregistrasi,
                    'namapasien'=>$item->namapasien,
                    'nocm'=>$item->nocm,
                    'namakamar'=>$item->namakamar,
                    'nobed'=>$item->nobed,
                    'namaruangan'=>$item->namaruangan,
                    'kelompokpasien'=>$item->kelompokpasien,
                    'namarekanan'=>$item->namarekanan,
                    'tglmasukinap'=>$item->tglmasukinap,
                    'tglpulang'=>$item->tglpulang,
                    'dokter'=>$item->dokter,
                    'namakelas'=>$item->namakelas,
                    'kddiagnosa'=>$item->kddiagnosa,
                    'mutasi'=>$item->mutasi,
                    'carakeluar'=>$item->carakeluar,
                );
            }
        }

        $result = array(
            'data' => $data10,
            'message' => 'as@Ramdan',
        );

        return $this->respond($result);
    }
    public function getLaporanPasienPindahan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idDepRawatInap = (int) $this->settingDataFixed('idDepRawatInap', $kdProfile);
        $dataLogin = $request->all();
        $data = \DB::table('pasiendaftar_t as pd')
            ->JOIN ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->JOIN ('registrasipelayananpasien_t as rpp','rpp.noregistrasifk','=','pd.norec')
            ->JOIN ('diagnosapasien_t as dp','dp.noregistrasifk','=','apd.norec')
            ->leftJOIN ('detaildiagnosapasien_t as ddp','ddp.noregistrasifk','=','apd.norec')
            ->JOIN ('diagnosa_m as d','d.id','=','ddp.objectdiagnosafk')
            ->leftJOIN ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftJOIN ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJOIN ('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJOIN ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftJOIN ('rekanan_m as rek','rek.id','=','pd.objectrekananfk')
            ->JOIN ('kamar_m as km','km.id','=','rpp.objectkamarfk')
            ->JOIN ('kelas_m as kls','kls.id','=','rpp.objectkelasfk')
            ->leftJOIN ('tempattidur_m as tt','tt.id','=','rpp.objecttempattidurfk')
            ->jOIN ('ruangan_m as ru','ru.id','=','pd.objectruanganasalfk')
            ->JOIN ('ruangan_m as ruu','ruu.id','=','pd.objectruanganlastfk')
            ->select('pd.noregistrasi','pd.tglregistrasi', 'ps.nocm', 'ps.namapasien','kps.kelompokpasien',
                'rek.namarekanan', 'jk.jeniskelamin','kls.namakelas','km.namakamar',
                'd.kddiagnosa','d.namadiagnosa','ru.namaruangan as ruanganasal',
                'ruu.namaruangan as ruangansekarang','tt.nomorbed as nobed',
                'pd.objectstatuskeluarfk','rpp.tglmasuk as tglmasukinap',
                DB::raw('pg.namalengkap as dokter,
                   case when pd.tglpulang is not null then \'Dipulangkan\' else \'Masih Dirawat\' end as status,
                   case when ruu.id=pd.objectruanganlastfk  then \'Pindahan\' else null end as mutasi'
                ))
            ->where('pd.kdprofile',$kdProfile)
            ->Where('ru.objectdepartemenfk','=',$idDepRawatInap)
            ->whereRaw('ru.id <> ruu.id');

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ruu.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('ruu.id', '=', $request['idRuangan']);
        }
        if (isset($request['idKelas']) && $request['idKelas'] != "" && $request['idKelas'] != "undefined") {
            $data = $data->where('kls.id', '=', $request['idKelas']);
        }

        $data = $data->distinct();
        $data = $data->get();
        $data10 =[];
        $sama=false;

        foreach ($data as $item) {
            $sama=false;
            $i=0;
            foreach ($data10 as $hideung){
                if ($item->noregistrasi == $data10[$i]['noregistrasi']){
                    $sama=true;
                    $data10[$i]['kddiagnosa']=$data10[$i]['kddiagnosa']. ', '.($item->kddiagnosa);
                }
                $i=$i+1;
            }
            if ($sama==false){
                $data10[]=array(
                    'noregistrasi'=>$item->noregistrasi,
                    'namapasien'=>$item->namapasien,
                    'nocm'=>$item->nocm,
                    'namakamar'=>$item->namakamar,
                    'nobed'=>$item->nobed,
                    'ruanganasal'=>$item->ruanganasal,
                    'ruangansekarang'=>$item->ruangansekarang,
                    'kelompokpasien'=>$item->kelompokpasien,
                    'namarekanan'=>$item->namarekanan,
                    'tglmasukinap'=>$item->tglmasukinap,
                    'dokter'=>$item->dokter,
                    'namakelas'=>$item->namakelas,
                    'status'=>$item->status,
                    'kddiagnosa'=>$item->kddiagnosa,
                    'mutasi'=>$item->mutasi,
                );
            }
        }

        $result = array(
            'data' => $data10,
            'message' => 'as@Ramdan',
        );

        return $this->respond($result);
    }
}
