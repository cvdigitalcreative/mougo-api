<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/Driver.php';
require_once dirname(__FILE__) . '/../entity/Umum.php';
require_once dirname(__FILE__) . '/../entity/Profile.php';

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

    return $response->withJson($resultDriver, SERVER_OK);
});

$app->post('/driver/konfirmasi/register/{id_user}', function ($request, $response, $args) {
    $id = $args['id_user'];
    $userKonfirmasi = new User(null, null, null, null, null, null);
    $userKonfirmasi->setDb($this->db);
    $hasil = $userKonfirmasi->konfirmasiSelesai($id);
    return $response->withJson($hasil, SERVER_OK);
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

// Driver
// PROFILE
$app->put('/driver/profile/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $driver = new Driver(null, null, null, null, null);
    $driver->setDb($this->db);
    $data_driver = $driver->getProfileDriver($args['id_user']);
    $ktp = $data['no_ktp'];
    if ($data_driver['status_akun_aktif'] == STATUS_DRIVER_AKTIF) {
        $ktp = null;
    }
    $profile = new Profile($args['id_user'], $ktp, $data['provinsi'], $data['kota'], $data['bank'], $data['no_rekening'], $data['atas_nama_bank'], null, null);
    $profile->setDb($this->db);
    return $response->withJson($profile->inputProfile(PROFILE_DRIVER), SERVER_OK);
})->add($tokenCheck);

// Driver
// PROFILE
$app->get('/driver/profile/{id_user}', function ($request, $response, $args) {    
    $profile = new Profile(null, null, null, null, null, null, null, null, null);
    $profile->setDb($this->db);
    $dataDriver = $profile->getAllProfile($args['id_user'],DRIVER_ROLE);
    if(empty($dataDriver)){
        return $response->withJson(['status' => 'Error', 'message' => 'Profile Driver Tidak Ditemukan'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Success', 'data' => $dataDriver], SERVER_OK);
})->add($tokenCheck);

// Driver
// USER
$app->put('/driver/user/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $profile = new User(null, $data['email'], $data['no_telpon'], $data['password'], null, null);
    $profile->setId_user($args['id_user']);
    $profile->setDb($this->db);
    if (empty($profile->cekEditUserPassword($args['id_user'], $data['konfirmasi_password']))) {
        return $response->withJson(['status' => 'Error', 'message' => 'Konfirmasi Password Anda Salah'], SERVER_OK);
    }
    return $response->withJson($profile->editUser(), SERVER_OK);
})->add($tokenCheck);

// Driver
// FOTO KTP
$app->post('/driver/foto_ktp/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoCustomer($args['id_user']);
    if($data['foto_ktp']!='-'){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_ktp'];
    $path_name = "../assets/foto/ktp/";
   return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile,FOTO_KTP, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// Driver
// FOTO KK
$app->post('/driver/foto_kk/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoCustomer($args['id_user']);
    if($data['foto_kk']!='-'){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_kk'];
    $path_name = "../assets/foto/kk/";
   return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile,FOTO_KK, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// Driver
// FOTO DIRI
$app->post('/driver/foto_diri/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoDriver($args['id_user']);
    if($data['foto_diri']!='-'){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_diri'];
    $path_name = "../assets/foto/diri/";
   return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile,FOTO_DIRI, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// Driver
// FOTO SIM
$app->post('/driver/foto_sim/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoDriver($args['id_user']);
    if($data['foto_sim']!='-'){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_sim'];
    $path_name = "../assets/foto/sim/";
   return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile,FOTO_SIM, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// Driver
// FOTO SIM
$app->post('/driver/foto_skck/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoDriver($args['id_user']);
    if($data['foto_skck']!='-'){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_skck'];
    $path_name = "../assets/foto/skck/";
   return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile,FOTO_SKCK, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// Driver
// FOTO SIM
$app->post('/driver/foto_stnk/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoDriver($args['id_user']);
    if($data['foto_stnk']!='-'){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_stnk'];
    $path_name = "../assets/foto/stnk/";
   return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile,FOTO_STNK, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// UPDATE
// POSITION
$app->put('/driver/position/{id_user}', function ($request, $response, $args) {
    $lat = $request->getQueryParam("lat");
    $long = $request->getQueryParam("long");
    $id = $args['id_user'];
    $position = new Umum();
    $position->setDb($this->db);
    return $response->withJson($position->updatePosition($id, $lat, $long), SERVER_OK);
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
    $user = new User(null, null, null, null, null, null);
    $user->setDB($this->db);
    if (empty($id_driver['id_driver'])) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $trip_acc = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    $trip_acc->setDb($this->db);
    $data_trip = $trip_acc->getTemporaryOrderDetail($id_trip);
    if (empty($data_trip)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Order Tidak Ada Atau Telah Diambil'], SERVER_OK);
    }
    if (!$trip_acc->deleteTemporaryOrderDetail($id_trip)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Menghapus Data'], SERVER_OK);
    }
    if (!$trip_acc->driverInputOrder($id_driver['id_driver'], $data_trip, STATUS_DRIVER_MENJEMPUT)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Input Data'], SERVER_OK);
    }
    $data_user = $user->getProfileUser($data_trip['id_customer']);
    $data_trip['id_trip'] = (int) $data_trip['id_trip'];
    $data_trip['status_trip'] = (int) $data_trip['status_trip'];
    $data_trip['jenis_pembayaran'] = (int) $data_trip['jenis_pembayaran'];
    $data_trip['jenis_trip'] = (int) $data_trip['jenis_trip'];
    $data_trip['no_telpon'] = $data_user['no_telpon'];
    $data_trip['nama'] = $data_user['nama'];
    return $response->withJson(['status' => 'Success', 'data' => $data_trip], SERVER_OK);
})->add($tokenCheck);

// DRIVER
// MENJEMPUT
$app->put('/driver/trip/terjemput/{id_trip}', function ($request, $response, $args) {
    $id_trip = $args['id_trip'];
    $trip_update_status = new Umum();
    $trip_update_status->setDb($this->db);
    $data_trip = $trip_update_status->updateStatusTrip($id_trip, STATUS_MENGANTAR_KETUJUAN);
    return $response->withJson($data_trip, SERVER_OK);
})->add($tokenCheck);

// DRIVER
// SAMPAI TUJUAN
$app->put('/driver/trip/finish/{id_trip}', function ($request, $response, $args) {
    $id_trip = $args['id_trip'];
    $trip_update_status = new Umum();
    $trip_update_status->setDb($this->db);
    $data_trip = $trip_update_status->updateStatusTrip($id_trip, STATUS_SAMPAI_TUJUAN);
    return $response->withJson($data_trip, SERVER_OK);
})->add($tokenCheck);

// DRIVER
// Cek Posisi Driver dan Customer
$app->get('/driver/trip/position/', function ($request, $response) {
    $lat = $request->getQueryParam("lat_driver");
    $long = $request->getQueryParam("long_driver");
    $lat_dest = $request->getQueryParam("lat_customer");
    $long_dest = $request->getQueryParam("long_customer");
    $jarak = new Umum();
    $jarak->setDb($this->db);
    $jarak = ($jarak->getDistance($lat, $long, $lat_dest, $long_dest)) * 1000;
    if ($jarak <= 50) {
        return $response->withJson(['status' => 'Success', 'jarak' => $jarak], SERVER_OK);
    }
    return $response->withJson(['status' => 'Error', 'jarak' => $jarak], SERVER_OK);
})->add($tokenCheck);

// DRIVER
// SET STATUS
$app->put('/driver/status/{status}/{id_user}', function ($request, $response, $args) {
    $driver = new Driver(null, null, null, null, null);
    $driver->setDb($this->db);
    return $response->withJson($driver->updateStatus($args['id_user'],$args['status']), SERVER_OK);
})->add($tokenCheck);

// DRIVER
// SEARCH TRIP
$app->get('/driver/status/{id_user}', function ($request, $response, $args) {
    $status = new Umum();
    $status->setDb($this->db);
    $data = $status->getDriverAdmin($args['id_user']);
    $data_driver =(int) $data['status_akun_aktif'];
    return $response->withJson(['status'=>'Success','status_driver'=>$data_driver], SERVER_OK);
})->add($tokenCheck);