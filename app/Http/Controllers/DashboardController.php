<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

//use Storage;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
// use Illuminate\Contracts\Encryption\DecryptException;
// use Picqer;



class DashboardController extends ApiController
{
    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }
    public function geTopTenDiagnosaByKD($kddiagnosa, Request $r)
    {
        $now = date('Y-m');
        $map = \DB::select(DB::raw("
                select * from (select count(x.provinsi) as jumlah,x.provinsi,x.kdmap
				from (
				select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
				kot.kotakabupaten,
            case when prov.provinsi is null then ( select provinsi_m.provinsi from profile_m
                join provinsi_m on provinsi_m.id =profile_m.provinsifk
                where profile_m.id = pm.profilefk limit 1) else prov.provinsi end as provinsi,
                case when prov.kdmap is null then ( select provinsi_m.kdmap from profile_m
                join provinsi_m on provinsi_m.id =profile_m.provinsifk
                where profile_m.id = pm.profilefk limit 1) else prov.kdmap end as kdmap
				from pelayananmedis_t as pm
				inner join pasien_m as ps on pm.pasienfk= ps.id
				left join alamat_m as al on ps.id = al.pasienfk
				left join provinsi_m as prov on prov.id = al.provinsifk
				left join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
				where pm.kddiagnosa is not null
				and pm.kddiagnosa ='$kddiagnosa'
                and pm.tglregistrasi between '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
				) as x GROUP BY x.provinsi,x.kdmap) as z
				order by z.jumlah desc
                 "));
        $data['map'] = $map;
        $data['kddiagnosa'] = $kddiagnosa;
        $dataMap =[];
        foreach ($map as $key => $m) {

            $dataMap [$m->kdmap] = (float) $m->jumlah;

        }
        return $dataMap;


    }
    public function geTopTenDiagnosaByRSAddress($kddiagnosa, Request $r)
    {
        $now = date('Y-m');
        $kdProfile = $_SESSION['kdProfile'];
        $map = \DB::select(DB::raw("
            select * from (
                select count(x.provinsi)as jumlah,x.provinsi,x.kdmap
                from (select dm.kddiagnosa,
					case when pro.namapropinsi is null then 'Banten' else pro.namapropinsi end as provinsi,
                dm.namadiagnosa,case when pro.namapropinsi is null then '4' else pro.kdmap end as kdmap
                from antrianpasiendiperiksa_t as app
                left join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
                left join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
                inner join pasiendaftar_t as pd on pd.norec = app.noregistrasifk
                inner join pasien_m as ps on ps.id = pd.nocmfk
                left join alamat_m as alm on alm.nocmfk = ps.id
                 left join kecamatan_m as kec on kec.id = alm.objectkecamatanfk
                left join kotakabupaten_m as kot on kot.id = alm.objectkotakabupatenfk
                left join propinsi_m as pro on pro.id = alm.objectpropinsifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where app.kdprofile = $kdProfile and dm.kddiagnosa <> '-'  and   pd.statusenabled=true and               
                pd.tglregistrasi BETWEEN '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
                and dm.kddiagnosa='$kddiagnosa'
                )as x GROUP BY x.provinsi ,x.kdmap 
                ) as z
                ORDER BY z.jumlah desc  limit 10
                 "));
        $data['map'] = $map;
        $data['kddiagnosa'] = $kddiagnosa;
        $dataMap =[];
        foreach ($map as $key => $m) {
            $dataMap [$m->kdmap] = (float) $m->jumlah;

        }
        return $dataMap;


    }
    public function getNameRegionBykode($kode){
        $data = \DB::select(\DB::raw("select * from provinsi_m where kdmap='$kode'"));

        return $data;
    }
    public function getDetailTableDiag(Request $r){
        $data =\DB::table('pelayananmedis_t as pm')
            ->join ('pasien_m as ps','pm.pasienfk','=','ps.id')
            ->join ('alamat_m as al','ps.id','=','al.pasienfk')
            ->join ('profile_m as prof','prof.id','=','pm.profilefk')
            ->join ('provinsi_m as prov','prov.id','=','al.provinsifk')
            ->join ('kotakabupaten_m as kot','kot.id','=','al.kotakabupatenfk')
            ->select(DB::raw("pm.noregistrasi,DATE_FORMAT(pm.tglregistrasi, '%Y-%m-%d') as tglregistrasi,pm.dpjp,pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
			prov.provinsi,kot.kotakabupaten,kot.kdmap,prof.namaprofile,pm.norm"))
            ->where('pm.kddiagnosa',$r['kddiagnosa'])
            ->where('kot.kdmap',$r['code'])
//            ->whereRaw("DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='$now'")
            ->get();
//        dd($data);
        return view('module.shared.detail-table-diagnosa',compact('data'));
    }
    public function getNameKotaBykode($kode,$kddiagnosa){
        $now = date('Y-m');
        $kota = \DB::select(\DB::raw("select * from kotakabupaten_m where kdmap='$kode'"));
//        $table =\DB::table('pelayananmedis_t as pm')
//            ->join ('pasien_m as ps','pm.pasienfk','=','ps.id')
//            ->join ('alamat_m as al','ps.id','=','al.pasienfk')
//            ->join ('profile_m as prof','prof.id','=','pm.profilefk')
//            ->join ('provinsi_m as prov','prov.id','=','al.provinsifk')
//            ->join ('kotakabupaten_m as kot','kot.id','=','al.kotakabupatenfk')
//            ->select(DB::raw("pm.noregistrasi,DATE_FORMAT(pm.tglregistrasi, '%Y-%m-%d') as tglregistrasi,pm.dpjp,pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//			prov.provinsi,kot.kotakabupaten,kot.kdmap,prof.namaprofile,pm.norm"))
//            ->where('pm.kddiagnosa',$kddiagnosa)
//            ->where('kot.kdmap',$kode)
////            ->whereRaw("DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='$now'")
//            ->get();
//        $map=\DB::select(DB::raw("
//		select count(x.namaprofile) as jumlah,x.namaprofile from (	select pm.noregistrasi,DATE_FORMAT(pm.tglregistrasi, '%Y-%m-%d') as 	tglregistrasi,
//				pm.dpjp,pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//						 	prov.provinsi,kot.kotakabupaten,kot.kdmap,prof.namaprofile,pm.norm
//						 	from pelayananmedis_t as pm
//						 	inner join pasien_m as ps on pm.pasienfk= ps.id
//						 	inner join alamat_m as al on ps.id = al.pasienfk
//						 	inner join profile_m as prof on prof.id = pm.profilefk
//						 	inner join provinsi_m as prov on prov.id = al.provinsifk
//						 	inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
//						 	where pm.kddiagnosa is not null
//						 	and pm.kddiagnosa ='Z03.8'
//						 	and kot.kdmap='282'
//						 	/*
//						 	and DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='2020-06'
//						 	 */
//				) as x
//				group by x.namaprofile
//				order by x.namaprofile
//			"));

        $data['kota'] = $kota;
//        $data['table'] = $table;
//        $data['diagnosa'] = $map;
        return $data;
    }

    public function geTopTenDiagnosaDetail($code,$nama,$kddiagnosa)
    {
        $now = date('Y-m');

        $map = \DB::select(DB::raw("
			select * from (select count(x.kotakabupaten) as jumlah,x.kotakabupaten,x.kdmap,x.namadiagnosa
			from (
			select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
			prov.provinsi,kot.kotakabupaten,kot.kdmap
			from pelayananmedis_t as pm
			inner join pasien_m as ps on pm.pasienfk= ps.id
			inner join alamat_m as al on ps.id = al.pasienfk
			inner join provinsi_m as prov on prov.id = al.provinsifk
			inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
			where pm.kddiagnosa is not null
			and pm.kddiagnosa ='$kddiagnosa'
			and prov.kdmap='$code'
			/*
			and DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='$now'
			 */
			) as x GROUP BY x.kotakabupaten,x.kdmap,x.namadiagnosa) as z
			order by z.jumlah desc "));
        $namawilayah = $nama;
        $kodewilayah = $code;
        return view('dashboard.pelayanan-detail',compact('map','kodewilayah','namawilayah','kddiagnosa'));


    }
    public function getChartByRS(Request $r){
        $now = date('Y-m');
        $kota = collect(\DB::select("select * from kotakabupaten_m where kdmap='$r[kodekota]'"))->first();

//        $chart =\DB::select(DB::raw("
//		select count(x.namaprofile) as jumlah,x.namaprofile from (	select pm.noregistrasi,DATE_FORMAT(pm.tglregistrasi, '%Y-%m-%d') as 	tglregistrasi,
//				pm.dpjp,pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//						 	prov.provinsi,kot.kotakabupaten,kot.kdmap,prof.namaprofile,pm.norm
//						 	from pelayananmedis_t as pm
//						 	inner join pasien_m as ps on pm.pasienfk= ps.id
//						 	inner join alamat_m as al on ps.id = al.pasienfk
//						 	inner join profile_m as prof on prof.id = pm.profilefk
//						 	inner join provinsi_m as prov on prov.id = al.provinsifk
//						 	inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
//						 	where pm.kddiagnosa is not null
//						 	and pm.kddiagnosa ='$r[kddiagnosa]'
//						 	and kot.kdmap='$r[kodekota]'
//						 	/*
//						 	and DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='2020-06'
//						 	 */
//                               and pm.tglregistrasi between '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
//				) as x
//				group by x.namaprofile
//				order by x.namaprofile
//			"));

        $chart =\DB::select(DB::raw("
		select  count(x.namaprofile) as jumlah,x.namaprofile
                from (
                select  pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,prof.namaprofile,
                case when kot.kdmap is null then (
                select kotakabupaten_m.kdmap from profile_m
                join kotakabupaten_m on kotakabupaten_m.id = profile_m.kotakabupatenfk
                where profile_m.id = pm.profilefk limit 1) else kot.kdmap end as kdmap
                from pelayananmedis_t as pm
                inner join pasien_m as ps on pm.pasienfk= ps.id
                left join alamat_m as al on ps.id = al.pasienfk
                left join provinsi_m as prov on prov.id = al.provinsifk
                left join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
                 join profile_m as prof on prof.id = pm.profilefk
                where pm.kddiagnosa is not null
                and pm.kddiagnosa ='$r[kddiagnosa]'
                 and pm.tglregistrasi between '$r[tglawal]' and '$r[tglakhir]'
                ) as x where x.kdmap ='$r[kodekota]' GROUP BY x.namaprofile
			"));

        $data['kota'] = $kota;
        $data['chart'] = $chart;
//        $data['diagnosa'] = $map;
        return $data;
    }
    public function getDetailRS(Request $r){
//        $data =\DB::table('pelayananmedis_t as pm')
//            ->join ('pasien_m as ps','pm.pasienfk','=','ps.id')
//            ->leftjoin ('alamat_m as al','ps.id','=','al.pasienfk')
//            ->join ('profile_m as prof','prof.id','=','pm.profilefk')
//            ->leftjoin ('provinsi_m as prov','prov.id','=','al.provinsifk')
//            ->leftjoin ('kotakabupaten_m as kot','kot.id','=','al.kotakabupatenfk')
//            ->leftjoin ('kecamatan_m as kec','kec.id','=','al.kecamatanfk')
//            ->leftjoin ('desakelurahan_m as des','des.id','=','al.desakelurahanfk')
//            ->select(DB::raw("pm.noregistrasi,DATE_FORMAT(pm.tglregistrasi, '%Y-%m-%d') as tglregistrasi,pm.dpjp,
//            pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//			prov.provinsi,kot.kotakabupaten,prof.namaprofile,pm.norm,kec.kecamatan,des.desakelurahan,
//			case when kot.kdmap is null then (
//                        select kotakabupaten_m.kdmap from profile_m
//                        join kotakabupaten_m on kotakabupaten_m.id = profile_m.kotakabupatenfk
//                        where profile_m.id = pm.profilefk limit 1) else kot.kdmap end as kdmap"))
//            ->where('pm.kddiagnosa',$r['kddiagnosa'])
//            ->where('kot.kdmap',$r['kodekota'])
//            ->where('prof.namaprofile',$r['namaprofile'])
//             ->whereBetween('pm.tglregistrasi',[ $r['tglawal'].' 00:00' , $r['tglakhir'].' 23:59'])
//            ->orderby('pm.tglregistrasi','desc')
//
////            ->whereRaw("DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='$now'")
//            ->get();
        $data = DB::select(DB::raw("select * from (SELECT
                    pm.noregistrasi,
                    --DATE_FORMAT(
                    --    pm.tglregistrasi,
                    --    '%Y-%m-%d'
                    --) AS tglregistrasi,

                    to_char(
                        pm.tglregistrasi,
                        'yyyy-MM-dd'
                    ) AS tglregistrasi,
                    pm.dpjp,
                    pm.kddiagnosa,
                    pm.namadiagnosa,
                    ps.namapasien,
                    al.alamatlengkap,
                    prov.provinsi,
                    kot.kotakabupaten,
                case when kot.kdmap is null then (
                                        select kotakabupaten_m.kdmap from profile_m
                                        join kotakabupaten_m on kotakabupaten_m.id = profile_m.kotakabupatenfk
                                        where profile_m.id = pm.profilefk limit 1) else kot.kdmap end as kdmap,
                    prof.namaprofile,
                    prof.id,
                    pm.norm,
                    kec.kecamatan,
                    des.desakelurahan
                FROM
                    pelayananmedis_t AS pm
                INNER JOIN pasien_m AS ps ON pm.pasienfk = ps.id
                LEFT JOIN alamat_m AS al ON ps.id = al.pasienfk
                INNER JOIN profile_m AS prof ON prof.id = pm.profilefk
                LEFT JOIN provinsi_m AS prov ON prov.id = al.provinsifk
                LEFT JOIN kotakabupaten_m AS kot ON kot.id = al.kotakabupatenfk
                LEFT JOIN kecamatan_m AS kec ON kec.id = al.kecamatanfk
                LEFT JOIN desakelurahan_m AS des ON des.id = al.desakelurahanfk
                WHERE
                    pm.kddiagnosa = '$r[kddiagnosa]'
                AND prof.namaprofile = '$r[namaprofile]'
                AND pm.tglregistrasi BETWEEN '$r[tglawal]'
                AND '$r[tglakhir]'
               ) as x where x.kdmap='$r[kodekota]' ORDER BY
                    x.tglregistrasi DESC
                "));
//        dd($data);
        foreach ($data as $d){
            $idprof = $d->id;
            if($d->kotakabupaten == null){
                $det = collect(DB::select("select 	profile_m.alamatlengkap , provinsi_m.provinsi,
                kotakabupaten_m.kotakabupaten,kecamatan_m.kecamatan,
                desakelurahan_m.desakelurahan from profile_m
                left join provinsi_m on provinsi_m.id = profile_m.provinsifk
                left join kotakabupaten_m on kotakabupaten_m.id = profile_m.kotakabupatenfk
                left join kecamatan_m on kecamatan_m.id = profile_m.kecamatanfk
                left join desakelurahan_m on desakelurahan_m.id = profile_m.desakelurahanfk
                where profile_m.id = $idprof"))->first();
//                dd($det);
                if(!empty($det)){
                    $d->provinsi = $det->provinsi;
                    $d->alamatlengkap = '-';
                    $d->kotakabupaten = $det->kotakabupaten;
                    $d->kecamatan = $det->kecamatan;
                    $d->desakelurahan = $det->desakelurahan;
                }
            }
        }
//        dd($data);
        return $data;
    }
    public function  getDataDashboard(Request $r){
        $colors = \App\Http\Controllers\MainController::getColor();
        $tglakhir = date('Y-m-d');
        $tglawal = Carbon::now()->subWeek(1)->format('Y-m-d');
        if(isset($r['tglawal'])){
            $tglawal = $r['tglawal'];
        }
        if(isset($r['tglakhir'])){
            $tglakhir = $r['tglakhir'];
        }


        $data = \DB::select(DB::raw("select * from (select count(x.kddiagnosa) as jumlah,x.kddiagnosa,x.namadiagnosa
                from (
                select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
                prov.provinsi,kot.kotakabupaten
                from pelayananmedis_t as pm
                inner join pasien_m as ps on pm.pasienfk= ps.id
                left join alamat_m as al on ps.id = al.pasienfk
                left join provinsi_m as prov on prov.id = al.provinsifk
                left join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
                where pm.kddiagnosa is not null
                and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
                and pm.kddiagnosa !='B34.2'
                ) as x GROUP BY x.namadiagnosa) as z
                order by z.jumlah desc limit 10"));
        foreach ($data as $key => $value) {
            $data[$key]->color = $colors[$key];
            # code...
        }

        // $corona = \DB::select(DB::raw("
        //         select dg.kddiagnosa,dg.namadiagnosa,case when counts.jumlah is null then 0 else counts.jumlah end as jumlah
        //          from diagnosa_m as dg
        //         left join (
        //         select count(pm.kddiagnosa) as jumlah,pm.kddiagnosa
        //         from pelayananmedis_t as pm
        //         inner join pasien_m as ps on pm.pasienfk= ps.id
        //         inner join profile_m as prof on prof.id = pm.profilefk
        //         where pm.kddiagnosa ='B34.2'
        //         and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
        //         group BY pm.kddiagnosa
        //         ) as counts on (dg.kddiagnosa = counts.kddiagnosa)
        //         where dg.kddiagnosa ='B34.2'"));
        $corona = \DB::select(DB::raw("
                select count(pm.kddiagnosa) as jumlah,pm.kddiagnosa
                from pelayananmedis_t as pm
                inner join pasien_m as ps on pm.pasienfk= ps.id
                where pm.kddiagnosa ='B34.2'
                and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
                group BY pm.kddiagnosa"));
//        $corona =[];
        if(count($data) > 0){
           $data =  array_merge($data);//array_merge($data,$corona);
        }
        $jmlTerkonfirmasi=0;
        if(count($corona) > 0){
            $jmlTerkonfirmasi = $corona[0]->jumlah;
        }

        $drilldown =  \DB::select(DB::raw("select * from (select count(x.namaprofile) as jumlah,x.namaprofile,x.kddiagnosa
                    from (
                    select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,prof.namaprofile
                    from pelayananmedis_t as pm
                    inner join pasien_m as ps on pm.pasienfk= ps.id
                    inner join profile_m as prof on prof.id = pm.profilefk
                    where pm.kddiagnosa is not null
                    and pm.kddiagnosa !='B34.2'
                    and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
                    ) as x GROUP BY x.namaprofile,x.kddiagnosa) as z
                    order by z.kddiagnosa desc"));

        // dd($data);
//        $umur = \DB::select(\DB::raw("select count(x.rangeumur) as jumlah,x.rangeumur from (
//            select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//            prov.provinsi,kot.kotakabupaten,ps.tgllahir,
//            case when TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <= 1 then 'Bayi : < 1 tahun'
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) >= 2 and TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <=5 then 'Balita : >=2 & <=5 Tahun '
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) > 5 and TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <=12 then 'Anak : > 5 & <=12 Tahun'
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) > 12 and TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <=50 then 'Dewasa : >12 & <=50 Tahun'
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) > 50  then 'Geriatri : >50 Tahun'  end as rangeumur
//
//            from pelayananmedis_t as pm
//            inner join pasien_m as ps on pm.pasienfk= ps.id
//            inner join alamat_m as al on ps.id = al.pasienfk
//            inner join provinsi_m as prov on prov.id = al.provinsifk
//            inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
//            where pm.kddiagnosa is not null
//           /*
//            and DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='$now'
//            */
//            ) as x
//            group by x.rangeumur"));
        $kddiagnosa = '';
        $namadiagnosa = '';
        if (count($data) > 0) {
            $kddiagnosa = $data[0]->kddiagnosa;
            $namadiagnosa = $data[0]->namadiagnosa;
//            $map = \DB::select(DB::raw("
//                    select * from (select count(x.provinsi) as jumlah,x.provinsi,x.kdmap
//                    from (
//                        select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//                        kot.kotakabupaten,
//                        case when prov.provinsi is null then ( select provinsi_m.provinsi from profile_m
//                        join provinsi_m on provinsi_m.id =profile_m.provinsifk
//                        where profile_m.id = pm.profilefk limit 1) else prov.provinsi end as provinsi,
//                        case when prov.kdmap is null then ( select provinsi_m.kdmap from profile_m
//                        join provinsi_m on provinsi_m.id =profile_m.provinsifk
//                        where profile_m.id = pm.profilefk limit 1) else prov.kdmap end as kdmap
//                        from pelayananmedis_t as pm
//                        inner join pasien_m as ps on pm.pasienfk= ps.id
//                        left join alamat_m as al on ps.id = al.pasienfk
//                        left join provinsi_m as prov on prov.id = al.provinsifk
//                        left join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
//                        where pm.kddiagnosa ='$kddiagnosa'
//                        and pm.kddiagnosa !='B34.2'
//                        and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
//                    ) as x GROUP BY x.provinsi,x.kdmap) as z
//                    order by z.jumlah desc ; "));
        }

        // $Faskes = \DB::select(DB::raw("SELECT COUNT(x.profilefk) as faskes
        //                         FROM (select pel.profilefk,pro.namaprofile
        //                         from pelayananmedis_t as pel
        //                         INNER JOIN profile_m as pro on pro.id = pel.profilefk
        //                         where pel.tglregistrasi BETWEEN '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
        //                               AND pel.kddiagnosa ='B34.2'
        //                         GROUP BY pel.profilefk,pro.namaprofile) as x"));

        $Sembuh = \DB::select(DB::raw("SELECT SUM(x.kddiagnosa) as sembuh
                              FROM (select CASE WHEN pel.kddiagnosa = 'B34.2' THEN 1 ELSE 0 END AS kddiagnosa,pro.namaprofile
                              from pelayananmedis_t as pel
                              LEFT JOIN profile_m as pro on pro.id = pel.profilefk
                              where pel.tglregistrasi BETWEEN '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
                                    AND pel.tglpulang IS NOT NULL
                                    AND pel.kddiagnosa ='B34.2') as x"));

        // $jmlFaskes = 0;
        // if (count($Faskes) > 0) {
        //     $jmlFaskes = $Faskes[0]->faskes;
        // }

        $jmlSembuh = 0;
        if (count($Sembuh) > 0) {
            $jmlSembuh = $Sembuh[0]->sembuh;
        }



        $result['listdiagnosa'] = $data;
        $result['listdiagnosacovid'] = $corona;
//        $result['map'] = $map;
        $result['kddiagnosa'] = $kddiagnosa;
        $result['namadiagnosa'] = $namadiagnosa;
        $result['umur'] = [];//$umur;
        $result['tglawal'] = $tglawal;
        $result['tglakhir'] = $tglakhir;
        $result['drilldown'] = $drilldown;

        $result['titleSuspek'] = 0;
        $result['titleTerkonfirmasi'] = $jmlTerkonfirmasi;
        $result['titleProbable'] = 0;
        $result['titleKontakErat'] = 0;
        $result['titlePelakuPerjalanan'] = 0;
        $result['titleDiscarded'] =  $jmlTerkonfirmasi;
        $result['titleSelesaiIsolasi'] = 0;
        $result['titleKematian'] = 0;
        // $result['faskes'] = $jmlFaskes;
        // $result['terkonfirmasi'] = $jmlTerkonfirmasi;
        // $result['sembuh'] = $jmlSembuh;
//        $result view('dashboard.pelayanan', compact('data'));

        return $result;
//        return view('dashboard.pelayanan', compact('data', 'map', 'kddiagnosa', 'umur'));

    }
    function getComboDiagnosa(){
        $data = DB::select(DB::raw("select kddiagnosa,namadiagnosa from pelayananmedis_t
                where kddiagnosa is not null
                and kddiagnosa !='-'
                GROUP BY kddiagnosa,namadiagnosa
                order by kddiagnosa asc"));
        echo "<option value=''>-- Filter Diagnosa --</option>";
        foreach ($data as $k ) {
            echo "<option value='$k->kddiagnosa' >".$k->kddiagnosa.' - '.$k->namadiagnosa."</option>";
        }
    }
    public function getDataChartRS(Request $r){
        $drilldown =  \DB::select(DB::raw("
                    select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,prof.namaprofile,pp.provinsi,1 as jumlah
                    from pelayananmedis_t as pm
                    inner join pasien_m as ps on pm.pasienfk= ps.id
                    left join alamat_m as al on al.pasienfk= ps.id
                    inner join profile_m as prof on prof.id = pm.profilefk
                    left join provinsi_m as pp on pp.id = al.provinsifk
                    where pm.kddiagnosa ='$r[kddiagnosa]'
                    and pm.tglregistrasi between '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
                   "));
        return $drilldown;
    }

    public function getDataFaskes(Request $r){
        // $data = \DB::select(DB::raw("
        //     SELECT COUNT(x.profilefk) as jumlah,x.namaprofile
        //                         FROM (select pel.profilefk,pro.namaprofile
        //                         from pelayananmedis_t as pel
        //                         INNER JOIN profile_m as pro on pro.id = pel.profilefk
        //                         where pel.tglregistrasi BETWEEN '$r[tglawal] 00:00' and '$r[tglakhir] 23:59'
        //                               AND pel.kddiagnosa ='B34.2'
        //                         ) as x
        //         GROUP BY x.namaprofile
        //         "));
        // dd($data);
        $data = [];
        return view('module.shared.detail-faskes',compact('data'));
    }
    public function getMapDataKabupatenKota(Request $r)
    {
        $map = \DB::select(DB::raw("
                SELECT
                    *
                FROM
                    (
                        SELECT
                            count(x.kotakabupaten) AS jumlah,
                            x.kotakabupaten,
                            x.lat,
                            x.`long`
                        FROM
                            (
                                SELECT
                                    pm.kddiagnosa,
                                    pm.namadiagnosa,
                                    ps.namapasien,
                                    al.alamatlengkap,
                                    CASE
                                WHEN kot.kotakabupaten IS NULL THEN
                                    (
                                        SELECT
                                            pr2.kotakabupaten
                                        FROM
                                            profile_m pf2
                                        JOIN kotakabupaten_m pr2 ON pr2.id = pf2.kotakabupatenfk
                                        WHERE
                                            pf2.id = pm.profilefk
                                        LIMIT 1
                                    )
                                ELSE
                                    kot.kotakabupaten
                                END AS kotakabupaten,
                                CASE
                            WHEN kot.lat IS NULL THEN
                                (
                                    SELECT
                                        pr2.lat
                                    FROM
                                        profile_m pf2
                                    JOIN kotakabupaten_m pr2 ON pr2.id = pf2.kotakabupatenfk
                                    WHERE
                                        pf2.id = pm.profilefk
                                    LIMIT 1
                                )
                            ELSE
                                kot.lat
                            END AS lat,
                            CASE
                        WHEN kot.`long` IS NULL THEN
                            (
                                SELECT
                                    pr2.`long`
                                FROM
                                    profile_m pf2
                                JOIN kotakabupaten_m pr2 ON pr2.id = pf2.kotakabupatenfk
                                WHERE
                                    pf2.id = pm.profilefk
                                LIMIT 1
                            )
                        ELSE
                            kot.`long`
                        END AS `long`
                        FROM
                            pelayananmedis_t AS pm
                        INNER JOIN pasien_m AS ps ON pm.pasienfk = ps.id
                        LEFT JOIN alamat_m AS al ON ps.id = al.pasienfk
                        LEFT JOIN provinsi_m AS prov ON prov.id = al.provinsifk
                        LEFT JOIN kotakabupaten_m AS kot ON kot.id = al.kotakabupatenfk
                        WHERE
                            pm.kddiagnosa = '$r[kddiagnosa]'
                        AND pm.tglregistrasi BETWEEN '$r[tglawal] 00:00'
                        AND '$r[tglakhir] 23:59'
                            ) AS x
                        GROUP BY
                            x.kotakabupaten,
                            x.`long`,
                            x.lat
                    ) AS z
                ORDER BY
                    z.jumlah DESC
                 "));
        $data['pasienbykota'] = $map;
        $data['kddiagnosa'] = $r['kddiagnosa'];
        $dataMap =[];
//        foreach ($map as $key => $m) {
//            $dataMap [$m->kdmap] = (float) $m->jumlah;
//
//        }
//        dd($map);
        return $data;


    }
    public function getDetailPasienKecamatan(Request $r)
    {
        $map = \DB::select(DB::raw("
                SELECT
                    *
                FROM
                    (
                        SELECT
                            count(x.kecamatan) AS jumlah,
                            x.kecamatan,
                            x.lat,
                            x.`long`
                        FROM
                            (
                                SELECT
                                    pm.kddiagnosa,
                                    pm.namadiagnosa,
                                    ps.namapasien,
                                    al.alamatlengkap,
                                    /*


                                    CASE
                                WHEN kec.kecamatan IS NULL THEN
                                    (
                                        SELECT
                                            pr2.kecamatan
                                        FROM
                                            profile_m pf2
                                        JOIN kecamatan_m pr2 ON pr2.id = pf2.kecamatanfk
                                        WHERE
                                            pf2.id = pm.profilefk
                                        LIMIT 1
                                    )
                                ELSE
                                    kec.kecamatan
                                END AS kecamatan,
                                CASE
                            WHEN kec.lat IS NULL THEN
                                (
                                    SELECT
                                        pr2.lat
                                    FROM
                                        profile_m pf2
                                    JOIN kecamatan_m pr2 ON pr2.id = pf2.kecamatanfk
                                    WHERE
                                        pf2.id = pm.profilefk
                                    LIMIT 1
                                )
                            ELSE
                                kec.lat
                            END AS lat,
                            CASE
                        WHEN kec.`long` IS NULL THEN
                            (
                                SELECT
                                    pr2.`long`
                                FROM
                                    profile_m pf2
                                JOIN kecamatan_m pr2 ON pr2.id = pf2.kecamatanfk
                                WHERE
                                    pf2.id = pm.profilefk
                                LIMIT 1
                            )
                        ELSE
                            kec.`long`
                        END AS `long`

                                     */
                                     kec.kecamatan,kec.lat,kec.`long`
                        FROM
                            pelayananmedis_t AS pm
                        INNER JOIN pasien_m AS ps ON pm.pasienfk = ps.id
                        LEFT JOIN alamat_m AS al ON ps.id = al.pasienfk
                          LEFT JOIN kotakabupaten_m AS kot ON kot.id = al.kotakabupatenfk
                        LEFT JOIN kecamatan_m AS kec ON kec.id = al.kecamatanfk
                        WHERE
                             pm.kddiagnosa = '$r[kddiagnosa]'
                        AND pm.tglregistrasi BETWEEN '$r[tglawal] 00:00'
                        AND '$r[tglakhir] 23:59'
                        and kot.kotakabupaten='$r[kabupaten]'
                            ) AS x
                        GROUP BY
                            x.kecamatan,
                            x.`long`,
                            x.lat
                    ) AS z
                ORDER BY
                    z.jumlah DESC
                 "));
        $data['pasienbykecamatan'] = $map;
        $jml = 0;
        foreach ($map as $m){
            if($m->lat != null && $m->long != null){
                $jml = $jml + 1 ;
            }
        }
        $data['kddiagnosa'] = $r['kddiagnosa'];
        $data['jumlah'] = $jml;
        $dataMap =[];
//        foreach ($map as $key => $m) {
//            $dataMap [$m->kdmap] = (float) $m->jumlah;
//
//        }
//        dd($map);
        return $data;


    }
    public function  getDataDashboardFlag(Request $r){
        $colors = MainController::getColor();
        $tglakhir = date('Y-m-d');
        $tglawal = Carbon::now()->subWeek(1)->format('Y-m-d');
        if(isset($r['tglawal'])){
            $tglawal = $r['tglawal'];
        }
        if(isset($r['tglakhir'])){
            $tglakhir = $r['tglakhir'];
        }

        $data = \DB::select(DB::raw("SELECT SUM(x.suspect) AS suspect,SUM(x.terkonfirmasi) AS terkonfirmasi,SUM(x.pelakuperjalanan) AS pelakuperjalanan,
                             SUM(x.discarded) AS discarded,SUM(x.selesaiisolasi) AS selesaiisolasi,SUM(x.kematian) AS kematian,
                             SUM(x.probable) AS probable,SUM(x.kontakerat) AS kontakerat
                FROM (select
                            CASE WHEN pm.statuscovidfk = 1 THEN 1 ELSE 0 END AS suspect,
                            CASE WHEN pm.statuscovidfk in (2,3,4,5,6) THEN 1 ELSE 0 END AS terkonfirmasi,
                            CASE WHEN pm.statuscovidfk = 12 THEN 1 ELSE 0 END AS pelakuperjalanan,
                            CASE WHEN pm.statuscovidfk = 7 THEN 1 ELSE 0 END AS discarded,
                            CASE WHEN pm.statuscovidfk = 8 THEN 1 ELSE 0 END AS selesaiisolasi,
                            CASE WHEN pm.statuscovidfk = 9 THEN 1 ELSE 0 END AS kematian,
                            CASE WHEN pm.statuscovidfk = 10 THEN 1 ELSE 0 END AS probable,
                            CASE WHEN pm.statuscovidfk = 11 THEN 1 ELSE 0 END AS kontakerat
                from pelayananmedis_t as pm


                LEFT JOIN statuscovid_m as sc on sc.id = pm.statuscovidfk
                where pm.kddiagnosa is not null
                and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
                and pm.kddiagnosa in ('B34.2','Z03.8') ) as x"));

        $suspect=0;
        $terkonfirmasi=0;
        $pelakuperjalanan=0;
        $discarded=0;
        $selesaiisolasi=0;
        $kematian=0;
        $probable=0;
        $kontakerat=0;
        if(count($data) > 0){
            $suspect = $data[0]->suspect;
            $terkonfirmasi = $data[0]->terkonfirmasi;
            $pelakuperjalanan = $data[0]->pelakuperjalanan;
            $discarded = $data[0]->discarded;
            $selesaiisolasi = $data[0]->selesaiisolasi;
            $kematian = $data[0]->kematian;
            $probable = $data[0]->probable;
            $kontakerat = $data[0]->probable;
        }

        $dataTerKonfirmasi = \DB::select(DB::raw("select  SUM(x.asimtomatic) AS asimtomatic, SUM(x.sakitringan) AS sakitringan,
                SUM(x.sakitsedang) AS sakitsedang,SUM(x.sakitberat) AS sakitberat,SUM(x.sakitkritis) AS sakitkritis
                from (select
                CASE WHEN pm.statuscovidfk = 6 THEN 1 ELSE 0 END AS asimtomatic,
                CASE WHEN pm.statuscovidfk = 2 THEN 1 ELSE 0 END AS sakitringan,
                CASE WHEN pm.statuscovidfk = 3 THEN 1 ELSE 0 END AS sakitsedang,
                CASE WHEN pm.statuscovidfk = 4 THEN 1 ELSE 0 END AS sakitberat,
                CASE WHEN pm.statuscovidfk = 5 THEN 1 ELSE 0 END AS sakitkritis
                from pelayananmedis_t as pm


                LEFT JOIN statuscovid_m as sc on sc.id = pm.statuscovidfk
                where pm.kddiagnosa is not null
                and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
                and pm.kddiagnosa in ('B34.2','Z03.8') ) as x"));

        $asimtomatic=0;
        $sakitringan=0;
        $sakitsedang=0;
        $sakitberat=0;
        $sakitkritis=0;
        if(count($dataTerKonfirmasi) > 0){
            $asimtomatic = $dataTerKonfirmasi[0]->asimtomatic;
            $sakitringan = $dataTerKonfirmasi[0]->sakitringan;
            $sakitsedang = $dataTerKonfirmasi[0]->sakitsedang;
            $sakitberat = $dataTerKonfirmasi[0]->sakitberat;
            $sakitkritis = $dataTerKonfirmasi[0]->sakitkritis;
        }

        $result['tglawal'] = $tglawal;
        $result['tglakhir'] = $tglakhir;
        $result['suspect'] = (int) $suspect;
        $result['terkonfirmasi'] = (int) $terkonfirmasi;
        $result['pelakuperjalanan'] = (int) $pelakuperjalanan;
        $result['kontakerat'] = (int) $kontakerat;
        $result['kematian'] = (int) $kematian;
        $result['discarded'] = (int) $discarded;
        $result['probable'] = (int) $probable;
        $result['selesaiisolasi'] = (int) $selesaiisolasi;
        $result['asimtomatic'] = (int) $asimtomatic;
        $result['sakitringan'] = (int) $sakitringan;
        $result['sakitsedang'] = (int) $sakitsedang;
        $result['sakitberat'] = (int) $sakitberat;
        $result['sakitkritis'] = (int) $sakitkritis;
        return $result;

    }
    public function  getDashboardPegawai(Request $r)
    {
        $jenisPegawai  = DB::select('
                    SELECT
                        case when jp.jenispegawai is null then \'-\' else jp.jenispegawai end as jenis,
                        pg.namalengkap,count( pg.namalengkap) as total,pro.namaprofile
                    FROM
                        pegawai_m AS pg
                    LEFT JOIN jenispegawai_m AS jp ON jp.id = pg.objectjenispegawaifk
                    JOIN profile_m AS pro ON pro.id = pg.profilefk
                    WHERE
                        pg.statusenabled = true
                         group by jp.jenispegawai ,  pg.namalengkap,pro.namaprofile
              ');
        $pendidikan  = DB::select('
                    SELECT
                          case when jp.pendidikan is null then \'-\' else jp.pendidikan end as jenis,
                        pg.namalengkap,count( pg.namalengkap) as total,pro.namaprofile
                    FROM
                        pegawai_m AS pg
                    LEFT JOIN pendidikan_m AS jp ON jp.id = pg.objectpendidikanfk
                    JOIN profile_m AS pro ON pro.id = pg.profilefk
                    WHERE
                        pg.statusenabled = true
                         group by jp.pendidikan ,  pg.namalengkap,pro.namaprofile
                ');
        $jabatan  = DB::select("
                    SELECT
                        case when jp.jabatan is null then '-' else jp.jabatan end as jenis,
                        pg.namalengkap,count( pg.namalengkap) as total,pro.namaprofile
                    FROM
                        pegawai_m AS pg
                    LEFT JOIN jabatan_m AS jp ON jp.id = pg.objectjabatanfk
                    JOIN profile_m AS pro ON pro.id = pg.profilefk
                    WHERE
                        pg.statusenabled = true
                   group by jp.jabatan ,  pg.namalengkap,pro.namaprofile");
        $jk  = DB::select("
                   SELECT
                    case when jp.jeniskelamin is null then '-' else jp.jeniskelamin end as jenis,
                    pg.namalengkap,count( pg.namalengkap) as total,pro.namaprofile
                    FROM
                    pegawai_m AS pg
                    LEFT JOIN jeniskelamin_m AS jp ON jp.id = pg.objectjeniskelaminfk
                    JOIN profile_m AS pro ON pro.id = pg.profilefk
                    WHERE
                    pg.statusenabled = true
                    group by jp.jeniskelamin ,  pg.namalengkap,pro.namaprofile
             ");
        $result['jenispegawai'] = $jenisPegawai;
        $result['pendidikan'] = $pendidikan;
        $result['jeniskelamin'] = $jk;
        $result['jabatan'] = $jabatan;
//        dd($result);
        return $result;
    }
    public function  getDashboardPersediaan(Request $r)
    {
        $trend  = DB::select("select * from (SELECT
                sum(tm.jumlah) AS jml,
                tm.deskripsi
            FROM
                 transaksimedis_t AS tm
            WHERE
                tm.tgltransaksi BETWEEN '$r[tglawal] 00:00'
            AND '$r[tglakhir] 23:59'
            AND tm.emrfk = 2000000001
            and tm.statusenabled=true
            GROUP BY
                tm.deskripsi
            ) as x order by x.jml desc
            limit 10");


        $result['trendobat'] = $trend;
//        $result['pendidikan'] = $pendidikan;
//        $result['jeniskelamin'] = $jk;
//        $result['jabatan'] = $jabatan;
        return $result;
    }
    public function  getDashboardPersediaanStok(Request $r)
    {
//        $profile = DB::table('profile_m')
//            ->whereIn('id',[10632,5]) //5,10629,10628,10632,10630
//            ->get();
////        dd($profile);
//        $data=[];
//        foreach ($profile as $p){
//            $curl = curl_init();
//
//            curl_setopt_array($curl, array(
//                CURLOPT_PORT => $p->portapi != null ? $p->portapi : '',
//                CURLOPT_URL=> $p->apiservice,// 'http://112.109.19.170:8000/service/logistik/get-stok-minimum_global?tglawal=1970-01-01&tglakhir=1970-01-01&leadtime=0&jmlharipesan=0',
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => "",
//                CURLOPT_MAXREDIRS => 10,
//                CURLOPT_TIMEOUT => 30,
//                CURLOPT_SSL_VERIFYHOST => 0,
//                CURLOPT_SSL_VERIFYPEER => 0,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => "GET",
//                CURLOPT_HTTPHEADER => array(
//                    "Content-Type: application/json;",
//                     "X-AUTH-TOKEN: ".  (string)$p->token,
//
//                ),
//            ));
//
//            $response = curl_exec($curl);
//            $err = curl_error($curl);
//
//            curl_close($curl);
//
//            if ($err) {
//                $result= "cURL Error #:" . $err;
//            } else {
//                $result =  json_decode($response);
//            }
//
//            $data [] =array(
//                'namaprofile' => $p->namaprofile,
//                'data' => $result->stok
//            );
//        }
//        $data2 =[];
//        foreach ($data as $d){
//            foreach ($d['data'] as $det){
//                $data2 []=array(
//                    'namaprofile' =>$d['namaprofile'],
//                    'id' =>$det->id,
//                    'namaproduk' =>$det->namaproduk,
//                    'satuanstandar' =>$det->satuanstandar,
//                    'qtyproduk' => (float) $det->qtyproduk,
//                );
//            }
//
//        }
        $data2 =[];
        $data = collect(DB::select("select ts.*,pro.namaprofile,prd.namaproduk,ss.satuanstandar
                 from transaksistok_t as ts
                join profile_m as pro on pro.id=ts.profilefk
                join produk_m as prd on prd.id= ts.produkfk
                left join satuanstandar_m as ss on ss.id= ts.satuanstandarfk"));
//        dd($data2);
        foreach ($data as $det) {
            $data2 [] = array(
                'namaprofile' =>$det->namaprofile,
                'norec' => $det->norec,
                'namaproduk' => $det->namaproduk,
                'satuanstandar' => $det->satuanstandar,
                'total' => (float)$det->total,
                'tglupdate' => $det->tglupdate,
            );
        }
        return view('module.shared.detail-table-stok',compact('data2'));
//        return $data2;

    }
    public function  getKetersediaanKamar(Request $r)
    {
//        $data = \DB::select(DB::raw("SELECT
//                            pr.id,pr.namaprofile,
//                            kl.namakelas
//
//                        FROM
//                            kelas_m AS kl
//                        JOIN kamar_m AS kmr ON kmr.objectkelasfk = kl.id
//                        JOIN tempattidur_m AS tt ON tt.objectkamarfk = kmr.id
//                        JOIN profile_m AS pr ON pr.id = tt.kdprofile
//                        WHERE
//                            tt.objectstatusbedfk = 2"));
        $data = \DB::select(DB::raw("
                   select x.namaprofile,
                x.provinsi,
                x.tglupdate,
                sum(x.kls1) as kls1 ,
                sum(x.kls2) as kls2 ,
                sum(x.kls3) as kls3,
                sum(x.vip) as vip ,
                sum(x.hcu) as hcu,
                sum(x.icu) as icu,
                sum(x.iccu) as iccu,
                sum(x.nicu) as nicu,
                sum(x.picu) as picu,
                sum(x.isolasi) as isolasi
                from (
                SELECT distinct
                                    pr.namaprofile,
                                    prf.provinsi,
                                    tt.tglupdate,
                            case when kl.id =3 then tt.tersedia else 0 end as kls1,
                            case when kl.id =2 then tt.tersedia else 0 end as kls2,
                            case when kl.id =1 then  tt.tersedia else 0 end as kls3,
                            case when kl.id =5 then tt.tersedia else 0 end as vip,
                            case when kl.id =14 then  tt.tersedia else 0 end as hcu,
                            case when kl.id =11 then tt.tersedia else 0 end as icu,
                            case when kl.id =9 then  tt.tersedia else 0 end as iccu,
                            case when kl.id =12 then  tt.tersedia else 0 end as nicu,
                            case when kl.id =13 then  tt.tersedia else 0 end as picu,
                            case when kl.id =10 then tt.tersedia else 0 end as isolasi
                                FROM
                            kelas_m AS kl
                            JOIN ketersediaantempattidur_t AS tt ON tt.objectkelasfk = kl.id
                            JOIN profile_m AS pr ON pr.id = tt.profilefk
                            LEFT JOIN provinsi_m AS prf ON prf.id = pr.provinsifk
                           order by tglupdate desc
                ) as x
                group by x.namaprofile,
                x.provinsi,
                x.tglupdate
                "));
        $data10 = [];
//        dd($data);
        $samateu = false;
        $tgl = date('2000-01-01 00:00');
        $dataNyawaTerakhir=[];
        foreach ($data as $item){
            $samateu = false;
            $i = 0;
            foreach ($dataNyawaTerakhir as $itemsss){
                if ($item->namaprofile == $dataNyawaTerakhir[$i]['namaprofile'] ){
                    $samateu = true;
//                    $dataNyawaTerakhir[$i]['tglupdate']  = $item->tglupdate;
                    $dataNyawaTerakhir[$i]['kls1'] =   (float) $dataNyawaTerakhir[$i]['kls1'] +  (float) $item->kls1;
                    $dataNyawaTerakhir[$i]['kls2'] =   (float) $dataNyawaTerakhir[$i]['kls2'] +  (float) $item->kls2;
                    $dataNyawaTerakhir[$i]['kls3'] =   (float) $dataNyawaTerakhir[$i]['kls3'] +  (float) $item->kls3;
                    $dataNyawaTerakhir[$i]['icu'] =   (float) $dataNyawaTerakhir[$i]['icu'] +  (float) $item->icu;
                    $dataNyawaTerakhir[$i]['nicu'] =   (float) $dataNyawaTerakhir[$i]['nicu'] +  (float) $item->nicu;
                    $dataNyawaTerakhir[$i]['picu'] =   (float) $dataNyawaTerakhir[$i]['picu'] +  (float) $item->picu;
                    $dataNyawaTerakhir[$i]['vip'] =   (float) $dataNyawaTerakhir[$i]['vip'] +  (float) $item->vip;
                    $dataNyawaTerakhir[$i]['iccu'] =   (float) $dataNyawaTerakhir[$i]['iccu'] +  (float) $item->iccu;
                    $dataNyawaTerakhir[$i]['isolasi'] =   (float) $dataNyawaTerakhir[$i]['isolasi'] +  (float) $item->isolasi;
                    $dataNyawaTerakhir[$i]['hcu'] =   (float) $dataNyawaTerakhir[$i]['hcu'] +  (float) $item->hcu;
//                    }
                }
                $i = $i + 1;
            }
            if ($samateu == false){
                $dataNyawaTerakhir[] = array(
                    'provinsi' => $item->provinsi,
                    'namaprofile' => $item->namaprofile,
                    'tglupdate' => $item->tglupdate,
                    'kls1' => $item->kls1,
                    'kls2' => $item->kls2,
                    'kls3' => $item->kls3,
                    'vip' => $item->vip,
                    'hcu' => $item->hcu,
                    'icu' => $item->icu,
                    'iccu' => $item->iccu,
                    'nicu' =>  $item->nicu,
                    'picu' => $item->picu,
                    'isolasi' => $item->isolasi,
                );
            }
        }

//        foreach ($data  as $d){
//            $data10[] = array(
//                'provinsi' => $d->provinsi,
//                'namaprofile' => $d->namaprofile,
//                'kls1' => $d->kls1,
//                'kls2' => $d->kls2,
//                'kls3' => $d->kls3,
//                'vip' => $d->vip,
//                'hcu' => $d->hcu,
//                'icu' => $d->icu,
//                'iccu' => $d->iccu,
//                'nicu' => $d->nicu,
//                'picu' => $d->picu,
//                'isolasi' => $d->isolasi,
//
//            );
//        }
        $data10 = $dataNyawaTerakhir;
//        dd($dataNyawaTerakhir);
        return view('module.shared.detail-table-kamar',compact('data10'));


//        $data10 = [];
//        $kls1 = 0;
//        $kls2 = 0;
//        $kls3 = 0;
//        $vip = 0;
//        $hcu = 0;
//        $icu = 0;
//        $iccu = 0;
//        $nicu = 0;
//        $picu = 0;
//        $isolasi = 0;
//        $sama = false;
//
//
//        foreach ($data as $item) {
//            $sama = false;
//            $i = 0;
//            foreach ($data10 as $hideung) {
//                if ($item->namaprofile == $data10[$i]['namaprofile']) {
//                    $sama = true;
//                    $jml = (float)$hideung['jumlah'] + 1;
//                    $data10[$i]['jumlah'] = $jml;
//                    if ($item->namakelas == 'Kelas I') {
//                        $data10[$i]['kls1'] = (float)$hideung['kls1'] + 1;
//                    }
//                    if ($item->namakelas == 'Kelas II') {
//                        $data10[$i]['kls2'] = (float)$hideung['kls2'] + 1;
//                    }
//                    if ($item->namakelas == 'Kelas III') {
//                        $data10[$i]['kls3'] = (float)$hideung['kls3'] + 1;
//                    }
//                    if ($item->namakelas == 'VIP') {
//                        $data10[$i]['vip'] = (float)$hideung['vip'] + 1;
//                    }
//                    if ($item->namakelas == 'ICU') {
//                        $data10[$i]['icu'] = (float)$hideung['icu'] + 1;
//                    }
//                    if ($item->namakelas == 'ICCU') {
//                        $data10[$i]['iccu'] = (float)$hideung['iccu'] + 1;
//                    }
//                    if ($item->namakelas == 'Ruang Isolasi') {
//                        $data10[$i]['kls2'] = (float)$hideung['kls2'] + 1;
//                    }
//                    if ($item->namakelas == 'NICU') {
//                        $data10[$i]['isolasi'] = (float)$hideung['isolasi'] + 1;
//                    }
//                    if ($item->namakelas == 'PICU') {
//                        $data10[$i]['picu'] = (float)$hideung['picu'] + 1;
//                    }
//                    if ($item->namakelas == 'HCU') {
//                        $data10[$i]['hcu'] = (float)$hideung['hcu'] + 1;
//                    }
//                }
//                $i = $i + 1;
//            }
//
//            if ($sama == false) {
//                if ($item->namakelas == 'Kelas I') {
//                    $kls1 = 1;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'Kelas II') {
//                    $kls1 = 0;
//                    $kls2 = 1;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'Kelas III') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 1;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'VIP') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 1;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'ICU') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 1;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'ICCU') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 1;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'Ruang Isolasi') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 1;
//                }
//                if ($item->namakelas == 'NICU') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 1;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'PICU') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 0;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 1;
//                    $isolasi = 0;
//                }
//                if ($item->namakelas == 'HCU') {
//                    $kls1 = 0;
//                    $kls2 = 0;
//                    $kls3 = 0;
//                    $vip = 0;
//                    $hcu = 1;
//                    $icu = 0;
//                    $iccu = 0;
//                    $nicu = 0;
//                    $picu = 0;
//                    $isolasi = 0;
//                }
//
//                $data10[] = array(
//                    'namaprofile' => $item->namaprofile,
//                    'jumlah' => 1,
//                    'kls1' => $kls1,
//                    'kls2' => $kls2,
//                    'kls3' => $kls3,
//                    'vip' => $vip,
//                    'hcu' => $hcu,
//                    'icu' => $icu,
//                    'iccu' => $iccu,
//                    'nicu' => $nicu,
//                    'picu' => $picu,
//                    'isolasi' => $isolasi,
//
//                );
//            }
//
//            foreach ($data10 as $key => $row) {
////                if( $row['jumlah'] )
//                $count[$key] = $row['jumlah'];
//            }
//
//            array_multisort($count, SORT_DESC, $data10);
//        }
        $data10 = $data;
//        dd($data10);
        return view('module.shared.detail-table-kamar',compact('data10'));
    }
    public function  getDetailCovid(Request $r){
        $tglawal = $r['tglawal'];
        $tglakhir = $r['tglakhir'];
        $param = $r['param'];
        $statuscovidfk = '';
        if($param == 'Suspek'){
            $statuscovidfk = '(1)';
        }
        if($param == 'Probable'){
            $statuscovidfk = '(10)';
        }
        if($param == 'Discarded'){
            $statuscovidfk = '(7)';
        }
        if($param == 'Selesai Isolasi'){
            $statuscovidfk = '(8)';
        }
        if($param == 'Kematian'){
            $statuscovidfk = '(9)';
        }
        if($param == 'Sakit Sedang'){
            $statuscovidfk = '(2,3,4,5,6)';
        }

        $data = collect(DB::select("select
                pm.norec,pm.noregistrasi,pm.tglregistrasi,pm.norm,ps.namapasien,pr.namaprofile
                from pelayananmedis_t as pm
                join pasien_m as ps on ps.id= pm.pasienfk
                join profile_m as pr on pr.id= pm.profilefk
                LEFT JOIN statuscovid_m as sc on sc.id = pm.statuscovidfk
                where pm.kddiagnosa is not null
                and pm.statuscovidfk in $statuscovidfk
                and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'
                and pm.kddiagnosa in ('B34.2','Z03.8')
                order by pm.tglregistrasi desc"));

        return view('module.shared.detail-covid',compact('data'));

    }
    public function  getDetailKun(Request $r){
        $tglawal = $r['tglawal'];
        $tglakhir = $r['tglakhir'];
        $param = $r['param'];
        // $data = collect(DB::select("SELECT
        //             pm.norec,pm.noregistrasi,pm.tglregistrasi,peld.tglmasuk,pm.norm,ps.namapasien,pr.namaprofile,
        //         case when ru.kodeexternal is null or ru.kodeexternal='RI' then 'RAWAT INAP' else ru.ruangan end as ruangan,
        //             case when ru.kodeexternal is null or ru.kodeexternal='RI' then 'RI' else ru.kodeexternal end as kode
        //             FROM
        //             pelayananmedisdetail_t as peld
        //         join pelayananmedis_t as pm on pm.norec= peld.pelayananmedisfk
        //             left join ruangan_m as ru on ru.id =peld.ruanganfk
        //             join pasien_m as ps on ps.id= pm.pasienfk
        //             join profile_m as pr on pr.id= pm.profilefk
        //             WHERE
        //             peld.tglmasuk BETWEEN  '$tglawal 00:00' and '$tglakhir 23:59'
        //             and peld.statusenabled=true
        //             and (case when ru.kodeexternal is null or ru.kodeexternal='RI' then 'RI' else ru.kodeexternal end) = '$param'"));
         $data = collect(DB::select("SELECT
                DISTINCT
                    pm.norec,pm.noregistrasi,pm.tglregistrasi,pm.tglregistrasi as tglmasuk,pm.norm,ps.namapasien,pr.namaprofile,
                    case when ru.kodeexternal is null or ru.kodeexternal='RI' then 'RAWAT INAP' else ru.ruangan end as ruangan,
                    case when ru.kodeexternal is null or ru.kodeexternal='RI' then 'RI' else ru.kodeexternal end as kode
                    FROM
                    pelayananmedisdetail_t as peld
                    join pelayananmedis_t as pm on pm.norec= peld.pelayananmedisfk
                    left join ruangan_m as ru on ru.id =peld.ruanganfk
                    join pasien_m as ps on ps.id= pm.pasienfk
                    join profile_m as pr on pr.id= pm.profilefk
                    WHERE
                    pm.tglregistrasi BETWEEN  '2020-09-05 00:00' and '2020-09-09 23:59'
                    and peld.statusenabled=true
                    and (case when ru.kodeexternal is null or ru.kodeexternal='RI' then 'RI' else ru.kodeexternal end) = '$param'"));
        return view('module.shared.detail-kunjungan',compact('data'));

    }
    public function  getDetailBed(Request $r){
        $tglawal = $r['tglawal'];
        $tglakhir = $r['tglakhir'];
        $param = $r['param'];
        $data = collect(DB::select("SELECT
            DISTINCT
                tt.tersedia,
                pr.namaprofile,
                kl.namakelas,
                prf.provinsi,
                tt.tglupdate
            FROM
                kelas_m AS kl
            JOIN ketersediaantempattidur_t AS tt ON tt.objectkelasfk = kl.id
            JOIN profile_m AS pr ON pr.id = tt.profilefk
            LEFT JOIN provinsi_m AS prf ON prf.id = pr.provinsifk
             where kl.namakelas= '$param'"));

        return view('module.shared.detail-bed',compact('data'));

    }
}
