<?php
require_once dirname(__FILE__) . '/../randomGen.php';
require_once dirname(__FILE__) . '/../entity/User.php';
require_once dirname(__FILE__) . '/../entity/Trip.php';
require_once dirname(__FILE__) . '/../entity/Profile.php';

//Customer
//REGISTER
$app->post('/customer/register/', function ($request, $response) {
    $user = $request->getParsedBody();
    $userData = new User($user['nama'], $user['email'], $user['no_telpon'], $user['password'], $user['kode_referal'], $user['kode_sponsor']);
    $userData->setDB($this->db);
    $userData->setWeb_url($this->web_url);
    $result = $userData->register(USER_ROLE);
    return $response->withJson($result, SERVER_OK);
});

$app->post('/customer/konfirmasi/register/{id_user}', function ($request, $response, $args) {
    $id = $args['id_user'];
    $userKonfirmasi = new User(null, null, null, null, null, null);
    $userKonfirmasi->setDb($this->db);
    $hasil = $userKonfirmasi->konfirmasiSelesai($id);
    return $response->withJson($hasil, SERVER_OK);
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

// Customer
// PROFILE
$app->put('/customer/profile/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $profile = new Profile($args['id_user'], $data['no_ktp'], $data['provinsi'], $data['kota'], $data['bank'], $data['no_rekening'], $data['atas_nama_bank'], null, null);
    $profile->setDb($this->db);
    return $response->withJson($profile->inputProfile(PROFILE), SERVER_OK);
})->add($tokenCheck);

// Customer
// PROFILE
$app->get('/customer/profile/{id_user}', function ($request, $response, $args) {
    $profile = new Profile(null, null, null, null, null, null, null, null, null);
    $profile->setDb($this->db);
    $data = $profile->getAllProfile($args['id_user'], USER_ROLE);
    if (empty($data)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Profile Driver Tidak Ditemukan'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Success', 'data' => $data], SERVER_OK);
})->add($tokenCheck);

// Customer
// USER
$app->put('/customer/user/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $profile = new User($data['nama'], $data['email'], $data['no_telpon'], $data['password'], null, null);
    $profile->setId_user($args['id_user']);
    $profile->setDb($this->db);
    $data_user = $profile->cekEditUserPassword($args['id_user'], $data['konfirmasi_password']);
    if (empty($data_user)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Konfirmasi Password Anda Salah'], SERVER_OK);
    }

    if ($data_user['email'] == $data['email']) {
        $data['email'] = null;
    }
    if ($data_user['no_telpon'] == $data['no_telpon']) {
        $data['no_telpon'] = null;
    }
    $profiles = new User($data['nama'], $data['email'], $data['no_telpon'], $data['password'], null, null);
    $profiles->setId_user($args['id_user']);
    $profiles->setDb($this->db);
    return $response->withJson($profiles->editUser(), SERVER_OK);
})->add($tokenCheck);

