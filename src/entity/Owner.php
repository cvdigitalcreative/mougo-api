<?php

class Owner {
    private $email_owner;
    private $password;
    private $db;

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function __construct($email, $password) {
        $this->email_owner = $email;
        $this->password = $password;
    }

    public function loginOwner() {
        if (!$this->isValid(LOGIN)) {
            return ['status' => 'Error', 'message' => 'Email atau Password Harus Di isi'];
        }
        $data_owner = $this->cekDataOwner($this->email_owner);
        if (empty($data_owner)) {
            return ['status' => 'Error', 'message' => 'Email atau Password salah'];
        }
        if ($data_owner['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $data_owner['email_owner'], 'role' => 'Owner'];
        }
        return ['status' => 'Error', 'message' => 'Email atau Password Salah'];

    }

    public function isValid($status) {
        switch ($status) {
            case LOGIN:
                if (empty($this->email_owner) || empty($this->password)) {
                    return false;
                }return true;
        }
    }

    public function cekDataOwner($email) {
        $sql = "SELECT * FROM owner
                WHERE email_owner LIKE '$email'  ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function inputEvent($judul, $deskripsi, $gambar, $tanggal) {
        $sql = "INSERT INTO event (judul_event,deskripsi_event,gambar_event,tanggal_event)
                VALUE('$judul','$deskripsi','$gambar','$tanggal')";
        $est = $this->db->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Event Berhasil Dipublikasi'];
        }return ['status' => 'Error', 'message' => 'Event Gagal Diupload'];
    }

    public function editEvent($id, $judul, $deskripsi, $gambar, $tanggal) {
        $sql = "UPDATE event
                SET judul_event = '$judul', deskripsi_event = '$deskripsi', tanggal_event = '$tanggal'";
        if (!empty($gambar)) {
            $sql = $sql . ", gambar_event = '$gambar' ";
        }
        $sql = $sql . " WHERE id = '$id'";
        $est = $this->db->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Event Berhasil Dipublikasi'];
        }return ['status' => 'Error', 'message' => 'Event Gagal Diupload'];
    }

    public function getEvent() {
        $sql = "SELECT * FROM event ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;

    }

    public function getEventCommon() {
        $sql = "SELECT * FROM event
                ORDER BY tanggal_event DESC
                LIMIT 5";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;

    }

    public function cekEvent($id) {
        $sql = "SELECT * FROM event
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;

    }

    public function deleteEvent($id) {
        $sql = "DELETE FROM event
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();

    }

    public function getAdmin() {
        $sql = "SELECT email_admin, nama_admin, no_telpon FROM admin ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;

    }

    public function getTrip() {
        $sql = "SELECT driver.nama AS nama_driver, customer.nama AS nama_customer, alamat_jemput, alamat_destinasi, total_harga, tanggal_transaksi FROM trip
                INNER JOIN user AS customer ON customer.id_user = trip.id_customer
                INNER JOIN user AS driver ON driver.id_user = trip.id_driver
                ORDER BY tanggal_transaksi DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getTripAll() {
        $trip = $this->getTrip();
        if (empty($trip)) {
            return ['status' => 'Error', 'message' => 'Trip Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($trip); $i++) {
            $trip[$i]['nama_driver'] = decrypt($trip[$i]['nama_driver'], MOUGO_CRYPTO_KEY);
            $trip[$i]['nama_customer'] = decrypt($trip[$i]['nama_customer'], MOUGO_CRYPTO_KEY);
            $trip[$i]['total_harga'] = (double) $trip[$i]['total_harga'];
        }
        return ['status' => 'Success', 'data' => $trip];
    }

    public function getBonusLevel() {
        $sql = "SELECT user.nama, bonus_level.pendapatan, bonus_level.tanggal_pendapatan FROM bonus_level
                INNER JOIN user ON user.id_user = bonus_level.id_user
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getBonusLevelAll() {
        $Bonus = $this->getBonusLevel();
        if (empty($Bonus)) {
            return ['status' => 'Error', 'message' => 'Bonus Level Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($Bonus); $i++) {
            $Bonus[$i]['nama'] = decrypt($Bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
            $Bonus[$i]['pendapatan'] = (double) $Bonus[$i]['pendapatan'];
        }
        return ['status' => 'Success', 'data' => $Bonus];
    }

    public function getWithdraw() {
        $sql = "SELECT withdraw.id, user.nama, withdraw.jumlah, jenis_withdraw.jenis_withdraw, status_withdraw.status_withdraw, withdraw.tanggal_withdraw FROM withdraw
                INNER JOIN user ON user.id_user = withdraw.id_user
                INNER JOIN jenis_withdraw ON jenis_withdraw.id = withdraw.jenis_withdraw
                INNER JOIN status_withdraw ON status_withdraw.id = withdraw.status_withdraw
                ORDER BY status_withdraw ASC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getWithdrawAll() {
        $withdraw = $this->getWithdraw();
        if (empty($withdraw)) {
            return ['status' => 'Error', 'message' => 'History Withdraw Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($withdraw); $i++) {
            $withdraw[$i]['nama'] = decrypt($withdraw[$i]['nama'], MOUGO_CRYPTO_KEY);
            $withdraw[$i]['jumlah'] = (double) $withdraw[$i]['jumlah'];
        }
        return ['status' => 'Success', 'data' => $withdraw];
    }

    public function getTopup() {
        $sql = "SELECT user.nama, top_up.jumlah_topup, status_topup.status, admin.nama_admin, top_up.tanggal_topup FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN admin ON admin.email_admin = top_up.admin
                INNER JOIN status_topup ON status_topup.id = top_up.status_topup
                ORDER BY tanggal_topup DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getTopupAll() {
        $topup = $this->getTopup();
        if (empty($topup)) {
            return ['status' => 'Error', 'message' => 'History Topup Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($topup); $i++) {
            $topup[$i]['nama'] = decrypt($topup[$i]['nama'], MOUGO_CRYPTO_KEY);
            $topup[$i]['jumlah_topup'] = (double) $topup[$i]['jumlah_topup'];
        }
        return ['status' => 'Success', 'data' => $topup];
    }

    public function getBantuan() {
        $sql = "SELECT user.nama, bantuan.pertanyaan, bantuan.jawaban, bantuan.tanggal_bantuan FROM bantuan
                INNER JOIN user ON user.id_user = bantuan.id_user
                ORDER BY tanggal_bantuan DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getBantuanAll() {
        $topup = $this->getBantuan();
        if (empty($topup)) {
            return ['status' => 'Error', 'message' => 'History Bantuan Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($topup); $i++) {
            $topup[$i]['nama'] = decrypt($topup[$i]['nama'], MOUGO_CRYPTO_KEY);
        }
        return ['status' => 'Success', 'data' => $topup];
    }

}
