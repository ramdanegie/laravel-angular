<?php

namespace App\Http\Controllers\EMR;

use App\Datatrans\EECG;
use App\Datatrans\PasienDaftar;
use App\Datatrans\Pegawai;
use App\Http\Controllers\ApiController;
use App\Model\Master\EMR;
use App\Model\Master\EMRD;

use App\Model\Transaksi\EMRPasienD_Temp;
use App\Transaksi\EmrFoto;
use App\Transaksi\EMRPasien;
use App\Transaksi\EMRPasienD;
use App\User;
use App\Traits\Valet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Webpatser\Uuid\Uuid;


class EMRController2 extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }

    public function getRekamMedisAtuh(Request $request)
    {
        $this->kdProfile = $_SESSION['kdProfile'];

        $dataRaw = \DB::table('emrd_t as emrd')
            ->join('emr_t as emr', 'emr.id', '=', 'emrd.emrfk')
            ->where('emr.id', $request['emrid'])
//            ->where('emrd.kdprofile', $this->kdProfile)
            ->where('emrd.statusenabled', '=', true)
            ->select('emrd.*', 'emr.namaemr', 'emr.caption as captionemr', 'emr.classgrid')
            ->orderBy('emrd.nourut');
        $dataRaw = $dataRaw->get();

        $dataraw3A = [];
        $dataraw3B = [];
        $dataraw3C = [];
        $dataraw3D = [];
        foreach ($dataRaw as $dataRaw2) {
            $head = '';
            if ($dataRaw2->kolom == 1) {
                $dataraw3A[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                    'children' => [],
                    'text' => $head . $dataRaw2->caption,
                    'state' => array(
                        'opened' => false,
                    ),
                );
            } elseif ($dataRaw2->kolom == 2) {
                $dataraw3B[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                    'children' => [],
                    'text' => $head . $dataRaw2->caption,
                    'state' => array(
                        'opened' => false,
                    ),
                    'parent' => (string)$dataRaw2->headfk
                );
            } elseif ($dataRaw2->kolom == 3) {
                $dataraw3C[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                    'children' => [],
                    'text' => $head . $dataRaw2->caption,
                    'state' => array(
                        'opened' => false,
                    ),
                    'parent' => (string)$dataRaw2->headfk
                );
            } else {
                $dataraw3D[] = array(
                    'kdprofile' => $dataRaw2->kdprofile,
                    'statusenabled' => $dataRaw2->statusenabled,
                    'kodeexternal' => $dataRaw2->kodeexternal,
                    'namaexternal' => $dataRaw2->namaexternal,
                    'reportdisplay' => $dataRaw2->reportdisplay,
                    'emrfk' => $dataRaw2->emrfk,
                    'caption' => $head . $dataRaw2->caption,
                    'type' => $dataRaw2->type,
                    'nourut' => $dataRaw2->nourut,
                    'satuan' => $dataRaw2->satuan,
                    'id' => $dataRaw2->id,
                    'headfk' => $dataRaw2->headfk,
                    'namaemr' => $dataRaw2->namaemr,
                    'style' => $dataRaw2->style,
                    'cbotable' => $dataRaw2->cbotable,
                    'child' => [],
                    'children' => [],
                    'text' => $head . $dataRaw2->caption,
                    'state' => array(
                        'opened' => false,
                    ),
                    'parent' => (string)$dataRaw2->headfk
                );
            }

            $title = $dataRaw2->captionemr;
            $classgrid = $dataRaw2->classgrid;
        }
//        $dataA = $dataraw3A;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                    $elements[$parent_id]['children'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }


        $dataA = [];
        $dataB = [];
        $dataC = [];
        $dataD = [];
        if (count($dataraw3A) > 0) {
            $dataA = recursiveElements($dataraw3A);
        }
        if (count($dataraw3B) > 0) {
            $dataB = recursiveElements($dataraw3B);
        }
        if (count($dataraw3C) > 0) {
            $dataC = recursiveElements($dataraw3C);
        }
        if (count($dataraw3D) > 0) {
            $dataD = recursiveElements($dataraw3D);
        }


        $result = array(
            'kolom1' => $dataA,
            'kolom2' => $dataB,
            'kolom3' => $dataC,
            'kolom4' => $dataD,
            'title' => isset($title) ? $title : '',
            'classgrid' => isset($classgrid) ? $classgrid : '',
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function getMenuRekamMedisAtuh(Request $request)
    {
        $this->kdProfile = $_SESSION['kdProfile'];
        $dataRaw = \DB::table('emr_t as emr')
//            ->where('emr.kdprofile',$this->kdProfile)
            ->where('emr.statusenabled', true)
            ->where('emr.namaemr', $request['namaemr'])
            ->select('emr.*')
            ->orderBy('emr.nourut');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
            $dataraw3[] = array(
                'id' => $dataRaw2->id,
                'kdprofile' => $dataRaw2->kdprofile,
                'statusenabled' => $dataRaw2->statusenabled,
                'kodeexternal' => $dataRaw2->kodeexternal,
                'namaexternal' => $dataRaw2->namaexternal,
                'reportdisplay' => $dataRaw2->reportdisplay,
                'namaemr' => $dataRaw2->namaemr,
                'caption' => $dataRaw2->caption,
                'headfk' => $dataRaw2->headfk,
                'nourut' => $dataRaw2->nourut,
                'text' => $dataRaw2->caption,
                'state' => array(
                    'selected' => false,
                ),
            );
        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                    $elements[$parent_id]['children'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }

        $data = recursiveElements($data);

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function saveMenuEMR(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        DB::beginTransaction();
        try {
            if ($r['id'] == '') {
                $mp = EMR::where('kdprofile', $idProfile)->max('id');
                $length = 5;
                $prefixLen = 2;
                $prefix = $idProfile;
                $subPrefix = substr(trim($mp), $prefixLen);
                $id = $prefix . (str_pad((int)$subPrefix + 1, $length - $prefixLen, "0", STR_PAD_LEFT));
                $model = new EMR();
                $model->id = $id;
                $model->kdprofile = $idProfile;
                $model->statusenabled = true;
                $nourut = EMR::where('namaemr', $r['namaemr'])
                        ->where('kdprofile', $idProfile)->count() + 1;
                $model->nourut = $nourut;
            } else {
                $model = EMR::where('id', $r['id'])->first();
            }
//            $model->kodeexternal =true;
//            $model->namaexternal =true;
//            $model->reportdisplay =true;
            $model->namaemr = $r['namaemr'];
            $model->caption = $r['caption'];
            $model->headfk = $r['headfk'];
            $model->classgrid = 'grid_12';
            $model->save();
            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }
        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteMenuEMR(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        DB::beginTransaction();
        try {
            $model = EMR::where('id', $r['id'])->update([
                'statusenabled' => false
            ]);

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }
        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveEMRD(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        DB::beginTransaction();
        try {
            $cek = EMRD::where('kdprofile', $idProfile)->where('emrfk', $r['data'][0]['emrfk'])->first();
            if (empty($cek)) {
                foreach ($r['data'] as $it) {
                    $mp = EMRD::where('kdprofile', $idProfile)->max('id');
                    $length = 8;
                    $prefixLen = 2;
                    $prefix = $idProfile;
                    $subPrefix = substr(trim($mp), $prefixLen);
                    $id = $prefix . (str_pad((int)$subPrefix + 1, $length - $prefixLen, "0", STR_PAD_LEFT));

                    $model = new EMRD();
                    $model->id = $id;
                    $model->kdprofile = $idProfile;
                    $model->statusenabled = true;
//                    $nourut = EMRD::where('emrfk', $it['emrfk'])
//                            ->where('kdprofile', $idProfile)->count() + 1;
                    $model->nourut = $id;//$nourut;
                    $model->kodeexternal = null;
                    $model->namaexternal = null;
                    $model->reportdisplay = null;
                    $model->headfk = $it['headfk'];
                    $model->caption = $it['caption'];
                    $model->type = $it['type'];
                    $model->satuan = $it['satuan'];
                    $model->cbotable = $it['cbotable'];
                    $model->emrfk = $it['emrfk'];
                    $model->save();
                }
            }

            $transStatus = true;
        } catch (\Exception $e) {
            $transStatus = false;
        }
        if ($transStatus) {
            $transMessage = 'Sukses';
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        } else {
            $transMessage = 'Hapus Gagal';
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message' => $transMessage,
                'as' => 'er@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getCboSediaan(Request $request)
    {
        $idProfile = (int)$_SESSION['kdProfile'];;
        $data = \DB::table('rm_sediaan_m')
            ->select('id as value', 'name as text')
//            ->where('kdprofile', $idProfile)
            ->where('statusenabled', true)
            ->orderBy('name')
            ->get();;

        return $this->respond($data);
    }

    public function getEMRTransaksiDetail(Request $request)
    {
        //todo : detail
        $kdProfile = (int)$_SESSION['kdProfile'];;

        $data = \DB::table('emrpasiend_t as emrdp')
            ->leftjoin('emrd_t as emrd', 'emrd.id', '=', 'emrdp.emrdfk')
            ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'emrdp.pegawaifk')
            ->select('emrdp.*', 'emrd.caption', 'emrd.type', 'emrd.nourut', 'emrdp.emrfk', 'emrd.reportdisplay', 'emrd.kodeexternal as kodeex', 'emrd.satuan', 'pg.namalengkap')
            ->where('emrdp.statusenabled', true)
            ->where('emrdp.kdprofile', $kdProfile)
            ->whereNotNull('emrdp.value')
            ->where('emrdp.value', '!=', 'Invalid date')
            ->orderBy('emrd.nourut');
        if (isset($request['noemr']) && $request['noemr'] != '') {
            $data = $data->where('emrdp.emrpasienfk', $request['noemr']);
        }
        if (isset($request['emrfk']) && $request['emrfk'] != '') {
            $data = $data->where('emrdp.emrfk', $request['emrfk']);
        }

        if (isset($request['objectid']) && $request['objectid'] != '') {
            $data = $data->where('emrdp.emrdfk', $request['objectid']);
        }
        if (isset($request['nik']) && $request['nik'] != '') {
            // $data = $data->where('ps.noidentitas', $request['nik']);
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function SaveTransaksiEMRBackup(Request $request)
    {
        $kdProfile = (int)$_SESSION['kdProfile'];
        $dataReq = $request->all();
        $head = $dataReq['head'];
        $data = $dataReq['data'];

        DB::beginTransaction();
        try {

            if ($head['norec_emr'] == '-') {

                $noemr = $this->generateCodeBySeqTable(new EMRPasien(), 'noemr', 15, 'MR' . date('ym') . '/', $kdProfile);
                // return $noemr;
                if ($noemr == '') {
                    $transMessage = "Gagal mengumpukan data, Coba lagi.!";
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $transMessage,
                        "as" => 'as@epic',
                    );
                    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                }

                $EMR = new EMRPasien();
                $norecHead = $EMR->generateNewId();
                $EMR->norec = $norecHead;
                $norecTehMenikitunyaeuy = $norecHead;
                $EMR->norec = $norecTehMenikitunyaeuy;
                $EMR->kdprofile = $kdProfile;
                $EMR->statusenabled = 1;
                if (isset($head['noregistrasi'])) {
                    $EMR->noregistrasifk = $head['noregistrasi'];
                }
                $EMRPASIENDETAIL = [];
                $EMRPASIENDETAILIMG = [];

            } else {
                $EMR = EMRPasien::where('noemr', $head['norec_emr'])
                    ->where('noregistrasifk', $head['noregistrasi'])
                    ->where('kdprofile', $kdProfile)
                    ->first();
                if (!empty($EMR)) {
                    $noemr = $EMR->noemr;
                } else {
                    $noemr = $head['norec_emr'];
                }
                //LOAD DATA EMR PEMBANDING
                $EMRPASIENDETAIL = EMRPasienD::where('emrpasienfk', $noemr)
                    ->select('emrdfk', 'value')
                    ->where('emrfk', $head['emrfk'])
                    ->where('kdprofile', $kdProfile)
                    ->where('statusenabled', 1)
                    ->orderBy('emrdfk')
                    ->get();
                if (isset($dataReq['dataimg'])) {
                    $EMRPASIENDETAILIMG = EmrFoto::where('noemrpasienfk', $noemr)
                        ->select('emrdfk', 'image')
                        ->where('emrfk', $head['emrfk'])
                        ->where('kdprofile', $kdProfile)
                        ->where('statusenabled', 1)
                        ->orderBy('emrdfk')
                        ->get();
                }

            }
            if (!empty($EMR)) {
                //VALIDASI JIKA NOREGISTRASI BEDA //
                if (trim($EMR->noregistrasifk) != $head['noregistrasi']) {
                    $transMessage = "Kesalahan loading data..!";
                    DB::rollBack();
                    $result = array(
                        "status" => 400,
                        "message" => $transMessage,
                        "as" => 'as@epic',
                    );
                    return $this->setStatusCode($result['status'])->respond($result, $transMessage);
                }
            }

            $EMR->noemr = $noemr;
            $EMR->emrfk = $head['emrfk'];
            $EMR->nocm = $head['nocm'];
            $EMR->namapasien = $head['namapasien'];
            $EMR->jeniskelamin = $head['jeniskelamin'];
            $EMR->umur = $head['umur'];
            if (isset($head['kelompokpasien'])) {
                $EMR->kelompokpasien = $head['kelompokpasien'];
            }
            if (isset($head['tglregistrasi'])) {
                $EMR->tglregistrasi = $head['tglregistrasi'];
            }
            if (isset($head['norec'])) {
                $EMR->norec_apd = $head['norec'];
            }
            if (isset($head['namakelas'])) {
                $EMR->namakelas = $head['namakelas'];
            }
            if (isset($head['namaruangan'])) {
                $EMR->namaruangan = $head['namaruangan'];
            } else {
                $EMR->namaruangan = "Triage Gawat Darurat";
            }
            if (isset($head['tgllahir'])) {
                $EMR->tgllahir = $head['tgllahir'];
            }
            if (isset($head['notelepon'])) {
                $EMR->notelepon = $head['notelepon'];
            }
            if (isset($head['alamatlengkap'])) {
                $EMR->alamat = $head['alamatlengkap'];
            }
            if (isset($head['jenisemr'])) {
                $EMR->jenisemr = $head['jenisemr'];
            }
            $EMR->pegawaifk = $this->getCurrentUserID();
            $EMR->tglemr = $this->getDateTime()->format('Y-m-d H:i:s');
            $EMR->save();

            $norec_EMR = $EMR->noemr;

            $i = 0;
            $sama = 0;
            $j = 0;
            $h = 0;
            foreach ($data as $item) {
                $emrdfk = $item['id'];
                if (is_array($item['values'])) {
                    $valueemr = $item['values']['value'] . '~' . $item['values']['text'];
                } else {
                    $valueemr = $item['values'];
                }
                $sama = 0;
                foreach ($EMRPASIENDETAIL as $emrupdate) {
                    $sama = 0;
                    if ($emrupdate->emrdfk == $emrdfk) {
                        $sama = 1;
                        if ($emrupdate->value != $valueemr) {
                            $sama = 2;
                            break;
                        }
                        break;
                    }
                }

                if ($sama == 2) {
                    $EMRPasienDUpdatekeun = EMRPasienD::where('emrpasienfk', $norec_EMR)
                        ->where('emrfk', $head['emrfk'])
                        ->where('emrdfk', $emrdfk)
                        ->where('kdprofile', $kdProfile)
                        ->where('statusenabled', 1)
                        ->update([
                            'value' => $valueemr
                        ]);
                    $j++;
                }
                $EMRD = [];
                if ($sama == 0) {
                    $EMRD = new EMRPasienD();
                    $norecD = $EMRD->generateNewId();
                    $EMRD->norec = $norecD;
                    $EMRD->kdprofile = $kdProfile;
                    $EMRD->statusenabled = 1;
                    $EMRD->emrpasienfk = $norec_EMR;
                    $EMRD->value = $valueemr;
                    $EMRD->emrdfk = $emrdfk;
                    $EMRD->emrfk = $head['emrfk'];
                    $EMRD->pegawaifk = $this->getCurrentUserID();
                    $EMRD->tgl = $this->getDateTime()->format('Y-m-d H:i:s');
                    $EMRD->save();
                    $h++;
                }

                $i = $i + 1;
            }

            if (isset($dataReq['image']) && $dataReq['image'] != null) {

                $img = $dataReq['image'];
                $datas = unpack("H*hex", $img);
                $datas = '0x' . $datas['hex'];

                $dataGambar = \DB::table('emrfoto_t as tt')
                    ->where('tt.noemrpasienfk', '=', $norec_EMR)
                    ->where('tt.emrfk', '=', $head['emrfk'])
                    ->where('tt.kdprofile', $kdProfile)
                    ->first();
//                return $this->respond($noRec);
                if ($dataGambar == '' || $dataGambar == null) {
                    $emrFto = new EmrFoto();
                    $norecFto = $emrFto->generateNewId();
                    $emrFto->norec = $norecFto;
                    $emrFto->kdprofile = $kdProfile;
                    $emrFto->statusenabled = 1;
                    $emrFto->noemrpasienfk = $norec_EMR;
                    $emrFto->emrfk = $head['emrfk'];
                } else {
                    $emrFto = EmrFoto::where('noemrpasienfk', $norec_EMR)
                        ->where('kdprofile', $kdProfile)->first();

                }
                $emrFto->image = \DB::raw("CONVERT(VARBINARY(MAX), $datas)");
                $emrFto->save();
            }

            if (isset($dataReq['dataimg'])) {
                $i2 = 0;
                $sama2 = 0;
                $j2 = 0;
                $h2 = 0;
                $dataImg = $dataReq['dataimg'];
                foreach ($dataImg as $item2) {
                    if ($item2['values'] != '../app/images/svg/no-image.svg') {

                        $emrdfk2 = $item2['id'];
                        $valueemr2 = $item2['values'];

                        $sama2 = 0;
                        foreach ($EMRPASIENDETAILIMG as $emrupdate) {
                            $sama2 = 0;
                            if ($emrupdate->emrdfk == $emrdfk2) {
                                $sama2 = 1;
                                if ($emrupdate->image != $valueemr2) {
                                    $sama2 = 2;
                                    break;
                                }
                                break;
                            }
                        }

                        if ($sama2 == 2) {
                            $EMRPasienDUpdatekeun2 = EmrFoto::where('noemrpasienfk', $norec_EMR)
                                ->where('emrfk', $head['emrfk'])
                                ->where('emrdfk', $emrdfk2)
                                ->where('kdprofile', $kdProfile)
                                ->where('statusenabled', 1)
                                ->update([
                                    'image' => $valueemr2
                                ]);
                            $j2++;
                        }
                        $EMRD2 = [];
                        if ($sama2 == 0) {
                            $EMRD2 = new EmrFoto();
                            $norecD2 = $EMRD2->generateNewId();
                            $EMRD2->norec = $norecD2;
                            $EMRD2->kdprofile = $kdProfile;
                            $EMRD2->statusenabled = 1;
                            $EMRD2->noemrpasienfk = $norec_EMR;
                            $EMRD2->image = $valueemr2;
                            $EMRD2->emrdfk = $emrdfk2;
                            $EMRD2->emrfk = $head['emrfk'];
                            $EMRD2->save();
                            $h2++;
                        }

                        $i2 = $i2 + 1;
                    }
                }
            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        $transMessage = 'Saving EMR Pasien ';

        if ($transStatus == 'true') {
            $transMessage = $transMessage . "Sukses";
            DB::commit();
            $result = array(
                "status" => 201,
                "data" => $EMR,
                // "data2" => $EMRD,
                "jumlah" => count($EMRPASIENDETAIL),
                "update" => $j,
                "new" => $h,
                "as" => 'as@epic',
            );
//            $this->saveEMRBackup($data, $head, $norec_EMR,$kdProfile);

        } else {
            $transMessage = $transMessage . " Gagal!!";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "data" => $data,
                "e" => $e->getMessage() . ' Line ' . $e->getLine(),
                "as" => 'as@epic',
            );
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveEMRBackup($data, $head, $norec_EMR, $kdProfile)
    {
        DB::beginTransaction();
        // try {
        foreach ($data as $item) {
            $emrdfk = $item['id'];
            if (is_array($item['values'])) {
                $valueemr = $item['values']['value'] . '~' . $item['values']['text'];
            } else {
                $valueemr = $item['values'];
            }
            $EMRD_temp = new \App\Transaksi\EMRPasienD_Temp();
            $norecDs = $EMRD_temp->generateNewId();
            $EMRD_temp->norec = $norecDs;
            $EMRD_temp->kdprofile = $kdProfile;
            $EMRD_temp->statusenabled = 1;
            $EMRD_temp->emrpasienfk = $norec_EMR;
            $EMRD_temp->value = $valueemr;
            $EMRD_temp->emrdfk = $emrdfk;
            $EMRD_temp->emrfk = $head['emrfk'];
            $EMRD_temp->pegawaifk = $this->getCurrentUserID();
            $EMRD_temp->tgl = $this->getDateTime()->format('Y-m-d H:i:s');
            $EMRD_temp->save();
        }
        $transStatus = 'true';
        if ($transStatus == 'true') {
            DB::commit();
        } else {
            DB::rollBack();
        }
    }

    public function getEMRTransaksiDetailForm(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int)$kdProfile;
        //todo : detail
        $paramNocm = '';
        $paramNoreg = '';
        $data = \DB::table('emrpasien_t as emrp')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'emrp.pegawaifk')
            ->select('emrp.*', 'emrp.noregistrasifk as noregistrasi', 'pg.namalengkap')
            ->where('emrp.statusenabled', true)
            ->where('emrp.kdprofile', $idProfile)
            ->orderBy('emrp.tglemr', 'desc');

        if (isset($request['noemr']) && $request['noemr'] != '') {
            $data = $data->where('emrp.noemr', $request['noemr']);
        }
        if (isset($request['emrfk']) && $request['emrfk'] != '') {
            $data = $data->where('emrdp.emrfk', $request['emrfk']);
        }
        if (isset($request['norec']) && $request['norec'] != '') {
            $data = $data->where('emrp.norec', $request['norec']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('emrp.nocm', $request['nocm']);
            $paramNocm = "AND emrp.nocm=" . $request['nocm'];
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['noregistrasi']);
            $paramNoreg = "AND emrp.noregistrasifk='" . $request['noregistrasi'] . "'";
        }
        if (isset($request['tgllahir']) && $request['tgllahir'] != "" && $request['tgllahir'] != "undefined" && $request['tgllahir'] != "null") {
            $tgllahir = $request['tgllahir'];
            $data = $data->whereRaw("format( emrp.tgllahir, 'yyyy-MM-dd')  ='$tgllahir' ");
        }
        if (isset($request['namapasien']) && $request['namapasien'] != '') {
            $data = $data->where('emrp.namapasien', $request['namapasien']);
        }
        if (isset($request['jenisEmr']) && $request['jenisEmr'] != '') {
            $data = $data->where('emrp.jenisemr', 'ilike', '%' . $request['jenisEmr'] . '%');
        } else {
            $data = $data->whereNull('emrp.jenisemr');
        }
        $data = $data->get();
        $jenisEMr = $request['jenisEmr'];
        $result = [];
        foreach ($data as $item) {
            $noemr = $item->noemr;

            $details = DB::select(DB::raw("
             SELECT
                    emrdp.emrpasienfk,
                    emrdp.emrfk,
                    emr.reportdisplay,
                    emrp.norec,emr.caption as namaform
                FROM
                    emrpasiend_t AS emrdp
                INNER JOIN emrpasien_t AS emrp ON emrp.noemr = emrdp.emrpasienfk
                --INNER JOIN emrd_t AS emrd ON emrd.id = emrdp.emrdfk
               INNER JOIN emr_t AS emr ON emr.id = emrdp.emrfk
                WHERE emrdp.kdprofile = $idProfile and
                    emrdp.statusenabled = true
                   $paramNocm
                   $paramNoreg
                    AND emrp.jenisemr ILIKE '%$jenisEMr%'
                    and emrdp.emrpasienfk='$noemr'
                    GROUP BY emrdp.emrpasienfk,
                    emrdp.emrfk,emrp.norec,emr.caption,
                    emr.reportdisplay

            "));

            $result [] = array(
                'norec' => $item->norec,
                'kdprofile' => $item->kdprofile,
                'statusenabled' => $item->statusenabled,
                'kodeexternal' => $item->kodeexternal,
                'emrfk' => $item->emrfk,
                'noregistrasifk' => $item->noregistrasifk,
                'noemr' => $item->noemr,
                'nocm' => $item->nocm,
                'namapasien' => $item->namapasien,
                'jeniskelamin' => $item->jeniskelamin,
                'noregistrasi' => $item->noregistrasi,
                'umur' => $item->umur,
                'kelompokpasien' => $item->kelompokpasien,
                'tglregistrasi' => $item->tglregistrasi,
                'norec_apd' => $item->norec_apd,
                'namakelas' => $item->namakelas,
                'namaruangan' => $item->namaruangan,
                'tglemr' => $item->tglemr,
                'tgllahir' => $item->tgllahir,
                'notelepon' => $item->notelepon,
                'alamat' => $item->alamat,
                'jenisemr' => $item->jenisemr,
                'pegawaifk' => $item->pegawaifk,
                'namalengkap' => $item->namalengkap,
                'details' => $details
            );
        }
        $result = array(
            'data' => $result,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getEMRTransaksiRiwayat(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int)$kdProfile;
        //todo : Riwayat
        $data = \DB::table('emrpasien_t as emrp')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'emrp.pegawaifk')
            ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'emrp.dokterfk')
            ->leftJoin('pegawai_m as pg3', 'pg3.id', '=', 'emrp.notifikasifk')
            ->select('emrp.*', 'emrp.noregistrasifk as noregistrasi', 'pg.namalengkap', 'pg2.namalengkap as dokter', 'pg3.namalengkap as notifikasi')
            ->where('emrp.kdprofile', $idProfile)
            ->where('emrp.statusenabled', true)
            ->orderBy('emrp.tglemr', 'desc');
        if (isset($request['norec_pd']) && $request['norec_pd'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['norec_pd']);
        }
        if (isset($request['norec_apd']) && $request['norec_apd'] != '') {
            $data = $data->where('emrp.norec_apd', $request['norec_apd']);
        }
        if (isset($request['nocm']) && $request['nocm'] != '') {
            $data = $data->where('emrp.nocm', $request['nocm']);
        }
        if (isset($request['noregistrasi']) && $request['noregistrasi'] != '') {
            $data = $data->where('emrp.noregistrasifk', $request['noregistrasi']);
        }
        if (isset($request['tgllahir']) && $request['tgllahir'] != "" && $request['tgllahir'] != "undefined" && $request['tgllahir'] != "null") {
            $tgllahir = $request['tgllahir'];
            $data = $data->whereRaw("to_char( emrp.tgllahir, 'yyyy-MM-dd')  ='$tgllahir' ");
        }
        if (isset($request['namapasien']) && $request['namapasien'] != '') {
            $data = $data->where('emrp.namapasien', $request['namapasien']);
        }
        if (isset($request['jenisEmr']) && $request['jenisEmr'] != '') {
            $data = $data->where('emrp.jenisemr', 'ilike', '%' . $request['jenisEmr'] . '%');
        } else {
            $data = $data->whereNull('emrp.jenisemr');
        }
        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getEMRTransData(Request $request)
    {

        $idProfile = (int)$_SESSION['kdProfile'];
        //todo : Riwayat
        $data = \DB::table('emrpasien_t as emrp')
            ->select('emrp.*')
            ->where('emrp.kdprofile', $idProfile)
            ->where('emrp.statusenabled', true)
            ->where('emrp.jenisemr', 'formularium')
            ->orderBy('emrp.tglemr', 'desc')
            ->get();
        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );
        return $this->respond($result);
    }

    public function getTreeFormularium(Request $request)
    {
        $this->kdProfile = $_SESSION['kdProfile'];
        $dataRaw = \DB::table('formularium_t as emr')
//            ->where('emr.kdprofile',$this->kdProfile)
            ->where('emr.statusenabled', true)
            ->where('emr.namaemr', $request['namaemr'])
            ->select('emr.*')
            ->orderBy('emr.nourut');
        $dataRaw = $dataRaw->get();
        foreach ($dataRaw as $dataRaw2) {
            $dataraw3[] = array(
                'id' => $dataRaw2->id,
                'kdprofile' => $dataRaw2->kdprofile,
                'statusenabled' => $dataRaw2->statusenabled,
                'kodeexternal' => $dataRaw2->kodeexternal,
                'namaexternal' => $dataRaw2->namaexternal,
                'reportdisplay' => $dataRaw2->reportdisplay,
                'namaemr' => $dataRaw2->namaemr,
                'caption' => $dataRaw2->caption,
                'headfk' => $dataRaw2->headfk,
                'nourut' => $dataRaw2->nourut,
                'text' => $dataRaw2->caption,
                'state' => array(
                    'selected' => false,
                ),
            );
        }
        $data = $dataraw3;

        function recursiveElements($data)
        {
            $elements = [];
            $tree = [];
            foreach ($data as &$element) {
//                $element['child'] = [];
                $id = $element['id'];
                $parent_id = $element['headfk'];

                $elements[$id] = &$element;
                if (isset($elements[$parent_id])) {
                    $elements[$parent_id]['child'][] = &$element;
                    $elements[$parent_id]['children'][] = &$element;
                } else {
                    if ($parent_id <= 10) {
                        $tree[] = &$element;
                    }
                }
                //}
            }
            return $tree;
        }

        $data = recursiveElements($data);

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);

    }

    public function getDetailFormularium(Request $request)
    {
        $this->kdProfile = $_SESSION['kdProfile'];
        $data = DB::table('formulariumd_t')
            ->select('namagenerik')
            ->where('statusenabled',true)
            ->where('emrfk',$request['id'])
            ->groupBy('namagenerik')
//            ->orderBy('nourut')
            ->get();

        $attrs = DB::table('formulariumd_t')
            ->where('statusenabled',true)
            ->where('emrfk',$request['id'])
            ->orderBy('nourut')
            ->get();

        foreach ($data as $key => $value) {
            $value->details = [];
            foreach ($attrs  as $v2){
                if($v2->namagenerik == $value->namagenerik){
                    $value->details []= $v2   ;
                }
            }
        }

        $result = array(
            'data' => $data,
            'message' => 'as@epic',
        );

        return $this->respond($result);
    }

}
