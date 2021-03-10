<?php
/**
 * Created by PhpStorm.
 * User: Agus Sustian
 * Date: 9/11/2019
 * Time: 10:54 AM
 */

/**
 * Powered by .
 * User: Egie Ramdan
 * Date: 9/11/2019
 * Time: 10:54 AM
 */

namespace App\Http\Controllers\Remunerasi;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Traits\Valet;
use App\Transaksi\StrukPagu;
use App\Transaksi\StrukDetailPagu;
use App\Transaksi\MapJenisPaguToPegawai;
use App\Transaksi\DetailPegawaiPagu;
use App\Transaksi\StrukClosing;
use App\Transaksi\PotonganRemun;

use Webpatser\Uuid\Uuid;
use DB;

class RemunerasiController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function getCombo(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        $deptPelayanan = explode (',',$this->settingDataFixed('kdDepartemenPelayanan',$kdProfile));
        $kdDepartemenRawatPelayanan = [];
        foreach ($deptPelayanan as $itemPelayanan){
            $kdDepartemenRawatPelayanan []=  (int)$itemPelayanan;
        }
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->where('dp.kdprofile', $kdProfile)
            ->whereIn('dp.id', $kdDepartemenRawatPelayanan)
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.kdprofile', $kdProfile)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();


        $dataDokter = \DB::table('pegawai_m as ru')
            ->where('ru.kdprofile', $kdProfile)
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
            ->where('kp.kdprofile', $kdProfile)
            ->where('kp.statusenabled', true)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getRemunerasiJP1_rev2(Request $request){
        $kdProfile = (int) $this->getDataKdProfile($request);
        //TODO : CARI PAGU
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $nama = $request['nama'];
        $produk = $request['produk'];
        $dtdtdt2 =[];
        $dataPersen3 = [];

        $SCSC = StrukPagu::where('periodeawal',$request['tglAwal'])->where('kdprofile', $kdProfile)->get();
        $StrukPagu = false;
        if (count($SCSC)>0){
            $StrukPagu = true;
        }
//        $data = DB::select(DB::raw(
//            "
//              select pp.tglpelayanan,pr.namaproduk,((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)  as jasapelayanan,
//              pgpj.namalengkap as dokterpj ,pgpj.id as dokterpjid ,
//                apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis,
//                (case when ppd.jasa is null then 0 else ppd.jasa end)*pp.jumlah as jasa
//                from pelayananpasiendetail_t as ppd
//                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                left join produk_m as pr on pr.id =ppd.produkfk
//                left join ruangan_m as ru on ru.id=apd.objectruanganfk
//                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' and pgpj.namalengkap like '%$nama%'
//                and pr.namaproduk like '%$produk%'
//                and ppd.komponenhargafk=35 and ppp.objectjenispetugaspefk=4 and pp.isparamedis is null
//
//                union ALL
//
//                select pp.tglpelayanan,pr.namaproduk,((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah) as jasapelayanan,
//                pgpj.namalengkap as dokterpj ,pgpj.id as dokterpjid ,
//                apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis,
//                (case when ppd.jasa is null then 0 else ppd.jasa end)*pp.jumlah as jasa
//                from pelayananpasiendetail_t as ppd
//                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                left join produk_m as pr on pr.id =ppd.produkfk
//                left join ruangan_m as ru on ru.id=apd.objectruanganfk
//                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'  and pgpj.namalengkap like '%$nama%'
//                and pr.namaproduk like '%$produk%'
//                and ppd.komponenhargafk=25 and ppp.objectjenispetugaspefk=4 and pp.isparamedis = 1
//
//
//          "
//        ));
        $data = DB::select(DB::raw(
            "
                select pp.tglpelayanan,pr.namaproduk,((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)  as jasapelayanan,
                pgpj.namalengkap as dokterpj ,pgpj.id as dokterpjid ,
                apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis,
                (case when ppd.jasa is null then 0 else ppd.jasa end)*pp.jumlah as jasa
                from pelayananpasiendetail_t as ppd
                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
                left join produk_m as pr on pr.id =ppd.produkfk
                left join ruangan_m as ru on ru.id=apd.objectruanganfk
                where ppd.kdprofile = $kdProfile and pd.tglpulang between '$tglAwal' and '$tglAkhir' and pgpj.namalengkap ilike '%$nama%'
                and pr.namaproduk ilike '%$produk%'
                and ppd.komponenhargafk=94 and ppp.objectjenispetugaspefk=4 
                and pd.statusenabled=true   
                --and pp.isparamedis is null
                
--                union ALL 
--                
--                select pp.tglpelayanan,pr.namaproduk,((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah) as jasapelayanan,
--                pgpj.namalengkap as dokterpj ,pgpj.id as dokterpjid ,
--               apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis,
--                (case when ppd.jasa is null then 0 else ppd.jasa end)*pp.jumlah as jasa
--                from pelayananpasiendetail_t as ppd
--                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
--                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
--                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
--                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
--                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
--                left join produk_m as pr on pr.id =ppd.produkfk
--                left join ruangan_m as ru on ru.id=apd.objectruanganfk
--                where pd.tglpulang between '$tglAwal' and '$tglAkhir'  and pgpj.namalengkap ilike '%$nama%' 
--                and pr.namaproduk ilike '%$produk%'
--                and ppd.komponenhargafk=25 and ppp.objectjenispetugaspefk=4 and pp.isparamedis = 1
                 and  ppd.kdprofile = $kdProfile

            "
        ));
//        $dataDokterAnestesi = DB::select(DB::raw(
//            "
//              select pp.tglpelayanan,ppp.objectpegawaifk as dokterpjid ,
//                pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,pp.isparamedis,pgpj.namalengkap as dokterpj
//                from  pelayananpasien_t as pp
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'  and pgpj.namalengkap like '%$nama%'
//                and ppp.objectjenispetugaspefk=6 and pp.isparamedis is null
//
//
//          "
//        ));
        $dataDokterAnestesi = [];
//        $dataDokterAnestesi = DB::select(DB::raw(
//            "
//              select pp.tglpelayanan,ppp.objectpegawaifk as dokterpjid ,
//                pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,pp.isparamedis,pgpj.namalengkap as dokterpj
//                from  pelayananpasien_t as pp
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                where pd.tglpulang between '$tglAwal' and '$tglAkhir'  and pgpj.namalengkap like '%$nama%'
//                and ppp.objectjenispetugaspefk=6 and pp.isparamedis is null
//
//
//          "
//        ));
        $newData = [];
        $sama = false;
        $iddokter = '';
        $nmdokter = '';
        foreach ($data as $dt){
            $sama = false;
            $iddokter = '';
            $nmdokter = '';
            foreach ($dataDokterAnestesi as $da){
                if  ($dt->norec_pp == $da->norec_pp){
                    $sama = true;
                    $iddokter = $da->objectpegawaifk;
                    $nmdokter = $da->dokterpj;
                }
            }
            if ((float)$dt->jasapelayanan > 0){
                if  ($sama == true){
//                    $newData[] = array(
//                        'tglpelayanan' => $dt->tglpelayanan,
//                        'namaproduk' => $dt->namaproduk,
//                        'jasapelayanan' => ((float)$dt->jasapelayanan /100)*75,
//                        'dokterpj' => $dt->dokterpj,
//                        'dokterpjid' => $dt->dokterpjid,
//                        'norec_apd' => $dt->norec_apd,
//                        'jumlah' => $dt->jumlah,
//                        'norec_pp' => $dt->norec_pp,
//                        'produkfk' => $dt->produkfk,
//                        'objectpegawaifk' => $dt->objectpegawaifk,
//                        'objectdepartemenfk' => $dt->objectdepartemenfk,
//                        'objectruanganfk' => $dt->objectruanganfk,
//                        'isparamedis' => $dt->isparamedis,
//                        'jasa' => ((float)$dt->jasa /100)*75,//$dt->jasa,
//                        'tipedokter' => 'Medis',
//                    );
//                    $newData[] = array(
//                        'tglpelayanan' => $dt->tglpelayanan,
//                        'namaproduk' => $dt->namaproduk,
//                        'jasapelayanan' => ((float)$dt->jasapelayanan /100)*25,
//                        'dokterpj' => $nmdokter,
//                        'dokterpjid' => $iddokter,
//                        'norec_apd' => $dt->norec_apd,
//                        'jumlah' => $dt->jumlah,
//                        'norec_pp' => $dt->norec_pp,
//                        'produkfk' => $dt->produkfk,
//                        'objectpegawaifk' => $dt->objectpegawaifk,
//                        'objectdepartemenfk' => $dt->objectdepartemenfk,
//                        'objectruanganfk' => $dt->objectruanganfk,
//                        'isparamedis' => $dt->isparamedis,
//                        'jasa' => ((float)$dt->jasa /100)*25,//$dt->jasa,
//                        'tipedokter' => 'Anestesi',
//                    );
                }else{
                    $newData[] = array(
                        'tglpelayanan' => $dt->tglpelayanan,
                        'namaproduk' => $dt->namaproduk,
                        'jasapelayanan' => (float)$dt->jasapelayanan,
                        'dokterpj' => $dt->dokterpj,
                        'dokterpjid' => $dt->dokterpjid,
                        'norec_apd' => $dt->norec_apd,
                        'jumlah' => $dt->jumlah,
                        'norec_pp' => $dt->norec_pp,
                        'produkfk' => $dt->produkfk,
                        'objectpegawaifk' => $dt->objectpegawaifk,
                        'objectdepartemenfk' => $dt->objectdepartemenfk,
                        'objectruanganfk' => $dt->objectruanganfk,
                        'isparamedis' => $dt->isparamedis,
                        'jasa' => $dt->jasa,
                        'tipedokter' => 'Medis',
                    );
                }
            }
        }
//        $data2 = DB::select(DB::raw(
//            "
//
//              select pp.tglpelayanan,pr.namaproduk,
//                case when pp.isparamedis = 1 then
//                    sum(case when ppd.komponenhargafk = 35 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                else
//                    sum(case when ppd.komponenhargafk = 25 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                end as rc,
//                case when pp.isparamedis = 1 then
//                    sum(case when ppd.komponenhargafk = 25 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                else
//                    sum(case when ppd.komponenhargafk = 35 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                end as rcdokter,
//                sum(case when ppd.komponenhargafk = 88 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as postremun,
//                sum(case when ppd.komponenhargafk in (86) then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as ccdireksi,
//                sum(case when ppd.komponenhargafk in (87) then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as ccstaffdireksi,
//                sum(case when ppd.komponenhargafk in (90) then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as ccmanajemen,
//                pgpj.namalengkap as dokter ,pgpj.id as dokterid ,
//                apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis
//                from pelayananpasiendetail_t as ppd
//                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                left join produk_m as pr on pr.id =ppd.produkfk
//                left join ruangan_m as ru on ru.id=apd.objectruanganfk
//                where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
//                and  ppp.objectjenispetugaspefk=4 and ppd.komponenhargafk in (86,87,90,25,88,35)
//                group by pp.tglpelayanan,pr.namaproduk,
//                pgpj.namalengkap ,pgpj.id  ,
//                apd.norec ,pp.jumlah,pp.norec ,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis
//                order by pp.norec
//
//          "
//        ));

//
//        $data2 = DB::select(DB::raw(
//            "
//
//              select pp.tglpelayanan,pr.namaproduk,
//                case when pp.isparamedis = 1 then
//                    sum(case when ppd.komponenhargafk = 35 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                else
//                    sum(case when ppd.komponenhargafk = 25 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                end as rc,
//                case when pp.isparamedis = 1 then
//                    sum(case when ppd.komponenhargafk = 25 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                else
//                    sum(case when ppd.komponenhargafk = 35 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end)
//                end as rcdokter,
//                sum(case when ppd.komponenhargafk = 88 then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as postremun,
//                sum(case when ppd.komponenhargafk in (86) then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as ccdireksi,
//                sum(case when ppd.komponenhargafk in (87) then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as ccstaffdireksi,
//                sum(case when ppd.komponenhargafk in (90) then (((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) else 0 end) as ccmanajemen,
//                pgpj.namalengkap as dokter ,pgpj.id as dokterid ,
//                apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis
//                from pelayananpasiendetail_t as ppd
//                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                left join produk_m as pr on pr.id =ppd.produkfk
//                left join ruangan_m as ru on ru.id=apd.objectruanganfk
//                where pd.tglpulang between '$tglAwal' and '$tglAkhir'
//                and  ppp.objectjenispetugaspefk=4 and ppd.komponenhargafk in (86,87,90,25,88,35)
//                group by pp.tglpelayanan,pr.namaproduk,
//                pgpj.namalengkap ,pgpj.id  ,
//                apd.norec ,pp.jumlah,pp.norec ,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis
//                order by pp.norec
//
//          "
//        ));
        $data2 = DB::select(DB::raw(
            "  
  
                select  x.*,
                    (x.jaspel*7.5)/100 as direksi,
                    (x.jaspel*5)/100 as struktural,
                    (x.jaspel*1)/100 as administrasi,
                    case when x.objectruanganfk in (535,577,580,576) then  (x.jaspel*30)/100
                            when x.objectruanganfk in (575) then  (x.jaspel*35)/100
                            when x.objectruanganfk in (125,94,116,59) then  (x.jaspel*28)/100
                            when x.objectruanganfk in (571,579) then  (x.jaspel*40)/100
                    else (x.jaspel*45)/100 end as jpl,
                    (x.jaspel*8.5)/100 as jptl,
                    case when x.objectruanganfk in (535,577,580,576) then  (x.jaspel*48)/100
                            when x.objectruanganfk in (575) then  (x.jaspel*43)/100
                            when x.objectruanganfk in (125,94,116,59) then  (x.jaspel*50)/100
                            when x.objectruanganfk in (571,579) then  (x.jaspel*38)/100
                    else (x.jaspel*33)/100  end as gabungan
                    from 
                    (select pp.tglpelayanan,pr.namaproduk,                
                    sum((((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) ) as jaspel,
                    pgpj.namalengkap as dokter ,pgpj.id as dokterid ,
                    apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis
                    from pelayananpasiendetail_t as ppd
                    inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
                    inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
                    left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                    inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                    left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
                    left join produk_m as pr on pr.id =ppd.produkfk
                    left join ruangan_m as ru on ru.id=apd.objectruanganfk
                    where pd.tglpulang between '$tglAwal' and '$tglAkhir'
                    and  ppp.objectjenispetugaspefk=4 and ppd.komponenhargafk in (94)
                    and ppd.kdprofile = $kdProfile
                    group by pp.tglpelayanan,pr.namaproduk,
                    pgpj.namalengkap ,pgpj.id  ,
                    apd.norec ,pp.jumlah,pp.norec ,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis) as x

            "
        ));
        $newData2 = [];
        $sama = false;
        $iddokter = '';
        $nmdokter = '';
        foreach ($data2 as $dt){
            $sama = false;
            $iddokter = '';
            $nmdokter = '';
            foreach ($dataDokterAnestesi as $da){
                if  ($dt->norec_pp == $da->norec_pp){
                    $sama = true;
                    $iddokter = $da->objectpegawaifk;
                    $nmdokter = $da->dokterpj;
                }
            }
            if  ($sama == true){//dokter pemeriksa dan dokter anestesi
//                $newData2[] = array(
//                    'tglpelayanan' => $dt->tglpelayanan,
//                    'namaproduk' => $dt->namaproduk,
//                    'rc' => $dt->rc,
//                    'rcdokter' => ((float)$dt->rcdokter /100)*75,
//                    'postremun' => $dt->postremun,
//                    'ccdireksi' => $dt->ccdireksi,
//                    'ccstaffdireksi' => $dt->ccstaffdireksi,
//                    'ccmanajemen' => $dt->ccmanajemen,
//                    'dokter' => $dt->dokter,
//                    'dokterid' => $dt->dokterid,
//                    'norec_apd' => $dt->norec_apd,
//                    'jumlah' => $dt->jumlah,
//                    'norec_pp' => $dt->norec_pp,
//                    'produkfk' => $dt->produkfk,
//                    'objectdepartemenfk' => $dt->objectdepartemenfk,
//                    'objectruanganfk' => $dt->objectruanganfk,
//                    'isparamedis' => $dt->isparamedis,
//                    'tipedokter' => 'RCD Medis',
//                );
//                $newData2[] = array(
//                    'tglpelayanan' => $dt->tglpelayanan,
//                    'namaproduk' => $dt->namaproduk,
//                    'rc' => $dt->rc,
//                    'rcdokter' => ((float)$dt->rcdokter /100)*25,
//                    'postremun' => 0,
//                    'ccdireksi' => 0,
//                    'ccstaffdireksi' => 0,
//                    'ccmanajemen' => 0,
//                    'dokter' => $nmdokter,
//                    'dokterid' => $iddokter,
//                    'norec_apd' => $dt->norec_apd,
//                    'jumlah' => $dt->jumlah,
//                    'norec_pp' => $dt->norec_pp,
//                    'produkfk' => $dt->produkfk,
//                    'objectdepartemenfk' => $dt->objectdepartemenfk,
//                    'objectruanganfk' => $dt->objectruanganfk,
//                    'isparamedis' => $dt->isparamedis,
//                    'tipedokter' => 'RDC anestesi',
//                );
            }else{
                if ($dt->objectdepartemenfk == 28){//jasa paramedis 60% ke CC direksi
//                    $newData2[] = array(
//                        'tglpelayanan' => $dt->tglpelayanan,
//                        'namaproduk' => $dt->namaproduk,
//                        'rc' =>  0,
//                        'rcdokter' => 0,
//                        'postremun' => 0,
//                        'ccdireksi' => (((float)$dt->rc)/100)* 60,
//                        'ccstaffdireksi' =>0,
//                        'ccmanajemen' => 0,
//                        'dokter' => $dt->dokter,
//                        'dokterid' => $dt->dokterid,
//                        'norec_apd' => $dt->norec_apd,
//                        'jumlah' => $dt->jumlah,
//                        'norec_pp' => $dt->norec_pp,
//                        'produkfk' => $dt->produkfk,
//                        'objectdepartemenfk' => $dt->objectdepartemenfk,
//                        'objectruanganfk' => $dt->objectruanganfk,
//                        'isparamedis' => $dt->isparamedis,
//                        'tipedokter' => 'RC Paramedis to CC Direksi 60%',
//                    );
//                    $newData2[] = array(
//                        'tglpelayanan' => $dt->tglpelayanan,
//                        'namaproduk' => $dt->namaproduk,
//                        'rc' => (((float)$dt->rc)/100)* 40  ,
//                        'rcdokter' => $dt->rcdokter,
//                        'postremun' => $dt->postremun,
//                        'ccdireksi' => $dt->ccdireksi,
//                        'ccstaffdireksi' => $dt->ccstaffdireksi,
//                        'ccmanajemen' => $dt->ccmanajemen,
//                        'dokter' => $dt->dokter,
//                        'dokterid' => $dt->dokterid,
//                        'norec_apd' => $dt->norec_apd,
//                        'jumlah' => $dt->jumlah,
//                        'norec_pp' => $dt->norec_pp,
//                        'produkfk' => $dt->produkfk,
//                        'objectdepartemenfk' => $dt->objectdepartemenfk,
//                        'objectruanganfk' => $dt->objectruanganfk,
//                        'isparamedis' => $dt->isparamedis,
//                        'tipedokter' => 'RC Paramedis to RC 40%',
//                    );
                }else{
                    $newData2[] = array(
                        'tglpelayanan' => $dt->tglpelayanan,
                        'namaproduk' => $dt->namaproduk,
                        'rc' => (float)$dt->jptl ,
                        'rcdokter' => $dt->jpl,
                        'postremun' => $dt->gabungan,
                        'ccdireksi' => $dt->direksi,
                        'ccstaffdireksi' => $dt->struktural,
                        'ccmanajemen' => $dt->administrasi,
                        'dokter' => $dt->dokter,
                        'dokterid' => $dt->dokterid,
                        'norec_apd' => $dt->norec_apd,
                        'jumlah' => $dt->jumlah,
                        'norec_pp' => $dt->norec_pp,
                        'produkfk' => $dt->produkfk,
                        'objectdepartemenfk' => $dt->objectdepartemenfk,
                        'objectruanganfk' => $dt->objectruanganfk,
                        'isparamedis' => $dt->isparamedis,
                        'tipedokter' => 'Medis',
                    );
                }
            }
        }

        $result = array(
//            'data1' => $data,
            'data1' => $newData,
//            'data2' => $data2,
            'data2' => $newData2,
            'data3' => $dataDokterAnestesi,
            'strukpagu' => $StrukPagu,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function saveStrukPaguWithDetailRev2(Request $request) {
        //TODO : SAVE PAGU
        DB::beginTransaction();
        $dataReq = $request->all();
        ini_set('max_execution_time', 500); //6 minutes
        try{
            $dataPegawai = \DB::table('loginuser_s as lu')
                ->select('lu.objectpegawaifk')
                ->where('lu.id',$dataReq['userData']['id'])
                ->first();
            $SCSC = StrukPagu::where('periodeawal',$dataReq['head']['periodeawal'])
                ->select('norec')
                ->first();

            if (!empty($SCSC)){
                $delSCSC = StrukPagu::where('norec',$SCSC->norec)
                    ->delete();
                $delSCSC2 = StrukDetailPagu::where('strukpagufk',$SCSC->norec)
                    ->delete();
            }


            $nostrukpagu = $this->generateCode(new StrukPagu(), 'nostrukpagu', 10, 'PGU/' . $this->getDateTime()->format('ym'));

            $dataSC = new StrukPagu();
            $dataSC->norec = $dataSC->generateNewId();
            $dataSC->kdprofile = 0;
            $dataSC->statusenabled = true;
            $dataSC->nostrukpagu = $nostrukpagu;
            $dataSC->tglstrukpagu = date('Y-m-d H:i:s');
            $dataSC->periodeawal = $dataReq['head']['periodeawal'];
            $dataSC->periodeakhir = null;
            $dataSC->pegawaiuserid = $dataPegawai->objectpegawaifk;
//            $dataSC->totaljasalayanan = $dataReq['head']['totaljasalayanan'];
//            $dataSC->totaljasaremun = $dataReq['head']['totaljasaremun'];
//            $dataSC->totaljasamanajemen = $dataReq['head']['totaljasamanajemen'];
            $dataSC->totalrcdokter = $dataReq['head']['rcdokter'];
            $dataSC->totalpostrm = $dataReq['head']['postremun'];
            $dataSC->totalrc = $dataReq['head']['rc'];
            $dataSC->totalccdireksi = $dataReq['head']['ccdireksi'];
            $dataSC->totalccstaffdireksi = $dataReq['head']['ccstaffdireksi'];
            $dataSC->totalccmanajemen = $dataReq['head']['ccmanajemen'];
            $dataSC->save();

            $norecSC = $dataSC->norec;

            foreach ($dataReq['data'] as $item) {
                if ((float)$item['rcdokter'] >0){
                    $dataSPD = new StrukDetailPagu();
                    $dataSPD->norec = $dataSPD->generateNewId();
                    $dataSPD->kdprofile = 0;
                    $dataSPD->statusenabled = true;
                    $dataSPD->strukpagufk = $norecSC;
                    $dataSPD->pelayananpasienfk = $item['norec_pp'];
                    $dataSPD->jenispagufk = 7;//RC DOKTER
                    $dataSPD->jenispagupersen = null;
                    $dataSPD->jenispagunilai = $item['rcdokter'];
                    $dataSPD->produkfk = $item['produkfk'];
                    $dataSPD->dokterid = $item['dokterid'];
                    $dataSPD->tglpelayanan = $item['tglpelayanan'];
                    $dataSPD->jumlah = $item['jumlah'];
                    $dataSPD->ruanganfk = $item['objectruanganfk'];
                    $dataSPD->namaexternal = $item['tipedokter'];
                    $dataSPD->save();
                }

//                'tipedokter' => 'RDC anestesi',
                if ((float)$item['rc'] >0) {
                    if  ($item['tipedokter'] != 'RDC anestesi'){
                        $dataSPD = new StrukDetailPagu();
                        $dataSPD->norec = $dataSPD->generateNewId();
                        $dataSPD->kdprofile = 0;
                        $dataSPD->statusenabled = true;
                        $dataSPD->strukpagufk = $norecSC;
                        $dataSPD->pelayananpasienfk = $item['norec_pp'];
                        $dataSPD->jenispagufk = 8;//RC
                        $dataSPD->jenispagupersen = null;
                        $dataSPD->jenispagunilai = $item['rc'];
                        $dataSPD->produkfk = $item['produkfk'];
//                $dataSPD->dokterid = $item['dokterid'];
                        $dataSPD->tglpelayanan = $item['tglpelayanan'];
                        $dataSPD->jumlah = $item['jumlah'];
                        $dataSPD->ruanganfk = $item['objectruanganfk'];
                        $dataSPD->namaexternal = $item['tipedokter'];
                        $dataSPD->save();
                    }
                }

                if ((float)$item['postremun'] >0) {
                    if  ($item['tipedokter'] != 'RDC anestesi') {
                        $dataSPD = new StrukDetailPagu();
                        $dataSPD->norec = $dataSPD->generateNewId();
                        $dataSPD->kdprofile = 0;
                        $dataSPD->statusenabled = true;
                        $dataSPD->strukpagufk = $norecSC;
                        $dataSPD->pelayananpasienfk = $item['norec_pp'];
                        $dataSPD->jenispagufk = 9;//POST REMUN
                        $dataSPD->jenispagupersen = null;
                        $dataSPD->jenispagunilai = $item['postremun'];
                        $dataSPD->produkfk = $item['produkfk'];
//                $dataSPD->dokterid = $item['dokterid'];
                        $dataSPD->tglpelayanan = $item['tglpelayanan'];
                        $dataSPD->jumlah = $item['jumlah'];
                        $dataSPD->ruanganfk = $item['objectruanganfk'];
                        $dataSPD->namaexternal = $item['tipedokter'];
                        $dataSPD->save();
                    }
                }

                if ((float)$item['ccdireksi'] >0) {
                    if  ($item['tipedokter'] != 'RDC anestesi') {
                        $dataSPD = new StrukDetailPagu();
                        $dataSPD->norec = $dataSPD->generateNewId();
                        $dataSPD->kdprofile = 0;
                        $dataSPD->statusenabled = true;
                        $dataSPD->strukpagufk = $norecSC;
                        $dataSPD->pelayananpasienfk = $item['norec_pp'];
                        $dataSPD->jenispagufk = 10;//CC DIREKSI
                        $dataSPD->jenispagupersen = null;
                        $dataSPD->jenispagunilai = $item['ccdireksi'];
                        $dataSPD->produkfk = $item['produkfk'];
//                $dataSPD->dokterid = $item['dokterid'];
                        $dataSPD->tglpelayanan = $item['tglpelayanan'];
                        $dataSPD->jumlah = $item['jumlah'];
                        $dataSPD->ruanganfk = $item['objectruanganfk'];
                        $dataSPD->namaexternal = $item['tipedokter'];
                        $dataSPD->save();
                    }
                }

                if ((float)$item['ccstaffdireksi'] >0) {
                    if  ($item['tipedokter'] != 'RDC anestesi') {
                        $dataSPD = new StrukDetailPagu();
                        $dataSPD->norec = $dataSPD->generateNewId();
                        $dataSPD->kdprofile = 0;
                        $dataSPD->statusenabled = true;
                        $dataSPD->strukpagufk = $norecSC;
                        $dataSPD->pelayananpasienfk = $item['norec_pp'];
                        $dataSPD->jenispagufk = 11;//CC STAFF DIREKSI
                        $dataSPD->jenispagupersen = null;
                        $dataSPD->jenispagunilai = $item['ccstaffdireksi'];
                        $dataSPD->produkfk = $item['produkfk'];
//                $dataSPD->dokterid = $item['dokterid'];
                        $dataSPD->tglpelayanan = $item['tglpelayanan'];
                        $dataSPD->jumlah = $item['jumlah'];
                        $dataSPD->ruanganfk = $item['objectruanganfk'];
                        $dataSPD->namaexternal = $item['tipedokter'];
                        $dataSPD->save();
                    }
                }

                if ((float)$item['ccmanajemen'] >0) {
                    if  ($item['tipedokter'] != 'RDC anestesi') {
                        $dataSPD = new StrukDetailPagu();
                        $dataSPD->norec = $dataSPD->generateNewId();
                        $dataSPD->kdprofile = 0;
                        $dataSPD->statusenabled = true;
                        $dataSPD->strukpagufk = $norecSC;
                        $dataSPD->pelayananpasienfk = $item['norec_pp'];
                        $dataSPD->jenispagufk = 12;//CC MANAJEMEN
                        $dataSPD->jenispagupersen = null;
                        $dataSPD->jenispagunilai = $item['ccmanajemen'];
                        $dataSPD->produkfk = $item['produkfk'];
//                $dataSPD->dokterid = $item['dokterid'];
                        $dataSPD->tglpelayanan = $item['tglpelayanan'];
                        $dataSPD->jumlah = $item['jumlah'];
                        $dataSPD->ruanganfk = $item['objectruanganfk'];
                        $dataSPD->namaexternal = $item['tipedokter'];
                        $dataSPD->save();
                    }
                }
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Pagu Remunerasi";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getHitungJP1Rev2(Request $request)
    {
        // : hitung remun
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

        $dataJasaPelayanan = DB::select(DB::raw(
            "
                select DISTINCT x.jpid,x.jenispagu,x.dokter as namalengkap,x.dokterid as pgid from
                (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sdp.jenispagunilai as jenispaginilaitotal,sdp.tglpelayanan,
                case when jp.id=7 then sdp.dokterid else null end as dokterid,
                sdp.norec as norec_sdp,pg.namalengkap as dokter,pg2.namalengkap as paramedis
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                left JOIN pegawai_m as pg on pg.id=sdp.dokterid
                left JOIN pegawai_m as pg2 on pg2.id=sdp.paramedisid
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0 and jp.id=7 and pg.statusenabled =1 ) as x
            "
        ));

//        $dataRemunLangsung = DB::select(DB::raw(
//            "
//              select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sdp.jenispagunilai as jenispaginilaitotal,sdp.tglpelayanan,
//                case when jp.id=7 then sdp.dokterid else null end as dokterid,
//                sdp.norec as norec_sdp,pr.namaproduk
//                from  jenispagu_t as jp
//                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
//                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
//                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
//                INNER JOIN produk_m as pr on pr.id=pp.produkfk
//                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
//                and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0
//--                group by jp.kelompokpaguid, jp.id ,jp.jenispagu,sdp.dokterid,sdp.paramedisid,sdp.tglpelayanan;
//          "
//        ));
        $dataRemunLangsung = DB::select(DB::raw(
            "
                select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sum(sdp.jenispagunilai) as jenispaginilaitotal,
                case when jp.id=7 then sdp.dokterid else null end as dokterid,sdp.norec as norec_sdp,sdp.tglpelayanan,
                pr.namaproduk
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN produk_m as pr on pr.id=pp.produkfk
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0 
                group by jp.kelompokpaguid, jp.id ,jp.jenispagu,sdp.norec,sdp.tglpelayanan,
                case when jp.id=7 then sdp.dokterid else null end ,
                pr.namaproduk
            "
        ));
        $dtRLangsung = [];
        foreach ($dataRemunLangsung as $itmLangsung ){
            if ($itmLangsung->jpid == 7 && $itmLangsung->dokterid != null){
                $dtRLangsung[] = array(
                    'pegawaiid' => (int)$itmLangsung->dokterid,
                    'jenispaginilaitotal' => (float)$itmLangsung->jenispaginilaitotal,
                    'jpid' => (int)$itmLangsung->jpid,
                    'tglpelayanan' => $itmLangsung->tglpelayanan,
                    'norec_sdp' => $itmLangsung->norec_sdp,
                    'jenis' => 'RC DOKTER',
                    'namaproduk' => $itmLangsung->namaproduk,
                    'jenispagu' => $itmLangsung->jenispagu
                );
            }

        }

        //POST REMUN
        $dataRemunTidakLangsung = DB::select(DB::raw(
            "
                select x.kelompokpaguid,x.jpid,x.jenispagu,x.jenispaginilaitotal,y.totalindex ,
                (x.jenispaginilaitotal / y.totalindex) as jmlRemunperpoint
                from 
                (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sum(sdp.jenispagunilai) as jenispaginilaitotal,pg.namakaryawan
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                left JOIN remundetailpegawai_t as pg on pg.idpegawai=sdp.dokterid
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.id =9 
                group by jp.kelompokpaguid, jp.id ,jp.jenispagu,sdp.dokterid,sdp.paramedisid,pg.namakaryawan) as x
                INNER JOIN
                (select sum(totalindex2) totalindex,9 as jpid from remundetailpegawai_t)as y on x.jpid=y.jpid
            "
        ));
        $dataPegawaiRemunTidakLangsung = DB::select(DB::raw(
            "   
                select distinct pg.idpegawai  as pgid, pg2.namalengkap,pg.totalindex2
                from mapjenispagutopegawai_t as mp
                INNER JOIN remundetailpegawai_t as pg on pg.idpegawai=mp.pegawaifk
                INNER JOIN pegawai_m as pg2 on pg2.id=pg.idpegawai
                INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                where jp.kodeexternal <> 'langsung';
            "
        ));
        foreach ($dataPegawaiRemunTidakLangsung as $itm){
            foreach ($dataRemunTidakLangsung as $itm2){
//                if ($itm->jpid == $itm2->jpid){
                $dtRLangsung[] = array(
                    'pegawaiid' => (int)$itm->pgid,
                    'jenispaginilaitotal' => (float)$itm2->jmlRemunperpoint * (float)$itm->totalindex2,
                    'jpid' => (int)7,
                    'kelompokpaguid' => (int)$itm2->kelompokpaguid,
                    'tglpelayanan' => null,
                    'norec_sdp' => null,
                    'jenis' => 'POST REMUN',
                    'namaproduk' => round($itm2->jmlRemunperpoint,3) . ' x ' . $itm->totalindex2,
                    'jenispagu' => $itm2->jenispagu
                );
//                }
            }
        }
        // END POST REMUN

        $dataRemunTidakLangsungRC = DB::select(DB::raw(
            "
                select (w.totalperruangan/x.ttlpointruangan) as nilaiperpoint,x.ruanganfk,x.namaruangan from
                (select sum(djp.point) as ttlpointruangan,djp.ruanganfk,ru.namaruangan 
                from detailjenispagu_t as djp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.detailjenispagufk=djp.id
                INNER JOIN ruangan_m as ru on ru.id=djp.ruanganfk
                where djp.statusenabled=1
                group by djp.ruanganfk,ru.namaruangan) as x
                LEFT JOIN 
                (select sum(jenispagunilai ) as totalperruangan,ruanganfk 
                from strukdetailpagu_t 
                where jenispagufk=8 and tglpelayanan between '$tglAwal' and '$tglAkhir'
                group by ruanganfk ) as w
                on x.ruanganfk=w.ruanganfk;
            "
        ));
        $dataPegawaiRemunTidakLangsungRC = DB::select(DB::raw(
            "   
                select rdp.namakaryawan, rdp.idpegawai,djp.point,djp.ruanganfk,djp.objectruanganfkarr,jp.jenispagu,jp.id as jpid,djp.detailjenispagu
                from remundetailpegawai_t as rdp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                INNER JOIN detailjenispagu_t as djp on djp.id=mp.detailjenispagufk
                INNER JOIN jenispagu_t as jp on jp.id=djp.jenispaguid
                where djp.jenispaguid=8;
            "
        ));
        $arrRuanganfk = [];
        foreach ($dataPegawaiRemunTidakLangsungRC as $itm){
            foreach ($dataRemunTidakLangsungRC as $itm2){
                $arrRuanganfk = explode (",", $itm->objectruanganfkarr);
                foreach ($arrRuanganfk as $tm){
                    if ($itm2->ruanganfk == (int)$tm){
                        $dtRLangsung[] = array(
                            'pegawaiid' => (int)$itm->idpegawai,
                            'jenispaginilaitotal' => (float)$itm->point * (float)$itm2->nilaiperpoint,
                            'kelompokpaguid' => (int)$itm->jpid,
                            'jpid' => (int)8,
                            'tglpelayanan' => null,
                            'norec_sdp' => null,
                            'jenis' => 'RC',
                            'namaproduk' => $itm->detailjenispagu,
                            'jenispagu' => $itm->jenispagu
                        );
                    }
                }
            }
        }
        $dataRemunCCManajemen = DB::select(DB::raw(
            "
                select (w.totalpagu/x.ttlpointmanajemen) as nilaiperpoint,x.jenispaguid from
                (select sum(djp.point) as ttlpointmanajemen,djp.jenispaguid
                from detailjenispagu_t as djp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.detailjenispagufk=djp.id
                where djp.statusenabled=1 and djp.jenispaguid in (12) 
                                group by djp.jenispaguid) as x
                LEFT JOIN 
                (select sum(jenispagunilai ) as totalpagu, 12 as jpid
                from strukdetailpagu_t 
                where jenispagufk in (12,11) and tglpelayanan between '$tglAwal' and '$tglAkhir' ) as w
                on x.jenispaguid=w.jpid;
            "
        ));
        $dataPegawaiCCManajemen = DB::select(DB::raw(
            "   
                select rdp.namakaryawan, rdp.idpegawai,djp.point,djp.ruanganfk,djp.objectruanganfkarr,jp.jenispagu,jp.id as jpid,djp.detailjenispagu
                from remundetailpegawai_t as rdp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                INNER JOIN detailjenispagu_t as djp on djp.id=mp.detailjenispagufk
                INNER JOIN jenispagu_t as jp on jp.id=djp.jenispaguid
                where djp.jenispaguid=12;
            "
        ));
        foreach ($dataPegawaiCCManajemen as $itm){
            foreach ($dataRemunCCManajemen as $itm2){
                $dtRLangsung[] = array(
                    'pegawaiid' => (int)$itm->idpegawai,
                    'jenispaginilaitotal' => (float)$itm2->nilaiperpoint * (float)$itm->point  ,
                    'kelompokpaguid' => (int)$itm->jpid,
                    'jpid' => (int)12,
                    'tglpelayanan' => null,
                    'norec_sdp' => null,
                    'jenis' => 'CC MANAJEMEN',
                    'namaproduk' => $itm->detailjenispagu,
                    'jenispagu' => $itm->jenispagu
                );
            }
        }


        $dataSave = $dtRLangsung;
        $result = array(
//            '$dataRemunTidakLangsungRC' => $arrRuanganfk,
//            '$dataPegawaiRemunTidakLangsungRC' => $dataPegawaiRemunTidakLangsungRC,
            'dataPegawaiRemunTidakLangsung' => $dataPegawaiRemunTidakLangsung,
            'pegawaijasapelayanan' => $dataJasaPelayanan,
            'datasave' => $dataSave,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDetailJP1(Request $request)
    {
        $noStrukPagu = $request['nostrukpagu'];
        $data = DB::select(DB::raw(
            "
                select x.namaproduk,x.produkfk,
                sum(x.remunDokter) as ttlremunDokter,sum(x.remunParamedis) as ttlremunParamedis,sum(x.remunRekamMedis) as ttlremunRekamMedis,
                sum(x.jasaSDM) as ttljasaSDM,sum(x.jasaManajemen) as ttljasaManajemen,sum(x.jasaNonStruktural) as ttljasaNonStruktural
                from (select pr.namaproduk,pr.id as produkfk,
                case when sdp.jenispagufk = 1 then sdp.jenispagunilai else 0 end as 'remunDokter',
                case when sdp.jenispagufk = 2 then sdp.jenispagunilai else 0 end as 'remunParamedis',
                case when sdp.jenispagufk = 3 then sdp.jenispagunilai else 0 end as 'remunRekamMedis',
                case when sdp.jenispagufk = 4 then sdp.jenispagunilai else 0 end as 'jasaSDM',
                case when sdp.jenispagufk = 5 then sdp.jenispagunilai else 0 end as 'jasaManajemen',
                case when sdp.jenispagufk = 6 then sdp.jenispagunilai else 0 end as 'jasaNonStruktural'
                from strukdetailpagu_t as sdp 
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispagufk
                INNER JOIN produk_m as pr on pr.id=sdp.produkfk
                where sp.nostrukpagu = '$noStrukPagu') as x
                group by x.namaproduk,x.produkfk
            "
        ));

        $data2 = DB::select(DB::raw(
            "
                select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,count(mp.pegawaifk) as jml
                from mapjenispagutopegawai_t as mp
                INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                group by jp.kelompokpaguid,jp.id ,jp.jenispagu
            "
        ));
        $result = array(
            'data' => $data,
            'datakelompokpegawai' => $data2,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function saveClosingPOSTREMUN(Request $request)
    {
        //TODO : Closing POST Remun
//        ini_set('max_execution_time', 500); //6 minutes

        DB::beginTransaction();
        try{
            $kdProfile = (int) $this->getDataKdProfile($request);
            $rq = $request->all();
            $tglAwal = $rq['head']['periodeawal'];
            $tglAkhir = $rq['head']['periodeakhir'];

            //POST REMUN
            $dataPostremunTotal = DB::select(DB::raw(
                "
                        select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sum(sdp.jenispagunilai) as jenispaginilaitotal--,pg.namakaryawan
                        from  jenispagu_t as jp 
                        INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                        --INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                        INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                        --left JOIN remundetailpegawai_t as pg on pg.idpegawai=sdp.dokterid
                        where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                        and jp.id =9 
                        and jp.kdprofile=$kdProfile
                        group by jp.kelompokpaguid, jp.id ,jp.jenispagu--,sdp.dokterid,sdp.paramedisid,pg.namakaryawan
                    "
            ));

            $dtPostRemunQTY = DB::select(DB::raw(
                "
                        select 
                        sum(case when case when pr.remunfixed is null then 0 else pr.remunfixed end > 0 then 0 else ((cast(totalindex as float)/100)*(100 -case when pr.potpersen is null then 0 else pr.potpersen end)) end ) as totalindex,
                        9 as jpid 
                        from remundetailpegawai_t as rdp
                        LEFT JOIN potonganremun_t as pr on pr.objectpegawaifk=rdp.idpegawai
                        where rdp.kdprofile=$kdProfile
                    "
            ));
            $dtPostRemunPotongan = DB::select(DB::raw(
                "
                       select sum(remunfixed) as potfixed from  potonganremun_t where objectjenispagufk=9 and kdprofile=$kdProfile;
                    "
            ));
            $remunPerPointPostRemun =  ((float)$dataPostremunTotal[0]->jenispaginilaitotal -(float)$dtPostRemunPotongan[0]->potfixed) / (float)$dtPostRemunQTY[0]->totalindex;
            $kelompokpaguPostRemun = $dataPostremunTotal[0]->kelompokpaguid;
            $jenispaguPostRemun = $dataPostremunTotal[0]->jenispagu;
            $dataPegawaiRemunTidakLangsung = DB::select(DB::raw(
                "   
                        select distinct pg.idpegawai  as pgid, --pg2.namalengkap,
                        pg.totalindex,mp.detailjenispagufk,
                        case when pr.potpersen is null then 0 else pr.potpersen end as potpersen,
                        case when pr.remunfixed is null then 0 else pr.remunfixed end as remunfixed
                        from mapjenispagutopegawai_t as mp
                        INNER JOIN remundetailpegawai_t as pg on pg.idpegawai=mp.pegawaifk
                        --INNER JOIN pegawai_m as pg2 on pg2.id=pg.idpegawai
                        INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                          left JOIN potonganremun_t as PR on pr.objectjenispagufk=jp.id and pr.objectpegawaifk=pg.idpegawai
                        where jp.kodeexternal <> 'langsung' and mp.jenispagufk=9
                        and pg.kdprofile=$kdProfile;
                    "
            ));
            foreach ($dataPegawaiRemunTidakLangsung as $itm){
                //                foreach ($dataRemunTidakLangsung as $itm2){
                //                if ($itm->jpid == $itm2->jpid){
                $potpersen = (float)$itm->potpersen;
                if ($remunPerPointPostRemun * (float)$itm->totalindex != 0){
                    if ($itm->remunfixed == 0 ) {
                        $dtRLangsung[] = array(
                            'pegawaiid' => (int)$itm->pgid,
                            'jenispaginilaitotal' => $remunPerPointPostRemun * (((float)$itm->totalindex /100)*(100-$potpersen)),
                            'jpid' => (int)9,
                            'kelompokpaguid' => (int)$kelompokpaguPostRemun,
                            'tglpelayanan' => null,
                            'norec_sdp' => null,
                            'jenis' => 'POST REMUN',
                            'namaproduk' => round($remunPerPointPostRemun,3) . ' x ' . $itm->totalindex . '/-' . $potpersen . '%',
                            'jenispagu' => $jenispaguPostRemun,
                            'detailjenispagufk' => $itm->detailjenispagufk,
                            'potpersen' => (100-$potpersen)
                        );
                    }else{
                        $dtRLangsung[] = array(
                            'pegawaiid' => (int)$itm->pgid,
                            'jenispaginilaitotal' => (float)$itm->remunfixed,
                            'jpid' => (int)9,
                            'kelompokpaguid' => (int)$kelompokpaguPostRemun,
                            'tglpelayanan' => null,
                            'norec_sdp' => null,
                            'jenis' => 'POST REMUN',
                            'namaproduk' => round($itm->remunfixed,3) . ' x ' . $itm->remunfixed,
                            'jenispagu' => $jenispaguPostRemun,
                            'detailjenispagufk' => $itm->detailjenispagufk,
                            'potpersen' => 'fix ' . $itm->remunfixed
                        );
                    }

                }
                //                }
                //                }
            }
            // END POST REMUN



            $dataSave = $dtRLangsung;
            //        $dataReq = $request->all();
            //        ini_set('max_execution_time', 500); //6 minutes

            $dataPegawai = \DB::table('loginuser_s as lu')
                ->select('lu.objectpegawaifk')
                ->where('lu.id',$rq['userData']['id'])
                ->where('lu.kdprofile',$kdProfile)
                ->first();
            $SCSC = StrukClosing::where('tglawal','>=',$rq['head']['periodeawal'])
                ->where('tglakhir','<=',$rq['head']['periodeakhir'])
                ->where('kdprofile',$kdProfile)
//                ->where('keteranganlainnya','=','PR')
                ->select('norec')
                ->update([
                    'statusenabled' => 0,
                ]);


            $nostrukClosing = $this->generateCode(new StrukClosing(), 'noclosing', 10, 'RC/' . $this->getDateTime()->format('ym'));

            $dataSC = new StrukClosing();
            $dataSC->norec = $dataSC->generateNewId();
            $dataSC->kdprofile = $kdProfile;
            $dataSC->statusenabled = true;
            $dataSC->noclosing = $nostrukClosing;
            $dataSC->tglclosing = date('Y-m-d H:i:s');
            $dataSC->tglawal = $rq['head']['periodeawal'];
            $dataSC->tglakhir = $rq['head']['periodeakhir'];
            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
            $dataSC->objectkelompoktransaksifk = 118;
//            $dataSC->keteranganlainnya = 'PR';
            $dataSC->save();

            $norecSC = $dataSC->norec;

            foreach ($dataSave as $item) {
                $dataSPD = new DetailPegawaiPagu();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = $kdProfile;
                $dataSPD->statusenabled = true;
                $dataSPD->strukclosingfk = $norecSC;
                $dataSPD->jenis = $item['jenis'];
                $dataSPD->jenispaginilaitotal = $item['jenispaginilaitotal'];
                $dataSPD->jpid = $item['jpid'];
                //                $dataSPD->kelompokpaguid = $item['kelompokpaguid'];
                $dataSPD->kodeexternal = $item['potpersen'];
                $dataSPD->norec_sdp = $item['norec_sdp'];
                $dataSPD->pegawaiid = $item['pegawaiid'];
                $dataSPD->tglpelayanan = $item['tglpelayanan'];
                $dataSPD->djpid = $item['detailjenispagufk'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Closing Pagu POST REMUN";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => count($dataSave),
                "norecsc" => $norecSC,
                "request" => $rq,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveClosingRCDokter(Request $request)
    {
        //TODO : Closing RC DOKTER
//        ini_set('max_execution_time', 1000); //6 minutes
        DB::beginTransaction();
        try{
            $rq = $request->all();
            $tglAwal = $rq['head']['periodeawal'];
            $tglAkhir = $rq['head']['periodeakhir'];

            $dataJasaPelayanan = DB::select(DB::raw(
                "
                        select DISTINCT x.jpid,x.jenispagu,--x.dokter as namalengkap,
                        x.dokterid as pgid from
                        (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sdp.jenispagunilai as jenispaginilaitotal,sdp.tglpelayanan,
                        case when jp.id=7 then sdp.dokterid else null end as dokterid,
                        sdp.norec as norec_sdp--,pg.namalengkap as dokter,pg2.namalengkap as paramedis
                        from  jenispagu_t as jp 
                        INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                        INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                        --left JOIN pegawai_m as pg on pg.id=sdp.dokterid
                        --left JOIN pegawai_m as pg2 on pg2.id=sdp.paramedisid
                        where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                        and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0 and jp.id=7 
                        --and pg.statusenabled =1 
                        ) as x
                    "
            ));

//            $dataRemunLangsung = DB::select(DB::raw(
//                "
//                        select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sum(sdp.jenispagunilai) as jenispaginilaitotal,
//                        case when jp.id=7 then sdp.dokterid else null end as dokterid,sdp.norec as norec_sdp,sdp.tglpelayanan,
//                        pr.namaproduk
//                        from  jenispagu_t as jp
//                        INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
//                        INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
//                        INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
//                        INNER JOIN produk_m as pr on pr.id=pp.produkfk
//                        where sp.periodeawal between '$tglAwal' and '$tglAkhir'
//                        and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0
//                        group by jp.kelompokpaguid, jp.id ,jp.jenispagu,sdp.norec,sdp.tglpelayanan,
//                        case when jp.id=7 then sdp.dokterid else null end ,
//                        pr.namaproduk
//                    "
//            ));
            $dataRemunLangsung = DB::select(DB::raw(
                "
                        select  sum(sdp.jenispagunilai) as jenispaginilaitotal,
                    sdp.dokterid  as dokterid
                    from   strukdetailpagu_t as sdp
                    INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                    where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                    and sdp.dokterid <> 0
                    group by sdp.dokterid
                "
            ));
            $dtRLangsung = [];
            foreach ($dataRemunLangsung as $itmLangsung ){
                if ($itmLangsung->dokterid != null){
                    if ((float)$itmLangsung->jenispaginilaitotal != 0){
                        $dtRLangsung[] = array(
                            'pegawaiid' => (int)$itmLangsung->dokterid,
                            'jenispaginilaitotal' => (float)$itmLangsung->jenispaginilaitotal,
                            'jpid' => 7,//(int)$itmLangsung->jpid,
                            'tglpelayanan' => null,//$itmLangsung->tglpelayanan,
                            'norec_sdp' => null,//$itmLangsung->norec_sdp,
                            'jenis' => 'RC DOKTER',
                            'namaproduk' => '',//$itmLangsung->namaproduk,
                            'jenispagu' => 'RC DOKTER',//$itmLangsung->jenispagu,
                            'detailjenispagufk' => 1,
                            'potpersen' => 0,
                        );
                    }
                }

            }
//            foreach ($dataRemunLangsung as $itmLangsung ){
//                if ($itmLangsung->jpid == 7 && $itmLangsung->dokterid != null){
//                    if ((float)$itmLangsung->jenispaginilaitotal != 0){
//                        $dtRLangsung[] = array(
//                            'pegawaiid' => (int)$itmLangsung->dokterid,
//                            'jenispaginilaitotal' => (float)$itmLangsung->jenispaginilaitotal,
//                            'jpid' => (int)$itmLangsung->jpid,
//                            'tglpelayanan' => $itmLangsung->tglpelayanan,
//                            'norec_sdp' => $itmLangsung->norec_sdp,
//                            'jenis' => 'RC DOKTER',
//                            'namaproduk' => $itmLangsung->namaproduk,
//                            'jenispagu' => $itmLangsung->jenispagu,
//                            'detailjenispagufk' => 1,
//                            'potpersen' => 0,
//                        );
//                    }
//                }
//
//            }

            $dataSave = $dtRLangsung;

//            $dataPegawai = \DB::table('loginuser_s as lu')
//                ->select('lu.objectpegawaifk')
//                ->where('lu.id',$rq['userData']['id'])
//                ->first();
//            $SCSC = StrukClosing::where('tglawal','>=',$rq['head']['periodeawal'])
//                ->where('tglakhir','<=',$rq['head']['periodeakhir'])
//                ->where('keteranganlainnya','=','RCD')
//                ->select('norec')
//                ->update([
//                    'statusenabled' => 0,
//                ]);
//
//
//            $nostrukClosing = $this->generateCode(new StrukClosing(), 'noclosing', 10, 'RC/' . $this->getDateTime()->format('ym'));
//
//            $dataSC = new StrukClosing();
//            $dataSC->norec = $dataSC->generateNewId();
//            $dataSC->kdprofile = 0;
//            $dataSC->statusenabled = true;
//            $dataSC->noclosing = $nostrukClosing;
//            $dataSC->tglclosing = date('Y-m-d H:i:s');
//            $dataSC->tglawal = $rq['head']['periodeawal'];
//            $dataSC->tglakhir = $rq['head']['periodeakhir'];
//            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
//            $dataSC->objectkelompoktransaksifk = 118;
//            $dataSC->keteranganlainnya = 'RCD';
//            $dataSC->save();

            $norecSC = $rq['head']['norecsc'];//$dataSC->norec;

            foreach ($dataSave as $item) {
                $dataSPD = new DetailPegawaiPagu();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = 0;
                $dataSPD->statusenabled = true;
                $dataSPD->strukclosingfk = $norecSC;
                $dataSPD->jenis = $item['jenis'];
                $dataSPD->jenispaginilaitotal = $item['jenispaginilaitotal'];
                $dataSPD->jpid = $item['jpid'];
                //                $dataSPD->kelompokpaguid = $item['kelompokpaguid'];
                $dataSPD->kodeexternal = $item['potpersen'];
                $dataSPD->norec_sdp = $item['norec_sdp'];
                $dataSPD->pegawaiid = $item['pegawaiid'];
                $dataSPD->tglpelayanan = $item['tglpelayanan'];
                $dataSPD->djpid = $item['detailjenispagufk'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Closing Pagu RC DOKTER";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => count($dataSave),
                "request" => $rq,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveClosingRC(Request $request)
    {
        //TODO : Closing RC
//        ini_set('max_execution_time', 500); //6 minutes
        DB::beginTransaction();
        try{
            $rq = $request->all();
            $tglAwal = $rq['head']['periodeawal'];
            $tglAkhir = $rq['head']['periodeakhir'];

            $dataRC = DB::select(DB::raw(
                "
                        select agus.ruanganfk,
                        (epic.totalperruangan-case when epic.potperruangan is null then 0 else epic.potperruangan end)/agus.ttlpointruangan as nilaiperpoint 
                        from 
                        (select sum(
                        case when case when pr.remunfixed is null then 0 else pr.remunfixed end  > 0 then 0 else((cast((djp.point) as float)/100)*(100-case when pr.potpersen is null then 0 else pr.potpersen end)) end) as ttlpointruangan, 
                        djp.ruanganfk
                        from detailjenispagu_t as djp
                        INNER JOIN mapjenispagutopegawai_t as mp on mp.detailjenispagufk=djp.id
                        left JOIN potonganremun_t as pr on pr.objectjenispagufk=djp.jenispaguid and pr.objectpegawaifk=mp.pegawaifk
                        where djp.statusenabled=1 --and mp.pegawaifk=22994
                        group by djp.ruanganfk) as agus
                        INNER JOIN
                        (select x.totalperruangan,x.ruanganfk,y.potperruangan from 
                        (select sum(sdp.jenispagunilai ) as totalperruangan,sdp.ruanganfk 
                        from strukdetailpagu_t as sdp
                        INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                        INNER JOIN produk_m as pr on pr.id=pp.produkfk 
                        where sdp.jenispagufk=8 and sdp.tglpelayanan between '$tglAwal' and '$tglAkhir' 
                        and pr.namaproduk not ilike '%eeg%'
                        group by sdp.ruanganfk) as x
                        left JOIN 
                        (select sum(pr.remunfixed) as potperruangan,djp.ruanganfk from potonganremun_t as pr 
                        INNER JOIN detailjenispagu_t as djp on djp.id=pr.objectdetailjenispagufk
                        where pr.objectjenispagufk=8 and pr.remunfixed >0
                        group by djp.ruanganfk ) as y on x.ruanganfk=y.ruanganfk) as epic
                        on agus.ruanganfk=epic.ruanganfk
                    "
            ));

            $dataPegawaiRemunTidakLangsungRC = DB::select(DB::raw(
                "   
                        select rdp.namakaryawan, rdp.idpegawai,(cast(djp.point as float)/100) *(100-case when pr.potpersen is null then 0 else pr.potpersen end) as point,
                        djp.ruanganfk,djp.objectruanganfkarr,jp.jenispagu,jp.id as jpid,djp.detailjenispagu,
                        mp.detailjenispagufk,(100-case when pr.potpersen is null then 0 else pr.potpersen end) as potpersen,
                        case when pr.remunfixed is null then 0 else pr.remunfixed end as remunfixed
                        from remundetailpegawai_t as rdp
                        INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                        INNER JOIN detailjenispagu_t as djp on djp.id=mp.detailjenispagufk
                        INNER JOIN jenispagu_t as jp on jp.id=djp.jenispaguid
                        LEFT JOIN potonganremun_t as pr on pr.objectjenispagufk=jp.id and pr.objectpegawaifk=rdp.idpegawai
                        where djp.jenispaguid=8
                    "
            ));

            $dataEEGMasukKeKaruUnitStroke = DB::select(DB::raw(
                "   
                    select sdp.* from strukdetailpagu_t  as sdp
                    INNER JOIN produk_m as pr on pr.id=sdp.produkfk
                    where  pr.namaproduk ilike '%eeg%' 
                    and sdp.tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'
                    and sdp.jenispagufk=8;
                "
            ));

            $dataPegawaiKaruUnitStroke = DB::select(DB::raw(
                "   
                    select pegawaifk from mapjenispagutopegawai_t where detailjenispagufk=41;
                "
            ));
            foreach ($dataEEGMasukKeKaruUnitStroke as $tuyul){
                $dtRLangsung[] = array(
                    'pegawaiid' => (int)$dataPegawaiKaruUnitStroke[0]->pegawaifk,
                    'jenispaginilaitotal' => (float)$tuyul->jenispagunilai,
                    'kelompokpaguid' => (int)1,
                    'jpid' => (int)8,
                    'tglpelayanan' => null,
                    'norec_sdp' => $tuyul->norec,
                    'jenis' => 'RC',
                    'namaproduk' => 'eeg',
                    'jenispagu' => 'RC',
                    'detailjenispagufk' => 41,
                    'potpersen' => 0,
                );
            }
            $arrRuanganfk = [];
            foreach ($dataPegawaiRemunTidakLangsungRC as $itm){
                foreach ($dataRC as $itm2){
                    $arrRuanganfk = $itm->ruanganfk;//explode (",", $itm->objectruanganfkarr);
                    //                    foreach ($arrRuanganfk as $tm){
                    if ($itm2->ruanganfk == (int)$arrRuanganfk){
                        if ((float)$itm->point * (float)$itm2->nilaiperpoint != 0){
                            if ($itm->remunfixed == 0 ) {
                                $dtRLangsung[] = array(
                                    'pegawaiid' => (int)$itm->idpegawai,
                                    'jenispaginilaitotal' => (float)$itm->point * (float)$itm2->nilaiperpoint,
                                    'kelompokpaguid' => (int)$itm->jpid,
                                    'jpid' => (int)8,
                                    'tglpelayanan' => null,
                                    'norec_sdp' => null,
                                    'jenis' => 'RC',
                                    'namaproduk' => $itm->detailjenispagu,
                                    'jenispagu' => $itm->jenispagu,
                                    'detailjenispagufk' => $itm->detailjenispagufk,
                                    'potpersen' => $itm->potpersen,
                                );
                            }else{
                                $dtRLangsung[] = array(
                                    'pegawaiid' => (int)$itm->idpegawai,
                                    'jenispaginilaitotal' => (float)$itm->remunfixed,
                                    'kelompokpaguid' => (int)$itm->jpid,
                                    'jpid' => (int)8,
                                    'tglpelayanan' => null,
                                    'norec_sdp' => null,
                                    'jenis' => 'RC',
                                    'namaproduk' => $itm->detailjenispagu,
                                    'jenispagu' => $itm->jenispagu,
                                    'detailjenispagufk' => $itm->detailjenispagufk,
                                    'potpersen' => 'fix ' .  $itm->remunfixed,
                                );
                            }
                        }
                    }
                    //                    }
                }
            }

            $dataSave = $dtRLangsung;
            //        $dataReq = $request->all();
            //        ini_set('max_execution_time', 500); //6 minutes

//            $dataPegawai = \DB::table('loginuser_s as lu')
//                ->select('lu.objectpegawaifk')
//                ->where('lu.id',$rq['userData']['id'])
//                ->first();
//            $SCSC = StrukClosing::where('tglawal','>=',$rq['head']['periodeawal'])
//                ->where('tglakhir','<=',$rq['head']['periodeakhir'])
//                ->where('keteranganlainnya','=','RC')
//                ->select('norec')
//                ->update([
//                    'statusenabled' => 0,
//                ]);
//
//
//            $nostrukClosing = $this->generateCode(new StrukClosing(), 'noclosing', 10, 'RC/' . $this->getDateTime()->format('ym'));
//
//            $dataSC = new StrukClosing();
//            $dataSC->norec = $dataSC->generateNewId();
//            $dataSC->kdprofile = 0;
//            $dataSC->statusenabled = true;
//            $dataSC->noclosing = $nostrukClosing;
//            $dataSC->tglclosing = date('Y-m-d H:i:s');
//            $dataSC->tglawal = $rq['head']['periodeawal'];
//            $dataSC->tglakhir = $rq['head']['periodeakhir'];
//            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
//            $dataSC->objectkelompoktransaksifk = 118;
//            $dataSC->keteranganlainnya = 'RC';
//            $dataSC->save();

            $norecSC = $rq['head']['norecsc'];//$dataSC->norec;

            foreach ($dataSave as $item) {
                $dataSPD = new DetailPegawaiPagu();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = 0;
                $dataSPD->statusenabled = true;
                $dataSPD->strukclosingfk = $norecSC;
                $dataSPD->jenis = $item['jenis'];
                $dataSPD->jenispaginilaitotal = $item['jenispaginilaitotal'];
                $dataSPD->jpid = $item['jpid'];
                $dataSPD->namaexternal = $item['namaproduk'];
                $dataSPD->kodeexternal = $item['potpersen'];
                $dataSPD->norec_sdp = $item['norec_sdp'];
                $dataSPD->pegawaiid = $item['pegawaiid'];
                $dataSPD->tglpelayanan = $item['tglpelayanan'];
                $dataSPD->djpid = $item['detailjenispagufk'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Closing Pagu RC";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => count($dataSave),
                "request" => $rq,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveClosingCC(Request $request)
    {
        //TODO : Closing CC
//        ini_set('max_execution_time', 500); //6 minutes
        DB::beginTransaction();
        try{
            $rq = $request->all();
            $tglAwal = $rq['head']['periodeawal'];
            $tglAkhir = $rq['head']['periodeakhir'];

//            $dataRemunCCManajemen = DB::select(DB::raw(
//                "
//                        select (w.totalpagu/x.ttlpointmanajemen) as nilaiperpoint,x.jenispaguid from
//                        (select sum(djp.point) as ttlpointmanajemen,djp.jenispaguid
//                        from detailjenispagu_t as djp
//                        INNER JOIN mapjenispagutopegawai_t as mp on mp.detailjenispagufk=djp.id
//                        where djp.statusenabled=1 and djp.jenispaguid in (12,10)
//                                        group by djp.jenispaguid) as x
//                        LEFT JOIN
//                        (select sum(sdp.jenispagunilai ) as totalpagu, 10 as jpid
//                        from strukdetailpagu_t as sdp
//                        INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
//                        where sdp.jenispagufk in (12,10) and sdp. tglpelayanan between '$tglAwal' and '$tglAkhir' ) as w
//                        on x.jenispaguid=w.jpid;
//                    "
//            ));
            $dataRemunCCDIREKDITOTAL = DB::select(DB::raw(
                "
                        select sum(sdp.jenispagunilai ) as totalpagu, 10 as jpid
                        from strukdetailpagu_t as sdp
                        INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                        where sdp.jenispagufk in (12,10) and sdp. tglpelayanan between '$tglAwal' and '$tglAkhir'
                    "
            ));
            $norecSC = $rq['head']['norecsc'];//$dataSC->norec;
            $dataRemunCCKABID19 = DB::select(DB::raw(
                "
                    select sum(dpp.jenispaginilaitotal) as totalPRCCSTAFF from strukclosing_t as sc
                    INNER JOIN detailpegawaipagu_t as dpp on dpp.strukclosingfk=sc.norec
                    INNER JOIN mapjenispagutopegawai_t as mp on  mp.pegawaifk=dpp.pegawaiid
                    where sc.norec= '$norecSC' and dpp.jpid in (10,9) and mp.jenispagufk=10;
                 "
            ));
//            $dataRemunCCPOint = DB::select(DB::raw(
//                "
//                        select sum(djp.point) as ttlpointmanajemen,djp.jenispaguid
//                        from detailjenispagu_t as djp
//                        INNER JOIN mapjenispagutopegawai_t as mp on mp.detailjenispagufk=djp.id
//                        where djp.statusenabled=1 and djp.jenispaguid in (12,10)
//                        group by djp.jenispaguid
//                    "
//            ));
            $dataPegawaiCCManajemen = DB::select(DB::raw(
                "   
                        select rdp.namakaryawan, rdp.idpegawai,djp.point,djp.ruanganfk,djp.objectruanganfkarr,jp.jenispagu,jp.id as jpid,djp.detailjenispagu,
                        mp.detailjenispagufk
                        from remundetailpegawai_t as rdp
                        INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                        INNER JOIN detailjenispagu_t as djp on djp.id=mp.detailjenispagufk
                        INNER JOIN jenispagu_t as jp on jp.id=djp.jenispaguid
                        where djp.jenispaguid=10;
                    "
            ));
            $remunTotal = (float)$dataRemunCCKABID19[0]->totalPRCCSTAFF + (float)$dataRemunCCDIREKDITOTAL[0]->totalpagu;
            $dir112 = ($remunTotal /100)*30;//30%
            $wadir113 = ($remunTotal /100)*8;//8%
            $kabid114 = ($remunTotal /100)*22;//22%
            $kasubag115 = ($remunTotal /100)*40;//40%
            foreach ($dataPegawaiCCManajemen as $itm){
                if  ($itm->detailjenispagufk == 112){//1
                    $dtRLangsung[] = array(
                        'pegawaiid' => (int)$itm->idpegawai,
                        'jenispaginilaitotal' => ((float)$dir112)   ,
                        'kelompokpaguid' => (int)$itm->jpid,
                        'jpid' => (int)10,
                        'tglpelayanan' => null,
                        'norec_sdp' => null,
                        'jenis' => 'CC DIREKSI',
                        'namaproduk' => $itm->detailjenispagu,
                        'jenispagu' => $itm->jenispagu,
                        'detailjenispagufk' => $itm->detailjenispagufk,
                        'potpersen' => 0,
                    );
                }
                if  ($itm->detailjenispagufk == 113){//2
                    $dtRLangsung[] = array(
                        'pegawaiid' => (int)$itm->idpegawai,
                        'jenispaginilaitotal' => ((float)$wadir113/2)   ,
                        'kelompokpaguid' => (int)$itm->jpid,
                        'jpid' => (int)10,
                        'tglpelayanan' => null,
                        'norec_sdp' => null,
                        'jenis' => 'CC DIREKSI',
                        'namaproduk' => $itm->detailjenispagu,
                        'jenispagu' => $itm->jenispagu,
                        'detailjenispagufk' => $itm->detailjenispagufk,
                        'potpersen' => 0,
                    );
                }
                if  ($itm->detailjenispagufk == 114){//6
                    $dtRLangsung[] = array(
                        'pegawaiid' => (int)$itm->idpegawai,
                        'jenispaginilaitotal' => ((float)$kabid114/6)   ,
                        'kelompokpaguid' => (int)$itm->jpid,
                        'jpid' => (int)10,
                        'tglpelayanan' => null,
                        'norec_sdp' => null,
                        'jenis' => 'CC DIREKSI',
                        'namaproduk' => $itm->detailjenispagu,
                        'jenispagu' => $itm->jenispagu,
                        'detailjenispagufk' => $itm->detailjenispagufk,
                        'potpersen' => 0,
                    );
                }
                if  ($itm->detailjenispagufk == 115){//13
                    $dtRLangsung[] = array(
                        'pegawaiid' => (int)$itm->idpegawai,
                        'jenispaginilaitotal' => ((float)$kasubag115/13)   ,
                        'kelompokpaguid' => (int)$itm->jpid,
                        'jpid' => (int)10,
                        'tglpelayanan' => null,
                        'norec_sdp' => null,
                        'jenis' => 'CC DIREKSI',
                        'namaproduk' => $itm->detailjenispagu,
                        'jenispagu' => $itm->jenispagu,
                        'detailjenispagufk' => $itm->detailjenispagufk,
                        'potpersen' => 0,
                    );
                }
            }


            $dataSave = $dtRLangsung;
            $dt = DB::raw(
                "
                        update detailpegawaipagu_t set statusenabled = 0
                        where strukclosingfk='$norecSC';
                     "
            );
            //        $dataReq = $request->all();
            //        ini_set('max_execution_time', 500); //6 minutes

//            $dataPegawai = \DB::table('loginuser_s as lu')
//                ->select('lu.objectpegawaifk')
//                ->where('lu.id',$rq['userData']['id'])
//                ->first();
//            $SCSC = StrukClosing::where('tglawal','>=',$rq['head']['periodeawal'])
//                ->where('tglakhir','<=',$rq['head']['periodeakhir'])
//                ->where('keteranganlainnya','=','CC')
//                ->select('norec')
//                ->update([
//                    'statusenabled' => 0,
//                ]);
//
//
//            $nostrukClosing = $this->generateCode(new StrukClosing(), 'noclosing', 10, 'RC/' . $this->getDateTime()->format('ym'));
//
//            $dataSC = new StrukClosing();
//            $dataSC->norec = $dataSC->generateNewId();
//            $dataSC->kdprofile = 0;
//            $dataSC->statusenabled = true;
//            $dataSC->noclosing = $nostrukClosing;
//            $dataSC->tglclosing = date('Y-m-d H:i:s');
//            $dataSC->tglawal = $rq['head']['periodeawal'];
//            $dataSC->tglakhir = $rq['head']['periodeakhir'];
//            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
//            $dataSC->objectkelompoktransaksifk = 118;
//            $dataSC->keteranganlainnya = 'CC';
//            $dataSC->save();

//            $norecSC = $rq['head']['norecsc'];//$dataSC->norec;

            foreach ($dataSave as $item) {
                $dataSPD = new DetailPegawaiPagu();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = 0;
                $dataSPD->statusenabled = true;
                $dataSPD->strukclosingfk = $norecSC;
                $dataSPD->jenis = $item['jenis'];
                $dataSPD->jenispaginilaitotal = $item['jenispaginilaitotal'];
                $dataSPD->jpid = $item['jpid'];
                //                $dataSPD->kelompokpaguid = $item['kelompokpaguid'];
                $dataSPD->kodeexternal = $item['potpersen'];
                $dataSPD->norec_sdp = $item['norec_sdp'];
                $dataSPD->pegawaiid = $item['pegawaiid'];
                $dataSPD->tglpelayanan = $item['tglpelayanan'];
                $dataSPD->djpid = $item['detailjenispagufk'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Closing Pagu CC";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => count($dataSave),
                "request" => $rq,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveClosingCCStaff(Request $request)
    {
        //TODO : Closing CC Staff
//        ini_set('max_execution_time', 500); //6 minutes
        DB::beginTransaction();
        try{
            $rq = $request->all();
            $tglAwal = $rq['head']['periodeawal'];
            $tglAkhir = $rq['head']['periodeakhir'];

            $dataRemunCCManajemen = DB::select(DB::raw(
                "
                        select (w.totalpagu/x.ttlpointmanajemen) as nilaiperpoint,x.jenispaguid from
                        (select sum(djp.point) as ttlpointmanajemen,djp.jenispaguid
                        from detailjenispagu_t as djp
                        INNER JOIN mapjenispagutopegawai_t as mp on mp.detailjenispagufk=djp.id
                        where djp.statusenabled=1 and djp.jenispaguid in (11) 
                                        group by djp.jenispaguid) as x
                        LEFT JOIN 
                        (select sum(sdp.jenispagunilai ) as totalpagu, 11 as jpid
                        from strukdetailpagu_t as sdp
                        INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                        where sdp.jenispagufk in (11) and sdp. tglpelayanan between '$tglAwal' and '$tglAkhir' ) as w
                        on x.jenispaguid=w.jpid;
                    "
            ));
            $dataPegawaiCCManajemen = DB::select(DB::raw(
                "   
                        select rdp.namakaryawan, rdp.idpegawai,djp.point,djp.ruanganfk,djp.objectruanganfkarr,jp.jenispagu,jp.id as jpid,djp.detailjenispagu,
                        mp.detailjenispagufk
                        from remundetailpegawai_t as rdp
                        INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                        INNER JOIN detailjenispagu_t as djp on djp.id=mp.detailjenispagufk
                        INNER JOIN jenispagu_t as jp on jp.id=djp.jenispaguid
                        where djp.jenispaguid=11;
                    "
            ));
            foreach ($dataPegawaiCCManajemen as $itm){
                foreach ($dataRemunCCManajemen as $itm2){
                    if ((float)$itm2->nilaiperpoint * (float)$itm->point  != 0){
                        $dtRLangsung[] = array(
                            'pegawaiid' => (int)$itm->idpegawai,
                            'jenispaginilaitotal' => (float)$itm2->nilaiperpoint * (float)$itm->point  ,
                            'kelompokpaguid' => (int)$itm->jpid,
                            'jpid' => (int)11,
                            'tglpelayanan' => null,
                            'norec_sdp' => null,
                            'jenis' => 'CC MANAJEMEN',
                            'namaproduk' => $itm->detailjenispagu,
                            'jenispagu' => $itm->jenispagu,
                            'detailjenispagufk' => $itm->detailjenispagufk,
                            'potpersen' => 0,
                        );
                    }
                }
            }


            $dataSave = $dtRLangsung;
            //        $dataReq = $request->all();
            //        ini_set('max_execution_time', 500); //6 minutes

//            $dataPegawai = \DB::table('loginuser_s as lu')
//                ->select('lu.objectpegawaifk')
//                ->where('lu.id',$rq['userData']['id'])
//                ->first();
//            $SCSC = StrukClosing::where('tglawal','>=',$rq['head']['periodeawal'])
//                ->where('tglakhir','<=',$rq['head']['periodeakhir'])
//                ->where('keteranganlainnya','=','CC')
//                ->select('norec')
//                ->update([
//                    'statusenabled' => 0,
//                ]);
//
//
//            $nostrukClosing = $this->generateCode(new StrukClosing(), 'noclosing', 10, 'RC/' . $this->getDateTime()->format('ym'));
//
//            $dataSC = new StrukClosing();
//            $dataSC->norec = $dataSC->generateNewId();
//            $dataSC->kdprofile = 0;
//            $dataSC->statusenabled = true;
//            $dataSC->noclosing = $nostrukClosing;
//            $dataSC->tglclosing = date('Y-m-d H:i:s');
//            $dataSC->tglawal = $rq['head']['periodeawal'];
//            $dataSC->tglakhir = $rq['head']['periodeakhir'];
//            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
//            $dataSC->objectkelompoktransaksifk = 118;
//            $dataSC->keteranganlainnya = 'CC';
//            $dataSC->save();

            $norecSC = $rq['head']['norecsc'];//$dataSC->norec;

            foreach ($dataSave as $item) {
                $dataSPD = new DetailPegawaiPagu();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = 0;
                $dataSPD->statusenabled = true;
                $dataSPD->strukclosingfk = $norecSC;
                $dataSPD->jenis = $item['jenis'];
                $dataSPD->jenispaginilaitotal = $item['jenispaginilaitotal'];
                $dataSPD->jpid = $item['jpid'];
                //                $dataSPD->kelompokpaguid = $item['kelompokpaguid'];
                $dataSPD->kodeexternal = $item['potpersen'];
                $dataSPD->norec_sdp = $item['norec_sdp'];
                $dataSPD->pegawaiid = $item['pegawaiid'];
                $dataSPD->tglpelayanan = $item['tglpelayanan'];
                $dataSPD->djpid = $item['detailjenispagufk'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Closing Pagu CC Staff";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => count($dataSave),
                "request" => $rq,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDaftarJP1Rev2(Request $request)
    {
        $kdProfile = (int) $this->getDataKdProfile($request);
//        $dataLogin = $request->all();
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
//        $data = DB::select(DB::raw(
//            "
//              select sp.norec,sp.nostrukpagu,sp.tglstrukpagu,sp.periodeawal,sp.periodeakhir,
//                sp.totalrcdokter as totalrcdokter, sp.totalpostrm as totalpostrm, sp.totalrc as totalrc,
//                sp.totalccdireksi as totalccdireksi, sp.totalccstaffdireksi as totalccstaffdireksi, sp.totalccmanajemen as totalccmanajemen
//                 from strukpagu_t as sp
//                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
//          "
//        ));

        /**
         * Ambil reguler aja
         */
        $data = DB::select(DB::raw("
            select x.norec,x.nostrukpagu,x.tglstrukpagu,x.periodeawal,x.periodeakhir,
            sum(x.totalrcdokter) as totalrcdokter,sum(x.totalrc) as totalrc,sum(x.totalpostrm) as totalpostrm,
            sum(x.totalccdireksi) as totalccdireksi,sum(x.totalccstaffdireksi) as totalccstaffdireksi,
            sum(x.totalccmanajemen) as totalccmanajemen
            from (
                select sp.norec,sp.nostrukpagu,sp.tglstrukpagu,sp.periodeawal,sp.periodeakhir,
                    case when sdp.jenispagufk  = 13 then sum(sdp.jenispagunilai) else 0 end as totalrcdokter,
                    case when sdp.jenispagufk  = 14 then sum(sdp.jenispagunilai) else 0 end as totalrc,
                    case when sdp.jenispagufk  = 15 then sum(sdp.jenispagunilai) else 0 end as totalpostrm,
                    case when sdp.jenispagufk  = 16 then sum(sdp.jenispagunilai) else 0 end as totalccdireksi,
                    case when sdp.jenispagufk  = 17 then sum(sdp.jenispagunilai) else 0 end as totalccstaffdireksi,
                    case when sdp.jenispagufk  = 18 then sum(sdp.jenispagunilai) else 0 end as totalccmanajemen
                    from strukdetailpagu_t as sdp
                    INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                    INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
                    -- INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispagufk
                    where sp.periodeawal BETWEEN '$tglAwal' and '$tglAkhir'
                    and sdp.tglpelayanan  > '2019-05-31 23:59'
                    and ru.iseksekutif = false
                    and sdp.kdprofile =$kdProfile
                    group by sp.norec,sp.nostrukpagu,sp.tglstrukpagu,sp.periodeawal,sp.periodeakhir,sdp.jenispagufk
            )as x
            group by x.norec,x.nostrukpagu,x.tglstrukpagu,x.periodeawal,x.periodeakhir
        "
        ));

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getListPegawai(Request $request)
    {
        $teks = $request['teks'];
        $field = $request['field'];
        $jenispaguid = $request['jenispagufk'];
//        if ($jenispaguid == '-'){
//            $data = DB::select(DB::raw(
//                "
//              select pg.id , pg.namalengkap,jb.namajabatan,kj.namakelompokjabatan ,jp.jenispegawai
//                from pegawai_m as pg
//                left JOIN kelompokjabatan_m as kj on kj.id=pg.objectkelompokjabatanfk
//                left JOIN jabatan_m as jb on jb.id=pg.objectjabatanstrukturalfk
//                left JOIN jenispegawai_m as jp on jp.id=pg.objectjenispegawaifk
//                where $field like '%$teks%'
//          "
//            ));
//        }else{
//            $data = DB::select(DB::raw(
//                "
//              select pg.id , pg.namalengkap,jb.namajabatan,kj.namakelompokjabatan ,jp.jenispegawai
//                from pegawai_m as pg
//                left JOIN kelompokjabatan_m as kj on kj.id=pg.objectkelompokjabatanfk
//                left JOIN jabatan_m as jb on jb.id=pg.objectjabatanstrukturalfk
//                left JOIN jenispegawai_m as jp on jp.id=pg.objectjenispegawaifk
//                where $field like '%$teks%'
//                and pg.id not in (select pegawaifk from  mapjenispagutopegawai_t where jenispagufk=$jenispaguid)
//          "
//            ));
//        }
//        if ($jenispaguid == '-'){
        $datastr = " where $field like '%$teks%'";
//        }else{
//            $datastr = "where $field like '%$teks%'
//                and idpegawai not in (select pegawaifk from  mapjenispagutopegawai_t where jenispagufk=$jenispaguid)";
//        }
        if($teks == ''){
            $datastr = '';
        }
        $data = DB::select(DB::raw(
            "
                    select idpegawai , namakaryawan,jabatan,unitbagianinstalasi,golongan
                from remundetailpegawai_t
                
            " . $datastr
        ));
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDataCombo(Request $request)
    {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $data = DB::select(DB::raw(
            "
                select * from jenispagu_t where 
                statusenabled = true
                and kdprofile   =$kdProfile
            "
        ));
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDetailPotonganRemunPegawai(Request $request)
    {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $idpegawai = $request['pegawaifk'];
        $data = DB::select(DB::raw(
            "

                select jp.jenispagu,pot.*,jp.id as jpid,djp.detailjenispagu from potonganremun_t as pot
                INNER JOIN jenispagu_t as jp on jp.id=pot.objectjenispagufk
                INNER JOIN detailjenispagu_t as djp on djp.id=pot.objectdetailjenispagufk
                where objectpegawaifk=$idpegawai
                and pot.kdprofile=$kdProfile;

            "
        ));
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function savePotonganRemun(Request $request) {
        DB::beginTransaction();
        $idpot = $request['idpot'];
        $kdProfile = (int) $this->getDataKdProfile($request);
        try {
            if ($idpot == ''){
                $newId = PotonganRemun::max('id');
                $newId = $newId + 1;

                $dataSC = new PotonganRemun();
                $dataSC->id = $newId;
                $dataSC->norec = $dataSC->generateNewId();
                $dataSC->kdprofile = $kdProfile;
                $dataSC->statusenabled = true;
            }else{
                $dataSC = PotonganRemun::where('id','=',$idpot)->first();
            }


            $dataSC->objectpegawaifk = $request['objectpegawaifk'];
            $dataSC->potpersen = $request['potpersen'];
            $dataSC->remunfixed = $request['remunfixed'];
            $dataSC->objectjenispagufk = $request['objectjenispagufk'];
            $dataSC->objectdetailjenispagufk = $request['objectdetailjenispagufk'];
            $dataSC->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus Potongan Remun';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
//                "data" => $norec,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveMapJenisPaguToPegawai(Request $request) {
        DB::beginTransaction();
        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataReq = $request->all();
        try{

            foreach ($dataReq['data'] as $item) {
                $dataSPD = new MapJenisPaguToPegawai();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = $kdProfile;
                $dataSPD->statusenabled = true;
                $dataSPD->jenispagufk = $dataReq['jenispaguid'];
                $dataSPD->detailjenispagufk = $dataReq['detailjenispaguid'];
                $dataSPD->pegawaifk = (int)$item['idpegawai'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Map Jenis Pagu to Pegawai";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getPegawaiByJenisPagu(Request $request)
    {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $jpid = $request['jpid'];
        $data = DB::select(DB::raw(
            "
                select mp.norec, pg.idpegawai as pgid, pg.namakaryawan as namalengkap,jp.id as jpid,jp.jenispagu 
                --,jb.namajabatan,kj.namakelompokjabatan ,jpg.jenispegawai
                from mapjenispagutopegawai_t as mp
                INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                INNER JOIN remundetailpegawai_t as pg on pg.idpegawai=mp.pegawaifk
                --left JOIN kelompokjabatan_m as kj on kj.id=pg.objectkelompokjabatanfk
                --left JOIN jabatan_m as jb on jb.id=pg.objectjabatanstrukturalfk
                --left JOIN jenispegawai_m as jpg on jpg.id=pg.objectjenispegawaifk
                where jp.id=$jpid
                and jp.kdprofile =$kdProfile
            "
        ));
        $dataDetailJenisPagu = DB::select(DB::raw(
            "   
            select id,detailjenispagu,objectruanganfkarr,jumlahorg 
            from detailjenispagu_t 
            where jenispaguid = $jpid 
             and kdprofile =$kdProfile
            and statusenabled = true
            order by id
            
            "
        ));
        $dataRuangan = DB::select(DB::raw(
            "   
            select id,namaruangan from ruangan_m where 
            statusenabled = true
             and kdprofile =$kdProfile
            "
        ));
        $arrRuanganfk = [];
        $namaruangan = '';
        $dataHasil = [];
        foreach ($dataDetailJenisPagu as $item){
            $arrRuanganfk = [];
            $arrRuanganfk = explode (",", $item->objectruanganfkarr);
            $namaruangan= '';
            foreach ($arrRuanganfk as $tm){
                foreach ($dataRuangan as $itm){
                    if ($tm ==  $itm->id){
                        $namaruangan = $itm->namaruangan . ', ' . $namaruangan;
                        break;
                    }
                }
            }
            $dataHasil[] = array(
                'id' => $item->id,
                'detailjenispagu' => $namaruangan . ' - ' . $item->detailjenispagu . ' - ' . $item->jumlahorg,
//                'namaruangan' => $namaruangan,
            );
        }
        $result = array(
            'data' => $data,
            'detailjenispagu' => $dataHasil,
//            'ruangan' =>$dataRuangan,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getPegawaiByDetailJenisPagu(Request $request)
    {
        $jpid = $request['jpid'];
        $djpid = $request['djpid'];
        $data = DB::select(DB::raw(
            "
                select mp.norec, pg.idpegawai as pgid, pg.namakaryawan as namalengkap,jp.id as jpid,jp.jenispagu 
                --,jb.namajabatan,kj.namakelompokjabatan ,jpg.jenispegawai
                from mapjenispagutopegawai_t as mp
                INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                INNER JOIN remundetailpegawai_t as pg on pg.idpegawai=mp.pegawaifk
                --left JOIN kelompokjabatan_m as kj on kj.id=pg.objectkelompokjabatanfk
                --left JOIN jabatan_m as jb on jb.id=pg.objectjabatanstrukturalfk
                --left JOIN jenispegawai_m as jpg on jpg.id=pg.objectjenispegawaifk
                where jp.id=$jpid and mp.detailjenispagufk = $djpid order by pg.nourutunitbagianinstalasi
            "
        ));
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function hapusMapJenisPagutoPegawai(Request $request) {
        DB::beginTransaction();
        $norec = $request['norec'];
        try {

            $EMR = MapJenisPaguToPegawai::where('norec', $norec)->delete();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Hapus map pegawai ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $norec,
                "as" => 'as@epic',
            );
        }else{
            $transMessage = $transMessage ." Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
//                "data" => $data,
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getHitungJP1(Request $request)
    {

        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];

        $dataJasaPelayanan = DB::select(DB::raw(
            "
                select DISTINCT x.jpid,x.jenispagu,x.dokter as namalengkap,x.dokterid as pgid from
                (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sdp.jenispagunilai as jenispaginilaitotal,sdp.tglpelayanan,
                case when jp.id=1 then sdp.dokterid else null end as dokterid,
                case when jp.id=2 then sdp.paramedisid else null end as paramedisid,
                sdp.norec as norec_sdp,pg.namalengkap as dokter,pg2.namalengkap as paramedis
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                left JOIN pegawai_m as pg on pg.id=sdp.dokterid
                left JOIN pegawai_m as pg2 on pg2.id=sdp.paramedisid
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0 and jp.id=1) as x
                union all
                select DISTINCT x.jpid,x.jenispagu,x.paramedis as namalengkap,x.paramedisid as pgid from
                (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sdp.jenispagunilai as jenispaginilaitotal,sdp.tglpelayanan,
                case when jp.id=1 then sdp.dokterid else null end as dokterid,
                case when jp.id=2 then sdp.paramedisid else null end as paramedisid,
                sdp.norec as norec_sdp,pg.namalengkap as dokter,pg2.namalengkap as paramedis
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                left JOIN pegawai_m as pg on pg.id=sdp.dokterid
                left JOIN pegawai_m as pg2 on pg2.id=sdp.paramedisid
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0 and jp.id=2) as x
            "
        ));

        $dataRemunLangsung = DB::select(DB::raw(
            "
                select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sdp.jenispagunilai as jenispaginilaitotal,sdp.tglpelayanan,
                case when jp.id=1 then sdp.dokterid else null end as dokterid,
                sdp.norec as norec_sdp,pr.namaproduk
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN produk_m as pr on pr.id=pp.produkfk
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.kodeexternal = 'langsung' and sdp.dokterid <> 0 
--                group by jp.kelompokpaguid, jp.id ,jp.jenispagu,sdp.dokterid,sdp.paramedisid,sdp.tglpelayanan;
            "
        ));
        $dtRLangsung = [];
        foreach ($dataRemunLangsung as $itmLangsung ){
            if ($itmLangsung->jpid == 1 && $itmLangsung->dokterid != null){
                $dtRLangsung[] = array(
                    'pegawaiid' => (int)$itmLangsung->dokterid,
                    'jenispaginilaitotal' => (float)$itmLangsung->jenispaginilaitotal,
                    'jpid' => (int)$itmLangsung->jpid,
                    'kelompokpaguid' => (int)$itmLangsung->kelompokpaguid,
                    'tglpelayanan' => $itmLangsung->tglpelayanan,
                    'norec_sdp' => $itmLangsung->norec_sdp,
                    'jenis' => 'remuntindakan',
                    'namaproduk' => $itmLangsung->namaproduk,
                    'jenispagu' => $itmLangsung->jenispagu
                );
            }
            if ($itmLangsung->jpid == 2 && $itmLangsung->paramedisid != null){
                $dtRLangsung[] = array(
                    'pegawaiid' => (int)$itmLangsung->paramedisid,
                    'jenispaginilaitotal' => (float)$itmLangsung->jenispaginilaitotal,
                    'jpid' => (int)$itmLangsung->jpid,
                    'kelompokpaguid' => (int)$itmLangsung->kelompokpaguid,
                    'tglpelayanan' => $itmLangsung->tglpelayanan,
                    'norec_sdp' => $itmLangsung->norec_sdp,
                    'jenis' => 'remuntindakanlangsung',
                    'namaproduk' => $itmLangsung->namaproduk,
                    'jenispagu' => $itmLangsung->jenispagu
                );
            }

        }

        $dataRemunTidakLangsung = DB::select(DB::raw(
            "
                select x.kelompokpaguid,x.jpid,x.jenispagu,x.jenispaginilaitotal,y.jmlpegawai ,
                (x.jenispaginilaitotal / y.jmlpegawai) as jmlRemun,x.tglpelayanan
                from 
                (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,sum(sdp.jenispagunilai) as jenispaginilaitotal,sdp.tglpelayanan
                from  jenispagu_t as jp 
                INNER JOIN strukdetailpagu_t as sdp on sdp.jenispagufk=jp.id
                INNER JOIN strukpagu_t as sp on sp.norec=sdp.strukpagufk
                INNER JOIN pegawai_m as pg on pg.id=sdp.dokterid
                where sp.periodeawal between '$tglAwal' and '$tglAkhir'
                and jp.kodeexternal <> 'langsung' and sdp.dokterid <> 0 
                group by jp.kelompokpaguid, jp.id ,jp.jenispagu,sdp.dokterid,sdp.paramedisid,pg.namalengkap,sdp.tglpelayanan) as x
                INNER JOIN
                (select jp.kelompokpaguid, jp.id as jpid,jp.jenispagu,count(mp.pegawaifk) as jmlpegawai
                from mapjenispagutopegawai_t as mp
                INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                where  jp.kodeexternal <> 'langsung'
                group by jp.kelompokpaguid, jp.id ,jp.jenispagu)as y on x.jpid=y.jpid
            "
        ));
        $dataPegawaiRemunTidakLangsung = DB::select(DB::raw(
            "   
                select pg.id as pgid, pg.namalengkap,jp.id as jpid,jp.jenispagu 
                from mapjenispagutopegawai_t as mp
                INNER JOIN pegawai_m as pg on pg.id=mp.pegawaifk
                INNER JOIN jenispagu_t as jp on jp.id=mp.jenispagufk
                where jp.kodeexternal <> 'langsung';
            "
        ));
        foreach ($dataPegawaiRemunTidakLangsung as $itm){
            foreach ($dataRemunTidakLangsung as $itm2){
                if ($itm->jpid == $itm2->jpid){
                    $dtRLangsung[] = array(
                        'pegawaiid' => (int)$itm->pgid,
                        'jenispaginilaitotal' => (float)$itm2->jmlRemun,
                        'jpid' => (int)$itm->jpid,
                        'kelompokpaguid' => (int)$itm2->kelompokpaguid,
                        'tglpelayanan' => $itm2->tglpelayanan,
                        'norec_sdp' => null,
                        'jenis' => 'remuntindakantidaklangsung',
                        'namaproduk' => null,
                        'jenispagu' => $itm2->jenispagu
                    );
                }
            }
        }
        $dataSave = $dtRLangsung;
        $result = array(
//            'dataRemunLangsung' => $dataRemunLangsung,
//            'dataRemunTidakLangsung' => $dataRemunTidakLangsung,
            'dataPegawaiRemunTidakLangsung' => $dataPegawaiRemunTidakLangsung,
            'pegawaijasapelayanan' => $dataJasaPelayanan,
            'datasave' => $dataSave,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDaftarRemunPegawai(Request $request)
    {
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $namapeg = '';
        $kdProfile = (int) $this->getDataKdProfile($request);
        if ($request['namalengkap'] != ''){
            $namapeg = " and pg.namakaryawan ilike '%" . $request['namalengkap'] . "%'";
        }
        $data = DB::select(DB::raw(
            "
                select  pg.idpegawai as pgid, pg.namakaryawan,sc.noclosing,sc.tglawal,sc.tglakhir,
                pg.jabatan,pg.golongan,pg.skpertamamasukrs,
                sum(dpp.jenispaginilaitotal) as total 
                from detailpegawaipagu_t as dpp
                INNER JOIN remundetailpegawai_t as pg on pg.idpegawai=dpp.pegawaiid
                INNER JOIN strukclosing_t as sc on sc.norec=dpp.strukclosingfk
                 where sc.tglclosing between '$tglAwal' and '$tglAkhir' $namapeg
                 -- and sc.statusenabled = true
                 and sc.statusenabled = true
                 and sc.kdprofile=$kdProfile
                group by pg.idpegawai , pg.namakaryawan,
                pg.jabatan,pg.golongan,pg.skpertamamasukrs,sc.noclosing,sc.tglawal,sc.tglakhir    
                limit 100            
            "
        ));
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function saveDetailPegawaiPagu(Request $request) {
//         : CLOSING REMUN old
        DB::beginTransaction();
        $dataReq = $request->all();
        ini_set('max_execution_time', 500); //6 minutes
        try{
            $dataPegawai = \DB::table('loginuser_s as lu')
                ->select('lu.objectpegawaifk')
                ->where('lu.id',$dataReq['userData']['id'])
                ->first();
            $SCSC = StrukClosing::where('tglawal','>=',$dataReq['head']['periodeawal'])
                ->where('tglakhir','<=',$dataReq['head']['periodeakhir'])
                ->select('norec')
                ->update([
                    'statusenabled' => 0,
                ]);
//            if (!empty($SCSC)){
//                $delSCSC = StrukClosing::where('norec',$SCSC->norec)
//                    ->delete();
//                $delDetail = DetailPegawaiPagu::where('strukclosingfk',$SCSC->norec)
//                    ->delete();
//            }


            $nostrukClosing = $this->generateCode(new StrukClosing(), 'noclosing', 10, 'RC/' . $this->getDateTime()->format('ym'));

            $dataSC = new StrukClosing();
            $dataSC->norec = $dataSC->generateNewId();
            $dataSC->kdprofile = 0;
            $dataSC->statusenabled = true;
            $dataSC->noclosing = $nostrukClosing;
            $dataSC->tglclosing = date('Y-m-d H:i:s');
            $dataSC->tglawal = $dataReq['head']['periodeawal'];
            $dataSC->tglakhir = $dataReq['head']['periodeakhir'];
            $dataSC->objectpegawaidiclosefk = $dataPegawai->objectpegawaifk;
            $dataSC->objectkelompoktransaksifk = 118;
            $dataSC->save();

            $norecSC = $dataSC->norec;

            foreach ($dataReq['data'] as $item) {
                $dataSPD = new DetailPegawaiPagu();
                $dataSPD->norec = $dataSPD->generateNewId();
                $dataSPD->kdprofile = 0;
                $dataSPD->statusenabled = true;
                $dataSPD->strukclosingfk = $norecSC;
                $dataSPD->jenis = $item['jenis'];
                $dataSPD->jenispaginilaitotal = $item['jenispaginilaitotal'];
                $dataSPD->jpid = $item['jpid'];
//                $dataSPD->kelompokpaguid = $item['kelompokpaguid'];
                $dataSPD->norec_sdp = $item['norec_sdp'];
                $dataSPD->pegawaiid = $item['pegawaiid'];
                $dataSPD->tglpelayanan = $item['tglpelayanan'];
                $dataSPD->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = "Closing Pagu Remunerasi";

        if ($transStatus == 'true') {
            $transMessage = $transMessage . " Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "by" => 'as@epic',
            );
        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "by" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function getDataClosing(Request $request)
    {
        $dataLogin = $request->all();
        $idpegawai = $dataLogin['userData']['id'];
        $data = DB::select(DB::raw(
            "

                select pg.id, pg.namalengkap from loginuser_s as lu
                INNER JOIN pegawai_m as pg on pg.id=lu.objectpegawaifk
                where lu.id=$idpegawai;

            "
        ));
        $data2 = DB::select(DB::raw(
            "   
                select
                noclosing + ', ' + to_char(tglawal,'yyyy-MM-dd') + ' s/d ' + to_char(tglakhir ,'yyyy-MM-dd') as namaclosing  
                -- noclosing || ', ' || to_char(tglawal,'yyyy-MM-dd') || ' s/d ' || to_char(tglakhir ,'yyyy-MM-dd') as namaclosing,
                noclosing,tglawal,tglakhir,norec
                from strukclosing_t where objectkelompoktransaksifk=118 and statusenabled=true

            "
        ));
        $result = array(
            'data' => $data,
            'data2' => $data2,
            'datalogin' => $dataLogin,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDetailRemunRCCC(Request $request)
    {
        //TODO : DETAIL PELAYANAN REMUN
        $jenispaguid = $request['jenispaguid'];
        $noclosing = $request['noclosing'];
        $pegawaiid = $request['pegawaiid'];
        $detailjenispaguid = $request['detailjenispaguid'];
        $namapasien = '';
        if ($request['namapasien'] != ''){
            $namapasien ="and ps.namapasien ilike '%" .  $request['namapasien'] . "%'";
        }
        if ($jenispaguid == 8){
            $data2 = DB::select(DB::raw(
                "
                select djp.*,ru.namaruangan,case when pr.potpersen is null then 0 else pr.potpersen end as potpersen  from mapjenispagutopegawai_t as mp 
                INNER JOIN detailjenispagu_t as djp   on djp.id=mp.detailjenispagufk
                INNER JOIN ruangan_m as ru on ru.id=djp.ruanganfk
                LEFT JOIN potonganremun_t as pr on pr.objectpegawaifk=mp.pegawaifk and pr.objectjenispagufk=djp.jenispaguid
                where mp.pegawaifk=$pegawaiid and djp.jenispaguid=$jenispaguid
                
            "
            ));
            $ruanganfk = 'and sdp.ruanganfk=' . $data2[0]->ruanganfk;
            $ruanganfk2 = 'and sdp.ruanganfk=' . $data2[0]->ruanganfk;
            $jenispaguidid =  'sdp.jenispagufk in (8)';
            $jenispaguidid2 =  'sdp.jenispaguid in (8)';
            $data = DB::select(DB::raw(
                "
                select  sum(sdp.jenispagunilai) as jenispagunilai,sdp.produkfk,pp.hargasatuan ,pr.namaproduk,
                sum(pp.jumlah) as jumlah, pp.isparamedis,pp.iscito,ru.namaruangan
                FROM strukclosing_t AS sp
                INNER JOIN  strukdetailpagu_t AS sdp ON sdp.tglpelayanan between sp.tglawal and sp.tglakhir
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN produk_m as pr on pr.id=pp.produkfk
                INNER JOIN ruangan_m as ru on ru.id= sdp.ruanganfk
                where sp.noclosing='$noclosing'  $ruanganfk and sdp.jenispagunilai>0
                group by sdp.produkfk,pp.hargasatuan ,pr.namaproduk, pp.isparamedis,pp.iscito,ru.namaruangan
                
            "
            ));
            $data3 = DB::select(DB::raw(
                "
                
                select sum(sdp.point) as pointtotal,count( rdp.idpegawai) as jml
                from remundetailpegawai_t as rdp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                INNER JOIN detailjenispagu_t as sdp on sdp.id=mp.detailjenispagufk
                INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispaguid
                where  $jenispaguidid2 $ruanganfk2
                --and mp.pegawaifk=$pegawaiid
                --and mp.detailjenispagufk=$detailjenispaguid
                
            "
            ));
        }
        if ($jenispaguid == 10){
            $data2 = DB::select(DB::raw(
                "
                select djp.*,'' as namaruangan  from mapjenispagutopegawai_t as mp 
                INNER JOIN detailjenispagu_t as djp   on djp.id=mp.detailjenispagufk
                where mp.pegawaifk=$pegawaiid  and mp.detailjenispagufk=$detailjenispaguid
                
            "
            ));

            $ruanganfk = '';
            $ruanganfk2 = '';
            $jenispaguidid =  'sdp.jenispagufk in (10,12)';
            $jenispaguidid2 =  'sdp.jenispaguid in (10,12)';
            $data = DB::select(DB::raw(
                "
                select  sum(sdp.jenispagunilai) as jenispagunilai,sdp.produkfk,pp.hargasatuan ,pr.namaproduk,
                sum(pp.jumlah) as jumlah, pp.isparamedis,pp.iscito,ru.namaruangan
                FROM strukclosing_t AS sp
                INNER JOIN  strukdetailpagu_t AS sdp ON sdp.tglpelayanan between sp.tglawal and sp.tglakhir
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN produk_m as pr on pr.id=pp.produkfk
                INNER JOIN ruangan_m as ru on ru.id= sdp.ruanganfk
                where sp.noclosing='$noclosing'  $ruanganfk and sdp.jenispagunilai>0
                group by sdp.produkfk,pp.hargasatuan ,pr.namaproduk, pp.isparamedis,pp.iscito,ru.namaruangan
                
            "
            ));
            $data3 = DB::select(DB::raw(
                "
                
                select sum(sdp.point) as pointtotal,count( rdp.idpegawai) as jml
                from remundetailpegawai_t as rdp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                INNER JOIN detailjenispagu_t as sdp on sdp.id=mp.detailjenispagufk
                INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispaguid
                where  $jenispaguidid2 $ruanganfk2
                --and mp.pegawaifk=$pegawaiid
                --and mp.detailjenispagufk=$detailjenispaguid
                
            "
            ));
        }
        if ($jenispaguid == 11){
            $data2 = DB::select(DB::raw(
                "
                select djp.*,'' as namaruangan  from mapjenispagutopegawai_t as mp 
                INNER JOIN detailjenispagu_t as djp   on djp.id=mp.detailjenispagufk
                where mp.pegawaifk=$pegawaiid  and mp.detailjenispagufk=$detailjenispaguid
                
            "
            ));

            $ruanganfk = '';
            $ruanganfk2 = '';
            $jenispaguidid =  'sdp.jenispagufk in (11)';
            $jenispaguidid2 =  'sdp.jenispaguid in (11)';
            $data = DB::select(DB::raw(
                "
                select  sum(sdp.jenispagunilai) as jenispagunilai,sdp.produkfk,pp.hargasatuan ,pr.namaproduk,
                sum(pp.jumlah) as jumlah, pp.isparamedis,pp.iscito,ru.namaruangan
                FROM strukclosing_t AS sp
                INNER JOIN  strukdetailpagu_t AS sdp ON sdp.tglpelayanan between sp.tglawal and sp.tglakhir
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN produk_m as pr on pr.id=pp.produkfk
                INNER JOIN ruangan_m as ru on ru.id= sdp.ruanganfk
                where sp.noclosing='$noclosing'  $ruanganfk and sdp.jenispagunilai>0
                group by sdp.produkfk,pp.hargasatuan ,pr.namaproduk, pp.isparamedis,pp.iscito,ru.namaruangan
                
            "
            ));
            $data3 = DB::select(DB::raw(
                "
                
                select sum(sdp.point) as pointtotal,count( rdp.idpegawai) as jml
                from remundetailpegawai_t as rdp
                INNER JOIN mapjenispagutopegawai_t as mp on mp.pegawaifk=rdp.idpegawai
                INNER JOIN detailjenispagu_t as sdp on sdp.id=mp.detailjenispagufk
                INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispaguid
                where  $jenispaguidid2 $ruanganfk2
                --and mp.pegawaifk=$pegawaiid
                --and mp.detailjenispagufk=$detailjenispaguid
                
              "
            ));
        }






        $result = array(
            'data' => $data,
            'data2' => $data2,
            'data3' => $data3,
            'message' => 'ea@epc',
        );
        return $this->respond($result);
    }
    public function getKomponenHargaPelayanan(Request $request)
    {
        $data4 = \DB::table('pelayananpasien_t as pp')
            ->join('pelayananpasiendetail_t as ppd', 'pp.norec', '=', 'ppd.pelayananpasien')
            ->join('komponenharga_m as kh', 'kh.id', '=', 'ppd.komponenhargafk')
            ->select('ppd.pelayananpasien as norec_pp', 'ppd.norec', 'kh.komponenharga', 'ppd.jumlah',
                'ppd.hargasatuan','ppd.hargadiscount','ppd.jasa')
            ->where('pp.norec', $request['norec_pp'])
            ->get();

        $result = array(
            'data'=> $data4,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function GetDetailRemunPegawai (Request $request)
    {
        //TODO : daftar jenis remun
//        $tgl = $request['tglAwal'];
//        $data = \DB::table('strukclosing_t as sp')
//            ->join ('detailpegawaipagu_t as dpp','dpp.strukclosingfk','=','sp.norec')
//            ->leftJoin ('strukdetailpagu_t as sdp','sdp.norec','=','dpp.norec_sdp')
//            ->leftJoin ('pelayananpasien_t as pp','pp.norec','=','sdp.pelayananpasienfk')
//            ->leftJoin ('antrianpasiendiperiksa_t as apd','apd.norec','=','pp.noregistrasifk')
//            ->leftJoin ('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
//            ->leftJoin ('produk_m as pro','pro.id','=','sdp.produkfk')
//            ->leftJoin ('pasien_m as pm','pm.id','=','pd.nocmfk')
//            ->leftJoin ('pegawai_m as pg','pg.id','=','sdp.dokterid')
//            ->leftJoin ('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
//            ->leftJoin ('departemen_m as dept','dept.id','=','ru.objectdepartemenfk')
//            ->leftJoin ('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
//            ->leftJoin ('detailjenispagu_t as djp','djp.id','=','dpp.djpid')
//            ->select(DB::raw("case when pp.tglpelayanan is null then sp.tglclosing else pp.tglpelayanan end as tglpelayanan,
//                            case when pm.nocm is null then '-' else pm.nocm end as nocm,
//                            case when pd.noregistrasi is null then '-' else pd.noregistrasi end as noregistrasi,
//                            case when pm.namapasien is null then '-' else pm.namapasien end as namapasien,
//                            sdp.produkfk,pp.jumlah,
//                            case when pro.namaproduk is null then dpp.jenis else pro.namaproduk end as namaproduk,
//                            case when pp.hargasatuan is null then 0 else pp.hargasatuan * pp.jumlah end as hargasatuan,
//                             apd.objectruanganfk,ru.namaruangan,ru.objectdepartemenfk,dept.namadepartemen,
//                             dpp.jenispaginilaitotal as jenispagunilai,sdp.dokterid,pg.namalengkap as dokter,
//                             br.pasiendaftarfk as norec_batal,pp.isparamedis,pp.iscito,dpp.jpid,pp.norec as norec_pp,
//                             dpp.djpid,djp.detailjenispagu"));

//        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
//            $data = $data->where('sp.tglclosing', '>=', $request['tglAwal']);
//        }
//        if (isset($request['tglAkhir']) && $request['tglAkhir']  != "" && $request['tglAkhir'] != "undefined") {
//            $tgl = $request['tglAkhir'];//." 23:59:59";
//            $data = $data->where('sp.tglclosing', '<=', $tgl);
//        }
//        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
//            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
//        }
//        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
//            $data = $data->where('apd.objectruanganfk', '=', $request['idRuangan']);
//        }
//        if (isset($request['noclosing']) && $request['noclosing'] != "" && $request['noclosing'] != "undefined") {
//            $data = $data->where('sp.noclosing', '=', $request['noclosing']);
//        }
//        if (isset($request['IdDokter']) && $request['IdDokter'] != "" && $request['IdDokter'] != "undefined") {
//            $data = $data->where('dpp.pegawaiid', '=', $request['IdDokter']);
//        }
        $noclosing = $request['noclosing'];
        $dokterid=$request['IdDokter'];
        $ruanganfk =DB::select(DB::raw("select djp.ruanganfk from mapjenispagutopegawai_t as mp
                    INNER JOIN detailjenispagu_t as djp on djp.id=mp.detailjenispagufk
                    where mp.pegawaifk=$dokterid and mp.jenispagufk=8;"));
        $RC = "";
        $RCEEG= "";
        for ($i=0; $i < count($ruanganfk); $i++) {
            $ruu = $ruanganfk[$i]->ruanganfk;
            $RC = $RC . " union all select  pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,pr.namaproduk,
                pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,sdp.jenispagunilai,pp.norec as norec_pp,sdp.jenispagufk as jpid,
                ru.namaruangan
                from strukclosing_t as sc
                INNER JOIN strukdetailpagu_t as sdp on sdp.tglpelayanan BETWEEN sc.tglawal and sc.tglakhir
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk 
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN produk_m as pr on pr.id=sdp.produkfk
                INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                where sc.noclosing='$noclosing' and sdp.ruanganfk=$ruu and sdp.jenispagufk=8 ";
            $RCEEG = $RCEEG . " union all select pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,pr.namaproduk,
                pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,sdp.jenispagunilai,pp.norec as norec_pp,sdp.jenispagufk as jpid,
                ru.namaruangan
                from strukclosing_t as sc
                INNER JOIN detailpegawaipagu_t as ddp on ddp.strukclosingfk=sc.norec
                INNER JOIN strukdetailpagu_t as sdp on sdp.norec=ddp.norec_sdp
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk 
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN produk_m as pr on pr.id=sdp.produkfk
                INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                where sc.noclosing='$noclosing' and ddp.namaexternal='eeg' and pegawaiid=$dokterid ";
        }
        // $RC = "";
        // $RCEEG= "";
        // if  (count($ruanganfk) != 0){
        //     $ruu = $ruanganfk[0]->ruanganfk;
        //     $RC = " union all select  pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,pr.namaproduk,
        //         pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,sdp.jenispagunilai,pp.norec as norec_pp,sdp.jenispagufk as jpid,
        //         ru.namaruangan
        //         from strukclosing_t as sc
        //         INNER JOIN strukdetailpagu_t as sdp on sdp.tglpelayanan BETWEEN sc.tglawal and sc.tglakhir
        //         INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
        //         INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        //         INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        //         INNER JOIN produk_m as pr on pr.id=sdp.produkfk
        //         INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
        //         INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        //         where sc.noclosing='$noclosing' and sdp.ruanganfk=$ruu and sdp.jenispagufk=8 ";
        //     $RCEEG = " union all select pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,pr.namaproduk,
        //         pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,sdp.jenispagunilai,pp.norec as norec_pp,sdp.jenispagufk as jpid,
        //         ru.namaruangan
        //         from strukclosing_t as sc
        //         INNER JOIN detailpegawaipagu_t as ddp on ddp.strukclosingfk=sc.norec
        //         INNER JOIN strukdetailpagu_t as sdp on sdp.norec=ddp.norec_sdp
        //         INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
        //         INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
        //         INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
        //         INNER JOIN produk_m as pr on pr.id=sdp.produkfk
        //         INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
        //         INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
        //         where sc.noclosing='$noclosing' and ddp.namaexternal='eeg' and pegawaiid=$dokterid ";
        // }

        $REMUN = "select  sc.tglclosing as tglpelayanan,'-' as nocm,'-' as noregistrasi,'-' as namapasien,dpp.jenis as  namaproduk,
                0 as jumlah,0 as isparamedis,0 as iscito,0 as hargasatuan,dpp.jenispaginilaitotal as jenispagunilai,'' as norec_pp,dpp.jpid as jpid,
                '' as namaruangan
                from strukclosing_t as sc
                INNER JOIN detailpegawaipagu_t as dpp on dpp.strukclosingfk=sc.norec
                where sc.noclosing='$noclosing' and dpp.pegawaiid = $dokterid and dpp.jpid in (9,10,11,12) and dpp.statusenabled = 1";

        $RCDOKTER = "select  pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,pr.namaproduk,
                pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,sdp.jenispagunilai,pp.norec as norec_pp,sdp.jenispagufk as jpid,
                ru.namaruangan
                from strukclosing_t as sc
                INNER JOIN strukdetailpagu_t as sdp on sdp.tglpelayanan BETWEEN sc.tglawal and sc.tglakhir
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk 
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN produk_m as pr on pr.id=sdp.produkfk
                INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                where sc.noclosing='$noclosing' and sdp.dokterid= $dokterid and sdp.jenispagufk=7";

        $data = DB::select(DB::raw(
            $REMUN . "  " . $RC  . " union all " . $RCDOKTER  . " " . $RCEEG
        ));

//        $data = $data->whereNull('br.pasiendaftarfk');
//        $data = $data->where('sp.statusenabled','=',1);
////        $data = $data->where('pd.noregistrasi','=','1905009532');//
//        $data = $data->orderBy('pp.tglpelayanan','Asc');
//        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'ea@epc',
        );
        return $this->respond($result);
    }
    public function getDaftarPerhitunganIndexPegawai(Request $request)
    {
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $namapeg = '';
        if ($request['namalengkap'] != ''){
            $namapeg = " and namakaryawan ilike '%" . $request['namalengkap'] . "%' ";
        }
        $data = DB::select(DB::raw(
            "
                select  * from remundetailpegawai_t where kdprofile=$kdProfile  $namapeg order by unitbagianinstalasi,no limit 100 
            "
        ));
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }
    public function getDataComboLaporanRemun(Request $request)
    {
        $dataLogin = $request->all();
        $dataPegawaiLogin = \DB::table('loginuser_s as lu')
            ->join('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select('lu.objectpegawaifk','pg.namalengkap')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id',[3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35])
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();
        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $dataDokter = \DB::table('pegawai_m as ru')
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


        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'user' => $dataPegawaiLogin,
            'message' => 'as@',
        );

        return $this->respond($result);
    }
    public function getDataDetailLaporanRemunerasi(Request $request) {
        $data = [];
        $data2 = [];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $deptId = $request['idDept'];
        $ruanganId = $request['idRuangan'];
        $isEksekutif = $request['isExsekutif'];

        $paramDep = ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' . $deptId;
        }

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramEksekutif = ' ';
        if (isset($isEksekutif) && $isEksekutif != "" && $isEksekutif != "undefined") {
            if ($isEksekutif == "true"){
                $paramEksekutif = ' and ru.iseksekutif = true';
            }else if($isEksekutif == "false"){
                $paramEksekutif = ' and ru.iseksekutif = false';
            }
        }

//        $data = DB::select(DB::raw("select x.namaruangan, sum(x.JasaDr) as JasaDr,sum(x.Paramedis) as Paramedis,sum(x.PostRemun) as PostRemun,
//                            sum(x.Direksi) as Direksi,sum(x.StaffDireksi) as StaffDireksi,sum(x.Manajemen) as Manajemen from
//                            (select ru.namaruangan,jp.id as jpid,jp.jenispagu,
//                            case when jp.id = 7 then sum(sdp.jenispagunilai) else 0 end as 'JasaDr',
//                            case when jp.id = 8 then sum(sdp.jenispagunilai) else 0 end as 'Paramedis',
//                            case when jp.id = 9 then sum(sdp.jenispagunilai) else 0 end as 'PostRemun',
//                            case when jp.id = 10 then sum(sdp.jenispagunilai) else 0 end as 'Direksi',
//                            case when jp.id = 11 then sum(sdp.jenispagunilai) else 0 end as 'StaffDireksi',
//                            case when jp.id = 12 then sum(sdp.jenispagunilai) else 0 end as 'Manajemen'
//                            from strukdetailpagu_t as sdp
//                            INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
//                            INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispagufk
//                            where tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'
//                            $paramDep
//                            $paramRuangan
//                            $paramEksekutif
//                            group by ru.namaruangan,jp.jenispagu,jp.id)as x
//                            group by x.namaruangan;"));

        $data = DB::select(DB::raw("select x.namaruangan, sum(x.JasaDr) as jasadr,sum(x.Paramedis) as paramedis,sum(x.PostRemun) as postremun,
                            sum(x.Direksi) as direksi,sum(x.StaffDireksi) as staffdireksi,sum(x.Manajemen) as manajemen from
                           (select ru.namaruangan,
                            case when sdp.jenispagufk  = 7 then sum(sdp.jenispagunilai) else 0 end as JasaDr,
                            case when sdp.jenispagufk = 8 then sum(sdp.jenispagunilai) else 0 end as Paramedis,
                            case when sdp.jenispagufk  = 9 then sum(sdp.jenispagunilai) else 0 end as PostRemun,
                            case when sdp.jenispagufk  = 10 then sum(sdp.jenispagunilai) else 0 end as Direksi,
                            case when sdp.jenispagufk  = 11 then sum(sdp.jenispagunilai) else 0 end as StaffDireksi,
                            case when sdp.jenispagufk  = 12 then sum(sdp.jenispagunilai) else 0 end as Manajemen
                            from strukdetailpagu_t as sdp
                            INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
                            inner join strukpagu_t as sp on sp.norec =sdp.strukpagufk
                            --  INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispagufk 
                            where sp.periodeawal BETWEEN '$tglAwal' and '$tglAkhir'
                            and sp.kdprofile=$kdProfile
                            and sdp.tglpelayanan  > '2019-05-31 23:59'
                            $paramDep
                            $paramRuangan
                            $paramEksekutif
                            group by ru.namaruangan,sdp.jenispagufk )as x
                            group by x.namaruangan"));

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataRekapLaporanRemunerasi(Request $request) {
        $data = [];
        $data2 = [];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $deptId = $request['idDept'];
        $ruanganId = $request['idRuangan'];
        $dokterId = $request['IdDokter'];
        $isEksekutif = $request['isExsekutif'];

        $paramDep = ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' . $deptId;
        }

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramDokter = ' ';
        if (isset($dokterId) && $dokterId != "" && $dokterId != "undefined") {
            $paramDokter = ' and pg.id = '.$dokterId;
        }

        $paramEksekutif = ' ';
        if (isset($isEksekutif) && $isEksekutif != "" && $isEksekutif != "undefined") {
            if ($isEksekutif == "true"){
                $paramEksekutif = ' and ru.iseksekutif = true';
            }else if($isEksekutif == "false"){
                $paramEksekutif = ' and ru.iseksekutif = false';
            }
        }

//        $data = DB::select(DB::raw("select x.namalengkap, sum(x.JasaDr) as JasaDr,sum(x.Paramedis) as Paramedis,sum(x.PostRemun) as PostRemun,
//                            sum(x.Direksi) as Direksi,sum(x.StaffDireksi) as StaffDireksi,sum(x.Manajemen) as Manajemen from
//                            (select pg.namalengkap,jp.id as jpid,jp.jenispagu,
//                            case when jp.id = 7 then sum(sdp.jenispagunilai) else 0 end as 'JasaDr',
//                            case when jp.id = 8 then sum(sdp.jenispagunilai) else 0 end as 'Paramedis',
//                            case when jp.id = 9 then sum(sdp.jenispagunilai) else 0 end as 'PostRemun',
//                            case when jp.id = 10 then sum(sdp.jenispagunilai) else 0 end as 'Direksi',
//                            case when jp.id = 11 then sum(sdp.jenispagunilai) else 0 end as 'StaffDireksi',
//                            case when jp.id = 12 then sum(sdp.jenispagunilai) else 0 end as 'Manajemen'
//                            from strukdetailpagu_t as sdp
//                            INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
//                            INNER JOIN jenispagu_t as jp on jp.id=sdp.jenispagufk
//                            INNER JOIN pegawai_m as pg on pg.id=sdp.dokterid
//                            where tglpelayanan BETWEEN '$tglAwal' and '$tglAkhir'
//                            $paramDokter
//                            $paramEksekutif
//                            group by pg.namalengkap,jp.jenispagu,jp.id)as x
//                            group by x.namalengkap;"));


        $data = DB::select(DB::raw("select x.namalengkap, sum(x.JasaDr) as jasadr,sum(x.Paramedis) as paramedis,sum(x.PostRemun) as postremun,
                        sum(x.Direksi) as direksi,sum(x.StaffDireksi) as staffdireksi,sum(x.Manajemen) as manajemen from
                        (select pg.namalengkap,
                        case when sdp.jenispagufk= 7 then sum(sdp.jenispagunilai) else 0 end as JasaDr,
                        case when sdp.jenispagufk = 8 then sum(sdp.jenispagunilai) else 0 end as Paramedis,
                        case when sdp.jenispagufk = 9 then sum(sdp.jenispagunilai) else 0 end as PostRemun,
                        case when sdp.jenispagufk = 10 then sum(sdp.jenispagunilai) else 0 end as Direksi,
                        case when sdp.jenispagufk= 11 then sum(sdp.jenispagunilai) else 0 end as StaffDireksi,
                        case when sdp.jenispagufk = 12 then sum(sdp.jenispagunilai) else 0 end as Manajemen
                        from strukdetailpagu_t as sdp
                        INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
                        inner join strukpagu_t as sp on sp.norec =sdp.strukpagufk
                        INNER JOIN pegawai_m as pg on pg.id=sdp.dokterid 
                        where sp.periodeawal BETWEEN '$tglAwal' and '$tglAkhir'
                        and sp.kdprofile=$kdProfile 
                         and sdp.tglpelayanan  > '2019-05-31 23:59'
                        $paramDokter
                        $paramEksekutif
                        group by pg.namalengkap,sdp.jenispagufk)as x
                        group by x.namalengkap;"));
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataDetailLaporanRemunerasiDokter(Request $request) {
        $data = [];
        $data2 = [];
          $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $dokterid = $request['dokterid'];
        $isEksekutif = $request['isExsekutif'];
        $deptId = $request['idDept'];
        $ruanganId = $request['idRuangan'];

        $paramDep = ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' . $deptId;
        }

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramDokter = ' ';
        if (isset($dokterid) && $dokterid != "" && $dokterid != "undefined") {
            $paramDokter = ' and sdp.dokterid = '.$dokterid;
        }

        $paramEksekutif = ' ';
        if (isset($isEksekutif) && $isEksekutif != "" && $isEksekutif != "undefined") {
            if ($isEksekutif == "true"){
                $paramEksekutif = ' and ru.iseksekutif = true';
            }else if($isEksekutif == "false"){
                $paramEksekutif = ' and ru.iseksekutif = false';
            }
        }

        $data = DB::select(DB::raw("select 
                     x.tglpelayanan,x.nocm,x.noregistrasi,x.namapasien,x.namaruangan,
                     x.namaproduk,x.isparamedis,x.iscito,x.hargasatuan,
                     --sum
                     (x.jumlah) as qty,sum(x.jenispagunilai) as total
                from
                (select pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,ru.namaruangan,
                        pr.namaproduk,pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,
                        sdp.jenispagunilai
                from strukdetailpagu_t as sdp 
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN produk_m as pr on sdp.produkfk=pr.id
                INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
              -- where pp.tglpelayanan between '$tglAwal' and '$tglAkhir'
                 where pd.tglpulang between '$tglAwal' and '$tglAkhir'
                   and pp.tglpelayanan  > '2019-05-31 23:59'
                   and pp.kdprofile=$kdProfile
                $paramDep 
                $paramRuangan
                $paramDokter
                $paramEksekutif) as x
                group by  x.tglpelayanan,x.nocm,x.noregistrasi,x.namapasien,x.namaruangan,
                          x.namaproduk,x.isparamedis,x.iscito,x.hargasatuan,
                          x.jumlah
                "));
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function getDataDetailLaporanRemunerasiParamedis(Request $request) {
        $data = [];
        $data2 = [];
        $kdProfile = (int) $this->getDataKdProfile($request);
        $tglAwal = $request['tglAwal'];
        $tglAkhir = $request['tglAkhir'];
        $dokterid = $request['dokterid'];
        $isEksekutif = $request['isExsekutif'];
        $deptId = $request['idDept'];
        $ruanganId = $request['idRuangan'];

        $paramDep = ' ';
        if (isset($deptId) && $deptId != "" && $deptId != "undefined") {
            $paramDep = ' and ru.objectdepartemenfk = ' . $deptId;
        }

        $paramRuangan = ' ';
        if (isset($ruanganId) && $ruanganId != "" && $ruanganId != "undefined") {
            $paramRuangan = ' and ru.id = ' . $ruanganId;
        }

        $paramDokter = ' ';
        if (isset($dokterid) && $dokterid != "" && $dokterid != "undefined") {
            $paramDokter = ' and sdp.dokterid = '.$dokterid;
        }

        $paramEksekutif = ' ';
        if (isset($isEksekutif) && $isEksekutif != "" && $isEksekutif != "undefined") {
            if ($isEksekutif == "true"){
                $paramEksekutif = ' and ru.iseksekutif = true';
            }else if($isEksekutif == "false"){
                $paramEksekutif = ' and ru.iseksekutif = false';
            }
        }

        $data = DB::select(DB::raw("select 
                     x.tglpelayanan,x.nocm,x.noregistrasi,x.namapasien,x.namaruangan,
                     x.namaproduk,x.isparamedis,x.iscito,x.hargasatuan,
                     sum(x.jumlah) as qty,sum(x.jenispagunilai) as total
                from
                (select pp.tglpelayanan,ps.nocm,pd.noregistrasi,ps.namapasien,ru.namaruangan,
                        pr.namaproduk,pp.jumlah,pp.isparamedis,pp.iscito,pp.hargasatuan,
                        sdp.jenispagunilai
                from strukdetailpagu_t as sdp 
                INNER JOIN pelayananpasien_t as pp on pp.norec=sdp.pelayananpasienfk
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                INNER JOIN pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                INNER JOIN produk_m as pr on sdp.produkfk=pr.id
                INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                INNER JOIN ruangan_m as ru on ru.id=sdp.ruanganfk
                --where pp.tglpelayanan between '$tglAwal' and '$tglAkhir' 
                  where pd.tglpulang between '$tglAwal' and '$tglAkhir' 
                       and pp.tglpelayanan  > '2019-05-31 23:59'
                and sdp.jenispagufk = 8
                and pp.kdprofile=$kdProfile
                $paramDep 
                $paramRuangan
                $paramDokter
                $paramEksekutif) as x
                group by  x.tglpelayanan,x.nocm,x.noregistrasi,x.namapasien,x.namaruangan,
                          x.namaproduk,x.isparamedis,x.iscito,x.hargasatuan
                "));
        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function saveRemunerasiJP1_rev3_2020(Request $request)
    {
        //TODO : CARI PAGU
//        ini_set('memory_limit', '256M');
//        ini_set('max_execution_time', 500); //6 minutes

        $kdProfile = (int) $this->getDataKdProfile($request);
        $dataReq =$request->all();
        $tglAwal = $dataReq['head']['periodeawal'];
        $tglAkhir = $dataReq['head']['periodeakhir'];

//        $kdJenisPetugas = $dataReq['head']['kdjenispetugas'];
//        $kdKomponen = $dataReq['head']['kdkomponen'];
        $nama = '';//$dataReq['nama'];
        $produk = '';//$dataReq['produk'];
        $dtdtdt2 =[];
        $dataPersen3 = [];

        $SCSC = StrukPagu::where('periodeawal',$request['tglAwal'])->where('kdprofile',$kdProfile)->get();
        $StrukPagu = false;
        if (count($SCSC)>0){
            $StrukPagu = true;
        }
        $dataDokterAnak = [];
//        $dataDokterAnak = DB::select(DB::raw(
//            "
//              select pp.tglpelayanan,ppp.objectpegawaifk as dokterpjid ,
//                pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,pp.isparamedis,pgpj.namalengkap as dokterpj
//                from  pelayananpasien_t as pp
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                where pd.tglpulang between '$tglAwal' and '$tglAkhir'  and pgpj.namalengkap like '%$nama%'
//                and ppp.objectjenispetugaspefk=15 and pp.isparamedis is null
//
//          "
//        ));
        $dataDokterAnestesi = [];
//        $dataDokterAnestesi = DB::select(DB::raw(
//            "
//              select pp.tglpelayanan,ppp.objectpegawaifk as dokterpjid ,
//                pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ppp.objectpegawaifk,pp.isparamedis,pgpj.namalengkap as dokterpj
//                from  pelayananpasien_t as pp
//                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=pp.norec
//                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
//                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
//                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
//                where pd.tglpulang between '$tglAwal' and '$tglAkhir'  and pgpj.namalengkap like '%$nama%'
//                and ppp.objectjenispetugaspefk=6 and pp.isparamedis is null
//
//
//          "
//        ));
        $data2 = DB::select(DB::raw(
            "  
  
                select  x.*,
                (x.jaspel*7.5)/100 as direksi,
                (x.jaspel*5)/100 as struktural,
                (x.jaspel*1)/100 as administrasi,
                case when x.objectruanganfk in (535,577,580,576) then  (x.jaspel*30)/100
                        when x.objectruanganfk in (575) then  (x.jaspel*35)/100
                        when x.objectruanganfk in (125,94,116,59) then  (x.jaspel*28)/100
                        when x.objectruanganfk in (571,579) then  (x.jaspel*40)/100
                else (x.jaspel*45)/100 end as jpl,
                (x.jaspel*8.5)/100 as jptl,
                case when x.objectruanganfk in (535,577,580,576) then  (x.jaspel*48)/100
                        when x.objectruanganfk in (575) then  (x.jaspel*43)/100
                        when x.objectruanganfk in (125,94,116,59) then  (x.jaspel*50)/100
                        when x.objectruanganfk in (571,579) then  (x.jaspel*38)/100
                else (x.jaspel*33)/100  end as gabungan
                from 
                (select pp.tglpelayanan,pr.namaproduk,                
                sum((((ppd.hargajual-(case when ppd.hargadiscount is null then 0 else ppd.hargadiscount end)+(case when ppd.jasa is null then 0 else ppd.jasa end))*pp.jumlah)) ) as jaspel,
                pgpj.namalengkap as dokter ,pgpj.id as dokterid ,
                apd.norec as norec_apd,pp.jumlah,pp.norec  as norec_pp,pp.produkfk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis
                from pelayananpasiendetail_t as ppd
                inner join pelayananpasien_t as pp on pp.norec=ppd.pelayananpasien
                inner join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien=ppd.pelayananpasien
                left join antrianpasiendiperiksa_t as apd on apd.norec=pp.noregistrasifk
                inner join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
                left join pegawai_m as pgpj on pgpj.id=ppp.objectpegawaifk
                left join produk_m as pr on pr.id =ppd.produkfk
                left join ruangan_m as ru on ru.id=apd.objectruanganfk
                where pd.tglpulang between '$tglAwal' and '$tglAkhir'
                --and  ppp.objectjenispetugaspefk=4 and ppd.komponenhargafk in (86,87,90,25,88,35,93)
                and  ppp.objectjenispetugaspefk=4 and ppd.komponenhargafk in (94)
                and ppd.hargajual >0
                and pp.kdprofile=$kdProfile
                group by pp.tglpelayanan,pr.namaproduk,
                pgpj.namalengkap ,pgpj.id  ,
                apd.norec ,pp.jumlah,pp.norec ,pp.produkfk,ppp.objectpegawaifk,ru.objectdepartemenfk,apd.objectruanganfk,pp.isparamedis) AS x
                

            "
        ));
        $newData2 = [];
        $sama = false;
        $iddokter = '';
        $nmdokter = '';
        foreach ($data2 as $dt){
            $newData2[] = array(
                'tglpelayanan' => $dt->tglpelayanan,
                'namaproduk' => $dt->namaproduk,

                'direksi' => (float)$dt->direksi,
                'struktural' => $dt->struktural,
                'administrasi' => $dt->administrasi,
                'jpl' => $dt->jpl,
                'jptl' => $dt->jptl,
                'gabungan' => $dt->gabungan,

                'dokter' => $dt->dokter,
                'dokterid' => $dt->dokterid,
                'norec_apd' => $dt->norec_apd,
                'jumlah' => $dt->jumlah,
                'norec_pp' => $dt->norec_pp,
                'produkfk' => $dt->produkfk,
                'objectdepartemenfk' => $dt->objectdepartemenfk,
                'objectruanganfk' => $dt->objectruanganfk,
                'isparamedis' => $dt->isparamedis,
                'tipedokter' => 'Medis',
//                'rcanak' => $dt->rcanak,
//                'rcnicu' => $dt->rcnicu,
//                'rcigd' => $dt->rcigd,
//                'rcvk' => $dt->rcvk,
//                'rcnifas' => $dt->rcnifas,
            );
        }
//        if($dataReq['head']['norecsc'] == '-') {
            $dataPegawai = \DB::table('loginuser_s as lu')
                ->select('lu.objectpegawaifk')
                ->where('lu.id',$dataReq['userData']['id'])
                ->where('lu.kdprofile',$kdProfile)
                ->first();
//            if ($kdKomponen == 86) {
                $SCSC = StrukPagu::where('periodeawal', $dataReq['head']['periodeawal'])
                    ->select('norec')
                    ->where('kdprofile',$kdProfile)
                    ->get();


                if (count($SCSC) > 0) {
                    $norecDel = [];
                    foreach ($SCSC as $itemAkuh) {
                        $norecDel [] = $itemAkuh->norec;

                    }
                    // return $this->respond($norecDel);
                    $delSCSC = StrukPagu::whereIn('norec', $norecDel)
                        ->where('kdprofile',$kdProfile)
                        ->delete();
                    $delSCSC2 = StrukDetailPagu::whereIn('strukpagufk', $norecDel)
                        ->where('kdprofile',$kdProfile)
                        ->delete();
                }
//            }


            $nostrukpagu = $this->generateCode(new StrukPagu(), 'nostrukpagu', 10, 'PGU/' . $this->getDateTime()->format('ym'),$kdProfile);

            $dataSC = new StrukPagu();
            $dataSC->norec = $dataSC->generateNewId();
            $dataSC->kdprofile = $kdProfile;
            $dataSC->statusenabled = true;
            $dataSC->nostrukpagu = $nostrukpagu;
            $dataSC->tglstrukpagu = date('Y-m-d H:i:s');
            $dataSC->periodeawal = $dataReq['head']['periodeawal'];
            $dataSC->periodeakhir = null;
            $dataSC->pegawaiuserid = $dataPegawai->objectpegawaifk;
//            $dataSC->totaljasalayanan = $dataReq['head']['totaljasalayanan'];
//            $dataSC->totaljasaremun = $dataReq['head']['totaljasaremun'];
//            $dataSC->totaljasamanajemen = $dataReq['head']['totaljasamanajemen'];
            $dataSC->totalrcdokter = $dataReq['head']['rcdokter'];
            $dataSC->totalpostrm = $dataReq['head']['postremun'];
            $dataSC->totalrc = $dataReq['head']['rc'];
            $dataSC->totalccdireksi = $dataReq['head']['ccdireksi'];
            $dataSC->totalccstaffdireksi = $dataReq['head']['ccstaffdireksi'];
            $dataSC->totalccmanajemen = $dataReq['head']['ccmanajemen'];
            $dataSC->save();

            $norecSC = $dataSC->norec;
//        }else{
//            $norecSC = $dataReq['head']['norecsc'];
//        }
        $dataInsert = [];
        foreach ($newData2 as $item) {
            if ((float)$item['direksi'] >0){
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'strukpagufk' => $norecSC,
                    'pelayananpasienfk' => $item['norec_pp'],
                    'jenispagufk' => 13,
                    'jenispagupersen' => null,
                    'jenispagunilai' => $item['direksi'],
                    'produkfk' => $item['produkfk'],
                    'dokterid' => $item['dokterid'],
                    'tglpelayanan' => $item['tglpelayanan'],
                    'jumlah' => $item['jumlah'],
                    'ruanganfk' => $item['objectruanganfk'],
                    'namaexternal' => $item['tipedokter']
                );
            }
            if ((float)$item['struktural'] >0){
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'strukpagufk' => $norecSC,
                    'pelayananpasienfk' => $item['norec_pp'],
                    'jenispagufk' => 14,
                    'jenispagupersen' => null,
                    'jenispagunilai' => $item['struktural'],
                    'produkfk' => $item['produkfk'],
                    'dokterid' => $item['dokterid'],
                    'tglpelayanan' => $item['tglpelayanan'],
                    'jumlah' => $item['jumlah'],
                    'ruanganfk' => $item['objectruanganfk'],
                    'namaexternal' => $item['tipedokter']
                );
            }
            if ((float)$item['administrasi'] >0){
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'strukpagufk' => $norecSC,
                    'pelayananpasienfk' => $item['norec_pp'],
                    'jenispagufk' => 15,
                    'jenispagupersen' => null,
                    'jenispagunilai' => $item['administrasi'],
                    'produkfk' => $item['produkfk'],
                    'dokterid' => $item['dokterid'],
                    'tglpelayanan' => $item['tglpelayanan'],
                    'jumlah' => $item['jumlah'],
                    'ruanganfk' => $item['objectruanganfk'],
                    'namaexternal' => $item['tipedokter']
                );
            }
            if ((float)$item['jpl'] >0){
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'strukpagufk' => $norecSC,
                    'pelayananpasienfk' => $item['norec_pp'],
                    'jenispagufk' => 16,
                    'jenispagupersen' => null,
                    'jenispagunilai' => $item['jpl'],
                    'produkfk' => $item['produkfk'],
                    'dokterid' => $item['dokterid'],
                    'tglpelayanan' => $item['tglpelayanan'],
                    'jumlah' => $item['jumlah'],
                    'ruanganfk' => $item['objectruanganfk'],
                    'namaexternal' => $item['tipedokter']
                );
            }
            if ((float)$item['jptl'] >0){
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'strukpagufk' => $norecSC,
                    'pelayananpasienfk' => $item['norec_pp'],
                    'jenispagufk' => 17,
                    'jenispagupersen' => null,
                    'jenispagunilai' => $item['jptl'],
                    'produkfk' => $item['produkfk'],
                    'dokterid' => $item['dokterid'],
                    'tglpelayanan' => $item['tglpelayanan'],
                    'jumlah' => $item['jumlah'],
                    'ruanganfk' => $item['objectruanganfk'],
                    'namaexternal' => $item['tipedokter']
                );
            }
            if ((float)$item['gabungan'] >0){
                $dataInsert[] = array(
                    'norec' => substr(Uuid::generate(), 0, 32),
                    'kdprofile' => $kdProfile,
                    'statusenabled' => true,
                    'strukpagufk' => $norecSC,
                    'pelayananpasienfk' => $item['norec_pp'],
                    'jenispagufk' => 18,
                    'jenispagupersen' => null,
                    'jenispagunilai' => $item['gabungan'],
                    'produkfk' => $item['produkfk'],
                    'dokterid' => $item['dokterid'],
                    'tglpelayanan' => $item['tglpelayanan'],
                    'jumlah' => $item['jumlah'],
                    'ruanganfk' => $item['objectruanganfk'],
                    'namaexternal' => $item['tipedokter']
                );
            }
//
//            if ((float)$item['rcanestesi'] >0){
//                $dataInsert[] = array(
//                    'norec' => substr(Uuid::generate(), 0, 32),
//                    'kdprofile' => 12,
//                    'statusenabled' => true,
//                    'strukpagufk' => $norecSC,
//                    'pelayananpasienfk' => $item['norec_pp'],
//                    'jenispagufk' => 7,//RC DOKTER,
//                    'jenispagupersen' => null,
//                    'jenispagunilai' => $item['rcanestesi'],
//                    'produkfk' => $item['produkfk'],
//                    'dokterid' => $item['dokterid'],
//                    'tglpelayanan' => $item['tglpelayanan'],
//                    'jumlah' => $item['jumlah'],
//                    'ruanganfk' => $item['objectruanganfk'],
//                    'namaexternal' => $item['tipedokter']
//                );
//            }
//            if ((float)$item['rcanak'] >0){
//                $dataInsert[] = array(
//                    'norec' => substr(Uuid::generate(), 0, 32),
//                    'kdprofile' => 12,
//                    'statusenabled' => true,
//                    'strukpagufk' => $norecSC,
//                    'pelayananpasienfk' => $item['norec_pp'],
//                    'jenispagufk' => 7,//RC DOKTER,
//                    'jenispagupersen' => null,
//                    'jenispagunilai' => $item['rcanak'],
//                    'produkfk' => $item['produkfk'],
//                    'dokterid' => $item['dokterid'],
//                    'tglpelayanan' => $item['tglpelayanan'],
//                    'jumlah' => $item['jumlah'],
//                    'ruanganfk' => $item['objectruanganfk'],
//                    'namaexternal' => $item['tipedokter']
//                );
//            }
//
////                'tipedokter' => 'RDC anestesi',
//            if ((float)$item['rc'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi'){
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 8,//RC
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['rc'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => $item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//
//            if ((float)$item['postremun'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi') {
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 9,//POST REMUN
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['postremun'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => $item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//
//            if ((float)$item['ccdireksi'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi') {
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 10,//CC DIREKSI
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['ccdireksi'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => $item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//
//            if ((float)$item['ccstaffdireksi'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi') {
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 11,//CC STAFF DIREKSI
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['ccstaffdireksi'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => $item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//
//            if ((float)$item['ccmanajemen'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi') {
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 12,//CC MANAJEMEN
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['ccmanajemen'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => $item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//            if ((float)$item['rcok'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi'){
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 8,//RC
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['rcok'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => 44,//$item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//            if ((float)$item['rcnicu'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi'){
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 8,//RC
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['rcnicu'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => 65,//$item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//            if ((float)$item['rcigd'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi'){
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 8,//RC
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['rcigd'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => 29,//$item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//            if ((float)$item['rcvk'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi'){
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 8,//RC
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['rcvk'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => 454,//$item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
//            if ((float)$item['rcnifas'] >0) {
//                if  ($item['tipedokter'] != 'RDC anestesi'){
//                    $dataInsert[] = array(
//                        'norec' => substr(Uuid::generate(), 0, 32),
//                        'kdprofile' => 0,
//                        'statusenabled' => true,
//                        'strukpagufk' => $norecSC,
//                        'pelayananpasienfk' => $item['norec_pp'],
//                        'jenispagufk' => 8,//RC
//                        'jenispagupersen' => null,
//                        'jenispagunilai' => $item['rcnifas'],
//                        'produkfk' => $item['produkfk'],
//                        'dokterid' => $item['dokterid'],
//                        'tglpelayanan' => $item['tglpelayanan'],
//                        'jumlah' => $item['jumlah'],
//                        'ruanganfk' => 80,//$item['objectruanganfk'],
//                        'namaexternal' => $item['tipedokter']
//                    );
//                }
//            }
            if (count($dataInsert) > 100){
                StrukDetailPagu::insert($dataInsert);
                $dataInsert = [];
            }
        }
        StrukDetailPagu::insert($dataInsert);

        $result = array(
//            'data1' => $newData,
//            'data2' => $newData2,
            'data3' => $dataDokterAnak,
            'strukpagu' => $StrukPagu,
            'norecsc' => $norecSC,
            'message' => 'as@epic',
            'messages' => 'Sukses',
        );

        return $this->respond($result);
    }

}