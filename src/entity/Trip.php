<?php

require_once dirname(__FILE__) . '/Umum.php';

class Trip {
    private $id_customer;
    private $id_driver;
    private $total_harga;
    private $alamat_jemput;
    private $latitude_jemput;
    private $longitude_jemput;
    private $alamat_destinasi;
    private $latitude_destinasi;
    private $longitude_destinasi;
    private $jarak;
    private $tanggal_transaksi;
    private $jenis_trip;
    private $status_trip;
    private $jenis_pembayaran;
    private $db;

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function __construct($id_customer, $id_driver, $total_harga, $alamat_jemput, $latitude_jemput, $longitude_jemput, $alamat_destinasi, $latitude_destinasi, $longitude_destinasi, $jarak, $tanggal_transaksi, $jenis_trip, $status_trip, $jenis_pembayaran) {
        $this->id_customer = $id_customer;
        $this->id_driver = $id_driver;
        $this->total_harga = $total_harga;
        $this->alamat_jemput = $alamat_jemput;
        $this->latitude_jemput = $latitude_jemput;
        $this->longitude_jemput = $longitude_jemput;
        $this->alamat_destinasi = $alamat_destinasi;
        $this->latitude_destinasi = $latitude_destinasi;
        $this->longitude_destinasi = $longitude_destinasi;
        $this->jarak = $jarak;
        $this->tanggal_transaksi = $tanggal_transaksi;
        $this->jenis_trip = $jenis_trip;
        $this->status_trip = $status_trip;
        $this->jenis_pembayaran = $jenis_pembayaran;
    }

    public function order_trip() {
        if (!$this->isDataValid()) {
            return ['status' => 'Error', 'message' => 'Order Trip Data Input Tidak Boleh Kosong'];
        }
        if ($this->jenis_pembayaran == PEMBAYARAN_SALDO) {
            $trip_cek = new Umum();
            $trip_cek->setDb($this->db);
            $saldo_user = $trip_cek->getSaldoUser($this->id_customer);
            if ($saldo_user['jumlah_saldo'] < $this->total_harga) {
                return ['status' => 'Error', 'message' => 'Saldo User Kurang Untuk Melakukan Trip'];
            }
            $saldo_user = $saldo_user['jumlah_saldo'];
            if (!$this->saldoTripUser($this->id_customer, TIPE_TRIP_ACCEPT, $this->jenis_pembayaran, USER_ROLE, $this->total_harga, $saldo_user)) {
                return ['status' => 'Error', 'message' => 'Gagal Update Saldo User'];
            }
        }
        // $this->hitungHarga();
        if ($this->cekTemporaryId() == 0 || $this->cekTemporaryId() == 1) {
            $this->resetAutoIncrement($this->cekMaxId() + 1);
        }
        $data['id_trip'] = $this->inputTemporaryOrder();
        if (!$data['id_trip']) {
            return ['status' => 'Error', 'message' => 'Pemesanan Error'];
        }
        $data['status_trip'] = $this->status_trip;
        return ['status' => 'Success', 'data' => $data];

    }

    public function cekTemporaryId() {
        $sql = "SELECT AUTO_INCREMENT AS id
                FROM information_schema.tables
                WHERE table_name = 'temporary_order'
                AND table_schema = DATABASE() ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt['id'];
    }

    public function resetAutoIncrement($id) {
        $sql = "ALTER TABLE temporary_order AUTO_INCREMENT = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function cekMaxId() {
        $sql = "SELECT MAX(id_trip) AS max_id
                FROM trip";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt['max_id'];
    }

    private function isDataValid() {
        $isValid = true;
        if (empty($this->id_customer) || empty($this->total_harga) || empty($this->alamat_jemput) || empty($this->latitude_jemput) || empty($this->longitude_jemput) || empty($this->alamat_destinasi) || empty($this->latitude_destinasi) || empty($this->longitude_destinasi) || empty($this->jarak) || empty($this->jenis_trip) || empty($this->jenis_pembayaran)) {
            $isValid = false;
        }
        return $isValid;

    }

