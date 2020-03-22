<?php

require_once dirname(__FILE__) . '/Umum.php';

class Trip {
    private $id_customer;
    private $id_driver;
    private $total_harga;
    private $alamat_jemput;
    private $latitude_jemput;
    private $longitude_jemput;
    private $alamat_destinasi;
    private $latitude_destinasi;
    private $longitude_destinasi;
    private $jarak;
    private $tanggal_transaksi;
    private $jenis_trip;
    private $status_trip;
    private $jenis_pembayaran;
    private $db;

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function __construct($id_customer, $id_driver, $total_harga, $alamat_jemput, $latitude_jemput, $longitude_jemput, $alamat_destinasi, $latitude_destinasi, $longitude_destinasi, $jarak, $tanggal_transaksi, $jenis_trip, $status_trip, $jenis_pembayaran) {
        $this->id_customer = $id_customer;
        $this->id_driver = $id_driver;
        $this->total_harga = $total_harga;
        $this->alamat_jemput = $alamat_jemput;
        $this->latitude_jemput = $latitude_jemput;
        $this->longitude_jemput = $longitude_jemput;
        $this->alamat_destinasi = $alamat_destinasi;
        $this->latitude_destinasi = $latitude_destinasi;
        $this->longitude_destinasi = $longitude_destinasi;
        $this->jarak = $jarak;
        $this->tanggal_transaksi = $tanggal_transaksi;
        $this->jenis_trip = $jenis_trip;
        $this->status_trip = $status_trip;
        $this->jenis_pembayaran = $jenis_pembayaran;
    }

    public function order_trip() {
        if (!$this->isDataValid()) {
            return ['status' => 'Error', 'message' => 'Order Trip Data Input Tidak Boleh Kosong'];
        }
        // $this->hitungHarga();
        $data['id_trip'] = $this->inputTemporaryOrder();
        if (!$data['id_trip']) {
            return ['status' => 'Error', 'message' => 'Pemesanan Error'];
        }
        $data['status_trip'] = $this->status_trip;
        return ['status' => 'Success', 'data' => $data];

    }

    private function isDataValid() {
        $isValid = true;
        if (empty($this->id_customer) || empty($this->total_harga) || empty($this->alamat_jemput) || empty($this->latitude_jemput) || empty($this->longitude_jemput) || empty($this->alamat_destinasi) || empty($this->latitude_destinasi) || empty($this->longitude_destinasi) || empty($this->jarak) || empty($this->jenis_trip) || empty($this->jenis_pembayaran)) {
            $isValid = false;
        }
        return $isValid;

    }

    public function inputTemporaryOrder() {
        $sql = "INSERT INTO temporary_order (id_customer,total_harga,alamat_jemput,latitude_jemput,longitude_jemput,alamat_destinasi,latitude_destinasi,longitude_destinasi,jarak,jenis_trip,status_trip,jenis_pembayaran)
            VALUES(:id_customer,:total_harga,:alamat_jemput,:latitude_jemput,:longitude_jemput,:alamat_destinasi,:latitude_destinasi,:longitude_destinasi,:jarak,:jenis_trip,:status_trip,:jenis_pembayaran)";
        $data = [
            ':id_customer' => $this->id_customer,
            ':total_harga' => $this->total_harga,
            ':alamat_jemput' => $this->alamat_jemput,
            ':latitude_jemput' => $this->latitude_jemput,
            ':longitude_jemput' => $this->longitude_jemput,
            ':alamat_destinasi' => $this->alamat_destinasi,
            ':latitude_destinasi' => $this->latitude_destinasi,
            ':longitude_destinasi' => $this->longitude_destinasi,
            ':jarak' => $this->jarak,
            ':jenis_trip' => $this->jenis_trip,
            ':status_trip' => $this->status_trip,
            ':jenis_pembayaran' => $this->jenis_pembayaran,
        ];
        $est = $this->getDb()->prepare($sql);
        if ($est->execute($data)) {
            return $this->getDb()->lastInsertId();
        }return false;

    }

    public function driverInputOrder($id_driver, $data_trip ,$status_trip) {
        $sql = "INSERT INTO trip (id_customer,id_driver,total_harga,alamat_jemput,latitude_jemput,longitude_jemput,alamat_destinasi,latitude_destinasi,longitude_destinasi,jarak,jenis_trip,status_trip,jenis_pembayaran)
        VALUES(:id_customer,:id_driver,:total_harga,:alamat_jemput,:latitude_jemput,:longitude_jemput,:alamat_destinasi,:latitude_destinasi,:longitude_destinasi,:jarak,:jenis_trip,:status_trip,:jenis_pembayaran)";
        $data = [
            ':id_customer' => $data_trip['id_customer'],
            ':id_driver' => $id_driver,
            ':total_harga' => $data_trip['total_harga'],
            ':alamat_jemput' => $data_trip['alamat_jemput'],
            ':latitude_jemput' => $data_trip['latitude_jemput'],
            ':longitude_jemput' => $data_trip['longitude_jemput'],
            ':alamat_destinasi' => $data_trip['alamat_destinasi'],
            ':latitude_destinasi' => $data_trip['latitude_destinasi'],
            ':longitude_destinasi' => $data_trip['longitude_destinasi'],
            ':jarak' => $data_trip['jarak'],
            ':jenis_trip' => $data_trip['jenis_trip'],
            ':status_trip' => $status_trip,
            ':jenis_pembayaran' => $data_trip['jenis_pembayaran'],
        ];
        $est = $this->getDb()->prepare($sql);
        if ($est->execute($data)) {
            return $data_trip;
        }return false;
    }

    public function cancelOrder($id_trip) {
        $data_order = $this->getTemporaryOrderDetail($id_trip);
        if (empty($data_order)) {
            $cancelOrder = new Umum();
            $cancelOrder->setDb($this->db);
            return $cancelOrder->updateStatusTrip($id_trip,STATUS_CANCEL);
        }
        $this->deleteTemporaryOrderDetail($id_trip);
        $cek = $this->driverInputOrder(ID_DRIVER_SILUMAN,$data_order,STATUS_CANCEL);
        if(empty($cek)){
            return ['status' => 'Error', 'message' => 'Trip Tidak Ada Atau Telah Dibatalkan'];
        }
        return ['status' => 'Success', 'message' => 'Trip Telah Dibatalkan'];

    }

    public function deleteTemporaryOrderDetail($id) {
        $sql = "DELETE FROM temporary_order
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        if ($est->execute()) {
            return true;
        }return false;
    }

    public function getTripDetail($id) {
        $sql = "SELECT * FROM trip
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return $stmt;
        }return $stmt;
    }

    public function getTemporaryOrderDetail($id) {
        $sql = "SELECT * FROM temporary_order
                WHERE id_trip = '$id'";
        $est = $this->getDb()->prepare($sql);
        $est->execute();
        $stmt = $est->fetch();
        if (!empty($stmt)) {
            return $stmt;
        }return $stmt;
    }

}
