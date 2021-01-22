<?php
namespace App\Traits;

use Response;

Trait JsonRespon
{

    protected $statusCode = 200;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param string $header
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondNotFound($header = "Not Found")
    {
        return $this->setStatusCode(404)->respondWithError(array(), $header);
    }

    /**
     * @param string $header
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondInternalServer($header = 'Internal Server Error')
    {
        return $this->setStatusCode(500)->respondWithError(array(), $header);
    }

    /**
     * @param $message
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondValidation($message, $header = [])
    {
        return $this->setStatusCode(400)->respondWithError($message, $header);
    }

    /**
     * @param $message
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondFailed($message, $header = [])
    {
        return $this->setStatusCode(400)->respondWithError($message, $header);
    }

    /**
     * @param $data
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond($data, $header = [])
    {
//        if($this->getStatusCode()>203){
//            //errornya gimana
//        }else{
//            $data['messages'] = array(
//                "label-success" =>  $header
//            );
//        }
        if($this->getStatusCode()>200&& !is_array($header)){
            $data = (array)$data;
            $data['messages'] = $header;
        }

        if (!is_array($header)) {
            $header = array('X-MESSAGE' => $header);
        }


        // return Response::json($data, $this->getStatusCode(), $header);
        return \Illuminate\Support\Facades\Response::json($data, $this->getStatusCode(), $header);
    }

    /**
     * @param $message
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithError($message, $header = [])
    {
        $data = (count($message) > 0) ? array('errors' => $message) : array();
        return $this->respond($data, $header);
    }
}