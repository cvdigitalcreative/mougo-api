<?php
require_once "randomGen.php";

//Customer
//REGISTER
$app->post('/customer/register/', function ($request, $response, $args) {
    $user = $request->getParsedBody();

    //Check Input
        if (!isset($user['nama'])) {
            return $response->withJson(['status' => 'Error', 'message' => 'Nama Kosong'], 200);
        }
        if (!isset($user['email'])) {
            return $response->withJson(['status' => 'Error', 'message' => 'Email Kosong'], 200);
        }
        if (!isset($user['no_telpon'])) {
            return $response->withJson(['status' => 'Error', 'message' => 'No Telpon Kosong'], 200);
        }
        if (!isset($user['password'])) {
            return $response->withJson(['status' => 'Error', 'message' => 'Password Kosong'], 200);
        }
        if (!isset($user['kode_referal'])) {
            $user_atasan_ref = 'RAAA000';
        } else {
            $user_atasan_ref = $user['kode_referal'];
        }
        if (!isset($user['kode_sponsor'])) {
            $user_atasan_sp = 'SAAA000';
        } else {
            $user_atasan_sp = $user['kode_sponsor'];
        }

    $user_email = $user['email'];
    $user_no_tlp = $user['no_telpon'];
    //cek email dan no_telpon
        $sql_cek_tlp = "SELECT * FROM user
            WHERE email LIKE '$user_email' OR no_telpon LIKE '$user_no_tlp'";

        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute();
        $stmtcek = $estcek->fetch();
        $numrowcek = $estcek->rowCount();

        if ($numrowcek > 0) {
            return $response->withJson(['status' => 'Error', 'message' => 'Email / Nomor Telpon Telah digunakan'], 200);
        }

    //User Input
        $sql = "INSERT INTO user (id_user , nama , email , no_telpon , role , password , status_aktif_trip)
                    VALUES (:id_user , :nama , :email , :no_telpon , :role , :password , :status_aktif_trip)";
        $id_user = sha1($user['nama'] . $user['email'] . $user['no_telpon']);
        $data_user = [
            ':id_user' => $id_user,
            ':nama' => $user['nama'],
            ':email' => $user['email'],
            ':no_telpon' => $user['no_telpon'],
            ':role' => 1,
            ':password' => $user['password'],
            ':status_aktif_trip' => 2,
        ];

        $este = $this->db->prepare($sql);

       

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
                    WHERE kode_referal = '$user_atasan_ref'";

        $est1 = $this->db->prepare($sql_ref_cek);
        $est1->execute();
        $stmt = $est1->fetch();
        $numrow = $est1->rowCount();

        $sql_sp_cek = "SELECT id_user AS id_atasan FROM kode_sponsor
                    WHERE kode_sponsor = '$user_atasan_sp'";

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

                if ($este->execute($data_user) && $estim->execute($data_ref) && $estim2->execute($data_sp)) {

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

                    return $response->withJson(['status' => 'Success', 'message' => 'User Created'], 200);
                }return $response->withJson(['status' => 'Error', 'message' => 'Input Bermasalah'], 200);

        }return $response->withJson(['status' => 'Error', 'message' => 'Referal Atasan Tidak Ditemukan'], 200);

});

//Customer
//LOGIN
$app->post('/customer/login/', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $password = $data['password'];
    if (isset($data['email'])) {

        $user_email = $data['email'];
        $sql_cek_tlp = "SELECT * FROM user
        WHERE email LIKE '$user_email'";

        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute();
        $stmtcek = $estcek->fetch();
        $numrowcek = $estcek->rowCount();

        if ($numrowcek < 1) {
            return $response->withJson(['status' => 'Error', 'message' => 'Email Belum Terdaftar'], 200);
        }

        $sql = "SELECT user.id_user , token , password FROM user
                        INNER JOIN api_token ON api_token.id_user = user.id_user
                        WHERE email = :email AND password = :pass ";
        $dats = [
            ":email" => $data['email'],
            ":pass" => $password,
        ];
        $est = $this->db->prepare($sql);
        $est->execute($dats);
        $stmt = $est->fetch();
        $numrow = $est->rowCount();
        $res['id_user'] = $stmt['id_user'];
        $res['token'] = $stmt['token'];
        if ($numrow > 0) {
            if ($stmt['password'] == $password) {
                return $response->withJson(['status' => 'Success', 'data' => $res], 200);
            }
        }return $response->withJson(['status' => 'Error', 'message' => 'Password Salah'], 200);
    }
    if (isset($data['no_telpon'])) {

        $user_tlp = $data['no_telpon'];
        $sql_cek_tlp = "SELECT * FROM user
        WHERE no_telpon LIKE '$user_tlp'";

        $estcek = $this->db->prepare($sql_cek_tlp);
        $estcek->execute();
        $stmtcek = $estcek->fetch();
        $numrowcek = $estcek->rowCount();

        if ($numrowcek < 1) {
            return $response->withJson(['status' => 'Error', 'message' => 'Nomor Telpon Belum Terdaftar'], 200);
        }

        $sql = "SELECT user.id_user , token , password FROM user
                INNER JOIN api_token ON api_token.id_user = user.id_user
                WHERE no_telpon = :no_telpon AND password = :pass ";
        $dats = [
            ":no_telpon" => $data['no_telpon'],
            ":pass" => $password,
        ];
        $est = $this->db->prepare($sql);
        $est->execute($dats);
        $stmt = $est->fetch();
        $numrow = $est->rowCount();
        $res['id_user'] = $stmt['id_user'];
        $res['token'] = $stmt['token'];
        if ($numrow > 0) {
            if ($stmt['password'] == $password) {
                return $response->withJson(['status' => 'Success', 'data' => $res], 200);
            }
        }return $response->withJson(['status' => 'Error', 'message' => 'Password Salah'], 200);
    }
    if (!isset($data['no_telpon']) && !isset($data['email'])) {
        return $response->withJson(['status' => 'Error', 'message' => 'No Telpon / Email tidak boleh kosong'], 200);
    }
    return $response->withJson(['status' => 'Error', 'message' => 'No Telpon / Email Tidak Ditemukan'], 200);

});
