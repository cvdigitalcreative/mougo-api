<?php
require_once dirname(__FILE__) . '/../entity/Admin.php';
require_once dirname(__FILE__) . '/../entity/Owner.php';

// REGISTER ADMIN
$app->post('/admin/register/', function ($request, $response) {
    $data_admin = $request->getParsedBody();
    $admin = new Admin($data_admin['email'], $data_admin['nama'], $data_admin['password'], $data_admin['no_telpon']);
    $admin->setDb($this->db);
    return $response->withJson($admin->registerAdmin(), SERVER_OK);
});

// OWNER LOGIN
$app->post('/owner/login/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner($data['email'], $data['password']);
    $owner->setDb($this->db);
    return $response->withJson($owner->loginOwner(), SERVER_OK);
});

// OWNER EVENT
$app->post('/owner/event/', function ($request, $response) {
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    $event = $owner->getEvent();
    if (count($event) >= EVENT_MAKSIMAL) {
        return $response->withJson(['status' => 'Error', 'message' => 'Gagal Upload, Event Telah Penuh'], SERVER_OK);
    }
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($uploadedFiles['gambar']->file) || empty($data['judul']) || empty($data['deskripsi']) || empty($data['tanggal_event'])) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    $uploadedFile = $uploadedFiles['gambar'];

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
            return $response->withJson(['status' => 'Error', 'message' => 'Gambar Event Harus JPG atau PNG'], SERVER_OK);
        }
        $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
        $directory = $this->get('settings')['upload_directory2'];
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        $path_name = "../assets-event/" . $filename;

    }return $response->withJson($owner->inputEvent($data['judul'], $data['deskripsi'], $path_name, $data['tanggal_event']), SERVER_OK);

});

// OWNER
// GET Event
$app->get('/owner/event/', function ($request, $response, $args) {
    $getevent = new Owner(null, null);
    $getevent->setDb($this->db);
    $event = $getevent->getEvent();
    if (empty($event)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }

    foreach ($event as $index => $value) {
        $tanggal = $value['tanggal_event'];
        $timestamp = strtotime($tanggal);
        $timestamp = date("d-m-Y", $timestamp);
        $event[$index]['tanggal_event'] = $timestamp;
    }
    return $response->withJson(['status' => 'Success', 'data' => $event], SERVER_OK);
});

