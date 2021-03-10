<?php
/**
 * Created by PhpStorm.
 * User: efan (ea@epic)
 * Date: 12/09/2019
 * Time:09:58
 */
namespace App\Http\Controllers\SysAdmin;


use App\Http\Controllers\ApiController;
use App\Master\Bku;
use App\Master\JenisPegawai;
use App\Master\KelompokTransaksi;
use App\Master\MapBkutoKelompokTransaksi;
use App\Master\MapPaketToProduk;
use App\Master\MapRuanganToPelayananMutu;
use App\Master\Paket;
use App\Master\PaketObat;
use App\Master\PaketObatDetail;
use App\Master\PelayananMutu;
use App\Master\Produk;
use App\Master\ProfileM;
use App\Traits\Valet;
use App\Transaksi\RiwayatPMKP;
use DB;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;

class SysAdminController  extends ApiController {

    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function getlistCombo (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
//        $jenis = DB::table('jenisruangan_m')
//            ->where('statusenabled',true)
//            ->get();
        $ruangan = DB::table('ruangan_m')
            ->select('id','namaruangan')
            ->where('statusenabled',true)
            ->where('objectdepartemenfk',18)
            ->where('kdprofile',$kdProfile)
            ->orderBy('namaruangan')
            ->get();
        $jenisPegawai = JenisPegawai::where('statusenabled',true)->get();
        $JenisProfile = DB::table('jenisprofile_m')
            ->select('id','jenisprofile')
            ->where('statusenabled',true)
            ->where('kdprofile',$kdProfile)
            ->orderBy('jenisprofile')
            ->get();
        $Password = $this->settingDataFixed('PasswordMenuSettingProfile', $kdProfile);

        $result = array(
//            'jenisruangan' => $jenis,
            'ruangan' => $ruangan,
            'jenispegawai' => $jenisPegawai,
            'jenisprofile' => $JenisProfile,
            'pass' => $Password,
            'created' => 'ea@epic'
        );
        return $this->respond($result);
    }

