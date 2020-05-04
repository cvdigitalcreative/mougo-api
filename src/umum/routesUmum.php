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
    $mail->Body = "Hello " . $nama  . " Berikut Adalah Link Untuk Mereset Password Mougo Anda ".$this->web_url."/mougo/resetpassword/" . $data['token'];
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
$app->post('/common/lupa_password/ganti/{id_user}', function ($request, $response, $args) {
    $password = $request->getParsedBody();
    $user = new User(null, null, null, null, null, null);
    $user->setDb($this->db);
    if (empty($user->getProfileUser($args['id_user']))) {
        return $response->withJson(['status' => 'Error', 'message' => 'User Tidak Ditemukan'], SERVER_OK);
    }
    
    $ganti = new Umum();
    $ganti->setDb($this->db);
    $data = $ganti->getUserLupaPassword($args['id_user']);
    $ganti->deleteUserLupaPassword($data['token']);
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

// USER
// GET SALDO dan Point
$app->get('/common/saldo-point/{id_user}', function ($request, $response, $args) {
    $user = new Umum();
    $user->setDb($this->db);
    $saldo = $user->getSaldoUser($args['id_user']);
    $data['saldo'] =(double) $saldo['jumlah_saldo'];
    $point = $user->getPointUser($args['id_user']);
    $data['point'] =(double) $point['jumlah_point'];
    return $response->withJson(['status'=>'Success','data'=>$data], SERVER_OK);
})->add($tokenCheck);

// UMUM
// GET Event
$app->get('/common/event/', function ($request, $response, $args) {
    $getevent = new Owner(null,null);
    $getevent->setDb($this->db);
    $event = $getevent->getEventCommon();
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

// UMUM
// GET Bank
$app->get('/common/bank/', function ($request, $response, $args) {
    $getbank = new Umum();
    $getbank->setDb($this->db);
    $bank = $getbank->getBank();
    return $response->withJson(['status' => 'Success' , 'data' => $bank ], SERVER_OK);
});

// COMMON
// Ahli Waris
$app->post('/common/ahli-waris/{id_user}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $user = new Umum();
    $user->setDb($this->db);
    $waris = $user->cekAhliWaris($args['id_user']);
    if(count($waris)>=AHLI_WARIS){
        return $response->withJson(['status' => 'Error', 'message' => 'Ahli Waris Anda Telah Penuh'], SERVER_OK);
    }
    if($user->insertAhliWaris($args['id_user'],$data['nama'])){
        return $response->withJson(['status' => 'Success', 'message' => 'Ahli Waris Anda Telah Berhasil Ditambahkan'], SERVER_OK);
    }
    return $response->withJson(['status' => 'Error', 'message' => 'Gagal Mengisi Ahli Waris'], SERVER_OK);
})->add($tokenCheck);

// UMUM
// GET Ahli Waris
$app->get('/common/ahli-waris/{id_user}', function ($request, $response, $args) {
    $profile = new Profile(null, null, null, null, null, null, null, null, null);
    $profile->setDb($this->db);
    $waris = $profile->getAhliWaris($args['id_user']);
    if(empty($waris)){
        return $response->withJson(['status' => 'Error' , 'message' => 'Ahli Waris Belum Ditambahkan' ], SERVER_OK);
    }
    for($i=0;$i<count($waris);$i++){
        $data[$i]['id'] =(int) $waris[$i]['id'];
        $data[$i]['nama_ahliwaris'] = $waris[$i]['nama_ahliwaris'];
    }
    return $response->withJson(['status' => 'Success' , 'data' => $data ], SERVER_OK);
})->add($tokenCheck);

// DELETE Ahli Waris
$app->delete('/common/ahli-waris/{id_waris}/{id_user}', function ($request, $response, $args) {
    $comon = new Umum();
    $comon->setDb($this->db);
    return $response->withJson($comon->deleteWaris($args['id_user'],$args['id_waris']), SERVER_OK);
});

// DELETE FOTO KTP KK USER
$app->delete('/common/foto/{id_user}', function ($request, $response, $args) {
    $comon = new Umum();
    $comon->setDb($this->db);
    return $response->withJson($comon->resetFoto($args['id_user']), SERVER_OK);
});

// UMUM
// GET CHILD Referal
$app->get('/common/user-referal/{id_user}', function ($request, $response, $args) {
    $id_user_anak = $request->getQueryParam("id_user_anak");
    $umum = new Umum();
    $umum->setDb($this->db);
    $user = $umum->cekUser($args['id_user']);
    $user_anak = $umum->cekUser($id_user_anak);
    if(empty($user) || empty($user_anak)){
        return $response->withJson(['status' => 'Error' , 'message' => 'User tidak ditemukan' ], SERVER_OK);
    }
    $child = $umum->getReferalChild($id_user_anak);
    $total = $umum->getTotalReferalChild($args['id_user']);
    $data['total_struktur'] = (int) $total;
    $data['parent'] = $user_anak['nama'];
    $data['anak'] = [];
    for($i=0;$i<count($child);$i++){
        $data['anak'][$i]['id_user'] = $child[$i]['id_user'];
        $data['anak'][$i]['nama'] = $child[$i]['nama'];
        $data['anak'][$i]['no_telpon'] = $child[$i]['no_telpon'];
    }
    return $response->withJson(['status' => 'Success' , 'message' => 'Berhasil Mendapatkan Struktur Referal User' , 'data' => $data ], SERVER_OK);
})->add($tokenCheck);