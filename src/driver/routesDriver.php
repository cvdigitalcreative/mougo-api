<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/Driver.php';

// Driver
// REGISTER
$app->post('/driver/register/', function ($request, $response) {
    $user = $request->getParsedBody();

    $userData = new Driver($user['no_polisi'], $user['cabang'], $user['alamat_domisili'], $user['merk_kendaraan'], $user['jenis_kendaraan']);
    $userData->setDB($this->db);
    $userData->setUser($user['nama'], $user['email'], $user['no_telpon'], $user['password'], $user['kode_referal'], $user['kode_sponsor']);
    if (!$userData->driverData()) {
        return $response->withJson(['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $resultUser = $userData->register(DRIVER_ROLE);
    if ($resultUser['status'] == "Error") {
        return $response->withJson($resultUser, SERVER_OK);
    }
    $resultDriver = $userData->driverRegis();

    if ($resultDriver['status'] == "Error") {
        return $response->withJson($resultDriver, SERVER_OK);
    }

    return $response->withJson($resultUser, SERVER_OK);
});

// Driver
// LOGIN
$app->post('/driver/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $user = new User(null, $data['emailTelpon'], $data['emailTelpon'], $data['password'], null, null);
    $user->setDB($this->db);
    $result = $user->login(DRIVER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

// DRIVER
// POSITION
$app->get('/driver/position/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $position = new Umum();
    $position->setDb($this->db);
    return $response->withJson($position->getPosition($id), SERVER_OK);
})->add($tokenCheck);

// DRIVER
// SEARCH TRIP
$app->get('/driver/trip/search/', function ($request, $response) {
    $lat = $request->getQueryParam("lat");
    $long = $request->getQueryParam("long");
    $trip_search = new Umum();
    $trip_search->setDb($this->db);
    return $response->withJson($trip_search->getTemporaryTrip($lat, $long), SERVER_OK);
})->add($tokenCheck);

// DRIVER
// ACCEPT
$app->post('/driver/trip/{id_trip}', function ($request, $response, $args) {
    $id_trip = $args['id_trip'];
    $id_driver = $request->getParsedBody();
    $trip_acc = new Trip(NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
    $trip_acc->setDb($this->db);
    $data_trip = $trip_acc->getTemporaryOrderDetail($id_trip);
    if(empty($data_trip)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Order Tidak Ada Atau Telah Diambil'], SERVER_OK);
    }
    if(!$trip_acc->deleteTemporaryOrderDetail($id_trip)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Gagal Menghapus Data'], SERVER_OK);
    }
    if(!$trip_acc->driverTerimaOrder($id_driver['id_driver'],$data_trip)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Gagal Menghapus Data'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Success' , 'data' =>$data_trip ], SERVER_OK);
})->add($tokenCheck);
