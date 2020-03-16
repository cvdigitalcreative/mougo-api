<?php
require_once dirname(__FILE__) . '/User.php';

class Driver extends User {
    private $no_polisi;
    private $cabang;
    private $alamat_domisili;
    private $merk_kendaraan;
    private $jenis_kendaraan;

    public function __construct($no_polisi, $cabang, $alamat_domisili, $merk_kendaraan, $jenis_kendaraan) {
        $this->no_polisi = $no_polisi;
        $this->cabang = $cabang;
        $this->alamat_domisili = $alamat_domisili;
        $this->merk_kendaraan = $merk_kendaraan;
        $this->jenis_kendaraan = $jenis_kendaraan;
    }

    public function regDriver($userDriver) {
        $this->no_polisi = $userDriver['no_polisi'];
        $this->cabang = $userDriver['cabang'];
        $this->alamat_domisili = $userDriver['alamat_domisili'];
        $this->merk_kendaraan = $userDriver['merk_kendaraan'];
        $this->jenis_kendaraan = $userDriver['jenis_kendaraan'];
    }

    public function driverRegis() {
        if ($this->insertDriver()) {
            return ['status' => 'Success', 'message' => 'Driver Terdaftar'];
        }return ['status' => 'Error', 'message' => 'Gagal Daftar Driver'];
    }

    public function driverData() {

        $isValid = true;
        if (empty($this->getNo_polisi()) || empty($this->getCabang()) || empty($this->getAlamat_domisili()) || empty($this->getMerk_kendaraan()) || empty($this->getJenis_kendaraan())) {
            $isValid = false;
        }
        return $isValid;

    }

    public function getNo_polisi() {
        return $this->no_polisi;
    }
    public function setNo_polisi($no_polisi) {
        $this->no_polisi = $no_polisi;
    }

    public function getCabang() {
        return $this->cabang;
    }
    public function setCabang($cabang) {
        $this->cabang = $cabang;
    }

    public function getAlamat_domisili() {
        return $this->alamat_domisili;
    }
    public function setAlamat_domisili($alamat_domisili) {
        $this->alamat_domisili = $alamat_domisili;
    }

    public function getMerk_kendaraan() {
        return $this->merk_kendaraan;
    }
    public function setMerk_kendaraan($merk_kendaraan) {
        $this->merk_kendaraan = $merk_kendaraan;
    }

    public function getJenis_kendaraan() {
        return $this->jenis_kendaraan;
    }
    public function setJenis_kendaraan($jenis_kendaraan) {
        $this->jenis_kendaraan = $jenis_kendaraan;
    }

    public function insertDriver() {
        $sql_driver = "INSERT INTO driver (id_user , status_online , no_polisi , cabang , alamat_domisili , merk_kendaraan , jenis_kendaraan , status_akun_aktif , foto_skck , foto_sim , foto_stnk)
                                VALUES (:id_user , :status_online , :no_polisi , :cabang , :alamat_domisili , :merk_kendaraan , :jenis_kendaraan , :status_akun_aktif , '' , '' , '')";
        $data_driver = [
            ':id_user' => $this->getId_user(),
            ':status_online' => STATUS_ONLINE,
            ':no_polisi' => $this->getNo_polisi(),
            ':cabang' => $this->getCabang(),
            ':alamat_domisili' => $this->getAlamat_domisili(),
            ':merk_kendaraan' => $this->getMerk_kendaraan(),
            ':jenis_kendaraan' => $this->getJenis_kendaraan(),
            ':status_akun_aktif' => STATUS_AKUN_AKTIF,
        ];
        $est_d = $this->getDb()->prepare($sql_driver);
        if ($est_d->execute($data_driver)) {
            return true;
        }return false;
    }

    
}
