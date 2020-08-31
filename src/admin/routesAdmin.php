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

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsTopup(), 'recordsFiltered' => $admin->countsTopup(), 'data' => $data_user], SERVER_OK);
});

// ADMIN Accept Konfirmasi Pembayaran
$app->put('/admin/topup/accept/{id_topup}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->topupUpdate($args['id_topup'], TOPUP_ACCEPT, $data['email_admin']), SERVER_OK);
});

// ADMIN Reject Konfirmasi Pembayaran
$app->put('/admin/topup/reject/{id_topup}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->topupUpdate($args['id_topup'], TOPUP_REJECT, $data['email_admin']), SERVER_OK);
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

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsTopup(), 'recordsFiltered' => $admin->countsTopup(), 'data' => $dataDriver], SERVER_OK);
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
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->updateDriverStatus($args['id_user'], STATUS_DRIVER_AKTIF, $data['email_admin']), SERVER_OK);
});

// ADMIN Reject Driver
$app->put('/admin/driver/reject/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->rejectDriver($args['id_user'], $data['email_admin']), SERVER_OK);
});

// ADMIN Tambah Keterangan Bantuan
$app->post('/admin/bantuan/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->insertBantuanUser(ID_DRIVER_SILUMAN, $data['pesan_bantuan'], $data['jawaban']), SERVER_OK);
});

// ADMIN Jawab Keterangan Bantuan
$app->post('/admin/bantuan/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->jawabBantuanAdmin($args['id'], $data['jawaban'], $data['email_admin']), SERVER_OK);
});

// ADMIN GET ALL BANTUAN
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

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsBantuan(), 'recordsFiltered' => $admin->countsBantuan(), 'data' => $data_user], SERVER_OK);
});

// ADMIN GET ALL BANTUAN LIST
$app->post('/admin/bantuan/list/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    $bantuan = $admin->getBantuanList($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bantuan)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bantuan Tidak Ditemukan'], SERVER_OK);
    }

    $data_user = [];
    for ($i = 0; $i < count($bantuan); $i++) {
        $data_user[$i]['id_bantuan'] = $bantuan[$i]['id_bantuan'];
        $data_user[$i]['pertanyaan'] = $bantuan[$i]['pertanyaan'];
        $data_user[$i]['jawaban'] = $bantuan[$i]['jawaban'];
        $data_user[$i]['tanggal_bantuan'] = $bantuan[$i]['tanggal_bantuan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsBantuanList(), 'recordsFiltered' => $admin->countsBantuanList(), 'data' => $data_user], SERVER_OK);
});

// DELETE ALL BANTUAN LIST
$app->delete('/admin/bantuan/list/{id}', function ($request, $response, $args) {
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->deleteBantuanList($args['id']), SERVER_OK);
});

// EDIT ALL BANTUAN LIST
$app->put('/admin/bantuan/list/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->updateBantuanList($args['id'], $data['pertanyaan'], $data['jawaban']), SERVER_OK);
});

// ADMIN GET ALL WITHDRAW
$app->post('/admin/withdraw/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    $withdraw = $admin->getWithdrawWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($withdraw)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Withdraw Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($withdraw); $i++) {
        $withdraw[$i]['nama'] = decrypt($withdraw[$i]['nama'], MOUGO_CRYPTO_KEY);
        $withdraw[$i]['jumlah'] = (double) $withdraw[$i]['jumlah'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsWithdraw(), 'recordsFiltered' => $admin->countsWithdraw(), 'data' => $withdraw], SERVER_OK);
});

// ADMIN Accept withdraw Transfer
$app->put('/admin/withdraw/accept/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->adminKonfirmasiWithdraw($args['id'], STATUS_WITHDRAW_SUCCESS, $data['email']), SERVER_OK);
});

// ADMIN Reject withdraw Transfer
$app->put('/admin/withdraw/reject/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->adminKonfirmasiWithdraw($args['id'], STATUS_WITHDRAW_REJECT, $data['email']), SERVER_OK);
});

// ADMIN GET ALL EMERGENCY
$app->post('/admin/emergency/list/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);

    $emergency = $admin->getEmergencyWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($emergency)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bantuan Tidak Ditemukan'], SERVER_OK);
    }

    $data_user = [];
    for ($i = 0; $i < count($emergency); $i++) {
        $data_user[$i]['nama'] = decrypt($emergency[$i]['nama'], MOUGO_CRYPTO_KEY);
        $data_user[$i]['no_telpon'] = decrypt($emergency[$i]['no_telpon'], MOUGO_CRYPTO_KEY);;
        $data_user[$i]['tanggal_emergency'] = $emergency[$i]['tanggal_emergency'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->countsEmergency(), 'recordsFiltered' => $admin->countsEmergency(), 'data' => $data_user], SERVER_OK);
});

// ADMIN REKAP DASBOR
$app->get('/admin/rekap/dasbor/', function ($request, $response) {
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->adminRekapDasbor(), SERVER_OK);
});

// ADMIN REKAP DRIVER
$app->get('/admin/rekap/driver/', function ($request, $response) {
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    $data = $admin->getKonfirmasiDriver();
    return $response->withJson(['status' => 'Success', 'datas' => $data['jumlah_konfirmasi_driver']], SERVER_OK);
});

// NOMOR MOUGO
// GET NOMOR MOUGO
$app->get('/nomor/mougo/', function ($request, $response) {
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->adminGetNomorMougo(), SERVER_OK);
});

// MENGUPDATE NOMOR MOUGO
$app->put('/nomor/mougo/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->adminEditNomorMougo($data['nomor_mougo']), SERVER_OK);
});

// KONTAK MOUGO
// GET KONTAK MOUGO
$app->get('/kontak/mougo/', function ($request, $response) {
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->adminGetKontakMougo(), SERVER_OK);
});

// MENGUPDATE KONTAK MOUGO
$app->put('/kontak/mougo/', function ($request, $response) {
    $data = $request->getParsedBody();
    $admin = new Admin(null, null, null, null);
    $admin->setDb($this->db);
    return $response->withJson($admin->adminEditKontakMougo($data['kontak_mougo']), SERVER_OK);
});
