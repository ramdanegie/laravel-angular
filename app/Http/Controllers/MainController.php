<?php

namespace App\Http\Controllers;

use App\Datatrans\EECG;
use App\Datatrans\PasienDaftar;
use App\Datatrans\Pegawai;
use App\Http\Controllers\SysAdmin\ModulAplikasiController;
use App\User;
use App\Traits\Valet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

//use Storage;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Webpatser\Uuid\Uuid;

// use Illuminate\Contracts\Encryption\DecryptException;
// use Picqer;

date_default_timezone_set('Asia/Jakarta');

class MainController extends ApiController
{
    use Valet;

    public function __construct()
    {
        parent::__construct($skip_authentication = true);
    }

    public function show_page(Request $r)
    {
        $request = array('role' => $r->role, 'pages' => $r->pages, "id" => $r->id);
        $request = $this->validate_input($request);
        $compact = [];
        $kdProfile = $_SESSION['kdProfile'];
        if ($request['role'] == 'admin') {
            switch ($request["pages"]) {
                case 'dashboard':
//                    array_push($compact,"listdiagnosa","map","kddiagnosa","umur","tglawal","tglakhir");
                    break;
                case 'jadwal-dokter':
                    $dokterId = '';
                    $ruanganId = '';

                    if (!isset($r->ruanganId) && !isset($r->dokterId)) {
                        $dokterId = $_SESSION['pegawai']->id;
                        return redirect()->route("show_page",
                            ["role" => $_SESSION['role'],
                                "pages" => $r->pages,
                                "dokterId" => $dokterId,
                                "ruanganId" => $ruanganId]);
                    } else {
                        $dokterId = $r->dokterId;
                        $ruanganId = $r->ruanganId;
                    }
                    if ($dokterId == 1) {
                        $dokterId = '';
                    }
                    $kdJeniPegawaiDokter = (int)$this->settingDataFixed('KdJenisPegawaiDokter', $kdProfile);
                    $dokter = \DB::table('pegawai_m')
                        ->select('id', 'namalengkap')
                        ->where('statusenabled', true)
                        ->where('kdprofile', $kdProfile)
                        ->where('objectjenispegawaifk', $kdJeniPegawaiDokter)
                        ->orderBy('namalengkap')
                        ->get();


                    $deptJalan = explode(',', $this->settingDataFixed('kdDepartemenRawatJalanFix', $kdProfile));
                    $kdDepartemenRawatJalan = [];
                    foreach ($deptJalan as $item) {
                        $kdDepartemenRawatJalan [] = (int)$item;
                    }
                    $deptInap = explode(',', $this->settingDataFixed('kdDepartemenRawatInapFix', $kdProfile));
                    $kdDeptInap = [];
                    foreach ($kdDeptInap as $item) {
                        $kdDeptInap [] = (int)$item;
                    }
                    $deptPatok = array_merge($kdDeptInap, $kdDepartemenRawatJalan);

                    $ruangan = \DB::table('ruangan_m as ru')
                        ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk')
                        ->where('ru.statusenabled', true)
                        ->wherein('ru.objectdepartemenfk', $kdDepartemenRawatJalan)
                        ->where('ru.kdprofile', (int)$kdProfile)
                        ->orderBy('ru.namaruangan')
                        ->get();

                    if ($dokterId != '') {
                        $dokterId = ' and pg.id=' . $dokterId;
                    }
                    if ($ruanganId != '') {
                        $ruanganId = ' and ru.id=' . $ruanganId;
                    }

                    $data = collect(DB::select("
                    SELECT
                        jd. ID AS idjadwalpegawai,
                        jd.objectruanganfk,
                        ru.namaruangan,
                        jd.objectpegawaifk,
                        pg.namalengkap,
                        jd.jammulai,
                        jd.jamakhir,
                        jd.keterangan,
                        jd.hari, pg.objectjeniskelaminfk as jkid,
                        case when pg.nip_pns is null then '-' else pg.nip_pns end as nip
                    FROM
                        jadwaldokter_m AS jd
                    INNER JOIN ruangan_m AS ru ON ru.id = jd.objectruanganfk
                    INNER JOIN pegawai_m AS pg ON pg.id = jd.objectpegawaifk
                    WHERE
                        jd.kdprofile = $kdProfile
                    AND jd.statusenabled= true
                    $dokterId
                    $ruanganId
                    ORDER BY
                        pg.namalengkap ASC"));
//                    dd($data->groupBy('namalengkap'));
//
                    array_push($compact, 'data', 'r', 'ruangan', 'dokter');
                    break;
                case 'daftar-pasien-dokter':
                    $kdJeniPegawaiDokter = (int)$this->settingDataFixed('KdJenisPegawaiDokter', $kdProfile);
                    $dokter = \DB::table('pegawai_m')
                        ->select('id', 'namalengkap')
                        ->where('statusenabled', true)
                        ->where('kdprofile', $kdProfile)
                        ->where('objectjenispegawaifk', $kdJeniPegawaiDokter)
                        ->orderBy('namalengkap')
                        ->get();

                    $deptJalan = explode(',', $this->settingDataFixed('kdDepartemenRawatJalanFix', $kdProfile));
                    $kdDepartemenRawatJalan = [];
                    foreach ($deptJalan as $item) {
                        $kdDepartemenRawatJalan [] = (int)$item;
                    }
                    $deptInap = explode(',', $this->settingDataFixed('kdDepartemenRanapFix', $kdProfile));
                    $kdDeptInap = [];
                    foreach ($deptInap as $item) {
                        $kdDeptInap [] = (int)$item;
                    }
                    $deptPatok = $kdDepartemenRawatJalan;

                    $ruangan = \DB::table('ruangan_m as ru')
                        ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk')
                        ->where('ru.statusenabled', true)
                        ->wherein('ru.objectdepartemenfk', $deptPatok)
                        ->where('ru.kdprofile', (int)$kdProfile)
                        ->orderBy('ru.namaruangan')
                        ->get();

                    $validatedata = $this->validate_input_v2($r);
                    $tempWhere = [];
                    $now = date('Y-m-d');
                    if (isset($validatedata["src_tglAwal"])) {
                        $tempWhere[0] = DB::raw("pd.tglregistrasi >= '$validatedata[src_tglAwal]'");
                    } else {
                        $r['src_tglAwal'] = $now;
                        $tempWhere[0] = DB::raw("pd.tglregistrasi >= '$now 00:00'");
                    }
                    if (isset($validatedata["scr_tglAkhir"])) {
                        $tempWhere[1] = DB::raw("pd.tglregistrasi <= '$validatedata[scr_tglAkhir]'");
                    } else {
                        $r['src_tglAkhir'] = $now;
                        $tempWhere[1] = DB::raw("pd.tglregistrasi <= '$now 23:59'");
                    }
                    if (isset($validatedata["src_nama"])) {
                        $tempWhere[2] = DB::raw("ps.namapasien iLIKE '%$validatedata[src_nama]%'");
                    }
                    if (isset($validatedata["src_nocm"])) {
                        $tempWhere[3] = DB::raw("(ps.nocm iLIKE '%$validatedata[src_nocm]%' or
                        ps.namapasien iLIKE '%$validatedata[src_nocm]%')");
                    }
                    if (isset($validatedata["src_idRuangan"])) {
                        $tempWhere[4] = DB::raw("ru.id = $validatedata[src_idRuangan]");
                    }
//                    dd($validatedata["src_idDokter"]);
                    if (isset($validatedata["src_idDokter"]) && $validatedata["src_idDokter"] != '1') {
                        $tempWhere[5] = DB::raw("pg.id = $validatedata[src_idDokter]");
                    } else if (isset($validatedata["src_idDokter"]) && $validatedata["src_idDokter"] == '1') {
                        $r['src_idDokter'] = '';
                    } else {
                        $r['src_idDokter'] = $_SESSION['pegawai']->id;
                        $tempWhere[5] = DB::raw("pg.id = $r[src_idDokter]");
                    }

                    $opr = "and";
                    $where = "pd.kdprofile =" . $kdProfile;
                    if (count($tempWhere) > 0) {
                        $where .= " AND " . implode(" " . $opr . " ", $tempWhere);
                    }
//                    dd($where);

                    $data = \DB::table('antrianpasiendiperiksa_t as apd')
                        ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                        ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                        ->leftjoin('alamat_m as alm', 'ps.id', '=', 'alm.nocmfk')
                        ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
                        ->join('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                        ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                        ->leftJoin('pegawai_m as pg', 'pg.id', '=', 'apd.objectpegawaifk')
                        ->leftJoin('pegawai_m as pg2', 'pg2.id', '=', 'apd.residencefk')
                        ->Join('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
                        ->leftjoin('rekanan_m as rek', 'rek.id', '=', 'pd.objectrekananfk')
                        ->leftjoin('antrianpasienregistrasi_t as apr', function ($join) {
                            $join->on('apr.noreservasi', '=', 'pd.statusschedule');
                            $join->on('apr.nocmfk', '=', 'pd.nocmfk');
                        })
                        ->leftjoin('pemakaianasuransi_t as pa', 'pa.noregistrasifk', '=', 'pd.norec')
                        ->leftjoin('asuransipasien_m as asu', 'pa.objectasuransipasienfk', '=', 'asu.id')
                        ->leftjoin('kelas_m as klstg', 'klstg.id', '=', 'asu.objectkelasdijaminfk')
                        ->select('pd.tglregistrasi', 'ps.nocm', 'pd.noregistrasi', 'ps.namapasien', 'ps.tgllahir', 'jk.jeniskelamin', 'apd.objectruanganfk', 'ru.namaruangan', 'kls.id as idkelas', 'kls.namakelas',
                            'kp.kelompokpasien', 'rek.namarekanan', 'apd.objectpegawaifk', 'pg.namalengkap as namadokter', 'pd.norec as norec_pd', 'apd.norec as norec_apd', 'apd.objectasalrujukanfk',
                            'apd.tgldipanggildokter', 'apd.statuspasien as statuspanggil', 'pd.statuspasien', 'apd.tgldipanggildokter', 'apd.tgldipanggilsuster', 'apr.noreservasi', 'apd.noantrian',
                            'apr.tanggalreservasi', 'alm.alamatlengkap', 'klstg.namakelas as kelasdijamin', 'apd.tglselesaiperiksa',
                            'apd.norec as norec_apd', 'ps.objectjeniskelaminfk as jkid',
                            'ru.ipaddress', 'ps.iskompleks', 'apd.residencefk', 'pg2.namalengkap as residence'
                            , DB::raw("case when apd.ispelayananpasien is null then 'false' else 'true' end as statuslayanan,
                                   EXTRACT(YEAR FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Thn '
                        || EXTRACT(MONTH FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Bln '
                        || EXTRACT(DAY FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Hr' AS umur"))
                        ->where('pd.statusenabled', true)
                        ->where('ps.statusenabled', true)
                        ->whereRaw($where)
                        ->whereIn('ru.objectdepartemenfk', [18, 24, 28, 26, 30, 34])
//                        ->whereIn('ru.objectdepartemenfk',$deptPatok)
                        ->orderBy('apd.noantrian');
                    $count = $data->get();
                    $data = $data->paginate(10);
//                    dd($count);
                    $norecaPd = '';
                    foreach ($data as $ob) {
                        $norecaPd = $norecaPd . ",'" . $ob->norec_apd . "'";
                        $ob->kddiagnosa = [];
                    }
                    $norecaPd = substr($norecaPd, 1, strlen($norecaPd) - 1);
                    $diagnosa = [];
                    if ($norecaPd != '') {
                        $diagnosa = DB::select(DB::raw("
                           select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
                           from detaildiagnosapasien_t as ddp
                           left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                           left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                           where ddp.noregistrasifk in ($norecaPd) "));
                        $i = 0;
                        foreach ($data as $h) {
                            $data[$i]->kddiagnosa = [];
                            foreach ($diagnosa as $d) {
                                if ($data[$i]->norec_apd == $d->norec_apd) {
                                    $data[$i]->kddiagnosa[] = $d->kddiagnosa;
                                }
                            }
                            $i++;
                        }
                    }
                    /*
                     * hitung terlayani
                     */
                    $norecaPd2 = '';
                    foreach ($count as $ob) {
                        $norecaPd2 = $norecaPd2 . ",'" . $ob->norec_apd . "'";
                        $ob->kddiagnosa = [];
                    }
                    $norecaPd2 = substr($norecaPd2, 1, strlen($norecaPd2) - 1);
                    $terlayani = 0;
                    if ($norecaPd2 != '') {
                        $diagnosa2 = DB::select(DB::raw("
                           select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
                           from detaildiagnosapasien_t as ddp
                           left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                           left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                           where ddp.noregistrasifk in ($norecaPd2) "));
                        $i = 0;
                        $terlayani = 0;
                        foreach ($count as $h) {
                            $count[$i]->kddiagnosa = [];
                            foreach ($diagnosa2 as $d) {
                                if ($count[$i]->norec_apd == $d->norec_apd) {
                                    $count[$i]->kddiagnosa[] = $d->kddiagnosa;
                                    if (count($count[$i]->kddiagnosa) == 1) {
                                        $terlayani = $terlayani + 1;
                                    }
                                }
                            }
                            $i++;
                        }
                    }
//                    dd($data);
                    $total['total'] = $count->count();
                    $total['terlayani'] = $terlayani;
                    $total['belumterlayani'] = $count->count() - $terlayani;
                    /*
                     * end hitung
                     */
//                    dd($total);
                    array_push($compact, 'data', 'r', 'ruangan', 'dokter', 'total');
                    break;
                case 'detail-billing':
                    $pasien = \DB::table('pasiendaftar_t as pd')
                        ->leftjoin('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                        ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
                        ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                        ->leftjoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                        ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
                        ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
                        ->leftjoin('alamat_m as alm', 'alm.id', '=', 'pd.nocmfk')
                        ->leftjoin('agama_m as agm', 'agm.id', '=', 'ps.objectagamafk')
                        ->select('pd.norec as norec_pd', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'ps.namapasien',
                            'ps.tgllahir', 'ps.namakeluarga', 'ru.namaruangan', 'kls.namakelas', 'kps.kelompokpasien', 'rk.namarekanan', 'alm.alamatlengkap',
                            'jk.jeniskelamin', 'agm.agama', 'ps.nohp', 'pd.statuspasien', 'pd.tglpulang')
                        ->where('pd.norec', $r['norec_pd'])
                        ->where('pd.kdprofile', $_SESSION['kdProfile'])
                        ->first();
                    $pelayanan = \DB::table('pasiendaftar_t as pd')
                        ->join('antrianpasiendiperiksa_t as apd', 'apd.noregistrasifk', '=', 'pd.norec')
                        ->leftjoin('pelayananpasien_t as pp', 'pp.noregistrasifk', '=', 'apd.norec')
                        ->leftjoin('produk_m as pr', 'pr.id', '=', 'pp.produkfk')
                        ->join('kelas_m as kl', 'kl.id', '=', 'apd.objectkelasfk')
                        ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                        ->leftjoin('strukpelayanan_t as sp', 'sp.norec', '=', 'pp.strukfk')
                        ->leftjoin('strukbuktipenerimaan_t as sbm', 'sp.nosbmlastfk', '=', 'sbm.norec')
                        ->leftjoin('strukresep_t as sre', 'sre.norec', '=', 'pp.strukresepfk')
                        ->select('pp.norec', 'pp.tglpelayanan', 'pp.rke', 'pr.id as prid', 'pr.namaproduk', 'pp.jumlah', 'kl.id as klid', 'kl.namakelas',
                            'ru.id as ruid', 'ru.namaruangan', 'pp.produkfk', 'pp.hargajual', 'pp.hargadiscount', 'sp.nostruk', 'sp.tglstruk', 'apd.norec as norec_apd',
                            'sbm.nosbm', 'sp.norec as norec_sp', 'pp.jasa', 'pd.nocmfk',
                            'pd.nostruklastfk', 'pd.noregistrasi',
                            'pd.tglregistrasi', 'pd.norec as norec_pd', 'pd.tglpulang',
                            'pd.objectrekananfk as rekananid',
                            'pp.jasa', 'sp.totalharusdibayar', 'sp.totalprekanan',
                            'sp.totalbiayatambahan', 'pp.aturanpakai', 'pp.iscito', 'pd.statuspasien', 'pp.isparamedis', 'pp.strukresepfk'
                        )
                        ->where('pd.kdprofile', $_SESSION['kdProfile'])
                        ->where('pd.norec', $r['norec_pd'])
                        ->get();

                    $noregistrasi = PasienDaftar::where('norec', $r['norec_pd'])->first();
                    $produkDeposit = $this->settingDataFixed('idProdukDeposit', $kdProfile);
                    if (count($pelayanan) > 0) {
                        $details = array();
                        foreach ($pelayanan as $value) {
                            if ($value->prid != $produkDeposit) {
                                $jasa = 0;
                                if (isset($value->jasa) && $value->jasa != "" && $value->jasa != null) {
                                    $jasa = (float)$value->jasa;
                                }
                                $jaspel = DB::table('pelayananpasiendetail_t')
                                    ->where('pelayananpasien', $value->norec)
                                    ->where('komponenhargafk', 94)
                                    ->first();
                                if (!empty($jaspel)) {
                                    $jasaD = 0;
                                    if (isset($jaspel->jasa) && $jaspel->jasa != "" && $jaspel->jasa != null) {
                                        $jasaD = (float)$jaspel->jasa;
                                    }
                                    $hargaD = (float)$jaspel->hargajual;
                                    $diskonD = (float)$jaspel->hargadiscount;
                                    $jasapelayanan = (($hargaD - $diskonD) * $jaspel->jumlah) + $jasaD;
//                                    dd($jasapelayanan);
                                } else {
                                    $jasapelayanan = 0;
                                }
                                $harga = (float)$value->hargajual;
                                $diskon = (float)$value->hargadiscount;
                                $detail = array(
                                    'norec' => $value->norec,
                                    'tglPelayanan' => $value->tglpelayanan,
                                    'namaPelayanan' => $value->namaproduk,
                                    'jumlah' => (float)$value->jumlah,
                                    'kelasTindakan' => @$value->namakelas,
                                    'ruanganTindakan' => @$value->namaruangan,
                                    'harga' => $harga,
                                    'diskon' => $diskon,
                                    'total' => (($harga - $diskon) * $value->jumlah) + $jasa,
                                    'strukfk' => $value->nostruk,
                                    'sbmfk' => $value->nosbm,
                                    'pgid' => '',
                                    'ruid' => $value->ruid,
                                    'prid' => $value->prid,
                                    'klid' => $value->klid,
                                    'norec_apd' => $value->norec_apd,
                                    'norec_pd' => $value->norec_pd,
                                    'norec_sp' => $value->norec_sp,
                                    'jasa' => $jasa,
                                    'aturanpakai' => $value->aturanpakai,
                                    'iscito' => $value->iscito,
                                    'isparamedis' => $value->isparamedis,
                                    'strukresepfk' => $value->strukresepfk,
                                    'jasapelayanan' => $jasapelayanan
                                );

                                $details[] = $detail;
                            }
                        }
                    }

                    $x = 0;
                    foreach ($details as $det) {
                        if ($details[$x]['strukresepfk'] != null) {
                            $details[$x]['ruanganTindakan'] = 'Pemakaian Obat & Alkes ' . $details[$x]['ruanganTindakan'];
                        }
                        $x++;
                    }
//                  dd($details);

                    $sama = false;
                    $groupingArr = [];
                    for ($i = 0; $i < count($details); $i++) {
                        $sama = false;
                        for ($x = 0; $x < count($groupingArr); $x++) {
                            if ($details[$i]['ruanganTindakan'] == $groupingArr[$x]['ruanganTindakan']) {
                                $sama = true;
                                $groupingArr[$x]['total'] = (float)$details[$i]['total'] + (float)$groupingArr[$x]['total'];
                                $groupingArr[$x]['jasapelayanan'] = (float)$details[$i]['jasapelayanan'] + (float)$groupingArr[$x]['jasapelayanan'];
                            }
                        }
                        if ($sama == false) {
                            $groupingArr[] = array(
                                'ruanganTindakan' => $details[$i]['ruanganTindakan'],
                                'total' => $details[$i]['total'],
                                'jasapelayanan' => $details[$i]['jasapelayanan'],
                            );
                        }
                    }
                    $res = array(
                        'pasien' => $pasien,
                        'details' => $groupingArr,
                        'deposit' => $this->getDepositPasien($noregistrasi),
                        'totalklaim' => $this->getTotalKlaim($noregistrasi, $kdProfile),
                        'bayar' => $this->getTotolBayar($noregistrasi, $kdProfile),
                    );
//                    dd($res);
                    array_push($compact, 'res', 'r');
                    break;
                case 'detail-registrasi':
                    $res = [];
                    $pasien = \DB::table('pasiendaftar_t as pd')
                        ->leftjoin('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                        ->leftjoin('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
                        ->leftjoin('kelas_m as kls', 'kls.id', '=', 'pd.objectkelasfk')
                        ->leftjoin('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
                        ->leftjoin('rekanan_m as rk', 'rk.id', '=', 'pd.objectrekananfk')
                        ->leftjoin('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
                        ->leftjoin('alamat_m as alm', 'alm.id', '=', 'pd.nocmfk')
                        ->leftjoin('agama_m as agm', 'agm.id', '=', 'ps.objectagamafk')
                        ->select('pd.norec as norec_pd', 'pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'ps.namapasien',
                            'ps.tgllahir', 'ps.namakeluarga', 'ru.namaruangan', 'kls.namakelas', 'kps.kelompokpasien', 'rk.namarekanan', 'alm.alamatlengkap',
                            'jk.jeniskelamin', 'agm.agama', 'ps.nohp', 'pd.statuspasien', 'pd.tglpulang', 'ps.id as nocmfk')
                        ->where('pd.norec', $r['norec_pd'])
                        ->where('pd.kdprofile', $_SESSION['kdProfile'])
                        ->first();

                    $data = \DB::table('pasien_m as ps')
                        ->join('pasiendaftar_t as pd', 'pd.nocmfk', '=', 'ps.id')
                        ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
                        ->join('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
                        ->leftjoin('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
                        ->leftJoin('batalregistrasi_t as br', 'br.pasiendaftarfk', '=', 'pd.norec')
                        ->select(DB::raw("pd.norec,pd.tglregistrasi,ps.nocm,pd.noregistrasi,ps.namapasien,pd.objectruanganlastfk,kp.kelompokpasien,ru.namaruangan,
			                  pd.objectpegawaifk,pg.namalengkap as namadokter,pd.tglpulang,ru.objectdepartemenfk,
			                  CASE when ru.objectdepartemenfk in (16,25,26) then 1 else 0 end as statusinap"))
                        ->whereNull('br.pasiendaftarfk')
                        ->where('ps.kdprofile', $_SESSION['kdProfile'])
                        ->where('ps.id', $pasien->nocmfk);
                    $data = $data->where('ps.statusenabled', true);
                    $data = $data->orderBy('pd.tglregistrasi');
                    $data = $data->get();

                    $norecaPd = '';
                    foreach ($data as $ob) {
                        $norecaPd = $norecaPd . ",'" . $ob->norec . "'";
                    }
                    $norecaPd = substr($norecaPd, 1, strlen($norecaPd) - 1);

                    $diagnosa = DB::select(DB::raw("
                            SELECT
                            apd.noregistrasifk,
                            ddp.objectjenisdiagnosafk,
                            dg.kddiagnosa AS diagnosa,
                            CASE
                        WHEN dp.iskasusbaru = TRUE
                        AND dp.iskasuslama = FALSE THEN
                            'BARU'
                        WHEN dp.iskasuslama = TRUE
                        AND dp.iskasusbaru = FALSE THEN
                            'LAMA'
                        ELSE
                            ''
                        END kasus
                        FROM
                            antrianpasiendiperiksa_t AS apd
                        INNER JOIN detaildiagnosapasien_t AS ddp ON ddp.noregistrasifk = apd.norec
                        INNER JOIN diagnosapasien_t AS dp ON dp.norec = ddp.objectdiagnosapasienfk
                        INNER JOIN diagnosa_m AS dg ON ddp.objectdiagnosafk = dg.id
                        WHERE
                            apd.kdprofile = $_SESSION[kdProfile]
                        AND apd.noregistrasifk IN ($norecaPd)
                            AND apd.statusenabled = true"));

                    $i = 0;
                    $dataDiagnosa = '';
                    foreach ($data as $items) {
                        $data[$i]->diagnosa = '';
                        foreach ($diagnosa as $dg) {
                            if ($data[$i]->norec == $dg->noregistrasifk) {
                                $data[$i]->diagnosa = $data[$i]->diagnosa . ', ' . $dg->diagnosa;
                            }
                        }
                        $i = $i + 1;
                    }
//                    dd($data);
                    $res = array(
                        'pasien' => $pasien,
                        'details' => $data,
                    );
                    array_push($compact, 'res', 'r');
                    break;
                case 'daftar-pasien-ri':
                    $kdJeniPegawaiDokter = (int)$this->settingDataFixed('KdJenisPegawaiDokter', $kdProfile);
                    $dokter = \DB::table('pegawai_m')
                        ->select('id', 'namalengkap')
                        ->where('statusenabled', true)
                        ->where('kdprofile', $kdProfile)
                        ->where('objectjenispegawaifk', $kdJeniPegawaiDokter)
                        ->orderBy('namalengkap')
                        ->get();


                    $deptInap = explode(',', $this->settingDataFixed('kdDepartemenRanapFix', $kdProfile));
                    $kdDeptInap = [];
                    foreach ($deptInap as $item) {
                        $kdDeptInap [] = (int)$item;
                    }
                    $deptPatok = $deptInap;

                    $ruangan = \DB::table('ruangan_m as ru')
                        ->select('ru.id', 'ru.namaruangan', 'ru.objectdepartemenfk')
                        ->where('ru.statusenabled', true)
                        ->wherein('ru.objectdepartemenfk', $deptPatok)
                        ->where('ru.kdprofile', (int)$kdProfile)
                        ->orderBy('ru.namaruangan')
                        ->get();

                    $valid = $this->validate_input_v2($r);
                    $tempWhere = [];
                    $ruangId = '';
                    if (isset($valid['src_idRuangan']) && $valid['src_idRuangan'] != "" && $valid['src_idRuangan'] != "undefined") {
                        $ruangId = ' AND ru.id = ' . $valid['src_idRuangan'];
                    }
                    $noreg = '';
                    if (isset($valid['noreg']) && $valid['noreg'] != "" && $valid['noreg'] != "undefined") {
                        $noreg = " AND pd.noregistrasi = '" . $valid['noreg'] . "'";
                    }
                    $norm = '';
                    if (isset($valid['src_nocm']) && $valid['src_nocm'] != "" && $valid['src_nocm'] != "undefined") {
                        $norm = " AND (ps.nocm ilike '%" . $valid['src_nocm'] . "%')
                        or  (ps.namapasien ilike '%" . $valid['src_nocm'] . "%') ";
                    }
                    $dokid = '';
                    if (isset($valid["src_idDokter"]) && $valid["src_idDokter"] != '1') {
                        $dokid = " and pg.id = $valid[src_idDokter]";
                    } else if (isset($valid["src_idDokter"]) && $valid["src_idDokter"] == '1') {
                        $r['src_idDokter'] = '';
                        $dokid = '';
                    } else {
                        $r['src_idDokter'] = $_SESSION['pegawai']->id;
                        $dokid = " and pg.id = $r[src_idDokter]";
                    }
                    $data = collect(DB::select("select * from
                        (select pd.tglregistrasi,  ps.id as nocmfk,  ps.nocm,  pd.noregistrasi,  ps.namapasien,  ps.tgllahir,
                         jk.jeniskelamin,  apd.objectruanganfk, ru.namaruangan,  kls.id as idkelas,kls.namakelas,  kp.kelompokpasien,  rek.namarekanan,
                         apd.objectpegawaifk,  pg.namalengkap as namadokter,ps.iskompleks,
                         jk.id as jkid,   EXTRACT(YEAR FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Thn '
                        || EXTRACT(MONTH FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Bln '
                        || EXTRACT(DAY FROM AGE(pd.tglregistrasi, ps.tgllahir)) || ' Hr' AS umur,null as noreservasi,
                          --br.norec,
                          klstg.namakelas as kelasditanggung,
                          EXTRACT(day from age(current_date, to_date(to_char(pd.tglregistrasi,'YYYY-MM-DD'),'YYYY-MM-DD'))) || ' Hari' as lamarawat,
                         pd.norec as norec_pd, apd.tglmasuk, apd.norec as norec_apd, row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum
                         from antrianpasiendiperiksa_t as apd
                         inner join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk and pd.objectruanganlastfk = apd.objectruanganfk
                     --    left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                         inner join pasien_m as ps on ps.id = pd.nocmfk
                         left join registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
                         left join jeniskelamin_m as jk on jk.id = ps.objectjeniskelaminfk
                         inner join kelas_m as kls on kls.id = pd.objectkelasfk
                         inner join ruangan_m as ru on ru.id = apd.objectruanganfk
                         inner join departemen_m as dept on dept.id = ru.objectdepartemenfk
                         left join pegawai_m as pg on pg.id = apd.objectpegawaifk
                         left join kelompokpasien_m as kp on kp.id = pd.objectkelompokpasienlastfk
                         left join rekanan_m as rek on rek.id = pd.objectrekananfk
                        left join pemakaianasuransi_t as pa on pa.noregistrasifk=pd.norec
                        left join asuransipasien_m as asu on pa.objectasuransipasienfk=asu.id
                        left join kelas_m as klstg on klstg.id=asu.objectkelasdijaminfk
                         --where br.norec is null
                         where pd.statusenabled = true and pd.kdprofile = $kdProfile
                        --and dept.id in (16,  17,  35)
                        and pd.tglpulang is null --and pd.noregistrasi='1808010084'
                        $ruangId $noreg $norm
                        $dokid

                         --order by ru.namaruangan asc
                         ) as x where x.rownum=1")
                    );
//                    $count = $data->toArray();
//                    if(isset($r['pages'])){
//                        $data = $data->forPage(1, 10); //Filter the page var
//                    }else{
//                        $data = $data->forPage($r['pages'], 10); //Filter the page var
//                    }
                    $count = $data->toArray();
                    $data = $data->paginate(10);

//                    dd($count);
                    $norecaPd = '';
                    foreach ($data as $ob) {
                        $norecaPd = $norecaPd . ",'" . $ob->norec_apd . "'";
                        $ob->kddiagnosa = [];
                    }
                    $norecaPd = substr($norecaPd, 1, strlen($norecaPd) - 1);
                    $diagnosa = [];
                    if ($norecaPd != '') {
                        $diagnosa = DB::select(DB::raw("
                           select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
                           from detaildiagnosapasien_t as ddp
                           left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                           left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                           where ddp.noregistrasifk in ($norecaPd) "));
                        $i = 0;
                        foreach ($data as $h) {
                            $data[$i]->kddiagnosa = [];
                            foreach ($diagnosa as $d) {
                                if ($data[$i]->norec_apd == $d->norec_apd) {
                                    $data[$i]->kddiagnosa[] = $d->kddiagnosa;
                                }
                            }
                            $i++;
                        }
                    }
                    /*
                     * hitung terlayani
                     */
                    $norecaPd2 = '';
                    foreach ($count as $ob) {
                        $norecaPd2 = $norecaPd2 . ",'" . $ob->norec_apd . "'";
                        $ob->kddiagnosa = [];
                    }
                    $norecaPd2 = substr($norecaPd2, 1, strlen($norecaPd2) - 1);
                    $terlayani = 0;
                    if ($norecaPd2 != '') {
                        $diagnosa2 = DB::select(DB::raw("
                           select dg.kddiagnosa,ddp.noregistrasifk as norec_apd
                           from detaildiagnosapasien_t as ddp
                           left join diagnosapasien_t as dp on dp.norec=ddp.objectdiagnosapasienfk
                           left join diagnosa_m as dg on ddp.objectdiagnosafk=dg.id
                           where ddp.noregistrasifk in ($norecaPd2) "));
                        $i = 0;
                        $terlayani = 0;
                        foreach ($count as $h) {
                            $count[$i]->kddiagnosa = [];
                            foreach ($diagnosa2 as $d) {
                                if ($count[$i]->norec_apd == $d->norec_apd) {
                                    $count[$i]->kddiagnosa[] = $d->kddiagnosa;
                                    if (count($count[$i]->kddiagnosa) == 1) {
                                        $terlayani = $terlayani + 1;
                                    }
                                }
                            }
                            $i++;
                        }
                    }
//                    dd($data);
                    $total['total'] = count($count);
                    $total['terlayani'] = $terlayani;
                    $total['belumterlayani'] = count($count) - $terlayani;
                    /*
                     * end hitung
                     */
//                    dd($total);
                    array_push($compact, 'data', 'r', 'ruangan', 'dokter', 'total');
                    break;
                case 'dashboard-pelayanan':
                    $tglawal = date('Y-m-d');
                    $tglakhir = date('Y-m-d');

                    if (!isset($r->tglawal) && !isset($r->tglakhir)) {

                        return redirect()->route("show_page",
                            ["role" => $_SESSION['role'],
                                "pages" => $r->pages,
                                "tglawal" => $tglawal,
                                "tglakhir" => $tglakhir]);
                    } else {
                        $tglawal = $r->tglawal;
                        $tglakhir = $r->tglakhir;
                    }

                    $res['pengunjung'] = $this->getPengunjung($tglawal, $tglakhir, $kdProfile);
                    $res['kunjungan'] = $this->getKunjungan($tglawal, $tglakhir, $kdProfile);
                    $res['trend_kunjungan'] = $this->getTrendKunjunganPasienRajal($tglawal, $tglakhir, $kdProfile);
                    $res['jenis_penjadwalan'] = $this->getPasienPerjenisPenjadwalan($tglawal, $tglakhir, $kdProfile);
                    $res['info_kedatangan'] = $this->getInfoKunjunganRawatJalanPerhari($tglawal, $tglakhir, $kdProfile);
                    $res['kunjungan_perjenispasien'] = $this->getKunjunganRSPerJenisPasien($tglawal, $tglakhir, $kdProfile);
                    $res['tt_usia'] = $this->getTempatTidurTerpakai($tglawal, $tglakhir, $kdProfile);


                    $i = 0;
                    foreach ($res['pengunjung'] as $k) {
                        $k->warna = $this->listWarna()[$i];
                        $k->gambar = $this->listGambar()[$i];
                        $k->namadepartemen = str_replace('Instalasi', 'Pengunjung ', $k->namadepartemen);
                        $i++;
                    }
                    $z = 0;
                    foreach ($res['kunjungan'] as $k) {
                        $res['kunjungan'][$z]['warna'] = $this->listWarna()[$z];
                        $res['kunjungan'][$z]['gambar'] = $this->listGambar()[$z];
                        $res['kunjungan'][$z]['namadepartemen'] = str_replace('Instalasi', 'Kunjungan ', $k['namadepartemen']);
                        $z++;
                    }

//                    dd($res);
                    array_push($compact, 'res', 'r');
                    break;
                case 'dashboard-pendapatan':
                    $tglawal = date('Y-m-d');
                    $tglakhir = date('Y-m-d');

                    if (!isset($r->tglawal) && !isset($r->tglakhir)) {
                        return redirect()->route("show_page",
                            ["role" => $_SESSION['role'],
                                "pages" => $r->pages,
                                "tglawal" => $tglawal,
                                "tglakhir" => $tglakhir]);
                    } else {
                        $tglawal = $r->tglawal;
                        $tglakhir = $r->tglakhir;
                    }
                    $res['pendapatan'] = $this->getPendapatanRumahSakit($tglawal, $tglakhir, $kdProfile, 'sehari');
//                    dd($res);
                    array_push($compact, 'res', 'r');
                    break;
                case 'dashboard-persediaan':
                    $tglawal = date('Y-m-d');
                    $tglakhir = date('Y-m-d');

                    if (!isset($r->tglawal) && !isset($r->tglakhir)) {
                        return redirect()->route("show_page",
                            ["role" => $_SESSION['role'],
                                "pages" => $r->pages,
                                "tglawal" => $tglawal,
                                "tglakhir" => $tglakhir]);
                    } else {
                        $tglawal = $r->tglawal;
                        $tglakhir = $r->tglakhir;
                    }
                    $res['obat'] = $this->getLaporanPemakaianObat($r);
                    $res['stok'] = $this->getInfoStok($r);
//                    dd(  $res['stok']);
                    $res['trend'] = $this->getTrendPemakaianObat($r);
                    array_push($compact, 'res', 'r');
                    break;
                case 'dashboard-sdm':
                    $tglawal = date('Y-m-d');
                    $tglakhir = date('Y-m-d');

                    if (!isset($r->tglawal) && !isset($r->tglakhir)) {
                        return redirect()->route("show_page",
                            ["role" => $_SESSION['role'],
                                "pages" => $r->pages,
                                "tglawal" => $tglawal,
                                "tglakhir" => $tglakhir]);
                    } else {
                        $tglawal = $r->tglawal;
                        $tglakhir = $r->tglakhir;
                    }

                    $res['pegawai'] = $this->getCountPegawai($r);
                    $aktif = 0;
                    $nonaktif = 0;
                    foreach ($res['pegawai']['statuspegawai'] as $s) {
                        if ($s->statuspegawai == 'Aktif') {
                            $aktif = $aktif + (float)$s->total;
                        } else {
                            $nonaktif = $nonaktif + (float)$s->total;
                        }
                    }
                    $res['pegawai']['aktif'] = $aktif;
                    $res['pegawai']['nonaktif'] = $nonaktif;
//                    dd($res['pegawai']);
                    array_push($compact, 'res', 'r');
                    break;
                case 'dashboard-backoffice':
                    $tglawal = date('Y-m-d');
                    $tglakhir = date('Y-m-d');

                    if (!isset($r->tglawal) && !isset($r->tglakhir)) {
                        return redirect()->route("show_page",
                            ["role" => $_SESSION['role'],
                                "pages" => $r->pages,
                                "tglawal" => $tglawal,
                                "tglakhir" => $tglakhir]);
                    } else {
                        $tglawal = $r->tglawal;
                        $tglakhir = $r->tglakhir;
                    }

                    $res = [];
                    array_push($compact, 'res', 'r');
                    break;
                case 'registrasi':
                    $res = [];
                    array_push($compact, 'res', 'r');
                    break;
                case 'formularium':
                    $res = [];
                    array_push($compact, 'res', 'r');
                    break;

                case 'formularium-rev':
                    $res = [];
                    array_push($compact, 'res', 'r');
                    break;
                case 'home':
                    $resuls = ModulAplikasiController::getMenDB();
                    $rs =  $this->GenerateNavHTML($resuls);
//                    dd($rs);
                    $res = [];
                    array_push($compact, 'res', 'r');
                    break;
               case 'formularium-rev':
                    $res = [];
                    array_push($compact, 'res', 'r');
                    break;
                case 'leaflet':
                    break;
                case 'UmVnaXN0cmFzaVBhc2llbkJhcnU=':
                    $request["pages"] = 'registasi-pasien-baru';
                      break;
                default:
                    return abort(404);
                    break;
            }
        } else {
            return abort(404);
        }

        $pages = $request["pages"];
        $role = $request["role"];
        array_push($compact, "pages");
//        if($pages == 'dashboard'){
//            return view("module.".$role.".".$pages,compact($compact));
//        }else  if($pages == 'dashboard-v2') {
//            return view("module.".$role.".".$pages, compact($compact));
//        }else{
        return view("module." . $role . "." . $pages . "." . $pages, compact($compact));
//        }

    }

// loop the multidimensional array recursively to generate the HTML
    public static function GenerateNavHTML()
    {
        $nav = file_get_contents(public_path()."/menu/".$_SESSION['role'].".json");
        $result = json_decode($nav);
        $html = '';
        foreach($nav as $page)
        {
            $html .= '<ul><li>';
            $html .= '<a href="' . $page['url'] . '">' . $page['name'] . '</a>';
            $html .= self::GenerateNavHTML($page['child']);
            $html .= '</li></ul>';
        }
        return $html;
    }
    function buildMenu($array)
    {
        $menu = '<li class="pcoded-hasmenu is-hover" subitem-icon="style1" dropdown-icon="style1">
           ';
        foreach ($array as $item)
        {
            echo ' <a href="javascript:void(0)">
            <span class="pcoded-micon"><i class="feather icon-map"></i></span>
            <span class="pcoded-mtext">'.$item['name'].'</span>
            <span class="pcoded-mcaret"></span>
            </a>';

            if (!empty($item['children']))
            {
                echo '<ul class="pcoded-submenu">';
                echo '<li class="pcoded-hasmenu is-hover" subitem-icon="style1" dropdown-icon="style1">
                    <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="ti-home"></i></span>
                    <span class="pcoded-mtext" data-i18n="nav.dash.main">'.$item['name'].'</span>
                    <span class="pcoded-mcaret"></span>
                    </a>';
                $this->buildMenu($item['children']);
                echo '</ul>';
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    function prepareMenu($array)
    {
        $return = array();
        //1
        krsort($array);
        foreach ($array as $k => &$item)
        {
            if (is_numeric($item['Parent']))
            {
                $parent = $item['Parent'];
                if (empty($array[$parent]['Childs']))
                {
                    $array[$parent]['Childs'] = array();
                }
                //2
                array_unshift($array[$parent]['Childs'],$item);
                unset($array[$k]);
            }
        }
        //3
        ksort($array);
        return $array;
    }
    public static function getDaftarPenerimaanSuplier($tglAwal, $tglAkhir, Request $request)
    {
        $kdProfile = $_SESSION['kdProfile'];
        $idProfile = (int)$kdProfile;
        $data = \DB::table('strukpelayanan_t as sp')
            ->JOIN('strukpelayanandetail_t as spd', 'spd.nostrukfk', '=', 'sp.norec')
            ->leftJOIN('rekanan_m as rkn', 'rkn.id', '=', 'sp.objectrekananfk')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.objectpegawaipenerimafk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganfk')
            ->LEFTJOIN('strukbuktipengeluaran_t as sbk', 'sbk.norec', '=', 'sp.nosbklastfk')
            ->select('sp.tglstruk', 'sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap', 'sp.nokontrak',
                'ru.namaruangan', 'sp.norec', 'sp.nofaktur', 'sp.tglfaktur', 'sp.totalharusdibayar', 'sbk.nosbk',
                'sp.nosppb', 'sp.noorderfk', 'sp.qtyproduk'
            )
            ->where('sp.kdprofile', $idProfile)
            ->groupBy('sp.tglstruk', 'sp.nostruk', 'rkn.namarekanan', 'pg.namalengkap', 'sp.nokontrak', 'ru.namaruangan', 'sp.norec', 'sp.nofaktur',
                'sp.tglfaktur', 'sp.totalharusdibayar', 'sbk.nosbk', 'sp.nosppb', 'sp.noorderfk', 'sp.qtyproduk');

        $data = $data->where('sp.tglstruk', '>=', $tglAwal . ' 00:00');
        $data = $data->where('sp.tglstruk', '<=', $tglAkhir . ' 23:59');

        if (isset($request['nostruk']) && $request['nostruk'] != "" && $request['nostruk'] != "undefined") {
            $data = $data->where('sp.nostruk', 'ilike', '%' . $request['nostruk']);
        }
        if (isset($request['namarekanan']) && $request['namarekanan'] != "" && $request['namarekanan'] != "undefined") {
            $data = $data->where('rkn.namarekanan', 'ilike', '%' . $request['namarekanan'] . '%');
        }
        if (isset($request['nofaktur']) && $request['nofaktur'] != "" && $request['nofaktur'] != "undefined") {
            $data = $data->where('sp.nofaktur', 'ilike', '%' . $request['nofaktur'] . '%');
        }
        if (isset($request['produkfk']) && $request['produkfk'] != "" && $request['produkfk'] != "undefined") {
            $data = $data->where('spd.objectprodukfk', '=', $request['produkfk']);
        }
        if (isset($request['noSppb']) && $request['noSppb'] != "" && $request['noSppb'] != "undefined") {
            $data = $data->where('sp.nosppb', 'ilike', '%' . $request['noSppb'] . '%');
        }
//        $data = $data->distinct();
        $data = $data->where('sp.statusenabled', true);
        $data = $data->where('sp.objectkelompoktransaksifk', 35);
        $data = $data->orderBy('sp.nostruk');
        $data = $data->get();

        foreach ($data as $item) {
            $details = \DB::select(DB::raw("select  pr.namaproduk,ss.satuanstandar,spd.qtyproduk,spd.qtyprodukretur,spd.hargasatuan,spd.hargadiscount,
                    --spd.hargappn,((spd.hargasatuan-spd.hargadiscount+spd.hargappn)*spd.qtyproduk) as total,spd.tglkadaluarsa,spd.nobatch
                    spd.hargappn,((spd.hargasatuan * spd.qtyproduk)-spd.hargadiscount+spd.hargappn) as total,spd.tglkadaluarsa,spd.nobatch
                    from strukpelayanandetail_t as spd
                    left JOIN produk_m as pr on pr.id=spd.objectprodukfk
                    left JOIN satuanstandar_m as ss on ss.id=spd.objectsatuanstandarfk
                    where spd.kdprofile = $idProfile and nostrukfk=:norec"),
                array(
                    'norec' => $item->norec,
                )
            );
            $result[] = array(
                'tglstruk' => $item->tglstruk,
                'nostruk' => $item->nostruk,
                'nofaktur' => $item->nofaktur,
                'tglfaktur' => $item->tglfaktur,
                'namarekanan' => $item->namarekanan,
                'norec' => $item->norec,
                'namaruangan' => $item->namaruangan,
                'namapenerima' => $item->namalengkap,
                'totalharusdibayar' => $item->totalharusdibayar,
                'nosbk' => $item->nosbk,
                'nosppb' => $item->nosppb,
                'nokontrak' => $item->nokontrak,
                'noorderfk' => $item->noorderfk,
                'jmlitem' => $item->qtyproduk,
                'details' => $details,
            );
        }
        if (count($data) == 0) {
            $result = [];
        }

//        $result = array(
//            'daftar' => $result,
//            'message' => 'as@epic',
//        );

        return $result;
    }

    public static function getDaftarDistribusiBarangPerUnit($tglAwal, $tglAkhir, Request $request)
    {
        $kdProfile = $_SESSION['kdProfile'];
        $idProfile = (int)$kdProfile;
        $kdSirs1 = $request['KdSirs1'];
        $kdSirs2 = $request['KdSirs2'];


        $data = \DB::table('strukkirim_t as sp')
            ->LEFTJOIN('pegawai_m as pg', 'pg.id', '=', 'sp.objectpegawaipengirimfk')
            ->LEFTJOIN('ruangan_m as ru', 'ru.id', '=', 'sp.objectruanganasalfk')
            ->LEFTJOIN('ruangan_m as ru2', 'ru2.id', '=', 'sp.objectruangantujuanfk')
            ->LEFTJOIN('kirimproduk_t as kp', 'kp.nokirimfk', '=', 'sp.norec')
            ->LEFTJOIN('produk_m as pr', 'pr.id', '=', 'kp.objectprodukfk')
            ->LEFTJOIN('detailjenisproduk_m as djp', 'djp.id', '=', 'pr.objectdetailjenisprodukfk')
            ->LEFTJOIN('jenisproduk_m as jp', 'jp.id', '=', 'djp.objectjenisprodukfk')
            ->LEFTJOIN('kelompokproduk_m as kps', 'kps.id', '=', 'jp.objectkelompokprodukfk')
            ->LEFTJOIN('asalproduk_m as ap', 'ap.id', '=', 'kp.objectasalprodukfk')
            ->LEFTJOIN('satuanstandar_m as ss', 'ss.id', '=', 'kp.objectsatuanstandarfk')
            ->select(
                DB::raw('sp.norec,pr.id as kodebarang,pr.kdproduk as kdsirs,pr.namaproduk,sp.nokirim,sp.jenispermintaanfk,sp.tglkirim,ss.satuanstandar,
                         kp.qtyproduk,kp.hargasatuan,ru.namaruangan as ruanganasal,ru2.namaruangan as ruangantujuan,(kp.qtyproduk*kp.hargasatuan) as total,
                         pr.objectdetailjenisprodukfk,djp.detailjenisproduk,djp.objectjenisprodukfk,jp.jenisproduk,jp.jenisproduk,jp.objectkelompokprodukfk,
                         kps.kelompokproduk,kp.objectasalprodukfk,ap.asalproduk')
            )
            ->where('sp.kdprofile', $idProfile);
        $data = $data->where('sp.tglkirim', '>=', $tglAwal . ' 00:00');
        $data = $data->where('sp.tglkirim', '<=', $tglAkhir . ' 23:59');

        if (isset($request['nokirim']) && $request['nokirim'] != "" && $request['nokirim'] != "undefined") {
            $data = $data->where('sp.nokirim', 'ilike', '%' . $request['nokirim']);
        }
        if (isset($request['ruanganasalfk']) && $request['ruanganasalfk'] != "" && $request['ruanganasalfk'] != "undefined") {
            $data = $data->where('ru.id', '=', $request['ruanganasalfk']);
        }
        if (isset($request['ruangantujuanfk']) && $request['ruangantujuanfk'] != "" && $request['ruangantujuanfk'] != "undefined") {
            $data = $data->where('ru2.id', '=', $request['ruangantujuanfk']);
        }
        if (isset($request['namaproduk']) && $request['namaproduk'] != "" && $request['namaproduk'] != "undefined") {
            $data = $data->where('pr.namaproduk', 'ilike', '%' . $request['namaproduk']);
        }

        if (isset($request['jenisProduk']) && $request['jenisProduk'] != "" && $request['jenisProduk'] != "undefined") {
            $data = $data->where('djp.objectjenisprodukfk', '=', $request['jenisProduk']);
        }
        if (isset($request['AsalProduk']) && $request['AsalProduk'] != "" && $request['AsalProduk'] != "undefined") {
            $data = $data->where('kp.objectasalprodukfk', '=', $request['AsalProduk']);
        }
        if (isset($request['kelompokProduk']) && $request['kelompokProduk'] != "" && $request['kelompokProduk'] != "undefined") {
            $data = $data->where('jp.objectkelompokprodukfk', '=', $request['kelompokProduk']);
        }
        if (isset($request['KdSirs1']) && $request['KdSirs1'] != '') {
            if ($request['KdSirs2'] != null && $request['KdSirs2'] != '' && $request['KdSirs1'] != null && $request['KdSirs1'] != '') {
                $data = $data->whereRaw(" (pr.kdproduk BETWEEN '" . $request['KdSirs1'] . "' and '" . $request['KdSirs2'] . "') ");
            } elseif ($request['KdSirs2'] && $request['KdSirs2'] != '' && $request['KdSirs1'] == '' || $request['KdSirs1'] == null) {
                $data = $data->whereRaw = (" pr.kdproduk like '" . $request['KdSirs2'] . "%'");
            } elseif ($request['KdSirs1'] && $request['KdSirs1'] != '' && $request['KdSirs2'] == '' || $request['KdSirs2'] == null) {
                $data = $data->whereRaw = (" pr.kdproduk like '" . $request['KdSirs1'] . "%'");
            }
        }

        $data = $data->where('sp.statusenabled', true);
        $data = $data->where('sp.objectkelompoktransaksifk', 34);
        $data = $data->where('kp.qtyproduk', '>', 0);
        $data = $data->orderBy('sp.nokirim');
        $data = $data->get();
        $result = array(
//            'datalogin' => $dataLogin,
            'data' => $data,
            'message' => 'Cepot'
        );
        return $data;
    }

    public function getInfoStok(Request $request)
    {
        $idProfile = $_SESSION['kdProfile'];
        $data = DB::select(DB::raw("select sum( cast (spd.qtyproduk as float))  as qtyproduk,prd.namaproduk,
                ru.namaruangan,ss.satuanstandar
                from stokprodukdetail_t as spd
                inner JOIN strukpelayanan_t as sk on sk.norec=spd.nostrukterimafk
                inner JOIN ruangan_m as ru on ru.id=spd.objectruanganfk
                inner JOIN produk_m as prd on prd.id=spd.objectprodukfk
                left JOIN satuanstandar_m as ss on ss.id=prd.objectsatuanstandarfk
                inner JOIN asalproduk_m as ap on ap.id=spd.objectasalprodukfk
                where spd.kdprofile = $idProfile and spd.qtyproduk > 0
                and prd.statusenabled=true
                and ru.statusenabled=true
                group by prd.namaproduk,ru.namaruangan,ss.satuanstandar
                order by prd.namaproduk"));
        if (count($data) > 0) {
            foreach ($data as $key => $row) {
                $count[$key] = $row->qtyproduk;
            }
            array_multisort($count, SORT_DESC, $data);
        }


        $result = array(
            'data' => $data,
            'message' => 'inhuman',
        );
        return $data;
    }

    public function getLaporanPemakaianObat(Request $request)
    {

        $idProfile = (int)$_SESSION['kdProfile'];
        $tglAwal = Carbon::now()->format('Y-m-d 00:00');
        $tglAkhir = Carbon::now()->format('Y-m-d 23:59');
        $data = DB::select(DB::raw("
            select * from (
                select x.namaproduk ,sum (x.jumlah) as jumlah ,sum(x.total ) as total from (

                select
                prd.namaproduk  , pp.jumlah , (
                ((  CASE WHEN   pp.hargasatuan IS NULL THEN 0 ELSE pp.hargasatuan END
                - CASE WHEN pp.hargadiscount IS NULL THEN   0 ELSE pp.hargadiscount END     ) * pp.jumlah
                ) + CASE    WHEN    pp.jasa IS NULL THEN 0  ELSE        pp.jasa END) AS total
                from  strukresep_t as sr
                join pelayananpasien_t  as pp  on pp.strukresepfk =sr.norec
                join produk_m as prd on pp.produkfk= prd.id
                where sr.kdprofile = $idProfile and pp.tglpelayanan BETWEEN  '$tglAwal' and '$tglAkhir'
                and pp.strukresepfk is not null

                Union all

                SELECT pr.namaproduk,  (spd.qtyproduk) as jumlah,
                  (((   CASE WHEN   spd.hargasatuan IS NULL THEN 0 ELSE spd.hargasatuan END
                - CASE WHEN spd.hargadiscount IS NULL THEN  0 ELSE spd.hargadiscount END    ) * spd.qtyproduk
                ) + CASE    WHEN    spd.hargatambahan IS NULL THEN 0    ELSE        spd.hargatambahan END) AS total
                FROM strukpelayanan_t as sp
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN   '$tglAwal' and '$tglAkhir'
                AND sp.nostruk_intern='-' AND substring(sp.nostruk,1,2)='OB'
                and sp.statusenabled != false
                Union ALL

                SELECT pr.namaproduk, (spd.qtyproduk) as jumlah,
                  (((   CASE WHEN   spd.hargasatuan IS NULL THEN 0 ELSE spd.hargasatuan END
                - CASE WHEN spd.hargadiscount IS NULL THEN  0 ELSE spd.hargadiscount END    ) * spd.qtyproduk
                ) + CASE    WHEN    spd.hargatambahan IS NULL THEN 0    ELSE        spd.hargatambahan END) AS total
                FROM strukpelayanan_t as sp
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN   '$tglAwal' and '$tglAkhir'
                AND sp.nostruk_intern not in ('-') AND substring(sp.nostruk,1,2)='OB'
                and sp.statusenabled != false
                ) as x
                group by x.namaproduk
            )  as z order by z.total desc")
        );

        $result = array(
            'data' => $data,
            'message' => 'er@epic',
        );
        return $result;
    }

    public function getDetailPegawai(Request $r)
    {
        $idProfile = (int)$_SESSION['kdProfile'];
        if ($r['jenis'] == 'Aktif') {
            $data = DB::select(DB::raw("select pg.namalengkap ,pg.id,jp.statuspegawai,
                  date_part('year', age( pg.tgllahir))::int as umur,pdd.pendidikan,jk.jeniskelamin,pg.tgllahir
                from pegawai_m  as pg
                left JOIN statuspegawai_m as jp on jp.id =pg.objectstatuspegawaifk
                left JOIN jeniskelamin_m as jk on jk.id =pg.objectjeniskelaminfk
                left JOIN pendidikan_m as pdd on pg.objectpendidikanterakhirfk = pdd.id
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                and jp.statuspegawai='$r[jenis]'
                order by pg.namalengkap"));
        } else {
            $data = DB::select(DB::raw("select pg.namalengkap ,pg.id,jp.statuspegawai,
              date_part('year', age( pg.tgllahir))::int  as umur,pdd.pendidikan,jk.jeniskelamin,pg.tgllahir
                from pegawai_m  as pg
                left JOIN statuspegawai_m as jp on jp.id =pg.objectstatuspegawaifk
                left JOIN jeniskelamin_m as jk on jk.id =pg.objectjeniskelaminfk
                left JOIN pendidikan_m as pdd on pg.objectpendidikanterakhirfk = pdd.id
                where pg.kdprofile = $idProfile and pg.statusenabled=true
            and (jp.statuspegawai in ('Non Aktif') or jp.statuspegawai is null )
                order by pg.namalengkap
                "));
        }
        return view('module.shared.detail-pegawai', compact('data'));


    }

    public static function getLaporanLayanan()
    {

        $idProfile = (int)$_SESSION['kdProfile'];

        $tglAwal = date('Y-m-d 00:00');
        $tglAkhir = date('Y-m-d 23:59');

        $results = DB::select(DB::raw("
                select pg.id as iddokter,pp.norec,pg.namalengkap as dokter,pp.jumlah as count, pp.hargasatuan as tariff ,
                pr.namaproduk as layanan, pp.hargasatuan * pp.jumlah as totall ,
                ps.nocm,ps.namapasien,pp.tglpelayanan
                from pelayananpasien_t as pp
                join antrianpasiendiperiksa_t as apd on apd.norec = pp.noregistrasifk
                join pasiendaftar_t as pd on pd.norec = apd.noregistrasifk
                join pasien_m as ps on ps.id = pd.nocmfk
                join pelayananpasienpetugas_t as ppp on ppp.pelayananpasien = pp.norec
                join pegawai_m as pg on pg.id =ppp.objectpegawaifk
                join produk_m as pr on pr.id = pp.produkfk
                where pp.kdprofile = $idProfile and pp.tglpelayanan BETWEEN '$tglAwal'  and '$tglAkhir'
                and pp.strukresepfk is null
                and ppp.objectjenispetugaspefk=4
                "));
        return $results;
    }

    public function getCountPegawai(Request $request)
    {

        $idProfile = (int)$_SESSION['kdProfile'];
        $kateg = $this->settingDataFixed('statusDataPegawaiException', $idProfile);
        $keduduk = $this->settingDataFixed('listDataKedudukanException', $idProfile);
        $jenisKelamin = DB::select(DB::raw("select count ( x.namalengkap) as total, x.jeniskelamin from (
                select jp.jeniskelamin,pg.namalengkap
                from pegawai_m  as pg
                left JOIN jeniskelamin_m as jp on jp.id =pg.objectjeniskelaminfk
                where pg.statusenabled=true
                 )as x GROUP BY x.jeniskelamin"));
        $kategoryPegawai = DB::select(DB::raw("select count ( x.namalengkap) as total, x.kategorypegawai from (
                select jp.kategorypegawai,pg.namalengkap
                from pegawai_m  as pg
                left JOIN kategorypegawai_m as jp on jp.id =pg.kategorypegawai
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                )as x GROUP BY x.kategorypegawai"));
        $kelompokJabatan = DB::select(DB::raw("select count ( x.id) as total, x.namakelompokjabatan from (
                select jp.detailkelompokjabatan as namakelompokjabatan,pg.namalengkap ,pg.id
                from pegawai_m  as pg
                inner JOIN nilaikelompokjabatan_m as jp on jp.id =pg.objectkelompokjabatanfk
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                 )as x GROUP BY x.namakelompokjabatan"));
        $unitKerja = DB::select(DB::raw("

            select count ( x.id) as total, x.unitkerja from (
            select uk.name as unitkerja,pg.namalengkap ,pg.id
            from pegawai_m as pg
            left JOIN unitkerjapegawai_m as uk on uk.id=pg.objectunitkerjapegawaifk
             where pg.statusenabled=true
             and pg.kdprofile = $idProfile
              )
            as x GROUP BY x.unitkerja
                "));
        $unitKerja2 = DB::select(DB::raw("

            select count ( x.id) as total, x.unitkerja from (
            select uk.namaruangan as unitkerja,pg.namalengkap ,pg.id
            from pegawai_m as pg
            left JOIN ruangan_m as uk on uk.id=pg.objectruangankerjafk
             where pg.statusenabled=true
             and pg.kdprofile = $idProfile
              )
            as x GROUP BY x.unitkerja
                "));
        $statusPegawai = DB::select(DB::raw("select count ( x.id) as total,x.statuspegawai as statuspegawai from (
                select pg.namalengkap ,pg.id,jp.statuspegawai
                from pegawai_m  as pg
                left JOIN statuspegawai_m as jp on jp.id =pg.objectstatuspegawaifk
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                )as x
                GROUP BY x.statuspegawai
                "));
//
//        $kedudukanPeg = DB::select(DB::raw("select count ( x.namalengkap) as total, x.kedudukan  from (
//                select jp.name as kedudukan,pg.namalengkap from pegawai_m  as pg
//                left JOIN sdm_kedudukan_m as jp on jp.id =pg.kedudukanfk
//                where pg.kdprofile = $idProfile and pg.statusenabled=true
//
//
//                )as x
//                GROUP BY x.kedudukan
//                "));

        $pendidikan = DB::select(DB::raw("select x.total, x.pendidikan  from (
                select jp.pendidikan, count(pg.namalengkap) as total from pegawai_m  as pg
                left JOIN pendidikan_m as jp on pg.objectpendidikanterakhirfk = jp.id
                where pg.statusenabled=true
                   and pg.kdprofile = $idProfile
                 GROUP by jp.pendidikan
                )as x
                order by x.total
                "));
        $jenispegawai = DB::select(DB::raw("select x.total, x.jenis  from (
                select jp.jenispegawai as jenis, count(pg.namalengkap) as total from pegawai_m  as pg
                left JOIN jenispegawai_m as jp on pg.objectjenispegawaifk = jp.id
                where pg.statusenabled=true
                   and pg.kdprofile = $idProfile
                 GROUP by jp.jenispegawai
                )as x
                order by x.total
                "));
        $usia = DB::select(DB::raw("
                select pg.namalengkap,pg.tgllahir ,
                --CONVERT(int,ROUND(DATEDIFF(hour,pg.tgllahir,GETDATE())/8766.0,0)) AS umur
                    date_part('year', age( pg.tgllahir))::int as umur
                 from pegawai_m  as pg
                where pg.kdprofile = $idProfile and pg.statusenabled=true
                "));
        $under20 = 0;
        $under30 = 0;
        $under40 = 0;
        $under50 = 0;
        $up51 = 0;
        $usiaa = [];
        foreach ($usia as $itemu) {
            if ($itemu->umur <= 20) {
                $under20 = $under20 + 1;
            }
            if ($itemu->umur > 20 && $itemu->umur <= 30) {
                $under30 = $under30 + 1;
            }
            if ($itemu->umur > 30 && $itemu->umur <= 40) {
                $under40 = $under40 + 1;
            }
            if ($itemu->umur > 40 && $itemu->umur <= 50) {
                $under50 = $under50 + 1;
            }
            if ($itemu->umur > 50) {
                $up51 = $up51 + 1;
            }
        }
        $usiaa [] = array(
            'total' => $under20,
            'usia' => 'dibawah 20 Tahun',
        );
        $usiaa [] = array(
            'total' => $under30,
            'usia' => '21 s/d 30 Tahun',
        );
        $usiaa [] = array(
            'total' => $under40,
            'usia' => '31 s/d 40 Tahun',
        );
        $usiaa [] = array(
            'total' => $under50,
            'usia' => '41 s/d 50 Tahun',
        );
        $usiaa [] = array(
            'total' => $up51,
            'usia' => 'diatas 51 Tahun',
        );
        $tglAwal = Carbon::now()->startOfMonth();
        $tglAkhir = Carbon::now()->endOfMonth();
        $dataPensiun = DB::select(DB::raw("
            select pg.id,pg.namalengkap,to_char(pg.tglpensiun,'YYYY-MM-DD') as tglpensiun,to_char (pg.tgllahir,'YYYY-MM-DD') as tgllahir,
            pg.nippns,gp.golonganpegawai,
            pdd.pendidikan,sm.name as subunitkerja,uk.name as unitkerja
            from mappegawaijabatantounitkerja_m as mappe
            left join pegawai_m as pg on mappe.objectpegawaifk =pg.id
            left join golonganpegawai_m as gp on pg.objectgolonganpegawaifk = gp.id
            left join pendidikan_m as pdd on pg.objectpendidikanterakhirfk = pdd.id
            left join subunitkerja_m sm on mappe.objectsubunitkerjapegawaifk = sm.id
            left join unitkerjapegawai_m  as uk on mappe.objectunitkerjapegawaifk = uk.id
            where mappe.kdprofile = $idProfile
            and pg.tglpensiun between '$tglAwal' and '$tglAkhir'
            order by pg.namalengkap"));

        $pensiun['tglAwal'] = $tglAwal;
        $pensiun['tglAkhir'] = $tglAkhir;
        $pensiun['bulan'] = Carbon::now()->format('F Y');
        $pensiun['data'] = $dataPensiun;

        $result = array(
            'jeniskelamin' => $jenisKelamin,
            'countjk' => count($jenisKelamin),
            'kategoripegawai' => $kategoryPegawai,
            'jenispegawai' => $jenispegawai,
            'kelompokjabatan' => $kelompokJabatan,
            'unitkerjapegawai' => $unitKerja,
            'statuspegawai' => $statusPegawai,
//            'kedudukan' => $kedudukanPeg,
            'unitkerja2' => $unitKerja2,
            'pendidikan' => $pendidikan,
            'usia' => $usiaa,
            'datapensiun' => $pensiun,
            'message' => 'ramdanegie',
        );
        return $result;

    }

    public function getTrendPemakaianObat(Request $request)
    {
        $idProfile = $_SESSION['kdProfile'];
        $tglAwal = Carbon::now()->format('Y-m-d 00:00');
        $tglAkhir = Carbon::now()->format('Y-m-d 23:59');
        $data = DB::select(DB::raw("select * from
                (
                select sum(pp.jumlah) as jumlah,prd.namaproduk
                from pelayananpasien_t  as pp
                join produk_m as prd on pp.produkfk= prd.id
                where pp.tglpelayanan BETWEEN '$tglAwal' and  '$tglAkhir'
                and pp.strukresepfk is not null
                GROUP BY prd.namaproduk

                UNION ALL
                SELECT  sum(spd.qtyproduk) as jumlah,pr.namaproduk
               FROM strukpelayanan_t as sp
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN '$tglAwal' and  '$tglAkhir'
                AND sp.nostruk_intern='-' AND substring(sp.nostruk,1,2)='OB'
                and sp.statusenabled != false
                GROUP BY pr.namaproduk
                UNION ALL
                 SELECT sum  (spd.qtyproduk) as jumlah,pr.namaproduk
               FROM strukpelayanan_t as sp
                JOIN strukpelayanandetail_t as spd on spd.nostrukfk = sp.norec
                join produk_m as pr  on pr.id =spd.objectprodukfk
                WHERE sp.kdprofile = $idProfile and sp.tglstruk BETWEEN '$tglAwal' and  '$tglAkhir'
              AND sp.nostruk_intern not in ('-') AND substring(sp.nostruk,1,2)='OB'
                and sp.statusenabled != false
                        GROUP BY pr.namaproduk
                ) as x
                order by x.jumlah desc")
        );

        $result = array(
            'chart' => $data,
            'message' => 'er@epic',
        );
        return $result;
    }

    public static function getPendapatanRumahSakit($tgl1, $tgl2, $kdProfile, $tipe)
    {

        $idProfile = (int)$kdProfile;
        $data = [];
        if ($tipe == 'sehari') {
            $tglAwal = $tgl1 . ' 00:00';
            $tglAkhir = $tgl2 . ' 23:59';
        }
        if ($tipe == 'seminggu') {
            $tglAwal = Carbon::now()->subWeek(1)->toDateString();//  Carbon::now()->subMonth(1);
            $tglAwal = date($tglAwal . ' 00:00');
            $tglAkhir = date('Y-m-d 23:59');
        }

        $data = DB::select(DB::raw("

      SELECT
    x.tglpencarian,
    x.namaruangan,
    x.namadepartemen,
    x.kelompokpasien,
    SUM (x.total) AS total
FROM
    (
        SELECT
            to_char (
                pp.tglpelayanan,
                'yyyy-MM-dd HH:mm'
            ) AS tglpencarian,
            ru.namaruangan,
            dpm.namadepartemen,
            kps.kelompokpasien,
            SUM (
                (
                    (
                        CASE
                        WHEN pp.hargajual IS NULL THEN
                            0
                        ELSE
                            pp.hargajual
                        END - CASE
                        WHEN pp.hargadiscount IS NULL THEN
                            0
                        ELSE
                            pp.hargadiscount
                        END
                    ) * pp.jumlah
                ) + CASE
                WHEN pp.jasa IS NULL THEN
                    0
                ELSE
                    pp.jasa
                END
            ) AS total
        FROM
            pelayananpasien_t AS pp
        JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
        JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
        JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
        LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
        LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
        WHERE pp.kdprofile = $idProfile and
            pp.tglpelayanan BETWEEN '$tglAwal'
        AND '$tglAkhir'
        AND pp.aturanpakai IS NULL
        AND pd.statusenabled = true
        GROUP BY
            ru.namaruangan,
            dpm.namadepartemen,
            kps.kelompokpasien,
            pp.tglpelayanan
        UNION ALL
            SELECT
                to_char (
                    sp.tglstruk,
                    'yyyy-MM-dd HH:mm'
                ) AS tglpencarian,
                ru.namaruangan,
                dp.namadepartemen,
                'Umum/Pribadi' AS kelompokpasien,
                SUM (
                    spd.qtyproduk * (
                        spd.hargasatuan - CASE
                        WHEN spd.hargadiscount IS NULL THEN
                            0
                        ELSE
                            spd.hargadiscount
                        END
                    ) + CASE
                    WHEN spd.hargatambahan IS NULL THEN
                        0
                    ELSE
                        spd.hargatambahan
                    END
                ) AS total
            FROM
                strukpelayanan_t AS sp
            JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
            LEFT JOIN ruangan_m AS ru ON ru.id = sp.objectruanganfk
            LEFT JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
            WHERE sp.kdprofile = $idProfile and
                sp.tglstruk BETWEEN '$tglAwal'
            AND '$tglAkhir'
            AND sp.nostruk LIKE 'OB%'
            AND sp.statusenabled <> false
            GROUP BY
                sp.tglstruk,
                ru.namaruangan,
                dp.namadepartemen
            UNION ALL
                SELECT
                    to_char (
                        pp.tglpelayanan,
                        'yyyy-MM-dd HH:mm'
                    ) AS tglpencarian,
                    ru.namaruangan,
                    dpm.namadepartemen,
                    kps.kelompokpasien,
                    SUM (
                        (
                            (
                                CASE
                                WHEN pp.hargajual IS NULL THEN
                                    0
                                ELSE
                                    pp.hargajual
                                END - CASE
                                WHEN pp.hargadiscount IS NULL THEN
                                    0
                                ELSE
                                    pp.hargadiscount
                                END
                            ) * pp.jumlah
                        ) + CASE
                        WHEN pp.jasa IS NULL THEN
                            0
                        ELSE
                            pp.jasa
                        END
                    ) AS total
                FROM
                    pelayananpasien_t AS pp
                JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
                JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
                LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                WHERE pp.kdprofile = $idProfile and
                    pp.tglpelayanan BETWEEN '$tglAwal'
                AND '$tglAkhir'
                AND pp.aturanpakai IS NOT NULL
                AND pd.statusenabled = true
                GROUP BY
                    ru.namaruangan,
                    dpm.namadepartemen,
                    kps.kelompokpasien,
                    pp.tglpelayanan
    ) AS x
GROUP BY
    x.tglpencarian,
    x.kelompokpasien,
    x.namaruangan,
    x.namadepartemen


           "));
        if (count($data) > 0) {
            foreach ($data as $key => $row) {
                $count[$key] = $row->tglpencarian;
            }


            array_multisort($count, SORT_ASC, $data);
        }


        $result = array(
            'data' => $data,
//            'count' => count($data),
            'message' => 'ramdanegie',
        );
        return $result;

    }

    public static function formatRp($number)
    {
        return 'Rp.' . number_format((float)$number, 2, ".", ",");
    }

    public static function getPenerimaanKasir($tgl, $tgl2, $kdProfile)
    {
        $request['tglAwal'] = $tgl . ' 00:00';
        $request['tglAkhir'] = $tgl2 . ' 23:59';
        $idProfile = (int)$kdProfile;
        $data = \DB::table('strukbuktipenerimaan_t as sbm')
            ->leftJOIN('strukbuktipenerimaancarabayar_t as sbmc', 'sbmc.nosbmfk', '=', 'sbm.norec')
            ->leftJOIN('carabayar_m as cb', 'cb.id', '=', 'sbmc.objectcarabayarfk')
            ->join('strukpelayanan_t as sp', 'sp.nosbmlastfk', '=', 'sbm.norec')
            ->leftJOIN('loginuser_s as lu', 'lu.id', '=', 'sbm.objectpegawaipenerimafk')
            ->leftJOIN('pegawai_m as pg2', 'pg2.id', '=', 'lu.objectpegawaifk')
            ->leftJOIN('pasiendaftar_t as pd', 'pd.norec', '=', 'sp.noregistrasifk')
            ->leftJOIN('pasien_m as ps', 'ps.id', '=', 'sp.nocmfk')
            ->leftJOIN('jeniskelamin_m as jk', 'jk.id', '=', 'ps.objectjeniskelaminfk')
            ->leftJOIN('pegawai_m as pg', 'pg.id', '=', 'pd.objectpegawaifk')
            ->leftJOIN('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->leftJoin('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->leftJOIN('kelompokpasien_m as kp', 'kp.id', '=', 'pd.objectkelompokpasienlastfk')
            ->select('sbm.tglsbm', 'ps.nocm', 'ru.namaruangan', 'pg.namalengkap', 'pg2.namalengkap as kasir',
                'sp.totalharusdibayar', 'sbm.totaldibayar', 'ru.objectdepartemenfk', 'ru.id as ruid', 'dp.namadepartemen',
                DB::raw('( case when pd.noregistrasi is null then sp.nostruk else pd.noregistrasi end) as noregistrasi,
                (case when ps.namapasien is null then sp.namapasien_klien else ps.namapasien end) as namapasien,
                (case when kp.kelompokpasien is null then null else kp.kelompokpasien end) as kelompokpasien,
                (CASE WHEN sp.totalprekanan is null then 0 else sp.totalprekanan end) as hutangpenjamin,
                (case when cb.id = 1 then sbm.totaldibayar else 0 end) as tunai,
                (case when cb.id > 1 then sbm.totaldibayar else 0 end) as nontunai')
            )
            ->where('sbm.kdprofile', $idProfile);
//            ->where('djp.objectjenisprodukfk','<>',97)
//            ->whereNull('sp.statusenabled')
//            ->where('ru.objectdepartemenfk',18);

        if (isset($request['tglAwal']) && $request['tglAwal'] != "" && $request['tglAwal'] != "undefined") {
            $data = $data->where('sbm.tglsbm', '>=', $request['tglAwal']);
        }
        if (isset($request['tglAkhir']) && $request['tglAkhir'] != "" && $request['tglAkhir'] != "undefined") {
            $tgl = $request['tglAkhir'];//." 23:59:59";
            $data = $data->where('sbm.tglsbm', '<=', $tgl);
        }
        if (isset($request['idKasir']) && $request['idKasir'] != "" && $request['idKasir'] != "undefined") {
            $data = $data->where('pg2.id', '=', $request['idKasir']);
        }
        if (isset($request['idDokter']) && $request['idDokter'] != "" && $request['idDokter'] != "undefined") {
            $data = $data->where('pd.objectpegawaifk', '=', $request['idDokter']);
        }
        if (isset($request['idDept']) && $request['idDept'] != "" && $request['idDept'] != "undefined") {
            $data = $data->where('ru.objectdepartemenfk', '=', $request['idDept']);
        }
        if (isset($request['idRuangan']) && $request['idRuangan'] != "" && $request['idRuangan'] != "undefined") {
            $data = $data->where('pd.objectruanganlastfk', '=', $request['idRuangan']);
        }
        if (isset($request['kelompokPasien']) && $request['kelompokPasien'] != "" && $request['kelompokPasien'] != "undefined") {
            $data = $data->where('kp.id', '=', $request['kelompokPasien']);
        }


        $data = $data->orderBy('pd.noregistrasi', 'ASC');

        $data = $data->get();
        $result = array(
            'data' => $data,
            'message' => 'inhuman'
        );
        return $result;
    }

    public function getDetailPendapatan(Request $r)
    {

        $data = DB::select(DB::raw("


    SELECT
                x.namapasien,
                x.nocm,
                x.noregistrasi,
                x.tglpencarian,
                x.namaruangan,
                x.namadepartemen,
                x.kelompokpasien,
                SUM (x.total) AS total,
                x.layanan
            FROM
            (
                SELECT
                        pm.namapasien,
                        pm.nocm,
                        pd.noregistrasi,
                        to_char (
                            pp.tglpelayanan,
                            'yyyy-MM-dd'
                        ) AS tglpencarian,
                        ru.namaruangan,
                        dpm.namadepartemen,
                        kps.kelompokpasien,
                        SUM (
                            (
                            (
                                    CASE
                                    WHEN pp.hargajual IS NULL THEN
                                        0
                                    ELSE
                                        pp.hargajual
                                    END - CASE
                                    WHEN pp.hargadiscount IS NULL THEN
                                        0
                                    ELSE
                                        pp.hargadiscount
                                    END
                                ) * pp.jumlah
                            ) + CASE
                            WHEN pp.jasa IS NULL THEN
                                0
                            ELSE
                                pp.jasa
                            END
                        ) AS total,
                        'Layanan' AS layanan
                    FROM
                        pelayananpasien_t AS pp
                    JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                    JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                    JOIN pasien_m AS pm ON pm.id = pd.nocmfk
                    JOIN ruangan_m AS ru ON ru.id = apd.objectruanganfk
                    LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                    LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                    WHERE
                        pp.tglpelayanan BETWEEN '$r[tglawal] 00:00'
AND '$r[tglakhir] 23:59'
and ru.namaruangan='$r[ruangan]'
AND pp.aturanpakai IS NULL
AND pd.statusenabled = true
                    GROUP BY
                        pm.namapasien,
                        pm.nocm,
                        pd.noregistrasi,
                        ru.namaruangan,
                        dpm.namadepartemen,
                        kps.kelompokpasien,
                        pp.tglpelayanan
                    UNION ALL
                        SELECT
                            sp.namapasien_klien AS namapasien,
                            sp.nostruk_intern AS nocm,
                            '-' AS noregistrasi,
                            to_char (
                                sp.tglstruk,
                                'yyyy-MM-dd'
                            ) AS tglpencarian,
                            ru.namaruangan,
                            dp.namadepartemen,
                            'Umum/Pribadi' AS kelompokpasien,
                            SUM (
                                spd.qtyproduk * (
                                    spd.hargasatuan - CASE
                                    WHEN spd.hargadiscount IS NULL THEN
                                        0
                                    ELSE
                                        spd.hargadiscount
                                    END
                                ) + CASE
                                WHEN spd.hargatambahan IS NULL THEN
                                    0
                                ELSE
                                    spd.hargatambahan
                                END
                            ) AS total,
                            'Non Layanan' AS layanan
                        FROM
                            strukpelayanan_t AS sp
                        JOIN strukpelayanandetail_t AS spd ON spd.nostrukfk = sp.norec
                        LEFT JOIN ruangan_m AS ru ON ru.id = sp.objectruanganfk
                        LEFT JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
                        WHERE
                            sp.tglstruk BETWEEN '$r[tglawal] 00:00'
AND '$r[tglakhir] 23:59'
and ru.namaruangan='$r[ruangan]'
AND SUBSTRING (sp.nostruk, 1, 2) = 'OB'
AND sp.statusenabled <> false
                        GROUP BY
                            sp.namapasien_klien,
                            sp.nostruk_intern,
                            sp.tglstruk,
                            ru.namaruangan,
                            dp.namadepartemen
                        UNION ALL
                            SELECT
                                pm.namapasien,
                                pm.nocm,
                                pd.noregistrasi,
                                to_char (
                                    pp.tglpelayanan,
                                    'yyyy-MM-dd'
                                ) AS tglpencarian,
                                ru.namaruangan,
                                dpm.namadepartemen,
                                kps.kelompokpasien,
                                SUM (
                                    (
                                    (
                                            CASE
                                            WHEN pp.hargajual IS NULL THEN
                                                0
                                            ELSE
                                                pp.hargajual
                                            END - CASE
                                            WHEN pp.hargadiscount IS NULL THEN
                                                0
                                            ELSE
                                                pp.hargadiscount
                                            END
                                        ) * pp.jumlah
                                    ) + CASE
                                    WHEN pp.jasa IS NULL THEN
                                        0
                                    ELSE
                                        pp.jasa
                                    END
                                ) AS total,
                                'Layanan' AS layanan
                            FROM
                                pelayananpasien_t AS pp
                            JOIN antrianpasiendiperiksa_t AS apd ON apd.norec = pp.noregistrasifk
                            JOIN strukresep_t AS sr ON sr.norec = pp.strukresepfk
                            JOIN pasiendaftar_t AS pd ON pd.norec = apd.noregistrasifk
                            JOIN pasien_m AS pm ON pm.id = pd.nocmfk
                            JOIN ruangan_m AS ru ON ru.id = sr.ruanganfk
                            LEFT JOIN kelompokpasien_m AS kps ON kps.id = pd.objectkelompokpasienlastfk
                            LEFT JOIN departemen_m AS dpm ON dpm.id = ru.objectdepartemenfk
                            WHERE
                                pp.tglpelayanan BETWEEN'$r[tglawal] 00:00'
                            AND '$r[tglakhir] 23:59'
                            and ru.namaruangan='$r[ruangan]'
                            AND pp.aturanpakai IS NOT NULL
                            AND pd.statusenabled = true
                            GROUP BY
                                pm.namapasien,
                                pm.nocm,
                                pd.noregistrasi,
                                ru.namaruangan,
                                dpm.namadepartemen,
                                kps.kelompokpasien,
                                pp.tglpelayanan
                ) AS x
            GROUP BY
                x.namapasien,
                x.nocm,
                x.noregistrasi,
                x.tglpencarian,
                x.kelompokpasien,
                x.namaruangan,
                x.namadepartemen,
                x.layanan



                       "));


        return view('module.shared.detail-pendapatan', compact('data'));


    }

    public function getDetailKun(Request $r)
    {
        $idProfile = $_SESSION['kdProfile'];
        $tglAwal = $r['tglawal'] . ' 00:00';
        $tglAkhir = $r['tglakhir'] . ' 23:59';
        $idDepRanap = (int)$this->settingDataFixed('idDepRawatInap', $idProfile);
        $idDepFarmasi = (int)$this->settingDataFixed('IdDepartemenInstalasiFarmasi', $idProfile);
        $isfaramsi = false;
        if ($r['jenis'] == 'pengunjung') {
            $data = DB::table('pasiendaftar_t as pd')
                ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
                ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                ->select('pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'ps.namapasien', 'ru.namaruangan',
                    'pd.tglpulang')
                ->where('pd.kdprofile', $idProfile)
                ->whereBetween('pd.tglregistrasi', [$tglAwal, $tglAkhir])
                ->where('ru.objectdepartemenfk', $r['id'])
                ->where('pd.statusenabled', 1)
                ->get();
        } else {
            if ($r['id'] == $idDepRanap) {
                $data = DB::select(DB::raw("SELECT
                    pd.noregistrasi,
                    pd.tglregistrasi,
                    ps.nocm,
                    ps.namapasien,
                    ru.namaruangan,
                    pd.tglpulang
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                INNER JOIN departemen_m AS dp ON dp.id = ru.objectdepartemenfk
                WHERE pd.kdprofile = $idProfile
                and pd.statusenabled = true
                AND (
                    pd.tglregistrasi < '$tglAwal'
                    AND pd.tglpulang >= '$tglAkhir'
                  OR pd.tglpulang IS NULL
                )

             "));
            } else if ($r['id'] == $idDepFarmasi) {
                $isfaramsi = true;
                $data = DB::select(DB::raw("
                SELECT
                    apd.norec,  pd.noregistrasi,pd.tglregistrasi,
                    ps.nocm,ps.namapasien,
                    ru.namaruangan, ru2.namaruangan AS ruanganfarmasi,sr.noresep,
                    sr.tglresep,    pg.namalengkap,pd.tglpulang
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
                INNER JOIN pasien_m AS ps ON ps. ID = pd.nocmfk
                INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                INNER JOIN strukresep_t AS sr ON sr.pasienfk = apd.norec
                LEFT JOIN pegawai_m AS pg ON sr.penulisresepfk = pg. ID
                LEFT JOIN ruangan_m AS ru2 ON ru2. ID = sr.ruanganfk
                WHERE pd.kdprofile = $idProfile and
                    sr.tglresep BETWEEN '$tglAwal'
                AND '$tglAkhir'
                 and (sr.statusenabled is null or sr.statusenabled = true)
               "));
            } else {
                $data = DB::table('antrianpasiendiperiksa_t as apd')
                    ->join('pasiendaftar_t as pd', 'pd.norec', '=', 'apd.noregistrasifk')
                    ->join('pasien_m as ps', 'ps.id', '=', 'pd.nocmfk')
                    ->join('ruangan_m as ru', 'ru.id', '=', 'apd.objectruanganfk')
                    ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
                    ->select('pd.noregistrasi', 'pd.tglregistrasi', 'ps.nocm', 'ps.namapasien', 'ru.namaruangan',
                        'pd.tglpulang')
                    ->where('apd.kdprofile', $idProfile)
                    ->whereBetween('apd.tglregistrasi', [$tglAwal, $tglAkhir])
                    ->where('pd.statusenabled', 1)
                    ->where('ru.objectdepartemenfk', $r['id'])
                    ->get();
            }
        }

        $title = DB::table('departemen_m')->where('id', $r['id'])->first()->namadepartemen;
        return view('module.shared.detail-kunjungan', compact('data', 'title', 'isfaramsi'));

    }

    function getComboDiagnosaTop()
    {
        $data = DB::select(DB::raw("select kddiagnosa,namadiagnosa from pelayananmedis_t
                where kddiagnosa is not null
                and kddiagnosa !='-'
                GROUP BY kddiagnosa,namadiagnosa
                order by kddiagnosa asc"));
        echo "<option value=''>-- Filter Diagnosa --</option>";
        foreach ($data as $k) {
            echo "<option value='$k->kddiagnosa' >" . $k->kddiagnosa . ' - ' . $k->namadiagnosa . "</option>";
        }
    }

    public static function getAllMonitoringKlaim()
    {
        $kdProfile = $_SESSION['kdProfile'];
        $y = date('Y') - 1;
        $start = $month = strtotime($y . '-01-01');
        $end = strtotime(date('Y-m-d'));
        $arrM = [];
        while ($month < $end) {
            $arrM [] = array(
                'blntahun' => date('Y-m', $month),
                'tahun' => date('Y', $month),
                'bulan' => date('F', $month),
                'jmlkasus_ri' => 0,
                'jmlkasuspending_ri' => 0,
                'pengajuan_ri' => static::formatRp(0),
                'pending_ri' => static::formatRp(0),
                'klaim_ri' => static::formatRp(0),
                'jmlkasus_rj' => 0,
                'jmlkasuspending_rj' => 0,
                'pengajuan_rj' => static::formatRp(0),
                'pending_rj' => static::formatRp(0),
                'klaim_rj' => static::formatRp(0),
            );
            $month = strtotime("+1 month", $month);
        }
        foreach ($arrM as $key => $row) {
            $count[$key] = $row['blntahun'];
        }
        array_multisort($count, SORT_DESC, $arrM);
        // return $arrM ;
        $data = DB::select(DB::raw("select x.tahun,x.bulan,
            sum(x.jmlkasus_ri) as jmlkasus_ri ,sum(x.jmlkasuspending_ri) as jmlkasuspending_ri ,
            sum(x.pengajuan_ri) as pengajuan_ri ,
            sum(x.pending_ri) as pending_ri ,
            sum(x.klaim_ri) as klaim_ri ,

            sum(x.jmlkasus_rj) as jmlkasus_rj ,sum(x.jmlkasuspending_rj) as jmlkasuspending_rj,
            sum(x.pengajuan_rj) as pengajuan_rj,
            sum(x.pending_rj) as pending_rj ,
            sum(x.klaim_rj) as klaim_rj,
            x.blntahun
             from (
            SELECT to_char(tglpulang,'yyyy') as tahun,to_char(tglpulang,'MM')as  bulan,
             to_char(tglpulang,'yyyy') || '-' ||to_char(tglpulang,'MM')as blntahun,
            case when jenispelayanan ='1' then count(norec) else 0 end as jmlkasus_ri,
            case when jenispelayanan ='2' then count(norec) else 0  end as jmlkasus_rj,
            case when jenispelayanan ='1'  and status='Proses Pending' then 1 else 0 end as jmlkasuspending_ri,
            case when jenispelayanan ='2'  and status='Proses Pending' then 1 else 0 end as jmlkasuspending_rj,
            case when jenispelayanan ='1' then   sum(totalpengajuan) else 0 end as pengajuan_ri,
            case when jenispelayanan ='2' then   sum(totalpengajuan) else 0 end as pengajuan_rj,
            case when jenispelayanan ='1' and  status='Proses Pending' then sum(totalpengajuan) else 0 end as pending_ri,
            case when jenispelayanan ='2' and  status='Proses Pending' then sum(totalpengajuan) else 0 end as pending_rj,
            case when jenispelayanan ='1' and status='Klaim' then sum(totalpengajuan) else 0 end as klaim_ri,
            case when jenispelayanan ='2' and status='Klaim' then sum(totalpengajuan) else 0 end as klaim_rj

            FROM monitoringklaim_t
            where kdprofile=$kdProfile
            group by tglpulang,status,jenispelayanan
            ) as x GROUP BY x.tahun,x.bulan,x.blntahun
            order by x.blntahun desc;


                "));
        $i = 0;
        foreach ($arrM as $key => $v) {
            foreach ($data as $key2 => $k) {
                if ($arrM[$i]['blntahun'] == $k->blntahun) {
                    $arrM[$i]['blntahun'] = $k->blntahun;
                    $arrM[$i]['tahun'] = $k->tahun;
                    // $arrM[$i]['bulan']  =  $k->bulan;
                    $arrM[$i]['jmlkasus_ri'] = $k->jmlkasus_ri;
                    $arrM[$i]['jmlkasuspending_ri'] = $k->jmlkasuspending_ri;
                    $arrM[$i]['pengajuan_ri'] = static::formatRp($k->pengajuan_ri);
                    $arrM[$i]['pending_ri'] = static::formatRp($k->pending_ri);
                    $arrM[$i]['klaim_ri'] = static::formatRp($k->klaim_ri);
                    $arrM[$i]['jmlkasus_rj'] = $k->jmlkasus_rj;
                    $arrM[$i]['jmlkasuspending_rj'] = $k->jmlkasuspending_rj;
                    $arrM[$i]['pengajuan_rj'] = static::formatRp($k->pengajuan_rj);
                    $arrM[$i]['pending_rj'] = static::formatRp($k->pending_rj);
                    $arrM[$i]['klaim_rj'] = static::formatRp($k->klaim_rj);

                }
            }
            $i++;
        }


        $result = array(
            'data' => $arrM,
            'as' => 'er@epic',
        );

        return $arrM;
    }

    public static function getKetersediaanTempatTidurPerkelas($tglAwal, $tglAkhir, $kdProfile)
    {

        $idProfile = (int)$kdProfile;

        $data = DB::select(DB::raw("select sum(x.isi) as terpakai, sum(x.kosong) as kosong,x.namakelas,
        sum(x.isi)+ sum(x.kosong) as jml from (
            SELECT
                kmr.id,
                kmr.namakamar,
                kl.id AS id_kelas,
                kl.namakelas,
                ru.id AS id_ruangan,
                ru.namaruangan,
                kmr.jumlakamarisi,
                kmr.qtybed,
                case when sb.id=1 then 1 else 0 end as isi,
                case when sb.id=2 then 1 else 0 end as kosong
            FROM
                tempattidur_m AS tt
            LEFT JOIN statusbed_m AS sb ON sb.id = tt.objectstatusbedfk
            LEFT JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            LEFT JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
            LEFT JOIN kelas_m AS kl ON kl.id = kmr.objectkelasfk
            WHERE tt.kdprofile = $idProfile and
                tt.statusenabled = true
                and kmr.statusenabled=TRUE) as x GROUP BY x.namakelas
            order by  x.namakelas

            "));

        return $data;
    }

    public static function getBorLosToi($tglAwal, $tglAkhir, $kdProfile)
    {

        $idProfile = (int)$kdProfile;
        $idDepRaJal = (int)static::settingDataFixed2('KdDepartemenRawatJalan', $idProfile);
        $idDepRanap = (int)static::settingDataFixed2('idDepRawatInap', $idProfile);
        $idStatKelMeninggal = (int)static::settingDataFixed2('KdStatKeluarMeninggal', $idProfile);
        $idKondisiPasienMeninggal = (int)static::settingDataFixed2('KdKondisiPasienMeninggal', $idProfile);
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $tahun = new \DateTime($tglAkhir);
        $tahun = date('Y');
        $datetime1 = new \DateTime($tglAwal);
        $datetime2 = new \DateTime($tglAkhir);
        $interval = $datetime1->diff($datetime2);
        $sehari = 1;//$interval->format('%d');
        $data10 = [];
        $jumlahTT = collect(DB::select("SELECT
                    tt.id,
                    tt.objectstatusbedfk
            FROM
                    tempattidur_m AS tt
            INNER JOIN kamar_m AS kmr ON kmr.id = tt.objectkamarfk
            INNER JOIN ruangan_m AS ru ON ru.id = kmr.objectruanganfk
            WHERE
                    tt.kdprofile = $idProfile
            AND tt.statusenabled = true
            AND kmr.statusenabled = true
            "))->count();
        if ($jumlahTT == 0) {
            $data10[] = array(
                'lamarawat' => 0,
                'hariperawatan' => 0,
                'pasienpulang' => 0,
                'meninggal' => 0,
                'matilebih48' => 0,
                'tahun' => 0,
                'bulan' => date('d-M-Y'),//(float)$item->bulanregis ,
                'bor' => 0,
                'alos' => 0,
                'bto' => 0,
                'toi' => 0,
                'gdr' => 0,
                'ndr' => 0,
            );

            return $data10;
        }

        $hariPerawatan = DB::select(DB::raw("
           SELECT   COUNT (x.noregistrasi) AS jumlahhariperawatan
            FROM
            (
                SELECT
                    pd.noregistrasi,
                    pd.tglpulang,
                    to_char ( pd.tglregistrasi,'mm') AS bulanregis
                FROM
                    pasiendaftar_t AS pd
                INNER JOIN ruangan_m AS ru ON ru.ID = pd.objectruanganlastfk
                WHERE
                    ru.objectdepartemenfk = $idDepRanap
                    and pd.kdprofile = $idProfile
            and (  pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir'
            )
            or pd.tglpulang is null
            and pd.statusenabled = true
            and pd.kdprofile = $idProfile
           and  ru.objectdepartemenfk = $idDepRanap
            ) AS x"
        ));
        $lamaRawat = DB::select(DB::raw("
                        select sum(x.hari) as lamarawat, count(x.noregistrasi)as jumlahpasienpulang from (
                        SELECT
                            date_part('DAY', pd.tglpulang- pd.tglregistrasi) as hari ,pd.noregistrasi
                            FROM
                                    pasiendaftar_t AS pd
                        --  INNER JOIN antrianpasiendiperiksa_t AS apd ON apd.noregistrasifk = pd.norec
                            INNER JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
                            WHERE pd.kdprofile = $idProfile and
                            pd.tglpulang BETWEEN '$tglAwal'
                              AND '$tglAkhir'
                            and pd.tglpulang is not null
                            and pd.statusenabled=true
                            and  ru.objectdepartemenfk = $idDepRanap
                            GROUP BY pd.noregistrasi,pd.tglpulang,pd.tglregistrasi
                         -- order by pd.noregistrasi
                      ) as x
                "));


        $dataMeninggal = DB::select(DB::raw("select count(x.noregistrasi) as jumlahmeninggal, x.bulanregis,
                count(case when x.objectkondisipasienfk = $idKondisiPasienMeninggal then 1 end ) AS jumlahlebih48 FROM
                (
                select noregistrasi,to_char(tglregistrasi , 'mm')  as bulanregis ,statuskeluar,kondisipasien,objectkondisipasienfk
                from pasiendaftar_t
                join statuskeluar_m on statuskeluar_m.id =pasiendaftar_t.objectstatuskeluarfk
                left join kondisipasien_m on kondisipasien_m.id =pasiendaftar_t.objectkondisipasienfk
                where pasiendaftar_t.kdprofile = $idProfile and objectstatuskeluarfk = $idStatKelMeninggal
                and  tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
                and pasiendaftar_t.statusenabled=true
                ) as x
                GROUP BY x.bulanregis;"));
//        return $this->respond($dataMeninggal);
        $year = Carbon::now()->year;
        $num_of_days = [];
        if ($year == date('Y'))
            $total_month = date('m');
        else
            $total_month = 12;

        for ($m = 1; $m <= $total_month; $m++) {
            $num_of_days[] = array(
                'bulan' => $m,
                'jumlahhari' => cal_days_in_month(CAL_GREGORIAN, $m, $year),
            );
        }
        $bor = 0;
        $alos = 0;
        $toi = 0;
        $bto = 0;
        $ndr = 0;
        $gdr = 0;
        $hariPerawatanJml = 0;
        $jmlPasienPlg = 0;
        $jmlLamaRawat = 0;
        $jmlMeninggal = 0;
        $jmlMatilebih48 = 0;
        foreach ($hariPerawatan as $item) {
            foreach ($lamaRawat as $itemLamaRawat) {
                foreach ($dataMeninggal as $itemDead) {
//                         if ($item->bulanregis == $itemLamaRawat->bulanpulang &&
//                             $itemLamaRawat->bulanpulang == $itemDead->bulanregis ) {
                    /** @var  $gdr = (Jumlah Mati dibagi Jumlah pasien Keluar (Hidup dan Mati) */
                    $gdr = (int)$itemDead->jumlahmeninggal * 1000 / (int)$itemLamaRawat->jumlahpasienpulang;
                    /** @var  $NDR = (Jumlah Mati > 48 Jam dibagi Jumlah pasien Keluar (Hidup dan Mati) */
                    $ndr = (int)$itemDead->jumlahlebih48 * 1000 / (int)$itemLamaRawat->jumlahpasienpulang;

                    $jmlMeninggal = (int)$itemDead->jumlahmeninggal;
                    $jmlMatilebih48 = (int)$itemDead->jumlahlebih48;
//                         }
                }
//                if ($item->bulanregis == $itemLamaRawat->bulanpulang ) {
                /** @var  $alos = (Jumlah Lama Dirawat dibagi Jumlah pasien Keluar (Hidup dan Mati) */
//                return $this->respond($itemLamaRawat->jumlahpasienpulang );
                if ((int)$itemLamaRawat->jumlahpasienpulang > 0) {
                    $alos = (int)$itemLamaRawat->lamarawat / (int)$itemLamaRawat->jumlahpasienpulang;
                }

                /** @var  $bto = Jumlah pasien Keluar (Hidup dan Mati) DIBAGI Jumlah tempat tidur */
                $bto = (int)$itemLamaRawat->jumlahpasienpulang / $jumlahTT;

//                }
//                foreach ($num_of_days as $numday){
//                    if ($numday['bulan'] == $item->bulanregis){
                /** @var  $bor = (Jumlah hari perawatn RS dibagi ( jumlah TT x Jumlah hari dalam satu periode ) ) x 100 % */
                $bor = ((int)$item->jumlahhariperawatan * 100 / ($jumlahTT * (float)$sehari));//$numday['jumlahhari']));
                /** @var  $toi = (Jumlah TT X Periode) - Hari Perawatn DIBAGI Jumlah pasien Keluar (Hidup dan Mati) */
//                        $toi = ( ( $jumlahTT * $numday['jumlahhari'] )- (int)$item->jumlahhariperawatan ) /(int)$itemLamaRawat->jumlahpasienpulang ;
                if ((int)$itemLamaRawat->jumlahpasienpulang > 0) {
                    $toi = (($jumlahTT * (float)$sehari) - (int)$item->jumlahhariperawatan) / (int)$itemLamaRawat->jumlahpasienpulang;
                }
                $hariPerawatanJml = (int)$item->jumlahhariperawatan;
                $jmlPasienPlg = (int)$itemLamaRawat->jumlahpasienpulang;
//                    }
//                }
            }

            $data10[] = array(
                'lamarawat' => (int)$itemLamaRawat->lamarawat,
                'hariperawatan' => $hariPerawatanJml,
                'pasienpulang' => $jmlPasienPlg,
                'meninggal' => $jmlMeninggal,
                'matilebih48' => $jmlMatilebih48,
                'tahun' => $tahun,
                'bulan' => date('d-M-Y'),//(float)$item->bulanregis ,
                'bor' => (float)number_format($bor, 2),
                'alos' => (float)number_format($alos, 2),
                'bto' => (float)number_format($bto, 2),
                'toi' => (float)number_format($toi, 2),
                'gdr' => (float)number_format($gdr, 2),
                'ndr' => (float)number_format($ndr, 2),
            );
        }
//        dd($data10);
        return $data10;

    }

    public function getTopDiagByKec(Request $r)
    {
        $idProfile = $_SESSION['kdProfile'];
        // $dataLogin = $request->all();
        $tglAwal = $r['tglawal'] . ' 00:00';
        $tglAkhir = $r['tglakhir'] . ' 23:59';
        $bulan = Carbon::now()->format('F');
        $paramProp = '';
        $paramKota = '';
        $paramKec = '';
//        if(isset($request['propinsiId']) && $request['propinsiId']!=''&& $request['propinsiId']!='undefined'){
//            $paramProp = ' and pro.id='.$request['propinsiId'];
//        }
//        if(isset($request['kotaId']) && $request['kotaId']!=''&& $request['kotaId']!='undefined'){
//            $paramKota = ' and kot.id='.$request['kotaId'];
//        }
        if (isset($r['id']) && $r['id'] != '' && $r['id'] != 'undefined') {
            $paramKec = ' and kec.id=' . $r['id'];
        }
        $data = DB::select(DB::raw("select * from (
                select count(x.kddiagnosa)as jumlah,x.kddiagnosa,x.namadiagnosa
                from (select dm.kddiagnosa,
                dm.namadiagnosa
                from antrianpasiendiperiksa_t as app
                left join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
                left join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
                inner join pasiendaftar_t as pd on pd.norec = app.noregistrasifk
                inner join pasien_m as ps on ps.id = pd.nocmfk
                left join alamat_m as alm on alm.nocmfk = ps.id
                 left join kecamatan_m as kec on kec.id = alm.objectkecamatanfk
                left join kotakabupaten_m as kot on kot.id = alm.objectkotakabupatenfk
                left join propinsi_m as pro on pro.id = alm.objectpropinsifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where app.kdprofile = $idProfile and dm.kddiagnosa <> '-'  and   pd.statusenabled=true and
                pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                $paramProp
                $paramKota
                 $paramKec

                )as x GROUP BY x.namadiagnosa ,x.kddiagnosa
                ) as z
                ORDER BY z.jumlah desc  limit 10

            "));
        if (count($data) > 0) {
            foreach ($data as $item) {
                $result[] = array(
                    'jumlah' => $item->jumlah,
                    'kd' => $item->kddiagnosa,
                    'kddiagnosa' => $item->kddiagnosa . '-' . $item->namadiagnosa,
                    'namadiagnosa' => $item->namadiagnosa,
                );
            }

        } else {
            $result[] = array(
                'jumlah' => 0,
                'kd' => null,
                'kddiagnosa' => null,
                'namadiagnosa' => null
            );
        }

        $results = array(
            'result' => $result,
            'month' => $bulan,
            'message' => 'ramdanegie',
        );
        return $result;
    }

    public static function getTopTenDiagnosa($tglAwal, $tglAkhir, $kdProfile, $prov = null)
    {
        $idProfile = (int)$kdProfile;
        // $dataLogin = $request->all();
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $bulan = Carbon::now()->format('F');
        $paramProp = '';
        $paramKota = '';
        $paramKec = '';
        if ($prov != null) {
            $paramProp = ' and pro.id=' . $prov;
        }
//        if(isset($request['kotaId']) && $request['kotaId']!=''&& $request['kotaId']!='undefined'){
//            $paramKota = ' and kot.id='.$request['kotaId'];
//        }
//        if(isset($request['kecamatanId']) && $request['kecamatanId']!='' && $request['kecamatanId']!='undefined'){
//            $paramKec = ' and kec.id='.$request['kecamatanId'];
//        }
        $data = DB::select(DB::raw("select * from (
                select count(x.kddiagnosa)as jumlah,x.kddiagnosa,x.namadiagnosa
                from (select dm.kddiagnosa,
                dm.namadiagnosa
                from antrianpasiendiperiksa_t as app
                left join diagnosapasien_t as dp on dp.noregistrasifk = app.norec
                left join detaildiagnosapasien_t as ddp on ddp.objectdiagnosapasienfk = dp.norec
                inner join diagnosa_m as dm on ddp.objectdiagnosafk = dm.id
                inner join pasiendaftar_t as pd on pd.norec = app.noregistrasifk
                inner join pasien_m as ps on ps.id = pd.nocmfk
                left join alamat_m as alm on alm.nocmfk = ps.id
                 left join kecamatan_m as kec on kec.id = alm.objectkecamatanfk
                left join kotakabupaten_m as kot on kot.id = alm.objectkotakabupatenfk
                left join propinsi_m as pro on pro.id = alm.objectpropinsifk
                left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                where app.kdprofile = $idProfile and dm.kddiagnosa <> '-'  and   pd.statusenabled=true and
                pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                $paramProp
                $paramKota
                 $paramKec

                )as x GROUP BY x.namadiagnosa ,x.kddiagnosa
                ) as z
                ORDER BY z.jumlah desc  limit 10

            "));
        if (count($data) > 0) {
            foreach ($data as $item) {
                $result[] = array(
                    'jumlah' => $item->jumlah,
                    'kd' => $item->kddiagnosa,
                    'kddiagnosa' => $item->kddiagnosa . '-' . $item->namadiagnosa,
                    'namadiagnosa' => $item->namadiagnosa,
                );
            }

        } else {
            $result[] = array(
                'jumlah' => 0,
                'kd' => null,
                'kddiagnosa' => null,
                'namadiagnosa' => null
            );
        }

        $results = array(
            'result' => $result,
            'month' => $bulan,
            'message' => 'ramdanegie',
        );
        return $result;
    }

    public static function getKunjunganRuanganRawatInap($tglAwal, $tglAkhir, $kdProfile)
    {

        $idProfile = (int)$kdProfile;
        $depInap = 16;
        $tglAwal = $tglAwal . ' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir = $tglAkhir . ' 23:59';
        $bulan = Carbon::now()->format('F');
        $idDepRanap = (int)static::settingDataFixed2('idDepRawatInap', $idProfile);
        $data = DB::select(DB::raw("SELECT
                        COUNT (z.kdruangan) AS jumlah,
                        z.namaruangan
                       FROM
                        (
                            select pd.noregistrasi, pd.tglregistrasi, ru.namaruangan, ru.id as kdruangan
                            from pasiendaftar_t as pd
                            --left join registrasipelayananpasien_t as rpp on rpp.noregistrasifk = pd.norec
                            left join ruangan_m as ru on ru.id = pd.objectruanganlastfk
                            where pd.kdprofile = $idProfile and ru.objectdepartemenfk = $idDepRanap
                            and pd.statusenabled = true
                            and (  pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir'
                           )
                            or pd.tglpulang is null
                            and pd.kdprofile = $idProfile and ru.objectdepartemenfk = $idDepRanap
                            and pd.statusenabled = true
                            group by pd.tglregistrasi, pd.noregistrasi, ru.namaruangan, ru.id

                        ) AS z
                    GROUP BY
                        z.namaruangan
            "));

        $result = array(
            'result' => $data,
            'month' => $bulan,
            'jml' => count($data),
            'message' => 'ramdanegie',
        );
        return $data;
    }

    public function getKecByProv(Request $r)
    {
        return DB::select(DB::raw("select kb.id as kbid,kb.namakotakabupaten,
            kk.id as kecid, kk.namakecamatan
             from kotakabupaten_m kb
            join kecamatan_m as kk on kk.objectkotakabupatenfk =kb.id
            where kb.objectpropinsifk in (select id from propinsi_m where kdmap='$r[kdmap]')
            order by kk.namakecamatan"));
    }

    public static function getKunjunganPerJenisPelayanan($tglAwal, $tglAkhir, $kdProfile)
    {

        $idProfile = (int)$kdProfile;
        $JenisPelayananReg = static::settingDataFixed2('KdJenisPelayananReg', $idProfile);
        $JenisPelayananEks = static::settingDataFixed2('KdJenisPelayananEks', $idProfile);
        $tglAwal = $tglAwal . ' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir = $tglAkhir . ' 23:59';
        $data = DB::select(DB::raw("
            SELECT dp.id,
            dp.namadepartemen,
            pd.norec as norec_pd,
            CASE WHEN jp.jenispelayanan IS NULL THEN 'REGULER' else jp.jenispelayanan end as jenispelayanan
            FROM
            pasiendaftar_t AS pd
            left JOIN jenispelayanan_m AS jp ON CAST (jp.id AS CHAR) = pd.jenispelayanan
            JOIN ruangan_m AS ru ON ru. ID = pd.objectruanganlastfk
            JOIN departemen_m AS dp ON dp. ID = ru.objectdepartemenfk
            WHERE pd.kdprofile = $idProfile and
            pd.tglregistrasi BETWEEN '$tglAwal'

            AND '$tglAkhir'
            and pd.statusenabled=true
        "));
        $data10 = [];
        $data20 = [];
        $reguler = 0;
        $eksekutif = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->id == $data10[$i]['id']) {
                        $sama = 1;
                        $jml = (float)$hideung['total'] + 1;
                        $data10[$i]['total'] = $jml;
                        if ($item->jenispelayanan == $JenisPelayananReg) {
                            $data10[$i]['reguler'] = (float)$hideung['reguler'] + 1;
                        }
                        if ($item->jenispelayanan == $JenisPelayananEks) {
                            $data10[$i]['eksekutif'] = (float)$hideung['eksekutif'] + 1;
                        }
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->jenispelayanan == $JenisPelayananReg) {
                        $reguler = 1;
                        $eksekutif = 0;

                    }
                    if ($item->jenispelayanan == $JenisPelayananEks) {
                        $reguler = 0;
                        $eksekutif = 1;
                    }

                    $data10[] = array(
                        'id' => $item->id,
                        'namadepartemen' => $item->namadepartemen,
                        'total' => 1,
                        'reguler' => $reguler,
                        'eksekutif' => $eksekutif


                    );

                }
                foreach ($data10 as $key => $row) {
                    $count[$key] = $row['id'];
                }
                array_multisort($count, SORT_DESC, $data10);
            }
        }
        $result = array(
            'data' => $data10,
            'jml' => count($data),
        );
        return $result;
    }

    public static function getTopTenAsalPerujukBPJS($tglAwal, $tglAkhir, $kdProfile)
    {

        $idProfile = (int)$kdProfile;
        $IdBPJS = 2;
        $tglAwal = $tglAwal . ' 00:00'; //Carbon::now()->startOfMonth()->subMonth(1);
        $tglAkhir = $tglAkhir . ' 23:59';
        $bulan = Carbon::now()->format('F');
        $data = DB::select(DB::raw("SELECT * FROM
            (
                SELECT COUNT (x.ppkrujukan) AS jumlah, x.ppkrujukan, x.kodeperujuk AS kodeppkrujukan
                FROM (SELECT pd.noregistrasi, CASE WHEN ap.kdprovider IS NULL THEN '-' ELSE ap.kdprovider
                END AS kodeperujuk,CASE WHEN ap.nmprovider IS NULL THEN '-' ELSE ap.nmprovider END AS ppkrujukan,
                pa.ppkrujukan AS kodepa
                FROM pasiendaftar_t AS pd
                LEFT JOIN pemakaianasuransi_t AS pa ON pa.noregistrasifk = pd.norec
                LEFT JOIN asuransipasien_m AS ap ON ap. ID = pa.objectasuransipasienfk
                WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal' AND '$tglAkhir'
                AND pd.objectkelompokpasienlastfk in (2,4,10)
                and   pd.statusenabled=true
                -- and ap.kdprovider  <> ''
                -- and ap.kdprovider  <> '-'
                --order by ap.kdprovider
                ) AS x GROUP BY x.ppkrujukan, x.kodeperujuk
            ) AS z
          ORDER BY
          z.jumlah DESC
         "));
        if (count($data) > 0) {
            $result = $data;
        } else {
            $result [] = array(
                'jumlah' => 0,
                'kodeppkrujukan' => null,
                'ppkrujukan' => null,
            );
        }

        $results = array(
            'result' => $result,
            'month' => $bulan,
            'message' => 'ramdanegie',
        );
        return $result;
    }

    public function getTempatTidurTerpakai($tglAwal, $tglAkhir, $kdProfile)
    {
        $idProfile = (int)$kdProfile;
        $idDepRanap = (int)$this->settingDataFixed('idDepRawatInap', $idProfile);
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $paramDep = '';
        $data = DB::select(DB::raw("SELECT
                pd.noregistrasi,
                pd.tglregistrasi,
                ru.namaruangan,
                jk.jeniskelamin,
                ps.objectjeniskelaminfk,
              date_part('year',age(ps.tgllahir))as umur,
                date_part('day',now()-ps.tgllahir)as hari
            FROM
                pasiendaftar_t AS pd
            LEFT JOIN pasien_m AS ps ON ps.id = pd.nocmfk
            LEFT JOIN jeniskelamin_m AS jk ON jk.id = ps.objectjeniskelaminfk
            LEFT JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk

            WHERE pd.kdprofile = $idProfile
                $paramDep
                 and pd.statusenabled = true
                and (pd.tglregistrasi < '$tglAwal' AND pd.tglpulang >= '$tglAkhir'
                )
                or pd.tglpulang is null and pd.kdprofile=$idProfile
                and pd.statusenabled = true
                $paramDep "));
        $L_balita = 0;
        $P_balita = 0;
        $L_anak = 0;
        $P_anak = 0;
        $L_remajaAwal = 0;
        $P_remajaAwal = 0;
        $L_remajaAkhir = 0;
        $P_remajaAkhir = 0;
        $L_dewasaAwal = 0;
        $P_dewasaAwal = 0;
        $L_dewasaAkhir = 0;
        $P_dewasaAkhir = 0;
        $L_lansiaAwal = 0;
        $P_lansiaAwal = 0;
        $L_lansiaakhir = 0;
        $P_lansiaakhir = 0;
        $L_manula = 0;
        $P_manula = 0;
        $jmlAll = 0;


        foreach ($data as $item) {
            $jmlAll = $jmlAll + 1;
            //DATA KEMENKES
            //1.Balita= 0 –5
            //2.Anak = 6 –11
            //3. Remaja Awal= 12 -16
            //4.Remaja Akhir= 17 –25
            //5.Dewasa Awal= 26 –35 .
            //6.Dewasa Akhir= 36 –45
            //7.Lansia Awal= 46 –55.
            //8.Lansia Akhir= 56 –65.
            //9.Manula= 65 –atas
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 0 && (float)$item->umur <= 5) {
                $L_balita = (float)$L_balita + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 0 && (float)$item->umur <= 5) {
                $P_balita = (float)$P_balita + 1;
            }
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 6 && (float)$item->umur <= 11) {
                $L_anak = (float)$L_anak + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 6 && (float)$item->umur <= 11) {
                $P_anak = (float)$P_anak + 1;
            }
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 12 && (float)$item->umur <= 16) {
                $L_remajaAwal = (float)$L_remajaAwal + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 12 && (float)$item->umur <= 16) {
                $P_remajaAwal = (float)$P_remajaAwal + 1;
            }
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 17 && (float)$item->umur <= 25) {
                $L_remajaAkhir = (float)$L_remajaAkhir + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 17 && (float)$item->umur <= 25) {
                $P_remajaAkhir = (float)$P_remajaAkhir + 1;
            }

            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 26 && (float)$item->umur <= 35) {
                $L_dewasaAwal = (float)$L_dewasaAwal + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 26 && (float)$item->umur <= 35) {
                $P_dewasaAwal = (float)$P_dewasaAwal + 1;
            }
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 36 && (float)$item->umur <= 45) {
                $L_dewasaAkhir = (float)$L_dewasaAkhir + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 36 && (float)$item->umur <= 45) {
                $P_dewasaAkhir = (float)$P_dewasaAkhir + 1;
            }

            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 46 && (float)$item->umur <= 55) {
                $L_lansiaAwal = (float)$L_lansiaAwal + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 46 && (float)$item->umur <= 55) {
                $P_lansiaAwal = (float)$P_lansiaAwal + 1;
            }
            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur >= 56 && (float)$item->umur <= 65) {
                $L_lansiaakhir = (float)$L_lansiaakhir + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur >= 56 && (float)$item->umur <= 65) {
                $P_lansiaakhir = (float)$P_lansiaakhir + 1;
            }

            if ($item->objectjeniskelaminfk == 1 && (float)$item->umur > 65) {
                $L_manula = (float)$L_manula + 1;
            }
            if ($item->objectjeniskelaminfk == 2 && (float)$item->umur > 65) {
                $P_manula = (float)$P_manula + 1;
            }

        }

        $resultData = array(
            'jumlah' => count($data),
            'L_balita' => $L_balita,
            'P_balita' => $P_balita,
            'L_anak' => $L_anak,
            'P_anak' => $P_anak,
            'L_remajaAwal' => $L_remajaAwal,
            'P_remajaAwal' => $P_remajaAwal,
            'L_remajaAkhir' => $L_remajaAkhir,
            'P_remajaAkhir' => $P_remajaAkhir,
            'L_dewasaAwal' => $L_dewasaAwal,
            'P_dewasaAwal' => $P_dewasaAwal,
            'L_dewasaAkhir' => $L_dewasaAkhir,
            'P_dewasaAkhir' => $P_dewasaAkhir,
            'L_lansiaAwal' => $L_lansiaAwal,
            'P_lansiaAwal' => $P_lansiaAwal,
            'L_lansiaakhir' => $L_lansiaakhir,
            'P_lansiaakhir' => $P_lansiaakhir,
            'L_manula' => $L_manula,
            'P_manula' => $P_manula,
            'all' => $jmlAll,

        );
        //DATA KEMENKES
        //1.Balita= 0 –5
        //2.Anak = 6 –11
        //3. Remaja Awal= 12 -16
        //4.Remaja Akhir= 17 –25
        //5.Dewasa Awal= 26 –35 .
        //6.Dewasa Akhir= 36 –45
        //7.Lansia Awal= 46 –55.
        //8.Lansia Akhir= 56 –65.
        //9.Manula= 65 –atas
        $result [] = ['name' => 'Balita Perempuan', 'umur' => '0 - 5 Tahun', 'jml' => $P_balita, 'img' => 'images/BayiPerempuan.png'];
        $result [] = ['name' => 'Balita Laki-laki', 'umur' => '0 - 5 Tahun', 'jml' => $L_balita, 'img' => 'images/BayiLaki-Laki.png'];
        $result [] = ['name' => 'Anak Perempuan', 'umur' => '6 - 11 Tahun', 'jml' => $P_anak, 'img' => 'images/AnakPerempuan.png'];
        $result [] = ['name' => 'Anak Laki-laki', 'umur' => '6 - 11 Tahun', 'jml' => $L_anak, 'img' => 'images/AnakLaki-Laki.png'];
        $result [] = ['name' => 'Remaja Perempuan', 'umur' => '12 - 25 Tahun', 'jml' => $P_remajaAwal + $P_remajaAkhir, 'img' => 'images/RemajaPerempuan.png'];
        $result [] = ['name' => 'Remaja Laki-laki', 'umur' => '12 - 25 Tahun', 'jml' => $L_remajaAwal + $L_remajaAkhir, 'img' => 'images/RemajaLaki-Laki.png'];
        $result [] = ['name' => 'Dewasa Perempuan', 'umur' => '26 - 45 Tahun', 'jml' => $P_dewasaAwal + $P_dewasaAkhir, 'img' => 'images/DewasaPerempuan.png'];
        $result [] = ['name' => 'Dewasa Laki-laki', 'umur' => '26 - 45 Tahun', 'jml' => $L_dewasaAwal + $L_dewasaAkhir, 'img' => 'images/DewasaLaki-Laki.png'];
        $result [] = ['name' => 'Lansia Perempuan', 'umur' => '46 - 65 Tahun', 'jml' => $P_lansiaAwal + $P_lansiaakhir, 'img' => 'images/Nenek2.png'];
        $result [] = ['name' => 'Lansia Laki-laki', 'umur' => '46 - 65 Tahun', 'jml' => $L_lansiaAwal + $L_lansiaakhir, 'img' => 'images/Kakek2.png'];
        $result [] = ['name' => 'Manula Perempuan', 'umur' => '> 65 Tahun', 'jml' => $P_manula, 'img' => 'images/Nenek.png'];
        $result [] = ['name' => 'Manula Laki-laki', 'umur' => '> 65 Tahun', 'jml' => $L_manula, 'img' => 'images/Kakek.png'];

//        dd($result);
        return $result;
    }

    public function getKunjunganRSPerJenisPasien($tglAwal, $tglAkhir, $kdProfile)
    {

        $idProfile = (int)$kdProfile;
        $kdKelompokPasienUmum = (int)$this->settingDataFixed('KdKelompokPasienUmum', $idProfile);
        $KelompokPasienBpjs = (int)$this->settingDataFixed('KdKelPasienBpjs', $idProfile);
        $KelompokPasienAsuransi = (int)$this->settingDataFixed('KdKelompokPasienAsuransi', $idProfile);
        $KdKelPasienPerusahaan = (int)$this->settingDataFixed('KdKelPasienPerusahaan', $idProfile);
        $KdKelPasienPerjanjian = (int)$this->settingDataFixed('KdKelPasienPerjanjian', $idProfile);
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $dataALL = DB::select(DB::raw("select x.kelompokpasien ,count(x.kelompokpasien) as jumlah from (
                select pd.noregistrasi,
                 kps.kelompokpasien,
                 pd.objectkelompokpasienlastfk,
               to_char (pd.tglregistrasi,'YYYY') as tahunregis
                 from pasiendaftar_t as pd
                 inner join kelompokpasien_m as kps on kps.id = pd.objectkelompokpasienlastfk
                left join batalregistrasi_t as br on br.pasiendaftarfk=pd.norec
                 --left join pemakaianasuransi_t as pa on pa.noregistrasifk=pd.norec
                 WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal'AND '$tglAkhir'
                and br.norec is null
                )as  x
                GROUP BY x.kelompokpasien"));


        $data = \DB::table('pasiendaftar_t as pd')
            ->join('kelompokpasien_m as kps', 'kps.id', '=', 'pd.objectkelompokpasienlastfk')
            ->join('ruangan_m as ru', 'ru.id', '=', 'pd.objectruanganlastfk')
            ->join('departemen_m as dp', 'dp.id', '=', 'ru.objectdepartemenfk')
            ->select('pd.noregistrasi', 'kps.kelompokpasien',
                'pd.objectkelompokpasienlastfk', 'dp.id',
                'dp.namadepartemen',
                'pd.norec as norec_pd',
                DB::raw("to_char (pd.tglregistrasi,'YYYY') as tahunregis"))
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.statusenabled', true)
            ->whereBetween('pd.tglregistrasi', [$tglAwal, $tglAkhir]);

        $data = $data->get();

        $data10 = [];
        $jmlBPJS = 0;
        $jmlAsuransiLain = 0;
        $jmlPerusahaan = 0;
        $jmlUmum = 0;
        $jmlPerjanjian = 0;
//         if (count($data) > 0) {
        foreach ($data as $item) {
            $sama = false;
            $i = 0;
            foreach ($data10 as $hideung) {
                if ($item->id == $data10[$i]['id']) {
                    $sama = 1;
                    $jml = (float)$hideung['total'] + 1;
                    $data10[$i]['total'] = $jml;
                    if ($item->objectkelompokpasienlastfk == $kdKelompokPasienUmum) {
                        $data10[$i]['jmlUmum'] = (float)$hideung['jmlUmum'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KelompokPasienBpjs) {
                        $data10[$i]['jmlBPJS'] = (float)$hideung['jmlBPJS'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KelompokPasienAsuransi) {
                        $data10[$i]['jmlAsuransiLain'] = (float)$hideung['jmlAsuransiLain'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KdKelPasienPerusahaan) {
                        $data10[$i]['jmlPerusahaan'] = (float)$hideung['jmlPerusahaan'] + 1;
                    }
                    if ($item->objectkelompokpasienlastfk == $KdKelPasienPerjanjian) {
                        $data10[$i]['jmlPerjanjian'] = (float)$hideung['jmlPerjanjian'] + 1;
                    }
                }
                $i = $i + 1;
            }
            if ($sama == false) {
                if ($item->objectkelompokpasienlastfk == $kdKelompokPasienUmum) {
                    $jmlBPJS = 0;
                    $jmlAsuransiLain = 0;
                    $jmlPerusahaan = 0;
                    $jmlUmum = 1;
                    $jmlPerjanjian = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KelompokPasienBpjs) {
                    $jmlBPJS = 1;
                    $jmlAsuransiLain = 0;
                    $jmlPerusahaan = 0;
                    $jmlUmum = 0;
                    $jmlPerjanjian = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KelompokPasienAsuransi) {
                    $jmlBPJS = 0;
                    $jmlAsuransiLain = 1;
                    $jmlPerusahaan = 0;
                    $jmlUmum = 0;
                    $jmlPerjanjian = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KdKelPasienPerusahaan) {
                    $jmlBPJS = 0;
                    $jmlAsuransiLain = 0;
                    $jmlPerusahaan = 1;
                    $jmlUmum = 0;
                    $jmlPerjanjian = 0;
                }
                if ($item->objectkelompokpasienlastfk == $KdKelPasienPerjanjian) {
                    $jmlBPJS = 0;
                    $jmlAsuransiLain = 0;
                    $jmlPerusahaan = 0;
                    $jmlUmum = 0;
                    $jmlPerjanjian = 1;
                }
                $data10[] = array(
                    'id' => $item->id,
                    'namadepartemen' => $item->namadepartemen,
                    'total' => 1,
                    'jmlBPJS' => $jmlBPJS,
                    'jmlAsuransiLain' => $jmlAsuransiLain,
                    'jmlPerusahaan' => $jmlPerusahaan,
                    'jmlUmum' => $jmlUmum,
                    'jmlPerjanjian' => $jmlPerjanjian,
                );
            }
            foreach ($data10 as $key => $row) {
                $count[$key] = $row['total'];
            }
            array_multisort($count, SORT_DESC, $data10);
        }

        $result = array(
            'dataAll' => $dataALL,
            'data' => $data10,
            'message' => 'ramdanegie',
        );
        return $result;
    }

    public function getPasienPerjenisPenjadwalan($tglAwal, $tglAkhir, $kdProfile)
    {
        $idProfile = (int)$kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $idDepRanap = (int)$this->settingDataFixed('idDepRawatInap', $idProfile);
        $idDepRehab = (int)$this->settingDataFixed('KdDepartemenInstalasiRehabilitasiMedik', $idProfile);
        $idDepBedahSentral = (int)$this->settingDataFixed('KdDeptBedahSentral', $idProfile);
        $idDepLaboratorium = (int)$this->settingDataFixed('KdDepartemenInstalasiLaboratorium', $idProfile);
        $idDepRadiologi = (int)$this->settingDataFixed('KdDepartemenInstalasiRadiologi', $idProfile);
        $idDepIGD = (int)$this->settingDataFixed('KdDepartemenInstalasiGawatDarurat', $idProfile);
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $data = DB::select(DB::raw("
                                   select count(x.noregistrasi) as jumlah  ,x.keterangan,
    count (case when x.departemen = 'rawat_jalan' then 1 end) AS rawatjalan,
count(case when x.departemen = 'igd' then 1 end) AS igd,
count(case when x.departemen = 'rawat_inap' then 1 end) AS rawat_inap,
count(case when x.departemen = 'radiologi' then 1 end) AS radiologi,
count(case when x.departemen = 'laboratorium' then 1 end) AS laboratorium,
count(case when x.departemen = 'operasi' then 1 end) AS operasi,
count(case when x.departemen = 'rahab_medik' then 1 end) AS rehab_medik
    from (
    SELECT
    case when apr.noreservasi is not null then 'Registrasi Online' else 'Loket Pendaftaran' end as keterangan,
            pd.noregistrasi,
    ru.namaruangan,pd.statusschedule,
    case when ru.objectdepartemenfk = $idDepRaJal  then 'rawat_jalan'
    when ru.objectdepartemenfk = $idDepIGD then 'igd'
    when ru.objectdepartemenfk = $idDepRanap   then 'rawat_inap'
    when ru.objectdepartemenfk = $idDepRadiologi  then 'radiologi'
    when ru.objectdepartemenfk = $idDepLaboratorium  then 'laboratorium'
    when ru.objectdepartemenfk = $idDepBedahSentral  then 'operasi'
    when ru.objectdepartemenfk = $idDepRehab  then 'rahab_medik'
    end as departemen
    FROM
    pasiendaftar_t AS pd
    left join antrianpasienregistrasi_t as apr on apr.noreservasi=pd.statusschedule
    inner JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
    WHERE pd.kdprofile = $idProfile and pd.tglregistrasi BETWEEN '$tglAwal'   AND '$tglAkhir'
    ) as x  group BY x.keterangan

            "));

        $res = array(
            'data' => $data
        );

        return $res;
    }

    public function getInfoKunjunganRawatJalanPerhari($tglAwal, $tglAkhir, $kdProfile)
    {
        $idProfile = (int)$kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $data = DB::select(DB::raw("

                    select * from (SELECT
                    pd.noregistrasi,
                    ps.namapasien,
                    br.norec AS norec_batal,
                    pd.nostruklastfk,
                    ru.namaruangan,
                    apd.tgldipanggilsuster,
                    pd.objectruanganlastfk AS kdruangan,row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum
                    FROM
                    pasiendaftar_t AS pd
                    INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                    INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                    inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                    LEFT JOIN batalregistrasi_t AS br ON br.pasiendaftarfk = pd.norec
                    WHERE pd.kdprofile = $idProfile and
                    pd.tglregistrasi BETWEEN '$tglAwal'
                    AND '$tglAkhir'
                    AND ru.objectdepartemenfk = $idDepRaJal) as x where x.rownum=1
                    ORDER BY
                    x.noregistrasi"));

        $data10 = [];
        $sudahPeriksa = 0;
        $belumPeriksa = 0;
        $batalRegis = 0;
        $totalAll = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->kdruangan == $data10[$i]['kdruangan']) {
                        $sama = 1;
                        $jml = (float)$hideung['total'] + 1;
                        $data10[$i]['total'] = $jml;
                        if ($item->tgldipanggilsuster != null && $item->norec_batal == null) {
//                         if ($item->statusantrian != '0' && $item->norec_batal == null) {
                            $data10[$i]['diperiksa'] = (float)$hideung['diperiksa'] + 1;

                        }
                        if ($item->tgldipanggilsuster == null && $item->norec_batal == null) {
//                        if ($item->statusantrian == '0' && $item->norec_batal == null) {
                            $data10[$i]['belumperiksa'] = (float)$hideung['belumperiksa'] + 1;
                        }
                        if ($item->norec_batal != null) {
                            $data10[$i]['batalregistrasi'] = (float)$hideung['batalregistrasi'] + 1;
                        }
                        //                    $data10[$i]['totalAll'] = $data10[$i]['diperiksa'] + $data10[$i]['belumperiksa'] + $data10[$i]['batalregistrasi'];
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->nostruklastfk != null && $item->norec_batal == null) {
                        $sudahPeriksa = 1;
                        $belumPeriksa = 0;
                        $batalRegis = 0;
                    }
                    if ($item->nostruklastfk == null && $item->norec_batal == null) {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 1;
                        $batalRegis = 0;
                    }
                    if ($item->norec_batal != null) {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 0;
                        $batalRegis = 1;
                    }
                    $data10[] = array(
                        'kdruangan' => $item->kdruangan,
                        'namaruangan' => $item->namaruangan,
                        'total' => 1,
                        'diperiksa' => $sudahPeriksa,
                        'belumperiksa' => $belumPeriksa,
                        'batalregistrasi' => $batalRegis,
//                        'count' => $totalAll,
                    );
                }
                foreach ($data10 as $key => $row) {
                    $count[$key] = $row['total'];
                }
                array_multisort($count, SORT_DESC, $data10);
            }
        } else {
            $data10[] = array(
                'kdruangan' => '-',
                'namaruangan' => 'Tidak Ada Data',
                'total' => 0,
                'diperiksa' => 0,
                'belumperiksa' => 0,
                'batalregistrasi' => 0,
            );
        }

        return $data10;
    }

    public function listGambar()
    {
        return ['images/icon-pasien.png', 'images/icon-pasien-emergency.png',
            'images/icon-pasien-rawat-inap.png', 'images/icon-radiologi.png', 'images/icon-laboratorium.png',
            'images/icon-pasien.png', 'images/icon-pasien-emergency.png',
            'images/icon-pasien-rawat-inap.png', 'images/icon-radiologi.png', 'images/icon-laboratorium.png',
            'images/icon-pasien.png', 'images/icon-pasien-emergency.png',
            'images/icon-pasien-rawat-inap.png', 'images/icon-radiologi.png', 'images/icon-laboratorium.png'];
    }

    public function listWarna()
    {
        return [
            'bg-aqua-gradient', 'bg-green-gradient', 'bg-blue-active', 'bg-yellow-gradient', 'bg-red-gradient',
            'bg-light-blue-gradient', 'bg-maroon-gradient', 'bg-purple-gradient', 'bg-teal-gradient',
            'bg-aqua-gradient', 'bg-green-gradient', 'bg-blue-active', 'bg-yellow-gradient', 'bg-red-gradient',
            'bg-light-blue-gradient', 'bg-maroon-gradient', 'bg-purple-gradient', 'bg-teal-gradient'];
    }

    public function getPengunjung($tglAwal, $tglAkhir, $kdProfile)
    {
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $dept = $this->settingDataFixed('kdDepartemenEIS', $kdProfile);
        $data = DB::select(DB::raw("SELECT dp.id ,dp.namadepartemen,count(pd.norec) as jumlah
                FROM departemen_m dp
                join ruangan_m as ru on ru.objectdepartemenfk=dp.id
                LEFT JOIN (SELECT pasiendaftar_t.norec,pasiendaftar_t.objectruanganlastfk
                FROM pasiendaftar_t
                where tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir' and pasiendaftar_t.statusenabled=true
                and pasiendaftar_t.kdprofile=$kdProfile) pd ON (ru.id= pd.objectruanganlastfk)
                WHERE dp.id in ($dept)
                and dp.kdprofile =$kdProfile
                and dp.statusenabled =true
                group by dp.namadepartemen,dp.id,dp.qdepartemen
                order by dp.qdepartemen asc
        "));
        return $data;
    }

    public function getKunjungan($tglAwal, $tglAkhir, $kdProfile)
    {
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $idDepRanap = (int)$this->settingDataFixed('idDepRawatInap', $kdProfile);
        $dept = $this->settingDataFixed('kdDepartemenRawatJalanFix', $kdProfile);
        $data = DB::select(DB::raw("SELECT dp.id ,dp.namadepartemen,count(pd.norec) as jumlah, dp.qdepartemen
                FROM departemen_m dp
                join ruangan_m as ru on ru.objectdepartemenfk=dp.id
                LEFT JOIN (SELECT antrianpasiendiperiksa_t.norec,objectruanganfk FROM antrianpasiendiperiksa_t
                where tglregistrasi BETWEEN '$tglAwal' and '$tglAkhir'
                and   antrianpasiendiperiksa_t.statusenabled=true
                and antrianpasiendiperiksa_t.kdprofile=$kdProfile)
                 pd ON (ru.id= pd.objectruanganfk)
                WHERE dp.id in ($dept)
                and dp.kdprofile =$kdProfile
                group by dp.namadepartemen,dp.id,dp.qdepartemen
                order by dp.qdepartemen asc
        "));
        $dataRanap = DB::select(DB::raw("select count(x.noregistrasi) as jumlah
            from ( select  pd.noregistrasi,pd.tglregistrasi
            from pasiendaftar_t as pd
            inner join ruangan_m as ru on ru.id = pd.objectruanganlastfk
            where ru.objectdepartemenfk = $idDepRanap
            and pd.statusenabled = true
            and pd.kdprofile=$kdProfile
            and (  pd.tglregistrasi < '$tglAwal'
                AND pd.tglpulang >= '$tglAkhir'
             ) or pd.tglpulang is null
               and pd.statusenabled = true
            and pd.kdprofile=$kdProfile

         ) as x
            "));
        $dataFarmasi = DB::select(DB::raw("
             SELECT
                COUNT (x.noresep) AS jumlah
              FROM
                (
                    SELECT *
                    FROM
                        strukresep_t AS sr
                    WHERE
                        sr.tglresep BETWEEN '$tglAwal'
                    AND '$tglAkhir'
                     and (sr.statusenabled is null or sr.statusenabled = true)
                     and sr.kdprofile=$kdProfile
                ) AS x"));
        $farmasi = 0;
        $masihDirawat = 0;
        if (count($dataFarmasi) > 0) {
            $farmasi = $dataFarmasi[0]->jumlah;
        }
        if (count($dataRanap) > 0) {
            $masihDirawat = $dataRanap[0]->jumlah;
        }
        $data10 = [];
        foreach ($data as $key => $value) {
            $data10 [] = array('id' => $value->id,
                'namadepartemen' => $value->namadepartemen,
                'jumlah' => $value->jumlah,
                'qdepartemen' => $value->qdepartemen);

            # code...
        }
        $data10 [] = array(
            'id' => 16,
            'namadepartemen' => 'Instalasi Rawat Inap',
            'jumlah' => $masihDirawat,
            'qdepartemen' => 3
        );
        $data10 [] = array(
            'id' => 14,
            'namadepartemen' => 'Instalasi Farmasi',
            'jumlah' => $farmasi,
            'qdepartemen' => 13
        );
        if (count($data10) > 0) {
            foreach ($data10 as $key => $row) {
                $count[$key] = $row['qdepartemen'];
            }
            array_multisort($count, SORT_ASC, $data10);
        }

        return $data10;
    }

    public function getTrendKunjunganPasienRajal($tglAwal, $tglAkhir, $kdProfile)
    {
        $tglAwal = $tglAwal . ' 00:00';
        $tglAkhir = $tglAkhir . ' 23:59';
        $idProfile = (int)$kdProfile;
        $idDepRaJal = (int)$this->settingDataFixed('KdDepartemenRawatJalan', $idProfile);
        $tglAwal = Carbon::now()->subWeek(3);//  Carbon::now()->subMonth(1);
        $tglAkhir = Carbon::now()->format('Y-m-d 23:59');
        $currentDate = Carbon::now();
        $last2week = $currentDate->subWeek();

        $data = DB::select(DB::raw("
                select * from (
                SELECT
                    pd.norec,
                    pd.noregistrasi,
                    to_char (
                        pd.tglregistrasi,
                       'dd, Mon YYYY'
                    ) AS tglregistrasi,
                    to_char (pd.tglregistrasi, 'dd. Mon') AS tanggal,
                    pd.tglregistrasi AS tgl,
                    CASE
                WHEN  apd.tgldipanggilsuster IS NOT NULL  and br.norec is  null THEN
                    'sudahdiperiksa'
                WHEN  apd.tgldipanggilsuster IS NULL  and br.norec is  null THEN
                    'belumdiperiksa'
                WHEN br.norec is not null THEN
                    'batalregis'
                END AS keterangan,
                 ps.namapasien
                ,
                row_number() over (partition by pd.noregistrasi order by apd.tglmasuk desc) as rownum
                FROM
                    pasiendaftar_t AS pd
                inner join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
                INNER JOIN ruangan_m AS ru ON ru.id = pd.objectruanganlastfk
                INNER JOIN pasien_m AS ps ON ps.id = pd.nocmfk
                 left join batalregistrasi_t as br on br.pasiendaftarfk = pd.norec
                WHERE pd.kdprofile = $idProfile and
                    pd.tglregistrasi BETWEEN  '$tglAwal'
                AND '$tglAkhir'
                AND ru.objectdepartemenfk = $idDepRaJal
                ) as x where x.rownum=1
                ORDER BY
                x.noregistrasi
        "));
        $data10 = [];
        $sudahPeriksa = 0;
        $belumPeriksa = 0;
        $batalRegis = 0;
        $totalAll = 0;
        if (count($data) > 0) {
            foreach ($data as $item) {
                $sama = false;
                $i = 0;
                foreach ($data10 as $hideung) {
                    if ($item->tglregistrasi == $data10[$i]['tglregistrasi']) {
                        $sama = 1;
                        $jml = (float)$hideung['totalterdaftar'] + 1;
                        $data10[$i]['totalterdaftar'] = $jml;
                        if ($item->keterangan == 'sudahdiperiksa') {
                            $data10[$i]['diperiksa'] = (float)$hideung['diperiksa'] + 1;
                        }
                        if ($item->keterangan == 'belumdiperiksa') {
                            $data10[$i]['belumperiksa'] = (float)$hideung['belumperiksa'] + 1;
                        }
                        if ($item->keterangan == 'batalregis') {
                            $data10[$i]['batalregistrasi'] = (float)$hideung['batalregistrasi'] + 1;
                        }
                    }
                    $i = $i + 1;
                }
                if ($sama == false) {
                    if ($item->keterangan == 'sudahdiperiksa') {
//                    if ($item->nostruklastfk != null && $item->norec_batal == null) {
                        $sudahPeriksa = 1;
                        $belumPeriksa = 0;
                        $batalRegis = 0;
                    }
//                    if ($item->nostruklastfk == null && $item->norec_batal == null) {
                    if ($item->keterangan == 'belumdiperiksa') {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 1;
                        $batalRegis = 0;
                    }
//                    if ($item->norec_batal != null) {
                    if ($item->keterangan == 'batalregis') {
                        $sudahPeriksa = 0;
                        $belumPeriksa = 0;
                        $batalRegis = 1;
                    }
                    $data10[] = array(
                        'tglregistrasi' => $item->tglregistrasi,
                        'tanggal' => $item->tanggal,
                        'totalterdaftar' => 1,
                        'diperiksa' => $sudahPeriksa,
                        'belumperiksa' => $belumPeriksa,
                        'batalregistrasi' => $batalRegis,

                    );
                }
                // foreach ($data10 as $key => $row) {
                //     $count[$key] = $row['totalterdaftar'];
                // }
                // array_multisort($count, SORT_DESC, $data10);
            }
        }
        return $data10;

    }

    public function getTotalKlaim($noregistrasi, $kdProfile)
    {
        $pelayanan = collect(\DB::select("select sum(x.totalppenjamin) as totalklaim
         from (select spp.norec,spp.totalppenjamin
         from pasiendaftar_t as pd
            join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
            join pelayananpasien_t as pp on pp.noregistrasifk =apd.norec
            join strukpelayanan_t as sp on sp.norec= pp.strukfk
            join strukpelayananpenjamin_t as spp on spp.nostrukfk=sp.norec
            where pd.noregistrasi ='$noregistrasi'
        and spp.statusenabled is null
        and pd.kdprofile=$kdProfile
        GROUP BY spp.norec,spp.totalppenjamin

        ) as x"))->first();
        if (!empty($pelayanan) && $pelayanan->totalklaim != null) {
            return (float)$pelayanan->totalklaim;
        } else {
            return 0;
        }


    }

    public function getTotolBayar($noregistrasi, $kdProfile)
    {
        $pelayanan = collect(\DB::select("select sum(x.totaldibayar) as totaldibayar
         from (select sbm.norec,sbm.totaldibayar
         from pasiendaftar_t as pd
        join antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
        join pelayananpasien_t as pp on pp.noregistrasifk =apd.norec
        join strukpelayanan_t as sp on sp.norec= pp.strukfk
        join strukbuktipenerimaan_t as sbm on sbm.nostrukfk = sp.norec
        where pd.noregistrasi ='$noregistrasi'
        and sp.statusenabled is null
        and sbm.statusenabled =true
        and pd.kdprofile=$kdProfile
        GROUP BY sbm.norec,sbm.totaldibayar

        ) as x"))->first();
        if (!empty($pelayanan) && $pelayanan->totaldibayar != null) {
            return (float)$pelayanan->totaldibayar;
        } else {
            return 0;
        }


    }

    protected function getDepositPasien($noregistrasi)
    {
        $produkIdDeposit = $this->getProdukIdDeposit();
        $deposit = 0;
        $pasienDaftar = PasienDaftar::has('pelayanan_pasien')->where('noregistrasi', $noregistrasi)->first();
        if ($pasienDaftar) {
            $depositList = $pasienDaftar->pelayanan_pasien()->where('nilainormal', '-1')->whereNull('strukfk')->get();
            foreach ($depositList as $item) {
                if ($item->produkfk == $produkIdDeposit) {
                    $deposit = $deposit + $item->hargasatuan;
                }
            }
        }
        return $deposit;
    }

    public function getDataDashboard($r)
    {
        $colors = $this->getColor();
        $tglakhir = date('Y-m-d');
        $tglawal = Carbon::now()->subWeek(1)->format('Y-m-d');
        if (isset($r['tglawal'])) {
            $tglawal = $r['tglawal'];
        }
        if (isset($r['tglakhir'])) {
            $tglakhir = $r['tglakhir'];
        }


        $data = \DB::select(DB::raw("select * from (select count(x.kddiagnosa) as jumlah,x.kddiagnosa,x.namadiagnosa
                from (
                select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
                prov.provinsi,kot.kotakabupaten
                from pelayananmedis_t as pm
                inner join pasien_m as ps on pm.pasienfk= ps.id
                inner join alamat_m as al on ps.id = al.pasienfk
                inner join provinsi_m as prov on prov.id = al.provinsifk
                inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
                where pm.kddiagnosa is not null
                and pm.tglregistrasi between '$tglawal 00:00' and '$tglakhir 23:59'

                ) as x GROUP BY x.namadiagnosa) as z
                order by z.jumlah desc limit 10"));
        $map = [];
        foreach ($data as $key => $value) {
            $data[$key]->color = $colors[$key];
            # code...
        }
        // dd($data);
//        $umur = \DB::select(\DB::raw("select count(x.rangeumur) as jumlah,x.rangeumur from (
//            select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
//            prov.provinsi,kot.kotakabupaten,ps.tgllahir,
//            case when TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <= 1 then 'Bayi : < 1 tahun'
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) >= 2 and TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <=5 then 'Balita : >=2 & <=5 Tahun '
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) > 5 and TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <=12 then 'Anak : > 5 & <=12 Tahun'
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) > 12 and TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) <=50 then 'Dewasa : >12 & <=50 Tahun'
//            when  TIMESTAMPDIFF(YEAR, ps.tgllahir, CURDATE()) > 50  then 'Geriatri : >50 Tahun'  end as rangeumur
//
//            from pelayananmedis_t as pm
//            inner join pasien_m as ps on pm.pasienfk= ps.id
//            inner join alamat_m as al on ps.id = al.pasienfk
//            inner join provinsi_m as prov on prov.id = al.provinsifk
//            inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
//            where pm.kddiagnosa is not null
//           /*
//            and DATE_FORMAT(pm.tglregistrasi, '%Y-%m')='$now'
//            */
//            ) as x
//            group by x.rangeumur"));
        $kddiagnosa = '';
        if (count($data) > 0) {
            $kddiagnosa = $data[0]->kddiagnosa;
            $map = \DB::select(DB::raw("
					select * from (select count(x.provinsi) as jumlah,x.provinsi,x.kdmap
					from (
					select pm.kddiagnosa,pm.namadiagnosa,ps.namapasien,al.alamatlengkap,
					prov.provinsi,kot.kotakabupaten,prov.kdmap
					from pelayananmedis_t as pm
					inner join pasien_m as ps on pm.pasienfk= ps.id
					inner join alamat_m as al on ps.id = al.pasienfk
					inner join provinsi_m as prov on prov.id = al.provinsifk
					inner join kotakabupaten_m as kot on kot.id = al.kotakabupatenfk
					where pm.kddiagnosa is not null
					and pm.kddiagnosa ='$kddiagnosa'

					) as x GROUP BY x.provinsi,x.kdmap) as z
					order by z.jumlah desc "));
        }

        $result['listdiagnosa'] = $data;
        $result['map'] = $map;
        $result['kddiagnosa'] = $kddiagnosa;
        $result['umur'] = [];//$umur;
        $result['tglawal'] = $tglawal;
        $result['tglakhir'] = $tglakhir;
        return $result;
//        return view('dashboard.pelayanan', compact('data', 'map', 'kddiagnosa', 'umur'));

    }

    public static function getColor()
    {
        $colors = [
            "#FF6384", "#4BC0C0", "#FFCE56",
            "#ffff9c", "#36A2EB", '#7cb5ec', '#75b2a3', '#9ebfcc', '#acdda8', '#d7f4d2', '#ccf2e8',
            '#468499', '#088da5', '#00ced1', '#3399ff', '#00ff7f',
            '#b4eeb4', '#a0db8e', '#999999', '#6897bb', '#0099cc', '#3b5998',
            '#000080', '#191970', '#8a2be2', '#31698a', '#87ff8a', '#49e334',
            '#13ec30', '#7faf7a', '#408055', '#09790e'
        ];
        return $colors;
    }

    public function showDataPegawai(Request $r)
    {
        $listJk = DB::table('jeniskelamin_m')
            ->select('*')
            ->where('statusenabled', true)
            ->get();
        $listJP = DB::table('jenispegawai_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('jenispegawai')
            ->get();
        $listPangkat = DB::table('pangkat_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('pangkat')
            ->get();
        $listPdd = DB::table('pendidikan_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('pendidikan')
            ->get();
        $listJB = DB::table('jabatan_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('jabatan')
            ->get();

        if (isset($r->id) && $r->id != null) {
            $valueEdit = DB::table('pegawai_m as pg')
                ->leftJoin('jeniskelamin_m as jk', 'jk.id', 'pg.objectjeniskelaminfk')
                ->leftJoin('jabatan_m as jb', 'jb.id', 'pg.objectjabatanfk')
                ->leftJoin('pendidikan_m as pdd', 'pdd.id', 'pg.objectpendidikanfk')
                ->leftJoin('pangkat_m as pn', 'pn.id', 'pg.objectpangkatfk')
                ->leftJoin('jenispegawai_m as jp', 'jp.id', 'pg.objectjenispegawaifk')
                ->select('pg.*', 'jk.jeniskelamin', 'jb.jabatan', 'pdd.pendidikan', 'pn.pangkat', 'jp.jenispegawai')
                ->where('pg.id', $r->id)
                ->first();
//            dd($valueEdit);
            return view('module.user.pegawai.assets.add-pegawai', compact('valueEdit',
                'listJk', 'listJB', 'listPdd', 'listPangkat', 'listJP'));
        } else {
            return view('module.user.pegawai.assets.add-pegawai', compact('r',
                'listJk', 'listJB', 'listPdd', 'listPangkat', 'listJP'));
        }
    }

    public function hapusPegawai(Request $r)
    {
        DB::beginTransaction();
        try {

            Pegawai::where('id', $r['id'])->delete();
            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal dihapus');
        }

        return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "pegawai"]);

    }

    public function savePegawai(Request $r)
    {
        DB::beginTransaction();
        try {
//            dd($r->input());
            $profile = DB::table('user_m')->where('id', $_SESSION['id'])->first();
//            dd($profile);
            $data = $r->input();
            if ($data['id'] == null) {
                $saveData = new Pegawai();
                $saveData->id = Pegawai::max('id') + 1;
                $saveData->statusenabled = true;
            } else {
                $saveData = Pegawai::where('id', $data['id'])->first();
            }
            $saveData->profilefk = $profile->profilefk;
            $saveData->namalengkap = $data['namalengkap'];
            $saveData->objectjeniskelaminfk = $data['jk'];
            $saveData->tgllahir = $data['tgllahir'];
            $saveData->tempatlahir = $data['tempatlahir'];
            $saveData->nip = $data['nip'];
            $saveData->objectpendidikanfk = $data['pendidikan'];
            $saveData->objectjabatanfk = $data['jabatan'];
            $saveData->objectpangkatfk = $data['pangkat'];
            $saveData->tglmasuk = $data['tglmasuk'];
            $saveData->tglkeluar = $data['tglkeluar'];
            $saveData->objectjenispegawaifk = $data['jenispegawai'];
            $saveData->tglupdate = date('Y-m-d H:i:s');
            $saveData->save();

            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal disimpan');
        }

        return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "pegawai"]);

    }

    public function showDataBed(Request $r)
    {

        $listKelas = DB::table('kelas_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('namakelas')
            ->get();

        if (isset($r['id']) && $r['id'] != null) {
            $valueEdit = DB::table('ketersediaantempattidur_t as pg')
                ->join('kelas_m as jk', 'jk.id', 'pg.objectkelasfk')
                ->Join('profile_m as pr', 'pr.id', 'pg.profilefk')
                ->select('pg.*', 'jk.namakelas', 'pr.namaprofile')
                ->where('pg.norec', $r['id'])
                ->first();

//            dd($valueEdit);
            return view('module.user.bed.assets.add-bed', compact('valueEdit',
                'listKelas'));
        } else {
            return view('module.user.bed.assets.add-bed', compact('listKelas'));
        }
    }

    public function hapusBed(Request $r)
    {
        DB::beginTransaction();
        try {

            DB::table('ketersediaantempattidur_t')->where('norec', $r['id'])->delete();
//            session()->flash('message',"Incorrect username or password");
            toastr()->success('Data Delete Succesfully !', 'Info');
            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal dihapus');
        }

        return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "bed"]);

    }

    public function saveBed(Request $r)
    {
        DB::beginTransaction();
        try {
//            dd($r->input());
            $profile = DB::table('user_m')->where('id', $_SESSION['id'])->first();
//            dd($profile);
            $data = $r->input();

            if ($data['id'] == null) {
                $cekData = DB::table('ketersediaantempattidur_t')
                    ->where('objectkelasfk', $data['kelas'])
                    ->where('profilefk', $profile->profilefk)
                    ->first();
                if (empty($cekData)) {
//                    $max =  DB::table('ketersediaantempattidur_t')->max('id');
                    DB::table('ketersediaantempattidur_t')->insert([
                        "norec" => substr(Uuid::generate(), 0, 32),
                        "statusenabled" => true,
                        "objectkelasfk" => $data['kelas'],
                        "profilefk" => $profile->profilefk,
                        "kapasitas" => $data['kapasitas'],
                        "tersedia" => $data['tersedia'],
                        "tglupdate" => date('Y-m-d H:i:s')
                    ]);
                } else {
                    DB::table('ketersediaantempattidur_t')->where('norec', $cekData->norec)
                        ->update([
                            "objectkelasfk" => $data['kelas'],
                            "profilefk" => $profile->profilefk,
                            "kapasitas" => $data['kapasitas'],
                            "tersedia" => $data['tersedia'],
                            "tglupdate" => date('Y-m-d H:i:s')
                        ]);
                }
            } else {
                DB::table('ketersediaantempattidur_t')
                    ->where('norec', $data['id'])
                    ->update([
                        "objectkelasfk" => $data['kelas'],
                        "profilefk" => $profile->profilefk,
                        "kapasitas" => $data['kapasitas'],
                        "tersedia" => $data['tersedia'],
                        "tglupdate" => date('Y-m-d H:i:s')
                    ]);
            }
            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal disimpan');
        }

        return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "bed"]);

    }

    public function showDataStok(Request $r)
    {

        $listProduk = DB::table('produk_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('namaproduk')
            ->get();
        $listSatuan = DB::table('satuanstandar_m')
            ->select('*')
            ->where('statusenabled', true)
            ->orderBy('satuanstandar')
            ->get();

        if (isset($r['norec']) && $r['norec'] != null) {
            $valueEdit = DB::table('transaksistok_t as pg')
                ->join('satuanstandar_m as jk', 'jk.id', 'pg.satuanstandarfk')
                ->Join('produk_m as prd', 'prd.id', 'pg.produkfk')
                ->Join('profile_m as pr', 'pr.id', 'pg.profilefk')
                ->select('pg.*', 'jk.satuanstandar', 'prd.namaproduk', 'pr.namaprofile')
                ->where('pg.norec', $r['norec'])
                ->first();

//            dd($valueEdit);
            return view('module.user.stok.assets.add-stok', compact('valueEdit',
                'listSatuan', 'listProduk'));
        } else {
            return view('module.user.stok.assets.add-stok', compact('listSatuan', 'listProduk'));
        }
    }

    public function hapusStok(Request $r)
    {
        DB::beginTransaction();
        try {

            DB::table('transaksistok_t')->where('norec', $r['norec'])->delete();
//            session()->flash('message',"Incorrect username or password");
            toastr()->success('Data Delete Succesfully !', 'Info');
            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal dihapus');
        }

        return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "stok"]);

    }

    public function saveStok(Request $r)
    {
        DB::beginTransaction();
        try {
//            dd($r->input());
            $profile = DB::table('user_m')->where('id', $_SESSION['id'])->first();
//            dd($profile);
            $data = $r->input();

            if ($data['norec'] == null) {
                $cekData = DB::table('transaksistok_t')
                    ->where('satuanstandarfk', $data['satuanstandar'])
                    ->where('produkfk', $data['produk'])
                    ->where('profilefk', $profile->profilefk)
                    ->first();
                if (empty($cekData)) {
                    DB::table('transaksistok_t')->insert([
                        "norec" => substr(Uuid::generate(), 0, 32),
                        "statusenabled" => true,
                        "produkfk" => $data['produk'],
                        "profilefk" => $profile->profilefk,
                        "satuanstandarfk" => $data['satuanstandar'],
                        "total" => $data['total'],
                        "tglupdate" => date('Y-m-d H:i:s')
                    ]);
                } else {
                    DB::table('transaksistok_t')->where('norec', $cekData->norec)
                        ->update([
                            "produkfk" => $data['produk'],
                            "profilefk" => $profile->profilefk,
                            "satuanstandarfk" => $data['satuanstandar'],
                            "total" => $data['total'],
                            "tglupdate" => date('Y-m-d H:i:s')
                        ]);
                }
            } else {
                DB::table('transaksistok_t')
                    ->where('norec', $data['norec'])
                    ->update([
                        "produkfk" => $data['produk'],
                        "profilefk" => $profile->profilefk,
                        "satuanstandarfk" => $data['satuanstandar'],
                        "total" => $data['total'],
                        "tglupdate" => date('Y-m-d H:i:s')
                    ]);
            }
            DB::commit();
            session()->flash('type', "success");
            session()->flash('message', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('type', "error");
            session()->flash('message', 'Data gagal disimpan');
        }

        return redirect()->route("show_page", ["role" => $_SESSION["role"], "pages" => "stok"]);

    }

    public function saveECG(Request $r)
    {
        $req = $r->getContent();
        $req = str_replace('[ECG SERVER V1.0]', '', $req);
        $req = str_replace('as@epic=##$ECG', '', $req);
        $req = str_replace('~', '', $req);

        $xml = simplexml_load_string($req, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);
        $uid = substr(Uuid::generate(), 0, 6);
        $save = [];
        $fdata = array_values($array['Data']);
        $i = 1;
        for ($x = 0; $x < count($fdata); $x++) {
            $ket = str_replace('xmlECG', '', array_keys($array['Data'])[$x]);
            if ($ket != 'xmlHeader' && $ket != 'Data') {
                if ($ket == 'date') {
                    $ket = 'ecgDate';
                }
                if ($ket == 'Time') {
                    $ket = 'ecgTime';
                }
                $save[] = array(
                    'norec' => date('YmdHis') . $uid . $array['Data']['xmlECGCustomerID'],
                    'kunci' => $ket,
                    'nilai' => $fdata[$x],
                    'urut' => $i,
                    'customerid' => $array['Data']['xmlECGCustomerID'],
                    'datesend' => $array['Data']['xmlECGdate'] . ' ' . $array['Data']['xmlECGTime']
                );
                $i++;
            }
        }
        $save[] = array(
            'norec' => date('YmdHis') . $uid . $array['Data']['xmlECGCustomerID'],
            'kunci' => 'expertise',
            'nilai' => '',
            'urut' => 10,
            'customerid' => $array['Data']['xmlECGCustomerID'],
            'datesend' => $array['Data']['xmlECGdate'] . ' ' . $array['Data']['xmlECGTime']
        );

        $frame = array_values($array['Data']['xmlECGData']);
        for ($x = 0; $x < count($frame); $x++) {
            $save[] = array(
                'norec' => date('YmdHis') . $uid . $array['Data']['xmlECGCustomerID'],
                'kunci' => 'xmlframe',
                'nilai' => $frame[$x],
                'urut' => 20 + $x,
                'customerid' => $array['Data']['xmlECGCustomerID'],
                'datesend' => $array['Data']['xmlECGdate'] . ' ' . $array['Data']['xmlECGTime']
            );
        }
//        DB::beginTransaction();
//        try{
//        return $save;
        foreach ($save as $s) {
            $ecg = new EECG();
            $ecg->norec = $s['norec'];
            //        $ecg->kdprofile = $array;
            //        $ecg->statusenabled = $array;
            //        $ecg->kodeexternal = $array;
            //        $ecg->namaexternal = $array;
            $ecg->reportdisplay = 'laravel';
            $ecg->kunci = $s['kunci'];
            $ecg->nilai = $s['nilai'];
            $ecg->urut = $s['urut'];
            $ecg->customerid = $s['customerid'];
//                $ecg->dateverif = $array;
            $ecg->datesend = $s['datesend'];
            $ecg->save();
        }
//            DB::commit();
        $result = array("response" => 'ECG',
            "metadata" =>
                array(
                    "code" => "200",
                    "message" => "Sukses")
        );
        return response()->json($result);

//        } catch (\Exception $e) {
//            DB::rollBack();
//            return response()->json($e);
//        }
    }

    public function tesPost(Request $request)
    {
        $mg['message'] = 'Sukses';
        return $this->setStatusCode('200')->respond($mg);
    }
}
