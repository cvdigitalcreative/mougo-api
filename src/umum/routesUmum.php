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
$app->get('/customer/trip/harga/{jarak}', function ($request, $response,$args) {
    $harga = new Umum();
    $harga->setDb($this->db);
    return $response->withJson($harga->getHargaTotal($args['jarak']), SERVER_OK);
});