// OWNER
// GET Driver
$app->get('/owner/driver/', function ($request, $response, $args) {
    $getdriver = new Umum();
    $getdriver->setDb($this->db);
    $driver = $getdriver->getAllDriver();
    if (empty($driver)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }
    $dataDriver = [];
    for ($i = 0; $i < count($driver); $i++) {
        $dataDriver[$i]['id_user'] = $driver[$i]['id_user'];
        $dataDriver[$i]['nama'] = decrypt($driver[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['email'] = decrypt($driver[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_telpon'] = decrypt($driver[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_polisi'] = decrypt($driver[$i]['no_polisi'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['cabang'] = $driver[$i]['cabang'];
        $dataDriver[$i]['jenis_kendaraan'] = $driver[$i]['jenis_kendaraan'];
        $dataDriver[$i]['merk_kendaraan'] = $driver[$i]['merk_kendaraan'];
        $dataDriver[$i]['foto_ktp'] = $driver[$i]['foto_ktp'];
        $dataDriver[$i]['foto_kk'] = $driver[$i]['foto_kk'];
        $dataDriver[$i]['foto_sim'] = $driver[$i]['foto_sim'];
        $dataDriver[$i]['foto_skck'] = $driver[$i]['foto_skck'];
        $dataDriver[$i]['foto_stnk'] = $driver[$i]['foto_stnk'];
        $dataDriver[$i]['foto_diri'] = $driver[$i]['foto_diri'];
        $dataDriver[$i]['no_rekening'] = $driver[$i]['no_rekening'];
        $dataDriver[$i]['nama_bank'] = $driver[$i]['name'];
    }
    return $response->withJson(['status' => 'Success', 'data' => $dataDriver], SERVER_OK);
});

// OWNER
// GET Customer
$app->get('/owner/customer/', function ($request, $response) {
    $getcustomer = new Umum();
    $getcustomer->setDb($this->db);
    $customer = $getcustomer->getAllCustomer();
    if (empty($customer)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Customer Tidak Ditemukan'], SERVER_OK);
    }
    $dataCustomer = [];
    for ($i = 0; $i < count($customer); $i++) {
        $dataCustomer[$i]['id_user'] = $customer[$i]['id_user'];
        $dataCustomer[$i]['nama'] = decrypt($customer[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataCustomer[$i]['email'] = decrypt($customer[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataCustomer[$i]['no_telpon'] = decrypt($customer[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataCustomer[$i]['provinsi'] = $customer[$i]['provinsi'];
        $dataCustomer[$i]['kota'] = $customer[$i]['kota'];
        $dataCustomer[$i]['kode_referal'] = $customer[$i]['kode_referal'];
        $dataCustomer[$i]['kode_sponsor'] = $customer[$i]['kode_sponsor'];
        $dataCustomer[$i]['no_rekening'] = $customer[$i]['no_rekening'];
        $dataCustomer[$i]['nama_bank'] = $customer[$i]['name'];
    }
    return $response->withJson(['status' => 'Success', 'data' => $dataCustomer], SERVER_OK);
});

// OWNER
// GET Admin
$app->get('/owner/admin/', function ($request, $response, $args) {
    $getadmin = new Owner(null, null);
    $getadmin->setDb($this->db);
    $admin = $getadmin->getAdmin();
    if (empty($admin)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Admin Tidak Ditemukan'], SERVER_OK);
    }

    return $response->withJson(['status' => 'Success', 'data' => $admin], SERVER_OK);
});

// OWNER
// DELETE Admin
$app->delete('/owner/admin/{admin}', function ($request, $response, $args) {
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson($owner->deleteAdminOwner($args['admin']), SERVER_OK);
});

// OWNER
// Edit Admin
$app->put('/owner/admin/{admin}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson($owner->updateAdminOwner($args['admin'], $data['nama'], $data['password'], $data['no_telpon']), SERVER_OK);
});

// OWNER
// DELETE Event
$app->delete('/owner/event/{id}', function ($request, $response, $args) {
    $getevent = new Owner(null, null);
    $getevent->setDb($this->db);
    $event = $getevent->cekEvent($args['id']);
    if (empty($event)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }

    if ($getevent->deleteEvent($args['id'])) {
        return $response->withJson(['status' => 'Success', 'message' => 'Event Berhasil Dihapus'], SERVER_OK);

    }
    return $response->withJson(['status' => 'Error', 'message' => 'Event Gagal Dihapus'], SERVER_OK);
});

// OWNER
// EDIT Event
$app->post('/owner/event/{id}', function ($request, $response, $args) {
    $getevent = new Owner(null, null);
    $getevent->setDb($this->db);
    $event = $getevent->cekEvent($args['id']);
    if (empty($event)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }

    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    if (empty($data['judul']) || empty($data['deskripsi']) || empty($data['tanggal_event'])) {
        return $response->withJson(['status' => 'Error', 'message' => 'Input Tidak Boleh Kosong'], SERVER_OK);
    }
    if (empty($uploadedFiles['gambar']->file)) {
        return $response->withJson($getevent->editEvent($args['id'], $data['judul'], $data['deskripsi'], null, $data['tanggal_event']), SERVER_OK);
    }

    $uploadedFile = $uploadedFiles['gambar'];

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if ($extension != "jpg" && $extension != "png" && $extension != "JPG" && $extension != "PNG" && $extension != "jpeg" && $extension != "JPEG") {
            return $response->withJson(['status' => 'Error', 'message' => 'Gambar Event Harus JPG atau PNG'], SERVER_OK);
        }
        if (unlink($event['gambar_event'])) {
            $filename = md5($uploadedFile->getClientFilename()) . time() . "." . $extension;
            $directory = $this->get('settings')['upload_directory2'];
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
            $path_name = "../assets-event/" . $filename;
        }

    }return $response->withJson($getevent->editEvent($args['id'], $data['judul'], $data['deskripsi'], $path_name, $data['tanggal_event']), SERVER_OK);

});

// OWNER
// GET TRIP
$app->get('/owner/trip/', function ($request, $response) {
    $getTrip = new Owner(null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getTripAll(), SERVER_OK);
});

// OWNER
// GET BONUS LEVEL
$app->get('/owner/bonus/level/', function ($request, $response) {
    $getBonus = new Owner(null, null);
    $getBonus->setDb($this->db);
    return $response->withJson($getBonus->getBonusLevelAll(), SERVER_OK);
});

// OWNER
// GET BONUS TRIP
$app->get('/owner/bonus/trip/', function ($request, $response) {
    $getBonus = new Owner(null, null);
    $getBonus->setDb($this->db);
    return $response->withJson($getBonus->getBonusTripAll(), SERVER_OK);
});

// OWNER
// GET BONUS LEVEL
$app->get('/owner/bonus/transfer/', function ($request, $response) {
    $getBonus = new Owner(null, null);
    $getBonus->setDb($this->db);
    return $response->withJson($getBonus->getBonusTransferAll(), SERVER_OK);
});

// OWNER
// GET WITHDRAW
$app->get('/owner/withdraw/', function ($request, $response) {
    $getTrip = new Owner(null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getWithdrawAll(), SERVER_OK);
});

// OWNER Accept withdraw Transfer
$app->put('/owner/withdraw/accept/{id}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->adminKonfirmasiWithdraw($args['id'], STATUS_WITHDRAW_SUCCESS), SERVER_OK);
});

// OWNER Reject withdraw Transfer
$app->put('/owner/withdraw/reject/{id}', function ($request, $response, $args) {
    $admin = new Umum();
    $admin->setDb($this->db);
    return $response->withJson($admin->adminKonfirmasiWithdraw($args['id'], STATUS_WITHDRAW_REJECT), SERVER_OK);
});

// OWNER
// GET TOPUP
$app->get('/owner/topup/', function ($request, $response) {
    $getTrip = new Owner(null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getTopupAll(), SERVER_OK);
});

// OWNER
// GET TOPUP DRIVER
$app->get('/owner/topup/driver/', function ($request, $response) {
    $getTrip = new Owner(null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getTopupDriverAll(), SERVER_OK);
});

// OWNER
// GET BANTUAN
$app->get('/owner/bantuan/', function ($request, $response) {
    $getTrip = new Owner(null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getBantuanAll(), SERVER_OK);
});

// OWNER
// GET TRANSFER
$app->get('/owner/transfer/', function ($request, $response) {
    $getTrip = new Owner(null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getTransferAll(), SERVER_OK);
});

// OWNER
// GET STRUKTUR LEVEL
$app->get('/owner/struktur/level/{id_user}', function ($request, $response, $args) {
    $getTrip = new Trip(null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    $getTrip->setDb($this->db);
    return $response->withJson($getTrip->getAllReferalBawahan($args['id_user']), SERVER_OK);
});

// OWNER
// GET USER
$app->get('/owner/user/', function ($request, $response) {
    $getUser = new Umum();
    $getUser->setDb($this->db);
    $user = $getUser->getAllUser();
    if (empty($user)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Customer Tidak Ditemukan'], SERVER_OK);
    }
    $dataUser = [];
    for ($i = 0; $i < count($user); $i++) {
        $dataUser[$i]['id_user'] = $user[$i]['id_user'];
        $dataUser[$i]['nama'] = decrypt($user[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataUser[$i]['email'] = decrypt($user[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataUser[$i]['no_telpon'] = decrypt($user[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataUser[$i]['provinsi'] = $user[$i]['provinsi'];
        $dataUser[$i]['kota'] = $user[$i]['kota'];
        $dataUser[$i]['kode_referal'] = $user[$i]['kode_referal'];
        $dataUser[$i]['kode_sponsor'] = $user[$i]['kode_sponsor'];
        $dataUser[$i]['no_rekening'] = $user[$i]['no_rekening'];
        $dataUser[$i]['nama_bank'] = $user[$i]['name'];
    }
    return $response->withJson(['status' => 'Success', 'data' => $dataUser], SERVER_OK);
});

// INSERT CABANG
$app->post('/owner/cabang/', function ($request, $response) {
    $data = $request->getParsedBody();
    $dataOwner = new Owner(null, null);
    $dataOwner->setDb($this->db);
    return $response->withJson($dataOwner->ownerInsertCabang($data['cabang']), SERVER_OK);
});

// GET CABANG
$app->get('/owner/cabang/', function ($request, $response) {
    $dataOwner = new Owner(null, null);
    $dataOwner->setDb($this->db);
    return $response->withJson($dataOwner->getCabangAllOwner(), SERVER_OK);
});

// UPDATE CABANG
$app->put('/owner/cabang/{id}', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $dataOwner = new Owner(null, null);
    $dataOwner->setDb($this->db);
    return $response->withJson($dataOwner->ownerUpdateCabang($data['cabang'], $args['id']), SERVER_OK);
});

// DELETE CABANG
$app->delete('/owner/cabang/{id}', function ($request, $response, $args) {
    $dataOwner = new Owner(null, null);
    $dataOwner->setDb($this->db);
    return $response->withJson($dataOwner->ownerDeleteCabang($args['id']), SERVER_OK);
});

// GET REKAP DASHBOARD OWNER
$app->get('/owner/rekap/dasbor/', function ($request, $response) {
    $dataOwner = new Owner(null, null);
    $dataOwner->setDb($this->db);
    return $response->withJson($dataOwner->ownerRekapDasbor(), SERVER_OK);
});

// GET REKAP STRUKTUR OWNER
$app->get('/owner/rekap/struktur/', function ($request, $response) {
    $dataOwner = new Owner(null, null);
    $dataOwner->setDb($this->db);
    return $response->withJson($dataOwner->ownerRekapStruktur(), SERVER_OK);
});

// OWNER
// GET Driver
$app->get('/owner/driver/web/', function ($request, $response) {
    $getdriver = new Umum();
    $getdriver->setDb($this->db);
    $driver = $getdriver->getAllDriverWeb();
    if (empty($driver)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }
    $dataDriver = [];
    for ($i = 0; $i < count($driver); $i++) {
        if ($driver[$i]['latitude'] == POSITION_LAT && $driver[$i]['longitude'] == POSITION_LONG) {

        }
        $dataDriver[$i]['id_user'] = $driver[$i]['id_user'];
        $dataDriver[$i]['nama'] = decrypt($driver[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['email'] = decrypt($driver[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_telpon'] = decrypt($driver[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_polisi'] = decrypt($driver[$i]['no_polisi'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['cabang'] = $driver[$i]['cabang'];
        $dataDriver[$i]['jenis_kendaraan'] = $driver[$i]['jenis_kendaraan'];
        $dataDriver[$i]['merk_kendaraan'] = $driver[$i]['merk_kendaraan'];
        $dataDriver[$i]['latitude'] = $driver[$i]['latitude'];
        $dataDriver[$i]['longitude'] = $driver[$i]['longitude'];
    }
    return $response->withJson(['status' => 'Success', 'data' => $dataDriver], SERVER_OK);
});

// GET DATA UNTUK OWNER TERBARU PAGINATION
// OWNER GET ALL EVENT
$app->post('/owner/event/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $event = $owner->getEventWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($event)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Event Tidak Ditemukan'], SERVER_OK);
    }

    foreach ($event as $index => $value) {
        $tanggal = $value['tanggal_event'];
        $timestamp = strtotime($tanggal);
        $timestamp = date("d-m-Y", $timestamp);
        $event[$index]['tanggal_event'] = $timestamp;
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsEvent(), 'recordsFiltered' => $owner->countsEvent(), 'data' => $event], SERVER_OK);
});

// OWNER GET ALL DRIVER
$app->post('/owner/driver/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $driver = $owner->getDriverWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($driver)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Driver Tidak Ditemukan'], SERVER_OK);
    }

    $dataDriver = [];
    for ($i = 0; $i < count($driver); $i++) {
        $dataDriver[$i]['nama'] = decrypt($driver[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['email'] = decrypt($driver[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_telpon'] = decrypt($driver[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['no_polisi'] = decrypt($driver[$i]['no_polisi'], MOUGO_CRYPTO_KEY);
        $dataDriver[$i]['cabang'] = $driver[$i]['cabang'];
        $dataDriver[$i]['jenis_kendaraan'] = $driver[$i]['jenis_kendaraan'];
        $dataDriver[$i]['merk_kendaraan'] = $driver[$i]['merk_kendaraan'];
        $dataDriver[$i]['foto_ktp'] = $driver[$i]['foto_ktp'];
        $dataDriver[$i]['foto_kk'] = $driver[$i]['foto_kk'];
        $dataDriver[$i]['foto_sim'] = $driver[$i]['foto_sim'];
        $dataDriver[$i]['foto_skck'] = $driver[$i]['foto_skck'];
        $dataDriver[$i]['foto_stnk'] = $driver[$i]['foto_stnk'];
        $dataDriver[$i]['foto_diri'] = $driver[$i]['foto_diri'];
        $dataDriver[$i]['no_rekening'] = $driver[$i]['no_rekening'];
        $dataDriver[$i]['nama_bank'] = $driver[$i]['name'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsDriver(), 'recordsFiltered' => $owner->countsDriver(), 'data' => $dataDriver], SERVER_OK);
});

// OWNER GET ALL CUSTOMER
$app->post('/owner/customer/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $customer = $owner->getCustomerWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($customer)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Customer Tidak Ditemukan'], SERVER_OK);
    }

    $dataCustomer = [];
    for ($i = 0; $i < count($customer); $i++) {
        $dataCustomer[$i]['nama'] = decrypt($customer[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataCustomer[$i]['email'] = decrypt($customer[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataCustomer[$i]['no_telpon'] = decrypt($customer[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataCustomer[$i]['provinsi'] = $customer[$i]['provinsi'];
        $dataCustomer[$i]['kota'] = $customer[$i]['kota'];
        $dataCustomer[$i]['kode_referal'] = $customer[$i]['kode_referal'];
        $dataCustomer[$i]['kode_sponsor'] = $customer[$i]['kode_sponsor'];
        $dataCustomer[$i]['no_rekening'] = $customer[$i]['no_rekening'];
        $dataCustomer[$i]['nama_bank'] = $customer[$i]['name'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsCustomer(), 'recordsFiltered' => $owner->countsCustomer(), 'data' => $dataCustomer], SERVER_OK);
});

// OWNER GET ALL ADMIN
$app->post('/owner/admin/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $admin = $owner->getAdminWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($admin)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Admin Tidak Ditemukan'], SERVER_OK);
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsAdmin(), 'recordsFiltered' => $owner->countsAdmin(), 'data' => $admin], SERVER_OK);
});

// OWNER GET ALL CABANG
$app->post('/owner/cabang/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $cabang = $owner->getCabangWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($cabang)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Cabang Tidak Ditemukan'], SERVER_OK);
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsCabang(), 'recordsFiltered' => $owner->countsCabang(), 'data' => $cabang], SERVER_OK);
});

// OWNER GET ALL WITHDRAW
$app->post('/owner/withdraw/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Admin(null, null, null, null);
    $owner->setDb($this->db);

    $withdraw = $owner->getWithdrawWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($withdraw)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Withdraw Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($withdraw); $i++) {
        $withdraw[$i]['nama'] = decrypt($withdraw[$i]['nama'], MOUGO_CRYPTO_KEY);
        $withdraw[$i]['jumlah'] = (double) $withdraw[$i]['jumlah'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsWithdraw(), 'recordsFiltered' => $owner->countsWithdraw(), 'data' => $withdraw], SERVER_OK);
});

// OWNER GET ALL TOPUP
$app->post('/owner/topup/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $topup = $owner->getTopupWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($topup)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Topup Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($topup); $i++) {
        $topup[$i]['nama'] = decrypt($topup[$i]['nama'], MOUGO_CRYPTO_KEY);
        $topup[$i]['jumlah_topup'] = (double) $topup[$i]['jumlah_topup'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsTopup(), 'recordsFiltered' => $owner->countsTopup(), 'data' => $topup], SERVER_OK);
});

// OWNER GET ALL TRANSFER
$app->post('/owner/transfer/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $transfer = $owner->getTransferWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($transfer)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Transfer Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($transfer); $i++) {
        $transfer[$i]['nama_pengirim'] = decrypt($transfer[$i]['nama_pengirim'], MOUGO_CRYPTO_KEY);
        $transfer[$i]['nama_penerima'] = decrypt($transfer[$i]['nama_penerima'], MOUGO_CRYPTO_KEY);
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsTransfer(), 'recordsFiltered' => $owner->countsTransfer(), 'data' => $transfer], SERVER_OK);
});

