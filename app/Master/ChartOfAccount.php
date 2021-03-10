<?php

namespace App\Master;

// use Illuminate\Database\Eloquent\Model;
use App\Transaksi\PeriodeAccount;
use App\Transaksi\PeriodeAccountSaldo;
use App\Transaksi\PostingJurnalTransaksi;
use App\Transaksi\PostingJurnalTransaksiD;

class ChartOfAccount extends MasterModel
{
    protected $table ="chartofaccount_m";
    protected $fillable = [];
    public $timestamps = false;

    public function __construct(){$this->setTransformerPath('App\Transformers\Master\ChartOfAccountTransformer');}

    public function gethasChildAttribute(){
        if($this->account_child!=null && count($this->account_child)>0){
            return true;
        }
        return false;
    }

    public function getreportDisplayAttribute(){
        return $this->noaccount .' - '. $this->namaaccount;
    }

    public function getcanCanAttribute(){
        if($this->hasChild){
            return false;
        }else{
            $account =  PostingJurnalTransaksiD::where('objectaccountfk', '=', $this->id)->first();
            if($account){
                return false;
            }
        }
        return true;
    }

    protected function isEditable($periodeID=null){
        if($this->hasChild){
            return false;
        }else{
            if($periodeID!=null){
                $periodeAccount = PeriodeAccount::where('norec', $periodeID)->first();
                if($periodeAccount){
                    $postingJurnalTransaksiD = PostingJurnalTransaksiD::with('posting_jurnal_transaksi')->where('objectaccountfk', '=', $this->id)
                        ->where('norecrelated', '=', $periodeID)->first();
                    //yang diatas seharusnya dia mencari berdarkan tgl periode account ada gak di journal id accou berdasarkan tanggal periode, kalo ada gak bisa edit
                    if($postingJurnalTransaksiD){
                        return false;
                    }
                }
            }
            return true;
        }
    }

    public function setSaldoPeriodeAttribute($periodeId){
        $this->attributes['saldoAwalD'] = 0;
        $this->attributes['saldoAwalK'] = 0;
        $this->attributes['saldoAkhirD'] = 0;
        $this->attributes['saldoAkhirK'] = 0;
        $this->attributes['isEditable'] = $this->isEditable($periodeId);
        if($periodeId==null){
            $periodeAccount = PeriodeAccount::orderBy('kdperiodeaccount', 'desc')->first();
            if($periodeAccount){
                $periodeAccountSaldo = PeriodeAccountSaldo::where('objectperiodeaccountfk', '=', $periodeAccount->kdperiodeaccount)
                    ->where('objectaccountfk', '=', $this->kdaccount)->first();
                if($periodeAccountSaldo){
                    $this->attributes['saldoAwalD'] = $periodeAccountSaldo->saldoakhirdperiode;
                    $this->attributes['saldoAwalK'] = $periodeAccountSaldo->saldoakhirkperiode;
                }
            }
        }else{
            $periodeAccountSaldo = PeriodeAccountSaldo::where('objectperiodeaccountfk', '=', $periodeId)
                ->where('objectaccountfk', '=', $this->id)->first();
            if($periodeAccountSaldo){
                $this->attributes['saldoAwalD'] = $periodeAccountSaldo->saldoawaldperiode;
                $this->attributes['saldoAwalK'] = $periodeAccountSaldo->saldoawalkperiode;
                $this->attributes['saldoAkhirD'] = $periodeAccountSaldo->saldoakhirdperiode;
                $this->attributes['saldoAkhirK'] = $periodeAccountSaldo->saldoakhirkperiode;
            }
        }
    }

    public function canEdit(){
        return $this->canCan;
    }

    public function canDelete(){
        return $this->canCan;
    }


    public function allChildrenAccounts()
    {
        return $this->account_child()->with('allChildrenAccounts');
    }

    public function scopeTopAccountByJenis($query, $jenisId){
        return $query->where('objectjenisaccountfk', $jenisId)->where('objectstrukturaccountfk', '1');
    }

            //hasMany begin
//    public function produk(){
//        return $this->hasMany('App\Master\Produk', 'objectaccountfk');
//    }

    //hasMany end

    //belongsTo begin

    // public function account_head(){
    //     return $this->belongsTo('App\Master\AccountHead', 'objectaccountheadfk');
    // }//tanya lagi ini maksudnya gimana ?

    public function jenis_account(){
        return $this->belongsTo('App\Master\JenisAccount', 'objectjenisaccountfk');
    }

    public function kategory_account(){
        return $this->belongsTo('App\Master\KategoryAccount', 'objectkategoryaccountfk');
    }

    public function status_account(){
        return $this->belongsTo('App\Master\StatusAccount', 'objectstatusaccountfk');
    }

    public function struktur_account(){
        return $this->belongsTo('App\Master\StrukturAccount', 'objectstrukturaccountfk');
    }
    
    //self
    public function account_child()
    {
        return $this->hasMany('App\Master\ChartOfAccount', 'objectaccountheadfk');
    }

    public function account_head()
    {
        return $this->belongsTo('App\Master\ChartOfAccount', 'objectaccountheadfk');
    }

    public function account_effect_add()
    {
        return $this->belongsTo('App\Master\ChartOfAccount', 'kdaccounteffectadd');
    }

    public function account_effect_min()
    {
        return $this->belongsTo('App\Master\ChartOfAccount', 'kdaccounteffectadd');
    }

}
