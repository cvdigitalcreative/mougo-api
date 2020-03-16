<?php

class User {
    private $id_user;
    private $nama;
    private $email;
    private $no_telpon;
    private $password;
    private $kode_referal;
    private $kode_sponsor;
    private $db;

    public function __construct($nama, $email, $no_telpon, $password, $kode_referal, $kode_sponsor) {
        $this->nama = $nama;
        $this->email = $email;
        $this->no_telpon = $no_telpon;
        $this->password = $password;
        $this->kode_referal = $kode_referal;
        $this->kode_sponsor = $kode_sponsor;
    }

    public function setUser($nama, $email, $no_telpon, $password, $kode_referal, $kode_sponsor) {
        $this->nama = $nama;
        $this->email = $email;
        $this->no_telpon = $no_telpon;
        $this->password = $password;
        $this->kode_referal = $kode_referal;
        $this->kode_sponsor = $kode_sponsor;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function getId_user() {
        return $this->id_user;
    }

    public function register($role) {
        if (!$this->isDataValid(REGISTER)) {
            return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
        }

        $status_aktif_user = STATUS_AKTIF_USER; //unactive
        $user_email = $this->email;
        $user_no_tlp = $this->no_telpon;


        //Kode Referal dan sponsor regenerate
        $kodeRefSp = $this->generateKodeRefSp();

        //CEK Kode Referal dan Sponsor Atasan

        $atasanRefSp = $this->atasanRefSp();

        if (empty($atasanRefSp)) {
            return ['status' => 'Error', 'message' => 'Referal Atasan Tidak Ditemukan'];
        }
        
        if ($this->cekUserEmailTelpon($user_email, $user_no_tlp)) {
            return ['status' => 'Error', 'message' => 'Email / Nomor Telpon Telah digunakan'];
        }

        //User Input
        if (!$this->insertUser($role, $status_aktif_user)) {
            return ['status' => 'Error', 'message' => 'Daftar User Gagal'];
        }

        //Input Kode Referal dan Sponsor Atasan

        if ($this->insertAtasanId($kodeRefSp['kode_ref'], $atasanRefSp['idAtasanRef'], $kodeRefSp['kode_sp'], $atasanRefSp['idAtasanSp'])) {

            //Token saldo point input
            if ($this->insertToken() && $this->insertSaldo() && $this->insertPoint() && $this->insertPosition()) {
                return ['status' => 'Success', 'message' => 'Pendaftaran Sukses'];
            }

        }return ['status' => 'Error', 'message' => 'Input Bermasalah'];

    }

    public function login($role) {
        if (!$this->isDataValid(LOGIN)) {
            return ['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'];
        }

        if ($this->cekRoleLogin($role)) {
            return ['status' => 'Error', 'message' => 'Akun Tidak Ditemukan'];
        }

        $result = $this->getToken();

        if (empty($result)) {
            return ['status' => 'Error', 'message' => 'Email dan No Telpon atau Password Salah'];
        }
        $res = [
            'id_user' => $result['id_user'],
            'token' => $result['token'],
        ];

        if ($result['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $res];
        }
        return ['status' => 'Error', 'message' => 'Password Salah'];
    }

    private function isDataValid($type) {
        switch ($type) {
            case LOGIN:
                $isValid = true;
                if (empty($this->email) || empty($this->no_telpon)) {
                    $isValid = false;
                }
                if (empty($this->password)) {
                    $isValid = false;
                }
                return $isValid;

            case REGISTER:
                $isValid = true;
                if (empty($this->kode_referal)) {
                    $this->kode_referal = KODE_REFERAL_DMS;
                }
                if (empty($this->kode_sponsor)) {
                    $this->kode_sponsor = KODE_SPONSOR_DMS;
                }
                if (empty($this->email) || empty($this->no_telpon) || empty($this->password) || empty($this->nama)) {
                    $isValid = false;
                }
                return $isValid;

        }

    }

    public function cekUserEmailTelpon($email, $no_telpon) {
        $sql_cek_tlp = "SELECT * FROM user
                WHERE email LIKE '$email' OR no_telpon LIKE '$no_telpon'";

        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute();
        $stmtcek = $estcek->fetch();

        if (!empty($stmtcek)) {
            return true;
        }return false;
    }

    public function insertUser($role, $status_aktif_user) {
        $sql = "INSERT INTO user (id_user , nama , email , no_telpon , role , password , status_aktif_trip)
                        VALUES (:id_user , :nama , :email , :no_telpon , :role , :password , :status_aktif_trip)";
        $id_user = sha1($this->nama . $this->email . $this->no_telpon);
        $data_user = [
            ':id_user' => $id_user,
            ':nama' => $this->nama,
            ':email' => $this->email,
            ':no_telpon' => $this->no_telpon,
            ':role' => $role,
            ':password' => $this->password,
            ':status_aktif_trip' => $status_aktif_user,
        ];

        $este = $this->db->prepare($sql);

        $this->id_user = $id_user;

        if ($este->execute($data_user)) {
            return true;
        }return false;
    }

    public function generateKodeRefSp() {
        while (true) {
            $kode = randomLett() . randomNum();
            $kode_ref = KODE_REFERAL_USER . $kode;
            $kode_sp = KODE_SPONSOR_USER . $kode;

            $sqlcek = "SELECT id_user FROM kode_referal
                            WHERE kode_referal LIKE '$kode_ref'";

            $estcek = $this->db->prepare($sqlcek);
            $estcek->execute();
            $stmtcek = $estcek->fetch();

            if (empty($stmtcek)) {
                break;
            }
        }
        return [
            'kode_ref' => $kode_ref,
            'kode_sp' => $kode_sp,
        ];
    }

    public function atasanRefSp() {
        $sql_ref_cek = "SELECT id_user AS id_atasan FROM kode_referal
                        WHERE kode_referal = '$this->kode_referal'";

        $est1 = $this->db->prepare($sql_ref_cek);
        $est1->execute();
        $stmt = $est1->fetch();

        $sql_sp_cek = "SELECT id_user AS id_atasan FROM kode_sponsor
                        WHERE kode_sponsor = '$this->kode_sponsor'";

        $est2 = $this->db->prepare($sql_sp_cek);
        $est2->execute();
        $stmt2 = $est2->fetch();

        if (!empty($stmt) && !empty($stmt2)) {
            return [
                'idAtasanRef' => $stmt['id_atasan'],
                'idAtasanSp' => $stmt2['id_atasan'],
            ];
        }
        return;
    }

    public function insertAtasanId($kodeRef, $atasanRef, $kodeSp, $atasanSp) {
        $sql_ref = "INSERT INTO kode_referal(id_user,kode_referal,id_user_atasan)
                    VALUES(:id_user,:kode_ref,:id_user_atasan)";
        $data_ref = [
            ':id_user' => $this->getId_user(),
            ':kode_ref' => $kodeRef,
            ':id_user_atasan' => $atasanRef,
        ];
        $estim = $this->db->prepare($sql_ref);

        $sql_sp = "INSERT INTO kode_sponsor(id_user,kode_sponsor,id_user_atasan)
                    VALUES(:id_user,:kode_sp,:id_user_atasan)";
        $data_sp = [
            ':id_user' => $this->getId_user(),
            ':kode_sp' => $kodeSp,
            ':id_user_atasan' => $atasanSp,
        ];
        $estim2 = $this->db->prepare($sql_sp);
        if ($estim->execute($data_ref) && $estim2->execute($data_sp)) {
            return true;
        }return false;
    }

    public function insertToken() {
        $sql_token = "INSERT INTO api_token (id_user , token , hits )
                            VALUES (:id_user , :token , :hits)";
        $data = [
            ':id_user' => $this->getId_user(),
            ':token' => sha1(rand()),
            ':hits' => HITS_AWAL,
        ];

        $estimat = $this->db->prepare($sql_token);
        if ($estimat->execute($data)) {
            return true;
        }return false;
    }

    public function insertSaldo() {
        $sql_saldo = "INSERT INTO saldo (id_user , jumlah_saldo )
                            VALUES (:id_user , :jumlah_saldo )";
        $data_saldo = [
            ':id_user' => $this->getId_user(),
            ':jumlah_saldo' => SALDO_AWAL,
        ];
        $estimate = $this->db->prepare($sql_saldo);
        if ($estimate->execute($data_saldo)) {
            return true;
        }return false;
    }

    public function insertPoint() {
        $sql_point = "INSERT INTO point (id_user , jumlah_point )
            VALUES (:id_user , :jumlah_point )";
        $data_point = [
            ':id_user' => $this->getId_user(),
            ':jumlah_point' => POINT_AWAL,
        ];
        $estimated = $this->db->prepare($sql_point);
        if ($estimated->execute($data_point)) {
            return true;
        }return false;
    }

    public function insertPosition() {
        $sql_position = "INSERT INTO position (id_user , latitude , longitude )
                            VALUES (:id_user , :latitude , :longitude)";
        $data = [
            ':id_user' => $this->getId_user(),
            ':latitude' => POSITION_LAT,
            ':longitude' => POSITION_LONG,
        ];

        $estimat = $this->db->prepare($sql_position);
        if ($estimat->execute($data)) {
            return true;
        }return false;
    }

    public function cekRoleLogin($role) {
        $sql_cek_tlp = "SELECT * FROM user
            WHERE ( email LIKE :email OR no_telpon LIKE :email ) AND role = $role ";
        $data_user = [
            ":email" => (!empty($this->email)) ? $this->email : $this->no_telpon,
        ];
        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute($data_user);
        $stmtcek = $estcek->fetch();
        if (empty($stmtcek)) {
            return true;
        }return false;
    }

    public function getToken() {
        $sql = "SELECT user.id_user , token , password FROM user
                        INNER JOIN api_token ON api_token.id_user = user.id_user
                        WHERE email = :email AND password = :pass OR no_telpon = :email AND password = :pass";
        $data_token = [
            ":email" => (!empty($this->email)) ? $this->email : $this->no_telpon,
            ":pass" => $this->password,
        ];
        $est = $this->db->prepare($sql);
        $est->execute($data_token);
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return [
                'id_user' => $stmt['id_user'],
                'token' => $stmt['token'],
                'password' => $stmt['password'],
            ];
        }return;
    }

}
