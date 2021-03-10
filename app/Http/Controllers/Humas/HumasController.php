<?php
/**
 * Created by PhpStorm.
 * HumasController
 * User: Efan Andrian (ea@epic)
 * Date: 07/08/2019
 * Time: 11:44 PM
 */
namespace App\Http\Controllers\Humas;
use App\Http\Controllers\Transaksi\Pegawai\Pegawai;
use App\Master\JadwalDokter;
use App\Master\JadwalPraktek;
use App\Master\JadwalPraktekBulanan;
use App\Master\KeluhanPelanggan;
use App\Traits\Valet;
use App\Transaksi\LoggingUser;
use App\Transaksi\PenangananKeluhanPelanggan;
use App\Transaksi\PenangananKeluhanPelangganD;
use App\Transaksi\PenungguPasien;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Response;
use App\Http\Controllers\ApiController;

class HumasController extends ApiController
{
    use Valet;
    public function __construct()
    {
        parent::__construct($skip_authentication=false);
    }

    public function getDataComboHumas(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$idProfile));
        $idKelas = explode(',', $this->settingDataFixed('KdListKelas',$idProfile));
        $idRajalRehab = explode(',',$this->settingDataFixed('KdDeptRajalRehab',$idProfile));
        $idDepRanap = (int) $this->settingDataFixed('idDepRawatInap', $idProfile);
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $kdlistKelas = [];
        foreach ($idKelas as $items){
            $kdlistKelas []=  (int)$items;
        }
        $kdlistRajalRehab = [];
        foreach ($idRajalRehab as $itemss){
            $kdlistRajalRehab []=  (int)$itemss;
        }
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id',$kdDepartemenRawatPelayanan)
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
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

            $dataInstalasi[] = array(
                'id' => $item->id,
                'departemen' => $item->namadepartemen,
                'ruangan' => $detail,
            );
        }
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk','ru.kdinternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.namakelas')
            ->where('kl.kdprofile', $idProfile)
            // ->whereIn('kl.id',$kdlistKelas)
            ->where('kl.statusenabled', true)
            ->orderBy('kl.namakelas')
            ->get();
        $dataRuanganRi = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk','ru.kdinternal')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->where('ru.objectdepartemenfk', $idDepRanap)
            ->orderBy('ru.namaruangan')
            ->get();
        $datadept = \DB::table('departemen_m as dp')
            ->select('dp.id', 'dp.namadepartemen')
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataklpro = \DB::table('kelompokproduk_m as dp')
            ->select('dp.id', 'dp.kelompokproduk')
            ->where('dp.kdprofile', $idProfile)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.kelompokproduk')
            ->get();
        $unitkerja = \DB::table('unitkerjapegawai_m as ukp')
            ->select('ukp.id','ukp.name')
            ->where('ukp.kdprofile', $idProfile)
            ->where('ukp.statusenabled', true)
            ->orderBy('ukp.id')
            ->get();
        $pekerjaan = \DB::table('pekerjaan_m as ukp')
            ->select('ukp.id','ukp.pekerjaan')
            ->where('ukp.kdprofile', $idProfile)
            ->where('ukp.statusenabled', true)
            ->orderBy('ukp.id')
            ->get();
        $jeniskelamin = \DB::table('jeniskelamin_m as ukp')
            ->select('ukp.id','ukp.jeniskelamin')
            ->where('ukp.kdprofile', $idProfile)
            ->where('ukp.statusenabled', true)
            ->orderBy('ukp.id')
            ->get();
        $dataKelompok = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();
        $dataJabatan = \DB::table('jabatan_m as kp')
            ->select('kp.id','kp.namajabatan')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled',true)
            ->orderBy('kp.namajabatan')
            ->get();
        $AsalProduk = \DB::table('asalproduk_m as kp')
            ->select('kp.id','kp.asalproduk')
            ->where('kp.kdprofile', $idProfile)
            ->where('kp.statusenabled',true)
            ->orderBy('kp.asalproduk')
            ->get();
        $dataRuanganRj = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan','ru.objectdepartemenfk','ru.kdinternal')
            ->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk', $kdlistRajalRehab)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataJenisKelamin = \DB::table('jeniskelamin_m as jk')
            ->select('jk.id','jk.reportdisplay','jk.jeniskelamin')
            ->where('jk.kdprofile', $idProfile)
            ->where('jk.statusenabled', true)
            ->orderBy('jk.id')
            ->get();
        $dataRuangan2 = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
          $dataHari = \DB::table('hari_m as hr')
            ->where('hr.kdprofile', $idProfile)
            ->where('hr.statusenabled', true)
            ->orderBy('hr.id')
            ->get();
        $dataRuangan3 = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
            ->whereIn('ru.objectdepartemenfk',[16,24,25])
            ->where('ru.kdprofile', $idProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataHubungan = \DB::table('hubungankeluarga_m as hm')
            ->select('hm.id','hm.hubungankeluarga')
            ->where('hm.kdprofile', $idProfile)
            ->where('hm.statusenabled', true)
            ->orderBy('hm.id')
            ->get();
        $result = array(
            'ruangan' => $dataRuangan,
            'ruanganhumas' => $dataRuangan,
            'ruanganrawatinap' => $dataRuanganRi,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'kelaskamar' => $dataKelas,
            'departemen' => $datadept,
            'kelompokproduk' => $dataklpro,
            'unitkerja' => $unitkerja,
            'pegawaiuser' => $dataPegawaiUser,
            'pekerjaan' => $pekerjaan,
            'jeniskelamin' => $jeniskelamin,
            'kelompokpasien' => $dataKelompok,
            'datadept' => $dataInstalasi,
            'dataruangan' => $dataRuangan,
            'jabatan' => $dataJabatan,
            'asalproduk' => $AsalProduk,
            'ruanganrj' => $dataRuanganRj,
            'jeniskelamin' => $dataJenisKelamin,
            'hari' => $dataHari,
            'ruangan3' => $dataRuangan3,
            'hubungan' =>$dataHubungan,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDataPegawai(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataPenulis = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap','st.nip_pns')
            ->where('st.kdprofile', $idProfile)
            ->where('st.statusenabled',true)
            ->where('st.objectjenispegawaifk','1')
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataPenulis = $dataPenulis->where('namalengkap','ilike', '%'.$req['filter']['filters'][0]['value'].'%' );
        };
        $dataPenulis = $dataPenulis->take(10);
        $dataPenulis = $dataPenulis->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
                'nip_pns' => $item->nip_pns,
            );
        }
        return $this->respond($dataPenulis2);
    }

    public function getDataPegawaiAll(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataPenulis = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap','st.nip_pns')
            ->where('st.kdprofile', $idProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataPenulis = $dataPenulis->where('namalengkap','ilike', '%'.$req['filter']['filters'][0]['value'].'%' );
        };
        $dataPenulis = $dataPenulis->take(10);
        $dataPenulis = $dataPenulis->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
                'nip_pns' => $item->nip_pns,
            );
        }
        return $this->respond($dataPenulis2);
    }

    public function getDataPegawaiPP (Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataPenulis = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap','st.nip_pns')
            ->where('st.kdprofile', $idProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataPenulis = $dataPenulis->where('namalengkap','ilike', '%'.$req['filter']['filters'][0]['value'].'%' );
        };
        $dataPenulis = $dataPenulis->take(10);
        $dataPenulis = $dataPenulis->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
                'nip_pns' => $item->nip_pns,
            );
        }
        return $this->respond($dataPenulis2);
    }

    public function getDataproduk(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $dataProduk=[];
        $dataProduk  = \DB::table('produk_m as pro')
            ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pro.objectdetailjenisprodukfk')
            ->leftJoin('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJoin('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->select('pro.id as kdproduk','pro.namaproduk','djp.id as djid','djp.detailjenisproduk','jp.id as jpid','jp.jenisproduk')
            ->where('pro.kdprofile', $idProfile)
            ->where('pro.statusenabled',true)
            ->orderBy('pro.namaproduk');

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('pro.namaproduk','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();
        return $this->respond($dataProduk);
    }

    public function getDaftarTarif(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $idKelas = $this->settingDataFixed('KdListKelas',$idProfile);
        $filter = $request->all();
        $produkid= '';
        if  ($filter['produkId'] != ''){
            $produkid= 'AND pr.id = ' . $filter['produkId'];
        }
        $ruanganid = '';
        if ($filter['ruanganId'] != ''){
            $ruanganid ='AND mprtp.objectruanganfk =' . $filter['ruanganId'];
        }
        $kelasid = '';
        if ($filter['kelasId'] != ''){
            $kelasid ='AND kls.id =' . $filter['kelasId'];
        }
        $jenispelid = '';
        if ($filter['jenispelayananId'] != ''){
            $jenispelid ='AND jnsp.id =' . $filter['jenispelayananId'];
        }
        $namaproduk = '';
        if ($filter['namaproduk'] != ''){
            $namaproduk ="AND pr.namaproduk ilike '%" . $filter['namaproduk'] . "%'";
        }

        $data =DB::select(DB::raw("
               SELECT distinct
               
                pr.id,
                pr.namaproduk,
                hrpk.harganetto1 AS hargalayanan,kls.id as idkelas,kls.namakelas ,jnsp.id as jenispelayananid,jnsp.jenispelayanan,mprtp.objectruanganfk as ruid,
                  ru.id as ruid,ru.namaruangan
                FROM
                produk_m AS pr
                INNER JOIN mapruangantoproduk_m AS mprtp ON mprtp.objectprodukfk = pr.id
               -- LEFT JOIN detailjenisproduk_m AS djp ON djp.id = pr.objectdetailjenisprodukfk
                --LEFT JOIN jenisproduk_m AS jp ON jp.id = djp.objectjenisprodukfk
                LEFT JOIN harganettoprodukbykelas_m AS hrpk ON hrpk.objectprodukfk = pr.id
                INNER JOIN kelas_m as kls on kls.id=hrpk.objectkelasfk
                INNER JOIN jenispelayanan_m as jnsp on jnsp.id=hrpk.objectjenispelayananfk
                INNER JOIN ruangan_m as ru on ru.id=mprtp.objectruanganfk
                WHERE pr.kdprofile = $idProfile and
                -- hrpk.statusenabled = true
                -- AND pr.statusenabled = true
                hrpk.statusenabled = true
                AND pr.statusenabled = true
              --  AND jp.id NOT IN (97)
                AND hrpk.objectkelasfk IN ($idKelas)
                $produkid
                 $ruanganid 
                 $kelasid $jenispelid $namaproduk

                 limit 50
                 ")
        );

        $result = array(
            'data'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDaftarTarifDetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $produkid= '';
        if  ($filter['produkId'] != ''){
            $produkid= 'AND pr.id = ' . $filter['produkId'];
        }
        $ruanganid = '';
        if ($filter['ruanganId'] != ''){
            $ruanganid ='AND mprtp.objectruanganfk =' . $filter['ruanganId'];
        }
        $kelasid = '';
        if ($filter['kelasId'] != ''){
            $kelasid ='AND kls.id =' . $filter['kelasId'];
        }
        $jenispelid = '';
        if ($filter['jenispelayananId'] != ''){
            $jenispelid ='AND jnspel.id =' . $filter['jenispelayananId'];
        }

        $data =DB::select(DB::raw("
                select  pr.id as kdeproduk,pr.namaproduk,kls.id as klsid,kh.id as idkomponen,kh.komponenharga,hrpkd.harganetto1,
                hrpkd.harganetto2,hrpkd.hargasatuan,jnspel.id as jnspelid,kls.namakelas,jnspel.jenispelayanan
                from produk_m as pr
                INNER JOIN mapruangantoproduk_m AS mprtp ON mprtp.objectprodukfk = pr.id
                left join detailjenisproduk_m as djp on djp.id = pr.objectdetailjenisprodukfk
                left join jenisproduk_m as jp on jp.id = djp.objectjenisprodukfk
                left join kelompokproduk_m as kp on kp.id = jp.objectkelompokprodukfk
                left join harganettoprodukbykelasd_m as hrpkd on hrpkd.objectprodukfk = pr.id
                inner join kelas_m as kls on kls.id = hrpkd.objectkelasfk
                inner join komponenharga_m as kh on kh.id = hrpkd.objectkomponenhargafk
                INNER JOIN jenispelayanan_m as jnspel on jnspel.id=hrpkd.objectjenispelayananfk
                where pr.kdprofile = $idProfile and
                pr.statusenabled=1 
                and hrpkd.statusenabled = 1
                -- pr.statusenabled = true
                -- and hrpkd.statusenabled = true
                $produkid $ruanganid $kelasid $jenispelid
                GROUP BY pr.id,pr.namaproduk,kls.id,kh.id,kh.komponenharga,hrpkd.harganetto1,
				         hrpkd.harganetto2,hrpkd.hargasatuan,jnspel.id,kls.namakelas,
				         jnspel.jenispelayanan")
        );
        $result = array(
            'data'=>$data,
//            'detail'=> $details,
            'message' => 'cepot',
        );
        return $this->respond($result);
    }

    public function getJadwalDokter (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
          // ** POSTGRESQL **//
//       $data = \DB::table('jadwaldokter_m as jd')
//           ->join('jadwalpraktek_m as jp','jp.id','=','jd.objectjadwalpraktekfk')
//           ->join('pegawai_m as pg','pg.id','=','jd.objectpegawaifk')
//           ->join('ruangan_m as ru','ru.id','=','jd.objectruanganfk')
//           ->select('jd.tanggaljadwal','jp.jampraktek','pg.id as pegawaiid','pg.namalengkap','ru.id as ruanganid','ru.namaruangan',
//               DB::raw('to_date(to_char(jd.tanggaljadwal,\'yyyy/mm/dd\'),\'yyyy/mm/dd\')||\' \'||jp.waktumulai as start,
//                       to_date(to_char(jd.tanggaljadwal,\'yyyy/mm/dd\'),\'yyyy/mm/dd\')||\' \'||jp.waktuselesai as end,
//                       EXTRACT(epoch from to_timestamp(to_date(to_char(jd.tanggaljadwal,\'yyyy/mm/dd\'),\'yyyy/mm/dd\')||\' \'||jp.waktumulai,\'yyyy/mm/dd HH24\')) as startepoch,
//                       EXTRACT(epoch from to_timestamp(to_date(to_char(jd.tanggaljadwal,\'yyyy/mm/dd\'),\'yyyy/mm/dd\')||\' \'||jp.waktuselesai,\'yyyy/mm/dd HH24\')) as endpoch')
//           )
//            ->where('jd.kdprofile', $idProfile);

        //** SQLSERVER **//
        // $data = \DB::table('jadwaldokter_m as jd')
        //     ->join('jadwalpraktek_m as jp','jp.id','=','jd.objectjadwalpraktekfk')
        //     ->join('pegawai_m as pg','pg.id','=','jd.objectpegawaifk')
        //     ->join('ruangan_m as ru','ru.id','=','jd.objectruanganfk')
        //     ->select('jd.tanggaljadwal','jp.jampraktek','pg.id as pegawaiid','pg.namalengkap','ru.id as ruanganid','ru.namaruangan',
        //         DB::raw(" convert(char,  jd.tanggaljadwal, 111) + ' ' +  convert(char, jp.waktumulai, 108)  AS start,
        //         convert(char,    jd.tanggaljadwal, 111) + ' ' +  convert(char, jp.waktuselesai, 108) AS ends,
        //         convert(char,    jd.tanggaljadwal, 111) + ' ' + convert(char, jp.waktumulai, 108) AS startepoch,
        //          convert(char,   jd.tanggaljadwal, 111) + ' ' +  convert(char, jp.waktuselesai, 108) AS endpoch")
        //     )
        //     ->where('jd.kdprofile', $idProfile);

        $data = \DB::table('jadwaldokter_m as jd')
            ->join('ruangan_m AS ru','ru.id','=','jd.objectruanganfk')
            ->join('pegawai_m as pg','pg.id','=','jd.objectpegawaifk')
            // ->leftJoin('hari_m AS hr','hr.id','=','jd.objecthariawal')
            // ->leftJoin('hari_m AS hr1','hr1.id','=','jd.objecthariakhir')
            ->select(DB::raw("jd.id as idjadwalpegawai,jd.objectruanganfk,ru.namaruangan,
                              jd.objectpegawaifk,pg.namalengkap,pg.nosip,pg.nostr,pg.noidentitas as nik,
                              jd.jammulai,jd.jamakhir,jd.keterangan, jd.hari"))
            ->where('jd.kdprofile', $idProfile)
            ->where('jd.statusenabled', true);

        if (isset($request['dokterId']) && $request['dokterId'] != "" && $request['dokterId'] != "undefined") {
            $data = $data->where('pg.id', '=', $request['dokterId']);
        }
        if (isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['ruanganId']);
        }
         if (isset($request['nik']) && $request['nik'] != "" && $request['nik'] != "undefined") {
            $data = $data->where('pg.noidentitas', '=', $request['nik']);
        }
    if (isset($request['nostr']) && $request['nostr'] != "" && $request['nostr'] != "undefined") {
            $data = $data->where('pg.nostr', '=', $request['nostr']);
        }

        $data = $data->orderBy('pg.namalengkap', 'asc');
        $data = $data->get();
        $result = array(
            'data'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getKetersediaanTempatTidur (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data = \DB::table('tempattidur_m as tdr')
            ->leftJoin('kamar_m as kr','kr.id','=','tdr.objectkamarfk')
            ->leftJoin('statusbed_m as sb','sb.id','=','tdr.objectstatusbedfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','kr.objectruanganfk')
            ->leftJoin('kelas_m as kl','kl.id','=','kr.objectkelasfk')
            ->leftJoin('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
            ->select('kr.tglupdate','kr.namakamar','ru.namaruangan','kl.namakelas','sb.statusbed')
            ->where('tdr.kdprofile', $idProfile);

        if (isset($request['kelasId']) && $request['kelasId'] != "" && $request['kelasId'] != "undefined") {
            $data = $data->where('kls.id', '=', $request['kelasId']);
        }
        if (isset($request['ruanganId']) && $request['ruanganId'] != "" && $request['ruanganId'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['ruanganId']);
        }
        $data = $data->orderBy('kr.namakamar', 'asc');
        $data = $data->get();
        $result = array(
            'data'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getKetersediaanTempatTidurView (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdRanap = $this->settingDataFixed('KdDeptRanapICU', $idProfile);
        $namaruangan= $request['namaruangan'];
        $idkelas= $request['idkelas'];
        $dataLogin = $request->all();
        if($namaruangan == "" && $idkelas == ""){
            $data = DB::select(DB::raw("select top 1 COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where tt.kdprofile = $idProfile and ru.objectdepartemenfk in ($kdRanap) and ru.statusenabled=1
				 and km.statusenabled=1 and tt.statusenabled=1)as x "),
                array(
//                    'namaruangan' => $namaruangan,
//                    'idkelas' => $idkelas,
                )
            );
        } elseif ($namaruangan != "" && $idkelas == ""){
            $data = DB::select(DB::raw("select COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where tt.kdprofile = $idProfile and ru.objectdepartemenfk in ($kdRanap) and ru.namaruangan=:namaruangan)as x"),
                array(
                    'namaruangan' => $namaruangan,
//                    'idkelas' => $idkelas,
                )
            );
        } elseif ($namaruangan == "" && $idkelas != ""){
            $data = DB::select(DB::raw("select COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where tt.kdprofile = $idProfile and ru.objectdepartemenfk in ($kdRanap) and kl.id=:idkelas)as x"),
                array(
//                    'namaruangan' => $namaruangan,
                    'idkelas' => $idkelas,
                )
            );
        } else {
            $data = DB::select(DB::raw("select COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
			    sum(x.kamarprosesadmin) as kamarprosesadmin, sum(x.kamartakterpakai) as kamartakterpakai from 
                (select 
                 ru.namaruangan, 
                 km.namakamar,
                 kl.id as kelasid,
                 kl.namakelas, 
                 tt.reportdisplay, 
                 tt.nomorbed, 
                 sb.id as idstatusbed, 
                 sb.statusbed,
                 (case when sb.id=1 then 1 else 0 end) as kamarisi,
                 (case when sb.id=2 then 1 else 0 end) as kamarkosong,
                 (case when sb.id=3 then 1 else 0 end) as kamarprosesadmin,
                 (case when sb.id=4 then 1 else 0 end) as kamartakterpakai
                 from tempattidur_m as tt
                 left join kamar_m as km on km.id = tt.objectkamarfk
                 left join ruangan_m as ru on ru.id = km.objectruanganfk
                 left join statusbed_m as sb on sb.id = tt.objectstatusbedfk
                 left join kelas_m as kl on kl.id = km.objectkelasfk
                 where tt.kdprofile = $idProfile and ru.objectdepartemenfk in ($kdRanap) and ru.namaruangan=:namaruangan and kl.id=:idkelas)as x"),
                array(
                    'namaruangan' => $namaruangan,
                    'idkelas' => $idkelas,
                )
            );
        }
        return $this->respond($data);
    }

    public function GetDataPegawaiAtasan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $datapegawai = \DB::table('pegawai_m as pg')
            ->leftJoin('mappingpegawaitoatasan_m as mpa','mpa.objectpegawaifk','=','pg.id')
            ->leftJoin('pegawai_m as pg1','pg1.id','=','mpa.objectatasanlangsungfk')
            ->leftJoin('jabatan_m as jb','jb.id','=','mpa.objectatasanpejabatpenilaifk')
            ->leftJoin('satuankerja_m as sk','sk.id','=', 'pg.objectsatuankerjafk')
            ->leftJoin('unitkerjapegawai_m as ukp','ukp.id','=','pg.objectunitkerjapegawaifk')
            ->select('pg.id','pg.namalengkap as namaLengkap','pg1.namalengkap as namaatasan','jb.namajabatan',
                     'sk.id as idsatuankerja','sk.satuankerja','ukp.id as ukpid','ukp.name as unitkerja')
            ->where('pg.kdprofile', $idProfile)
            ->where('pg.id',$request['pegawaiuser'])
            ->orWhere('pg1.id',$request['pegawaiuser'])
            ->where('pg.statusenabled',true)
            ->where('pg1.statusenabled',true)
            ->get();

        $result = array(
            'datalogin'=>$dataLogin,
            'pegawaiuser'=>$dataPegawaiUser,
            'datapegawai'=>$datapegawai,
            'message' => 'cepot',
        );
        return $this->respond($result);
    }

    public function SaveKeluhanPelanggan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();
        try{
        if ($request['data']['id']==''){
            $dataKeluhan = new KeluhanPelanggan();
            $idDK = KeluhanPelanggan::max('id');
            $norecDK = $dataKeluhan->generateNewId();
            $dataKeluhan->kdprofile = $idProfile;
            $dataKeluhan->statusenabled = true;
            $dataKeluhan->id = $idDK + 1;
            $dataKeluhan->norec = $norecDK;

        }else{
            $dataKeluhan =  KeluhanPelanggan::where('id',$request['data']['id'])->where('kdprofile', $idProfile)->first();
        }
          $dataKeluhan->alamat =  $request['data']['alamat'];
          $dataKeluhan->email =  $request['data']['email'];
          $dataKeluhan->keluhan = $request['data']['keluhan'];
          $dataKeluhan->namapasien = $request['data']['namapasien'];
          $dataKeluhan->norm =  $request['data']['norm'];
          $dataKeluhan->notlp =  $request['data']['notlp'];
          $dataKeluhan->objectruanganfk = $request['data']['objectruanganfk'];
          $dataKeluhan->saran = $request['data']['saran'];
          $dataKeluhan->objectpekerjaanfk = $request['data']['objectpekerjaanfk'];
          $dataKeluhan->umur = $request['data']['umur'];
          $dataKeluhan->tglkeluhan = $request['data']['tglkeluhan'];
//          $dataKeluhan->tglorder = $request['data']['tglorder'];
          $dataKeluhan->objectpegawaifk = (int)$dataPegawai->objectpegawaifk;//$request['data']['objectpegawaifk'];
          $dataKeluhan->notlpkntr = $request['data']['notlpkntr'];
          $dataKeluhan->save();
          $norecKeluhan = $dataKeluhan->norec;

        //## Logging User
        $newId = LoggingUser::max('id');
        $newId = $newId +1;
        $logUser = new LoggingUser();
        $logUser->id = $newId;
        $logUser->norec = $logUser->generateNewId();
        $logUser->kdprofile= $idProfile;
        $logUser->statusenabled=true;
        $logUser->jenislog = 'Keluhan Pelanggan atas nama ' . $request['data']['namapasien'];
        $logUser->noreff = $norecKeluhan;
        $logUser->referensi='norec Keluhan Pelanggan';
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
                "norec" => $norecKeluhan,
                "as" => 'Cepot',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "norec" => $norecKeluhan,
                "as" => 'Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarKeluhan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('keluhanpelanggan_m as kp')
            ->leftJoin('pasien_m as ps','ps.nocm','=','kp.norm')
            ->leftJoin('alamat_m as al','al.nocmfk','=','ps.id')
            ->leftJoin('pekerjaan_m as pkr','pkr.id','=','kp.objectpekerjaanfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJoin('jeniskelamin_m as jk1','jk1.id','=','kp.objectjeniskelaminfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','kp.objectruanganfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','kp.objectpegawaifk')
            ->select(DB::raw('kp.norec,kp.id as kpid,kp.tglkeluhan,kp.tglorder,
                         CASE WHEN ps.nocm = kp.norm then ps.nocm else kp.norm end as nocm,
			             CASE WHEN ps.nocm = kp.norm then ps.namapasien else kp.namapasien end as namapasien,
                         kp.umur,
                         CASE WHEN ps.nocm = kp.norm then al.alamatlengkap else kp.alamat end as alamat,
                         CASE WHEN ps.nocm = kp.norm then ps.notelepon else kp.notlp end as notlp,
                         CASE WHEN ps.nocm = kp.norm then jk.id else jk1.id end as jkid,
                         CASE WHEN ps.nocm = kp.norm then jk.jeniskelamin else jk1.jeniskelamin end as jeniskelamin,
                         ru.id as ruid,ru.namaruangan,kp.keluhan,kp.saran,pkr.id as pekerjaanid,pkr.pekerjaan,kp.notlpkntr,
                         pg.id as pegawaiid,pg.namalengkap','kp.email')
            )
            ->where('kp.kdprofile', $idProfile);
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('kp.tglkeluhan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('kp.tglkeluhan', '<=', $tgl);
        }
        if (isset($request['noRm']) && $request['noRm'] != "" && $request['noRm'] != "undefined") {
            $data = $data->where('kp.norm', '=', $request['noRm']);
        }
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $data = $data->where('kp.namapasien', 'ilike', '%'. $request['namaPasien'].'%');
        }
        if (isset($request['IdKeluhan']) && $request['IdKeluhan'] != "" && $request['IdKeluhan'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['IdKeluhan']);
        }

        $data = $data->where('kp.statusenabled',true);
        $data = $data->orderBy('kp.tglkeluhan', 'asc');
        $data = $data->get();

        $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function savePenangananKeluhan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $detLogin = $request->all();
        DB::beginTransaction();
        try{
            if ($request['data']['norec_pp'] == ''){
                $dataPP = new PenangananKeluhanPelanggan();
                $dataPP->kdprofile = $idProfile;
                $dataPP->statusenabled = true;
                $dataPP->norec = $dataPP->generateNewId();
            }else{
                $dataPP =  PenangananKeluhanPelanggan::where('norec',$request['data']['norec_pp'])->where('kdprofile', $idProfile)->first();
                $delPPD = PenangananKeluhanPelangganD::where('penanganankeluhanfk', $request['data']['norec_pp'])->where('kdprofile', $idProfile)
                         ->delete();
            }
            $dataPP->email = $request['data']['email'];
            $dataPP->namapetugas = $request['data']['namapetugas'];
            $dataPP->objectpegawaifk = $request['data']['objectpegawaifk'];
            $dataPP->reply = $request['data']['reply']; //count tgl pasien perruanga
            $dataPP->keluhanpelangganfk = $request['data']['keluhanpelangganfk'];
            $dataPP->tglpenanganan=$request['data']['tglpenanganan'];
            $dataPP->save();
            $idPP=$dataPP->norec;

            $dataPPD = new PenangananKeluhanPelangganD();
            $dataPPD->norec = $dataPPD->generateNewId();;
            $dataPPD->kdprofile = $idProfile;
            $dataPPD->statusenabled = true;
            $dataPPD->penanganankeluhanfk = $dataPP->norec;
            $dataPPD->hasilklarifikasi =$request['data']['reply'];
            $dataPPD->kesimpulankronologis =$request['data']['kesimpulanKronologis'];
            $dataPPD->tindaklanjut=$request['data']['tindakLanjut'];
            $dataPPD->respon=$request['data']['respon'];
            $dataPPD->solusikeluhan=$request['data']['solusikeluhan'];
            $dataPPD->kategorikomplain=$request['data']['kategorikomplain'];
            $dataPPD->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan Penangangan Keluhan Pelanggan";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'noPenangangan' => $idPP,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Gagal Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'noPenangangan' => $idPP,
                'message'  => $transStatus,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveBatalKeluhan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile', $idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = KeluhanPelanggan::where('id', $request['data']['kpid'])
                ->where('lu.kdprofile', $idProfile)
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
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Keluhan Pelanggan';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Keluhan Pelanggan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

        $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Keluhan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Batal Keluhan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Mr.Cepot',
            );
        } else {
            $transMessage = "Batal Keluhan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'Mr.Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getJadwalDokterss(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('jadwalpraktikdokter_m as jpd')
            ->leftjoin('ruangan_m as ruangan','ruangan.id','=','jpd.objectruanganfk')
            ->leftjoin('jadwalpraktek_m as jp','jp.id','=','jpd.objectjadwalpraktekfk')
            ->leftjoin('hari_m as hari','hari.id','=','jpd.objectharifk')
            ->leftjoin('pegawai_m as pgw','pgw.id','=','jpd.objectpegawaifk')

            ->select('ruangan.namaruangan as namaruangan','ruangan.id as idruangan',
                'jp.jampraktek as jampraktek', 'jp.id as idjampraktek',
                'hari.namahari as namahari', 'hari.id as idhari',
                'pgw.namalengkap as namalengkap','pgw.id as idpeg',
                'jpd.id as id','jpd.quota as quota')
            ->where('jpd.kdprofile', $idProfile)
            ->orderBy('hari.namahari','jp.jampraktek');

        if(isset($request['ruangan']) && $request['ruangan']!="" && $request['ruangan']!="undefined"){
            $data = $data->where('ruangan.id', $request['ruangan']);
        }
        if(isset($request['hari']) && $request['hari']!="" && $request['hari']!="undefined"){
            $data = $data->where('hari.id', $request['hari']);
        }
        $data = $data->where('jpd.statusenabled',true);
        // $data = $data->take(100);
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDaftarPenanganKeluhan (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('keluhanpelanggan_m as kp')
            ->leftJoin('penanganankeluhanpelanggan_t as pkp','pkp.keluhanpelangganfk','=','kp.id')
            ->leftJoin('penanganankeluhanpelanggand_t as pkpd','pkpd.penanganankeluhanfk','=','pkp.norec')
            ->leftJoin('pasien_m as ps','ps.nocm','=','kp.norm')
            ->leftJoin('alamat_m as al','al.nocmfk','=','ps.id')
            ->leftJoin('pekerjaan_m as pkr','pkr.id','=','kp.objectpekerjaanfk')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftJoin('jeniskelamin_m as jk1','jk1.id','=','kp.objectjeniskelaminfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','kp.objectruanganfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','kp.objectpegawaifk')
            ->select(DB::raw('kp.norec,kp.id as kpid,kp.tglkeluhan,kp.tglorder,
                         CASE WHEN ps.nocm = kp.norm then ps.nocm else kp.norm end as nocm,
			             CASE WHEN ps.nocm = kp.norm then ps.namapasien else kp.namapasien end as namapasien,
                         kp.umur,
                         CASE WHEN ps.nocm = kp.norm then al.alamatlengkap else kp.alamat end as alamat,
                         CASE WHEN ps.nocm = kp.norm then ps.notelepon else kp.notlp end as notlp,
                         CASE WHEN ps.nocm = kp.norm then jk.id else jk1.id end as jkid,
                         CASE WHEN ps.nocm = kp.norm then jk.jeniskelamin else jk1.jeniskelamin end as jeniskelamin,
                         ru.id as ruid,ru.namaruangan,kp.keluhan,kp.saran,pkr.id as pekerjaanid,pkr.pekerjaan,kp.notlpkntr,
                         pg.id as pegawaiid,pg.namalengkap,kp.email,pkp.reply,pkpd.hasilklarifikasi,pkpd.kesimpulankronologis,
                         pkpd.solusikeluhan,pkpd.tindaklanjut,pkpd.respon,pkp.norec as norec_pp,pkp.tglpenanganan,pkpd.kategorikomplain')
            )
            ->where('kp.kdprofile', $idProfile);
        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('kp.tglkeluhan', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];
            $data = $data->where('kp.tglkeluhan', '<=', $tgl);
        }
        if (isset($request['noRm']) && $request['noRm'] != "" && $request['noRm'] != "undefined") {
            $data = $data->where('kp.norm', '=', $request['noRm']);
        }
        if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
            $data = $data->where('kp.namapasien', 'ilike', '%'. $request['namaPasien'].'%');
        }
        if (isset($request['IdKeluhan']) && $request['IdKeluhan'] != "" && $request['IdKeluhan'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['IdKeluhan']);
        }

        $data = $data->where('pkp.statusenabled',true);
        $data = $data->orderBy('kp.tglkeluhan', 'asc');
        $data = $data->get();

        $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function saveBatalPenangananKeluhan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $Kel = PenangananKeluhanPelanggan::where('norec', $request['data']['norec'])
                ->where('kdprofile',$idProfile)
                ->update([
                    'statusenabled' => 'f',
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Penanganan Keluhan Pelanggan';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Penanganan Keluhan Pelanggan';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Batal Penanganan Keluhan Gagal";
        }

        if ($transStatus == 'true') {
            $transMessage = "Batal Penanganan Keluhan Berhasil";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'Mr.Cepot',
            );
        } else {
            $transMessage = "Batal Penanganan Keluhan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transStatus,
                'as' => 'Mr.Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataDokterCoy(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdJeniPegawaiDokter = (int) $this->settingDataFixed('KdJenisPegawaiDokter',$idProfile);
        $req=$request->all();
        $dataPenulis = \DB::table('pegawai_m as st')
            ->select('st.id','st.namalengkap','st.nip_pns')
            ->where('st.kdprofile', $idProfile)
            ->where('st.statusenabled',true)
            ->where('st.objectjenispegawaifk',$kdJeniPegawaiDokter)
            ->orderBy('st.namalengkap');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataPenulis = $dataPenulis->where('namalengkap','ilike', '%'.$req['filter']['filters'][0]['value'].'%' );
        };
        $dataPenulis = $dataPenulis->take(20);
        $dataPenulis = $dataPenulis->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }
        return $this->respond($dataPenulis2);
    }

    public function getDaftarMapKelompokKerjaToPegawai (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pegawai_m as pgw')
            ->Join('kelompokkerja_m as kk','kk.id','=','pgw.objectkelompokkerjafk')
            ->Join('kelompokkerjahead_m as kkh','kkh.id','=','kk.objectkelompokkerjahead')
            ->select(DB::raw('pgw.id as pgwid,pgw.namalengkap,kk.id as kkid,kk.kelompokkerja,kkh.id as kkhid,kkh.kelompokkerjahead'))
            ->where('pgw.kdprofile', $idProfile);

        if (isset($request['kkHead']) && $request['kkHead'] != "" && $request['kkHead'] != "undefined") {
            $data = $data->where('kkh.id', '=', $request['kkHead']);
        }
        if (isset($request['Kkerja']) && $request['Kkerja'] != "" && $request['Kkerja'] != "undefined") {
            $data = $data->where('kk.id', '=', $request['Kkerja']);
        }
        if (isset($request['PegawaiId']) && $request['PegawaiId'] != "" && $request['PegawaiId'] != "undefined") {
            $data = $data->where('pgw.id', '=', $request['PegawaiId']);
        }

        $data = $data->where('pgw.statusenabled',true);
        $data = $data->orderBy('kk.id', 'asc');
        $data = $data->get();

        $result = array(
            'datas'=>$data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function UpdaterObjectKelompokKerja(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            $dataPegawai = \App\Master\Pegawai::where('id', $request['idPegawai'])
                ->where('kdprofile', $idProfile)
                ->update([
                    'objectkelompokkerjafk' => $request['objectkelompokkerja'],
                ]);

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal";
        }


        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'cepot',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataViewBed(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $kdRanapICU = explode(',',$this->settingDataFixed('KdDeptRanapICU',$idProfile));
        $kdListRanapICU = [];
        foreach ($kdRanapICU as $items){
            $kdListRanapICU []=  (int)$items;
        }
        $data= \DB::table('tempattidur_m as tt')
            ->leftjoin('kamar_m as km', 'km.id', '=', 'tt.objectkamarfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'km.objectruanganfk')
            ->leftjoin('statusbed_m as sb', 'sb.id', '=', 'tt.objectstatusbedfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'km.objectkelasfk')
            ->select('ru.id as idruangan','ru.namaruangan','km.id as idkamar','km.namakamar','tt.id as idtempattidur',
                'tt.reportdisplay','tt.nomorbed','sb.id as idstatusbed','sb.statusbed','kl.id as idkelas','kl.namakelas')
            ->where('tt.kdprofile', $idProfile)
            ->whereIn('ru.objectdepartemenfk',$kdListRanapICU)
            ->where('ru.statusenabled',true)
            ->where('km.statusenabled',true)
            ->where('tt.statusenabled',true);

        if(isset($request['namaruangan']) && $request['namaruangan']!="" && $request['namaruangan']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike','%'. $request['namaruangan'] .'%');
        };
        if(isset($request['namakamar']) && $request['namakamar']!="" && $request['namakamar']!="undefined"){
            $data = $data->where('km.namakamar','ilike','%'. $request['namakamar'] .'%');
        };
        if(isset($request['idkelas']) && $request['idkelas']!="" && $request['idkelas']!="undefined"){
            $data = $data->where('kl.id', $request['idkelas']);
        };
        if(isset($request['namabed']) && $request['namabed']!="" && $request['namabed']!="undefined"){
            $data = $data->where('tt.reportdisplay','ilike','%'. $request['namabed'] .'%');
        };
        if(isset($request['idstatusbed']) && $request['idstatusbed']!="" && $request['idstatusbed']!="undefined"){
            $data = $data->where('sb.id', $request['idstatusbed']);
        };
        $data = $data->get();


        return $this->respond($data);
    }

    public function getRuanganPart(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $req=$request->all();
        $data  = \DB::table('ruangan_m as st')
            ->select('st.id','st.namaruangan')
            ->where('st.kdprofile', $idProfile)
            ->where('st.statusenabled',true)
            ->orderBy('st.namaruangan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where('st.namaruangan','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        if(isset($req['namaruangan']) && $req['namaruangan']!="" && $req['namaruangan']!="undefined"){
            $data = $data
                ->where('st.namaruangan','ilike','%'.$req['namaruangan'].'%' );
        }
        if(isset($req['idRuangan']) && $req['idRuangan']!="" && $req['idRuangan']!="undefined"){
            $data = $data
                ->where('st.id','=',$req['idRuangan'] );
        }
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getComboDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter');
        $req = $request->all();
        $data = \DB::table('pegawai_m')
            ->select('id','namalengkap')
            ->where('statusenabled', true)
            ->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
            ->where('kdprofile',$kdProfile)
            ->orderBy('namalengkap');

        // if(isset($req['namalengkap']) &&
        //     $req['namalengkap']!="" &&
        //     $req['namalengkap']!="undefined"){
        //     $data = $data->where('namalengkap','ilike','%'. $req['namalengkap'] .'%' );
        // };
        // if(isset($req['idpegawai']) &&
        //     $req['idpegawai']!="" &&
        //     $req['idpegawai']!="undefined"){
        //     $data = $data->where('id', $req['idpegawai'] );
        // };
        // if(isset($req['filter']['filters'][0]['value']) &&
        //     $req['filter']['filters'][0]['value']!="" &&
        //     $req['filter']['filters'][0]['value']!="undefined"){
        //     $data = $data
        //         ->where('namalengkap','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        // }

        $data = $data->get();
        return $this->respond($data);
    }

    public function saveJadwalBulanan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item) {
                if ($request['id'] == '') {
                    $id = JadwalPraktekBulanan::max('id');
                    $dataJadwal = new JadwalPraktekBulanan();
                    $dataJadwal->id = $id + 1;
                    $dataJadwal->norec = $dataJadwal->generateNewId();
                    $dataJadwal->kdprofile = $idProfile;
                    $dataJadwal->statusenabled = true;

                } else {
                    $dataJadwal = JadwalPraktekBulanan::where('id', $request['id'])->where('kdprofile', $idProfile)->first();
                }
                $dataJadwal->reportdisplay = '';
                $dataJadwal->objectpegawaifk = $item['idpegawai'];
                $dataJadwal->objectruanganfk = $request['ruanganfk'];
                $dataJadwal->jammulai = $request['jammulai'];
                $dataJadwal->jamselesai = $request['jamselesai'];
                $dataJadwal->keterangan = $request['keterangan'];
                $dataJadwal->tglmulai = $item['tglmulai'];
                $dataJadwal->tglselesai = $item['tglselesai'];
                $dataJadwal->objectstatushadirfk = 1;//hadir $request['objectstatushadirfk'];
                $dataJadwal->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Jadwal Dokter";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "res" => $dataJadwal,
                "as" => 'ramdanegie@epic',
            );
        } else {
            $transMessage = "Simpan Jadwal Dokter Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ramdanegie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getJadwalBulananDokter(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('jadwalpraktekbulanan_m as jd')
            ->join('pegawai_m as pg','pg.id','=','jd.objectpegawaifk')
            ->join('ruangan_m as ru','ru.id','=','jd.objectruanganfk')
            ->select('jd.*','pg.namalengkap','ru.namaruangan')
            ->where('jd.kdprofile', $idProfile)
            ->where('jd.statusenabled', true)
            ->orderByRaw('pg.namalengkap,jd.tglmulai desc');


        if(isset($request['bulan']) &&
            $request['bulan']!="" &&
            $request['bulan']!="undefined"){
            $tgl = $request['bulan']  ;
            $data = $data->whereRaw("
            -- STUFF(CONVERT(varchar(10), jd.tglmulai,104),1,3,'')  
              OVERLAY(to_char(jd.tglmulai,'DD.MM.YYYY') placing '' from 1 for 3)= '$tgl' " );
        };
        if(isset($request['namalengkap']) &&
            $request['namalengkap']!="" &&
            $request['namalengkap']!="undefined"){
            $data = $data->where('pg.namalengkap','ilike','%'. $request['namalengkap'] .'%' );
        };
        if(isset($request['idRuangan']) &&
            $request['idRuangan']!="" &&
            $request['idRuangan']!="undefined"){
            $data = $data->where('ru.id','=', $request['idRuangan'] );
        };
        if(isset($request['idPegawai']) &&
            $request['idPegawai']!="" &&
            $request['idPegawai']!="undefined"){
            $idPegawai =$request['idPegawai'];
            $data = $data->whereRaw("pg.id in ( $idPegawai )");
        };

        $data = $data->get();
        return $this->respond($data);
    }

    public function getDaftarRegistrasiPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
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
            ->whereNull('br.norec')
            ->where('pd.kdprofile', $idProfile);

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

    public function getDataAntrianPasien(Request $request){
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
                and pd.tglpulang is null
                $ruangId $noreg  $namaRuangan
              ) as x where x.rownum=1")
        );
        return $this->respond($data);
    }

    public function getDataInformasiPasienPulang(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request->all();
        $data= \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as p', 'p.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->select('pd.norec','pd.tglregistrasi', 'p.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'p.namapasien', 'kp.kelompokpasien',
                     'pd.tglpulang', 'pd.statuspasien','pd.nostruklastfk', 'pd.nosbmlastfk','dept.id as deptid','pd.tglclosing')
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.statusenabled',true)
            ->whereNotNull('pd.tglpulang');

        if(isset($filter['tglAwal']) && $filter['tglAwal']!="" && $filter['tglAwal']!="undefined"){
            $data = $data->where('pd.tglpulang','>=', $filter['tglAwal']);
        }
        if(isset($filter['tglAkhir']) && $filter['tglAkhir']!="" && $filter['tglAkhir']!="undefined"){
            $data = $data->where('pd.tglpulang','<=', $filter['tglAkhir']);
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
        $data =$data->get();

        $result = array();
        foreach ($data as $pasienD){
            $status="-";
            if ($pasienD->nostruklastfk == null && $pasienD->nosbmlastfk == null) {
                $status = "Belum Verifikasi";
            } elseif ($pasienD->nostruklastfk != null && $pasienD->nosbmlastfk == null) {
                $status = "Verifikasi";
            } elseif ($pasienD->nostruklastfk != null && $pasienD->nosbmlastfk != null) {
                $status = '-';//"Lunas";
            }
            $result[] = array(
                'tanggalMasuk'  =>$pasienD->tglregistrasi,
                'noCm'  =>$pasienD->nocm,
                'noRegistrasi'  =>$pasienD->noregistrasi,
                'namaRuangan'  =>$pasienD->namaruangan,
                'namaPasien'  =>$pasienD->namapasien,
                'jenisAsuransi'  =>$pasienD->kelompokpasien,
                'tanggalPulang' => $pasienD->tglpulang,
                'status'    =>  $status,
                'deptid' => $pasienD->deptid,
                'tglclosing' => $pasienD->tglclosing,
            );
        }
        return $this->respond($result);
    }

    public function getDataInformasiPasien( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('ps.nocm','ps.namapasien','ps.tgldaftar', 'ps.tgllahir',
                'jk.jeniskelamin','ps.noidentitas','alm.alamatlengkap',
                'ps.id as nocmfk','ps.namaayah','ps.notelepon','ps.nohp','ps.tglmeninggal')
            ->where('ps.kdprofile', $idProfile);

        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
            $data = $data->where('ps.tgllahir','>=', $request['tglLahir'].' 00:00');
        };
        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
            $data = $data->where('ps.tgllahir','<=', $request['tglLahir'].' 23:59');
        };
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['namaPasien'] .'%');
        };
        if(isset($request['alamat']) && $request['alamat']!="" && $request['alamat']!="undefined") {
            $data = $data->where('alm.alamatlengkap', 'ilike', '%'. $request['alamat'] .'%');
        };
        if(isset($request['namaAyah']) && $request['namaAyah']!="" && $request['namaAyah']!="undefined"){
            $data = $data->where('ps.namaayah','=', $request['namaAyah']);
        };
        if (isset($request['jmlRows']) && $request['jmlRows']!="" && $request['jmlRows']!="undefined") {
            $data = $data->take($request['jmlRows']);
        };
        $data = $data->where('ps.statusenabled',true);
        $data=$data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }

    public function getDataInformasiRiwayatRegistrasi( Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->join('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select(DB::raw("pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pd.objectruanganlastfk,ru.namaruangan,
			                  pd.objectpegawaifk,pg.namalengkap as namadokter,pd.tglpulang,ru.objectdepartemenfk,
			                  CASE when ru.objectdepartemenfk in (16,25,26) then 1 else 0 end as statusinap"))
            ->whereNull('br.pasiendaftarfk')
            ->where('ps.kdprofile',$idProfile);
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['namaPasien'] .'%');
        };
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $data = $data->where('pd.objectruanganlastfk','=', $request['idRuangan']);
        };

        $data = $data->where('ps.statusenabled',true);
        $data = $data->orderBy('pd.tglregistrasi');
        $data=$data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getInformasiDataPasienDalamPerawatan (Request $request){
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
        $data = \DB::select(DB::raw("
      
          SELECT 
                -- DATEDIFF(day, pd.tglregistrasi,GETDATE()) AS hari,
                EXTRACT(day from age(now(), pd.tglregistrasi)) as hari,
                pd.tglregistrasi,pd.noregistrasi,ps.nocm,ps.namapasien,ru.namaruangan,kp.kelompokpasien,kls.namakelas,ps.tgllahir,pd.norec,
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
                WHERE pd.kdprofile = $idProfile and                
                 pd.tglpulang is null and pd.statusenabled = true
                $paramIdRuang
                $paramKelompokPasien
                $paramNoregistrasi
                $paramNoRM
                $paramPasien
                GROUP BY pd.tglregistrasi,pd.noregistrasi,ps.nocm,kls.namakelas,ps.tgllahir,pd.norec,
                ps.namapasien,ru.namaruangan,kp.kelompokpasien
                order by pd.tglregistrasi"));
//            $data = \DB::table('v_pasiendalamperawatan as v');


        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function getDataInformasiPasienPerjanjian(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $filter = $request;
        $data = \DB::table('antrianpasienregistrasi_t as apr')
            ->leftJoin('pasien_m as pm','pm.id','=','apr.nocmfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','apr.objectruanganfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','apr.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kps','kps.id','=','apr.objectkelompokpasienfk')
            ->select('apr.norec','pm.nocm','apr.noreservasi','apr.tanggalreservasi','apr.objectruanganfk',
                'apr.objectpegawaifk','ru.namaruangan','apr.isconfirm','pg.namalengkap as dokter',
                'apr.notelepon','pm.namapasien','apr.namapasien','apr.objectkelompokpasienfk','kps.kelompokpasien',
                'apr.tglinput',
                DB::raw('(case when pm.namapasien is null then apr.namapasien else pm.namapasien end) as namapasien, 
                (case when apr.isconfirm=true then \'Confirm\' else \'Reservasi\' end) as status')
            )
            ->where('apr.kdprofile', $idProfile)
            ->where('apr.noreservasi','<>','-')
            ->where('apr.statusenabled',true)
            ->whereNotNull('apr.noreservasi');

        if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('apr.tanggalreservasi', '>=', $filter['tglAwal']. " 00:00:00");
        }
        if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $data = $data->where('apr.tanggalreservasi', '<=', $filter['tglAkhir']. " 23:59:59" );
        }
        if(isset($filter['ruanganId']) && $filter['ruanganId'] != "" && $filter['ruanganId'] != "undefined") {
            $data = $data->where('ru.id','=',$filter['ruanganId']);
        }
        if(isset($filter['kdReservasi']) && $filter['kdReservasi'] != "" && $filter['kdReservasi'] != "undefined") {
            $data = $data->where('apr.noreservasi','=',$filter['kdReservasi']);
        }
        if(isset($filter['statusRev']) && $filter['statusRev'] == "Confirm" && $filter['statusRev'] == "Confirm" && $filter['statusRev'] == "Confirm") {
            $data = $data->where('apr.isconfirm','=',true);
        }
        if(isset($filter['statusRev']) && $filter['statusRev'] == "Reservasi" && $filter['statusRev'] == "Reservasi" && $filter['statusRev'] == "Reservasi") {
            $data = $data->whereNull('apr.isconfirm');
        }
        if(isset($filter['namapasienpm']) && $filter['namapasienpm'] != "" && $filter['namapasienpm'] != "undefined") {
            $data = $data->where('pm.namapasien','ilike','%'. $filter['namapasienpm'] .'%');
        }

        $data = $data->orderBy('apr.tanggalreservasi','asc');
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function getDetailPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasien_m as ps')
            ->leftJOIN('alamat_m as al','al.nocmfk','=','ps.id')
            ->leftJOIN('pekerjaan_m as pkr','pkr.id','=','ps.objectpekerjaanfk')
            ->leftJOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('ps.nocm','ps.namapasien','ps.notelepon','ps.tgllahir','al.alamatlengkap','pkr.id as pekerjaanid','pkr.pekerjaan','jk.id as jkid',
                'jk.jeniskelamin','al.alamatemail')
            ->where('ps.statusenabled',true)
            ->where('ps.kdprofile', $idProfile);
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm', $request['nocm'] );
        };
        $data = $data->first();
        return $this->respond($data);
    }

    public function getInformasiDataPasienDalamPerawatanKeswamas (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        ini_set('max_execution_time', 1000); //6 minutes
        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $paramIdRuang = '';
        $paramKelompokPasien = '';
        $paramNoregistrasi='';
        $paramNoRM ='';
        $paramPasien ='';
        $paramAlamat ='';
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
        if (isset($request['alamat']) && $request['alamat'] != "" && $request['alamat'] != "undefined") {
            $paramPasien = " and alm.alamatlengkap ilike '%".$request['alamat']."%'";
        }
        $data = \DB::select(DB::raw("
      
          SELECT 
                EXTRACT(day from age(now(), pd.tglregistrasi)) as hari,
                -- DATEDIFF(day, pd.tglregistrasi,GETDATE()) AS hari,
                pd.tglregistrasi,pd.noregistrasi,ps.nocm,ps.namapasien,ru.namaruangan,kp.kelompokpasien,kls.namakelas,ps.tgllahir, jk.jeniskelamin, alm.alamatlengkap, ps.penanggungjawab,pd.norec
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                inner join ruangan_m as ru on ru.id= pd.objectruanganlastfk
                inner join kelas_m as kls on kls.id= pd.objectkelasfk
                inner join kelompokpasien_m as kp on kp.id= pd.objectkelompokpasienlastfk
                inner join pasien_m as ps on ps.id= pd.nocmfk
                INNER JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
                LEFT JOIN pelayananpasien_t AS pp ON pp.noregistrasifk = apd.norec
                LEFT JOIN alamat_m AS alm ON alm.nocmfk = ps.id
                WHERE pd.kdprofile = $idProfile and
                 pd.tglpulang is null
                $paramIdRuang
                $paramKelompokPasien
                $paramNoregistrasi
                $paramNoRM
                $paramPasien
                AND pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                GROUP BY pd.tglregistrasi,pd.noregistrasi,ps.nocm,kls.namakelas,ps.tgllahir,
                ps.namapasien,ru.namaruangan,kp.kelompokpasien, jk.jeniskelamin, alm.alamatlengkap, ps.penanggungjawab,pd.norec
                order by pd.tglregistrasi"));
//            $data = \DB::table('v_pasiendalamperawatan as v');


        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
  public function getSurveyKepuasan(Request $request){
      $kdProfile = $this->getDataKdProfile($request);
      $idProfile = (int) $kdProfile;
             $filter = $request;
             $data = \DB::table('surveykepuasanpelanggan_t as apr')
                 ->leftJoin('parameterkepuasan_m as pm','pm.id','=','apr.objectparameterkepuasanfk')
                 ->select('apr.*','pm.name as status')
                 ->where('apr.kdprofile', $idProfile)
                 ->where('apr.statusenabled',true);


             if(isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
                 $data = $data->where('apr.tglsurvey', '>=', $filter['tglAwal']);
             }
             if(isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
                 $data = $data->where('apr.tglsurvey', '<=', $filter['tglAkhir'] );
             }
            if(isset($filter['statusId']) && $filter['statusId'] != "" && $filter['statusId'] != "undefined") {
                 $data = $data->where('pm.id', '=', $filter['statusId'] );
             }
             if(isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
                 $data = $data->where('apr.namalengkap','ilike','%'. $filter['nama'] .'%');
             }

             $data = $data->orderBy('apr.tglsurvey','desc');
             $data = $data->get();

             $result = array(
                 'data' => $data,
                 'message' => 'er@epic',
             );
             return $this->respond($result);
         }
      public function getComboSurvey(Request $request){
          $kdProfile = $this->getDataKdProfile($request);
          $idProfile = (int) $kdProfile;
              $data = \DB::table('parameterkepuasan_m')
                ->where('kdprofile', $idProfile)
                ->where('statusenabled',true)->get();
               $result = array(
                   'parameterkepuasa' => $data,
                   'message' => 'er@epic',
               );
               return $this->respond($result);
      }
      public function saveInformasiDokter(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();

        try{

            if ($request['idjadwal']==''){
                $dataJadwalDokter = new JadwalDokter();
                $idDK = JadwalDokter::max('id');
                $norecDK = $dataJadwalDokter->generateNewId();
                $dataJadwalDokter->kdprofile = $idProfile;
                $dataJadwalDokter->statusenabled = true;
                $dataJadwalDokter->id = $idDK + 1;
                $dataJadwalDokter->norec = $norecDK;
                $dataJadwalDokter->objectpegawaifk = $request['objectpegawaifk'];
                $dataJadwalDokter->tglinput = $tglAyeuna;
            }else{
                $dataJadwalDokter =  JadwalDokter::where('id',$request['idjadwal'])->where('kdprofile', $idProfile)->first();
            }
                $dataJadwalDokter->objectruanganfk = $request['objectruanganfk'];
                $dataJadwalDokter->hari = $request['hari'];
                // $dataJadwalDokter->objecthariawal = $request['objecthariawal'];
                // $dataJadwalDokter->objecthariakhir = $request['objecthariakhir'];
                $dataJadwalDokter->jammulai = $request['jammulai'];
                $dataJadwalDokter->jamakhir = $request['jamakhir'];
                $dataJadwalDokter->keterangan = $request['keterangan'];
                $dataJadwalDokter->save();
                $idPegawai = $dataJadwalDokter->id;

           //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Simpan Informasi jadwal Dokter ';
            $logUser->noreff = $idPegawai;
            $logUser->referensi='id Informasi Jadwal Dokter';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Simpan Informasi jadwal Dokter' . $request['objectpegawaifk'];
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
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteInformasiDokter(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();

        try{

            $dataJadwalDokter =  JadwalDokter::where('id',$request['idJadwal'])->where('kdprofile', $idProfile)
                                 ->update([
                                     "statusenabled" => 'f',
                                 ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= $idProfile;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Delete Informasi jadwal Dokter ';
            $logUser->noreff = $request['idJadwal'];
            $logUser->referensi='id Informasi Jadwal Dokter';
            $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
            $logUser->tanggal = $tglAyeuna;
            $logUser->keterangan = 'Delete Informasi jadwal Dokter' . $request['objectpegawaifk'];
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
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function savePenungguPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        DB::beginTransaction();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->where('lu.kdprofile', $idProfile)
            ->first();

        try{

            $pp = new PenungguPasien();
            $pp->norec = $pp->generateNewId();
            $pp->kdprofile = $idProfile;
            $pp->statusenabled = true;
            $pp->tgltunggu = $request['tgltunggu'];
            $pp->identitas = $request['identitas'];
            $pp->keterangan = $request['keterangan'];
            $pp->noregistrasifk = $request['norec'];
            $pp->objecthubunganfk = $request['hubungankeluarga'];
            $pp->objectloginuserfk = $dataLogin['userData']['id'];
            $pp->namapenunggu = $request['namapenunggu'];
            $pp->save();

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
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
     
    }

    public function getLaporanPenunggu (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        ini_set('max_execution_time', 1000); //6 minutes
        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $paramIdRuang = '';
        $paramKelompokPasien = '';
        $paramNoregistrasi='';
        $paramNoRM ='';
        $paramPasien ='';
        $paramAlamat ='';
        $paramPenunggu='';
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
        if (isset($request['namapengunjung']) && $request['namapengunjung'] != "" && $request['namapengunjung'] != "undefined") {
            $paramPenunggu = " and pt.namapenunggu ilike '%".$request['namapengunjung']."%'";
        }
        if (isset($request['namapasien']) && $request['namapasien'] != "" && $request['namapasien'] != "undefined") {
            $paramPasien = " and ps.namapasien ilike '%".$request['namapasien']."%'";
        }
        if (isset($request['alamat']) && $request['alamat'] != "" && $request['alamat'] != "undefined") {
            $paramPasien = " and alm.alamatlengkap ilike '%".$request['alamat']."%'";
        }
        $data = \DB::select(DB::raw("select distinct 
                pt.tgltunggu ,p.namalengkap,ps.nocm,ps.namapasien,ru.namaruangan,pt.identitas,pt.namapenunggu,pt.keterangan,
                hm.hubungankeluarga,km.namakamar,pd.norec,pt.pengambil,h2.hubungankeluarga as hubunganpengambil, pt.norec as norec_pg
                FROM
                pasiendaftar_t AS pd
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                inner join ruangan_m as ru on ru.id= pd.objectruanganlastfk
                inner join pasien_m as ps on ps.id= pd.nocmfk
                INNER JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
                inner join penunggupasien_t as pt on pd.norec = pt.noregistrasifk 
                inner join loginuser_s as ls  on ls.id = pt.objectloginuserfk 
                inner join pegawai_m as p on p.id = ls.objectpegawaifk 
                inner join hubungankeluarga_m as hm on hm.id = pt.objecthubunganfk
                left join hubungankeluarga_m as h2 on pt.objecthubunganpengambilfk = h2.id
                left join kamar_m as km on km.id = apd.objectkamarfk 
                WHERE pd.kdprofile = $idProfile and
                pt.statusenabled = true
                $paramIdRuang
                $paramKelompokPasien
                $paramNoregistrasi
                $paramNoRM
                $paramPasien
                $paramPenunggu
                AND pt.tgltunggu BETWEEN '$tglAwal' AND '$tglAkhir'"));
//            $data = \DB::table('v_pasiendalamperawatan as v');


        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function savePengambilPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();

        try{
            $data = PenungguPasien::where('kdprofile', $idProfile)
            ->where('noregistrasifk',$request['norec'])
            ->update([
                "pengambil" => $request['namapengambil'],
                "objecthubunganpengambilfk" => $request['hubungankeluarga']
            ]);
            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
    }

    public function deletePenungguPasien(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        try{
            $data = PenungguPasien::where('kdprofile', $idProfile)
            ->where('norec',$request['norec'])
            ->update([
                "statusenabled" => 0
            ]);
            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
    }

}
