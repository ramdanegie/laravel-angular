<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Master\MasterController;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Traits\InternalList;
use DB;

class GenericController extends MasterController
{

    public function GetHubunganKeluarga(Request $request)
    {
        $hubunganKeluarga = \DB::table('hubungankeluarga_m as hb')
            ->select('hb.id','hb.namaexternal as namaexternal')
            ->where('statusenabled', true)
            ->get();


        $result = array(
            'hubunganKeluarga' => $hubunganKeluarga,

        );

        return $this->respond($result);
    }

    public function GetStatusBawa(Request $request)
    {
        $statusBawa = \DB::table('rm_statusbawa_m as sb')
            ->select('sb.id','sb.name')
            ->where('statusenabled', true)
            ->get();


        $result = array(
            'statusBawa' => $statusBawa,

        );

        return $this->respond($result);
    }

    public function GetJenisKelamin(Request $request)
    {
        $jenisKelamin = \DB::table('jeniskelamin_m as jk')
            ->select('jk.id','jk.namaexternal as name')
            ->where('statusenabled', true)
            ->get();


        $result = array(
            'jenisKelamin' => $jenisKelamin,

        );

        return $this->respond($result);
    }

    public function GetPemeriksaanTriage(Request $request)
    {
        $pemeriksaanTriage = \DB::table('rm_pemeriksaantriage_m as pt')
            ->select('pt.id','pt.jenispemeriksaan as jenisPemeriksaan','pt.namatriage as namaTriage')
            ->where('statusenabled', true)
            ->get();


        $result = array(
            'jenisPemeriksaan' => $pemeriksaanTriage,

        );

        return $this->respond($result);
    }

    public function GetKategoriTriage(Request $request)
    {
        $hasilKategoriTriage = \DB::table('hasilkategoritriase_m as htt')
            ->select('htt.id','htt.namahasilkategoritriase as namaHasilKategoriTriase')
            ->where('statusenabled', true)
            ->get();


        $result = array(
            'hasilKategoriTriage' => $hasilKategoriTriage,

        );

        return $this->respond($result);
    }

