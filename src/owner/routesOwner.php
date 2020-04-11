<?php
require_once dirname(__FILE__) . '/../entity/Admin.php';
require_once dirname(__FILE__) . '/../entity/Owner.php';

// REGISTER ADMIN
$app->post('/admin/register/', function ($request, $response) {
    $data_admin = $request->getParsedBody();
    $admin = new Admin($data_admin['email'], $data_admin['nama'], $data_admin['password'], $data_admin['no_telpon']);
    $admin->setDb($this->db);
    return $response->withJson($admin->registerAdmin(), SERVER_OK);
});

// OWNER LOGIN
$app->post('/owner/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner($data['email'], $data['password']);
    $owner->setDb($this->db);
    return $response->withJson($owner->loginOwner(), SERVER_OK);
});

// OWNER EVENT
$app->post('/owner/event/', function ($request, $response) {
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    $event = $owner->getEvent();
    if (count($event) >= EVENT_MAKSIMAL) {
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Event Telah Penuh'], SERVER_OK);
    }
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file) || empty($data['judul']) || empty($data['deskripsi'])) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
            return $response->withJson(['status' => 'Error', 'message' => 'Gambar Event Harus JPG atau PNG'], SERVER_OK);
        }
        $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
        $directory = $this->get('settings')['upload_directory2'];
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $path_name = "../assets-event/" . $filename;

    }return $response->withJson($owner->inputEvent($data['judul'], $data['deskripsi'], $path_name), SERVER_OK);

});