// OWNER GET ALL BONUS LEVEL
$app->post('/owner/bonus/level/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $bonus = $owner->getBonusLevelWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bonus)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bonus Level Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($bonus); $i++) {
        $bonus[$i]['nama'] = decrypt($bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
        $bonus[$i]['pendapatan'] = (double) $bonus[$i]['pendapatan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsBonusLevel(), 'recordsFiltered' => $owner->countsBonusLevel(), 'data' => $bonus], SERVER_OK);
});

// OWNER GET ALL BONUS TRIP
$app->post('/owner/bonus/trip/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $bonus = $owner->getBonusTripWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bonus)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bonus Trip Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($bonus); $i++) {
        $bonus[$i]['nama'] = decrypt($bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
        $bonus[$i]['pendapatan'] = (double) $bonus[$i]['pendapatan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsBonusTrip(), 'recordsFiltered' => $owner->countsBonusTrip(), 'data' => $bonus], SERVER_OK);
});

// OWNER GET ALL BONUS TRANSFER
$app->post('/owner/bonus/transfer/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $bonus = $owner->getBonusTransferWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bonus)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bonus Transfer Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($bonus); $i++) {
        $bonus[$i]['nama'] = decrypt($bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
        $bonus[$i]['pendapatan'] = (double) $bonus[$i]['pendapatan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsBonusTransfer(), 'recordsFiltered' => $owner->countsBonusTransfer(), 'data' => $bonus], SERVER_OK);
});

// OWNER GET ALL BONUS SPONSOR
$app->post('/owner/bonus/sponsor/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $bonus = $owner->getBonusSponsorWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bonus)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bonus Sponsor Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($bonus); $i++) {
        $bonus[$i]['nama'] = decrypt($bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
        $bonus[$i]['pendapatan'] = (double) $bonus[$i]['pendapatan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsBonusSponsor(), 'recordsFiltered' => $owner->countsBonusSponsor(), 'data' => $bonus], SERVER_OK);
});

// OWNER GET ALL BONUS TITIK
$app->post('/owner/bonus/titik/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $bonus = $owner->getBonusTitikWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($bonus)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Bonus Titik Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($bonus); $i++) {
        $bonus[$i]['nama'] = decrypt($bonus[$i]['nama'], MOUGO_CRYPTO_KEY);
        $bonus[$i]['pendapatan'] = (double) $bonus[$i]['pendapatan'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsBonusTitik(), 'recordsFiltered' => $owner->countsBonusTitik(), 'data' => $bonus], SERVER_OK);
});

// OWNER GET ALL TRIP
$app->post('/owner/trip/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $trip = $owner->getTripWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($trip)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Trip Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($trip); $i++) {
        $trip[$i]['nama_driver'] = decrypt($trip[$i]['nama_driver'], MOUGO_CRYPTO_KEY);
        $trip[$i]['nama_customer'] = decrypt($trip[$i]['nama_customer'], MOUGO_CRYPTO_KEY);
        $trip[$i]['total_harga'] = (double) $trip[$i]['total_harga'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsTrip(), 'recordsFiltered' => $owner->countsTrip(), 'data' => $trip], SERVER_OK);
});