    public function saldoTripUser($id_user, $trip_fungsi, $jenis_pembayaran, $role, $harga, $saldo_user) {
        $umum = new Umum();
        $umum->setDb($this->db);
        if ($role == DRIVER_ROLE && $trip_fungsi == TIPE_TRIP_ACCEPT) {
            if ($jenis_pembayaran == PEMBAYARAN_CASH) {
                $saldo = $saldo_user - ($harga * 0.2);
                $umum->updateSaldo($id_user, $saldo);
                return true;
            }
        }
        if ($role == DRIVER_ROLE && $trip_fungsi == TIPE_TRIP_CANCEL) {
            if ($jenis_pembayaran == PEMBAYARAN_CASH) {
                $saldo = $saldo_user + ($harga * 0.2);
                $umum->updateSaldo($id_user, $saldo);
                return true;
            }
        }
        if ($role == USER_ROLE && $trip_fungsi == TIPE_TRIP_ACCEPT && $jenis_pembayaran == PEMBAYARAN_SALDO) {
            $saldo = $saldo_user - $harga;
            $umum->updateSaldo($id_user, $saldo);
            return true;
        }
        if ($role == USER_ROLE && $trip_fungsi == TIPE_TRIP_CANCEL && $jenis_pembayaran == PEMBAYARAN_SALDO) {
            $saldo = $saldo_user + $harga;
            $umum->updateSaldo($id_user, $saldo);
            return true;
        }

    }

    public function inputTemporaryOrder() {
        $sql = "INSERT INTO temporary_order (id_customer,total_harga,alamat_jemput,latitude_jemput,longitude_jemput,alamat_destinasi,latitude_destinasi,longitude_destinasi,jarak,jenis_trip,status_trip,jenis_pembayaran)
            VALUES(:id_customer,:total_harga,:alamat_jemput,:latitude_jemput,:longitude_jemput,:alamat_destinasi,:latitude_destinasi,:longitude_destinasi,:jarak,:jenis_trip,:status_trip,:jenis_pembayaran)";
        $data = [
            ':id_customer' => $this->id_customer,
            ':total_harga' => $this->total_harga,
            ':alamat_jemput' => $this->alamat_jemput,
            ':latitude_jemput' => $this->latitude_jemput,
            ':longitude_jemput' => $this->longitude_jemput,
            ':alamat_destinasi' => $this->alamat_destinasi,
            ':latitude_destinasi' => $this->latitude_destinasi,
            ':longitude_destinasi' => $this->longitude_destinasi,
            ':jarak' => $this->jarak,
            ':jenis_trip' => $this->jenis_trip,
            ':status_trip' => $this->status_trip,
            ':jenis_pembayaran' => $this->jenis_pembayaran,
        ];
        $est = $this->getDb()->prepare($sql);
        if ($est->execute($data)) {
            return $this->getDb()->lastInsertId();
        }return false;

    }

    public function driverInputOrder($id_driver, $data_trip, $status_trip) {
        $sql = "INSERT INTO trip (id_trip,id_customer,id_driver,total_harga,alamat_jemput,latitude_jemput,longitude_jemput,alamat_destinasi,latitude_destinasi,longitude_destinasi,jarak,jenis_trip,status_trip,jenis_pembayaran)
        VALUES(:id_trip,:id_customer,:id_driver,:total_harga,:alamat_jemput,:latitude_jemput,:longitude_jemput,:alamat_destinasi,:latitude_destinasi,:longitude_destinasi,:jarak,:jenis_trip,:status_trip,:jenis_pembayaran)";
        $data = [
            ':id_trip' => $data_trip['id_trip'],
            ':id_customer' => $data_trip['id_customer'],
            ':id_driver' => $id_driver,
            ':total_harga' => $data_trip['total_harga'],
            ':alamat_jemput' => $data_trip['alamat_jemput'],
            ':latitude_jemput' => $data_trip['latitude_jemput'],
            ':longitude_jemput' => $data_trip['longitude_jemput'],
            ':alamat_destinasi' => $data_trip['alamat_destinasi'],
            ':latitude_destinasi' => $data_trip['latitude_destinasi'],
            ':longitude_destinasi' => $data_trip['longitude_destinasi'],
            ':jarak' => $data_trip['jarak'],
            ':jenis_trip' => $data_trip['jenis_trip'],
            ':status_trip' => $status_trip,
            ':jenis_pembayaran' => $data_trip['jenis_pembayaran'],
        ];
        $est = $this->getDb()->prepare($sql);
        if ($est->execute($data)) {
            return $data_trip;
        }return false;
    }

