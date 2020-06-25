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

    public function getBonusTrip() {
        $sql = "SELECT user.nama, bonus_trip.pendapatan, bonus_trip.tanggal_pendapatan FROM bonus_trip
                INNER JOIN user ON user.id_user = bonus_trip.id_user
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getBonusTripAll() {
        $Bonus = $this->getBonusTrip();
        if (empty($Bonus)) {
            return ['status' => 'Error', 'message' => 'Bonus Trip Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($Bonus); $i++) {
            $Bonus[$i]['nama'] = decrypt($Bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
            $Bonus[$i]['pendapatan'] = (double) $Bonus[$i]['pendapatan'];
        }
        return ['status' => 'Success', 'data' => $Bonus];
    }

    public function getBonusTransfer() {
        $sql = "SELECT user.nama, bonus_transfer.pendapatan, bonus_transfer.tanggal_transfer FROM bonus_transfer
                INNER JOIN user ON user.id_user = bonus_transfer.id_user
                ORDER BY tanggal_transfer DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getBonusTransferAll() {
        $Bonus = $this->getBonusTransfer();
        if (empty($Bonus)) {
            return ['status' => 'Error', 'message' => 'Bonus Transfer Tidak Ditemukan'];
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

    public function getTopupDriver() {
        $sql = "SELECT user.nama, top_up.jumlah_topup, status_topup.status, admin.nama_admin, top_up.tanggal_topup FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN admin ON admin.email_admin = top_up.admin
                INNER JOIN status_topup ON status_topup.id = top_up.status_topup
                WHERE user.role = '2'
                ORDER BY tanggal_topup DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getTopupDriverAll() {
        $topup = $this->getTopupDriver();
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

    public function getTopupAdmin($admin) {
        $sql = "SELECT * FROM top_up
                WHERE admin = '$admin'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function updateTopupAdmin($admin) {
        $sql = "UPDATE top_up
                SET admin = :admin
                WHERE admin = '$admin'";
        $est = $this->getDb()->prepare($sql);
        $data = [
            ':admin' => ADMIN_SILUMAN_MOUGO,
        ];
        return $est->execute($data);
    }

    public function deleteAdminOwner($id) {
        $admin = new Admin(null, null, null, null);
        $admin->setDb($this->db);
        if (empty($admin->cekDataAdmin($id, null))) {
            return ['status' => 'Error', 'message' => "Admin tidak ditemukan"];
        }
        if (!empty($this->getTopupAdmin($id))) {
            $this->updateTopupAdmin($id);
        }
        if (!$admin->deleteAdmin($id)) {
            return ['status' => 'Error', 'message' => "Gagal Menghapus Admin"];
        }
        return ['status' => 'Success', 'message' => "Berhasil Menghapus Admin"];
    }

    public function updateAdminOwner($id, $nama, $password, $no_telpon) {
        $admin = new Admin(null, null, null, null);
        $admin->setDb($this->db);
        if (empty($admin->cekDataAdmin($id, null))) {
            return ['status' => 'Error', 'message' => "Admin tidak ditemukan"];
        }
        if (empty($nama) && empty($password) && empty($no_telpon)) {
            return ['status' => 'Success', 'message' => "Tidak Ada Item Admin Yang Diupdate"];
        }
        $sql = "UPDATE admin
                SET ";
        if (!empty($nama)) {
            $sql = $sql . "nama_admin = '$nama' ";
        }
        if (!empty($nama) && !empty($password)) {
            $sql = $sql . ", ";
        }
        if (!empty($password)) {
            $sql = $sql . "password = '$password' ";
        }
        if ((!empty($nama) || !empty($password)) && !empty($no_telpon)) {
            $sql = $sql . ", ";
        }
        if (!empty($no_telpon)) {
            $sql = $sql . "no_telpon = '$no_telpon' ";
        }
        $sql = $sql . " WHERE email_admin = '$id' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return ['status' => 'Success', 'message' => "Berhasil Mengupdate Admin"];
    }

    public function getTransfer() {
        $sql = "SELECT sender.nama AS nama_pengirim, receipent.nama AS nama_penerima, transfer.total_transfer, transfer.tanggal_transfer FROM transfer
                INNER JOIN user AS sender ON sender.id_user = transfer.sender_user_id
                INNER JOIN user AS receipent ON receipent.id_user = transfer.receipent_user_id
                ORDER BY tanggal_transfer DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getTransferAll() {
        $transfer = $this->getTransfer();
        if (empty($transfer)) {
            return ['status' => 'Error', 'message' => 'History Transfer Tidak Ditemukan'];
        }
        for ($i = 0; $i < count($transfer); $i++) {
            $transfer[$i]['nama_pengirim'] = decrypt($transfer[$i]['nama_pengirim'], MOUGO_CRYPTO_KEY);
            $transfer[$i]['nama_penerima'] = decrypt($transfer[$i]['nama_penerima'], MOUGO_CRYPTO_KEY);
        }
        return ['status' => 'Success', 'data' => $transfer];
    }

    public function getCabang($nama) {
        $sql = "SELECT * FROM cabang
                WHERE cabang = '$nama'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getCabangId($id) {
        $sql = "SELECT * FROM cabang
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function insertCabang($id, $nama) {
        $sql = "INSERT INTO cabang (id, cabang)
                VALUE('$id', '$nama')";
        $est = $this->db->prepare($sql);
        return $est->execute();
    }

    public function updateCabang($id, $nama) {
        $sql = "UPDATE cabang
                SET cabang = '$nama'
                WHERE id = '$id'";
        $est = $this->db->prepare($sql);
        return $est->execute();
    }

    public function deleteCabang($id) {
        $sql = "DELETE FROM cabang
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();

    }

    public function getCabangAll() {
        $sql = "SELECT * FROM cabang";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getLastIdCabang() {
        $sql = "SELECT MAX(id) AS id FROM cabang";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getCabangAllOwner() {
        $dataCabang = $this->getCabangAll();
        if (empty($dataCabang)) {
            return ['status' => 'Success', 'message' => 'Cabang Kosong', 'data' => []];
        }return ['status' => 'Success', 'message' => 'Cabang Didapatkan', 'data' => $dataCabang];
    }

    public function ownerInsertCabang($nama) {
        if (!empty($this->getCabang($nama))) {
            return ['status' => 'Error', 'message' => 'Cabang Telah Terdaftar'];
        }
        $dataCabang = $this->getLastIdCabang();
        $id = $dataCabang[0]['id'] + 1;
        if ($this->insertCabang($id, $nama)) {
            return ['status' => 'Success', 'message' => 'Cabang Berhasil Ditambahkan'];
        }return ['status' => 'Error', 'message' => 'Cabang Gagal Ditambahkan'];
    }

    public function ownerUpdateCabang($nama, $id) {
        if (empty($nama)) {
            return ['status' => 'Error', 'message' => 'Nama Cabang Tidak Boleh Kosong'];
        }
        if (!empty($this->getCabang($nama))) {
            return ['status' => 'Error', 'message' => 'Cabang Telah Terdaftar'];
        }
        if (empty($this->getCabangId($id))) {
            return ['status' => 'Error', 'message' => 'Id Tidak Ditemukan'];
        }
        if ($this->updateCabang($id, $nama)) {
            return ['status' => 'Success', 'message' => 'Cabang Berhasil Diupdate'];
        }return ['status' => 'Error', 'message' => 'Cabang Gagal Diupdate'];
    }

    public function ownerDeleteCabang($id) {
        if (empty($this->getCabangId($id))) {
            return ['status' => 'Error', 'message' => 'Id Tidak Ditemukan'];
        }
        if ($this->deleteCabang($id)) {
            return ['status' => 'Success', 'message' => 'Cabang Berhasil Dihapus'];
        }return ['status' => 'Error', 'message' => 'Cabang Gagal Dihapus'];
    }

    public function getTopupSaldoMonthly() {
        $sql = "SELECT SUM(jumlah_topup) AS topup_saldo_bulanan FROM top_up
                WHERE MONTH(tanggal_topup) = MONTH(CURRENT_DATE())
                AND YEAR(tanggal_topup) = YEAR(CURRENT_DATE())";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function getTripMonthly() {
        $id_driver_siluman = ID_DRIVER_SILUMAN;
        $sql = "SELECT COUNT(id_trip) AS trip_bulanan FROM trip
                WHERE MONTH(tanggal_transaksi) = MONTH(CURRENT_DATE())
                AND YEAR(tanggal_transaksi) = YEAR(CURRENT_DATE())
                AND id_driver != '$id_driver_siluman'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function getTripTransaksiMonthly() {
        $id_driver_siluman = ID_DRIVER_SILUMAN;
        $sql = "SELECT SUM(total_harga) AS tansaksi_bulanan FROM trip
                WHERE MONTH(tanggal_transaksi) = MONTH(CURRENT_DATE())
                AND YEAR(tanggal_transaksi) = YEAR(CURRENT_DATE())
                AND id_driver != '$id_driver_siluman'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function ownerRekapDasbor() {
        $saldo_topup_month = $this->getTopupSaldoMonthly();
        $trip_month = $this->getTripMonthly();
        $transaksi_month = $this->getTripTransaksiMonthly();
        $data['tansaksi_bulanan'] = (double) $transaksi_month['tansaksi_bulanan'];
        $data['topup_saldo_bulanan'] = (double) $saldo_topup_month['topup_saldo_bulanan'];
        $data['trip_bulanan'] = (double) $trip_month['trip_bulanan'];
        return ['status' => 'Success', 'message' => 'Rekapitulasi Bulanan', 'data' => $data];

    }

    public function ownerRekapStruktur() {
        $struktur = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $struktur->setDb($this->db);
        $id_perusahaan = ID_PERUSAHAAN;
        $data2 = $struktur->getAllReferalBawahan($id_perusahaan);
        $data['jumlah_mitra'] = (double) $data2['data']['jumlah_mitra_referal'] - 2;
        $data['jumlah_mitra_level1'] = (double) count($data2['data']['mitra_referal'][0]) - 1;
        $data['jumlah_mitra_level2'] = (double) count($data2['data']['mitra_referal'][1]);
        $data['jumlah_mitra_level3'] = (double) count($data2['data']['mitra_referal'][2]);
        $data['jumlah_mitra_level4'] = (double) count($data2['data']['mitra_referal'][3]);
        return ['status' => 'Success', 'message' => 'Rekapitulasi Bulanan', 'data' => $data];

    }

    private $column_search_event = array('judul_event', 'deskripsi_event', 'gambar_event', 'tanggal_event');
    private $event_id = array('judul_event' => 'asc');

    public function getEventWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getEventQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getEventQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM event";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0 || 1 || 2 || 3) {
                $temp = "event";
            }
            $order_in = $this->column_search_event[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->event_id)) {
            $order_by = $this->event_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsEvent() {
        $sql = "SELECT * FROM event";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_driver = array('nama', 'email', 'no_telpon', 'no_polisi', 'cabang', 'jenis_kendaraan', 'merk_kendaraan', 'no_rekening', 'nama_bank', 'foto_diri', 'foto_ktp', 'foto_kk', 'foto_sim', 'foto_skck', 'foto_stnk');
    private $driver_id = array('nama' => 'asc');

    public function getDriverWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getDriverQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getDriverQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM user
                INNER JOIN driver ON driver.id_user = user.id_user
                INNER JOIN cabang ON cabang.id = driver.cabang
                INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
                INNER JOIN detail_user ON detail_user.id_user = driver.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                WHERE user.role = 2";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0 || $order_by == 1 || $order_by == 2) {
                $temp = "user";
            } else if ($order_by == 3 || $order_by == 4 || $order_by == 5 || $order_by == 6 || $order_by == 9 || $order_by == 12 || $order_by == 13 || $order_by == 14) {
                $temp = "driver";
            } else if ($order_by == 7 || $order_by == 8 || $order_by == 10 || $order_by == 11) {
                $temp = "detail_user";
            }
            $order_in = $this->column_search_driver[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->driver_id)) {
            $order_by = $this->driver_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsDriver() {
        $sql = "SELECT * FROM user
                INNER JOIN driver ON driver.id_user = user.id_user
                INNER JOIN cabang ON cabang.id = driver.cabang
                INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
                INNER JOIN detail_user ON detail_user.id_user = driver.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                WHERE user.role = 2";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_customer = array('nama', 'email', 'no_telpon', 'provinsi', 'kota', 'no_rekening', 'nama_bank', 'kode_referal', 'kode_sponsor');
    private $customer_id = array('nama' => 'asc');

    public function getCustomerWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getCustomerQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getCustomerQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
                WHERE user.role = 1";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0 || $order_by == 1 || $order_by == 2) {
                $temp = "user";
            } else if ($order_by == 3 || $order_by == 4 || $order_by == 5 || $order_by == 6) {
                $temp = "detail_user";
            } else if ($order_by == 7) {
                $temp = "kode_referal";
            } else if ($order_by == 8) {
                $temp = "kode_sponsor";
            }
            $order_in = $this->column_search_customer[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->customer_id)) {
            $order_by = $this->customer_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsCustomer() {
        $sql = "SELECT * FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
                WHERE user.role = 1";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_admin = array('email_admin', 'nama_admin', 'no_telpon');
    private $admin_id = array('email_admin' => 'asc');

    public function getAdminWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getAdminQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getAdminQuery($order_by, $order, $search) {
        $sql = "SELECT email_admin, nama_admin, no_telpon FROM admin";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0 || 1 || 2) {
                $temp = "admin";
            }
            $order_in = $this->column_search_admin[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->admin_id)) {
            $order_by = $this->admin_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsAdmin() {
        $sql = "SELECT email_admin, nama_admin, no_telpon FROM admin";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_cabang = array('id', 'cabang');
    private $cabang_id = array('id' => 'asc');

    public function getCabangWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getCabangQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getCabangQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM cabang";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0 || 1) {
                $temp = "cabang";
            }
            $order_in = $this->column_search_cabang[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->cabang_id)) {
            $order_by = $this->cabang_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsCabang() {
        $sql = "SELECT * FROM cabang";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_topup = array('nama', 'jumlah_topup', 'status_topup', 'admin', 'tanggal_topup');
    private $topup_id = array('nama' => 'asc');

    public function getTopupWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getTopupQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTopupQuery($order_by, $order, $search) {
        $sql = "SELECT user.nama, top_up.jumlah_topup, status_topup.status, admin.nama_admin, top_up.tanggal_topup FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN admin ON admin.email_admin = top_up.admin
                INNER JOIN status_topup ON status_topup.id = top_up.status_topup";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "user";
            } else if ($order_by == 1 || 2 || 3 || 4) {
                $temp = "top_up";
            }
            $order_in = $this->column_search_topup[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->topup_id)) {
            $order_by = $this->topup_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsTopup() {
        $sql = "SELECT user.nama, top_up.jumlah_topup, status_topup.status, admin.nama_admin, top_up.tanggal_topup FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN admin ON admin.email_admin = top_up.admin
                INNER JOIN status_topup ON status_topup.id = top_up.status_topup";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_transfer = array('nama', 'nama', 'total_transfer', 'tanggal_transfer');
    private $transfer_id = array('nama' => 'asc');

    public function getTransferWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getTransferQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTransferQuery($order_by, $order, $search) {
        $sql = "SELECT sender.nama AS nama_pengirim, receipent.nama AS nama_penerima, transfer.total_transfer, transfer.tanggal_transfer FROM transfer
                INNER JOIN user AS sender ON sender.id_user = transfer.sender_user_id
                INNER JOIN user AS receipent ON receipent.id_user = transfer.receipent_user_id";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "sender";
            } else if ($order_by == 1) {
                $temp = "receipent";
            } else if ($order_by == 2 || 3) {
                $temp = "transfer";
            }
            $order_in = $this->column_search_transfer[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->transfer_id)) {
            $order_by = $this->transfer_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsTransfer() {
        $sql = "SELECT sender.nama AS nama_pengirim, receipent.nama AS nama_penerima, transfer.total_transfer, transfer.tanggal_transfer FROM transfer
                INNER JOIN user AS sender ON sender.id_user = transfer.sender_user_id
                INNER JOIN user AS receipent ON receipent.id_user = transfer.receipent_user_id";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_bonus = array('nama', 'pendapatan', 'tanggal_pendapatan');
    private $bonus_id = array('nama' => 'asc');

    public function getBonusLevelWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getBonusLevelQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getBonusLevelQuery($order_by, $order, $search) {
        $sql = "SELECT user.nama, bonus_level.pendapatan, bonus_level.tanggal_pendapatan FROM bonus_level
                INNER JOIN user ON user.id_user = bonus_level.id_user";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "user";
            } else if ($order_by == 1 || 2) {
                $temp = "bonus_level";
            }
            $order_in = $this->column_search_bonus[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->bonus_id)) {
            $order_by = $this->bonus_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsBonusLevel() {
        $sql = "SELECT user.nama, bonus_level.pendapatan, bonus_level.tanggal_pendapatan FROM bonus_level
                INNER JOIN user ON user.id_user = bonus_level.id_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    public function getBonusTripWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getBonusTripQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getBonusTripQuery($order_by, $order, $search) {
        $sql = "SELECT user.nama, bonus_trip.pendapatan, bonus_trip.tanggal_pendapatan FROM bonus_trip
        INNER JOIN user ON user.id_user = bonus_trip.id_user";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "user";
            } else if ($order_by == 1 || 2) {
                $temp = "bonus_trip";
            }
            $order_in = $this->column_search_bonus[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->bonus_id)) {
            $order_by = $this->bonus_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsBonusTrip() {
        $sql = "SELECT user.nama, bonus_trip.pendapatan, bonus_trip.tanggal_pendapatan FROM bonus_trip
                INNER JOIN user ON user.id_user = bonus_trip.id_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_bonus_tf = array('nama', 'pendapatan', 'tanggal_transfer');
    private $bonus_tf_id = array('nama' => 'asc');

    public function getBonusTransferWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getBonusTransferQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getBonusTransferQuery($order_by, $order, $search) {
        $sql = "SELECT user.nama, bonus_transfer.pendapatan, bonus_transfer.tanggal_transfer FROM bonus_transfer
                INNER JOIN user ON user.id_user = bonus_transfer.id_user";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "user";
            } else if ($order_by == 1 || 2) {
                $temp = "bonus_transfer";
            }
            $order_in = $this->column_search_bonus_tf[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->bonus_tf_id)) {
            $order_by = $this->bonus_tf_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsBonusTransfer() {
        $sql = "SELECT user.nama, bonus_transfer.pendapatan, bonus_transfer.tanggal_transfer FROM bonus_transfer
                INNER JOIN user ON user.id_user = bonus_transfer.id_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_trip = array('nama', 'nama', 'alamat_jemput', 'alamat_destinasi', 'tanggal_transaksi', 'total_harga');
    private $trip_id = array('nama' => 'asc');

    public function getTripWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getTripQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTripQuery($order_by, $order, $search) {
        $sql = "SELECT driver.nama AS nama_driver, customer.nama AS nama_customer, alamat_jemput, alamat_destinasi, total_harga, tanggal_transaksi FROM trip
                INNER JOIN user AS customer ON customer.id_user = trip.id_customer
                INNER JOIN user AS driver ON driver.id_user = trip.id_driver";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "customer";
            } else if ($order_by == 1) {
                $temp = "driver";
            } else if ($order_by == 2 || 3 || 4 || 5) {
                $temp = "trip";
            }
            $order_in = $this->column_search_trip[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->trip_id)) {
            $order_by = $this->trip_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsTrip() {
        $sql = "SELECT driver.nama AS nama_driver, customer.nama AS nama_customer, alamat_jemput, alamat_destinasi, total_harga, tanggal_transaksi FROM trip
                INNER JOIN user AS customer ON customer.id_user = trip.id_customer
                INNER JOIN user AS driver ON driver.id_user = trip.id_driver";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_user = array('nama', 'email', 'no_telpon', 'provinsi', 'kota', 'kode_referal', 'kode_sponsor', 'no_rekening', 'atas_nama_bank');
    private $user_id = array('nama' => 'asc');

    public function getUserWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getUserQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getUserQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0 || $order_by == 1 || $order_by == 2) {
                $temp = "user";
            } else if ($order_by == 3 || $order_by == 4) {
                $temp = "detail_user";
            } else if ($order_by == 5) {
                $temp = "kode_referal";
            } else if ($order_by == 6) {
                $temp = "kode_sponsor";
            } else if ($order_by == 7 || $order_by == 8) {
                $temp = "detail_user";
            }
            $order_in = $this->column_search_user[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->user_id)) {
            $order_by = $this->user_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsUser() {
        $sql = "SELECT * FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    
    private $column_search_trip_driver = array('nama', 'tanggal_transaksi', 'total_harga');
    private $trip_driver_id = array('nama' => 'asc');

    public function getTripDriverWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getTripDriverQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTripDriverQuery($order_by, $order, $search) {
        $sql = "SELECT user.nama , total_harga, tanggal_transaksi FROM trip
                INNER JOIN user ON user.id_user = trip.id_driver
                WHERE user.role = 2";
        // foreach ($this->column_search as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR ";
        //             if($index === 1){
        //                 $sql = $sql . "top_up.";
        //             }
        //             $sql = $sql . "$value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by == 0) {
                $temp = "user";
            } else if ($order_by == 1 || $order_by == 2) {
                $temp = "trip";
            } 
            $order_in = $this->column_search_trip_driver[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->trip_driver_id)) {
            $order_by = $this->trip_driver_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsTripDriver() {
        $sql = "SELECT user.nama , total_harga, tanggal_transaksi FROM trip
                INNER JOIN user ON user.id_user = trip.id_driver
                WHERE user.role = 2";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

}
