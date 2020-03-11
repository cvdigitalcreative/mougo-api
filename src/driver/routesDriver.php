<?php
require_once(dirname(__FILE__).'/../randomGen.php');
require_once(dirname(__FILE__).'/../entity/User.php');

//Driver
//REGISTER
$app->post('/driver/register/', function ($request, $response) {
    $user = $request->getParsedBody();

    $userRole = 2;
    $userDriver = [
        "no_polisi" => $user['no_polisi'],
        "cabang" => $user['cabang'],
        "alamat_domisili" => $user['alamat_domisili'],
        "merk_kendaraan" => $user['merk_kendaraan'],
        "jenis_kendaraan" => $user['jenis_kendaraan'],
    ];
    $userData = new User($user['nama'],$user['email'],$user['no_telpon'],$user['password'],$user['kode_referal'],$user['kode_sponsor']);
    $userData->regDriver($userDriver);
    $userData->setDB($this->db);

    $result = $userData->register($userRole);
    return $response->withJson($result,200);$data = $request->getParsedBody();

});

//Driver
//LOGIN
$app->post('/driver/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $userRole = 2;
    $user = new User(NULL,$data['emailTelpon'],$data['emailTelpon'],$data['password'],NULL,NULL);
    $user->setDB($this->db);
    $result = $user->login($userRole);
    return $response->withJson($result,200);
});
