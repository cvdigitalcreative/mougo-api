<?php

require_once dirname(__FILE__) . '/../entity/User.php';
require_once dirname(__FILE__) . '/../entity/Umum.php';
require_once dirname(__FILE__) . '/../entity/Profile.php';
require_once dirname(__FILE__) . '/../entity/Merchant.php';
require_once dirname(__FILE__) . '/../entity/DetailMerchant.php';
require_once dirname(__FILE__) . '/../entity/Barang.php';
require_once dirname(__FILE__) . '/../model/Barang.php';
require_once dirname(__FILE__) . '/../model/Merchant.php';
require_once dirname(__FILE__) . '/../model/DetailMerchant.php';

//
// MERCHANT
function registrasiMerchant($db, $email, $nama, $no_telpon, $password, $kode_referal, $kode_sponsor, $no_ktp, $nama_bank, $no_rekening, $atas_nama_bank, $nama_usaha, $alamat_usaha, $no_telpon_kantor, $no_izin, $no_fax, $nama_direktur, $url_web_aplikasi, $lama_bisnis, $omset_perbulan, $kategori_bisnis, $uploadedFiles, $directory_ktp, $directory_izin, $directory_rekening, $directory_banner) {

    if (empty($email) || empty($nama) || empty($no_telpon) || empty($password) || empty($no_ktp) || empty($nama_bank) || empty($no_rekening) || empty($atas_nama_bank) || empty($nama_usaha) || empty($alamat_usaha) || empty($no_telpon_kantor) || empty($no_izin) || empty($no_fax) || empty($nama_direktur) || empty($url_web_aplikasi) || empty($lama_bisnis) || empty($omset_perbulan) || empty($kategori_bisnis) || empty($uploadedFiles['foto_ktp']->file) || empty($uploadedFiles['foto_dokumen_izin']->file) || empty($uploadedFiles['foto_rekening_tabungan']->file) || empty($uploadedFiles['foto_banner_ukm']->file)) {
        return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
    }
    $extension = pathinfo($uploadedFiles['foto_ktp']->getClientFilename(), PATHINFO_EXTENSION);
    if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }
    $extension = pathinfo($uploadedFiles['foto_dokumen_izin']->getClientFilename(), PATHINFO_EXTENSION);
    if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }
    $extension = pathinfo($uploadedFiles['foto_rekening_tabungan']->getClientFilename(), PATHINFO_EXTENSION);
    if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }
    $extension = pathinfo($uploadedFiles['foto_banner_ukm']->getClientFilename(), PATHINFO_EXTENSION);
    if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
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
    
    $kategori_bisnis = str_replace( [' '],'' ,$kategori_bisnis);
    $kategori_bisnis_arr = explode( "," ,$kategori_bisnis);
    for ($i=0; $i < count($kategori_bisnis_arr); $i++) { 
        insertKategori($db, $id_user, $kategori_bisnis_arr[$i]);
    }
    $user_id['id_user'] = $id_user;
    return ['status' => 'Success', 'message' => 'Berhasil Mendaftarkan Merchant', 'data' => $user_id];

}

