<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//if (isset($_SESSION)) {
    session_start();
//}

Route::get('/', "Auth\AuthController@show")->name("login");
Route::post('/login-web', "Auth\AuthController@loginKeun")->name("login_validation");
Route::get('/logout', "Auth\AuthController@logoutKeun")->name("logout");

Route::group(["middleware" => "login_check"], function () {

    Route::get('/get-dashboard-pegawai', 'DashboardController@getDashboardPegawai');
    Route::get('/get-dashboard-persediaan', 'DashboardController@getDashboardPersediaan');
    Route::get('/get-dashboard-persediaan-stok', 'DashboardController@getDashboardPersediaanStok');
    Route::get('/get-dashboard-kamar', 'DashboardController@getKetersediaanKamar');
    Route::get('/get-detail-covid-pasien', 'DashboardController@getDetailCovid');
    Route::get('/get-detail-kunjungan-pasien', 'DashboardController@getDetailKun');
    Route::get('/get-detail-bed', 'DashboardController@getDetailBed');
    Route::get('/get-kec-by-prov', 'MainController@getKecByProv');
    Route::get('/get-top-diagnosa-by-kec', 'MainController@getTopDiagByKec');
    Route::get('/get-detail-kunjungan-pasien', 'MainController@getDetailKun');
    Route::get('/get-detail-pendapatan', 'MainController@getDetailPendapatan');
    Route::get('/get-detail-pegawai', 'MainController@getDetailPegawai');
    Route::get('/get-count-pegawai', 'MainController@getCountPegawai');

    Route::group(['prefix' => 'service/transmedic'], function () {
        Route::get('get-profile-login', function () {
            $profile = \App\Web\Profile::where('statusenabled', true)->first();
            return array('as' => 'ea&er@epic', 'profile' => $profile->login);
        });

        Route::group(['prefix' => 'auth'], function () {
            Route::post('sign-in', 'Auth\LoginController@loginUser');
            Route::post('sign-out', 'Auth\LoginController@signOut');
            Route::post('sign-in-andro', 'Auth\LoginController@loginAndro');
            Route::post('ubah-password', 'Auth\LoginController@ubahPassword');
        });
        // Route::group(['prefix' => 'akuntansi'], function () {
        /*GET*/
        Route::get('akuntansi/get-data-combo-coa', 'Akuntansi\AkuntansiController@getDataCoa');
        Route::get('akuntansi/get-data-buku-besar-rev2/', 'Akuntansi\AkuntansiController@getDataBukuBesarRev2');
        Route::get('akuntansi/get-data-detail-buku-besar', 'Akuntansi\AkuntansiController@getDetailJurnalRev2018BukuBesar');
        Route::get('akuntansi/get-data-daftar-saldo-awal', 'Akuntansi\AkuntansiController@getDaftarSaldoAwal');
        Route::get('akuntansi/get-data-buku-besar-pembantu/', 'Akuntansi\AkuntansiController@getDataBukuBesarPembantu');
        Route::get('akuntansi/get-data-trial-balance', 'Akuntansi\AkuntansiController@getDataTrialBalance');
        Route::get('akuntansi/get-data-aruskas', 'Akuntansi\AkuntansiController@getDataArusKas');
        Route::get('akuntansi/get-data-combo-master', 'Akuntansi\AkuntansiController@getDataComboMasterAkun');
        Route::get('akuntansi/get-data-piutang-pasien', 'Akuntansi\AkuntansiController@getDataPiutangPasien');
        Route::get('akuntansi/get-data-daftar-master-coa', 'Akuntansi\AkuntansiController@getDaftarCoa');
        Route::get('akuntansi/get-data-detail-jurnal', 'Akuntansi\AkuntansiController@getDetailJurnalRev2018'); //done
        Route::get('akuntansi/get-data-detail-jurnal-posting', 'Akuntansi\AkuntansiController@getDetailJurnalPosting'); //done
        Route::get('akuntansi/get-data-combo-coa-part', 'Akuntansi\AkuntansiController@getCoaSaeutik');
        Route::get('akuntansi/get-data-jurnal-umum-2018', 'Akuntansi\AkuntansiController@getDataJurnalUmumRev2019'); //done
        Route::get('akuntansi/get-data-sal', 'Akuntansi\AkuntansiController@getDataSal'); //done
        /*END GET*/

        /*POST*/
        Route::post('akuntansi/save-data-saldo-awal', 'Akuntansi\AkuntansiController@SaveSaldoAwal');
        Route::post('akuntansi/save-hapus-saldo-awal', 'Akuntansi\AkuntansiController@SaveHapusSaldoAwal');
        Route::post('akuntansi/save-data-closing-jurnal', 'Akuntansi\AkuntansiController@SaveClosingJurnal');
        Route::post('akuntansi/save-batal-closing-jurnal', 'Akuntansi\AkuntansiController@SaveBatalClosingJurnal');
        Route::post('akuntansi/save-data-master-coa', 'Akuntansi\AkuntansiController@SaveDataChartOfAccount'); //done
        Route::post('akuntansi/save-bengkel-jurnal', 'Akuntansi\AkuntansiController@BengkelJurnal');
        Route::post('akuntansi/save-hapus-double-jurnal', 'Akuntansi\AkuntansiController@HapusDoubleJurnal');
        //            Route::post('akuntansi/save-jurnal-pembayaran_tagihan', 'Akuntansi\AkuntansiController@PostingJurnal_pembayaran_tagihan');
        //            Route::post('akuntansi/save-jurnal-verifikasi_tarek', 'Akuntansi\AkuntansiController@PostingJurnal_strukpelayanan_t_verifikasi_tarek');
        Route::post('akuntansi/save-hapus-data-jurnal', 'Akuntansi\AkuntansiController@PostingHapusJurnal_entry'); //done
        Route::post('akuntansi/save-entry-jurnal', 'Akuntansi\AkuntansiController@PostingJurnal_entry');
        Route::post('akuntansi/save-posting-jurnalv1', 'Akuntansi\AkuntansiController@PostingJurnalRev2018');
        Route::post('akuntansi/save-unposting-jurnalv1/', 'Akuntansi\AkuntansiController@UnPostingJurnalRev2018');
        Route::post('akuntansi/save-data-sal', 'Akuntansi\AkuntansiController@SaveDataSal'); //done
        Route::post('akuntansi/delete-data-sal', 'Akuntansi\AkuntansiController@deleteDataSal'); //done
        /*END POST*/
        // });
        // Route::group(['prefix' => 'ambulance'], function () {
        Route::get('ambulance/get-data-apd', 'Ambulance\AmbulanController@getDataApdAmbulance');
        Route::get('ambulance/get-kelompok-user', 'Ambulance\AmbulanController@getKelompokUser');
        Route::get('ambulance/get-data-for-combo', 'Ambulance\AmbulanController@GetDataForComboAmbulan');
        Route::get('ambulance/get-data-order-ambulan', 'Ambulance\AmbulanController@getDaftarOrderAmbulan');
        Route::get('ambulance/get-data-pasien-ambulan', 'Ambulance\AmbulanController@getPasienAmbulan');
        Route::post('ambulance/simpan-data-pelayanan-ambulan', 'Ambulance\AmbulanController@savePelayananPasienAmbulan');
        Route::get('ambulance/get-data-rincian-ambulan', 'Ambulance\AmbulanController@getRincianPelayananAmbulan');
        Route::get('ambulance/get-data-pelayanan-ambulan', 'Ambulance\AmbulanController@getPelayananAmbulan');
        Route::get('ambulance/get-data-jadwal-ambulan', 'Ambulance\AmbulanController@getJadwalAmbulan');
        Route::post('ambulance/delete-order-pelayanan-ambulan', 'JAmbulance\AmbulanController@hapusOrderPelayananAmbulan');
        Route::get('ambulance/get-riwayat-order-ambulan', 'Ambulance\AmbulanController@getRiwayatOrderPelayananAmbulan');
        Route::get('ambulance/get-order-pelayanan-ambulan', 'Ambulance\AmbulanController@getOrderPelayananAmbulan');
        Route::get('ambulance/get-data-combo-operator', 'Ambulance\AmbulanController@getDataComboOperator');
        Route::get('ambulance/get-data-diagnosa', 'Ambulance\AmbulanController@getDataDiagnosa');
        Route::post('ambulance/save-apd', 'Ambulance\AmbulanController@saveAntrianPasien');
        Route::get('ambulance/get-data-combo-labrad', 'Ambulance\AmbulanController@getDataComboLabRab');
        Route::post('ambulance/delete-pelayanan-pasien', 'Ambulance\AmbulanController@deletePelayananPasien');
        Route::get('ambulance/get-data-registrasi-pasien-ambulan', 'Ambulance\AmbulanController@getDaftarRegistrasiPasienAmbulan');
        Route::post('ambulance/save-order-pelayanan-ambulan', 'Ambulance\AmbulanController@saveOrderAmbulan');
        Route::post('ambulance/delete-order-pelayanan-ambulan', 'Ambulance\AmbulanController@hapusOrderPelayananAmbulan');
        Route::get('ambulance/get-data-produk', 'Ambulance\AmbulanController@getDataProduk');
        Route::post('ambulance/save-input-non-layanan', 'Ambulance\AmbulanController@SaveInputTagihan');
        Route::get('ambulance/daftar-tagihan-non-layanan', 'Ambulance\AmbulanController@daftarTagihanNonLayanan');
        Route::get('ambulance/detail-tagihan-non-layanan', 'Ambulance\AmbulanController@detailTagihanNonLayanan');
        // });
        Route::group(['prefix' => 'askep'], function () {

        });
        // Route::group(['prefix' => 'bedah'], function () {
        Route::get('bedah/get-data-combo-dokter', 'Bedah\BedahController@getDataComboDokter');
        Route::get('bedah/get-daftar-antrian-bedah', 'Bedah\BedahController@getDaftarRegistrasiDokterBedah');
        Route::get('bedah/get-dokters-combos', 'Bedah\BedahController@getDokters');
        Route::post('bedah/save-update-dokter-antrian', 'Bedah\BedahController@updateDokterAntrian');
        Route::post('bedah/save-pelayanan-pasien-bedah', 'Bedah\BedahController@savePelayananPasienBedah');
        Route::post('bedah/delete-verif-bedah', 'Bedah\BedahController@deleteVerifBedah');
        Route::get('bedah/lap-tindakan-bedah', 'Bedah\BedahController@getLaporanTindakanBedah');
        Route::get('bedah/get-tindakan-bedah', 'Bedah\BedahController@getTindakanBedah');
        // });
        // Route::group(['prefix' => 'bedmonitor'], function () {
        Route::get('bedmonitor/get-data-view-bed', 'BedMonitor\BedMonitorController@getKetersediaanTempatTidurViewBM');
        Route::get('bedmonitor/get-view-bed', 'BedMonitor\BedMonitorController@viewBedBM');
        Route::get('bedmonitor/get-profile-login', 'BedMonitor\BedMonitorController@getDataProfileLogin');
        // });
        // Route::group(['prefix' => 'bendaharapenerimaan'], function () {
        //**GET**//
        Route::get('bendaharapenerimaan/get-data-combo', 'BendaharaPenerimaan\BendaharaPenerimaanController@getDataCombo');
        Route::get('bendaharapenerimaan/get-daftar-sbm', 'BendaharaPenerimaan\BendaharaPenerimaanController@getDaftarSBM');
        Route::get('bendaharapenerimaan/get-daftar-penerimaan-bank', 'BendaharaPenerimaan\BendaharaPenerimaanController@daftarBKU');
        Route::get('bendaharapenerimaan/get-trial-balance', 'BendaharaPenerimaan\BendaharaPenerimaanController@getTrialBalanceBendahara');
        Route::get('bendaharapenerimaan/get-data-rekapitulasi-pendapatan-daerah', 'BendaharaPenerimaan\BendaharaPenerimaanController@getDataRekapitulasiPendapatanDaerah');
        Route::get('bendaharapenerimaan/get-data-rekapitulasi-pendapatan-daerahtahun', 'BendaharaPenerimaan\BendaharaPenerimaanController@getDataRekapitulasiPendapatanDaerahTahunan');
        Route::get('bendaharapenerimaan/get-data-pendapatan-bendahara', 'BendaharaPenerimaan\BendaharaPenerimaanController@getDataPendapatanBP');
        //**GET**//

        //**POST**//
        Route::post('bendaharapenerimaan/setoran-kasir', 'BendaharaPenerimaan\BendaharaPenerimaanController@simpanSetoranKasir');
        Route::post('bendaharapenerimaan/batal-setoran-kasir', 'BendaharaPenerimaan\BendaharaPenerimaanController@batalSetoranKasir');
        Route::post('bendaharapenerimaan/simpan-penerimaan-bank', 'BendaharaPenerimaan\BendaharaPenerimaanController@simpanBKU');
        Route::post('bendaharapenerimaan/save-temp-bku', 'BendaharaPenerimaan\BendaharaPenerimaanController@saveTempBukuKasUmum');
        Route::post('bendaharapenerimaan/save-temp-beritaacara', 'BendaharaPenerimaan\BendaharaPenerimaanController@saveBeritaAcara');
        Route::post('bendaharapenerimaan/save-lampiran-beritaacara', 'BendaharaPenerimaan\BendaharaPenerimaanController@saveTempLampiranBA');
        Route::post('bendaharapenerimaan/hapus-bku', 'BendaharaPenerimaan\BendaharaPenerimaanController@hapusBKU');
        //**POST**//
        // });
        // Route::group(['prefix' => 'bendaharapengeluaran'], function () {
        Route::get('bendaharapengeluaran/get-data-combo', 'BendaharaPengeluaran\BendaharaPengeluaranController@getDataCombo');
        Route::get('bendaharapengeluaran/get-daftar-pembayaran', 'BendaharaPengeluaran\BendaharaPengeluaranController@GetDaftarPembayaran');
        Route::get('bendaharapengeluaran/get-data-tagihan-suplier', 'BendaharaPengeluaran\BendaharaPengeluaranController@GetDaftarTagihanSuplier');
        Route::get('bendaharapengeluaran/get-detail-tagihan-suplier', 'BendaharaPengeluaran\BendaharaPengeluaranController@GetDetailTagihanSupplier');
        Route::get('bendaharapengeluaran/get-riwayat-pembayaran-suplier', 'BendaharaPengeluaran\BendaharaPengeluaranController@GetRiwayatPembayaran');
        Route::get('bendaharapengeluaran/get-data-penerimaan-kas-kecil', 'BendaharaPengeluaran\BendaharaPengeluaranController@getDaftarPenerimaanKasKecil');
        Route::get('bendaharapengeluaran/get-daftar-bku-bk', 'BendaharaPengeluaran\BendaharaPengeluaranController@daftarBKUPengeluaran');
        Route::get('bendaharapengeluaran/get-data-combo-bk', 'BendaharaPengeluaran\BendaharaPengeluaranController@getDataComboBk');
        Route::post('bendaharapengeluaran/hapus-bku', 'BendaharaPengeluaran\BendaharaPengeluaranController@hapusBKU');
        //**GET**//

        //**POST**//
        Route::post('bendaharapengeluaran/save-pembayaran-tagihan-suplier', 'BendaharaPengeluaran\BendaharaPengeluaranController@savePembayaranTagihanSuplier');
        Route::post('bendaharapengeluaran/simpan-pengeluaran-bku', 'BendaharaPengeluaran\BendaharaPengeluaranController@simpanBKUBk');
        // });
//        Route::group(['prefix' => 'bridging'], function () {
        // Route::group(['prefix' => 'bpjs'], function () {
        Route::get('bridging/bpjs/get-signature', 'Bridging\BridgingBPJSController@getSignature');
        Route::get('bridging/bpjs/get-has-code', 'Bridging\BridgingBPJSController@getHasCode');
        Route::get('bridging/bpjs/get-combo-bpjs-txt', 'Bridging\BridgingBPJSController@getComboBPJS');
        //**Referensi***
        Route::get('bridging/bpjs/get-poli', 'Bridging\BridgingBPJSController@getPoli');
        Route::get('bridging/bpjs/get-ref-diagnosa', 'Bridging\BridgingBPJSController@getDiagnosa');
        Route::get('bridging/bpjs/get-ref-diagnosa-part', 'Bridging\BridgingBPJSController@getDiagnosaPart');
        Route::get('bridging/bpjs/get-ref-faskes', 'Bridging\BridgingBPJSController@getFaskes');
        Route::get('bridging/bpjs/get-ref-faskes-part', 'Bridging\BridgingBPJSController@getFaskesSaeutik');
        Route::get('bridging/bpjs/get-ref-diagnosatindakan', 'Bridging\BridgingBPJSController@getProcedureDiagnosaTindakan');
        Route::get('bridging/bpjs/get-ref-diagnosatindakan-part', 'Bridging\BridgingBPJSController@getProcedureDiagnosaTindakanPart');
        Route::get('bridging/bpjs/get-ref-kelasrawat', 'Bridging\BridgingBPJSController@getKelasRawat');
        Route::get('bridging/bpjs/get-ref-dokter', 'Bridging\BridgingBPJSController@getDokter');
        Route::get('bridging/bpjs/get-ref-spesialistik', 'Bridging\BridgingBPJSController@getSpesialistik');
        Route::get('bridging/bpjs/get-ref-ruangrawat', 'Bridging\BridgingBPJSController@getRuangRawat');
        Route::get('bridging/bpjs/get-ref-carakeluar', 'Bridging\BridgingBPJSController@getCaraKeluar');
        Route::get('bridging/bpjs/get-ref-pascapulang', 'Bridging\BridgingBPJSController@getPascaPulang');
        Route::get('bridging/bpjs/get-ref-diagnosa-contoh', 'Bridging\BridgingBPJSController@getDiagnosaReferen');
        Route::get('bridging/bpjs/get-ref-dokter-part', 'Bridging\BridgingBPJSController@getDokterSaeutik');
        Route::get('bridging/bpjs/get-poli-part', 'Bridging\BridgingBPJSController@getPoliSaeutik');
        Route::get('bridging/bpjs/get-ref-dokter-dpjp', 'Bridging\BridgingBPJSController@getDokterDPJP');
        Route::get('bridging/bpjs/get-ref-propinsi', 'Bridging\BridgingBPJSController@getPropinsi');
        Route::get('bridging/bpjs/get-ref-kabupaten', 'Bridging\BridgingBPJSController@getKabupaten');
        Route::get('bridging/bpjs/get-ref-kecamatan', 'Bridging\BridgingBPJSController@getKecamatan');
        //*End Ref
        //**SEP
        Route::post('bridging/bpjs/insert-sep', 'Bridging\BridgingBPJSController@insertSEP');
        Route::get('bridging/bpjs/cek-sep', 'Bridging\BridgingBPJSController@cekSep');
        Route::delete('bridging/bpjs/delete-sep', 'Bridging\BridgingBPJSController@deleteSEP');
        Route::put('bridging/bpjs/update-sep', 'Bridging\BridgingBPJSController@updateSEP');
        Route::post('bridging/bpjs/post-pengajuan', 'Bridging\BridgingBPJSController@postPengajuan');
        Route::post('bridging/bpjs/post-aprovalSEP', 'Bridging\BridgingBPJSController@postApprovalPengajuanSep');
        Route::put('bridging/bpjs/update-tglpulang', 'Bridging\BridgingBPJSController@updateTglPulang');
        Route::get('bridging/bpjs/get-integrasi-inacbg', 'Bridging\BridgingBPJSController@getIntegrasiSepInaCbg');
        Route::get('bridging/bpjs/get-suplesi-jasaraharja', 'Bridging\BridgingBPJSController@getSuplesiJasaRaharja');
        Route::post('bridging/bpjs/insert-sep-v1.1', 'Bridging\BridgingBPJSController@insertSepV11');
        Route::put('bridging/bpjs/update-sep-v1.1', 'Bridging\BridgingBPJSController@updateSepV11');
        Route::get('bridging/bpjs/generateskdp', 'Bridging\BridgingBPJSController@generateNoSKDP');
        Route::get('bridging/bpjs/generate-sep-dummy', 'Bridging\BridgingBPJSController@generateSEPDummy');
        //End SEP
        //*PESERTA
        Route::get('bridging/bpjs/get-no-peserta', 'Bridging\BridgingBPJSController@getNoPeserta');
        Route::get('bridging/bpjs/get-nik', 'Bridging\BridgingBPJSController@getNIK');
        Route::get('bridging/bpjs/get-no-peserta-v1', 'Bridging\BridgingBPJSController@getNoPesertaV1');
        //** End Peserta */
        //##Rujukan
        Route::get('bridging/bpjs/get-rujukan-rs', 'Bridging\BridgingBPJSController@getNoRujukanRs');
        Route::get('bridging/bpjs/get-rujukan-pcare', 'Bridging\BridgingBPJSController@getNoRujukanPcare');
        Route::get('bridging/bpjs/get-rujukan-rs-nokartu', 'Bridging\BridgingBPJSController@getNoRujukanRsNoKartu');
        Route::get('bridging/bpjs/get-rujukan-pcare-nokartu', 'Bridging\BridgingBPJSController@getNoRujukanPcareNoKartu');
        Route::post('bridging/bpjs/insert-rujukan', 'Bridging\BridgingBPJSController@insertRujukan');
        Route::put('bridging/bpjs/update-rujukan', 'Bridging\BridgingBPJSController@updateRujukan');
        Route::delete('delete-rujukan', 'Bridging\BridgingBPJSController@deleteRujukan');
        Route::get('bridging/bpjs/get-rujukan-pcare-nokartu-multi', 'Bridging\BridgingBPJSController@getRujukanNoKartuMulti');
        Route::get('bridging/bpjs/get-rujukan-rs-nokartu-multi', 'Bridging\BridgingBPJSController@getRujukanNoKartuMultiRS');
        Route::get('bridging/bpjs/get-rujukanbytglrujukan', 'Bridging\BridgingBPJSController@getRujukanByTglRujukan');
        Route::get('bridging/bpjs/get-rujukanbytglrujukan-rs', 'Bridging\BridgingBPJSController@getRujukanByTglRujukanRS');
        //##End Rujukan
        //##Lembar Pengajuan Klaim
        Route::post('bridging/bpjs/insert-lpk', 'Bridging\BridgingBPJSController@insertLPK');
        Route::put('bridging/bpjs/update-lpk', 'Bridging\BridgingBPJSController@updateLPK');
        Route::delete('delete-lpk', 'Bridging\BridgingBPJSController@deleteLPK');
        Route::get('bridging/bpjs/data-lpk', 'Bridging\BridgingBPJSController@dataLPK');
        //##End Lembar Pengajuan Klaim
        //##Monitoring
        Route::get('bridging/bpjs/get-monitoring-kunjungan', 'Bridging\BridgingBPJSController@getMonitoringKunjungan');
        Route::get('bridging/bpjs/get-monitoring-klaim', 'Bridging\BridgingBPJSController@getMonitoringKlaim');
        Route::get('bridging/bpjs/monitoring/HistoriPelayanan/NoKartu/{noKartu}', 'Bridging\BridgingBPJSController@getMonitoringHistori');
        Route::get('bridging/bpjs/get-monitoring-klaim-jasaraharja', 'Bridging\BridgingBPJSController@getMonitoringJasaRaharja');
        Route::get('bridging/bpjs/get-monitoring-historipelayanan-peserta', 'Bridging\BridgingBPJSController@getHistoryPelayananPeserta');
        //##End Monitoring
        Route::get('bridging/bpjs/get-diagnosa-saeutik', 'Bridging\BridgingBPJSController@getDiagnosaSaeutik');
        Route::get('bridging/bpjs/get-diagnosa-tindakan-saeutik', 'Bridging\BridgingBPJSController@getDiagnosaTindakanSaeutik');
        Route::get('bridging/bpjs/get-ruangan-ri', 'Bridging\BridgingBPJSController@getRuanganRI');
        Route::get('bridging/bpjs/get-ruangan-rj', 'Bridging\BridgingBPJSController@getRuanganRJ');
        Route::get('bridging/bpjs/get-sep-bynoregistrasi', 'Bridging\BridgingBPJSController@getSepByNoregistrasi');

        Route::post('bridging/bpjs/save-bpjs-klaim', 'Bridging\BridgingBPJSController@simpanBpjsKlaim');
        Route::post('bridging/bpjs/save-bpjs-klaim-gagal-hitung', 'Bridging\BridgingBPJSController@simpanGagalHitungBpjsKlaim');
        Route::get('bridging/bpjs/get-checklist-klaim', 'Bridging\BridgingBPJSController@getChecklistKlaim');
        Route::post('bridging/bpjs/save-rujukan', 'Bridging\BridgingBPJSController@simpanLokalRujukan');
        Route::get('bridging/bpjs/get-daftar-rujukan', 'Bridging\BridgingBPJSController@getLokalRujukan');
        Route::post('bridging/bpjs/save-monitoring-klaim', 'Bridging\BridgingBPJSController@saveMonitoringKlaim');
        Route::get('bridging/bpjs/get-monitoring-klaim-status', 'Bridging\BridgingBPJSController@getMonitoringKlaimStts');

        Route::get('bridging/bpjs/get-daftar-poli-internal', 'Bridging\BridgingBPJSController@getRuanganBPJSInternal');
        Route::get('bridging/bpjs/get-nosep-by-norec-pd', 'Bridging\BridgingBPJSController@getNoSEPByNorecPd');

        Route::get('bridging/bpjs/aplicaresws/get-tt', 'Bridging\BridgingBPJSController@getKetersediaanTTNew');
        Route::get('bridging/bpjs/aplicaresws/rest/ref/kelas', 'Bridging\BridgingBPJSController@getReferensiKamar');
        Route::post('bridging/bpjs/aplicaresws/rest/bed/update/{kodeppk}', 'Bridging\BridgingBPJSController@updateKetersediaanTT');
        Route::post('bridging/bpjs/aplicaresws/rest/bed/create/{kodeppk}', 'Bridging\BridgingBPJSController@postRuanganBaru');
        Route::get('bridging/bpjs/aplicaresws/rest/bed/read/{kodeppk}/{start}/{limit}', 'Bridging\BridgingBPJSController@getKetersedianKamarRS');
        Route::post('bridging/bpjs/aplicaresws/rest/bed/delete/{kodeppk}', 'Bridging\BridgingBPJSController@hapusRuangan');
        Route::post('bridging/bpjs/dukcapil/get-nik', 'Bridging\BridgingBPJSController@getInformasiDukcapilFromNIK');
        //##YANKES
        Route::get('bridging/bpjs/yankes-get-kunjungan', 'Bridging\BridgingYankesController@getKunjungan');
        Route::post('bridging/bpjs/yankes-update-kunjungan', 'Bridging\BridgingYankesController@updateKunjungan');
        Route::get('bridging/bpjs/yankes-count-kunjungan-pasien', 'Bridging\BridgingYankesController@countKunjungan');
        Route::post('bridging/bpjs/yankes-insert-kunjungan', 'Bridging\BridgingYankesController@insertKunjungan');
        Route::get('bridging/bpjs/yankes-get-rujukan', 'Bridging\BridgingYankesController@getRujukan');
        Route::post('bridging/bpjs/yankes-update-rujukan', 'Bridging\BridgingYankesController@updateRujukan');
        Route::post('bridging/bpjs/yankes-insert-rujukan', 'Bridging\BridgingYankesController@insertRujukan');
        //##END YANKES
        // });
        // Route::group(['prefix' => 'inacbg'], function () {
        Route::get('bridging/inacbg/get-daftar-pasien-inacbg', 'Bridging\InaCbgController@getDaftarPasien');
        Route::post('bridging/inacbg/save-bridging-inacbg', 'Bridging\InaCbgController@saveBridgingINACBG');
        Route::get('bridging/inacbg/get-combo', 'Bridging\InaCbgController@getComboInaCbg');
        Route::get('bridging/inacbg/get-daftar-pasien-inacbg-rev-2', 'Bridging\InaCbgController@getDaftarPasienRev');
        Route::get('bridging/inacbg/get-daftar-informasi-tanggungan', 'Bridging\InaCbgController@getDaftarPasienInformasiTanggungan');

        Route::post('bridging/inacbg/save-proposi-bridging-inacbg', 'Bridging\InaCbgController@saveProposiBridgingINACBG');
        Route::post('bridging/inacbg/get-daftar-pasien-statusnaikkelas', 'Bridging\InaCbgController@getStatusNaikKelas');
        Route::post('bridging/inacbg/save-informasi-tanggungan', 'Bridging\InaCbgController@saveInformasiTanggungan');
        // });
        // Route::group(['prefix' => 'farmasi'], function () {
        Route::post('bridging/farmasi/save-consis-d', 'Bridging\BridgingFarmasiController@SimpanBridgingConsisD');
        // });

        // Route::group(['prefix' => 'sisrute'], function () {
        Route::get('bridging/sisrute/referensi/faskes', 'Bridging\BridgingSisruteController@getFaskes');
        Route::get('bridging/sisrute/referensi/faskes-paging', 'Bridging\BridgingSisruteController@getFaskesPaging');
        Route::get('bridging/sisrute/referensi/alasanrujukan', 'Bridging\BridgingSisruteController@getAlasanRujukan');
        Route::get('bridging/sisrute/referensi/diagnosa', 'Bridging\BridgingSisruteController@getDiagnosa');
        Route::get('bridging/sisrute/referensi/diagnosa-paging', 'Bridging\BridgingSisruteController@getDiagnosaPaging');
        Route::get('bridging/sisrute/rujukan/get', 'Bridging\BridgingSisruteController@getRujukan');
        Route::get('bridging/sisrute/get-pasien-nocm', 'Bridging\BridgingSisruteController@getPasienByNoCMSisrute');
        Route::get('bridging/sisrute/get-combo', 'Bridging\BridgingSisruteController@getComboSisrute');

        Route::post('bridging/sisrute/rujukan/post', 'Bridging\BridgingSisruteController@postRujukan');
        Route::put('bridging/sisrute/rujukan/put', 'Bridging\BridgingSisruteController@putRujukan');
        Route::put('bridging/sisrute/rujukan/jawab', 'Bridging\BridgingSisruteController@jawabRujukan');
        Route::put('bridging/sisrute/rujukan/batal', 'Bridging\BridgingSisruteController@batalRujukan');
        Route::put('bridging/sisrute/rujukan/notif', 'Bridging\BridgingSisruteController@notifRujukan');

        // });
        // Route::group(['prefix' => 'penunjang'], function () {
        Route::post('bridging/penunjang/save-bridging-zeta', 'Bridging\BridgingPenunjangController@saveBridgingZeta');
        Route::post('bridging/penunjang/save-bridging-sysmex', 'Bridging\BridgingPenunjangController@saveBridgingSysmex');
        Route::post('bridging/penunjang/save-bridging-vans-lab', 'Bridging\BridgingPenunjangController@saveBridgingVansLab');
        Route::post('bridging/penunjang/save-hapus-order-lab', 'Bridging\BridgingPenunjangController@saveHapusOrderLab');

        Route::get('bridging/penunjang/get-hasil-vans-lab', 'Bridging\BridgingPenunjangController@getHasilLaborat');
        // });
        // Route::group(['prefix' => 'dukcapil'], function () {
        Route::get('bridging/dukcapil/get-identitas-by-nik/{nik}', 'Bridging\BridgingDukcapilController@getIdentitasByNIK');
        Route::get('bridging/dukcapil/get-nik/{nik}', 'Bridging\BridgingDukcapilController@getNIKwilayahProv');
        Route::get('bridging/dukcapil/get-nik-indo/{nik}', 'Bridging\BridgingDukcapilController@getNikNasional');
        // });

        // });
        Route::post('bni/create-billing', 'Bridging\BridgingBNIController@createBilling');
        Route::post('bni/inquiry-billing', 'Bridging\BridgingBNIController@inquiryBilling');
        Route::post('bni/update-transaction', 'Bridging\BridgingBNIController@updateTransaction');
        Route::post('bni/callback-payment', 'Bridging\BridgingBNIController@callBackPayment');
        Route::post('bni/create-billing-sms', 'Bridging\BridgingBNIController@createBillingSMS');
        Route::post('bni/check-callback', 'Bridging\BridgingBNIController@checkCallBackPayment');

        Route::post('bank-agi/transfer-out-va-inquiry', 'Bridging\BridgingAGIController@inquiryOut');
        Route::post('bank-agi/transfer-out-va', 'Bridging\BridgingAGIController@transferOut');
        Route::post('bank-agi/transfer-incoming-va-inquiry', 'Bridging\BridgingAGIController@inquiryIncomingVA');
        Route::post('bank-agi/notif-transfer', 'Bridging\BridgingAGIController@notifTransfer');
        Route::post('bank-agi/transfer-out-real-inquiry', 'Bridging\BridgingAGIController@transferOutRealAccount');
        Route::post('bank-agi/transfer-out-real', 'Bridging\BridgingAGIController@transferBankLain');
        Route::post('bank-agi/inquiry-balance', 'Bridging\BridgingAGIController@accounInfo');
        Route::post('bank-agi/history-transaction', 'Bridging\BridgingAGIController@historyTransaction');
        Route::post('bank-agi/transfer-out-va-inquiry-test', 'Bridging\BridgingAGIController@inquiryOutTEST');
        Route::group(['prefix' => 'cssd'], function () {

        });
        // Route::group(['prefix' => 'eis'], function () {
        Route::get('eis/get-count-pasien', 'EIS\EISController@countPasienRS');
        Route::get('eis/get-count-pasien-terlayani', 'EIS\EISController@countPasienRSTerlayani');
        Route::get('eis/get-tempattidur-terpakai', 'EIS\EISController@getTempatTidurTerpakai');
        Route::get('eis/get-tempattidur-perkelas', 'EIS\EISController@getKetersediaanTempatTidurPerkelas');
        Route::get('eis/get-info-kunjungan-rawatjalan', 'EIS\EISController@getInfoKunjunganRawatJalanPerhari');
        Route::get('eis/get-borlostoi', 'EIS\EISController@getBorLosToi');
        Route::get('eis/get-trend-kunjungan-rawatjalan', 'EIS\EISController@getTrendKunjunganPasienRajal');
        Route::get('eis/get-pasien-perjenis-penjadwalan', 'EIS\EISController@getPasienPerjenisPenjadwalan');
        Route::get('eis/get-kunjungan-rs', 'EIS\EISController@getKunjunganRS');
        Route::get('eis/get-kunjungan-perjenispasien', 'EIS\EISController@getKunjunganRSPerJenisPasien');
        Route::get('eis/get-topten-asalperujuk-bpjs', 'EIS\EISController@getTopTenAsalPerujukBPJS');
        Route::get('eis/get-topten-diagnosa', 'EIS\EISController@getTopTenDiagnosa');
        Route::get('eis/get-kunjungan-jenis-pelayanan', 'EIS\EISController@getKunjunganPerJenisPelayanan');
        Route::get('eis/get-kunjungan-rawatinap', 'EIS\EISController@getKunjunganRuanganRawatInap');
        Route::get('eis/get-pendapatan-rs', 'EIS\EISController@getPendapatanRumahSakit');
        Route::get('eis/get-penerimaan-rs', 'EIS\EISController@getPenerimaanKasir');
        Route::get('eis/get-realisasitarget', 'EIS\EISController@getPenerimaanRealisasiTarget');
        Route::get('eis/get-realisasitarget-farmasi', 'EIS\EISController@getRealisasiTargetFarmasi');
        Route::get('eis/get-realisasitarget-usahalain', 'EIS\EISController@getUsahaLainnya');
        Route::get('eis/get-realisasitarget-grid', 'EIS\EISController@getPenerimaanRealisasiTargetGrid');
        Route::get('eis/get-count-pegawai', 'EIS\EISController@getCountPegawai');
        Route::get('eis/get-detail-layanan', 'EIS\EISController@getLaporanLayanan');
        Route::get('eis/get-info-stok', 'EIS\EISController@getInfoStok');
        Route::get('eis/get-pemakaianobat', 'EIS\EISController@getTrendPemakaianObat');
        Route::get('eis/detail-pasien-rj', 'EIS\EISController@detailPasienRJ');
        Route::get('eis/get-regiter-summary', 'EIS\EISController@getRegisterSummary');
        Route::get('eis/get-kunjungan-berdasarkan-param', 'EIS\EISController@getKunjunganBerdasarkanParameter');
        Route::get('eis/get-laporan-obat', 'EIS\EISController@getLaporanPemakaianObat');
        Route::get('eis/get-daftar-penerimaan', 'EIS\EISController@getDaftarPenerimaanSuplier');
        Route::get('eis/get-daftar-distribusi-barang-perunit', 'EIS\EISController@getDaftarDistribusiBarangPerUnit');
        Route::get('eis/get-combo-address', 'EIS\EISController@getComboAddressEIS');
        Route::get('eis/get-propinsi', 'EIS\EISController@getPropinsi');
        Route::get('eis/get-kecamatan', 'EIS\EISController@getKecamatan');
        Route::get('eis/get-kota', 'EIS\EISController@getKota');
        Route::get('eis/detail-pasien-teralayani/{idDept}', 'EIS\EISController@detailPasienTerlayani');
        Route::get('eis/get-info-absen', 'EIS\EISController@getInfoAbsen');
        Route::get('eis/get-monitoring-klaim', 'EIS\EISController@getAllMonitoringKlaim');

        Route::post('eis/save-txt-bpjs', 'EIS\EISController@simpanTXTBPJS');
        // });
        // Route::group(['prefix' => 'emr'], function () {
        //2019-12 penambahan arif awal
        Route::get('emr/get-radiologi-by-no-transaksi', 'EMR\EMRController@getRadiologiByNotransaksi');
        Route::get('emr/get-lab-by-no-transaksi', 'EMR\EMRController@getLabByNotransaksi');
        Route::get('emr/get-diagnosa-by-no-transaksi', 'EMR\EMRController@getDiagnosaByNotransaksi');
        Route::get('emr/get-resume-medis-db-lama/{notransaksi}', 'EMR\EMRController@getResumeMedisDbLama');
        Route::get('emr/get-menu-rekam-medis-dynamic-db-lama', 'EMR\EMRController@getMenuRekamMedisAtuhDbLama');
        //2019-12 penambahan arif akhir

        Route::post('emr/save-data-rekam-medis', 'EMR\EMRController@saveRekamMedis');
        Route::get('emr/get-data-rekam-medis', 'EMR\EMRController@getRekamMedis');
        Route::get('emr/get-combo', 'EMR\EMRController@getComboRekMed');
        Route::get('emr/get-info-pasien', 'EMR\EMRController@getInfoPasien');
        Route::get('emr/get-data-master-pap', 'EMR\EMRController@getMasterPAP');
        Route::get('emr/get-master-diagnosa-kep', 'EMR\EMRController@getDiagnosaKeperawatan');
        Route::get('emr/get-detail-diagnosa-kep-by-id', 'EMR\EMRController@getDetailDiagnosaKeperawatan');
        Route::get('emr/get-data-rekam-medis-dp', 'EMR\EMRController@getHistoryDiagnosaKeperawatan');
        Route::get('emr/get-histori-diagnosa-kep', 'EMR\EMRController@getHistoriDiagnosaKeperawatan');
        Route::get('emr/get-rekammedis-dokter', 'EMR\EMRController@getRekamMedisDokter');
        Route::post('emr/save-rekammedis-dokter', 'EMR\EMRController@saveRekamMedisDokter');
        Route::post('emr/hapus-rekammedis-dokter', 'EMR\EMRController@hapusRekamMedisDokter');
        Route::post('emr/post-anamnesis/{method}', 'EMR\EMRController@postAnamnesis');
        Route::get('emr/get-anamnesis', 'EMR\EMRController@getAnamnesis');
        Route::post('emr/post-riwayat/{method}', 'EMR\EMRController@postRiwayatPengobatan');
        Route::get('emr/get-riwayat', 'EMR\EMRController@getRiwayatPengobatan');
        Route::post('emr/post-pemeriksaanumum/{method}', 'EMR\EMRController@postPemeriksaanUmum');
        Route::get('emr/get-pemeriksaanumum', 'EMR\EMRController@getPemeriksaanUmum');
        Route::post('emr/post-edukasi/{method}', 'EMR\EMRController@postEdukasi');
        Route::get('emr/get-edukasi', 'EMR\EMRController@getEdukasi');
        Route::post('emr/post-rencana/{method}', 'EMR\EMRController@postRencana');
        Route::get('emr/get-rencana', 'EMR\EMRController@getRencana');
        Route::post('emr/post-perjanjian/{method}', 'EMR\EMRController@postPerjanjianPasien');
        Route::get('emr/get-perjanjian', 'EMR\EMRController@getPasienPerjanjian');
        Route::post('emr/post-cppt/{method}', 'EMR\EMRController@postCPPT');
        Route::get('emr/get-cppt', 'EMR\EMRController@getCPPT');
        Route::get('emr/get-apd', 'EMR\EMRController@getAntrianPasienDiperiksa');
        Route::post('emr/save-pengkajianpasien', 'EMR\EMRController@savePengkajianPasien');
        Route::get('emr/get-pengkajianpasien', 'EMR\EMRController@getPengkajianPasien');
        Route::post('emr/hapus-pengkajianpasien', 'EMR\EMRController@hapusPengkajianPasien');
        Route::post('emr/post-diagnosa-kep/{method}', 'EMR\EMRController@postMasterDiagnosaKeperawatan');
        Route::post('emr/post-resume-medis/{method}', 'EMR\EMRController@saveResumeMedis');
        Route::get('emr/get-resume-medis/{nocm}', 'EMR\EMRController@getResumeMedis');
        Route::post('emr/post-resume-medis-inap/{method}', 'EMR\EMRController@postResumeMedisInap');
        Route::get('emr/get-resume-medis-inap/{nocm}', 'EMR\EMRController@getResumeMedisInap');
        Route::post('emr/post-konsultasi', 'EMR\EMRController@saveOrderKonsul');
        Route::get('emr/get-order-konsul', 'EMR\EMRController@getOrderKonsul');
        Route::post('emr/disabled-konsultasi', 'EMR\EMRController@disabledOrderKonsul');
        Route::post('emr/save-konsul-from-order', 'EMR\EMRController@saveKonsulFromOrder');
        Route::get('emr/get-data-pengkajian-medis-pasien', 'EMR\EMRController@getDataPasienPengkajianMedis');
        Route::get('emr/get-rekam-medis-dynamic', 'EMR\EMRController@getRekamMedisAtuh');
        Route::get('emr/get-menu-rekam-medis-dynamic', 'EMR\EMRController@getMenuRekamMedisAtuh');
        Route::post('emr/save-emr-dinamis', 'EMR\EMRController@SaveTransaksiEMRBackup');
        // Route::post('emr/save-emr-dinamis', 'EMR\EMRController@SaveTransaksiEMR');
        Route::get('emr/get-emr-transaksi', 'EMR\EMRController@getEMRTransaksiRiwayat');
        Route::get('emr/get-emr-transaksi-detail', 'EMR\EMRController@getEMRTransaksiDetail');
        Route::get('emr/get-datacombo-part-pegawai', 'EMR\EMRController@getDataComboPegawaiPart');
        Route::get('emr/get-datacombo-part-ruangan', 'EMR\EMRController@getDataComboRuanganPart');
        Route::get('emr/get-datacombo-part-diagnosa', 'EMR\EMRController@getDataComboDiagnosaPart');
        Route::get('emr/get-datacombo-part-tindakan', 'EMR\EMRController@getDataComboTindakanPart');
        Route::get('emr/get-datacombo-part-dokter', 'EMR\EMRController@getDataComboDokterPart');
        Route::get('emr/get-datacombo-part-ruangan-pelayanan', 'EMR\EMRController@getDataComboRuanganPelayananPart');
        Route::get('emr/get-datacombo-part-jk', 'EMR\EMRController@getDataComboJKPart');
        Route::get('emr/get-datacombo-part-jenisdiagnosa', 'EMR\EMRController@getComboJensiDiagnosaPart');
        Route::get('emr/get-datacombo-part-diagnosa-tindakan', 'EMR\EMRController@getDataComboDiagnosa9Part');
        Route::get('emr/get-data-diagnosis', 'EMR\EMRController@getDataDiagnosis');
        Route::post('emr/save-data-odontogram', 'EMR\EMRController@SaveTransaksiEMROdontogram');
        Route::get('emr/get-data-odontogram', 'EMR\EMRController@getDataOdontogram');
        Route::post('emr/hapus-emr-transaksi', 'EMR\EMRController@hapusEMRTransaksi');
        Route::get('emr/get-daftar-dokumen-rekmed', 'EMR\EMRController@getDaftarDokumenRekamMedis');
        Route::get('emr/get-combo-dokumen-rekmed', 'EMR\EMRController@getComboDokRekMed');
        Route::post('emr/save-dokumen-rekmed', 'EMR\EMRController@saveDokumenRekamMedis');
        Route::get('emr/get-histori-dokumen-rekmed/{nocm}', 'EMR\EMRController@getHistoriDokumenRekmed');
        Route::get('emr/get-ruangan-part', 'EMR\EMRController@getRuanganPart');
        Route::get('emr/get-daftar-dokumen-rekmed-ruangan', 'EMR\EMRController@getDaftarDokumenRekamMedisRuangan');
        Route::post('emr/save-dokumen-rekmed-ruangan', 'EMR\EMRController@saveDokumenRekamMedisRuangan');
        Route::get('emr/get-data-keterangan-pengkajianicu', 'EMR\EMRController@getDataPengakajianICU');
        Route::get('emr/get-data-combo-emr', 'EMR\EMRController@getDataComboERM');
        Route::post('emr/save-data-emr-icu', 'EMR\EMRController@SaveTransaksiEMRICU');
        Route::get('emr/get-data-riwayat-emr-icu', 'EMR\EMRController@getDataRiwayatERMICU');
        Route::get('emr/get-data-detail-emr-icu', 'EMR\EMRController@getDataDetailEMRICU');
        Route::get('emr/get-data-riwayat-emr-icu-detail', 'EMR\EMRController@getDataRiwayatERMICUDetail');
        Route::get('emr/get-data-pasien/{nocm}', 'EMR\EMRController@getDataPasien');
        Route::get('emr/get-datacombo-part-asalrujukan', 'EMR\EMRController@getDataComboAsalRujukan');
        Route::get('emr/get-datacombo-part-kelompokpasien', 'EMR\EMRController@getDataComboKelompokpPasien');
        Route::post('emr/save-data-emr-pemeriksaanfisik-igd', 'EMR\EMRController@saveRekamMedisPemeriksaanFisik');
        Route::get('emr/get-data-riwayat-emr-pemeriksaan-fisik', 'EMR\EMRController@getDataRiwayatERMPemeriksaanFisik');
        Route::get('emr/get-data-pasien-new', 'EMR\EMRController@getDataPasien');
        Route::get('emr/get-intervensi', 'EMR\EMRController@getDiagnisaKepIntervensi');
        Route::get('emr/get-evaluasi', 'EMR\EMRController@getDiagnisaKepEvaluasi');
        Route::get('emr/get-implementasi', 'EMR\EMRController@getDiagnisaKepImplemen');
        Route::post('emr/post-{table}-diagnosakeperawatan/{method}', 'EMR\EMRController@postDetailDiagnoaKep');
        Route::get('emr/get-data-riwayat-emr', 'EMR\EMRController@getDataRiwayatEMR');
        Route::post('emr/update-data-emrpasien', 'EMR\EMRController@updateNoCmInEmrPasien');
        Route::post('emr/update-data-emrpasien-pd', 'EMR\EMRController@updatePdInEmrPasien');
        Route::get('emr/get-data-riwayat-pengkajiankeperawatan', 'EMR\EMRController@getDataPasienPengkajianKeperawatan');
        Route::get('emr/get-datacombo-part-agama', 'EMR\EMRController@getDataComboAgama');
        Route::get('emr/get-datacombo-metode', 'EMR\EMRController@getDataMetodebelajar');
        Route::get('emr/get-datacombo-diagnosa-jiwa', 'EMR\EMRController@getDataDiagnosaJiwa');
        Route::get('emr/get-datacombo-pendidikan', 'EMR\EMRController@getDataPendidikan');
        Route::get('emr/get-datacombo-perkawinan', 'EMR\EMRController@getDataPerkawinan');
        Route::get('emr/get-datacombo-pekerjaan', 'EMR\EMRController@getDataPekerjaan');
        Route::get('emr/get-riwayat-perawatan-pasien', 'EMR\EMRController@getDaftarRiwayatRegistrasiPHR');
        Route::get('emr/get-emr-transaksi-detail-img', 'EMR\EMRController@getEMRTransaksiImage');

        //2019-12 penambahan arif awal
        Route::get('emr/get-antrian-pasien-norec-db-lama/{notransaksi}', 'EMR\EMRController@getAntrianPasienDiperiksaDbLama');
        //2019-12 penambahan arif akhir

        Route::get('emr/get-antrian-pasien-norec/{norec}', 'EMR\EMRController@getAntrianPasienDiperiksa');
        Route::post('emr/post-anamnesis/{method}', 'EMR\EMRController@postAnamnesis');
        Route::get('emr/get-anamnesis', 'EMR\EMRController@getAnamnesis');
        Route::post('emr/post-riwayat/{method}', 'EMR\EMRController@postRiwayatPengobatan');
        Route::get('emr/get-riwayat', 'EMR\EMRController@getRiwayatPengobatan');
        Route::post('emr/post-pemeriksaanumum/{method}', 'EMR\EMRController@postPemeriksaanUmum');
        Route::get('emr/get-pemeriksaanumum', 'EMR\EMRController@getPemeriksaanUmum');
        Route::post('emr/post-edukasi/{method}', 'EMR\EMRController@postEdukasi');
        Route::get('emr/get-edukasi', 'EMR\EMRController@getEdukasi');
        Route::post('emr/post-rencana/{method}', 'EMR\EMRController@postRencana');
        Route::get('emr/get-rencana', 'EMR\EMRController@getRencana');
        Route::post('emr/post-perjanjian/{method}', 'EMR\EMRController@postPerjanjianPasien');
        Route::get('emr/get-perjanjian', 'EMR\EMRController@getPasienPerjanjian');
        Route::post('emr/post-cppt/{method}', 'EMR\EMRController@postCPPT');
        Route::get('emr/get-cppt', 'EMR\EMRController@getCPPT');

        Route::get('emr/get-data-riwayat-pengkajiankeperawatan', 'EMR\EMRController@getDataPasienPengkajianKeperawatan');
        Route::get('emr/daftar-riwayat-registrasi', 'EMR\EMRController@getDaftarRiwayatRegistrasi');
        Route::get('emr/get-pasien', 'EMR\EMRController@getDaftarPasien');
        // Route::post('emr/save-order-pelayanan', 'EMR\EMRController@saveOrderPelayanan');
        Route::get('emr/get-combo-icd9', 'EMR\EMRController@getIcd9');
        Route::get('emr/get-combo-icd10', 'EMR\EMRController@getDiagnosaIcd10Part');
        Route::get('emr/get-combo-diagnosis', 'EMR\EMRController@getComboDiagnosis');
        Route::get('emr/get-diagnosapasienbynoregicd9', 'EMR\EMRController@getDiagnosaPasienByNoregICD9');
        Route::get('emr/get-diagnosapasienbynoreg', 'EMR\EMRController@getDiagnosaPasienByNoreg');
        Route::post('emr/save-diagnosa-tindakan-pasien', 'EMR\EMRController@saveDiagnosaTindakanPasien');
        Route::post('emr/delete-diagnosa-tindakan-pasien', 'EMR\EMRController@deleteDiagnosaTindakanPasien');
        Route::post('emr/save-diagnosa-pasien', 'EMR\EMRController@saveDiagnosaPasien');
        Route::post('emr/delete-diagnosa-pasien', 'EMR\EMRController@deleteDiagnosaPasien');

        Route::get('emr/get-combo-resep-emr', 'EMR\EMRController@getDataComboResepEMR');
        Route::get('emr/get-daftar-detail-order', 'EMR\EMRController@getDaftarDetailOrder');
        Route::get('emr/get-info-stok', 'EMR\EMRController@getInformasiStok');
        Route::get('emr/get-produkdetail', 'Farmasi\PelayananResepController@getProdukDetail');
        Route::get('emr/get-jenis-obat', 'EMR\EMRController@getJenisObat');
        Route::post('emr/simpan-order-pelayananobatfarmasi', 'EMR\EMRController@SimpanOrderPelayananObat');
        Route::get('emr/get-transaksi-pelayanan', 'Farmasi\PelayananResepController@getTransaksiPelayananApotik');
        Route::get('emr/get-combo-penunjang', 'EMR\EMRController@getComboPenunjangOrder');
        Route::get('emr/get-riwayat-order-penunjang', 'EMR\EMRController@getRiwayatOrderPenunjang');
        Route::post('emr/save-order-pelayanan', 'EMR\EMRController@saveOrderPelayananLabRad');
        Route::post('emr/delete-order-pelayanan', 'EMR\EMRController@hapusOrderPelayananLabRad');
        Route::get('emr/get-riwayat-order-radiologi', 'EMR\EMRController@getRiwayatOrderRad');

        Route::get('emr/get-data-combo-surveilans', 'EMR\EMRController@getComboSurveilans');
        Route::get('emr/get-data-history-surveilans', 'EMR\EMRController@getHistorySurveilans');
        Route::get('emr/get-detail-history-surveilans', 'EMR\EMRController@getHistoryDetailSurveilans');
        Route::post('emr/save-data-surveilans', 'EMR\EMRController@saveDataSurveilans');
        Route::post('emr/hapus-data-surveilans', 'EMR\EMRController@hapusDataSurveilans');

        Route::get('emr/get-data-ecg-epic', 'EMR\EMRController@getDataECG');
        Route::post('emr/save-data-ecg', 'EMR\EMRController@SaveTransaksiECG');
        Route::get('emr/get-daftar-ecg-epic', 'EMR\EMRController@getDaftarECG');
        Route::get('emr/get-order-ok', 'EMR\EMRController@getOrderOK');
        Route::post('emr/send-chat-api', 'EMR\EMRController@sendChatAPI');

        Route::get('emr/get-pegawai-parts', 'EMR\EMRController@getPegawaiParts');
        Route::post('emr/hapus-order-pelayananobatfarmasi', 'EMR\EMRController@hapusOrderResep');

        Route::get('emr/get-daftar-obat-sering-diresepkan', 'EMR\EMRController@getObatSeringDiresepkanDokter');
        Route::get('emr/get-datacombo-part-kelompokpasien', 'EMR\EMRController@getDataComboKelompokPaisnePart');
        Route::get('emr/get-datacombo-part-kelas', 'EMR\EMRController@getDataComboKelasPart');

        Route::post('emr/save-data-delegasi-obat', 'EMR\EMRController@SimpanDelegasiPemberiObat');
        Route::get('emr/get-daftar-delegasi', 'EMR\EMRController@getDaftarDelegasiObat');
        Route::get('emr/get-delegasi-obat', 'EMR\EMRController@getDelegasiObat');
        Route::get('emr/get-data-kepatuhan-cuci-tangan', 'EMR\EMRController@getDataKepatuhanCuciTangan');
        Route::post('emr/hapus-delegasi-obat', 'EMR\EMRController@hapusDelegasiObat');

        Route::get('emr/get-emr-transaksi-detail-form', 'EMR\EMRController@getEMRTransaksiDetailForm');
        Route::post('emr/hapus-emr-transaksi-norec', 'EMR\EMRController@hapusEMRtransaksiNorec');
        Route::post('emr/save-verif-cppt-dokter', 'EMR\EMRController@saveVerifCPPTEmr');
        Route::post('emr/save-kepatuhancuci', 'EMR\EMRController@saveDataKepatuhanCuci');
        Route::get('emr/get-data-kepatuhan-cuci-tanganload', 'EMR\EMRController@getDataKepatuhanCuciTanganload');
        Route::post('emr/batal-kepatuhan-cuci-tangan', 'EMR\EMRController@saveBatalKepatuhanCuciTangan');
        Route::post('emr/save-kepatuhanhandhygiene', 'EMR\EMRController@saveDataKepatuhanHandHygiene');
        Route::get('emr/get-data-kepatuhan-handhygiene', 'EMR\EMRController@getDataKepatuhanHandHygiene');
        Route::post('emr/batal-kepatuhan-handhygiene', 'EMR\EMRController@saveBatalKepatuhanHandHygiene');
        Route::get('emr/get-nilai-statis-igd', 'EMR\EMRController@getNilaiStatisIGD');
        Route::post('emr/disable-emr-details', 'EMR\EMRController@disableEMRdetail');
        Route::get('emr/get-data-dg-primary/{Noregistrasi}', 'EMR\EMRController@getDataDiagnosaPrimary');
        Route::post('emr/update-jawaban-konsultasi', 'EMR\EMRController@jawabKonsul');
        Route::get('emr/get-vital-sign', 'EMR\EMRController@getVitalSign');
        Route::get('emr/get-emrbyid', 'EMR\EMRController@getMenuEmrById');
        Route::get('emr/get-info-emr-pasien', 'EMR\EMRController@getInfoEMRPasien');
        Route::get('emr/get-data-kepatuhan-handhygiene-ipcn', 'EMR\EMRController@getDataKepatuhanHandHygieneIPCN');
        Route::post('emr/save-keterangan-delegasi-obat-detail', 'EMR\EMRController@saveKetDelegasiObatDetail');
        Route::post('emr/save-perawat-delegasi-obat', 'EMR\EMRController@savePerawatDelegasiObat');
        Route::post('emr/image-upload', 'EMR\EMRController@saveCapKakiBayi');
        Route::get('emr/get-cap-kaki-bayi', 'EMR\EMRController@getCapKakiBayi');
        Route::get('emr/get-rekam-medis-dynamic', 'EMR\EMRController@getRekamMedisAtuh');
        Route::get('emr/get-menu-rekam-medis-dynamic', 'EMR\EMRController@getMenuRekamMedisAtuh');
        Route::get('emr/get-cbo-sediaan', 'EMR\EMRController@getCboSediaan');
        Route::get('emr/get-emr-transaksi-detail', 'EMR\EMRController@getEMRTransaksiDetail');
        Route::get('emr/get-emr-transaksi-detail-form', 'EMR\EMRController@getEMRTransaksiDetail');
        Route::get('emr/get-emr-data', 'EMR\EMRController@getEMRTransData');
        Route::get('emr/get-treeview-formularium', 'EMR\EMRController@getTreeFormularium');
        Route::get('emr/get-detail-formularium', 'EMR\EMRController@getDetailFormularium');

        Route::post('emr/save-navigasi', 'EMR\EMRController@saveMenuEMR');
        Route::post('emr/save-emr-d', 'EMR\EMRController@saveEMRD');
        Route::post('emr/delete-navigasi', 'EMR\EMRController@deleteMenuEMR');
        Route::post('emr/save-emr-dinamis', 'EMR\EMRController@SaveTransaksiEMRBackup');

        // });

        // Route::group(['prefix' => 'farmasi'], function () {
        Route::get('farmasi/get-daftar-order', 'Farmasi\ResepElektronikController@getDaftarOrder');
        Route::get('farmasi/get-daftar-detail-order', 'Farmasi\ResepElektronikController@getDaftarDetailOrder');
        Route::post('farmasi/save-status-resepelektonik', 'Farmasi\ResepElektronikController@saveStatusResepElektronik');

        Route::get('farmasi/get-datacombo_dp', 'Farmasi\FarmasiController@getDataComboDaftarPasien');
        Route::get('farmasi/get-daftar-pasien-farmasi', 'Farmasi\FarmasiController@getDaftarPasien');

        Route::get('farmasi/get-detailPD', 'Farmasi\PelayananResepController@getDataPengkajian');
        Route::get('farmasi/get-transaksi-pelayanan', 'Farmasi\PelayananResepController@getTransaksiPelayananApotik');
        Route::get('farmasi/get-detail-reg-farmasi', 'Farmasi\PelayananResepController@getDetailRegApotik');
        Route::get('farmasi/get-daftar-paket-obat-pasien', 'Farmasi\PelayananResepController@getDaftarPaketObatPasien');
        Route::post('farmasi/save-hapus-pelayananobat', 'Farmasi\PelayananResepController@DeletePelayananObat');

        Route::get('farmasi/get-datacombo', 'Farmasi\PelayananResepController@getDataCombo');
        Route::get('farmasi/get_detail-resep', 'Farmasi\PelayananResepController@getDetailResep');
        Route::get('farmasi/get-detail-order', 'Farmasi\PelayananResepController@getDetailOrder');
        Route::get('farmasi/get-info-stok', 'Farmasi\PelayananResepController@getInformasiStok');
        Route::get('farmasi/get-produkdetail', 'Farmasi\PelayananResepController@getProdukDetail');
        Route::post('farmasi/save-stock-merger', 'Farmasi\PelayananResepController@StokMerger');
        Route::get('farmasi/get-jenis-obat', 'Farmasi\PelayananResepController@getJenisObat');
        Route::post('farmasi/save-pelayananobat', 'Farmasi\PelayananResepController@SimpanPelayananObat');

        Route::get('farmasi/get-data-combo-transfer', 'Farmasi\PaketObatController@getDataComboTransfer');
        Route::get('farmasi/get-detail-ruangan-apd', 'Farmasi\PaketObatController@getRuanganFromAPD');
        Route::get('farmasi/get-detail-kirim-barang', 'Farmasi\PaketObatController@getDetailKirimBarang');
        Route::get('farmasi/get-detail-order-for-kirim-barang', 'Farmasi\PaketObatController@getDetailOrderBarangForKirim');
        Route::post('farmasi/save-kirim-barang', 'Farmasi\PaketObatController@saveKirimBarang');

        Route::get('farmasi/get-daftar-pasien-farmasi-ri', 'Farmasi\FarmasiController@getDaftarPasienRI');
        Route::get('farmasi/get-daftar-resep', 'Farmasi\FarmasiController@getDaftarResep');
        Route::get('farmasi/get-daftar-retur-obat', 'Farmasi\FarmasiController@getDaftarReturObat');
        Route::get('farmasi/get-daftar-paket-obat-pasien', 'Farmasi\FarmasiController@getDaftarPaketObatPasien');

        Route::get('farmasi/get-norec_bebas', 'Farmasi\PelayananObatBebasController@GetNorecResepBebas');
        Route::get('farmasi/get-detail-obat-bebas', 'Farmasi\PelayananObatBebasController@getDetailResepBebas');
        Route::get('farmasi/get-detail-pasien', 'Farmasi\PelayananObatBebasController@getDetailPasien');
        Route::post('farmasi/save-input-non-layanan-obat', 'Farmasi\PelayananObatBebasController@SaveInputTagihanObat');
        Route::get('farmasi/get-daftar-jual-bebas', 'Farmasi\PelayananObatBebasController@getDaftarPenjualanBebas');
        Route::post('farmasi/hapus-resep_ob', 'Farmasi\PelayananObatBebasController@DeleteResepOB');
        Route::post('farmasi/delete-terima-barang-suplier', 'SysAdmin\GeneralController@DeletePenerimaanSuplier');

        Route::get('farmasi/get-data-grid-sasaran-mutu', 'Farmasi\SasaranMutuController@getDataGrid');
        Route::get('farmasi/get-combo-sasaranmutu', 'Farmasi\SasaranMutuController@getDataCombo');

        Route::get('farmasi/get-hasil_consis-d', 'Farmasi\BridgingMinir45Controller@getConsisD');

        Route::post('farmasi/save-data-skrining-farmasi', 'Farmasi\ResepElektronikController@saveSkriningFarmasi');
        Route::get('farmasi/get-histori-skrining', 'Farmasi\ResepElektronikController@getDataSkriningFarmasi');
        Route::get('farmasi/get-alamat', 'Farmasi\ResepElektronikController@getAlamat');
        Route::post('farmasi/save-retur-obat-non-layanan', 'Farmasi\PelayananObatBebasController@saveReturTagihanObat');
        Route::get('farmasi/get-laporan-pengeluaran-obat', 'Farmasi\PelayananObatBebasController@getLaporanPengeluaranObat');
        Route::get('farmasi/get-daftar-retur-obat-detail', 'Farmasi\FarmasiController@getDaftarReturObatDetail');
        Route::get('farmasi/get-laporan-penyerahan-obat', 'Farmasi\PelayananObatBebasController@getLaporanPenyerahanObat');
        Route::get('farmasi/get-data-waktuminum-resep', 'Farmasi\PelayananResepController@getDataWaktuMinum');
        Route::get('farmasi/get-laporan-rekapitulasi-pelayanan', 'Farmasi\PelayananObatBebasController@getLaporanRekapitulasiPelayanan');
        Route::get('farmasi/get-transaksi-pelayanan-obat-kronis', 'Farmasi\PelayananResepController@getTransaksiPelayananObatKronis');
        Route::post('farmasi/save-pelayanan-obat-kronis', 'Farmasi\PelayananResepController@SimpanPelayananObatKronis');
        Route::get('farmasi/get-nostruk-kasir', 'Farmasi\PelayananResepController@getNoStrukKasir');
        Route::post('farmasi/batal-verifikasi-order-resep', 'Farmasi\PelayananResepController@saveBatalVerifikasiResep');
        Route::get('farmasi/get-laporan-penjualan-obat-detail', 'Farmasi\PelayananResepController@getLaporanPenjualanObatDetail');
        Route::get('farmasi/get-daftar-satuanresep', 'Farmasi\PelayananResepController@getDaftarSatuanResep');
        Route::post('farmasi/save-data-satuanresep', 'Farmasi\PelayananResepController@saveDataSatuanResep');
        Route::get('farmasi/get-laporan-penjualan-perkwitansi', 'Farmasi\PelayananObatBebasController@getLaporanPenjualanObatPerKwitansi');
        Route::get('farmasi/get-data-antrian-pasien', 'Farmasi\PelayananResepController@getRuanganTerakhirPasien');
        Route::get('farmasi/get-data-registrasi-pasien-farmasi', 'Farmasi\PelayananResepController@getDaftarRegistrasiPasien');

        // Route::group(['prefix' => 'produksi'], function (){
        Route::get('farmasi/produksi/get-daftar-produksi-obat', 'Farmasi\ProduksiBarangController@getDaftarProduksiObat');
        Route::post('farmasi/produksi/hapus-produksi-barang', 'Farmasi\ProduksiBarangController@hapusObatProduksi');
        Route::get('farmasi/produksi/get-data-combo-produksi', 'Farmasi\ProduksiBarangController@getDataComboProduksi');
        Route::get('farmasi/produksi/get-detail-master-barang-produksi', 'Farmasi\ProduksiBarangController@getDetailMasterProduksi');
        Route::get('farmasi/produksi/get-info-stok-detail', 'Farmasi\ProduksiBarangController@getInformasiStokDetail');
        Route::post('farmasi/produksi/save-input-sisa-produksi-barang', 'Farmasi\ProduksiBarangController@saveInputSisaProduksiBarang');
        Route::get('farmasi/produksi/get-daftar-master-barang-produksi', 'Farmasi\ProduksiBarangController@getDaftarBarangProduksi');
        Route::get('farmasi/produksi/get-detail-master-barang-produksi', 'Farmasi\ProduksiBarangController@getDetailMasterProduksi');
        Route::post('farmasi/produksi/save-produksi-barang', 'Farmasi\ProduksiBarangController@saveProduksiBarang');
        Route::get('farmasi/produksi/get-daftar-produksi-obat-detail', 'Farmasi\ProduksiBarangController@getDaftarProduksiObatDetail');

        // });
        // });
        // Route::group(['prefix' => 'gizi'], function () {
        Route::get('gizi/get-produk-menudiet', 'Gizi\GiziController@getProdukMenu');
        Route::post('gizi/save-order-gizi', 'Gizi\GiziController@saveOrderGizi');
        Route::get('gizi/get-daftar-order', 'Gizi\GiziController@getDaftarOrderGizi');
        Route::get('gizi/get-daftar-order-detail', 'Gizi\GiziController@getDaftarOrderGiziDetail');
        Route::get('gizi/get-combo', 'Gizi\GiziController@getDataComboBox');
        Route::post('gizi/delete-orderpelayanan-gizi', 'Gizi\GiziController@deleteOrderPelayananGizi');
        Route::post('gizi/save-kirimmenu-gizi', 'Gizi\GiziController@saveKirimMenuGizi');
        Route::get('gizi/get-daftar-kirim', 'Gizi\GiziController@getDaftarKirim');
        Route::post('gizi/update-orderpelayanan-gizi', 'Gizi\GiziController@updateOrderPelayananGizi');
        Route::post('gizi/batal-kirim', 'Gizi\GiziController@deleteKirimMenu');
        Route::post('gizi/hapus-order-gizi', 'Gizi\GiziController@hapusOrderGzi');
        Route::post('gizi/hapus-peritem-order', 'Gizi\GiziController@hapusOrderGziPeritem');
        Route::post('gizi/update-peritem-order', 'Gizi\GiziController@updateOrderGizi');
        // });
        // Route::group(['prefix' => 'humas'], function () {
        // GET //
        Route::get('humas/get-daftar-tarif-layanan', 'Humas\HumasController@getDaftarTarif');
        Route::get('humas/get-daftar-combo', 'Humas\HumasController@getDataComboHumas');
        Route::get('humas/get-daftar-data-produk', 'Humas\HumasController@getDataproduk');
        Route::get('humas/get-daftar-tarif-layanan-detail', 'Humas\HumasController@getDaftarTarifDetail');
        Route::get('humas/get-data-view-bed', 'Humas\HumasController@getDataViewBed');
        Route::get('humas/get-ketersediaan-tempat-tidur-view', 'Humas\HumasController@getKetersediaanTempatTidurView');
        Route::get('humas/get-daftar-combo-pegawai', 'Humas\HumasController@getDataPegawai');
        Route::get('humas/get-daftar-jadwal-dokter', 'Humas\HumasController@getJadwalDokter');
        Route::get('humas/get-ruangan-part', 'Humas\HumasController@getRuanganPart');
        Route::get('humas/get-combo-dokter', 'Humas\HumasController@getComboDokter');
        Route::get('humas/get-jadwal-perbulan', 'Humas\HumasController@getJadwalBulananDokter');
        Route::get('humas/get-daftar-registrasi-pasien', 'Humas\HumasController@getDaftarRegistrasiPasien');
        Route::get('humas/get-data-norec-apd', 'Humas\HumasController@getDataAntrianPasien');
        Route::get('humas/get-data-informasi-pasien-pulang', 'Humas\HumasController@getDataInformasiPasienPulang');
        Route::get('humas/get-data-informasi-pasien', 'Humas\HumasController@getDataInformasiPasien');
        Route::get('humas/get-data-informasi-riwayat-registrasi', 'Humas\HumasController@getDataInformasiRiwayatRegistrasi');
        Route::get('humas/get-data-informasi-pasien-dalam-perawatan', 'Humas\HumasController@getInformasiDataPasienDalamPerawatan');
        Route::get('humas/get-data-informasi-pasien-perjanjian', 'Humas\HumasController@getDataInformasiPasienPerjanjian');
        Route::get('humas/get-data-keluhan-pelanggan', 'Humas\HumasController@getDaftarKeluhan');
        Route::get('humas/get-data-detail-pasien', 'Humas\HumasController@getDetailPasien');
        Route::get('humas/get-penanganan-keluhan-pelanggan', 'Humas\HumasController@getDaftarPenanganKeluhan');
        Route::get('humas/get-combo-pegawai', 'Humas\HumasController@getDataPegawaiAll');
        Route::get('humas/get-data-informasi-pasien-dalam-perawatan-keswamas', 'Humas\HumasController@getInformasiDataPasienDalamPerawatanKeswamas');
        Route::get('humas/get-daftar-survey-puas', 'Humas\HumasController@getSurveyKepuasan');
        Route::get('humas/get-combo-survey', 'Humas\HumasController@getComboSurvey');
        Route::get('humas/get-lap-penunggu', 'Humas\HumasController@getLaporanPenunggu');
        // POST //
        Route::post('humas/save-jadwal-perbulan', 'Humas\HumasController@saveJadwalBulanan');
        Route::post('humas/save-keluhan-pelanggan', 'Humas\HumasController@SaveKeluhanPelanggan');
        Route::post('humas/save-batal-penangankeluhan', 'Humas\HumasController@saveBatalPenangananKeluhan');
        Route::post('humas/save-keluhan-penanganan-pelanggan', 'Humas\HumasController@savePenangananKeluhan');
        Route::post('humas/save-informasi-dokter', 'Humas\HumasController@saveInformasiDokter');
        Route::post('humas/delete-informasi-dokter', 'Humas\HumasController@deleteInformasiDokter');
        Route::post('humas/save-penunggu-pasien', 'Humas\HumasController@savePenungguPasien');
        Route::post('humas/save-pengambil-pasien', 'Humas\HumasController@savePengambilPasien');
        Route::post('humas/delete-penunggu-pasien', 'Humas\HumasController@deletePenungguPasien');
        // });
        // Route::group(['prefix' => 'igd'], function () {
        Route::get('igd/get-data-combo', 'IGD\IGDController@getCombo');
        Route::get('igd/get-daftar-pasien', 'IGD\IGDController@getAntrianPasienGawatDarurat');
        Route::get('igd/get-pemeriksaan-triage', 'IGD\IGDController@GetPemeriksaanTriage');
        Route::get('igd/get-kategori-triage', 'IGD\IGDController@GetKategoriTriage');
        Route::get('igd/get-hasil-triase', 'IGD\IGDController@GetHasilTriase');
        Route::get('igd/get-combo-triase', 'IGD\IGDController@getComboTriase');
        Route::post('igd/simpan-triage', 'IGD\IGDController@SimpanHasilTriase');
        Route::get('igd/get-data-pasien', 'IGD\IGDController@getDaftarPasien');
        Route::get('igd/get-data-pasien/{nocm}', 'IGD\IGDController@getDataPasien');
        // });
        // Route::group(['prefix' => 'jenazah'], function () {
        Route::get('jenazah/get-data-for-combo', 'Jenazah\JenazahController@GetDataForCombo');
        Route::get('jenazah/get-data-pasien', 'Jenazah\JenazahController@GetDataPasien');
        Route::post('jenazah/simpan-data-pengambilan-jenazah', 'Jenazah\JenazahController@SimpanDataPengambilanJenazah');
        Route::get('jenazah/get-data-jenazah', 'Jenazah\JenazahController@GetDataJenazah');
        Route::get('jenazah/get-data-order-jenazah', 'Jenazah\JenazahController@getDaftarOrderJenazah');
        Route::get('jenazah/get-data-pasien-forensikMedikolegal', 'Jenazah\JenazahController@getPasienForensikMedikolegal');
        Route::post('jenazah/simpan-data-pelayanan-Jenazah', 'Jenazah\JenazahController@savePelayananPasienJenazah');
        Route::get('jenazah/get-data-registrasi-pasien-Jenazah', 'Jenazah\JenazahController@getDaftarRegistrasiPasienJenazah');
        Route::get('jenazah/get-data-rincian-pasien-Jenazah', 'Jenazah\JenazahController@getRincianPelayananJenazah');
        Route::get('jenazah/get-order-pelayanan-jenazah', 'Jenazah\JenazahController@getOrderPelayananJenazah');
        Route::post('jenazah/save-order-pelayanan-jenazah', 'Jenazah\JenazahController@saveOrderJenazah');
        Route::post('jenazah/delete-order-pelayanan-jenazah', 'Jenazah\JenazahController@hapusOrderPelayananJenazah');
        Route::get('jenazah/get-riwayat-order-jenazah', 'Jenazah\JenazahController@getRiwayatOrderPelayananJenazah');
        Route::get('jenazah/get-daftar-pasien-meninggal', 'Jenazah\JenazahController@getDaftarPasienMeninggal');
        Route::get('jenazah/get-data-combo-operator', 'Jenazah\JenazahController@getDataComboOperator');
        Route::get('jenazah/get-data-diagnosa', 'Jenazah\JenazahController@getDataDiagnosa');
        Route::post('jenazah/save-apd', 'Jenazah\JenazahController@saveAntrianPasien');
        Route::get('jenazah/get-data-combo-labrad', 'Jenazah\JenazahController@getDataComboLabRab');
        Route::post('jenazah/delete-pelayanan-pasien', 'Jenazah\JenazahController@deletePelayananPasien');
        Route::get('jenazah/get-lap-pasien-meninggal', 'Jenazah\JenazahController@getLaporanPasienMeninggal');
        Route::get('jenazah/get-lap-pemulasaran-jenazah', 'Jenazah\JenazahController@getLaporanPemulasaranJenazah');
        Route::post('jenazah/batal-meninggal-pasien', 'Jenazah\JenazahController@BatalMeninggalPasien');
        Route::post('jenazah/save-permohonan-pelayanan-jenazah', 'Jenazah\JenazahController@savePermohonanPelayananJenazah');
        Route::get('jenazah/get-data-permohonan-pelayanan-jenazah', 'Jenazah\JenazahController@getDataPermohonanPelayananJenazah');
        Route::post('jenazah/hapus-permohonan-pelayanan-jenazah', 'Jenazah\JenazahController@HapusPermohonanPelayananJenazah');
        // });
        // Route::group(['prefix' => 'kasir'], function () {
        // GET
        Route::get('kasir/get-data-combo-kasir', 'Kasir\KasirController@getDataComboKasir');
        Route::get('kasir/get-data-combo-ruangan', 'Kasir\KasirController@getComboRuanganRanapRajal');
        Route::get('kasir/daftar-tagihan-non-layanan', 'Kasir\KasirController@daftarTagihanNonLayanan');
        Route::get('kasir/get-data-produk', 'Kasir\KasirController@getDataProduk');
        Route::get('kasir/detail-tagihan-non-layanan', 'Kasir\KasirController@detailTagihanNonLayanan');
        Route::get('kasir/daftar-tagihan-pasien', 'Kasir\KasirController@daftarTagihanPasien');
        Route::get('kasir/detail-tagihan-pasien', 'Kasir\KasirController@detailTagihanPasien');
        Route::get('kasir/daftar-piutang-layanan', 'Kasir\KasirController@daftarPiutang');
        Route::get('kasir/daftar-pasien-aktif', 'Kasir\KasirController@daftarPasienAktif');
        Route::get('kasir/data-daftar-sbm', 'Kasir\KasirController@daftarSBM');
        Route::get('kasir/get-data-lap-penerimaan-kasir-harian', 'Kasir\KasirController@getDataLaporanPenerimaanKasirHarian');
        Route::get('kasir/get-data-lap-penerimaan-kasir-perusahaan', 'Kasir\KasirController@getDataLaporanPenerimaanKasirPerusahaan');
        Route::get('kasir/get-data-lap-pendapatan', 'Kasir\KasirController@getDataLapPendapatan');
        Route::get('kasir/get-data-lap-pendapatan-ruangan', 'Kasir\KasirController@getDataLaporanPendapatanRuangan');
        Route::get('kasir/get-data-lap-pendapatan-ruanganNew', 'Kasir\KasirController@getDataLaporanPendapatanRuanganNew');
        Route::get('kasir/get-data-lap-piutang-penjamin', 'Kasir\KasirController@getDataLaporanPiutangPenjamin');
        Route::get('kasir/get-data-pembayaran', 'Kasir\KasirController@pembayaran');
        Route::get('kasir/detail-pasien-deposit/{noRegister}', 'Kasir\KasirController@detailPasienDeposit');
        Route::get('kasir/get-data-lap-perincian-penerimaan', 'Kasir\KasirController@getDataLaporanPenerimaan');
        Route::get('kasir/get-data-lap-rekap-pendapatan-harian', 'Kasir\KasirController@getDataLaporanRekapPendapatanHarian');
        Route::get('kasir/get-data-lap-perincian-penerimaan-mingguan', 'Kasir\KasirController@getDataLaporanPenerimaanMingguan');
        Route::get('kasir/get-data-lap-rekap-retribusi-daerah', 'Kasir\KasirController@getDataLaporanRekapHasilRetribusiDaerah');
        Route::get('kasir/get-data-lap-target-realisasi-pendapatan', 'Kasir\KasirController@getDataLaporanTargetRealisasiPendapatan');
        Route::get('kasir/get-data-lap-opd-administrasi', 'Kasir\KasirController@getDataLaporanOPDAdministrasi');
        Route::get('kasir/get-data-lap-opd-fungsional', 'Kasir\KasirController@getDataLaporanOPDFungsional');
        Route::get('kasir/get-data-lap-penerimaan-rekening', 'Kasir\KasirController@getDataLaporanPerRekening');
        Route::get('kasir/get-data-lap-non-layanan', 'Kasir\KasirController@getDataLaporanPendapatanDiklat');
        Route::get('kasir/get-data-rekap-diklat', 'Kasir\KasirController@getDataRekapDiklat');
        Route::get('kasir/get-laporan-jaspel-ranap-rajal', 'Kasir\KasirController@getLapJaspelRajalRanap');
        Route::get('kasir/get-data-lap-penerimaan-semua-kasir', 'Kasir\KasirController@getDataLaporanPenerimaanSemuaKasir');
        Route::get('kasir/cetak-pdf-lap-penerimaan-semua-kasir', 'Report\ReportController@getDataLaporanPenerimaanSemuaKasirPDF');
        Route::get('kasir/get-data-detail-lap-penerimaan-semua-kasir', 'Kasir\KasirController@getLaporanDetailPenerimaanKasir');
        Route::get('kasir/get-data-detail-lap-penerimaan-semua-kasir-non', 'Kasir\KasirController@getLaporanDetailPenerimaanKasirNonLayanan');
        Route::get('kasir/get-data-lap-penerimaan-azalea-mcu', 'Kasir\KasirController@getDataLaporanPenerimaanAzaleaMCU');
        Route::get('kasir/get-data-detail-lap-penerimaan-semua-kasir-ok', 'Kasir\KasirController@getLaporanDetailPenerimaanKasirObatKronis');
        Route::get('kasir/data-virtual-account', 'Kasir\KasirController@daftarVirtualAccount');
        Route::get('kasir/detail-piutang-pasien/{norecSPP}', 'Kasir\KasirController@detailPiutangPasien');
        // GET

        // POST
        Route::post('kasir/save-input-non-layanan', 'Kasir\KasirController@SaveInputTagihan');
        Route::post('kasir/save-log-batal-bayar', 'Kasir\KasirController@saveLogBatalBayar');
        Route::post('kasir/save-ubah-cara-bayar', 'Kasir\KasirController@UbahCaraBayar');
        Route::post('kasir/save-batal-bayar', 'Kasir\KasirController@deletePembayaranTagihan');
        Route::post('kasir/simpan-data-pembayaran', 'Kasir\KasirController@simpanPembayaran');  //done
        Route::post('kasir/save-batal-pulang', 'Kasir\KasirController@HapusTglPulang');  //done
        Route::post('kasir/hapus-transaksi-non-layanan', 'Kasir\KasirController@BatalInputTagihanNonLayanan');
        Route::post('kasir/simpan-data-pembayaran-virtual', 'Kasir\KasirController@createBillingSIMRS');
        Route::post('kasir/send-sms/{nomor}/{contena}', 'EMR\EMRController@sendSMS');

        // POST
        // });
        // Route::group(['prefix' => 'kiosk'], function () {
        Route::post('kiosk/save-antrian', 'KiosK\KiosKController@saveAntrianTouchscreen');
        Route::get('kiosk/get-ruanganbykode/{kode}', 'KiosK\KiosKController@getRuanganByKodeInternal');
        Route::get('kiosk/get-diagnosabykode/{kode}', 'KiosK\KiosKController@getDiagnosaByKode');
        Route::get('kiosk/get-view-bed-tea', 'KiosK\KiosKController@getKetersediaanTempatTidurView');
        Route::get('kiosk/get-view-bed', 'KiosK\KiosKController@viewBed');
        Route::get('kiosk/get-combo', 'KiosK\KiosKController@getDataCombo');
        Route::get('kiosk/get-tarif', 'KiosK\KiosKController@getDaftarTarif');
        Route::post('kiosk/save-survey', 'KiosK\KiosKController@saveSurvey');
        Route::get('kiosk/get-combo-dokter-temp', 'KiosK\KiosKController@getComboDokterKios');
        Route::get('kiosk/get-combo-setting', 'KiosK\KiosKController@getComboSettingKios');
        Route::get('kiosk/get-ruangan', 'KiosK\KiosKController@getComboRuanganKios');
        Route::get('kiosk/get-slotting-kiosk', 'KiosK\KiosKController@getSlottingKios');
        Route::get('kiosk/get-slotting-kosong', 'KiosK\KiosKController@getSlottingKosong');
        Route::get('kiosk/get-list-loket', 'KiosK\KiosKController@getListLoket');
        Route::get('kiosk/get-dokter-internal', 'KiosK\KiosKController@getDokterInternal');
        Route::get('kiosk/get-combo-kiosk2', 'KiosK\KiosKController@getComboKios2');


        Route::post('kiosk/save-slotting-kiosk', 'KiosK\KiosKController@saveSlottingKios');
        Route::post('kiosk/delete-slotting-kiosk', 'KiosK\KiosKController@deleteSlotting');

        // });
        // Route::group(['prefix' => 'laboratorium'], function () {
        Route::get('laboratorium/get-hasil-lab', 'Laboratorium\LaboratoriumController@getHasilLab');
        Route::get('laboratorium/get-hasil-lab-vans', 'Laboratorium\LaboratoriumController@getHasilLaborat');
        Route::get('laboratorium/get-datfar-pasien-ri-lab', 'Laboratorium\LaboratoriumController@getDaftarRIlabRad');
        Route::get('laboratorium/get-detail-reg-lab-rad', 'Laboratorium\LaboratoriumController@getDetailRegLabRad');
        Route::get('laboratorium/get-hasil-lab-fire', 'Laboratorium\LaboratoriumController@getHasilLabVans');
        Route::get('laboratorium/get-laporan-tindakan', 'Laboratorium\LaboratoriumController@getLaporanTindakanLaboratorium');
        Route::get('laboratorium/get-hasil-lab-pa', 'Laboratorium\LaboratoriumController@getHasilLabPA');
        Route::post('laboratorium/save-hasil-lab-pa', 'Laboratorium\LaboratoriumController@saveHasilLabPA');
        Route::get('laboratorium/get-lap-pemeriksaan-pa', 'Laboratorium\LaboratoriumController@getLapPemeriksaanPA');
        Route::get('laboratorium/get-hasil-lis', 'Bridging\BridgingPenunjangController@getHasilLIS');
        Route::get('laboratorium/get-laporan-tindakan-bank-darah', 'Laboratorium\LaboratoriumController@getLaporanTindakanBankDarah');
        Route::get('report/get-data-hasil-lab', 'Report\ReportController@cetakHasilLIS');
        Route::get('report/cetak-ekspertise', 'Report\ReportController@cetakEkspertise');
        Route::get('report/cetak-hasil-lab-histopatologi', 'Report\ReportController@cetakHispatologi');
        Route::get('report/cetak-resep-dokter', 'Report\ReportController@cetakResepDokter');
        Route::get('laboratorium/get-lap-kunjungan', 'Laboratorium\LaboratoriumController@getLaporanKunjungan');
        Route::get('laboratorium/get-combo-dokter-lab', 'Laboratorium\LaboratoriumController@getDokter');


        Route::get('laboratorium/get-combo-map-lab', 'Laboratorium\LaboratoriumController@getComboMapLab');
        Route::get('laboratorium/get-data-jenis-pemeriksaan', 'Laboratorium\LaboratoriumController@getJenisPemeriksaan');
        Route::get('laboratorium/get-hasil-lab-manual', 'Laboratorium\LaboratoriumController@getHasilLabManual');
        Route::get('laboratorium/get-master-produk', 'Laboratorium\LaboratoriumController@getMasterProduk');
        Route::get('laboratorium/get-data-satuan-hasil', 'Laboratorium\LaboratoriumController@getSatuanHasil');
        Route::get('laboratorium/get-data-nilai-normal', 'Laboratorium\LaboratoriumController@getNilaiNormal');
        Route::get('laboratorium/get-map-hasil-lab', 'Laboratorium\LaboratoriumController@getMapHasilLab');
        Route::get('laboratorium/get-map-hasil-lab-bantu', 'Laboratorium\LaboratoriumController@getMapLabBantu');

        Route::post('laboratorium/save-detail-jenis', 'Laboratorium\LaboratoriumController@saveDetailJenis');
        Route::post('laboratorium/save-satuan', 'Laboratorium\LaboratoriumController@saveSatuanHasil');
        Route::post('laboratorium/save-nilai-normal', 'Laboratorium\LaboratoriumController@saveNilaiNormal');
        Route::post('laboratorium/save-map-hasil-lab', 'Laboratorium\LaboratoriumController@saveMapHasilLab');
        Route::post('laboratorium/hapus-map-hasil-lab', 'Laboratorium\LaboratoriumController@hapusMapHasilLab');
        Route::post('laboratorium/update-produk', 'Laboratorium\LaboratoriumController@updateProduk');
        Route::post('laboratorium/save-hasil-lab-manual', 'Laboratorium\LaboratoriumController@saveHasilLabManual');
        Route::post('laboratorium/save-map-hasil-lab-bantu', 'Laboratorium\LaboratoriumController@saveMapHasilLabVB');
        Route::post('laboratorium/save-apd-darah', 'Laboratorium\LaboratoriumController@saveAntrianPasienDarah');
        Route::post('laboratorium/save-pmi', 'Laboratorium\LaboratoriumController@savePMI');

        Route::post('laboratorium/save-catatan-lab', 'Bridging\BridgingPenunjangController@saveUpdateCatatan');
        Route::get('laboratorium/get-catatan-lab', 'Bridging\BridgingPenunjangController@getCatatan');
        // });
        // Route::group(['prefix' => 'laundry'], function () {
        //Route::get('get-combo-logistik','Laundry\LaundryController@getComboLaundry');
        Route::get('laundry/get-combo-laundry', 'Laundry\LaundryController@getComboLaundry');
        Route::get('laundry/get-daftar-kirim-laundry', 'Laundry\LaundryController@getDaftarKirimLaundry');
        Route::get('laundry/get-detail-kirim-laundry-ruangan', 'Laundry\LaundryController@getDetailKirimLaundry');
        Route::get('laundry/get-daftar-order-laundry-ruangan', 'Laundry\LaundryController@getDaftarOrderLaundry');
        Route::get('laundry/get-detail-order-laundry-ruangan', 'Laundry\LaundryController@getDetailOrderLaundry');
        Route::get('laundry/get-detail-order-for-kirim-laundry', 'Laundry\LaundryController@getDetailOrderLaundryForKirim');
        Route::get('laundry/get-daftar-barang-batal-laundry', 'Laundry\LaundryController@getDaftarProdukToBatalLaundry');
        Route::get('laundry/get-daftar-cuci-laundry', 'Laundry\LaundryController@getDaftartCuciLaundry');
        Route::get('laundry/get-stok-ruangan-linen', 'Laundry\LaundryController@getDataStokRuanganDetailLaundry');
        Route::get('laundry/get-detail-registrasi-linen', 'Laundry\LaundryController@getDetailRegistrasiLinen');
        Route::get('laundry/get-produkdetail', 'Laundry\LaundryController@getProdukDetailLaundry');
        Route::get('laundry/get-data-laporan-penerimaan-linen', 'Laundry\LaundryController@getDataLaporanPenerimaanLinen');
        Route::get('laundry/get-data-laporan-distribusi-linen', 'Laundry\LaundryController@getDataLaporanDistribusiLinen');
        Route::get('laundry/get-data-laporan-pencucian-linen', 'Laundry\LaundryController@getDataLaporanPencucianLinen');

        Route::post('laundry/save-kirim-linen-ruangan', 'Laundry\LaundryController@saveKirimLinen');
        Route::post('laundry/save-batal-terima', 'Laundry\LaundryController@saveBatalKirim');
        Route::post('laundry/save-order-laundry-ruangan', 'Laundry\LaundryController@saveOrderLaundry');
        Route::post('laundry/save-batal-order', 'Laundry\LaundryController@saveBatalOrder');
        Route::post('laundry/delete-order-laundry-ruangan', 'Laundry\LaundryController@deleteOrderLaundry');
        Route::post('laundry/save-terima-laundry', 'Laundry\LaundryController@saveTerimaLaundry');
        Route::post('laundry/cek-terima-laundry', 'Laundry\LaundryController@CekProdukKirimLaundry');
        Route::post('laundry/batal-kirim-terima-laundry', 'Laundry\LaundryController@BatalKirimTerimaLaundry');
        Route::post('laundry/save-pencucian-linen', 'Laundry\LaundryController@saveCuciLinen');
        Route::post('laundry/save-registrasi-linen', 'Laundry\LaundryController@saveRegistrasiLinen');

        // });

        Route::get('bankdarah/get-datacombo', 'BankDarah\BankDarahController@getComboBankDarah');
        Route::get('bankdarah/get-daftar-pemakaian-darah', 'BankDarah\BankDarahController@getDaftarPemakaianDarah');
        Route::post('bankdarah/save-pemakaian-darah', 'BankDarah\BankDarahController@savePemakaianDarah');
        Route::get('bankdarah/get-daftar-registrasi-bankdarah', 'BankDarah\BankDarahController@getDaftarRegistrasiPasienBankDarah');

        // Route::group(['prefix' => 'logistik'], function () {
        // GET //
        Route::get('logistik/get-combo-logistik', 'Logistik\LogistikController@getComboLogistik');
        Route::get('logistik/get-stok-ruangan-detail', 'Logistik\LogistikController@getDataStokRuanganDetail');
        Route::get('logistik/get-combo-barang-logistik', 'Logistik\LogistikController@getDataProdukLogistik');
        Route::get('logistik/get-data-produk-detail', 'Logistik\LogistikController@getDataProdukDetail');
        Route::get('logistik/get-combo-pegawai-logistik', 'Logistik\LogistikController@getDataPegawaiPart');
        Route::get('logistik/get-combo-rekanan-logistik', 'Logistik\LogistikController@getDataRekananPart');
        Route::get('logistik/get-data-kartu-stok', 'Logistik\LogistikController@GetDataKartuStok');
        Route::get('logistik/get-fast-moving', 'Logistik\LogistikController@getFastMoving');
        Route::get('logistik/get-slow-moving', 'Logistik\LogistikController@getSlowMoving');
        Route::get('logistik/get-stok-ruangan-so', 'Logistik\LogistikController@getStokRuanganSO');
        Route::get('logistik/get-data-monitoring-usulan', 'Logistik\LogistikController@getDaftarMonitoringUsulan');
        Route::get('logistik/get-daftar-usulan-permintaan-barang-ruangan', 'Logistik\LogistikController@getDaftarUsulanPermintaan');
        Route::get('logistik/get-daftar-rencana-usulan-permintaan-barang-ruangan', 'Logistik\LogistikController@getDaftarRencanaUsulanPermintaan');
        Route::get('logistik/get-nomor-usulan', 'Logistik\LogistikController@getNoUsulan');
        Route::get('logistik/get-data-detail-rencana-usulan-ajukan', 'Logistik\LogistikController@getDetailDataRencanaUsulan');
        Route::get('logistik/get-data-detail-rencana-usulan', 'Logistik\LogistikController@getDetailRUPB');
        Route::get('logistik/get-data-detail-usulan', 'Logistik\LogistikController@getDataDetailUsulanPermintaanBarangRuangan');
        Route::get('logistik/get-data-harga', 'Logistik\LogistikController@getHargaTerakhir');
        Route::get('logistik/get-daftar-permintaaan-barang-ruangan', 'Logistik\LogistikController@getDaftarPermintaanBarangRuangan');
        Route::get('logistik/get-daftar-sppb', 'Logistik\LogistikController@getDaftarSPPB');
        Route::get('logistik/get-detail-sppb', 'Logistik\LogistikController@getDetailDataSPPB');
        Route::get('logistik/get-detail-rekanan', 'Logistik\LogistikController@getDataDetailRekanan');
        Route::get('logistik/get-nomor-sppb', 'Logistik\LogistikController@getNomorSPPB');
        Route::get('logistik/get-nomor-terima', 'Logistik\LogistikController@getNoTerimaGenerate');
        Route::get('logistik/get-detail-penerimaan', 'Logistik\LogistikController@getDetailPenerimaanBarang');
        Route::get('logistik/get-detail-spk', 'Logistik\LogistikController@getDetailDataSPk');
        Route::get('logistik/get-detail-sppb-peritem', 'Logistik\LogistikController@getDetailSPPBPerItem');
        Route::get('logistik/get-data-detail-sppb', 'Logistik\LogistikController@getDaftarSPPBDetail');
        Route::get('logistik/get-daftar-spk', 'Logistik\LogistikController@getDaftarSPK');
        Route::get('logistik/get-data-spkkeupk', 'Logistik\LogistikController@getDaftarUPKKeSPK');
        Route::get('logistik/get-daftar-penerimaan', 'Logistik\LogistikController@getDaftarPenerimaanSuplier');
        Route::get('logistik/get-daftar-penerimaan-perunit', 'Logistik\LogistikController@getDaftarPenerimaanSuplierPerUnit');
        Route::get('logistik/get-daftar-pemakaian-ruangan', 'Logistik\LogistikController@getDaftarPemakaianStokRuangan');
        Route::get('logistik/get-detail-pemakaian-ruangan', 'Logistik\LogistikController@getPemakaianStokRuanganByNorec');
        Route::get('logistik/get-detail-order-barang-ruangan', 'Logistik\LogistikController@getDetailOrderBarang');
        Route::get('logistik/get-produkdetail', 'Logistik\LogistikController@getProdukDetail');
        Route::get('logistik/get-data-order-barang-ruangan', 'Logistik\LogistikController@getDaftarOrderBarang');
        Route::get('logistik/get-detail-kirim-barang-ruangan', 'Logistik\LogistikController@getDetailKirimBarang');
        Route::get('logistik/get-detail-order-for-kirim-barang', 'Logistik\LogistikController@getDetailOrderBarangForKirim');
        Route::get('logistik/get-daftar-distribusi-barang', 'Logistik\LogistikController@getDaftarDistribusiBarang');
        Route::get('logistik/get-daftar-barang-batal', 'Logistik\LogistikController@getDaftarProdukToBatal');
        Route::get('logistik/get-daftar-distribusi-barang-perunit', 'Logistik\LogistikController@getDaftarDistribusiBarangPerUnit');
        Route::get('logistik/get-daftar-bareng-per-bulan', 'Logistik\LogistikController@getDaftarBarangPerBulan');
        Route::get('logistik/get-daftar-mutasi-barang-expired', 'Logistik\LogistikController@getDaftarMutasiBarangKadaluarsa');
        Route::get('logistik/get-daftar-amprahan-hpp', 'Logistik\LogistikController@getDaftarAmprahanHpp');
        Route::get('logistik/get-barang-for-regis', 'Logistik\LogistikController@getDetailBarang');
        Route::get('logistik/get-detail-registrasiasset', 'Logistik\LogistikController@getDetailBarangRegisterAset');
        Route::get('logistik/get-daftar-asset', 'Logistik\LogistikController@getDataBarangRegisterAset');
        Route::get('logistik/get-data-produkforkirim', 'Logistik\LogistikController@getDataProdukKirim');
        Route::get('logistik/get-nomor-asset', 'Logistik\LogistikController@getNoAsset');
        Route::get('logistik/get-data-detail-asset', 'Logistik\LogistikControllerr@getProdukDetailAsset');
        Route::get('logistik/get-daftar-retur-distribusi-ruangan', 'Logistik\LogistikController@getDaftarReturDistribusiBarang');
        Route::get('logistik/get-daftar-retur-penerimaan', 'Logistik\LogistikController@getDaftarReturPenerimaanSuplier');
        Route::get('logistik/get-nomor-faktur', 'Logistik\LogistikController@getNoFaktur');
        Route::get('logistik/get-nomor-struk', 'Logistik\LogistikController@getNoTerima');
        Route::get('logistik/get-data-to-retur', 'Logistik\LogistikController@getDaftarProdukToReturPenerimaan');
//            Route::get('logistik/get-daftar-stock-flow', 'Logistik\LogistikController@getDaftarStockFlowDetailRev1');
        Route::get('logistik/get-daftar-stock-flow', 'Logistik\LogistikController@getDaftarStockFlowDetailRev2');
        Route::get('logistik/get-daftar-produkkadaluarsa', 'Logistik\LogistikController@getDataComboKadaluarsa');
        Route::get('logistik/get-daftar-barang-kadaluarsa', 'Logistik\LogistikController@getBarangKadaluarsa');
        Route::get('logistik/get-daftar-retur-penerimaan-detail', 'Logistik\LogistikController@getDaftarReturPenerimaanSuplierDetail');
        Route::get('logistik/get-daftar-so', 'Logistik\LogistikController@getDaftarStokRuanganSO');
        Route::get('logistik/get-daftar-so-detail', 'Logistik\LogistikController@getDaftarStokRuanganSODetail');
        Route::get('logistik/get-daftar-combo-anggaran', 'Logistik\LogistikController@getDataComboMataAnggaran');
        Route::get('logistik/get-stok-minimum_global', 'Logistik\LogistikController@getStokMinimumGlobal');
        Route::get('logistik/get-no-spk', 'Logistik\LogistikController@getNoSPK');
        Route::get('logistik/get-no-kontrak', 'Logistik\LogistikController@getKontrak');
        Route::get('logistik/get-neraca-persediaan', 'Logistik\LogistikController@Neraca');
        Route::get('logistik/get-kartu-persediaan-barang', 'Logistik\NeracaPersediaanController@GetDataKartuPersediaan');
        // GET //

        // POST //
        Route::post('logistik/get-stok-ruangan-so-from-file', 'Logistik\LogistikController@getStokRuanganSOFromFile');
        Route::post('logistik/get-stok-ruangan-so-from-fileexcel', 'Logistik\LogistikController@getStokRuanganSOFromFileExcel');
        Route::post('logistik/delete-terima-barang-suplier', 'Logistik\LogistikController@DeletePenerimaanSuplier');
        Route::post('logistik/delete-data-usulan-ruangan', 'Logistik\LogistikController@saveBatalUsulanPermintaanBarang');
        Route::post('logistik/delete-data-rencana-usulan-ruangan', 'Logistik\LogistikController@hapusDataRUPB');
        Route::post('logistik/save-data-rencana-usulan', 'Logistik\LogistikController@saveRencanaUsulanPermintaanNew');
        Route::post('logistik/save-verifikasi-direktur-keuangan', 'Logistik\LogistikController@saveVerifikasiDK');
        Route::post('logistik/save-verifikasi-pengelola-urusan', 'Logistik\LogistikController@saveVerifikasiPengelolaUrusan');
        Route::post('logistik/save-verifikasi-kepala-instalasi', 'Logistik\LogistikController@saveVerifikasiKepalaInstalasi');
        Route::post('logistik/save-data-usulan-permintaan-barang-ruangan', 'Logistik\LogistikController@saveUsulanPermintaan');
        Route::post('logistik/save-perbaiki-kartu-stok', 'Logistik\LogistikController@savePerbaikiKartuStok');
        Route::post('logistik/save-data-sppb', 'Logistik\LogistikController@SaveSPPB');
        Route::post('logistik/save-data-penerimaan', 'Logistik\LogistikController@saveTerimaBarangSuplier');
        Route::post('logistik/save-ubah-status-spk', 'Logistik\LogistikController@UpdateStatusUPK');
        Route::post('logistik/delete-data-spk', 'Logistik\LogistikController@DeleteSPK');
        Route::post('logistik/save-data-spk', 'Logistik\LogistikController@SaveSPK');
        Route::post('logistik/delete-data-penerimaan', 'Logistik\LogistikController@DeletePenerimaanBarangSupplier');
        Route::post('logistik/delete-pemakaian-ruangan', 'Logistik\LogistikController@hapusPemakaianStokRuangan');
        Route::post('logistik/save-pemakaian-ruangan', 'Logistik\LogistikController@savePemakaianStokRuangan');
        Route::post('logistik/save-order-barang-ruangan', 'Logistik\LogistikController@saveOrderBarang');
        Route::post('logistik/delete-order-barang-ruangan', 'Logistik\LogistikController@saveBatalOrderBarang');
        Route::post('logistik/save-kirim-barang-ruangan', 'Logistik\LogistikController@saveKirimBarangRuangan');
        Route::post('logistik/cek-kirim-barang-ruangan', 'Logistik\LogistikController@CekProdukKirim');
        Route::post('logistik/batal-kirim-terima-barang', 'Logistik\LogistikController@BatalKirimTerima');
        Route::post('logistik/batal-kirim-terima-barang-peritem', 'Logistik\LogistikController@BatalKirimTerimaPerItem');
        Route::post('logistik/simpan-detail-regisaset', 'Logistik\LogistikController@SimpanDetailRegisterAset');
        Route::post('logistik/simpan-penyusutan-aset', 'Logistik\LogistikController@SimpanPenyusutanAset');
        Route::post('logistik/simpan-delete-penyusutan-aset', 'Logistik\LogistikController@SimpanDeletePenyusutanAset');
        Route::post('logistik/simpan-kirimbarang-aset', 'Logistik\LogistikController@saveKirimBarangAsset');
        Route::post('logistik/retur-distribusi-barang-ruangan', 'Logistik\LogistikController@SaveReturDistribusi');
        Route::post('logistik/retur-penerimaan-barang-supplier', 'Logistik\LogistikController@SaveReturPenerimaan');
        Route::post('logistik/save-closing-persediaan', 'Logistik\LogistikController@SaveClosingPersediaan');
        Route::post('logistik/save-barang-kadaluarsa', 'Logistik\LogistikController@saveBarangKadaluarsa');
        Route::post('logistik/save-data-stock-opname', 'Logistik\LogistikController@saveStockOpname');
        Route::post('logistik/delete-barang-kadaluarsa', 'Logistik\LogistikController@DeleteBarangKadaluarsa');
        Route::post('logistik/save-data-verifikasi', 'Logistik\LogistikController@saveVerifikasiAnggaran');
        Route::get('logistik/get-data-penyusutan-asset', 'Logistik\LogistikController@getDataPenyusutan');
        Route::get('logistik/get-daftar-history-pindah-asset', 'Logistik\LogistikController@getDaftarHistoryAsset');
        Route::post('logistik/update-tglkadaluarsa-spd', 'Logistik\LogistikController@updateBarangKadaluarsa');
        Route::post('logistik/save-adjusment-stok', 'Logistik\LogistikController@saveAdjustmentStok');
        Route::post('logistik/update-head', 'Logistik\LogistikController@updateHead');
        // POST //
        // });
        // Route::group(['prefix' => 'asset'], function () {
        Route::post('asset/save-data-jadwal-kalibrasi', 'Asset\AssetController@SaveDataJadwalAssetKalibrasi');
        Route::post('asset/save-data-jadwal-pemeliharaan', 'Asset\AssetController@SaveDataJadwalAssetPemeliharaan');
        Route::get('asset/get-data-jadwal-kalibrasi', 'Asset\AssetController@getDaftarKalibrasi');
        Route::get('asset/get-data-jadwal-pemeliharaan', 'Asset\AssetController@getDaftarPemeliharaan');
        Route::get('asset/get-jadwal-kalibrasi', 'Asset\AssetController@getJadwalKalibrasi');
        Route::get('asset/get-jadwal-pemeliharaan', 'Asset\AssetController@getJadwalPemeliharaan');
        Route::get('asset/get-produk-asset-part', 'Asset\AssetController@getDataProduk');
        // });
        // Route::group(['prefix' => 'microservice'], function () {
        //     Route::group(['prefix' => 'pendaftaran'], function () {
        /*GET*/
        Route::get('microservice/pendaftaran/get-bukti-pendaftaran', 'MicroService\MicroServiceController@GetBuktiPendaftaran');
        /*END GET*/
        // });
        // });
        Route::group(['prefix' => 'perawatanintensive'], function () {

        });
        // Route::group(['prefix' => 'perencanaan'], function () {

        Route::get('perencanaan/get-data-combo', 'Perencanaan\PerencanaanController@getDataCombo');
        Route::get('perencanaan/get-kel-keempat-part', 'Perencanaan\PerencanaanController@getKelAnggaranKeempat');
        Route::get('perencanaan/get-kel-ketiga-part', 'Perencanaan\PerencanaanController@getKelAnggaranKeTiga');
        Route::get('perencanaan/get-kel-kedua-part', 'Perencanaan\PerencanaanController@getKelAnggaranKedua');
        Route::get('perencanaan/get-child-bynorec', 'Perencanaan\PerencanaanController@getChildByNorec');
        Route::get('perencanaan/get-daftar-mataanggaran', 'Perencanaan\PerencanaanController@getDaftarMataAnggaran');
        Route::get('perencanaan/get-daftar-monitoring-mataanggaran', 'Perencanaan\PerencanaanController@getDaftarMonitoringAnggaran');
        Route::get('perencanaan/get-daftar-riwayat-realisasi', 'Perencanaan\PerencanaanController@getDaftarRiwayatRealisasi');
        Route::get('perencanaan/get-daftar-usulan-anggaran', 'Perencanaan\PerencanaanController@getDaftarUsulanAnggaran');
        Route::get('perencanaan/get-daftar-mataanggaran-upk', 'Perencanaan\PerencanaanController@getDaftarMataAnggaranForUpk');
        Route::get('perencanaan/get-kel-kelima-part', 'Perencanaan\PerencanaanController@getKelAnggaranKelima');
        Route::get('perencanaan/get-kel-keenam-part', 'Perencanaan\PerencanaanController@getKelAnggaranKeenam');

        Route::post('perencanaan/save-child', 'Perencanaan\PerencanaanController@saveChild');
        Route::post('perencanaan/delete-child', 'Perencanaan\PerencanaanController@deleteChildAnggaran');
        Route::post('perencanaan/save-kel-head-anggaran', 'Perencanaan\PerencanaanController@saveKelHead');
        Route::post('perencanaan/save-kel-pertama-anggaran', 'Perencanaan\PerencanaanController@saveKelPertama');
        Route::post('perencanaan/save-kel-kedua-anggaran', 'Perencanaan\PerencanaanController@saveKelKedua');
        Route::post('perencanaan/save-kel-ketiga-anggaran', 'Perencanaan\PerencanaanController@saveKelKetiga');
        Route::post('perencanaan/save-kel-keempat-anggaran', 'Perencanaan\PerencanaanController@saveKelKeempat');
        Route::post('perencanaan/delete-kel-head-anggaran', 'Perencanaan\PerencanaanController@deleteKelompokHead');
        Route::post('perencanaan/delete-kel-pertama-anggaran', 'Perencanaan\PerencanaanController@deleteKelompokPertama');
        Route::post('perencanaan/delete-kel-kedua-anggaran', 'Perencanaan\PerencanaanController@deleteKelompokKedua');
        Route::post('perencanaan/delete-kel-ketiga-anggaran', 'Perencanaan\PerencanaanController@deleteKelompokKetiga');
        Route::post('perencanaan/delete-kel-keempat-anggaran', 'Perencanaan\PerencanaanController@deleteKelompokKeempat');

        Route::post('perencanaan/delete-kel-anggaran', 'Perencanaan\PerencanaanController@deleteKelompokAnggaran');
        Route::post('perencanaan/save-kel-anggaran', 'Perencanaan\PerencanaanController@saveKelompokAnggaran');

        // });
        // Route::group(['prefix' => 'piutang'], function () {
        /*GET*/
        Route::get('piutang/get-data-combo-piutang', 'Piutang\PiutangController@getDataComboPiutang');
        Route::get('piutang/daftar-piutang-layanan', 'Piutang\PiutangController@daftarPiutang');
        Route::get('piutang/daftar-collected-piutang-layanan', 'Piutang\PiutangController@daftarCollectedPiutang');
        Route::get('piutang/collected-piutang-layanan/{noposting}', 'Piutang\PiutangController@collectedPiutang');
        Route::get('piutang/collecting-from-txt-inacbgs', 'Piutang\PiutangController@CollectingFromTxtInaCbgs');
        Route::get('piutang/batal-collected-piutang-layanan', 'Piutang\PiutangController@batalCollectingPiutang');
        Route::get('piutang/detail-piutang-pasien-collect/{noPosting}', 'Piutang\PiutangController@detailPiutangPasienCollecting');
        Route::get('piutang/get-daftar-kartupiutang', 'Piutang\PiutangController@daftarKartuPiutang');
        Route::get('piutang/daftar-pembayaran-piutang-perusahaan-periode', 'Piutang\PiutangController@daftarPembayaranPiutangPeriode');
        Route::get('piutang/get-data-histori-piutang', 'Piutang\PiutangController@getDaftarHistoriPiutang');
        Route::get('piutang/rekap-klaim-by-diagnosa/', 'Piutang\PiutangController@RekapKlainDiagnosaTXT');
        Route::get('piutang/get-checklist-klaim', 'Piutang\PiutangController@getChecklistKlaim');
        Route::get('piutang/daftar-kartu-piutang-perusahaan', 'Piutang\PiutangController@daftarKartuPiutangPerusahaanPeriode');
        /*END GET*/

        /*POST*/
        Route::post('piutang/collecting-piutang-layanan', 'Piutang\PiutangController@collectingPiutang');
        Route::post('piutang/save-bpjs-klaim-gagal-hitung', 'Piutang\PiutangController@simpanGagalHitungBpjsKlaim');
        Route::post('piutang/save-bpjs-klaim', 'Piutang\PiutangController@simpanBpjsKlaim');
        /*END POST*/
        // });
        // Route::group(['prefix' => 'pmkp'], function () {
        /*GET*/
        Route::get('pmkp/get-riwayat', 'PMKP\PMKPController@getRiwayat');
        Route::get('pmkp/get-data-laporan-dokter-pelayanan-poliklinik', 'PMKP\PMKPController@getDataLaporanDokterPelayananPoliklinik');
        Route::get('pmkp/get-data-laporan-dokter-rawat-inap', 'PMKP\PMKPController@getDataLaporanDokterPelayananRanap');
        Route::get('pmkp/get-data-combo-pmkp', 'PMKP\PMKPController@getDataCombo');
        Route::get('pmkp/get-data-laporan-dokter-penanggungjawab-rawat-inap', 'PMKP\PMKPController@getDataLaporanDokterPenanggungJawabRanap');
        Route::get('pmkp/get-data-laporan-jamvisite-dokter-rawat-inap', 'PMKP\PMKPController@getLaporanJamVisiteDokter');
        Route::get('pmkp/get-data-laporan-kematian-pasien-ranap', 'PMKP\PMKPController@getLaporanKematianPasienRanap');
        Route::get('pmkp/get-data-laporan-pasien-pulang-paksa', 'PMKP\PMKPController@getLaporanPasienPulangPaksa');
        Route::get('pmkp/get-data-laporan-lama-hari-perawatan-pasien', 'PMKP\PMKPController@getLaporanLamaHariPerawatanPasien');
        Route::get('pmkp/get-data-laporan-data-perawat-d3', 'PMKP\PMKPController@getPerawatMinimalD3');
        Route::get('pmkp/get-data-laporan-data-kematian', 'PMKP\PMKPController@getLaporanKematianPasienIgd');
        Route::get('pmkp/get-data-combo-indikator-mutu', 'PMKP\PMKPController@getComboIndikatorMutu');
        Route::get('pmkp/get-data-indikator-departemen', 'PMKP\PMKPController@getDaftarIndikator');
        Route::get('pmkp/get-data-sasaran-mutu', 'PMKP\PMKPController@getDataSasaranMutu');
        Route::get('pmkp/get-data-investigasi-sederhana', 'PMKP\PMKPController@GetDaftarLembarInvestigasiSederhana');
        Route::get('pmkp/get-data-insiden-internal', 'PMKP\PMKPController@GetDaftarLaporanInsidenInternal');
        Route::get('pmkp/get-data-insiden-keselamatan-pasien', 'PMKP\PMKPController@GetDaftarInsidenKeselamatanPasien');
        Route::get('pmkp/get-data-sasaran-mutu-bulanan', 'PMKP\PMKPController@getDataRekapSasaranMutu');
        Route::get('pmkp/get-data-pasien-registrasi', 'PMKP\PMKPController@getDataPasienRegistrasi');
        Route::get('pmkp/get-data-identifikasi-risiko', 'PMKP\PMKPController@GetDaftarLaporanIdentifikasiRisiko');
        Route::get('pmkp/get-data-risk-register', 'PMKP\PMKPController@GetDaftarLaporanRiskRegister');
        Route::get('pmkp/get-data-laporan-sensus-keselamatan-pasien', 'PMKP\PMKPController@getLaporanSensusKeselamatanPasienBulanan');
        Route::get('pmkp/get-detail-identifikasi-risiko', 'PMKP\PMKPController@GetDetailLaporanIdentifikasiRisiko');
        Route::get('pmkp/get-lap-indikator-mutu', 'PMKP\PMKPController@getLapIndikatorMutu');
        Route::get('pmkp/get-chart-jenis-keselamatan', 'PMKP\PMKPController@getChartJenisKeselamatan');
        Route::get('pmkp/get-chart-grading', 'PMKP\PMKPController@getChartGrading');
        /*END GET*/

        /*POST*/
        Route::post('pmkp/save-data-pmkp', 'PMKP\PMKPController@saveRiwayat');
        Route::post('pmkp/delete-data-pmkp', 'PMKP\PMKPController@hapusRiwayat');
        Route::post('pmkp/save-data-sasaran-mutu', 'PMKP\PMKPController@saveSasaranMutu');
        Route::post('pmkp/save-data-lembar-investigasi', 'PMKP\PMKPController@saveLembarKerjaInvestigasi');
        Route::post('pmkp/delete-data-lembar-investigasi', 'PMKP\PMKPController@hapusDataLembarInvestigasi');
        Route::post('pmkp/save-data-insiden-internal', 'PMKP\PMKPController@saveLaporanInsidenInternal');
        Route::post('pmkp/delete-data-insiden-internal', 'PMKP\PMKPController@hapusDataLaporanInsidenInternal');
        Route::post('pmkp/save-insiden-keselamatan-pasien', 'PMKP\PMKPController@saveInsidenKeselamatan');
        Route::post('pmkp/delete-insiden-keselamatan-pasien', 'PMKP\PMKPController@hapusInsidenKeselamatan');
        Route::post('pmkp/save-data-analisa-sasaran-mutu', 'PMKP\PMKPController@saveAnalisaSasaranMutu');
        Route::post('pmkp/save-data-identifikasi-risiko', 'PMKP\PMKPController@saveIdentifikasiRisiko');
        Route::post('pmkp/hapus-data-identifikasi-risiko', 'PMKP\PMKPController@hapusIdentifikasiRisiko');
        Route::post('pmkp/save-data-risk-register', 'PMKP\PMKPController@saveRiskRegister');
        Route::post('pmkp/hapus-data-risk-register', 'PMKP\PMKPController@hapusRiskRegister');
        /*END POST*/
        // });
        // Route::group(['prefix' => 'radiologi'], function () {
        Route::get('radiologi/get-data-combo-labrad', 'Radiologi\RadiologiController@getDataComboLabRab');
        Route::get('radiologi/get-rincian-pelayanan', 'Radiologi\RadiologiController@getRincianPelayanan');
        Route::post('radiologi/save-order-pelayanan', 'Radiologi\RadiologiController@saveOrderPelayananLabRad');
        Route::post('radiologi/delete-pelayanan-pasien', 'Radiologi\RadiologiController@deletePelayananPasien');
        Route::get('radiologi/get-acc-number', 'Radiologi\RadiologiController@getAccNumberRadiologi');
        Route::get('radiologi/get-combo', 'Radiologi\RadiologiController@getComboRad');
        Route::get('radiologi/get-daftar-pasien-penunjang', 'Radiologi\RadiologiController@getDaftarPasienPenunjang');
        Route::post('radiologi/update-jenis-kelamin', 'Radiologi\RadiologiController@updateJenisKelaminPasien');
        Route::post('radiologi/update-gol-darah', 'Radiologi\RadiologiController@updateGolonganDarah');
        Route::get('radiologi/get-combo-regis', 'Radiologi\RadiologiController@getComboRegs');
        Route::get('radiologi/get-daftar-pasien-labrad', 'Radiologi\RadiologiController@getDaftarRegistrasiPasienLabRad');
        Route::get('radiologi/get-antrian', 'Radiologi\RadiologiController@getAntrian');
        Route::get('radiologi/get-apd', 'Radiologi\RadiologiController@getAPD');
        Route::post('radiologi/save-apd', 'Radiologi\RadiologiController@saveAntrianPasien');
        Route::get('radiologi/get-daftar-order', 'Radiologi\RadiologiController@getDaftarOrderPenunjang');
        Route::get('radiologi/get-diagnosapasienbynoreg', 'Radiologi\RadiologiController@getDiagnosaRad');
        Route::get('radiologi/get-order-pelayanan', 'Radiologi\RadiologiController@getOrderPelayanan');
        Route::post('radiologi/save-pelayanan-pasien', 'Radiologi\RadiologiController@savePelayananPasien');
        Route::post('radiologi/delete-order-pelayanan', 'Radiologi\RadiologiController@deleteOrderPelayanan');
        Route::post('radiologi/delete-order-penunjang', 'Radiologi\RadiologiController@hapusOrderPenunjang');
        Route::post('radiologi/save-hasil-radiologi', 'Radiologi\RadiologiController@saveHasilRadiologi');
        Route::get('radiologi/get-hasil-radiologi', 'Radiologi\RadiologiController@getHasilRadiologi');
        Route::get('radiologi/get-laporan-tindakan', 'Radiologi\RadiologiController@getLaporanTindakanRadiologi');
        Route::get('radiologi/get-norec-hasil-radiologi', 'Radiologi\RadiologiController@getNoRecRadiologi');
        Route::get('radiologi/get-ekspertise', 'Radiologi\RadiologiController@getEkspertise');
        Route::post('radiologi/save-ekspertise', 'Radiologi\RadiologiController@saveEkspertise');
        Route::post('radiologi/save-ekspertise-pacs', 'Radiologi\RadiologiController@saveEkspertisePACS');
        Route::post('radiologi/save-dicom', 'Radiologi\RadiologiController@saveDicomView');
        Route::get('radiologi/get-list-hasil-radiologi', 'Radiologi\RadiologiController@getListHasilRadiologi');
        Route::get('radiologi/delete-list-hasil-radiologi', 'Radiologi\RadiologiController@deleteListHasilRadiologi');
        Route::get('radiologi/get-detail-verifikasi', 'Radiologi\RadiologiController@getDetailVerifLabRad');
        Route::get('radiologi/get-pelayananpasien-radiologi', 'Radiologi\RadiologiController@getPelayananRad');
        Route::get('radiologi/get-rincian-pelayanan-radiologi', 'Radiologi\RadiologiController@getRiwayatTindakanRadiologi');

        Route::get('radiologi/images/pacs/{filename}/{ext}', function ($filename, $ext) {
            $path = storage_path('images/pacs/' . $filename . '.' . $ext);
            if (!File::exists($path)) {
                abort(404);
            }
            $file = File::get($path);
            $type = File::mimeType($path);
            return response($file, 200)->header('Content-Type', $type);
        });


        // });
        // Route::group(['prefix' => 'rawatinap'], function () {
        Route::get('rawatinap/get-data-combo-dokter', 'RawatInap\RawatInapController@getDataComboDokter');
        Route::get('rawatinap/get-daftar-antrian-ranap', 'RawatInap\RawatInapController@getDaftarRegistrasiDokterRanap');
        Route::get('rawatinap/get-dokters-combos', 'RawatInap\RawatInapController@getDokters');
        Route::post('rawatinap/save-update-dokter-antrian', 'RawatInap\RawatInapController@updateDokterAntrian');
        Route::get('rawatinap/get-ruangan-last', 'RawatInap\RawatInapController@getRuanganLast');
        Route::post('rawatinap/save-pasien-jatuh', 'RawatInap\RawatInapController@saveIndikatorPasienJatuh');
        Route::get('rawatinap/get-combo-pindahpasien', 'RawatInap\PindahPulangController@getComboPindahPulang');
        Route::get('rawatinap/get-pasien-bynorec', 'RawatInap\PindahPulangController@getPindahPasienByNoreg2');
        Route::get('rawatinap/get-pasien-bynorec/{norec_pd}/{norec_apd}', 'RawatInap\PindahPulangController@getPindahPasienByNoreg');

        Route::get('rawatinap/get-kelasbyruangan', 'RawatInap\PindahPulangController@getKelasByRuangan');
        Route::get('rawatinap/get-kamarbyruangankelas', 'RawatInap\PindahPulangController@getKamarByKelasRuangan');
        Route::get('rawatinap/get-nobedbykamar', 'RawatInap\PindahPulangController@getNoBedByKamar');
        Route::get('rawatinap/get-nosep-by-norec-pd', 'Bridging\BridgingBPJSController@getNoSEPByNorecPd2');
        Route::get('rawatinap/get-nosep-by-norec-pd/{norec_pd}', 'Bridging\BridgingBPJSController@getNoSEPByNorecPd');

        Route::post('rawatinap/save-akomodasi-tea', 'RawatInap\RawatInapController@saveAkomodasiOtomatis');
        Route::get('rawatinap/get-kamar-ruangan-ibu', 'RawatInap\RawatInapController@getKamarIbuLast');
        Route::post('rawatinap/save-pulang-pasien', 'RawatInap\PindahPulangController@savePulangPasien');
        Route::post('rawatinap/save-pindah-pasien', 'RawatInap\PindahPulangController@savePindahPasien');
        Route::get('rawatinap/get-daftar-pasien-masih-dirawat', 'RawatInap\RawatInapController@getPasienMasihDirawat');
        Route::get('rawatinap/get-combo-pasien-masih-dirawat', 'RawatInap\RawatInapController@getComboPasienMasihDirawat');
        Route::get('rawatinap/get-antrian-pasien-diperiksa', 'RawatInap\RawatInapController@getDetailAntrianPasienDiperiksa');
        Route::post('rawatinap/save-batal-rawat-inap', 'RawatInap\RawatInapController@saveBatalRanap');
        Route::post('rawatinap/save-batal-pindah-ruangan', 'RawatInap\RawatInapController@saveBatalPindahRuangan');
        Route::post('rawatinap/update-kamar', 'RawatInap\RawatInapController@updateKamar');
        Route::get('rawatinap/get-daftar-pasien-rencana-pindah', 'RawatInap\RawatInapController@getDaftarRencanaPindahPasien');
        Route::get('rawatinap/get-data-pasien-pindah', 'RawatInap\RawatInapController@getDataPasienPindah');
        Route::get('rawatinap/get-pasien-jatuh', 'RawatInap\RawatInapController@getIndikatorPasienJatuh');
        Route::post('rawatinap/save-pasien-jatuh', 'RawatInap\RawatInapController@saveIndikatorPasienJatuh');
        Route::get('rawatinap/get-combo-pasien-jatuh', 'RawatInap\RawatInapController@getComboPasienJatuh');
        Route::get('rawatinap/get-combo-gizi', 'RawatInap\RawatInapController@getDataComboBoxGizi');
        Route::get('rawatinap/get-pasien-ranap-gizi', 'RawatInap\RawatInapController@getPasienDirawatGizi');
        Route::post('rawatinap/save-data-edukasi', 'RawatInap\RawatInapController@saveEdukasiIpcln');
        Route::get('rawatinap/get-data-ipcln', 'RawatInap\RawatInapController@getDataIPCLN');
        Route::post('rawatinap/hapus-jadwal-perbulan-pegawai', 'RawatInap\RawatInapController@hapusJadwalBulananPegawai');
        Route::get('rawatinap/get-combo-surveilan', 'RawatInap\RawatInapController@getDataComboSurv');
        Route::get('rawatinap/get-data-surveilans', 'RawatInap\RawatInapController@getDataSurveilans');
        Route::get('rawatinap/get-data-harian-surveilans', 'RawatInap\RawatInapController@getDataHarianSurveilans');
        Route::get('rawatinap/get-data-ido-surveilans', 'RawatInap\RawatInapController@getDataIdoSurveilans');
        Route::post('rawatinap/save-data-apd', 'RawatInap\RawatInapController@saveCheklisApd');
        Route::get('rawatinap/get-data-cheklis-apd', 'RawatInap\RawatInapController@getDataCheklisApd');
        // });
        // Route::group(['prefix' => 'rawatjalan'], function () {
        Route::get('rawatjalan/get-data-combo-dokter', 'RawatJalan\RawatJalanController@getDataComboDokter');
        Route::get('rawatjalan/get-dokters-combos', 'RawatJalan\RawatJalanController@getDokters');
        Route::get('rawatjalan/get-combo-surat', 'RawatJalan\RawatJalanController@getDataComboSurat');
        Route::get('rawatjalan/get-data-combo-operator', 'RawatJalan\RawatJalanController@getDataComboOperator');
        Route::get('rawatjalan/get-daftar-antrian-rajal', 'RawatJalan\RawatJalanController@getDaftarRegistrasiDokterRajal');
        Route::post('rawatjalan/save-batal-panggil', 'RawatJalan\RawatJalanController@pasienBatalPanggil');
        Route::post('rawatjalan/save-update-dokter-antrian', 'RawatJalan\RawatJalanController@updateDokterAntrian');
        Route::post('rawatjalan/save-pasien-jatuh', 'RawatJalan\RawatJalanController@saveIndikatorPasienJatuh');
        Route::post('rawatjalan/save-panggil', 'RawatJalan\RawatJalanController@savePanggilDokter');
        Route::post('rawatjalan/save-daftar-surat', 'RawatJalan\RawatJalanController@saveDaftarSurat');
        Route::get('rawatjalan/get-detail-pasien-surat', 'RawatJalan\RawatJalanController@getDetailpasienSurat');
        Route::get('rawatjalan/get-daftar-surat', 'RawatJalan\RawatJalanController@getDaftarSurat');
        Route::get('rawatjalan/get-daftarpasien-by-diagnosa', 'RawatJalan\RawatJalanController@getDaftarSurat');
        Route::get('rawatjalan/get-order-konsul', 'RawatJalan\RawatJalanController@getOrderKonsul');
        Route::post('rawatjalan/save-konsul-from-order', 'RawatJalan\RawatJalanController@saveKonsulFromOrder');
        Route::get('rawatjalan/get-combo', 'RawatJalan\RawatJalanController@getComboS');
        Route::get('rawatjalan/get-pemeriksaan-keluar-lab', 'RawatJalan\RawatJalanController@getPemeriksaanKeluarLab');
        Route::post('rawatjalan/save-pasien-kompleks', 'RawatJalan\RawatJalanController@savePasienKompleks');
        Route::post('rawatjalan/save-update-residence', 'RawatJalan\RawatJalanController@saveResidence');
        Route::get('rawatjalan/get-daftar-konsul-from-order', 'RawatJalan\RawatJalanController@getDaftarKonsulFromOrder');
        Route::post('rawatjalan/save-periksa', 'RawatJalan\RawatJalanController@saveSelesaiPeriksa');
        Route::post('rawatjalan/simpan-meninggal-pasien', 'RawatJalan\RawatJalanController@SimpanMeninggalPasien');
        // });
        // Route::group(['prefix' => 'registrasi'], function () {
        // 2019-12 penambahan arif awal
        Route::get('registrasi/get-apd-db-lama', 'Registrasi\RegistrasiController@getAntrianPasienDbLama');
        // 2019-12 penambahan arif akhir
        Route::get('registrasi/get-pasienbynocm', 'Registrasi\RegistrasiController@getPasienByNoCm');
        Route::get('registrasi/get-data-combo', 'Registrasi\RegistrasiController@getDataCombo');
        Route::get('registrasi/get-data-combo-reg-lama', 'Registrasi\RegistrasiController@getDataComboRegLama');
        Route::get('registrasi/get-kelasbyruangan', 'Registrasi\RegistrasiController@getKelasByRuangan');
        Route::get('registrasi/get-kamarbyruangankelas', 'Registrasi\RegistrasiController@getKamarByKelasRuangan');
        Route::get('registrasi/get-nobedbykamar', 'Registrasi\RegistrasiController@getNoBedByKamar');
        Route::post('registrasi/save-registrasipasien', 'Registrasi\RegistrasiController@saveRegistrasiPasien');
        Route::get('registrasi/get-rekanan-saeutik', 'Registrasi\RegistrasiController@getRekananSaeutik');
        Route::get('registrasi/get-pasienbynorec-pd', 'Registrasi\RegistrasiController@getPasienByNoRecPD');
        Route::get('registrasi/get-diagnosa-saeutik', 'Registrasi\RegistrasiController@getDiagnosaSaeutik');
        Route::post('registrasi/save-asuransipasien', 'Registrasi\RegistrasiController@saveAsuransiPasien');
        Route::get('registrasi/get-penjaminbykelompokpasien', 'Registrasi\RegistrasiController@getPenjaminByKelompokPasien');
        Route::get('registrasi/get-asuransipasienbynocm', 'Registrasi\RegistrasiController@getAsuransiPasienByNoCm');
        Route::get('registrasi/get-pasien-bynorec', 'Registrasi\RegistrasiController@getPasienByNoreg');
        Route::get('registrasi/get-history-pemakaianasuransi', 'Registrasi\RegistrasiController@getHistoryPemakaianAsuransi');
        Route::get('registrasi/get-pasien', 'Registrasi\RegistrasiController@getDaftarPasien');
        Route::get('registrasi/get-tglpulang-pasien', 'Registrasi\RegistrasiController@cekTglPulangPasien');
        Route::get('registrasi/get-apd', 'Registrasi\RegistrasiController@getAntrianPasien');
        Route::get('registrasi/get-history-pemakaianasuransi2', 'Registrasi\RegistrasiController@getHistoryPemakaianAsuransi2');
        Route::get('registrasi/cek-pasien-daftar-duakali', 'Registrasi\RegistrasiController@cekPasienDaftarDuaKali');
        Route::get('registrasi/cek-noregistrasi', 'Registrasi\RegistrasiController@cekNoregistrasi');
        Route::get('registrasi/update-noregistrasi', 'Registrasi\RegistrasiController@updateNoregis');
        Route::post('registrasi/update-kelas-ditanggung', 'Registrasi\RegistrasiController@updateAsuransiPasien');
        Route::get('registrasi/get-combo-kelas', 'Registrasi\RegistrasiController@getComboKelas');
        Route::get('registrasi/get-pemakaian-asuransi', 'Registrasi\RegistrasiController@getPemakaianAsuransi');
        Route::get('registrasi/get-bynocm', 'Registrasi\RegistrasiController@getPsnByNoCm');
        Route::get('registrasi/get-history-pemakaianasuransi-new', 'Registrasi\RegistrasiController@getHistoryPemakaianAsuransiNew');
        Route::get('registrasi/get-pasienonline-bynorec/{noreservasi}', 'Registrasi\RegistrasiController@getPasienOnlineByNorec');
        Route::get('registrasi/generate-noregistrasi', 'Registrasi\RegistrasiController@getMaxNoregistrasi');
        Route::post('registrasi/batal-periksa-delete', 'Registrasi\RegistrasiController@batalPeriksaDelete');
        Route::get('registrasi/get-combo-registrasi', 'Registrasi\RegistrasiController@getComboRegBaru');
        Route::get('registrasi/get-combo-address', 'Registrasi\RegistrasiController@getComboAddress');
        Route::get('registrasi/get-desa-kelurahan-paging', 'Registrasi\RegistrasiController@getDesaKelurahanPaging');
        Route::post('registrasi/save-pasien-fix', 'Registrasi\RegistrasiController@savePasienFix');
        Route::post('registrasi/save-pasien-before-foto', 'Registrasi\RegistrasiController@saveIdPasienDoang');
        Route::get('registrasi/get-data-combo-new', 'Registrasi\RegistrasiController@getDataComboNEW');
        Route::get('registrasi/get-dokter-part', 'Registrasi\RegistrasiController@getComboDokterPart');
        Route::get('registrasi/get-data-detail-pasien', 'Registrasi\RegistrasiController@getPasienDetailTea');
        Route::post('registrasi/save-batal-ranap', 'Registrasi\RegistrasiController@saveBatalRanap');
        Route::get('registrasi/cek-pasien-bayar', 'Registrasi\RegistrasiController@cekPasienBayar');
        Route::get('registrasi/daftar-riwayat-registrasi', 'Registrasi\RegistrasiController@getDaftarRiwayatRegistrasi');
        Route::post('registrasi/save-batal-ranap-rev', 'Registrasi\RegistrasiController@saveBatalRanapRev');
        Route::post('registrasi/save-batal-pindah-ruangan', 'Registrasi\RegistrasiController@saveBatalPindahRuangan');
        Route::get('registrasi/get-combo-pemakaian-asuransi', 'Registrasi\RegistrasiController@getDataComboAsuransiPasien');
        Route::get('registrasi/get-daftar-combo-pegawai-all', 'Registrasi\RegistrasiController@getDataPegawaiAll');
        Route::get('registrasi/cek-piutang-pasien', 'Registrasi\RegistrasiController@cekPiutangPasien');

        //###BAYI
        Route::get('registrasi/get-nocm-ibu', 'Registrasi\RegistrasiController@getNoCmIbu');
        Route::get('registrasi/get-desakelurahan-part', 'Registrasi\RegistrasiController@getDesaKelurahanPart');
        Route::get('registrasi/get-kecamatan-part', 'Registrasi\RegistrasiController@getKecamatanPart');
        Route::get('registrasi/get-kotakabupaten-part', 'Registrasi\RegistrasiController@getKotaKabupatenPart');
        Route::get('registrasi/get-propinsi-part', 'Registrasi\RegistrasiController@getPropinsiPart');
        Route::get('registrasi/get-alamat-bykodepos', 'Registrasi\RegistrasiController@getAlamatByKodePos');
        Route::get('registrasi/get-negara-part', 'Registrasi\RegistrasiController@getNegaraPart');
        Route::post('registrasi/save-bayi', 'Registrasi\RegistrasiController@savePasienBayi');
        Route::post('registrasi/generate-nocm', 'Registrasi\RegistrasiController@saveGenerateNocm');
        Route::get('registrasi/cek-table-generate-nocm', 'Registrasi\RegistrasiController@getNoCmBelumDipake');

        Route::post('registrasi/save-pasien', 'Registrasi\RegistrasiController@savePasien');
        Route::post('registrasi/update-pasien', 'Registrasi\RegistrasiController@updatePasien');
        Route::post('registrasi/update-false-pasien', 'Registrasi\RegistrasiController@updateStatusEnabledPasien');
        Route::get('registrasi/cek-pasien-bpjs-daftar', 'Registrasi\RegistrasiController@cekPasienBPJSDaftar');
        Route::get('registrasi/get-apd-detail', 'Registrasi\RegistrasiController@getApdDetail');
        Route::get('registrasi/get-kecamatanbydesa/{idDesa}', 'Registrasi\RegistrasiController@getKecamatanByDesaKelurahan');

        Route::get('registrasi/identifikasi-label', 'Registrasi\RegistrasiController@IdentifikasiLabel');
        Route::get('registrasi/identifikasi-buktiLayanan', 'Registrasi\RegistrasiController@IdentifikasiBuktiLayanan');
//            Route::get('registrasi/identifikasi-buktiLayanan', 'Registrasi\RegistrasiController@IdentifikasiBuktiLayanan');
        Route::get('registrasi/identifikasi-sum-list', 'Registrasi\RegistrasiController@IdentifikasiSummaryList');
        Route::get('registrasi/identifikasi-tracer', 'Registrasi\RegistrasiController@IdentifikasiTracer');
        Route::get('registrasi/identifikasi-sep', 'Registrasi\RegistrasiController@IdentifikasiSEP');
//            Route::get('registrasi/identifikasi-label', 'Registrasi\RegistrasiController@IdentifikasiLabel');
        Route::get('registrasi/identifikasi-kartu-pasien', 'Registrasi\RegistrasiController@IdentifikasiKartuPasien');
        Route::get('registrasi/identifikasi-rmk', 'Registrasi\RegistrasiController@IdentifikasiRMK');

        Route::post('registrasi/save-diagnosa-rmk', 'Registrasi\RegistrasiController@saveDiagnosaPasienRMK');
        Route::post('registrasi/update-data-emrpasien', 'Registrasi\RegistrasiController@updateNoCmInEmrPasienReg');
        Route::post('registrasi/confirm-pasien-online', 'Registrasi\RegistrasiController@ConfirmOnline');
        Route::post('registrasi/update-data-emrpasien-pd', 'Registrasi\RegistrasiController@updatePdInEmrPasien');
        Route::get('registrasi/get-data-pasien-reservasi', 'Registrasi\RegistrasiController@getPasienPerjanjian');
        Route::get('registrasi/get-combo-perjanjian', 'Registrasi\RegistrasiController@getComboPasienPerjanjian');


        Route::get('registrasi/daftar-antrian-pasien/get-combo', 'Registrasi\RegistrasiController@getComboAntrianPasienOperator');
        Route::get('registrasi/daftar-antrian-pasien/get-diagnosa', 'Registrasi\RegistrasiController@getDiagnosaDaftarAntrian');
        Route::get('registrasi/daftar-antrian-pasien/get-daftar-antrian-pasien', 'Registrasi\RegistrasiController@getDaftarAntrianPasienDiperiksa');
        Route::get('registrasi/daftar-antrian-pasien/get-data-pasien-mau-batal', 'Registrasi\RegistrasiController@getDataPasienMauBatal');
        Route::post('registrasi/daftar-antrian-pasien/save-batal-registrasi', 'Registrasi\RegistrasiController@SimpanBatalPeriksa');
        Route::post('registrasi/daftar-antrian-pasien/save-batal-panggil', 'Registrasi\RegistrasiController@pasienBatalPanggil');
        Route::get('registrasi/daftar-antrian-pasien/get-diagnosa-pasien-by-norecapd', 'Registrasi\RegistrasiController@getDiagnosaPasienByNorecAPD');
        Route::post('registrasi/daftar-antrian-pasien/delete-diagnosa-pasien', 'Registrasi\RegistrasiController@deleteDiagnosaPasien');
        Route::get('registrasi/daftar-antrian-pasien/get-data-diagnosa-pasien', 'Registrasi\RegistrasiController@getDiagnosaPasienByNoreg');
        Route::post('registrasi/daftar-antrian-pasien/save-diagnosa-array', 'Registrasi\RegistrasiController@saveArrDiagnosaPasien');
        Route::post('registrasi/daftar-antrian-pasien/update-kelas-antrian', 'Registrasi\RegistrasiController@saveUpdateKelasAPD');
        Route::get('registrasi/daftar-antrian-pasien/get-dokters-combos', 'Registrasi\RegistrasiController@getDokters');

        Route::get('registrasi/get-pelayanan-pasien', 'Registrasi\RegistrasiController@getPelayananPasienNonDetail');
        Route::get('registrasi/get-jenis-pelayanan', 'Registrasi\RegistrasiController@getJenisPelayananByNorecPd');
        Route::get('registrasi/get-status-close', 'Registrasi\RegistrasiController@getStatusClosePeriksa');
        Route::get('registrasi/tindakan/get-combo', 'Registrasi\RegistrasiController@getComboTindakanPendaftaran');
        Route::post('registrasi/simpan-mutasi-pasien', 'Registrasi\RegistrasiController@saveMutasiPasien');

        Route::get('registrasi/get-daftar-pasienbatal', 'Registrasi\RegistrasiController@getDaftarPasienBatal');
        Route::get('registrasi/get-daftar-pasien-meninggal', 'Registrasi\RegistrasiController@getDaftarPasienMeninggal');
        Route::get('registrasi/get-bayi-baru-lahir', 'Registrasi\RegistrasiController@getBayiBaruLahir');

        Route::get('registrasi/get-combo-riwayat-regis', 'Registrasi\RegistrasiController@getComboRiwayatRegis');
        Route::get('registrasi/get-pasien-by-nocm-riwayat-regis', 'Registrasi\RegistrasiController@getPasienByNoCmRiwayatRegis');
        Route::get('registrasi/get-antrian-by-nocm-rev', 'Registrasi\RegistrasiController@getAntrianPasienByNocmRev');
        Route::get('registrasi/get-icd-9', 'Registrasi\RegistrasiController@getIcd9');
        Route::get('registrasi/get-diagnosa-9-by-noreg', 'Registrasi\RegistrasiController@getDiagnosaPasienByNoregICD9');
        Route::get('registrasi/get-diagnosa-10-by-noreg', 'Registrasi\RegistrasiController@getDiagnosaPasienByNoreg');
        Route::get('registrasi/get-pasien-daftar-by-noreg', 'Registrasi\RegistrasiController@getPasienDaftarByNoreg');
        Route::post('registrasi/delete-diagnosa-tindakan-pasien', 'Registrasi\RegistrasiController@deleteDiagnosaTindakanPasien');
        Route::post('registrasi/save-diagnosa-pasien', 'Registrasi\RegistrasiController@saveDiagnosaPasien');
        Route::post('registrasi/save-diagnosa-tindakan-pasien', 'Registrasi\RegistrasiController@saveDiagnosaTindakanPasien');
        Route::get('registrasi/get-anamnesis', 'Registrasi\RegistrasiController@getAnamnesis');
        Route::get('registrasi/get-resume-medis-inap/{nocm}', 'Registrasi\RegistrasiController@getResumeMedisInap');

        Route::get('registrasi/get-image', 'Registrasi\RegistrasiController@getImage');
        Route::get('registrasi/store-image-to-folder/{nocm}', 'Registrasi\RegistrasiController@storeImageToFolder');
        Route::post('registrasi/save-image', 'Registrasi\RegistrasiController@saveImage');
        Route::post('registrasi/hapus-pemakaian-asuransi', 'Registrasi\RegistrasiController@hapusPemakaianAsuransi');
        Route::get('registrasi/get-hasil-pengunjung', 'Registrasi\RegistrasiController@getHasilRegistrasi');

        Route::post('registrasi/merge-nomor-rm', 'Registrasi\RegistrasiController@saveMergeNoRM');
        Route::post('registrasi/update-sep-igd', 'Registrasi\RegistrasiController@updateSEPIGD');
        Route::get('registrasi/get-daftar-rujukan', 'Registrasi\RegistrasiController@getDaftarRujukan');
        Route::get('registrasi/get-ruanganbykodebpjs/{kode}', 'Registrasi\RegistrasiController@getRuanganByKodeInternal');
        Route::post('registrasi/update-rujukan-transdata', 'Registrasi\RegistrasiController@updateRujukanTransdata');
        Route::post('registrasi/save-adminsitrasi', 'Registrasi\RegistrasiController@saveAdministrasi');
        Route::get('registrasi/get-detail-registrasi-pasien', 'Registrasi\RegistrasiController@getDetailRegistrasiPasien');

        Route::get('registrasi/cek-nobpjs', 'Registrasi\RegistrasiController@cekNoBPJSpasienBaru');
        // Route::group(['prefix' => 'daftar-registrasi'], function () {
        // 2019-12 penambahan arif awal
        Route::get('registrasi/daftar-registrasi/get-daftar-registrasi-pasien-db-lama', 'Registrasi\RegistrasiController@getDaftarRegistrasiPasienOperatordblama');
        Route::get('registrasi/daftar-registrasi/get-riwayat-pasien-db-lama-by-rm', 'Registrasi\RegistrasiController@getRiwayatPasiendblamaByRm');
        Route::get('registrasi/daftar-registrasi/get-data-combo-operator-db-lama', 'Registrasi\RegistrasiController@getDataComboOperatordblama');
        // 2019-12 penambahan arif akhir
        Route::get('registrasi/daftar-registrasi/get-daftar-registrasi-pasien', 'Registrasi\RegistrasiController@getDaftarRegistrasiPasienOperator');
        Route::get('registrasi/daftar-registrasi/get-data-combo-operator', 'Registrasi\RegistrasiController@getDataComboOperator');
        Route::get('registrasi/daftar-registrasi/get-data-diagnosa', 'Registrasi\RegistrasiController@getDiagnosaDaftarAntrian');
        Route::post('registrasi/daftar-registrasi/update-tgl-pulang', 'Registrasi\RegistrasiController@updateTanggalPulang');
        Route::post('registrasi/daftar-registrasi/update-nosep', 'Registrasi\RegistrasiController@updateNoSEP');
        Route::post('registrasi/daftar-registrasi/update-dokter', 'Registrasi\RegistrasiController@simpanUpdateDokters');
        Route::get('registrasi/daftar-registrasi/get-data-diagnosa-pasien', 'Registrasi\RegistrasiController@getDiagnosaPasienByNoreg');
        Route::post('registrasi/daftar-registrasi/save-diagnosa-rmk', 'Registrasi\RegistrasiController@saveDiagnosaPasienRMK');
        Route::get('registrasi/daftar-registrasi/get-data-pasien-mau-batal', 'Registrasi\RegistrasiController@getDataPasienMauBatal');
        Route::post('registrasi/daftar-registrasi/save-batal-registrasi', 'Registrasi\RegistrasiController@SimpanBatalPeriksa');
        Route::get('registrasi/daftar-registrasi/get-acc-number-radiologi', 'Registrasi\RegistrasiController@getAccNumberRadiologi');
        Route::get('registrasi/daftar-registrasi/get-apd', 'Registrasi\RegistrasiController@getAPD');
        Route::get('registrasi/daftar-registrasi/get-daftar-order-hasil-lab', 'Registrasi\RegistrasiController@getDaftarHasilLab');
        Route::get('registrasi/daftar-registrasi/get-daftar-order-hasil-rad', 'Registrasi\RegistrasiController@getDaftarHasilRad');
        Route::get('registrasi/get-norec-apd', 'Registrasi\RegistrasiController@getNorecAPD');
        Route::get('registrasi/daftar-registrasi/get-daftar-expertise-rad', 'Registrasi\RegistrasiController@getDaftarExpRad');

        // });
        // Route::group(['prefix' => 'dokumenrm'], function () {
        Route::get('registrasi/dokumenrm/get-pasien-by-nocm', 'Registrasi\KendaliDokumenRMController@getPasienByNoCm');
        Route::get('registrasi/dokumenrm/get-daftar-registrasi', 'Registrasi\KendaliDokumenRMController@getPasienDaftar');
        Route::get('registrasi/dokumenrm/get-daftar-kendali-dokumen', 'Registrasi\KendaliDokumenRMController@getDaftarKendaliDokumen');
        Route::post('registrasi/dokumenrm/save-kendali-dokumen', 'Registrasi\KendaliDokumenRMController@saveKendaliDokRM');
        Route::get('registrasi/dokumenrm/get-data-combo-kdrm', 'Registrasi\KendaliDokumenRMController@getDataCombo');
        Route::get('registrasi/dokumenrm/get-data-kendali', 'Registrasi\KendaliDokumenRMController@getDataTambahKendali');
        Route::get('registrasi/dokumenrm/get-data', 'Registrasi\KendaliDokumenRMController@getDataKendali');
        Route::get('registrasi/dokumenrm/get-laporan-tracer', 'Registrasi\KendaliDokumenRMController@getLaporanTracer');
        // });
        // Route::group(['prefix' => 'orderlabel'], function () {
        Route::get('registrasi/orderlabel/get-pasienbynoreg', 'Registrasi\OrderLabelController@getPasienDaftarByNoreg');
        Route::post('registrasi/orderlabel/save-order-label', 'Registrasi\OrderLabelController@saveOrderLabel');
        Route::post('registrasi/orderlabel/delete-order-label', 'Registrasi\OrderLabelController@deleteOrderLabel');
        Route::get('registrasi/orderlabel/get-daftar-permintaan', 'Registrasi\OrderLabelController@getDaftarPermintaan');
        Route::get('registrasi/orderlabel/get-data-combo', 'Registrasi\OrderLabelController@getDataCombo');
        Route::post('registrasi/orderlabel/save-update-order-label', 'Registrasi\OrderLabelController@simpanUpdatePenerimaLabel');
        // });
        // Route::group(['prefix' => 'laporan'], function () {
        Route::get('registrasi/laporan/get-data-combo-laporan', 'Registrasi\LaporanRegistrasiController@getDataCombo');
        Route::get('registrasi/laporan/get-laporan-pasien-daftar', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienDaftar');
        Route::get('registrasi/laporan/get-data-pasien-masuk-ranap', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienMasukRawatInap');
        Route::get('registrasi/laporan/get-data-pasien-keluar-ruangan-ranap', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienKeluarRuanganRawatInap');
        Route::get('registrasi/laporan/get-data-pasien-pindahan', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienPindahan');
        Route::get('registrasi/laporan/get-data-pasien-meninggal', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienMeninggalRuangan');
        Route::get('registrasi/laporan/get-data-pasien-informasi-ruangan', 'Registrasi\LaporanRegistrasiController@getDataLaporanInformasiRuangan');
        Route::get('registrasi/laporan/get-data-pasien-dipindahan', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienDipindahankan');
        Route::get('registrasi/laporan/get-laporan-pasien-pulang', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienPulang');
        Route::get('registrasi/laporan/get-laporan-pasien-pulangNew', 'Registrasi\LaporanRegistrasiController@getDataLaporanPasienPulangNew');
        Route::post('registrasi/laporan/post-laporan-pasien-pulang', 'Registrasi\LaporanRegistrasiController@CetakLaporanPasienPulang');
        Route::get('registrasi/laporan/get-data-lap-pendapatan-poli', 'Registrasi\LaporanRegistrasiController@getDataLaporanPendapatanPoli');
        Route::get('registrasi/laporan/get-detail-layanan', 'Registrasi\LaporanRegistrasiController@getLaporanLayanan');
        Route::get('registrasi/laporan/get-data-jadwaldokter-lap', 'Registrasi\LaporanRegistrasiController@getJadwalDokter');
        Route::post('registrasi/laporan/post-laporan-layanan', 'Registrasi\LaporanRegistrasiController@CetakLaporanLayanan');
        Route::get('registrasi/laporan/get-produk-part', 'Registrasi\LaporanRegistrasiController@getDataProduk');
        Route::get('registrasi/laporan/get-data-lap-pasien-baru-lama', 'Registrasi\LaporanRegistrasiController@GetLaporanPasienBaruLama');

        Route::get('registrasi/laporan/get-produk-mapping-rl', 'Registrasi\LaporanRekamMedisController@getProdukMapLaporanRL');
        Route::get('registrasi/laporan/get-mapping-rl', 'Registrasi\LaporanRekamMedisController@getMapLaporanRL');
        Route::post('registrasi/laporan/save-mapping-rl', 'Registrasi\LaporanRekamMedisController@SaveMappingRl');
        Route::post('registrasi/laporan/delete-mapping-rl', 'Registrasi\LaporanRekamMedisController@deleteMapProdukToLaporanRL');
        Route::get('registrasi/laporan/get-combo-mapping-rl', 'Registrasi\LaporanRekamMedisController@getComboMappingRL');

        Route::get('registrasi/laporan/get-laporan-rl12', 'Registrasi\LaporanRekamMedisController@getDataRL31RawatInap');
        Route::get('registrasi/laporan/get-laporan-rl31', 'Registrasi\LaporanRekamMedisController@getDataRL31RawatInap');
        Route::get('registrasi/laporan/get-laporan-rl32', 'Registrasi\LaporanRekamMedisController@getLaporanRL32RawatDarurat');
        Route::get('registrasi/laporan/get-laporan-rl33', 'Registrasi\LaporanRekamMedisController@getKegiatanKesehatanGigidanMulut');
        Route::get('registrasi/laporan/get-laporan-rl34', 'Registrasi\LaporanRekamMedisController@getLaporanRL34Kebidanan');
        Route::get('registrasi/laporan/get-laporan-rl35', 'Registrasi\LaporanRekamMedisController@getLaporanRL35Perinatologi');
        Route::get('registrasi/laporan/get-laporan-rl36', 'Registrasi\LaporanRekamMedisController@getLaporanRL36Pembedahan');
        Route::get('registrasi/laporan/get-laporan-rl37', 'Registrasi\LaporanRekamMedisController@getLaporanRL37');
        Route::get('registrasi/laporan/get-laporan-rl38', 'Registrasi\LaporanRekamMedisController@getPemeriksaanLab');
        Route::get('registrasi/laporan/get-laporan-rl39', 'Registrasi\LaporanRekamMedisController@getPelayananRehab');
        Route::get('registrasi/laporan/get-laporan-rl310', 'Registrasi\LaporanRekamMedisController@getLaporanRL310Khusus');
        Route::get('registrasi/laporan/get-laporan-rl311', 'Registrasi\LaporanRekamMedisController@getLaporanRL311KesehatanJiwa');
        Route::get('registrasi/laporan/get-laporan-rl312', 'Registrasi\LaporanRekamMedisController@getLaporanRL312KeluargaBerencana');
        Route::get('registrasi/laporan/get-laporan-rl313', 'Registrasi\LaporanRekamMedisController@getPengadaanObat');
        Route::get('registrasi/laporan/get-laporan-rl313-2', 'Registrasi\LaporanRekamMedisController@getPelayananResep');
        Route::get('registrasi/laporan/get-laporan-rl314', 'Registrasi\LaporanRekamMedisController@getRL314Rujukan');
        Route::get('registrasi/laporan/get-laporan-rl315', 'Registrasi\LaporanRekamMedisController@getRL315CaraBayar');
        Route::get('registrasi/laporan/get-laporan-rl4a', 'Registrasi\LaporanRekamMedisController@getLaporanRL4aRawatInap');
        Route::get('registrasi/laporan/get-laporan-rl4b', 'Registrasi\LaporanRekamMedisController@getLaporanRL4b');
        Route::get('registrasi/laporan/get-laporan-rl51', 'Registrasi\LaporanRekamMedisController@getDataLaporanRL51');
        Route::get('registrasi/laporan/get-laporan-rl52', 'Registrasi\LaporanRekamMedisController@getDataLaporanRL52');
        Route::get('registrasi/laporan/get-laporan-rl53', 'Registrasi\LaporanRekamMedisController@getDataLaporanRL53');
        Route::get('registrasi/laporan/get-laporan-rl54', 'Registrasi\LaporanRekamMedisController@getDataLaporanRL54');

        Route::get('registrasi/laporan/get-data-lap-pasien-masuk', 'Registrasi\LaporanRekamMedisController@getDataLaporanPasienMasuk');
        Route::get('registrasi/laporan/get-data-lap-pasien-keluar', 'Registrasi\LaporanRekamMedisController@getDataLaporanPasienKeluar');
        Route::get('registrasi/laporan/get-data-lap-pasien-pindahan', 'Registrasi\LaporanRekamMedisController@getLaporanPasienPindahan');
        Route::get('registrasi/laporan/get-data-lap-rincian-pelayanan', 'Registrasi\LaporanRegistrasiController@getDataLaporanRincianPelayanan');
        Route::get('registrasi/laporan/get-data-lap-pengunjung', 'Registrasi\LaporanPengunjungController@getLaporanPengunjung');
        Route::get('registrasi/laporan/get-combo-box-laporan-pengunjung', 'Registrasi\LaporanPengunjungController@getDataCombo');
        Route::get('registrasi/laporan/get-data-lap-darah', 'Registrasi\LaporanPengunjungController@getLapDarah');

        Route::get('registrasi/laporan/get-combo-box-laporan-summary', 'Registrasi\RegistrasiController@getDataComboSummary');
        Route::get('registrasi/laporan/get-data-lap-summary-kunjungan', 'Registrasi\RegistrasiController@getLaporanSummaryKunjungan');
        Route::get('registrasi/laporan/get-data-lap-summary-pendidikan', 'Registrasi\RegistrasiController@getLaporanSummaryPendidikan');
        Route::get('registrasi/laporan/get-data-lap-summary-daerah', 'Registrasi\RegistrasiController@getLaporanSummaryDaerah');
        Route::get('registrasi/laporan/get-data-lap-summary-pekerjaan', 'Registrasi\RegistrasiController@getLaporanSummaryPekerjaan');
        Route::get('registrasi/laporan/get-data-lap-summary-agama', 'Registrasi\RegistrasiController@getLaporanSummaryAgama');
        Route::get('registrasi/laporan/get-data-lap-summary-kujungan-tahunan', 'Registrasi\RegistrasiController@getLaporanSummaryKunjunganTahunan');
        Route::get('registrasi/laporan/get-data-lap-pengunjung-tindakan', 'Registrasi\LaporanPengunjungController@getLaporanPengunjungTindakan');
        Route::get('registrasi/laporan/get-data-lap-kegiatan-rj', 'Registrasi\RegistrasiController@getLaporanKegiatanRJ');
        Route::get('registrasi/laporan/get-data-lap-pengunjung-pemeriksaan', 'Registrasi\LaporanPengunjungController@getLaporanPengunjungPemeriksaan');
        Route::get('registrasi/laporan/get-lap-paseinrj-per-dokpemeriksa', 'Registrasi\LaporanPengunjungController@getLaporanPasienRJPerDokterPemeriksa');
        Route::get('registrasi/laporan/get-data-lap-pasien-dpjp', 'Registrasi\LaporanPengunjungController@getLaporanPasienDPJP');
        Route::get('registrasi/laporan/get-laporan-topten-diagnosa', 'Registrasi\RegistrasiController@getTopTenDiagnosa');
        Route::get('registrasi/laporan/get-data-lap-kinerja-bayar-rj', 'Registrasi\RegistrasiController@getLaporanKinerjaBayarRJ');
        Route::get('registrasi/laporan/get-data-lap-kinerja-bayar-ri', 'Registrasi\RegistrasiController@getLaporanKinerjaBayarRI');
        Route::get('registrasi/laporan/get-data-lap-kinerja-kun-igd', 'Registrasi\RegistrasiController@getLaporanKinerjaKunjunganIGD');
        Route::get('registrasi/laporan/get-data-lap-kinerja-pengunjung', 'Registrasi\RegistrasiController@getLaporanKinerjaPengunjung');
        Route::get('registrasi/laporan/get-data-lap-kinerja-kunjungan', 'Registrasi\RegistrasiController@getLaporanKinerjaKunjungan');
        Route::get('registrasi/laporan/get-data-lap-kinerja-rawat-inap', 'Registrasi\RegistrasiController@getLaporanKinerjaRawatInap');
        Route::get('registrasi/laporan/get-data-lap-summary-usia', 'Registrasi\RegistrasiController@getLaporanSummaryUsia');

        Route::get('registrasi/laporan/get-data-lap-demografi-ri-kel', 'Registrasi\RegistrasiController@getLaporanDemoRIKelompok');
        Route::get('registrasi/laporan/get-data-lap-demografi-ri-pen', 'Registrasi\RegistrasiController@getLaporanDemoRIPendidikan');
        Route::get('registrasi/laporan/get-data-lap-demografi-ri-daer', 'Registrasi\RegistrasiController@getLaporanDemoRIDaerah');
        Route::get('registrasi/laporan/get-data-lap-demografi-ri-pek', 'Registrasi\RegistrasiController@getLaporanDemoRIPekerjaan');
        Route::get('registrasi/laporan/get-data-lap-demografi-ri-usia', 'Registrasi\RegistrasiController@getLaporanDemoRIUsia');
        Route::get('registrasi/laporan/get-data-lap-demografi-ri-agama', 'Registrasi\RegistrasiController@getLaporanDemoRIAgama');
        Route::get('registrasi/laporan/get-data-lap-demografi-ri-item', 'Registrasi\RegistrasiController@getLaporanDemoRIItem');
        Route::get('registrasi/laporan/get-data-lap-target-realisasi', 'Registrasi\RegistrasiController@getLaporanTargetRealisasi');
        Route::get('registrasi/laporan/get-data-lap-indikator-pelayanan', 'Registrasi\RegistrasiController@getLaporanIndikatorPelayanan');
        Route::get('registrasi/laporan/get-data-lap-jumlah-pasien-cara', 'Registrasi\RegistrasiController@getLaporanJumlahPasienDanCaraBayar');
        Route::get('registrasi/laporan/get-data-lap-jumlah-pasien-cara-igd', 'Registrasi\RegistrasiController@getLaporanJumlahPasienDanCaraBayarIGD');
        Route::get('registrasi/laporan/get-data-lap-lab-tahunan', 'Registrasi\RegistrasiController@getLaporanLabTahunan');
        Route::get('registrasi/laporan/get-lab-mutu-rj', 'Registrasi\RegistrasiController@getLaporanMutuRJ');
        Route::get('registrasi/laporan/get-lap-mutu-rad', 'Registrasi\RegistrasiController@getLaporanMutuRad');

        Route::get('registrasi/laporan/get-kinerja-pelayanan-ranap', 'Registrasi\LaporanRegistrasiController@getKinerjaPelayananRanap');
        Route::get('registrasi/laporan/get-kinerja-pelayanan-ranap-tahunan', 'Registrasi\LaporanRegistrasiController@getKinerjaPelayananRanapTahunan');
        // });


        // });
        // Route::group(['prefix' => 'remunerasi'], function () {
        Route::get('remunerasi/get-combo', 'Remunerasi\RemunerasiController@getCombo');
        Route::get('remunerasi/get-jasa-layanan-pagu-rev2', 'Remunerasi\RemunerasiController@getRemunerasiJP1_rev2');
        Route::get('remunerasi/get-hitung-jasa-pelayanan-satu-rev2', 'Remunerasi\RemunerasiController@getHitungJP1Rev2');
        Route::get('remunerasi/get-daftar-jasa-layanan-pagu-rev2', 'Remunerasi\RemunerasiController@getDaftarJP1Rev2');
        Route::get('remunerasi/get-detail-jasa-layanan-pagu', 'Remunerasi\RemunerasiController@getDetailJP1');
        Route::get('remunerasi/get-list-pegawai', 'Remunerasi\RemunerasiController@getListPegawai');
        Route::get('remunerasi/get-data-combo', 'Remunerasi\RemunerasiController@getDataCombo');
        Route::get('remunerasi/get-detail-pot-remun', 'Remunerasi\RemunerasiController@getDetailPotonganRemunPegawai');
        Route::get('remunerasi/get-list-pegawai-perjenispagu', 'Remunerasi\RemunerasiController@getPegawaiByJenisPagu');
        Route::get('remunerasi/get-list-pegawai-perdetailjenispagu', 'Remunerasi\RemunerasiController@getPegawaiByDetailJenisPagu');
        Route::get('remunerasi/get-hitung-jasa-pelayanan-satu', 'Remunerasi\RemunerasiController@getHitungJP1');
        Route::get('remunerasi/get-daftar-remunerasi-pegawai', 'Remunerasi\RemunerasiController@getDaftarRemunPegawai');
        Route::get('remunerasi/get-daftar-closing', 'Remunerasi\RemunerasiController@getDataClosing');
        Route::get('remunerasi/get-detail-remun_rc', 'Remunerasi\RemunerasiController@getDetailRemunRCCC');
        Route::get('remunerasi/get-komponenharga-pelayanan', 'Remunerasi\RemunerasiController@getKomponenHargaPelayanan');
        Route::get('remunerasi/get-detail-remun-pegawai', 'Remunerasi\RemunerasiController@GetDetailRemunPegawai');
        Route::get('remunerasi/get-daftar-index-pegawai', 'Remunerasi\RemunerasiController@getDaftarPerhitunganIndexPegawai');
        Route::get('remunerasi/get-data-combo-laporan', 'Remunerasi\RemunerasiController@getDataComboLaporanRemun');
        Route::get('remunerasi/get-detail-laporan-remun', 'Remunerasi\RemunerasiController@getDataDetailLaporanRemunerasi');
        Route::get('remunerasi/get-rekap-laporan-remun', 'Remunerasi\RemunerasiController@getDataRekapLaporanRemunerasi');
        Route::get('remunerasi/get-detail-laporan-remun-dokter', 'Remunerasi\RemunerasiController@getDataDetailLaporanRemunerasiDokter');
        Route::get('remunerasi/get-detail-laporan-remun-paramedis', 'Remunerasi\RemunerasiController@getDataDetailLaporanRemunerasiParamedis');


        Route::post('remunerasi/save-closing-jasa-pelayanan-satu', 'Remunerasi\RemunerasiController@saveDetailPegawaiPagu');
        Route::post('remunerasi/save-hapus-map-jenis-pagu', 'Remunerasi\RemunerasiController@hapusMapJenisPagutoPegawai');
        Route::post('remunerasi/save-potongan-remun', 'Remunerasi\RemunerasiController@savePotonganRemun');
        // Route::post('remunerasi/save-struk-pagu-rev2', 'Remunerasi\RemunerasiController@saveStrukPaguWithDetailRev2');
        Route::post('remunerasi/save-struk-pagu-rev2', 'Remunerasi\RemunerasiController@saveRemunerasiJP1_rev3_2020');
        Route::post('remunerasi/save-closing-pr', 'Remunerasi\RemunerasiController@saveClosingPOSTREMUN');
        Route::post('remunerasi/save-closing-rcd', 'Remunerasi\RemunerasiController@saveClosingRCDokter');
        Route::post('remunerasi/save-closing-rc', 'Remunerasi\RemunerasiController@saveClosingRC');
        Route::post('remunerasi/save-closing-cc', 'Remunerasi\RemunerasiController@saveClosingCC');
        Route::post('remunerasi/save-closing-ccs', 'Remunerasi\RemunerasiController@saveClosingCCStaff');
        Route::post('remunerasi/save-map-jenis-pagu-topegawai', 'Remunerasi\RemunerasiController@saveMapJenisPaguToPegawai');
        // });

        Route::post('reservasi/update-nocmfk-antrian-registrasi', 'ReservasiOnline\ReservasiOnlineController@updateNoCmInAntrianRegistrasi');
        // Route::group(['prefix' => 'reservasionline'], function () {
        Route::get('reservasionline/get-list-data', 'ReservasiOnline\ReservasiOnlineController@getComboReservasi');
        Route::get('reservasionline/get-daftar-slotting', 'ReservasiOnline\ReservasiOnlineController@getDaftarSlotting');
        Route::post('reservasionline/save-slotting', 'ReservasiOnline\ReservasiOnlineController@saveSlotting');
        Route::post('reservasionline/update-data-status-reservasi', 'ReservasiOnline\ReservasiOnlineController@UpdateStatConfirm');
        Route::post('reservasionline/update-nocmfk-antrian-registrasi', 'ReservasiOnline\ReservasiOnlineController@updateNoCmInAntrianRegistrasi');
        Route::get('reservasionline/get-history', 'ReservasiOnline\ReservasiOnlineController@getHistoryReservasi');
        Route::get('reservasionline/get-pasien/{nocm}/{tgllahir}', 'ReservasiOnline\ReservasiOnlineController@getPasienByNoCmTglLahir');
        Route::get('reservasionline/get-libur', 'ReservasiOnline\ReservasiOnlineController@getLiburSlotting');
        Route::get('reservasionline/get-bank-account', 'ReservasiOnline\ReservasiOnlineController@getNomorRekening');
        Route::post('reservasionline/save', 'ReservasiOnline\ReservasiOnlineController@saveReservasi');
        Route::post('reservasionline/delete', 'ReservasiOnline\ReservasiOnlineController@deleteReservasi');
        Route::get('reservasionline/cek-reservasi-satu', 'ReservasiOnline\ReservasiOnlineController@cekReservasiDipoliYangSama');
        Route::get('reservasionline/get-slotting-by-ruangan-new/{kode}/{tgl}', 'ReservasiOnline\ReservasiOnlineController@getSlottingByRuanganNew');
        Route::get('reservasionline/get-slot-available', 'ReservasiOnline\ReservasiOnlineController@getDaftarSlottingAktif');
        Route::get('reservasionline/tagihan/get-pasien/{noregistrasi}', 'ReservasiOnline\ReservasiOnlineController@getPasienByNoRegistrasi'); //done
        Route::get('reservasionline/get-tagihan-pasien/{noregistasi}', 'ReservasiOnline\ReservasiOnlineController@getTagihanEbilling');
        Route::get('reservasionline/get-setting', 'ReservasiOnline\ReservasiOnlineController@getSetting');
        Route::get('reservasionline/daftar-riwayat-registrasi', 'ReservasiOnline\ReservasiOnlineController@getDaftarRiwayatRegistrasi');
        Route::get('reservasionline/cek-pasien-baru-by-nik/{nik}', 'ReservasiOnline\ReservasiOnlineController@cekPasienByNik');
        Route::post('reservasionline/save-libur', 'ReservasiOnline\ReservasiOnlineController@saveLibur');
        Route::post('reservasionline/delete-libur', 'ReservasiOnline\ReservasiOnlineController@deleteLibur');

        // });
        // Route::group(['prefix' => 'sanitasi'], function () {
        Route::get('sanitasi/get-tempat-tidur', 'Sanitasi\SanitasiController@getTempatTidur');
        Route::post('sanitasi/save-signdate', 'Sanitasi\SanitasiController@SaveDataSignDate');
        Route::post('sanitasi/save-alokasistaff', 'Sanitasi\SanitasiController@SaveDataAlokasiStaff');
        Route::post('sanitasi/save-worklist', 'Sanitasi\SanitasiController@SaveDataWorkList');
        Route::post('sanitasi/save-inspeksi', 'Sanitasi\SanitasiController@SaveDataInspeksi');
        Route::post('sanitasi/save-startdate', 'Sanitasi\SanitasiController@SaveDataStartDate');
        Route::post('sanitasi/save-duedate', 'Sanitasi\SanitasiController@SaveDataDueDate');
        Route::post('sanitasi/save-tambah-kegiatan', 'Sanitasi\SanitasiController@saveTambahKegiatan');
        Route::get('sanitasi/get-data-sanitasi', 'Sanitasi\SanitasiController@getDataSanitasi');
//            Route::get('sanitasi/get-combo-sanitasi','Sanitasi\SanitasiController@getComboSanitasi');
        Route::get('sanitasi/get-combo-pegawai', 'Sanitasi\SanitasiController@getDataPegawaiGeneral');
        Route::get('sanitasi/get-combo-jenispek', 'Sanitasi\SanitasiController@getDataCombo');
        Route::post('sanitasi/save-data-signdate', 'Sanitasi\SanitasiController@SaveSignDate');
        Route::post('sanitasi/save-data-alokasistaff', 'Sanitasi\SanitasiController@SaveAlokasistaff');
        Route::post('sanitasi/save-data-worklist', 'Sanitasi\SanitasiController@SaveWorklist');
        Route::post('sanitasi/save-data-startdate', 'Sanitasi\SanitasiController@SaveStartDate');
        Route::get('sanitasi/get-jenis-layanan-sanitasi', 'Sanitasi\SanitasiController@getJenisLayananSanitasi');
        Route::post('sanitasi/save-permohonan-sanitasi', 'Sanitasi\SanitasiController@SavePermohonanSanitasi');
        Route::get('sanitasi/get-daftar-permohonan-sanitasi', 'Sanitasi\SanitasiController@getDaftarPermohonanSanitasi');
        Route::post('sanitasi/save-jenis-layanan-sanitasi', 'Sanitasi\SanitasiController@SaveDataJenisLayananSanitasi');
        Route::get('sanitasi/get-status-pekerjaan-sanitasi', 'Sanitasi\SanitasiController@getStatusPekerjaanSanitasi');
        Route::post('sanitasi/hapus-permohonan-sanitasi', 'Sanitasi\SanitasiController@HapusPermohonanSanitasi');

        // });
        // Route::group(['prefix' => 'ipsrs'], function () {
        Route::get('ipsrs/get-daftar-permohonan', 'IPSRS\IPSRSController@getDaftarIPSRS');
        Route::post('ipsrs/save-permohonan', 'IPSRS\IPSRSController@SavePermohonan');
        Route::post('ipsrs/save-alokasistaff', 'IPSRS\IPSRSController@SaveDataAlokasiStaff');
        Route::post('ipsrs/save-worklist', 'IPSRS\IPSRSController@SaveDataWorkList');
        Route::post('ipsrs/save-inspeksi', 'IPSRS\IPSRSController@SaveDataInspeksi');
        Route::post('ipsrs/save-identifikasi-kerusakan', 'IPSRS\IPSRSController@SaveDataIdentifikasi');
        Route::post('ipsrs/save-jenis-kerusakan', 'IPSRS\IPSRSController@SaveDataJenisKerusakan');
        Route::get('ipsrs/get-jenis-pekerjaan', 'IPSRS\IPSRSController@getJenisPekerjaan');
        Route::get('ipsrs/get-status-pekerjaan', 'IPSRS\IPSRSController@getStatusPekerjaan');
        Route::post('ipsrs/save-status-pekerjaan', 'IPSRS\IPSRSController@SaveDataStatus');
        Route::get('ipsrs/send-notif-whatsapp', 'IPSRS\IPSRSController@sendNotifWhatsapp');
        Route::get('ipsrs/get-jenis-alat', 'IPSRS\IPSRSController@getJenisAlat');
        Route::post('ipsrs/save-jenis-alat', 'IPSRS\IPSRSController@SaveDataJenisAlat');
        Route::post('ipsrs/hapus-permohonan-ipsrs', 'IPSRS\IPSRSController@HapusPermohonanIPSRS');
        Route::post('ipsrs/save-pengerjaan-permohonan', 'IPSRS\IPSRSController@SavePengerjaanPermohonan');
        Route::post('ipsrs/hapus-pengerjaan-permohonan', 'IPSRS\IPSRSController@SaveHapusPengerjaanPermohonan');
        Route::get('ipsrs/get-ruangan', 'IPSRS\IPSRSController@getComboRuanganIPSRS');

        // });
        // Route::group(['prefix' => 'sdm'], function () {
        /*GET*/
        Route::get('sdm/get-data-combo-sdm', 'SDM\SumberDayaManusiaController@getComboPegawaiSdm');
        Route::get('sdm/get-data-pegawai-all-sdm', 'SDM\SumberDayaManusiaController@getDaftarPegawai');
        Route::get('sdm/get-data-detail-pegawai', 'SDM\SumberDayaManusiaController@getDetailPegawai');
        Route::get('sdm/get-data-urut-kepangakatan-pegawai', 'SDM\SumberDayaManusiaController@getDataDaftarUrutKepangkatan');
        Route::get('sdm/get-data-jadwal-kerja-pegawai-ruangan', 'SDM\SumberDayaManusiaController@getDataJadwalKerjaRuangan');
        Route::get('sdm/get-data-sip', 'SDM\SumberDayaManusiaController@getDataSip');
        Route::get('sdm/get-data-str', 'SDM\SumberDayaManusiaController@getDataStr');
        Route::get('sdm/download-data-sipstr', 'SDM\SumberDayaManusiaController@downloadFileSipStr');
        Route::get('sdm/data-pegawai-sudah-pensiun', 'SDM\SumberDayaManusiaController@getPegawaiSudahPensiun');
        Route::get('sdm/get-data-pensiun', 'SDM\SumberDayaManusiaController@getPegawaiPensiun');
        Route::get('sdm/data-combo-pensiun', 'SDM\SumberDayaManusiaController@getComboPensiun');
        Route::get('sdm/get-jadwal-perbulan-pegawai', 'SDM\SumberDayaManusiaController@getJadwalBulananPegawai');
        Route::get('sdm/get-combo-pegawai-jadwal-all', 'SDM\SumberDayaManusiaController@getComboPegawaiJadwal');
        Route::get('sdm/get-monitoring-absensi-pegawai', 'SDM\SumberDayaManusiaController@getMonitoringAbsensiPegawai');
        Route::get('sdm/get-data-keluarga-pegawai', 'SDM\SumberDayaManusiaController@getDataKeluarga');
        Route::get('sdm/get-rekap-pegawai', 'SDM\SumberDayaManusiaController@getRekapPegawai');
        Route::get('sdm/get-data-urut-informasi-jabatan', 'SDM\SumberDayaManusiaController@getDataInformasijabatanStruktural');
        Route::get('sdm/get-pegawai-all', 'SDM\SumberDayaManusiaController@getPegawaiAll');
        Route::get('sdm/get-permohonan-cuti', 'SDM\SumberDayaManusiaController@getPermohonanCuti');
        Route::get('sdm/get-absensi-pegawai', 'SDM\SumberDayaManusiaController@getAbsesnsiPegawai');
        Route::get('sdm/get-pegawai-by-unitkerja', 'SDM\SumberDayaManusiaController@getPegawaiByUnitKerja');
        Route::get('sdm/get-combo-jadwal', 'SDM\SumberDayaManusiaController@getJadwalAbasensiCbo');
        Route::get('sdm/download-sip-str', 'SDM\SumberDayaManusiaController@createZipSipStr');
        Route::get('sdm/get-data-sk', 'SDM\SumberDayaManusiaController@getSuratKeputusan');
        Route::get('sdm/get-list-shift-kerja', 'SDM\SumberDayaManusiaController@getShiftKerja');
        Route::get('sdm/get-list-pegawai', 'SDM\SumberDayaManusiaController@getComboPegawai');
        Route::get('sdm/get-mapping-pegawai', 'SDM\SumberDayaManusiaController@getMappingPegawai');
        Route::get('sdm/get-datapelayanan-pegawai', 'SDM\SumberDayaManusiaController@getDataPelayananPetugas');
        /*END GET*/

        /*POST*/
        Route::post('sdm/update-false-pegawai', 'SDM\SumberDayaManusiaController@deleteDataPegawai');
        Route::post('sdm/update-idfinger-pegawai', 'SDM\SumberDayaManusiaController@updateDataIdFingerPrint');
        Route::post('sdm/delete-data-keluarga-pegawai', 'SDM\SumberDayaManusiaController@deleteDataKeluarga');
        Route::post('sdm/delete-data-sipstr', 'SDM\SumberDayaManusiaController@deleteDataSipStr');
        Route::post('sdm/save-rekam-data-pegawai', 'SDM\SumberDayaManusiaController@saveDataRekamDataPegawai');
        Route::post('sdm/upload-data-sipstr', 'SDM\SumberDayaManusiaController@simpanSipStr');
        Route::post('sdm/update-data-pegawai-form-pensiun', 'SDM\SumberDayaManusiaController@updateDataPegawaiFormPensiun');
        Route::post('sdm/save-data-pensiun-pegawai', 'SDM\SumberDayaManusiaController@SavePensiunPegawai');
        Route::post('sdm/save-jadwal-perbulan-pegawai', 'SDM\SumberDayaManusiaController@saveJadwalBulananPegawai');
        Route::post('sdm/hapus-jadwal-perbulan-pegawai', 'SDM\SumberDayaManusiaController@hapusJadwalBulananPegawai');
        Route::post('sdm/save-data-keluarga-pegawai', 'SDM\SumberDayaManusiaController@saveDataKeluarga');
        Route::post('sdm/save-permohonan-cuti', 'SDM\SumberDayaManusiaController@savePermohonanCuti');
        Route::post('sdm/delete-permohonan-cuti', 'SDM\SumberDayaManusiaController@deletePermohonanCuti');
        Route::post('sdm/verifkasi-cuti', 'SDM\SumberDayaManusiaController@verifCuti');
        Route::post('sdm/unverif-permohonan-cuti', 'SDM\SumberDayaManusiaController@unverifCuti');
        Route::post('sdm/save-permohonan-cuti-bersama', 'SDM\SumberDayaManusiaController@savePermohonanCutiBersama');
        Route::post('sdm/save-absensi-pegawai', 'SDM\SumberDayaManusiaController@saveAbsensi');
        Route::post('sdm/delete-data-sk', 'SDM\SumberDayaManusiaController@deleteSuratKeputusan');
        Route::post('sdm/save-data-sk', 'SDM\SumberDayaManusiaController@saveSuratKeputusan');
        Route::post('sdm/save-shift-kerja', 'SDM\SumberDayaManusiaController@saveShiftKerja');
        Route::post('sdm/save-map-pegawai-to-unit', 'SDM\SumberDayaManusiaController@saveMapPegawaiToUnit');
        Route::post('sdm/delete-map-pegawai-to-unit', 'SDM\SumberDayaManusiaController@DeleteMapPegawaiToUnit');
        Route::post('sdm/save-jadwal-kerja-pegawai', 'SDM\SumberDayaManusiaController@saveJadwalKerjaPegawai');
        /*END POST*/
        // Route::group(['prefix' => 'pelatihan'], function () {
        /*GET*/
        Route::get('sdm/pelatihan/get-combo-pelatihan', 'SDM\PelatihanController@getComboPelatihan');
        Route::get('sdm/pelatihan/get-daftar-monitoring-pengajuan-pelatihan', 'SDM\PelatihanController@GetDaftarMonitoringPengajuanPelatihan');
        Route::get('sdm/pelatihan/get-data-pengajuan-pelatihan', 'SDM\PelatihanController@GetDaftarPengajuanPelatihan');
        Route::get('sdm/pelatihan/get-detail-pengajuan-pelatihan', 'SDM\PelatihanController@getDetailPengajuanPenelitian');
        Route::get('sdm/pelatihan/get-combo-data-pelatihan', 'SDM\PelatihanController@getComboDataPelatihan');
        Route::get('sdm/pelatihan/get-data-peserta-pelatihan', 'SDM\PelatihanController@GetDaftarPesertaPelatihan');
        Route::get('sdm/pelatihan/get-daftar-kehadiran-peserta-pelatihan', 'SDM\PelatihanController@GetDaftarKehadiranPesertaPelatihan');
        Route::get('sdm/pelatihan/get-daftar-rekapitulasi-kehadiran-peserta-pelatihan', 'SDM\PelatihanController@GetDaftarRekapitulasiKehadiranPesertaPelatihan');
        Route::get('sdm/pelatihan/get-detail-kehadiran-peserta-pelatihan', 'SDM\PelatihanController@GetDetailKehadiranPesertaPelatihan');
        Route::get('sdm/pelatihan/get-data-pelaksanaan-pelatihan', 'SDM\PelatihanController@GetDaftarPelaksanaanPelatihan');
        Route::get('sdm/pelatihan/get-data-detail-evaluasi-penyelenggara', 'SDM\PelatihanController@getDetailEvaluasiPenyelenggara');
        Route::get('sdm/pelatihan/get-detail-evaluasi-narasumber-kompetensi', 'SDM\PelatihanController@getDetailEvaluasiNarasumber');
        Route::get('sdm/pelatihan/get-daftar-evaluasi-penyelenggara', 'SDM\PelatihanController@getDaftarEvaluasiPenyelenggara');
        Route::get('sdm/pelatihan/get-daftar-evaluasi-narasumber', 'SDM\PelatihanController@getDaftarEvaluasiNarasumber');
        Route::get('sdm/pelatihan/get-daftar-narasumber', 'SDM\PelatihanController@getDataNarasumber');
        /*END GET*/

        /*POST*/
        Route::post('sdm/pelatihan/verifikasi-pengajuan-pelatihan', 'SDM\PelatihanController@saveVerifikasiPengajuan');
        Route::post('sdm/pelatihan/unverifikasi-pengajuan-pelatihan', 'SDM\PelatihanController@saveUnverifikasiPengajua');
        Route::post('sdm/pelatihan/hapus-pengajuan-pelatihan', 'SDM\PelatihanController@hapusDataPengajuanPelatihan');
        Route::post('sdm/pelatihan/save-pengajuan-pelatihan', 'SDM\PelatihanController@savePengajuanPelatihan');
        Route::post('sdm/pelatihan/save-kehadiran-peserta-pelatihan', 'SDM\PelatihanController@saveKehadiranPesertaPelatihan');
        Route::post('sdm/pelatihan/save-evaluasi-penyelenggara', 'SDM\PelatihanController@saveEvaluasiPenyelenggara');
        Route::post('sdm/pelatihan/save-evaluasi-narasumber', 'SDM\PelatihanController@saveEvaluasiNarasumber');
        Route::post('sdm/pelatihan/delete-data-pelatihan', 'SDM\SumberDayaManusiaController@deleteDataPelatihan');
        Route::post('sdm/pelatihan/hapus-evaluasi-penyelenggara', 'SDM\PelatihanController@hapusDataEvaluasiPenyelenggara');
        Route::post('sdm/pelatihan/hapus-evaluasi-narasumber', 'SDM\PelatihanController@hapusDataEvaluasiNarasumberKompetensi');
        Route::post('sdm/pelatihan/save-data-narasumber', 'SDM\PelatihanController@saveDataNarasumber');
        Route::post('sdm/pelatihan/hapus-data-narasumber', 'SDM\PelatihanController@hapusNarasumber');
        Route::get('sdm/pendidikan/get-mapkategoripendidikan-to-programpendidikan', 'SDM\PelatihanController@getMapKatPendidikanTOprogram');
        /*END POST*/
        // });
        // Route::group(['prefix' => 'pendidikan'], function () {
        Route::get('sdm/pendidikan/get-daftar-diklat-kategory', 'SDM\SumberDayaManusiaController@getDaftarDiklatKategory');
        Route::get('sdm/pendidikan/get-daftar-diklat-jurusan', 'SDM\SumberDayaManusiaController@getDaftarDiklatJurusan');
        Route::get('sdm/pendidikan/get-combo-map-kategori', 'SDM\SumberDayaManusiaController@getDataComboMapKategoriPendidikanToProgramPendidikan');
        Route::get('sdm/pendidikan/get-dcbo-pesertadidik', 'SDM\SumberDayaManusiaController@getDataComboPesertaDidik');
        Route::get('sdm/pendidikan/get-daftar-pesertadidik', 'SDM\SumberDayaManusiaController@getDaftarPesertaDidik');
        Route::get('sdm/pendidikan/get-cbo-tenagapengajar', 'SDM\SumberDayaManusiaController@getDataComboTenagaPengajar');
        Route::get('sdm/pendidikan/get-daftar-tenagapengajar', 'SDM\SumberDayaManusiaController@getDaftarTenagaPengajar');
        Route::get('sdm/pendidikan/get-detail-tenagapengajar', 'SDM\SumberDayaManusiaController@getDetailTenagaPengajar');

        Route::post('sdm/pendidikan/save-tenagapengajar', 'SDM\SumberDayaManusiaController@saveTenagaPengajar');
        Route::post('sdm/pendidikan/hapus-tenagapengajar', 'SDM\SumberDayaManusiaController@hapusTenagaPengajar');
        Route::post('sdm/pendidikan/save-daftar-diklat-jurusan', 'SDM\SumberDayaManusiaController@saveDiklatJurusan');
        Route::post('sdm/pendidikan/save-daftar-diklat-kategory', 'SDM\SumberDayaManusiaController@saveDiklatKategory');
        Route::post('sdm/pendidikan/save-pesertadidik', 'SDM\SumberDayaManusiaController@savePesertaDidik');
        Route::post('sdm/pendidikan/hapus-peserta-didik', 'SDM\SumberDayaManusiaController@hapusPesertaDidik');

        // });
        // Route::group(['prefix' => 'penelitian'], function (){
        Route::get('sdm/penelitian/get-data-combo-penelitian', 'SDM\PenelitianController@getDataComboPenelitian');
        Route::post('sdm/penelitian/save-kegiatan-penelitian-eksternal', 'SDM\PenelitianController@saveKegiatanPenelitianExternal');
        Route::get('sdm/penelitian/get-daftar-penelitian-eksternal', 'SDM\PenelitianController@getDaftarPenelitianKegiatanEksternal');
        Route::post('sdm/penelitian/batal-kegiatan-penelitian-eksternal', 'SDM\PenelitianController@saveBatalPenelitianEksternal');
        Route::get('sdm/penelitian/get-detail-penelitian-eksternal', 'SDM\PenelitianController@getDetailPenelitianKegiatanEksternal');
        Route::post('sdm/penelitian/save-kegiatan-penelitian-pegawai', 'SDM\PenelitianController@saveKegiatanPenelitianPegawai');
        Route::post('sdm/penelitian/batal-kegiatan-penelitian-pegawai', 'SDM\PenelitianController@saveBatalPenelitianPegawai');
        Route::get('sdm/penelitian/get-data-pegawai', 'SDM\PenelitianController@getDaftarPegawai');
        Route::get('sdm/penelitian/get-daftar-penelitian-pegawai', 'SDM\PenelitianController@getDaftarPenelitianKegiatanPegawai');
        Route::get('sdm/penelitian/get-detail-penelitian-pegawai', 'SDM\PenelitianController@getDetailPenelitianKegiatanPegawai');
        // });
        // });
        // Route::group(['prefix' => 'rensar'], function () {
        Route::get('rensar/get-indikator-rensar', 'EIS\EISController@getIndikatorRensar');
        // });
        // Route::group(['prefix' => 'sterilisasi'], function () {
        Route::get('sterilisasi/get-data-combo-steril', 'Sterilisasi\SterilisasiController@getComboSteril');
        Route::get('sterilisasi/get-data-stok-steril', 'Sterilisasi\SterilisasiController@getDataStokInsSteril');
        Route::get('sterilisasi/get-data-combo-terima-steril', 'Sterilisasi\SterilisasiController@getComboTerimaBarang');
        Route::get('sterilisasi/get-daftar-terima-steril', 'Sterilisasi\SterilisasiController@getDaftarDistribusiBarangSteril');
        Route::post('sterilisasi/save-update-status-sterilisasi', 'Sterilisasi\SterilisasiController@UpdateStatusSterilisasi');
        Route::post('sterilisasi/save-registrasi-barang-sterilisasi', 'Sterilisasi\SterilisasiController@saveRegistrasiBarangSteril');
        Route::get('sterilisasi/get-daftar-registrasi-steril', 'Sterilisasi\SterilisasiController@getDataRegistrasiBarangSteril');
        Route::post('sterilisasi/delete-registrasi-barang-sterilisasi', 'Sterilisasi\SterilisasiController@DeleteRegistrasiAlatCssd');
        Route::post('sterilisasi/save-Kelompok-alat-sterilisasi', 'Sterilisasi\SterilisasiController@saveKelompokAlat');
        Route::get('sterilisasi/get-data-kelompokalat', 'Sterilisasi\SterilisasiController@getDataKelompokAlat');
        Route::post('sterilisasi/delete-Kelompok-alat-sterilisasi', 'Sterilisasi\SterilisasiController@deleteKelompokAlat');
        Route::post('sterilisasi/save-update-status-pemakaianalat', 'Sterilisasi\SterilisasiController@UpdateStatusPemakaianAlat');
        Route::post('sterilisasi/save-update-status-bersihkan', 'Sterilisasi\SterilisasiController@UpdateStatusBersih');
        Route::get('sterilisasi/get-data-orderalatsteril', 'Sterilisasi\SterilisasiController@getDaftarOrderAlatSteril');
        Route::post('sterilisasi/save-update-status-alatkotor', 'Sterilisasi\SterilisasiController@UpdateStatusAlatKotor');
        // });

        // Route::group(['prefix' => 'sysadmin'], function () {
        Route::get('sysadmin/get-list-combo', 'SysAdmin\SysAdminController@getlistCombo');
        Route::get('sysadmin/get-map-ruangantojenis', 'SysAdmin\SysAdminController@getDataMapping');
        Route::post('sysadmin/save-map-ruangantojenis', 'SysAdmin\SysAdminController@saveMapRuanganToJenis');
        Route::get('sysadmin/get-combo-paket', 'SysAdmin\SysAdminController@getComboPaket');
        Route::get('sysadmin/get-mapping-paket', 'SysAdmin\SysAdminController@getMappingPaket');
        Route::post('sysadmin/save-map-paket-to-produk', 'SysAdmin\SysAdminController@saveMapPaketToProduk');
        Route::post('sysadmin/delete-map-paket-to-produk', 'SysAdmin\SysAdminController@deleteMapPaketToProduk');
        Route::get('sysadmin/get-combo-mkttbku', 'SysAdmin\SysAdminController@getComboMKTTBKU');
        Route::get('sysadmin/get-mapping-mkttbku', 'SysAdmin\SysAdminController@getMappingMKTTBKU');
        Route::post('sysadmin/save-data-mkttbku', 'SysAdmin\SysAdminController@saveMapMKTTBKU');
        Route::post('sysadmin/delete-data-mkttbku', 'SysAdmin\SysAdminController@DeleteMapMKTTBKU');
        Route::get('sysadmin/get-data-profile', 'SysAdmin\SysAdminController@getDataProfile');
        Route::post('sysadmin/save-statusenabled-profile', 'SysAdmin\SysAdminController@UpdateStatusEnabledProfile');
        Route::post('sysadmin/save-data-profile', 'SysAdmin\SysAdminController@SaveDataProfile');
        Route::post('sysadmin/save-data-paket-obat', 'SysAdmin\SysAdminController@savePaketObat');
        Route::get('sysadmin/get-paket-obat', 'SysAdmin\SysAdminController@getDataPaketObat');
        Route::post('sysadmin/delete-data-paket-obat', 'SysAdmin\SysAdminController@DeletePaketObat');
        Route::get('sysadmin/get-combo-pelayananmutu', 'SysAdmin\SysAdminController@getComboPelayananMutu');
        Route::get('sysadmin/get-mapping-ruanganpelayananmutu', 'SysAdmin\SysAdminController@getMappingRuanganToPelayananMutu');
        Route::post('sysadmin/save-map-ruangan-to-pelayananmutu', 'SysAdmin\SysAdminController@saveMapRuanganToPelayananMutu');
        Route::post('sysadmin/delete-map-ruangan-to-pelayananmutu', 'SysAdmin\SysAdminController@DeleteMapRuanganToPelayananMutu');

        // Route::group(['prefix' => 'menu'], function () {
        Route::get('sysadmin/menu/get-menu-dinamis', 'SysAdmin\ModulAplikasiController@getMenuDinamis');
        Route::get('sysadmin/menu/get-all-default-objek-modul', 'SysAdmin\ModulAplikasiController@getAllDefaultObjectModul');
//                Route::get('sysadmin/menu/get-menu-dinamis', 'SysAdmin\ModulAplikasiController@getMenuDinamis');
        Route::get('sysadmin/menu/get-objek-modul-aplikasi', 'SysAdmin\ModulAplikasiController@getObjekModulAplikasiStandar');
        Route::get('sysadmin/menu/get-map-modul-to-objek-modul-aplikasi', 'SysAdmin\ModulAplikasiController@getMapModulToObjekModul');
        Route::post('sysadmin/menu/save-modul-aplikasi', 'SysAdmin\ModulAplikasiController@saveModulAplikasi');
        Route::get('sysadmin/menu/get-daftar-objek-modul-aplikasi', 'SysAdmin\ModulAplikasiController@getDaftarObjekModulAplikasi');
        Route::post('sysadmin/menu/save-objek-modul-aplikasi', 'SysAdmin\ModulAplikasiController@saveObjekModulAplikasi');
        Route::get('sysadmin/menu/get-map-login-to-modul-aplikasi', 'SysAdmin\ModulAplikasiController@getMapLoginToModulApp');
        Route::get('sysadmin/menu/save-map-login-to-modul-aplikasi', 'SysAdmin\ModulAplikasiController@saveMapLoginToModulApp');
        Route::get('sysadmin/menu/delete-map-login-to-modul-aplikasi', 'SysAdmin\ModulAplikasiController@deleteMapLoginToModulApp');
        Route::post('sysadmin/menu/save-map-modul-to-objek-modul-aplikasi', 'SysAdmin\ModulAplikasiController@saveMapModultoObjekModulApp');
        Route::post('sysadmin/menu/save-objek-modul-from-json', 'SysAdmin\ModulAplikasiController@saveObjekModulAplikasiFromJson');
        Route::post('sysadmin/menu/save-waktu-login', 'SysAdmin\ModulAplikasiController@saveEndWaktuLogin');
        Route::get('sysadmin/menu/get-waktu-login', 'SysAdmin\ModulAplikasiController@getEndWaktuLogin');
        Route::post('sysadmin/menu/delete-end-waktu-login', 'SysAdmin\ModulAplikasiController@deleteEndWaktuLogin');
        Route::post('sysadmin/menu/save-new-user', 'SysAdmin\ModulAplikasiController@saveNewUser');
        Route::get('sysadmin/menu/get-pegawai-part', 'SysAdmin\ModulAplikasiController@getPegawaiPart');
        Route::get('sysadmin/menu/get-daftar-user', 'SysAdmin\ModulAplikasiController@getDaftarUser');
        Route::post('sysadmin/menu/delete-new-user', 'SysAdmin\ModulAplikasiController@deleteNewUser');
        Route::get('sysadmin/menu/get-master-modul-aplikasi', 'SysAdmin\ModulAplikasiController@getMasterModulAplikasi');

        Route::get('sysadmin/menu/data', 'SysAdmin\HakAksesPegawaiController@getData');
        Route::post('sysadmin/menu/simpan-modul-aplikasi', 'SysAdmin\HakAksesPegawaiController@modulAplikasi');
        Route::post('sysadmin/menu/hapus-modul-aplikasi', 'SysAdmin\HakAksesPegawaiController@HapusModulAplikasi');
        Route::post('sysadmin/menu/simpan-objek-modul-aplikasi', 'SysAdmin\HakAksesPegawaiController@objekModulAplikasi');
        Route::post('sysadmin/menu/hapus-objek-modul-aplikasi', 'SysAdmin\HakAksesPegawaiController@HapusObjekModulAplikasi');
        Route::post('sysadmin/menu/hapus-map-objek-modul-aplikasi', 'SysAdmin\HakAksesPegawaiController@HapusMAPObjekModulAplikasi');
        Route::post('sysadmin/menu/map-objek-modultokelompok-user', 'SysAdmin\HakAksesPegawaiController@mapObjekModulToKelompokUser');
        Route::post('sysadmin/menu/hapus-objek-modultokelompok-user', 'SysAdmin\HakAksesPegawaiController@HapusmapObjekModulToKelompokUser');
        Route::get('sysadmin/menu/data-map-login-usertoruangan', 'SysAdmin\HakAksesPegawaiController@getMapLoginUsertoRuangan');
        Route::post('sysadmin/menu/map-login-usertoruangan', 'SysAdmin\HakAksesPegawaiController@MapLoginUsertoRuangan');
        Route::delete('map-login-usertoruangan', 'SysAdmin\HakAksesPegawaiController@HapusMapLoginUsertoRuangan');
        Route::get('sysadmin/menu/data-subsitem-modul-menu', 'SysAdmin\HakAksesPegawaiController@getDataSubsitemModulMenu');
        Route::get('sysadmin/menu/data-ruangan/{id}', 'SysAdmin\HakAksesPegawaiController@getRuangan');
        Route::get('sysadmin/menu/svc-modul', 'SysAdmin\HakAksesPegawaiController@getalldata');
        Route::post('sysadmin/menu/svc-modul/add', 'SysAdmin\HakAksesPegawaiController@addAlldata');
        Route::get('sysadmin/menu/svc-ruang', 'SysAdmin\HakAksesPegawaiController@getdataRuangAll');
        Route::post('sysadmin/menu/svc-ruang/add', 'SysAdmin\HakAksesPegawaiController@addMapUserRuang');
        Route::get('sysadmin/menu/data-pegawai', 'SysAdmin\HakAksesPegawaiController@getallPegawai');
        Route::get('sysadmin/menu/get-recursive-ruangan', 'SysAdmin\HakAksesPegawaiController@getRecursiveRuangan');
        Route::get('sysadmin/menu/get-recursive-modul', 'SysAdmin\HakAksesPegawaiController@getRecursiveModul');

        Route::get('sysadmin/menu/save-map-luRuangan', 'SysAdmin\HakAksesPegawaiController@saveMapLoginUser');
        Route::get('sysadmin/menu/save-hapus-map-luRuangan', 'SysAdmin\HakAksesPegawaiController@saveHapusMapLoginUser');
        Route::get('sysadmin/menu/hash-password', 'SysAdmin\HakAksesPegawaiController@hasPassword');
        Route::get('sysadmin/menu/get-child-idhead', 'SysAdmin\HakAksesPegawaiController@getChildIdHead');
        Route::get('sysadmin/menu/get-kelompok-user', 'SysAdmin\HakAksesPegawaiController@getKelompokUser');
        // });
        // Route::group(['prefix' => 'logging'], function () {
        Route::get('sysadmin/logging/save-log-verifikasi-tarek', 'SysAdmin\LoggingController@saveLoggingVerifTarek');
        Route::get('sysadmin/logging/save-log-unverifikasi-tarek', 'SysAdmin\LoggingController@saveLoggingUnverifTarek');
        Route::post('sysadmin/logging/save-log-input-tindakan', 'SysAdmin\LoggingController@saveLoggingInputTindakan');
        Route::post('sysadmin/logging/save-log-hapus-tindakan', 'SysAdmin\LoggingController@saveLogHapusTindakan');
        Route::get('sysadmin/logging/save-log-konsul', 'SysAdmin\LoggingController@saveLoggingKonsulRuangan');
        Route::get('sysadmin/logging/save-log-input-resep', 'SysAdmin\LoggingController@saveLogInputResep');
        Route::get('sysadmin/logging/save-log-hapus-resep', 'SysAdmin\LoggingController@saveLogHapusResep');
        Route::get('sysadmin/logging/save-log-ubah-rekanan', 'SysAdmin\LoggingController@saveLogUbahRekanan');
        Route::get('sysadmin/logging/save-log-pendaftaran-pasien', 'SysAdmin\LoggingController@saveLogPasienDaftar');
        Route::get('sysadmin/logging/save-log-pindah-ruangan', 'SysAdmin\LoggingController@saveLogPindahKamar');
        Route::get('sysadmin/logging/save-log-pulang-pasien', 'SysAdmin\LoggingController@saveLogPulanginPasien');
        Route::get('sysadmin/logging/save-log-batal-bayar', 'SysAdmin\LoggingController@saveLogBatalBayar');
        Route::get('sysadmin/logging/save-log-retur-resep', 'SysAdmin\LoggingController@saveLogReturResep');
        Route::get('sysadmin/logging/Daftar-log-user', 'SysAdmin\LoggingController@getDaftarLog');
        Route::get('sysadmin/logging/save-log-all', 'SysAdmin\LoggingController@saveLoggingAll');
        Route::get('sysadmin/logging/get-data-combo', 'SysAdmin\LoggingController@getCombo');
        Route::get('sysadmin/logging/get-aktivitas-user', 'SysAdmin\LoggingController@getAktivitasUser');
        Route::get('sysadmin/logging/save-log-bayar', 'SysAdmin\LoggingController@saveLogBayartTagihanPasien');
        // });
        // Route::group(['prefix' => 'settingdatafixed'], function () {
        Route::get('sysadmin/settingdatafixed/get-settingdatafixed', 'SysAdmin\Master\SettingDataFixedController@getDataFixed');
        Route::get('sysadmin/settingdatafixed/get-settingdatafixedbyid/{id}', 'SysAdmin\Master\SettingDataFixedController@getSettingById');
        Route::post('sysadmin/settingdatafixed/post-settingdatafixe', 'SysAdmin\Master\SettingDataFixedController@SaveSettingDataFixed');
        Route::post('sysadmin/settingdatafixed/hapus-settingdatafixe', 'SysAdmin\Master\SettingDataFixedController@HapusSettingDataFixed');
        Route::post('sysadmin/settingdatafixed/tambah-settingdatafixe', 'SysAdmin\Master\SettingDataFixedController@TambahSettingDataFixed');
        Route::post('sysadmin/settingdatafixed/delete', 'SysAdmin\Master\SettingDataFixedController@deleteSetting');
        Route::get('sysadmin/settingdatafixed/update-status-enabled', 'SysAdmin\Master\SettingDataFixedController@updateStatuEnabled');
        Route::get('sysadmin/settingdatafixed/get-kelompok-setting', 'SysAdmin\Master\SettingDataFixedController@getKelompokSettingDataFix');
        Route::get('sysadmin/settingdatafixed/get-setting-detail', 'SysAdmin\Master\SettingDataFixedController@getSettingDetail');
        Route::get('sysadmin/settingdatafixed/get-setting-combo', 'SysAdmin\Master\SettingDataFixedController@getComboPart');
        Route::post('sysadmin/settingdatafixed/update-setting', 'SysAdmin\Master\SettingDataFixedController@updateSettingDataFix');
        Route::get('sysadmin/settingdatafixed/get-table', 'SysAdmin\Master\SettingDataFixedController@getTable');
        Route::get('sysadmin/settingdatafixed/get-field-table', 'SysAdmin\Master\SettingDataFixedController@getFieldTable');
        Route::get('sysadmin/settingdatafixed/get-data-from-table', 'SysAdmin\Master\SettingDataFixedController@getDataFromTable');
        Route::get('sysadmin/settingdatafixed/get-report-display', 'SysAdmin\Master\SettingDataFixedController@getReportDisplayTable');
        Route::get('sysadmin/settingdatafixed/get/{namaField}', 'SysAdmin\Master\SettingDataFixedController@getSettingDataFixedGeneric');
        // });
        // Route::group(['prefix' => 'general'], function () {
        // GET //
        Route::get('sysadmin/general/identifikasi-sep', 'SysAdmin\GeneralController@IdentifikasiSEP');
        Route::get('sysadmin/general/get-sudah-posting', 'SysAdmin\GeneralController@getStatusPostingTgl');
        Route::get('sysadmin/general/get-status-close/{noregistrasi}', 'SysAdmin\GeneralController@getStatusClosePeriksa');
        Route::get('sysadmin/general/get-tgl-posting', 'SysAdmin\GeneralController@getPostingTgl');
        Route::get('sysadmin/general/get-combo-pegawai', 'SysAdmin\GeneralController@getDataPegawaiGeneral');
        Route::get('sysadmin/general/get-combo-address', 'SysAdmin\GeneralController@getComboAddressGeneral');
        Route::get('sysadmin/general/get-desa-kelurahan', 'SysAdmin\GeneralController@getDesaKelurahanGeneral');
        Route::get('sysadmin/general/get-alamat-bykodepos', 'SysAdmin\GeneralController@getAlamatByKodePosGeneral');
        Route::get('sysadmin/general/get-datacombo-ruangan', 'SysAdmin\GeneralController@getDataComboRuanganGeneral');
        Route::get('sysadmin/general/get-datacombo-rekanan', 'SysAdmin\GeneralController@getDataComboRekananGeneral');
        Route::get('sysadmin/general/get-sudah-verif', 'SysAdmin\GeneralController@getVerifikasiNoregistrasiGeneral');
        Route::get('sysadmin/general/view-bed', 'SysAdmin\GeneralController@viewBed');
        Route::get('sysadmin/general/get-datacombo-produk', 'SysAdmin\GeneralController@getProdukPart');
        Route::get('sysadmin/general/get-datacombo-icd10', 'SysAdmin\GeneralController@getIcd10');
        Route::get('sysadmin/general/get-jenis-pelayanan/{norec}', 'SysAdmin\GeneralController@getJenisPelayananByNorecPd');
        Route::get('sysadmin/general/get-tindakan', 'SysAdmin\GeneralController@getTindakanPart');
        Route::get('sysadmin/general/get-komponenharga', 'SysAdmin\GeneralController@getKomponenHarga');
        Route::get('sysadmin/general/get-komponenharga-paket', 'SysAdmin\GeneralController@getKomponenHargaPaket');

        Route::get('sysadmin/general/get-acc-number-radiologi', 'SysAdmin\GeneralController@getAccNumberRadiologi');
        Route::get('sysadmin/general/get-produk', 'SysAdmin\GeneralController@getDataProduk');
        Route::get('sysadmin/general/get-master-diagnosa-kep', 'SysAdmin\GeneralController@getDiagnosaKeperawatan');
        Route::get('sysadmin/general/get-ruangan-part', 'SysAdmin\GeneralController@getRuanganPart');
        Route::get('sysadmin/general/get-combo-pegawai', 'SysAdmin\GeneralController@getComboPegawai');
        Route::get('sysadmin/general/get-icd9-part', 'SysAdmin\GeneralController@getPartIcd9');
        Route::get('sysadmin/general/get-icd10-part', 'SysAdmin\GeneralController@getPartIcd10');
        Route::get('sysadmin/general/get-terbilang/{number}', 'SysAdmin\GeneralController@getTerbilangGeneral');                // GET //
        Route::get('sysadmin/general/get-icd9-part', 'SysAdmin\GeneralController@getPartIcd9');
        Route::get('sysadmin/general/get-icd10-part', 'SysAdmin\GeneralController@getPartIcd10');
        Route::get('sysadmin/general/settingdatafixed/get/{namaField}', 'SysAdmin\Master\SettingDataFixedController@getSettingDataFixedGeneric');
        Route::get('sysadmin/general/get-paket-tindakan', 'SysAdmin\GeneralController@getPaketTindakan');
        Route::get('sysadmin/general/get-combo-registrasi-general', 'SysAdmin\GeneralController@getComboRegGeneral');
        Route::get('sysadmin/general/get-data-bynocm', 'SysAdmin\GeneralController@getPsnByNoCmGeneral');
        Route::get('sysadmin/general/get-combo-ruangan', 'SysAdmin\GeneralController@getRuangan');
        Route::get('sysadmin/general/get-combo-akomdasi', 'SysAdmin\GeneralController@getComboAkomodasi');
        Route::get('sysadmin/general/get-combo-ruangan-general', 'SysAdmin\GeneralController@getComboRuanganGeneral');
        Route::get('sysadmin/general/get-dokter-general', 'SysAdmin\GeneralController@getcomboDokterPart');
        Route::get('sysadmin/general/get-diagnosa-pasien', 'SysAdmin\GeneralController@getDiagnosaPasien');
        Route::get('sysadmin/general/get-data-produk-detail', 'SysAdmin\GeneralController@getDataProdukDetail');
        Route::get('sysadmin/general/get-datacombo-departemen', 'SysAdmin\GeneralController@getDataComboDepartemenGeneral');
        Route::get('sysadmin/general/get-data-detail-pasien-general', 'SysAdmin\GeneralController@getDetailPasienGeneral');
        Route::get('sysadmin/general/get-datacombo-handhygiene-general', 'SysAdmin\GeneralController@getDataComboHandHygieneGeneral');
        Route::get('sysadmin/general/get-datacombo-indikasi-general', 'SysAdmin\GeneralController@getDataComboIndikasiGeneral');
        Route::get('sysadmin/general/get-datacombo-jenispegawai-general', 'SysAdmin\GeneralController@getDataComboJenisPegawaiGeneral');
        Route::get('sysadmin/general/get-datacombo-jenispegawai-cppt', 'SysAdmin\GeneralController@getDataComboJenisPegawaiCPPT');
        Route::get('sysadmin/general/get-apd-general', 'SysAdmin\GeneralController@getAPD');
        Route::get('sysadmin/general/get-diagnosapasienbynoreg', 'SysAdmin\GeneralController@getDiagnosaPasien');
        Route::get('sysadmin/general/get-pasien-bynorec-general', 'SysAdmin\GeneralController@getPasienByNoreg');
        Route::get('sysadmin/general/get-daftar-permohonan-simrs', 'SysAdmin\GeneralController@getDaftarPermohonanSIMRS');
        Route::get('sysadmin/general/get-jenis-pekerjaan-simrs', 'SysAdmin\GeneralController@getJenisPekerjaanSIMRS');
        Route::get('sysadmin/general/get-status-pekerjaan-simrs', 'SysAdmin\GeneralController@getStatusPekerjaanSIMRS');
        Route::get('sysadmin/general/get-combo-pegawai-simrs', 'SysAdmin\GeneralController@getDataPegawaiGeneralSIMRS');
        Route::get('sysadmin/general/get-combo-administrasi', 'SysAdmin\GeneralController@getComboAdministrasi');
        Route::get('sysadmin/general/get-jenispel', 'SysAdmin\GeneralController@getJenisPelayanan');
        Route::get('sysadmin/general/get-lingkuppelayanan', 'SysAdmin\GeneralController@getLingkupPelayanan');
        Route::get('sysadmin/general/get-combo-administrasi', 'SysAdmin\GeneralController@getComboAdministrasi');
        Route::get('sysadmin/general/get-data-maplingkuppelayanan', 'SysAdmin\GeneralController@getDataMapLapKeuanganToLingkupPelayanan');
        Route::get('sysadmin/general/get-data-depart', 'SysAdmin\GeneralController@getDepartemen');
        // GET //

        // POST //
        Route::post('sysadmin/general/hapus-jurnal-penerimaan-barang', 'SysAdmin\GeneralController@PostingHapusJurnal_Penerimaan');
        Route::post('sysadmin/general/save-jurnal-penerimaan-barang', 'SysAdmin\GeneralController@PostingJurnal_terimabarang');
        Route::post('sysadmin/general/save-jurnal-amprahan-barang-all', 'SysAdmin\GeneralController@PostingJurnal_amprahanForDaftar');
        Route::post('sysadmin/general/hapus-jurnal-amprahan-barang', 'SysAdmin\GeneralController@PostingHapusJurnal_BatalKirim');
        Route::post('sysadmin/general/update-jurnal-batalkirim-peritem', 'SysAdmin\GeneralController@UpdatePostingJurnal_BatalKirimPerItem');
        Route::post('sysadmin/general/hapus-jurnal-pembayarantagihan', 'SysAdmin\GeneralController@hapusJurnalpembayaranTagihanNoBatch');
        Route::post('sysadmin/general/save-jurnal-verifikasi_tarek', 'SysAdmin\GeneralController@PostingJurnal_strukpelayanan_t_verifikasi_tarek');
        Route::post('sysadmin/general/save-jurnal-pelayananpasien_t', 'SysAdmin\GeneralController@PostingJurnal_pelayananpasien_t');
        Route::post('sysadmin/general/save-jurnal-pembayarantagihan', 'SysAdmin\GeneralController@PostingJurnal_pembayaranTagihanNoBatch');
        Route::post('sysadmin/general/save-jurnal-pembayaran_tagihan', 'SysAdmin\GeneralController@PostingJurnal_pembayaran_tagihan');
        Route::post('sysadmin/general/save-jurnal-setorankasir', 'SysAdmin\GeneralController@PostingJurnal_setoranKasir');
        Route::post('sysadmin/general/hapus-jurnal-setorankasir', 'SysAdmin\GeneralController@hapusJurnalSetoranKasir');
        Route::post('sysadmin/general/post-diagnosa-kep/{method}', 'SysAdmin\GeneralController@postMasterDiagnosaKeperawatan');
        Route::post('sysadmin/general/post-{table}-diagnosakeperawatan/{method}', 'SysAdmin\GeneralController@postDetailDiagnoaKep');
        Route::post('sysadmin/general/save-map-akomodasi', 'SysAdmin\GeneralController@saveMappingAkomodasiCuy');
        Route::post('sysadmin/general/save-permohonan-simrs', 'SysAdmin\GeneralController@SavePermohonanSIMRS');
        Route::post('sysadmin/general/save-jenis-kerusakan-simrs', 'SysAdmin\GeneralController@SaveDataJenisKerusakanSIMRS');
        Route::post('sysadmin/general/hapus-permohonan-simrs', 'SysAdmin\GeneralController@HapusPermohonanSIMRS');
        Route::post('sysadmin/general/save-map-administrasi', 'SysAdmin\GeneralController@saveMappingAdminstrasiCuy');
        Route::post('sysadmin/general/save-map-laporankeuanganlingkuppelayanan', 'SysAdmin\GeneralController@saveMappingLaporanKeuanganToLingkupPelayanan');
        Route::get('sysadmin/logging/save-log-meninggal-pasien-rj', 'SysAdmin\LoggingController@saveLogMeninggalPasienRJ');
        // POST //

        // Route::group(['prefix' => 'rensar'], function () {
        /*GET*/
        Route::get('sysadmin/general/rensar/get-data-combo', 'EIS\EISController@getCombo');
        Route::get('sysadmin/general/rensar/get-data-indikator', 'EIS\EISController@getDaftarIndikatorRensar');
        Route::get('sysadmin/general/rensar/get-jenis-indikator', 'EIS\EISController@getJenisIndikator');
        Route::get('sysadmin/general/rensar/get-indikator-rensar-m', 'EIS\EISController@getIndikatorRensar_M');
        Route::get('sysadmin/general/rensar/get-indikator-rensar', 'EIS\EISController@getIndikatorRensar');
        Route::get('sysadmin/general/rensar/get-kelompok-transaksi', 'EIS\EISController@getKelompokTransaksi');

        Route::get('sysadmin/general/rensar/get-jenis-indikator', 'EIS\EISController@getJenisIndikator');
        Route::get('sysadmin/general/rensar/get-target-indikator', 'EIS\EISController@getTargetIndikator');
        Route::get('sysadmin/general/rensar/get-indikator-ikt', 'EIS\EISController@getIndikatorIKT');
        /*END GET*/

        /*POST*/
        Route::post('sysadmin/general/rensar/save-indikator-rensar-m', 'EIS\EISController@saveIndikatorRensar_M');
        Route::post('sysadmin/general/rensar/delete-master-indikator', 'EIS\EISController@deleteIndikatorRensar');
        Route::post('sysadmin/general/rensar/save-master-indikator', 'EIS\EISController@saveIndikatorRensar_M');
        Route::post('sysadmin/general/rensar/save-jenis-indikator', 'EIS\EISController@saveJenisInidkator');
        Route::post('sysadmin/general/rensar/save-target-indikator', 'EIS\EISController@saveTargetIndikator');
        Route::post('sysadmin/general/rensar/delete-target-indikator', 'EIS\EISController@deleteTargetIndikator');
        Route::post('sysadmin/general/rensar/post-waktu-tunggu-pelayanan-lab', 'EIS\EISController@postWaktuTungguPelayananLab');
        Route::post('sysadmin/general/rensar/post-waktu-tunggu-pelayanan-rad', 'EIS\EISController@postWaktuTungguPelayananRad');
        Route::post('sysadmin/general/rensar/save-indikator', 'EIS\EISController@saveIndikatorRensar');
        Route::post('sysadmin/general/rensar/delete-indikator', 'EIS\EISController@deleteIndikatorRensar');
        Route::post('sysadmin/general/rensar/post-ketepatan-jam-visite', 'EIS\EISController@postKetepatanJamVisite');
        Route::post('sysadmin/general/rensar/post-kejadian-pasien-jatuh', 'EIS\EISController@postPasienJatuh');
        Route::post('sysadmin/general/rensar/post-tindakan-nicu', 'EIS\EISController@postTindakanOperasiNICU');
        Route::post('sysadmin/general/rensar/post-keluhan-pelanggan', 'EIS\EISController@postKeluhanPelanggan');
        Route::post('sysadmin/general/rensar/post-pengembalian-dok-rm', 'EIS\EISController@postPengembalianRekamMedik');
        Route::post('sysadmin/general/rensar/post-waktu-tunggu-rj', 'EIS\EISController@postWaktuTungguRawatJalan');
        Route::post('sysadmin/general/rensar/save-kelompok-transaksi', 'EIS\EISController@saveKelompokTransaksi');
        /*POST*/
        // });
        // });
        // Route::group(['prefix' => 'master'], function () {
        // GET //
        Route::get('sysadmin/master/replace-isi-table', 'SysAdmin\Master\MasterController@replaceTableField');
        Route::get('sysadmin/master/get-data-produk', 'SysAdmin\Master\MasterController@getListProduk');
        Route::get('sysadmin/master/get-produkbyid', 'SysAdmin\Master\MasterController@getProdukbyId');
        Route::get('sysadmin/master/get-jenis-produk', 'SysAdmin\Master\MasterController@getjenisproduk');
        Route::get('sysadmin/master/get-detail-jenis-produk', 'SysAdmin\Master\MasterController@getDetailjenisprodukbyIdjenisproduk');
        Route::get('sysadmin/master/get-data-combo-master', 'SysAdmin\Master\MasterController@getDataComboMaster');
        Route::get('sysadmin/master/get-data-combo-rekanan', 'SysAdmin\Master\MasterController@getDataRekananMaster');
        Route::get('sysadmin/master/get-detail-produk-kelompok', 'SysAdmin\Master\MasterController@getDataProdukbyDetailProduk');
        Route::get('sysadmin/master/get-data-produk-kelompok', 'SysAdmin\Master\MasterController@getDataKelompok');
        Route::get('sysadmin/master/get-data-produk-perkode', 'SysAdmin\Master\MasterController@getDataProdukPerKode');
        Route::get('sysadmin/master/get-konversi-satuan', 'SysAdmin\Master\MasterController@getKonversiSatuan');
        Route::get('sysadmin/master/get-data-barang-konversi', 'SysAdmin\Master\MasterController@getBarangKonversi');
        Route::get('sysadmin/master/get-data-rekanan', 'SysAdmin\Master\MasterController@getDataRekanan');
        Route::get('sysadmin/master/get-rekanan-perkode', 'SysAdmin\Master\MasterController@getRekananById');

        Route::get('sysadmin/master/get-list_table', 'SysAdmin\Master\MasterController@getKelompokTableMaster');
        Route::get('sysadmin/master/get-table-detail', 'SysAdmin\Master\MasterController@getTableDetail');
        Route::get('sysadmin/master/get-table-row-detail', 'SysAdmin\Master\MasterController@getTableRowDetail');
        Route::get('sysadmin/master/get-setting-combo', 'SysAdmin\Master\MasterController@getComboPartTable');

        Route::get('sysadmin/master/get-kelompok-user', 'SysAdmin\Master\MasterController@getKelompokUser');
        Route::get('sysadmin/master/get-tarif-harganettoprodukbykelas', 'SysAdmin\Master\MasterController@view_harganettoprodukbykelas');
        Route::get('sysadmin/master/get-tarif-harganettoprodukbykelas_d', 'SysAdmin\Master\MasterController@view_harganettoprodukbykelasD');
        Route::get('sysadmin/master/get-tarif-produk', 'SysAdmin\Master\MasterController@GetTarifProduk');
        Route::get('sysadmin/master/get-combo-tarif', 'SysAdmin\Master\MasterController@getComboHargaNetto');
        Route::get('sysadmin/master/get-list-komponen', 'SysAdmin\Master\MasterController@ListMaster');

        Route::get('sysadmin/master/get-data-mapping-ruangan-to-produk', 'SysAdmin\Master\MasterController@getDataMapRuanganToProduk');
        Route::get('sysadmin/master/get-produkbyIdformap', 'SysAdmin\Master\MasterController@getProdukbyIdformap');

        Route::get('sysadmin/master/get-ruanganbyidDepart/{id}', 'SysAdmin\Master\MasterController@getRuanganbyIddepartemen');
        Route::get('sysadmin/master/list-produk/{limit?}', 'SysAdmin\Master\MasterController@getListProdukMap');
        Route::get('sysadmin/master/get-kelompok-produk', 'SysAdmin\Master\MasterController@getkelompokproduk');
        Route::get('sysadmin/master/get-daftar-jenisdiet', 'SysAdmin\Master\MasterController@getDaftarJenisDiet');
        Route::get('sysadmin/master/get-daftar-jeniswaktu', 'SysAdmin\Master\MasterController@getDaftarJenisWaktu');
        Route::get('sysadmin/master/get-departemen', 'SysAdmin\Master\MasterController@getDepartemen');

        Route::get('sysadmin/master/get-daftar-kategorydiet', 'SysAdmin\Master\MasterController@getKategoryDiet');
        Route::get('sysadmin/master/get-combo-siklus-gizi', 'SysAdmin\Master\MasterController@getComboSiklus');
        Route::get('sysadmin/master/get-daftar-siklus-gizi', 'SysAdmin\Master\MasterController@getDaftarSiklusGizi');
        Route::get('sysadmin/master/get-data-paket', 'SysAdmin\Master\MasterController@getListPaket');
        Route::get('sysadmin/master/get-paketbyid', 'SysAdmin\Master\MasterController@getPaketbyId');

        Route::get('sysadmin/master/get-daftar-pelayananmutu', 'SysAdmin\Master\MasterController@getDaftarPelayananMutu');
        Route::get('sysadmin/master/get-combo-kios', 'SysAdmin\Master\MasterController@getMasterKiosk');
        Route::get('sysadmin/master/get-setting-kios', 'SysAdmin\Master\MasterController@getSettingKios');

        // GET //

        // POST //
        Route::post('sysadmin/master/delete-setting-kiosk', 'SysAdmin\Master\MasterController@deleteSettingKiosk');
        Route::post('sysadmin/master/save-setting-kiosk', 'SysAdmin\Master\MasterController@saveSettingKiosk');
        Route::post('sysadmin/master/save-siklus-gizi', 'SysAdmin\Master\MasterController@saveSiklusGizi');
        Route::post('sysadmin/master/delete-siklus-gizi', 'SysAdmin\Master\MasterController@deleteSiklusGizi');
        Route::post('sysadmin/master/save-statusenabled-produk', 'SysAdmin\Master\MasterController@UpdateStatusEnabledProduk');
        Route::post('sysadmin/master/save-data-produk', 'SysAdmin\Master\MasterController@saveDataProduk');
        Route::post('sysadmin/master/save-kelompok-produk-bpjs', 'SysAdmin\Master\MasterController@SaveKelompokProduk');
        Route::post('sysadmin/master/save-konversi-satuan', 'SysAdmin\Master\MasterController@SaveKonversiSatuan');
        Route::post('sysadmin/master/delete-konversi-satuan', 'SysAdmin\Master\MasterController@hapusKonversiSatuan');
        Route::post('sysadmin/master/save-statusenabled-rekanan', 'SysAdmin\Master\MasterController@UpdateStatusEnabledRekanan');
        Route::post('sysadmin/master/save-data-rekanan', 'SysAdmin\Master\MasterController@saveDataRekanan');

        Route::post('sysadmin/master/save-table-row', 'SysAdmin\Master\MasterController@saveTable');
        Route::post('sysadmin/master/hapus-tarif-harganetto', 'SysAdmin\Master\MasterController@hapusHargaNettoByKelas');
        // Route::post('sysadmin/master/save-harganettoprodukbykelas', 'SysAdmin\Master\MasterController@saveharganettoprodukbykelas_kelasD');
        Route::post('sysadmin/master/save-harganettoprodukbykelas', 'SysAdmin\Master\MasterController@saveharganettoprodukbykelasM_D');
        Route::post('sysadmin/master/mapping-ruangan-to-produk-disable', 'SysAdmin\Master\MasterController@tombolDisable');
        Route::post('sysadmin/master/delete-mapping-ruangan-to-produk', 'SysAdmin\Master\MasterController@DeleteMappingProdukToRuangan');

        Route::post('sysadmin/master/save-data-mapproduktoruangan', 'SysAdmin\Master\MasterController@addProdukToRuangan');
        Route::post('sysadmin/master/save-data-bed', 'SysAdmin\Master\MasterController@saveBed');
        Route::post('sysadmin/master/save-jenisdiet', 'SysAdmin\Master\MasterController@saveJenisDiet');
        Route::post('sysadmin/master/delete-jenisdiet', 'SysAdmin\Master\MasterController@deleteJenisDiet');
        Route::post('sysadmin/master/save-jeniswaktu', 'SysAdmin\Master\MasterController@saveJenisWaktu');
        Route::post('sysadmin/master/delete-jeniswaktu', 'SysAdmin\Master\MasterController@deleteJenisWaktu');
        Route::post('sysadmin/master/save-kategorydiet', 'SysAdmin\Master\MasterController@saveKategoryDiet');
        Route::post('sysadmin/master/delete-kategorydiet', 'SysAdmin\Master\MasterController@deleteKategoryDiet');
        Route::post('sysadmin/master/save-data-paket', 'SysAdmin\Master\MasterController@saveDataPaket');
        Route::post('sysadmin/master/save-statusenabled-paket', 'SysAdmin\Master\MasterController@UpdateStatusEnabledPaket');

        Route::post('sysadmin/master/save-pelayananmutu', 'SysAdmin\Master\MasterController@savePelayananMutu');
        Route::post('sysadmin/master/delete-pelayananmutu', 'SysAdmin\Master\MasterController@deletePelayananMutu');
        Route::post('sysadmin/master/aktif-pelayananmutu', 'SysAdmin\Master\MasterController@aktifPelayananMutu');
        // POST //
        // });
        // });

        // Route::group(['prefix' => 'tatarekening'], function () {
        Route::post('tatarekening/save-akomodasi-tea', 'TataRekening\TagihanController@saveAkomodasiOtomatis');
        Route::get('tatarekening/get-detail_apd', 'TataRekening\TagihanController@getDetailpasien');
        Route::get('tatarekening/get-combo-detail-regis', 'TataRekening\TagihanController@getDataComboDetailRegis');
        Route::get('tatarekening/get-detail-pasien', 'TataRekening\TagihanController@getAPD');
        Route::get('tatarekening/get-data-master', 'TataRekening\TagihanController@getTableMaster');  //done
        Route::get('tatarekening/get-sudah-verif', 'TataRekening\TagihanController@getVerifikasiNoregistrasi');
        Route::post('tatarekening/save-konsul-keruangan', 'TataRekening\TagihanController@simpanInsertAPD');
        Route::post('tatarekening/save-update-dokter_apd', 'TataRekening\TagihanController@simpanUpdateDokterAPD');
        Route::post('tatarekening/save-update-rekanan_pd', 'TataRekening\TagihanController@simpanUpdateRekananPD');
        Route::post('tatarekening/hapus-antrian-pasien', 'TataRekening\TagihanController@hapusAPD');
        Route::post('tatarekening/ubah-tgl-detailregistrasi', 'TataRekening\TagihanController@ubahTanggalDetailRegis');
        Route::get('tatarekening/get-status-close-pemeriksaan', 'TataRekening\TagihanController@getStatusClosePemeriksaan');
        Route::get('tatarekening/get-data-login', 'TataRekening\TagihanController@getLogin');  //done
        Route::get('tatarekening/detail-tagihan/{noRegister}', 'TataRekening\TagihanController@detailTagihan');  //done
        Route::post('tatarekening/save-update-dokter_ppp', 'TataRekening\TagihanController@simpanUpdateDokterPPP');
        Route::post('tatarekening/save-update-tanggal_pelayanan', 'TataRekening\TagihanController@simpanUpdateTglPelayanan');
        Route::post('tatarekening/save-update-harga-diskon-komponen', 'TataRekening\TagihanController@simpanUpdateDiskonKomponen');
        Route::get('tatarekening/get-data-login-cetakan', 'TataRekening\TagihanController@getDataLogin');  //done
        Route::get('tatarekening/get-pegawai-saeutik', 'TataRekening\TagihanController@getPegawaiSaeutik');  //done
        Route::get('tatarekening/get-combo-jenis-petugas', 'TataRekening\TagihanController@getComboJenisPetugasPel');
        Route::get('tatarekening/get-petugasbypelayananpasien', 'TataRekening\TagihanController@getPelPetugasByPelPasien');
        Route::post('tatarekening/save-ppasienpetugas', 'TataRekening\TagihanController@simpanDokterPPP');
        Route::post('tatarekening/hapus-ppasienpetugas', 'TataRekening\TagihanController@hapusPPP');
        Route::post('tatarekening/update-harga-pelayanan-pasien', 'TataRekening\TagihanController@UpdateHargaPelayananPasien');
        Route::post('tatarekening/delete-pelayanan-pasien', 'TataRekening\TagihanController@deletePelayananPasien');
        Route::get('tatarekening/get-komponenharga-pelayanan', 'TataRekening\TagihanController@getKomponenHargaPelayanan');
        Route::get('tatarekening/detail-tindakan-takterklaim', 'TataRekening\TagihanController@getTindakanTakTerklaim');  //done
        Route::post('tatarekening/close-pemeriksaan', 'TataRekening\TagihanController@closePemeriksaan');  //done
        Route::get('tatarekening/get-data-combo-order', 'TataRekening\TagihanController@getDataComboOrder');  //done
        Route::get('tatarekening/get-tindakan', 'TataRekening\TagihanController@getTindakanParts');  //done

        Route::get('tatarekening/get-header-data-pasien/{noregistrasi}', 'TataRekening\TagihanController@getHeaderRekapTagihan'); //done
        Route::get('tatarekening/get-rekap-tagihan-pasien/{noregistrasi}', 'TataRekening\TagihanController@getRekapTagihan'); //done
        Route::get('tatarekening/get-pasien-bynorec', 'TataRekening\TagihanController@getPasienBynorecpdapd'); //done


        Route::get('tatarekening/get-status-verif-piutang', 'TataRekening\TagihanController@getStatusVerifPiutang'); //done
        Route::post('tatarekening/save-log-unverifikasi-tarek', 'TataRekening\TagihanController@saveLoggingUnverifTarek');
        Route::get('tatarekening/daftar-pasien-pulang', 'TataRekening\TagihanController@daftarPasienPulang'); //done
        Route::post('tatarekening/save-selesai-transaksi', 'TataRekening\TagihanController@closePemeriksaanPD'); //done
        Route::get('tatarekening/get-struk-pelayanan/{noRegister}', 'TataRekening\TagihanController@getStrukPelayanan');
        Route::get('tatarekening/get-status-verif-piutang', 'TataRekening\TagihanController@getStatusVerifPiutang');
        Route::post('tatarekening/batal-verifikasi-tagihan', 'TataRekening\TagihanController@batalVerifikasiTagihan');
        Route::get('tatarekening/verifikasi-tagihan2', 'TataRekening\TagihanController@verifikasiTagihan2');
//          Route::get('tatarekening/detail-tagihan-verifikasi', 'TataRekening\TagihanController@detailTagihanVerifikasi');
        Route::post('tatarekening/simpan-verifikasi-tagihan', 'TataRekening\TagihanController@simpanVerifikasiTagihan');
        Route::post('tatarekening/simpan-verifikasi-tagihan-tatarekening', 'TataRekening\TagihanController@simpanVerifikasiTagihanTatarekening');
        Route::get('tatarekening/detail-tagihan-verifikasi', 'TataRekening\TagihanController@detailTagihanVerifikasiTatarekening');
        Route::get('tatarekening/get-data-combo-daftarregpasien', 'TataRekening\TagihanController@getDataComboDaftarRegPasien');
        Route::get('tatarekening/get-penjaminbykelompokpasien', 'TataRekening\TagihanController@getPenjaminByKelompokPasien');
        Route::post('tatarekening/save-update-rekanan_pd', 'TataRekening\TagihanController@simpanUpdateRekananPD');
        Route::get('tatarekening/daftar-piutang-pasien', 'TataRekening\TagihanController@daftarPiutangPasien');
        Route::post('tatarekening/verify-piutang-pasien', 'TataRekening\TagihanController@verifyPiutangPasien');
        Route::post('tatarekening/cancel-verify-piutang-pasien', 'TataRekening\TagihanController@cancelVerifyPiutangPasien');
        Route::get('tatarekening/get-daftar-registrasi-pasien', 'TataRekening\TagihanController@getDaftarRegistrasiPasien');
        Route::get('tatarekening/get-norec-apd', 'TataRekening\TagihanController@getNorecAPD');
        Route::post('tatarekening/save-update-dokter', 'TataRekening\TagihanController@simpanUpdateDokter');
        Route::post('tatarekening/save-update-dokter_apd', 'TataRekening\TagihanController@simpanUpdateDokterAPD');
        Route::post('tatarekening/save-pemakaian-asuransi', 'TataRekening\TagihanController@simpanPemakaianAsuransi');
        Route::get('tatarekening/get-data-apd', 'TataRekening\TagihanController@getAntrianPasien');
        Route::get('tatarekening/get-daftar-deposit-pasien', 'TataRekening\TagihanController@getDaftarDepositPasien');
        Route::get('tatarekening/get-pasien-dalam-perawatan', 'TataRekening\TagihanController@getLapPasienDalamPerawatan');
        Route::get('tatarekening/get-data-lap-pendapatan-perkelas', 'TataRekening\TagihanController@getDataLaporanPendapatanPerkelas');
        Route::get('tatarekening/get-list-laporan-volume', 'TataRekening\TagihanController@getDataLaporanVolumeKegiatan');
        Route::get('tatarekening/get-laporan-rehab', 'TataRekening\TagihanController@getLaporanRehab');
        Route::get('tatarekening/get-data-diagnosa-pasien', 'TataRekening\TagihanController@getDataLaporanDiagnosaPasien');
        Route::get('tatarekening/get-penerimaan-deposit', 'TataRekening\TagihanController@getDepositPasienPulang');
        Route::get('tatarekening/get-rekapkunjungan-rawatjalan', 'TataRekening\TagihanController@getRekapKunjunganRJ');
        Route::get('tatarekening/get-rekappembayaran-jasapelayanan', 'TataRekening\TagihanController@getRekapPembayaranJasaPelayanan');
        Route::get('tatarekening/get-detail-rekappembayaran-jasapelayanan', 'TataRekening\TagihanController@getDetailJasaPelayanan');
        Route::get('tatarekening/get-laporankegiatanoperasional', 'TataRekening\TagihanController@getLaporanKegiatanOperasionalRJ');
        Route::get('tatarekening/get-detail-laporankegiatan', 'TataRekening\TagihanController@getLaporanKegiatanOperasionalDetail');
        Route::get('tatarekening/get-laporankegiatanoperasional-detail', 'TataRekening\TagihanController@getLaporanKegiatanOperasionalRuangan');
        Route::get('tatarekening/data-pasien-perjanjian/{noRegister}', 'TataRekening\TagihanController@getDataPasienPerjanjian');
        Route::get('tatarekening/list-kelompok-pasienperjanjian', 'TataRekening\TagihanController@getJenisPasienPerjanjian');
        Route::get('tatarekening/get-data-detail-verifikasi', 'TataRekening\TagihanController@getDataDetailVerifikasi');
        Route::get('tatarekening/get-laporan-pendapatan-instalasi', 'TataRekening\TagihanController@getPendapatanInstalasi');
        Route::get('tatarekening/get-daftar-sync-trans', 'TataRekening\TagihanController@getDatfarSyncTrans');
        Route::post('tatarekening/sync-trandata-pasien', 'TataRekening\TagihanController@saveSyncTrans');
        Route::post('tatarekening/sync-trandata-pasien-emr', 'TataRekening\TagihanController@saveSyncTransEMR');

        Route::get('tatarekening/detail-tagihan-tes/{noRegister}', 'TataRekening\TagihanController@detailTagihanV22');  //done tes

        Route::post('tatarekening/sync-trandata-new', 'TataRekening\TagihanController@saveSyncTransNEW');
        Route::post('tatarekening/sync-trandata-update-status', 'TataRekening\TagihanController@updateStatusCovid');

        Route::post('tatarekening/save-nosuratketerangakematian-pasien', 'TataRekening\TagihanController@saveNomorSuratKeteranganKematian');
        Route::post('tatarekening/delete-nosuratketerangakematian-pasien', 'TataRekening\TagihanController@HapusNomorSuratKeteranganKematian');
        Route::get('tatarekening/get-nosuratketerangakematian-pasien', 'TataRekening\TagihanController@getDataNoSuratKeteranganKematian');
//          Route::get('tatarekening/get-nosuratketerangakematian-pasien','TataRekening\TagihanController@getDataSuratKeteranganJenazah');
        Route::post('tatarekening/save-suratpelimpahanjenazah-pasien', 'TataRekening\TagihanController@savePelimpahanRuangJenazah');
        Route::post('tatarekening/delete-suratpelimpahanjenazah-pasien', 'TataRekening\TagihanController@hapusPelimpahanRuangJenazah');
        Route::get('tatarekening/get-datasuratpelimpahanjenazah', 'TataRekening\TagihanController@getDataPelimpahanJenazah');
        //*****END*****

        // Route::group(['prefix' => 'tindakan'], function () {
//                Route::get('get-tindakan', 'TataRekening\TindakanController@getTindakan');
        Route::get('tatarekening/tindakan/get-combo', 'TataRekening\TindakanController@getCombo');
        Route::get('tatarekening/tindakan/get-pegawaibyjenispetugas', 'TataRekening\TindakanController@getPegawaiByJenisPetugasPe');
        Route::post('tatarekening/tindakan/save-tindakan', 'TataRekening\TindakanController@saveTindakan');
        Route::get('tatarekening/tindakan/get-komponenharga', 'TataRekening\TindakanController@getKomponenHarga');
        Route::post('tatarekening/tindakan/update-dokter-pel-pasien', 'TataRekening\TindakanController@updateDokterAll');
        Route::get('tatarekening/tindakan/get-komponenharga-jasa-medis', 'TataRekening\TindakanController@getKomponenHargaJasaMedis');
        Route::post('tatarekening/tindakan/update-dokter-pel-pasien-new', 'TataRekening\TindakanController@updateDokterppp');
        Route::get('tatarekening/tindakan/get-pegawai-byjenispetugas-byapd', 'TataRekening\TindakanController@getPegawaiByJnsPetugasByAPD');
        Route::get('tatarekening/tindakan/get-data-login-pegawai', 'TataRekening\TindakanController@getDataLogin');
        Route::get('tatarekening/tindakan/get-pelayanan-pasien', 'TataRekening\TindakanController@getPelayananPasienNonDetail');
        Route::post('tatarekening/tindakan/hapus-pelayanan-pasien', 'TataRekening\TindakanController@hapusPelayananPasien');
        Route::post('tatarekening/tindakan/save-tindakan-tidak-terklaim', 'TataRekening\TindakanController@saveTindakanTidakTerklaim');
        Route::post('tatarekening/tindakan/hapus-tindakan-tidak-terklaim', 'TataRekening\TindakanController@hapusPelayananPasienTidakTerklaim');
        Route::get('tatarekening/tindakan/get-riwayat-tindakan', 'TataRekening\TindakanController@getRiwayarRuanganPerAntrian');
        Route::get('tatarekening/tindakan/get-pegawaipenginput', 'TataRekening\TindakanController@GetPegawaiPenginputTindakan');
        Route::get('tatarekening/tindakan/get-tindakan', 'TataRekening\TindakanController@getTindakanPart');
        Route::get('tatarekening/tindakan/get-jenis-pelayanan/{norec_pd}', 'TataRekening\TindakanController@getJenisPelayananByNorecPd');
        Route::get('tatarekening/tindakan/get-tanggal-posting', 'TataRekening\TindakanController@getPostingTgl');
        Route::get('tatarekening/tindakan/get-pasien-bynorec', 'TataRekening\TindakanController@getHeaderInputTindakan');
        Route::post('tatarekening/tindakan/update-dokter-pel-pasien', 'TataRekening\TindakanController@updateDokterAll');
        Route::post('tatarekening/tindakan/update-dokter-pel-pasien-new', 'TataRekening\TindakanController@updateDokterppp');
        Route::get('tatarekening/tindakan/get-mutu', 'TataRekening\TindakanController@getMutu');
        Route::post('tatarekening/tindakan/save-mutu', 'TataRekening\TindakanController@saveMutu');
        Route::get('tatarekening/tindakan/get-riwayat-mutu', 'TataRekening\TindakanController@getRiwayatMutu');
        Route::post('tatarekening/tindakan/delete-mutu', 'TataRekening\TindakanController@delMutu');
        //     });
        //
        // });
        // Route::group(['prefix' => 'desktopservice'], function () {
        Route::get('desktopservice/get-antrian-display', 'DesktopService\DesktopServiceController@getDataDisplayAntrian');
        Route::get('desktopservice/get-data-for-rs', 'DesktopService\DesktopServiceController@getDataForRecordSet');
        Route::get('desktopservice/save-data-for-rs', 'DesktopService\DesktopServiceController@saveDataFromRecordSet');
        // });


        Route::get('report/kegiatan-rawat-inap', 'Report\ReportController@getKegiatanRanap');

        Route::post('get-token', 'Auth\LoginController@getTokens');
        Route::get('global/get-menu',  'SysAdmin\ModulAplikasiController@getMenuDinamisByName');
       
    });

    Route::get('logistik/get-stok-minimum_global', 'Logistik\LogistikController@getStokMinimumGlobal');
    Route::get('logistik/get-topten-obat', 'Logistik\LogistikController@getTrendPemakaianObat');
    Route::get('logistik/get-produk', 'Logistik\LogistikController@getProdukAvailable');

    Route::get('lab/get-detail-transaksi', 'SysAdmin\ExternalController@getDetailTransaksiLab');
    Route::get('lab/get-by-tanggal', 'SysAdmin\ExternalController@getByTanggalLab');
    Route::get('lab/post-lab', 'SysAdmin\ExternalController@postLab');
    Route::get('profile/update-kdprofile', 'SysAdmin\ExternalController@updateKdProfile');


    Route::group(['prefix' => 'jkn'], function () {
        Route::post('get-token', 'Auth\LoginController@getTokens');
        Route::post('get-no-antrean', 'ReservasiOnline\ReservasiOnlineController@GetNoAntrianMobileJKN');
        Route::post('get-rekap-antrean', 'ReservasiOnline\ReservasiOnlineController@GetRekapMobileJKN');
        Route::post('get-kode-booking-operasi', 'ReservasiOnline\ReservasiOnlineController@getKodeBokingOperasi');
        Route::post('get-jadwal-operasi', 'ReservasiOnline\ReservasiOnlineController@getJadwalOperasi');
//        Route::post('get-token', 'Auth\LoginController@getTokens');
    });

    Route::group(['prefix' => 'api'], function () {
        Route::post('generate-token', 'Auth\LoginController@getTokens');
        Route::get('bed-monitor', 'SysAdmin\GeneralController@readBed');

    });
//    Route::get('{role}/{pages}', function ($type, $group, $filename) {
//        dd('asa');
//    });
      Route::get('{role}/{pages}', "MainController@show_page")->name("show_page");
});

Route::get('encode-base64/{data}', function ($data) {
    return base64_encode($data);
});
Route::get('decode-base64/{data}', function ($data) {
    return base64_decode($data);
});


Route::get('/katalog', function () {
    return view('module.katalog.index');
});
Route::get('storage/{type}/{group}/{filename}', function ($type, $group, $filename) {
    $path = storage_path($type . '/' . $group . '/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('storage/app/photo/{filename}', function ($filename)
{
    $path = storage_path('public/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});
Route::get('storage/sdm/sip-str/{norec}/{filename}', function ($norec,$filename)
{
    $path = public_path('SDM/FileSipStr/'.$norec.'/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('storage/sdm/sk/{id}/{filename}', function ($id,$filename)
{
    $path = public_path('SDM/SuratKeputusan/'.$id.'/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});


Route::get('storage/test', function () {
    return var_dump('test');
});
Route::post('ecg','Auth\LoginController@saveECG');
Route::group(['middleware' => 'cors'], function () {
    Route::get('bor', 'Bridging\BridgingSiranapV2Controller@getdataBOR');
    Route::get('diagnosa_iri', 'Bridging\BridgingSiranapV2Controller@getDiagnosaRanap');
    Route::get('diagnosa_irj', 'Bridging\BridgingSiranapV2Controller@getDiagnosaRajal');
    Route::post('bedmonitor-post', 'Bridging\BridgingSiranapV2Controller@getBedMonitor');
    Route::get('bedmonitor', 'Auth\BridgingSiranapV2Controller@getBedMonitorRS');
    Route::get('{instalasi}', 'Bridging\BridgingSiranapV2Controller@getKunjungan');
    Route::get('hapus-bed/{kode_tipe_pasien}/{kode_kelas_ruang}', 'Bridging\BridgingSiranapV2Controller@hapusBed');

    Route::post('LapV2/PasienMasuk/{method}', 'Bridging\BridgingSiranapV2Controller@PasienMasuk');
    Route::post('LapV2/PasienDirawatKomorbid/{method}', 'Bridging\BridgingSiranapV2Controller@Komorbid');
    Route::post('LapV2/PasienDirawatTanpaKomorbid/{method}', 'Bridging\BridgingSiranapV2Controller@NonKomorbid');
    Route::post('LapV2/PasienKeluar/{method}', 'Bridging\BridgingSiranapV2Controller@PasienKeluar');

    Route::post('Referensi/usia_meninggal_probable', 'Bridging\BridgingSiranapV2Controller@getRefUsia');
    Route::post('Referensi/tempat_tidur', 'Bridging\BridgingSiranapV2Controller@getRefTT');
    Route::post('Fasyankes/{method}', 'Bridging\BridgingSiranapV2Controller@Fasyankes');

    Route::post('Referensi/kebutuhan_sdm', 'Bridging\BridgingSiranapV2Controller@getReffSDM');
    Route::post('Fasyankes/sdm/{method}', 'Bridging\BridgingSiranapV2Controller@FasyankesSDM');

    Route::post('Referensi/kebutuhan_apd', 'Bridging\BridgingSiranapV2Controller@getReffAPD');
    Route::post('Fasyankes/apd/{method}', 'Bridging\BridgingSiranapV2Controller@FasyankesAPD');

});
Route::post('/ecg', 'MainController@saveECG');
