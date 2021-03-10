<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use App\Http\Requests;
//use Namshi\JOSE\SimpleJWS;
use Namshi\JOSE\JWS;

class TeststokenController extends Controller
{
    public function index(Request $request){
        $token =  $request->header('X-AUTH-TOKEN');
//        return $token;
//        ?
        return $this->checkToken($token);
//        return $this->generateToken();
    }

    protected function  generateToken(){
        $secret = "akumahapaatuh";
        $jws  = new JWS(array(
            'alg' => 'HS256'
        ), 'SecLib');
//        dd($jws->setEncodedSignature($secret));
        $jws->setPayload(array(
            'uid' => '12',
            'nama' => 'Vinra Gunanta pandia',
        ));

//        $jws->sign(file_get_contents(SSL_KEYS_PATH . "private.key"), 'tests');

        return $jws->getTokenString();
//        return $jws->sign($secret);
//        return $jws->getSignature();
    }

    protected function checkToken($token){
        try {
            /** @var JWS $jws */
            $jws = JWS::load($token);
        } catch (\InvalidArgumentException $e) {
            return "0";
        }

//        dd($jws);

        if (!$jws->verify('JASAMEDIK', "HS512")) {
            dd($jws);
            return "kode invalid";
        }

//        if (!$jws->verify('secret', 'RS512')) {
//            return "1";
//        }

//        if (!$jws->verify($this->getPublicKey())) {
//            return false;
//        }

        return Response::json($jws->getPayload(), 200);
//        dd();
    }

}
