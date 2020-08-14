<?php

function insertBarang($db, $id_user, $nama_barang, $harga_barang, $foto_barang, $kategori_barang) {
    $sql = "INSERT INTO barang_ukm (id_user, nama_barang, harga_barang, foto_barang, kategori_barang)
                VALUES ('$id_user', '$nama_barang', '$harga_barang', '$foto_barang', '$kategori_barang')";
    $est = $db->prepare($sql);
    return $est->execute();
}