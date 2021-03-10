<?php


namespace App\Http\Controllers\BedMonitor;
use App\Http\Controllers\ApiController;
use App\Traits\Valet;
use DB;
use Illuminate\Http\Request;

class BedMonitorController extends ApiController {
    use Valet;

    public function __construct(){
        parent::__construct($skip_authentication = true);
    }

    public function getKetersediaanTempatTidurViewBM (Request $request){
        $namaruangan= $request['namaruangan'];
        $idkelas= $request['idkelas'];
        $dataLogin = $request->all();
        if($namaruangan == "" && $idkelas == ""){
            $data = DB::select(DB::raw("select top 1 COUNT(x.idstatusbed) as kamartotal, SUM(x.kamarisi) as kamarisi, sum(x.kamarkosong) as kamarkosong, 
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
                 where ru.objectdepartemenfk in (16,35) and ru.statusenabled=1
				 and km.statusenabled=1 and tt.statusenabled=1)as x "),
                array(
//                    'namaruangan' => $namaruangan,
//                    'idkelas' => $idkelas,
                )
            );
        } elseif ($namaruangan != "" && $idkelas == ""){
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
                 where ru.objectdepartemenfk in (16,35) and ru.namaruangan=:namaruangan)as x"),
                array(
                    'namaruangan' => $namaruangan,
//                    'idkelas' => $idkelas,
                )
            );
        } elseif ($namaruangan == "" && $idkelas != ""){
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
                 where ru.objectdepartemenfk in (16,35) and kl.id=:idkelas)as x"),
                array(
//                    'namaruangan' => $namaruangan,
                    'idkelas' => $idkelas,
                )
            );
        } else {
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
                 where ru.objectdepartemenfk in (16,35) and ru.namaruangan=:namaruangan and kl.id=:idkelas)as x"),
                array(
                    'namaruangan' => $namaruangan,
                    'idkelas' => $idkelas,
                )
            );
        }
        return $this->respond($data);
    }

    public function viewBedBM(Request $request){
        $data= \DB::table('tempattidur_m as tt')
            ->leftjoin('kamar_m as km', 'km.id', '=', 'tt.objectkamarfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'km.objectruanganfk')
            ->leftjoin('statusbed_m as sb', 'sb.id', '=', 'tt.objectstatusbedfk')
            ->leftjoin('kelas_m as kl', 'kl.id', '=', 'km.objectkelasfk')
            ->select('ru.id as idruangan','ru.namaruangan','km.id as idkamar','km.namakamar','tt.id as idtempattidur',
                'tt.reportdisplay','tt.nomorbed','sb.id as idstatusbed','sb.statusbed','kl.id as idkelas','kl.namakelas')
            ->whereIn('ru.objectdepartemenfk',array(16,35))
            ->where('ru.statusenabled',true)
            ->where('km.statusenabled',true)
            ->where('tt.statusenabled',true);

        if(isset($request['namaruangan']) && $request['namaruangan']!="" && $request['namaruangan']!="undefined"){
            $data = $data->where('ru.namaruangan','ilike','%'. $request['namaruangan'] .'%');
        };
        if(isset($request['namakamar']) && $request['namakamar']!="" && $request['namakamar']!="undefined"){
            $data = $data->where('km.namakamar','ilike','%'. $request['namakamar'] .'%');
        };
        if(isset($request['idkelas']) && $request['idkelas']!="" && $request['idkelas']!="undefined"){
            $data = $data->where('kl.id', $request['idkelas']);
        };
        if(isset($request['namabed']) && $request['namabed']!="" && $request['namabed']!="undefined"){
            $data = $data->where('tt.reportdisplay','ilike','%'. $request['namabed'] .'%');
        };
        if(isset($request['idstatusbed']) && $request['idstatusbed']!="" && $request['idstatusbed']!="undefined"){
            $data = $data->where('sb.id', $request['idstatusbed']);
        };
        $data = $data->get();
        return $this->respond($data);
    }

    public function getDataProfileLogin(Request $request) {

        $dataData = \DB::table('profile_m as pf')
            ->select(DB::raw("pf.login"))
            ->where('pf.statusenabled',1);
        $dataData = $dataData->first();
        $result = array(
            'profile' => $dataData,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

}