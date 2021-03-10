<?php
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 5/11/2020
 * Time: 12:34 AM
 */

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\ApiController;
use App\Transaksi\LoggingUser;
use Illuminate\Http\Request;
use DB;
use App\Traits\Valet;
use App\Master\Pegawai;
use App\Master\Produk;
use App\Transaksi\StokProdukDetail;
use App\Transaksi\KartuStok;
use App\Transaksi\StrukKirim;
use App\Transaksi\KirimProduk;

use App\Transaksi\PersediaanSaldoAwal;

use App\Transaksi\StrukClosing;
use App\Transaksi\StokProdukDetailOpname;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;

use App\Transaksi\StrukPelayanan;
use App\Transaksi\StrukPelayananDetail;

use App\Transaksi\PemakaianBHP;
use App\Transaksi\PemakaianBHPdetail;
class NeracaPersediaanController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct();
    }
    public function getDataComboStockFlow(Request $request) {
        $dataLogin = $request->all();
        $dataRuangan = \DB::table('maploginusertoruangan_s as mlu')
            ->JOIN('ruangan_m as ru','ru.id','=','mlu.objectruanganfk')
            ->select('ru.id','ru.namaruangan')
            ->where('mlu.objectloginuserfk',$dataLogin['userData']['id'])
            ->get();

        $result = array(
            'ruangan' => $dataRuangan,
//            'detailjenisproduk' =>$detailJenis,
//            'jenisproduk' =>$dataJenisProduk,
//            'kodesirs' => $kdProdukSirs
        );
        return $this->respond($result);
    }

    public function Neraca(Request $request){
//        ini_set('max_execution_time', 1000); //6 minutes

        $tglawal = $request['tglawal'];
        $tglakhir = $request['tglakhir'];
        $filterProduk = "";
        $filterRuangan = "";
        $filterRuangans = "";
        if ($request['nmproduk'] != ''){
            $filterProduk = " and pr.namaproduk like '%".$request['nmproduk']."%'";
        }
        if ($request['idRuangan'] != ''){
            $filterRuangan = " and ru.id = ".$request['idRuangan'];
            $filterRuangans = " and ks.ruanganfk = " .$request['idRuangan'];
        }
        //objectdetailjenisprodukfk
        $filterDjProduk = "";
        if ($request['djp'] != ''){
            $filterDjProduk = " and pr.objectdetailjenisprodukfk in (".$request['djp'].")";
        }


        $dataProduk = DB::select(DB::raw("

                select pr.id,pr.namaproduk,case when pr.isgeneric = 1 then 'Generik' else 'Non Generik' end as isgeneric,ss.satuanstandar from produk_m as pr
                INNER JOIN satuanstandar_m as ss on ss.id=pr.objectsatuanstandarfk
                where pr.statusenabled=1   
                $filterProduk $filterDjProduk
                group by  pr.id,pr.namaproduk,pr.isgeneric,ss.satuanstandar 
                order by pr.namaproduk
                
            ")
        );

        $StockAwaldrBlnSebelumnya  = DB::select(DB::raw("

                select * from (
                
                        select * from (
                        select ks.norec,ks.produkfk,pr.namaproduk,
                        case when ks.status =0 then ks.saldoawal else ks.saldoawal end as saldoawal,ks.ruanganfk, ks.tglinput,ks.CREATED_AT,
                        row_number() over (partition by ks.produkfk,ks.ruanganfk order by ks.CREATED_AT desc) as rownum ,ks.jumlah as jumlah,ks.saldoawal as saldo,
                        '*' as ket,ks.status,ru.namaruangan
                        from kartustok_t as ks
                        INNER JOIN produk_m as pr on pr.id=ks.produkfk 
                        INNER JOIN ruangan_m as ru on ru.id=ks.ruanganfk 
                        where ks.tglinput < '$tglawal' $filterProduk $filterRuangan
                        )as x WHERE x.rownum=1 
                
               
                ) as xx order by xx.namaproduk
                

                
            ")
        );
        $StockAwaldrBlnIni  = DB::select(DB::raw("

               select * from (
                        select x.norec,x.produkfk,x.namaproduk,x.saldoawal as saldoawal,x.ruanganfk,x.tglinput,x.CREATED_AT,x.rownum,x.jumlah,'' as ket ,x.status,
                        x.namaruangan from (
                        select ks.norec,ks.produkfk,pr.namaproduk,ks.ruanganfk,ks.status,ru.namaruangan,
                        case when ks.status =0 then ks.saldoawal+ks.jumlah else ks.saldoawal-ks.jumlah end as saldoawal,ks.tglinput,ks.CREATED_AT,
                         ks.jumlah,ks.saldoawal as saldo,
                        row_number() over (partition by ks.produkfk,ks.ruanganfk order by ks.CREATED_AT) as rownum 
                        from kartustok_t as ks
                        INNER JOIN produk_m as pr on pr.id=ks.produkfk
                        INNER JOIN ruangan_m as ru on ru.id=ks.ruanganfk
                        where ks.tglinput BETWEEN '$tglawal' and '$tglakhir' $filterProduk $filterRuangan
                        )as x WHERE x.rownum=1 
                ) as xx order by xx.namaproduk
                

                
            ")
        );
        $sama = false;
        $stockawal = [];
        $stockawal = $StockAwaldrBlnIni;
        foreach ($StockAwaldrBlnSebelumnya as $itm2){
            $sama=false;
            foreach ($StockAwaldrBlnIni as $itm){
                if ($itm2->produkfk == $itm->produkfk){
                    if ($itm2->ruanganfk == $itm->ruanganfk) {
                        $sama = true;
                        break;
                    }
                }
            }
            if ($sama == false ){
                $stockawal[] = $itm2;
            }
        }

        $dataHargaAwal = DB::select(DB::raw("select * from (
                    select spd.tglpelayanan,spd.objectprodukfk,spd.harganetto1 as hargaawal, row_number() over (partition by spd.objectprodukfk order by spd.tglpelayanan desc) as rownum
                    from strukpelayanan_t as sp
                    FULL OUTER JOIN stokprodukdetail_t as spd on spd.nostrukterimafk=sp.norec
                    where spd.tglpelayanan <= '$tglawal' and sp.statusenabled=1 
                    ) tmp where rownum =1 order by objectprodukfk;;
                ")
        );
        $dataHargaAkhir = DB::select(DB::raw("select * from (
                    select spd.tglpelayanan,spd.objectprodukfk,spd.harganetto1 as hargaakhir, row_number() over (partition by spd.objectprodukfk order by spd.tglpelayanan desc) as rownum
                    from strukpelayanan_t as sp
                    FULL OUTER JOIN stokprodukdetail_t as spd on spd.nostrukterimafk=sp.norec
                    where spd.tglpelayanan BETWEEN '2016-01-01 00:00' and '$tglakhir' and  sp.statusenabled=1 
                    ) tmp where rownum =1 order by objectprodukfk;;
                ")
        );

        $datapembelian = DB::select(DB::raw("select sum(ks.jumlah) as qty,ks.produkfk,ks.ruanganfk from kartustok_t as ks
                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir' $filterRuangans
                and ks.flagfk in (1) group by ks.produkfk,ks.ruanganfk
             ")
        );
        $dataPinjamplus = [];
        $datakoreksiplus = DB::select(DB::raw("select sum(ks.jumlah) as qty,ks.produkfk,ks.ruanganfk from kartustok_t as ks
                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir' $filterRuangans
                and ks.flagfk in (4) group by ks.produkfk,ks.ruanganfk
             ")
        );
        $dataresep = DB::select(DB::raw("select sum(ks.jumlah) as qty,ks.produkfk,ks.ruanganfk from kartustok_t as ks
                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir' $filterRuangans
                and ks.flagfk in (7) group by ks.produkfk,ks.ruanganfk
             ")
        );
        $datafloorstok = DB::select(DB::raw("select sum(ks.jumlah) as qty,ks.produkfk,ks.ruanganfk from kartustok_t as ks
                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir' $filterRuangans
                and ks.flagfk in (2) group by ks.produkfk,ks.ruanganfk
             ")
        );

        $dataPinjamMin = [];
        $datakoreksiMin = DB::select(DB::raw("select sum(ks.jumlah) as qty,ks.produkfk,ks.ruanganfk from kartustok_t as ks
                where ks.tglinput BETWEEN '$tglawal' and '$tglakhir' $filterRuangans
                and ks.flagfk in (5) group by ks.produkfk,ks.ruanganfk
             ")
        );

        $asepic = array(
            'produk' =>  $dataProduk,
            'stockawal' =>  $stockawal,
            'hargapokok' => $dataHargaAwal,
            'hargaakhir' => $dataHargaAkhir,
            'pembelian' => $datapembelian,
            'pinjamplus' => $dataPinjamplus,
            'koreksiplus' => $datakoreksiplus,
            'resep' => $dataresep,
            'floorstock' =>$datafloorstok,
            'pinjammin' => $dataPinjamMin,
            'koreksimin' => $datakoreksiMin,

        );
        return $this->respond($asepic);
    }

    public function GetDataKartuPersediaan(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('kartustok_t as ks')
            ->JOIN('produk_m as pro','pro.id','=','ks.produkfk')
            ->JOIN('ruangan_m as ru','ru.id','=','ks.ruanganfk')
            ->leftJoin('flag_m as fg','fg.id','=','ks.flagfk')
            ->leftJoin('stokprodukdetail_t AS spd', function($join){
                $join->on('spd.nostrukterimafk','=','ks.nostrukterimafk');
                $join->on('ks.produkfk','=','spd.objectprodukfk');
                $join->on('ks.ruanganfk','=','spd.objectruanganfk');
            })
            ->select('ks.keterangan','ks.tglinput','ks.tglkejadian','ks.produkfk','pro.namaproduk','ks.ruanganfk','ru.namaruangan',
                              'ks.status','fg.flag','spd.harganetto1',
                \DB::raw('COALESCE(ks.jumlah,0.0) as jumlah, coalesce(ks.saldoawal,0.0) as saldoakhir')
            )
            ->where('ks.kdprofile',$idProfile)
            ->groupBy('ks.keterangan','ks.tglinput','ks.tglkejadian','ks.produkfk','pro.namaproduk',
                      'ks.ruanganfk','ru.namaruangan','ks.status','fg.flag','spd.harganetto1',
                      'ks.jumlah','ks.saldoawal');
        if(isset($request['tglAwal']) && $request['tglAwal']!="" && $request['tglAwal']!="undefined"){
            $data = $data->where('ks.tglkejadian','>=', $request['tglAwal']);
        }
        if(isset($request['tglAkhir']) && $request['tglAkhir']!="" && $request['tglAkhir']!="undefined"){
            $tgl= $request['tglAkhir'];
            $data = $data->where('ks.tglkejadian','<=', $tgl);
        }
        if(isset($request['ruanganfk']) && $request['ruanganfk']!="" && $request['ruanganfk']!="undefined"){
            $data = $data->where('ks.ruanganfk','=', $request['ruanganfk']);
        }
        if(isset($request['produkfk']) && $request['produkfk']!="" && $request['produkfk']!="undefined"){
            $data = $data->where('ks.produkfk','=', $request['produkfk']);
        }
        if(isset($request['nmproduk']) && $request['nmproduk']!="" && $request['nmproduk']!="undefined"){
            $data = $data->where('pro.namaproduk','ILIKE','%'. $request['nmproduk'].'%');
        }
        $data = $data->where('ks.statusenabled',true);
        $data = $data->orderBy('ks.tglkejadian');
        $data = $data->get();

        return $this->respond($data);
    }
}