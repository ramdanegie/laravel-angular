<?php
namespace App\Http\Controllers;

// use App\Master\LoginUser;
use App\Traits\JsonRespon;

class ApiController extends Controller
{

    use JsonRespon;
    protected $current_user = null;
    protected $skip_authentication;

    //result
    protected $transStatus=true;
    protected $transMessage = null;

    //other
    protected $kdProfile=1;

    public function __construct($skip_authentication = false)
    {
        $this->skip_authentication = $skip_authentication;
        if (!$this->skip_authentication) {
            $this->middleware('auth.token');
            $this->current_user = (object)\Session::get('userData');
        }
    }
    public function validate_input($data)
    {
        $keys = array_keys($data);
        for ($i=0; $i < count($data); $i++) {
            if (is_array($data[$keys[$i]])) {
                for ($j=0; $j < count($data[$keys[$i]]); $j++) {
                    $data[$keys[$i]][$j] = trim(htmlentities(strip_tags(str_replace("  "," ",$data[$keys[$i]][$j]))));
                    if ($data[$keys[$i]][$j] == '') {
                        $data[$keys[$i]][$j] = null;
                    }
                }
            }else{
                $data[$keys[$i]] = trim(htmlentities(strip_tags(str_replace("  "," ",$data[$keys[$i]]))));
                if ($data[$keys[$i]] == '') {
                    $data[$keys[$i]] = null;
                }
            }
        }
        return $data;
    }
    public function randomString($repeat=2,$length=30,$symbol=true)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($symbol) {
            $pool .= '!@#$%^&*(_=)';
        }
        return substr(str_shuffle(str_repeat($pool, $repeat)), 0, $length);
    }

}
