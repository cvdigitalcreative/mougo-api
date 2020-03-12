<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/User.php';

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
    $user = new User(null, $data['emailTelpon'], $data['emailTelpon'], $data['password'], null, null);
    $user->setDB($this->db);
    $result = $user->login(USER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

//Customer
//Trip
$app->post('/customer/trip/', function ($request, $response) {

});
