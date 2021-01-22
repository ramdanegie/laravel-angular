<?php
/**
 * Created by PhpStorm.
 * User: Abdul Rohim
 * Date: 3/20/2020
 * Time: 2:48 PM
 */

namespace App\Http\Controllers\MedicalRecord;


use App\Datatrans\Antropometri;
use App\Datatrans\Gigi;
use App\Datatrans\Hematologi;
use App\Datatrans\Imunisasi;
use App\Datatrans\Kecelakaan;
use App\Datatrans\Sakit;
use App\Datatrans\Siswa;
use App\Datatrans\TandaVital;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class MedisisController extends ApiController
{
    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }

    /*public function checkAvailability(Request $request)
    {
        $url = "https://medisis-api.mybeam.me/api/link_medisis/check_nik";

        $payload = array(
            "data" => $request['data']
        );

        $header[] = "Content-Type: application/json";

        $client = new \GuzzleHttp\Client(['verify' => 'C:\wamp\bin\php\php7.2.18\cacert.pem']);
        //$client = new \GuzzleHttp\Client();

        $response = $client->post($url,  [
            'headers' => $header,
            'form_params' => $payload
        ]);

        if ($response->getStatusCode() == 200) {
            $json = $response->getBody()->getContents();
            $json = json_decode($json);
            $message = '';
        } else {
            $json = null;
            $message = 'wrong access';
        }

        $result = array(
            'data' => $json,
            'message' => $message,
            'status' => $response->getStatusCode(),
            'as' => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function getProfilebyNISN(Request $request)
    {
        $url = "https://medisis-api.mybeam.me/api/link_medisis/get_profile";

        $payload[] = $request['nisn'];

        $header[] = "Content-Type: application/json";

        $client = new \GuzzleHttp\Client(['verify' => 'C:\wamp\bin\php\php7.2.18\cacert.pem']);
        //$client = new \GuzzleHttp\Client();

        $response = $client->post($url,  [
            'headers' => $header,
            'form_params' => $payload
        ]);

        if ($response->getStatusCode() == 200) {
            $json = $response->getBody()->getContents();
            $json = json_decode($json);
            $message = '';
        } else {
            $json = null;
            $message = 'wrong access';
        }

        $result = array(
            'data' => $json->data,
            'response' => $json->response,
            'message' => $message,
            'status' => $response->getStatusCode(),
            'as' => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result);
    }*/

    public function checkAvailability(Request $request)
    {
        $payload = $request['data'];

        for ($i = 0; $i < count($payload); $i++) {
            $res = \DB::table('siswa_m')->select('nisn')->where('nik', $payload[$i]['nik'])->first();

            $result[] = array(
                'nik' => $payload[$i]['nik'],
                'nisn' => Siswa::where('nik', $payload[$i]['nik'])->first() ? $res->nisn : NULL,
                'status' => Siswa::where('nik', $payload[$i]['nik'])->first() ? true : false
            );
        }

        $result = array(
            'data' => $result,
            'message' => '',
            'status' => 200,
            'as' => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function getProfilebyNISN(Request $request)
    {
        $payload = Siswa::where('nisn', $request['nisn'])->first();

        $result = array(
            'data' => $payload,
            'message' => '',
            'status' => 200,
            'as' => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function saveDataSiswa(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Siswa();
        $saveData->nisn = $data['nisn'];
        $saveData->nik = $data['nik'];
        $saveData->namasiswa = $data['nama'];
        $saveData->tgllahir = $data['lahir'];
        //$saveData->tempatlahir = $data['tempatlahir'];
        $saveData->jeniskelamin = $data['jk'];
        $saveData->golongandarahfk = $data['gd'];
        $saveData->alergi = $data['alergi'];
        $saveData->keteranganalergi = $data['ket'];
        $saveData->nohp = $data['no_hp'];
        $saveData->createdat = $data['created_at'];

        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function updateDataSiswa(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Siswa::where('nisn', $data['nisn'])->first();
        $saveData->nisn = $data['nisn'];
        $saveData->nik = $data['nik'];
        $saveData->namasiswa = $data['nama'];
        $saveData->tgllahir = $data['lahir'];
        //$saveData->tempatlahir = $data['tempatlahir'];
        $saveData->jeniskelamin = $data['jk'];
        $saveData->golongandarahfk = $data['gd'];
        $saveData->alergi = $data['alergi'];
        $saveData->keteranganalergi = $data['ket'];
        $saveData->nohp = $data['no_hp'];

        $saveData->save();

        $transMessage = "Update Berhasil";
        DB::commit();

        $result = array(
            "status" => 201,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveAntropometri(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Antropometri();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->berat = $data['m'];
        $saveData->tinggi = $data['t'];
        $saveData->bmi = $data['bmi'];
        $saveData->tglperiksa = $data['tgl'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteAntropometri(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Antropometri::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveHematologi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Hematologi();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->tglperiksa = $data['tgl'];
        $saveData->hgb = $data['hgb'];
        $saveData->wbc = $data['wbc'];
        $saveData->plt = $data['plt'];
        $saveData->hct = $data['hct'];
//        $saveData->normalhgb = $data['normal_hgb'];
//        $saveData->normalwbc = $data['normal_wbc'];
//        $saveData->normalplt = $data['normalplt'];
//        $saveData->normalhct = $data['normalhct'];
//        $saveData->limithgbhigh = $data['limithgbhigh'];
//        $saveData->limithgblow = $data['limithgblow'];
//        $saveData->limitwbchigh = $data['limitwbchigh'];
//        $saveData->limitwbclow = $data['limitwbclow'];
//        $saveData->limitplthigh = $data['limitplthigh'];
//        $saveData->limitpltlow = $data['limitpltlow'];
//        $saveData->limithcthigh = $data['limithcthigh'];
//        $saveData->limithctlow = $data['limithctlow'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteHematologi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Hematologi::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveTandaVital(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new TandaVital();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->tglperiksa = $data['tgl'];
        $saveData->sistolik = $data['sist'];
        $saveData->diastolik = $data['diast'];
//        $saveData->normalsistolik = $data['normal_sist'];
//        $saveData->normaldiastolik = $data['normal_diast'];
//        $saveData->limitsistoliklow = $data['limit_sist_low'];
//        $saveData->limitsistolikhigh = $data['limit_sist_high'];
//        $saveData->limitdiastoliklow = $data['limit_diast_low'];
//        $saveData->limitdiastolikhigh = $data['limit_diast_high'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteTandaVital(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = TandaVital::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveKecelakaan(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Kecelakaan();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->tglkecelakaan = $data['tgl'];
        $saveData->idjeniskecelakaan = $data['kc'];
        $saveData->keterangan = $data['ket'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteKecelakaan(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Kecelakaan::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveSakit(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Sakit();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->tglsakit = $data['tgl'];
        $saveData->idpenyakit = $data['kp'];
        $saveData->keterangan = $data['ket'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteSakit(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Sakit::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveImunisasi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Imunisasi();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->tglimunisasi = $data['tgl'];
        $saveData->idjenisimunisasi = $data['jenis'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteImunisasi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Imunisasi::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveGigi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = new Gigi();
        $saveData->id = $data['insert_id'];
        $saveData->npsn = $data['npsn'];
        $saveData->nisn = $data['nisn_'];
        $saveData->tglperiksa = $data['tgl'];
        $saveData->susu = $data['susu'];
        $saveData->permanen = $data['permanen'];
        $saveData->save();

        $transMessage = "Simpan Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function updateGigi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Gigi::where('id', $data['id'])->first();
        $saveData->susu = $data['susu'];
        $saveData->permanen = $data['permanen'];
        $saveData->updatedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Ubah Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun'
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function deleteGigi(Request $request)
    {
        DB::beginTransaction();
        $data = $request->all();

        $saveData = Gigi::where('id', $data['id'])->first();
        $saveData->deletedat = date('Y-m-d H:i:s');
        $saveData->save();

        $transMessage = "Hapus Berhasil";
        DB::commit();

        $result = array(
            "status" => 200,
            "message" => $transMessage,
            "data" => $data,
            "as" => 'ingsun',
        );

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
}
