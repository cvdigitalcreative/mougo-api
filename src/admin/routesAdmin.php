<?php
require_once dirname(__FILE__) . '/../entity/Admin.php';

// ADMIN LOGIN
$app->post('/admin/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin($data['email'], null, $data['password'], null);
    $admin->setDb($this->db);
    return $response->withJson($admin->loginAdmin(), SERVER_OK);
});

// ADMIN GET ALL TOPUP DAN BUKTI PEMBAYARAN
$app->post('/admin/topup/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    $topup = $admin->getTopupWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($topup)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Topup Tidak Ditemukan'], SERVER_OK);
    }

    $data_user = [];
    for ($i = 0; $i < count($topup); $i++) {
        $data_user[$i]['id_topup'] = $topup[$i]['id_topup'];
        $data_user[$i]['nama'] = decrypt($topup[$i]['nama'], MOUGO_CRYPTO_KEY);
        $data_user[$i]['email'] = decrypt($topup[$i]['email'], MOUGO_CRYPTO_KEY);
        $data_user[$i]['jumlah_topup'] = $topup[$i]['jumlah_topup'];
        $data_user[$i]['foto_transfer'] = $topup[$i]['foto_transfer'];
        $data_user[$i]['tanggal_transfer'] = $topup[$i]['tanggal_transfer'];

    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsTopup(), 'recordsFiltered' => count($topup), 'data' => $data_user], SERVER_OK);
});

// ADMIN Accept Konfirmasi Pembayaran
$app->put('/admin/topup/accept/{id_topup}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->topupUpdate($args['id_topup'], TOPUP_ACCEPT), SERVER_OK);
});

// ADMIN Reject Konfirmasi Pembayaran
$app->put('/admin/topup/reject/{id_topup}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->topupUpdate($args['id_topup'], TOPUP_REJECT), SERVER_OK);
});

// ADMIN Semua Data Driver (Belum Konfirmasi)
$app->post('/admin/driver/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    $topup = $admin->getDriverAdminWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($topup)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }

    $dataDriver = [];
    for ($i = 0; $i < count($topup); $i++) {
        $dataDriver[$i]['id_user'] = $topup[$i]['id_user'];
        $dataDriver[$i]['nama'] = decrypt($topup[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['email'] = decrypt($topup[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_telpon'] = decrypt($topup[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_polisi'] = decrypt($topup[$i]['no_polisi'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['alamat_domisili'] = decrypt($topup[$i]['alamat_domisili'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['cabang'] = $topup[$i]['cabang'];
        $dataDriver[$i]['jenis_kendaraan'] = $topup[$i]['jenis_kendaraan'];
        $dataDriver[$i]['merk_kendaraan'] = $topup[$i]['merk_kendaraan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsTopup(), 'recordsFiltered' => count($topup), 'data' => $dataDriver], SERVER_OK);
});

// ADMIN Data Driver (Belum Konfirmasi)
$app->post('/admin/driver/confirm/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    if (empty($admin->cekDriverConfirm($data['id']))) {
        return $response->withJson(['status' => 'Error', 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }
    $driver = $admin->getDriverConfirm($data['id']);
    if (empty($driver)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Driver Belum Memenuhi Syarat'], SERVER_OK);
    }

    $dataDriver = [];
    $dataDriver['id_user'] = $driver['id_user'];
    $dataDriver['nama'] = $driver['nama'];
    $dataDriver['email'] = $driver['email'];
    $dataDriver['no_telpon'] = $driver['no_telpon'];
    $dataDriver['no_ktp'] = $driver['no_ktp'];
    $dataDriver['no_polisi'] = $driver['no_polisi'];
    $dataDriver['alamat_domisili'] = $driver['alamat_domisili'];
    $dataDriver['cabang'] = $driver['cabang'];
    $dataDriver['jenis_kendaraan'] = $driver['jenis_kendaraan'];
    $dataDriver['merk_kendaraan'] = $driver['merk_kendaraan'];
    $dataDriver['foto_ktp'] = $driver['foto_ktp'];
    $dataDriver['foto_kk'] = $driver['foto_kk'];
    $dataDriver['foto_sim'] = $driver['foto_sim'];
    $dataDriver['foto_skck'] = $driver['foto_skck'];
    $dataDriver['foto_stnk'] = $driver['foto_stnk'];
    $dataDriver['foto_diri'] = $driver['foto_diri'];

    return $response->withJson(['status' => 'Success', 'data' => $dataDriver], SERVER_OK);
});

// ADMIN Accept Driver
$app->put('/admin/driver/accept/{id_user}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->editDriverStatus($args['id_user'], STATUS_DRIVER_AKTIF), SERVER_OK);
});

// ADMIN Reject Driver
$app->put('/admin/driver/reject/{id_user}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->rejectDriver($args['id_user']), SERVER_OK);
});

// ADMIN Tambah Keterangan Bantuan
$app->post('/admin/bantuan/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->insertBantuanUser(ID_DRIVER_SILUMAN, $data['pesan_bantuan'], $data['jawaban']), SERVER_OK);
});

// ADMIN Jawab Keterangan Bantuan
$app->put('/admin/bantuan/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->jawabBantuanAdmin($args['id'], $data['jawaban']), SERVER_OK);
});

// ADMIN GET ALL TOPUP DAN BUKTI PEMBAYARAN
$app->post('/admin/bantuan/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    $bantuan = $admin->getBantuanWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bantuan)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bantuan Tidak Ditemukan'], SERVER_OK);
    }

    $data_user = [];
    for ($i = 0; $i < count($bantuan); $i++) {
        $data_user[$i]['id_bantuan'] = $bantuan[$i]['id_bantuan'];
        $data_user[$i]['nama'] = decrypt($bantuan[$i]['nama'], MOUGO_CRYPTO_KEY);
        $data_user[$i]['pertanyaan'] = $bantuan[$i]['pertanyaan'];
        $data_user[$i]['tanggal_bantuan'] = $bantuan[$i]['tanggal_bantuan'];

    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsBantuan(), 'recordsFiltered' => count($bantuan), 'data' => $data_user], SERVER_OK);
});