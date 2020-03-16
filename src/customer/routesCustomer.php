<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/User.php';
require_once dirname(__FILE__) . '/../entity/Trip.php';

//Customer
//REGISTER
$app->post('/customer/register/', function ($request, $response) {
    $user = $request->getParsedBody();
    $userData = new User($user['nama'], $user['email'], $user['no_telpon'], $user['password'], $user['kode_referal'], $user['kode_sponsor']);
    $userData->setDB($this->db);
    $result = $userData->register(USER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

//Customer
//LOGIN
$app->post('/customer/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $user = new User(NULL, $data['emailTelpon'], $data['emailTelpon'], $data['password'], NULL, NULL);
    $user->setDB($this->db);
    $result = $user->login(USER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

//Customer
//Trip
$app->post('/customer/trip/', function ($request, $response) {
    $body = $request->getParsedBody();
    $user = new Trip($body['id_user'],NULL,$body['total_harga'],$body['alamat_jemput'],$body['lat_jemput'],$body['long_jemput'],$body['alamat_destinasi'],$body['lat_destinasi'],$body['long_destinasi'],$body['jarak'],NULL,$body['jenis_trip'],STATUS_MENCARI_DRIVER,$body['jenis_pembayaran']);
    $user->setDB($this->db);
    $result = $user->order_trip();
    return $response->withJson($result, SERVER_OK);
})->add($tokenCheck);

// Customer
// Cek trip
$app->get('/customer/trip/cek/{id}', function ($request,$response,$args){
    $id = $args['id'];
    $trip_cek = new Umum();
    $trip_cek->setDb($this->db);
    return $response->withJson($trip_cek->getCekTripStatusCustomer($id), SERVER_OK);
})->add($tokenCheck);