    public function getDataMapping (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('mapruangantojenisruangan_m as map')
            ->join('ruangan_m as ru','map.objectruanganfk','=','ru.id')
            ->join('jenisruangan_m as jr','map.objectjenisruanganfk','=','jr.id')
            ->select('map.*','ru.namaruangan','jr.jenisruangan')
            ->where('map.statusenabled',true)
            ->where('map.objectjenisruanganfk',$request['idjenis'])
            ->where('map.kdprofile',$kdProfile)
            ->get();
        $result = array(
            'data' => $data,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }
    public function getComboPaket (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paket = Paket::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $paketObat = PaketObat::where('statusenabled', true)->where('kdprofile', $kdProfile)->get();
        //$produk = Produk::where('statusenabled',true)->get();
        $produk = DB::table('harganettoprodukbykelas_m as hnp')
            ->join('produk_m as prd','prd.id','=','hnp.objectprodukfk')
            ->select('prd.id','prd.namaproduk')
            ->where('hnp.statusenabled',true)
            ->where('prd.statusenabled',true)
            ->where('hnp.kdprofile',$kdProfile)
            ->orderBy('prd.namaproduk')
            ->distinct()
            ->get();

        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->whereIn('jp.id',[97,283,210])
//            ->whereIn('jp.id',$arrkdjenisobat)
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataSatuanResep = \DB::table('satuanresep_m as kp')
            ->select('kp.id', 'kp.satuanresep')
            ->where('kp.statusenabled', true)
            ->orderBy('kp.satuanresep')
            ->get();

        $result = array(
            'paket' => $paket,
            'paketobat' => $paketObat,
            'produk' => $produk,
            'produkobat' =>$dataProduk,
            'satuanresep'=>$dataSatuanResep,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }
    public function getMappingPaket (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paket = DB::table('mappakettoproduk_m as maps')
            ->join('paket_m as pak','pak.id','=','maps.objectpaketfk')
            ->join('produk_m as prd','prd.id','=','maps.objectprodukfk')
            ->select('maps.*','pak.namapaket','prd.namaproduk')
            ->where('maps.statusenabled',true)
            ->where('maps.kdprofile',$kdProfile);

        if(isset($request['paketId']) && $request['paketId'] !='' ){
            $paket = $paket->where('maps.objectpaketfk',$request['paketId']);
        }
        if(isset($request['namaProduk']) && $request['namaProduk'] !='' ){
            $paket = $paket->where('prd.namaproduk','ilike','%'.$request['namaProduk'].'%');
        }
        $paket = $paket->get();
        $result = array(
            'data' => $paket,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }

    public function saveMapPaketToProduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ( $request['details'] as $item){
                $kode[] = (double) $item['id'];

            }

            $hapus = MapPaketToProduk::where('statusenabled',true)
                ->where('objectpaketfk',$request['paketId'])
                ->whereIn('objectprodukfk',$kode)
                ->delete();
            foreach ( $request['details'] as $item){
                $map = new MapPaketToProduk();
                $map->id = MapPaketToProduk::max('id') + 1;
                $map->kdprofile = $kdProfile;//12;
                $map->statusenabled = true;
                $map->norec =  substr(\Webpatser\Uuid\Uuid::generate(), 0, 32);
                $map->objectpaketfk = $request['paketId'];
                $map->objectprodukfk = $item['id'];
                $map->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
                'data' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteMapPaketToProduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item){
                MapPaketToProduk::where('id',$item['id'])->where('kdprofile',$kdProfile)->delete();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
//                'data' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
	
    public function getComboMKTTBKU (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $produk = KelompokTransaksi::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $paket = Bku::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $result = array(
            'paket' => $paket,
            'produk' => $produk,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }
    public function getMappingMKTTBKU (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paket = DB::table('mapbkutokelompoktransaksi_m as mp')
            ->join('bku_m as bku','bku.id','=','mp.idbku')
            ->join('kelompoktransaksi_m as kt','kt.id','=','mp.kelompoktransaksifk')
            ->select('mp.id','mp.kelompoktransaksifk','mp.idbku','bku.bku','kt.kelompoktransaksi')
            ->where('mp.statusenabled',true)
            ->where('mp.kdprofile',$kdProfile);

        if(isset($request['idBku']) && $request['idBku'] !='' ){
            $paket = $paket->where('mp.idbku',$request['idBku']);
        }
        if(isset($request['idTransaksi']) && $request['idTransaksi'] !='' ){
            $paket = $paket->where('mp.kelompoktransaksifk',$request['idTransaksi']);
        }
        $paket = $paket->get();
        $result = array(
            'data' => $paket,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }

    public function saveMapMKTTBKU(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ( $request['details'] as $item){
                $kode[] = (double) $item['id'];
            }

            $hapus = MapBkutoKelompokTransaksi::where('statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->where('idbku',$request['idBku'])
                ->whereIn('kelompoktransaksifk',$kode)
                ->delete();

            foreach ( $request['details'] as $item){
                $map = new MapBkutoKelompokTransaksi();
                $map->id = MapBkutoKelompokTransaksi::max('id') + 1;
                $map->kdprofile = $kdProfile;
                $map->statusenabled = true;
                $map->norec =  substr(\Webpatser\Uuid\Uuid::generate(), 0, 32);
                $map->idbku = $request['idBku'];
                $map->kelompoktransaksifk = $item['id'];
                $map->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
                'data' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteMapMKTTBKU(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item){
                MapBkutoKelompokTransaksi::where('id',$item['id'])->where('kdprofile', $kdProfile)->delete();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
//                'data' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
	
	public function getDataProfile(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataData = \DB::table('profile_m as pf')
            ->leftJoin('jenisprofile_m as jp','jp.id','=','pf.objectjenisprofilefk')
            ->leftJoin('desakelurahan_m as kel','kel.id','=','pf.objectdesakelurahanfk')
            ->leftJoin('kecamatan_m as kec','kec.id','=','pf.objectkecamatanfk')
            ->leftJoin('kotakabupaten_m as kab','kab.id','=','pf.objectkotakabupatenfk')
            ->leftJoin('propinsi_m as prov','prov.id','=','pf.objectpropinsifk')
            ->select(DB::raw("pf.id as idprofile,pf.statusenabled,pf.objectdesakelurahanfk,pf.objectjenisprofilefk,jp.jenisprofile,
                    pf.objectdesakelurahanfk,kel.namadesakelurahan as kelurahan,
                    pf.objectkecamatanfk,kec.namakecamatan as kecamatan,pf.objectkotakabupatenfk,kab.namakotakabupaten as kabupaten,
                    pf.objectpropinsifk,prov.namapropinsi as provinsi,pf.alamatemail,pf.alamatlengkap,pf.faksimile,
                    pf.fixedphone,pf.kodepos,pf.luasbangunan,pf.luastanah,pf.messagetopasien,pf.mobilephone,pf.mottosemboyan,
                    pf.namalengkap as namaprofile,pf.nopkp,pf.nosuratijinlast,
                    pf.npwp,pf.website,pf.gambarlogo,pf.tglregistrasi,pf.namapemerintahan,
                    pf.logo1,pf.logo2,pf.logo3,pf.logo4,pf.kota,pf.login"))
//            ->where('pf.kdprofile', $kdProfile)
            ->orderBy('pf.statusenabled','asc');
//            ->take(50);

        if (isset($request['namaProfile']) && $request['namaProfile'] != "" && $request['namaProfile'] != "undefined") {
            $dataData = $dataData->where(' pf.namalengkap', 'ilike', '%'. $request['namaProfile'].'%');
        }
        if (isset($request['alamatProfile']) && $request['alamatProfile'] != "" && $request['alamatProfile'] != "undefined") {
            $dataData = $dataData->where('pf.alamatlengkap', 'ilike','%'. $request['alamatProfile'].'%');
        }
        if (isset($request['idJenisProfile']) && $request['idJenisProfile'] != "" && $request['idJenisProfile'] != "undefined") {
            $dataData = $dataData->where('pf.objectjenisprofilefk', '=', $request['idJenisProfile']);
        }
        if (isset($request['idProfile']) && $request['idProfile'] != "" && $request['idProfile'] != "undefined") {
            $dataData = $dataData->where('pf.id', '=', $request['idProfile']);
        }
        if (isset($request['statusProfile']) && $request['statusProfile'] != "" && $request['statusProfile'] != "undefined") {
            if ($request['statusProfile'] == 'true' || $request['statusProfile'] == true){
                $dataData = $dataData->where('pf.statusenabled', '=', 1);
            }
        }
        $dataData = $dataData->get();
        $result = array(
            'profile' => $dataData,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function UpdateStatusEnabledProfile(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $data=$request['data'];
        try {

            $dataOP = ProfileM::where('id', $request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => $request['status'],
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
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function  SaveDataProfile (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            if($request['kdprofile'] == '') {
                $data = new ProfileM();
                $newId = ProfileM::max('id') + 1;
                $data->id = $newId;
                $data->norec = $data->generateNewId();
                $data->kdprofile = $newId;
                $data->statusenabled = $request['statusenabled'];
            }else{
                $data = ProfileM::where('kdprofile',$request['kdprofile'])->where('kdprofile', $kdProfile)->first();
            }
            if (isset($data->tglregistrasi) && $data->tglregistrasi != 'Invalid date'){
                $data->tglregistrasi = $request['tglregistrasi'];
            }
            $data->namalengkap = $request['namalengkap'];
            $data->objectjenisprofilefk = $request['jenisprofilefk'];
            $data->alamatlengkap = $request['alamatlengkap'];
            $data->kodepos = $request['kodepos'];
            $data->objectdesakelurahanfk = $request['desakelurahan'];
            $data->objectkecamatanfk = $request['kecamatan'];
            $data->objectkotakabupatenfk = $request['kabupaten'];
            $data->objectpropinsifk = $request['provinsi'];
            $data->faksimile = $request['faksimile'];
            $data->fixedphone = $request['phone'];
            $data->website = $request['website'];
            $data->alamatemail = $request['email'];
            $data->namapemerintahan = $request['dinas'];
            $data->save();
            $norecs = $data->id;
            if(isset($request['login']) && $request['login']!=null){
                $img = $request['login'];
                $datas = unpack("H*hex", $img);
                $datas = '0x'.$datas['hex'];
                $upLogin = ProfileM::where('id', $norecs)->update(
                    ['login' =>  \DB::raw("CONVERT(VARBINARY(MAX), $datas) ")]
                );
                #egi
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
                "norec" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "norec" => $data,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function savePaketObat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            if($request['idPaket'] == '') {
                $data = new PaketObat();
                $newId = PaketObat::max('id') + 1;
                $data->id = $newId;
                $data->kdpaket = $newId;
                $data->norec = $data->generateNewId();
                $data->kdprofile = $kdProfile;
                $data->statusenabled = true;
                $data->namapaket = $request['namapaket'];
            }else{
                $data = PaketObat::where('id',$request['idPaket'])->where('kdprofile', $kdProfile)->first();
                $dataDetail = PaketObatDetail::where('objectpaketobatfk', $request['idPaket'])->where('kdprofile', $kdProfile)->delete();
            }
            $data->reportdisplay = $request['namapaket'];
            $data->namaexternal = $request['namapaket'];
            $data->save();
            $idPaket = $data->id;

            foreach ( $request['paketobat'] as $item){
                $map = new PaketObatDetail();
                $map->id = PaketObatDetail::max('id') + 1;
                $map->kdprofile = $kdProfile;//12;
                $map->statusenabled = true;
                $map->norec =  $data->generateNewId();
                $map->objectpaketobatfk = $idPaket;
                $map->produkfk = $item['produkfk'];
                $map->qpaket = 0;
                $map->harga = 0;
                $map->ispagi = $item['ispagi'];
                $map->issiang = $item['issiang'];
                $map->ismalam = $item['ismalam'];
                $map->issore = $item['issore'];
                if (isset($item['satuanresepfk'])){
                    $map->satuanresepfk = $item['satuanresepfk'];
                }
                $map->qty = $item['jumlah'];
                if (isset($item['aturanpakai'])){
                    $map->aturanpakai = $item['aturanpakai'];
                }
                if (isset($item['keterangan'])){
                    $map->keterangan = $item['keterangan'];
                }
                $map->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();

            $result = array(
                'status' => 201,
                'data' => $data,
                'detail' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataPaketObat (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $result=[];
        $data = \DB::table('paketobat_m as sp')
            ->select('sp.id as paketId','sp.namapaket')
            ->where('sp.kdprofile', $kdProfile)
            ->where('sp.statusenabled', true);

        if(isset($request['paketId']) && $request['paketId'] !='' ){
            $data = $data->where('sp.id',$request['paketId']);
        }
        if(isset($request['namaPaket']) && $request['namaPaket'] !='' ){
            $data = $data->where('sp.namapaket','ilike','%'.$request['namaPaket'].'%');
        }
        $data = $data->get();
        foreach ($data as $item) {
            $details = \DB::select(DB::raw("SELECT pkd.*,pro.namaproduk,pro.objectsatuanstandarfk,ss.satuanstandar,
                         pkd.qty as jumlah,sn.satuanresep
                    FROM paketobatd_m as pkd
                    INNER JOIN produk_m As pro ON pro.id = pkd.produkfk
                    LEFT JOIN satuanstandar_m AS ss ON ss.id = pro.objectsatuanstandarfk
                    LEFT JOIN satuanresep_m AS sn ON sn.id = pkd.satuanresepfk
                    where pkd.kdprofile = $kdProfile and pkd.objectpaketobatfk=:norec"),
                array(
                    'norec' => $item->paketId,
                )
            );
            $result[] = array(
                'paketId' => $item->paketId,
                'namapaket' => $item->namapaket,
                'details' => $details,
            );
        }
        $result = array(
            'data' => $result,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }

    public function DeletePaketObat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {

            $data = PaketObat::where('id',$request['idPaket'])->where('kdprofile', $kdProfile)
                ->update(['statusenabled' => 'f',]);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();

            $result = array(
                'status' => 201,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getComboPelayananMutu (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $pelayananMutu = PelayananMutu::where('statusenabled',true)->where('kdprofile', $kdProfile)->get();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.statusenabled', true)
            ->where('kdprofile', $kdProfile)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled',true)
            ->where('kdprofile', $kdProfile);
        if(isset($request['idDept']) && $request['idDept']!="" && $request['idDept']!="undefined"){
            $dataRuangan = $dataRuangan->where('ru.objectdepartemenfk',$request['idDept']);
        };
        if(isset($request['Ruangan']) && $request['Ruangan']!="" && $request['Ruangan']!="undefined"){
            $dataRuangan = $dataRuangan->where('ru.namaruangan','ilike','%'. $request['Ruangan'].'%' );
        };
        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $dataRuangan = $dataRuangan->where('ru.id',$request['idRuangan']);
        };
        $dataRuangan = $dataRuangan->orderBy('ru.namaruangan');
        $dataRuangan = $dataRuangan->get();
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

        $result = array(
            'pelayananmutu' => $pelayananMutu,
            'departemen' => $dataDepartemen,
            'listdepartemen' => $dataInstalasi,
            'listruangan' => $dataRuangan,
            'as' => 'inhuman'
        );
        return $this->respond($result);
    }

    public function getMappingRuanganToPelayananMutu (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $paket = DB::table('mapruangantopelayananmutu_m as maps')
            ->join('pelayananmutu_m as pak','pak.id','=','maps.objectpelayananmutufk')
            ->join('ruangan_m as prd','prd.id','=','maps.objectruanganfk')
            ->select('maps.*','pak.pelayananmutu','prd.namaruangan')
            ->where('maps.statusenabled',true)
            ->where('maps.kdprofile',$kdProfile);

        if(isset($request['PelayananMutu']) && $request['PelayananMutu'] !='' ){
            $paket = $paket->where('maps.objectpelayananmutufk',$request['PelayananMutu']);
        }
        if(isset($request['idRuangan']) && $request['idRuangan'] !='' ){
            $paket = $paket->where('maps.objectruanganfk',$request['idRuangan']);
        }
        $paket = $paket->get();
        $result = array(
            'data' => $paket,
            'as' => 'ea@epic'
        );
        return $this->respond($result);
    }

    public function saveMapRuanganToPelayananMutu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ( $request['details'] as $item){
                $kode[] = (double) $item['id'];

            }
            $hapus = MapRuanganToPelayananMutu::where('statusenabled',true)
                ->where('objectpelayananmutufk',$request['pelayananmutu'])
                ->whereIn('objectruanganfk',$kode)
                ->delete();
            foreach ( $request['details'] as $item){
                $map = new MapRuanganToPelayananMutu();
                $map->id = MapRuanganToPelayananMutu::max('id') + 1;
                $map->kdprofile = $kdProfile;
                $map->statusenabled = true;
                $map->norec =  substr(\Webpatser\Uuid\Uuid::generate(), 0, 32);
                $map->objectpelayananmutufk = $request['pelayananmutu'];
                $map->objectruanganfk = $item['id'];
                $map->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
                'data' => $map,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteMapRuanganToPelayananMutu(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
            foreach ($request['data'] as $item){
                MapRuanganToPelayananMutu::where('id',$item['id'])->where('kdprofile',$kdProfile)->delete();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();

            $result = array(
                'status' => 201,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = "Hapus Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}