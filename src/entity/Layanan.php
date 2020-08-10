<?php

    class Layanan {
        private $nama_layanan;
        private $deskripsi_layanan;
        private $foto_layanan;
    
        public function __construct($nama_layanan, $deskripsi_layanan, $foto_layanan) {
            $this->nama_layanan = $nama_layanan;
            $this->deskripsi_layanan = $deskripsi_layanan;
            $this->foto_layanan = $foto_layanan;
        }
    
        public function getNama_layanan() {
            return $this->nama_layanan;
        }
    
        public function getDeskripsi_layanan() {
            return $this->deskripsi_layanan;
        }
    
        public function getFoto_layanan() {
            return $this->foto_layanan;
        }
    
    }
    