<?php
/**
 * Created by PhpStorm.
 * User: Efan Andrian(ea@epic)
 * Date: 14-Aug-19
 * Time: 16:35
 */
namespace App\Http\Controllers\SysAdmin\Master;

use App\Http\Controllers\ApiController;
use App\Master\HargaNettoProdukByKelas1;
use App\Master\HargaNettoProdukByKelasD;
use App\Master\JenisDiet;
use App\Master\JenisWaktu;
use App\Master\Kamar;
use App\Master\KategoryDiet;
use App\Master\Kelas;
use App\Master\KomponenHarga;
use App\Master\MapKelompokPasientoPenjamin;
use App\Master\MapRuanganToProduk;
use App\Master\Paket;
use App\Master\PelayananMutu;
use App\Master\Produk;
use App\Master\Rekanan;
use App\Master\Ruangan;
use App\Master\SiklusGizi;
use App\Master\SlottingOnline;
use App\Master\StatusBed;
use App\Master\TempatTidur;
use App\Traits\Valet;
use App\Transaksi\KonversiSatuan;
use App\Transaksi\OrderPelayanan;
use DB;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class MasterController extends ApiController{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication=false);
    }

    public function getListProduk(Request $request,$limit=null){
        //get request
        $kdProfile = (int) $this->getDataKdProfile($request);
        $filter = $request->all();
        //get data
        $data = \DB::table('produk_m as p')
            ->select('p.id','p.kdproduk','p.kdbarcode','p.deskripsiproduk','p.namaproduk','dj.detailjenisproduk','p.statusenabled')
            ->leftJoin('rm_sediaan_m as rm','rm.id','=','p.objectsediaanfk')
            ->leftJoin('merkproduk_m as mp','mp.id','=','objectmerkprodukfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','p.objectrekananfk')
            ->leftJoin('golongandarah_m as gd','gd.id','=','p.golongandarahfk')
            ->leftJoin('status_barang_m as sb','sb.id','=','p.objectstatusbarangfk')
            ->leftJoin('rhesus_m as rh','rh.id','=','p.rhesusfk')
            ->leftJoin('rm_generik_m as rg','rg.id','=','p.objectgenerikfk')
            ->leftJoin('rm_detail_obat_m as rd','rd.id','=','p.objectdetailobatfk')
            ->leftJoin('bahansample_m as bs','bs.id','=','p.bahansamplefk')
            ->leftJoin('chartofaccount_m as ch','ch.id','=','p.objectaccountfk')
            ->leftJoin('bahanproduk_m as bh','bh.id','=','p.objectbahanprodukfk')
            ->leftJoin('bentukproduk_m as bp','bp.id','=','p.objectbentukprodukfk')
            ->leftJoin('departemen_m as dp','dp.id','=','p.objectdepartemenfk')
            ->leftJoin('detailgolonganproduk_m as dg','dg.id','=','p.objectdetailgolonganprodukfk')
            ->leftJoin('detailjenisproduk_m as dj','dj.id','=','p.objectdetailjenisprodukfk')
            ->where('p.kdprofile', $kdProfile);

        if(isset($filter['kdProduk']) && !empty($filter['kdProduk'])){
            $data = $data->where('p.id','=',$filter['kdProduk']);
        }elseif (isset($filter['kdInternal']) && !empty($filter['kdInternal'])) {
            $data = $data->where('p.kdproduk','=',$filter['kdInternal']);
        }elseif (isset($filter['kdBarcode']) && !empty($filter['kdBarcode'])) {
            $data = $data->where('p.kdbarcode','=',$filter['kdBarcode']);
        }elseif (isset($filter['kdBmn']) && !empty($filter['kdBmn'])) {
            $data = $data->where('p.kodebmn','=',$filter['kdBmn']);
        }elseif (isset($filter['nmProduk']) && !empty($filter['nmProduk'])) {
            $data = $data->where('p.namaproduk','ilike','%'.$filter['nmProduk'].'%');
        }
        

        $data = $data->orderBy('p.namaproduk');
        $data = $data->take($filter['jmlRows']);
        $data = $data->get();
        return $this->respond($data);
    }

    public function UpdateStatusEnabledProduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $data=$request['data'];
        try {
                $dataOP = Produk::where('id', $request['id'])
                    ->where('kdprofile',$kdProfile)
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

    public function saveDataProduk(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($data['id'] == null || $data['id'] == ''){
                $newId = Produk::max('id');
                $newId = $newId + 1;
                $prod = new Produk();
                $prod->id = $newId;
                $prod->kdprofile = $kdProfile;
            }else{
                $prod =  Produk::where('id',$data['id'])->where('kdprofile', $kdProfile)->first();
            }
            $prod->statusenabled =  $data['statusenabled'];
            $prod->kodeexternal = $data['kodeexternal'];
            $prod->namaexternal = $data['namaexternal'];
            $prod->norec = $prod->generateNewId();
            $prod->reportdisplay = $data['reportdisplay'];
            $prod->objectaccountfk = $data['objectaccountfk'];
            $prod->objectbahanprodukfk = $data['objectbahanprodukfk'];
            $prod->objectbentukprodukfk = $data['objectbentukprodukfk'];
            $prod->objectdepartemenfk = $data['objectdepartemenfk'];
            $prod->objectdetailgolonganprodukfk = $data['objectdetailgolonganprodukfk'];
            $prod->objectdetailjenisprodukfk = $data['objectdetailjenisprodukfk'];
            $prod->objectfungsiprodukfk = $data['objectfungsiprodukfk'];
            $prod->objectgprodukfk = $data['objectgprodukfk'];
            $prod->objectgolonganprodukfk = $data['objectgolonganprodukfk'];
            $prod->objectjenisperiksafk = $data['objectjenisperiksafk'];
            $prod->objectkategoryprodukfk = $data['objectkategoryprodukfk'];
            $prod->objectlevelprodukfk = $data['objectlevelprodukfk'];
            $prod->objectprodusenprodukfk = $data['objectprodusenprodukfk'];
            $prod->objectsatuanbesarfk = $data['objectsatuanbesarfk'];
            $prod->objectsatuankecilfk = $data['objectsatuankecilfk'];
            $prod->objectsatuanstandarfk = $data['objectsatuanstandarfk'];
            $prod->objectstatusprodukfk = $data['objectstatusprodukfk'];
            $prod->objecttypeprodukfk = $data['objecttypeprodukfk'];
            $prod->objectunitlaporanfk = $data['objectunitlaporanfk'];
            $prod->objectwarnaprodukfk = $data['objectwarnaprodukfk'];
            $prod->deskripsiproduk = $data['deskripsiproduk'];
            $prod->kdbarcode = $data['kdbarcode'];
            $prod->kdproduk = $data['kdproduk'];
            $prod->kdproduk_intern = $data['kdproduk_intern'];
            $prod->kekuatan = $data['kekuatan'];
            $prod->namaproduk = $data['namaproduk'];
            $prod->nilainormal = $data['nilainormal'];
            $prod->qproduk = $data['qproduk'];
            $prod->qtyjualterkecil = $data['qtyjualterkecil'];
            $prod->qtylemak = $data['qtylemak'];
            $prod->qtyporsi = $data['qtyporsi'];
            $prod->qtyprotein = $data['qtyprotein'];
            $prod->qtysatukemasan = $data['qtysatukemasan'];
            $prod->qtyterkecil = $data['qtyterkecil'];
            $prod->qtykalori = $data['qtykalori'];
            $prod->qtykarbohidrat = $data['qtykarbohidrat'];
            $prod->kdprodukintern = $data['kdprodukintern'];
            $prod->objectjenisperiksapenunjangfk = $data['objectjenisperiksapenunjangfk'];
            $prod->bahansamplefk = $data['bahansamplefk'];
            $prod->objectdetailobatfk = $data['objectdetailobatfk'];
            $prod->objectgenerikfk = $data['objectgenerikfk'];
            $prod->objectmerkprodukfk = $data['objectmerkprodukfk'];
            $prod->objectrekananfk = $data['objectrekananfk'];
            $prod->objectsediaanfk = $data['objectsediaanfk'];
            $prod->objectstatusbarangfk = $data['objectstatusbarangfk'];
            $prod->golongandarahfk = $data['golongandarahfk'];
            $prod->rhesusfk = $data['rhesusfk'];
            $prod->kodebmn = $data['kodebmn'];
            $prod->spesifikasi = $data['spesifikasi'];
            $prod->tglproduksi = $data['tglproduksi'];
            $prod->status = $data['status'];
            $prod->verifikasianggaran = $data['verifikasianggaran'];
            if (isset($request['isprodukintern'])){
                $prod->isprodukintern = $data['isprodukintern'];
            }
            if (isset($request['isarvdonasi'])){
                $prod->isprodukintern = $data['isarvdonasi'];
            }
            if (isset($request['isnarkotika'])){
                $prod->isprodukintern = $data['isnarkotika'];
            }
            if (isset($request['ispsikotropika'])){
                $prod->isprodukintern = $data['ispsikotropika'];
            }
            if (isset($request['isonkologi'])){
                $prod->isprodukintern = $data['isonkologi'];
            }
            if (isset($request['isoot'])){
                $prod->isprodukintern = $data['isoot'];
            }
            if (isset($request['isprekusor'])){
                $prod->isprodukintern = $data['isprekusor'];
            }
            if (isset($request['isvaksindonasi'])){
                $prod->isprodukintern = $data['isvaksindonasi'];
            }
            if (isset($request['objectjenisgenerikfk'])){
                $prod->isprodukintern = $data['objectjenisgenerikfk'];
            }
            if (isset($request['keterangan'])){
                $prod->isprodukintern = $data['keterangan'];
            }
            if (isset($request['poinmedis'])){
                $prod->isprodukintern = $data['poinmedis'];
            }
            $prod->save();

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
                "data" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getProdukbyId(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['idProduk'];
        $data = \DB::table('produk_m as p')
            ->select('p.*','dj.objectjenisprodukfk','dj.detailjenisproduk','dj.objectjenisprodukfk',
                'jp.jenisproduk','jp.objectkelompokprodukfk','kp.kelompokproduk','ss.satuanstandar',
                'kategory.kategoryproduk','rg.name as rm_generikname','gnr.namagproduk','level.levelproduk',
                'dp.namadepartemen','fungsi.fungsiproduk','bentuk.namabentukproduk','bhn.namabahanproduk',
                'type.typeproduk','warna.warnaproduk','merk.merkproduk','rd.name as detailobat','golongan.golonganproduk',
                'dg.detailgolonganproduk')
            ->leftJoin('rm_sediaan_m as rm','rm.id','=','p.objectsediaanfk')
            ->leftJoin('merkproduk_m as merk','merk.id','=','objectmerkprodukfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','p.objectrekananfk')
            ->leftJoin('golongandarah_m as gd','gd.id','=','p.golongandarahfk')
            ->leftJoin('status_barang_m as sb','sb.id','=','p.objectstatusbarangfk')
            ->leftJoin('rhesus_m as rh','rh.id','=','p.rhesusfk')
            ->leftJoin('rm_generik_m as rg','rg.id','=','p.objectgenerikfk')
            ->leftJoin('rm_detail_obat_m as rd','rd.id','=','p.objectdetailobatfk')
            ->leftJoin('bahansample_m as bs','bs.id','=','p.bahansamplefk')
            ->leftJoin('chartofaccount_m as ch','ch.id','=','p.objectaccountfk')
            ->leftJoin('bahanproduk_m as bhn','bhn.id','=','p.objectbahanprodukfk')
            ->leftJoin('bentukproduk_m as bentuk','bentuk.id','=','p.objectbentukprodukfk')
            ->leftJoin('departemen_m as dp','dp.id','=','p.objectdepartemenfk')
            ->leftJoin('detailgolonganproduk_m as dg','dg.id','=','p.objectdetailgolonganprodukfk')
            ->leftJoin('detailjenisproduk_m as dj','dj.id','=','p.objectdetailjenisprodukfk')
            ->leftJoin('jenisproduk_m as jp','jp.id','=','dj.objectjenisprodukfk')
            ->leftJoin('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJoin('satuanstandar_m as ss','ss.id','=','p.objectsatuanstandarfk')
            ->leftJoin('kategoryproduk_m as kategory','kategory.id','=','p.objectkategoryprodukfk')
            ->leftJoin('generalproduk_m as gnr','gnr.id','=','p.objectgprodukfk')
            ->leftJoin('levelproduk_m as level','level.id','=','p.objectlevelprodukfk')
            ->leftJoin('fungsiproduk_m as fungsi','fungsi.id','=','p.objectfungsiprodukfk')
            ->leftJoin('typeproduk_m as type','type.id','=','p.objecttypeprodukfk')
            ->leftJoin('warnaproduk_m as warna','warna.id','=','p.objectwarnaprodukfk')
            ->leftJoin('golonganproduk_m as golongan','golongan.id','=','p.objectgolonganprodukfk')
            ->where('p.id','=',$id)
            ->where('p.kdprofile', $kdProfile)
            ->get();

        $id_jenisproduk = $data[0]->objectjenisprodukfk;
        if(!empty($id_jenisproduk)){
            $data_jenisproduk = DB::table('jenisproduk_m')
                ->select('objectkelompokprodukfk')
                ->where('id',$id_jenisproduk)
                ->get();

            //add field to obj
            $data[0]->objectkelompokprodukfk = $data_jenisproduk[0]->objectkelompokprodukfk;
        }
        return $this->respond($data);
    }

    public function getjenisproduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idkp = $request['kelompokProdukfk'];
        $data = \DB::table('jenisproduk_m')
            ->select('id','objectkelompokprodukfk as id_kelompokproduk','jenisproduk')
            ->where('kdprofile', $kdProfile);

        if($idkp !== null){
            $data = $data->where('objectkelompokprodukfk','=',$idkp);
        }
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDetailjenisprodukbyIdjenisproduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['jenisProdukId'];
        $data = DB::table('detailjenisproduk_m')
            ->select('id','detailjenisproduk')
            ->where('objectjenisprodukfk','=',$id)
            ->where('kdprofile', $kdProfile)
            ->get();

        return $this->respond($data);
    }

    public function getDataComboMaster(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $kelompokProdukfk = $request['kelompokProdukfk'];
        $JenisProdukfk = $request['objectjenisprodukfk'];
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(\Illuminate\Support\Facades\DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $kdProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        // Kategory Produk //
            $kategori = DB::table('kategoryproduk_m')
                ->select('id','kategoryproduk')
                ->where('kdprofile', $kdProfile)
                ->where('statusenabled', true)
                ->get();

            $generik = DB::table('rm_generik_m')
                ->select('id','name')
                ->where('kdprofile', $kdProfile)
                ->where('statusenabled', true)
                ->get();

            $gproduk = $data = DB::table('generalproduk_m')
                ->select('id','namagproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $levelproduk = $data = DB::table('levelproduk_m')
                ->select('id','levelproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $detailjenis = \DB::table('detailjenisproduk_m')
                ->select('id','objectjenisprodukfk as id_jenisproduk','detailjenisproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();
        // Kategory Produk //

        // Spesifikasi Produk //
            $fungsiproduk = DB::table('fungsiproduk_m')
                ->select('id','fungsiproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $golonganproduk = DB::table('golonganproduk_m')
                ->select('id','golonganproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $bentukproduk = DB::table('bentukproduk_m')
                ->select('id','namabentukproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $bahanproduk = DB::table('bahanproduk_m')
                ->select('id','namabahanproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $typeproduk =  DB::table('typeproduk_m')
                ->select('id','typeproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $warnaproduk = DB::table('warnaproduk_m')
                ->select('id','warnaproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $merkproduk = DB::table('merkproduk_m')
                ->select('id','merkproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $detailobat = $data = DB::table('rm_detail_obat_m')
                ->select('id','name')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $detailgolonganproduk = DB::table('detailgolonganproduk_m')
                ->select('id','detailgolonganproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();
        // Spesifikasi Produk //

        // Satuan Produk //
            $satuanbesar =  DB::table('satuanbesar_m')
                ->select('id','satuanbesar')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $satuankecil = DB::table('satuankecil_m')
                ->select('id','satuankecil')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $satuanstandar =  DB::table('satuanstandar_m')
                ->select('id','satuanstandar')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();
        // Satuan Produk //

        // Bahan Sample Produk //
            $jenisperiksa = DB::table('jenisperiksapenunjang_m')
                ->select('id','jenisperiksa')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $jenisperiksapenunjang = DB::table('jenisperiksapenunjang_m')
                ->select('id','jenisperiksa')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $bahansample = DB::table('bahansample_m')
                ->select('id','namabahansample')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();
        // Bahan Sample Produk //

        // Bank Darah //
            $rhesus = $data = DB::table('rhesus_m')
                ->select('id','rhesus')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $golongandarah =  DB::table('golongandarah_m')
                ->select('id','golongandarah')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();
        // Bank Darah //
            $kelompokproduk = \DB::table('kelompokproduk_m')
                ->select('id','kelompokproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $jenisproduk = \DB::table('jenisproduk_m')
                ->select('id','objectkelompokprodukfk as id_kelompokproduk','jenisproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile);
            if (isset($kelompokProdukfk) && $kelompokProdukfk != "" && $kelompokProdukfk != "undefined" && $kelompokProdukfk != 'null') {
                $jenisproduk = $jenisproduk->where('objectkelompokprodukfk', $kelompokProdukfk);
            }
            $jenisproduk = $jenisproduk->get();

            $detailjenisProduk = DB::table('detailjenisproduk_m')
                ->select('id','detailjenisproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile);
                if (isset($JenisProdukfk) && $JenisProdukfk != "" && $JenisProdukfk != "undefined" && $JenisProdukfk != 'null') {
                    $detailjenisProduk = $detailjenisProduk->where('objectjenisprodukfk', $JenisProdukfk);
                }
            $detailjenisProduk = $detailjenisProduk->get();

            $dataInstalasi = \DB::table('departemen_m as dp')
                ->where('dp.statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->orderBy('dp.namadepartemen')
                ->get();

            $produsenproduk = DB::table('produsenproduk_m')
                ->select('id','namaprodusenproduk')
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->get();

            $statusproduk = DB::table('statusproduk_m')
                ->where('statusenabled', true)
                ->select('id','statusproduk')
                ->where('kdprofile', $kdProfile)
                ->get();

            $chartofaccount = DB::table('chartofaccount_m')
                ->where('statusenabled', true)
                ->select('id','namaaccount')
                ->where('kdprofile', $kdProfile)
                ->get();

            $kelompokprodukbpjs = \DB::table('kelompokprodukbpjs_m as djp')
                ->select('djp.id','djp.kelompokprodukbpjs')
                ->where('kdprofile', $kdProfile)
                ->where('djp.statusenabled',true)->get();

            //////
            $dataRuangan = \DB::table('ruangan_m as ru')
                ->where('ru.statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->orderBy('ru.namaruangan')
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
            $dataModulAplikasi = \DB::table('modulaplikasi_s as ma')
                ->select('ma.id','ma.modulaplikasi')
                ->where('kdprofile', $kdProfile)
                ->where('ma.statusenabled',true)
                ->orderBy('ma.modulaplikasi')
                ->take(100)
                ->get();

            $dataJenisRekanan = \DB::table('jenisrekanan_m as jr')
                ->select('jr.id','jr.jenisrekanan')
                ->where('kdprofile', $kdProfile)
                ->where('jr.statusenabled',true)
                ->orderBy('jr.jenisrekanan')
                ->take(100)
                ->get();

            $dataKelompok = \DB::table('kelompokpasien_m as kp')
                ->select('kp.id', 'kp.kelompokpasien')
                ->where('kdprofile', $kdProfile)
                ->where('kp.statusenabled', true)
                ->orderBy('kp.kelompokpasien')
                ->get();

            $dataKelas = \DB::table('kelas_m as kl')
                ->select('kl.id', 'kl.namakelas')
                ->where('kdprofile', $kdProfile)
                ->where('kl.statusenabled', true)
                ->orderBy('kl.namakelas')
                ->get();
            $KdDepartemenRawatJalan = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $kdProfile);
            $ruanganRajal = Ruangan::where('statusenabled',true)
            ->select('id','namaruangan','objectdepartemenfk')
            ->where('kdprofile', $kdProfile)
            ->where('objectdepartemenfk',$KdDepartemenRawatJalan)
            ->orderBy('namaruangan')
            ->get();

        $data = array(
            "datalogin" => $dataLogin,
            "detailuser" => $dataPegawaiUser,
            "kelompokproduk" => $kelompokproduk,
            "jenisproduk" => $jenisproduk,
            "detailjenisproduk" => $detailjenisProduk,
            "departemen" => $dataInstalasi,
            "produsenproduk" => $produsenproduk,
            "statusproduk" => $statusproduk,
            "chartofaccount" => $chartofaccount,
            'kelompokprodukbpjs' => $kelompokprodukbpjs,
            'departemen' => $dataDepartemen,
            'modulaplikasi' =>   $dataModulAplikasi,
            'jenisrekanan' => $dataJenisRekanan,
            'kelompokpasien' => $dataKelompok,
            "kategori" => array(
                "kategori" => $kategori,
                "generik" => $generik,
                "gproduk" => $gproduk,
                "level" => $levelproduk,
                "detailjenis" => $detailjenis,
            ),
            "spesifikasi" => array(
				"fungsiproduk" => $fungsiproduk,
				"bentukproduk" => $bentukproduk,
				"bahanproduk" => $bahanproduk,
				"typeproduk" => $typeproduk,
				"warnaproduk" => $warnaproduk,
				"merkproduk" => $merkproduk,
				"detailobat" => $detailobat,
				"golonganproduk" => $golonganproduk,
				"detailgolonganproduk" => $detailgolonganproduk
			),
            "satuan" => array(
                "standar" => $satuanstandar,
                "besar" => $satuanbesar,
                "kecil" => $satuankecil
            ),
            "penunjang" => array(
                "jenisperiksa" => $jenisperiksa,
                "jenisperiksapenunjang" => $jenisperiksapenunjang,
                "bahansample" => $bahansample
            ),
            "labdarah" => array(
                "golongandarah" => $golongandarah,
                "rhesus" => $rhesus
            ),
            "kelas" => $dataKelas,
            "ruanganrajal" => $ruanganRajal,
            "as" => "ea@epic"
        );
        return $this->respond($data);
    }

    public function getDataRekananMaster(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk=[];
        $dataProduk  = \DB::table('rekanan_m as st')
            ->select('st.id','st.namarekanan','st.alamatlengkap','st.telepon','st.faksimile')
            ->where('st.statusenabled',true)
            ->where('st.kdprofile', $kdProfile)
            ->orderBy('st.namarekanan');
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('st.namarekanan','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(10);
        $dataProduk = $dataProduk->get();
        return $this->respond($dataProduk);
    }

    public function getDataProdukbyDetailProduk(Request $request) {
//        $req=$request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk = \DB::table('produk_m as pr')
            ->select('pr.id','pr.namaproduk')
            ->where('pr.statusenabled',true)
            ->where('pr.kdprofile', $kdProfile)
            ->orderBy('pr.namaproduk');

        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $request['namaproduk'].'%' );
        };

        if(isset($request['detailjenisprodukfk']) && $request['detailjenisprodukfk']!="" && $request['detailjenisprodukfk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.objectdetailjenisprodukfk','=',$request['detailjenisprodukfk'] );
        };

        if(isset($request['kelompokBPJS']) && $request['kelompokBPJS']!="" && $request['kelompokBPJS']!="undefined"){
            $dataProduk = $dataProduk->where('pr.objectkelompokprodukbpjsfk','=',$request['kelompokBPJS']);
        };

//        $dataProduk = $dataProduk->take(200);
        $dataProduk = $dataProduk->get();

        $result = array(
            'produk' => $dataProduk,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDataKelompok(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk = \DB::table('produk_m as pr')
            ->select('pr.id','pr.namaproduk')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->orderBy('pr.namaproduk');
        if(isset($request['kelompokprodukbpjsfk']) && $request['kelompokprodukbpjsfk']!="" && $request['kelompokprodukbpjsfk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.objectkelompokprodukbpjsfk','=', $request['kelompokprodukbpjsfk'] );
        };
        $dataProduk = $dataProduk->get();

        $result = array(
            'produk' => $dataProduk,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function SaveKelompokProduk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request['0'];
        try {
            foreach ($req['data'] as $item){
                $newKS = Produk::where('id',$item['produkId'])->where('kdprofile',$kdProfile)->first();
                $newKS->objectkelompokprodukbpjsfk = $item['kelompokprodukbpjsfk'];
                $newKS->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Simpan Kelompok";
        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "produk" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "produk" => $newKS,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDataProdukPerKode(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req=$request->all();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss', 'ss.id', '=', 'pr.objectsatuanstandarfk')
            ->select('pr.id', 'pr.namaproduk', 'ss.id as ssid', 'ss.satuanstandar','pr.kdproduk')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled', true)
            ->orderBy('pr.namaproduk');

        if(isset($req['namaproduk']) &&
            $req['namaproduk']!="" &&
            $req['namaproduk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $req['namaproduk'] .'%' );
        };

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $req['filter']['filters'][0]['value'].'%' );
        };
        $dataProduk = $dataProduk->take(20);
        $dataProduk = $dataProduk->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar',
                'ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $kdProfile)
            ->where('ks.statusenabled',true)
            ->get();


        $dataProdukResult=[];
        foreach ($dataProduk as $item){
            $satuanKonversi=[];
            foreach ($dataKonversiProduk  as $item2){
                if ($item->id == $item2->objekprodukfk){
                    $satuanKonversi[] =array(
                        'ssid' =>   $item2->satuanstandar_tujuan,
                        'satuanstandar' =>   $item2->satuanstandar2,
                        'nilaikonversi' =>   $item2->nilaikonversi,
                    );
                }
            }

            $dataProdukResult[]=array(
                'id' =>   $item->id,
                'namaproduk' =>   $item->namaproduk,
                'ssid' =>   $item->ssid,
                'satuanstandar' =>   $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
                'kdproduk' => $item->kdproduk,
            );
        }

        return $this->respond($dataProdukResult);
    }

    public function getKonversiSatuan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('produk_m as pr')
            ->leftJOIN('konversisatuan_t as ks','pr.id','=','ks.objekprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->leftJOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.norec','ks.nilaikonversi','ks.objekprodukfk','ss.id as ssidasal','ss.satuanstandar as satuanstandar_asal',
                'ss2.id as ssidtujuan','ss2.satuanstandar as satuanstandar_tujuan')
            ->where('pr.kdprofile', $kdProfile);
//            ->where('ks.statusenabled',true);
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('pr.id', $request['produkfk'] );
        };
        $data = $data->get();

        return $this->respond($data);
    }

    public function SaveKonversiSatuan(Request $request) {
        $req = $request['0'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        //## KartuStok
        if ($req['norec'] == '-') {
            $newKS = new KonversiSatuan();
            $norecKS = $newKS->generateNewId();
        }else{
            $newKS = KonversiSatuan::where('norec',$req['norec'])->where('kdprofile', $kdProfile)->first();
            $norecKS = $req['norec'];
        }
        $newKS->norec = $norecKS;
        $newKS->kdprofile = $kdProfile;
        $newKS->statusenabled = true;
        $newKS->nilaikonversi = $req['nilaikonversi'];
        $newKS->objekprodukfk = $req['objekprodukfk'];
        $newKS->satuanstandar_asal = $req['satuanstandar_asal'];
        $newKS->satuanstandar_tujuan = $req['satuanstandar_tujuan'];
        try {
            $newKS->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Konversi Satuan";
        }
        if ($transStatus == 'true') {
            $transMessage = "Simpan Konversi Satuan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Konversi Satuan Gagal!!";
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

    public function hapusKonversiSatuan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $req = $request['0'];
        try {
            $newKS = KonversiSatuan::where('norec',$req['norec'])->where('kdprofile', $kdProfile)->delete();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Konversi Satuan";
        }
        if ($transStatus == 'true') {
            $transMessage = "Hapus Konversi Satuan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newKS,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Hapus Konversi Satuan Gagal!!";
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

    public function getBarangKonversi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('pr.kdprofile', $kdProfile)
            ->orderBy('pr.namaproduk');

        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $dataProduk = $dataProduk->where('pr.namaproduk','ilike','%'. $request['namaproduk'].'%' );
        };
        $dataProduk = $dataProduk->take(100);
        $dataProduk = $dataProduk->get();

        $dataDetailJenisProduk = \DB::table('detailjenisproduk_m as djp')
            ->select('djp.id','djp.detailjenisproduk','djp.objectjenisprodukfk')
            ->where('djp.kdprofile', $kdProfile)
            ->where('djp.statusenabled',true)->get();

        $KdJenisProdukObat = (int) $this->settingDataFixed('KdJenisProdukObat', $kdProfile);
        $dataJenisProduk = \DB::table('jenisproduk_m as jp')
            ->select('jp.id','jp.jenisproduk')
            ->where('jp.kdprofile', $kdProfile)
            ->where('jp.id',$KdJenisProdukObat)
            ->where('jp.statusenabled',true)->get();

        $dataSatuanStandar = \DB::table('satuanstandar_m as ss')
            ->select('ss.id','ss.satuanstandar')
            ->where('ss.kdprofile', $kdProfile)
            ->where('ss.statusenabled',true)->get();

//        $dataHIS = DB::select(DB::raw("
//             select ho.barcodeid,ho.hobatid,pr.namaproduk,ho.packageunit,ho.dosageform,ss.id as ssid,ss.satuanstandar,ho.statusenabled
//             from his_obat_ms_t as ho inner join produk_m as pr on cast(ho.hobatid as INTEGER) = pr.id
//             inner join satuanstandar_m as ss on ss.id = pr.objectsatuanstandarfk
//            where ho.statusenabled = :statusenabled order by ho.barcodeid"),
//            array(
//                'statusenabled' => true,
//            )
//        );

        foreach ($dataJenisProduk as $item){
            $detailjenisproduk=[];
            foreach ($dataDetailJenisProduk  as $item2){
                if ($item->id == $item2->objectjenisprodukfk){
                    $detailjenisproduk[] =array(
                        'id' =>   $item2->id,
                        'detailjenisproduk' =>   $item2->detailjenisproduk,
                    );
                }
            }

            $dataJenisProduk[]=array(
                'id' =>   $item->id,
                'jenisproduk' =>   $item->jenisproduk,
                'detailjenisproduk' => $detailjenisproduk,
            );
        }

        $result = array(
            'jenisproduk' => $dataJenisProduk,
            'produk' => $dataProduk,
            'satuanstandar' => $dataSatuanStandar,
//            'dataHIS' => $dataHIS,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function UpdateStatusEnabledRekanan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $data=$request['data'];
        try {

            $dataOP = Rekanan::where('id', $request['id'])
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

    public function getDataRekanan(Request $request) {
        $dataLogin = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataRekanan = \DB::table('rekanan_m as r')
            ->JOIN ('jenisrekanan_m as jr','jr.id','=','r.objectjenisrekananfk')
            ->select('r.id', 'r.kdrekanan','r.kodeexternal' ,'r.namarekanan', 'jr.jenisrekanan',
                     'r.statusenabled','r.objectjenisrekananfk')
            ->where('r.kdprofile', $kdProfile)
//            ->where('r.statusenabled',true)
            ->orderBy('r.id','ASC');
//            ->get();

        if (isset($request['kdrekanan']) && $request['kdrekanan'] != "" && $request['kdrekanan'] != "undefined") {
            $dataRekanan = $dataRekanan->where('r.kdrekanan', '=', $request['kdrekanan']);
        }
        if (isset($request['id']) && $request['id'] != "" && $request['id'] != "undefined") {
            $dataRekanan = $dataRekanan->where('r.id', '=', $request['id']);
        }
        if (isset($request['namarekanan']) && $request['namarekanan'] != "" && $request['namarekanan'] != "undefined") {
            $dataRekanan = $dataRekanan->where('r.namarekanan', 'ilike','%'. $request['namarekanan'].'%');
        }
        if (isset($request['objectjenisrekananfk']) && $request['objectjenisrekananfk'] != "" && $request['objectjenisrekananfk'] != "undefined") {
            $dataRekanan = $dataRekanan->where('r.objectjenisrekananfk', '=', $request['objectjenisrekananfk']);
        }
        if (isset($request['kodeexternal']) && $request['kodeexternal'] != "" && $request['kodeexternal'] != "undefined") {
            $dataRekanan = $dataRekanan->where('r.kodeexternal', '=', $request['kodeexternal']);
        }
        $dataRekanan = $dataRekanan -> take($request['jmlRows']);
        $dataRekanan = $dataRekanan -> get();
        $result = array(
            'rekanan' => $dataRekanan,
            'datalogin' => $dataLogin,
            'message' => 'ramdan@mithyc',
        );

        return $this->respond($result);
    }

    public function getRekananById(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idRekanan = $request['idRekanan'];
        $data = \DB::table('rekanan_m as rek')
            ->select('rek.*','jr.jenisrekanan','mp.id as idmap','mp.objectkelompokpasienfk')
            ->leftJoin('chartofaccount_m as coa','coa.id','=','rek.objectaccountfk')
            ->leftJoin('desakelurahan_m as dk','dk.id','=','rek.objectdesakelurahanfk')
            ->leftJoin('jenisrekanan_m as jr','jr.id','=','rek.objectjenisrekananfk')
            ->leftJoin('kecamatan_m as kc','kc.id','=','rek.objectkecamatanfk')
            ->leftJoin('kotakabupaten_m as kk','kk.id','=','rek.objectkotakabupatenfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','rek.objectpegawaifk')
            ->leftJoin('propinsi_m as pr','pr.id','=','rek.objectpropinsifk')
            ->leftJoin('mapkelompokpasientopenjamin_m as mp','mp.kdpenjaminpasien','=','rek.id')
            ->where('rek.kdprofile', $kdProfile)
            ->where('rek.id','=', $idRekanan)
            ->get();
        return $this->respond($data);
    }

    public function saveDataRekanan(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $dataLogin = $request->all();
        //## RUANGAN
        $r_R=$request['rekanan'];
        try{
            if ($request['rekanan']['idrekanan']==''){
                $newR = new Rekanan();
                $idRekanan = Rekanan::max('id');
                $idRekanan = $idRekanan + 1;
                $newR->id = $idRekanan;
                $newR->kdrekanan = $idRekanan;
                $newR->kdprofile = $kdProfile;
                $newR->norec = $idRekanan;
                $newR->kodeexternal = $idRekanan;
                $newR->statusenabled = true;


            }else{
                $newR =  Rekanan::where('id',$request['rekanan']['idrekanan'])->where('kdprofile', $kdProfile)->first();
            }
            // $newR->norec = $newR->generateNewId();
            $newR->namaexternal = $r_R['namarekanan'];
            $newR->reportdisplay = $r_R['namarekanan'];
            $newR->namarekanan = $r_R['namarekanan'];
    //        $newR->objectaccountfk = $r_R['objectaccountfk'];
            $newR->objectdesakelurahanfk = $r_R['objectdesakelurahanfk'];
            $newR->objectjenisrekananfk = $r_R['objectjenisrekananfk'];
            $newR->objectkecamatanfk = $r_R['objectkecamatanfk'];
            $newR->objectkotakabupatenfk = $r_R['objectkotakabupatenfk'];
            $newR->objectpegawaifk = $r_R['objectpegawaifk'];
            $newR->objectpropinsifk = $r_R['objectpropinsifk'];
    //        $newR->objectrekananheadfk = $r_R['objectrekananheadfk'];
            $newR->alamatlengkap = $r_R['alamatlengkap'];
            $newR->bankrekeningnama = $r_R['bankrekeningnama'];
            $newR->bankrekeningnomor = $r_R['bankrekeningnomor'];
            $newR->contactperson = $r_R['contactperson'];
            $newR->desakelurahan = $r_R['desakelurahan'];
            $newR->email = $r_R['email'];
            $newR->faksimile = $r_R['faksimile'];
            $newR->kodepos = $r_R['kodepos'];
            $newR->kotakabupaten = $r_R['kotakabupaten'];
            $newR->nopkp = $r_R['nopkp'];
            $newR->npwp = $r_R['npwp'];
            $newR->rtrw = $r_R['rtrw'];
            $newR->telepon = $r_R['telepon'];
            $newR->website = $r_R['website'];
            $newR->namadesakelurahan = $r_R['namadesakelurahan'];
            $newR->namakecamatan = $r_R['namakecamatan'];
            $newR->namakotakabupaten = $r_R['namakotakabupaten'];
            $newR->rekananmoupksfk = $r_R['rekananmoupksfk'];
            $newR->perjanjiankerjasama = $r_R['perjanjiankerjasama'];
            $newR->qrekanan = 0;
            $newR->save();

            $idMap = MapKelompokPasientoPenjamin::max('id');
            $idMap = $idMap + 1;
                $r_R=$request['rekanan'];

            if ($request['rekanan']['idMap']==''){
                $newM = new MapKelompokPasientoPenjamin();
                $newM->id = $idMap;
                $newM->norec = $idMap;
                $newM->kodeexternal = $idMap;
                if($request['rekanan']['idrekanan'] == ''){
                    $newM->kdpenjaminpasien = $idRekanan;
                }else{
                    $newM->kdpenjaminpasien = $request['rekanan']['idrekanan'];
                }

            }else{
                $newM =  MapKelompokPasientoPenjamin::where('id',$request['rekanan']['idMap'])->where('kdprofile', $kdProfile)->first();
            }
            // $newR->norec = $newR->generateNewId();
            $newM->kdprofile = $kdProfile;
            $newM->statusenabled = true;
            $newM->namaexternal = '';
            $newM->reportdisplay = '';
            $newM->objectkelompokpasienfk = $r_R['objectkelompokpasienfk'];
            $newM->save();

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
                "datRekanan" => $newR,
                "as" => 'egie@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "datRekanan" => $newR,
                "as" => 'egie@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getKelompokTableMaster(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
//        $data = DB::table('settingdatafixed_m')
//            ->select(DB::raw("case when kelompok is null then 'Lain-lain' else kelompok end as kelompok"))
//            ->groupBy('kelompok')
//            ->orderBy('kelompok')
//            ->get();
        $namaTable = $request['namatable'];
        $data = DB::select(DB::raw(
            "SELECT
              
                table_name as kelompok FROM information_schema.tables WHERE 
				--table_type = 'base table' and 
				table_name ilike '%_m'
				and table_name like '%$namaTable%'
                order by table_name
                limit 50;"
        ));
        $result = array(
            'data' => $data,
            'as' => 'as@epic'
        );
        return $this->respond($result);
    }

    public function getTableRowDetail(Request $request) {
        $id = $request['id'];
        $table = $request['table'];
        $value = $request['value'];
        $text = $request['text'];
        $idid = $request['idid'];

        $dataRaw = DB::select(DB::raw(
            "
				SELECT $value as value,$text as text,$idid as idid
                FROM $table
                WHERE $value  = $id

			"
        ));


        return $this->respond($dataRaw);
    }

    public function saveTable(Request $request) {
        $table = $request['table'];
        $data = $request['data'];
        $id = $request['id'];
        $tglAyeuna = date('Y-m-d H:i:s');
        $field = '';
        // return  $data ;
        $values = '';
        if ($id != ''){
            //update row data
            foreach ($data as $itm){
                if ($itm['id'] != 'id'){
                    if ($itm['data_type'] == 'varchar' ||$itm['data_type'] == "character varying" || $itm['data_type'] ==  "character"){
                        $values = $values . "," . $itm['id'] . "='" . $itm['values'] . "'";
                    }elseif ($itm['data_type'] == 'char'){
                        $values = $values . "," . $itm['id'] . "='" . $itm['values'] . "'";
                    }elseif ($itm['data_type'] == 'boolean'){
                        if($itm['values'] == 1){
                            $values = $values . "," . $itm['id'] . "='" .'t'."'" ;
                        }else{
                            $values = $values . "," . $itm['id'] . "='" . 'f'."'" ;
                        }
                    }elseif ($itm['data_type'] == 'timestamp without time zone'){
                        $values =  $values . "," . $itm['id'] ."='". $tglAyeuna."'";
                    }else{
                        $values = $values . "," . $itm['id'] . "=" . (float)$itm['values'] ;
                    }
                }
            }

            $values = substr($values, 1, strlen($values)-1);;
            $queryStr = "
				update $table
				set " . $values . "
				where id = $id
		  	
			";
        }else{
            //insert new row
            $maxID = DB::select(DB::raw(
                "
				SELECT max(id)
                FROM $table

			"
            ));
            // return $this->respond($maxID);
            $maxid = (int)$maxID[0]->max + 1;
            $field  ='' ;
            $values ='';
            foreach ($data as $itm){
                // if ($itm['id'] != 'id'){
                //     if ($itm['data_type'] == 'nvarchar' ||$itm['data_type'] == "character varying" || $itm['data_type'] == "character"){
                //         $field  = $field . "," . $itm['id'] ;
                //         $values = $values . ",'" . $itm['values'] . "'";
                //     }elseif ($itm['data_type'] == 'nchar'){
                //         $field  = $field . "," . $itm['id'] ;
                //         $values = $values . ",'" . $itm['values'] . "'";
                //     }elseif ($itm['data_type'] == 'boolean'){
                //         $field  = $field . "," . $itm['id'] ;
                //         if($itm['values'] == 1){
                //            $values = $values . ",'" . 't'."'";
                //         }else{
                //            $values = $values . ",'" . 'f'."'";
                //         }
                //     }else{
                //         $field  = $field . "," . $itm['id'] ;
                //         $values = $values . "," . (float)$itm['values'] ;
                //     }

                // }
                if ($itm['id'] != 'id'){
                    $field =  $field .",".$itm['id'] ;
                    if ($itm['data_type'] == 'varchar' ||$itm['data_type'] == "character varying" || $itm['data_type'] ==
                        "character"
                    ){
                        $values = $values . ",'" . $itm['values'] . "'";
                    }elseif ($itm['data_type'] == 'char'){
                        $values = $values . ",'" . $itm['values'] . "'";
                    }elseif ($itm['data_type'] == 'boolean'){

                        if($itm['values'] == 1){
                            $values = $values . ",'" .'t'."'" ;
                        }else{
                            $values = $values . ",'" . 'f'."'" ;
                        }
                        // return $values;

                    }elseif ($itm['data_type'] == 'timestamp without time zone'){
                        $values = $values =  $values . "," ."'". $tglAyeuna."'";
                    }else{
                        $values = $values . "," . (float)$itm['values'] ;
                    }
                }
            }

            $values = substr($values, 1, strlen($values)-1);;
            $field = substr($field, 1, strlen($field)-1);
            $queryStr = "
				insert into $table   (id,".$field.")
                values
				 (". $maxid ."," . $values . ")
				
				
			";
        }

        $dataRaw = DB::statement(
            $queryStr
        );
        return $queryStr;
    }

    public function getTableDetail(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
//        if($request['kelompok'] == 'Lain-lain'){
//            $request['kelompok']  = null;
//        }
//        $dataRaw = \DB::table('settingdatafixed_m')
//            ->where('kelompok', $request['kelompok'])
//            ->select('*','keteranganfungsi as caption','nilaifield')
//            ->orderBy('id');
//        $dataRaw = $dataRaw->get();
        $namaTable = $request['kelompok'];
        $filterField = $request['filterfield'];
        $filter = $request['filter'];

        $dataRaw = DB::select(DB::raw(
            "
				SELECT *
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME  ='$namaTable'

			"
        ));
        if  ($filterField == '-'){
            $dataTable = DB::select(DB::raw(
                "
				SELECT
                 -- top 100 
                  *
                FROM $namaTable
                limit 100
			"
            ));
        }else{
            $dataTable = DB::select(DB::raw(
                "
				SELECT 
              
                *
                FROM $namaTable
                WHERE $filterField ilike '%$filter%'

                limit 100

			"
            ));
        }

        $setting = DB::table('mastertable_m')
            ->select('*')
            ->get();
        $dataraw3A =[];
        $dataColumn = [];
//        $i = 0 ;
        foreach ($dataRaw as $dataRaw2) {
            $head = '';

            $tipe ='';
            $idTabel = '';
            $textTabel = '';
            $tableRelasi = '';
            $column_name = '';
            foreach ($setting as $epan){
                if ($epan->namafield == $dataRaw2->column_name){
                    $tipe = 'combobox';
//                    $column_name = $epan->namafield;
//                    $idTabel = $epan->fieldkeytabelrelasi;
//                    $textTabel = $epan->fieldreportdisplaytabelrelasi;
//                    $tableRelasi = $epan->tabelrelasi;
                }
            }
            if ($tipe == ''){
                $type =  $dataRaw2->data_type;
            }else{
                $type =  $tipe;
            }





            $width = 0;
            if  ((float)$dataRaw2->character_maximum_length == null){
                $width =11 * 5;
            }else{
                $width = (float)$dataRaw2->character_maximum_length * 5;
            }
            $hide = false;
            if ($dataRaw2->column_name == 'kodeexternal'){
                $hide =true;
            }
            if ($dataRaw2->column_name == 'namaexternal'){
                $hide =true;
            }
            if ($dataRaw2->column_name == 'norec'){
                $hide =true;
            }
            if ($dataRaw2->column_name == 'reportdisplay'){
                $hide =true;
            }
            if (strpos($dataRaw2->column_name, 'object') !== false) {
                $hide =true;
            }
            $dataColumn[] = array(
                'field' => $dataRaw2->column_name,
                'title' => $dataRaw2->column_name,
                'width' => $width ,
                'hidden' => $hide
            );


            $dataraw3A[] = array(
                'table_catalog' => $dataRaw2->table_catalog,
                'table_schema' => $dataRaw2->table_schema,
                'table_name' => $dataRaw2->table_name,
                'column_name' => $dataRaw2->column_name,
                'ordinal_position' => $dataRaw2->ordinal_position,
                'is_nullable' => $dataRaw2->is_nullable,
                'data_type' => $dataRaw2->data_type,
                'caption' => $dataRaw2->column_name ,
                'character_maximum_length' => $dataRaw2->character_maximum_length ,

//                'cbotable' => $dataRaw2->tabelrelasi,
//                'fieldreportdisplaytabelrelasi' => $dataRaw2->fieldreportdisplaytabelrelasi,
//                'keteranganfungsi' => $dataRaw2->keteranganfungsi,
//                'namafield' => $dataRaw2->namafield,
                'id' => $dataRaw2->ordinal_position ,
//                'nilaifield' => $dataRaw2->nilaifield ,
//                'tabelrelasi' => $dataRaw2->tabelrelasi,
//                'typefield' => $dataRaw2->typefield,
                'type' =>$type  =='character varying' ? 'textbox':$type,
//                'kelompok' => $dataRaw2->kelompok,
                'value' => NULL ,
//                'text' => $dataRaw2->reportdisplay,

//                'kdprofile' => $dataRaw2->kdprofile,
//                'statusenabled' => $dataRaw2->statusenabled,
//                'kodeexternal'=> $dataRaw2->kodeexternal,
//                'namaexternal' => $dataRaw2->namaexternal,
//                'reportdisplay' => $dataRaw2->reportdisplay,
//                'fieldkeytabelrelasi' => $dataRaw2->fieldkeytabelrelasi,
//                'caption' => $head . $dataRaw2->caption ,
//
//                'cbotable' => $dataRaw2->tabelrelasi,
//                'fieldreportdisplaytabelrelasi' => $dataRaw2->fieldreportdisplaytabelrelasi,
//                'keteranganfungsi' => $dataRaw2->keteranganfungsi,
//                'namafield' => $dataRaw2->namafield,
//                'id' => $dataRaw2->id ,
//                'nilaifield' => $dataRaw2->nilaifield ,
//                'tabelrelasi' => $dataRaw2->tabelrelasi,
//                'typefield' => $dataRaw2->typefield,
//                'type' =>$type ,
//                'kelompok' => $dataRaw2->kelompok,
//                'value' => $dataRaw2->nilaifield ,
//                'text' => $dataRaw2->reportdisplay,
            );
//            $i = $i + 1;
        }



        $result = array(
            'kolom1' => $dataraw3A,
            'data' => $dataTable,
            'kolomsetting' => $dataColumn,
            'comboboxorigin' => $setting,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getComboPartTable(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['columnName'];
        $req= $request->all();
        $setting = DB::table('mastertable_m')
            ->select('*')
            ->where('namafield',$id)
            ->get();

        $table =  $setting[0]->tabelrelasi;
        $namaField = strtolower ($setting[0]->fieldreportdisplaytabelrelasi);
        $keyField = strtolower ($setting[0]->fieldkeytabelrelasi);
        $table = strtolower ($table);
        $data  = \DB::table("$table")
            ->select("$namaField as text" ,"$keyField as value")
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy("$keyField");

        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data->where("$namaField",'ilike','%'. $req['filter']['filters'][0]['value'].'%' );

        };
        $data = $data->take(10);
        $data = $data->get();

        return $this->respond($data);
    }

    public function getKelompokUser(Request $request){
        $detailLogin = $request->all();
        $data = \DB::table('kelompokuser_s as ku')
            ->join('loginuser_s as lu','lu.objectkelompokuserfk','=','ku.id')
            ->select('ku.id','ku.kelompokuser','lu.namauser')
            ->where('lu.id',$request['luId'])
            ->first();
        $result = array(
            "data" => $data,
            "as" => 'as@rmdn',
        );
        return $this->respond($result);
    }

    public function view_harganettoprodukbykelas(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data= \DB::table('harganettoprodukbykelas_m as ht')
            ->leftjoin('asalproduk_m as ap', 'ap.id', '=', 'ht.objectasalprodukfk')
            ->leftjoin('jenistarif_m as jt', 'jt.id', '=', 'ht.objectjenistariffk')
            ->leftjoin('matauang_m as mu', 'mu.id', '=', 'ht.objectmatauangfk')
            ->join('produk_m as pr', 'pr.id', '=', 'ht.objectprodukfk')
            ->join('kelas_m as kl', 'kl.id', '=', 'ht.objectkelasfk')
            ->join('jenispelayanan_m as jp', 'jp.id', '=', 'ht.objectjenispelayananfk')
            ->select('ht.id','ht.kdprofile','ht.statusenabled','ht.kodeexternal','ht.namaexternal',
                'ht.norec','ht.reportdisplay','ht.objectasalprodukfk','ap.asalproduk','ht.objectjenistariffk','jt.jenistarif',
                'ht.objectkelasfk','kl.namakelas','ht.objectmatauangfk','mu.matauang','ht.objectprodukfk','pr.namaproduk',
                'ht.factorrate','ht.hargadiscount','ht.harganetto1','ht.harganetto2','ht.hargasatuan','ht.persendiscount',
                'ht.qtycurrentstok','ht.tglberlakuakhir','ht.tglberlakuawal','ht.tglkadaluarsalast','ht.id as id_hn_m','ht.objectjenispelayananfk','jp.jenispelayanan')
            ->where('ht.statusenabled',true)
            ->where('ht.kdprofile', $kdProfile);

        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike','%'. $request['namaproduk'] .'%');
        }
        if(isset($request['objectasalprodukfk']) && $request['objectasalprodukfk']!="" && $request['objectasalprodukfk']!="undefined"){
            $data = $data->where('ht.objectasalprodukfk', $request['objectasalprodukfk'] );
        }
        if(isset($request['objectjenistariffk']) && $request['objectjenistariffk']!="" && $request['objectjenistariffk']!="undefined"){
            $data = $data->where('ht.objectjenistariffk', $request['objectjenistariffk'] );
        }
        if(isset($request['objectkelasfk']) && $request['objectkelasfk']!="" && $request['objectkelasfk']!="undefined"){
            $data = $data->where('ht.objectkelasfk', $request['objectkelasfk']);
        }
        if(isset($request['objectmatauangfk']) && $request['objectmatauangfk']!="" && $request['objectmatauangfk']!="undefined"){
            $data = $data->where('ht.objectmatauangfk', $request['objectmatauangfk'] );
        }
        if(isset($request['objectprodukfk']) && $request['objectprodukfk']!="" && $request['objectprodukfk']!="undefined"){
            $data = $data->where('ht.objectprodukfk', $request['objectprodukfk']);
        }
        if(isset($request['id']) && $request['id']!="" && $request['id']!="undefined"){
            $data = $data->where('ht.id', $request['id']);
        }

        $data = $data->take(50)->get();

        return $this->respond($data);
    }

    public function GetTarifProduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $hal = $request{'page'};
        $skp = 10 * $hal;
        $produk = Produk::where('statusenabled',true)->where('kdprofile',$kdProfile);
        if(isset($request['namaproduk']) && $request['namaproduk']!="" && $request['namaproduk']!="undefined") {
            $produk = $produk->where('namaproduk','ilike','%'. $request->namaproduk .'%');
        }
        if ($skp < $produk->count()){
            $produk = $produk->take(10)->skip($skp);
        }
        $produk = $produk->get();

        foreach ($produk as $item){
            $list[] = array("id" => $item->id,
                "namaProduk" => $item->namaproduk,
            );
        }

        return $this->respond($list);
    }

    public function hapusHargaNettoByKelas(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $JD = HargaNettoProdukByKelas1::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => false
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function view_harganettoprodukbykelasD(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data= \DB::table('harganettoprodukbykelasd_m as ht')
            ->join('komponenharga_m as kh', 'kh.id', '=', 'ht.objectkomponenhargafk')
//            ->join('jenistarif_m as jt', 'jt.id', '=', 'ht.objectjenistariffk')
//            ->join('matauang_m as mu', 'mu.id', '=', 'ht.objectmatauangfk')
//            ->join('produk_m as pr', 'pr.id', '=', 'ht.objectprodukfk')
//            ->join('kelas_m as kl', 'kl.id', '=', 'ht.objectkelasfk')
            ->select('ht.id','ht.kdprofile','ht.statusenabled','ht.kodeexternal','ht.namaexternal',
                'ht.norec','ht.reportdisplay','ht.objectasalprodukfk','ht.objectjenistariffk',
                'ht.objectkelasfk','ht.objectmatauangfk','ht.objectkomponenhargafk',
                'ht.factorrate','ht.hargadiscount','ht.harganetto1','ht.harganetto2','ht.hargasatuan','ht.persendiscount',
                'ht.qtycurrentstok','ht.tglberlakuakhir','ht.tglberlakuawal','ht.tglkadaluarsalast','kh.komponenharga',
                'ht.id as id_hn_d')
            ->where('ht.kdprofile', $kdProfile)
        ;

        if(isset($request['objectprodukfk']) && $request['objectprodukfk']!="" && $request['objectprodukfk']!="undefined"){
            $data = $data->where('ht.objectprodukfk',$request['objectprodukfk'] );
        }
        if(isset($request['objectkelasfk']) && $request['objectkelasfk']!="" && $request['objectkelasfk']!="undefined"){
            $data = $data->where('ht.objectkelasfk', $request['objectkelasfk'] );
        }
        if(isset($request['objectasalprodukfk']) && $request['objectasalprodukfk']!="" && $request['objectasalprodukfk']!="undefined" && $request['objectasalprodukfk']!='null'){
            $data = $data->where('ht.objectasalprodukfk', $request['objectasalprodukfk'] );
        }
        if(isset($request['objectjenistariffk']) && $request['objectjenistariffk']!="" && $request['objectjenistariffk']!="undefined" &&  $request['objectjenistariffk']!='null'){
            $data = $data->where('ht.objectjenistariffk', $request['objectjenistariffk']);
        }
        if(isset($request['objectmatauangfk']) && $request['objectmatauangfk']!="" && $request['objectmatauangfk']!="undefined" &&  $request['objectmatauangfk'] != 'null'){
            $data = $data->where('ht.objectmatauangfk', $request['objectmatauangfk'] );
        }
        if(isset($request['objectjenispelayananfk']) && $request['objectjenispelayananfk']!="" && $request['objectjenispelayananfk']!="undefined" && $request['objectjenispelayananfk']!='null'){
            $data = $data->where('ht.objectjenispelayananfk', $request['objectjenispelayananfk'] );
        }

        $data = $data->get();
        //return $this->respond(array($request['namaproduk']));
        return $this->respond($data);
    }

    public function getComboHargaNetto(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $jenisTarif = \DB::table('jenistarif_m')
            ->select('id','jenistarif as jenisTarif')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('jenistarif')
            ->get();
        $asalProduk = \DB::table('asalproduk_m')
            ->select('id','asalproduk as asalProduk')
            ->where('statusenabled',true)
            ->where('kdprofile', $kdProfile)
            ->orderBy('asalproduk')
            ->get();

        $kelas = \DB::table('kelas_m')
            ->select('id','namakelas as namaKelas')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('namakelas')
            ->get();
        $matauang = \DB::table('matauang_m')
            ->select('id','matauang as mataUang')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('matauang')
            ->get();
        $sk = \DB::table('suratkeputusan_m')
            ->select('id','namask','nosk')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('id')
            ->get();
        $jp = \DB::table('jenispelayanan_m')
            ->select('id','jenispelayanan')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->orderBy('id')
            ->get();
        $sks =[];
        foreach ($sk as $item){
            $sks []= array(
                'id' => $item->id,
                'namask' => $item->namask,
                'nosk' => $item->nosk,
                'sk' => $item->nosk.' - '. $item->namask,
            );
        }
        $result = array(
            'jenistarif' => $jenisTarif,
            'asalproduk' => $asalProduk,
            'kelas' => $kelas,
            'matauang' => $matauang,
            'suratkeputusan' => $sks,
            'jenispelayanan' => $jp
        );
        return $this->respond($result);
    }

    public function ListMaster(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        if(isset($request['jenis']) && $request['jenis']!="" && $request['jenis']!="undefined"){
            if ($request['jenis'] == 'ruangan'){
                $R = Ruangan::where('statusenabled',true)->where('kdprofile',$kdProfile)->get();
                foreach ($R as $item){
                    $data[] = ['id' =>  $item->id,
                        'namaruangan' => $item->namaruangan,
                    ];
                }
            }
            if ($request['jenis'] == 'kamar'){
                $R = Kamar::where('statusenabled',true)->where('kdprofile',$kdProfile)->get();
                foreach ($R as $item){
                    $data[] = ['id' =>  $item->id,
                        'namakamar' => $item->namakamar,
                    ];
                }
            }
            if ($request['jenis'] == 'statusbed'){
                $R = StatusBed::where('statusenabled',true)->where('kdprofile',$kdProfile)->get();
                foreach ($R as $item){
                    $data[] = ['id' =>  $item->id,
                        'statusbed' => $item->statusbed,
                    ];
                }
            }
            if ($request['jenis'] == 'kelas'){
                $R = Kelas::where('statusenabled',true)->where('kdprofile',$kdProfile)->get();
                foreach ($R as $item){
                    $data[] = ['id' =>  $item->id,
                        'namakelas' => $item->namakelas,
                    ];
                }
            }
            if ($request['jenis'] == 'komponenharga'){
                $R = KomponenHarga::where('statusenabled',true)->where('kdprofile',$kdProfile)->get();
                foreach ($R as $item){
                    $data[] = ['id' =>  $item->id,
                        'komponenharga' => $item->komponenharga,
                        'factorrate' => $item->factorrate,
                    ];
                }
            }
        }
        return $this->respond($data);
    }

    public function saveharganettoprodukbykelas_kelasD(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idIdAn = 0;
        $input=$request->all();
        //return $this->respond(array($input['head']['idHead']));
        $transStatus = true;
        $transMsg =null;
        DB::beginTransaction();

        if ($input['jenis'] == 'simpan'){
            $h1 = 0;
            $h2 = 0;
            $h3 = 0;
            $h4 = 0;

            $newId = HargaNettoProdukByKelas1::max('id');
            $newId = $newId + 1;

            $HHHH = new HargaNettoProdukByKelas1();
            $HHHH->id = $newId;
            $HHHH->kdprofile = $kdProfile;
            $idIdAn=$newId;
            $HHHH->hargadiscount = (float)$h1;
            $HHHH->harganetto1 = (float)$h2;
            $HHHH->harganetto2 = (float)$h3;
            $HHHH->hargasatuan = (float)$h4;
            $HHHH->objectprodukfk = $input['head']['objectprodukfk'];
            $HHHH->objectjenistariffk = $input['head']['objectjenistariffk'];
            $HHHH->objectasalprodukfk = $input['head']['objectasalprodukfk'];
            $HHHH->objectkelasfk = $input['head']['objectkelasfk'];
            $HHHH->objectmatauangfk = $input['head']['objectmatauangfk'];
            $HHHH->persendiscount = (float)$input['head']['persendiscount'];
            $HHHH->factorrate = (float)$input['head']['factorrate'];
            $HHHH->qtycurrentstok = (float)$input['head']['qtycurrentstok'];
            if(isset($input['head']['tglberlakuakhir']) && $input['head']['tglberlakuakhir']!="" && $input['head']['tglberlakuakhir']!="undefined"){
                $HHHH->tglberlakuakhir = $input['head']['tglberlakuakhir'];
            }
            if(isset($input['head']['tglberlakuawal']) && $input['head']['tglberlakuawal']!="" && $input['head']['tglberlakuawal']!="undefined"){
                $HHHH->tglberlakuawal = $input['head']['tglberlakuawal'];
            }
            if(isset($input['head']['tglkadaluarsalast']) && $input['head']['tglkadaluarsalast']!="" && $input['head']['tglkadaluarsalast']!="undefined"){
                $HHHH->tglkadaluarsalast = $input['head']['tglkadaluarsalast'];
            }
            $HHHH->reportdisplay = $input['head']['reportdisplay'];
            $HHHH->kodeexternal = $input['head']['kodeexternal'];
            $HHHH->namaexternal = $input['head']['namaexternal'];
            $HHHH->statusenabled = true;

            try {
                $HHHH->save();
            } catch (\Exception $e) {
                $transStatus = false;
                $transMsg = "Simpan New HargaNettoProdukByKelas Gagal";
            }
        }//end simpan head
        if ($input['jenis'] == 'update'){
            if ($input['detail']['idDetail'] != '') {
                $hhhh_D = HargaNettoProdukByKelasD::where('id', $input['detail']['idDetail'])->first();
                $hhhh_D->objectkomponenhargafk = $input['detail']['objectkomponenhargafk'];
                $hhhh_D->factorrate = $input['detail']['factorrate'];
                $hhhh_D->hargadiscount = $input['detail']['hargadiscount'];
                $hhhh_D->harganetto1 = $input['detail']['harganetto1'];
                $hhhh_D->harganetto2 = $input['detail']['harganetto2'];
                $hhhh_D->hargasatuan = $input['detail']['hargasatuan'];
                $hhhh_D->persendiscount = $input['detail']['persendiscount'];
                if(isset($input['head']['tglberlakuakhir']) && $input['head']['tglberlakuakhir']!="" && $input['head']['tglberlakuakhir']!="undefined"){
                    $hhhh_D->tglberlakuakhir = $input['head']['tglberlakuakhir'];
                }
                if(isset($input['head']['tglberlakuawal']) && $input['head']['tglberlakuawal']!="" && $input['head']['tglberlakuawal']!="undefined"){
                    $hhhh_D->tglberlakuawal = $input['head']['tglberlakuawal'];
                }
                if(isset($input['head']['tglkadaluarsalast']) && $input['head']['tglkadaluarsalast']!="" && $input['head']['tglkadaluarsalast']!="undefined"){
                    $hhhh_D->tglkadaluarsalast = $input['head']['tglkadaluarsalast'];
                }
                try {
                    $hhhh_D->save();
                } catch (\Exception $e) {
                    $transStatus = false;
                    $transMsg = "Update HargaNettoProdukByKelasD Gagal";
                    //break;
                }
            }
            if ($input['detail']['idDetail'] == '' && $input['detail']['objectkomponenhargafk'] != 0) {
                $ID_id_an = $this->getSequence('harganettoprodukbykelasd_m_id_seq');

                $hhhh_D = new  HargaNettoProdukByKelasD;
                $hhhh_D->id = $ID_id_an;
                $hhhh_D->kdprofile = $kdProfile;
                $hhhh_D->statusenabled = 1;
                //$hhhh_D->kodeexternal = '';
                //$hhhh_D->namaexternal = '';
                $hhhh_D->norec = $hhhh_D->generateNewId();
                //$hhhh_D->reportdisplay = '';
                $hhhh_D->objectasalprodukfk = $input['head']['objectasalprodukfk'];
                $hhhh_D->objectjenistariffk = $input['head']['objectjenistariffk'];
                $hhhh_D->objectkelasfk = $input['head']['objectkelasfk'];
                $hhhh_D->objectkomponenhargafk = $input['detail']['objectkomponenhargafk'];
                $hhhh_D->objectmatauangfk = $input['head']['objectmatauangfk'];
                $hhhh_D->objectprodukfk = $input['head']['objectprodukfk'];
                $hhhh_D->factorrate = $input['detail']['factorrate'];
                $hhhh_D->hargadiscount = $input['detail']['hargadiscount'];
                $hhhh_D->harganetto1 = $input['detail']['harganetto1'];
                $hhhh_D->harganetto2 = $input['detail']['harganetto2'];
                $hhhh_D->hargasatuan = $input['detail']['hargasatuan'];
                $hhhh_D->persendiscount = $input['detail']['persendiscount'];
                //$hhhh_D->qtycurrentstok = 0;
//                $hhhh_D->tglberlakuakhir = null;
//                $hhhh_D->tglberlakuawal = null;
//                $hhhh_D->tglkadaluarsalast = null;
                if ($transStatus) {
                    try {
                        $hhhh_D->save();
                    } catch (\Exception $e) {
                        $transStatus = false;
                        $transMsg = "Add HargaNettoProdukByKelasD Gagal";
                    }
                }
            }

            try{
                $HD = HargaNettoProdukByKelasD::where('objectprodukfk',$input['head']['objectprodukfk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('objectjenistariffk',$input['head']['objectjenistariffk'])
                    ->where('objectasalprodukfk',$input['head']['objectasalprodukfk'])
                    ->where('objectkelasfk',$input['head']['objectkelasfk'])
                    ->where('objectmatauangfk',$input['head']['objectmatauangfk'])->get();
            }
            catch(\Exception $e){
                $transStatus = false;
                $transMsg = "Get sum HargaNettoProdukByKelasD Gagal";
                //break;
            }

            if ($transStatus) {
                $h1 = 0;
                $h2 = 0;
                $h3 = 0;
                $h4 = 0;
                foreach ($HD as $item) {
                    $h1 = $h1 + $item->hargadiscount;
                    $h2 = $h2 + $item->harganetto1;
                    $h3 = $h3 + $item->harganetto2;
                    $h4 = $h4 + $item->hargasatuan;
                }

                $HHHH = HargaNettoProdukByKelas1::where('id', $input['head']['idHead'])->where('kdprofile', $kdProfile)->first();
                $HHHH->hargadiscount = (float)$h1;
                $HHHH->harganetto1 = (float)$h2;
                $HHHH->harganetto2 = (float)$h3;
                $HHHH->hargasatuan = (float)$h4;
                //klo di update relasi ke komponen jd rusak
                //                $HHHH->objectjenistariffk = $input['head']['objectjenistariffk'];
                //                $HHHH->objectasalprodukfk = $input['head']['objectasalprodukfk'];
                //                $HHHH->objectkelasfk = $input['head']['objectkelasfk'];
                //                $HHHH->objectmatauangfk = $input['head']['objectmatauangfk'];
                //end#### klo di update relasi ke komponen jd rusak
                $HHHH->persendiscount = (float)$input['head']['persendiscount'];
                $HHHH->factorrate = (float)$input['head']['factorrate'];
                $HHHH->qtycurrentstok = (float)$input['head']['qtycurrentstok'];
                //                if(isset($input['head']['tglberlakuakhir']) && $input['head']['tglberlakuakhir']!="" && $input['head']['tglberlakuakhir']!="undefined"){
                //                    $HHHH->tglberlakuakhir = $input['head']['tglberlakuakhir'];
                //                }
                //                if(isset($input['head']['tglberlakuawal']) && $input['head']['tglberlakuawal']!="" && $input['head']['tglberlakuawal']!="undefined"){
                //                    $HHHH->tglberlakuawal = $input['head']['tglberlakuawal'];
                //                }
                //                if(isset($input['head']['tglkadaluarsalast']) && $input['head']['tglkadaluarsalast']!="" && $input['head']['tglkadaluarsalast']!="undefined"){
                //                    $HHHH->tglkadaluarsalast = $input['head']['tglkadaluarsalast'];
                //                }
                $HHHH->reportdisplay = $input['head']['reportdisplay'];
                $HHHH->kodeexternal = $input['head']['kodeexternal'];
                $HHHH->namaexternal = $input['head']['namaexternal'];
                //                $transStatus = false;
                //                $HHHH->save();

                try {
                    $HHHH->save();
                } catch (\Exception $e) {
                    $transStatus = false;
                    $transMsg = "Update HargaNettoProdukByKelas Gagal";
                    //return $this->respond($HD);
                    //break;
                }
            }

        }//end jenis update
        if ($input['jenis'] == 'delete'){
            if ($input['detail']['idDetail'] != '') {
                try {
                    $hhhh_D = HargaNettoProdukByKelasD::where('id', $input['detail']['idDetail'])->where('kdprofile', $kdProfile)->delete();
                } catch (\Exception $e) {
                    $transStatus = false;
                    $transMsg = "Delete HargaNettoProdukByKelasD Gagal";
                    //break;
                }
            }


            try{
                $HD = HargaNettoProdukByKelasD::where('objectprodukfk',$input['head']['objectprodukfk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('objectjenistariffk',$input['head']['objectjenistariffk'])
                    ->where('objectasalprodukfk',$input['head']['objectasalprodukfk'])
                    ->where('objectkelasfk',$input['head']['objectkelasfk'])
                    ->where('objectmatauangfk',$input['head']['objectmatauangfk'])->get();
            }
            catch(\Exception $e){
                $transStatus = false;
                $transMsg = "Get sum HargaNettoProdukByKelasD Gagal";
                //break;
            }

            if ($transStatus) {
                $h1 = 0;
                $h2 = 0;
                $h3 = 0;
                $h4 = 0;
                foreach ($HD as $item) {
                    $h1 = $h1 + $item->hargadiscount;
                    $h2 = $h2 + $item->harganetto1;
                    $h3 = $h3 + $item->harganetto2;
                    $h4 = $h4 + $item->hargasatuan;
                }

                $HHHH = HargaNettoProdukByKelas1::where('id', $input['head']['idHead'])->where('kdprofile', $kdProfile)->first();
                $HHHH->hargadiscount = (float)$h1;
                $HHHH->harganetto1 = (float)$h2;
                $HHHH->harganetto2 = (float)$h3;
                $HHHH->hargasatuan = (float)$h4;
                //klo di update relasi ke komponen jd rusak
                //                $HHHH->objectjenistariffk = $input['head']['objectjenistariffk'];
                //                $HHHH->objectasalprodukfk = $input['head']['objectasalprodukfk'];
                //                $HHHH->objectkelasfk = $input['head']['objectkelasfk'];
                //                $HHHH->objectmatauangfk = $input['head']['objectmatauangfk'];
                //end#### klo di update relasi ke komponen jd rusak
                $HHHH->persendiscount = (float)$input['head']['persendiscount'];
                $HHHH->factorrate = (float)$input['head']['factorrate'];
                $HHHH->qtycurrentstok = (float)$input['head']['qtycurrentstok'];
                //                if(isset($input['head']['tglberlakuakhir']) && $input['head']['tglberlakuakhir']!="" && $input['head']['tglberlakuakhir']!="undefined"){
                //                    $HHHH->tglberlakuakhir = $input['head']['tglberlakuakhir'];
                //                }
                //                if(isset($input['head']['tglberlakuawal']) && $input['head']['tglberlakuawal']!="" && $input['head']['tglberlakuawal']!="undefined"){
                //                    $HHHH->tglberlakuawal = $input['head']['tglberlakuawal'];
                //                }
                //                if(isset($input['head']['tglkadaluarsalast']) && $input['head']['tglkadaluarsalast']!="" && $input['head']['tglkadaluarsalast']!="undefined"){
                //                    $HHHH->tglkadaluarsalast = $input['head']['tglkadaluarsalast'];
                //                }
                $HHHH->reportdisplay = $input['head']['reportdisplay'];
                $HHHH->kodeexternal = $input['head']['kodeexternal'];
                $HHHH->namaexternal = $input['head']['namaexternal'];
                //                $transStatus = false;
                //                $HHHH->save();

                try {
                    $HHHH->save();
                } catch (\Exception $e) {
                    $transStatus = false;
                    $transMsg = "Update HargaNettoProdukByKelas Gagal";
                    //return $this->respond($HD);
                    //break;
                }
            }

        }//end jenis Delete
        if($transStatus){
            $this->setStatusCode(201);
            $transMsg = "Transaksi Berhasil";
            //DB::rollBack();
            DB::commit();
        }else{
            $this->setStatusCode(400);
            DB::rollBack();
        }
        return $this->respond(['id'=>$idIdAn], $transMsg);
    }

    public function getDataMapRuanganToProduk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('mapruangantoproduk_m as mp')
            ->JOIN('produk_m as pr','pr.id','=','mp.objectprodukfk')
            ->JOIN('ruangan_m as ru','ru.id','=','mp.objectruanganfk')
            ->join ('departemen_m as dpt','dpt.id','=','ru.objectdepartemenfk')
            ->select('mp.id','mp.norec','mp.kodeexternal','pr.id as idproduk','pr.namaproduk','ru.id as ruanganid','ru.namaruangan','mp.statusenabled','dpt.namadepartemen')
            ->where('mp.kdprofile', $kdProfile);

        if(isset($request['deptId']) && $request['deptId']!="" && $request['deptId']!="undefined"){
            $data = $data->where('dpt.id','=',$request['deptId']);
        }
        if(isset($request['kdproduk']) && $request['kdproduk']!="" && $request['kdproduk']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike', '%'.$request['kdproduk'].'%');
        }
        if(isset($request['ruangan']) && $request['ruangan']!="" && $request['ruangan']!="undefined"){
            $data = $data->where('ru.id','=', $request['ruangan']);
        }
        if(isset($request['isExsekutif']) && $request['isExsekutif']!="" && $request['isExsekutif']!="undefined"){
            $data = $data->where('pr.namaproduk','ilike', '%'.$request['isExsekutif']);
        }

        $data = $data->take(2000);
        $data = $data->orderBy('mp.id');
        $data = $data->get();

        $result = array(
            'datas' => $data,
            'message' => 'Cepot',
        );

        return $this->respond($result);
    }

    public function getProdukbyIdformap(Request $request){
        $ids = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('produk_m')
            ->where('kdprofile', $kdProfile)
            ->select('id','namaproduk');

        if(isset($ids['iddjenis']) && !empty($ids['iddjenis'])){
            $data =	$data->where('objectdetailjenisprodukfk',$ids['iddjenis']);
        }elseif (isset($ids['idruang']) && !empty($ids['idruang'])) {
            $ids_produk = DB::table('mapruangantoproduk_m')
                ->select('objectprodukfk')
                ->where('kdprofile',$kdProfile)
                ->where('objectruanganfk',$ids['idruang'])
                ->get();

            foreach ($ids_produk as $idproduk) {
                $idsproduk[] = (int)$idproduk->objectprodukfk;
            }

            if(!empty($idsproduk)){
                $data = $data->whereIn('id',$idsproduk);
            }else{
                $data = $data->where('id','=',intval(''));
            }

        }

        $data = $data->orderBy('namaproduk')
            ->get();

        return $this->respond($data);
    }

    public function tombolDisable (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data=$request['data'];
        \Illuminate\Support\Facades\DB::beginTransaction();
        try{
            #region savePegawaiFormPensiun
            foreach ($data as $item) {
                $disableProduk = MapRuanganToProduk::where('id', $item['id'])->where('kdprofile', $kdProfile)->first();
                $disableProduk->statusenabled = $item['statusenabled'];
                $disableProduk->save();
            }

            #endregion savePegawaiFormPensiun
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
                "dataproduk" => $disableProduk,
                "as" => 'ridwan',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "dataproduk" => $disableProduk,
                "as" => 'ridwan',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function DeleteMappingProdukToRuangan (Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data=$request['data'];
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {

            foreach ($data as $item) {
                $dataOP = MapRuanganToProduk::where('id', $item['id'])
                    ->where('kdprofile',$kdProfile)
                    ->delete();
            }

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan";
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

    public function getRuanganbyIddepartemen($id, Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::table('ruangan_m')
            ->select('id','namaruangan')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true)
            ->where('objectdepartemenfk','=',$id)
            ->get();

        return $this->respond($data);

    }

    public function getListProdukMap(Request $request,$limit=null){
        //get request
        $filter = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        //get data
        $data = \DB::table('produk_m as p')
            ->select('p.id','p.kdproduk','p.kdbarcode','p.deskripsiproduk','p.namaproduk','dj.detailjenisproduk','dp.namadepartemen','p.statusenabled')
            ->leftJoin('rm_sediaan_m as rm','rm.id','=','p.objectsediaanfk')
            ->leftJoin('merkproduk_m as mp','mp.id','=','objectmerkprodukfk')
            ->leftJoin('rekanan_m as rk','rk.id','=','p.objectrekananfk')
            ->leftJoin('golongandarah_m as gd','gd.id','=','p.golongandarahfk')
            ->leftJoin('status_barang_m as sb','sb.id','=','p.objectstatusbarangfk')
            ->leftJoin('rhesus_m as rh','rh.id','=','p.rhesusfk')
            ->leftJoin('rm_generik_m as rg','rg.id','=','p.objectgenerikfk')
            ->leftJoin('rm_detail_obat_m as rd','rd.id','=','p.objectdetailobatfk')
            ->leftJoin('bahansample_m as bs','bs.id','=','p.bahansamplefk')
            ->leftJoin('chartofaccount_m as ch','ch.id','=','p.objectaccountfk')
            ->leftJoin('bahanproduk_m as bh','bh.id','=','p.objectbahanprodukfk')
            ->leftJoin('bentukproduk_m as bp','bp.id','=','p.objectbentukprodukfk')
            ->leftJoin('departemen_m as dp','dp.id','=','p.objectdepartemenfk')
            ->leftJoin('detailgolonganproduk_m as dg','dg.id','=','p.objectdetailgolonganprodukfk')
            ->leftJoin('detailjenisproduk_m as dj','dj.id','=','p.objectdetailjenisprodukfk')
            ->where('p.kdprofile',$kdProfile);


        if(isset($filter['kdProduk']) && !empty($filter['kdProduk'])){
            $data = $data->where('p.id','=',$filter['kdProduk']);
        }elseif (isset($filter['kdInternal']) && !empty($filter['kdInternal'])) {
            $data = $data->where('p.kdproduk','=',$filter['kdInternal']);
        }elseif (isset($filter['kdBarcode']) && !empty($filter['kdBarcode'])) {
            $data = $data->where('p.kdbarcode','=',$filter['kdBarcode']);
        }elseif (isset($filter['kdBmn']) && !empty($filter['kdBmn'])) {
            $data = $data->where('p.kodebmn','=',$filter['kdBmn']);
        }elseif (isset($filter['nmProduk']) && !empty($filter['nmProduk'])) {
            $data = $data->where('p.namaproduk','ilike','%'.$filter['nmProduk'].'%');
        }
        if(isset($filter['dpt']) && !empty($filter['dpt'])) {
            $data = $data->where('p.objectdepartemenfk','=',$filter['dpt']);
        }
        if (isset($filter['stts']) && !empty($filter['stts'])) {
            $data = $data->where('p.statusenabled','=',$filter['stts']);
        }

        $data = $data->orderBy('p.namaproduk');
        $data = $data->take(500);
        if($limit !== null){
            $data = $data->limit($limit)->get();
        }else{
            $data = $data->get();
        }
        return $this->respond($data);
    }

    public function getkelompokproduk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['idKlmpokProduk'];
        $data = \DB::table('kelompokproduk_m')
            ->select('id','kelompokproduk')
            ->where('kdprofile', $kdProfile);
        if (isset($request['idKlmpokProduk']) && $request['idKlmpokProduk'] != "" && $request['idKlmpokProduk'] != "undefined") {
            $data = $data->where('id','=',$request['idKlmpokProduk']);
        }
        $data = $data->get();
        return $this->respond($data);
    }

    public function addProdukToRuangan(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $is_search=$request['is_search'];
        $data = $request['data'];
        $id = MapRuanganToProduk::max('id') +1;// $this->getMapRuanganToProdId();
        $cData = count($data);
        DB::beginTransaction();
        try{
            for ($i=0; $i < $cData; $i++) {

                if ($is_search == 0 || empty($is_search)) { // is_serach is false

                    $filterD = DB::table('mapruangantoproduk_m')
                        ->select('objectprodukfk as idproduk','objectruanganfk as idruangan')
                        ->where('objectruanganfk','=',(int)$data[$i]['idruangan'])
                        ->where('kdprofile', $kdProfile)
                        ->get();
                }

                $filter[] = DB::table('mapruangantoproduk_m')
                    ->select(DB::raw('count(*) as jml, id'))
                    ->where([
                        ['objectprodukfk','=',(int)$data[$i]['idproduk']],
                        ['objectruanganfk','=',(int)$data[$i]['idruangan']]
                    ])
                    ->where('kdprofile', $kdProfile)
                    ->groupBy('id')
                    ->get();

                //remove null array vlaue
                $newfilter = array_filter($filter);

                if ($is_search == 0 || empty($is_search)) { // is_serach is false

                    if(!empty($data) && !empty($filterD)){
                        //convert obj to array
                        $newfilterD = json_decode(json_encode($filterD), true);

                        // Compare all values by a json_encode
                        $diff = array_diff(array_map('json_encode', $newfilterD), array_map('json_encode', $data));

                        // Json decode the result
                        $rowDataforDelete = array_map('json_decode', $diff);

                        //reset index of array
                        $rowDataforDelete_ = array_values($rowDataforDelete);
                    }
                }

                if($filter[$i] == null){ //if not exist on table

                    $id = $id+$i;

                    $rowDataforInsert[] = array(
                        "id" => (int)$id,
                        "kdprofile" => $kdProfile,
                        "statusenabled" => (bool)"t",
                        "kodeexternal" => 2017,//$data[$i]['kodeexternal'],
                        "namaexternal" => "tes",
                        "norec" => substr(Uuid::generate(), 0, 32),
                        "reportdisplay" => "tes",
                        "objectprodukfk" => (int)$data[$i]['idproduk'],
                        "objectruanganfk" => (int)$data[$i]['idruangan'],
                        "status" => ""

                    );
                }elseif ($newfilter[$i][0]->jml == 1) { //if exist on table
//                    return 'a';

                    $rowDataforUpdate[] = array(
                        "id" => (int)$newfilter[$i][0]->id,
                        "statusenabled" => (bool)"t",
                        "kodeexternal" =>2017,// $data[$i]['kodeexternal'],
                        "namaexternal" => "tes update",
                        "reportdisplay" => "tes update",
                        "objectprodukfk" => (int)$data[$i]['idproduk'],
                        "objectruanganfk" => (int)$data[$i]['idruangan'],
                        "status" => ""
                    );
                }

            }

            if ($is_search == 0 || empty($is_search)) {// is_serach is false

                if (!empty($rowDataforDelete_)) {
                    for ($i=0; $i < count($rowDataforDelete_); $i++) {

                        //rename property name
                        $rowDataforDelete_[$i]->objectprodukfk = $rowDataforDelete_[$i]->idproduk;
                        unset($rowDataforDelete_[$i]->idproduk);

                        $rowDataforDelete_[$i]->objectruanganfk = $rowDataforDelete_[$i]->idruangan;
                        unset($rowDataforDelete_[$i]->idruangan);

                        //convert obj to array
                        $rowDataforDeleted_ = json_encode($rowDataforDelete_);
                        $data = json_decode($rowDataforDeleted_,true);


                        $is_delete[] = DB::table('mapruangantoproduk_m')
                            ->where([
                                ['objectprodukfk','=',(int)$data[$i]['objectprodukfk']],
                                ['objectruanganfk','=',(int)$data[$i]['objectruanganfk']]
                            ])
                            ->delete();
                    }
                }
            }


            if(!empty($rowDataforInsert)){
                //insert all data in array
                $is_saved = DB::table('mapruangantoproduk_m')
                    ->insert($rowDataforInsert);

            }

            if (!empty($rowDataforUpdate)) {

                for ($i=0; $i < count($rowDataforUpdate); $i++) {
                    $is_update = DB::table('mapruangantoproduk_m')
                        ->where('id',$rowDataforUpdate[$i]['id']);

                    unset($rowDataforUpdate[$i]['id']); //remove id, for not update id

                    $is_update	= $is_update->update($rowDataforUpdate[$i]);
                }

            }

//
//            if(isset($is_saved) || isset($is_update) || isset($is_delete)){
//                $message = "SUKSES";
//            }else{
//                $message = "GAGAL";
//            }
//
//            $result = array(
//                "data" => array(
//                    "message" => array(
//                        "label-success" => $message
//                    )
//                )
//            );
//
//            return $this->respond($result);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Gagal Berhasil";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                "data" => $rowDataforUpdate,
                "as" => 'epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                "data" => $rowDataforUpdate,
                "as" => 'epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveBed(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $input=$request->all();
        $transStatus = true;
        $transMsg =null;
        DB::beginTransaction();

        $TT = TempatTidur::where('id',$input['idtempattidur'])->where('kdprofile', $kdProfile)->first();
        $TT->kdprofile = $kdProfile;
        $TT->objectkamarfk = $input['idkamar'];
        $TT->objectstatusbedfk = $input['idstatusbed'];
        $TT->nomorbed = $input['nomorbed'];
        $TT->reportdisplay = $input['namabed'];
        try{
            $TT->save();
        }
        catch(\Exception $e){
            $transStatus = false;
            $transMsg = "Update Tempat Tidur Gagal";
            //break;
        }
        $kosong = 0;
        $isi = 0;
        $TTqtyStatus = $TT = TempatTidur::where('objectkamarfk',$input['idkamar'])->get();
        foreach ($TTqtyStatus as $item){
            if ($item->objectstatusbedfk == 1){
                $isi = $isi + 1;
            }
            if ($item->objectstatusbedfk == 2){
                $kosong = $kosong + 1;
            }
        }

        $KMR = Kamar::where('id',$input['idkamar'])->where('kdprofile', $kdProfile)->first();
        $KMR->objectruanganfk = $input['idruangan'];
        $KMR->jumlakamarisi = $isi;
        $KMR->jumlakamarkosong = $kosong;
        try{
            $KMR->save();
        }
        catch(\Exception $e){
            $transStatus = false;
            $transMsg = "Update Kamar Gagal";
            //break;
        }

        if($transStatus){
            $this->setStatusCode(201);
            $transMsg = "Transaksi Berhasil";
            //DB::rollBack();
            DB::commit();
        }else{
            $this->setStatusCode(400);
            DB::rollBack();
        }
        return $this->respond([], $transMsg);
    }

    public function getDaftarJenisDiet(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('jenisdiet_m as jd')
            ->leftjoin ('kelompokproduk_m as kp','kp.id','=','jd.objectkelompokprodukfk')
            ->select('jd.*','kp.kelompokproduk')
            ->where('kp.kdprofile', $kdProfile)
            ->where('jd.statusenabled', true);

        if(isset($request['id']) && $request['id']!="" && $request['id']!="undefined"){
            $data = $data->where('jd.id','=', $request['id']);
        }
        if(isset($request['kdJenis']) && $request['kdJenis']!="" && $request['kdJenis']!="undefined"){
            $data = $data->where('jd.kdjenisdiet','=', $request['kdJenis']);
        }
        if(isset($request['jenisDiet']) && $request['jenisDiet']!="" && $request['jenisDiet']!="undefined"){
            $data = $data->where('jd.jenisdiet','ILIKE', '%'.$request['jenisDiet'].'%');
        }
        if(isset($request['kelompokProdukId']) && $request['kelompokProdukId']!="" && $request['kelompokProdukId']!="undefined"){
            $data = $data->where('jd.objectkelompokprodukfk','=', $request['kelompokProdukId']);
        }
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }

    public function saveJenisDiet(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $idJenis = JenisDiet::max('id');
            $idJenis= $idJenis +1;
            if ($request['id'] == "") {

                $JD = new JenisDiet();
                $JD->norec = $JD->generateNewId();
                $JD->id = $idJenis;
                $JD->kdprofile = $kdProfile;
                $JD->statusenabled = true;
            }else{
                $JD = JenisDiet::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();

            }
            $JD->kodeexternal = $idJenis;
            $JD->namaexternal = $request['jenisdiet'];
            $JD->reportdisplay = $request['jenisdiet'];
            $JD->jenisdiet = $request['jenisdiet'];
            $JD->qjenisdiet = $idJenis;
            $JD->keterangan = $request['keterangan'];
            $JD->kdjenisdiet =  $request['kdjenisdiet'];
            $JD->objectkelompokprodukfk = $request['objectkelompokprodukfk'];
            $JD->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Jenis Diet";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteJenisDiet(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $JD = JenisDiet::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => false
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarJenisWaktu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('jeniswaktu_m as jw')
            ->leftjoin ('kelompokproduk_m as kp','kp.id','=','jw.objectkelompokprodukfk')
            ->leftjoin ('departemen_m as dp','dp.id','=','jw.objectdepartemenfk')
            ->select('jw.*','kp.kelompokproduk','dp.namadepartemen')
            ->where('jw.kdprofile', $kdProfile)
            ->where('jw.statusenabled', true);

        if(isset($request['id']) && $request['id']!="" && $request['id']!="undefined"){
            $data = $data->where('jw.id','=', $request['id']);
        }
        if(isset($request['kdJenis']) && $request['kdJenis']!="" && $request['kdJenis']!="undefined"){
            $data = $data->where('jw.kdjenisdiet','=', $request['kdJenis']);
        }
        if(isset($request['jenisWaktu']) && $request['jenisWaktu']!="" && $request['jenisWaktu']!="undefined"){
            $data = $data->where('jw.jeniswaktu','ILIKE', '%'.$request['jenisWaktu'].'%');
        }
        if(isset($request['kelompokProdukId']) && $request['kelompokProdukId']!="" && $request['kelompokProdukId']!="undefined"){
            $data = $data->where('jw.objectkelompokprodukfk','=', $request['kelompokProdukId']);
        }
        if(isset($request['departemenId']) && $request['departemenId']!="" && $request['departemenId']!="undefined"){
            $data = $data->where('jw.objectdepartemenfk','=', $request['departemenId']);
        }
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function saveJenisWaktu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $idJenis = JenisWaktu::max('id');
            $idJenis= $idJenis +1;
            if ($request['id'] == "") {
                $JD = new JenisWaktu();
                $JD->norec = $JD->generateNewId();
                $JD->id = $idJenis;
                $JD->kdprofile = $kdProfile;
                $JD->statusenabled = true;
            }else{
                $JD = JenisWaktu::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();

            }
            $JD->kodeexternal = $idJenis;
            $JD->namaexternal = $request['jeniswaktu'];
            $JD->reportdisplay = $request['jeniswaktu'];
            $JD->jeniswaktu = $request['jeniswaktu'];
            $JD->jamakhir = $request['jamakhir'];
            $JD->jamawal = $request['jamawal'];
            $JD->kdjeniswaktu =  $request['kdjeniswaktu'];
            $JD->objectkelompokprodukfk = $request['objectkelompokprodukfk'];
            $JD->objectdepartemenfk = $request['objectdepartemenfk'];
//            $JD->nourut = $request['objectkelompokprodukfk'];
            $JD->qjeniswaktu =$idJenis;
            $JD->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Jenis Waktu";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteJenisWaktu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $JD = JenisWaktu::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => false
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDepartemen(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['idDep'];
        $data = \DB::table('departemen_m')
            ->select('id','namadepartemen')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled',true);
        if (isset($request['idDep']) && $request['idDep'] != "" && $request['idDep'] != "undefined") {
            $data = $data->where('id','=',$request['idDep']);
        }
        $data = $data->get();
        return $this->respond($data);
    }
    public function getKategoryDiet(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('kategorydiet_m as kd')
            ->leftjoin ('kelompokproduk_m as kp','kp.id','=','kd.objectkelompokprodukfk')
            ->select('kd.*','kp.kelompokproduk')
            ->where('kd.kdprofile', $kdProfile)
            ->where('kd.statusenabled', true);

        if(isset($request['id']) && $request['id']!="" && $request['id']!="undefined"){
            $data = $data->where('kd.id','=', $request['id']);
        }
        if(isset($request['kdKat']) && $request['kdKat']!="" && $request['kdKat']!="undefined"){
            $data = $data->where('kd.kdkategorydiet','=', $request['kdKat']);
        }
        // if(isset($request['namaexternal']) && $request['namaexternal']!="" && $request['namaexternal']!="undefined"){
        //     $data = $data->where('kd.namaexternal','=', $request['namaexternal']);
        // }
        if(isset($request['kategoryDiet']) && $request['kategoryDiet']!="" && $request['kategoryDiet']!="undefined"){
            $data = $data->where('kd.kategorydiet','ILIKE', '%'.$request['kategoryDiet'].'%');
        }
        if(isset($request['kelompokProdukId']) && $request['kelompokProdukId']!="" && $request['kelompokProdukId']!="undefined"){
            $data = $data->where('jw.objectkelompokprodukfk','=', $request['kelompokProdukId']);
        }

        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'inhuman',
        );
        return $this->respond($result);
    }
    public function saveKategoryDiet(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $idJenis = KategoryDiet::max('id');
            $idJenis= $idJenis +1;
            if ($request['id'] == "") {
                $JD = new KategoryDiet();
                $JD->norec = $JD->generateNewId();
                $JD->id = $idJenis;
                $JD->kdprofile = $kdProfile;
                $JD->statusenabled = true;
            }else{
                $JD = KategoryDiet::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
            }
            $JD->kodeexternal = $idJenis;
            $JD->namaexternal = $request['namaexternal'];
            $JD->reportdisplay = $request['kategorydiet'];
            $JD->kategorydiet = $request['kategorydiet'];
            $JD->kdkategorydiet =$JD->kodeexternal;
            $JD->objectkelompokprodukfk = $request['objectkelompokprodukfk'];
            $JD->qkategorydiet =$JD->kodeexternal;
            $JD->save();
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Kategory Diet";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteKategoryDiet(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $JD = KategoryDiet::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => false
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getComboSiklus(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataJenisDiet = \DB::table('jenisdiet_m as jd')
            ->select('jd.id', 'jd.jenisdiet')
            ->where('jd.kdprofile', $kdProfile)
            ->where('jd.statusenabled', true)
            ->get();

        $dataJenisWaktu = \DB::table('jeniswaktu_m as jw')
            ->select('jw.id', 'jw.jeniswaktu')
            ->where('jw.kdprofile', $kdProfile)
            ->where('jw.statusenabled', true)
            ->get();

        $dataKategoryDiet = \DB::table('kategorydiet_m as kd')
            ->select('kd.id', 'kd.kategorydiet')
            ->where('kd.kdprofile', $kdProfile)
            ->where('kd.statusenabled', true)
            ->get();
        $bentuk = \DB::table('bentukproduk_m as kd')
            ->select('kd.id', 'kd.namabentukproduk')
            ->where('kd.kdprofile', $kdProfile)
            ->where('kd.statusenabled', true)
            ->get();

        $dataKelas = \DB::table('kelas_m as kls')
            ->select('kls.id','kls.namakelas')
            ->where('kls.kdprofile', $kdProfile)
            ->where('kls.statusenabled',true)
            ->get();
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->JOIN('kelompokproduk_m as kp','kp.id','=','jp.objectkelompokprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
//            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.kdprofile', $kdProfile)
            ->where('pr.statusenabled',true)
            ->where('kp.id',(int)$this->settingDataFixed('kdKelasNonKelasRegistrasi',  $kdProfile))
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();
        $result = array(
            'jenisdiet' => $dataJenisDiet,
            'jeniswaktu' => $dataJenisWaktu,
            'kategorydiet' => $dataKategoryDiet,
            'bentukproduk' => $bentuk,
            'kelas' => $dataKelas,
            'produk' => $dataProduk,
            'message' => 'er@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarSiklusGizi (Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $siklusKe = $request['siklusKe'];
        $kelasId = $request['kelasId'];
        $jenisDietId = $request['jenisDietId'];
        $kategoryDietId = $request['kategoryDiet'];
        $jenisWaktuId = $request['jenisWaktuId'];
        $namaProduk = $request['namaProduk'];
        $row = $request['jmlRow'];
        $arrJenisWaktu = explode(',',$jenisWaktuId);

//        return $this->respond($arrJenisWaktu);
        $data = DB::table('siklusgizi_m as sm')
            ->join ('produk_m as prd','prd.id','=','sm.objectprodukfk')
            ->join ('jeniswaktu_m as jw','jw.id','=','sm.objectjeniswaktufk')
            ->join ('jenisdiet_m as jd','jd.id','=','sm.objectjenisdietfk')
            ->join ('kelas_m as kls','kls.id','=','sm.objectkelasfk')
            ->join ('kategorydiet_m as kd','kd.id','=','sm.objectkategoryprodukfk')
            ->leftjoin ('bentukproduk_m as bp','bp.id','=','sm.objectbentukprodukfk')
            ->select('sm.id','prd.id as objectprodukfk', 'prd.namaproduk',
                'sm.sikluske','sm.objectjeniswaktufk','jw.jeniswaktu','sm.objectjenisdietfk','jd.jenisdiet' ,
                'sm.objectkelasfk','kls.namakelas','kd.kategorydiet','bp.namabentukproduk')
            ->where('sm.kdprofile', $kdProfile)
            ->where('sm.statusenabled',true);

        if(isset($siklusKe) && $siklusKe!=''){
            $data = $data->where('sm.sikluske',$siklusKe);
        }
        if(isset($kelasId) && $kelasId!=''){
            $data = $data->where('kls.id',$kelasId);
        }
        if(isset($jenisDietId) && $jenisDietId!=''){
            $data = $data->where('jd.id',$jenisDietId);
        }
        if(isset($kategoryDietId) && $kategoryDietId!=''){
            $data = $data->where('kd.id',$kategoryDietId);
        }
//        if(isset($jenisWaktuId) && $jenisWaktuId!=''){
//            $data = $data->where('jw.id',$jenisWaktuId);
//        }
        if(isset($jenisWaktuId) && $jenisWaktuId!=''){
            $data = $data->whereIn('jw.id',$arrJenisWaktu);
        }
        if(isset($namaProduk) && $namaProduk!=''){
            $data = $data->where('prd.namaproduk','ilike','%'.$namaProduk.'%');
        }

        if(isset($row) && $row !=''){
            $data = $data->limit($row);
        }
        $data = $data->get();
        $result = array(
            'data'=>$data
        );
        return $this->respond($result);
    }
    public function saveSiklusGizi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            foreach ( $request['details'] as $item){
                $kode[] = (double) $item['produkfk'];
                $kelasfk[] = (double) $item['kelasfk'];
            }

            $hapus = SiklusGizi::where('statusenabled',true)
                ->where('kdprofile', $kdProfile)
                ->where('sikluske',$request['sikluske'])
                ->where('objectjenisdietfk',$request['objectjenisdietfk'])
                ->whereIn('objectkelasfk',$kelasfk)
                ->where('objectjeniswaktufk',$request['objectjeniswaktufk'])
                ->whereIn('objectprodukfk',$kode)
                ->where('objectkategoryprodukfk',$request['objectkategoryprodukfk'])
                ->where('objectbentukprodukfk',$request['objectbentukprodukfk'])
                ->delete();

            foreach ($request['details'] as $item ){
                $SG = new SiklusGizi();
                $SG->id =  SiklusGizi::max('id')+1;
                $SG->kdprofile = $kdProfile;
                $SG->statusenabled = true;
                $SG->kodeexternal = 'GIZI';
                $SG->norec =  $SG->generateNewId();
                $SG->sikluske = $request['sikluske'];
                $SG->objectjeniswaktufk = $request['objectjeniswaktufk'];
                $SG->objectjenisdietfk =  $request['objectjenisdietfk'];
                $SG->objectkelasfk =$item['kelasfk'];
                $SG->objectprodukfk =  $item['produkfk'];
                $SG->objectbentukprodukfk = $request['objectbentukprodukfk'];
                $SG->objectkategoryprodukfk = $request['objectkategoryprodukfk'];
//                $SG->namaexternal =  $item[''];
//                $SG->reportdisplay =  $item[''];
                $SG->save();
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "siklus" => $SG,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function deleteSiklusGizi(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $hapus = SiklusGizi::where('id',$request['id'])
                ->where('kdprofile',$kdProfile)
                ->update(
                    ['statusenabled' => false]
                );

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getMapRuanganToProdId(){
        $maxId = DB::table('mapruangantoproduk_m')->find(DB::table('mapruangantoproduk_m')->max('id'));
        $nextId = $maxId->id+1;
        return $nextId;
    }
    public function saveharganettoprodukbykelasM_D(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
//        return $kdProfile;
        $idIdAn = 0;
        $idIdAnD = 0;
        $input=$request->all();
        //return $this->respond(array($input['head']['idHead']));
        $transStatus = true;
        $transMsg =null;
        DB::beginTransaction();

        if ($input['jenis'] == 'simpan'){
            try {
            $h1 = 0;
            $h2 = 0;
            $h3 = 0;
            $h4 = 0;
            if ($input['head']['id_hn_m']=='') {
                $newId = HargaNettoProdukByKelas1::max('id');
                $newId = $newId + 1;
                $HHHH = new HargaNettoProdukByKelas1();
                $HHHH->id = $newId;
                $idIdAn=$newId;
            }else{
                $HHHH =  HargaNettoProdukByKelas1::where('id',$input['head']['id_hn_m'])->first();
                $del = HargaNettoProdukByKelasD::where('objectprodukfk',$input['head']['objectprodukfk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('objectjenistariffk',$input['head']['objectjenistariffk'])
                    ->where('objectasalprodukfk',$input['head']['objectasalprodukfk'])
                    ->where('objectkelasfk',$input['head']['objectkelasfk'])
                    ->where('objectjenispelayananfk',$input['head']['objectjenispelayananfk'])
                    ->delete();

            }
            $HHHH->hargadiscount = (float)$input['head']['hargadiscount'];;
            $HHHH->harganetto1 = (float)$input['head']['harganetto1'];
            $HHHH->harganetto2 = (float)$input['head']['harganetto2'];
            $HHHH->hargasatuan = (float)$input['head']['hargasatuan'];
            $HHHH->objectprodukfk = $input['head']['objectprodukfk'];
            $HHHH->objectjenistariffk = $input['head']['objectjenistariffk'];
            $HHHH->objectasalprodukfk = $input['head']['objectasalprodukfk'];
            $HHHH->objectkelasfk = $input['head']['objectkelasfk'];
            $HHHH->objectmatauangfk = $input['head']['objectmatauangfk'];
            $HHHH->persendiscount = (float)$input['head']['persendiscount'];
            $HHHH->factorrate = (float)$input['head']['factorrate'];
            $HHHH->qtycurrentstok = (float)$input['head']['qtycurrentstok'];
            if(isset($input['head']['tglberlakuakhir']) && $input['head']['tglberlakuakhir']!="" && $input['head']['tglberlakuakhir']!="undefined"){
                $HHHH->tglberlakuakhir = $input['head']['tglberlakuakhir'];
            }
            if(isset($input['head']['tglberlakuawal']) && $input['head']['tglberlakuawal']!="" && $input['head']['tglberlakuawal']!="undefined"){
                $HHHH->tglberlakuawal = $input['head']['tglberlakuawal'];
            }
            if(isset($input['head']['tglkadaluarsalast']) && $input['head']['tglkadaluarsalast']!="" && $input['head']['tglkadaluarsalast']!="undefined"){
                $HHHH->tglkadaluarsalast = $input['head']['tglkadaluarsalast'];
            }
            $HHHH->reportdisplay = $input['head']['reportdisplay'];
//                if($input['head']['kodeexternal']== ''){
//	                $input['head']['kodeexternal'] =2017;
//                }
            $HHHH->kdprofile = $kdProfile;
            $HHHH->kodeexternal = date('Y');
            $HHHH->namaexternal = $input['head']['namaexternal'];
            $HHHH->statusenabled = true;
            $HHHH->suratkeputusanfk = $input['head']['suratkeputusanfk'];;
            $HHHH->objectjenispelayananfk = $input['head']['objectjenispelayananfk'];
//            try {
                $HHHH->save();
//            } catch (\Exception $e) {
//                $transStatus = false;
//                $transMsg = "Simpan New HargaNettoProdukByKelas Gagal";
//            }

            $r_Detail =$input['detail'];
            foreach ($r_Detail as $r_Detail)
            {

//                if ($r_Detail['id_hn_d']=='' || $r_Detail['id_hn_d']=='undefined') {
                $newIdD = HargaNettoProdukByKelasD::max('id');
                $newIdD = $newIdD + 1;

                $DDDD = new HargaNettoProdukByKelasD();
                $DDDD->id = $newIdD;
                $idIdAnD=$newIdD;
//
//                }else{
//                    $DDDD =  HargaNettoProdukByKelasD::where('id',$r_Detail['id_hn_d'])->first();
//
//                }
                $DDDD->kdprofile = $kdProfile;
                $DDDD->factorrate = (float)$input['head']['factorrate'];
                $DDDD->objectkomponenhargafk = $r_Detail['objectkomponenhargafk'];
                $DDDD->hargadiscount = (float)$r_Detail['hargadiscount'];;
                $DDDD->harganetto1 = (float)$r_Detail['hargasatuan'];
                $DDDD->harganetto2 = (float)$r_Detail['hargasatuan'];
                $DDDD->hargasatuan = (float)$r_Detail['hargasatuan'];
                $DDDD->objectprodukfk = $input['head']['objectprodukfk'];
                $DDDD->objectjenistariffk = $input['head']['objectjenistariffk'];
                $DDDD->objectasalprodukfk = $input['head']['objectasalprodukfk'];
                $DDDD->objectkelasfk = $input['head']['objectkelasfk'];
                $DDDD->objectmatauangfk = $input['head']['objectmatauangfk'];
                $DDDD->persendiscount = (float)$r_Detail['persendiscount'];

                $DDDD->qtycurrentstok = (float)$input['head']['qtycurrentstok'];
                if(isset($input['head']['tglberlakuakhir']) && $input['head']['tglberlakuakhir']!="" && $input['head']['tglberlakuakhir']!="undefined"){
                    $DDDD->tglberlakuakhir = $input['head']['tglberlakuakhir'];
                }
                if(isset($input['head']['tglberlakuawal']) && $input['head']['tglberlakuawal']!="" && $input['head']['tglberlakuawal']!="undefined"){
                    $DDDD->tglberlakuawal = $input['head']['tglberlakuawal'];
                }
                if(isset($input['head']['tglkadaluarsalast']) && $input['head']['tglkadaluarsalast']!="" && $input['head']['tglkadaluarsalast']!="undefined"){
                    $DDDD->tglkadaluarsalast = $input['head']['tglkadaluarsalast'];
                }
                $DDDD->reportdisplay = $input['head']['reportdisplay'];
                $DDDD->kodeexternal = $input['head']['kodeexternal'];
                $DDDD->namaexternal = $input['head']['namaexternal'];
                $DDDD->statusenabled = true;
                $DDDD->objectjenispelayananfk = $input['head']['objectjenispelayananfk'];
//                try {
                    $DDDD->save();
//                } catch (\Exception $e) {
//                    $transStatus = false;
//                    $transMsg = "Simpan New HargaNettoProdukByKelas_D Gagal";
//                }
             }
                $transStatus = true;
            } catch (\Exception $e) {
                $transStatus = false;
            }
        }
        //end simpan head

        if($transStatus){
            $this->setStatusCode(201);
            $transMsg = "Simpan Harga Netto Produk Berhasil";
            //DB::rollBack();
            DB::commit();
        }else{
            $this->setStatusCode(400);
            $transMsg = "Simpan New HargaNettoProdukByKelas Gagal";
            DB::rollBack();
        }
        return $this->respond(['id_harganettobykelas_m'=>$idIdAn], $transMsg);
    }

    public function getListPaket(Request $request,$limit=null){
        //get request
        $kdProfile = (int) $this->getDataKdProfile($request);
        $filter = $request->all();
        $namapaket = $request['nmProduk'];
        $idpaket = $request['kdProduk'];
        //get data
        $data = \DB::table('paket_m as p')
            ->select('p.namapaket','p.id','p.statusenabled')
            ->where('p.kdprofile', $kdProfile)
            ->orderBy('p.id');

        if(isset($namapaket) && $namapaket!=''){
            $data = $data->where('p.namapaket','ilike','%'.$namapaket.'%');
        }
        if(isset($idpaket) && $idpaket!=''){
            $data = $data->where('p.id',$idpaket);
        }

        $data = $data->get();

        return $this->respond($data);
    }

    public function getPaketbyId(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $id = $request['idProduk'];
        $data = \DB::table('paket_m as p')
            ->where('p.id','=',$id)
            ->where('p.kdprofile', $kdProfile)
            ->get();

        return $this->respond($data);
    }

    public function saveDataPaket(Request $request){
        DB::beginTransaction();
        $data = $request->all();
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($data['id'] == null || $data['id'] == ''){
                $newId = Paket::max('id');
                $newId = $newId + 1;
                $prod = new Paket();
                $prod->id = $newId;
                $prod->kdprofile = $kdProfile;
            }else{
                $prod =  Paket::where('id',$data['id'])->where('kdprofile', $kdProfile)->first();
            }
            $prod->statusenabled =  $data['statusenabled'];
            $prod->norec = $prod->id;
            $prod->namapaket = $data['namapaket'];
            $prod->save();

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
                "data" => $data,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $data,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function UpdateStatusEnabledPaket(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        $data=$request['data'];
        try {
                $dataOP = Paket::where('id', $request['id'])
                    ->where('kdprofile',$kdProfile)
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

    public function getDaftarPelayananMutu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pelayananmutu_m as jd')
            ->select('jd.*')
            ->where('jd.kdprofile', $kdProfile);
//            ->where('jd.statusenabled', true);

        if(isset($request['id']) && $request['id']!="" && $request['id']!="undefined"){
            $data = $data->where('jd.id','=', $request['id']);
        }
        if(isset($request['kdPelayananMutu']) && $request['kdPelayananMutu']!="" && $request['kdPelayananMutu']!="undefined"){
            $data = $data->where('jd.kdpelmutu','=', $request['kdPelayananMutu']);
        }
        if(isset($request['PelayananMutu']) && $request['PelayananMutu']!="" && $request['PelayananMutu']!="undefined"){
            $data = $data->where('jd.pelayananmutu','ILIKE', '%'.$request['PelayananMutu'].'%');
        }
        $data = $data->get();
        $result = array(
            'data'=> $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }

    public function savePelayananMutu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $idJenis = PelayananMutu::max('id');
            $idJenis= $idJenis +1;
            if ($request['id'] == "") {
                $JD = new PelayananMutu();
                $JD->norec = $JD->generateNewId();
                $JD->id = $idJenis;
                $JD->kdprofile = $kdProfile;
                $JD->statusenabled = true;
            }else{
                $JD = PelayananMutu::where('id',$request['id'])->where('kdprofile', $kdProfile)->first();
            }
            $JD->kodeexternal = $idJenis;
            $JD->namaexternal = $request['pelayananmutu'];
            $JD->reportdisplay = $request['pelayananmutu'];
            $JD->pelayananmutu = $request['pelayananmutu'];
            $JD->qpelmutu = $idJenis;
            $JD->kdpelmutu =  $idJenis;
            $JD->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Jenis Diet";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deletePelayananMutu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $JD = PelayananMutu::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => false
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function aktifPelayananMutu(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            $JD = PelayananMutu::where('id',$request['id'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'statusenabled' => true
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "jenisdiet" => $JD,
                "as" => 'inhuman',
            );

        } else {
            $transMessage = "Hapus gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'inhuman',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getMasterKiosk(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
    
        $kategori = DB::table('informasikiosk_m')
            ->select('id','jenis')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled', true)
            ->get();

        $KdDepartemenRawatJalan = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $kdProfile);
            $ruanganRajal = Ruangan::where('statusenabled',true)
            ->select('id','namaruangan','objectdepartemenfk')
            ->where('kdprofile', $kdProfile)
            ->where('objectdepartemenfk',$KdDepartemenRawatJalan)
            ->orderBy('namaruangan')
            ->get();

        $data = array(
           
            "ruanganrajal" => $ruanganRajal,
            "jenis" => $kategori,
            "as" => "ea@epic"
        );
        return $this->respond($data);
    }
    public function saveSettingKiosk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            if($request['norec'] == ''){
                  $insert[] = array(
                        "norec" => substr(Uuid::generate(), 0, 32),
                        "kdprofile" => $kdProfile,
                        "statusenabled" => true,
                        "informasifk" =>$request['informasifk'],
                        "deskripsi" =>$request['deskripsi']
                     );
                 DB::table('settingkiosk_t')->insert($insert);
             }else{
                  $insert = array(
                        "informasifk" =>$request['informasifk'],
                        "deskripsi" =>$request['deskripsi']
                     );
                   DB::table('settingkiosk_t')->where('norec',$request['norec'])->update($insert);
             }
          
          
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => '',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                // "e" =>$e,
                "as" => '',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
      public function deleteSettingKiosk(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        DB::beginTransaction();
        try{
            DB::table('settingkiosk_t')->where('norec',$request['norec'])->update(
                [ 'statusenabled' =>false
                ]);
          
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => '',
            );

        } else {
            $transMessage = "Simpan gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => '',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
     public function getSettingKios(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
    
        $data = DB::table('settingkiosk_t as ss')
            ->join('informasikiosk_m as ii','ii.id','=','ss.informasifk')
            ->select('ss.*','ii.jenis')
            ->where('ss.kdprofile', $kdProfile)
            ->where('ss.statusenabled', true)
            ->get();

       
        $data = array(
            "data" => $data,
            "as" => "er@epic"
        );
        return $this->respond($data);
    }

}