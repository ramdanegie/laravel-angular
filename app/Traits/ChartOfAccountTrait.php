<?php
namespace App\Traits;

use App\Master\ChartOfAccount;
use App\Transaksi\PeriodeAccount;
use App\Transaksi\PeriodeAccountSaldo;
Trait ChartOfAccountTrait
{
    protected function getSaldoNormalPembalik($balance){
        if($balance=='D'){
            return 'K';
        }else{
            return 'D';
        }
    }

    protected function getAccountByJenis($jenisAccountId){
        return ChartOfAccount::orderBy('noaccount', 'asc')->where('objectjenisaccountfk','=', $jenisAccountId)->get();
    }

    protected function getAccountWithSaldoPeriode($jenisAccountId, $periodeAccountId=null){
        $result=$this->getAccountByJenis($jenisAccountId);
        foreach ($result as $key => $re){
            $re->SaldoPeriode = $periodeAccountId;
            $result[$key]= $re;
        }
        return $result;
    }

    /*------------------------------------start import from excel----------------------------*/
    /*
     * fungsi ini dibuat karna kemarin pak bobby pengennya no account yang 
     * contohnya 110101920 di sistemnya diidentifikasi menjadi 1.1.01.0.
     */
    protected function genNoAccount($noAccount){
        $realNoAccount=$noAccount;
        $countNoAccount = strlen($noAccount);
        if($countNoAccount>1){
            $i=1;
            $containerCode = array();
            while($i<= $countNoAccount){
                if($i<=2){
                    $containerCode[] = substr($noAccount, ($i-1), 1);
                    $i++;
                }else{
                    $containerCode[] = substr($noAccount, ($i-1), 2);
                    $i = $i + 2;
                }


            }
            $realNoAccount = implode(".",$containerCode);
        }
        return $realNoAccount;
    }

    protected function getStrukturAccount($item){
        $result = array('hasParent' => true);
        //getlevel account
        $countNoAccount = strlen($item['noAccount']);
        if($countNoAccount<=2){
            $result['level'] = $countNoAccount;
            if ($countNoAccount==1){
                $result['hasParent'] = false;
            }

        }else{
            $result['level'] = 2 + ceil(($countNoAccount-2)/2);
        }
        $result['noaccount'] = $this->genNoAccount($item['noAccount']);
        return $result;
    }
    /*------------------------------------end import from excel----------------------------*/


    
    protected function abc(){} 
}