<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 11/19/2020
 * Time: 2:25 AM
 */

namespace App\Http\Controllers\BankDarah;

use App\Http\Controllers\ApiController;
use App\Transaksi\KartuStok;
use App\Transaksi\KirimProduk;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\StrukKirim;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;
use Webpatser\Uuid\Uuid;




class BankDarahController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getComboBankDarah(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap,lu.objectkelompokuserfk
                from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $dataLogin['userData']['id'],
            )
        );
        $data = \DB::table('loginuser_s as lu')
            ->JOIN('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('pg.id','pg.namalengkap')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id',$request['userData']['id'])
            ->get();

        $dataProduk = \DB::table('produk_m as pr')
            ->JOIN('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->JOIN('jenisproduk_m as jp','jp.id','=','djp.objectjenisprodukfk')
            ->leftJOIN('satuanstandar_m as ss','ss.id','=','pr.objectsatuanstandarfk')
            ->JOIN('stokprodukdetail_t as spd','spd.objectprodukfk','=','pr.id')
            ->select('pr.id','pr.kdproduk as kdsirs','pr.namaproduk','ss.id as ssid','ss.satuanstandar')
            ->where('pr.statusenabled',true)
            ->where('pr.kdprofile', $idProfile)
            ->where('pr.id',4048843)
//            ->where('jp.id',97)
            ->where('spd.qtyproduk','>',0)
            ->groupBy('pr.id','pr.kdproduk','pr.namaproduk','ss.id','ss.satuanstandar')
            ->orderBy('pr.namaproduk')
            ->get();

        $dataKonversiProduk = \DB::table('konversisatuan_t as ks')
            ->JOIN('satuanstandar_m as ss','ss.id','=','ks.satuanstandar_asal')
            ->JOIN('satuanstandar_m as ss2','ss2.id','=','ks.satuanstandar_tujuan')
            ->select('ks.objekprodukfk','ks.satuanstandar_asal','ss.satuanstandar','ks.satuanstandar_tujuan','ss2.satuanstandar as satuanstandar2',
                'ks.nilaikonversi')
            ->where('ks.kdprofile', $idProfile)
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
                'kdsirs' => $item->kdsirs,
                'kdproduk' => $item->kdsirs,
                'ssid' =>   $item->ssid,
                'satuanstandar' =>   $item->satuanstandar,
                'konversisatuan' => $satuanKonversi,
            );
        }


        $result = array(
            'pegawai' => $data,
            'datalogin' => $dataLogin,
            'produk' => $dataProdukResult,
            'by' => 'as@epic',
        );
        return $this->respond($result);
    }
    public function savePemakaianDarah(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
//        return $this->respond(array(date($request['strukkirim']['tglkirim'])));
        if ($request['strukkirim']['jenispermintaanfk'] == 2) {
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'TRF-' . $this->getDateTime()->format('ym'), $idProfile);
        }else{
            $noKirim = $this->generateCodeBySeqTable(new StrukKirim, 'nokirim', 14, 'AMP-' . $this->getDateTime()->format('ym'), $idProfile);
        }
        if ($noKirim == ''){
            $transMessage = "Gagal mengumpukan data, Coba lagi.!";
            \DB::rollBack();
            $result = array(
                "status" => 400,
                "NOKIRIM" => $noKirim,
                "message"  => $transMessage,
                "as" => 'as@epic',
            );
            return $this->setStatusCode($result['status'])->respond($result, $transMessage);
        }
        DB::beginTransaction();
        $ruanganAsal = DB::select(DB::raw("
                    select  ru.namaruangan
                    from ruangan_m as ru 
                    where ru.kdprofile = $idProfile and ru.id=:id"),
            array(
                'id' => $request['strukkirim']['objectruanganfk'],
            )
        );
        $strRuanganAsal ='';
        $strRuanganAsal = $ruanganAsal[0]->namaruangan;

        $ruanganTujuan = DB::select(DB::raw("
                     select  ru.namaruangan
                     from ruangan_m as ru 
                    where ru.kdprofile = $idProfile and ru.id=:id"),
            array(
                'id' => $request['strukkirim']['objectruangantujuanfk'],
            )
        );
        $strRuanganTujuan='';
        $strRuanganTujuan = $ruanganTujuan[0]->namaruangan;

        try{

            $dataSK = new StrukKirim();
            $dataSK->norec = $dataSK->generateNewId();
            $dataSK->nokirim = $noKirim;


            $dataSK->kdprofile = $idProfile;
            $dataSK->statusenabled = true;
            $dataSK->objectpegawaipengirimfk = $request['strukkirim']['objectpegawaipengirimfk'];
            $dataSK->objectruanganasalfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruanganfk = $request['strukkirim']['objectruanganfk'];
            $dataSK->objectruangantujuanfk = $request['strukkirim']['objectruangantujuanfk'];
            $dataSK->jenispermintaanfk = $request['strukkirim']['jenispermintaanfk'];
            $dataSK->objectkelompoktransaksifk = 33;
            $dataSK->keteranganlainnyakirim =$request['strukkirim']['keteranganlainnyakirim'];
            $dataSK->qtydetailjenisproduk = 0;
            $dataSK->qtyproduk = $request['strukkirim']['qtyproduk'];
            $dataSK->tglkirim = date($request['strukkirim']['tglkirim']);
            $dataSK->totalbeamaterai = 0;
            $dataSK->totalbiayakirim = 0;
            $dataSK->totalbiayatambahan = 0;
            $dataSK->totaldiscount = 0;
            $dataSK->totalhargasatuan = $request['strukkirim']['totalhargasatuan'];
            $dataSK->totalharusdibayar = 0;
            $dataSK->totalpph =0;
            $dataSK->totalppn = 0;
            $dataSK->noregistrasifk = $request['strukkirim']['norec_apd'];
            $dataSK->noorderfk = $request['strukkirim']['norecOrder'];
            $dataSK->save();

            $norecSK = $dataSK->norec;

            foreach ($request['details'] as $item) {
                //cari satuan standar
                $satuanstandar = DB::select(DB::raw("
                     select  ru.objectsatuanstandarfk
                     from produk_m as ru 
                    where ru.kdprofile = $idProfile and ru.id=:id"),
                    array(
                        'id' => $item['produkfk'],
                    )
                );
                $satuanstandarfk = $satuanstandar[0]->objectsatuanstandarfk;


                if ($request['strukkirim']['jenispermintaanfk'] == 1) {
                    //PENGIRIM AMPRAHAN
                    $dataSaldoAwalK = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec,objectasalprodukfk as asalprodukfk,
                        hargadiscount,harganetto1 as harganetto,harganetto1 as hargasatuan
                        from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk and qtyproduk > 0 "),
                        array(
                            'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                            'produkfk' => $item['produkfk'],
                        )
                    );
                    $saldoAwalPengirim = 0;
                    $jumlah=(float)$item['jumlah'] * (float)$item['nilaikonversi'];
                    foreach ($dataSaldoAwalK as $items) {
                        $saldoAwalPengirim =$saldoAwalPengirim + (float)$items->qty;
                        if ((float)$items->qty <= $jumlah){
                            $dataKP = new KirimProduk();
                            $dataKP->norec = $dataKP->generateNewId();
                            $dataKP->kdprofile = $idProfile;
                            $dataKP->statusenabled = true;
                            $dataKP->objectasalprodukfk = $items->asalprodukfk;
                            $dataKP->hargadiscount = $items->hargadiscount;
                            $dataKP->harganetto = $items->harganetto;
                            $dataKP->hargapph = 0;
                            $dataKP->hargappn = 0;
                            $dataKP->hargasatuan = $items->hargasatuan;
                            $dataKP->hargatambahan = 0;
                            $dataKP->hasilkonversi = (float)$items->qty;
                            $dataKP->objectprodukfk = $item['produkfk'];
                            $dataKP->objectprodukkirimfk = $item['produkfk'];
                            $dataKP->nokirimfk = $norecSK;
                            $dataKP->persendiscount = 0;
                            $dataKP->qtyproduk = (float)$items->qty;
                            $dataKP->qtyprodukkonfirmasi = (float)$items->qty;
                            $dataKP->qtyprodukretur = 0;
                            $dataKP->qtyorder = $item['qtyorder'];
                            $dataKP->qtyprodukterima = (float)$items->qty;
                            $dataKP->nostrukterimafk = $items->nostrukterimafk;
                            $dataKP->objectruanganfk =$request['strukkirim']['objectruangantujuanfk'];
                            $dataKP->objectruanganpengirimfk =  $request['strukkirim']['objectruanganfk'];
                            $dataKP->satuan = '-';
                            $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
                            $dataKP->satuanviewfk = $item['satuanviewfk'];
                            $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
                            $dataKP->qtyprodukterimakonversi = (float)$items->qty;
                            $dataKP->save();

                            $jumlah = $jumlah - (float)$items->qty;
                            StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile', $idProfile)
                                ->update([
                                        'qtyproduk' => 0]
                                );
                        }else{

                            $dataKP = new KirimProduk;
                            $dataKP->norec = $dataKP->generateNewId();
                            $dataKP->kdprofile = $idProfile;
                            $dataKP->statusenabled = true;
                            $dataKP->objectasalprodukfk = $items->asalprodukfk;
                            $dataKP->hargadiscount = $items->hargadiscount;
                            $dataKP->harganetto = $items->harganetto;
                            $dataKP->hargapph = 0;
                            $dataKP->hargappn = 0;
                            $dataKP->hargasatuan = $items->hargasatuan;
                            $dataKP->hargatambahan = 0;
                            $dataKP->hasilkonversi = $jumlah;
                            $dataKP->objectprodukfk = $item['produkfk'];
                            $dataKP->objectprodukkirimfk = $item['produkfk'];
                            $dataKP->nokirimfk = $norecSK;
                            $dataKP->persendiscount = 0;
                            $dataKP->qtyproduk = $jumlah;
                            $dataKP->qtyprodukkonfirmasi = $jumlah;
                            $dataKP->qtyprodukretur = 0;
                            $dataKP->qtyorder = $item['qtyorder'];
                            $dataKP->qtyprodukterima = $jumlah;
                            $dataKP->nostrukterimafk = $items->nostrukterimafk;
                            $dataKP->objectruanganfk =$request['strukkirim']['objectruangantujuanfk'];
                            $dataKP->objectruanganpengirimfk =  $request['strukkirim']['objectruanganfk'];
                            $dataKP->satuan = '-';
                            $dataKP->objectsatuanstandarfk = $satuanstandarfk;//$item['satuanstandarfk'];
                            $dataKP->satuanviewfk = $item['satuanviewfk'];
                            $dataKP->tglpelayanan = date($request['strukkirim']['tglkirim']);
                            $dataKP->qtyprodukterimakonversi = $jumlah;
                            $dataKP->save();

                            $saldoakhir =(float)$items->qty - $jumlah;
                            $jumlah=0;
                            StokProdukDetail::where('norec', $items->norec)
                                ->where('kdprofile', $idProfile)
                                ->update([
                                        'qtyproduk' => (float)$saldoakhir]
                                );
                        }
                    }

                    //## KartuStok
                    $newKS = new KartuStok();
                    $norecKS = $newKS->generateNewId();
                    $newKS->norec = $norecKS;
                    $newKS->kdprofile = $idProfile;
                    $newKS->statusenabled = true;
                    $newKS->jumlah = ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
//                    $newKS->keterangan = 'Kirim Amprahan, dari Ruangan '. $strRuanganAsal .' ke Ruangan ' . $strRuanganTujuan . ' No Kirim: ' .  $dataSK->nokirim;
                    $newKS->keterangan = $request['strukkirim']['keteranganlainnyakirim'];
                    $newKS->produkfk = $item['produkfk'];
                    $newKS->ruanganfk = $request['strukkirim']['objectruanganfk'];
                    $newKS->saldoawal = (float)$saldoAwalPengirim - ((float)$item['jumlah'] * (float)$item['nilaikonversi']);
                    $newKS->status = 0;
                    $newKS->tglinput = date('Y-m-d H:i:s');
                    $newKS->tglkejadian = date('Y-m-d H:i:s');
                    $newKS->nostrukterimafk =  $items->norec;
                    $newKS->norectransaksi = $norecSK;
                    $newKS->tabletransaksi = 'strukkirim_t';
                    $newKS->flagfk = 2;
                    $newKS->save();
                }
                $dataSTOKDETAIL2[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk   "),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruangantujuanfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $dataSTOKDETAIL[] = DB::select(DB::raw("select qtyproduk as qty,nostrukterimafk,norec from stokprodukdetail_t 
                        where kdprofile = $idProfile and objectruanganfk=:ruanganfk and objectprodukfk=:produkfk"),
                    array(
                        'ruanganfk' => $request['strukkirim']['objectruanganfk'],
                        'produkfk' => $item['produkfk'],
                    )
                );
                $kirim = KartuStok::where('ruanganfk',$request['strukkirim']['objectruanganfk'])
                    ->where('kdprofile', $idProfile)
                    ->where('produkfk',$item['produkfk'])
                    ->get();
                $terima = KartuStok::where('ruanganfk',$request['strukkirim']['objectruangantujuanfk'])
                    ->where('kdprofile', $idProfile)
                    ->where('produkfk',$item['produkfk'])
                    ->get();
            }
//
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        $transMessage = "Pemakaian Darah";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "nokirim" => $dataSK,//$noResep,,//$noResep,
                "stokdetailPenerima" => $dataSTOKDETAIL2,
                "as" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "nokirim" => $dataSK,//$noResep,
                "stokdetail" => $dataSTOKDETAIL,
                "stokdetailTujuan" => $dataSTOKDETAIL2,
                "req" => $request->all(),
                "kartuStok" => $kirim,
                "kartuStokTujuan" => $terima,
                "kirimproduk" => $dataKP,
//                "saldoawalTujuan" => $dataSaldoAwalT,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarPemakaianDarah(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawaiUser = \DB::select(DB::raw("select pg.id,pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.kdprofile = $idProfile and lu.id=:idLoginUser"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->where('mlu.kdprofile', $idProfile)
            ->get();
        $strRuangan = [];
        foreach ($dataRuangan as $epic){
            $strRuangan[] = $epic->id;
        }
        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN ('kirimproduk_t as kp','kp.nokirimfk','=','sp.norec')
            ->LEFTJOIN('pegawai_m as pg','pg.id','=','sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru','ru.id','=','sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2','ru2.id','=','sp.objectruangantujuanfk')
            ->join('antrianpasiendiperiksa_t as apd','apd.norec','=','sp.noregistrasifk')
            ->join('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->select('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk',
                'ru.id as ruasalid','ru.namaruangan as ruanganasal','ru2.id as rutujuanid','ru2.namaruangan as ruangantujuan','sp.keteranganlainnyakirim',
                'pd.noregistrasi','ps.namapasien','ps.nocm',
                DB::raw('count(kp.objectprodukfk) as jmlitem')
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.norec','sp.tglkirim','sp.nokirim','sp.jenispermintaanfk','pg.namalengkap','sp.noorderfk','ru.id',
                'ru.namaruangan','ru2.id','ru2.namaruangan','sp.keteranganlainnyakirim','pd.noregistrasi','ps.namapasien','ps.nocm');

        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('sp.tglkirim','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('sp.tglkirim','<=', $tgl);
        }
        if(isset($request['nokirim']) && $request['nokirim']!="" && $request['nokirim']!="undefined"){
            $data = $data->where('sp.nokirim','ILIKE','%'. $request['nokirim'].'%');
        }
        if(isset($request['ruangantujuanfk']) && $request['ruangantujuanfk']!="" && $request['ruangantujuanfk']!="undefined"){
            $data = $data->where('ru2.namaruangan','ILIKE', '%'.$request['ruangantujuanfk'].'%');
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('kp.objectprodukfk','=', $request['produkfk']);
        }
//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled',true);
        $data = $data->where('sp.objectkelompoktransaksifk',33);
//        $data = $data->wherein('sp.objectruanganasalfk',$strRuangan);
//        $data = $data->where('sp.noregistrasifk','=',0);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();

        $results =array();
        foreach ($data as $item){
            $details = DB::select(DB::raw("
                     select  pr.id as kdproduk,pr.kdproduk as kdsirs,pr.namaproduk,
                    ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.objectprodukfk
                     from kirimproduk_t as spd 
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nokirimfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $jeniskirim ='';
            if ($item->jenispermintaanfk == 1){
                $jeniskirim ='Pemakaian Darah';
            }
            if ($item->jenispermintaanfk == 2){
                $jeniskirim ='Transfer';
            }
            $results[] = array(
//                'status' => 'Kirim Barang',
                'tglstruk' => $item->tglkirim,
                'nostruk' => $item->nokirim,
                'noorderfk' => $item->noorderfk,
                'jenispermintaanfk' => $item->jenispermintaanfk,
                'jeniskirim' => $jeniskirim,
                'norec' => $item->norec,
                'ruasalid'=> $item->ruasalid,
                'namaruanganasal' => $item->ruanganasal,
                'rutujuanid'=> $item->rutujuanid,
                'namaruangantujuan' => $item->ruangantujuan,
                'petugas' => $item->namalengkap,
                'keterangan' => $item->keteranganlainnyakirim,
                'jmlitem' => $item->jmlitem,
                'noregistrasi' => $item->noregistrasi,
                'namapasien' => $item->namapasien,
                'nocm' => $item->nocm,
                'details' => $details,
            );
        }

        $result = array(
            'daftar' => $results,
            'datalogin' => $dataPegawaiUser,//$dataLogin,
            'message' => 'as@epic',
            'str' => $strRuangan,
        );

        return $this->respond($result);
    }

    public function getDaftarRegistrasiPasienBankDarah(Request $request)
    {
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
//            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pemakaianasuransi_t as pas', 'pas.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select('apd.norec as norec_apd','pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk', 'sbm.nosbm', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pd.objectruanganlastfk as ruanganid','pas.nosep','pas.norec as norec_pa','br.norec as norec_br')
            ->whereNull('br.norec');

        $filter = $request->all();
        if (isset($filter['tglAwal']) && $filter['tglAwal'] != "" && $filter['tglAwal'] != "undefined") {
            $data = $data->where('pd.tglregistrasi', '>=', $filter['tglAwal']);
        }
        if (isset($filter['tglAkhir']) && $filter['tglAkhir'] != "" && $filter['tglAkhir'] != "undefined") {
            $tgl = $filter['tglAkhir'];//." 23:59:59";
            $data = $data->where('pd.tglregistrasi', '<=', $tgl);
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
}