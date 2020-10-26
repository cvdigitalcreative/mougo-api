<?php

require_once dirname(__FILE__) . '/../entity/Blog.php';
require_once dirname(__FILE__) . '/../model/Blog.php';

function postBlog($db, $judul_blog, $isi_blog, $kategori_blog, $nama_penulis, $uploadedFile, $directory) {
    if (empty($judul_blog) || empty($isi_blog) || empty($kategori_blog) || empty($nama_penulis) || empty($uploadedFile['foto_blog']->file)) {
        return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
    }
    $path_blog = saveFile($uploadedFile['foto_blog'], FOTO_BLOG, $directory);
    if ($path_blog == STATUS_ERROR) {
        return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
    }

    $blog = new Blog($judul_blog, $isi_blog, $kategori_blog, $path_blog, $nama_penulis);
    if (insertBlog($db, $blog->getJudul_blog(), $blog->getIsi_blog(), $blog->getKategori_blog(), $blog->getNama_penulis(), $blog->getFoto_blog())) {
        return ['status' => 'Success', 'message' => 'Berhasil Memposting Blog'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Memposting Blog'];
}

function getBlog($db) {
    $data = getAllBlog($db);
    return ['status' => 'Success', 'message' => 'Berhasil Mengambil Data', 'data' => $data];
}

function getBlogDetail($db, $id) {
    $data = getBlogBy($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Gagal Mengambil Blog'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mengambil Data', 'data' => $data];
}

function deleteBlog($db, $id) {
    $data = getBlogBy($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Gagal Menghapus Blog Tidak Ditemukan'];
    }

    if (deleteBlogById($db, $id)) {
        unlink(PATH_PUBLIC.$data['foto_blog']);
        return ['status' => 'Success', 'message' => 'Berhasil Menghapus Blog'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Menghapus Blog'];
}

function editBlog($db, $id, $judul_blog, $isi_blog, $kategori_blog, $nama_penulis, $uploadedFile, $directory) {
    $data_blog = getBlogBy($db, $id);
    if (empty($data_blog)) {
        return ['status' => 'Error', 'message' => 'Gagal Mengupdate Blog Tidak Ditemukan'];
    }
    if (empty($judul_blog) & empty($isi_blog) & empty($kategori_blog) & empty($nama_penulis) & empty($uploadedFile['foto_blog']->file)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Blog'];
    }
    $path_blog = null;
    if (!empty($uploadedFile['foto_blog']->file)) {
        $path_blog = saveFile($uploadedFile['foto_blog'], FOTO_BLOG, $directory);
        if ($path_blog == STATUS_ERROR) {
            return ['status' => 'Error', 'message' => 'Gambar Harus JPG atau PNG'];
        }
        unlink(PATH_PUBLIC.$data_blog['foto_blog']);
    }
    $blog = new Blog($judul_blog, $isi_blog, $kategori_blog, $path_blog, $nama_penulis);
    if (updateBlog($db, $id, $blog->getJudul_blog(), $blog->getIsi_blog(), $blog->getKategori_blog(), $blog->getNama_penulis(), $blog->getFoto_blog())) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Blog'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mengupdate Blog'];
}

//
// KATEGORI BLOG
function getKategoriBlogAll($db) {
    $data = getKategoriBlog($db);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori tidak ditemukan'];
    }
    return ['status' => 'Success', 'message' => 'Berhasil Mendapatkan Kategori', 'data' => $data];
}

function insertKategoriBlogWeb($db, $nama) {
    if (empty($nama)) {
        return ['status' => 'Error', 'message' => 'Data input tidak boleh kosong'];
    }
    $data = getKategoriBlogByName($db, $nama);
    if (!empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori blog telah ada atau telah ditambahkan'];
    }
    if (insertKategoriBlog($db, $nama)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mendaftarkan Kategori Blog'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mendaftarkan Kategori Blog'];
}

function updateKategoriBlogWeb($db, $id, $nama) {
    if (empty($nama)) {
        return ['status' => 'Error', 'message' => 'Data input tidak boleh kosong'];
    }
    $data = getKategoriBlogByName($db, $nama);
    if (!empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori blog telah ada atau telah ditambahkan'];
    }
    if (updateKategoriBlog($db, $id, $nama)) {
        return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Kategori Blog'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Mengupdate Kategori Blog'];
}

function deleteKategoriBlogWeb($db, $id) {
    $data = getKategoriBlogById($db, $id);
    if (empty($data)) {
        return ['status' => 'Error', 'message' => 'Kategori blog tidak ada atau telah dihapus'];
    }
    if (deleteKategoriBlog($db, $id)) {
        return ['status' => 'Success', 'message' => 'Berhasil Menghapus Kategori Blog'];
    }
    return ['status' => 'Error', 'message' => 'Gagal Menghapus Kategori Blog'];
}