    public function cancelOrder($id_trip) {
        $data_order = $this->getTemporaryOrderDetail($id_trip);
        $data_trip = $this->getTripDetail($id_trip);

        $cancelOrder = new Umum();
        $cancelOrder->setDb($this->db);

        if (empty($data_order)) {
            $saldo_customer = $cancelOrder->getSaldoUser($data_trip['id_customer']);
            $saldo_driver = $cancelOrder->getSaldoUser($data_trip['id_driver']);
            $this->saldoTripUser($data_trip['id_customer'], TIPE_TRIP_CANCEL, $data_trip['jenis_pembayaran'], USER_ROLE, $data_trip['total_harga'], $saldo_customer['jumlah_saldo']);
            $this->saldoTripUser($data_trip['id_driver'], TIPE_TRIP_CANCEL, $data_trip['jenis_pembayaran'], DRIVER_ROLE, $data_trip['total_harga'], $saldo_driver['jumlah_saldo']);
            return $cancelOrder->updateStatusTrip($id_trip, STATUS_CANCEL);
        }
        $saldo_customer = $cancelOrder->getSaldoUser($data_order['id_customer']);
        $this->saldoTripUser($data_order['id_customer'], TIPE_TRIP_CANCEL, $data_order['jenis_pembayaran'], USER_ROLE, $data_order['total_harga'], $saldo_customer['jumlah_saldo']);
        $this->deleteTemporaryOrderDetail($id_trip);
        $cek = $this->driverInputOrder(ID_DRIVER_SILUMAN, $data_order, STATUS_CANCEL);
        if (empty($cek)) {
            return ['status' => 'Error', 'message' => 'Trip Tidak Ada Atau Telah Dibatalkan'];
        }
        return ['status' => 'Success', 'message' => 'Trip Telah Dibatalkan'];

    }

