<?php

function insertDetailMerchant($db, $id_user, $no_izin, $no_fax, $nama_direktur, $lama_bisnis, $omset_perbulan, $foto_dokumen_perizinan, $foto_rekening_tabungan) {
    $sql = "INSERT INTO detail_ukm (id_user, no_izin, no_fax, nama_direktur, lama_bisnis, omset_perbulan, foto_dokumen_perizinan, foto_rekening_tabungan)
                VALUES ('$id_user', '$no_izin', '$no_fax', '$nama_direktur', '$lama_bisnis', '$omset_perbulan', '$foto_dokumen_perizinan', '$foto_rekening_tabungan')";
    $est = $db->prepare($sql);
    return $est->execute();
}
