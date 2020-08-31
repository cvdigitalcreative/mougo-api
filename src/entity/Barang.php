<?php

class Barang {
    private $nama_barang;
    private $harga_barang;
    private $foto_barang;
    private $kategori_barang;

    public function __construct($nama_barang, $harga_barang, $foto_barang, $kategori_barang) {
        $this->nama_barang = $nama_barang;
        $this->harga_barang = $harga_barang;
        $this->foto_barang = $foto_barang;
        $this->kategori_barang = $kategori_barang;
    }

    public function getNama_barang() {
        return $this->nama_barang;
    }

    public function getHarga_barang() {
        return $this->harga_barang;
    }

    public function getFoto_barang() {
        return $this->foto_barang;
    }

    public function getKategori_barang() {
        return $this->kategori_barang;
    }


}
