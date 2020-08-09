<?php

class DetailMerchant {
    private $id_user;
    private $no_izin;
    private $no_fax;
    private $nama_direktur;
    private $lama_bisnis;
    private $omset_perbulan;
    private $foto_dokumen_perizinan;
    private $foto_rekening_tabungan;

    public function __construct($id_user, $no_izin, $no_fax, $nama_direktur, $lama_bisnis, $omset_perbulan, $foto_dokumen_perizinan, $foto_rekening_tabungan) {
        $this->id_user = $id_user;
        $this->no_izin = $no_izin;
        $this->no_fax = $no_fax;
        $this->nama_direktur = $nama_direktur;
        $this->lama_bisnis = $lama_bisnis;
        $this->omset_perbulan = $omset_perbulan;
        $this->foto_dokumen_perizinan = $foto_dokumen_perizinan;
        $this->foto_rekening_tabungan = $foto_rekening_tabungan;
    }

    public function getId_user() {
        return $this->id_user;
    }

    public function getNo_izin() {
        return $this->no_izin;
    }

    public function getNo_fax() {
        return $this->no_fax;
    }

    public function getNama_direktur() {
        return $this->nama_direktur;
    }

    public function getLama_bisnis() {
        return $this->lama_bisnis;
    }

    public function getOmset_perbulan() {
        return $this->omset_perbulan;
    }

    public function getFoto_dokumen_perizinan() {
        return $this->foto_dokumen_perizinan;
    }

    public function getFoto_rekening_tabungan() {
        return $this->foto_rekening_tabungan;
    }

}
