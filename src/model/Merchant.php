<?php

//
// MERCHANT
function insertMerchant($db, $id_user, $nama_usaha, $alamat_usaha, $no_telpon_kantor, $url_web_aplikasi, $status_online, $status_verif) {
    $sql = "INSERT INTO ukm (id_user, nama_usaha, alamat_usaha, no_telpon_kantor, url_web_aplikasi, status_online_merchant, status_verifikasi_merchant)
                VALUES ('$id_user', '$nama_usaha', '$alamat_usaha', '$no_telpon_kantor', '$url_web_aplikasi', $status_online, $status_verif)";
    $est = $db->prepare($sql);
    return $est->execute();
}

//
// MERCHANT KATEGORI
function insertKategori($db, $id_user, $kategori) {
    $sql = "INSERT INTO kategori_ukm (id_user, id_kategori)
                VALUES ('$id_user', '$kategori')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getMerchantDetailById($db, $id_user) {
    $sql = "SELECT user.id_user, user.email, user.nama, user.no_telpon, kode_referal.kode_referal, kode_sponsor.kode_sponsor, detail_user.no_ktp, bank.name AS nama_bank, detail_user.no_rekening, detail_user.atas_nama_bank, ukm.nama_usaha, ukm.alamat_usaha, ukm.no_telpon_kantor, ukm.url_web_aplikasi, detail_ukm.nama_direktur, detail_ukm.lama_bisnis, detail_ukm.omset_perbulan, detail_user.foto_ktp, detail_ukm.foto_dokumen_perizinan AS foto_izin, detail_ukm.foto_rekening_tabungan AS foto_rekening, detail_ukm.foto_banner_ukm FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN kategori_ukm ON kategori_ukm.id_user = ukm.id_user
            INNER JOIN detail_ukm ON detail_ukm.id_user = ukm.id_user
            WHERE user.id_user = '$id_user'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

function getMerchantDetail($db) {
    $sql = "SELECT user.id_user, user.email, user.nama, user.no_telpon, kode_referal.kode_referal, kode_sponsor.kode_sponsor, detail_user.no_ktp, bank.name AS nama_bank, detail_user.no_rekening, detail_user.atas_nama_bank, ukm.nama_usaha, ukm.alamat_usaha, ukm.no_telpon_kantor, ukm.url_web_aplikasi, detail_ukm.nama_direktur, detail_ukm.lama_bisnis, detail_ukm.omset_perbulan, detail_user.foto_ktp, detail_ukm.foto_dokumen_perizinan AS foto_izin, detail_ukm.foto_rekening_tabungan AS foto_rekening, detail_ukm.foto_banner_ukm FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN kategori_ukm ON kategori_ukm.id_user = ukm.id_user
            INNER JOIN detail_ukm ON detail_ukm.id_user = ukm.id_user
            GROUP BY user.id_user";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getMerchantDetailVerifi($db) {
    $status = STATUS_MERCHANT_ACCEPTED;
    $sql = "SELECT user.id_user, user.email, user.nama, user.no_telpon, kode_referal.kode_referal, kode_sponsor.kode_sponsor, detail_user.no_ktp, bank.name AS nama_bank, detail_user.no_rekening, detail_user.atas_nama_bank, ukm.nama_usaha, ukm.alamat_usaha, ukm.no_telpon_kantor, ukm.url_web_aplikasi, detail_ukm.nama_direktur, detail_ukm.lama_bisnis, detail_ukm.omset_perbulan, detail_user.foto_ktp, detail_ukm.foto_dokumen_perizinan AS foto_izin, detail_ukm.foto_rekening_tabungan AS foto_rekening, detail_ukm.foto_banner_ukm FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN detail_ukm ON detail_ukm.id_user = ukm.id_user
            WHERE ukm.status_verifikasi_merchant = $status";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getMerchantaKategoriUkm($db, $id) {
    $sql = "SELECT kategori_bisnis.nama_kategori FROM ukm 
            INNER JOIN kategori_ukm ON kategori_ukm.id_user = ukm.id_user
            INNER JOIN kategori_bisnis ON kategori_bisnis.id_kategori = kategori_ukm.id_kategori
            WHERE ukm.id_user = '$id'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getMerchantDetailForConfirm($db, $id) {
    $sql = "SELECT user.id_user, user.email, user.nama, user.no_telpon, kode_referal.kode_referal, kode_sponsor.kode_sponsor, detail_user.no_ktp, bank.name AS nama_bank, detail_user.no_rekening, detail_user.atas_nama_bank, ukm.nama_usaha, ukm.alamat_usaha, ukm.no_telpon_kantor, ukm.url_web_aplikasi, detail_ukm.nama_direktur, detail_ukm.lama_bisnis, detail_ukm.omset_perbulan, detail_user.foto_ktp, detail_ukm.foto_dokumen_perizinan AS foto_izin, detail_ukm.foto_rekening_tabungan AS foto_rekening, detail_ukm.foto_banner_ukm FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN kategori_ukm ON kategori_ukm.id_user = ukm.id_user
            INNER JOIN detail_ukm ON detail_ukm.id_user = ukm.id_user
            WHERE ukm.status_verifikasi_merchant = 1
            AND (no_ktp = '$id' OR user.email = '$id' OR user.no_telpon = '$id')";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

function getMerchantDetailForConfirmList($db) {
    $status = STATUS_MERCHANT_PENDING;
    $sql = "SELECT user.id_user, user.email, user.nama, user.no_telpon, kode_referal.kode_referal, kode_sponsor.kode_sponsor, detail_user.no_ktp, bank.name AS nama_bank, detail_user.no_rekening, detail_user.atas_nama_bank, ukm.nama_usaha, ukm.alamat_usaha, ukm.no_telpon_kantor, ukm.url_web_aplikasi, detail_ukm.nama_direktur, detail_ukm.lama_bisnis, detail_ukm.omset_perbulan, detail_user.foto_ktp, detail_ukm.foto_dokumen_perizinan AS foto_izin, detail_ukm.foto_rekening_tabungan AS foto_rekening, detail_ukm.foto_banner_ukm FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN kategori_ukm ON kategori_ukm.id_user = ukm.id_user
            INNER JOIN detail_ukm ON detail_ukm.id_user = ukm.id_user
            WHERE ukm.status_verifikasi_merchant = $status
            GROUP BY user.id_user";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getMerchantDetailCek($db, $id) {
    $sql = "SELECT * FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN kategori_ukm ON kategori_ukm.id_user = ukm.id_user
            INNER JOIN kategori_bisnis ON kategori_bisnis.id_kategori = kategori_ukm.id_kategori
            INNER JOIN detail_ukm ON detail_ukm.id_user = ukm.id_user
            WHERE user.id_user = '$id'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

function countsUserDriver($db) {
    $role = DRIVER_ROLE;
    $sql = "SELECT * FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            WHERE user.role = $role";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->rowCount();
}

function countsUserCustomer($db) {
    $role = USER_ROLE;
    $sql = "SELECT * FROM user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            WHERE user.role = $role";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->rowCount();
}

function countsUserMerchant($db) {
    $status = STATUS_MERCHANT_ACCEPTED;
    $sql = "SELECT * FROM user
            INNER JOIN ukm ON ukm.id_user = user.id_user
            INNER JOIN detail_user ON detail_user.id_user = user.id_user
            INNER JOIN bank ON bank.code = detail_user.bank
            INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
            INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
            WHERE ukm.status_verifikasi_merchant = $status";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->rowCount();
}

function updateVerifikasiMerchant($db, $id, $status) {
    $sql = "UPDATE ukm 
            SET status_verifikasi_merchant = '$status'
            WHERE id_user = '$id'";
    $est = $db->prepare($sql);
    return $est->execute();
}

//
// UPDATE VERIFIKASI MERCHANT ADMIN
function insertVerifikasiAdminUKM($db, $id_user, $email) {
    $sql = "INSERT INTO verifikasi_ukm (id_user, email_admin)
                VALUES ('$id_user', '$email')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getAdminByEmail($db, $email) {
    $sql = "SELECT * FROM admin
            WHERE email_admin = '$email'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

//
// MERCHANT KATEGORI BISNIS
function getMerchantKategoriBisnis($db) {
    $sql = "SELECT * FROM kategori_bisnis";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getMerchantKategoriBisnisByName($db, $nama) {
    $sql = "SELECT * FROM kategori_bisnis
            WHERE nama_kategori = '$nama'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function insertKategoriBisnis($db, $nama) {
    $sql = "INSERT INTO kategori_bisnis (nama_kategori)
                VALUES ('$nama')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getMerchantKategoriBisnisById($db, $id) {
    $sql = "SELECT * FROM kategori_bisnis
            WHERE id_kategori = '$id'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function updateKategoriBisnis($db, $id, $nama) {
    $sql = "UPDATE kategori_bisnis
            SET nama_kategori = '$nama'
            WHERE id_kategori = $id";
    $est = $db->prepare($sql);
    return $est->execute();
}

function deleteKategoriBisnis($db, $id) {
    $sql = "DELETE FROM kategori_bisnis
            WHERE id_kategori = '$id'";
    $est = $db->prepare($sql);
    return $est->execute();
}

//
// MERCHANT KATEGORI BARANG
function getMerchantKategoriBarangUKM($db) {
    $sql = "SELECT * FROM kategori_barang";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getMerchantKategoriBarangByName($db, $nama) {
    $sql = "SELECT * FROM kategori_barang
            WHERE nama_kategori = '$nama'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function insertKategoriBarang($db, $nama) {
    $sql = "INSERT INTO kategori_barang (nama_kategori)
                VALUES ('$nama')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getMerchantKategoriBarangById($db, $id) {
    $sql = "SELECT * FROM kategori_barang
            WHERE id_kategori = '$id'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function updateKategoriBarang($db, $id, $nama) {
    $sql = "UPDATE kategori_barang
            SET nama_kategori = '$nama'
            WHERE id_kategori = $id";
    $est = $db->prepare($sql);
    return $est->execute();
}

function deleteKategoriBarang($db, $id) {
    $sql = "DELETE FROM kategori_barang
            WHERE id_kategori = '$id'";
    $est = $db->prepare($sql);
    return $est->execute();
}
