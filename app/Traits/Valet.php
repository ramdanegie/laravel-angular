<?php
namespace App\Traits;

use App\Datatrans\SeqNumber;
use Carbon\Carbon;

use DB;

Trait Valet
{
    protected function generateCodeBySeqTable($objectModel, $atrribute, $length=8, $prefix=''){
        DB::beginTransaction();
        try {
            $result = SeqNumber::where('seqnumber', 'LIKE', $prefix.'%')
                ->where('seqname',$atrribute)
                ->where('kdprofile',1)
                ->max('seqnumber');
            $prefixLen = strlen($prefix);
            $subPrefix = substr(trim($result),$prefixLen);
            $SN = $prefix.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));

            $newSN = new SeqNumber();
            $newSN->kdprofile = 1;
            $newSN->seqnumber = $SN;
            $newSN->tgljamseq = date('Y-m-d H:i:s');;
            $newSN->seqname = $atrribute;
            $newSN->save();

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }

        if ($transStatus == 'true') {
            DB::commit();
            return $SN;
        } else {
            DB::rollBack();
            return '';
        }

        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }
    protected function generateCode($objectModel, $atrribute, $length=8, $prefix='',$kdProfile){
        $result = $objectModel->where($atrribute, 'LIKE', $prefix.'%')
            ->where('kdprofile',$kdProfile)
            ->max($atrribute);
        $prefixLen = strlen($prefix);
        $subPrefix = substr(trim($result),$prefixLen);
        return $prefix.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));
    }
    protected function generateCodeDibelakang($objectModel, $atrribute, $length=8, $prefix=''){
        $result = $objectModel->where($atrribute, 'LIKE', '%'.$prefix)->max($atrribute);
        $prefixLen = strlen($prefix);
        $subPrefix = substr(trim($result),$prefixLen);
        return (str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT)).$prefix;
    }
    protected function generateCode2($objectModel, $atrribute, $length=0, $prefix=''){
        $result = $objectModel->where($atrribute, 'LIKE', $prefix.'%')->max($atrribute);
        $prefixLen = strlen($prefix);
        $subPrefix = substr(trim($result),$prefixLen);
        return $prefix.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));
    }
    public static function terbilangs($angka) {
        $angka=abs($angka);
        $baca =array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");

        $terbilang="";
        if ($angka < 12){
            $terbilang= " " . $baca[$angka];
        }
        else if ($angka < 20){
            $terbilang= static::terbilangs($angka - 10) . " Belas";
        }
        else if ($angka < 100){
            $terbilang= static::terbilangs($angka / 10) . " Puluh" . static::terbilangs($angka % 10);
        }
        else if ($angka < 200){
            $terbilang= " Seratus" . static::terbilangs($angka - 100);
        }
        else if ($angka < 1000){
            $terbilang= static::terbilangs($angka / 100) . " Ratus" . static::terbilangs($angka % 100);
        }
        else if ($angka < 2000){
            $terbilang= " Seribu" . terbilangs($angka - 1000);
        }
        else if ($angka < 1000000){
            $terbilang= static::terbilangs($angka / 1000) . " Ribu" . static::terbilangs($angka % 1000);
        }
        else if ($angka < 1000000000){
            $terbilang= static::terbilangs($angka / 1000000) . " Juta" . static::terbilangs($angka % 1000000);
        }
        return $terbilang;
    }
    protected function getCountArray($objectArr){
        $counting =0 ;
        foreach ($objectArr as $hint){
            $counting = $counting +1 ;
        }
        return $counting;
    }

    protected function getSequence($name='hibernate_sequence'){
        $result=null;
        if(\DB::connection()->getName() == 'pgsql'){
            $next_id = \DB::select("select nextval('".$name."')");
            $result = $next_id['0']->nextval;
        }
        return $result;
    }

    protected function getDateTime(){
        return Carbon::now();
    }

    protected function terbilang($number){
            $x = abs($number);
            $angka = array("", "satu", "dua", "tiga", "empat", "lima",
                "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $temp = "";
            if ($number <12) {
                $temp = " ". $angka[$number];
            } else if ($number <20) {
                $temp = $this->terbilang($number - 10). " belas";
            } else if ($number <100) {
                $temp = $this->terbilang($number/10)." puluh". $this->terbilang($number % 10);
            } else if ($number <200) {
                $temp = " seratus" . $this->terbilang($number - 100);
            } else if ($number <1000) {
                $temp = $this->terbilang($number/100) . " ratus" . $this->terbilang($number % 100);
            } else if ($number <2000) {
                $temp = " seribu" . $this->terbilang($number - 1000);
            } else if ($number <1000000) {
                $temp = $this->terbilang($number/1000) . " ribu" . $this->terbilang($number % 1000);
            } else if ($number <1000000000) {
                $temp = $this->terbilang($number/1000000) . " juta" . $this->terbilang($number % 1000000);
            } else if ($number <1000000000000) {
                $temp = $this->terbilang($number/1000000000) . " milyar" . $this->terbilang(fmod($number,1000000000));
            } else if ($number <1000000000000000) {
                $temp = $this->terbilang($number/1000000000000) . " trilyun" . $this->terbilang(fmod($number,1000000000000));
            }
            return $temp;
    }

    protected function makeTerbilang($number, $prefix=' rupiah', $suffix=''){
        if($number<0) {
            $hasil = "negatif ". trim($this->terbilang($number));
        } else {
            $hasil = trim($this->terbilang($number));
        }
        return $suffix.$hasil.$prefix;
    }

    public function getMoneyFormatString($number){
        return number_format($number,2,",",".");
    }

    public function getQtyFormatString($number){
        return str_replace(',00', '',number_format($number,2,",","."));
    }

    public function getDateReport($objectCarbonDate){
        $tahun=$objectCarbonDate->year;
        $bulan=$objectCarbonDate->month;
        $tanggal=$objectCarbonDate->day;
        $labelBulan = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
        return $tanggal." ".$labelBulan[$bulan]." ".$tahun;
    }

    public function getDateTimeReport($objectCarbonDate){
        $dateString = $this->getDateReport($objectCarbonDate);
        return $dateString." ".$objectCarbonDate->hour.":".$objectCarbonDate->minute.":".$objectCarbonDate->second;
    }

    public function getBiayaMaterai($total){
        $biayaMaterai = 0;

        if($total > 1000000.99 ){
            $biayaMaterai =6000;
        }elseif($total > 500000.99){
            $biayaMaterai = 3000;
        }
        return $biayaMaterai;
    }

    public function hitungUmur($params){
            $tahun=(int)date('Y', strtotime($params));
            $bulan=(int)date('m', strtotime($params));
            $tanggal=(int)date('d', strtotime($params));
            $selisih_bulan=0;
            $selisih_tahun=0;

            $selisih_tanggal = (int)date('d')-$tanggal;
            if($selisih_tanggal<0){
                $selisih_bulan--;
                $selisih_tanggal+= 30;
            }

            $selisih_bulan += (int)date('m')-$bulan;
            if($selisih_bulan<0){
                $selisih_tahun--;
                $selisih_bulan += 12;
            }


            $selisih_tahun += (int)date('Y') - $tahun;
            $result = "";
            if($selisih_tahun>0){
                $result = abs($selisih_tahun).' Tahun, ';
            }
            if($selisih_bulan>0){
                $result .= abs($selisih_bulan).' Bulan, ';
            }
            if($selisih_tanggal>0){
                $result .= abs($selisih_tanggal).' Hari. ';
            }

            return $result;
    }


    protected function subDateTime($string){
        return substr($string, 0, 19);
    }

    protected function isPasienRawatInap($pasienDaftar){
        if($pasienDaftar->objectruanganlastfk!=null){
            if((int)$pasienDaftar->ruangan->objectdepartemenfk==16){
                return true;
            }
        }
        return false;
    }
    protected function isPasienRawatInap2($pasienDaftar){
        if($pasienDaftar->objectruanganlastfk!=null){
            if((int)$pasienDaftar->objectdepartemenfk==16){
                return true;
            }
        }
        return false;
    }
    protected function KonDecRomawi($angka)
    {
        $hsl = "";
        if ($angka == 1) {
            $hsl='I';
        };
        if ($angka == 2) {
            $hsl='II';
        };
        if ($angka == 3) {
            $hsl='III';
        };
        if ($angka == 4) {
            $hsl='IV';
        };
        if ($angka == 5) {
            $hsl='V';
        };
        if ($angka == 6) {
            $hsl='VI';
        };
        if ($angka == 7) {
            $hsl='VII';
        };
        if ($angka == 8) {
            $hsl='VIII';
        };
        if ($angka == 9) {
            $hsl='IX';
        };
        if ($angka == 10) {
            $hsl='X';
        };
        if ($angka == 11) {
            $hsl='XI';
        };
        if ($angka == 12) {
            $hsl='XII';
        };
        return ($hsl);
    }

    protected function genCode2($objectModel, $atrribute, $length=4, $prefix=''){

        $result = $objectModel->where($atrribute, 'LIKE', '%'.'/RSM/'.'%')->max($atrribute);
        $bln2 = Carbon::now()->format('Y/m');
        $a=substr(trim($result),0,7);

        if($a!=$bln2){
            $subPrefix = '000';
        }else{
            $subPrefix = substr(trim($result),8,11);
        }
        $prefixLen = strlen($prefix);


        return $prefix.(str_pad((int)$subPrefix+1, $length-$prefixLen, "0", STR_PAD_LEFT));
    }
    public function settingDataFixed($NamaField, $KdProfile=null){
        $Query = DB::table('settingdatafixed_m')
            ->where('namafield', '=', $NamaField);
        if($KdProfile){
            $Query->where('kdprofile', '=', $KdProfile);
        }
        $settingDataFixed = $Query->first();
        if(!empty($settingDataFixed)){
            return $settingDataFixed->nilaifield;
        }else{
            return null;
        }
    }
    public static function settingDataFixed2($NamaField, $KdProfile=null){
        $Query = DB::table('settingdatafixed_m')
            ->where('namafield', '=', $NamaField);
        if($KdProfile){
            $Query->where('kdprofile', '=', $KdProfile);
        }
        $settingDataFixed = $Query->first();
        if(!empty($settingDataFixed)){
            return $settingDataFixed->nilaifield;
        }else{
            return null;
        }
    }
    public function getAge($tgllahir,$now){
        $datetime = new \DateTime(date($tgllahir));
        return $datetime->diff(new \DateTime($now))
            ->format('%ythn %mbln %dhr');
    }
    public static function getDateIndo($date2) { // fungsi atau method untuk mengubah tanggal ke format indonesia
        // variabel BulanIndo merupakan variabel array yang menyimpan nama-nama bulan
        $BulanIndo2 = array("Jan", "Feb", "Mar",
            "Apr", "Mei", "Jun",
            "Jul", "Agus", "Sept",
            "Okt", "Nov", "Des");

        $tahun2 = substr($date2, 0, 4); // memisahkan format tahun menggunakan substring
        $bulan2 = substr($date2, 5, 2); // memisahkan format bulan menggunakan substring
        $tgl2   = substr($date2, 8, 2); // memisahkan format tanggal menggunakan substring

        $result = $tgl2 . " " . $BulanIndo2[(int)$bulan2-1] . " ". $tahun2;
        return($result);
    }
    public static function get_menu() {
        $result = file_get_contents(public_path()."/menu/".$_SESSION['role'].".json");
        $result = json_decode($result);
        $menu       = '';
        $menu_child = '';
        if(count( $result) > 0){
            foreach ($result as $parent){
                $li_parent='';
                if(isset($parent->child)){
                    $result_child = $parent->child;
                }else{
                    $result_child =[];
                }

                if(count($result_child)>0){
                    $li_parent='
					<li class="pcoded-hasmenu">
                        <a href="javascript:void(0)">
                          <span class="pcoded-micon"><i class="'.$parent->icon.'"></i></span>
                          <span class="pcoded-mtext" data-i18n="nav.dash.main">'.$parent->name.'</span>
                                    <span class="pcoded-mcaret"></span>
                        </a>';
                    $menu_child='<ul class="pcoded-submenu">';
                    foreach ($result_child as $child){
                        $menu_child = $menu_child.
                            '<li>
                                <a href="'.$child->url.'">
                                  <span class="pcoded-micon"><i class="<i class="'.$child->icon.'"></i></span>
                                  <span class="pcoded-mtext" data-i18n="nav.dash.default">'.$child->name.'</span>
                                  <span class="pcoded-mcaret"></span>
                                </a>
                              </li>';
                    }
                    $menu_child=$menu_child.'</ul>';
                }else{
                    $menu_child="";
                    $li_parent='
                      <li class="pcoded-hasmenu">
                        <a href="'.$parent->url.'">
                          <span class="pcoded-micon"><i class="'.$parent->icon.'"></i></span>
                          <span class="pcoded-mtext" data-i18n="nav.dash.main">'.$parent->name.'</span>
                          <span class="pcoded-mcaret"></span>
                        </a>';
                }
                        $menu=$menu.'
              '.$li_parent.'
              '.$menu_child.'
                </li>';
            }
        }
        return $menu;

    }
    protected function getProdukIdDeposit(){
        $set = DB::table('settingdatafixed_m')->where('namafield', 'idProdukDeposit')->first();
        $this->id= ($set) ? (int)$set->nilaifield: null;
        return $this->id;
    }

    public function validate_input_v2($request,$except=[])
    {
        $this->tempData = [];
        array_push($except,"_token",'button');
        $i = 0;
        foreach ($request->except($except) as $key => $r) {
            if (is_array($r)) {
                $this->tempData[$key] = [];
                $this->tempData[$key] = $this->rekursif_validate($r,$this->tempData[$key],$key);
            }else{
                $this->tempData[$key] = str_replace("--","\-\-",addslashes(trim(htmlentities(strip_tags(str_replace("  "," ",$r))))));
                if ($this->tempData[$key] == '') {
                    $this->tempData[$key] = null;
                }
            }
        }
        return $this->tempData;
    }
}
