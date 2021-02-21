<?php

class Admin {
    private $email_admin;
    private $nama_admin;
    private $password;
    private $no_telpon;
    private $db;

    public function __construct($email, $nama, $password, $no_telpon) {
        $this->email_admin = $email;
        $this->nama_admin = $nama;
        $this->password = $password;
        $this->no_telpon = $no_telpon;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function isValid($status) {
        switch ($status) {
            case LOGIN:
                if (empty($this->email_admin) || empty($this->password)) {
                    return false;
                }return true;

            case REGISTER:
                if (empty($this->email_admin) || empty($this->nama_admin) || empty($this->no_telpon) || empty($this->password)) {
                    return false;
                }return true;

        }
    }

    public function registerAdmin() {
        if (!$this->isValid(REGISTER)) {
            return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
        }
        if (!empty($this->cekDataAdmin($this->email_admin, $this->no_telpon))) {
            return ['status' => 'Error', 'message' => 'Email Atau Nomor Telpon Telah Digunakan'];
        }
        if ($this->inputAdmin()) {
            return ['status' => 'Success', 'message' => 'Admin Terdaftar'];
        }
        return ['status' => 'Error', 'message' => 'Gagal Input Data'];

    }

    public function loginAdmin() {
        if (!$this->isValid(LOGIN)) {
            return ['status' => 'Error', 'message' => 'Email atau Password Harus Di isi'];
        }
        $data_admin = $this->cekDataAdmin($this->email_admin, $this->no_telpon);
        if (empty($data_admin)) {
            return ['status' => 'Error', 'message' => 'Email atau Password salah'];
        }
        if ($data_admin['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $data_admin['email_admin'], 'nama' => $data_admin['nama_admin'], 'role' => 'Admin'];
        }
        return ['status' => 'Error', 'message' => 'Email atau Password Salah'];

    }

    public function inputAdmin() {
        $sql = "INSERT INTO admin (email_admin,nama_admin,password,no_telpon)
                VALUES(:email,:nama,:password,:no_telpon)";
        $data = [
            ':email' => $this->email_admin,
            ':nama' => $this->nama_admin,
            ':password' => $this->password,
            ':no_telpon' => $this->no_telpon,
        ];
        $est = $this->getDb()->prepare($sql);
        if ($est->execute($data)) {
            return true;
        }return false;
    }

    public function cekDataAdmin($email, $no_telpon) {
        $sql = "SELECT * FROM admin
                WHERE email_admin LIKE '$email' OR no_telpon LIKE '$no_telpon' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function deleteAdmin($id) {
        $sql = "DELETE FROM admin
                WHERE email_admin = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    private $column_search = array('id_topup', 'jumlah_topup', 'nama', 'email', 'id_topup', 'tanggal_topup');
    private $orderan = array('nama' => 'asc');

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
        $sql = "SELECT * FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN bukti_pembayaran ON bukti_pembayaran.id_topup = top_up.id_topup
                WHERE status_topup = 1 ";
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
            if ($order_by == 0 || $order_by == 1 || $order_by == 4 || $order_by == 5) {
                $temp = "top_up";
            } else if ($order_by == 3 || $order_by == 2) {
                $temp = "user";
            } 
            $order_in = $this->column_search[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->orderan)) {
            $order_by = $this->orderan;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsTopup() {
        $sql = "SELECT * FROM top_up
        INNER JOIN user ON user.id_user = top_up.id_user
        INNER JOIN bukti_pembayaran ON bukti_pembayaran.id_topup = top_up.id_topup
        WHERE status_topup = 1";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_driver = array('nama', 'email', 'no_telpon', 'alamat_domisili', 'cabang', 'jenis_kendaraan', 'merk_kendaraan', 'no_polisi');
    private $order_driver = array('nama' => 'asc');

    public function getDriverAdminWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getDriverQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;

    }

    public function getDriverConfirm($id) {
        $sql = "SELECT * FROM user
        INNER JOIN detail_user ON detail_user.id_user = user.id_user
        INNER JOIN driver ON driver.id_user = user.id_user
        INNER JOIN cabang ON cabang.id = driver.cabang
        INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
        WHERE driver.status_akun_aktif = 0
        AND (no_ktp = '$id' OR user.email = '$id' OR user.no_telpon = '$id')";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function cekDriverConfirm($id) {
        $sql = "SELECT * FROM user
        INNER JOIN detail_user ON detail_user.id_user = user.id_user
        INNER JOIN driver ON driver.id_user = user.id_user
        INNER JOIN cabang ON cabang.id = driver.cabang
        INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
        WHERE (no_ktp = '$id' OR user.email = '$id' OR user.no_telpon = '$id')";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function getDriverQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM user
        INNER JOIN driver ON driver.id_user = user.id_user
        INNER JOIN cabang ON cabang.id = driver.cabang
        INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
        WHERE driver.foto_skck <> '-' AND driver.foto_sim <> '-' AND driver.foto_stnk <> '-' AND driver.foto_diri <> '-' AND driver.status_akun_aktif = 0 ";
        // foreach ($this->column_driver as $index => $value) {
        //     if (!empty($search)) {
        //         if ($index === 0) {
        //             $sql = $sql . " AND $value LIKE '%$search%' ";

        //         } else {
        //             $sql = $sql . " OR $value LIKE '%$search%' ";
        //         }
        //     }
        // }

        if (isset($order_by)) {
            $temp = "";
            if ($order_by < 3) {
                $temp = "user";
            } else {
                $temp = "driver";
            }
            $order_in = $this->column_driver[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->order_driver)) {
            $order_by = $this->order_driver;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    private $column_search_bantuan = array('nama', 'pertanyaan', 'jawaban');
    private $bantuan_id = array('nama' => 'asc');

    public function getBantuanWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getBantuanQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getBantuanQuery($order_by, $order, $search) {
        $sql = "SELECT * FROM bantuan
                INNER JOIN user ON user.id_user = bantuan.id_user
                WHERE bantuan.jawaban = '-' ";
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
                $temp = "bantuan";
            }
            $order_in = $this->column_search_bantuan[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->bantuan_id)) {
            $order_by = $this->bantuan_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsBantuan() {
        $sql = "SELECT * FROM bantuan
        INNER JOIN user ON user.id_user = bantuan.id_user
        WHERE bantuan.jawaban = '-'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_list_bantuan = array('pertanyaan', 'jawaban', 'tanggal_bantuan');
    private $bantuan_list = array('pertanyaan' => 'asc');

    public function getBantuanList($order_by, $order, $start, $length, $search) {
        $sql = $this->getBantuanListQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getBantuanListQuery($order_by, $order, $search) {
        $driver_siluman = ID_DRIVER_SILUMAN;
        $sql = "SELECT * FROM bantuan
                INNER JOIN user ON user.id_user = bantuan.id_user
                WHERE bantuan.id_user = '$driver_siluman' ";
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
                $temp = "bantuan";
            }
            $order_in = $this->column_list_bantuan[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->bantuan_list)) {
            $order_by = $this->bantuan_list;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsBantuanList() {
        $driver_siluman = ID_DRIVER_SILUMAN;
        $sql = "SELECT * FROM bantuan
                INNER JOIN user ON user.id_user = bantuan.id_user
                WHERE bantuan.id_user = '$driver_siluman' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    private $column_search_withdraw = array('nama', 'jumlah', 'jenis_withdraw' , 'status_withdraw' , 'tanggal_withdraw');
    private $withdraw_id = array('nama' => 'asc');

    public function getWithdrawWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getWithdrawQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getWithdrawQuery($order_by, $order, $search) {
        $sql = "SELECT withdraw.id, user.nama, withdraw.jumlah, jenis_withdraw.jenis_withdraw, status_withdraw.status_withdraw, withdraw.tanggal_withdraw FROM withdraw
                INNER JOIN user ON user.id_user = withdraw.id_user
                INNER JOIN jenis_withdraw ON jenis_withdraw.id = withdraw.jenis_withdraw
                INNER JOIN status_withdraw ON status_withdraw.id = withdraw.status_withdraw
                WHERE withdraw.status_withdraw = '0' ";
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
            } else if ($order_by == 1 || $order_by == 4) {
                $temp = "withdraw";
            } else if ($order_by == 2) {
                $temp = "jenis_withdraw";
            } else if ($order_by == 3) {
                $temp = "status_withdraw";
            }
            $order_in = $this->column_search_withdraw[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->withdraw_id)) {
            $order_by = $this->withdraw_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsWithdraw() {
        $sql = "SELECT withdraw.id, user.nama, withdraw.jumlah, jenis_withdraw.jenis_withdraw, status_withdraw.status_withdraw, withdraw.tanggal_withdraw FROM withdraw
        INNER JOIN user ON user.id_user = withdraw.id_user
        INNER JOIN jenis_withdraw ON jenis_withdraw.id = withdraw.jenis_withdraw
        INNER JOIN status_withdraw ON status_withdraw.id = withdraw.status_withdraw
        WHERE withdraw.status_withdraw = '0'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    public function cekDataBantuan($id) {
        $sql = "SELECT * FROM bantuan
                WHERE id_bantuan LIKE '$id' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function deleteBantuan($id) {
        $sql = "DELETE FROM bantuan
                WHERE id_bantuan = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function deleteBantuanList($id) {
        if (empty($this->cekDataBantuan($id, null))) {
            return ['status' => 'Error', 'message' => "Bantuan tidak ditemukan"];
        }
        if (!$this->deleteBantuan($id)) {
            return ['status' => 'Error', 'message' => "Gagal Menghapus Bantuan"];
        }
        return ['status' => 'Success', 'message' => "Berhasil Menghapus Bantuan"];
    }

    public function updateBantuanList($id, $pertanyaan, $jawaban) {
        if (empty($this->cekDataBantuan($id))) {
            return ['status' => 'Error', 'message' => "Bantuan tidak ditemukan"];
        }
        if (empty($pertanyaan) && empty($jawaban)) {
            return ['status' => 'Success', 'message' => "Tidak Ada Item Bantuan Yang Diupdate"];
        }
        $sql = "UPDATE bantuan
                SET ";
        if (!empty($pertanyaan)) {
            $sql = $sql . "pertanyaan = '$pertanyaan' ";
        }
        if (!empty($pertanyaan) && !empty($jawaban)) {
            $sql = $sql . ", ";
        }
        if (!empty($jawaban)) {
            $sql = $sql . "jawaban = '$jawaban' ";
        }
        $sql = $sql . " WHERE id_bantuan = '$id' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return ['status' => 'Success', 'message' => "Berhasil Mengupdate Bantuan"];
    }

  private $column_search_emergency = array('nama', 'no_telpon', 'tanggal_emergency');
    private $emergency_id = array('nama' => 'asc');

    public function getEmergencyWeb($order_by, $order, $start, $length, $search) {
        $sql = $this->getEmergencyQuery($order_by, $order, $search);

        if ($length != -1) {
            $sql = $sql . " LIMIT $start, $length";
        }
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetchAll();
        return $stmt;
    }

    public function getEmergencyQuery($order_by, $order, $search) {
        $sql = "SELECT user.nama, user.no_telpon, emergency.tanggal_emergency FROM emergency
                INNER JOIN user ON user.id_user = emergency.id_user";
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
            } else if ($order_by == 1 ) {
                $temp = "user";
            } else if ($order_by == 2) {
                $temp = "emergency";
            } 
            $order_in = $this->column_search_emergency[$order_by];
            $sql = $sql . " ORDER BY $temp.$order_in $order ";

        } else if (isset($this->emergency_id)) {
            $order_by = $this->emergency_id;
            $key = key($order_by);
            $order = $order_by[key($order_by)];
            $sql = $sql . " ORDER BY $key $order ";

        }
        return $sql;
    }

    public function countsEmergency() {
        $sql = "SELECT * FROM emergency
        INNER JOIN user ON user.id_user = emergency.id_user";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->rowCount();
    }

    public function getKonfirmasiTopup() {
        $sql = "SELECT COUNT(top_up.id_topup) AS jumlah_konfirmasi_topup FROM top_up
                INNER JOIN user ON user.id_user = top_up.id_user
                INNER JOIN bukti_pembayaran ON bukti_pembayaran.id_topup = top_up.id_topup
                WHERE status_topup = 1 ";
                
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function getKonfirmasiDriver() {
        $sql = "SELECT COUNT(user.id_user) AS jumlah_konfirmasi_driver FROM user
                INNER JOIN detail_user ON detail_user.id_user = user.id_user
                INNER JOIN driver ON driver.id_user = user.id_user
                INNER JOIN cabang ON cabang.id = driver.cabang
                INNER JOIN kategori_kendaraan ON kategori_kendaraan.id = driver.jenis_kendaraan
                WHERE (driver.foto_skck <> '-'
                AND driver.foto_sim <> '-'
                AND driver.foto_stnk <> '-'
                AND driver.foto_diri <> '-'
                AND driver.status_akun_aktif = 0)";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function getEmergencyAll() {
        $sql = "SELECT COUNT(id_emergency) AS jumlah_emergency FROM emergency";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function getBantuanReply() {
        $sql = "SELECT COUNT(id_bantuan) AS jumlah_bantuan FROM bantuan
                WHERE jawaban = '-'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function adminRekapDasbor() {
        $topup_konfirmasi = $this->getKonfirmasiTopup();
        $driver_konfirmasi = $this->getKonfirmasiDriver();
        $emergency_konfirmasi = $this->getEmergencyAll();
        $bantuan_konfirmasi = $this->getBantuanReply();
        $data['jumlah_konfirmasi_topup'] =(double) $topup_konfirmasi['jumlah_konfirmasi_topup'];
        $data['jumlah_konfirmasi_driver'] =(double) $driver_konfirmasi['jumlah_konfirmasi_driver'];
        $data['jumlah_emergency'] =(double) $emergency_konfirmasi['jumlah_emergency'];
        $data['jumlah_bantuan'] =(double) $bantuan_konfirmasi['jumlah_bantuan'];
        return ['status' => 'Success', 'message' => 'Rekapitulasi', 'data' => $data];
        
    }

    public function adminGetNomorMougo(){
        $data = $this->getNomorMougo();
        return ['status' => 'Success', 'message' => 'Nomor Mougo', 'data' => $data];
    }

    public function getNomorMougo() {
        $sql = "SELECT * FROM nomor_mougo";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function adminEditNomorMougo($nomor_mougo){
        if(empty($nomor_mougo)){
            return ['status' => 'Error', 'message' => 'Input tidak boleh kosong'];  
        }
        if ($this->editNomorMougo($nomor_mougo)){
            return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Nomor Mougo'];
        }
        return ['status' => 'Error', 'message' => 'Gagal Mengupdate Nomor Mougo'];
    }

    public function editNomorMougo($nomor_mougo) {
        $sql = "UPDATE nomor_mougo
                SET nomor_mougo = '$nomor_mougo'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function adminGetKontakMougo(){
        $data = $this->getKontakMougo();
        return ['status' => 'Success', 'message' => 'Kontak Mougo', 'data' => $data];
    }

    public function getKontakMougo() {
        $sql = "SELECT * FROM kontak_mougo";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function adminEditKontakMougo($kontak_mougo){
        if(empty($kontak_mougo)){
            return ['status' => 'Error', 'message' => 'Input tidak boleh kosong'];  
        }
        if ($this->editKontakMougo($kontak_mougo)){
            return ['status' => 'Success', 'message' => 'Berhasil Mengupdate Kontak Mougo'];
        }
        return ['status' => 'Error', 'message' => 'Gagal Mengupdate Kontak Mougo'];
    }

    public function editKontakMougo($kontak_mougo) {
        $sql = "UPDATE kontak_mougo
                SET kontak_mougo = '$kontak_mougo'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

}
