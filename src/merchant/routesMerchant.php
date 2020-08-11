<?php

require_once dirname(__FILE__) . '/../controller/Merchant.php';

// PENDAFTARAN MERCHANT UKM
$app->post('/merchant/register', function ($request, $response) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(registrasiMerchant($this->db, $data['email'], $data['nama'], $data['no_telpon'], $data['password'], $data['kode_referal'], $data['kode_sponsor'], $data['no_ktp'], $data['bank'], $data['no_rekening'], $data['atas_nama_bank'], $data['nama_usaha'], $data['alamat_usaha'], $data['no_telpon_kantor'], $data['no_izin'], $data['no_fax'], $data['nama_direktur'], $data['url_web_aplikasi'], $data['lama_bisnis'], $data['omset_perbulan'], $data['kategori_bisnis'], $uploadedFiles,  $this->get('settings')['upload_dir_foto_ktp'], $this->get('settings')['upload_dir_foto_izin'], $this->get('settings')['upload_dir_foto_rekening'], $this->get('settings')['upload_dir_foto_banner']), SERVER_OK);
});

// KONFIRMASI EMAIL MERCHANT UKM
$app->post('/merchant/konfirmasi/{id_user}', function ($request, $response, $args) {
    $id = $args['id_user'];
    $userKonfirmasi = new User(null, null, null, null, null, null);
    $userKonfirmasi->setDb($this->db);
    $hasil = $userKonfirmasi->konfirmasiSelesai($id);
    return $response->withJson($hasil, SERVER_OK);
});