<?php
require_once dirname(__FILE__) . '/../entity/Umum.php';

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
$app->post('/common/topup/{id_user}', function ($request, $response,$args) {
    $saldo = $request->getParsedBody();
    $topup = new Umum();
    $topup->setDb($this->db);
    return $response->withJson($topup->inputSaldo($saldo['saldo'], $args['id_user']), SERVER_OK);
})->add($tokenCheck);

// CUSTOMER
// JARAK DAN OSRM
$app->get('/customer/trip/orderan/',function ($request,$response){
    $lat = substr($request->getQueryParam("lat"), 0,7);
    $long = substr($request->getQueryParam("long"), 0,8);
    $lat_dest = substr($request->getQueryParam("lat_destinasi"), 0,7);
    $long_dest = substr($request->getQueryParam("long_destinasi"), 0,8);
    $response_web = file_get_contents("http://router.project-osrm.org/route/v1/driving/$long,$lat;$long_dest,$lat_dest?geometries=geojson&alternatives=true&steps=true&generate_hints=false");
    $response_web = json_decode($response_web);
    $jarak = ($response_web->routes[0]->distance)/1000;
    $harga = new Umum();
    $harga->setDb($this->db);
    $data_data = $harga->getHargaTotal($jarak);
    $data_data['jarak'] = $jarak; 
    $data_data['koordinat']=$response_web;
    return $response->withJson($data_data, SERVER_OK);
})->add($tokenCheck);

// USER 
// KONFIRMASI TOPUP SALDO
$app->post('/common/topup/konfirmasi/{id_topup}', function ($request, $response,$args) {
    $uploadedFiles = $request->getUploadedFiles();
    if(empty($uploadedFiles['gambar']->file)){
        return $response->withJson(['status'=>'Error','message'=>'Gambar Tidak Boleh Kosong'],SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];
    $topup = new Umum();
    $topup->setDb($this->db);
    if(empty($topup->getDetailTopup($args['id_topup']))){
        return $response->withJson(['status'=>'Error','message'=>'ID Topup Tidak Ditemukan'],SERVER_OK);
   }
   if(!empty($topup->getBuktiPembayaran($args['id_topup']))){
        return $response->withJson(['status'=>'Error','message'=>'Anda Telah Mengirim Bukti Pembayaran'],SERVER_OK);
    }
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if($extension!="jpg"&&$extension!="png"&&$extension!="JPG"&&$extension!="PNG"){
                return $response->withJson(['status'=>'Error','message'=>'Bukti Transfer Harus JPG atau PNG'],SERVER_OK);
        }
        $filename = md5($uploadedFile->getClientFilename()).time().".".$extension;
        $directory = $this->get('settings')['upload_directory'];
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $path_name = "../assets/".$filename;

    }return $response->withJson($topup->insertBuktiPembayaran($args['id_topup'],$path_name), SERVER_OK);

})->add($tokenCheck);

