<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
class MasterControl extends Controller
{



	 
	public function GetDokter(){

		$query = "
				SELECT 
				jenispegawai_m.jenispegawai,
				pegawai_m.id,
				pegawai_m.namalengkap


				from pegawai_m
				INNER JOIN jenispegawai_m

				on jenispegawai_m.id = pegawai_m.objectjenispegawaifk

				 WHERE pegawai_m.objectjenispegawaifk = 1
				 AND pegawai_m.statusenabled =1

				 order by namalengkap
		";

		$data = DB::select($query);



		// $data = DB::table("pegawai_m")
		// ->join("jenispegawai_m")
		// ->where("namalengkap","like","dr.%")
		// ->where("statusenabled",1)
		// ->orderBy("namalengkap")
		// ->get();

		return response()->json($data);
	}	

	public function GetPasien(Request $req){
		$data = DB::table("pasien_m")
		->where("nocm",$req["rm"])
		->first();

		if (!$data) {
			return response()->json(array("messages"=>"RM tidak ditemukan ..."));
		 
		}else{
		return response()->json($data);

		}

	}	

	public function GetObat(){
		$data = DB::select("
			SELECT
				prd.namaproduk,
				jp.jenisproduk 
			FROM
				produk_m AS prd
				JOIN detailjenisproduk_m AS djp ON djp.id= prd.objectdetailjenisprodukfk
				JOIN jenisproduk_m AS jp ON jp.id= djp.objectjenisprodukfk 
			WHERE
				jp.id IN ( 97, 283 ) 
				AND prd.statusenabled =1
			");

		return response()->json($data);
	}

	public function GetPasienDetail(Request $req){
		$rm =$req["rm"];
		$data = DB::select("
		SELECT 
				antrianpasiendiperiksa_t.tglregistrasi,
				antrianpasiendiperiksa_t.tgldipanggildokter,
				antrianpasiendiperiksa_t.tgldipanggilsuster,
				pasien_m.nocm as no_rm,
				pasien_m.tgllahir,
				pasien_m.namapasien,
				pasien_m.tempatlahir,
				pegawai_m.id AS idpegawai,
				pegawai_m.namalengkap AS namapegawai,
				ruangan_m.id AS idruangan,
				ruangan_m.namaruangan,
				ruangan_m.lokasiruangan,
				jenispelayanan_m.jenispelayanan AS jenis,
				pasiendaftar_t.noregistrasi AS no_registrasi,
				pasiendaftar_t.* 
			FROM
				pasiendaftar_t
				INNER JOIN pasien_m ON pasien_m.id = pasiendaftar_t.nocmfk
				left JOIN antrianpasiendiperiksa_t on antrianpasiendiperiksa_t.noregistrasifk = pasiendaftar_t.norec
				LEFT JOIN pegawai_m ON pegawai_m.id = pasiendaftar_t.objectpegawaifk
				left  JOIN ruangan_m ON ruangan_m.id = pasiendaftar_t.objectruanganlastfk
				LEFT JOIN jenispelayanan_m ON jenispelayanan_m.id = pasiendaftar_t.jenispelayanan 
				
 			WHERE
 				pasiendaftar_t.noregistrasi IN (
 				SELECT TOP
 					1 a.noregistrasi 
 				FROM
 					pasiendaftar_t a
 					left JOIN pasien_m ON pasien_m.id = a.nocmfk 
 				WHERE
 					pasien_m.nocm = '".$rm."'
 				ORDER BY
 					a.tglregistrasi DESC 
 				)  
				
			ORDER BY
				pasiendaftar_t.tglregistrasi,
				pasien_m.nocm DESC
			");

		if (!$data) {
			return response()->json(array("messages"=>"RM tidak ditemukan ..."));
		 
		}else{
		return response()->json(array("result"=>$data));

		}

	}

	

	public function GetUnit(Request $req){
		$data = DB::table("form_indikator_m")
		->select("unit")
		->groupBy("unit")
		->get();

		return response()->json($data);

	}

	public function GetUnitRuangan(Request $req){
		$data = DB::table("form_indikator_m");
		
		 if(isset($req['unit']) && $req['unit']!="" && $req['unit']!="undefined"){
            $data = $data->where('unit','=', $req['unit']);
        };

		$data = $data->get();

		return response()->json($data);

	}
    public function GetPasienMeninggal()
    {
        $data = \DB::table('pasiendaftar_t as pd')
        ->join ('pasien_m as ps','ps.id','=','pd.nocmfk')
        ->join ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
        ->leftjoin ('statuskeluar_m as sk','sk.id','=','pd.objectstatuskeluarfk')
        ->leftjoin ('statuspulang_m as sp','sp.id','=','pd.objectstatuspulangfk')
        ->leftjoin ('penyebabkematian_m as pk','pk.id','=','pd.objectpenyebabkematianfk')
        ->select(DB::raw("pd.tglregistrasi,pd.noregistrasi,ps.nocm,ps.namapasien,jk.jeniskelamin,ps.tgllahir,
           sk.statuskeluar,sp.statuspulang,pd.namalengkapambilpasien,
           case when pd.objectpenyebabkematianfk = 4 then pd.keteranganpenyebabkematian else pk.penyebabkematian end as penyebabkematian,
           pd.tglmeninggal"))
        ->where('pd.objectstatuskeluarfk', 5);

        return response()->json($data);
    }

    public function GetDiagnosa(Request $req){
    	$data = DB::select("
    		SELECT 
				pasien_m.nocm,
				pasien_m.namapasien,
				pd.noregistrasi,
				pd.tglregistrasi,
				apd.objectruanganfk,
				ru.namaruangan,
				apd.norec AS norec_apd,
				ddp.objectdiagnosafk,
				dg.kddiagnosa,
				dg.namadiagnosa,
				ddp.tglinputdiagnosa,
				ddp.objectjenisdiagnosafk,
				jd.jenisdiagnosa,
				dp.norec AS norec_diagnosapasien,
				ddp.norec AS norec_detaildpasien,
				ddp.tglinputdiagnosa,
				pg.namalengkap,
				dp.ketdiagnosis,
				ddp.keterangan,
				dg.*,
				dp.iskasusbaru,
				dp.iskasuslama 
			FROM
				pasiendaftar_t AS pd
				INNER JOIN pasien_m on pasien_m.id = pd.nocmfk
				INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
				INNER JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
				INNER JOIN diagnosapasien_t AS dp ON dp.noregistrasifk = apd.norec
				INNER JOIN detaildiagnosapasien_t AS ddp ON ddp.objectdiagnosapasienfk = dp.norec
				INNER JOIN diagnosa_m AS dg ON dg.id = ddp.objectdiagnosafk
				INNER JOIN jenisdiagnosa_m AS jd ON jd.id = ddp.objectjenisdiagnosafk
				LEFT JOIN pegawai_m AS pg ON pg.id = ddp.objectpegawaifk
				
				WHERE pd.noregistrasi ='1906016010'

    		");
    }

public function GetRuangan(){
	$data = DB::table("ruangan_m as ru")
	->where('statusenabled',true)
	->whereNotNull('lokasiruangan')
	->get();

	return response()->json($data);
}


}