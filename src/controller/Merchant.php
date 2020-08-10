<?php

require_once dirname(__FILE__) . '/../entity/User.php';
require_once dirname(__FILE__) . '/../entity/Umum.php';
require_once dirname(__FILE__) . '/../entity/Profile.php';
require_once dirname(__FILE__) . '/../entity/Merchant.php';
require_once dirname(__FILE__) . '/../entity/DetailMerchant.php';
require_once dirname(__FILE__) . '/../model/Merchant.php';
require_once dirname(__FILE__) . '/../model/DetailMerchant.php';

function registrasiMerchant($db, $email, $nama, $no_telpon, $password, $kode_referal, $kode_sponsor, $no_ktp, $nama_bank, $no_rekening, $atas_nama_bank, $nama_usaha, $alamat_usaha, $no_telpon_kantor, $no_izin, $no_fax, $nama_direktur, $url_web_aplikasi, $lama_bisnis, $omset_perbulan, $kategori_bisnis, $uploadedFiles, $directory_ktp, $directory_izin, $directory_rekening, $directory_banner) {

    if (empty($email) || empty($nama) || empty($no_telpon) || empty($password) || empty($no_ktp) || empty($nama_bank) || empty($no_rekening) || empty($atas_nama_bank) || empty($nama_usaha) || empty($alamat_usaha) || empty($no_telpon_kantor) || empty($no_izin) || empty($no_fax) || empty($nama_direktur) || empty($url_web_aplikasi) || empty($lama_bisnis) || empty($omset_perbulan) || empty($kategori_bisnis) || empty($uploadedFiles['foto_ktp']->file) || empty($uploadedFiles['foto_dokumen_izin']->file) || empty($uploadedFiles['foto_rekening_tabungan']->file)) {
        return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
    }       

    $path_ktp = saveFile($uploadedFiles['foto_ktp'], FOTO_KTP, $directory_ktp);
    if ($path_ktp == STATUS_ERROR) {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }
    $path_izin = saveFile($uploadedFiles['foto_dokumen_izin'], FOTO_IZIN, $directory_izin);
    if ($path_izin == STATUS_ERROR) {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }
    $path_rekening = saveFile($uploadedFiles['foto_rekening_tabungan'], FOTO_REKENING, $directory_rekening);
    if ($path_rekening == STATUS_ERROR) {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }
    $path_banner = saveFile($uploadedFiles['foto_banner_ukm'], FOTO_BANNER, $directory_banner);
    if ($path_banner == STATUS_ERROR) {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }

    $user = new User($nama, $email, $no_telpon, $password, $kode_referal, $kode_sponsor);
    $user->setDb($db);
    $daftar = $user->register(MERCHANT_ROLE);
    if ($daftar['status'] == STATUS_ERROR) {
        return $daftar;
    }
    $id_user = sha1($nama . $email . $no_telpon);
    $detail_user = new Profile($id_user, $no_ktp, "-", "-", $nama_bank, $no_rekening, $atas_nama_bank, null, null);
    $detail_user->setDb($db);
    $daftar_detail = $detail_user->insertDetailUser();
    if (!$daftar_detail) {
        return ['status' => 'Error', 'message' => 'Gagal Input Detail User'];
    }

    $umum = new Umum();
    $umum->setDb($db);
    $umum->updateFoto($id_user, $path_ktp, FOTO_KTP);

    $merchant = new Merchant($id_user, $nama_usaha, $alamat_usaha, $no_telpon_kantor, $url_web_aplikasi);

    if (!insertMerchant($db, $merchant->getId_user(), $merchant->getNama_usaha(), $merchant->getAlamat_usaha(), $merchant->getNo_telpon_kantor(), $merchant->getUrl_web_aplikasi(), $merchant->getStatus_online_merchant(), $merchant->getStatus_verifikasi_merchant())) {
        return ['status' => 'Error', 'message' => 'Gagal Input Merchant Ukm'];
    }

    $detailMerchant = new DetailMerchant($id_user, $no_izin, $no_fax, $nama_direktur, $lama_bisnis, $omset_perbulan, $path_izin, $path_rekening, $path_banner);

    if (!insertDetailMerchant($db, $detailMerchant->getId_user(), $detailMerchant->getNo_izin(), $detailMerchant->getNo_fax(), $detailMerchant->getNama_direktur(), $detailMerchant->getLama_bisnis(), $detailMerchant->getOmset_perbulan(), $detailMerchant->getFoto_dokumen_perizinan(), $detailMerchant->getFoto_rekening_tabungan(), $detailMerchant->getFoto_banner_ukm())) {
        return ['status' => 'Error', 'message' => 'Gagal Input Detail Merchant Ukm'];
    }

    return ['status' => 'Success', 'message' => 'Berhasil Mendaftarkan Merchant'];

}

function saveFile($uploadedFile, $type, $directory) {
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
            return STATUS_ERROR;
        }
        $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        if ($type == FOTO_KTP) {
            return "../assets/foto/ktp/" . $filename;
        }
        if ($type == FOTO_IZIN) {
            return "../assets/foto/izin/" . $filename;
        }
        if ($type == FOTO_REKENING) {
            return "../assets/foto/rekening/" . $filename;
        }
        if ($type == FOTO_BANNER) {
            return "../assets/foto/banner/" . $filename;
        }
    }
}

function loginMerchant($emailNotelpon, $password) {

}
