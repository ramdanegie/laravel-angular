<?php
namespace App\Traits;

use Illuminate\Http\Request;

use App\Http\Requests;
use Namshi\JOSE\JWS;
use App\User;
//use Namshi\JOSE\SimpleJWS;
//Powered by Egie Ramdan

Trait AuthToken{
    protected $userData=null;


    protected function setUserData($data){
        $this->userData=$data;
    }

    protected function getUserData(){
        return $this->userData;
    }

    protected function  checkToken($token){
        try {
            /** @var JWS $jws */

            $jws = JWS::load($token);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        if (!$jws->verify('JASAMEDIKA', "HS512")) {
          return false;
        }


        $dataToken = (object)$jws->getPayload();
        $user = \DB::table('loginuser_s')->where('namauser',  $dataToken->sub)->first();
        if(!$user){
          return false;
        }

        // $filterUser = array(
        //     "username" =>$user->namauser,
        //     'id'    => $user->id
        // );

        // $this->setUserData($filterUser);
        // \Session::put('userData',$this->getUserData());
        // \Session::save();
        return true;
    }
}