// OWNER GET ALL USER
$app->post('/owner/user/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $user = $owner->getUserWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($user)) {
        return $response->withJson(['status' => 'Error', 'message' => 'User Tidak Ditemukan'], SERVER_OK);
    }

    $dataUser = [];
    for ($i = 0; $i < count($user); $i++) {
        $dataUser[$i]['id_user'] = $user[$i]['id_user'];
        $dataUser[$i]['nama'] = decrypt($user[$i]['nama'], MOUGO_CRYPTO_KEY);
        $dataUser[$i]['email'] = decrypt($user[$i]['email'], MOUGO_CRYPTO_KEY);
        $dataUser[$i]['no_telpon'] = decrypt($user[$i]['no_telpon'], MOUGO_CRYPTO_KEY);
        $dataUser[$i]['provinsi'] = $user[$i]['provinsi'];
        $dataUser[$i]['kota'] = $user[$i]['kota'];
        $dataUser[$i]['kode_referal'] = $user[$i]['kode_referal'];
        $dataUser[$i]['kode_sponsor'] = $user[$i]['kode_sponsor'];
        $dataUser[$i]['no_rekening'] = $user[$i]['no_rekening'];
        $dataUser[$i]['nama_bank'] = $user[$i]['name'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsUser(), 'recordsFiltered' => $owner->countsUser(), 'data' => $dataUser], SERVER_OK);
});

