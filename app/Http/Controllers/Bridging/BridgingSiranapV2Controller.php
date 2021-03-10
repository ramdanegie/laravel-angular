<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 07/02/2019
 * Time: 16.33
 */


namespace App\Http\Controllers\Bridging;

use App\Http\Controllers\ApiController;
use App\Master\Ruangan;
use App\Traits\Valet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Date;
class BridgingSiranapV2Controller  extends ApiController
{
    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }
    protected  function kodeRS (){
        return $this->settingDataFixed('userIdSisrute');
    }
    protected  function passwordRS (){
        return $this->settingDataFixed('passwordSisrute');
    }

    // protected $kodeRS = 3174260;
    // protected $passwordRS = "12345";

    function is_decimal( $val )
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }
    public function getBedMonitor(Request $request)
    {

        $kdProfile = $this->getDataKdProfile($request);
        if($kdProfile==16){
            $user = '3673004';//banten
            $password ='12345';//
        }else if($kdProfile==17){
            $user = '2171066S';//galang
            $password ='12345';//
        }else if($kdProfile==18){
            $user = '3171900S'; //wisma
            $password ='12345';//
        }


        $data = collect(DB::select("
             select sum(x.terpakaimale)+ sum(x.terpakaifemale) + sum(x.kosong) as total ,sum(x.terpakaimale) as terpakaimale,
                    sum(x.terpakaifemale) as terpakaifemale,
                    sum(x.kosong) as kosong
                    from (
                    SELECT 
                   -- DISTINCT 
                    case when sb.id in (2,9,6,1 ) then 1 else 0 end as jml,
                    tt. ID AS tt_id,tt.nomorbed AS namabed,
                    kmr. ID AS kmr_id,kmr.namakamar,ru. ID AS id_ruangan,pd.jkid,
                    case when pd.jkid = 1 then 1 else 0 end as terpakaimale,
                    case when pd.jkid = 2 then 1 else 0 end as terpakaifemale,
                    case when pd.jkid is null and  sb.id in (2,9,6)  then 1 else 0 end as kosong
                    FROM
                    tempattidur_m AS tt
                    INNER JOIN statusbed_m AS sb ON sb. ID = tt.objectstatusbedfk
                    INNER JOIN kamar_m AS kmr ON kmr. ID = tt.objectkamarfk
                    INNER JOIN ruangan_m AS ru ON ru. ID = kmr.objectruanganfk
                    left join (select * from (
                            select   row_number() over (partition by pd.noregistrasi 
                            order by apd.tglmasuk desc) as rownum ,ps.nocm,
                            ps.objectjeniskelaminfk AS jkid,ps.tgllahir,ps.namapasien,
                            pd.tglregistrasi,
                            EXTRACT (YEAR FROM
                            age(CURRENT_DATE, ps.tgllahir)
                            ) :: INT AS umur,   DATE_PART(
                            'day',
                            now() - pd.tglregistrasi
                            ) AS lamarawat,pd.noregistrasi,
                            ps.nohp,apd.objectruanganfk,apd.objectkamarfk,apd.nobed
                            from pasiendaftar_t as pd 
                            join antrianpasiendiperiksa_t as apd on pd.norec =apd.noregistrasifk
                            and apd.objectruanganfk=pd.objectruanganlastfk
                            and apd.tglkeluar is null
                            join pasien_m as ps on ps.id=pd.nocmfk
                            where pd.tglpulang is null 
                            and pd.statusenabled=TRUE
                            and pd.kdprofile=$kdProfile
                            )as x where x.rownum=1
                    ) as pd on pd.objectruanganfk=ru.id 
                    and pd.objectkamarfk = kmr.id
                    and pd.nobed=tt.id
                    WHERE
                    tt.kdprofile =$kdProfile
                    and sb.id in (2,9,6,1)
                    AND tt.statusenabled = TRUE
                    AND kmr.statusenabled = TRUE
                    ) as x
           
            "))->first();
        // return $this->respond($data);
        $kosongmale = 0;
        $kosongfemale = 0;
        $totakhir = $data->kosong /2;
        if($this->is_decimal($totakhir)) {
            $kosongmale = $totakhir;
            $kosongfemale = $totakhir;
            $whole = floor($totakhir);      // 1
            $fraction = $totakhir - $whole; // .25
            $kosongmale = $totakhir - $fraction;
            $kosongfemale = ($whole + ($fraction * 2)) ;
        }else{
            $kosongmale = $totakhir;
            $kosongfemale = $totakhir;
        }
        $arr []= array(
            'kode_ruang' => '0059',
            'tipe_pasien' => '0011',
            'terpakaiMale' => $data->terpakaimale,
            'terpakaiFemale' => $data->terpakaifemale,
            'kosongMale' => $kosongmale,
            'kosongFemale' => $kosongfemale,
            'waiting' => 0,
            'tgl_update' => date('Y-m-d H:i:s'),
        );
        $date =date('Y-m-d H:i:s');
        $ttl = $data->total;
        $terpakaimale =$data->terpakaimale;
        $terpakaifemale =$data->terpakaifemale;
        $str ='<xml>
            <data>
                <kode_ruang>0011</kode_ruang>
                <tipe_pasien>0059</tipe_pasien>
                <total_TT>'.$ttl.'</total_TT>
                <terpakai_male>'.$terpakaimale.'</terpakai_male>
                <terpakai_female>'.$terpakaifemale.'</terpakai_female>
                <kosong_male>'.$kosongmale.'</kosong_male>
                <kosong_female>'.$kosongfemale.'</kosong_female>
                <waiting>0</waiting>
                <tgl_update>'.$date.'</tgl_update>
                </data>
        </xml>';
        // $str = ''
//         return $str;
        $passEn =  md5($password);
        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        $curl = curl_init();

        $dataJsonSend =$str;// json_encode ($arr);
        // $resss = array(
        //     'X-rs-id' => $user,
        //     'X-pass' => $passEn,
        //     'data' => $arr,
        // );
        // return $this->respond(   $resss);
        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  "http://sirs.yankes.kemkes.go.id/sirsservice/ranap",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/xml;",
                "X-rs-id: $user",
                "X-pass: $passEn" ,
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result =  json_decode($response);
        }
        // return $response;
        return $this->respond($result );

    }
    private function getTotalBed (){
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
                 where ru.objectdepartemenfk in (16,35) and ru.statusenabled='t'
				 and km.statusenabled='t' and tt.statusenabled='t')as x limit 1"));
        return $data;
    }
    public function hapusBed($kode_tipe_pasien,$kode_kelas_ruang)
    {
        # seting koneksi webservices #
        $xrsid = $this->kodeRS; # ID Rumah Sakit #
        $xpass = md5( $this->passwordRS ); # Password #

        $strURLSiranap = "http://sirs.yankes.kemkes.go.id/sirsservice/sisrute/hapusdata/$xrsid/$kode_tipe_pasien/$kode_kelas_ruang";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $strURLSiranap);
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                "X-rs-id: $xrsid",
                "X-pass:$xpass",
                "Content-Type:application/xml; charset=ISO-8859-1",
                "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")
        );
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "Ada Kesalahan #:" . $err;
        } else {
            $result = (array) json_decode($response);
        }
        return $this->respond($result);
    }
    public function getKunjungan($instalasi, Request $request)
    {
        $tgl = $request['tanggal'];
        $result = [];

        if($instalasi == 'irj'){
            $data = DB::select(DB::raw(" select x.namaruangan,	sum(x.jkn)AS jkn,
				sum(x.nonjkn)AS nonjkn
				from 
				(
				select ru.namaruangan,case when pd.objectkelompokpasienlastfk in(2,4,10) then 1 else 0 end as jkn
				,case when pd.objectkelompokpasienlastfk not in (2,4,10) then 1 else 0 end as nonjkn,
				apd.norec 
				from  antrianpasiendiperiksa_t as apd 
				join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
				join ruangan_m as ru on ru.id =apd.objectruanganfk
				where format(apd.tglregistrasi , 'dd-MM-yyyy') = '$tgl'
				and ru.objectdepartemenfk =18
				and pd.statusenabled=1
				) as x GROUP BY x.namaruangan
				order by x.namaruangan
					"));
            foreach ($data as $item){
                $result [] = array(
                    'KLINIK' => $item->namaruangan,
                    'JKN' => (int) $item->jkn,
                    'NON JKN' =>  (int)  $item->nonjkn,
                );
            }
        }
        if($instalasi == 'igd'){
            $data = DB::select(DB::raw("select x.namaruangan,	sum(x.jkn)AS jkn,
				sum(x.nonjkn)AS nonjkn
				from 
				(
				select ru.namaruangan,case when pd.objectkelompokpasienlastfk in(2,4,10) then 1 else 0 end as jkn
				,case when pd.objectkelompokpasienlastfk not in (2,4,10) then 1 else 0 end as nonjkn,
				apd.norec 
				from  antrianpasiendiperiksa_t as apd 
				join pasiendaftar_t as pd on pd.norec=apd.noregistrasifk
				join ruangan_m as ru on ru.id =apd.objectruanganfk
				where format(apd.tglregistrasi , 'dd-MM-yyyy') = '$tgl'
				and ru.objectdepartemenfk =24
				and pd.statusenabled=1
				) as x GROUP BY x.namaruangan
				order by x.namaruangan
					"));
            if(count($data) > 0){
                foreach ($data as $item){
                    $result  = array(
                        'JKN' => (int) $item->jkn,
                        'NON JKN' =>  (int)  $item->nonjkn,
                    );
                }
            }else{
                $result  = array(
                    'JKN' => 0,
                    'NON JKN' => 0,
                );
            }

        }
        if($instalasi == 'iri'){
            $tglAwal = $tgl.' 00:00';
            $tglAkhir = $tgl.' 23:59';
            $data = DB::select(DB::raw("select kls.namasiranap,count(pd.norec) as jml 
				from pasiendaftar_t as pd 
				join ruangan_m as ru on ru.id =pd.objectruanganlastfk
				join kelas_m as kls on kls.id =pd.objectkelasfk
				where format(pd.tglregistrasi,'dd-MM-yyyy') = '$tgl'
				  and (   format(pd.tglregistrasi,'dd-MM-yyyy HH:mm') < '$tglAwal'
				   AND  format(pd.tglpulang,'dd-MM-yyyy HH:mm') >= '$tglAkhir' 
	             )

	            or pd.tglpulang is null
	            and pd.statusenabled = 1
				and ru.objectdepartemenfk =16
				group by kls.namasiranap
					"));
            foreach ($data as $item){
                $result [] = array(
                    'CONTENT' =>  $item->namasiranap,
                    'JLH' =>  (int)  $item->jml,
                );
            }
        }
        return $this->respond($result);
    }

    public function get($instalasi, Request $request)
    {
        $tgl = $request['tanggal'];
        $result = [];

        if($instalasi == 'irj'){
            $data = DB::select(DB::raw("select x.namaruangan,	count(*) FILTER (WHERE x.objectkelompokpasienlastfk = 2 )   AS jkn,
					count(*) FILTER (WHERE x.objectkelompokpasienlastfk <> 2 )  AS nonjkn
					from 
					(
					select ru.namaruangan,pd.objectkelompokpasienlastfk,apd.norec 
					from pasiendaftar_t as pd 
					join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
					join ruangan_m as ru on ru.id =apd.objectruanganfk
					where to_char(apd.tglregistrasi , 'DD-MM-YYYY') = '$tgl'
					and ru.objectdepartemenfk in(18,28,26,34,30)
					order by ru.namaruangan
					) as x GROUP BY x.namaruangan
					order by x.namaruangan
					"));
            foreach ($data as $item){
                $result [] = array(
                    'KLINIK' => $item->namaruangan,
                    'JKN' => (int) $item->jkn,
                    'NON JKN' =>  (int)  $item->nonjkn,
                );
            }
        }
        if($instalasi == 'igd'){
            $data = DB::select(DB::raw("select count(*) FILTER (WHERE x.objectkelompokpasienlastfk = 2 )   AS jkn,
					count(*) FILTER (WHERE x.objectkelompokpasienlastfk <> 2 )  AS nonjkn
					from 
					(
					select ru.namaruangan,pd.objectkelompokpasienlastfk,apd.norec 
					from pasiendaftar_t as pd 
					join antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
					join ruangan_m as ru on ru.id =apd.objectruanganfk
					where to_char(apd.tglregistrasi , 'DD-MM-YYYY') = '$tgl'
					and ru.objectdepartemenfk =24
					order by ru.namaruangan
					) as x 
					"));
            foreach ($data as $item){
                $result  = array(
                    'JKN' => (int) $item->jkn,
                    'NON JKN' =>  (int)  $item->nonjkn,
                );
            }
        }
        if($instalasi == 'iri'){
            $data = DB::select(DB::raw("select kls.namakelas,count(pd.norec) as jml 
				from pasiendaftar_t as pd 
				join ruangan_m as ru on ru.id =pd.objectruanganlastfk
				join kelas_m as kls on kls.id =pd.objectkelasfk
				where to_char(pd.tglregistrasi, 'DD-MM-YYYY') = '$tgl'
				and ru.objectdepartemenfk =16
				group by kls.namakelas
					"));
            foreach ($data as $item){
                $result [] = array(
                    'CONTENT' =>  $item->namakelas,
                    'JLH' =>  (int)  $item->jml,
                );
            }
        }
        return $this->respond($result);
    }
    public function getDiagnosaRajal(Request $request)
    {
        $yM = $request['bulan'];
        if (isset($yM)){
            $date = explode('-',$yM);
            $lengthBln = strlen($date[0]);
            $bln = $date[0];
            $thn = $date[1];
            if($lengthBln == 1){
                $bln = '0'.$date[0];
            }
            $blnThn = $bln.'-'.$thn;
        }
//		return $blnThn;
        $data10 = [];
        $dataDiagnosa = DB::select(DB::raw("
		select x.kddiagnosa, count(x.kddiagnosa)as jumlah,x.tgldiagnosa from (
			select dm.kddiagnosa, 
			 dm.namadiagnosa, 
			 dp.norec, 
			 format(ddp.tglinputdiagnosa, 
			'dd-MM-yyyy') as tgldiagnosa
			 from antrianpasiendiperiksa_t as app
			 inner join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
			 inner join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
			 inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
			 inner join ruangan_m as ru on ru.id = app.objectruanganfk
			 where ru.objectdepartemenfk in (18,28,26,34,30) and format(ddp.tglinputdiagnosa, 
			'MM-yyyy') = '$blnThn'
			and dm.kddiagnosa not in ('-')

			) as x
			group by x.kddiagnosa,x.tgldiagnosa
			order by x.kddiagnosa
			"));
        foreach ($dataDiagnosa as $item) {
            $data10[] = array(
                'ID_DIAG' => $item->kddiagnosa,
                'JUMLAH_KASUS' => $item->jumlah,
                'TANGGAL' => $item->tgldiagnosa,
            );
        }
        if(count($data10 )!= 0){
            foreach ($data10 as $key => $row) {
                $count[$key] = $row['ID_DIAG'];
            }

            array_multisort($count, SORT_ASC, $data10);
        }



        return $this->respond($data10);

    }
    public function getDiagnosaRanap(Request $request)
    {
        $yM = $request['bulan'];
        if (isset($yM)){
            $date = explode('-',$yM);
            $lengthBln = strlen($date[0]);
            $bln = $date[0];
            $thn = $date[1];
            if($lengthBln == 1){
                $bln = '0'.$date[0];
            }
            $blnThn = $bln.'-'.$thn;
        }
//		return $blnThn;
        $dataDiagnosa = DB::select(DB::raw("
		select x.kddiagnosa, count(x.kddiagnosa)as jumlah,x.tgldiagnosa from (
			select dm.kddiagnosa, 
			 dm.namadiagnosa, 
			 dp.norec, 
			 format(ddp.tglinputdiagnosa, 
			'dd-MM-yyyy') as tgldiagnosa
			 from antrianpasiendiperiksa_t as app
			 inner join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
			 inner join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
			 inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
			 inner join ruangan_m as ru on ru.id = app.objectruanganfk
			 where ru.objectdepartemenfk in (16,17,35,26) and format(ddp.tglinputdiagnosa, 
			'MM-yyyy') = '$blnThn'
			and dm.kddiagnosa not in ('-') and
			dm.kddiagnosa !=''
			) as x
			group by x.kddiagnosa,x.tgldiagnosa
			order by x.kddiagnosa
			"));
        foreach ($dataDiagnosa as $item) {
            $data10[] = array(
                'ID_DIAG' => $item->kddiagnosa,
                'JUMLAH_KASUS' => $item->jumlah,
                'TANGGAL' => $item->tgldiagnosa,
            );
        }

        foreach ($data10 as $key => $row) {
            $count[$key] = $row['ID_DIAG'];
        }

        array_multisort($count, SORT_ASC, $data10);


        return $this->respond($data10);

    }
    public function getdataBOR(Request $request)
    {
        $yM = $request['bulan'];
        if (isset($yM)){
            $date = explode('-',$yM);
            $lengthBln = strlen($date[0]);
            $bln = $date[0];
            $blnKode = (int)$date[0];
            $thn = $date[1];
            if($lengthBln == 1){
                $bln = '0'.$date[0];
            }
            $blnThn = $bln.'-'.$thn;
        }
        $jmlHari = cal_days_in_month(CAL_GREGORIAN,$bln,$thn);

        $dateStart = Carbon::now();
        $dayInMonth = array();
        $type = CAL_GREGORIAN;

        $month =$bln;// Carbon::parse($blnKode)->format('m'); // Month ID, 1 through to 12.

        $year =$thn;// Carbon::parse($blnKode)->format('Y'); //date('Y'); // Year in 4 digit 2009 format.
        $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days\
        // return $day_count ;
        $hp = 0;
        for ($i = 1; $i <= $day_count; $i++) {
            $date = $year.'/'.$month.'/'.$i; //format date
            $get_name = date('l', strtotime($date)); //get week day
            $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

            //if not a weekend add day to array
//            if($day_name != 'Sun' && $day_name != 'Sat'){
            $strLength= strlen($i);
            if($strLength  == 1){
                $i = '0'.$i;
            }

            $dayInMonth[] =$thn.'-'.$month.'-'.$i;// date ('Y-'.$month.'-'.$i);

            $tglAwal = $thn.'-'.$month.'-'.$i.' 00:00';
            $tglAkhir = $thn.'-'.$month.'-'.$i.' 23:59';

            $pasien = DB::select(DB::raw("
		     
                    SELECT
                        pd.noregistrasi,
                        pd.tglregistrasi,
                        pd.tglpulang
                    FROM
                        pasiendaftar_t AS pd
                    INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                    WHERE
                    ru.objectdepartemenfk = 16  and(
                     pd.tglregistrasi  < '$tglAwal' 
                    and pd.tglpulang  >= '$tglAkhir' 
                    )
                 OR (pd.tglpulang IS NULL and pd.tglregistrasi < '$tglAkhir') 
                    and pd.statusenabled = 1 "
            ));
            $hp =   $hp + count($pasien);

        }

        $data10=[];
        $jumlahTT= 0;
        $jmlHariPerawatan = 0;
        $dataTT =  DB::select(DB::raw("SELECT
				COUNT (x.id) AS jml
			FROM
				(
					SELECT
						tt.id,
						ru.id AS idruangan,
						ru.namaruangan,
						km.id AS idkamar,
						km.namakamar,
						kl.id AS idkelas,
						kl.namakelas
					FROM
						tempattidur_m AS tt
					LEFT JOIN kamar_m AS km ON km.id = tt.objectkamarfk
					LEFT JOIN ruangan_m AS ru ON ru.id = km.objectruanganfk
					LEFT JOIN kelas_m AS kl ON kl.id = km.objectkelasfk
					WHERE
						ru.objectdepartemenfk IN (16, 35)
					AND ru.statusenabled = 1
					AND km.statusenabled = 1
					AND tt.statusenabled = 1
				) AS x"));
        if(count($dataTT) > 0){
            $jumlahTT = $dataTT[0]->jml;
        }

        /** @var  $bor = (Jumlah hari perawatn RS dibagi ( jumlah TT x Jumlah hari dalam satu periode ) ) x 100 % */

        $bor = ( $hp / ((float)$jumlahTT  * (float)$day_count)) * 100;
        $data10['BOR'] = (float) number_format($bor,2);

        return $this->respond($data10);


    }

    function getHeadersCovid($kdProfile){
        if($kdProfile==16){
            $user = '3673004';//banten
            $password ='S!rs2020!!';//
        }else if($kdProfile==17){
            $user = '2171066S';//galang
            $password ='S1Rs2020!!';//
        }else if($kdProfile==18){
            $user = '3171900S'; //wisma
            $password ='S1Rs2020!!';//
        }else if($kdProfile==21){
            $user = '3201046'; //wisma
            $password ='S!rs2020!!';//
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        $header = [
            "X-rs-id: ".$user,
            "X-Timestamp: ".$tStamp,
            "X-pass: ".$password,
            "Content-type: application/json",
        ];
        return $header;
    }

    public function PasienMasuk($method,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        if($method == 'post'){
            $now = date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
                    $query = collect(DB::select("SELECT sum(x.igd_confirm_p) as igd_confirm_p,
        sum(x.igd_confirm_l)as igd_confirm_l,
        sum(x.rj_confirm_l) as rj_confirm_l,
        sum(x.rj_confirm_p) as rj_confirm_p,
        sum(x.ri_confirm_l) as ri_confirm_l,
        sum(x.ri_confirm_p) as ri_confirm_p,
        sum(x.igd_suspect_p) as igd_suspect_p,
        sum(x.igd_suspect_l)as igd_suspect_l,
        sum(x.rj_suspect_l) as rj_suspect_l,
        sum(x.rj_suspect_p) as rj_suspect_p,
        sum(x.ri_suspect_l) as ri_suspect_l,
        sum(x.ri_suspect_p) as ri_suspect_p 
        FROM(select pd.noregistrasi,dg.kddiagnosa, case when dg.kddiagnosa= 'B34.2' and ru.objectdepartemenfk=24 and ps.objectjeniskelaminfk =1  then 1 else 0 end as igd_confirm_l,
            case when dg.kddiagnosa= 'B34.2'  and ru.objectdepartemenfk=24 and ps.objectjeniskelaminfk =2  then 1 else 0 end as igd_confirm_p,
            case when dg.kddiagnosa= 'B34.2' and ru.objectdepartemenfk in(16,35) and ps.objectjeniskelaminfk =1  then 1 else 0 end as ri_confirm_l,
            case when dg.kddiagnosa= 'B34.2' and ru.objectdepartemenfk in(16,35)  and ps.objectjeniskelaminfk =2  then 1 else 0 end as ri_confirm_p,
            case when dg.kddiagnosa= 'B34.2' and ru.objectdepartemenfk=18 and ps.objectjeniskelaminfk =1  then 1 else 0 end as rj_confirm_l,
            case when dg.kddiagnosa= 'B34.2' and ru.objectdepartemenfk=18 and ps.objectjeniskelaminfk =2  then 1 else 0 end as rj_confirm_p,

        case when dg.kddiagnosa= 'Z03.8' and ru.objectdepartemenfk=24 and ps.objectjeniskelaminfk =1  then 1 else 0 end as igd_suspect_l,
            case when dg.kddiagnosa= 'Z03.8'  and ru.objectdepartemenfk=24 and ps.objectjeniskelaminfk =2  then 1 else 0 end as igd_suspect_p,
            case when dg.kddiagnosa= 'Z03.8' and ru.objectdepartemenfk in(16,35) and ps.objectjeniskelaminfk =1  then 1 else 0 end as ri_suspect_l,
            case when dg.kddiagnosa= 'Z03.8'  and ru.objectdepartemenfk in(16,35)  and ps.objectjeniskelaminfk =2  then 1 else 0 end as ri_suspect_p,
            case when dg.kddiagnosa= 'Z03.8'  and ru.objectdepartemenfk=18 and ps.objectjeniskelaminfk =1  then 1 else 0 end as rj_suspect_l,
            case when dg.kddiagnosa= 'Z03.8'  and ru.objectdepartemenfk=18 and ps.objectjeniskelaminfk =2  then 1 else 0 end as rj_suspect_p,
        row_number() over (partition by pd.noregistrasi order by ddg.tglinputdiagnosa ASC) as rownum 
                
                FROM
                                
                pasiendaftar_t as pd
                                inner JOIN antrianpasiendiperiksa_t as apd ON apd.noregistrasifk = pd.norec
                                INNER JOIN diagnosapasien_t as dgp ON dgp.noregistrasifk = apd.norec
                                INNER JOIN detaildiagnosapasien_t as ddg ON ddg.objectdiagnosapasienfk = dgp.norec
                                INNER JOIN diagnosa_m as dg ON dg.id = ddg.objectdiagnosafk
                inner join ruangan_m as ru on ru.id=apd.objectruanganfk
                                inner join pasien_m as ps on ps.id=pd.nocmfk
                WHERE
                pd.kdprofile=$kdProfile
                and pd.statusenabled =true
                                and ddg.objectjenisdiagnosafk = '1'
                                and ddg.kdprofile = $kdProfile
                and pd.tglregistrasi BETWEEN'$now 00:00'
                AND '$now 23:59'  ) AS X
WHERE X.rownum = 1"))->first();
            $methods ='POST';
            $arr = array(
                "tanggal" =>  $now,
                "igd_suspect_l"=> isset($query->igd_suspect_l) && $query->igd_suspect_l != null ? $query->igd_suspect_l : 0,
                "igd_suspect_p"=>isset($query->igd_suspect_p) && $query->igd_suspect_p != null ? $query->igd_suspect_p : 0,
                "igd_confirm_l"=> isset($query->igd_confirm_l) && $query->igd_confirm_l != null ? $query->igd_confirm_l : 0,
                "igd_confirm_p"=>isset($query->igd_confirm_p) && $query->igd_confirm_p != null ? $query->igd_confirm_p : 0,
                "rj_suspect_l"=> isset($query->rj_suspect_l) && $query->rj_suspect_l != null ? $query->rj_suspect_l : 0,
                "rj_suspect_p"=> isset($query->rj_suspect_p) && $query->rj_suspect_p != null ? $query->rj_suspect_p : 0,
                "rj_confirm_l"=>isset($query->rj_confirm_l) && $query->rj_confirm_l != null ? $query->rj_confirm_l : 0,
                "rj_confirm_p"=>isset($query->rj_confirm_p) && $query->rj_confirm_p != null ? $query->rj_confirm_p : 0,
                "ri_suspect_l"=>isset($query->ri_suspect_l) && $query->ri_suspect_l != null ? $query->ri_suspect_l : 0,
                "ri_suspect_p"=> isset($query->ri_suspect_p) && $query->ri_suspect_p != null ? $query->ri_suspect_p : 0,
                "ri_confirm_l"=>isset($query->ri_confirm_l) && $query->ri_confirm_l != null ? $query->ri_confirm_l : 0,
                "ri_confirm_p"=> isset($query->ri_confirm_p) && $query->ri_confirm_p != null ? $query->ri_confirm_p : 0,
            );
//                        return $this->respond($arr);
            $dataJsonSend = json_encode ($arr);
        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        }else if($method == 'delete'){
            $methods ='DELETE';
            $now = date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $arr = array(
                "tanggal" => $now,
            );
            $dataJsonSend = json_encode ($arr);
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/LapV2/PasienMasuk";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url, $methods);
        return $this->respond($response);
    }

    public function Komorbid($method,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        if($method == 'post'){
            $now = date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $query = collect(DB::select("SELECT sum(x.icu_dengan_ventilator_confirm_p) as icu_dengan_ventilator_confirm_p,
            sum(x.icu_dengan_ventilator_confirm_l)as icu_dengan_ventilator_confirm_l,
            sum(x.icu_dengan_ventilator_suspect_l) as icu_dengan_ventilator_suspect_l,
            sum(x.icu_dengan_ventilator_suspect_p)as icu_dengan_ventilator_suspect_p,
            sum(x.isolasi_tekanan_negatif_suspect_l) as isolasi_tekanan_negatif_suspect_l   ,
            sum(x.isolasi_tekanan_negatif_suspect_p) as isolasi_tekanan_negatif_suspect_p,
            sum(x.isolasi_tekanan_negatif_confirm_l) as isolasi_tekanan_negatif_confirm_l,
            sum(x.isolasi_tekanan_negatif_confirm_p) as isolasi_tekanan_negatif_confirm_p,
            sum(x.nicu_khusus_covid_suspect_l)as nicu_khusus_covid_suspect_l,
            sum(x.nicu_khusus_covid_suspect_p) as nicu_khusus_covid_suspect_p,
            sum(x.nicu_khusus_covid_confirm_l) as nicu_khusus_covid_confirm_l,
            sum(x.nicu_khusus_covid_confirm_p) as nicu_khusus_covid_confirm_p,
            sum(x.picu_khusus_covid_suspect_l) as picu_khusus_covid_suspect_l,
            sum(x.picu_khusus_covid_suspect_p) as picu_khusus_covid_suspect_p,
            sum(x.picu_khusus_covid_confirm_l) as picu_khusus_covid_confirm_l,
            sum(x.picu_khusus_covid_confirm_p) as picu_khusus_covid_confirm_p
            FROM(select case when dg.kddiagnosa= 'B34.2' and ru.id IN(654,655) and ps.objectjeniskelaminfk =1  then 1 else 0 end as isolasi_tekanan_negatif_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id IN(654,655) and ps.objectjeniskelaminfk =2  then 1 else 0 end as isolasi_tekanan_negatif_confirm_p,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(671) and ps.objectjeniskelaminfk =1  then 1 else 0 end as icu_dengan_ventilator_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(671)  and ps.objectjeniskelaminfk =2  then 1 else 0 end as icu_dengan_ventilator_confirm_p,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(673) and ps.objectjeniskelaminfk =1  then 1 else 0 end as nicu_khusus_covid_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(673)and ps.objectjeniskelaminfk =2  then 1 else 0 end as nicu_khusus_covid_confirm_p,
                case when dg.kddiagnosa= 'B34.2' and ru.id=672 and ps.objectjeniskelaminfk =1  then 1 else 0 end as picu_khusus_covid_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id=672 and ps.objectjeniskelaminfk =2  then 1 else 0 end as picu_khusus_covid_confirm_p,
                    
            case when dg.kddiagnosa= 'Z03.8' and ru.id IN(654,655) and ps.objectjeniskelaminfk =1  then 1 else 0 end as isolasi_tekanan_negatif_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id IN(654,655) and ps.objectjeniskelaminfk =2  then 1 else 0 end as isolasi_tekanan_negatif_suspect_p,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(671) and ps.objectjeniskelaminfk =1  then 1 else 0 end as icu_dengan_ventilator_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(671)  and ps.objectjeniskelaminfk =2  then 1 else 0 end as icu_dengan_ventilator_suspect_p,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(673) and ps.objectjeniskelaminfk =1  then 1 else 0 end as nicu_khusus_covid_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(673)and ps.objectjeniskelaminfk =2  then 1 else 0 end as nicu_khusus_covid_suspect_p,
                case when dg.kddiagnosa= 'Z03.8' and ru.id=672 and ps.objectjeniskelaminfk =1  then 1 else 0 end as picu_khusus_covid_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id=672 and ps.objectjeniskelaminfk =2  then 1 else 0 end as picu_khusus_covid_suspect_p
            FROM 
            pasiendaftar_t as pd
                                inner JOIN antrianpasiendiperiksa_t as apd ON apd.noregistrasifk = pd.norec
                                INNER JOIN diagnosapasien_t as dgp ON dgp.noregistrasifk = apd.norec
                                INNER JOIN detaildiagnosapasien_t as ddg ON ddg.objectdiagnosapasienfk = dgp.norec
                                INNER JOIN diagnosa_m as dg ON dg.id = ddg.objectdiagnosafk
                inner join ruangan_m as ru on ru.id=pd.objectruanganlastfk
                                inner join pasien_m as ps on ps.id=pd.nocmfk
                WHERE
                pd.kdprofile=$kdProfile 
                                AND ddg.objectjenisdiagnosafk IN (1,5)
                                AND apd.norec IN (SELECT dip.noregistrasifk FROM diagnosapasien_t AS dip 
                                                                    INNER JOIN detaildiagnosapasien_t as ddip ON ddip.objectdiagnosapasienfk = dip.norec
                                                                    WHERE ddip.objectjenisdiagnosafk = 2)
                and pd.statusenabled =true
                                and ddg.kdprofile = $kdProfile
                and pd.tglpulang ISNULL ) as x
           "))->first();
            $methods ='POST';
            if($query->icu_dengan_ventilator_suspect_l== null )
            {$arr = array(
                "tanggal"=> $now,
                "icu_dengan_ventilator_suspect_l"=> "0",
                "icu_dengan_ventilator_suspect_p"=> "0",
                "icu_dengan_ventilator_confirm_l"=> "0",
                "icu_dengan_ventilator_confirm_p"=> "0",
                "icu_tanpa_ventilator_suspect_l"=> "0",
                "icu_tanpa_ventilator_suspect_p"=> "0",
                "icu_tanpa_ventilator_confirm_l"=> "0",
                "icu_tanpa_ventilator_confirm_p"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_p"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_p"=> "0",
                "isolasi_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tekanan_negatif_confirm_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                "nicu_khusus_covid_suspect_l"=> "0",
                "nicu_khusus_covid_suspect_p"=> "0",
                "nicu_khusus_covid_confirm_l"=> "0",
                "nicu_khusus_covid_confirm_p"=> "0",
                "picu_khusus_covid_suspect_l"=> "0",
                "picu_khusus_covid_suspect_p"=> "0",
                "picu_khusus_covid_confirm_l"=> "0",
                "picu_khusus_covid_confirm_p"=> "0", );
            }else {
                $arr = array(
                "tanggal"=> $now,
                "icu_dengan_ventilator_suspect_l"=> $query->icu_dengan_ventilator_suspect_l,
                "icu_dengan_ventilator_suspect_p"=> $query->icu_dengan_ventilator_suspect_p,
                "icu_dengan_ventilator_confirm_l"=> $query->icu_dengan_ventilator_confirm_l,
                "icu_dengan_ventilator_confirm_p"=> $query->icu_dengan_ventilator_confirm_p,
                "icu_tanpa_ventilator_suspect_l"=> "0",
                "icu_tanpa_ventilator_suspect_p"=> "0",
                "icu_tanpa_ventilator_confirm_l"=> "0",
                "icu_tanpa_ventilator_confirm_p"=> "0",
                // "icu_tanpa_ventilator_suspect_l"=> $query->icu_tanpa_ventilator_suspect_l,
                // "icu_tanpa_ventilator_suspect_p"=> $query->icu_tanpa_ventilator_suspect_p,
                // "icu_tanpa_ventilator_confirm_l"=> $query->icu_tanpa_ventilator_confirm_l,
                // "icu_tanpa_ventilator_confirm_p"=> $query->icu_tanpa_ventilator_confirm_p,
                "icu_tekanan_negatif_dengan_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_p"=> "0",
                // "icu_tekanan_negatif_dengan_ventilator_suspect_l"=> $query->icu_tekanan_negatif_dengan_ventilator_suspect_l,
                // "icu_tekanan_negatif_dengan_ventilator_suspect_p"=> $query->icu_tekanan_negatif_dengan_ventilator_suspect_p,
                // "icu_tekanan_negatif_dengan_ventilator_confirm_l"=> $query->icu_tekanan_negatif_dengan_ventilator_confirm_l,
                // "icu_tekanan_negatif_dengan_ventilator_confirm_p"=> $query->icu_tekanan_negatif_dengan_ventilator_confirm_p,
                "icu_tekanan_negatif_tanpa_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_p"=> "0",
                // "icu_tekanan_negatif_tanpa_ventilator_suspect_l"=> $query->icu_tekanan_negatif_tanpa_ventilator_suspect_l,
                // "icu_tekanan_negatif_tanpa_ventilator_suspect_p"=> $query->icu_tekanan_negatif_tanpa_ventilator_suspect_p,
                // "icu_tekanan_negatif_tanpa_ventilator_confirm_l"=> $query->icu_tekanan_negatif_tanpa_ventilator_confirm_l,
                // "icu_tekanan_negatif_tanpa_ventilator_confirm_p"=> $query->icu_tekanan_negatif_tanpa_ventilator_confirm_p,
                "isolasi_tekanan_negatif_suspect_l"=> $query->isolasi_tekanan_negatif_suspect_l,
                "isolasi_tekanan_negatif_suspect_p"=> $query->isolasi_tekanan_negatif_suspect_p,
                "isolasi_tekanan_negatif_confirm_l"=> $query->isolasi_tekanan_negatif_confirm_l,
                "isolasi_tekanan_negatif_confirm_p"=> $query->isolasi_tekanan_negatif_confirm_p,
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                // "isolasi_tanpa_tekanan_negatif_suspect_l"=> $query->isolasi_tanpa_tekanan_negatif_suspect_l,
                // "isolasi_tanpa_tekanan_negatif_suspect_p"=> $query->isolasi_tanpa_tekanan_negatif_suspect_p,
                // "isolasi_tanpa_tekanan_negatif_confirm_l"=> $query->isolasi_tanpa_tekanan_negatif_confirm_l,
                // "isolasi_tanpa_tekanan_negatif_confirm_p"=> $query->isolasi_tanpa_tekanan_negatif_confirm_p,
                // "isolasi_tanpa_tekanan_negatif_suspect_l"=> $query->isolasi_tanpa_tekanan_negatif_suspect_l,
                // "isolasi_tanpa_tekanan_negatif_suspect_p"=> $query->isolasi_tanpa_tekanan_negatif_suspect_p,
                // "isolasi_tanpa_tekanan_negatif_confirm_l"=> $query->isolasi_tanpa_tekanan_negatif_confirm_l,
                "nicu_khusus_covid_suspect_l"=> $query->nicu_khusus_covid_suspect_l,
                "nicu_khusus_covid_suspect_p"=> $query->nicu_khusus_covid_suspect_p,
                "nicu_khusus_covid_confirm_l"=> $query->nicu_khusus_covid_confirm_l,
                "nicu_khusus_covid_confirm_p"=> $query->nicu_khusus_covid_confirm_p,
                "picu_khusus_covid_suspect_l"=> $query->picu_khusus_covid_suspect_l,
                "picu_khusus_covid_suspect_p"=> $query->picu_khusus_covid_suspect_p,
                "picu_khusus_covid_confirm_l"=> $query->picu_khusus_covid_confirm_l,
                "picu_khusus_covid_confirm_p"=> $query->picu_khusus_covid_confirm_p, );

            }
            
           
//            return $this->respond($arr);
            $dataJsonSend = json_encode ($arr);
        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        }else if($method == 'delete'){
            $methods ='DELETE';
            $now =  date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $arr = array(
                "tanggal" =>$now ,
            );
            $dataJsonSend = json_encode ($arr);
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/LapV2/PasienDirawatKomorbid";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url ,$methods);
        return $this->respond($response);

    }
    public function NonKomorbid($method,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        if($method == 'post'){
            $now =  date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $query = collect(DB::select("SELECT sum(x.icu_dengan_ventilator_suspect_l) as icu_dengan_ventilator_suspect_l,
            sum(x.icu_dengan_ventilator_suspect_p)as icu_dengan_ventilator_suspect_p,
            sum(x.icu_dengan_ventilator_confirm_l) as icu_dengan_ventilator_confirm_l,
            sum(x.icu_dengan_ventilator_confirm_p)as icu_dengan_ventilator_confirm_p,
            sum(x.isolasi_tekanan_negatif_suspect_l) as isolasi_tekanan_negatif_suspect_l   ,
            sum(x.isolasi_tekanan_negatif_suspect_p) as isolasi_tekanan_negatif_suspect_p,
            sum(x.isolasi_tekanan_negatif_confirm_l) as isolasi_tekanan_negatif_confirm_l,
            sum(x.isolasi_tekanan_negatif_confirm_p) as isolasi_tekanan_negatif_confirm_p,
            sum(x.nicu_khusus_covid_suspect_l)as nicu_khusus_covid_suspect_l,
            sum(x.nicu_khusus_covid_suspect_p) as nicu_khusus_covid_suspect_p,
            sum(x.nicu_khusus_covid_confirm_l) as nicu_khusus_covid_confirm_l,
            sum(x.nicu_khusus_covid_confirm_p) as nicu_khusus_covid_confirm_p,
            sum(x.picu_khusus_covid_suspect_l) as picu_khusus_covid_suspect_l,
            sum(x.picu_khusus_covid_suspect_p) as picu_khusus_covid_suspect_p,
            sum(x.picu_khusus_covid_confirm_l) as picu_khusus_covid_confirm_l,
            sum(x.picu_khusus_covid_confirm_p) as picu_khusus_covid_confirm_p
            FROM(select case when dg.kddiagnosa= 'B34.2' and ru.id IN(654,655) and ps.objectjeniskelaminfk =1  then 1 else 0 end as isolasi_tekanan_negatif_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id IN(654,655) and ps.objectjeniskelaminfk =2  then 1 else 0 end as isolasi_tekanan_negatif_confirm_p,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(671) and ps.objectjeniskelaminfk =1  then 1 else 0 end as icu_dengan_ventilator_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(671)  and ps.objectjeniskelaminfk =2  then 1 else 0 end as icu_dengan_ventilator_confirm_p,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(673) and ps.objectjeniskelaminfk =1  then 1 else 0 end as nicu_khusus_covid_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id in(673)and ps.objectjeniskelaminfk =2  then 1 else 0 end as nicu_khusus_covid_confirm_p,
                case when dg.kddiagnosa= 'B34.2' and ru.id=672 and ps.objectjeniskelaminfk =1  then 1 else 0 end as picu_khusus_covid_confirm_l,
                case when dg.kddiagnosa= 'B34.2' and ru.id=672 and ps.objectjeniskelaminfk =2  then 1 else 0 end as picu_khusus_covid_confirm_p,
                    
            case when dg.kddiagnosa= 'Z03.8' and ru.id IN(654,655) and ps.objectjeniskelaminfk =1  then 1 else 0 end as isolasi_tekanan_negatif_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id IN(654,655) and ps.objectjeniskelaminfk =2  then 1 else 0 end as isolasi_tekanan_negatif_suspect_p,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(671) and ps.objectjeniskelaminfk =1  then 1 else 0 end as icu_dengan_ventilator_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(671)  and ps.objectjeniskelaminfk =2  then 1 else 0 end as icu_dengan_ventilator_suspect_p,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(673) and ps.objectjeniskelaminfk =1  then 1 else 0 end as nicu_khusus_covid_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id in(673)and ps.objectjeniskelaminfk =2  then 1 else 0 end as nicu_khusus_covid_suspect_p,
                case when dg.kddiagnosa= 'Z03.8' and ru.id=672 and ps.objectjeniskelaminfk =1  then 1 else 0 end as picu_khusus_covid_suspect_l,
                case when dg.kddiagnosa= 'Z03.8' and ru.id=672 and ps.objectjeniskelaminfk =2  then 1 else 0 end as picu_khusus_covid_suspect_p
            FROM 
            pasiendaftar_t as pd
                                inner JOIN antrianpasiendiperiksa_t as apd ON apd.noregistrasifk = pd.norec
                                INNER JOIN diagnosapasien_t as dgp ON dgp.noregistrasifk = apd.norec
                                INNER JOIN detaildiagnosapasien_t as ddg ON ddg.objectdiagnosapasienfk = dgp.norec
                                INNER JOIN diagnosa_m as dg ON dg.id = ddg.objectdiagnosafk
                                inner join ruangan_m as ru on ru.id=pd.objectruanganlastfk
                                inner join pasien_m as ps on ps.id=pd.nocmfk
                WHERE
                pd.kdprofile=16 
                                AND ddg.objectjenisdiagnosafk IN (1,5)
                                AND apd.norec NOT IN (SELECT dip.noregistrasifk FROM diagnosapasien_t AS dip 
                                                                    INNER JOIN detaildiagnosapasien_t as ddip ON ddip.objectdiagnosapasienfk = dip.norec
                                                                    WHERE ddip.objectjenisdiagnosafk = 2)
                and pd.statusenabled =true
                                and ddg.kdprofile = $kdProfile
                and pd.tglpulang ISNULL ) as x
            "))->first();
            $methods ='POST';
            if($query->icu_dengan_ventilator_suspect_l== null )
            {
                $arr = array(
                "tanggal"=> $now,
                "icu_dengan_ventilator_suspect_l"=> "0",
                "icu_dengan_ventilator_suspect_p"=> "0",
                "icu_dengan_ventilator_confirm_l"=> "0",
                "icu_dengan_ventilator_confirm_p"=> "0",
                "icu_tanpa_ventilator_suspect_l"=> "0",
                "icu_tanpa_ventilator_suspect_p"=> "0",
                "icu_tanpa_ventilator_confirm_l"=> "0",
                "icu_tanpa_ventilator_confirm_p"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_p"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_p"=> "0",
                "isolasi_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tekanan_negatif_confirm_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                "nicu_khusus_covid_suspect_l"=> "0",
                "nicu_khusus_covid_suspect_p"=> "0",
                "nicu_khusus_covid_confirm_l"=> "0",
                "nicu_khusus_covid_confirm_p"=> "0",
                "picu_khusus_covid_suspect_l"=> "0",
                "picu_khusus_covid_suspect_p"=> "0",
                "picu_khusus_covid_confirm_l"=> "0",
                "picu_khusus_covid_confirm_p"=> "0", );
            }else {
                $arr = array(
                "tanggal"=> $now,
                "icu_dengan_ventilator_suspect_l"=> $query->icu_dengan_ventilator_suspect_l,
                "icu_dengan_ventilator_suspect_p"=> $query->icu_dengan_ventilator_suspect_p,
                "icu_dengan_ventilator_confirm_l"=> $query->icu_dengan_ventilator_confirm_l,
                "icu_dengan_ventilator_confirm_p"=> $query->icu_dengan_ventilator_confirm_p,
                "icu_tanpa_ventilator_suspect_l"=> "0",
                "icu_tanpa_ventilator_suspect_p"=> "0",
                "icu_tanpa_ventilator_confirm_l"=> "0",
                "icu_tanpa_ventilator_confirm_p"=> "0",
                // "icu_tanpa_ventilator_suspect_l"=> $query->icu_tanpa_ventilator_suspect_l,
                // "icu_tanpa_ventilator_suspect_p"=> $query->icu_tanpa_ventilator_suspect_p,
                // "icu_tanpa_ventilator_confirm_l"=> $query->icu_tanpa_ventilator_confirm_l,
                // "icu_tanpa_ventilator_confirm_p"=> $query->icu_tanpa_ventilator_confirm_p,
                "icu_tekanan_negatif_dengan_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_dengan_ventilator_confirm_p"=> "0",
                // "icu_tekanan_negatif_dengan_ventilator_suspect_l"=> $query->icu_tekanan_negatif_dengan_ventilator_suspect_l,
                // "icu_tekanan_negatif_dengan_ventilator_suspect_p"=> $query->icu_tekanan_negatif_dengan_ventilator_suspect_p,
                // "icu_tekanan_negatif_dengan_ventilator_confirm_l"=> $query->icu_tekanan_negatif_dengan_ventilator_confirm_l,
                // "icu_tekanan_negatif_dengan_ventilator_confirm_p"=> $query->icu_tekanan_negatif_dengan_ventilator_confirm_p,
                "icu_tekanan_negatif_tanpa_ventilator_suspect_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_suspect_p"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_l"=> "0",
                "icu_tekanan_negatif_tanpa_ventilator_confirm_p"=> "0",
                // "icu_tekanan_negatif_tanpa_ventilator_suspect_l"=> $query->icu_tekanan_negatif_tanpa_ventilator_suspect_l,
                // "icu_tekanan_negatif_tanpa_ventilator_suspect_p"=> $query->icu_tekanan_negatif_tanpa_ventilator_suspect_p,
                // "icu_tekanan_negatif_tanpa_ventilator_confirm_l"=> $query->icu_tekanan_negatif_tanpa_ventilator_confirm_l,
                // "icu_tekanan_negatif_tanpa_ventilator_confirm_p"=> $query->icu_tekanan_negatif_tanpa_ventilator_confirm_p,
                "isolasi_tekanan_negatif_suspect_l"=> $query->isolasi_tekanan_negatif_suspect_l,
                "isolasi_tekanan_negatif_suspect_p"=> $query->isolasi_tekanan_negatif_suspect_p,
                "isolasi_tekanan_negatif_confirm_l"=> $query->isolasi_tekanan_negatif_confirm_l,
                "isolasi_tekanan_negatif_confirm_p"=> $query->isolasi_tekanan_negatif_confirm_p,
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_suspect_p"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_l"=> "0",
                "isolasi_tanpa_tekanan_negatif_confirm_p"=> "0",
                // "isolasi_tanpa_tekanan_negatif_suspect_l"=> $query->isolasi_tanpa_tekanan_negatif_suspect_l,
                // "isolasi_tanpa_tekanan_negatif_suspect_p"=> $query->isolasi_tanpa_tekanan_negatif_suspect_p,
                // "isolasi_tanpa_tekanan_negatif_confirm_l"=> $query->isolasi_tanpa_tekanan_negatif_confirm_l,
                // "isolasi_tanpa_tekanan_negatif_confirm_p"=> $query->isolasi_tanpa_tekanan_negatif_confirm_p,
                // "isolasi_tanpa_tekanan_negatif_suspect_l"=> $query->isolasi_tanpa_tekanan_negatif_suspect_l,
                // "isolasi_tanpa_tekanan_negatif_suspect_p"=> $query->isolasi_tanpa_tekanan_negatif_suspect_p,
                // "isolasi_tanpa_tekanan_negatif_confirm_l"=> $query->isolasi_tanpa_tekanan_negatif_confirm_l,
                "nicu_khusus_covid_suspect_l"=> $query->nicu_khusus_covid_suspect_l,
                "nicu_khusus_covid_suspect_p"=> $query->nicu_khusus_covid_suspect_p,
                "nicu_khusus_covid_confirm_l"=> $query->nicu_khusus_covid_confirm_l,
                "nicu_khusus_covid_confirm_p"=> $query->nicu_khusus_covid_confirm_p,
                "picu_khusus_covid_suspect_l"=> $query->picu_khusus_covid_suspect_l,
                "picu_khusus_covid_suspect_p"=> $query->picu_khusus_covid_suspect_p,
                "picu_khusus_covid_confirm_l"=> $query->picu_khusus_covid_confirm_l,
                "picu_khusus_covid_confirm_p"=> $query->picu_khusus_covid_confirm_p, );

//            return $this->respond($arr);

            }
            $dataJsonSend = json_encode ($arr);
        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        }else if($method == 'delete'){
            $methods ='DELETE';
            $now =  date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $arr = array(
                "tanggal" =>  $now,
            );
            $dataJsonSend = json_encode ($arr);
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/LapV2/PasienDirawatTanpaKomorbid";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }

    public function PasienKeluar($method,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        $now = date('Y-m-d');
        if($method == 'post'){
            $now =  date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $kom = collect(DB::select("select
                    sum(z.sembuh) as sembuh,
                    sum(z.discarded) as discarded,
                    sum(z.meninggal_komorbid) as meninggal_komorbid,
                    sum(z.meninggal_prob_pre_komorbid) as meninggal_prob_pre_komorbid,
                    sum(z.meninggal_prob_bayi_komorbid) as meninggal_prob_bayi_komorbid,
                    sum(z.meninggal_prob_neo_komorbid) as meninggal_prob_neo_komorbid,
                    sum(z.meninggal_prob_balita_komorbid) as meninggal_prob_balita_komorbid,
                    sum(z.meninggal_prob_anak_komorbid) as meninggal_prob_anak_komorbid,
                    sum(z.meninggal_prob_remaja_komorbid) as meninggal_prob_remaja_komorbid,
                    sum(z.meninggal_prob_dws_komorbid) as meninggal_prob_dws_komorbid,
                       sum(z.meninggal_prob_lansia_komorbid) as meninggal_prob_lansia_komorbid,
                    sum(z.meninggal_disarded_komorbid) as meninggal_disarded_komorbid,
                    sum(z.aps) as aps,
                    sum(z.dirujuk) as dirujuk,
                    sum(z.isman) as isman
                    from (
                    select
                    case when x.spid in (6,34,20,1) and x.kddiagnosa='B34.2' then 1 else 0 end as sembuh,
                    case when x.spid in (5,4,3,10,11) and x.kddiagnosa='B34.2' then 1 else 0 end   as discarded ,
                    case when x.skid in (11,5,15) and x.kddiagnosa='B34.2' then 1 else 0 end as meninggal_komorbid ,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.hari <= 6) and x.scid =10 then 1 else 0 end as meninggal_prob_pre_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.hari >= 7 and x.hari <= 28) and x.scid =10 then 1 else 0 end as meninggal_prob_neo_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.hari >= 29 and x.umur < 1) and x.scid =10 then 1 else 0 end as meninggal_prob_bayi_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 1 and x.umur < 4) and x.scid =10 then 1 else 0 end as meninggal_prob_balita_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 5 and x.umur <= 18) and x.scid =10 then 1 else 0 end as meninggal_prob_anak_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 19 and x.umur <= 40) and x.scid =10 then 1 else 0 end as meninggal_prob_remaja_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 41 and x.umur <= 60) and x.scid =10 then 1 else 0 end as meninggal_prob_dws_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur > 60 ) and x.scid =10 then 1 else 0 end as meninggal_prob_lansia_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and x.scid in (7) then 1 else 0 end as meninggal_disarded_komorbid ,
                    case when x.spid in (2) then 1 else 0 end as aps,
                    case when x.spid in (11,10,3,4) then 1 else 0 end as dirujuk,
                    0 as isman
                    from (
                    SELECT
                    dg.kddiagnosa,
                    ddg.objectjenisdiagnosafk,
                    pd.noregistrasi,
                    pd.tglpulang,
                    sk.statuskeluar,
                    sp.statuspulang,
                    ps.tgllahir,
                    0 as scid,
                    sp.id as spid,
                    sk.id as skid,
                    (CURRENT_DATE - ps.tgllahir::date) as hari,
                    DATE_PART('year',CURRENT_DATE) - DATE_PART('year', ps.tgllahir::date)as umur
                    FROM
                    pasiendaftar_t as pd
                    INNER JOIN statuskeluar_m as sk on sk.id = pd.objectstatuskeluarfk
                    INNER JOIN statuspulang_m as sp on sp.id = pd.objectstatuspulangfk
                    INNER JOIN antrianpasiendiperiksa_t as apd ON apd.noregistrasifk = pd.norec
                    INNER JOIN diagnosapasien_t as dgp ON dgp.noregistrasifk = apd.norec
                    INNER JOIN detaildiagnosapasien_t as ddg ON ddg.objectdiagnosapasienfk = dgp.norec
                    INNER JOIN diagnosa_m as dg ON dg.id = ddg.objectdiagnosafk
                    INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                    INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                    WHERE
                    pd.kdprofile=$kdProfile
                    and pd.statusenabled = true
                    and pd.tglpulang between '$now 00:00' and '$now 23:59'
                    AND apd.norec  IN (SELECT dip.noregistrasifk FROM diagnosapasien_t AS dip 
                                                                INNER JOIN detaildiagnosapasien_t as ddip ON ddip.objectdiagnosapasienfk = dip.norec
                                                                WHERE ddip.objectjenisdiagnosafk = 2)
                    
                    ) as x
                    ) as z
                "))->first();

            $nonKom = collect(DB::select("select
                    sum(z.sembuh) as sembuh,
                    sum(z.discarded) as discarded,
                    sum(z.meninggal_tanpa_komorbid) as meninggal_tanpa_komorbid,
                    sum(z.meninggal_prob_pre_tanpa_komorbid) as meninggal_prob_pre_tanpa_komorbid,
                    sum(z.meninggal_prob_neo_tanpa_komorbid) as meninggal_prob_neo_tanpa_komorbid,
                    sum(z.meninggal_prob_bayi_tanpa_komorbid) as meninggal_prob_bayi_tanpa_komorbid,
                    sum(z.meninggal_prob_balita_tanpa_komorbid) as meninggal_prob_balita_tanpa_komorbid,
                    sum(z.meninggal_prob_anak_tanpa_komorbid) as meninggal_prob_anak_tanpa_komorbid,
                    sum(z.meninggal_prob_remaja_tanpa_komorbid) as meninggal_prob_remaja_tanpa_komorbid,
                    sum(z.meninggal_prob_dws_tanpa_komorbid) as meninggal_prob_dws_tanpa_komorbid,
                    sum(z.meninggal_prob_lansia_tanpa_komorbid) as meninggal_prob_lansia_tanpa_komorbid,
                    sum(z.meninggal_discarded_tanpa_komorbid) as meninggal_discarded_tanpa_komorbid,
                    sum(z.aps) as aps,
                    sum(z.dirujuk) as dirujuk,
                    sum(z.isman) as isman
                    from (
                    select
                    case when x.spid in (6,34,20,1) and x.kddiagnosa='B34.2' then 1 else 0 end as sembuh,
                    case when x.spid in (5,4,3,10,11) and x.kddiagnosa='B34.2' then 1 else 0 end   as discarded ,
                    case when x.skid in (11,5,15) and x.kddiagnosa='B34.2' then 1 else 0 end as meninggal_tanpa_komorbid ,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.hari <= 6) and x.scid =10 then 1 else 0 end as meninggal_prob_pre_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.hari >= 7 and x.hari <= 28) and x.scid =10 then 1 else 0 end as meninggal_prob_neo_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.hari >= 29 and x.umur < 1) and x.scid =10 then 1 else 0 end as meninggal_prob_bayi_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 1 and x.umur < 4) and x.scid =10 then 1 else 0 end as meninggal_prob_balita_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 5 and x.umur <= 18) and x.scid =10 then 1 else 0 end as meninggal_prob_anak_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 19 and x.umur <= 40) and x.scid =10 then 1 else 0 end as meninggal_prob_remaja_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur >= 41 and x.umur <= 60) and x.scid =10 then 1 else 0 end as meninggal_prob_dws_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and (x.umur > 60 ) and x.scid =10 then 1 else 0 end as meninggal_prob_lansia_tanpa_komorbid,
                    case when x.skid in (11,5,15) and x.kddiagnosa='Z03.8' and x.scid in (7) then 1 else 0 end as meninggal_discarded_tanpa_komorbid ,
                    case when x.spid in (2) then 1 else 0 end as aps,
                    case when x.spid in (11,10,3,4) then 1 else 0 end as dirujuk,
                    0 as isman
                    from (
                    SELECT
                    dg.kddiagnosa,
                    ddg.objectjenisdiagnosafk,
                    pd.noregistrasi,
                    pd.tglpulang,
                    sk.statuskeluar,
                    sp.statuspulang,
                    ps.tgllahir,
                    0 as scid,
                    sp.id as spid,
                    sk.id as skid,
                    (CURRENT_DATE - ps.tgllahir::date) as hari,
                    DATE_PART('year',CURRENT_DATE) - DATE_PART('year', ps.tgllahir::date)as umur
                    FROM
                    pasiendaftar_t as pd
                    INNER JOIN statuskeluar_m as sk on sk.id = pd.objectstatuskeluarfk
                    INNER JOIN statuspulang_m as sp on sp.id = pd.objectstatuspulangfk
                    INNER JOIN antrianpasiendiperiksa_t as apd ON apd.noregistrasifk = pd.norec
                    INNER JOIN diagnosapasien_t as dgp ON dgp.noregistrasifk = apd.norec
                    INNER JOIN detaildiagnosapasien_t as ddg ON ddg.objectdiagnosapasienfk = dgp.norec
                    INNER JOIN diagnosa_m as dg ON dg.id = ddg.objectdiagnosafk
                    INNER JOIN ruangan_m as ru on ru.id=apd.objectruanganfk
                    INNER JOIN pasien_m as ps on ps.id=pd.nocmfk
                    WHERE
                    pd.kdprofile=$kdProfile
                    and pd.statusenabled = true
                    and pd.tglpulang between '$now 00:00' and '$now 23:59'
                    AND apd.norec NOT IN (SELECT dip.noregistrasifk FROM diagnosapasien_t AS dip 
                                                                INNER JOIN detaildiagnosapasien_t as ddip ON ddip.objectdiagnosapasienfk = dip.norec
                                                                WHERE ddip.objectjenisdiagnosafk = 2)
                    
                    ) as x
                    ) as z
                "))->first();
//            dd($query);
            $methods ='POST';
            $discared = 0;
            if($nonKom->discarded != null ){ $discared= $nonKom->discarded ; }
            if($kom->discarded != null ){$discared= $discared + $kom->discarded ; }
            $sembuh = 0;
            if($nonKom->sembuh != null ){ $sembuh= $nonKom->sembuh ; }
            if($kom->sembuh != null ){$sembuh= $sembuh + $kom->sembuh ;}
            $rujuk = 0;
            if($nonKom->dirujuk != null ){ $rujuk= $nonKom->dirujuk ; }
            if($kom->dirujuk != null ){$rujuk= $rujuk + $kom->dirujuk ;  }
            $isman = 0;
            if($nonKom->isman != null ){ $isman= $nonKom->isman ; }
            if($kom->isman != null ){$isman= $isman + $kom->isman ;  }
            $aps = 0;
            if($nonKom->aps != null ){ $aps= $nonKom->aps ; }
            if($kom->aps != null ){$aps= $aps + $kom->aps ;  }
            $arr = array(
                "tanggal"=> $now,
                "sembuh"=> $sembuh,
                "discarded"=> $discared,
                "meninggal_komorbid"=>  $kom->meninggal_komorbid != null ? $kom->meninggal_komorbid : 0,
                "meninggal_tanpa_komorbid"=> $nonKom->meninggal_tanpa_komorbid != null ? $nonKom->meninggal_tanpa_komorbid : 0,
                "meninggal_prob_pre_komorbid"=>   $kom->meninggal_prob_pre_komorbid != null ? $kom->meninggal_prob_pre_komorbid : 0,
                "meninggal_prob_neo_komorbid"=>   $kom->meninggal_prob_neo_komorbid != null ? $kom->meninggal_prob_neo_komorbid : 0,
                "meninggal_prob_bayi_komorbid"=>  $kom->meninggal_prob_bayi_komorbid != null ? $kom->meninggal_prob_bayi_komorbid : 0,
                "meninggal_prob_balita_komorbid"=>   $kom->meninggal_prob_balita_komorbid != null ? $kom->meninggal_prob_balita_komorbid : 0,
                "meninggal_prob_anak_komorbid"=>   $kom->meninggal_prob_anak_komorbid != null ? $kom->meninggal_prob_anak_komorbid : 0,
                "meninggal_prob_remaja_komorbid"=>   $kom->meninggal_prob_remaja_komorbid != null ? $kom->meninggal_prob_remaja_komorbid : 0,
                "meninggal_prob_dws_komorbid"=>   $kom->meninggal_prob_dws_komorbid != null ? $kom->meninggal_prob_dws_komorbid : 0,
                "meninggal_prob_lansia_komorbid"=>  $kom->meninggal_prob_lansia_komorbid != null ? $kom->meninggal_prob_lansia_komorbid : 0,
                "meninggal_prob_pre_tanpa_komorbid"=>   $nonKom->meninggal_prob_pre_tanpa_komorbid != null ? $nonKom->meninggal_prob_pre_tanpa_komorbid : 0,
                "meninggal_prob_neo_tanpa_komorbid"=> $nonKom->meninggal_prob_neo_tanpa_komorbid != null ? $nonKom->meninggal_prob_neo_tanpa_komorbid : 0,
                "meninggal_prob_bayi_tanpa_komorbid"=> $nonKom->meninggal_prob_bayi_tanpa_komorbid != null ? $nonKom->meninggal_prob_bayi_tanpa_komorbid : 0,
                "meninggal_prob_balita_tanpa_komorbid"=>$nonKom->meninggal_prob_balita_tanpa_komorbid != null ? $nonKom->meninggal_prob_balita_tanpa_komorbid : 0,
                "meninggal_prob_anak_tanpa_komorbid"=> $nonKom->meninggal_prob_anak_tanpa_komorbid != null ? $nonKom->meninggal_prob_anak_tanpa_komorbid : 0,
                "meninggal_prob_remaja_tanpa_komorbid"=> $nonKom->meninggal_prob_remaja_tanpa_komorbid != null ? $nonKom->meninggal_prob_remaja_tanpa_komorbid : 0,
                "meninggal_prob_dws_tanpa_komorbid"=>$nonKom->meninggal_prob_dws_tanpa_komorbid != null ? $nonKom->meninggal_prob_dws_tanpa_komorbid : 0,
                "meninggal_prob_lansia_tanpa_komorbid"=> $nonKom->meninggal_prob_lansia_tanpa_komorbid != null ? $nonKom->meninggal_prob_lansia_tanpa_komorbid : 0,
                "meninggal_disarded_komorbid"=>$kom->meninggal_disarded_komorbid != null ? $kom->meninggal_disarded_komorbid : 0,
                "meninggal_discarded_tanpa_komorbid"=>$nonKom->meninggal_discarded_tanpa_komorbid != null ? $nonKom->meninggal_discarded_tanpa_komorbid : 0,
                "dirujuk"=>$rujuk,
                "isman"=>$isman,
                "aps"=>$aps,
            );
//            return $this->respond($arr);
            $dataJsonSend = json_encode ($arr);
        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        }else if($method == 'delete'){
            $methods ='DELETE';
            $now =  date('Y-m-d');
            if(isset($request['tgl'])){
                $now  = $request['tgl'];
            }
            $arr = array(
                "tanggal" =>  $now,
            );
            $dataJsonSend = json_encode ($arr);
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/LapV2/PasienKeluar";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }
    public function getRefTT(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        $dataJsonSend = null;
        $methods ='GET';
        $url = "http://sirs.kemkes.go.id/fo/index.php/Referensi/tempat_tidur";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }
    public function getRefUsia(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        $dataJsonSend = null;
        $methods ='GET';
        $url = "http://sirs.kemkes.go.id/fo/index.php/Referensi/usia_meninggal_probable";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }
    public function Fasyankes($method, Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        if($method == 'post'){
            $q = collect(DB::select("select 
            x.kodesirs as id_tt,
            sum(x.isi) as terpakai,
            count(x.tt_id) as jumlah,
            x.jumlah_ruang,
            x.qdepartemen
             from (
            SELECT distinct
            tt.id as tt_id,tt.nomorbed as namabed,
            kmr.id as kmr_id,kmr.namakamar,
            ru.id AS id_ruangan,ru.namaruangan,
           -- case when ru.kodesirs  is not null then 
		ru.kodesirs 
        --else kls.kodeexternal end as kodesirs
        ,
            sb.statusbed,case when sb.id in (1) then 1 else 0 end as isi,
             case when sb.id in (2,9,6)then 1 else 0 end as kosong,
            dp.qdepartemen,
             1 as jumlah_ruang
            FROM
            tempattidur_m AS tt
            inner JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
            inner JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            inner JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
            inner JOIN kelas_m AS kls ON kls.id = kmr.objectkelasfk
            inner JOIN departemen_m AS dp ON ru.objectdepartemenfk = dp.id
            WHERE tt.kdprofile = $kdProfile and
            tt.statusenabled = true and
            kmr.statusenabled = true
            and ru.kodesirs is not null
            ) as x
            group by x.kodesirs,x.qdepartemen,x.jumlah_ruang; "));
            $methods ='POST';
           // return $this->respond($q);
            $tt = [];
            foreach ($q as $v){
                $arr = array(
                    "id_tt"=> (string)$v->id_tt ,
                    "jumlah_ruang"=> $v->jumlah_ruang ,
                    "jumlah"=>  $v->jumlah ,
                    "terpakai"=>  $v->terpakai ,
//                    "tower"=>  $v->qdepartemen ,
                );
                $tt [] =   $arr;
                $dataJsonSend = json_encode ($arr);
                $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes";
                $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
//                return $this->respond($response);
            }
            return $this->respond(  $response);
        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        } else if($method == 'delete'){
            $methods ='DELETE';

            $arr = array(
                "id_tt"=> $request['id_tt'],
            );
            $dataJsonSend = json_encode ($arr);
        }else if($method == 'put'){
            $methods ='PUT';
            $q = collect(DB::select("select 
            x.kodesirs as id_tt,
            sum(x.isi) as terpakai,
            count(x.tt_id) as jumlah,
            x.jumlah_ruang,
            x.qdepartemen
             from (
            SELECT distinct
            tt.id as tt_id,tt.nomorbed as namabed,
            kmr.id as kmr_id,kmr.namakamar,
            ru.id AS id_ruangan,ru.namaruangan,
           -- case when ru.kodesirs  is not null then 
        ru.kodesirs 
        --else kls.kodeexternal end as kodesirs
        ,
            sb.statusbed,case when sb.id in (1) then 1 else 0 end as isi,
             case when sb.id in (2,9,6)then 1 else 0 end as kosong,
            dp.qdepartemen,
             1 as jumlah_ruang
            FROM
            tempattidur_m AS tt
            inner JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
            inner JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            inner JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
            inner JOIN kelas_m AS kls ON kls.id = kmr.objectkelasfk
            inner JOIN departemen_m AS dp ON ru.objectdepartemenfk = dp.id
            WHERE tt.kdprofile = $kdProfile and
            tt.statusenabled = true and
            kmr.statusenabled = true
            and ru.kodesirs is not null
            ) as x
            group by x.kodesirs,x.qdepartemen,x.jumlah_ruang; "));
//            return $this->respond($q);
            $methods ='PUT';
            foreach ($q as $v){
                $arr = array(
                    "id_tt"=> (string)$v->id_tt ,
                    "jumlah_ruang"=> $v->jumlah_ruang ,
                    "jumlah"=>  $v->jumlah ,
                    "terpakai"=>  $v->terpakai ,
//                    "tower"=>  $v->qdepartemen ,
                );
                $tt [] =   $arr;
                $dataJsonSend = json_encode ($arr);
                $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes";
                $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
//                return $this->respond($response);
            }
            return $this->respond(  $response);
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }

    public function getReffSDM(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        $dataJsonSend = null;
        $methods ='GET';
        $url = "http://sirs.kemkes.go.id/fo/index.php/Referensi/kebutuhan_sdm";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }
    public function FasyankesSDM($method,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        if($method == 'post'){
            $q = collect(DB::select("
               select count(x.id) as jml,x.kdjabatan from (
                select pg.id,pg.namalengkap,jb.namajabatan,jb.kdjabatan
                from pegawai_m as pg 
                join jabatan_m as jb on jb.id=pg.objectjabatanfungsionalfk
                where pg.statusenabled =true 
                and pg.kdprofile=$kdProfile
                ) as x
                group  by x.kdjabatan"));
            $methods ='POST';
            foreach ($q as $v){
                $arr = array(
                    "id_kebutuhan"=>$v->kdjabatan,
                    "jumlah_eksisting"=> $v->jml,
                    "jumlah"=>  $v->jml,
                    "jumlah_diterima"=> "0"
                );
                $dataJsonSend = json_encode ($arr);
                $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes/sdm";
                $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
                return $this->respond($response);
            }

        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        } else if($method == 'delete'){
            $methods ='DELETE';
            $arr = array(
                "id_kebutuhan"=> $request['id_kebutuhan'],
            );
            $dataJsonSend = json_encode ($arr);
        }else if($method == 'put'){
            $q = collect(DB::select("
               select count(x.id) as jml,x.kdjabatan from (
                select pg.id,pg.namalengkap,jb.namajabatan,jb.kdjabatan
                from pegawai_m as pg 
                join jabatan_m as jb on jb.id=pg.objectjabatanfungsionalfk
                where pg.statusenabled =true 
                and pg.kdprofile=$kdProfile
                ) as x
                group  by x.kdjabatan"));
            $methods ='PUT';
            foreach ($q as $v){
                $arr = array(
                    "id_kebutuhan"=>$q->kdjabatan,
                    "jumlah_eksisting"=> $q->jml,
                    "jumlah"=>  $q->jml,
                    "jumlah_diterima"=> "0"
                );
                $dataJsonSend = json_encode ($arr);
                $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes/sdm";
                $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
                return $this->respond($response);
            }
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes/sdm";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }

    public function getReffAPD(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        $dataJsonSend = null;
        $methods ='GET';
        $url = "http://sirs.kemkes.go.id/fo/index.php/Referensi/kebutuhan_apd";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);
    }
    public function FasyankesAPD($method,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $headers =  $this->getHeadersCovid($kdProfile);
        if($method == 'post'){
            $methods ='POST';
            $arr = array(
                "id_kebutuhan"=> "16",
                "jumlah_eksisting"=> "154",
                "jumlah"=> "3",
                "jumlah_diterima"=> "1"
            );
            $dataJsonSend = json_encode ($arr);
        } else if($method == 'get'){
            $methods ='GET';
            $dataJsonSend = null;
        } else if($method == 'delete'){
            $methods ='DELETE';
            $arr = array(
                "id_kebutuhan"=> "24",
            );
            $dataJsonSend = json_encode ($arr);
        }else if($method == 'put'){
            $methods ='PUT';
            $arr = array(
                "id_kebutuhan"=> "16",
                "jumlah_eksisting"=> "154",
                "jumlah"=> "3",
                "jumlah_diterima"=> "1"
            );
            $dataJsonSend = json_encode ($arr);
        }
        $url = "http://sirs.kemkes.go.id/fo/index.php/Fasyankes/apd";
        $response = $this->sendBridgingCurl($headers , $dataJsonSend, $url,$methods);
        return $this->respond($response);

    }
    public function getBedMonitorRS(Request $request)
    {

        $kdProfile = $this->getDataKdProfile($request);
        if($kdProfile == 18){

        }
        $user = '3171900S';//$this->settingDataFixed('userIdSisrute',$kdProfile);
        $password ='12345';// $this->settingDataFixed('userIdSisrute',$kdProfile);
        $data = collect(DB::select("
             select count(tt_id) as total ,sum(x.terpakaimale) as terpakaimale,
                    sum(x.terpakaifemale) as terpakaifemale,
                    sum(x.kosong) as kosong
                    from (
                    SELECT 
                    DISTINCT tt. ID AS tt_id,tt.nomorbed AS namabed,
                    kmr. ID AS kmr_id,kmr.namakamar,ru. ID AS id_ruangan,pd.jkid,
                    case when pd.jkid = 1 then 1 else 0 end as terpakaimale,
                    case when pd.jkid = 2 then 1 else 0 end as terpakaifemale,
                    case when pd.jkid is null then 1 else 0 end as kosong
                    FROM
                    tempattidur_m AS tt
                    INNER JOIN statusbed_m AS sb ON sb. ID = tt.objectstatusbedfk
                    INNER JOIN kamar_m AS kmr ON kmr. ID = tt.objectkamarfk
                    INNER JOIN ruangan_m AS ru ON ru. ID = kmr.objectruanganfk
                    left join (select * from (
                            select   row_number() over (partition by pd.noregistrasi 
                            order by apd.tglmasuk desc) as rownum ,ps.nocm,
                            ps.objectjeniskelaminfk AS jkid,ps.tgllahir,ps.namapasien,
                            pd.tglregistrasi,
                            EXTRACT (YEAR FROM
                            age(CURRENT_DATE, ps.tgllahir)
                            ) :: INT AS umur,   DATE_PART(
                            'day',
                            now() - pd.tglregistrasi
                            ) AS lamarawat,pd.noregistrasi,
                            ps.nohp,apd.objectruanganfk,apd.objectkamarfk,apd.nobed
                            from pasiendaftar_t as pd 
                            join antrianpasiendiperiksa_t as apd on pd.norec =apd.noregistrasifk
                            and apd.objectruanganfk=pd.objectruanganlastfk
                            join pasien_m as ps on ps.id=pd.nocmfk
                            where pd.tglpulang is null 
                            and pd.statusenabled=TRUE
                            and pd.kdprofile=$kdProfile
                            )as x where x.rownum=1
                    ) as pd on pd.objectruanganfk=ru.id 
                    and pd.objectkamarfk = kmr.id
                    and pd.nobed=tt.id
                    WHERE
                    tt.kdprofile =$kdProfile
                    AND tt.statusenabled = TRUE
                    AND kmr.statusenabled = TRUE
                    ) as x
           
            "))->first();
        $kosongmale = 0;
        $kosongfemale = 0;
        $totakhir = $data->kosong /2;
        if($this->is_decimal($totakhir)) {
            $kosongmale = $totakhir;
            $kosongfemale = $totakhir;
            $whole = floor($totakhir);      // 1
            $fraction = $totakhir - $whole; // .25
            $kosongmale = $totakhir - $fraction;
            $kosongfemale = ($whole + ($fraction * 2)) ;
        }else{
            $kosongmale = $totakhir;
            $kosongfemale = $totakhir;
        }
        $arr [] = array(
            'kode_ruang' => '0059',
            'tipe_pasien' => '0011',
            'terpakaiMale' => $data->terpakaimale,
            'terpakaiFemale' => $data->terpakaifemale,
            'kosongMale' => $kosongmale,
            'kosongFemale' => $kosongfemale,
            'waiting' => 0,
            'tgl_update' => date('Y-m-d H:i:s'),
        );
        // $passEn =  md5($password);
        // date_default_timezone_set('UTC');
        // $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
        // $curl = curl_init();

        // $dataJsonSend = json_encode ($arr);
        // return $this->respond(   $arr);
        curl_setopt_array($curl, array(
//                CURLOPT_PORT => $this->getPortBrigdingBPJS(),
            CURLOPT_URL=>  "http://sirs.yankes.kemkes.go.id/sirsservice/ranap",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataJsonSend,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json;",
                "X-rs-id: $user",
                "X-pass: $passEn" ,
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $result= "cURL Error #:" . $err;
        } else {
            $result =  json_decode($response);
        }
        return $this->respond($result );

    }

}