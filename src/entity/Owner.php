<?php

class Owner {
    private $email_owner;
    private $password;
    private $db;

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function __construct($email, $password) {
        $this->email_owner = $email;
        $this->password = $password;
    }

    public function loginOwner() {
        if (!$this->isValid(LOGIN)) {
            return ['status' => 'Error', 'message' => 'Email atau Password Harus Di isi'];
        }
        $data_owner = $this->cekDataOwner($this->email_owner);
        if (empty($data_owner)) {
            return ['status' => 'Error', 'message' => 'Email atau Password salah'];
        }
        if ($data_owner['password'] == $this->password) {
            return ['status' => 'Success', 'data' => $data_owner['email_owner']];
        }
        return ['status' => 'Error', 'message' => 'Email atau Password Salah'];

    }

    public function isValid($status) {
        switch ($status) {
            case LOGIN:
                if (empty($this->email_owner) || empty($this->password)) {
                    return false;
                }return true;
        }
    }

    public function cekDataOwner($email) {
        $sql = "SELECT * FROM owner
                WHERE email_owner LIKE '$email'  ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
    }

    public function inputEvent($judul, $deskripsi, $gambar) {
        $sql = "INSERT INTO event (judul_event,deskripsi_event,gambar_event)
                VALUE('$judul','$deskripsi','$gambar')";
        $est = $this->db->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Event Berhasil Dipublikasi'];
        }return ['status' => 'Error', 'message' => 'Event Gagal Diupload'];
    }

    public function getEvent(){
        $sql = "SELECT * FROM event ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
        
    }

    public function getEventCommon(){
        $sql = "SELECT * FROM event 
                ORDER BY tanggal_event DESC
                LIMIT 5";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
        
    }

}
