<?php

namespace App\Transaksi;

use App\BaseModel;

class HistoryAset extends Transaksi
{
    protected $table = "historyaset_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}