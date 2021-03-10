<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 9/22/2017
 * Time: 2:55 AM
 */
/**
 * Created by PhpStorm.
 * User: nengepic
 * Date: 09/08/2019
 * Time: 10:34
 */

namespace App\Http\Controllers\Bridging;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Traits\Valet;
use DB;

use App\Transaksi\BridgingMiniR45;
use App\Transaksi\HIS_Obat_MS;
use App\Transaksi\HIS_Trans_HD;
use App\Transaksi\HIS_Trans_IT;



class BridgingFarmasiController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = false);
    }
    public function SimpanBridgingConsisD(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $data = DB::select(DB::raw("
             select pp.produkfk ,sr.norec, sr.noresep, sr.tglresep, ps.id as psid, pd.noregistrasi, ps.nocm, ps.namapasien, ru.id as ruid, ru.namaruangan, pg.id as pgid, 
            pg.namalengkap as dokter, jk.jeniskelamin, ps.tgllahir, pd.tglregistrasi, ala.alamatlengkap, ps.tgllahir, pp.jumlah, pp.dosis, pp.aturanpakai, 
            pp.produkfk, pr.namaproduk, pp.rke
             from strukresep_t as sr inner join pelayananpasien_t as pp on pp.strukresepfk = sr.norec 
            inner join antrianpasiendiperiksa_t as apd on apd.norec = sr.pasienfk inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk 
            inner join pasien_m as ps on ps.id = pd.nocmfk inner join produk_m as pr on pr.id = pp.produkfk left join alamat_m as ala on ala.nocmfk = pd.nocmfk 
            inner join pegawai_m as pg on pg.id = sr.penulisresepfk inner join ruangan_m as ru on ru.id = apd.objectruanganfk 
            inner join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk inner join his_obat_ms_t as ho on cast(ho.hobatid as INTEGER) = pp.produkfk 
            where sr.kdprofile = $idProfile and pp.strukresepfk = :norec_resep and ho.statusenabled =true and pp.jeniskemasanfk=2"),
            array(
                'norec_resep' => $request['strukresep'],
            )
        );

        $statusCounter = 'Kosong';
//        $dataCounterLast = DB::select(DB::raw("
//            select max(counterid) as maxcounterid from his_trans_hd_t where status=:status and cast(counterid as INTEGER)<9"),
//            array(
//                'status' => '0',
//            )
//        );
//            if ((int)$dataCounterLast[0]->maxcounterid < 8){
//                $counterid=1+(int)$dataCounterLast[0]->maxcounterid;
//            }else{
//                $dataCounterKosong = DB::select(DB::raw("
//                    select counterid  from his_trans_hd_t where status=:status"),
//                    array(
//                        'status' => '1',
//                    )
//                );
//                if (count($dataCounterKosong) == 0){
//                    $statusCounter = 'Penuh';
//                }else{
//                    $counterid=(int)$dataCounterKosong[0]->counterid;
//                    $update = DB::select(DB::raw("
//                    update  his_trans_hd_t set status='2' where counterid=:counterid"),
//                        array(
//                            'counterid' => $counterid,
//                        )
//                    );
//                }
//            }
        if ($statusCounter == 'Kosong') {
            if ($data[0]->jeniskelamin == 'Perempuan') {
                $jk = 'F';
            } else {
                $jk = 'M';
            };
            $nmpasien = $data[0]->namapasien;#
            $alamat = str_limit($data[0]->alamatlengkap, 100);#
            $transactionCode = $data[0]->noresep;#
            $umur = $this->hitungUmur($data[0]->tgllahir);

            $umur = str_replace('Tahun', true, $umur);
            $umur = str_replace('Bulan', 'b', $umur);
            $umur = str_replace('Hari', 'h', $umur);
            $umur = str_replace(' ', '', $umur);
            $umur = str_replace(',', '', $umur);
            $umur = str_replace('.', '', $umur);
            $umur = str_limit($umur, 9);

            $nmpasien = str_replace(',', '', $nmpasien);
            $nmpasien = str_replace('\'', '', $nmpasien);
            $nmpasien = str_replace('"', '', $nmpasien);
            $nmpasien = str_replace('/', '', $nmpasien);
            $nmpasien = str_replace('\\', '', $nmpasien);

            $alamat = str_replace(',', '', $alamat);
            $alamat = str_replace('\'', '', $alamat);
            $alamat = str_replace('"', '', $alamat);
            $alamat = str_replace('/', '', $alamat);
            $alamat = str_replace('\\', '', $alamat);

            $transactionCode = str_replace(',', '', $transactionCode);
            $transactionCode = str_replace('\'', '', $transactionCode);
            $transactionCode = str_replace('"', '', $transactionCode);
            $transactionCode = str_replace('/', '', $transactionCode);
            $transactionCode = str_replace('\\', '', $transactionCode);


            try {
                $newBRG = new HIS_Trans_HD();#
                $norecBRG = $newBRG->generateNewId();#
                $norecHIS = $this->generateCode(new HIS_Trans_HD, 'transaksiid', 13, $transactionCode . '/');
                $newBRG->norec = $norecBRG;#
                $newBRG->kdprofile = 0;#
                $newBRG->statusenabled = true;#
                $newBRG->transaksiid = $norecHIS;//$transactionCode;#
                $newBRG->counterid = $request['counterid'];#
                $newBRG->mrn = $data[0]->nocm;#
                $newBRG->nama = $nmpasien;#
                $newBRG->umur = $umur;#
                $newBRG->alamat = $alamat;#
                $newBRG->jeniskelamin = $jk;#
                $newBRG->status = '0';#

                $newBRG->save();
                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "Simpan Bridging";
            }

            foreach ($data as $item) {
                try {
                    $dataobat = DB::select(DB::raw("
                            select packageunit from his_obat_ms_t where hobatid=:hobatid"),
                        array(
                            'hobatid' => $item->produkfk,
                        )
                    );
                    $qtypack = (int)$dataobat[0]->packageunit;
                    if ((int)$item->jumlah % $qtypack > 0) {
                        $qty = (int)((int)$item->jumlah / $qtypack) + 1;
                    } else {
                        $qty = (int)((int)$item->jumlah / $qtypack);
                    }


                    $newIT = new HIS_Trans_IT();#
                    $norecIT = $newIT->generateNewId();#
                    $newIT->norec = $norecIT;#
                    $newIT->kdprofile = 0;#
                    $newIT->statusenabled = true;#
                    $newIT->obatid = $item->produkfk;#
                    $newIT->qty = $qty;#
                    $newIT->transaksiid =$norecHIS;// $transactionCode;#

                    $newIT->save();
                    $transStatus = 'true';
                } catch (\Exception $e) {
                    $transStatus = 'false';
                    $transMessage = "Simpan Bridging";
                }
            }
        }

//
        $transMessage = "Simpan Bridging Gagal!!";
        if ($statusCounter == 'Penuh'){
            $transStatus = 'false';
            $transMessage = "CounterID Full";
            $newBRG ='penuh';
        }
        if ($transStatus == 'true' ) {
            $transMessage = "Simpan Bridging Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        } else {

            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
                "noresep" => $newBRG,
                "as" => 'as@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}