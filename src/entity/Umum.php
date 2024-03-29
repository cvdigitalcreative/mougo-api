<?php
require_once dirname(__FILE__) . '/Trip.php';
require_once dirname(__FILE__) . '/Driver.php';
require_once dirname(__FILE__) . '/../randomGen.php';

class Umum {
    private $db;

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function getAllCabang() {
        $sql = "SELECT * FROM cabang";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        if (!empty($stmt)) {
            return ['status' => 'Success', 'data' => $stmt];
        }return ['status' => 'Error', 'message' => 'Cabang Tidak Ditemukan'];
    }

    public function getAllJenisKendaraan() {
        $sql = "SELECT * FROM kategori_kendaraan";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        if (!empty($stmt)) {
            return ['status' => 'Success', 'data' => $stmt];
        }return ['status' => 'Error', 'message' => 'Jenis Kendaraan Tidak Ditemukan'];
    }

    public function getAllJenisWithdraw($id_user) {
        $sql = "SELECT * FROM jenis_withdraw";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt['jenis_withdraw'] = $est->fetchAll();
        if (!empty($stmt)) {
            $point = $this->getPointUser($id_user);
            $stmt['point']['id_user'] = $point['id_user'];
            $stmt['point']['jumlah_point'] = (double) $point['jumlah_point'];
            return ['status' => 'Success', 'data' => $stmt];
        }return ['status' => 'Error', 'message' => 'Withdraw Tidak Ditemukan'];
    }

    public function getDistance($lat, $long, $latTemp, $longTemp) {
        $radLat1 = pi() * $lat / 180;
        $radTemp = pi() * $latTemp / 180;
        $tetha = $long - $longTemp;
        $radTetha = pi() * $tetha / 180;
        $distance = sin($radLat1) * sin($radTemp) + cos($radLat1) * cos($radTemp) * cos($radTetha);

        // if ($distance > 5) {
        //     $distance = 5;
        // }

        $distance = acos($distance);
        $distance = $distance * 180 / pi();
        $distance = $distance * 60 * 1.1515;
        $distance = $distance * 1.609344;
        return $distance;
    }

    public function getTemporaryTrip($lat, $long) {
        $sql = "SELECT *
                FROM temporary_order
                WHERE tanggal_transaksi >= now() - interval 1 hour AND tanggal_transaksi <= current_timestamp()";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();

        $numrow = $stmt->rowCount();

        $i = 0;
        while ($i < $numrow) {
            $latTemp = $result[$i]['latitude_jemput'];
            $longTemp = $result[$i]['longitude_jemput'];

            $result[$i]['distance'] = $this->getDistance($lat, $long, $latTemp, $longTemp);
            $i++;
        }

        uasort($result, function ($a, $b) {
            return strcmp($a['distance'], $b['distance']);
        });

        $temp = [];
        $j = 0;
        foreach ($result as $key => $value) {
            if ($value['distance'] < JARAK_MINIMAL) {
                $temp[$j]['id_trip'] = (int) $value['id_trip'];
                $temp[$j]['alamat_jemput'] = $value['alamat_jemput'];
                // $temp[$j]['latitude_jemput'] = $value['latitude_jemput'];
                // $temp[$j]['longitude_jemput'] = $value['longitude_jemput'];
                $temp[$j]['alamat_destinasi'] = $value['alamat_destinasi'];
                // $temp[$j]['latitude_destinasi'] = $value['latitude_destinasi'];
                // $temp[$j]['longitude_destinasi'] = $value['longitude_destinasi'];
                $temp[$j]['jenis_pembayaran'] = (int) $value['jenis_pembayaran'];
                $temp[$j]['jenis_trip'] = (int) $value['jenis_trip'];
                $temp[$j]['jarak'] = (double) $value['jarak'];
                $temp[$j]['total_harga'] = (double) $value['total_harga'];
                // $temp[$j]['distance'] = $value['distance'];
                ++$j;
            }
        }

        if (empty($temp)) {
            return ['status' => 'Error', 'message' => 'Customer Trip Tidak Ditemukan'];
        }
        return ['status' => 'Success', 'data' => $temp];
    }

