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

    for($i = 0 ; $i < count($topup) ; $i++ ){
        $topup[$i]['nama'] = decrypt($topup[$i]['nama'],MOUGO_CRYPTO_KEY);
        $topup[$i]['email'] = decrypt($topup[$i]['email'],MOUGO_CRYPTO_KEY);
        
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->counts(), 'recordsFiltered' => count($topup), 'data' => $topup], SERVER_OK);
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
        return $response->withJson(['status' => 'Error', 'message' => 'Topup Tidak Ditemukan'], SERVER_OK);
    }

    for($i = 0 ; $i < count($topup) ; $i++ ){
        $topup[$i]['nama'] = decrypt($topup[$i]['nama'],MOUGO_CRYPTO_KEY);
        $topup[$i]['email'] = decrypt($topup[$i]['email'],MOUGO_CRYPTO_KEY);
        $topup[$i]['no_telpon'] = decrypt($topup[$i]['no_telpon'],MOUGO_CRYPTO_KEY);
        $topup[$i]['no_polisi'] = decrypt($topup[$i]['no_polisi'],MOUGO_CRYPTO_KEY);
        $topup[$i]['alamat_domisili'] = decrypt($topup[$i]['alamat_domisili'],MOUGO_CRYPTO_KEY);
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $admin->counts(), 'recordsFiltered' => count($topup), 'data' => $topup], SERVER_OK);
});

// ADMIN Accept Driver
$app->put('/admin/driver/accept/{id_user}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->editDriverStatus($args['id_user'], STATUS_DRIVER_AKTIF), SERVER_OK);
});
