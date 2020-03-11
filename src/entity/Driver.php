<?php

class Driver {
    private $id_user;
    private $no_polisi;
    private $cabang;
    private $alamat_domisili;
    private $merk_kendaraan;
    private $jenis_kendaraan;

    public function __construct($id_user,$no_polisi,$cabang,$alamat_domisili,$merk_kendaraan,$jenis_kendaraan) {
        $this->id_user = $id_user;
        $this->emano_polisiil = $no_polisi;
        $this->cabang = $cabang;
        $this->alamat_domisili = $alamat_domisili;
        $this->merk_kendaraan = $merk_kendaraan;
        $this->jenis_kendaraan = $jenis_kendaraan;
    }

    public function regDriver($userDriver) {
        $this->id_user = NULL;
        $this->no_polisi = $userDriver['no_polisi'];
        $this->cabang = $userDriver['cabang'];
        $this->alamat_domisili = $userDriver['alamat_domisili'];
        $this->merk_kendaraan = $userDriver['merk_kendaraan'];
        $this->jenis_kendaraan = $userDriver['jenis_kendaraan'];
    }

    function getId_user(){
        return $this->id_user;
    }
    function setId_user($id_user){
        $this->id_user = $id_user;
    }

    function getNo_polisi(){
        return $this->no_polisi;
    }
    function setNo_polisi($no_polisi){
        $this->no_polisi = $no_polisi;
    }

    function getCabang(){
        return $this->cabang;
    }
    function setCabang($cabang){
        $this->cabang = $cabang;
    }

    function getAlamat_domisili(){
        return $this->alamat_domisili;
    }
    function setAlamat_domisili($alamat_domisili){
        $this->alamat_domisili = $alamat_domisili;
    }

    function getMerk_kendaraan(){
        return $this->merk_kendaraan;
    }
    function setMerk_kendaraan($merk_kendaraan){
        $this->merk_kendaraan = $merk_kendaraan;
    }

    function getJenis_kendaraan(){
        return $this->jenis_kendaraan;
    }
    function setJenis_kendaraan($jenis_kendaraan){
        $this->jenis_kendaraan = $jenis_kendaraan;
    }
}