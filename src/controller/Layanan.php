<?php

require_once dirname(__FILE__) . '/../entity/Layanan.php';
require_once dirname(__FILE__) . '/../model/Layanan.php';

function postLayanan($db, $nama_layanan, $deskripsi_layanan, $uploadedFile, $directory) {
    if (empty($nama_layanan) || empty($deskripsi_layanan) || empty($uploadedFile['foto_layanan']->file)) {
        return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
    }
    $path_layanan = saveFile($uploadedFile['foto_layanan'], FOTO_LAYANAN, $directory);
    if ($path_layanan == STATUS_ERROR) {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }

    $layanan = new Layanan($nama_layanan, $deskripsi_layanan, $path_layanan);
    if (insertLayanan($db, $layanan->getNama_layanan(), $layanan->getDeskripsi_layanan(), $layanan->getFoto_layanan())) {
        return ['status' => 'Success', 'message' => 'Berhasil Memposting Layanan'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Memposting Layanan'];
}

function getLayanan($db) {
    $data = getAllLayanan($db);
    return ['status' => 'Success', 'message' => 'Berhasil Mengambil Data', 'data' => $data];
}

function getLayananDetail($db, $id) {
    $data = getLayananBy($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Gagal Menghapus Layanan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mengambil Data', 'data' => $data];
}

function deleteLayanan($db, $id) {
    $data = getLayananBy($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Gagal Menghapus Layanan Tidak Ditemukan'];
    }
    if (deleteLayananById($db, $id)) {
        unlink($data['foto_layanan']);
        return ['status' => 'Success', 'message' => 'Berhasil Menghapus Layanan'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Menghapus Layanan'];
}

function editLayanan($db, $id, $nama_layanan, $deskripsi_layanan, $uploadedFile, $directory) {
    $data_layanan = getLayananBy($db, $id);
    if (empty($data_layanan)) {
        return ['status' => 'Error', 'message' => 'Gagal Mengupdate Layanan Tidak Ditemukan'];
    }
    if (empty($nama_layanan) & empty($deskripsi_layanan) & empty($uploadedFile['foto_layanan']->file)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Layanan'];
    }
    $path_layanan = null;
    if (!empty($uploadedFile['foto_layanan']->file)) {
        $path_layanan = saveFile($uploadedFile['foto_layanan'], FOTO_LAYANAN, $directory);
        if ($path_layanan == STATUS_ERROR) {
            return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
        }
        unlink($data_layanan['foto_layanan']);
    }
    $layanan = new Layanan($nama_layanan, $deskripsi_layanan, $path_layanan);
    if (updateLayanan($db, $id, $layanan->getNama_layanan(), $layanan->getDeskripsi_layanan(), $layanan->getFoto_layanan())) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Layanan'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mengupdate Layanan'];
}