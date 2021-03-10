<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 02/04/2019
 * Time: 10:14
 */
namespace App\Http\Controllers\ReservasiOnline;

use App\Http\Controllers\ApiController;
use App\Master\Alamat;
use App\Master\Pasien;
use App\Master\SlottingOnline;
use App\Master\SlottingLibur;
use App\Master\Ruangan;
use App\Web\Profile;
use Carbon\Carbon;
//use Faker\Provider\DateTime;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Traits\PelayananPasienTrait;
use App\Traits\Valet;
use DB;
use App\Transaksi\AntrianPasienRegistrasi;
use Webpatser\Uuid\Uuid;

class ReservasiOnlineController extends ApiController
{
    use Valet, PelayananPasienTrait;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

	public function getComboReservasi(Request $request)
	{
        $kdProfile = $this->getDataKdProfile($request);
        // return  $kdProfile;
		$deptJalan = explode (',',$this->settingDataFixed('kdDepartemenReservasiOnline',   $kdProfile ));
		$kdDepartemenRawatJalan = [];
		foreach ($deptJalan as $item){
			$kdDepartemenRawatJalan []=  (int)$item;
		}

		$dataRuanganJalan = \DB::table('ruangan_m as ru')
			->select('ru.id','ru.namaruangan','ru.objectdepartemenfk')
			->where('ru.statusenabled', true)
            ->where('ru.kdprofile', $kdProfile)
			->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
			->orderBy('ru.namaruangan')
			->get();
		$jk = \DB::table('jeniskelamin_m')
			->select('id','jeniskelamin')
			->where('statusenabled', true)
			->orderBy('jeniskelamin')
			->get();
		$kdJenisPegawaiDokter = $this->settingDataFixed('kdJenisPegawaiDokter',   $kdProfile );

		$dkoter = \DB::table('pegawai_m')
			->select('*')
			->where('statusenabled', true)
              ->where('kdprofile', $kdProfile)
			->where('objectjenispegawaifk',$kdJenisPegawaiDokter)
			->orderBy('namalengkap')
			->get();

		$kelompokPasien = \DB::table('kelompokpasien_m')
			->select('id','kelompokpasien')
           ->where('kdprofile', $kdProfile)
			->where('statusenabled', true)
			->orderBy('kelompokpasien')
			->get();
		$result = array(
			'ruanganrajal' => $dataRuanganJalan,
			'jeniskelamin' => $jk,
			'dokter' => $dkoter,
			'kelompokpasien' => $kelompokPasien,
			'message' => 'ramdan@epic',
		);

		return $this->respond($result);
	}
	public function getPasienByNoCmTglLahir($nocm,$tgllahir) {
		$data = \DB::table('pasien_m as ps')
			->leftJOIN ('alamat_m as alm','alm.nocmfk','=','ps.id')
			->leftjoin ('pendidikan_m as pdd','ps.objectpendidikanfk','=','pdd.id')
			->leftjoin ('pekerjaan_m as pk','ps.objectpekerjaanfk','=','pk.id')
			->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
			->select('ps.nocm','ps.id as nocmfk','ps.namapasien','ps.objectjeniskelaminfk','jk.jeniskelamin',
				'alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.noidentitas','ps.notelepon','ps.tempatlahir',
                'ps.nobpjs',
				DB::raw(" to_char ( ps.tgllahir,'yyyy-MM-dd') as tgllahir"))
            ->where('ps.statusenabled',true);
			// ->where('ps.nocm', $nocm);
        // if(isset($tgllahir) &&$tgllahir != "" && $tgllahir != "undefined" && $tgllahir != "null") {
        //     $data = $data ->whereRaw("CONVERT(varchar, ps.tgllahir, 105)  ='$tgllahir' " );
        // }
        if(isset($nocm) &&$nocm != "" && $nocm != "undefined" && $nocm != "null") {
            $data = $data->where('ps.nocm','=',$nocm)
             ->Orwhere('ps.noidentitas','=',$nocm);
        }
		$data = $data->get();

		$result = array(
			'data'=> $data,
			'message' => 'ramdanegie',
		);
		return $this->respond($result);
	}