    public function getCekTripStatusCustomer($id) {
        $sql = "SELECT * FROM temporary_order
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return ['status' => false, 'message' => 'Sedang Mencari Driver'];
        }
        $trip_cek = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $trip_driver = new Driver(null, null, null, null, null);
        $trip_cek->setDb($this->db);
        $trip_driver->setDb($this->db);
        $data_trip = $trip_cek->getTripDetail($id);
        if ($data_trip['id_driver'] == ID_DRIVER_SILUMAN) {
            return ['status' => false, 'message' => 'Trip Telah Dibatalkan'];
        }
        return ['status' => true, 'message' => 'Dapat Driver', 'data_trip' => $data_trip, 'data_driver' => $trip_driver->getProfileDriver($data_trip['id_driver'])];

    }

    public function updatePosition($id, $lat, $long) {
        $sql = "UPDATE position
            SET latitude = '$lat' , longitude = '$long'
            WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Posisi Sudah Diupdate'];
        }return ['status' => 'Error', 'message' => 'Posisi Gagal Diupdate'];

    }

    public function getPosition($id, $id_trip) {
        $sql = "SELECT * FROM position
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            $trip_cek_status = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
            $trip_cek_status->setDb($this->db);
            $data_trip = $trip_cek_status->getTripDetail($id_trip);
            switch ($data_trip['status_trip']) {
                case '2':
                    $pesan_status = STATUS_TRIP_MENJEMPUT;
                    break;
                case '3':
                    $pesan_status = STATUS_TRIP_MENGANTAR;
                    break;
                case '4':
                    $pesan_status = STATUS_TRIP_SELESAI;
                    break;
            }
            return ['status' => 'Success', 'data' => $stmt, 'message' => $pesan_status];
        }return ['status' => 'Error', 'message' => 'Posisi Tidak Ditemukan'];
    }

    public function updateStatusTrip($id, $status) {
        $trip_cek = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $trip_cek->setDb($this->db);
        $data_trip = $trip_cek->getTripDetail($id);
        if (empty($data_trip)) {
            return ['status' => 'Error', 'message' => 'Trip Tidak Ditemukan'];
        }
        $sql = "UPDATE trip
            SET status_trip = '$status'
            WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            if ($status == STATUS_MENGANTAR_KETUJUAN) {
                return ['status' => 'Success', 'message' => 'Mengantar Customer'];
            }
            if ($status == STATUS_SAMPAI_TUJUAN) {
                // return ['status' => 'Success', 'message' => 'Sampai Tujuan'];
                if ($trip_cek->bonusFinish($id, $data_trip['id_customer'], $data_trip['id_driver'], $data_trip['total_harga'], $data_trip['jenis_pembayaran'])) {
                    return ['status' => 'Success', 'message' => 'Sampai Tujuan'];
                }
            }
            if ($status == STATUS_CANCEL) {
                return ['status' => 'Success', 'message' => 'Trip Telah Dibatalkan'];
            }
        }return ['status' => 'Error', 'message' => 'Gagal Update Status'];
    }

    public function getHargaTotal($jarak) {
        $cek_harga = new Owner(null,null);
        $cek_harga->setDb($this->db);
        $minimal_harga = $cek_harga->getHargaAwalTrip();
        $perkilo_harga = $cek_harga->getHargaPerkiloTrip();
        if ($jarak <= JARAK_MINIMAL) {
            return ['status' => 'Success', 'harga' => $minimal_harga[0]['harga_awal_motor']];
        } else {
            $harga = $minimal_harga[0]['harga_awal_motor'];
            $jarak_pertama = JARAK_MINIMAL + 1;
            for ($i = $jarak_pertama; $i <= $jarak; $i++) {
                $harga = $harga + $perkilo_harga[0]['harga_perkilo_motor'];
            }
            return ['status' => 'Success', 'harga' => $harga];
        }return ['status' => 'Error', 'message' => 'Gagal Mendapatkan Harga'];
    }

    public function getHargaTotalCar($jarak) {
        $cek_harga = new Owner(null,null);
        $cek_harga->setDb($this->db);
        $minimal_harga = $cek_harga->getHargaAwalTrip();
        $perkilo_harga = $cek_harga->getHargaPerkiloTrip();
        if ($jarak <= JARAK_MINIMAL) {
            return ['status' => 'Success', 'harga' => $minimal_harga[0]['harga_awal_mobil']];
        } else {
            $harga = $minimal_harga[0]['harga_awal_mobil'];
            $jarak_pertama = JARAK_MINIMAL + 1;
            for ($i = $jarak_pertama; $i <= $jarak; $i++) {
                $harga = $harga + $perkilo_harga[0]['harga_perkilo_mobil'];
            }
            return ['status' => 'Success', 'harga' => $harga];
        }return ['status' => 'Error', 'message' => 'Gagal Mendapatkan Harga'];
    }

    public function getHargaCekTotal($jarak, $jenis) {
        if ($jenis == TRIP_MOU_BIKE || $jenis == TRIP_MOU_NOW_BIKE) {
            return $this->getHargaTotal($jarak);
        }
        if ($jenis == TRIP_MOU_CAR || $jenis == TRIP_MOU_NOW_CAR) {
            return $this->getHargaTotalCar($jarak);
        }
        return ['status' => 'Error', 'message' => 'Belum Tersedia'];
    }

    public function inputSaldo($jumlah_topup, $id_user) {
        if ($jumlah_topup < TOPUP_MINIMAL) {
            return ['status' => 'Error', 'message' => 'Pengisian Saldo Tidak Boleh Kurang Dari Rp10.000'];
        }
        while (true) {
            $id = randomNum() . randomLett();
            if (empty($this->cekTopup($id))) {
                break;
            }
        }
        $sql = "INSERT INTO top_up (id_topup,id_user,jumlah_topup,status_topup,admin)
                VALUES('$id','$id_user','$jumlah_topup',:status,:admin)";
        $data = [
            ':status' => STATUS_TOPUP_PENDING,
            ':admin' => ADMIN_SILUMAN_MOUGO,
        ];
        $est = $this->getDb()->prepare($sql);

        $cek_bank = new Owner(null,null);
        $cek_bank->setDb($this->db);
        $bank_mougo = $cek_bank->getBankMougo();
        $list_bank = [];
        for ($i=0; $i < count($bank_mougo); $i++) { 
            $list_bank[$i]['no_rek'] = $bank_mougo[$i]['norek_bank'];
            $list_bank[$i]['nama_rek'] = $bank_mougo[$i]['atas_nama_bank'];
            $list_bank[$i]['nama_bank'] = $bank_mougo[$i]['nama_bank'];
        }
        
        if ($est->execute($data)) {
            $data = [
                'id_topup' => $id,
                'jumlah_topup' => $jumlah_topup,
                'list_bank' => $list_bank,
            ];
            return ['status' => 'Success', 'message' => 'Berhasil, Silahkan Konfirmasi Top Up Anda', 'data' => $data];
        }return ['status' => 'Error', 'message' => 'Gagal Top Up'];
    }

    public function cekTopup($id) {
        $sql = "SELECT * FROM top_up
                WHERE id_topup LIKE '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getAllTopUp() {
        $sql = "SELECT * FROM top_up
            INNER JOIN bukti_pembayaran ON bukti_pembayaran.id_topup = top_up.id_topup
            WHERE top_up.status_topup = 1";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        if (!empty($temp)) {
            return ['status' => 'Success', 'data' => $temp];
        }return ['status' => 'Error', 'message' => 'Belum ada topup'];

    }

    public function insertBuktiPembayaran($id_topup, $path) {
        $sql = 'INSERT INTO bukti_pembayaran( id_topup , foto_transfer )
        VALUE( :id_topup, :foto_transfer)';
        $est = $this->db->prepare($sql);
        $data = [
            ":id_topup" => $id_topup,
            ":foto_transfer" => $path,
        ];

        if ($est->execute($data)) {
            return ['status' => 'Success', 'message' => 'Upload Bukti Pembayaran Berhasil, Silahkan Tunggu Konfirmasi Admin'];
        }return ['status' => 'Error', 'message' => 'Gagal Upload Bukti Pembayaran'];

    }

    public function insertAhliWaris($id_user, $nama_waris) {
        $sql = "INSERT INTO ahli_waris(nama_ahliwaris,id_user)
                VALUE ('$nama_waris','$id_user')";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function cekAhliWaris($id_user) {
        $sql = "SELECT * FROM ahli_waris
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function topupUpdate($id, $status, $email) {
        $data_topup = $this->getDetailTopup($id);
        if (empty($data_topup)) {
            return ['status' => 'Error', 'message' => 'ID Topup Tidak Ditemukan'];
        }
        $bukti_pembayaran = $this->getBuktiPembayaran($id);
        if (empty($this->getBuktiPembayaran($id))) {
            return ['status' => 'Error', 'message' => 'User Belum Memberikan Bukti Pembayaran'];
        }
        if (empty($email)) {
            return ['status' => 'Error', 'message' => 'Email Admin Harus Dimasukkan'];
        }
        switch ($status) {
            case TOPUP_ACCEPT:
                $detail_topup = $data_topup;
                if (empty($detail_topup)) {
                    return ['status' => 'Error', 'message' => 'Topup Tidak Ditemukan'];
                }
                if ($detail_topup['status_topup'] == 2) {
                    return ['status' => 'Error', 'message' => 'Gagal, Topup Ini Telah Diterima Oleh Admin'];
                }
                $detail_saldo = $this->getSaldoUser($detail_topup['id_user']);
                $detail_saldo['jumlah_saldo'] = $detail_saldo['jumlah_saldo'] + $detail_topup['jumlah_topup'];
                if (!$this->updateSaldo($detail_topup['id_user'], $detail_saldo['jumlah_saldo'])) {
                    return ['status' => 'Error', 'message' => 'Gagal Tambah Saldo'];
                }
                if (!$this->updateTopup($id, STATUS_TOPUP_ACCEPT, $email)) {
                    return ['status' => 'Error', 'message' => 'Gagal Tambah Saldo'];
                }
                return ['status' => 'Success', 'message' => 'Saldo User Berhasil Diterima'];
            case TOPUP_REJECT:
                if ($data_topup['status_topup'] == 2) {
                    return ['status' => 'Error', 'message' => 'Gagal, Topup User Telah Berhasil Diterima Oleh Admin'];
                }
                if (unlink(PATH_PUBLIC.$bukti_pembayaran['foto_transfer'])) {
                    if (!$this->deleteBuktiPembayaran($id)) {
                        return ['status' => 'Error', 'message' => 'Gagal Menolak Topup'];
                    }
                }
                if (!$this->updateTopup($id, STATUS_TOPUP_REJECT, $email)) {
                    return ['status' => 'Error', 'message' => 'Gagal Reject Topup'];
                }
                return ['status' => 'Success', 'message' => 'Berhasil Menolak Topup'];

        }
    }

    public function getBuktiPembayaran($id) {
        $sql = "SELECT * FROM bukti_pembayaran
                WHERE id_topup = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function deleteBuktiPembayaran($id_topup) {
        $sql = "DELETE FROM bukti_pembayaran
                WHERE id_topup = '$id_topup'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }
        return false;
    }

    public function getDetailTopup($id) {
        $sql = "SELECT * FROM top_up
                WHERE id_topup = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function updateSaldo($id_user, $saldo) {
        $sql = "UPDATE saldo
                SET jumlah_saldo = '$saldo'
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }return false;
    }

    public function getSaldoUser($id_user) {
        $sql = "SELECT * FROM saldo
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function getPointUser($id_user) {
        $sql = "SELECT * FROM point
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function updatePoint($id_user, $point) {
        $sql = "UPDATE point
                SET jumlah_point = '$point'
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function updateTopup($id, $status, $email) {
        $sql = "UPDATE top_up
                SET status_topup = '$status', admin = '$email' , tanggal_topup = tanggal_topup
                WHERE id_topup = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }return false;
    }

    public function getDriverAdmin($id_user) {
        $sql = "SELECT * FROM driver
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function getAllDriver() {
        $sql = "SELECT * FROM user
                INNER JOIN driver ON driver.id_user = user.id_user
                INNER JOIN cabang ON cabang.id = driver.cabang
                INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
                INNER JOIN detail_user ON detail_user.id_user = driver.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                WHERE user.role = 2
                LIMIT 100";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getAllDriverWeb() {
        $sql = "SELECT * FROM user
                INNER JOIN driver ON driver.id_user = user.id_user
                INNER JOIN cabang ON cabang.id = driver.cabang
                INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
                INNER JOIN detail_user ON detail_user.id_user = driver.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN position ON position.id_user = user.id_user
                WHERE user.role = 2
                AND driver.status_online = 1
                LIMIT 100";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getAllCustomer() {
        $sql = "SELECT * FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user
                WHERE user.role = 1
                LIMIT 100";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getAllUser() {
        $sql = "SELECT * FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN bank ON bank.code = detail_user.bank
                INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
                INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function deleteDriverFoto($id_user) {
        $sql = "UPDATE driver
                SET foto_skck = '-', foto_sim = '-', foto_stnk = '-',foto_diri = '-'
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function rejectDriver($id_user,$email_admin) {
        $data = $this->getDriverAdmin($id_user);
        $user = $this->cekFotoCustomer($id_user);
        if ($data['status_akun_aktif'] == STATUS_DRIVER_AKTIF) {
            return ['status' => 'Error', 'message' => 'Gagal, Reject Driver / Driver Telah Diterima Oleh Admin'];
        }
        if ($data['foto_skck'] == '-' && $data['foto_stnk'] == '-' && $data['foto_sim'] == '-' && $data['foto_diri'] == '-') {
            return ['status' => 'Error', 'message' => 'Gagal, Reject Driver / Driver Telah Direject Oleh Admin'];
        }

        unlink(PATH_PUBLIC.$data['foto_skck']);
        unlink(PATH_PUBLIC.$data['foto_stnk']);
        unlink(PATH_PUBLIC.$data['foto_sim']);
        unlink(PATH_PUBLIC.$data['foto_diri']);
        unlink(PATH_PUBLIC.$user['foto_ktp']);
        unlink(PATH_PUBLIC.$user['foto_kk']);

        if ($this->deleteUserFoto($id_user)) {
            if(!empty($email_admin)){
                $cek = $this->getAdminVerifiDriver($id_user);
                if (empty($cek)) {
                    $this->insertAdminVerifiDriver($id_user, $email_admin);  
                }else{
                    $this->editAdminVerifiDriver($id_user, $email_admin);
                }
            }
            if (!$this->deleteDriverFoto($id_user)) {
                return ['status' => 'Error', 'message' => 'Gagal Reject Driver'];
            }
        }
        
        return ['status' => 'Success', 'message' => 'Berhasil Reject Driver'];
    }

    public function editDriverStatus($id_driver, $status) {
        $sql = "UPDATE driver
                SET status_akun_aktif = '$status'
                WHERE id_user = '$id_driver'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }
    
    public function editAdminVerifiDriver($id_driver, $email) {
        $sql = "UPDATE verifikasi_driver
                SET email_admin = '$email'
                WHERE id_user = '$id_driver'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }
     
    public function getAdminVerifiDriver($id_driver) {
        $sql = "SELECT * FROM verifikasi_driver
                WHERE id_user = '$id_driver'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function insertAdminVerifiDriver($id_driver, $email) {
        $sql = "INSERT INTO verifikasi_driver (id_user, email_admin)
                VALUES ('$id_driver', '$email')";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }
    
    public function updateDriverStatus($id_driver, $status, $email_admin) {
        $data = $this->cekUser($id_driver);
        if (empty($data)) {   
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        if ($this->editDriverStatus($id_driver, $status) ) {
            if(!empty($email_admin)){
                $cek = $this->getAdminVerifiDriver($id_driver);
                if (empty($cek)) {
                    $this->insertAdminVerifiDriver($id_driver, $email_admin);  
                }else{
                    $this->editAdminVerifiDriver($id_driver, $email_admin);
                }
            }
            return ['status' => 'Success', 'message' => 'Berhasil Mengaktifkan Driver'];
        }return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Driver'];
    }

    public function resetFoto($id) {
        $data = $this->cekFotoCustomer($id);
        if (file_exists($data['foto_kk'])) {
            unlink(PATH_PUBLIC.$data['foto_kk']);
        }
        if (file_exists($data['foto_ktp'])) {
            unlink(PATH_PUBLIC.$data['foto_ktp']);
        }
        if ($this->deleteUserFoto($id)) {
            return ['status' => 'Success', 'message' => 'Berhasil Mereset Foto KK dan KTP'];
        }return ['status' => 'Error', 'message' => 'Gagal Mereset Foto KK dan KTP'];
    }

    public function deleteUserFoto($id_user) {
        $sql = "UPDATE detail_user
                SET foto_ktp = '-', foto_kk = '-'
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function lupaPassword($emailTelpon) {
        $user = $this->getUser($emailTelpon);
        if (empty($user)) {
            return $user;
        }
        $lupa_password = $this->getUserLupaPassword($user['id_user']);
        if (!empty($lupa_password)) {
            $this->deleteUserLupaPassword($lupa_password['token']);
        }
        $this->insertLupaPassword($user['id_user'], sha1(rand()));
        $lupa_password = $this->getUserLupaPassword($user['id_user']);
        $user['token'] = $lupa_password['token'];
        return $user;
    }

    public function deleteUserLupaPassword($token) {
        $sql = "DELETE FROM lupa_password
                WHERE token = '$token'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }
        return false;
    }

    public function getUserLupaPassword($id) {
        $sql = "SELECT * FROM lupa_password
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function getUserLupaPasswordToken($id) {
        $sql = "SELECT id_user, DAY(expired_date) AS day FROM lupa_password
                WHERE token = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function insertLupaPassword($id, $token) {
        $sql = "INSERT INTO lupa_password(id_user, token, expired_date, status_token )
                VALUE ('$id', '$token', CURRENT_TIMESTAMP, :status )";
        $est = $this->getDb()->prepare($sql);
        $data = [
            ':status' => STATUS_TOKEN_ACTIVE,
        ];
        if ($est->execute($data)) {
            return true;
        }return false;
    }

    public function getUser($emailTelpon) {
        $sql = "SELECT * FROM user
                WHERE user.email = '$emailTelpon' OR user.no_telpon = '$emailTelpon'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function updatePassword($id, $password) {
        $sql = "UPDATE user
                SET password = '$password'
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }return false;
    }

    public function getBank() {
        $sql = "SELECT * FROM bank";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function uploadFileFoto($id_user, $uploadedFile, $role, $directory, $path_name) {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
                return ['status' => 'Error', 'message' => 'Gambar Yang Dipilih Harus JPG atau PNG'];
            }
            $filename = $id_user . "." . $extension;
            if (file_exists("$directory/$filename")) {
                unlink(PATH_PUBLIC."$directory/$filename");
            }

            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
            $path_name = $path_name . $filename;
            if ($this->updateFoto($id_user, $path_name, $role)) {
                return ['status' => 'Success', 'message' => 'Foto Berhasil Diupload'];
            }
        }
    }

    public function updateFoto($id, $path, $role) {
        if ($role == FOTO_KTP || $role == FOTO_KK) {
            $sql = "UPDATE detail_user ";
        }
        if ($role == FOTO_SIM || $role == FOTO_DIRI || $role == FOTO_SKCK || $role == FOTO_STNK) {
            $sql = "UPDATE driver ";
        }
        if ($role == FOTO_KTP) {
            $sql = $sql . "SET foto_ktp = '$path'
            WHERE id_user = '$id'";
        }
        if ($role == FOTO_KK) {
            $sql = $sql . "SET foto_kk = '$path'
            WHERE id_user = '$id'";
        }
        if ($role == FOTO_SIM) {
            $sql = $sql . "SET foto_sim = '$path'
            WHERE id_user = '$id'";
        }
        if ($role == FOTO_DIRI) {
            $sql = $sql . "SET foto_diri = '$path'
            WHERE id_user = '$id'";
        }
        if ($role == FOTO_SKCK) {
            $sql = $sql . "SET foto_skck = '$path'
            WHERE id_user = '$id'";
        }
        if ($role == FOTO_STNK) {
            $sql = $sql . "SET foto_stnk = '$path'
            WHERE id_user = '$id'";
        }
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function cekFotoCustomer($id) {
        $sql = "SELECT * FROM detail_user
                WHERE id_user ='$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function cekFotoDriver($id) {
        $sql = "SELECT * FROM driver
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function deleteWaris($id_user, $id) {
        $sql = "DELETE FROM ahli_waris
                WHERE id_user = '$id_user' AND id = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Berhasil Menghapus Ahli Waris'];
        }
        return ['status' => 'Error', 'message' => 'Gagal Menghapus Ahli Waris'];
    }

    public function cekUser($id) {
        $sql = "SELECT * FROM user
                WHERE id_user ='$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function getReferalChild($id) {
        $sql = "SELECT * FROM kode_referal
                INNER JOIN user ON user.id_user = kode_referal.id_user
                WHERE kode_referal.id_user_atasan ='$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTotalReferalChild($id) {
        $total = 0;
        $level1 = $this->getReferalChild($id);
        for ($i = 0; $i < count($level1); $i++) {
            $total++;
            $level2 = $this->getReferalChild($level1[$i]['id_user']);
            for ($j = 0; $j < count($level2); $j++) {
                $total++;
                $level3 = $this->getReferalChild($level2[$j]['id_user']);
                for ($c = 0; $c < count($level3); $c++) {
                    $total++;
                }
            }
        }
        return $total;
    }

    public function inputBonusTransfer($id_user, $pendapatan) {
        $sql = "INSERT INTO bonus_transfer(id_user, pendapatan)
                VALUES('$id_user', '$pendapatan')";
        $est = $this->db->prepare($sql);
        return $est->execute();
    }

    public function bonusTransferLevel($id_user, $pendapatan) {
        $getAtasan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getAtasan->setDb($this->db);
        $temp_hasil = $pendapatan;
        $atasanUser = $getAtasan->getAllReferalAtasan($id_user);
        for ($i = 0; $i < count($atasanUser); $i++) {
            $temp_hasil = $temp_hasil * 0.5;
            if ($i + 1 == count($atasanUser)) {
                $temp_hasil = $temp_hasil * 2;
            }
            $point_atasan = $this->getPointUser($atasanUser[$i]['id_user_atasan']);
            $atasan_point = (double) $point_atasan['jumlah_point'];
            $total_point = $atasan_point + $temp_hasil;
            $this->updatePoint($atasanUser[$i]['id_user_atasan'], $total_point);
            $getAtasan->insertBonusLevel($atasanUser[$i]['id_user_atasan'], $temp_hasil); // ganti
        }
    }

    public function bonusTransferSponsor($id_user, $pendapatan) {
        $getAtasan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getAtasan->setDb($this->db);
        $atasan_sponsor = $getAtasan->getSponsorUp($id_user);
        $point_atasan_sponsor = $this->getPointUser($atasan_sponsor['id_user_atasan']);
        $point_sponsor = $pendapatan + $point_atasan_sponsor['jumlah_point'];
        $this->updatePoint($atasan_sponsor['id_user_atasan'], $point_sponsor);
        $getAtasan->insertBonusSponsor($atasan_sponsor['id_user_atasan'], $pendapatan); // ganti
    }

    public function insertTransfer($id_user, $id_user_penerima, $jumlah) {
        $saldo_user = $this->getSaldoUser($id_user);
        $saldo_penerima = $this->getSaldoUser($id_user_penerima);
        $saldo_perusahaan = $this->getSaldoUser(ID_PERUSAHAAN);

        $saldo_user = $saldo_user['jumlah_saldo'] - ($jumlah + TRANSFER_CHARGE);
        $saldo_penerima = $saldo_penerima['jumlah_saldo'] + $jumlah;
        $saldo_perusahaan = $saldo_perusahaan['jumlah_saldo'] + (0.5 * TRANSFER_CHARGE);

        $point_user = $this->getPointUser($id_user);
        $point_user = $point_user['jumlah_point'] + (0.15 * TRANSFER_CHARGE);

        $this->inputBonusTransfer($id_user, (0.15 * TRANSFER_CHARGE));
        $this->bonusTransferLevel($id_user, (0.15 * TRANSFER_CHARGE));
        $this->bonusTransferSponsor($id_user, (0.1 * TRANSFER_CHARGE));

        if (!$this->updateSaldo($id_user, $saldo_user) || !$this->updateSaldo($id_user_penerima, $saldo_penerima) || !$this->updateSaldo(ID_PERUSAHAAN, $saldo_perusahaan) || !$this->updatePoint($id_user, $point_user)) {
            return ['status' => 'Error', 'message' => 'Gagal Update Saldo'];
        }

        $sql = 'INSERT INTO transfer( sender_user_id , receipent_user_id , total_transfer )
        VALUE( :sender_user_id, :receipent_user_id , :total_transfer)';
        $est = $this->db->prepare($sql);
        $data = [
            ":sender_user_id" => $id_user,
            ":receipent_user_id" => $id_user_penerima,
            ":total_transfer" => $jumlah,
        ];

        if ($est->execute($data)) {
            return ['status' => 'Success', 'message' => 'Transfer Berhasil Diproses'];
        }return ['status' => 'Error', 'message' => 'Terjadi Masalah Ketika Mengupdate Saldo'];

    }

    public function withdrawPoint($id_user, $jumlah, $jenis) {

        if (empty($this->cekUser($id_user))) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        if (empty($jumlah) || $jumlah <= JUMLAH_WITHDRAW_TERKECIL) {
            return ['status' => 'Error', 'message' => 'Input Nominal Tidak Boleh Kosong'];
        }

        $point_user = $this->getPointUser($id_user);
        if ($jumlah < JUMLAH_WITHDRAW_MINIMAL && $jenis == JENIS_WITHDRAW_REKENING) {
            return ['status' => 'Error', 'message' => 'Untuk Withdraw Melalui Rekening Minimal Withdraw 100.000 Rupiah'];
        }
        if ($point_user['jumlah_point'] < $jumlah) {
            return ['status' => 'Error', 'message' => 'Point Tidak Mencukupi Untuk Melakukan Withdraw'];
        }

        $point_user = $point_user['jumlah_point'] - $jumlah;

        if (!$this->updatePoint($id_user, $point_user)) {
            return ['status' => 'Error', 'message' => 'Gagal Update Point'];
        }

        $draw_status = STATUS_WITHDRAW_PENDING;
        $rekening = ", Untuk Withdraw Rekening Akan Dikirim Ke Nomor Rekening Yang Tersimpan";
        if ($jenis == JENIS_WITHDRAW_SALDO) {
            $draw_status = STATUS_WITHDRAW_SUCCESS;
            $saldo_user = $this->getSaldoUser($id_user);
            $saldo_user = $saldo_user['jumlah_saldo'] + $jumlah;
            $rekening = "";
            if (!$this->updateSaldo($id_user, $saldo_user)) {
                return ['status' => 'Error', 'message' => 'Gagal Update Saldo'];
            }
        }

        $sql = 'INSERT INTO withdraw( id_user , jumlah , jenis_withdraw, status_withdraw, admin )
        VALUE( :id_user , :jumlah , :jenis_withdraw, :status_withdraw, :admin)';
        $est = $this->db->prepare($sql);
        $data = [
            ":id_user" => $id_user,
            ":jumlah" => $jumlah,
            ":jenis_withdraw" => $jenis,
            ":status_withdraw" => $draw_status,
            ":admin" => '-',
        ];

        if ($est->execute($data)) {
            return ['status' => 'Success', 'message' => 'Withdraw Berhasil Diproses' . $rekening];
        }return ['status' => 'Error', 'message' => 'Terjadi Masalah Ketika Melakukan Withdraw'];

    }

    public function getHistoryWithdraw($id) {
        $sql = "SELECT withdraw.*,status_withdraw.status_withdraw,jenis_withdraw.jenis_withdraw FROM withdraw
                INNER JOIN status_withdraw ON status_withdraw.id = withdraw.status_withdraw
                INNER JOIN jenis_withdraw ON jenis_withdraw.id = withdraw.jenis_withdraw
                WHERE id_user ='$id'
                ORDER BY withdraw.tanggal_withdraw DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTransferHistory($id) {
        $sql = "SELECT user.nama,user.email,user.no_telpon,transfer.total_transfer,transfer.tanggal_transfer FROM transfer
                INNER JOIN user ON user.id_user = transfer.receipent_user_id
                WHERE transfer.sender_user_id = '$id'
                ORDER BY transfer.tanggal_transfer DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTransferHistoryReceipt($id) {
        $sql = "SELECT user.nama,user.email,user.no_telpon,transfer.total_transfer,transfer.tanggal_transfer FROM transfer
                INNER JOIN user ON user.id_user = transfer.sender_user_id
                WHERE transfer.receipent_user_id = '$id'
                ORDER BY transfer.tanggal_transfer DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTransferHistoryUser($id) {
        if (empty($this->cekUser($id))) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        $data = $this->getTransferHistory($id);
        $data2 = $this->getTransferHistoryReceipt($id);
        if (empty($data) && empty($data2)) {
            return ['status' => 'Success', 'data' => []];
        }

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['message'] = PESAN_TRANSFER;
            $data[$i]['total_transfer'] = (double) $data[$i]['total_transfer'];
        }
        for ($i = 0; $i < count($data2); $i++) {
            $data2[$i]['message'] = PESAN_TRANSFER_DITERIMA;
            $data2[$i]['total_transfer'] = (double) $data2[$i]['total_transfer'];
        }

        $data_lengkap = array_merge($data,$data2);

        for ($i = 0; $i < count($data_lengkap); $i++) {
            $swapped = false;

            for ($j = 0; $j < count($data_lengkap) - $i - 1; $j++) {

                if ($data_lengkap[$j]['tanggal_transfer'] < $data_lengkap[$j + 1]['tanggal_transfer']) {
                    $t = $data_lengkap[$j];
                    $data_lengkap[$j] = $data_lengkap[$j + 1];
                    $data_lengkap[$j + 1] = $t;
                    $swapped = true;
                }
            }

            if ($swapped == false) {
                break;
            }

        }

        return ['status' => 'Success', 'data' => $data_lengkap];
    }

    public function getTopupHistory($id) {
        $sql = "SELECT top_up.id_topup,top_up.jumlah_topup,status_topup.status,top_up.tanggal_topup FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN status_topup ON status_topup.id = top_up.status_topup
                WHERE top_up.id_user = '$id'
                ORDER BY top_up.tanggal_topup DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTopupHistoryUser($id) {
        if (empty($this->cekUser($id))) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        $data = $this->getTopupHistory($id);
        if (empty($data)) {
            return ['status' => 'Success', 'data' => []];
        }
        $cek_bank = new Owner(null,null);
        $cek_bank->setDb($this->db);
        $bank_mougo = $cek_bank->getBankMougo();
        $list_bank = [];
        for ($i=0; $i < count($bank_mougo); $i++) { 
            $list_bank[$i]['no_rek'] = $bank_mougo[$i]['norek_bank'];
            $list_bank[$i]['nama_rek'] = $bank_mougo[$i]['atas_nama_bank'];
            $list_bank[$i]['nama_bank'] = $bank_mougo[$i]['nama_bank'];
        }
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['jumlah_topup'] = (double) $data[$i]['jumlah_topup'];
            $data[$i]['list_bank'] = $list_bank;
            if ($data[$i]['status'] == TOPUP_ACCEPT_NAME) {
                $data[$i]['pesan_topup'] = PESAN_TOPUP_ACCEPT;
            }
            if ($data[$i]['status'] == TOPUP_REJECT_NAME) {
                $data[$i]['pesan_topup'] = PESAN_TOPUP_REJECT;
            }
            if ($data[$i]['status'] == TOPUP_PENDING_NAME) {
                $data[$i]['pesan_topup'] = PESAN_TOPUP_PENDING;
            }
        }
        return ['status' => 'Success', 'data' => $data];
    }

    public function getBantuanUser($id) {
        $sql = "SELECT * FROM bantuan
                WHERE id_user = '$id'
                ORDER BY tanggal_bantuan DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getBantuanDefault() {
        $data = $this->getBantuanUser(ID_DRIVER_SILUMAN);
        if (empty($data)) {
            return ['status' => 'Success', 'data' => []];
        }
        return ['status' => 'Success', 'data' => $data];
    }

    public function getBantuanFromUser($id) {
        if (empty($this->cekUser($id))) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        $data = $this->getBantuanUser($id);
        if (empty($data)) {
            return ['status' => 'Success', 'data' => []];
        }
        return ['status' => 'Success', 'data' => $data];
    }

    public function insertBantuanUser($id, $pesan, $jawaban) {
        if (empty($this->cekUser($id))) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        if (empty($pesan)) {
            return ['status' => 'Error', 'message' => 'Keterangan Bantuan Tidak Boleh Kosong'];
        }
        if (empty($jawaban)) {
            return ['status' => 'Error', 'message' => 'Keterangan Jawaban Bantuan Tidak Boleh Kosong'];
        }
        $sql = "INSERT INTO bantuan(id_user, pertanyaan, jawaban, tanggal_bantuan )
                VALUE ('$id', '$pesan', '$jawaban', CURRENT_TIMESTAMP )";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => "Pertanyaan Anda Berhasil Dikirim"];
        }
        return ['status' => 'Error', 'message' => "Gagal Mengirim Pertanyaan"];
    }

    public function cekUserBantuan($id) {
        $sql = "SELECT * FROM bantuan
                WHERE id_bantuan ='$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function jawabBantuanAdmin($id, $jawaban, $email) {
        if (empty($this->cekUserBantuan($id))) {
            return ['status' => 'Error', 'message' => 'Bantuan tidak ditemukan'];
        }
        if (empty($jawaban)) {
            return ['status' => 'Error', 'message' => 'Keterangan Jawaban Bantuan Tidak Boleh Kosong'];
        }
        $sql = "UPDATE bantuan
            SET jawaban = '$jawaban', tanggal_bantuan = tanggal_bantuan
            WHERE id_bantuan = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            $sql = "INSERT INTO admin_menjawab (id_bantuan, email_admin)
                    VALUES('$id', '$email')";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
            return ['status' => 'Success', 'message' => 'Berhasil Menjawab Pertanyaan User'];
        }return ['status' => 'Error', 'message' => 'Gagal Mengupdate Jawaban Bantuan'];

    }

    public function cekWithdraw($id) {
        $sql = "SELECT * FROM withdraw
                WHERE id ='$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        return $stmt;
    }

    public function editWithdraw($id, $status, $email) {
        $sql = "UPDATE withdraw
                SET status_withdraw = '$status', tanggal_withdraw = tanggal_withdraw, admin = '$email'
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return;
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Berhasil Mengaktifkan Driver'];
        }return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Driver'];
    }

    public function adminKonfirmasiWithdraw($id, $status, $email) {
        $data = $this->cekWithdraw($id);
        if (empty($data)) {
            return ['status' => 'Error', 'message' => 'Withdraw tidak ditemukan'];
        }
        if ($data['status_withdraw'] == STATUS_WITHDRAW_SUCCESS) {
            return ['status' => 'Error', 'message' => 'Withdraw Tersebut Telah Diterima Sebelumnya'];
        }
        if ($data['status_withdraw'] == STATUS_WITHDRAW_REJECT) {
            return ['status' => 'Error', 'message' => 'Withdraw Tersebut Telah Ditolak Oleh Admin'];
        }
        if ($this->editWithdraw($id, $status, $email)) {
            return ['status' => 'Error', 'message' => 'Gagal Update Withdraw'];
        }
        if ($status == STATUS_WITHDRAW_REJECT) {
            $point_user = $this->getPointUser($data['id_user']);
            $point = $point_user['jumlah_point'] + $data['jumlah'];
            if (!$this->updatePoint($data['id_user'], $point)) {
                return ['status' => 'Error', 'message' => 'Gagal Update Point User'];
            }
            return ['status' => 'Success', 'message' => 'Berhasil Menolak Withdraw User'];
        }
        return ['status' => 'Success', 'message' => 'Berhasil Menerima Withdraw User'];

    }

    public function getTripHistoryCustomer($id) {
        $sql = "SELECT jenis_trip.jenis_trip,jenis_pembayaran.jenis_pembayaran,trip.alamat_destinasi,trip.total_harga,trip.tanggal_transaksi FROM trip
                INNER JOIN jenis_pembayaran ON jenis_pembayaran.id = trip.jenis_pembayaran
                INNER JOIN jenis_trip ON jenis_trip.id = trip.jenis_trip
                WHERE trip.id_customer = '$id'
                ORDER BY trip.tanggal_transaksi DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTripHistoryDriver($id) {
        $sql = "SELECT jenis_trip.jenis_trip,jenis_pembayaran.jenis_pembayaran,trip.alamat_destinasi,trip.total_harga,trip.tanggal_transaksi FROM trip
                INNER JOIN jenis_pembayaran ON jenis_pembayaran.id = trip.jenis_pembayaran
                INNER JOIN jenis_trip ON jenis_trip.id = trip.jenis_trip
                WHERE trip.id_driver = '$id'
                ORDER BY trip.tanggal_transaksi DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getTripHistoryUser($id) {
        $user = $this->cekUser($id);
        if (empty($user)) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        if ($user['role'] == USER_ROLE) {
            $data = $this->getTripHistoryCustomer($id);
        } else {
            $data = $this->getTripHistoryDriver($id);
        }
        if (empty($data)) {
            return ['status' => 'Success', 'data' => []];
        }
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['total_harga'] = (double) $data[$i]['total_harga'];
        }
        return ['status' => 'Success', 'data' => $data];
    }

    public function getAllHistoryUser($id) {
        $user = $this->cekUser($id);
        if (empty($user)) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }

        $data2 = $this->getTopupHistory($id);
        $data3 = $this->getHistoryWithdraw($id);
        $data4 = $this->getTransferHistory($id);
        $data5 = $this->getTransferHistoryReceipt($id);
        
        $cek_bank = new Owner(null,null);
        $cek_bank->setDb($this->db);
        $bank_mougo = $cek_bank->getBankMougo();

        if (!empty($data2)) {
            for ($i = 0; $i < count($data2); $i++) {
                $data2[$i]['jumlah_topup'] = (double) $data2[$i]['jumlah_topup'];
                $data2[$i]['no_rek'] = $bank_mougo[0]['norek_bank'];
                $data2[$i]['nama_rek'] = $bank_mougo[0]['atas_nama_bank'];
                $data2[$i]['nama_bank'] = $bank_mougo[0]['nama_bank'];
                if ($data2[$i]['status'] == TOPUP_ACCEPT_NAME) {
                    $data2[$i]['message'] = PESAN_TOPUP_ACCEPT;
                }
                if ($data2[$i]['status'] == TOPUP_REJECT_NAME) {
                    $data2[$i]['message'] = PESAN_TOPUP_REJECT;
                }
                if ($data2[$i]['status'] == TOPUP_PENDING_NAME) {
                    $data2[$i]['message'] = PESAN_TOPUP_PENDING;
                }
                $data2[$i]['status_topup'] = $data2[$i]['status'];
                $data2[$i]['tanggal'] = $data2[$i]['tanggal_topup'];
                $data2[$i]['type'] = TYPE_TOPUP;
                unset($data2[$i]['status']);
                unset($data2[$i]['tanggal_topup']);
            }
        }

        $data = [];

        if (!empty($data3)) {
            for ($i = 0; $i < count($data3); $i++) {
                $data[$i]['id'] = (int) $data3[$i]['id'];
                $data[$i]['id_user'] = $data3[$i]['id_user'];
                $data[$i]['jumlah'] = (double) $data3[$i]['jumlah'];
                $data[$i]['jenis_withdraw'] = $data3[$i]['jenis_withdraw'];
                $data[$i]['status_withdraw'] = $data3[$i]['status_withdraw'];
                if ($data3[$i]['jenis_withdraw'] == JENIS_WITHDRAW_SALDO) {
                    $data[$i]['message'] = PESAN_WITHDRAW_SALDO;
                } else {
                    $data[$i]['message'] = PESAN_WITHDRAW_REKENING;
                }
                $data[$i]['tanggal'] = $data3[$i]['tanggal_withdraw'];
                $data[$i]['type'] = TYPE_WITHDRAW;
            }
        }

        if (!empty($data4)) {
            for ($i = 0; $i < count($data4); $i++) {
                $data4[$i]['total_transfer'] = (double) $data4[$i]['total_transfer'];
                $data4[$i]['message'] = PESAN_TRANSFER;
                $data4[$i]['tanggal'] = $data4[$i]['tanggal_transfer'];
                $data4[$i]['type'] = TYPE_TRANSFER;
                unset($data4[$i]['tanggal_transfer']);
            }
        }

        if (!empty($data5)) {
            for ($i = 0; $i < count($data5); $i++) {
                $data5[$i]['total_transfer'] = (double) $data5[$i]['total_transfer'];
                $data5[$i]['message'] = PESAN_TRANSFER_DITERIMA;
                $data5[$i]['tanggal'] = $data5[$i]['tanggal_transfer'];
                $data5[$i]['type'] = TYPE_TRANSFER;
                unset($data5[$i]['tanggal_transfer']);
            }
        }

        for ($i = 0; $i < count($data2); $i++) {
            $data[count($data)] = $data2[$i];
        }

        for ($i = 0; $i < count($data4); $i++) {
            $data[count($data)] = $data4[$i];
        }

        for ($i = 0; $i < count($data5); $i++) {
            $data[count($data)] = $data5[$i];
        }

        for ($i = 0; $i < count($data); $i++) {
            $swapped = false;

            for ($j = 0; $j < count($data) - $i - 1; $j++) {

                if ($data[$j]['tanggal'] < $data[$j + 1]['tanggal']) {
                    $t = $data[$j];
                    $data[$j] = $data[$j + 1];
                    $data[$j + 1] = $t;
                    $swapped = true;
                }
            }

            if ($swapped == false) {
                break;
            }

        }

        if (empty($data)) {
            return ['status' => 'Success', 'data' => []];
        }

        return ['status' => 'Success', 'data' => array_slice($data, 0, 15)];
    }

    public function getEmergency() {
        $cek_emergency = new Owner(null,null);
        $cek_emergency->setDb($this->db);
        $emergency = $cek_emergency->getNomorEmergency();
        $data = [
            'nomor_emergency' => $emergency[0]['nomor_emergency'],
        ];
        return ['status' => 'Success', 'data' => $data, 'message' => 'Nomor Emergency Mougo'];
    }

    public function insertEmergencyUser($id_user) {
        $sql = 'INSERT INTO emergency( id_user , jenis_bantuan )
        VALUE( :id_user, :jenis_bantuan)';
        $est = $this->db->prepare($sql);
        $data = [
            ":id_user" => $id_user,
            ":jenis_bantuan" => JENIS_EMERGENCY_TELPON,
        ];

        if ($est->execute($data)) {
            return ['status' => 'Success', 'message' => 'Berhasil Melaporkan Emergency'];
        }return ['status' => 'Error', 'message' => 'Gagal Melaporkan Emergency'];

    }

    public function getBonusLevel($id_user) {
        $sql = "SELECT SUM(pendapatan) AS pendapatan_level FROM bonus_level
                WHERE id_user = '$id_user'";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getBonusTrip($id_user) {
        $sql = "SELECT SUM(pendapatan) AS pendapatan_trip FROM bonus_trip
                WHERE id_user = '$id_user'";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getBonusTransfer($id_user) {
        $sql = "SELECT SUM(pendapatan) AS pendapatan_transfer FROM bonus_transfer
                WHERE id_user = '$id_user'";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getBonusSponsor($id_user) {
        $sql = "SELECT SUM(pendapatan) AS pendapatan_sponsor FROM bonus_sponsor
                WHERE id_user_atasan = '$id_user'";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getBonusTitik($id_user) {
        $sql = "SELECT SUM(pendapatan) AS pendapatan_titik FROM bonus_titik
                WHERE id_user = '$id_user'";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getBonus($id_user) {
        $user = $this->cekUser($id_user);
        if (empty($user)) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        $level = $this->getBonusLevel($id_user);
        $trip = $this->getBonusTrip($id_user);
        $transfer = $this->getBonusTransfer($id_user);
        $sponsor = $this->getBonusSponsor($id_user);
        $titik = $this->getBonusTitik($id_user);

        $data['total_bonus'] = $level['pendapatan_level'] + $trip['pendapatan_trip'] + $transfer['pendapatan_transfer'] + $sponsor['pendapatan_sponsor'] + $titik['pendapatan_titik'];
        $data['data_bonus'][0]['id_bonus'] = ID_BONUS_LEVEL;
        $data['data_bonus'][0]['nama_bonus'] = BONUS_LEVEL;
        $data['data_bonus'][0]['pendapatan'] = (double) $level['pendapatan_level'];
        $data['data_bonus'][1]['id_bonus'] = ID_BONUS_TRIP;
        $data['data_bonus'][1]['nama_bonus'] = BONUS_TRIP;
        $data['data_bonus'][1]['pendapatan'] = (double) $trip['pendapatan_trip'];
        $data['data_bonus'][2]['id_bonus'] = ID_BONUS_TRANSFER;
        $data['data_bonus'][2]['nama_bonus'] = BONUS_TRANSFER;
        $data['data_bonus'][2]['pendapatan'] = (double) $transfer['pendapatan_transfer'];
        $data['data_bonus'][3]['id_bonus'] = ID_BONUS_SPONSOR;
        $data['data_bonus'][3]['nama_bonus'] = BONUS_SPONSOR;
        $data['data_bonus'][3]['pendapatan'] = (double) $sponsor['pendapatan_sponsor'];
        $data['data_bonus'][4]['id_bonus'] = ID_BONUS_TITIK;
        $data['data_bonus'][4]['nama_bonus'] = BONUS_TITIK;
        $data['data_bonus'][4]['pendapatan'] = (double) $titik['pendapatan_titik'];
        return ['status' => 'Success', 'message' => 'Bonus History', 'data' => $data];
    }

    public function getBonusLevelDetail($id_user) {
        $sql = "SELECT pendapatan, tanggal_pendapatan FROM bonus_level
                WHERE id_user = '$id_user'
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getBonusTripDetail($id_user) {
        $sql = "SELECT  jenis_trip.jenis_trip AS keterangan_bonus, pendapatan, tanggal_pendapatan FROM bonus_trip
                INNER JOIN trip ON trip.id_trip = bonus_trip.id_trip
                INNER JOIN jenis_trip ON jenis_trip.id = trip.jenis_trip
                WHERE bonus_trip.id_user = '$id_user'
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getBonusTransferDetail($id_user) {
        $sql = "SELECT pendapatan, tanggal_transfer AS tanggal_pendapatan FROM bonus_transfer
                WHERE id_user = '$id_user'
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getBonusSponsorDetail($id_user) {
        $sql = "SELECT pendapatan, tanggal_pendapatan FROM bonus_sponsor
                WHERE id_user_atasan = '$id_user'
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getBonusTitikDetail($id_user) {
        $sql = "SELECT pendapatan, tanggal_pendapatan FROM bonus_titik
                WHERE id_user = '$id_user'
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->db->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function getBonusDetail($id, $id_user) {
        $user = $this->cekUser($id_user);
        if (empty($user)) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        if ($id == ID_BONUS_LEVEL) {
            $data = $this->getBonusLevelDetail($id_user);
            if (empty($data)) {
                return ['status' => 'Error', 'message' => 'User Belum Mendapatkan Bonus Level'];
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['keterangan_bonus'] = BONUS_LEVEL;
                $data[$i]['pendapatan'] = (double) $data[$i]['pendapatan'];
            }
            return ['status' => 'Success', 'data' => $data];
        }
        if ($id == ID_BONUS_TRIP) {
            $data = $this->getBonusTripDetail($id_user);
            if (empty($data)) {
                return ['status' => 'Error', 'message' => 'User Belum Mendapatkan Bonus Trip'];
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['pendapatan'] = (double) $data[$i]['pendapatan'];
            }
            return ['status' => 'Success', 'data' => $data];
        }
        if ($id == ID_BONUS_TRANSFER) {
            $data = $this->getBonusTransferDetail($id_user);
            if (empty($data)) {
                return ['status' => 'Error', 'message' => 'User Belum Mendapatkan Bonus Transfer'];
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['keterangan_bonus'] = BONUS_TRANSFER;
                $data[$i]['pendapatan'] = (double) $data[$i]['pendapatan'];
            }
            return ['status' => 'Success', 'data' => $data];
        }
        if ($id == ID_BONUS_SPONSOR) {
            $data = $this->getBonusSponsorDetail($id_user);
            if (empty($data)) {
                return ['status' => 'Error', 'message' => 'User Belum Mendapatkan Bonus Sponsor'];
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['keterangan_bonus'] = BONUS_SPONSOR;
                $data[$i]['pendapatan'] = (double) $data[$i]['pendapatan'];
            }
            return ['status' => 'Success', 'data' => $data];
        }
        if ($id == ID_BONUS_TITIK) {
            $data = $this->getBonusTitikDetail($id_user);
            if (empty($data)) {
                return ['status' => 'Error', 'message' => 'User Belum Mendapatkan Bonus Titik'];
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['keterangan_bonus'] = BONUS_TITIK;
                $data[$i]['pendapatan'] = (double) $data[$i]['pendapatan'];
            }
            return ['status' => 'Success', 'data' => $data];
        }
        return ['status' => 'Error', 'message' => 'Id Salah'];
    }

    public function getBonusAllHistory($id_user) {
        $user = $this->cekUser($id_user);
        if (empty($user)) {
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        $data = $this->getBonusLevelDetail($id_user);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['keterangan_bonus'] = BONUS_LEVEL;
            $data[$i]['pendapatan'] = (double) $data[$i]['pendapatan'];
        }

        $data2 = $this->getBonusTripDetail($id_user);
        for ($i = 0; $i < count($data2); $i++) {
            $data2[$i]['pendapatan'] = (double) $data2[$i]['pendapatan'];
        }

        $data3 = $this->getBonusTransferDetail($id_user);
        for ($i = 0; $i < count($data3); $i++) {
            $data3[$i]['keterangan_bonus'] = BONUS_TRANSFER;
            $data3[$i]['pendapatan'] = (double) $data3[$i]['pendapatan'];
        }

        $data4 = $this->getBonusSponsorDetail($id_user);
        for ($i = 0; $i < count($data4); $i++) {
            $data4[$i]['keterangan_bonus'] = BONUS_SPONSOR;
            $data4[$i]['pendapatan'] = (double) $data4[$i]['pendapatan'];
        }

        $data5 = $this->getBonusTitikDetail($id_user);
        for ($i = 0; $i < count($data5); $i++) {
            $data5[$i]['keterangan_bonus'] = BONUS_TITIK;
            $data5[$i]['pendapatan'] = (double) $data5[$i]['pendapatan'];
        }

        if (empty($data) && empty($data2) && empty($data3) && empty($data4) && empty($data5)) {
            return ['status' => 'Error', 'message' => 'User Belum Mendapatkan Bonus'];
        }

        $data = array_merge($data, $data2);
        $data = array_merge($data, $data3);
        $data = array_merge($data, $data4);
        $data = array_merge($data, $data5);

        for ($i = 0; $i < count($data); $i++) {
            for ($j = 0; $j < count($data) - $i - 1; $j++) {
                if ($data[$j]['tanggal_pendapatan'] < $data[$j + 1]['tanggal_pendapatan']) {
                    $temp = $data[$j + 1];
                    $data[$j + 1] = $data[$j];
                    $data[$j] = $temp;
                }
            }
        }
        return ['status' => 'Success', 'data' => $data];

    }

    public function kodeReferalAll($id_user) {
        $getBawahan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getBawahan->setDb($this->db);
        $bawahPerusahaan = $getBawahan->getReferalDownSys($id_user);

        $tampungBawah[0] = $bawahPerusahaan;
        $state = true;
        $i = 0;
        while ($state) {
            $j = 0;
            $state2 = true;

            while ($state2) {
                if ($tampungBawah[$i][$j]['id_user'] == ID_PERUSAHAAN) {
                    if (empty($tampungBawah[$i][$j + 1])) {
                        $state2 = false;
                    }
                    $j++;
                    continue;
                }
                $temp = $getBawahan->getReferalDownSys($tampungBawah[$i][$j]['id_user']);

                if (empty($temp)) {
                    if (empty($tampungBawah[$i][$j + 1])) {
                        $state2 = false;
                    }
                    $j++;
                    continue;
                }
                if (empty($tampungBawah[$i + 1])) {
                    $tampungBawah[$i + 1] = [];
                    $tampungBawah[$i + 1] = $temp;
                } else {
                    $tampungBawah[$i + 1] = array_merge($tampungBawah[$i + 1], $temp);
                }
                if (empty($tampungBawah[$i][$j + 1])) {
                    $state2 = false;
                }

                $j++;
            }

            if (empty($tampungBawah[$i + 1])) {
                $state = false;
            }

            $i++;
        }

        return $tampungBawah;

    }

    public function kodeReferalAllList($id_user) {
        $getBawahan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getBawahan->setDb($this->db);
        $bawahPerusahaan = $getBawahan->getReferalDownSysFull($id_user);

        $tampungBawah = $bawahPerusahaan;
        $temper = 0;
        $cek_nice = true;
        while (true) {
            $temper = $temper + count($tampungBawah);
            if ($cek_nice == true) {
                $temper = 0;
            }
            for ($j = $temper; $j < count($tampungBawah); $j++) {
                if ($tampungBawah[$j]['id_user'] == ID_PERUSAHAAN) {
                    continue;
                }

                $temp = $getBawahan->getReferalDownSysFull($tampungBawah[$j]['id_user']);

                if (empty($temp)) {
                    continue;
                } else {
                    $tampungBawah = array_merge($tampungBawah, $temp);
                }
            }
            $cek_nice == false;
            if (empty($tampungBawah[count($tampungBawah) + 1])) {
                break;
            }
        }

        return $tampungBawah;

    }

    public function getForBonusTitikCustomerTahun($id_user, $year) {
        $finish = STATUS_SAMPAI_TUJUAN;
        $sql = "SELECT count(id_trip) AS jumlah_trip_tahun FROM trip
                WHERE YEAR(trip.tanggal_transaksi) = '$year'
                AND status_trip = '$finish'
                AND id_customer = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getForBonusTitikDriverTahun($id_user, $year) {
        $finish = STATUS_SAMPAI_TUJUAN;
        $sql = "SELECT count(id_trip) AS jumlah_trip_tahun FROM trip
                WHERE YEAR(trip.tanggal_transaksi) = '$year'
                AND status_trip = '$finish'
                AND id_driver = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getForBonusTitikCustomerBulan($id_user, $year, $month) {
        $finish = STATUS_SAMPAI_TUJUAN;
        $sql = "SELECT count(id_trip) AS jumlah_trip_bulan FROM trip
                WHERE MONTH(trip.tanggal_transaksi) = '$month'
                AND YEAR(trip.tanggal_transaksi) = '$year'
                AND status_trip = '$finish'
                AND id_customer = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getForBonusTitikDriverBulan($id_user, $year, $month) {
        $finish = STATUS_SAMPAI_TUJUAN;
        $sql = "SELECT count(id_trip) AS jumlah_trip_bulan FROM trip
                WHERE MONTH(trip.tanggal_transaksi) = '$month'
                AND  YEAR(trip.tanggal_transaksi) = '$year'
                AND status_trip = '$finish'
                AND id_driver = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getForBonusTitikCustomerHari($id_user, $date) {
        $finish = STATUS_SAMPAI_TUJUAN;
        $sql = "SELECT count(id_trip) AS jumlah_trip_harian FROM trip
                WHERE DATE(trip.tanggal_transaksi) = '$date'
                AND status_trip = '$finish'
                AND id_customer = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getForBonusTitikDriverHari($id_user, $date) {
        $finish = STATUS_SAMPAI_TUJUAN;
        $sql = "SELECT count(id_trip) AS jumlah_trip_harian FROM trip
                WHERE DATE(trip.tanggal_transaksi) = '$date'
                AND status_trip = '$finish'
                AND id_driver = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getForBonusTitik() {
        $sql = "SELECT user.*, kode_referal.id_user AS bawah_referal FROM user
                INNER JOIN kode_referal ON kode_referal.id_user_atasan = user.id_user
                GROUP BY user.id_user ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function insertBonusTitik($id_user, $pendapatan) {
        $sql = "INSERT INTO bonus_titik(id_user, pendapatan)
                VALUES('$id_user','$pendapatan')";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function dayofyear2date($tDay, $tFormat = 'Y-m-d') {
        $day = intval($tDay);
        $day = ($day == 0) ? $day : $day - 1;
        $offset = intval(intval($tDay) * 86400);
        $str = date($tFormat, strtotime('Jan 1, ' . date('Y')) + $offset);
        return ($str);
    }

    public function bonusTitikTrigger() {
        $getBawahan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getBawahan->setDb($this->db);

        $titik_user = $this->getForBonusTitik();

        $bawah_user = [];
        for ($i = 0; $i < count($titik_user); $i++) {
            $bawah_user[$i] = $this->kodeReferalAllList($titik_user[$i]['id_user']);
        }

        $tahun = date('Y');
        $tahun_lalu = date('Y') - 1;

        $datediff = strtotime("$tahun-01-01") - strtotime("$tahun_lalu-01-01");
        $hari = round($datediff / (60 * 60 * 24));

        for ($j = 0; $j < count($titik_user); $j++) {

            $bawahan_berhasil = 0;
            for ($i = 0; $i < count($bawah_user[$j]); $i++) {
                $cek = true;
                $cek_tf = true;
                $total_transfer = $this->getForBonusTitikTransferTahun($bawah_user[$j][$i]['id_user'], $tahun_lalu);
                if ($total_transfer['jumlah_transfer_tahun'] < $hari * MINIMAL_TITIK_PERHARI) {
                    $cek_tf = false;
                }
                if ($bawah_user[$j][$i]['role'] == USER_ROLE) {
                    $total_trip = $this->getForBonusTitikCustomerTahun($bawah_user[$j][$i]['id_user'], $tahun_lalu);
                    if ($total_trip['jumlah_trip_tahun'] < $hari * MINIMAL_TITIK_PERHARI) {
                        $cek = false;
                    }
                } else {
                    $total_trip = $this->getForBonusTitikDriverTahun($bawah_user[$j][$i]['id_user'], $tahun_lalu);
                    if ($total_trip['jumlah_trip_tahun'] < $hari * MINIMAL_TITIK_PERHARI) {
                        $cek = false;
                    }
                }
                if ($cek == true) {
                    $bawahan_berhasil++;
                }
                if ($cek_tf == true) {
                    $bawahan_berhasil++;
                }
            }
            if ($bawahan_berhasil > 0) {
                $total_point_titik = $bawahan_berhasil * BONUS_TITIK_POINT;
                $point_user = $this->getPointUser($titik_user[$j]['id_user']);
                $point_titik = $point_user['jumlah_point'] + ($total_point_titik);
                $this->updatePoint($titik_user[$j]['id_user'], $point_titik);
                $this->insertBonusTitik($titik_user[$j]['id_user'], ($total_point_titik));
            }

        }
        return ['status' => 'Success', 'message' => 'Berhasil Menjalankan Bonus Titik'];
    }

    public function getForBonusTitikTransferTahun($id_user, $tahun) {
        $sql = "SELECT count(sender_user_id) AS jumlah_transfer_tahun FROM transfer
                WHERE YEAR(transfer.tanggal_transfer) = '$tahun'
                AND sender_user_id = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function inputAdminBantuan() {
        $sql = "SELECT * FROM bantuan";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        
        $sql = "SELECT * FROM admin_menjawab";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt2 = $est->fetchAll();
        
        if(empty($stmt2)){
            for ($i=0; $i < count($stmt); $i++) { 
                $id_bantuan = $stmt[$i]['id_bantuan'];
                $email = ADMIN_SILUMAN_MOUGO;
                $sql = "INSERT INTO admin_menjawab(id_bantuan, email_admin)
                VALUES('$id_bantuan','$email')";
                $est = $this->getDb()->prepare($sql);
                $est->execute();
            }
        }else{
            for ($i=0; $i < count($stmt); $i++) { 
                $status = true;
                for ($j=0; $j < count($stmt2) ; $j++) { 
                    if($stmt[$i]['id_bantuan'] == $stmt2[$j]['id_bantuan']){
                        $status = false;
                    }
                }
                if ($status) {
                    $id_bantuan = $stmt[$i]['id_bantuan'];
                    $email = ADMIN_SILUMAN_MOUGO;
                    $sql = "INSERT INTO admin_menjawab(id_bantuan, email_admin)
                    VALUES('$id_bantuan','$email')";
                    $est = $this->getDb()->prepare($sql);
                    $est->execute();
                }
            }
        }
        return ['status' => 'Success', 'message' => 'Berhasil Input Admin Jawab'];

    }
    
    public function editFilepath() {
        $sql = "SELECT * FROM detail_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        
        $sql = "SELECT * FROM driver";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt2 = $est->fetchAll();

        $sql = "SELECT * FROM detail_ukm";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt3 = $est->fetchAll();
        
        $sql = "SELECT * FROM blog";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt4 = $est->fetchAll();
        
        $sql = "SELECT * FROM layanan";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt5 = $est->fetchAll();
        
        $sql = "SELECT * FROM barang_ukm";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt6 = $est->fetchAll();
        
        $sql = "SELECT * FROM event";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt7 = $est->fetchAll();
        
        $sql = "SELECT * FROM bukti_pembayaran";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt8 = $est->fetchAll();
        
        if(!empty($stmt)){
            $this->FilePathUser($stmt);
        }

        if(!empty($stmt2)){
            $this->FilePathDriver($stmt2);
        }

        if(!empty($stmt3)){
            $this->FilePathDetailUkm($stmt3);
        }

        if(!empty($stmt4)){
            $this->FilePathBlog($stmt4);
        }

        if(!empty($stmt5)){
            $this->FilePathLayanan($stmt5);
        }

        if(!empty($stmt6)){
            $this->FilePathBarang($stmt6);
        }

        if(!empty($stmt7)){
            $this->FilePathEvent($stmt7);
        }

        if(!empty($stmt8)){
            $this->FilePathBuktiPembayaran($stmt8);
        }

        return ['status' => 'Success', 'message' => 'Berhasil Edit FilePath'];

    }

    public function FilePathUser($stmt){
        for ($i=0; $i < count($stmt); $i++) { 
            $id = $stmt[$i]['id_user'];
            $sql = "UPDATE detail_user
                    SET ";
            if($this->IsStripString($stmt[$i]['foto_ktp']) && $this->IsStripString($stmt[$i]['foto_kk'])){
                continue;
            }
            $file = $stmt[$i]['foto_ktp'];
            $sql2 = "foto_ktp = '$file', ";
            if(!$this->IsStripString($stmt[$i]['foto_ktp']) && !$this->IsStringPathNew($stmt[$i]['foto_ktp'])){
                $file = $this->FormatNewPath($stmt[$i]['foto_ktp']);
                $sql2 = "foto_ktp = '$file', ";
            }
            $file = $stmt[$i]['foto_kk'];
            $sql3 = "foto_kk = '$file' ";
            if(!$this->IsStripString($stmt[$i]['foto_kk']) && !$this->IsStringPathNew($stmt[$i]['foto_kk'])){
                $file = $this->FormatNewPath($stmt[$i]['foto_kk']);
                $sql3 = "foto_kk = '$file' ";
            }
            $sql = $sql.$sql2.$sql3." WHERE id_user = '$id'";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }

    public function FilePathDriver($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id_user'];
            $sql = "UPDATE driver
                    SET ";
            if($this->IsStripString($stmt2[$i]['foto_diri']) && $this->IsStripString($stmt2[$i]['foto_skck']) && $this->IsStripString($stmt2[$i]['foto_sim']) && $this->IsStripString($stmt2[$i]['foto_stnk'])){
                continue;
            }
            $file = $stmt2[$i]['foto_diri'];
            $sql2 = "foto_diri = '$file', ";
            if(!$this->IsStripString($stmt2[$i]['foto_diri']) && !$this->IsStringPathNew($stmt2[$i]['foto_diri'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_diri']);
                $sql2 = "foto_diri = '$file', ";
            }
            $file = $stmt2[$i]['foto_skck'];
            $sql3 = "foto_skck = '$file', ";
            if(!$this->IsStripString($stmt2[$i]['foto_skck']) && !$this->IsStringPathNew($stmt2[$i]['foto_skck'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_skck']);
                $sql3 = "foto_skck = '$file', ";
            }
            $file = $stmt2[$i]['foto_sim'];
            $sql4 = "foto_sim = '$file', ";
            if(!$this->IsStripString($stmt2[$i]['foto_sim']) && !$this->IsStringPathNew($stmt2[$i]['foto_sim'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_sim']);
                $sql4 = "foto_sim = '$file', ";
            }
            $file = $stmt2[$i]['foto_stnk'];
            $sql5 = "foto_stnk = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['foto_stnk']) && !$this->IsStringPathNew($stmt2[$i]['foto_stnk'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_stnk']);
                $sql5 = "foto_stnk = '$file' ";
            }
            $sql = $sql.$sql2.$sql3.$sql4.$sql5." WHERE id_user = '$id'";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }
    
    public function FilePathDetailUkm($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id_user'];
            $sql = "UPDATE detail_ukm
                    SET ";
            if($this->IsStripString($stmt2[$i]['foto_dokumen_perizinan']) && $this->IsStripString($stmt2[$i]['foto_rekening_tabungan']) && $this->IsStripString($stmt2[$i]['foto_banner_ukm'])){
                continue;
            }
            $file = $stmt2[$i]['foto_dokumen_perizinan'];
            $sql2 = "foto_dokumen_perizinan = '$file', ";
            if(!$this->IsStripString($stmt2[$i]['foto_dokumen_perizinan'])  && !$this->IsStringPathNew($stmt2[$i]['foto_dokumen_perizinan'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_dokumen_perizinan']);
                $sql2 = "foto_dokumen_perizinan = '$file', ";
            }
            $file = $stmt2[$i]['foto_rekening_tabungan'];
            $sql3 = "foto_rekening_tabungan = '$file', ";
            if(!$this->IsStripString($stmt2[$i]['foto_rekening_tabungan'])  && !$this->IsStringPathNew($stmt2[$i]['foto_rekening_tabungan'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_rekening_tabungan']);
                $sql3 = "foto_rekening_tabungan = '$file', ";
            }
            $file = $stmt2[$i]['foto_banner_ukm'];
            $sql5 = "foto_banner_ukm = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['foto_banner_ukm']) && !$this->IsStringPathNew($stmt2[$i]['foto_banner_ukm'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_banner_ukm']);
                $sql5 = "foto_banner_ukm = '$file' ";
            }
            $sql = $sql.$sql2.$sql3.$sql5." WHERE id_user = '$id'";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }

    public function FilePathBlog($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id_blog'];
            $sql = "UPDATE blog
                    SET ";
            if($this->IsStripString($stmt2[$i]['foto_blog'])){
                continue;
            }
            $file = $stmt2[$i]['foto_blog'];
            $sql5 = "foto_blog = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['foto_blog'])  && !$this->IsStringPathNew($stmt2[$i]['foto_blog'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_blog']);
                $sql5 = "foto_blog = '$file' ";
            }
            $sql = $sql.$sql5." WHERE id_blog = $id";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }
    
    public function FilePathLayanan($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id_layanan'];
            $sql = "UPDATE layanan
                    SET ";
            if($this->IsStripString($stmt2[$i]['foto_layanan'])){
                continue;
            }
            $file = $stmt2[$i]['foto_layanan'];
            $sql5 = "foto_layanan = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['foto_layanan'])  && !$this->IsStringPathNew($stmt2[$i]['foto_layanan'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_layanan']);
                $sql5 = "foto_layanan = '$file' ";
            }
            $sql = $sql.$sql5." WHERE id_layanan = $id";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }
   
    public function FilePathBarang($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id_barang'];
            $sql = "UPDATE barang_ukm
                    SET ";
            if($this->IsStripString($stmt2[$i]['foto_barang'])){
                continue;
            }
            $file = $stmt2[$i]['foto_barang'];
            $sql5 = "foto_barang = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['foto_barang'])  && !$this->IsStringPathNew($stmt2[$i]['foto_barang'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_barang']);
                $sql5 = "foto_barang = '$file' ";
            }
            $sql = $sql.$sql5." WHERE id_barang = $id";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }

    public function FilePathEvent($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id'];
            $sql = "UPDATE event
                    SET ";
            if($this->IsStripString($stmt2[$i]['gambar_event'])){
                continue;
            }
            $file = $stmt2[$i]['gambar_event'];
            $sql5 = "gambar_event = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['gambar_event'])  && !$this->IsStringPathNew($stmt2[$i]['gambar_event'])){
                $file = $this->FormatNewPath($stmt2[$i]['gambar_event']);
                $sql5 = "gambar_event = '$file' ";
            }
            $sql = $sql.$sql5." WHERE id = $id";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }
    
    public function FilePathBuktiPembayaran($stmt2){
        for ($i=0; $i < count($stmt2); $i++) { 
            $id = $stmt2[$i]['id_topup'];
            $sql = "UPDATE bukti_pembayaran
                    SET ";
            if($this->IsStripString($stmt2[$i]['foto_transfer'])){
                continue;
            }
            $file = $stmt2[$i]['foto_transfer'];
            $sql5 = "foto_transfer = '$file' ";
            if(!$this->IsStripString($stmt2[$i]['foto_transfer'])  && !$this->IsStringPathNew($stmt2[$i]['foto_transfer'])){
                $file = $this->FormatNewPath($stmt2[$i]['foto_transfer']);
                $sql5 = "foto_transfer = '$file' ";
            }
            $sql = $sql.$sql5." WHERE id_topup = '$id'";
            $est = $this->getDb()->prepare($sql);
            $est->execute();
        }
    }

    public function IsStripString($string){
        if($string == STRING_KOSONG){
            return true;
        }
        return false;
    }

    public function IsStringPathNew($string){
        if(substr($string,0,1) != "/"){
            return false;
        }
        return true;
    }

    public function FormatNewPath($string){
        return "/".$string;
    }

    public function CustomerGetDriverOnlinePosition(){
        $data = $this->GetDriverOnlinePosition();
        if (empty($data)) {
            return ['status' => 'Error', 'message' => 'Tidak Ada Driver Online', 'data' => []];
        }
        return ['status' => 'Success', 'message' => 'Posisi Driver Online Ditemukan', 'data' => $data];
    }

    public function GetDriverOnlinePosition(){
        $status = STATUS_ONLINE;
        $sql = "SELECT position.latitude, position.longitude FROM position
                INNER JOIN driver ON driver.id_user = position.id_user
                WHERE status_online = $status";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

    public function deleteUser($id_user) {
        $sql = "DELETE FROM user
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }
    
    public function deleteDetailUser($id_user) {
        $sql = "DELETE FROM detail_user
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }
    
    public function deleteUkmUser($id_user) {
        $sql = "DELETE FROM ukm
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }
    
    public function deleteDetailUkmUser($id_user) {
        $sql = "DELETE FROM detail_ukm
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function deleteKategoriUkmUser($id_user) {
        $sql = "DELETE FROM kategori_ukm
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function MerchantRollbackData($id_user){
        $this->deleteKategoriUkmUser($id_user);
        $this->deleteDetailUkmUser($id_user);
        $this->deleteUkmUser($id_user);
        $this->deleteDetailUser($id_user);
        $this->deleteUser($id_user);
    }
    
}