// Customer
// FOTO KTP
$app->post('/customer/foto_ktp/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoCustomer($args['id_user']);
    if ($data['foto_ktp'] != '-') {
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_ktp'];
    $path_name = "/assets/foto/ktp/";
    return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile, FOTO_KTP, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

// Customer
// FOTO KK
$app->post('/customer/foto_kk/{id_user}', function ($request, $response, $args) {
    $foto = new Umum();
    $foto->setDb($this->db);
    $data = $foto->cekFotoCustomer($args['id_user']);
    if ($data['foto_kk'] != '-') {
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Anda Telah Memasang Foto'], SERVER_OK);
    }
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $directory = $this->get('settings')['upload_dir_foto_kk'];
    $path_name = "/assets/foto/kk/";
    return $response->withJson($foto->uploadFileFoto($args['id_user'], $uploadedFile, FOTO_KK, $directory, $path_name), SERVER_OK);

})->add($tokenCheck);

//Customer
//Trip
$app->post('/customer/trip/', function ($request, $response) {
    $body = $request->getParsedBody();
    $user = new Trip($body['id_user'], null, $body['total_harga'], $body['alamat_jemput'], $body['lat_jemput'], $body['long_jemput'], $body['alamat_destinasi'], $body['lat_destinasi'], $body['long_destinasi'], $body['jarak'], null, $body['jenis_trip'], STATUS_MENCARI_DRIVER, $body['jenis_pembayaran']);
    $user->setDB($this->db);
    $result = $user->order_trip();
    return $response->withJson($result, SERVER_OK);
})->add($tokenCheck);

// Customer
// Cek trip
$app->get('/customer/trip/cek/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $trip_cek = new Umum();
    $trip_cek->setDb($this->db);
    return $response->withJson($trip_cek->getCekTripStatusCustomer($id), SERVER_OK);
})->add($tokenCheck);

// Customer
// Cancel Trip
$app->post('/customer/trip/cancel/', function ($request, $response) {
    $id = $request->getParsedBody();
    $trip_cek = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    $trip_cek->setDb($this->db);
    return $response->withJson($trip_cek->cancelOrder($id['id_trip']), SERVER_OK);
})->add($tokenCheck);

// Customer
// POSITION
$app->get('/customer/trip/position/{id_driver}', function ($request, $response, $args) {
    $id = $args['id_driver'];
    $id_trip = $request->getQueryParam("id_trip");
    $position = new Umum();
    $position->setDb($this->db);
    return $response->withJson($position->getPosition($id, $id_trip), SERVER_OK);
})->add($tokenCheck);

// Customer
// Cek Trip Harga Saldo Customer
$app->get('/customer/trip/cek/osrm/{id_user}/{jenis_trip}', function ($request, $response, $args) {
    $lat = substr($request->getQueryParam("lat"), 0, 7);
    $long = substr($request->getQueryParam("long"), 0, 8);
    $lat_dest = substr($request->getQueryParam("lat_destinasi"), 0, 7);
    $long_dest = substr($request->getQueryParam("long_destinasi"), 0, 8);
    $response_web = file_get_contents("http://router.project-osrm.org/route/v1/driving/$long,$lat;$long_dest,$lat_dest?geometries=geojson&alternatives=true&steps=true&generate_hints=false");
    $response_web = json_decode($response_web);
    if(empty($response_web->routes[0]->distance)){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Mendapatkan Data Dari Server'], SERVER_OK);
    }
    $dist = ceil(($response_web->routes[0]->distance) / 1000);

    $jarak = new Umum();
    $jarak->setDb($this->db);

    $harga = $jarak->getHargaCekTotal($dist, $args['jenis_trip']);

    $saldo = $jarak->getSaldoUser($args['id_user']);
    $data = [
        'harga' => $harga['harga'],
        'saldo' => (double) $saldo['jumlah_saldo'],
    ];
    if ($saldo['jumlah_saldo'] >= $harga['harga']) {
        return $response->withJson(['status' => 'Success', 'data' => $data, 'message' => 'Saldo Anda Cukup'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Error', 'data' => $data, 'message' => 'Saldo Anda Tidak Cukup'], SERVER_OK);
})->add($tokenCheck);

// Customer
// Cek Trip Harga Saldo Customer
$app->get('/customer/trip/cek/{id_user}/{jenis_trip}', function ($request, $response, $args) {
    $lat = $request->getQueryParam("lat");
    $long = $request->getQueryParam("long");
    $lat_dest = $request->getQueryParam("lat_destinasi");
    $long_dest = $request->getQueryParam("long_destinasi"); 
    $token = $_ENV['GOOGLE_MAPS_API_TOKEN'];
    $response_web = file_get_contents("https://maps.googleapis.com/maps/api/directions/json?origin=$lat,$long&destination=$lat_dest,$long_dest&key=$token");
    $response_web = json_decode($response_web);
    if(empty($response_web->routes[0]->legs[0]->distance->value)){
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Mendapatkan Data Dari Server'], SERVER_OK);
    }
    $dist = ceil(($response_web->routes[0]->legs[0]->distance->value) / 1000);

    $jarak = new Umum();
    $jarak->setDb($this->db);

    $harga = $jarak->getHargaCekTotal($dist, $args['jenis_trip']);

    $saldo = $jarak->getSaldoUser($args['id_user']);
    $data = [
        'harga' => $harga['harga'],
        'saldo' => (double) $saldo['jumlah_saldo'],
    ];
    if ($saldo['jumlah_saldo'] >= $harga['harga']) {
        return $response->withJson(['status' => 'Success', 'data' => $data, 'message' => 'Saldo Anda Cukup'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Error', 'data' => $data, 'message' => 'Saldo Anda Tidak Cukup'], SERVER_OK);
})->add($tokenCheck);


// CUSTOMER GET DRIVER ONLINE POSITION 
$app->get('/customer/driver/position/', function ($request, $response) {
    $user = new Umum();
    $user->setDb($this->db);
    return $response->withJson($user->CustomerGetDriverOnlinePosition(), SERVER_OK);
})->add($tokenCheck);