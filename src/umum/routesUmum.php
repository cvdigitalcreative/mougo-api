<?php
require_once dirname(__FILE__) . '/../entity/Umum.php';
require_once dirname(__FILE__) . '/../aes.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// USER
// Lupa Password
$app->post('/common/lupa_password/', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    if(empty($data['emailTelpon'])){
       return $response->withJson(['status' => 'Error', 'message' => 'Email atau Nomor Telpon Tidak Boleh Kosong'], SERVER_OK);
    }
    $lupaPass = new Umum();
    $lupaPass->setDb($this->db);
    $data = $lupaPass->lupaPassword($data['emailTelpon']);
    if (empty($data)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Email atau Nomor Telpon Tidak Ditemukan'], SERVER_OK);
    }
    $email = decrypt($data['email'],MOUGO_CRYPTO_KEY);
    $nama = decrypt($data['nama'],MOUGO_CRYPTO_KEY);
    if ($email==false) {
        return $response->withJson(['status' => 'Error', 'message' => 'Email atau Nomor Telpon Tidak Ditemukan'], SERVER_OK);
    }
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    // $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = "tls";
    $mail->SMTPAuth = true;
    $mail->Username = "mougo.noreply@gmail.com";
    $mail->Password = "mougodms1@!";
    
    $mail->setFrom('mougo.noreply@gmail.com', 'MOUGO DMS');
    $mail->addAddress($email, $nama);
    $mail->isHTML(true);
    $mail->Subject = "MOUGO DMS Reset Password";
    $mail->Body = "Hello " . $nama  . " \nBerikut Adalah Link Untuk Mereset Password Mougo Anda " . $data['token'];

    if ($mail->send()) {
        return $response->withJson(['status' => 'Success', 'message' => 'Konfirmasi Lupa Password Akan Dikirim Melalui Email'], SERVER_OK);
    }

    return $response->withJson(['status' => 'Error', 'message' => 'Gagal Mengirim Konfirmasi Email'], SERVER_BAD);
});

// Konfirmasi Email
// Lupa Password
$app->get('/common/lupa_password/konfirmasi/{token}', function ($request, $response, $args) {
    $confirm = new Umum();
    $confirm->setDb($this->db);
    $status = $confirm->getUserLupaPasswordToken($args['token']);
    if (empty($status)) {
        return $response->withJson(['status' => false, 'message' => 'Konfirmasi Lupa Password Tidak Ditemukan'], SERVER_OK);
    }
    if ($status['day'] != DATE('d')) {
        $confirm->deleteUserLupaPassword($args['token']);
        return $response->withJson(['status' => false, 'message' => 'Waktu Untuk Konfirmasi Lupa Password Telah Habis'], SERVER_OK);
    }
    return $response->withJson(['status' => true, 'id_user' => $status['id_user']], SERVER_OK);
});

// Ganti Password
// Lupa Password
$app->get('/common/lupa_password/ganti/{id_user}', function ($request, $response, $args) {
    $password = $request->getParsedBody();
    $user = new User(null, null, null, null, null, null);
    $user->setDb($this->db);
    if (empty($user->getProfileUser($args['id_user']))) {
        return $response->withJson(['status' => 'Error', 'message' => 'User Tidak Ditemukan'], SERVER_OK);
    }

    $ganti = new Umum();
    $ganti->setDb($this->db);
    $status = $ganti->updatePassword($args['id_user'], $password['password']);
    if ($status) {
        return $response->withJson(['status' => 'Success', 'message' => 'Password Berhasil Diganti'], SERVER_OK);
    }

    return $response->withJson(['status' => 'Error', 'message' => 'Password Gagal Diganti'], SERVER_OK);
});

// Driver
// Get Cabang
$app->get('/driver/cabang/', function ($request, $response) {
    $cabang = new Umum();
    $cabang->setDb($this->db);
    return $response->withJson($cabang->getAllCabang(), SERVER_OK);
});

// Driver
// Get Jenis Kendaraan
$app->get('/driver/jenis-kendaraan/', function ($request, $response) {
    $jenis = new Umum();
    $jenis->setDb($this->db);
    return $response->withJson($jenis->getAllJenisKendaraan(), SERVER_OK);
});

// Customer
// Harga Trip
$app->get('/customer/trip/harga/{jarak}', function ($request, $response, $args) {
    $harga = new Umum();
    $harga->setDb($this->db);
    return $response->withJson($harga->getHargaTotal($args['jarak']), SERVER_OK);
})->add($tokenCheck);

// CUSTOMER DRIVER
// ISI SALDO
$app->post('/common/topup/{id_user}', function ($request, $response, $args) {
    $saldo = $request->getParsedBody();
    $topup = new Umum();
    $topup->setDb($this->db);
    return $response->withJson($topup->inputSaldo($saldo['saldo'], $args['id_user']), SERVER_OK);
})->add($tokenCheck);

// CUSTOMER
// JARAK DAN OSRM
$app->get('/customer/trip/orderan/', function ($request, $response) {
    $lat = substr($request->getQueryParam("lat"), 0, 7);
    $long = substr($request->getQueryParam("long"), 0, 8);
    $lat_dest = substr($request->getQueryParam("lat_destinasi"), 0, 7);
    $long_dest = substr($request->getQueryParam("long_destinasi"), 0, 8);
    $response_web = file_get_contents("http://router.project-osrm.org/route/v1/driving/$long,$lat;$long_dest,$lat_dest?geometries=geojson&alternatives=true&steps=true&generate_hints=false");
    $response_web = json_decode($response_web);
    $jarak = ($response_web->routes[0]->distance) / 1000;
    $harga = new Umum();
    $harga->setDb($this->db);
    $data_data = $harga->getHargaTotal($jarak);
    $data_data['jarak'] = $jarak;
    $data_data['koordinat'] = $response_web;
    return $response->withJson($data_data, SERVER_OK);
})->add($tokenCheck);

// USER
// KONFIRMASI TOPUP SALDO
$app->post('/common/topup/konfirmasi/{id_topup}', function ($request, $response, $args) {
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Gambar Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $topup = new Umum();
    $topup->setDb($this->db);
    if (empty($topup->getDetailTopup($args['id_topup']))) {
        return $response->withJson(['status' => 'Error', 'message' => 'ID Topup Tidak Ditemukan'], SERVER_OK);
    }
    if (!empty($topup->getBuktiPembayaran($args['id_topup']))) {
        return $response->withJson(['status' => 'Error', 'message' => 'Anda Telah Mengirim Bukti Pembayaran'], SERVER_OK);
    }
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG") {
            return $response->withJson(['status' => 'Error', 'message' => 'Bukti Transfer Harus JPG atau PNG'], SERVER_OK);
        }
        $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
        $directory = $this->get('settings')['upload_directory'];
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $path_name = "../assets/" . $filename;

    }return $response->withJson($topup->insertBuktiPembayaran($args['id_topup'], $path_name), SERVER_OK);

})->add($tokenCheck);

// Customer
// GET SALDO
$app->get('/common/saldo/{id_user}', function ($request, $response, $args) {
    $saldo = new Umum();
    $saldo->setDb($this->db);
    return $response->withJson($saldo->getSaldoUser($args['id_user']), SERVER_OK);
})->add($tokenCheck);
