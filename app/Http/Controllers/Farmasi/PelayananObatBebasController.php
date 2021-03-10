<?php
//“Great men are not born great, they grow great . . .”
//― Mario Puzo, The Godfather
/**
 * FarmasiController
 * Created by PhpStorm.
 * User: as@epic
 * Date: 08/08/2017
 * Time: 12:48
 */
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 22/08/2019
 * Time: 9:57
 */

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\ApiController;
use App\Transaksi\PelayananPasienRetur;
use App\Transaksi\StrukRetur;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\KartuStok;



class PelayananObatBebasController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }

    public function GetNorecResepBebas(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $norecResep = DB::select(DB::raw("select norec from strukpelayanan_t 
                  where kdprofile = $kdProfile and nostruk=:nostruk"),
            array(
                'nostruk' => $request['nostruk'],
            )
        );
        return $this->respond($norecResep);
    }
    public function getDetailResepBebas(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataAsalProduk = \DB::table('asalproduk_m as ap')
            ->select('ap.id','ap.asalproduk')
            ->where('ap.kdprofile',$kdProfile)
            ->get();
        $dataSigna = \DB::table('stigma as st')
            ->select('st.id','st.name')
            ->where('st.kdprofile',$kdProfile)
            ->get();
        $dataStruk = \DB::table('strukpelayanan_t as sr')
            ->JOIN('pegawai_m as pg','pg.id','=','sr.objectpegawaipenanggungjawabfk')
            ->JOIN('ruangan_m as ru','ru.id','=','sr.objectruanganfk')
            ->select('sr.nostruk','pg.id as pgid','pg.namalengkap','ru.id','ru.namaruangan','sr.nostruk_intern as nocm',
                'sr.namapasien_klien as nama','sr.tglfaktur as tgllahir','sr.noteleponfaks as notlp','sr.namatempattujuan as alamat',
                'sr.tglstruk as tglresep')
            ->where('sr.kdprofile',$kdProfile);

        if(isset($request['norecResep']) && $request['norecResep']!="" && $request['norecResep']!="undefined"){
            $dataStruk = $dataStruk->where('sr.norec','=', $request['norecResep']);
        }
        $dataStruk = $dataStruk->first();

        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd','spd.nostrukfk','=','sp.norec')
    //            ->JOIN('antrianpasiendiperiksa_t as apd','apd.norec','=','pp.noregistrasifk')
            ->JOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->JOIN('jeniskemasan_m as jk','jk.id','=','spd.objectjeniskemasanfk')
    //            ->JOIN('routefarmasi as rt','rt.id','=','pp.routefk')
            ->JOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
            ->JOIN('satuanstandar_m as ss','ss.id','=','spd.objectsatuanstandarfk')
            ->leftJoin('satuanresep_m as sn','sn.id','=','spd.satuanresepfk')
    //            ->JOIN('satuanstandar_m as ss2','ss2.id','=','cast(spd.satuanstandar as integer)')
            ->select('sp.nostruk','spd.hargasatuan','spd.qtyprodukoutext','sp.objectruanganfk','ru.namaruangan',
                'spd.resepke','jk.id as jkid','jk.jeniskemasan','spd.aturanpakai',
                'spd.objectprodukfk as produkfk','pr.namaproduk','spd.hasilkonversi as nilaikonversi',
                'spd.objectsatuanstandarfk','ss.satuanstandar','spd.satuanstandar as satuanviewfk','ss.satuanstandar as ssview',
                'spd.qtyproduk as jumlah','spd.hargadiscount','spd.hargatambahan as jasa','spd.hargasatuan as hargajual','spd.harganetto',
                'spd.qtydetailresep','spd.ispagi','spd.issiang','spd.ismalam','spd.issore','pr.kekuatan','spd.dosis','spd.satuanresepfk','sn.satuanresep','spd.tglkadaluarsa')
            ->where('sp.kdprofile',$kdProfile);

        if(isset($request['norecResep']) && $request['norecResep']!="" && $request['norecResep']!="undefined"){
            $data = $data->where('sp.norec','=', $request['norecResep']);
        }
        $data = $data->get();

        $pelayananPasien=[];
        $i = 0;
        $dataStok = DB::select(DB::raw("select sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                                spd.harganetto2 as hargajual,spd.harganetto2 as harganetto,spd.hargadiscount,
                        sum(spd.qtyproduk) as qtyproduk,spd.objectruanganfk
                        from stokprodukdetail_t as spd
                        inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk                     
                        where spd.kdprofile = $kdProfile and spd.objectruanganfk =:ruanganid
                        group by sk.norec,spd.objectprodukfk, sk.tglstruk,spd.objectasalprodukfk,
                                spd.harganetto2,spd.hargadiscount,
                        spd.objectruanganfk
                        order By sk.tglstruk"),
            array(
                'ruanganid' => $dataStruk->id
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
        $aturanpakaifk=0;
        foreach ($data as $item){
            $i = $i+1;

    //            $tarifjasa =0;
    //            $qty20 =0;
    //            if ($item->jkid == 2){
    //                $tarifjasa = 800;
    //            }
    //            if ($item->jkid == 1){
    ////                $qty20 = Math.floor(parseFloat($scope.item.jumlah)/20)
    //                $qty20 = number_format($item->jumlah/20,0);
    //                if ($item->jumlah % 20 == 0) {
    //                    $qty20 = $qty20;
    //                }else{
    //                    $qty20 = $qty20 + 1;
    //                }
    //                $tarifjasa = 800 * $qty20;
    //            }
            foreach ($dataStok as $item2){
                $hargajual = 0;
                $harganetto = 0;
                $hargadiscount = 0;
                if ($item2->objectprodukfk == $item->produkfk){
    //                    if ($item2->qtyproduk >= $item->jumlah*$item->nilaikonversi){
                    if ($item2->qtyproduk >= 0){
    //                        $hargajual = $item2->hargajual+(($item2->hargajual*25)/100);
    //                        $harganetto = $item2->harganetto+(($item2->harganetto*25)/100);

                        $hargajual = $item->hargajual;
                        $harganetto = $item->harganetto;

                        $nostrukterimafk = $item2->norec;
                        $asalprodukfk = $item2->objectasalprodukfk;
    //                        $asalproduk = $item2->objectasalprodukfk;
                        $jmlstok = $item2->qtyproduk;
                        $hargasatuan = $harganetto;//$item2->harganetto;
                        $hargadiscount = $item->hargadiscount;
    //                        $hargadiscount = $item2->hargadiscount;
                        $total =(((float)$item->jumlah * ((float)$hargasatuan-(float)$hargadiscount))) ;
                        break;
                    }
                }
            }
            foreach ($dataAsalProduk as $item3){
                if ($asalprodukfk == $item3->id){
                    $asalproduk = $item3->asalproduk;
                }
            }
            foreach ($dataSigna as $item4){
                if ($item->aturanpakai == $item4->id){
                    $aturanpakaifk = $item4->id;
                }
            }
            $jmlxMakan = (((float)$item->jumlah/(float)$item->nilaikonversi)/(float)$item->dosis)*(float)$item->kekuatan;
            $pelayananPasien[] = array(
                'no' => $i,
                'noregistrasifk' => '',
                'tglregistrasi' => '',
                'generik' => null,
                'hargajual' => $hargajual,
                'jenisobatfk' => '',
                'kelasfk' => '',
                'stock' => $jmlstok,
                'harganetto' => $harganetto,
                'nostrukterimafk' => $nostrukterimafk,
                'ruanganfk' => $item->objectruanganfk,
                'rke' => $item->resepke,
                'jeniskemasanfk' => $item->jkid,
                'jeniskemasan' => $item->jeniskemasan,
                'aturanpakaifk' => $aturanpakaifk,
                'aturanpakai' => $item->aturanpakai,
                'routefk' => 0,
                'route' => '',
                'asalprodukfk' => $asalprodukfk,
                'asalproduk' => $asalproduk,
                'produkfk' => $item->produkfk,
                'namaproduk' => $item->namaproduk,
                'nilaikonversi' => $item->nilaikonversi,
                'satuanstandarfk' => $item->satuanviewfk,//objectsatuanstandarfk,
                'satuanstandar' => $item->ssview,//satuanstandar,
                'satuanviewfk' => $item->satuanviewfk,
                'satuanview' => $item->ssview,
                'jmlstok' => $jmlstok,
                'jumlah' => $item->jumlah/$item->nilaikonversi,
                'dosis' => $item->dosis,
                'hargasatuan' => $hargasatuan,
                'hargadiscount' => $hargadiscount,
                'total' => $total +$item->jasa,
//                'jmldosis' => (String)($item->jumlah/$item->nilaikonversi)/1 . '/' . (String)1,
                'jasa' => $item->jasa,
                'jmldosis' => (String)$jmlxMakan . '/' . (String)$item->dosis . '/' . $item->kekuatan,
                'ispagi'  => $item->ispagi,
                'issiang' => $item->issiang,
                'ismalam' => $item->ismalam,
                'issore' => $item->issore,
                'jumlahobat' => $item->qtydetailresep,
                'satuanresep' => $item->satuanresepfk,
                'satuanresepview' => $item->satuanresep,
                'tglkadaluarsa' => $item->tglkadaluarsa,
            );
        }

        $result = array(
            'detailresep' => $dataStruk,
            'pelayananPasien' => $pelayananPasien,
            'data' => $data,
            'datastok' => $dataStok,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDetailPasien(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = \DB::table('pasien_m as ps')
            ->leftJOIN('alamat_m as al','al.nocmfk','=','ps.id')
            ->leftJOIN('pekerjaan_m as pkr','pkr.id','=','ps.objectpekerjaanfk')
            ->leftJOIN('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->select('ps.nocm','ps.namapasien','ps.notelepon','ps.tgllahir','al.alamatlengkap','pkr.id as pekerjaanid','pkr.pekerjaan','jk.id as jkid',
                'jk.jeniskelamin','al.alamatemail')
            ->where('ps.kdprofile', $kdProfile)
            ->where('ps.statusenabled',true);
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('ps.nocm', $request['nocm'] );
        };
        $data = $data->first();

        return $this->respond($data);
    }

    public function SaveInputTagihanObat(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $noResep = $this->generateCodeBySeqTable(new StrukPelayanan, 'nostruk', 13, 'OB/'.$this->getDateTime()->format('ym/'), $kdProfile);
        if ($noResep == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        DB::beginTransaction();
        $transMessage='';
        $req = $request->all();
//        return $this->respond($req);
        $namaPasien = $request['strukresep']['nocm'] . ' ' . $request['strukresep']['namapasien'];
        $resepOld = StrukPelayanan::where('norec', $request['strukresep']['noresep'])->where('kdprofile',$kdProfile);
        try{
//            return $this->respond((float)$request['strukresep']['totalharusdibayar']);
            if ($request['strukresep']['noresep'] == ''){
                $SP = new StrukPelayanan();
                $norecSP = $SP->generateNewId();
                $noStruk = $noResep;//$this->generateCode(new StrukPelayanan, 'nostruk', 13, 'OB/'.$this->getDateTime()->format('ym/'));
                $SP->norec = $norecSP;
                $SP->kdprofile = $kdProfile;
                $SP->statusenabled = true;
                $SP->nostruk = $noStruk;
            }else{
                $SP = StrukPelayanan::where('norec',$request['strukresep']['noresep'])->where('kdprofile', $kdProfile)->first();
                $SPOld = StrukPelayanan::where('norec',$request['strukresep']['noresep'])->where('kdprofile', $kdProfile)->first();
                $noStruk = $SP->nostruk;
                $norecSP = $SP->norec;
                KartuStok::where('keterangan',  'Pelayanan Obat Bebas ' . $noStruk . ' ' . $namaPasien)
                    ->where('kdprofile', $kdProfile)
                    ->update([
                        'flagfk' => null
                    ]);

                if ($request['strukresep']['ruanganfk'] == $SPOld->objectruanganfk){
                    //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                    $dataKembaliStok = DB::select(DB::raw("select spd.qtyproduk ,spd.hasilkonversi  ,spd.objectruanganfk ,
                    spd.objectprodukfk,spd.harganetto
                    from strukpelayanandetail_t as spd
                    where spd.kdprofile = $kdProfile and spd.nostrukfk=:strukresepfk"),
                        array(
                            'strukresepfk' => $norecSP,
                        )
                    );
                    foreach ($dataKembaliStok as $item5){
                        $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;
                        $newSPD = StokProdukDetail::where('objectruanganfk',$item5->objectruanganfk)
                            ->where('kdprofile', $kdProfile)
                            ->where('objectprodukfk',$item5->objectprodukfk)
                            //  ->where('harganetto1',$item5->harganetto)
                            ->orderby('tglkadaluarsa','desc')
                            ->first();
                        StokProdukDetail::where('norec', $newSPD->norec)
                            ->where('kdprofile', $kdProfile)
                            ->update([
                                    'qtyproduk' => (float)$newSPD->qtyproduk + (float)$TambahStok]
                            );
                        // $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
                        // $newSPD->save();

                        $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $item5->objectruanganfk,
                                'produkfk' => $item5->objectprodukfk,
                            )
                        );
                        $saldoAwal=0;
                        foreach ($dataSaldoAwal as $itemss){
                            $saldoAwal = (float)$itemss->qty;
                        }

                        $tglnow =  date('Y-m-d H:i:s');
                        $tglUbah = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow)));

                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $kdProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                        $newKS->keterangan = 'Ubah Resep Obat Bebas No. ' . $noStruk;
                        $newKS->produkfk = $item5->objectprodukfk;
                        $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
                        $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
                        $newKS->status = 1;
                        $newKS->tglinput = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                        $newKS->tglkejadian = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                        $newKS->norectransaksi = $newSPD->norec;
                        $newKS->tabletransaksi = 'stokprodukdetail_t';
                        $newKS->save();
                    }
                    //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL

                } else {

                    //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                    $dataKembaliStok = DB::select(DB::raw("select spd.qtyproduk ,spd.hasilkonversi  ,spd.objectruanganfk ,
                    spd.objectprodukfk,spd.harganetto
                    from strukpelayanandetail_t as spd
                    where spd.kdprofile = $kdProfile and spd.nostrukfk=:strukresepfk"),
                        array(
                            'strukresepfk' => $norecSP,
                        )
                    );
                    foreach ($dataKembaliStok as $item5){
                        $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;
                        $newSPD = StokProdukDetail::where('objectruanganfk', $SPOld->objectruanganfk)
                            ->where('kdprofile', $kdProfile)
                            ->where('objectprodukfk',$item5->objectprodukfk)
                            //  ->where('harganetto1',$item5->harganetto)
                            ->orderby('tglkadaluarsa','desc')
                            ->first();
                        StokProdukDetail::where('norec', $newSPD->norec)
                            ->where('kdprofile', $kdProfile)
                            ->update([
                                    'qtyproduk' => (float)$newSPD->qtyproduk + (float)$TambahStok]
                            );
                        // $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
                        // $newSPD->save();

                        $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                        where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                            array(
                                'ruanganfk' => $SPOld->objectruanganfk,
                                'produkfk' => $item5->objectprodukfk,
                            )
                        );
                        $saldoAwal=0;
                        foreach ($dataSaldoAwal as $itemss){
                            $saldoAwal = (float)$itemss->qty;
                        }

                        $tglnow =  date('Y-m-d H:i:s');
                        $tglUbah = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow)));

                        $newKS = new KartuStok();
                        $norecKS = $newKS->generateNewId();
                        $newKS->norec = $norecKS;
                        $newKS->kdprofile = $kdProfile;
                        $newKS->statusenabled = true;
                        $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                        $newKS->keterangan = 'Ubah Resep Obat Bebas No. ' . $noStruk;
                        $newKS->produkfk = $item5->objectprodukfk;
                        $newKS->ruanganfk = $SPOld->objectruanganfk;//$item->ruanganfk;
                        $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
                        $newKS->status = 1;
                        $newKS->tglinput = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                        $newKS->tglkejadian = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                        $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                        $newKS->norectransaksi = $newSPD->norec;
                        $newKS->tabletransaksi = 'stokprodukdetail_t';
                        $newKS->save();
                    }
                    //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
                }
                $delSPD = StrukPelayananDetail::where('nostrukfk',$request['strukresep']['noresep'])
                          ->where('kdprofile', $kdProfile)
                          ->delete();
            }

            $SP->objectkelompoktransaksifk = 2;
            $SP->keteranganlainnya = $request['strukresep']['keteranganlainnya'];
            $SP->namapasien_klien = $request['strukresep']['namapasien'];
            $SP->nostruk_intern = $request['strukresep']['nocm'];
            $SP->namarekanan = 'Umum/Tunai';
            if (isset($request['strukresep']['tglLahir'])){
                $SP->tglfaktur =  $request['strukresep']['tglLahir'];//tgllahir
            }
            $SP->noteleponfaks =  $request['strukresep']['noTelepon'];//notlp
            $SP->namatempattujuan =  $request['strukresep']['alamat'];//alamat
            $SP->objectpegawaipenanggungjawabfk = $request['strukresep']['penulisresepfk'];
            $SP->tglstruk = $request['strukresep']['tglresep'];
            $SP->totalharusdibayar = $request['strukresep']['totalharusdibayar'];
            $SP->objectruanganfk = $request['strukresep']['ruanganfk'];
            $SP->namakurirpengirim = $request['strukresep']['karyawan'];
            $SP->save();
