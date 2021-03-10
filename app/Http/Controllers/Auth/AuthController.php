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
    protected $formAwal = "formularium-rev";

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
            ->sign($signer, "TRANSMEDIC")
            ->getToken();
        return $token;
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
        return Hash::make($pass);
    }


    public function show()
    {
        if (isset($_SESSION["tokenLogin"])) {
            return redirect("admin/" . $this->formAwal);
        }
        return view("auth.login");
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


    public function loginKeun(Request $r)
    {
        try {
            // if ($r->input('g-recaptcha-response') == null) {
            //     $notification = array(
            //         'message' => 'Captcha Tidak Valid !',
            //         'alert-type' => 'error'
            //     );
            //     toastr()->error('Cannot Verified Captcha ', 'Error !');
            //     return redirect()->route("login", ['username' => $r->username])->with($notification);
            // }
            $data = array('username' => $r->username, 'password' => $r->password
            // , 'recaptcha' => $r->input('g-recaptcha-response')
            );
            $data = $this->validate_input($data);
//dd($this->validate_login($data));
            if ($this->validate_login($data)) {
                if (isset($_SESSION["role"]) && $_SESSION["role"] == 'user') {
                    return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "bed"]);
                }
                return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => $this->formAwal]);
            } 
            // else if (!$this->validate_login($data)) {
            //     toastr()->error('Incorrect username or password.', 'Error !');
            //     $notification = array(
            //         'message' => 'Nama User atau Kata Sandi Salah !',
            //         'alert-type' => 'error'
            //     );
            //     return redirect()->route("login", ['username' => $r->username])->with($notification);
            // } 
            else {
                toastr()->error('Incorrect username or password.', 'Error !');
                $notification = array(
                    'message' => 'Captcha Tidak Valid !',
                    'alert-type' => 'error'
                );
                return redirect()->route("login", ['username' => $r->username])->with($notification);
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
            // $captchaResponse = $data["recaptcha"];
            // $secret = '6LdwosIZAAAAAPM9FsjT7ruqweBjFNhXbAtN3d-S';
            // // $secret = '6LeyqNAZAAAAAKO8uCFObSxNO1YWo2vzpP44ydfS';
            // $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret="
            //     . $secret . "&response=" . $captchaResponse . "&remoteip=" . $_SERVER['REMOTE_ADDR']), true);
            //   dd($response);
            $response['success'] = true;
            if ($response['success']) {
                $_SESSION["role"] = 'admin';
                $pegawai = DB::table('pegawai_m')->where('id', $user->objectpegawaifk)
                    ->first();
                $profile = DB::table('profile_m')
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
                $sts = 'cap';
            }
        } else {
            $sts = false;
        }
        return $sts;
    }

    public function logoutKeun()
    {
        //        session_start();
        //        session_unset();
        if (isset($_SESSION)) {
            session_destroy();
        }
        return redirect()->route("login");
    }

}
