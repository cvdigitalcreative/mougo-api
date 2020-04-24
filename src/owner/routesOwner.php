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
    if (empty($uploadedFiles['gambar']->file) || empty($data['judul']) || empty($data['deskripsi']) || empty($data['tanggal_event'])) {
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
        if(!is_dir($directory)){
            mkdir($directory, 0755, true);
        }
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $path_name = "../assets-event/" . $filename;

    }return $response->withJson($owner->inputEvent($data['judul'], $data['deskripsi'], $path_name, $data['tanggal_event']), SERVER_OK);

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
    $dataDriver[$i]['nama'] = decrypt($driver[$i]['nama'],MOUGO_CRYPTO_KEY);
    $dataDriver[$i]['no_polisi'] =  decrypt($driver[$i]['no_polisi'],MOUGO_CRYPTO_KEY);
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

// OWNER
// GET Customer
$app->get('/owner/customer/', function ($request, $response) {
    $getcustomer = new Umum();
    $getcustomer->setDb($this->db);
    $customer = $getcustomer->getAllCustomer();
    if(empty($customer)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }
    $dataCustomer = [];
    for ($i=0; $i < count($customer); $i++) { 
    $dataCustomer[$i]['id_user'] = $customer[$i]['id_user'];
    $dataCustomer[$i]['nama'] = decrypt($customer[$i]['nama'],MOUGO_CRYPTO_KEY);
    $dataCustomer[$i]['provinsi'] = $customer[$i]['provinsi'];
    $dataCustomer[$i]['kota'] = $customer[$i]['kota']; 
    $dataCustomer[$i]['kode_referal'] = $customer[$i]['kode_referal'];
    $dataCustomer[$i]['kode_sponsor'] = $customer[$i]['kode_sponsor']; 
    }
    return $response->withJson(['status' => 'Success' , 'data' => $dataCustomer ], SERVER_OK);
});

// OWNER
// GET Admin
$app->get('/owner/admin/', function ($request, $response, $args) {
    $getadmin = new Owner(null,null);
    $getadmin->setDb($this->db);
    $admin = $getadmin->getAdmin();
    if(empty($admin)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Admin Tidak Ditemukan'], SERVER_OK);
    }

    return $response->withJson(['status' => 'Success' , 'data' => $admin ], SERVER_OK);
});

// OWNER
// DELETE Event
$app->delete('/owner/event/{id}', function ($request, $response, $args) {
    $getevent = new Owner(null,null);
    $getevent->setDb($this->db);
    $event = $getevent->cekEvent($args['id']);
    if(empty($event)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }

    if($getevent->deleteEvent($args['id'])){
        return $response->withJson(['status' => 'Success' , 'message' => 'Event Berhasil Dihapus' ], SERVER_OK);

    }
    return $response->withJson(['status' => 'Error' , 'message' => 'Event Gagal Dihapus' ], SERVER_OK);
});

// OWNER
// EDIT Event
$app->post('/owner/event/{id}', function ($request, $response, $args) {
    $getevent = new Owner(null,null);
    $getevent->setDb($this->db);
    $event = $getevent->cekEvent($args['id']);
    if(empty($event)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }

    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    if ( empty($data['judul']) || empty($data['deskripsi']) || empty($data['tanggal_event'])) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    if(empty($uploadedFiles['gambar']->file)){
        return $response->withJson($getevent->editEvent($args['id'],$data['judul'], $data['deskripsi'], null, $data['tanggal_event']), SERVER_OK);
    }

    $uploadedFile = $uploadedFiles['gambar'];

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
            return $response->withJson(['status' => 'Error', 'message' => 'Gambar Event Harus JPG atau PNG'], SERVER_OK);
        }
        if(unlink($event['gambar_event'])){
            $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
            $directory = $this->get('settings')['upload_directory2'];
            if(!is_dir($directory)){
                mkdir($directory, 0755, true);
            }
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
            $path_name = "../assets-event/" . $filename;
        }
        
    }return $response->withJson($getevent->editEvent($args['id'],$data['judul'], $data['deskripsi'], $path_name, $data['tanggal_event']), SERVER_OK);

});