    public function deleteTemporaryOrderDetail($id) {
        $sql = "DELETE FROM temporary_order
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }return false;
    }

    public function getTripDetail($id) {
        $sql = "SELECT * FROM trip
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return $stmt;
        }return $stmt;
    }

    public function getTemporaryOrderDetail($id) {
        $sql = "SELECT * FROM temporary_order
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return $stmt;
        }return $stmt;
    }

    // BONUS
    public function bonusFinish($id_trip, $id_customer, $id_driver, $harga, $type) {
        $bayar = new Umum();
        $bayar->setDb($this->getDb());
        $harga = (double) $harga;
        $uang_driver = $bayar->getSaldoUser($id_driver);
        $point_driver = $bayar->getPointUser($id_driver);
        $point_customer = $bayar->getPointUser($id_customer);
        $point_perusahaan = $bayar->getPointUser(ID_PERUSAHAAN);

        $driver_uang = (double) $uang_driver['jumlah_saldo'];
        $driver_point = (double) $point_driver['jumlah_point'];
        $customer_point = (double) $point_customer['jumlah_point'];
        $perusahaan_point = (double) $point_perusahaan['jumlah_point'];

        $bersih = ($harga * 0.2);

        if ($type == PEMBAYARAN_SALDO) {
            $bersih_driver = ($harga * 0.8);
            $total_point_driver = $driver_point + $bersih_driver;
            $bayar->updatePoint($id_driver, $total_point_driver);
        }

        $point_driver = $bayar->getPointUser($id_driver);
        $driver_point = (double) $point_driver['jumlah_point'];

        $bonus = $bersih * 0.3;

        // $asli = $driver_uang - $bersih;
        // $bayar->updateSaldo($id_driver, $asli);

        // 6% UANG BERSIH PERUSAHAAN
        $bersih_perusahaan = $perusahaan_point + $bonus;
        $bayar->updatePoint(ID_PERUSAHAAN, $bersih_perusahaan);

        $bersih_trip = $bonus * 0.5;

        // 3%x2 BONUS TRIP DRIVER DAN CUSTOMER
        $total_point_driver = $driver_point + $bersih_trip;
        $bayar->updatePoint($id_driver, $total_point_driver);
        $this->insertBonusTrip($id_driver, $id_trip, $bersih_trip);

        $total_point_pengguna = $customer_point + $bersih_trip;
        $bayar->updatePoint($id_customer, $total_point_pengguna);
        $this->insertBonusTrip($id_customer, $id_trip, $bersih_trip);

        // 3%x2 BONUS LEVEL DRIVER DAN CUSTOMER
        $atasan_driver = $this->getAllReferalAtasan($id_driver);
        $bersih_level = $bonus * 0.5;
        $temp_hasil = $bersih_level;
        for ($i = 0; $i < count($atasan_driver); $i++) {
            $temp_hasil = $temp_hasil * 0.5;
            if ($i + 1 == count($atasan_driver)) {
                $temp_hasil = $temp_hasil * 2;
            }
            $point_atasan = $bayar->getPointUser($atasan_driver[$i]['id_user_atasan']);
            $atasan_point = (double) $point_atasan['jumlah_point'];
            $total_point = $atasan_point + $temp_hasil;
            $bayar->updatePoint($atasan_driver[$i]['id_user_atasan'], $total_point);
            $this->insertBonusLevel($atasan_driver[$i]['id_user_atasan'], $temp_hasil); // ganti
        }

        $atasan_customer = $this->getAllReferalAtasan($id_customer);
        $temp_hasil = $bersih_level;
        for ($i = 0; $i < count($atasan_customer); $i++) {
            $temp_hasil = $temp_hasil * 0.5;
            if ($i + 1 == count($atasan_customer)) {
                $temp_hasil = $temp_hasil * 2;
            }
            $point_atasan = $bayar->getPointUser($atasan_customer[$i]['id_user_atasan']);
            $atasan_point = (double) $point_atasan['jumlah_point'];
            $total_point = $atasan_point + $temp_hasil;
            $bayar->updatePoint($atasan_customer[$i]['id_user_atasan'], $total_point);
            $this->insertBonusLevel($atasan_customer[$i]['id_user_atasan'], $temp_hasil); // ganti
        }

        // 10% dari Bersih(harga*0.2) digunakan untuk bonus titik dan bonus sponsor
        $bersih_sponsor_titik = 0.1 * $bersih;

        // 1% BONUS SPONSOR DRIVER DAN CUSTOMER ATASAN
        $bersih_sponsor = 0.5 * $bersih_sponsor_titik;

        // SPONSOR ATASAN DRIVER
        $atasan_sponsor_driver = $this->getSponsorUp($id_driver);
        $point_atasan_sponsor_driver = $bayar->getPointUser($atasan_sponsor_driver['id_user_atasan']);
        $point_sponsor_driver = $bersih_sponsor + $point_atasan_sponsor_driver['jumlah_point'];
        $bayar->updatePoint($atasan_sponsor_driver['id_user_atasan'], $point_sponsor_driver);
        $this->insertBonusSponsor($atasan_sponsor_driver['id_user_atasan'], $bersih_sponsor); // ganti

        // SPONSOR ATASAN CUSTOMER
        $atasan_sponsor_customer = $this->getSponsorUp($id_customer);
        $point_atasan_sponsor_customer = $bayar->getPointUser($atasan_sponsor_customer['id_user_atasan']);
        $point_sponsor_customer = $bersih_sponsor + $point_atasan_sponsor_customer['jumlah_point'];
        $bayar->updatePoint($atasan_sponsor_customer['id_user_atasan'], $point_sponsor_customer);
        $this->insertBonusSponsor($atasan_sponsor_customer['id_user_atasan'], $bersih_sponsor); // ganti

        return true;
    }

    public function insertBonusTrip($id_user, $id_trip, $pendapatan) {
        $sql = "INSERT INTO bonus_trip(id_user, id_trip, pendapatan)
                VALUES('$id_user','$id_trip','$pendapatan')";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function insertBonusLevel($id_user, $pendapatan) { // ganti
        $sql = "INSERT INTO bonus_level(id_user, pendapatan)
                VALUES('$id_user','$pendapatan')";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function insertBonusSponsor($id_user, $pendapatan) { // ganti
        $sql = "INSERT INTO bonus_sponsor(id_user_atasan, pendapatan)
                VALUES('$id_user','$pendapatan')";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function getAllReferalAtasan($id) {
        $id_atasan[0]['id_user'] = '';
        $i = 0;
        $state = true;
        $temp_id = $id;
        while ($state) {
            $data = $this->getReferalUp($temp_id);
            $id_atasan[$i] = $data;
            $temp_id = $id_atasan[$i]['id_user_atasan'];
            if ($data['id_user_atasan'] == $id_atasan[$i]['id_user']) {
                $state = false;
                unset($id_atasan[$i]);
            }
            $i++;
        }
        return $id_atasan;
    }

    public function getAllReferalBawahan($id) {
        $getUser = new Umum();
        $getUser->setDb($this->db);
        $id_bawah = [];
        $i = 0;
        $state = true;
        $state2 = true;
        $data_user = $getUser->cekUser($id);
        $data = $this->getReferalDown($id);
        $id_bawah[0] = $data;

        $i = count($data);
        $data_lengkap = [
            'id_user' => $id,
            'nama' => decrypt($data_user['nama'], MOUGO_CRYPTO_KEY),
            'jumlah_mitra_referal' => $i,
            'mitra_referal' => [],
        ];
        if (empty($data)) {
            return ['status' => 'Error', 'message' => 'Tidak ditemukan user yang menggunakan referal dengan id user tersebut', 'data' => $data_lengkap];
        }
        $k = 0;
        for ($i = 0; $i < count($id_bawah[0]); $i++) {
            $id_bawah[0][$i]['nama'] = decrypt($id_bawah[0][$i]['nama'], MOUGO_CRYPTO_KEY);
        }
        // while ($state) {
        //     $c = 0;
        //     $state2 = true;
        //     while ($state2) {
        //         $temp = $this->getReferalDown($id_bawah[$k][$c]['id_user']);
        //         if ($id_bawah[$k][$c]['id_user'] == ID_PERUSAHAAN) {
        //             if (count($id_bawah[$k]) - 1 <= $c) {
        //                 $state2 = false;
        //             }
        //             $c++;
        //             continue;
        //         }

        //         if (empty($id_bawah[$k + 1]) && !empty($temp)) {
        //             $id_bawah[$k + 1] = $temp;
        //             $i = $i + count($temp);
        //         } else if (!empty($id_bawah[$k + 1]) && !empty($temp)) {
        //             array_push($id_bawah[$k + 1], $temp);
        //         }

        //         if (count($id_bawah[$k]) - 1 <= $c) {
        //             $state2 = false;
        //         }
        //         $c++;
        //     }
        //     if (count($id_bawah) - 1 <= $k) {
        //         $state = false;
        //     }
        //     $k++;
        // }
        $data_lengkap = [
            'id_user' => $id,
            'nama' => decrypt($data_user['nama'], MOUGO_CRYPTO_KEY),
            'jumlah_mitra_referal' => $i,
            'mitra_referal' => $id_bawah,
        ];

        return ['status' => 'Success', 'message' => 'User Referal Ditemukan', 'data' => $data_lengkap];

    }

    public function getReferalUp($id) {
        $sql = "SELECT * FROM kode_referal
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getReferalDown($id) {
        $sql = "SELECT kode_referal.*, kode_sponsor.kode_sponsor, user.nama FROM kode_referal
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = kode_referal.id_user
                INNER JOIN user ON user.id_user = kode_referal.id_user
                WHERE kode_referal.id_user_atasan = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getReferalDownSys($id) {
        $sql = "SELECT kode_referal.* FROM kode_referal
                WHERE kode_referal.id_user_atasan = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getReferalDownSysFull($id) {
        $sql = "SELECT kode_referal.*, user.role FROM kode_referal
                INNER JOIN user ON user.id_user = kode_referal.id_user
                WHERE kode_referal.id_user_atasan = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getAllSponsorAtasan($id) {
        $id_atasan = [];
        $i = 0;
        $state = true;
        $temp_id = $id;
        while ($state) {
            $data = $this->getSponsorUp($temp_id);
            if ($data['id_user_atasan'] == $id_atasan[$i]) {
                $state = false;
            }
            $id_atasan[$i] = $data;
            $temp_id = $id_atasan[$i];
            $i++;
        }
        return $id_atasan;
    }

    public function getAllSponsorBawahan($id) {

    }

    public function getSponsorUp($id) {
        $sql = "SELECT * FROM kode_sponsor
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getSponsorDown($id) {
        $sql = "SELECT * FROM kode_sponsor
                WHERE id_user_atasan = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

}
