<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 08/07/2019
 * Time: 14:13
 */

namespace App\Transaksi;

class StrukPelayananPenjaminDetail extends Transaksi
{
	protected $table = "strukpelayananpenjamindetail_t";
	protected $fillable = [];
	public $timestamps = false;
	public $incrementing = false;
	protected $primaryKey = "norec";

}