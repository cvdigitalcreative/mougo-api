<?php

function insertLayanan($db, $nama_layanan, $deskripsi_layanan, $foto_layanan) {
    $sql = "INSERT INTO layanan (nama_layanan, deskripsi_layanan, foto_layanan)
                VALUES ('$nama_layanan', '$deskripsi_layanan', '$foto_layanan')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getAllLayanan($db) {
    $sql = "SELECT * FROM layanan";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function deleteLayananById($db, $id) {
    $sql = "DELETE FROM layanan
            WHERE id_layanan = $id";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getLayananBy($db, $id) {
    $sql = "SELECT * FROM layanan
            WHERE id_layanan = $id";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

function updateLayanan($db, $id, $nama_layanan, $deskripsi_layanan, $foto_layanan) {
    $sql = "UPDATE layanan
            SET ";
    if (!empty($nama_layanan)) {
        $sql = $sql . "nama_layanan = '$nama_layanan' ";
    }
    if (!empty($nama_layanan) && !empty($deskripsi_layanan)) {
        $sql = $sql . ", ";
    }
    if (!empty($deskripsi_layanan)) {
        $sql = $sql . "deskripsi_layanan = '$deskripsi_layanan' ";
    }
    if ((!empty($nama_layanan) || !empty($deskripsi_layanan)) && !empty($foto_layanan)) {
        $sql = $sql . ", ";
    }
    if (!empty($foto_layanan)) {
        $sql = $sql . "foto_layanan = '$foto_layanan' ";
    }
    $sql = $sql . " WHERE id_layanan = $id ";
    $est = $db->prepare($sql);
    return $est->execute();
}
