<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Valet;
use App\Master\LoginUser;


use App\Master\Produk;
use App\Master\Ruangan;
use App\Master\Departemen;
use DB;

class Master extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct();
    }

    public function get_Master(Request $request)
    {
        if ($request['table'] == 'produk'){
            $data = Produk::where('id',10008865)->get();
            return $this->respond($data);
        }
        if ($request['table'] == 'ruangan'){
            $data = Ruangan::where('statusenabled','t')->get();

            foreach ($data as $item){
                $result[] =array('id' => $item['id'],
                            'namaruangan' => $item['namaruangan']
                );
            }
            return $this->respond($result);
        }
        if ($request['table'] == 'departemen'){
            $data = Departemen::where('statusenabled','t')->get();

            foreach ($data as $item){
                $result[] =array('id' => $item['id'],
                    'namadepartemen' => $item['namadepartemen']
                );
            }
            return $this->respond($result);
        }
    }

    public function getMapRuanganToProduk(Request $equest)
    {
        $data= \DB::table('mapruangantoproduk_m as mr')
            ->join('produk_m as pr', 'pr.id', '=', 'mr.objectprodukfk')
            ->join('ruangan_m as rg', 'rg.id', '=', 'mr.objectruanganfk')
            ->select('mr.id','mr.statusenabled','mr.objectprodukfk','pr.namaproduk','mr.objectruanganfk','rg.namaruangan')
        ;

        if(isset($request['objectprodukfk']) && $request['objectprodukfk']!="" && $request['objectprodukfk']!="undefined"){
            $data = $data->where('mr.objectprodukfk',$request['objectprodukfk'] );
        }
        if(isset($request['objectruanganfk']) && $request['objectruanganfk']!="" && $request['objectruanganfk']!="undefined"){
            $data = $data->where('mr.objectruanganfk',$request['objectruanganfk'] );
        }

        $data = $data->get();
        return $this->respond($data);
    }
}