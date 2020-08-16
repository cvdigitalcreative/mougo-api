<?php

require_once dirname(__FILE__) . '/../controller/Blog.php';

// BLOG POSTING
$app->post('/blog/', function ($request, $response) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(postBlog($this->db, $data['judul_blog'], $data['isi_blog'], $data['kategori_blog'], $data['nama_penulis'], $uploadedFiles, $this->get('settings')['upload_dir_foto_blog']), SERVER_OK);
})->add($tokenCheck);

// BLOG VIEW
$app->get('/blog/', function ($request, $response) {
    return $response->withJson(getBlog($this->db), SERVER_OK);
})->add($tokenCheck);

// BLOG VIEW ID
$app->get('/blog/{id_blog}', function ($request, $response, $args) {
    return $response->withJson(getBlogDetail($this->db, $args['id_blog']), SERVER_OK);
})->add($tokenCheck);

// BLOG DELETE
$app->delete('/blog/{id_blog}', function ($request, $response, $args) {
    return $response->withJson(deleteBlog($this->db, $args['id_blog']), SERVER_OK);
})->add($tokenCheck);

// BLOG UPDATE
$app->post('/blog/{id_blog}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    return $response->withJson(editBlog($this->db, $args['id_blog'], $data['judul_blog'], $data['isi_blog'], $data['kategori_blog'], $data['nama_penulis'], $uploadedFiles, $this->get('settings')['upload_dir_foto_blog']), SERVER_OK);
})->add($tokenCheck);
