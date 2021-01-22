1. Signature/Token
    - api service : https://svr1.rsdarurat.com/service/medifirst2000/auth/sign-in
	- body->raw : 	{
						"namaUser":"wsa.simrs",
						"kataSandi":"admin"
						"isTrust" : true
					}
	- Content-Type : application/json
	- response : 	{
						"data": {
							"id": 30158,
							"kdProfile": 18,
							"namaUser": "wsa.simrs",
							"kataSandi": "f3640fffae2b1635803263826c83da8418c6396c",
							"passCode": "d033e22ae348aeb5660fc2140aec35850c4da997",
							"kelompokUser": {
								"id": 2,
								"kelompokUser": "operator"
							},
							"mapLoginUserToRuangan": [],
							"pegawai": {
								"id": 320261028,
								"namaLengkap": "ADMIN WISMA ATLET",
								"tempatLahir": "-",
								"tglLahir": null,
								"noIdentitas": null,
								"statusEnabled": true,
								"jenisPegawai": {
									"id": 1,
									"kdprofile": 17,
									"statusenabled": true,
									"kodeexternal": null,
									"namaexternal": "DOKTER",
									"norec": "385cd9d8-e6dc-435e-be1a-0da6dd89",
									"reportdisplay": "DOKTER",
									"objectdetailkelompokpegawaifk": 1,
									"jenispegawai": "DOKTER",
									"kdjenispegawai": "A0",
									"qjenispegawai": 1
								},
								"kdProfile": 18,
								"ruangan": {
									"id": null,
									"namaruangan": "tidak ada mapping ke ruangan"
								}
							},
							"profile": {
								"id": 18,
								"kdprofile": 18,
								"statusenabled": true,
								"kodeexternal": null,
								"namaexternal": "Rumah Sakit Darurat Covid -19 Wisma Atlet",
								"norec": "17                              ",
								"reportdisplay": "RUMAH SAKIT DARURAT PENANGANAN COVID -19 WISMA ATLET",
								"objectaccountfk": null,
								"objectdepartemenfk": null,
								"objectdesakelurahanfk": null,
								"objectjenisprofilefk": null,
								"objectjenistariffk": null,
								"objectkecamatanfk": null,
								"objectkelaslevelfk": null,
								"objectkotakabupatenfk": null,
								"objectpegawaikepalafk": null,
								"objectpemilikprofilefk": null,
								"objectpropinsifk": null,
								"objectsatuankerjafk": null,
								"objectstatusakreditasilastfk": null,
								"objectstatussuratijinlastfk": null,
								"objecttahapanakreditasilastfk": null,
								"alamatemail": null,
								"alamatlengkap": "RT.001 RW.009 KEBON KOSONG KEC. KEMAYORAN, JAKARTA PUSAT, DKI JAKARTA 1630",
								"faksimile": null,
								"fixedphone": null,
								"kodepos": null,
								"luasbangunan": null,
								"luastanah": null,
								"messagetopasien": null,
								"mobilephone": null,
								"mottosemboyan": null,
								"namalengkap": "RUMAH SAKIT DARURAT COVID-19 WISMA ATLET",
								"nopkp": null,
								"nosuratijinlast": null,
								"npwp": null,
								"qprofile": null,
								"rtrw": "",
								"signaturebylast": null,
								"website": null,
								"gambarlogo": null,
								"tglakreditasilast": null,
								"tglregistrasi": null,
								"tglsuratijinexpiredlast": null,
								"tglsuratijinlast": null,
								"namapemerintahan": null,
								"namakota": "Jakarta",
								"logoprofil": null,
								"logopemerintahan": null
							}
						},
						"messages": {
							"X-AUTH-TOKEN": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ3c2Euc2ltcnMifQ.nIC8Aiibv6N9AeQ0kmLJEpQ_k8z_RqEAu4ot5a1SvMboOunZDZM7tH6ChAGjyigprNRHNtQOyzQSfxXt5Xy87Q"
						},
						"status": 201,
						"as": "#Inhuman"
					}
					
2. get data Master Pasien Alamat
	- api service : https://svr1.rsdarurat.com/medifirst2000/registrasi/get-combo-address
	- header->Content-Type : application/json
	- header->X-AUTH-TOKEN : {{Get Signature/Token}}
	- respone : {
					"kebangsaan": [
						{
							"id": 1,
							"name": "WNI"
						},
						{
							"id": 2,
							"name": "WNA"
						}
					],
					"negara": [
						{
							"id": 4,
							"namanegara": "BRUNEI DARUSSALAM"
						},
						{
							"id": 2,
							"namanegara": "FILIPINA"
						},
						{
							"id": 0,
							"namanegara": "INDONESIA"
						},
						{
							"id": 8,
							"namanegara": "KAMBOJA"
						},
						{
							"id": 6,
							"namanegara": "LAOS"
						},
						{
							"id": 9,
							"namanegara": "MALAYSIA"
						},
						{
							"id": 7,
							"namanegara": "MYANMAR"
						},
						{
							"id": 3,
							"namanegara": "SINGAPURA"
						},
						{
							"id": 1,
							"namanegara": "THAILAND"
						},
						{
							"id": 5,
							"namanegara": "VIETNAM"
						}
					],
					"kotakabupaten": [
						{
							"id": 5,
							"namakotakabupaten": "KAB. ACEH BARAT"
						},
						{
							"id": 12,
							"namakotakabupaten": "KAB. ACEH BARAT DAYA"
						},
						{
							"id": 6,
							"namakotakabupaten": "KAB. ACEH BESAR"
						},
						{
							"id": 14,
							"namakotakabupaten": "KAB. ACEH JAYA"
						},
						{
							"id": 1,
							"namakotakabupaten": "KAB. ACEH SELATAN"
						},
						{
							"id": 10,
							"namakotakabupaten": "KAB. ACEH SINGKIL"
						},
						{
							"id": 16,
							"namakotakabupaten": "KAB. ACEH TAMIANG"
						},
						{
							"id": 4,
							"namakotakabupaten": "KAB. ACEH TENGAH"
						},
						{
							"id": 2,
							"namakotakabupaten": "KAB. ACEH TENGGARA"
						},
						{
							"id": 3,
							"namakotakabupaten": "KAB. ACEH TIMUR"
						},
						{
							"id": 8,
							"namakotakabupaten": "KAB. ACEH UTARA"
						},
						{
							"id": 155,
							"namakotakabupaten": "KAB. ADM. KEP. SERIBU"
						},
						{
							"id": 62,
							"namakotakabupaten": "KAB. AGAM"
						},
						{
							"id": 297,
							"namakotakabupaten": "KAB. ALOR"
						},
						{
							"id": 32,
							"namakotakabupaten": "KAB. ASAHAN"
						},
						{
							"id": 491,
							"namakotakabupaten": "KAB. ASMAT"
						},
						{
							"id": 276,
							"namakotakabupaten": "KAB. BADUNG"
						},
						{
							"id": 353,
							"namakotakabupaten": "KAB. BALANGAN"
						},
						{
							"id": 164,
							"namakotakabupaten": "KAB. BANDUNG"
						},
						{
							"id": 177,
							"namakotakabupaten": "KAB. BANDUNG BARAT"
						},
						{
							"id": 386,
							"namakotakabupaten": "KAB. BANGGAI"
						},
						{
							"id": 392,
							"namakotakabupaten": "KAB. BANGGAI KEPULAUAN"
						},
						{
							"id": 396,
							"namakotakabupaten": "KAB. BANGGAI LAUT"
						},
						{
							"id": 141,
							"namakotakabupaten": "KAB. BANGKA"
						},
						{
							"id": 145,
							"namakotakabupaten": "KAB. BANGKA BARAT"
						},
						{
							"id": 253,
							"namakotakabupaten": "KAB. BANGKALAN"
						},
						{
							"id": 143,
							"namakotakabupaten": "KAB. BANGKA SELATAN"
						},
						{
							"id": 144,
							"namakotakabupaten": "KAB. BANGKA TENGAH"
						},
						{
							"id": 279,
							"namakotakabupaten": "KAB. BANGLI"
						},
						{
							"id": 345,
							"namakotakabupaten": "KAB. BANJAR"
						},
						{
							"id": 191,
							"namakotakabupaten": "KAB. BANJARNEGARA"
						},
						{
							"id": 401,
							"namakotakabupaten": "KAB. BANTAENG"
						},
						{
							"id": 224,
							"namakotakabupaten": "KAB. BANTUL"
						},
						{
							"id": 105,
							"namakotakabupaten": "KAB. BANYUASIN"
						},
						{
							"id": 189,
							"namakotakabupaten": "KAB. BANYUMAS"
						},
						{
							"id": 237,
							"namakotakabupaten": "KAB. BANYUWANGI"
						},
						{
							"id": 346,
							"namakotakabupaten": "KAB. BARITO KUALA"
						},
						{
							"id": 332,
							"namakotakabupaten": "KAB. BARITO SELATAN"
						},
						{
							"id": 341,
							"namakotakabupaten": "KAB. BARITO TIMUR"
						},
						{
							"id": 333,
							"namakotakabupaten": "KAB. BARITO UTARA"
						},
						{
							"id": 409,
							"namakotakabupaten": "KAB. BARRU"
						},
						{
							"id": 212,
							"namakotakabupaten": "KAB. BATANG"
						},
						{
							"id": 91,
							"namakotakabupaten": "KAB. BATANGHARI"
						},
						{
							"id": 42,
							"namakotakabupaten": "KAB. BATU BARA"
						},
						{
							"id": 176,
							"namakotakabupaten": "KAB. BEKASI"
						},
						{
							"id": 142,
							"namakotakabupaten": "KAB. BELITUNG"
						},
						{
							"id": 146,
							"namakotakabupaten": "KAB. BELITUNG TIMUR"
						},
						{
							"id": 296,
							"namakotakabupaten": "KAB. BELU"
						},
						{
							"id": 17,
							"namakotakabupaten": "KAB. BENER MERIAH"
						},
						{
							"id": 78,
							"namakotakabupaten": "KAB. BENGKALIS"
						},
						{
							"id": 321,
							"namakotakabupaten": "KAB. BENGKAYANG"
						},
						{
							"id": 116,
							"namakotakabupaten": "KAB. BENGKULU SELATAN"
						},
						{
							"id": 124,
							"namakotakabupaten": "KAB. BENGKULU TENGAH"
						},
						{
							"id": 118,
							"namakotakabupaten": "KAB. BENGKULU UTARA"
						},
						{
							"id": 358,
							"namakotakabupaten": "KAB. BERAU"
						},
						{
							"id": 479,
							"namakotakabupaten": "KAB. BIAK NUMFOR"
						},
						{
							"id": 288,
							"namakotakabupaten": "KAB. BIMA"
						},
						{
							"id": 148,
							"namakotakabupaten": "KAB. BINTAN"
						},
						{
							"id": 11,
							"namakotakabupaten": "KAB. BIREUEN"
						},
						{
							"id": 232,
							"namakotakabupaten": "KAB. BLITAR"
						},
						{
							"id": 203,
							"namakotakabupaten": "KAB. BLORA"
						},
						{
							"id": 442,
							"namakotakabupaten": "KAB. BOALEMO"
						},
						{
							"id": 161,
							"namakotakabupaten": "KAB. BOGOR"
						},
						{
							"id": 249,
							"namakotakabupaten": "KAB. BOJONEGORO"
						},
						{
							"id": 371,
							"namakotakabupaten": "KAB. BOLAANG MONGONDOW"
						},
						{
							"id": 381,
							"namakotakabupaten": "KAB. BOLAANG MONGONDOW SELATAN"
						},
						{
							"id": 380,
							"namakotakabupaten": "KAB. BOLAANG MONGONDOW TIMUR"
						},
						{
							"id": 378,
							"namakotakabupaten": "KAB. BOLAANG MONGONDOW UTARA"
						},
						{
							"id": 429,
							"namakotakabupaten": "KAB. BOMBANA"
						},
						{
							"id": 238,
							"namakotakabupaten": "KAB. BONDOWOSO"
						},
						{
							"id": 406,
							"namakotakabupaten": "KAB. BONE"
						},
						{
							"id": 443,
							"namakotakabupaten": "KAB. BONE BOLANGO"
						},
						{
							"id": 489,
							"namakotakabupaten": "KAB. BOVEN DIGOEL"
						},
						{
							"id": 196,
							"namakotakabupaten": "KAB. BOYOLALI"
						},
						{
							"id": 216,
							"namakotakabupaten": "KAB. BREBES"
						},
						{
							"id": 281,
							"namakotakabupaten": "KAB. BULELENG"
						},
						{
							"id": 400,
							"namakotakabupaten": "KAB. BULUKUMBA"
						},
						{
							"id": 366,
							"namakotakabupaten": "KAB. BULUNGAN"
						},
						{
							"id": 95,
							"namakotakabupaten": "KAB. BUNGO"
						},
						{
							"id": 390,
							"namakotakabupaten": "KAB. BUOL"
						},
						{
							"id": 456,
							"namakotakabupaten": "KAB. BURU"
						},
						{
							"id": 461,
							"namakotakabupaten": "KAB. BURU SELATAN"
						},
						{
							"id": 427,
							"namakotakabupaten": "KAB. BUTON"
						},
						{
							"id": 438,
							"namakotakabupaten": "KAB. BUTON SELATAN"
						},
						{
							"id": 437,
							"namakotakabupaten": "KAB. BUTON TENGAH"
						},
						{
							"id": 433,
							"namakotakabupaten": "KAB. BUTON UTARA"
						},
						{
							"id": 167,
							"namakotakabupaten": "KAB. CIAMIS"
						},
						{
							"id": 163,
							"namakotakabupaten": "KAB. CIANJUR"
						},
						{
							"id": 188,
							"namakotakabupaten": "KAB. CILACAP"
						},
						{
							"id": 169,
							"namakotakabupaten": "KAB. CIREBON"
						},
						{
							"id": 34,
							"namakotakabupaten": "KAB. DAIRI"
						},
						{
							"id": 501,
							"namakotakabupaten": "KAB. DEIYAI"
						},
						{
							"id": 30,
							"namakotakabupaten": "KAB. DELI SERDANG"
						},
						{
							"id": 208,
							"namakotakabupaten": "KAB. DEMAK"
						},
						{
							"id": 66,
							"namakotakabupaten": "KAB. DHARMASRAYA"
						},
						{
							"id": 499,
							"namakotakabupaten": "KAB. DOGIYAI"
						},
						{
							"id": 287,
							"namakotakabupaten": "KAB. DOMPU"
						},
						{
							"id": 388,
							"namakotakabupaten": "KAB. DONGGALA"
						},
						{
							"id": 109,
							"namakotakabupaten": "KAB. EMPAT LAWANG"
						},
						{
							"id": 300,
							"namakotakabupaten": "KAB. ENDE"
						},
						{
							"id": 414,
							"namakotakabupaten": "KAB. ENREKANG"
						},
						{
							"id": 505,
							"namakotakabupaten": "KAB. FAK FAK"
						},
						{
							"id": 298,
							"namakotakabupaten": "KAB. FLORES TIMUR"
						},
						{
							"id": 165,
							"namakotakabupaten": "KAB. GARUT"
						},
						{
							"id": 13,
							"namakotakabupaten": "KAB. GAYO LUES"
						},
						{
							"id": 277,
							"namakotakabupaten": "KAB. GIANYAR"
						},
						{
							"id": 441,
							"namakotakabupaten": "KAB. GORONTALO"
						},
						{
							"id": 445,
							"namakotakabupaten": "KAB. GORONTALO UTARA"
						},
						{
							"id": 404,
							"namakotakabupaten": "KAB. GOWA"
						},
						{
							"id": 252,
							"namakotakabupaten": "KAB. GRESIK"
						},
						{
							"id": 202,
							"namakotakabupaten": "KAB. GROBOGAN"
						},
						{
							"id": 225,
							"namakotakabupaten": "KAB. GUNUNGKIDUL"
						},
						{
							"id": 338,
							"namakotakabupaten": "KAB. GUNUNG MAS"
						},
						{
							"id": 464,
							"namakotakabupaten": "KAB. HALMAHERA BARAT"
						},
						{
							"id": 467,
							"namakotakabupaten": "KAB. HALMAHERA SELATAN"
						},
						{
							"id": 465,
							"namakotakabupaten": "KAB. HALMAHERA TENGAH"
						},
						{
							"id": 469,
							"namakotakabupaten": "KAB. HALMAHERA TIMUR"
						},
						{
							"id": 466,
							"namakotakabupaten": "KAB. HALMAHERA UTARA"
						},
						{
							"id": 348,
							"namakotakabupaten": "KAB. HULU SUNGAI SELATAN"
						},
						{
							"id": 349,
							"namakotakabupaten": "KAB. HULU SUNGAI TENGAH"
						},
						{
							"id": 350,
							"namakotakabupaten": "KAB. HULU SUNGAI UTARA"
						},
						{
							"id": 39,
							"namakotakabupaten": "KAB. HUMBANG HASUNDUTAN"
						},
						{
							"id": 79,
							"namakotakabupaten": "KAB. INDRAGIRI HILIR"
						},
						{
							"id": 77,
							"namakotakabupaten": "KAB. INDRAGIRI HULU"
						},
						{
							"id": 172,
							"namakotakabupaten": "KAB. INDRAMAYU"
						},
						{
							"id": 500,
							"namakotakabupaten": "KAB. INTAN JAYA"
						},
						{
							"id": 476,
							"namakotakabupaten": "KAB. JAYAPURA"
						},
						{
							"id": 475,
							"namakotakabupaten": "KAB. JAYAWIJAYA"
						},
						{
							"id": 236,
							"namakotakabupaten": "KAB. JEMBER"
						},
						{
							"id": 274,
							"namakotakabupaten": "KAB. JEMBRANA"
						},
						{
							"id": 402,
							"namakotakabupaten": "KAB. JENEPONTO"
						},
						{
							"id": 207,
							"namakotakabupaten": "KAB. JEPARA"
						},
						{
							"id": 244,
							"namakotakabupaten": "KAB. JOMBANG"
						},
						{
							"id": 510,
							"namakotakabupaten": "KAB. KAIMANA"
						},
						{
							"id": 76,
							"namakotakabupaten": "KAB. KAMPAR"
						},
						{
							"id": 331,
							"namakotakabupaten": "KAB. KAPUAS"
						},
						{
							"id": 320,
							"namakotakabupaten": "KAB. KAPUAS HULU"
						},
						{
							"id": 200,
							"namakotakabupaten": "KAB. KARANGANYAR"
						},
						{
							"id": 280,
							"namakotakabupaten": "KAB. KARANGASEM"
						},
						{
							"id": 175,
							"namakotakabupaten": "KAB. KARAWANG"
						},
						{
							"id": 149,
							"namakotakabupaten": "KAB. KARIMUN"
						},
						{
							"id": 29,
							"namakotakabupaten": "KAB. KARO"
						},
						{
							"id": 334,
							"namakotakabupaten": "KAB. KATINGAN"
						},
						{
							"id": 119,
							"namakotakabupaten": "KAB. KAUR"
						},
						{
							"id": 325,
							"namakotakabupaten": "KAB. KAYONG UTARA"
						},
						{
							"id": 192,
							"namakotakabupaten": "KAB. KEBUMEN"
						},
						{
							"id": 233,
							"namakotakabupaten": "KAB. KEDIRI"
						},
						{
							"id": 484,
							"namakotakabupaten": "KAB. KEEROM"
						},
						{
							"id": 211,
							"namakotakabupaten": "KAB. KENDAL"
						},
						{
							"id": 123,
							"namakotakabupaten": "KAB. KEPAHIANG"
						},
						{
							"id": 379,
							"namakotakabupaten": "KAB. KEP. SIAU TAGULANDANG BIARO"
						},
						{
							"id": 152,
							"namakotakabupaten": "KAB. KEPULAUAN ANAMBAS"
						},
						{
							"id": 459,
							"namakotakabupaten": "KAB. KEPULAUAN ARU"
						},
						{
							"id": 65,
							"namakotakabupaten": "KAB. KEPULAUAN MENTAWAI"
						},
						{
							"id": 85,
							"namakotakabupaten": "KAB. KEPULAUAN MERANTI"
						},
						{
							"id": 373,
							"namakotakabupaten": "KAB. KEPULAUAN SANGIHE"
						},
						{
							"id": 399,
							"namakotakabupaten": "KAB. KEPULAUAN SELAYAR"
						},
						{
							"id": 468,
							"namakotakabupaten": "KAB. KEPULAUAN SULA"
						},
						{
							"id": 374,
							"namakotakabupaten": "KAB. KEPULAUAN TALAUD"
						},
						{
							"id": 478,
							"namakotakabupaten": "KAB. KEPULAUAN YAPEN"
						},
						{
							"id": 88,
							"namakotakabupaten": "KAB. KERINCI"
						},
						{
							"id": 318,
							"namakotakabupaten": "KAB. KETAPANG"
						},
						{
							"id": 197,
							"namakotakabupaten": "KAB. KLATEN"
						},
						{
							"id": 278,
							"namakotakabupaten": "KAB. KLUNGKUNG"
						},
						{
							"id": 424,
							"namakotakabupaten": "KAB. KOLAKA"
						},
						{
							"id": 434,
							"namakotakabupaten": "KAB. KOLAKA TIMUR"
						},
						{
							"id": 431,
							"namakotakabupaten": "KAB. KOLAKA UTARA"
						},
						{
							"id": 425,
							"namakotakabupaten": "KAB. KONAWE"
						},
						{
							"id": 435,
							"namakotakabupaten": "KAB. KONAWE KEPULAUAN"
						},
						{
							"id": 428,
							"namakotakabupaten": "KAB. KONAWE SELATAN"
						},
						{
							"id": 432,
							"namakotakabupaten": "KAB. KONAWE UTARA"
						},
						{
							"id": 344,
							"namakotakabupaten": "KAB. KOTABARU"
						},
						{
							"id": 329,
							"namakotakabupaten": "KAB. KOTAWARINGIN BARAT"
						},
						{
							"id": 330,
							"namakotakabupaten": "KAB. KOTAWARINGIN TIMUR"
						},
						{
							"id": 84,
							"namakotakabupaten": "KAB. KUANTAN SINGINGI"
						},
						{
							"id": 326,
							"namakotakabupaten": "KAB. KUBU RAYA"
						},
						{
							"id": 206,
							"namakotakabupaten": "KAB. KUDUS"
						},
						{
							"id": 223,
							"namakotakabupaten": "KAB. KULON PROGO"
						},
						{
							"id": 168,
							"namakotakabupaten": "KAB. KUNINGAN"
						},
						{
							"id": 293,
							"namakotakabupaten": "KAB. KUPANG"
						},
						{
							"id": 359,
							"namakotakabupaten": "KAB. KUTAI BARAT"
						},
						{
							"id": 357,
							"namakotakabupaten": "KAB. KUTAI KARTANEGARA"
						},
						{
							"id": 360,
							"namakotakabupaten": "KAB. KUTAI TIMUR"
						},
						{
							"id": 33,
							"namakotakabupaten": "KAB. LABUHANBATU"
						},
						{
							"id": 45,
							"namakotakabupaten": "KAB. LABUHANBATU SELATAN"
						},
						{
							"id": 46,
							"namakotakabupaten": "KAB. LABUHANBATU UTARA"
						},
						{
							"id": 102,
							"namakotakabupaten": "KAB. LAHAT"
						},
						{
							"id": 337,
							"namakotakabupaten": "KAB. LAMANDAU"
						},
						{
							"id": 251,
							"namakotakabupaten": "KAB. LAMONGAN"
						},
						{
							"id": 129,
							"namakotakabupaten": "KAB. LAMPUNG BARAT"
						},
						{
							"id": 126,
							"namakotakabupaten": "KAB. LAMPUNG SELATAN"
						},
						{
							"id": 127,
							"namakotakabupaten": "KAB. LAMPUNG TENGAH"
						},
						{
							"id": 132,
							"namakotakabupaten": "KAB. LAMPUNG TIMUR"
						},
						{
							"id": 128,
							"namakotakabupaten": "KAB. LAMPUNG UTARA"
						},
						{
							"id": 322,
							"namakotakabupaten": "KAB. LANDAK"
						},
						{
							"id": 28,
							"namakotakabupaten": "KAB. LANGKAT"
						},
						{
							"id": 496,
							"namakotakabupaten": "KAB. LANNY JAYA"
						},
						{
							"id": 267,
							"namakotakabupaten": "KAB. LEBAK"
						},
						{
							"id": 122,
							"namakotakabupaten": "KAB. LEBONG"
						},
						{
							"id": 305,
							"namakotakabupaten": "KAB. LEMBATA"
						},
						{
							"id": 63,
							"namakotakabupaten": "KAB. LIMA PULUH KOTA"
						},
						{
							"id": 151,
							"namakotakabupaten": "KAB. LINGGA"
						},
						{
							"id": 283,
							"namakotakabupaten": "KAB. LOMBOK BARAT"
						},
						{
							"id": 284,
							"namakotakabupaten": "KAB. LOMBOK TENGAH"
						},
						{
							"id": 285,
							"namakotakabupaten": "KAB. LOMBOK TIMUR"
						},
						{
							"id": 290,
							"namakotakabupaten": "KAB. LOMBOK UTARA"
						},
						{
							"id": 235,
							"namakotakabupaten": "KAB. LUMAJANG"
						},
						{
							"id": 415,
							"namakotakabupaten": "KAB. LUWU"
						},
						{
							"id": 419,
							"namakotakabupaten": "KAB. LUWU TIMUR"
						},
						{
							"id": 418,
							"namakotakabupaten": "KAB. LUWU UTARA"
						},
						{
							"id": 246,
							"namakotakabupaten": "KAB. MADIUN"
						},
						{
							"id": 195,
							"namakotakabupaten": "KAB. MAGELANG"
						},
						{
							"id": 247,
							"namakotakabupaten": "KAB. MAGETAN"
						},
						{
							"id": 362,
							"namakotakabupaten": "KAB. MAHAKAM ULU"
						},
						{
							"id": 170,
							"namakotakabupaten": "KAB. MAJALENGKA"
						},
						{
							"id": 451,
							"namakotakabupaten": "KAB. MAJENE"
						},
						{
							"id": 313,
							"namakotakabupaten": "KAB. MALAKA"
						},
						{
							"id": 234,
							"namakotakabupaten": "KAB. MALANG"
						},
						{
							"id": 367,
							"namakotakabupaten": "KAB. MALINAU"
						},
						{
							"id": 460,
							"namakotakabupaten": "KAB. MALUKU BARAT DAYA"
						},
						{
							"id": 453,
							"namakotakabupaten": "KAB. MALUKU TENGAH"
						},
						{
							"id": 454,
							"namakotakabupaten": "KAB. MALUKU TENGGARA"
						},
						{
							"id": 455,
							"namakotakabupaten": "KAB MALUKU TENGGARA BARAT"
						},
						{
							"id": 449,
							"namakotakabupaten": "KAB. MAMASA"
						},
						{
							"id": 493,
							"namakotakabupaten": "KAB. MAMBERAMO RAYA"
						},
						{
							"id": 494,
							"namakotakabupaten": "KAB. MAMBERAMO TENGAH"
						},
						{
							"id": 448,
							"namakotakabupaten": "KAB. MAMUJU"
						},
						{
							"id": 452,
							"namakotakabupaten": "KAB. MAMUJU TENGAH"
						},
						{
							"id": 447,
							"namakotakabupaten": "KAB. MAMUJU UTARA"
						},
						{
							"id": 36,
							"namakotakabupaten": "KAB. MANDAILING NATAL"
						},
						{
							"id": 302,
							"namakotakabupaten": "KAB. MANGGARAI"
						},
						{
							"id": 307,
							"namakotakabupaten": "KAB. MANGGARAI BARAT"
						},
						{
							"id": 311,
							"namakotakabupaten": "KAB. MANGGARAI TIMUR"
						},
						{
							"id": 504,
							"namakotakabupaten": "KAB. MANOKWARI"
						},
						{
							"id": 513,
							"namakotakabupaten": "KAB. MANOKWARI SELATAN"
						},
						{
							"id": 490,
							"namakotakabupaten": "KAB. MAPPI"
						},
						{
							"id": 407,
							"namakotakabupaten": "KAB. MAROS"
						},
						{
							"id": 512,
							"namakotakabupaten": "KAB. MAYBRAT"
						},
						{
							"id": 324,
							"namakotakabupaten": "KAB. MELAWI"
						},
						{
							"id": 316,
							"namakotakabupaten": "KAB. MEMPAWAH"
						},
						{
							"id": 89,
							"namakotakabupaten": "KAB. MERANGIN"
						},
						{
							"id": 474,
							"namakotakabupaten": "KAB. MERAUKE"
						},
						{
							"id": 136,
							"namakotakabupaten": "KAB. MESUJI"
						},
						{
							"id": 482,
							"namakotakabupaten": "KAB. MIMIKA"
						},
						{
							"id": 372,
							"namakotakabupaten": "KAB. MINAHASA"
						},
						{
							"id": 375,
							"namakotakabupaten": "KAB. MINAHASA SELATAN"
						},
						{
							"id": 377,
							"namakotakabupaten": "KAB. MINAHASA TENGGARA"
						},
						{
							"id": 376,
							"namakotakabupaten": "KAB. MINAHASA UTARA"
						},
						{
							"id": 243,
							"namakotakabupaten": "KAB. MOJOKERTO"
						},
						{
							"id": 391,
							"namakotakabupaten": "KAB. MOROWALI"
						},
						{
							"id": 397,
							"namakotakabupaten": "KAB. MOROWALI UTARA"
						},
						{
							"id": 101,
							"namakotakabupaten": "KAB. MUARA ENIM"
						},
						{
							"id": 92,
							"namakotakabupaten": "KAB. MUARO JAMBI"
						},
						{
							"id": 121,
							"namakotakabupaten": "KAB. MUKO MUKO"
						},
						{
							"id": 426,
							"namakotakabupaten": "KAB. MUNA"
						},
						{
							"id": 436,
							"namakotakabupaten": "KAB. MUNA BARAT"
						},
						{
							"id": 340,
							"namakotakabupaten": "KAB. MURUNG RAYA"
						},
						{
							"id": 104,
							"namakotakabupaten": "KAB. MUSI BANYUASIN"
						},
						{
							"id": 103,
							"namakotakabupaten": "KAB. MUSI RAWAS"
						},
						{
							"id": 111,
							"namakotakabupaten": "KAB. MUSI RAWAS UTARA"
						},
						{
							"id": 477,
							"namakotakabupaten": "KAB. NABIRE"
						},
						{
							"id": 15,
							"namakotakabupaten": "KAB. NAGAN RAYA"
						},
						{
							"id": 308,
							"namakotakabupaten": "KAB. NAGEKEO"
						},
						{
							"id": 150,
							"namakotakabupaten": "KAB. NATUNA"
						},
						{
							"id": 497,
							"namakotakabupaten": "KAB. NDUGA"
						},
						{
							"id": 301,
							"namakotakabupaten": "KAB. NGADA"
						},
						{
							"id": 245,
							"namakotakabupaten": "KAB. NGANJUK"
						},
						{
							"id": 248,
							"namakotakabupaten": "KAB. NGAWI"
						},
						{
							"id": 27,
							"namakotakabupaten": "KAB. NIAS"
						},
						{
							"id": 48,
							"namakotakabupaten": "KAB. NIAS BARAT"
						},
						{
							"id": 37,
							"namakotakabupaten": "KAB. NIAS SELATAN"
						},
						{
							"id": 47,
							"namakotakabupaten": "KAB. NIAS UTARA"
						},
						{
							"id": 368,
							"namakotakabupaten": "KAB. NUNUKAN"
						},
						{
							"id": 108,
							"namakotakabupaten": "KAB. OGAN ILIR"
						},
						{
							"id": 100,
							"namakotakabupaten": "KAB. OGAN KOMERING ILIR"
						},
						{
							"id": 99,
							"namakotakabupaten": "KAB. OGAN KOMERING ULU"
						},
						{
							"id": 107,
							"namakotakabupaten": "KAB. OGAN KOMERING ULU SELATAN"
						},
						{
							"id": 106,
							"namakotakabupaten": "KAB. OGAN KOMERING ULU TIMUR"
						},
						{
							"id": 228,
							"namakotakabupaten": "KAB. PACITAN"
						},
						{
							"id": 44,
							"namakotakabupaten": "KAB. PADANG LAWAS"
						},
						{
							"id": 43,
							"namakotakabupaten": "KAB. PADANG LAWAS UTARA"
						},
						{
							"id": 61,
							"namakotakabupaten": "KAB. PADANG PARIAMAN"
						},
						{
							"id": 38,
							"namakotakabupaten": "KAB. PAKPAK BHARATA"
						},
						{
							"id": 255,
							"namakotakabupaten": "KAB. PAMEKASAN"
						},
						{
							"id": 266,
							"namakotakabupaten": "KAB. PANDEGLANG"
						},
						{
							"id": 178,
							"namakotakabupaten": "KAB. PANGANDARAN"
						},
						{
							"id": 408,
							"namakotakabupaten": "KAB. PANGKAJENE KEPULAUAN"
						},
						{
							"id": 481,
							"namakotakabupaten": "KAB. PANIAI"
						},
						{
							"id": 393,
							"namakotakabupaten": "KAB. PARIGI MOUTONG"
						},
						{
							"id": 64,
							"namakotakabupaten": "KAB. PASAMAN"
						},
						{
							"id": 68,
							"namakotakabupaten": "KAB. PASAMAN BARAT"
						},
						{
							"id": 356,
							"namakotakabupaten": "KAB. PASER"
						},
						{
							"id": 241,
							"namakotakabupaten": "KAB. PASURUAN"
						},
						{
							"id": 205,
							"namakotakabupaten": "KAB. PATI"
						},
						{
							"id": 514,
							"namakotakabupaten": "KAB. PEGUNUNGAN ARFAK"
						},
						{
							"id": 485,
							"namakotakabupaten": "KAB PEGUNUNGAN BINTANG"
						},
						{
							"id": 213,
							"namakotakabupaten": "KAB. PEKALONGAN"
						},
						{
							"id": 80,
							"namakotakabupaten": "KAB. PELALAWAN"
						},
						{
							"id": 214,
							"namakotakabupaten": "KAB. PEMALANG"
						},
						{
							"id": 361,
							"namakotakabupaten": "KAB. PENAJAM PASER UTARA"
						},
						{
							"id": 110,
							"namakotakabupaten": "KAB. PENUKAL ABAB LEMATANG ILIR"
						},
						{
							"id": 134,
							"namakotakabupaten": "KAB. PESAWARAN"
						},
						{
							"id": 138,
							"namakotakabupaten": "KAB. PESISIR BARAT"
						},
						{
							"id": 57,
							"namakotakabupaten": "KAB. PESISIR SELATAN"
						},
						{
							"id": 7,
							"namakotakabupaten": "KAB. PIDIE"
						},
						{
							"id": 18,
							"namakotakabupaten": "KAB. PIDIE JAYA"
						},
						{
							"id": 413,
							"namakotakabupaten": "KAB. PINRANG"
						},
						{
							"id": 444,
							"namakotakabupaten": "KAB. POHUWATO"
						},
						{
							"id": 417,
							"namakotakabupaten": "KAB. POLEWALI MAMASA"
						},
						{
							"id": 450,
							"namakotakabupaten": "KAB. POLEWALI MANDAR"
						},
						{
							"id": 229,
							"namakotakabupaten": "KAB. PONOROGO"
						},
						{
							"id": 387,
							"namakotakabupaten": "KAB. POSO"
						},
						{
							"id": 135,
							"namakotakabupaten": "KAB. PRINGSEWU"
						},
						{
							"id": 240,
							"namakotakabupaten": "KAB. PROBOLINGGO"
						},
						{
							"id": 339,
							"namakotakabupaten": "KAB. PULANG PISAU"
						},
						{
							"id": 470,
							"namakotakabupaten": "KAB. PULAU MOROTAI"
						},
						{
							"id": 471,
							"namakotakabupaten": "KAB. PULAU TALIABU"
						},
						{
							"id": 498,
							"namakotakabupaten": "KAB. PUNCAK"
						},
						{
							"id": 480,
							"namakotakabupaten": "KAB. PUNCAK JAYA"
						},
						{
							"id": 190,
							"namakotakabupaten": "KAB. PURBALINGGA"
						},
						{
							"id": 174,
							"namakotakabupaten": "KAB. PURWAKARTA"
						},
						{
							"id": 193,
							"namakotakabupaten": "KAB. PURWOREJO"
						},
						{
							"id": 507,
							"namakotakabupaten": "KAB. RAJA AMPAT"
						},
						{
							"id": 117,
							"namakotakabupaten": "KAB. REJANG LEBONG"
						},
						{
							"id": 204,
							"namakotakabupaten": "KAB. REMBANG"
						},
						{
							"id": 82,
							"namakotakabupaten": "KAB. ROKAN HILIR"
						},
						{
							"id": 81,
							"namakotakabupaten": "KAB. ROKAN HULU"
						},
						{
							"id": 306,
							"namakotakabupaten": "KAB. ROTE NDAO"
						},
						{
							"id": 312,
							"namakotakabupaten": "KAB. SABU RAIJUA"
						},
						{
							"id": 315,
							"namakotakabupaten": "KAB. SAMBAS"
						},
						{
							"id": 40,
							"namakotakabupaten": "KAB. SAMOSIR"
						},
						{
							"id": 254,
							"namakotakabupaten": "KAB. SAMPANG"
						},
						{
							"id": 317,
							"namakotakabupaten": "KAB. SANGGAU"
						},
						{
							"id": 483,
							"namakotakabupaten": "KAB. SARMI"
						},
						{
							"id": 90,
							"namakotakabupaten": "KAB. SAROLANGUN"
						},
						{
							"id": 323,
							"namakotakabupaten": "KAB. SEKADAU"
						},
						{
							"id": 120,
							"namakotakabupaten": "KAB. SELUMA"
						},
						{
							"id": 209,
							"namakotakabupaten": "KAB. SEMARANG"
						},
						{
							"id": 458,
							"namakotakabupaten": "KAB. SERAM BAGIAN BARAT"
						},
						{
							"id": 457,
							"namakotakabupaten": "KAB. SERAM BAGIAN TIMUR"
						},
						{
							"id": 269,
							"namakotakabupaten": "KAB. SERANG"
						},
						{
							"id": 41,
							"namakotakabupaten": "KAB. SERDANG BEDAGAI"
						},
						{
							"id": 335,
							"namakotakabupaten": "KAB. SERUYAN"
						},
						{
							"id": 83,
							"namakotakabupaten": "KAB. SIAK"
						},
						{
							"id": 412,
							"namakotakabupaten": "KAB. SIDENRENG RAPPANG"
						},
						{
							"id": 242,
							"namakotakabupaten": "KAB. SIDOARJO"
						},
						{
							"id": 395,
							"namakotakabupaten": "KAB. SIGI"
						},
						{
							"id": 59,
							"namakotakabupaten": "KAB. SIJUNJUNG"
						},
						{
							"id": 299,
							"namakotakabupaten": "KAB. SIKKA"
						},
						{
							"id": 31,
							"namakotakabupaten": "KAB. SIMALUNGUN"
						},
						{
							"id": 9,
							"namakotakabupaten": "KAB. SIMEULUE"
						},
						{
							"id": 405,
							"namakotakabupaten": "KAB. SINJAI"
						},
						{
							"id": 319,
							"namakotakabupaten": "KAB. SINTANG"
						},
						{
							"id": 239,
							"namakotakabupaten": "KAB. SITUBONDO"
						},
						{
							"id": 226,
							"namakotakabupaten": "KAB. SLEMAN"
						},
						{
							"id": 58,
							"namakotakabupaten": "KAB. SOLOK"
						},
						{
							"id": 67,
							"namakotakabupaten": "KAB. SOLOK SELATAN"
						},
						{
							"id": 410,
							"namakotakabupaten": "KAB. SOPPENG"
						},
						{
							"id": 503,
							"namakotakabupaten": "KAB. SORONG"
						},
						{
							"id": 506,
							"namakotakabupaten": "KAB. SORONG SELATAN"
						},
						{
							"id": 201,
							"namakotakabupaten": "KAB. SRAGEN"
						},
						{
							"id": 173,
							"namakotakabupaten": "KAB. SUBANG"
						},
						{
							"id": 162,
							"namakotakabupaten": "KAB. SUKABUMI"
						},
						{
							"id": 336,
							"namakotakabupaten": "KAB. SUKAMARA"
						},
						{
							"id": 198,
							"namakotakabupaten": "KAB. SUKOHARJO"
						},
						{
							"id": 304,
							"namakotakabupaten": "KAB. SUMBA BARAT"
						},
						{
							"id": 310,
							"namakotakabupaten": "KAB. SUMBA BARAT DAYA"
						},
						{
							"id": 309,
							"namakotakabupaten": "KAB. SUMBA TENGAH"
						},
						{
							"id": 303,
							"namakotakabupaten": "KAB. SUMBA TIMUR"
						},
						{
							"id": 286,
							"namakotakabupaten": "KAB. SUMBAWA"
						},
						{
							"id": 289,
							"namakotakabupaten": "KAB. SUMBAWA BARAT"
						},
						{
							"id": 171,
							"namakotakabupaten": "KAB. SUMEDANG"
						},
						{
							"id": 256,
							"namakotakabupaten": "KAB. SUMENEP"
						},
						{
							"id": 492,
							"namakotakabupaten": "KAB. SUPIORI"
						},
						{
							"id": 351,
							"namakotakabupaten": "KAB. TABALONG"
						},
						{
							"id": 275,
							"namakotakabupaten": "KAB. TABANAN"
						},
						{
							"id": 403,
							"namakotakabupaten": "KAB. TAKALAR"
						},
						{
							"id": 511,
							"namakotakabupaten": "KAB. TAMBRAUW"
						},
						{
							"id": 352,
							"namakotakabupaten": "KAB. TANAH BUMBU"
						},
						{
							"id": 60,
							"namakotakabupaten": "KAB. TANAH DATAR"
						},
						{
							"id": 343,
							"namakotakabupaten": "KAB. TANAH LAUT"
						},
						{
							"id": 369,
							"namakotakabupaten": "KAB. TANA TIDUNG"
						},
						{
							"id": 416,
							"namakotakabupaten": "KAB. TANA TORAJA"
						},
						{
							"id": 268,
							"namakotakabupaten": "KAB. TANGERANG"
						},
						{
							"id": 131,
							"namakotakabupaten": "KAB. TANGGAMUS"
						},
						{
							"id": 93,
							"namakotakabupaten": "KAB. TANJUNG JABUNG BARAT"
						},
						{
							"id": 94,
							"namakotakabupaten": "KAB. TANJUNG JABUNG TIMUR"
						},
						{
							"id": 26,
							"namakotakabupaten": "KAB. TAPANULI SELATAN"
						},
						{
							"id": 24,
							"namakotakabupaten": "KAB. TAPANULI TENGAH"
						},
						{
							"id": 25,
							"namakotakabupaten": "KAB. TAPANULI UTARA"
						},
						{
							"id": 347,
							"namakotakabupaten": "KAB. TAPIN"
						},
						{
							"id": 166,
							"namakotakabupaten": "KAB. TASIKMALAYA"
						},
						{
							"id": 96,
							"namakotakabupaten": "KAB. TEBO"
						},
						{
							"id": 215,
							"namakotakabupaten": "KAB. TEGAL"
						},
						{
							"id": 508,
							"namakotakabupaten": "KAB. TELUK BINTUNI"
						},
						{
							"id": 509,
							"namakotakabupaten": "KAB. TELUK WONDAMA"
						},
						{
							"id": 210,
							"namakotakabupaten": "KAB. TEMANGGUNG"
						},
						{
							"id": 294,
							"namakotakabupaten": "KAB TIMOR TENGAH SELATAN"
						},
						{
							"id": 295,
							"namakotakabupaten": "KAB. TIMOR TENGAH UTARA"
						},
						{
							"id": 35,
							"namakotakabupaten": "KAB. TOBA SAMOSIR"
						},
						{
							"id": 394,
							"namakotakabupaten": "KAB. TOJO UNA UNA"
						},
						{
							"id": 487,
							"namakotakabupaten": "KAB. TOLIKARA"
						},
						{
							"id": 389,
							"namakotakabupaten": "KAB. TOLI TOLI"
						},
						{
							"id": 420,
							"namakotakabupaten": "KAB. TORAJA UTARA"
						},
						{
							"id": 230,
							"namakotakabupaten": "KAB. TRENGGALEK"
						},
						{
							"id": 250,
							"namakotakabupaten": "KAB. TUBAN"
						},
						{
							"id": 130,
							"namakotakabupaten": "KAB. TULANG BAWANG"
						},
						{
							"id": 137,
							"namakotakabupaten": "KAB. TULANG BAWANG BARAT"
						},
						{
							"id": 231,
							"namakotakabupaten": "KAB. TULUNGAGUNG"
						},
						{
							"id": 411,
							"namakotakabupaten": "KAB. WAJO"
						},
						{
							"id": 430,
							"namakotakabupaten": "KAB. WAKATOBI"
						},
						{
							"id": 488,
							"namakotakabupaten": "KAB. WAROPEN"
						},
						{
							"id": 133,
							"namakotakabupaten": "KAB. WAY KANAN"
						},
						{
							"id": 199,
							"namakotakabupaten": "KAB. WONOGIRI"
						},
						{
							"id": 194,
							"namakotakabupaten": "KAB. WONOSOBO"
						},
						{
							"id": 486,
							"namakotakabupaten": "KAB. YAHUKIMO"
						},
						{
							"id": 495,
							"namakotakabupaten": "KAB. YALIMO"
						},
						{
							"id": 158,
							"namakotakabupaten": "KOTA ADM. JAKARTA BARAT"
						},
						{
							"id": 156,
							"namakotakabupaten": "KOTA ADM. JAKARTA PUSAT"
						},
						{
							"id": 159,
							"namakotakabupaten": "KOTA ADM. JAKARTA SELATAN"
						},
						{
							"id": 160,
							"namakotakabupaten": "KOTA ADM. JAKARTA TIMUR"
						},
						{
							"id": 157,
							"namakotakabupaten": "KOTA ADM. JAKARTA UTARA"
						},
						{
							"id": 462,
							"namakotakabupaten": "KOTA AMBON"
						},
						{
							"id": 363,
							"namakotakabupaten": "KOTA BALIKPAPAN"
						},
						{
							"id": 19,
							"namakotakabupaten": "KOTA BANDA ACEH"
						},
						{
							"id": 139,
							"namakotakabupaten": "KOTA BANDAR LAMPUNG"
						},
						{
							"id": 181,
							"namakotakabupaten": "KOTA BANDUNG"
						},
						{
							"id": 187,
							"namakotakabupaten": "KOTA BANJAR"
						},
						{
							"id": 355,
							"namakotakabupaten": "KOTA BANJARBARU"
						},
						{
							"id": 354,
							"namakotakabupaten": "KOTA BANJARMASIN"
						},
						{
							"id": 153,
							"namakotakabupaten": "KOTA BATAM"
						},
						{
							"id": 265,
							"namakotakabupaten": "KOTA BATU"
						},
						{
							"id": 440,
							"namakotakabupaten": "KOTA BAU BAU"
						},
						{
							"id": 183,
							"namakotakabupaten": "KOTA BEKASI"
						},
						{
							"id": 125,
							"namakotakabupaten": "KOTA BENGKULU"
						},
						{
							"id": 292,
							"namakotakabupaten": "KOTA BIMA"
						},
						{
							"id": 53,
							"namakotakabupaten": "KOTA BINJAI"
						},
						{
							"id": 383,
							"namakotakabupaten": "KOTA BITUNG"
						},
						{
							"id": 258,
							"namakotakabupaten": "KOTA BLITAR"
						},
						{
							"id": 179,
							"namakotakabupaten": "KOTA BOGOR"
						},
						{
							"id": 365,
							"namakotakabupaten": "KOTA BONTANG"
						},
						{
							"id": 73,
							"namakotakabupaten": "KOTA BUKITTINGGI"
						},
						{
							"id": 271,
							"namakotakabupaten": "KOTA CILEGON"
						},
						{
							"id": 185,
							"namakotakabupaten": "KOTA CIMAHI"
						},
						{
							"id": 182,
							"namakotakabupaten": "KOTA CIREBON"
						},
						{
							"id": 282,
							"namakotakabupaten": "KOTA DENPASAR"
						},
						{
							"id": 184,
							"namakotakabupaten": "KOTA DEPOK"
						},
						{
							"id": 87,
							"namakotakabupaten": "KOTA DUMAI"
						},
						{
							"id": 446,
							"namakotakabupaten": "KOTA GORONTALO"
						},
						{
							"id": 56,
							"namakotakabupaten": "KOTA GUNUNGSITOLI"
						},
						{
							"id": 97,
							"namakotakabupaten": "KOTA JAMBI"
						},
						{
							"id": 502,
							"namakotakabupaten": "KOTA JAYAPURA"
						},
						{
							"id": 257,
							"namakotakabupaten": "KOTA KEDIRI"
						},
						{
							"id": 439,
							"namakotakabupaten": "KOTA KENDARI"
						},
						{
							"id": 385,
							"namakotakabupaten": "KOTA KOTAMOBAGU"
						},
						{
							"id": 314,
							"namakotakabupaten": "KOTA KUPANG"
						},
						{
							"id": 22,
							"namakotakabupaten": "KOTA LANGSA"
						},
						{
							"id": 21,
							"namakotakabupaten": "KOTA LHOKSEUMAWE"
						},
						{
							"id": 114,
							"namakotakabupaten": "KOTA LUBUK LINGGAU"
						},
						{
							"id": 263,
							"namakotakabupaten": "KOTA MADIUN"
						},
						{
							"id": 217,
							"namakotakabupaten": "KOTA MAGELANG"
						},
						{
							"id": 421,
							"namakotakabupaten": "KOTA MAKASSAR"
						},
						{
							"id": 259,
							"namakotakabupaten": "KOTA MALANG"
						},
						{
							"id": 382,
							"namakotakabupaten": "KOTA MANADO"
						},
						{
							"id": 291,
							"namakotakabupaten": "KOTA MATARAM"
						},
						{
							"id": 49,
							"namakotakabupaten": "KOTA MEDAN"
						},
						{
							"id": 140,
							"namakotakabupaten": "KOTA METRO"
						},
						{
							"id": 262,
							"namakotakabupaten": "KOTA MOJOKERTO"
						},
						{
							"id": 69,
							"namakotakabupaten": "KOTA PADANG"
						},
						{
							"id": 72,
							"namakotakabupaten": "KOTA PADANG PANJANG"
						},
						{
							"id": 55,
							"namakotakabupaten": "KOTA PADANG SIDEMPUAN"
						},
						{
							"id": 113,
							"namakotakabupaten": "KOTA PAGAR ALAM"
						},
						{
							"id": 342,
							"namakotakabupaten": "KOTA PALANGKARAYA"
						},
						{
							"id": 112,
							"namakotakabupaten": "KOTA PALEMBANG"
						},
						{
							"id": 423,
							"namakotakabupaten": "KOTA PALOPO"
						},
						{
							"id": 398,
							"namakotakabupaten": "KOTA PALU"
						},
						{
							"id": 147,
							"namakotakabupaten": "KOTA PANGKAL PINANG"
						},
						{
							"id": 422,
							"namakotakabupaten": "KOTA PARE PARE"
						},
						{
							"id": 75,
							"namakotakabupaten": "KOTA PARIAMAN"
						},
						{
							"id": 261,
							"namakotakabupaten": "KOTA PASURUAN"
						},
						{
							"id": 74,
							"namakotakabupaten": "KOTA PAYAKUMBUH"
						},
						{
							"id": 221,
							"namakotakabupaten": "KOTA PEKALONGAN"
						},
						{
							"id": 86,
							"namakotakabupaten": "KOTA PEKANBARU"
						},
						{
							"id": 50,
							"namakotakabupaten": "KOTA PEMATANG SIANTAR"
						},
						{
							"id": 327,
							"namakotakabupaten": "KOTA PONTIANAK"
						},
						{
							"id": 115,
							"namakotakabupaten": "KOTA PRABUMULIH"
						},
						{
							"id": 260,
							"namakotakabupaten": "KOTA PROBOLINGGO"
						},
						{
							"id": 20,
							"namakotakabupaten": "KOTA SABANG"
						},
						{
							"id": 219,
							"namakotakabupaten": "KOTA SALATIGA"
						},
						{
							"id": 364,
							"namakotakabupaten": "KOTA SAMARINDA"
						},
						{
							"id": 71,
							"namakotakabupaten": "KOTA SAWAHLUNTO"
						},
						{
							"id": 220,
							"namakotakabupaten": "KOTA SEMARANG"
						},
						{
							"id": 272,
							"namakotakabupaten": "KOTA SERANG"
						},
						{
							"id": 51,
							"namakotakabupaten": "KOTA SIBOLGA"
						},
						{
							"id": 328,
							"namakotakabupaten": "KOTA SINGKAWANG"
						},
						{
							"id": 70,
							"namakotakabupaten": "KOTA SOLOK"
						},
						{
							"id": 515,
							"namakotakabupaten": "KOTA SORONG"
						},
						{
							"id": 23,
							"namakotakabupaten": "KOTA SUBULUSSALAM"
						},
						{
							"id": 180,
							"namakotakabupaten": "KOTA SUKABUMI"
						},
						{
							"id": 98,
							"namakotakabupaten": "KOTA SUNGAI PENUH"
						},
						{
							"id": 264,
							"namakotakabupaten": "KOTA SURABAYA"
						},
						{
							"id": 218,
							"namakotakabupaten": "KOTA SURAKARTA"
						},
						{
							"id": 270,
							"namakotakabupaten": "KOTA TANGERANG"
						},
						{
							"id": 273,
							"namakotakabupaten": "KOTA TANGERANG SELATAN"
						},
						{
							"id": 52,
							"namakotakabupaten": "KOTA TANJUNG BALAI"
						},
						{
							"id": 154,
							"namakotakabupaten": "KOTA TANJUNG PINANG"
						},
						{
							"id": 370,
							"namakotakabupaten": "KOTA TARAKAN"
						},
						{
							"id": 186,
							"namakotakabupaten": "KOTA TASIKMALAYA"
						},
						{
							"id": 54,
							"namakotakabupaten": "KOTA TEBING TINGGI"
						},
						{
							"id": 222,
							"namakotakabupaten": "KOTA TEGAL"
						},
						{
							"id": 472,
							"namakotakabupaten": "KOTA TERNATE"
						},
						{
							"id": 473,
							"namakotakabupaten": "KOTA TIDORE KEPULAUAN"
						},
						{
							"id": 384,
							"namakotakabupaten": "KOTA TOMOHON"
						},
						{
							"id": 463,
							"namakotakabupaten": "KOTA TUAL"
						},
						{
							"id": 227,
							"namakotakabupaten": "KOTA YOGYAKARTA"
						}
					],
					"propinsi": [
						{
							"id": 1,
							"namapropinsi": "ACEH"
						},
						{
							"id": 17,
							"namapropinsi": "BALI"
						},
						{
							"id": 16,
							"namapropinsi": "BANTEN"
						},
						{
							"id": 7,
							"namapropinsi": "BENGKULU"
						},
						{
							"id": 14,
							"namapropinsi": "DI. YOGYAKARTA"
						},
						{
							"id": 11,
							"namapropinsi": "DKI. JAKARTA"
						},
						{
							"id": 29,
							"namapropinsi": "GORONTALO"
						},
						{
							"id": 5,
							"namapropinsi": "JAMBI"
						},
						{
							"id": 12,
							"namapropinsi": "JAWA BARAT"
						},
						{
							"id": 13,
							"namapropinsi": "JAWA TENGAH"
						},
						{
							"id": 15,
							"namapropinsi": "JAWA TIMUR"
						},
						{
							"id": 20,
							"namapropinsi": "KALIMANTAN BARAT"
						},
						{
							"id": 22,
							"namapropinsi": "KALIMANTAN SELATAN"
						},
						{
							"id": 21,
							"namapropinsi": "KALIMANTAN TENGAH"
						},
						{
							"id": 23,
							"namapropinsi": "KALIMANTAN TIMUR"
						},
						{
							"id": 24,
							"namapropinsi": "KALIMANTAN UTARA"
						},
						{
							"id": 9,
							"namapropinsi": "KEP. BANGKA BELITUNG"
						},
						{
							"id": 10,
							"namapropinsi": "KEP. RIAU"
						},
						{
							"id": 8,
							"namapropinsi": "LAMPUNG"
						},
						{
							"id": 31,
							"namapropinsi": "MALUKU"
						},
						{
							"id": 32,
							"namapropinsi": "MALUKU UTARA"
						},
						{
							"id": 18,
							"namapropinsi": "NUSA TENGGARA BARAT"
						},
						{
							"id": 19,
							"namapropinsi": "NUSA TENGGARA TIMUR"
						},
						{
							"id": 33,
							"namapropinsi": "PAPUA"
						},
						{
							"id": 34,
							"namapropinsi": "PAPUA BARAT"
						},
						{
							"id": 4,
							"namapropinsi": "RIAU"
						},
						{
							"id": 30,
							"namapropinsi": "SULAWESI BARAT"
						},
						{
							"id": 27,
							"namapropinsi": "SULAWESI SELATAN"
						},
						{
							"id": 26,
							"namapropinsi": "SULAWESI TENGAH"
						},
						{
							"id": 28,
							"namapropinsi": "SULAWESI TENGGARA"
						},
						{
							"id": 25,
							"namapropinsi": "SULAWESI UTARA"
						},
						{
							"id": 3,
							"namapropinsi": "SUMATERA BARAT"
						},
						{
							"id": 6,
							"namapropinsi": "SUMATERA SELATAN"
						},
						{
							"id": 2,
							"namapropinsi": "SUMATERA UTARA"
						}
					],
					"kecamatan": [
						{
							"id": 1410,
							"namakecamatan": "ABAB"
						},
						{
							"id": 1567,
							"namakecamatan": "ABAB"
						},
						{
							"id": 4370,
							"namakecamatan": "ABANG"
						},
						{
							"id": 6531,
							"namakecamatan": "ABELI"
						},
						{
							"id": 7476,
							"namakecamatan": "ABENAHO"
						},
						{
							"id": 7007,
							"namakecamatan": "ABENAHO"
						},
						{
							"id": 7292,
							"namakecamatan": "ABENAHO"
						},
						{
							"id": 7600,
							"namakecamatan": "ABEPURA"
						},
						{
							"id": 4347,
							"namakecamatan": "ABIANSEMAL"
						},
						{
							"id": 7261,
							"namakecamatan": "ABOY"
						},
						{
							"id": 6299,
							"namakecamatan": "ABUKI"
						},
						{
							"id": 7613,
							"namakecamatan": "ABUN"
						},
						{
							"id": 7804,
							"namakecamatan": "ABUN"
						},
						{
							"id": 1811,
							"namakecamatan": "ABUNG BARAT"
						},
						{
							"id": 1827,
							"namakecamatan": "ABUNG KUNANG"
						},
						{
							"id": 1824,
							"namakecamatan": "ABUNG PEKURUN"
						},
						{
							"id": 1812,
							"namakecamatan": "ABUNG SELATAN"
						},
						{
							"id": 1818,
							"namakecamatan": "ABUNG SEMULI"
						},
						{
							"id": 1819,
							"namakecamatan": "ABUNG SURAKARTA"
						},
						{
							"id": 1816,
							"namakecamatan": "ABUNG TENGAH"
						},
						{
							"id": 1810,
							"namakecamatan": "ABUNG TIMUR"
						},
						{
							"id": 1817,
							"namakecamatan": "ABUNG TINGGI"
						},
						{
							"id": 332,
							"namakecamatan": "ADIAN KOTING"
						},
						{
							"id": 1968,
							"namakecamatan": "ADILUWIH"
						},
						{
							"id": 1898,
							"namakecamatan": "ADILUWIH"
						},
						{
							"id": 2949,
							"namakecamatan": "ADIMULYO"
						},
						{
							"id": 2848,
							"namakecamatan": "ADIPALA"
						},
						{
							"id": 3359,
							"namakecamatan": "ADIWERNA"
						},
						{
							"id": 4656,
							"namakecamatan": "ADONARA"
						},
						{
							"id": 4647,
							"namakecamatan": "ADONARA BARAT"
						},
						{
							"id": 4657,
							"namakecamatan": "ADONARA TENGAH"
						},
						{
							"id": 4649,
							"namakecamatan": "ADONARA TIMUR"
						},
						{
							"id": 366,
							"namakecamatan": "AEK BILAH"
						},
						{
							"id": 532,
							"namakecamatan": "AEK KUASAN"
						},
						{
							"id": 742,
							"namakecamatan": "AEK KUO"
						},
						{
							"id": 568,
							"namakecamatan": "AEK KUO"
						},
						{
							"id": 546,
							"namakecamatan": "AEK LEDONG"
						},
						{
							"id": 732,
							"namakecamatan": "AEK NABARA BARUMUN"
						},
						{
							"id": 550,
							"namakecamatan": "AEK NATAS"
						},
						{
							"id": 745,
							"namakecamatan": "AEK NATAS"
						},
						{
							"id": 535,
							"namakecamatan": "AEK SONGSONGAN"
						},
						{
							"id": 6491,
							"namakecamatan": "AERE"
						},
						{
							"id": 5707,
							"namakecamatan": "AERTEMBAGA"
						},
						{
							"id": 4807,
							"namakecamatan": "AESESA"
						},
						{
							"id": 4710,
							"namakecamatan": "AESESA"
						},
						{
							"id": 4813,
							"namakecamatan": "AESESA SELATAN"
						},
						{
							"id": 4717,
							"namakecamatan": "AESESA SELATAN"
						},
						{
							"id": 755,
							"namakecamatan": "AFULU"
						},
						{
							"id": 389,
							"namakecamatan": "AFULU"
						},
						{
							"id": 7556,
							"namakecamatan": "AGANDUGUME"
						},
						{
							"id": 7155,
							"namakecamatan": "AGANDUGUME"
						},
						{
							"id": 7437,
							"namakecamatan": "AGATS"
						},
						{
							"id": 7211,
							"namakecamatan": "AGIMUGA"
						},
						{
							"id": 7186,
							"namakecamatan": "AGISIGA"
						},
						{
							"id": 7589,
							"namakecamatan": "AGISIGA"
						},
						{
							"id": 2303,
							"namakecamatan": "AGRABINTA"
						},
						{
							"id": 7828,
							"namakecamatan": "AIFAT"
						},
						{
							"id": 7631,
							"namakecamatan": "AIFAT"
						},
						{
							"id": 7708,
							"namakecamatan": "AIFAT"
						},
						{
							"id": 7831,
							"namakecamatan": "AIFAT SELATAN"
						},
						{
							"id": 7633,
							"namakecamatan": "AIFAT SELATAN"
						},
						{
							"id": 7722,
							"namakecamatan": "AIFAT SELATAN"
						},
						{
							"id": 7711,
							"namakecamatan": "AIFAT TIMUR"
						},
						{
							"id": 7830,
							"namakecamatan": "AIFAT TIMUR"
						},
						{
							"id": 7630,
							"namakecamatan": "AIFAT TIMUR"
						},
						{
							"id": 7840,
							"namakecamatan": "AIFAT TIMUR JAUH"
						},
						{
							"id": 7841,
							"namakecamatan": "AIFAT TIMUR SELATAN"
						},
						{
							"id": 7839,
							"namakecamatan": "AIFAT TIMUR TENGAH"
						},
						{
							"id": 7632,
							"namakecamatan": "AIFAT UTARA"
						},
						{
							"id": 7829,
							"namakecamatan": "AIFAT UTARA"
						},
						{
							"id": 7721,
							"namakecamatan": "AIFAT UTARA"
						},
						{
							"id": 4422,
							"namakecamatan": "AIKMEL"
						},
						{
							"id": 7139,
							"namakecamatan": "AIMANDO PADAIDO"
						},
						{
							"id": 7609,
							"namakecamatan": "AIMAS"
						},
						{
							"id": 4701,
							"namakecamatan": "AIMERE"
						},
						{
							"id": 527,
							"namakecamatan": "AIR BATU"
						},
						{
							"id": 5014,
							"namakecamatan": "AIR BESAR"
						},
						{
							"id": 1660,
							"namakecamatan": "AIR BESI"
						},
						{
							"id": 6764,
							"namakecamatan": "AIR BUAYA"
						},
						{
							"id": 1713,
							"namakecamatan": "AIR DIKIT"
						},
						{
							"id": 7369,
							"namakecamatan": "AIRGARAM"
						},
						{
							"id": 2037,
							"namakecamatan": "AIR GEGAS"
						},
						{
							"id": 1194,
							"namakecamatan": "AIR HANGAT"
						},
						{
							"id": 1210,
							"namakecamatan": "AIR HANGAT BARAT"
						},
						{
							"id": 1200,
							"namakecamatan": "AIR HANGAT TIMUR"
						},
						{
							"id": 1847,
							"namakecamatan": "AIR HITAM"
						},
						{
							"id": 1241,
							"namakecamatan": "AIR HITAM"
						},
						{
							"id": 523,
							"namakecamatan": "AIR JOMAN"
						},
						{
							"id": 1498,
							"namakecamatan": "AIR KUMBANG"
						},
						{
							"id": 5647,
							"namakecamatan": "AIRMADIDI"
						},
						{
							"id": 1712,
							"namakecamatan": "AIR MAJUNTO"
						},
						{
							"id": 1910,
							"namakecamatan": "AIR NANINGAN"
						},
						{
							"id": 1661,
							"namakecamatan": "AIR NAPAL"
						},
						{
							"id": 1618,
							"namakecamatan": "AIR NIPIS"
						},
						{
							"id": 1665,
							"namakecamatan": "AIR PADANG"
						},
						{
							"id": 1691,
							"namakecamatan": "AIR PERIUKAN"
						},
						{
							"id": 839,
							"namakecamatan": "AIRPURA"
						},
						{
							"id": 708,
							"namakecamatan": "AIR PUTIH"
						},
						{
							"id": 516,
							"namakecamatan": "AIR PUTIH"
						},
						{
							"id": 1706,
							"namakecamatan": "AIR RAMI"
						},
						{
							"id": 1493,
							"namakecamatan": "AIR SALEK"
						},
						{
							"id": 1380,
							"namakecamatan": "AIR SUGIHAN"
						},
						{
							"id": 7078,
							"namakecamatan": "AIRU"
						},
						{
							"id": 4942,
							"namakecamatan": "AIR UPAS"
						},
						{
							"id": 7833,
							"namakecamatan": "AITINYO"
						},
						{
							"id": 7635,
							"namakecamatan": "AITINYO"
						},
						{
							"id": 7705,
							"namakecamatan": "AITINYO"
						},
						{
							"id": 7634,
							"namakecamatan": "AITINYO BARAT"
						},
						{
							"id": 7832,
							"namakecamatan": "AITINYO BARAT"
						},
						{
							"id": 7850,
							"namakecamatan": "AITINYO RAYA"
						},
						{
							"id": 7849,
							"namakecamatan": "AITINYO TENGAH"
						},
						{
							"id": 7720,
							"namakecamatan": "AITINYO UTARA"
						},
						{
							"id": 7636,
							"namakecamatan": "AITINYO UTARA"
						},
						{
							"id": 7834,
							"namakecamatan": "AITINYO UTARA"
						},
						{
							"id": 6029,
							"namakecamatan": "AJANGALE"
						},
						{
							"id": 2883,
							"namakecamatan": "AJIBARANG"
						},
						{
							"id": 591,
							"namakecamatan": "AJIBATA"
						},
						{
							"id": 3682,
							"namakecamatan": "AJUNG"
						},
						{
							"id": 932,
							"namakecamatan": "AKABILURU"
						},
						{
							"id": 7440,
							"namakecamatan": "AKAT"
						},
						{
							"id": 4857,
							"namakecamatan": "ALAK"
						},
						{
							"id": 5262,
							"namakecamatan": "ALALAK"
						},
						{
							"id": 7227,
							"namakecamatan": "ALAMA"
						},
						{
							"id": 7532,
							"namakecamatan": "ALAMA"
						},
						{
							"id": 1325,
							"namakecamatan": "ALAM BARAJO"
						},
						{
							"id": 1590,
							"namakecamatan": "ALANG-ALANG LEBAR"
						},
						{
							"id": 180,
							"namakecamatan": "ALAPAN"
						},
						{
							"id": 4438,
							"namakecamatan": "ALAS"
						},
						{
							"id": 753,
							"namakecamatan": "ALASA"
						},
						{
							"id": 379,
							"namakecamatan": "ALASA"
						},
						{
							"id": 401,
							"namakecamatan": "ALASA TALUMUZOI"
						},
						{
							"id": 752,
							"namakecamatan": "ALASA TALUMUZOI"
						},
						{
							"id": 4450,
							"namakecamatan": "ALAS BARAT"
						},
						{
							"id": 7264,
							"namakecamatan": "ALEMSOM"
						},
						{
							"id": 2945,
							"namakecamatan": "ALIAN"
						},
						{
							"id": 6122,
							"namakecamatan": "ALLA"
						},
						{
							"id": 6685,
							"namakecamatan": "ALLU"
						},
						{
							"id": 4663,
							"namakecamatan": "ALOK"
						},
						{
							"id": 4672,
							"namakecamatan": "ALOK BARAT"
						},
						{
							"id": 4673,
							"namakecamatan": "ALOK TIMUR"
						},
						{
							"id": 4625,
							"namakecamatan": "ALOR BARAT DAYA"
						},
						{
							"id": 4624,
							"namakecamatan": "ALOR BARAT LAUT"
						},
						{
							"id": 4626,
							"namakecamatan": "ALOR SELATAN"
						},
						{
							"id": 4629,
							"namakecamatan": "ALOR TENGAH UTARA"
						},
						{
							"id": 4627,
							"namakecamatan": "ALOR TIMUR"
						},
						{
							"id": 4630,
							"namakecamatan": "ALOR TIMUR LAUT"
						},
						{
							"id": 5239,
							"namakecamatan": "ALUH ALUH"
						},
						{
							"id": 4537,
							"namakecamatan": "AMABI OEFETO"
						},
						{
							"id": 4532,
							"namakecamatan": "AMABI OEFETO TIMUR"
						},
						{
							"id": 6700,
							"namakecamatan": "AMAHAI"
						},
						{
							"id": 6797,
							"namakecamatan": "AMALATU"
						},
						{
							"id": 6035,
							"namakecamatan": "AMALI"
						},
						{
							"id": 4550,
							"namakecamatan": "AMANATUN SELATAN"
						},
						{
							"id": 4551,
							"namakecamatan": "AMANATUN UTARA"
						},
						{
							"id": 637,
							"namakecamatan": "AMANDRAYA"
						},
						{
							"id": 4549,
							"namakecamatan": "AMANUBAN BARAT"
						},
						{
							"id": 4548,
							"namakecamatan": "AMANUBAN SELATAN"
						},
						{
							"id": 4547,
							"namakecamatan": "AMANUBAN TENGAH"
						},
						{
							"id": 4546,
							"namakecamatan": "AMANUBAN TIMUR"
						},
						{
							"id": 7226,
							"namakecamatan": "AMAR"
						},
						{
							"id": 4521,
							"namakecamatan": "AMARASI"
						},
						{
							"id": 4529,
							"namakecamatan": "AMARASI BARAT"
						},
						{
							"id": 4530,
							"namakecamatan": "AMARASI SELATAN"
						},
						{
							"id": 4531,
							"namakecamatan": "AMARASI TIMUR"
						},
						{
							"id": 2941,
							"namakecamatan": "AMBAL"
						},
						{
							"id": 4961,
							"namakecamatan": "AMBALAU"
						},
						{
							"id": 6833,
							"namakecamatan": "AMBALAU"
						},
						{
							"id": 6771,
							"namakecamatan": "AMBALAU"
						},
						{
							"id": 4480,
							"namakecamatan": "AMBALAWI"
						},
						{
							"id": 1964,
							"namakecamatan": "AMBARAWA"
						},
						{
							"id": 1906,
							"namakecamatan": "AMBARAWA"
						},
						{
							"id": 3250,
							"namakecamatan": "AMBARAWA"
						},
						{
							"id": 7412,
							"namakecamatan": "AMBATKWI"
						},
						{
							"id": 7666,
							"namakecamatan": "AMBERBAKEN"
						},
						{
							"id": 7808,
							"namakecamatan": "AMBERBAKEN"
						},
						{
							"id": 7825,
							"namakecamatan": "AMBERBAKEN BARAT"
						},
						{
							"id": 3677,
							"namakecamatan": "AMBULU"
						},
						{
							"id": 4087,
							"namakecamatan": "AMBUNTEN"
						},
						{
							"id": 1724,
							"namakecamatan": "AMEN"
						},
						{
							"id": 4533,
							"namakecamatan": "AMFOANG BARAT DAYA"
						},
						{
							"id": 4534,
							"namakecamatan": "AMFOANG BARAT LAUT"
						},
						{
							"id": 4524,
							"namakecamatan": "AMFOANG SELATAN"
						},
						{
							"id": 4542,
							"namakecamatan": "AMFOANG TENGAH"
						},
						{
							"id": 4538,
							"namakecamatan": "AMFOANG TIMUR"
						},
						{
							"id": 4525,
							"namakecamatan": "AMFOANG UTARA"
						},
						{
							"id": 6317,
							"namakecamatan": "AMONGGEDO"
						},
						{
							"id": 5888,
							"namakecamatan": "AMPANA KOTA"
						},
						{
							"id": 5757,
							"namakecamatan": "AMPANA KOTA"
						},
						{
							"id": 5756,
							"namakecamatan": "AMPANA TETE"
						},
						{
							"id": 5887,
							"namakecamatan": "AMPANA TETE"
						},
						{
							"id": 910,
							"namakecamatan": "AMPEK ANGKEK"
						},
						{
							"id": 916,
							"namakecamatan": "AMPEK NAGARI"
						},
						{
							"id": 3014,
							"namakecamatan": "AMPEL"
						},
						{
							"id": 3617,
							"namakecamatan": "AMPELGADING"
						},
						{
							"id": 3345,
							"namakecamatan": "AMPELGADING"
						},
						{
							"id": 4502,
							"namakecamatan": "AMPENAN"
						},
						{
							"id": 5862,
							"namakecamatan": "AMPIBABO"
						},
						{
							"id": 7300,
							"namakecamatan": "AMUMA"
						},
						{
							"id": 7572,
							"namakecamatan": "AMUNGKALPIA"
						},
						{
							"id": 5312,
							"namakecamatan": "AMUNTAI SELATAN"
						},
						{
							"id": 5313,
							"namakecamatan": "AMUNTAI TENGAH"
						},
						{
							"id": 5314,
							"namakecamatan": "AMUNTAI UTARA"
						},
						{
							"id": 5631,
							"namakecamatan": "AMURANG"
						},
						{
							"id": 5638,
							"namakecamatan": "AMURANG BARAT"
						},
						{
							"id": 5639,
							"namakecamatan": "AMURANG TIMUR"
						},
						{
							"id": 1804,
							"namakecamatan": "ANAK RATU AJI"
						},
						{
							"id": 1798,
							"namakecamatan": "ANAK TUHA"
						},
						{
							"id": 7377,
							"namakecamatan": "ANAWI"
						},
						{
							"id": 321,
							"namakecamatan": "ANDAM DEWI"
						},
						{
							"id": 7134,
							"namakecamatan": "ANDEY"
						},
						{
							"id": 2775,
							"namakecamatan": "ANDIR"
						},
						{
							"id": 3028,
							"namakecamatan": "ANDONG"
						},
						{
							"id": 6398,
							"namakecamatan": "ANDOOLO"
						},
						{
							"id": 6471,
							"namakecamatan": "ANDOWIA"
						},
						{
							"id": 6397,
							"namakecamatan": "ANGATA"
						},
						{
							"id": 6313,
							"namakecamatan": "ANGGABERI"
						},
						{
							"id": 5373,
							"namakecamatan": "ANGGANA"
						},
						{
							"id": 6121,
							"namakecamatan": "ANGGERAJA"
						},
						{
							"id": 7664,
							"namakecamatan": "ANGGI"
						},
						{
							"id": 7858,
							"namakecamatan": "ANGGI"
						},
						{
							"id": 7681,
							"namakecamatan": "ANGGI GIDA"
						},
						{
							"id": 7859,
							"namakecamatan": "ANGGI GIDA"
						},
						{
							"id": 6558,
							"namakecamatan": "ANGGREK"
						},
						{
							"id": 6608,
							"namakecamatan": "ANGGREK"
						},
						{
							"id": 7289,
							"namakecamatan": "ANGGRUK"
						},
						{
							"id": 7110,
							"namakecamatan": "ANGKAISERA"
						},
						{
							"id": 5290,
							"namakecamatan": "ANGKINANG"
						},
						{
							"id": 345,
							"namakecamatan": "ANGKOLA BARAT"
						},
						{
							"id": 375,
							"namakecamatan": "ANGKOLA SANGKUNUR"
						},
						{
							"id": 350,
							"namakecamatan": "ANGKOLA SELATAN"
						},
						{
							"id": 347,
							"namakecamatan": "ANGKOLA TIMUR"
						},
						{
							"id": 6208,
							"namakecamatan": "ANGKONA"
						},
						{
							"id": 5339,
							"namakecamatan": "ANGSANA"
						},
						{
							"id": 4168,
							"namakecamatan": "ANGSANA"
						},
						{
							"id": 6987,
							"namakecamatan": "ANIMHA"
						},
						{
							"id": 2623,
							"namakecamatan": "ANJATAN"
						},
						{
							"id": 5261,
							"namakecamatan": "ANJIR MUARA"
						},
						{
							"id": 5260,
							"namakecamatan": "ANJIR PASAR"
						},
						{
							"id": 4897,
							"namakecamatan": "ANJONGAN"
						},
						{
							"id": 6683,
							"namakecamatan": "ANREAPI"
						},
						{
							"id": 5082,
							"namakecamatan": "ANTANG KALANG"
						},
						{
							"id": 2790,
							"namakecamatan": "ANTAPANI"
						},
						{
							"id": 4290,
							"namakecamatan": "ANYAR"
						},
						{
							"id": 7475,
							"namakecamatan": "APALAPSILI"
						},
						{
							"id": 7005,
							"namakecamatan": "APALAPSILI"
						},
						{
							"id": 7291,
							"namakecamatan": "APALAPSILI"
						},
						{
							"id": 7236,
							"namakecamatan": "APAWER HULU"
						},
						{
							"id": 7179,
							"namakecamatan": "ARADIDE"
						},
						{
							"id": 2619,
							"namakecamatan": "ARAHAN"
						},
						{
							"id": 6655,
							"namakecamatan": "ARALLE"
						},
						{
							"id": 645,
							"namakecamatan": "ARAMO"
						},
						{
							"id": 7758,
							"namakecamatan": "ARANDAY"
						},
						{
							"id": 5249,
							"namakecamatan": "ARANIO"
						},
						{
							"id": 2794,
							"namakecamatan": "ARCAMANIK"
						},
						{
							"id": 2553,
							"namakecamatan": "ARGAPURA"
						},
						{
							"id": 3394,
							"namakecamatan": "ARGOMULYO"
						},
						{
							"id": 7699,
							"namakecamatan": "ARGUNI"
						},
						{
							"id": 7410,
							"namakecamatan": "ARIMOP"
						},
						{
							"id": 3687,
							"namakecamatan": "ARJASA"
						},
						{
							"id": 4099,
							"namakecamatan": "ARJASA"
						},
						{
							"id": 3754,
							"namakecamatan": "ARJASA"
						},
						{
							"id": 2329,
							"namakecamatan": "ARJASARI"
						},
						{
							"id": 2532,
							"namakecamatan": "ARJAWINANGUN"
						},
						{
							"id": 3503,
							"namakecamatan": "ARJOSARI"
						},
						{
							"id": 1666,
							"namakecamatan": "ARMA JAYA"
						},
						{
							"id": 7770,
							"namakecamatan": "AROBA"
						},
						{
							"id": 86,
							"namakecamatan": "ARONGAN LAMBALEK"
						},
						{
							"id": 4035,
							"namakecamatan": "AROSBAYA"
						},
						{
							"id": 358,
							"namakecamatan": "ARSE"
						},
						{
							"id": 7244,
							"namakecamatan": "ARSO"
						},
						{
							"id": 7250,
							"namakecamatan": "ARSO BARAT"
						},
						{
							"id": 7248,
							"namakecamatan": "ARSO TIMUR"
						},
						{
							"id": 5973,
							"namakecamatan": "ARUNGKEKE"
						},
						{
							"id": 6805,
							"namakecamatan": "ARU SELATAN"
						},
						{
							"id": 6812,
							"namakecamatan": "ARU SELATAN TIMUR"
						},
						{
							"id": 6813,
							"namakecamatan": "ARU SELATAN UTARA"
						},
						{
							"id": 6806,
							"namakecamatan": "ARU TENGAH"
						},
						{
							"id": 6811,
							"namakecamatan": "ARU TENGAH SELATAN"
						},
						{
							"id": 6810,
							"namakecamatan": "ARU TENGAH TIMUR"
						},
						{
							"id": 5068,
							"namakecamatan": "ARUT SELATAN"
						},
						{
							"id": 5070,
							"namakecamatan": "ARUT UTARA"
						},
						{
							"id": 6807,
							"namakecamatan": "ARU UTARA"
						},
						{
							"id": 6808,
							"namakecamatan": "ARU UTARA TIMURBATULEY"
						},
						{
							"id": 4510,
							"namakecamatan": "ASAKOTA"
						},
						{
							"id": 971,
							"namakecamatan": "ASAM JUJUHAN"
						},
						{
							"id": 3756,
							"namakecamatan": "ASEMBAGUS"
						},
						{
							"id": 4155,
							"namakecamatan": "ASEM ROWO"
						},
						{
							"id": 6297,
							"namakecamatan": "ASERA"
						},
						{
							"id": 6463,
							"namakecamatan": "ASERA"
						},
						{
							"id": 7813,
							"namakecamatan": "ASES"
						},
						{
							"id": 6320,
							"namakecamatan": "ASINUA"
						},
						{
							"id": 6998,
							"namakecamatan": "ASOLOGAIMA"
						},
						{
							"id": 7023,
							"namakecamatan": "ASOLOKOBAL"
						},
						{
							"id": 7058,
							"namakecamatan": "ASOTIPO"
						},
						{
							"id": 6564,
							"namakecamatan": "ASPARAGA"
						},
						{
							"id": 7427,
							"namakecamatan": "ASSUE"
						},
						{
							"id": 5245,
							"namakecamatan": "ASTAMBUL"
						},
						{
							"id": 2780,
							"namakecamatan": "ASTANA ANYAR"
						},
						{
							"id": 2518,
							"namakecamatan": "ASTANAJAPURA"
						},
						{
							"id": 4779,
							"namakecamatan": "ATADEI"
						},
						{
							"id": 4619,
							"namakecamatan": "ATAMBUA BARAT"
						},
						{
							"id": 4620,
							"namakecamatan": "ATAMBUA SELATAN"
						},
						{
							"id": 7726,
							"namakecamatan": "ATHABU/AITINYO BARAT"
						},
						{
							"id": 6550,
							"namakecamatan": "ATIANGOLA"
						},
						{
							"id": 6606,
							"namakecamatan": "ATINGGOLA"
						},
						{
							"id": 7438,
							"namakecamatan": "ATSJ"
						},
						{
							"id": 76,
							"namakecamatan": "ATU LINTANG"
						},
						{
							"id": 1012,
							"namakecamatan": "AUR BIRUGO TIGO BALEH"
						},
						{
							"id": 5195,
							"namakecamatan": "AWANG"
						},
						{
							"id": 6027,
							"namakecamatan": "AWANGPONE"
						},
						{
							"id": 6235,
							"namakecamatan": "AWAN RANTE KARUA"
						},
						{
							"id": 6191,
							"namakecamatan": "AWAN RANTE KARUA"
						},
						{
							"id": 5343,
							"namakecamatan": "AWAYAN"
						},
						{
							"id": 7208,
							"namakecamatan": "AWEIDA"
						},
						{
							"id": 7383,
							"namakecamatan": "AWEKU"
						},
						{
							"id": 7499,
							"namakecamatan": "AWINA"
						},
						{
							"id": 7271,
							"namakecamatan": "AWINBON"
						},
						{
							"id": 2935,
							"namakecamatan": "AYAH"
						},
						{
							"id": 7835,
							"namakecamatan": "AYAMARU"
						},
						{
							"id": 7637,
							"namakecamatan": "AYAMARU"
						},
						{
							"id": 7706,
							"namakecamatan": "AYAMARU"
						},
						{
							"id": 7848,
							"namakecamatan": "AYAMARU BARAT"
						},
						{
							"id": 7843,
							"namakecamatan": "AYAMARU JAYA"
						},
						{
							"id": 7842,
							"namakecamatan": "AYAMARU SELATAN"
						},
						{
							"id": 7844,
							"namakecamatan": "AYAMARU SELATAN JAYA"
						},
						{
							"id": 7847,
							"namakecamatan": "AYAMARU TENGAH"
						},
						{
							"id": 7837,
							"namakecamatan": "AYAMARU TIMUR"
						},
						{
							"id": 7639,
							"namakecamatan": "AYAMARU TIMUR"
						},
						{
							"id": 7719,
							"namakecamatan": "AYAMARU TIMUR"
						},
						{
							"id": 7845,
							"namakecamatan": "AYAMARU TIMURSELATAN"
						},
						{
							"id": 7836,
							"namakecamatan": "AYAMARU UTARA"
						},
						{
							"id": 7716,
							"namakecamatan": "AYAMARU UTARA"
						},
						{
							"id": 7638,
							"namakecamatan": "AYAMARU UTARA"
						},
						{
							"id": 7846,
							"namakecamatan": "AYAMARU UTARA TIMUR"
						},
						{
							"id": 7753,
							"namakecamatan": "AYAU"
						},
						{
							"id": 7454,
							"namakecamatan": "AYIP"
						},
						{
							"id": 7500,
							"namakecamatan": "AYUMNATI"
						},
						{
							"id": 5077,
							"namakecamatan": "BAAMANG"
						},
						{
							"id": 3525,
							"namakecamatan": "BABADAN"
						},
						{
							"id": 221,
							"namakecamatan": "BABAH ROT"
						},
						{
							"id": 2513,
							"namakecamatan": "BABAKAN"
						},
						{
							"id": 2673,
							"namakecamatan": "BABAKANCIKAO"
						},
						{
							"id": 2773,
							"namakecamatan": "BABAKAN CIPARAY"
						},
						{
							"id": 2199,
							"namakecamatan": "BABAKAN MADANG"
						},
						{
							"id": 424,
							"namakecamatan": "BABALAN"
						},
						{
							"id": 3990,
							"namakecamatan": "BABAT"
						},
						{
							"id": 1479,
							"namakecamatan": "BABAT SUPAT"
						},
						{
							"id": 1471,
							"namakecamatan": "BABAT TOMAN"
						},
						{
							"id": 2710,
							"namakecamatan": "BABELAN"
						},
						{
							"id": 5310,
							"namakecamatan": "BABIRIK"
						},
						{
							"id": 7757,
							"namakecamatan": "BABO"
						},
						{
							"id": 24,
							"namakecamatan": "BABUL MAKMUR"
						},
						{
							"id": 29,
							"namakecamatan": "BABUL RAHMAH"
						},
						{
							"id": 5442,
							"namakecamatan": "BABULU"
						},
						{
							"id": 22,
							"namakecamatan": "BABUSSALAM"
						},
						{
							"id": 6895,
							"namakecamatan": "BACAN"
						},
						{
							"id": 6896,
							"namakecamatan": "BACAN BARAT"
						},
						{
							"id": 6901,
							"namakecamatan": "BACAN BARAT UTARA"
						},
						{
							"id": 6904,
							"namakecamatan": "BACAN SELATAN"
						},
						{
							"id": 6894,
							"namakecamatan": "BACAN TIMUR"
						},
						{
							"id": 6908,
							"namakecamatan": "BACAN TIMUR SELATAN"
						},
						{
							"id": 6909,
							"namakecamatan": "BACAN TIMUR TENGAH"
						},
						{
							"id": 6250,
							"namakecamatan": "BACUKIKI"
						},
						{
							"id": 6253,
							"namakecamatan": "BACUKIKI BARAT"
						},
						{
							"id": 23,
							"namakecamatan": "BADAR"
						},
						{
							"id": 3611,
							"namakecamatan": "BADAS"
						},
						{
							"id": 4981,
							"namakecamatan": "BADAU"
						},
						{
							"id": 2034,
							"namakecamatan": "BADAU"
						},
						{
							"id": 3522,
							"namakecamatan": "BADEGAN"
						},
						{
							"id": 324,
							"namakecamatan": "BADIRI"
						},
						{
							"id": 3208,
							"namakecamatan": "BAE"
						},
						{
							"id": 6202,
							"namakecamatan": "BAEBUNTA"
						},
						{
							"id": 1122,
							"namakecamatan": "BAGAN SINEMBAH"
						},
						{
							"id": 2964,
							"namakecamatan": "BAGELEN"
						},
						{
							"id": 3879,
							"namakecamatan": "BAGOR"
						},
						{
							"id": 6839,
							"namakecamatan": "BAGUALA"
						},
						{
							"id": 7645,
							"namakecamatan": "BAGUN"
						},
						{
							"id": 1262,
							"namakecamatan": "BAHAR SELATAN"
						},
						{
							"id": 1261,
							"namakecamatan": "BAHAR UTARA"
						},
						{
							"id": 5488,
							"namakecamatan": "BAHAU HULU"
						},
						{
							"id": 5832,
							"namakecamatan": "BAHODOPI"
						},
						{
							"id": 411,
							"namakecamatan": "BAHOROK"
						},
						{
							"id": 1941,
							"namakecamatan": "BAHUGA"
						},
						{
							"id": 6415,
							"namakecamatan": "BAITO"
						},
						{
							"id": 285,
							"namakecamatan": "BAITURRAHMAN"
						},
						{
							"id": 111,
							"namakecamatan": "BAITUSSALAM"
						},
						{
							"id": 4706,
							"namakecamatan": "BAJAWA"
						},
						{
							"id": 4715,
							"namakecamatan": "BAJAWA UTARA"
						},
						{
							"id": 5986,
							"namakecamatan": "BAJENG"
						},
						{
							"id": 6002,
							"namakecamatan": "BAJENG BARAT"
						},
						{
							"id": 813,
							"namakecamatan": "BAJENIS"
						},
						{
							"id": 6133,
							"namakecamatan": "BAJO"
						},
						{
							"id": 6149,
							"namakecamatan": "BAJO BARAT"
						},
						{
							"id": 1251,
							"namakecamatan": "BAJUBANG"
						},
						{
							"id": 5216,
							"namakecamatan": "BAJUIN"
						},
						{
							"id": 2027,
							"namakecamatan": "BAKAM"
						},
						{
							"id": 5281,
							"namakecamatan": "BAKARANGAN"
						},
						{
							"id": 1774,
							"namakecamatan": "BAKAUHENI"
						},
						{
							"id": 3067,
							"namakecamatan": "BAKI"
						},
						{
							"id": 1,
							"namakecamatan": "BAKONGAN"
						},
						{
							"id": 15,
							"namakecamatan": "BAKONGAN TIMUR"
						},
						{
							"id": 672,
							"namakecamatan": "BAKTIRAJA"
						},
						{
							"id": 146,
							"namakecamatan": "BAKTIYA"
						},
						{
							"id": 164,
							"namakecamatan": "BAKTIYA BARAT"
						},
						{
							"id": 5267,
							"namakecamatan": "BAKUMPAI"
						},
						{
							"id": 3568,
							"namakecamatan": "BAKUNG"
						},
						{
							"id": 5782,
							"namakecamatan": "BALAESANG"
						},
						{
							"id": 5801,
							"namakecamatan": "BALAESANG TANJUNG"
						},
						{
							"id": 4911,
							"namakecamatan": "BALAI"
						},
						{
							"id": 5151,
							"namakecamatan": "BALAI RIAM"
						},
						{
							"id": 6682,
							"namakecamatan": "BALANIPA"
						},
						{
							"id": 5726,
							"namakecamatan": "BALANTAK"
						},
						{
							"id": 5738,
							"namakecamatan": "BALANTAK SELATAN"
						},
						{
							"id": 5739,
							"namakecamatan": "BALANTAK UTARA"
						},
						{
							"id": 3352,
							"namakecamatan": "BALAPULANG"
						},
						{
							"id": 4225,
							"namakecamatan": "BALARAJA"
						},
						{
							"id": 2345,
							"namakecamatan": "BALEENDAH"
						},
						{
							"id": 3950,
							"namakecamatan": "BALEN"
						},
						{
							"id": 3895,
							"namakecamatan": "BALEREJO"
						},
						{
							"id": 584,
							"namakecamatan": "BALIGE"
						},
						{
							"id": 1832,
							"namakecamatan": "BALIK BUKIT"
						},
						{
							"id": 5450,
							"namakecamatan": "BALIKPAPAN BARAT"
						},
						{
							"id": 5454,
							"namakecamatan": "BALIKPAPAN KOTA"
						},
						{
							"id": 5453,
							"namakecamatan": "BALIKPAPAN SELATAN"
						},
						{
							"id": 5452,
							"namakecamatan": "BALIKPAPAN TENGAH"
						},
						{
							"id": 5449,
							"namakecamatan": "BALIKPAPAN TIMUR"
						},
						{
							"id": 5451,
							"namakecamatan": "BALIKPAPAN UTARA"
						},
						{
							"id": 7485,
							"namakecamatan": "BALINGGA"
						},
						{
							"id": 7026,
							"namakecamatan": "BALINGGA"
						},
						{
							"id": 7504,
							"namakecamatan": "BALINGGA BARAT"
						},
						{
							"id": 5875,
							"namakecamatan": "BALINGGI"
						},
						{
							"id": 6665,
							"namakecamatan": "BALLA"
						},
						{
							"id": 6057,
							"namakecamatan": "BALOCCI"
						},
						{
							"id": 3520,
							"namakecamatan": "BALONG"
						},
						{
							"id": 2614,
							"namakecamatan": "BALONGAN"
						},
						{
							"id": 3820,
							"namakecamatan": "BALONGBENDO"
						},
						{
							"id": 4014,
							"namakecamatan": "BALONGPANGGANG"
						},
						{
							"id": 3675,
							"namakecamatan": "BALUNG"
						},
						{
							"id": 6169,
							"namakecamatan": "BALUSU"
						},
						{
							"id": 6224,
							"namakecamatan": "BALUSU"
						},
						{
							"id": 6072,
							"namakecamatan": "BALUSU"
						},
						{
							"id": 6635,
							"namakecamatan": "BAMBAIRA"
						},
						{
							"id": 6626,
							"namakecamatan": "BAMBALAMOTU"
						},
						{
							"id": 6664,
							"namakecamatan": "BAMBANG"
						},
						{
							"id": 3436,
							"namakecamatan": "BAMBANGLIPURO"
						},
						{
							"id": 21,
							"namakecamatan": "BAMBEL"
						},
						{
							"id": 7434,
							"namakecamatan": "BAMGI"
						},
						{
							"id": 7812,
							"namakecamatan": "BAMUSBAMA"
						},
						{
							"id": 5177,
							"namakecamatan": "BANAMA TINGANG"
						},
						{
							"id": 5778,
							"namakecamatan": "BANAWA"
						},
						{
							"id": 5788,
							"namakecamatan": "BANAWA SELATAN"
						},
						{
							"id": 5797,
							"namakecamatan": "BANAWA TENGAH"
						},
						{
							"id": 3256,
							"namakecamatan": "BANCAK"
						},
						{
							"id": 3969,
							"namakecamatan": "BANCAR"
						},
						{
							"id": 6708,
							"namakecamatan": "BANDA"
						},
						{
							"id": 49,
							"namakecamatan": "BANDA ALAM"
						},
						{
							"id": 171,
							"namakecamatan": "BANDA BARO"
						},
						{
							"id": 263,
							"namakecamatan": "BANDA MULIA"
						},
						{
							"id": 506,
							"namakecamatan": "BANDAR"
						},
						{
							"id": 3302,
							"namakecamatan": "BANDAR"
						},
						{
							"id": 64,
							"namakecamatan": "BANDAR"
						},
						{
							"id": 3505,
							"namakecamatan": "BANDAR"
						},
						{
							"id": 270,
							"namakecamatan": "BANDAR"
						},
						{
							"id": 291,
							"namakecamatan": "BANDA RAYA"
						},
						{
							"id": 115,
							"namakecamatan": "BANDAR BARU"
						},
						{
							"id": 282,
							"namakecamatan": "BANDAR BARU"
						},
						{
							"id": 280,
							"namakecamatan": "BANDAR DUA"
						},
						{
							"id": 116,
							"namakecamatan": "BANDAR DUA"
						},
						{
							"id": 505,
							"namakecamatan": "BANDAR HULUAN"
						},
						{
							"id": 3862,
							"namakecamatan": "BANDARKEDUNGMULYO"
						},
						{
							"id": 465,
							"namakecamatan": "BANDAR KHALIFAH"
						},
						{
							"id": 694,
							"namakecamatan": "BANDAR KHALIFAH"
						},
						{
							"id": 507,
							"namakecamatan": "BANDAR MASILAM"
						},
						{
							"id": 1795,
							"namakecamatan": "BANDAR MATARAM"
						},
						{
							"id": 1909,
							"namakecamatan": "BANDAR NEGERISEMUONG"
						},
						{
							"id": 1851,
							"namakecamatan": "BANDAR NEGERI SUOH"
						},
						{
							"id": 531,
							"namakecamatan": "BANDAR PASIR MANDOGE"
						},
						{
							"id": 1101,
							"namakecamatan": "BANDAR PETALANGAN"
						},
						{
							"id": 529,
							"namakecamatan": "BANDAR PULAU"
						},
						{
							"id": 264,
							"namakecamatan": "BANDAR PUSAKA"
						},
						{
							"id": 1100,
							"namakecamatan": "BANDAR SEI KIJANG"
						},
						{
							"id": 1927,
							"namakecamatan": "BANDAR SRIBHAWONO"
						},
						{
							"id": 1803,
							"namakecamatan": "BANDAR SURABAYA"
						},
						{
							"id": 297,
							"namakecamatan": "BANDA SAKTI"
						},
						{
							"id": 1338,
							"namakecamatan": "BANDING AGUNG"
						},
						{
							"id": 1521,
							"namakecamatan": "BANDING AGUNG"
						},
						{
							"id": 3005,
							"namakecamatan": "BANDONGAN"
						},
						{
							"id": 3561,
							"namakecamatan": "BANDUNG"
						},
						{
							"id": 4294,
							"namakecamatan": "BANDUNG"
						},
						{
							"id": 3260,
							"namakecamatan": "BANDUNGAN"
						},
						{
							"id": 2791,
							"namakecamatan": "BANDUNG KIDUL"
						},
						{
							"id": 2785,
							"namakecamatan": "BANDUNG KULON"
						},
						{
							"id": 2779,
							"namakecamatan": "BANDUNG WETAN"
						},
						{
							"id": 6687,
							"namakecamatan": "BANGGAE"
						},
						{
							"id": 6694,
							"namakecamatan": "BANGGAE TIMUR"
						},
						{
							"id": 5843,
							"namakecamatan": "BANGGAI"
						},
						{
							"id": 5911,
							"namakecamatan": "BANGGAI"
						},
						{
							"id": 5916,
							"namakecamatan": "BANGGAI SELATAN"
						},
						{
							"id": 5855,
							"namakecamatan": "BANGGAI SELATAN"
						},
						{
							"id": 5854,
							"namakecamatan": "BANGGAI TENGAH"
						},
						{
							"id": 5917,
							"namakecamatan": "BANGGAI TENGAH"
						},
						{
							"id": 5851,
							"namakecamatan": "BANGGAI UTARA"
						},
						{
							"id": 5912,
							"namakecamatan": "BANGGAI UTARA"
						},
						{
							"id": 1744,
							"namakecamatan": "BANG HAJI"
						},
						{
							"id": 3798,
							"namakecamatan": "BANGIL"
						},
						{
							"id": 3968,
							"namakecamatan": "BANGILAN"
						},
						{
							"id": 5965,
							"namakecamatan": "BANGKALA"
						},
						{
							"id": 5970,
							"namakecamatan": "BANGKALA BARAT"
						},
						{
							"id": 4031,
							"namakecamatan": "BANGKALAN"
						},
						{
							"id": 6183,
							"namakecamatan": "BANGKELEKILA"
						},
						{
							"id": 6231,
							"namakecamatan": "BANGKELEKILA"
						},
						{
							"id": 1036,
							"namakecamatan": "BANGKINANG"
						},
						{
							"id": 1022,
							"namakecamatan": "BANGKINANG KOTA"
						},
						{
							"id": 1212,
							"namakecamatan": "BANGKO"
						},
						{
							"id": 1119,
							"namakecamatan": "BANGKO"
						},
						{
							"id": 1220,
							"namakecamatan": "BANGKO BARAT"
						},
						{
							"id": 1127,
							"namakecamatan": "BANGKO PUSAKO"
						},
						{
							"id": 5853,
							"namakecamatan": "BANGKURUNG"
						},
						{
							"id": 5914,
							"namakecamatan": "BANGKURUNG"
						},
						{
							"id": 4363,
							"namakecamatan": "BANGLI"
						},
						{
							"id": 2606,
							"namakecamatan": "BANGODUA"
						},
						{
							"id": 3698,
							"namakecamatan": "BANGOREJO"
						},
						{
							"id": 3836,
							"namakecamatan": "BANGSAL"
						},
						{
							"id": 3674,
							"namakecamatan": "BANGSALSARI"
						},
						{
							"id": 3218,
							"namakecamatan": "BANGSRI"
						},
						{
							"id": 1111,
							"namakecamatan": "BANGUN PURBA"
						},
						{
							"id": 459,
							"namakecamatan": "BANGUN PURBA"
						},
						{
							"id": 1779,
							"namakecamatan": "BANGUN REJO"
						},
						{
							"id": 3443,
							"namakecamatan": "BANGUNTAPAN"
						},
						{
							"id": 5315,
							"namakecamatan": "BANJANG"
						},
						{
							"id": 4377,
							"namakecamatan": "BANJAR"
						},
						{
							"id": 4181,
							"namakecamatan": "BANJAR"
						},
						{
							"id": 2842,
							"namakecamatan": "BANJAR"
						},
						{
							"id": 1862,
							"namakecamatan": "BANJAR AGUNG"
						},
						{
							"id": 2570,
							"namakecamatan": "BANJARAN"
						},
						{
							"id": 2326,
							"namakecamatan": "BANJARAN"
						},
						{
							"id": 4359,
							"namakecamatan": "BANJARANGKAN"
						},
						{
							"id": 5354,
							"namakecamatan": "BANJARBARU"
						},
						{
							"id": 1883,
							"namakecamatan": "BANJAR BARU"
						},
						{
							"id": 5358,
							"namakecamatan": "BANJARBARU SELATAN"
						},
						{
							"id": 5357,
							"namakecamatan": "BANJARBARU UTARA"
						},
						{
							"id": 3161,
							"namakecamatan": "BANJAREJO"
						},
						{
							"id": 3383,
							"namakecamatan": "BANJARHARJO"
						},
						{
							"id": 2923,
							"namakecamatan": "BANJARMANGU"
						},
						{
							"id": 1874,
							"namakecamatan": "BANJAR MARGO"
						},
						{
							"id": 5351,
							"namakecamatan": "BANJARMASIN BARAT"
						},
						{
							"id": 5349,
							"namakecamatan": "BANJARMASIN SELATAN"
						},
						{
							"id": 5353,
							"namakecamatan": "BANJARMASIN TENGAH"
						},
						{
							"id": 5350,
							"namakecamatan": "BANJARMASIN TIMUR"
						},
						{
							"id": 5352,
							"namakecamatan": "BANJARMASIN UTARA"
						},
						{
							"id": 2920,
							"namakecamatan": "BANJARNEGARA"
						},
						{
							"id": 2458,
							"namakecamatan": "BANJARSARI"
						},
						{
							"id": 3391,
							"namakecamatan": "BANJARSARI"
						},
						{
							"id": 4205,
							"namakecamatan": "BANJARSARI"
						},
						{
							"id": 2382,
							"namakecamatan": "BANJARWANGI"
						},
						{
							"id": 1939,
							"namakecamatan": "BANJIT"
						},
						{
							"id": 3276,
							"namakecamatan": "BANSARI"
						},
						{
							"id": 5958,
							"namakecamatan": "BANTAENG"
						},
						{
							"id": 1058,
							"namakecamatan": "BANTAN"
						},
						{
							"id": 3764,
							"namakecamatan": "BANTARAN"
						},
						{
							"id": 3340,
							"namakecamatan": "BANTARBOLANG"
						},
						{
							"id": 2238,
							"namakecamatan": "BANTARGADUNG"
						},
						{
							"id": 2812,
							"namakecamatan": "BANTAR GEBANG"
						},
						{
							"id": 2409,
							"namakecamatan": "BANTARKALONG"
						},
						{
							"id": 3368,
							"namakecamatan": "BANTARKAWUNG"
						},
						{
							"id": 2865,
							"namakecamatan": "BANTARSARI"
						},
						{
							"id": 2550,
							"namakecamatan": "BANTARUJEG"
						},
						{
							"id": 6041,
							"namakecamatan": "BANTIMURUNG"
						},
						{
							"id": 3439,
							"namakecamatan": "BANTUL"
						},
						{
							"id": 3614,
							"namakecamatan": "BANTUR"
						},
						{
							"id": 5319,
							"namakecamatan": "BANUA LAWAS"
						},
						{
							"id": 5193,
							"namakecamatan": "BANUA LIMA"
						},
						{
							"id": 909,
							"namakecamatan": "BANUHAMPU"
						},
						{
							"id": 3607,
							"namakecamatan": "BANYAKAN"
						},
						{
							"id": 3766,
							"namakecamatan": "BANYUANYAR"
						},
						{
							"id": 1480,
							"namakecamatan": "BANYUASIN I"
						},
						{
							"id": 1481,
							"namakecamatan": "BANYUASIN II"
						},
						{
							"id": 1482,
							"namakecamatan": "BANYUASIN III"
						},
						{
							"id": 4057,
							"namakecamatan": "BANYUATES"
						},
						{
							"id": 3247,
							"namakecamatan": "BANYUBIRU"
						},
						{
							"id": 3021,
							"namakecamatan": "BANYUDONO"
						},
						{
							"id": 3759,
							"namakecamatan": "BANYUGLUGUR"
						},
						{
							"id": 5021,
							"namakecamatan": "BANYUKE HULU"
						},
						{
							"id": 3406,
							"namakecamatan": "BANYUMANIK"
						},
						{
							"id": 1967,
							"namakecamatan": "BANYUMAS"
						},
						{
							"id": 1907,
							"namakecamatan": "BANYUMAS"
						},
						{
							"id": 2880,
							"namakecamatan": "BANYUMAS"
						},
						{
							"id": 3315,
							"namakecamatan": "BANYUPUTIH"
						},
						{
							"id": 3757,
							"namakecamatan": "BANYUPUTIH"
						},
						{
							"id": 2365,
							"namakecamatan": "BANYURESMI"
						},
						{
							"id": 2702,
							"namakecamatan": "BANYUSARI"
						},
						{
							"id": 2967,
							"namakecamatan": "BANYUURIP"
						},
						{
							"id": 3712,
							"namakecamatan": "BANYUWANGI"
						},
						{
							"id": 5808,
							"namakecamatan": "BAOLAN"
						},
						{
							"id": 6262,
							"namakecamatan": "BARA"
						},
						{
							"id": 5303,
							"namakecamatan": "BARABAI"
						},
						{
							"id": 1940,
							"namakecamatan": "BARADATU"
						},
						{
							"id": 6120,
							"namakecamatan": "BARAKA"
						},
						{
							"id": 5271,
							"namakecamatan": "BARAMBAI"
						},
						{
							"id": 1005,
							"namakecamatan": "BARANGIN"
						},
						{
							"id": 6346,
							"namakecamatan": "BARANGKA"
						},
						{
							"id": 6502,
							"namakecamatan": "BARANGKA"
						},
						{
							"id": 6098,
							"namakecamatan": "BARANTI"
						},
						{
							"id": 6628,
							"namakecamatan": "BARAS"
						},
						{
							"id": 3912,
							"namakecamatan": "BARAT"
						},
						{
							"id": 6020,
							"namakecamatan": "BAREBBO"
						},
						{
							"id": 2472,
							"namakecamatan": "BAREGBEG"
						},
						{
							"id": 3848,
							"namakecamatan": "BARENG"
						},
						{
							"id": 5187,
							"namakecamatan": "BARITO TUHUP RAYA"
						},
						{
							"id": 6129,
							"namakecamatan": "BAROKO"
						},
						{
							"id": 5996,
							"namakecamatan": "BAROMBONG"
						},
						{
							"id": 3875,
							"namakecamatan": "BARON"
						},
						{
							"id": 5407,
							"namakecamatan": "BARONG TONGKOK"
						},
						{
							"id": 4282,
							"namakecamatan": "BAROS"
						},
						{
							"id": 2768,
							"namakecamatan": "BAROS"
						},
						{
							"id": 6068,
							"namakecamatan": "BARRU"
						},
						{
							"id": 6528,
							"namakecamatan": "BARUGA"
						},
						{
							"id": 728,
							"namakecamatan": "BARUMUN"
						},
						{
							"id": 356,
							"namakecamatan": "BARUMUN"
						},
						{
							"id": 731,
							"namakecamatan": "BARUMUN SELATAN"
						},
						{
							"id": 354,
							"namakecamatan": "BARUMUN TENGAH"
						},
						{
							"id": 723,
							"namakecamatan": "BARUMUN TENGAH"
						},
						{
							"id": 6228,
							"namakecamatan": "BARUPPU"
						},
						{
							"id": 6177,
							"namakecamatan": "BARUPPU"
						},
						{
							"id": 310,
							"namakecamatan": "BARUS"
						},
						{
							"id": 436,
							"namakecamatan": "BARUSJAHE"
						},
						{
							"id": 326,
							"namakecamatan": "BARUS UTARA"
						},
						{
							"id": 837,
							"namakecamatan": "BASA AMPEK BALAITAPAN"
						},
						{
							"id": 6416,
							"namakecamatan": "BASALA"
						},
						{
							"id": 5097,
							"namakecamatan": "BASARANG"
						},
						{
							"id": 5805,
							"namakecamatan": "BASIDONDO"
						},
						{
							"id": 911,
							"namakecamatan": "BASO"
						},
						{
							"id": 6130,
							"namakecamatan": "BASSE SANGTEMPE"
						},
						{
							"id": 6151,
							"namakecamatan": "BASSE SANGTEMPE UTARA"
						},
						{
							"id": 6772,
							"namakecamatan": "BATABUAL"
						},
						{
							"id": 5106,
							"namakecamatan": "BATAGUH"
						},
						{
							"id": 622,
							"namakecamatan": "BATAHAN"
						},
						{
							"id": 6340,
							"namakecamatan": "BATALAIWARU"
						},
						{
							"id": 2144,
							"namakecamatan": "BATAM KOTA"
						},
						{
							"id": 3311,
							"namakecamatan": "BATANG"
						},
						{
							"id": 5968,
							"namakecamatan": "BATANG"
						},
						{
							"id": 5304,
							"namakecamatan": "BATANG ALAI SELATAN"
						},
						{
							"id": 5307,
							"namakecamatan": "BATANG ALAI TIMUR"
						},
						{
							"id": 5305,
							"namakecamatan": "BATANG ALAI UTARA"
						},
						{
							"id": 3187,
							"namakecamatan": "BATANGAN"
						},
						{
							"id": 888,
							"namakecamatan": "BATANG ANAI"
						},
						{
							"id": 351,
							"namakecamatan": "BATANG ANGKOLA"
						},
						{
							"id": 1235,
							"namakecamatan": "BATANG ASAI"
						},
						{
							"id": 1270,
							"namakecamatan": "BATANG ASAM"
						},
						{
							"id": 4091,
							"namakecamatan": "BATANG BATANG"
						},
						{
							"id": 1049,
							"namakecamatan": "BATANG CENAKU"
						},
						{
							"id": 1050,
							"namakecamatan": "BATANG GANGSAL"
						},
						{
							"id": 899,
							"namakecamatan": "BATANG GASAN"
						},
						{
							"id": 1918,
							"namakecamatan": "BATANGHARI"
						},
						{
							"id": 1469,
							"namakecamatan": "BATANG HARI LEKO"
						},
						{
							"id": 1925,
							"namakecamatan": "BATANGHARI NUBAN"
						},
						{
							"id": 830,
							"namakecamatan": "BATANG KAPAS"
						},
						{
							"id": 5161,
							"namakecamatan": "BATANG KAWA"
						},
						{
							"id": 477,
							"namakecamatan": "BATANG KUIS"
						},
						{
							"id": 372,
							"namakecamatan": "BATANG LUBU SUTAM"
						},
						{
							"id": 730,
							"namakecamatan": "BATANG LUBU SUTAM"
						},
						{
							"id": 4979,
							"namakecamatan": "BATANG LUPAR"
						},
						{
							"id": 1222,
							"namakecamatan": "BATANG MASUMAI"
						},
						{
							"id": 1196,
							"namakecamatan": "BATANG MERANGIN"
						},
						{
							"id": 620,
							"namakecamatan": "BATANG NATAL"
						},
						{
							"id": 361,
							"namakecamatan": "BATANG ONANG"
						},
						{
							"id": 719,
							"namakecamatan": "BATANG ONANG"
						},
						{
							"id": 1056,
							"namakecamatan": "BATANG PERANAP"
						},
						{
							"id": 429,
							"namakecamatan": "BATANG SERANGAN"
						},
						{
							"id": 346,
							"namakecamatan": "BATANG TORU"
						},
						{
							"id": 1080,
							"namakecamatan": "BATANG TUAKA"
						},
						{
							"id": 7279,
							"namakecamatan": "BATANI"
						},
						{
							"id": 7754,
							"namakecamatan": "BATANTA SELATAN"
						},
						{
							"id": 7749,
							"namakecamatan": "BATANTA UTARA"
						},
						{
							"id": 6519,
							"namakecamatan": "BATAUGA"
						},
						{
							"id": 6373,
							"namakecamatan": "BATAUGA"
						},
						{
							"id": 3215,
							"namakecamatan": "BATEALIT"
						},
						{
							"id": 117,
							"namakecamatan": "BATEE"
						},
						{
							"id": 1298,
							"namakecamatan": "BATHIN III"
						},
						{
							"id": 1301,
							"namakecamatan": "BATHIN III ULU"
						},
						{
							"id": 1302,
							"namakecamatan": "BATHIN II PELAYANG"
						},
						{
							"id": 1242,
							"namakecamatan": "BATHIN VIII"
						},
						{
							"id": 5211,
							"namakecamatan": "BATI BATI"
						},
						{
							"id": 1656,
							"namakecamatan": "BATIK NAU"
						},
						{
							"id": 1297,
							"namakecamatan": "BATIN II BABEKO"
						},
						{
							"id": 1248,
							"namakecamatan": "BATIN XXIV"
						},
						{
							"id": 886,
							"namakecamatan": "BATIPUAH SELATAN"
						},
						{
							"id": 874,
							"namakecamatan": "BATIPUH"
						},
						{
							"id": 7258,
							"namakecamatan": "BATOM"
						},
						{
							"id": 4159,
							"namakecamatan": "BATU"
						},
						{
							"id": 2146,
							"namakecamatan": "BATU AJI"
						},
						{
							"id": 4891,
							"namakecamatan": "BATU AMPAR"
						},
						{
							"id": 5438,
							"namakecamatan": "BATU AMPAR"
						},
						{
							"id": 5051,
							"namakecamatan": "BATU AMPAR"
						},
						{
							"id": 2136,
							"namakecamatan": "BATU AMPAR"
						},
						{
							"id": 5215,
							"namakecamatan": "BATU AMPAR"
						},
						{
							"id": 5143,
							"namakecamatan": "BATU AMPAR"
						},
						{
							"id": 4101,
							"namakecamatan": "BATUAN"
						},
						{
							"id": 6522,
							"namakecamatan": "BATU ATAS"
						},
						{
							"id": 6385,
							"namakecamatan": "BATU ATAS"
						},
						{
							"id": 5299,
							"namakecamatan": "BATU BENAWA"
						},
						{
							"id": 1838,
							"namakecamatan": "BATU BRAK"
						},
						{
							"id": 4298,
							"namakecamatan": "BATUCEPER"
						},
						{
							"id": 6546,
							"namakecamatan": "BATUDAA"
						},
						{
							"id": 6548,
							"namakecamatan": "BATUDAA PANTAI"
						},
						{
							"id": 5894,
							"namakecamatan": "BATUDAKA"
						},
						{
							"id": 5368,
							"namakecamatan": "BATU ENGAU"
						},
						{
							"id": 1129,
							"namakecamatan": "BATU HAMPAR"
						},
						{
							"id": 5721,
							"namakecamatan": "BATUI"
						},
						{
							"id": 5735,
							"namakecamatan": "BATUI SELATAN"
						},
						{
							"id": 2315,
							"namakecamatan": "BATUJAJAR"
						},
						{
							"id": 2740,
							"namakecamatan": "BATUJAJAR"
						},
						{
							"id": 2686,
							"namakecamatan": "BATUJAYA"
						},
						{
							"id": 6359,
							"namakecamatan": "BATUKARA"
						},
						{
							"id": 1849,
							"namakecamatan": "BATU KETULIS"
						},
						{
							"id": 4404,
							"namakecamatan": "BATUKLIANG"
						},
						{
							"id": 4413,
							"namakecamatan": "BATUKLIANG UTARA"
						},
						{
							"id": 4440,
							"namakecamatan": "BATU LANTEH"
						},
						{
							"id": 6117,
							"namakecamatan": "BATU LAPPA"
						},
						{
							"id": 4400,
							"namakecamatan": "BATU LAYAR"
						},
						{
							"id": 5331,
							"namakecamatan": "BATU LICIN"
						},
						{
							"id": 5344,
							"namakecamatan": "BATU MANDI"
						},
						{
							"id": 4073,
							"namakecamatan": "BATUMARMAR"
						},
						{
							"id": 2782,
							"namakecamatan": "BATUNUNGGAL"
						},
						{
							"id": 6543,
							"namakecamatan": "BATUPOARO"
						},
						{
							"id": 5399,
							"namakecamatan": "BATU PUTIH"
						},
						{
							"id": 4556,
							"namakecamatan": "BATU PUTIH"
						},
						{
							"id": 6273,
							"namakecamatan": "BATU PUTIH"
						},
						{
							"id": 4092,
							"namakecamatan": "BATU PUTIH"
						},
						{
							"id": 6450,
							"namakecamatan": "BATU PUTIH"
						},
						{
							"id": 2930,
							"namakecamatan": "BATUR"
						},
						{
							"id": 1348,
							"namakecamatan": "BATURAJA BARAT"
						},
						{
							"id": 1349,
							"namakecamatan": "BATURAJA TIMUR"
						},
						{
							"id": 3076,
							"namakecamatan": "BATURETNO"
						},
						{
							"id": 4343,
							"namakecamatan": "BATURITI"
						},
						{
							"id": 2891,
							"namakecamatan": "BATURRADEN"
						},
						{
							"id": 5360,
							"namakecamatan": "BATU SOPANG"
						},
						{
							"id": 3073,
							"namakecamatan": "BATUWARNO"
						},
						{
							"id": 6274,
							"namakecamatan": "BAULA"
						},
						{
							"id": 3947,
							"namakecamatan": "BAURENO"
						},
						{
							"id": 3305,
							"namakecamatan": "BAWANG"
						},
						{
							"id": 2919,
							"namakecamatan": "BAWANG"
						},
						{
							"id": 3251,
							"namakecamatan": "BAWEN"
						},
						{
							"id": 386,
							"namakecamatan": "BAWOLATO"
						},
						{
							"id": 7205,
							"namakecamatan": "BAYA BIRU"
						},
						{
							"id": 4199,
							"namakecamatan": "BAYAH"
						},
						{
							"id": 2968,
							"namakecamatan": "BAYAN"
						},
						{
							"id": 4500,
							"namakecamatan": "BAYAN"
						},
						{
							"id": 832,
							"namakecamatan": "BAYANG"
						},
						{
							"id": 3035,
							"namakecamatan": "BAYAT"
						},
						{
							"id": 2376,
							"namakecamatan": "BAYONGBONG"
						},
						{
							"id": 1474,
							"namakecamatan": "BAYUNG LENCIR"
						},
						{
							"id": 4371,
							"namakecamatan": "BEBANDEM"
						},
						{
							"id": 2521,
							"namakecamatan": "BEBER"
						},
						{
							"id": 61,
							"namakecamatan": "BEBESEN"
						},
						{
							"id": 4905,
							"namakecamatan": "BEDUAI"
						},
						{
							"id": 3278,
							"namakecamatan": "BEJEN"
						},
						{
							"id": 3797,
							"namakecamatan": "BEJI"
						},
						{
							"id": 2823,
							"namakecamatan": "BEJI"
						},
						{
							"id": 2807,
							"namakecamatan": "BEKASI BARAT"
						},
						{
							"id": 2809,
							"namakecamatan": "BEKASI SELATAN"
						},
						{
							"id": 2806,
							"namakecamatan": "BEKASI TIMUR"
						},
						{
							"id": 2808,
							"namakecamatan": "BEKASI UTARA"
						},
						{
							"id": 1792,
							"namakecamatan": "BEKRI"
						},
						{
							"id": 2135,
							"namakecamatan": "BELAKANG PADANG"
						},
						{
							"id": 1834,
							"namakecamatan": "BELALAU"
						},
						{
							"id": 5657,
							"namakecamatan": "BELANG"
						},
						{
							"id": 5625,
							"namakecamatan": "BELANG"
						},
						{
							"id": 5160,
							"namakecamatan": "BELANTIKAN RAYA"
						},
						{
							"id": 2095,
							"namakecamatan": "BELAT"
						},
						{
							"id": 6087,
							"namakecamatan": "BELAWA"
						},
						{
							"id": 5265,
							"namakecamatan": "BELAWANG"
						},
						{
							"id": 1414,
							"namakecamatan": "BELIDA DARAT"
						},
						{
							"id": 3337,
							"namakecamatan": "BELIK"
						},
						{
							"id": 1413,
							"namakecamatan": "BELIMBING"
						},
						{
							"id": 5030,
							"namakecamatan": "BELIMBING"
						},
						{
							"id": 4956,
							"namakecamatan": "BELIMBING"
						},
						{
							"id": 5039,
							"namakecamatan": "BELIMBING HULU"
						},
						{
							"id": 2023,
							"namakecamatan": "BELINYU"
						},
						{
							"id": 5029,
							"namakecamatan": "BELITANG"
						},
						{
							"id": 1501,
							"namakecamatan": "BELITANG"
						},
						{
							"id": 4921,
							"namakecamatan": "BELITANG"
						},
						{
							"id": 1346,
							"namakecamatan": "BELITANG"
						},
						{
							"id": 5027,
							"namakecamatan": "BELITANG HILIR"
						},
						{
							"id": 4917,
							"namakecamatan": "BELITANG HILIR"
						},
						{
							"id": 5028,
							"namakecamatan": "BELITANG HULU"
						},
						{
							"id": 4918,
							"namakecamatan": "BELITANG HULU"
						},
						{
							"id": 1507,
							"namakecamatan": "BELITANG II"
						},
						{
							"id": 1361,
							"namakecamatan": "BELITANG II"
						},
						{
							"id": 1362,
							"namakecamatan": "BELITANG III"
						},
						{
							"id": 1508,
							"namakecamatan": "BELITANG III"
						},
						{
							"id": 1515,
							"namakecamatan": "BELITANG JAYA"
						},
						{
							"id": 1516,
							"namakecamatan": "BELITANG MADANG RAYA"
						},
						{
							"id": 1517,
							"namakecamatan": "BELITANG MULYA"
						},
						{
							"id": 4474,
							"namakecamatan": "BELO"
						},
						{
							"id": 6136,
							"namakecamatan": "BELOPA"
						},
						{
							"id": 6143,
							"namakecamatan": "BELOPA UTARA"
						},
						{
							"id": 1152,
							"namakecamatan": "BENAI"
						},
						{
							"id": 1409,
							"namakecamatan": "BENAKAT"
						},
						{
							"id": 7477,
							"namakecamatan": "BENAWA"
						},
						{
							"id": 7018,
							"namakecamatan": "BENAWA"
						},
						{
							"id": 4299,
							"namakecamatan": "BENDA"
						},
						{
							"id": 256,
							"namakecamatan": "BENDAHARA"
						},
						{
							"id": 3910,
							"namakecamatan": "BENDO"
						},
						{
							"id": 3063,
							"namakecamatan": "BENDOSARI"
						},
						{
							"id": 3539,
							"namakecamatan": "BENDUNGAN"
						},
						{
							"id": 2976,
							"namakecamatan": "BENER"
						},
						{
							"id": 274,
							"namakecamatan": "BENER KELIPAH"
						},
						{
							"id": 5430,
							"namakecamatan": "BENGALON"
						},
						{
							"id": 1057,
							"namakecamatan": "BENGKALIS"
						},
						{
							"id": 4996,
							"namakecamatan": "BENGKAYANG"
						},
						{
							"id": 2143,
							"namakecamatan": "BENGKONG"
						},
						{
							"id": 1842,
							"namakecamatan": "BENGKUNAT"
						},
						{
							"id": 1995,
							"namakecamatan": "BENGKUNAT"
						},
						{
							"id": 1845,
							"namakecamatan": "BENGKUNAT BELIMBING"
						},
						{
							"id": 1996,
							"namakecamatan": "BENGKUNAT BELIMBING"
						},
						{
							"id": 6037,
							"namakecamatan": "BENGO"
						},
						{
							"id": 4016,
							"namakecamatan": "BENJENG"
						},
						{
							"id": 4146,
							"namakecamatan": "BENOWO"
						},
						{
							"id": 5936,
							"namakecamatan": "BENTENG"
						},
						{
							"id": 5414,
							"namakecamatan": "BENTIAN BESAR"
						},
						{
							"id": 6408,
							"namakecamatan": "BENUA"
						},
						{
							"id": 4939,
							"namakecamatan": "BENUA KAYONG"
						},
						{
							"id": 7467,
							"namakecamatan": "BENUKI"
						},
						{
							"id": 7391,
							"namakecamatan": "BENUKI"
						},
						{
							"id": 5604,
							"namakecamatan": "BEO"
						},
						{
							"id": 7146,
							"namakecamatan": "BEOGA"
						},
						{
							"id": 7552,
							"namakecamatan": "BEOGA"
						},
						{
							"id": 7559,
							"namakecamatan": "BEOGA BARAT"
						},
						{
							"id": 7560,
							"namakecamatan": "BEOGA TIMUR"
						},
						{
							"id": 5620,
							"namakecamatan": "BEO SELATAN"
						},
						{
							"id": 5616,
							"namakecamatan": "BEO UTARA"
						},
						{
							"id": 581,
							"namakecamatan": "BERAMPU"
						},
						{
							"id": 435,
							"namakecamatan": "BERASTAGI"
						},
						{
							"id": 7606,
							"namakecamatan": "BERAUR"
						},
						{
							"id": 3474,
							"namakecamatan": "BERBAH"
						},
						{
							"id": 1287,
							"namakecamatan": "BERBAK"
						},
						{
							"id": 3868,
							"namakecamatan": "BERBEK"
						},
						{
							"id": 3253,
							"namakecamatan": "BERGAS"
						},
						{
							"id": 483,
							"namakecamatan": "BERINGIN"
						},
						{
							"id": 1633,
							"namakecamatan": "BERMANI ILIR"
						},
						{
							"id": 1727,
							"namakecamatan": "BERMANI ILIR"
						},
						{
							"id": 1631,
							"namakecamatan": "BERMANI ULU"
						},
						{
							"id": 1645,
							"namakecamatan": "BERMANI ULU RAYA"
						},
						{
							"id": 5251,
							"namakecamatan": "BERUNTUNG BARU"
						},
						{
							"id": 426,
							"namakecamatan": "BESITANG"
						},
						{
							"id": 3773,
							"namakecamatan": "BESUK"
						},
						{
							"id": 3745,
							"namakecamatan": "BESUKI"
						},
						{
							"id": 3559,
							"namakecamatan": "BESUKI"
						},
						{
							"id": 6309,
							"namakecamatan": "BESULUTU"
						},
						{
							"id": 1267,
							"namakecamatan": "BETARA"
						},
						{
							"id": 5512,
							"namakecamatan": "BETAYAU"
						},
						{
							"id": 7455,
							"namakecamatan": "BETCBAMU"
						},
						{
							"id": 6536,
							"namakecamatan": "BETOAMBARI"
						},
						{
							"id": 1484,
							"namakecamatan": "BETUNG"
						},
						{
							"id": 248,
							"namakecamatan": "BEUTONG"
						},
						{
							"id": 254,
							"namakecamatan": "BEUTONG ATEUHBANGGALANG"
						},
						{
							"id": 7356,
							"namakecamatan": "BEWANI"
						},
						{
							"id": 7128,
							"namakecamatan": "BIAK BARAT"
						},
						{
							"id": 7121,
							"namakecamatan": "BIAK KOTA"
						},
						{
							"id": 7123,
							"namakecamatan": "BIAK TIMUR"
						},
						{
							"id": 7122,
							"namakecamatan": "BIAK UTARA"
						},
						{
							"id": 7183,
							"namakecamatan": "BIANDOGA"
						},
						{
							"id": 7588,
							"namakecamatan": "BIANDOGA"
						},
						{
							"id": 5679,
							"namakecamatan": "BIARO"
						},
						{
							"id": 5580,
							"namakecamatan": "BIARO"
						},
						{
							"id": 5400,
							"namakecamatan": "BIATAN"
						},
						{
							"id": 5817,
							"namakecamatan": "BIAU"
						},
						{
							"id": 6615,
							"namakecamatan": "BIAU"
						},
						{
							"id": 7184,
							"namakecamatan": "BIBIDA"
						},
						{
							"id": 4581,
							"namakecamatan": "BIBOKI ANLEU"
						},
						{
							"id": 4598,
							"namakecamatan": "BIBOKI FEOTLEU"
						},
						{
							"id": 4597,
							"namakecamatan": "BIBOKI MOENLEU"
						},
						{
							"id": 4577,
							"namakecamatan": "BIBOKI SELATAN"
						},
						{
							"id": 4596,
							"namakecamatan": "BIBOKI TAN PAH"
						},
						{
							"id": 4580,
							"namakecamatan": "BIBOKI UTARA"
						},
						{
							"id": 5395,
							"namakecamatan": "BIDUK-BIDUK"
						},
						{
							"id": 78,
							"namakecamatan": "BIES"
						},
						{
							"id": 4969,
							"namakecamatan": "BIKA"
						},
						{
							"id": 7811,
							"namakecamatan": "BIKAR"
						},
						{
							"id": 4590,
							"namakecamatan": "BIKOMI NILULAT"
						},
						{
							"id": 4588,
							"namakecamatan": "BIKOMI SELATAN"
						},
						{
							"id": 4589,
							"namakecamatan": "BIKOMI TENGAH"
						},
						{
							"id": 4591,
							"namakecamatan": "BIKOMI UTARA"
						},
						{
							"id": 553,
							"namakecamatan": "BILAH BARAT"
						},
						{
							"id": 554,
							"namakecamatan": "BILAH HILIR"
						},
						{
							"id": 555,
							"namakecamatan": "BILAH HULU"
						},
						{
							"id": 5549,
							"namakecamatan": "BILALANG"
						},
						{
							"id": 6566,
							"namakecamatan": "BILATO"
						},
						{
							"id": 6563,
							"namakecamatan": "BILUHU"
						},
						{
							"id": 7263,
							"namakecamatan": "BIME"
						},
						{
							"id": 7563,
							"namakecamatan": "BINA"
						},
						{
							"id": 3740,
							"namakecamatan": "BINAKAL"
						},
						{
							"id": 5967,
							"namakecamatan": "BINAMU"
						},
						{
							"id": 3579,
							"namakecamatan": "BINANGUN"
						},
						{
							"id": 2849,
							"namakecamatan": "BINANGUN"
						},
						{
							"id": 1641,
							"namakecamatan": "BINDURIANG"
						},
						{
							"id": 1721,
							"namakecamatan": "BINGIN KUNING"
						},
						{
							"id": 415,
							"namakecamatan": "BINJAI"
						},
						{
							"id": 807,
							"namakecamatan": "BINJAI BARAT"
						},
						{
							"id": 4967,
							"namakecamatan": "BINJAI HULU"
						},
						{
							"id": 806,
							"namakecamatan": "BINJAI KOTA"
						},
						{
							"id": 809,
							"namakecamatan": "BINJAI SELATAN"
						},
						{
							"id": 808,
							"namakecamatan": "BINJAI TIMUR"
						},
						{
							"id": 805,
							"namakecamatan": "BINJAI UTARA"
						},
						{
							"id": 2639,
							"namakecamatan": "BINONG"
						},
						{
							"id": 6379,
							"namakecamatan": "BINONGKO"
						},
						{
							"id": 6443,
							"namakecamatan": "BINONGKO"
						},
						{
							"id": 66,
							"namakecamatan": "BINTANG"
						},
						{
							"id": 5330,
							"namakecamatan": "BINTANG ARA"
						},
						{
							"id": 705,
							"namakecamatan": "BINTANG BAYU"
						},
						{
							"id": 2082,
							"namakecamatan": "BINTAN PESISIR"
						},
						{
							"id": 2074,
							"namakecamatan": "BINTAN TIMUR"
						},
						{
							"id": 2075,
							"namakecamatan": "BINTAN UTARA"
						},
						{
							"id": 5521,
							"namakecamatan": "BINTAUNA"
						},
						{
							"id": 5668,
							"namakecamatan": "BINTAUNA"
						},
						{
							"id": 7755,
							"namakecamatan": "BINTUNI"
						},
						{
							"id": 6676,
							"namakecamatan": "BINUANG"
						},
						{
							"id": 4278,
							"namakecamatan": "BINUANG"
						},
						{
							"id": 5275,
							"namakecamatan": "BINUANG"
						},
						{
							"id": 38,
							"namakecamatan": "BIREM BAYEUN"
						},
						{
							"id": 5995,
							"namakecamatan": "BIRINGBULU"
						},
						{
							"id": 6246,
							"namakecamatan": "BIRINGKANAYA"
						},
						{
							"id": 7772,
							"namakecamatan": "BISCOOP"
						},
						{
							"id": 5957,
							"namakecamatan": "BISSAPPU"
						},
						{
							"id": 6153,
							"namakecamatan": "BITTUANG"
						},
						{
							"id": 7386,
							"namakecamatan": "BIUK"
						},
						{
							"id": 3303,
							"namakecamatan": "BLADO"
						},
						{
							"id": 4352,
							"namakecamatan": "BLAHBATUH"
						},
						{
							"id": 1828,
							"namakecamatan": "BLAMBANGAN PAGAR"
						},
						{
							"id": 1937,
							"namakecamatan": "BLAMBANGAN UMPU"
						},
						{
							"id": 2644,
							"namakecamatan": "BLANAKAN"
						},
						{
							"id": 114,
							"namakecamatan": "BLANG BINTANG"
						},
						{
							"id": 233,
							"namakecamatan": "BLANGJERANGO"
						},
						{
							"id": 225,
							"namakecamatan": "BLANGKEJEREN"
						},
						{
							"id": 298,
							"namakecamatan": "BLANG MANGAT"
						},
						{
							"id": 230,
							"namakecamatan": "BLANGPEGAYON"
						},
						{
							"id": 216,
							"namakecamatan": "BLANG PIDIE"
						},
						{
							"id": 4045,
							"namakecamatan": "BLEGA"
						},
						{
							"id": 4109,
							"namakecamatan": "BLIMBING"
						},
						{
							"id": 2397,
							"namakecamatan": "BL. LIMBANGAN"
						},
						{
							"id": 3159,
							"namakecamatan": "BLORA"
						},
						{
							"id": 3987,
							"namakecamatan": "BLULUK"
						},
						{
							"id": 4080,
							"namakecamatan": "BLUTO"
						},
						{
							"id": 4809,
							"namakecamatan": "BOAWAE"
						},
						{
							"id": 4705,
							"namakecamatan": "BOAWAE"
						},
						{
							"id": 2905,
							"namakecamatan": "BOBOTSARI"
						},
						{
							"id": 3339,
							"namakecamatan": "BODEH"
						},
						{
							"id": 7182,
							"namakecamatan": "BOGABAIDA"
						},
						{
							"id": 7384,
							"namakecamatan": "BOGONUK"
						},
						{
							"id": 2761,
							"namakecamatan": "BOGOR BARAT"
						},
						{
							"id": 3165,
							"namakecamatan": "BOGOREJO"
						},
						{
							"id": 2758,
							"namakecamatan": "BOGOR SELATAN"
						},
						{
							"id": 2760,
							"namakecamatan": "BOGOR TENGAH"
						},
						{
							"id": 2759,
							"namakecamatan": "BOGOR TIMUR"
						},
						{
							"id": 2762,
							"namakecamatan": "BOGOR UTARA"
						},
						{
							"id": 3287,
							"namakecamatan": "BOJA"
						},
						{
							"id": 4267,
							"namakecamatan": "BOJONEGARA"
						},
						{
							"id": 3952,
							"namakecamatan": "BOJONEGORO"
						},
						{
							"id": 3326,
							"namakecamatan": "BOJONG"
						},
						{
							"id": 2672,
							"namakecamatan": "BOJONG"
						},
						{
							"id": 3351,
							"namakecamatan": "BOJONG"
						},
						{
							"id": 4171,
							"namakecamatan": "BOJONG"
						},
						{
							"id": 2410,
							"namakecamatan": "BOJONGASIH"
						},
						{
							"id": 2412,
							"namakecamatan": "BOJONGGAMBIR"
						},
						{
							"id": 2207,
							"namakecamatan": "BOJONG GEDE"
						},
						{
							"id": 2248,
							"namakecamatan": "BOJONGGENTENG"
						},
						{
							"id": 2774,
							"namakecamatan": "BOJONGLOA KALER"
						},
						{
							"id": 2787,
							"namakecamatan": "BOJONGLOA KIDUL"
						},
						{
							"id": 2731,
							"namakecamatan": "BOJONGMANGU"
						},
						{
							"id": 4203,
							"namakecamatan": "BOJONGMANIK"
						},
						{
							"id": 2287,
							"namakecamatan": "BOJONGPICUNG"
						},
						{
							"id": 2910,
							"namakecamatan": "BOJONGSARI"
						},
						{
							"id": 2828,
							"namakecamatan": "BOJONGSARI"
						},
						{
							"id": 2321,
							"namakecamatan": "BOJONGSOANG"
						},
						{
							"id": 5913,
							"namakecamatan": "BOKAN KEPULAUAN"
						},
						{
							"id": 5849,
							"namakecamatan": "BOKAN KEPULAUAN"
						},
						{
							"id": 5814,
							"namakecamatan": "BOKAT"
						},
						{
							"id": 4557,
							"namakecamatan": "BOKING"
						},
						{
							"id": 7342,
							"namakecamatan": "BOKONDINI"
						},
						{
							"id": 7355,
							"namakecamatan": "BOKONERI"
						},
						{
							"id": 6091,
							"namakecamatan": "BOLA"
						},
						{
							"id": 4669,
							"namakecamatan": "BOLA"
						},
						{
							"id": 5530,
							"namakecamatan": "BOLAANG"
						},
						{
							"id": 5541,
							"namakecamatan": "BOLAANGITANG TIMUR"
						},
						{
							"id": 5548,
							"namakecamatan": "BOLAANG TIMUR"
						},
						{
							"id": 5688,
							"namakecamatan": "BOLAANG UKI"
						},
						{
							"id": 5524,
							"namakecamatan": "BOLAANG UKI"
						},
						{
							"id": 7009,
							"namakecamatan": "BOLAKME"
						},
						{
							"id": 5520,
							"namakecamatan": "BOLANGITANG BARAT"
						},
						{
							"id": 5670,
							"namakecamatan": "BOLANGITANG BARAT"
						},
						{
							"id": 5669,
							"namakecamatan": "BOLANGITANG TIMUR"
						},
						{
							"id": 5881,
							"namakecamatan": "BOLANO"
						},
						{
							"id": 5867,
							"namakecamatan": "BOLANO LAMBUNU"
						},
						{
							"id": 4802,
							"namakecamatan": "BOLENG"
						},
						{
							"id": 6552,
							"namakecamatan": "BOLIYOHUTO"
						},
						{
							"id": 4472,
							"namakecamatan": "BOLO"
						},
						{
							"id": 7407,
							"namakecamatan": "BOMAKIA"
						},
						{
							"id": 7693,
							"namakecamatan": "BOMBERAY"
						},
						{
							"id": 7329,
							"namakecamatan": "BOMELA"
						},
						{
							"id": 1114,
							"namakecamatan": "BONAI DARUSSALAM"
						},
						{
							"id": 3238,
							"namakecamatan": "BONANG"
						},
						{
							"id": 606,
							"namakecamatan": "BONATUA LUNASI"
						},
						{
							"id": 7141,
							"namakecamatan": "BONDIFUAR"
						},
						{
							"id": 6310,
							"namakecamatan": "BONDOALA"
						},
						{
							"id": 3731,
							"namakecamatan": "BONDOWOSO"
						},
						{
							"id": 6583,
							"namakecamatan": "BONE"
						},
						{
							"id": 6351,
							"namakecamatan": "BONE"
						},
						{
							"id": 6193,
							"namakecamatan": "BONE BONE"
						},
						{
							"id": 6337,
							"namakecamatan": "BONEGUNU"
						},
						{
							"id": 6478,
							"namakecamatan": "BONEGUNU"
						},
						{
							"id": 6652,
							"namakecamatan": "BONEHAU"
						},
						{
							"id": 6578,
							"namakecamatan": "BONEPANTAI"
						},
						{
							"id": 6584,
							"namakecamatan": "BONE RAYA"
						},
						{
							"id": 5412,
							"namakecamatan": "BONGAN"
						},
						{
							"id": 2622,
							"namakecamatan": "BONGAS"
						},
						{
							"id": 6154,
							"namakecamatan": "BONGGAKARADENG"
						},
						{
							"id": 7232,
							"namakecamatan": "BONGGO"
						},
						{
							"id": 7242,
							"namakecamatan": "BONGGO TIMUR"
						},
						{
							"id": 6554,
							"namakecamatan": "BONGOMEME"
						},
						{
							"id": 936,
							"namakecamatan": "BONJOL"
						},
						{
							"id": 2957,
							"namakecamatan": "BONOROWO"
						},
						{
							"id": 5467,
							"namakecamatan": "BONTANG BARAT"
						},
						{
							"id": 5466,
							"namakecamatan": "BONTANG SELATAN"
						},
						{
							"id": 5465,
							"namakecamatan": "BONTANG UTARA"
						},
						{
							"id": 4904,
							"namakecamatan": "BONTI"
						},
						{
							"id": 6043,
							"namakecamatan": "BONTOA"
						},
						{
							"id": 6241,
							"namakecamatan": "BONTOALA"
						},
						{
							"id": 5949,
							"namakecamatan": "BONTO BAHARI"
						},
						{
							"id": 6012,
							"namakecamatan": "BONTOCANI"
						},
						{
							"id": 5937,
							"namakecamatan": "BONTOHARU"
						},
						{
							"id": 5999,
							"namakecamatan": "BONTOLEMPANGANG"
						},
						{
							"id": 5939,
							"namakecamatan": "BONTOMANAI"
						},
						{
							"id": 5990,
							"namakecamatan": "BONTOMARANNU"
						},
						{
							"id": 5938,
							"namakecamatan": "BONTOMATENE"
						},
						{
							"id": 5985,
							"namakecamatan": "BONTONOMPO"
						},
						{
							"id": 6000,
							"namakecamatan": "BONTONOMPO SELATAN"
						},
						{
							"id": 5971,
							"namakecamatan": "BONTORAMBA"
						},
						{
							"id": 5940,
							"namakecamatan": "BONTOSIKUYU"
						},
						{
							"id": 5950,
							"namakecamatan": "BONTO TIRO"
						},
						{
							"id": 589,
							"namakecamatan": "BORBOR"
						},
						{
							"id": 7259,
							"namakecamatan": "BORME"
						},
						{
							"id": 2993,
							"namakecamatan": "BOROBUDUR"
						},
						{
							"id": 657,
							"namakecamatan": "BORONADU"
						},
						{
							"id": 4830,
							"namakecamatan": "BORONG"
						},
						{
							"id": 4728,
							"namakecamatan": "BORONG"
						},
						{
							"id": 491,
							"namakecamatan": "BOSAR MALIGAS"
						},
						{
							"id": 7655,
							"namakecamatan": "BOTAIN"
						},
						{
							"id": 4856,
							"namakecamatan": "BOTIN LEOBELE"
						},
						{
							"id": 4618,
							"namakecamatan": "BOTIN LEO BELE"
						},
						{
							"id": 3742,
							"namakecamatan": "BOTOLINGGO"
						},
						{
							"id": 396,
							"namakecamatan": "BOTOMUZOI"
						},
						{
							"id": 6573,
							"namakecamatan": "BOTUMOITA"
						},
						{
							"id": 6581,
							"namakecamatan": "BOTUPINGGE"
						},
						{
							"id": 7189,
							"namakecamatan": "BOWOBADO"
						},
						{
							"id": 7595,
							"namakecamatan": "BOWOBADO"
						},
						{
							"id": 4986,
							"namakecamatan": "BOYAN TANJUNG"
						},
						{
							"id": 3017,
							"namakecamatan": "BOYOLALI"
						},
						{
							"id": 3546,
							"namakecamatan": "BOYOLANGU"
						},
						{
							"id": 7056,
							"namakecamatan": "BPIRI"
						},
						{
							"id": 1934,
							"namakecamatan": "BRAJA SELEBAH"
						},
						{
							"id": 1274,
							"namakecamatan": "BRAM ITAM"
						},
						{
							"id": 428,
							"namakecamatan": "BRANDAN BARAT"
						},
						{
							"id": 4495,
							"namakecamatan": "BRANG ENE"
						},
						{
							"id": 4449,
							"namakecamatan": "BRANG REA"
						},
						{
							"id": 4493,
							"namakecamatan": "BRANG REA"
						},
						{
							"id": 3289,
							"namakecamatan": "BRANGSONG"
						},
						{
							"id": 3145,
							"namakecamatan": "BRATI"
						},
						{
							"id": 3375,
							"namakecamatan": "BREBES"
						},
						{
							"id": 3252,
							"namakecamatan": "BRINGIN"
						},
						{
							"id": 3933,
							"namakecamatan": "BRINGIN"
						},
						{
							"id": 3992,
							"namakecamatan": "BRONDONG"
						},
						{
							"id": 2973,
							"namakecamatan": "BRUNO"
						},
						{
							"id": 7503,
							"namakecamatan": "BRUWA"
						},
						{
							"id": 7136,
							"namakecamatan": "BRUYADORI"
						},
						{
							"id": 1458,
							"namakecamatan": "BTS. ULU"
						},
						{
							"id": 6137,
							"namakecamatan": "BUA"
						},
						{
							"id": 2792,
							"namakecamatan": "BUAHBATU"
						},
						{
							"id": 2584,
							"namakecamatan": "BUAHDUA"
						},
						{
							"id": 5728,
							"namakecamatan": "BUALEMO"
						},
						{
							"id": 1535,
							"namakecamatan": "BUANA PEMACA"
						},
						{
							"id": 6134,
							"namakecamatan": "BUA PONRANG"
						},
						{
							"id": 3329,
							"namakecamatan": "BUARAN"
						},
						{
							"id": 2936,
							"namakecamatan": "BUAYAN"
						},
						{
							"id": 1949,
							"namakecamatan": "BUAY BAHUGA"
						},
						{
							"id": 1345,
							"namakecamatan": "BUAY MADANG"
						},
						{
							"id": 1500,
							"namakecamatan": "BUAY MADANG"
						},
						{
							"id": 1510,
							"namakecamatan": "BUAY MADANG TIMUR"
						},
						{
							"id": 1527,
							"namakecamatan": "BUAY PEMACA"
						},
						{
							"id": 1353,
							"namakecamatan": "BUAY PEMACA"
						},
						{
							"id": 1530,
							"namakecamatan": "BUAY PEMATANG RIBURANAU TENGAH"
						},
						{
							"id": 1518,
							"namakecamatan": "BUAY PEMUKA BANGSARAJA"
						},
						{
							"id": 1354,
							"namakecamatan": "BUAY PEMUKAL P."
						},
						{
							"id": 1503,
							"namakecamatan": "BUAY PEMUKA PELIUNG"
						},
						{
							"id": 1351,
							"namakecamatan": "BUAY RANJUNG"
						},
						{
							"id": 1537,
							"namakecamatan": "BUAY RAWAN"
						},
						{
							"id": 1525,
							"namakecamatan": "BUAY RUNJUNG"
						},
						{
							"id": 1350,
							"namakecamatan": "BUAY SANDANG AJI"
						},
						{
							"id": 1524,
							"namakecamatan": "BUAY SANDANG AJI"
						},
						{
							"id": 85,
							"namakecamatan": "BUBON"
						},
						{
							"id": 3942,
							"namakecamatan": "BUBULAN"
						},
						{
							"id": 4140,
							"namakecamatan": "BUBUTAN"
						},
						{
							"id": 6642,
							"namakecamatan": "BUDONG-BUDONG"
						},
						{
							"id": 6697,
							"namakecamatan": "BUDONG-BUDONG"
						},
						{
							"id": 3823,
							"namakecamatan": "BUDURAN"
						},
						{
							"id": 4453,
							"namakecamatan": "BUER"
						},
						{
							"id": 7055,
							"namakecamatan": "BUGI"
						},
						{
							"id": 7516,
							"namakecamatan": "BUGUK GONA"
						},
						{
							"id": 4121,
							"namakecamatan": "BUGUL KIDUL"
						},
						{
							"id": 7648,
							"namakecamatan": "BUK"
						},
						{
							"id": 5819,
							"namakecamatan": "BUKAL"
						},
						{
							"id": 2898,
							"namakecamatan": "BUKATEJA"
						},
						{
							"id": 6412,
							"namakecamatan": "BUKE"
						},
						{
							"id": 5946,
							"namakecamatan": "BUKI"
						},
						{
							"id": 931,
							"namakecamatan": "BUKIK BARISAN"
						},
						{
							"id": 62,
							"namakecamatan": "BUKIT"
						},
						{
							"id": 271,
							"namakecamatan": "BUKIT"
						},
						{
							"id": 1059,
							"namakecamatan": "BUKIT BATU"
						},
						{
							"id": 5203,
							"namakecamatan": "BUKIT BATU"
						},
						{
							"id": 2150,
							"namakecamatan": "BUKIT BESTARI"
						},
						{
							"id": 2062,
							"namakecamatan": "BUKITINTAN"
						},
						{
							"id": 1185,
							"namakecamatan": "BUKIT KAPUR"
						},
						{
							"id": 1586,
							"namakecamatan": "BUKIT KECIL"
						},
						{
							"id": 1806,
							"namakecamatan": "BUKIT KEMUNING"
						},
						{
							"id": 1209,
							"namakecamatan": "BUKITKERMAN"
						},
						{
							"id": 614,
							"namakecamatan": "BUKIT MALINTANG"
						},
						{
							"id": 5134,
							"namakecamatan": "BUKIT RAYA"
						},
						{
							"id": 1177,
							"namakecamatan": "BUKIT RAYA"
						},
						{
							"id": 5087,
							"namakecamatan": "BUKIT SANTUAI"
						},
						{
							"id": 849,
							"namakecamatan": "BUKIT SUNDI"
						},
						{
							"id": 27,
							"namakecamatan": "BUKIT TUSAM"
						},
						{
							"id": 5848,
							"namakecamatan": "BUKO"
						},
						{
							"id": 5859,
							"namakecamatan": "BUKO SELATAN"
						},
						{
							"id": 6706,
							"namakecamatan": "BULA"
						},
						{
							"id": 6778,
							"namakecamatan": "BULA"
						},
						{
							"id": 6789,
							"namakecamatan": "BULA BARAT"
						},
						{
							"id": 5847,
							"namakecamatan": "BULAGI"
						},
						{
							"id": 5850,
							"namakecamatan": "BULAGI SELATAN"
						},
						{
							"id": 5858,
							"namakecamatan": "BULAGI UTARA"
						},
						{
							"id": 4156,
							"namakecamatan": "BULAK"
						},
						{
							"id": 3380,
							"namakecamatan": "BULAKAMBA"
						},
						{
							"id": 2139,
							"namakecamatan": "BULANG"
						},
						{
							"id": 6589,
							"namakecamatan": "BULANGO SELATAN"
						},
						{
							"id": 6590,
							"namakecamatan": "BULANGO TIMUR"
						},
						{
							"id": 6588,
							"namakecamatan": "BULANGO ULU"
						},
						{
							"id": 6579,
							"namakecamatan": "BULANGO UTARA"
						},
						{
							"id": 6591,
							"namakecamatan": "BULAWA"
						},
						{
							"id": 4379,
							"namakecamatan": "BULELENG"
						},
						{
							"id": 5156,
							"namakecamatan": "BULIK"
						},
						{
							"id": 5157,
							"namakecamatan": "BULIK TIMUR"
						},
						{
							"id": 6686,
							"namakecamatan": "BULO"
						},
						{
							"id": 1911,
							"namakecamatan": "BULOK"
						},
						{
							"id": 3168,
							"namakecamatan": "BULU"
						},
						{
							"id": 3059,
							"namakecamatan": "BULU"
						},
						{
							"id": 3261,
							"namakecamatan": "BULU"
						},
						{
							"id": 3087,
							"namakecamatan": "BULUKERTO"
						},
						{
							"id": 5953,
							"namakecamatan": "BULUKUMPA"
						},
						{
							"id": 3625,
							"namakecamatan": "BULULAWANG"
						},
						{
							"id": 6008,
							"namakecamatan": "BULUPODDO"
						},
						{
							"id": 2940,
							"namakecamatan": "BULUSPESANTREN"
						},
						{
							"id": 6632,
							"namakecamatan": "BULU TABA"
						},
						{
							"id": 1926,
							"namakecamatan": "BUMI AGUNG"
						},
						{
							"id": 1950,
							"namakecamatan": "BUMI AGUNG"
						},
						{
							"id": 4160,
							"namakecamatan": "BUMIAJI"
						},
						{
							"id": 3369,
							"namakecamatan": "BUMIAYU"
						},
						{
							"id": 3350,
							"namakecamatan": "BUMIJAWA"
						},
						{
							"id": 5217,
							"namakecamatan": "BUMI MAKMUR"
						},
						{
							"id": 1801,
							"namakecamatan": "BUMI NABUNG"
						},
						{
							"id": 1791,
							"namakecamatan": "BUMI RATU NUBAN"
						},
						{
							"id": 5831,
							"namakecamatan": "BUMI RAYA"
						},
						{
							"id": 2016,
							"namakecamatan": "BUMI WARAS"
						},
						{
							"id": 5693,
							"namakecamatan": "BUNAKEN"
						},
						{
							"id": 5702,
							"namakecamatan": "BUNAKEN KEPULAUAN"
						},
						{
							"id": 4024,
							"namakecamatan": "BUNGAH"
						},
						{
							"id": 1620,
							"namakecamatan": "BUNGA MAS"
						},
						{
							"id": 1821,
							"namakecamatan": "BUNGA MAYANG"
						},
						{
							"id": 1509,
							"namakecamatan": "BUNGA MAYANG"
						},
						{
							"id": 1140,
							"namakecamatan": "BUNGA RAYA"
						},
						{
							"id": 3760,
							"namakecamatan": "BUNGATAN"
						},
						{
							"id": 5993,
							"namakecamatan": "BUNGAYA"
						},
						{
							"id": 2390,
							"namakecamatan": "BUNGBULANG"
						},
						{
							"id": 6539,
							"namakecamatan": "BUNGI"
						},
						{
							"id": 6123,
							"namakecamatan": "BUNGIN"
						},
						{
							"id": 3512,
							"namakecamatan": "BUNGKAL"
						},
						{
							"id": 5830,
							"namakecamatan": "BUNGKU BARAT"
						},
						{
							"id": 5837,
							"namakecamatan": "BUNGKU PESISIR"
						},
						{
							"id": 5828,
							"namakecamatan": "BUNGKU SELATAN"
						},
						{
							"id": 5827,
							"namakecamatan": "BUNGKU TENGAH"
						},
						{
							"id": 5840,
							"namakecamatan": "BUNGKU TIMUR"
						},
						{
							"id": 5826,
							"namakecamatan": "BUNGKU UTARA"
						},
						{
							"id": 5925,
							"namakecamatan": "BUNGKU UTARA"
						},
						{
							"id": 1299,
							"namakecamatan": "BUNGO DANI"
						},
						{
							"id": 6058,
							"namakecamatan": "BUNGORO"
						},
						{
							"id": 5283,
							"namakecamatan": "BUNGUR"
						},
						{
							"id": 2100,
							"namakecamatan": "BUNGURAN BARAT"
						},
						{
							"id": 2115,
							"namakecamatan": "BUNGURAN BATUBI"
						},
						{
							"id": 2113,
							"namakecamatan": "BUNGURAN SELATAN"
						},
						{
							"id": 2111,
							"namakecamatan": "BUNGURAN TENGAH"
						},
						{
							"id": 2102,
							"namakecamatan": "BUNGURAN TIMUR"
						},
						{
							"id": 2110,
							"namakecamatan": "BUNGURAN TIMUR LAUT"
						},
						{
							"id": 2103,
							"namakecamatan": "BUNGURAN UTARA"
						},
						{
							"id": 2840,
							"namakecamatan": "BUNGURSARI"
						},
						{
							"id": 2674,
							"namakecamatan": "BUNGURSARI"
						},
						{
							"id": 995,
							"namakecamatan": "BUNGUS TELUK KABUNG"
						},
						{
							"id": 5815,
							"namakecamatan": "BUNOBOGU"
						},
						{
							"id": 5722,
							"namakecamatan": "BUNTA"
						},
						{
							"id": 6219,
							"namakecamatan": "BUNTAO"
						},
						{
							"id": 6159,
							"namakecamatan": "BUNTAO"
						},
						{
							"id": 6127,
							"namakecamatan": "BUNTU BATU"
						},
						{
							"id": 6601,
							"namakecamatan": "BUNTULIA"
						},
						{
							"id": 6669,
							"namakecamatan": "BUNTUMALANGKA"
						},
						{
							"id": 530,
							"namakecamatan": "BUNTU PANE"
						},
						{
							"id": 6176,
							"namakecamatan": "BUNTU PEPASAN"
						},
						{
							"id": 6227,
							"namakecamatan": "BUNTU PEPASAN"
						},
						{
							"id": 1097,
							"namakecamatan": "BUNUT"
						},
						{
							"id": 4972,
							"namakecamatan": "BUNUT HILIR"
						},
						{
							"id": 4973,
							"namakecamatan": "BUNUT HULU"
						},
						{
							"id": 5477,
							"namakecamatan": "BUNYU"
						},
						{
							"id": 6210,
							"namakecamatan": "BURAU"
						},
						{
							"id": 4033,
							"namakecamatan": "BURNEH"
						},
						{
							"id": 2089,
							"namakecamatan": "BURU"
						},
						{
							"id": 7793,
							"namakecamatan": "BURUWAY"
						},
						{
							"id": 5427,
							"namakecamatan": "BUSANG"
						},
						{
							"id": 4376,
							"namakecamatan": "BUSUNG BIU"
						},
						{
							"id": 2970,
							"namakecamatan": "BUTUH"
						},
						{
							"id": 4784,
							"namakecamatan": "BUYASURI"
						},
						{
							"id": 2724,
							"namakecamatan": "CABANGBUNGIN"
						},
						{
							"id": 4183,
							"namakecamatan": "CADASARI"
						},
						{
							"id": 4504,
							"namakecamatan": "CAKRANEGARA"
						},
						{
							"id": 2190,
							"namakecamatan": "CAKUNG"
						},
						{
							"id": 6040,
							"namakecamatan": "CAMBA"
						},
						{
							"id": 1607,
							"namakecamatan": "CAMBAI"
						},
						{
							"id": 2663,
							"namakecamatan": "CAMPAKA"
						},
						{
							"id": 2296,
							"namakecamatan": "CAMPAKA"
						},
						{
							"id": 2306,
							"namakecamatan": "CAMPAKAMULYA"
						},
						{
							"id": 6672,
							"namakecamatan": "CAMPALAGIAN"
						},
						{
							"id": 4052,
							"namakecamatan": "CAMPLONG"
						},
						{
							"id": 3560,
							"namakecamatan": "CAMPURDARAT"
						},
						{
							"id": 3815,
							"namakecamatan": "CANDI"
						},
						{
							"id": 5279,
							"namakecamatan": "CANDI LARAS SELATAN"
						},
						{
							"id": 5280,
							"namakecamatan": "CANDI LARAS UTARA"
						},
						{
							"id": 3006,
							"namakecamatan": "CANDIMULYO"
						},
						{
							"id": 3647,
							"namakecamatan": "CANDIPURO"
						},
						{
							"id": 1770,
							"namakecamatan": "CANDIPURO"
						},
						{
							"id": 3272,
							"namakecamatan": "CANDIROTO"
						},
						{
							"id": 3403,
							"namakecamatan": "CANDISARI"
						},
						{
							"id": 917,
							"namakecamatan": "CANDUNG"
						},
						{
							"id": 3483,
							"namakecamatan": "CANGKRINGAN"
						},
						{
							"id": 2357,
							"namakecamatan": "CANGKUANG"
						},
						{
							"id": 2617,
							"namakecamatan": "CANTIGI"
						},
						{
							"id": 5003,
							"namakecamatan": "CAPKALA"
						},
						{
							"id": 4277,
							"namakecamatan": "CARENANG"
						},
						{
							"id": 2221,
							"namakecamatan": "CARINGIN"
						},
						{
							"id": 2265,
							"namakecamatan": "CARINGIN"
						},
						{
							"id": 2395,
							"namakecamatan": "CARINGIN"
						},
						{
							"id": 4189,
							"namakecamatan": "CARITA"
						},
						{
							"id": 2202,
							"namakecamatan": "CARIU"
						},
						{
							"id": 7864,
							"namakecamatan": "CATUBOUW"
						},
						{
							"id": 7685,
							"namakecamatan": "CATUBOUW"
						},
						{
							"id": 3036,
							"namakecamatan": "CAWAS"
						},
						{
							"id": 71,
							"namakecamatan": "CELALA"
						},
						{
							"id": 6113,
							"namakecamatan": "CEMPA"
						},
						{
							"id": 5074,
							"namakecamatan": "CEMPAGA"
						},
						{
							"id": 5085,
							"namakecamatan": "CEMPAGA HULU"
						},
						{
							"id": 1502,
							"namakecamatan": "CEMPAKA"
						},
						{
							"id": 5356,
							"namakecamatan": "CEMPAKA"
						},
						{
							"id": 1347,
							"namakecamatan": "CEMPAKA"
						},
						{
							"id": 2157,
							"namakecamatan": "CEMPAKA PUTIH"
						},
						{
							"id": 6124,
							"namakecamatan": "CENDANA"
						},
						{
							"id": 1384,
							"namakecamatan": "CENGAL"
						},
						{
							"id": 2167,
							"namakecamatan": "CENGKARENG"
						},
						{
							"id": 6048,
							"namakecamatan": "CENRANA"
						},
						{
							"id": 6031,
							"namakecamatan": "CENRANA"
						},
						{
							"id": 3042,
							"namakecamatan": "CEPER"
						},
						{
							"id": 3293,
							"namakecamatan": "CEPIRING"
						},
						{
							"id": 3015,
							"namakecamatan": "CEPOGO"
						},
						{
							"id": 3155,
							"namakecamatan": "CEPU"
						},
						{
							"id": 5266,
							"namakecamatan": "CERBON"
						},
						{
							"id": 1151,
							"namakecamatan": "CERENTI"
						},
						{
							"id": 4023,
							"namakecamatan": "CERME"
						},
						{
							"id": 3735,
							"namakecamatan": "CERMEE"
						},
						{
							"id": 1244,
							"namakecamatan": "CERMIN NAN GEDANG"
						},
						{
							"id": 2281,
							"namakecamatan": "CIAMBAR"
						},
						{
							"id": 2441,
							"namakecamatan": "CIAMIS"
						},
						{
							"id": 2209,
							"namakecamatan": "CIAMPEA"
						},
						{
							"id": 2682,
							"namakecamatan": "CIAMPEL"
						},
						{
							"id": 2282,
							"namakecamatan": "CIANJUR"
						},
						{
							"id": 2640,
							"namakecamatan": "CIASEM"
						},
						{
							"id": 2660,
							"namakecamatan": "CIATER"
						},
						{
							"id": 2437,
							"namakecamatan": "CIAWI"
						},
						{
							"id": 2218,
							"namakecamatan": "CIAWI"
						},
						{
							"id": 2486,
							"namakecamatan": "CIAWIGEBANG"
						},
						{
							"id": 2245,
							"namakecamatan": "CIBADAK"
						},
						{
							"id": 4214,
							"namakecamatan": "CIBADAK"
						},
						{
							"id": 4726,
							"namakecamatan": "CIBAL"
						},
						{
							"id": 4737,
							"namakecamatan": "CIBAL BARAT"
						},
						{
							"id": 4164,
							"namakecamatan": "CIBALIUNG"
						},
						{
							"id": 2407,
							"namakecamatan": "CIBALONG"
						},
						{
							"id": 2388,
							"namakecamatan": "CIBALONG"
						},
						{
							"id": 2730,
							"namakecamatan": "CIBARUSAH"
						},
						{
							"id": 2371,
							"namakecamatan": "CIBATU"
						},
						{
							"id": 2675,
							"namakecamatan": "CIBATU"
						},
						{
							"id": 4309,
							"namakecamatan": "CIBEBER"
						},
						{
							"id": 2284,
							"namakecamatan": "CIBEBER"
						},
						{
							"id": 4215,
							"namakecamatan": "CIBEBER"
						},
						{
							"id": 2788,
							"namakecamatan": "CIBEUNYING KALER"
						},
						{
							"id": 2784,
							"namakecamatan": "CIBEUNYING KIDUL"
						},
						{
							"id": 2770,
							"namakecamatan": "CIBEUREUM"
						},
						{
							"id": 2837,
							"namakecamatan": "CIBEUREUM"
						},
						{
							"id": 2504,
							"namakecamatan": "CIBEUREUM"
						},
						{
							"id": 2481,
							"namakecamatan": "CIBINGBIN"
						},
						{
							"id": 2195,
							"namakecamatan": "CIBINONG"
						},
						{
							"id": 2301,
							"namakecamatan": "CIBINONG"
						},
						{
							"id": 2795,
							"namakecamatan": "CIBIRU"
						},
						{
							"id": 2715,
							"namakecamatan": "CIBITUNG"
						},
						{
							"id": 2259,
							"namakecamatan": "CIBITUNG"
						},
						{
							"id": 4188,
							"namakecamatan": "CIBITUNG"
						},
						{
							"id": 2399,
							"namakecamatan": "CIBIUK"
						},
						{
							"id": 4304,
							"namakecamatan": "CIBODAS"
						},
						{
							"id": 2648,
							"namakecamatan": "CIBOGO"
						},
						{
							"id": 2689,
							"namakecamatan": "CIBUAYA"
						},
						{
							"id": 2578,
							"namakecamatan": "CIBUGEL"
						},
						{
							"id": 2210,
							"namakecamatan": "CIBUNGBULANG"
						},
						{
							"id": 2338,
							"namakecamatan": "CICALENGKA"
						},
						{
							"id": 2262,
							"namakecamatan": "CICANTAYAN"
						},
						{
							"id": 2776,
							"namakecamatan": "CICENDO"
						},
						{
							"id": 2250,
							"namakecamatan": "CICURUG"
						},
						{
							"id": 2778,
							"namakecamatan": "CIDADAP"
						},
						{
							"id": 2278,
							"namakecamatan": "CIDADAP"
						},
						{
							"id": 2251,
							"namakecamatan": "CIDAHU"
						},
						{
							"id": 2487,
							"namakecamatan": "CIDAHU"
						},
						{
							"id": 2304,
							"namakecamatan": "CIDAUN"
						},
						{
							"id": 2277,
							"namakecamatan": "CIDOLOG"
						},
						{
							"id": 2445,
							"namakecamatan": "CIDOLOG"
						},
						{
							"id": 2256,
							"namakecamatan": "CIEMAS"
						},
						{
							"id": 2428,
							"namakecamatan": "CIGALONTANG"
						},
						{
							"id": 2508,
							"namakecamatan": "CIGANDAMEKAR"
						},
						{
							"id": 2568,
							"namakecamatan": "CIGASONG"
						},
						{
							"id": 2377,
							"namakecamatan": "CIGEDUG"
						},
						{
							"id": 4224,
							"namakecamatan": "CIGEMLONG"
						},
						{
							"id": 4166,
							"namakecamatan": "CIGEULIS"
						},
						{
							"id": 2232,
							"namakecamatan": "CIGOMBONG"
						},
						{
							"id": 2216,
							"namakecamatan": "CIGUDEG"
						},
						{
							"id": 2494,
							"namakecamatan": "CIGUGUR"
						},
						{
							"id": 2467,
							"namakecamatan": "CIGUGUR"
						},
						{
							"id": 2751,
							"namakecamatan": "CIGUGUR"
						},
						{
							"id": 2741,
							"namakecamatan": "CIHAMPELAS"
						},
						{
							"id": 2358,
							"namakecamatan": "CIHAMPELAS"
						},
						{
							"id": 4222,
							"namakecamatan": "CIHARA"
						},
						{
							"id": 2446,
							"namakecamatan": "CIHAURBEUTI"
						},
						{
							"id": 2832,
							"namakecamatan": "CIHIDEUNG"
						},
						{
							"id": 2384,
							"namakecamatan": "CIHURIP"
						},
						{
							"id": 4212,
							"namakecamatan": "CIJAKU"
						},
						{
							"id": 2650,
							"namakecamatan": "CIJAMBE"
						},
						{
							"id": 2310,
							"namakecamatan": "CIJATI"
						},
						{
							"id": 2222,
							"namakecamatan": "CIJERUK"
						},
						{
							"id": 2443,
							"namakecamatan": "CIJEUNGJING"
						},
						{
							"id": 2749,
							"namakecamatan": "CIJULANG"
						},
						{
							"id": 2465,
							"namakecamatan": "CIJULANG"
						},
						{
							"id": 2307,
							"namakecamatan": "CIKADU"
						},
						{
							"id": 2381,
							"namakecamatan": "CIKAJANG"
						},
						{
							"id": 2237,
							"namakecamatan": "CIKAKAK"
						},
						{
							"id": 2404,
							"namakecamatan": "CIKALONG"
						},
						{
							"id": 2293,
							"namakecamatan": "CIKALONGKULON"
						},
						{
							"id": 2336,
							"namakecamatan": "CIKALONGWETAN"
						},
						{
							"id": 2735,
							"namakecamatan": "CIKALONGWETAN"
						},
						{
							"id": 2691,
							"namakecamatan": "CIKAMPEK"
						},
						{
							"id": 2340,
							"namakecamatan": "CIKANCUNG"
						},
						{
							"id": 4275,
							"namakecamatan": "CIKANDE"
						},
						{
							"id": 2716,
							"namakecamatan": "CIKARANG BARAT"
						},
						{
							"id": 2728,
							"namakecamatan": "CIKARANG PUSAT"
						},
						{
							"id": 2727,
							"namakecamatan": "CIKARANG SELATAN"
						},
						{
							"id": 2719,
							"namakecamatan": "CIKARANG TIMUR"
						},
						{
							"id": 2717,
							"namakecamatan": "CIKARANG UTARA"
						},
						{
							"id": 2406,
							"namakecamatan": "CIKATOMAS"
						},
						{
							"id": 2653,
							"namakecamatan": "CIKAUM"
						},
						{
							"id": 4187,
							"namakecamatan": "CIKEDAL"
						},
						{
							"id": 2604,
							"namakecamatan": "CIKEDUNG"
						},
						{
							"id": 2389,
							"namakecamatan": "CIKELET"
						},
						{
							"id": 2244,
							"namakecamatan": "CIKEMBAR"
						},
						{
							"id": 4283,
							"namakecamatan": "CIKEUSAL"
						},
						{
							"id": 4165,
							"namakecamatan": "CIKEUSIK"
						},
						{
							"id": 2240,
							"namakecamatan": "CIKIDANG"
						},
						{
							"id": 2551,
							"namakecamatan": "CIKIJING"
						},
						{
							"id": 2765,
							"namakecamatan": "CIKOLE"
						},
						{
							"id": 2442,
							"namakecamatan": "CIKONENG"
						},
						{
							"id": 4213,
							"namakecamatan": "CIKULUR"
						},
						{
							"id": 4242,
							"namakecamatan": "CIKUPA"
						},
						{
							"id": 2866,
							"namakecamatan": "CILACAP SELATAN"
						},
						{
							"id": 2867,
							"namakecamatan": "CILACAP TENGAH"
						},
						{
							"id": 2868,
							"namakecamatan": "CILACAP UTARA"
						},
						{
							"id": 2285,
							"namakecamatan": "CILAKU"
						},
						{
							"id": 2701,
							"namakecamatan": "CILAMAYA KULON"
						},
						{
							"id": 2693,
							"namakecamatan": "CILAMAYA WETAN"
						},
						{
							"id": 2180,
							"namakecamatan": "CILANDAK"
						},
						{
							"id": 2378,
							"namakecamatan": "CILAWU"
						},
						{
							"id": 2501,
							"namakecamatan": "CILEBAK"
						},
						{
							"id": 2708,
							"namakecamatan": "CILEBAR"
						},
						{
							"id": 4301,
							"namakecamatan": "CILEDUG"
						},
						{
							"id": 2510,
							"namakecamatan": "CILEDUG"
						},
						{
							"id": 4310,
							"namakecamatan": "CILEGON"
						},
						{
							"id": 4206,
							"namakecamatan": "CILELES"
						},
						{
							"id": 2320,
							"namakecamatan": "CILENGKRANG"
						},
						{
							"id": 2201,
							"namakecamatan": "CILEUNGSI"
						},
						{
							"id": 2318,
							"namakecamatan": "CILEUNYI"
						},
						{
							"id": 2742,
							"namakecamatan": "CILILIN"
						},
						{
							"id": 2331,
							"namakecamatan": "CILILIN"
						},
						{
							"id": 2489,
							"namakecamatan": "CILIMUS"
						},
						{
							"id": 2164,
							"namakecamatan": "CILINCING"
						},
						{
							"id": 2825,
							"namakecamatan": "CILODONG"
						},
						{
							"id": 4216,
							"namakecamatan": "CILOGRANG"
						},
						{
							"id": 2886,
							"namakecamatan": "CILONGOK"
						},
						{
							"id": 2500,
							"namakecamatan": "CIMAHI"
						},
						{
							"id": 2829,
							"namakecamatan": "CIMAHI SELATAN"
						},
						{
							"id": 2830,
							"namakecamatan": "CIMAHI TENGAH"
						},
						{
							"id": 2831,
							"namakecamatan": "CIMAHI UTARA"
						},
						{
							"id": 2596,
							"namakecamatan": "CIMALAKA"
						},
						{
							"id": 2819,
							"namakecamatan": "CIMANGGIS"
						},
						{
							"id": 4163,
							"namakecamatan": "CIMANGGU"
						},
						{
							"id": 2280,
							"namakecamatan": "CIMANGGU"
						},
						{
							"id": 2858,
							"namakecamatan": "CIMANGGU"
						},
						{
							"id": 2588,
							"namakecamatan": "CIMANGGUNG"
						},
						{
							"id": 4179,
							"namakecamatan": "CIMANUK"
						},
						{
							"id": 2469,
							"namakecamatan": "CIMARAGAS"
						},
						{
							"id": 4207,
							"namakecamatan": "CIMARGA"
						},
						{
							"id": 2330,
							"namakecamatan": "CIMAUNG"
						},
						{
							"id": 2319,
							"namakecamatan": "CIMENYAN"
						},
						{
							"id": 2750,
							"namakecamatan": "CIMERAK"
						},
						{
							"id": 2466,
							"namakecamatan": "CIMERAK"
						},
						{
							"id": 6021,
							"namakecamatan": "CINA"
						},
						{
							"id": 2799,
							"namakecamatan": "CINAMBO"
						},
						{
							"id": 4291,
							"namakecamatan": "CINANGKA"
						},
						{
							"id": 2421,
							"namakecamatan": "CINEAM"
						},
						{
							"id": 2826,
							"namakecamatan": "CINERE"
						},
						{
							"id": 2571,
							"namakecamatan": "CINGAMBUL"
						},
						{
							"id": 2478,
							"namakecamatan": "CINIRU"
						},
						{
							"id": 2223,
							"namakecamatan": "CIOMAS"
						},
						{
							"id": 4287,
							"namakecamatan": "CIOMAS"
						},
						{
							"id": 2451,
							"namakecamatan": "CIPAKU"
						},
						{
							"id": 2309,
							"namakecamatan": "CIPANAS"
						},
						{
							"id": 4200,
							"namakecamatan": "CIPANAS"
						},
						{
							"id": 2342,
							"namakecamatan": "CIPARAY"
						},
						{
							"id": 2863,
							"namakecamatan": "CIPARI"
						},
						{
							"id": 2316,
							"namakecamatan": "CIPATAT"
						},
						{
							"id": 2738,
							"namakecamatan": "CIPATAT"
						},
						{
							"id": 2402,
							"namakecamatan": "CIPATUJAH"
						},
						{
							"id": 2824,
							"namakecamatan": "CIPAYUNG"
						},
						{
							"id": 2194,
							"namakecamatan": "CIPAYUNG"
						},
						{
							"id": 2833,
							"namakecamatan": "CIPEDES"
						},
						{
							"id": 2337,
							"namakecamatan": "CIPENDEUY"
						},
						{
							"id": 4176,
							"namakecamatan": "CIPEUCANG"
						},
						{
							"id": 2736,
							"namakecamatan": "CIPEUNDEUY"
						},
						{
							"id": 2651,
							"namakecamatan": "CIPEUNDUEY"
						},
						{
							"id": 2497,
							"namakecamatan": "CIPICUNG"
						},
						{
							"id": 4262,
							"namakecamatan": "CIPOCOK JAYA"
						},
						{
							"id": 4321,
							"namakecamatan": "CIPOCOK JAYA"
						},
						{
							"id": 4300,
							"namakecamatan": "CIPONDOH"
						},
						{
							"id": 2333,
							"namakecamatan": "CIPONGKOR"
						},
						{
							"id": 2743,
							"namakecamatan": "CIPONGKOR"
						},
						{
							"id": 2649,
							"namakecamatan": "CIPUNAGARA"
						},
						{
							"id": 4326,
							"namakecamatan": "CIPUTAT"
						},
						{
							"id": 4250,
							"namakecamatan": "CIPUTAT"
						},
						{
							"id": 4258,
							"namakecamatan": "CIPUTAT TIMUR"
						},
						{
							"id": 4327,
							"namakecamatan": "CIPUTAT TIMUR"
						},
						{
							"id": 2260,
							"namakecamatan": "CIRACAP"
						},
						{
							"id": 2193,
							"namakecamatan": "CIRACAS"
						},
						{
							"id": 2286,
							"namakecamatan": "CIRANJANG"
						},
						{
							"id": 2269,
							"namakecamatan": "CIREUNGHAS"
						},
						{
							"id": 4223,
							"namakecamatan": "CIRINTEN"
						},
						{
							"id": 4269,
							"namakecamatan": "CIRUAS"
						},
						{
							"id": 2263,
							"namakecamatan": "CISAAT"
						},
						{
							"id": 2470,
							"namakecamatan": "CISAGA"
						},
						{
							"id": 2633,
							"namakecamatan": "CISALAK"
						},
						{
							"id": 2734,
							"namakecamatan": "CISARUA"
						},
						{
							"id": 2355,
							"namakecamatan": "CISARUA"
						},
						{
							"id": 2219,
							"namakecamatan": "CISARUA"
						},
						{
							"id": 2597,
							"namakecamatan": "CISARUA"
						},
						{
							"id": 4184,
							"namakecamatan": "CISATA"
						},
						{
							"id": 4247,
							"namakecamatan": "CISAUK"
						},
						{
							"id": 2433,
							"namakecamatan": "CISAYONG"
						},
						{
							"id": 2227,
							"namakecamatan": "CISEENG"
						},
						{
							"id": 2394,
							"namakecamatan": "CISEWU"
						},
						{
							"id": 2579,
							"namakecamatan": "CISITU"
						},
						{
							"id": 4229,
							"namakecamatan": "CISOKA"
						},
						{
							"id": 2239,
							"namakecamatan": "CISOLOK"
						},
						{
							"id": 2387,
							"namakecamatan": "CISOMPET"
						},
						{
							"id": 2379,
							"namakecamatan": "CISURUPAN"
						},
						{
							"id": 7424,
							"namakecamatan": "CITAK-MITAK"
						},
						{
							"id": 2766,
							"namakecamatan": "CITAMIANG"
						},
						{
							"id": 4316,
							"namakecamatan": "CITANGKIL"
						},
						{
							"id": 2197,
							"namakecamatan": "CITEUREUP"
						},
						{
							"id": 6080,
							"namakecamatan": "CITTA"
						},
						{
							"id": 4312,
							"namakecamatan": "CIWANDAN"
						},
						{
							"id": 2534,
							"namakecamatan": "CIWARINGIN"
						},
						{
							"id": 2480,
							"namakecamatan": "CIWARU"
						},
						{
							"id": 2352,
							"namakecamatan": "CIWIDEY"
						},
						{
							"id": 3702,
							"namakecamatan": "CLURING"
						},
						{
							"id": 3198,
							"namakecamatan": "CLUWAK"
						},
						{
							"id": 2772,
							"namakecamatan": "COBLONG"
						},
						{
							"id": 3106,
							"namakecamatan": "COLOMADU"
						},
						{
							"id": 3346,
							"namakecamatan": "COMAL"
						},
						{
							"id": 2646,
							"namakecamatan": "COMPRENG"
						},
						{
							"id": 1087,
							"namakecamatan": "CONCONG"
						},
						{
							"id": 2581,
							"namakecamatan": "CONGGEANG"
						},
						{
							"id": 162,
							"namakecamatan": "COT GIREK"
						},
						{
							"id": 2292,
							"namakecamatan": "CUGENANG"
						},
						{
							"id": 1893,
							"namakecamatan": "CUKUH BALAK"
						},
						{
							"id": 2411,
							"namakecamatan": "CULAMEGA"
						},
						{
							"id": 3727,
							"namakecamatan": "CURAHDAMI"
						},
						{
							"id": 6125,
							"namakecamatan": "CURIO"
						},
						{
							"id": 4281,
							"namakecamatan": "CURUG"
						},
						{
							"id": 4241,
							"namakecamatan": "CURUG"
						},
						{
							"id": 4320,
							"namakecamatan": "CURUG"
						},
						{
							"id": 4219,
							"namakecamatan": "CURUG BITUNG"
						},
						{
							"id": 2276,
							"namakecamatan": "CURUG KEMBAR"
						},
						{
							"id": 1630,
							"namakecamatan": "CURUP"
						},
						{
							"id": 1639,
							"namakecamatan": "CURUP SELATAN"
						},
						{
							"id": 1640,
							"namakecamatan": "CURUP TENGAH"
						},
						{
							"id": 1638,
							"namakecamatan": "CURUP TIMUR"
						},
						{
							"id": 1637,
							"namakecamatan": "CURUP UTARA"
						},
						{
							"id": 232,
							"namakecamatan": "DABUN GELANG"
						},
						{
							"id": 5105,
							"namakecamatan": "DADAHUP"
						},
						{
							"id": 7174,
							"namakecamatan": "DAGAI"
						},
						{
							"id": 3889,
							"namakecamatan": "DAGANGAN"
						},
						{
							"id": 5297,
							"namakecamatan": "DAHA BARAT"
						},
						{
							"id": 5293,
							"namakecamatan": "DAHA SELATAN"
						},
						{
							"id": 5294,
							"namakecamatan": "DAHA UTARA"
						},
						{
							"id": 5811,
							"namakecamatan": "DAKO PEMEAN"
						},
						{
							"id": 7535,
							"namakecamatan": "DAL"
						},
						{
							"id": 5408,
							"namakecamatan": "DAMAI"
						},
						{
							"id": 5169,
							"namakecamatan": "DAMANG BATU"
						},
						{
							"id": 2059,
							"namakecamatan": "DAMAR"
						},
						{
							"id": 5611,
							"namakecamatan": "DAMAU"
						},
						{
							"id": 6760,
							"namakecamatan": "DAMER"
						},
						{
							"id": 6815,
							"namakecamatan": "DAMER"
						},
						{
							"id": 5802,
							"namakecamatan": "DAMPAL SELATAN"
						},
						{
							"id": 5803,
							"namakecamatan": "DAMPAL UTARA"
						},
						{
							"id": 5776,
							"namakecamatan": "DAMPELAS"
						},
						{
							"id": 3616,
							"namakecamatan": "DAMPIT"
						},
						{
							"id": 860,
							"namakecamatan": "DANAU KEMBAR"
						},
						{
							"id": 1191,
							"namakecamatan": "DANAU KERINCI"
						},
						{
							"id": 5309,
							"namakecamatan": "DANAU PANGGANG"
						},
						{
							"id": 193,
							"namakecamatan": "DANAU PARIS"
						},
						{
							"id": 5142,
							"namakecamatan": "DANAU SELULUK"
						},
						{
							"id": 5137,
							"namakecamatan": "DANAU SEMBULUH"
						},
						{
							"id": 4992,
							"namakecamatan": "DANAU SETARUM"
						},
						{
							"id": 1326,
							"namakecamatan": "DANAU SIPIN"
						},
						{
							"id": 1322,
							"namakecamatan": "DANAU TELUK"
						},
						{
							"id": 3943,
							"namakecamatan": "DANDER"
						},
						{
							"id": 6493,
							"namakecamatan": "DANGIA"
						},
						{
							"id": 7380,
							"namakecamatan": "DANIME"
						},
						{
							"id": 3487,
							"namakecamatan": "DANUREJAN"
						},
						{
							"id": 6630,
							"namakecamatan": "DAPURANG"
						},
						{
							"id": 2667,
							"namakecamatan": "DARANGDAN"
						},
						{
							"id": 2493,
							"namakecamatan": "DARMA"
						},
						{
							"id": 2577,
							"namakecamatan": "DARMARAJA"
						},
						{
							"id": 35,
							"namakecamatan": "DARUL AMAN"
						},
						{
							"id": 56,
							"namakecamatan": "DARUL FALAH"
						},
						{
							"id": 25,
							"namakecamatan": "DARUL HASANAH"
						},
						{
							"id": 243,
							"namakecamatan": "DARUL HIKMAH"
						},
						{
							"id": 55,
							"namakecamatan": "DARUL IHSAN"
						},
						{
							"id": 98,
							"namakecamatan": "DARUL IMARAH"
						},
						{
							"id": 110,
							"namakecamatan": "DARUL KAMAL"
						},
						{
							"id": 249,
							"namakecamatan": "DARUL MAKMUR"
						},
						{
							"id": 103,
							"namakecamatan": "DARUSSALAM"
						},
						{
							"id": 4089,
							"namakecamatan": "DASUK"
						},
						{
							"id": 7767,
							"namakecamatan": "DATARAN BEIMES"
						},
						{
							"id": 7855,
							"namakecamatan": "DATARAN ISIM"
						},
						{
							"id": 7683,
							"namakecamatan": "DATARAN ISIM"
						},
						{
							"id": 803,
							"namakecamatan": "DATUK BANDAR"
						},
						{
							"id": 804,
							"namakecamatan": "DATUK BANDAR TIMUR"
						},
						{
							"id": 3633,
							"namakecamatan": "DAU"
						},
						{
							"id": 4361,
							"namakecamatan": "DAWAN"
						},
						{
							"id": 3843,
							"namakecamatan": "DAWARBLANDONG"
						},
						{
							"id": 3210,
							"namakecamatan": "DAWE"
						},
						{
							"id": 6823,
							"namakecamatan": "DAWELOR DAWERA"
						},
						{
							"id": 2560,
							"namakecamatan": "DAWUAN"
						},
						{
							"id": 2658,
							"namakecamatan": "DAWUAN"
						},
						{
							"id": 2325,
							"namakecamatan": "DAYEUHKOLOT"
						},
						{
							"id": 2861,
							"namakecamatan": "DAYEUHLUHUR"
						},
						{
							"id": 1138,
							"namakecamatan": "DAYUN"
						},
						{
							"id": 4953,
							"namakecamatan": "DEDAI"
						},
						{
							"id": 7206,
							"namakecamatan": "DEIYAI MIYO"
						},
						{
							"id": 7296,
							"namakecamatan": "DEKAI"
						},
						{
							"id": 4010,
							"namakecamatan": "DEKET"
						},
						{
							"id": 5155,
							"namakecamatan": "DELANG"
						},
						{
							"id": 3047,
							"namakecamatan": "DELANGGU"
						},
						{
							"id": 31,
							"namakecamatan": "DELENG POKHKISEN"
						},
						{
							"id": 118,
							"namakecamatan": "DELIMA"
						},
						{
							"id": 472,
							"namakecamatan": "DELI TUA"
						},
						{
							"id": 4937,
							"namakecamatan": "DELTA PAWAN"
						},
						{
							"id": 3237,
							"namakecamatan": "DEMAK"
						},
						{
							"id": 7399,
							"namakecamatan": "DEMBA"
						},
						{
							"id": 4653,
							"namakecamatan": "DEMON PAGONG"
						},
						{
							"id": 3233,
							"namakecamatan": "DEMPET"
						},
						{
							"id": 1595,
							"namakecamatan": "DEMPO SELATAN"
						},
						{
							"id": 1596,
							"namakecamatan": "DEMPO TENGAH"
						},
						{
							"id": 1594,
							"namakecamatan": "DEMPO UTARA"
						},
						{
							"id": 7072,
							"namakecamatan": "DEMTA"
						},
						{
							"id": 2057,
							"namakecamatan": "DENDANG"
						},
						{
							"id": 1282,
							"namakecamatan": "DENDANG"
						},
						{
							"id": 6175,
							"namakecamatan": "DENDE PIONGAN NAPO"
						},
						{
							"id": 6226,
							"namakecamatan": "DENDE' PIONGAN NAPO"
						},
						{
							"id": 6600,
							"namakecamatan": "DENGILO"
						},
						{
							"id": 4385,
							"namakecamatan": "DENPASAR BARAT"
						},
						{
							"id": 4383,
							"namakecamatan": "DENPASAR SELATAN"
						},
						{
							"id": 4384,
							"namakecamatan": "DENPASAR TIMUR"
						},
						{
							"id": 4386,
							"namakecamatan": "DENPASAR UTARA"
						},
						{
							"id": 1879,
							"namakecamatan": "DENTE TELADAS"
						},
						{
							"id": 7065,
							"namakecamatan": "DEPAPRE"
						},
						{
							"id": 1206,
							"namakecamatan": "DEPATI TUJUH"
						},
						{
							"id": 2539,
							"namakecamatan": "DEPOK"
						},
						{
							"id": 3473,
							"namakecamatan": "DEPOK"
						},
						{
							"id": 7450,
							"namakecamatan": "DER KOUMUR"
						},
						{
							"id": 7558,
							"namakecamatan": "DERVOS"
						},
						{
							"id": 4694,
							"namakecamatan": "DETUKELI"
						},
						{
							"id": 4685,
							"namakecamatan": "DETUSOKO"
						},
						{
							"id": 147,
							"namakecamatan": "DEWANTARA"
						},
						{
							"id": 7682,
							"namakecamatan": "DIDOHU"
						},
						{
							"id": 7862,
							"namakecamatan": "DIDOHU"
						},
						{
							"id": 7013,
							"namakecamatan": "DIMBA"
						},
						{
							"id": 7483,
							"namakecamatan": "DIMBA"
						},
						{
							"id": 5649,
							"namakecamatan": "DIMEMBE"
						},
						{
							"id": 7105,
							"namakecamatan": "DIPA"
						},
						{
							"id": 7322,
							"namakecamatan": "DIRWEMNA"
						},
						{
							"id": 3852,
							"namakecamatan": "DIWEK"
						},
						{
							"id": 3835,
							"namakecamatan": "DLANGGU"
						},
						{
							"id": 3442,
							"namakecamatan": "DLINGO"
						},
						{
							"id": 7584,
							"namakecamatan": "DOGIYAI"
						},
						{
							"id": 7101,
							"namakecamatan": "DOGIYAI"
						},
						{
							"id": 7207,
							"namakecamatan": "DOGOMO"
						},
						{
							"id": 3581,
							"namakecamatan": "DOKO"
						},
						{
							"id": 7166,
							"namakecamatan": "DOKOME"
						},
						{
							"id": 447,
							"namakecamatan": "DOLAT RAYAT"
						},
						{
							"id": 5773,
							"namakecamatan": "DOLO"
						},
						{
							"id": 5907,
							"namakecamatan": "DOLO"
						},
						{
							"id": 5796,
							"namakecamatan": "DOLO BARAT"
						},
						{
							"id": 5906,
							"namakecamatan": "DOLO BARAT"
						},
						{
							"id": 714,
							"namakecamatan": "DOLOK"
						},
						{
							"id": 353,
							"namakecamatan": "DOLOK"
						},
						{
							"id": 500,
							"namakecamatan": "DOLOK BATU NANGGAR"
						},
						{
							"id": 697,
							"namakecamatan": "DOLOK MASIHUL"
						},
						{
							"id": 461,
							"namakecamatan": "DOLOK MASIHUL"
						},
						{
							"id": 695,
							"namakecamatan": "DOLOK MERAWAN"
						},
						{
							"id": 463,
							"namakecamatan": "DOLOK MERAWAN"
						},
						{
							"id": 496,
							"namakecamatan": "DOLOK PANRIBUAN"
						},
						{
							"id": 503,
							"namakecamatan": "DOLOK PARDAMEAN"
						},
						{
							"id": 675,
							"namakecamatan": "DOLOK SANGGUL"
						},
						{
							"id": 362,
							"namakecamatan": "DOLOK SIGOMPULON"
						},
						{
							"id": 713,
							"namakecamatan": "DOLOK SIGOMPULON"
						},
						{
							"id": 509,
							"namakecamatan": "DOLOK SILAU"
						},
						{
							"id": 3887,
							"namakecamatan": "DOLOPO"
						},
						{
							"id": 5904,
							"namakecamatan": "DOLO SELATAN"
						},
						{
							"id": 5785,
							"namakecamatan": "DOLO SELATAN"
						},
						{
							"id": 4463,
							"namakecamatan": "DOMPU"
						},
						{
							"id": 5804,
							"namakecamatan": "DONDO"
						},
						{
							"id": 4478,
							"namakecamatan": "DONGGO"
						},
						{
							"id": 3534,
							"namakecamatan": "DONGKO"
						},
						{
							"id": 3612,
							"namakecamatan": "DONOMULYO"
						},
						{
							"id": 3498,
							"namakecamatan": "DONOROJO"
						},
						{
							"id": 3226,
							"namakecamatan": "DONOROJO"
						},
						{
							"id": 6078,
							"namakecamatan": "DONRI DONRI"
						},
						{
							"id": 4678,
							"namakecamatan": "DORENG"
						},
						{
							"id": 3321,
							"namakecamatan": "DORO"
						},
						{
							"id": 7553,
							"namakecamatan": "DOUFO"
						},
						{
							"id": 7157,
							"namakecamatan": "DOUFO"
						},
						{
							"id": 7371,
							"namakecamatan": "DOW"
						},
						{
							"id": 2224,
							"namakecamatan": "DRAMAGA"
						},
						{
							"id": 3779,
							"namakecamatan": "DRINGU"
						},
						{
							"id": 4027,
							"namakecamatan": "DRIYOREJO"
						},
						{
							"id": 6030,
							"namakecamatan": "DUA BOCCOE"
						},
						{
							"id": 6111,
							"namakecamatan": "DUAMPANUA"
						},
						{
							"id": 6103,
							"namakecamatan": "DUA PITUE"
						},
						{
							"id": 4017,
							"namakecamatan": "DUDUKSAMPEYAN"
						},
						{
							"id": 6602,
							"namakecamatan": "DUHIADAA"
						},
						{
							"id": 4148,
							"namakecamatan": "DUKUHPAKIS"
						},
						{
							"id": 3200,
							"namakecamatan": "DUKUHSETI"
						},
						{
							"id": 3361,
							"namakecamatan": "DUKUHTURI"
						},
						{
							"id": 3366,
							"namakecamatan": "DUKUHWARU"
						},
						{
							"id": 2997,
							"namakecamatan": "DUKUN"
						},
						{
							"id": 4013,
							"namakecamatan": "DUKUN"
						},
						{
							"id": 2524,
							"namakecamatan": "DUKUPUNTANG"
						},
						{
							"id": 6570,
							"namakecamatan": "DULUPI"
						},
						{
							"id": 7187,
							"namakecamatan": "DUMADAMA"
						},
						{
							"id": 1183,
							"namakecamatan": "DUMAI BARAT"
						},
						{
							"id": 1188,
							"namakecamatan": "DUMAI KOTA"
						},
						{
							"id": 1189,
							"namakecamatan": "DUMAI SELATAN"
						},
						{
							"id": 1184,
							"namakecamatan": "DUMAI TIMUR"
						},
						{
							"id": 6624,
							"namakecamatan": "DUMBO RAYA"
						},
						{
							"id": 5550,
							"namakecamatan": "DUMOGA"
						},
						{
							"id": 5526,
							"namakecamatan": "DUMOGA BARAT"
						},
						{
							"id": 5552,
							"namakecamatan": "DUMOGA TENGAH"
						},
						{
							"id": 5551,
							"namakecamatan": "DUMOGA TENGGARA"
						},
						{
							"id": 5527,
							"namakecamatan": "DUMOGA TIMUR"
						},
						{
							"id": 5528,
							"namakecamatan": "DUMOGA UTARA"
						},
						{
							"id": 7364,
							"namakecamatan": "DUNDU"
						},
						{
							"id": 6567,
							"namakecamatan": "DUNGALIYO"
						},
						{
							"id": 6620,
							"namakecamatan": "DUNGINGI"
						},
						{
							"id": 4093,
							"namakecamatan": "DUNGKEK"
						},
						{
							"id": 944,
							"namakecamatan": "DUO KOTO"
						},
						{
							"id": 2092,
							"namakecamatan": "DURAI"
						},
						{
							"id": 7337,
							"namakecamatan": "DURAM"
						},
						{
							"id": 3543,
							"namakecamatan": "DURENAN"
						},
						{
							"id": 2191,
							"namakecamatan": "DUREN SAWIT"
						},
						{
							"id": 6631,
							"namakecamatan": "DURIPOKU"
						},
						{
							"id": 6342,
							"namakecamatan": "DURUKA"
						},
						{
							"id": 5108,
							"namakecamatan": "DUSUN HILIR"
						},
						{
							"id": 5112,
							"namakecamatan": "DUSUN SELATAN"
						},
						{
							"id": 5196,
							"namakecamatan": "DUSUN TENGAH"
						},
						{
							"id": 5192,
							"namakecamatan": "DUSUN TIMUR"
						},
						{
							"id": 5110,
							"namakecamatan": "DUSUN UTARA"
						},
						{
							"id": 7074,
							"namakecamatan": "EBUNGFA"
						},
						{
							"id": 7425,
							"namakecamatan": "EDERA"
						},
						{
							"id": 7366,
							"namakecamatan": "EGIAM"
						},
						{
							"id": 7285,
							"namakecamatan": "EIPUMEK"
						},
						{
							"id": 7196,
							"namakecamatan": "EKADIDE"
						},
						{
							"id": 4834,
							"namakecamatan": "ELAR"
						},
						{
							"id": 4727,
							"namakecamatan": "ELAR"
						},
						{
							"id": 4838,
							"namakecamatan": "ELAR SELATAN"
						},
						{
							"id": 7474,
							"namakecamatan": "ELELIM"
						},
						{
							"id": 7017,
							"namakecamatan": "ELELIM"
						},
						{
							"id": 6984,
							"namakecamatan": "ELIKOBAL"
						},
						{
							"id": 5032,
							"namakecamatan": "ELLA HILIR"
						},
						{
							"id": 4958,
							"namakecamatan": "ELLA HILIR"
						},
						{
							"id": 6803,
							"namakecamatan": "ELPAPUTIH"
						},
						{
							"id": 4970,
							"namakecamatan": "EMBALOH HILIR"
						},
						{
							"id": 4971,
							"namakecamatan": "EMBALOH HULU"
						},
						{
							"id": 7541,
							"namakecamatan": "EMBETPEN"
						},
						{
							"id": 4980,
							"namakecamatan": "EMPANANG"
						},
						{
							"id": 4447,
							"namakecamatan": "EMPANG"
						},
						{
							"id": 903,
							"namakecamatan": "ENAM LINGKUNG"
						},
						{
							"id": 4682,
							"namakecamatan": "ENDE"
						},
						{
							"id": 4683,
							"namakecamatan": "ENDE SELATAN"
						},
						{
							"id": 4698,
							"namakecamatan": "ENDE TENGAH"
						},
						{
							"id": 4699,
							"namakecamatan": "ENDE TIMUR"
						},
						{
							"id": 4697,
							"namakecamatan": "ENDE UTARA"
						},
						{
							"id": 7320,
							"namakecamatan": "ENDOMEN"
						},
						{
							"id": 2013,
							"namakecamatan": "ENGGAL"
						},
						{
							"id": 1646,
							"namakecamatan": "ENGGANO"
						},
						{
							"id": 1071,
							"namakecamatan": "ENOK"
						},
						{
							"id": 6119,
							"namakecamatan": "ENREKANG"
						},
						{
							"id": 4920,
							"namakecamatan": "ENTIKONG"
						},
						{
							"id": 7471,
							"namakecamatan": "ERAGAYAM"
						},
						{
							"id": 7020,
							"namakecamatan": "ERAGAYAM"
						},
						{
							"id": 7574,
							"namakecamatan": "ERELMAKAWIA"
						},
						{
							"id": 5959,
							"namakecamatan": "EREMERASA"
						},
						{
							"id": 5555,
							"namakecamatan": "ERIS"
						},
						{
							"id": 3077,
							"namakecamatan": "EROMOKO"
						},
						{
							"id": 5606,
							"namakecamatan": "ESSANG"
						},
						{
							"id": 5621,
							"namakecamatan": "ESSANG SELATAN"
						},
						{
							"id": 7762,
							"namakecamatan": "FAFURWAR"
						},
						{
							"id": 7687,
							"namakecamatan": "FAK-FAK"
						},
						{
							"id": 7688,
							"namakecamatan": "FAK-FAK BARAT"
						},
						{
							"id": 7691,
							"namakecamatan": "FAK-FAK TENGAH"
						},
						{
							"id": 7689,
							"namakecamatan": "FAK-FAK TIMUR"
						},
						{
							"id": 7698,
							"namakecamatan": "FAKFAK TIMUR TENGAH"
						},
						{
							"id": 648,
							"namakecamatan": "FANAYAMA"
						},
						{
							"id": 4571,
							"namakecamatan": "FATUKOPA"
						},
						{
							"id": 4522,
							"namakecamatan": "FATULEU"
						},
						{
							"id": 4539,
							"namakecamatan": "FATULEU BARAT"
						},
						{
							"id": 4540,
							"namakecamatan": "FATULEU TENGAH"
						},
						{
							"id": 4554,
							"namakecamatan": "FATUMNASI"
						},
						{
							"id": 4570,
							"namakecamatan": "FAUTMOLO"
						},
						{
							"id": 7147,
							"namakecamatan": "FAWI"
						},
						{
							"id": 7441,
							"namakecamatan": "FAYIT"
						},
						{
							"id": 7799,
							"namakecamatan": "FEF"
						},
						{
							"id": 7611,
							"namakecamatan": "FEF"
						},
						{
							"id": 6836,
							"namakecamatan": "FENA FAFAN"
						},
						{
							"id": 6775,
							"namakecamatan": "FENA LEISELA"
						},
						{
							"id": 7414,
							"namakecamatan": "FIRIWAGE"
						},
						{
							"id": 7411,
							"namakecamatan": "FOFI"
						},
						{
							"id": 7727,
							"namakecamatan": "FOKOUR"
						},
						{
							"id": 7702,
							"namakecamatan": "FURWAGI"
						},
						{
							"id": 2067,
							"namakecamatan": "GABEK"
						},
						{
							"id": 3191,
							"namakecamatan": "GABUS"
						},
						{
							"id": 3139,
							"namakecamatan": "GABUS"
						},
						{
							"id": 2603,
							"namakecamatan": "GABUSWETAN"
						},
						{
							"id": 3769,
							"namakecamatan": "GADING"
						},
						{
							"id": 1746,
							"namakecamatan": "GADING CEMPAKA"
						},
						{
							"id": 4119,
							"namakecamatan": "GADINGREJO"
						},
						{
							"id": 1894,
							"namakecamatan": "GADING REJO"
						},
						{
							"id": 1963,
							"namakecamatan": "GADING REJO"
						},
						{
							"id": 5820,
							"namakecamatan": "GADUNG"
						},
						{
							"id": 3234,
							"namakecamatan": "GAJAH"
						},
						{
							"id": 3404,
							"namakecamatan": "GAJAHMUNGKUR"
						},
						{
							"id": 276,
							"namakecamatan": "GAJAH PUTIH"
						},
						{
							"id": 5809,
							"namakecamatan": "GALANG"
						},
						{
							"id": 469,
							"namakecamatan": "GALANG"
						},
						{
							"id": 2142,
							"namakecamatan": "GALANG"
						},
						{
							"id": 6869,
							"namakecamatan": "GALELA"
						},
						{
							"id": 6879,
							"namakecamatan": "GALELA BARAT"
						},
						{
							"id": 6881,
							"namakecamatan": "GALELA SELATAN"
						},
						{
							"id": 6880,
							"namakecamatan": "GALELA UTARA"
						},
						{
							"id": 5984,
							"namakecamatan": "GALESONG"
						},
						{
							"id": 5980,
							"namakecamatan": "GALESONG SELATAN"
						},
						{
							"id": 5981,
							"namakecamatan": "GALESONG UTARA"
						},
						{
							"id": 4873,
							"namakecamatan": "GALING"
						},
						{
							"id": 4048,
							"namakecamatan": "GALIS"
						},
						{
							"id": 4065,
							"namakecamatan": "GALIS"
						},
						{
							"id": 3423,
							"namakecamatan": "GALUR"
						},
						{
							"id": 2153,
							"namakecamatan": "GAMBIR"
						},
						{
							"id": 3703,
							"namakecamatan": "GAMBIRAN"
						},
						{
							"id": 5241,
							"namakecamatan": "GAMBUT"
						},
						{
							"id": 7482,
							"namakecamatan": "GAMELIA"
						},
						{
							"id": 7008,
							"namakecamatan": "GAMELIA"
						},
						{
							"id": 3597,
							"namakecamatan": "GAMPENGREJO"
						},
						{
							"id": 3467,
							"namakecamatan": "GAMPING"
						},
						{
							"id": 6170,
							"namakecamatan": "GANDANGBATU SILLANAN"
						},
						{
							"id": 205,
							"namakecamatan": "GANDAPURA"
						},
						{
							"id": 4085,
							"namakecamatan": "GANDING"
						},
						{
							"id": 2855,
							"namakecamatan": "GANDRUNGMANGU"
						},
						{
							"id": 1587,
							"namakecamatan": "GANDUS"
						},
						{
							"id": 3578,
							"namakecamatan": "GANDUSARI"
						},
						{
							"id": 3540,
							"namakecamatan": "GANDUSARI"
						},
						{
							"id": 2593,
							"namakecamatan": "GANEAS"
						},
						{
							"id": 6891,
							"namakecamatan": "GANE BARAT"
						},
						{
							"id": 6910,
							"namakecamatan": "GANE BARAT SELATAN"
						},
						{
							"id": 6911,
							"namakecamatan": "GANE BARAT UTARA"
						},
						{
							"id": 6890,
							"namakecamatan": "GANE TIMUR"
						},
						{
							"id": 6913,
							"namakecamatan": "GANE TIMUR SELATAN"
						},
						{
							"id": 6914,
							"namakecamatan": "GANE TIMUR TENGAH"
						},
						{
							"id": 4498,
							"namakecamatan": "GANGGA"
						},
						{
							"id": 6079,
							"namakecamatan": "GANRA"
						},
						{
							"id": 2625,
							"namakecamatan": "GANTAR"
						},
						{
							"id": 5963,
							"namakecamatan": "GANTARANG KEKE"
						},
						{
							"id": 3033,
							"namakecamatan": "GANTIWARNO"
						},
						{
							"id": 5947,
							"namakecamatan": "GANTORANG"
						},
						{
							"id": 2056,
							"namakecamatan": "GANTUNG"
						},
						{
							"id": 4094,
							"namakecamatan": "GAPURA"
						},
						{
							"id": 2484,
							"namakecamatan": "GARAWANGI"
						},
						{
							"id": 343,
							"namakecamatan": "GAROGA"
						},
						{
							"id": 3574,
							"namakecamatan": "GARUM"
						},
						{
							"id": 2988,
							"namakecamatan": "GARUNG"
						},
						{
							"id": 2360,
							"namakecamatan": "GARUT KOTA"
						},
						{
							"id": 3068,
							"namakecamatan": "GATAK"
						},
						{
							"id": 1081,
							"namakecamatan": "GAUNG"
						},
						{
							"id": 1075,
							"namakecamatan": "GAUNG ANAK SERKA"
						},
						{
							"id": 4095,
							"namakecamatan": "GAYAM"
						},
						{
							"id": 3965,
							"namakecamatan": "GAYAM"
						},
						{
							"id": 3399,
							"namakecamatan": "GAYAMSARI"
						},
						{
							"id": 4149,
							"namakecamatan": "GAYUNGAN"
						},
						{
							"id": 7033,
							"namakecamatan": "GEAREK"
						},
						{
							"id": 7525,
							"namakecamatan": "GEAREK"
						},
						{
							"id": 2538,
							"namakecamatan": "GEBANG"
						},
						{
							"id": 2974,
							"namakecamatan": "GEBANG"
						},
						{
							"id": 423,
							"namakecamatan": "GEBANG"
						},
						{
							"id": 3209,
							"namakecamatan": "GEBOG"
						},
						{
							"id": 3640,
							"namakecamatan": "GEDANGAN"
						},
						{
							"id": 3824,
							"namakecamatan": "GEDANGAN"
						},
						{
							"id": 3462,
							"namakecamatan": "GEDANGSARI"
						},
						{
							"id": 2797,
							"namakecamatan": "GEDEBAGE"
						},
						{
							"id": 3840,
							"namakecamatan": "GEDEG"
						},
						{
							"id": 1951,
							"namakecamatan": "GEDONG TATAAN"
						},
						{
							"id": 1756,
							"namakecamatan": "GEDONG TATAAN"
						},
						{
							"id": 3488,
							"namakecamatan": "GEDONGTENGEN"
						},
						{
							"id": 1860,
							"namakecamatan": "GEDUNG AJI"
						},
						{
							"id": 1881,
							"namakecamatan": "GEDUNG AJI BARU"
						},
						{
							"id": 1865,
							"namakecamatan": "GEDUNG MENENG"
						},
						{
							"id": 1843,
							"namakecamatan": "GEDUNG SURIAN"
						},
						{
							"id": 4036,
							"namakecamatan": "GEGER"
						},
						{
							"id": 3888,
							"namakecamatan": "GEGER"
						},
						{
							"id": 2274,
							"namakecamatan": "GEGERBITUNG"
						},
						{
							"id": 2536,
							"namakecamatan": "GEGESIK"
						},
						{
							"id": 2308,
							"namakecamatan": "GEKBRONG"
						},
						{
							"id": 7507,
							"namakecamatan": "GELOK BEAM"
						},
						{
							"id": 1396,
							"namakecamatan": "GELUMBANG"
						},
						{
							"id": 3891,
							"namakecamatan": "GEMARANG"
						},
						{
							"id": 3280,
							"namakecamatan": "GEMAWANG"
						},
						{
							"id": 3193,
							"namakecamatan": "GEMBONG"
						},
						{
							"id": 5610,
							"namakecamatan": "GEMEH"
						},
						{
							"id": 3124,
							"namakecamatan": "GEMOLONG"
						},
						{
							"id": 3796,
							"namakecamatan": "GEMPOL"
						},
						{
							"id": 2545,
							"namakecamatan": "GEMPOL"
						},
						{
							"id": 3291,
							"namakecamatan": "GEMUH"
						},
						{
							"id": 3778,
							"namakecamatan": "GENDING"
						},
						{
							"id": 3923,
							"namakecamatan": "GENENG"
						},
						{
							"id": 4134,
							"namakecamatan": "GENTENG"
						},
						{
							"id": 3705,
							"namakecamatan": "GENTENG"
						},
						{
							"id": 6611,
							"namakecamatan": "GENTUMA RAYA"
						},
						{
							"id": 3400,
							"namakecamatan": "GENUK"
						},
						{
							"id": 1286,
							"namakecamatan": "GERAGAI"
						},
						{
							"id": 3936,
							"namakecamatan": "GERIH"
						},
						{
							"id": 4314,
							"namakecamatan": "GEROGOL"
						},
						{
							"id": 4374,
							"namakecamatan": "GEROKGAK"
						},
						{
							"id": 4387,
							"namakecamatan": "GERUNG"
						},
						{
							"id": 2066,
							"namakecamatan": "GERUNGGANG"
						},
						{
							"id": 7522,
							"namakecamatan": "GESELMA"
						},
						{
							"id": 7024,
							"namakecamatan": "GESELMA"
						},
						{
							"id": 3129,
							"namakecamatan": "GESI"
						},
						{
							"id": 3241,
							"namakecamatan": "GETASAN"
						},
						{
							"id": 120,
							"namakecamatan": "GEULUMPANG TIGA"
						},
						{
							"id": 119,
							"namakecamatan": "GEUMPANG"
						},
						{
							"id": 170,
							"namakecamatan": "GEUREDONG PASE"
						},
						{
							"id": 7365,
							"namakecamatan": "GEYA"
						},
						{
							"id": 3136,
							"namakecamatan": "GEYER"
						},
						{
							"id": 4353,
							"namakecamatan": "GIANYAR"
						},
						{
							"id": 381,
							"namakecamatan": "GIDO"
						},
						{
							"id": 7375,
							"namakecamatan": "GIKA"
						},
						{
							"id": 4083,
							"namakecamatan": "GILI GINTING"
						},
						{
							"id": 6093,
							"namakecamatan": "GILIRENG"
						},
						{
							"id": 7359,
							"namakecamatan": "GILUBANDU"
						},
						{
							"id": 3713,
							"namakecamatan": "GIRI"
						},
						{
							"id": 5709,
							"namakecamatan": "GIRIAN"
						},
						{
							"id": 3091,
							"namakecamatan": "GIRIMARTO"
						},
						{
							"id": 2068,
							"namakecamatan": "GIRIMAYA"
						},
						{
							"id": 1653,
							"namakecamatan": "GIRI MULYA"
						},
						{
							"id": 3428,
							"namakecamatan": "GIRIMULYO"
						},
						{
							"id": 3464,
							"namakecamatan": "GIRISUBO"
						},
						{
							"id": 3071,
							"namakecamatan": "GIRITONTRO"
						},
						{
							"id": 3072,
							"namakecamatan": "GIRIWOYO"
						},
						{
							"id": 499,
							"namakecamatan": "GIRSANG SIPANGANBOLON"
						},
						{
							"id": 1904,
							"namakecamatan": "GISTING"
						},
						{
							"id": 3711,
							"namakecamatan": "GLAGAH"
						},
						{
							"id": 4011,
							"namakecamatan": "GLAGAH"
						},
						{
							"id": 3706,
							"namakecamatan": "GLENMORE"
						},
						{
							"id": 143,
							"namakecamatan": "GLUMPANG BARO"
						},
						{
							"id": 5111,
							"namakecamatan": "GN. BINTANG AWAI"
						},
						{
							"id": 7502,
							"namakecamatan": "GOA BALIM"
						},
						{
							"id": 3468,
							"namakecamatan": "GODEAN"
						},
						{
							"id": 3147,
							"namakecamatan": "GODONG"
						},
						{
							"id": 4702,
							"namakecamatan": "GOLEWA"
						},
						{
							"id": 4719,
							"namakecamatan": "GOLEWA BARAT"
						},
						{
							"id": 4718,
							"namakecamatan": "GOLEWA SELATAN"
						},
						{
							"id": 7498,
							"namakecamatan": "GOLLO"
						},
						{
							"id": 2953,
							"namakecamatan": "GOMBONG"
						},
						{
							"id": 7150,
							"namakecamatan": "GOME"
						},
						{
							"id": 7557,
							"namakecamatan": "GOME"
						},
						{
							"id": 7573,
							"namakecamatan": "GOME UTARA"
						},
						{
							"id": 632,
							"namakecamatan": "GOMO"
						},
						{
							"id": 3117,
							"namakecamatan": "GONDANG"
						},
						{
							"id": 3882,
							"namakecamatan": "GONDANG"
						},
						{
							"id": 3553,
							"namakecamatan": "GONDANG"
						},
						{
							"id": 3828,
							"namakecamatan": "GONDANG"
						},
						{
							"id": 3963,
							"namakecamatan": "GONDANG"
						},
						{
							"id": 3621,
							"namakecamatan": "GONDANGLEGI"
						},
						{
							"id": 3107,
							"namakecamatan": "GONDANGREJO"
						},
						{
							"id": 3802,
							"namakecamatan": "GONDANGWETAN"
						},
						{
							"id": 3486,
							"namakecamatan": "GONDOKUSUMAN"
						},
						{
							"id": 3493,
							"namakecamatan": "GONDOMANAN"
						},
						{
							"id": 6788,
							"namakecamatan": "GOROM TIMUR"
						},
						{
							"id": 7345,
							"namakecamatan": "GOYAGE"
						},
						{
							"id": 3009,
							"namakecamatan": "GRABAG"
						},
						{
							"id": 2961,
							"namakecamatan": "GRABAG"
						},
						{
							"id": 3985,
							"namakecamatan": "GRABAGAN"
						},
						{
							"id": 3804,
							"namakecamatan": "GRATI"
						},
						{
							"id": 2546,
							"namakecamatan": "GREGED"
						},
						{
							"id": 4028,
							"namakecamatan": "GRESIK"
						},
						{
							"id": 7080,
							"namakecamatan": "GRESI SELATAN"
						},
						{
							"id": 3307,
							"namakecamatan": "GRINGSING"
						},
						{
							"id": 3143,
							"namakecamatan": "GROBOGAN"
						},
						{
							"id": 3066,
							"namakecamatan": "GROGOL"
						},
						{
							"id": 3598,
							"namakecamatan": "GROGOL"
						},
						{
							"id": 2168,
							"namakecamatan": "GROGOL PETAMBURAN"
						},
						{
							"id": 139,
							"namakecamatan": "GRONG-GRONG"
						},
						{
							"id": 3726,
							"namakecamatan": "GRUJUGAN"
						},
						{
							"id": 6517,
							"namakecamatan": "GU"
						},
						{
							"id": 6372,
							"namakecamatan": "G U"
						},
						{
							"id": 4135,
							"namakecamatan": "GUBENG"
						},
						{
							"id": 3148,
							"namakecamatan": "GUBUG"
						},
						{
							"id": 7172,
							"namakecamatan": "GUBUME"
						},
						{
							"id": 3657,
							"namakecamatan": "GUCIALIT"
						},
						{
							"id": 3846,
							"namakecamatan": "GUDO"
						},
						{
							"id": 921,
							"namakecamatan": "GUGUAK"
						},
						{
							"id": 1010,
							"namakecamatan": "GUGUAK PANJANG"
						},
						{
							"id": 4084,
							"namakecamatan": "GULUK-GULUK"
						},
						{
							"id": 1436,
							"namakecamatan": "GUMAY TALANG"
						},
						{
							"id": 1440,
							"namakecamatan": "GUMAY ULU"
						},
						{
							"id": 5787,
							"namakecamatan": "GUMBASA"
						},
						{
							"id": 5903,
							"namakecamatan": "GUMBASA"
						},
						{
							"id": 2884,
							"namakecamatan": "GUMELAR"
						},
						{
							"id": 3669,
							"namakecamatan": "GUMUKMAS"
						},
						{
							"id": 7512,
							"namakecamatan": "GUNA"
						},
						{
							"id": 7361,
							"namakecamatan": "GUNDAGI"
						},
						{
							"id": 3169,
							"namakecamatan": "GUNEM"
						},
						{
							"id": 3229,
							"namakecamatan": "GUNTUR"
						},
						{
							"id": 927,
							"namakecamatan": "GUNUANG OMEH"
						},
						{
							"id": 1982,
							"namakecamatan": "GUNUNG AGUNG"
						},
						{
							"id": 1873,
							"namakecamatan": "GUNUNG AGUNG"
						},
						{
							"id": 1905,
							"namakecamatan": "GUNUNG ALIP"
						},
						{
							"id": 4152,
							"namakecamatan": "GUNUNG ANYAR"
						},
						{
							"id": 2261,
							"namakecamatan": "GUNUNG GURUH"
						},
						{
							"id": 2334,
							"namakecamatan": "GUNUNGHALU"
						},
						{
							"id": 2746,
							"namakecamatan": "GUNUNGHALU"
						},
						{
							"id": 2529,
							"namakecamatan": "GUNUNG JATI"
						},
						{
							"id": 4256,
							"namakecamatan": "GUNUNG KALER"
						},
						{
							"id": 4204,
							"namakecamatan": "GUNUNGKENCANA"
						},
						{
							"id": 1195,
							"namakecamatan": "GUNUNG KERINCI"
						},
						{
							"id": 2072,
							"namakecamatan": "GUNUNG KIJANG"
						},
						{
							"id": 1946,
							"namakecamatan": "GUNUNG LABUHAN"
						},
						{
							"id": 485,
							"namakecamatan": "GUNUNG MALELA"
						},
						{
							"id": 486,
							"namakecamatan": "GUNUNG MALIGAS"
						},
						{
							"id": 1394,
							"namakecamatan": "GUNUNG MEGANG"
						},
						{
							"id": 188,
							"namakecamatan": "GUNUNG MERIAH"
						},
						{
							"id": 451,
							"namakecamatan": "GUNUNG MERIAH"
						},
						{
							"id": 3407,
							"namakecamatan": "GUNUNGPATI"
						},
						{
							"id": 1930,
							"namakecamatan": "GUNUNG PELINDUNG"
						},
						{
							"id": 5115,
							"namakecamatan": "GUNUNG PUREI"
						},
						{
							"id": 2196,
							"namakecamatan": "GUNUNG PUTRI"
						},
						{
							"id": 2764,
							"namakecamatan": "GUNUNG PUYUH"
						},
						{
							"id": 1190,
							"namakecamatan": "GUNUNG RAYA"
						},
						{
							"id": 1041,
							"namakecamatan": "GUNUNG SAHILAN"
						},
						{
							"id": 4395,
							"namakecamatan": "GUNUNGSARI"
						},
						{
							"id": 4293,
							"namakecamatan": "GUNUNG SARI"
						},
						{
							"id": 2205,
							"namakecamatan": "GUNUNG SINDUR"
						},
						{
							"id": 580,
							"namakecamatan": "GUNUNG SITEMBER"
						},
						{
							"id": 821,
							"namakecamatan": "GUNUNGSITOLI"
						},
						{
							"id": 376,
							"namakecamatan": "GUNUNG SITOLI"
						},
						{
							"id": 825,
							"namakecamatan": "GUNUNGSITOLI ALO'OA"
						},
						{
							"id": 394,
							"namakecamatan": "GUNUNGSITOLI ALO'OA"
						},
						{
							"id": 409,
							"namakecamatan": "GUNUNGSITOLI BARAT"
						},
						{
							"id": 826,
							"namakecamatan": "GUNUNGSITOLI BARAT"
						},
						{
							"id": 824,
							"namakecamatan": "GUNUNGSITOLI IDANOI"
						},
						{
							"id": 392,
							"namakecamatan": "GUNUNGSITOLI IDANOI/ IDANOI"
						},
						{
							"id": 390,
							"namakecamatan": "GUNUNGSITOLI SELATAN"
						},
						{
							"id": 822,
							"namakecamatan": "GUNUNGSITOLI SELATAN"
						},
						{
							"id": 823,
							"namakecamatan": "GUNUNGSITOLI UTARA"
						},
						{
							"id": 391,
							"namakecamatan": "GUNUNGSITOLI UTARA"
						},
						{
							"id": 1781,
							"namakecamatan": "GUNUNG SUGIH"
						},
						{
							"id": 5393,
							"namakecamatan": "GUNUNG TABUR"
						},
						{
							"id": 848,
							"namakecamatan": "GUNUNG TALANG"
						},
						{
							"id": 2424,
							"namakecamatan": "GUNUNG TANJUNG"
						},
						{
							"id": 1861,
							"namakecamatan": "GUNUNG TERANG"
						},
						{
							"id": 1981,
							"namakecamatan": "GUNUNG TERANG"
						},
						{
							"id": 5114,
							"namakecamatan": "GUNUNG TIMANG"
						},
						{
							"id": 1153,
							"namakecamatan": "GUNUNGTOAR"
						},
						{
							"id": 1204,
							"namakecamatan": "GUNUNG TUJUH"
						},
						{
							"id": 985,
							"namakecamatan": "GUNUNGTULEH"
						},
						{
							"id": 942,
							"namakecamatan": "GUNUNG TULEH"
						},
						{
							"id": 3197,
							"namakecamatan": "GUNUNGWUNGKAL"
						},
						{
							"id": 7505,
							"namakecamatan": "GUPURA"
						},
						{
							"id": 7159,
							"namakecamatan": "GURAGE"
						},
						{
							"id": 3595,
							"namakecamatan": "GURAH"
						},
						{
							"id": 587,
							"namakecamatan": "HABINSARAN"
						},
						{
							"id": 4739,
							"namakecamatan": "HAHARU"
						},
						{
							"id": 7426,
							"namakecamatan": "HAJU"
						},
						{
							"id": 5342,
							"namakecamatan": "HALONG"
						},
						{
							"id": 359,
							"namakecamatan": "HALONGONAN"
						},
						{
							"id": 715,
							"namakecamatan": "HALONGONAN"
						},
						{
							"id": 5231,
							"namakecamatan": "HAMPANG"
						},
						{
							"id": 474,
							"namakecamatan": "HAMPARAN PERAK"
						},
						{
							"id": 1330,
							"namakecamatan": "HAMPARAN RAWANG"
						},
						{
							"id": 1199,
							"namakecamatan": "HAMPARAN RAWANG"
						},
						{
							"id": 5138,
							"namakecamatan": "HANAU"
						},
						{
							"id": 5306,
							"namakecamatan": "HANTAKAN"
						},
						{
							"id": 2502,
							"namakecamatan": "HANTARA"
						},
						{
							"id": 498,
							"namakecamatan": "HARANGGAOL HORISON"
						},
						{
							"id": 924,
							"namakecamatan": "HARAU"
						},
						{
							"id": 597,
							"namakecamatan": "HARIAN"
						},
						{
							"id": 684,
							"namakecamatan": "HARIAN"
						},
						{
							"id": 2803,
							"namakecamatan": "HARJAMUKTI"
						},
						{
							"id": 5323,
							"namakecamatan": "HARUAI"
						},
						{
							"id": 5298,
							"namakecamatan": "HARUYAN"
						},
						{
							"id": 495,
							"namakecamatan": "HATONDUHAN"
						},
						{
							"id": 5286,
							"namakecamatan": "HATUNGUN"
						},
						{
							"id": 5316,
							"namakecamatan": "HAUR GADING"
						},
						{
							"id": 2601,
							"namakecamatan": "HAURGEULIS"
						},
						{
							"id": 2312,
							"namakecamatan": "HAURWANGI"
						},
						{
							"id": 4526,
							"namakecamatan": "HAWU MEHARA"
						},
						{
							"id": 4843,
							"namakecamatan": "HAWU MEHARA"
						},
						{
							"id": 7602,
							"namakecamatan": "HERAM"
						},
						{
							"id": 7316,
							"namakecamatan": "HEREAPINI"
						},
						{
							"id": 5951,
							"namakecamatan": "HERLANG"
						},
						{
							"id": 4676,
							"namakecamatan": "HEWOKLOANG"
						},
						{
							"id": 634,
							"namakecamatan": "HIBALA"
						},
						{
							"id": 380,
							"namakecamatan": "HILIDUHO"
						},
						{
							"id": 641,
							"namakecamatan": "HILIMEGAI"
						},
						{
							"id": 7336,
							"namakecamatan": "HILIPUK"
						},
						{
							"id": 858,
							"namakecamatan": "HILIRAN GUMANTI"
						},
						{
							"id": 653,
							"namakecamatan": "HILISALAWA'AHE"
						},
						{
							"id": 395,
							"namakecamatan": "HILISERANGKAI"
						},
						{
							"id": 420,
							"namakecamatan": "HINAI"
						},
						{
							"id": 7867,
							"namakecamatan": "HINGK"
						},
						{
							"id": 7686,
							"namakecamatan": "HINK"
						},
						{
							"id": 7191,
							"namakecamatan": "HITADIPA"
						},
						{
							"id": 7590,
							"namakecamatan": "HITADIPA"
						},
						{
							"id": 6741,
							"namakecamatan": "HOAT SORBAY"
						},
						{
							"id": 7653,
							"namakecamatan": "HOBARD"
						},
						{
							"id": 7303,
							"namakecamatan": "HOGIO"
						},
						{
							"id": 7323,
							"namakecamatan": "HOLUON"
						},
						{
							"id": 7181,
							"namakecamatan": "HOMEO"
						},
						{
							"id": 7586,
							"namakecamatan": "HOMEYO"
						},
						{
							"id": 7223,
							"namakecamatan": "HOYA"
						},
						{
							"id": 6800,
							"namakecamatan": "HUAMUAL"
						},
						{
							"id": 6717,
							"namakecamatan": "HUAMUAL BELAKANG"
						},
						{
							"id": 6796,
							"namakecamatan": "HUAMUAL BELAKANG"
						},
						{
							"id": 7038,
							"namakecamatan": "HUBIKIAK"
						},
						{
							"id": 7006,
							"namakecamatan": "HUBIKOSI"
						},
						{
							"id": 6625,
							"namakecamatan": "HULONTHALANGI"
						},
						{
							"id": 4975,
							"namakecamatan": "HULU GURUNG"
						},
						{
							"id": 4991,
							"namakecamatan": "HULU KAPUAS"
						},
						{
							"id": 1158,
							"namakecamatan": "HULU KUANTAN"
						},
						{
							"id": 1664,
							"namakecamatan": "HULU PALIK"
						},
						{
							"id": 721,
							"namakecamatan": "HULU SIHAPAS"
						},
						{
							"id": 4940,
							"namakecamatan": "HULU SUNGAI"
						},
						{
							"id": 1822,
							"namakecamatan": "HULU SUNGKAI"
						},
						{
							"id": 724,
							"namakecamatan": "HURISTAK"
						},
						{
							"id": 368,
							"namakecamatan": "HURISTAK"
						},
						{
							"id": 650,
							"namakecamatan": "HURUNA"
						},
						{
							"id": 626,
							"namakecamatan": "HUTA BARGOT"
						},
						{
							"id": 501,
							"namakecamatan": "HUTA BAYU RAJA"
						},
						{
							"id": 363,
							"namakecamatan": "HUTA RAJA TINGGI"
						},
						{
							"id": 726,
							"namakecamatan": "HUTA RAJA TINGGI"
						},
						{
							"id": 4465,
							"namakecamatan": "HU'U"
						},
						{
							"id": 7039,
							"namakecamatan": "IBELE"
						},
						{
							"id": 6849,
							"namakecamatan": "IBU"
						},
						{
							"id": 2349,
							"namakecamatan": "IBUN"
						},
						{
							"id": 6854,
							"namakecamatan": "IBU SELATAN"
						},
						{
							"id": 6853,
							"namakecamatan": "IBU UTARA"
						},
						{
							"id": 385,
							"namakecamatan": "IDANOGAWO"
						},
						{
							"id": 37,
							"namakecamatan": "IDI RAYEUK"
						},
						{
							"id": 57,
							"namakecamatan": "IDI TIMUR"
						},
						{
							"id": 48,
							"namakecamatan": "IDI TUNONG"
						},
						{
							"id": 7143,
							"namakecamatan": "ILAGA"
						},
						{
							"id": 7550,
							"namakecamatan": "ILAGA"
						},
						{
							"id": 7567,
							"namakecamatan": "ILAGA UTARA"
						},
						{
							"id": 7162,
							"namakecamatan": "ILAMBURAWI"
						},
						{
							"id": 4780,
							"namakecamatan": "ILE APE"
						},
						{
							"id": 4786,
							"namakecamatan": "ILE APE TIMUR"
						},
						{
							"id": 4652,
							"namakecamatan": "ILE BOLENG"
						},
						{
							"id": 4655,
							"namakecamatan": "ILE BURA"
						},
						{
							"id": 4643,
							"namakecamatan": "ILE MANDIRI"
						},
						{
							"id": 1579,
							"namakecamatan": "ILIR BARAT I"
						},
						{
							"id": 1576,
							"namakecamatan": "ILIR BARAT II"
						},
						{
							"id": 1699,
							"namakecamatan": "ILIR TALO"
						},
						{
							"id": 1580,
							"namakecamatan": "ILIR TIMUR I"
						},
						{
							"id": 1581,
							"namakecamatan": "ILIR TIMUR II"
						},
						{
							"id": 7144,
							"namakecamatan": "ILU"
						},
						{
							"id": 7473,
							"namakecamatan": "ILUGWA"
						},
						{
							"id": 7030,
							"namakecamatan": "ILUGWA"
						},
						{
							"id": 6994,
							"namakecamatan": "ILWAYAB"
						},
						{
							"id": 3441,
							"namakecamatan": "IMOGIRI"
						},
						{
							"id": 6798,
							"namakecamatan": "INAMOSOL"
						},
						{
							"id": 7707,
							"namakecamatan": "INANWATAN"
						},
						{
							"id": 2835,
							"namakecamatan": "INDIHIANG"
						},
						{
							"id": 242,
							"namakecamatan": "INDRA JAYA"
						},
						{
							"id": 121,
							"namakecamatan": "INDRA JAYA"
						},
						{
							"id": 1541,
							"namakecamatan": "INDRALAYA"
						},
						{
							"id": 1375,
							"namakecamatan": "INDRALAYA"
						},
						{
							"id": 1545,
							"namakecamatan": "INDRALAYA SELATAN"
						},
						{
							"id": 1544,
							"namakecamatan": "INDRALAYA UTARA"
						},
						{
							"id": 47,
							"namakecamatan": "INDRA MAKMU"
						},
						{
							"id": 2615,
							"namakecamatan": "INDRAMAYU"
						},
						{
							"id": 94,
							"namakecamatan": "INDRAPURI"
						},
						{
							"id": 4720,
							"namakecamatan": "INERIE"
						},
						{
							"id": 7395,
							"namakecamatan": "INGGERUS"
						},
						{
							"id": 101,
							"namakecamatan": "INGIN JAYA"
						},
						{
							"id": 7537,
							"namakecamatan": "INIKGAL"
						},
						{
							"id": 7409,
							"namakecamatan": "INIYANDIT"
						},
						{
							"id": 7538,
							"namakecamatan": "INIYE"
						},
						{
							"id": 4582,
							"namakecamatan": "INSANA"
						},
						{
							"id": 4594,
							"namakecamatan": "INSANA BARAT"
						},
						{
							"id": 4593,
							"namakecamatan": "INSANA FAFINESU"
						},
						{
							"id": 4595,
							"namakecamatan": "INSANA TENGAH"
						},
						{
							"id": 4583,
							"namakecamatan": "INSANA UTARA"
						},
						{
							"id": 1157,
							"namakecamatan": "INUMAN"
						},
						{
							"id": 4617,
							"namakecamatan": "IO KUFEU"
						},
						{
							"id": 4850,
							"namakecamatan": "IO KUFEU"
						},
						{
							"id": 1704,
							"namakecamatan": "IPUH"
						},
						{
							"id": 7815,
							"namakecamatan": "IRERES"
						},
						{
							"id": 7160,
							"namakecamatan": "IRIMULI"
						},
						{
							"id": 7041,
							"namakecamatan": "ITLAY HISAGE"
						},
						{
							"id": 831,
							"namakecamatan": "IV JURAI"
						},
						{
							"id": 908,
							"namakecamatan": "IV KOTO"
						},
						{
							"id": 895,
							"namakecamatan": "IV KOTO AUR MALINTANG"
						},
						{
							"id": 865,
							"namakecamatan": "IV NAGARI"
						},
						{
							"id": 838,
							"namakecamatan": "IV NAGARI BAYANGUTARA"
						},
						{
							"id": 7224,
							"namakecamatan": "IWAKA"
						},
						{
							"id": 6289,
							"namakecamatan": "IWOIMENDAA"
						},
						{
							"id": 7257,
							"namakecamatan": "IWUR"
						},
						{
							"id": 850,
							"namakecamatan": "IX KOTO SUNGAI LASI"
						},
						{
							"id": 5180,
							"namakecamatan": "JABIREN"
						},
						{
							"id": 3813,
							"namakecamatan": "JABON"
						},
						{
							"id": 3628,
							"namakecamatan": "JABUNG"
						},
						{
							"id": 1915,
							"namakecamatan": "JABUNG"
						},
						{
							"id": 2183,
							"namakecamatan": "JAGAKARSA"
						},
						{
							"id": 6981,
							"namakecamatan": "JAGEBOB"
						},
						{
							"id": 4999,
							"namakecamatan": "JAGOI BABANG"
						},
						{
							"id": 77,
							"namakecamatan": "JAGONG JEGET"
						},
						{
							"id": 6847,
							"namakecamatan": "JAILOLO"
						},
						{
							"id": 6851,
							"namakecamatan": "JAILOLO SELATAN"
						},
						{
							"id": 6852,
							"namakecamatan": "JAILOLO TIMUR"
						},
						{
							"id": 7406,
							"namakecamatan": "JAIR"
						},
						{
							"id": 3186,
							"namakecamatan": "JAKEN"
						},
						{
							"id": 3189,
							"namakecamatan": "JAKENAN"
						},
						{
							"id": 2488,
							"namakecamatan": "JALAKSANA"
						},
						{
							"id": 2643,
							"namakecamatan": "JALANCAGAK"
						},
						{
							"id": 2436,
							"namakecamatan": "JAMANIS"
						},
						{
							"id": 4150,
							"namakecamatan": "JAMBANGAN"
						},
						{
							"id": 4228,
							"namakecamatan": "JAMBE"
						},
						{
							"id": 3743,
							"namakecamatan": "JAMBESARI DARUSSHOLAH"
						},
						{
							"id": 1253,
							"namakecamatan": "JAMBI LUAR KOTA"
						},
						{
							"id": 1318,
							"namakecamatan": "JAMBI SELATAN"
						},
						{
							"id": 1319,
							"namakecamatan": "JAMBI TIMUR"
						},
						{
							"id": 2548,
							"namakecamatan": "JAMBLANG"
						},
						{
							"id": 3529,
							"namakecamatan": "JAMBON"
						},
						{
							"id": 3248,
							"namakecamatan": "JAMBU"
						},
						{
							"id": 2255,
							"namakecamatan": "JAMPANG KULON"
						},
						{
							"id": 2242,
							"namakecamatan": "JAMPANG TENGAH"
						},
						{
							"id": 4408,
							"namakecamatan": "JANAPRIA"
						},
						{
							"id": 208,
							"namakecamatan": "JANGKA"
						},
						{
							"id": 279,
							"namakecamatan": "JANGKA BUAYA"
						},
						{
							"id": 142,
							"namakecamatan": "JANGKA BUYA"
						},
						{
							"id": 4903,
							"namakecamatan": "JANGKANG"
						},
						{
							"id": 3755,
							"namakecamatan": "JANGKAR"
						},
						{
							"id": 1211,
							"namakecamatan": "JANGKAT"
						},
						{
							"id": 1228,
							"namakecamatan": "JANGKAT TIMUR"
						},
						{
							"id": 3166,
							"namakecamatan": "JAPAH"
						},
						{
							"id": 2499,
							"namakecamatan": "JAPARA"
						},
						{
							"id": 1421,
							"namakecamatan": "JARAI"
						},
						{
							"id": 5329,
							"namakecamatan": "JARO"
						},
						{
							"id": 2213,
							"namakecamatan": "JASINGA"
						},
						{
							"id": 3105,
							"namakecamatan": "JATEN"
						},
						{
							"id": 3204,
							"namakecamatan": "JATI"
						},
						{
							"id": 3151,
							"namakecamatan": "JATI"
						},
						{
							"id": 1766,
							"namakecamatan": "JATI AGUNG"
						},
						{
							"id": 2814,
							"namakecamatan": "JATIASIH"
						},
						{
							"id": 3744,
							"namakecamatan": "JATIBANTENG"
						},
						{
							"id": 3373,
							"namakecamatan": "JATIBARANG"
						},
						{
							"id": 2613,
							"namakecamatan": "JATIBARANG"
						},
						{
							"id": 2600,
							"namakecamatan": "JATIGEDE"
						},
						{
							"id": 3885,
							"namakecamatan": "JATIKALEN"
						},
						{
							"id": 2872,
							"namakecamatan": "JATILAWANG"
						},
						{
							"id": 2664,
							"namakecamatan": "JATILUHUR"
						},
						{
							"id": 2452,
							"namakecamatan": "JATINAGARA"
						},
						{
							"id": 2589,
							"namakecamatan": "JATINANGOR"
						},
						{
							"id": 2187,
							"namakecamatan": "JATINEGARA"
						},
						{
							"id": 3355,
							"namakecamatan": "JATINEGARA"
						},
						{
							"id": 3051,
							"namakecamatan": "JATINOM"
						},
						{
							"id": 2576,
							"namakecamatan": "JATINUNGGAL"
						},
						{
							"id": 3090,
							"namakecamatan": "JATIPURNO"
						},
						{
							"id": 3095,
							"namakecamatan": "JATIPURO"
						},
						{
							"id": 3827,
							"namakecamatan": "JATIREJO"
						},
						{
							"id": 3967,
							"namakecamatan": "JATIROGO"
						},
						{
							"id": 3661,
							"namakecamatan": "JATIROTO"
						},
						{
							"id": 3084,
							"namakecamatan": "JATIROTO"
						},
						{
							"id": 2692,
							"namakecamatan": "JATISARI"
						},
						{
							"id": 2815,
							"namakecamatan": "JATI SEMPURNA"
						},
						{
							"id": 3089,
							"namakecamatan": "JATISRONO"
						},
						{
							"id": 2563,
							"namakecamatan": "JATITUJUH"
						},
						{
							"id": 4297,
							"namakecamatan": "JATIUWUNG"
						},
						{
							"id": 2559,
							"namakecamatan": "JATIWANGI"
						},
						{
							"id": 2420,
							"namakecamatan": "JATIWARAS"
						},
						{
							"id": 3096,
							"namakecamatan": "JATIYOSO"
						},
						{
							"id": 4865,
							"namakecamatan": "JAWAI"
						},
						{
							"id": 4878,
							"namakecamatan": "JAWAI SELATAN"
						},
						{
							"id": 502,
							"namakecamatan": "JAWA MARAJA BAHJAMBI"
						},
						{
							"id": 4286,
							"namakecamatan": "JAWILAN"
						},
						{
							"id": 240,
							"namakecamatan": "JAYA"
						},
						{
							"id": 292,
							"namakecamatan": "JAYA BARU"
						},
						{
							"id": 2700,
							"namakecamatan": "JAYAKERTA"
						},
						{
							"id": 1452,
							"namakecamatan": "JAYALOKA"
						},
						{
							"id": 4226,
							"namakecamatan": "JAYANTI"
						},
						{
							"id": 1514,
							"namakecamatan": "JAYAPURA"
						},
						{
							"id": 7599,
							"namakecamatan": "JAYAPURA SELATAN"
						},
						{
							"id": 7598,
							"namakecamatan": "JAYAPURA UTARA"
						},
						{
							"id": 3390,
							"namakecamatan": "JEBRES"
						},
						{
							"id": 2051,
							"namakecamatan": "JEBUS"
						},
						{
							"id": 5274,
							"namakecamatan": "JEJANGKIT"
						},
						{
							"id": 1383,
							"namakecamatan": "JEJAWI"
						},
						{
							"id": 5204,
							"namakecamatan": "JEKAN RAYA"
						},
						{
							"id": 3207,
							"namakecamatan": "JEKULO"
						},
						{
							"id": 5150,
							"namakecamatan": "JELAI"
						},
						{
							"id": 4935,
							"namakecamatan": "JELAI HULU"
						},
						{
							"id": 3690,
							"namakecamatan": "JELBUK"
						},
						{
							"id": 5020,
							"namakecamatan": "JELIMPO"
						},
						{
							"id": 1324,
							"namakecamatan": "JELUTUNG"
						},
						{
							"id": 2133,
							"namakecamatan": "JEMAJA"
						},
						{
							"id": 2096,
							"namakecamatan": "JEMAJA"
						},
						{
							"id": 2107,
							"namakecamatan": "JEMAJA TIMUR"
						},
						{
							"id": 2132,
							"namakecamatan": "JEMAJA TIMUR"
						},
						{
							"id": 4334,
							"namakecamatan": "JEMBRANA"
						},
						{
							"id": 5411,
							"namakecamatan": "JEMPANG"
						},
						{
							"id": 5107,
							"namakecamatan": "JENAMAS"
						},
						{
							"id": 3527,
							"namakecamatan": "JENANGAN"
						},
						{
							"id": 3131,
							"namakecamatan": "JENAR"
						},
						{
							"id": 3111,
							"namakecamatan": "JENAWI"
						},
						{
							"id": 3681,
							"namakecamatan": "JENGGAWAH"
						},
						{
							"id": 3977,
							"namakecamatan": "JENU"
						},
						{
							"id": 3216,
							"namakecamatan": "JEPARA"
						},
						{
							"id": 3158,
							"namakecamatan": "JEPON"
						},
						{
							"id": 4712,
							"namakecamatan": "JEREBUU"
						},
						{
							"id": 4489,
							"namakecamatan": "JEREWEH"
						},
						{
							"id": 4434,
							"namakecamatan": "JEREWEH"
						},
						{
							"id": 4433,
							"namakecamatan": "JEROWARU"
						},
						{
							"id": 2853,
							"namakecamatan": "JERUKLEGI"
						},
						{
							"id": 7283,
							"namakecamatan": "JETFA"
						},
						{
							"id": 3485,
							"namakecamatan": "JETIS"
						},
						{
							"id": 3518,
							"namakecamatan": "JETIS"
						},
						{
							"id": 3440,
							"namakecamatan": "JETIS"
						},
						{
							"id": 3842,
							"namakecamatan": "JETIS"
						},
						{
							"id": 7449,
							"namakecamatan": "JETSY"
						},
						{
							"id": 202,
							"namakecamatan": "JEUMPA"
						},
						{
							"id": 223,
							"namakecamatan": "JEUMPA"
						},
						{
							"id": 200,
							"namakecamatan": "JEUNIEB"
						},
						{
							"id": 3157,
							"namakecamatan": "JIKEN"
						},
						{
							"id": 7215,
							"namakecamatan": "JILA"
						},
						{
							"id": 4177,
							"namakecamatan": "JIPUT"
						},
						{
							"id": 7214,
							"namakecamatan": "JITA"
						},
						{
							"id": 3894,
							"namakecamatan": "JIWAN"
						},
						{
							"id": 7447,
							"namakecamatan": "JOERAT"
						},
						{
							"id": 3039,
							"namakecamatan": "JOGONALAN"
						},
						{
							"id": 3921,
							"namakecamatan": "JOGOROGO"
						},
						{
							"id": 3863,
							"namakecamatan": "JOGOROTO"
						},
						{
							"id": 80,
							"namakecamatan": "JOHAN PAHWALAN"
						},
						{
							"id": 2160,
							"namakecamatan": "JOHAR BARU"
						},
						{
							"id": 4313,
							"namakecamatan": "JOMBANG"
						},
						{
							"id": 3853,
							"namakecamatan": "JOMBANG"
						},
						{
							"id": 3666,
							"namakecamatan": "JOMBANG"
						},
						{
							"id": 4403,
							"namakecamatan": "JONGGAT"
						},
						{
							"id": 2200,
							"namakecamatan": "JONGGOL"
						},
						{
							"id": 4974,
							"namakecamatan": "JONGKONG"
						},
						{
							"id": 489,
							"namakecamatan": "JORLANG HATARAN"
						},
						{
							"id": 5208,
							"namakecamatan": "JORONG"
						},
						{
							"id": 4055,
							"namakecamatan": "JRENGIK"
						},
						{
							"id": 5341,
							"namakecamatan": "JUAI"
						},
						{
							"id": 440,
							"namakecamatan": "JUHAR"
						},
						{
							"id": 1291,
							"namakecamatan": "JUJUHAN"
						},
						{
							"id": 1303,
							"namakecamatan": "JUJUHAN ILIR"
						},
						{
							"id": 207,
							"namakecamatan": "JULI"
						},
						{
							"id": 36,
							"namakecamatan": "JULOK"
						},
						{
							"id": 3098,
							"namakecamatan": "JUMANTONO"
						},
						{
							"id": 3097,
							"namakecamatan": "JUMAPOLO"
						},
						{
							"id": 3270,
							"namakecamatan": "JUMO"
						},
						{
							"id": 854,
							"namakecamatan": "JUNJUNG SIRIH"
						},
						{
							"id": 4161,
							"namakecamatan": "JUNREJO"
						},
						{
							"id": 2611,
							"namakecamatan": "JUNTINYUAT"
						},
						{
							"id": 3188,
							"namakecamatan": "JUWANA"
						},
						{
							"id": 3031,
							"namakecamatan": "JUWANGI"
						},
						{
							"id": 3045,
							"namakecamatan": "JUWIRING"
						},
						{
							"id": 6422,
							"namakecamatan": "KABAENA"
						},
						{
							"id": 6380,
							"namakecamatan": "KABAENA"
						},
						{
							"id": 6431,
							"namakecamatan": "KABAENA BARAT"
						},
						{
							"id": 6430,
							"namakecamatan": "KABAENA SELATAN"
						},
						{
							"id": 6433,
							"namakecamatan": "KABAENA TENGAH"
						},
						{
							"id": 6423,
							"namakecamatan": "KABAENA TIMUR"
						},
						{
							"id": 6381,
							"namakecamatan": "KABAENA TIMUR"
						},
						{
							"id": 6432,
							"namakecamatan": "KABAENA UTARA"
						},
						{
							"id": 2253,
							"namakecamatan": "KABANDUNGAN"
						},
						{
							"id": 6348,
							"namakecamatan": "KABANGKA"
						},
						{
							"id": 434,
							"namakecamatan": "KABANJAHE"
						},
						{
							"id": 5608,
							"namakecamatan": "KABARUAN"
						},
						{
							"id": 3710,
							"namakecamatan": "KABAT"
						},
						{
							"id": 6349,
							"namakecamatan": "KABAWO"
						},
						{
							"id": 7333,
							"namakecamatan": "KABIANGGAMA"
						},
						{
							"id": 6576,
							"namakecamatan": "KABILA"
						},
						{
							"id": 6582,
							"namakecamatan": "KABILA BONE"
						},
						{
							"id": 4632,
							"namakecamatan": "KABOLA"
						},
						{
							"id": 3860,
							"namakecamatan": "KABUH"
						},
						{
							"id": 1113,
							"namakecamatan": "KABUN"
						},
						{
							"id": 6525,
							"namakecamatan": "KADATUA"
						},
						{
							"id": 6383,
							"namakecamatan": "KADATUA"
						},
						{
							"id": 3567,
							"namakecamatan": "KADEMANGAN"
						},
						{
							"id": 4114,
							"namakecamatan": "KADEMANGAN"
						},
						{
							"id": 6533,
							"namakecamatan": "KADIA"
						},
						{
							"id": 2561,
							"namakecamatan": "KADIPATEN"
						},
						{
							"id": 2438,
							"namakecamatan": "KADIPATEN"
						},
						{
							"id": 2264,
							"namakecamatan": "KADUDAMPIT"
						},
						{
							"id": 2477,
							"namakecamatan": "KADUGEDE"
						},
						{
							"id": 4180,
							"namakecamatan": "KADUHEJO"
						},
						{
							"id": 2369,
							"namakecamatan": "KADUNGORA"
						},
						{
							"id": 2298,
							"namakecamatan": "KADUPANDAK"
						},
						{
							"id": 4074,
							"namakecamatan": "KADUR"
						},
						{
							"id": 4751,
							"namakecamatan": "KAHAUNGU ETI"
						},
						{
							"id": 5178,
							"namakecamatan": "KAHAYAN HILIR"
						},
						{
							"id": 5165,
							"namakecamatan": "KAHAYAN HULU UTARA"
						},
						{
							"id": 5175,
							"namakecamatan": "KAHAYAN KUALA"
						},
						{
							"id": 5176,
							"namakecamatan": "KAHAYAN TENGAH"
						},
						{
							"id": 6013,
							"namakecamatan": "KAHU"
						},
						{
							"id": 7382,
							"namakecamatan": "KAI"
						},
						{
							"id": 7428,
							"namakecamatan": "KAIBAR"
						},
						{
							"id": 5519,
							"namakecamatan": "KAIDIPANG"
						},
						{
							"id": 5671,
							"namakecamatan": "KAIDIPANG"
						},
						{
							"id": 7792,
							"namakecamatan": "KAIMANA"
						},
						{
							"id": 6793,
							"namakecamatan": "KAIRATU"
						},
						{
							"id": 6702,
							"namakecamatan": "KAIRATU"
						},
						{
							"id": 6799,
							"namakecamatan": "KAIRATU BARAT"
						},
						{
							"id": 7717,
							"namakecamatan": "KAIS"
						},
						{
							"id": 7729,
							"namakecamatan": "KAIS DARAT"
						},
						{
							"id": 7253,
							"namakecamatan": "KAISENAR"
						},
						{
							"id": 7769,
							"namakecamatan": "KAITARO"
						},
						{
							"id": 5952,
							"namakecamatan": "KAJANG"
						},
						{
							"id": 3323,
							"namakecamatan": "KAJEN"
						},
						{
							"id": 3003,
							"namakecamatan": "KAJORAN"
						},
						{
							"id": 6014,
							"namakecamatan": "KAJUARA"
						},
						{
							"id": 5558,
							"namakecamatan": "KAKAS"
						},
						{
							"id": 5572,
							"namakecamatan": "KAKAS BARAT"
						},
						{
							"id": 4603,
							"namakecamatan": "KAKULUK MESAK"
						},
						{
							"id": 6213,
							"namakecamatan": "KALAENA"
						},
						{
							"id": 4220,
							"namakecamatan": "KALANGANYAR"
						},
						{
							"id": 2252,
							"namakecamatan": "KALAPANUNGGAL"
						},
						{
							"id": 3476,
							"namakecamatan": "KALASAN"
						},
						{
							"id": 5652,
							"namakecamatan": "KALAWAT"
						},
						{
							"id": 6377,
							"namakecamatan": "KALEDUPA"
						},
						{
							"id": 6441,
							"namakecamatan": "KALEDUPA"
						},
						{
							"id": 6445,
							"namakecamatan": "KALEDUPA SELATAN"
						},
						{
							"id": 1759,
							"namakecamatan": "KALIANDA"
						},
						{
							"id": 4077,
							"namakecamatan": "KALIANGET"
						},
						{
							"id": 3004,
							"namakecamatan": "KALIANGKRIK"
						},
						{
							"id": 2879,
							"namakecamatan": "KALIBAGOR"
						},
						{
							"id": 3707,
							"namakecamatan": "KALIBARU"
						},
						{
							"id": 2991,
							"namakecamatan": "KALIBAWANG"
						},
						{
							"id": 3431,
							"namakecamatan": "KALIBAWANG"
						},
						{
							"id": 2932,
							"namakecamatan": "KALIBENING"
						},
						{
							"id": 2257,
							"namakecamatan": "KALIBUNDER"
						},
						{
							"id": 3558,
							"namakecamatan": "KALIDAWIR"
						},
						{
							"id": 2172,
							"namakecamatan": "KALIDERES"
						},
						{
							"id": 1585,
							"namakecamatan": "KALIDONI"
						},
						{
							"id": 2965,
							"namakecamatan": "KALIGESING"
						},
						{
							"id": 2900,
							"namakecamatan": "KALIGONDANG"
						},
						{
							"id": 3112,
							"namakecamatan": "KALIJAMBE"
						},
						{
							"id": 2635,
							"namakecamatan": "KALIJATI"
						},
						{
							"id": 2983,
							"namakecamatan": "KALIKAJAR"
						},
						{
							"id": 3054,
							"namakecamatan": "KALIKOTES"
						},
						{
							"id": 2902,
							"namakecamatan": "KALIMANAH"
						},
						{
							"id": 2503,
							"namakecamatan": "KALIMANGGIS"
						},
						{
							"id": 3223,
							"namakecamatan": "KALINYAMATAN"
						},
						{
							"id": 5431,
							"namakecamatan": "KALIORANG"
						},
						{
							"id": 3175,
							"namakecamatan": "KALIORI"
						},
						{
							"id": 3622,
							"namakecamatan": "KALIPARE"
						},
						{
							"id": 2461,
							"namakecamatan": "KALIPUCANG"
						},
						{
							"id": 2755,
							"namakecamatan": "KALIPUCANG"
						},
						{
							"id": 3717,
							"namakecamatan": "KALIPURO"
						},
						{
							"id": 1778,
							"namakecamatan": "KALIREJO"
						},
						{
							"id": 4985,
							"namakecamatan": "KALIS"
						},
						{
							"id": 3692,
							"namakecamatan": "KALISAT"
						},
						{
							"id": 4005,
							"namakecamatan": "KALITENGAH"
						},
						{
							"id": 3953,
							"namakecamatan": "KALITIDU"
						},
						{
							"id": 3684,
							"namakecamatan": "KALIWATES"
						},
						{
							"id": 2537,
							"namakecamatan": "KALIWEDI"
						},
						{
							"id": 2980,
							"namakecamatan": "KALIWIRO"
						},
						{
							"id": 3288,
							"namakecamatan": "KALIWUNGU"
						},
						{
							"id": 3257,
							"namakecamatan": "KALIWUNGU"
						},
						{
							"id": 3202,
							"namakecamatan": "KALIWUNGU"
						},
						{
							"id": 3300,
							"namakecamatan": "KALIWUNGU SELATAN"
						},
						{
							"id": 7266,
							"namakecamatan": "KALOMDOL"
						},
						{
							"id": 7167,
							"namakecamatan": "KALOME"
						},
						{
							"id": 5614,
							"namakecamatan": "KALONGAN"
						},
						{
							"id": 3265,
							"namakecamatan": "KALORAN"
						},
						{
							"id": 6640,
							"namakecamatan": "KALUKKU"
						},
						{
							"id": 6641,
							"namakecamatan": "KALUMPANG"
						},
						{
							"id": 5295,
							"namakecamatan": "KALUMPANG"
						},
						{
							"id": 4034,
							"namakecamatan": "KAMAL"
						},
						{
							"id": 866,
							"namakecamatan": "KAMANG BARU"
						},
						{
							"id": 918,
							"namakecamatan": "KAMANG MAGEK"
						},
						{
							"id": 6142,
							"namakecamatan": "KAMANRE"
						},
						{
							"id": 4754,
							"namakecamatan": "KAMBATAMAPAMBUHANG"
						},
						{
							"id": 4753,
							"namakecamatan": "KAMBERA"
						},
						{
							"id": 7368,
							"namakecamatan": "KAMBONERI"
						},
						{
							"id": 6477,
							"namakecamatan": "KAMBOWA"
						},
						{
							"id": 6354,
							"namakecamatan": "KAMBOWA"
						},
						{
							"id": 7796,
							"namakecamatan": "KAMBRAU"
						},
						{
							"id": 6535,
							"namakecamatan": "KAMBU"
						},
						{
							"id": 5122,
							"namakecamatan": "KAMIPANG"
						},
						{
							"id": 3537,
							"namakecamatan": "KAMPAK"
						},
						{
							"id": 1023,
							"namakecamatan": "KAMPAR"
						},
						{
							"id": 1028,
							"namakecamatan": "KAMPAR KIRI"
						},
						{
							"id": 1029,
							"namakecamatan": "KAMPAR KIRI HILIR"
						},
						{
							"id": 1030,
							"namakecamatan": "KAMPAR KIRI HULU"
						},
						{
							"id": 1040,
							"namakecamatan": "KAMPAR KIRI TENGAH"
						},
						{
							"id": 1038,
							"namakecamatan": "KAMPAR TIMUR"
						},
						{
							"id": 1039,
							"namakecamatan": "KAMPAR UTARA"
						},
						{
							"id": 2869,
							"namakecamatan": "KAMPUNG LAUT"
						},
						{
							"id": 1749,
							"namakecamatan": "KAMPUNG MELAYU"
						},
						{
							"id": 556,
							"namakecamatan": "KAMPUNG RAKYAT"
						},
						{
							"id": 735,
							"namakecamatan": "KAMPUNG RAKYAT"
						},
						{
							"id": 7575,
							"namakecamatan": "KAMU"
						},
						{
							"id": 7085,
							"namakecamatan": "KAMU"
						},
						{
							"id": 7774,
							"namakecamatan": "KAMUNDAN"
						},
						{
							"id": 7581,
							"namakecamatan": "KAMU SELATAN"
						},
						{
							"id": 7095,
							"namakecamatan": "KAMU SELATAN"
						},
						{
							"id": 7582,
							"namakecamatan": "KAMU TIMUR"
						},
						{
							"id": 7099,
							"namakecamatan": "KAMU TIMUR"
						},
						{
							"id": 7578,
							"namakecamatan": "KAMU UTARA"
						},
						{
							"id": 7089,
							"namakecamatan": "KAMU UTARA"
						},
						{
							"id": 4757,
							"namakecamatan": "KANATANG"
						},
						{
							"id": 3604,
							"namakecamatan": "KANDANGAN"
						},
						{
							"id": 3266,
							"namakecamatan": "KANDANGAN"
						},
						{
							"id": 5291,
							"namakecamatan": "KANDANGAN"
						},
						{
							"id": 2621,
							"namakecamatan": "KANDANGHAUR"
						},
						{
							"id": 3316,
							"namakecamatan": "KANDANGSERANG"
						},
						{
							"id": 3590,
							"namakecamatan": "KANDAT"
						},
						{
							"id": 3313,
							"namakecamatan": "KANDEMAN"
						},
						{
							"id": 1142,
							"namakecamatan": "KANDIS"
						},
						{
							"id": 1550,
							"namakecamatan": "KANDIS"
						},
						{
							"id": 4677,
							"namakecamatan": "KANGAE"
						},
						{
							"id": 4102,
							"namakecamatan": "KANGAYAN"
						},
						{
							"id": 7343,
							"namakecamatan": "KANGGIME"
						},
						{
							"id": 3297,
							"namakecamatan": "KANGKUNG"
						},
						{
							"id": 4117,
							"namakecamatan": "KANIGARAN"
						},
						{
							"id": 3573,
							"namakecamatan": "KANIGORO"
						},
						{
							"id": 3948,
							"namakecamatan": "KANOR"
						},
						{
							"id": 6872,
							"namakecamatan": "KAO"
						},
						{
							"id": 6886,
							"namakecamatan": "KAO BARAT"
						},
						{
							"id": 6887,
							"namakecamatan": "KAO TELUK"
						},
						{
							"id": 6885,
							"namakecamatan": "KAO UTARA"
						},
						{
							"id": 6234,
							"namakecamatan": "KAPALA PITU"
						},
						{
							"id": 6190,
							"namakecamatan": "KAPALLA PITU"
						},
						{
							"id": 3951,
							"namakecamatan": "KAPAS"
						},
						{
							"id": 2530,
							"namakecamatan": "KAPETAKAN"
						},
						{
							"id": 7597,
							"namakecamatan": "KAPIRAYA"
						},
						{
							"id": 7190,
							"namakecamatan": "KAPIRAYA"
						},
						{
							"id": 6322,
							"namakecamatan": "KAPOIALA"
						},
						{
							"id": 3753,
							"namakecamatan": "KAPONGAN"
						},
						{
							"id": 6386,
							"namakecamatan": "KAPONTORI"
						},
						{
							"id": 6991,
							"namakecamatan": "KAPTEL"
						},
						{
							"id": 4900,
							"namakecamatan": "KAPUAS"
						},
						{
							"id": 5094,
							"namakecamatan": "KAPUAS BARAT"
						},
						{
							"id": 5091,
							"namakecamatan": "KAPUAS HILIR"
						},
						{
							"id": 5101,
							"namakecamatan": "KAPUAS HULU"
						},
						{
							"id": 5093,
							"namakecamatan": "KAPUAS KUALA"
						},
						{
							"id": 5096,
							"namakecamatan": "KAPUAS MURUNG"
						},
						{
							"id": 5100,
							"namakecamatan": "KAPUAS TENGAH"
						},
						{
							"id": 5092,
							"namakecamatan": "KAPUAS TIMUR"
						},
						{
							"id": 926,
							"namakecamatan": "KAPUR IX"
						},
						{
							"id": 5821,
							"namakecamatan": "KARAMAT"
						},
						{
							"id": 2610,
							"namakecamatan": "KARANGAMPEL"
						},
						{
							"id": 5437,
							"namakecamatan": "KARANGAN"
						},
						{
							"id": 3536,
							"namakecamatan": "KARANGAN"
						},
						{
							"id": 3049,
							"namakecamatan": "KARANGANOM"
						},
						{
							"id": 2907,
							"namakecamatan": "KARANGANYAR"
						},
						{
							"id": 2954,
							"namakecamatan": "KARANGANYAR"
						},
						{
							"id": 3235,
							"namakecamatan": "KARANGANYAR"
						},
						{
							"id": 3935,
							"namakecamatan": "KARANGANYAR"
						},
						{
							"id": 3322,
							"namakecamatan": "KARANGANYAR"
						},
						{
							"id": 3103,
							"namakecamatan": "KARANGANYAR"
						},
						{
							"id": 4369,
							"namakecamatan": "KARANGASEM"
						},
						{
							"id": 3228,
							"namakecamatan": "KARANGAWEN"
						},
						{
							"id": 2718,
							"namakecamatan": "KARANG BAHAGIA"
						},
						{
							"id": 257,
							"namakecamatan": "KARANG BARU"
						},
						{
							"id": 4009,
							"namakecamatan": "KARANGBINANGUN"
						},
						{
							"id": 5337,
							"namakecamatan": "KARANG BINTANG"
						},
						{
							"id": 3333,
							"namakecamatan": "KARANGDADAP"
						},
						{
							"id": 1461,
							"namakecamatan": "KARANG DAPO"
						},
						{
							"id": 1573,
							"namakecamatan": "KARANG DAPO"
						},
						{
							"id": 3044,
							"namakecamatan": "KARANGDOWO"
						},
						{
							"id": 2955,
							"namakecamatan": "KARANGGAYAM"
						},
						{
							"id": 3026,
							"namakecamatan": "KARANGGEDE"
						},
						{
							"id": 4003,
							"namakecamatan": "KARANGGENENG"
						},
						{
							"id": 5244,
							"namakecamatan": "KARANG INTAN"
						},
						{
							"id": 2913,
							"namakecamatan": "KARANGJAMBU"
						},
						{
							"id": 3925,
							"namakecamatan": "KARANGJATI"
						},
						{
							"id": 2422,
							"namakecamatan": "KARANG JAYA"
						},
						{
							"id": 1459,
							"namakecamatan": "KARANG JAYA"
						},
						{
							"id": 1574,
							"namakecamatan": "KARANG JAYA"
						},
						{
							"id": 2505,
							"namakecamatan": "KARANG KANCANA"
						},
						{
							"id": 2927,
							"namakecamatan": "KARANGKOBAR"
						},
						{
							"id": 2887,
							"namakecamatan": "KARANGLEWAS"
						},
						{
							"id": 3120,
							"namakecamatan": "KARANGMALANG"
						},
						{
							"id": 3457,
							"namakecamatan": "KARANGMOJO"
						},
						{
							"id": 2908,
							"namakecamatan": "KARANGMONCOL"
						},
						{
							"id": 3041,
							"namakecamatan": "KARANGNONGKO"
						},
						{
							"id": 2403,
							"namakecamatan": "KARANGNUNGGAL"
						},
						{
							"id": 3102,
							"namakecamatan": "KARANGPANDAN"
						},
						{
							"id": 2361,
							"namakecamatan": "KARANGPAWITAN"
						},
						{
							"id": 4062,
							"namakecamatan": "KARANGPENANG"
						},
						{
							"id": 4128,
							"namakecamatan": "KARANGPILANG"
						},
						{
							"id": 3634,
							"namakecamatan": "KARANG PLOSO"
						},
						{
							"id": 2857,
							"namakecamatan": "KARANGPUCUNG"
						},
						{
							"id": 3133,
							"namakecamatan": "KARANGRAYUNG"
						},
						{
							"id": 2906,
							"namakecamatan": "KARANGREJA"
						},
						{
							"id": 3913,
							"namakecamatan": "KARANGREJO"
						},
						{
							"id": 3552,
							"namakecamatan": "KARANGREJO"
						},
						{
							"id": 2960,
							"namakecamatan": "KARANGSAMBUNG"
						},
						{
							"id": 2514,
							"namakecamatan": "KARANGSEMBUNG"
						},
						{
							"id": 4186,
							"namakecamatan": "KARANG TANJUNG"
						},
						{
							"id": 3092,
							"namakecamatan": "KARANGTENGAH"
						},
						{
							"id": 2375,
							"namakecamatan": "KARANGTENGAH"
						},
						{
							"id": 2288,
							"namakecamatan": "KARANGTENGAH"
						},
						{
							"id": 3231,
							"namakecamatan": "KARANGTENGAH"
						},
						{
							"id": 4307,
							"namakecamatan": "KARANG TENGAH"
						},
						{
							"id": 1648,
							"namakecamatan": "KARANG TINGGI"
						},
						{
							"id": 1735,
							"namakecamatan": "KARANG TINGGI"
						},
						{
							"id": 2542,
							"namakecamatan": "KARANGWARENG"
						},
						{
							"id": 7692,
							"namakecamatan": "KARAS"
						},
						{
							"id": 3914,
							"namakecamatan": "KARAS"
						},
						{
							"id": 5109,
							"namakecamatan": "KARAU KUALA"
						},
						{
							"id": 4302,
							"namakecamatan": "KARAWACI"
						},
						{
							"id": 2679,
							"namakecamatan": "KARAWANG BARAT"
						},
						{
							"id": 2704,
							"namakecamatan": "KARAWANG TIMUR"
						},
						{
							"id": 3890,
							"namakecamatan": "KARE"
						},
						{
							"id": 4750,
							"namakecamatan": "KARERA"
						},
						{
							"id": 2086,
							"namakecamatan": "KARIMUN"
						},
						{
							"id": 3220,
							"namakecamatan": "KARIMUN JAWA"
						},
						{
							"id": 6647,
							"namakecamatan": "KAROSSA"
						},
						{
							"id": 6699,
							"namakecamatan": "KAROSSA"
						},
						{
							"id": 3069,
							"namakecamatan": "KARTASURA"
						},
						{
							"id": 3915,
							"namakecamatan": "KARTOHARJO"
						},
						{
							"id": 4125,
							"namakecamatan": "KARTOHARJO"
						},
						{
							"id": 7510,
							"namakecamatan": "KARU"
						},
						{
							"id": 7341,
							"namakecamatan": "KARUBAGA"
						},
						{
							"id": 5201,
							"namakecamatan": "KARUSEN JANANG"
						},
						{
							"id": 1990,
							"namakecamatan": "KARYA PENGGAWA"
						},
						{
							"id": 1840,
							"namakecamatan": "KARYA PENGGAWA"
						},
						{
							"id": 3639,
							"namakecamatan": "KASEMBON"
						},
						{
							"id": 4263,
							"namakecamatan": "KASEMEN"
						},
						{
							"id": 4318,
							"namakecamatan": "KASEMEN"
						},
						{
							"id": 7826,
							"namakecamatan": "KASI"
						},
						{
							"id": 3447,
							"namakecamatan": "KASIHAN"
						},
						{
							"id": 3957,
							"namakecamatan": "KASIMAN"
						},
						{
							"id": 5868,
							"namakecamatan": "KASIMBAR"
						},
						{
							"id": 6902,
							"namakecamatan": "KASIRUTA BARAT"
						},
						{
							"id": 6903,
							"namakecamatan": "KASIRUTA TIMUR"
						},
						{
							"id": 2572,
							"namakecamatan": "KASOKANDEL"
						},
						{
							"id": 2657,
							"namakecamatan": "KASOMALANG"
						},
						{
							"id": 3937,
							"namakecamatan": "KASREMAN"
						},
						{
							"id": 1938,
							"namakecamatan": "KASUI"
						},
						{
							"id": 4756,
							"namakecamatan": "KATALA HAMU LINGU"
						},
						{
							"id": 2324,
							"namakecamatan": "KATAPANG"
						},
						{
							"id": 1077,
							"namakecamatan": "KATEMAN"
						},
						{
							"id": 1761,
							"namakecamatan": "KATIBUNG"
						},
						{
							"id": 4814,
							"namakecamatan": "KATIKU TANA"
						},
						{
							"id": 4772,
							"namakecamatan": "KATIKU TANA"
						},
						{
							"id": 4818,
							"namakecamatan": "KATIKU TANA SELATAN"
						},
						{
							"id": 5123,
							"namakecamatan": "KATINGAN HILIR"
						},
						{
							"id": 5129,
							"namakecamatan": "KATINGAN HULU"
						},
						{
							"id": 5131,
							"namakecamatan": "KATINGAN KUALA"
						},
						{
							"id": 5126,
							"namakecamatan": "KATINGAN TENGAH"
						},
						{
							"id": 6341,
							"namakecamatan": "KATOBU"
						},
						{
							"id": 6462,
							"namakecamatan": "KATOI"
						},
						{
							"id": 5436,
							"namakecamatan": "KAUBUN"
						},
						{
							"id": 5646,
							"namakecamatan": "KAUDITAN"
						},
						{
							"id": 3521,
							"namakecamatan": "KAUMAN"
						},
						{
							"id": 3549,
							"namakecamatan": "KAUMAN"
						},
						{
							"id": 7073,
							"namakecamatan": "KAUREH"
						},
						{
							"id": 1675,
							"namakecamatan": "KAUR SELATAN"
						},
						{
							"id": 1674,
							"namakecamatan": "KAUR TENGAH"
						},
						{
							"id": 1673,
							"namakecamatan": "KAUR UTARA"
						},
						{
							"id": 7421,
							"namakecamatan": "KAWAGIT"
						},
						{
							"id": 2449,
							"namakecamatan": "KAWALI"
						},
						{
							"id": 2836,
							"namakecamatan": "KAWALU"
						},
						{
							"id": 5564,
							"namakecamatan": "KAWANGKOAN"
						},
						{
							"id": 5574,
							"namakecamatan": "KAWANGKOAN BARAT"
						},
						{
							"id": 5573,
							"namakecamatan": "KAWANGKOAN UTARA"
						},
						{
							"id": 81,
							"namakecamatan": "KAWAY XVI"
						},
						{
							"id": 3905,
							"namakecamatan": "KAWEDANAN"
						},
						{
							"id": 7270,
							"namakecamatan": "KAWOR"
						},
						{
							"id": 2854,
							"namakecamatan": "KAWUNGANTEN"
						},
						{
							"id": 4499,
							"namakecamatan": "KAYANGAN"
						},
						{
							"id": 5481,
							"namakecamatan": "KAYAN HILIR"
						},
						{
							"id": 4954,
							"namakecamatan": "KAYAN HILIR"
						},
						{
							"id": 4955,
							"namakecamatan": "KAYAN HULU"
						},
						{
							"id": 5482,
							"namakecamatan": "KAYAN HULU"
						},
						{
							"id": 5487,
							"namakecamatan": "KAYAN SELATAN"
						},
						{
							"id": 7701,
							"namakecamatan": "KAYAUNI"
						},
						{
							"id": 3182,
							"namakecamatan": "KAYEN"
						},
						{
							"id": 3609,
							"namakecamatan": "KAYEN KIDUL"
						},
						{
							"id": 7339,
							"namakecamatan": "KAYO"
						},
						{
							"id": 6889,
							"namakecamatan": "KAYOA"
						},
						{
							"id": 6898,
							"namakecamatan": "KAYOA BARAT"
						},
						{
							"id": 6899,
							"namakecamatan": "KAYOA SELATAN"
						},
						{
							"id": 6900,
							"namakecamatan": "KAYOA UTARA"
						},
						{
							"id": 1371,
							"namakecamatan": "KAYU AGUNG"
						},
						{
							"id": 1198,
							"namakecamatan": "KAYU ARO"
						},
						{
							"id": 1208,
							"namakecamatan": "KAYU ARO BARAT"
						},
						{
							"id": 3108,
							"namakecamatan": "KEBAKKRAMAT"
						},
						{
							"id": 7807,
							"namakecamatan": "KEBAR"
						},
						{
							"id": 7663,
							"namakecamatan": "KEBAR"
						},
						{
							"id": 7822,
							"namakecamatan": "KEBAR SELATAN"
						},
						{
							"id": 7821,
							"namakecamatan": "KEBAR TIMUR"
						},
						{
							"id": 2874,
							"namakecamatan": "KEBASEN"
						},
						{
							"id": 1732,
							"namakecamatan": "KEBAWETAN"
						},
						{
							"id": 69,
							"namakecamatan": "KEBAYAKAN"
						},
						{
							"id": 2181,
							"namakecamatan": "KEBAYORAN BARU"
						},
						{
							"id": 2179,
							"namakecamatan": "KEBAYORAN LAMA"
						},
						{
							"id": 7194,
							"namakecamatan": "KEBO"
						},
						{
							"id": 4026,
							"namakecamatan": "KEBOMAS"
						},
						{
							"id": 3240,
							"namakecamatan": "KEBONAGUNG"
						},
						{
							"id": 3502,
							"namakecamatan": "KEBONAGUNG"
						},
						{
							"id": 3038,
							"namakecamatan": "KEBONARUM"
						},
						{
							"id": 2171,
							"namakecamatan": "KEBON JERUK"
						},
						{
							"id": 2268,
							"namakecamatan": "KEBONPEDES"
						},
						{
							"id": 3886,
							"namakecamatan": "KEBON SARI"
						},
						{
							"id": 2946,
							"namakecamatan": "KEBUMEN"
						},
						{
							"id": 1846,
							"namakecamatan": "KEBUN TEBU"
						},
						{
							"id": 2014,
							"namakecamatan": "KEDAMAIAN"
						},
						{
							"id": 4020,
							"namakecamatan": "KEDAMEAN"
						},
						{
							"id": 1997,
							"namakecamatan": "KEDATON"
						},
						{
							"id": 2528,
							"namakecamatan": "KEDAWUNG"
						},
						{
							"id": 3115,
							"namakecamatan": "KEDAWUNG"
						},
						{
							"id": 3962,
							"namakecamatan": "KEDEWAN"
						},
						{
							"id": 4388,
							"namakecamatan": "KEDIRI"
						},
						{
							"id": 4340,
							"namakecamatan": "KEDIRI"
						},
						{
							"id": 2628,
							"namakecamatan": "KEDOKAN BUNDER"
						},
						{
							"id": 1755,
							"namakecamatan": "KEDONDONG"
						},
						{
							"id": 1957,
							"namakecamatan": "KEDONDONG"
						},
						{
							"id": 4118,
							"namakecamatan": "KEDOPAK"
						},
						{
							"id": 3267,
							"namakecamatan": "KEDU"
						},
						{
							"id": 3211,
							"namakecamatan": "KEDUNG"
						},
						{
							"id": 3945,
							"namakecamatan": "KEDUNGADEM"
						},
						{
							"id": 2892,
							"namakecamatan": "KEDUNGBANTENG"
						},
						{
							"id": 3356,
							"namakecamatan": "KEDUNGBANTENG"
						},
						{
							"id": 4054,
							"namakecamatan": "KEDUNGDUNG"
						},
						{
							"id": 3929,
							"namakecamatan": "KEDUNGGALAR"
						},
						{
							"id": 3660,
							"namakecamatan": "KEDUNGJAJANG"
						},
						{
							"id": 3132,
							"namakecamatan": "KEDUNGJATI"
						},
						{
							"id": 4111,
							"namakecamatan": "KEDUNGKANDANG"
						},
						{
							"id": 3991,
							"namakecamatan": "KEDUNGPRING"
						},
						{
							"id": 2846,
							"namakecamatan": "KEDUNGREJA"
						},
						{
							"id": 3154,
							"namakecamatan": "KEDUNGTUBAN"
						},
						{
							"id": 2720,
							"namakecamatan": "KEDUNG WARINGIN"
						},
						{
							"id": 3547,
							"namakecamatan": "KEDUNGWARU"
						},
						{
							"id": 3328,
							"namakecamatan": "KEDUNGWUNI"
						},
						{
							"id": 1611,
							"namakecamatan": "KEDURANG"
						},
						{
							"id": 1617,
							"namakecamatan": "KEDURANG ILIR"
						},
						{
							"id": 6094,
							"namakecamatan": "KEERA"
						},
						{
							"id": 7527,
							"namakecamatan": "KEGAYEM"
						},
						{
							"id": 6728,
							"namakecamatan": "KEI BESAR"
						},
						{
							"id": 6729,
							"namakecamatan": "KEI BESAR SELATAN"
						},
						{
							"id": 6743,
							"namakecamatan": "KEI BESAR SELATAN BARAT"
						},
						{
							"id": 6742,
							"namakecamatan": "KEI BESAR UTARA BARAT"
						},
						{
							"id": 6730,
							"namakecamatan": "KEI BESAR UTARA TIMUR"
						},
						{
							"id": 6726,
							"namakecamatan": "KEI KECIL"
						},
						{
							"id": 6739,
							"namakecamatan": "KEI KECIL BARAT"
						},
						{
							"id": 6738,
							"namakecamatan": "KEI KECIL TIMUR"
						},
						{
							"id": 6744,
							"namakecamatan": "KEI KECIL TIMURSELATAN"
						},
						{
							"id": 2989,
							"namakecamatan": "KEJAJAR"
						},
						{
							"id": 2801,
							"namakecamatan": "KEJAKSAN"
						},
						{
							"id": 3790,
							"namakecamatan": "KEJAYAN"
						},
						{
							"id": 2899,
							"namakecamatan": "KEJOBONG"
						},
						{
							"id": 260,
							"namakecamatan": "KEJURUAN MUDA"
						},
						{
							"id": 4965,
							"namakecamatan": "KELAM PERMAI"
						},
						{
							"id": 1679,
							"namakecamatan": "KELAM TENGAH"
						},
						{
							"id": 2052,
							"namakecamatan": "KELAPA"
						},
						{
							"id": 4252,
							"namakecamatan": "KELAPA DUA"
						},
						{
							"id": 2166,
							"namakecamatan": "KELAPA GADING"
						},
						{
							"id": 2058,
							"namakecamatan": "KELAPA KAMPIT"
						},
						{
							"id": 4859,
							"namakecamatan": "KELAPA LIMA"
						},
						{
							"id": 5969,
							"namakecamatan": "KELARA"
						},
						{
							"id": 5388,
							"namakecamatan": "KELAY"
						},
						{
							"id": 1045,
							"namakecamatan": "KELAYANG"
						},
						{
							"id": 1411,
							"namakecamatan": "KELEKAR"
						},
						{
							"id": 7470,
							"namakecamatan": "KELILA"
						},
						{
							"id": 7000,
							"namakecamatan": "KELILA"
						},
						{
							"id": 1197,
							"namakecamatan": "KELILING DANAU"
						},
						{
							"id": 4693,
							"namakecamatan": "KELIMUTU"
						},
						{
							"id": 3219,
							"namakecamatan": "KELING"
						},
						{
							"id": 5320,
							"namakecamatan": "KELUA"
						},
						{
							"id": 1473,
							"namakecamatan": "KELUANG"
						},
						{
							"id": 4650,
							"namakecamatan": "KELUBAGOLIT"
						},
						{
							"id": 7513,
							"namakecamatan": "KELULOME"
						},
						{
							"id": 5235,
							"namakecamatan": "KELUMPANG BARAT"
						},
						{
							"id": 5234,
							"namakecamatan": "KELUMPANG HILIR"
						},
						{
							"id": 5225,
							"namakecamatan": "KELUMPANG HULU"
						},
						{
							"id": 5224,
							"namakecamatan": "KELUMPANG SELATAN"
						},
						{
							"id": 5226,
							"namakecamatan": "KELUMPANG TENGAH"
						},
						{
							"id": 5227,
							"namakecamatan": "KELUMPANG UTARA"
						},
						{
							"id": 5645,
							"namakecamatan": "KEMA"
						},
						{
							"id": 3052,
							"namakecamatan": "KEMALANG"
						},
						{
							"id": 2206,
							"namakecamatan": "KEMANG"
						},
						{
							"id": 2897,
							"namakecamatan": "KEMANGKON"
						},
						{
							"id": 2155,
							"namakecamatan": "KEMAYORAN"
						},
						{
							"id": 3224,
							"namakecamatan": "KEMBANG"
						},
						{
							"id": 2174,
							"namakecamatan": "KEMBANGAN"
						},
						{
							"id": 4004,
							"namakecamatan": "KEMBANGBAHU"
						},
						{
							"id": 5379,
							"namakecamatan": "KEMBANG JANGGUT"
						},
						{
							"id": 122,
							"namakecamatan": "KEMBANG TANJONG"
						},
						{
							"id": 2889,
							"namakecamatan": "KEMBARAN"
						},
						{
							"id": 4907,
							"namakecamatan": "KEMBAYAN"
						},
						{
							"id": 7562,
							"namakecamatan": "KEMBRU"
						},
						{
							"id": 7344,
							"namakecamatan": "KEMBU"
						},
						{
							"id": 2009,
							"namakecamatan": "KEMILING"
						},
						{
							"id": 2972,
							"namakecamatan": "KEMIRI"
						},
						{
							"id": 4233,
							"namakecamatan": "KEMIRI"
						},
						{
							"id": 3841,
							"namakecamatan": "KEMLAGI"
						},
						{
							"id": 1088,
							"namakecamatan": "KEMPAS"
						},
						{
							"id": 4464,
							"namakecamatan": "KEMPO"
						},
						{
							"id": 2875,
							"namakecamatan": "KEMRANJEN"
						},
						{
							"id": 7067,
							"namakecamatan": "KEMTUK"
						},
						{
							"id": 7068,
							"namakecamatan": "KEMTUK GRESI"
						},
						{
							"id": 1584,
							"namakecamatan": "KEMUNING"
						},
						{
							"id": 1083,
							"namakecamatan": "KEMUNING"
						},
						{
							"id": 3029,
							"namakecamatan": "KEMUSU"
						},
						{
							"id": 3667,
							"namakecamatan": "KENCONG"
						},
						{
							"id": 5593,
							"namakecamatan": "KENDAHE"
						},
						{
							"id": 3922,
							"namakecamatan": "KENDAL"
						},
						{
							"id": 3295,
							"namakecamatan": "KENDAL"
						},
						{
							"id": 6527,
							"namakecamatan": "KENDARI"
						},
						{
							"id": 6530,
							"namakecamatan": "KENDARI BARAT"
						},
						{
							"id": 4925,
							"namakecamatan": "KENDAWANGAN"
						},
						{
							"id": 3748,
							"namakecamatan": "KENDIT"
						},
						{
							"id": 3966,
							"namakecamatan": "KENDURUAN"
						},
						{
							"id": 4144,
							"namakecamatan": "KENJERAN"
						},
						{
							"id": 5378,
							"namakecamatan": "KENOHAN"
						},
						{
							"id": 7518,
							"namakecamatan": "KENYAM"
						},
						{
							"id": 7001,
							"namakecamatan": "KENYAM"
						},
						{
							"id": 4713,
							"namakecamatan": "KEO TENGAH"
						},
						{
							"id": 4812,
							"namakecamatan": "KEO TENGAH"
						},
						{
							"id": 1730,
							"namakecamatan": "KEPAHIANG"
						},
						{
							"id": 1636,
							"namakecamatan": "KEPAHIANG"
						},
						{
							"id": 6769,
							"namakecamatan": "KEPALA MADAN"
						},
						{
							"id": 6834,
							"namakecamatan": "KEPALA MADAN"
						},
						{
							"id": 3624,
							"namakecamatan": "KEPANJEN"
						},
						{
							"id": 4106,
							"namakecamatan": "KEPANJENKIDUL"
						},
						{
							"id": 6653,
							"namakecamatan": "KEP. BALA BALAKANG"
						},
						{
							"id": 1106,
							"namakecamatan": "KEPENUHAN"
						},
						{
							"id": 1116,
							"namakecamatan": "KEPENUHAN HULU"
						},
						{
							"id": 2978,
							"namakecamatan": "KEPIL"
						},
						{
							"id": 6434,
							"namakecamatan": "KEP. MASALOKA RAYA"
						},
						{
							"id": 3946,
							"namakecamatan": "KEPOH BARU"
						},
						{
							"id": 7116,
							"namakecamatan": "KEPULAUAN AMBAI"
						},
						{
							"id": 7459,
							"namakecamatan": "KEPULAUAN ARURI"
						},
						{
							"id": 7734,
							"namakecamatan": "KEPULAUAN AYAU"
						},
						{
							"id": 7626,
							"namakecamatan": "KEPULAUAN AYAU"
						},
						{
							"id": 6905,
							"namakecamatan": "KEPULAUANBOTANGLOMANG"
						},
						{
							"id": 6912,
							"namakecamatan": "KEPULAUAN JORONGA"
						},
						{
							"id": 5046,
							"namakecamatan": "KEPULAUAN KARIMATA"
						},
						{
							"id": 6801,
							"namakecamatan": "KEPULAUAN MANIPA"
						},
						{
							"id": 5602,
							"namakecamatan": "KEPULAUAN MARORE"
						},
						{
							"id": 2042,
							"namakecamatan": "KEPULAUAN PONGOK"
						},
						{
							"id": 2127,
							"namakecamatan": "KEPULAUAN POSEK"
						},
						{
							"id": 6829,
							"namakecamatan": "KEPULAUAN ROMANG"
						},
						{
							"id": 7746,
							"namakecamatan": "KEPULAUAN SEMBILAN"
						},
						{
							"id": 2152,
							"namakecamatan": "KEPULAUAN SERIBUSELATAN"
						},
						{
							"id": 2151,
							"namakecamatan": "KEPULAUAN SERIBUUTARA"
						},
						{
							"id": 3603,
							"namakecamatan": "KEPUNG"
						},
						{
							"id": 663,
							"namakecamatan": "KERAJAAN"
						},
						{
							"id": 4338,
							"namakecamatan": "KERAMBITAN"
						},
						{
							"id": 3973,
							"namakecamatan": "KEREK"
						},
						{
							"id": 1139,
							"namakecamatan": "KERINCI KANAN"
						},
						{
							"id": 1078,
							"namakecamatan": "KERITANG"
						},
						{
							"id": 3110,
							"namakecamatan": "KERJO"
						},
						{
							"id": 1651,
							"namakecamatan": "KERKAP"
						},
						{
							"id": 2372,
							"namakecamatan": "KERSAMANAH"
						},
						{
							"id": 3377,
							"namakecamatan": "KERSANA"
						},
						{
							"id": 2562,
							"namakecamatan": "KERTAJATI"
						},
						{
							"id": 5240,
							"namakecamatan": "KERTAK HANYAR"
						},
						{
							"id": 2914,
							"namakecamatan": "KERTANEGARA"
						},
						{
							"id": 1588,
							"namakecamatan": "KERTAPATI"
						},
						{
							"id": 2344,
							"namakecamatan": "KERTASARI"
						},
						{
							"id": 2608,
							"namakecamatan": "KERTASEMAYA"
						},
						{
							"id": 2984,
							"namakecamatan": "KERTEK"
						},
						{
							"id": 3873,
							"namakecamatan": "KERTOSONO"
						},
						{
							"id": 4414,
							"namakecamatan": "KERUAK"
						},
						{
							"id": 1096,
							"namakecamatan": "KERUMUTAN"
						},
						{
							"id": 3856,
							"namakecamatan": "KESAMBEN"
						},
						{
							"id": 3582,
							"namakecamatan": "KESAMBEN"
						},
						{
							"id": 2805,
							"namakecamatan": "KESAMBI"
						},
						{
							"id": 3324,
							"namakecamatan": "KESESI"
						},
						{
							"id": 6229,
							"namakecamatan": "KESU"
						},
						{
							"id": 6181,
							"namakecamatan": "KE'SU"
						},
						{
							"id": 2847,
							"namakecamatan": "KESUGIHAN"
						},
						{
							"id": 1657,
							"namakecamatan": "KETAHUN"
						},
						{
							"id": 30,
							"namakecamatan": "KETAMBE"
						},
						{
							"id": 3382,
							"namakecamatan": "KETANGGUNGAN"
						},
						{
							"id": 4060,
							"namakecamatan": "KETAPANG"
						},
						{
							"id": 1767,
							"namakecamatan": "KETAPANG"
						},
						{
							"id": 68,
							"namakecamatan": "KETOL"
						},
						{
							"id": 4950,
							"namakecamatan": "KETUNGAU HILIR"
						},
						{
							"id": 4952,
							"namakecamatan": "KETUNGAU HULU"
						},
						{
							"id": 4951,
							"namakecamatan": "KETUNGAU TENGAH"
						},
						{
							"id": 136,
							"namakecamatan": "KEUMALA"
						},
						{
							"id": 4668,
							"namakecamatan": "KEWAPANTE"
						},
						{
							"id": 7420,
							"namakecamatan": "KI"
						},
						{
							"id": 6790,
							"namakecamatan": "KIAN DARAT"
						},
						{
							"id": 2786,
							"namakecamatan": "KIARACONDONG"
						},
						{
							"id": 2678,
							"namakecamatan": "KIARAPEDES"
						},
						{
							"id": 4276,
							"namakecamatan": "KIBIN"
						},
						{
							"id": 4552,
							"namakecamatan": "KI'E"
						},
						{
							"id": 1434,
							"namakecamatan": "KIKIM BARAT"
						},
						{
							"id": 1431,
							"namakecamatan": "KIKIM SELATAN"
						},
						{
							"id": 1433,
							"namakecamatan": "KIKIM TENGAH"
						},
						{
							"id": 1432,
							"namakecamatan": "KIKIM TIMUR"
						},
						{
							"id": 7531,
							"namakecamatan": "KILMID"
						},
						{
							"id": 6785,
							"namakecamatan": "KILMURY"
						},
						{
							"id": 4466,
							"namakecamatan": "KILO"
						},
						{
							"id": 6978,
							"namakecamatan": "KIMAAM"
						},
						{
							"id": 1671,
							"namakecamatan": "KINAL"
						},
						{
							"id": 941,
							"namakecamatan": "KINALI"
						},
						{
							"id": 984,
							"namakecamatan": "KINALI"
						},
						{
							"id": 5954,
							"namakecamatan": "KINDANG"
						},
						{
							"id": 5792,
							"namakecamatan": "KINOVARO"
						},
						{
							"id": 5908,
							"namakecamatan": "KINOVARO"
						},
						{
							"id": 4365,
							"namakecamatan": "KINTAMANI"
						},
						{
							"id": 5213,
							"namakecamatan": "KINTAP"
						},
						{
							"id": 5723,
							"namakecamatan": "KINTOM"
						},
						{
							"id": 7396,
							"namakecamatan": "KIRIHI"
						},
						{
							"id": 1529,
							"namakecamatan": "KISAM ILIR"
						},
						{
							"id": 1528,
							"namakecamatan": "KISAM TINGGI"
						},
						{
							"id": 6830,
							"namakecamatan": "KISAR UTARA"
						},
						{
							"id": 3085,
							"namakecamatan": "KISMANTORO"
						},
						{
							"id": 7255,
							"namakecamatan": "KIWIROK"
						},
						{
							"id": 7260,
							"namakecamatan": "KIWIROK TIMUR"
						},
						{
							"id": 7175,
							"namakecamatan": "KIYAGE"
						},
						{
							"id": 3734,
							"namakecamatan": "KLABANG"
						},
						{
							"id": 7619,
							"namakecamatan": "KLABOT"
						},
						{
							"id": 3663,
							"namakecamatan": "KLAKAH"
						},
						{
							"id": 3146,
							"namakecamatan": "KLAMBU"
						},
						{
							"id": 7610,
							"namakecamatan": "KLAMONO"
						},
						{
							"id": 4037,
							"namakecamatan": "KLAMPIS"
						},
						{
							"id": 2531,
							"namakecamatan": "KLANGENAN"
						},
						{
							"id": 2226,
							"namakecamatan": "KLAPANUNGGAL"
						},
						{
							"id": 2683,
							"namakecamatan": "KLARI"
						},
						{
							"id": 7652,
							"namakecamatan": "KLASAFET"
						},
						{
							"id": 7642,
							"namakecamatan": "KLASO"
						},
						{
							"id": 3057,
							"namakecamatan": "KLATEN SELATAN"
						},
						{
							"id": 3056,
							"namakecamatan": "KLATEN TENGAH"
						},
						{
							"id": 3055,
							"namakecamatan": "KLATEN UTARA"
						},
						{
							"id": 7875,
							"namakecamatan": "KLAURUNG"
						},
						{
							"id": 7620,
							"namakecamatan": "KLAWAK"
						},
						{
							"id": 7641,
							"namakecamatan": "KLAYILI"
						},
						{
							"id": 3277,
							"namakecamatan": "KLEDUNG"
						},
						{
							"id": 3027,
							"namakecamatan": "KLEGO"
						},
						{
							"id": 2939,
							"namakecamatan": "KLIRONG"
						},
						{
							"id": 4110,
							"namakecamatan": "KLOJEN"
						},
						{
							"id": 3,
							"namakecamatan": "KLUET SELATAN"
						},
						{
							"id": 13,
							"namakecamatan": "KLUET TENGAH"
						},
						{
							"id": 14,
							"namakecamatan": "KLUET TIMUR"
						},
						{
							"id": 2,
							"namakecamatan": "KLUET UTARA"
						},
						{
							"id": 1901,
							"namakecamatan": "KLUMBAYAN"
						},
						{
							"id": 1912,
							"namakecamatan": "KLUMBAYAN BARAT"
						},
						{
							"id": 4360,
							"namakecamatan": "KLUNGKUNG"
						},
						{
							"id": 2043,
							"namakecamatan": "KOBA"
						},
						{
							"id": 7469,
							"namakecamatan": "KOBAGMA"
						},
						{
							"id": 7004,
							"namakecamatan": "KOBAKMA"
						},
						{
							"id": 4605,
							"namakecamatan": "KOBALIMA"
						},
						{
							"id": 4855,
							"namakecamatan": "KOBALIMA"
						},
						{
							"id": 4854,
							"namakecamatan": "KOBALIMA TIMUR"
						},
						{
							"id": 4622,
							"namakecamatan": "KOBALIMA TIMUR"
						},
						{
							"id": 6278,
							"namakecamatan": "KODEOHA"
						},
						{
							"id": 6452,
							"namakecamatan": "KODEOHA"
						},
						{
							"id": 4760,
							"namakecamatan": "KODI"
						},
						{
							"id": 4825,
							"namakecamatan": "KODI"
						},
						{
							"id": 4829,
							"namakecamatan": "KODI BALAGHAR"
						},
						{
							"id": 4761,
							"namakecamatan": "KODI BANGEDO"
						},
						{
							"id": 4824,
							"namakecamatan": "KODI BANGEDO"
						},
						{
							"id": 4826,
							"namakecamatan": "KODI UTARA"
						},
						{
							"id": 4776,
							"namakecamatan": "KODI UTARA"
						},
						{
							"id": 7739,
							"namakecamatan": "KOFIAU"
						},
						{
							"id": 2163,
							"namakecamatan": "KOJA"
						},
						{
							"id": 6540,
							"namakecamatan": "KOKALUKUNA"
						},
						{
							"id": 3427,
							"namakecamatan": "KOKAP"
						},
						{
							"id": 7690,
							"namakecamatan": "KOKAS"
						},
						{
							"id": 4565,
							"namakecamatan": "KOK BAUN"
						},
						{
							"id": 7628,
							"namakecamatan": "KOKODA"
						},
						{
							"id": 7712,
							"namakecamatan": "KOKODA"
						},
						{
							"id": 7724,
							"namakecamatan": "KOKODA UTARA"
						},
						{
							"id": 4040,
							"namakecamatan": "KOKOP"
						},
						{
							"id": 6266,
							"namakecamatan": "KOLAKA"
						},
						{
							"id": 315,
							"namakecamatan": "KOLANG"
						},
						{
							"id": 7506,
							"namakecamatan": "KOLAWA"
						},
						{
							"id": 4561,
							"namakecamatan": "KOLBANO"
						},
						{
							"id": 7445,
							"namakecamatan": "KOLF BRAZA"
						},
						{
							"id": 6404,
							"namakecamatan": "KOLONO"
						},
						{
							"id": 7417,
							"namakecamatan": "KOMBAY"
						},
						{
							"id": 5429,
							"namakecamatan": "KOMBENG"
						},
						{
							"id": 5556,
							"namakecamatan": "KOMBI"
						},
						{
							"id": 7357,
							"namakecamatan": "KOMBONERI"
						},
						{
							"id": 7408,
							"namakecamatan": "KOMBUT"
						},
						{
							"id": 4801,
							"namakecamatan": "KOMODO"
						},
						{
							"id": 7321,
							"namakecamatan": "KONA"
						},
						{
							"id": 4047,
							"namakecamatan": "KONANG"
						},
						{
							"id": 6321,
							"namakecamatan": "KONAWE"
						},
						{
							"id": 6402,
							"namakecamatan": "KONDA"
						},
						{
							"id": 7718,
							"namakecamatan": "KONDA"
						},
						{
							"id": 7352,
							"namakecamatan": "KONDA/ KONDAGA"
						},
						{
							"id": 7651,
							"namakecamatan": "KONHIR"
						},
						{
							"id": 6355,
							"namakecamatan": "KONTU KOWUNA"
						},
						{
							"id": 6345,
							"namakecamatan": "KONTUNAGA"
						},
						{
							"id": 4410,
							"namakecamatan": "KOPANG"
						},
						{
							"id": 7451,
							"namakecamatan": "KOPAY"
						},
						{
							"id": 4285,
							"namakecamatan": "KOPO"
						},
						{
							"id": 7542,
							"namakecamatan": "KORA"
						},
						{
							"id": 7048,
							"namakecamatan": "KORAGI"
						},
						{
							"id": 6752,
							"namakecamatan": "KORMOMOLIN"
						},
						{
							"id": 4194,
							"namakecamatan": "KORONCONG"
						},
						{
							"id": 7526,
							"namakecamatan": "KOROPTAK"
						},
						{
							"id": 7327,
							"namakecamatan": "KORUPUN"
						},
						{
							"id": 4238,
							"namakecamatan": "KOSAMBI"
						},
						{
							"id": 7310,
							"namakecamatan": "KOSAREK"
						},
						{
							"id": 7112,
							"namakecamatan": "KOSIWO"
						},
						{
							"id": 4104,
							"namakecamatan": "KOTA"
						},
						{
							"id": 1422,
							"namakecamatan": "KOTA AGUNG"
						},
						{
							"id": 1885,
							"namakecamatan": "KOTA AGUNG"
						},
						{
							"id": 1902,
							"namakecamatan": "KOTA AGUNG BARAT"
						},
						{
							"id": 1903,
							"namakecamatan": "KOTA AGUNG TIMUR"
						},
						{
							"id": 3771,
							"namakecamatan": "KOTAANYAR"
						},
						{
							"id": 1652,
							"namakecamatan": "KOTA ARGA MAKMUR"
						},
						{
							"id": 4610,
							"namakecamatan": "KOTA ATAMBUA"
						},
						{
							"id": 17,
							"namakecamatan": "KOTA BAHAGIA"
						},
						{
							"id": 191,
							"namakecamatan": "KOTA BAHARU"
						},
						{
							"id": 5377,
							"namakecamatan": "KOTA BANGUN"
						},
						{
							"id": 6617,
							"namakecamatan": "KOTA BARAT"
						},
						{
							"id": 1323,
							"namakecamatan": "KOTA BARU"
						},
						{
							"id": 2703,
							"namakecamatan": "KOTA BARU"
						},
						{
							"id": 4692,
							"namakecamatan": "KOTA BARU"
						},
						{
							"id": 5073,
							"namakecamatan": "KOTA BESI"
						},
						{
							"id": 1807,
							"namakecamatan": "KOTABUMI"
						},
						{
							"id": 1815,
							"namakecamatan": "KOTABUMI SELATAN"
						},
						{
							"id": 1814,
							"namakecamatan": "KOTABUMI UTARA"
						},
						{
							"id": 5684,
							"namakecamatan": "KOTABUNAN"
						},
						{
							"id": 5532,
							"namakecamatan": "KOTABUNAN"
						},
						{
							"id": 107,
							"namakecamatan": "KOTA COT GLIE"
						},
						{
							"id": 1800,
							"namakecamatan": "KOTA GAJAH"
						},
						{
							"id": 3497,
							"namakecamatan": "KOTAGEDE"
						},
						{
							"id": 106,
							"namakecamatan": "KOTA JANTHO"
						},
						{
							"id": 211,
							"namakecamatan": "KOTA JUANG"
						},
						{
							"id": 4579,
							"namakecamatan": "KOTA KEFAMENANU"
						},
						{
							"id": 533,
							"namakecamatan": "KOTA KISARAN BARAT"
						},
						{
							"id": 534,
							"namakecamatan": "KOTA KISARAN TIMUR"
						},
						{
							"id": 4835,
							"namakecamatan": "KOTA KOMBA"
						},
						{
							"id": 4729,
							"namakecamatan": "KOTA KOMBA"
						},
						{
							"id": 259,
							"namakecamatan": "KOTA KUALASINPANG"
						},
						{
							"id": 3203,
							"namakecamatan": "KOTA KUDUS"
						},
						{
							"id": 4862,
							"namakecamatan": "KOTA LAMA"
						},
						{
							"id": 6946,
							"namakecamatan": "KOTA MABA"
						},
						{
							"id": 1615,
							"namakecamatan": "KOTA MANNA"
						},
						{
							"id": 6716,
							"namakecamatan": "KOTA MASOHI"
						},
						{
							"id": 5720,
							"namakecamatan": "KOTAMOBAGU BARAT"
						},
						{
							"id": 5534,
							"namakecamatan": "KOTAMOBAGU BARAT"
						},
						{
							"id": 5719,
							"namakecamatan": "KOTAMOBAGU SELATAN"
						},
						{
							"id": 5544,
							"namakecamatan": "KOTAMOBAGU SELATAN"
						},
						{
							"id": 5718,
							"namakecamatan": "KOTAMOBAGU TIMUR"
						},
						{
							"id": 5543,
							"namakecamatan": "KOTAMOBAGU TIMUR"
						},
						{
							"id": 5717,
							"namakecamatan": "KOTAMOBAGU UTARA"
						},
						{
							"id": 5542,
							"namakecamatan": "KOTAMOBAGU UTARA"
						},
						{
							"id": 1701,
							"namakecamatan": "KOTA MUKOMUKO"
						},
						{
							"id": 615,
							"namakecamatan": "KOTANOPAN"
						},
						{
							"id": 1627,
							"namakecamatan": "KOTA PADANG"
						},
						{
							"id": 734,
							"namakecamatan": "KOTAPINANG"
						},
						{
							"id": 558,
							"namakecamatan": "KOTA PINANG"
						},
						{
							"id": 4861,
							"namakecamatan": "KOTA RAJA"
						},
						{
							"id": 698,
							"namakecamatan": "KOTARIH"
						},
						{
							"id": 6618,
							"namakecamatan": "KOTA SELATAN"
						},
						{
							"id": 123,
							"namakecamatan": "KOTA SIGLI"
						},
						{
							"id": 4543,
							"namakecamatan": "KOTA SOE"
						},
						{
							"id": 4076,
							"namakecamatan": "KOTA SUMENEP"
						},
						{
							"id": 4827,
							"namakecamatan": "KOTA TAMBOLAKA"
						},
						{
							"id": 6622,
							"namakecamatan": "KOTA TENGAH"
						},
						{
							"id": 6961,
							"namakecamatan": "KOTA TERNATE SELATAN"
						},
						{
							"id": 6965,
							"namakecamatan": "KOTA TERNATE TENGAH"
						},
						{
							"id": 6962,
							"namakecamatan": "KOTA TERNATE UTARA"
						},
						{
							"id": 6621,
							"namakecamatan": "KOTA TIMUR"
						},
						{
							"id": 6619,
							"namakecamatan": "KOTA UTARA"
						},
						{
							"id": 4774,
							"namakecamatan": "KOTA WAIKABUBAK"
						},
						{
							"id": 4738,
							"namakecamatan": "KOTA WAINGAPU"
						},
						{
							"id": 7747,
							"namakecamatan": "KOTA WAISAI"
						},
						{
							"id": 5069,
							"namakecamatan": "KOTAWARINGIN LAMA"
						},
						{
							"id": 4674,
							"namakecamatan": "KOTING"
						},
						{
							"id": 987,
							"namakecamatan": "KOTO BALINGKA"
						},
						{
							"id": 1334,
							"namakecamatan": "KOTO BARU"
						},
						{
							"id": 962,
							"namakecamatan": "KOTO BARU"
						},
						{
							"id": 861,
							"namakecamatan": "KOTO BARU"
						},
						{
							"id": 972,
							"namakecamatan": "KOTO BESAR"
						},
						{
							"id": 1141,
							"namakecamatan": "KOTO GASIB"
						},
						{
							"id": 1042,
							"namakecamatan": "KOTO KAMPAR HULU"
						},
						{
							"id": 4562,
							"namakecamatan": "KOT OLIN"
						},
						{
							"id": 975,
							"namakecamatan": "KOTO PARIK GADANGDIATEH"
						},
						{
							"id": 968,
							"namakecamatan": "KOTO SALAK"
						},
						{
							"id": 1001,
							"namakecamatan": "KOTO TANGAH"
						},
						{
							"id": 868,
							"namakecamatan": "KOTO VII"
						},
						{
							"id": 833,
							"namakecamatan": "KOTO XI TARUSAN"
						},
						{
							"id": 7405,
							"namakecamatan": "KOUH"
						},
						{
							"id": 855,
							"namakecamatan": "K. PARIK GD. DIATEH"
						},
						{
							"id": 3138,
							"namakecamatan": "KRADENAN"
						},
						{
							"id": 3153,
							"namakecamatan": "KRADENAN"
						},
						{
							"id": 3178,
							"namakecamatan": "KRAGAN"
						},
						{
							"id": 4271,
							"namakecamatan": "KRAGILAN"
						},
						{
							"id": 3774,
							"namakecamatan": "KRAKSAAN"
						},
						{
							"id": 3363,
							"namakecamatan": "KRAMAT"
						},
						{
							"id": 2188,
							"namakecamatan": "KRAMATJATI"
						},
						{
							"id": 2492,
							"namakecamatan": "KRAMATMULYA"
						},
						{
							"id": 4265,
							"namakecamatan": "KRAMATWATU"
						},
						{
							"id": 7694,
							"namakecamatan": "KRAMONGMONGGA"
						},
						{
							"id": 3273,
							"namakecamatan": "KRANGGAN"
						},
						{
							"id": 2609,
							"namakecamatan": "KRANGKENG"
						},
						{
							"id": 3588,
							"namakecamatan": "KRAS"
						},
						{
							"id": 3800,
							"namakecamatan": "KRATON"
						},
						{
							"id": 3492,
							"namakecamatan": "KRATON"
						},
						{
							"id": 5497,
							"namakecamatan": "KRAYAN"
						},
						{
							"id": 5499,
							"namakecamatan": "KRAYAN SELATAN"
						},
						{
							"id": 3775,
							"namakecamatan": "KREJENGAN"
						},
						{
							"id": 4142,
							"namakecamatan": "KREMBANGAN"
						},
						{
							"id": 3811,
							"namakecamatan": "KREMBUNG"
						},
						{
							"id": 7548,
							"namakecamatan": "KREPKURI"
						},
						{
							"id": 4230,
							"namakecamatan": "KRESEK"
						},
						{
							"id": 3434,
							"namakecamatan": "KRETEK"
						},
						{
							"id": 3819,
							"namakecamatan": "KRIAN"
						},
						{
							"id": 3642,
							"namakecamatan": "KROMENGAN"
						},
						{
							"id": 4231,
							"namakecamatan": "KRONJO"
						},
						{
							"id": 2602,
							"namakecamatan": "KROYA"
						},
						{
							"id": 2851,
							"namakecamatan": "KROYA"
						},
						{
							"id": 3768,
							"namakecamatan": "KRUCIL"
						},
						{
							"id": 112,
							"namakecamatan": "KRUENG BARONA JAYA"
						},
						{
							"id": 237,
							"namakecamatan": "KRUENG SABEE"
						},
						{
							"id": 1993,
							"namakecamatan": "KRUI SELATAN"
						},
						{
							"id": 1853,
							"namakecamatan": "KRUI SELATAN"
						},
						{
							"id": 413,
							"namakecamatan": "KUALA"
						},
						{
							"id": 245,
							"namakecamatan": "KUALA"
						},
						{
							"id": 212,
							"namakecamatan": "KUALA"
						},
						{
							"id": 196,
							"namakecamatan": "KUALA BARU"
						},
						{
							"id": 220,
							"namakecamatan": "KUALA BATEE"
						},
						{
							"id": 5018,
							"namakecamatan": "KUALA BEHE"
						},
						{
							"id": 1275,
							"namakecamatan": "KUALA BETARA"
						},
						{
							"id": 1052,
							"namakecamatan": "KUALA CENAKU"
						},
						{
							"id": 1072,
							"namakecamatan": "KUALA INDRAGIRI"
						},
						{
							"id": 1284,
							"namakecamatan": "KUALA JAMBI"
						},
						{
							"id": 1099,
							"namakecamatan": "KUALA KAMPAR"
						},
						{
							"id": 7218,
							"namakecamatan": "KUALA KENCANA"
						},
						{
							"id": 563,
							"namakecamatan": "KUALA LEIDONG"
						},
						{
							"id": 4894,
							"namakecamatan": "KUALA MANDOR B"
						},
						{
							"id": 5048,
							"namakecamatan": "KUALA MANDOR B"
						},
						{
							"id": 251,
							"namakecamatan": "KUALA PESISIR"
						},
						{
							"id": 4563,
							"namakecamatan": "KUALIN"
						},
						{
							"id": 741,
							"namakecamatan": "KUALUH HILIR"
						},
						{
							"id": 562,
							"namakecamatan": "KUALUH HILIR"
						},
						{
							"id": 549,
							"namakecamatan": "KUALUH HULU"
						},
						{
							"id": 739,
							"namakecamatan": "KUALUH HULU"
						},
						{
							"id": 740,
							"namakecamatan": "KUALUH LEIDONG"
						},
						{
							"id": 746,
							"namakecamatan": "KUALUH SELATAN"
						},
						{
							"id": 567,
							"namakecamatan": "KUALUH SELATAN"
						},
						{
							"id": 4553,
							"namakecamatan": "KUANFATU"
						},
						{
							"id": 1150,
							"namakecamatan": "KUANTAN HILIR"
						},
						{
							"id": 1159,
							"namakecamatan": "KUANTAN HILIR SEBERANG"
						},
						{
							"id": 1147,
							"namakecamatan": "KUANTAN MUDIK"
						},
						{
							"id": 1148,
							"namakecamatan": "KUANTAN TENGAH"
						},
						{
							"id": 7354,
							"namakecamatan": "KUARI"
						},
						{
							"id": 5364,
							"namakecamatan": "KUARO"
						},
						{
							"id": 4569,
							"namakecamatan": "KUATNANA"
						},
						{
							"id": 7351,
							"namakecamatan": "KUBU"
						},
						{
							"id": 4373,
							"namakecamatan": "KUBU"
						},
						{
							"id": 4890,
							"namakecamatan": "KUBU"
						},
						{
							"id": 5052,
							"namakecamatan": "KUBU"
						},
						{
							"id": 1118,
							"namakecamatan": "KUBU"
						},
						{
							"id": 1132,
							"namakecamatan": "KUBU BABUSSALAM"
						},
						{
							"id": 851,
							"namakecamatan": "KUBUNG"
						},
						{
							"id": 4381,
							"namakecamatan": "KUBUTAMBAHAN"
						},
						{
							"id": 3861,
							"namakecamatan": "KUDU"
						},
						{
							"id": 5771,
							"namakecamatan": "KULAWI"
						},
						{
							"id": 5900,
							"namakecamatan": "KULAWI"
						},
						{
							"id": 5901,
							"namakecamatan": "KULAWI SELATAN"
						},
						{
							"id": 5790,
							"namakecamatan": "KULAWI SELATAN"
						},
						{
							"id": 6336,
							"namakecamatan": "KULISUSU"
						},
						{
							"id": 6476,
							"namakecamatan": "KULISUSU"
						},
						{
							"id": 6479,
							"namakecamatan": "KULISUSU BARAT"
						},
						{
							"id": 6335,
							"namakecamatan": "KULISUSU BARAT"
						},
						{
							"id": 6480,
							"namakecamatan": "KULISUSU UTARA"
						},
						{
							"id": 6100,
							"namakecamatan": "KULO"
						},
						{
							"id": 7508,
							"namakecamatan": "KULY LANNY"
						},
						{
							"id": 5067,
							"namakecamatan": "KUMAI"
						},
						{
							"id": 5636,
							"namakecamatan": "KUMELEMBUAI"
						},
						{
							"id": 1255,
							"namakecamatan": "KUMPEH"
						},
						{
							"id": 1258,
							"namakecamatan": "KUMPEH ULU"
						},
						{
							"id": 1332,
							"namakecamatan": "KUMUN DEBAI"
						},
						{
							"id": 1202,
							"namakecamatan": "KUMUN DEBAI"
						},
						{
							"id": 2085,
							"namakecamatan": "KUNDUR"
						},
						{
							"id": 3163,
							"namakecamatan": "KUNDURAN"
						},
						{
							"id": 2091,
							"namakecamatan": "KUNDUR BARAT"
						},
						{
							"id": 2090,
							"namakecamatan": "KUNDUR UTARA"
						},
						{
							"id": 2485,
							"namakecamatan": "KUNINGAN"
						},
						{
							"id": 3650,
							"namakecamatan": "KUNIR"
						},
						{
							"id": 3606,
							"namakecamatan": "KUNJANG"
						},
						{
							"id": 1107,
							"namakecamatan": "KUNTO DARUSSALAM"
						},
						{
							"id": 1026,
							"namakecamatan": "KUOK"
						},
						{
							"id": 4517,
							"namakecamatan": "KUPANG BARAT"
						},
						{
							"id": 4520,
							"namakecamatan": "KUPANG TENGAH"
						},
						{
							"id": 4518,
							"namakecamatan": "KUPANG TIMUR"
						},
						{
							"id": 870,
							"namakecamatan": "KUPITAN"
						},
						{
							"id": 999,
							"namakecamatan": "KURANJI"
						},
						{
							"id": 5340,
							"namakecamatan": "KURANJI"
						},
						{
							"id": 5210,
							"namakecamatan": "KURAU"
						},
						{
							"id": 7764,
							"namakecamatan": "KURI"
						},
						{
							"id": 6985,
							"namakecamatan": "KURIK"
						},
						{
							"id": 7288,
							"namakecamatan": "KURIMA"
						},
						{
							"id": 3763,
							"namakecamatan": "KURIPAN"
						},
						{
							"id": 4401,
							"namakecamatan": "KURIPAN"
						},
						{
							"id": 5268,
							"namakecamatan": "KURIPAN"
						},
						{
							"id": 7787,
							"namakecamatan": "KURI WAMESA"
						},
						{
							"id": 6727,
							"namakecamatan": "KUR MANGUR"
						},
						{
							"id": 6189,
							"namakecamatan": "KURRA"
						},
						{
							"id": 6846,
							"namakecamatan": "KUR SELATAN"
						},
						{
							"id": 6997,
							"namakecamatan": "KURULU"
						},
						{
							"id": 5163,
							"namakecamatan": "KURUN"
						},
						{
							"id": 6510,
							"namakecamatan": "KUSAMBI"
						},
						{
							"id": 6331,
							"namakecamatan": "KUSAMBI"
						},
						{
							"id": 5332,
							"namakecamatan": "KUSAN HILIR"
						},
						{
							"id": 5335,
							"namakecamatan": "KUSAN HULU"
						},
						{
							"id": 4345,
							"namakecamatan": "KUTA"
						},
						{
							"id": 286,
							"namakecamatan": "KUTA ALAM"
						},
						{
							"id": 102,
							"namakecamatan": "KUTA BARO"
						},
						{
							"id": 215,
							"namakecamatan": "KUTA BLANG"
						},
						{
							"id": 446,
							"namakecamatan": "KUTABULUH"
						},
						{
							"id": 454,
							"namakecamatan": "KUTALIMBARU"
						},
						{
							"id": 148,
							"namakecamatan": "KUTA MAKMUR"
						},
						{
							"id": 108,
							"namakecamatan": "KUTA MALAKA"
						},
						{
							"id": 432,
							"namakecamatan": "KUTAMBARU"
						},
						{
							"id": 226,
							"namakecamatan": "KUTAPANJANG"
						},
						{
							"id": 290,
							"namakecamatan": "KUTA RAJA"
						},
						{
							"id": 460,
							"namakecamatan": "KUTARIH"
						},
						{
							"id": 2903,
							"namakecamatan": "KUTASARI"
						},
						{
							"id": 4349,
							"namakecamatan": "KUTA SELATAN"
						},
						{
							"id": 4350,
							"namakecamatan": "KUTA UTARA"
						},
						{
							"id": 2685,
							"namakecamatan": "KUTAWALUYA"
						},
						{
							"id": 2359,
							"namakecamatan": "KUTAWARINGIN"
						},
						{
							"id": 70,
							"namakecamatan": "KUTE PANANG"
						},
						{
							"id": 2969,
							"namakecamatan": "KUTOARJO"
						},
						{
							"id": 3833,
							"namakecamatan": "KUTOREJO"
						},
						{
							"id": 2944,
							"namakecamatan": "KUTOWINANGUN"
						},
						{
							"id": 2950,
							"namakecamatan": "KUWARASAN"
						},
						{
							"id": 4798,
							"namakecamatan": "KUWUS"
						},
						{
							"id": 7010,
							"namakecamatan": "KUYAWAGE"
						},
						{
							"id": 7487,
							"namakecamatan": "KUYAWAGE"
						},
						{
							"id": 3924,
							"namakecamatan": "KWADUNGAN"
						},
						{
							"id": 7222,
							"namakecamatan": "KWAMKI NARAMA"
						},
						{
							"id": 6607,
							"namakecamatan": "KWANDANG"
						},
						{
							"id": 6549,
							"namakecamatan": "KWANDANG"
						},
						{
							"id": 4041,
							"namakecamatan": "KWANYAR"
						},
						{
							"id": 7334,
							"namakecamatan": "KWELEMDUA"
						},
						{
							"id": 7819,
							"namakecamatan": "KWESEFO"
						},
						{
							"id": 7335,
							"namakecamatan": "KWIKMA"
						},
						{
							"id": 7802,
							"namakecamatan": "KWOOR"
						},
						{
							"id": 7621,
							"namakecamatan": "KWOOR"
						},
						{
							"id": 6059,
							"namakecamatan": "LABAKKANG"
						},
						{
							"id": 4042,
							"namakecamatan": "LABANG"
						},
						{
							"id": 4452,
							"namakecamatan": "LABANGKA"
						},
						{
							"id": 5915,
							"namakecamatan": "LABOBO"
						},
						{
							"id": 5842,
							"namakecamatan": "LABOBO"
						},
						{
							"id": 4777,
							"namakecamatan": "LABOYA BARAT"
						},
						{
							"id": 4173,
							"namakecamatan": "LABUAN"
						},
						{
							"id": 5779,
							"namakecamatan": "LABUAN"
						},
						{
							"id": 5300,
							"namakecamatan": "LABUAN AMAS SELATAN"
						},
						{
							"id": 5301,
							"namakecamatan": "LABUAN AMAS UTARA"
						},
						{
							"id": 4394,
							"namakecamatan": "LABUAPI"
						},
						{
							"id": 4451,
							"namakecamatan": "LABUHAN BADAS"
						},
						{
							"id": 475,
							"namakecamatan": "LABUHAN DELI"
						},
						{
							"id": 4,
							"namakecamatan": "LABUHAN HAJI"
						},
						{
							"id": 4430,
							"namakecamatan": "LABUHAN HAJI"
						},
						{
							"id": 12,
							"namakecamatan": "LABUHAN HAJI BARAT"
						},
						{
							"id": 11,
							"namakecamatan": "LABUHAN HAJI TIMUR"
						},
						{
							"id": 1914,
							"namakecamatan": "LABUHAN MARINGGAI"
						},
						{
							"id": 2010,
							"namakecamatan": "LABUHAN RATU"
						},
						{
							"id": 1933,
							"namakecamatan": "LABUHAN RATU"
						},
						{
							"id": 6271,
							"namakecamatan": "LADONGI"
						},
						{
							"id": 6484,
							"namakecamatan": "LADONGI"
						},
						{
							"id": 4852,
							"namakecamatan": "LAENMANEN"
						},
						{
							"id": 4612,
							"namakecamatan": "LAEN MANEN"
						},
						{
							"id": 579,
							"namakecamatan": "LAE PARIRA"
						},
						{
							"id": 6414,
							"namakecamatan": "LAEYA"
						},
						{
							"id": 5746,
							"namakecamatan": "LAGE"
						},
						{
							"id": 585,
							"namakecamatan": "LAGUBOTI"
						},
						{
							"id": 5446,
							"namakecamatan": "LAHAM"
						},
						{
							"id": 5421,
							"namakecamatan": "LAHAM"
						},
						{
							"id": 1425,
							"namakecamatan": "LAHAT"
						},
						{
							"id": 5118,
							"namakecamatan": "LAHEI"
						},
						{
							"id": 5121,
							"namakecamatan": "LAHEI BARAT"
						},
						{
							"id": 378,
							"namakecamatan": "LAHEWA"
						},
						{
							"id": 756,
							"namakecamatan": "LAHEWA"
						},
						{
							"id": 757,
							"namakecamatan": "LAHEWA TIMUR"
						},
						{
							"id": 400,
							"namakecamatan": "LAHEWA TIMUR"
						},
						{
							"id": 758,
							"namakecamatan": "LAHOMI"
						},
						{
							"id": 406,
							"namakecamatan": "LAHOMI/ GAHORI"
						},
						{
							"id": 633,
							"namakecamatan": "LAHUSA"
						},
						{
							"id": 6401,
							"namakecamatan": "LAINEA"
						},
						{
							"id": 1655,
							"namakecamatan": "LAIS"
						},
						{
							"id": 1467,
							"namakecamatan": "LAIS"
						},
						{
							"id": 4145,
							"namakecamatan": "LAKARSANTRI"
						},
						{
							"id": 2457,
							"namakecamatan": "LAKBOK"
						},
						{
							"id": 5813,
							"namakecamatan": "LAKEA"
						},
						{
							"id": 6371,
							"namakecamatan": "LAKUDO"
						},
						{
							"id": 6512,
							"namakecamatan": "LAKUDO"
						},
						{
							"id": 6076,
							"namakecamatan": "LALABATA"
						},
						{
							"id": 1476,
							"namakecamatan": "LALAN"
						},
						{
							"id": 6407,
							"namakecamatan": "LALEMBUU"
						},
						{
							"id": 6285,
							"namakecamatan": "LALOLAE"
						},
						{
							"id": 6487,
							"namakecamatan": "LALOLAE"
						},
						{
							"id": 6325,
							"namakecamatan": "LALONGGASUMEETO"
						},
						{
							"id": 638,
							"namakecamatan": "LALOWAU"
						},
						{
							"id": 4599,
							"namakecamatan": "LAMAKNEN"
						},
						{
							"id": 4616,
							"namakecamatan": "LAMAKNEN SELATAN"
						},
						{
							"id": 5725,
							"namakecamatan": "LAMALA"
						},
						{
							"id": 5154,
							"namakecamatan": "LAMANDAU"
						},
						{
							"id": 6138,
							"namakecamatan": "LAMASI"
						},
						{
							"id": 6147,
							"namakecamatan": "LAMASI TIMUR"
						},
						{
							"id": 6455,
							"namakecamatan": "LAMBAI"
						},
						{
							"id": 4724,
							"namakecamatan": "LAMBA LEDA"
						},
						{
							"id": 4832,
							"namakecamatan": "LAMBA LEDA"
						},
						{
							"id": 6281,
							"namakecamatan": "LAMBANDIA"
						},
						{
							"id": 6486,
							"namakecamatan": "LAMBANDIA"
						},
						{
							"id": 7570,
							"namakecamatan": "LAMBEWI"
						},
						{
							"id": 4487,
							"namakecamatan": "LAMBITU"
						},
						{
							"id": 4771,
							"namakecamatan": "LAMBOYA"
						},
						{
							"id": 4482,
							"namakecamatan": "LAMBU"
						},
						{
							"id": 1984,
							"namakecamatan": "LAMBU KIBANG"
						},
						{
							"id": 1869,
							"namakecamatan": "LAMBU KIBANG"
						},
						{
							"id": 6290,
							"namakecamatan": "LAMBUYA"
						},
						{
							"id": 4007,
							"namakecamatan": "LAMONGAN"
						},
						{
							"id": 5807,
							"namakecamatan": "LAMPASIO"
						},
						{
							"id": 5345,
							"namakecamatan": "LAMPIHONG"
						},
						{
							"id": 1016,
							"namakecamatan": "LAMPOSI TIGO NAGORI"
						},
						{
							"id": 6024,
							"namakecamatan": "LAMURU"
						},
						{
							"id": 5355,
							"namakecamatan": "LANDASAN ULIN"
						},
						{
							"id": 6475,
							"namakecamatan": "LANDAWE"
						},
						{
							"id": 6400,
							"namakecamatan": "LANDONO"
						},
						{
							"id": 4796,
							"namakecamatan": "LANDU LEKO"
						},
						{
							"id": 7328,
							"namakecamatan": "LANGDA"
						},
						{
							"id": 2845,
							"namakecamatan": "LANGENSARI"
						},
						{
							"id": 1094,
							"namakecamatan": "LANGGAM"
						},
						{
							"id": 6315,
							"namakecamatan": "LANGGIKIMA"
						},
						{
							"id": 6465,
							"namakecamatan": "LANGGIKIMA"
						},
						{
							"id": 4481,
							"namakecamatan": "LANGGUDU"
						},
						{
							"id": 163,
							"namakecamatan": "LANGKAHAN"
						},
						{
							"id": 2468,
							"namakecamatan": "LANGKAPLANCAR"
						},
						{
							"id": 2752,
							"namakecamatan": "LANGKAPLANCAR"
						},
						{
							"id": 2012,
							"namakecamatan": "LANGKAPURA"
						},
						{
							"id": 4732,
							"namakecamatan": "LANGKE REMBONG"
						},
						{
							"id": 5562,
							"namakecamatan": "LANGOWAN BARAT"
						},
						{
							"id": 5569,
							"namakecamatan": "LANGOWAN SELATAN"
						},
						{
							"id": 5561,
							"namakecamatan": "LANGOWAN TIMUR"
						},
						{
							"id": 5571,
							"namakecamatan": "LANGOWAN UTARA"
						},
						{
							"id": 301,
							"namakecamatan": "LANGSA BARAT"
						},
						{
							"id": 304,
							"namakecamatan": "LANGSA BARO"
						},
						{
							"id": 302,
							"namakecamatan": "LANGSA KOTA"
						},
						{
							"id": 303,
							"namakecamatan": "LANGSA LAMA"
						},
						{
							"id": 300,
							"namakecamatan": "LANGSA TIMUR"
						},
						{
							"id": 7509,
							"namakecamatan": "LANNYNA"
						},
						{
							"id": 6115,
							"namakecamatan": "LANSIRANG"
						},
						{
							"id": 6438,
							"namakecamatan": "LANTARI JAYA"
						},
						{
							"id": 4462,
							"namakecamatan": "LANTUNG"
						},
						{
							"id": 6406,
							"namakecamatan": "LAONTI"
						},
						{
							"id": 6394,
							"namakecamatan": "LAPANDEWA"
						},
						{
							"id": 6521,
							"namakecamatan": "LAPANDEWA"
						},
						{
							"id": 168,
							"namakecamatan": "LAPANG"
						},
						{
							"id": 4445,
							"namakecamatan": "LAPE"
						},
						{
							"id": 6023,
							"namakecamatan": "LAPPARIAJA"
						},
						{
							"id": 4308,
							"namakecamatan": "LARANGAN"
						},
						{
							"id": 3381,
							"namakecamatan": "LARANGAN"
						},
						{
							"id": 4070,
							"namakecamatan": "LARANGAN"
						},
						{
							"id": 4642,
							"namakecamatan": "LARANTUKA"
						},
						{
							"id": 928,
							"namakecamatan": "LAREH SAGO HALABAN"
						},
						{
							"id": 3993,
							"namakecamatan": "LAREN"
						},
						{
							"id": 6637,
							"namakecamatan": "LARIANG"
						},
						{
							"id": 6131,
							"namakecamatan": "LAROMPONG"
						},
						{
							"id": 6139,
							"namakecamatan": "LAROMPONG SELATAN"
						},
						{
							"id": 6339,
							"namakecamatan": "LASALEPA"
						},
						{
							"id": 6387,
							"namakecamatan": "LASALIMU"
						},
						{
							"id": 6388,
							"namakecamatan": "LASALIMU SELATAN"
						},
						{
							"id": 3180,
							"namakecamatan": "LASEM"
						},
						{
							"id": 4615,
							"namakecamatan": "LASIOLAT"
						},
						{
							"id": 6298,
							"namakecamatan": "LASOLO"
						},
						{
							"id": 6467,
							"namakecamatan": "LASOLO"
						},
						{
							"id": 6474,
							"namakecamatan": "LASOLO KEPULAUAN"
						},
						{
							"id": 6267,
							"namakecamatan": "LASUSUA"
						},
						{
							"id": 6448,
							"namakecamatan": "LASUSUA"
						},
						{
							"id": 6276,
							"namakecamatan": "LATAMBAGA"
						},
						{
							"id": 6141,
							"namakecamatan": "LATIMOJONG"
						},
						{
							"id": 6305,
							"namakecamatan": "LATOMA"
						},
						{
							"id": 6050,
							"namakecamatan": "LAU"
						},
						{
							"id": 442,
							"namakecamatan": "LAUBALENG"
						},
						{
							"id": 5184,
							"namakecamatan": "LAUNG TUHUP"
						},
						{
							"id": 4762,
							"namakecamatan": "LAURA"
						},
						{
							"id": 75,
							"namakecamatan": "LAUT TAWAR"
						},
						{
							"id": 6503,
							"namakecamatan": "LAWA"
						},
						{
							"id": 6347,
							"namakecamatan": "LAWA"
						},
						{
							"id": 3636,
							"namakecamatan": "LAWANG"
						},
						{
							"id": 1397,
							"namakecamatan": "LAWANG KIDUL"
						},
						{
							"id": 1478,
							"namakecamatan": "LAWANG WETAN"
						},
						{
							"id": 19,
							"namakecamatan": "LAWE ALAS"
						},
						{
							"id": 26,
							"namakecamatan": "LAWE BULAN"
						},
						{
							"id": 20,
							"namakecamatan": "LAWE SIGALA-GALA"
						},
						{
							"id": 32,
							"namakecamatan": "LAWE SUMUR"
						},
						{
							"id": 3387,
							"namakecamatan": "LAWEYAN"
						},
						{
							"id": 6542,
							"namakecamatan": "LEA-LEA"
						},
						{
							"id": 3318,
							"namakecamatan": "LEBAKBARANG"
						},
						{
							"id": 4221,
							"namakecamatan": "LEBAKGEDONG"
						},
						{
							"id": 3354,
							"namakecamatan": "LEBAKSIU"
						},
						{
							"id": 2483,
							"namakecamatan": "LEBAKWANGI"
						},
						{
							"id": 4295,
							"namakecamatan": "LEBAK WANGI"
						},
						{
							"id": 4781,
							"namakecamatan": "LEBATUKAN"
						},
						{
							"id": 1623,
							"namakecamatan": "LEBONG ATAS"
						},
						{
							"id": 1716,
							"namakecamatan": "LEBONG ATAS"
						},
						{
							"id": 1722,
							"namakecamatan": "LEBONG SAKTI"
						},
						{
							"id": 1718,
							"namakecamatan": "LEBONG SELATAN"
						},
						{
							"id": 1625,
							"namakecamatan": "LEBONG SELATAN"
						},
						{
							"id": 1717,
							"namakecamatan": "LEBONG TENGAH"
						},
						{
							"id": 1624,
							"namakecamatan": "LEBONG TENGAH"
						},
						{
							"id": 1715,
							"namakecamatan": "LEBONG UTARA"
						},
						{
							"id": 3765,
							"namakecamatan": "LECES"
						},
						{
							"id": 6954,
							"namakecamatan": "LEDE"
						},
						{
							"id": 6936,
							"namakecamatan": "LEDE"
						},
						{
							"id": 4995,
							"namakecamatan": "LEDO"
						},
						{
							"id": 3693,
							"namakecamatan": "LEDOKOMBO"
						},
						{
							"id": 4244,
							"namakecamatan": "LEGOK"
						},
						{
							"id": 2652,
							"namakecamatan": "LEGONKULON"
						},
						{
							"id": 6714,
							"namakecamatan": "LEIHITU"
						},
						{
							"id": 6721,
							"namakecamatan": "LEIHITU BARAT"
						},
						{
							"id": 6841,
							"namakecamatan": "LEITIMUR SELATAN"
						},
						{
							"id": 3806,
							"namakecamatan": "LEKOK"
						},
						{
							"id": 2981,
							"namakecamatan": "LEKSONO"
						},
						{
							"id": 6835,
							"namakecamatan": "LEKSULA"
						},
						{
							"id": 6767,
							"namakecamatan": "LEKSULA"
						},
						{
							"id": 4661,
							"namakecamatan": "LELA"
						},
						{
							"id": 4735,
							"namakecamatan": "LELAK"
						},
						{
							"id": 2605,
							"namakecamatan": "LELEA"
						},
						{
							"id": 2311,
							"namakecamatan": "LELES"
						},
						{
							"id": 2368,
							"namakecamatan": "LELES"
						},
						{
							"id": 2697,
							"namakecamatan": "LEMAHABANG"
						},
						{
							"id": 2515,
							"namakecamatan": "LEMAHABANG"
						},
						{
							"id": 2549,
							"namakecamatan": "LEMAHSUGIH"
						},
						{
							"id": 2802,
							"namakecamatan": "LEMAH WUNGKUK"
						},
						{
							"id": 5008,
							"namakecamatan": "LEMBAH BAWANG"
						},
						{
							"id": 845,
							"namakecamatan": "LEMBAH GUMANTI"
						},
						{
							"id": 1219,
							"namakecamatan": "LEMBAH MASURAI"
						},
						{
							"id": 934,
							"namakecamatan": "LEMBAH MELINTANG"
						},
						{
							"id": 981,
							"namakecamatan": "LEMBAH MELINTANG"
						},
						{
							"id": 224,
							"namakecamatan": "LEMBAH SABIL"
						},
						{
							"id": 1004,
							"namakecamatan": "LEMBAH SEGAR"
						},
						{
							"id": 105,
							"namakecamatan": "LEMBAH SEULAWAH"
						},
						{
							"id": 616,
							"namakecamatan": "LEMBAH SORIK MARAPI"
						},
						{
							"id": 1407,
							"namakecamatan": "LEMBAK"
						},
						{
							"id": 2732,
							"namakecamatan": "LEMBANG"
						},
						{
							"id": 6112,
							"namakecamatan": "LEMBANG"
						},
						{
							"id": 2354,
							"namakecamatan": "LEMBANG"
						},
						{
							"id": 847,
							"namakecamatan": "LEMBANG JAYA"
						},
						{
							"id": 4399,
							"namakecamatan": "LEMBAR"
						},
						{
							"id": 5557,
							"namakecamatan": "LEMBEAN TIMUR"
						},
						{
							"id": 5704,
							"namakecamatan": "LEMBEH SELATAN"
						},
						{
							"id": 5711,
							"namakecamatan": "LEMBEH UTARA"
						},
						{
							"id": 3903,
							"namakecamatan": "LEMBEYAN"
						},
						{
							"id": 6468,
							"namakecamatan": "LEMBO"
						},
						{
							"id": 6318,
							"namakecamatan": "LEMBO"
						},
						{
							"id": 5921,
							"namakecamatan": "LEMBO"
						},
						{
							"id": 5824,
							"namakecamatan": "LEMBO"
						},
						{
							"id": 1622,
							"namakecamatan": "LEMBONG UTARA"
						},
						{
							"id": 4799,
							"namakecamatan": "LEMBOR"
						},
						{
							"id": 5838,
							"namakecamatan": "LEMBO RAYA"
						},
						{
							"id": 5920,
							"namakecamatan": "LEMBO RAYA"
						},
						{
							"id": 4805,
							"namakecamatan": "LEMBOR SELATAN"
						},
						{
							"id": 4637,
							"namakecamatan": "LEMBUR"
						},
						{
							"id": 2769,
							"namakecamatan": "LEMBURSITU"
						},
						{
							"id": 6594,
							"namakecamatan": "LEMITO"
						},
						{
							"id": 1841,
							"namakecamatan": "LEMONG"
						},
						{
							"id": 1988,
							"namakecamatan": "LEMONG"
						},
						{
							"id": 1379,
							"namakecamatan": "LEMPUING"
						},
						{
							"id": 1388,
							"namakecamatan": "LEMPUING JAYA"
						},
						{
							"id": 4460,
							"namakecamatan": "LENANGGUAR"
						},
						{
							"id": 3424,
							"namakecamatan": "LENDAH"
						},
						{
							"id": 829,
							"namakecamatan": "LENGAYANG"
						},
						{
							"id": 1363,
							"namakecamatan": "LENGKITI"
						},
						{
							"id": 2783,
							"namakecamatan": "LENGKONG"
						},
						{
							"id": 2241,
							"namakecamatan": "LENGKONG"
						},
						{
							"id": 3884,
							"namakecamatan": "LENGKONG"
						},
						{
							"id": 4082,
							"namakecamatan": "LENTENG"
						},
						{
							"id": 2036,
							"namakecamatan": "LEPAR PONGOK"
						},
						{
							"id": 4700,
							"namakecamatan": "LEPEMBUSU KELISOKE"
						},
						{
							"id": 113,
							"namakecamatan": "LEUPUNG"
						},
						{
							"id": 34,
							"namakecamatan": "LEUSER"
						},
						{
							"id": 4202,
							"namakecamatan": "LEUWIDAMAR"
						},
						{
							"id": 2370,
							"namakecamatan": "LEUWIGOONG"
						},
						{
							"id": 2208,
							"namakecamatan": "LEUWILIANG"
						},
						{
							"id": 2558,
							"namakecamatan": "LEUWIMUNDING"
						},
						{
							"id": 2233,
							"namakecamatan": "LEUWISADENG"
						},
						{
							"id": 2429,
							"namakecamatan": "LEUWISARI"
						},
						{
							"id": 4740,
							"namakecamatan": "LEWA"
						},
						{
							"id": 4755,
							"namakecamatan": "LEWA TIDAHU"
						},
						{
							"id": 4654,
							"namakecamatan": "LEWOLEMA"
						},
						{
							"id": 93,
							"namakecamatan": "LHOKNGA"
						},
						{
							"id": 149,
							"namakecamatan": "LHOKSUKON"
						},
						{
							"id": 92,
							"namakecamatan": "LHOONG"
						},
						{
							"id": 5846,
							"namakecamatan": "LIANG"
						},
						{
							"id": 5359,
							"namakecamatan": "LIANG ANGGANG"
						},
						{
							"id": 7385,
							"namakecamatan": "LI ANOGOMMA"
						},
						{
							"id": 7045,
							"namakecamatan": "LIBAREK"
						},
						{
							"id": 6017,
							"namakecamatan": "LIBURENG"
						},
						{
							"id": 3720,
							"namakecamatan": "LICIN"
						},
						{
							"id": 2564,
							"namakecamatan": "LIGUNG"
						},
						{
							"id": 5650,
							"namakecamatan": "LIKUPANG BARAT"
						},
						{
							"id": 5654,
							"namakecamatan": "LIKUPANG SELATAN"
						},
						{
							"id": 5651,
							"namakecamatan": "LIKUPANG TIMUR"
						},
						{
							"id": 6777,
							"namakecamatan": "LILIALY"
						},
						{
							"id": 6074,
							"namakecamatan": "LILIRAJA"
						},
						{
							"id": 6075,
							"namakecamatan": "LILIRILAU"
						},
						{
							"id": 876,
							"namakecamatan": "LIMA KAUM"
						},
						{
							"id": 517,
							"namakecamatan": "LIMA PULUH"
						},
						{
							"id": 1174,
							"namakecamatan": "LIMA PULUH"
						},
						{
							"id": 709,
							"namakecamatan": "LIMA PULUH"
						},
						{
							"id": 1908,
							"namakecamatan": "LIMAU"
						},
						{
							"id": 3286,
							"namakecamatan": "LIMBANGAN"
						},
						{
							"id": 6196,
							"namakecamatan": "LIMBONG"
						},
						{
							"id": 6681,
							"namakecamatan": "LIMBORO"
						},
						{
							"id": 6544,
							"namakecamatan": "LIMBOTO"
						},
						{
							"id": 6560,
							"namakecamatan": "LIMBOTO BARAT"
						},
						{
							"id": 1294,
							"namakecamatan": "LIMBUR LUBUKMENGKUANG"
						},
						{
							"id": 2821,
							"namakecamatan": "LIMO"
						},
						{
							"id": 5308,
							"namakecamatan": "LIMPASU"
						},
						{
							"id": 3308,
							"namakecamatan": "LIMPUNG"
						},
						{
							"id": 1236,
							"namakecamatan": "LIMUN"
						},
						{
							"id": 5899,
							"namakecamatan": "LINDU"
						},
						{
							"id": 5798,
							"namakecamatan": "LINDU"
						},
						{
							"id": 59,
							"namakecamatan": "LINGE"
						},
						{
							"id": 2119,
							"namakecamatan": "LINGGA"
						},
						{
							"id": 2070,
							"namakecamatan": "LINGGA"
						},
						{
							"id": 621,
							"namakecamatan": "LINGGA BAYU"
						},
						{
							"id": 5415,
							"namakecamatan": "LINGGANG BIGUNG"
						},
						{
							"id": 2124,
							"namakecamatan": "LINGGA TIMUR"
						},
						{
							"id": 2079,
							"namakecamatan": "LINGGA UTARA"
						},
						{
							"id": 2122,
							"namakecamatan": "LINGGA UTARA"
						},
						{
							"id": 835,
							"namakecamatan": "LINGGO SARI BAGANTI"
						},
						{
							"id": 4398,
							"namakecamatan": "LINGSAR"
						},
						{
							"id": 1558,
							"namakecamatan": "LINTANG KANAN"
						},
						{
							"id": 1428,
							"namakecamatan": "LINTANG KANAN"
						},
						{
							"id": 878,
							"namakecamatan": "LINTAU BUO"
						},
						{
							"id": 885,
							"namakecamatan": "LINTAU BUO UTARA"
						},
						{
							"id": 674,
							"namakecamatan": "LINTONG NIHUTA"
						},
						{
							"id": 4691,
							"namakecamatan": "LIO TIMUR"
						},
						{
							"id": 1051,
							"namakecamatan": "LIRIK"
						},
						{
							"id": 5603,
							"namakecamatan": "LIRUNG"
						},
						{
							"id": 6054,
							"namakecamatan": "LIUKANG KALMAS"
						},
						{
							"id": 6053,
							"namakecamatan": "LIUKANG TANGAYA"
						},
						{
							"id": 6055,
							"namakecamatan": "LIUKANG TUPABBIRING"
						},
						{
							"id": 6065,
							"namakecamatan": "LIUKANG TUPABBIRINGUTARA"
						},
						{
							"id": 5372,
							"namakecamatan": "LOA JANAN"
						},
						{
							"id": 5464,
							"namakecamatan": "LOA JANAN ILIR"
						},
						{
							"id": 5371,
							"namakecamatan": "LOA KULU"
						},
						{
							"id": 2975,
							"namakecamatan": "LOANO"
						},
						{
							"id": 4789,
							"namakecamatan": "LOBALAIN"
						},
						{
							"id": 5736,
							"namakecamatan": "LOBU"
						},
						{
							"id": 3869,
							"namakecamatan": "LOCERET"
						},
						{
							"id": 6483,
							"namakecamatan": "LOEA"
						},
						{
							"id": 6288,
							"namakecamatan": "LOEA"
						},
						{
							"id": 1156,
							"namakecamatan": "LOGAS TANAH DARAT"
						},
						{
							"id": 2618,
							"namakecamatan": "LOHBENER"
						},
						{
							"id": 6343,
							"namakecamatan": "LOHIA"
						},
						{
							"id": 5284,
							"namakecamatan": "LOKPAIKAT"
						},
						{
							"id": 5296,
							"namakecamatan": "LOKSADO"
						},
						{
							"id": 5529,
							"namakecamatan": "LOLAK"
						},
						{
							"id": 7324,
							"namakecamatan": "LOLAT"
						},
						{
							"id": 5531,
							"namakecamatan": "LOLAYAN"
						},
						{
							"id": 4769,
							"namakecamatan": "LOLI"
						},
						{
							"id": 6848,
							"namakecamatan": "LOLODA"
						},
						{
							"id": 6884,
							"namakecamatan": "LOLODA KEPULAUAN"
						},
						{
							"id": 6874,
							"namakecamatan": "LOLODA UTARA"
						},
						{
							"id": 382,
							"namakecamatan": "LOLOFITU MOI"
						},
						{
							"id": 764,
							"namakecamatan": "LOLOFITU MOI"
						},
						{
							"id": 631,
							"namakecamatan": "LOLOMATUA"
						},
						{
							"id": 6773,
							"namakecamatan": "LOLONG GUBA"
						},
						{
							"id": 5401,
							"namakecamatan": "LONG APARI"
						},
						{
							"id": 5447,
							"namakecamatan": "LONG APARI"
						},
						{
							"id": 5444,
							"namakecamatan": "LONG BAGUN"
						},
						{
							"id": 5403,
							"namakecamatan": "LONG BAGUN"
						},
						{
							"id": 5404,
							"namakecamatan": "LONG HUBUNG"
						},
						{
							"id": 5445,
							"namakecamatan": "LONG HUBUNG"
						},
						{
							"id": 5365,
							"namakecamatan": "LONG IKIS"
						},
						{
							"id": 5405,
							"namakecamatan": "LONG IRAM"
						},
						{
							"id": 5367,
							"namakecamatan": "LONG KALI"
						},
						{
							"id": 197,
							"namakecamatan": "LONGKIB"
						},
						{
							"id": 309,
							"namakecamatan": "LONGKIB"
						},
						{
							"id": 5439,
							"namakecamatan": "LONG MESANGAT"
						},
						{
							"id": 5448,
							"namakecamatan": "LONG PAHANGAI"
						},
						{
							"id": 5402,
							"namakecamatan": "LONG PAHANGAI"
						},
						{
							"id": 4459,
							"namakecamatan": "LOPOK"
						},
						{
							"id": 5766,
							"namakecamatan": "LORE BARAT"
						},
						{
							"id": 5768,
							"namakecamatan": "LORE PIORE"
						},
						{
							"id": 5752,
							"namakecamatan": "LORE SELATAN"
						},
						{
							"id": 5751,
							"namakecamatan": "LORE TENGAH"
						},
						{
							"id": 5767,
							"namakecamatan": "LORE TIMUR"
						},
						{
							"id": 5750,
							"namakecamatan": "LORE UTARA"
						},
						{
							"id": 2620,
							"namakecamatan": "LOSARANG"
						},
						{
							"id": 3378,
							"namakecamatan": "LOSARI"
						},
						{
							"id": 2511,
							"namakecamatan": "LOSARI"
						},
						{
							"id": 747,
							"namakecamatan": "LOTU"
						},
						{
							"id": 388,
							"namakecamatan": "LOTU"
						},
						{
							"id": 4819,
							"namakecamatan": "LOURA"
						},
						{
							"id": 4113,
							"namakecamatan": "LOWOKWARU"
						},
						{
							"id": 923,
							"namakecamatan": "LUAK"
						},
						{
							"id": 1680,
							"namakecamatan": "LUAS"
						},
						{
							"id": 1404,
							"namakecamatan": "LUBAI"
						},
						{
							"id": 1415,
							"namakecamatan": "LUBAI ULU"
						},
						{
							"id": 867,
							"namakecamatan": "LUBUAK TAROK"
						},
						{
							"id": 887,
							"namakecamatan": "LUBUK ALUNG"
						},
						{
							"id": 2140,
							"namakecamatan": "LUBUK BAJA"
						},
						{
							"id": 371,
							"namakecamatan": "LUBUK BARUMUN"
						},
						{
							"id": 725,
							"namakecamatan": "LUBUK BARUMUN"
						},
						{
							"id": 905,
							"namakecamatan": "LUBUK BASUNG"
						},
						{
							"id": 1357,
							"namakecamatan": "LUBUK BATANG"
						},
						{
							"id": 1054,
							"namakecamatan": "LUBUK BATU JAYA"
						},
						{
							"id": 996,
							"namakecamatan": "LUBUK BEGALUNG"
						},
						{
							"id": 2048,
							"namakecamatan": "LUBUK BESAR"
						},
						{
							"id": 1143,
							"namakecamatan": "LUBUK DALAM"
						},
						{
							"id": 1552,
							"namakecamatan": "LUBUK KELIAT"
						},
						{
							"id": 997,
							"namakecamatan": "LUBUK KILANGAN"
						},
						{
							"id": 1598,
							"namakecamatan": "LUBUK LINGGAU BARAT I"
						},
						{
							"id": 1602,
							"namakecamatan": "LUBUK LINGGAU BARAT II"
						},
						{
							"id": 1599,
							"namakecamatan": "LUBUK LINGGAU SELATANI"
						},
						{
							"id": 1603,
							"namakecamatan": "LUBUK LINGGAU SELATANII"
						},
						{
							"id": 1597,
							"namakecamatan": "LUBUK LINGGAU TIMUR I"
						},
						{
							"id": 1601,
							"namakecamatan": "LUBUK LINGGAU TIMURII"
						},
						{
							"id": 1600,
							"namakecamatan": "LUBUK LINGGAU UTARA I"
						},
						{
							"id": 1604,
							"namakecamatan": "LUBUK LINGGAU UTARA II"
						},
						{
							"id": 478,
							"namakecamatan": "LUBUK PAKAM"
						},
						{
							"id": 1700,
							"namakecamatan": "LUBUK PINANG"
						},
						{
							"id": 1365,
							"namakecamatan": "LUBUK RAJA"
						},
						{
							"id": 1692,
							"namakecamatan": "LUBUK SANDI"
						},
						{
							"id": 937,
							"namakecamatan": "LUBUK SIKAPING"
						},
						{
							"id": 1002,
							"namakecamatan": "LUBUK SIKARAH"
						},
						{
							"id": 289,
							"namakecamatan": "LUENG BATA"
						},
						{
							"id": 989,
							"namakecamatan": "LUHAK NAN DUO"
						},
						{
							"id": 3654,
							"namakecamatan": "LUMAJANG"
						},
						{
							"id": 5005,
							"namakecamatan": "LUMAR"
						},
						{
							"id": 3788,
							"namakecamatan": "LUMBANG"
						},
						{
							"id": 3784,
							"namakecamatan": "LUMBANG"
						},
						{
							"id": 592,
							"namakecamatan": "LUMBAN JULU"
						},
						{
							"id": 2870,
							"namakecamatan": "LUMBIR"
						},
						{
							"id": 5496,
							"namakecamatan": "LUMBIS"
						},
						{
							"id": 5507,
							"namakecamatan": "LUMBIS OGONG"
						},
						{
							"id": 1850,
							"namakecamatan": "LUMBOK SEMINUNG"
						},
						{
							"id": 2474,
							"namakecamatan": "LUMBUNG"
						},
						{
							"id": 7164,
							"namakecamatan": "LUMO"
						},
						{
							"id": 328,
							"namakecamatan": "LUMUT"
						},
						{
							"id": 836,
							"namakecamatan": "LUNANG"
						},
						{
							"id": 1683,
							"namakecamatan": "LUNGKANG KULE"
						},
						{
							"id": 4435,
							"namakecamatan": "LUNYUK"
						},
						{
							"id": 2482,
							"namakecamatan": "LURAGUNG"
						},
						{
							"id": 5724,
							"namakecamatan": "LUWUK"
						},
						{
							"id": 5740,
							"namakecamatan": "LUWUK SELATAN"
						},
						{
							"id": 5731,
							"namakecamatan": "LUWUK TIMUR"
						},
						{
							"id": 5741,
							"namakecamatan": "LUWUK UTARA"
						},
						{
							"id": 6680,
							"namakecamatan": "LUYO"
						},
						{
							"id": 6938,
							"namakecamatan": "MABA"
						},
						{
							"id": 6939,
							"namakecamatan": "MABA SELATAN"
						},
						{
							"id": 6944,
							"namakecamatan": "MABA TENGAH"
						},
						{
							"id": 6945,
							"namakecamatan": "MABA UTARA"
						},
						{
							"id": 7568,
							"namakecamatan": "MABUGI"
						},
						{
							"id": 4797,
							"namakecamatan": "MACANG PACAR"
						},
						{
							"id": 515,
							"namakecamatan": "MADANG BERAS"
						},
						{
							"id": 1505,
							"namakecamatan": "MADANG SUKU I"
						},
						{
							"id": 1359,
							"namakecamatan": "MADANG SUKU I"
						},
						{
							"id": 1358,
							"namakecamatan": "MADANG SUKU II"
						},
						{
							"id": 1504,
							"namakecamatan": "MADANG SUKU II"
						},
						{
							"id": 1511,
							"namakecamatan": "MADANG SUKU III"
						},
						{
							"id": 4483,
							"namakecamatan": "MADAPANGGA"
						},
						{
							"id": 46,
							"namakecamatan": "MADAT"
						},
						{
							"id": 5705,
							"namakecamatan": "MADIDIR"
						},
						{
							"id": 3893,
							"namakecamatan": "MADIUN"
						},
						{
							"id": 2922,
							"namakecamatan": "MADUKARA"
						},
						{
							"id": 3995,
							"namakecamatan": "MADURAN"
						},
						{
							"id": 5710,
							"namakecamatan": "MAESA"
						},
						{
							"id": 5637,
							"namakecamatan": "MAESAAN"
						},
						{
							"id": 3721,
							"namakecamatan": "MAESAN"
						},
						{
							"id": 7565,
							"namakecamatan": "MAGE'ABUME"
						},
						{
							"id": 3384,
							"namakecamatan": "MAGELANG SELATAN"
						},
						{
							"id": 3386,
							"namakecamatan": "MAGELANG TENGAH"
						},
						{
							"id": 3385,
							"namakecamatan": "MAGELANG UTARA"
						},
						{
							"id": 4670,
							"namakecamatan": "MAGEPANDA"
						},
						{
							"id": 4124,
							"namakecamatan": "MAGERSARI"
						},
						{
							"id": 3906,
							"namakecamatan": "MAGETAN"
						},
						{
							"id": 6327,
							"namakecamatan": "MAGINTI"
						},
						{
							"id": 6506,
							"namakecamatan": "MAGINTI"
						},
						{
							"id": 4759,
							"namakecamatan": "MAHU"
						},
						{
							"id": 5172,
							"namakecamatan": "MAHUNING RAYA"
						},
						{
							"id": 7059,
							"namakecamatan": "MAIMA"
						},
						{
							"id": 6118,
							"namakecamatan": "MAIWA"
						},
						{
							"id": 4209,
							"namakecamatan": "MAJA"
						},
						{
							"id": 2554,
							"namakecamatan": "MAJA"
						},
						{
							"id": 2346,
							"namakecamatan": "MAJALAYA"
						},
						{
							"id": 2699,
							"namakecamatan": "MAJALAYA"
						},
						{
							"id": 2555,
							"namakecamatan": "MAJALENGKA"
						},
						{
							"id": 4195,
							"namakecamatan": "MAJASARI"
						},
						{
							"id": 6085,
							"namakecamatan": "MAJAULENG"
						},
						{
							"id": 1676,
							"namakecamatan": "MAJE"
						},
						{
							"id": 2859,
							"namakecamatan": "MAJENANG"
						},
						{
							"id": 6156,
							"namakecamatan": "MAKALE"
						},
						{
							"id": 6180,
							"namakecamatan": "MAKALE SELATAN"
						},
						{
							"id": 6178,
							"namakecamatan": "MAKALE UTARA"
						},
						{
							"id": 1488,
							"namakecamatan": "MAKARTI JAYA"
						},
						{
							"id": 6238,
							"namakecamatan": "MAKASAR"
						},
						{
							"id": 2192,
							"namakecamatan": "MAKASAR"
						},
						{
							"id": 7603,
							"namakecamatan": "MAKBON"
						},
						{
							"id": 6897,
							"namakecamatan": "MAKIAN BARAT"
						},
						{
							"id": 7092,
							"namakecamatan": "MAKIMI"
						},
						{
							"id": 7481,
							"namakecamatan": "MAKKI"
						},
						{
							"id": 6999,
							"namakecamatan": "MAKKI"
						},
						{
							"id": 204,
							"namakecamatan": "MAKMUR"
						},
						{
							"id": 7650,
							"namakecamatan": "MALABOTOM"
						},
						{
							"id": 7877,
							"namakecamatan": "MALADUM MES"
						},
						{
							"id": 7876,
							"namakecamatan": "MALAIMSIMSA"
						},
						{
							"id": 4608,
							"namakecamatan": "MALAKA BARAT"
						},
						{
							"id": 4846,
							"namakecamatan": "MALAKA BARAT"
						},
						{
							"id": 4845,
							"namakecamatan": "MALAKA TENGAH"
						},
						{
							"id": 4606,
							"namakecamatan": "MALAKA TENGAH"
						},
						{
							"id": 4604,
							"namakecamatan": "MALAKA TIMUR"
						},
						{
							"id": 4853,
							"namakecamatan": "MALAKA TIMUR"
						},
						{
							"id": 919,
							"namakecamatan": "MALALAK"
						},
						{
							"id": 5701,
							"namakecamatan": "MALALAYANG"
						},
						{
							"id": 2373,
							"namakecamatan": "MALANGBONG"
						},
						{
							"id": 6192,
							"namakecamatan": "MALANGKE"
						},
						{
							"id": 6199,
							"namakecamatan": "MALANGKE BARAT"
						},
						{
							"id": 2574,
							"namakecamatan": "MALAUSMA"
						},
						{
							"id": 2506,
							"namakecamatan": "MALEBER"
						},
						{
							"id": 6873,
							"namakecamatan": "MALIFUT"
						},
						{
							"id": 6333,
							"namakecamatan": "MALIGANO"
						},
						{
							"id": 5179,
							"namakecamatan": "MALIKU"
						},
						{
							"id": 6207,
							"namakecamatan": "MALILI"
						},
						{
							"id": 6186,
							"namakecamatan": "MALIMBONG BALEPE"
						},
						{
							"id": 5485,
							"namakecamatan": "MALINAU BARAT"
						},
						{
							"id": 5479,
							"namakecamatan": "MALINAU KOTA"
						},
						{
							"id": 5483,
							"namakecamatan": "MALINAU SELATAN"
						},
						{
							"id": 5490,
							"namakecamatan": "MALINAU SELATAN HILIR"
						},
						{
							"id": 5491,
							"namakecamatan": "MALINAU SELATAN HULU"
						},
						{
							"id": 5484,
							"namakecamatan": "MALINAU UTARA"
						},
						{
							"id": 6988,
							"namakecamatan": "MALIND"
						},
						{
							"id": 1705,
							"namakecamatan": "MALIN DEMAN"
						},
						{
							"id": 4197,
							"namakecamatan": "MALINGPING"
						},
						{
							"id": 6044,
							"namakecamatan": "MALLLAWA"
						},
						{
							"id": 6070,
							"namakecamatan": "MALLUSETASI"
						},
						{
							"id": 3954,
							"namakecamatan": "MALO"
						},
						{
							"id": 6126,
							"namakecamatan": "MALUA"
						},
						{
							"id": 4496,
							"namakecamatan": "MALUK"
						},
						{
							"id": 6690,
							"namakecamatan": "MALUNDA"
						},
						{
							"id": 7534,
							"namakecamatan": "MAM"
						},
						{
							"id": 6237,
							"namakecamatan": "MAMAJANG"
						},
						{
							"id": 6656,
							"namakecamatan": "MAMASA"
						},
						{
							"id": 7465,
							"namakecamatan": "MAMBERAMO HILIR"
						},
						{
							"id": 7462,
							"namakecamatan": "MAMBERAMO HULU"
						},
						{
							"id": 7234,
							"namakecamatan": "MAMBERAMO TENGAH"
						},
						{
							"id": 7461,
							"namakecamatan": "MAMBERAMO TENGAH"
						},
						{
							"id": 7464,
							"namakecamatan": "MAMBERAMO TENGAHTIMUR"
						},
						{
							"id": 7238,
							"namakecamatan": "MAMBERAMO TENGAHTIMUR"
						},
						{
							"id": 6654,
							"namakecamatan": "MAMBI"
						},
						{
							"id": 7423,
							"namakecamatan": "MAMBIOMAN BAPAI"
						},
						{
							"id": 4816,
							"namakecamatan": "MAMBORO"
						},
						{
							"id": 4764,
							"namakecamatan": "MAMBORO"
						},
						{
							"id": 5926,
							"namakecamatan": "MAMOSALATO"
						},
						{
							"id": 5835,
							"namakecamatan": "MAMOSALATO"
						},
						{
							"id": 2177,
							"namakecamatan": "MAMPANG PRAPATAN"
						},
						{
							"id": 6638,
							"namakecamatan": "MAMUJU"
						},
						{
							"id": 6572,
							"namakecamatan": "MANANGGU"
						},
						{
							"id": 4292,
							"namakecamatan": "MANCAK"
						},
						{
							"id": 1076,
							"namakecamatan": "MANDAH"
						},
						{
							"id": 6039,
							"namakecamatan": "MANDAI"
						},
						{
							"id": 2800,
							"namakecamatan": "MANDALAJATI"
						},
						{
							"id": 4178,
							"namakecamatan": "MANDALAWANGI"
						},
						{
							"id": 6063,
							"namakecamatan": "MANDALLE"
						},
						{
							"id": 5263,
							"namakecamatan": "MANDASTANA"
						},
						{
							"id": 1065,
							"namakecamatan": "MANDAU"
						},
						{
							"id": 5104,
							"namakecamatan": "MANDAU TALAWANG"
						},
						{
							"id": 2289,
							"namakecamatan": "MANDE"
						},
						{
							"id": 1240,
							"namakecamatan": "MANDIANGIN"
						},
						{
							"id": 1011,
							"namakecamatan": "MANDIANGIN K. SELAYAN"
						},
						{
							"id": 4078,
							"namakecamatan": "MANDING"
						},
						{
							"id": 6906,
							"namakecamatan": "MANDIOLI SELATAN"
						},
						{
							"id": 6907,
							"namakecamatan": "MANDIOLI UTARA"
						},
						{
							"id": 2917,
							"namakecamatan": "MANDIRAJA"
						},
						{
							"id": 2490,
							"namakecamatan": "MANDIRANCAN"
						},
						{
							"id": 7402,
							"namakecamatan": "MANDOBO"
						},
						{
							"id": 5575,
							"namakecamatan": "MANDOLANG"
						},
						{
							"id": 6526,
							"namakecamatan": "MANDONGA"
						},
						{
							"id": 5013,
							"namakecamatan": "MANDOR"
						},
						{
							"id": 762,
							"namakecamatan": "MANDREHE"
						},
						{
							"id": 383,
							"namakecamatan": "MANDREHE"
						},
						{
							"id": 760,
							"namakecamatan": "MANDREHE BARAT"
						},
						{
							"id": 397,
							"namakecamatan": "MANDREHE BARAT"
						},
						{
							"id": 763,
							"namakecamatan": "MANDREHE UTARA"
						},
						{
							"id": 393,
							"namakecamatan": "MANDREHE UTARA"
						},
						{
							"id": 314,
							"namakecamatan": "MANDUAMAS"
						},
						{
							"id": 141,
							"namakecamatan": "MANE"
						},
						{
							"id": 7823,
							"namakecamatan": "MANEKAR"
						},
						{
							"id": 5590,
							"namakecamatan": "MANGANITU"
						},
						{
							"id": 5587,
							"namakecamatan": "MANGANITU SELATAN"
						},
						{
							"id": 5977,
							"namakecamatan": "MANGARABOMBANG"
						},
						{
							"id": 3752,
							"namakecamatan": "MANGARAN"
						},
						{
							"id": 6247,
							"namakecamatan": "MANGGALA"
						},
						{
							"id": 4469,
							"namakecamatan": "MANGGALEWA"
						},
						{
							"id": 2055,
							"namakecamatan": "MANGGAR"
						},
						{
							"id": 7413,
							"namakecamatan": "MANGGELUM"
						},
						{
							"id": 218,
							"namakecamatan": "MANGGENG"
						},
						{
							"id": 4368,
							"namakecamatan": "MANGGIS"
						},
						{
							"id": 2839,
							"namakecamatan": "MANGKUBUMI"
						},
						{
							"id": 6204,
							"namakecamatan": "MANGKUTANA"
						},
						{
							"id": 6923,
							"namakecamatan": "MANGOLI BARAT"
						},
						{
							"id": 6929,
							"namakecamatan": "MANGOLI SELATAN"
						},
						{
							"id": 6928,
							"namakecamatan": "MANGOLI TENGAH"
						},
						{
							"id": 6918,
							"namakecamatan": "MANGOLI TIMUR"
						},
						{
							"id": 6930,
							"namakecamatan": "MANGOLI UTARA"
						},
						{
							"id": 6927,
							"namakecamatan": "MANGOLI UTARA TIMUR"
						},
						{
							"id": 4126,
							"namakecamatan": "MANGUHARJO"
						},
						{
							"id": 2753,
							"namakecamatan": "MANGUNJAYA"
						},
						{
							"id": 2476,
							"namakecamatan": "MANGUNJAYA"
						},
						{
							"id": 2426,
							"namakecamatan": "MANGUNREJA"
						},
						{
							"id": 640,
							"namakecamatan": "MANIAMOLO"
						},
						{
							"id": 6089,
							"namakecamatan": "MANIANGPAJO"
						},
						{
							"id": 2668,
							"namakecamatan": "MANIIS"
						},
						{
							"id": 7765,
							"namakecamatan": "MANIMERI"
						},
						{
							"id": 4924,
							"namakecamatan": "MANIS MATA"
						},
						{
							"id": 3040,
							"namakecamatan": "MANISRENGGO"
						},
						{
							"id": 1614,
							"namakecamatan": "MANNA"
						},
						{
							"id": 7251,
							"namakecamatan": "MANNEM"
						},
						{
							"id": 7657,
							"namakecamatan": "MANOKWARI"
						},
						{
							"id": 7668,
							"namakecamatan": "MANOKWARI BARAT"
						},
						{
							"id": 7671,
							"namakecamatan": "MANOKWARI SELATAN"
						},
						{
							"id": 7669,
							"namakecamatan": "MANOKWARI TIMUR"
						},
						{
							"id": 7670,
							"namakecamatan": "MANOKWARI UTARA"
						},
						{
							"id": 2423,
							"namakecamatan": "MANONJAYA"
						},
						{
							"id": 2081,
							"namakecamatan": "MANTANG"
						},
						{
							"id": 5098,
							"namakecamatan": "MANTANGAI"
						},
						{
							"id": 5338,
							"namakecamatan": "MANTEWE"
						},
						{
							"id": 5935,
							"namakecamatan": "MANTIKULORE"
						},
						{
							"id": 3931,
							"namakecamatan": "MANTINGAN"
						},
						{
							"id": 5742,
							"namakecamatan": "MANTOH"
						},
						{
							"id": 3491,
							"namakecamatan": "MANTRIJERON"
						},
						{
							"id": 4001,
							"namakecamatan": "MANTUP"
						},
						{
							"id": 5167,
							"namakecamatan": "MANUHING"
						},
						{
							"id": 5998,
							"namakecamatan": "MANUJU"
						},
						{
							"id": 255,
							"namakecamatan": "MANYAK PAYED"
						},
						{
							"id": 4022,
							"namakecamatan": "MANYAR"
						},
						{
							"id": 3079,
							"namakecamatan": "MANYARAN"
						},
						{
							"id": 6740,
							"namakecamatan": "MANYEUW"
						},
						{
							"id": 2852,
							"namakecamatan": "MAOS"
						},
						{
							"id": 3911,
							"namakecamatan": "MAOSPATI"
						},
						{
							"id": 5700,
							"namakecamatan": "MAPANGET"
						},
						{
							"id": 940,
							"namakecamatan": "MAPAT TUNGGUL"
						},
						{
							"id": 947,
							"namakecamatan": "MAPAT TUNGGUL SELATAN"
						},
						{
							"id": 7002,
							"namakecamatan": "MAPENDUMA"
						},
						{
							"id": 7519,
							"namakecamatan": "MAPENDUMA"
						},
						{
							"id": 7086,
							"namakecamatan": "MAPIA"
						},
						{
							"id": 7576,
							"namakecamatan": "MAPIA"
						},
						{
							"id": 7580,
							"namakecamatan": "MAPIA BARAT"
						},
						{
							"id": 7094,
							"namakecamatan": "MAPIA BARAT"
						},
						{
							"id": 7583,
							"namakecamatan": "MAPIA TENGAH"
						},
						{
							"id": 7100,
							"namakecamatan": "MAPIA TENGAH"
						},
						{
							"id": 6678,
							"namakecamatan": "MAPILLI"
						},
						{
							"id": 4679,
							"namakecamatan": "MAPITARA"
						},
						{
							"id": 6179,
							"namakecamatan": "MAPPAK"
						},
						{
							"id": 5976,
							"namakecamatan": "MAPPAKASUNGGU"
						},
						{
							"id": 6201,
							"namakecamatan": "MAPPEDECENG"
						},
						{
							"id": 5272,
							"namakecamatan": "MARABAHAN"
						},
						{
							"id": 364,
							"namakecamatan": "MARANCAR"
						},
						{
							"id": 6060,
							"namakecamatan": "MARANG"
						},
						{
							"id": 5386,
							"namakecamatan": "MARANG KAYU"
						},
						{
							"id": 5398,
							"namakecamatan": "MARATUA"
						},
						{
							"id": 4923,
							"namakecamatan": "MARAU"
						},
						{
							"id": 5909,
							"namakecamatan": "MARAWOLA"
						},
						{
							"id": 5777,
							"namakecamatan": "MARAWOLA"
						},
						{
							"id": 5910,
							"namakecamatan": "MARAWOLA BARAT"
						},
						{
							"id": 5793,
							"namakecamatan": "MARAWOLA BARAT"
						},
						{
							"id": 552,
							"namakecamatan": "MARBAU"
						},
						{
							"id": 743,
							"namakecamatan": "MARBAU"
						},
						{
							"id": 443,
							"namakecamatan": "MARDINGDING"
						},
						{
							"id": 6018,
							"namakecamatan": "MARE"
						},
						{
							"id": 7838,
							"namakecamatan": "MARE"
						},
						{
							"id": 7710,
							"namakecamatan": "MARE"
						},
						{
							"id": 7624,
							"namakecamatan": "MARE"
						},
						{
							"id": 7851,
							"namakecamatan": "MARE SELATAN"
						},
						{
							"id": 4341,
							"namakecamatan": "MARGA"
						},
						{
							"id": 2323,
							"namakecamatan": "MARGAASIH"
						},
						{
							"id": 3419,
							"namakecamatan": "MARGADANA"
						},
						{
							"id": 2322,
							"namakecamatan": "MARGAHAYU"
						},
						{
							"id": 1958,
							"namakecamatan": "MARGA PUNDUH"
						},
						{
							"id": 1670,
							"namakecamatan": "MARGA SAKTI SEBELAT"
						},
						{
							"id": 3349,
							"namakecamatan": "MARGASARI"
						},
						{
							"id": 1936,
							"namakecamatan": "MARGA SEKAMPUNG"
						},
						{
							"id": 1923,
							"namakecamatan": "MARGA TIGA"
						},
						{
							"id": 3959,
							"namakecamatan": "MARGOMULYO"
						},
						{
							"id": 3192,
							"namakecamatan": "MARGOREJO"
						},
						{
							"id": 1231,
							"namakecamatan": "MARGO TABIR"
						},
						{
							"id": 3196,
							"namakecamatan": "MARGOYOSO"
						},
						{
							"id": 7640,
							"namakecamatan": "MARIAT"
						},
						{
							"id": 5128,
							"namakecamatan": "MARIKIT"
						},
						{
							"id": 6077,
							"namakecamatan": "MARIORIAWA"
						},
						{
							"id": 6073,
							"namakecamatan": "MARIORIWAWO"
						},
						{
							"id": 6596,
							"namakecamatan": "MARISA"
						},
						{
							"id": 6236,
							"namakecamatan": "MARISO"
						},
						{
							"id": 6101,
							"namakecamatan": "MARITENGNGAE"
						},
						{
							"id": 6356,
							"namakecamatan": "MAROBO"
						},
						{
							"id": 3777,
							"namakecamatan": "MARON"
						},
						{
							"id": 4457,
							"namakecamatan": "MARONGE"
						},
						{
							"id": 6042,
							"namakecamatan": "MAROS BARU"
						},
						{
							"id": 1256,
							"namakecamatan": "MARO SEBO"
						},
						{
							"id": 1252,
							"namakecamatan": "MARO SEBO ILIR"
						},
						{
							"id": 1250,
							"namakecamatan": "MARO SEBO ULU"
						},
						{
							"id": 1179,
							"namakecamatan": "MARPOYAN DAMAI"
						},
						{
							"id": 1341,
							"namakecamatan": "MARTAPURA"
						},
						{
							"id": 1499,
							"namakecamatan": "MARTAPURA"
						},
						{
							"id": 5243,
							"namakecamatan": "MARTAPURA"
						},
						{
							"id": 5252,
							"namakecamatan": "MARTAPURA BARAT"
						},
						{
							"id": 5253,
							"namakecamatan": "MARTAPURA TIMUR"
						},
						{
							"id": 6046,
							"namakecamatan": "MARUSU"
						},
						{
							"id": 4098,
							"namakecamatan": "MASALEMBU"
						},
						{
							"id": 6128,
							"namakecamatan": "MASALLE"
						},
						{
							"id": 5730,
							"namakecamatan": "MASAMA"
						},
						{
							"id": 6194,
							"namakecamatan": "MASAMBA"
						},
						{
							"id": 6182,
							"namakecamatan": "MASANDA"
						},
						{
							"id": 3114,
							"namakecamatan": "MASARAN"
						},
						{
							"id": 4418,
							"namakecamatan": "MASBAGIK"
						},
						{
							"id": 7390,
							"namakecamatan": "MASIREI"
						},
						{
							"id": 7661,
							"namakecamatan": "MASNI"
						},
						{
							"id": 7771,
							"namakecamatan": "MASYETA"
						},
						{
							"id": 6684,
							"namakecamatan": "MATAKALI"
						},
						{
							"id": 150,
							"namakecamatan": "MATANGKULI"
						},
						{
							"id": 6679,
							"namakecamatan": "MATANGNGA"
						},
						{
							"id": 4933,
							"namakecamatan": "MATAN HILIR SELATAN"
						},
						{
							"id": 4922,
							"namakecamatan": "MATAN HILIR UTARA"
						},
						{
							"id": 6425,
							"namakecamatan": "MATA OLEO"
						},
						{
							"id": 4503,
							"namakecamatan": "MATARAM"
						},
						{
							"id": 5250,
							"namakecamatan": "MATARAMAN"
						},
						{
							"id": 1928,
							"namakecamatan": "MATARAM BARU"
						},
						{
							"id": 4634,
							"namakecamatan": "MATARU"
						},
						{
							"id": 6439,
							"namakecamatan": "MATA USU"
						},
						{
							"id": 4752,
							"namakecamatan": "MATAWAI LA PAWU"
						},
						{
							"id": 7723,
							"namakecamatan": "MATEMANI"
						},
						{
							"id": 3099,
							"namakecamatan": "MATESIH"
						},
						{
							"id": 6106,
							"namakecamatan": "MATIRRO SOMPE"
						},
						{
							"id": 2185,
							"namakecamatan": "MATRAMAN"
						},
						{
							"id": 6108,
							"namakecamatan": "MATTIRO BULU"
						},
						{
							"id": 5708,
							"namakecamatan": "MATUARI"
						},
						{
							"id": 907,
							"namakecamatan": "MATUR"
						},
						{
							"id": 403,
							"namakecamatan": "MA'U"
						},
						{
							"id": 7622,
							"namakecamatan": "MAUDUS"
						},
						{
							"id": 4232,
							"namakecamatan": "MAUK"
						},
						{
							"id": 4690,
							"namakecamatan": "MAUKARO"
						},
						{
							"id": 4858,
							"namakecamatan": "MAULAFA"
						},
						{
							"id": 4810,
							"namakecamatan": "MAUPONGGO"
						},
						{
							"id": 4703,
							"namakecamatan": "MAUPONGO"
						},
						{
							"id": 4689,
							"namakecamatan": "MAUROLE"
						},
						{
							"id": 7820,
							"namakecamatan": "MAWABUAN"
						},
						{
							"id": 6369,
							"namakecamatan": "MAWASANGKA"
						},
						{
							"id": 6515,
							"namakecamatan": "MAWASANGKA"
						},
						{
							"id": 6514,
							"namakecamatan": "MAWASANGKA TENGAH"
						},
						{
							"id": 6389,
							"namakecamatan": "MAWASANGKA TENGAH"
						},
						{
							"id": 6370,
							"namakecamatan": "MAWASANGKA TIMUR"
						},
						{
							"id": 6513,
							"namakecamatan": "MAWASANGKA TIMUR"
						},
						{
							"id": 7615,
							"namakecamatan": "MAYAMUK"
						},
						{
							"id": 3691,
							"namakecamatan": "MAYANG"
						},
						{
							"id": 4116,
							"namakecamatan": "MAYANGAN"
						},
						{
							"id": 3214,
							"namakecamatan": "MAYONG"
						},
						{
							"id": 643,
							"namakecamatan": "MAZINO"
						},
						{
							"id": 647,
							"namakecamatan": "MAZO"
						},
						{
							"id": 7700,
							"namakecamatan": "MBAHAMDANDARA"
						},
						{
							"id": 4806,
							"namakecamatan": "MBELILING"
						},
						{
							"id": 7540,
							"namakecamatan": "MBUA TENGAH"
						},
						{
							"id": 7539,
							"namakecamatan": "MBULMU YALMA"
						},
						{
							"id": 7027,
							"namakecamatan": "MBUWA"
						},
						{
							"id": 7524,
							"namakecamatan": "MBUWA"
						},
						{
							"id": 7529,
							"namakecamatan": "MEBAROK"
						},
						{
							"id": 774,
							"namakecamatan": "MEDAN AMPLAS"
						},
						{
							"id": 775,
							"namakecamatan": "MEDAN AREA"
						},
						{
							"id": 770,
							"namakecamatan": "MEDAN BARAT"
						},
						{
							"id": 782,
							"namakecamatan": "MEDAN BARU"
						},
						{
							"id": 773,
							"namakecamatan": "MEDAN BELAWAN"
						},
						{
							"id": 771,
							"namakecamatan": "MEDAN DELI"
						},
						{
							"id": 769,
							"namakecamatan": "MEDAN DENAI"
						},
						{
							"id": 706,
							"namakecamatan": "MEDANG DERAS"
						},
						{
							"id": 1187,
							"namakecamatan": "MEDANG KAMPAI"
						},
						{
							"id": 768,
							"namakecamatan": "MEDAN HELVETIA"
						},
						{
							"id": 776,
							"namakecamatan": "MEDAN JOHOR"
						},
						{
							"id": 766,
							"namakecamatan": "MEDAN KOTA"
						},
						{
							"id": 778,
							"namakecamatan": "MEDAN LABUHAN"
						},
						{
							"id": 780,
							"namakecamatan": "MEDAN MAIMUN"
						},
						{
							"id": 777,
							"namakecamatan": "MEDAN MARELAN"
						},
						{
							"id": 783,
							"namakecamatan": "MEDAN PERJUANGAN"
						},
						{
							"id": 784,
							"namakecamatan": "MEDAN PETISAH"
						},
						{
							"id": 781,
							"namakecamatan": "MEDAN POLONIA"
						},
						{
							"id": 2811,
							"namakecamatan": "MEDAN SATRIA"
						},
						{
							"id": 786,
							"namakecamatan": "MEDAN SELAYANG"
						},
						{
							"id": 767,
							"namakecamatan": "MEDAN SUNGGAL"
						},
						{
							"id": 779,
							"namakecamatan": "MEDAN TEMBUNG"
						},
						{
							"id": 785,
							"namakecamatan": "MEDAN TIMUR"
						},
						{
							"id": 772,
							"namakecamatan": "MEDAN TUNTUNGAN"
						},
						{
							"id": 3864,
							"namakecamatan": "MEGALUH"
						},
						{
							"id": 7015,
							"namakecamatan": "MEGAMBILIS"
						},
						{
							"id": 7472,
							"namakecamatan": "MEGAMBILIS"
						},
						{
							"id": 2220,
							"namakecamatan": "MEGAMENDUNG"
						},
						{
							"id": 1456,
							"namakecamatan": "MEGANG SAKTI"
						},
						{
							"id": 4660,
							"namakecamatan": "MEGO"
						},
						{
							"id": 6670,
							"namakecamatan": "MEHALAAN"
						},
						{
							"id": 3896,
							"namakecamatan": "MEJAYAN"
						},
						{
							"id": 3206,
							"namakecamatan": "MEJOBO"
						},
						{
							"id": 1352,
							"namakecamatan": "MEKAKAU ILIR"
						},
						{
							"id": 1526,
							"namakecamatan": "MEKAKAU ILIR"
						},
						{
							"id": 4257,
							"namakecamatan": "MEKAR BARU"
						},
						{
							"id": 4191,
							"namakecamatan": "MEKARJAYA"
						},
						{
							"id": 2391,
							"namakecamatan": "MEKARMUKTI"
						},
						{
							"id": 5270,
							"namakecamatan": "MEKARSARI"
						},
						{
							"id": 7517,
							"namakecamatan": "MELAGI"
						},
						{
							"id": 7484,
							"namakecamatan": "MELAGINERI"
						},
						{
							"id": 7011,
							"namakecamatan": "MELAGINERI"
						},
						{
							"id": 5406,
							"namakecamatan": "MELAK"
						},
						{
							"id": 4333,
							"namakecamatan": "MELAYA"
						},
						{
							"id": 4919,
							"namakecamatan": "MELIAU"
						},
						{
							"id": 1929,
							"namakecamatan": "MELINTING"
						},
						{
							"id": 5609,
							"namakecamatan": "MELONGUANE"
						},
						{
							"id": 5618,
							"namakecamatan": "MELONGUANE TIMUR"
						},
						{
							"id": 6314,
							"namakecamatan": "MELUHU"
						},
						{
							"id": 2031,
							"namakecamatan": "MEMBALONG"
						},
						{
							"id": 7233,
							"namakecamatan": "MEMBERAMO HILIR"
						},
						{
							"id": 7235,
							"namakecamatan": "MEMBERAMO HULU"
						},
						{
							"id": 7860,
							"namakecamatan": "MEMBEY"
						},
						{
							"id": 7680,
							"namakecamatan": "MEMBEY"
						},
						{
							"id": 4882,
							"namakecamatan": "MEMPAWAH HILIR"
						},
						{
							"id": 5011,
							"namakecamatan": "MEMPAWAH HULU"
						},
						{
							"id": 4899,
							"namakecamatan": "MEMPAWAH TIMUR"
						},
						{
							"id": 1145,
							"namakecamatan": "MEMPURA"
						},
						{
							"id": 1279,
							"namakecamatan": "MENDAHARA"
						},
						{
							"id": 1285,
							"namakecamatan": "MENDAHARA ULU"
						},
						{
							"id": 5130,
							"namakecamatan": "MENDAWAI"
						},
						{
							"id": 2025,
							"namakecamatan": "MENDO BARAT"
						},
						{
							"id": 4331,
							"namakecamatan": "MENDOYO"
						},
						{
							"id": 4174,
							"namakecamatan": "MENES"
						},
						{
							"id": 4025,
							"namakecamatan": "MENGANTI"
						},
						{
							"id": 1856,
							"namakecamatan": "MENGGALA"
						},
						{
							"id": 1884,
							"namakecamatan": "MENGGALA TIMUR"
						},
						{
							"id": 6163,
							"namakecamatan": "MENGKENDEK"
						},
						{
							"id": 4346,
							"namakecamatan": "MENGWI"
						},
						{
							"id": 5012,
							"namakecamatan": "MENJALIN"
						},
						{
							"id": 7106,
							"namakecamatan": "MENOU"
						},
						{
							"id": 5478,
							"namakecamatan": "MENTARANG"
						},
						{
							"id": 5489,
							"namakecamatan": "MENTARANG HULU"
						},
						{
							"id": 5078,
							"namakecamatan": "MENTAWA BARUKETAPANG"
						},
						{
							"id": 5080,
							"namakecamatan": "MENTAYA HILIR SELATAN"
						},
						{
							"id": 5079,
							"namakecamatan": "MENTAYA HILIR UTARA"
						},
						{
							"id": 5075,
							"namakecamatan": "MENTAYA HULU"
						},
						{
							"id": 4987,
							"namakecamatan": "MENTEBAH"
						},
						{
							"id": 2158,
							"namakecamatan": "MENTENG"
						},
						{
							"id": 5158,
							"namakecamatan": "MENTHOBI RAYA"
						},
						{
							"id": 2049,
							"namakecamatan": "MENTOK"
						},
						{
							"id": 5829,
							"namakecamatan": "MENUI KEPULAUAN"
						},
						{
							"id": 4959,
							"namakecamatan": "MENUKUNG"
						},
						{
							"id": 5033,
							"namakecamatan": "MENUKUNG"
						},
						{
							"id": 5015,
							"namakecamatan": "MENYUKE"
						},
						{
							"id": 7740,
							"namakecamatan": "MEOS MANSAR"
						},
						{
							"id": 5872,
							"namakecamatan": "MEPANGA"
						},
						{
							"id": 1880,
							"namakecamatan": "MERAKSA AJI"
						},
						{
							"id": 3978,
							"namakecamatan": "MERAKURAK"
						},
						{
							"id": 2087,
							"namakecamatan": "MERAL"
						},
						{
							"id": 2093,
							"namakecamatan": "MERAL BARAT"
						},
						{
							"id": 522,
							"namakecamatan": "MERANTI"
						},
						{
							"id": 5017,
							"namakecamatan": "MERANTI"
						},
						{
							"id": 1424,
							"namakecamatan": "MERAPI BARAT"
						},
						{
							"id": 1441,
							"namakecamatan": "MERAPI SELATAN"
						},
						{
							"id": 1438,
							"namakecamatan": "MERAPI TIMUR"
						},
						{
							"id": 6975,
							"namakecamatan": "MERAUKE"
						},
						{
							"id": 2024,
							"namakecamatan": "MERAWANG"
						},
						{
							"id": 1060,
							"namakecamatan": "MERBAU"
						},
						{
							"id": 1166,
							"namakecamatan": "MERBAU"
						},
						{
							"id": 1771,
							"namakecamatan": "MERBAU MATARAM"
						},
						{
							"id": 448,
							"namakecamatan": "MERDEKA"
						},
						{
							"id": 7756,
							"namakecamatan": "MERDEY"
						},
						{
							"id": 438,
							"namakecamatan": "MEREK"
						},
						{
							"id": 3495,
							"namakecamatan": "MERGANGSAN"
						},
						{
							"id": 1731,
							"namakecamatan": "MERIGI"
						},
						{
							"id": 1741,
							"namakecamatan": "MERIGI KELINDANG"
						},
						{
							"id": 1742,
							"namakecamatan": "MERIGI SAKTI"
						},
						{
							"id": 1268,
							"namakecamatan": "MERLUNG"
						},
						{
							"id": 1245,
							"namakecamatan": "MERSAM"
						},
						{
							"id": 3001,
							"namakecamatan": "MERTOYUDAN"
						},
						{
							"id": 275,
							"namakecamatan": "MESIDAH"
						},
						{
							"id": 100,
							"namakecamatan": "MESJID RAYA"
						},
						{
							"id": 6660,
							"namakecamatan": "MESSAWA"
						},
						{
							"id": 1257,
							"namakecamatan": "MESTONG"
						},
						{
							"id": 1971,
							"namakecamatan": "MESUJI"
						},
						{
							"id": 1370,
							"namakecamatan": "MESUJI"
						},
						{
							"id": 1855,
							"namakecamatan": "MESUJI"
						},
						{
							"id": 1386,
							"namakecamatan": "MESUJI MAKMUR"
						},
						{
							"id": 1387,
							"namakecamatan": "MESUJI RAYA"
						},
						{
							"id": 1972,
							"namakecamatan": "MESUJI TIMUR"
						},
						{
							"id": 1875,
							"namakecamatan": "MESUJI TIMUR"
						},
						{
							"id": 2019,
							"namakecamatan": "METRO BARAT"
						},
						{
							"id": 1922,
							"namakecamatan": "METRO KIBANG"
						},
						{
							"id": 2017,
							"namakecamatan": "METRO PUSAT"
						},
						{
							"id": 2021,
							"namakecamatan": "METRO SELATAN"
						},
						{
							"id": 2020,
							"namakecamatan": "METRO TIMUR"
						},
						{
							"id": 2018,
							"namakecamatan": "METRO UTARA"
						},
						{
							"id": 5,
							"namakecamatan": "MEUKEK"
						},
						{
							"id": 281,
							"namakecamatan": "MEURAH DUA"
						},
						{
							"id": 144,
							"namakecamatan": "MEURAH DUA"
						},
						{
							"id": 152,
							"namakecamatan": "MEURAH MULIA"
						},
						{
							"id": 287,
							"namakecamatan": "MEURAXA"
						},
						{
							"id": 124,
							"namakecamatan": "MEURENDU"
						},
						{
							"id": 88,
							"namakecamatan": "MEUREUBO"
						},
						{
							"id": 277,
							"namakecamatan": "MEUREUDU"
						},
						{
							"id": 7148,
							"namakecamatan": "MEWOLUK"
						},
						{
							"id": 7777,
							"namakecamatan": "MEYADO"
						},
						{
							"id": 5615,
							"namakecamatan": "MIANGAS"
						},
						{
							"id": 2099,
							"namakecamatan": "MIDAI"
						},
						{
							"id": 5168,
							"namakecamatan": "MIHING RAYA"
						},
						{
							"id": 3409,
							"namakecamatan": "MIJEN"
						},
						{
							"id": 3236,
							"namakecamatan": "MIJEN"
						},
						{
							"id": 125,
							"namakecamatan": "MILA"
						},
						{
							"id": 7496,
							"namakecamatan": "MILIMBO"
						},
						{
							"id": 7213,
							"namakecamatan": "MIMIKA BARAT"
						},
						{
							"id": 7220,
							"namakecamatan": "MIMIKA BARAT JAUH"
						},
						{
							"id": 7221,
							"namakecamatan": "MIMIKA BARAT TENGAH"
						},
						{
							"id": 7210,
							"namakecamatan": "MIMIKA BARU"
						},
						{
							"id": 7217,
							"namakecamatan": "MIMIKA TENGAH"
						},
						{
							"id": 7212,
							"namakecamatan": "MIMIKA TIMUR"
						},
						{
							"id": 7216,
							"namakecamatan": "MIMIKA TIMUR JAUH"
						},
						{
							"id": 1135,
							"namakecamatan": "MINAS"
						},
						{
							"id": 6062,
							"namakecamatan": "MINASA TENE"
						},
						{
							"id": 7403,
							"namakecamatan": "MINDIPTANA"
						},
						{
							"id": 3470,
							"namakecamatan": "MINGGIR"
						},
						{
							"id": 7866,
							"namakecamatan": "MINYAMBAOUW"
						},
						{
							"id": 7667,
							"namakecamatan": "MINYAMBOUW"
						},
						{
							"id": 7430,
							"namakecamatan": "MINYAMUR"
						},
						{
							"id": 4585,
							"namakecamatan": "MIOMAFFO TENGAH"
						},
						{
							"id": 4576,
							"namakecamatan": "MIOMAFO BARAT"
						},
						{
							"id": 4575,
							"namakecamatan": "MIOMAFO TIMUR"
						},
						{
							"id": 3125,
							"namakecamatan": "MIRI"
						},
						{
							"id": 5170,
							"namakecamatan": "MIRI MANASA"
						},
						{
							"id": 2942,
							"namakecamatan": "MIRIT"
						},
						{
							"id": 7745,
							"namakecamatan": "MISOOL BARAT"
						},
						{
							"id": 7730,
							"namakecamatan": "MISOOL (MISOOL UTARA)"
						},
						{
							"id": 7627,
							"namakecamatan": "MISOOL SELATAN"
						},
						{
							"id": 7742,
							"namakecamatan": "MISOOL SELATAN"
						},
						{
							"id": 7735,
							"namakecamatan": "MISOOL TIMUR"
						},
						{
							"id": 7617,
							"namakecamatan": "MIYAH"
						},
						{
							"id": 7800,
							"namakecamatan": "MIYAH"
						},
						{
							"id": 7814,
							"namakecamatan": "MIYAH SELATAN"
						},
						{
							"id": 3747,
							"namakecamatan": "MLANDINGAN"
						},
						{
							"id": 3517,
							"namakecamatan": "MLARAK"
						},
						{
							"id": 3472,
							"namakecamatan": "MLATI"
						},
						{
							"id": 3217,
							"namakecamatan": "MLONGGO"
						},
						{
							"id": 6756,
							"namakecamatan": "MNDONA HIERA"
						},
						{
							"id": 6816,
							"namakecamatan": "MNDONA HIERA"
						},
						{
							"id": 6758,
							"namakecamatan": "MOA LAKOR"
						},
						{
							"id": 6814,
							"namakecamatan": "MOA LAKOR"
						},
						{
							"id": 7545,
							"namakecamatan": "MOBA"
						},
						{
							"id": 5535,
							"namakecamatan": "MODAYAG"
						},
						{
							"id": 5686,
							"namakecamatan": "MODAYAG"
						},
						{
							"id": 5546,
							"namakecamatan": "MODAYAG BARAT"
						},
						{
							"id": 5687,
							"namakecamatan": "MODAYAG BARAT"
						},
						{
							"id": 3988,
							"namakecamatan": "MODO"
						},
						{
							"id": 5622,
							"namakecamatan": "MODOINDING"
						},
						{
							"id": 4046,
							"namakecamatan": "MODUNG"
						},
						{
							"id": 7282,
							"namakecamatan": "MOFINOP"
						},
						{
							"id": 3335,
							"namakecamatan": "MOGA"
						},
						{
							"id": 5734,
							"namakecamatan": "MOILONG"
						},
						{
							"id": 7643,
							"namakecamatan": "MOISEGEN"
						},
						{
							"id": 3587,
							"namakecamatan": "MOJO"
						},
						{
							"id": 3850,
							"namakecamatan": "MOJOAGUNG"
						},
						{
							"id": 3844,
							"namakecamatan": "MOJOANYAR"
						},
						{
							"id": 3109,
							"namakecamatan": "MOJOGEDANG"
						},
						{
							"id": 3065,
							"namakecamatan": "MOJOLABAN"
						},
						{
							"id": 4103,
							"namakecamatan": "MOJOROTO"
						},
						{
							"id": 3834,
							"namakecamatan": "MOJOSARI"
						},
						{
							"id": 3018,
							"namakecamatan": "MOJOSONGO"
						},
						{
							"id": 2987,
							"namakecamatan": "MOJOTENGAH"
						},
						{
							"id": 3851,
							"namakecamatan": "MOJOWARNO"
						},
						{
							"id": 7494,
							"namakecamatan": "MOKONI"
						},
						{
							"id": 7050,
							"namakecamatan": "MOLAGALOME"
						},
						{
							"id": 7165,
							"namakecamatan": "MOLANIKIME"
						},
						{
							"id": 6466,
							"namakecamatan": "MOLAWE"
						},
						{
							"id": 6316,
							"namakecamatan": "MOLAWE"
						},
						{
							"id": 4564,
							"namakecamatan": "MOLLO BARAT"
						},
						{
							"id": 4544,
							"namakecamatan": "MOLLO SELATAN"
						},
						{
							"id": 4572,
							"namakecamatan": "MOLLO TENGAH"
						},
						{
							"id": 4545,
							"namakecamatan": "MOLLO UTARA"
						},
						{
							"id": 6762,
							"namakecamatan": "MOLU MARU"
						},
						{
							"id": 7856,
							"namakecamatan": "MOMI WAREN"
						},
						{
							"id": 7675,
							"namakecamatan": "MOMI - WAREN"
						},
						{
							"id": 5812,
							"namakecamatan": "MOMUNU"
						},
						{
							"id": 6614,
							"namakecamatan": "MONANO"
						},
						{
							"id": 6051,
							"namakecamatan": "MONCONG LOE"
						},
						{
							"id": 3127,
							"namakecamatan": "MONDOKAN"
						},
						{
							"id": 4471,
							"namakecamatan": "MONTA"
						},
						{
							"id": 5113,
							"namakecamatan": "MONTALLAT"
						},
						{
							"id": 96,
							"namakecamatan": "MONTASIK"
						},
						{
							"id": 5000,
							"namakecamatan": "MONTERADO"
						},
						{
							"id": 3975,
							"namakecamatan": "MONTONG"
						},
						{
							"id": 4424,
							"namakecamatan": "MONTONG GADING"
						},
						{
							"id": 5418,
							"namakecamatan": "MOOK MANAAR BULATN"
						},
						{
							"id": 7104,
							"namakecamatan": "MOORA"
						},
						{
							"id": 6557,
							"namakecamatan": "MOOTILANGO"
						},
						{
							"id": 7806,
							"namakecamatan": "MORAID"
						},
						{
							"id": 7604,
							"namakecamatan": "MORAID"
						},
						{
							"id": 6405,
							"namakecamatan": "MORAMO"
						},
						{
							"id": 6411,
							"namakecamatan": "MORAMO UTARA"
						},
						{
							"id": 5922,
							"namakecamatan": "MORI ATAS"
						},
						{
							"id": 5823,
							"namakecamatan": "MORIS ATAS"
						},
						{
							"id": 5923,
							"namakecamatan": "MORI UTARA"
						},
						{
							"id": 5836,
							"namakecamatan": "MORI UTARA"
						},
						{
							"id": 2084,
							"namakecamatan": "MORO"
						},
						{
							"id": 5619,
							"namakecamatan": "MORONGE"
						},
						{
							"id": 398,
							"namakecamatan": "MORO'O"
						},
						{
							"id": 761,
							"namakecamatan": "MORO'O"
						},
						{
							"id": 6949,
							"namakecamatan": "MOROTAI JAYA"
						},
						{
							"id": 6883,
							"namakecamatan": "MOROTAI JAYA"
						},
						{
							"id": 6868,
							"namakecamatan": "MOROTAI SELATAN"
						},
						{
							"id": 6947,
							"namakecamatan": "MOROTAI SELATAN"
						},
						{
							"id": 6867,
							"namakecamatan": "MOROTAI SELATAN BARAT"
						},
						{
							"id": 6948,
							"namakecamatan": "MOROTAI SELATAN BARAT"
						},
						{
							"id": 6882,
							"namakecamatan": "MOROTAI TIMUR"
						},
						{
							"id": 6951,
							"namakecamatan": "MOROTAI TIMUR"
						},
						{
							"id": 6950,
							"namakecamatan": "MOROTAI UTARA"
						},
						{
							"id": 6866,
							"namakecamatan": "MOROTAI UTARA"
						},
						{
							"id": 7776,
							"namakecamatan": "MOSKONA BARAT"
						},
						{
							"id": 7759,
							"namakecamatan": "MOSKONA SELATAN"
						},
						{
							"id": 7778,
							"namakecamatan": "MOSKONA TIMUR"
						},
						{
							"id": 7760,
							"namakecamatan": "MOSKONA UTARA"
						},
						{
							"id": 7629,
							"namakecamatan": "MOSWAREN"
						},
						{
							"id": 7713,
							"namakecamatan": "MOSWAREN"
						},
						{
							"id": 5628,
							"namakecamatan": "MOTOLING"
						},
						{
							"id": 5642,
							"namakecamatan": "MOTOLING BARAT"
						},
						{
							"id": 5643,
							"namakecamatan": "MOTOLING TIMUR"
						},
						{
							"id": 6472,
							"namakecamatan": "MOTUI"
						},
						{
							"id": 5864,
							"namakecamatan": "MOUTONG"
						},
						{
							"id": 6488,
							"namakecamatan": "MOWEWE"
						},
						{
							"id": 6265,
							"namakecamatan": "MOWEWE"
						},
						{
							"id": 6410,
							"namakecamatan": "MOWILA"
						},
						{
							"id": 4442,
							"namakecamatan": "MOYO HILIR"
						},
						{
							"id": 4443,
							"namakecamatan": "MOYO HULU"
						},
						{
							"id": 4456,
							"namakecamatan": "MOYO UTARA"
						},
						{
							"id": 3469,
							"namakecamatan": "MOYUDAN"
						},
						{
							"id": 4512,
							"namakecamatan": "MPUNDA"
						},
						{
							"id": 7824,
							"namakecamatan": "MPUR"
						},
						{
							"id": 3227,
							"namakecamatan": "MRANGGEN"
						},
						{
							"id": 2904,
							"namakecamatan": "MREBET"
						},
						{
							"id": 7161,
							"namakecamatan": "MUARA"
						},
						{
							"id": 7515,
							"namakecamatan": "MUARA"
						},
						{
							"id": 344,
							"namakecamatan": "MUARA"
						},
						{
							"id": 5422,
							"namakecamatan": "MUARA ANCALONG"
						},
						{
							"id": 5374,
							"namakecamatan": "MUARA BADAK"
						},
						{
							"id": 1748,
							"namakecamatan": "MUARA BANGKA HULU"
						},
						{
							"id": 624,
							"namakecamatan": "MUARA BATANG GADIS"
						},
						{
							"id": 373,
							"namakecamatan": "MUARA BATANG TORU"
						},
						{
							"id": 151,
							"namakecamatan": "MUARA BATU"
						},
						{
							"id": 1412,
							"namakecamatan": "MUARA BELIDA"
						},
						{
							"id": 1453,
							"namakecamatan": "MUARA BELITI"
						},
						{
							"id": 5424,
							"namakecamatan": "MUARA BENGKAL"
						},
						{
							"id": 1247,
							"namakecamatan": "MUARA BULIAN"
						},
						{
							"id": 1336,
							"namakecamatan": "MUARA DUA"
						},
						{
							"id": 1519,
							"namakecamatan": "MUARA DUA"
						},
						{
							"id": 296,
							"namakecamatan": "MUARA DUA"
						},
						{
							"id": 1339,
							"namakecamatan": "MUARA DUA KISAM"
						},
						{
							"id": 1522,
							"namakecamatan": "MUARA DUA KISAM"
						},
						{
							"id": 1392,
							"namakecamatan": "MUARA ENIM"
						},
						{
							"id": 2725,
							"namakecamatan": "MUARAGEMBONG"
						},
						{
							"id": 5326,
							"namakecamatan": "MUARA HARUS"
						},
						{
							"id": 5383,
							"namakecamatan": "MUARA JAWA"
						},
						{
							"id": 1366,
							"namakecamatan": "MUARA JAYA"
						},
						{
							"id": 5380,
							"namakecamatan": "MUARA KAMAN"
						},
						{
							"id": 1447,
							"namakecamatan": "MUARA KELINGI"
						},
						{
							"id": 1734,
							"namakecamatan": "MUARA KEMUMU"
						},
						{
							"id": 5366,
							"namakecamatan": "MUARA KOMAM"
						},
						{
							"id": 1538,
							"namakecamatan": "MUARA KUANG"
						},
						{
							"id": 1367,
							"namakecamatan": "MUARA KUANG"
						},
						{
							"id": 1446,
							"namakecamatan": "MUARA LAKITAN"
						},
						{
							"id": 5409,
							"namakecamatan": "MUARA LAWA"
						},
						{
							"id": 5370,
							"namakecamatan": "MUARA MUNTAI"
						},
						{
							"id": 1486,
							"namakecamatan": "MUARA PADANG"
						},
						{
							"id": 5410,
							"namakecamatan": "MUARA PAHU"
						},
						{
							"id": 1272,
							"namakecamatan": "MUARA PAPALIK"
						},
						{
							"id": 4938,
							"namakecamatan": "MUARA PAWAN"
						},
						{
							"id": 1443,
							"namakecamatan": "MUARAPAYANG"
						},
						{
							"id": 1554,
							"namakecamatan": "MUARA PINANG"
						},
						{
							"id": 1417,
							"namakecamatan": "MUARA PINANG"
						},
						{
							"id": 1283,
							"namakecamatan": "MUARA SABAK BARAT"
						},
						{
							"id": 1277,
							"namakecamatan": "MUARA SABAK TIMUR"
						},
						{
							"id": 1681,
							"namakecamatan": "MUARA SAHUNG"
						},
						{
							"id": 5369,
							"namakecamatan": "MUARA SAMU"
						},
						{
							"id": 299,
							"namakecamatan": "MUARA SATU"
						},
						{
							"id": 1213,
							"namakecamatan": "MUARA SIAU"
						},
						{
							"id": 619,
							"namakecamatan": "MUARA SIPONGI"
						},
						{
							"id": 1492,
							"namakecamatan": "MUARA SUGIHAN"
						},
						{
							"id": 1820,
							"namakecamatan": "MUARA SUNGKAI"
						},
						{
							"id": 1316,
							"namakecamatan": "MUARA TABIR"
						},
						{
							"id": 7601,
							"namakecamatan": "MUARA TAMI"
						},
						{
							"id": 1487,
							"namakecamatan": "MUARA TELANG"
						},
						{
							"id": 1246,
							"namakecamatan": "MUARA TEMBESI"
						},
						{
							"id": 126,
							"namakecamatan": "MUARA TIGA"
						},
						{
							"id": 5325,
							"namakecamatan": "MUARA UYA"
						},
						{
							"id": 5423,
							"namakecamatan": "MUARA WAHAU"
						},
						{
							"id": 5387,
							"namakecamatan": "MUARA WIS"
						},
						{
							"id": 7810,
							"namakecamatan": "MUBRANI"
						},
						{
							"id": 7684,
							"namakecamatan": "MUBRANI"
						},
						{
							"id": 7304,
							"namakecamatan": "MUGI"
						},
						{
							"id": 7025,
							"namakecamatan": "MUGI"
						},
						{
							"id": 7523,
							"namakecamatan": "MUGI"
						},
						{
							"id": 4901,
							"namakecamatan": "MUKOK"
						},
						{
							"id": 1295,
							"namakecamatan": "MUKO-MUKO BATHIN VII"
						},
						{
							"id": 1430,
							"namakecamatan": "MULAK ULU"
						},
						{
							"id": 7142,
							"namakecamatan": "MULIA"
						},
						{
							"id": 7054,
							"namakecamatan": "MULIAMA"
						},
						{
							"id": 4153,
							"namakecamatan": "MULYOREJO"
						},
						{
							"id": 3688,
							"namakecamatan": "MUMBULSARI"
						},
						{
							"id": 4201,
							"namakecamatan": "MUNCANG"
						},
						{
							"id": 3701,
							"namakecamatan": "MUNCAR"
						},
						{
							"id": 2520,
							"namakecamatan": "MUNDU"
						},
						{
							"id": 930,
							"namakecamatan": "MUNGKA"
						},
						{
							"id": 6261,
							"namakecamatan": "MUNGKAJANG"
						},
						{
							"id": 3000,
							"namakecamatan": "MUNGKID"
						},
						{
							"id": 4169,
							"namakecamatan": "MUNJUL"
						},
						{
							"id": 3532,
							"namakecamatan": "MUNJUNGAN"
						},
						{
							"id": 439,
							"namakecamatan": "MUNTE"
						},
						{
							"id": 2999,
							"namakecamatan": "MUNTILAN"
						},
						{
							"id": 6541,
							"namakecamatan": "MURHUM"
						},
						{
							"id": 7281,
							"namakecamatan": "MURKIM"
						},
						{
							"id": 5513,
							"namakecamatan": "MURUK RIAN"
						},
						{
							"id": 5182,
							"namakecamatan": "MURUNG"
						},
						{
							"id": 5324,
							"namakecamatan": "MURUNG PUDAK"
						},
						{
							"id": 7301,
							"namakecamatan": "MUSAIK"
						},
						{
							"id": 7021,
							"namakecamatan": "MUSATFAK"
						},
						{
							"id": 4586,
							"namakecamatan": "MUSI"
						},
						{
							"id": 2816,
							"namakecamatan": "MUSTIKA JAYA"
						},
						{
							"id": 3016,
							"namakecamatan": "MUSUK"
						},
						{
							"id": 127,
							"namakecamatan": "MUTIARA"
						},
						{
							"id": 138,
							"namakecamatan": "MUTIARA TIMUR"
						},
						{
							"id": 6976,
							"namakecamatan": "MUTING"
						},
						{
							"id": 4587,
							"namakecamatan": "MUTIS"
						},
						{
							"id": 7200,
							"namakecamatan": "MUYE"
						},
						{
							"id": 7082,
							"namakecamatan": "NABIRE"
						},
						{
							"id": 7103,
							"namakecamatan": "NABIRE BARAT"
						},
						{
							"id": 7358,
							"namakecamatan": "NABUNAGE"
						},
						{
							"id": 630,
							"namakecamatan": "NAGA JUANG"
						},
						{
							"id": 4778,
							"namakecamatan": "NAGA WUTUNG"
						},
						{
							"id": 2246,
							"namakecamatan": "NAGRAK"
						},
						{
							"id": 2339,
							"namakecamatan": "NAGREG"
						},
						{
							"id": 4592,
							"namakecamatan": "NAIBENU"
						},
						{
							"id": 7785,
							"namakecamatan": "NAIKERE"
						},
						{
							"id": 599,
							"namakecamatan": "NAINGGOLAN"
						},
						{
							"id": 682,
							"namakecamatan": "NAINGGOLAN"
						},
						{
							"id": 744,
							"namakecamatan": "NA IX - X"
						},
						{
							"id": 551,
							"namakecamatan": "NA. IX-X"
						},
						{
							"id": 7201,
							"namakecamatan": "NAKAMA"
						},
						{
							"id": 7295,
							"namakecamatan": "NALCA"
						},
						{
							"id": 1221,
							"namakecamatan": "NALO TATAN"
						},
						{
							"id": 3222,
							"namakecamatan": "NALUMSARI"
						},
						{
							"id": 2047,
							"namakecamatan": "NAMANG"
						},
						{
							"id": 449,
							"namakecamatan": "NAMAN TERAN"
						},
						{
							"id": 7076,
							"namakecamatan": "NAMBLUONG"
						},
						{
							"id": 5743,
							"namakecamatan": "NAMBO"
						},
						{
							"id": 6763,
							"namakecamatan": "NAMLEA"
						},
						{
							"id": 387,
							"namakecamatan": "NAMOHALU ESIWA"
						},
						{
							"id": 751,
							"namakecamatan": "NAMOHALU ESIWA"
						},
						{
							"id": 456,
							"namakecamatan": "NAMORAMBE"
						},
						{
							"id": 6770,
							"namakecamatan": "NAMROLE"
						},
						{
							"id": 6831,
							"namakecamatan": "NAMROLE"
						},
						{
							"id": 4621,
							"namakecamatan": "NANAET DUABESI"
						},
						{
							"id": 4916,
							"namakecamatan": "NANGA MAHAP"
						},
						{
							"id": 5026,
							"namakecamatan": "NANGA MAHAP"
						},
						{
							"id": 4680,
							"namakecamatan": "NANGAPANDA"
						},
						{
							"id": 5031,
							"namakecamatan": "NANGA PINOH"
						},
						{
							"id": 4957,
							"namakecamatan": "NANGA PINOH"
						},
						{
							"id": 4808,
							"namakecamatan": "NANGARORO"
						},
						{
							"id": 4704,
							"namakecamatan": "NANGARORO"
						},
						{
							"id": 4915,
							"namakecamatan": "NANGA TAMAN"
						},
						{
							"id": 5025,
							"namakecamatan": "NANGA TAMAN"
						},
						{
							"id": 4932,
							"namakecamatan": "NANGA TAYAP"
						},
						{
							"id": 6217,
							"namakecamatan": "NANGGALA"
						},
						{
							"id": 6158,
							"namakecamatan": "NANGGALA"
						},
						{
							"id": 1000,
							"namakecamatan": "NANGGALO"
						},
						{
							"id": 3429,
							"namakecamatan": "NANGGULAN"
						},
						{
							"id": 2215,
							"namakecamatan": "NANGGUNG"
						},
						{
							"id": 889,
							"namakecamatan": "NAN SABARIS"
						},
						{
							"id": 5607,
							"namakecamatan": "NANUSA"
						},
						{
							"id": 6332,
							"namakecamatan": "NAPABALANO"
						},
						{
							"id": 1658,
							"namakecamatan": "NAPAL PUTIH"
						},
						{
							"id": 7083,
							"namakecamatan": "NAPAN"
						},
						{
							"id": 6511,
							"namakecamatan": "NAPANO KUSAMBI"
						},
						{
							"id": 6361,
							"namakecamatan": "NAPANO KUSAMBI"
						},
						{
							"id": 7035,
							"namakecamatan": "NAPUA"
						},
						{
							"id": 2305,
							"namakecamatan": "NARINGGUL"
						},
						{
							"id": 4389,
							"namakecamatan": "NARMADA"
						},
						{
							"id": 1677,
							"namakecamatan": "NASAL"
						},
						{
							"id": 604,
							"namakecamatan": "NASSAU"
						},
						{
							"id": 5146,
							"namakecamatan": "NATAI KELAMPAI"
						},
						{
							"id": 623,
							"namakecamatan": "NATAL"
						},
						{
							"id": 1757,
							"namakecamatan": "NATAR"
						},
						{
							"id": 6986,
							"namakecamatan": "NAUKENJERAI"
						},
						{
							"id": 3504,
							"namakecamatan": "NAWANGAN"
						},
						{
							"id": 4795,
							"namakecamatan": "NDAO NUSE"
						},
						{
							"id": 4684,
							"namakecamatan": "NDONA"
						},
						{
							"id": 4695,
							"namakecamatan": "NDONA TIMUR"
						},
						{
							"id": 4696,
							"namakecamatan": "NDORI"
						},
						{
							"id": 4804,
							"namakecamatan": "NDOSO"
						},
						{
							"id": 4330,
							"namakecamatan": "NEGARA"
						},
						{
							"id": 1947,
							"namakecamatan": "NEGARA BATIN"
						},
						{
							"id": 1943,
							"namakecamatan": "NEGERI AGUNG"
						},
						{
							"id": 1948,
							"namakecamatan": "NEGERI BESAR"
						},
						{
							"id": 1764,
							"namakecamatan": "NEGERI KATON"
						},
						{
							"id": 1952,
							"namakecamatan": "NEGERI KATON"
						},
						{
							"id": 4305,
							"namakecamatan": "NEGLASARI"
						},
						{
							"id": 4528,
							"namakecamatan": "NEKAMESE"
						},
						{
							"id": 7353,
							"namakecamatan": "NELAWI"
						},
						{
							"id": 4665,
							"namakecamatan": "NELLE"
						},
						{
							"id": 7674,
							"namakecamatan": "NENEY"
						},
						{
							"id": 7854,
							"namakecamatan": "NENEY"
						},
						{
							"id": 7547,
							"namakecamatan": "NENGGEAGIN"
						},
						{
							"id": 5010,
							"namakecamatan": "NGABANG"
						},
						{
							"id": 3008,
							"namakecamatan": "NGABLAK"
						},
						{
							"id": 4708,
							"namakecamatan": "NGADA BAWA"
						},
						{
							"id": 3589,
							"namakecamatan": "NGADILUWIH"
						},
						{
							"id": 3269,
							"namakecamatan": "NGADIREJO"
						},
						{
							"id": 3508,
							"namakecamatan": "NGADIROJO"
						},
						{
							"id": 3082,
							"namakecamatan": "NGADIROJO"
						},
						{
							"id": 4758,
							"namakecamatan": "NGADU NGALA"
						},
						{
							"id": 3478,
							"namakecamatan": "NGAGLIK"
						},
						{
							"id": 3631,
							"namakecamatan": "NGAJUNG"
						},
						{
							"id": 3410,
							"namakecamatan": "NGALIYAN"
						},
						{
							"id": 3940,
							"namakecamatan": "NGAMBON"
						},
						{
							"id": 1844,
							"namakecamatan": "NGAMBUR"
						},
						{
							"id": 1994,
							"namakecamatan": "NGAMBUR"
						},
						{
							"id": 3299,
							"namakecamatan": "NGAMPEL"
						},
						{
							"id": 3489,
							"namakecamatan": "NGAMPILAN"
						},
						{
							"id": 2317,
							"namakecamatan": "NGAMPRAH"
						},
						{
							"id": 2737,
							"namakecamatan": "NGAMPRAH"
						},
						{
							"id": 3592,
							"namakecamatan": "NGANCAR"
						},
						{
							"id": 3878,
							"namakecamatan": "NGANJUK"
						},
						{
							"id": 3638,
							"namakecamatan": "NGANTANG"
						},
						{
							"id": 3548,
							"namakecamatan": "NGANTRU"
						},
						{
							"id": 6279,
							"namakecamatan": "NGAPA"
						},
						{
							"id": 6453,
							"namakecamatan": "NGAPA"
						},
						{
							"id": 3101,
							"namakecamatan": "NGARGOYOSO"
						},
						{
							"id": 3916,
							"namakecamatan": "NGARIBOYO"
						},
						{
							"id": 3140,
							"namakecamatan": "NGARINGAN"
						},
						{
							"id": 3610,
							"namakecamatan": "NGASEM"
						},
						{
							"id": 3941,
							"namakecamatan": "NGASEM"
						},
						{
							"id": 3162,
							"namakecamatan": "NGAWEN"
						},
						{
							"id": 3461,
							"namakecamatan": "NGAWEN"
						},
						{
							"id": 3053,
							"namakecamatan": "NGAWEN"
						},
						{
							"id": 3927,
							"namakecamatan": "NGAWI"
						},
						{
							"id": 3528,
							"namakecamatan": "NGEBEL"
						},
						{
							"id": 3477,
							"namakecamatan": "NGEMPLAK"
						},
						{
							"id": 3023,
							"namakecamatan": "NGEMPLAK"
						},
						{
							"id": 3867,
							"namakecamatan": "NGETOS"
						},
						{
							"id": 4741,
							"namakecamatan": "NGGAHA ORI ANGU"
						},
						{
							"id": 6990,
							"namakecamatan": "NGGUTI"
						},
						{
							"id": 3989,
							"namakecamatan": "NGIMBANG"
						},
						{
							"id": 3572,
							"namakecamatan": "NGLEGOK"
						},
						{
							"id": 3450,
							"namakecamatan": "NGLIPAR"
						},
						{
							"id": 2994,
							"namakecamatan": "NGLUWAR"
						},
						{
							"id": 3883,
							"namakecamatan": "NGLUYU"
						},
						{
							"id": 2962,
							"namakecamatan": "NGOMBOL"
						},
						{
							"id": 3831,
							"namakecamatan": "NGORO"
						},
						{
							"id": 3847,
							"namakecamatan": "NGORO"
						},
						{
							"id": 3938,
							"namakecamatan": "NGRAHO"
						},
						{
							"id": 3920,
							"namakecamatan": "NGRAMBE"
						},
						{
							"id": 3119,
							"namakecamatan": "NGRAMPAL"
						},
						{
							"id": 3511,
							"namakecamatan": "NGRAYUN"
						},
						{
							"id": 3872,
							"namakecamatan": "NGRONGGOT"
						},
						{
							"id": 3805,
							"namakecamatan": "NGULING"
						},
						{
							"id": 3075,
							"namakecamatan": "NGUNTORONADI"
						},
						{
							"id": 3917,
							"namakecamatan": "NGUNTORONADI"
						},
						{
							"id": 3555,
							"namakecamatan": "NGUNUT"
						},
						{
							"id": 3865,
							"namakecamatan": "NGUSIKAN"
						},
						{
							"id": 3062,
							"namakecamatan": "NGUTER"
						},
						{
							"id": 166,
							"namakecamatan": "NIBONG"
						},
						{
							"id": 1460,
							"namakecamatan": "NIBUNG"
						},
						{
							"id": 1571,
							"namakecamatan": "NIBUNG"
						},
						{
							"id": 7790,
							"namakecamatan": "NIKIWAR"
						},
						{
							"id": 7514,
							"namakecamatan": "NIKOGWE"
						},
						{
							"id": 7070,
							"namakecamatan": "NIMBOKRANG"
						},
						{
							"id": 7069,
							"namakecamatan": "NIMBORAN"
						},
						{
							"id": 7489,
							"namakecamatan": "NINAME"
						},
						{
							"id": 7418,
							"namakecamatan": "NINATI"
						},
						{
							"id": 7290,
							"namakecamatan": "NINIA"
						},
						{
							"id": 7171,
							"namakecamatan": "NIOGA"
						},
						{
							"id": 1278,
							"namakecamatan": "NIPAH PANJANG"
						},
						{
							"id": 7311,
							"namakecamatan": "NIPSAN"
						},
						{
							"id": 7536,
							"namakecamatan": "NIRKURI"
						},
						{
							"id": 6753,
							"namakecamatan": "NIRUNMAS"
						},
						{
							"id": 161,
							"namakecamatan": "NISAM"
						},
						{
							"id": 172,
							"namakecamatan": "NISAM ANTARA"
						},
						{
							"id": 4662,
							"namakecamatan": "NITA"
						},
						{
							"id": 4566,
							"namakecamatan": "NOEBANA"
						},
						{
							"id": 4568,
							"namakecamatan": "NOEBEBA"
						},
						{
							"id": 4578,
							"namakecamatan": "NOEMUTI"
						},
						{
							"id": 4584,
							"namakecamatan": "NOEMUTI TIMUR"
						},
						{
							"id": 7490,
							"namakecamatan": "NOGI"
						},
						{
							"id": 3024,
							"namakecamatan": "NOGOSARI"
						},
						{
							"id": 5799,
							"namakecamatan": "NOKILALAKI"
						},
						{
							"id": 5898,
							"namakecamatan": "NOKILALAKI"
						},
						{
							"id": 4096,
							"namakecamatan": "NONGGUNONG"
						},
						{
							"id": 7287,
							"namakecamatan": "NONGME"
						},
						{
							"id": 2138,
							"namakecamatan": "NONGSA"
						},
						{
							"id": 6666,
							"namakecamatan": "NOSU"
						},
						{
							"id": 4902,
							"namakecamatan": "NOYAN"
						},
						{
							"id": 5533,
							"namakecamatan": "NUANGAN"
						},
						{
							"id": 5685,
							"namakecamatan": "NUANGAN"
						},
						{
							"id": 4782,
							"namakecamatan": "NUBATUKAN"
						},
						{
							"id": 6205,
							"namakecamatan": "NUHA"
						},
						{
							"id": 5733,
							"namakecamatan": "NUHON"
						},
						{
							"id": 7362,
							"namakecamatan": "NUMBA"
						},
						{
							"id": 7151,
							"namakecamatan": "NUME"
						},
						{
							"id": 7124,
							"namakecamatan": "NUMFOR BARAT"
						},
						{
							"id": 7125,
							"namakecamatan": "NUMFOR TIMUR"
						},
						{
							"id": 4574,
							"namakecamatan": "NUNBENA"
						},
						{
							"id": 7360,
							"namakecamatan": "NUNGGAWI"
						},
						{
							"id": 4559,
							"namakecamatan": "NUNKOLO"
						},
						{
							"id": 5494,
							"namakecamatan": "NUNUKAN"
						},
						{
							"id": 5501,
							"namakecamatan": "NUNUKAN SELATAN"
						},
						{
							"id": 40,
							"namakecamatan": "NURUSSALAM"
						},
						{
							"id": 2496,
							"namakecamatan": "NUSAHERANG"
						},
						{
							"id": 6715,
							"namakecamatan": "NUSA LAUT"
						},
						{
							"id": 6837,
							"namakecamatan": "NUSANIWE"
						},
						{
							"id": 4358,
							"namakecamatan": "NUSA PENIDA"
						},
						{
							"id": 5586,
							"namakecamatan": "NUSA TABUKAN"
						},
						{
							"id": 2850,
							"namakecamatan": "NUSAWUNGU"
						},
						{
							"id": 2273,
							"namakecamatan": "NYALINDUNG"
						},
						{
							"id": 5416,
							"namakecamatan": "NYUATAN"
						},
						{
							"id": 6969,
							"namakecamatan": "OBA"
						},
						{
							"id": 7422,
							"namakecamatan": "OBAA"
						},
						{
							"id": 6973,
							"namakecamatan": "OBA SELATAN"
						},
						{
							"id": 6972,
							"namakecamatan": "OBA TENGAH"
						},
						{
							"id": 6968,
							"namakecamatan": "OBA UTARA"
						},
						{
							"id": 6893,
							"namakecamatan": "OBI"
						},
						{
							"id": 6915,
							"namakecamatan": "OBI BARAT"
						},
						{
							"id": 7297,
							"namakecamatan": "OBIO"
						},
						{
							"id": 6892,
							"namakecamatan": "OBI SELATAN"
						},
						{
							"id": 6916,
							"namakecamatan": "OBI TIMUR"
						},
						{
							"id": 6917,
							"namakecamatan": "OBI UTARA"
						},
						{
							"id": 4860,
							"namakecamatan": "OEBOBO"
						},
						{
							"id": 4560,
							"namakecamatan": "OENINO"
						},
						{
							"id": 7561,
							"namakecamatan": "OGAMANIM"
						},
						{
							"id": 5806,
							"namakecamatan": "OGODEIDE"
						},
						{
							"id": 6470,
							"namakecamatan": "OHEO"
						},
						{
							"id": 6977,
							"namakecamatan": "OKABA"
						},
						{
							"id": 7269,
							"namakecamatan": "OK AOM"
						},
						{
							"id": 7278,
							"namakecamatan": "OKBAB"
						},
						{
							"id": 7265,
							"namakecamatan": "OKBAPE"
						},
						{
							"id": 7276,
							"namakecamatan": "OKBEMTAU"
						},
						{
							"id": 7256,
							"namakecamatan": "OKBIBAB"
						},
						{
							"id": 7273,
							"namakecamatan": "OKHIKA"
						},
						{
							"id": 7275,
							"namakecamatan": "OKLIP"
						},
						{
							"id": 7274,
							"namakecamatan": "OKSAMOL"
						},
						{
							"id": 7277,
							"namakecamatan": "OKSEBANG"
						},
						{
							"id": 7254,
							"namakecamatan": "OKSIBIL"
						},
						{
							"id": 7267,
							"namakecamatan": "OKSOP"
						},
						{
							"id": 4053,
							"namakecamatan": "OMBEN"
						},
						{
							"id": 4783,
							"namakecamatan": "OMESURI"
						},
						{
							"id": 7569,
							"namakecamatan": "OMUKIA"
						},
						{
							"id": 677,
							"namakecamatan": "ONAN GANJANG"
						},
						{
							"id": 681,
							"namakecamatan": "ONAN RUNGGU"
						},
						{
							"id": 600,
							"namakecamatan": "ONAN RUNGGU"
						},
						{
							"id": 6326,
							"namakecamatan": "ONEMBUTE"
						},
						{
							"id": 7571,
							"namakecamatan": "ONERI"
						},
						{
							"id": 5882,
							"namakecamatan": "ONGKA MALINO"
						},
						{
							"id": 652,
							"namakecamatan": "ONOHAZUMBA"
						},
						{
							"id": 651,
							"namakecamatan": "O'O'U"
						},
						{
							"id": 7853,
							"namakecamatan": "ORANSBARI"
						},
						{
							"id": 7662,
							"namakecamatan": "ORANSBARI"
						},
						{
							"id": 7140,
							"namakecamatan": "ORIDEK"
						},
						{
							"id": 7137,
							"namakecamatan": "ORKERI"
						},
						{
							"id": 4461,
							"namakecamatan": "ORONG TELU"
						},
						{
							"id": 7397,
							"namakecamatan": "OUDATE"
						},
						{
							"id": 5703,
							"namakecamatan": "PAAL DUA"
						},
						{
							"id": 1327,
							"namakecamatan": "PAAL MERAH"
						},
						{
							"id": 4139,
							"namakecamatan": "PABEAN CANTIKAN"
						},
						{
							"id": 2512,
							"namakecamatan": "PABEDILAN"
						},
						{
							"id": 3245,
							"namakecamatan": "PABELAN"
						},
						{
							"id": 4749,
							"namakecamatan": "PABERIWAI"
						},
						{
							"id": 2541,
							"namakecamatan": "PABUARAN"
						},
						{
							"id": 2636,
							"namakecamatan": "PABUARAN"
						},
						{
							"id": 2271,
							"namakecamatan": "PABUARAN"
						},
						{
							"id": 4288,
							"namakecamatan": "PABUARAN"
						},
						{
							"id": 3870,
							"namakecamatan": "PACE"
						},
						{
							"id": 2291,
							"namakecamatan": "PACET"
						},
						{
							"id": 2343,
							"namakecamatan": "PACET"
						},
						{
							"id": 3829,
							"namakecamatan": "PACET"
						},
						{
							"id": 3999,
							"namakecamatan": "PACIRAN"
						},
						{
							"id": 3501,
							"namakecamatan": "PACITAN"
						},
						{
							"id": 2460,
							"namakecamatan": "PADAHERANG"
						},
						{
							"id": 2754,
							"namakecamatan": "PADAHERANG"
						},
						{
							"id": 7130,
							"namakecamatan": "PADAIDO"
						},
						{
							"id": 2430,
							"namakecamatan": "PADAKEMBANG"
						},
						{
							"id": 2739,
							"namakecamatan": "PADALARANG"
						},
						{
							"id": 2314,
							"namakecamatan": "PADALARANG"
						},
						{
							"id": 2911,
							"namakecamatan": "PADAMARA"
						},
						{
							"id": 3658,
							"namakecamatan": "PADANG"
						},
						{
							"id": 3956,
							"namakecamatan": "PADANGAN"
						},
						{
							"id": 993,
							"namakecamatan": "PADANG BARAT"
						},
						{
							"id": 5288,
							"namakecamatan": "PADANG BATUNG"
						},
						{
							"id": 352,
							"namakecamatan": "PADANG BOLAK"
						},
						{
							"id": 716,
							"namakecamatan": "PADANG BOLAK"
						},
						{
							"id": 360,
							"namakecamatan": "PADANG BOLAK JULU"
						},
						{
							"id": 717,
							"namakecamatan": "PADANG BOLAK JULU"
						},
						{
							"id": 1955,
							"namakecamatan": "PADANG CERMIN"
						},
						{
							"id": 1754,
							"namakecamatan": "PADANG CERMIN"
						},
						{
							"id": 883,
							"namakecamatan": "PADANG GANTING"
						},
						{
							"id": 949,
							"namakecamatan": "PADANG GELUGUR"
						},
						{
							"id": 1684,
							"namakecamatan": "PADANG GUCI HILIR"
						},
						{
							"id": 1685,
							"namakecamatan": "PADANG GUCI HULU"
						},
						{
							"id": 812,
							"namakecamatan": "PADANG HILIR"
						},
						{
							"id": 810,
							"namakecamatan": "PADANG HULU"
						},
						{
							"id": 1654,
							"namakecamatan": "PADANG JAYA"
						},
						{
							"id": 970,
							"namakecamatan": "PADANG LAWEH"
						},
						{
							"id": 1009,
							"namakecamatan": "PADANG PANJANG BARAT"
						},
						{
							"id": 1008,
							"namakecamatan": "PADANG PANJANG TIMUR"
						},
						{
							"id": 1780,
							"namakecamatan": "PADANG RATU"
						},
						{
							"id": 898,
							"namakecamatan": "PADANG SAGO"
						},
						{
							"id": 991,
							"namakecamatan": "PADANG SELATAN"
						},
						{
							"id": 820,
							"namakecamatan": "PADANGSIDIMPUANANGKOLA JULU"
						},
						{
							"id": 817,
							"namakecamatan": "PADANGSIDIMPUANBATUNADUA"
						},
						{
							"id": 818,
							"namakecamatan": "PADANGSIDIMPUANHUTAIMBARU"
						},
						{
							"id": 816,
							"namakecamatan": "PADANGSIDIMPUANSELATAN"
						},
						{
							"id": 819,
							"namakecamatan": "PADANGSIDIMPUANTENGGARA"
						},
						{
							"id": 815,
							"namakecamatan": "PADANGSIDIMPUANUTARA"
						},
						{
							"id": 128,
							"namakecamatan": "PADANG TIJI"
						},
						{
							"id": 992,
							"namakecamatan": "PADANG TIMUR"
						},
						{
							"id": 422,
							"namakecamatan": "PADANG TUALANG"
						},
						{
							"id": 1628,
							"namakecamatan": "PADANG ULAK TANDING"
						},
						{
							"id": 994,
							"namakecamatan": "PADANG UTARA"
						},
						{
							"id": 4289,
							"namakecamatan": "PADARINCANG"
						},
						{
							"id": 3926,
							"namakecamatan": "PADAS"
						},
						{
							"id": 2165,
							"namakecamatan": "PADEMANGAN"
						},
						{
							"id": 4064,
							"namakecamatan": "PADEMAWU"
						},
						{
							"id": 2958,
							"namakecamatan": "PADURESO"
						},
						{
							"id": 4659,
							"namakecamatan": "PAGA"
						},
						{
							"id": 2638,
							"namakecamatan": "PAGADEN"
						},
						{
							"id": 2659,
							"namakecamatan": "PAGADEN BARAT"
						},
						{
							"id": 961,
							"namakecamatan": "PAGAI SELATAN"
						},
						{
							"id": 952,
							"namakecamatan": "PAGAI UTARA"
						},
						{
							"id": 3613,
							"namakecamatan": "PAGAK"
						},
						{
							"id": 7158,
							"namakecamatan": "PAGALEME"
						},
						{
							"id": 1593,
							"namakecamatan": "PAGAR ALAM SELATAN"
						},
						{
							"id": 1592,
							"namakecamatan": "PAGAR ALAM UTARA"
						},
						{
							"id": 339,
							"namakecamatan": "PAGARAN"
						},
						{
							"id": 1115,
							"namakecamatan": "PAGARAN TAPAHDARUSSALAM"
						},
						{
							"id": 1848,
							"namakecamatan": "PAGAR DEWA"
						},
						{
							"id": 1871,
							"namakecamatan": "PAGAR DEWA"
						},
						{
							"id": 1985,
							"namakecamatan": "PAGAR DEWA"
						},
						{
							"id": 1437,
							"namakecamatan": "PAGAR GUNUNG"
						},
						{
							"id": 1650,
							"namakecamatan": "PAGAR JATI"
						},
						{
							"id": 1739,
							"namakecamatan": "PAGAR JATI"
						},
						{
							"id": 481,
							"namakecamatan": "PAGAR MERBAU"
						},
						{
							"id": 4246,
							"namakecamatan": "PAGEDANGAN"
						},
						{
							"id": 2934,
							"namakecamatan": "PAGEDONGAN"
						},
						{
							"id": 1966,
							"namakecamatan": "PAGELARAN"
						},
						{
							"id": 2299,
							"namakecamatan": "PAGELARAN"
						},
						{
							"id": 3644,
							"namakecamatan": "PAGELARAN"
						},
						{
							"id": 4170,
							"namakecamatan": "PAGELARAN"
						},
						{
							"id": 1889,
							"namakecamatan": "PAGELARAN"
						},
						{
							"id": 1970,
							"namakecamatan": "PAGELARAN UTARA"
						},
						{
							"id": 2928,
							"namakecamatan": "PAGENTAN"
						},
						{
							"id": 2439,
							"namakecamatan": "PAGERAGEUNG"
						},
						{
							"id": 3353,
							"namakecamatan": "PAGERBARANG"
						},
						{
							"id": 3282,
							"namakecamatan": "PAGERUYUNG"
						},
						{
							"id": 3550,
							"namakecamatan": "PAGERWOJO"
						},
						{
							"id": 5727,
							"namakecamatan": "PAGIMANA"
						},
						{
							"id": 667,
							"namakecamatan": "PAGINDAR"
						},
						{
							"id": 3596,
							"namakecamatan": "PAGU"
						},
						{
							"id": 6597,
							"namakecamatan": "PAGUAT"
						},
						{
							"id": 6568,
							"namakecamatan": "PAGUYAMAN"
						},
						{
							"id": 6574,
							"namakecamatan": "PAGUYAMAN PANTAI"
						},
						{
							"id": 3370,
							"namakecamatan": "PAGUYANGAN"
						},
						{
							"id": 335,
							"namakecamatan": "PAHAE JAE"
						},
						{
							"id": 334,
							"namakecamatan": "PAHAE JULU"
						},
						{
							"id": 5202,
							"namakecamatan": "PAHANDUT"
						},
						{
							"id": 4747,
							"namakecamatan": "PAHUNGA LODU"
						},
						{
							"id": 3772,
							"namakecamatan": "PAITON"
						},
						{
							"id": 3438,
							"namakecamatan": "PAJANGAN"
						},
						{
							"id": 1427,
							"namakecamatan": "PAJAR BULAN"
						},
						{
							"id": 4470,
							"namakecamatan": "PAJO"
						},
						{
							"id": 5198,
							"namakecamatan": "PAJU EPAT"
						},
						{
							"id": 5961,
							"namakecamatan": "PAJUKUKANG"
						},
						{
							"id": 4157,
							"namakecamatan": "PAKAL"
						},
						{
							"id": 628,
							"namakecamatan": "PAKANTAN"
						},
						{
							"id": 3562,
							"namakecamatan": "PAKEL"
						},
						{
							"id": 3737,
							"namakecamatan": "PAKEM"
						},
						{
							"id": 3482,
							"namakecamatan": "PAKEM"
						},
						{
							"id": 2392,
							"namakecamatan": "PAKENJENG"
						},
						{
							"id": 3007,
							"namakecamatan": "PAKIS"
						},
						{
							"id": 3629,
							"namakecamatan": "PAKIS"
						},
						{
							"id": 3630,
							"namakecamatan": "PAKISAJI"
						},
						{
							"id": 3225,
							"namakecamatan": "PAKIS AJI"
						},
						{
							"id": 2690,
							"namakecamatan": "PAKISJAYA"
						},
						{
							"id": 678,
							"namakecamatan": "PAKKAT"
						},
						{
							"id": 4071,
							"namakecamatan": "PAKONG"
						},
						{
							"id": 5200,
							"namakecamatan": "PAKU"
						},
						{
							"id": 3494,
							"namakecamatan": "PAKUALAMAN"
						},
						{
							"id": 1942,
							"namakecamatan": "PAKUAN RATU"
						},
						{
							"id": 6449,
							"namakecamatan": "PAKUE"
						},
						{
							"id": 6268,
							"namakecamatan": "PAKUE"
						},
						{
							"id": 6457,
							"namakecamatan": "PAKUE TENGAH"
						},
						{
							"id": 6458,
							"namakecamatan": "PAKUE UTARA"
						},
						{
							"id": 4239,
							"namakecamatan": "PAKUHAJI"
						},
						{
							"id": 3770,
							"namakecamatan": "PAKUNIRAN"
						},
						{
							"id": 3689,
							"namakecamatan": "PAKUSARI"
						},
						{
							"id": 6026,
							"namakecamatan": "PALAKKA"
						},
						{
							"id": 3983,
							"namakecamatan": "PALANG"
						},
						{
							"id": 6399,
							"namakecamatan": "PALANGGA"
						},
						{
							"id": 5991,
							"namakecamatan": "PALANGGA"
						},
						{
							"id": 6409,
							"namakecamatan": "PALANGGA SELATAN"
						},
						{
							"id": 5455,
							"namakecamatan": "PALARAN"
						},
						{
							"id": 1763,
							"namakecamatan": "PALAS"
						},
						{
							"id": 5878,
							"namakecamatan": "PALASA"
						},
						{
							"id": 2567,
							"namakecamatan": "PALASAH"
						},
						{
							"id": 5816,
							"namakecamatan": "PALELEH"
						},
						{
							"id": 5822,
							"namakecamatan": "PALELEH BARAT"
						},
						{
							"id": 4068,
							"namakecamatan": "PALENGGAAN"
						},
						{
							"id": 6116,
							"namakecamatan": "PALETEANG"
						},
						{
							"id": 4488,
							"namakecamatan": "PALIBELO"
						},
						{
							"id": 2525,
							"namakecamatan": "PALIMANAN"
						},
						{
							"id": 598,
							"namakecamatan": "PALIPI"
						},
						{
							"id": 683,
							"namakecamatan": "PALIPI"
						},
						{
							"id": 3453,
							"namakecamatan": "PALIYAN"
						},
						{
							"id": 2097,
							"namakecamatan": "PALMATAK"
						},
						{
							"id": 2129,
							"namakecamatan": "PALMATAK"
						},
						{
							"id": 2173,
							"namakecamatan": "PAL MERAH"
						},
						{
							"id": 4870,
							"namakecamatan": "PALOH"
						},
						{
							"id": 5897,
							"namakecamatan": "PALOLO"
						},
						{
							"id": 5783,
							"namakecamatan": "PALOLO"
						},
						{
							"id": 5929,
							"namakecamatan": "PALU BARAT"
						},
						{
							"id": 4664,
							"namakecamatan": "PALUE"
						},
						{
							"id": 913,
							"namakecamatan": "PALUPUH"
						},
						{
							"id": 5930,
							"namakecamatan": "PALU SELATAN"
						},
						{
							"id": 5928,
							"namakecamatan": "PALU TIMUR"
						},
						{
							"id": 5931,
							"namakecamatan": "PALU UTARA"
						},
						{
							"id": 2642,
							"namakecamatan": "PAMANUKAN"
						},
						{
							"id": 4284,
							"namakecamatan": "PAMARAYAN"
						},
						{
							"id": 2459,
							"namakecamatan": "PAMARICAN"
						},
						{
							"id": 514,
							"namakecamatan": "PAMATANG SILIMA HUTA"
						},
						{
							"id": 6688,
							"namakecamatan": "PAMBOANG"
						},
						{
							"id": 7286,
							"namakecamatan": "PAMEK"
						},
						{
							"id": 4066,
							"namakecamatan": "PAMEKASAN"
						},
						{
							"id": 1216,
							"namakecamatan": "PAMENANG"
						},
						{
							"id": 1223,
							"namakecamatan": "PAMENANG BARAT"
						},
						{
							"id": 1230,
							"namakecamatan": "PAMENANG SELATAN"
						},
						{
							"id": 2386,
							"namakecamatan": "PAMEUNGPEUK"
						},
						{
							"id": 2327,
							"namakecamatan": "PAMEUNGPEUK"
						},
						{
							"id": 2211,
							"namakecamatan": "PAMIJAHAN"
						},
						{
							"id": 5317,
							"namakecamatan": "PAMINGGIR"
						},
						{
							"id": 6082,
							"namakecamatan": "PAMMANA"
						},
						{
							"id": 5763,
							"namakecamatan": "PAMONA BARAT"
						},
						{
							"id": 5747,
							"namakecamatan": "PAMONA PUSELEMBA"
						},
						{
							"id": 5749,
							"namakecamatan": "PAMONA SELATAN"
						},
						{
							"id": 5769,
							"namakecamatan": "PAMONA TENGGARA"
						},
						{
							"id": 5748,
							"namakecamatan": "PAMONA TIMUR"
						},
						{
							"id": 5770,
							"namakecamatan": "PAMONA UTARA"
						},
						{
							"id": 3173,
							"namakecamatan": "PAMOTAN"
						},
						{
							"id": 1378,
							"namakecamatan": "PAMPANGAN"
						},
						{
							"id": 5236,
							"namakecamatan": "PAMUKAN BARAT"
						},
						{
							"id": 5228,
							"namakecamatan": "PAMUKAN SELATAN"
						},
						{
							"id": 5230,
							"namakecamatan": "PAMUKAN UTARA"
						},
						{
							"id": 4328,
							"namakecamatan": "PAMULANG"
						},
						{
							"id": 4249,
							"namakecamatan": "PAMULANG"
						},
						{
							"id": 2587,
							"namakecamatan": "PAMULIHAN"
						},
						{
							"id": 2393,
							"namakecamatan": "PAMULIHAN"
						},
						{
							"id": 6657,
							"namakecamatan": "PANA"
						},
						{
							"id": 7349,
							"namakecamatan": "PANAGA"
						},
						{
							"id": 565,
							"namakecamatan": "PANAI HILIR"
						},
						{
							"id": 566,
							"namakecamatan": "PANAI HULU"
						},
						{
							"id": 564,
							"namakecamatan": "PANAI TENGAH"
						},
						{
							"id": 6244,
							"namakecamatan": "PANAKUKKANG"
						},
						{
							"id": 3749,
							"namakecamatan": "PANARUKAN"
						},
						{
							"id": 2450,
							"namakecamatan": "PANAWANGAN"
						},
						{
							"id": 1976,
							"namakecamatan": "PANCA JAYA"
						},
						{
							"id": 1882,
							"namakecamatan": "PANCA JAYA"
						},
						{
							"id": 2498,
							"namakecamatan": "PANCALANG"
						},
						{
							"id": 6095,
							"namakecamatan": "PANCA LAUTAN"
						},
						{
							"id": 6099,
							"namakecamatan": "PANCA RIJANG"
						},
						{
							"id": 2405,
							"namakecamatan": "PANCATENGAH"
						},
						{
							"id": 4015,
							"namakecamatan": "PANCENG"
						},
						{
							"id": 2182,
							"namakecamatan": "PANCORAN"
						},
						{
							"id": 2818,
							"namakecamatan": "PANCORAN MAS"
						},
						{
							"id": 827,
							"namakecamatan": "PANCUNG SOAL"
						},
						{
							"id": 3177,
							"namakecamatan": "PANCUR"
						},
						{
							"id": 455,
							"namakecamatan": "PANCUR BATU"
						},
						{
							"id": 3795,
							"namakecamatan": "PANDAAN"
						},
						{
							"id": 3437,
							"namakecamatan": "PANDAK"
						},
						{
							"id": 312,
							"namakecamatan": "PANDAN"
						},
						{
							"id": 2933,
							"namakecamatan": "PANDANARUM"
						},
						{
							"id": 4744,
							"namakecamatan": "PANDAWAI"
						},
						{
							"id": 5302,
							"namakecamatan": "PANDAWAN"
						},
						{
							"id": 4182,
							"namakecamatan": "PANDEGLANG"
						},
						{
							"id": 5174,
							"namakecamatan": "PANDIH BATU"
						},
						{
							"id": 206,
							"namakecamatan": "PANDRAH"
						},
						{
							"id": 487,
							"namakecamatan": "PANEI"
						},
						{
							"id": 3908,
							"namakecamatan": "PANEKAN"
						},
						{
							"id": 241,
							"namakecamatan": "PANGA"
						},
						{
							"id": 6696,
							"namakecamatan": "PANGALE"
						},
						{
							"id": 6643,
							"namakecamatan": "PANGALE"
						},
						{
							"id": 2328,
							"namakecamatan": "PANGALENGAN"
						},
						{
							"id": 2462,
							"namakecamatan": "PANGANDARAN"
						},
						{
							"id": 2756,
							"namakecamatan": "PANGANDARAN"
						},
						{
							"id": 4061,
							"namakecamatan": "PANGARENGAN"
						},
						{
							"id": 342,
							"namakecamatan": "PANGARIBUAN"
						},
						{
							"id": 2400,
							"namakecamatan": "PANGATIKAN"
						},
						{
							"id": 1155,
							"namakecamatan": "PANGEAN"
						},
						{
							"id": 2519,
							"namakecamatan": "PANGENAN"
						},
						{
							"id": 3454,
							"namakecamatan": "PANGGANG"
						},
						{
							"id": 4198,
							"namakecamatan": "PANGGARANGAN"
						},
						{
							"id": 7309,
							"namakecamatan": "PANGGEMA"
						},
						{
							"id": 3531,
							"namakecamatan": "PANGGUL"
						},
						{
							"id": 4122,
							"namakecamatan": "PANGGUNGREJO"
						},
						{
							"id": 3576,
							"namakecamatan": "PANGGUNGREJO"
						},
						{
							"id": 3357,
							"namakecamatan": "PANGKAH"
						},
						{
							"id": 6056,
							"namakecamatan": "PANGKAJENE"
						},
						{
							"id": 2680,
							"namakecamatan": "PANGKALAN"
						},
						{
							"id": 5072,
							"namakecamatan": "PANGKALAN BANTENG"
						},
						{
							"id": 2044,
							"namakecamatan": "PANGKALAN BARU"
						},
						{
							"id": 1227,
							"namakecamatan": "PANGKALAN JAMBU"
						},
						{
							"id": 1091,
							"namakecamatan": "PANGKALAN KERINCI"
						},
						{
							"id": 925,
							"namakecamatan": "PANGKALAN KOTO BARU"
						},
						{
							"id": 1092,
							"namakecamatan": "PANGKALAN KURAS"
						},
						{
							"id": 5071,
							"namakecamatan": "PANGKALAN LADA"
						},
						{
							"id": 1385,
							"namakecamatan": "PANGKALAN LAMPAM"
						},
						{
							"id": 1093,
							"namakecamatan": "PANGKALAN LESUNG"
						},
						{
							"id": 425,
							"namakecamatan": "PANGKALAN SUSU"
						},
						{
							"id": 2064,
							"namakecamatan": "PANGKAL BALAM"
						},
						{
							"id": 560,
							"namakecamatan": "PANGKATAN"
						},
						{
							"id": 3932,
							"namakecamatan": "PANGKUR"
						},
						{
							"id": 2533,
							"namakecamatan": "PANGURAGAN"
						},
						{
							"id": 687,
							"namakecamatan": "PANGURURAN"
						},
						{
							"id": 596,
							"namakecamatan": "PANGURURAN"
						},
						{
							"id": 7177,
							"namakecamatan": "PANIAI BARAT"
						},
						{
							"id": 7176,
							"namakecamatan": "PANIAI TIMUR"
						},
						{
							"id": 4167,
							"namakecamatan": "PANIMBANG"
						},
						{
							"id": 3317,
							"namakecamatan": "PANINGGARAN"
						},
						{
							"id": 2448,
							"namakecamatan": "PANJALU"
						},
						{
							"id": 2000,
							"namakecamatan": "PANJANG"
						},
						{
							"id": 3422,
							"namakecamatan": "PANJATAN"
						},
						{
							"id": 3751,
							"namakecamatan": "PANJI"
						},
						{
							"id": 488,
							"namakecamatan": "PANOMBEIAN PANE"
						},
						{
							"id": 4243,
							"namakecamatan": "PANONGAN"
						},
						{
							"id": 7230,
							"namakecamatan": "PANTAI BARAT"
						},
						{
							"id": 4791,
							"namakecamatan": "PANTAI BARU"
						},
						{
							"id": 844,
							"namakecamatan": "PANTAI CERMIN"
						},
						{
							"id": 480,
							"namakecamatan": "PANTAI CERMIN"
						},
						{
							"id": 689,
							"namakecamatan": "PANTAI CERMIN"
						},
						{
							"id": 7442,
							"namakecamatan": "PANTAI KASUARI"
						},
						{
							"id": 482,
							"namakecamatan": "PANTAI LABU"
						},
						{
							"id": 5152,
							"namakecamatan": "PANTAI LUNCI"
						},
						{
							"id": 7231,
							"namakecamatan": "PANTAI TIMUR"
						},
						{
							"id": 7241,
							"namakecamatan": "PANTAI TIMUR BAGIANBARAT"
						},
						{
							"id": 235,
							"namakecamatan": "PANTAN CUACA"
						},
						{
							"id": 4628,
							"namakecamatan": "PANTAR"
						},
						{
							"id": 4631,
							"namakecamatan": "PANTAR BARAT"
						},
						{
							"id": 4639,
							"namakecamatan": "PANTAR BARU LAUT"
						},
						{
							"id": 4638,
							"namakecamatan": "PANTAR TENGAH"
						},
						{
							"id": 4636,
							"namakecamatan": "PANTAR TIMUR"
						},
						{
							"id": 45,
							"namakecamatan": "PANTE BIDARI"
						},
						{
							"id": 87,
							"namakecamatan": "PANTE CEUREUMEN"
						},
						{
							"id": 283,
							"namakecamatan": "PANTERAJA"
						},
						{
							"id": 140,
							"namakecamatan": "PANTE RAJA"
						},
						{
							"id": 3679,
							"namakecamatan": "PANTI"
						},
						{
							"id": 939,
							"namakecamatan": "PANTI"
						},
						{
							"id": 91,
							"namakecamatan": "PANTON REU"
						},
						{
							"id": 2447,
							"namakecamatan": "PANUMBANGAN"
						},
						{
							"id": 608,
							"namakecamatan": "PANYABUNGAN"
						},
						{
							"id": 612,
							"namakecamatan": "PANYABUNGAN BARAT"
						},
						{
							"id": 611,
							"namakecamatan": "PANYABUNGAN SELATAN"
						},
						{
							"id": 610,
							"namakecamatan": "PANYABUNGAN TIMUR"
						},
						{
							"id": 609,
							"namakecamatan": "PANYABUNGAN UTARA"
						},
						{
							"id": 2798,
							"namakecamatan": "PANYILEUKAN"
						},
						{
							"id": 2566,
							"namakecamatan": "PANYINGKIRAN"
						},
						{
							"id": 5212,
							"namakecamatan": "PANYIPATAN"
						},
						{
							"id": 6644,
							"namakecamatan": "PAPALANG"
						},
						{
							"id": 3599,
							"namakecamatan": "PAPAR"
						},
						{
							"id": 4486,
							"namakecamatan": "PARADO"
						},
						{
							"id": 3268,
							"namakecamatan": "PARAKAN"
						},
						{
							"id": 2249,
							"namakecamatan": "PARAKANSALAK"
						},
						{
							"id": 5255,
							"namakecamatan": "PARAMASAN"
						},
						{
							"id": 3902,
							"namakecamatan": "PARANG"
						},
						{
							"id": 3093,
							"namakecamatan": "PARANGGUPITO"
						},
						{
							"id": 673,
							"namakecamatan": "PARANGINAN"
						},
						{
							"id": 5989,
							"namakecamatan": "PARANGLOE"
						},
						{
							"id": 578,
							"namakecamatan": "PARBULUAN"
						},
						{
							"id": 1965,
							"namakecamatan": "PARDASUKA"
						},
						{
							"id": 1892,
							"namakecamatan": "PARDASUKA"
						},
						{
							"id": 3602,
							"namakecamatan": "PARE"
						},
						{
							"id": 3974,
							"namakecamatan": "PARENGAN"
						},
						{
							"id": 5076,
							"namakecamatan": "PARENGGEAN"
						},
						{
							"id": 1020,
							"namakecamatan": "PARIAMAN SELATAN"
						},
						{
							"id": 1018,
							"namakecamatan": "PARIAMAN TENGAH"
						},
						{
							"id": 1021,
							"namakecamatan": "PARIAMAN TIMUR"
						},
						{
							"id": 1019,
							"namakecamatan": "PARIAMAN UTARA"
						},
						{
							"id": 881,
							"namakecamatan": "PARIANGAN"
						},
						{
							"id": 2748,
							"namakecamatan": "PARIGI"
						},
						{
							"id": 6001,
							"namakecamatan": "PARIGI"
						},
						{
							"id": 2464,
							"namakecamatan": "PARIGI"
						},
						{
							"id": 6350,
							"namakecamatan": "PARIGI"
						},
						{
							"id": 5861,
							"namakecamatan": "PARIGI"
						},
						{
							"id": 5876,
							"namakecamatan": "PARIGI BARAT"
						},
						{
							"id": 5871,
							"namakecamatan": "PARIGI SELATAN"
						},
						{
							"id": 5880,
							"namakecamatan": "PARIGI TENGAH"
						},
						{
							"id": 5879,
							"namakecamatan": "PARIGI UTARA"
						},
						{
							"id": 4908,
							"namakecamatan": "PARINDU"
						},
						{
							"id": 5346,
							"namakecamatan": "PARINGIN"
						},
						{
							"id": 5347,
							"namakecamatan": "PARINGIN SELATAN"
						},
						{
							"id": 2054,
							"namakecamatan": "PARITTIGA"
						},
						{
							"id": 7696,
							"namakecamatan": "PARIWARI"
						},
						{
							"id": 670,
							"namakecamatan": "PARLILITAN"
						},
						{
							"id": 607,
							"namakecamatan": "PARMAKSIAN"
						},
						{
							"id": 340,
							"namakecamatan": "PARMONANGAN"
						},
						{
							"id": 7528,
							"namakecamatan": "PARO"
						},
						{
							"id": 3928,
							"namakecamatan": "PARON"
						},
						{
							"id": 2733,
							"namakecamatan": "PARONGPONG"
						},
						{
							"id": 2356,
							"namakecamatan": "PAROPONG"
						},
						{
							"id": 2204,
							"namakecamatan": "PARUNG"
						},
						{
							"id": 2247,
							"namakecamatan": "PARUNGKUDA"
						},
						{
							"id": 2214,
							"namakecamatan": "PARUNG PANJANG"
						},
						{
							"id": 2408,
							"namakecamatan": "PARUNGPONTENG"
						},
						{
							"id": 5103,
							"namakecamatan": "PASAK TALAWANG"
						},
						{
							"id": 2540,
							"namakecamatan": "PASALEMAN"
						},
						{
							"id": 935,
							"namakecamatan": "PASAMAN"
						},
						{
							"id": 982,
							"namakecamatan": "PASAMAN"
						},
						{
							"id": 5665,
							"namakecamatan": "PASAN"
						},
						{
							"id": 6627,
							"namakecamatan": "PASANGKAYU"
						},
						{
							"id": 325,
							"namakecamatan": "PASARIBU TOBING"
						},
						{
							"id": 1320,
							"namakecamatan": "PASAR JAMBI"
						},
						{
							"id": 4236,
							"namakecamatan": "PASAR KEMIS"
						},
						{
							"id": 3389,
							"namakecamatan": "PASAR KLIWON"
						},
						{
							"id": 1621,
							"namakecamatan": "PASAR MANNA"
						},
						{
							"id": 2178,
							"namakecamatan": "PASAR MINGGU"
						},
						{
							"id": 1290,
							"namakecamatan": "PASAR MUARO BUNGO"
						},
						{
							"id": 2189,
							"namakecamatan": "PASAR REBO"
						},
						{
							"id": 6375,
							"namakecamatan": "PASARWAJO"
						},
						{
							"id": 2495,
							"namakecamatan": "PASAWAHAN"
						},
						{
							"id": 2671,
							"namakecamatan": "PASAWAHAN"
						},
						{
							"id": 4075,
							"namakecamatan": "PASEAN"
						},
						{
							"id": 2348,
							"namakecamatan": "PASEH"
						},
						{
							"id": 2582,
							"namakecamatan": "PASEH"
						},
						{
							"id": 2629,
							"namakecamatan": "PASEKAN"
						},
						{
							"id": 7302,
							"namakecamatan": "PASEMA"
						},
						{
							"id": 1426,
							"namakecamatan": "PASEMAH AIR KERUH"
						},
						{
							"id": 1560,
							"namakecamatan": "PASEMAH AIR KERUH"
						},
						{
							"id": 244,
							"namakecamatan": "PASIE RAYA"
						},
						{
							"id": 6358,
							"namakecamatan": "PASI KOLAGA"
						},
						{
							"id": 5944,
							"namakecamatan": "PASILAMBENA"
						},
						{
							"id": 5942,
							"namakecamatan": "PASIMARANNU"
						},
						{
							"id": 5941,
							"namakecamatan": "PASIMASUNGGU"
						},
						{
							"id": 5945,
							"namakecamatan": "PASIMASUNGGU TIMUR"
						},
						{
							"id": 10,
							"namakecamatan": "PASI RAJA"
						},
						{
							"id": 5362,
							"namakecamatan": "PASIR BALENGKONG"
						},
						{
							"id": 3648,
							"namakecamatan": "PASIRIAN"
						},
						{
							"id": 2351,
							"namakecamatan": "PASIRJAMBU"
						},
						{
							"id": 2313,
							"namakecamatan": "PASIRKUDA"
						},
						{
							"id": 1123,
							"namakecamatan": "PASIR LIMAU KAPAS"
						},
						{
							"id": 1046,
							"namakecamatan": "PASIR PENYU"
						},
						{
							"id": 6353,
							"namakecamatan": "PASIR PUTIH"
						},
						{
							"id": 7549,
							"namakecamatan": "PASIR PUTIH"
						},
						{
							"id": 1931,
							"namakecamatan": "PASIR SAKTI"
						},
						{
							"id": 2367,
							"namakecamatan": "PASIRWANGI"
						},
						{
							"id": 4088,
							"namakecamatan": "PASONGSONGAN"
						},
						{
							"id": 3789,
							"namakecamatan": "PASREPAN"
						},
						{
							"id": 3655,
							"namakecamatan": "PASRUJAMBE"
						},
						{
							"id": 5536,
							"namakecamatan": "PASSI BARAT"
						},
						{
							"id": 5539,
							"namakecamatan": "PASSI TIMUR"
						},
						{
							"id": 7429,
							"namakecamatan": "PASSUE"
						},
						{
							"id": 7435,
							"namakecamatan": "PASSUE BAWAH"
						},
						{
							"id": 6110,
							"namakecamatan": "PATAMPANUA"
						},
						{
							"id": 902,
							"namakecamatan": "PATAMUAN"
						},
						{
							"id": 5194,
							"namakecamatan": "PATANGKEP TUTUI"
						},
						{
							"id": 6857,
							"namakecamatan": "PATANI"
						},
						{
							"id": 6863,
							"namakecamatan": "PATANI BARAT"
						},
						{
							"id": 6865,
							"namakecamatan": "PATANI TIMUR"
						},
						{
							"id": 6861,
							"namakecamatan": "PATANI UTARA"
						},
						{
							"id": 2843,
							"namakecamatan": "PATARUMAN"
						},
						{
							"id": 3284,
							"namakecamatan": "PATEAN"
						},
						{
							"id": 3294,
							"namakecamatan": "PATEBON"
						},
						{
							"id": 3190,
							"namakecamatan": "PATI"
						},
						{
							"id": 4185,
							"namakecamatan": "PATIA"
						},
						{
							"id": 3874,
							"namakecamatan": "PATIANROWO"
						},
						{
							"id": 2881,
							"namakecamatan": "PATIKRAJA"
						},
						{
							"id": 6598,
							"namakecamatan": "PATILANGGIO"
						},
						{
							"id": 6038,
							"namakecamatan": "PATIMPENG"
						},
						{
							"id": 2864,
							"namakecamatan": "PATIMUAN"
						},
						{
							"id": 2647,
							"namakecamatan": "PATOKBEUSI"
						},
						{
							"id": 3685,
							"namakecamatan": "PATRANG"
						},
						{
							"id": 2631,
							"namakecamatan": "PATROL"
						},
						{
							"id": 5997,
							"namakecamatan": "PATTALASANG"
						},
						{
							"id": 5982,
							"namakecamatan": "PATTALLASSANG"
						},
						{
							"id": 3452,
							"namakecamatan": "PATUK"
						},
						{
							"id": 471,
							"namakecamatan": "PATUMBAK"
						},
						{
							"id": 1238,
							"namakecamatan": "PAUH"
						},
						{
							"id": 998,
							"namakecamatan": "PAUH"
						},
						{
							"id": 978,
							"namakecamatan": "PAUH DUO"
						},
						{
							"id": 165,
							"namakecamatan": "PAYA BAKONG"
						},
						{
							"id": 922,
							"namakecamatan": "PAYAKUMBUH"
						},
						{
							"id": 1013,
							"namakecamatan": "PAYAKUMBUH BARAT"
						},
						{
							"id": 1017,
							"namakecamatan": "PAYAKUMBUH SELATAN"
						},
						{
							"id": 1015,
							"namakecamatan": "PAYAKUMBUH TIMUR"
						},
						{
							"id": 1014,
							"namakecamatan": "PAYAKUMBUH UTARA"
						},
						{
							"id": 4357,
							"namakecamatan": "PAYANGAN"
						},
						{
							"id": 1553,
							"namakecamatan": "PAYARAMAN"
						},
						{
							"id": 2039,
							"namakecamatan": "PAYUNG"
						},
						{
							"id": 444,
							"namakecamatan": "PAYUNG"
						},
						{
							"id": 846,
							"namakecamatan": "PAYUNG SEKAKI"
						},
						{
							"id": 1181,
							"namakecamatan": "PAYUNG SEKAKI"
						},
						{
							"id": 2721,
							"namakecamatan": "PEBAYURAN"
						},
						{
							"id": 3314,
							"namakecamatan": "PECALUNGAN"
						},
						{
							"id": 3212,
							"namakecamatan": "PECANGAAN"
						},
						{
							"id": 1369,
							"namakecamatan": "PEDAMARAN"
						},
						{
							"id": 1390,
							"namakecamatan": "PEDAMARAN TIMUR"
						},
						{
							"id": 3043,
							"namakecamatan": "PEDAN"
						},
						{
							"id": 2688,
							"namakecamatan": "PEDES"
						},
						{
							"id": 6634,
							"namakecamatan": "PEDONGGA"
						},
						{
							"id": 3401,
							"namakecamatan": "PEDURUNGAN"
						},
						{
							"id": 577,
							"namakecamatan": "PEGAGAN HILIR"
						},
						{
							"id": 702,
							"namakecamatan": "PEGAJAHAN"
						},
						{
							"id": 3290,
							"namakecamatan": "PEGANDON"
						},
						{
							"id": 4069,
							"namakecamatan": "PEGANTENAN"
						},
						{
							"id": 65,
							"namakecamatan": "PEGASING"
						},
						{
							"id": 2947,
							"namakecamatan": "PEJAGOAN"
						},
						{
							"id": 3776,
							"namakecamatan": "PEJARAKAN"
						},
						{
							"id": 2929,
							"namakecamatan": "PEJAWARAN"
						},
						{
							"id": 1131,
							"namakecamatan": "PEKAITAN"
						},
						{
							"id": 2804,
							"namakecamatan": "PEKALIPAN"
						},
						{
							"id": 1916,
							"namakecamatan": "PEKALONGAN"
						},
						{
							"id": 3412,
							"namakecamatan": "PEKALONGAN BARAT"
						},
						{
							"id": 3415,
							"namakecamatan": "PEKALONGAN SELATAN"
						},
						{
							"id": 3413,
							"namakecamatan": "PEKALONGAN TIMUR"
						},
						{
							"id": 3414,
							"namakecamatan": "PEKALONGAN UTARA"
						},
						{
							"id": 1172,
							"namakecamatan": "PEKANBARU KOTA"
						},
						{
							"id": 4468,
							"namakecamatan": "PEKAT"
						},
						{
							"id": 2885,
							"namakecamatan": "PEKUNCEN"
						},
						{
							"id": 4332,
							"namakecamatan": "PEKUTATAN"
						},
						{
							"id": 1723,
							"namakecamatan": "PELABAI"
						},
						{
							"id": 2235,
							"namakecamatan": "PELABUHANRATU"
						},
						{
							"id": 5209,
							"namakecamatan": "PELAIHARI"
						},
						{
							"id": 1095,
							"namakecamatan": "PELALAWAN"
						},
						{
							"id": 1084,
							"namakecamatan": "PELANGIRAN"
						},
						{
							"id": 1239,
							"namakecamatan": "PELAWAN"
						},
						{
							"id": 1321,
							"namakecamatan": "PELAYANGAN"
						},
						{
							"id": 7028,
							"namakecamatan": "PELEBAGA"
						},
						{
							"id": 914,
							"namakecamatan": "PELEMBAYAN"
						},
						{
							"id": 1293,
							"namakecamatan": "PELEPAT"
						},
						{
							"id": 1296,
							"namakecamatan": "PELEPAT ILIR"
						},
						{
							"id": 5857,
							"namakecamatan": "PELING TENGAH"
						},
						{
							"id": 4945,
							"namakecamatan": "PEMAHAN"
						},
						{
							"id": 3342,
							"namakecamatan": "PEMALANG"
						},
						{
							"id": 2026,
							"namakecamatan": "PEMALI"
						},
						{
							"id": 4867,
							"namakecamatan": "PEMANGKAT"
						},
						{
							"id": 504,
							"namakecamatan": "PEMATANG BANDAR"
						},
						{
							"id": 433,
							"namakecamatan": "PEMATANG JAYA"
						},
						{
							"id": 5197,
							"namakecamatan": "PEMATANG KARAU"
						},
						{
							"id": 1900,
							"namakecamatan": "PEMATANG SAWA"
						},
						{
							"id": 493,
							"namakecamatan": "PEMATANG SIDAMANIK"
						},
						{
							"id": 1662,
							"namakecamatan": "PEMATANG TIGA"
						},
						{
							"id": 1738,
							"namakecamatan": "PEMATANG TIGA"
						},
						{
							"id": 1249,
							"namakecamatan": "PEMAYUNG"
						},
						{
							"id": 4501,
							"namakecamatan": "PEMENANG"
						},
						{
							"id": 1376,
							"namakecamatan": "PEMULUTAN"
						},
						{
							"id": 1542,
							"namakecamatan": "PEMULUTAN"
						},
						{
							"id": 1547,
							"namakecamatan": "PEMULUTAN BARAT"
						},
						{
							"id": 1546,
							"namakecamatan": "PEMULUTAN SELATAN"
						},
						{
							"id": 5440,
							"namakecamatan": "PENAJAM"
						},
						{
							"id": 190,
							"namakecamatan": "PENANGGALAN"
						},
						{
							"id": 306,
							"namakecamatan": "PENANGGALAN"
						},
						{
							"id": 1709,
							"namakecamatan": "PENARIK"
						},
						{
							"id": 3134,
							"namakecamatan": "PENAWANGAN"
						},
						{
							"id": 1877,
							"namakecamatan": "PENAWAR AJI"
						},
						{
							"id": 1867,
							"namakecamatan": "PENAWAR TAMA"
						},
						{
							"id": 1117,
							"namakecamatan": "PENDALIAN IV KOTO"
						},
						{
							"id": 1555,
							"namakecamatan": "PENDOPO"
						},
						{
							"id": 1418,
							"namakecamatan": "PENDOPO"
						},
						{
							"id": 1563,
							"namakecamatan": "PENDOPO BARAT"
						},
						{
							"id": 4342,
							"namakecamatan": "PENEBEL"
						},
						{
							"id": 1762,
							"namakecamatan": "PENENGAHAN"
						},
						{
							"id": 1266,
							"namakecamatan": "PENGABUAN"
						},
						{
							"id": 2912,
							"namakecamatan": "PENGADEGAN"
						},
						{
							"id": 1343,
							"namakecamatan": "PENGANDONAN"
						},
						{
							"id": 5247,
							"namakecamatan": "PENGAROM"
						},
						{
							"id": 3426,
							"namakecamatan": "PENGASIH"
						},
						{
							"id": 4988,
							"namakecamatan": "PENGKADAN"
						},
						{
							"id": 1344,
							"namakecamatan": "PENINJAUAN"
						},
						{
							"id": 2161,
							"namakecamatan": "PENJARINGAN"
						},
						{
							"id": 6092,
							"namakecamatan": "PENRANG"
						},
						{
							"id": 1403,
							"namakecamatan": "PENUKAL"
						},
						{
							"id": 1566,
							"namakecamatan": "PENUKAL"
						},
						{
							"id": 1565,
							"namakecamatan": "PENUKAL UTARA"
						},
						{
							"id": 1408,
							"namakecamatan": "PENUKAL UTARA"
						},
						{
							"id": 5413,
							"namakecamatan": "PENYINGGAHAN"
						},
						{
							"id": 7262,
							"namakecamatan": "PEPERA"
						},
						{
							"id": 3845,
							"namakecamatan": "PERAK"
						},
						{
							"id": 1047,
							"namakecamatan": "PERANAP"
						},
						{
							"id": 690,
							"namakecamatan": "PERBAUNGAN"
						},
						{
							"id": 479,
							"namakecamatan": "PERBAUNGAN"
						},
						{
							"id": 476,
							"namakecamatan": "PERCUT SEI TUAN"
						},
						{
							"id": 666,
							"namakecamatan": "PERGETTENG GETTENGSENGKUT"
						},
						{
							"id": 1037,
							"namakecamatan": "PERHENTIAN RAJA"
						},
						{
							"id": 4303,
							"namakecamatan": "PERIUK"
						},
						{
							"id": 268,
							"namakecamatan": "PERMATA"
						},
						{
							"id": 74,
							"namakecamatan": "PERMATA"
						},
						{
							"id": 5185,
							"namakecamatan": "PERMATA INTAN"
						},
						{
							"id": 5153,
							"namakecamatan": "PERMATA KECUBUNG"
						},
						{
							"id": 3697,
							"namakecamatan": "PESANGGARAN"
						},
						{
							"id": 2184,
							"namakecamatan": "PESANGGRAHAN"
						},
						{
							"id": 4105,
							"namakecamatan": "PESANTREN"
						},
						{
							"id": 1201,
							"namakecamatan": "PESISIR BUKIT"
						},
						{
							"id": 1329,
							"namakecamatan": "PESISIR BUKIT"
						},
						{
							"id": 1987,
							"namakecamatan": "PESISIR SELATAN"
						},
						{
							"id": 1829,
							"namakecamatan": "PESISIR SELATAN"
						},
						{
							"id": 1830,
							"namakecamatan": "PESISIR TENGAH"
						},
						{
							"id": 1986,
							"namakecamatan": "PESISIR TENGAH"
						},
						{
							"id": 1989,
							"namakecamatan": "PESISIR UTARA"
						},
						{
							"id": 1831,
							"namakecamatan": "PESISIR UTARA"
						},
						{
							"id": 5474,
							"namakecamatan": "PESO"
						},
						{
							"id": 5475,
							"namakecamatan": "PESO HILIR"
						},
						{
							"id": 5133,
							"namakecamatan": "PETAK MALAI"
						},
						{
							"id": 2938,
							"namakecamatan": "PETANAHAN"
						},
						{
							"id": 4348,
							"namakecamatan": "PETANG"
						},
						{
							"id": 3344,
							"namakecamatan": "PETARUKAN"
						},
						{
							"id": 5918,
							"namakecamatan": "PETASIA"
						},
						{
							"id": 5825,
							"namakecamatan": "PETASIA"
						},
						{
							"id": 5841,
							"namakecamatan": "PETASIA BARAT"
						},
						{
							"id": 5927,
							"namakecamatan": "PETASIA BARAT"
						},
						{
							"id": 5919,
							"namakecamatan": "PETASIA TIMUR"
						},
						{
							"id": 5839,
							"namakecamatan": "PETASIA TIMUR"
						},
						{
							"id": 3854,
							"namakecamatan": "PETERONGAN"
						},
						{
							"id": 4279,
							"namakecamatan": "PETIR"
						},
						{
							"id": 3319,
							"namakecamatan": "PETUNGKRIYONO"
						},
						{
							"id": 201,
							"namakecamatan": "PEUDADA"
						},
						{
							"id": 50,
							"namakecamatan": "PEUDAWA"
						},
						{
							"id": 99,
							"namakecamatan": "PEUKAN BADA"
						},
						{
							"id": 129,
							"namakecamatan": "PEUKAN BARO"
						},
						{
							"id": 210,
							"namakecamatan": "PEULIMBANG"
						},
						{
							"id": 58,
							"namakecamatan": "PEUNARON"
						},
						{
							"id": 2385,
							"namakecamatan": "PEUNDEUY"
						},
						{
							"id": 41,
							"namakecamatan": "PEUREULAK"
						},
						{
							"id": 52,
							"namakecamatan": "PEUREULAK BARAT"
						},
						{
							"id": 51,
							"namakecamatan": "PEUREULAK TIMUR"
						},
						{
							"id": 203,
							"namakecamatan": "PEUSANGAN"
						},
						{
							"id": 214,
							"namakecamatan": "PEUSANGAN SELATAN"
						},
						{
							"id": 213,
							"namakecamatan": "PEUSANGAN SIBLAH KRUENG"
						},
						{
							"id": 5282,
							"namakecamatan": "PIANI"
						},
						{
							"id": 4172,
							"namakecamatan": "PICUNG"
						},
						{
							"id": 130,
							"namakecamatan": "PIDIE"
						},
						{
							"id": 7544,
							"namakecamatan": "PIJA"
						},
						{
							"id": 3898,
							"namakecamatan": "PILANGKENCENG"
						},
						{
							"id": 4306,
							"namakecamatan": "PINANG"
						},
						{
							"id": 1726,
							"namakecamatan": "PINANG BELAPIS"
						},
						{
							"id": 1669,
							"namakecamatan": "PINANG RAYA"
						},
						{
							"id": 313,
							"namakecamatan": "PINANGSORI"
						},
						{
							"id": 5565,
							"namakecamatan": "PINELENG"
						},
						{
							"id": 5791,
							"namakecamatan": "PINEMBANI"
						},
						{
							"id": 1069,
							"namakecamatan": "PINGGIR"
						},
						{
							"id": 229,
							"namakecamatan": "PINING"
						},
						{
							"id": 1613,
							"namakecamatan": "PINO"
						},
						{
							"id": 5672,
							"namakecamatan": "PINOGALUMAN"
						},
						{
							"id": 5518,
							"namakecamatan": "PINOGALUMAN"
						},
						{
							"id": 6592,
							"namakecamatan": "PINOGU"
						},
						{
							"id": 5038,
							"namakecamatan": "PINOH SELATAN"
						},
						{
							"id": 5037,
							"namakecamatan": "PINOH UTARA"
						},
						{
							"id": 5690,
							"namakecamatan": "PINOLOSIAN"
						},
						{
							"id": 5525,
							"namakecamatan": "PINOLOSIAN (BARAT)"
						},
						{
							"id": 5545,
							"namakecamatan": "PINOLOSIAN TENGAH"
						},
						{
							"id": 5691,
							"namakecamatan": "PINOLOSIAN TENGAH"
						},
						{
							"id": 5692,
							"namakecamatan": "PINOLOSIAN TIMUR"
						},
						{
							"id": 5540,
							"namakecamatan": "PINOLOSIAN TIMUR"
						},
						{
							"id": 1616,
							"namakecamatan": "PINO RAYA"
						},
						{
							"id": 588,
							"namakecamatan": "PINTU POHAN MERANTI"
						},
						{
							"id": 267,
							"namakecamatan": "PINTU RIME GAYO"
						},
						{
							"id": 72,
							"namakecamatan": "PINTU RIME GAYO"
						},
						{
							"id": 4743,
							"namakecamatan": "PINU PAHAR"
						},
						{
							"id": 5772,
							"namakecamatan": "PIPIKORO"
						},
						{
							"id": 5902,
							"namakecamatan": "PIPIKORO"
						},
						{
							"id": 169,
							"namakecamatan": "PIRAK TIMUR"
						},
						{
							"id": 7053,
							"namakecamatan": "PIRAMID"
						},
						{
							"id": 7480,
							"namakecamatan": "PIRIME"
						},
						{
							"id": 7003,
							"namakecamatan": "PIRIME"
						},
						{
							"id": 7047,
							"namakecamatan": "PISUGI"
						},
						{
							"id": 3934,
							"namakecamatan": "PITU"
						},
						{
							"id": 6090,
							"namakecamatan": "PITUMPANUA"
						},
						{
							"id": 6105,
							"namakecamatan": "PITU RAISE"
						},
						{
							"id": 6104,
							"namakecamatan": "PITU RIAWA"
						},
						{
							"id": 2971,
							"namakecamatan": "PITURUH"
						},
						{
							"id": 7577,
							"namakecamatan": "PIYAIYE"
						},
						{
							"id": 7090,
							"namakecamatan": "PIYAIYE"
						},
						{
							"id": 3445,
							"namakecamatan": "PIYUNGAN"
						},
						{
							"id": 1589,
							"namakecamatan": "PLAJU"
						},
						{
							"id": 1475,
							"namakecamatan": "PLAKAT TINGGI"
						},
						{
							"id": 4446,
							"namakecamatan": "PLAMPANG"
						},
						{
							"id": 3859,
							"namakecamatan": "PLANDAAN"
						},
						{
							"id": 3281,
							"namakecamatan": "PLANTUNGAN"
						},
						{
							"id": 3907,
							"namakecamatan": "PLAOSAN"
						},
						{
							"id": 3451,
							"namakecamatan": "PLAYEN"
						},
						{
							"id": 3601,
							"namakecamatan": "PLEMAHAN"
						},
						{
							"id": 2544,
							"namakecamatan": "PLERED"
						},
						{
							"id": 2665,
							"namakecamatan": "PLERED"
						},
						{
							"id": 3444,
							"namakecamatan": "PLERET"
						},
						{
							"id": 3858,
							"namakecamatan": "PLOSO"
						},
						{
							"id": 3594,
							"namakecamatan": "PLOSOKLATEN"
						},
						{
							"id": 2526,
							"namakecamatan": "PLUMBON"
						},
						{
							"id": 3982,
							"namakecamatan": "PLUMPANG"
						},
						{
							"id": 3113,
							"namakecamatan": "PLUPUH"
						},
						{
							"id": 6529,
							"namakecamatan": "POASIA"
						},
						{
							"id": 4831,
							"namakecamatan": "POCO RANAKA"
						},
						{
							"id": 4722,
							"namakecamatan": "POCO RANAKA"
						},
						{
							"id": 4837,
							"namakecamatan": "POCO RANAKA TIMUR"
						},
						{
							"id": 7488,
							"namakecamatan": "POGA"
						},
						{
							"id": 7031,
							"namakecamatan": "POGA"
						},
						{
							"id": 3542,
							"namakecamatan": "POGALAN"
						},
						{
							"id": 7367,
							"namakecamatan": "POGANERI"
						},
						{
							"id": 7554,
							"namakecamatan": "POGOMA"
						},
						{
							"id": 7154,
							"namakecamatan": "POGOMA"
						},
						{
							"id": 3801,
							"namakecamatan": "POHJENTREK"
						},
						{
							"id": 5537,
							"namakecamatan": "POIGAR"
						},
						{
							"id": 7138,
							"namakecamatan": "POIRU"
						},
						{
							"id": 3048,
							"namakecamatan": "POLANHARJO"
						},
						{
							"id": 6365,
							"namakecamatan": "POLEANG"
						},
						{
							"id": 6418,
							"namakecamatan": "POLEANG"
						},
						{
							"id": 6424,
							"namakecamatan": "POLEANG BARAT"
						},
						{
							"id": 6428,
							"namakecamatan": "POLEANG SELATAN"
						},
						{
							"id": 6436,
							"namakecamatan": "POLEANG TENGAH"
						},
						{
							"id": 6429,
							"namakecamatan": "POLEANG TENGGARA"
						},
						{
							"id": 6366,
							"namakecamatan": "POLEANG TIMUR"
						},
						{
							"id": 6419,
							"namakecamatan": "POLEANG TIMUR"
						},
						{
							"id": 6427,
							"namakecamatan": "POLEANG UTARA"
						},
						{
							"id": 4555,
							"namakecamatan": "POLEN"
						},
						{
							"id": 6674,
							"namakecamatan": "POLEWALI"
						},
						{
							"id": 6287,
							"namakecamatan": "POLINGGONA"
						},
						{
							"id": 6485,
							"namakecamatan": "POLI POLIA"
						},
						{
							"id": 6284,
							"namakecamatan": "POLI-POLIA"
						},
						{
							"id": 671,
							"namakecamatan": "POLLUNG"
						},
						{
							"id": 3064,
							"namakecamatan": "POLOKARTO"
						},
						{
							"id": 5978,
							"namakecamatan": "POLOMBANGKENGSELATAN"
						},
						{
							"id": 5979,
							"namakecamatan": "POLOMBANGKENG UTARA"
						},
						{
							"id": 6269,
							"namakecamatan": "POMALAA"
						},
						{
							"id": 3618,
							"namakecamatan": "PONCOKUSUMO"
						},
						{
							"id": 3901,
							"namakecamatan": "PONCOL"
						},
						{
							"id": 2959,
							"namakecamatan": "PONCOWARNO"
						},
						{
							"id": 6293,
							"namakecamatan": "PONDIDAHA"
						},
						{
							"id": 4248,
							"namakecamatan": "PONDOK AREN"
						},
						{
							"id": 4325,
							"namakecamatan": "PONDOK AREN"
						},
						{
							"id": 2813,
							"namakecamatan": "PONDOK GEDE"
						},
						{
							"id": 1663,
							"namakecamatan": "PONDOK KELAPA"
						},
						{
							"id": 1737,
							"namakecamatan": "PONDOK KELAPA"
						},
						{
							"id": 1743,
							"namakecamatan": "PONDOK KUBANG"
						},
						{
							"id": 2817,
							"namakecamatan": "PONDOK MELATI"
						},
						{
							"id": 2677,
							"namakecamatan": "PONDOKSALAM"
						},
						{
							"id": 1703,
							"namakecamatan": "PONDOK SUGUH"
						},
						{
							"id": 1333,
							"namakecamatan": "PONDOK TINGGI"
						},
						{
							"id": 6613,
							"namakecamatan": "PONELO KEPULAUAN"
						},
						{
							"id": 3569,
							"namakecamatan": "PONGGOK"
						},
						{
							"id": 3458,
							"namakecamatan": "PONJONG"
						},
						{
							"id": 3526,
							"namakecamatan": "PONOROGO"
						},
						{
							"id": 6140,
							"namakecamatan": "PONRANG"
						},
						{
							"id": 6150,
							"namakecamatan": "PONRANG SELATAN"
						},
						{
							"id": 6022,
							"namakecamatan": "PONRE"
						},
						{
							"id": 4272,
							"namakecamatan": "PONTANG"
						},
						{
							"id": 5058,
							"namakecamatan": "PONTIANAK BARAT"
						},
						{
							"id": 5060,
							"namakecamatan": "PONTIANAK KOTA"
						},
						{
							"id": 5056,
							"namakecamatan": "PONTIANAK SELATAN"
						},
						{
							"id": 5061,
							"namakecamatan": "PONTIANAK TENGGARA"
						},
						{
							"id": 5057,
							"namakecamatan": "PONTIANAK TIMUR"
						},
						{
							"id": 5059,
							"namakecamatan": "PONTIANAK UTARA"
						},
						{
							"id": 7111,
							"namakecamatan": "POOM"
						},
						{
							"id": 6593,
							"namakecamatan": "POPAYATO"
						},
						{
							"id": 6605,
							"namakecamatan": "POPAYATO BARAT"
						},
						{
							"id": 6604,
							"namakecamatan": "POPAYATO TIMUR"
						},
						{
							"id": 7060,
							"namakecamatan": "POPUGOBA"
						},
						{
							"id": 6459,
							"namakecamatan": "POREHU"
						},
						{
							"id": 3812,
							"namakecamatan": "PORONG"
						},
						{
							"id": 590,
							"namakecamatan": "PORSEA"
						},
						{
							"id": 718,
							"namakecamatan": "PORTIBI"
						},
						{
							"id": 367,
							"namakecamatan": "PORTIBI"
						},
						{
							"id": 5689,
							"namakecamatan": "POSIGADAN"
						},
						{
							"id": 5523,
							"namakecamatan": "POSIGADAN"
						},
						{
							"id": 5744,
							"namakecamatan": "POSO KOTA"
						},
						{
							"id": 5764,
							"namakecamatan": "POSO KOTA SELATAN"
						},
						{
							"id": 5765,
							"namakecamatan": "POSO KOTA UTARA"
						},
						{
							"id": 5745,
							"namakecamatan": "POSO PESISIR"
						},
						{
							"id": 5762,
							"namakecamatan": "POSO PESISIR SELATAN"
						},
						{
							"id": 5761,
							"namakecamatan": "POSO PESISIR UTARA"
						},
						{
							"id": 4494,
							"namakecamatan": "POTO TANO"
						},
						{
							"id": 6732,
							"namakecamatan": "P.P. ARU SELATAN"
						},
						{
							"id": 6733,
							"namakecamatan": "P.P.ARU TENGAH"
						},
						{
							"id": 1605,
							"namakecamatan": "PRABUMULIH BARAT"
						},
						{
							"id": 1610,
							"namakecamatan": "PRABUMULIH SELATAN"
						},
						{
							"id": 1606,
							"namakecamatan": "PRABUMULIH TIMUR"
						},
						{
							"id": 1609,
							"namakecamatan": "PRABUMULIH UTARA"
						},
						{
							"id": 3070,
							"namakecamatan": "PRACIMANTORO"
						},
						{
							"id": 7660,
							"namakecamatan": "PRAFI"
						},
						{
							"id": 4086,
							"namakecamatan": "PRAGAAN"
						},
						{
							"id": 3736,
							"namakecamatan": "PRAJEKAN"
						},
						{
							"id": 4123,
							"namakecamatan": "PRAJURIT KULON"
						},
						{
							"id": 3475,
							"namakecamatan": "PRAMBANAN"
						},
						{
							"id": 3032,
							"namakecamatan": "PRAMBANAN"
						},
						{
							"id": 3810,
							"namakecamatan": "PRAMBON"
						},
						{
							"id": 3871,
							"namakecamatan": "PRAMBON"
						},
						{
							"id": 4402,
							"namakecamatan": "PRAYA"
						},
						{
							"id": 4406,
							"namakecamatan": "PRAYA BARAT"
						},
						{
							"id": 4412,
							"namakecamatan": "PRAYA BARAT DAYA"
						},
						{
							"id": 4411,
							"namakecamatan": "PRAYA TENGAH"
						},
						{
							"id": 4407,
							"namakecamatan": "PRAYA TIMUR"
						},
						{
							"id": 2943,
							"namakecamatan": "PREMBUN"
						},
						{
							"id": 3794,
							"namakecamatan": "PRIGEN"
						},
						{
							"id": 3255,
							"namakecamatan": "PRINGAPUS"
						},
						{
							"id": 4421,
							"namakecamatan": "PRINGGABAYA"
						},
						{
							"id": 4409,
							"namakecamatan": "PRINGGARATA"
						},
						{
							"id": 4425,
							"namakecamatan": "PRINGGASELA"
						},
						{
							"id": 3499,
							"namakecamatan": "PRINGKUKU"
						},
						{
							"id": 1890,
							"namakecamatan": "PRINGSEWU"
						},
						{
							"id": 1962,
							"namakecamatan": "PRINGSEWU"
						},
						{
							"id": 3264,
							"namakecamatan": "PRINGSURAT"
						},
						{
							"id": 7313,
							"namakecamatan": "PRONGGOLI"
						},
						{
							"id": 3646,
							"namakecamatan": "PRONOJIWO"
						},
						{
							"id": 4067,
							"namakecamatan": "PROPPO"
						},
						{
							"id": 1435,
							"namakecamatan": "PSEKSU"
						},
						{
							"id": 1796,
							"namakecamatan": "PUBIAN"
						},
						{
							"id": 3185,
							"namakecamatan": "PUCAKWANGI"
						},
						{
							"id": 3556,
							"namakecamatan": "PUCANGLABAN"
						},
						{
							"id": 3998,
							"namakecamatan": "PUCUK"
						},
						{
							"id": 1161,
							"namakecamatan": "PUCUK RANTAU"
						},
						{
							"id": 3530,
							"namakecamatan": "PUDAK"
						},
						{
							"id": 2029,
							"namakecamatan": "PUDING BESAR"
						},
						{
							"id": 5327,
							"namakecamatan": "PUGAAN"
						},
						{
							"id": 3673,
							"namakecamatan": "PUGER"
						},
						{
							"id": 7199,
							"namakecamatan": "PUGO DAGI"
						},
						{
							"id": 1895,
							"namakecamatan": "PUGUNG"
						},
						{
							"id": 3094,
							"namakecamatan": "PUHPELEM"
						},
						{
							"id": 6071,
							"namakecamatan": "PUJANANTING"
						},
						{
							"id": 3725,
							"namakecamatan": "PUJER"
						},
						{
							"id": 3637,
							"namakecamatan": "PUJON"
						},
						{
							"id": 1125,
							"namakecamatan": "PUJUD"
						},
						{
							"id": 5480,
							"namakecamatan": "PUJUNGAN"
						},
						{
							"id": 4405,
							"namakecamatan": "PUJUT"
						},
						{
							"id": 183,
							"namakecamatan": "PULAU BANYAK"
						},
						{
							"id": 198,
							"namakecamatan": "PULAU BANYAK BARAT"
						},
						{
							"id": 6964,
							"namakecamatan": "PULAU BATANG DUA"
						},
						{
							"id": 1520,
							"namakecamatan": "PULAU BERINGIN"
						},
						{
							"id": 1337,
							"namakecamatan": "PULAU BERINGIN"
						},
						{
							"id": 2041,
							"namakecamatan": "PULAUBESAR"
						},
						{
							"id": 1086,
							"namakecamatan": "PULAU BURUNG"
						},
						{
							"id": 5394,
							"namakecamatan": "PULAU DERAWAN"
						},
						{
							"id": 6843,
							"namakecamatan": "PULAU DULLAH SELATAN"
						},
						{
							"id": 6737,
							"namakecamatan": "PULAU DULLAH SELATAN"
						},
						{
							"id": 6736,
							"namakecamatan": "PULAU DULLAH UTARA"
						},
						{
							"id": 6842,
							"namakecamatan": "PULAU DULLAH UTARA"
						},
						{
							"id": 4681,
							"namakecamatan": "PULAU ENDE"
						},
						{
							"id": 6858,
							"namakecamatan": "PULAU GEBE"
						},
						{
							"id": 6718,
							"namakecamatan": "PULAU GOROM"
						},
						{
							"id": 6781,
							"namakecamatan": "PULAU GOROM"
						},
						{
							"id": 5081,
							"namakecamatan": "PULAU HANAUT"
						},
						{
							"id": 6712,
							"namakecamatan": "PULAU HARUKU"
						},
						{
							"id": 6966,
							"namakecamatan": "PULAU HIRI"
						},
						{
							"id": 7119,
							"namakecamatan": "PULAU KURUDU"
						},
						{
							"id": 6825,
							"namakecamatan": "PULAU LAKOR"
						},
						{
							"id": 2105,
							"namakecamatan": "PULAU LAUT"
						},
						{
							"id": 5219,
							"namakecamatan": "PULAULAUT BARAT"
						},
						{
							"id": 5237,
							"namakecamatan": "PULAULAUT KEPULAUAN"
						},
						{
							"id": 5220,
							"namakecamatan": "PULAULAUT SELATAN"
						},
						{
							"id": 5238,
							"namakecamatan": "PULAULAUT TANJUNGSELAYAR"
						},
						{
							"id": 5233,
							"namakecamatan": "PULAULAUT TENGAH"
						},
						{
							"id": 5221,
							"namakecamatan": "PULAULAUT TIMUR"
						},
						{
							"id": 5223,
							"namakecamatan": "PULAULAUT UTARA"
						},
						{
							"id": 6821,
							"namakecamatan": "PULAU LETI"
						},
						{
							"id": 6757,
							"namakecamatan": "PULAU LETTI"
						},
						{
							"id": 6888,
							"namakecamatan": "PULAU MAKIAN"
						},
						{
							"id": 5125,
							"namakecamatan": "PULAU MALAN"
						},
						{
							"id": 6822,
							"namakecamatan": "PULAU MASELA"
						},
						{
							"id": 5044,
							"namakecamatan": "PULAU MAYA"
						},
						{
							"id": 4930,
							"namakecamatan": "PULAU MAYA KARIMATA"
						},
						{
							"id": 1167,
							"namakecamatan": "PULAUMERBAU"
						},
						{
							"id": 6963,
							"namakecamatan": "PULAU MOTI"
						},
						{
							"id": 1888,
							"namakecamatan": "PULAU PANGGUNG"
						},
						{
							"id": 6786,
							"namakecamatan": "PULAU PANJANG"
						},
						{
							"id": 5095,
							"namakecamatan": "PULAU PETAK"
						},
						{
							"id": 1423,
							"namakecamatan": "PULAUPINANG"
						},
						{
							"id": 1991,
							"namakecamatan": "PULAUPISANG"
						},
						{
							"id": 1854,
							"namakecamatan": "PULAU PISANG"
						},
						{
							"id": 6731,
							"namakecamatan": "PULAU-PULAU ARU"
						},
						{
							"id": 6804,
							"namakecamatan": "PULAU-PULAU ARU"
						},
						{
							"id": 6754,
							"namakecamatan": "PULAU-PULAU BABAR"
						},
						{
							"id": 6817,
							"namakecamatan": "PULAU-PULAU BABAR"
						},
						{
							"id": 6755,
							"namakecamatan": "PULAU-PULAU BABARTIMUR"
						},
						{
							"id": 6818,
							"namakecamatan": "PULAU-PULAU BABARTIMUR"
						},
						{
							"id": 635,
							"namakecamatan": "PULAU-PULAU BATU"
						},
						{
							"id": 659,
							"namakecamatan": "PULAU-PULAU BATU BARAT"
						},
						{
							"id": 646,
							"namakecamatan": "PULAU-PULAU BATUTIMUR"
						},
						{
							"id": 660,
							"namakecamatan": "PULAU-PULAU BATU UTARA"
						},
						{
							"id": 6734,
							"namakecamatan": "PULAU PULAU KUR"
						},
						{
							"id": 6845,
							"namakecamatan": "PULAU-PULAU KUR"
						},
						{
							"id": 6759,
							"namakecamatan": "PULAU-PULAU TERSELATAN"
						},
						{
							"id": 6820,
							"namakecamatan": "PULAU-PULAU TERSELATAN"
						},
						{
							"id": 963,
							"namakecamatan": "PULAU PUNJUNG"
						},
						{
							"id": 862,
							"namakecamatan": "PULAU PUNJUNG"
						},
						{
							"id": 4633,
							"namakecamatan": "PULAU PURA"
						},
						{
							"id": 528,
							"namakecamatan": "PULAU RAKYAT"
						},
						{
							"id": 1483,
							"namakecamatan": "PULAU RIMAU"
						},
						{
							"id": 5222,
							"namakecamatan": "PULAUSEBUKU"
						},
						{
							"id": 5218,
							"namakecamatan": "PULAUSEMBILAN"
						},
						{
							"id": 6011,
							"namakecamatan": "PULAU SEMBILAN"
						},
						{
							"id": 6960,
							"namakecamatan": "PULAU TERNATE"
						},
						{
							"id": 7448,
							"namakecamatan": "PULAU TIGA"
						},
						{
							"id": 2106,
							"namakecamatan": "PULAU TIGA"
						},
						{
							"id": 2116,
							"namakecamatan": "PULAU TIGA BARAT"
						},
						{
							"id": 6824,
							"namakecamatan": "PULAU WETANG"
						},
						{
							"id": 7120,
							"namakecamatan": "PULAU YERUI"
						},
						{
							"id": 7319,
							"namakecamatan": "PULDAMA"
						},
						{
							"id": 3533,
							"namakecamatan": "PULE"
						},
						{
							"id": 104,
							"namakecamatan": "PULO ACEH"
						},
						{
							"id": 4268,
							"namakecamatan": "PULO AMPEL"
						},
						{
							"id": 544,
							"namakecamatan": "PULO BANDRING"
						},
						{
							"id": 2186,
							"namakecamatan": "PULOGADUNG"
						},
						{
							"id": 3137,
							"namakecamatan": "PULOKULON"
						},
						{
							"id": 4311,
							"namakecamatan": "PULOMERAK"
						},
						{
							"id": 4193,
							"namakecamatan": "PULOSARI"
						},
						{
							"id": 3336,
							"namakecamatan": "PULOSARI"
						},
						{
							"id": 6559,
							"namakecamatan": "PULUBALA"
						},
						{
							"id": 3516,
							"namakecamatan": "PULUNG"
						},
						{
							"id": 5617,
							"namakecamatan": "PULUTAN"
						},
						{
							"id": 627,
							"namakecamatan": "PUNCAK SORIK MARAPI"
						},
						{
							"id": 3593,
							"namakecamatan": "PUNCU"
						},
						{
							"id": 3435,
							"namakecamatan": "PUNDONG"
						},
						{
							"id": 1773,
							"namakecamatan": "PUNDUH PIDADA"
						},
						{
							"id": 1956,
							"namakecamatan": "PUNDUH PIDADA"
						},
						{
							"id": 2926,
							"namakecamatan": "PUNGGELAN"
						},
						{
							"id": 3832,
							"namakecamatan": "PUNGGING"
						},
						{
							"id": 1783,
							"namakecamatan": "PUNGGUR"
						},
						{
							"id": 3500,
							"namakecamatan": "PUNUNG"
						},
						{
							"id": 4344,
							"namakecamatan": "PUPUAN"
						},
						{
							"id": 2272,
							"namakecamatan": "PURABAYA"
						},
						{
							"id": 497,
							"namakecamatan": "PURBA"
						},
						{
							"id": 2901,
							"namakecamatan": "PURBALINGGA"
						},
						{
							"id": 2841,
							"namakecamatan": "PURBARATU"
						},
						{
							"id": 337,
							"namakecamatan": "PURBA TUA"
						},
						{
							"id": 1920,
							"namakecamatan": "PURBOLINGGO"
						},
						{
							"id": 4635,
							"namakecamatan": "PUREMAN"
						},
						{
							"id": 3837,
							"namakecamatan": "PURI"
						},
						{
							"id": 6306,
							"namakecamatan": "PURIALA"
						},
						{
							"id": 2937,
							"namakecamatan": "PURING"
						},
						{
							"id": 4990,
							"namakecamatan": "PURING KENCANA"
						},
						{
							"id": 2637,
							"namakecamatan": "PURWADADI"
						},
						{
							"id": 2475,
							"namakecamatan": "PURWADADI"
						},
						{
							"id": 2844,
							"namakecamatan": "PURWAHARJA"
						},
						{
							"id": 2662,
							"namakecamatan": "PURWAKARTA"
						},
						{
							"id": 4315,
							"namakecamatan": "PURWAKARTA"
						},
						{
							"id": 2918,
							"namakecamatan": "PURWANEGARA"
						},
						{
							"id": 3086,
							"namakecamatan": "PURWANTORO"
						},
						{
							"id": 2707,
							"namakecamatan": "PURWASARI"
						},
						{
							"id": 3600,
							"namakecamatan": "PURWOASRI"
						},
						{
							"id": 1457,
							"namakecamatan": "PURWODADI"
						},
						{
							"id": 2963,
							"namakecamatan": "PURWODADI"
						},
						{
							"id": 3785,
							"namakecamatan": "PURWODADI"
						},
						{
							"id": 3144,
							"namakecamatan": "PURWODADI"
						},
						{
							"id": 3699,
							"namakecamatan": "PURWOHARJO"
						},
						{
							"id": 2882,
							"namakecamatan": "PURWOJATI"
						},
						{
							"id": 2894,
							"namakecamatan": "PURWOKERTO BARAT"
						},
						{
							"id": 2893,
							"namakecamatan": "PURWOKERTO SELATAN"
						},
						{
							"id": 2895,
							"namakecamatan": "PURWOKERTO TIMUR"
						},
						{
							"id": 2896,
							"namakecamatan": "PURWOKERTO UTARA"
						},
						{
							"id": 2916,
							"namakecamatan": "PURWOREJA KLAMPOK"
						},
						{
							"id": 2966,
							"namakecamatan": "PURWOREJO"
						},
						{
							"id": 4120,
							"namakecamatan": "PURWOREJO"
						},
						{
							"id": 3955,
							"namakecamatan": "PURWOSARI"
						},
						{
							"id": 3466,
							"namakecamatan": "PURWOSARI"
						},
						{
							"id": 3792,
							"namakecamatan": "PURWOSARI"
						},
						{
							"id": 2661,
							"namakecamatan": "PUSAKAJAYA"
						},
						{
							"id": 2641,
							"namakecamatan": "PUSAKANAGARA"
						},
						{
							"id": 1146,
							"namakecamatan": "PUSAKO"
						},
						{
							"id": 5641,
							"namakecamatan": "PUSOMAEN"
						},
						{
							"id": 5656,
							"namakecamatan": "PUSOMAEN"
						},
						{
							"id": 2416,
							"namakecamatan": "PUSPAHIANG"
						},
						{
							"id": 3787,
							"namakecamatan": "PUSPO"
						},
						{
							"id": 231,
							"namakecamatan": "PUTERI BETUNG"
						},
						{
							"id": 1805,
							"namakecamatan": "PUTRA RUMBIA"
						},
						{
							"id": 1659,
							"namakecamatan": "PUTRI HIJAU"
						},
						{
							"id": 4984,
							"namakecamatan": "PUTUSSIBAU SELATAN"
						},
						{
							"id": 4968,
							"namakecamatan": "PUTUSSIBAU UTARA"
						},
						{
							"id": 6534,
							"namakecamatan": "PUUWATU"
						},
						{
							"id": 4097,
							"namakecamatan": "RA\"AS"
						},
						{
							"id": 4511,
							"namakecamatan": "RABA"
						},
						{
							"id": 4734,
							"namakecamatan": "RAHONG UTARA"
						},
						{
							"id": 536,
							"namakecamatan": "RAHUNIG"
						},
						{
							"id": 4601,
							"namakecamatan": "RAIHAT"
						},
						{
							"id": 4513,
							"namakecamatan": "RAIJUA"
						},
						{
							"id": 4844,
							"namakecamatan": "RAIJUA"
						},
						{
							"id": 4611,
							"namakecamatan": "RAIMANUK"
						},
						{
							"id": 7114,
							"namakecamatan": "RAIMBAWI"
						},
						{
							"id": 5605,
							"namakecamatan": "RAINIS"
						},
						{
							"id": 2006,
							"namakecamatan": "RAJABASA"
						},
						{
							"id": 1769,
							"namakecamatan": "RAJA BASA"
						},
						{
							"id": 2453,
							"namakecamatan": "RAJADESA"
						},
						{
							"id": 2557,
							"namakecamatan": "RAJAGALUH"
						},
						{
							"id": 2435,
							"namakecamatan": "RAJAPOLAH"
						},
						{
							"id": 4235,
							"namakecamatan": "RAJEG"
						},
						{
							"id": 2925,
							"namakecamatan": "RAKIT"
						},
						{
							"id": 1055,
							"namakecamatan": "RAKIT KULIM"
						},
						{
							"id": 5206,
							"namakecamatan": "RAKUMPIT"
						},
						{
							"id": 1921,
							"namakecamatan": "RAMAN UTARA"
						},
						{
							"id": 1104,
							"namakecamatan": "RAMBAH"
						},
						{
							"id": 1109,
							"namakecamatan": "RAMBAH HILIR"
						},
						{
							"id": 1108,
							"namakecamatan": "RAMBAH SAMO"
						},
						{
							"id": 1405,
							"namakecamatan": "RAMBANG"
						},
						{
							"id": 1393,
							"namakecamatan": "RAMBANG DANGKU"
						},
						{
							"id": 1608,
							"namakecamatan": "RAMBANG KPK TENGAH"
						},
						{
							"id": 1551,
							"namakecamatan": "RAMBANG KUANG"
						},
						{
							"id": 875,
							"namakecamatan": "RAMBATAN"
						},
						{
							"id": 3678,
							"namakecamatan": "RAMBIPUJI"
						},
						{
							"id": 1485,
							"namakecamatan": "RAMBUTAN"
						},
						{
							"id": 811,
							"namakecamatan": "RAMBUTAN"
						},
						{
							"id": 6200,
							"namakecamatan": "RAMPI"
						},
						{
							"id": 840,
							"namakecamatan": "RANAH AMPEK HULUTAPAN"
						},
						{
							"id": 943,
							"namakecamatan": "RANAH BATAHAN"
						},
						{
							"id": 986,
							"namakecamatan": "RANAH BATAHAN"
						},
						{
							"id": 828,
							"namakecamatan": "RANAH PESISIR"
						},
						{
							"id": 4836,
							"namakecamatan": "RANA MESE"
						},
						{
							"id": 2353,
							"namakecamatan": "RANCABALI"
						},
						{
							"id": 2228,
							"namakecamatan": "RANCA BUNGUR"
						},
						{
							"id": 2341,
							"namakecamatan": "RANCAEKEK"
						},
						{
							"id": 2455,
							"namakecamatan": "RANCAH"
						},
						{
							"id": 2590,
							"namakecamatan": "RANCAKALONG"
						},
						{
							"id": 2793,
							"namakecamatan": "RANCASARI"
						},
						{
							"id": 6595,
							"namakecamatan": "RANDANGAN"
						},
						{
							"id": 3662,
							"namakecamatan": "RANDUAGUNG"
						},
						{
							"id": 3152,
							"namakecamatan": "RANDUBLATUNG"
						},
						{
							"id": 3341,
							"namakecamatan": "RANDUDONGKAL"
						},
						{
							"id": 4210,
							"namakecamatan": "RANGKASBITUNG"
						},
						{
							"id": 2065,
							"namakecamatan": "RANGKUI"
						},
						{
							"id": 1164,
							"namakecamatan": "RANGSANG"
						},
						{
							"id": 1063,
							"namakecamatan": "RANGSANG"
						},
						{
							"id": 1163,
							"namakecamatan": "RANGSANG BARAT"
						},
						{
							"id": 1064,
							"namakecamatan": "RANGSANG BARAT"
						},
						{
							"id": 1170,
							"namakecamatan": "RANGSANG PESISIR"
						},
						{
							"id": 6188,
							"namakecamatan": "RANO"
						},
						{
							"id": 6403,
							"namakecamatan": "RANOMEETO"
						},
						{
							"id": 6417,
							"namakecamatan": "RANOMEETO BARAT"
						},
						{
							"id": 5706,
							"namakecamatan": "RANOWULU"
						},
						{
							"id": 5624,
							"namakecamatan": "RANOYAPO"
						},
						{
							"id": 7852,
							"namakecamatan": "RANSIKI"
						},
						{
							"id": 7658,
							"namakecamatan": "RANSIKI"
						},
						{
							"id": 262,
							"namakecamatan": "RANTAU"
						},
						{
							"id": 1382,
							"namakecamatan": "RANTAU ALAI"
						},
						{
							"id": 1543,
							"namakecamatan": "RANTAU ALAI"
						},
						{
							"id": 5264,
							"namakecamatan": "RANTAU BADAUH"
						},
						{
							"id": 1490,
							"namakecamatan": "RANTAU BAYUR"
						},
						{
							"id": 1130,
							"namakecamatan": "RANTAU KOPAR"
						},
						{
							"id": 1289,
							"namakecamatan": "RANTAU PANDAN"
						},
						{
							"id": 1548,
							"namakecamatan": "RANTAU PANJANG"
						},
						{
							"id": 44,
							"namakecamatan": "RANTAU PEUREULAK"
						},
						{
							"id": 5435,
							"namakecamatan": "RANTAU PULUNG"
						},
						{
							"id": 1280,
							"namakecamatan": "RANTAU RASAU"
						},
						{
							"id": 42,
							"namakecamatan": "RANTAU SELAMAT"
						},
						{
							"id": 548,
							"namakecamatan": "RANTAU SELATAN"
						},
						{
							"id": 547,
							"namakecamatan": "RANTAU UTARA"
						},
						{
							"id": 6451,
							"namakecamatan": "RANTE ANGIN"
						},
						{
							"id": 6277,
							"namakecamatan": "RANTE ANGIN"
						},
						{
							"id": 6232,
							"namakecamatan": "RANTEBUA"
						},
						{
							"id": 6168,
							"namakecamatan": "RANTEBUA"
						},
						{
							"id": 6668,
							"namakecamatan": "RANTEBULAHAN TIMUR"
						},
						{
							"id": 6215,
							"namakecamatan": "RANTEPAO"
						},
						{
							"id": 6166,
							"namakecamatan": "RANTEPAO"
						},
						{
							"id": 6162,
							"namakecamatan": "RANTETAYO"
						},
						{
							"id": 625,
							"namakecamatan": "RANTO BAEK"
						},
						{
							"id": 3664,
							"namakecamatan": "RANUYOSO"
						},
						{
							"id": 946,
							"namakecamatan": "RAO"
						},
						{
							"id": 951,
							"namakecamatan": "RAO SELATAN"
						},
						{
							"id": 950,
							"namakecamatan": "RAO UTARA"
						},
						{
							"id": 6248,
							"namakecamatan": "RAPPOCINI"
						},
						{
							"id": 5199,
							"namakecamatan": "RAREN BATUAH"
						},
						{
							"id": 6420,
							"namakecamatan": "RAROWATU"
						},
						{
							"id": 6367,
							"namakecamatan": "RAROWATU"
						},
						{
							"id": 6426,
							"namakecamatan": "RAROWATU UTARA"
						},
						{
							"id": 4508,
							"namakecamatan": "RASANAE BARAT"
						},
						{
							"id": 4509,
							"namakecamatan": "RASANAE TIMUR"
						},
						{
							"id": 4895,
							"namakecamatan": "RASAU JAYA"
						},
						{
							"id": 5053,
							"namakecamatan": "RASAU JAYA"
						},
						{
							"id": 7786,
							"namakecamatan": "RASIEI"
						},
						{
							"id": 5632,
							"namakecamatan": "RATAHAN"
						},
						{
							"id": 5655,
							"namakecamatan": "RATAHAN"
						},
						{
							"id": 5666,
							"namakecamatan": "RATAHAN TIMUR"
						},
						{
							"id": 5635,
							"namakecamatan": "RATATOTOK"
						},
						{
							"id": 5658,
							"namakecamatan": "RATATOTOK"
						},
						{
							"id": 5893,
							"namakecamatan": "RATOLINDO"
						},
						{
							"id": 1750,
							"namakecamatan": "RATU AGUNG"
						},
						{
							"id": 1751,
							"namakecamatan": "RATU SAMBAN"
						},
						{
							"id": 7079,
							"namakecamatan": "RAVENI RARA"
						},
						{
							"id": 1866,
							"namakecamatan": "RAWA JITU SELATAN"
						},
						{
							"id": 1872,
							"namakecamatan": "RAWA JITU TIMUR"
						},
						{
							"id": 1868,
							"namakecamatan": "RAWA JITU UTARA"
						},
						{
							"id": 1973,
							"namakecamatan": "RAWA JITU UTARA"
						},
						{
							"id": 2873,
							"namakecamatan": "RAWALO"
						},
						{
							"id": 2810,
							"namakecamatan": "RAWA LUMBU"
						},
						{
							"id": 2696,
							"namakecamatan": "RAWAMERTA"
						},
						{
							"id": 543,
							"namakecamatan": "RAWANG PANCA ARGA"
						},
						{
							"id": 1876,
							"namakecamatan": "RAWA PITU"
						},
						{
							"id": 1572,
							"namakecamatan": "RAWAS ILIR"
						},
						{
							"id": 1448,
							"namakecamatan": "RAWAS ILIR"
						},
						{
							"id": 1570,
							"namakecamatan": "RAWAS ULU"
						},
						{
							"id": 1449,
							"namakecamatan": "RAWAS ULU"
						},
						{
							"id": 512,
							"namakecamatan": "RAYA"
						},
						{
							"id": 490,
							"namakecamatan": "RAYA KAHEAN"
						},
						{
							"id": 3304,
							"namakecamatan": "REBAN"
						},
						{
							"id": 1945,
							"namakecamatan": "REBANG TANGKAS"
						},
						{
							"id": 2781,
							"namakecamatan": "REGOL"
						},
						{
							"id": 3807,
							"namakecamatan": "REJOSO"
						},
						{
							"id": 3881,
							"namakecamatan": "REJOSO"
						},
						{
							"id": 3557,
							"namakecamatan": "REJOTANGAN"
						},
						{
							"id": 3176,
							"namakecamatan": "REMBANG"
						},
						{
							"id": 2909,
							"namakecamatan": "REMBANG"
						},
						{
							"id": 3799,
							"namakecamatan": "REMBANG"
						},
						{
							"id": 5560,
							"namakecamatan": "REMBOKEN"
						},
						{
							"id": 6171,
							"namakecamatan": "REMBON"
						},
						{
							"id": 1271,
							"namakecamatan": "RENAH MENDALUH"
						},
						{
							"id": 1229,
							"namakecamatan": "RENAH PAMENANG"
						},
						{
							"id": 1226,
							"namakecamatan": "RENAH PEMBARAP"
						},
						{
							"id": 4366,
							"namakecamatan": "RENDANG"
						},
						{
							"id": 2684,
							"namakecamatan": "RENGASDENGKLOK"
						},
						{
							"id": 1043,
							"namakecamatan": "RENGAT"
						},
						{
							"id": 1044,
							"namakecamatan": "RENGAT BARAT"
						},
						{
							"id": 3979,
							"namakecamatan": "RENGEL"
						},
						{
							"id": 4731,
							"namakecamatan": "REOK"
						},
						{
							"id": 4736,
							"namakecamatan": "REOK BARAT"
						},
						{
							"id": 1070,
							"namakecamatan": "RETEH"
						},
						{
							"id": 4454,
							"namakecamatan": "RHEE"
						},
						{
							"id": 2028,
							"namakecamatan": "RIAU SILIP"
						},
						{
							"id": 227,
							"namakecamatan": "RIKIT GAIB"
						},
						{
							"id": 5956,
							"namakecamatan": "RILAUALE"
						},
						{
							"id": 1121,
							"namakecamatan": "RIMBA MELINTANG"
						},
						{
							"id": 1308,
							"namakecamatan": "RIMBO BUJANG"
						},
						{
							"id": 1312,
							"namakecamatan": "RIMBO ILIR"
						},
						{
							"id": 1626,
							"namakecamatan": "RIMBO PENGADANG"
						},
						{
							"id": 1719,
							"namakecamatan": "RIMBO PENGADANG"
						},
						{
							"id": 1300,
							"namakecamatan": "RIMBO TENGAH"
						},
						{
							"id": 1311,
							"namakecamatan": "RIMBO ULU"
						},
						{
							"id": 4746,
							"namakecamatan": "RINDI"
						},
						{
							"id": 6161,
							"namakecamatan": "RINDINGALLO"
						},
						{
							"id": 6218,
							"namakecamatan": "RINDINGALLO"
						},
						{
							"id": 3298,
							"namakecamatan": "RINGINARUM"
						},
						{
							"id": 3608,
							"namakecamatan": "RINGINREJO"
						},
						{
							"id": 4849,
							"namakecamatan": "RINHAT"
						},
						{
							"id": 4609,
							"namakecamatan": "RINHAT"
						},
						{
							"id": 5774,
							"namakecamatan": "RIO PAKAVA"
						},
						{
							"id": 7393,
							"namakecamatan": "RISEI SAYATI"
						},
						{
							"id": 4709,
							"namakecamatan": "RIUNG"
						},
						{
							"id": 4714,
							"namakecamatan": "RIUNG BARAT"
						},
						{
							"id": 4058,
							"namakecamatan": "ROBATAL"
						},
						{
							"id": 3709,
							"namakecamatan": "ROGOJAMPI"
						},
						{
							"id": 1103,
							"namakecamatan": "ROKAN IV KOTO"
						},
						{
							"id": 2744,
							"namakecamatan": "RONGGA"
						},
						{
							"id": 2335,
							"namakecamatan": "RONGGA"
						},
						{
							"id": 686,
							"namakecamatan": "RONGGUR NIHUTA"
						},
						{
							"id": 594,
							"namakecamatan": "RONGGUR NIHUTA"
						},
						{
							"id": 3459,
							"namakecamatan": "RONGKOP"
						},
						{
							"id": 7788,
							"namakecamatan": "ROON"
						},
						{
							"id": 4444,
							"namakecamatan": "ROPANG"
						},
						{
							"id": 7789,
							"namakecamatan": "ROSWAR"
						},
						{
							"id": 4793,
							"namakecamatan": "ROTE BARAT"
						},
						{
							"id": 4787,
							"namakecamatan": "ROTE BARAT DAYA"
						},
						{
							"id": 4788,
							"namakecamatan": "ROTE BARAT LAUT"
						},
						{
							"id": 4794,
							"namakecamatan": "ROTE SELATAN"
						},
						{
							"id": 4790,
							"namakecamatan": "ROTE TENGAH"
						},
						{
							"id": 4792,
							"namakecamatan": "ROTE TIMUR"
						},
						{
							"id": 6312,
							"namakecamatan": "ROUTA"
						},
						{
							"id": 3652,
							"namakecamatan": "ROWOKANGKUNG"
						},
						{
							"id": 2951,
							"namakecamatan": "ROWOKELE"
						},
						{
							"id": 3296,
							"namakecamatan": "ROWOSARI"
						},
						{
							"id": 4090,
							"namakecamatan": "RUBARU"
						},
						{
							"id": 7463,
							"namakecamatan": "RUFAER"
						},
						{
							"id": 7237,
							"namakecamatan": "RUFAER"
						},
						{
							"id": 1176,
							"namakecamatan": "RUMBAI"
						},
						{
							"id": 1182,
							"namakecamatan": "RUMBAI PESISIR"
						},
						{
							"id": 7784,
							"namakecamatan": "RUMBERPON"
						},
						{
							"id": 6368,
							"namakecamatan": "RUMBIA"
						},
						{
							"id": 1786,
							"namakecamatan": "RUMBIA"
						},
						{
							"id": 6421,
							"namakecamatan": "RUMBIA"
						},
						{
							"id": 5974,
							"namakecamatan": "RUMBIA"
						},
						{
							"id": 6435,
							"namakecamatan": "RUMBIA TENGAH"
						},
						{
							"id": 1035,
							"namakecamatan": "RUMBIO JAYA"
						},
						{
							"id": 2212,
							"namakecamatan": "RUMPIN"
						},
						{
							"id": 307,
							"namakecamatan": "RUNDENG"
						},
						{
							"id": 187,
							"namakecamatan": "RUNDENG"
						},
						{
							"id": 5166,
							"namakecamatan": "RUNGAN"
						},
						{
							"id": 5173,
							"namakecamatan": "RUNGAN BARAT"
						},
						{
							"id": 5171,
							"namakecamatan": "RUNGAN HULU"
						},
						{
							"id": 4130,
							"namakecamatan": "RUNGKUT"
						},
						{
							"id": 1532,
							"namakecamatan": "RUNJUNG AGUNG"
						},
						{
							"id": 1066,
							"namakecamatan": "RUPAT"
						},
						{
							"id": 1067,
							"namakecamatan": "RUPAT UTARA"
						},
						{
							"id": 1569,
							"namakecamatan": "RUPIT"
						},
						{
							"id": 1451,
							"namakecamatan": "RUPIT"
						},
						{
							"id": 79,
							"namakecamatan": "RUSIP ANTARA"
						},
						{
							"id": 4723,
							"namakecamatan": "RUTENG"
						},
						{
							"id": 1144,
							"namakecamatan": "SABAK AUH"
						},
						{
							"id": 5205,
							"namakecamatan": "SABANGAU"
						},
						{
							"id": 6081,
							"namakecamatan": "SABANGPARU"
						},
						{
							"id": 6195,
							"namakecamatan": "SABBANG"
						},
						{
							"id": 4839,
							"namakecamatan": "SABU BARAT"
						},
						{
							"id": 4514,
							"namakecamatan": "SABU BARAT"
						},
						{
							"id": 4842,
							"namakecamatan": "SABU LIAE"
						},
						{
							"id": 4527,
							"namakecamatan": "SABU LIAE"
						},
						{
							"id": 4541,
							"namakecamatan": "SABU TENGAH"
						},
						{
							"id": 4840,
							"namakecamatan": "SABU TENGAH"
						},
						{
							"id": 4841,
							"namakecamatan": "SABU TIMUR"
						},
						{
							"id": 4515,
							"namakecamatan": "SABU TIMUR"
						},
						{
							"id": 6165,
							"namakecamatan": "SA'DAN"
						},
						{
							"id": 6220,
							"namakecamatan": "SA'DAN"
						},
						{
							"id": 2444,
							"namakecamatan": "SADANANYA"
						},
						{
							"id": 2956,
							"namakecamatan": "SADANG"
						},
						{
							"id": 4898,
							"namakecamatan": "SADANIANG"
						},
						{
							"id": 1281,
							"namakecamatan": "S A D U"
						},
						{
							"id": 7649,
							"namakecamatan": "SAENGKEDUK"
						},
						{
							"id": 7452,
							"namakecamatan": "SAFAN"
						},
						{
							"id": 2632,
							"namakecamatan": "SAGALAHERANG"
						},
						{
							"id": 2275,
							"namakecamatan": "SAGARANTEN"
						},
						{
							"id": 2747,
							"namakecamatan": "SAGULING"
						},
						{
							"id": 2145,
							"namakecamatan": "SAGULUNG"
						},
						{
							"id": 6850,
							"namakecamatan": "SAHU"
						},
						{
							"id": 6855,
							"namakecamatan": "SAHU TIMUR"
						},
						{
							"id": 7725,
							"namakecamatan": "SAIFI"
						},
						{
							"id": 1173,
							"namakecamatan": "SAIL"
						},
						{
							"id": 349,
							"namakecamatan": "SAIPAR DOLOK HOLE"
						},
						{
							"id": 4876,
							"namakecamatan": "SAJAD"
						},
						{
							"id": 4871,
							"namakecamatan": "SAJINGAN BESAR"
						},
						{
							"id": 4208,
							"namakecamatan": "SAJIRA"
						},
						{
							"id": 6084,
							"namakecamatan": "SAJOANGING"
						},
						{
							"id": 4175,
							"namakecamatan": "SAKETI"
						},
						{
							"id": 1583,
							"namakecamatan": "SAKO"
						},
						{
							"id": 4415,
							"namakecamatan": "SAKRA"
						},
						{
							"id": 4432,
							"namakecamatan": "SAKRA BARAT"
						},
						{
							"id": 4431,
							"namakecamatan": "SAKRA TIMUR"
						},
						{
							"id": 131,
							"namakecamatan": "SAKTI"
						},
						{
							"id": 6713,
							"namakecamatan": "SALAHUTU"
						},
						{
							"id": 664,
							"namakecamatan": "SALAK"
						},
						{
							"id": 2995,
							"namakecamatan": "SALAM"
						},
						{
							"id": 4336,
							"namakecamatan": "SALAMADEG TIMUR"
						},
						{
							"id": 2992,
							"namakecamatan": "SALAMAN"
						},
						{
							"id": 5285,
							"namakecamatan": "SALAM BABARIS"
						},
						{
							"id": 174,
							"namakecamatan": "SALANG"
						},
						{
							"id": 412,
							"namakecamatan": "SALAPIAN"
						},
						{
							"id": 4880,
							"namakecamatan": "SALATIGA"
						},
						{
							"id": 7607,
							"namakecamatan": "SALAWATI"
						},
						{
							"id": 7750,
							"namakecamatan": "SALAWATI BARAT"
						},
						{
							"id": 7616,
							"namakecamatan": "SALAWATI SELATAN"
						},
						{
							"id": 7654,
							"namakecamatan": "SALAWATI TENGAH"
						},
						{
							"id": 7751,
							"namakecamatan": "SALAWATI TENGAH"
						},
						{
							"id": 7733,
							"namakecamatan": "SALAWATI UTARA"
						},
						{
							"id": 2415,
							"namakecamatan": "SALAWU"
						},
						{
							"id": 3170,
							"namakecamatan": "SALE"
						},
						{
							"id": 3367,
							"namakecamatan": "SALEM"
						},
						{
							"id": 4337,
							"namakecamatan": "SALEMADEG BARAT"
						},
						{
							"id": 5613,
							"namakecamatan": "SALIBABU"
						},
						{
							"id": 882,
							"namakecamatan": "SALIMPAUANG"
						},
						{
							"id": 1562,
							"namakecamatan": "SALING"
						},
						{
							"id": 7728,
							"namakecamatan": "SALKMA"
						},
						{
							"id": 1034,
							"namakecamatan": "SALO"
						},
						{
							"id": 6015,
							"namakecamatan": "SALOMEKKO"
						},
						{
							"id": 2419,
							"namakecamatan": "SALOPA"
						},
						{
							"id": 6152,
							"namakecamatan": "SALUPUTI"
						},
						{
							"id": 6,
							"namakecamatan": "SAMADUA"
						},
						{
							"id": 199,
							"namakecamatan": "SAMALANGA"
						},
						{
							"id": 4994,
							"namakecamatan": "SAMALANTAN"
						},
						{
							"id": 2366,
							"namakecamatan": "SAMARANG"
						},
						{
							"id": 5458,
							"namakecamatan": "SAMARINDA ILIR"
						},
						{
							"id": 5463,
							"namakecamatan": "SAMARINDA KOTA"
						},
						{
							"id": 5456,
							"namakecamatan": "SAMARINDA SEBERANG"
						},
						{
							"id": 5457,
							"namakecamatan": "SAMARINDA ULU"
						},
						{
							"id": 5459,
							"namakecamatan": "SAMARINDA UTARA"
						},
						{
							"id": 84,
							"namakecamatan": "SAMATIGA"
						},
						{
							"id": 6282,
							"namakecamatan": "SAMATURU"
						},
						{
							"id": 5390,
							"namakecamatan": "SAMBALIUNG"
						},
						{
							"id": 4863,
							"namakecamatan": "SAMBAS"
						},
						{
							"id": 4423,
							"namakecamatan": "SAMBELIA"
						},
						{
							"id": 3996,
							"namakecamatan": "SAMBENG"
						},
						{
							"id": 3022,
							"namakecamatan": "SAMBI"
						},
						{
							"id": 4158,
							"namakecamatan": "SAMBIKEREP"
						},
						{
							"id": 4730,
							"namakecamatan": "SAMBI RAMPAS"
						},
						{
							"id": 4833,
							"namakecamatan": "SAMBI RAMPAS"
						},
						{
							"id": 3116,
							"namakecamatan": "SAMBIREJO"
						},
						{
							"id": 3513,
							"namakecamatan": "SAMBIT"
						},
						{
							"id": 5382,
							"namakecamatan": "SAMBOJA"
						},
						{
							"id": 3156,
							"namakecamatan": "SAMBONG"
						},
						{
							"id": 3118,
							"namakecamatan": "SAMBUNGMACAN"
						},
						{
							"id": 5254,
							"namakecamatan": "SAMBUNG MAKMUR"
						},
						{
							"id": 5461,
							"namakecamatan": "SAMBUTAN"
						},
						{
							"id": 7294,
							"namakecamatan": "SAMENAGE"
						},
						{
							"id": 3430,
							"namakecamatan": "SAMIGALUH"
						},
						{
							"id": 7132,
							"namakecamatan": "SAMOFA"
						},
						{
							"id": 6645,
							"namakecamatan": "SAMPAGA"
						},
						{
							"id": 5229,
							"namakecamatan": "SAMPANAHAN"
						},
						{
							"id": 4051,
							"namakecamatan": "SAMPANG"
						},
						{
							"id": 2862,
							"namakecamatan": "SAMPANG"
						},
						{
							"id": 6294,
							"namakecamatan": "SAMPARA"
						},
						{
							"id": 239,
							"namakecamatan": "SAMPOINIET"
						},
						{
							"id": 6520,
							"namakecamatan": "SAMPOLAWA"
						},
						{
							"id": 6374,
							"namakecamatan": "SAMPOLAWA"
						},
						{
							"id": 3523,
							"namakecamatan": "SAMPUNG"
						},
						{
							"id": 153,
							"namakecamatan": "SAMUDERA"
						},
						{
							"id": 5127,
							"namakecamatan": "SANAMAN MANTIKEI"
						},
						{
							"id": 6919,
							"namakecamatan": "SANANA"
						},
						{
							"id": 6935,
							"namakecamatan": "SANANA UTARA"
						},
						{
							"id": 3570,
							"namakecamatan": "SANANKULON"
						},
						{
							"id": 4108,
							"namakecamatan": "SANANWETAN"
						},
						{
							"id": 4926,
							"namakecamatan": "SANDAI"
						},
						{
							"id": 5432,
							"namakecamatan": "SANDARAN"
						},
						{
							"id": 3433,
							"namakecamatan": "SANDEN"
						},
						{
							"id": 4507,
							"namakecamatan": "SANDUBAYA"
						},
						{
							"id": 1470,
							"namakecamatan": "SANGA DESA"
						},
						{
							"id": 6164,
							"namakecamatan": "SANGALLA"
						},
						{
							"id": 6184,
							"namakecamatan": "SANGALLA SELATAN"
						},
						{
							"id": 6185,
							"namakecamatan": "SANGALLA UTARA"
						},
						{
							"id": 5384,
							"namakecamatan": "SANGA SANGA"
						},
						{
							"id": 5433,
							"namakecamatan": "SANGATTA SELATAN"
						},
						{
							"id": 5425,
							"namakecamatan": "SANGATTA UTARA"
						},
						{
							"id": 6155,
							"namakecamatan": "SANGGALANGI"
						},
						{
							"id": 6221,
							"namakecamatan": "SANGGALANGI"
						},
						{
							"id": 4479,
							"namakecamatan": "SANGGAR"
						},
						{
							"id": 4998,
							"namakecamatan": "SANGGAU LEDO"
						},
						{
							"id": 6390,
							"namakecamatan": "SANGIA WAMBULU"
						},
						{
							"id": 6518,
							"namakecamatan": "SANGIA WAMBULU"
						},
						{
							"id": 973,
							"namakecamatan": "SANGIR"
						},
						{
							"id": 842,
							"namakecamatan": "SANGIR"
						},
						{
							"id": 979,
							"namakecamatan": "SANGIR BALAI JANGGO"
						},
						{
							"id": 857,
							"namakecamatan": "SANGIR BATANG HARI"
						},
						{
							"id": 977,
							"namakecamatan": "SANGIR BATANG HARI"
						},
						{
							"id": 976,
							"namakecamatan": "SANGIR JUJUAN"
						},
						{
							"id": 856,
							"namakecamatan": "SANGIR JUJUHAN"
						},
						{
							"id": 4029,
							"namakecamatan": "SANGKAPURA"
						},
						{
							"id": 5667,
							"namakecamatan": "SANGKUB"
						},
						{
							"id": 5538,
							"namakecamatan": "SANGKUB"
						},
						{
							"id": 5426,
							"namakecamatan": "SANGKULIRANG"
						},
						{
							"id": 5522,
							"namakecamatan": "SANG TOMBOLANG"
						},
						{
							"id": 4800,
							"namakecamatan": "SANO NGGOANG"
						},
						{
							"id": 5983,
							"namakecamatan": "SANROBONE"
						},
						{
							"id": 4567,
							"namakecamatan": "SANTIAN"
						},
						{
							"id": 6711,
							"namakecamatan": "SAPARUA"
						},
						{
							"id": 6725,
							"namakecamatan": "SAPARUA TIMUR"
						},
						{
							"id": 4476,
							"namakecamatan": "SAPE"
						},
						{
							"id": 4100,
							"namakecamatan": "SAPEKEN"
						},
						{
							"id": 3463,
							"namakecamatan": "SAPTOSARI"
						},
						{
							"id": 2979,
							"namakecamatan": "SAPURAN"
						},
						{
							"id": 3897,
							"namakecamatan": "SARADAN"
						},
						{
							"id": 3171,
							"namakecamatan": "SARANG"
						},
						{
							"id": 5698,
							"namakecamatan": "SARIO"
						},
						{
							"id": 4012,
							"namakecamatan": "SARIREJO"
						},
						{
							"id": 2431,
							"namakecamatan": "SARIWANGI"
						},
						{
							"id": 6636,
							"namakecamatan": "SARJO"
						},
						{
							"id": 7228,
							"namakecamatan": "SARMI"
						},
						{
							"id": 7239,
							"namakecamatan": "SARMI SELATAN"
						},
						{
							"id": 7240,
							"namakecamatan": "SARMI TIMUR"
						},
						{
							"id": 1237,
							"namakecamatan": "SAROLANGUN"
						},
						{
							"id": 4081,
							"namakecamatan": "SARONGGI"
						},
						{
							"id": 329,
							"namakecamatan": "SARUDIK"
						},
						{
							"id": 6629,
							"namakecamatan": "SARUDU"
						},
						{
							"id": 990,
							"namakecamatan": "SASAK RANAH PESISIR"
						},
						{
							"id": 4607,
							"namakecamatan": "SASITAMEAN"
						},
						{
							"id": 4851,
							"namakecamatan": "SASITAMEAN"
						},
						{
							"id": 4725,
							"namakecamatan": "SATAR MESE"
						},
						{
							"id": 4733,
							"namakecamatan": "SATAR MESE BARAT"
						},
						{
							"id": 5334,
							"namakecamatan": "SATUI"
						},
						{
							"id": 7605,
							"namakecamatan": "SAUSAPOR"
						},
						{
							"id": 7803,
							"namakecamatan": "SAUSAPOR"
						},
						{
							"id": 5866,
							"namakecamatan": "SAUSU"
						},
						{
							"id": 6469,
							"namakecamatan": "SAWA"
						},
						{
							"id": 6301,
							"namakecamatan": "SAWA"
						},
						{
							"id": 7439,
							"namakecamatan": "SAWA ERMA"
						},
						{
							"id": 4133,
							"namakecamatan": "SAWAHAN"
						},
						{
							"id": 3866,
							"namakecamatan": "SAWAHAN"
						},
						{
							"id": 3899,
							"namakecamatan": "SAWAHAN"
						},
						{
							"id": 2154,
							"namakecamatan": "SAWAH BESAR"
						},
						{
							"id": 7392,
							"namakecamatan": "SAWAI"
						},
						{
							"id": 7468,
							"namakecamatan": "SAWAI"
						},
						{
							"id": 4380,
							"namakecamatan": "SAWAN"
						},
						{
							"id": 7,
							"namakecamatan": "SAWANG"
						},
						{
							"id": 160,
							"namakecamatan": "SAWANG"
						},
						{
							"id": 2998,
							"namakecamatan": "SAWANGAN"
						},
						{
							"id": 2820,
							"namakecamatan": "SAWANGAN"
						},
						{
							"id": 6501,
							"namakecamatan": "SAWERIGADI"
						},
						{
							"id": 7709,
							"namakecamatan": "SAWIAT"
						},
						{
							"id": 3020,
							"namakecamatan": "SAWIT"
						},
						{
							"id": 430,
							"namakecamatan": "SAWIT SEBERANG"
						},
						{
							"id": 405,
							"namakecamatan": "SAWO"
						},
						{
							"id": 748,
							"namakecamatan": "SAWO"
						},
						{
							"id": 3514,
							"namakecamatan": "SAWOO"
						},
						{
							"id": 5034,
							"namakecamatan": "SAYAN"
						},
						{
							"id": 4962,
							"namakecamatan": "SAYAN"
						},
						{
							"id": 7612,
							"namakecamatan": "SAYOSA"
						},
						{
							"id": 7656,
							"namakecamatan": "SAYOSA TIMUR"
						},
						{
							"id": 3230,
							"namakecamatan": "SAYUNG"
						},
						{
							"id": 365,
							"namakecamatan": "SAYUR MATINGGI"
						},
						{
							"id": 5181,
							"namakecamatan": "SEBANGAU KUALA"
						},
						{
							"id": 5019,
							"namakecamatan": "SEBANGKI"
						},
						{
							"id": 5493,
							"namakecamatan": "SEBATIK"
						},
						{
							"id": 5500,
							"namakecamatan": "SEBATIK BARAT"
						},
						{
							"id": 5504,
							"namakecamatan": "SEBATIK TENGAH"
						},
						{
							"id": 5502,
							"namakecamatan": "SEBATIK TIMUR"
						},
						{
							"id": 5503,
							"namakecamatan": "SEBATIK UTARA"
						},
						{
							"id": 4877,
							"namakecamatan": "SEBAWI"
						},
						{
							"id": 1273,
							"namakecamatan": "SEBERANG KOTA"
						},
						{
							"id": 1733,
							"namakecamatan": "SEBERANG MUSI"
						},
						{
							"id": 1577,
							"namakecamatan": "SEBERANG ULU I"
						},
						{
							"id": 1578,
							"namakecamatan": "SEBERANG ULU II"
						},
						{
							"id": 4978,
							"namakecamatan": "SEBERUANG"
						},
						{
							"id": 5498,
							"namakecamatan": "SEBUKU"
						},
						{
							"id": 5376,
							"namakecamatan": "SEBULU"
						},
						{
							"id": 3011,
							"namakecamatan": "SECANG"
						},
						{
							"id": 419,
							"namakecamatan": "SECANGGANG"
						},
						{
							"id": 3172,
							"namakecamatan": "SEDAN"
						},
						{
							"id": 3825,
							"namakecamatan": "SEDATI"
						},
						{
							"id": 3448,
							"namakecamatan": "SEDAYU"
						},
						{
							"id": 2517,
							"namakecamatan": "SEDONG"
						},
						{
							"id": 5391,
							"namakecamatan": "SEGAH"
						},
						{
							"id": 4896,
							"namakecamatan": "SEGEDONG"
						},
						{
							"id": 6061,
							"namakecamatan": "SEGERI"
						},
						{
							"id": 7608,
							"namakecamatan": "SEGET"
						},
						{
							"id": 1612,
							"namakecamatan": "SEGINIM"
						},
						{
							"id": 7614,
							"namakecamatan": "SEGUN"
						},
						{
							"id": 521,
							"namakecamatan": "SEI BALAI"
						},
						{
							"id": 712,
							"namakecamatan": "SEI BALAI"
						},
						{
							"id": 703,
							"namakecamatan": "SEI BAMBAN"
						},
						{
							"id": 2141,
							"namakecamatan": "SEI BEDUK"
						},
						{
							"id": 414,
							"namakecamatan": "SEI BINGEI"
						},
						{
							"id": 537,
							"namakecamatan": "SEI DADAP"
						},
						{
							"id": 561,
							"namakecamatan": "SEI KANAN"
						},
						{
							"id": 525,
							"namakecamatan": "SEI KEPAYANG"
						},
						{
							"id": 538,
							"namakecamatan": "SEI KEPAYANG BARAT"
						},
						{
							"id": 539,
							"namakecamatan": "SEI KEPAYANG TIMUR"
						},
						{
							"id": 427,
							"namakecamatan": "SEI LEPAN"
						},
						{
							"id": 5505,
							"namakecamatan": "SEI MENGGARIS"
						},
						{
							"id": 843,
							"namakecamatan": "SEI PAGU"
						},
						{
							"id": 468,
							"namakecamatan": "SEI. RAMPAH"
						},
						{
							"id": 692,
							"namakecamatan": "SEI. RAMPAH"
						},
						{
							"id": 520,
							"namakecamatan": "SEI SUKA"
						},
						{
							"id": 707,
							"namakecamatan": "SEI SUKA"
						},
						{
							"id": 801,
							"namakecamatan": "SEI TUALANG RASO"
						},
						{
							"id": 4868,
							"namakecamatan": "SEJANGKUNG"
						},
						{
							"id": 5023,
							"namakecamatan": "SEKADAU HILIR"
						},
						{
							"id": 4913,
							"namakecamatan": "SEKADAU HILIR"
						},
						{
							"id": 5024,
							"namakecamatan": "SEKADAU HULU"
						},
						{
							"id": 4914,
							"namakecamatan": "SEKADAU HULU"
						},
						{
							"id": 1917,
							"namakecamatan": "SEKAMPUNG"
						},
						{
							"id": 1924,
							"namakecamatan": "SEKAMPUNG UDIK"
						},
						{
							"id": 3964,
							"namakecamatan": "SEKAR"
						},
						{
							"id": 3994,
							"namakecamatan": "SEKARAN"
						},
						{
							"id": 4505,
							"namakecamatan": "SEKARBELA"
						},
						{
							"id": 5476,
							"namakecamatan": "SEKATAK"
						},
						{
							"id": 4906,
							"namakecamatan": "SEKAYAM"
						},
						{
							"id": 1466,
							"namakecamatan": "SEKAYU"
						},
						{
							"id": 266,
							"namakecamatan": "SEKERAK"
						},
						{
							"id": 1254,
							"namakecamatan": "SEKERNAN"
						},
						{
							"id": 1836,
							"namakecamatan": "SEKINCAU"
						},
						{
							"id": 6198,
							"namakecamatan": "SEKO"
						},
						{
							"id": 5420,
							"namakecamatan": "SEKOLAQ DARAT"
						},
						{
							"id": 4448,
							"namakecamatan": "SEKONGKANG"
						},
						{
							"id": 4492,
							"namakecamatan": "SEKONGKANG"
						},
						{
							"id": 4393,
							"namakecamatan": "SEKOTONG"
						},
						{
							"id": 2137,
							"namakecamatan": "SEKUPANG"
						},
						{
							"id": 7326,
							"namakecamatan": "SELA"
						},
						{
							"id": 2398,
							"namakecamatan": "SELAAWI"
						},
						{
							"id": 1797,
							"namakecamatan": "SELAGAI LINGGA"
						},
						{
							"id": 1708,
							"namakecamatan": "SELAGAN RAYA"
						},
						{
							"id": 2491,
							"namakecamatan": "SELAJAMBE"
						},
						{
							"id": 4869,
							"namakecamatan": "SELAKAU"
						},
						{
							"id": 4881,
							"namakecamatan": "SELAKAU TIMUR"
						},
						{
							"id": 1455,
							"namakecamatan": "SELANGIT"
						},
						{
							"id": 4506,
							"namakecamatan": "SELAPRANG"
						},
						{
							"id": 6746,
							"namakecamatan": "SELARU"
						},
						{
							"id": 4372,
							"namakecamatan": "SELAT"
						},
						{
							"id": 5090,
							"namakecamatan": "SELAT"
						},
						{
							"id": 2032,
							"namakecamatan": "SELAT NASIK"
						},
						{
							"id": 7741,
							"namakecamatan": "SELAT SAGAWIN"
						},
						{
							"id": 2125,
							"namakecamatan": "SELAYAR"
						},
						{
							"id": 1745,
							"namakecamatan": "SELEBAR"
						},
						{
							"id": 4335,
							"namakecamatan": "SELEMADEG"
						},
						{
							"id": 7827,
							"namakecamatan": "SELEMKAI"
						},
						{
							"id": 416,
							"namakecamatan": "SELESAI"
						},
						{
							"id": 4976,
							"namakecamatan": "SELIMBAU"
						},
						{
							"id": 3013,
							"namakecamatan": "SELO"
						},
						{
							"id": 3080,
							"namakecamatan": "SELOGIRI"
						},
						{
							"id": 2982,
							"namakecamatan": "SELOMERTO"
						},
						{
							"id": 4420,
							"namakecamatan": "SELONG"
						},
						{
							"id": 3275,
							"namakecamatan": "SELOPAMPANG"
						},
						{
							"id": 3585,
							"namakecamatan": "SELOPURO"
						},
						{
							"id": 3584,
							"namakecamatan": "SELOREJO"
						},
						{
							"id": 4997,
							"namakecamatan": "SELUAS"
						},
						{
							"id": 1687,
							"namakecamatan": "SELUMA"
						},
						{
							"id": 1693,
							"namakecamatan": "SELUMA BARAT"
						},
						{
							"id": 1696,
							"namakecamatan": "SELUMA SELATAN"
						},
						{
							"id": 1694,
							"namakecamatan": "SELUMA TIMUR"
						},
						{
							"id": 1695,
							"namakecamatan": "SELUMA UTARA"
						},
						{
							"id": 1632,
							"namakecamatan": "SELUPU REJANG"
						},
						{
							"id": 28,
							"namakecamatan": "SEMADAM"
						},
						{
							"id": 1896,
							"namakecamatan": "SEMAKA"
						},
						{
							"id": 4143,
							"namakecamatan": "SEMAMPIR"
						},
						{
							"id": 3980,
							"namakecamatan": "SEMANDING"
						},
						{
							"id": 6979,
							"namakecamatan": "SEMANGGA"
						},
						{
							"id": 3456,
							"namakecamatan": "SEMANU"
						},
						{
							"id": 3408,
							"namakecamatan": "SEMARANG BARAT"
						},
						{
							"id": 3402,
							"namakecamatan": "SEMARANG SELATAN"
						},
						{
							"id": 3396,
							"namakecamatan": "SEMARANG TENGAH"
						},
						{
							"id": 3398,
							"namakecamatan": "SEMARANG TIMUR"
						},
						{
							"id": 3397,
							"namakecamatan": "SEMARANG UTARA"
						},
						{
							"id": 1591,
							"namakecamatan": "SEMATANG BORANG"
						},
						{
							"id": 5159,
							"namakecamatan": "SEMATU JAYA"
						},
						{
							"id": 4516,
							"namakecamatan": "SEMAU"
						},
						{
							"id": 4535,
							"namakecamatan": "SEMAU SELATAN"
						},
						{
							"id": 5495,
							"namakecamatan": "SEMBAKUNG"
						},
						{
							"id": 5508,
							"namakecamatan": "SEMBAKUNG ATULAI"
						},
						{
							"id": 4428,
							"namakecamatan": "SEMBALUN"
						},
						{
							"id": 1496,
							"namakecamatan": "SEMBAWA"
						},
						{
							"id": 966,
							"namakecamatan": "SEMBILAN KOTO"
						},
						{
							"id": 3672,
							"namakecamatan": "SEMBORO"
						},
						{
							"id": 5145,
							"namakecamatan": "SEMBULUH RAYA"
						},
						{
							"id": 3586,
							"namakecamatan": "SEMEN"
						},
						{
							"id": 1512,
							"namakecamatan": "SEMENDAWAI BARAT"
						},
						{
							"id": 1360,
							"namakecamatan": "SEMENDAWAI S. III"
						},
						{
							"id": 1506,
							"namakecamatan": "SEMENDAWAI SUKU III"
						},
						{
							"id": 1513,
							"namakecamatan": "SEMENDAWAI TIMUR"
						},
						{
							"id": 1398,
							"namakecamatan": "SEMENDE DARAT LAUT"
						},
						{
							"id": 1399,
							"namakecamatan": "SEMENDE DARAT TENGAH"
						},
						{
							"id": 1400,
							"namakecamatan": "SEMENDE DARAT ULU"
						},
						{
							"id": 1356,
							"namakecamatan": "SEMIDANG AJI"
						},
						{
							"id": 1689,
							"namakecamatan": "SEMIDANG ALAS"
						},
						{
							"id": 1690,
							"namakecamatan": "SEMIDANG ALAS MARAS"
						},
						{
							"id": 1678,
							"namakecamatan": "SEMIDANG GUMAY"
						},
						{
							"id": 3460,
							"namakecamatan": "SEMIN"
						},
						{
							"id": 4977,
							"namakecamatan": "SEMITAU"
						},
						{
							"id": 4875,
							"namakecamatan": "SEMPARUK"
						},
						{
							"id": 3739,
							"namakecamatan": "SEMPOL"
						},
						{
							"id": 2952,
							"namakecamatan": "SEMPOR"
						},
						{
							"id": 3716,
							"namakecamatan": "SEMPU"
						},
						{
							"id": 1175,
							"namakecamatan": "SENAPELAN"
						},
						{
							"id": 2120,
							"namakecamatan": "SENAYANG"
						},
						{
							"id": 2071,
							"namakecamatan": "SENAYANG"
						},
						{
							"id": 6260,
							"namakecamatan": "SENDANA"
						},
						{
							"id": 6689,
							"namakecamatan": "SENDANA"
						},
						{
							"id": 3551,
							"namakecamatan": "SENDANG"
						},
						{
							"id": 1799,
							"namakecamatan": "SENDANG AGUNG"
						},
						{
							"id": 3656,
							"namakecamatan": "SENDURO"
						},
						{
							"id": 2156,
							"namakecamatan": "SENEN"
						},
						{
							"id": 5016,
							"namakecamatan": "SENGAH TEMILA"
						},
						{
							"id": 7245,
							"namakecamatan": "SENGGI"
						},
						{
							"id": 7678,
							"namakecamatan": "SENOPI"
						},
						{
							"id": 7809,
							"namakecamatan": "SENOPI"
						},
						{
							"id": 3970,
							"namakecamatan": "SENORI"
						},
						{
							"id": 1160,
							"namakecamatan": "SENTAJO RAYA"
						},
						{
							"id": 7063,
							"namakecamatan": "SENTANI"
						},
						{
							"id": 7066,
							"namakecamatan": "SENTANI BARAT"
						},
						{
							"id": 7064,
							"namakecamatan": "SENTANI TIMUR"
						},
						{
							"id": 3425,
							"namakecamatan": "SENTOLO"
						},
						{
							"id": 1276,
							"namakecamatan": "SENYERANG"
						},
						{
							"id": 5443,
							"namakecamatan": "SEPAKU"
						},
						{
							"id": 5147,
							"namakecamatan": "SEPAN BIHA"
						},
						{
							"id": 5162,
							"namakecamatan": "SEPANG SIMIN"
						},
						{
							"id": 4240,
							"namakecamatan": "SEPATAN"
						},
						{
							"id": 4254,
							"namakecamatan": "SEPATAN TIMUR"
						},
						{
							"id": 4949,
							"namakecamatan": "SEPAUK"
						},
						{
							"id": 4944,
							"namakecamatan": "SEPONTI"
						},
						{
							"id": 5045,
							"namakecamatan": "SEPONTI"
						},
						{
							"id": 4038,
							"namakecamatan": "SEPULU"
						},
						{
							"id": 1793,
							"namakecamatan": "SEPUTIH AGUNG"
						},
						{
							"id": 1787,
							"namakecamatan": "SEPUTIH BANYAK"
						},
						{
							"id": 1788,
							"namakecamatan": "SEPUTIH MATARAM"
						},
						{
							"id": 1785,
							"namakecamatan": "SEPUTIH RAMAN"
						},
						{
							"id": 1789,
							"namakecamatan": "SEPUTIH SURABAYA"
						},
						{
							"id": 1314,
							"namakecamatan": "SERAI SERUMPUN"
						},
						{
							"id": 7268,
							"namakecamatan": "SERAMBAKON"
						},
						{
							"id": 6794,
							"namakecamatan": "SERAM BARAT"
						},
						{
							"id": 6703,
							"namakecamatan": "SERAM BARAT"
						},
						{
							"id": 6707,
							"namakecamatan": "SERAM TIMUR"
						},
						{
							"id": 6779,
							"namakecamatan": "SERAM TIMUR"
						},
						{
							"id": 6705,
							"namakecamatan": "SERAM UTARA"
						},
						{
							"id": 6719,
							"namakecamatan": "SERAM UTARA BARAT"
						},
						{
							"id": 6724,
							"namakecamatan": "SERAM UTARA TIMURKOBI"
						},
						{
							"id": 6723,
							"namakecamatan": "SERAM UTARA TIMUR SETI"
						},
						{
							"id": 5084,
							"namakecamatan": "SERANAU"
						},
						{
							"id": 4261,
							"namakecamatan": "SERANG"
						},
						{
							"id": 4317,
							"namakecamatan": "SERANG"
						},
						{
							"id": 2729,
							"namakecamatan": "SERANG BARU"
						},
						{
							"id": 2654,
							"namakecamatan": "SERANGPANJANG"
						},
						{
							"id": 2101,
							"namakecamatan": "SERASAN"
						},
						{
							"id": 2114,
							"namakecamatan": "SERASAN TIMUR"
						},
						{
							"id": 4960,
							"namakecamatan": "SERAWAI"
						},
						{
							"id": 39,
							"namakecamatan": "SERBAJADI"
						},
						{
							"id": 700,
							"namakecamatan": "SERBA JADI"
						},
						{
							"id": 7331,
							"namakecamatan": "SEREDELA"
						},
						{
							"id": 7714,
							"namakecamatan": "SEREMUK"
						},
						{
							"id": 3388,
							"namakecamatan": "SERENGAN"
						},
						{
							"id": 5190,
							"namakecamatan": "SERIBU RIAM"
						},
						{
							"id": 2083,
							"namakecamatan": "SERI KUALA LOBAM"
						},
						{
							"id": 4375,
							"namakecamatan": "SERIRIT"
						},
						{
							"id": 4323,
							"namakecamatan": "SERPONG"
						},
						{
							"id": 4245,
							"namakecamatan": "SERPONG"
						},
						{
							"id": 4324,
							"namakecamatan": "SERPONG UTARA"
						},
						{
							"id": 4259,
							"namakecamatan": "SERPONG UTARA"
						},
						{
							"id": 258,
							"namakecamatan": "SERUWAY"
						},
						{
							"id": 5135,
							"namakecamatan": "SERUYAN HILIR"
						},
						{
							"id": 5140,
							"namakecamatan": "SERUYAN HILIR TIMUR"
						},
						{
							"id": 5139,
							"namakecamatan": "SERUYAN HULU"
						},
						{
							"id": 5148,
							"namakecamatan": "SERUYAN HULU UTARA"
						},
						{
							"id": 5141,
							"namakecamatan": "SERUYAN RAYA"
						},
						{
							"id": 5136,
							"namakecamatan": "SERUYAN TENGAH"
						},
						{
							"id": 5509,
							"namakecamatan": "SESAYAP"
						},
						{
							"id": 5510,
							"namakecamatan": "SESAYAP HILIR"
						},
						{
							"id": 6157,
							"namakecamatan": "SESEAN"
						},
						{
							"id": 6216,
							"namakecamatan": "SESEAN"
						},
						{
							"id": 6187,
							"namakecamatan": "SESEAN SULOARA"
						},
						{
							"id": 6233,
							"namakecamatan": "SESEAN SULOARA"
						},
						{
							"id": 6661,
							"namakecamatan": "SESENAPADANG"
						},
						{
							"id": 7419,
							"namakecamatan": "SESNUK"
						},
						{
							"id": 4491,
							"namakecamatan": "SETELUK"
						},
						{
							"id": 4437,
							"namakecamatan": "SETELUK"
						},
						{
							"id": 222,
							"namakecamatan": "SETIA"
						},
						{
							"id": 238,
							"namakecamatan": "SETIA BHAKTI"
						},
						{
							"id": 2176,
							"namakecamatan": "SETIABUDI"
						},
						{
							"id": 541,
							"namakecamatan": "SETIA JANJI"
						},
						{
							"id": 2726,
							"namakecamatan": "SETU"
						},
						{
							"id": 4260,
							"namakecamatan": "SETU"
						},
						{
							"id": 4329,
							"namakecamatan": "SETU"
						},
						{
							"id": 95,
							"namakecamatan": "SEULIMEUM"
						},
						{
							"id": 246,
							"namakecamatan": "SEUNAGAN"
						},
						{
							"id": 247,
							"namakecamatan": "SEUNAGAN TIMUR"
						},
						{
							"id": 154,
							"namakecamatan": "SEUNUDDON"
						},
						{
							"id": 6330,
							"namakecamatan": "SEWERGADI"
						},
						{
							"id": 3446,
							"namakecamatan": "SEWON"
						},
						{
							"id": 3471,
							"namakecamatan": "SEYEGAN"
						},
						{
							"id": 613,
							"namakecamatan": "SIABU"
						},
						{
							"id": 1133,
							"namakecamatan": "SIAK"
						},
						{
							"id": 1027,
							"namakecamatan": "SIAK HULU"
						},
						{
							"id": 1068,
							"namakecamatan": "SIAK KECIL"
						},
						{
							"id": 595,
							"namakecamatan": "SIANJAR MULA MULA"
						},
						{
							"id": 685,
							"namakecamatan": "SIANJAR MULA MULA"
						},
						{
							"id": 4889,
							"namakecamatan": "SIANTAN"
						},
						{
							"id": 2098,
							"namakecamatan": "SIANTAN"
						},
						{
							"id": 2128,
							"namakecamatan": "SIANTAN"
						},
						{
							"id": 2131,
							"namakecamatan": "SIANTAN SELATAN"
						},
						{
							"id": 2108,
							"namakecamatan": "SIANTAN SELATAN"
						},
						{
							"id": 2112,
							"namakecamatan": "SIANTAN TENGAH"
						},
						{
							"id": 2134,
							"namakecamatan": "SIANTAN TENGAH"
						},
						{
							"id": 2130,
							"namakecamatan": "SIANTAN TIMUR"
						},
						{
							"id": 2109,
							"namakecamatan": "SIANTAN TIMUR"
						},
						{
							"id": 484,
							"namakecamatan": "SIANTAR"
						},
						{
							"id": 788,
							"namakecamatan": "SIANTAR BARAT"
						},
						{
							"id": 791,
							"namakecamatan": "SIANTAR MARIHAT"
						},
						{
							"id": 794,
							"namakecamatan": "SIANTAR MARIMBUN"
						},
						{
							"id": 792,
							"namakecamatan": "SIANTAR MARTOBA"
						},
						{
							"id": 603,
							"namakecamatan": "SIANTAR NARUMONDA"
						},
						{
							"id": 790,
							"namakecamatan": "SIANTAR SELATAN"
						},
						{
							"id": 793,
							"namakecamatan": "SIANTAR SITALASARI"
						},
						{
							"id": 787,
							"namakecamatan": "SIANTAR TIMUR"
						},
						{
							"id": 789,
							"namakecamatan": "SIANTAR UTARA"
						},
						{
							"id": 331,
							"namakecamatan": "SIATAS BARITA"
						},
						{
							"id": 5583,
							"namakecamatan": "SIAU BARAT"
						},
						{
							"id": 5674,
							"namakecamatan": "SIAU BARAT"
						},
						{
							"id": 5677,
							"namakecamatan": "SIAU BARAT SELATAN"
						},
						{
							"id": 5584,
							"namakecamatan": "SIAU BARAT SELATAN"
						},
						{
							"id": 5595,
							"namakecamatan": "SIAU BARAT UTARA"
						},
						{
							"id": 5680,
							"namakecamatan": "SIAU BARAT UTARA"
						},
						{
							"id": 5599,
							"namakecamatan": "SIAU TENGAH"
						},
						{
							"id": 5681,
							"namakecamatan": "SIAU TENGAH"
						},
						{
							"id": 5581,
							"namakecamatan": "SIAU TIMUR"
						},
						{
							"id": 5673,
							"namakecamatan": "SIAU TIMUR"
						},
						{
							"id": 5582,
							"namakecamatan": "SIAU TIMUR SELATAN"
						},
						{
							"id": 5676,
							"namakecamatan": "SIAU TIMUR SELATAN"
						},
						{
							"id": 317,
							"namakecamatan": "SIBABANGUN"
						},
						{
							"id": 1048,
							"namakecamatan": "SIBERIDA"
						},
						{
							"id": 956,
							"namakecamatan": "SIBERUT BARAT"
						},
						{
							"id": 957,
							"namakecamatan": "SIBERUT BARAT DAYA"
						},
						{
							"id": 954,
							"namakecamatan": "SIBERUT SELATAN"
						},
						{
							"id": 958,
							"namakecamatan": "SIBERUT TENGAH"
						},
						{
							"id": 955,
							"namakecamatan": "SIBERUT UTARA"
						},
						{
							"id": 457,
							"namakecamatan": "SIBIRU-BIRU"
						},
						{
							"id": 453,
							"namakecamatan": "SIBOLANGIT"
						},
						{
							"id": 796,
							"namakecamatan": "SIBOLGA KOTA"
						},
						{
							"id": 798,
							"namakecamatan": "SIBOLGA SAMBAS"
						},
						{
							"id": 797,
							"namakecamatan": "SIBOLGA SELATAN"
						},
						{
							"id": 795,
							"namakecamatan": "SIBOLGA UTARA"
						},
						{
							"id": 338,
							"namakecamatan": "SIBORONG-BORONG"
						},
						{
							"id": 6019,
							"namakecamatan": "SIBULUE"
						},
						{
							"id": 492,
							"namakecamatan": "SIDAMANIK"
						},
						{
							"id": 2757,
							"namakecamatan": "SIDAMULIH"
						},
						{
							"id": 2463,
							"namakecamatan": "SIDAMULIH"
						},
						{
							"id": 2856,
							"namakecamatan": "SIDAREJA"
						},
						{
							"id": 4021,
							"namakecamatan": "SIDAYU"
						},
						{
							"id": 4367,
							"namakecamatan": "SIDEMEN"
						},
						{
							"id": 7677,
							"namakecamatan": "SIDEY"
						},
						{
							"id": 569,
							"namakecamatan": "SIDIKALANG"
						},
						{
							"id": 5004,
							"namakecamatan": "SIDING"
						},
						{
							"id": 5883,
							"namakecamatan": "SIDOAN"
						},
						{
							"id": 3816,
							"namakecamatan": "SIDOARJO"
						},
						{
							"id": 3122,
							"namakecamatan": "SIDOHARJO"
						},
						{
							"id": 3083,
							"namakecamatan": "SIDOHARJO"
						},
						{
							"id": 3395,
							"namakecamatan": "SIDOMUKTI"
						},
						{
							"id": 1760,
							"namakecamatan": "SIDOMULYO"
						},
						{
							"id": 3392,
							"namakecamatan": "SIDOREJO"
						},
						{
							"id": 3918,
							"namakecamatan": "SIDOREJO"
						},
						{
							"id": 655,
							"namakecamatan": "SIDUA'ORI"
						},
						{
							"id": 572,
							"namakecamatan": "SIEMPAT NEMPU"
						},
						{
							"id": 576,
							"namakecamatan": "SIEMPAT NEMPU HILIR"
						},
						{
							"id": 575,
							"namakecamatan": "SIEMPAT NEMPU HULU"
						},
						{
							"id": 669,
							"namakecamatan": "SIEMPAT RUBE"
						},
						{
							"id": 7042,
							"namakecamatan": "SIEPKOSI"
						},
						{
							"id": 2921,
							"namakecamatan": "SIGALUH"
						},
						{
							"id": 5775,
							"namakecamatan": "SIGI BIROMARU"
						},
						{
							"id": 5896,
							"namakecamatan": "SIGI BIROMARU"
						},
						{
							"id": 602,
							"namakecamatan": "SIGUMPAR"
						},
						{
							"id": 733,
							"namakecamatan": "SIHAPAS BARUMUN"
						},
						{
							"id": 676,
							"namakecamatan": "SIJAMAPOLANG"
						},
						{
							"id": 2033,
							"namakecamatan": "SIJUK"
						},
						{
							"id": 864,
							"namakecamatan": "SIJUNJUNG"
						},
						{
							"id": 960,
							"namakecamatan": "SIKAKAP"
						},
						{
							"id": 1561,
							"namakecamatan": "SIKAP DALAM"
						},
						{
							"id": 4417,
							"namakecamatan": "SIKUR"
						},
						{
							"id": 586,
							"namakecamatan": "SILAEN"
						},
						{
							"id": 582,
							"namakecamatan": "SILAHISABUNGAN"
						},
						{
							"id": 557,
							"namakecamatan": "SILANGKITANG"
						},
						{
							"id": 738,
							"namakecamatan": "SILANGKITANG"
						},
						{
							"id": 4982,
							"namakecamatan": "SILAT HILIR"
						},
						{
							"id": 4983,
							"namakecamatan": "SILAT HULU"
						},
						{
							"id": 542,
							"namakecamatan": "SILAU LAUT"
						},
						{
							"id": 841,
							"namakecamatan": "SILAUT"
						},
						{
							"id": 5662,
							"namakecamatan": "SILIAN RAYA"
						},
						{
							"id": 60,
							"namakecamatan": "SILIH NARA"
						},
						{
							"id": 508,
							"namakecamatan": "SILIMAKUTA"
						},
						{
							"id": 573,
							"namakecamatan": "SILIMA PUNGGA PUNGGA"
						},
						{
							"id": 7293,
							"namakecamatan": "SILIMO"
						},
						{
							"id": 699,
							"namakecamatan": "SILINDA"
						},
						{
							"id": 3718,
							"namakecamatan": "SILIRAGUNG"
						},
						{
							"id": 3695,
							"namakecamatan": "SILO"
						},
						{
							"id": 7052,
							"namakecamatan": "SILO KARNO DOGA"
						},
						{
							"id": 510,
							"namakecamatan": "SILOU KAHEAN"
						},
						{
							"id": 1006,
							"namakecamatan": "SILUNGKANG"
						},
						{
							"id": 5417,
							"namakecamatan": "SILUQ NGURAI"
						},
						{
							"id": 3519,
							"namakecamatan": "SIMAN"
						},
						{
							"id": 720,
							"namakecamatan": "SIMANGAMBAT"
						},
						{
							"id": 369,
							"namakecamatan": "SIMANGAMBAT"
						},
						{
							"id": 336,
							"namakecamatan": "SIMANGUMBAN"
						},
						{
							"id": 680,
							"namakecamatan": "SIMANINDO"
						},
						{
							"id": 601,
							"namakecamatan": "SIMANINDO"
						},
						{
							"id": 6047,
							"namakecamatan": "SIMBANG"
						},
						{
							"id": 6649,
							"namakecamatan": "SIMBORO DANKEPULAUAN"
						},
						{
							"id": 6160,
							"namakecamatan": "SIMBUANG"
						},
						{
							"id": 178,
							"namakecamatan": "SIMEULUE BARAT"
						},
						{
							"id": 182,
							"namakecamatan": "SIMEULUE CUT"
						},
						{
							"id": 173,
							"namakecamatan": "SIMEULUE TENGAH"
						},
						{
							"id": 176,
							"namakecamatan": "SIMEULUE TIMUR"
						},
						{
							"id": 3025,
							"namakecamatan": "SIMO"
						},
						{
							"id": 4138,
							"namakecamatan": "SIMOKERTO"
						},
						{
							"id": 1523,
							"namakecamatan": "SIMPANG"
						},
						{
							"id": 1340,
							"namakecamatan": "SIMPANG"
						},
						{
							"id": 948,
							"namakecamatan": "SIMPANG ALAHAN MATI"
						},
						{
							"id": 4941,
							"namakecamatan": "SIMPANG DUA"
						},
						{
							"id": 5246,
							"namakecamatan": "SIMPANG EMPAT"
						},
						{
							"id": 526,
							"namakecamatan": "SIMPANG EMPAT"
						},
						{
							"id": 5336,
							"namakecamatan": "SIMPANG EMPAT"
						},
						{
							"id": 445,
							"namakecamatan": "SIMPANG EMPAT"
						},
						{
							"id": 4931,
							"namakecamatan": "SIMPANG HILIR"
						},
						{
							"id": 5042,
							"namakecamatan": "SIMPANG HILIR"
						},
						{
							"id": 4929,
							"namakecamatan": "SIMPANG HULU"
						},
						{
							"id": 54,
							"namakecamatan": "SIMPANG JERNIH"
						},
						{
							"id": 1128,
							"namakecamatan": "SIMPANG KANAN"
						},
						{
							"id": 184,
							"namakecamatan": "SIMPANG KANAN"
						},
						{
							"id": 2046,
							"namakecamatan": "SIMPANG KATIS"
						},
						{
							"id": 185,
							"namakecamatan": "SIMPANG KIRI"
						},
						{
							"id": 305,
							"namakecamatan": "SIMPANG KIRI"
						},
						{
							"id": 167,
							"namakecamatan": "SIMPANG KRAMAT"
						},
						{
							"id": 209,
							"namakecamatan": "SIMPANG MAMPLAM"
						},
						{
							"id": 1859,
							"namakecamatan": "SIMPANG PEMATANG"
						},
						{
							"id": 1975,
							"namakecamatan": "SIMPANG PEMATANG"
						},
						{
							"id": 2061,
							"namakecamatan": "SIMPANG PESAK"
						},
						{
							"id": 5737,
							"namakecamatan": "SIMPANG RAYA"
						},
						{
							"id": 2060,
							"namakecamatan": "SIMPANG RENGGIANG"
						},
						{
							"id": 2038,
							"namakecamatan": "SIMPANG RIMBA"
						},
						{
							"id": 2050,
							"namakecamatan": "SIMPANG TERITIP"
						},
						{
							"id": 132,
							"namakecamatan": "SIMPANG TIGA"
						},
						{
							"id": 109,
							"namakecamatan": "SIMPANG TIGA"
						},
						{
							"id": 43,
							"namakecamatan": "SIMPANG ULIM"
						},
						{
							"id": 2236,
							"namakecamatan": "SIMPENAN"
						},
						{
							"id": 5292,
							"namakecamatan": "SIMPUR"
						},
						{
							"id": 658,
							"namakecamatan": "SIMUK"
						},
						{
							"id": 1124,
							"namakecamatan": "SINABOI"
						},
						{
							"id": 7555,
							"namakecamatan": "SINAK"
						},
						{
							"id": 7145,
							"namakecamatan": "SINAK"
						},
						{
							"id": 7564,
							"namakecamatan": "SINAK BARAT"
						},
						{
							"id": 1364,
							"namakecamatan": "SINAR PENINJAUAN"
						},
						{
							"id": 2616,
							"namakecamatan": "SINDANG"
						},
						{
							"id": 2573,
							"namakecamatan": "SINDANG"
						},
						{
							"id": 2507,
							"namakecamatan": "SINDANG AGUNG"
						},
						{
							"id": 2302,
							"namakecamatan": "SINDANGBARANG"
						},
						{
							"id": 1644,
							"namakecamatan": "SINDANG BELITI ILIR"
						},
						{
							"id": 1642,
							"namakecamatan": "SINDANG BELITI ULU"
						},
						{
							"id": 1534,
							"namakecamatan": "SINDANG DANAU"
						},
						{
							"id": 1643,
							"namakecamatan": "SINDANG DATARAN"
						},
						{
							"id": 4253,
							"namakecamatan": "SINDANG JAYA"
						},
						{
							"id": 2471,
							"namakecamatan": "SINDANGKASIH"
						},
						{
							"id": 1629,
							"namakecamatan": "SINDANG KELINGI"
						},
						{
							"id": 2332,
							"namakecamatan": "SINDANGKERTA"
						},
						{
							"id": 2745,
							"namakecamatan": "SINDANGKERTA"
						},
						{
							"id": 4192,
							"namakecamatan": "SINDANGRESMI"
						},
						{
							"id": 2569,
							"namakecamatan": "SINDANGWANGI"
						},
						{
							"id": 5780,
							"namakecamatan": "SINDUE"
						},
						{
							"id": 5795,
							"namakecamatan": "SINDUE TOBATA"
						},
						{
							"id": 5794,
							"namakecamatan": "SINDUE TOMBUSABORA"
						},
						{
							"id": 3919,
							"namakecamatan": "SINE"
						},
						{
							"id": 2383,
							"namakecamatan": "SINGAJAYA"
						},
						{
							"id": 2425,
							"namakecamatan": "SINGAPARNA"
						},
						{
							"id": 1753,
							"namakecamatan": "SINGARAN PATI"
						},
						{
							"id": 3972,
							"namakecamatan": "SINGGAHAN"
						},
						{
							"id": 1149,
							"namakecamatan": "SINGINGI"
						},
						{
							"id": 1154,
							"namakecamatan": "SINGINGI HILIR"
						},
						{
							"id": 5063,
							"namakecamatan": "SINGKAWANG BARAT"
						},
						{
							"id": 5066,
							"namakecamatan": "SINGKAWANG SELATAN"
						},
						{
							"id": 5062,
							"namakecamatan": "SINGKAWANG TENGAH"
						},
						{
							"id": 5064,
							"namakecamatan": "SINGKAWANG TIMUR"
						},
						{
							"id": 5065,
							"namakecamatan": "SINGKAWANG UTARA"
						},
						{
							"id": 2069,
							"namakecamatan": "SINGKEP"
						},
						{
							"id": 2118,
							"namakecamatan": "SINGKEP"
						},
						{
							"id": 2073,
							"namakecamatan": "SINGKEP BARAT"
						},
						{
							"id": 2121,
							"namakecamatan": "SINGKEP BARAT"
						},
						{
							"id": 2123,
							"namakecamatan": "SINGKEP PESISIR"
						},
						{
							"id": 2126,
							"namakecamatan": "SINGKEP SELATAN"
						},
						{
							"id": 5695,
							"namakecamatan": "SINGKIL"
						},
						{
							"id": 186,
							"namakecamatan": "SINGKIL"
						},
						{
							"id": 192,
							"namakecamatan": "SINGKIL UTARA"
						},
						{
							"id": 195,
							"namakecamatan": "SINGKOHOR"
						},
						{
							"id": 4943,
							"namakecamatan": "SINGKUP"
						},
						{
							"id": 1243,
							"namakecamatan": "SINGKUT"
						},
						{
							"id": 3708,
							"namakecamatan": "SINGOJURUH"
						},
						{
							"id": 3285,
							"namakecamatan": "SINGOROJO"
						},
						{
							"id": 3635,
							"namakecamatan": "SINGOSARI"
						},
						{
							"id": 5877,
							"namakecamatan": "SINIU"
						},
						{
							"id": 6003,
							"namakecamatan": "SINJAI BARAT"
						},
						{
							"id": 6009,
							"namakecamatan": "SINJAI BORONG"
						},
						{
							"id": 6004,
							"namakecamatan": "SINJAI SELATAN"
						},
						{
							"id": 6006,
							"namakecamatan": "SINJAI TENGAH"
						},
						{
							"id": 6005,
							"namakecamatan": "SINJAI TIMUR"
						},
						{
							"id": 6007,
							"namakecamatan": "SINJAI UTARA"
						},
						{
							"id": 5964,
							"namakecamatan": "SINOA"
						},
						{
							"id": 5629,
							"namakecamatan": "SINONSAYANG"
						},
						{
							"id": 4947,
							"namakecamatan": "SINTANG"
						},
						{
							"id": 897,
							"namakecamatan": "SINTUAK TOBOH GADANG"
						},
						{
							"id": 629,
							"namakecamatan": "SINUNUKAN"
						},
						{
							"id": 6384,
							"namakecamatan": "SIOMPU"
						},
						{
							"id": 6524,
							"namakecamatan": "SIOMPU"
						},
						{
							"id": 6395,
							"namakecamatan": "SIOMPU BARAT"
						},
						{
							"id": 6523,
							"namakecamatan": "SIOMPU BARAT"
						},
						{
							"id": 6391,
							"namakecamatan": "SIOTAPINA"
						},
						{
							"id": 341,
							"namakecamatan": "SIPAHUTAR"
						},
						{
							"id": 6623,
							"namakecamatan": "SIPATANA"
						},
						{
							"id": 348,
							"namakecamatan": "SIPIROK"
						},
						{
							"id": 696,
							"namakecamatan": "SIPISPIS"
						},
						{
							"id": 462,
							"namakecamatan": "SIPIS-PIS"
						},
						{
							"id": 333,
							"namakecamatan": "SIPOHOLON"
						},
						{
							"id": 953,
							"namakecamatan": "SIPORA SELATAN"
						},
						{
							"id": 959,
							"namakecamatan": "SIPORA UTARA"
						},
						{
							"id": 1374,
							"namakecamatan": "SIRAH PULAU PADANG"
						},
						{
							"id": 3371,
							"namakecamatan": "SIRAMPOG"
						},
						{
							"id": 320,
							"namakecamatan": "SIRANDORUNG"
						},
						{
							"id": 431,
							"namakecamatan": "SIRAPIT"
						},
						{
							"id": 5781,
							"namakecamatan": "SIRENJA"
						},
						{
							"id": 7453,
							"namakecamatan": "SIRETS"
						},
						{
							"id": 6838,
							"namakecamatan": "SIRIMAU"
						},
						{
							"id": 6791,
							"namakecamatan": "SIRITAUN WIDA TIMUR"
						},
						{
							"id": 7188,
							"namakecamatan": "SIRIWO"
						},
						{
							"id": 7091,
							"namakecamatan": "SIRIWO"
						},
						{
							"id": 759,
							"namakecamatan": "SIROMBU"
						},
						{
							"id": 384,
							"namakecamatan": "SIROMBU"
						},
						{
							"id": 6809,
							"namakecamatan": "SIR-SIR"
						},
						{
							"id": 322,
							"namakecamatan": "SITAHUIS"
						},
						{
							"id": 662,
							"namakecamatan": "SITELU TALI URANG JEHE"
						},
						{
							"id": 665,
							"namakecamatan": "SITELU TALI URANG JULU"
						},
						{
							"id": 1193,
							"namakecamatan": "SITINJAU LAUT"
						},
						{
							"id": 583,
							"namakecamatan": "SITINJO"
						},
						{
							"id": 688,
							"namakecamatan": "SITIO-TIO"
						},
						{
							"id": 965,
							"namakecamatan": "SITIUNG"
						},
						{
							"id": 872,
							"namakecamatan": "SITIUNG"
						},
						{
							"id": 407,
							"namakecamatan": "SITOLU ORI"
						},
						{
							"id": 750,
							"namakecamatan": "SITOLU ORI"
						},
						{
							"id": 3750,
							"namakecamatan": "SITUBONDO"
						},
						{
							"id": 929,
							"namakecamatan": "SITUJUAH LIMO NAGARI"
						},
						{
							"id": 2580,
							"namakecamatan": "SITURAJA"
						},
						{
							"id": 1205,
							"namakecamatan": "SIULAK"
						},
						{
							"id": 1207,
							"namakecamatan": "SIULAK MUKAI"
						},
						{
							"id": 6784,
							"namakecamatan": "SIWALALAT"
						},
						{
							"id": 3332,
							"namakecamatan": "SIWALAN"
						},
						{
							"id": 7247,
							"namakecamatan": "SKANTO"
						},
						{
							"id": 3510,
							"namakecamatan": "SLAHUNG"
						},
						{
							"id": 3358,
							"namakecamatan": "SLAWI"
						},
						{
							"id": 3479,
							"namakecamatan": "SLEMAN"
						},
						{
							"id": 2612,
							"namakecamatan": "SLIYEG"
						},
						{
							"id": 3088,
							"namakecamatan": "SLOGOHIMO"
						},
						{
							"id": 3179,
							"namakecamatan": "SLUKE"
						},
						{
							"id": 4707,
							"namakecamatan": "SOA"
						},
						{
							"id": 7305,
							"namakecamatan": "SOBA"
						},
						{
							"id": 7332,
							"namakecamatan": "SOBAHAM"
						},
						{
							"id": 4196,
							"namakecamatan": "SOBANG"
						},
						{
							"id": 4218,
							"namakecamatan": "SOBANG"
						},
						{
							"id": 4032,
							"namakecamatan": "SOCAH"
						},
						{
							"id": 2413,
							"namakecamatan": "SODONGHILIR"
						},
						{
							"id": 410,
							"namakecamatan": "SOGAE'ADU"
						},
						{
							"id": 5784,
							"namakecamatan": "SOJOL"
						},
						{
							"id": 5800,
							"namakecamatan": "SOJOL UTARA"
						},
						{
							"id": 4964,
							"namakecamatan": "SOKAN"
						},
						{
							"id": 5036,
							"namakecamatan": "SOKAN"
						},
						{
							"id": 2888,
							"namakecamatan": "SOKARAJA"
						},
						{
							"id": 3976,
							"namakecamatan": "SOKO"
						},
						{
							"id": 4059,
							"namakecamatan": "SOKOBANAH"
						},
						{
							"id": 4255,
							"namakecamatan": "SOLEAR"
						},
						{
							"id": 7325,
							"namakecamatan": "SOLOIKMA"
						},
						{
							"id": 2347,
							"namakecamatan": "SOLOKANJERUK"
						},
						{
							"id": 4000,
							"namakecamatan": "SOLOKURO"
						},
						{
							"id": 4645,
							"namakecamatan": "SOLOR BARAT"
						},
						{
							"id": 4658,
							"namakecamatan": "SOLOR SELATAN"
						},
						{
							"id": 4646,
							"namakecamatan": "SOLOR TIMUR"
						},
						{
							"id": 2878,
							"namakecamatan": "SOMAGEDE"
						},
						{
							"id": 656,
							"namakecamatan": "SOMAMBAWA"
						},
						{
							"id": 5992,
							"namakecamatan": "SOMBA UPU"
						},
						{
							"id": 404,
							"namakecamatan": "SOMOLO-MOLO"
						},
						{
							"id": 5022,
							"namakecamatan": "SOMPAK"
						},
						{
							"id": 5563,
							"namakecamatan": "SONDER"
						},
						{
							"id": 3376,
							"namakecamatan": "SONGGOM"
						},
						{
							"id": 3715,
							"namakecamatan": "SONGGON"
						},
						{
							"id": 3839,
							"namakecamatan": "SOOKO"
						},
						{
							"id": 3515,
							"namakecamatan": "SOOKO"
						},
						{
							"id": 6172,
							"namakecamatan": "SOPAI"
						},
						{
							"id": 6222,
							"namakecamatan": "SOPAI"
						},
						{
							"id": 6069,
							"namakecamatan": "SOPPENG RIAJA"
						},
						{
							"id": 6538,
							"namakecamatan": "SORAWOLIO"
						},
						{
							"id": 2350,
							"namakecamatan": "SOREANG"
						},
						{
							"id": 6252,
							"namakecamatan": "SOREANG"
						},
						{
							"id": 311,
							"namakecamatan": "SORKAM"
						},
						{
							"id": 319,
							"namakecamatan": "SORKAM BARAT"
						},
						{
							"id": 4485,
							"namakecamatan": "SOROMANDI"
						},
						{
							"id": 7644,
							"namakecamatan": "SORONG"
						},
						{
							"id": 7868,
							"namakecamatan": "SORONG"
						},
						{
							"id": 7870,
							"namakecamatan": "SORONG BARAT"
						},
						{
							"id": 7871,
							"namakecamatan": "SORONG KEPULAUAN"
						},
						{
							"id": 7874,
							"namakecamatan": "SORONG KOTA"
						},
						{
							"id": 7873,
							"namakecamatan": "SORONG MANOI"
						},
						{
							"id": 7869,
							"namakecamatan": "SORONG TIMUR"
						},
						{
							"id": 7872,
							"namakecamatan": "SORONG UTARA"
						},
						{
							"id": 6300,
							"namakecamatan": "SOROPIA"
						},
						{
							"id": 729,
							"namakecamatan": "SOSA"
						},
						{
							"id": 357,
							"namakecamatan": "SOSA"
						},
						{
							"id": 1342,
							"namakecamatan": "SOSOH BUAY RAYAP"
						},
						{
							"id": 355,
							"namakecamatan": "SOSOPAN"
						},
						{
							"id": 722,
							"namakecamatan": "SOSOPAN"
						},
						{
							"id": 318,
							"namakecamatan": "SOSOR GADONG"
						},
						{
							"id": 6982,
							"namakecamatan": "SOTA"
						},
						{
							"id": 7791,
							"namakecamatan": "SOUG JAYA"
						},
						{
							"id": 7401,
							"namakecamatan": "SOYOI MAMBAI"
						},
						{
							"id": 5924,
							"namakecamatan": "SOYO JAYA"
						},
						{
							"id": 5833,
							"namakecamatan": "SOYO JAYA"
						},
						{
							"id": 3121,
							"namakecamatan": "SRAGEN"
						},
						{
							"id": 3325,
							"namakecamatan": "SRAGI"
						},
						{
							"id": 1768,
							"namakecamatan": "SRAGI"
						},
						{
							"id": 3432,
							"namakecamatan": "SRANDAKAN"
						},
						{
							"id": 3566,
							"namakecamatan": "SRENGAT"
						},
						{
							"id": 4049,
							"namakecamatan": "SRESEH"
						},
						{
							"id": 3704,
							"namakecamatan": "SRONO"
						},
						{
							"id": 2996,
							"namakecamatan": "SRUMBUNG"
						},
						{
							"id": 2948,
							"namakecamatan": "SRUWENG"
						},
						{
							"id": 417,
							"namakecamatan": "STABAT"
						},
						{
							"id": 1454,
							"namakecamatan": "STL ULU TERAWAS"
						},
						{
							"id": 458,
							"namakecamatan": "STM HILIR"
						},
						{
							"id": 470,
							"namakecamatan": "STM HULU"
						},
						{
							"id": 2117,
							"namakecamatan": "SUAK MIDAI"
						},
						{
							"id": 1495,
							"namakecamatan": "SUAK TAPEH"
						},
						{
							"id": 7443,
							"namakecamatan": "SUATOR"
						},
						{
							"id": 4872,
							"namakecamatan": "SUBAH"
						},
						{
							"id": 3309,
							"namakecamatan": "SUBAH"
						},
						{
							"id": 2479,
							"namakecamatan": "SUBANG"
						},
						{
							"id": 2634,
							"namakecamatan": "SUBANG"
						},
						{
							"id": 2104,
							"namakecamatan": "SUBI"
						},
						{
							"id": 3746,
							"namakecamatan": "SUBOH"
						},
						{
							"id": 7416,
							"namakecamatan": "SUBUR"
						},
						{
							"id": 2401,
							"namakecamatan": "SUCINARAJA"
						},
						{
							"id": 3509,
							"namakecamatan": "SUDIMORO"
						},
						{
							"id": 7180,
							"namakecamatan": "SUGAPA"
						},
						{
							"id": 7585,
							"namakecamatan": "SUGAPA"
						},
						{
							"id": 3944,
							"namakecamatan": "SUGIHWARAS"
						},
						{
							"id": 3997,
							"namakecamatan": "SUGIO"
						},
						{
							"id": 4989,
							"namakecamatan": "SUHAID"
						},
						{
							"id": 327,
							"namakecamatan": "SUKA BANGUN"
						},
						{
							"id": 2008,
							"namakecamatan": "SUKABUMI"
						},
						{
							"id": 2266,
							"namakecamatan": "SUKABUMI"
						},
						{
							"id": 4927,
							"namakecamatan": "SUKADANA"
						},
						{
							"id": 2454,
							"namakecamatan": "SUKADANA"
						},
						{
							"id": 5041,
							"namakecamatan": "SUKADANA"
						},
						{
							"id": 1913,
							"namakecamatan": "SUKADANA"
						},
						{
							"id": 4234,
							"namakecamatan": "SUKADIRI"
						},
						{
							"id": 2627,
							"namakecamatan": "SUKAGUMIWANG"
						},
						{
							"id": 2556,
							"namakecamatan": "SUKAHAJI"
						},
						{
							"id": 2434,
							"namakecamatan": "SUKAHENING"
						},
						{
							"id": 2777,
							"namakecamatan": "SUKAJADI"
						},
						{
							"id": 1171,
							"namakecamatan": "SUKAJADI"
						},
						{
							"id": 2229,
							"namakecamatan": "SUKAJAYA"
						},
						{
							"id": 295,
							"namakecamatan": "SUKAJAYA"
						},
						{
							"id": 2722,
							"namakecamatan": "SUKAKARYA"
						},
						{
							"id": 294,
							"namakecamatan": "SUKAKARYA"
						},
						{
							"id": 1465,
							"namakecamatan": "SUKA KARYA"
						},
						{
							"id": 2270,
							"namakecamatan": "SUKALARANG"
						},
						{
							"id": 2290,
							"namakecamatan": "SUKALUYU"
						},
						{
							"id": 6197,
							"namakecamatan": "SUKAMAJU"
						},
						{
							"id": 250,
							"namakecamatan": "SUKA MAKMUE"
						},
						{
							"id": 2203,
							"namakecamatan": "SUKAMAKMUR"
						},
						{
							"id": 97,
							"namakecamatan": "SUKAMAKMUR"
						},
						{
							"id": 2473,
							"namakecamatan": "SUKAMANTRI"
						},
						{
							"id": 5149,
							"namakecamatan": "SUKAMARA"
						},
						{
							"id": 1444,
							"namakecamatan": "SUKAMERINDU"
						},
						{
							"id": 4419,
							"namakecamatan": "SUKAMULIA"
						},
						{
							"id": 4251,
							"namakecamatan": "SUKAMULYA"
						},
						{
							"id": 2295,
							"namakecamatan": "SUKANAGARA"
						},
						{
							"id": 3761,
							"namakecamatan": "SUKAPURA"
						},
						{
							"id": 2198,
							"namakecamatan": "SUKARAJA"
						},
						{
							"id": 2267,
							"namakecamatan": "SUKARAJA"
						},
						{
							"id": 2418,
							"namakecamatan": "SUKARAJA"
						},
						{
							"id": 1686,
							"namakecamatan": "SUKARAJA"
						},
						{
							"id": 2427,
							"namakecamatan": "SUKARAME"
						},
						{
							"id": 1998,
							"namakecamatan": "SUKARAME"
						},
						{
							"id": 1582,
							"namakecamatan": "SUKARAMI"
						},
						{
							"id": 2432,
							"namakecamatan": "SUKARATU"
						},
						{
							"id": 2440,
							"namakecamatan": "SUKARESIK"
						},
						{
							"id": 4190,
							"namakecamatan": "SUKARESMI"
						},
						{
							"id": 2380,
							"namakecamatan": "SUKARESMI"
						},
						{
							"id": 2294,
							"namakecamatan": "SUKARESMI"
						},
						{
							"id": 4378,
							"namakecamatan": "SUKASADA"
						},
						{
							"id": 2655,
							"namakecamatan": "SUKASARI"
						},
						{
							"id": 2586,
							"namakecamatan": "SUKASARI"
						},
						{
							"id": 2771,
							"namakecamatan": "SUKASARI"
						},
						{
							"id": 2676,
							"namakecamatan": "SUKASARI"
						},
						{
							"id": 2666,
							"namakecamatan": "SUKATANI"
						},
						{
							"id": 2723,
							"namakecamatan": "SUKATANI"
						},
						{
							"id": 1839,
							"namakecamatan": "SUKAU"
						},
						{
							"id": 2711,
							"namakecamatan": "SUKAWANGI"
						},
						{
							"id": 4351,
							"namakecamatan": "SUKAWATI"
						},
						{
							"id": 2374,
							"namakecamatan": "SUKAWENING"
						},
						{
							"id": 7579,
							"namakecamatan": "SUKIKAI SELATAN"
						},
						{
							"id": 7096,
							"namakecamatan": "SUKIKAI SELATAN"
						},
						{
							"id": 2822,
							"namakecamatan": "SUKMAJAYA"
						},
						{
							"id": 4002,
							"namakecamatan": "SUKODADI"
						},
						{
							"id": 3128,
							"namakecamatan": "SUKODONO"
						},
						{
							"id": 3659,
							"namakecamatan": "SUKODONO"
						},
						{
							"id": 3822,
							"namakecamatan": "SUKODONO"
						},
						{
							"id": 1969,
							"namakecamatan": "SUKOHARJO"
						},
						{
							"id": 3061,
							"namakecamatan": "SUKOHARJO"
						},
						{
							"id": 1891,
							"namakecamatan": "SUKOHARJO"
						},
						{
							"id": 2990,
							"namakecamatan": "SUKOHARJO"
						},
						{
							"id": 4136,
							"namakecamatan": "SUKOLILO"
						},
						{
							"id": 3181,
							"namakecamatan": "SUKOLILO"
						},
						{
							"id": 4154,
							"namakecamatan": "SUKOMANUNGGAL"
						},
						{
							"id": 3877,
							"namakecamatan": "SUKOMORO"
						},
						{
							"id": 3909,
							"namakecamatan": "SUKOMORO"
						},
						{
							"id": 3680,
							"namakecamatan": "SUKORAMBI"
						},
						{
							"id": 3986,
							"namakecamatan": "SUKORAME"
						},
						{
							"id": 4107,
							"namakecamatan": "SUKOREJO"
						},
						{
							"id": 3524,
							"namakecamatan": "SUKOREJO"
						},
						{
							"id": 3793,
							"namakecamatan": "SUKOREJO"
						},
						{
							"id": 3283,
							"namakecamatan": "SUKOREJO"
						},
						{
							"id": 3724,
							"namakecamatan": "SUKOSARI"
						},
						{
							"id": 3961,
							"namakecamatan": "SUKOSEWU"
						},
						{
							"id": 3694,
							"namakecamatan": "SUKOWONO"
						},
						{
							"id": 2624,
							"namakecamatan": "SUKRA"
						},
						{
							"id": 4112,
							"namakecamatan": "SUKUN"
						},
						{
							"id": 6920,
							"namakecamatan": "SULABESI BARAT"
						},
						{
							"id": 6926,
							"namakecamatan": "SULABESI SELATAN"
						},
						{
							"id": 6924,
							"namakecamatan": "SULABESI TENGAH"
						},
						{
							"id": 6925,
							"namakecamatan": "SULABESI TIMUR"
						},
						{
							"id": 4519,
							"namakecamatan": "SULAMU"
						},
						{
							"id": 3174,
							"namakecamatan": "SULANG"
						},
						{
							"id": 6132,
							"namakecamatan": "SULI"
						},
						{
							"id": 6148,
							"namakecamatan": "SULI BARAT"
						},
						{
							"id": 920,
							"namakecamatan": "SULIKI"
						},
						{
							"id": 5144,
							"namakecamatan": "SULING TAMBUN"
						},
						{
							"id": 308,
							"namakecamatan": "SULTAN DAULAT"
						},
						{
							"id": 189,
							"namakecamatan": "SULTAN DAULAT"
						},
						{
							"id": 5644,
							"namakecamatan": "SULUUN TARERAN"
						},
						{
							"id": 6609,
							"namakecamatan": "SUMALATA"
						},
						{
							"id": 6551,
							"namakecamatan": "SUMALATA"
						},
						{
							"id": 6616,
							"namakecamatan": "SUMALATA TIMUR"
						},
						{
							"id": 6659,
							"namakecamatan": "SUMARORONG"
						},
						{
							"id": 1309,
							"namakecamatan": "SUMAY"
						},
						{
							"id": 2890,
							"namakecamatan": "SUMBANG"
						},
						{
							"id": 4441,
							"namakecamatan": "SUMBAWA"
						},
						{
							"id": 2523,
							"namakecamatan": "SUMBER"
						},
						{
							"id": 3167,
							"namakecamatan": "SUMBER"
						},
						{
							"id": 3762,
							"namakecamatan": "SUMBER"
						},
						{
							"id": 3781,
							"namakecamatan": "SUMBERASIH"
						},
						{
							"id": 5186,
							"namakecamatan": "SUMBER BARITO"
						},
						{
							"id": 3668,
							"namakecamatan": "SUMBERBARU"
						},
						{
							"id": 3949,
							"namakecamatan": "SUMBEREJO"
						},
						{
							"id": 3554,
							"namakecamatan": "SUMBERGEMPOL"
						},
						{
							"id": 1463,
							"namakecamatan": "SUMBER HARTA"
						},
						{
							"id": 3696,
							"namakecamatan": "SUMBERJAMBE"
						},
						{
							"id": 2565,
							"namakecamatan": "SUMBERJAYA"
						},
						{
							"id": 1833,
							"namakecamatan": "SUMBER JAYA"
						},
						{
							"id": 3126,
							"namakecamatan": "SUMBERLAWANG"
						},
						{
							"id": 3758,
							"namakecamatan": "SUMBERMALANG"
						},
						{
							"id": 3615,
							"namakecamatan": "SUMBERMANJING WETAN"
						},
						{
							"id": 1497,
							"namakecamatan": "SUMBER MARGA TELANG"
						},
						{
							"id": 3623,
							"namakecamatan": "SUMBERPUCUNG"
						},
						{
							"id": 1897,
							"namakecamatan": "SUMBER REJO"
						},
						{
							"id": 3686,
							"namakecamatan": "SUMBERSARI"
						},
						{
							"id": 3665,
							"namakecamatan": "SUMBERSUKO"
						},
						{
							"id": 3738,
							"namakecamatan": "SUMBERWRINGIN"
						},
						{
							"id": 570,
							"namakecamatan": "SUMBUL"
						},
						{
							"id": 2591,
							"namakecamatan": "SUMEDANG SELATAN"
						},
						{
							"id": 2592,
							"namakecamatan": "SUMEDANG UTARA"
						},
						{
							"id": 7340,
							"namakecamatan": "SUMO"
						},
						{
							"id": 3855,
							"namakecamatan": "SUMOBITO"
						},
						{
							"id": 3249,
							"namakecamatan": "SUMOWONO"
						},
						{
							"id": 2876,
							"namakecamatan": "SUMPIUH"
						},
						{
							"id": 869,
							"namakecamatan": "SUMPUR KUDUS"
						},
						{
							"id": 4162,
							"namakecamatan": "SUMUR"
						},
						{
							"id": 2789,
							"namakecamatan": "SUMUR BANDUNG"
						},
						{
							"id": 7768,
							"namakecamatan": "SUMURI"
						},
						{
							"id": 5049,
							"namakecamatan": "SUNGAI AMBAWANG"
						},
						{
							"id": 4892,
							"namakecamatan": "SUNGAI AMBAWANG"
						},
						{
							"id": 1134,
							"namakecamatan": "SUNGAI APIT"
						},
						{
							"id": 1533,
							"namakecamatan": "SUNGAI ARE"
						},
						{
							"id": 988,
							"namakecamatan": "SUNGAIAUR"
						},
						{
							"id": 5189,
							"namakecamatan": "SUNGAI BABUAT"
						},
						{
							"id": 1259,
							"namakecamatan": "SUNGAI BAHAR"
						},
						{
							"id": 1089,
							"namakecamatan": "SUNGAI BATANG"
						},
						{
							"id": 980,
							"namakecamatan": "SUNGAIBEREMAS"
						},
						{
							"id": 933,
							"namakecamatan": "SUNGAI BEREMAS"
						},
						{
							"id": 5006,
							"namakecamatan": "SUNGAI BETUNG"
						},
						{
							"id": 5486,
							"namakecamatan": "SUNGAI BOH"
						},
						{
							"id": 1335,
							"namakecamatan": "SUNGAI BUNGKAL"
						},
						{
							"id": 5232,
							"namakecamatan": "SUNGAIDURIAN"
						},
						{
							"id": 893,
							"namakecamatan": "SUNGAI GARINGGING"
						},
						{
							"id": 1260,
							"namakecamatan": "SUNGAI GELAM"
						},
						{
							"id": 4885,
							"namakecamatan": "SUNGAI KAKAP"
						},
						{
							"id": 5055,
							"namakecamatan": "SUNGAI KAKAP"
						},
						{
							"id": 737,
							"namakecamatan": "SUNGAI KANAN"
						},
						{
							"id": 1468,
							"namakecamatan": "SUNGAI KERUH"
						},
						{
							"id": 5460,
							"namakecamatan": "SUNGAI KUNJANG"
						},
						{
							"id": 4893,
							"namakecamatan": "SUNGAI KUNYIT"
						},
						{
							"id": 1053,
							"namakecamatan": "SUNGAI LALA"
						},
						{
							"id": 4928,
							"namakecamatan": "SUNGAI LAUR"
						},
						{
							"id": 2022,
							"namakecamatan": "SUNGAILIAT"
						},
						{
							"id": 1472,
							"namakecamatan": "SUNGAI LILIN"
						},
						{
							"id": 894,
							"namakecamatan": "SUNGAI LIMAU"
						},
						{
							"id": 5333,
							"namakecamatan": "SUNGAI LOBAN"
						},
						{
							"id": 1214,
							"namakecamatan": "SUNGAI MANAU"
						},
						{
							"id": 1137,
							"namakecamatan": "SUNGAI MANDAU"
						},
						{
							"id": 82,
							"namakecamatan": "SUNGAI MAS"
						},
						{
							"id": 4946,
							"namakecamatan": "SUNGAI MELAYU RAYAK"
						},
						{
							"id": 1381,
							"namakecamatan": "SUNGAI MENANG"
						},
						{
							"id": 974,
							"namakecamatan": "SUNGAI PAGU"
						},
						{
							"id": 5311,
							"namakecamatan": "SUNGAI PANDAN"
						},
						{
							"id": 1328,
							"namakecamatan": "SUNGAI PENUH"
						},
						{
							"id": 1192,
							"namakecamatan": "SUNGAI PENUH"
						},
						{
							"id": 1549,
							"namakecamatan": "SUNGAI PINANG"
						},
						{
							"id": 5462,
							"namakecamatan": "SUNGAI PINANG"
						},
						{
							"id": 5248,
							"namakecamatan": "SUNGAI PINANG"
						},
						{
							"id": 4888,
							"namakecamatan": "SUNGAI PINYUH"
						},
						{
							"id": 915,
							"namakecamatan": "SUNGAI PUA"
						},
						{
							"id": 53,
							"namakecamatan": "SUNGAI RAYA"
						},
						{
							"id": 5287,
							"namakecamatan": "SUNGAI RAYA"
						},
						{
							"id": 4993,
							"namakecamatan": "SUNGAI RAYA"
						},
						{
							"id": 5047,
							"namakecamatan": "SUNGAI RAYA"
						},
						{
							"id": 4883,
							"namakecamatan": "SUNGAI RAYA"
						},
						{
							"id": 5007,
							"namakecamatan": "SUNGAI RAYAKEPULAUAN"
						},
						{
							"id": 1406,
							"namakecamatan": "SUNGAI ROTAN"
						},
						{
							"id": 871,
							"namakecamatan": "SUNGAI RUMBAI"
						},
						{
							"id": 964,
							"namakecamatan": "SUNGAI RUMBAI"
						},
						{
							"id": 1714,
							"namakecamatan": "SUNGAI RUMBAI"
						},
						{
							"id": 2045,
							"namakecamatan": "SUNGAI SELAN"
						},
						{
							"id": 1186,
							"namakecamatan": "SUNGAI SEMBILAN"
						},
						{
							"id": 1752,
							"namakecamatan": "SUNGAI SERUT"
						},
						{
							"id": 5242,
							"namakecamatan": "SUNGAI TABUK"
						},
						{
							"id": 5318,
							"namakecamatan": "SUNGAI TABUKAN"
						},
						{
							"id": 880,
							"namakecamatan": "SUNGAI TARAB"
						},
						{
							"id": 4966,
							"namakecamatan": "SUNGAI TEBELIAN"
						},
						{
							"id": 5492,
							"namakecamatan": "SUNGAI TUBU"
						},
						{
							"id": 879,
							"namakecamatan": "SUNGAYANG"
						},
						{
							"id": 473,
							"namakecamatan": "SUNGGAL"
						},
						{
							"id": 1826,
							"namakecamatan": "SUNGKAI BARAT"
						},
						{
							"id": 1825,
							"namakecamatan": "SUNGKAI JAYA"
						},
						{
							"id": 1808,
							"namakecamatan": "SUNGKAI SELATAN"
						},
						{
							"id": 1823,
							"namakecamatan": "SUNGKAI TENGAH"
						},
						{
							"id": 1813,
							"namakecamatan": "SUNGKAI UTARA"
						},
						{
							"id": 7647,
							"namakecamatan": "SUNOOK"
						},
						{
							"id": 7330,
							"namakecamatan": "SUNTAMON"
						},
						{
							"id": 1837,
							"namakecamatan": "SUOH"
						},
						{
							"id": 7460,
							"namakecamatan": "SUPIORI BARAT"
						},
						{
							"id": 7126,
							"namakecamatan": "SUPIORI SELATAN"
						},
						{
							"id": 7456,
							"namakecamatan": "SUPIORI SELATAN"
						},
						{
							"id": 7458,
							"namakecamatan": "SUPIORI TIMUR"
						},
						{
							"id": 7127,
							"namakecamatan": "SUPIORI UTARA"
						},
						{
							"id": 7457,
							"namakecamatan": "SUPIORI UTARA"
						},
						{
							"id": 7752,
							"namakecamatan": "SUPNIN"
						},
						{
							"id": 6107,
							"namakecamatan": "SUPPA"
						},
						{
							"id": 3364,
							"namakecamatan": "SURADADI"
						},
						{
							"id": 2258,
							"namakecamatan": "SURADE"
						},
						{
							"id": 4426,
							"namakecamatan": "SURALAGA"
						},
						{
							"id": 2547,
							"namakecamatan": "SURANENGGALA"
						},
						{
							"id": 2583,
							"namakecamatan": "SURIAN"
						},
						{
							"id": 194,
							"namakecamatan": "SURO MAKMUR"
						},
						{
							"id": 3244,
							"namakecamatan": "SURUH"
						},
						{
							"id": 3544,
							"namakecamatan": "SURUH"
						},
						{
							"id": 7861,
							"namakecamatan": "SURUREY"
						},
						{
							"id": 7665,
							"namakecamatan": "SURUREY"
						},
						{
							"id": 7298,
							"namakecamatan": "SURU SURU"
						},
						{
							"id": 7444,
							"namakecamatan": "SURU-SURU"
						},
						{
							"id": 219,
							"namakecamatan": "SUSOH"
						},
						{
							"id": 639,
							"namakecamatan": "SUSUA"
						},
						{
							"id": 2535,
							"namakecamatan": "SUSUKAN"
						},
						{
							"id": 2915,
							"namakecamatan": "SUSUKAN"
						},
						{
							"id": 3243,
							"namakecamatan": "SUSUKAN"
						},
						{
							"id": 2516,
							"namakecamatan": "SUSUKAN LEBAK"
						},
						{
							"id": 4362,
							"namakecamatan": "SUSUT"
						},
						{
							"id": 834,
							"namakecamatan": "SUTERA"
						},
						{
							"id": 5002,
							"namakecamatan": "SUTI SEMARANG"
						},
						{
							"id": 3575,
							"namakecamatan": "SUTOJAYAN"
						},
						{
							"id": 6577,
							"namakecamatan": "SUWAWA"
						},
						{
							"id": 6586,
							"namakecamatan": "SUWAWA SELATAN"
						},
						{
							"id": 6587,
							"namakecamatan": "SUWAWA TENGAH"
						},
						{
							"id": 6585,
							"namakecamatan": "SUWAWA TIMUR"
						},
						{
							"id": 4429,
							"namakecamatan": "SUWELA"
						},
						{
							"id": 7135,
							"namakecamatan": "SWANDIWE"
						},
						{
							"id": 7432,
							"namakecamatan": "SYAHCAME"
						},
						{
							"id": 155,
							"namakecamatan": "SYAMTALIRA ARON"
						},
						{
							"id": 156,
							"namakecamatan": "SYAMTALIRA BAYU"
						},
						{
							"id": 288,
							"namakecamatan": "SYIAH KUALA"
						},
						{
							"id": 269,
							"namakecamatan": "SYIAH UTAMA"
						},
						{
							"id": 67,
							"namakecamatan": "SYIAH UTAMA"
						},
						{
							"id": 7623,
							"namakecamatan": "SYUJAK"
						},
						{
							"id": 7805,
							"namakecamatan": "SYUJAK"
						},
						{
							"id": 5397,
							"namakecamatan": "TABALAR"
						},
						{
							"id": 4339,
							"namakecamatan": "TABANAN"
						},
						{
							"id": 5381,
							"namakecamatan": "TABANG"
						},
						{
							"id": 6663,
							"namakecamatan": "TABANG"
						},
						{
							"id": 1740,
							"namakecamatan": "TABA PENANJUNG"
						},
						{
							"id": 1649,
							"namakecamatan": "TABA PENANJUNG"
						},
						{
							"id": 1215,
							"namakecamatan": "TABIR"
						},
						{
							"id": 1233,
							"namakecamatan": "TABIR BARAT"
						},
						{
							"id": 1224,
							"namakecamatan": "TABIR ILIR"
						},
						{
							"id": 1232,
							"namakecamatan": "TABIR LINTAS"
						},
						{
							"id": 1218,
							"namakecamatan": "TABIR SELATAN"
						},
						{
							"id": 1225,
							"namakecamatan": "TABIR TIMUR"
						},
						{
							"id": 1217,
							"namakecamatan": "TABIR ULU"
						},
						{
							"id": 6959,
							"namakecamatan": "TABONA"
						},
						{
							"id": 6562,
							"namakecamatan": "TABONGO"
						},
						{
							"id": 6992,
							"namakecamatan": "TABONJI"
						},
						{
							"id": 5269,
							"namakecamatan": "TABUKAN"
						},
						{
							"id": 5592,
							"namakecamatan": "TABUKAN SELATAN"
						},
						{
							"id": 5596,
							"namakecamatan": "TABUKAN SELATANTENGAH"
						},
						{
							"id": 5597,
							"namakecamatan": "TABUKAN SELATANTENGGARA"
						},
						{
							"id": 5591,
							"namakecamatan": "TABUKAN TENGAH"
						},
						{
							"id": 5585,
							"namakecamatan": "TABUKAN UTARA"
						},
						{
							"id": 6658,
							"namakecamatan": "TABULAHAN"
						},
						{
							"id": 4742,
							"namakecamatan": "TABUNDUNG"
						},
						{
							"id": 5258,
							"namakecamatan": "TABUNGANEN"
						},
						{
							"id": 252,
							"namakecamatan": "TADU RAYA"
						},
						{
							"id": 4536,
							"namakecamatan": "TAEBENU"
						},
						{
							"id": 7040,
							"namakecamatan": "TAELAREK"
						},
						{
							"id": 7173,
							"namakecamatan": "TAGANOMBAK"
						},
						{
							"id": 7381,
							"namakecamatan": "TAGIME"
						},
						{
							"id": 7049,
							"namakecamatan": "TAGIME"
						},
						{
							"id": 7051,
							"namakecamatan": "TAGINERI"
						},
						{
							"id": 7372,
							"namakecamatan": "TAGINERI"
						},
						{
							"id": 5578,
							"namakecamatan": "TAGULANDANG"
						},
						{
							"id": 5675,
							"namakecamatan": "TAGULANDANG"
						},
						{
							"id": 5598,
							"namakecamatan": "TAGULANDANG SELATAN"
						},
						{
							"id": 5682,
							"namakecamatan": "TAGULANDANG SELATAN"
						},
						{
							"id": 5678,
							"namakecamatan": "TAGULANDANG UTARA"
						},
						{
							"id": 5579,
							"namakecamatan": "TAGULANDANG UTARA"
						},
						{
							"id": 7857,
							"namakecamatan": "TAHOTA"
						},
						{
							"id": 5594,
							"namakecamatan": "TAHUNA"
						},
						{
							"id": 5600,
							"namakecamatan": "TAHUNA BARAT"
						},
						{
							"id": 3221,
							"namakecamatan": "TAHUNAN"
						},
						{
							"id": 5601,
							"namakecamatan": "TAHUNA TIMUR"
						},
						{
							"id": 7863,
							"namakecamatan": "TAIGE"
						},
						{
							"id": 7679,
							"namakecamatan": "TAIGE"
						},
						{
							"id": 3626,
							"namakecamatan": "TAJINAN"
						},
						{
							"id": 2231,
							"namakecamatan": "TAJURHALANG"
						},
						{
							"id": 5943,
							"namakecamatan": "TAKA BONERATE"
						},
						{
							"id": 4523,
							"namakecamatan": "TAKARI"
						},
						{
							"id": 3904,
							"namakecamatan": "TAKERAN"
						},
						{
							"id": 5207,
							"namakecamatan": "TAKISUNG"
						},
						{
							"id": 6083,
							"namakecamatan": "TAKKALALLA"
						},
						{
							"id": 2297,
							"namakecamatan": "TAKOKAK"
						},
						{
							"id": 4322,
							"namakecamatan": "TAKTAKAN"
						},
						{
							"id": 4264,
							"namakecamatan": "TAKTAKAN"
						},
						{
							"id": 2552,
							"namakecamatan": "TALAGA"
						},
						{
							"id": 6565,
							"namakecamatan": "TALAGA JAYA"
						},
						{
							"id": 6516,
							"namakecamatan": "TALAGA RAYA"
						},
						{
							"id": 938,
							"namakecamatan": "TALAMAU"
						},
						{
							"id": 983,
							"namakecamatan": "TALAMAU"
						},
						{
							"id": 7318,
							"namakecamatan": "TALAMBO"
						},
						{
							"id": 3360,
							"namakecamatan": "TALANG"
						},
						{
							"id": 1736,
							"namakecamatan": "TALANG EMPAT"
						},
						{
							"id": 1647,
							"namakecamatan": "TALANG EMPAT"
						},
						{
							"id": 1489,
							"namakecamatan": "TALANG KELAPA"
						},
						{
							"id": 4079,
							"namakecamatan": "TALANGO"
						},
						{
							"id": 1559,
							"namakecamatan": "TALANG PADANG"
						},
						{
							"id": 1429,
							"namakecamatan": "TALANG PADANG"
						},
						{
							"id": 1886,
							"namakecamatan": "TALANG PADANG"
						},
						{
							"id": 1564,
							"namakecamatan": "TALANG UBI"
						},
						{
							"id": 1395,
							"namakecamatan": "TALANG UBI"
						},
						{
							"id": 5895,
							"namakecamatan": "TALATAKO"
						},
						{
							"id": 5653,
							"namakecamatan": "TALAWAAN"
						},
						{
							"id": 1007,
							"namakecamatan": "TALAWI"
						},
						{
							"id": 519,
							"namakecamatan": "TALAWI"
						},
						{
							"id": 710,
							"namakecamatan": "TALAWI"
						},
						{
							"id": 2396,
							"namakecamatan": "TALEGONG"
						},
						{
							"id": 6952,
							"namakecamatan": "TALIABU BARAT"
						},
						{
							"id": 6921,
							"namakecamatan": "TALIABU BARAT"
						},
						{
							"id": 6933,
							"namakecamatan": "TALIABU BARAT LAUT"
						},
						{
							"id": 6953,
							"namakecamatan": "TALIABU BARAT LAUT"
						},
						{
							"id": 6934,
							"namakecamatan": "TALIABU SELATAN"
						},
						{
							"id": 6958,
							"namakecamatan": "TALIABU SELATAN"
						},
						{
							"id": 6956,
							"namakecamatan": "TALIABU TIMUR"
						},
						{
							"id": 6931,
							"namakecamatan": "TALIABU -TIMUR"
						},
						{
							"id": 6922,
							"namakecamatan": "TALIABU TIMUR SELATAN"
						},
						{
							"id": 6957,
							"namakecamatan": "TALIABU TIMUR SELATAN"
						},
						{
							"id": 6932,
							"namakecamatan": "TALIABU UTARA"
						},
						{
							"id": 6955,
							"namakecamatan": "TALIABU UTARA"
						},
						{
							"id": 4666,
							"namakecamatan": "TALIBURA"
						},
						{
							"id": 5389,
							"namakecamatan": "TALISAYAN"
						},
						{
							"id": 4490,
							"namakecamatan": "TALIWANG"
						},
						{
							"id": 4436,
							"namakecamatan": "TALIWANG"
						},
						{
							"id": 6242,
							"namakecamatan": "TALLO"
						},
						{
							"id": 6225,
							"namakecamatan": "TALLUNGLIPU"
						},
						{
							"id": 1688,
							"namakecamatan": "TALO"
						},
						{
							"id": 1697,
							"namakecamatan": "TALO KECIL"
						},
						{
							"id": 6599,
							"namakecamatan": "TALUDITI"
						},
						{
							"id": 2522,
							"namakecamatan": "TALUN"
						},
						{
							"id": 3577,
							"namakecamatan": "TALUN"
						},
						{
							"id": 3320,
							"namakecamatan": "TALUN"
						},
						{
							"id": 6174,
							"namakecamatan": "TALUNGLIPU"
						},
						{
							"id": 5589,
							"namakecamatan": "TAMAKO"
						},
						{
							"id": 6249,
							"namakecamatan": "TAMALANREA"
						},
						{
							"id": 6245,
							"namakecamatan": "TAMALATE"
						},
						{
							"id": 5966,
							"namakecamatan": "TAMALATEA"
						},
						{
							"id": 3821,
							"namakecamatan": "TAMAN"
						},
						{
							"id": 3343,
							"namakecamatan": "TAMAN"
						},
						{
							"id": 4127,
							"namakecamatan": "TAMAN"
						},
						{
							"id": 3722,
							"namakecamatan": "TAMANAN"
						},
						{
							"id": 3741,
							"namakecamatan": "TAMAN KROCOK"
						},
						{
							"id": 1263,
							"namakecamatan": "TAMAN RAJO"
						},
						{
							"id": 2225,
							"namakecamatan": "TAMANSARI"
						},
						{
							"id": 2838,
							"namakecamatan": "TAMANSARI"
						},
						{
							"id": 2063,
							"namakecamatan": "TAMAN SARI"
						},
						{
							"id": 2169,
							"namakecamatan": "TAMAN SARI"
						},
						{
							"id": 2877,
							"namakecamatan": "TAMBAK"
						},
						{
							"id": 4030,
							"namakecamatan": "TAMBAK"
						},
						{
							"id": 3971,
							"namakecamatan": "TAMBAKBOYO"
						},
						{
							"id": 2656,
							"namakecamatan": "TAMBAKDAHAN"
						},
						{
							"id": 3939,
							"namakecamatan": "TAMBAKREJO"
						},
						{
							"id": 3183,
							"namakecamatan": "TAMBAKROMO"
						},
						{
							"id": 4137,
							"namakecamatan": "TAMBAKSARI"
						},
						{
							"id": 2456,
							"namakecamatan": "TAMBAKSARI"
						},
						{
							"id": 5259,
							"namakecamatan": "TAMBAN"
						},
						{
							"id": 5102,
							"namakecamatan": "TAMBAN CATUR"
						},
						{
							"id": 1024,
							"namakecamatan": "TAMBANG"
						},
						{
							"id": 617,
							"namakecamatan": "TAMBANGAN"
						},
						{
							"id": 5214,
							"namakecamatan": "TAMBANG ULANG"
						},
						{
							"id": 2077,
							"namakecamatan": "TAMBELAN"
						},
						{
							"id": 2712,
							"namakecamatan": "TAMBELANG"
						},
						{
							"id": 4056,
							"namakecamatan": "TAMBELANGAN"
						},
						{
							"id": 4484,
							"namakecamatan": "TAMBORA"
						},
						{
							"id": 2170,
							"namakecamatan": "TAMBORA"
						},
						{
							"id": 2714,
							"namakecamatan": "TAMBUN SELATAN"
						},
						{
							"id": 2713,
							"namakecamatan": "TAMBUN UTARA"
						},
						{
							"id": 1105,
							"namakecamatan": "TAMBUSAI"
						},
						{
							"id": 1110,
							"namakecamatan": "TAMBUSAI UTARA"
						},
						{
							"id": 261,
							"namakecamatan": "TAMIANG HULU"
						},
						{
							"id": 6692,
							"namakecamatan": "TAMMERODO SENDANA"
						},
						{
							"id": 605,
							"namakecamatan": "TAMPAHAN"
						},
						{
							"id": 4354,
							"namakecamatan": "TAMPAKSIRING"
						},
						{
							"id": 1178,
							"namakecamatan": "TAMPAN"
						},
						{
							"id": 5612,
							"namakecamatan": "TAMPAN' AMMA"
						},
						{
							"id": 1568,
							"namakecamatan": "TANAH ABANG"
						},
						{
							"id": 2159,
							"namakecamatan": "TANAH ABANG"
						},
						{
							"id": 1402,
							"namakecamatan": "TANAH ABANG"
						},
						{
							"id": 5363,
							"namakecamatan": "TANAH GROGOT"
						},
						{
							"id": 494,
							"namakecamatan": "TANAH JAWA"
						},
						{
							"id": 1203,
							"namakecamatan": "TANAH KAMPUNG"
						},
						{
							"id": 1331,
							"namakecamatan": "TANAH KAMPUNG"
						},
						{
							"id": 157,
							"namakecamatan": "TANAH LUAS"
						},
						{
							"id": 661,
							"namakecamatan": "TANAH MASA"
						},
						{
							"id": 4043,
							"namakecamatan": "TANAH MERAH"
						},
						{
							"id": 1079,
							"namakecamatan": "TANAH MERAH"
						},
						{
							"id": 6980,
							"namakecamatan": "TANAH MIRING"
						},
						{
							"id": 158,
							"namakecamatan": "TANAH PASIR"
						},
						{
							"id": 574,
							"namakecamatan": "TANAH PINEM"
						},
						{
							"id": 5035,
							"namakecamatan": "TANAH PINOH"
						},
						{
							"id": 4963,
							"namakecamatan": "TANAH PINOH"
						},
						{
							"id": 5040,
							"namakecamatan": "TANAH PINOH BARAT"
						},
						{
							"id": 1120,
							"namakecamatan": "TANAH PUTIH"
						},
						{
							"id": 1126,
							"namakecamatan": "TANAH PUTIH TANJUNGMELAWAN"
						},
						{
							"id": 7673,
							"namakecamatan": "TANAH RUBUH"
						},
						{
							"id": 2763,
							"namakecamatan": "TANAH SAREAL"
						},
						{
							"id": 1292,
							"namakecamatan": "TANAH SEPENGGAL"
						},
						{
							"id": 1304,
							"namakecamatan": "TANAH SEPENGGALLINTAS"
						},
						{
							"id": 5183,
							"namakecamatan": "TANAH SIANG"
						},
						{
							"id": 5188,
							"namakecamatan": "TANAH SIANG SELATAN"
						},
						{
							"id": 1288,
							"namakecamatan": "TANAH TUMBUH"
						},
						{
							"id": 5511,
							"namakecamatan": "TANA LIA"
						},
						{
							"id": 6203,
							"namakecamatan": "TANA LILI"
						},
						{
							"id": 5786,
							"namakecamatan": "TANAMBULAVA"
						},
						{
							"id": 5905,
							"namakecamatan": "TANAMBULAVA"
						},
						{
							"id": 5789,
							"namakecamatan": "TANANTOVEA"
						},
						{
							"id": 4274,
							"namakecamatan": "TANARA"
						},
						{
							"id": 4763,
							"namakecamatan": "TANA RIGHU"
						},
						{
							"id": 6088,
							"namakecamatan": "TANASITOLO"
						},
						{
							"id": 4675,
							"namakecamatan": "TANA WAWO"
						},
						{
							"id": 4141,
							"namakecamatan": "TANDES"
						},
						{
							"id": 6662,
							"namakecamatan": "TANDUK KALUA"
						},
						{
							"id": 1112,
							"namakecamatan": "TANDUN"
						},
						{
							"id": 6066,
							"namakecamatan": "TANETE RIAJA"
						},
						{
							"id": 6032,
							"namakecamatan": "TANETE RIATTANG"
						},
						{
							"id": 6033,
							"namakecamatan": "TANETE RIATTANG BARAT"
						},
						{
							"id": 6034,
							"namakecamatan": "TANETE RIATTANG TIMUR"
						},
						{
							"id": 6067,
							"namakecamatan": "TANETE RILAU"
						},
						{
							"id": 217,
							"namakecamatan": "TANGAN-TANGAN"
						},
						{
							"id": 4879,
							"namakecamatan": "TANGARAN"
						},
						{
							"id": 3130,
							"namakecamatan": "TANGEN"
						},
						{
							"id": 4296,
							"namakecamatan": "TANGERANG"
						},
						{
							"id": 6280,
							"namakecamatan": "TANGGETADA"
						},
						{
							"id": 2300,
							"namakecamatan": "TANGGEUNG"
						},
						{
							"id": 3671,
							"namakecamatan": "TANGGUL"
						},
						{
							"id": 3814,
							"namakecamatan": "TANGGULANGIN"
						},
						{
							"id": 3563,
							"namakecamatan": "TANGGUNGGUNUNG"
						},
						{
							"id": 3150,
							"namakecamatan": "TANGGUNGHARJO"
						},
						{
							"id": 7307,
							"namakecamatan": "TANGMA"
						},
						{
							"id": 133,
							"namakecamatan": "TANGSE"
						},
						{
							"id": 6745,
							"namakecamatan": "TANIMBAR SELATAN"
						},
						{
							"id": 6749,
							"namakecamatan": "TANIMBAR UTARA"
						},
						{
							"id": 6795,
							"namakecamatan": "TANIWEL"
						},
						{
							"id": 6704,
							"namakecamatan": "TANIWEL"
						},
						{
							"id": 6802,
							"namakecamatan": "TANIWEL TIMUR"
						},
						{
							"id": 884,
							"namakecamatan": "TANJUANG BARU"
						},
						{
							"id": 3379,
							"namakecamatan": "TANJUNG"
						},
						{
							"id": 5322,
							"namakecamatan": "TANJUNG"
						},
						{
							"id": 4497,
							"namakecamatan": "TANJUNG"
						},
						{
							"id": 1391,
							"namakecamatan": "TANJUNG AGUNG"
						},
						{
							"id": 1667,
							"namakecamatan": "TANJUNG AGUNG PALIK"
						},
						{
							"id": 3876,
							"namakecamatan": "TANJUNGANOM"
						},
						{
							"id": 524,
							"namakecamatan": "TANJUNG BALAI"
						},
						{
							"id": 799,
							"namakecamatan": "TANJUNG BALAI SELATAN"
						},
						{
							"id": 800,
							"namakecamatan": "TANJUNG BALAI UTARA"
						},
						{
							"id": 1539,
							"namakecamatan": "TANJUNG BATU"
						},
						{
							"id": 1372,
							"namakecamatan": "TANJUNG BATU"
						},
						{
							"id": 693,
							"namakecamatan": "TANJUNG BERINGIN"
						},
						{
							"id": 466,
							"namakecamatan": "TANJUNG BERINGIN"
						},
						{
							"id": 1758,
							"namakecamatan": "TANJUNG BINTANG"
						},
						{
							"id": 4039,
							"namakecamatan": "TANJUNG BUMI"
						},
						{
							"id": 4644,
							"namakecamatan": "TANJUNG BUNGA"
						},
						{
							"id": 877,
							"namakecamatan": "TANJUNG EMAS"
						},
						{
							"id": 863,
							"namakecamatan": "TANJUNG GADANG"
						},
						{
							"id": 5361,
							"namakecamatan": "TANJUNG HARAPAN"
						},
						{
							"id": 1003,
							"namakecamatan": "TANJUNG HARAPAN"
						},
						{
							"id": 2417,
							"namakecamatan": "TANJUNGJAYA"
						},
						{
							"id": 1999,
							"namakecamatan": "TANJUNGKARANG BARAT"
						},
						{
							"id": 2002,
							"namakecamatan": "TANJUNGKARANG PUSAT"
						},
						{
							"id": 2001,
							"namakecamatan": "TANJUNGKARANG TIMUR"
						},
						{
							"id": 1672,
							"namakecamatan": "TANJUNG KEMUNING"
						},
						{
							"id": 2594,
							"namakecamatan": "TANJUNGKERTA"
						},
						{
							"id": 1491,
							"namakecamatan": "TANJUNG LAGO"
						},
						{
							"id": 1368,
							"namakecamatan": "TANJUNG LUBUK"
						},
						{
							"id": 2595,
							"namakecamatan": "TANJUNGMEDAR"
						},
						{
							"id": 452,
							"namakecamatan": "TANJUNG MORAWA"
						},
						{
							"id": 904,
							"namakecamatan": "TANJUNG MUTIARA"
						},
						{
							"id": 5468,
							"namakecamatan": "TANJUNG PALAS"
						},
						{
							"id": 5469,
							"namakecamatan": "TANJUNG PALAS BARAT"
						},
						{
							"id": 5473,
							"namakecamatan": "TANJUNG PALAS TENGAH"
						},
						{
							"id": 5471,
							"namakecamatan": "TANJUNG PALAS TIMUR"
						},
						{
							"id": 5470,
							"namakecamatan": "TANJUNG PALAS UTARA"
						},
						{
							"id": 2030,
							"namakecamatan": "TANJUNG PANDAN"
						},
						{
							"id": 2147,
							"namakecamatan": "TANJUNG PINANG BARAT"
						},
						{
							"id": 2149,
							"namakecamatan": "TANJUNG PINANG KOTA"
						},
						{
							"id": 2148,
							"namakecamatan": "TANJUNG PINANG TIMUR"
						},
						{
							"id": 2162,
							"namakecamatan": "TANJUNG PRIOK"
						},
						{
							"id": 421,
							"namakecamatan": "TANJUNG PURA"
						},
						{
							"id": 1809,
							"namakecamatan": "TANJUNG RAJA"
						},
						{
							"id": 1540,
							"namakecamatan": "TANJUNG RAJA"
						},
						{
							"id": 1373,
							"namakecamatan": "TANJUNG RAJA"
						},
						{
							"id": 906,
							"namakecamatan": "TANJUNG RAYA"
						},
						{
							"id": 1864,
							"namakecamatan": "TANJUNG RAYA"
						},
						{
							"id": 1977,
							"namakecamatan": "TANJUNG RAYA"
						},
						{
							"id": 5392,
							"namakecamatan": "TANJUNG REDEB"
						},
						{
							"id": 1439,
							"namakecamatan": "TANJUNG SAKTI PUMI"
						},
						{
							"id": 1416,
							"namakecamatan": "TANJUNGSAKTI PUMU"
						},
						{
							"id": 2585,
							"namakecamatan": "TANJUNGSARI"
						},
						{
							"id": 3465,
							"namakecamatan": "TANJUNGSARI"
						},
						{
							"id": 2230,
							"namakecamatan": "TANJUNGSARI"
						},
						{
							"id": 1775,
							"namakecamatan": "TANJUNG SARI"
						},
						{
							"id": 5472,
							"namakecamatan": "TANJUNG SELOR"
						},
						{
							"id": 2007,
							"namakecamatan": "TANJUNG SENANG"
						},
						{
							"id": 2645,
							"namakecamatan": "TANJUNGSIANG"
						},
						{
							"id": 1442,
							"namakecamatan": "TANJUNGTEBAT"
						},
						{
							"id": 518,
							"namakecamatan": "TANJUNG TIRAM"
						},
						{
							"id": 711,
							"namakecamatan": "TANJUNG TIRAM"
						},
						{
							"id": 33,
							"namakecamatan": "TANOH ALAS"
						},
						{
							"id": 3123,
							"namakecamatan": "TANON"
						},
						{
							"id": 374,
							"namakecamatan": "TANO TOMBANGANANGKOLA"
						},
						{
							"id": 6045,
							"namakecamatan": "TANRALILI"
						},
						{
							"id": 5321,
							"namakecamatan": "TANTA"
						},
						{
							"id": 5874,
							"namakecamatan": "TAOPA"
						},
						{
							"id": 6575,
							"namakecamatan": "TAPA"
						},
						{
							"id": 8,
							"namakecamatan": "TAPAKTUAN"
						},
						{
							"id": 6639,
							"namakecamatan": "TAPALANG"
						},
						{
							"id": 6650,
							"namakecamatan": "TAPALANG BARAT"
						},
						{
							"id": 6677,
							"namakecamatan": "TAPANGO"
						},
						{
							"id": 3730,
							"namakecamatan": "TAPEN"
						},
						{
							"id": 511,
							"namakecamatan": "TAPIAN DOLOK"
						},
						{
							"id": 316,
							"namakecamatan": "TAPIAN NAULI"
						},
						{
							"id": 5276,
							"namakecamatan": "TAPIN SELATAN"
						},
						{
							"id": 5277,
							"namakecamatan": "TAPIN TENGAH"
						},
						{
							"id": 5278,
							"namakecamatan": "TAPIN UTARA"
						},
						{
							"id": 2827,
							"namakecamatan": "TAPOS"
						},
						{
							"id": 1031,
							"namakecamatan": "TAPUNG"
						},
						{
							"id": 1032,
							"namakecamatan": "TAPUNG HILIR"
						},
						{
							"id": 1033,
							"namakecamatan": "TAPUNG HULU"
						},
						{
							"id": 679,
							"namakecamatan": "TARABINTANG"
						},
						{
							"id": 2414,
							"namakecamatan": "TARAJU"
						},
						{
							"id": 5514,
							"namakecamatan": "TARAKAN BARAT"
						},
						{
							"id": 5515,
							"namakecamatan": "TARAKAN TENGAH"
						},
						{
							"id": 5516,
							"namakecamatan": "TARAKAN TIMUR"
						},
						{
							"id": 5517,
							"namakecamatan": "TARAKAN UTARA"
						},
						{
							"id": 4458,
							"namakecamatan": "TARANO"
						},
						{
							"id": 5634,
							"namakecamatan": "TARERAN"
						},
						{
							"id": 3809,
							"namakecamatan": "TARIK"
						},
						{
							"id": 2363,
							"namakecamatan": "TAROGONG KALER"
						},
						{
							"id": 2364,
							"namakecamatan": "TAROGONG KIDUL"
						},
						{
							"id": 3605,
							"namakecamatan": "TAROKAN"
						},
						{
							"id": 5975,
							"namakecamatan": "TAROWANG"
						},
						{
							"id": 3362,
							"namakecamatan": "TARUB"
						},
						{
							"id": 2709,
							"namakecamatan": "TARUMAJAYA"
						},
						{
							"id": 7272,
							"namakecamatan": "TARUP"
						},
						{
							"id": 330,
							"namakecamatan": "TARUTUNG"
						},
						{
							"id": 4602,
							"namakecamatan": "TASIFETO BARAT"
						},
						{
							"id": 4600,
							"namakecamatan": "TASIFETOTIMUR"
						},
						{
							"id": 3104,
							"namakecamatan": "TASIKMADU"
						},
						{
							"id": 5132,
							"namakecamatan": "TASIK PAYAWAN"
						},
						{
							"id": 1169,
							"namakecamatan": "TASIK PUTRI PUYU"
						},
						{
							"id": 5257,
							"namakecamatan": "TATAH MAKMUR"
						},
						{
							"id": 5933,
							"namakecamatan": "TATANGA"
						},
						{
							"id": 5640,
							"namakecamatan": "TATAPAAN"
						},
						{
							"id": 5588,
							"namakecamatan": "TATOARENG"
						},
						{
							"id": 5934,
							"namakecamatan": "TAWAELI"
						},
						{
							"id": 6667,
							"namakecamatan": "TAWALIAN"
						},
						{
							"id": 2834,
							"namakecamatan": "TAWANG"
						},
						{
							"id": 3142,
							"namakecamatan": "TAWANGHARJO"
						},
						{
							"id": 3100,
							"namakecamatan": "TAWANGMANGU"
						},
						{
							"id": 3060,
							"namakecamatan": "TAWANGSARI"
						},
						{
							"id": 6735,
							"namakecamatan": "TAYANDO TAM"
						},
						{
							"id": 6844,
							"namakecamatan": "TAYANDO TAM"
						},
						{
							"id": 4910,
							"namakecamatan": "TAYAN HILIR"
						},
						{
							"id": 4909,
							"namakecamatan": "TAYAN HULU"
						},
						{
							"id": 3199,
							"namakecamatan": "TAYU"
						},
						{
							"id": 4866,
							"namakecamatan": "TEBAS"
						},
						{
							"id": 1729,
							"namakecamatan": "TEBAT KARAI"
						},
						{
							"id": 1635,
							"namakecamatan": "TEBAT KARAI"
						},
						{
							"id": 2175,
							"namakecamatan": "TEBET"
						},
						{
							"id": 2088,
							"namakecamatan": "TEBING"
						},
						{
							"id": 704,
							"namakecamatan": "TEBING SYAHBANDAR"
						},
						{
							"id": 464,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 1420,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 1557,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 1269,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 1162,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 701,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 1061,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 5348,
							"namakecamatan": "TEBING TINGGI"
						},
						{
							"id": 1062,
							"namakecamatan": "TEBING TINGGI BARAT"
						},
						{
							"id": 1165,
							"namakecamatan": "TEBING TINGGI BARAT"
						},
						{
							"id": 814,
							"namakecamatan": "TEBING TINGGI KOTA"
						},
						{
							"id": 1168,
							"namakecamatan": "TEBING TINGGI TIMUR"
						},
						{
							"id": 1306,
							"namakecamatan": "TEBO ILIR"
						},
						{
							"id": 1305,
							"namakecamatan": "TEBO TENGAH"
						},
						{
							"id": 1307,
							"namakecamatan": "TEBO ULU"
						},
						{
							"id": 4356,
							"namakecamatan": "TEGALALLANG"
						},
						{
							"id": 3733,
							"namakecamatan": "TEGALAMPEL"
						},
						{
							"id": 3416,
							"namakecamatan": "TEGAL BARAT"
						},
						{
							"id": 2279,
							"namakecamatan": "TEGAL BULEUD"
						},
						{
							"id": 3700,
							"namakecamatan": "TEGALDLIMO"
						},
						{
							"id": 3506,
							"namakecamatan": "TEGALOMBO"
						},
						{
							"id": 3484,
							"namakecamatan": "TEGALREJO"
						},
						{
							"id": 3010,
							"namakecamatan": "TEGALREJO"
						},
						{
							"id": 4132,
							"namakecamatan": "TEGALSARI"
						},
						{
							"id": 3719,
							"namakecamatan": "TEGALSARI"
						},
						{
							"id": 3418,
							"namakecamatan": "TEGAL SELATAN"
						},
						{
							"id": 3780,
							"namakecamatan": "TEGALSIWALAN"
						},
						{
							"id": 3417,
							"namakecamatan": "TEGAL TIMUR"
						},
						{
							"id": 2706,
							"namakecamatan": "TEGALWARU"
						},
						{
							"id": 2669,
							"namakecamatan": "TEGALWARU"
						},
						{
							"id": 1953,
							"namakecamatan": "TEGINENENG"
						},
						{
							"id": 1765,
							"namakecamatan": "TEGINENENG"
						},
						{
							"id": 3149,
							"namakecamatan": "TEGOWANU"
						},
						{
							"id": 6710,
							"namakecamatan": "TEHORU"
						},
						{
							"id": 7284,
							"namakecamatan": "TEIRAPLU"
						},
						{
							"id": 4382,
							"namakecamatan": "TEJAKULA"
						},
						{
							"id": 4874,
							"namakecamatan": "TEKARANG"
						},
						{
							"id": 3653,
							"namakecamatan": "TEKUNG"
						},
						{
							"id": 6545,
							"namakecamatan": "TELAGA"
						},
						{
							"id": 5089,
							"namakecamatan": "TELAGA ANTANG"
						},
						{
							"id": 5256,
							"namakecamatan": "TELAGA BAUNTUNG"
						},
						{
							"id": 6553,
							"namakecamatan": "TELAGA BIRU"
						},
						{
							"id": 5289,
							"namakecamatan": "TELAGA LANGSAT"
						},
						{
							"id": 6382,
							"namakecamatan": "TELAGA RAYA"
						},
						{
							"id": 2695,
							"namakecamatan": "TELAGASARI"
						},
						{
							"id": 1317,
							"namakecamatan": "TELANAIPURA"
						},
						{
							"id": 5086,
							"namakecamatan": "TELAWANG"
						},
						{
							"id": 5428,
							"namakecamatan": "TELEN"
						},
						{
							"id": 7376,
							"namakecamatan": "TELENGGEME"
						},
						{
							"id": 6036,
							"namakecamatan": "TELLULIMPOE"
						},
						{
							"id": 6096,
							"namakecamatan": "TELLU LIMPOE"
						},
						{
							"id": 6010,
							"namakecamatan": "TELLU LIMPOE"
						},
						{
							"id": 6028,
							"namakecamatan": "TELLU SIATTINGE"
						},
						{
							"id": 6257,
							"namakecamatan": "TELLUWANUA"
						},
						{
							"id": 4936,
							"namakecamatan": "TELOK BATANG"
						},
						{
							"id": 2078,
							"namakecamatan": "TELOK SEBONG"
						},
						{
							"id": 6840,
							"namakecamatan": "TELUK AMBON"
						},
						{
							"id": 7115,
							"namakecamatan": "TELUK AMPIMOI"
						},
						{
							"id": 7794,
							"namakecamatan": "TELUK ARGUNI ATAS"
						},
						{
							"id": 7797,
							"namakecamatan": "TELUK ARGUNI BAWAH"
						},
						{
							"id": 5043,
							"namakecamatan": "TELUK BATANG"
						},
						{
							"id": 5396,
							"namakecamatan": "TELUK BAYUR"
						},
						{
							"id": 1085,
							"namakecamatan": "TELUK BELENGKONG"
						},
						{
							"id": 2004,
							"namakecamatan": "TELUKBETUNG BARAT"
						},
						{
							"id": 2003,
							"namakecamatan": "TELUKBETUNG SELATAN"
						},
						{
							"id": 2015,
							"namakecamatan": "TELUKBETUNG TIMUR"
						},
						{
							"id": 2005,
							"namakecamatan": "TELUKBETUNG UTARA"
						},
						{
							"id": 2076,
							"namakecamatan": "TELUK BINTAN"
						},
						{
							"id": 545,
							"namakecamatan": "TELUK DALAM"
						},
						{
							"id": 177,
							"namakecamatan": "TELUK DALAM"
						},
						{
							"id": 636,
							"namakecamatan": "TELUK DALAM"
						},
						{
							"id": 7202,
							"namakecamatan": "TELUK DEYA"
						},
						{
							"id": 7781,
							"namakecamatan": "TELUK DUAIRI"
						},
						{
							"id": 6720,
							"namakecamatan": "TELUK ELPAPUTIH"
						},
						{
							"id": 7795,
							"namakecamatan": "TELUK ETNA"
						},
						{
							"id": 1389,
							"namakecamatan": "TELUK GELAM"
						},
						{
							"id": 2705,
							"namakecamatan": "TELUKJAMBE BARAT"
						},
						{
							"id": 2681,
							"namakecamatan": "TELUKJAMBE TIMUR"
						},
						{
							"id": 6776,
							"namakecamatan": "TELUK KAIELY"
						},
						{
							"id": 4864,
							"namakecamatan": "TELUK KERAMAT"
						},
						{
							"id": 7097,
							"namakecamatan": "TELUK KIMI"
						},
						{
							"id": 7738,
							"namakecamatan": "TELUK MAYALIBIT"
						},
						{
							"id": 467,
							"namakecamatan": "TELUK MENGKUDU"
						},
						{
							"id": 691,
							"namakecamatan": "TELUK MENGKUDU"
						},
						{
							"id": 1098,
							"namakecamatan": "TELUK MERANTI"
						},
						{
							"id": 4623,
							"namakecamatan": "TELUK MUTIARA"
						},
						{
							"id": 4237,
							"namakecamatan": "TELUKNAGA"
						},
						{
							"id": 802,
							"namakecamatan": "TELUK NIBUNG"
						},
						{
							"id": 5054,
							"namakecamatan": "TELUK PAKEDAI"
						},
						{
							"id": 4884,
							"namakecamatan": "TELUK PAKEDAI"
						},
						{
							"id": 5434,
							"namakecamatan": "TELUK PANDAN"
						},
						{
							"id": 1960,
							"namakecamatan": "TELUK PANDAN"
						},
						{
							"id": 7695,
							"namakecamatan": "TELUK PATIPI"
						},
						{
							"id": 5083,
							"namakecamatan": "TELUK SAMPIT"
						},
						{
							"id": 1747,
							"namakecamatan": "TELUK SEGARA"
						},
						{
							"id": 7093,
							"namakecamatan": "TELUK UMAR"
						},
						{
							"id": 6792,
							"namakecamatan": "TELUK WARU"
						},
						{
							"id": 6722,
							"namakecamatan": "TELUTIH"
						},
						{
							"id": 3263,
							"namakecamatan": "TEMANGGUNG"
						},
						{
							"id": 3958,
							"namakecamatan": "TEMAYANG"
						},
						{
							"id": 7219,
							"namakecamatan": "TEMBAGAPURA"
						},
						{
							"id": 3405,
							"namakecamatan": "TEMBALANG"
						},
						{
							"id": 3262,
							"namakecamatan": "TEMBARAK"
						},
						{
							"id": 3857,
							"namakecamatan": "TEMBELANG"
						},
						{
							"id": 1073,
							"namakecamatan": "TEMBILAHAN"
						},
						{
							"id": 1082,
							"namakecamatan": "TEMBILAHAN HULU"
						},
						{
							"id": 4364,
							"namakecamatan": "TEMBUKU"
						},
						{
							"id": 7763,
							"namakecamatan": "TEMBUNI"
						},
						{
							"id": 7704,
							"namakecamatan": "TEMINABUAN"
						},
						{
							"id": 3420,
							"namakecamatan": "TEMON"
						},
						{
							"id": 6086,
							"namakecamatan": "TEMPE"
						},
						{
							"id": 3649,
							"namakecamatan": "TEMPEH"
						},
						{
							"id": 3480,
							"namakecamatan": "TEMPEL"
						},
						{
							"id": 2053,
							"namakecamatan": "TEMPILANG"
						},
						{
							"id": 1074,
							"namakecamatan": "TEMPULING"
						},
						{
							"id": 4948,
							"namakecamatan": "TEMPUNAK"
						},
						{
							"id": 2698,
							"namakecamatan": "TEMPURAN"
						},
						{
							"id": 3002,
							"namakecamatan": "TEMPURAN"
						},
						{
							"id": 3683,
							"namakecamatan": "TEMPUREJO"
						},
						{
							"id": 3645,
							"namakecamatan": "TEMPURSARI"
						},
						{
							"id": 1180,
							"namakecamatan": "TENAYAN RAYA"
						},
						{
							"id": 5630,
							"namakecamatan": "TENGA"
						},
						{
							"id": 1313,
							"namakecamatan": "TENGAH ILIR"
						},
						{
							"id": 2543,
							"namakecamatan": "TENGAH TANI"
						},
						{
							"id": 3242,
							"namakecamatan": "TENGARAN"
						},
						{
							"id": 3728,
							"namakecamatan": "TENGGARANG"
						},
						{
							"id": 5375,
							"namakecamatan": "TENGGARONG"
						},
						{
							"id": 5385,
							"namakecamatan": "TENGGARONG SEBERANG"
						},
						{
							"id": 4151,
							"namakecamatan": "TENGGILIS MEJOYO"
						},
						{
							"id": 265,
							"namakecamatan": "TENGGULUN"
						},
						{
							"id": 2217,
							"namakecamatan": "TENJO"
						},
						{
							"id": 2234,
							"namakecamatan": "TENJOLAYA"
						},
						{
							"id": 6701,
							"namakecamatan": "TEON NILA SERUA"
						},
						{
							"id": 6787,
							"namakecamatan": "TEOR"
						},
						{
							"id": 3455,
							"namakecamatan": "TEPUS"
						},
						{
							"id": 1707,
							"namakecamatan": "TERAMANG JAYA"
						},
						{
							"id": 228,
							"namakecamatan": "TERANGUN"
						},
						{
							"id": 4416,
							"namakecamatan": "TERARA"
						},
						{
							"id": 3019,
							"namakecamatan": "TERAS"
						},
						{
							"id": 1702,
							"namakecamatan": "TERAS TERUNJAM"
						},
						{
							"id": 1784,
							"namakecamatan": "TERBANGGI BESAR"
						},
						{
							"id": 5050,
							"namakecamatan": "TERENTANG"
						},
						{
							"id": 4886,
							"namakecamatan": "TERENTANG"
						},
						{
							"id": 5001,
							"namakecamatan": "TERIAK"
						},
						{
							"id": 5419,
							"namakecamatan": "TERING"
						},
						{
							"id": 234,
							"namakecamatan": "TERIPE JAYA"
						},
						{
							"id": 3306,
							"namakecamatan": "TERSONO"
						},
						{
							"id": 1790,
							"namakecamatan": "TERUSAN NUNYAI"
						},
						{
							"id": 7865,
							"namakecamatan": "TESTEGA"
						},
						{
							"id": 7672,
							"namakecamatan": "TESTEGA"
						},
						{
							"id": 1682,
							"namakecamatan": "TETAP"
						},
						{
							"id": 236,
							"namakecamatan": "TEUNOM"
						},
						{
							"id": 175,
							"namakecamatan": "TEUPAH BARAT"
						},
						{
							"id": 179,
							"namakecamatan": "TEUPAH SELATAN"
						},
						{
							"id": 181,
							"namakecamatan": "TEUPAH TENGAH"
						},
						{
							"id": 5164,
							"namakecamatan": "TEWAH"
						},
						{
							"id": 5124,
							"namakecamatan": "TEWANG SANGALANGGARING"
						},
						{
							"id": 5119,
							"namakecamatan": "TEWEH BARU"
						},
						{
							"id": 5120,
							"namakecamatan": "TEWEH SELATAN"
						},
						{
							"id": 5117,
							"namakecamatan": "TEWEH TENGAH"
						},
						{
							"id": 5116,
							"namakecamatan": "TEWEH TIMUR"
						},
						{
							"id": 1234,
							"namakecamatan": "TIANG PUMPUNG"
						},
						{
							"id": 1462,
							"namakecamatan": "TIANG PUMPUNGKEPUNGUT"
						},
						{
							"id": 6547,
							"namakecamatan": "TIBAWA"
						},
						{
							"id": 6967,
							"namakecamatan": "TIDORE"
						},
						{
							"id": 6970,
							"namakecamatan": "TIDORE SELATAN"
						},
						{
							"id": 6974,
							"namakecamatan": "TIDORE TIMUR"
						},
						{
							"id": 6971,
							"namakecamatan": "TIDORE UTARA"
						},
						{
							"id": 441,
							"namakecamatan": "TIGABINANGA"
						},
						{
							"id": 1536,
							"namakecamatan": "TIGA DIHAJI"
						},
						{
							"id": 571,
							"namakecamatan": "TIGALINGGA"
						},
						{
							"id": 450,
							"namakecamatan": "TIGANDERKET"
						},
						{
							"id": 437,
							"namakecamatan": "TIGAPANAH"
						},
						{
							"id": 4227,
							"namakecamatan": "TIGARAKSA"
						},
						{
							"id": 7593,
							"namakecamatan": "TIGI"
						},
						{
							"id": 7178,
							"namakecamatan": "TIGI"
						},
						{
							"id": 7596,
							"namakecamatan": "TIGI BARAT"
						},
						{
							"id": 7193,
							"namakecamatan": "TIGI BARAT"
						},
						{
							"id": 7594,
							"namakecamatan": "TIGI TIMUR"
						},
						{
							"id": 7185,
							"namakecamatan": "TIGI TIMUR"
						},
						{
							"id": 859,
							"namakecamatan": "TIGO LURAH"
						},
						{
							"id": 945,
							"namakecamatan": "TIGO NAGARI"
						},
						{
							"id": 5697,
							"namakecamatan": "TIKALA"
						},
						{
							"id": 6173,
							"namakecamatan": "TIKALA"
						},
						{
							"id": 6223,
							"namakecamatan": "TIKALA"
						},
						{
							"id": 6633,
							"namakecamatan": "TIKKE RAYA"
						},
						{
							"id": 4008,
							"namakecamatan": "TIKUNG"
						},
						{
							"id": 6571,
							"namakecamatan": "TILAMUTA"
						},
						{
							"id": 6561,
							"namakecamatan": "TILANGO"
						},
						{
							"id": 912,
							"namakecamatan": "TILATANG KAMANG"
						},
						{
							"id": 5818,
							"namakecamatan": "TILOAN"
						},
						{
							"id": 6580,
							"namakecamatan": "TILONGKABILA"
						},
						{
							"id": 63,
							"namakecamatan": "TIMANG GAJAH"
						},
						{
							"id": 273,
							"namakecamatan": "TIMANG GAJAH"
						},
						{
							"id": 7363,
							"namakecamatan": "TIMORI"
						},
						{
							"id": 5099,
							"namakecamatan": "TIMPAH"
						},
						{
							"id": 967,
							"namakecamatan": "TIMPEH"
						},
						{
							"id": 668,
							"namakecamatan": "TINADA"
						},
						{
							"id": 6671,
							"namakecamatan": "TINAMBUNG"
						},
						{
							"id": 6396,
							"namakecamatan": "TINANGGEA"
						},
						{
							"id": 5845,
							"namakecamatan": "TINANGKUNG"
						},
						{
							"id": 5852,
							"namakecamatan": "TINANGKUNG SELATAN"
						},
						{
							"id": 5860,
							"namakecamatan": "TINANGKUNG UTARA"
						},
						{
							"id": 5988,
							"namakecamatan": "TINGGIMONCONG"
						},
						{
							"id": 7153,
							"namakecamatan": "TINGGINAMBUT"
						},
						{
							"id": 540,
							"namakecamatan": "TINGGI RAJA"
						},
						{
							"id": 7818,
							"namakecamatan": "TINGGOUW"
						},
						{
							"id": 3393,
							"namakecamatan": "TINGKIR"
						},
						{
							"id": 5863,
							"namakecamatan": "TINOMBO"
						},
						{
							"id": 5870,
							"namakecamatan": "TINOMBO SELATAN"
						},
						{
							"id": 6283,
							"namakecamatan": "TINONDO"
						},
						{
							"id": 6490,
							"namakecamatan": "TINONDO"
						},
						{
							"id": 7479,
							"namakecamatan": "TIOM"
						},
						{
							"id": 6996,
							"namakecamatan": "TIOM"
						},
						{
							"id": 7012,
							"namakecamatan": "TIOMNERI"
						},
						{
							"id": 7486,
							"namakecamatan": "TIOMNERI"
						},
						{
							"id": 7492,
							"namakecamatan": "TIOM OLLO"
						},
						{
							"id": 7748,
							"namakecamatan": "TIPLOL MAYALIBIT"
						},
						{
							"id": 6482,
							"namakecamatan": "TIRAWUTA"
						},
						{
							"id": 6264,
							"namakecamatan": "TIRAWUTA"
						},
						{
							"id": 3767,
							"namakecamatan": "TIRIS"
						},
						{
							"id": 6114,
							"namakecamatan": "TIROANG"
						},
						{
							"id": 135,
							"namakecamatan": "TIRO/TRUSEB"
						},
						{
							"id": 2687,
							"namakecamatan": "TIRTAJAYA"
						},
						{
							"id": 2694,
							"namakecamatan": "TIRTAMULYA"
						},
						{
							"id": 4273,
							"namakecamatan": "TIRTAYASA"
						},
						{
							"id": 3330,
							"namakecamatan": "TIRTO"
						},
						{
							"id": 3074,
							"namakecamatan": "TIRTOMOYO"
						},
						{
							"id": 3641,
							"namakecamatan": "TIRTOYUDO"
						},
						{
							"id": 4641,
							"namakecamatan": "TITEHENA"
						},
						{
							"id": 145,
							"namakecamatan": "TITEUE"
						},
						{
							"id": 969,
							"namakecamatan": "TIUMANG"
						},
						{
							"id": 6329,
							"namakecamatan": "TIWORO KEPULAUAN"
						},
						{
							"id": 6509,
							"namakecamatan": "TIWORO KEPULAUAN"
						},
						{
							"id": 6505,
							"namakecamatan": "TIWORO SELATAN"
						},
						{
							"id": 6363,
							"namakecamatan": "TIWORO SELATAN"
						},
						{
							"id": 6328,
							"namakecamatan": "TIWORO TENGAH"
						},
						{
							"id": 6507,
							"namakecamatan": "TIWORO TENGAH"
						},
						{
							"id": 6508,
							"namakecamatan": "TIWORO UTARA"
						},
						{
							"id": 6364,
							"namakecamatan": "TIWORO UTARA"
						},
						{
							"id": 6461,
							"namakecamatan": "TIWU"
						},
						{
							"id": 7436,
							"namakecamatan": "TI ZAIN"
						},
						{
							"id": 159,
							"namakecamatan": "T. JAMBO AYE"
						},
						{
							"id": 4063,
							"namakecamatan": "TLANAKAN"
						},
						{
							"id": 3274,
							"namakecamatan": "TLOGOMULYO"
						},
						{
							"id": 3723,
							"namakecamatan": "TLOGOSARI"
						},
						{
							"id": 3194,
							"namakecamatan": "TLOGOWUNGU"
						},
						{
							"id": 2080,
							"namakecamatan": "TOAPAYA"
						},
						{
							"id": 6286,
							"namakecamatan": "TOARI"
						},
						{
							"id": 4912,
							"namakecamatan": "TOBA"
						},
						{
							"id": 6695,
							"namakecamatan": "TOBADAK"
						},
						{
							"id": 6651,
							"namakecamatan": "TOBADAK"
						},
						{
							"id": 6870,
							"namakecamatan": "TOBELO"
						},
						{
							"id": 6878,
							"namakecamatan": "TOBELO BARAT"
						},
						{
							"id": 6871,
							"namakecamatan": "TOBELO SELATAN"
						},
						{
							"id": 6876,
							"namakecamatan": "TOBELO TENGAH"
						},
						{
							"id": 6877,
							"namakecamatan": "TOBELO TIMUR"
						},
						{
							"id": 6875,
							"namakecamatan": "TOBELO UTARA"
						},
						{
							"id": 2035,
							"namakecamatan": "TOBOALI"
						},
						{
							"id": 7816,
							"namakecamatan": "TOBOUW"
						},
						{
							"id": 4573,
							"namakecamatan": "TOBU"
						},
						{
							"id": 3164,
							"namakecamatan": "TODANAN"
						},
						{
							"id": 5885,
							"namakecamatan": "TOGEAN"
						},
						{
							"id": 5754,
							"namakecamatan": "TOGIAN"
						},
						{
							"id": 6447,
							"namakecamatan": "TOGO BINONGKO"
						},
						{
							"id": 4887,
							"namakecamatan": "TOHO"
						},
						{
							"id": 7676,
							"namakecamatan": "TOHOTA"
						},
						{
							"id": 4558,
							"namakecamatan": "TOIANAS"
						},
						{
							"id": 5729,
							"namakecamatan": "TOILI"
						},
						{
							"id": 5732,
							"namakecamatan": "TOILI BARAT"
						},
						{
							"id": 5891,
							"namakecamatan": "TOJO"
						},
						{
							"id": 5760,
							"namakecamatan": "TOJO"
						},
						{
							"id": 5759,
							"namakecamatan": "TOJO BARAT"
						},
						{
							"id": 5890,
							"namakecamatan": "TOJO BARAT"
						},
						{
							"id": 6460,
							"namakecamatan": "TOLALA"
						},
						{
							"id": 6556,
							"namakecamatan": "TOLANGOHULA"
						},
						{
							"id": 6610,
							"namakecamatan": "TOLINGGULA"
						},
						{
							"id": 6555,
							"namakecamatan": "TOLINGGULA"
						},
						{
							"id": 5810,
							"namakecamatan": "TOLI-TOLI UTARA"
						},
						{
							"id": 5627,
							"namakecamatan": "TOLUAAN"
						},
						{
							"id": 642,
							"namakecamatan": "TOMA"
						},
						{
							"id": 7703,
							"namakecamatan": "TOMAGE"
						},
						{
							"id": 5567,
							"namakecamatan": "TOMBARIRI"
						},
						{
							"id": 5576,
							"namakecamatan": "TOMBARIRI TIMUR"
						},
						{
							"id": 5659,
							"namakecamatan": "TOMBATU"
						},
						{
							"id": 5626,
							"namakecamatan": "TOMBATU"
						},
						{
							"id": 5663,
							"namakecamatan": "TOMBATU TIMUR"
						},
						{
							"id": 5664,
							"namakecamatan": "TOMBATU UTARA"
						},
						{
							"id": 5994,
							"namakecamatan": "TOMBOLOPAO"
						},
						{
							"id": 5566,
							"namakecamatan": "TOMBULU"
						},
						{
							"id": 6378,
							"namakecamatan": "TOMIA"
						},
						{
							"id": 6442,
							"namakecamatan": "TOMIA"
						},
						{
							"id": 6446,
							"namakecamatan": "TOMIA TIMUR"
						},
						{
							"id": 5865,
							"namakecamatan": "TOMINI"
						},
						{
							"id": 6648,
							"namakecamatan": "TOMMO"
						},
						{
							"id": 2598,
							"namakecamatan": "TOMO"
						},
						{
							"id": 5715,
							"namakecamatan": "TOMOHON BARAT"
						},
						{
							"id": 5712,
							"namakecamatan": "TOMOHON SELATAN"
						},
						{
							"id": 5713,
							"namakecamatan": "TOMOHON TENGAH"
						},
						{
							"id": 5716,
							"namakecamatan": "TOMOHON TIMUR"
						},
						{
							"id": 5714,
							"namakecamatan": "TOMOHON UTARA"
						},
						{
							"id": 6612,
							"namakecamatan": "TOMOLITO"
						},
						{
							"id": 6211,
							"namakecamatan": "TOMONI"
						},
						{
							"id": 6212,
							"namakecamatan": "TOMONI TIMUR"
						},
						{
							"id": 7592,
							"namakecamatan": "TOMOSIGA"
						},
						{
							"id": 5559,
							"namakecamatan": "TOMPASO"
						},
						{
							"id": 5577,
							"namakecamatan": "TOMPASO BARAT"
						},
						{
							"id": 5623,
							"namakecamatan": "TOMPASO BARU"
						},
						{
							"id": 5987,
							"namakecamatan": "TOMPOBULLU"
						},
						{
							"id": 6049,
							"namakecamatan": "TOMPOBULU"
						},
						{
							"id": 5960,
							"namakecamatan": "TOMPO BULU"
						},
						{
							"id": 7773,
							"namakecamatan": "TOMU"
						},
						{
							"id": 5553,
							"namakecamatan": "TONDANO BARAT"
						},
						{
							"id": 5570,
							"namakecamatan": "TONDANO SELATAN"
						},
						{
							"id": 5554,
							"namakecamatan": "TONDANO TIMUR"
						},
						{
							"id": 5568,
							"namakecamatan": "TONDANO UTARA"
						},
						{
							"id": 6230,
							"namakecamatan": "TONDON"
						},
						{
							"id": 6167,
							"namakecamatan": "TONDON"
						},
						{
							"id": 6064,
							"namakecamatan": "TONDONG TALLASA"
						},
						{
							"id": 3783,
							"namakecamatan": "TONGAS"
						},
						{
							"id": 6304,
							"namakecamatan": "TONGAUNA"
						},
						{
							"id": 6352,
							"namakecamatan": "TONGKUNO"
						},
						{
							"id": 6357,
							"namakecamatan": "TONGKUNO SELATAN"
						},
						{
							"id": 3372,
							"namakecamatan": "TONJONG"
						},
						{
							"id": 6016,
							"namakecamatan": "TONRA"
						},
						{
							"id": 6437,
							"namakecamatan": "TONTONUNU"
						},
						{
							"id": 7209,
							"namakecamatan": "TOPIYAI"
						},
						{
							"id": 1720,
							"namakecamatan": "TOPOS"
						},
						{
							"id": 6698,
							"namakecamatan": "TOPOYO"
						},
						{
							"id": 6646,
							"namakecamatan": "TOPOYO"
						},
						{
							"id": 7229,
							"namakecamatan": "TOR ATAS"
						},
						{
							"id": 7152,
							"namakecamatan": "TORERE"
						},
						{
							"id": 736,
							"namakecamatan": "TORGAMBA"
						},
						{
							"id": 559,
							"namakecamatan": "TORGAMBA"
						},
						{
							"id": 5873,
							"namakecamatan": "TORIBULU"
						},
						{
							"id": 4050,
							"namakecamatan": "TORJUN"
						},
						{
							"id": 3135,
							"namakecamatan": "TOROH"
						},
						{
							"id": 5869,
							"namakecamatan": "TORUE"
						},
						{
							"id": 3808,
							"namakecamatan": "TOSARI"
						},
						{
							"id": 5844,
							"namakecamatan": "TOTIKUM"
						},
						{
							"id": 5856,
							"namakecamatan": "TOTIKUM SELATAN"
						},
						{
							"id": 5660,
							"namakecamatan": "TOULUAAN"
						},
						{
							"id": 5661,
							"namakecamatan": "TOULUAAN SELATAN"
						},
						{
							"id": 7249,
							"namakecamatan": "TOWE"
						},
						{
							"id": 6362,
							"namakecamatan": "TOWEA"
						},
						{
							"id": 6206,
							"namakecamatan": "TOWUTI"
						},
						{
							"id": 4044,
							"namakecamatan": "TRAGAH"
						},
						{
							"id": 3201,
							"namakecamatan": "TRANGKIL"
						},
						{
							"id": 3830,
							"namakecamatan": "TRAWAS"
						},
						{
							"id": 3541,
							"namakecamatan": "TRENGGALEK"
						},
						{
							"id": 3271,
							"namakecamatan": "TRETEP"
						},
						{
							"id": 284,
							"namakecamatan": "TRIENGGADENG"
						},
						{
							"id": 134,
							"namakecamatan": "TRIENG GADENG"
						},
						{
							"id": 7034,
							"namakecamatan": "TRIKORA"
						},
						{
							"id": 1782,
							"namakecamatan": "TRIMURJO"
						},
						{
							"id": 253,
							"namakecamatan": "TRIPA MAKMUR"
						},
						{
							"id": 2626,
							"namakecamatan": "TRISI"
						},
						{
							"id": 3838,
							"namakecamatan": "TROWULAN"
						},
						{
							"id": 3960,
							"namakecamatan": "TRUCUK"
						},
						{
							"id": 3037,
							"namakecamatan": "TRUCUK"
						},
						{
							"id": 9,
							"namakecamatan": "TRUMON"
						},
						{
							"id": 18,
							"namakecamatan": "TRUMON TENGAH"
						},
						{
							"id": 16,
							"namakecamatan": "TRUMON TIMUR"
						},
						{
							"id": 1464,
							"namakecamatan": "TUAH NEGERI"
						},
						{
							"id": 1136,
							"namakecamatan": "TUALANG"
						},
						{
							"id": 5088,
							"namakecamatan": "TUALAN HULU"
						},
						{
							"id": 3981,
							"namakecamatan": "TUBAN"
						},
						{
							"id": 6989,
							"namakecamatan": "TUBANG"
						},
						{
							"id": 6693,
							"namakecamatan": "TUBO SENDANA"
						},
						{
							"id": 754,
							"namakecamatan": "TUGALA OYO"
						},
						{
							"id": 408,
							"namakecamatan": "TUGALA OYO"
						},
						{
							"id": 3411,
							"namakecamatan": "TUGU"
						},
						{
							"id": 3535,
							"namakecamatan": "TUGU"
						},
						{
							"id": 1445,
							"namakecamatan": "TUGUMULYO"
						},
						{
							"id": 749,
							"namakecamatan": "TUHEMBERUA"
						},
						{
							"id": 377,
							"namakecamatan": "TUHEMBERUA"
						},
						{
							"id": 7766,
							"namakecamatan": "TUHIBA"
						},
						{
							"id": 5009,
							"namakecamatan": "TUJUH BELAS"
						},
						{
							"id": 2040,
							"namakecamatan": "TUKAK SADAI"
						},
						{
							"id": 2630,
							"namakecamatan": "TUKDANA"
						},
						{
							"id": 323,
							"namakecamatan": "TUKKA"
						},
						{
							"id": 3507,
							"namakecamatan": "TULAKAN"
						},
						{
							"id": 3817,
							"namakecamatan": "TULANGAN"
						},
						{
							"id": 1978,
							"namakecamatan": "TULANG BAWANG TENGAH"
						},
						{
							"id": 1857,
							"namakecamatan": "TULANG BAWANG TENGAH"
						},
						{
							"id": 1858,
							"namakecamatan": "TULANG BAWANG UDIK"
						},
						{
							"id": 1980,
							"namakecamatan": "TULANG BAWANG UDIK"
						},
						{
							"id": 5506,
							"namakecamatan": "TULIN ONSOI"
						},
						{
							"id": 3310,
							"namakecamatan": "TULIS"
						},
						{
							"id": 3050,
							"namakecamatan": "TULUNG"
						},
						{
							"id": 3545,
							"namakecamatan": "TULUNGAGUNG"
						},
						{
							"id": 1377,
							"namakecamatan": "TULUNG SELAPAN"
						},
						{
							"id": 4934,
							"namakecamatan": "TUMBANG TITI"
						},
						{
							"id": 1870,
							"namakecamatan": "TUMIJAJAR"
						},
						{
							"id": 1979,
							"namakecamatan": "TUMIJAJAR"
						},
						{
							"id": 5694,
							"namakecamatan": "TUMINITING"
						},
						{
							"id": 5633,
							"namakecamatan": "TUMPAAN"
						},
						{
							"id": 3627,
							"namakecamatan": "TUMPANG"
						},
						{
							"id": 1265,
							"namakecamatan": "TUNGKAL ILIR"
						},
						{
							"id": 1494,
							"namakecamatan": "TUNGKAL ILIR"
						},
						{
							"id": 1477,
							"namakecamatan": "TUNGKAL JAYA"
						},
						{
							"id": 1264,
							"namakecamatan": "TUNGKAL ULU"
						},
						{
							"id": 3160,
							"namakecamatan": "TUNJUNGAN"
						},
						{
							"id": 4280,
							"namakecamatan": "TUNJUNG TEJA"
						},
						{
							"id": 3246,
							"namakecamatan": "TUNTANG"
						},
						{
							"id": 5972,
							"namakecamatan": "TURATEA"
						},
						{
							"id": 3620,
							"namakecamatan": "TUREN"
						},
						{
							"id": 3481,
							"namakecamatan": "TURI"
						},
						{
							"id": 4006,
							"namakecamatan": "TURI"
						},
						{
							"id": 6052,
							"namakecamatan": "TURIKALE"
						},
						{
							"id": 6675,
							"namakecamatan": "TUTAR"
						},
						{
							"id": 6783,
							"namakecamatan": "TUTUK TOLU"
						},
						{
							"id": 3786,
							"namakecamatan": "TUTUR"
						},
						{
							"id": 5547,
							"namakecamatan": "TUTUYAN"
						},
						{
							"id": 5683,
							"namakecamatan": "TUTUYAN"
						},
						{
							"id": 7312,
							"namakecamatan": "UBAHAK"
						},
						{
							"id": 7317,
							"namakecamatan": "UBALIHI"
						},
						{
							"id": 4355,
							"namakecamatan": "UBUD"
						},
						{
							"id": 3565,
							"namakecamatan": "UDANAWU"
						},
						{
							"id": 6492,
							"namakecamatan": "UEESI"
						},
						{
							"id": 6307,
							"namakecamatan": "UEPAI"
						},
						{
							"id": 7591,
							"namakecamatan": "UGIMBA"
						},
						{
							"id": 1401,
							"namakecamatan": "UJAN MAS"
						},
						{
							"id": 1728,
							"namakecamatan": "UJAN MAS"
						},
						{
							"id": 1634,
							"namakecamatan": "UJAN MAS"
						},
						{
							"id": 6251,
							"namakecamatan": "UJUNG"
						},
						{
							"id": 1102,
							"namakecamatan": "UJUNG BATU"
						},
						{
							"id": 2796,
							"namakecamatan": "UJUNG BERUNG"
						},
						{
							"id": 5948,
							"namakecamatan": "UJUNG BULU"
						},
						{
							"id": 2599,
							"namakecamatan": "UJUNGJAYA"
						},
						{
							"id": 5955,
							"namakecamatan": "UJUNGLOE"
						},
						{
							"id": 513,
							"namakecamatan": "UJUNG PADANG"
						},
						{
							"id": 6239,
							"namakecamatan": "UJUNG PANDANG"
						},
						{
							"id": 4019,
							"namakecamatan": "UJUNGPANGKAH"
						},
						{
							"id": 6243,
							"namakecamatan": "UJUNG TANAH"
						},
						{
							"id": 7308,
							"namakecamatan": "UKHA"
						},
						{
							"id": 1090,
							"namakecamatan": "UKUI"
						},
						{
							"id": 896,
							"namakecamatan": "ULAKAN TAPAKIH"
						},
						{
							"id": 6025,
							"namakecamatan": "ULAWENG"
						},
						{
							"id": 293,
							"namakecamatan": "ULEE KARENG"
						},
						{
							"id": 6983,
							"namakecamatan": "ULILIN"
						},
						{
							"id": 137,
							"namakecamatan": "ULIM"
						},
						{
							"id": 278,
							"namakecamatan": "ULIM"
						},
						{
							"id": 1668,
							"namakecamatan": "ULOK KUPAI"
						},
						{
							"id": 593,
							"namakecamatan": "ULUAN"
						},
						{
							"id": 727,
							"namakecamatan": "ULU BARUMUN"
						},
						{
							"id": 370,
							"namakecamatan": "ULU BARUMUN"
						},
						{
							"id": 1899,
							"namakecamatan": "ULU BELU"
						},
						{
							"id": 5758,
							"namakecamatan": "ULUBONGKA"
						},
						{
							"id": 5889,
							"namakecamatan": "ULUBONGKA"
						},
						{
							"id": 5962,
							"namakecamatan": "ULUERE"
						},
						{
							"id": 402,
							"namakecamatan": "ULUGAWO"
						},
						{
							"id": 6489,
							"namakecamatan": "ULUIWOI"
						},
						{
							"id": 6275,
							"namakecamatan": "ULUIWOI"
						},
						{
							"id": 5932,
							"namakecamatan": "ULUJADI"
						},
						{
							"id": 3347,
							"namakecamatan": "ULUJAMI"
						},
						{
							"id": 6691,
							"namakecamatan": "ULUMANDA"
						},
						{
							"id": 1619,
							"namakecamatan": "ULU MANNA"
						},
						{
							"id": 765,
							"namakecamatan": "ULU MORO'O"
						},
						{
							"id": 399,
							"namakecamatan": "ULU MORO'O/ ULUNARWO"
						},
						{
							"id": 1556,
							"namakecamatan": "ULU MUSI"
						},
						{
							"id": 1419,
							"namakecamatan": "ULU MUSI"
						},
						{
							"id": 649,
							"namakecamatan": "ULUNOYO"
						},
						{
							"id": 1355,
							"namakecamatan": "ULU OGAN"
						},
						{
							"id": 618,
							"namakecamatan": "ULU PUNGKUT"
						},
						{
							"id": 1450,
							"namakecamatan": "ULU RAWAS"
						},
						{
							"id": 1575,
							"namakecamatan": "ULU RAWAS"
						},
						{
							"id": 654,
							"namakecamatan": "ULUSUSUA"
						},
						{
							"id": 1698,
							"namakecamatan": "ULU TALO"
						},
						{
							"id": 7348,
							"namakecamatan": "UMAGI"
						},
						{
							"id": 4745,
							"namakecamatan": "UMALULU"
						},
						{
							"id": 3496,
							"namakecamatan": "UMBULHARJO"
						},
						{
							"id": 3670,
							"namakecamatan": "UMBULSARI"
						},
						{
							"id": 644,
							"namakecamatan": "UMBUNASI"
						},
						{
							"id": 4773,
							"namakecamatan": "UMBU RATU NGGAY"
						},
						{
							"id": 4817,
							"namakecamatan": "UMBU RATU NGGAY"
						},
						{
							"id": 4815,
							"namakecamatan": "UMBU RATU NGGAYBARAT"
						},
						{
							"id": 4775,
							"namakecamatan": "UMBU RATU NGGAYBARAT"
						},
						{
							"id": 6291,
							"namakecamatan": "UNAAHA"
						},
						{
							"id": 5753,
							"namakecamatan": "UNA UNA"
						},
						{
							"id": 5884,
							"namakecamatan": "UNA UNA"
						},
						{
							"id": 3205,
							"namakecamatan": "UNDAAN"
						},
						{
							"id": 2094,
							"namakecamatan": "UNGAR"
						},
						{
							"id": 3254,
							"namakecamatan": "UNGARAN"
						},
						{
							"id": 3258,
							"namakecamatan": "UNGARAN BARAT"
						},
						{
							"id": 3259,
							"namakecamatan": "UNGARAN TIMUR"
						},
						{
							"id": 7446,
							"namakecamatan": "UNIR SIRAU"
						},
						{
							"id": 4455,
							"namakecamatan": "UNTER IWES"
						},
						{
							"id": 7071,
							"namakecamatan": "UNURUM GUAY"
						},
						{
							"id": 5328,
							"namakecamatan": "UPAU"
						},
						{
							"id": 1725,
							"namakecamatan": "URAM JAYA"
						},
						{
							"id": 7394,
							"namakecamatan": "UREI FAISEI"
						},
						{
							"id": 7043,
							"namakecamatan": "USILIMO"
						},
						{
							"id": 4439,
							"namakecamatan": "UTAN"
						},
						{
							"id": 5191,
							"namakecamatan": "UUT MURUNG"
						},
						{
							"id": 7087,
							"namakecamatan": "UWAPA"
						},
						{
							"id": 7431,
							"namakecamatan": "VENAHA"
						},
						{
							"id": 1310,
							"namakecamatan": "VII KOTO"
						},
						{
							"id": 1315,
							"namakecamatan": "VII KOTO ILIR"
						},
						{
							"id": 891,
							"namakecamatan": "VII KOTO SUNGAI SARIK"
						},
						{
							"id": 1711,
							"namakecamatan": "V KOTO"
						},
						{
							"id": 892,
							"namakecamatan": "V KOTO KAMPUNGDALAM"
						},
						{
							"id": 900,
							"namakecamatan": "V KOTO TIMUR"
						},
						{
							"id": 6993,
							"namakecamatan": "WAAN"
						},
						{
							"id": 6393,
							"namakecamatan": "WABULA"
						},
						{
							"id": 6504,
							"namakecamatan": "WADAGA"
						},
						{
							"id": 6360,
							"namakecamatan": "WA DAGA"
						},
						{
							"id": 7046,
							"namakecamatan": "WADANGKU"
						},
						{
							"id": 2977,
							"namakecamatan": "WADASLINTANG"
						},
						{
							"id": 2575,
							"namakecamatan": "WADO"
						},
						{
							"id": 6765,
							"namakecamatan": "WAEAPO"
						},
						{
							"id": 7170,
							"namakecamatan": "WAEGI"
						},
						{
							"id": 6774,
							"namakecamatan": "WAELATA"
						},
						{
							"id": 4721,
							"namakecamatan": "WAE RII"
						},
						{
							"id": 6766,
							"namakecamatan": "WAESAMA"
						},
						{
							"id": 6832,
							"namakecamatan": "WAESAMA"
						},
						{
							"id": 3632,
							"namakecamatan": "WAGIR"
						},
						{
							"id": 4671,
							"namakecamatan": "WAIBLAMA"
						},
						{
							"id": 7075,
							"namakecamatan": "WAIBU"
						},
						{
							"id": 7625,
							"namakecamatan": "WAIGEO BARAT"
						},
						{
							"id": 7736,
							"namakecamatan": "WAIGEO BARAT"
						},
						{
							"id": 7744,
							"namakecamatan": "WAIGEO BARATKEPULAUAN"
						},
						{
							"id": 7732,
							"namakecamatan": "WAIGEO SELATAN"
						},
						{
							"id": 7737,
							"namakecamatan": "WAIGEO TIMUR"
						},
						{
							"id": 7731,
							"namakecamatan": "WAIGEO UTARA"
						},
						{
							"id": 4667,
							"namakecamatan": "WAIGETE"
						},
						{
							"id": 3619,
							"namakecamatan": "WAJAK"
						},
						{
							"id": 6240,
							"namakecamatan": "WAJO"
						},
						{
							"id": 6782,
							"namakecamatan": "WAKATE"
						},
						{
							"id": 6334,
							"namakecamatan": "WAKORUMBA"
						},
						{
							"id": 6338,
							"namakecamatan": "WAKORUMBA SELATAN"
						},
						{
							"id": 6481,
							"namakecamatan": "WAKORUMBA UTARA"
						},
						{
							"id": 7374,
							"namakecamatan": "WAKUWO"
						},
						{
							"id": 7036,
							"namakecamatan": "WALAIK"
						},
						{
							"id": 4319,
							"namakecamatan": "WALANTAKA"
						},
						{
							"id": 4270,
							"namakecamatan": "WALANTAKA"
						},
						{
							"id": 5892,
							"namakecamatan": "WALEA BESAR"
						},
						{
							"id": 5755,
							"namakecamatan": "WALEA KEPULAUAN"
						},
						{
							"id": 5886,
							"namakecamatan": "WALEA KEPULAUAN"
						},
						{
							"id": 2509,
							"namakecamatan": "WALED"
						},
						{
							"id": 7019,
							"namakecamatan": "WALELAGAMA"
						},
						{
							"id": 6135,
							"namakecamatan": "WALENRANG"
						},
						{
							"id": 6144,
							"namakecamatan": "WALENRANG BARAT"
						},
						{
							"id": 6146,
							"namakecamatan": "WALENRANG TIMUR"
						},
						{
							"id": 6145,
							"namakecamatan": "WALENRANG UTARA"
						},
						{
							"id": 7314,
							"namakecamatan": "WALMA"
						},
						{
							"id": 2254,
							"namakecamatan": "WALURAN"
						},
						{
							"id": 7061,
							"namakecamatan": "WAME"
						},
						{
							"id": 6995,
							"namakecamatan": "WAMENA"
						},
						{
							"id": 7783,
							"namakecamatan": "WAMESA"
						},
						{
							"id": 7761,
							"namakecamatan": "WAMESA"
						},
						{
							"id": 418,
							"namakecamatan": "WAMPU"
						},
						{
							"id": 2924,
							"namakecamatan": "WANADADI"
						},
						{
							"id": 2362,
							"namakecamatan": "WANARAJA"
						},
						{
							"id": 5273,
							"namakecamatan": "WANARAYA"
						},
						{
							"id": 2860,
							"namakecamatan": "WANAREJA"
						},
						{
							"id": 4427,
							"namakecamatan": "WANASABA"
						},
						{
							"id": 4217,
							"namakecamatan": "WANASALAM"
						},
						{
							"id": 3374,
							"namakecamatan": "WANASARI"
						},
						{
							"id": 2670,
							"namakecamatan": "WANAYASA"
						},
						{
							"id": 2931,
							"namakecamatan": "WANAYASA"
						},
						{
							"id": 7587,
							"namakecamatan": "WANDAI"
						},
						{
							"id": 7192,
							"namakecamatan": "WANDAI"
						},
						{
							"id": 5699,
							"namakecamatan": "WANEA"
						},
						{
							"id": 7156,
							"namakecamatan": "WANGBE"
						},
						{
							"id": 7551,
							"namakecamatan": "WANGBE"
						},
						{
							"id": 7088,
							"namakecamatan": "WANGGAR"
						},
						{
							"id": 6603,
							"namakecamatan": "WANGGARASI"
						},
						{
							"id": 6440,
							"namakecamatan": "WANGI-WANGI"
						},
						{
							"id": 6376,
							"namakecamatan": "WANGI-WANGI"
						},
						{
							"id": 6444,
							"namakecamatan": "WANGI WANGI SELATAN"
						},
						{
							"id": 2871,
							"namakecamatan": "WANGON"
						},
						{
							"id": 7225,
							"namakecamatan": "WANIA"
						},
						{
							"id": 7501,
							"namakecamatan": "WANO BARAT"
						},
						{
							"id": 4770,
							"namakecamatan": "WANOKAKA"
						},
						{
							"id": 7168,
							"namakecamatan": "WANWI"
						},
						{
							"id": 6768,
							"namakecamatan": "WAPLAU"
						},
						{
							"id": 7102,
							"namakecamatan": "WAPOGA"
						},
						{
							"id": 7398,
							"namakecamatan": "WAPOGA"
						},
						{
							"id": 6254,
							"namakecamatan": "WARA"
						},
						{
							"id": 6259,
							"namakecamatan": "WARA BARAT"
						},
						{
							"id": 6256,
							"namakecamatan": "WARA SELATAN"
						},
						{
							"id": 6258,
							"namakecamatan": "WARA TIMUR"
						},
						{
							"id": 6255,
							"namakecamatan": "WARA UTARA"
						},
						{
							"id": 4266,
							"namakecamatan": "WARINGINKURUNG"
						},
						{
							"id": 7243,
							"namakecamatan": "WARIS"
						},
						{
							"id": 7370,
							"namakecamatan": "WARI/TAIYEVE II"
						},
						{
							"id": 1531,
							"namakecamatan": "WARKUK RANAU SELATAN"
						},
						{
							"id": 7659,
							"namakecamatan": "WARMARE"
						},
						{
							"id": 7389,
							"namakecamatan": "WAROPEN ATAS"
						},
						{
							"id": 7466,
							"namakecamatan": "WAROPEN ATAS"
						},
						{
							"id": 7388,
							"namakecamatan": "WAROPEN BAWAH"
						},
						{
							"id": 7404,
							"namakecamatan": "WAROPKO"
						},
						{
							"id": 7129,
							"namakecamatan": "WARSA"
						},
						{
							"id": 7697,
							"namakecamatan": "WARTUTIN"
						},
						{
							"id": 5441,
							"namakecamatan": "WARU"
						},
						{
							"id": 4072,
							"namakecamatan": "WARU"
						},
						{
							"id": 3826,
							"namakecamatan": "WARU"
						},
						{
							"id": 2767,
							"namakecamatan": "WARUDOYONG"
						},
						{
							"id": 3312,
							"namakecamatan": "WARUNGASEM"
						},
						{
							"id": 4211,
							"namakecamatan": "WARUNGGUNUNG"
						},
						{
							"id": 2243,
							"namakecamatan": "WARUNG KIARA"
						},
						{
							"id": 2283,
							"namakecamatan": "WARUNGKONDANG"
						},
						{
							"id": 3348,
							"namakecamatan": "WARUNGPRING"
						},
						{
							"id": 3365,
							"namakecamatan": "WARUREJA"
						},
						{
							"id": 7743,
							"namakecamatan": "WARWARBOMI"
						},
						{
							"id": 6937,
							"namakecamatan": "WASILE"
						},
						{
							"id": 6940,
							"namakecamatan": "WASILE SELATAN"
						},
						{
							"id": 6941,
							"namakecamatan": "WASILE TENGAH"
						},
						{
							"id": 6943,
							"namakecamatan": "WASILE TIMUR"
						},
						{
							"id": 6942,
							"namakecamatan": "WASILE UTARA"
						},
						{
							"id": 7779,
							"namakecamatan": "WASIOR"
						},
						{
							"id": 6214,
							"namakecamatan": "WASUPONDA"
						},
						{
							"id": 6097,
							"namakecamatan": "WATANG PULU"
						},
						{
							"id": 6109,
							"namakecamatan": "WATANG SAWITO"
						},
						{
							"id": 3591,
							"namakecamatan": "WATES"
						},
						{
							"id": 3421,
							"namakecamatan": "WATES"
						},
						{
							"id": 3583,
							"namakecamatan": "WATES"
						},
						{
							"id": 6344,
							"namakecamatan": "WATOPUTE"
						},
						{
							"id": 6270,
							"namakecamatan": "WATUBANGGA"
						},
						{
							"id": 3338,
							"namakecamatan": "WATUKUMPUL"
						},
						{
							"id": 3538,
							"namakecamatan": "WATULIMO"
						},
						{
							"id": 2986,
							"namakecamatan": "WATUMALANG"
						},
						{
							"id": 6456,
							"namakecamatan": "WATUNOHU"
						},
						{
							"id": 1932,
							"namakecamatan": "WAWAY KARYA"
						},
						{
							"id": 6454,
							"namakecamatan": "WAWO"
						},
						{
							"id": 4475,
							"namakecamatan": "WAWO"
						},
						{
							"id": 6473,
							"namakecamatan": "WAWOLESEA"
						},
						{
							"id": 6295,
							"namakecamatan": "WAWONII BARAT"
						},
						{
							"id": 6494,
							"namakecamatan": "WAWONII BARAT"
						},
						{
							"id": 6499,
							"namakecamatan": "WAWONII SELATAN"
						},
						{
							"id": 6302,
							"namakecamatan": "WAWONII SELATAN"
						},
						{
							"id": 6500,
							"namakecamatan": "WAWONII TENGAH"
						},
						{
							"id": 6319,
							"namakecamatan": "WAWONII TENGAH"
						},
						{
							"id": 6498,
							"namakecamatan": "WAWONII TENGGARA"
						},
						{
							"id": 6323,
							"namakecamatan": "WAWONII TENGGARA"
						},
						{
							"id": 6296,
							"namakecamatan": "WAWONII TIMUR"
						},
						{
							"id": 6497,
							"namakecamatan": "WAWONII TIMUR"
						},
						{
							"id": 6324,
							"namakecamatan": "WAWONII TIMUR LAUT"
						},
						{
							"id": 6496,
							"namakecamatan": "WAWONII TIMUR LAUT"
						},
						{
							"id": 6303,
							"namakecamatan": "WAWONII UTARA"
						},
						{
							"id": 6495,
							"namakecamatan": "WAWONII UTARA"
						},
						{
							"id": 6292,
							"namakecamatan": "WAWOTOBI"
						},
						{
							"id": 1935,
							"namakecamatan": "WAY BUNGUR"
						},
						{
							"id": 7715,
							"namakecamatan": "WAYER"
						},
						{
							"id": 2011,
							"namakecamatan": "WAY HALIM"
						},
						{
							"id": 1919,
							"namakecamatan": "WAY JEPARA"
						},
						{
							"id": 1878,
							"namakecamatan": "WAY KENANGA"
						},
						{
							"id": 1983,
							"namakecamatan": "WAY KENANGA"
						},
						{
							"id": 1959,
							"namakecamatan": "WAY KHILAU"
						},
						{
							"id": 1852,
							"namakecamatan": "WAY KRUI"
						},
						{
							"id": 1992,
							"namakecamatan": "WAY KRUI"
						},
						{
							"id": 1772,
							"namakecamatan": "WAY LIMA"
						},
						{
							"id": 1954,
							"namakecamatan": "WAY LIMA"
						},
						{
							"id": 1794,
							"namakecamatan": "WAY PANGUBUAN"
						},
						{
							"id": 1777,
							"namakecamatan": "WAY PANJI"
						},
						{
							"id": 1961,
							"namakecamatan": "WAY RATAI"
						},
						{
							"id": 1802,
							"namakecamatan": "WAY SEPUTIH"
						},
						{
							"id": 1974,
							"namakecamatan": "WAY SERDANG"
						},
						{
							"id": 1863,
							"namakecamatan": "WAY SERDANG"
						},
						{
							"id": 1776,
							"namakecamatan": "WAY SULAN"
						},
						{
							"id": 1835,
							"namakecamatan": "WAY TENONG"
						},
						{
							"id": 1944,
							"namakecamatan": "WAY TUBA"
						},
						{
							"id": 7246,
							"namakecamatan": "WEB"
						},
						{
							"id": 6856,
							"namakecamatan": "WEDA"
						},
						{
							"id": 3195,
							"namakecamatan": "WEDARIJAKSA"
						},
						{
							"id": 6860,
							"namakecamatan": "WEDA SELATAN"
						},
						{
							"id": 6862,
							"namakecamatan": "WEDA TENGAH"
						},
						{
							"id": 6864,
							"namakecamatan": "WEDA TIMUR"
						},
						{
							"id": 6859,
							"namakecamatan": "WEDA UTARA"
						},
						{
							"id": 3034,
							"namakecamatan": "WEDI"
						},
						{
							"id": 3239,
							"namakecamatan": "WEDUNG"
						},
						{
							"id": 7198,
							"namakecamatan": "WEGEE BINO"
						},
						{
							"id": 7197,
							"namakecamatan": "WEGEE MUKA"
						},
						{
							"id": 7280,
							"namakecamatan": "WEIME"
						},
						{
							"id": 3213,
							"namakecamatan": "WELAHAN"
						},
						{
							"id": 4803,
							"namakecamatan": "WELAK"
						},
						{
							"id": 7478,
							"namakecamatan": "WELAREK"
						},
						{
							"id": 7032,
							"namakecamatan": "WELAREK"
						},
						{
							"id": 3292,
							"namakecamatan": "WELERI"
						},
						{
							"id": 7057,
							"namakecamatan": "WELESI"
						},
						{
							"id": 4614,
							"namakecamatan": "WELIMAN"
						},
						{
							"id": 4848,
							"namakecamatan": "WELIMAN"
						},
						{
							"id": 7646,
							"namakecamatan": "WEMAK"
						},
						{
							"id": 7378,
							"namakecamatan": "WENAM"
						},
						{
							"id": 5696,
							"namakecamatan": "WENANG"
						},
						{
							"id": 4477,
							"namakecamatan": "WERA"
						},
						{
							"id": 7495,
							"namakecamatan": "WEREKA"
						},
						{
							"id": 7775,
							"namakecamatan": "WERIAGAR"
						},
						{
							"id": 7306,
							"namakecamatan": "WERIMA"
						},
						{
							"id": 6709,
							"namakecamatan": "WERINAMA"
						},
						{
							"id": 6780,
							"namakecamatan": "WERINAMA"
						},
						{
							"id": 6748,
							"namakecamatan": "WER MAKTIAN"
						},
						{
							"id": 6747,
							"namakecamatan": "WER TAMRIAN"
						},
						{
							"id": 3058,
							"namakecamatan": "WERU"
						},
						{
							"id": 2527,
							"namakecamatan": "WERU"
						},
						{
							"id": 7062,
							"namakecamatan": "WESAPUT"
						},
						{
							"id": 6819,
							"namakecamatan": "WETAR"
						},
						{
							"id": 6761,
							"namakecamatan": "WETAR"
						},
						{
							"id": 6827,
							"namakecamatan": "WETAR BARAT"
						},
						{
							"id": 6828,
							"namakecamatan": "WETAR TIMUR"
						},
						{
							"id": 6826,
							"namakecamatan": "WETAR UTARA"
						},
						{
							"id": 4686,
							"namakecamatan": "WEWARIA"
						},
						{
							"id": 4822,
							"namakecamatan": "WEWEWA BARAT"
						},
						{
							"id": 4765,
							"namakecamatan": "WEWEWA BARAT"
						},
						{
							"id": 4823,
							"namakecamatan": "WEWEWA SELATAN"
						},
						{
							"id": 4766,
							"namakecamatan": "WEWEWA SELATAN"
						},
						{
							"id": 4828,
							"namakecamatan": "WEWEWA TENGAH"
						},
						{
							"id": 4821,
							"namakecamatan": "WEWEWA TIMUR"
						},
						{
							"id": 4767,
							"namakecamatan": "WEWEWA TIMUR"
						},
						{
							"id": 4768,
							"namakecamatan": "WEWEWA UTARA"
						},
						{
							"id": 4820,
							"namakecamatan": "WEWEWA UTARA"
						},
						{
							"id": 4847,
							"namakecamatan": "WEWIKU"
						},
						{
							"id": 4613,
							"namakecamatan": "WEWIKU"
						},
						{
							"id": 3984,
							"namakecamatan": "WIDANG"
						},
						{
							"id": 2607,
							"namakecamatan": "WIDASARI"
						},
						{
							"id": 3930,
							"namakecamatan": "WIDODAREN"
						},
						{
							"id": 73,
							"namakecamatan": "WIH PESAM"
						},
						{
							"id": 272,
							"namakecamatan": "WIH PESAM"
						},
						{
							"id": 3880,
							"namakecamatan": "WILANGAN"
						},
						{
							"id": 7817,
							"namakecamatan": "WILHEM ROUMBOUTS"
						},
						{
							"id": 7347,
							"namakecamatan": "WINA"
						},
						{
							"id": 7780,
							"namakecamatan": "WINDESI"
						},
						{
							"id": 7118,
							"namakecamatan": "WINDESI"
						},
						{
							"id": 3012,
							"namakecamatan": "WINDUSARI"
						},
						{
							"id": 3184,
							"namakecamatan": "WINONG"
						},
						{
							"id": 3803,
							"namakecamatan": "WINONGAN"
						},
						{
							"id": 3331,
							"namakecamatan": "WIRADESA"
						},
						{
							"id": 7497,
							"namakecamatan": "WIRINGGAMBUT"
						},
						{
							"id": 3490,
							"namakecamatan": "WIROBRAJAN"
						},
						{
							"id": 3141,
							"namakecamatan": "WIROSARI"
						},
						{
							"id": 5834,
							"namakecamatan": "WITA PONDA"
						},
						{
							"id": 7044,
							"namakecamatan": "WITA WAYA"
						},
						{
							"id": 4651,
							"namakecamatan": "WITIHAMA"
						},
						{
							"id": 6311,
							"namakecamatan": "WIWIRANO"
						},
						{
							"id": 6464,
							"namakecamatan": "WIWIRANO"
						},
						{
							"id": 4147,
							"namakecamatan": "WIYUNG"
						},
						{
							"id": 3580,
							"namakecamatan": "WLINGI"
						},
						{
							"id": 4473,
							"namakecamatan": "WOHA"
						},
						{
							"id": 4467,
							"namakecamatan": "WOJA"
						},
						{
							"id": 6413,
							"namakecamatan": "WOLASI"
						},
						{
							"id": 6537,
							"namakecamatan": "WOLIO"
						},
						{
							"id": 6272,
							"namakecamatan": "WOLO"
						},
						{
							"id": 7022,
							"namakecamatan": "WOLO"
						},
						{
							"id": 4688,
							"namakecamatan": "WOLOJITA"
						},
						{
							"id": 4716,
							"namakecamatan": "WOLOMEZE"
						},
						{
							"id": 6392,
							"namakecamatan": "WOLOWA"
						},
						{
							"id": 4711,
							"namakecamatan": "WOLOWAE"
						},
						{
							"id": 4811,
							"namakecamatan": "WOLOWAE"
						},
						{
							"id": 4687,
							"namakecamatan": "WOLOWARU"
						},
						{
							"id": 7117,
							"namakecamatan": "WONAWA"
						},
						{
							"id": 7782,
							"namakecamatan": "WONDIBOY"
						},
						{
							"id": 6308,
							"namakecamatan": "WONGGEDUKU"
						},
						{
							"id": 3714,
							"namakecamatan": "WONGSOREJO"
						},
						{
							"id": 7350,
							"namakecamatan": "WONIKI"
						},
						{
							"id": 4115,
							"namakecamatan": "WONOASIH"
						},
						{
							"id": 3900,
							"namakecamatan": "WONOASRI"
						},
						{
							"id": 3818,
							"namakecamatan": "WONOAYU"
						},
						{
							"id": 3279,
							"namakecamatan": "WONOBOYO"
						},
						{
							"id": 4129,
							"namakecamatan": "WONOCOLO"
						},
						{
							"id": 3564,
							"namakecamatan": "WONODADI"
						},
						{
							"id": 3081,
							"namakecamatan": "WONOGIRI"
						},
						{
							"id": 3334,
							"namakecamatan": "WONOKERTO"
						},
						{
							"id": 4131,
							"namakecamatan": "WONOKROMO"
						},
						{
							"id": 3782,
							"namakecamatan": "WONOMERTO"
						},
						{
							"id": 6673,
							"namakecamatan": "WONOMULYO"
						},
						{
							"id": 3327,
							"namakecamatan": "WONOPRINGGO"
						},
						{
							"id": 3791,
							"namakecamatan": "WONOREJO"
						},
						{
							"id": 3849,
							"namakecamatan": "WONOSALAM"
						},
						{
							"id": 3232,
							"namakecamatan": "WONOSALAM"
						},
						{
							"id": 3643,
							"namakecamatan": "WONOSARI"
						},
						{
							"id": 3449,
							"namakecamatan": "WONOSARI"
						},
						{
							"id": 3729,
							"namakecamatan": "WONOSARI"
						},
						{
							"id": 6569,
							"namakecamatan": "WONOSARI"
						},
						{
							"id": 3046,
							"namakecamatan": "WONOSARI"
						},
						{
							"id": 3030,
							"namakecamatan": "WONOSEGORO"
						},
						{
							"id": 1887,
							"namakecamatan": "WONOSOBO"
						},
						{
							"id": 2985,
							"namakecamatan": "WONOSOBO"
						},
						{
							"id": 3571,
							"namakecamatan": "WONOTIRTO"
						},
						{
							"id": 3301,
							"namakecamatan": "WONOTUNGGAL"
						},
						{
							"id": 7400,
							"namakecamatan": "WONTI"
						},
						{
							"id": 5648,
							"namakecamatan": "WORI"
						},
						{
							"id": 7014,
							"namakecamatan": "WOSAK"
						},
						{
							"id": 7521,
							"namakecamatan": "WOSAK"
						},
						{
							"id": 4648,
							"namakecamatan": "WOTAN ULUMANDO"
						},
						{
							"id": 6209,
							"namakecamatan": "WOTU"
						},
						{
							"id": 7037,
							"namakecamatan": "WOUMA"
						},
						{
							"id": 83,
							"namakecamatan": "WOYLA"
						},
						{
							"id": 89,
							"namakecamatan": "WOYLA BARAT"
						},
						{
							"id": 90,
							"namakecamatan": "WOYLA TIMUR"
						},
						{
							"id": 3732,
							"namakecamatan": "WRINGIN"
						},
						{
							"id": 4018,
							"namakecamatan": "WRINGINANOM"
						},
						{
							"id": 6102,
							"namakecamatan": "WT. SIDENRENG"
						},
						{
							"id": 6751,
							"namakecamatan": "WUAR LABOBAR"
						},
						{
							"id": 6532,
							"namakecamatan": "WUA-WUA"
						},
						{
							"id": 7379,
							"namakecamatan": "WUGI"
						},
						{
							"id": 4785,
							"namakecamatan": "WULANDONI"
						},
						{
							"id": 4640,
							"namakecamatan": "WULANGGITANG"
						},
						{
							"id": 4748,
							"namakecamatan": "WULLA WAIJELU"
						},
						{
							"id": 3676,
							"namakecamatan": "WULUHAN"
						},
						{
							"id": 6263,
							"namakecamatan": "WUNDULAKO"
						},
						{
							"id": 3892,
							"namakecamatan": "WUNGU"
						},
						{
							"id": 7346,
							"namakecamatan": "WUNIM"
						},
						{
							"id": 3078,
							"namakecamatan": "WURYANTORO"
						},
						{
							"id": 7299,
							"namakecamatan": "WUSAMA"
						},
						{
							"id": 7543,
							"namakecamatan": "WUSI"
						},
						{
							"id": 7546,
							"namakecamatan": "WUTPAGA"
						},
						{
							"id": 890,
							"namakecamatan": "X ENAMLINGKUANG"
						},
						{
							"id": 1025,
							"namakecamatan": "XIII KOTO KAMPAR"
						},
						{
							"id": 1710,
							"namakecamatan": "XIV KOTO"
						},
						{
							"id": 901,
							"namakecamatan": "X KAYU TANAM"
						},
						{
							"id": 873,
							"namakecamatan": "X KOTO"
						},
						{
							"id": 853,
							"namakecamatan": "X KOTO DIATAS"
						},
						{
							"id": 852,
							"namakecamatan": "X KOTO SINGKARAK"
						},
						{
							"id": 7252,
							"namakecamatan": "YAFFI"
						},
						{
							"id": 7203,
							"namakecamatan": "YAGAI"
						},
						{
							"id": 7315,
							"namakecamatan": "YAHULIAMBUT"
						},
						{
							"id": 7433,
							"namakecamatan": "YAKOMI"
						},
						{
							"id": 7533,
							"namakecamatan": "YAL"
						},
						{
							"id": 7029,
							"namakecamatan": "YALENGGA"
						},
						{
							"id": 7163,
							"namakecamatan": "YAMBI"
						},
						{
							"id": 7149,
							"namakecamatan": "YAMO"
						},
						{
							"id": 7169,
							"namakecamatan": "YAMONERI"
						},
						{
							"id": 7798,
							"namakecamatan": "YAMOR"
						},
						{
							"id": 7415,
							"namakecamatan": "YANIRUMA"
						},
						{
							"id": 7108,
							"namakecamatan": "YAPEN BARAT"
						},
						{
							"id": 7107,
							"namakecamatan": "YAPEN SELATAN"
						},
						{
							"id": 7109,
							"namakecamatan": "YAPEN TIMUR"
						},
						{
							"id": 7113,
							"namakecamatan": "YAPEN UTARA"
						},
						{
							"id": 7077,
							"namakecamatan": "YAPSI"
						},
						{
							"id": 7098,
							"namakecamatan": "YARO"
						},
						{
							"id": 6750,
							"namakecamatan": "YARU"
						},
						{
							"id": 7195,
							"namakecamatan": "YATAMO"
						},
						{
							"id": 7084,
							"namakecamatan": "YAUR"
						},
						{
							"id": 7133,
							"namakecamatan": "YAWOSI"
						},
						{
							"id": 7618,
							"namakecamatan": "YEMBUN"
						},
						{
							"id": 7801,
							"namakecamatan": "YEMBUN"
						},
						{
							"id": 7131,
							"namakecamatan": "YENDIDORI"
						},
						{
							"id": 7530,
							"namakecamatan": "YENGGELO"
						},
						{
							"id": 7016,
							"namakecamatan": "YIGI"
						},
						{
							"id": 7520,
							"namakecamatan": "YIGI"
						},
						{
							"id": 7491,
							"namakecamatan": "YIGINUA"
						},
						{
							"id": 7511,
							"namakecamatan": "YILUK"
						},
						{
							"id": 7338,
							"namakecamatan": "YOGOSEM"
						},
						{
							"id": 7081,
							"namakecamatan": "YOKARI"
						},
						{
							"id": 3651,
							"namakecamatan": "YOSOWILANGUN"
						},
						{
							"id": 7204,
							"namakecamatan": "YOUTADI"
						},
						{
							"id": 7566,
							"namakecamatan": "YUGUMUAK"
						},
						{
							"id": 7493,
							"namakecamatan": "YUGUNGWI"
						},
						{
							"id": 7387,
							"namakecamatan": "YUKO"
						},
						{
							"id": 7373,
							"namakecamatan": "YUNERI"
						}
					],
					"message": "inhuman"
				}
3. Get Data Master
	- api service : https://svr1.rsdarurat.com/medifirst2000/registrasi/get-combo-registrasi
	- header->Content-Type : application/json
	- header->X-AUTH-TOKEN : {{Get Signature/Token}}
	- respone : {
					"jeniskelamin": [
						{
							"id": 0,
							"jeniskelamin": "-"
						},
						{
							"id": 1,
							"jeniskelamin": "LAKI-LAKI"
						},
						{
							"id": 2,
							"jeniskelamin": "PEREMPUAN"
						}
					],
					"agama": [
						{
							"id": 0,
							"agama": "-"
						},
						{
							"id": 1,
							"agama": "ISLAM"
						},
						{
							"id": 2,
							"agama": "KRISTEN PROTESTAN"
						},
						{
							"id": 3,
							"agama": "KRISTEN KATHOLIK"
						},
						{
							"id": 4,
							"agama": "HINDU"
						},
						{
							"id": 5,
							"agama": "BUDHA"
						},
						{
							"id": 6,
							"agama": "KONGHUCHU"
						},
						{
							"id": 7,
							"agama": "ALIRAN KEPERCAYAAN"
						},
						{
							"id": 8,
							"agama": "LAINNYA"
						},
						{
							"id": 9,
							"agama": "KRISTEN"
						}
					],
					"statusperkawinan": [
						{
							"id": 0,
							"statusperkawinan": "-",
							"namadukcapil": "-"
						},
						{
							"id": 2,
							"statusperkawinan": "KAWIN",
							"namadukcapil": "KAWIN"
						},
						{
							"id": 4,
							"statusperkawinan": "JANDA",
							"namadukcapil": "Janda"
						},
						{
							"id": 5,
							"statusperkawinan": "DUDA",
							"namadukcapil": "Duda"
						},
						{
							"id": 1,
							"statusperkawinan": "BELUM KAWIN",
							"namadukcapil": "BELUM KAWIN"
						}
					],
					"pendidikan": [
						{
							"id": 0,
							"pendidikan": "TIDAK SEKOLAH",
							"namadukcapil": "TIDAK/BELUM SEKOLAH"
						},
						{
							"id": 1,
							"pendidikan": "TK",
							"namadukcapil": "BELUM TAMAT SD/SEDERAJAT"
						},
						{
							"id": 2,
							"pendidikan": "SD",
							"namadukcapil": "TAMAT SD /SEDERAJAT"
						},
						{
							"id": 3,
							"pendidikan": "SLTP",
							"namadukcapil": "SLTP/SEDERAJAT"
						},
						{
							"id": 4,
							"pendidikan": "SLTA",
							"namadukcapil": "SLTA/SEDERAJAT"
						},
						{
							"id": 5,
							"pendidikan": "DIPLOMA I",
							"namadukcapil": "DIPLOMA I"
						},
						{
							"id": 6,
							"pendidikan": "DIPLOMA II",
							"namadukcapil": "DIPLOMA II"
						},
						{
							"id": 7,
							"pendidikan": "DIPLOMA III",
							"namadukcapil": "AKADEMI/DIPLOMA III/S. MUDA"
						},
						{
							"id": 8,
							"pendidikan": "DIPLOMA IV",
							"namadukcapil": "DIPLOMA IV"
						},
						{
							"id": 9,
							"pendidikan": "S1",
							"namadukcapil": "DIPLOMA IV/STRATA I"
						},
						{
							"id": 10,
							"pendidikan": "S2",
							"namadukcapil": "STRATA II"
						},
						{
							"id": 11,
							"pendidikan": "S3",
							"namadukcapil": "STRATA III"
						},
						{
							"id": 13,
							"pendidikan": "-",
							"namadukcapil": "-"
						},
						{
							"id": 17,
							"pendidikan": "NERS",
							"namadukcapil": "Ners"
						},
						{
							"id": 18,
							"pendidikan": "APOTEKER",
							"namadukcapil": "Apoteker"
						},
						{
							"id": 19,
							"pendidikan": "S1 PROFESI",
							"namadukcapil": "S1 PROFESI"
						},
						{
							"id": 20,
							"pendidikan": "BELUM SEKOLAH",
							"namadukcapil": "BELUM SEKOLAH"
						},
						{
							"id": 21,
							"pendidikan": "TIDAK TAHU",
							"namadukcapil": "TIDAK TAHU"
						},
						{
							"id": 22,
							"pendidikan": "TDS",
							"namadukcapil": "TDS"
						},
						{
							"id": 23,
							"pendidikan": "SLB",
							"namadukcapil": "SLB"
						},
						{
							"id": 24,
							"pendidikan": "SMK",
							"namadukcapil": "SMK"
						},
						{
							"id": 25,
							"pendidikan": "MA",
							"namadukcapil": "MA"
						},
						{
							"id": 26,
							"pendidikan": "PAKET C",
							"namadukcapil": "PAKET C"
						}
					],
					"pekerjaan": [
						{
							"id": 0,
							"pekerjaan": "-",
							"namadukcapil": "-"
						},
						{
							"id": 1,
							"pekerjaan": "TIDAK BEKERJA",
							"namadukcapil": "BELUM/TIDAK BEKERJA"
						},
						{
							"id": 2,
							"pekerjaan": "MENGURUS RUMAH TANGGA",
							"namadukcapil": "MENGURUS RUMAH TANGGA"
						},
						{
							"id": 3,
							"pekerjaan": "PELAJAR/ MAHASISWA",
							"namadukcapil": "PELAJAR/MAHASISWA"
						},
						{
							"id": 4,
							"pekerjaan": "PEGAWAI SWASTA",
							"namadukcapil": "KARYAWAN SWASTA"
						},
						{
							"id": 5,
							"pekerjaan": "PEGAWAI NEGERI/ BUMN/ BUMD",
							"namadukcapil": "KARYAWAN BUMN\r\n"
						},
						{
							"id": 6,
							"pekerjaan": "TNI/ POLISI",
							"namadukcapil": "TENTARA NASIONAL INDONESIA\r\n"
						},
						{
							"id": 7,
							"pekerjaan": "WIRASWASTA/ PENGUSAHA",
							"namadukcapil": "WIRASWASTA\r\n"
						},
						{
							"id": 8,
							"pekerjaan": "PEJABAT NEGARA",
							"namadukcapil": "Pejabat negara"
						},
						{
							"id": 9,
							"pekerjaan": "PENSIUNAN",
							"namadukcapil": "PENSIUNAN\r\n"
						},
						{
							"id": 10,
							"pekerjaan": "LAIN-LAIN",
							"namadukcapil": "Lain-lain"
						},
						{
							"id": 11,
							"pekerjaan": "PETANI",
							"namadukcapil": "PETANI/PEKEBUN"
						},
						{
							"id": 12,
							"pekerjaan": "TIDAK BEKERJA",
							"namadukcapil": "BELUM/TIDAK BEKERJA"
						},
						{
							"id": 13,
							"pekerjaan": "WIRASWASTA",
							"namadukcapil": "WIRASWASTA"
						},
						{
							"id": 14,
							"pekerjaan": "PENSIUNAN",
							"namadukcapil": "PENSIUNAN"
						},
						{
							"id": 15,
							"pekerjaan": "BURUH",
							"namadukcapil": "BURUH HARIAN LEPAS"
						},
						{
							"id": 16,
							"pekerjaan": "PNS",
							"namadukcapil": "PEGAWAI NEGERI SIPIL"
						},
						{
							"id": 17,
							"pekerjaan": "SWASTA",
							"namadukcapil": "WIRASWASTA\r\n"
						},
						{
							"id": 18,
							"pekerjaan": "PNS ( POLRI)",
							"namadukcapil": "KEPOLISIAN RI\r\n"
						},
						{
							"id": 19,
							"pekerjaan": "IBU R.TANGGA",
							"namadukcapil": "MENGURUS RUMAH TANGGA\r\n"
						},
						{
							"id": 20,
							"pekerjaan": "PELAJAR",
							"namadukcapil": "PELAJAR/MAHASISWA\r\n"
						},
						{
							"id": 21,
							"pekerjaan": "PEG. KONTRAK",
							"namadukcapil": "PEG. KONTRAK"
						},
						{
							"id": 23,
							"pekerjaan": "BLM BEKERJA",
							"namadukcapil": "BELUM/TIDAK BEKERJA"
						},
						{
							"id": 24,
							"pekerjaan": "PURNAWIRAWAN",
							"namadukcapil": "PURNAWIRAWAN"
						},
						{
							"id": 25,
							"pekerjaan": "TNI",
							"namadukcapil": "TENTARA NASIONAL INDONESIA\r\n"
						},
						{
							"id": 26,
							"pekerjaan": "MAHASISWA",
							"namadukcapil": "PELAJAR/MAHASISWA\r\n"
						},
						{
							"id": 27,
							"pekerjaan": "PEDAGANG",
							"namadukcapil": "PEDAGANG"
						},
						{
							"id": 28,
							"pekerjaan": "POLRI",
							"namadukcapil": "KEPOLISIAN RI\r\n"
						},
						{
							"id": 29,
							"pekerjaan": "PEG. HONORER",
							"namadukcapil": "KARYAWAN HONORER\r\n"
						},
						{
							"id": 30,
							"pekerjaan": "SISWA TARUNA",
							"namadukcapil": "SISWA TARUNA"
						},
						{
							"id": 31,
							"pekerjaan": "CALON KARYAWAN",
							"namadukcapil": "CALON KARYAWAN"
						},
						{
							"id": 32,
							"pekerjaan": "NELAYAN",
							"namadukcapil": "NELAYAN/PERIKANAN\r\n"
						},
						{
							"id": 33,
							"pekerjaan": "PROFESIONAL/PROFESI",
							"namadukcapil": "PROFESIONAL/PROFESI"
						},
						{
							"id": 34,
							"pekerjaan": "TENAGA KERJA INDONESIA (TKI)",
							"namadukcapil": "TKI"
						}
					],
					"pegawaiLogin": "-",
					"golongandarah": [
						{
							"id": 0,
							"golongandarah": "-",
							"namadukcapil": "-"
						},
						{
							"id": 1,
							"golongandarah": "A",
							"namadukcapil": "A"
						},
						{
							"id": 2,
							"golongandarah": "B",
							"namadukcapil": "B"
						},
						{
							"id": 3,
							"golongandarah": "O",
							"namadukcapil": "0"
						},
						{
							"id": 4,
							"golongandarah": "AB",
							"namadukcapil": "AB"
						},
						{
							"id": 5,
							"golongandarah": "A-",
							"namadukcapil": "A-"
						},
						{
							"id": 6,
							"golongandarah": "B-",
							"namadukcapil": "B-"
						},
						{
							"id": 7,
							"golongandarah": "O-",
							"namadukcapil": "O-"
						},
						{
							"id": 8,
							"golongandarah": "AB-",
							"namadukcapil": "AB-"
						},
						{
							"id": 9,
							"golongandarah": "A+",
							"namadukcapil": "A+"
						},
						{
							"id": 10,
							"golongandarah": "B+",
							"namadukcapil": "B+"
						},
						{
							"id": 11,
							"golongandarah": "O+",
							"namadukcapil": "O+"
						},
						{
							"id": 12,
							"golongandarah": "AB+",
							"namadukcapil": "AB+"
						},
						{
							"id": 13,
							"golongandarah": "TIDAK TAHU",
							"namadukcapil": "TIDAK TAHU"
						}
					],
					"suku": [
						{
							"id": 0,
							"suku": "-"
						},
						{
							"id": 1,
							"suku": "JAWA"
						},
						{
							"id": 2,
							"suku": "SUNDA"
						},
						{
							"id": 3,
							"suku": "MADURA"
						},
						{
							"id": 4,
							"suku": "MANADO"
						},
						{
							"id": 5,
							"suku": "BALI"
						},
						{
							"id": 6,
							"suku": "DAYAK"
						},
						{
							"id": 7,
							"suku": "BETAWI"
						},
						{
							"id": 8,
							"suku": "* TRIAL "
						},
						{
							"id": 9,
							"suku": "PAPUA"
						},
						{
							"id": 10,
							"suku": "AMBON"
						},
						{
							"id": 11,
							"suku": "PADANG"
						},
						{
							"id": 12,
							"suku": "ACEH"
						},
						{
							"id": 13,
							"suku": "BUGIS"
						},
						{
							"id": 14,
							"suku": "MAKASAR"
						},
						{
							"id": 15,
							"suku": "MELAYU"
						},
						{
							"id": 16,
							"suku": "TIONGHOA"
						},
						{
							"id": 17,
							"suku": "ARAB"
						},
						{
							"id": 18,
							"suku": "KAILI"
						},
						{
							"id": 20,
							"suku": "INDONESIA"
						},
						{
							"id": 21,
							"suku": "BATAK"
						},
						{
							"id": 22,
							"suku": "BANJAR"
						},
						{
							"id": 23,
							"suku": "* TRIAL * TRIAL "
						},
						{
							"id": 24,
							"suku": "ULUN LAMPUNG"
						},
						{
							"id": 25,
							"suku": "EROPA"
						},
						{
							"id": 26,
							"suku": "ASMAT"
						},
						{
							"id": 27,
							"suku": "ATONI"
						},
						{
							"id": 28,
							"suku": "MATAUS"
						},
						{
							"id": 29,
							"suku": "BOJO"
						},
						{
							"id": 30,
							"suku": "BENGKULU"
						}
					],
					"message": "inhuman"
				}
					
4. Pasien Baru
	- api service : https://svr1.rsdarurat.com/service/medifirst2000/registrasi/save-pasien-fix	
	- Content-Type : application/json	
	- body - raw : {
						"isbayi": false,
						"isPenunjang": false,
						"idpasien": "",
						"pasien": {
							"namaPasien": "{{Nama Pasien}}" ->wajib diisi,
							"noIdentitas": "{{NIK}}",
							"namaSuamiIstri": {{Nama Suami/Istri}},
							"noAsuransiLain": {{No Asuransi Selain Bpjs}},
							"noBpjs": {{No Asuransi Bpjs}},
							"noHp": {{No Hp}} ->wajib diisi,
							"tempatLahir": {{tempat lahir}} ->wajib diisi,
							"namaKeluarga": {{nama keluarga}} ,
							"tglLahir": {{tanggal lahir}} ->wajib diisi,,
							"image": null
						},
						"agama": {
							{{ agama -> baca referensi no.3}} 
						},
						"jenisKelamin": {
							{{ jeniskelamin -> baca referensi no.3}} ->wajib diisi
						},
						"pekerjaan": {
							{{ pekerjaan -> baca referensi no.3}}
						},
						"pendidikan": {
							{{ pendidikan -> baca referensi no.3}}
						},
						"statusPerkawinan": {
							{{ status perkawinan -> baca referensi no.3}}
						},
						"golonganDarah": {
							{{ golongan darah -> baca referensi no.3}}
						},
						"suku": {
							{{ suku -> baca referensi }}
						},
						"namaIbu": {{nama ibu}},
						"noTelepon": {{no telepon}},
						"noAditional": {{no aditional}},
						"kebangsaan": {
							{{ kebangsaan -> baca referensi no.2}}
						},
						"negara": {
							{{ negara -> baca referensi no.2}}
						},
						"namaAyah": {{nama ayah}},
						"alamatLengkap": {{alamatLengkap}} -> wajib diisi,
						"desaKelurahan": {
							{{ desa kelurahan -> baca referensi no.2}}
						},
						"kecamatan": {
							{{ kecamatan -> baca referensi no.2}}
						},
						"kotaKabupaten": {
							{{ kota kabupaten -> baca referensi no.2}}
						},
						"propinsi": {
							{{ propinsi -> baca referensi }}
						},
						"kodePos": {{ kodepos -> baca referensi no.2}},
						"jenisalamat": 3,
						"alamatLengkaptd": {{alamatLengkap domisili}},
						"desaKelurahantd": {
							{{desa kelurahan domisili no.2}}
						},
						"kecamatantd": {
							{{kecamatan domisili no.2}}
						},
						"kotaKabupatentd": {
							{{kabupaten domisili no.2}}
						},
						"propinsitd": {
							{{propinsi domisili no.2}}
						},
						"kodePostd": {{kodepos domisili no.2}},
						"jenisalamattd": 4,
						"penanggungjawab": {{penanggung jawab pasien}},
						"hubungankeluargapj": {{hubungan keluarga pasien}},
						"pekerjaanpenangggungjawab": {{pekerjaan penanggung jawab }},
						"ktppenanggungjawab": {{ktp penanggung jawab }},
						"alamatrmh": {{alamat rumah penanggung jawab }},
						"alamatktr": {{ktp penanggung jawab pasien}},
						"teleponpenanggungjawab": {{telepon penanggung jawab pasien}},
						"bahasa": {{bahasa penanggung jawab pasien}},
						"jeniskelaminpenanggungjawab": {{jeniskelamin penanggung jawab pasien}},
						"umurpenanggungjawab": {{umur penanggung jawab pasien}},
						"dokterpengirim": {{dokter pengirim}},
						"alamatdokter": {{alamat dokter}},
						"isAlamatSama": true
					}
--------------------------------------------------------------------------------------------------------------------------------------------------
					"isbayi": false,
						"isPenunjang": false,
						"idpasien": "",
						"pasien": {
							"namaPasien": "EFAN ANDRIAN",
							"noIdentitas": "3213030509940006",
							"namaSuamiIstri": null,
							"noAsuransiLain": null,
							"noBpjs": null,
							"noHp": "081322389499",
							"tempatLahir": "SUBANG",
							"namaKeluarga": null,
							"tglLahir": "1994-09-05 00:00",
							"image": null
						},
						"agama": {
							"id": 1
						},
						"jenisKelamin": {
							"id": 1
						},
						"pekerjaan": {
							"id": 4
						},
						"pendidikan": {
							"id": 7
						},
						"statusPerkawinan": {
							"id": 2
						},
						"golonganDarah": {
							"id": 2
						},
						"suku": {
							"id": 2
						},
						"namaIbu": null,
						"noTelepon": null,
						"noAditional": null,
						"kebangsaan": {
							"id": 1
						},
						"negara": {
							"id": 0
						},
						"namaAyah": null,
						"alamatLengkap": "JLN. CIHANJUANG GG.BAGJA 3 NO.88 RT/RW:003/011",
						"desaKelurahan": {
							"id": 40301,
							"namaDesaKelurahan": "CIBABAT"
						},
						"kecamatan": {
							"id": 2831,
							"namaKecamatan": "Cimahi Utara"
						},
						"kotaKabupaten": {
							"id": 185,
							"namaKotaKabupaten": "Kota Cimahi"
						},
						"propinsi": {
							"id": 12
						},
						"kodePos": "40513",
						"jenisalamat": 3,
						"alamatLengkaptd": "JLN. CIHANJUANG GG.BAGJA 3 NO.88 RT/RW:003/011",
						"desaKelurahantd": {
							"id": 40301,
							"namaDesaKelurahan": "CIBABAT"
						},
						"kecamatantd": {
							"id": 2831,
							"namaKecamatan": "Cimahi Utara"
						},
						"kotaKabupatentd": {
							"id": 185,
							"namaKotaKabupaten": "Kota Cimahi"
						},
						"propinsitd": {
							"id": 12
						},
						"kodePostd": "40513",
						"jenisalamattd": 4,
						"penanggungjawab": null,
						"hubungankeluargapj": null,
						"pekerjaanpenangggungjawab": null,
						"ktppenanggungjawab": null,
						"alamatrmh": null,
						"alamatktr": null,
						"teleponpenanggungjawab": null,
						"bahasa": null,
						"jeniskelaminpenanggungjawab": null,
						"umurpenanggungjawab": null,
						"dokterpengirim": null,
						"alamatdokter": null,
						"isAlamatSama": true
					}
	- response : {
					"status": 201,
					"as": "ramdanegie",
					"messages": "Simpan Berhasil"
				 }
				 
5. Registrasi Pasien
	- api service : https://svr1.rsdarurat.com/service/medifirst2000/registrasi/save-registrasipasien	
	- Content-Type : application/json	
	- body - raw : {
						"pasiendaftar": {
							"tglregistrasi": {{Tgl Registrasi (YYYY-MM-dd HH:mm:ss)}},
							"tglregistrasidate": {{Tgl Registrasi (YYYY-MM-dd )}},
							"nocmfk": {{id pasien}},
							"objectruanganfk": {{id Ruangan daftar}},
							"objectdepartemenfk": {{id Departemen Daftar}},
							"objectkelasfk": {{id Kelas Dirawat}},
							"objectkelompokpasienlastfk": {{id Tipe Pasien}},
							"objectrekananfk": {{id rekanan penjamin pasien}},
							"tipelayanan": "1",
							"objectpegawaifk": {{id dokter penanggung Jawab}},
							"noregistrasi": "",
							"norec_pd": "",
							"israwatinap": "true",
							"statusschedule": "",
							"statuspasien": "LAMA",
							"statuscovid": "{{status covid}},
							"statuscovidfk": {{id status covid}}
						},
						"antrianpasiendiperiksa": {
							"norec_apd": "",
							"tglregistrasi": {{Tgl Registrasi (YYYY-MM-dd HH:mm:ss)}},
							"objectruanganfk": {{id Ruangan daftar}},,
							"objectkelasfk": {{id Kelas Dirawat}},
							"objectpegawaifk": null,
							"objectkamarfk": {{id Kamar Dirawat}},
							"nobed": null,
							"objectdepartemenfk": {{id Departemen}},,
							"objectasalrujukanfk": {{id Asal Rujukan}},,
							"israwatgabung": 0
						}
					}
--------------------------------------------------------------------------------------------------------------------------------------------------
					{
						"pasiendaftar": {
							"tglregistrasi": "2020-09-17 21:51:29",
							"tglregistrasidate": "2020-09-17",
							"nocmfk": 56477,
							"objectruanganfk": 659,
							"objectdepartemenfk": 16,
							"objectkelasfk": 6,
							"objectkelompokpasienlastfk": 16,
							"objectrekananfk": null,
							"tipelayanan": "1",
							"objectpegawaifk": null,
							"noregistrasi": "",
							"norec_pd": "",
							"israwatinap": "true",
							"statusschedule": "",
							"statuspasien": "LAMA",
							"statuscovid": "TERKONFIRMASI - ASIMTOMATIK",
							"statuscovidfk": 6
						},
						"antrianpasiendiperiksa": {
							"norec_apd": "",
							"tglregistrasi": "2020-09-17 21:51:29",
							"objectruanganfk": 659,
							"objectkelasfk": 6,
							"objectpegawaifk": null,
							"objectkamarfk": 421,
							"nobed": null,
							"objectdepartemenfk": 16,
							"objectasalrujukanfk": 2,
							"israwatgabung": 0
						}
					}
		- response : {
					"status": 201,
					"as": "ramdanegie",
					"messages": "Simpan Berhasil"
				 }

6. Penerimaan Barang Supplier
	- api service : https://svr1.rsdarurat.com/service/medifirst2000/logistik/save-data-penerimaa	
	- Content-Type : application/json	
	- body - raw : {
					  "struk": {
						"nostruk": "",
						"noorder": {{nomo order}},
						"rekananfk": {{id rekanan }},
						"namarekanan": {{nama rekanan }},
						"ruanganfk": {{id ruangan }},
						"nokontrak": {{no kontrak}},
						"nofaktur": {{no faktur }},
						"tglfaktur": {{Tgl tglfaktur (YYYY-MM-dd HH:mm:ss)}},
						"tglstruk": {{Tgl tglstruk (YYYY-MM-dd HH:mm:ss)}},
						"tglorder": {{Tgl tglorder (YYYY-MM-dd HH:mm:ss)}},
						"tglrealisasi": {{Tgl tglrealisasi (YYYY-MM-dd HH:mm:ss)}},
						"tglkontrak": {{Tgl tglrealisasi (YYYY-MM-dd HH:mm:ss)}},
						"objectpegawaipenanggungjawabfk": {{id pegawai}},
						"pegawaimenerimafk": {{id pegawai}},
						"namapegawaipenerima": "-",
						"qtyproduk": {{jml produk}},
						"totalharusdibayar": {{totalharusdibayar}},
						"totalppn": 0,
						"totaldiscount": 0,
						"totalhargasatuan": {{totalhargasatuan}},
						"asalproduk": 1,
						"ruanganfkKK": {{id ruangan}},
						"tglKK": {{tglKK (YYYY-MM-dd HH:mm:ss)}},
						"pegawaifkKK": null,
						"norecsppb": "",
						"kelompoktranskasi": 35,
						"norecrealisasi": "",
						"nousulan": {{nousulan}}
						"objectmataanggaranfk": "",
						"noterima": {{noterima}},
						"noBuktiKK": "",
						"ketterima": "-",
						"jenisusulan": {{jenisusulan}},
						"jenisusulanfk": 1,
						"namapengadaan": {{namapengadaan}},
						"norecOrder": null,
						"tgljatuhtempo": {{tgljatuhtempo (YYYY-MM-dd HH:mm:ss)}}
					  },
					  "details": [
						{
						  "no": 1,
						  "hargasatuan": {{hargasatuan}},
						  "ruanganfk": {{ruanganfk}},
						  "asalprodukfk": {{asalprodukfk}},
						  "asalproduk": {{asalproduk}},
						  "produkfk": {{produkfk}},
						  "namaproduk": {{namaproduk}},
						  "nilaikonversi": 1,
						  "satuanstandarfk": {{satuanstandarfk}},
						  "satuanstandar": {{satuanstandar}},
						  "satuanviewfk": {{satuanviewfk}},
						  "satuanview": {{satuanview}},
						  "jumlah": {{jumlah}},
						  "hargadiscount": {{hargadiscount}},
						  "persendiscount": {{persendiscount}},
						  "ppn": {{ppn}},
						  "persenppn": {{persenppn}},
						  "total": {{total}},
						  "keterangan": {{keterangan}},
						  "nobatch": {{nobatch}},
						  "tglkadaluarsa": {{tglkadaluarsa}}
						},
						{
						  "no": 2,
						  "hargasatuan": {{hargasatuan}},
						  "ruanganfk": {{ruanganfk}},
						  "asalprodukfk": {{asalprodukfk}},
						  "asalproduk": {{asalproduk}},
						  "produkfk": {{produkfk}},
						  "namaproduk": {{namaproduk}},
						  "nilaikonversi": 1,
						  "satuanstandarfk": {{satuanstandarfk}},
						  "satuanstandar": {{satuanstandar}},
						  "satuanviewfk": {{satuanviewfk}},
						  "satuanview": {{satuanview}},
						  "jumlah": {{jumlah}},
						  "hargadiscount": {{hargadiscount}},
						  "persendiscount": {{persendiscount}},
						  "ppn": {{ppn}},
						  "persenppn": {{persenppn}},
						  "total": {{total}},
						  "keterangan": {{keterangan}},
						  "nobatch": {{nobatch}},
						  "tglkadaluarsa": {{tglkadaluarsa}}
						}
					  ]
					}	
---------------------------------------------------------------------------------------------------------------------------------------
					{
					 "struk": {
						"nostruk": "",
						"noorder": "Tes-00001",
						"rekananfk": 18585370,
						"namarekanan": "PARIT PADANG GLOBAL, PT",
						"ruanganfk": 657,
						"nokontrak": "Tes-00001",
						"nofaktur": "PB/09-20/APT/00001",
						"tglfaktur": "2020-09-17T15:23:48.770Z",
						"tglstruk": "2020-09-17T15:23:48.770Z",
						"tglorder": "2020-09-17 22:23",
						"tglrealisasi": "2020-09-17T15:23:48.770Z",
						"tglkontrak": "2020-09-17 22:23",
						"objectpegawaipenanggungjawabfk": 24377,
						"pegawaimenerimafk": 320261028,
						"namapegawaipenerima": "-",
						"qtyproduk": 2,
						"totalharusdibayar": 29000,
						"totalppn": 0,
						"totaldiscount": 0,
						"totalhargasatuan": 29000,
						"asalproduk": 1,
						"ruanganfkKK": 657,
						"tglKK": "2020-09-17T15:23:48.770Z",
						"pegawaifkKK": null,
						"norecsppb": "",
						"kelompoktranskasi": 35,
						"norecrealisasi": "",
						"nousulan": "Tes-00001",
						"objectmataanggaranfk": "",
						"noterima": "RS/2009/00001",
						"noBuktiKK": "",
						"ketterima": "-",
						"jenisusulan": "Medis",
						"jenisusulanfk": 1,
						"namapengadaan": "Obat Alkes",
						"norecOrder": null,
						"tgljatuhtempo": "2020-09-17T15:23:48.770Z"
					  },
					  "details": [
						{
						  "no": 1,
						  "hargasatuan": "1000",
						  "ruanganfk": 657,
						  "asalprodukfk": 1,
						  "asalproduk": "Badan Layanan Umum",
						  "produkfk": 28266,
						  "namaproduk": "Ambroxol HCl 30 mg Tablet",
						  "nilaikonversi": 1,
						  "satuanstandarfk": 335,
						  "satuanstandar": "TABLET",
						  "satuanviewfk": 335,
						  "satuanview": "TABLET",
						  "jumlah": "20",
						  "hargadiscount": "0",
						  "persendiscount": "0",
						  "ppn": "0",
						  "persenppn": "10",
						  "total": 20000,
						  "keterangan": null,
						  "nobatch": "-",
						  "tglkadaluarsa": "2020-09-17T15:28:59.092Z"
						},
						{
						  "no": 2,
						  "hargasatuan": "1500",
						  "ruanganfk": 657,
						  "asalprodukfk": 1,
						  "asalproduk": "Badan Layanan Umum",
						  "produkfk": 28181,
						  "namaproduk": "Ekacetol (Paracetamol) 120 mg/5 mL Syrup 60mL",
						  "nilaikonversi": 1,
						  "satuanstandarfk": 339,
						  "satuanstandar": "BOTOL",
						  "satuanviewfk": 339,
						  "satuanview": "BOTOL",
						  "jumlah": "6",
						  "hargadiscount": "0",
						  "persendiscount": "0",
						  "ppn": "0",
						  "persenppn": "0",
						  "total": 9000,
						  "keterangan": null,
						  "nobatch": "-",
						  "tglkadaluarsa": "2020-09-17T15:29:36.524Z"
						}
					  ]
					}
		- response : {
					"status": 201,
					"as": "ramdanegie",
					"messages": "Simpan Berhasil"
				 }
