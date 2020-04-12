<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/User.php';
require_once dirname(__FILE__) . '/../entity/Trip.php';
require_once dirname(__FILE__) . '/../entity/Profile.php';

//Customer
//REGISTER
$app->post('/customer/register/', function ($request, $response) {
    $user = $request->getParsedBody();
    $userData = new User($user['nama'], $user['email'], $user['no_telpon'], $user['password'], $user['kode_referal'], $user['kode_sponsor']);
    $userData->setDB($this->db);
    $result = $userData->register(USER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

$app->post('/customer/konfirmasi/register/{id_user}', function ($request, $response, $args) {
    $id = $args['id_user'];
    $userKonfirmasi = new User(null, null, null, null, null, null);
    $userKonfirmasi->setDb($this->db);
    $hasil = $userKonfirmasi->konfirmasiSelesai($id);
    return $response->withJson($hasil, SERVER_OK);
});

//Customer
//LOGIN
$app->post('/customer/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $user = new User(null, $data['emailTelpon'], $data['emailTelpon'], $data['password'], null, null);
    $user->setDB($this->db);
    $result = $user->login(USER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

// Customer
// PROFILE
$app->put('/customer/profile/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $profile = new Profile($args['id_user'], $data['no_ktp'], $data['provinsi'], $data['kota'], $data['bank'], $data['no_rekening'], $data['atas_nama_bank'], null, null);
    $profile->setDb($this->db);
    return $response->withJson($profile->inputProfile(PROFILE), SERVER_OK);
})->add($tokenCheck);

// Customer
// PROFILE
$app->get('/customer/profile/{id_user}', function ($request, $response, $args) {
    $profile = new Profile(null, null, null, null, null, null, null, null, null);
    $profile->setDb($this->db);
    $data = $profile->getDetailUser($args['id_user']);
    if(empty($data)){
        return $response->withJson(['status' => 'Error', 'message' => 'Profile Driver Tidak Ditemukan'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Success', 'data' => $data], SERVER_OK);
})->add($tokenCheck);

// Customer
// USER
$app->put('/customer/user/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $profile = new User($data['nama'], $data['email'], $data['no_telpon'], $data['password'],  null, null);
    $profile->setId_user($args['id_user']);
    $profile->setDb($this->db);
    if(empty($profile->cekEditUserPassword($args['id_user'],$data['konfirmasi_password']))){
        return $response->withJson(['status' => 'Error', 'message' => 'Konfirmasi Password Anda Salah'], SERVER_OK);
    }
    return $response->withJson($profile->editUser(), SERVER_OK);
})->add($tokenCheck);

//Customer
//Trip
$app->post('/customer/trip/', function ($request, $response) {
    $body = $request->getParsedBody();
    $user = new Trip($body['id_user'], null, $body['total_harga'], $body['alamat_jemput'], $body['lat_jemput'], $body['long_jemput'], $body['alamat_destinasi'], $body['lat_destinasi'], $body['long_destinasi'], $body['jarak'], null, $body['jenis_trip'], STATUS_MENCARI_DRIVER, $body['jenis_pembayaran']);
    $user->setDB($this->db);
    $result = $user->order_trip();
    return $response->withJson($result, SERVER_OK);
})->add($tokenCheck);

// Customer
// Cek trip
$app->get('/customer/trip/cek/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $trip_cek = new Umum();
    $trip_cek->setDb($this->db);
    return $response->withJson($trip_cek->getCekTripStatusCustomer($id), SERVER_OK);
})->add($tokenCheck);

// Customer
// Cancel Trip
$app->post('/customer/trip/cancel/', function ($request, $response) {
    $id = $request->getParsedBody();
    $trip_cek = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    $trip_cek->setDb($this->db);
    return $response->withJson($trip_cek->cancelOrder($id['id_trip']), SERVER_OK);
})->add($tokenCheck);

// Customer
// POSITION
$app->get('/customer/trip/position/{id_driver}', function ($request, $response, $args) {
    $id = $args['id_driver'];
    $id_trip = $request->getQueryParam("id_trip");
    $position = new Umum();
    $position->setDb($this->db);
    return $response->withJson($position->getPosition($id, $id_trip), SERVER_OK);
})->add($tokenCheck);
