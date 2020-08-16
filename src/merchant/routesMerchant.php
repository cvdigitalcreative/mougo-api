<?php

require_once dirname(__FILE__) . '/../controller/Merchant.php';

// PENDAFTARAN MERCHANT UKM
$app->post('/merchant/register', function ($request, $response) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(registrasiMerchant($this->db, $data['email'], $data['nama'], $data['no_telpon'], $data['password'], $data['kode_referal'], $data['kode_sponsor'], $data['no_ktp'], $data['bank'], $data['no_rekening'], $data['atas_nama_bank'], $data['nama_usaha'], $data['alamat_usaha'], $data['no_telpon_kantor'], $data['no_izin'], $data['no_fax'], $data['nama_direktur'], $data['url_web_aplikasi'], $data['lama_bisnis'], $data['omset_perbulan'], $data['kategori_bisnis'], $uploadedFiles, $this->get('settings')['upload_dir_foto_ktp'], $this->get('settings')['upload_dir_foto_izin'], $this->get('settings')['upload_dir_foto_rekening'], $this->get('settings')['upload_dir_foto_banner']), SERVER_OK);
});

// MERCHANT INPUT BARANG
$app->post('/merchant/{id_user}/barang', function ($request, $response, $args) {
    $id = $args['id_user'];
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(barangMerchant($this->db, $id, $data['nama_barang'], $data['harga_barang'], $data['kategori_barang'], $uploadedFiles, $this->get('settings')['upload_dir_foto_barang']), SERVER_OK);
});

// MERCHANT LOGIN
$app->post('/merchant/login', function ($request, $response) {
    $data = $request->getParsedBody();
    $user = new User(null, $data['emailTelpon'], $data['emailTelpon'], $data['password'], null, null);
    $user->setDB($this->db);
    $result = $user->login(MERCHANT_ROLE);
    return $response->withJson($result, SERVER_OK);
});

// KONFIRMASI EMAIL MERCHANT UKM
$app->post('/merchant/{id_user}', function ($request, $response, $args) {
    $id = $args['id_user'];
    $userKonfirmasi = new User(null, null, null, null, null, null);
    $userKonfirmasi->setDb($this->db);
    $hasil = $userKonfirmasi->konfirmasiSelesai($id);
    return $response->withJson($hasil, SERVER_OK);
});

// GET MERCHANT BY ID
$app->get('/merchant/{id_user}', function ($request, $response, $args) {
    $id = $args['id_user'];
    return $response->withJson(getMerchantById($this->db, $id), SERVER_OK);
});

// GET MERCHANT
$app->get('/merchant', function ($request, $response) {
    return $response->withJson(getMerchant($this->db), SERVER_OK);
});

// KATEGORI BISNIS
// GET MERCHANT KATEGORI BISNIS
$app->get('/merchant/kategori/', function ($request, $response) {
    return $response->withJson(getMerchantKategori($this->db), SERVER_OK);
});

// MEMBUAT KATEGORI BISNIS
$app->post('/merchant/kategori/', function ($request, $response) {
    $data = $request->getParsedBody();
    return $response->withJson(insertMerchantKategoriBisnis($this->db, $data['nama_kategori']), SERVER_OK);
});

// MENGUPDATE KATEGORI BISNIS
$app->put('/merchant/kategori/{id_kategori}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    return $response->withJson(updateMerchantKategoriBisnis($this->db, $args['id_kategori'], $data['nama_kategori']), SERVER_OK);
});

// MENGUPDATE KATEGORI BISNIS
$app->delete('/merchant/kategori/{id_kategori}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    return $response->withJson(deleteMerchantKategoriBisnis($this->db, $args['id_kategori']), SERVER_OK);
});

// GET MERCHANT KATEGORI BARANG
$app->get('/merchant/barang/kategori/', function ($request, $response) {
    return $response->withJson(getMerchantKategoriBarang($this->db), SERVER_OK);
});

// MEMBUAT KATEGORI BARANG
$app->post('/merchant/barang/kategori/', function ($request, $response) {
    $data = $request->getParsedBody();
    return $response->withJson(insertMerchantKategoriBarang($this->db, $data['nama_kategori']), SERVER_OK);
});

// MENGUPDATE KATEGORI BARANG
$app->put('/merchant/barang/kategori/{id_kategori}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    return $response->withJson(updateMerchantKategoriBarang($this->db, $args['id_kategori'], $data['nama_kategori']), SERVER_OK);
});

// MENGUPDATE KATEGORI BARANG
$app->delete('/merchant/barang/kategori/{id_kategori}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    return $response->withJson(deleteMerchantKategoriBarang($this->db, $args['id_kategori']), SERVER_OK);
});
