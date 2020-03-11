<?php
require_once(dirname(__FILE__).'/Driver.php');

class User extends Driver{
    private $nama;
    private $email;
    private $no_telpon;
    private $password;
    private $kode_referal;
    private $kode_sponsor;
    private $db;

    public function __construct($nama,$email,$no_telpon,$password,$kode_referal,$kode_sponsor) {
        $this->nama = $nama;
        $this->email = $email;
        $this->no_telpon = $no_telpon;
        $this->password = $password;
        $this->kode_referal = $kode_referal;
        $this->kode_sponsor = $kode_sponsor;
    }

    public function setDb($db){
        $this->db = $db;
    }

    public function register($role){
        $register = "register";
        if(!$this->isDataValid($register)){
            return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
        }
    
        $status_aktif_user = 2;//unactive
        $user_email = $this->email;
        $user_no_tlp =  $this->no_telpon;
        //cek email dan no_telpon
        $sql_cek_tlp = "SELECT * FROM user
                WHERE email LIKE '$user_email' OR no_telpon LIKE '$user_no_tlp'";

        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute();
        $stmtcek = $estcek->fetch();
        $numrowcek = $estcek->rowCount();

        if ($numrowcek > 0) {
            return ['status' => 'Error', 'message' => 'Email / Nomor Telpon Telah digunakan'];
        }

        //User Input
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

        //Driver
        if($role == 2){
            $sql_driver = "INSERT INTO driver (id_user , status_online , no_polisi , cabang , alamat_domisili , merk_kendaraan , jenis_kendaraan , status_akun_aktif)
                                VALUES (:id_user , :status_online , :no_polisi , :cabang , :alamat_domisili , :merk_kendaraan , :jenis_kendaraan , :status_akun_aktif)";
            $data_driver = [
                ':id_user' => $id_user,
                ':status_online' => 0,
                ':no_polisi' => $this->getNo_polisi(),
                ':cabang' => $this->getCabang(),
                ':alamat_domisili' => $this->getAlamat_domisili(),
                ':merk_kendaraan' => $this->getMerk_kendaraan(),
                ':jenis_kendaraan' => $this->getJenis_kendaraan(),
                ':status_akun_aktif' => 0,
            ];
            $drive = "role2";
            $est_d = $this->db->prepare($sql_driver);
            if(!$this->isDataValid($drive)){
                return ['status' => 'Error', 'message' => 'Data Input Tidak Boleh Kosong'];
            }
        }
        //Kode Referal dan sponsor regenerate
        $states = true;
        while ($states) {
            $kode_ref = 'R' . randomLett() . randomNum();
            $kode_sp = 'S' . substr($kode_ref, 1);

            $sqlcek = "SELECT id_user FROM kode_referal
                            WHERE kode_referal LIKE '$kode_ref'";

            $estcek = $this->db->prepare($sqlcek);
            $estcek->execute();
            $stmtcek = $estcek->fetch();
            $numrowcek = $estcek->rowCount();

            if ($numrowcek > 0) {
                $states = true;
            } else {
                $states = false;
            }
        }

        //CEK Kode Referal dan Sponsor Atasan

        $sql_ref_cek = "SELECT id_user AS id_atasan FROM kode_referal
                        WHERE kode_referal = '$this->kode_referal'";

        $est1 = $this->db->prepare($sql_ref_cek);
        $est1->execute();
        $stmt = $est1->fetch();
        $numrow = $est1->rowCount();

        $sql_sp_cek = "SELECT id_user AS id_atasan FROM kode_sponsor
                        WHERE kode_sponsor = '$this->kode_sponsor'";

        $est2 = $this->db->prepare($sql_sp_cek);
        $est2->execute();
        $stmt2 = $est2->fetch();
        $numrow2 = $est2->rowCount();

        if ($numrow > 0 && $numrow2 > 0) {
            //Input Kode Referal dan Sponsor Atasan
            $sql_ref = "INSERT INTO kode_referal(id_user,kode_referal,id_user_atasan)
                    VALUES('$id_user','$kode_ref',:id_user_atasan)";
            $data_ref = [
                ':id_user_atasan' => $stmt['id_atasan'],
            ];
            $estim = $this->db->prepare($sql_ref);

            $sql_sp = "INSERT INTO kode_sponsor(id_user,kode_sponsor,id_user_atasan)
                    VALUES('$id_user','$kode_sp',:id_user_atasan)";
            $data_sp = [
                ':id_user_atasan' => $stmt['id_atasan'],
            ];
            $estim2 = $this->db->prepare($sql_sp);

            if ($este->execute($data_user) && $role==2?$est_d->execute($data_driver):TRUE && $estim->execute($data_ref) && $estim2->execute($data_sp)) {

                //Token input
                $sql_token = "INSERT INTO api_token (id_user , token , hits )
                            VALUES (:id_user , :token , :hits)";
                $data = [
                    ':id_user' => $id_user,
                    ':token' => sha1(rand()),
                    ':hits' => 0,
                ];

                $estimat = $this->db->prepare($sql_token);
                $estimat->execute($data);

                //saldo dan point input
                $sql_saldo = "INSERT INTO saldo (id_user , jumlah_saldo )
                            VALUES ('$id_user' , :jumlah_saldo )";
                $data_saldo = [
                    ':jumlah_saldo' => 0,
                ];
                $estimate = $this->db->prepare($sql_saldo);
                $estimate->execute($data_saldo);

                $sql_point = "INSERT INTO point (id_user , jumlah_point )
                            VALUES ('$id_user' , :jumlah_point )";
                $data_point = [
                    ':jumlah_point' => 0,
                ];
                $estimated = $this->db->prepare($sql_point);
                $estimated->execute($data_point);

                return ['status' => 'Success', 'message' => 'Pendaftaran Sukses'];
            }return ['status' => 'Error', 'message' => 'Input Bermasalah'];

        }return ['status' => 'Error', 'message' => 'Referal Atasan Tidak Ditemukan'];


    }
    
