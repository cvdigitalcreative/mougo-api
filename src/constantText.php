<?php
// HTTP RESPONSE
define('SERVER_OK', 200);
define('SERVER_BAD', 400);

// ROLE
define('USER_ROLE', 1);
define('DRIVER_ROLE', 2);

// KODE REFERAL DAN SPONSOR DMS MOUGO
define('KODE_REFERAL_DMS', "RAAA000");
define('KODE_SPONSOR_DMS', "SAAA000");

// KODE REFERAL DAN SPONSOR USER
define('KODE_REFERAL_USER', "R");
define('KODE_SPONSOR_USER', "S");

// SALDO INFO
define('TAMBAH_SALDO', 1);

// FUNGSI USER
define('REGISTER', 'register');
define('LOGIN', 'login');
define('PROFILE', 'profile');

define('PROFILE_DRIVER', 'profile_driver');

// DRIVER
define('STATUS_ONLINE', 0);
define('STATUS_AKUN_AKTIF', 0);
define('STATUS_AKTIF_USER', 2);
define('STATUS_AKTIF_USER_REGISTER', 3);
define('STATUS_DRIVER_UPLOAD_FOTO', 2);
define('STATUS_DRIVER_AKTIF', 1);

// USER HITS, SALDO, POINT AWAL
define('HITS_AWAL', 0);
define('SALDO_AWAL', 0);
define('POINT_AWAL', 0);

// HARGA
define('HARGA_JARAK_MINIMAL', 9000);
define('HARGA_JARAK_PERKILO', 2000);

// JARAK MIN
define('JARAK_MINIMAL', 2);

// STATUS TRIP
define('STATUS_MENCARI_DRIVER', 1);
define('STATUS_DRIVER_MENJEMPUT', 2);
define('STATUS_MENGANTAR_KETUJUAN', 3);
define('STATUS_SAMPAI_TUJUAN', 4);
define('STATUS_CANCEL', 5);

// POSITION STATUS TRIP
define('STATUS_TRIP_MENJEMPUT', "Driver Dalam Perjalanan Menjemput");
define('STATUS_TRIP_MENGANTAR', "Driver Sedang Mengantar Anda");
define('STATUS_TRIP_SELESAI', "Selamat Anda Telah Sampai Tujuan");

// POSITION SET
define('POSITION_LAT', -2.980670100);
define('POSITION_LONG', 104.726203500);

// DRIVER SILUMAN
define('ID_DRIVER_SILUMAN', "f768adacff6bb95eafb7d9a2d56be1c2f2ef6d13");

// ADMIN MOUGO
define('ADMIN_SILUMAN_MOUGO', "mougodms@gmail.com");

// STATUS TOPUP
define('STATUS_TOPUP_PENDING', 1);
define('STATUS_TOPUP_ACCEPT', 2);

// STATUS TOPUP
define('TOPUP_ACCEPT', 1);
define('TOPUP_REJECT', 2);

// REK MOUGO
define('NO_REK_PERUSAHAAN', "081231251924");
define('NAMA_REK_PERUSAHAAN', "PT. DMS MOUGO");

// STATUS TOKEN LUPA PASSWORD
define('STATUS_TOKEN_UNACTIVE', 2);
define('STATUS_TOKEN_ACTIVE', 1);

// PASSWORD AES
define('MOUGO_CRYPTO_KEY', '0eGKi0b67fGRNJVgSDmyP+Ien68bN2KliJ6S/DlrV9M=');

// MAKSIMAL EVENT
define('EVENT_MAKSIMAL', 5);

// Foto Role
define('FOTO_KTP', 0);
define('FOTO_KK', 1);