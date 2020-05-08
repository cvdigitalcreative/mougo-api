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
            $stmt['point']['jumlah_point'] =(double) $point['jumlah_point']; 
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
                FROM temporary_order";

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
                if($trip_cek->bonusFinish($id,$data_trip['id_customer'],$data_trip['id_driver'],$data_trip['total_harga'],$data_trip['jenis_pembayaran'])){
                    return ['status' => 'Success', 'message' => 'Sampai Tujuan'];
                }
            }
            if ($status == STATUS_CANCEL) {
                return ['status' => 'Success', 'message' => 'Trip Telah Dibatalkan'];
            }
        }return ['status' => 'Error', 'message' => 'Gagal Update Status'];
    }

    public function getHargaTotal($jarak) {
        if ($jarak <= JARAK_MINIMAL) {
            return ['status' => 'Success', 'harga' => HARGA_JARAK_MINIMAL];
        } else {
            $harga = HARGA_JARAK_MINIMAL;
            for ($i = 3; $i <= $jarak; $i++) {
                $harga = $harga + HARGA_JARAK_PERKILO;
            }
            return ['status' => 'Success', 'harga' => $harga];
        }return ['status' => 'Error', 'message' => 'Gagal Mendapatkan Harga'];
    }

    public function inputSaldo($jumlah_topup, $id_user) {
        if ($jumlah_topup < 50000) {
            return ['status' => 'Error', 'message' => 'Pengisian Saldo Tidak Boleh Kurang Dari Rp50.000'];
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
        if ($est->execute($data)) {
            $data = [
                'id_topup' => $id, 
                'jumlah_topup' => $jumlah_topup, 
                'no_rek' => NO_REK_PERUSAHAAN, 
                'nama_rek' => NAMA_REK_PERUSAHAAN, 
                'nama_bank' => NAMA_BANK_PERUSAHAAN
            ];
            return ['status' => 'Success', 'message' => 'Berhasil, Silahkan Konfirmasi Top Up Anda', 'data'=>$data];
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

    public function topupUpdate($id, $status) {
        $data_topup = $this->getDetailTopup($id);
        if (empty($data_topup)) {
            return ['status' => 'Error', 'message' => 'ID Topup Tidak Ditemukan'];
        }
        $bukti_pembayaran = $this->getBuktiPembayaran($id);
        if (empty($this->getBuktiPembayaran($id))) {
            return ['status' => 'Error', 'message' => 'User Belum Memberikan Bukti Pembayaran'];
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
                if (!$this->updateTopup($id, STATUS_TOPUP_ACCEPT)) {
                    return ['status' => 'Error', 'message' => 'Gagal Tambah Saldo'];
                }
                return ['status' => 'Success', 'message' => 'Saldo User Berhasil Diterima'];
            case TOPUP_REJECT:
                if ($data_topup['status_topup'] == 2) {
                    return ['status' => 'Error', 'message' => 'Gagal, Topup User Telah Berhasil Diterima Oleh Admin'];
                }
                if (unlink($bukti_pembayaran['foto_transfer'])) {
                    if (!$this->deleteBuktiPembayaran($id)) {
                        return ['status' => 'Error', 'message' => 'Gagal Menolak Topup'];
                    }
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

    public function updateTopup($id, $status) {
        $sql = "UPDATE top_up
                SET status_topup = '$status'
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

    public function deleteDriverFoto($id_user) {
        $sql = "UPDATE driver
                SET foto_skck = '-', foto_sim = '-', foto_stnk = '-',foto_diri = '-'
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function rejectDriver($id_user) {
        $data = $this->getDriverAdmin($id_user);
        $user = $this->cekFotoCustomer($id_user);
        if ($data['status_akun_aktif'] == STATUS_DRIVER_AKTIF) {
            return ['status' => 'Error', 'message' => 'Gagal, Reject Driver / Driver Telah Diterima Oleh Admin'];
        }
        if ($data['foto_skck'] == '-' && $data['foto_stnk'] == '-' && $data['foto_sim'] == '-' && $data['foto_diri'] == '-') {
            return ['status' => 'Error', 'message' => 'Gagal, Reject Driver / Driver Telah Direject Oleh Admin'];
        }
        if (unlink($data['foto_skck']) && unlink($data['foto_stnk']) && unlink($data['foto_sim']) && unlink($data['foto_diri']) && unlink($user['foto_ktp']) && unlink($user['foto_kk'])) {
            if($this->deleteUserFoto($id_user)){
            if (!$this->deleteDriverFoto($id_user)) {
                return ['status' => 'Error', 'message' => 'Gagal Reject Driver'];
            }
        }
        }
        return ['status' => 'Success', 'message' => 'Berhasil Reject Driver'];
    }

    public function editDriverStatus($id_driver, $status) {
        $sql = "UPDATE driver
                SET status_akun_aktif = '$status'
                WHERE id_user = '$id_driver'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Berhasil Mengaktifkan Driver'];
        }return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Driver'];
    }

    public function resetFoto($id) {
        $data = $this->cekFotoCustomer($id);
        if (file_exists($data['foto_kk'])) {
            unlink($data['foto_kk']);
        }
        if (file_exists($data['foto_ktp'])) {
            unlink($data['foto_ktp']);
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
                unlink("$directory/$filename");
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

    public function deleteWaris($id_user,$id) {
        $sql = "DELETE FROM ahli_waris
                WHERE id_user = '$id_user' AND id = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()){
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
        for ($i=0; $i < count($level1); $i++) { 
            $total++;
            $level2 = $this->getReferalChild($level1[$i]['id_user']);
            for ($j=0; $j < count($level2); $j++) { 
                $total++;
                $level3 = $this->getReferalChild($level2[$j]['id_user']);
                for ($c=0; $c < count($level3); $c++) { 
                    $total++;
                }    
            }    
        }
        return $total;
    }

    public function insertTransfer($id_user, $id_user_penerima, $jumlah) {

        $saldo_user = $this->getSaldoUser($id_user);
        $saldo_penerima = $this->getSaldoUser($id_user_penerima);
        $saldo_perusahaan = $this->getSaldoUser(ID_PERUSAHAAN);

        $saldo_user = $saldo_user['jumlah_saldo'] - ($jumlah + TRANSFER_CHARGE);
        $saldo_penerima = $saldo_penerima['jumlah_saldo'] + $jumlah;
        $saldo_perusahaan = $saldo_perusahaan['jumlah_saldo'] + TRANSFER_CHARGE;

        if(!$this->updateSaldo($id_user,$saldo_user) || !$this->updateSaldo($id_user_penerima,$saldo_penerima) || !$this->updateSaldo(ID_PERUSAHAAN,$saldo_perusahaan) ){
            return ['status' => 'Error', 'message' => 'Gagal Update Saldo'];
        }

        $sql = 'INSERT INTO transfer( sender_user_id , receipent_user_id , total_transfer )
        VALUE( :sender_user_id, :receipent_user_id , :total_transfer)';
        $est = $this->db->prepare($sql);
        $data = [
            ":sender_user_id" => $id_user,
            ":receipent_user_id" => $id_user_penerima,
            ":total_transfer" => $jumlah
        ];

        if ($est->execute($data)) {
            return ['status' => 'Success', 'message' => 'Transfer Berhasil Diproses'];
        }return ['status' => 'Error', 'message' => 'Terjadi Masalah Ketika Mengupdate Saldo'];

    }

    public function withdrawPoint($id_user, $jumlah, $jenis) {

        if(empty($this->cekUser($id_user))){
            return ['status' => 'Error', 'message' => 'User Tidak Ditemukan'];
        }
        if(empty($jumlah) || $jumlah<=JUMLAH_WITHDRAW_TERKECIL){
            return ['status' => 'Error', 'message' => 'Input Nominal Tidak Boleh Kosong'];
        }

        $point_user = $this->getPointUser($id_user);
        if($jumlah<JUMLAH_WITHDRAW_MINIMAL && $jenis == JENIS_WITHDRAW_REKENING){
            return ['status' => 'Error', 'message' => 'Untuk Withdraw Melalui Rekening Minimal Withdraw 100.000 Rupiah'];
        }
        if($point_user['jumlah_point']<$jumlah){
            return ['status' => 'Error', 'message' => 'Point Tidak Mencukupi Untuk Melakukan Withdraw'];
        }

        $point_user = $point_user['jumlah_point'] - $jumlah;

        if(!$this->updatePoint($id_user,$point_user) ){
            return ['status' => 'Error', 'message' => 'Gagal Update Point'];
        }

        $draw_status = STATUS_WITHDRAW_PENDING;
        $rekening = ", Untuk Withdraw Rekening Akan Dikirim Ke Nomor Rekening Yang Tersimpan";
        if($jenis == JENIS_WITHDRAW_SALDO){
            $draw_status = STATUS_WITHDRAW_SUCCESS;
            $saldo_user = $this->getSaldoUser($id_user);
            $saldo_user = $saldo_user['jumlah_saldo'] + $jumlah ;
            $rekening = "";
            if(!$this->updateSaldo($id_user,$saldo_user) ){
                return ['status' => 'Error', 'message' => 'Gagal Update Saldo'];
            }
        }
        
        $sql = 'INSERT INTO withdraw( id_user , jumlah , jenis_withdraw, status_withdraw )
        VALUE( :id_user , :jumlah , :jenis_withdraw, :status_withdraw)';
        $est = $this->db->prepare($sql);
        $data = [
            ":id_user" => $id_user,
            ":jumlah" => $jumlah,
            ":jenis_withdraw" => $jenis,
            ":status_withdraw" => $draw_status
        ];

        if ($est->execute($data)) {
            return ['status' => 'Success', 'message' => 'Withdraw Berhasil Diproses'.$rekening];
        }return ['status' => 'Error', 'message' => 'Terjadi Masalah Ketika Melakukan Withdraw'];

    }

}
