<?php

class Profile {
    private $id_user;
    private $no_ktp;
    private $provinsi;
    private $kota;
    private $bank;
    private $no_rekening;
    private $atas_nama_bank;
    private $foto_ktp;
    private $foto_kk;
    private $db;

    public function __construct($id_user, $no_ktp, $provinsi, $kota, $bank, $no_rekening, $atas_nama_bank, $foto_ktp, $foto_kk) {
        $this->id_user = $id_user;
        $this->no_ktp = $no_ktp;
        $this->provinsi = $provinsi;
        $this->kota = $kota;
        $this->bank = $bank;
        $this->no_rekening = $no_rekening;
        $this->atas_nama_bank = $atas_nama_bank;
        $this->foto_ktp = $foto_ktp;
        $this->foto_kk = $foto_kk;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function isValid($status) {
        switch ($status) {
            case PROFILE:
                if (empty($this->id_user) || empty($this->no_ktp) || empty($this->provinsi) || empty($this->kota) || empty($this->bank) || empty($this->no_rekening) || empty($this->atas_nama_bank)) {
                    return false;
                }return true;

            case PROFILE_DRIVER:
                if (empty($this->id_user) || empty($this->provinsi) || empty($this->kota) || empty($this->bank) || empty($this->no_rekening) || empty($this->atas_nama_bank)) {
                    return false;
                }return true;
        }
    }

    public function inputProfile($status) {
        $data = $this->getDetailUser($this->id_user);
        if (empty($data)) {
            return ['status' => 'Error', 'message' => 'ID tidak ditemukan'];
        }
        if ($status == PROFILE_DRIVER) {
            if ($this->no_ktp == null) {
                if ($this->insertDetailUserDriver()) {
                    return ['status' => 'Success', 'message' => 'Profile Berhasil Terupdate'];
                }
                return ['status' => 'Error', 'message' => 'Gagal Update Data'];
            }
            if ($this->insertDetailUser()) {
                return ['status' => 'Success', 'message' => 'Profile Berhasil Terupdate'];
            }
            return ['status' => 'Error', 'message' => 'Gagal Update Data'];
        }
        if ($this->insertDetailUser()) {
            return ['status' => 'Success', 'message' => 'Profile Berhasil Terupdate'];
        }
        return ['status' => 'Error', 'message' => 'Gagal Update Data'];
    }

    public function getDetailUser($id_user) {
        $sql = "SELECT * FROM detail_user
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function insertDetailUserDriver() {
        $sql = "UPDATE detail_user
                SET provinsi = :provinsi, kota = :kota, bank = :bank, no_rekening = :no_rekening, atas_nama_bank = :atas_nama_bank
                WHERE id_user = :id_user";
        $data = [
            ':provinsi' => $this->provinsi,
            ':kota' => $this->kota,
            ':bank' => $this->bank,
            ':no_rekening' => $this->no_rekening,
            ':atas_nama_bank' => $this->atas_nama_bank,
            ':id_user' => $this->id_user,
        ];
        $est = $this->getDb()->prepare($sql);
        return $est->execute($data);
    }

    public function insertDetailUser() {
        $sql = "UPDATE detail_user
                SET no_ktp = :no_ktp, provinsi = :provinsi, kota = :kota, bank = :bank, no_rekening = :no_rekening, atas_nama_bank = :atas_nama_bank
                WHERE id_user = :id_user";
        $data = [
            ':no_ktp' => $this->no_ktp,
            ':provinsi' => $this->provinsi,
            ':kota' => $this->kota,
            ':bank' => $this->bank,
            ':no_rekening' => $this->no_rekening,
            ':atas_nama_bank' => $this->atas_nama_bank,
            ':id_user' => $this->id_user,
        ];
        $est = $this->getDb()->prepare($sql);
        return $est->execute($data);
    }

    public function getAllProfile($id_user, $role) {
        $sql = "SELECT user.id_user, user.nama, user.email, user.no_telpon, user.status_aktif_trip, detail_user.no_ktp, detail_user.provinsi, detail_user.kota, detail_user.no_rekening, detail_user.atas_nama_bank, detail_user.foto_ktp, detail_user.foto_kk, bank.code AS kode_bank, bank.name AS nama_bank, saldo.jumlah_saldo, point.jumlah_point, kode_referal.kode_referal, kode_sponsor.kode_sponsor  FROM user
                ";

        if ($role == DRIVER_ROLE) {
            $sql = "SELECT user.id_user, user.nama, user.email, user.no_telpon, detail_user.no_ktp, detail_user.provinsi, detail_user.kota, detail_user.no_rekening, detail_user.atas_nama_bank, detail_user.foto_ktp, detail_user.foto_kk, bank.code AS kode_bank, bank.name AS nama_bank , cabang.id AS id_cabang , cabang.cabang, saldo.jumlah_saldo, point.jumlah_point, driver.foto_diri, driver.foto_skck, driver.foto_sim, driver.foto_stnk, kode_referal.kode_referal, kode_sponsor.kode_sponsor, driver.status_akun_aktif FROM user
            INNER JOIN driver ON driver.id_user = user.id_user
            INNER JOIN cabang ON driver.cabang = cabang.id  ";
        }

        $sql = $sql . " INNER JOIN detail_user ON detail_user.id_user = user.id_user
        INNER JOIN bank ON bank.code = detail_user.bank
        INNER JOIN saldo ON saldo.id_user = user.id_user
        INNER JOIN point ON point.id_user = user.id_user
        INNER JOIN kode_referal ON kode_referal.id_user = user.id_user
        INNER JOIN kode_sponsor ON kode_sponsor.id_user = user.id_user ";

        $sql = $sql . " WHERE user.id_user = '$id_user' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $data = $est->fetch();
        $waris = $this->getAhliWaris($id_user);
        if (!empty($waris)) {
            for ($i = 0; $i < count($waris); $i++) {
                $data['ahli_waris'][$i]['id'] = (int) $waris[$i]['id'];
                $data['ahli_waris'][$i]['nama_ahliwaris'] = $waris[$i]['nama_ahliwaris'];
            }
        } else {
            $data['ahli_waris'] = [];
        }
        if ($role == DRIVER_ROLE) {
            $data['status_akun_aktif'] = (int) $data['status_akun_aktif'];
        }
        $data['kode_bank'] = (int) $data['kode_bank'];
        $data['jumlah_saldo'] = (double) $data['jumlah_saldo'];
        $data['jumlah_point'] = (double) $data['jumlah_point'];

        $nama = decrypt($data['nama'], MOUGO_CRYPTO_KEY);
        $search = array(' ');
        $replace = array('+');
        $namaa = str_replace($search, $replace, $nama);
        $data['avatar_ui'] = "https://ui-avatars.com/api/?name=$namaa&rounded=true";
        return $data;
    }

    public function getAhliWaris($id_user) {
        $sql = "SELECT id, nama_ahliwaris FROM ahli_waris
                WHERE id_user = '$id_user'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetchAll();
    }

}
