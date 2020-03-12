<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/Driver.php';

//Driver
//REGISTER
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

//Driver
//LOGIN
$app->post('/driver/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $user = new User(null, $data['emailTelpon'], $data['emailTelpon'], $data['password'], null, null);
    $user->setDB($this->db);
    $result = $user->login(DRIVER_ROLE);
    return $response->withJson($result, SERVER_OK);
});
