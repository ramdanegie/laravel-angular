<?php
namespace App\Transaksi;

class StrukPelayananPenjamin extends Transaksi
{
    protected $table = "strukpelayananpenjamin_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";


    public function struk_pelayanan(){
        return $this->belongsTo(\App\Transaksi\StrukPelayanan::class, 'nostrukfk', 'norec');
    }

    public function getStatusCollectingPiutangAttribute(){
        $data = $this->posting_hutang_piutang()->count();
        if($data>0){
            return "Collecting";
        }
        return "Piutang";
    }

    public function posting_hutang_piutang(){
        return $this->hasMany(\App\Transaksi\PostingHutangPiutang::class, 'nostrukfk');
    }



}