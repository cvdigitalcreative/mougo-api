<?php
function insertBlog($db,  $judul_blog, $isi_blog, $kategori_blog, $nama_penulis, $foto_blog) {
    $sql = "INSERT INTO blog (judul_blog, isi_blog, kategori_blog, nama_penulis, foto_blog)
                    VALUES ('$judul_blog', '$isi_blog', $kategori_blog, '$nama_penulis', '$foto_blog')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getAllBlog($db) {
    $sql = "SELECT blog.id_blog, blog.judul_blog, blog.isi_blog, kategori_blog.id_kategori, kategori_blog.nama_kategori AS kategori_blog, blog.nama_penulis, blog.foto_blog, blog.tanggal_posting FROM blog
            INNER JOIN kategori_blog ON kategori_blog.id_kategori = blog.kategori_blog";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function deleteBlogById($db, $id) {
    $sql = "DELETE FROM blog
            WHERE id_blog = $id";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getBlogBy($db, $id) {
    $sql = "SELECT blog.id_blog, blog.judul_blog, blog.isi_blog, kategori_blog.id_kategori, kategori_blog.nama_kategori AS kategori_blog, blog.nama_penulis, blog.foto_blog , blog.tanggal_posting FROM blog
            INNER JOIN kategori_blog ON kategori_blog.id_kategori = blog.kategori_blog
            WHERE id_blog = $id";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetch();
}

function updateBlog($db, $id, $judul_blog, $isi_blog, $kategori_blog, $nama_penulis, $foto_blog) {
    $sql = "UPDATE blog
                SET ";
    if (!empty($judul_blog)) {
        $sql = $sql . "judul_blog = '$judul_blog' ";
    }
    if (!empty($judul_blog) && !empty($isi_blog)) {
        $sql = $sql . ", ";
    }
    if (!empty($isi_blog)) {
        $sql = $sql . "isi_blog = '$isi_blog' ";
    }
    if ((!empty($judul_blog) || !empty($isi_blog)) && !empty($kategori_blog)) {
        $sql = $sql . ", ";
    }
    if (!empty($kategori_blog)) {
        $sql = $sql . "kategori_blog = '$kategori_blog' ";
    }
    if (((!empty($judul_blog) || !empty($isi_blog)) || !empty($kategori_blog)) && !empty($nama_penulis)) {
        $sql = $sql . ", ";
    }
    if (!empty($nama_penulis)) {
        $sql = $sql . "nama_penulis = '$nama_penulis' ";
    }
    if (((!empty($judul_blog) || !empty($isi_blog)) || !empty($kategori_blog) || !empty($nama_penulis)) && !empty($foto_blog)) {
        $sql = $sql . ", ";
    }
    if (!empty($foto_blog)) {
        $sql = $sql . "foto_blog = '$foto_blog' ";
    }
    if (((!empty($judul_blog) || !empty($isi_blog)) || !empty($kategori_blog) || !empty($nama_penulis)) || !empty($foto_blog)) {
        $sql = $sql . ", tanggal_posting = tanggal_posting";
    }
    $sql = $sql . " WHERE id_blog = $id ";
    $est = $db->prepare($sql);
    return $est->execute();
}

//
// KATEGORI BLOG
function getKategoriBlog($db) {
    $sql = "SELECT * FROM kategori_blog";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function getKategoriBlogByName($db, $nama) {
    $sql = "SELECT * FROM kategori_blog
            WHERE nama_kategori = '$nama'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function insertKategoriBlog($db, $nama) {
    $sql = "INSERT INTO kategori_blog (nama_kategori)
                VALUES ('$nama')";
    $est = $db->prepare($sql);
    return $est->execute();
}

function getKategoriBlogById($db, $id) {
    $sql = "SELECT * FROM kategori_blog
            WHERE id_kategori = '$id'";
    $est = $db->prepare($sql);
    $est->execute();
    return $est->fetchAll();
}

function updateKategoriBlog($db, $id, $nama) {
    $sql = "UPDATE kategori_blog
            SET nama_kategori = '$nama'
            WHERE id_kategori = $id";
    $est = $db->prepare($sql);
    return $est->execute();
}

function deleteKategoriBlog($db, $id) {
    $sql = "DELETE FROM kategori_blog
            WHERE id_kategori = '$id'";
    $est = $db->prepare($sql);
    return $est->execute();
}