    public function TestArray(Request $request) {

//        DB::beginTransaction();



        $r_R=$request[''];


        //Detail Hasil Triase
        $arr = json_decode($request->getContent(),true);


        foreach($arr['detailHasilTriase'] as $item) { //foreach element in $arr
            $pemeriksaanTriage = $item['pemeriksaanTriage']['id']; //etc
            $norec=$item['noRec'];

        }



            $result = array(
                "status" => 400,
//                "message"  => $pemeriksaanTriage);
                "message"  => $norec);

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function GetHasilTriase(Request $request)
    {
        $hasilTriase = \DB::table('hasiltriase_t as ht')
            ->leftjoin('pengantarpasien_t as pp','pp.objecthasiltriasefk','=','ht.norec')

            ->select(
                'ht.norec',
                'generatetriase',
                'hasiltriasewaktu',
                'objectkategorihasiltriasefk',
                'pasien',
                'tanggalmasuk',
                'namapasien',
                'statuspasien',
                'beratbadan',
                'tekanandarah',
                'suhu',
                'nadi',
                'pernapasan',
                DB::raw('ROW_NUMBER() OVER(ORDER BY ht.norec asc) AS no')
            )

            ->orderBy('no')
            ->take(50);

            if (isset($request['noRec']) && $request['noRec'] != "" && $request['noRec'] != "undefined") {
                $hasilTriase = $hasilTriase->where('ht.norec', $request['noRec']);
            }

            if (isset($request['namaPasien']) && $request['namaPasien'] != "" && $request['namaPasien'] != "undefined") {
                $hasilTriase = $hasilTriase->where('ht.namapasien', 'ilike','%'. $request['namaPasien'].'%');
            }

            if (isset($request['tglMasukAwal']) && $request['tglMasukAwal'] != "" && $request['tglMasukAwal'] != "undefined") {
                if (isset($request['tglMasukAkhir']) && $request['tglMasukAkhir'] != "" && $request['tglMasukAkhir'] != "undefined") {
                    $hasilTriase = $hasilTriase->whereBetween('ht.tanggalmasuk', [$request['tglMasukAwal'], $request['tglMasukAkhir']]);
                }
            }


            $hasilTriase=$hasilTriase->get();


        $hasilTriaseTandaVital = \DB::table('hasiltriase_t as ht')
            ->select('beratbadan','tekanandarah','suhu','nadi','pernapasan','norec')
            ->where('ht.norec', $request['noRec'])
            ->get();

        $detailhasilTriase = \DB::table('detailhasiltriase_t as dht')
            ->select('norec','objectpemeriksaantriagefk as id')
            ->where('dht.objecthasiltriasefk', $request['noRec'])
            ->get();

        $pengantarPasien = \DB::table('pengantarpasien_t as pp')
            ->leftjoin('hubungankeluarga_m as hk','hk.id','=','pp.objecthubungankeluargafk')
            ->leftjoin('rm_statusbawa_m as sb','sb.id','=','pp.objectstatusbawafk')
            ->select(
                'pp.norec',
                'pp.namakeluarga',
                'pp.objecthubungankeluargafk',
                'pp.tgllahir',
                'pp.tglkejadian',
                'pp.tempatkejadian',
                'pp.objecthasiltriasefk',
                'pp.objectjeniskelaminfk',
                'pp.objectstatusbawafk',
                'sb.id',
                'sb.name',
                'hk.id',
                'hk.namaexternal')
            ->where('pp.objecthasiltriasefk', $request['noRec'])
            ->get();

        $result = array(
            'hasilTriase' => $hasilTriase,
            'hasilTriaseTandaVital'=>$hasilTriaseTandaVital,
            'detailHasilTriase'=>$detailhasilTriase,
            'pengantarPasien'=>$pengantarPasien
        );

        return $this->respond($result);
    }

    public function GetHasilTriaseTandaVital(Request $request)
    {
        $hasilTriase = \DB::table('hasiltriase_t as ht')
//            ->leftjoin('detailhasiltriase_t as dht','dht.objecthasiltriasefk','=','ht.norec')
            ->select('beratbadan as Berat Badan','tekanandarah as Tekanan Darah','suhu as Suhu','nadi as Nadi','pernapasan as Pernapasan')
            ->where('ht.norec', $request['noRec'])
            ->get();


        $result = array(
            'hasilTriase' => $hasilTriase,

        );

        return $this->respond($result);
    }

    public function GetDataPasien(Request $request)
    {
        $dataPasien = \DB::table('pasien_m as p')
            ->select('*')
            ->where('p.nocm', $request['noCm'])
            ->get();


        $result = array(
            'dataPasien' => $dataPasien,

        );

        return $this->respond($result);
    }

    public function GetKamars(Request $request)
    {
        $kamars = \DB::table('kamar_m as k')
            ->leftjoin('kelas_m as kls','kls.id','=','k.objectkelasfk')
            ->leftjoin('ruangan_m as r','r.id','=','k.objectruanganfk')
            ->select(
                'k.id',
                'k.kdprofile',
                'k.statusenabled',
                'k.kodeexternal',
                'k.namaexternal',
                'k.norec',
                'k.reportdisplay',
                'k.kdkamar',
                'k.namakamar',
                'k.qkamar',
                'k.qtybed',
                'k.jumlakamarisi',
                'k.jumlakamarkosong',
                'k.keterangan',
                'k.tglupdate',
                'k.objectkelasfk',
                'kls.namakelas as kelas',
                'r.namaruangan as ruangan',

                DB::raw('ROW_NUMBER() OVER(ORDER BY k.norec asc) AS no'))
            
            // ->where('k.statusenabled', 'true')
            ->orderBy('no')
            ->take(50);
            
            if (isset($request['namaKamar']) && $request['namaKamar'] != "" && $request['namaKamar'] != "undefined") {
                $kamars = $kamars->where('k.namakamar', 'ilike','%'. $request['namaKamar'].'%');
                
            }

            $kamars=$kamars->get();

        $result = array(
            'kamars' => $kamars,

        );

        return $this->respond($result);
    }

    public function GetKelass(Request $request)
    {
        $kelass = \DB::table('kelas_m as k')
            ->select('k.id','k.namakelas');
            // ->where('statusenabled', true)
            // ->get();


        if (isset($request['statusEnabled']) && $request['statusEnabled'] != "" && $request['statusEnabled'] != "undefined") {
            $kelass = $kelass->where('k.statusenabled', $request['statusEnabled']);
            
        }

        $kelass=$kelass->get();

        $result = array(
            'kelass' => $kelass,

        );

        return $this->respond($result);
    }

    public function GetRuangans(Request $request)
    {
        $ruangans = \DB::table('ruangan_m as k')
            ->select('k.id','k.namaruangan');
            // ->where('statusenabled', true)
            // ->get();


        if (isset($request['statusEnabled']) && $request['statusEnabled'] != "" && $request['statusEnabled'] != "undefined") {
            $ruangans = $ruangans->where('k.statusenabled', $request['statusEnabled']);
            
        }

        $ruangans=$ruangans->get();

        $result = array(
            'ruangans' => $ruangans,

        );

        return $this->respond($result);
    }

    public function GetKelompokKerjas(Request $request)
    {
        $kelompokKerjas = \DB::table('kelompokkerja_m as kk')
        ->select(
                'kk.id',
                'kk.kdprofile',
                'kk.statusenabled',
                'kk.kodeexternal',
                'kk.namaexternal',
                'kk.norec',
                'kk.reportdisplay',
                'kk.kdkelompokkerja',
                'kk.kelompokkerja',
                'kk.qkelompokkerja',
                'kk.objectkelompokkerjahead',

                DB::raw('ROW_NUMBER() OVER(ORDER BY kk.id asc) AS no'));
            // ->where('kk.statusenabled', $request['statusenabled'])
            // ->get();


         if (isset($request['namakelompokkerja']) && $request['namakelompokkerja'] != "" && $request['namakelompokkerja'] != "undefined") {
            $kelompokKerjas = $kelompokKerjas->where('kk.kelompokkerja', 'ilike','%'. $request['namakelompokkerja'].'%');
            
        }

        $kelompokKerjas=$kelompokKerjas->get();

        $result = array(
            'kelompokkerjas' => $kelompokKerjas,

        );

        return $this->respond($result);
    }

    public function GetKelompokKerjaHeads(Request $request)
    {
        $kelompokKerjas = \DB::table('kelompokkerjahead_m as kkh')
        ->select(
                'kkh.id',
                'kkh.kdprofile',
                'kkh.statusenabled',
                'kkh.kodeexternal',
                'kkh.namaexternal',
                'kkh.norec',
                'kkh.reportdisplay',
                'kkh.kdkelompokkerjahead',
                'kkh.kelompokkerjahead',
                'kkh.qkelompokkerjahead',

                DB::raw('ROW_NUMBER() OVER(ORDER BY kkh.id asc) AS no'));
            // ->where('kk.statusenabled', $request['statusenabled'])
            // ->get();


         if (isset($request['namakelompokkerja']) && $request['namakelompokkerja'] != "" && $request['namakelompokkerja'] != "undefined") {
            $kelompokKerjas = $kelompokKerjas->where('kkh.kelompokkerjahead', 'ilike','%'. $request['namakelompokkerja'].'%');
            
        }

        $kelompokKerjas=$kelompokKerjas->get();

        $result = array(
            'kelompokkerjas' => $kelompokKerjas,

        );

        return $this->respond($result);
    }

    public function GetRouteFarmasis(Request $request)
    {
        $routefarmasis = \DB::table('routefarmasi as rf')
        ->select(
                'rf.id',
                'rf.kdprofile',
                'rf.statusenabled',
                'rf.kodeexternal',
                'rf.namaexternal',
                'rf.norec',
                'rf.reportdisplay',
                'rf.name',

                DB::raw('ROW_NUMBER() OVER(ORDER BY rf.id asc) AS no'));
            

            
         if (isset($request['routefarmasi']) && $request['routefarmasi'] != "" && $request['routefarmasi'] != "undefined") {
            $routefarmasis = $routefarmasis->where('rf.name', 'ilike','%'. $request['routefarmasi'].'%');
            
        }

        $routefarmasis=$routefarmasis->get();

        $result = array(
            'routefarmasis' => $routefarmasis,

        );

        return $this->respond($result);
    }

}