	public function saveReservasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        DB::beginTransaction();
        try {
	        $tgl =$request['tglReservasiFix'];
	        $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
		        ->select('apr.norec','apr.tanggalreservasi')
		        ->whereRaw("apr.tanggalreservasi = '$tgl'")
		        ->where('apr.objectruanganfk', $request['poliKlinik']['id'])
		        ->where('apr.noreservasi','!=','-')
		        ->whereNotNull('apr.noreservasi')
		        ->where('apr.statusenabled',true)
                ->where('apr.kdprofile', (int) $kdProfile )
		        ->get();
			if(count($dataReservasi) > 0){
				$result = array(
					"status" => 400,
					"message" => 'Tidak bisa Reservasi, Coba di jadwal yang lain',
				);
				return $this->setStatusCode($result['status'])->respond($result, 'Mohon maaf dijam tersebut sudah ada yang reservasi, Coba di jadwal yang lain');
			}
            if($request['isBaru'] == false){
                $pasien  = Pasien::where('nocm',$request['noCm'])->first();
            }

            $newptp = new AntrianPasienRegistrasi();
            $nontrian=AntrianPasienRegistrasi::max('noantrian')+ 1;
            $newptp->norec = $newptp->generateNewId();;
            $newptp->kdprofile = (int) $kdProfile ;
            $newptp->statusenabled = true;
            $newptp->objectruanganfk =$request['poliKlinik']['id'];
            $newptp->objectjeniskelaminfk =$request['jenisKelamin']['id'];
            $newptp->noreservasi =substr(Uuid::generate(), 0, 7);
            $newptp->tanggalreservasi = $request['tglReservasiFix'];
            $newptp->tgllahir= $request['tglLahir'];
            $newptp->objectkelompokpasienfk= $request['tipePembayaran']['id'];
            $newptp->objectpendidikanfk = 0;
            $newptp->namapasien=  $request['namaPasien'];
            $newptp->noidentitas=  $request['nik'];
            $newptp->tglinput = date('Y-m-d H:i:s');
            if($request['tipePembayaran']['id'] == 2){
                $newptp->nobpjs= $request['noKartuPeserta'];
                $newptp->norujukan= $request['noRujukan'];
                
            }else{
                $newptp->noasuransilain= $request['noKartuPeserta'];
            }
            $newptp->notelepon= $request['noTelpon'];
            if(isset($request['dokter']['id'])){
                  $newptp->objectpegawaifk=  $request['dokter']['id'];
  
            }
        
            if($request['isBaru'] == true){
                $newptp->tipepasien = "BARU";
                $newptp->type = "BARU";
            }else{
                $newptp->tipepasien = "LAMA";
                $newptp->type = "LAMA";
            }
//            $newptp->objectasalrujukanfk = 0;
//            $newptp->objectstrukreturfk= 0;
//            $newptp->objecttitlefk= 0;
//            $newptp->isconfirm= 0;
//            $newptp->jenis = $request['datas']['norecpap'];
//            $newptp->statuspanggil = 0;
            if(isset($pasien) && !empty($pasien)){
                $newptp->objectagamafk= $pasien->objectagamafk;
                $alamat = Alamat::where('nocmfk',$pasien->id)->first();
                if(!empty($alamat)){
                    $newptp->alamatlengkap= $alamat->alamatlengkap;
                    $newptp->objectdesakelurahanfk = $alamat->objectdesakelurahanfk;
                    $newptp->negara= $alamat->objectnegarafk;
                }   
                $newptp->objectgolongandarahfk=  $pasien->objectgolongandarahfk;
                $newptp->kebangsaan= $pasien->objectkebangsaanfk;
                $newptp->namaayah=$pasien->namaayah;
                $newptp->namaibu=$pasien->namaibu;
                $newptp->namasuamiistri= $pasien->namasuamiistri;
        
                $newptp->noaditional= $pasien->noaditional;
//                $newptp->noantrian= 0;
                $newptp->noidentitas= $pasien->noidentitas;
                $newptp->nocmfk=  $pasien->id;
                $newptp->paspor=  $pasien->paspor;
                $newptp->objectpekerjaanfk=  $pasien->objectpekerjaanfk;
                $newptp->objectpendidikanfk= $pasien->objectpendidikanfk != null? $pasien->objectpendidikanfk  : 0 ;
                $newptp->objectstatusperkawinanfk=  $pasien->objectstatusperkawinanfk;
                $newptp->tempatlahir= $pasien->tempatlahir;

            }
            $newptp->save();
            $newptp->namaruangan =Ruangan::where('id',$newptp->objectruanganfk )
                                ->where('kdprofile',(int) $kdProfile )
                                ->first()->namaruangan;
            $transStatus = true;
         } catch (\Exception $e) {
            $transStatus = false;
         
        }
        $transMessage = "Simpan Reservasi";
        if ($transStatus ==true) {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "data"=>$newptp,
                "as" => 'ramdan@epic',
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
    public function getHistoryReservasi(Request $request)
    {        
        $kdProfile = $this->getDataKdProfile($request);

        $data = \DB::table('antrianpasienregistrasi_t as apr')
            ->leftJoin('pasien_m as pm','pm.id','=','apr.nocmfk')
            ->leftJoin('alamat_m as alm','alm.nocmfk','=','pm.id')
            ->leftJoin('jeniskelamin_m as jk','jk.id','=','pm.objectjeniskelaminfk')
            ->leftJoin('jeniskelamin_m as jks','jks.id','=','apr.objectjeniskelaminfk')
            ->leftJoin('pekerjaan_m as pk','pk.id','=','pm.objectpekerjaanfk')
            ->leftJoin('pendidikan_m as pdd','pdd.id','=','pm.objectpendidikanfk')
            ->leftJoin('ruangan_m as ru','ru.id','=','apr.objectruanganfk')
            ->leftJoin('pegawai_m as pg','pg.id','=','apr.objectpegawaifk')
            ->leftJoin('kelompokpasien_m as kps','kps.id','=','apr.objectkelompokpasienfk')
            ->select('apr.norec','pm.nocm','apr.noreservasi','apr.tanggalreservasi','apr.objectruanganfk',
                'apr.objectpegawaifk','ru.namaruangan','apr.isconfirm','pg.namalengkap as dokter','pm.id as nocmfk',
               'pm.namapasien','apr.namapasien','alm.alamatlengkap','pk.pekerjaan','pm.noasuransilain','pm.noidentitas',
                'apr.nobpjs','pm.nohp','pdd.pendidikan','apr.type','kps.kelompokpasien','apr.objectkelompokpasienfk','ru.objectdepartemenfk',
                'ru.prefixnoantrian', 'apr.norujukan',
                DB::raw('(case when pm.namapasien is null then apr.namapasien else pm.namapasien end) as namapasien, 
                (case when apr.isconfirm=true then \'Confirm\' else \'Reservasi\' end) as status,case when pm.tempatlahir is null then apr.tempatlahir else pm.tempatlahir end as tempatlahir,
                case when jk.jeniskelamin is null then jks.jeniskelamin else jk.jeniskelamin end as jeniskelamin,
                case when apr.tgllahir is null then pm.tgllahir else apr.tgllahir end as tgllahir,
                case when apr.tipepasien = \'LAMA\' then pm.nohp else  apr.notelepon end as notelepon' )
            )
            ->whereNull('apr.isconfirm')
            ->where('apr.noreservasi','!=','-')
            ->whereNotNull('apr.noreservasi')
             ->where('apr.kdprofile',  $kdProfile )
            ->where('apr.statusenabled',true);


        if(isset($request['nocmNama']) && $request['nocmNama'] != "" && $request['nocmNama'] != "undefined" && $request['nocmNama'] != "null") {
            $data =
                 $data->where('pm.nocm', $request['nocmNama'])
//                     ->Orwhere('pm.noidentitas', $request['nocmNama'])
                 -> Orwhere('apr.namapasien', 'ilike','%'.$request['nocmNama'].'%');

        }
        if(isset($request['tgllahir']) && $request['tgllahir'] != "" && $request['tgllahir'] != "undefined" && $request['tgllahir'] != "null" &&  $request['tgllahir'] !='Invaliddate') {
            $tgllahir= $request['tgllahir'];
            $data =
//                $data->whereRaw("CONVERT(varchar, pm.tgllahir, 105)  ='$tgllahir' " )
                $data->whereRaw("to_char( apr.tgllahir, 'dd-MM-yyyy')  ='$tgllahir' " );
        }

        if(isset($request['noReservasi']) && $request['noReservasi'] != "" && $request['noReservasi'] != "undefined" && $request['noReservasi'] != "null") {
            $data =
                 $data->where('apr.noreservasi', $request['noReservasi']);
            
        }
        $data = $data->orderBy('apr.tanggalreservasi','desc');
        if(isset($request['jmlRows']) && $request['jmlRows'] != "" && $request['jmlRows'] != "undefined" && $request['jmlRows'] != "null" && $request['jmlRows'] != 0) {
            $data=$data->take($request['jmlRows']);
        }

        if(isset($request['jmlOffset']) && $request['jmlOffset'] != "" && $request['jmlOffset'] != "undefined" && $request['jmlOffset'] != "null") {
            $data=$data->offset($request['jmlOffset']);
        }
        $data = $data->get();

        $result = array(
            'data' => $data,
            'as' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
    public function deleteReservasi(Request $request)
    {
        DB::beginTransaction();
        try {
            AntrianPasienRegistrasi::where('norec',$request['norec'])->update([
               'statusenabled'=>false,
            ]);
            $transStatus = 'true';

            $transMessage = "Hapus Reservasi Sukses";
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Reservasi Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
                "as" => 'ramdan@epic',
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
    public function saveAntrianTouchscreen(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $dataLogin = $request->all();
        DB::beginTransaction();
        $noRec='';
        $tglAyeuna = date('Y-m-d H:i:s');
        $tglAwal = date('Y-m-d 00:00:00');
        $tglAkhir = date('Y-m-d 23:59:59');
        $kdRuanganTPP = $this->settingDataFixed('idRuanganTPP1',   $kdProfile);
        try {
            $newptp = new AntrianPasienRegistrasi();
            $norec = $newptp->generateNewId();
            $nontrian= AntrianPasienRegistrasi::where('jenis',$request['jenis'])
                    ->whereBetween('tanggalreservasi',[$tglAwal,$tglAkhir])
                    ->where('kdprofile', $kdProfile)
                    ->max('noantrian')+1;
            $newptp->norec = $norec;
            $newptp->kdprofile = $kdProfile;
            $newptp->statusenabled = true;
            $newptp->objectruanganfk = $kdRuanganTPP;
            $newptp->objectjeniskelaminfk = 0;
            $newptp->noantrian = $nontrian;
            $newptp->noreservasi = "-";
            $newptp->objectpendidikanfk = 0;
            $newptp->tanggalreservasi = $tglAyeuna;
            $newptp->tipepasien = "BARU";
            $newptp->type = "BARU";
            $newptp->jenis = $request['jenis'];
            $newptp->statuspanggil = 0;
            $newptp->save();
            $noRec=$newptp->norec;


            $transMessage = "Simpan Antrian";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Antrian Gagal";
        }

        if ($transStatus != 'false') {
         
            DB::commit();
            $result = array(
                "noRec" =>$noRec,
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
                "noRec" =>$noRec,
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
  public function getRuanganByKodeInternal($kode)
    {
         $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('ruangan_m')
            ->where('statusenabled',true)
            ->where('kdinternal', '=',$kode)
             ->where('kdprofile', '=',$kdProfile)
            ->first();

        $result = array(
            'data' => $data,
            'as' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
    public function getDiagnosaByKode($kode)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('diagnosa_m')
            ->where('statusenabled',true)
            ->where('kddiagnosa', '=',$kode)
            ->where('kdprofile', '=',$kdProfile)
            ->first();

        $result = array(
            'data' => $data,
            'as' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
	public function getSlottingByRuangan($kode,$tgl)
	{
		$dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
			->select('apr.norec','apr.tanggalreservasi')
			->whereRaw(" format(apr.tanggalreservasi,'yyyy-MM-dd') = '$tgl'")
			->where('apr.objectruanganfk', $kode)
			->where('apr.noreservasi','!=','-')
			->whereNotNull('apr.noreservasi')
			->where('apr.statusenabled',true)
			->get();

		$ruangan = \DB::table('ruangan_m as ru')
			->join('slottingonline_m as slot', 'slot.objectruanganfk', '=', 'ru.id')
			->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk', 'slot.jambuka', 'slot.jamtutup',
				'slot.quota',
				DB::raw("datepart(hour,slot.jamtutup) -datepart(hour, slot.jambuka)as totaljam"))
			->where('ru.statusenabled', true)
			->where('ru.id', $kode)
			->where('slot.statusenabled', true)
			->first();
		$begin = new Carbon($ruangan->jambuka);
//		return $this->respond($begin);
		$end = new Carbon($ruangan->jamtutup);
		$waktuPerorang = ((float)$ruangan->totaljam / (float)$ruangan->quota) * 60;
		$waktuPerorang = $waktuPerorang . ' min';
		$interval = \DateInterval::createFromDateString($waktuPerorang . ' min');
		$times = new \DatePeriod($begin, $interval, $end);
//		return $dataReservasi;
		$jamArr = [];
		foreach ($times as $time) {
			$jamArr []= array(
				'jam' => $time->format('H:i'),
//				'disable' => true,
			);
		}

		$i =0;
		$reservasi = [];
		foreach ($dataReservasi as $items){
			$jamUse =  new Carbon($items->tanggalreservasi);
			$reservasi [] = array(
				'jamreservasi' => $jamUse->format('H:i')
			);
		}
//		foreach ($jamArr as $itemJam) {
//				foreach ($dataReservasi as $items){
//					$jamUse = new \DateTime( $items->tanggalreservasi);
//					if ($jamUse->format('H:i') == $itemJam['jam']) {
//						array_splice( $jamArr,$i,count($jamArr));
//					}
//			}
//			$i = $i +1;
//
//		}
//			if(count($dataReservasi) > 0){
//				foreach ($dataReservasi as $items){
//					$jamUse = new \DateTime( $items->tanggalreservasi);
//					$jamUse2 = $time->format('H:i');
//					if ($jamUse->format('H:i') == $time->format('H:i')) {
//						$jam []= array(
//							'jam' => $time->format('H:i'),
//							'disable' => false,
//						);
////						break;
//					}
//
////					else{
////						$jam []= array(
////							'jam' => $time->format('H:i'),
////							'disable' => true,
////						);
////					}
//				}
//			}
//			else{
//				$jam [] =array(
//					'jamaktif' =>$time->format('H:i'),
//					'disable' => false,
//				);
//			}

//		}
		$slot  = array(
			'id' => $ruangan->id,
			'namaruangan' => $ruangan->namaruangan,
			'objectdepartemenfk' => $ruangan->objectdepartemenfk,
			'jambuka' => $ruangan->jambuka,
			'jamtutup' => $ruangan->jamtutup,
			'totaljam' => $ruangan->totaljam,
			'quota' => $ruangan->quota,
			'waktu' => $waktuPerorang,
			'listjam' => $jamArr
		);
		$result = array(
			'slot' => $slot,
			'reservasi' => $reservasi,
//			'$jamUse' => $jamUse->format('H:i'),
//			'$jamUse2' => $jamUse2,
			'as' => 'ramdan@epic',
		);
		return $this->respond($result);
	}
	public function getDaftarSlotting(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
		$ruangan = \DB::table('ruangan_m as ru')
			->join('slottingonline_m as slot', 'slot.objectruanganfk', '=', 'ru.id')
			->select('ru.id as idruangan','slot.id', 'ru.namaruangan', 'ru.objectdepartemenfk', 'slot.jambuka', 'slot.jamtutup',
				'slot.quota',
				DB::raw("extract(hour from slot.jamtutup) -extract(hour from slot.jambuka)as totaljam"))
            // DB::raw("datepart(hour,slot.jamtutup) -datepart(hour, slot.jambuka)as totaljam"))
			->where('ru.statusenabled', true)
            ->where('slot.kdprofile', $kdProfile)
			->where('slot.statusenabled', true);
//			->where('ru.id', $kode)
		if(isset($request['namaRuangan']) && $request['namaRuangan']!='undefined' && $request['namaRuangan']!=''){
			$ruangan =$ruangan->where('ru.namaruangan','ilike','%'.$request['namaRuangan'].'%');
		}
		if(isset($request['quota']) && $request['quota']!='undefined' && $request['quota']!=''){
			$ruangan =$ruangan->where('slot.quota','=',$request['quota']);
		}
		$ruangan=$ruangan->get();

		$result = array(
			'data' => $ruangan,
			'as' => 'ramdan@epic',
		);
		return $this->respond($result);
	}
	public function saveSlotting(Request $request){
     $kdProfile = $this->getDataKdProfile($request);
		DB::beginTransaction();
		try {
			if($request['id'] == ''){
				$newptp = new SlottingOnline();
				$newptp->id = SlottingOnline::max('id')+1;
				$newptp->statusenabled = true;
				$newptp->kdprofile = $kdProfile;
			}else{
				$newptp = SlottingOnline::where('id',$request['id'])->first();
			}

			$newptp->objectruanganfk = $request['objectruanganfk'];
			$newptp->jambuka = $request['jambuka'];
			$newptp->jamtutup =  $request['jamtutup'];
			$newptp->quota =  $request['quota'];
			$newptp->save();

			$transMessage = "Simpan Slotting";
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
			$transMessage = "Simpan Slotting Gagal";
		}

		if ($transStatus != 'false') {
			DB::commit();
			$result = array(
				"data" =>$newptp,
				"status" => 201,
				"message" => $transMessage,
			);
		} else {
			DB::rollBack();
			$result = array(
//				"noRec" =>$noRec,
				"status" => 400,
				"message" => $transMessage,
			);
		}

		return $this->setStatusCode($result['status'])->respond($result, $transMessage);
	}
	public function deleteSlotting(Request $request){

		try {
			SlottingOnline::where('id',$request['id'])->delete();
			$transMessage = "Sukses ";
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
			$transMessage = "Hapus slotting Gagal";
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
    public function getSlottingByRuanganNew($kode,$tgl,Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
            ->select('apr.norec','apr.tanggalreservasi')
            ->whereRaw("to_char(apr.tanggalreservasi,'yyyy-MM-dd') = '$tgl'")
            ->where('apr.objectruanganfk', $kode)
            ->where('apr.noreservasi','!=','-')
            ->where('apr.kdprofile',$kdProfile)
            ->whereNotNull('apr.noreservasi')
            ->where('apr.statusenabled',true)
            ->get();

        $ruangan = \DB::table('ruangan_m as ru')
            ->join('slottingonline_m as slot', 'slot.objectruanganfk', '=', 'ru.id')
            ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk', 'slot.jambuka', 'slot.jamtutup',
                'slot.quota',
                // DB::raw("DATEDIFF(second,  	[slot].[jambuka],	[slot].[jamtutup]) / 3600.0 AS totaljam "))
               DB::raw("(EXTRACT(EPOCH FROM slot.jamtutup) - EXTRACT(EPOCH FROM slot.jambuka))/3600 as totaljam"))
            ->where('ru.statusenabled', true)
            ->where('ru.id', $kode)
            ->where('slot.kdprofile',$kdProfile)
            ->where('slot.statusenabled', true)
            ->first();

	    $begin = new Carbon($ruangan->jambuka);
	    $jamBuka = $begin->format('H:i');
	    $end = new Carbon($ruangan->jamtutup);
	    $jamTutup = $end->format('H:i');
	    $quota =(float)$ruangan->quota;
        $waktuPerorang = ((float)$ruangan->totaljam / (float)$ruangan->quota) * 60;

        $i =0;
        $reservasi = [];
        foreach ($dataReservasi as $items){
            $jamUse =  new Carbon($items->tanggalreservasi);
            $reservasi [] = array(
                'jamreservasi' => $jamUse->format('H:i')
            );
        }

        $slot  = array(
            'id' => $ruangan->id,
            'namaruangan' => $ruangan->namaruangan,
            'objectdepartemenfk' => $ruangan->objectdepartemenfk,
            'jambuka' => $jamBuka,
            'jamtutup' => $jamTutup,
            'totaljam' => $ruangan->totaljam,
            'quota' => (float)$quota,
            'interval' => $waktuPerorang,
//            'listjam' => $jamArr
        );
        $result = array(
        	'tanggal'=> $tgl,
            'slot' => $slot,
            'reservasi' => $reservasi,
            'as' => 'ramdan@epic',
        );
        return $this->respond($result);
    }
	public function getDaftarSlottingAktif(Request $request)
	{
		$tglAwal = $request['tglAwal'].' 00:00';
		$tglAkhir = $request['tglAkhir']. ' 23:59';
		$ruangan = \DB::table('ruangan_m as ru')
			->join('slottingonline_m as slot', 'slot.objectruanganfk', '=', 'ru.id')
			->select('ru.id as idruangan','slot.id', 'ru.namaruangan', 'ru.objectdepartemenfk', 'slot.jambuka', 'slot.jamtutup',
				'slot.quota',
				DB::raw("datepart(hour,slot.jamtutup) -datepart(hour, slot.jambuka)as totaljam
				"))
			->where('ru.statusenabled', true)
			->where('slot.statusenabled', true)
			->get();
		$slot = [];
		if(count($ruangan)> 0){
			foreach ($ruangan as $item){
				$waktuPerorang = ((float)$item->totaljam / (float)$item->quota) * 60;
				$slot [] = array(
					'id' => $item->id,
					'idruangan' => $item->idruangan,
					'namaruangan' => $item->namaruangan,
					'jambuka' => $item->jambuka,
					'jamtutup' => $item->jamtutup,
					'quota' =>(float) $item->quota,
					'totaljam' => (float) $item->totaljam,
					'interval' => $waktuPerorang,
					);

			}
		}

		$dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
			->select('apr.norec','apr.tanggalreservasi')
			->whereRaw("format(apr.tanggalreservasi,'yyyy-MM-dd') = '$tglAwal'")
//			->where(" format(apr.tanggalreservasi,'yyyy-MM-dd') <= '$tglAkhir'")
//			->where('apr.objectruanganfk', $kode)
			->where('apr.noreservasi','!=','-')
			->whereNotNull('apr.noreservasi')
			->where('apr.statusenabled',true)
			->get();


		$result = array(
			'slotting' => $slot,
			'reservasi' => $dataReservasi,
			'as' => 'ramdan@epic',
		);
		return $this->respond($result);
	}
    public function getLiburSlotting(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
     
        $data = \DB::table('slottinglibur_m')
            ->select(DB::raw("to_char(tgllibur,'yyyy-MM-dd') as tgllibur,id,statusenabled"))
            ->where('statusenabled', true)
             ->where('kdprofile', $kdProfile)
            ->orderBy('tgllibur');
        if(isset($request['tgllibur']) && $request['tgllibur']!=''){
            $tgl = $request['tgllibur'];
            $data = $data->whereRaw("to_char(tgllibur,'yyyy-MM-dd') ='$tgl'");
        }    

        $data=$data->get();
   
        $result = array(
           
            'libur' => $data,
            'message' => 'ramdan@epic',
        );

        return $this->respond($result);
    }
      public function getRincianPelayanan($noRegister)
    {
//        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegister)->first();
        $kdProfile = $this->getDataKdProfile($request);
//        $pelayanan = $this->getPelayananPasienByNoRegistrasi($noRegister);
        $pelayanan = DB::select(DB::raw("select pd.objectruanganlastfk,pd.nostruklastfk,ps.id as psid,ps.nocm,
            ps.namapasien,pd.tglpulang,kps.kelompokpasien,kl.namakelas,
            pd.objectruanganlastfk,ru.objectdepartemenfk,
            pd.noregistrasi,pd.tglregistrasi,ru.namaruangan,
            pp.* 

            from pasiendaftar_t pd
            left JOIN antrianpasiendiperiksa_t apd on apd.noregistrasifk=pd.norec
            left JOIN pelayananpasien_t pp on pp.noregistrasifk=apd.norec
            left JOIN pasien_m ps on ps.id=pd.nocmfk
            left JOIN kelas_m kl on kl.id=pd.objectkelasfk
            left JOIN kelompokpasien_m kps on kps.id=pd.objectkelompokpasienlastfk
            left JOIN ruangan_m ru on ru.id=pd.objectruanganlastfk
            where pd.noregistrasi=:noregistrasi 
            and pd.kdprofile=$kdProfile
            --and pp.strukfk is null;
            "),
            array(
                'noregistrasi' => $noRegister,
            )
        );

        $pelayanantidakterklaim = DB::select(DB::raw("select pd.objectruanganlastfk,pd.nostruklastfk,ps.id as psid,ps.nocm,
            ps.namapasien,pd.tglpulang,kps.kelompokpasien,kl.namakelas,
            pd.objectruanganlastfk,ru.objectdepartemenfk,
            pd.noregistrasi,pp.* from pasiendaftar_t pd
            INNER JOIN antrianpasiendiperiksa_t apd on apd.noregistrasifk=pd.norec
            INNER JOIN pelayananpasientidakterklaim_t pp on pp.noregistrasifk=apd.norec
            INNER JOIN pasien_m ps on ps.id=pd.nocmfk
            INNER JOIN kelas_m kl on kl.id=pd.objectkelasfk
            INNER JOIN kelompokpasien_m kps on kps.id=pd.objectkelompokpasienlastfk
            INNER JOIN ruangan_m ru on ru.id=pd.objectruanganlastfk
            where pd.noregistrasi=:noregistrasi 
               and pd.kdprofile=$kdProfile
            --and pp.strukfk is null;
            "),
            array(
                'noregistrasi' => $noRegister,
            )
        );
//        $pelayanan=$pelayanan[0];
//        $billing = $this->getBillingFromPelayananPasien($pelayanan);
        $totalBilling = 0;
        $totalKlaim = 0;
        $totalDeposit = 0;
        $totaltakterklaim =0;

        foreach ($pelayanantidakterklaim as $values) {
//            if ($values->produkfk == $this->getProdukIdDeposit()) {
//                $totalDeposit = $totalDeposit + $values->hargajual;
//            } else {
                $totaltakterklaim = $totaltakterklaim + (($values->hargajual - $values->hargadiscount) * $values->jumlah) + $values->jasa;
//            }
        }

        foreach ($pelayanan as $value) {
            if ($value->produkfk == $this->getProdukIdDeposit()) {
                $totalDeposit = $totalDeposit + $value->hargajual;
            } else {
                $totalBilling = $totalBilling + (($value->hargajual - $value->hargadiscount) * $value->jumlah) + $value->jasa;
            }

        }

//        $billing = new \stdClass();
//        $billing->totalBilling = $totalBilling;
//        $billing->totalKlaim= $totalKlaim;
//        $billing->totalDeposit = $totalDeposit;

        $totalBilling = $totalBilling;
//        $isRawatInap  = $this->isPasienRawatInap2($pelayanan);
        $pelayanan = $pelayanan[0];
        $isRawatInap = false;
        if ($pelayanan->objectruanganlastfk != null) {
            if ((int)$pelayanan->objectdepartemenfk == 16) {
                $isRawatInap = true;
            }
        }


        $dataTotaldibayar = DB::select(DB::raw("select sum(((case when pp.hargajual is null then 0 else pp.hargajual  end - case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) + case when pp.jasa is null then 0 else pp.jasa end) as total
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
                INNER JOIN strukpelayanan_t as sp on sp.norec=pp.strukfk
                where  pd.noregistrasi=:noregistrasi and sp.nosbmlastfk is not null and pp.produkfk not in (402611)
                   and pd.kdprofile=$kdProfile;
            "),
            array(
                'noregistrasi' => $noRegister ,
            )
        );
        $dibayar=0;
        $dibayar = $dataTotaldibayar[0]->total;

        $totalDeposit = $totalDeposit;
        $totalKlaim = 0;
        $result = array(
            'pasienID' => $pelayanan->psid,
            'noCm' => $pelayanan->nocm,
            'noRegistrasi' => $pelayanan->noregistrasi,
            'namaPasien' => $pelayanan->namapasien,
            'tglPulang' => $pelayanan->tglpulang,
            'jenisPasien' => $pelayanan->kelompokpasien,
            'kelasRawat' => $pelayanan->namakelas,
            'tglRegistrasi' => $pelayanan->tglregistrasi,
            'ruangan' => $pelayanan->namaruangan,
            'noAsuransi' => '-', //ambil dari asuransi pasien -m tapi datanya blum ada brooo..
            'kelasPenjamin' => '-', //ini blum ada datanya gimana mau munculin,, gila yaa ?
            'billing' => $totalBilling,
            'penjamin' => '',//$penjamin=$this->getPenjamin($pelayanan)->namarekanan,
            'deposit' => $totalDeposit, //ngambil dari mana
            'totalKlaim' => $totalKlaim, //ngambil dari mana? dihitunga gak
            'jumlahBayar' => $dibayar,//$totalBilling - $totalDeposit - $totalKlaim, //jumlah bayar ini perlu gak
            'jumlahBayarNew' =>  $totalBilling - $totalDeposit - $totalKlaim - $totaltakterklaim, //jumlah bayar dengan tindakan yang tidak d klaim
            'jumlahPiutang' => 0, //ini ngambil dari pembayaran gak ?
            'needDokument' => true, //ini ngambil ddokument dari mana ? pake datafixed
            'dokuments' => [], // sama ini juga ngambilnya dari mana ..
            'totaltakterklaim' => $totaltakterklaim,
            'isRawatInap' => $isRawatInap,
        );
        return $this->respond($result);
    }
    public function getSetting(Request $request){
        $data = \DB::table('settingdatafixed_m')
            ->select('nilaifield')
            ->where('statusenabled', true)
            ->where('namafield', $request['namaField'])
            ->first();
   
        return $this->respond($data->nilaifield);
    }   
    public  function cekReservasiDipoliYangSama(Request $request)
    {       
            $kdProfile = $this->getDataKdProfile($request);
            $tgl =$request['tglReservasi'];
           if($request['tipePasien']=='baru' ){
               $tglLahir =$request['tglLahir'];
               $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
                    ->select('apr.norec','apr.tanggalreservasi')
                    ->whereRaw("format(apr.tanggalreservasi,'yyyy-MM-dd' )= '$tgl'")
//                    ->where('apr.objectruanganfk', $request['ruanganId'])
                    ->where('apr.noreservasi','!=','-')
                    ->where('apr.namapasien',$request['namaPasien'])
                    ->whereRaw("format(apr.tgllahir,'yyyy-MM-dd') = '$tglLahir'")
                    ->whereNotNull('apr.noreservasi')
                    ->where('apr.statusenabled',true)
                    ->where('apr.kdprofile',$kdProfile)
                    ->where('apr.objectkelompokpasienfk',2)
                    ->get();
           }else{
             $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
                    ->join('pasien_m as ps','ps.id','=','apr.nocmfk')
                    ->select('apr.norec','apr.tanggalreservasi','ps.nocm')
                    ->whereRaw("format(apr.tanggalreservasi,'yyyy-MM-dd') = '$tgl'")
//                    ->where('apr.objectruanganfk', $request['ruanganId'])
                    ->where('apr.noreservasi','!=','-')
                    ->where('ps.nocm',$request['noCm'])
                    ->whereNotNull('apr.noreservasi')
                       ->where('apr.kdprofile',$kdProfile)
                    ->where('apr.statusenabled',true)
                    ->where('apr.objectkelompokpasienfk',2)
                    ->get();
           }

           $result = array(
                'data' =>  $dataReservasi,
                'msg' => 'er@epic'
             );
           return $this->respond($result);
           
    }
    public function getTagihanEbilling($noregistasi,Request $request)
    {
         $kdProfile = $this->getDataKdProfile($request);
        $pelayanan = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
            ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
            ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
            ->join('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
            ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
            ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
            ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftjoin('strukresep_t as sre', 'sre.norec', '=', 'pp.strukresepfk')
            ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk',
                'pd.nostruklastfk','pd.noregistrasi',
                'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang',
                'pd.objectrekananfk as rekananid',
                'pp.jasa',  'sp.totalharusdibayar', 'sp.totalprekanan',
                'sp.totalbiayatambahan','pp.aturanpakai','pp.iscito','pd.statuspasien','pp.isparamedis','pp.strukresepfk'
            )
             ->where('pd.kdprofile', $kdProfile)
            ->where('pd.noregistrasi', $noregistasi);
//          ->orderBy('pp.tglpelayanan', 'pp.rke');

        $pelayanan = $pelayanan->get();

        if (count($pelayanan) > 0) {
            $details = array();
            foreach ($pelayanan as $value) {
                if($value->prid != $this->getProdukIdDeposit()){
                    $jasa = 0;
                    if (isset($value->jasa) && $value->jasa != "" && $value->jasa != null) {
                        $jasa =(float) $value->jasa;
                    }

                    $harga = (float)$value->hargajual;
                    $diskon = (float)$value->hargadiscount;
                    $detail = array(
                        'norec' => $value->norec,
                        'tglPelayanan' => $value->tglpelayanan,
                        'namaPelayanan' => $value->namaproduk,
                        'jumlah' => (float)$value->jumlah,
                        'kelasTindakan' => @$value->namakelas,
                        'ruanganTindakan' => @$value->namaruangan,
                        'harga' => $harga,
                        'diskon' => $diskon,
                        'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                        'strukfk' => $value->nostruk ,
                        'sbmfk' => $value->nosbm,
                        'pgid' => '',
                        'ruid' => $value->ruid,
                        'prid' => $value->prid,
                        'klid' => $value->klid,
                        'norec_apd' => $value->norec_apd,
                        'norec_pd' => $value->norec_pd,
                        'norec_sp' => $value->norec_sp,
                        'jasa' => $jasa,
                        'aturanpakai' => $value->aturanpakai,
                        'iscito' => $value->iscito,
                        'isparamedis' => $value->isparamedis,
                        'strukresepfk' => $value->strukresepfk
                    );

                    $details[] = $detail;
                }
                

            }
        }

        $arrHsil = array(
            'details' => $details,
            'deposit' =>  $this->getDepositPasien($noregistasi),
            'totalklaim' =>  $this->getTotalKlaim($noregistasi,$kdProfile),
            'bayar' =>  $this->getTotolBayar($noregistasi,$kdProfile),
        );
        return $this->respond($arrHsil);
    }

    public  function getTotalKlaim($noregistrasi,$kdProfile)
    {
       $pelayanan =collect(\DB::select("select sum(x.totalppenjamin) as totalklaim
         from (select spp.norec,spp.totalppenjamin
         from pasiendaftar_t as pd
            join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            join pelayananpasien_t as pp on pp.noregistrasifk =apd.norec
            join strukpelayanan_t as sp on sp.norec= pp.strukfk
            join strukpelayananpenjamin_t as spp on spp.nostrukfk=sp.norec
            where pd.noregistrasi ='$noregistrasi'
        and spp.statusenabled is null 
        and pd.kdprofile=$kdProfile
        GROUP BY spp.norec,spp.totalppenjamin

        ) as x"))->first();
        if(!empty($pelayanan) && $pelayanan->totalklaim!= null){
             return (float) $pelayanan->totalklaim;
         }else{
            return 0;
         }
       

    }
    public function getTotolBayar($noregistrasi,$kdProfile)
    {
      $pelayanan =collect(\DB::select("select sum(x.totaldibayar) as totaldibayar
         from (select sbm.norec,sbm.totaldibayar
         from pasiendaftar_t as pd
        join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
        join pelayananpasien_t as pp on pp.noregistrasifk =apd.norec
        join strukpelayanan_t as sp on sp.norec= pp.strukfk
        join strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
        where pd.noregistrasi ='$noregistrasi'
        and sp.statusenabled is null 
        and sbm.statusenabled =true
        and pd.kdprofile=$kdProfile
        GROUP BY sbm.norec,sbm.totaldibayar

        ) as x"))->first();
        if(!empty($pelayanan) && $pelayanan->totaldibayar!= null){
             return (float) $pelayanan->totaldibayar;
         }else{
            return 0;
         }
       
        
    }
    public function getNomorRekening(Request $request){
        $data = \DB::table('bankaccount_m')
            ->select('*')
            ->where('statusenabled', true)
            ->get();
    
        $result  = array('data' => $data,
                    'as'=> 'er@epic' );
        return $this->respond($result);
    }

    public function UpdateStatConfirm(Request $request)
    {
//        $data=$request['data'];
//        return $this->respond($data);
        try {
//            foreach ($data as $item) {
            $dataApr = AntrianPasienRegistrasi::where('noreservasi', $request['noreservasi'])
                ->update([
                    'isconfirm' => true,
//                        'objectstatusbarang'=> 2
                ]);
//            }

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Update Status Gagal";
        }


        if ($transStatus == 'true') {
            $transMessage = "Update Status Berhasil";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
//                    "as" => 'cepot',
            );
        } else {
            $transMessage = "Update Status Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
//                    "as" => 'Cepot',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function updateNoCmInAntrianRegistrasi(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        try {

            $dataApr = AntrianPasienRegistrasi::where('norec', $request['norec'])
                ->update([
                    'nocmfk' => $request['nocmfk'],
                ]);

            $transStatus = 'true';
        }catch (\Exception $e) {
            $transStatus = 'false';
        }


        if ($transStatus == 'true') {
            $transMessage = "Update Reconfirm";
            DB::commit();
            $result = array(
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            $transMessage = "Update Reconfirm Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "message"  => $transMessage,
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    public function  getPasienByNoRegistrasi($noregistrasi,Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('pasiendaftar_t as pd')
            ->leftjoin ('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin ('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin ('kelas_m as kls','kls.id','=','pd.objectkelasfk')
            ->leftjoin ('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin ('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->leftjoin ('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin ('alamat_m as alm','alm.id','=','pd.nocmfk')
            ->leftjoin ('agama_m as agm','agm.id','=','ps.objectagamafk')
            ->select('pd.norec as norec_pd','pd.noregistrasi','pd.tglregistrasi','ps.nocm','ps.namapasien',
                'ps.tgllahir','ps.namakeluarga','ru.namaruangan','kls.namakelas','kps.kelompokpasien','rk.namarekanan','alm.alamatlengkap',
                'jk.jeniskelamin','agm.agama','ps.nohp','pd.statuspasien','pd.tglpulang')
            ->where('pd.noregistrasi', $noregistrasi)
            ->where('pd.kdprofile',   $kdProfile)
            ->first();

        $result = array(
            'data'=> $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getDaftarRiwayatRegistrasi( Request $request) {
            $kdProfile = $this->getDataKdProfile($request);
        $data = \DB::table('pasien_m as ps')
            ->join('pasiendaftar_t as pd','pd.nocmfk','=','ps.id')
            ->join('ruangan_m as ru','ru.id','=','pd.objectruanganlastfk')
            ->leftjoin('pegawai_m as pg','pg.id','=','pd.objectpegawaifk')
            ->leftJoin('batalregistrasi_t as br','br.pasiendaftarfk','=','pd.norec')
            ->select(DB::raw("pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pd.objectruanganlastfk,ru.namaruangan,
			                  pd.objectpegawaifk,pg.namalengkap as namadokter,pd.tglpulang,ru.objectdepartemenfk,
			                  CASE when ru.objectdepartemenfk in (16,25,26) then 1 else 0 end as statusinap,ps.tgllahir"))
            ->whereNull('br.pasiendaftarfk')
                 ->where('pd.kdprofile',   $kdProfile)
;

//        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
//            $data = $data->where('ps.tgllahir','>=', $request['tglLahir'].' 00:00');
//        };
//        if(isset($request['tglLahir']) && $request['tglLahir']!="" && $request['tglLahir']!="undefined"){
//            $data = $data->where('ps.tgllahir','<=', $request['tglLahir'].' 23:59');
//        };
        if(isset($request['norm']) && $request['norm']!="" && $request['norm']!="undefined") {
            $data = $data->where('ps.nocm', 'ilike', '%'. $request['norm'] .'%');
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['namaPasien'] .'%');
        };
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $data = $data->where('pd.objectruanganlastfk','=', $request['idRuangan']);
        };

        $data = $data->where('ps.statusenabled',true);
        $data = $data->orderBy('pd.tglregistrasi');
        $data=$data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function cekPasienByNik($nik) {
        $data =  Pasien::where('noidentitas',$nik)
            ->where('statusenabled',true)->get();

        $result = array(
            'data'=> $data,
            'message' => 'er@epic',
        );
        return $this->respond($result);
    }
    public function saveLibur(Request $request){

        DB::beginTransaction();
        try {
            $tgl =[];
              foreach ($request['listtanggal'] as $key => $value) {
                $tgl [] = $value['tgl'];
              }
            $del = SlottingLibur::whereIn('tgllibur',$tgl)->delete();
            foreach ($request['listtanggal'] as $key => $value) {

                $newptp = new SlottingLibur();
                $newptp->id = SlottingLibur::max('id')+1;
                $newptp->statusenabled = true;
                $newptp->kdprofile = 11;
                $newptp->tgllibur = $value['tgl'];
                $newptp->save();
            }
            

            $transMessage = "Simpan Libur";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan Libur Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                "data" =>$newptp,
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
//              "noRec" =>$noRec,
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
      public function deleteLibur(Request $request){

        DB::beginTransaction();
        try {
            
            $newptp= SlottingLibur::where('id',$request['id'])->update(
                ['statusenabled' => false ]
            );            

            $transMessage = "Hapus Libur";
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Hapus Libur Gagal";
        }

        if ($transStatus != 'false') {
            DB::commit();
            $result = array(
                // "data" =>$newptp,
                "status" => 201,
                "message" => $transMessage,
            );
        } else {
            DB::rollBack();
            $result = array(
//              "noRec" =>$noRec,
                "status" => 400,
                "message" => $transMessage,
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    
      public function getLibur( Request $request) {
        $data = \DB::table('slottinglibur_m as ps')
            ->select('ps.*')
            ->where('ps.statusenabled',true);

        if(isset($request['tgllibur']) && $request['tgllibur']!="" && $request['tgllibur']!="undefined") {
            $tgls= $request['tgllibur'];
            $data = $data->whereRaw("format(ps.tgllibur,'yyyy-MM-dd')= 'tgls'");
        };
        if(isset($request['namaPasien']) && $request['namaPasien']!="" && $request['namaPasien']!="undefined") {
            $data = $data->where('ps.namapasien', 'ilike', '%'. $request['namaPasien'] .'%');
        };
        if(isset($request['noReg']) && $request['noReg']!="" && $request['noReg']!="undefined"){
            $data = $data->where('pd.noregistrasi','=', $request['noReg']);
        };
        if(isset($request['idRuangan']) && $request['idRuangan']!="" && $request['idRuangan']!="undefined"){
            $data = $data->where('pd.objectruanganlastfk','=', $request['idRuangan']);
        };


        $data = $data->orderBy('ps.id');
        $data= $data->get();
        $result = array(
            'daftar' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($result);
    }
    public function GetNoAntrianMobileJKN(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);

//       $request =  array(
//           "nomorkartu" => "0000172381691",
//           "nik" => "3372051109800010",
//           "notelp" => "085642649135",
//           "tanggalperiksa" => "2020-10-09",
//           "kodepoli" => "JIW",
//           "nomorreferensi" => "0001R0040116A000001",
//           "jenisreferensi" => "1",
//           "jenisrequest" => "2",
//           "polieksekutif" => "0"
//       );

        $request = $request->json()->all();
//        print_r($request);
//        exit();

        if(empty($request['nomorkartu']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Nomor Kartu BPJS tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        if(empty($request['tanggalperiksa'])) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tanggal Periksa tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$request['tanggalperiksa'])) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Format Tanggal Periksa salah"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        if($request['tanggalperiksa'] >= date('Y-m-d',strtotime('+90 days',strtotime(date('Y-m-d'))))){
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tanggal periksa maksimal 90 hari dari hari ini"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
        if($request['tanggalperiksa'] == date('Y-m-d')){
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tanggal periksa minimal besok"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
        if($request['tanggalperiksa'] < date('Y-m-d')){
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tanggal periksa minimal besok"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
//        return date('w',strtotime( $request['tanggalperiksa'] ) );
        if(date('w',strtotime( $request['tanggalperiksa'] )) == 0 ){
            $result = array("response"=>array(),
                "metadata"=>array("code" => "400",
                    "message" => "Tidak ada jadwal Poli di hari Minggu")
            );
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
//        return  date('w',strtotime( $request['tanggalperiksa'] ));
//
//        function isWeekend($date) {
//            $weekDay = date('w', strtotime($date));
//            return ($weekDay == 0
////                || $weekDay == 6
//            );
//        }
        if(empty($request['nik']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "NIK tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
        if(empty($request['kodepoli']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Kodepoli tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }else{
//            if($request['kodepoli'] != "JIW" ) {
//                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Kodepoli tidak sesuai"));
//                return $this->setStatusCode($result['metadata']['code'])->respond($result);
//            }
        }

        if(empty($request['jenisreferensi']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Jenis Referensi tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }else{
            if($request['jenisreferensi'] < "1" || $request['jenisreferensi'] > "2") {
                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Jenis Referensi tidak sesuai"));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }
        }

        if(empty($request['jenisrequest']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Jenis Request tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }else{
            if($request['jenisrequest'] < "1" || $request['jenisrequest'] > "2") {
                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Jenis Request tidak sesuai"));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }
        }

        if(empty($request['polieksekutif']) && $request['polieksekutif'] != "0") {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Poli Eksekutif tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }else{
            if($request['polieksekutif'] < "0" || $request['polieksekutif'] > "1") {
                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Poli Eksekutif tidak sesuai"));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }
        }


//        return $antrian;
            $eksek = false;
            if($request['polieksekutif'] == 1){
                $eksek = true;
            }
            if($request['jenisrequest']  == '2') {//POLI
                DB::beginTransaction();
                try {

                $antrian = $this->GetJamKosong($request['kodepoli'], $request['tanggalperiksa'], $kdProfile,$eksek);
                $pasien = \DB::table('pasien_m')
                    ->whereRaw("nobpjs = '" . $request['nomorkartu'] . "'")
                    ->where('statusenabled', true)
                    ->where('kdprofile', $kdProfile)
                    ->first();


                $ruang = Ruangan::where('kdinternal', $request['kodepoli'])
                    ->where('statusenabled', true)
                    ->where('iseksekutif',$eksek)
                    ->where('kdprofile', $kdProfile)->first();
                if (empty($ruang)) {
                    $result = array("response" => array(), "metadata" => array("code" => "400", "message" => "Kodepoli tidak sesuai"));
                    return $this->setStatusCode($result['metadata']['code'])->respond($result);
                }
                if (empty($pasien)) {
//                    return $this->respond($pasien);
                      $request['jenisrequest']  ='1';
                       DB::commit();
                      return  $this->postPendaftaranJKN($request,$kdProfile);
//                    $pro = Profile::where('id', $kdProfile)->first();
//                    $result = array(
//                        "response" => array(),
//                        "metadata" => array("code" => "400", "message" => "Belum terdaftar sebagai pasien " . $pro->namaexternal)
//                    );
//                    return $this->setStatusCode($result['metadata']['code'])->respond($result);
                }

                    $tipepembayaran = '2';
                    $tgl = $request['tanggalperiksa'];

                    $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
                        ->select('apr.noantrian', 'apr.noreservasi', 'ru.namaexternal', 'apr.tanggalreservasi')
                        ->join('ruangan_m as ru', 'ru.id', '=', 'apr.objectruanganfk')
//                        ->whereRaw("apr.tanggalreservasi BETWEEN '$tgl' AND '" . date('Y-m-d', strtotime('+1 day', strtotime($tgl))) . "'")
                        ->whereRaw("to_char(apr.tanggalreservasi,'yyyy-MM-dd')= '$tgl'")
                        ->where('apr.objectruanganfk', '=', $ruang->id)
                        ->where('apr.noreservasi', '!=', '-')
                        ->where('apr.noidentitas', '=', $request['nik'])
                        ->where('apr.nobpjs', '=', $request['nomorkartu'])
                        ->whereNotNull('apr.noreservasi')
                        ->where('apr.statusenabled', true)
                        ->first();
//                return $this->respond($dataReservasi);
                    if (isset($dataReservasi) && !empty($dataReservasi)) {
                        $result = array("response" => array(), "metadata" => array("code" => "400", "message" => "Mohon maaf anda sudah mendaftar pada tanggal " . $tgl));
                        return $this->setStatusCode($result['metadata']['code'])->respond($result);
                    }

                    $newptp = new AntrianPasienRegistrasi();
                    $nontrian = AntrianPasienRegistrasi::max('noantrian') + 1;
                    $newptp->norec = $newptp->generateNewId();;
                    $newptp->kdprofile = $kdProfile;
                    $newptp->statusenabled = true;
                    $newptp->objectruanganfk = $ruang->id;
                    $newptp->objectjeniskelaminfk = $pasien->objectjeniskelaminfk;
                    $newptp->noreservasi = substr(Uuid::generate(), 0, 7);
                    $newptp->tanggalreservasi = $request['tanggalperiksa'] . " " . $antrian['jamkosong'];
                    $newptp->tgllahir = $pasien->tgllahir;
                    $newptp->objectkelompokpasienfk = $tipepembayaran;
                    $newptp->objectpendidikanfk = 0;
                    $newptp->namapasien = $pasien->namapasien;
                    $newptp->noidentitas = $request['nik'];
                    $newptp->tglinput = date('Y-m-d H:i:s');
                    $newptp->nobpjs = $request['nomorkartu'];
                    $newptp->norujukan = $request['nomorreferensi'];
                    $newptp->notelepon = $pasien->nohp;
                    $newptp->objectpegawaifk = null;
                    $newptp->tipepasien = "LAMA";
                    $newptp->ismobilejkn = 1;
                    $newptp->type = "LAMA";

                    $newptp->objectagamafk = $pasien->objectagamafk;
                    $alamat = Alamat::where('nocmfk', $pasien->id)->first();
                    if (!empty($alamat)) {
                        $newptp->alamatlengkap = $alamat->alamatlengkap;
                        $newptp->objectdesakelurahanfk = $alamat->objectdesakelurahanfk;
                        $newptp->negara = $alamat->objectnegarafk;
                    }
                    $newptp->objectgolongandarahfk = $pasien->objectgolongandarahfk;
                    $newptp->kebangsaan = $pasien->objectkebangsaanfk;
                    $newptp->namaayah = $pasien->namaayah;
                    $newptp->namaibu = $pasien->namaibu;
                    $newptp->namasuamiistri = $pasien->namasuamiistri;

                    $newptp->noaditional = $pasien->noaditional;
                    $newptp->noantrian = $antrian['antrian'];
                    $newptp->noidentitas = $pasien->noidentitas;
                    $newptp->nocmfk = $pasien->id;
                    $newptp->paspor = $pasien->paspor;
                    $newptp->objectpekerjaanfk = $pasien->objectpekerjaanfk;
                    $newptp->objectpendidikanfk = $pasien->objectpendidikanfk != null ? $pasien->objectpendidikanfk : 0;
                    $newptp->objectstatusperkawinanfk = $pasien->objectstatusperkawinanfk;
                    $newptp->tempatlahir = $pasien->tempatlahir;

                    $newptp->save();
                    $transStatus = 'true';

                    $transMessage = "Ok";
                } catch (Exception $e) {
                    $transMessage = "Gagal Reservasi";
                    $transStatus = 'false';
                }

                if ($transStatus != 'false') {
                    DB::commit();
//                        $dataHasil = \DB::table('antrianpasienregistrasi_t as apr')
//                            ->select('apr.noantrian', 'apr.noreservasi', 'ru.namaexternal', 'apr.tanggalreservasi')
//                            ->join('ruangan_m as ru', 'ru.id', '=', 'apr.objectruanganfk')
//                            ->whereRaw("apr.tanggalreservasi BETWEEN '$tgl' AND '" . date('Y-m-d', strtotime('+1 day', strtotime($tgl))) . "'")
//                            ->where('apr.objectruanganfk', '=', $ruang->id)
//                            ->where('apr.noreservasi', '!=', '-')
//                            ->where('apr.noidentitas', '=', $request['nik'])
//                            ->where('apr.nobpjs', '=', $request['nomorkartu'])
//                            ->whereNotNull('apr.noreservasi')
//                            ->where('apr.statusenabled', true)
//                            ->first();

                    $estimasidilayani = strtotime($newptp->tanggalreservasi) * 1000;
                    $result = array(
                        "response" => array(
                            "nomorantrean" => $newptp->noantrian,
                            "kodebooking" => $newptp->noreservasi,
                            "jenisantrean" => '2',
                            "estimasidilayani" => $estimasidilayani,
                            "namapoli" => $ruang->namaexternal,
                            "namadokter" => '',

                        ),
                        "metadata" => array(
                            "code" => "200",
                            "message" => $transMessage)
                    );
                }else{
                    DB::rollBack();
                    $result = array(
                        "response" => array(),
                        "metadata" => array(
                            "code" => "200",
                            "message" => $transMessage)
                    );

                }
                return $this->setStatusCode($result['metadata']['code'])->respond($result);

            }else{
                /*
                 * jenis reqeust 1 //PENDAFTARAN
                 */
                return $this->postPendaftaranJKN($request,$kdProfile);

        }
    }
    public function  postPendaftaranJKN($request,$kdProfile){
        $eksek = false;
        if($request['polieksekutif'] == 1){
            $eksek = true;
        }
        DB::beginTransaction();
        try {
            $pasien = \DB::table('pasien_m')
                ->whereRaw("nobpjs = '" . $request['nomorkartu'] . "'")
                ->where('statusenabled', true)
                ->where('kdprofile', $kdProfile)
                ->first();
            $ruang = Ruangan::where('kdinternal', $request['kodepoli'])
                ->where('statusenabled', true)
                ->where('iseksekutif',$eksek)
                ->where('kdprofile', $kdProfile)->first();
            if (empty($ruang)) {
                $result = array("response" => array(), "metadata" => array("code" => "400", "message" => "Kodepoli tidak sesuai"));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }

            $tgl = $request['tanggalperiksa'];

            $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
                ->select('apr.noantrian', 'apr.noreservasi', 'ru.namaexternal', 'apr.tanggalreservasi')
                ->join('ruangan_m as ru', 'ru.id', '=', 'apr.objectruanganfk')
//                        ->whereRaw("apr.tanggalreservasi BETWEEN '$tgl' AND '" . date('Y-m-d', strtotime('+1 day', strtotime($tgl))) . "'")
                ->whereRaw("to_char(apr.tanggalreservasi,'yyyy-MM-dd')= '$tgl'")
                ->where('apr.objectruanganfk', '=', $ruang->id)
                ->where('apr.noreservasi', '=', '-')
                ->where('apr.noidentitas', '=', $request['nik'])
                ->where('apr.nobpjs', '=', $request['nomorkartu'])
                ->whereNotNull('apr.noreservasi')
                ->where('apr.statusenabled', true)
                ->first();
//                return $this->respond($dataReservasi);
            if (isset($dataReservasi) && !empty($dataReservasi)) {
                $nomor = str_pad($dataReservasi->noantrian, 3, '0', STR_PAD_LEFT);
                $result = array("response" => array(), "metadata" => array("code" => "400",
                    "message" => "Mohon maaf anda sudah mendaftar pada tanggal " . $tgl ." No Antrean : B-".$nomor));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }
            $tglAyeuna = $request['tanggalperiksa'] . date(' H:i:s');

            $tglAwal = $request['tanggalperiksa'] .' 00:00:00';
            $tglAkhir =$request['tanggalperiksa'] .' 23:59:59';
            $kdRuanganTPP = $this->settingDataFixed('idRuanganTPP1',$kdProfile);

            $newptp = new AntrianPasienRegistrasi();
            $norec = $newptp->generateNewId();
            $nontrian = AntrianPasienRegistrasi::where('jenis', 'B')
                    ->whereBetween('tanggalreservasi', [$tglAwal, $tglAkhir])
                    ->max('noantrian') + 1;
            //                return $nontrian;
            $newptp->norec = $norec;
            $newptp->kdprofile = $kdProfile;
            $newptp->statusenabled = true;
            $newptp->objectruanganfk =  $ruang->id;//$kdRuanganTPP;
            $newptp->objectjeniskelaminfk = 0;
            $newptp->noantrian = $nontrian;
            $newptp->noreservasi = "-";
            $newptp->objectpendidikanfk = 0;

            $newptp->namapasien = !empty($pasien) ? $pasien->namapasien : null;
            $newptp->noidentitas = $request['nik'];
            $newptp->tglinput = date('Y-m-d H:i:s');
            $newptp->nobpjs = $request['nomorkartu'];
            $newptp->norujukan = $request['nomorreferensi'];
            $newptp->notelepon = !empty($pasien) ? $pasien->nohp : null;
            $newptp->nocmfk = !empty($pasien) ? $pasien->id : null;

            $newptp->tanggalreservasi = $tglAyeuna;
            $newptp->tipepasien = "BARU";
            $newptp->type = substr(Uuid::generate(), 0, 7);//"BARU";
            $newptp->jenis = 'B';//BPJS
            $newptp->statuspanggil = 0;
            $newptp->ismobilejkn = 1;
            $newptp->save();
//                    $nontrian= 2;
            /*
             * estimasi dilayani 5 menit sekali dari poli buka sesuai antrian
             */
            $es = date('Y-m-d H:i:s',strtotime('+'. (float) 5 * $nontrian  .' minutes',strtotime($request['tanggalperiksa'].' 08:00:00')));
            //                return $estimasidilayani;
            $estimasidilayani = strtotime($es) * 1000;
            $transStatus = 'true';
            $transMessage = "Ok";
        } catch (Exception $e) {
            $transMessage = "Gagal Reservasi";
            $transStatus = 'false';
        }
        if($transStatus == 'true') {
            DB::commit();
            $nomor = str_pad($newptp->noantrian, 3, '0', STR_PAD_LEFT);
//                    return $this->respond($nomor);
            $result = array(
                "response" => array(
                    "nomorantrean" => 'B-'.$nomor,
                    "kodebooking" => $newptp->type,
                    "jenisantrean" => '1', //Pendafaran
                    "estimasidilayani" => $estimasidilayani,
                    "namapoli" => $ruang->namaexternal,
                    "namadokter" => '',

                ),
                "metadata" => array(
                    "code" => "200",
                    "message" => $transMessage)
            );
        }else{
            $result = array(
                "response"=>array(),
                "metadata"=>array("code" => "400","message" => "Gagal Reservasi")
            );
        }
        return $this->setStatusCode($result['metadata']['code'])->respond($result);
    }
    public function GetJamKosong($kode,$tgl,$kdProfile,$eksek){
        $ruang = Ruangan::where('kdinternal',$kode)
            ->where('statusenabled',true)
            ->where('iseksekutif',$eksek)
            ->where('kdprofile',$kdProfile)->first();
       if(empty($ruang)){
           $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Kodepoli tidak sesuai"));
           return $this->setStatusCode($result['metadata']['code'])->respond($result);
       }
        $dataReservasi = \DB::table('antrianpasienregistrasi_t as apr')
            ->select('apr.norec','apr.tanggalreservasi')
            ->whereRaw(" to_char(apr.tanggalreservasi,'yyyy-MM-dd') = '$tgl'")
            ->where('apr.objectruanganfk', $ruang->id)
            ->where('apr.noreservasi','!=','-')
            ->whereNotNull('apr.noreservasi')
            ->where('apr.statusenabled',true)
            ->where('apr.kdprofile',$kdProfile)
            ->orderBy('apr.tanggalreservasi')
            ->get();

        $ruangan = \DB::table('ruangan_m as ru')
            ->join('slottingonline_m as slot', 'slot.objectruanganfk', '=', 'ru.id')
            ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk', 'slot.jambuka', 'slot.jamtutup',
                'slot.quota',
                DB::raw("(EXTRACT(EPOCH FROM slot.jamtutup) - EXTRACT(EPOCH FROM slot.jambuka))/3600 as totaljam"))
            ->where('ru.statusenabled', true)
            ->where('ru.id',  $ruang->id)
            ->where('ru.kdprofile',$kdProfile)
            ->where('slot.statusenabled', true)
            ->first();
//        return $this->respond($ruangan);
        if(empty($ruangan)){
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Jadwal penuh"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        $begin = new Carbon($ruangan->jambuka);
        $jamBuka = $begin->format('H:i');
        $end = new Carbon($ruangan->jamtutup);
        $jamTutup = $end->format('H:i');
        $quota =(float)$ruangan->quota;
        $waktuPerorang = ((float)$ruangan->totaljam / (float)$ruangan->quota) * 60;

        $i =0;
        $slotterisi = 0;
        $jamakhir = '00:00';
        $reservasi = [];
        foreach ($dataReservasi as $items){
            $jamUse =  new Carbon($items->tanggalreservasi);
            $slotterisi += 1;
            $reservasi [] = array(
                'jamreservasi' => $jamUse->format('H:i')
            );
            $jamakhir = $jamUse->format('H:i');
        }
        /*
         * old
         */
//        $slotakhir = $quota-$slotterisi;
//        if($slotakhir > 0){
//            //$cenvertedTime = date('Y-m-d H:i:s',strtotime('+1 hour +30 minutes +45 seconds',strtotime($startTime)));
//            $jamkosongpre = date('H:i',strtotime('+'.floor($waktuPerorang)." minutes",strtotime($jamakhir)));
//            $jamkosongfix = new Carbon($jamkosongpre);
//            $jamkosongfix = $jamkosongfix->format("H:i");
//            return array("antrian"=>$slotterisi+1,"jamkosong"=>$jamkosongfix);
//        }else{
//            return array("antrian"=>0,"jamkosong"=>"00:00");
//        }
        /*
        * end old
        */


//        return   date('H:i',strtotime('+'.floor($waktuPerorang)." minutes",strtotime($jamTutup)));
        /*
         * slot
         */
        $intervals = [];
        $intervalsAwal  = [];
        $begin = new \DateTime($jamBuka);
        $end = new \DateTime($jamTutup);
        $interval = \DateInterval::createFromDateString(floor($waktuPerorang).' minutes');
        $period = new \DatePeriod($begin, $interval, $end);
        foreach ($period as $dt) {
            $intervals[] = array(
                'jam'=>  $dt->format("H:i")
            );
            $intervalsAwal[] = array(
                'jam'=>  $dt->format("H:i")
            );
        }
        if(count($intervals) == 0){
            return array("antrian"=> 0,"jamkosong"=>"00:00");
        }
        if (count($reservasi) > 0) {
            for ( $j = count($reservasi) - 1; $j >= 0; $j--) {
                for ( $k =count($intervals)- 1; $k >= 0; $k--) {
                    if ($intervals[$k]['jam'] == $reservasi[$j]['jamreservasi']) {
//                        this.listJam.splice([i], 1);
                        array_splice($intervals,$k,1);
                    }
                }
            }
        }
        if(count($intervals) > 0){
            $antrian = 1;
            for ($x = 0; $x <= count($intervalsAwal); $x++) {
                if($intervals[0]['jam']= $intervalsAwal[$x]['jam']){
                    $antrian = $x;
                    break;
                }
            }
            return array("antrian"=> $antrian+1,"jamkosong"=>$intervals[0]['jam']);
        }else{
            return array("antrian"=> 0,"jamkosong"=>"00:00");
        }

    }
    public function GetRekapMobileJKN(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
//        $request =  array(
//            "tanggalperiksa" => "2020-10-09",
//            "kodepoli" => "JIW",
//            "polieksekutif" => "0"
//        );

        $request = $request->json()->all();


        if(empty($request['tanggalperiksa']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tanggal Periksa tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$request['tanggalperiksa'])) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Format Tanggal Periksa salah"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        if($request['tanggalperiksa'] >= date('Y-m-d',strtotime('+90 days',strtotime(date('Y-m-d'))))){
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tanggal periksa maksimal 90 hari dari hari ini"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }

        if(empty($request['kodepoli']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Kodepoli tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }else{
//            if($request['kodepoli'] != "JIW" ) {
//                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Kodepoli tidak sesuai"));
//                return $this->setStatusCode($result['metadata']['code'])->respond($result);
//            }
        }

        if(empty($request['polieksekutif']) && $request['polieksekutif'] != "0") {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Poli Eksekutif tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }else{
            if($request['polieksekutif'] < "0" || $request['polieksekutif'] > "1") {
                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Poli Eksekutif tidak sesuai"));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }
        }
        $eksek = false;
        if($request['polieksekutif'] == 1){
            $eksek = true;
        }
        try {
            $ruang = Ruangan::where('kdinternal',$request['kodepoli'])
                ->where('statusenabled',true)
                ->where('iseksekutif', $eksek)
                ->where('kdprofile',$kdProfile)->first();
            if(empty($ruang)){
                $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Kodepoli tidak sesuai"));
                return $this->setStatusCode($result['metadata']['code'])->respond($result);
            }
//            $ruang = Ruangan::where('kdprofile',$kdProfile)
            $tgl = $request['tanggalperiksa'];

            $data = \DB::table('antrianpasienregistrasi_t as apr')
                ->leftJoin('ruangan_m as ru','ru.id','=','apr.objectruanganfk')
                ->select('ru.namaexternal','apr.norec','apr.noreservasi')
                ->where('apr.objectruanganfk','=',$ruang->id)
                ->whereRaw(" to_char(apr.tanggalreservasi,'yyyy-MM-dd') = '$tgl'")
//                ->where('apr.noreservasi','!=','-')
                ->where('apr.ismobilejkn','=','1')
                ->whereNotNull('apr.noreservasi')
                ->where('apr.statusenabled',true)
                ->where('apr.kdprofile',$kdProfile)
                ->get();
//            return $this->respond($data);
            if(count($data) > 0){
                $ruId =$ruang->id;
                $terlayani = collect(DB::select("SELECT
                        pd.norec,pd.noregistrasi,pd.statusschedule
                    FROM
                        pasiendaftar_t AS pd  
                    LEFT JOIN antrianpasienregistrasi_t AS apr ON apr.noreservasi = pd.statusschedule
                    AND apr.nocmfk = pd.nocmfk
                    where pd.kdprofile=$kdProfile
                    and pd.statusenabled=true
                    and apr.ismobilejkn=true
                    and pd.objectruanganlastfk =$ruId and to_char(pd.tglregistrasi,'yyyy-MM-dd')='$tgl' "))->count();
                $result = array(
                    "response" =>
                        array(
                            "namapoli" => $data[0]->namaexternal,
                            "totalantrean" => count($data),
                            "jumlahterlayani" => $terlayani,
                            "lastupdate" => $milliseconds = round(microtime(true) * 1000)
                        ),
                    "metadata"=>
                        array(
                            'message' => "OK",
                            'code' => '200',
                        )
                );
            }else{
                $result = array(
                    "response" =>
                        array(),
                    "metadata"=>
                        array(
                            'message' => "Belum ada data yang bisa ditampilkan",
                            'code' => '400',
                        )
                );
            }


        } catch (Exception $e) {
            $result = array(
                "response" =>
                    array(),
                "metadata"=>
                    array(
                        'message' => "Gagal menampilkan data",
                        'code' => '400',
                    )
            );
        }
        return $this->respond($result);
    }
    public function getKodeBokingOperasi(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $request = $request->json()->all();
        if(empty($request['nopeserta']) ) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "No Peserta tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
        $depbedah = $this->settingDataFixed('KdInstalasiBedahSentral', $kdProfile);
        try {
            $data = DB::select(DB::raw("SELECT
                    so.noorder as kodebooking,
                    so.tglpelayananawal  as tanggaloperasi,
                    pr.namaproduk as jenistindakan,
                    ru.kdinternal as kodepoli,
                        ru.namaexternal AS namapoli,
                    pas.nocm,
                    pd.noregistrasi,pas.nobpjs
                      
                    FROM
                        strukorder_t AS so
                    join orderpelayanan_t as op on op.noorderfk=so.norec
                    join produk_m as pr on pr.id=op.objectprodukfk
                    LEFT JOIN pasiendaftar_t AS pd ON pd.norec = so.noregistrasifk
                    INNER JOIN pasien_m AS pas ON pas.id = pd.nocmfk
                    LEFT JOIN ruangan_m AS ru ON ru.id = so.objectruanganfk
                    LEFT JOIN ruangan_m AS ru2 ON ru2.id = so.objectruangantujuanfk
                    
                    WHERE
                        so.kdprofile = $kdProfile
                    --AND pas.nocm ILIKE '%11233764%'
                        and pas.nobpjs='$request[nopeserta]'
                    AND ru2.objectdepartemenfk = $depbedah
                    AND so.statusenabled = true
                    and so.statusorder is null
                    and ru.kdinternal is not null
                    and pd.objectkelompokpasienlastfk=2
                    ORDER BY
                        so.tglorder desc"));
            $list = [];
            foreach ($data as $k){
                $list [] = array(
                    'kodebooking' => $k->kodebooking,
                    'tanggaloperasi' => date('Y-m-d',strtotime($k->tanggaloperasi)),
                    'jenistindakan' => $k->jenistindakan,
                    'kodepoli' => $k->kodepoli,
                    'namapoli' => $k->namapoli,
                    'terlaksana' => 0,
                );
            }

            if(count($list) > 0){
                $result = array(
                    "response" =>
                        array(
                            "list" => $list,
                        ),
                    "metadata"=>
                        array(
                            'message' => "OK",
                            'code' => '200',
                        )
                );
            }else{
                $result = array(
                    "response" =>
                        array(),
                    "metadata"=>
                        array(
                            'message' => "Belum ada data yang bisa ditampilkan",
                            'code' => '400',
                        )
                );
            }


        } catch (Exception $e) {
            $result = array(
                "response" =>
                    array(),
                "metadata"=>
                    array(
                        'message' => "Gagal menampilkan data",
                        'code' => '400',
                    )
            );
        }
        return $this->respond($result);
    }
    public function getJadwalOperasi(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $request = $request->json()->all();
        if((!isset($request['tanggalawal']) &&  empty($request['tanggalawal']) )
            && (!isset($request['tanggalakhir']) &&  empty($request['tanggalakhir']))) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tgl Awal dan Akhir tidak boleh kosong"));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
        if($request['tanggalawal'] >  $request['tanggalakhir']) {
            $result = array("response"=>array(),"metadata"=>array("code" => "400","message" => "Tgl Awal tidak boleh melebihi Tgl Akhir "));
            return $this->setStatusCode($result['metadata']['code'])->respond($result);
        }
        $depbedah = $this->settingDataFixed('KdInstalasiBedahSentral', $kdProfile);
        try {
            $data = DB::select(DB::raw("SELECT
                    so.noorder as kodebooking,
                    so.tglpelayananawal  as tanggaloperasi,
                    pr.namaproduk as jenistindakan,
                    ru.kdinternal as kodepoli,
                        ru.namaexternal AS namapoli,
                    pas.nocm,
                    pd.noregistrasi,pas.nobpjs,
                    so.statusorder,pd.objectkelompokpasienlastfk
                      
                    FROM
                        strukorder_t AS so
                    join orderpelayanan_t as op on op.noorderfk=so.norec
                    join produk_m as pr on pr.id=op.objectprodukfk
                    LEFT JOIN pasiendaftar_t AS pd ON pd.norec = so.noregistrasifk
                    INNER JOIN pasien_m AS pas ON pas.id = pd.nocmfk
                    LEFT JOIN ruangan_m AS ru ON ru.id = so.objectruanganfk
                    LEFT JOIN ruangan_m AS ru2 ON ru2.id = so.objectruangantujuanfk
                    
                    WHERE
                        so.kdprofile = $kdProfile
                    --AND pas.nocm ILIKE '%11233764%'
                    AND ru2.objectdepartemenfk = $depbedah
                    AND so.statusenabled = true
                    --and so.statusorder is null
                    and ru.kdinternal is not null
                    and so.tglpelayananawal between '$request[tanggalawal] 00:00:00' and '$request[tanggalakhir] 23:59:59'
                    ORDER BY
                        so.tglorder desc"));
            $list = [];
            foreach ($data as $k){
                $stt = $k->statusorder;
                if( $k->statusorder == null){
                    $stt = 0;
                }
                //1 sudah dilaksanakan , 0 belum ,2 batal

                $list [] = array(
                    'kodebooking' => $k->kodebooking,
                    'tanggaloperasi' => date('Y-m-d',strtotime($k->tanggaloperasi)),
                    'jenistindakan' => $k->jenistindakan,
                    'kodepoli' => $k->kodepoli,
                    'namapoli' => $k->namapoli,
                    'terlaksana' => $stt ,
                    'nopeserta' => $k->objectkelompokpasienlastfk != 2 ? '': $k->nobpjs,
                    'lastupdate' => round(microtime(true) * 1000)
                );
            }

            if(count($list) > 0){
                $result = array(
                    "response" =>
                        array(
                            "list" => $list,
                        ),
                    "metadata"=>
                        array(
                            'message' => "OK",
                            'code' => '200',
                        )
                );
            }else{
                $result = array(
                    "response" =>
                        array(),
                    "metadata"=>
                        array(
                            'message' => "Belum ada data yang bisa ditampilkan",
                            'code' => '400',
                        )
                );
            }


        } catch (Exception $e) {
            $result = array(
                "response" =>
                    array(),
                "metadata"=>
                    array(
                        'message' => "Gagal menampilkan data",
                        'code' => '400',
                    )
            );
        }
        return $this->respond($result);
    }
}
