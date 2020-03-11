<?php
require_once(dirname(__FILE__).'/../randomGen.php');
require_once(dirname(__FILE__).'/../entity/User.php');

//Customer
//REGISTER
$app->post('/customer/register/', function ($request, $response) {
    $user = $request->getParsedBody();

    $userRole = 1;
    $userData = new User($user['nama'],$user['email'],$user['no_telpon'],$user['password'],$user['kode_referal'],$user['kode_sponsor']);
    $userData->setDB($this->db);
    $result = $userData->register($userRole);
    return $response->withJson($result,200);
});

//Customer
//LOGIN
$app->post('/customer/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $userRole = 1;
    $user = new User(NULL,$data['emailTelpon'],$data['emailTelpon'],$data['password'],NULL,NULL);
    $user->setDB($this->db);
    $result = $user->login($userRole);
    return $response->withJson($result,200);
});

//Customer
//Trip
$app->post('/customer/trip/',function($request,$response){
    
});
