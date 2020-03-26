<?php
require_once dirname(__FILE__) . '/../entity/Admin.php';

$app->post('/admin/register/',function($request,$response){
    $data_admin = $request->getParsedBody();
    $admin = new Admin($data_admin['email'],$data_admin['nama'],$data_admin['password'],$data_admin['no_telpon']);
    $admin->setDb($this->db);
    return $response->withJson($admin->registerAdmin(),SERVER_OK);
});




?>