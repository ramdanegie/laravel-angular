<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 12/6/2017
 * Time: 09:40
 */

namespace App\Http\Controllers\Farmasi;
use App\Http\Controllers\ApiController;
use App\Transaksi\LoggingUser;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

use App\Master\ProdukFormulaProduksi;
use App\Master\Pegawai;
use App\Transaksi\ProduksiNonSteril;
use App\Transaksi\StrukPelayanan;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\StrukPelayananDetail;
use App\Transaksi\KartuStok;

class ProduksiBarangController  extends ApiController {

    use Valet;
    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function saveMasterBarangProduksi(Request $request) {
        DB::beginTransaction();
        $transMessage='';
        $req = $request;
        $kdProfile = (int)$this->getDataKdProfile($request);
        $data = ProdukFormulaProduksi::where('objectprodukhasilfk',$request['produkhasilfk'])
            ->where('kdprofile', $kdProfile)
            ->delete();
        $qtyJumlah = 0.0;
        foreach ($req['details'] as $item) {
            $qtyJumlah = (float)$item['jumlah']*(float)$item['nilaikonversi'];
            $newID = ProdukFormulaProduksi::max('id');
            $newID = $newID + 1;

//            $noStruk = $this->generateCode(new ProdukFormulaProduksi, 'kodeexternal', 13, 'PR/'.$this->getDateTime()->format('ym/'));

            $PFP = new ProdukFormulaProduksi();
            $PFP->id = $newID;
            $PFP->kdprofile = $kdProfile;
            $PFP->statusenabled = true;
            $PFP->norec = $PFP->generateNewId();

            $PFP->objectprodukasalfk = $item['produkfk'];
            $PFP->objectprodukhasilfk = $request['produkhasilfk'];
            $PFP->formulaproduksi = $request['keterangan'];
            $PFP->keteranganlainnya = $request['keterangan'];
            $PFP->qtyprodukasal = $qtyJumlah;
            $PFP->satuanprodukasal = $item['satuanviewfk'];
            $PFP->nilaikonversi = $item['nilaikonversi'];
            $PFP->qtyhasil = $request['qtyhasil'];

            try {
                $PFP->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
            }

        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Berhasil!";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $PFP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "data" => $PFP,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getDaftarBarangProduksi(Request $request) {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('produkformulaproduksi_m as pfp')
            ->leftJOIN('produk_m as pr1','pr1.id','=','pfp.objectprodukhasilfk')
            ->leftJOIN('produk_m as pr2','pr2.id','=','pfp.objectprodukasalfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pfp.satuanprodukasal')
            ->select('pfp.id','pr1.id as idprodukhasil','pr1.namaproduk as namaprodukhasil',
                'pr2.id as idprodukasal','pr2.namaproduk as namaprodukasal','ss.id as ssid','ss.satuanstandar as satuanview',
                'pfp.qtyprodukasal','pfp.nilaikonversi','pfp.keteranganlainnya','pfp.id','pfp.norec','pfp.qtyhasil');

        if(isset($request['namaprodukhasil']) && $request['namaprodukhasil']!="" && $request['namaprodukhasil']!="undefined"){
            $data = $data->where('pr1.namaproduk','ilike','%'. $request['namaprodukhasil']);
        }
        if(isset($request['pridhasil']) && $request['pridhasil']!="" && $request['pridhasil']!="undefined"){
            $data = $data->where('pr1.id', $request['pridhasil']);
        }
        if(isset($request['dpid']) && $request['dpid']!="" && $request['dpid']!="undefined"){
            $data = $data->where('pr1.namaproduk','ilike','%'. $request['dpid'].'%');
        }
        $data = $data->where('pfp.statusenabled',true);
        $data = $data->take(50);
        $data = $data->get();

        $result=[];
        $stt=false;
        $head=[];
        foreach ($data as $item) {
            foreach ($head as $rrsl) {
                if ($item->idprodukhasil == $rrsl['idprodukhasil']) {
                    $stt = true;
                    break;
                } else {
                    $stt = false;
                }
            }
            if ($stt == false) {
                $head[] = array('idprodukhasil' => $item->idprodukhasil,
                    'namaprodukhasil' => $item->namaprodukhasil,
                    'qtyhasil' => $item->qtyhasil,
                    'keteranganlainnya' => $item->keteranganlainnya,
                    'details' => [],
                );
            }
        }
        $results=[];
        foreach ($head as $item2){
            $result=[];
            foreach ($data as $item) {
                if ($item->idprodukhasil == $item2['idprodukhasil']){
                    $result[]=array(
                        'idprodukasal' => $item->idprodukasal,
                        'namaprodukasal' => $item->namaprodukasal,
                        'ssid' => $item->ssid,
                        'satuanview' => $item->satuanview,
                        'qtyprodukasal' => (float)$item->qtyprodukasal*(float)$item->nilaikonversi,
                        'nilaikonversi' => $item->nilaikonversi,
                        'id' => $item->id,
                        'norec' => $item->norec,
                    );
                }
            }
            $results[] = array(
//                'id' => $item2->id,
                'idprodukhasil' => $item2['idprodukhasil'],
                'namaprodukhasil' => $item2['namaprodukhasil'],
                'qtyhasil' => $item2['qtyhasil'],
                'keteranganlainnya' => $item2['keteranganlainnya'],
                'details' => $result,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getDetailMasterProduksi(Request $request) {
        $data = \DB::table('produkformulaproduksi_m as pfp')
            ->leftJOIN('produk_m as pr1','pr1.id','=','pfp.objectprodukhasilfk')
            ->leftJOIN('produk_m as pr2','pr2.id','=','pfp.objectprodukasalfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pfp.satuanprodukasal')
            ->leftJOIN('satuanstandar_m as ss2','ss2.id','=','pr1.objectsatuanstandarfk')
            ->select('pr1.id as idprodukhasil','pr1.namaproduk as namaprodukhasil',
                'pr2.id as idprodukasal','pr2.namaproduk as namaprodukasal','ss.id as ssid','ss.satuanstandar as satuanview',
                'pfp.qtyprodukasal','pfp.nilaikonversi','pfp.keteranganlainnya','pfp.id','pfp.norec','pfp.qtyhasil',
                'ss2.id as ssid2','ss2.satuanstandar as satuanview2');

        if(isset($request['namaprodukhasil']) && $request['namaprodukhasil']!="" && $request['namaprodukhasil']!="undefined"){
            $data = $data->where('pr1.namaproduk','ilike','%'. $request['namaprodukhasil']);
        }
        if(isset($request['pridhasil']) && $request['pridhasil']!="" && $request['pridhasil']!="undefined"){
            $data = $data->where('pr1.id', $request['pridhasil']);
        }
        $data = $data->where('pfp.statusenabled',true);
//        $data = $data->take(50);
        if(isset($request['idprodukhasil']) && $request['idprodukhasil']!="" && $request['idprodukhasil']!="undefined"){
            $data = $data->where('pr1.id','=', $request['idprodukhasil']);
        }
        $data = $data->get();

        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->select('ap.id','ap.asalproduk')
            ->get();

        $result=[];
        $stt=false;
        $head=[];
        foreach ($data as $item) {
            foreach ($head as $rrsl) {
                if ($item->idprodukhasil == $rrsl['idprodukhasil']) {
                    $stt = true;
                    break;
                } else {
                    $stt = false;
                }
            }
            if ($stt == false) {
                $head[] = array('idprodukhasil' => $item->idprodukhasil,
                    'namaprodukhasil' => $item->namaprodukhasil,
                    'qtyhasil' => $item->qtyhasil,
                    'keteranganlainnya' => $item->keteranganlainnya,
                    'ssid' => $item->ssid,
                    'satuanview' => $item->satuanview,
//                    'details' => [],
                );
            }
        }
        $results=[];
        foreach ($head as $item2){
            $result=[];
            foreach ($data as $item) {
                if ($item->idprodukhasil == $item2['idprodukhasil']){
                    $result[]=array(
                        'idprodukasal' => $item->idprodukasal,
                        'namaprodukasal' => $item->namaprodukasal,
                        'ssid' => $item->ssid,
                        'satuanview' => $item->satuanview,
                        'qtyprodukasal' => (float)$item->qtyprodukasal*(float)$item->nilaikonversi,
                        'nilaikonversi' => $item->nilaikonversi,
                        'id' => $item->id,
                        'norec' => $item->norec,
                    );
                }
            }
            $results[] = array('produkhasilfk' => $item2['idprodukhasil'],
                'namaprodukhasil' => $item2['namaprodukhasil'],
                'qtyhasil' => $item2['qtyhasil'],
                'keterangan' => $item2['keteranganlainnya'],
                'ssid' => $item->ssid,
                'satuanview' => $item->satuanview,
//                'details' => $result,
            );
        }
        $i=0;
        $dataStok = DB::select(DB::raw("select sk.norec, sk.tglstruk,spd.objectprodukfk, spd.objectasalprodukfk,
                    sum(spd.qtyproduk) as qtyproduk,spd.harganetto1
                    from stokprodukdetail_t as spd
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where  spd.objectruanganfk =:ruanganid and spd.qtyproduk >0 
                    group by sk.norec,sk.tglstruk,spd.objectprodukfk, spd.objectasalprodukfk,
                    spd.harganetto1
                    order By sk.tglstruk"),
            array(
                'ruanganid' => $request->ruid
            )
        );
        $hargajual=0;
        $harganetto=0;
        $nostrukterimafk='';
        $asalprodukfk=0;
        $asalproduk='';
        $jmlstok=0;
        $hargasatuan=0;
        $hargadiscount=0;
        $total=0;
        foreach ($result as $item){
            $i = $i+1;
            $jmlstok=0;
            $harganetto = 0.0;
            foreach ($dataStok as $item2){
                if ($item2->objectprodukfk == $item['idprodukasal']){
//                    $harganetto = 0.0;
                    if ((float)$item2->qtyproduk >= (float)$item['qtyprodukasal']*(float)$item['nilaikonversi']){

//                        $hargajual = $item2->harganetto1;
                        $harganetto = $item2->harganetto1;

                        $nostrukterimafk = $item2->norec;
                        $asalprodukfk = $item2->objectasalprodukfk;
                        $jmlstok = $jmlstok + (float)$item2->qtyproduk;
//                        $hargasatuan = $harganetto;//$item2->harganetto;
                    }
                }
            }
            foreach ($dataAsalProduk as $item3){
                if ($asalprodukfk == $item3->id){
                    $asalproduk = $item3->asalproduk;
                }
            }
            $formula[] = array(
                'no' => $i,#
                'hargajual' => 0,#
                'stock' => 0,#
                'harganetto' => $harganetto,#
                'nostrukterimafk' => $nostrukterimafk,#
                'ruanganfk' => null,#
                'asalprodukfk' => $asalprodukfk,#
                'asalproduk' => $asalproduk,#
                'produkfk' => $item['idprodukasal'],#
                'namaproduk' => $item['namaprodukasal'],#
                'nilaikonversi' => $item['nilaikonversi'],#
                'satuanstandarfk' => $item['ssid'],#
                'satuanstandar' => $item['satuanview'],#
                'satuanviewfk' => $item['ssid'],#
                'satuanview' => $item['satuanview'],#
                'jmlstok' => $jmlstok,#
                'jumlah' => $item['qtyprodukasal'],#
                'jumlahbahan' => 0,#
                'hargasatuan' => $harganetto,#
                'hargadiscount' => 0,#
                'total' => 0,#
            );
        }

        $result = array(
            'head' => $results,
            'child' => $result,
            'details' => $formula,
            'stok'=> $dataStok,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDataComboProduksi(Request $request) {
        $dataLogin = $request->all();
//        $dataPenulis = Pegawai::where('statusenabled',true)
//            ->whereNotIn('objectjenispegawaifk',[1])
//            ->get();
//        foreach ($dataPenulis as $item){
//            $dataPenulis2[]=array(
//                'id' => $item->id,
//                'namalengkap' => $item->namalengkap,
//            );
//        }
//        $dataSigna = \DB::table('stigma as st')
//            ->select('st.id','st.name')
//            ->orderBy('st.name')
//            ->get();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id','ru.namaruangan')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
//        $dataRuanganall = \DB::table('ruangan_m as ru')
//            ->select('ru.id','ru.namaruangan')
////            ->where('ru.objectdepartemenfk',14)
//            ->where('ru.statusenabled',true)
//            ->orderBy('ru.namaruangan')
//            ->get();

//        $dataJenisKemasan = \DB::table('jeniskemasan_m as jk')
//            ->select('jk.id','jk.jeniskemasan')
//            ->where('jk.statusenabled',true)
//            ->get();
//        $dataJenisRacikan = \DB::table('jenisracikan_m as jk')
//            ->select('jk.id','jk.jenisracikan')
//            ->where('jk.statusenabled',true)
//            ->get();

//        foreach ($dataRuangan as $lalala){
//            $ruFilter[]=array($lalala->id);
//        }
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
//            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->JOIN('produkformulaproduksi_m as pfp','pfp.objectprodukhasilfk','=','pr.id')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar','pfp.qtyhasil','pfp.keteranganlainnya')
            ->where('pr.statusenabled',true)
            ->where('jp.id',97)
//            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','ss.id','pfp.qtyhasil','pfp.keteranganlainnya','pr.namaproduk','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

//        $dataAsalProduk = \DB::table('asalproduk_m as ap')
//            ->JOIN('stokprodukdetail_t as spd','spd.objectasalprodukfk','=','ap.id')
//            ->select('ap.id','ap.asalproduk')
//            ->where('ap.statusenabled',true)
//            ->orderBy('ap.id')
//            ->groupBy('ap.id')
//            ->get();

//        $dataRoute = \DB::table('routefarmasi as rt')
//            ->select('rt.id','rt.name')
//            ->where('rt.statusenabled',true)
//            ->orderBy('rt.id')
//            ->get();
//        $dataKelas = \DB::table('kelas_m as rt')
//            ->select('rt.id','rt.namakelas')
//            ->where('rt.statusenabled',true)
//            ->orderBy('rt.id')
//            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
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
                'qtyhasil' =>   $item->qtyhasil,
                'keteranganlainnya' =>   $item->keteranganlainnya,
                'konversisatuan' => $satuanKonversi,
            );
        }

        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $result = array(
            'produk' => $dataProdukResult,
//            'penulisresep' =>   $dataPenulis2,
            'ruangan' => $dataRuangan,
//            'ruanganall' => $dataRuanganall,
//            'jeniskemasan' => $dataJenisKemasan,
//            'asalproduk' => $dataAsalProduk,
//            'route' => $dataRoute,
//            'kelas' => $dataKelas,
//            'signa' => $dataSigna,
//            'jenisracikan' => $dataJenisRacikan,
            'detaillogin' => $dataPegawaiUser,
            'message' => 'as@epic',
//            'asdas' => $ruFilter,
        );

        return $this->respond($result);
    }

    public function saveProduksiBarang(Request $request) {
        DB::beginTransaction();
        $ruanganAsal = DB::select(DB::raw("
                     select  ru.namaruangan
                     from ruangan_m as ru 
                    where ru.id=:id"),
            array(
                'id' => $request['struk']['objectruanganfk'],
            )
        );
        $strRuanganAsal =$ruanganAsal[0]->namaruangan;
        try{
            $noStruk = $this->generateCode(new ProduksiNonSteril, 'noproduksi', 14, 'FP-' . $this->getDateTime()->format('ym'));

            $dataPNS = new ProduksiNonSteril;
            $dataPNS->norec = $dataPNS->generateNewId();
            $dataPNS->kdprofile = 0;
            $dataPNS->statusenabled = true;
            $dataPNS->hargasatuan = $request['struk']['hargasatuan'];
            $dataPNS->jumlahproduksi = $request['struk']['jumlahproduksi'];
            $dataPNS->noproduksi = $noStruk;
//            $dataPNS->objectpegawaiygmemberikanfk = null;
//            $dataPNS->objectpegawaiygmemintafk = null;
            $dataPNS->objectpegawaiygmengetahuifk = $request['struk']['objectpegawaiygmengetahuifk'];
            $dataPNS->objectprodukfk = $request['struk']['objectprodukfk'];
            $dataPNS->satuan = $request['struk']['satuan'];
            $dataPNS->spesifikasi = $request['struk']['spesifikasi'];
            $dataPNS->tanggalexpired = $request['struk']['tanggalexpired'];
            $dataPNS->tglproduksi = date('Y-m-d H:i:s');
//            $dataPNS->unitcost = null;

            $dataPNS->save();
            $norecPNS = $dataPNS->norec;

            $SP = new StrukPelayanan();
            $norecSP = $SP->generateNewId();
            $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'FP/'.$this->getDateTime()->format('ym/'));
            $SP->norec = $norecSP;
            $SP->kdprofile = 0;
            $SP->statusenabled = true;
            $SP->nostruk = $noStruk;
            $SP->noterima = $noStruk;

            $SP->objectkelompoktransaksifk = 87;
//            $SP->objectrekananfk =  null;
//            $SP->namarekanan = $req['struk']['namarekanan'];
            $SP->objectruanganfk = $request['struk']['objectruanganfk'];
            $SP->keteranganlainnya = 'Farmasi Produksi';
//            $SP->nofaktur = $req['struk']['nofaktur'];
//            $SP->tglfaktur =  $req['struk']['tglfaktur'];
            $SP->tglstruk = date('Y-m-d H:i:s');
            $SP->objectpegawaipenerimafk = $request['struk']['objectpegawaiygmengetahuifk'];
            $SP->objectpegawaimenerimafk = $request['struk']['objectpegawaiygmengetahuifk'];
//            $SP->namapegawaipenerima = $req['struk']['namapegawaipenerima'];
            $SP->qtyproduk =  1;

//            $SP->totalharusdibayar = $req['struk']['totalharusdibayar'];
//            $SP->totalppn = $req['struk']['totalppn'];
//            $SP->totaldiscount = $req['struk']['totaldiscount'];
            $SP->totalhargasatuan = $request['struk']['hargasatuan'];

            $SP->save();

            $norec_SP = $SP->norec;

            $SPD = new StrukPelayananDetail();
            $norecKS = $SPD->generateNewId();
            $SPD->norec = $norecKS;
            $SPD->kdprofile = 0;
            $SPD->statusenabled = true;
            $SPD->nostrukfk = $SP->norec;

            $SPD->objectasalprodukfk = 10;
            $SPD->objectprodukfk = $request['struk']['objectprodukfk'];
            $SPD->objectruanganfk = $request['struk']['objectruanganfk'];
            $SPD->objectruanganstokfk = $request['struk']['objectruanganfk'];
            $SPD->objectsatuanstandarfk = $request['struk']['satuan'];
            $SPD->hargadiscount =  0;
            $SPD->hargadiscountgive = 0;
            $SPD->hargadiscountsave = 0;
            $SPD->harganetto = $request['struk']['hargasatuan'];
            $SPD->hargapph = 0;
            $SPD->hargappn = 0;
            $SPD->hargasatuan = $request['struk']['hargasatuan'];
            $SPD->hasilkonversi = 1;
//            $SPD->namaproduk = $item['namaproduk'];
            $SPD->keteranganlainnya = 'Farmasi Produksi';
            $SPD->hargasatuandijamin = 0;
            $SPD->hargasatuanppenjamin = 0;
            $SPD->hargatambahan = 0;
            $SPD->hargasatuanpprofile = 0;
            $SPD->isonsiteservice = 0;
            $SPD->kdpenjaminpasien = 0;
            $SPD->persendiscount = 0;
            $SPD->persenppn =0;
            $SPD->qtyproduk =$request['struk']['jumlahproduksi'];
            $SPD->qtyprodukoutext = 0;
            $SPD->qtyprodukoutint = 0;
            $SPD->qtyprodukretur = 0;
            $SPD->satuan =  '-';//$item['satuanstandar'];;
            $SPD->satuanstandar =  $request['struk']['satuan'];
            $SPD->tglpelayanan = date('Y-m-d H:i:s');
            $SPD->is_terbayar = 0;
            $SPD->linetotal = 0;
            $SPD->tglkadaluarsa = $request['struk']['tanggalexpired'];
            $SPD->nobatch = $noStruk;

            $SPD->save();

            //PENAMBAH STOCK HASIL PRODUKSI
            $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                    where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                array(
                    'ruanganfk' => $request['struk']['objectruanganfk'],
                    'produkfk' => $request['struk']['objectprodukfk'],
                )
            );
            $saldoAwalPenerima = 0;
            foreach ($dataSaldoAwalT as $items) {
                $saldoAwalPenerima = (float)$items->qty;
            }
//            $data = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
//                ->where('objectprodukfk', $item['produkfk'])
//                ->where('nobatch', $dataSPDK->nobatch)
//                ->where('qtyproduk', '>',0)
//                ->where('objectruanganfk', $request['struk']['objectruangantujuanfk'])
//                ->first();
//            if (count($data) > 0) {
//                StokProdukDetail::where('norec', $data->norec)
//                    ->update([
//                            'qtyproduk' => (float)$data->qtyproduk + ((float)$item['jumlah'] * (float)$item['nilaikonversi'])]
//                    );
//            } else {
                $dataNewSPD = new StokProdukDetail;
                $dataNewSPD->norec = $dataNewSPD->generateNewId();
                $dataNewSPD->kdprofile = 0;
                $dataNewSPD->statusenabled = true;
                $dataNewSPD->objectasalprodukfk = 10;
                $dataNewSPD->hargadiscount = 0;
                $dataNewSPD->harganetto1 = $request['struk']['hargasatuan'];
                $dataNewSPD->harganetto2 = $request['struk']['hargasatuan'];
                $dataNewSPD->persendiscount = 0;
                $dataNewSPD->objectprodukfk = $request['struk']['objectprodukfk'];
                $dataNewSPD->qtyproduk = (float)$request['struk']['jumlahproduksi'];
                $dataNewSPD->qtyprodukonhand = 0;
                $dataNewSPD->qtyprodukoutext = 0;
                $dataNewSPD->qtyprodukoutint = 0;
                $dataNewSPD->objectruanganfk = $request['struk']['objectruanganfk'];
                $dataNewSPD->nostrukterimafk = $norec_SP;
                $dataNewSPD->noverifikasifk = null;
                $dataNewSPD->nobatch = $noStruk;
                $dataNewSPD->tglkadaluarsa = $request['struk']['tanggalexpired'];
                $dataNewSPD->tglpelayanan = date('Y-m-d H:i:s');
                $dataNewSPD->tglproduksi = date('Y-m-d H:i:s');
                $dataNewSPD->save();
//            }
            //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = 0;
                $newKS->statusenabled = true;
                $newKS->jumlah = (float)$request['struk']['jumlahproduksi'];
                $newKS->keterangan = 'Farmasi Produksi ruangan ' . $strRuanganAsal . ' Kode Produksi '.  $SP->nostruk = $noStruk;
                $newKS->produkfk = $request['struk']['objectprodukfk'];
                $newKS->ruanganfk = $request['struk']['objectruanganfk'];
                $newKS->saldoawal = (float)$saldoAwalPenerima + (float)$request['struk']['jumlahproduksi'];
                $newKS->status = 1;
                $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $norec_SP;
                $newKS->save();

            foreach ($request['details'] as $item) {
                //PENGURANG STOCK
                $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                    where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['struk']['objectruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $saldoAwalPengirim = 0;
                foreach ($dataSaldoAwalK as $items) {
                    $saldoAwalPengirim = (float)$items->qty;
                }

                $dataSPDK = StokProdukDetail::where('objectprodukfk', $item['produkfk'])
                    ->where('objectruanganfk', $request['struk']['objectruanganfk'])
                    ->where('nostrukterimafk', $item['nostrukterimafk'])
                    ->where('qtyproduk', '>',0)
                    ->first();

                StokProdukDetail::where('norec', $dataSPDK->norec)
                ->update([
                        'qtyproduk' => (float)$dataSPDK->qtyproduk - ((float)$item['jumlah'] * (float)$item['nilaikonversi'])]
                );

                //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = 0;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->keterangan = 'Farmasi Produksi ruangan ' . $strRuanganAsal . ' Kode Produksi '.  $SP->nostruk = $noStruk;
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['struk']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                    $newKS->nostrukterimafk = $item['nostrukterimafk'];
                    $newKS->save();
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
                "saldoawal"=>$dataSaldoAwalT,
                "noproduksi" => $dataPNS,
                "dataSPDK" => $dataSPDK,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "saldoawal"=>$dataSaldoAwalT,
                "noproduksi" => $dataPNS,
                "dataSPDK" => $dataSPDK,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataComboMasterProduksi(Request $request) {
        $dataLogin = $request->all();
        $dataPenulis = Pegawai::where('statusenabled',true)
            ->where('objectjenispegawaifk',1)
            ->get();
        foreach ($dataPenulis as $item){
            $dataPenulis2[]=array(
                'id' => $item->id,
                'namalengkap' => $item->namalengkap,
            );
        }
//        $dataSigna = \DB::table('stigma as st')
//            ->select('st.id','st.name')
//            ->orderBy('st.name')
//            ->get();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id','ru.namaruangan')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();
        $dataRuanganall = \DB::table('ruangan_m as ru')
            ->select('ru.id','ru.namaruangan')
//            ->where('ru.objectdepartemenfk',14)
            ->where('ru.statusenabled',true)
            ->orderBy('ru.namaruangan')
            ->get();

//        $dataJenisKemasan = \DB::table('jeniskemasan_m as jk')
//            ->select('jk.id','jk.jeniskemasan')
//            ->where('jk.statusenabled',true)
//            ->get();
//        $dataJenisRacikan = \DB::table('jenisracikan_m as jk')
//            ->select('jk.id','jk.jenisracikan')
//            ->where('jk.statusenabled',true)
//            ->get();

        foreach ($dataRuangan as $lalala){
            $ruFilter[]=array($lalala->id);
        }
        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->select('pr.id','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('jp.id',97)
            ->groupBy('pr.id','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->JOIN('stokprodukdetail_t as spd','spd.objectasalprodukfk','=','ap.id')
            ->select('ap.id','ap.asalproduk')
            ->where('ap.statusenabled',true)
            ->orderBy('ap.id')
            ->groupBy('ap.id','ap.asalproduk')
            ->get();

//        $dataRoute = \DB::table('routefarmasi as rt')
//            ->select('rt.id','rt.name')
//            ->where('rt.statusenabled',true)
//            ->orderBy('rt.id')
//            ->get();
//        $dataKelas = \DB::table('kelas_m as rt')
//            ->select('rt.id','rt.namakelas')
//            ->where('rt.statusenabled',true)
//            ->orderBy('rt.id')
//            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.statusenabled',true)
            ->get();



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
            );
        }

        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );

        $result = array(
            'produk' => $dataProdukResult,
            'penulisresep' =>   $dataPenulis2,
            'ruangan' => $dataRuangan,
            'ruanganall' => $dataRuanganall,
//            'jeniskemasan' => $dataJenisKemasan,
            'asalproduk' => $dataAsalProduk,
//            'route' => $dataRoute,
//            'kelas' => $dataKelas,
//            'signa' => $dataSigna,
//            'jenisracikan' => $dataJenisRacikan,
            'detaillogin' => $dataPegawaiUser,
            'message' => 'as@epic',
//            'asdas' => $ruFilter,
        );

        return $this->respond($result);
    }

    public function getDaftarProduksiObat(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayanan_t as sp')
            ->leftJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk','sbk.norec','=','sp.nosbklastfk')
//            ->LEFTJOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
//            ->LEFTJOIN('jeniskemasan_m as jkm','jkm.id','=','spd.objectjeniskemasanfk')
            ->select('sp.tglstruk','sp.nostruk','rkn.namarekanan','pg.namalengkap',
                'ru.namaruangan','sp.norec','sp.nofaktur','sp.tglfaktur','sp.totalharusdibayar','sbk.nosbk'
//                'spd.hargasatuan','spd.hargadiscount','spd.qtyproduk','spd.hargatambahan' ,
//                'pr.namaproduk as namaprodukstandar',
//                'spd.resepke as rke','jkm.jeniskemasan'
            );

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglstruk','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglstruk','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ilike','%'. $request['nostruk'].'%');
        }
        if(isset($request['namarekanan']) && $request['namarekanan']!="" && $request['namarekanan']!="undefined"){
            $data = $data->where('rkn.namarekanan','ilike','%'. $request['namarekanan'].'%');
        }
        if(isset($request['nofaktur']) && $request['nofaktur']!="" && $request['nofaktur']!="undefined"){
            $data = $data->where('sp.nofaktur','ilike','%'. $request['nofaktur'].'%');
        }
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',87);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,
                    spd.hargappn,((spd.hargasatuan-spd.hargadiscount+spd.hargappn)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
                     from strukpelayanandetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where nostrukfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tglstruk' => $item->tglstruk,
                'nostruk' => $item->nostruk,
                'nofaktur' => $item->nofaktur,
                'tglfaktur' => $item->tglfaktur,
                'namarekanan' => $item->namarekanan,
                'norec' => $item->norec,
                'namaruangan' => $item->namaruangan,
                'namapenerima' => $item->namalengkap,
                'totalharusdibayar' => $item->totalharusdibayar,
                'nosbk' => $item->nosbk,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $result,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function saveInputSisaProduksiBarang(Request $request) {
    DB::beginTransaction();
    $ruanganAsal = DB::select(DB::raw("
                     select  ru.namaruangan
                     from ruangan_m as ru 
                    where ru.id=:id"),
        array(
            'id' => $request['struk']['objectruanganfk'],
        )
    );
    $strRuanganAsal =$ruanganAsal[0]->namaruangan;
    try{
        $noStruk = $this->generateCode(new ProduksiNonSteril, 'noproduksi', 14, 'FP-' . $this->getDateTime()->format('ym'));

        $dataPNS = new ProduksiNonSteril;
        $dataPNS->norec = $dataPNS->generateNewId();
        $dataPNS->kdprofile = 0;
        $dataPNS->statusenabled = true;
        $dataPNS->hargasatuan = $request['struk']['hargasatuan'];
        $dataPNS->jumlahproduksi = $request['struk']['jumlahproduksi'];
        $dataPNS->noproduksi = $noStruk;
//            $dataPNS->objectpegawaiygmemberikanfk = null;
//            $dataPNS->objectpegawaiygmemintafk = null;
        $dataPNS->objectpegawaiygmengetahuifk = $request['struk']['objectpegawaiygmengetahuifk'];
        $dataPNS->objectprodukfk = $request['struk']['objectprodukfk'];
        $dataPNS->satuan = $request['struk']['satuan'];
        $dataPNS->spesifikasi = $request['struk']['spesifikasi'];
        $dataPNS->tanggalexpired = $request['struk']['tanggalexpired'];
        $dataPNS->tglproduksi = date('Y-m-d H:i:s');
//            $dataPNS->unitcost = null;

        $dataPNS->save();
        $norecPNS = $dataPNS->norec;

        $SP = new StrukPelayanan();
        $norecSP = $SP->generateNewId();
        $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'FP/'.$this->getDateTime()->format('ym/'));
        $SP->norec = $norecSP;
        $SP->kdprofile = 0;
        $SP->statusenabled = true;
        $SP->nostruk = $noStruk;
        $SP->noterima = $noStruk;

        $SP->objectkelompoktransaksifk = 110;
//            $SP->objectrekananfk =  null;
//            $SP->namarekanan = $req['struk']['namarekanan'];
        $SP->objectruanganfk = $request['struk']['objectruanganfk'];
        $SP->keteranganlainnya = 'Farmasi Input Sisa Produksi';
//            $SP->nofaktur = $req['struk']['nofaktur'];
//            $SP->tglfaktur =  $req['struk']['tglfaktur'];
        $SP->tglstruk = date('Y-m-d H:i:s');
        $SP->objectpegawaipenerimafk = $request['struk']['objectpegawaiygmengetahuifk'];
        $SP->objectpegawaimenerimafk = $request['struk']['objectpegawaiygmengetahuifk'];
//            $SP->namapegawaipenerima = $req['struk']['namapegawaipenerima'];
        $SP->qtyproduk =  1;

//            $SP->totalharusdibayar = $req['struk']['totalharusdibayar'];
//            $SP->totalppn = $req['struk']['totalppn'];
//            $SP->totaldiscount = $req['struk']['totaldiscount'];
        $SP->totalhargasatuan = $request['struk']['hargasatuan'];

        $SP->save();

        $norec_SP = $SP->norec;

        $SPD = new StrukPelayananDetail();
        $norecKS = $SPD->generateNewId();
        $SPD->norec = $norecKS;
        $SPD->kdprofile = 0;
        $SPD->statusenabled = true;
        $SPD->nostrukfk = $SP->norec;

        $SPD->objectasalprodukfk = 10;
        $SPD->objectprodukfk = $request['struk']['objectprodukfk'];
        $SPD->objectruanganfk = $request['struk']['objectruanganfk'];
        $SPD->objectruanganstokfk = $request['struk']['objectruanganfk'];
        $SPD->objectsatuanstandarfk = $request['struk']['satuan'];
        $SPD->hargadiscount =  0;
        $SPD->hargadiscountgive = 0;
        $SPD->hargadiscountsave = 0;
        $SPD->harganetto = $request['struk']['hargasatuan'];
        $SPD->hargapph = 0;
        $SPD->hargappn = 0;
        $SPD->hargasatuan = $request['struk']['hargasatuan'];
        $SPD->hasilkonversi = 1;
//            $SPD->namaproduk = $item['namaproduk'];
        $SPD->keteranganlainnya = 'Farmasi Input Sisa Produksi';
        $SPD->hargasatuandijamin = 0;
        $SPD->hargasatuanppenjamin = 0;
        $SPD->hargatambahan = 0;
        $SPD->hargasatuanpprofile = 0;
        $SPD->isonsiteservice = 0;
        $SPD->kdpenjaminpasien = 0;
        $SPD->persendiscount = 0;
        $SPD->persenppn =0;
        $SPD->qtyproduk =$request['struk']['jumlahproduksi'];
        $SPD->qtyprodukoutext = 0;
        $SPD->qtyprodukoutint = 0;
        $SPD->qtyprodukretur = 0;
        $SPD->satuan =  '-';//$item['satuanstandar'];;
        $SPD->satuanstandar =  $request['struk']['satuan'];
        $SPD->tglpelayanan = date('Y-m-d H:i:s');
        $SPD->is_terbayar = 0;
        $SPD->linetotal = 0;
        $SPD->tglkadaluarsa = $request['struk']['tanggalexpired'];
        $SPD->nobatch = $noStruk;

        $SPD->save();

        //PENAMBAH STOCK HASIL PRODUKSI
        $dataSaldoAwalT = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                    where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
            array(
                'ruanganfk' => $request['struk']['objectruanganfk'],
                'produkfk' => $request['struk']['objectprodukfk'],
            )
        );
        $saldoAwalPenerima = 0;
        foreach ($dataSaldoAwalT as $items) {
            $saldoAwalPenerima = (float)$items->qty;
        }
//            $data = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
//                ->where('objectprodukfk', $item['produkfk'])
//                ->where('nobatch', $dataSPDK->nobatch)
//                ->where('qtyproduk', '>',0)
//                ->where('objectruanganfk', $request['struk']['objectruangantujuanfk'])
//                ->first();
//            if (count($data) > 0) {
//                StokProdukDetail::where('norec', $data->norec)
//                    ->update([
//                            'qtyproduk' => (float)$data->qtyproduk + ((float)$item['jumlah'] * (float)$item['nilaikonversi'])]
//                    );
//            } else {
        $dataNewSPD = new StokProdukDetail;
        $dataNewSPD->norec = $dataNewSPD->generateNewId();
        $dataNewSPD->kdprofile = 0;
        $dataNewSPD->statusenabled = true;
        $dataNewSPD->objectasalprodukfk = 10;
        $dataNewSPD->hargadiscount = 0;
        $dataNewSPD->harganetto1 = $request['struk']['hargasatuan'];
        $dataNewSPD->harganetto2 = $request['struk']['hargasatuan'];
        $dataNewSPD->persendiscount = 0;
        $dataNewSPD->objectprodukfk = $request['struk']['objectprodukfk'];
        $dataNewSPD->qtyproduk = (float)$request['struk']['jumlahproduksi'];
        $dataNewSPD->qtyprodukonhand = 0;
        $dataNewSPD->qtyprodukoutext = 0;
        $dataNewSPD->qtyprodukoutint = 0;
        $dataNewSPD->objectruanganfk = $request['struk']['objectruanganfk'];
        $dataNewSPD->nostrukterimafk = $norec_SP;
        $dataNewSPD->noverifikasifk = null;
        $dataNewSPD->nobatch = $noStruk;
        $dataNewSPD->tglkadaluarsa = $request['struk']['tanggalexpired'];
        $dataNewSPD->tglpelayanan = date('Y-m-d H:i:s');
        $dataNewSPD->tglproduksi = date('Y-m-d H:i:s');
        $dataNewSPD->save();
//            }
        //## KartuStok
        $newKS = new KartuStok();
        $norecKS = $newKS->generateNewId();
        $newKS->norec = $norecKS;
        $newKS->kdprofile = 0;
        $newKS->statusenabled = true;
        $newKS->jumlah = (float)$request['struk']['jumlahproduksi'];
        $newKS->keterangan = 'Farmasi Input Sisa Produksi ruangan ' . $strRuanganAsal . ' Kode Produksi '.  $SP->nostruk = $noStruk;
        $newKS->produkfk = $request['struk']['objectprodukfk'];
        $newKS->ruanganfk = $request['struk']['objectruanganfk'];
        $newKS->saldoawal = (float)$saldoAwalPenerima + (float)$request['struk']['jumlahproduksi'];
        $newKS->status = 1;
        $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
        $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
        $newKS->nostrukterimafk = $norec_SP;
        $newKS->save();

//        foreach ($request['details'] as $item) {
//            //PENGURANG STOCK
//            $dataSaldoAwalK = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
//                    where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
//                array(
//                    'ruanganfk' => $request['struk']['objectruanganfk'],
//                    'produkfk' => $item['produkfk'],
//                )
//            );
//            $saldoAwalPengirim = 0;
//            foreach ($dataSaldoAwalK as $items) {
//                $saldoAwalPengirim = (float)$items->qty;
//            }
//
//            $dataSPDK = StokProdukDetail::where('objectprodukfk', $item['produkfk'])
//                ->where('objectruanganfk', $request['struk']['objectruanganfk'])
//                ->where('nostrukterimafk', $item['nostrukterimafk'])
//                ->where('qtyproduk', '>',0)
//                ->first();
//
//            StokProdukDetail::where('norec', $dataSPDK->norec)
//                ->update([
//                        'qtyproduk' => (float)$dataSPDK->qtyproduk - ((float)$item['jumlah'] * (float)$item['nilaikonversi'])]
//                );
//
//            //## KartuStok
//            $newKS = new KartuStok();
//            $norecKS = $newKS->generateNewId();
//            $newKS->norec = $norecKS;
//            $newKS->kdprofile = 0;
//            $newKS->statusenabled = true;
//            $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
//            $newKS->keterangan = 'Farmasi Produksi ruangan ' . $strRuanganAsal . ' Kode Produksi '.  $SP->nostruk = $noStruk;
//            $newKS->produkfk = $item['produkfk'];
//            $newKS->ruanganfk = $request['struk']['objectruanganfk'];
//            $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
//            $newKS->status = 0;
//            $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
//            $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
//            $newKS->nostrukterimafk = $item['nostrukterimafk'];
//            $newKS->save();
//        }
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
//            "saldoawal"=>$dataSaldoAwalT,
            "noproduksi" => $dataPNS,
//            "dataSPDK" => $dataSPDK,
            "as" => 'as@epic',
        );
    } else {
        $transMessage = "Simpan Gagal!!";
        DB::rollBack();
        $result = array(
            "status" => 400,
            "message"  => $transMessage,
//            "saldoawal"=>$dataSaldoAwalT,
            "noproduksi" => $dataPNS,
//            "dataSPDK" => $dataSPDK,
            "as" => 'as@epic',
        );
    }
    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
}

    public function hapusObatProduksi(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $dataPNS = StrukPelayanan::where('nostruk', $request['data']['nostruk'])
                ->update([
                    'statusenabled' => 'f',
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= 0;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Input Produksi Obat';
            $logUser->noreff =$request['data']['norec'];
            $logUser->referensi='norec Produksi Non Steril';
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

    public function hapusMasterBarangProduksi(Request $request){
        DB::beginTransaction();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{

            $dataPNS = ProdukFormulaProduksi::where('objectprodukhasilfk', $request['data']['idprodukhasil'])
                ->update([
                    'statusenabled' => 'f',
                ]);

            //## Logging User
            $newId = LoggingUser::max('id');
            $newId = $newId +1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile= 0;
            $logUser->statusenabled=true;
            $logUser->jenislog = 'Batal Input Master Barang Produksi';
            $logUser->noreff =$request['data']['idprodukhasil'];
            $logUser->referensi='id Produk Formula Produksi';
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

    public function getInformasiStokDetail(Request $request) {

        $results = DB::select(DB::raw("select * from (select ru.namaruangan,sk.nostruk,
                    sum(spd.qtyproduk) as qtyproduk
                    from stokprodukdetail_t as spd
                    inner JOIN ruangan_m as ru on ru.id=spd.objectruanganfk
                    inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                    where spd.objectprodukfk =:produkId and ru.statusenabled =true and spd.objectruanganfk=:ruanganId
                    group by sk.nostruk,ru.namaruangan
                    order By ru.namaruangan,sk.nostruk) as x where x.qtyproduk >0"),
            array(
                'produkId' => $request['produkfk'],
                'ruanganId' => $request['ruanganfk'],
            )
        );

        $result= array(
            'detail' => $results,
            'message' => 'inhuman@epic',
        );
        return $this->respond($result);
    }

    public function getDaftarProduksiObatDetail(Request $request) {
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayanan_t as sp')
            ->leftJOIN('rekanan_m as rkn','rkn.id','=','sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk','sbk.norec','=','sp.nosbklastfk')
            ->LEFTJOIN('produk_m as pr','pr.id','=','sbk.objectprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->SELECT(DB::raw("sp.tglstruk,sp.nostruk,rkn.namarekanan,pg.namalengkap,ru.namaruangan,
                                    sp.norec,sp.nofaktur,sp.tglfaktur,sp.totalharusdibayar,sbk.nosbk,
                                    pr.namaproduk,ss.satuanstandar
                                    -- spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,
                                    -- sbk.hargappn,((sbk.hargasatuan-sbk.hargadiscount+sbk.hargappn)*sbk.qtyproduk) as total,
                                    -- sbk.tglkadaluarsa,sbk.nobatch
                                    "));

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglstruk','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglstruk','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ilike','%'. $request['nostruk'].'%');
        }
        if(isset($request['namarekanan']) && $request['namarekanan']!="" && $request['namarekanan']!="undefined"){
            $data = $data->where('rkn.namarekanan','ilike','%'. $request['namarekanan'].'%');
        }
        if(isset($request['nofaktur']) && $request['nofaktur']!="" && $request['nofaktur']!="undefined"){
            $data = $data->where('sp.nofaktur','ilike','%'. $request['nofaktur'].'%');
        }
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',87);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();
        $result = array(
            'daftar' => $data,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
}