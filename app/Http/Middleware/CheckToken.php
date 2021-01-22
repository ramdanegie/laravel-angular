<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Traits\AuthToken;

class CheckToken
{
    use AuthToken;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token =  $request->header('X-AUTH-TOKEN');
//        if(!$token){
//            $token = $request->input('X-AUTH-TOKEN');
//        }
        if($token){
            if(!$this->checkToken($token)){
                $data = array(
                    'code' => 403,
                    'message' => trans('auth.token_not_valid')
                );
                // return  Response::json($this->checkToken($token), 403);
                return Response::json($data, 403)->header('X-MESSAGE', trans('auth.token_not_valid'));
            }else{
                if($this->userData != null){
                    $userData = $this->userData;
                    $request->merge(compact('userData'));
                }
            }
        }else{
            $data = array(
                'code' => 401,
                'message' => trans('auth.token_not_provided')
            );
            return Response::json($data, 401)->header('X-MESSAGE', trans('auth.token_not_provided'));
        }


        return $next($request);
    }
}
