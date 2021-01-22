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
session_start();
Route::get('/', "Auth\AuthController@show")->name("index");
// Route::post('/', "Auth\AuthController@loginKeun")->name("login_validation");

Route::get('/logout', "Auth\AuthController@logoutKeun")->name("logout");

//Route::get('/bed-delete', "Auth\AuthController@hapusBed")->name("hapusBed");

Route::group(['middleware' => 'cors', 'prefix' => 'service/transdata'], function () {
    Route::get('get-signature', 'Auth\AuthController@getSignature');
    Route::get('get', 'MedicalRecord\PasienController@getAgama');
    Route::get('get-pasien', 'MedicalRecord\PasienController@getPasien');
    Route::get('get-master-pasien', 'MedicalRecord\PasienController@getPasienMaster');
    Route::post('save-pasien', 'MedicalRecord\PasienController@saveDataPasien');
    Route::post('save-update-pasien', 'MedicalRecord\PasienController@saveUpdatePasien');
    Route::post('save-alamat-pasien', 'MedicalRecord\PasienController@saveUpdateAlamat');
    Route::post('save-pasien-v2', 'MedicalRecord\PasienController@savePasienWithAlamat');


    Route::post('save-medical-record', 'MedicalRecord\TransmedicController@saveDataMedicalRecord');
    Route::post('save-rujukan', 'MedicalRecord\TransmedicController@saveRujukan');
    Route::get('get-medical-record', 'MedicalRecord\TransmedicController@getPasienMedicalRecord');
    Route::get('get-profile', 'MedicalRecord\TransmedicController@getProfile');
    Route::get('get-ruangan', 'MedicalRecord\TransmedicController@getRuangan');
    Route::get('get-ruangan-all', 'MedicalRecord\TransmedicController@getRuanganAll');
    Route::get('get-emr', 'MedicalRecord\TransmedicController@getMasterEMR');
    Route::get('get-kelompokvariabel', 'MedicalRecord\TransmedicController@getKelompokVariable');
    Route::get('get-rujukan', 'MedicalRecord\TransmedicController@getRujukan');
    Route::get('get-daftar-rujukan', 'MedicalRecord\TransmedicController@getDaftarRujukan');
    Route::get('get-daftar-rujukan', 'MedicalRecord\TransmedicController@getDaftarRujukan');
    Route::post('update-status-rujukan', 'MedicalRecord\TransmedicController@updateStatusRujukan');
    Route::post('save-ruangan', 'MedicalRecord\GeneralController@saveRuanganBpjs');
    Route::post('save-faskes', 'MedicalRecord\GeneralController@saveFaskesBPJS');

    Route::get('get-emr-transaksi-detail-form', 'MedicalRecord\TransmedicController@getEMRTransaksiDetailForm');
    Route::get('get-menu-rekam-medis-dynamic', 'MedicalRecord\TransmedicController@getMenuRekamMedisAtuh');
    Route::get('emr/get-rekam-medis-dynamic', 'MedicalRecord\TransmedicController@getRekamMedisAtuh');
    Route::get('emr/get-emr-transaksi-detail', 'MedicalRecord\TransmedicController@getEMRTransaksiDetail');
    Route::get('emr/get-emrbyid', 'MedicalRecord\TransmedicController@getMenuEmrById');
    Route::get('get-master-{param}', 'MedicalRecord\TransmedicController@getMasterTable');
    Route::get('get-status-covid', 'MedicalRecord\TransmedicController@getStatusCovid');

    Route::post('update-status-covid', 'MedicalRecord\TransmedicController@updateStatusCovid');
    Route::post('check-availability', 'MedicalRecord\MedisisController@checkAvailability');
    Route::post('get-profile-by-nisn', 'MedicalRecord\MedisisController@getProfilebyNISN');
    Route::post('save-siswa', 'MedicalRecord\MedisisController@saveDataSiswa');
    Route::post('update-siswa', 'MedicalRecord\MedisisController@updateDataSiswa');
    Route::post('save-antropometri', 'MedicalRecord\MedisisController@saveAntropometri');
    Route::post('delete-antropometri', 'MedicalRecord\MedisisController@deleteAntropometri');
    Route::post('save-hematologi', 'MedicalRecord\MedisisController@saveHematologi');
    Route::post('delete-hematologi', 'MedicalRecord\MedisisController@deleteHematologi');
    Route::post('save-tanda-vital', 'MedicalRecord\MedisisController@saveTandaVital');
    Route::post('delete-tanda-vital', 'MedicalRecord\MedisisController@deleteTandaVital');
    Route::post('save-kecelakaan', 'MedicalRecord\MedisisController@saveKecelakaan');
    Route::post('delete-kecelakaan', 'MedicalRecord\MedisisController@deleteKecelakaan');
    Route::post('save-sakit', 'MedicalRecord\MedisisController@saveSakit');
    Route::post('delete-sakit', 'MedicalRecord\MedisisController@deleteSakit');
    Route::post('save-imunisasi', 'MedicalRecord\MedisisController@saveImunisasi');
    Route::post('delete-imunisasi', 'MedicalRecord\MedisisController@deleteImunisasi');
    Route::post('save-gigi', 'MedicalRecord\MedisisController@saveGigi');
    Route::post('update-gigi', 'MedicalRecord\MedisisController@updateGigi');
    Route::post('delete-gigi', 'MedicalRecord\MedisisController@deleteGigi');
    Route::post('update-statuspasiencovid', 'MedicalRecord\TransmedicController@saveUpdateStatusCovidPasien');
    Route::get('get-kelas-all', 'MedicalRecord\TransmedicController@getKelasAll');
    //** SDM */
    Route::post('save-sync-pegawai', 'MedicalRecord\TransmedicController@saveDataSyncPegawai');
    Route::get('get-jabatan-all', 'MedicalRecord\TransmedicController@getJabatanAll');
    Route::get('get-pangkat-all', 'MedicalRecord\TransmedicController@getPangkatAll');
    Route::get('get-pendidikan-all', 'MedicalRecord\TransmedicController@getPendidikanAll');
    Route::get('get-jenispegawai-all', 'MedicalRecord\TransmedicController@getJenisPegawaiAll');
//    Route::get('pegawai/show', "MedicalRecord\TransmedicController@showDataPegawai")->name("showPegawai");

    //** END SDM */

    //** KETERSEDIAAN TEMPAT TIDUR */
    Route::post('save-sync-ketersediaan-tempat-tidur', 'MedicalRecord\TransmedicController@saveDataSyncKetersediaanTempatTidur');
    //** KETERSEDIAAN TEMPAT TIDUR */

    //** LOGISTIK */
    Route::get('get-produk-all', 'MedicalRecord\TransmedicController@getProdukAll');
    Route::get('get-satuanstandar-all', 'MedicalRecord\TransmedicController@getJenisPegawaiAll');
    Route::post('save-sync-transaksi-stok', 'MedicalRecord\TransmedicController@getSatuanAll');
    //** LOGISTIK */
 });

