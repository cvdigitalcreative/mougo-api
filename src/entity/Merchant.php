<?php

class Merchant {
    private $id_user;
    private $nama_usaha;
    private $alamat_usaha;
    private $no_telpon_kantor;
    private $url_web_aplikasi;
    private $status_online_merchant;
    private $status_verifikasi_merchant;

    public function __construct($id_user, $nama_usaha, $alamat_usaha, $no_telpon_kantor, $url_web_aplikasi) {
        $this->id_user = $id_user;
        $this->nama_usaha = $nama_usaha;
        $this->alamat_usaha = $alamat_usaha;
        $this->no_telpon_kantor = $no_telpon_kantor;
        $this->url_web_aplikasi = $url_web_aplikasi;
        $this->status_online_merchant = STATUS_MERCHANT_OFFLINE;
        $this->status_verifikasi_merchant = STATUS_MERCHANT_PENDING;
    }

    public function getId_user() {
        return $this->id_user;
    }

    public function getNama_usaha() {
        return $this->nama_usaha;
    }

    public function getAlamat_usaha() {
        return $this->alamat_usaha;
    }

    public function getNo_telpon_kantor() {
        return $this->no_telpon_kantor;
    }

    public function getUrl_web_aplikasi() {
        return $this->url_web_aplikasi;
    }

    public function getStatus_online_merchant() {
        return $this->status_online_merchant;
    }

    public function getStatus_verifikasi_merchant() {
        return $this->status_verifikasi_merchant;
    }

}