// OWNER GET ALL TRIP
$app->post('/owner/trip/driver/web/', function ($request, $response) {
    $data = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);

    $trip = $owner->getTripDriverWeb($data['order'][0]['column'], $data['order'][0]['dir'], $data['start'], $data['length'], $data['search']['value']);

    if (empty($trip)) {
        return $response->withJson(['status' => 'Error', 'message' => 'Trip Tidak Ditemukan'], SERVER_OK);
    }

    for ($i = 0; $i < count($trip); $i++) {
        $trip[$i]['nama'] = decrypt($trip[$i]['nama'], MOUGO_CRYPTO_KEY);
        $trip[$i]['total_harga'] = (double) $trip[$i]['total_harga'];
    }

    return $response->withJson(['status' => 'Success', 'draw' => $data['draw'], 'recordsTotal' => $owner->countsTripDriver(), 'recordsFiltered' => $owner->countsTripDriver(), 'data' => $trip], SERVER_OK);
});

// OWNER GET NOMOR EMERGENCY
$app->get('/owner/nomor/emergency/', function ($request, $response){
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson(['status' => 'Success', 'data' => $owner->getNomorEmergency()], SERVER_OK);
});

// OWNER UPDATE NOMOR EMERGENCY
$app->put('/owner/nomor/emergency/{id}', function ($request, $response, $args){
    $update = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson($owner->editNomorEmergency($args['id'], $update['nomor_emergency']), SERVER_OK);
});

