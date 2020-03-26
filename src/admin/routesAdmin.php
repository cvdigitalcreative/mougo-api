<?php
require_once dirname(__FILE__) . '/../entity/Admin.php';

// ADMIN LOGIN
$app->post('/admin/login/',function($request,$response){
    $data = $request->getParsedBody();
    $admin = new Admin($data['email'],null,$data['password'],null);
    $admin->setDb($this->db);
    return $response->withJson($admin->loginAdmin(), SERVER_OK);
});

// ADMIN GET ALL TOPUP DAN BUKTI PEMBAYARAN
$app->get('/admin/topup/',function($request,$response){
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->getAllTopUp(), SERVER_OK);
});

// ADMIN Accept Konfirmasi Pembayaran
$app->put('/admin/topup/accept/{id_topup}',function($request,$response,$args){
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->topupUpdate($args['id_topup'],TOPUP_ACCEPT), SERVER_OK);
});

// ADMIN Reject Konfirmasi Pembayaran
$app->put('/admin/topup/reject/{id_topup}',function($request,$response,$args){
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->topupUpdate($args['id_topup'],TOPUP_REJECT), SERVER_OK);
});
