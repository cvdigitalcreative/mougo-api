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

    public function inputEvent($judul, $deskripsi, $gambar, $tanggal) {
        $sql = "INSERT INTO event (judul_event,deskripsi_event,gambar_event,tanggal_event)
                VALUE('$judul','$deskripsi','$gambar','$tanggal')";
        $est = $this->db->prepare($sql);
        if ($est->execute()) {
            return ['status' => 'Success', 'message' => 'Event Berhasil Dipublikasi'];
        }return ['status' => 'Error', 'message' => 'Event Gagal Diupload'];
    }

    public function editEvent($id,$judul, $deskripsi, $gambar, $tanggal) {
        $sql = "UPDATE event 
                SET judul_event = '$judul', deskripsi_event = '$deskripsi', tanggal_event = '$tanggal'";
        if(!empty($gambar)){
            $sql = $sql.", gambar_event = '$gambar' ";
        }
        $sql = $sql . " WHERE id = '$id'";
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

    public function cekEvent($id){
        $sql = "SELECT * FROM event 
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetch();
        return $temp;
        
    }

    public function deleteEvent($id){
        $sql = "DELETE FROM event 
                WHERE id = '$id'";
        $est = $this->getDb()->prepare($sql);
        return $est->execute();
        
    }

    public function getAdmin(){
        $sql = "SELECT email_admin, nama_admin, no_telpon FROM admin ";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
        
    }

    public function getTrip(){
        $sql = "SELECT driver.nama AS nama_driver, customer.nama AS nama_customer, alamat_jemput, alamat_destinasi, total_harga, tanggal_transaksi FROM trip
                INNER JOIN user AS customer ON customer.id_user = trip.id_customer
                INNER JOIN user AS driver ON driver.id_user = trip.id_driver
                ORDER BY tanggal_transaksi DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getTripAll(){
        $trip = $this->getTrip();
        if(empty($trip)){
            return ['status' => 'Error' , 'message' => 'Trip Tidak Ditemukan'];
        }
        $dataCustomer = [];
        for ($i=0; $i < count($trip); $i++) { 
            $dataCustomer[$i]['nama_driver'] = decrypt($trip[$i]['nama_driver'],MOUGO_CRYPTO_KEY);
            $dataCustomer[$i]['nama_customer'] = decrypt($trip[$i]['nama_customer'],MOUGO_CRYPTO_KEY);
            $dataCustomer[$i]['alamat_jemput'] = $trip[$i]['alamat_jemput'];
            $dataCustomer[$i]['alamat_destinasi'] = $trip[$i]['alamat_destinasi'];
            $dataCustomer[$i]['total_harga'] =(double) $trip[$i]['total_harga']; 
            $dataCustomer[$i]['tanggal_trip'] = $trip[$i]['tanggal_transaksi'];
        }
        return ['status' => 'Success' , 'data' => $dataCustomer ];
    }

    public function getBonusLevel(){
        $sql = "SELECT * FROM bonus_level
                INNER JOIN user ON user.id_user = bonus_level.id_user
                ORDER BY tanggal_pendapatan DESC";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $temp = $est->fetchAll();
        return $temp;
    }

    public function getBonusLevelAll(){
        $Bonus = $this->getBonusLevel();
        if(empty($Bonus)){
            return ['status' => 'Error' , 'message' => 'Bonus Level Tidak Ditemukan'];
        }
        $dataCustomer = [];
        for ($i=0; $i < count($Bonus); $i++) { 
            $dataCustomer[$i]['nama'] = decrypt($Bonus[$i]['nama'],MOUGO_CRYPTO_KEY);
            $dataCustomer[$i]['pendapatan'] =(double) $Bonus[$i]['pendapatan']; 
            $dataCustomer[$i]['tanggal_pendapatan'] = $Bonus[$i]['tanggal_pendapatan'];
        }
        return ['status' => 'Success' , 'data' => $dataCustomer ];
    }

}
