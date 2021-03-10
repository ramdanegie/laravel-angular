<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 23/10/2018
 * Time: 12:24
 */

namespace App\Transaksi;


class IndikatorAuditMakan extends Transaksi
{
    protected $table = "auditmakanpasien_t";
    protected $fillable = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = "norec";
}