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
class BridgingSiranapController  extends ApiController
{
	use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=true);
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
		// $data = DB::select(DB::raw("select pd.noregistrasi , ps.objectjeniskelaminfk ,pd.objectkelasfk,
		// 	kmr.id as idkamar, kmr.namakamar, kmen.kodeexternal as kode_ruang,
		// 	kls.kodeexternal as tipe_pasien
		// 	from pasiendaftar_t as pd
		// 	join antrianpasiendiperiksa_t as apd  on pd.norec= apd.noregistrasifk and  apd.objectruanganfk =pd.objectruanganlastfk
		// 	join registrasipelayananpasien_t as rpp  on pd.norec= rpp.noregistrasifk and  rpp.objectruanganfk =pd.objectruanganlastfk
		// 	join kamar_m as kmr  on kmr.id= apd.objectkamarfk
		// 	join tempattidur_m as tt on tt.id= rpp.objecttempattidurfk
		// 	join kelas_m as kls on kls.id = pd.objectkelasfk
		// 	left join ruangperawatankemenkes_m as kmen on kmen.id = tt.objectruangperawatankemenkesfk
		// 	join pasien_m as ps on ps.id=pd.nocmfk
		// 	where pd.tglpulang is NULL
		// 	and apd.tglkeluar is null
		// 	and pd.objectkelasfk is not null
		// 	ORDER BY pd.noregistrasi;
		// 	"));
		// $dataTempatTidur = [];
		// $terpakaiMale = 0;
		// $terpakaiFemale = 0;
		// foreach ($data  as $item) {
		// 	if($item->kode_ruang != null && $item->tipe_pasien != null){
		// 		$sama = false;
		// 		$i = 0;
		// 		foreach ($dataTempatTidur as $item2) {
		// 			if ($item->kode_ruang == $dataTempatTidur[$i]['kode_ruang']
		// 				&& $item->tipe_pasien == $dataTempatTidur[$i]['tipe_pasien']) {
		// 				$sama = true;
		// 				$jml = (float)$item2['total_tt'] + 1;
		// 				$dataTempatTidur[$i]['total_tt'] = $jml;
		// 				if ($item->objectjeniskelaminfk == 1) {
		// 					$dataTempatTidur[$i]['terpakaiMale'] = (float)$item2['terpakaiMale'] + 1;
		// 				}
		// 				if ($item->objectjeniskelaminfk == 2) {
		// 					$dataTempatTidur[$i]['terpakaiFemale'] = (float)$item2['terpakaiFemale'] + 1;
		// 				}
		// 			}
		// 			$i = $i + 1;
		// 		}
		// 		if ($sama == false) {
		// 			$dataTempatTidur [] = array(
		// 				'total_tt' => 0,
		// 				'kode_ruang' =>$item->kode_ruang,
		// 				'tipe_pasien' =>$item->tipe_pasien,
		// 				'terpakaiMale' =>$terpakaiMale,
		// 				'terpakaiFemale' => $terpakaiFemale,
		// 				'kosongMale' => 0,
		// 				'kosongFemale' => 0,
		// 				'waiting' => 0,
		// 				'tgl_update' => date('Y-m-d H:i:s'),
		// 			);
		// 		}
		// 	}
		// }
		// return $this->respond($dataTempatTidur);
		$data = DB::select(DB::raw("SELECT
			x.kode_ruang,
			x.tipe_pasien,
			COUNT (x.id) AS total_tt,
			0 AS terpakaiMale,
			0 AS terpakaiFemale,
			sum(kosongMale) as kosongMale,
			sum(kosongFemale) as kosongFemale,
			0 AS waiting,
			format (getdate(), 'yyyy-MM-dd') AS tgl_update
				
		FROM
			(
				SELECT
					ru.kodesiranap AS kode_ruang,
					kl.kodesiranap AS tipe_pasien,
					[kmr].[id],
					[kmr].[namakamar],
					[kl].[id] AS [id_kelas],
					[kl].[namakelas],
					[ru].[id] AS [id_ruangan],
					[ru].[namaruangan],
					case when ru.jenis ='male' then 1 else 0 end as kosongMale ,
					case when ru.jenis ='female' then 1 else 0 end as kosongFemale 
				FROM
					[tempattidur_m] AS [tt]
				LEFT JOIN [statusbed_m] AS [sb] ON [sb].[id] = [tt].[objectstatusbedfk]
				LEFT JOIN [kamar_m] AS [kmr] ON [kmr].[id] = [tt].[objectkamarfk]
				LEFT JOIN [ruangan_m] AS [ru] ON [ru].[id] = [kmr].[objectruanganfk]
				LEFT JOIN [kelas_m] AS [kl] ON [kl].[id] = [kmr].[objectkelasfk]
				WHERE
					[tt].[statusenabled] = 1
			) AS x
		GROUP BY
			x.kode_ruang,
			x.tipe_pasien
		order by x.kode_ruang"));
	
		foreach ($data as $key => $values) {
			$dat = 0;
			if($values->kosongFemale == '0' && $values->kosongMale == '0' ){
				$dat =(float) $values->total_tt /2;
				if($this->is_decimal($dat)){
					$values->kosongMale =$values->total_tt ;
				}else{
					$values->kosongMale = $dat;
					$values->kosongFemale = $dat;
				}				
			}

				if(( $values->kosongFemale != '0' && $values->kosongMale != '0'  )&&(
					(float) $values->kosongFemale +(float) $values->kosongMale  != (float) $values->total_tt) ){
					$tot  = 0;
					$tot2  = 0;
					$totakhir =0;
					$tot = (float) $values->kosongFemale +(float) $values->kosongMale  ;
					$tot2 = (float) $values->total_tt - $tot ;

					$totakhir = $tot2 /2;

					if($this->is_decimal($totakhir)){
						$values->kosongMale =(float)$values->kosongMale +$tot2  ;
					}else{
						$values->kosongMale = (float)$values->kosongMale+ $totakhir ;
						$values->kosongFemale = (float)$values->kosongFemale+$totakhir ;
					}	
				}
			# code...
		}
		// return $data;


		 $pasien = DB::select(DB::raw("SELECT
				pd.noregistrasi,
					pd.tglregistrasi,
					pd.tglpulang,
					kl.kodesiranap AS tipe_pasien,
					ru.kodesiranap AS kode_ruang,
					DATEDIFF(
						DAY,
						pd.tglregistrasi,
						CASE
					WHEN pd.tglpulang IS NULL THEN
						getdate()
					ELSE
						pd.tglpulang
					END
					) AS hari,case when ps.objectjeniskelaminfk =1 then 'male' else 'female' end as jenis
				FROM
					pasiendaftar_t AS pd
				INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
				INNER JOIN kelas_m AS kl ON kl.ID = pd.objectkelasfk
				INNER JOIN pasien_m AS ps ON ps.id= pd.nocmfk
				WHERE
					ru.objectdepartemenfk = 16
				AND pd.tglpulang IS NULL
				ORDER BY
					pd.tglregistrasi ASC
                "));
		 $total_tt=0;
		 foreach ($data as $key => $ruang) {
		 	 $total_tt=(float)$ruang->total_tt + $total_tt;
		 	foreach ($pasien as $key => $psn) {
		 		# code...
		 		if($ruang->kode_ruang  ==  $psn->kode_ruang && $ruang->tipe_pasien  ==  $psn->tipe_pasien 
		 			&& $psn->jenis == 'male'){
		 			$ruang->terpakaiMale  = (float)$ruang->terpakaiMale + 1;
		 			
		 		}
		 		if($ruang->kode_ruang  ==  $psn->kode_ruang && $ruang->tipe_pasien  ==  $psn->tipe_pasien 
		 			&& $psn->jenis == 'female'){
		 			$ruang->terpakaiFemale  = (float)$ruang->terpakaiFemale + 1;
		 			
		 		}

		 	}
		 	# code...
		 }
		 $terpakai = 0;
		 $kosong = 0;
		 foreach ( $data as $key => $value) {
		 	if( (float)$value->kosongMale!=0){
		 		 $value->kosongMale =(float) $value->kosongMale -(float) $value->terpakaiMale;
		 	}
		 	if( (float)$value->kosongFemale!=0){
		 		 $value->kosongFemale =(float) $value->kosongFemale -(float) $value->terpakaiFemale;
		 	}

			
			// if(((float) $value->total_tt !=)) 
		 	# code...
		 }
		 foreach ($data as $key ) {
		  	 $key->kosongFemale = (string) $key->kosongFemale;
		  	 $key->kosongMale = (string) $key->kosongMale;
		  	 $key->terpakaiFemale = (string) $key->terpakaiFemale;
		  	 $key->terpakaiMale = (string) $key->terpakaiMale;
		 }
		 // $res =  array(
		 // 			'total_tt' => $total_tt, 
	 	// 			'totalPakai' => $terpakai, 
	 	// 			'kosong' => $kosong, 
			// 		'data' => $data
			// 	);
		 return $this->respond($data);

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
	public function sitegar(Request $request){
		   $kdProfile = $this->getDataKdProfile($request);
		// Konfigurasi Database lokal data bed untuk RS

			$config['db_server'] 	= "localhost";
			$config['db_name'] 	= "bogor_simrs";
			$config['db_user']	= "root";
			$config['db_password']	= "root";

			// Init API sesuai dengan kode pada SITEGAR

			$config['email'] 	= "rsudcibinong@gmail.com";
			$config['password'] 	= "rsudcibinong@gmail.com";
			$config['redaksi']	= "RS Umum Daerah Cibinong";
			$config['api_url'] 	= "https://sitegar.bogorkab.go.id/api";

			// SQL Getter untuk data BED, 5 kolom wajib data BED SITEGAR

			$config['column_bed']		= "bed";
			$config['column_kapasitas_l']	= "kapasitas_l";
			$config['column_terpakai_l']	= "terpakai_l";
			$config['column_kapasitas_p']	= "kapasitas_p";
			$config['column_terpakai_p']	= "terpakai_p";
			$config['column_kelas'] 		= "kelas";

		$config['sql'] = "select
			b.nama as bed,b.kapasitas_l,b.terpakai_l,b.kapasitas_p,b.terpakai_p,k.nama as kelas
			FROM tb_bed b
			JOIN tb_kelas_bed k ON (k.id_kelas_bed = b.kelas)";

			$conn = new mysqli($config['db_server'], $config['db_user'], $config['db_password'], $config['db_name']);
			if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

			$result = $conn->query($config['sql']);

			$bed = array();
			if ($result->num_rows > 0) {
			    while($row = $result->fetch_assoc()) {
			    	if (!$result)
			    		die($conn->error);
				    $bed[] = array(
						$row[$config['column_bed']],
						$row[$config['column_kapasitas_l']],
						$row[$config['column_terpakai_l']],
						$row[$config['column_kapasitas_p']],
						$row[$config['column_terpakai_p']],
						$row[$config['column_kelas']]
					);
			    }
			}

			$ch = curl_init($config['api_url']);
			$jsonDataEncoded = json_encode(
				array(
					'email' => $config['email'] ,
					'password' => $config['password'] ,
					'data' => array(
						'rs' => $config['redaksi'],
						'bed' => $bed
					)
				));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_exec($ch);

			$conn->close();
	}
}