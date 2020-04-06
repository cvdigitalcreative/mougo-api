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
        if(empty($data_admin)){
        return ['status' => 'Error', 'message' => 'Email atau Password salah'];
        }
        if ($data_admin['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $data_admin['email_admin']];
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
}