//
// BARANG MERCHANT
function barangMerchant($db, $id_user, $nama_barang, $harga_barang, $kategori_barang, $uploadedFiles, $directory_barang) {

    if (empty($id_user) || empty($nama_barang) || empty($harga_barang) || empty($kategori_barang) || empty($uploadedFiles['foto_barang'])) {
        return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
    }

    $umum = new Umum();
    $umum->setDb($db);
    $cek_user = $umum->cekUser($id_user);
    if (empty($cek_user)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    // foreach ($uploadedFiles['foto_barang'] as $uploadedFile) {
    //     if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
    //         $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    //         if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
    //             return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    //         }
    //     }
    // }

    // $state = true;
    // $i = 0;
    // while ($state) {
    //     if (empty($nama_barang[$i]) || empty($harga_barang[$i]) || empty($kategori_barang[$i]) || empty($uploadedFiles['foto_barang'])) {
    //         break;
    //     }
        $path_barang = saveFile($uploadedFiles['foto_barang'], FOTO_BARANG, $directory_barang);
        if ($path_barang == STATUS_ERROR) {
            return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
        }
        $barang = new Barang($nama_barang, $harga_barang, $path_barang, $kategori_barang);
        insertBarang($db, $id_user, $barang->getNama_barang(), $barang->getHarga_barang(), $barang->getFoto_barang(), $barang->getKategori_barang());

        // $i++;

    // }

    return ['status' => 'Success', 'message' => 'Berhasil Mendaftarkan Barang Merchant'];

}

function getMerchantDetailByInfo($db, $id) {
    if (empty($id)) {
        return ['status' => 'Error', 'message' => 'Harus Memasukkan Informasi Berupa Nomor KTP, Email Atau Nomor Telpon'];
    }
    $data = getMerchantDetailForConfirm($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Merchant', 'data' => $data];
}

function updateMerchantVerifikasi($db, $id_user, $type){
    $data = getMerchantDetailCek($db, $id_user);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    if($data['status_verifikasi_merchant'] == STATUS_MERCHANT_ACCEPTED){
        return ['status' => 'Error', 'message' => 'Merchant telah diverifikasi'];
    }
    if($data['status_verifikasi_merchant'] == STATUS_MERCHANT_REJECTED){
        return ['status' => 'Error', 'message' => 'Verifikasi telah ditolak oleh admin'];
    }
    if (updateVerifikasiMerchant($db, $id_user, $type)) {
        return ['status' => 'Success', 'message' => 'Berhasil Melakukan Verifikasi Merchant'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Melakukan Verifikasi'];
}

function getMerchantBarang($db, $id) {
    $data = getMerchantDetailById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    $barang = getBarangById($db, $id);
    if (!empty($barang)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Barang', 'data' => $barang];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mendapatkan Barang'];
}

function getMerchantDetailBarang($db) {
    $data = getMerchantDetailVerifi($db);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    $final = [];
    $k = 0 ;
    // $l = 0;
    for ($i=0; $i < count($data); $i++) { 
        $barang = getBarangById($db, $data[$i]['id_user']);
        if(!empty($barang)){
            $final[$k] = $data[$i];
            for ($j=0; $j < count($barang); $j++) { 
                $final[$k]['barang'][$j] = $barang[$j];
            }
            $k++;
        }
    }
    for ($i=0; $i < count($final); $i++) { 
        $kategori_bisnis = getMerchantaKategoriUkm($db, $final[$i]['id_user']);
        // var_dump($kategori_bisnis);
        if(!empty($kategori_bisnis)){
            // $final[$l] = $data[$l];
            for ($j=0; $j < count($kategori_bisnis); $j++) { 
                $final[$i]['kategori_bisnis'][$j] = $kategori_bisnis[$j];
            }
            // $l++; 
        }
    }
    if (!empty($data)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Barang', 'data' => array_slice($final, 0, 10)];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mendapatkan Barang Merchant'];
}

function getMerchantBarangDetail($db, $id, $id_barang) {
    $data = getMerchantDetailById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    $barang = getBarangByIdBarang($db, $id_barang, $id);
    if (!empty($barang)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Barang', 'data' => $barang];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mendapatkan Barang'];
}

function deleteMerchantBarang($db, $id, $id_barang) {
    $data = getMerchantDetailById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    $barang = getBarangByIdBarang($db, $id_barang, $id);
    if (empty($barang)) {
        return ['status' => 'Error', 'message' => 'Barang tidak ada atau telah dihapus'];
    }
    if (deleteBarang($db, $id_barang, $id)) {
        return ['status' => 'Success', 'message' => 'Berhasil Menghapus Barang'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Menghapus Barang'];
}

function updateMerchantBarang($db, $id, $id_barang, $nama_barang, $harga_barang, $kategori_barang, $uploadedFiles, $directory_barang) {
    if ( empty($nama_barang) && empty($harga_barang) && empty($kategori_barang) && empty($uploadedFiles['foto_barang'])) {
        return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
    }
    $data = getMerchantDetailById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    $barang = getBarangByIdBarang($db, $id_barang, $id);
    if (empty($barang)) {
        return ['status' => 'Error', 'message' => 'Barang tidak ditemukan'];
    }
    $path_barang = null;
    if (!empty($uploadedFiles['foto_barang'])) {
        $path_barang = saveFile($uploadedFiles['foto_barang'], FOTO_BARANG, $directory_barang);
        if ($path_barang == STATUS_ERROR) {
            return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
        }
        unlink($barang['foto_barang']);
    }
    if(updateBarang($db, $id_barang, $nama_barang, $harga_barang, $path_barang,$kategori_barang)){
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Barang'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mengupdate Barang'];
}

//
// SAVE UPLOADED FILE
function saveFile($uploadedFile, $type, $directory) {
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
            return STATUS_ERROR;
        }
        $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
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
        if ($type == FOTO_LAYANAN) {
            return "../assets/foto/layanan/" . $filename;
        }
        if ($type == FOTO_BARANG) {
            return "../assets/foto/barang/" . $filename;
        }
        if ($type == FOTO_BLOG) {
            return "../assets/foto/blog/" . $filename;
        }
    }
}

//
// MERCHANT
function getMerchantById($db, $id_user) {
    $data = getMerchantDetailById($db, $id_user);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Detail Merchant', 'data' => $data];
}

function getMerchant($db) {
    $data = getMerchantDetail($db);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'User tidak ditemukan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Detail Merchant', 'data' => $data];
}

//
// MERCHANT KATEGORI BISNIS
function getMerchantKategori($db) {
    $data = getMerchantKategoriBisnis($db);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori tidak ditemukan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Kategori', 'data' => $data];
}

function insertMerchantKategoriBisnis($db, $nama) {
    if (empty($nama)) {
        return ['status' => 'Error', 'message' => 'Data input tidak boleh kosong'];
    }
    $data = getMerchantKategoriBisnisByName($db, $nama);
    if (!empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori telah ada atau telah ditambahkan'];
    }
    if (insertKategoriBisnis($db, $nama)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mendaftarkan Kategori'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mendaftarkan Kategori'];
}

function updateMerchantKategoriBisnis($db, $id, $nama) {
    if (empty($nama)) {
        return ['status' => 'Error', 'message' => 'Data input tidak boleh kosong'];
    }
    $data = getMerchantKategoriBisnisByName($db, $nama);
    if (!empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori telah ada atau telah ditambahkan'];
    }
    if (updateKategoriBisnis($db, $id, $nama)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Kategori'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mengupdate Kategori'];
}

function deleteMerchantKategoriBisnis($db, $id) {
    $data = getMerchantKategoriBisnisById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori tidak ada atau telah dihapus'];
    }
    if (deleteKategoriBisnis($db, $id)) {
        return ['status' => 'Success', 'message' => 'Berhasil Menghapus Kategori'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Menghapus Kategori'];
}

//
// MERCHANT KATEGORI BARANG
function getMerchantKategoriBarang($db) {
    $data = getMerchantKategoriBarangUKM($db);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori tidak ditemukan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Kategori', 'data' => $data];
}

function insertMerchantKategoriBarang($db, $nama) {
    if (empty($nama)) {
        return ['status' => 'Error', 'message' => 'Data input tidak boleh kosong'];
    }
    $data = getMerchantKategoriBarangByName($db, $nama);
    if (!empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori barang telah ada atau telah ditambahkan'];
    }
    if (insertKategoriBarang($db, $nama)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mendaftarkan Kategori Barang'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mendaftarkan Kategori Barang'];
}

function updateMerchantKategoriBarang($db, $id, $nama) {
    if (empty($nama)) {
        return ['status' => 'Error', 'message' => 'Data input tidak boleh kosong'];
    }
    $data = getMerchantKategoriBarangByName($db, $nama);
    if (!empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori barang telah ada atau telah ditambahkan'];
    }
    if (updateKategoriBarang($db, $id, $nama)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Kategori Barang'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mengupdate Kategori Barang'];
}

function deleteMerchantKategoriBarang($db, $id) {
    $data = getMerchantKategoriBarangById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori barang tidak ada atau telah dihapus'];
    }
    if (deleteKategoriBarang($db, $id)) {
        return ['status' => 'Success', 'message' => 'Berhasil Menghapus Kategori Barang'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Menghapus Kategori Barang'];
}

//
// WEBSITE GET TOTAL MITRA
function getTotalMitraWebsite($db) {
    $data = [];
    $data['total_driver'] = countsUserDriver($db);
    $data['total_customer'] = countsUserCustomer($db);
    $data['total_merchant'] = countsUserMerchant($db);
    $data['total_mitra'] = $data['total_driver'] + $data['total_customer'] + $data['total_merchant'];
    
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Mitra', 'data' => $data];
}