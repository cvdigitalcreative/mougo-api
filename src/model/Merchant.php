<?php

function insertMerchant($db, $id_user, $nama_usaha, $alamat_usaha, $no_telpon_kantor, $url_web_aplikasi, $status_online, $status_verif) {
    $sql = "INSERT INTO ukm (id_user, nama_usaha, alamat_usaha, no_telpon_kantor, url_web_aplikasi, status_online_merchant, status_verifikasi_merchant)
                VALUES ('$id_user', '$nama_usaha', '$alamat_usaha', '$no_telpon_kantor', '$url_web_aplikasi', $status_online, $status_verif)";
    $est = $db->prepare($sql);
    return $est->execute();
}
