<?php
require_once dirname(__FILE__) . '/../aes.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class User {
    private $id_user;
    private $nama;
    private $email;
    private $no_telpon;
    private $password;
    private $kode_referal;
    private $kode_sponsor;
    private $db;
    private $web_url;

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

    public function setWeb_url($web_url) {
        $this->web_url = $web_url;
    }

    public function getWeb_url() {
        return $this->web_url;
    }

    public function getProfileUser($id) {
        $sql = "SELECT * FROM user
                WHERE id_user = '$id' ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return $stmt;
        }return $stmt;
    }

    public function getId_user() {
        return $this->id_user;
    }

    public function setId_user($id_user) {
        $this->id_user = $id_user;
    }

    public function register($role) {
        if (!$this->isDataValid(REGISTER)) {
            return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
        }

        $status_aktif_user = STATUS_AKTIF_USER_REGISTER; //belum konfirmasi
        $user_email = $this->email;
        $user_no_tlp = $this->no_telpon;

        //Kode Referal dan sponsor regenerate
        $kodeRefSp = $this->generateKodeRefSp();

        $this->kode_referal = $this->cekReferalUser($this->kode_referal);
        //CEK Kode Referal dan Sponsor Atasan
        $atasanRefSp = $this->atasanRefSp();

        $user_email = decrypt($user_email, MOUGO_CRYPTO_KEY);
        $user_email = str_replace(' ', '', $user_email);
        $user_email = encrypt($user_email, MOUGO_CRYPTO_KEY);
        $this->email = $user_email;

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

        if (!$this->insertTanggalPendaftaran()) {
            return ['status' => 'Error', 'message' => 'Gagal Set Tanggal Pendaftaran'];
        }
        //Input Kode Referal dan Sponsor Atasan

        if (!$this->insertAtasanId($kodeRefSp['kode_ref'], $atasanRefSp['idAtasanRef'], $kodeRefSp['kode_sp'], $atasanRefSp['idAtasanSp'])) {
            return ['status' => 'Error', 'message' => 'Input Bermasalah'];
        }

        if($role != MERCHANT_ROLE){
            $email_send = new SendEmail($this->email, $this->nama, $role, $this->getWeb_url(), $this->id_user);
            $email_send->start();
        }else{
            $this->insertDetailProfile();
        }

        return ['status' => 'Success', 'message' => 'Pendaftaran Sukses'];

    }

    public function login($role) {
        if (!$this->isDataValid(LOGIN)) {
            return ['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'];
        }

        if ($this->cekRoleLogin($role)) {
            return ['status' => 'Error', 'message' => 'Akun Tidak Ditemukan'];
        }

        $user = $this->getUserData();
        $result = $this->getToken();

        if ($user['status_akun'] == 3) {
            return ['status' => 'Error', 'message' => 'Belum Konfirmasi Akun'];
        }

        if (empty($result)) {
            return ['status' => 'Error', 'message' => 'Email atau Nomor telpon salah'];
        }

        $res = [
            'id_user' => $result['id_user'],
            'token' => $result['token'],
        ];

        if ($role == DRIVER_ROLE) {
            $driver = new Driver(null, null, null, null, null);
            $driver->setDb($this->db);
            $data_driver = $driver->getProfileDriver($result['id_user']);
            $res['jenis_kendaraan'] = $data_driver['jenis_kendaraan'];
        }

        if ($result['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $res];
        }
        return ['status' => 'Error', 'message' => 'Password Salah'];
    }

    public function getUserData() {
        $sql = "SELECT user.id_user , password , status_aktif_trip FROM user
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
                'password' => $stmt['password'],
                'status_akun' => $stmt['status_aktif_trip'],
            ];
        }return;
    }

    public function cekEditUserPassword($id, $password) {
        $sql = "SELECT * FROM user
                WHERE id_user = '$id' AND password = '$password'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function editUser() {

        if (!empty($this->email) || !empty($this->no_telpon)) {
            if ($this->cekUserEmailTelpon($this->email, $this->no_telpon)) {
                return ['status' => 'Error', 'message' => 'Email atau Nomor Telepon Telah digunakan'];
            }
        }
        if (!empty($this->email)) {
            $this->editUserEmailSql();
        }
        if (!empty($this->no_telpon)) {
            $this->editUserNoTelponSql();
        }
        if (!empty($this->nama)) {
            $this->editUserNamaSql();
        }
        if (!empty($this->password)) {
            $this->editUserpasswordSql();
        }

        return ['status' => 'Success', 'message' => 'Profile Telah Diupdate'];
    }

    public function editUserNamaSql() {
        $nama = $this->nama;
        $id = $this->id_user;
        $sql = " UPDATE user
                 SET nama = '$nama'
                 WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function editUserEmailSql() {
        $email = $this->email;
        $id = $this->id_user;
        $sql = " UPDATE user
                 SET email = '$email'
                 WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function editUserNoTelponSql() {
        $no_telpon = $this->no_telpon;
        $id = $this->id_user;
        $sql = " UPDATE user
                 SET no_telpon = '$no_telpon'
                 WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
    }

    public function editUserPasswordSql() {
        $password = $this->password;
        $id = $this->id_user;
        $sql = " UPDATE user
                 SET password = '$password'
                 WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
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
                    $this->kode_referal = $this->kodeReferalGet(ID_PERUSAHAAN);
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

    public function cekReferalUser($referal_atasan){
        $getBawahan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getBawahan->setDb($this->db);
        $id_user_ref = $this->getIdUserReferal($referal_atasan);
        $data_referal = $getBawahan->getReferalDownSys($id_user_ref);
        if(empty($data_referal) || (count($data_referal) < MINIMAL_REFERAL)){
            return $referal_atasan;
        }
        return $this->kodeReferalGet($id_user_ref);
    }

    public function getIdUserReferal($kode_ref){
        $sql_ref_cek = "SELECT id_user AS id_atasan FROM kode_referal
                        WHERE kode_referal = '$kode_ref'";

        $est1 = $this->db->prepare($sql_ref_cek);
        $est1->execute();
        $stmt = $est1->fetch();
        return $stmt['id_atasan'];
    }

    public function kodeReferalGet($id) {
        $getBawahan = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        $getBawahan->setDb($this->db);
        $bawahPerusahaan = $getBawahan->getReferalDownSys($id);
        if(empty($bawahPerusahaan)){
            return KODE_REFERAL_DMS;
        }
        $tampungBawah[0] = $bawahPerusahaan;
        $state = true;
        $i = 0;
        while($state){
            $j = 0;
            $state2 = true;

            while($state2){
                if($tampungBawah[$i][$j]['id_user'] == ID_PERUSAHAAN){
                    if(empty($tampungBawah[$i][$j+1])){
                        $state2 = false;
                    }
                    $j++;
                    continue;
                }
                $temp = $getBawahan->getReferalDownSys($tampungBawah[$i][$j]['id_user']);
                
                if(empty($temp) || (count($temp) < MINIMAL_REFERAL)){
                    return $tampungBawah[$i][$j]['kode_referal'];
                }
                if(empty($tampungBawah[$i+1])){
                    $tampungBawah[$i+1] = [];
                    $tampungBawah[$i+1] = $temp;
                }else{
                    $tampungBawah[$i+1] = array_merge($tampungBawah[$i+1], $temp);
                }
                if(empty($tampungBawah[$i][$j+1])){
                    $state2 = false;
                }

                $j++;
            }

            if(empty($tampungBawah[$i+1])){
                $state = false;
            }

            $i++;
        }
        
    }

    public function konfirmasiSelesai($id_user) {
        $data = $this->cekStatusUser($id_user, STATUS_AKUN_AKTIF);
        if (empty($id_user) || empty($data)) {
            return ['status' => 'Error', 'message' => 'Konfirmasi Gagal'];
        }
        $this->setId_user($id_user);
        if ($data['status_aktif_trip'] != 3) {
            return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Akun, Akun Telah Aktif'];
        }
        if ($data['role'] == USER_ROLE) {
            //Token saldo point input
            if ($this->insertToken() && $this->insertSaldo() && $this->insertPoint() && $this->insertDetailProfile() && $this->gantiStatusAkun()) {
                return ['status' => 'Success', 'message' => 'Selamat Akun Mougo Anda Telah Aktif'];
            }
            return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Akun'];
        }
        if ($data['role'] == MERCHANT_ROLE) {
            //Token saldo point input
            if ($this->insertToken() && $this->insertSaldo() && $this->insertPoint() && $this->gantiStatusAkun()) {
                return ['status' => 'Success', 'message' => 'Selamat Akun Mougo Anda Telah Aktif'];
            }
            return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Akun'];
        }
        //Token saldo point input
        if ($this->insertToken() && $this->insertSaldo() && $this->insertPoint() && $this->insertPosition() && $this->insertDetailProfile() && $this->gantiStatusAkun()) {
            return ['status' => 'Success', 'message' => 'Selamat Akun Mougo Anda Telah Aktif'];
        }
        return ['status' => 'Error', 'message' => 'Gagal Mengaktifkan Akun'];
    }

    public function insertDetailProfile() {
        $sql = "INSERT INTO detail_user (id_user , no_ktp , provinsi , kota , bank, no_rekening, atas_nama_bank , foto_ktp , foto_kk )
                VALUES (:id_user , :no_ktp , :provinsi , :kota , :bank, :no_rekening, :atas_nama_bank , :foto_ktp , :foto_kk )";
        $data = [
            ':id_user' => $this->getId_user(),
            ':no_ktp' => "-",
            ':provinsi' => "-",
            ':kota' => "-",
            ':bank' => 0,
            ':no_rekening' => "-",
            ':atas_nama_bank' => "-",
            ':foto_ktp' => "-",
            ':foto_kk' => "-",
        ];
        $estimate = $this->db->prepare($sql);
        return $estimate->execute($data);

    }

    public function cekStatusUser($id) {
        $sql = "SELECT * FROM user
                WHERE id_user = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        return $est->fetch();
    }

    public function gantiStatusAkun() {
        $sql = "UPDATE user
                SET status_aktif_trip = :status_aktif_trip
                WHERE id_user = :id_user";
        $data = [
            ':status_aktif_trip' => STATUS_AKTIF_USER,
            ':id_user' => $this->getId_user(),
        ];
        $est = $this->getDb()->prepare($sql);
        return $est->execute($data);

    }

    public function emailKonfirmasi($email, $nama, $role) {
        $email = decrypt($email, MOUGO_CRYPTO_KEY);
        $nama = decrypt($nama, MOUGO_CRYPTO_KEY);
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        // $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = "tls";
        $mail->SMTPAuth = true;
        $mail->Username = "mougo.noreply@gmail.com";
        $mail->Password = "mougodms1@!?";

        $mail->setFrom('mougo.noreply@gmail.com', 'MOUGO DMS');
        $mail->addAddress($email, $nama);
        $mail->isHTML(true);
        $mail->Subject = "MOUGO DMS Register Akun";
        if ($role == USER_ROLE) {
            $mail->Body = "Hello " . $nama . " \n Berikut Adalah Link Konfirmasi Register Akun MOUGO Anda " . $this->getWeb_url() . "/mougo/customerRegister/" . $this->id_user;
        }
        if ($role == DRIVER_ROLE) {
            $mail->Body = "Hello " . $nama . " \n Berikut Adalah Link Konfirmasi Register Akun MOUGO Driver Anda " . $this->getWeb_url() . "/mougo/driverRegister/" . $this->id_user;
        }
        if ($role == MERCHANT_ROLE) {
            $mail->Body = "Hello " . $nama . " \n Selamat Anda Telah Melakukan Registrasi Akun Merchant MOUGO." ;
        }
        return $mail->send();

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

    public function insertTanggalPendaftaran() {
        $sql = "INSERT INTO tanggal_pendaftaran (id_user)
                        VALUES (:id_user)";
        $data_user = [
            ':id_user' => $this->id_user,
        ];

        $este = $this->db->prepare($sql);
        return $este->execute($data_user);
    }

    public function getTanggalPendaftaran($id_user) {
        $sql = "SELECT tanggal_pendaftaran.tanggal_pendaftaran FROM tanggal_pendaftaran 
                WHERE id_user = '$id_user'";

        $este = $this->db->prepare($sql);
        $este->execute();
        return $este->fetch();
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
        $sql = "SELECT user.id_user , token , password  FROM user
                        INNER JOIN api_token ON api_token.id_user = user.id_user
                        WHERE email = :email OR no_telpon = :email ";
        $data_token = [
            ":email" => (!empty($this->email)) ? $this->email : $this->no_telpon,
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
        }
    }

}
class SendEmail extends Thread{  

    private $email;
    private $nama;
    private $role;
    private $web_url;
    private $id_user;

    function __construct($email, $nama, $role, $web_url, $id_user) {
        $this->email = $email;
        $this->nama = $nama;
        $this->role = $role;
        $this->web_url = $web_url;
        $this->id_user = $id_user;
    }

    public function run() {
        $email = decrypt($this->email, MOUGO_CRYPTO_KEY);
        $nama = decrypt($this->nama, MOUGO_CRYPTO_KEY);
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        // $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = "tls";
        $mail->SMTPAuth = true;
        $mail->Username = "mougo.noreply@gmail.com";
        $mail->Password = "mougodms1@!?";

        $mail->setFrom('mougo.noreply@gmail.com', 'MOUGO DMS');
        $mail->addAddress($email, $nama);
        $mail->isHTML(true);
        $mail->Subject = "MOUGO DMS Register Akun";
        if ($this->role == USER_ROLE) {
            $mail->Body = "Hello " . $nama . " \n Berikut Adalah Link Konfirmasi Register Akun MOUGO Anda " . $this->web_url . "/mougo/customerRegister/" . $this->id_user;
        }
        if ($this->role == DRIVER_ROLE) {
            $mail->Body = "Hello " . $nama . " \n Berikut Adalah Link Konfirmasi Register Akun MOUGO Driver Anda " . $this->web_url . "/mougo/driverRegister/" . $this->id_user;
        }
        if ($this->role == MERCHANT_ROLE) {
            $mail->Body = "Hello " . $nama . " \n Selamat Anda Telah Melakukan Registrasi Akun Merchant MOUGO." ;
        }
        return $mail->send();

    }
}
