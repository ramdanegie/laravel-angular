<?php
/**
 * Created by PhpStorm.
 * MicroServiceController
 * User: Efan Andrian (ea@epic)
 * Date: 24/09/2019
 * Time: 03:48 PM
 */

namespace App\Http\Controllers\MicroService;
use App\Http\Controllers\ApiController;

use App\Traits\Valet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MicroServiceController extends ApiController{
    use Valet;
    public function __construct(){
        parent::__construct($skip_authentication=false);
    }

    public function GetBuktiPendaftaran (Request $request){

        $data = \DB::table('pasiendaftar_t as pd')
            ->join('pasien_m as ps','pd.nocmfk','=','ps.id')
            ->leftJoin('alamat_m as ap', 'ap.nocmfk','=' ,'ps.id')
            ->join('jeniskelamin_m as jk','ps.objectjeniskelaminfk','=','jk.id')
            ->join('ruangan_m as ru','pd.objectruanganlastfk','=','ru.id')
            ->leftJoin('pegawai_m as pp','pd.objectpegawaifk','=','pp.id')
            ->join('kelompokpasien_m as kp','pd.objectkelompokpasienlastfk','=','kp.id')
            ->join('antrianpasiendiperiksa_t as apdp','apdp.noregistrasifk','=','pd.norec')
            ->leftJoin('antrianpasienregistrasi_t as apr','apr.noreservasi','=','pd.statusschedule')
            ->select(DB::raw("pd.noregistrasi,ps.nocm,ps.tgllahir,ps.namapasien,pd.tglregistrasi,jk.reportdisplay AS jk,  
				     ap.alamatlengkap,ap.mobilephone2,ru.namaruangan AS ruanganPeriksa,pp.namalengkap AS namadokter,  
				     kp.kelompokpasien,apdp.noantrian,pd.statuspasien,apr.noreservasi,apr.tanggalreservasi"));

        if (isset($request['noRegistrasi']) && $request['noRegistrasi'] != "" && $request['noRegistrasi'] != "undefined") {
            $data = $data->where('pd.noregistrasi', '=', $request['noRegistrasi']);
        }
        $data = $data->get();
        $hasil=array();
        $fields=array();
        $jumlah = 15;
        foreach ($data as $item){
            $hasil[] = array(
                $item->noregistrasi,
                $item->nocm,
                $item->tgllahir,
                $item->namapasien,
                $item->tglregistrasi,
                $item->jk,
                $item->alamatlengkap,
                $item->mobilephone2,
                $item->ruanganPeriksa,
                $item->namadokter,
                $item->kelompokpasien,
                $item->statuspasien,
                $item->noantrian,
                $item->noreservasi,
                $item->tanggalreservasi,
            );
        }
        foreach ($data as $items){
            if ($items != []){
                $Fields[] = array(
                
                );
            }
        }
        $result = array(
            "RecordCount" => $jumlah,
            "Fields" => 0,
            'RowsCols' => $hasil,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
}