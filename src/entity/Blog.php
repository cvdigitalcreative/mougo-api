<?php

    class Blog {
        private $judul_blog;
        private $isi_blog;
        private $kategori_blog;
        private $foto_blog;
        private $nama_penulis;
    
        public function __construct($judul_blog, $isi_blog, $kategori_blog, $foto_blog, $nama_penulis) {
            $this->judul_blog = $judul_blog;
            $this->isi_blog = $isi_blog;
            $this->kategori_blog = $kategori_blog;
            $this->foto_blog = $foto_blog;
            $this->nama_penulis = $nama_penulis;
        }
    
        public function getJudul_blog() {
            return $this->judul_blog;
        }
    
        public function getIsi_blog() {
            return $this->isi_blog;
        }
    
        public function getKategori_blog() {
            return $this->kategori_blog;
        }
    
        public function getFoto_blog() {
                return $this->foto_blog;
        }
        
        public function getNama_penulis() {
                return $this->nama_penulis;
        }
        
    }
    