//            return $this->respond($request->all());
            foreach ($request['details'] as $item) {
                $qtyJumlah = (float)$item['jumlah'];
                $SPD = new StrukPelayananDetail();
                $norecKS = $SPD->generateNewId();
                $SPD->norec = $norecKS;
                $SPD->kdprofile = $kdProfile;
                $SPD->statusenabled = true;
                $SPD->nostrukfk = $SP->norec;
                $SPD->objectasalprodukfk = $item['asalprodukfk'];
                $SPD->objectjeniskemasanfk = $item['jeniskemasanfk'];
                $SPD->objectprodukfk = $item['produkfk'];
                $SPD->objectruanganfk = $item['ruanganfk'];
                $SPD->objectruanganstokfk = $item['ruanganfk'];
                $SPD->objectsatuanstandarfk = $item['satuanstandarfk'];
                $SPD->aturanpakai = $item['aturanpakai'];
                $SPD->hargadiscount = $item['hargadiscount'];
                $SPD->hargadiscountgive = 0;
                $SPD->hargadiscountsave = 0;
                $SPD->harganetto = $item['hargasatuan'];
                $SPD->hargapph = 0;
                $SPD->hargappn = 0;
                $SPD->hargasatuan = $item['hargasatuan'];
                $SPD->hasilkonversi = $item['nilaikonversi'];
                $SPD->namaproduk = $item['namaproduk'];
                $SPD->resepke = $item['rke'];
                $SPD->hargasatuandijamin = 0;
                $SPD->hargasatuanppenjamin = 0;
                $SPD->hargasatuanpprofile = 0;
                $SPD->hargatambahan = $item['jasa'];
                $SPD->isonsiteservice = 0;
                $SPD->kdpenjaminpasien = 0;
                $SPD->persendiscount = 0;
                $SPD->qtyproduk = $item['jumlah'];
                $SPD->qtyprodukoutext = 0;
                $SPD->qtyprodukoutint = 0;
                $SPD->qtyprodukretur = 0;
                $SPD->satuan = '-';
                $SPD->satuanstandar = $item['satuanviewfk'];
                if(isset($item['satuanresep'])){
                    $SPD->satuanresepfk = $item['satuanresep'];
                }
                $SPD->tglpelayanan = $request['strukresep']['tglresep'];
                $SPD->is_terbayar = 0;
                $SPD->linetotal = 0;
                $SPD->qtydetailresep = $item['jumlahobat'];
                $SPD->ispagi = $item['ispagi'];
                $SPD->issiang = $item['issiang'];
                $SPD->ismalam = $item['ismalam'];
                $SPD->issore = $item['issore'];
                $SPD->dosis = $item['dosis'];
                if (isset($item['tglkadaluarsa']) && $item['tglkadaluarsa'] != 'Invalid date' && $item['tglkadaluarsa'] != ''){
                    $SPD->tglkadaluarsa = $item['tglkadaluarsa'];
                }
                $SPD->save();

                //## StokProdukDetail
                $GetNorec = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('objectruanganfk', $item['ruanganfk'])
                    ->where('objectasalprodukfk', $item['asalprodukfk'])
                    ->where('objectprodukfk', $item['produkfk'])
                    ->where('qtyproduk', '>', 0)
                    ->select('norec')
                    ->get();

                $jmlPengurang = (float)$qtyJumlah;
                $kurangStok = (float)0;
                foreach ($GetNorec as $item2) {
                    $newSPD = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
                        ->where('kdprofile', $kdProfile)
                        ->where('objectruanganfk', $item['ruanganfk'])
                        ->where('objectasalprodukfk', $item['asalprodukfk'])
                        ->where('objectprodukfk', $item['produkfk'])
                        ->where('norec', $item2->norec)
                        ->where('qtyproduk', '>', 0)
                        ->first();

                    if ((float)$newSPD->qtyproduk <= (float)$jmlPengurang) {
                        $kurangStok = (float)$newSPD->qtyproduk;
                        $jmlPengurang = (float)$jmlPengurang - (float)$kurangStok;
                    } else {
                        $kurangStok = (float)$jmlPengurang;
                        $jmlPengurang = (float)$jmlPengurang - (float)$kurangStok;
                    }

                    $newSPD->qtyproduk = (float)$newSPD->qtyproduk - (float)$kurangStok;//$r_PPL['jumlah'];
                    $dadada[] = array('kurangStok' => (float)$kurangStok, 'jmlPengurang' => (float)$jmlPengurang, 'stok' => (float)$newSPD->qtyproduk);
                    $newSPD->save();
                }

                $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                  where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['strukresep']['ruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );

                foreach ($dataSaldoAwal as $items) {
                    $saldoAwal = (float)$items->qty;
                }


                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $kdProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = $qtyJumlah;
                $newKS->keterangan = 'Pelayanan Obat Bebas ' . $noStruk . ' ' . $namaPasien;
                $newKS->produkfk = $item['produkfk'];
                $newKS->ruanganfk = $request['strukresep']['ruanganfk'];
                $newKS->saldoawal = (float)$saldoAwal;//- (float)$qtyJumlah;
                $newKS->status = 0;
                $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $item['nostrukterimafk'];
                $newKS->norectransaksi = $SP->norec;
                $newKS->tabletransaksi = 'strukpelayanan_t';
                $newKS->flagfk = 7;
                $newKS->save();

            }
//        }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Struk Pelayanan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $SP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Struk Pelayanan Gagal!!";
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

    public function getDaftarPenjualanBebas(Request $request) {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        $data = \DB::table('strukpelayanan_t as sp')
//            ->JOIN('strukpelayanandetail_t as spd','spd.nostrukfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipenanggungjawabfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipenerimaan_t as sbm','sbm.norec','=','sp.nosbmlastfk')
//            ->LEFTJOIN('produk_m as pr','pr.id','=','spd.objectprodukfk')
//            ->LEFTJOIN('jeniskemasan_m as jkm','jkm.id','=','spd.objectjeniskemasanfk')
            ->select('sp.tglstruk','sp.nostruk','sp.nostruk_intern','sp.namapasien_klien','pg.namalengkap',
                'ru.namaruangan','sp.norec','sp.noteleponfaks','sp.namatempattujuan','sbm.nosbm'
                , 'sp.namakurirpengirim'
//                'spd.hargasatuan','spd.hargadiscount','spd.qtyproduk','spd.hargatambahan' ,
//                'pr.namaproduk as namaprodukstandar',
//                'spd.resepke as rke','jkm.jeniskemasan'
            )
            ->where('sp.kdprofile', $kdProfile);

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglstruk','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglstruk','<=', $tgl);
        }
        if(isset($request['nostruk']) && $request['nostruk']!="" && $request['nostruk']!="undefined"){
            $data = $data->where('sp.nostruk','ilike','%'. $request['nostruk']);
        }
        if(isset($request['nocm']) && $request['nocm']!="" && $request['nocm']!="undefined"){
            $data = $data->where('sp.nostruk_intern','ilike','%'. $request['nocm'].'%');
        }
        if(isset($request['namapasien']) && $request['namapasien']!="" && $request['namapasien']!="undefined"){
            $data = $data->where('sp.namapasien_klien','ilike','%'. $request['namapasien'].'%');
        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $data = $data->where('ru.id', '=', $request['ruid']);
        }
        $data = $data->where('sp.statusenabled',true);
        $data = $data->wherein('ru.objectdepartemenfk',[14]);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();
        $result=[];
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                    select spd.tglpelayanan, spd.resepke,jkm.jeniskemasan,pr.namaproduk,objectjeniskemasanfk,
                    ss.satuanstandar,spd.qtyproduk,spd.hargasatuan,spd.hargadiscount,
                    spd.hargatambahan,((spd.hargasatuan-spd.hargadiscount)*spd.qtyproduk)+spd.hargatambahan as total,
                    spd.tglkadaluarsa
                    from strukpelayanandetail_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN jeniskemasan_m as jkm on jkm.id=spd.objectjeniskemasanfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $kdProfile and nostrukfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $namapasienasdsa = $item->namapasien_klien ;
            if ($item->namakurirpengirim != ''){
                $namapasienasdsa = $item->namakurirpengirim . ' / ' .$item->namapasien_klien;
            }
            $result[] = array(
                'tglstruk' => $item->tglstruk,
                'nostruk' => $item->nostruk,
                'nostruk_intern' => $item->nostruk_intern,
                'namapasien_klien' => $namapasienasdsa,
                'namalengkap' => $item->namalengkap,
                'norec' => $item->norec,
                'namaruangan' => $item->namaruangan,
                'noteleponfaks' => $item->noteleponfaks,
                'namatempattujuan' => $item->namatempattujuan,
                'nosbm' => $item->nosbm,
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

    public function DeleteResepOB(Request $request) {
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
        $dataKembaliStok = DB::select(DB::raw("select spd.qtyproduk ,spd.hasilkonversi  ,spd.objectruanganfk ,
                spd.objectprodukfk,spd.harganetto,sp.nostruk
                from strukpelayanandetail_t as spd inner join strukpelayanan_t as sp on sp.norec=spd.nostrukfk
                where spd.kdprofile = $kdProfile and spd.nostrukfk=:strukfk"),
            array(
                'strukfk' => $request['norec_sp'],
            )
        );
        try {

        foreach ($dataKembaliStok as $item5){
            $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;
            $newSPD = StokProdukDetail::where('objectruanganfk',$item5->objectruanganfk)
                ->where('kdprofile',$kdProfile)
                ->where('objectprodukfk',$item5->objectprodukfk)
//                ->where('harganetto1',$item5->harganetto)
                ->orderby('tglkadaluarsa','desc')
                ->first();
            $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
            $newSPD->save();
//                $transStatus = 'true';
//            } catch (\Exception $e) {
//                $transStatus = 'false';
//                $transMessage = "update Stok obat";
//            }

            $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                    where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                array(
                    'ruanganfk' => $item5->objectruanganfk,
                    'produkfk' => $item5->objectprodukfk,
                )
            );
            $saldoAwal=0;
            foreach ($dataSaldoAwal as $itemss){
                $saldoAwal = (float)$itemss->qty;
            }

            $dataPasien = \DB::table('strukpelayanan_t as sr')
                ->select(DB::raw("sr.namapasien_klien, sr.nostruk, sr.nostruk_intern"))
                ->where('sr.kdprofile',$kdProfile)
                ->where('sr.norec',$request['norec_sp'])
                ->first();

            $dataKs = KartuStok::where('keterangan',  'Pelayanan Obat Bebas'.' '. $dataPasien->nostruk .' '. $dataPasien->nostruk_intern .' '. $dataPasien->namapasien_klien)
                ->where('kdprofile', $kdProfile)
                ->update([
                    'flagfk' => null
                ]);
//                ->first();

//            return $this->respond($dataKs);

            $newKS = new KartuStok();
            $norecKS = $newKS->generateNewId();
            $newKS->norec = $norecKS;
            $newKS->kdprofile = $kdProfile;
            $newKS->statusenabled = true;
            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
            $newKS->keterangan = 'Hapus Resep Obat Bebas No. ' . $item5->nostruk;
            $newKS->produkfk = $item5->objectprodukfk;
            $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
            $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
            $newKS->status = 1;
            $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
            $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
            $newKS->nostrukterimafk =  $newSPD['nostrukterimafk'];
            $newKS->norectransaksi = $newSPD->norec;
            $newKS->tabletransaksi = 'stokprodukdetail_t';
//            $newKS->flagfk = 8;
//            try {
                $newKS->save();
//                $transStatus = 'true';
//            } catch (\Exception $e) {
//                $transStatus = 'false';
//                $transMessage = "Kartu Stok Ubah Resep";
//            }
        }
        //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//        try {
            $datadel = StrukPelayananDetail::where('nostrukfk',$request['norec_sp'])->delete();
            $datadel2 = StrukPelayanan::where('norec', $request['norec_sp'])->delete();
//                ->update([
//                        'statusenabled' => 'f']
//                );
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "gagal Pelayanan Pasien";
        }

        ##KartuStok


        if ($transStatus == 'true') {
            $transMessage = "Hapus Pelayanan OB Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Hapus Pelayanan OB Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveReturTagihanObat(Request $request) {
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $transMessage='';
        $req = $request->all();
//        return $this->respond($req);
        try{
            if ($request['strukresep']['noresep'] == ''){
                $SP = new StrukPelayanan();
                $norecSP = $SP->generateNewId();
                $noStruk = $this->generateCode(new StrukPelayanan, 'nostruk', 13, 'OB/'.$this->getDateTime()->format('ym/'), $kdProfile);
                $SP->norec = $norecSP;
                $SP->kdprofile = $kdProfile;
                $SP->statusenabled = true;
                $SP->nostruk = $noStruk;
            }else{
                $SP = StrukPelayanan::where('norec',$request['strukresep']['noresep'])->where('kdprofile', $kdProfile)->first();
                $noStruk = $SP->nostruk;
                $norecSP = $SP->norec;
                //<editor-fold desc="Description">

                //</editor-fold>
                //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//                $dataKembaliStok = DB::select(DB::raw("select spd.qtyproduk ,spd.hasilkonversi  ,spd.objectruanganfk ,
//                    spd.objectprodukfk,spd.harganetto
//                    from strukpelayanandetail_t as spd
//                    where spd.nostrukfk=:strukresepfk"),
//                    array(
//                        'strukresepfk' => $norecSP,
//                    )
//                );
//                foreach ($dataKembaliStok as $item5){
//                    $TambahStok = (float)$item5->qtyproduk*(float)$item5->hasilkonversi;
//                    $newSPD = StokProdukDetail::where('objectruanganfk',$item5->objectruanganfk)
//                        ->where('objectprodukfk',$item5->objectprodukfk)
////                    ->where('harganetto1',$item5->harganetto)
//                        ->orderby('tglkadaluarsa','desc')
//                        ->first();
//                    $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
//                    $newSPD->save();
//
//                    $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
//                        where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
//                        array(
//                            'ruanganfk' => $item5->objectruanganfk,
//                            'produkfk' => $item5->objectprodukfk,
//                        )
//                    );
//                    $saldoAwal=0;
//                    foreach ($dataSaldoAwal as $itemss){
//                        $saldoAwal = (float)$itemss->qty;
//                    }
//
//                    $newKS = new KartuStok();
//                    $norecKS = $newKS->generateNewId();
//                    $newKS->norec = $norecKS;
//                    $newKS->kdprofile = 0;
//                    $newKS->statusenabled = true;
//                    $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
//                    $newKS->keterangan = 'Ubah Resep Obat Bebas No. ' . $noStruk;
//                    $newKS->produkfk = $item5->objectprodukfk;
//                    $newKS->ruanganfk = $item5->objectruanganfk;//$item->ruanganfk;
//                    $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
//                    $newKS->status = 1;
//                    $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
//                    $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
//                    $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
//                    $newKS->save();
//                }
//                //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//
//                $delSPD = StrukPelayananDetail::where('nostrukfk',$request['strukresep']['noresep'])
//                    ->delete();
            }

            $SP->objectkelompoktransaksifk = 2;
            $SP->keteranganlainnya = $req['strukresep']['keteranganlainnya'];
            $SP->namapasien_klien = $req['strukresep']['namapasien'];
            $SP->nostruk_intern = $req['strukresep']['nocm'];
            $SP->namarekanan = 'Umum/Tunai';
            $SP->tglfaktur =  $req['strukresep']['tglLahir'];//tgllahir
            $SP->noteleponfaks =  $req['strukresep']['noTelepon'];//notlp
            $SP->namatempattujuan =  $req['strukresep']['alamat'];//alamat
            $SP->objectpegawaipenanggungjawabfk = $req['strukresep']['penulisresepfk'];
            $SP->tglstruk = $req['strukresep']['tglresep'];
            $SP->totalharusdibayar = $req['strukresep']['totalharusdibayar'];
            $SP->objectruanganfk = $req['strukresep']['ruanganfk'];
            $SP->namakurirpengirim = $req['strukresep']['karyawan'];
            $SP->save();
            $namaPasien = $req['strukresep']['nocm'] . ' ' . $req['strukresep']['namapasien'];
            $nocmNama = $req['strukresep']['nocm'] . '-' . $req['strukresep']['namapasien'];
            if ($request['strukresep']['noresep'] != '-' ) {
                if ( $request['strukresep']['retur']!= '') {
                    $newSRetur = new StrukRetur();
                    $norecSRetur = $newSRetur->generateNewId();
                    $noRetur = $this->generateCode(new StrukRetur, 'noretur', 12, 'Ret/' . $this->getDateTime()->format('ym') . '/', $kdProfile);
                    $newSRetur->norec = $norecSRetur;
                    $newSRetur->kdprofile = $kdProfile;
                    $newSRetur->statusenabled = true;
                    $newSRetur->objectkelompoktransaksifk = 50;
                    $newSRetur->keteranganalasan = $request['strukresep']['alasan'];
                    $newSRetur->keteranganlainnya = 'RETUR OBAT BEBAS';
                    $newSRetur->noretur = $noRetur;
                    $newSRetur->objectruanganfk = $request['strukresep']['ruanganfk'];
                    $newSRetur->objectpegawaifk = $request['strukresep']['pegawairetur'];
                    $newSRetur->tglretur = $this->getDateTime()->format('Y-m-d H:i:s');
                    $newSRetur->strukresepfk = $norecSP;
                    $newSRetur->save();
                    $transStatus = 'false';

                    $norec_retur = $newSRetur->norec ;

                    $r_PP = $request['details'];
                    foreach ($r_PP as $r_PPLXXXX) {
                        if ((int)$r_PPLXXXX['jmlretur'] != 0) {
                            $newPPR = new PelayananPasienRetur();
                            $norecPPR = $newPPR->generateNewId();
                            $newPPR->norec = $norecPPR;
                            $newPPR->kdprofile = $kdProfile;
                            $newPPR->statusenabled = true;
//                            $newPPR->noregistrasifk = $r_PPLXXXX['noregistrasifk'];
//                            $newPPR->tglregistrasi = $r_PPLXXXX['tglregistrasi'];
                            $newPPR->aturanpakai = $r_PPLXXXX['aturanpakai'];
                            $newPPR->generik = $r_PPLXXXX['generik'];
                            $newPPR->hargadiscount = $r_PPLXXXX['hargadiscount'];
                            $newPPR->hargajual = $r_PPLXXXX['hargajual'];
                            $newPPR->hargasatuan = $r_PPLXXXX['hargasatuan'];
                            $newPPR->jenisobatfk = $r_PPLXXXX['jenisobatfk'];
                            $newPPR->jumlah = $r_PPLXXXX['jmlretur'];
//                            $newPPR->kelasfk = $r_PPLXXXX['kelasfk'];
                            $newPPR->kdkelompoktransaksi = 1;
                            $newPPR->produkfk = $r_PPLXXXX['produkfk'];
                            $newPPR->routefk = $r_PPLXXXX['routefk'];
                            $newPPR->stock = $r_PPLXXXX['stock'];
                            $newPPR->tglpelayanan = $req['strukresep']['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newPPR->harganetto = $r_PPLXXXX['harganetto'];
                            $newPPR->jeniskemasanfk = $r_PPLXXXX['jeniskemasanfk'];
                            $newPPR->rke = $r_PPLXXXX['rke'];
                            $newPPR->strukresepfk = $norecSP;
                            $newPPR->satuanviewfk = $r_PPLXXXX['satuanviewfk'];
                            $newPPR->nilaikonversi = (float)$r_PPLXXXX['nilaikonversi'];
                            $newPPR->strukterimafk = $r_PPLXXXX['nostrukterimafk'];
                            $newPPR->dosis = $r_PPLXXXX['dosis'];
                            $newPPR->keteranganlain = $nocmNama;
                            if ((int)$r_PPLXXXX['jumlah'] == 0) {
                                $newPPR->jasa = $r_PPLXXXX['jasa'];
                            } else {
                                $newPPR->jasa = 0;
                            }
                            $newPPR->strukreturfk = $norec_retur;
                            $newPPR->save();

                            //## TAMBAH STOK DARI RETUR
                            $TambahStok = (float)$r_PPLXXXX['jmlretur'] * (float)$r_PPLXXXX['nilaikonversi'];//$r_PPLXXXX['jmlretur'];
                            $newSPD = StokProdukDetail::where('nostrukterimafk',$r_PPLXXXX['nostrukterimafk'])
                                ->where('kdprofile',$kdProfile)
                                ->where('objectruanganfk',$req['strukresep']['ruanganfk'])
                                ->where('objectprodukfk',$r_PPLXXXX['produkfk'])
                                ->orderby('tglkadaluarsa','desc')
                                //                        ->where('qtyproduk','>',0)
                                ->first();
                            $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
                            //                        try {
                            $newSPD->save();
                            $transStatus = 'true';
                            //                        } catch (\Exception $e) {
                            //                            $transStatus = 'false';
                            //                            $transMessage = "update Stok obat";
                            //                        }

                            $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                            where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                                array(
                                    'ruanganfk' => $req['strukresep']['ruanganfk'],
                                    'produkfk' =>  $r_PPLXXXX['produkfk'],
                                )
                            );
                            $saldoAwal=0;
                            foreach ($dataSaldoAwal as $itemss){
                                $saldoAwal = (float)$itemss->qty;
                            }

                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $kdProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                            $newKS->keterangan = 'Retur Resep Bebas No. ' . $noStruk .' '.$namaPasien;
                            $newKS->produkfk = $r_PPLXXXX['produkfk'];
                            $newKS->ruanganfk = $req['strukresep']['ruanganfk'];
                            $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
                            $newKS->status = 1;
                            $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                            $newKS->norectransaksi = $newSPD->norec;
                            $newKS->tabletransaksi = 'stokprodukdetail_t';
                            $newKS->flagfk = 3;
                            $newKS->save();
                            $transStatus = 'true';
                            //                        } catch (\Exception $e) {
                            //                            $transStatus = 'false';
                            //                            $transMessage = "Kartu Stok Ubah Resep";
                            //                        }

                            //##TAMBAH STOK DARI DELETE PELAYANANPASIEN_T
                            $TambahStok=0;
                            $TambahStok = (float)$r_PPLXXXX['jumlah'] * (float)$r_PPLXXXX['nilaikonversi'];//$r_PPLXXXX['jmlretur'];
                            $newSPD = StokProdukDetail::where('nostrukterimafk',$r_PPLXXXX['nostrukterimafk'])
                                ->where('kdprofile',$kdProfile)
                                ->where('objectruanganfk',$req['strukresep']['ruanganfk'])
                                ->where('objectprodukfk',$r_PPLXXXX['produkfk'])
                                ->orderby('tglkadaluarsa','desc')
                                //                        ->where('qtyproduk','>',0)
                                ->first();
                            $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
                            //                        try {
                            $newSPD->save();
                            //                            $transStatus = 'true';
                            //                        } catch (\Exception $e) {
                            //                            $transStatus = 'false';
                            //                            $transMessage = "update Stok obat";
                            //                        }

                            $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t 
                            where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                                array(
                                    'ruanganfk' => $req['strukresep']['ruanganfk'],
                                    'produkfk' =>  $r_PPLXXXX['produkfk'],
                                )
                            );
                            $saldoAwal=0;
                            foreach ($dataSaldoAwal as $itemss){
                                $saldoAwal = (float)$itemss->qty;
                            }

                            //                        KartuStok::where('keterangan',  'Ubah Resep Obat Bebas No. ' . $noStruk;)
//                            ->where('kdprofile', $kdProfile)
//                            ->update([
//                                    'flagfk' => null
//                            ]);

                            KartuStok::where('keterangan',  'Pelayanan Obat Bebas ' . $noStruk . ' ' . $namaPasien)
                                ->where('kdprofile', $kdProfile)
                                ->update([
                                    'flagfk' => null
                                ]);

                            $tglnow =  date('Y-m-d H:i:s');
                            $tglUbah = date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($tglnow)));

                            $newKS = new KartuStok();
                            $norecKS = $newKS->generateNewId();
                            $newKS->norec = $norecKS;
                            $newKS->kdprofile = $kdProfile;
                            $newKS->statusenabled = true;
                            $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
                            $newKS->keterangan = 'Ubah Resep No. ' . $noStruk;
                            $newKS->produkfk = $r_PPLXXXX['produkfk'];
                            $newKS->ruanganfk = $req['strukresep']['ruanganfk'];
                            $newKS->saldoawal = (float)$saldoAwal;//- (float)$qtyJumlah;
                            $newKS->status = 1;
                            $newKS->tglinput = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->tglkejadian = $tglUbah; //date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                            $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
                            $newKS->norectransaksi = $newSPD->norec;
                            $newKS->tabletransaksi = 'stokprodukdetail_t';
//                            $newKS->flagfk = 6;
                            $newKS->save();
                        }
                    }

                    $delSPD = StrukPelayananDetail::where('nostrukfk',$request['strukresep']['noresep'])
                        ->where('kdprofile', $kdProfile)
                        ->delete();

                }
//                else{
//                    //##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//                    $dataKembaliStok = DB::select(DB::raw("select pp.strukterimafk,pp.jumlah,pp.nilaikonversi,sr.ruanganfk,pp.produkfk
//                            from pelayananpasien_t as pp
//                            INNER JOIN strukresep_t sr on sr.norec=pp.strukresepfk
//                            where sr.norec=:strukresepfk"),
//                        array(
//                            'strukresepfk' => $norec_SR,
//                        )
//                    );
//                    foreach ($dataKembaliStok as $item5){
//                        $TambahStok = (float)$item5->jumlah;//*(float)$item5->nilaikonversi;
//                        $newSPD = StokProdukDetail::where('nostrukterimafk',$item5->strukterimafk)
//                            ->where('objectruanganfk',$item5->ruanganfk)
//                            ->where('objectprodukfk',$item5->produkfk)
//                            ->orderby('tglkadaluarsa','desc')
//                            //                        ->where('qtyproduk','>',0)
//                            ->first();
//                        $newSPD->qtyproduk = (float)$newSPD->qtyproduk + (float)$TambahStok;
//                        //                    try {
//                        $newSPD->save();
//                        //                        $transStatus = 'true';
//                        //                    } catch (\Exception $e) {
//                        //                        $transStatus = 'false';
//                        //                        $transMessage = "update Stok obat";
//                        //                    }
//
//                        $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
//                            where objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
//                            array(
//                                'ruanganfk' => $item5->ruanganfk,
//                                'produkfk' => $item5->produkfk,
//                            )
//                        );
//                        $saldoAwal=0;
//                        foreach ($dataSaldoAwal as $itemss){
//                            $saldoAwal = (float)$itemss->qty;
//                        }
//                        $namaPasien = $req['strukresep']['nocm'] . ' ' . $req['strukresep']['namapasien'];
//                        $newKS = new KartuStok();
//                        $norecKS = $newKS->generateNewId();
//                        $newKS->norec = $norecKS;
//                        $newKS->kdprofile = 0;
//                        $newKS->statusenabled = true;
//                        $newKS->jumlah = $TambahStok;//$r_PPL['jumlah'];
//                        $newKS->keterangan = 'Ubah Resep Bebas No. ' . $noStruk .' '.$namaPasien;
//                        $newKS->produkfk = $item5->produkfk;
//                        $newKS->ruanganfk = $r_SR['ruanganfk'];//$item->ruanganfk;
//                        $newKS->saldoawal = (float)$saldoAwal ;//- (float)$qtyJumlah;
//                        $newKS->status = 1;
//                        $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
//                        $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
//                        $newKS->nostrukterimafk =  $newSPD->nostrukterimafk;
//                        //                    try {
//                        $newKS->save();
//                        //                        $transStatus = 'true';
//                        //                    } catch (\Exception $e) {
//                        //                        $transStatus = 'false';
//                        //                        $transMessage = "Kartu Stok Ubah Resep";
//                        //                    }
//                    }
//                    //END##PENAMBAHAN KEMBALI STOKPRODUKDETAIL
//
//                    $HapusPP = PelayananPasien::where('strukresepfk', $norec_SR)->get();
//                    foreach ($HapusPP as $pp){
//                        $HapusPPD = PelayananPasienDetail::where('pelayananpasien', $pp['norec'])->delete();
//                        $HapusPPP = PelayananPasienPetugas::where('pelayananpasien', $pp['norec'])->delete();
//                    }
//                    $Edit = PelayananPasien::where('strukresepfk', $norec_SR)->delete();
//                }
                //### LOGACC untuk penjurnalan blm ada
            }

            foreach ($req['details'] as $item) {
                $qtyJumlah = (float)$item['jumlah'];

                $SPD = new StrukPelayananDetail();
                $norecKS = $SPD->generateNewId();
                $SPD->norec = $norecKS;
                $SPD->kdprofile = $kdProfile;
                $SPD->statusenabled = true;
                $SPD->nostrukfk = $SP->norec;
                $SPD->objectasalprodukfk = $item['asalprodukfk'];
                $SPD->objectjeniskemasanfk = $item['jeniskemasanfk'];
                $SPD->objectprodukfk = $item['produkfk'];
                $SPD->objectruanganfk = $item['ruanganfk'];
                $SPD->objectruanganstokfk = $item['ruanganfk'];
                $SPD->objectsatuanstandarfk = $item['satuanstandarfk'];
                $SPD->aturanpakai = $item['aturanpakai'];
                $SPD->hargadiscount = $item['hargadiscount'];
                $SPD->hargadiscountgive = 0;
                $SPD->hargadiscountsave = 0;
                $SPD->harganetto = $item['hargasatuan'];
                $SPD->hargapph = 0;
                $SPD->hargappn = 0;
                $SPD->hargasatuan = $item['hargasatuan'];
                $SPD->hasilkonversi = (float)$item['nilaikonversi'];
                $SPD->namaproduk = $item['namaproduk'];
                $SPD->resepke = $item['rke'];
                $SPD->hargasatuandijamin = 0;
                $SPD->hargasatuanppenjamin = 0;
                $SPD->hargasatuanpprofile = 0;
                $SPD->hargatambahan = $item['jasa'];
                $SPD->isonsiteservice = 0;
                $SPD->kdpenjaminpasien = 0;
                $SPD->persendiscount = 0;
                $SPD->qtyproduk = $item['jumlah'];
                $SPD->qtyprodukoutext = 0;
                $SPD->qtyprodukoutint = 0;
                $SPD->qtyprodukretur = 0;
                $SPD->satuan = '-';
                $SPD->satuanstandar = $item['satuanviewfk'];
                $SPD->tglpelayanan = $req['strukresep']['tglresep'];
                $SPD->is_terbayar = 0;
                $SPD->linetotal = 0;
                $SPD->save();

                //## StokProdukDetail
                $GetNorec = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('objectruanganfk', $item['ruanganfk'])
                    ->where('objectasalprodukfk', $item['asalprodukfk'])
                    ->where('objectprodukfk', $item['produkfk'])
                    ->where('qtyproduk', '>', 0)
                    ->select('norec')
                    ->get();

                $jmlPengurang = (float)$qtyJumlah;
                $kurangStok = (float)0;
                foreach ($GetNorec as $item2) {
                    $newSPD = StokProdukDetail::where('nostrukterimafk', $item['nostrukterimafk'])
                        ->where('kdprofile', $kdProfile)
                        ->where('objectruanganfk', $item['ruanganfk'])
                        ->where('objectasalprodukfk', $item['asalprodukfk'])
                        ->where('objectprodukfk', $item['produkfk'])
                        ->where('norec', $item2->norec)
                        ->where('qtyproduk', '>', 0)
                        ->first();

                    if ((float)$newSPD->qtyproduk <= (float)$jmlPengurang) {
                        $kurangStok = (float)$newSPD->qtyproduk;
                        $jmlPengurang = (float)$jmlPengurang - (float)$kurangStok;
                    } else {
                        $kurangStok = (float)$jmlPengurang;
                        $jmlPengurang = (float)$jmlPengurang - (float)$kurangStok;
                    }

                    $newSPD->qtyproduk = (float)$newSPD->qtyproduk - (float)$kurangStok;//$r_PPL['jumlah'];
                    $dadada[] = array('kurangStok' => (float)$kurangStok, 'jmlPengurang' => (float)$jmlPengurang, 'stok' => (float)$newSPD->qtyproduk);
                    $newSPD->save();
                }

                $dataSaldoAwal = DB::select(DB::raw("select sum(qtyproduk) as qty from stokprodukdetail_t
                  where kdprofile = $kdProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $req['strukresep']['ruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );

                foreach ($dataSaldoAwal as $items) {
                    $saldoAwal = (float)$items->qty;
                }


                //## KartuStok
                $newKS = new KartuStok();
                $norecKS = $newKS->generateNewId();
                $newKS->norec = $norecKS;
                $newKS->kdprofile = $kdProfile;
                $newKS->statusenabled = true;
                $newKS->jumlah = $qtyJumlah;
                $newKS->keterangan = 'Pelayanan Obat Bebas ' . $noStruk . ' ' . $namaPasien;
                $newKS->produkfk = $item['produkfk'];
                $newKS->ruanganfk = $req['strukresep']['ruanganfk'];
                $newKS->saldoawal = (float)$saldoAwal;//- (float)$qtyJumlah;
                $newKS->status = 0;
                $newKS->tglinput = date('Y-m-d H:i:s');//$r_SR['tglresep'];//$r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->tglkejadian = date('Y-m-d H:i:s');//$r_SR['tglresep'];// $r_SR['tglresep']->format('Y-m-d H:i:s');
                $newKS->nostrukterimafk = $item['nostrukterimafk'];
                $newKS->norectransaksi = $SP->norec;
                $newKS->tabletransaksi = 'strukpelayanan_t';
                $newKS->flagfk = 7;
                $newKS->save();

            }
//        }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Struk Pelayanan Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data" => $SP,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = "Simpan Struk Pelayanan Gagal!!";
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

    public function getLaporanPengeluaranObat(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal= $request['tglAwal'];
        $tglAkhir= $request['tglAkhir'];
        $dokid= '';
        $kpid='';
        $ruid='';

        if(isset($request['dokid']) && $request['dokid']!="" && $request['dokid']!="undefined"){
            $dokid = ' and pg.id='.$request['dokid'];
        }
        if(isset($request['kpid']) && $request['kpid']!="" && $request['kpid']!="undefined"){
            $kpid = ' and pd.objectkelompokpasienlastfk='.$request['kpid'];
        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $ruid = ' and ru.id='.$request['ruid'];
        }


        $data=DB::select(DB::raw("SELECT x.tglresep,x.noresep,x.noregistrasi,x.nocm,x.namapasien,x.jeniskelamin,x.kelompokpasien,x.namalengkap,  
              x.ruanganapotik,SUM(x.tunai) AS tunai,SUM(x.penjamin) AS penjamin FROM(SELECT to_char(sr.tglresep, 'yyyy-MM-dd') AS tglresep,sr.noresep,pd.noregistrasi,ps.nocm,UPPER(ps.namapasien) AS namapasien,  
              UPPER(jk.reportdisplay) AS jeniskelamin,kp.kelompokpasien,CASE WHEN pg.namalengkap IS NULL THEN '-' ELSE pg.namalengkap END AS namalengkap,ru.namaruangan AS ruanganapotik,  
              CASE WHEN pd.objectkelompokpasienlastfk = 1  THEN CAST(sp.totalharusdibayar AS FLOAT) ELSE 0 END AS tunai,
              CASE WHEN pd.objectkelompokpasienlastfk = 2 THEN CAST(sp.totalprekanan AS FLOAT) 
              WHEN pd.objectkelompokpasienlastfk in (3,8,11,12,13,14,15,16,17) THEN CAST(sp.totalharusdibayar AS FLOAT) ELSE 0 END AS penjamin   
              FROM strukresep_t as sr  
              INNER JOIN pelayananpasien_t AS pp ON pp.strukresepfk = sr.norec  
              INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec=sr.pasienfk  
              INNER JOIN pasiendaftar_t AS pd ON pd.norec=apd.noregistrasifk  
              INNER JOIN pasien_m AS ps ON ps.id=pd.nocmfk  
              LEFT JOIN jeniskelamin_m AS jk ON jk.id=ps.objectjeniskelaminfk  
              LEFT JOIN pegawai_m AS pg ON pg.id=sr.penulisresepfk  
              LEFT JOIN ruangan_m AS ru ON ru.id=sr.ruanganfk  
              LEFT JOIN kelompokpasien_m kp ON kp.id=pd.objectkelompokpasienlastfk  
              INNER JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk 
              WHERE sr.kdprofile = $kdProfile and sr.tglresep BETWEEN '$tglAwal' and '$tglAkhir'  
              $dokid
              $kpid
              $ruid
              GROUP BY sr.tglresep,sr.noresep,pd.noregistrasi,ps.nocm,ps.namapasien,jk.reportdisplay,kp.kelompokpasien,
				 pg.namalengkap,ru.namaruangan,pd.objectkelompokpasienlastfk,sp.totalharusdibayar,sp.totalprekanan
            UNION ALL  

              SELECT to_char(sp.tglstruk, 'yyyy-MM-dd')  AS tglresep,sp.nostruk AS noresep,'-' AS noregistrasi,'-' AS nocm,  
              UPPER(sp.namapasien_klien) AS namapasien,'-' AS jeniskelamin,'Umum/Pribadi' as kelompokpasien,  
              CASE WHEN pg.namalengkap IS NULL THEN '-' ELSE pg.namalengkap END AS namalengkap,ru.namaruangan AS ruanganapotik,  
              sp.totalharusdibayar AS tunai, 0 AS penjamin  
              FROM strukpelayanan_t as sp  
              LEFT JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
              LEFT JOIN pegawai_m as pg on pg.id=sp.objectpegawaipenanggungjawabfk  
              INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk  
              LEFT JOIN pegawai_m as pg2 on pg2.id = sbm.objectpegawaipenerimafk  
              LEFT JOIN loginuser_s as lu on lu.id = sbm.objectpegawaipenerimafk  
              LEFT JOIN pegawai_m as pg3 on pg3.id = lu.objectpegawaifk  
              LEFT JOIN ruangan_m as ru on ru.id=sp.objectruanganfk  
              WHERE sp.kdprofile = $kdProfile and sp.tglstruk BETWEEN '$tglAwal' and '$tglAkhir'
                    AND sp.nostruk_intern='-' AND substring(sp.nostruk,1,2)='OB'  
                    $dokid
                    $ruid
              GROUP BY sp.tglstruk,sp.nostruk,sp.namapasien_klien,pg.namalengkap,ru.namaruangan,sp.totalharusdibayar
                    
            UNION ALL 
             
              SELECT  to_char(sp.tglstruk, 'yyyy-MM-dd')  AS tglresep,sp.nostruk AS noresep,'-' AS noregistrasi,ps.nocm,  
              UPPER(sp.namapasien_klien) AS namapasien,UPPER(jk.reportdisplay) AS jeniskelamin,  
              'Umum/Pribadi' as kelompokpasien,CASE WHEN pg.namalengkap IS NULL THEN '-' ELSE pg.namalengkap END AS namalengkap,  
              ru.namaruangan AS ruanganapotik,sp.totalharusdibayar AS tunai, 0 AS penjamin  
              FROM strukpelayanan_t as sp  
              INNER JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec  
              INNER JOIN strukbuktipenerimaan_t as sbm on sbm.norec = sp.nosbmlastfk  
              LEFT JOIN pegawai_m as pg2 on pg2.id = sbm.objectpegawaipenerimafk  
              LEFT JOIN loginuser_s as lu on lu.objectpegawaifk = sbm.objectpegawaipenerimafk  
              LEFT JOIN pasien_m as ps on ps.nocm=sp.nostruk_intern  
              LEFT JOIN jeniskelamin_m as jk on jk.id=ps.objectjeniskelaminfk  
              LEFT JOIN pegawai_m as pg on pg.id=sp.objectpegawaipenanggungjawabfk  
              LEFT JOIN ruangan_m as ru on ru.id=sp.objectruanganfk  
              WHERE sp.kdprofile = $kdProfile and sp.tglstruk BETWEEN '$tglAwal' and '$tglAkhir'
                AND sp.nostruk_intern not in ('-') AND substring(sp.nostruk,1,2)='OB'  
                $dokid
               $ruid
              GROUP BY sp.tglstruk,sp.nostruk,sp.namapasien_klien,jk.reportdisplay,ps.nocm,
				       pg.namalengkap,ru.namaruangan,sp.totalharusdibayar
              ) AS x GROUP BY x.tglresep,x.noresep,x.noregistrasi,x.nocm,x.namapasien,  
              x.jeniskelamin,x.kelompokpasien,x.namalengkap,x.ruanganapotik
              ORDER BY x.tglresep asc"));




        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getLaporanPenyerahanObat(Request $request){
        $tglAwal= $request['tglAwal'];
        $tglAkhir= $request['tglAkhir'];
        $kdProfile = (int) $this->getDataKdProfile($request);

        $data=DB::table('strukresep_t as sr ')
            ->LEFTJOIN('antrianapotik_t as aa', 'aa.noresep', '=', 'sr.noresep')
            ->LEFTJOIN('strukorder_t as so', 'so.norec', '=', 'sr.orderfk')
            ->LEFTJOIN('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'sr.pasienfk')
            ->LEFTJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->LEFTJOIN('pasien_m as pm', 'pm.id', '=', 'pd.nocmfk')
            ->LEFTJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'pm.objectjeniskelaminfk')
            ->LEFTJOIN('alamat_m as alm', 'alm.nocmfk', '=', 'pm.id')
            ->LEFTJOIN( 'ruangan_m as ru', 'ru.id', '=', 'sr.ruanganfk')
            ->where('sr.kdprofile', $kdProfile)
            ->WHEREBETWEEN('sr.tglresep', [$tglAwal, $tglAkhir])
            ->WHERENOTNULL('aa.noantri')
            ->SELECT('so.noorder',
                'sr.noresep',
                'pm.nocm',
                'pd.noregistrasi',
                'pm.namapasien',
                'jk.jeniskelamin',
                'so.tglorder',
                'sr.tglresep as tglverifikasi',
                DB::raw("CONCAT(aa.jenis,'-', aa.noantri) AS noantri"),
                'so.namapengambilorder',
                'so.tglambilorder',
                'ru.namaruangan as namaruanganapotik',
                'so.keterangankeperluan');

        if(isset($request['jeniskemasan']) && $request['jeniskemasan']!="" && $request['jeniskemasan']!="undefined"){
            if($request['jeniskemasan'] == 1){
                $data = $data->where('aa.jenis', 'R');
            }
            else{
                $data = $data->where('aa.jenis', 'N');
            }

        }

        if(isset($request['IdFarmasi']) && $request['IdFarmasi']!="" && $request['IdFarmasi']!="undefined"){
            $data = $data->where('sr.ruanganfk', $request['IdFarmasi']);
        }

        $data= $data->get();


        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getLaporanRekapitulasiPelayanan(Request $request){
        $tglAwal= $request['tglAwal'];
        $tglAkhir= $request['tglAkhir'];
        $kdProfile = (int) $this->getDataKdProfile($request);

//        $data=DB::select(DB::raw("SELECT x.kelompokpasien, COUNT(x.kelompokpasien) AS jumlah FROM
//            (SELECT
//            kp.kelompokpasien, sr.tglresep
//            FROM
//            dbo.pelayananpasien_t AS pp
//            INNER JOIN dbo.strukresep_t AS sr ON sr.norec = pp.strukresepfk
//            INNER JOIN dbo.antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
//            INNER JOIN dbo.pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
//            INNER JOIN dbo.kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
//            WHERE sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir'
//
//            UNION ALL
//
//            SELECT
//            'Umum/Pribadi' AS kelompokpasien, sp.tglstruk AS tglresep
//            FROM
//            dbo.strukpelayanan_t AS sp
//            INNER JOIN dbo.strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
//            WHERE sp.objectkelompoktransaksifk=2 and sp.keteranganlainnya='Penjualan Obat Bebas'
//            AND sp.tglstruk BETWEEN '$tglAwal' AND '$tglAkhir'
//
//            UNION ALL
//
//            SELECT
//            CASE WHEN pr.isfornas=1 THEN 'FORNAS' ELSE 'NON FORNAS' END AS \"kelompokpasien\", sr.tglresep
//            FROM
//            dbo.pelayananpasien_t AS pp
//            INNER JOIN dbo.produk_m AS pr ON pr.id = pp.produkfk
//            INNER JOIN dbo.strukresep_t AS sr ON sr.norec = pp.strukresepfk
//            WHERE sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir'
//
//            UNION ALL
//
//            SELECT
//            CASE WHEN pr.isgeneric=1 THEN 'GENERIC' ELSE 'NON GENERIC' END AS \"kelompokpasien\", sr.tglresep
//            FROM
//            dbo.pelayananpasien_t AS pp
//            INNER JOIN dbo.produk_m AS pr ON pr.id = pp.produkfk
//            INNER JOIN dbo.strukresep_t AS sr ON sr.norec = pp.strukresepfk
//            WHERE sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir'
//            ) AS x
//            GROUP BY x.kelompokpasien"));

        $idfarmasiSR='';
        $idfarmasiSP='';
        if(isset($request['IdFarmasi']) && $request['IdFarmasi']!="" && $request['IdFarmasi']!="undefined"){
            $idfarmasiSR = 'AND sr.ruanganfk=' .$request['IdFarmasi'];
            $idfarmasiSP = 'AND sp.objectruanganfk=' .$request['IdFarmasi'];
        }


        $data=DB::select(DB::raw("select x.namaruangan, count(x.noresep) as jmlresep, cast(sum(x.harga) as float) as hargaa
                        from
                    (SELECT
                        sr.ruanganfk, rm.namaruangan , sr.noresep, sum(pp.hargasatuan) as harga
                        FROM
                        pelayananpasien_t AS pp
                        INNER JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
                        INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                        INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                        INNER JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
                        inner join ruangan_m rm on rm.id = sr.ruanganfk 
                        inner join departemen_m dm on dm.id = rm.objectdepartemenfk 
                        where sr.kdprofile = $kdProfile and dm.id = 14 and sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir' $idfarmasiSR
                        group by sr.ruanganfk ,rm.namaruangan , sr.noresep) as x 
                group by x.namaruangan
                "));
        // SELECT x.kelompokpasien, COUNT(x.noresep) AS jumlahresep, SUM(x.JumlahRKE) AS jumlahRke FROM
        //     (SELECT
        //     kp.kelompokpasien, sr.noresep, COUNT(pp.rke) AS JumlahRKE
        //     FROM
        //     pelayananpasien_t AS pp
        //     INNER JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
        //     INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
        //     INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
        //     INNER JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
        //     WHERE sr.kdprofile = $kdProfile and sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir' $idfarmasiSR
        //     GROUP BY kp.kelompokpasien, sr.noresep
            
        //     UNION ALL
            
        //     SELECT
        //     'Umum/Pribadi' AS kelompokpasien, sp.nostruk AS noresep, COUNT(*) AS JumlahRKE
        //     FROM
        //     strukpelayanan_t AS sp
        //     INNER JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
        //     WHERE sp.kdprofile = $kdProfile and sp.objectkelompoktransaksifk=2 and sp.keteranganlainnya='Penjualan Obat Bebas'
        //     AND sp.tglstruk BETWEEN '$tglAwal' AND '$tglAkhir' $idfarmasiSP
        //     GROUP BY sp.nostruk
            
        //     UNION ALL
            
        //     SELECT
        //     CASE WHEN pr.isfornas=true THEN 'FORNAS' ELSE 'NON FORNAS' END AS \"kelompokpasien\", sr.noresep, COUNT(pp.rke) AS JumlahRKE
        //     FROM
        //     pelayananpasien_t AS pp
        //     INNER JOIN produk_m AS pr ON pr.id = pp.produkfk
        //     INNER JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
        //     WHERE pp.kdprofile = $kdProfile and sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir' $idfarmasiSR
        //     GROUP BY pr.isfornas, sr.noresep
            
        //     UNION ALL
            
        //     SELECT
        //     CASE WHEN pr.isgeneric=true THEN 'GENERIC' ELSE 'NON GENERIC' END AS \"kelompokpasien\", sr.noresep, COUNT(pp.rke) AS JumlahRKE
        //     FROM
        //     pelayananpasien_t AS pp
        //     INNER JOIN produk_m AS pr ON pr.id = pp.produkfk
        //     INNER JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
        //     WHERE pp.kdprofile = $kdProfile and sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir' $idfarmasiSR
        //     GROUP BY pr.isgeneric, sr.noresep
            
        //     UNION ALL
            
        //     SELECT
        //     'ANTIBIOTIK' AS \"kelompokpasien\", sr.noresep, COUNT(pp.rke) AS JumlahRKE
        //     FROM
        //     pelayananpasien_t AS pp
        //     INNER JOIN produk_m AS pr ON pr.id = pp.produkfk
        //     INNER JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
        //     WHERE sr.kdprofile = $kdProfile and sr.tglresep BETWEEN '$tglAwal' AND '$tglAkhir' AND pr.isantibiotik=true $idfarmasiSR
        //     GROUP BY sr.noresep
        //     ) AS x
        //     GROUP BY x.kelompokpasien 
            


        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getLaporanPenjualanObatPerKwitansi(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal= $request['tglAwal'];
        $tglAkhir= $request['tglAkhir'];
        $dokid= '';
        $kpid='';
        $ruid='';
        $paramKasir = '';

        if(isset($request['dokid']) && $request['dokid']!="" && $request['dokid']!="undefined"){
            $dokid = ' and pg.id='.$request['dokid'];
        }
        if(isset($request['kpid']) && $request['kpid']!="" && $request['kpid']!="undefined"){
            $kpid = ' and pd.objectkelompokpasienlastfk='.$request['kpid'];
        }
        if(isset($request['ruid']) && $request['ruid']!="" && $request['ruid']!="undefined"){
            $ruid = ' and ru.id='.$request['ruid'];
        }

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

        $data = DB::select(DB::raw("
            
            SELECT x.tglregistrasi,x.namaruangan,x.tglresep,x.noresep,x.nocm,x.noregistrasi,x.namapasien,x.kelompokpasien,
			x.tglpulang,x.tglsbm,x.nomorverif,x.nosbm,x.totaldibayar,x.kasir,'Pelayanan Resep' AS keterangan
            FROM(SELECT pd.tglregistrasi,ru.namaruangan,sr.tglresep,sr.noresep,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,			 
                        pd.tglpulang,sbm.tglsbm,sp.nostruk as nomorverif,sbm.nosbm,sbm.totaldibayar,pg.namalengkap as kasir,
                        row_number() over (partition by pd.noregistrasi order by pd.tglregistrasi desc) as rownum 
            FROM pelayananpasien_t AS pp
            INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
            INNER JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
            INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
            INNER JOIN pasien_m AS pm ON pm.id = pd.nocmfk
            LEFT JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
            INNER JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk
            LEFT JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = sp.norec AND sbm.statusenabled = true
            LEFT JOIN loginuser_s AS lu ON lu.id = sbm.objectpegawaipenerimafk
            LEFT JOIN pegawai_m AS pg ON pg.id = lu.objectpegawaifk
            LEFT JOIN produk_m AS pr ON pr.id = pp.produkfk
            LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
            WHERE pp.kdprofile = $kdProfile AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir' 
                  AND pd.objectkelompokpasienlastfk <> 2 AND pp.strukresepfk IS NOT NULL
                  $ruid
                  $paramKasir
            ) AS x
            WHERE x.rownum = 1
                 
            UNION ALL
            
            SELECT  x.tglregistrasi,x.namaruangan,x.tglresep,x.noresep,x.nocm,x.noregistrasi,x.namapasien,x.kelompokpasien,			 
                    x.tglpulang,x.tglsbm,x.nomorverif,x.nosbm,x.totaldibayar,x.kasir,'Pelayanan Resep Obat Kronis' AS keterangan
            FROM(SELECT pd.tglregistrasi,ru.namaruangan,sr.tglresep,sr.noresep,pm.nocm,pd.noregistrasi,pm.namapasien,kp.kelompokpasien,			 
                        pd.tglpulang,sbm.tglsbm,sp.nostruk as nomorverif,sbm.nosbm,sbm.totaldibayar,pg.namalengkap as kasir,
                        row_number() over (partition by pd.noregistrasi order by pd.tglregistrasi desc) as rownum 
            FROM strukresep_t AS sr
            INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = sr.pasienfk
            INNER JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
            INNER JOIN pelayananpasien_t AS pp ON pp.strukresepfk = sr.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
            INNER JOIN strukpelayanan_t AS sp ON sp.norec = pp.strukfk
            INNER JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = sp.norec AND sbm.statusenabled = TRUE
            LEFT JOIN loginuser_s AS lu ON lu. ID = sbm.objectpegawaipenerimafk
            LEFT JOIN pegawai_m AS pg ON pg. ID = lu.objectpegawaifk
            INNER JOIN pasien_m AS pm ON pm.id = pd.nocmfk
            LEFT JOIN kelompokpasien_m AS kp ON kp.id = pd.objectkelompokpasienlastfk
            WHERE sr.kdprofile = $kdProfile AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir' 
                  AND pp.iskronis = true AND pd.objectkelompokpasienlastfk=2
                  $ruid
                  $paramKasir                 
                  ) AS x
            WHERE x.rownum = 1
                          
            UNION ALL
            
            SELECT sp.tglstruk AS tglregistrasi,ru.namaruangan,sp.tglstruk AS tglresep,sp.nostruk AS noresep,
                   '' AS nocm,'' AS noregistrasi,sp.namapasien_klien AS namapasien,'Umum/Pribadi' AS kelompokpasien,			 
                   sp.tglstruk AS tglpulang,sbm.tglsbm,sbm.nosbm as nomorverif,sbm.nosbm,sbm.totaldibayar,
                   pg.namalengkap as kasir,'Pelayanan Resep Non Layanan' AS keterangan
             FROM strukpelayanan_t AS sp
             INNER JOIN strukbuktipenerimaan_t AS sbm ON sbm.nostrukfk = sp.norec AND sbm.statusenabled = true
             LEFT JOIN ruangan_m AS ru ON ru.id = sp.objectruanganfk
             LEFT JOIN loginuser_s AS lu ON lu. ID = sbm.objectpegawaipenerimafk
             LEFT JOIN pegawai_m AS pg ON pg. ID = lu.objectpegawaifk
             WHERE sp.kdprofile = $kdProfile AND SUBSTRING(sp.nostruk,1,2) = 'OB' AND sbm.tglsbm BETWEEN '$tglAwal' and '$tglAkhir' 
             $ruid                 
             $paramKasir
        "));

        $result = array(
            'daftar' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
}