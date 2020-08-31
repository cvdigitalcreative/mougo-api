<?php

require_once dirname(__FILE__) . '/../controller/Layanan.php';

// OWNER LAYANAN POSTING
$app->post('/layanan/', function ($request, $response) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(postLayanan($this->db, $data['nama_layanan'], $data['deskripsi_layanan'], $uploadedFiles, $this->get('settings')['upload_dir_foto_layanan']), SERVER_OK);
});

// OWNER LAYANAN VIEW
$app->get('/layanan/', function ($request, $response) {
    return $response->withJson(getLayanan($this->db), SERVER_OK);
});

// OWNER LAYANAN VIEW ID
$app->get('/layanan/{id}', function ($request, $response, $args) {
    return $response->withJson(getLayananDetail($this->db, $args['id']), SERVER_OK);
});

// OWNER LAYANAN DELETE
$app->delete('/layanan/{id}', function ($request, $response, $args) {
    return $response->withJson(deleteLayanan($this->db, $args['id']), SERVER_OK);
});

// OWNER LAYANAN UPDATE
$app->post('/layanan/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(editLayanan($this->db, $args['id'], $data['nama_layanan'], $data['deskripsi_layanan'], $uploadedFiles, $this->get('settings')['upload_dir_foto_layanan']), SERVER_OK);
});