    public function login($role){
        $login = "login";
        if(!$this->isDataValid($login)){
            return ['status' => 'Error', 'message' => 'Akun Tidak Ditemukan'];
        }
        //check role
        $sql_cek_tlp = "SELECT * FROM user
            WHERE email LIKE :email AND role = $role OR no_telpon LIKE :email AND role = $role ";
        $data_user = [
            ":email" => (!empty($this->email))?$this->email:$this->no_telpon,
        ];
        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute($data_user);
        $stmtcek = $estcek->fetch();

        if (empty($stmtcek)) {
            return['status' => 'Error', 'message' => 'Akun Tidak Ditemukan'];
        }
        
        $sql = "SELECT user.id_user , token , password FROM user
                        INNER JOIN api_token ON api_token.id_user = user.id_user
                        WHERE email = :email AND password = :pass OR no_telpon = :email AND password = :pass";
        $data_token = [
            ":email" => (!empty($this->email))?$this->email:$this->no_telpon,
            ":pass" => $this->password,
        ];
        $est = $this->db->prepare($sql);
        $est->execute($data_token);
        $stmt = $est->fetch();

        $res = [
            'id_user' => $stmt['id_user'],
            'token' => $stmt['token'],
        ];

        if(empty($stmt)){
            return ['status' => 'Error', 'message' => 'Akun Tidak Ditemukan'];
        }
        if ($stmt['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $res];
        }
        return ['status' => 'Error', 'message' => 'Password Salah'];   
    }

    private function isDataValid($type){
        switch ($type) {
            case 'login':
                $isValid = TRUE;
                if(empty($this->email)||empty($this->no_telpon)){
                    $isValid = FALSE;
                }
                if(empty($this->password)){
                    $isValid = FALSE;
                }
                return $isValid;
                break;
            
            case 'register':
                 $isValid = TRUE;
                if(empty($this->kode_referal)){
                    $this->kode_referal = "RAAA000";
                }
                if(empty($this->kode_sponsor)){
                    $this->kode_sponsor = "SAAA000";
                }
                if(empty($this->email)||empty($this->no_telpon)||empty($this->password)||empty($this->nama)){
                    $isValid = FALSE;
                }
                return $isValid;
                break;

            case 'role2':
                 $isValid = TRUE;
                if(empty($this->getNo_polisi())||empty($this->getCabang())||empty($this->getAlamat_domisili())||empty($this->getMerk_kendaraan())||empty($this->getJenis_kendaraan())){
                    $isValid = FALSE;
                }
                return $isValid;
                break;    
            default:
                # code...
                break;
        }
        
    }


}


