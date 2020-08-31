<?php

function insertBarang($db, $id_user, $nama_barang, $harga_barang, $foto_barang, $kategori_barang) {
    $sql = "INSERT INTO barang_ukm (id_user, nama_barang, harga_barang, foto_barang, kategori_barang)
                VALUES ('$id_user', '$nama_barang', '$harga_barang', '$foto_barang', '$kategori_barang')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getBarangById($db, $id) {
    $sql = "SELECT barang_ukm.id_barang, barang_ukm.id_user, barang_ukm.nama_barang, barang_ukm.harga_barang, barang_ukm.foto_barang, kategori_barang.nama_kategori AS kategori_barang FROM barang_ukm
            INNER JOIN kategori_barang ON kategori_barang.id_kategori = barang_ukm.kategori_barang
            WHERE id_user = '$id'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getBarangByIdBarang($db, $id, $id_user) {
    $sql = "SELECT barang_ukm.id_barang, barang_ukm.id_user, barang_ukm.nama_barang, barang_ukm.harga_barang, barang_ukm.foto_barang, kategori_barang.nama_kategori AS kategori_barang FROM barang_ukm
            INNER JOIN kategori_barang ON kategori_barang.id_kategori = barang_ukm.kategori_barang
            WHERE barang_ukm.id_barang = '$id' AND barang_ukm.id_user = '$id_user'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

function deleteBarang($db, $id, $id_user) {
    $sql = "DELETE FROM barang_ukm
            WHERE id_barang = '$id' AND id_user = '$id_user'";
    $est = $db->prepare($sql);
    return $est->execute();
}

function updateBarang($db, $id, $nama_barang, $harga_barang, $foto_barang, $kategori_barang) {
    $sql = "UPDATE barang_ukm
            SET ";
    if (!empty($nama_barang)) {
        $sql = $sql . "nama_barang = '$nama_barang' ";
    }
    if (!empty($nama_barang) && !empty($harga_barang)) {
        $sql = $sql . ", ";
    }
    if (!empty($harga_barang)) {
        $sql = $sql . "harga_barang = '$harga_barang' ";
    }
    if ((!empty($nama_barang) || !empty($harga_barang)) && !empty($foto_barang)) {
        $sql = $sql . ", ";
    }
    if (!empty($foto_barang)) {
        $sql = $sql . "foto_barang = '$foto_barang' ";
    }
    if (((!empty($nama_barang) || !empty($harga_barang)) || !empty($foto_barang)) && !empty($kategori_barang)) {
        $sql = $sql . ", ";
    }
    if (!empty($kategori_barang)) {
        $sql = $sql . "kategori_barang = $kategori_barang ";
    }
    $sql = $sql . " WHERE id_barang = $id ";
    $est = $db->prepare($sql);
    return $est->execute();
}
