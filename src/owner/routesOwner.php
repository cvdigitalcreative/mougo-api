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

// OWNER
// GET Event
$app->get('/owner/event/', function ($request, $response, $args) {
    $getevent = new Owner(null,null);
    $getevent->setDb($this->db);
    $event = $getevent->getEvent();
    if(empty($event)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }
   
    foreach($event as $index => $value){
        $tanggal = $value['tanggal_event'];
        $timestamp = strtotime($tanggal);
        $timestamp = date("d-m-Y", $timestamp);
        $event[$index]['tanggal_event'] = $timestamp;
    }
    return $response->withJson(['status' => 'Success' , 'data' => $event ], SERVER_OK);
});

// OWNER
// GET Driver
$app->get('/owner/driver/', function ($request, $response, $args) {
    $getdriver = new Umum();
    $getdriver->setDb($this->db);
    $driver = $getdriver->getAllDriver();
    if(empty($driver)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }
    $dataDriver = [];
    for ($i=0; $i < count($driver); $i++) { 
    $dataDriver[$i]['id_user'] = $driver[$i]['id_user'];
    $dataDriver[$i]['nama'] = $driver[$i]['nama'];
    $dataDriver[$i]['email'] = $driver[$i]['email'];
    $dataDriver[$i]['no_telpon'] = $driver[$i]['no_telpon'];
    $dataDriver[$i]['no_ktp'] = $driver[$i]['no_ktp'];
    $dataDriver[$i]['no_polisi'] = $driver[$i]['no_polisi'];
    $dataDriver[$i]['alamat_domisili'] = $driver[$i]['alamat_domisili'];
    $dataDriver[$i]['cabang'] = $driver[$i]['cabang'];
    $dataDriver[$i]['jenis_kendaraan'] = $driver[$i]['jenis_kendaraan'];
    $dataDriver[$i]['merk_kendaraan'] = $driver[$i]['merk_kendaraan'];
    $dataDriver[$i]['foto_ktp'] = $driver[$i]['foto_ktp'];
    $dataDriver[$i]['foto_kk'] = $driver[$i]['foto_kk'];
    $dataDriver[$i]['foto_sim'] = $driver[$i]['foto_sim'];
    $dataDriver[$i]['foto_skck'] = $driver[$i]['foto_skck'];
    $dataDriver[$i]['foto_stnk'] = $driver[$i]['foto_stnk'];
    $dataDriver[$i]['foto_diri'] = $driver[$i]['foto_diri'];   
    }
    return $response->withJson(['status' => 'Success' , 'data' => $dataDriver ], SERVER_OK);
});
