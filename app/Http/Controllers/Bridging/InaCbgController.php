<?php
/**
 * Created by PhpStorm.
 * User: as@epic
 * Date: 19/02/2019
 * Time: 14.54
 */

namespace App\Http\Controllers\Bridging;

use App\Http\Controllers\ApiController;
use App\Master\DiagnosaKeperawatan;
use App\Master\KelompokTransaksi;
use App\Transaksi\InformasiTanggungan;
use DB;
use Illuminate\Http\Request;
use App\Traits\Valet;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;


class InaCbgController   extends ApiController
{

    use Valet;

    public function __construct() {
    parent::__construct($skip_authentication=false);
}
    public function saveBridgingINACBG(Request $request)
    {
        $data  = \DB::table('settingdatafixed_m')
            ->select('namafield','nilaifield')
            ->where('keteranganfungsi','inacbg')
            ->get();
//        return $data;
        foreach ($data as $item){
            if ($item->namafield == 'codernik'){
                $codernik = $item->nilaifield;
            }
            if ($item->namafield == 'key'){
                $key = $item->nilaifield;
            }
            if ($item->namafield == 'url'){
                $url = $item->nilaifield;
            }
        }

//        $key = "1fa5106f46eedd6d536bb816f4dc23728e0ca1dee3db20904e634134b205b28d";//        $url = "localhost/E-Klaim/ws.php";

        $dataReq = $request['data'];
        $responseArr = [];
        foreach ($dataReq as $dataLoop){
            $json_request = json_encode($dataLoop);
            $payload = $this->inacbg_encrypt($json_request,$key);
            $header = array("Content-Type: application/x-www-form-urlencoded");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            if ($err) {
                return $this->setStatusCode(400)->respond($err, $err);
            }
            $first  = strpos($response, "\n")+1;
            $last   = strrpos($response, "\n")-1;
            $response  = substr($response,
                $first,
                strlen($response) - $first - $last);
            $response = $this->inacbg_decrypt($response,$key);
            $responseArr[] =array(
                'datarequest' => $dataLoop,
                'dataresponse' =>   $response
            );
        }
        $result = array(
            "status" => 201,
            "dataresponse" => $responseArr,
            "as" => 'as@epic',
        );
//        return $this->respond($responseArr);
        return $this->setStatusCode($result['status'])->respond($result, "Bridging InaCBG");
    }
    // Encryption Function
    function inacbg_encrypt($data, $key) {
        /// make binary representasion of $key
        $key = hex2bin($key);
        /// check key length, must be 256 bit or 32 bytes
        if (mb_strlen($key, "8bit") !== 32) {
          throw new Exception("Needs a 256-bit key!");
        }
        /// create initialization vector
        $iv_size = openssl_cipher_iv_length("aes-256-cbc");
        $iv = openssl_random_pseudo_bytes($iv_size);
        // dengan catatan dibawah
        /// encrypt
        $encrypted = openssl_encrypt($data,
            "aes-256-cbc",
            $key,
            OPENSSL_RAW_DATA,
            $iv );
        /// create signature, against padding oracle attacks
        $signature = mb_substr(hash_hmac("sha256",
            $encrypted,
            $key,
            true),0,10,"8bit");
        /// combine all, encode, and format
        $encoded = chunk_split(base64_encode($signature.$iv.$encrypted));
        return $encoded;
    }
    // Decryption Function
    function inacbg_decrypt($str, $strkey){
        /// make binary representation of $key
        $key = hex2bin($strkey);
        /// check key length, must be 256 bit or 32 bytes
        if (mb_strlen($key, "8bit") !== 32) {
            throw new Exception("Needs a 256-bit key!");
        }
        /// calculate iv size
        $iv_size = openssl_cipher_iv_length("aes-256-cbc");
        /// breakdown parts
        $decoded = base64_decode($str);
        $signature = mb_substr($decoded,0,10,"8bit");
        $iv = mb_substr($decoded,10,$iv_size,"8bit");
        $encrypted = mb_substr($decoded,$iv_size+10,NULL,"8bit");
        /// check signature, against padding oracle attack
        $calc_signature = mb_substr(hash_hmac("sha256",
            $encrypted,
            $key,
            true),0,10,"8bit");
        if($this->inacbg_compare($signature,$calc_signature)) {
//            return "SIGNATURE_NOT_MATCH"; /// signature doesn't match
        }
        $decrypted = openssl_decrypt($encrypted,
            "aes-256-cbc",
            $key,
            OPENSSL_RAW_DATA,
            $iv);
        $dtdtd = json_decode($decrypted);
        return $dtdtd;
    }
    /// Compare Function
    function inacbg_compare($a, $b) {
        /// compare individually to prevent timing attacks
        /// compare length
        if (strlen($a) !== strlen($b)) return false;
        /// compare individual
        $result = 0;
        for($i = 0; $i < strlen($a); $i ++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result == 0;
    }
    public function getDaftarPasien(Request $request)
    {
        $data  = \DB::table('settingdatafixed_m')
            ->select('namafield','nilaifield')
            ->where('keteranganfungsi','inacbg')
            ->get();
        foreach ($data as $item){
            if ($item->namafield == 'codernik'){
                $codernik = $item->nilaifield;
            }
            if ($item->namafield == 'key'){
                $key = $item->nilaifield;
            }
            if ($item->namafield == 'url'){
                $url = $item->nilaifield;
            }
            if ($item->namafield == 'kodetarif'){
                $kodetarif = $item->nilaifield;
            }
        }

        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
//            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pemakaianasuransi_t as pas', 'pas.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('asuransipasien_m as asu', 'asu.id', '=', 'pas.objectasuransipasienfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'asu.objectkelasdijaminfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk', 'sbm.nosbm', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pd.objectruanganlastfk as ruanganid','pas.nosep','pas.norec as norec_pa','br.norec as norec_br',
                'pas.nokepesertaan','ps.tgllahir','ps.objectjeniskelaminfk','dept.id as deptid','kls.nourut as nokelasdaftar','kls2.nourut as nokelasdijamin',
                'kls2.namakelas','pd.objectstatuspulangfk')
            ->whereNull('br.norec')
            ->where('pas.nosep','<>','')
            ->whereNotNull('pas.nosep')
            ->whereNotNull('pd.tglpulang');

//            ->where('apd.objectruanganasalfk',null);

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
//        if (isset($filter['kelId']) && $filter['kelId'] != "" && $filter['kelId'] != "undefined") {
//            $data = $data->where('kp.id', '=', $filter['kelId']);
//        }
        $paramKel  ='';
        if(isset($request['kelId']) && $request['kelId']!="" && $request['kelId']!="undefined"){
            $arrKel = explode(',',$request['kelId']) ;
            $kodeKel = [];
            foreach ( $arrKel as $item){
                $kodeKel[] = (int) $item;
            }
            $paramKel = ' and kp.id in ('.$request['kelId'].')';
            $data = $data->whereIn('kp.id',$kodeKel);
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
        if (isset($filter['nosep']) && $filter['nosep'] != "" && $filter['nosep'] != "undefined") {
            $data = $data->where('pas.nosep', '=', $filter['nosep']);
        }
//        if (isset($filter['jmlRows']) && $filter['jmlRows'] != "" && $filter['jmlRows'] != "undefined") {
//            $data = $data->take($filter['jmlRows']);
//        }
        $data = $data->orderBy('pd.noregistrasi');

        $data = $data->get();

        $i = 0 ;
        $dtdt = '';
        $dataDiagnosa = \DB::table('detaildiagnosapasien_t as dp')
            ->join('diagnosa_m as dg', 'dg.id', '=', 'dp.objectdiagnosafk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dp.noregistrasifk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('dg.kddiagnosa','apd.objectasalrujukanfk','pd.norec')
//            ->where('apd.noregistrasifk',$data[$i]->norec)
            ->where('pd.tglregistrasi', '>=', $filter['tglAwal'])
            ->where('pd.tglregistrasi', '<=', $filter['tglAkhir'])
            ->get();
        foreach ($data as $item){
            $dtdt = '';
            $asalRujukan = '';
//            $dataDiagnosa = \DB::table('detaildiagnosapasien_t as dp')
//                ->join('diagnosa_m as dg', 'dg.id', '=', 'dp.objectdiagnosafk')
//                ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dp.noregistrasifk')
//                ->select('dg.kddiagnosa','apd.objectasalrujukanfk')
//                ->where('apd.noregistrasifk',$data[$i]->norec)
//                ->get();
            foreach ($dataDiagnosa as $item2){
                if ($item2->norec == $data[$i]->norec){
                    $dtdt = $dtdt . '#' .  $item2->kddiagnosa;
                    $asalRujukan = $item2->objectasalrujukanfk;
                }
            }
            $data[$i]->icd10 = substr($dtdt,1,strlen($dtdt)-1);
            $data[$i]->codernik = $codernik;
            $data[$i]->objectasalrujukanfk = $asalRujukan;
            $data[$i]->kodetarif = $kodetarif;
            $i= $i + 1 ;
        }

        $i = 0 ;
        $dtdt = '';
        $dataICD9 = \DB::table('diagnosatindakanpasien_t as dpa')
            ->join('detaildiagnosatindakanpasien_t as dp', 'dpa.norec', '=', 'dp.objectdiagnosatindakanpasienfk')
            ->join('diagnosatindakan_m as dg', 'dg.id', '=', 'dp.objectdiagnosatindakanfk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dpa.objectpasienfk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('dg.kddiagnosatindakan','pd.norec')
//            ->where('apd.noregistrasifk',$data[$i]->norec)
            ->where('pd.tglregistrasi', '>=', $filter['tglAwal'])
            ->where('pd.tglregistrasi', '<=', $filter['tglAkhir'])
            ->get();
        foreach ($data as $item){
            $dtdt = '';
//            $dataICD9 = \DB::table('diagnosatindakanpasien_t as dpa')
//                ->join('detaildiagnosatindakanpasien_t as dp', 'dpa.norec', '=', 'dp.objectdiagnosatindakanpasienfk')
//                ->join('diagnosatindakan_m as dg', 'dg.id', '=', 'dp.objectdiagnosatindakanfk')
//                ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dpa.objectpasienfk')
//                ->select('dg.kddiagnosatindakan')
//                ->where('apd.noregistrasifk',$data[$i]->norec)
//                ->get();
            foreach ($dataICD9 as $item2){
                if ($item2->norec == $data[$i]->norec) {
                    $dtdt = $dtdt . '#' . $item2->kddiagnosatindakan;
                }
            }
            $data[$i]->icd9 = substr($dtdt,1,strlen($dtdt)-1);
            $i= $i + 1 ;
        }

        $tglawalawal = $filter['tglAwal'];
        $tglakhirakhir = $filter['tglAkhir'];
        $dataTarif16 = DB::select(DB::raw("select pd.norec, sum(((pp.hargajual - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah)+ case when pp.jasa is null then 0 else pp.jasa end) as ttl,kpb.namaexternal
            from pasiendaftar_t as pd
            inner join pasien_m as ps on ps.id = pd.nocmfk
            INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
            INNER JOIN produk_m as pr on pr.id=pp.produkfk
            INNER JOIN kelompokprodukbpjs_m as kpb on kpb.id=pr.objectkelompokprodukbpjsfk
            left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
            left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
            where br.norec is null  and 
            pd.tglregistrasi >= '$tglawalawal' and pd.tglregistrasi <= '$tglakhirakhir'  
            $paramKel
            group  by pd.norec,kpb.namaexternal order by pd.norec")
        );
        $i = 0 ;
        $prosedur_non_bedah ='';
        $prosedur_bedah ='';
        $konsultasi ='';
        $tenaga_ahli ='';
        $keperawatan ='';
        $penunjang ='';
        $radiologi ='';
        $laboratorium ='';
        $pelayanan_darah ='';
        $rehabilitasi ='';
        $kamar ='';
        $rawat_intensif ='';
        $obat ='';
        $obat_kronis ='';
        $obat_kemoterapi ='';
        $alkes ='';
        $bmhp ='';
        $sewa_alat ='';
        foreach ($data as $item){
            $norecpd= $data[$i]->norec;
            foreach ($dataTarif16 as $itm){
                if ($itm->norec == $norecpd){
//                    $data[$i]->tarif_rs->($dataTarif16[0]->namaexternal) => (float)$dataTarif16[0]->ttl,
                    if ($itm->namaexternal == 'prosedur_non_bedah'){
                        $prosedur_non_bedah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'prosedur_bedah'){
                        $prosedur_bedah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'konsultasi'){
                        $konsultasi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'tenaga_ahli'){
                        $tenaga_ahli = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'keperawatan'){
                        $keperawatan = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'penunjang'){
                        $penunjang = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'radiologi'){
                        $radiologi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'laboratorium'){
                        $laboratorium = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'pelayanan_darah'){
                        $pelayanan_darah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'rehabilitasi'){
                        $rehabilitasi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'kamar'){
                        $kamar = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'rawat_intensif'){
                        $rawat_intensif = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat'){
                        $obat = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat_kronis'){
                        $obat_kronis = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat_kemoterapi'){
                        $obat_kemoterapi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'alkes'){
                        $alkes = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'bmhp'){
                        $bmhp = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'sewa_alat'){
                        $sewa_alat = (float)$itm->ttl;
                    }
                }
            }

            $datatatat = array(
                'prosedur_non_bedah' => (float)$prosedur_non_bedah,
                'prosedur_bedah' => (float)$prosedur_bedah,
                'konsultasi' => (float)$konsultasi,
                'tenaga_ahli' => (float)$tenaga_ahli,
                'keperawatan' => (float)$keperawatan,
                'penunjang' => (float)$penunjang,
                'radiologi' => (float)$radiologi,
                'laboratorium' => (float)$laboratorium,
                'pelayanan_darah' => (float)$pelayanan_darah,
                'rehabilitasi' => (float)$rehabilitasi,
                'kamar' => (float)$kamar,
                'rawat_intensif' => (float)$rawat_intensif,
                'obat' => (float)$obat,
                'obat_kronis' => (float)$obat_kronis,
                'obat_kemoterapi' => (float)$obat_kemoterapi,
                'alkes' => (float)$alkes,
                'bmhp' => (float)$bmhp,
                'sewa_alat' => (float)$sewa_alat,
            );
            $prosedur_non_bedah =0;
            $prosedur_bedah =0;
            $konsultasi =0;
            $tenaga_ahli =0;
            $keperawatan =0;
            $penunjang =0;
            $radiologi =0;
            $laboratorium =0;
            $pelayanan_darah =0;
            $rehabilitasi =0;
            $kamar =0;
            $rawat_intensif =0;
            $obat =0;
            $obat_kronis =0;
            $obat_kemoterapi =0;
            $alkes =0;
            $bmhp =0;
            $sewa_alat =0;
            $data[$i]->tarif_rs = $datatatat;

            $i= $i + 1 ;
//            $dataTarif16 = DB::select(DB::raw("select kpb.id,kpb.namaexternal,sum(x.total) as ttl
//                from kelompokprodukbpjs_m kpb
//                left JOIN
//                (select (pp.hargajual - pp.hargadiscount) * pp.jumlah as total ,pr.objectkelompokprodukbpjsfk
//                from produk_m as pr
//                INNER JOIN pelayananpasien_t as pp on pr.id=pp.produkfk
//                INNER JOIN antrianpasiendiperiksa_t as apd on pp.noregistrasifk=apd.norec
//                INNER JOIN pasiendaftar_t as pd on apd.noregistrasifk=pd.norec
//                where pd.norec='$norecpd' ) as x
//                on x.objectkelompokprodukbpjsfk=kpb.id
//                where kpb.id <> 0
//                group by kpb.id,kpb.namaexternal
//                order by kpb.id")
//            );



        }

//        return $this->respond(array(
//            'data1' => $dataTarif16,
//            'data2' =>$data,
//        ));
        return $this->respond($data);
    }
    public function getComboInaCbg(Request $request)
    {
        $dataLogin = $request->all();
        $dataInstalasi = \DB::table('departemen_m as dp')
            ->whereIn('dp.id', array(3, 14, 16, 17, 18, 19, 24, 25, 26, 27, 28, 35))
            ->where('dp.statusenabled', true)
            ->orderBy('dp.namadepartemen')
            ->get();

        $dataRuangan = \DB::table('ruangan_m as ru')
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();

        $darah = \DB::table('ruangan_m as ru')
            ->where('ru.kdruangan', '41')
            ->orderBy('ru.namaruangan')
            ->get();

        $dept = \DB::table('departemen_m as dept')
            ->where('dept.id', '18')
            ->orderBy('dept.namadepartemen')
            ->get();

        $departemen = \DB::table('departemen_m as dept')
            ->where('dept.statusenabled', true)
            ->orderBy('dept.namadepartemen')
            ->get();

        $deptRajalInap = \DB::table('departemen_m as dept')
            ->whereIn('dept.id', [18, 16])
            ->orderBy('dept.namadepartemen')
            ->get();

        $ruanganRi = \DB::table('ruangan_m as ru')
            ->whereIn('ru.objectdepartemenfk',[16,17,25,35])
//            ->wherein('ru.objectdepartemenfk', ['18', '28'])
            ->where('ru.statusenabled',true)
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

        $dataKelas = \DB::table('kelas_m as kl')
            ->select('kl.id', 'kl.reportdisplay','kl.namakelas')
            ->where('kl.statusenabled', true)
            ->orderBy('kl.namakelas')
            ->get();

        $pembatalan = \DB::table('pembatal_m as p')
            ->select('p.id', 'p.name')
            ->where('p.statusenabled', true)
            ->orderBy('p.name')
            ->get();

        $kdPelayananRanap = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.statusenabled', true)
            ->where('p.namafield','kddeptlayananRI')
            ->first();

        $kdPelayananOk = \DB::table('settingdatafixed_m as p')
            ->select('p.nilaifield')
            ->where('p.statusenabled', true)
            ->where('p.namafield','KdPelayananOk')
            ->first();

        $dataKelompokTanpaUmum = \DB::table('kelompokpasien_m as kp')
            ->select('kp.id', 'kp.kelompokpasien')
            ->where('kp.statusenabled', true)
            ->where('kp.id', '<>', 1)
            ->orderBy('kp.kelompokpasien')
            ->get();

        $result = array(
            'departemen' => $dataDepartemen,
            'kelompokpasien' => $dataKelompok,
            'dokter' => $dataDokter,
            'datalogin' => $dataLogin,
            'kelas' => $dataKelas,
            'darah' => $darah,
            'dept' => $dept,
            'ruanganRi' => $ruanganRi,
            'deptrirj' => $deptRajalInap,
            'ruanganall' => $dataRuangan,
            'pembatalan' => $pembatalan,
            'deptt' => $departemen,
//            'rekanan' => $dataRekanan,
            'kelompokpasiensatu' => $dataKelompokTanpaUmum,
            'kddeptlayananranap' => $kdPelayananRanap,
            'kddeptlayananok' => $kdPelayananOk,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getDaftarPasienInformasiTanggungan(Request $request)
    {

        $data  = \DB::table('settingdatafixed_m')
            ->select('namafield','nilaifield')
            ->where('keteranganfungsi','inacbg')
            ->get();
        foreach ($data as $item){
            if ($item->namafield == 'codernik'){
                $codernik = $item->nilaifield;
            }
            if ($item->namafield == 'key'){
                $key = $item->nilaifield;
            }
            if ($item->namafield == 'url'){
                $url = $item->nilaifield;
            }
            if ($item->namafield == 'kodetarif'){
                $kodetarif = $item->nilaifield;
            }
        }

        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pemakaianasuransi_t as pas', 'pas.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('asuransipasien_m as asu', 'asu.id', '=', 'pas.objectasuransipasienfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'asu.objectkelasdijaminfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('informasitanggungansementara_t as hg', 'hg.noregistrasifk', '=', 'pd.norec')
//            ->leftjoin('diagnosaberatbadanbayi_t as dbb', 'dbb.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk', 'sbm.nosbm', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pd.objectruanganlastfk as ruanganid','pas.nosep','pas.norec as norec_pa','br.norec as norec_br',
                'pas.nokepesertaan','ps.tgllahir','ps.objectjeniskelaminfk','dept.id as deptid','kls.nourut as nokelasdaftar','kls2.nourut as nokelasdijamin',
                'kls.reportdisplay as namakelasdaftar','kls2.reportdisplay as namakelas','pd.objectstatuspulangfk','hg.totalpiutangpenjamin','hg.biayanaikkelas',
//                'dbb.beratbadan',
                'rk.id as idrekanan')
            ->whereNull('br.norec')
            ->where('pas.nosep','<>','')
            ->whereNotNull('pas.nosep')
            ->whereNull('pd.tglpulang');

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
            $arr = explode(',',$filter['kelId']);

            $kode = [];
            foreach ($arr as $itm){
                $kode[]= $itm;
            }

            $data = $data->whereIn('kp.id', $kode);
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
        if (isset($filter['nosep']) && $filter['nosep'] != "" && $filter['nosep'] != "undefined") {
            $data = $data->where('pas.nosep', '=', $filter['nosep']);
        }
        if (isset($filter['kelasId']) && $filter['kelasId'] != "" && $filter['kelasId'] != "undefined") {
            $data = $data->where('kls.id', '=', $filter['kelasId']);
        }

        $data = $data->orderBy('pd.noregistrasi');

        $data = $data->get();
//        return $this->respond($data);
        // return($data);
        // die();
        $i = 0 ;
        $dtdt = '';

        $dataDiagnosa = \DB::table('detaildiagnosapasien_t as dp')
            ->join('diagnosa_m as dg', 'dg.id', '=', 'dp.objectdiagnosafk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dp.noregistrasifk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('dg.kddiagnosa','apd.objectasalrujukanfk','pd.norec')
            ->wherein('dp.objectjenisdiagnosafk',array(1,2))
            ->where('pd.tglregistrasi', '>=', $filter['tglAwal'])
            ->where('pd.tglregistrasi', '<=', $filter['tglAkhir'])
            ->get();
        foreach ($data as $item){
            $dtdt = '';
            $asalRujukan = '';

            foreach ($dataDiagnosa as $item2){
                if ($item2->norec == $data[$i]->norec){
                    $dtdt = $dtdt . '#' .  $item2->kddiagnosa;
                    $asalRujukan = $item2->objectasalrujukanfk;
                }
            }
            $data[$i]->icd10 = substr($dtdt,1,strlen($dtdt)-1);
            $data[$i]->codernik = $codernik;
            $data[$i]->objectasalrujukanfk = $asalRujukan;
            $data[$i]->kodetarif = $kodetarif;
            $i= $i + 1 ;
        }


        $i = 0 ;
        $dtdt = '';
        $dataICD9 = \DB::table('diagnosatindakanpasien_t as dpa')
            ->join('detaildiagnosatindakanpasien_t as dp', 'dpa.norec', '=', 'dp.objectdiagnosatindakanpasienfk')
            ->join('diagnosatindakan_m as dg', 'dg.id', '=', 'dp.objectdiagnosatindakanfk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dpa.objectpasienfk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('dg.kddiagnosatindakan','pd.norec')
            //            ->where('apd.noregistrasifk',$data[$i]->norec)
            ->where('pd.tglregistrasi', '>=', $filter['tglAwal'])
            ->where('pd.tglregistrasi', '<=', $filter['tglAkhir'])
            ->get();
        foreach ($data as $item){
            $dtdt = '';
            foreach ($dataICD9 as $item2){
                if ($item2->norec == $data[$i]->norec) {
                    $dtdt = $dtdt . '#' . $item2->kddiagnosatindakan;
                }
            }
            $data[$i]->icd9 = substr($dtdt,1,strlen($dtdt)-1);
            $i= $i + 1 ;
        }

        $tglawalawal = $filter['tglAwal'];
        $tglakhirakhir = $filter['tglAkhir'];
        $kelompokPasien=$filter['kelId'];
        $dataTarif16 = DB::select(DB::raw("select pd.norec, 
            sum(((pp.hargajual - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah)+ case when pp.jasa is null then 0 else pp.jasa end) as ttl,kpb.namaexternal
            from pasiendaftar_t as pd
            inner join pasien_m as ps on ps.id = pd.nocmfk
            INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
            INNER JOIN produk_m as pr on pr.id=pp.produkfk
            INNER JOIN kelompokprodukbpjs_m as kpb on kpb.id=pr.objectkelompokprodukbpjsfk
            left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
            left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
            where br.norec is null  and 
            pd.tglregistrasi >= '$tglawalawal' and pd.tglregistrasi <= '$tglakhirakhir' and kp.id in ($kelompokPasien)
            group  by pd.norec,kpb.namaexternal order by pd.norec")
        );

//        return $this->respond($dataTarif16);
        $i = 0 ;
        $prosedur_non_bedah ='';
        $prosedur_bedah ='';
        $konsultasi ='';
        $tenaga_ahli ='';
        $keperawatan ='';
        $penunjang ='';
        $radiologi ='';
        $laboratorium ='';
        $pelayanan_darah ='';
        $rehabilitasi ='';
        $kamar ='';
        $rawat_intensif ='';
        $obat ='';
        $obat_kronis ='';
        $obat_kemoterapi ='';
        $alkes ='';
        $bmhp ='';
        $sewa_alat ='';
        foreach ($data as $item){
            $norecpd= $data[$i]->norec;
            $data[$i]->totaltagihan = $this->getTotalBiaya($data[$i]->noregistrasi);
            foreach ($dataTarif16 as $itm){
                if ($itm->norec == $norecpd){
                    if ($itm->namaexternal == 'prosedur_non_bedah'){
                        $prosedur_non_bedah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'prosedur_bedah'){
                        $prosedur_bedah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'konsultasi'){
                        $konsultasi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'tenaga_ahli'){
                        $tenaga_ahli = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'keperawatan'){
                        $keperawatan = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'penunjang'){
                        $penunjang = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'radiologi'){
                        $radiologi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'laboratorium'){
                        $laboratorium = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'pelayanan_darah'){
                        $pelayanan_darah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'rehabilitasi'){
                        $rehabilitasi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'kamar'){
                        $kamar = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'rawat_intensif'){
                        $rawat_intensif = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat'){
                        $obat = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat_kronis'){
                        $obat_kronis = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat_kemoterapi'){
                        $obat_kemoterapi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'alkes'){
                        $alkes = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'bmhp'){
                        $bmhp = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'sewa_alat'){
                        $sewa_alat = (float)$itm->ttl;
                    }
                }
            }

            $datatatat = array(
                'prosedur_non_bedah' => (float)$prosedur_non_bedah,
                'prosedur_bedah' => (float)$prosedur_bedah,
                'konsultasi' => (float)$konsultasi,
                'tenaga_ahli' => (float)$tenaga_ahli,
                'keperawatan' => (float)$keperawatan,
                'penunjang' => (float)$penunjang,
                'radiologi' => (float)$radiologi,
                'laboratorium' => (float)$laboratorium,
                'pelayanan_darah' => (float)$pelayanan_darah,
                'rehabilitasi' => (float)$rehabilitasi,
                'kamar' => (float)$kamar,
                'rawat_intensif' => (float)$rawat_intensif,
                'obat' => (float)$obat,
                'obat_kronis' => (float)$obat_kronis,
                'obat_kemoterapi' => (float)$obat_kemoterapi,
                'alkes' => (float)$alkes,
                'bmhp' => (float)$bmhp,
                'sewa_alat' => (float)$sewa_alat,
            );
            $prosedur_non_bedah =0;
            $prosedur_bedah =0;
            $konsultasi =0;
            $tenaga_ahli =0;
            $keperawatan =0;
            $penunjang =0;
            $radiologi =0;
            $laboratorium =0;
            $pelayanan_darah =0;
            $rehabilitasi =0;
            $kamar =0;
            $rawat_intensif =0;
            $obat =0;
            $obat_kronis =0;
            $obat_kemoterapi =0;
            $alkes =0;
            $bmhp =0;
            $sewa_alat =0;
            $data[$i]->tarif_rs = $datatatat;

            $i= $i + 1 ;
        }
            return $this->respond($data);

    }
    protected  function getTotalBiaya ($noregis){
        $data = DB::select(DB::raw("SELECT 
            sum((((pp.hargasatuan - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is not null then pp.jasa else 0 end ) 
            -(CASE WHEN (pp.produkfk = 402611) THEN pp.hargasatuan ELSE (0) END * pp.jumlah)) as total
            FROM
            pelayananpasien_t AS pp
            INNER JOIN antrianpasiendiperiksa_t AS apd ON pp.noregistrasifk= apd.norec
            INNER JOIN pasiendaftar_t AS pd ON apd.noregistrasifk = pd.norec
            WHERE pd.noregistrasi='$noregis'"));
        $total = 0;
        if(count($data )> 0){
            $total = (float) $data[0]->total;
        }
        return $total;
    }
    protected  function getTotalBiayaRS ($norec_pd){
        $data = DB::select(DB::raw("SELECT 
            sum((((pp.hargasatuan - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is not null then pp.jasa else 0 end ) 
            -(CASE WHEN (pp.produkfk = 402611) THEN pp.hargasatuan ELSE (0) END * pp.jumlah)) as total
            FROM
            pelayananpasien_t AS pp
            INNER JOIN antrianpasiendiperiksa_t AS apd ON pp.noregistrasifk= apd.norec
            INNER JOIN pasiendaftar_t AS pd ON apd.noregistrasifk = pd.norec
            WHERE pd.norec='$norec_pd'"));
        $total = 0;
        if(count($data )> 0){
            $total = (float) $data[0]->total;
        }
        return $total;
    }
    public function getStatusNaikKelas(Request $request)
    {
        $data =[];
        foreach ($request['data'] as $items){

            $noregistrasifk= $items['noreg'] ;//$request['noreg'];
            $hakkelas= $items['namakelas'];///$request['namakelas'];

            if($hakkelas=='Non Kelas'){
                $b=0;
            }else if($hakkelas=='kelas_3'){
                $b=1;
            }else if($hakkelas=='kelas_2'){
                $b=2;
            }else if($hakkelas=='kelas_1'){
                $b=3;
            }else if($hakkelas=='vip') {
                $b = 4;
            }else if($hakkelas==''){
                $b=0;
            }

        $datas = DB::select(DB::raw("
          select * from ( select tglmasuk,tglkeluar,reportdisplay,	
            CASE antrianpasiendiperiksa_t.objectkelasfk WHEN 6 THEN  0
            WHEN 1 THEN  1  WHEN 2 THEN 2 WHEN 3 THEN 3  WHEN 4 THEN  4
            WHEN 7 THEN  4
            ELSE 1 END AS derajatkelas 
            from 
                antrianpasiendiperiksa_t
            INNER JOIN kelas_m ON antrianpasiendiperiksa_t.objectkelasfk = kelas_m.id
            where noregistrasifk ='$noregistrasifk'  ) as x where  x.derajatkelas>$b order by x.derajatkelas asc 
            ")
        );

        $datass = DB::select(DB::raw("select tglmasuk,tglkeluar from antrianpasiendiperiksa_t where objectruanganfk in ('64','65','77','78','127') and 
                noregistrasifk='$noregistrasifk' order by tglmasuk asc"));

        $statusrawatintensiv='0';
        $lamarawatintensivaa=0;
        foreach($datass as $item){

            $statusrawatintensiv='1';
            $tglmasuk =date_format(date_create($item->tglmasuk),'Y-m-d');
            $tglkeluar =date_format(date_create($item->tglkeluar),'Y-m-d');
            if($tglmasuk==$tglkeluar){
                $lamarawatintensiv = '1';
            }else{
                $tglmasuk = strtotime($tglmasuk);
                $tglkeluar = strtotime($tglkeluar);
                $diffs= $tglkeluar-$tglmasuk;
                $dayss = floor($diffs / (3600 * 24));;
                $lamarawatintensiv = $dayss;

            }
            $lamarawatintensivaa= $lamarawatintensivaa + $lamarawatintensiv;
        }

        $statusnaikkelas='0';
        $kelastertinggi='';
        $lamarawatnaikkelass=0;

        foreach($datas as $item){

            $statusnaikkelas = '1';

            $kelastertinggi = $item->reportdisplay;
            $tglmasuk =date_format(date_create($item->tglmasuk),'Y-m-d');
            $tglkeluar =date_format(date_create($item->tglkeluar),'Y-m-d');
            if($tglmasuk==$tglkeluar){
                $lamarawatnaikkelas = '1';
            }else{
                $tglmasuk = strtotime($tglmasuk);
                $tglkeluar = strtotime($tglkeluar);
                $diff= $tglkeluar-$tglmasuk;
                $days = floor($diff / (3600 * 24));;
                $lamarawatnaikkelas = $days;

            }
            $lamarawatnaikkelass= $lamarawatnaikkelass + $lamarawatnaikkelas;
        }

            $data []= array(
                'nosep' => $items['nosep'],
                'norec_pd' => $items['noreg'] ,
                'deptid' => $items['deptid'] ,
                'statusnaikkelas' => $statusnaikkelas,
                'kelastertinggi' => $kelastertinggi,
                'lamarawatnaikkelas' => $lamarawatnaikkelass,
                'statusrawatintensiv' =>$statusrawatintensiv,
                'lamarawatintensiv' =>$lamarawatintensivaa,
            );
        }
        return $this->respond($data);

    }

    public function saveInformasiTanggungan(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request['proporsi'] as $item){
                $noregistrasifk = $item['noregistrasifk'];
                $totaldijamin = $item['totalDijamin'];
                $biayanaikkelas = $item['biayaNaikkelas'];

                $delete = DB::table('informasitanggungansementara_t')
                    ->where("noregistrasifk",$item['noregistrasifk'])
                    ->delete();

                $new = new InformasiTanggungan();
                $new->norec =  $new->generateNewId();
                $new->noregistrasifk = $noregistrasifk;
                $new->totalbiayars = $this->getTotalBiayaRS($noregistrasifk);
                $new->totalpiutangpenjamin = $totaldijamin;
                $new->biayanaikkelas = $biayanaikkelas;
                $new->save();
            }

            $transStatus = 'true';
            $transMessage = "Simpan Informasi Tanggungan";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Gagal  ";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function saveProposiBridgingINACBG(Request $request)
    {
        DB::beginTransaction();
        try {


            $noregistrasifk=$request['noregistrasifk'];
            $totaldijamin=$request['totalDijamin'];
            $biayanaikkelas=$request['biayaNaikkelas'];
            $b="";
            $c="";
            $d=[];
            $delete = DB::table('hasilgrouping_t')
                ->where("noregistrasifk",$request['noregistrasifk'])
                ->delete();



            $noregistrasifk_apd = AntrianPasiendiperiksa::where('noregistrasifk', $request['noregistrasifk'])->first();
            $noregistrasifk_apd=$noregistrasifk_apd['norec'];
            //     return($noregistrasifk_apd);
            //    die();
            $TotalBiayaRS = DB::select(DB::raw("select sum(pelayananpasien_t.hargasatuan) as TotalBiayaRS
            FROM pasiendaftar_t INNER JOIN antrianpasiendiperiksa_t ON pasiendaftar_t.norec = antrianpasiendiperiksa_t.noregistrasifk INNER JOIN pelayananpasien_t ON antrianpasiendiperiksa_t.norec = pelayananpasien_t.noregistrasifk
            where pasiendaftar_t.norec = '$noregistrasifk'")
            );

            foreach ($TotalBiayaRS as $item){
                $TotalBiayaRS = $item->TotalBiayaRS;

            }

            $TotalBiayaPerTindakan = DB::select(DB::raw("select pelayananpasien_t.norec, CASE WHEN hargasatuan <> 0 THEN hargasatuan * jumlah ELSE 0 END AS TotalBiayaPerTindakan, pelayananpasien_t.noregistrasifk, pelayananpasien_t.piutangpenjamin
            FROM pelayananpasien_t INNER JOIN antrianpasiendiperiksa_t ON pelayananpasien_t.noregistrasifk = antrianpasiendiperiksa_t.norec INNER JOIN pasiendaftar_t ON antrianpasiendiperiksa_t.noregistrasifk = pasiendaftar_t.norec
            where pasiendaftar_t.norec = '$noregistrasifk'")
            );
            //  return($TotalBiayaPerTindakan);
            //  die();
            foreach ($TotalBiayaPerTindakan as $item){
                if ($item->TotalBiayaPerTindakan<>0){

                    $piutangpenjamin = ($item->TotalBiayaPerTindakan / $TotalBiayaRS)*$totaldijamin;

                    $a=ceil($piutangpenjamin);

                    $dt=PelayananPasien::where('norec', $item->norec)
                        // ->where('statusenabled')
                        ->update([
                                'piutangpenjamin' => $a]
                        );
                }

                $TotalPiutangPenjamin = DB::select(DB::raw("select SUM(pelayananpasien_t.piutangpenjamin) AS totalpiutangpenjamin FROM pelayananpasien_t INNER JOIN antrianpasiendiperiksa_t ON pelayananpasien_t.noregistrasifk = antrianpasiendiperiksa_t.norec INNER JOIN pasiendaftar_t ON antrianpasiendiperiksa_t.noregistrasifk = pasiendaftar_t.norec
                where pasiendaftar_t.norec = '$noregistrasifk'")
                );
                foreach ($TotalPiutangPenjamin as $item){
                    $TotalPiutangPenjamin = $item->totalpiutangpenjamin;
                }
                $norecupdate = DB::select(DB::raw("select TOP (1) pelayananpasien_t.piutangpenjamin, pelayananpasien_t.norec FROM pelayananpasien_t INNER JOIN antrianpasiendiperiksa_t ON pelayananpasien_t.noregistrasifk = antrianpasiendiperiksa_t.norec INNER JOIN pasiendaftar_t ON antrianpasiendiperiksa_t.noregistrasifk = pasiendaftar_t.norec
                where pasiendaftar_t.norec = '$noregistrasifk' ORDER BY pelayananpasien_t.piutangpenjamin desc")
                );
                foreach ($norecupdate as $item){
                    $norecupdate = $item->norec;
                    $piutangpenjamintindakan = ceil($item->piutangpenjamin);
                }

                if($TotalPiutangPenjamin>$totaldijamin){
                    $d=$TotalPiutangPenjamin-$totaldijamin;
                    $piutangpenjamintindakan=$piutangpenjamintindakan-$d;

                    $dt=PelayananPasien::where('norec',$norecupdate)
                        ->update([
                            'piutangpenjamin' => $piutangpenjamintindakan
                        ]);
                }
                if($TotalPiutangPenjamin<$totaldijamin){
                    $d=$totaldijamin-$TotalPiutangPenjamin;
                    $piutangpenjamintindakan=$piutangpenjamintindakan+$d;

                    $dt=PelayananPasien::where('norec',$norecupdate)
                        ->update([
                            'piutangpenjamin' => $piutangpenjamintindakan
                        ]);
                }

                $transStatus = 'true';
                $transMessage = "Simpan Proposi Berhasil...! ";
            }
            $new = new HasilGrouping();
            $new->norec =  $new->generateNewId();
            $new->noregistrasifk = $noregistrasifk;
            $new->totalbiayars = $TotalBiayaRS;
            $new->totalpiutangpenjamin = $totaldijamin;
            $new->biayanaikkelas = $biayanaikkelas;
            // return($newRujukan);
            // die();
            $new->save();
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Gagal Simpan Data ...! ";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message" => $transMessage,
            );
        }

        // return 'sukses';
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
      public function getDaftarPasienRev(Request $request)
    {
        $kdProfile = (int)$this->getDataKdProfile($request);
        $data  = \DB::table('settingdatafixed_m')
            ->select('namafield','nilaifield')
            ->where('keteranganfungsi','inacbg')
            ->where('kdprofile', $kdProfile)
            ->get();
        foreach ($data as $item){
            if ($item->namafield == 'codernik'){
                $codernik = $item->nilaifield;
            }
            if ($item->namafield == 'key'){
                $key = $item->nilaifield;
            }
            if ($item->namafield == 'url'){
                $url = $item->nilaifield;
            }
            if ($item->namafield == 'kodetarif'){
                $kodetarif = $item->nilaifield;
            }
        }

        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->leftJoin('departemen_m as dept', 'dept.id', '=', 'ru.objectdepartemenfk')
//            ->join('antrianpasiendiperiksa_t as apd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->leftJoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pd.nostruklastfk')
            ->leftJoin('strukbuktipenerimaan_t as sbm', 'sbm.norec', '=', 'pd.nosbmlastfk')
            ->leftjoin('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftjoin('pegawai_m as pgs', 'pgs.id', '=', 'lu.objectpegawaifk')
            ->leftjoin('pemakaianasuransi_t as pas', 'pas.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('asuransipasien_m as asu', 'asu.id', '=', 'pas.objectasuransipasienfk')
            ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->leftjoin('kelas_m as kls2', 'kls2.id', '=', 'asu.objectkelasdijaminfk')
            ->leftjoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
            ->leftjoin('hasilgrouping_t as hg', 'hg.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('diagnosaberatbadanbayi_t as dbb', 'dbb.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
            ->select('pd.norec', 'pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ru.namaruangan', 'ps.namapasien', 'kp.kelompokpasien',
                'pd.tglpulang', 'pd.statuspasien', 'sp.nostruk', 'sbm.nosbm', 'pg.id as pgid', 'pg.namalengkap as namadokter',
                'pgs.namalengkap as kasir','pd.objectruanganlastfk as ruanganid','pas.nosep','pas.norec as norec_pa','br.norec as norec_br',
                'pas.nokepesertaan','ps.tgllahir','ps.objectjeniskelaminfk','dept.id as deptid','kls.nourut as nokelasdaftar','kls2.nourut as nokelasdijamin',
                'kls.reportdisplay as namakelasdaftar','kls2.reportdisplay as namakelas','pd.objectstatuspulangfk',
                'hg.biayanaikkelas','dbb.beratbadan','rk.id as idrekanan','hg.status as statusgrouping','ru.jenis as statuscovid','ps.noidentitas',
                DB::raw(" 'verifikasi'  as status, null as loscovid"),
                DB::raw("case when hg.totalpiutangpenjamin is null then 1 else hg.totalpiutangpenjamin end as totalpiutangpenjamin"))
            ->whereNull('br.norec')
            ->where('pd.kdprofile',$kdProfile)
            // ->where('pas.nosep','<>','')
            ->whereNotNull('pas.nosep')
            ->whereNotNull('pd.tglpulang');

//            ->where('apd.objectruanganasalfk',null);

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
            $data = $data->where('pd.noregistrasi', 'like', '%' . $filter['noreg'] . '%');
        }
        if (isset($filter['norm']) && $filter['norm'] != "" && $filter['norm'] != "undefined") {
            $data = $data->where('ps.nocm', 'like', '%' . $filter['norm'] . '%');
        }
        if (isset($filter['nama']) && $filter['nama'] != "" && $filter['nama'] != "undefined") {
            $data = $data->where('ps.namapasien', 'like', '%' . $filter['nama'] . '%');
        }
        if (isset($filter['nosep']) && $filter['nosep'] != "" && $filter['nosep'] != "undefined") {
            $data = $data->where('pas.nosep', '=', $filter['nosep']);
        }
//        if (isset($filter['jmlRows']) && $filter['jmlRows'] != "" && $filter['jmlRows'] != "undefined") {
//            $data = $data->take($filter['jmlRows']);
//        }
        $data = $data->orderBy('pd.noregistrasi');

        $data = $data->get();

        $i = 0 ;
        $dtdt = '';






        $dataDiagnosa = \DB::table('detaildiagnosapasien_t as dp')
            ->join('diagnosa_m as dg', 'dg.id', '=', 'dp.objectdiagnosafk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dp.noregistrasifk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('dg.kddiagnosa','apd.objectasalrujukanfk','pd.norec')
//            ->where('apd.noregistrasifk',$data[$i]->norec)
            ->wherein('dp.objectjenisdiagnosafk',array(1,2))
            ->where('pd.tglregistrasi', '>=', $filter['tglAwal'])
            ->where('pd.tglregistrasi', '<=', $filter['tglAkhir'])
            ->where('pd.kdprofile',$kdProfile)
            ->orderBy('dp.objectjenisdiagnosafk', 'asc')
            ->get();
        foreach ($data as $item){
            $dtdt = '';
            $asalRujukan = '';
            $covid19_status_cd = '';
//            $dataDiagnosa = \DB::table('detaildiagnosapasien_t as dp')
//                ->join('diagnosa_m as dg', 'dg.id', '=', 'dp.objectdiagnosafk')
//                ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dp.noregistrasifk')
//                ->select('dg.kddiagnosa','apd.objectasalrujukanfk')
//                ->where('apd.noregistrasifk',$data[$i]->norec)
//                ->get();
            foreach ($dataDiagnosa as $item2){
                if ($item2->norec == $data[$i]->norec){
                    $dtdt = $dtdt . '#' .  $item2->kddiagnosa;
                    $asalRujukan = $item2->objectasalrujukanfk;
                }
            }
            $data[$i]->icd10 = substr($dtdt,1,strlen($dtdt)-1);
            $data[$i]->codernik = $codernik;
            $data[$i]->objectasalrujukanfk = $asalRujukan;
            $data[$i]->kodetarif = $kodetarif;
            $i= $i + 1 ;
        }

        $i = 0 ;
        $dtdt = '';
        $dataICD9 = \DB::table('diagnosatindakanpasien_t as dpa')
            ->join('detaildiagnosatindakanpasien_t as dp', 'dpa.norec', '=', 'dp.objectdiagnosatindakanpasienfk')
            ->join('diagnosatindakan_m as dg', 'dg.id', '=', 'dp.objectdiagnosatindakanfk')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dpa.objectpasienfk')
            ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
            ->select('dg.kddiagnosatindakan','pd.norec')
//            ->where('apd.noregistrasifk',$data[$i]->norec)
            ->where('pd.tglregistrasi', '>=', $filter['tglAwal'])
            ->where('pd.tglregistrasi', '<=', $filter['tglAkhir'])
            ->where('pd.kdprofile',$kdProfile)
            ->get();
        foreach ($data as $item){
            $dtdt = '';
//            $dataICD9 = \DB::table('diagnosatindakanpasien_t as dpa')
//                ->join('detaildiagnosatindakanpasien_t as dp', 'dpa.norec', '=', 'dp.objectdiagnosatindakanpasienfk')
//                ->join('diagnosatindakan_m as dg', 'dg.id', '=', 'dp.objectdiagnosatindakanfk')
//                ->join('antrianpasiendiperiksa_t as apd', 'apd.norec', '=', 'dpa.objectpasienfk')
//                ->select('dg.kddiagnosatindakan')
//                ->where('apd.noregistrasifk',$data[$i]->norec)
//                ->get();
            foreach ($dataICD9 as $item2){
                if ($item2->norec == $data[$i]->norec) {
                    $dtdt = $dtdt . '#' . $item2->kddiagnosatindakan;
                }
            }
            $data[$i]->icd9 = substr($dtdt,1,strlen($dtdt)-1);
            $i= $i + 1 ;
        }

        $tglawalawal = $filter['tglAwal'];
        $tglakhirakhir = $filter['tglAkhir'];
        $kelompokPasien=$filter['kelId'];
        $dataTarif16 = DB::select(DB::raw("select pd.norec, sum(((pp.hargajual - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah)+ case when pp.jasa is null then 0 else pp.jasa end) as ttl,kpb.namaexternal
            from pasiendaftar_t as pd
            inner join pasien_m as ps on ps.id = pd.nocmfk
            INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
            INNER JOIN produk_m as pr on pr.id=pp.produkfk
            INNER JOIN kelompokprodukbpjs_m as kpb on kpb.id=pr.objectkelompokprodukbpjsfk
            left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
            left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
            where br.norec is null   
            and pd.kdprofile=$kdProfile
            and pd.tglregistrasi >= '$tglawalawal' and pd.tglregistrasi <= '$tglakhirakhir'  and kp.id in ($kelompokPasien)
            group  by pd.norec,kpb.namaexternal order by pd.norec")
        );
        $i = 0 ;
        $prosedur_non_bedah ='';
        $prosedur_bedah ='';
        $konsultasi ='';
        $tenaga_ahli ='';
        $keperawatan ='';
        $penunjang ='';
        $radiologi ='';
        $laboratorium ='';
        $pelayanan_darah ='';
        $rehabilitasi ='';
        $kamar ='';
        $rawat_intensif ='';
        $obat ='';
        $obat_kronis ='';
        $obat_kemoterapi ='';
        $alkes ='';
        $bmhp ='';
        $sewa_alat ='';
        foreach ($data as $item){
            $norecpd= $data[$i]->norec;
            foreach ($dataTarif16 as $itm){
                if ($itm->norec == $norecpd){
//                    $data[$i]->tarif_rs->($dataTarif16[0]->namaexternal) => (float)$dataTarif16[0]->ttl,
                    if ($itm->namaexternal == 'prosedur_non_bedah'){
                        $prosedur_non_bedah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'prosedur_bedah'){
                        $prosedur_bedah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'konsultasi'){
                        $konsultasi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'tenaga_ahli'){
                        $tenaga_ahli = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'keperawatan'){
                        $keperawatan = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'penunjang'){
                        $penunjang = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'radiologi'){
                        $radiologi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'laboratorium'){
                        $laboratorium = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'pelayanan_darah'){
                        $pelayanan_darah = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'rehabilitasi'){
                        $rehabilitasi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'kamar'){
                        $kamar = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'rawat_intensif'){
                        $rawat_intensif = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat'){
                        $obat = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat_kronis'){
                        $obat_kronis = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'obat_kemoterapi'){
                        $obat_kemoterapi = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'alkes'){
                        $alkes = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'bmhp'){
                        $bmhp = (float)$itm->ttl;
                    }
                    if ($itm->namaexternal == 'sewa_alat'){
                        $sewa_alat = (float)$itm->ttl;
                    }
                }
            }

            $datatatat = array(
                'prosedur_non_bedah' => (float)$prosedur_non_bedah,
                'prosedur_bedah' => (float)$prosedur_bedah,
                'konsultasi' => (float)$konsultasi,
                'tenaga_ahli' => (float)$tenaga_ahli,
                'keperawatan' => (float)$keperawatan,
                'penunjang' => (float)$penunjang,
                'radiologi' => (float)$radiologi,
                'laboratorium' => (float)$laboratorium,
                'pelayanan_darah' => (float)$pelayanan_darah,
                'rehabilitasi' => (float)$rehabilitasi,
                'kamar' => (float)$kamar,
                'rawat_intensif' => (float)$rawat_intensif,
                'obat' => (float)$obat,
                'obat_kronis' => (float)$obat_kronis,
                'obat_kemoterapi' => (float)$obat_kemoterapi,
                'alkes' => (float)$alkes,
                'bmhp' => (float)$bmhp,
                'sewa_alat' => (float)$sewa_alat,
            );
            $prosedur_non_bedah =0;
            $prosedur_bedah =0;
            $konsultasi =0;
            $tenaga_ahli =0;
            $keperawatan =0;
            $penunjang =0;
            $radiologi =0;
            $laboratorium =0;
            $pelayanan_darah =0;
            $rehabilitasi =0;
            $kamar =0;
            $rawat_intensif =0;
            $obat =0;
            $obat_kronis =0;
            $obat_kemoterapi =0;
            $alkes =0;
            $bmhp =0;
            $sewa_alat =0;
            $data[$i]->tarif_rs = $datatatat;

            $i= $i + 1 ;
//            $dataTarif16 = DB::select(DB::raw("select kpb.id,kpb.namaexternal,sum(x.total) as ttl
//                from kelompokprodukbpjs_m kpb
//                left JOIN
//                (select (pp.hargajual - pp.hargadiscount) * pp.jumlah as total ,pr.objectkelompokprodukbpjsfk
//                from produk_m as pr
//                INNER JOIN pelayananpasien_t as pp on pr.id=pp.produkfk
//                INNER JOIN antrianpasiendiperiksa_t as apd on pp.noregistrasifk=apd.norec
//                INNER JOIN pasiendaftar_t as pd on apd.noregistrasifk=pd.norec
//                where pd.norec='$norecpd' ) as x
//                on x.objectkelompokprodukbpjsfk=kpb.id
//                where kpb.id <> 0
//                group by kpb.id,kpb.namaexternal
//                order by kpb.id")
//            );



        }

//        return $this->respond(array(
//            'data1' => $dataTarif16,
//            'data2' =>$data,
//        ));
        return $this->respond($data);
    }

}