//Route::get('/', function () {
//    return view('welcome');
//});
Route::group([ 'prefix' => 'view'], function () {
    Route::get('pasien', 'MedicalRecord\GeneralController@getPasien')->name('pasien');
    Route::get('detail-emr', 'MedicalRecord\GeneralController@getDetailEMR')->name('detailemr');
    Route::get('get-pasien', 'MedicalRecord\GeneralController@getPasien');
});

Route::get('/emr', function () {
    return view('form/emr');
});
Route::get('/index', function () {
    return view('template/template');
});
Route::post('/bed-save-dt', "Auth\AuthController@saveDataBeds")->name("saveDataBed");
Route::get('/bed', "Auth\AuthController@showIndex")->name("home");
Route::get('/data-harian', "Auth\AuthController@getDataHarian")->name("dataHarian");
Route::get('/daftar-pasien-aktif', "Auth\AuthController@getDaftarPasienAktif")->name("daftarPasienAktif");
Route::get('/bed-get', "Auth\AuthController@getDataBed")->name("showBedDetail");

Route::get('/bed-get-byid', "Auth\AuthController@getDataBed");
Route::group(["middleware" => "login_check"], function () {
  

    Route::get('/get-diagnosa-bykode/{kddiagnosa}', 'DashboardController@geTopTenDiagnosaByKD');
    Route::get('/get-diagnosa-bykode-byrsaddress/{kddiagnosa}', 'DashboardController@geTopTenDiagnosaByRSAddress');
    Route::get('/get-name-prov/{kode}', 'DashboardController@getNameRegionBykode');
    Route::get('/get-name-kota/{kode}/{kddiagnosa}', 'DashboardController@getNameKotaBykode');
    Route::get('/pelayanan-detail/{code}/{nama}/{kddiagnosa}','DashboardController@geTopTenDiagnosaDetail');
    Route::get('/get-detail-table-diagnosa','DashboardController@getDetailTableDiag');
    Route::get('/get-chart-by-rs','DashboardController@getChartByRS');
    Route::get('/get-detail-rs-table','DashboardController@getDetailRS');
    Route::get('/get-data-dashboard','DashboardController@getDataDashboard');
    Route::get('/get-combo-diagnosa','DashboardController@getComboDiagnosa');
    Route::get('/get-data-chart-rs','DashboardController@getDataChartRS');
    Route::get('/get-data-faskes','DashboardController@getDataFaskes');
    Route::get('/get-pasien-bymap', 'DashboardController@getMapDataKabupatenKota');
    Route::get('/get-pasien-by-kotakab', 'DashboardController@getDetailPasienKecamatan');
    Route::get('/get-data-flag','DashboardController@getDataDashboardFlag');
    Route::get('/get-dashboard-pegawai','DashboardController@getDashboardPegawai');
    Route::get('/get-dashboard-persediaan','DashboardController@getDashboardPersediaan');
    Route::get('/get-dashboard-persediaan-stok','DashboardController@getDashboardPersediaanStok');
    Route::get('/get-dashboard-kamar','DashboardController@getKetersediaanKamar');
    Route::get('/get-detail-covid-pasien','DashboardController@getDetailCovid');
    Route::get('/get-detail-kunjungan-pasien','DashboardController@getDetailKun');
    Route::get('/get-detail-bed','DashboardController@getDetailBed');

    Route::get('{role}/{pages}', "MainController@show_page")->name("show_page");
    Route::get('/pegawai-show', "MainController@showDataPegawai")->name("showPegawai");
    Route::post('/pegawai-save', "MainController@savePegawai")->name("savePegawai");
    Route::get('/pegawai-delete', "MainController@hapusPegawai")->name("hapusPegawai");

    Route::get('/bed-show', "MainController@showDataBed")->name("showBed");
    Route::post('/bed-save', "MainController@saveBed")->name("saveBed");
    Route::get('/bed-delete', "MainController@hapusBed")->name("hapusBed");

    Route::get('/stok-show', "MainController@showDataStok")->name("showStok");
    Route::post('/stok-save', "MainController@saveStok")->name("saveStok");
    Route::get('/stok-delete', "MainController@hapusStok")->name("hapusStok");

});

Route::get('/katalog', function () {
    return view('module.katalog.index');
});
