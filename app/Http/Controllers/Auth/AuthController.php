<?php

/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 7/31/2019
 * Time: 4:33 PM
 */

namespace App\Http\Controllers\Auth;

use App\Datatrans\LoggingUser;
use App\Datatrans\StrukOrder;
use App\Http\Controllers\ApiController;

use App\User;
use App\Web\Profile;
use App\Traits\CrudMaster;
use App\Traits\Valet;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Web\LoginUser;
use App\Web\Token;

use DB;
use Illuminate\Support\Facades\Hash;
use Namshi\JOSE\Base64\Base64UrlSafeEncoder;
use Namshi\JOSE\JWT;
use Namshi\JOSE\JWS;
use Namshi\JOSE\Base64\Encoder;
use Webpatser\Uuid\Uuid;

use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use App\Datatrans\PasienDaftar;
use App\Datatrans\AntrianPasienDiperiksa;
use App\Datatrans\TempatTidur;
use App\Datatrans\RegistrasiPelayananPasien;

class AuthController extends ApiController
{
    use CrudMaster, Valet;
    protected $kdProfile = 18;
    protected $formAwal = "dashboard-pelayanan";
    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }


    public function createToken($namaUser)
    {
        $class = new Builder();
        $signer = new Sha512();
        $time = time();
        // return date('Y-m-d H:i:s',$time + 30);
        $token = $class->setHeader('alg', 'HS512')
            ->set('sub', $namaUser)
            ->sign($signer, "TRANSDATA")
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 30) // Configures the expiration time of the token (exp claim) in second
            ->getToken();

        // $signer = new Sha256();
        // $time = time();

        // $token = (new Builder())
        //                 // ->issuedBy('http://example.com') // Configures the issuer (iss claim)
        //                 // ->permittedFor('http://example.org') // Configures the audience (aud claim)
        //                 // ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
        //                 ->issuedAt($time) // Configures the time that the token was issue (iat claim)
        //                 // ->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
        //                 ->expiresAt($time + 30) // Configures the expiration time of the token (exp claim)
        //                 ->withClaim('uid', 1) // Configures a new claim, called "uid"
        //                 ->set('sub', $namaUser)
        //                 ->getToken($signer, new Key('TRANSDATA')); // Retrieves the generated token
        return $token;
    }
    public function createToken2($namaUser)
    {
        $class = new Builder();
        $signer = new Sha512();
        $token = $class->setHeader('alg', 'HS512')
            ->set('sub', $namaUser)
            ->sign($signer, "JASAMEDIKA")
            ->getToken();
        return $token;
    }
    public function getSignature(Request $request)
    {
        /*
         * composer update --no-plugins --no-scripts
         * composer require lcobucci/jwt
         * sumber -> https://github.com/lcobucci/jwt
         */

        $login = DB::table('user_m')
            ->where('password', '=', $this->encryptSHA1($request->input('password')))
            ->where('username', '=', $request->input('username'));
        $LoginUser = $login->first();
        if (!empty($LoginUser)) {

            $token['X-ID'] = $LoginUser->id;
            $token['X-USERNAME'] = $LoginUser->username;
            $token['X-AUTH-TOKEN'] = $this->createToken($LoginUser->username) . '';

            return $this->setStatusCode(200)->respond($token);

            //endregion
        } else {

            return $this->setStatusCode(500)->respond('Login gagal, Username atau Password salah');
        }
    }
    public function signOuts(Request $request)
    {
        $result['code'] = 401;
        $result['message'] = 'You have not logged';
        $QueryLogin = DB::table('loginuser_s')
            //            ->where('katasandi', '=', $this->encryptSHA1($request->input('kataSandi')))
            ->where('id', '=', $request->input('kdUser'));
        $LoginUser = $QueryLogin->get();

        if (count($LoginUser) > 0) {
            $result['message'] = 'Logout Success';
            $result['id'] = $LoginUser[0]->id;
            $result['kdUser'] = $LoginUser[0]->namauser;
            $result['code'] = 200;
            $result['as'] = '#Inhuman';
        }
        $resData = array(
            'data' => $result
        );
        return response()->json($result);
    }

    protected function encryptSHA1($pass)
    {
        return sha1($pass);
    }
    protected function encryptHash($pass)
    {
        return  Hash::make($pass);
    }


    public function signIn(Request $request)
    {
        /*
         * composer update --no-plugins --no-scripts
         * composer require lcobucci/jwt
         * sumber -> https://github.com/lcobucci/jwt
         */

        $makeHash = Hash::make($request->input('kataSandi'));
        // if (Hash::check('admin',  $makeHash ))
        // return $makeHash;
        $login = DB::table('sys_users')
            // ->where('password', '=',  $makeHash  ) //$this->encryptSHA1($request->input('kataSandi'))
            ->where('username', '=', $request->input('namaUser'))
            ->where('active', '=', 1);
        $LoginUser = $login->first();
        if (!empty($LoginUser) && Hash::check($request->input('kataSandi'), $LoginUser->password)) {


            $dataLogin = array(
                'username' => $LoginUser->username,
                'fullname' => $LoginUser->fullname,
                'level' =>  $LoginUser->level,
                'foto' =>  $LoginUser->foto,
                'jabatan' =>  $LoginUser->jabatan,

            );
            $token['X-AUTH-TOKEN'] = $this->createToken($LoginUser->username) . '';

            $result = array(
                'data' => $dataLogin,
                'messages' => $token,
                'status' => 200,
                'as' => 'ramdanegie'
            );

            //endregion
        } else {
            //region Login Gagal send 400 code
            $result = array(
                'data' => [],
                'messages' => 'Login gagal, Username atau Password salah',
                'status' => 500,
                'as' => 'ramdanegie'
            );
            //endregion
        }

        return $this->setStatusCode($result['status'])->respond($result);
    }

    public function show()
    {
        if (isset($_SESSION["tokenLogin"])) {
            return redirect("admin/".$this->formAwal);
        }
        return view("auth.login");
    }
    public function showIndex(Request $r)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $set = collect(DB::select("select nilaifield from settingdatafixed_m where kdprofile=$kdProfile and namafield='kdDepartemenRanapFix'"))->first();
        $deptRanap = explode(',', $set->nilaifield);
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap) {
            $kdDepartemenRawatInap[] =  (int)$itemRanap;
        }

        $departemen = \DB::table('departemen_m as dp')
            ->select('dp.id', 'dp.kdprofile', 'dp.namadepartemen')
            ->where('dp.statusenabled', true)
            ->whereIn('dp.id', $kdDepartemenRawatInap)
            ->whereIn('dp.kdprofile', [$kdProfile, 0])
            ->orderBy('dp.namadepartemen')
            ->get();
        // dd($departemen);

        $objectruanganfk =  null;
        $objectdepartemenfk =  82;

        $ruangan = DB::table('ruangan_m')
            ->select('id', 'namaruangan')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled', true)
            ->where('objectdepartemenfk', $objectdepartemenfk)
            ->orderBy('namaruangan')
            ->get();
        if (isset($r->objectdepartemenfk)) {
            $ruangan = DB::table('ruangan_m')
                ->select('id', 'namaruangan')
                ->where('kdprofile', $kdProfile)
                ->where('statusenabled', true)
                ->where('objectdepartemenfk', $r->objectdepartemenfk)
                ->orderBy('namaruangan')
                ->get();
        }
        $stts = DB::table('statusbed_m')
            ->select('id', 'statusbed')
            //            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            //            ->where('objectdepartemenfk',16)
            ->orderBy('statusbed')
            ->get();

        $namaruang = '-';
        $namakamar = '';
        $namapasien = '';
        $statusbedfk = '';
        $objectruanganfk = $ruangan[0]->id;

        if (
            !isset($r->objectruanganfk) && !isset($r->namakamar) && !isset($r->namapasien) && !isset($r->statusbedfk)
            &&  !isset($r->objectdepartemenfk)
        ) {
            $ruangans = DB::table('ruangan_m')->where('id', $objectruanganfk)
                ->where('objectdepartemenfk', $objectdepartemenfk)->first();
            $namaruang  = $ruangans->namaruangan;
            return redirect()->route("home", [
                "objectruanganfk" => $objectruanganfk,
                'namaruang' => $namaruang,
                'namakamar' => $namakamar,
                'namapasien' => $namapasien,
                'statusbedfk' => $statusbedfk,
                'objectdepartemenfk' => $objectdepartemenfk
            ]);
        } else {
            $objectruanganfk = $r->objectruanganfk;
            if ($objectruanganfk != '') {
                $ruangans = DB::table('ruangan_m')->where('id', $objectruanganfk)
                    ->where('objectdepartemenfk', $r->objectdepartemenfk)
                    ->first();
                $namaruang  = $ruangans->namaruangan;
            } else {
                $namaruang = '';
            }

            $namakamar =  $r->namakamar;
            $namapasien =  $r->namapasien;
            $statusbedfk =  $r->statusbedfk;
            $objectdepartemenfk =  $r->objectdepartemenfk;
        }
        //        dd($namakamar);
        $param = '';
        if ($namakamar != '') {
            $param = " and kmr.namakamar ilike '%" . $namakamar . "%'";
        }
        $param2 = '';
        if ($namapasien != '') {
            $param2 = " and ps.namapasien ilike '%" . $namapasien . "%'";
        }
        $paramStatus = '';
        if ($statusbedfk != '') {
            $paramStatus = " and sb.id = " . $statusbedfk;
        }
        //        return $this->getAllBed();
        $paramruangs = '';
        if ($objectruanganfk != '') {
            $paramruangs = " and ru.id = $objectruanganfk";
        }
        $data = collect(DB::select("

                SELECT distinct
                tt.id as tt_id,
                tt.nomorbed as namabed,
                kmr.id as kmr_id,
                kmr.namakamar,
                ru.id AS id_ruangan,
                ru.namaruangan,
                sb.statusbed,ps.nocm,ps.objectjeniskelaminfk as jkid,
                ps.tgllahir,ps.namapasien,pd.tglregistrasi,
                 EXTRACT(year FROM age(current_date,ps.tgllahir)) :: int as umur,
                 sb.color,sb.txtcolor,
                 	DATE_PART('day',now()-  pd.tglregistrasi ) as lamarawat,
                 	pd.noregistrasi,ps.nohp
                FROM
                tempattidur_m AS tt
                inner JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
                inner JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                inner JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                LEFT JOIN antrianpasiendiperiksa_t as rpp on rpp.nobed=tt.id
                    and kmr.id= rpp.objectkamarfk
                    and rpp.statusenabled =true
                    and rpp.tglkeluar is null
                LEFT JOIN pasiendaftar_t AS pd ON pd.norec=rpp.noregistrasifk
                    and pd.tglpulang is  null
                    and rpp.objectruanganfk=pd.objectruanganlastfk
                    and pd.statusenabled=true
                LEFT JOIN pasien_m AS ps ON ps.id = pd.nocmfk

                WHERE tt.kdprofile = $kdProfile and
                tt.statusenabled = true and
                kmr.statusenabled = true
                $param
                $param2
                $paramStatus
               $paramruangs
                order by tt.nomorbed"));
        // $data = collect(DB::select("

        //      SELECT
        //         tt. ID AS tt_id,
        //         tt.nomorbed AS namabed,
        //         kmr. ID AS kmr_id,
        //         kmr.namakamar,
        //         ru. ID AS id_ruangan,
        //         ru.namaruangan,
        //         sb.statusbed,
        //         pd.nocm,
        //         pd.jkid,
        //         pd.tgllahir,
        //         pd.namapasien,
        //         pd.tglregistrasi,
        //         pd.umur,
        //         sb.color,
        //         sb.txtcolor,
        //     pd.lamarawat,
        //         pd.noregistrasi,
        //         pd.nohp
        //     FROM
        //         tempattidur_m AS tt
        //     INNER JOIN statusbed_m AS sb ON sb. ID = tt.objectstatusbedfk
        //     INNER JOIN kamar_m AS kmr ON kmr. ID = tt.objectkamarfk
        //     INNER JOIN ruangan_m AS ru ON ru. ID = kmr.objectruanganfk
        //     left join (select * from (
        //     select   row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum ,ps.nocm,
        //         ps.objectjeniskelaminfk AS jkid,
        //         ps.tgllahir,
        //         ps.namapasien,
        //         pd.tglregistrasi,
        //         EXTRACT (
        //             YEAR
        //             FROM
        //                 age(CURRENT_DATE, ps.tgllahir)
        //         ) :: INT AS umur,   DATE_PART(
        //             'day',
        //             now() - pd.tglregistrasi
        //         ) AS lamarawat,
        //         pd.noregistrasi,
        //         ps.nohp,apd.objectruanganfk,apd.objectkamarfk,apd.nobed
        //      from pasiendaftar_t as pd
        //     join antrianpasiendiperiksa_t as apd on pd.norec =apd.noregistrasifk
        //     and apd.objectruanganfk=pd.objectruanganlastfk
        //     join pasien_m as ps on ps.id=pd.nocmfk
        //     where pd.tglpulang is null
        //     and pd.statusenabled=TRUE
        //     and pd.kdprofile=$kdProfile
        //     $param2
        //     )as x where x.rownum=1
        //     ) as pd on pd.objectruanganfk=ru.id
        //     and pd.objectkamarfk = kmr.id
        //     and pd.nobed=tt.id
        //     WHERE
        //         tt.kdprofile = $kdProfile
        //     AND tt.statusenabled = TRUE
        //     AND kmr.statusenabled = TRUE

        //       $param

        //          $paramStatus
        //         $paramruangs

        //     ORDER BY
        //         tt.nomorbed;


        //     "));

        //         foreach ($data as $key => $row) {
        //             $count[$key] = $row->namakamar;
        //         }
        //         array_multisort($count, SORT_ASC, $data);
        //        $dataNa =$data->sortBy('namakamar')->groupBy('namakamar');
        //        dd($data);

        $sbStatus2 = DB::select(DB::raw("
         SELECT
                COUNT (tts.objectstatusbedfk) AS jml,
                sb.statusbed,
                sb.color,
                sb.txtcolor
            FROM
                statusbed_m AS sb
            LEFT JOIN (
                SELECT
                    tt.id,
                    tt.objectstatusbedfk
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE
               
                --and ru.id = $objectruanganfk
                $paramruangs
            
            ) AS tts ON (
                sb.id = tts.objectstatusbedfk
            )
            where sb.statusenabled =true
            GROUP BY
                sb.statusbed,
                sb.color,
                sb.txtcolor,
                sb.nourut
            ORDER BY
                sb.nourut;
                        "));
        //        dd($statusb);

        foreach ($data as $d) {
            $d->umur_string  = null;
            if ($d->tgllahir != null) {
                $d->umur_string = $this->getAge($d->tgllahir, date('Y-m-d'));
            }
        }
        $totalz = 0;
        $sbStatus = [];
        foreach ($sbStatus2 as $k) {
            $totalz = $totalz + (float) $k->jml;
            $sbStatus[] = array(
                "jml" =>  (float) $k->jml,
                "statusbed" =>   $k->statusbed,
                "color" =>   $k->color,
                "txtcolor" =>   $k->txtcolor,
            );
        }
        $sbStatus[] = array(
            "jml" =>  $totalz,
            "statusbed" => 'TOTAL',
            "color" =>  'bg-c-maroon',
            "txtcolor" =>   'text-white',
        );


        //        dd($stts);

        return view("module.view-bed.index", compact('departemen', 'ruangan', 'data', 'namaruang', 'statusbed', 'sbStatus', 'stts'));
    }
    public static function getAllBed(Request $r)
    {
        $parDep = '';
        if (isset($r['objectdepartemenfk'])) {
            $parDep = " and ru.objectdepartemenfk = $r[objectdepartemenfk]";
        }

        $kdProfile = $_SESSION['kdProfile'];
        $data = DB::select(DB::raw("  SELECT
sb.id,
                COUNT (tts.objectstatusbedfk) AS jml,
                sb.statusbed,
                sb.color,
                sb.txtcolor
            FROM
                statusbed_m AS sb
            LEFT JOIN (
                SELECT
                    tt.id,
                    tt.objectstatusbedfk
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE
                $parDep

            ) AS tts ON (
                sb.id = tts.objectstatusbedfk
            )
            where sb.statusenabled =true
            GROUP BY
                sb.statusbed,
                sb.color,
                sb.txtcolor,
                sb.id,
                sb.nourut
        "));
        $kamar = collect(DB::select("select *  from kamar_m where kdprofile=$kdProfile and statusenabled =true"))->count();
        $kamarRusak = collect(DB::select("  SELECT kmr.namakamar
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE
                and tt.objectstatusbedfk=5
                $parDep
                GROUP BY kmr.namakamar"))->count();
        $kamarPerawat = collect(DB::select("  SELECT kmr.namakamar
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE
                and tt.objectstatusbedfk=10
                $parDep
                GROUP BY kmr.namakamar"))->count();
        $kamarIsi = collect(DB::select("  SELECT kmr.namakamar
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE
                and tt.objectstatusbedfk=1
                $parDep
                GROUP BY kmr.namakamar"))->count();
        $totalBed = 0;
        $rusak  = 0;
        $dipakaiperawat = 0;
        $terisipasien = 0;
        foreach ($data as $k) {
            $totalBed = $totalBed +  (float) $k->jml;
            if ($k->id == 5) {
                $rusak = $rusak + (float) $k->jml;
            }
            if ($k->id == 10) {
                $dipakaiperawat = $dipakaiperawat + (float) $k->jml;
            }
            if ($k->id == 1) {
                $terisipasien = $terisipasien + (float) $k->jml;
            }
        }

        $result['totalBed'] = $totalBed;
        $result['totalKamar'] = $kamar;
        $result['totalBedRusak'] = $rusak;
        $result['totalKamarRusak'] = $kamarRusak;
        $result['totalBedPerawat'] = $dipakaiperawat;
        $result['totalKamarPerawat'] = $kamarPerawat;
        $result['totalBedUtkPasien'] =  $result['totalBed'] - ($result['totalBedRusak'] +  $result['totalBedPerawat']);
        $result['totalKamarUtkPasien'] = $result['totalKamar'] - ($result['totalKamarRusak'] +  $result['totalKamarPerawat']);
        $result['totalBedIsi'] = $terisipasien;
        $result['totalKamarIsi'] = $kamarIsi;
        $result['totalBedKapasitas'] =  $result['totalBedUtkPasien'] - $result['totalBedIsi'];
        $result['totalKamarKapasitas'] = $result['totalKamarUtkPasien'] - $result['totalKamarIsi'];
        $result['totalPresentase'] = number_format(($result['totalBedIsi'] /  $result['totalBedUtkPasien']) * 100, 2, ',', '.');
        return $result;
        //        return $data;
    }
    public function getDataBed(Request $r)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $valueEdit = collect(DB::select("
            select
            tt.id as tt_id,
            tt.nomorbed as namabed,
            kmr.id as kmr_id,
            kmr.namakamar,
            ru.id AS id_ruangan,
            ru.namaruangan,
            ps.nocm,ps.namapasien,
            ps.objectjeniskelaminfk as jkid,
            jk.jeniskelamin,ps.tgllahir,
            sb.id as sbid,sb.statusbed,
            EXTRACT(year FROM age(current_date,ps.tgllahir)) :: int as umur,
            pd.tglregistrasi,pd.noregistrasi,ps.nohp
            FROM
            tempattidur_m AS tt
            inner JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
            inner JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            inner JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
             LEFT JOIN antrianpasiendiperiksa_t as rpp on rpp.nobed=tt.id
                    and kmr.id= rpp.objectkamarfk
                    and rpp.statusenabled =true
                    and rpp.tglkeluar is null
                LEFT JOIN pasiendaftar_t AS pd ON pd.norec=rpp.noregistrasifk
                    and pd.tglpulang is  null
                    and pd.statusenabled=true
                LEFT JOIN pasien_m AS ps ON ps.id = pd.nocmfk
            --LEFT JOIN pasien_m AS ps ON ps.id = tt.nocmfk
            LEFT JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
             -- LEFT JOIN pasiendaftar_t AS pd ON pd.nocmfk = ps.id and pd.tglpulang is  null and pd.statusenabled=true
            WHERE tt.kdprofile = $kdProfile and
            tt.statusenabled = true and
            kmr.statusenabled = true
            and tt.id = $r[id]
           "))->first();
        $listStatus  = DB::table('statusbed_m')
            ->select('id', 'statusbed')
            //            ->where('kdprofile',$kdProfile)
            ->where('statusenabled', true)
            ->get();
        $valueEdit->umur_string = $this->getAge($valueEdit->tgllahir, date('Y-m-d'));
        //            dd($valueEdit);

        return view('module.view-bed.input-bed', compact(
            'valueEdit',
            'listStatus'
        ));
    }
    public function getAge($tgllahir, $now)
    {
        // dd($tgllahir);
        $datetime = new \DateTime(date($tgllahir));
        $y = $datetime->diff(new \DateTime($now))->y;
        $m = $datetime->diff(new \DateTime($now))->m;

        if ($y == 0 && $m == 0) {
            return $datetime->diff(new \DateTime($now))
                ->format('%dhr');
        }
        if ($y == 0 && $m > 0) {
            return $datetime->diff(new \DateTime($now))
                ->format('%mbln %dhr');
        }
        if ($y > 0 && $m > 0) {
            return $datetime->diff(new \DateTime($now))
                ->format('%ythn %mbln %dhr');
        }
        if ($y > 0 && $m == 0) {
            return $datetime->diff(new \DateTime($now))
                ->format('%ythn %mbln %dhr');
        }
    }
    public function getLamaRawat($tgllahir, $now)
    {
        $now = strtotime($now);
        $your_date = strtotime($tgllahir);
        $datediff = $now - $your_date;

        return round($datediff / (60 * 60 * 24));
    }
    public function saveDataBeds(Request $r)
    {
        DB::beginTransaction();
        try {
            if ($r->input('statusbeds') != 1) {
                DB::table('tempattidur_m')->where('id', $r['tt_id'])
                    ->where('kdprofile', $_SESSION["kdProfile"])->update(
                        [
                            'objectstatusbedfk' => $r->input('statusbeds'),
                            'nocmfk' =>  null,
                            'jeniskelaminfk' => null,
                        ]
                    );
            } else {
                DB::table('tempattidur_m')->where('id', $r['tt_id'])
                    ->where('kdprofile', $_SESSION["kdProfile"])->update(
                        [
                            'objectstatusbedfk' => $r->input('statusbeds'),
                            //                            'nocmfk' =>  null,
                            //                            'jeniskelaminfk' => null,
                        ]
                    );
            }
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil diupdate');
            toastr()->error('Incorrect username or password.', 'Error !');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal diupdate');
        }
        $dept = DB::table('ruangan_m')->where('id', $r['id_ruangan'])->first();
        //        return view("module.view-bed.index",compact('ruangan','data','namaruang','statusbed'));
        return redirect()->route("home", ['objectdepartemenfk' =>  !empty($dept) ? $dept->objectdepartemenfk : '', 'objectruanganfk' => $r['id_ruangan']]);
    }
    public function loginKeun(Request $r)
    {
        try {

            $data = array('username' => $r->username, 'password' => $r->password);
            $data = $this->validate_input($data);


            if ($this->validate_login($data)) {
//                dd($_SESSION["role"]);
                if (isset($_SESSION["role"]) && $_SESSION["role"] == 'user') {
                    return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "bed"]);
                }
                return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => $this->formAwal]);
            } else {
                toastr()->error('Incorrect username or password.', 'Error !');
                return redirect()->route("login",['username' =>   $r->username]);
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }
    public function validate_login($data)
    {
        $user = \DB::table('loginuser_s')
            ->where('namauser', $data["username"])
            ->where('passcode', $this->encryptSHA1($data['password']))
            ->first();
//            dd($user);
        if (!empty($user)) {
            $_SESSION["role"] = 'admin';
            $pegawai = DB::table('pegawai_m')->where('id', $user->objectpegawaifk)
                ->first();
            $profile =  DB::table('profile_m')
                ->where('id', '=', $user->kdprofile)
                ->first();
            $_SESSION["namaLengkap"] = $pegawai->namalengkap;
            $_SESSION["username"] = $user->namauser;
            $_SESSION["kdProfile"] = $profile->id;
            $_SESSION["namaProfile"] = $profile->namaexternal;
            $_SESSION["id"] = $user->id;
            $_SESSION['pegawai'] = $pegawai;
            $_SESSION["tokenLogin"] = $this->createToken2($user->namauser) . ''; //
            $sts = true;
        } else {
            $sts = false;
        }
        return $sts;
    }

    public function logoutKeun()
    {
        //        session_start();
        //        session_unset();
        if(isset($_SESSION) ){
            session_destroy();
        }
        return redirect()->route("login");
    }
    public function getDataHarian(Request $r)
    {
        $kdProfile = $_SESSION["kdProfile"];
        //        $d = $this->getPersonel();
        //        dd($d);
        //        $dari = date('Y-m-d 06:00');
        $set = collect(DB::select("select nilaifield from settingdatafixed_m where kdprofile=$kdProfile and namafield='kdDepartemenRanapFix'"))->first();
        $deptRanap = explode(',', $set->nilaifield);
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap) {
            $kdDepartemenRawatInap[] =  (int)$itemRanap;
        }

        $departemen = \DB::table('departemen_m as dp')
            ->select('dp.id', 'dp.kdprofile', 'dp.namadepartemen')
            ->where('dp.statusenabled', true)
            ->whereIn('dp.id', $kdDepartemenRawatInap)
            ->whereIn('dp.kdprofile', [$kdProfile, 0])
            ->orderBy('dp.namadepartemen')
            ->get();
        $dari = Carbon::now()->subDay(1)->format('Y-m-d 06:00');
        $sampai = date('Y-m-d 06:00');
        $objectdepartemenfk = 82;

        $now = $this->getDateIndo($sampai);
        //        dd($dari);
        if (!isset($r->dari) && !isset($r->sampai) && !isset($r->objectdepartemenfk)) {
            return redirect()->route("dataHarian", [
                "dari" => $dari,
                'sampai' => $sampai,
                'objectdepartemenfk' => $objectdepartemenfk,
            ]);
        } else {
            $dari  = $r->dari;
            $sampai =  $r->sampai;
            $objectdepartemenfk = $r->objectdepartemenfk;
        }
        $harianRanap = collect(DB::select("select * from pasiendaftar_t
                join ruangan_m as ru on ru.id=pasiendaftar_t.objectruanganlastfk
                where tglregistrasi BETWEEN '$dari' and '$sampai'
                and pasiendaftar_t.statusenabled= true
                and pasiendaftar_t.kdprofile = $kdProfile
                and ru.objectdepartemenfk=$objectdepartemenfk "))->count();
        $pulangIsolasi = collect(DB::select("select * from pasiendaftar_t
               join ruangan_m as ru on ru.id=pasiendaftar_t.objectruanganlastfk
                where tglpulang BETWEEN '$dari' and '$sampai'
                and objectstatuspulangfk  in (27,28,16,2)
                and pasiendaftar_t.statusenabled= true
                and pasiendaftar_t.kdprofile = $kdProfile 
                and ru.objectdepartemenfk=$objectdepartemenfk"))->count();
        $pulangsSembuh = collect(DB::select("select * from pasiendaftar_t
              join ruangan_m as ru on ru.id=pasiendaftar_t.objectruanganlastfk
                where pasiendaftar_t.tglpulang BETWEEN '$dari' and '$sampai'
                and pasiendaftar_t.objectstatuspulangfk  in (1,6,15,20)
                and pasiendaftar_t.statusenabled= true
                and pasiendaftar_t.kdprofile = $kdProfile 
                and ru.objectdepartemenfk=$objectdepartemenfk"))->count();
        $pulangsSembuhKum = collect(DB::select("select * from pasiendaftar_t
              join ruangan_m as ru on ru.id=pasiendaftar_t.objectruanganlastfk
                where  pasiendaftar_t.objectstatuspulangfk  in (1,6,15,20)
                and pasiendaftar_t.statusenabled= true
                and pasiendaftar_t.kdprofile = $kdProfile 
                and ru.objectdepartemenfk=$objectdepartemenfk"))->count();
        $pulangIsolasiKumulatif = collect(DB::select("select * from pasiendaftar_t
              join ruangan_m as ru on ru.id=pasiendaftar_t.objectruanganlastfk
                where pasiendaftar_t.tglpulang is not null
                and pasiendaftar_t.statusenabled= true
                and pasiendaftar_t.objectstatuspulangfk  in (27,28,16,2)
                and pasiendaftar_t.kdprofile = $kdProfile 
                and ru.objectdepartemenfk=$objectdepartemenfk"))->count();
        $pulangRujukTowerLain = collect(DB::select("SELECT
                pd.norec,pd.noregistrasi,st.statuskeluar,sp.statuspulang,
            pd.objectstatuskeluarfk,
            pd.objectstatuspulangfk
            FROM
                pasiendaftar_t as pd
            left join statuskeluar_m as st on st.id=pd.objectstatuskeluarfk
            left join statuspulang_m as sp on sp.id=pd.objectstatuspulangfk
              join ruangan_m as ru on ru.id=pd.objectruanganlastfk
            WHERE
                pd.tglpulang BETWEEN '$dari' and '$sampai'
            AND pd.kdprofile = $kdProfile
            AND pd.statusenabled = TRUE
            and pd.objectstatuspulangfk in (13,14,26) 
              and ru.objectdepartemenfk=$objectdepartemenfk
            "))->count();
        $rujuk = collect(DB::select("SELECT
                pd.norec,pd.noregistrasi,st.statuskeluar,sp.statuspulang,
            pd.objectstatuskeluarfk,
            pd.objectstatuspulangfk
            FROM
                pasiendaftar_t as pd
            left join statuskeluar_m as st on st.id=pd.objectstatuskeluarfk
            left join statuspulang_m as sp on sp.id=pd.objectstatuspulangfk
               join ruangan_m as ru on ru.id=pd.objectruanganlastfk
            WHERE
                pd.tglpulang BETWEEN '$dari' and '$sampai'
            AND pd.kdprofile = $kdProfile
            AND pd.statusenabled = TRUE
            and pd.objectstatuspulangfk in (4,5,10,11,18,19,23,24)
              and ru.objectdepartemenfk=$objectdepartemenfk
            "))->count();
        $pulangRujukKumTwrLain = collect(DB::select("SELECT
                pd.norec,pd.noregistrasi
            FROM
                pasiendaftar_t as pd
                   join ruangan_m as ru on ru.id=pd.objectruanganlastfk
            WHERE
                 pd.kdprofile = $kdProfile
            AND pd.statusenabled = TRUE
             and pd.objectstatuspulangfk in (13,14,26) 
              and ru.objectdepartemenfk=$objectdepartemenfk;
            "))->count();
        $rujukKum = collect(DB::select("SELECT
                pd.norec,pd.noregistrasi
            FROM
                pasiendaftar_t as pd
                 join ruangan_m as ru on ru.id=pd.objectruanganlastfk
            WHERE
                 pd.kdprofile = $kdProfile
            AND pd.statusenabled = TRUE
          and pd.objectstatuspulangfk in (4,5,10,11,18,19,23,24)
           and ru.objectdepartemenfk=$objectdepartemenfk;
            "))->count();
        $totalDirawat = collect(DB::select("select count(x.norec) as jml, sum(x.konfirmasi) as konfirmasi ,
                    sum(x.suspek) as suspek ,sum(x.kontakerat) as kontakerat
                    from (SELECT
                        pd.norec,pd.noregistrasi,st.status,
                    case when st.id in (2,3,4,5,6) then 1 else 0 end as konfirmasi,
                    case when st.id in (1) then 1 else 0 end as suspek,
                    case when st.id in (11) then 1 else 0 end as kontakerat
                    FROM
                        pasiendaftar_t as pd
                    left join statuscovid_m as st on st.id=pd.statuscovidfk
                     join ruangan_m as ru on ru.id=pd.objectruanganlastfk
                    WHERE
                        pd.tglpulang is null
                    AND pd.kdprofile = $kdProfile
                    AND pd.statusenabled = TRUE
                     and ru.objectdepartemenfk=$objectdepartemenfk
                    ) as x "))->first();
        $okupansi = collect(DB::select("   SELECT
                    tt.id,
                    tt.objectstatusbedfk
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE
                 and ru.objectdepartemenfk=$objectdepartemenfk
                and tt.objectstatusbedfk=1"))->count();

        $totalDirawatKumulatif = collect(DB::select("select count(x.norec) as jml, sum(x.konfirmasi) as konfirmasi ,
                    sum(x.suspek) as suspek ,sum(x.kontakerat) as kontakerat
                    from (SELECT
                        pd.norec,pd.noregistrasi,st.status,
                    case when st.id in (2,3,4,5,6) then 1 else 0 end as konfirmasi,
                    case when st.id in (1) then 1 else 0 end as suspek,
                    case when st.id in (11) then 1 else 0 end as kontakerat
                    FROM
                        pasiendaftar_t as pd
                    left join statuscovid_m as st on st.id=pd.statuscovidfk
                         join ruangan_m as ru on ru.id=pd.objectruanganlastfk
                    WHERE pd.kdprofile = $kdProfile
                    AND pd.statusenabled = TRUE
                     and ru.objectdepartemenfk=$objectdepartemenfk
                    ) as x "))->first();
        $meninggal = collect(DB::select("select count(x.norec ) as jml ,case when x.probable is null then 0 else sum(x.probable) end as probable
                from (SELECT
                pd.norec,pd.noregistrasi,
                case when pd.statuscovidfk =10 then 1 else 0 end as probable
                FROM
                pasiendaftar_t as pd
                  join ruangan_m as ru on ru.id=pd.objectruanganlastfk
                WHERE
                pd.kdprofile = $kdProfile
                AND pd.statusenabled = TRUE
                and pd.objectstatuskeluarfk =5
                and ru.objectdepartemenfk=$objectdepartemenfk
                ) as x
                group by x.probable
                            "))->first();
        $result['now'] = $now;
        $result['hour'] = '06.00 WIB';
        $result['harianRajal'] = 0;
        $result['harianRanap'] = $harianRanap;
        $result['pulangSembuh'] = $pulangsSembuh;

        $result['pulangIsolasi'] = $pulangIsolasi;
        $result['pulangRujukTowerLain'] = $pulangRujukTowerLain;

        $result['rujuk'] = $rujuk;
        $result['totalDirawat'] = $totalDirawat->jml;
        $result['dirawatKonfirmasi'] = $totalDirawat->konfirmasi;
        $result['dirawatSuspek'] = $totalDirawat->suspek;
        $result['dirawatKontakErat'] = $totalDirawat->kontakerat;
        $result['okupansiBed'] = $okupansi;
        $result['usulTotal'] = $this->getByAll($kdProfile);
        $result['kumulatif'] = array(
            'kunjunganPasien' => $totalDirawatKumulatif->jml,
            'konfirmasi' => $totalDirawatKumulatif->jml,
            'suspek' => $totalDirawatKumulatif->suspek,
            'kontakerat' => $totalDirawatKumulatif->kontakerat,
            'pulangisolasi' => $pulangIsolasiKumulatif,
            'rujuk' => $rujukKum,
            'pulangSembuhKum' => $pulangsSembuhKum,
            'meninggal' => !empty($meninggal) ? $meninggal->jml : 0,
            'probable' => !empty($meninggal) ? $meninggal->probable : 0,
            'pulangRujukTowerLain' => $pulangRujukKumTwrLain,
        );
        //        dd(  $result['usulTotal']);
        return view('module.data-harian.index', compact('departemen', 'result'));
    }
    function getByAll($kdProfile)
    {
        $data = DB::select(DB::raw("SELECT
                pd.norec,
                pd.noregistrasi,
                pd.tglregistrasi,
                ps.objectjeniskelaminfk,
              date_part('year',age(ps.tgllahir))as umur,
                date_part('day',now()-ps.tgllahir)as hari,
                kb.\"name\" as  kebangsaan
            FROM
                pasiendaftar_t AS pd
             JOIN pasien_m AS ps ON ps.id = pd.nocmfk
            LEFT JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
            left JOIN kebangsaan_m AS kb ON kb.id = ps.objectkebangsaanfk
            WHERE pd.kdprofile = $kdProfile
                and pd.statusenabled = true
                and pd.tglpulang is null"));
        $jmlBalitaL = 0;
        $jmlAnakLaki = 0;
        $jmlDewasa = 0;
        $jmlGeriatri = 0;
        $jmlAll = 0;
        $jmlLaki = 0;
        $jmlPerem = 0;
        $wna = 0;
        $wni = 0;
        foreach ($data as $item) {
            $jmlAll = $jmlAll + 1;
            //   bayi 0-30 hari
            //               anak 30 hari - 17 th
            //   dewsa >17-50 th
            //               geriatri >50  keatas
            //            return  $this->respond($item->)
            if ($item->objectjeniskelaminfk == 1) {
                $jmlLaki = (float)$jmlLaki + 1;
            }
            if ($item->objectjeniskelaminfk == 2) {
                $jmlPerem = (float)$jmlPerem + 1;
            }
            if ($item->kebangsaan == 'WNA') {
                $wna = (float)$wna + 1;
            }
            if ($item->kebangsaan == 'WNI') {
                $wni = (float)$wni + 1;
            }
            if ((float)$item->hari <= 30) {
                $jmlBalitaL = (float)$jmlBalitaL + 1;
            }
            if ((float) $item->hari > 30 && (float)$item->umur <= 17) {
                $jmlAnakLaki = (float)$jmlAnakLaki + 1;
            }

            if ((float)$item->umur > 17 && (float)$item->umur <= 60) {
                $jmlDewasa = (float)$jmlDewasa + 1;
            }

            if ((float)$item->umur > 60) {
                $jmlGeriatri = (float)$jmlGeriatri + 1;
            }
        }
        $asalRujukan = DB::select(DB::raw(" SELECT
		COUNT (asl2.objectasalrujukanfk) AS jml,
		sb.asalrujukan,sb.id
            FROM
                    asalrujukan_m AS sb
            LEFT JOIN (
            select * from (SELECT
            pd.norec,
            pd.noregistrasi,
            pd.tglregistrasi,
            asl.asalrujukan,
            apd.objectasalrujukanfk,
            row_number() over (partition by pd.noregistrasi  order by apd.tglmasuk asc) as rownum
            FROM
            pasiendaftar_t AS pd
            join antrianpasiendiperiksa_t as apd on apd.noregistrasifk = pd.norec
            left join asalrujukan_m as asl on asl.id = apd.objectasalrujukanfk
            WHERE pd.kdprofile = $kdProfile
            and pd.statusenabled = true
            and pd.tglpulang is null) as x where x.rownum=1)
              AS asl2 ON (
                            sb.id = asl2.objectasalrujukanfk
                        )
            where sb.statusenabled =true
            group by sb.asalrujukan,sb.id"));
        //        $puskes = 0;
        //        $rs = 0;
        $datangsendiri = 0;
        $rujukan = 0;
        //        $wismapademangan = 0;
        //        $rsdc = 0;
        //        $kkp = 0;

        foreach ($asalRujukan as $k) {
            //            if($k->id==1){
            //                $puskes = $k->jml;
            //            }
            //            if($k->id==2){
            //                $rs = $k->jml;
            //            }
            if ($k->id == 5) {
                $datangsendiri = $datangsendiri + (float)$k->jml;
            }
            if ($k->id != 5) {
                $rujukan  = $rujukan + (float)$k->jml;
            }

            //            if($k->id==8){
            //                $kkp = $k->jml;
            //            }
            //            if($k->id==27){
            //                $wismapademangan = $k->jml;
            //            }
            //            if($k->id==28){
            //                $rsdc = $k->jml;
            //            }
        }
        $pendidikan = DB::select(DB::raw("SELECT
        COUNT (asl2.objectpendidikanfk) AS jml,
       upper(sb.pendidikan) as pendidikan,sb.id
		FROM
		pendidikan_m AS sb
		LEFT JOIN (SELECT
		pd.norec,
		pd.noregistrasi,
		pd.tglregistrasi,case when ps.objectpendidikanfk is null
		then  21 else ps.objectpendidikanfk end as objectpendidikanfk
		FROM
		pasiendaftar_t AS pd
	    join pasien_m as ps on ps.id=pd.nocmfk
		WHERE pd.kdprofile = 18
		and pd.statusenabled = true
		and pd.tglpulang is null)
        AS asl2 ON (sb.id = asl2.objectpendidikanfk)
		where sb.statusenabled =true
		group by sb.pendidikan,sb.id"));

        $pekerjaan = DB::select(DB::raw("SELECT
        COUNT (asl2.objectpekerjaanfk) AS jml,
       upper(sb.pekerjaan) as pekerjaan,sb.id
		FROM
		pekerjaan_m AS sb
		LEFT JOIN (SELECT
		pd.norec,
		pd.noregistrasi,
		pd.tglregistrasi,
		case when ps.objectpekerjaanfk is null
		then  0 else ps.objectpekerjaanfk end as objectpekerjaanfk
		FROM
		pasiendaftar_t AS pd
	    join pasien_m as ps on ps.id=pd.nocmfk
		WHERE pd.kdprofile = 18
		and pd.statusenabled = true
		and pd.tglpulang is null)
        AS asl2 ON (sb.id = asl2.objectpekerjaanfk)
		where sb.statusenabled =true

		group by sb.pekerjaan,sb.id"));
        $resultData = array(
            'jumlah' => $jmlAll,
            'usia' => array(
                'geriatri' => $jmlGeriatri,
                'dewasa' => $jmlDewasa,
                'anak' => $jmlAnakLaki,
                'balita' => $jmlBalitaL,
            ),
            'jeniskelamin' => array(
                'laki2' => $jmlLaki,
                'perempuan' => $jmlPerem,
            ),
            'kebangsaan' => array(
                'wna' => $wna,
                'wni' => $wni,
            ),
            'asalrujukan' => $asalRujukan,
            //                $asalRujukan,
            //                array(
            //                'puskesmas' => $puskes,
            //                'rs' => $rs,
            //                'datangsendiri' => $datangsendiri,
            //                 'rujuk' => $rujukan,
            //                'kkp' => $kkp,
            //                'wismapademangan' => $wismapademangan,
            //                'rsdc' => $rsdc,
            //            ),
            'pendidikan' => $pendidikan,
            'pekerjaan' => $pekerjaan
        );
        return $resultData;
    }
    public static function getPersonel()
    {
        $kdProfile = 18;
        $data = DB::select(DB::raw("SELECT pg.id,pg.namalengkap,jp.namajabatan,pg.objectjabatanfungsionalfk,
            jp.qjabatan
            FROM pegawai_m  as pg
            left join jabatan_m as jp  on jp.id= pg.objectjabatanfungsionalfk
            where pg.kdprofile=$kdProfile and  pg.statusenabled=true
            order by jp.namajabatan "));

        $arr[] = ['id' => 1, 'no' => 'A', 'ket' => 'POSKO', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 2, 'no' => 'B', 'ket' => 'TENAGA KESEHATAN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 3, 'no' => '1', 'ket' => 'SPESIALIS PENYAKIT DALAM', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 4, 'no' => '2', 'ket' => 'SPESIALIS JANTUNG', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '3', 'ket' => 'SPESIALIS PARU', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 6, 'no' => '4', 'ket' => 'SPESIALIS RADIOLOGI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 7, 'no' => '5', 'ket' => 'SPESIALIS ANESTESI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 8, 'no' => '6', 'ket' => 'SPESIALIS ANAK', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 9, 'no' => '7', 'ket' => 'SPESIALIS PATOLOGI KLINIK', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 10, 'no' => '8', 'ket' => 'SPESIALIS KULIT DAN KELAMIN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 11, 'no' => '9', 'ket' => 'SPESIALIS THT', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 12, 'no' => '10', 'ket' => 'SPESIALIS BEDAH', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 13, 'no' => '11', 'ket' => 'SPESIALIS JIWA', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 14, 'no' => '12', 'ket' => 'DOKTER UMUM', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 15, 'no' => '13', 'ket' => 'DOKTER INTERENSHIP', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 16, 'no' => '14', 'ket' => 'DOKTER GIGI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 17, 'no' => '15', 'ket' => 'PERAWAT', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 18, 'no' => '16', 'ket' => 'KEBIDANAN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 19, 'no' => '17', 'ket' => 'AHLI GIZI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 20, 'no' => '18', 'ket' => 'APOTEKER', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 21, 'no' => '19', 'ket' => 'ASISTEN APOTEKER', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 22, 'no' => '20', 'ket' => 'ANALIS LABORATORIUM', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 23, 'no' => '21', 'ket' => 'TERAPIS GIGI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 24, 'no' => '22', 'ket' => 'PENATA RONTGEN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 25, 'no' => '23', 'ket' => 'ELEKTROMEDIK', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 26, 'no' => '24', 'ket' => 'KESEHATAN LINGKUNGAN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 27, 'no' => '25', 'ket' => 'K3RS', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 28, 'no' => '26', 'ket' => 'REKAM MEDIK', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 29, 'no' => '27', 'ket' => 'SURVEILANS', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 30, 'no' => 'C', 'ket' => 'TENAGA LAINNYA', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 31, 'no' => '1', 'ket' => 'PSIKOLOGIS KLINIS', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 32, 'no' => '2', 'ket' => 'SARJANA PSIKOLOGI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 33, 'no' => '3', 'ket' => 'DEKON NAKES', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 34, 'no' => '4', 'ket' => 'DEKON APD (kesling fhci)', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 35, 'no' => '5', 'ket' => 'DEKON RUANG RAWAT INAP', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 36, 'no' => '6', 'ket' => 'SANITASI LIMBAH MEDIS', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 37, 'no' => '7', 'ket' => 'PENGELOLA DATA LAB', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 38, 'no' => '8', 'ket' => 'ADMIN SWAB', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 39, 'no' => '9', 'ket' => 'ADMIN ID CARD', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 40, 'no' => '10', 'ket' => 'ADMIN DATA NAKES', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 41, 'no' => '11', 'ket' => 'ADMIN ABSEN NAKES', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 42, 'no' => '12', 'ket' => 'ADMIN SEKRETARIAT', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 43, 'no' => '13', 'ket' => 'ADMIN YANMED', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 44, 'no' => '14', 'ket' => 'ADMIN MCU', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 45, 'no' => '15', 'ket' => 'ADMIN FHCI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 46, 'no' => '16', 'ket' => 'ADMIN KOORDINATOR DOKTER', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 47, 'no' => '17', 'ket' => 'ADMIN ORIENTASI', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 48, 'no' => '18', 'ket' => 'ADMIN INVENTARIS', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 49, 'no' => '19', 'ket' => 'LOGISTIK NAKES/YELLOW ZONE', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 50, 'no' => '20', 'ket' => 'LOGISTIK PASIEN/RED ZONE', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 51, 'no' => '21', 'ket' => 'LOGISTIK SIMAK/INVENTARIS', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 52, 'no' => '22', 'ket' => 'DUKUNGAN LOGISTIK ALKES', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 53, 'no' => '23', 'ket' => 'DELIVERY RED ZONE/PORTER', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 54, 'no' => '24', 'ket' => 'NON MEDIS (Kemenkes)', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 55, 'no' => '25', 'ket' => 'PENGANTAR OXIGEN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 56, 'no' => '26', 'ket' => 'PENGANTAR BERKAS PASIEN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 57, 'no' => '27', 'ket' => 'PEMULASARAN JENAZAH', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 58, 'no' => '28', 'ket' => 'SOPIR AMBULAN', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 59, 'no' => '29', 'ket' => 'CALL CENTRE', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 60, 'no' => '30', 'ket' => 'IT', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 61, 'no' => '31', 'ket' => 'PEKARYA TWR.5', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 62, 'no' => '32', 'ket' => 'ADMIN TWR.5', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        foreach ($arr as $a) {
            foreach ($data as $d) {
                if ($a['id'] == $d->qjabatan) {
                    $a['aktif']  = (float) $a['aktif']  + 1;
                    $a['total']  = (float) $a['total']  + 1;
                }
            }
        }
        return $arr;
    }
    public static function getKapasitasBed()
    {

        $kdProfile = $_SESSION['kdProfile'];
        $data = collect(DB::select("SELECT
                    tt.id,
                    tt.objectstatusbedfk
                FROM
                    tempattidur_m AS tt
                INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
                INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
                WHERE
                    tt.kdprofile = $kdProfile 
                AND tt.statusenabled = TRUE
                AND kmr.statusenabled = TRUE"))->count();
        return $data;
    }
    public static function getMatkes()
    {
        $arr[] = ['id' => 1, 'no' => '1', 'ket' => 'Baju Pasien', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 2, 'no' => '2', 'ket' => 'Baju Petugas', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 3, 'no' => '3', 'ket' => 'Coverall Standar', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 4, 'no' => '4', 'ket' => 'Goggles ', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '5', 'ket' => 'Handscoon Non Steril', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '6', 'ket' => 'Handscoon Steril', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '7', 'ket' => 'Masker N95', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '8', 'ket' => 'Masker KN95', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '9', 'ket' => 'Masker Bedah', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '10', 'ket' => 'Surgical Cup', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '11', 'ket' => 'Rapid Test', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '12', 'ket' => 'VTM', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        $arr[] = ['id' => 5, 'no' => '13', 'ket' => 'Ventitalor', 'aktif' => 0, 'isolasi' => 0, 'total' => 0,];
        return $arr;
    }
    public function getDaftarPasienAktif(Request $r)
    {
        $idProfile = $_SESSION['kdProfile'];

        $set = collect(DB::select("select nilaifield from settingdatafixed_m where kdprofile=$idProfile and namafield='kdDepartemenRanapFix'"))->first();
        $deptRanap = explode(',', $set->nilaifield);
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap) {
            $kdDepartemenRawatInap[] =  (int)$itemRanap;
        }

        $departemen = \DB::table('departemen_m as dp')
            ->select('dp.id', 'dp.kdprofile', 'dp.namadepartemen')
            ->where('dp.statusenabled', true)
            ->whereIn('dp.id', $kdDepartemenRawatInap)
            ->whereIn('dp.kdprofile', [$idProfile, 0])
            ->orderBy('dp.namadepartemen')
            ->get();
        $nocm = '';
        $namapasien = '';
        $ruanganfk = '';
        $objectdepartemenfk = '';
        $paginate = 10;
        $listruangan = DB::table('ruangan_m')
            ->select('id', 'namaruangan')
            ->where('kdprofile', $idProfile)
            ->where('statusenabled', true)
            ->whereIn('objectdepartemenfk', [82])
            ->get();
        if (isset($r->objectdepartemenfk)) {
            $listruangan = DB::table('ruangan_m')
                ->select('id', 'namaruangan')
                ->where('kdprofile', $idProfile)
                ->where('statusenabled', true)
                ->whereIn('objectdepartemenfk', [$r->objectdepartemenfk])
                ->get();
        }



        $listPage = [10, 20, 30, 50, 100, 200];
        if (!isset($r->nocm) && !isset($r->namapasien) && !isset($r->ruanganfk) && !isset($r->paginate)) {
            return redirect()->route("daftarPasienAktif", [
                "nocm" => $nocm,
                "namapasien" => $namapasien,
                "ruanganfk" => $ruanganfk,
                "objectdepartemenfk" => $objectdepartemenfk,
                "paginate" => $paginate
            ]);
        } else {
            $nocm = $r->nocm;
            $namapasien = $r->namapasien;
            $ruanganfk = $r->ruanganfk;
            $objectdepartemenfk = $r->objectdepartemenfk;
            //            dd($paginate);
            $paginate = $r->paginate;
            //        dd($paginate);
        }
        $data = DB::table('pasiendaftar_t AS pd')
            ->JOIN('antrianpasiendiperiksa_t AS apd', function ($join) {
                $join->on('pd.norec', '=', 'apd.noregistrasifk');
                //                    ->on('apd.tglkeluar','null');
            })
            ->whereNull('apd.tglkeluar')
            ->JOIN('pasien_m AS p', 'p.id', '=', 'pd.nocmfk')
            ->JOIN('ruangan_m AS ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->JOIN('kelas_m AS kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->JOIN('jeniskelamin_m AS jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->LEFTJOIN('pegawai_m AS pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->LEFTJOIN('kelompokpasien_m AS kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->LEFTJOIN('departemen_m AS dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->LEFTJOIN('batalregistrasi_t AS br', 'pd.norec', '=', 'br.pasiendaftarfk')
            ->leftJOIN('kamar_m as kmr', 'kmr.id', '=', 'apd.objectkamarfk')
            ->leftJOIN('tempattidur_m as tt', 'tt.id', '=', 'apd.nobed')
            ->leftjoin('alamat_m as alm', 'alm.nocmfk', '=', 'p.id')
            ->leftjoin('statuscovid_m as sc', 'sc.id', '=', 'pd.statuscovidfk')
            ->leftjoin('asalrujukan_m as ar', 'ar.id', '=', 'apd.objectasalrujukanfk')
            ->select(DB::raw("pd.tglregistrasi,p.nocm,
				pd.noregistrasi,ru.namaruangan,p.namapasien,kp.kelompokpasien,kls.namakelas,alm.alamatlengkap,
				jk.jeniskelamin,pg.namalengkap AS namadokter,
				pd.norec AS norec_pd,pd.tglpulang,pd.statuspasien,p.tgllahir,pd.objectruanganlastfk,
				pd.objectkelasfk, apd.objectkamarfk,kmr.namakamar,apd.nobed,tt.reportdisplay as namabed,
	                EXTRACT(YEAR FROM AGE(pd.tglregistrasi, p.tgllahir)) || ' Thn '
				|| EXTRACT(MONTH FROM AGE(pd.tglregistrasi, p.tgllahir)) || ' Bln '
				|| EXTRACT(DAY FROM AGE(pd.tglregistrasi, p.tgllahir)) || ' Hr' AS umur,
				CASE WHEN pd.statuscovidfk IS NULL THEN '-' ELSE sc.status END AS status,
				CASE WHEN kmr.namakamar IS NOT NULL THEN ru.namaruangan || ' Kamar ' || kmr.namakamar || ' bed ' || tt.nomorbed
				ELSE '-' END AS kamarpasien,ar.asalrujukan ,DATE_PART('day', now() - pd.tglregistrasi) || ' Hari' as lamarawat,
				pd.tglregistrasi + INTERVAL '10 day' as rencanapulang"))
            ->where('pd.statusenabled', true)
            ->where('pd.kdprofile', $idProfile)
            ->whereNull('pd.tglpulang');
        if ($nocm != '') {
            $data = $data->where('p.nocm', 'ilike', '%' . $nocm . '%');
        }
        if ($namapasien != '') {
            $data = $data->where('p.namapasien', 'ilike', '%' . $namapasien . '%');
        }
        if ($ruanganfk != '') {
            $data = $data->where('ru.id', '=', $ruanganfk);
        }
        // if($objectdepartemenfk!=''){
        //     $data = $data->where('ru.objectdepartemenfk','=',$objectdepartemenfk);
        // }
        if ($ruanganfk != '') {
            $data = $data->where('ru.id', '=', $ruanganfk);
        }
        $data = $data->orderByRaw('ru.namaruangan,pd.tglregistrasi asc');
        $data = $data->paginate($paginate);

        //        $paginated = \CollectionHelper::paginate($data, $pageSize);
        //        dd($data->groupBy('namaruangan'));
        return view('module.daftar-pasien-aktif.index', compact('departemen', 'listruangan', 'listPage', 'data'));
    }

    public function getDepartemen(Request $request)
    {
        $kdProfile = $this->getDataKdProfile($request);
        $deptRanap = explode(',', $this->settingDataFixed('kdDepartemenRanapFix', $kdProfile));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap) {
            $kdDepartemenRawatInap[] =  (int)$itemRanap;
        }

        $data = \DB::table('departemen_m as dp')
            ->select('dp.id', 'dp.kdprofile', 'dp.namadepartemen')
            ->where('dp.statusenabled', true)
            ->whereIn('dp.id', $kdDepartemenRawatInap)
            ->whereIn('dp.kdprofile', [$kdProfile, 0])
            ->orderBy('dp.namadepartemen')
            ->get();


        return $this->respond($data);
    }
    public function getRuanganByDept(Request $r)
    {
        $kdProfile = $_SESSION['kdProfile'];
        $ruangan = DB::table('ruangan_m')
            ->select('id', 'namaruangan')
            ->where('kdprofile', $kdProfile)
            ->where('statusenabled', true)
            ->where('objectdepartemenfk', $r['dep'])
            ->orderBy('namaruangan')
            ->get();

        echo "<option value=''>-- Pilih Ruangan --</option>";
        foreach ($ruangan as $t) {
            echo "<option   value='$t->id'>" . $t->namaruangan . "</option>";
        }
    }
    public function getDaftarPasiens(Request $r)
    {

        $idProfile = $_SESSION['kdProfile'];
        //        dd($idProfile);
        $set = collect(DB::select("select nilaifield from settingdatafixed_m where kdprofile=$idProfile and namafield='kdDepartemenRanapFix'"))->first();
        $deptRanap = explode(',', $set->nilaifield);
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap) {
            $kdDepartemenRawatInap[] =  (int)$itemRanap;
        }

        $departemen = \DB::table('departemen_m as dp')
            ->select('dp.id', 'dp.kdprofile', 'dp.namadepartemen')
            ->where('dp.statusenabled', true)
            ->whereIn('dp.id', $kdDepartemenRawatInap)
            ->whereIn('dp.kdprofile', [$idProfile, 0])
            ->orderBy('dp.namadepartemen')
            ->get();
        $nocm = '';
        $namapasien = '';
        $ruanganfk = '';
        $objectdepartemenfk = '';
        $lamarawats = '';
        $ketkliniss = '';
        $paginate = 20;
        $listruangan = DB::table('ruangan_m')
            ->select('id', 'namaruangan')
            ->where('kdprofile', $idProfile)
            ->where('statusenabled', true)
            ->whereIn('objectdepartemenfk', [82])
            ->get();
        if (isset($r->objectdepartemenfk)) {
            $listruangan = DB::table('ruangan_m')
                ->select('id', 'namaruangan')
                ->where('kdprofile', $idProfile)
                ->where('statusenabled', true)
                ->whereIn('objectdepartemenfk', [$r->objectdepartemenfk])
                ->get();
        }



        $listPage = [10, 20, 30, 50, 100, 200];
        if (
            !isset($r->nocm) && !isset($r->namapasien) && !isset($r->ruanganfk) && !isset($r->paginate)
            && !isset($r->objectdepartemenfk) && !isset($r->lamarawats) && !isset($r->ketkliniss)
        ) {
            return redirect()->route("daftarPasien", [
                "nocm" => $nocm,
                "namapasien" => $namapasien,
                "ruanganfk" => $ruanganfk,
                "objectdepartemenfk" => $objectdepartemenfk,
                "paginate" => $paginate,
                "lamarawats" => $lamarawats,
                "ketkliniss" => $ketkliniss,
            ]);
        } else {
            $nocm = $r->nocm;
            $namapasien = $r->namapasien;
            $ruanganfk = $r->ruanganfk;
            $objectdepartemenfk = $r->objectdepartemenfk;
            $paginate = $r->paginate;
            $lamarawats = $r->lamarawats;
            $ketkliniss = $r->ketkliniss;
            //        dd($paginate);
        }
        $data = DB::table('pasiendaftar_t AS pd')
            ->JOIN('antrianpasiendiperiksa_t AS apd', function ($join) {
                $join->on('pd.norec', '=', 'apd.noregistrasifk');
                $join->whereNull('apd.tglkeluar');
            })
            // ->whereNull('apd.tglkeluar')
            ->JOIN('pasien_m AS p', 'p.id', '=', 'pd.nocmfk')
            ->JOIN('ruangan_m AS ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->JOIN('kelas_m AS kls', 'kls.id', '=', 'pd.objectkelasfk')
            ->JOIN('jeniskelamin_m AS jk', 'jk.id', '=', 'p.objectjeniskelaminfk')
            ->LEFTJOIN('pegawai_m AS pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->LEFTJOIN('kelompokpasien_m AS kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->LEFTJOIN('departemen_m AS dept', 'dept.id', '=', 'ru.objectdepartemenfk')
            ->LEFTJOIN('batalregistrasi_t AS br', 'pd.norec', '=', 'br.pasiendaftarfk')
            ->leftJOIN('kamar_m as kmr', 'kmr.id', '=', 'apd.objectkamarfk')
            ->leftJOIN('tempattidur_m as tt', 'tt.id', '=', 'apd.nobed')
            ->leftjoin('alamat_m as alm', 'alm.nocmfk', '=', 'p.id')
            ->leftjoin('statuscovid_m as sc', 'sc.id', '=', 'pd.statuscovidfk')
            ->leftjoin('asalrujukan_m as ar', 'ar.id', '=', 'apd.objectasalrujukanfk')
            ->leftJoin('strukorder_t  as so', function ($join) {
                $join->on('so.noregistrasifk', '=', 'pd.norec');
                $join->where('so.statusenabled', true);
                $join->where('so.objectkelompoktransaksifk', 153);
                $join->where('so.statusorder', 0);
            })
            ->leftjoin('suratketerangan_t as st', function ($join) {
                $join->on('st.pasiendaftarfk', '=', 'pd.norec');
                $join->where('st.jenissuratfk', '=', 12);
                $join->where('st.statusenabled', '=', true);
            })
            ->select(DB::raw("pd.tglregistrasi,p.nocm,
				pd.noregistrasi,ru.namaruangan,p.namapasien,kp.kelompokpasien,kls.namakelas,alm.alamatlengkap,
				jk.jeniskelamin,pg.namalengkap AS namadokter,
				pd.norec AS norec_pd,pd.tglpulang,pd.statuspasien,p.tgllahir,pd.objectruanganlastfk,
				pd.objectkelasfk, apd.objectkamarfk,kmr.namakamar,apd.nobed,tt.reportdisplay as namabed,
	                EXTRACT(YEAR FROM AGE(pd.tglregistrasi, p.tgllahir)) || ' Thn '
				|| EXTRACT(MONTH FROM AGE(pd.tglregistrasi, p.tgllahir)) || ' Bln '
				|| EXTRACT(DAY FROM AGE(pd.tglregistrasi, p.tgllahir)) || ' Hr' AS umur,
				CASE WHEN pd.statuscovidfk IS NULL THEN '-' ELSE sc.status END AS status,
				CASE WHEN kmr.namakamar IS NOT NULL THEN dept.namadepartemen ||' ' || ru.namaruangan || ' Kamar ' || kmr.namakamar || ' bed ' || tt.nomorbed
				ELSE '-' END AS kamarpasien,ar.asalrujukan ,
				EXTRACT(day from age(current_date,
             to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari' as lamarawat,
                pd.tglregistrasi + INTERVAL '10 day' as rencanapulang,p.objectjeniskelaminfk as jkid,

                apd.norec as norec_apd,so.norec as norec_so,so.tglrencana,
                CASE WHEN st.nosurat IS NOT NULL THEN SUBSTRING(st.nosurat,5) ELSE '' END AS nosurat,
                case when pd.ketklinis is not null then pd.ketklinis else
                'HIJAU' end as ketklinis
                "))
            ->where('pd.statusenabled', true)
            ->where('pd.kdprofile', $idProfile)
            ->whereNull('pd.tglpulang')
            // ->whereNull('apd.tglkeluar')
        ;
        if ($nocm != '') {
            $data = $data->where('p.nocm', 'ilike', '%' . $nocm . '%');
        }
        if ($namapasien != '') {
            $namapasien =  str_replace("'", "",  $namapasien);
            $data = $data->whereRaw("(replace(p.namapasien,'''','') ilike '%" . $namapasien . "%' 
            or replace(p.nocm,'''','') ilike '%" . $namapasien . "%' 
            )");
        }
        if ($ruanganfk != '') {
            $data = $data->where('ru.id', '=', $ruanganfk);
        }

        if ($lamarawats != '') {
            $data = $data->whereRaw(" EXTRACT(day from age(current_date,
             to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) = " . $lamarawats);
        }
        if ($objectdepartemenfk != '') {
            $data = $data->where('ru.objectdepartemenfk', '=', $objectdepartemenfk);
        }
        if ($ketkliniss != '') {
            if (strtolower($ketkliniss) == 'hijau') {
                $data = $data->whereNotIn('pd.ketklinis', ['MERAH', 'KUNING']);
            } else {
                $data = $data->where('pd.ketklinis', 'ilike', '%' . $ketkliniss . '%');
            }
        }
        $data = $data->orderByRaw('pd.tglregistrasi desc');
        // $data = $data->get();
        // dd(       $data);
        $data = $data->paginate($paginate);
        // foreach ($data as $it){
        //            dd($it);
        // $so = DB::table ('strukorder_t')
        //     ->where('norec_apd',$it->norec_apd)->first();
        // if(!empty($so)){
        //     $it->norec_so =$so->norec;
        // }else{
        //     $it->norec_so = null;
        // }
        // }

        //        session()->flash('type',"success");
        //        session()->flash('message','Data berhasil disimpan');
        //        toastr()->error('Incorrect username or password.', 'Error !');
        //         dd($data);
        return view('module.nurse.daftar-pasien', compact('departemen', 'listruangan', 'listPage', 'data'));
    }
    public function getPopUpPindah(Request $r)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $pasien = collect(DB::select("select pd.noregistrasi,ps.nocm,ps.namapasien,
                CASE WHEN kmr.namakamar IS NOT NULL THEN   dept.namadepartemen || ' ' || ru.namaruangan || ' Kamar ' || kmr.namakamar || ' Bed ' || tt.nomorbed
                            ELSE '-' END AS kamarpasien
                from antrianpasiendiperiksa_t  as apd
            join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
            join pasien_m as ps on ps.id= pd.nocmfk
            join ruangan_m as ru on ru.id= pd.objectruanganlastfk
            join departemen_m AS dept on dept.id=ru.objectdepartemenfk
            left join kamar_m as kmr on kmr.id=apd.objectkamarfk
            left join tempattidur_m as tt on tt.id=apd.nobed
            where apd.norec='$r[norec]'"))->first();
        $norec_apd =  $r['norec'];
        // dd($pasien);
        $deptRanap = explode(',', $this->settingDataFixed('kdDepartemenRanapFix', $kdProfile));
        $kdDepartemenRawatInap = [];
        foreach ($deptRanap as $itemRanap) {
            $kdDepartemenRawatInap[] =  (int)$itemRanap;
        }
        $dept = \DB::table('departemen_m as ru')
            ->select('ru.id', 'ru.namadepartemen')
            ->where('ru.statusenabled', true)
            ->wherein('ru.id', $kdDepartemenRawatInap)
            // ->where('ru.kdprofile',$kdProfile)
            ->orderBy('ru.namadepartemen', 'desc')
            ->get();
        $dataRuanganInap = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk')
            ->where('ru.statusenabled', true)
            ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatInap)
            ->where('ru.kdprofile', (int)$kdProfile)
            ->orderBy('ru.namaruangan')
            ->get();
        $dataDepartemen = [];
        foreach ($dept as $item) {
            $detail = [];
            foreach ($dataRuanganInap as $item2) {
                if ($item->id == $item2->objectdepartemenfk) {
                    $detail[] = array(
                        'id' => $item2->id,
                        'namaruangan' => $item2->namaruangan,
                    );
                }
            }

            $dataDepartemen[] = array(
                'id' => $item->id,
                'namadepartemen' => $item->namadepartemen,
                'ruangan' => $detail,
            );
        }
        $paramCari['namapasien'] = $r['namapasien'];
        $paramCari['objectdepartemenfk'] = $r['objectdepartemenfk'];
        $paramCari['ruanganfk'] = $r['ruanganfk'];
        $paramCari['lamarawats'] = $r['lamarawats'];
        //         dd($paramCari);
        return view('module.nurse.detail-pindah', compact('pasien', 'dataDepartemen', 'norec_apd', 'paramCari'));
    }
    public function getKamarByKelasRuangan(Request $request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $data = \DB::table('kamar_m as kmr')
            ->join('ruangan_m as ru', 'ru.id', '=', 'kmr.objectruanganfk')
            ->join('kelas_m as kl', 'kl.id', '=', 'kmr.objectkelasfk')
            ->select(
                'kmr.id',
                'kmr.namakamar',
                'kl.id as id_kelas',
                'kl.namakelas',
                'ru.id as id_ruangan',
                'ru.namaruangan',
                'kmr.jumlakamarisi',
                'kmr.qtybed'
            )
            ->where('kmr.objectruanganfk', $request['idRuangan'])
            ->where('kmr.objectkelasfk', $request['idKelas'])
            ->where('kmr.statusenabled', true)
            ->where('kmr.kdprofile', (int)$kdProfile)
            ->get()
            ->toArray();
        // dd($data);
        for ($i = count($data) - 1; $i >= 0; $i--) {
            $id = $data[$i]->id;

            $des =  DB::select(DB::raw("select * from tempattidur_m 
              where objectkamarfk ='$id' and objectstatusbedfk=2 and kdprofile=$kdProfile and statusenabled=true"));
            //   dd($des);
            if (count($des) == 0) {
                array_splice($data, $i, 1);
            }
        }
        echo "<option value=''>-- Pilih Kamar --</option>";
        foreach ($data as $t) {
            echo "<option   value='$t->id'>" . $t->namakamar . "</option>";
        }
    }
    public function getNoBedByKamar(Request $request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $data = \DB::table('tempattidur_m as tt')
            ->join('statusbed_m as sb', 'sb.id', '=', 'tt.objectstatusbedfk')
            ->join('kamar_m as km', 'km.id', '=', 'tt.objectkamarfk')
            ->select('tt.id', 'sb.statusbed', 'tt.reportdisplay', 'tt.nomorbed')
            ->where('tt.objectkamarfk', $request['idKamar'])
            ->where('km.statusenabled', true)
            ->where('tt.kdprofile', (int)$kdProfile)
            ->where('tt.statusenabled', true)
            ->get();

        echo "<option value=''>-- Pilih Bed --</option>";
        foreach ($data as $t) {
            echo "<option   value='$t->id'>" . $t->nomorbed . "</option>";
        }
    }
    public function savePindahPasien(Request $request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        //         dd($request->input());
        $r = $request->input();
        DB::beginTransaction();
        try {
            $datana = collect(DB::select("select pd.norec as norec_pd,pd.objectruanganlastfk,
                to_char(pd.tglregistrasi,'yyyy-MM-dd') as tglregistrasi,
                apd.objectasalrujukanfk,pd.nocmfk,ps.nocm
                from antrianpasiendiperiksa_t  as apd
                join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                join pasien_m as ps on ps.id= pd.nocmfk
                where apd.norec='$request[norec_apd]'"))->first();
            $updatePD = PasienDaftar::where('norec', $datana->norec_pd)
                ->where('kdprofile', $kdProfile)
                ->update([
                    'objectruanganlastfk' => $r['ruangan'],
                    'objectkelasfk' => 6,
                ]);

            $updateAPD = AntrianPasienDiperiksa::where('norec', $request['norec_apd'])
                ->update([
                    'tglkeluar' => date('Y-m-d H:i:s'),
                    'objectruanganasalfk' =>  $datana->objectruanganlastfk,
                ]);
            $ruangasal = DB::select(
                DB::raw("select * from antrianpasiendiperiksa_t 
                         where noregistrasifk=:noregistrasifk and objectruanganfk=:objectruanganfk;"),
                array(
                    'noregistrasifk' => $datana->norec_pd,
                    'objectruanganfk' => $datana->objectruanganlastfk,
                )
            );

            //update statusbed jadi Kosong
            foreach ($ruangasal as $Hit) {
                // return $this->respond($Hit);
                if ($kdProfile  == 18) {
                    TempatTidur::where('id', $Hit->nobed)
                        ->update([
                            'objectstatusbedfk' => 6,
                            'nocmfk' => null,
                            'jeniskelaminfk' => null,
                        ]);
                } else {
                    TempatTidur::where('id', $Hit->nobed)
                        ->update([
                            'objectstatusbedfk' => 2,
                            'nocmfk' => null,
                            'jeniskelaminfk' => null,
                        ]);
                }
            }

            $countNoAntrian = AntrianPasienDiperiksa::where('objectruanganfk', $r['ruangan'])
                ->where('kdprofile', $kdProfile)
                ->where('tglregistrasi', '>=',  $datana->tglregistrasi . ' 00:00')
                ->where('tglregistrasi', '<=',  $datana->tglregistrasi . ' 23:59')
                ->where('statusenabled', true)
                ->max('noantrian');
            $noAntrian = $countNoAntrian + 1;

            $pd = PasienDaftar::where('norec', $datana->norec_pd)->first();
            //            dd((string)$pd->tglregistrasi);
            $dataAPD = new AntrianPasienDiperiksa;
            $dataAPD->norec = $dataAPD->generateNewId();
            $dataAPD->kdprofile = $kdProfile;
            $dataAPD->statusenabled = true;
            $dataAPD->objectruanganfk = $r['ruangan'];
            $dataAPD->objectasalrujukanfk =  $datana->objectasalrujukanfk;
            $dataAPD->objectkamarfk =  $r['kamar'];
            $dataAPD->objectkasuspenyakitfk = null;
            $dataAPD->objectkelasfk = 6;
            $dataAPD->noantrian = $noAntrian; //count tgl pasien perruanga
            $dataAPD->nobed = $r['bed'];
            $dataAPD->noregistrasifk = (string)$datana->norec_pd;
            $dataAPD->statusantrian = 0;
            $dataAPD->statuskunjungan = 'LAMA';
            $dataAPD->statuspasien = 1;
            $dataAPD->tglregistrasi =  $pd->tglregistrasi;
            $dataAPD->objectruanganasalfk = $datana->objectruanganlastfk;
            $dataAPD->tglkeluar = null;
            $dataAPD->tglmasuk = date('Y-m-d H:i:s');
            $dataAPD->israwatgabung = false;
            $dataAPD->save();

            TempatTidur::where('id',  $r['bed'])
                ->update(['objectstatusbedfk' => 1]);

            $dataRPP = new RegistrasiPelayananPasien();
            $dataRPP->norec = $dataRPP->generateNewId();;
            $dataRPP->kdprofile = $kdProfile;
            $dataRPP->statusenabled = true;
            $dataRPP->objectasalrujukanfk =  $datana->objectasalrujukanfk;
            $dataRPP->israwatgabung = false;
            $dataRPP->objectkamarfk = $r['kamar'];
            $dataRPP->objectkelasfk = 6;
            $dataRPP->objectkelaskamarfk = 6;
            $dataRPP->kdpenjaminpasien = 0;
            $dataRPP->noantrianbydokter = 0;
            $dataRPP->nocmfk = $datana->nocmfk;
            $dataRPP->noregistrasifk = $datana->norec_pd;
            $dataRPP->objectruanganasalfk = $datana->objectruanganlastfk;
            $dataRPP->objectruanganfk = $r['ruangan'];
            $dataRPP->objectstatuskeluarfk = $kdProfile == 18 ? 8 : 2;
            $dataRPP->objecttempattidurfk = $r['bed'];
            $dataRPP->tglmasuk = date('Y-m-d H:i:s');
            $dataRPP->tglpindah = date('Y-m-d H:i:s');
            $dataRPP->save();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
            DB::commit();
            return redirect()->route(
                "daftarPasien",
                [
                    'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                    'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                    'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                    'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                    'paginate' => 10,
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal disimpan');
            return redirect()->route(
                "daftarPasien",
                [
                    'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                    'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                    'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                    'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                    'paginate' => 10,
                ]
            );
        }
    }
    public function getDataPulang(Request $r)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $pasien = collect(DB::select("select pd.noregistrasi,ps.nocm,ps.namapasien,
                CASE WHEN kmr.namakamar IS NOT NULL THEN   
                dept.namadepartemen || ' ' || ru.namaruangan || ' Kamar ' || kmr.namakamar || ' Bed ' || tt.nomorbed
                ELSE '-' END AS kamarpasien
                from antrianpasiendiperiksa_t  as apd
            join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
            join pasien_m as ps on ps.id= pd.nocmfk
            join ruangan_m as ru on ru.id= pd.objectruanganlastfk
            join departemen_m AS dept on dept.id=ru.objectdepartemenfk
            left join kamar_m as kmr on kmr.id=apd.objectkamarfk
            left join tempattidur_m as tt on tt.id=apd.nobed
            where apd.norec='$r[norec]'"))->first();
        $norec_apd =  $r['norec'];

        $statusKeluar = \DB::table('statuskeluar_m as st')
            ->select('st.id', 'st.statuskeluar')
            ->where('st.statusenabled', true)
            ->where('st.kdprofile', $kdProfile)
            ->orderBy('st.statuskeluar')
            ->get();
        $kondisiKeluar = \DB::table('kondisipasien_m as kp')
            ->select('kp.id', 'kp.kondisipasien')
            ->where('kp.statusenabled', true)
            ->where('kp.kdprofile', $kdProfile)
            ->orderBy('kp.kondisipasien')
            ->get();
        $statusPulang = \DB::table('statuspulang_m as sp')
            ->select('sp.id', 'sp.statuspulang')
            ->where('sp.statusenabled', true)
            ->where('sp.kdprofile', $kdProfile)
            ->orderBy('sp.statuspulang')
            ->get();
        $hubunganKeluarga = \DB::table('hubungankeluarga_m as sp')
            ->select('sp.id', 'sp.hubungankeluarga')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.hubungankeluarga')
            ->get();
        $cbo['statusKeluar'] = $statusKeluar;
        $cbo['hubunganKeluarga'] = $hubunganKeluarga;
        $cbo['statusPulang'] = $statusPulang;
        $cbo['kondisiKeluar'] = $kondisiKeluar;
        $paramCari['namapasien'] = $r['namapasien'];
        $paramCari['objectdepartemenfk'] = $r['objectdepartemenfk'];
        $paramCari['ruanganfk'] = $r['ruanganfk'];
        $paramCari['lamarawats'] = $r['lamarawats'];
        return view('module.nurse.detail-pulang', compact('pasien', 'cbo', 'norec_apd', 'paramCari'));
    }
    public  function  savePulang(Request $request)
    {
        //        $r = $request->input();
        //        dd($r);
        if (($request['statuspulang'] == 15 || $request['statuspulang'] == 20) && !isset($request['islangsung'])
            //            || $request['statuspulang'] == 16 || $request['statuspulang']== 28 ||
            //            $request['statuspulang']== 20
        ) {
            //            $r = $request->input();
            //            dd($r);
            $kdProfile = $_SESSION["kdProfile"];
            $r = $request->input();
            //        dd($r);
            DB::beginTransaction();
            try {
                $jam                                 = date("H:i:s");
                $jam2                             = "15:00:00";
                $tglnyunyu                     = date("Y-m-d");
                //                $tglnyonyo 					= date('Y-m-d', strtotime('+1 days', strtotime($tglnyunyu)));
                if ($tglnyunyu == substr($r['tglpulang'], 0, 10)) {
                    if ($jam > $jam2) {
                        DB::rollback();
                        session()->flash('type', "info");
                        session()->flash('message', 'Order Rencana Pulang di hari yang sama tidak boleh melebihi jam 15:00');
                        return redirect()->route(
                            "daftarPasien",
                            [
                                'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                                'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                                'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                                'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                                'paginate' => 10,
                            ]
                        );
                    }
                }


                $datana = collect(DB::select("select 
                pd.norec as norec_pd,pd.objectruanganlastfk,
                pd.tglregistrasi,pd.noregistrasi,
                apd.objectasalrujukanfk,pd.nocmfk,ps.nocm,
                so.norec as norec_so
                from antrianpasiendiperiksa_t  as apd
                join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
                LEFT JOIN strukorder_t AS so ON so.noregistrasifk = pd.norec
                AND so.statusenabled = true
                AND so.objectkelompoktransaksifk = 153
                AND so.statusorder = 0
                join pasien_m as ps on ps.id= pd.nocmfk
                where apd.norec='$request[norec_apd]'"))->first();
                if ($datana->norec_so ==  null) {
                    $statusLogging = "Input Rencana Pulang";
                    $keteranganLogging = "Input Rencana Pulang Pasien Noregistrasi : " .
                        $datana->noregistrasi . " Tgl Rencana Pulang : " .
                        $r['tglpulang'];
                    $noKirim = $this->generateCodeBySeqTable(
                        new StrukOrder,
                        'norencanapindah',
                        14,
                        'RPP-' . $this->getDateTime()->format('ym'),
                        $kdProfile
                    );
                    $dataRPP = new StrukOrder();
                    $dataRPP->norec = $dataRPP->generateNewId();;
                    $dataRPP->kdprofile = $kdProfile;
                    $dataRPP->statusenabled = true;
                } else {

                    $dataRPP = StrukOrder::where('norec', $datana->norec_so)
                        ->where('kdprofile', $kdProfile)
                        ->first();
                    $noKirim = $dataRPP->noorder;
                    $statusLogging = "Update Rencana Pulang";
                    $keteranganLogging = "Update Rencana Pulang Pasien Noregisrasi : " .
                        $datana->noregistrasi . " Tgl Rencana Pulang : " . $r['tglpulang'];
                }
                $dataRPP->objectkelompoktransaksifk = 153;
                $dataRPP->nocmfk = $datana->nocmfk;
                $dataRPP->noorder = $noKirim;
                $dataRPP->noregistrasifk = $datana->norec_pd;
                $dataRPP->norec_apd = $request['norec_apd'];
                $dataRPP->tglorder = $this->getDateTime()->format('Y-m-d H:i:s');
                $dataRPP->statusorder = 0;
                $dataRPP->isdelivered = 0;
                $dataRPP->qtyjenisproduk = 0;
                $dataRPP->qtyproduk = 0;
                $dataRPP->qtyjenisproduk = 0;
                $dataRPP->qtyproduk = 0;
                $dataRPP->totalbiayakirim = 0;
                $dataRPP->totalbiayatambahan = 0;
                $dataRPP->totaldiscount = 0;
                $dataRPP->totalhargasatuan = 0;
                $dataRPP->totalharusdibayar = 0;
                $dataRPP->totalpph = 0;
                $dataRPP->totalppn = 0;
                $dataRPP->totalbeamaterai = 0;
                $dataRPP->objectstatuskeluarfk = $kdProfile == 18 ? 7 : 1;
                $dataRPP->statuspasien =  $datana->norec_pd;
                $dataRPP->objectstatuspulangfk = $r['statuspulang'];
                $dataRPP->objecthubungankeluargaambilpasienfk = $r['hubungankeluarga'];
                $dataRPP->namalengkapambilpasien = $r['namapembawa'];
                $dataRPP->tglrencana = $r['tglpulang'];
                //            $dataRPP->keteranganpulang = $r['hubungankeluarga'];
                $dataRPP->objectkondisipasienfk = $r['kondisi'];
                $dataRPP->save();
                $norecRpp = $dataRPP->norec;

                //## Logging User
                $newId = LoggingUser::max('id');
                $newId = $newId + 1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile = $kdProfile;
                $logUser->statusenabled = true;
                $logUser->jenislog = $statusLogging;
                $logUser->noreff = $norecRpp;
                $logUser->referensi = 'norec Struk Order';
                $logUser->objectloginuserfk =  $_SESSION['id'];
                $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                $logUser->keterangan = $keteranganLogging;
                $logUser->save();
                DB::commit();
                session()->flash('type', "success");
                session()->flash('message', 'Data berhasil disimpan');
                return redirect()->route(
                    "daftarPasien",
                    [
                        'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                        'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                        'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                        'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                        'paginate' => 10,
                    ]
                );
            } catch (\Exception $e) {
                dd($e->getMessage());
                DB::rollback();
                session()->flash('type', "error");
                session()->flash('message', 'Data gagal disimpan');
                return redirect()->route(
                    "daftarPasien",
                    [
                        'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                        'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                        'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                        'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                        'paginate' => 10,
                    ]
                );
            }
        } else {
            $kdProfile = $_SESSION["kdProfile"];
            $r = $request->input();
            DB::beginTransaction();
            //##Update Pasiendaftar##
            try {
                $datana = collect(DB::select("select 
            pd.norec as norec_pd,pd.objectruanganlastfk,
            pd.tglregistrasi,pd.noregistrasi,
            apd.objectasalrujukanfk,pd.nocmfk,ps.nocm
            from antrianpasiendiperiksa_t  as apd
            join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
            join pasien_m as ps on ps.id= pd.nocmfk
            where apd.norec='$r[norec_apd]'"))->first();

                $updatePD = PasienDaftar::where('norec', $datana->norec_pd)
                    ->where('kdprofile', $kdProfile)
                    ->update([
                        'objecthubungankeluargaambilpasienfk' => $r['hubungankeluarga'],
                        'objectkondisipasienfk' => $r['kondisi'],
                        'namalengkapambilpasien' => $r['namapembawa'],
                        'objectstatuskeluarfk' => $kdProfile == 18 ? 7 : 1,
                        'objectstatuspulangfk' => $r['statuspulang'],
                        'tglpulang' => $r['tglpulang'],
                    ]);
                $updateAPD = AntrianPasienDiperiksa::where('norec', $r['norec_apd'])
                    ->where('kdprofile', $kdProfile)
                    ->update([
                        'tglkeluar' => $r['tglpulang'],
                    ]);

                $ruangasal = DB::select(
                    DB::raw("select * from antrianpasiendiperiksa_t 
                     where norec=:norec and kdprofile=:kdProfile;"),
                    array(
                        'kdProfile' => $kdProfile,
                        'norec' => $r['norec_apd'],
                    )
                );

                //update statusbed jadi Kosong
                foreach ($ruangasal as $Hit) {
                    $stId = 2;
                    if ($kdProfile == 18) {
                        $stId = 9;
                    }
                    TempatTidur::where('id', $Hit->nobed)->update([
                        'objectstatusbedfk' => $stId,
                        'nocmfk' =>  null,
                        'jeniskelaminfk' => null,
                    ]);

                    // TempatTidur::where('id',$Hit->nobed)->update(['objectstatusbedfk'=>2]);
                }


                $updateRPP = RegistrasiPelayananPasien::where('objectruanganfk', $datana->objectruanganlastfk)
                    ->where('noregistrasifk', $datana->norec_pd)
                    ->where('kdprofile', $kdProfile)
                    ->update([
                        'objectstatuskeluarfk' => $kdProfile == 18 ? 7 : 1,
                        'tglkeluar' => $r['tglpulang'],
                        'tglkeluarrencana' => $r['tglpulang'],
                    ]);

                //## Logging User
                $keteranganLogging = "Pulang Pasien Noregisrasi : " .
                    $datana->noregistrasi . " Tgl Pulang : " . $r['tglpulang'];
                $newId = LoggingUser::max('id');
                $newId = $newId + 1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile = $kdProfile;
                $logUser->statusenabled = true;
                $logUser->jenislog = 'Pulang Pasien';
                $logUser->noreff = $datana->norec_pd;
                $logUser->referensi = 'norec Pasien Daftar';
                $logUser->objectloginuserfk =  $_SESSION['id'];
                $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
                $logUser->keterangan = $keteranganLogging;
                $logUser->save();

                if (isset($request['islangsung'])) {
                    $Kel = StrukOrder::where('norec', $r['norecorder'])
                        ->where('kdprofile', $kdProfile)
                        ->update([
                            'statusorder' => 1,
                        ]);

                    /*Logging User*/
                    $newId = LoggingUser::max('id');
                    $newId = $newId + 1;
                    $logUser = new LoggingUser();
                    $logUser->id = $newId;
                    $logUser->norec = $logUser->generateNewId();
                    $logUser->kdprofile = $kdProfile;
                    $logUser->statusenabled = true;
                    $logUser->jenislog = 'Verifikasi Order Rencana Pulang';
                    $logUser->noreff = $request['norec'];
                    $logUser->referensi = 'norec Struk Order';
                    $logUser->objectloginuserfk = $_SESSION['id'];
                    $logUser->tanggal = date('Y-m-d H:i:s');
                    $logUser->save();
                }

                DB::commit();

                session()->flash('type', "success");
                session()->flash('message', 'Data berhasil disimpan');
                return redirect()->route(
                    "daftarPasien",
                    [
                        'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                        'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                        'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                        'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                        'paginate' => 10,
                    ]
                );
            } catch (\Exception $e) {
                dd($e->getMessage());
                DB::rollback();
                session()->flash('type', "error");
                session()->flash('message', 'Data gagal disimpan');
                return redirect()->route(
                    "daftarPasien",
                    [
                        'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                        'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                        'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                        'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                        'paginate' => 10,
                    ]
                );
            }
        }
    }
    public function saveRencanaPulangPasien($request)
    {
    }
    public function savePulangPasien($request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $r = $request->input();
        DB::beginTransaction();
        //##Update Pasiendaftar##
        try {
            $datana = collect(DB::select("select 
            pd.norec as norec_pd,pd.objectruanganlastfk,
            pd.tglregistrasi,pd.noregistrasi,
            apd.objectasalrujukanfk,pd.nocmfk,ps.nocm
            from antrianpasiendiperiksa_t  as apd
            join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
            join pasien_m as ps on ps.id= pd.nocmfk
            where apd.norec='$r[norec_apd]'"))->first();

            $updatePD = PasienDaftar::where('norec', $datana->norec_pd)
                ->where('kdprofile', $kdProfile)
                ->update([
                    'objecthubungankeluargaambilpasienfk' => $r['hubungankeluarga'],
                    'objectkondisipasienfk' => $r['kondisi'],
                    'namalengkapambilpasien' => $r['namapembawa'],
                    'objectstatuskeluarfk' => $kdProfile == 18 ? 7 : 1,
                    'objectstatuspulangfk' => $r['statuspulang'],
                    'tglpulang' => $r['tglpulang'],
                ]);
            $updateAPD = AntrianPasienDiperiksa::where('norec', $r['norec_apd'])
                ->where('kdprofile', $kdProfile)
                ->update([
                    'tglkeluar' => $r['tglpulang'],
                ]);

            $ruangasal = DB::select(
                DB::raw("select * from antrianpasiendiperiksa_t 
                     where norec=:norec and kdprofile=:kdProfile;"),
                array(
                    'kdProfile' => $kdProfile,
                    'norec' => $r['norec_apd'],
                )
            );

            //update statusbed jadi Kosong
            foreach ($ruangasal as $Hit) {
                $stId = 2;
                if ($kdProfile == 18) {
                    $stId = 9;
                }
                TempatTidur::where('id', $Hit->nobed)->update([
                    'objectstatusbedfk' => $stId,
                    'nocmfk' =>  null,
                    'jeniskelaminfk' => null,
                ]);

                // TempatTidur::where('id',$Hit->nobed)->update(['objectstatusbedfk'=>2]);
            }


            $updateRPP = RegistrasiPelayananPasien::where('objectruanganfk', $datana->objectruanganlastfk)
                ->where('noregistrasifk', $datana->norec_pd)
                ->where('kdprofile', $kdProfile)
                ->update([
                    'objectstatuskeluarfk' => $kdProfile == 18 ? 7 : 1,
                    'tglkeluar' => $r['tglpulang'],
                    'tglkeluarrencana' => $r['tglpulang'],
                ]);

            //## Logging User
            $keteranganLogging = "Pulang Pasien Noregisrasi : " .
                $datana->noregistrasi . " Tgl Pulang : " . $r['tglpulang'];
            $newId = LoggingUser::max('id');
            $newId = $newId + 1;
            $logUser = new LoggingUser();
            $logUser->id = $newId;
            $logUser->norec = $logUser->generateNewId();
            $logUser->kdprofile = $kdProfile;
            $logUser->statusenabled = true;
            $logUser->jenislog = 'Pulang Pasien';
            $logUser->noreff = $datana->norec_pd;
            $logUser->referensi = 'norec Pasien Daftar';
            $logUser->objectloginuserfk =  $_SESSION['id'];
            $logUser->tanggal = $this->getDateTime()->format('Y-m-d H:i:s');
            $logUser->keterangan = $keteranganLogging;
            $logUser->save();

            DB::commit();

            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
            return redirect()->route(
                "daftarPasien",
                [
                    'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                    'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                    'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                    'paginate' => 10,
                ]
            );
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal disimpan');
            return redirect()->route(
                "daftarPasien",
                [
                    'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                    'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                    'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                    'paginate' => 10,
                ]
            );
        }
    }
    public function getOrderKonsul(Request $request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $r = $request->input();
        $pasien = collect(DB::select("select pd.noregistrasi,ps.nocm,ps.namapasien,
                CASE WHEN kmr.namakamar IS NOT NULL THEN   dept.namadepartemen || ' ' || ru.namaruangan || ' Kamar ' || kmr.namakamar || ' Bed ' || tt.nomorbed
                            ELSE '-' END AS kamarpasien,
                            pd.norec as norec_pd,dept.id as deptid
                from antrianpasiendiperiksa_t  as apd
            join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
            join pasien_m as ps on ps.id= pd.nocmfk
            join ruangan_m as ru on ru.id= pd.objectruanganlastfk
            join departemen_m AS dept on dept.id=ru.objectdepartemenfk
            left join kamar_m as kmr on kmr.id=apd.objectkamarfk
            left join tempattidur_m as tt on tt.id=apd.nobed
            where apd.norec='$r[norec]'"))->first();

        $kelTrans = DB::table('kelompoktransaksi_m')->where('kelompoktransaksi', 'KONSULTASI DOKTER')->first();
        $data = \DB::table('strukorder_t as so')
            ->Join('pasiendaftar_t as pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->Join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
            ->leftJoin('ruangan_m as ru', 'ru.id', '=', 'so.objectruanganfk')
            ->leftJoin('ruangan_m as rutuju', 'rutuju.id', '=', 'so.objectruangantujuanfk')
            ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'so.objectpegawaiorderfk')
            ->leftJoin('pegawai_m as pet', 'pet.id', '=', 'so.objectpetugasfk')
            ->leftJoin('antrianpasiendiperiksa_t as apd', 'apd.objectstrukorderfk', '=', 'so.norec')
            ->select(
                'so.norec',
                'so.noorder',
                'so.tglorder',
                'ru.namaruangan as ruanganasal',
                'pg.namalengkap',
                'rutuju.namaruangan as ruangantujuan',
                'pet.namalengkap as pengonsul',
                'pd.noregistrasi',
                'pd.tglregistrasi',
                'ps.nocm',
                'so.keteranganorder',
                'pd.norec as norec_pd',
                'ps.namapasien',
                'pg.id as pegawaifk',
                'so.objectruangantujuanfk',
                'so.objectruanganfk',
                'apd.norec as norec_apd',
                'so.keteranganlainnya',
                'apd.objectstrukorderfk'
            )
            ->where('so.kdprofile', $kdProfile)
            ->where('so.statusenabled', true)
            ->where('so.objectkelompoktransaksifk', $kelTrans->id)
            ->where('pd.norec', $pasien->norec_pd)
            ->orderBy('so.tglorder', 'desc');
        $data = $data->get();
        $norec_pd = $pasien->norec_pd;
        $deptKonsul = explode(',', $this->settingDataFixed('KdDeptKonsul', $kdProfile));

        $kdDepartemenKonsul = [];
        foreach ($deptKonsul as $items) {
            $kdDepartemenKonsul[] = (int)$items;
        }
        $ruangan = \DB::table('ruangan_m as ru')
            ->select('ru.id', 'ru.namaruangan')
            ->where('ru.kdprofile', $kdProfile)
            ->whereIn('ru.objectdepartemenfk', $kdDepartemenKonsul)
            ->where('ru.statusenabled', true)
            ->orderBy('ru.namaruangan')
            ->get();
        $dokter = \DB::table('pegawai_m as rm')
            ->select('rm.id', 'rm.namalengkap')
            ->where('rm.kdprofile', $kdProfile)
            ->where('rm.statusenabled', true)
            ->where('rm.objectjenispegawaifk', 1)
            ->orderBy('rm.namalengkap')
            ->get();
        $paramCari['namapasien'] = $r['namapasien'];
        $paramCari['objectdepartemenfk'] = $r['objectdepartemenfk'];
        $paramCari['ruanganfk'] = $r['ruanganfk'];
        $paramCari['lamarawats'] = $r['lamarawats'];
        $selectedRuangan  = null;
        if ($pasien->deptid == 81) {
            $selectedRuangan = 13;
        } else if ($pasien->deptid == 82) {
            $selectedRuangan = 757;
        }
        return view('module.nurse.detail-konsul', compact('data', 'pasien', 'norec_pd', 'ruangan', 'dokter', 'paramCari', 'selectedRuangan'));
    }
    public function unverifKonsul(Request $request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        DB::beginTransaction();
        try {
            $strukOrder = DB::table('strukorder_t')
                ->where('norec', $request['norec_so'])
                ->update([
                    'statusenabled' => false
                ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus != 'false') {
            //            session()->flash('type',"success");
            //            session()->flash('message','Suskes');
            DB::commit();
            return true;
        } else {
            //            session()->flash('type',"error");
            //            session()->flash('message','Data gagal dihapus');
            DB::rollBack();
            return false;
        }
    }
    public function saveOrderKonsul(Request $request)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $r = $request->input();

        DB::beginTransaction();
        try {
            $dataPD = PasienDaftar::where('norec', $r['norec_pd'])->where('kdprofile', $kdProfile)->first();
            //            if ($request['norec_so'] == "") {
            $dataSO = new StrukOrder();
            $dataSO->norec = $dataSO->generateNewId();
            $dataSO->kdprofile = $kdProfile;
            $dataSO->statusenabled = true;
            $noOrder = $this->generateCode(
                new StrukOrder,
                'noorder',
                11,
                'K' . $this->getDateTime()->format('ym'),
                $kdProfile
            );
            //            } else {
            //                $dataSO = StrukOrder::where('norec', $request['norec_so'])->where('kdprofile', $kdProfile)->first();
            //                $noOrder = $dataSO->noorder;
            //            }
            $dataSO->nocmfk = $dataPD->nocmfk;
            $dataSO->isdelivered = 1;
            $dataSO->noorder = $noOrder;
            $dataSO->noorderintern = $noOrder;
            $dataSO->noregistrasifk =  $r['norec_pd'];
            if (isset($r['dokter'])) {
                $dataSO->objectpegawaiorderfk = $r['dokter'];
            }

            $dataSO->objectpetugasfk =  $_SESSION['id'];
            $dataSO->qtyjenisproduk = 0;
            $dataSO->qtyproduk = 0;
            $dataSO->objectruanganfk = $dataPD->objectruanganlastfk;
            $dataSO->objectruangantujuanfk = $r['poli'];
            $dataSO->keteranganorder = $r['keterangan'];
            $kelompokTransaksi = DB::table('kelompoktransaksi_m')->where('kelompoktransaksi', 'KONSULTASI DOKTER')->first();
            $dataSO->objectkelompoktransaksifk = $kelompokTransaksi->id;
            $dataSO->tglorder = date('Y-m-d H:i:s');
            $dataSO->totalbeamaterai = 0;
            $dataSO->totalbiayakirim = 0;
            $dataSO->totalbiayatambahan = 0;
            $dataSO->totaldiscount = 0;
            $dataSO->totalhargasatuan = 0;
            $dataSO->totalharusdibayar = 0;
            $dataSO->totalpph = 0;
            $dataSO->totalppn = 0;
            $dataSO->save();
            // dd($dataSO);

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
            return redirect()->route(
                "daftarPasien",
                [
                    'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                    'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                    'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                    'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                    'paginate' => 10,
                ]
            );
        } else {
            DB::rollBack();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
            return redirect()->route(
                "daftarPasien",
                [
                    'namapasien' => $r['namapasienc'] != null ? $r['namapasienc'] : '',
                    'objectdepartemenfk' => $r['objectdepartemenfkc'] != null ? $r['objectdepartemenfkc'] : '',
                    'ruanganfk' => $r['ruanganfkc'] != null ? $r['ruanganfkc'] : '',
                    'lamarawats' => $r['lamarawatc'] != null ? $r['lamarawatc'] : '',
                    'paginate' => 10,
                ]
            );
        }
    }
    public function getDataPulangRencana(Request $r)
    {
        $kdProfile = $_SESSION["kdProfile"];
        $pasien = collect(DB::select("select pd.noregistrasi,ps.nocm,ps.namapasien,
                CASE WHEN kmr.namakamar IS NOT NULL THEN   
                dept.namadepartemen || ' ' || ru.namaruangan || ' Kamar ' || kmr.namakamar || ' Bed ' || tt.nomorbed
                ELSE '-' END AS kamarpasien,pd.norec as norec_pd
                from antrianpasiendiperiksa_t  as apd
            join pasiendaftar_t as pd on pd.norec= apd.noregistrasifk
            join pasien_m as ps on ps.id= pd.nocmfk
            join ruangan_m as ru on ru.id= pd.objectruanganlastfk
            join departemen_m AS dept on dept.id=ru.objectdepartemenfk
            left join kamar_m as kmr on kmr.id=apd.objectkamarfk
            left join tempattidur_m as tt on tt.id=apd.nobed
            where apd.norec='$r[norec]'"))->first();
        $norec_apd =  $r['norec'];

        $statusKeluar = \DB::table('statuskeluar_m as st')
            ->select('st.id', 'st.statuskeluar')
            ->where('st.statusenabled', true)
            ->where('st.kdprofile', $kdProfile)
            ->orderBy('st.statuskeluar')
            ->get();
        $kondisiKeluar = \DB::table('kondisipasien_m as kp')
            ->select('kp.id', 'kp.kondisipasien')
            ->where('kp.statusenabled', true)
            ->where('kp.kdprofile', $kdProfile)
            ->orderBy('kp.kondisipasien')
            ->get();
        $statusPulang = \DB::table('statuspulang_m as sp')
            ->select('sp.id', 'sp.statuspulang')
            ->where('sp.statusenabled', true)
            ->where('sp.kdprofile', $kdProfile)
            ->orderBy('sp.statuspulang')
            ->get();
        $hubunganKeluarga = \DB::table('hubungankeluarga_m as sp')
            ->select('sp.id', 'sp.hubungankeluarga')
            ->where('sp.statusenabled', true)
            ->orderBy('sp.hubungankeluarga')
            ->get();
        $cbo['statusKeluar'] = $statusKeluar;
        $cbo['hubunganKeluarga'] = $hubunganKeluarga;
        $cbo['statusPulang'] = $statusPulang;
        $cbo['kondisiKeluar'] = $kondisiKeluar;

        $dataRencana = \DB::table('strukorder_t AS so')
            ->join('pasiendaftar_t AS pd', 'pd.norec', '=', 'so.noregistrasifk')
            ->join('pasien_m AS ps', 'ps.id', '=', 'so.nocmfk')
            //            ->leftjoin('jeniskelamin_m AS jk','jk.id', '=','ps.objectjeniskelaminfk')
            ->leftjoin('statuskeluar_m AS sk', 'sk.id', '=', 'so.objectstatuskeluarfk')
            ->leftjoin('statuspulang_m AS sp', 'sp.id', '=', 'so.objectstatuspulangfk')
            ->leftjoin('hubungankeluarga_m AS hk', 'hk.id', '=', 'so.objecthubungankeluargaambilpasienfk')
            //            ->leftjoin('ruangan_m AS ru','ru.id','=','pd.objectruanganlastfk')
            //            ->leftjoin('departemen_m AS dep','dep.id','=','ru.objectdepartemenfk')
            //            ->leftjoin('kelompokpasien_m AS kp','kp.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('kondisipasien_m AS kd', 'kd.id', '=', 'so.objectkondisipasienfk')
            //            ->leftjoin('suratketerangan_t as st','st.pasiendaftarfk','=','so.noregistrasifk')
            ->leftjoin('suratketerangan_t as st', function ($join) {
                $join->on('st.pasiendaftarfk', '=', 'so.noregistrasifk');
                $join->where('st.jenissuratfk', '=', 12);
                $join->where('st.statusenabled', '=', true);
            })
            ->select(DB::raw("so.noorder,so.norec as norecorder,so.tglorder,so.noregistrasifk as norec_pd,so.norec_apd,
                                     pd.tglregistrasi,so.tglrencana,ps.namapasien,so.nocmfk,ps.nocm,pd.noregistrasi,
                                    pd.objectruanganlastfk,
                                     so.objectstatuskeluarfk,sk.statuskeluar,so.objectstatuspulangfk,sp.statuspulang,
                                     so.objecthubungankeluargaambilpasienfk,hk.hubungankeluarga,so.namalengkapambilpasien,
                                     so.keteranganpulang,pd.objectkelompokpasienlastfk,pd.statuscovidfk,
                                     ps.tgllahir,pd.statuscovid,to_char(pd.tglregistrasi,'DD-MM-YYYY') AS tglRegis,
                                     so.objectkondisipasienfk,kd.kondisipasien,st.norec as norec_sk,
                                     CASE WHEN st.nosurat IS NOT NULL THEN SUBSTRING(st.nosurat,5) ELSE '' END AS nosurat,
                                     EXTRACT(day from age(current_date, to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari' as lamarawat"))
            ->where('so.statusenabled', true)
            ->where('so.objectkelompoktransaksifk', 153)
            ->where('pd.kdprofile', $kdProfile)
            ->where('so.statusorder', 0)
            ->where('pd.norec', $pasien->norec_pd)
            ->first();
        //            dd($dataRencana);

        $paramCari['namapasien'] = $r['namapasien'];
        $paramCari['objectdepartemenfk'] = $r['objectdepartemenfk'];
        $paramCari['ruanganfk'] = $r['ruanganfk'];
        $paramCari['lamarawats'] = $r['lamarawats'];
        return view(
            'module.nurse.detail-pulang-langsung',
            compact('pasien', 'cbo', 'norec_apd', 'paramCari', 'dataRencana')
        );
    }
}
