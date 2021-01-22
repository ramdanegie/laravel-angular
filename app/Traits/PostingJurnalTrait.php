<?php
namespace App\Traits;

use DB;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\PostingJurnalTransaksiD;
use App\Transaksi\RekapPostingJurnalTransaksiD;
use App\Transaksi\StrukPosting;
trait PostingJurnalTrait
{
    protected $jenisJurnalId=1;

    protected function getjenisJurnalId(){
        return $this->jenisJurnalId;
    }

    public function setjenisJurnalId($jenisJurnalId)
    {
        $this->jenisJurnalId = $jenisJurnalId;
        return $this;
    }

    protected function setRekapJurnal($rekapJurnal, $detailjurnal){
        $flag_record = false;
        foreach ($rekapJurnal as $key => $rekap){
            if($rekap['account_id'] == $detailjurnal->objectaccountfk){
                $rekapJurnal[$key]['hargasatuand']  +=  $detailjurnal->hargasatuand;
                $rekapJurnal[$key]['hargasatuank']  +=  $detailjurnal->hargasatuank;
            }
        }

        if(!$flag_record){
            $rekapJurnal[] = array(
                "account_id" => $detailjurnal->objectaccountfk,
                "hargasatuand" => $detailjurnal->hargasatuand,
                "hargasatuank" => $detailjurnal->hargasatuank
            );
        }
        return $rekapJurnal;
    }

//    siapin juga untuk bulk untuk import jurnal sih..
    protected function postingJournal($jurnal){
//        DB::beginTransaction();
        $rekapJurnal = array();
        foreach ($jurnal['detailJurnal'] as $detail){
            //POSTINGJURNAL
            $postingJurnalTransaksi = new PostingJurnalTransaksi();
            $postingJurnalTransaksi->norec = $postingJurnalTransaksi->generateNewId();
            $nojurnal = $this->getSequence('postingjurnaltransaksi_t_nojurnal_seq');
            $postingJurnalTransaksi->noposting = $jurnal['noposting'];
            $postingJurnalTransaksi->nojurnal = $nojurnal;
            $postingJurnalTransaksi->kdprofile = $this->getKdProfile();
            $postingJurnalTransaksi->objectjenisjurnalfk = $this->getjenisJurnalId();
            $postingJurnalTransaksi->nobuktitransaksi = $detail['nobuktitransaksi'];
            $postingJurnalTransaksi->tglbuktitransaksi = $detail['tglbuktitransaksi'];
            $postingJurnalTransaksi->keteranganlainnya =  isset($detail['keteranganlainnya']) ? $detail['keteranganlainnya'] : '';

            try{
                $postingJurnalTransaksi->save();
            }
            catch(\Exception $e){
                $this->transStatus = false;
                throw new \Exception($e->getMessage());
                $this->transMessage = "Simpan Jurnal Gagal {simpan jurnal}";
                break;
            }

            //POSTINGJURNALDETAIL
            foreach ($detail['saldoJurnal'] as $saldo){
                $postingJurnalTransaksiD = new PostingJurnalTransaksiD();
                $postingJurnalTransaksiD->norec = $postingJurnalTransaksiD->generateNewId();
                $postingJurnalTransaksiD->noposting = $jurnal['noposting'];
                $postingJurnalTransaksiD->nojurnal = $postingJurnalTransaksi->nojurnal;
                $postingJurnalTransaksiD->kdprofile = $this->getKdProfile();
                $postingJurnalTransaksiD->norecrelated = $postingJurnalTransaksi->norec;
                $postingJurnalTransaksiD->Balance= $saldo['balance'];
                $postingJurnalTransaksiD->Saldo= $saldo['saldo'];
                $postingJurnalTransaksiD->objectaccountfk = $saldo['account_id'];
                try{
                    $postingJurnalTransaksiD->save();
                }
                catch(\Exception $e){
                    $this->transStatus = false;
                    $this->transMessage = "Simpan Jurnal Gagal {simpan detail jurnal}";
                    break;
                }

                $rekapJurnal = $this->setRekapJurnal($rekapJurnal, $postingJurnalTransaksiD);

            }

            //STRUKPOSTING
            $strukPosting = new StrukPosting();
            $strukPosting->norec = $strukPosting->generateNewId();
            $strukPosting->kdprofile = $this->getKdProfile();
            $strukPosting->noposting = $jurnal['noposting'];
            if(isset($detail['ruanganid'])){
                $strukPosting->objectruanganfk = $detail['ruanganid'];
            }
            if(isset($detail['kelompoktransaksiid'])){
                $strukPosting->objectkelompoktransaksifk = $detail['kelompoktransaksiid'];
            }

            try{
                $strukPosting->save();
            }
            catch(\Exception $e){
                throw new \Exception($e->getMessage());
                $this->transStatus = false;
                $this->transMessage = "Simpan Jurnal Gagal {simpan strukposting}";
                break;
            }
        }

        if($this->transStatus){
            foreach ($rekapJurnal as $rekap){
                $rekapPostingJurnalTransaksiD = new RekapPostingJurnalTransaksiD();
                $rekapPostingJurnalTransaksiD->norec = $rekapPostingJurnalTransaksiD->generateNewId();
                $rekapPostingJurnalTransaksiD->noposting = $jurnal['noposting'];
                $rekapPostingJurnalTransaksiD->kdprofile = $this->getKdProfile();
                $rekapPostingJurnalTransaksiD->hargasatuand= $rekap['hargasatuand'];
                $rekapPostingJurnalTransaksiD->hargasatuank= $rekap['hargasatuank'];
                $rekapPostingJurnalTransaksiD->objectaccountfk = $rekap['account_id'];
                try{
                    $rekapPostingJurnalTransaksiD->save();
                }
                catch(\Exception $e){
//                    throw new \Exception($e->getMessage());
                    $this->transStatus = false;
                    $this->transMessage = "Simpan Jurnal Gagal {simpan rekap}";
                    break;
                }
            }
        }

        if($this->transStatus){
            $this->transMessage = "Simpan Jurnal Berhasil";
            //DB::commit();
        }else{
            //DB::rollBack();
        }
        return $this->transStatus;
    }

}