// OWNER GET BANK MOUGO
$app->get('/owner/bank/mougo/', function ($request, $response){
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson(['status' => 'Success', 'data' => $owner->getBankMougo()], SERVER_OK);
});

// OWNER UPDATE BANK MOUGO
$app->put('/owner/bank/mougo/{id}', function ($request, $response, $args){
    $update = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson($owner->editBankMougo($args['id'], $update['norek_bank'], $update['nama_bank'], $update['atas_nama_bank']), SERVER_OK);
});

// OWNER GET HARGA AWAL TRIP
$app->get('/owner/harga/awal/', function ($request, $response){
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    $data = $owner->getHargaAwalTrip();
    return $response->withJson(['status' => 'Success', 'data' => $data], SERVER_OK);
});

// OWNER UPDATE HARGA AWAL TRIP
$app->put('/owner/harga/awal/{id}', function ($request, $response, $args){
    $update = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson($owner->editHargaAwalTrip($args['id'], $update['harga_awal_motor'], $update['harga_awal_mobil']), SERVER_OK);
});

// OWNER GET HARGA PERKILO TRIP
$app->get('/owner/harga/perkilo/', function ($request, $response){
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    $data = $owner->getHargaPerkiloTrip();
    return $response->withJson(['status' => 'Success', 'data' => $data], SERVER_OK);
});


// OWNER UPDATE HARGA PERKILO TRIP
$app->put('/owner/harga/perkilo/{id}', function ($request, $response, $args){
    $update = $request->getParsedBody();
    $owner = new Owner(null, null);
    $owner->setDb($this->db);
    return $response->withJson($owner->editHargaPerkiloTrip($args['id'], $update['harga_perkilo_motor'], $update['harga_perkilo_mobil']), SERVER_OK);
});