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
        if (empty($trip_cek->getTripDetail($id))) {
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
                return ['status' => 'Success', 'message' => 'Sampai Tujuan'];
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
        if($jumlah_topup<50000){
            return ['status'=>'Error','message'=>'Pengisian Saldo Tidak Boleh Kurang Dari Rp50.000'];
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
            return ['status' => 'Success', 'message' => 'Berhasil, Silahkan Konfirmasi Top Up Anda', 'id_topup' => $id, 'jumlah_topup' => $jumlah_topup, 'no_rek' => NO_REK_PERUSAHAAN, 'nama_rek' => NAMA_REK_PERUSAHAAN];
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
        return $temp;
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
            return ['Status' => 'Success', 'message' => 'Upload Bukti Pembayaran Berhasil, Silahkan Tunggu Konfirmasi Admin'];
        }return ['Status' => 'Error', 'message' => 'Gagal Upload Bukti Pembayaran'];

    }

    public function topupUpdate($id, $status) {
        $data_topup = $this->getDetailTopup($id);
        if(empty($data_topup)){
            return ['Status' => 'Error', 'message' => 'ID Topup Tidak Ditemukan'];
        }
        if(empty($this->getBuktiPembayaran($id))){
            return ['Status' => 'Error', 'message' => 'User Belum Memberikan Bukti Pembayaran'];
        }
        switch ($status) {
            case TOPUP_ACCEPT:
                $detail_topup = $data_topup;
                if (empty($detail_topup)) {
                    return ['Status' => 'Error', 'message' => 'Topup Tidak Ditemukan'];
                }
                if ($detail_topup['status_topup']==2) {
                    return ['Status' => 'Error', 'message' => 'Gagal, Topup Ini Telah Diterima Oleh Admin'];
                }
                $detail_saldo = $this->getSaldoUser($detail_topup['id_user']);
                $detail_saldo['jumlah_saldo'] = $detail_saldo['jumlah_saldo'] + $detail_topup['jumlah_topup'];
                if (!$this->updateSaldo($detail_topup['id_user'], $detail_saldo['jumlah_saldo'])) {
                    return ['Status' => 'Error', 'message' => 'Gagal Tambah Saldo'];
                }
                if (!$this->updateTopup($id, STATUS_TOPUP_ACCEPT)) {
                    return ['Status' => 'Error', 'message' => 'Gagal Tambah Saldo'];
                }
                return ['Status' => 'Success', 'message' => 'Saldo User Berhasil Diterima'];
            case TOPUP_REJECT:
                if($data_topup['status_topup']==2){
                    return ['Status' => 'Error', 'message' => 'Gagal, Topup User Telah Berhasil Diterima Oleh Admin'];
                }
                if (!$this->deleteBuktiPembayaran($id)) {
                    return ['Status' => 'Error', 'message' => 'Gagal Menolak Topup'];
                }
                return ['Status' => 'Success', 'message' => 'Berhasil Menolak Topup'];

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

    public function updateTopup($id, $status) {
        $sql = "UPDATE top_up
                SET status_topup = '$status'
                WHERE id_topup = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }return false;
    }

}
